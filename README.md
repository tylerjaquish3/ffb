### Suntown FFB website

All environments now use sqlite for the database. There is an outdated version of the database in mysql on PC and Production.
To get new data, use the admin page that will interact with the Yahoo API. 
There is a laravel project inside the fun-facts folder so that jobs can be run. Here are the jobs:
php artisan funFacts : update all of the manager fun facts. 
php artisan gameTimes : get game times from the storage/app/games CSVs and update rosters table. Also update game_slot based on game_time


# Bugs

- start streaks on dashboard... maybe they started 4-0 3 times? put all years
- If yahoo api request has an error, try it again. seems to continue after but skips errored rows
    - make sure it doesnt add duplicate rows, this happens for names with an apostrophe in the name

- record for best free agent pick is not using player aliases to match players
- misc stats start streaks says cam started 0-3 in 2025 and finished 10th but the season isnt over yet

# Ideas

- Current season, worst draft picks remove players that got traded midseason
- add trophy graphic on trophy page
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
- analyze text messages by manager in group text
- milestones about to happen
    - Matt about to go over 10,000 QB points
    - league on pace to break record
- page just for charts
    - pie chart for points by position with filters for manager or all
    - lineup accuracy
- win/lose streaks, put the start and end week and year
- page for random league facts
    - all Marvin's since 2009 have been Jrs
    - Baltimore has been owned the most but only by 7 managers
- regular season, game time analysis, make some way to highlight the current year bests and where they rank so its easier to use
- players page, add some analysis by nfl team and year from rosters
- separate pages now into a folder with an index and include other page parts
    - need to update links to that page to just go to that folder rather than .php file
    - do this on a new branch in case it gets messy
- check points missed to see which teams missed out on the most wins

Notes:
Moved to 18 week schedule in 2021
Rams moved to LA between 2015 & 2016
Chargers moved to LA between 2016 & 2017
Raiders moved to LV between 2019 & 2020

Team Name:
- Almost Always Almost Win

Draft ideas:
- magic 8 ball or mystic pickle?
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


Billy madison
have 5 daquiris? opening scene
tallyhoo - early scene with business men
pickle race 
4 and 7 in 1st grade classroom

Storyline
A decathlon to determine the draft order