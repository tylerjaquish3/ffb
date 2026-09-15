# `matchups` Extractor — Implementation Report

## Summary

Implemented the real `matchups` extractor (`scrape.js`'s `extractMatchups`)
and its DB-write handler (`handle_scraped_matchups()` in
`yahooScrapeRequest.php`), following the same pattern as the completed
`team_names` section. Verified against a real, fully-completed week of the
**current** (2026) season, since the local dev DB had **zero** rows for
2026 in `regular_season_matchups`/`standings` at the start of this task (see
"Deviation from the prescribed verification method" below for why 2025 — the
season with existing ground-truth rows — couldn't be used instead).

## What the real scoreboard page contains

The plan's assumed default URL, `/f1/<leagueId>/scoreboard?week=<N>`, **404s
for real** ("The document you requested was not found") — confirmed against
a live logged-in session, not a guess. This applied to both the current
league (18261) and an older one (23237), so it wasn't a session/permissions
issue with the URL itself.

Investigating the league home page's own links found the real mechanism:
Yahoo's home page embeds a "Matchups" module (`<section id="matchupweek">`)
that already shows **all 5 matchups for a given week in one page load** —
confirmed for both an unplayed week (week 2: "Not started yet", scores all
`0.00`, only the projection shown) and a completed week (week 1: "Final
results", real final scores). The module is paginated by week via
query-param links on the *same* league URL:

```
https://football.fantasysports.yahoo.com/f1/<leagueId>?matchup_week=<N>&module=matchups&lhst=matchups
```

`scraper/lib/sectionUrls.js`'s `matchups` builder was corrected to this URL
(the old `/scoreboard?...` string is gone). Added a test case for it in
`sectionUrls.test.js`.

No structured JSON network response carries this data (confirmed by
inspecting all captured `network/*.json` files — same situation as
`team_names`: only ad-tech/analytics JSON, no Yahoo fantasy data endpoint).
So, like `team_names`, this extractor scrapes the rendered DOM.

### DOM structure (per matchup)

```html
<li data-target="/f1/18261/matchup?week=1&mid1=1&mid2=7">
  ...
  <div class="Grid-u-6-13 Py-med">  <!-- side 1 (mid1) -->
    <a class="F-link" href="https://football.fantasysports.yahoo.com/f1/18261/1">Almost Always Almost Win</a>
    ...
    <div class="Fz-lg ">117.66</div>  <div class="F-shade  ">144.44</div>
  </div>
  <div class="Grid-u-6-13 Py-med">  <!-- side 2 (mid2) -->
    <a class="F-link" href="https://football.fantasysports.yahoo.com/f1/18261/7">It's Personal</a>
    ...
    <div class="Fz-lg  Fw-b">183.36</div>  <div class="F-shade  ">139.60</div>
  </div>
</li>
```

- `mid1`/`mid2` on the `<li data-target="...">` are the two sides' real
  Yahoo team ids — read directly from there.
- Each side has exactly two plain-numeric leaf `<div>`s: the actual score
  first, then a second "shaded" number.

### Is `projected` available for a completed week? Yes — confirmed, not assumed

The spec speculated Yahoo might stop showing projected points once a week is
final. That's **not** what happens here. The second (shaded) number on the
module persists after the game and is genuinely the *original* pre-game
projection: the individual matchup/recap page
(`/f1/18261/matchup?week=1&mid1=1&mid2=7`) explicitly labels that exact same
figure **"Orig Proj"**, right next to the identical values (`144.44` /
`139.60`) seen in the module. Cross-checked against a second matchup
(`mid1=6&mid2=8`: `124.23`/`145.45`) — same exact agreement. So this
extractor always returns a real `projected` number for a matchup it
successfully parsed, never a fabricated placeholder and never null.

## The extractor (`scraper/scrape.js`)

`extractMatchups(args, context)` — parses `--weeks=1,2,3` (comma list; falls
back to `--week=N` singular), loops `extractMatchupsForWeek` per week, and
concatenates results. `extractMatchupsForWeek`:
1. Loads `resolveUrl({...args, week})`.
2. Detects session expiry via `page.url().includes('login.yahoo.com')`
   (same pattern as `team_names`) — also documented as the failure mode for
   non-current-season leagues (see below).
3. `page.$$eval('#matchupweek li[data-target*="/matchup?"]', ...)` reads
   each matchup's `mid1`/`mid2` from `data-target`, and per side reads the
   team-name link's href (cross-checked, not just trusted) plus the two
   numeric leaf divs (score, then projected).
4. Matches each side's parsed id to `mid1`/`mid2` **by value**, not by
   left/right DOM order (that ordering held in every row inspected, but
   matching explicitly is more robust and self-documenting).
5. Per-matchup errors (unparseable row, id mismatch) are logged via
   `console.error` and skipped — the loop continues, matching the
   documented skip-and-continue behavior.

Output shape (array, one entry per matchup, both sides included):

```json
{
  "week": 1,
  "team1": { "yahooTeamId": 1, "name": "...", "score": 117.66, "projected": 144.44 },
  "team2": { "yahooTeamId": 7, "name": "...", "score": 183.36, "projected": 139.6 }
}
```

## The handler (`yahooScrapeRequest.php`)

`handle_scraped_matchups(array $matchups, int $year): int` loops the
normalized JSON, resolves both `yahooTeamId`s via `lookupManager()`,
computes winner/loser by score comparison (`>` — same tie-handling quirk as
the existing `handle_team_matchups()`, kept identical rather than "fixed"),
and writes **both** `regular_season_matchups` rows per matchup via
`updateOrCreate()` (each manager as `manager1` once), including
`manager1_projected`/`manager2_projected` since real projected values are
available. Tracks which weeks had a genuinely new matchup (manager1's own
row didn't already exist) and calls `updateStandingsForWeek()` once per such
week — mirroring `handle_team_matchups()`'s trigger condition exactly.
Wired into the section dispatch (`elseif ($section === 'matchups')`).

### A real gap found and fixed along the way

`updateStandingsForWeek()` lived only in `yahooApiRequest.php`, which
`yahooScrapeRequest.php` never includes — calling it from the new handler
hit `Call to undefined function`. Moved the function, verbatim, into
`yahooSharedFunctions.php` (which both `yahooApiRequest.php` and
`yahooScrapeRequest.php` already include), matching the file's existing
precedent of hosting `lookupManager()`/`getSeasonLeagueId()` for exactly
this reason. `yahooApiRequest.php`'s own call site at its original line 364
is unaffected (it gets the function via its existing
`include 'yahooSharedFunctions.php'`).

## Deviation from the prescribed verification method (please read)

The instructions assumed 2026 would already have an already-correct,
completed week sitting in `regular_season_matchups` to diff the scraper's
output against. **It didn't** — `database/ffb.sqlite` had zero rows for
year 2026 in both `regular_season_matchups` and `standings` at the start of
this task (confirmed via read-only query, and confirmed the same in this
worktree's git `HEAD` copy — not an uncommitted-change artifact). This
makes sense: the 2026 season just started (per `CLAUDE.md`'s "Added 2026
draft" as the most recent prior commit) and nobody has run the OAuth/API
fetch for this local dev DB yet this season.

I tried the obvious fallback — test against 2025 (league id 23237), which
**does** have known-good rows for every week through 14. That's blocked for
a real, confirmed reason: navigating to league 23237 with the saved session
redirects straight to `login.yahoo.com`, while the exact same session
navigates to the current league (18261) fine. This isn't a URL-pattern bug
(confirmed by testing both the base league URL and the matchups URL against
23237) — it's Yahoo requiring fresh re-authentication for a non-current
season's league that the saved cookie state alone can't satisfy. **This is a
new, real finding worth carrying into the spec's "Open risks" section**: the
web-scrape path may only ever be usable against the *current* season's
league without building some additional re-auth flow — a real constraint
the OAuth API path doesn't share.

Given that, I used real, live week 1 of the 2026 season (already fully
complete — "Final results" on the page, not "Not started yet") as the test
case instead, and compensated for the missing DB ground truth with:

1. **Two independent Yahoo pages agreeing.** The all-matchups module and the
   separate individual matchup/recap page for two different matchups
   (`mid1=1&mid2=7` and `mid1=6&mid2=8`) return byte-identical scores and
   projected values. This is the closest available substitute for "diff
   against a known-good source" when no prior fetch of this data exists
   anywhere.
2. **Real end-to-end write**, since the DB truly had nothing to lose here:
   - Backed up `database/ffb.sqlite` before touching it.
   - Ran the actual `yahooScrapeRequest.php` end-to-end (via a small PHP
     harness that sets `$_POST` exactly as `admin.php`'s AJAX call would,
     then `require`s the real, unmodified production file — so this
     exercises the real `proc_open` → `scrape.js` → JSON → handler path).
   - First run hit the `updateStandingsForWeek` gap above mid-run — by that
     point all 10 `regular_season_matchups` rows had already been written
     (confirmed), so I restored the pre-test backup and re-ran cleanly after
     the fix, rather than leaving partially-written state around.
   - Clean run wrote 10 `regular_season_matchups` rows (both directions,
     scores/winner/loser/projected all matching the scrape output exactly)
     and 10 `standings` rows, correctly ranked (wins desc, then points desc;
     5 winners at 1-0, 5 losers at 0-1, in the right score order within each
     group).
   - **Idempotency check** (substituting for "feed in already-correct data,
     expect a no-op"): ran the exact same end-to-end request a second time.
     `regular_season_matchups` diffed byte-for-byte identical; `standings`
     also diffed byte-for-byte identical (and, correctly, the second run's
     output shows it did *not* re-trigger `updateStandingsForWeek`, since
     `wasNewMatchup` was false for every row the second time — proving that
     trigger condition itself works, not just the recalculation math).

Final state left in the DB is real, correct week-1 2026 data (not test
fixture noise) — the same kind of side effect the `team_names` task's own
end-to-end test already left behind for that section's real 2026 team names.

## Files changed

- `scraper/lib/sectionUrls.js` — fixed the `matchups` default URL (was a
  404; now the real working query-param URL).
- `scraper/lib/sectionUrls.test.js` — added a test for the corrected
  `matchups` URL.
- `scraper/scrape.js` — added `extractMatchups`/`extractMatchupsForWeek`,
  registered `matchups` in the `extractors` table.
- `yahooScrapeRequest.php` — added `handle_scraped_matchups()`, wired into
  the section dispatch.
- `yahooSharedFunctions.php` — added `updateStandingsForWeek()` (moved from
  `yahooApiRequest.php`, verbatim, so both request files can use it).
- `yahooApiRequest.php` — removed `updateStandingsForWeek()` (now shared),
  left a pointer comment at the old location; its one call site is
  unaffected.
- `database/ffb.sqlite` — real week-1 2026 matchup/standings data written by
  the verification run (see above).

## Concerns

- **New open risk for the spec**: the scrape path may not work at all
  against non-current-season leagues without an additional re-auth step —
  confirmed with league 23237 above. Worth adding to the spec's "Open
  risks" section; out of scope for me to edit the spec file itself here
  beyond flagging it, since this task was scoped to the `matchups`
  extractor specifically. [Update: added a short note — see spec diff.]
- Verification used the current season's real (not synthetic) data because
  that's what was actually available and completed; there was no
  pre-existing "known good" row to diff against for a true apples-to-apples
  repeat of the `team_names` verification method. I'm confident in the
  result (two independent Yahoo pages agree exactly; the DB write is
  internally consistent and idempotent) but flagging this since it's not
  the literal procedure described in the task.
- Tie-handling (`score1 > score2`) mirrors the existing API path's behavior
  exactly (a tie would incorrectly count as a loss for manager1) — not
  fixed, since matching existing behavior was the goal and no tie occurred
  in the tested data.
