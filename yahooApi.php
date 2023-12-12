<?php

$pageName = 'Yahoo API';
include 'header.php';
include 'sidebar.html';

// 1. Get Request Token URL
$request_token_url = get_request_token_url($consumer_key);

if( ! $request_token_url ) {
    print "Could not retrieve request token data\n";
    exit;
}

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">

            <div class="row">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header"></div>
                        <div class="card-body info-card">
                            <!-- 2. Direct user to Yahoo! for authorization (retrieve verifier) -->
                            <h2>First click Verify and then come back and enter the code</h2>
                            <a class="btn btn-secondary" href="<?php echo $request_token_url; ?>" target="_blank">Verify</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-4 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Settings</h4>
                        </div>
                        <div class="card-body info-card">
                            <h3>Code</h3>
                            <input type="text" name="code">

                            <h3>League</h3>
                            <input type="text" name="league_id" value="74490">

                            <h3>Sections to Update</h3>
                            <input type="checkbox" name="sections[]" value="yahoo_ids"> Manager Yahoo IDs<br>
                            <input type="checkbox" name="sections[]" value="team_names" checked="checked"> Team Names<br>
                            <input type="checkbox" name="sections[]" value="matchups" checked="checked"> Matchups<br>
                            <input type="checkbox" name="sections[]" value="rosters" checked="checked"> Rosters<br>
                            <input type="checkbox" name="sections[]" value="trades" checked="checked"> Trades<br>
                            <input type="checkbox" name="sections[]" value="fun_facts" checked="checked"> Fun Facts<br>

                            <h3>Weeks</h3>
                            <input type="checkbox" name="weeks[]" value="1"> 1<br>
                            <input type="checkbox" name="weeks[]" value="2"> 2<br>
                            <input type="checkbox" name="weeks[]" value="3"> 3<br>
                            <input type="checkbox" name="weeks[]" value="4"> 4<br>
                            <input type="checkbox" name="weeks[]" value="5"> 5<br>
                            <input type="checkbox" name="weeks[]" value="6"> 6<br>
                            <input type="checkbox" name="weeks[]" value="7"> 7<br>
                            <input type="checkbox" name="weeks[]" value="8"> 8<br>
                            <input type="checkbox" name="weeks[]" value="9"> 9<br>
                            <input type="checkbox" name="weeks[]" value="10"> 10<br>
                            <input type="checkbox" name="weeks[]" value="11"> 11<br>
                            <input type="checkbox" name="weeks[]" value="12"> 12<br>
                            <input type="checkbox" name="weeks[]" value="13"> 13<br>
                            <input type="checkbox" name="weeks[]" value="14"> 14<br>

                            <button class="btn btn-secondary" id="make_request">Submit</button>
                            <button class="btn btn-secondary" id="show_leagues">Show Leagues</button>
                        </div>
                    </div>
                </div>
                <div class="col-sm-8 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Output</h4>
                        </div>
                        <div class="card-body info-card" style="overflow: scroll;">
                            <div id="output">Output will show here...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script type="text/javascript">

    var access_token = null;

    $('#make_request').click(function () {
        var league_id = $('input[name="league_id"]').val();
        var weeks = [];
        $('input[name="weeks[]"]:checked').each(function () {
            weeks.push($(this).val());
        });

        if (!access_token) {
            $.ajax({
                url: 'yahooApiToken.php',
                type: 'POST',
                data: {
                    code: $('input[name="code"]').val()
                },
                success: function(response) {
                    access_token = response;
    
                    makeRequest(league_id, weeks);
                }
            });
        } else {
            makeRequest(league_id, weeks);
        }
    });

    $('#show_leagues').click(function () {
        let info = 'Year: 2023 | League ID: 74490 <br />';
        info += 'Year: 2022 | League ID: 84027 <br />';
        info += 'Year: 2021 | League ID: 16064 <br />';
        info += 'Year: 2020 | League ID: 43673 <br />';
        info += 'Year: 2019 | League ID: 201651 <br />';
        info += 'Year: 2018 | League ID: 224863 <br />';
        info += 'Year: 2017 | League ID: 262191 <br />';
        info += 'Year: 2016 | League ID: 477642 <br />';
        info += 'Year: 2015 | League ID: 217861 <br />';
        info += 'Year: 2014 | League ID: 53077 <br />';
        info += 'Year: 2013 | League ID: 27577 <br />';
        info += 'Year: 2012 | League ID: 26725 <br />';
        info += 'Year: 2011 | League ID: 163601 <br />';
        info += 'Year: 2010 | League ID: 35443 <br />';
        info += 'Year: 2009 | League ID: 42150 <br />';
        info += 'Year: 2008 | League ID: 8224 <br />';
        info += 'Year: 2007 | League ID: 73988 <br />';
        info += 'Year: 2006 | League ID: 48909 <br />';

        $('#output').html(info);
    });

    function makeRequest(league_id, weeks) {
        $('#output').html('');

        // For each selected section, make request
        $('input[name="sections[]"]:checked').each(function () {

            let section = $(this).val();
            if (section == 'rosters') {
                makeRosterRequest(league_id, weeks, 1);
            } else {
                $.ajax({
                    url: 'yahooApiRequest.php',
                    type: 'POST',
                    data: {
                        token: access_token,
                        league_id: league_id,
                        section: section,
                        weeks: weeks
                    },
                    success: function(response) {
                        $('#output').append(response);
                    }
                });
            }
        });
    }

    function makeRosterRequest(league_id, weeks, manager) 
    {
        if (manager == 11) {
            return;
        }
        $.ajax({
            url: 'yahooApiRequest.php',
            type: 'POST',
            data: {
                token: access_token,
                league_id: league_id,
                section: 'rosters',
                weeks: weeks,
                manager: manager
            },
            success: function(response) {
                $('#output').append(response);
                manager++;
                setTimeout(function () {
                    makeRosterRequest(league_id, weeks, manager);
                }, 5000);
            }
        });   
    }

</script>

<?php
  
///////////////////////////////////////////////////////////////////////////////
//  FUNCTION get_request_token
/// @brief Get a request token for a given application.
///////////////////////////////////////////////////////////////////////////////
function get_request_token_url(string $consumer_key) 
{
    $url = 'https://api.login.yahoo.com/oauth2/request_auth';
    $params = [
        'client_id' => $consumer_key,
        'redirect_uri' => 'oob',  // Set OOB for ease of use -- could be a URL
        'response_type' => 'code',
        'language' => 'en-us'
    ];

    // Urlencode params and generate param string
    $param_list = [];
    foreach( $params as $key => $value ) {
      $param_list[] = urlencode( $key ) . '=' . urlencode( $value );
    }
    $param_string = join('&', $param_list);

    // Return url like this
    // https://api.login.yahoo.com/oauth2/request_auth?client_id=dj0yJmk9Mjc0ZUJKQmk3NHVaJmQ9WVdrOVlrSkVNRkJ6Y1ZvbWNHbzlNQT09JnM9Y29uc3VtZXJzZWNyZXQmc3Y9MCZ4PWRl&redirect_uri=oob&response_type=code&language=en-us
    
    return $url.'?'.$param_string;
}

?>