<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use App\Models\RecordLog;
use App\Models\ManagerFunFact;
use App\Models\FunFact;

class UpdateWeeklyRecords implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The year to update records for.
     */
    protected $year;

    /**
     * The week to update records for. If null, will update all weeks.
     */
    protected $week;
    
    /**
     * Whether to run a test calculation with debug output.
     */
    protected $testMode = false;

    /**
     * Create a new job instance.
     */
    public function __construct($year, $week = null, $testMode = false)
    {
        $this->year = $year;
        $this->week = $week;
        $this->testMode = $testMode;
    }

    /**
     * Keep track of last leaders for each fun fact
     */
    protected $lastLeaders = [];

    /**
     * Starting year for the league
     */
    protected $startYear = 2006;

    /**
     * Current year
     */
    protected $currentYear;

    /**
     * Current week
     */
    protected $currentWeek;

    /**
     * Execute the job.
     */
    public function handle()
    {
        $success = true;
        $message = "";
        
        try {
            // Special test mode for debugging
            if ($this->testMode === true) {
                $this->runTestMode();
                return 0;
            }
            
            echo "=============================================" . PHP_EOL;
            echo "Starting to update weekly records for year: {$this->year}" . ($this->week ? ", week: {$this->week}" : "") . PHP_EOL;
            echo "=============================================" . PHP_EOL;
            
            $this->currentYear = date('Y');
            $this->currentWeek = $this->getCurrentWeek();
            
            echo "Current year detected as: {$this->currentYear}, Current week: {$this->currentWeek}" . PHP_EOL;
            
            // Reset any cached state
            $this->lastLeaders = [];
            
            // If a specific year/week was provided, only process that one
            if ($this->year && $this->week) {
                $this->processYearWeek($this->year, $this->week);
            } 
            // If just a year was provided, process all weeks in that year
            else if ($this->year) {
                $maxWeek = ($this->year == $this->currentYear) ? $this->currentWeek : 17;
                for ($week = 1; $week <= $maxWeek; $week++) {
                    $this->processYearWeek($this->year, $week);
                }
            } 
            // If nothing was provided, process the entire league history in chronological order
            else {
                // First, clear all existing records if we're doing a full reprocessing
                // This ensures data consistency when processing the entire history
                if (!$this->year && !$this->week) {
                    echo "Clearing all existing record logs for full reprocessing" . PHP_EOL;
                    RecordLog::query()->delete();
                }
                
                // Process in strict chronological order
                for ($year = $this->startYear; $year <= $this->currentYear; $year++) {
                    echo "Processing year {$year}" . PHP_EOL;
                    $maxWeek = ($year == $this->currentYear) ? $this->currentWeek : 17;
                    
                    for ($week = 1; $week <= $maxWeek; $week++) {
                        echo "Processing week {$week} of {$year}" . PHP_EOL;
                        $this->processYearWeek($year, $week);
                    }
                }
            }
            
            $recordCount = RecordLog::count();
            $message = "Weekly records updated successfully. Total records: {$recordCount}";
            echo "=============================================" . PHP_EOL;
            echo $message . PHP_EOL;
            echo "=============================================" . PHP_EOL;
        } catch (\Exception $e) {
            $success = false;
            $message = "Error updating weekly records: " . $e->getMessage();
            echo "=============================================" . PHP_EOL;
            echo "ERROR: {$message}" . PHP_EOL;
            echo $e->getTraceAsString() . PHP_EOL;
            echo "=============================================" . PHP_EOL;
        }
        
        return $success ? 0 : 1;
    }
    
    /**
     * Process a specific year and week
     */
    protected function processYearWeek($year, $week)
    {
        echo "Processing records for Year {$year}, Week {$week}" . PHP_EOL;
        
        // Get all fun facts
        $funFacts = FunFact::all();
        
        foreach ($funFacts as $funFact) {
            $this->processRecord($year, $week, $funFact);
        }
    }
    
    /**
     * Process a record for a specific fun fact at the given year and week
     */
    protected function processRecord($year, $week, $funFact)
    {
        // Check if we already have records for this year/week/fun fact
        $existingRecords = RecordLog::where([
            'year' => $year,
            'week' => $week,
            'fun_fact_id' => $funFact->id
        ])->get();
        
        // Skip if we already have records unless we're explicitly processing a single year/week
        if ($existingRecords->count() > 0 && !($this->year && $this->week)) {
            echo "Skipping record for fun fact {$funFact->id} at {$year}-W{$week} - already exists" . PHP_EOL;
            return;
        }
        
        // Initialize UpdateFunFacts job with the historical point in time
        $updateFunFacts = new UpdateFunFacts($year, $week);
        
        // Get the current leader(s) for this fun fact at this point in time
        $leaders = $this->getLeaderForFunFact($funFact->id, $year, $week, $updateFunFacts);
        
        if ($leaders) {
            // Handle multiple leaders (ties)
            $leadersArray = is_array($leaders) ? $leaders : [$leaders];
            
            // Check if all leaders have a value of 0 - if so, skip recording
            $allLeadersZero = true;
            foreach ($leadersArray as $leader) {
                if ($leader['value'] != 0) {
                    $allLeadersZero = false;
                    break;
                }
            }
            
            if ($allLeadersZero) {
                echo "Skipping fun fact {$funFact->id} at {$year}-W{$week} - all leaders tied with 0" . PHP_EOL;
                return;
            }
            
            // Delete existing records if we're recalculating
            if ($existingRecords->count() > 0) {
                RecordLog::whereIn('id', $existingRecords->pluck('id'))->delete();
            }
            
            foreach ($leadersArray as $leader) {
                $this->recordSingleFunFact($year, $week, $funFact, $leader);
            }
        } else {
            echo "WARNING: No leader found for fun fact {$funFact->id} at {$year}-W{$week}" . PHP_EOL;
        }
    }
    
    /**
     * Record a single fun fact leader in the record_log
     */
    protected function recordSingleFunFact($year, $week, $funFact, $leader)
    {
        // Determine if this is a new leader by comparing to the previous chronological entry
        // for this fun fact, whether it was last week or last season
        $previousRecord = null;
        
        // Get the previous week/round (handles both regular season and playoffs)
        $previousWeekOrRound = $this->getPreviousWeekOrRound($year, $week);
        
        if ($previousWeekOrRound) {
            $previousRecord = RecordLog::where([
                'year' => $year,
                'week' => $previousWeekOrRound,
                'fun_fact_id' => $funFact->id
            ])->first();
        }
        
        // If no previous record in current season, check end of previous season
        if (!$previousRecord && $year > $this->startYear) {
            $previousRecord = RecordLog::where([
                'year' => $year - 1,
                'week' => 17, // Assume 17-week season
                'fun_fact_id' => $funFact->id
            ])->first();
        }
        
        // If this is the very first week of the first season, or we have a new leader
        $isNewLeader = !$previousRecord || (int)$previousRecord->manager_id !== (int)$leader['manager_id'];
        
        // Special case for first week of first season - everyone is a new leader
        if ($year == $this->startYear && $week == 1) {
            $isNewLeader = true;
        }
        
        // Record the leader in our log
        $weekOrRound = $this->getPlayoffRound($year, $week);
        $recordLog = RecordLog::updateOrCreate([
            'year' => $year,
            'week' => $weekOrRound,
            'fun_fact_id' => $funFact->id,
        ], [
            'manager_id' => $leader['manager_id'],
            'value' => $leader['value'],
            'note' => $leader['note'] ?? null,
            'new_leader' => $isNewLeader,
        ]);
        
        echo "Recorded fun fact {$funFact->id} for {$year}-W{$week}: {$leader['manager_id']} " . 
             ($isNewLeader ? "(NEW LEADER)" : "(continuing leader)") . PHP_EOL;
        
        // Update the last leader for this fun fact in our memory cache
        $this->lastLeaders[$funFact->id] = $leader['manager_id'];
        
        return $recordLog;
    }
    
    /**
     * Process a single specific fun fact by ID
     * This is useful for testing or repairing specific records
     */
    public function processSingleFunFact($funFactId, $year = null, $week = null)
    {
        $funFact = FunFact::find($funFactId);
        
        if (!$funFact) {
            echo "ERROR: Fun fact with ID {$funFactId} not found." . PHP_EOL;
            return null;
        }
        
        $year = $year ?: $this->year;
        $week = $week ?: $this->week;
        
        if (!$year || !$week) {
            echo "ERROR: Cannot process single fun fact without year and week." . PHP_EOL;
            return null;
        }
        
        return $this->processRecord($year, $week, $funFact);
    }
    
    /**
     * Get the leader for a fun fact at the given point in time
     */
    /**
     * Get the current leader(s) for this fun fact at this point in time
     * Returns array of leaders (multiple if tied)
     */
    protected function getLeaderForFunFact($funFactId, $year, $week, $updateFunFacts)
    {
        try {
            // Check if we already have this recorded in our record_log
            $existingRecords = RecordLog::where([
                'fun_fact_id' => $funFactId,
                'year' => $year,
                'week' => $week
            ])->get();
            
            if ($existingRecords->count() > 0 && !($this->year && $this->week)) {
                // If we have existing records and aren't explicitly asked to recalculate,
                // just return the data from those records
                return $existingRecords->map(function($record) {
                    return [
                        'manager_id' => $record->manager_id,
                        'value' => $record->value,
                        'note' => $record->note
                    ];
                })->toArray();
            }
            
            // Try to calculate the leader(s) based on the historical data up to this point
            $calculatedLeaders = $this->calculateLeaderForFunFact($funFactId, $year, $week, $updateFunFacts);
            if ($calculatedLeaders) {
                return $calculatedLeaders;
            } else {
                return null;
            }
            
            // Don't fall back to current data for historical calculations - that would be inaccurate
            // If there's no historical data, then there's no record for that time period
        } catch (\Exception $e) {
            echo "ERROR: Getting leader for fun fact {$funFactId} at {$year}-W{$week}: " . $e->getMessage() . PHP_EOL;
            echo $e->getTraceAsString() . PHP_EOL;
        }
        
        return null;
    }
    
    /**
     * Calculate the leader for a specific fun fact
     * This will call the corresponding method in UpdateFunFacts
     */
    protected function calculateLeaderForFunFact($funFactId, $year, $week, $updateFunFacts)
    {
        // Skip postseason-related fun facts during regular season
        // Playoffs started at week 14 from 2006-2020, but moved to week 15 starting in 2021
        $postseasonFunFactIds = [4, 5, 6, 12, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 31, 50, 51, 52, 53, 54, 55, 58, 59, 60, 61, 65, 66];
        $playoffStartWeek = ($year >= 2021) ? 15 : 14;
        $isPlayoffWeek = $week >= $playoffStartWeek;
        
        if (in_array($funFactId, $postseasonFunFactIds) && !$isPlayoffWeek) {
            // Skip postseason fun facts during regular season
            return null;
        }
        
        if (!in_array($funFactId, $postseasonFunFactIds) && $isPlayoffWeek) {
            // Skip regular season fun facts during playoffs
            return null;
        }
        
        // Map fun fact IDs to their respective methods in UpdateFunFacts (in numerical order)
        $methodMap = [
            // IDs 1-3: Most Points For
            1 => 'mostPointsFor',
            2 => 'mostPointsFor',
            3 => 'mostPointsFor',
            // IDs 4-6: Most Postseason Points For
            4 => 'mostPostseasonPointsFor', 
            5 => 'mostPostseasonPointsFor',
            6 => 'mostPostseasonPointsFor',
            // IDs 7-9: Points Against
            7 => 'leastPointsAgainst',
            8 => 'leastPointsAgainst',
            9 => 'leastPointsAgainst',
            // IDs 10-11: Most Wins
            10 => 'mostWins',
            11 => 'mostWins',
            // ID 12: Most Playoff Wins
            12 => 'postseasonRecords',
            // IDs 13-15: Least Points For
            13 => 'leastPointsFor',
            14 => 'leastPointsFor',
            15 => 'leastPointsFor',
            // IDs 16-17: Most Losses
            16 => 'mostLosses',
            17 => 'mostLosses',
            // IDs 18-21: Underdog Wins
            18 => 'postseasonRecords',
            19 => 'postseasonRecords', 
            20 => 'postseasonRecords',
            21 => 'postseasonRecords',
            // IDs 22-25: Top Seed Losses
            22 => 'postseasonRecords',
            23 => 'postseasonRecords',
            24 => 'postseasonRecords', 
            25 => 'postseasonRecords',
            // IDs 26-28: Playoff Seeding
            26 => 'postseasonRecords',
            27 => 'postseasonRecords',
            28 => 'postseasonRecords',
            // IDs 29-30: Single Opponent
            29 => 'singleOpponent',
            30 => 'singleOpponent',
            // ID 31: Most championships
            31 => 'postseasonRecords',
            // ID 32: Least championships
            32 => 'leastChampionships',
            // IDs 33-36: Average Points
            33 => 'averagePoints',
            34 => 'averagePoints',
            35 => 'averagePoints',
            36 => 'averagePoints',
            // IDs 39-40: Streaks
            39 => 'streaks',
            40 => 'streaks',
            // IDs 41-42: Championship Appearances
            41 => 'appearances',
            42 => 'appearances',
            // IDs 45-48: Margins
            45 => 'margins',
            46 => 'margins',
            47 => 'margins',
            48 => 'margins',
            // IDs 50-55: Postseason Margin
            50 => 'postseasonMargin',
            51 => 'postseasonMargin',
            52 => 'postseasonMargin',
            53 => 'postseasonMargin',
            54 => 'postseasonMargin',
            55 => 'postseasonMargin',
            // IDs 56-57: Streaks
            56 => 'streaks',
            57 => 'streaks',
            // IDs 58-59: Postseason Win Percentage
            58 => 'postseasonWinPct',
            59 => 'postseasonWinPct',
            // IDs 60-61: Current Postseason Streak
            60 => 'currentPostseasonStreak',
            61 => 'currentPostseasonStreak',
            // IDs 62-63: Draft
            62 => 'draft',
            63 => 'draft',
            // IDs 65-66: More Postseason Records
            65 => 'postseasonRecords',
            66 => 'postseasonRecords',
            // IDs 67-70: Single Opponent
            67 => 'singleOpponent',
            68 => 'singleOpponent',
            69 => 'singleOpponent',
            70 => 'singleOpponent',
            // IDs 71-72: Draft
            71 => 'draft',
            72 => 'draft',
            // IDs 73-75: Moves
            73 => 'moves',
            74 => 'moves',
            75 => 'moves',
            // IDs 76-80: Current Season Stats
            76 => 'currentSeasonStats',
            77 => 'currentSeasonStats',
            78 => 'currentSeasonStats',
            79 => 'currentSeasonStats',
            80 => 'currentSeasonStats',
            // IDs 81-87: Current Season Points
            81 => 'currentSeasonPoints',
            82 => 'currentSeasonPoints',
            83 => 'currentSeasonPoints',
            84 => 'currentSeasonPoints',
            85 => 'currentSeasonPoints',
            86 => 'currentSeasonPoints',
            87 => 'currentSeasonPoints',
            // IDs 89-91: Points Against (continued)
            89 => 'leastPointsAgainst',
            90 => 'leastPointsAgainst',
            91 => 'leastPointsAgainst',
            // IDs 92-93: Weekly Ranks
            92 => 'weeklyRanks',
            93 => 'weeklyRanks',
            // IDs 97-98: Draft Picks
            97 => 'draftPicks',
            98 => 'draftPicks',
            // IDs 107-108: Draft Picks (continued)
            107 => 'draftPicks',
            108 => 'draftPicks',
            // IDs 109-110: Weekly Rankings (Top/Bottom 3) - not yet implemented
            // 109 => 'weeklyTopBottomRanks',  // Most Weeks with Bottom 3 Points
            // 110 => 'weeklyTopBottomRanks',  // Most Weeks with Top 3 Points
            // IDs 111-128: Position Totals
            111 => 'positionTotals',  // Most DEF Points (Week)
            112 => 'positionTotals',  // Most DEF Points (Season)
            113 => 'positionTotals',  // Most DEF Points (All Time)
            114 => 'positionTotals',  // Most K Points (Week)
            115 => 'positionTotals',  // Most K Points (Season)
            116 => 'positionTotals',  // Most K Points (All Time)
            117 => 'positionTotals',  // Most TE Points (Week)
            118 => 'positionTotals',  // Most TE Points (Season)
            119 => 'positionTotals',  // Most TE Points (All Time)
            120 => 'positionTotals',  // Most WR Points (Week)
            121 => 'positionTotals',  // Most WR Points (Season)
            122 => 'positionTotals',  // Most WR Points (All Time)
            123 => 'positionTotals',  // Most RB Points (Week)
            124 => 'positionTotals',  // Most RB Points (Season)
            125 => 'positionTotals',  // Most RB Points (All Time)
            126 => 'positionTotals',  // Most QB Points (Week)
            127 => 'positionTotals',  // Most QB Points (Season)
            128 => 'positionTotals',  // Most QB Points (All Time)
            // IDs 129-134: Bench Points
            129 => 'benchPoints',
            130 => 'benchPoints',
            131 => 'benchPoints',
            132 => 'benchPoints',
            133 => 'benchPoints',
            134 => 'benchPoints',
            // ID 135: Comeback
            135 => 'comeback',
            // IDs 136-137: Points In Win Loss
            136 => 'pointsInWinLoss',
            137 => 'pointsInWinLoss', 
            // IDs 138-139: Free Agent
            138 => 'freeAgent',
            139 => 'freeAgent',
            // IDs 140-141: IR Players
            140 => 'irPlayers',
            141 => 'irPlayers',
            // IDs 142-144: Weekly Position Players
            142 => 'weeklyPositionPlayers',
            143 => 'weeklyPositionPlayers',
            144 => 'weeklyPositionPlayers',
            // IDs 147-164: Position Performance Tracking
            147 => 'weeklyPositionPerformance',
            148 => 'weeklyPositionPerformance',
            149 => 'weeklyPositionPerformance',
            150 => 'weeklyPositionPerformance',
            151 => 'weeklyPositionPerformance',
            152 => 'weeklyPositionPerformance',
            153 => 'weeklyPositionPerformance',
            154 => 'weeklyPositionPerformance',
            155 => 'weeklyPositionPerformance',
            156 => 'weeklyPositionPerformance',
            157 => 'weeklyPositionPerformance',
            158 => 'weeklyPositionPerformance',
            159 => 'weeklyPositionPerformance',
            160 => 'weeklyPositionPerformance',
            161 => 'weeklyPositionPerformance',
            162 => 'weeklyPositionPerformance',
            163 => 'weeklyPositionPerformance',
            164 => 'weeklyPositionPerformance',
            // IDs 165-168: Most Picks By Position
            165 => 'mostPicksByPosition',
            166 => 'mostPicksByPosition',
            167 => 'mostPicksByPosition',
            168 => 'mostPicksByPosition',
            // IDs 169-170: Seahawks Drafted
            169 => 'seahawksDrafted',
            170 => 'seahawksDrafted',
        ];
        
        if (isset($methodMap[$funFactId])) {
            try {
                // Use database filtering to only include data up to the specified year and week
                // This is crucial to ensure we only calculate records based on data available at that point in time
                
                // Begin transaction to ensure data consistency
                DB::beginTransaction();
                
                // Save existing fun fact data before we modify it
                $existingFact = ManagerFunFact::where('fun_fact_id', $funFactId)->first();
                
                // Create temporary tables that contain only the historical data up to the given year and week
                // For regular season matchups - only include games up to the specific year and week
                DB::statement("
                    CREATE TEMPORARY TABLE temp_regular_season_matchups AS
                    SELECT * FROM regular_season_matchups 
                    WHERE (year < {$year}) OR (year = {$year} AND week_number <= {$week})
                ");
                
                // For playoff matchups - include games from previous years
                // For historical calculations during playoff weeks, also include current year playoff data
                // but only up to the current playoff round (since those specific rounds have been completed)
                if ($isPlayoffWeek) {
                    $currentRound = $this->getPlayoffRound($year, $week);
                    $roundsToInclude = [];
                    
                    // Build list of rounds to include based on current round
                    if ($currentRound === 'Quarterfinal') {
                        $roundsToInclude = ['Quarterfinal'];
                    } elseif ($currentRound === 'Semifinal') {
                        $roundsToInclude = ['Quarterfinal', 'Semifinal'];
                    } elseif ($currentRound === 'Final') {
                        $roundsToInclude = ['Quarterfinal', 'Semifinal', 'Final'];
                    }
                    
                    if (!empty($roundsToInclude)) {
                        $roundsList = "'" . implode("', '", $roundsToInclude) . "'";
                        $playoffFilter = "(year < {$year}) OR (year = {$year} AND round IN ({$roundsList}))";
                    } else {
                        $playoffFilter = "year < {$year}";
                    }
                } else {
                    $playoffFilter = "year < {$year}";
                }
                
                DB::statement("
                    CREATE TEMPORARY TABLE temp_playoff_matchups AS
                    SELECT * FROM playoff_matchups 
                    WHERE {$playoffFilter}
                ");
                
                // For drafts - only include drafts up to the given year
                DB::statement("
                    CREATE TEMPORARY TABLE temp_drafts AS
                    SELECT * FROM draft
                    WHERE year <= {$year}
                ");
                
                // For rosters - only include up to the given year and week
                DB::statement("
                    CREATE TEMPORARY TABLE temp_rosters AS
                    SELECT * FROM rosters
                    WHERE (year < {$year}) OR (year = {$year} AND week <= {$week})
                ");
                
                // For finishes - only include up to previous year
                DB::statement("
                    CREATE TEMPORARY TABLE temp_finishes AS
                    SELECT * FROM finishes
                    WHERE year < {$year}
                ");
                
                // Save existing manager fun facts to restore later
                DB::statement("
                    CREATE TEMPORARY TABLE saved_manager_fun_facts
                    AS SELECT * FROM manager_fun_facts
                ");
                
                // Clear the target fun fact so it will be recalculated
                ManagerFunFact::where('fun_fact_id', $funFactId)->delete();
                
                // Rename the original tables to backups and put our filtered tables in place
                // This way the UpdateFunFacts methods will work with our filtered data
                // Using SQLite compatible table renaming syntax
                DB::statement("ALTER TABLE regular_season_matchups RENAME TO original_regular_season_matchups");
                DB::statement("ALTER TABLE playoff_matchups RENAME TO original_playoff_matchups");
                DB::statement("ALTER TABLE draft RENAME TO original_drafts");
                DB::statement("ALTER TABLE rosters RENAME TO original_rosters");
                DB::statement("ALTER TABLE finishes RENAME TO original_finishes");
                
                // Put our temporary tables in place of the actual tables
                DB::statement("ALTER TABLE temp_regular_season_matchups RENAME TO regular_season_matchups");
                DB::statement("ALTER TABLE temp_playoff_matchups RENAME TO playoff_matchups");
                DB::statement("ALTER TABLE temp_drafts RENAME TO draft");
                DB::statement("ALTER TABLE temp_rosters RENAME TO rosters");
                DB::statement("ALTER TABLE temp_finishes RENAME TO finishes");
                
                // Force the UpdateFunFacts job to use our specific year as the current year
                // This is critical to ensure calculations are historically accurate
                $currentSeasonProp = new \ReflectionProperty($updateFunFacts, 'currentSeason');
                $currentSeasonProp->setAccessible(true);
                $originalCurrentSeason = $currentSeasonProp->getValue($updateFunFacts);
                $currentSeasonProp->setValue($updateFunFacts, $year);
                
                $lastSeasonProp = new \ReflectionProperty($updateFunFacts, 'lastSeason');
                $lastSeasonProp->setAccessible(true);
                $originalLastSeason = $lastSeasonProp->getValue($updateFunFacts);
                $lastSeasonProp->setValue($updateFunFacts, $year - 1);
                
                // Also set the current week
                $currentWeekProp = new \ReflectionProperty($updateFunFacts, 'currentWeek');
                $currentWeekProp->setAccessible(true);
                $originalCurrentWeek = $currentWeekProp->getValue($updateFunFacts);
                $currentWeekProp->setValue($updateFunFacts, $week);
                
                // Set historical calculation flag to indicate data is already filtered by table swapping
                $historicalProp = new \ReflectionProperty($updateFunFacts, 'isHistoricalCalculation');
                $historicalProp->setAccessible(true);
                $originalHistorical = $historicalProp->getValue($updateFunFacts);
                $historicalProp->setValue($updateFunFacts, true);
                
                // Execute the method to calculate the stat
                $method = $methodMap[$funFactId];
                $reflection = new \ReflectionMethod($updateFunFacts, $method);
                $reflection->setAccessible(true);
                $reflection->invoke($updateFunFacts);
                
                // Restore the original seasons and week after calculation
                $currentSeasonProp->setValue($updateFunFacts, $originalCurrentSeason);
                $lastSeasonProp->setValue($updateFunFacts, $originalLastSeason);
                $currentWeekProp->setValue($updateFunFacts, $originalCurrentWeek);
                $historicalProp->setValue($updateFunFacts, $originalHistorical);
                
                // Get the newly calculated result(s) - there may be multiple tied leaders
                $results = ManagerFunFact::where('fun_fact_id', $funFactId)->get();
                
                // Store the result(s) to return
                $returnResult = null;
                if ($results->count() > 0) {
                    $returnResult = $results->map(function($result) {
                        return [
                            'manager_id' => $result->manager_id,
                            'value' => $result->value,
                            'note' => $result->note
                        ];
                    })->toArray();
                }
                
                // Restore the original manager fun facts, but don't restore the specific fun fact we just calculated
                DB::statement("DELETE FROM manager_fun_facts WHERE fun_fact_id = {$funFactId}");
                
                // Don't restore the existing fact - we want to keep the historically calculated result
                // If there was an existing fun fact, only restore it if we didn't get a result from our calculation
                if ($existingFact && !$returnResult) {
                    ManagerFunFact::create([
                        'fun_fact_id' => $existingFact->fun_fact_id,
                        'manager_id' => $existingFact->manager_id,
                        'value' => $existingFact->value,
                        'note' => $existingFact->note
                    ]);
                }
                
                // Restore original tables in the correct order
                // Using SQLite compatible table renaming syntax
                DB::statement("ALTER TABLE regular_season_matchups RENAME TO temp_regular_season_matchups");
                DB::statement("ALTER TABLE playoff_matchups RENAME TO temp_playoff_matchups");
                DB::statement("ALTER TABLE draft RENAME TO temp_drafts");
                DB::statement("ALTER TABLE rosters RENAME TO temp_rosters");
                DB::statement("ALTER TABLE finishes RENAME TO temp_finishes");
                
                DB::statement("ALTER TABLE original_regular_season_matchups RENAME TO regular_season_matchups");
                DB::statement("ALTER TABLE original_playoff_matchups RENAME TO playoff_matchups");
                DB::statement("ALTER TABLE original_drafts RENAME TO draft");
                DB::statement("ALTER TABLE original_rosters RENAME TO rosters");
                DB::statement("ALTER TABLE original_finishes RENAME TO finishes");
                
                // Clean up temp tables
                DB::statement("DROP TABLE IF EXISTS temp_regular_season_matchups");
                DB::statement("DROP TABLE IF EXISTS temp_playoff_matchups");
                DB::statement("DROP TABLE IF EXISTS temp_drafts");
                DB::statement("DROP TABLE IF EXISTS temp_rosters");
                DB::statement("DROP TABLE IF EXISTS temp_finishes");
                DB::statement("DROP TABLE IF EXISTS saved_manager_fun_facts");
                
                // Commit the transaction
                DB::commit();
                
                return $returnResult;
            } catch (\Exception $e) {
                // Something went wrong, rollback and log
                DB::rollBack();
                echo ("Error calculating fun fact $funFactId for $year-W$week: " . $e->getMessage());
                echo ($e->getTraceAsString());
                
                // Ensure all original tables are restored
                try {
                    // First check if our renamed tables exist and restore them
                    // Use SQLite syntax instead of MySQL's SHOW TABLES
                    $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name LIKE 'original_%'");
                    
                    if (count($tables) > 0) {
                        // Original tables exist, try to restore them
                        // Using SQLite compatible table renaming syntax
                        if (DB::getSchemaBuilder()->hasTable('original_regular_season_matchups')) {
                            DB::statement("ALTER TABLE original_regular_season_matchups RENAME TO regular_season_matchups");
                        }
                        if (DB::getSchemaBuilder()->hasTable('original_playoff_matchups')) {
                            DB::statement("ALTER TABLE original_playoff_matchups RENAME TO playoff_matchups");
                        }
                        if (DB::getSchemaBuilder()->hasTable('original_drafts')) {
                            DB::statement("ALTER TABLE original_drafts RENAME TO draft");
                        }
                        if (DB::getSchemaBuilder()->hasTable('original_rosters')) {
                            DB::statement("ALTER TABLE original_rosters RENAME TO rosters");
                        }
                        if (DB::getSchemaBuilder()->hasTable('original_finishes')) {
                            DB::statement("ALTER TABLE original_finishes RENAME TO finishes");
                        }
                    }
                    
                    // Clean up any temporary tables
                    DB::statement("DROP TABLE IF EXISTS temp_regular_season_matchups");
                    DB::statement("DROP TABLE IF EXISTS temp_playoff_matchups");
                    DB::statement("DROP TABLE IF EXISTS temp_drafts");
                    DB::statement("DROP TABLE IF EXISTS temp_rosters");
                    DB::statement("DROP TABLE IF EXISTS temp_finishes");
                    DB::statement("DROP TABLE IF EXISTS saved_manager_fun_facts");
                } catch (\Exception $restoreError) {
                    echo ("Error restoring original tables: " . $restoreError->getMessage());
                    echo ("!!! DATABASE MAY BE IN AN INCONSISTENT STATE - MANUAL RECOVERY MAY BE NEEDED !!!");
                }
            }
        }
        
        return null;
    }
    
    /**
     * Get the current week of the fantasy football season
     */
    protected function getCurrentWeek()
    {
        // Simple calculation - NFL season typically starts around week 36 of the year
        $currentWeekOfYear = (int)date('W');
        $nflStartWeek = 36; // Approximately week 36
        
        $week = $currentWeekOfYear - $nflStartWeek + 1;
        
        // Ensure week is within valid range
        if ($week < 1) {
            $week = 1;
        } elseif ($week > 17) {
            $week = 17;
        }
        
        return $week;
    }

    /**
     * Run test mode for debugging
     */
    protected function runTestMode()
    {
        echo "RUNNING IN TEST MODE - Debugging historical calculations" . PHP_EOL;
        
        // Test Year and Week
        $testYear = $this->year ?: 2012;
        $testWeek = $this->week ?: 10;
        
        echo "Testing with Year: $testYear, Week: $testWeek" . PHP_EOL;
        
        // Get a fun fact to test with
        $funFact = FunFact::find(1); // Most Points For (All Time)
        if (!$funFact) {
            echo "ERROR: Could not find fun fact with ID 1" . PHP_EOL;
            return;
        }
        
        echo "Testing Fun Fact: {$funFact->id} - {$funFact->name}" . PHP_EOL;
        
        // Get the leader at this point in time
        $updateFunFacts = new UpdateFunFacts($testYear, $testWeek);
        
        // Direct invoke of mostPointsFor()
        $reflection = new \ReflectionClass($updateFunFacts);
        $method = $reflection->getMethod('mostPointsFor');
        $method->setAccessible(true);
        
        echo "Invoking mostPointsFor() method with historical context..." . PHP_EOL;
        $method->invoke($updateFunFacts);
        
        // Now check the manager_fun_facts table for the result
        $factResult = DB::table('manager_fun_facts')
            ->where('fun_fact_id', 1)
            ->first();
            
        if ($factResult) {
            $manager = DB::table('managers')
                ->where('id', $factResult->manager_id)
                ->first();
                
            echo "RESULT: Fun Fact ID 1 as of {$testYear}-W{$testWeek}" . PHP_EOL;
            echo "Leader: " . ($manager ? $manager->name : "Unknown ({$factResult->manager_id})") . PHP_EOL;
            echo "Value: {$factResult->value}" . PHP_EOL;
            echo "Note: {$factResult->note}" . PHP_EOL;
        } else {
            echo "No result found for fun fact ID 1" . PHP_EOL;
        }
        
        echo "Test completed." . PHP_EOL;
    }
    
    /**
     * Test fun fact ID 168 specifically (Most QB picks in first round)
     */
    /**
     * Call the trackTopPositionPerformances method in UpdateFunFacts
     * This covers fun facts 147-164 related to weekly position performances
     */
    public function weeklyPositionPerformance()
    {
        return $this->trackTopPositionPerformances();
    }
    
    /**
     * Call the trackTopPositionPerformances method in UpdateFunFacts for IDs 147-164
     */
    protected function trackTopPositionPerformances()
    {
        $updateFunFacts = new UpdateFunFacts($this->currentYear, $this->currentWeek);
        $updateFunFacts->trackTopPositionPerformances();
    }

    /**
     * Determine the playoff round name based on year and week
     */
    protected function getPlayoffRound($year, $week)
    {
        $playoffStartWeek = ($year >= 2021) ? 15 : 14;
        
        if ($week < $playoffStartWeek) {
            return (string)$week; // Regular season, return week number as string
        }
        
        // Playoff weeks - determine round
        $weeksSincePlayoffStart = $week - $playoffStartWeek;
        
        switch ($weeksSincePlayoffStart) {
            case 0:
                return 'Quarterfinal';
            case 1:
                return 'Semifinal';
            case 2:
                return 'Final';
            default:
                return (string)$week; // Fallback to week number
        }
    }

    /**
     * Get the previous week/round for new leader comparison
     */
    protected function getPreviousWeekOrRound($year, $week)
    {
        $playoffStartWeek = ($year >= 2021) ? 15 : 14;
        
        if ($week < $playoffStartWeek) {
            // Regular season - just subtract 1
            return $week > 1 ? (string)($week - 1) : null;
        }
        
        // Playoff weeks - determine previous round
        $weeksSincePlayoffStart = $week - $playoffStartWeek;
        
        switch ($weeksSincePlayoffStart) {
            case 0:
                // Quarterfinal - previous is the last regular season week
                return (string)($playoffStartWeek - 1);
            case 1:
                // Semifinal - previous is Quarterfinal
                return 'Quarterfinal';
            case 2:
                // Final - previous is Semifinal
                return 'Semifinal';
            default:
                return null;
        }
    }

}