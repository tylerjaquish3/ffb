<?php
$pageName = 'Vote Info';
include 'header.php';
include 'sidebar.html';

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">

            <div class="row" id="waivers">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Continual Rolling List Waivers vs FAB Info</h4>
                        </div>
                        <div class="card-body info-card">
                            In a Free Agents Budgets (FAB) waivers system, each manager receives a dollar amount to place blind offers on waived players throughout the season. 
                            The manager with the highest offer at the end of the waiver period claims that player, and that offer amount is deducted from the team's free agent budget for the season. If multiple managers place an equal offer on a player, tiebreak options are available.
                            <br />
                            <br />
                            We could start with any amount of FAB budget (standard is $100 or $200) with the option to also place $0 bids.
                            <br />
                            <br />
                            The benefit to using the FAB system is that it gives everyone a chance to acquire a waiver player, and will be determined by who
                            is willing to be the most aggressive with their budget. The downside is that there may be a learning curve to understanding how much players are worth.
                            It also makes it harder to predict which managers will land a player versus the system currently in place.
                            <br />
                            <br />
                            Note: waiver periods would not change.
                            <br />
                            <br />
                            <b>Proposal:</b> Change the Waiver Priority setting to FAB with reverse order of standings as tiebreak, budget set to $200.
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
                            
                            2020: 4 IR spots<br />
                            2021: 2 IR spots<br />
                            The NFL's IR designation changed in 2020 to not be a season-ending or 8-week designation, but can now be as few as 3 weeks.
                            IR spots were added in our fantasy league to provide more flexibility for a season that had many unknowns.<br />
                            The IR spot is useful because more players are put on real life IR and return in three weeks.
                            It helps managers not have to use bench spots for injured players and therefore have better rosters. <br />
                            The argument against the IR spot is to make managers decide if the injured player is worth stashing (taking up a bench spot) while on IR.
                            This could create more activity for waivers.
                            <br />
                            <br />
                            <b>Proposal:</b> Change the number of IR spots to 1 or 0.
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php include 'footer.html'; ?>