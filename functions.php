<?php
$salt1 = "qm&h*";
$salt2 = "pg!@";

function destroySession() {
    $_SESSION=array();

    if (session_id() != "" || isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-2592000, '/');
    }    
    session_destroy();
}

?>
