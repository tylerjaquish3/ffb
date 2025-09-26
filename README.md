### Suntown FFB website

All environments now use sqlite for the database. There is an outdated version of the database in mysql on PC and Production.
To get new data, use the admin page that will interact with the Yahoo API. 
There is a laravel project inside the fun-facts folder so that jobs can be run. Here are the jobs:
php artisan funFacts : update all of the manager fun facts. 
php artisan gameTimes : get game times from the storage/app/games CSVs and update rosters table. Also update game_slot based on game_time

Playoff Calculator: https://playoffcomputer.appspot.com/

# Bugs
- In regular_season_matchups table, lots of projections are wrong (looking at 2019)
- start streaks on dashboard... maybe they started 4-0 3 times? put all years

- If yahoo api request has an error, try it again. seems to continue after but skips errored rows
    - make sure it doesnt add duplicate rows
- index page, postseason stats, average finish table - fix spacing for cole's trophies
- some current season records are showing 2006 data (i deleted them but the logic may put them back)
- fix logic for determining fun fact new leader
- current season draft performance has player missing (BRJ)

# Ideas

- Current season, worst draft picks remove players that got traded midseason
- add trophy graphic on trophy page
- run some "what if" scenarios
- chances of making playoffs, make request to playoffcomputer linked above
- add a bunch more fun facts
    - record against everyone
    - best trade
    - fewest points by position (all time, season)
    - most QB/RB/WR/etc points current season

- fun fact finder page
- allow people add comments/smack talk to profile

- Add league stats
    - Lineup accuracy
    - Playoff points/margin
- use lighthouse to find unneeded css and js, make more efficient
- add form to submit new ideas

Notes:
Moved to 18 week schedule in 2021
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

