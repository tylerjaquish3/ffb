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
- rosters page,
    - add optimal lineups info on left
- add a bunch more fun facts
    - most weeks with top/bottom 3 in points
    - record against everyone
    - best/worst draft pick
    - best free agent pick up
    - biggest comeback in matchup
    - most #1 pos rank players in a week, season, all time
    - most/least bench points week, season, all time
    - most/least IR players
- chances of making playoffs, make request to playoffcomputer linked above

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
Kim, Lizzy, Kristen, Stacie, Sheri, Monique, Carrie, Oksana, Laura, Olya

# Questions
I just have 5 quick survey questions for you relating to our fantasy football league that's about to start its 19th season.

1. On a scale from 1-10, how excited are you for fantasy football season approaching?
    - Why did you pick that number?
2. How many championships would you guess "XXXX" has won?
    - Why do you think he hasn't won more than that?
3. If you could pick a draft position for "XXXX" what would you pick?
    - Do you think he would want that position or are you trying to sabotage him?
4. This may be a shot in the dark, how many points do you think "XXXX" scored last year?
5. In a few weeks, we're going to vote on a new punishment for the league loser. Do you have any ideas?

That's it. Thank you so much for participating. And please keep this confidential from "XXXX" until I share the results with him, I would appreciate it.


