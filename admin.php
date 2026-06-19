<?php

session_start();

include_once 'connections.php';
include_once 'functions.php';

if (isset($APP_ENV) && $APP_ENV === 'production') {
    $authError = '';

    if (isset($_POST['admin_password'])) {
        $userRow = $conn->querySingle("SELECT password FROM users LIMIT 1", true);
        if ($userRow && password_verify($_POST['admin_password'], $userRow['password'])) {
            $_SESSION['admin_auth'] = true;
        } else {
            $authError = 'Incorrect password.';
        }
    }

    if (empty($_SESSION['admin_auth'])) {
        ?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin — Suntown FFB</title>
    <style>
        body { margin: 0; background: #f5f5f5; display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: sans-serif; }
        .login-box { background: #fff; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,.15); padding: 40px 36px; width: 320px; }
        .login-box h2 { margin: 0 0 24px; font-size: 20px; }
        .login-box input[type=password] { width: 100%; box-sizing: border-box; padding: 8px 12px; font-size: 15px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 16px; }
        .login-box button { width: 100%; padding: 9px; background: #5a8dee; color: #fff; border: none; border-radius: 4px; font-size: 15px; cursor: pointer; }
        .login-box button:hover { background: #4a7dde; }
        .error { color: #c0392b; margin-bottom: 12px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Admin</h2>
        <?php if ($authError): ?><div class="error"><?php echo htmlspecialchars($authError); ?></div><?php endif; ?>
        <form method="POST">
            <input type="password" name="admin_password" placeholder="Password" autofocus>
            <button type="submit">Sign in</button>
        </form>
    </div>
</body>
</html><?php
        exit;
    }
}

$pageName = "Admin";
include 'header.php';
include 'sidebar.php';

// Determine active tab
$activeTab = $_GET['tab'] ?? 'yahoo-api';

?>

<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-body">

            <!-- Tabs Navigation -->
            <div class="row mb-1">
                <div class="col-sm-12">
                    <div class="tab-buttons-container">
                        <button class="tab-button<?php echo $activeTab === 'yahoo-api' ? ' active' : ''; ?>" id="yahoo-api-tab" onclick="showCard('yahoo-api')">Yahoo API</button>
                        <button class="tab-button<?php echo $activeTab === 'newsletter' ? ' active' : ''; ?>" id="newsletter-tab" onclick="showCard('newsletter')">Newsletter</button>
                        <button class="tab-button<?php echo $activeTab === 'recap' ? ' active' : ''; ?>" id="recap-tab" onclick="showCard('recap')">Recap</button>
                    </div>
                </div>
            </div>

            <!-- Yahoo API Tab -->
            <div class="card-section" id="yahoo-api"<?php echo $activeTab !== 'yahoo-api' ? ' style="display: none;"' : ''; ?>>
                <?php include 'yahooApi.php'; ?>
            </div>

            <!-- Newsletter Tab -->
            <div class="card-section" id="newsletter"<?php echo $activeTab !== 'newsletter' ? ' style="display: none;"' : ''; ?>>
                <?php include 'editNewsletter.php'; ?>
            </div>

            <!-- Recap Tab -->
            <div class="card-section" id="recap"<?php echo $activeTab !== 'recap' ? ' style="display: none;"' : ''; ?>>
                <?php include 'generateSeasonRecap.php'; ?>
            </div>

        </div>
    </div>
</div>

<script>
function showCard(cardId) {
    document.querySelectorAll('.card-section').forEach(el => el.style.display = 'none');
    document.getElementById(cardId).style.display = 'block';
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    document.getElementById(cardId + '-tab').classList.add('active');

    // Update URL without page reload
    window.history.pushState({}, '', '?tab=' + cardId);
}
</script>

<?php include 'footer.php'; ?>

<!-- CKEditor for Newsletter tab -->
<link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/47.3.0/ckeditor5.css" crossorigin>
<script src="https://cdn.ckeditor.com/ckeditor5/47.3.0/ckeditor5.umd.js" crossorigin></script>
<script src="https://cdn.ckbox.io/ckbox/2.9.2/ckbox.js" crossorigin></script>

<script>
var access_token = null;

$( document ).ready( () => {

    var newsletterDirty = false;

    const {
        ClassicEditor,Autoformat,AutoImage,Autosave,BlockQuote,Bold,Emoji,
        Essentials,Heading,Indent,IndentBlock,Italic,Link,List,MediaEmbed,Mention,Paragraph,
        Table,TableCaption,TableToolbar,TextTransformation,TodoList,Underline
    } = CKEDITOR;

    let plugins = [
        Autoformat,AutoImage,Autosave,BlockQuote,Bold,Emoji,
        Essentials,Heading,Indent,IndentBlock,Italic,Link,List,MediaEmbed,Mention,Paragraph,
        Table,TableCaption,TableToolbar,TextTransformation,TodoList,Underline
    ];

    let toolbar = ['undo','redo','|',
        'heading','|',
        'bold','italic','underline','|',
        'emoji','link','mediaEmbed','insertTable','blockQuote','|',
        'bulletedList','numberedList','todoList','outdent','indent'
    ];

    // Initialize CKEditor for newsletter tab textareas (only if they exist)
    if ($('textarea#newsletter-recap').length) {
        ClassicEditor.create( $( 'textarea#newsletter-recap' )[ 0 ], {
            licenseKey: '<?php echo $CKEDITOR_LICENSE; ?>',
            plugins: plugins,
            toolbar: toolbar
        })
        .then( editor => {
            window.newsletterRecapEditor = editor;
            editor.model.document.on('change:data', function() { newsletterDirty = true; });
        })
        .catch( error => {
            console.error( 'Error initializing CKEditor 5 for recap:', error );
        });
    }

    if ($('textarea#preview').length) {
        ClassicEditor.create( $( 'textarea#preview' )[ 0 ], {
            licenseKey: '<?php echo $CKEDITOR_LICENSE; ?>',
            plugins: plugins,
            toolbar: toolbar
        })
        .then( editor => {
            window.newsletterPreviewEditor = editor;
            editor.model.document.on('change:data', function() { newsletterDirty = true; });
        })
        .catch( error => {
            console.error( 'Error initializing CKEditor 5 for preview:', error );
        });
    }

    if ($('textarea#newsletter-recap-notes').length) {
        ClassicEditor.create( $( 'textarea#newsletter-recap-notes' )[ 0 ], {
            licenseKey: '<?php echo $CKEDITOR_LICENSE; ?>',
            plugins: plugins,
            toolbar: toolbar
        })
        .then( editor => {
            window.newsletterRecapNotesEditor = editor;
            editor.model.document.on('change:data', function() { newsletterDirty = true; });
        })
        .catch( error => {
            console.error( 'Error initializing CKEditor 5 for recap-notes:', error );
        });
    }

    if ($('textarea#newsletter-notes').length) {
        ClassicEditor.create( $( 'textarea#newsletter-notes' )[ 0 ], {
            licenseKey: '<?php echo $CKEDITOR_LICENSE; ?>',
            plugins: plugins,
            toolbar: toolbar
        })
        .then( editor => {
            window.newsletterNotesEditor = editor;
            editor.model.document.on('change:data', function() { newsletterDirty = true; });
        })
        .catch( error => {
            console.error( 'Error initializing CKEditor 5 for notes:', error );
        });
    }

    // Yahoo API functionality - Manager selection
    $('input[name="managers[]"][value="all"]').change(function() {
        if ($(this).is(':checked')) {
            $('input[name="managers[]"]:not([value="all"])').prop('checked', false);
        }
    });

    $('input[name="managers[]"]:not([value="all"])').change(function() {
        if ($(this).is(':checked')) {
            $('input[name="managers[]"][value="all"]').prop('checked', false);
        }
    });

    // Yahoo API Submit
    $('#make_request').click(function () {
        var year = $('input[name="year"]').val();
        var weeks = [];
        $('input[name="weeks[]"]:checked').each(function () {
            weeks.push(parseInt($(this).val()));
        });

        var managers = [];
        $('input[name="managers[]"]:checked').each(function () {
            managers.push($(this).val());
        });

        var matchupsSelected = $('input[name="sections[]"][value="matchups"]:checked').length > 0;
        var playoffWeeksSelected = weeks.some(function(week) {
            return week > 14;
        });

        if (matchupsSelected && playoffWeeksSelected) {
            $('#output').html('<div class="alert alert-danger"><strong>Error:</strong> Playoff matchups (weeks 15+) are not available from the Yahoo API. Please deselect weeks 15-17 when updating matchups, or deselect the Matchups option.</div>');
            return false;
        }

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

    // Generate weekly preview with AI
    $('#generate-preview-btn').click(function() {
        var notes = '';
        if (window.newsletterNotesEditor) {
            notes = window.newsletterNotesEditor.getData();
        } else {
            notes = $('#newsletter-notes').val();
        }

        if (!notes || notes.replace(/<[^>]*>/g, '').trim() === '') {
            alert('Please enter some notes first.');
            return;
        }

        // Check if preview editor already has content
        var previewEditor = window.newsletterPreviewEditor;
        var existingPreview = previewEditor ? previewEditor.getData() : $('#preview').val();
        if (existingPreview && existingPreview.replace(/<[^>]*>/g, '').trim() !== '') {
            if (!confirm('The preview field already has content. Generating will show a new AI result above it — your current text will not be changed. Continue?')) {
                return;
            }
        }

        var btn = $(this);
        var originalText = btn.text();
        btn.text('Generating...').prop('disabled', true);
        $('#preview-ai-result').hide();
        $('#preview-ai-error').hide();

        $.ajax({
            url: 'generateWeeklyPreview.php',
            type: 'POST',
            data: { notes: notes },
            success: function(response) {
                btn.text(originalText).prop('disabled', false);
                if (response.error) {
                    $('#preview-ai-error').text(response.error).show();
                } else {
                    $('#preview-ai-text').text(response.text);
                    $('#preview-ai-result').show();
                }
            },
            error: function() {
                btn.text(originalText).prop('disabled', false);
                $('#preview-ai-error').text('Request failed. Please try again.').show();
            },
            dataType: 'json'
        });
    });

    // Generate weekly recap with AI
    $('#generate-recap-btn').click(function() {
        var notes = '';
        if (window.newsletterRecapNotesEditor) {
            notes = window.newsletterRecapNotesEditor.getData();
        } else {
            notes = $('#newsletter-recap-notes').val();
        }

        if (!notes || notes.replace(/<[^>]*>/g, '').trim() === '') {
            alert('Please enter some recap notes first.');
            return;
        }

        var recapEditor = window.newsletterRecapEditor;
        var existingRecap = recapEditor ? recapEditor.getData() : $('#newsletter-recap').val();
        if (existingRecap && existingRecap.replace(/<[^>]*>/g, '').trim() !== '') {
            if (!confirm('The recap field already has content. Generating will show a new AI result above it — your current text will not be changed. Continue?')) {
                return;
            }
        }

        var btn = $(this);
        var originalText = btn.text();
        btn.text('Generating...').prop('disabled', true);
        $('#recap-ai-result').hide();
        $('#recap-ai-error').hide();

        $.ajax({
            url: 'generateWeeklyRecap.php',
            type: 'POST',
            data: { notes: notes },
            success: function(response) {
                btn.text(originalText).prop('disabled', false);
                if (response.error) {
                    $('#recap-ai-error').text(response.error).show();
                } else {
                    $('#recap-ai-text').text(response.text);
                    $('#recap-ai-result').show();
                }
            },
            error: function() {
                btn.text(originalText).prop('disabled', false);
                $('#recap-ai-error').text('Request failed. Please try again.').show();
            },
            dataType: 'json'
        });
    });

    // Copy AI recap text to clipboard
    $('#copy-ai-recap-btn').click(function() {
        var text = $('#recap-ai-text').text();
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(function() {
                showCopyFeedback($('#copy-ai-recap-btn'));
            });
        } else {
            fallbackCopyTextToClipboard(text, $('#copy-ai-recap-btn'));
        }
    });

    // Copy AI preview text to clipboard
    $('#copy-ai-preview-btn').click(function() {
        var text = $('#preview-ai-text').text();
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(function() {
                showCopyFeedback($('#copy-ai-preview-btn'));
            });
        } else {
            fallbackCopyTextToClipboard(text, $('#copy-ai-preview-btn'));
        }
    });

    // Clipboard copy
    $('.copy-btn').click(function(e) {
        e.preventDefault();
        var textToCopy = $(this).data('clipboard-text');

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(textToCopy).then(function() {
                showCopyFeedback($(e.target));
            }).catch(function(err) {
                console.error('Failed to copy: ', err);
                fallbackCopyTextToClipboard(textToCopy, $(e.target));
            });
        } else {
            fallbackCopyTextToClipboard(textToCopy, $(e.target));
        }
    });

    // Newsletter unsaved-changes guard
    $('#headline').on('input', function() { newsletterDirty = true; });
    $('#hero_image, input[name="remove_hero_image"]').on('change', function() { newsletterDirty = true; });
    $('form[action="admin.php?tab=newsletter"]').on('submit', function() { newsletterDirty = false; });
    window.addEventListener('beforeunload', function(e) {
        if (newsletterDirty) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

} );
</script>

<script>
function makeRequest(year, weeks, managers) {
    var pendingRequests = $('input[name="sections[]"]:checked').length;
    var hasRosters = $('input[name="sections[]"][value="rosters"]:checked').length > 0;

    if (pendingRequests === 0) {
        $('#loading').hide();
        $('#output').html('<div class="alert alert-warning">Please select at least one section to update.</div>');
        return;
    }

    if (hasRosters) {
        pendingRequests--;
    }

    $('input[name="sections[]"]:checked').each(function () {
        let section = $(this).val();
        if (section == 'rosters') {
            makeRosterRequest(year, weeks, managers, 0, function() {
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
                    if (pendingRequests === 0 && !hasRosters) {
                        $('#loading').hide();
                    }
                },
                error: function() {
                    $('#output').append('<div class="alert alert-danger">Error processing ' + section + '. Please try again.</div>');
                    pendingRequests--;
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
    var managersToProcess = [];

    if (managers.includes('all')) {
        for (var i = 1; i <= 10; i++) {
            managersToProcess.push(i);
        }
    } else {
        managersToProcess = managers.filter(function(manager) {
            return manager !== 'all' && !isNaN(manager);
        });
    }

    if (managerIndex >= managersToProcess.length) {
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
