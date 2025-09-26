<?php
// Increase maximum execution time to 300 seconds (5 minutes)
set_time_limit(300);

$pageName = 'Yahoo API';
include 'header.php';

// Custom styles for loader
echo '<style>
.spinner {
    display: inline-block;
    width: 40px;
    height: 40px;
    border: 4px solid rgba(0, 0, 0, 0.1);
    border-radius: 50%;
    border-top-color: #0275d8;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>';

// Check if environment is set to production
if (isset($APP_ENV) && $APP_ENV === 'production') {
    header("Location: 404.php");
    exit;
}

include 'sidebar.php';

if (isset($_GET['archive'])) {
    $consumer_key = $archive_key;
}

// 1. Get Request Token URL
$request_token_url = get_request_token_url($consumer_key);

if( ! $request_token_url ) {
    print "Could not retrieve request token data\n";
    exit;
}

?>

<div class="app-content content">
    <div class="content-wrapper">

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

                            <h3>Season</h3>
                            <input type="text" name="year" value="<?php echo date('Y'); ?>">

                            <h3>Sections to Update</h3>
                            <input type="checkbox" name="sections[]" value="yahoo_ids"> Manager Yahoo IDs<br>
                            <input type="checkbox" name="sections[]" value="team_names"> Team Names<br>
                            <input type="checkbox" name="sections[]" value="matchups"> Matchups<br>
                            <input type="checkbox" name="sections[]" value="rosters"> Rosters<br>
                            <input type="checkbox" name="sections[]" value="trades"> Trades<br>
                            <!-- <input type="checkbox" name="sections[]" value="fun_facts"> Fun Facts<br> -->

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
                        </div>
                    </div>
                </div>
                <div class="col-sm-8 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Output</h4>
                        </div>
                        <div class="card-body info-card" style="overflow: scroll;">
                            <div id="loading" style="display: none; text-align: center; margin: 20px 0;">
                                <div class="spinner"></div>
                                <p style="margin-top: 10px;">Processing request, please wait...</p>
                            </div>
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
        var year = $('input[name="year"]').val();
        var weeks = [];
        $('input[name="weeks[]"]:checked').each(function () {
            weeks.push($(this).val());
        });

        // Show the loading spinner
        $('#loading').show();
        $('#output').html('');
        
        if (!access_token) {
            $.ajax({
                url: 'yahooApiToken.php',
                type: 'POST',
                data: {
                    code: $('input[name="code"]').val(),
                    year: year
                },
                success: function(response) {
                    access_token = response;
    
                    makeRequest(year, weeks);
                },
                error: function() {
                    $('#loading').hide();
                    $('#output').html('<div class="alert alert-danger">Error fetching access token. Please try again.</div>');
                }
            });
        } else {
            makeRequest(year, weeks);
        }
    });

    function makeRequest(year, weeks) {
        // Count selected sections for tracking completion
        var pendingRequests = $('input[name="sections[]"]:checked').length;
        var hasRosters = $('input[name="sections[]"][value="rosters"]:checked').length > 0;
        
        // If no sections are selected, hide the spinner
        if (pendingRequests === 0) {
            $('#loading').hide();
            $('#output').html('<div class="alert alert-warning">Please select at least one section to update.</div>');
            return;
        }
        
        // If rosters is selected, we handle it differently because of its recursive nature
        if (hasRosters) {
            pendingRequests--;
        }

        // For each selected section, make request
        $('input[name="sections[]"]:checked').each(function () {
            let section = $(this).val();
            if (section == 'rosters') {
                makeRosterRequest(year, weeks, 1, function() {
                    // Hide spinner when rosters are complete
                    if (pendingRequests === 0) {
                        $('#loading').hide();
                    }
                });
            } else {
                $.ajax({
                    url: 'yahooApiRequest.php',
                    type: 'POST',
                    data: {
                        token: access_token,
                        year: year,
                        section: section,
                        weeks: weeks
                    },
                    success: function(response) {
                        $('#output').append(response);
                        pendingRequests--;
                        
                        // Hide spinner when all requests are complete
                        if (pendingRequests === 0 && !hasRosters) {
                            $('#loading').hide();
                        }
                    },
                    error: function() {
                        $('#output').append('<div class="alert alert-danger">Error processing ' + section + '. Please try again.</div>');
                        pendingRequests--;
                        
                        // Hide spinner when all requests are complete
                        if (pendingRequests === 0 && !hasRosters) {
                            $('#loading').hide();
                        }
                    }
                });
            }
        });
    }

    function makeRosterRequest(year, weeks, manager, callback) 
    {
        if (manager == 11) {
            // All managers processed, call the callback to signal completion
            if (callback) callback();
            return;
        }
        $.ajax({
            url: 'yahooApiRequest.php',
            type: 'POST',
            data: {
                token: access_token,
                year: year,
                section: 'rosters',
                weeks: weeks,
                manager: manager
            },
            success: function(response) {
                $('#output').append(response);
                manager++;
                setTimeout(function () {
                    makeRosterRequest(year, weeks, manager, callback);
                }, 2000);
            },
            error: function() {
                $('#output').append('<div class="alert alert-danger">Error processing rosters for manager ' + manager + '. Continuing with next manager.</div>');
                manager++;
                setTimeout(function () {
                    makeRosterRequest(year, weeks, manager, callback);
                }, 2000);
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