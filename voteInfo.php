<?php
$pageName = 'Vote Info';
include 'header.php';
include 'sidebar.html';

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">

            <div class="row" id="fab">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Keep FAB System for Waivers</h4>
                        </div>
                        <div class="card-body info-card">
                            Last year we voted and approved a Free Agents Budgets (FAB) waivers system, each manager receives a dollar amount to place blind offers on waived players throughout the season. 
                            More information (refresher) of the FAB system can be found on the Constitution page, 2022 Meeting Agenda section.
                            <br />
                            <br />
                            The benefit to using the FAB system is that it gives everyone a chance to acquire a waiver player, and will be determined by who
                            is willing to be the most aggressive with their budget. The downside is that there may be a learning curve to understanding how much players are worth.
                            It also makes it harder to predict which managers will land a player versus the system currently in place.
                            <br />
                            <br />
                            <b>Proposal:</b> Keep the Waiver Priority setting to FAB with reverse order of standings as tiebreak, budget set to $200.
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" id="kicker-points">
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
            </div>
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

<?php include 'footer.html'; ?>