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
                                <ol style="padding-left: 0; list-style-position: inside;">
                                    <li>Proposal: Add stat category for "Pick 6s Thrown" worth -4 points.
                                        <ul>
                                            <li>2025: Burrow had 3, Flacco & Stafford had 2, and 20 QBs had 1.</li>
                                            <li>2024: Levis had 4, Lock had 3, 4 QBs had 2, and 13 QBs had 1.</li>
                                            <li>This would be in addition to an Interception worth -2 pts. So a Pick 6 would really be -6 pts for the QB.</li>
                                            <li>Set to -4 points because anything less wouldn't be meaningful. </li>
                                            <li>Pro: Won't make a big impact on the season, but could be a fun wrinkle in a weekly matchup.</li>
                                            <li>Con: Could impact pocket passers more likely than running QBs, which already have an edge.</li>
                                        </ul>
                                    </li>
                                    <li>Proposal: Increase points for DEF turnovers as follows:
                                        <div class="table-responsive mt-2 mb-2">
                                            <table class="table table-sm table-bordered text-center small" style="max-width: 320px; line-height: 1.2;">
                                                <thead class="thead-light">
                                                    <tr><th>Category</th><th>2025</th><th>2026 (proposed)</th><th>Top 20 DEF Range</th></tr>
                                                </thead>
                                                <tbody>
                                                    <tr><td>Sack</td><td>0.5</td><td>0.5 (unchanged)</td><td>35-50 sacks per season</td></tr>
                                                    <tr><td>3 and Out</td><td>0.5</td><td>0.5 (unchanged)</td><td>30-50 per season</td></tr>
                                                    <tr class="table-warning"><td>Interception</td><td>1</td><td><strong>2</strong></td><td>10-20 ints per season</td></tr>
                                                    <tr class="table-warning"><td>Fumble Recovery</td><td>1</td><td><strong>2</strong></td><td>7-12 per season</td></tr>
                                                    <tr class="table-warning"><td>Safety</td><td>2</td><td><strong>4</strong></td><td>0-1 per season</td></tr>
                                                    <tr class="table-warning"><td>Block Kick</td><td>1</td><td><strong>4</strong></td><td>1-4 per season</td></tr>
                                                    <tr class="table-warning"><td>4th Down Stop</td><td>1</td><td><strong>2</strong></td><td>10-20 per season</td></tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <ul>
                                            <li>We all know that DEF scoring is not ideal, based on Yahoo's ranges for points and yards allowed. But we've tweaked it into maybe the best situation it can be in a broken system. Points and yards allowed are not a perfect indication of defensive performance, but hopefully the combination of those and turnovers are.</li>
                                            <li>Pro: Would reward defenses for creating turnovers, rather than just limiting points and yards.</li>
                                            <li>Con: Good defenses would be much more valuable, potentially creating a larger gap between top and bottom teams.</li>
                                        </ul>
                                        <br />
                                        <h4>How DEF compares to other positions (2025)</h4>

                                        <div class="table-responsive mt-2 mb-2">
                                            <table class="table table-sm table-bordered text-center small" style="max-width: 460px; line-height: 1.2;">
                                                <thead class="thead-light">
                                                    <tr><th>Pos</th><th>#1</th><th>Last Starter</th><th>Last Starter Pts</th><th>Avg (top starters)</th></tr>
                                                </thead>
                                                <tbody>
                                                    <tr><td>QB</td><td>361</td><td>#25</td><td>159</td><td>257</td></tr>
                                                    <tr><td>RB</td><td>365</td><td>#30</td><td>137</td><td>220</td></tr>
                                                    <tr><td>WR</td><td>309</td><td>#40</td><td>137</td><td>180</td></tr>
                                                    <tr><td>TE</td><td>253</td><td>#15</td><td>107</td><td>149</td></tr>
                                                    <tr><td>K</td><td>211</td><td>#15</td><td>143</td><td>166</td></tr>
                                                    <tr class="table-danger"><td>DEF (current)</td><td>243</td><td>#15</td><td>137</td><td>181</td></tr>
                                                    <tr class="table-success"><td>DEF (proposed)</td><td>299</td><td>#15</td><td>176</td><td>222</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <h4>2025 DEF Weekly Stats</h4>
                                        Min: -7 pts | Max: 38 pts | Avg: 10.6 pts
                                        <div class="table-responsive mt-2 mb-2">
                                            <table class="table table-sm table-bordered text-center small" style="max-width: 320px; line-height: 1.2;">
                                                <tbody>
                                                    <tr><td>Above 20 pts</td><td>15 times</td><td></td><td></td></tr>
                                                    <tr><td>10 – 20 pts</td><td>53 times</td><td></td><td></td></tr>
                                                    <tr><td>0 – 10 pts</td><td>62 times</td><td></td><td></td></tr>
                                                    <tr><td>Negative pts</td><td>10 times</td><td></td><td></td></tr>
                                                    <tr class="font-weight-bold"><td>Total</td><td>140</td><td></td><td></td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        2025 Week 16 actual examples are shown below. Take a minute and try to rank these defensive performances to determine how they should ideally score fantasy points.
                                        <div class="table-responsive mt-2 mb-2">
                                            <table class="table table-sm table-bordered text-center small" style="line-height: 1.2;">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Team</th>
                                                        <th>Pts vs.</th>
                                                        <th>Yards Allowed</th>
                                                        <th>Sack</th>
                                                        <th>Safe</th>
                                                        <th>Int</th>
                                                        <th>Fum Rec</th>
                                                        <th>TD</th>
                                                        <th>Blk Kick</th>
                                                        <th>4 Dwn Stop</th>
                                                        <th>3 And Outs</th>
                                                        <th class="def-hidden-col" style="display:none;">Fantasy Pts</th>
                                                        <th class="def-hidden-col" style="display:none;">Fantasy Pts (proposed)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr><td>Texans</td><td>21</td><td>315</td><td>3</td><td>0</td><td>1</td><td>0</td><td>1</td><td>0</td><td>0</td><td>4</td><td class="def-hidden-col" style="display:none;">10.5</td><td class="def-hidden-col" style="display:none;">11.5</td></tr>
                                                    <tr><td>Titans</td><td>9</td><td>133</td><td>4</td><td>1</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td><td>4</td><td class="def-hidden-col" style="display:none;">20</td><td class="def-hidden-col" style="display:none;">22</td></tr>
                                                    <tr><td>Bengals</td><td>21</td><td>389</td><td>0</td><td>0</td><td>2</td><td>1</td><td>0</td><td>0</td><td>1</td><td>2</td><td class="def-hidden-col" style="display:none;">5</td><td class="def-hidden-col" style="display:none;">8</td></tr>
                                                    <tr><td>Ravens</td><td>28</td><td>453</td><td>4</td><td>0</td><td>1</td><td>1</td><td>0</td><td>0</td><td>1</td><td>1</td><td class="def-hidden-col" style="display:none;">2.5</td><td class="def-hidden-col" style="display:none;">4.5</td></tr>
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
                                <strong>Draft date: 9.5.22 | Time: 3pm | Location: <a href="#" id="reveal-def-cols" onclick="document.querySelectorAll('.def-hidden-col').forEach(el => el.style.display=''); this.style.pointerEvents='none'; return false;" style="color: inherit; text-decoration: none;">Everett's</a></strong>
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