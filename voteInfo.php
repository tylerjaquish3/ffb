<?php
$pageName = 'Vote Info';
include 'header.php';
include 'sidebar.html';

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">

            <div class="row" id="new_pos">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Add an 18th Roster Position</h4>
                        </div>
                        <div class="card-body info-card">
                            In order to make the draft more even, it is proposed to add an 18th roster position so there are 18 rounds in the draft. This would
                            prevent the first pick in the first round from also having the first pick in the last round.
                            <br />
                            <br />
                            The other question would be what position to add. The two options are an additional Bench position or an additional W/R/T position.
                            Both proposals would make the free agent pool smaller. Adding a bench spot would allow managers to hold more players, so navigating
                            bye weeks would be easier. Adding a W/R/T position would make the flex position more valuable and would allow managers to start more
                            RBs or WRs (most likely), but it would make it harder than it is now to fill your roster with only 6 bench spots.
                            <br />
                            <br />
                            <b>Proposal A:</b> Add an additional Bench position.<br />
                            <b>Proposal B:</b> Add an additional W/R/T position.
                        </div>
                    </div>
                </div>
            </div>
            <!-- <div class="row" id="kicker-points">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Remove Negative Kicker Points</h4>
                        </div>
                        <div class="card-body info-card">
                            Current Settings: <br />
                            <ul>
                                <li>Missed FG 0-19 yds   :   -5 pts</li>
                                <li>Missed FG 20-29 yds  :   -4 pts</li>
                                <li>Missed FG 30-39 yds  :   -2 pts</li>
                            </ul>
                            Currently, the negative points for missed field goals are too drastic for what they are worth if made. For instance, a 19 yard field goal is worth 1.9 points, but costs -5 if missed.
                            That equates to a 6.9 point swing for a short field goal missed, which is one of the largest point changes in our league. 
                            <br />
                            <br />
                            The reason we set it this way was to make kickers more impactful and base the negative points on the likelihood of making the field goal and how often it occurs.
                            <br />
                            <br />
                            <b>Proposal:</b>
                            <ul>
                                <li>0-19: -2 pts</li>
                                <li>20-29: -1 pts</li>
                                <li>30-39: -1 pts</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div> -->
            <!-- <div class="row" id="waivers">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Waiver Info</h4>
                        </div>
                        <div class="card-body info-card">
                            
                            Current Settings: <br />
                            <ul>
                                <li>Waiver Time: 3 days</li>
                            </ul>
                            Currently, players stay on waivers for 3 days once being dropped and that doesn't allow players to be added for the following week in the typical add/drop cycle. 
                            For instance, on Waiver day (Tuesday) people put in waivers and overnight those waivers are processed. So in this typical cycle, if a player is dropped technically on Wednesday, 
                            they are on waivers until Sunday morning. In some cases, that makes this player ineligible to be added for the following week (if they play Thursday).
                            <br />
                            <br />
                            Sometimes a player is held and dropped at a certain time in order to block other managers from getting them, which is fine and valid. This rule would just extend the time
                            that a manager would have to keep the player. For instance, if you dropped a player on Thursday, they would be on waivers until Monday morning. If this new rule was in place,
                            the player would have to be kept until Friday in order to block other managers from adding them for Sunday.
                            <br />
                            <br />
                            <b>Proposal:</b>
                            <ul>
                                <li>Waiver Time: 2 days</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div> -->
            
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>