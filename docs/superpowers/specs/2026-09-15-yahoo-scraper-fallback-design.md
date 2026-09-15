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
| `yahoo_ids` | `handle_managers` | `season_managers.yahoo_id` | `handle_scraped_managers` |
| `team_names` | `handle_teams` | `team_names` (name, moves, trades) | `handle_scraped_teams` |
| `matchups` | `handle_team_matchups` | `regular_season_matchups` (scores, projected, winner/loser) + triggers `updateStandingsForWeek` | `handle_scraped_matchups` |
| `rosters` | `handle_team_rosters` + `get_player_stats` | `rosters` (player/position/team/points) + `stats` (full per-category breakdown — pass_yds, pass_tds, ints, rush_yds, rush_tds, receptions, rec_yds, rec_tds, fumbles, pat_made, fg_yards, fg_made, def_int, def_fum, def_sacks) + optimal lineup back onto `regular_season_matchups` | `handle_scraped_rosters` |
| `trades` | `handle_trades` | `trades` (player, from/to manager, week, trade id) | `handle_scraped_trades` |

`rosters` is the highest-risk section: it requires the same per-stat-category
breakdown as the API provides, not just a fantasy-points total, so
whichever Yahoo page/endpoint is used for it must expose that same
granularity (Yahoo's own box-score view renders this same table, so it's
expected to be available — to be confirmed via `inspect.js`).

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

- Yahoo may detect and block scripted logins over time (rate limiting,
  anomaly detection, forced 2FA). The manual-login fallback described
  under "Credentials & session" is the mitigation, not yet built.
- Yahoo's website structure/internal endpoints can change without
  notice, same risk profile as any scraper. No versioning/monitoring
  strategy beyond manual re-inspection when a section starts failing.
- `rosters`' per-stat-category requirement is the least certain to be
  scrapeable at the same fidelity as the API — confirmed or refuted only
  once `inspect.js` output for that section is reviewed.
