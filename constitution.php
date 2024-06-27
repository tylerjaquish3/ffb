<?php

$pageName = "Constitution";
include 'header.php';
include 'sidebar.html';

$allYears = [];
$result = query("SELECT distinct year FROM regular_season_matchups ORDER BY YEAR ASC");
while ($row = fetch_array($result)) {
    $allYears[] = $row['year'];
}

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>League Info</h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block" style="direction: ltr">
                                <p>We, the members of Suntown Fantasy Football League, in order to promote and encourage interest in Fantasy Football,
                                    do ordain and establish this Constitution for the government of our association, including the heathens that do not hail from Sunnyside, WA.
                                </p>

                                <p>The Suntown Fantasy Football league began many moons ago, as a group of college students that enjoyed watching football together.
                                    The core of the league began in 2004 with just six members: Gavin, Tyler, AJ, Andy, Everett, and Ben. In 2005, the league grew to 
                                    eight members, as Cole and Matt joined the league. In 2006, the league remained at eight members, however, Justin replaced Andy
                                    in the league. And finally in 2008, the league grew to ten members, as Cameron and Andy (re)joined the league.
                                    The coveted league trophy was introduced in 2006, where the league only contained eight members. 
                                    Henceforth, the data on this website dates back to 2006 to correlate with the league trophy.</p>

                                <p>This league will always be a free, head-to-head league hosted by Yahoo. The only prize is a plaque with the champion's name inscribed that gets added to the league trophy,
                                    as well as the trophy living with the champion until a new champion is crowned. The league has also voted that the 10th place finisher
                                    must pay for the new plaque at the end of each season, sit on a toilet during the next draft, and their team name (for the following season) shall be set by the champion.</p>

                                <p>The league matchups schedule shall use data from prior seasons to determine the optimal weekly schedules for each manager to face all opponents as equally as possible.
                                </p>

                                <p>All parts of this constitution, including rules and practices, may be modified or amended with a majority vote. However, if deemed minor, changes may be made by the commissioner without
                                    whining or complaining by the members. Questions concerning the interpretation of this document shall be reviewed and <u>swiftly</u> deleted by the commissioner.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Draft</h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block" style="direction: ltr">
                                <p>The fantasy football draft is the single most important day of the fantasy football season, but the league is not won at the draft.
                                Our draft is held on or around the end of August, generally one week before the kickoff of the NFL season.</p>

                                <p>Approximately 6 weeks before the draft, the draft order is determined in a unique but random fashion. This is created by the commissioner
                                and recorded for video evidence, with no retakes or edits in order to preserve authenticity and integrity. For instance, when the dog ran
                                away with the tennis ball with Gavin's name on it, the video continued uncut. The draft order will always be determined with complete randomness,
                                giving every manager an equal chance at getting the #1 pick.</p>

                                <p>The draft shall always be of "snake" orientation and draft grades given by Yahoo shall always be considered 100% accurate and reliable.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
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
                                    <li>Trades have a 2 day period where they can be reviewed/vetoed by the league. Therefore, the deadline for a trade to take effect
                                        for the current week is to be accepted by Thursday. If any trade elements play on Thursday, the deadline for the trade to be accepted is Monday.
                                    </li>
                                    <li>Smack talk is encouraged, especially if it hurts feelings.</li>
                                    <li>Players added and dropped in the same day shall return to Free Agent status (not Waivers).</li>
                                    <li>Rules/settings can be changed with a majority vote. However, in-season changes require a super-majority (80% yea).</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
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

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Roster Position History</h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block" style="direction: ltr">
                                <table id="datatable-settings" class="table table-responsive table-striped nowrap">
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
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Meeting Agenda | TBD 2024</h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block" style="direction: ltr">
                                <br />
                                <strong>Vote on the following: </strong>
                                <ol>
                                    <li>Add an 18th roster position (BN or W/R/T) | <a href="voteInfo.php#new_pos">More Info</a><strong></strong></li>
                                    <li>Push trades through right away | <a href="voteInfo.php#trade_voting">More Info</a><strong></strong></li>
                                    <li>Able to trade FAB dollars | <a href="voteInfo.php#trade_fab">More Info</a><strong></strong></li>
                                    <li>Add Punt/Kick Return TDs to DEF | <a href="voteInfo.php#return_td">More Info</a><strong></strong></li>
                                    <li>Replacing toilet seat as punishment | <a href="voteInfo.php#punishment">More Info</a><strong></strong></li>
                                </ol>
                                <strong>Next year's draft location</strong><br />
                                <strong>Determine 2024 draft order</strong>
                                <br /><br />
                                <strong>Draft date: 8.25.24 | Time: 3:30pm | Location: Sartin's (Spokane, WA)</strong>
                                <br /><br />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
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
                            
                                <strong>Draft date: 8.27.23 | Time: 2pm | Location: Didier's (Pasco, WA)</strong>
                                <br /><br />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
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
                                <strong>Draft date: 9.5.22 | Time: 3pm | Location: E. Boboth's (Sunnyside, WA)</strong>
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
                </div>
            </div>
            
            <div class="row">
                <div class="col-sm-12">
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

                                <strong>Draft date: 9.6.21 | Time: 4pm | Location: Stamschror's (Spokane Valley, WA)</strong>
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
    });
</script>