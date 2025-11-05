<?php
// Increase maximum execution time to 300 seconds (5 minutes)
set_time_limit(300);

$pageName = 'Yahoo API';
include 'header.php';

// Get managers for the current year
$currentYear = date('Y');
$managersResult = query("SELECT sm.yahoo_id, m.name FROM season_managers sm 
    JOIN managers m ON sm.manager_id = m.id 
    WHERE sm.year = $currentYear 
    ORDER BY m.name");
$managers = [];
while ($row = fetch_array($managersResult)) {
    $managers[] = [
        'yahoo_id' => $row['yahoo_id'],
        'name' => $row['name']
    ];
}

// Get the current week to select by default (latest week + 1)
$weekResult = query("SELECT MAX(week_number) as latest_week FROM regular_season_matchups WHERE year = $currentYear");
$weekRow = fetch_array($weekResult);
$defaultWeek = $weekRow ? $weekRow['latest_week'] + 1 : 1;

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

<style>
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

</style>

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
                            <div class="row">
                                <div class="col-md-6">
                                    <?php
                                    // Display weeks 8-14 in second column
                                    for ($week = 8; $week <= 14; $week++) {
                                        $checked = ($week == $defaultWeek) ? 'checked' : '';
                                        echo '<input type="checkbox" name="weeks[]" value="' . $week . '" ' . $checked . '> ' . $week . '<br>';
                                    }
                                    ?>
                                </div>
                                <div class="col-md-6">
                                    <?php
                                    // Display weeks 1-7 in first column
                                    for ($week = 1; $week <= 7; $week++) {
                                        $checked = ($week == $defaultWeek) ? 'checked' : '';
                                        echo '<input type="checkbox" name="weeks[]" value="' . $week . '" ' . $checked . '> ' . $week . '<br>';
                                    }
                                    ?>
                                </div>
                            </div>

                            <h3>Managers</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <?php
                                    // Display first 5 managers
                                    for ($i = 0; $i < 5 && $i < count($managers); $i++) {
                                        echo '<input type="checkbox" name="managers[]" value="' . $managers[$i]['yahoo_id'] . '"> ' . $managers[$i]['name'] . '<br>';
                                    }
                                    ?>
                                </div>
                                <div class="col-md-6">
                                    <input type="checkbox" name="managers[]" value="all" checked> All<br>
                                    <?php
                                    // Display remaining managers (6-10)
                                    for ($i = 5; $i < count($managers); $i++) {
                                        echo '<input type="checkbox" name="managers[]" value="' . $managers[$i]['yahoo_id'] . '"> ' . $managers[$i]['name'] . '<br>';
                                    }
                                    ?>
                                </div>
                            </div>

                            <br /><br />
                            <button class="btn btn-secondary" id="make_request">Submit</button>
                            <hr />
                            <p style="margin-top: 10px;">
                                Note: after running these updates, update the awards by running:
                                <br />
                                <button class="btn btn-sm btn-outline-primary copy-btn" data-clipboard-text="cd fun-facts && php artisan funFacts" style="margin: 5px 0;">
                                    📋 Copy: php artisan funFacts
                                </button>
                                <br />Then update the record log with:
                                <br />
                                <button class="btn btn-sm btn-outline-primary copy-btn" data-clipboard-text="php artisan weekly:records <?php echo $currentYear; ?> <?php echo $defaultWeek; ?>" style="margin: 5px 0;">
                                    📋 Copy: php artisan weekly:records <?php echo $currentYear; ?> <?php echo $defaultWeek; ?>
                                </button>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-8 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Output</h4>
                        </div>
                        <div class="card-body info-card" style="height: 800px; overflow: scroll;">
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

    // Handle manager selection logic
    $(document).ready(function() {
        // When "All" is clicked
        $('input[name="managers[]"][value="all"]').change(function() {
            if ($(this).is(':checked')) {
                // Deselect all individual managers
                $('input[name="managers[]"]:not([value="all"])').prop('checked', false);
            }
        });

        // When any individual manager is clicked
        $('input[name="managers[]"]:not([value="all"])').change(function() {
            if ($(this).is(':checked')) {
                // Deselect "All"
                $('input[name="managers[]"][value="all"]').prop('checked', false);
            }
        });
    });

    $('#make_request').click(function () {
        var year = $('input[name="year"]').val();
        var weeks = [];
        $('input[name="weeks[]"]:checked').each(function () {
            weeks.push($(this).val());
        });

        var managers = [];
        $('input[name="managers[]"]:checked').each(function () {
            managers.push($(this).val());
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
    
                    makeRequest(year, weeks, managers);
                },
                error: function() {
                    $('#loading').hide();
                    $('#output').html('<div class="alert alert-danger">Error fetching access token. Please try again.</div>');
                }
            });
        } else {
            makeRequest(year, weeks, managers);
        }
    });

    function makeRequest(year, weeks, managers) {
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
                makeRosterRequest(year, weeks, managers, 0, function() {
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

    function makeRosterRequest(year, weeks, managers, managerIndex, callback) 
    {
        // Determine which managers to process
        var managersToProcess = [];
        
        if (managers.includes('all')) {
            // If "all" is selected, process managers 1-10
            for (var i = 1; i <= 10; i++) {
                managersToProcess.push(i);
            }
        } else {
            // Only process selected managers (convert yahoo_ids to manager numbers)
            managersToProcess = managers.filter(function(manager) {
                return manager !== 'all' && !isNaN(manager);
            });
        }
        
        // Check if we've processed all managers
        if (managerIndex >= managersToProcess.length) {
            // All managers processed, call the callback to signal completion
            if (callback) callback();
            return;
        }
        
        var currentManager = managersToProcess[managerIndex];
        
        $.ajax({
            url: 'yahooApiRequest.php',
            type: 'POST',
            data: {
                token: access_token,
                year: year,
                section: 'rosters',
                weeks: weeks,
                manager: currentManager
            },
            success: function(response) {
                $('#output').append(response);
                setTimeout(function () {
                    makeRosterRequest(year, weeks, managers, managerIndex + 1, callback);
                }, 2000);
            },
            error: function() {
                $('#output').append('<div class="alert alert-danger">Error processing rosters for manager ' + currentManager + '. Continuing with next manager.</div>');
                setTimeout(function () {
                    makeRosterRequest(year, weeks, managers, managerIndex + 1, callback);
                }, 2000);
            }
        });   
    }

    // Clipboard copy functionality
    $(document).ready(function() {
        $('.copy-btn').click(function(e) {
            e.preventDefault();
            var textToCopy = $(this).data('clipboard-text');
            
            // Modern clipboard API
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(textToCopy).then(function() {
                    showCopyFeedback($(e.target));
                }).catch(function(err) {
                    console.error('Failed to copy: ', err);
                    fallbackCopyTextToClipboard(textToCopy, $(e.target));
                });
            } else {
                // Fallback for older browsers
                fallbackCopyTextToClipboard(textToCopy, $(e.target));
            }
        });
    });

    function fallbackCopyTextToClipboard(text, button) {
        var textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        textArea.style.left = "-999999px";
        textArea.style.top = "-999999px";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            var successful = document.execCommand('copy');
            if (successful) {
                showCopyFeedback(button);
            }
        } catch (err) {
            console.error('Fallback: Unable to copy', err);
        }
        
        document.body.removeChild(textArea);
    }

    function showCopyFeedback(button) {
        var originalText = button.text();
        button.text('✓ Copied!');
        button.removeClass('btn-outline-primary').addClass('btn-success');
        
        setTimeout(function() {
            button.text(originalText);
            button.removeClass('btn-success').addClass('btn-outline-primary');
        }, 2000);
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