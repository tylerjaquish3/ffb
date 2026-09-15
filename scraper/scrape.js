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

const extractors = {
    team_names: extractTeamNames,
    matchups: extractMatchups,
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
