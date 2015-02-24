<?php
require_once 'conninfo.php';

function destroySession() {
    $_SESSION=array();

    if (session_id() != "" || isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-2592000, '/');
    }    
    session_destroy();
}

$mysqli = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

function error($error) {
    foreach ($error as $alert) {
        echo <<<_END
<div class="notify"><br />
    <div class="ui-widget">
        <div class="ui-state-error ui-corner-all" style="padding:0 .7em;">
            <p>
                <span class="ui-icon ui-icon-alert" style="float:left;margin-right:.3em;"></span>
                <strong>Alert:</strong>
                $alert
            </p>
        </div>
    </div>
<br /></div>
_END;
    }
}

function info($info) {
    foreach ($info as $hilite) {
        echo <<<_END
<div class="notify"><br />
    <div class="ui-widget">
        <div class="ui-state-highlight ui-corner-all" style="padding:0 .7em;">
            <p>
                <span class="ui-icon ui-icon-info" style="float:left;margin-right:.3em;"></span>
                <strong>Info:</strong>
                $hilite
            </p>
        </div>
    </div>
<br /></div>
_END;
    }
}
?>