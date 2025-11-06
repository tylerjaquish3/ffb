<?php
$pageName = 'Bid Analysis';
include 'header.php';
include 'sidebar.php';

// Function to parse the bid data
function parseBidData($filePath) {
    if (!file_exists($filePath)) {
        return [];
    }
    
    $content = file_get_contents($filePath);
    $entries = explode('-------------------------------------', $content);
    $bids = [];
    
    foreach ($entries as $entry) {
        $entry = trim($entry);
        if (empty($entry)) continue;
        
        $lines = explode("\n", $entry);
        $lines = array_map('trim', array_filter($lines)); // Remove empty lines and trim
        
        if (count($lines) >= 4) {
            $player = $lines[0];
            
            // Extract dollar amount from the bid line (e.g., "$25 Waiver" -> "25")
            $bidLine = $lines[1];
            preg_match('/\$(\d+)/', $bidLine, $matches);
            $amount = isset($matches[1]) ? intval($matches[1]) : 0;
            
            $manager = $lines[2];
            $date = $lines[3];
            
            // Convert date to a more sortable format
            $dateObj = DateTime::createFromFormat('M j, g:i a', $date);
            $sortableDate = $dateObj ? $dateObj->format('Y-m-d H:i:s') : $date;
            
            $bids[] = [
                'player' => $player,
                'amount' => $amount,
                'manager' => $manager,
                'date' => $date,
                'sortable_date' => $sortableDate
            ];
        }
    }
    
    // Sort by date (most recent first)
    usort($bids, function($a, $b) {
        return strcmp($b['sortable_date'], $a['sortable_date']);
    });
    
    return $bids;
}

// Parse competitive bids first
$competitiveBids = parseCompetitiveBids(__DIR__ . '/parsing/competitive bids.txt');

// Parse the bid data from the desktop file
$bidData = parseBidData(__DIR__ . '/parsing/all bids.txt');

// Add $0 winning bids from competitive bids.txt if missing
foreach ($competitiveBids as $compKey => $comp) {
    $parts = explode('|', $compKey);
    if (count($parts) < 3) continue;
    $player = trim($parts[0]);
    $manager = trim($parts[1]);
    $date = trim($parts[2]);
    $alreadyExists = false;
    foreach ($bidData as $bid) {
        if (
            trim($bid['player']) == trim($player) &&
            trim($bid['manager']) == trim($manager) &&
            str_replace(' ', '', trim($bid['date'])) == str_replace(' ', '', trim($date))
        ) {
            $alreadyExists = true;
            break;
        }
    }
    if (!$alreadyExists && isset($comp['winningAmount']) && $comp['winningAmount'] == 0) {
        // Parse date to sortable format
        $dateObj = DateTime::createFromFormat('M j, g:i a', $date);
        $sortableDate = $dateObj ? $dateObj->format('Y-m-d H:i:s') : $date;
        $bidData[] = [
            'player' => $player,
            'amount' => 0,
            'manager' => $manager,
            'date' => $date,
            'sortable_date' => $sortableDate,
            'overspend' => $comp['overspend'],
            'bid_count' => isset($comp['bidManagers']) ? count($comp['bidManagers']) : 1
        ];
    }
}

// Parse competitive bids for overspend calculation
// Parse competitive bids for overspend calculation
function parseCompetitiveBids($filePath) {
    if (!file_exists($filePath)) return [];
    $content = file_get_contents($filePath);
    $entries = explode('----------------------------------------------', $content);
    $compBids = [];
    foreach ($entries as $entry) {
        $entry = trim($entry);
        if (empty($entry)) continue;
        $lines = array_map('trim', array_filter(explode("\n", $entry)));
        if (count($lines) < 5) continue;
        $player = $lines[0];
        $winningLine = $lines[1];
        preg_match('/\$(\d+)/', $winningLine, $winMatch);
        $winningAmount = isset($winMatch[1]) ? intval($winMatch[1]) : 0;
        $bidManagers = [];
        $awardedTo = '';
        $date = '';
        for ($i = 2; $i < count($lines); $i++) {
            if (isset($lines[$i]) && is_string($lines[$i]) && strpos($lines[$i], 'Awarded To:') !== false) {
                $awardedTo = isset($lines[$i+1]) ? trim($lines[$i+1]) : '';
                $date = isset($lines[$i+2]) ? trim($lines[$i+2]) : '';
                break;
            }
            // Parse lower offer or lower waiver priority
            if (isset($lines[$i]) && is_string($lines[$i]) && preg_match('/^(.*?) \$\d+ \((Lower Offer|Lower waiver priority)\)/', $lines[$i], $offerMatch)) {
                $bidManagers[] = trim($offerMatch[1]);
            }
        }
        // Add awarded manager
        if ($awardedTo) {
            $bidManagers[] = $awardedTo;
        }
        // Find next highest bid
        $lowerOffers = [];
        for ($i = 2; $i < count($lines); $i++) {
            if (isset($lines[$i]) && is_string($lines[$i]) && preg_match('/^(.*?) \$(\d+)/', $lines[$i], $offerMatch)) {
                $lowerOffers[] = intval($offerMatch[2]);
            }
        }
        $nextBid = 0;
        if (count($lowerOffers) > 0) {
            rsort($lowerOffers);
            $nextBid = $lowerOffers[0];
        }
        $key = $player . '|' . $awardedTo . '|' . $date;
        $compBids[$key] = [
            'winningAmount' => $winningAmount,
            'nextBid' => $nextBid,
            'overspend' => $winningAmount - $nextBid,
            'bidManagers' => $bidManagers
        ];
    }
    return $compBids;
}

$competitiveBids = parseCompetitiveBids(__DIR__ . '/parsing/competitive bids.txt');

// Attach overspend to each bid
foreach ($bidData as &$bid) {
    // Try to match by player, manager, and date
    $found = false;
    foreach ($competitiveBids as $compKey => $comp) {
        $parts = explode('|', $compKey);
        if (count($parts) < 3) continue;
        $compPlayer = trim($parts[0]);
        $compManager = trim($parts[1]);
        $compDate = str_replace(' ', '', trim($parts[2]));
        $bidPlayer = isset($bid['player']) ? trim($bid['player']) : '';
        $bidManager = isset($bid['manager']) ? trim($bid['manager']) : '';
        $bidDate = isset($bid['date']) ? str_replace(' ', '', trim($bid['date'])) : '';
        if (
            $bidPlayer == $compPlayer &&
            $bidManager == $compManager &&
            $bidDate !== '' && $compDate !== '' && $bidDate == $compDate
        ) {
            $bid['overspend'] = $comp['overspend'];
            $bid['bid_count'] = isset($comp['bidManagers']) ? count($comp['bidManagers']) : 1;
            $found = true;
            break;
        }
    }
    if (!isset($bid['bid_count'])) {
        $bid['bid_count'] = 1;
    }
    if (!$found) {
        $bid['overspend'] = $bid['amount'];
    }
}
unset($bid);

// Calculate some statistics
$totalBids = count($bidData);
$totalSpent = array_sum(array_column($bidData, 'amount'));
$managerTotals = [];
$playerCounts = [];

// Track total overspend per manager
$managerOverspend = [];
foreach ($bidData as $bid) {
    // Manager spending totals
    if (!isset($managerTotals[$bid['manager']])) {
        $managerTotals[$bid['manager']] = 0;
    }
    $managerTotals[$bid['manager']] += $bid['amount'];
    // Count bids per manager
    if (!isset($playerCounts[$bid['manager']])) {
        $playerCounts[$bid['manager']] = 0;
    }
    $playerCounts[$bid['manager']]++;
    // Track overspend
    if (!isset($managerOverspend[$bid['manager']])) {
        $managerOverspend[$bid['manager']] = 0;
    }
    $managerOverspend[$bid['manager']] += $bid['overspend'];
}

// Sort managers by total spending
arsort($managerTotals);
?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">

        <div class="content-body">
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                    <i class="icon-grid font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green-ffb media-body">
                                    <h5>Total Bids</h5>
                                    <h5 class="text-bold-400"><?php echo $totalBids; ?></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                    <i class="icon-dollar font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green-ffb media-body">
                                    <h5>Total Spent</h5>
                                    <h5 class="text-bold-400">$<?php echo number_format($totalSpent); ?></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                    <i class="icon-calculator font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green-ffb media-body">
                                    <h5>Average Bid</h5>
                                    <h5 class="text-bold-400">$<?php echo $totalBids > 0 ? number_format($totalSpent / $totalBids, 2) : '0'; ?></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                    <i class="icon-users font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green-ffb media-body">
                                    <h5>Active Managers</h5>
                                    <h5 class="text-bold-400"><?php echo count($managerTotals); ?></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manager Spending Summary -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Manager Spending Summary</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="managerSummaryTable">
                                    <thead>
                                        <tr>
                                            <th>Manager</th>
                                            <th>Total Spent</th>
                                            <th>Total Overspend</th>
                                            <th>Number of Bids</th>
                                            <th>Avg Bid</th>
                                            <th>Avg Overspend</th>
                                            <th>Overspend %</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($managerTotals as $manager => $total): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($manager); ?></strong></td>
                                            <td>$<?php echo number_format($total); ?></td>
                                            <td>$<?php echo isset($managerOverspend[$manager]) ? number_format($managerOverspend[$manager]) : '0'; ?></td>
                                            <td><?php echo $playerCounts[$manager]; ?></td>
                                            <td>$<?php echo number_format($total / $playerCounts[$manager], 2); ?></td>
                                            <td>$<?php echo ($playerCounts[$manager] > 0 && isset($managerOverspend[$manager])) ? number_format($managerOverspend[$manager] / $playerCounts[$manager], 2) : '0.00'; ?></td>
                                            <td><?php echo ($total > 0 && isset($managerOverspend[$manager])) ? number_format(100 * $managerOverspend[$manager] / $total, 1) . '%' : '0.00%'; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- All Bids Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">All Bids</h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <div class="table-responsive">
                                <table class="table table-striped" id="bidsTable">
                                    <thead>
                                        <tr>
                                            <th>Manager</th>
                                            <th>Amount</th>
                                            <th>Overspend</th>
                                            <th># Bids</th>
                                            <th>Player</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bidData as $bid): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($bid['manager']); ?></strong></td>
                                            <td class="text-right">$<?php echo number_format($bid['amount']); ?></td>
                                            <td class="text-right">$<?php echo number_format($bid['overspend']); ?></td>
                                            <td class="text-center"><?php echo $bid['bid_count']; ?></td>
                                            <td><?php echo htmlspecialchars($bid['player']); ?></td>
                                            <td data-sort="<?php echo htmlspecialchars($bid['sortable_date']); ?>"><?php echo htmlspecialchars($bid['date']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
$(document).ready(function() {

    $('#managerSummaryTable').DataTable({
        "paging": false,
        "searching": false,
        "info": false,
        "ordering": true
    });

    $('#bidsTable').DataTable({
        "pageLength": 25,
        "order": [[ 5, "desc" ]],
        "columnDefs": [
            {
                "targets": 1, // Amount column
                "type": "num-fmt"
            }
        ]
    });
});
</script>
