<?php

require_once 'functions.php';

echo "Starting to populate standings table...\n";

// First, clear any existing standings data
query("DELETE FROM standings");
echo "Cleared existing standings data.\n";

// Get all distinct years and weeks from regular_season_matchups
$yearsResult = query("SELECT DISTINCT year FROM regular_season_matchups ORDER BY year");
$years = [];
while ($yearRow = fetch_array($yearsResult)) {
    $years[] = $yearRow['year'];
}

echo "Found years: " . implode(', ', $years) . "\n";

foreach ($years as $year) {
    echo "\nProcessing year $year...\n";
    
    // Get all weeks for this year
    $weeksResult = query("SELECT DISTINCT week_number FROM regular_season_matchups WHERE year = $year ORDER BY week_number");
    $weeks = [];
    while ($weekRow = fetch_array($weeksResult)) {
        $weeks[] = $weekRow['week_number'];
    }
    
    echo "Found weeks for $year: " . implode(', ', $weeks) . "\n";
    
    foreach ($weeks as $week) {
        echo "Processing year $year, week $week...\n";
        
        // Calculate cumulative standings through this week using modified weekStandings logic
        $standings = [];
        
        // Initialize standings for all managers (assuming 10 managers)
        for ($x = 1; $x <= 10; $x++) {
            $standings[$x] = [
                'manager_id' => $x, 
                'wins' => 0, 
                'losses' => 0, 
                'points' => 0, 
                'name' => ''
            ];
        }
        
        // Get all matchups through current week - avoid duplicates by using manager1_id < manager2_id
        $result = query("SELECT rsm.*, m1.name as manager1_name, m2.name as manager2_name 
                        FROM regular_season_matchups rsm
                        JOIN managers m1 ON rsm.manager1_id = m1.id
                        JOIN managers m2 ON rsm.manager2_id = m2.id
                        WHERE rsm.year = $year AND rsm.week_number <= $week AND rsm.manager1_id < rsm.manager2_id
                        ORDER BY rsm.week_number");
        
        while ($row = fetch_array($result)) {
            // Process manager1
            if (isset($standings[$row['manager1_id']])) {
                if ($row['winning_manager_id'] == $row['manager1_id']) {
                    $standings[$row['manager1_id']]['wins']++;
                } else {
                    $standings[$row['manager1_id']]['losses']++;
                }
                $standings[$row['manager1_id']]['name'] = $row['manager1_name'];
                $standings[$row['manager1_id']]['points'] += $row['manager1_score'];
            }
            
            // Process manager2 in the same loop
            if (isset($standings[$row['manager2_id']])) {
                if ($row['winning_manager_id'] == $row['manager2_id']) {
                    $standings[$row['manager2_id']]['wins']++;
                } else {
                    $standings[$row['manager2_id']]['losses']++;
                }
                $standings[$row['manager2_id']]['name'] = $row['manager2_name'];
                $standings[$row['manager2_id']]['points'] += $row['manager2_score'];
            }
        }
        
        // Remove managers with no data (empty name)
        $standings = array_filter($standings, function($standing) {
            return !empty($standing['name']);
        });
        
        // Sort by wins (desc) then points (desc) to determine rankings
        usort($standings, function($a, $b) { 
            $winDiff = $b['wins'] - $a['wins'];
            if ($winDiff != 0) return $winDiff; 
            
            return $b['points'] <=> $a['points'];
        });
        
        // Insert standings into database with wins and losses
        $rank = 1;
        foreach ($standings as $standing) {
            $managerId = $standing['manager_id'];
            $points = round($standing['points'], 2);
            $wins = $standing['wins'];
            $losses = $standing['losses'];
            
            $insertSql = "INSERT INTO standings (year, week, manager_id, rank, points, wins, losses) 
                         VALUES ($year, $week, $managerId, $rank, $points, $wins, $losses)";
            
            $insertResult = query($insertSql);
            if (!$insertResult) {
                echo "Error inserting standing for manager {$standing['name']} (ID: $managerId) in $year week $week\n";
            }
            
            $rank++;
        }
        
        echo "Inserted standings for " . count($standings) . " managers in $year week $week\n";
    }
}

echo "\nFinished populating standings table!\n";

// Display some sample data to verify
echo "\nSample standings data:\n";
$sampleResult = query("SELECT s.*, m.name as manager_name 
                      FROM standings s 
                      JOIN managers m ON s.manager_id = m.id 
                      ORDER BY s.year DESC, s.week DESC, s.rank ASC 
                      LIMIT 10");

echo "Year\tWeek\tRank\tManager\t\tWins\tLosses\tPoints\n";
echo "----\t----\t----\t-------\t\t----\t------\t------\n";
while ($row = fetch_array($sampleResult)) {
    echo "{$row['year']}\t{$row['week']}\t{$row['rank']}\t{$row['manager_name']}\t\t{$row['wins']}\t{$row['losses']}\t{$row['points']}\n";
}

echo "\nDone!\n";

?>