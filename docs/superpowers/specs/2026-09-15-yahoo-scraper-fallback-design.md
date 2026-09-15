# Yahoo Web-Scrape Fallback — Design

## Motivation

The admin page's "Yahoo API" tab requires the user's own browser to visit
`api.login.yahoo.com` to retrieve an OAuth auth code (`yahooApi.php`'s
"Verify" link). On the user's Zscaler-monitored network, this step gets
blocked, making the existing Yahoo Fantasy Sports API integration
unusable from that network. This spec adds a second path on the same
admin tab that bypasses OAuth entirely by driving a real logged-in
browser session against Yahoo's website with Playwright, extracting the
same data the API currently provides, and writing it into the same
database tables via the same downstream helpers.

This is additive: the existing OAuth/API path is untouched and remains
the primary path. The scrape path is a fallback, selected explicitly by
the user per run.

## Scope

Covers all 5 sections the API path currently handles: `yahoo_ids`,
`team_names`, `matchups`, `rosters`, `trades`. Runs local-only — against
the user's local dev environment (Valet + local `database/ffb.sqlite`),
never against production. Production keeps using the OAuth/API path
exclusively (or the user runs the local scraper and syncs the resulting
local DB to production via the existing `sync-db.sh`/`download-db.php`
flow — out of scope here, no changes to that sync mechanism).

## Non-goals

- No changes to the existing OAuth/API path (`yahooApi.php`,
  `yahooApiRequest.php`, `yahooApiToken.php`, `yahooCallback.php`,
  `yahooSharedFunctions.php`) beyond what's needed to add a sibling UI
  toggle in `admin.php`.
- No production hosting changes. Playwright/Node do not need to run
  anywhere but the user's local machine.
- No general-purpose scraping framework — this is Yahoo-Fantasy-specific
  and single-league-specific, consistent with how the rest of this
  codebase is written for exactly one league.

## Architecture

New `scraper/` directory at the repo root, a standalone Node project
(own `package.json`, Playwright as a dependency, its own
`node_modules`, gitignored — unrelated to `suntown`'s Node setup, which
is Vite/Tailwind for its own frontend).

```
scraper/
  package.json
  login.js            # shared login/session module
  inspect.js           # discovery tool (see "Discovery workflow")
  scrape.js            # the real extractor, built section by section
  .auth/                # gitignored — saved Playwright storage state
  inspection-output/    # gitignored — dumped HTML/network JSON from inspect.js runs
```

### Credentials & session

- `YAHOO_USERNAME` / `YAHOO_PASSWORD` live in `ffb/.env.local` (same file
  as `DB_SYNC_TOKEN`), loaded by the Node scripts via `dotenv`.
  `.env.local` is gitignored (see "Housekeeping" below — it was
  previously tracked due to a `.gitignore` bug, now fixed).
- `login.js` logs into Yahoo with those credentials using Playwright,
  then saves the resulting storage state (cookies) to
  `scraper/.auth/yahoo-state.json`. Subsequent runs load that file and
  skip login entirely while the session remains valid. If Yahoo rejects
  the saved session (redirect to a login page), `login.js` re-runs the
  full credential login and re-saves state.
- If Yahoo starts throwing a captcha/anomaly challenge at the scripted
  login on a regular basis, the fallback is to switch `login.js` to a
  non-headless one-time manual login (open a real window, the user logs
  in themselves, state gets saved the same way) — noted here as a known
  escape hatch, not built until/unless it's actually needed.

### Discovery workflow

Yahoo Fantasy's site is a single-page app. Two extraction strategies are
possible per page: parse the rendered HTML, or intercept the JSON the
page's own JS pulls from Yahoo's internal endpoints. The latter is
preferred where available (more structured, less coupled to visual
markup) but which endpoints exist, and what they return, is unknown
until inspected on a real logged-in session — which requires the user,
since the agent does not have Yahoo credentials or browser access.

`inspect.js` is the tool for this: run locally with flags for section,
year, week(s), and manager, e.g.:

```
node scraper/inspect.js --section=rosters --year=2026 --week=3 --manager=<yahoo_team_id>
```

It logs in (reusing `login.js`), navigates to the Yahoo page
corresponding to that section/week/manager, and writes to
`scraper/inspection-output/<section>-<timestamp>/`:
- `page.html` — full rendered DOM at time of capture
- `network/*.json` — every intercepted XHR/fetch response body from a
  `yahoo.com` domain during that page load

The user runs this once per section (and, if a section's page layout
varies meaningfully by context — e.g. playoff vs. regular season
matchup weeks — potentially more than once), then hands the output
files over. That output is what the real per-section extraction logic
in `scrape.js` gets built from — this design does not attempt to
predict Yahoo's page structure in advance.

### Extractor

`scrape.js` takes the same shape of input the admin UI already collects
(section, year, weeks, managers) and, per section, produces a single
normalized JSON blob on stdout — one shape per section, chosen to map
directly onto the DB columns the existing `handle_*` functions in
`yahooApiRequest.php` already write (see "Data mapping" below), rather
than mimicking the Yahoo API's own nested response shape. All
Yahoo-page-specific parsing (selectors, endpoint shapes, pagination)
lives here, discovered incrementally per section using `inspect.js`
output. Errors for a single item (a player row, a matchup, a trade) are
caught and logged; extraction continues rather than aborting the whole
run, matching the existing API path's "skip and continue" behavior
noted in `README.md`'s Bugs section.

Invocation: PHP calls this via `proc_open`, e.g.
`node scraper/scrape.js --section=rosters --year=2026 --weeks=3 --manager=<yahoo_team_id>`,
capturing stdout as the JSON payload and stderr for error text and a
non-zero exit code as failure.

## Data mapping

| Section | Existing API handler | DB writes | Scraped JSON → PHP handler |
|---|---|---|---|
| `yahoo_ids` | `handle_managers` | `season_managers.yahoo_id` | **Not implemented — confirmed permanently unscrapeable.** Investigated 2026-09-15: the manager's Yahoo nickname (the only signal `handle_managers` can bootstrap a manager mapping from) renders as a literal `--hidden--` placeholder everywhere on the website — the standings page, and all 10 individual team pages, including the scraping account's own team. That last point rules out a per-account privacy setting: Yahoo's website no longer sends this value to the browser at all, for anyone. Stays API-only; see "Open risks" below for possible future workarounds. |
| `team_names` | `handle_teams` | `team_names` (name, **moves only** — see note) | `handle_scraped_team_names` (done) — the standings page has no trades count anywhere (confirmed by full-page search of live-captured HTML); the write path omits `trades` from `updateOrCreate()` entirely so it never overwrites an existing value, rather than writing 0. |
| `matchups` | `handle_team_matchups` | `regular_season_matchups` (scores, projected, winner/loser) + triggers `updateStandingsForWeek` | `handle_scraped_matchups` (done) — the assumed `/scoreboard?week=` URL 404s for real; the actual page is the league home page's "Matchups" module (`?matchup_week=<N>&module=matchups&lhst=matchups`), which returns all 5 matchups for a week in one load. Projected points ARE available even for a completed week (confirmed against the individual matchup page's "Orig Proj" label) — contrary to this doc's earlier speculation, so `projected` is never nulled out here the way `team_names`' `trades` is. |
| `rosters` | `handle_team_rosters` + `get_player_stats` | `rosters` (player/position/team/points) + `stats` (full per-category breakdown — pass_yds, pass_tds, ints, rush_yds, rush_tds, receptions, rec_yds, rec_tds, fumbles, pat_made, fg_yards, fg_made, def_int, def_fum, def_sacks) + optimal lineup back onto `regular_season_matchups` | `handle_scraped_rosters` (done) — the originally-assumed `/f1/<leagueId>/<manager>?week=<week>` URL turned out to be correct (unlike matchups/trades' first guesses): it's the manager's own team page, rendered as a full box score. The full per-stat-category breakdown — the single biggest open risk in this whole project — IS present in the rendered DOM: up to three per-position-group tables (offense/kickers/defense) inside `#team-roster`, each row (starters, bench, AND IR all mixed together, distinguished by the "Pos" column's `data-pos`) carrying every `<th title="...">`-labeled stat column needed. Confirmed 2026-09-15 against a real capture (league 18261, all 10 managers, week 1) and sanity-checked by reverse-engineering the league's 0.5-PPR scoring formula from 3 players' numbers and getting an exact match to Yahoo's own displayed "Fan Pts" total. Two real parsing gaps found and fixed: (1) a player with an injury designation (Doubtful/IR-Return/etc.) gets an extra `.Fz-xxs`-classed span for the injury badge itself, which a naive selector picks up instead of the real "TEAM - POS" text — fixed by scoping to `.D-b .Fz-xxs`; (2) Yahoo renders a team defense under its nickname ("Eagles"), not the city name ("Philadelphia") the OAuth API and every existing `rosters` DEF row use — translated via a hardcoded current-season abbreviation→city-name map so the scrape path writes to the same row the API path would. |
| `trades` | `handle_trades` | `trades` (player, from/to manager, week, trade id) | `handle_scraped_trades` (done) — the assumed `?scope=all&type=trade` URL loads fine (no 404) but those params aren't real; Yahoo silently ignores them and renders the default "All Transactions" tab instead. The real filter param, read off the page's own "Trades" tab link, is `transactionsfilter=trade`. With that applied, the current 2026 league (18261) genuinely has zero trades so far this season — confirmed both by the live page's own "No recent transactions" empty state and by the `trades` DB table already having zero 2026 rows — so `[]` is a correct, verified result, not a gap. **Caveat**: no real trade row exists anywhere reachable (this account is in only the one league, and past seasons of it aren't reachable with the saved session — see "Open risks"), so the non-empty-row parsing logic (which player/team/date a real trade row would contain) is a best-effort structural inference from the confirmed add/drop row layout on the same page, not a verified pattern. Team identity is resolved via the page's own team-picker flyout (name → Yahoo team id map, confirmed present regardless of trade count) rather than assuming a trade row itself links to both teams. `trade_identifier` is necessarily synthetic (`<leagueId><row index>`) since nothing on the rendered page exposes Yahoo's real transaction id — harmless for `firstOrCreate()` correctness since that id isn't part of its uniqueness key. The admin UI shows an extra caution banner the first time this section ever writes 1+ rows, given the unverified parsing. |

All 5 sections are now implemented (or, for `yahoo_ids`, confirmed
unscrapeable and documented above). `rosters` was the highest-risk section —
it required the same per-stat-category breakdown as the API provides, not
just a fantasy-points total — and it resolved favorably: Yahoo's own
box-score view renders that same granularity directly in the DOM.

**Pre-existing data-integrity gap found while verifying `rosters` (not
caused by this work, not fixed beyond the one table it touches):** the
`stats` table already had ~113 rows (across all years, in the untouched
DB) whose `roster_id` points at a `rosters` row that no longer exists —
`rosters.id` is a plain `INTEGER PRIMARY KEY` (no `AUTOINCREMENT`), so a
brand-new roster row's id is just the next free id, and it can land on one
of those stale orphaned ids purely by coincidence. This bit a real 2026
week 1 IR player during verification (a long-dead row's leftover defensive
stats showed up attached to a new WR on IR). `handle_scraped_rosters()`
defensively deletes anything already sitting under a roster_id it's about
to skip (IR players never get a `stats` row), so its own writes are
internally clean — but `handle_team_rosters()` in the OAuth API path has
the identical exposure and was NOT touched (out of scope here). A full
`DELETE FROM stats WHERE roster_id NOT IN (SELECT id FROM rosters)` cleanup
would remove the risk permanently but touches historical data across all
years, so it was left for the user to decide on rather than done here.

**Also required to make `rosters`' optimal-lineup step meaningful:** the
`season_positions` table had zero rows for year 2026 (a separate,
unrelated pre-existing gap — this table is also read by several
non-scraper features, e.g. `constitution.php`), which would make
`calculateOptimalForManager()` unconditionally return 0.0 regardless of
input, for either the scrape or the API path. Seeded by copying 2025's 19
rows forward to 2026 — justified not just by "assume unchanged from last
year" but by the real scraped roster structure itself confirming the same
11 starting slots (QB, 3×WR, 2×RB, TE, FLEX, SUPERFLEX, K, DEF) and 6 bench
slots; the IR slot count (copied as 2) is the one part not independently
confirmed (a manager was observed using 2, which is consistent, but a
single observation can't rule out the league having configured only 1).

New file `yahooScrapeRequest.php` (sibling to `yahooApiRequest.php`)
holds the `handle_scraped_*` functions. These are simpler than their API
counterparts — they loop the already-normalized JSON and call
`updateOrCreate`/`firstOrCreate` directly — and reuse existing helpers
as-is rather than duplicating them: `lookupManager`,
`updateStandingsForWeek`, `calculateOptimalForManager` (from
`yahooSharedFunctions.php`/`yahooApiRequest.php`/`functions.php`).

## Admin UI integration

`admin.php`'s existing "Yahoo API" tab (rendered by `yahooApi.php`) gets
a mode toggle at the top — "Yahoo API" vs. "Web Scrape" — rather than a
new top-level tab, since the Season/Sections/Weeks/Managers inputs are
identical between the two modes.

- **Web Scrape mode**: the Code field and "Verify" link are hidden (no
  OAuth involved at all). Season/Sections/Weeks/Managers behave exactly
  as today, feeding the same client-side validation currently in
  `admin.php` — including the "playoff weeks aren't available" check
  for matchups, which needs to be re-verified for the scrape path rather
  than assumed, since the *website* (unlike the API) does show playoff
  weeks; whether that check should even apply in scrape mode is
  something to confirm during implementation, not now.
- **Submit**: skips the token exchange and POSTs to
  `yahooScrapeRequest.php` instead of `yahooApiRequest.php`. The
  existing per-manager roster staggering logic
  (`makeRosterRequest`/`admin.php:468-512`) is reused as-is against the
  new endpoint.
- **Output**: identical `#output`/`#loading` behavior — visually
  indistinguishable from an API run once submitted.

`yahooScrapeRequest.php` shells out to `scrape.js` per request (mirroring
`yahooApiRequest.php`'s per-section/per-manager request pattern), parses
its stdout JSON, calls the matching `handle_scraped_*` function, and
echoes the same progress/error HTML fragments the JS already expects.

## Error handling

- **Login failure** (bad credentials, or a captcha/anomaly challenge):
  `scrape.js`/`login.js` exit non-zero with a specific stderr message;
  `yahooScrapeRequest.php` surfaces it as the same `alert-danger` block
  the API path already produces on failure.
- **Session expired mid-run**: detected as a redirect to a login page
  where data was expected; reported distinctly from a structural
  parsing failure so the two causes aren't confused.
- **Per-item extraction failure** (a selector/endpoint no longer
  matches after a Yahoo UI change): caught and logged per item, loop
  continues — matches the existing API path's documented
  skip-and-continue behavior.
- **Node process failure** (non-zero exit, non-JSON stdout,
  `proc_open` failure itself): surfaced as a generic request error in
  `#output`, same as any other failed AJAX call today.

## Testing

This repo has no automated test suite (plain PHP, no phpunit here).
Verification is manual: for each section, run the scraper against a
week/section already fetched via the API previously, and diff the
resulting DB rows against the known-good values before trusting the
scraper against new, unverified data.

## Infrastructure notes

- `scraper/` needs its own `npm install` and `npx playwright install
  chromium` (browser binary download) — independent of `suntown`'s
  existing `node_modules` (Vite/Tailwind, unrelated).
- Requires Node.js available on the local machine running Valet/PHP.

## Housekeeping done during this work (already applied, not part of implementation)

- `.gitignore` had a bug: `visit_log.txt` and `.env.local` were merged
  onto one line (`visit_log.txt.env.local`) with no newline between
  them, so neither pattern actually matched. Fixed by splitting them
  into two lines. `.env.local` is now genuinely gitignored.
- `.env.local` was tracked in git despite being meant as a local-only
  secrets file (a previous commit, `638105d`, added it under the buggy
  gitignore). It has been `git rm --cached`'d (staged, not committed)
  so it stops being tracked going forward. The already-committed
  `DB_SYNC_TOKEN` from that prior commit remains in git history — out of
  scope here, but worth rotating that token at some point given it's
  been exposed in history.
- `YAHOO_USERNAME`/`YAHOO_PASSWORD` were briefly placed in
  `suntown/.env` (a separate git repo) before being moved to `ffb`'s
  `.env.local`, where they belong given the scraper lives in this repo.
  `suntown/.env` is gitignored there and was never committed, so no
  exposure occurred.

## Open risks

- The scrape path may only work against the **current** season's league.
  Investigated 2026-09-15 while building `matchups`: navigating to a past
  season's league (year 2025, league id 23237) with the saved session
  redirects straight to `login.yahoo.com`, while the exact same session
  navigates to the current league (18261) fine — confirmed against both the
  base league URL and the matchups URL, so it isn't a URL-pattern bug. This
  looks like Yahoo requiring fresh re-authentication for a non-current
  season that the saved cookie state alone can't satisfy. Not investigated
  further (out of scope for the `matchups` task) — if a past season ever
  needs re-scraping, this will need its own look, possibly via the
  non-headless manual-login escape hatch already noted below.
- Yahoo may detect and block scripted logins over time (rate limiting,
  anomaly detection, forced 2FA). The manual-login fallback described
  under "Credentials & session" is the mitigation, not yet built.
- Yahoo's website structure/internal endpoints can change without
  notice, same risk profile as any scraper. No versioning/monitoring
  strategy beyond manual re-inspection when a section starts failing.
- `rosters`' per-stat-category requirement — the least certain part of
  this whole project at the design stage — is now resolved: the same
  fidelity as the API IS scrapeable (see "Data mapping" above). The
  pre-existing orphaned-`stats`-rows and missing-2026-`season_positions`
  gaps found while verifying it are noted there instead, since they're app
  data-layer issues, not scraper risks.
- `yahoo_ids` cannot be scraped at all (see "Data mapping" above) — the
  manager-nickname field the whole section depends on is masked
  everywhere on the website now. If this is ever needed without the API,
  the two realistic paths are: (1) have the user manually click through
  Yahoo's real invite/manage-members UI to check whether nicknames appear
  there for the commissioner, then `inspect.js` that real navigation
  flow (a guessed direct URL to it redirected to a login/crumb check,
  inconclusive); or (2) skip scraping entirely and add a small one-time
  manual-mapping step — the user (who knows all 10 people) confirms
  which real manager a given `yahooTeamId`/team name belongs to, once
  per season, writing straight to `season_managers.yahoo_id` the same
  way `handle_managers`/`handle_scraped_team_names` already do.
