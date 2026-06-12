<?php
$code = $_GET['code'] ?? null;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Yahoo Authorization</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 60px auto; text-align: center; }
        .code { font-size: 1.4em; font-weight: bold; background: #f4f4f4; border: 1px solid #ccc; border-radius: 6px; padding: 16px 24px; display: inline-block; margin: 20px 0; letter-spacing: 2px; }
        .copy-btn { cursor: pointer; padding: 8px 20px; background: #0275d8; color: white; border: none; border-radius: 4px; font-size: 1em; }
    </style>
</head>
<body>
    <?php if ($code): ?>
        <h2>Authorization successful</h2>
        <p>Copy this code and paste it into the admin page:</p>
        <div class="code" id="code"><?php echo htmlspecialchars($code); ?></div>
        <br>
        <button class="copy-btn" onclick="navigator.clipboard.writeText(document.getElementById('code').innerText).then(() => this.innerText = 'Copied!')">Copy Code</button>
        <p style="margin-top: 30px; color: #888;">You can close this tab after copying.</p>
    <?php else: ?>
        <h2>No authorization code received</h2>
        <p>Something went wrong. Please try the authorization flow again from the admin page.</p>
    <?php endif; ?>
</body>
</html>
