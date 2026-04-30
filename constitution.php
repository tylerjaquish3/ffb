<?php

$pageName = "Constitution";
include 'header.php';
include 'sidebar.php';

$allYears = [];
$result = query("SELECT distinct year FROM season_positions ORDER BY YEAR ASC");
while ($row = fetch_array($result)) {
    $allYears[] = $row['year'];
}

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">

        <div class="content-body">

            <!-- Hero Banner -->
            <div class="page-hero">
                <img src="/images/full-logo.jpeg" alt="Suntown FFB" class="page-hero-logo">
            </div>

            <!-- Tabs Navigation -->
            <div class="row mb-1">
                <div class="col-sm-12">
                    <div class="tab-buttons-container">
                        <button class="tab-button active" id="league-info-tab" onclick="showCard('league-info')">
                            League Info
                        </button>
                        <button class="tab-button" id="draft-tab" onclick="showCard('draft')">
                            Draft
                        </button>
                        <button class="tab-button" id="league-rules-tab" onclick="showCard('league-rules')">
                            League Rules
                        </button>
                        <button class="tab-button" id="league-settings-tab" onclick="showCard('league-settings')">
                            League Settings
                        </button>
                        <button class="tab-button" id="roster-history-tab" onclick="showCard('roster-history')">
                            Roster Position History
                        </button>
                        <button class="tab-button" id="meeting-notes-tab" onclick="showCard('meeting-notes')">
                            Meeting Notes
                        </button>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="league-info">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>League Info</h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block" style="direction: ltr">
                                <p>We, the members of Suntown Fantasy Football League, hereby establish this Constitution to govern our association — including the heathens who don't hail from Sunnyside, WA.</p>

                                <p>The league kicked off in 2004 with six founding members: Gavin, Tyler, AJ, Andy, Everett, and Ben. Cole and Matt joined in 2005, bringing it to eight. In 2006, Justin came aboard as Andy stepped out (temporarily — he'd return in 2008 along with Cameron, rounding the league out to its current ten-man format). The league trophy was introduced that same year, so all records on this site start in 2006.</p>

                                <p>This is a free, head-to-head league hosted on Yahoo — no money, no buy-in. The only hardware is a physical trophy and an engraved plaque added each year for the champion, who gets to keep the trophy until someone takes it from them. Last place isn't without consequence, though: the 10th-place finisher pays for that plaque and gets their team name assigned by the champion for the following season.</p>

                                <p>Weekly matchups are scheduled using historical data to ensure every manager faces all opponents as evenly as possible over the course of a season.</p>

                                <p>This site exists to document the history of the league — the wins, the losses, the bad trades, and the bragging rights. Browse around.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="draft" style="display: none;">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Draft</h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block" style="direction: ltr">
                                <p>The fantasy football draft is the single most important day of the fantasy football season, but the league is not won at the draft.
                                Our draft is held on or around the end of August, generally one week before the kickoff of the NFL season.</p>

                                <p>Approximately 4 weeks before the draft, the draft order is determined in a unique but random fashion. This is created by the commissioner
                                and recorded for video evidence, with no retakes or edits in order to preserve authenticity and integrity. For instance, when the dog ran
                                away with the tennis ball with Gavin's name on it, the video continued uncut. The draft order will always be determined with complete randomness,
                                giving every manager an equal chance at getting the #1 pick.</p>

                                <p>The draft shall always be of "snake" orientation and draft grades given by Yahoo shall always be considered 100% accurate and reliable.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="league-rules" style="display: none;">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>League Rules</h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block" style="direction: ltr">
                                <ul>
                                    <li>No collusion amongst teams in regards to trades or determining the outcome of a matchup. This includes intentional losing to effect the league standings.</li>
                                    <li>No trade "rentals" where the same trade is reversed.</li>
                                    <li>No trading if you're mathematically eliminated from postseason as this is considered collusion.</li>
                                    <li>Trades have a 1 day period where they can be reviewed/vetoed by the league.</li>
                                    <li>Smack talk is encouraged, especially if it hurts feelings.</li>
                                    <li>Players added and dropped in the same day shall return to Free Agent status (not Waivers).</li>
                                    <li>Waivers are decided using FAB system and each team starts the year with $200.</li>
                                    <li>Managers are allowed to include FAB dollars in trades, but they must report the agreed upon value to the commissioner to make the appropriate adjustments.</li> 
                                    <li>Rules/settings can be changed with a majority vote. However, in-season changes require a super-majority (80% yea).</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="league-settings" style="display: none;">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>League Settings</h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block" style="direction: ltr">
                                <ul>
                                    <li>Waiver Time: 3 days</li>
                                    <li>Waiver Type: FAB w/ Reverse order of standings tie-breaker</li>
                                    <li>Weekly Waivers:	Game Time - Tuesday</li>
                                    <li>Post Draft Players:	Follow Waiver Rules</li>
                                    <li>Playoffs: Week 15, 16, and 17 (6 teams)</li>
                                    <li>Playoff Tie-Breaker: Best regular season record vs opponent</li>
                                    <li>Playoff Reseeding: Yes</li>
                                    <li>Divisions: No</li>
                                    <li>Roster Positions: QB, WR, WR, WR, RB, RB, TE, W/R/T, Q/W/R/T, K, DEF, BN, BN, BN, BN, BN, BN, IR, IR</li>
                                    <li>Fractional Points: Yes</li>
                                    <li>Negative Points: Yes</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="roster-history" style="display: none;">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Roster Position History</h4>
                        </div>
                        <div class="card-body" style="padding: 0;">
                            <table id="datatable-settings" class="table table-striped nowrap" style="width: 100%;">
                                <thead>
                                    <th>Count</th>
                                    <?php
                                    foreach ($allYears as $year) {
                                        echo "<th>".$year."</th>";
                                    } ?>
                                </thead>
                                <tbody>
                                    <?php
                                    
                                    $result = query("SELECT * FROM season_positions ORDER BY sort_order ASC");
                                    while ($row = fetch_array($result)) {
                                        $spots[$row['year']][] =  $row['position'];
                                    }

                                    for ($i = 0; $i < 25; $i++) {
                                        echo '<tr>';
                                            echo '<td>'.($i+1).'</td>';
                                            foreach ($allYears as $year) {
                                                if (isset($spots[$year][$i])) {
                                                    $order = $i+1;
                                                    echo "<td data-order='".$order."'>".$spots[$year][$i]."</td>";
                                                } else {
                                                    echo "<td data-order='99'></td>";
                                                }
                                            }
                                        echo '</tr>';
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="meeting-notes" style="display: none;">
                <div class="col-sm-12">

                    <!-- Meeting Agenda TBD 2026 -->
                    <div class="card">
                        <div class="card-header">
                            <h4>Meeting Agenda | TBD 2026</h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block" style="direction: ltr">
                                <br />
                                <strong>Vote on the following: </strong>
                                <ol>
                                    <li>Add stat category for Pick 6s Thrown worth -4 points. Burrow had 3, 2 QBs had 2, and 20 QBs had 1.</li>
                                    <li>Increase points for DEF turnovers &amp; sacks.
                                        <div class="table-responsive mt-2 mb-2">
                                            <table class="table table-sm table-bordered text-center small" style="max-width: 320px; line-height: 1.2;">
                                                <thead class="thead-light">
                                                    <tr><th>Category</th><th>2025</th><th>2026</th></tr>
                                                </thead>
                                                <tbody>
                                                    <tr><td>Sack</td><td>0.5</td><td>0.5</td></tr>
                                                    <tr class="table-warning"><td>Interception</td><td>1</td><td><strong>2</strong></td></tr>
                                                    <tr class="table-warning"><td>Fumble Recovery</td><td>1</td><td><strong>2</strong></td></tr>
                                                    <tr class="table-warning"><td>Safety</td><td>2</td><td><strong>4</strong></td></tr>
                                                    <tr class="table-warning"><td>Block Kick</td><td>1</td><td><strong>4</strong></td></tr>
                                                    <tr class="table-warning"><td>4th Down Stop</td><td>1</td><td><strong>2</strong></td></tr>
                                                    <tr><td>3 and Out</td><td>0.5</td><td>0.5</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        How DEF compares to other positions (2025):
                                        <div class="table-responsive mt-2 mb-2">
                                            <table class="table table-sm table-bordered text-center small" style="max-width: 380px; line-height: 1.2;">
                                                <thead class="thead-light">
                                                    <tr><th>Pos</th><th>#1</th><th>#25</th><th>Avg</th></tr>
                                                </thead>
                                                <tbody>
                                                    <tr><td>QB</td><td>361</td><td>159</td><td>257</td></tr>
                                                    <tr><td>RB</td><td>365</td><td>161</td><td>237</td></tr>
                                                    <tr><td>WR</td><td>309</td><td>163</td><td>198</td></tr>
                                                    <tr class="table-danger"><td>DEF now</td><td>243</td><td>95</td><td>156</td></tr>
                                                    <tr class="table-success"><td>DEF prop.</td><td>299</td><td>129</td><td>196</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </li>
                                </ol>
                                <strong>Determine 2026 draft order</strong><br />
                                <br />
                                <strong>Draft date: 8.29.26 | Time: TBD | Location: Cole's</strong>
                                <br /><br />
                            </div>
                        </div>
                    </div>

                    <!-- 2025 -->
                    <div class="card">
                        <div class="card-header">
                            <h4><a href="/year20">Twenty Year Anniversary</a></h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block" style="direction: ltr">
                                <strong>No rule changes/voting necessary.</strong><br /><br />
                                <strong>Determined 2025 draft order with challenges</strong><br />
                                <ol>
                                    <li>Mysterious Body | Winner: Justin</li>
                                    <li>The Art of the Tortilla | Winner: Cole</li>
                                    <li>Pink Flag Rally | Winner: Cam & Tyler</li>
                                    <li>I Scream Challenge | Winner: Ben</li>
                                    <li>FG Face Off | Winner: Tyler</li>
                                </ol>
                                Overall winner: Justin
                                <br />
                                <strong>Draft date: 8.24.25 | Time: 11:00am | Location: Tyler's</strong>
                                <br /><br />
                            </div>
                        </div>
                    </div>

                    <!-- Meeting Agenda 7.24.24 -->
                    <div class="card">
                        <div class="card-header">
                            <h4>Meeting Agenda | 7.24.24</h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block" style="direction: ltr">
                                <br />
                                <strong>Voted on the following: </strong>
                                <ol>
                                    <li>Add an 18th roster position (BN or W/R/T) | <strong>Voted to keep as-is (vote was 5-5).</strong></li>
                                    <li>Push trades through right away | <strong>Voted to change to 1 day vote/veto period (vote was 9-0).</strong></li>
                                    <li>Able to trade FAB dollars | <strong>Voted yes to trading FAB (vote was 9-0).</strong></li>
                                    <li>Add Punt/Kick Return TDs to DEF | <strong>Voted to keep as-is (vote was 5-3).</strong></li>
                                    <li>Replacing toilet seat as punishment | <strong>Voted to add T-shirt for loser to wear to draft event (vote was 7-5).</strong></li>
                                </ol>
                                <strong>Determined 2024 draft order</strong><br />
                                <a href="https://youtu.be/GswkYPesTJk" target="_blank">Watch Video Here</a>
                                <br /><br />
                                <strong>Draft date: 8.25.24 | Time: 3:30pm | Location: AJ's</strong>
                                <br /><br />
                            </div>
                        </div>
                    </div>

                    <!-- Meeting Agenda 8.2.2023 -->
                    <div class="card">
                        <div class="card-header">
                            <h4>Meeting Agenda | 8.2.2023</h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block" style="direction: ltr">
                                <br />
                                <strong>Voted on the following:</strong>
                                <ol>
                                    <li>Keep FAB system. | <strong>Voted to keep FAB system as-is (vote was 6-0).</strong></li>
                                    <li>Remove negative kicker points | <strong>Voted to reduce the negative kicker points to the following (vote was 4-1):</strong><br />
                                    <ul><li>0-19 yds: -3 pts</li><li>20-29 yds: -2 pts</li><li>30-39 yds: -1 pts</li></ul></li>
                                </ol>
                                <strong>Determined 2023 draft order (bingo balls determined who picked their draft spot next)</strong>
                                <br /><br />
                            
                                <strong>Draft date: 8.27.23 | Time: 2pm | Location: Justin's</strong>
                                <br /><br />
                            </div>
                        </div>
                    </div>

                    <!-- Meeting Agenda 7.26.2022 -->
                    <div class="card">
                        <div class="card-header">
                            <h4>Meeting Agenda | 7.26.2022</h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block" style="direction: ltr">
                                <br />
                                <strong>Voted on the following:</strong>
                                <ol>
                                    <li>Change waiver settings to use FAB system. | <strong>Vote passed to change to FAB system (vote was 6-3).</strong> More info below</li>
                                    <li>Change number of IR positions to 1 or 0 | <strong>Voted to keep as-is with 2 IR spots (vote was 5-3).</strong></li>
                                </ol>
                                <strong>Determined 2022 draft order:</strong><br />

                                <a href="https://www.cameo.com/recipient/62dac9a54baeecb8a4f7d7ce?from_share_sheet=1&utm_campaign=video_share_to_copy">Watch Dean Blandino Video Here</a>
                                <br /><br />
                                <strong>Draft date: 9.5.22 | Time: 3pm | Location: Everett's</strong>
                                <br /><br />
                                FAB info:<br />
                                <ul>
                                    <li>In a Free Agents Budgets (FAB) waivers system, each manager receives a dollar amount to place blind offers on waived players throughout the season. 
                                        The manager with the highest offer at the end of the waiver period claims that player, and that offer amount is deducted from the team's free agent budget for the season. 
                                    </li>
                                    <li>If multiple managers place an equal offer on a player, tiebreaker goes by reverse order of standings.</li>
                                    <li>The FAB system charges you the exact amount you bid if your bid is successful. It will never automatically adjust your bid to beat any other bids by $1.</li>
                                    <li>Each manager starts the season with a $200 budget.</li>
                                    <li>FAB does not apply to free-agent pickups. All free agents can be picked up on a first-come, first-served basis, without bids. Just like before, players become free agents after the waiver period ends.</li>
                                    <li>Minimum bid is $0.</li>
                                    <li>Just like before, you can update/change/cancel your bid on a player on your team page before they process.</li>
                                    <li>Waiver deadlines and periods remain the same (Game time - Tuesday).</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Meeting Agenda 8.3.2021 -->
                    <div class="card">
                        <div class="card-header">
                            <h4>Meeting Agenda | 8.3.2021</h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block" style="direction: ltr">
                                <br />
                                <strong>Voted on the following:</strong>
                                <ol>
                                    <li>Include/exclude IDP positions and how many? | <strong>Voted to keep as-is.</strong></li>
                                    <li>Keep the superflex position (Q/W/R/T) or go back to QB spot? | <strong>Voted to keep as-is.</strong></li>
                                    <li>Keep 4 IR positions or fewer? | <strong>Voted to change to 2 IR positions.</strong></li>
                                    <li>Team defense points awarded | <strong>Voted to add points for prop A (yards allowed). Voted to keep prop B (turnover points) as-is.</strong></li>
                                    <li>Field goal yards per point | <strong>Voted to change to YPP stat category and remove ranges.</strong></li>
                                    <li>Add license plate frame for loser? | <strong>Voted to make loser sit on a toilet during draft and their team name shall be set by the champion.</strong></li>
                                    <li>Change to 2 team DEF positions | <strong>Voted to keep as-is.</strong></li>
                                    <li>Remove Justin from the league | <strong>Voted unanimously.</strong></li>
                                </ol>
                                <strong>Determined 2021 draft order</strong><br />

                                <strong>Draft date: 9.6.21 | Time: 4pm | Location: Andy's</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script type="text/javascript">

    let settingsTable = $('#datatable-settings').DataTable({
        searching: false,
        paging: false,
        info: false,
        autoWidth: false,
        scrollX: "100%",
        scrollCollapse: true
    });

    // Initialize the page with League Info tab active
    document.addEventListener('DOMContentLoaded', function() {
        showCard('league-info');
    });

</script>