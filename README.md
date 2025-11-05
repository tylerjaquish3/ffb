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
    - make sure it doesnt add duplicate rows, this happens for names with an apostrophe in the name
- current season draft performance has player missing (BRJ). also has some players not matching up (Stroud), need to use alias?


# Ideas

- Current season, worst draft picks remove players that got traded midseason
- add trophy graphic on trophy page
- run some "what if" scenarios
- chances of making playoffs, make request to playoffcomputer linked above
- add more fun facts
    - record against everyone
    - best trade
    - fewest points by position (all time, season)
    - most QB/RB/WR/etc points current season
    - optimal points missed
    - matchup combined score high/low
    - highest/lowest avg margin

- fun fact finder page
- allow people add comments/smack talk to profile

- Add league stats
    - Lineup accuracy
    - Playoff points/margin
- move functions to lookup after page load to make more efficient
    - current season
    - profile
- make records page more useful to find new current leaders by adding a filter for it
- profile head to head, add average margin & average combined
- make a db table for standings to keep track of the standings over time so we dont have to calculate it
    - we can make a temp job that stores them based on rsm table
    - how many weeks we spent in top 3, top 6, bottom 3, etc.
    - regular season, league standings history, add lookups by manager and week
- regular season, weekly rank, add column for opponent avg weekly rank
- do some analysis on fab spent

Notes:
Moved to 18 week schedule in 2021
Rams moved to LA between 2015 & 2016
Chargers moved to LA between 2016 & 2017
Raiders moved to LV between 2019 & 2020

Team Name:
- Almost Always Almost Win


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

week 6 baseball
week 7 
week 8. AJ, short for Andrew James and Ben, short for genetic reasons
week 9 halloween
week 10 birthday
week 11 gavin is 3-16 in week 11 all time
lowest combined points in a matchup was when Matt & Cam faced off in week 2 (206)

week 13 - Andy set the high in week 4 (196 pts), Tyler had most points in a loss (150)
week 14 - cole is 4-0 in week 14
highest optimal was AJ in week 5. lowest optimal was gavin in week 5. biggest blowout was these 2 in week 5


Cole has never won more than 9 in a season (2017)