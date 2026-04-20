# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Suntown Fantasy Football League website — a PHP/Laravel hybrid app that displays historical and current season stats for a 10-manager league. Data is stored in SQLite and fetched from the Yahoo Fantasy Sports API.

**Managers (in order):** Tyler, AJ, Gavin, Matt, Cameron, Andy, Everett, Justin, Cole, Ben

## Commands

### Laravel (run from `fun-facts/` directory)
```bash
php artisan funFacts        # Recalculate and update all manager fun facts
php artisan gameTimes       # Parse game CSVs from storage/app/games and update rosters + game_slot
php artisan schedule:run    # Run scheduled jobs
```

### Frontend assets (run from `fun-facts/`)
```bash
npm run dev     # Development build
npm run watch   # Watch and rebuild on change
npm run prod    # Production/minified build
```

### Tests (run from `fun-facts/`)
```bash
vendor/bin/phpunit                  # All tests
vendor/bin/phpunit tests/Unit       # Unit tests only
vendor/bin/phpunit tests/Feature    # Feature tests only
vendor/bin/phpunit --filter TestName  # Single test
```

## Architecture

### Two-layer structure

**Layer 1 — Main PHP site (repo root)**
Plain PHP files served directly by Apache. Each page (`index.php`, `currentSeason.php`, `records.php`, etc.) includes:
- `connections.php` — opens SQLite connections and Yahoo API credentials
- `functions.php` — monolithic 4,400+ line utility file containing all data-fetching, stat calculations, and HTML-rendering helpers
- `header.php` / `sidebar.php` / `footer.php` — shared layout templates

All database queries run against `database/ffb.sqlite`.

**Layer 2 — Laravel project (`fun-facts/`)**
Handles background data processing only. No public web routes are used in production. Key pieces:
- `app/Console/Commands/` — Artisan commands that trigger jobs
- `app/Jobs/UpdateFunFactsJob.php` — large job (~133KB) that pre-calculates stats and writes to `fun_facts` / `manager_fun_facts` tables
- `app/Models/` — Eloquent models: `Manager`, `Roster`, `FunFact`, `ManagerFunFact`, `RegularSeasonMatchup`, `PlayoffMatchup`, `Draft`, `Finish`, `TeamName`, `NflTeam`, `RecordLog`

### Database

Single SQLite file: `database/ffb.sqlite`. Connection setup is in `connections.php` (root) and `fun-facts/.env`.

Key table groups:
- **League:** `managers`, `rosters`, `regular_season_matchups`, `playoff_matchups`, `finishes`
- **Fun facts:** `fun_facts`, `manager_fun_facts` (pre-computed, rebuilt by `php artisan funFacts`)
- **Historical:** `records`, `records_log`, `streaks`
- **Player/NFL:** `players`, `nfl_teams`, `drafts`
- **Schedule:** `game_times`, `game_slots`

### Deployment

GitHub Actions (`.github/workflows/main.yml`) auto-deploys to production via FTP on every push to `master`.

### Notable conventions
- The schedule moved to 18 weeks in 2021. Season-length logic must account for this.
- NFL team relocations: Rams (LA, 2016), Chargers (LA, 2017), Raiders (LV, 2020).
- Player aliases exist in the DB to handle name variations — queries matching player names should join/use aliases.
- `functions.php` is intentionally monolithic; new shared helpers go there until a refactor separates pages into folders.
