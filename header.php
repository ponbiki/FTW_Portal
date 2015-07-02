<?php
require_once 'functions.php';

session_start();

if (!isset($_SESSION['generated']) || $_SESSION['generated'] < (time() - 30)) {
    session_regenerate_id();
    $_SESSION['generated'] = time();
}

if (isset($_SESSION['user'])) {
    $username = $_SESSION['user'];
    $loggedin = TRUE;
} else {
    $loggedin = FALSE;
}

if (isset($_SESSION['admin'])) {
    $admin = TRUE;
} else {
    $admin = FALSE;
}

function htmlheader($title, $meta, $scripts = array()) {
    echo <<<_END
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html" charset="UTF-8">

        <title>$title</title>

        <meta name="keywords" content="FTW, FTW Portal, $meta">

        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <meta name="mobile-web-app-capable" content="yes">

        <meta name="apple-mobile-web-app-capable" content="yes">

        <meta name="application-name" content="FTWPortal">

        <link rel="shortcut icon" sizes="192x192" href="img/applogo.png">

        <link rel="apple-touch-icon" sizes="192x192" href="img/applogo.png">

        <link rel="icon" href="img/favicon.ico" type="image/x-icon">

_END;

    foreach ($scripts as $x) {
        echo "$x\n\n";
    }

echo <<<_END
        <link rel="stylesheet" href="css/main.css" type="text/css" />
        <link rel="stylesheet" href="css/jquery-ui.css" type="text/css" />
        <link rel="stylesheet" href="css/jquery-ui.structure.css" type="text/css" />
        <link rel="stylesheet" href="css/jquery-ui.theme.css" type="text/css" />
    </head>

    <body background="img/rms.jpg">

_END;
}

function bar($page) {

    echo <<<_END

        <div class="pagebar" title="$page">
            <h2>$page</h2>
        </div>

_END;
}

function tail() {
    echo <<<_END
   </body>
</html>

_END;
}

$logo = "<div class='logo'><img class='logo' src='img/ftw_logo.png' title='FTW Logo' /></div>";

if (($loggedin)&&(!$admin)) {
    $navigation = "<div class='navbar'>
        <a href='menu.php' title='Menu'>Menu</a>
        <a href='logout.php' title='Logout'>Logout</a>
        </div>\n";
} else {
    if ($admin) {
        $navigation = "<div class='navbar'>
            <a href='manage.php' title='User Management'>Users</a>
            <a href='confmanage.php' title='Config File Management'>Configs</a>
            <a href='logout.php' title='Logout'>Logout</a>
            </div>\n";
    }
}