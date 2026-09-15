const { chromium } = require('playwright');
const { parseArgs } = require('./lib/args');
const { getExtractor } = require('./lib/dispatch');
const { resolveUrl } = require('./lib/sectionUrls');
const { getAuthenticatedContext } = require('./login');

// Yahoo's league standings page (https://football.fantasysports.yahoo.com/f1/<leagueId>,
// resolved by resolveUrl for section "team_names") renders a `#standingstable`
// table with one row per team. Confirmed via a real inspect.js capture
// (scraper/inspection-output/team_names-1789487642135/page.html) that:
//   - none of the 27 captured network JSON responses are Yahoo's own fantasy
//     data endpoints (they're all ad-tech/analytics calls: prebid, rubicon,
//     ybar, signal-service, etc.) — there is no structured JSON to intercept
//     for this section, so this extractor scrapes the rendered DOM instead.
//   - the standings table has a "Team" column (name + yahoo team id, via the
//     row's `data-target="/f1/<leagueId>/<teamId>"` attribute and the team
//     link's href) and a "Moves" column, but NO trades count anywhere on the
//     page or in any network response. `trades` is therefore always null
//     here — see handle_scraped_team_names() in yahooScrapeRequest.php for
//     how that's handled on the write side (existing trades values are left
//     untouched, never zeroed out).
async function extractTeamNames(args, context) {
    if (!args['league-id']) {
        throw new Error('Missing required --league-id=<id> argument.');
    }

    const url = resolveUrl(args);
    const page = await context.newPage();
    try {
        await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });

        if (page.url().includes('login.yahoo.com')) {
            throw new Error(
                'Redirected to Yahoo login while loading the standings page — ' +
                'the saved session has expired. Re-run to trigger a fresh login.'
            );
        }

        await page.waitForSelector('#standingstable tbody tr', { timeout: 15000 });

        const rows = await page.$$eval('#standingstable tbody tr', (trs) =>
            trs.map((tr) => {
                const target = tr.getAttribute('data-target') || '';
                const idMatch = target.match(/\/(\d+)$/);
                const nameLink = tr.querySelector('td.Tst-manager a.F-reset');
                const movesCell = tr.querySelector('td.last');
                return {
                    yahooTeamId: idMatch ? idMatch[1] : null,
                    name: nameLink ? nameLink.textContent.trim() : null,
                    movesRaw: movesCell ? movesCell.textContent.trim() : null,
                };
            })
        );

        const teams = [];
        for (const row of rows) {
            if (!row.yahooTeamId || !row.name) {
                console.error(`Skipping unparseable standings row: ${JSON.stringify(row)}`);
                continue;
            }
            teams.push({
                yahooTeamId: Number(row.yahooTeamId),
                name: row.name,
                moves: !row.movesRaw || row.movesRaw === '-' ? 0 : Number(row.movesRaw),
                trades: null,
            });
        }
        return teams;
    } finally {
        await page.close();
    }
}

// The originally-assumed `/f1/<leagueId>/scoreboard?week=<N>` URL 404s for
// real ("The document you requested was not found") — confirmed against a
// live logged-in session against the current (2026) league. The real page
// that shows all of a week's matchups at once is the league home page's
// "Matchups" module, fetched via the same query params its own prev/next
// links use (`?matchup_week=<N>&module=matchups&lhst=matchups` — see the
// corrected default URL in lib/sectionUrls.js). Confirmed via a real
// inspect.js capture against the corrected URL
// (scraper/inspection-output/matchups-1789493634106/page.html, league
// 18261/week 1) plus a direct fetch of that same URL for an unplayed week
// (week 2: "Not started yet", scores all 0.00) — one page load returns ALL
// 5 matchups for a 10-manager league, exactly as the spec hoped, so there's
// no need to loop per-manager the way the OAuth API does.
//
// Per matchup `<li data-target="/f1/<leagueId>/matchup?week=<N>&mid1=<a>&mid2=<b>">`:
//   - `mid1`/`mid2` in that data-target attribute are the two sides' real
//     Yahoo team ids — read directly from there rather than inferred from
//     left/right layout order (more robust, and self-documenting).
//   - each side's block has a team-name link (`a.F-link`, whose href ends
//     in `/<teamId>`, used to double check the block's id agrees with
//     mid1/mid2) and exactly two "plain numeric text" leaf divs: the actual
//     score first, then a second, greyed-out ("F-shade") number.
//   - for the completed week 1 test case, that second number is NOT null
//     and not zero — it's genuinely present. Confirmed via the *individual*
//     matchup/recap page (`/f1/<leagueId>/matchup?week=1&mid1=1&mid2=7`),
//     which explicitly labels that same figure "Orig Proj" right next to
//     the exact same two values (144.44 / 139.60) seen in the module. So,
//     contrary to the spec's speculation, Yahoo's website DOES keep
//     showing projected points for an already-played week — this extractor
//     always returns a real `projected` number, never a fabricated one, and
//     never null for a page that actually rendered a matchup row.
async function extractMatchups(args, context) {
    if (!args['league-id']) {
        throw new Error('Missing required --league-id=<id> argument.');
    }

    const weekList = String(args.weeks || args.week || '')
        .split(',')
        .map((w) => w.trim())
        .filter(Boolean);
    if (weekList.length === 0) {
        throw new Error('Missing required --weeks=<comma-separated weeks> (or --week=<N>) argument.');
    }

    const matchups = [];
    for (const week of weekList) {
        const weekMatchups = await extractMatchupsForWeek(args, context, week);
        matchups.push(...weekMatchups);
    }
    return matchups;
}

async function extractMatchupsForWeek(args, context, week) {
    const url = resolveUrl({ ...args, week });
    const page = await context.newPage();
    try {
        await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });

        if (page.url().includes('login.yahoo.com')) {
            throw new Error(
                `Redirected to Yahoo login while loading the week ${week} matchups page — ` +
                'the saved session has expired (or, for a non-current season, this league ' +
                'requires re-authentication that the saved session alone cannot satisfy). ' +
                'Re-run to trigger a fresh login.'
            );
        }

        await page.waitForSelector('#matchupweek', { timeout: 15000 });

        const rows = await page.$$eval('#matchupweek li[data-target*="/matchup?"]', (lis) =>
            lis.map((li) => {
                const target = li.getAttribute('data-target') || '';
                const idMatch = target.match(/mid1=(\d+)&mid2=(\d+)/);

                const blocks = Array.from(li.querySelectorAll('.Grid-u-6-13.Py-med')).map((block) => {
                    const nameLink = block.querySelector('a.F-link');
                    const href = (nameLink && nameLink.getAttribute('href')) || '';
                    const hrefMatch = href.match(/\/(\d+)$/);
                    const numbers = Array.from(block.querySelectorAll('div'))
                        .map((d) => d.textContent.trim())
                        .filter((text) => /^\d+(\.\d+)?$/.test(text));
                    return {
                        yahooTeamId: hrefMatch ? hrefMatch[1] : null,
                        name: nameLink ? nameLink.textContent.trim() : null,
                        score: numbers[0] ?? null,
                        projected: numbers[1] ?? null,
                    };
                });

                return {
                    dataTarget: target,
                    mid1: idMatch ? idMatch[1] : null,
                    mid2: idMatch ? idMatch[2] : null,
                    blocks,
                };
            })
        );

        const results = [];
        for (const row of rows) {
            if (!row.mid1 || !row.mid2 || row.blocks.length !== 2) {
                console.error(`Skipping unparseable matchup row for week ${week}: ${JSON.stringify(row)}`);
                continue;
            }

            const [blockA, blockB] = row.blocks;
            // Match blocks to mid1/mid2 by id rather than assuming left=mid1,
            // right=mid2 — that ordering held in every real row inspected,
            // but matching explicitly costs nothing and is more robust.
            const side1 = [blockA, blockB].find((b) => b.yahooTeamId === row.mid1);
            const side2 = [blockA, blockB].find((b) => b.yahooTeamId === row.mid2);

            if (!side1 || !side2 || side1.score === null || side2.score === null) {
                console.error(`Skipping matchup week ${week} (${row.dataTarget}) — could not match both team blocks to mid1/mid2.`);
                continue;
            }

            results.push({
                week: Number(week),
                team1: {
                    yahooTeamId: Number(side1.yahooTeamId),
                    name: side1.name,
                    score: Number(side1.score),
                    projected: side1.projected !== null ? Number(side1.projected) : null,
                },
                team2: {
                    yahooTeamId: Number(side2.yahooTeamId),
                    name: side2.name,
                    score: Number(side2.score),
                    projected: side2.projected !== null ? Number(side2.projected) : null,
                },
            });
        }
        return results;
    } finally {
        await page.close();
    }
}

// The originally-assumed `?scope=all&type=trade` query params don't 404
// (unlike matchups' original guess) but they're simply not real params
// Yahoo's transactions page understands — confirmed live: that URL loads
// fine, yet the page's own nav shows "All Transactions" as Selected, not
// "Trades", and all 16 rows returned were plain adds/drops. The real
// filter param, read directly off the page's own "Trades" tab link
// (`<a class="Navtarget" href="?transactionsfilter=trade">Trades</a>`),
// is `transactionsfilter=trade` — see the corrected default URL in
// lib/sectionUrls.js.
//
// CONFIRMED (against a real inspect.js capture of the corrected URL,
// league 18261, Sep 2026): with that filter applied, the nav's "Trades"
// tab correctly shows "Selected" and the table body renders Yahoo's own
// empty-state markup (`.F-faded` div containing "No recent transactions")
// — this league genuinely has zero trades so far in 2026 (matches the
// `trades` DB table, which also has zero 2026 rows). No JSON network
// response on this page is Yahoo's own fantasy data either (same finding
// as team_names: all 19-20 captured responses are ad-tech/analytics), so
// this is DOM-scraped like team_names, not JSON-intercepted.
//
// UNVERIFIED CAVEAT: this account is only in the one league (no other
// Yahoo fantasy football league reachable from its nav to peek at for a
// real trade example), and past seasons of this same league aren't
// reachable with the saved session (see the design doc's "Open risks").
// So there is no real trade row anywhere reachable to build or verify the
// non-empty-table parsing logic against — everything below the empty-
// state check is a best-effort structural inference from the CONFIRMED
// add/drop row layout in this exact table (same one-icon-per-action
// pattern, same per-player sub-label under each player's name, same
// right-hand Tst-team-name attribution + timestamp), not a verified
// pattern. Per-row/per-player parsing failures are skipped and logged
// rather than guessed. The returned shape is the same flat array every
// other extractor returns (so the PHP dispatch layer doesn't need a
// per-section special case) — instead, handle_scraped_trades() in
// yahooScrapeRequest.php adds an extra caution banner whenever this
// section actually writes 1+ rows, since a non-empty result exercises
// entirely unverified parsing. The first time this ever returns real
// rows, manually diff them against the Yahoo page by eye before trusting
// a DB write.
//
// Team identity for both sides of a trade is resolved via the page's own
// team-picker flyout (`#playermatchupsteam select option`), NOT via
// scraping a link out of the trade row itself — confirmed present in
// every capture of this page (trades or not), it maps each team's real
// Yahoo team id straight from `value="...&mid=<id>"` to that team's
// display name (e.g. `<option value="?transactionsfilter=trade&mid=3">
// Love in the Time of Jeanty</option>`). This sidesteps needing to know
// whether an unseen trade row's "traded to <team>" text is ever a real
// link — a plain-text team name is enough to resolve a Yahoo team id via
// this map.
//
// `tradeIdentifier` cannot be Yahoo's real internal transaction id: unlike
// the OAuth API's JSON (`transaction_id`), nothing on the rendered page
// exposes it (confirmed: no `data-*`/id attribute anywhere on a
// transaction `<tr>` carries one). Since `handle_scraped_trades()`'s
// `firstOrCreate()` key is `(player, year, manager_from_id)` — NOT
// `trade_identifier` — the real Yahoo id was never load-bearing for
// write correctness; it's informational metadata used to group both
// sides of the same swap together. A synthetic id (`<leagueId><row
// index>`) is used instead, unique per row within one extraction run,
// which is all that's needed for that grouping.
async function extractTrades(args, context) {
    if (!args['league-id']) {
        throw new Error('Missing required --league-id=<id> argument.');
    }
    if (!args.year) {
        throw new Error('Missing required --year=<season year> argument.');
    }

    const url = resolveUrl(args);
    const page = await context.newPage();
    try {
        await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });

        if (page.url().includes('login.yahoo.com')) {
            throw new Error(
                'Redirected to Yahoo login while loading the trades transactions page — ' +
                'the saved session has expired. Re-run to trigger a fresh login.'
            );
        }

        await page.waitForSelector('#transactions table.Tst-transaction-table', { timeout: 15000 });

        const teamIdByName = await page.$$eval('#playermatchupsteam option', (opts) => {
            const map = {};
            for (const opt of opts) {
                const match = (opt.getAttribute('value') || '').match(/mid=(\d+)/);
                if (match) {
                    map[opt.textContent.trim()] = Number(match[1]);
                }
            }
            return map;
        });
        if (Object.keys(teamIdByName).length === 0) {
            throw new Error(
                'Could not find the team-picker flyout (#playermatchupsteam) used to map team ' +
                'names to Yahoo team ids — the transactions page structure may have changed.'
            );
        }

        const rawRows = await page.$$eval('#transactions table.Tst-transaction-table tbody > tr', (trs) =>
            trs.map((tr) => {
                if (tr.querySelector('.F-faded')) {
                    return { empty: true };
                }

                const iconTitles = Array.from(tr.querySelectorAll('td.Grid-u-1-12 span.F-icon'))
                    .map((el) => el.getAttribute('title') || '');

                const playerBlocks = Array.from(tr.querySelectorAll('td.Fill-x > div')).map((div) => {
                    const nameLink = Array.from(div.querySelectorAll('a')).find(
                        (a) => !a.classList.contains('playernote')
                    );
                    const subLabel = div.querySelector('h6.F-shade');
                    return {
                        player: nameLink ? nameLink.textContent.trim() : null,
                        subLabel: subLabel ? subLabel.textContent.trim() : null,
                    };
                });

                const teamLink = tr.querySelector('a.Tst-team-name');
                const timestampEl = tr.querySelector('.F-timestamp');

                return {
                    empty: false,
                    iconTitles,
                    playerBlocks,
                    rowTeamName: teamLink ? teamLink.textContent.trim() : null,
                    timestampText: timestampEl ? timestampEl.textContent.trim() : null,
                };
            })
        );

        if (rawRows.length === 0 || rawRows.every((row) => row.empty)) {
            return [];
        }

        const trades = [];
        let rowIndex = 0;
        for (const row of rawRows) {
            if (row.empty) {
                continue;
            }
            rowIndex++;

            const isTrade = row.iconTitles.some((title) => /trade/i.test(title));
            if (!isTrade) {
                console.error(`Skipping non-trade row despite the trades filter (icon titles: ${JSON.stringify(row.iconTitles)}).`);
                continue;
            }
            if (!row.rowTeamName || !row.timestampText) {
                console.error(`Skipping trade row missing an attributed team or timestamp: ${JSON.stringify(row)}`);
                continue;
            }

            const fromTeamId = teamIdByName[row.rowTeamName];
            if (!fromTeamId) {
                console.error(`Skipping trade row — could not resolve team "${row.rowTeamName}" to a Yahoo team id.`);
                continue;
            }

            const date = parseTradeDate(row.timestampText, Number(args.year));
            if (!date) {
                console.error(`Skipping trade row — could not parse timestamp "${row.timestampText}".`);
                continue;
            }

            const tradeIdentifier = Number(`${args['league-id']}${rowIndex}`);

            for (const block of row.playerBlocks) {
                if (!block.player || !block.subLabel) {
                    console.error(`Skipping unparseable trade player block: ${JSON.stringify(block)}`);
                    continue;
                }
                const destMatch = block.subLabel.match(/traded\s+to\s+(.+)/i);
                if (!destMatch) {
                    console.error(`Skipping trade player block with unrecognized sub-label "${block.subLabel}" for player ${block.player} — expected wording like "Traded to <team>".`);
                    continue;
                }
                const toTeamName = destMatch[1].trim();
                const toTeamId = teamIdByName[toTeamName];
                if (!toTeamId) {
                    console.error(`Skipping trade player block for ${block.player} — could not resolve destination team "${toTeamName}" to a Yahoo team id.`);
                    continue;
                }

                trades.push({
                    player: block.player,
                    fromTeamYahooId: fromTeamId,
                    fromTeamName: row.rowTeamName,
                    toTeamYahooId: toTeamId,
                    toTeamName,
                    date,
                    tradeIdentifier,
                });
            }
        }

        return trades;
    } finally {
        await page.close();
    }
}

// Yahoo's transaction timestamp text has no year ("Sep 13, 12:39 pm") —
// the season year comes from --year instead, matching how the OAuth API
// path derives the trade date from a Unix timestamp that's implicitly
// within the season year. Only the date (not the time) is used downstream
// (handle_scraped_trades() -> lookup_week()), mirroring handle_trades()'s
// own `date('Y-m-d', $timestamp)` truncation.
const TRADE_MONTHS = {
    jan: '01', feb: '02', mar: '03', apr: '04', may: '05', jun: '06',
    jul: '07', aug: '08', sep: '09', oct: '10', nov: '11', dec: '12',
};

function parseTradeDate(timestampText, year) {
    const match = /^([A-Za-z]{3})[a-z]*\s+(\d{1,2})/.exec(timestampText.trim());
    if (!match) {
        return null;
    }
    const month = TRADE_MONTHS[match[1].toLowerCase()];
    if (!month) {
        return null;
    }
    const day = String(match[2]).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// The default rosters URL (`/f1/<leagueId>/<manager>?week=<week>`, already
// in lib/sectionUrls.js) loads the manager's own team page for that week —
// unlike matchups/trades, this was NOT a wrong first guess: confirmed via a
// real inspect.js capture (league 18261, manager 1, week 1 — see
// scraper/inspection-output/rosters-1789495713698/page.html) that Yahoo
// renders this exact URL as a full box score, complete with the page's own
// "Week 1" nav confirming the requested week and a caption reading "...
// roster for week 1."
//
// The page contains up to three per-position-group tables inside
// `#team-roster` (`#statTable0`/`data-pos-type="O"` offense,
// `#statTable1`/`"K"` kickers, `#statTable2`/`"DT"` defense/special teams —
// discovered by selector rather than assumed ids/order, since which ones
// exist depends on what's actually rostered), each with one row per
// ROSTERED player — starters AND bench AND IR all appear in the same three
// tables, distinguished only by the "Pos" column's `data-pos` attribute
// (e.g. "QB"/"RB"/"W/R/T"/"Q/W/R/T"/"BN"/"IR"/"K"/"DEF" — this is the
// roster_spot, matching `selected_position` from the OAuth API).
//
// Critically, each table's real per-stat-category breakdown IS present —
// this was the single biggest open risk in the whole project, and it
// resolved favorably. Every <th> in a table's second header row carries a
// `title` attribute naming the exact stat (title="Passing Yards",
// title="Sack", title="Field Goals Made", etc.) that lines up 1:1, after
// expanding any `colspan` (the header's "Action" <th> spans 2 <td>s), with
// each row's <td>s. STAT_TITLE_TO_KEY below maps every one of those titles
// that's relevant to the same statIds map get_player_stats() uses in
// yahooApiRequest.php. None of this section's captured network JSON
// responses were Yahoo's own fantasy endpoints (same ad-tech-only finding
// as team_names/trades), so this is DOM-scraped, not JSON-intercepted.
//
// This mapping is NOT just plausible-looking — it was sanity-checked by
// reverse-engineering the league's own (0.5-PPR) scoring formula from the
// numbers themselves: pass_yds*0.04 + pass_td*4 + int*-2 + rush_yds*0.1 +
// rush_td*6 + rec*0.5 + rec_yds*0.1 reproduces the exact "Fan Pts" total
// Yahoo displays for 3 different offensive players in the real capture
// (Justin Herbert 13.26, Chase Brown 16.30, Rhamondre Stevenson 12.00),
// using nothing but the per-category numbers this extractor reads. That
// would not happen by coincidence if the column-to-stat mapping were wrong.
const STAT_TITLE_TO_KEY = {
    'Passing Yards': 'pass_yds',
    'Passing Touchdowns': 'pass_tds',
    'Interceptions': 'ints',
    'Rushing Yards': 'rush_yds',
    'Rushing Touchdowns': 'rush_tds',
    'Receptions': 'receptions',
    'Receiving Yards': 'rec_yds',
    'Receiving Touchdowns': 'rec_tds',
    'Fumbles Lost': 'fumbles',
    'Point After Attempt Made': 'pat_made',
    'Field Goals Total Yards': 'fg_yards',
    'Field Goals Made': 'fg_made',
    'Interception': 'def_int',
    'Fumble Recovery': 'def_fum',
    'Sack': 'def_sacks',
};
const POINTS_TITLE = 'Fantasy Points';

// Yahoo's roster page shows a team defense under its NICKNAME (e.g.
// "Eagles"), but the OAuth API — and therefore every existing `rosters` row
// with position='DEF' — stores just the CITY name ("Philadelphia"), or for
// the two-team LA/NY markets, the full disambiguated name ("Los Angeles
// Rams", "New York Giants") — though the API path itself is inconsistent
// in the LA market (2025 has 4 rows stored under the bare, ambiguous
// "Los Angeles" alongside the fuller "Los Angeles Rams"/"Los Angeles
// Chargers" rows), so this mapping picks the better convention rather
// than guaranteeing it matches every historical row. Without this
// translation, the same
// defense would get a SECOND, different `rosters` row every time the
// scrape path ran (the row's uniqueness key includes `player`) instead of
// updating the one the API path already wrote. Built from only the
// CURRENT (2026) 32 team abbreviations — this scraper only ever targets
// the current season (see the design doc's "Open risks"), so no
// legacy-relocation codes (OAK/SD/STL) are needed.
const TEAM_DEFENSE_NAMES = {
    ARI: 'Arizona', ATL: 'Atlanta', BAL: 'Baltimore', BUF: 'Buffalo',
    CAR: 'Carolina', CHI: 'Chicago', CIN: 'Cincinnati', CLE: 'Cleveland',
    DAL: 'Dallas', DEN: 'Denver', DET: 'Detroit', GB: 'Green Bay',
    HOU: 'Houston', IND: 'Indianapolis', JAX: 'Jacksonville', KC: 'Kansas City',
    LAC: 'Los Angeles Chargers', LAR: 'Los Angeles Rams', LV: 'Las Vegas',
    MIA: 'Miami', MIN: 'Minnesota', NE: 'New England', NO: 'New Orleans',
    NYG: 'New York Giants', NYJ: 'New York Jets', PHI: 'Philadelphia',
    PIT: 'Pittsburgh', SEA: 'Seattle', SF: 'San Francisco', TB: 'Tampa Bay',
    TEN: 'Tennessee', WAS: 'Washington',
};

async function extractRosters(args, context) {
    if (!args['league-id']) {
        throw new Error('Missing required --league-id=<id> argument.');
    }
    if (!args.manager) {
        throw new Error('Missing required --manager=<yahoo team id> argument.');
    }

    const weekList = String(args.weeks || args.week || '')
        .split(',')
        .map((w) => w.trim())
        .filter(Boolean);
    if (weekList.length === 0) {
        throw new Error('Missing required --weeks=<comma-separated weeks> (or --week=<N>) argument.');
    }

    const players = [];
    for (const week of weekList) {
        const weekPlayers = await extractRosterForWeek(args, context, week);
        players.push(...weekPlayers);
    }
    return players;
}

async function extractRosterForWeek(args, context, week) {
    const url = resolveUrl({ ...args, week });
    const page = await context.newPage();
    try {
        await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });

        if (page.url().includes('login.yahoo.com')) {
            throw new Error(
                `Redirected to Yahoo login while loading manager ${args.manager}'s week ${week} roster page — ` +
                'the saved session has expired. Re-run to trigger a fresh login.'
            );
        }

        await page.waitForSelector('#team-roster', { timeout: 15000 });

        const rawRows = await page.$$eval(
            '#team-roster table[id^="statTable"]',
            (tables, { statTitleMap, pointsTitle }) => {
                const rows = [];
                for (const table of tables) {
                    const headerRows = table.querySelectorAll('thead tr');
                    if (headerRows.length === 0) {
                        continue;
                    }
                    // The last header row is the one with per-column `title`
                    // attributes (the first is just group labels like
                    // "Passing"/"Rushing" spanning multiple columns).
                    const headerRow = headerRows[headerRows.length - 1];
                    const titles = [];
                    headerRow.querySelectorAll('th').forEach((th) => {
                        const title = th.getAttribute('title') || '';
                        const span = parseInt(th.getAttribute('colspan') || '1', 10);
                        for (let i = 0; i < span; i++) {
                            titles.push(title);
                        }
                    });

                    table.querySelectorAll('tbody tr').forEach((tr) => {
                        const posLabel = tr.querySelector('.pos-label');
                        const rosterSpot = posLabel ? posLabel.getAttribute('data-pos') : null;
                        const nameEl = tr.querySelector('td.player a.name');
                        const playerName = nameEl ? nameEl.textContent.trim() : null;
                        // Scoped to `.D-b .Fz-xxs` specifically, NOT just any
                        // `.Fz-xxs` in the cell — a player with an injury
                        // designation (Doubtful/Questionable/IR-Return/etc.)
                        // gets an EARLIER sibling span also classed
                        // `Fz-xxs` (`<span class="... F-injury Fz-xxs">D</span>`)
                        // holding just the injury abbreviation ("D", "IR-R"),
                        // which a bare `.Fz-xxs` selector would match first
                        // instead of the real "TEAM - POS" span. Confirmed via
                        // a real capture: Sam Darnold (Doubtful) and Jordyn
                        // Tyson (IR-Return) both hit this — their injury span
                        // is a sibling of `.D-b`, not nested inside it.
                        const teamPosEl = tr.querySelector('td.player .D-b .Fz-xxs');
                        const teamPosText = teamPosEl ? teamPosEl.textContent.trim() : null;

                        const values = [];
                        tr.querySelectorAll('td').forEach((td) => {
                            const span = parseInt(td.getAttribute('colspan') || '1', 10);
                            const text = td.textContent.trim();
                            for (let i = 0; i < span; i++) {
                                values.push(text);
                            }
                        });

                        const stats = {};
                        let points = null;
                        const len = Math.min(titles.length, values.length);
                        for (let i = 0; i < len; i++) {
                            const title = titles[i];
                            if (!title) {
                                continue;
                            }
                            const raw = values[i];
                            const isBlank = raw === '' || raw === '-' || raw === '—';
                            if (title === pointsTitle) {
                                points = isBlank ? null : Number(raw);
                            } else if (statTitleMap[title]) {
                                stats[statTitleMap[title]] = isBlank ? null : Number(raw.replace(/,/g, ''));
                            }
                        }

                        rows.push({
                            rosterSpot,
                            playerName,
                            teamPosText,
                            points,
                            stats,
                            titleCount: titles.length,
                            valueCount: values.length,
                        });
                    });
                }
                return rows;
            },
            { statTitleMap: STAT_TITLE_TO_KEY, pointsTitle: POINTS_TITLE }
        );

        const players = [];
        for (const row of rawRows) {
            if (row.titleCount !== row.valueCount) {
                console.error(
                    `Warning: header/column count mismatch (${row.titleCount} vs ${row.valueCount}) for a roster row ` +
                    `(manager ${args.manager}, week ${week}) — zipping on the shorter length, some trailing columns may be dropped.`
                );
            }
            if (!row.rosterSpot || !row.playerName || !row.teamPosText) {
                console.error(`Skipping unparseable roster row for manager ${args.manager} week ${week}: ${JSON.stringify(row)}`);
                continue;
            }
            const match = /^(.+?)\s-\s(.+)$/.exec(row.teamPosText);
            if (!match) {
                console.error(`Skipping roster row for ${row.playerName} — could not parse team/position text "${row.teamPosText}".`);
                continue;
            }
            const team = match[1].trim().toUpperCase();
            const pos = match[2].trim();

            let playerName = row.playerName;
            if (pos === 'DEF') {
                if (!TEAM_DEFENSE_NAMES[team]) {
                    console.error(`Skipping DEF row for team "${team}" — no known city-name mapping (unexpected team abbreviation).`);
                    continue;
                }
                playerName = TEAM_DEFENSE_NAMES[team];
            }

            const cleanStats = {};
            for (const [key, value] of Object.entries(row.stats)) {
                if (value !== null && !Number.isFinite(value)) {
                    console.error(`Unexpected non-numeric value for stat "${key}" on ${playerName} (manager ${args.manager}, week ${week}) — treating as null.`);
                    cleanStats[key] = null;
                } else {
                    cleanStats[key] = value;
                }
            }

            players.push({
                week: Number(week),
                yahooTeamId: Number(args.manager),
                player: playerName,
                position: pos,
                team,
                rosterSpot: row.rosterSpot,
                points: row.points !== null && Number.isFinite(row.points) ? row.points : 0,
                stats: cleanStats,
            });
        }
        return players;
    } finally {
        await page.close();
    }
}

const extractors = {
    team_names: extractTeamNames,
    matchups: extractMatchups,
    trades: extractTrades,
    rosters: extractRosters,
};

async function main() {
    const args = parseArgs(process.argv.slice(2));
    if (!args.section) {
        throw new Error('Missing required --section=<name> argument.');
    }
    const extractor = getExtractor(extractors, args.section);

    const browser = await chromium.launch({ headless: true });
    try {
        const context = await getAuthenticatedContext(browser);
        try {
            const result = await extractor(args, context);
            process.stdout.write(JSON.stringify(result));
        } finally {
            await context.close();
        }
    } finally {
        await browser.close();
    }
}

main().catch((err) => {
    console.error(err.message);
    process.exitCode = 1;
});
