<?php
$pageName = 'Vote Info';
include 'header.php';
include 'sidebar.html';

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">

            <div class="row" id="idp">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Roster IDP Info</h4>
                        </div>
                        <div class="card-body info-card">
                            2019: 3 D spots (for any defensive position) and 2 DB spots (CB and S)<br />
                            Highest points (reg. season): Tyler - 513 pts (39 pts/wk)<br />
                            Lowest points: Ben - 374 pts (29 pts/wk)<br />

                            2020: 0 IDP
                            <hr />
                            <b>Proposal:</b> Compromise at 3 D spots (for any defensive position). Any less than 3 makes the position worth too little.
                            <br />
                            <b>Arguments for:</b>
                            <ul>
                                <li>It's fun to root for your linebacker to get tackles or IDPs to get turnovers, etc. </li>
                                <li>It sets our league apart (something different than most)</li>
                                <li>More decisions for filling rosters on bye weeks, which induces different strategies</li>
                                <li>Can be added to make trades more even if slightly unbalanced</li>
                            </ul>
                            <b>Arguments against:</b>
                            <ul>
                                <li>The draft duration is extended</li>
                                <li>It's the only position where a manager can "double up" on points if they own both the IDP and DEF from the same team</li>
                                <li>Can create a league imbalance for managers that overlook the strategy</li>
                                <li>Some IDP players may not be valuable enough to keep during a bye (however this could also be said about other positions, and with only the top 30 being owned, most should have value)</li>
                            </ul>

                        </div>
                    </div>
                </div>
            </div>
            <div class="row" id="superflex">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Roster Superflex Info</h4>
                        </div>
                        <div class="card-body info-card">
                            2019: 2 QB spots, no superflex<br />
                            2020: 1 QB spot, 1 Q/W/R/T spot<br />
                            Superflex was added to provide more flexibility for a season that had many unknowns (covid).<br />
                            Throughout the season, the flexibility was utilized a total of 4 times:
                            <ul>
                                <li>Tyler wk 4 - wasn't forced to, and didn't work out beneficially</li>
                                <li>Ben wk 6 - forced to based on roster make up and byes</li>
                                <li>Tyler wk 6 - forced to based on roster make up and injury</li>
                                <li>Everett wk 13 - wasn't forced to, and didn't work out beneficially</li>
                            </ul>
                            It seemed that if QBs were effected by covid, the manager was able to pivot to another QB in all cases.
                            Also, it appears that the 2021 season has fewer unknowns than 2020.<br />
                            <b>Proposal:</b> Change the Q/W/R/T position back to a QB position.
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" id="ir">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Roster IR Info</h4>
                        </div>
                        <div class="card-body info-card">
                            2019: 0 IR spots<br />
                            2020: 4 IR spots<br />
                            The NFL's IR designation changed in 2020 to not be a season-ending or 8-week designation, but can now be as few as 3 weeks.
                            IR spots were added in our fantasy league to provide more flexibility for a season that had many unknowns (covid).<br />
                            2020 numbers (reg. season):<br />
                            <ul>
                                <li>4 IR spots used three times in a week (Justin wk 3, Cole wk 12, Cole wk 13)</li>
                                <li>3 IR spots used twelve times</li>
                                <li>Total weeks = 130 (10 managers for 13 reg. season weeks)</li>
                                <li>Therefore, the extra spots were used 15 times in 130 weeks</li>
                            </ul>
                            We don't have exact numbers on how many IR uses were related to covid, but best guess is less than 10%.
                            Regardless, the IR spot is useful because more players are put on real life IR and return in three weeks.
                            It helps managers not have to use bench spots for injured players. <br />

                            <b>Proposal:</b> Change the number of IR spots from 4 to 2.
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" id="teamdef">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Team Defense Points Info</h4>
                        </div>
                        <div class="card-body info-card">
                            It's been requested to address the points awarded for team defenses, most notably the Points Allowed and Yards Allowed, but now would be a good time to address all of the values.
                            Having removed IDPs from last season, it might make sense to increase points for turnovers. They had been set intentionally low so that IDPs points could be more valuable than team DEF.

                            This proposal has two parts (see below) that may be voted on separately or jointly.
                            <br />Background info:
                            <ul>
                                <li>2019 Avg NFL points scored: 21.8 (this doesn't include defensive TDs not counted against opposing team DEF)<br /></li>
                                <li>2019 Avg NFL yards allowed: 348</li>
                                <li>2020 Avg NFL points scored: 23.9 (this doesn't include defensive TDs not counted against opposing team DEF)<br /></li>
                                <li>2020 Avg NFL yards allowed: 359</li>
                                <li>2020 DEF points highest in lineup: Everett 157.5 (12 pts/wk)</li>
                                <li>2020 DEF points lowest in lineup: Cole 47 (3.6 pts/wk)</li>
                                <li>Top 10 DEFs scored 1521 pts total. 14% from sacks, 10% from Ints, 6% from Fum, 10% from TDs, 8% from 4D Stops, 13% from 3outs, 2% other. So 37% of DEF scoring is from Points and Yards Allowed.</li>
                                <li>2020 Avg for top 10 DEFs: 9.5 pts/wk</li>
                                <li>For reference, QBs in lineups averaged 21 pts/wk, RBs averaged 13.5 pts/wk, WRs averaged 12.2 pts/wk, W/R/T averaged 10.7 pts/wk</li>
                                <li>Top DEF (LAR) would have ranked #23 among all W/R/T, and the average of top 10 DEFs ranks #62 among W/R/T</li>
                            </ul>

                            Current league settings:
                            <table class="settings-table">
                                <tr><th>Category</th><th>League Value</th><th>Yahoo Default Value</th></tr>
                                <tr><td>Sack</td><td>.5</td><td>1</td></tr>
                                <tr><td>Interception</td><td>1</td><td>2</td></tr>
                                <tr><td>Fumble Recovery</td><td>1</td><td>2</td></tr>
                                <tr><td>Touchdown</td><td>6</td><td>0</td></tr>
                                <tr><td>Safety</td><td>2</td><td>0</td></tr>
                                <tr><td>Block Kick</td><td>1</td><td>2</td></tr>
                                <tr><td>4th Down Stops</td><td>	1</td><td>0</td></tr>
                                <tr><td>Three and Outs Forced</td><td>.5</td><td>0</td></tr>
                                <tr><td>Extra Point Returned</td><td>2</td><td>0</td></tr>

                                <tr><td>Points Allowed 0 points</td><td>16</td><td>10</td></tr>
                                <tr><td>Points Allowed 1-6 points</td><td>12</td><td>7</td></tr>
                                <tr><td>Points Allowed 7-13 points</td><td>8</td><td>4</td></tr>
                                <tr><td>Points Allowed 14-20 points</td><td>4</td><td>1</td></tr>
                                <tr><td>Points Allowed 21-27 points</td><td>0</td><td>0</td></tr>
                                <tr><td>Points Allowed 28-34 points	</td><td>-3</td><td>-1</td></tr>
                                <tr><td>Points Allowed 35+ points</td><td>-6</td><td>-4</td></tr>
                                <tr><td>Defensive Yards Allowed - Negative</td><td>	12</td><td>0</td></tr>
                                <tr><td>Defensive Yards Allowed 0-99</td><td>9</td><td>0</td></tr>
                                <tr><td>Defensive Yards Allowed 100-199</td><td>6</td><td>0</td></tr>
                                <tr><td>Defensive Yards Allowed 200-299</td><td>3</td><td>0</td></tr>
                                <tr><td>Defensive Yards Allowed 300-399</td><td>0</td><td>0</td></tr>
                                <tr><td>Defensive Yards Allowed 400-499</td><td>-2</td><td>	0</td></tr>
                                <tr><td>Defensive Yards Allowed 500+</td><td>-4</td><td>0</td></tr>
                            </table>
                            <hr />
                            <b>Proposal A:</b> Remove the categories for Yards Allowed.
                            <br />

                            <b>Arguments for:</b>
                            <ul>
                                <li>This was originally set up to be a bonus, but the first three ranges (up to 199 yards allowed) were basically unattainable.
                                    So the bonus was limited to just 3 pts for giving up less than 299 yds, and would more often that not result in zero or negative outcomes.</li>
                                <li>This will make more defenses less risky because their max negative would be -6 from giving up points</li>
                                <li>This wouldn't have a big effect on the good DEFs, but would benefit more of the mediocre or bad DEFs that consistently give up 400+ yds.</li>
                                <li>Not having the flexibility to change the ranges makes it hard to have perfect values</li>
                            </ul>
                            <b>Arguments against:</b>
                            <ul>
                                <li>Scenario where the offense constantly turns the ball over with a short field, the DEF gives up points but limits the yards, and they don't get credited for that (tough luck).</li>
                            </ul>
                            <hr />
                            <b>Proposal B:</b> Change only the following Turnover Points:
                            <br />
                            <table class="settings-table">
                                <tr><th>Category</th><th>New Value</th></tr>

                                <tr><td>Fumble Recovery</td><td>2</td></tr>
                                <tr><td>Safety</td><td>4</td></tr>
                                <tr><td>Block Kick</td><td>2</td></tr>
                                <tr><td>4th Down Stops</td><td>2</td></tr>
                                <tr><td>Extra Point Returned</td><td>4</td></tr>
                            </table>
                            <b>Arguments for:</b>
                            <ul>
                                <li>Will make defenses more valuable, which increases draft stock and also keepability during a bye week</li>
                                <li>With just these changes and not passing Proposal A, the top DEF from 2020 would look like this:</li>
                                <li><ol>
                                    <li>LAR 208 pts, rank #23 among WRT, with Prop B: 231 pts, rank #12</li>
                                    <li>Pit 176 pts, rank #41 among WRT, with Prop B: 202 pts, rank #24</li>
                                    <li>Bal 169 pts, rank #46 among WRT, with Prop B: 204 pts, rank #23</li>
                                    <li>Was 158 pts, rank #57 among WRT, with Prop B: 182 pts, rank #35</li>
                                    <li>Ind 156 pts, rank #60 among WRT, with Prop B: 180 pts, rank #38</li>
                                </ol></li>
                            </ul>
                            <b>Arguments against:</b>
                            <ul>
                                <li>We may not want defenses worth too much in comparison to other positions</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" id="fgyards">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Field Goal YPP Info</h4>
                        </div>
                        <div class="card-body info-card">
                            Yahoo added a new stat category for kickers: Field Goal Yards Per Point <br />
                            Using this would alleviate the problem with the designated yard ranges that Yahoo has set, where a 39 yd FG is worth 3 but a 40 yd FG is worth 4, etc.
                            With this new system, a 39 yd FG would be worth 3.9 pts and a 40 yd FG would be 4.0 pts.
                            <br />
                            <b>Proposal:</b> Use Field Goal Yards Per Point set to 10 yards = 1pt and remove the yard ranges for kickers. Note: we would keep the points for missed FG as-is.
                            <ul>
                                <li>FG Missed 0-19 yds: -5 pts</li>
                                <li>FG Missed 20-29 yds: -4 pts</li>
                                <li>FG Missed 30-39 yds: -2 pts</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" id="license">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">License Plate Info</h4>
                        </div>
                        <div class="card-body info-card">
                            <b>Proposal:</b> The last place finisher (postseason) to install the following license plate frame on their primary vehicle for a full year.
                            Commissioner would cover the cost. <br />
                            <img src="https://images-na.ssl-images-amazon.com/images/I/519BlDG-5iL._AC_SX679_.jpg" width="320">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.html'; ?>

<script type="text/javascript">
    $(document).ready(function () {


        $('#datatable-wins').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [3, "desc"]
            ]
        });

        // Misc tables
        $('#datatable-misc1').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [1, "desc"]
            ]
        });
        $('#datatable-misc2').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [1, "desc"]
            ]
        });

    });


</script>