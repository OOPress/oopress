<?php
namespace OOPress\Core;

class Application
{
    public function run(): void
    {
        echo "Hello World — OOPress development has started!\n";
    }

    public function runWeb(): void
    {
        // Base path for logos relative to public root
        $logoBasePath = '/assets/images/logo';

        // Choose default light logo
        $logoLight = $logoBasePath . '/oopress_logo.png';
        $logoDark  = $logoBasePath . '/oopress_logo_dark.png';

        // HTML output with automatic light/dark switching
        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OOPress - Hello World</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
            background-color: #f5f5f5;
            color: #333;
        }
        img.logo {
            width: 300px;
            height: auto;
        }
        h1 {
            color: #FF9500;
        }
        p {
            font-size: 1.2em;
        }

        /* Automatic dark mode logo */
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #1e1e1e;
                color: #f5f5f5;
            }
            img.logo {
                content: url("$logoDark");
            }
            h1 {
                color: #FFA500;
            }
        }
    </style>
</head>
<body>
    <img class="logo" src="$logoLight" alt="OOPress Logo">
    <h1>Hello World!</h1>
    <p>OOPress development has started.</p>
</body>
</html>
HTML;
    }
}
