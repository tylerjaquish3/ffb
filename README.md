### Suntown FFB website


# Ideas

- Profile page, add pie chart for wins vs each opponent
- Profile page, in grid with all teams and records, highlight the top values for each column
- Profile page, line chart for points each season vs league average
- trades before 2012?
- Awards page, inaccurate data for 
    - Most Total TDs (current season)
    - most championships should be 6
- Current season, combine players that got traded midseason
- Draft page, add chart for positions by round?

- draft order based on wives/gfs responses to something?


--
-- File generated with SQLiteStudio v3.3.3 on Thu Aug 31 15:12:48 2023
--
-- Text encoding used: UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- Table: trades
CREATE TABLE trades (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE, trade_identifier INTEGER NOT NULL, year INTEGER, manager_from_id INTEGER, manager_to_id INTEGER, player STRING);

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;


INSERT INTO trades (
                       player,
                       manager_to_id,
                       manager_from_id,
                       year,
                       trade_identifier,
                       id
                   )
                   VALUES (
                       'Vincent Jackson',
                       5,
                       10,
                       2013,
                       24,
                       85
                   ),
                   (
                       'Larry Fitzgerald',
                       10,
                       5,
                       2013,
                       24,
                       86
                   ),
                   (
                       'Maurice Jones-Drew',
                       5,
                       4,
                       2013,
                       23,
                       87
                   ),
                   (
                       'Mike Glennon',
                       4,
                       5,
                       2013,
                       23,
                       88
                   ),
                   (
                       'Brandon Marshall',
                       2,
                       9,
                       2017,
                       22,
                       83
                   ),
                   (
                       'Danny Amendola',
                       9,
                       2,
                       2017,
                       22,
                       84
                   ),
                   (
                       'Amari Cooper',
                       2,
                       6,
                       2017,
                       21,
                       77
                   ),
                   (
                       'Russell Wilson',
                       2,
                       6,
                       2017,
                       21,
                       78
                   ),
                   (
                       'Jacksonville',
                       6,
                       2,
                       2017,
                       21,
                       79
                   ),
                   (
                       'Sammy Watkins',
                       6,
                       2,
                       2017,
                       21,
                       80
                   ),
                   (
                       'Sterling Shepard',
                       6,
                       2,
                       2017,
                       21,
                       81
                   ),
                   (
                       'Eli Manning',
                       6,
                       2,
                       2017,
                       21,
                       82
                   ),
                   (
                       'Bobby Wagner',
                       1,
                       8,
                       2017,
                       20,
                       67
                   ),
                   (
                       'Jerick McKinnon',
                       1,
                       8,
                       2017,
                       20,
                       68
                   ),
                   (
                       'Duke Johnson',
                       1,
                       8,
                       2017,
                       20,
                       69
                   ),
                   (
                       'Keenan Allen',
                       1,
                       8,
                       2017,
                       20,
                       70
                   ),
                   (
                       'Philip Rivers',
                       1,
                       8,
                       2017,
                       20,
                       71
                   ),
                   (
                       'Kenny Vaccaro',
                       8,
                       1,
                       2017,
                       20,
                       72
                   ),
                   (
                       'Cincinnati',
                       8,
                       1,
                       2017,
                       20,
                       73
                   ),
                   (
                       'Stefon Diggs',
                       8,
                       1,
                       2017,
                       20,
                       74
                   ),
                   (
                       'DeMarco Murray',
                       8,
                       1,
                       2017,
                       20,
                       75
                   ),
                   (
                       'Drew Brees',
                       8,
                       1,
                       2017,
                       20,
                       76
                   ),
                   (
                       'Ryan Fitzpatrick',
                       8,
                       5,
                       2018,
                       19,
                       65
                   ),
                   (
                       'James Conner',
                       5,
                       8,
                       2018,
                       19,
                       66
                   ),
                   (
                       'Melvin Ingram',
                       1,
                       6,
                       2018,
                       18,
                       59
                   ),
                   (
                       'Rex Burkhead',
                       1,
                       6,
                       2018,
                       18,
                       60
                   ),
                   (
                       'Adam Thielen',
                       1,
                       6,
                       2018,
                       18,
                       61
                   ),
                   (
                       'Jordan Hicks',
                       6,
                       1,
                       2018,
                       18,
                       62
                   ),
                   (
                       'Royce Freeman',
                       6,
                       1,
                       2018,
                       18,
                       63
                   ),
                   (
                       'Tyler Lockett',
                       6,
                       1,
                       2018,
                       18,
                       64
                   ),
                   (
                       'Benjamin Watson',
                       1,
                       10,
                       2018,
                       17,
                       55
                   ),
                   (
                       'Alvin Kamara',
                       1,
                       10,
                       2018,
                       17,
                       56
                   ),
                   (
                       'Carlos Hyde',
                       10,
                       1,
                       2018,
                       17,
                       57
                   ),
                   (
                       'Zach Ertz',
                       10,
                       1,
                       2018,
                       17,
                       58
                   ),
                   (
                       'Courtland Sutton',
                       1,
                       6,
                       2018,
                       16,
                       52
                   ),
                   (
                       'Ryan Fitzpatrick',
                       1,
                       6,
                       2018,
                       16,
                       53
                   ),
                   (
                       'Kerryon Johnson',
                       6,
                       1,
                       2018,
                       16,
                       54
                   ),
                   (
                       'Doug Martin',
                       1,
                       9,
                       2018,
                       15,
                       50
                   ),
                   (
                       'Marlon Mack',
                       9,
                       1,
                       2018,
                       15,
                       51
                   ),
                   (
                       'Gardner Minshew',
                       6,
                       7,
                       2019,
                       14,
                       46
                   ),
                   (
                       'Tyrell Williams',
                       7,
                       6,
                       2019,
                       14,
                       47
                   ),
                   (
                       'Marlon Mack',
                       2,
                       10,
                       2019,
                       13,
                       48
                   ),
                   (
                       'Odell Beckham Jr.',
                       10,
                       2,
                       2019,
                       13,
                       49
                   ),
                   (
                       'Kerryon Johnson',
                       1,
                       4,
                       2019,
                       12,
                       44
                   ),
                   (
                       'Chris Carson',
                       4,
                       1,
                       2019,
                       12,
                       45
                   ),
                   (
                       'Stefon Diggs',
                       1,
                       2,
                       2020,
                       11,
                       42
                   ),
                   (
                       'Chris Carson',
                       2,
                       1,
                       2020,
                       11,
                       43
                   ),
                   (
                       'Mike Davis',
                       10,
                       1,
                       2020,
                       10,
                       40
                   ),
                   (
                       'Austin Ekeler',
                       1,
                       10,
                       2020,
                       10,
                       41
                   ),
                   (
                       'Mike Williams',
                       4,
                       8,
                       2020,
                       9,
                       38
                   ),
                   (
                       'Boston Scott',
                       8,
                       4,
                       2020,
                       9,
                       39
                   ),
                   (
                       'Michael Thomas',
                       1,
                       9,
                       2021,
                       8,
                       32
                   ),
                   (
                       'San Francisco',
                       1,
                       9,
                       2021,
                       8,
                       33
                   ),
                   (
                       'Russell Wilson',
                       1,
                       9,
                       2021,
                       8,
                       34
                   ),
                   (
                       'Denver',
                       9,
                       1,
                       2021,
                       8,
                       35
                   ),
                   (
                       'Melvin Gordon',
                       9,
                       1,
                       2021,
                       8,
                       36
                   ),
                   (
                       'CeeDee Lamb',
                       9,
                       1,
                       2021,
                       8,
                       37
                   ),
                   (
                       'Younghoe Koo',
                       1,
                       10,
                       2021,
                       7,
                       24
                   ),
                   (
                       'Christian McCaffrey',
                       1,
                       10,
                       2021,
                       7,
                       25
                   ),
                   (
                       'David Johnson',
                       1,
                       10,
                       2021,
                       7,
                       26
                   ),
                   (
                       'DK Metcalf',
                       1,
                       10,
                       2021,
                       7,
                       27
                   ),
                   (
                       'Daniel Carlson',
                       10,
                       1,
                       2021,
                       7,
                       28
                   ),
                   (
                       'Russell Wilson',
                       10,
                       1,
                       2021,
                       7,
                       29
                   ),
                   (
                       'Damien Harris',
                       10,
                       1,
                       2021,
                       7,
                       30
                   ),
                   (
                       'Deebo Samuel',
                       10,
                       1,
                       2021,
                       7,
                       31
                   ),
                   (
                       'Terry McLaurin',
                       4,
                       8,
                       2021,
                       6,
                       21
                   ),
                   (
                       'Tyreek Hill',
                       8,
                       4,
                       2021,
                       6,
                       22
                   ),
                   (
                       'Mike Williams',
                       4,
                       8,
                       2021,
                       6,
                       23
                   ),
                   (
                       'Stefon Diggs',
                       5,
                       6,
                       2022,
                       5,
                       19
                   ),
                   (
                       'Aaron Rodgers',
                       6,
                       5,
                       2022,
                       5,
                       20
                   ),
                   (
                       'Kyler Murray',
                       6,
                       8,
                       2022,
                       4,
                       13
                   ),
                   (
                       'Amari Cooper',
                       6,
                       8,
                       2022,
                       4,
                       14
                   ),
                   (
                       'Tyreek Hill',
                       6,
                       8,
                       2022,
                       4,
                       15
                   ),
                   (
                       'CeeDee Lamb',
                       8,
                       6,
                       2022,
                       4,
                       16
                   ),
                   (
                       'Justin Jefferson',
                       8,
                       6,
                       2022,
                       4,
                       17
                   ),
                   (
                       'Aaron Rodgers',
                       8,
                       6,
                       2022,
                       4,
                       18
                   ),
                   (
                       'Mecole Hardman',
                       5,
                       10,
                       2022,
                       3,
                       9
                   ),
                   (
                       'Devin Duvernay',
                       5,
                       10,
                       2022,
                       3,
                       10
                   ),
                   (
                       'Jahan Dotson',
                       10,
                       5,
                       2022,
                       3,
                       11
                   ),
                   (
                       'Taysom Hill',
                       10,
                       5,
                       2022,
                       3,
                       12
                   ),
                   (
                       'Jimmy Garoppolo',
                       10,
                       7,
                       2022,
                       2,
                       8
                   ),
                   (
                       'Raheem Mostert',
                       7,
                       10,
                       2022,
                       2,
                       7
                   ),
                   (
                       'Joshua Palmer',
                       1,
                       10,
                       2022,
                       1,
                       2
                   ),
                   (
                       'George Kittle',
                       1,
                       10,
                       2022,
                       1,
                       3
                   ),
                   (
                       'Davante Adams',
                       1,
                       10,
                       2022,
                       1,
                       4
                   ),
                   (
                       'Darrell Henderson',
                       10,
                       1,
                       2022,
                       1,
                       5
                   ),
                   (
                       'Jakobi Meyers',
                       10,
                       1,
                       2022,
                       1,
                       6
                   ),
                   (
                       'Dalvin Cook',
                       10,
                       1,
                       2022,
                       1,
                       1
                   );

