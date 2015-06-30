<?php
require 'header.php';

$page = "Log Out";

unset($error);
$error = array();

if (isset($_SESSION['user'])) {
    destroySession();
    header("Location: index.php");
    exit();
} else {
    $error[] = "You are not logged in!<br />Return to <a href='index.php' title='FTW Portal'>the beginning</a>";
}

htmlheader($page, $page, array('
        <script src="js/jquery.js"></script>
        <script src="js/jquery-ui.js"></script>
        <script>
            $(document).ready(function() {
                setTimeout(function() {
                    $( ".notify" ).fadeOut(100000, function () {
                        //$( ".notify" ).remove();
                        $( ".notify" ).css({"visibility":"hidden",display:"block"}).slideUp();
                    });
                }, 3500);
            });
        </script>
'));


echo $logo;

bar($page);

if (!empty($error)) {
    error($error);
}

tail();

