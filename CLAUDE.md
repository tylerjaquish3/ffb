# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Suntown Fantasy Football League website — a plain PHP site displaying historical stats for a 10-manager league. Data is stored in SQLite and fetched from the Yahoo Fantasy Sports API. The background jobs that recompute those stats now run from the sibling `suntown/` Laravel project (see "Sibling project" below), not from this repo.

**Managers (in order):** Tyler, AJ, Gavin, Matt, Cameron, Andy, Everett, Justin, Cole, Ben

## Commands

This repo is now plain PHP only — there is no Laravel project or `vendor/bin/phpunit` here. The background jobs that used to live in a `fun-facts/` Laravel subproject were merged into `suntown/` (see "Sibling project" below); run them from there:
```bash
cd ../suntown
php artisan funFacts        # Recalculate and update all manager fun facts
php artisan gameTimes       # Parse game CSVs and update rosters + game_slot
php artisan weekly:records  # Update weekly records and log them
```

## Architecture

### Main PHP site (repo root)

Plain PHP files served directly by Apache. Each page (`index.php`, `currentSeason.php`, `records.php`, etc.) includes:
- `connections.php` — opens SQLite connections and Yahoo API credentials
- `functions.php` — monolithic 4,400+ line utility file containing all data-fetching, stat calculations, and HTML-rendering helpers
- `header.php` / `sidebar.php` / `footer.php` — shared layout templates

All database queries run against `database/ffb.sqlite`.

### Database

Single SQLite file: `database/ffb.sqlite` (always lives at `/database/ffb.sqlite` relative to the repo root). Connection setup is in `connections.php` (root) and, for the background jobs, `suntown/.env`'s `FFB_DATABASE`.

Key table groups:
- **League:** `managers`, `rosters`, `regular_season_matchups`, `playoff_matchups`, `finishes`
- **Fun facts:** `fun_facts`, `manager_fun_facts` (pre-computed, rebuilt by `php artisan funFacts`)
- **Historical:** `records`, `records_log`, `streaks`
- **Player/NFL:** `players`, `nfl_teams`, `drafts`
- **Schedule:** `game_times`, `game_slots`

### Sibling project — `suntown/`

`suntown/` is a separate, self-contained Laravel app with its own git repo and its own `CLAUDE.md` — it just happens to live inside this repo's working tree at `ffb/suntown`. It's primarily the live current-season app (rosters, weekly lineups, matchups, trades, waivers) for the same 10 managers, and is one of three related-but-separate projects on this machine, alongside `../draft` (the live snake-draft tool at `/Users/tyler.jaquish/sites/draft`).

**All background data processing for this repo now lives there too.** The `fun-facts/` Laravel subproject that used to handle this repo's historical stats (fun facts, weekly records, game times, optimal-lineup calculations, draft transfer from `../draft`) was merged into `suntown/` and deleted from this repo. In `suntown/`:
- `App\Models\History\*`, `App\Jobs\History\*`, `App\Console\Commands\History\*` — the migrated models/jobs/commands, unchanged in behavior from the old fun-facts app
- A dedicated `ffb` Eloquent connection (`config/database.php`, `FFB_DATABASE` env var) points at **this repo's** `database/ffb.sqlite`, read-write — the one place suntown writes to a sibling project's database rather than just reading from one
- `storage/app/private/games/*.csv` — the per-season NFL schedule CSVs, moved from this repo's old `fun-facts/storage/app/games/`; suntown's own `nfl-schedule:import` reads them too, into its own separate `NflGame` table

See `suntown/CLAUDE.md` for full architecture, models, and conventions. To run any of this repo's historical-stats commands (`funFacts`, `gameTimes`, `weekly:records`, `calculateOptimal`, `rosterTeams`, `transferDraft`, `updateSchedule`, `importGames`), `cd suntown` and run them there — they still read/write this repo's `database/ffb.sqlite`, just from a different working directory.

### Deployment

GitHub Actions (`.github/workflows/main.yml`) auto-deploys to production via FTP on every push to `master`.

### Notable conventions
- The schedule moved to 18 weeks in 2021. Season-length logic must account for this.
- NFL team relocations: Rams (LA, 2016), Chargers (LA, 2017), Raiders (LV, 2020).
- Player aliases exist in the DB to handle name variations — queries matching player names should join/use aliases.
- `functions.php` is intentionally monolithic; new shared helpers go there until a refactor separates pages into folders.
- Bootstrap version in use requires `col-sm-*` grid classes (e.g. `col-sm-12`, `col-sm-6`) — plain `col-*` classes (e.g. `col-12`) do not apply correctly.
- `regular_season_matchups` stores **one row per manager per matchup** (each matchup appears twice — once with each manager as `manager1`). To get a manager's full stats without double-counting, query only the `manager1` side. `playoff_matchups` is **not** duplicated — each matchup appears once.
