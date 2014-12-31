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

function htmlHeader($title, $meta, $scripts = array()) {
    echo <<<END
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html" charset=UTF-8">

        <title>$title</title>
            
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
            
        

        <meta name="robots" content="FTW, FTW Portal, $meta" />

        <link rel="shortcut icon" href="img/favicon.ico" type="mage/vnd.microsoft.icon" />
            
        <script src="http://code.jquery.com/jquery.js"></script>
            
        <script src="js/bootstrap.min.js"></script>

END;

    foreach ($scripts as $x) {
        echo "$x\n\n";
    }

echo <<<_END
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
