### Suntown FFB website

# Starting a New Season

## 1. Build the Schedule
Insert the new season's matchups into the `schedule` table (one row per game, not both directions). Columns: `id, year, week, manager1_id, manager2_id`.

**Structure:** 14 weeks, 5 matchups per week. Weeks 10–14 are identical to weeks 1–5 — those are the "rematch" weeks. Each manager plays all 9 opponents once (weeks 1–9) and 5 opponents twice (weeks 1–5 / 10–14).

**Picking the 5 rematches:** Run the query below to see how many times each pair has played historically, then prioritize rematches for the lowest-count pairs. Andy and Cameron joined in 2008 so they naturally have fewer matchups — lean their rematch slots toward the lowest pairs first.

```sql
SELECT m1.name, m2.name, COUNT(*) as games
FROM regular_season_matchups rsm
JOIN managers m1 ON rsm.manager1_id = m1.id
JOIN managers m2 ON rsm.manager2_id = m2.id
WHERE rsm.manager1_id < rsm.manager2_id
GROUP BY rsm.manager1_id, rsm.manager2_id
ORDER BY COUNT(*) ASC;
```

Each manager must appear in exactly 5 rematch pairs (since they play 5 rematches). The 25 total rematch pairs must also form 5 valid weeks (each week has all 10 managers playing once). Ask Claude to do this — it will optimize the pairing and handle the scheduling math.

## 2. Import NFL Game Schedule CSV

1. Go to `https://www.pro-football-reference.com/years/YYYY/games.htm` (replace YYYY with the season year)
2. Select all content in the games table and copy it
3. Paste into `fun-facts/storage/app/games/raw.txt`
4. Run from `fun-facts/`:
```bash
php artisan importGames
```

This converts the tab-separated paste into `storage/app/games/YYYY.csv` (matching the format of the existing CSVs) and deletes `raw.txt`. Run once per year, at the start of the season after Week 1 kickoff so all game times are published.

## 3. Pull Data from Yahoo API
Use the admin page to fetch the current season's data from the Yahoo Fantasy Sports API. Do this each week during the season to keep scores, rosters, and standings current.


---

All environments now use sqlite for the database.
To get new data, use the admin page that will interact with the Yahoo API. 
There is a laravel project inside the fun-facts folder so that jobs can be run. Here are the jobs:
php artisan funFacts : update all of the manager fun facts. 
php artisan gameTimes : parse storage/app/games/YYYY.csv and update game_time + game_slot on rosters table


# Bugs

- If yahoo api request has an error, try it again. seems to continue after but skips errored rows
    - make sure it doesnt add duplicate rows, this happens for names with an apostrophe in the name

# Ideas

- add more fun facts
    - best trade
- add these awards as records
    - most postseason losses
    - best record against everyone
    - most weeks with bottom 3 points
    - most weeks with top 3 points

- allow people add comments/smack talk to profile
- move functions to lookup after page load to make more efficient
    - current season
    - profile
- analyze text messages by manager in group text
- milestones about to happen
    - league on pace to break record
- make newsletter show random additional data so its not the same every week

- add preview notes for each week of the new season
- have a page for luck
    - wins with bottom points
    - wins under 5 pt margin

Notes:
Moved to 18 week schedule in 2021
Rams moved to LA between 2015 & 2016
Chargers moved to LA between 2016 & 2017
Raiders moved to LV between 2019 & 2020

Draft order ideas:
- movie like the Mean Girls one i saw, but still random


# Fun Fact ideas...

Head to Head record
    - Any major trends in recent years?
    - Highest/lowest score against opponent
        - This year, last year, all time?
    - Revenge game? 
    - how many times 1st place beat last place
    - biggest/smallest win
    
Current season stats
    - Major outliers
        - only team to not have a rush TD
        - more int than 5 teams combined
    - Matchup projections
    - Played optimal lineup

Fun facts
    - new leader for a long term category
    - most points scored/wins in week 7 all-time
    - the last time Andy was in first place

Generic league ideas
    - no one is mathematically eliminated
    - everyone has drafted Joe Flacco except Ben
    - odds of making playoffs
    - spokane vs. yakima valley vs. seattle
    - boboth brothers


$managersInOrder = ['Tyler', 'AJ', 'Gavin', 'Matt', 'Cameron', 'Andy', 'Everett', 'Justin', 'Cole', 'Ben'];

# Newsletter themes

- Halloween, Thanksgiving, Christmas
- 80s, superheroes
- Baseball, golf, olympics
- Mexican, italian, canadian
- Pirates, animals, space, casino, school (report card), race car


2026 pre draft order notes
- the last 5 champions have picked in the top 5, but none of them were #1
- Cole and AJ were 11-3 last year and they picked #1 and #2
- Cam, Ev, and Justin have never had #1
- Justin has only had 2 picks in the top 3, and hasn't had a top 3 pick in 14 years
- the only time #1 pick won was Ben in 2011


# Feature Ideas

## Interactive / Social Features

- **Smack Talk / Comments Board** — each week's matchups get a comment thread for trash talk before and after games. Store manager_id, week, year, body, timestamp. Give it a "Letters to the Editor" newspaper vibe.
- **Weekly Pick'em** — before each week locks, managers predict who wins each of the 5 matchups (or just their own). Running leaderboard of prediction accuracy. Keeps eliminated managers engaged every week.
- **Confidence Poll** — every manager ranks all 10 managers 1–10 in power ranking order. Average the votes, show composite ranking vs. actual standings, flag disagreements. Huge discussion driver.
- **Playoff Bracket Predictions** — at the start of the postseason, everyone locks in their bracket prediction and it scores in real time as games complete.

## Trivia / Guessing Games

- **"Guess That Score" Game** — show a week/year/matchup and ask: what was the final score? Closest guess wins bragging rights. Rotate a new puzzle weekly. Uses existing data with zero new fetching.
- **Manager Trivia Quiz** — 10-question quiz about league history ("Who scored the most points in a single game?", "Who beat Tyler in 2017 week 6?"). All answers in the DB. Shareable score at the end.
- **Historical Reenactment** — "It's Week 3, 2019. You're Matt, sitting at 1-2. What do you do?" Present a historical roster/waiver situation, let managers vote on the decision, then reveal what actually happened.

## Charts Page Additions

- **Point Distribution Violin/Box Plot** — show the spread of scores, not just averages. Who is consistently mediocre vs. who has wild swings?
- **Head-to-Head Win% Heatmap** — 10×10 grid, each cell colored by how often manager A beats manager B all-time. Visually striking and immediately creates conversation.
- **"Lucky vs. Good" Scatter Plot** — X-axis: points scored, Y-axis: actual wins. Managers above the line won more than their points deserved (lucky); below the line are unlucky. Per season or all-time.
- **Week-by-Week Points Bump Chart** — animated bump chart showing each manager's rank by total points scored, week by week through a season.
- **Optimal Lineup % Over Time** — line chart showing if managers got smarter about setting lineups over the years. Do some managers chronically leave points on the bench?
- **Score Distribution Bell Curve Overlay** — all scores in league history as a histogram with a normal curve overlay. Mark where each manager's average falls.

## Animations / Dashboard Intro

- **Season Opener Intro Sequence** — when a new season starts, a brief full-screen animation plays once (cookie-gated): stadium crowd roar, Suntown logo slams in, then transitions to the page. CSS + JS only.
- **Animated Trophy Case** — on the awards/trophy page, trophies "fall" into their shelf position on first load one by one with a clink effect.
- **Live Ticker Bar** — scrolling ESPN-style ticker below the nav during the season: "Tyler leads the league in points scored · AJ is on a 3-game win streak · Cole's optimal lineup % is league-worst." Pulls from existing fun facts.
- **"On This Day in Suntown"** — dashboard widget: "On this date in 2019, Cameron scored 187 points, the 4th-highest single-game score in history." Rotates daily. Zero new data, pure DB queries.

## Newsletter "Turning the Page" Sections

- **The Classifieds** — auto-generated fake classified ads based on roster data: "FOR SALE: 3 backup quarterbacks, gently used. Contact: Everett." Managers with depth at a position get a funny ad.
- **The Police Blotter** — absurdist "crimes" triggered by real events: "Justin cited for fielding an injured player. Bail set at 6 waiver points."
- **The Box Scores in Print** — style the weekly matchup results like a literal newspaper box score layout — tiny font, columns, totals. Different skin on existing data.
- **The Weather Report** — metaphor-based matchup preview: "Forecast: Stormy for Cole (faces #1 scoring offense). Sunny skies for Tyler (faces last-place Matt)."

## Quick Wins

- **Odds of Making Playoffs** — run 1000 random outcomes for remaining weeks (weighted by points avg), show each manager's playoff probability. Managers would refresh this constantly.
- **"Milestones Watch" Widget** — surface milestone proximity on the dashboard: "Tyler needs 47 more points to hit 10,000 career points."
- **Group Text Analyzer** — export the group chat and visualize: who texts most during the season, most common trash talk targets, busiest week, word clouds per manager.