<?php
require_once 'header.php';
require_once 'conninfo.php';

if (!$loggedin) {
    header("Location: index.php");
}
if (!$admin) {
    header("Location: index.php");
}

$page = "Manage Interface";

htmlheader($page, $page, array());

echo $logo;

bar($page);

tail();
?>