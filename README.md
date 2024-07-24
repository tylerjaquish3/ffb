### Suntown FFB website

All environments now use sqlite for the database. There is an outdated version of the database in mysql on PC and Production.
To get new data, use the admin page that will interact with the Yahoo API. 
There is a laravel project inside the fun-facts folder so that jobs can be run. Here are the jobs:
php artisan funFacts : update all of the manager fun facts. 
php artisan gameTimes : get game times from the storage/app/games CSVs and update rosters table. Also update game_slot based on game_time

Playoff Calculator: https://playoffcomputer.appspot.com/

# Bugs
- fun facts current season margin doesnt update new_leader and is not accurate
- Current Season - Optimal Lineups endpoint needs optimizing (times out)
- In regular_season_matchups table, lots of projections are wrong (looking at 2019)

# Ideas

- Profile page, line chart for points each season vs league average
- Current season, worst draft picks remove players that got traded midseason
- add trophy graphic on trophy page
- make sidebar collapsible
- grab postseason matchup rosters
- chances of making playoffs, make request to playoffcomputer linked above
- rosters page,
    - add optimal lineups info on left
- add a bunch more fun facts
    - record against everyone
    - best trade
    - most #1 pos rank players in a week, season, all time
    - fewest points by position (all time, season)
    - most QBs/RBs/etc drafted in first round
    - most seahawks drafted

Notes:
Rams moved to LA between 2015 & 2016
Chargers moved to LA between 2016 & 2017
Raiders moved to LV between 2019 & 2020


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

