<?php
require 'header.php';

$page = "Log Out";

htmlheader($page, $page, array());

echo $navigation; echo $logo;

bar($page);

if (isset($_SESSION['user'])) {
    destroySession();
    header("Location: index.php");
} else {
    echo "You are not logged in!<br />";
    echo "Return to <a href='index.php' title='FTW Portal'>the beginning</a>";
}

tail();
?>
