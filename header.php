<?php
require 'functions.php';
session_start();

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

        <link rel="icon" href="img/favicon.ico" type="image/x-icon">

END;

    foreach ($scripts as $x) {
        echo "$x\n\n";
    }

echo <<<_END
       <link rel="stylesheet" href="css/base.css" type="text/css" />\n

    </head>

    <body>

_END;
}

function bar($page) {

    echo <<<_END

        <div class="replymode">
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

$logo = "<div class='logo'><img src='img/ftw_logo.png' title='FTW Logo' name='FTW Logo' /></div>";
?>
