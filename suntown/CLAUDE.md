# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Suntown FFB — a Laravel 12 fantasy football app for a private, closed league of the same 10 managers every year (Tyler, AJ, Gavin, Matt, Cameron, Andy, Everett, Justin, Cole, Ben; Tyler is commissioner). Backend is PHP 8.4 + Laravel 12 with Breeze (Blade + Tailwind CSS v3), SQLite database. No public registration — the commissioner seeds the 10 accounts directly.

This app is one of three related but separate projects on this machine:
- `../draft` — a Laravel draft-prep tool where the actual live snake draft happens each season. Suntown reads from its SQLite database (players, draft picks, managers) but never writes to it.
- `../ffb` — a long-running plain-PHP site with historical league stats going back years. Suntown owns the NFL schedule CSVs (`storage/app/private/games/`) that both projects' schedule imports read, and — via the `App\Models\History`/`App\Jobs\History`/`App\Console\Commands\History` namespaces below — writes directly to `../ffb`'s `database/ffb.sqlite` over a dedicated `ffb` connection. This is the one place suntown writes into a sibling project's database rather than just reading from one.
- `suntown` (this repo) — the live, current-season app: rosters, weekly lineups, matchups, standings, stat entry.

## Commands

```bash
# Install
composer install
npm install

# Database
php artisan migrate:fresh    # drop + recreate all tables (safe in dev — no production data yet)
php artisan db:seed          # seeds the 10 managers/teams, default roster positions, default stat categories

# Sync from sibling projects (see "External data sync" below)
php artisan players:import              # full NFL player pool from ../draft (excludes soft-deleted/retired)
php artisan draft:import {season}       # draft picks + rosters for a season, from ../draft
php artisan nfl-schedule:import {season} # real NFL game schedule, into suntown's own NflGame table

# Historical-stats commands (merged from ../ffb's old fun-facts app — see "Historical stats" below)
php artisan funFacts                    # recalculate all manager fun facts in ../ffb's database
php artisan gameTimes                   # parse storage/app/private/games/*.csv into ../ffb's rosters.game_time/game_slot
php artisan importGames                 # parse a pasted PFR games table into storage/app/private/games/YYYY.csv
php artisan weekly:records {year?} {week?} # update ../ffb's weekly records/record log
php artisan calculateOptimal {year?} {week?} # store optimal lineup scores in ../ffb's regular_season_matchups
php artisan rosterTeams                 # backfill missing NFL team on ../ffb's rosters (scrapes footballdb.com)
php artisan transferDraft               # pull this year's draft picks from ../draft into ../ffb's draft table
php artisan updateSchedule               # update ../ffb's schedule table

# Dev server
php artisan serve --port=8000
npm run dev     # Vite dev server
npm run build   # production asset build

# Tests
php artisan test
```

## Architecture

### Routes (`routes/web.php`)
All routes require `auth` middleware (Breeze). Public registration is removed — see `routes/auth.php`.

- `/` (`dashboard`) — League page (`LeagueController@index`), tabbed via `?tab=`: **Standings** (default — record table + this week's matchups), **Playoffs** (top-4 bracket projected live off current standings), **Transactions** (league-wide `Transaction` feed), **Settings** (read-only summary of roster positions + stat categories). Also shows a league-wide banner for any trade currently under veto review, regardless of tab.
- `/players` — Players page (`PlayersController@index`): searchable/filterable list of every player in the local pool, season points, fantasy team or Free Agent, `+ Add` link for free agents.
- `/players/{player}/add` (GET/POST) — `AddPlayerController`: add-a-free-agent flow, with a forced drop if the roster is full.
- `/teams/{team}` (GET), `/teams/{team}` (PUT, name only), `/teams/{team}/lineup` (PUT) — `TeamController`: roster + weekly lineup editor + team name edit.
- `/teams/{team}/roster/{player}` (DELETE) — `TeamController@dropPlayer`: voluntarily cut a player loose (only other route to a drop is a forced one — roster full on add, or trade overflow). Sends them to waivers just the same.
- `/teams/{team}/trade/create` (GET) — `TradeController@create`: propose a trade with `{team}`.
- `/trades/{trade}` (GET), plus `/trades` (POST, store), `/trades/{trade}/accept|decline|cancel|veto` (POST), `/trades/{trade}/counter` (GET), `/trades/{trade}/resolve` (GET/POST) — `TradeController`: full trade lifecycle, see "Trades" below.
- `/players/{player}/bid` (GET/POST) — `WaiverController`: blind FAB bid on a waiver-locked player, see "Waivers" below.
- `/matchup` — `MatchupController@mine`: redirects to the current week's matchup for `Auth::user()->team`.
- `/matchups/{matchup}` — `MatchupController`: head-to-head lineup comparison for a scheduled matchup, plus a smack-talk comment thread (`/matchups/{matchup}/comments`, `MatchupCommentController`).
- `/profile` — Breeze account settings.

Commissioner-only, under `/admin` + `commissioner` middleware (`App\Http\Middleware\EnsureCommissioner`, checks `is_commissioner` on the user):
- `roster-positions` — CRUD for roster slot types (QB/RB/WR/.../BENCH), counts, eligible positions.
- `stat-categories` — CRUD for scoring categories and point values.
- `draft-import` — three sync actions against the sibling projects: Sync Player Pool, Sync NFL Schedule, Import Season (draft picks).
- `schedule` — generate a round-robin fantasy matchup schedule, or add/remove matchups manually.
- `stats` — manual weekly stat entry, the only way stat data gets into the app right now (see "Scoring" below).
- `league-settings` — single-row edit form for `LeagueSetting` (trade review days, waiver days, starting FAB budget). See "Trades" and "Waivers" below.

### Key Models
- `User` — has one `Team`; `is_commissioner` boolean gates admin routes.
- `Team` — one per user. `scoreForWeek()`, `recordForSeason()`, `matchupForWeek()`, `lineupForWeek()`, `pendingTrades()`, `fabBudgetRemaining(season)` (starting budget minus won *and* still-pending claims, so a team can't blind-bid more than it has across several simultaneous bids). `Team::standingsOrder(season)` is the shared best-to-worst ranking used by the League page and the waiver tiebreak. `resolvedLineupWeek(season, week)` — a starting lineup carries forward unchanged week to week until a manager explicitly edits and saves; this resolves to whichever earlier week's saved `Lineup` rows are actually in effect for the requested week (nothing is copied/materialized — it's resolved live on every read).
- `Player` — central entity. `position` is always a base NFL position (QB/RB/WR/TE/K/DEF — see `Player::POSITIONS`). `external_id` is the matching id from `../draft`'s `players` table, used for idempotent upserts. `pointsForWeek()`, `pointsForSeason()`, `nflGameForWeek()`, `isEligibleFor(RosterPosition)`.
- `RosterPlayer` — season-long roster ownership (`team_id`, `player_id`, unique on `player_id` — a player can only be on one team). Current ownership only; history of how a player got there lives in `Transaction`, not here.
- `RosterPosition` — commissioner-configurable slot *types* (`code`, `label`, `eligible_positions` JSON array, `slot_count`, `sort_order`). `RosterPosition::BENCH_CODE = 'BN'`; `isBench()`; `RosterPosition::rosterLimit()` sums `slot_count` across all rows — this is the total roster size cap used by the add/drop and trade flows.
- `StatCategory` — commissioner-configurable scoring (`code`, `label`, `points_per_unit`, `base_points`, `eligible_positions` JSON array — which base `Player::POSITIONS` this stat applies to). `pointsFor($value)` = `base_points + value × points_per_unit` — `base_points` defaults to 0 for ordinary per-unit stats; it's only nonzero for a stat that starts from a flat value and decays as the raw stat rises (see "Scoring" below). The Team page uses `eligible_positions` to split the roster into separate Offense/Kickers/Defense tables, each showing only its relevant stat columns.
- `Trade` / `TradeItem` / `TradeVeto` — a trade proposal between two teams. `Trade` has `proposer_team_id`, `recipient_team_id`, `status` (`pending`/`under_review`/`accepted`/`declined`/`cancelled`/`countered`/`vetoed`), optional `parent_trade_id` (self-referencing, set when a trade is itself a counter-offer), `review_ends_at`/`executed_at`. `TradeItem.team_id` is the team **giving up** that player — the other team receives it; `is_forced_drop` marks a roster-limit drop chosen during `resolve()` (dropped outright, not received by anyone). `TradeVeto` is one row per team's veto vote (unique per trade+team). `Trade::execute()` and `Trade::processDueReviews()` hold the actual move-players logic. See "Trades" below.
- `Transaction` — append-only log of every add, drop, and trade/waiver-driven player movement (`type`: `add`/`drop`/`trade`, `team_id`, `player_id`, `season`, plus `counterparty_team_id`/`trade_id` for trades). Powers the League page's Transactions tab, and is also the source of truth for whether a player is currently waiver-locked (see `Player::isOnWaivers()`). Never updated after creation.
- `WaiverClaim` — a blind FAB bid on a waiver-locked player (`team_id`, `player_id`, `season`, `amount`, optional `drop_player_id` — a roster-limit drop chosen up front, used only if the bid wins while the team is full — `status`: `pending`/`won`/`lost`/`cancelled`). `WaiverClaim::processDueWaivers()` resolves every player whose lock has lifted. See "Waivers" below.
- `LeagueSetting` — single-row commissioner settings: `trade_review_days` (0-3), `waiver_days` (0-4), `starting_fab_budget`. Always accessed via `LeagueSetting::current()`, which creates the row with defaults on first use.
- `PlayerWeekStat` — manual per-player-per-week stat entry (`player_id`, `season`, `week`, `stat_category_id`, `value`). Fantasy points (`->points` accessor) are always computed live via `StatCategory::pointsFor()`, summed across categories — never cached.
- `DraftPick` — immutable audit record of a draft import (season, round, pick, team, player, the roster slot they were drafted into).
- `Lineup` — weekly starting lineup. **Bench is implicit**: a `Lineup` row only exists for a player who is *starting* that week (`team_id`, `season`, `week`, `roster_position_id`, `slot_index`, `player_id`). A rostered player with no `Lineup` row for that week is on the bench. `slot_index` distinguishes e.g. RB slot 1 vs RB slot 2. This means an *empty* starting slot (nobody assigned) has no row at all and currently has no explicit UI indicator — see "Known gaps" below.
- `Matchup` — `season`, `week`, `home_team_id`, `away_team_id`. No stored scores; `homeScore()`/`awayScore()` compute live from that week's `Lineup` + stats.
- `NflTeam` — the 32 NFL teams, with real `primary_color`/`secondary_color` (used for the little team-color dots throughout the UI). `gameForWeek()`.
- `NflGame` — real NFL schedule (`season`, `week`, `kickoff_at`, `home_nfl_team_id`, `away_nfl_team_id`). `opponentFor()`, `isHomeFor()`.

### Support
- `App\Support\Season::currentWeek($season)` — the earliest week that hasn't finished playing yet, based on real `NflGame.kickoff_at` times (falls back to the latest scheduled fantasy matchup week, or 1, if the NFL schedule hasn't been synced). Used as the default week on the League and Team pages.
- `App\Console\Commands\Concerns\SyncsPlayersFromDraftSource` — shared trait used by `PlayersImportCommand` and `DraftImportCommand`: `importNflTeams()` and `upsertPlayer()`. This is the one place that maps a `../draft` player row to a local `Player` row (position code, NFL team, soft-delete filtering).

### External data sync
One read-only external source, configured via `.env`:
- `DRAFT_SOURCE_DATABASE` — path to `../draft/database/database.sqlite`, wired as the `draft_source` DB connection in `config/database.php`.

Commands:
- `php artisan players:import` — upserts every **non-soft-deleted** player from `../draft`'s `players` table (regardless of draft status) into the local `players` table, and **deletes** any local player whose `external_id` is no longer active in the source (soft-deleted/retired there). This is what makes the Players page show real free agents, not just drafted ones.
- `php artisan draft:import {season}` — pulls that season's `draft_selections` from `../draft`, scoped to the "Suntown FFB" league's managers (matched to local users by name). **Full rebuild**: wipes all `RosterPlayer`/`DraftPick`/week-1 `Lineup` rows and recreates them from the current picks. Safe to re-run repeatedly while a draft is in progress. Bench-drafted picks get a `DraftPick` row but no `Lineup` row (implicit bench).
- `php artisan nfl-schedule:import {season}` — parses the pro-football-reference-style CSV (`Week,Day,Date,Time,Winner/tie,,Loser/tie`, where a `@` in the blank column means the first team is away) from `storage/app/private/games/{season}.csv` into `NflGame` rows.

### Historical stats (merged from `../ffb`'s old `fun-facts` app)

`../ffb` is a long-running plain-PHP site with league stats going back years, stored in its own `database/ffb.sqlite`. It used to have its own Laravel subproject (`fun-facts/`) just to run background recalculation jobs against that database; that subproject was merged into suntown and deleted. Everything under it now lives under an `App\*\History` namespace here, unchanged in behavior:

- **Connection**: `ffb` (`config/database.php`, `FFB_DATABASE` env var, defaults to `../ffb/database/ffb.sqlite`) — read-write. Every model below sets `protected $connection = 'ffb';`, and every raw `DB::` call in the jobs is explicitly `DB::connection('ffb')->...` (this app's *default* connection is still its own local `database.sqlite` — nothing here silently touches suntown's live tables, and nothing in the live app silently touches `../ffb`'s).
- **Models** (`app/Models/History/`): `Draft`, `Finish`, `FunFact`, `Manager`, `ManagerFunFact`, `NflTeam`, `PlayoffMatchup`, `PlayoffRoster`, `RecordLog`, `RegularSeasonMatchup`, `Roster`, `Schedule`, `SeasonManager`, `SeasonPosition`, `Stat`, `TeamName`. Namespaced separately from this app's own `Manager`-less, `NflTeam`-having live-app models to avoid collisions (e.g. there are two unrelated `NflTeam` models — `App\Models\NflTeam` for suntown's live schedule, `App\Models\History\NflTeam` for `../ffb`'s legacy table).
- **Jobs** (`app/Jobs/History/`): `UpdateFunFacts` (the big one — pre-calculates every fun fact), `UpdateWeeklyRecords`, `FetchGameTimes`, `DownloadGamesCsv`, `CalculateOptimalJob`, `FetchRosterTeams` (scrapes footballdb.com via Guzzle), `TransferDraftResults` (reads `../draft` via the `draft_source` connection, same as `draft:import` above), `UpdateSchedule`.
- **Commands** (`app/Console/Commands/History/`) — see the Commands section above for the full list and signatures.
- Two fun-facts commands were **not** migrated: `positionFix` referenced a `FixRosterPositions` job class that didn't actually exist anywhere in the old codebase (dead/broken), and `runQuery` was a one-off debugging scratchpad, not a real feature.
- The per-season NFL schedule CSVs (`storage/app/private/games/*.csv`) are shared source data for *two* separate imports into *two* separate tables: `gameTimes`/`importGames` here feed `../ffb`'s legacy `rosters`/`game_slot`, while `nfl-schedule:import` (see "External data sync" above) feeds this app's own `NflGame`.

## Scoring

There is currently **no live stats API integration** — this was an explicit open decision left for later. Fantasy points are entirely driven by `PlayerWeekStat` rows the commissioner enters by hand on `/admin/stats`, run through `StatCategory::pointsFor($value)` (`base_points + value × points_per_unit`) and summed. The schema is structured so a future live sync job could populate `PlayerWeekStat` the same way without changing anything downstream (`pointsForWeek`, `scoreForWeek`, `Matchup::homeScore()`, etc. would all just start returning real numbers).

Most categories are plain per-unit stats (`base_points = 0`) — the commissioner enters a raw count (yards, TDs, etc.) and it's multiplied straight through. Two DEF-only categories instead use a **flat base that decays** as the entered value rises, for stats where more-of-the-thing is bad and 0 is the ceiling:
- `def_pts_allowed` ("Pts Allowed") — `16 − 0.625 × points allowed`. 0 PA = 16, 21 PA ≈ 2.9, 35 PA ≈ -5.9.
- `def_yds_allowed` ("Yds Allowed") — `12 − 0.03 × yards allowed`. 0 YA = 12, 300 YA = 3, 400 YA = 0, 500 YA = -3.

For both, the commissioner enters the actual raw value (points/yards the defense gave up that week), not a pre-computed score — same data-entry pattern as every other category. The admin stat-entry page (`/admin/stats`) surfaces a hint for any category with a nonzero `base_points`, and the stat-categories editor (`/admin/stat-categories`) exposes `base_points` as a "Base" field alongside "Points / Unit".

## Roster limit & add/drop

`RosterPosition::rosterLimit()` (sum of every slot's `slot_count`, including bench) is the hard cap on roster size. `AddPlayerController`:
- `create()` — redirects to the waiver bid flow if the player is waiver-locked (see "Waivers" above). Otherwise, if the team's current player count is below the limit, shows a simple confirm; if at/over the limit, shows a required "choose a player to drop" select (with each current roster player's season points, to help decide).
- `store()` — re-validates the same limit check and waiver lock server-side, then in one transaction: removes the dropped player's `RosterPlayer` row and **all** their `Lineup` rows for that team (any season/week), logs both moves to `Transaction`, then creates the new `RosterPlayer` row. A newly added player has no `Lineup` row, so they start out on the bench by construction — no extra code needed.

`TeamController::dropPlayer()` is the only *voluntary*, standalone way to cut a player loose without adding anyone — same `RosterPlayer`/`Lineup` cleanup and `Transaction` log, just without a replacement.

Only the team's own owner (or the commissioner, per the same pattern used on the Team page) can add/drop for a team; the flow always operates on `Auth::user()->team`.

## Design system

Built with the `frontend-design` skill — a deliberate "Friday night scoreboard" identity, not a generic SaaS look. Tokens live in `tailwind.config.js` and `resources/css/app.css`:
- **Colors**: `ink` (#12203A, stadium-night navy — nav/headers), `turf` (#1F5E40, field green — card accents/positive), `gold` (#F2B134, stadium-light amber — CTAs/scores/LED digits), `chalk`/`chalk-white` (warm off-white page/card backgrounds), `endzone` (#B23A2E, errors only).
- **Type**: `font-display` = Bebas Neue (headlines, big scores), `font-sans` = Libre Franklin (body/UI), `font-mono` = JetBrains Mono (every score, record, and stat — tabular figures).
- **Signature motif**: real season/week and scores render as glowing amber "LED" digits in dark chips (`.led-digits`, `<x-week-nav>` component) — the app's actual data doubling as the visual identity. Most visible on the Matchup page's "fight card" VS header.
- **Recurring components**: `.card-panel` (chalk-white card, turf top stripe, subtle bolted corners), `.rank-badge` (jersey-numeral circle), `.record-chip` (dark W-L chip), `.team-dot` (small circle using a real NFL team's `primary_color`).
- Commissioner/admin pages intentionally use the same system but stay visually quiet — the design's "boldness" is spent on the manager-facing pages (League/Team/Matchup), not the utility admin screens.

## Notable conventions

- **Single hardcoded league.** No multi-league support — this app is Suntown FFB and only Suntown FFB.
- **Redraft, not dynasty.** `draft:import` fully rebuilds rosters for the season each time it runs — there's no concept of keeping a roster across seasons independent of a fresh draft import.
- **Implicit bench** (see `Lineup` above) — don't create empty placeholder `Lineup` rows for bench or unfilled slots; a missing row *is* the bench state.
- **Slot-per-player editing.** The Team page lists rostered players, each with a single `<select>` of eligible slots (not slots-with-player-dropdowns) — this makes "the same player in two slots" structurally impossible. Two different players claiming the same slot is caught server-side with a plain-English error naming both players.
- **Matchup row alignment.** Because bench is implicit and teams can have different numbers of starters filled at any moment, the Matchup page aligns rows by the *slot definition* (roster position + slot index), not by array position — see `MatchupController::lineupsBySlot()`.
- **Every roster change is logged.** Adds, drops (including forced drops from add-a-free-agent and trade overflow), and trade-driven acquisitions all create `Transaction` rows — see "Trades" below and the League page's Transactions tab.

## Trades

Any manager can propose a trade from another team's page (`TradeController::create`, gated on having a team of your own and not viewing your own team). The propose form lists both rosters with a checkbox per player; submitting requires at least one player selected on each side (`TradeController::store`).

- **Lifecycle**: `pending` → (recipient responds) → `under_review` → `accepted`, OR → `declined` | `cancelled` | `countered` | `vetoed`. Only the recipient can accept/decline/be countered against; only the proposer can cancel; those checks explicitly exclude the other side even when that side is the commissioner (`TradeController::canRespond()`/`canCancel()`) — otherwise a commissioner-manager could approve their own proposal. Countering (`TradeController::counter`) reopens the propose form from the other side, pre-filled with the same players mirrored, and on submit marks the original `countered` while creating a new `pending` trade with `parent_trade_id` set to it.
- **Roster-limit overflow**: accepting a trade that would push either team over `RosterPosition::rosterLimit()` doesn't move to review yet — it redirects to `/trades/{trade}/resolve`, where each over-limit team must pick exactly enough players to drop (from their post-trade roster, including newly-incoming players); those become `TradeItem` rows with `is_forced_drop = true`. Only once that's settled does the trade enter review.
- **League veto review** (`LeagueSetting::trade_review_days`, 0-3): once accepted, a trade sits `under_review` for that many days — 0 executes immediately. While under review it's visible to the *whole league* (not just the two teams — `TradeController::authorizeParty()` opens up specifically for `under_review`), and any team not party to it gets one veto vote (`POST /trades/{trade}/veto`, unique per trade+team). `Trade::VETO_THRESHOLD` (6, a fixed rule, not a setting) votes kills it outright — it never executes regardless of time left. There's no scheduler/queue, so expiry is resolved lazily: `Trade::processDueReviews()` (executes any `under_review` trade past its `review_ends_at`) is called at the top of `LeagueController::index`, `TeamController::show`, and `TradeController::show`.
- **Notifications**: a team's own page shows a banner for every *pending* trade it's a party to — "X proposed a trade" (recipient) or "Waiting on X to respond" (proposer). Separately, the League page shows a banner for every trade currently `under_review`, league-wide, with a live vote tally and a veto button for eligible teams. There's no trade inbox/history index — resolved trades are only reachable via their permalink.

## Waivers

A dropped player (voluntary `TeamController::dropPlayer`, or a forced drop from add-when-full / trade overflow) doesn't become a plain free agent right away — `Player::isOnWaivers()` derives this live from the `Transaction` log: locked if their most recent `drop` transaction is within `LeagueSetting::waiver_days` (0-4), with **no separate "waiver period" record to keep in sync**. `Player::waiverClearsAt()` returns when the lock lifts (or null if never dropped).

- **Blind FAB bidding**: while locked, `/players/{player}/add` redirects to `/players/{player}/bid` (`WaiverController`) instead of the normal instant-add flow. Every manager starts the season with `LeagueSetting::starting_fab_budget` (default $200); `Team::fabBudgetRemaining()` is that minus every `won` claim and every still-`pending` claim this season, so a team can't overcommit across simultaneous bids. Bids are genuinely blind — a team only ever sees its own claim amount, never anyone else's (`WaiverController::create` scopes the existing-claim lookup to `team_id`).
- **Resolution** (`WaiverClaim::processDueWaivers()`, called lazily wherever waivers/rosters are shown — `PlayersController::index`, `TeamController::show`, `LeagueController::index`): once a player's lock lifts, the highest bid wins; ties are broken by `Team::standingsOrder()` — worse current record wins. If the winning team is at the roster limit, its bid's pre-chosen `drop_player_id` (picked at bid time, since resolution can happen days later with nobody watching) is dropped in the same transaction as the add; if it's at the limit with no drop chosen, that claim is forfeited (cancelled) and the next-highest bid gets a shot instead.

## Known gaps (not yet addressed)

- **Unfilled starting slots aren't visible.** If a starter is dropped and nobody fills that slot yet, the Team page (which lists *rostered players*, not slots) simply has no row for that empty slot. It's still fillable — any eligible rostered player's own dropdown will offer it — but there's no "DEF needs a player" indicator. Fixing this would mean either always rendering full slot rows in addition to player rows, or a small "unfilled slots" summary.
- **No live stats source.** See "Scoring" above.
- **Manager email/passwords are placeholders** (`{firstname}@suntownffb.local`, shared default password) seeded by `ManagerSeeder`. Real emails would need to be set for password-reset to actually deliver.
