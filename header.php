<?php
require 'functions.php';
session_start();

if (isset($_SESSION['user'])) {
    $djname = $_SESSION['user'];
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

        <meta name="robots" content="7chanradio, 7chan, Radio, $meta" />

        <link rel="shortcut icon" href="img/favicon.ico" type="image/vnd.microsoft.icon" />

END;

    foreach ($scripts as $x) {
        echo "$x\n\n";
    }

echo <<<_END
       <link rel="stylesheet" href="css/burichan.css" type="text/css" />\n

    </head>

    <body>

_END;
}

function tail() {
    echo <<<_END
   </body>
</html>

_END;
}
?>
