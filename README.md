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

- start streaks on dashboard... maybe they started 4-0 3 times? put all years
- If yahoo api request has an error, try it again. seems to continue after but skips errored rows
    - make sure it doesnt add duplicate rows, this happens for names with an apostrophe in the name

# Ideas

- add more fun facts
    - record against everyone
    - best trade
    - fewest points by position (all time, season)
    - most QB/RB/WR/etc points current season
    - optimal points missed
    - matchup combined score high/low
    - highest/lowest avg margin

- allow people add comments/smack talk to profile

- move functions to lookup after page load to make more efficient
    - current season
    - profile
- analyze text messages by manager in group text
- milestones about to happen
    - league on pace to break record
- page just for charts
    - pie chart for points by position with filters for manager or all
    - lineup accuracy
- make newsletter show random additional data so its not the same every week
- add lineup accuracy to profile head to head
- add a table for top free agents
- add preview notes for each week of the new season

Notes:
Moved to 18 week schedule in 2021
Rams moved to LA between 2015 & 2016
Chargers moved to LA between 2016 & 2017
Raiders moved to LV between 2019 & 2020

Draft ideas:
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