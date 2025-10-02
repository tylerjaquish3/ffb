<?php
$pageName = 'Suggestions';
include 'header.php';
include 'sidebar.php';

// Handle form submission
// Show thank you message after submission
$showThankYou = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['description'], $_POST['submitted_by'])) {
    $desc = $_POST['description'];
    $by = $_POST['submitted_by'];
    $desc = str_replace("'", "''", $desc); // basic SQL escape
    $by = str_replace("'", "''", $by);
    query("INSERT INTO suggestions (description, submitted_by) VALUES ('$desc', '$by')");
    $showThankYou = true;
}

$result = query("SELECT * FROM suggestions WHERE is_active = 1 ORDER BY created_at DESC");
$suggestions = [];
while ($row = fetch_array($result)) {
    if ($row) $suggestions[] = $row;
}
?>
<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-body">
            <!-- Tabs Navigation -->
            <div class="row mb-1">
                <div class="col-sm-12">
                    <div class="tab-buttons-container">
                        <button class="tab-button active" id="submit-tab" onclick="showCard('submit')">
                            Submit Suggestion
                        </button>
                        <button class="tab-button" id="list-tab" onclick="showCard('list')">
                            Active Suggestions
                        </button>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="submit">
                <?php if ($showThankYou): ?>
                <div class="alert alert-success" role="alert" style="margin-bottom:1rem; direction: ltr;">
                    Thanks for your submission! It will be reviewed and taken into consideration.
                </div>
                <?php endif; ?>
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Submit a Suggestion</h4>
                        </div>
                        <div class="card-body p-2" style="direction: ltr;">
                            <form method="POST">
                                <div class="mb-2">
                                    <label for="submitted_by" class="form-label">Your Name</label>
                                    <input type="text" class="form-control" id="submitted_by" name="submitted_by" required value="Anonymous">
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Suggestion</label>
                                    <textarea class="form-control" id="description" name="description" rows="4" required placeholder="Please be as descriptive as possible"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="list" style="display: none;">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Active Suggestions</h4>
                        </div>
                        <div class="card-body p-1" style="direction: ltr; overflow-x: auto;">
                            <table id="datatable-suggestions" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Description</th>
                                        <th>Submitted By</th>
                                        <th>Completed</th>
                                        <th>Submitted</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($suggestions as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['description']) ?></td>
                                        <td><?= htmlspecialchars($row['submitted_by']) ?></td>
                                        <td><?= $row['is_completed'] ? 'Yes' : 'No' ?></td>
                                        <td><?= htmlspecialchars(substr($row['created_at'], 0, 10)) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>

<script type="text/javascript">
    function showCard(cardId) {
        document.querySelectorAll('.card-section').forEach(function(section) {
            section.style.display = 'none';
        });
        document.getElementById(cardId).style.display = '';
        document.querySelectorAll('.tab-button').forEach(function(btn) {
            btn.classList.remove('active');
        });
        document.getElementById(cardId+'-tab').classList.add('active');
    }
    document.addEventListener('DOMContentLoaded', function() {
        showCard('submit');
        // Initialize DataTable for suggestions
        if (window.jQuery && $('#datatable-suggestions').length) {
            $('#datatable-suggestions').DataTable({
                searching: true,
                paging: true,
                info: true,
                autoWidth: false,
                order: []
            });
        }
    });
</script>