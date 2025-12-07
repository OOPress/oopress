<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Example Module</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 50px; background-color: #f5f5f5; color: #333; }
        img.logo { width: 300px; height: auto; }
        h1 { color: #FF9500; }
        p { font-size: 1.2em; }
        @media (prefers-color-scheme: dark) {
            body { background-color: #1e1e1e; color: #f5f5f5; }
            img.logo { content: url("<?php echo $logoDark; ?>"); }
            h1 { color: #FFA500; }
        }
    </style>
</head>
<body>
    <img class="logo" src="<?php echo $logoLight; ?>" alt="OOPress Logo">
    <h1>Example Module Home</h1>
    <p>This is a dynamically loaded module page.</p>
    <p><a href="/">Back to OOPress Home</a></p>
</body>
</html>
