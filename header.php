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
    echo <<<END
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html" charset=UTF-8">

        <title>$title</title>

        <meta name="robots" content="FTW, FTW Portal, $meta">
            
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="icon" href="img/favicon.ico" type="image/x-icon">

END;

    foreach ($scripts as $x) {
        echo "$x\n\n";
    }

echo <<<_END
        <link rel="stylesheet" href="css/main.css" type="text/css" />\n
        <link rel="stylesheet" href="css/jquery-ui.css" type="text/css" />\n
        <link rel="stylesheet" href="css/jquery-ui.structure.css" type="text/css" />\n
        <link rel="stylesheet" href="css/jquery-ui.theme.css" type="text/css" />\n
    </head>

    <body>

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

$logo = "<div class='logo'><img class='logo' src='img/ftw_logo.png' title='FTW Logo' name='FTW Logo' /></div>";

if (($loggedin)&&(!$admin)) {
    $navigation = "<div class='navbar'>
        <a href='confselect.php' title='Config Selection'>Select Config</a>
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
?>