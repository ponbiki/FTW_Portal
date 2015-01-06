<?php
require 'header.php';

$page = "Log Out";

htmlheader($page, $page, array());

echo $logo;

bar($page);

if (isset($_SESSION['user'])) {
    destroySession();
    header("Location: index.php");
} else {
    echo "You are not logged in!<br />";
    echo "Return to <a href='index.php' title='FTW Portal'>the beginning</a>";
}

echo "<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;<span"
    . " style='color:BurlyWood;font-size:12pt;font-weight:bold'>$error</span><br />";

tail();
?>
