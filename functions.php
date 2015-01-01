<?php
require_once('conninfo.php');
/*
$dbhost = 'localhost';
$dbname = 'radio';
$dbuser = 'radio';
$dbpass = 'password';

mysql_connect($dbhost, $dbuser, $dbpass) or die(mysql_error());
mysql_select_db($dbname) or die(mysql_error());

function queryMysql($query) {
    $result = mysql_query($query) or die(mysql_error());
    return $result;
}
*/
function destroySession() {
    $_SESSION=array();

    if (session_id() != "" || isset($_COOKIE[session_name()]))
        setcookie(session_name(), '', time()-2592000, '/');

    session_destroy();
}

function sanitizeString($var) {
    $var = strip_tags($var);
    $var = htmlentities($var);
    $var = stripslashes($var);
    return mysql_real_escape_string($var);
}

function exec_ssh2($cmd) {
    if (!($con = ssh2_connect($server, $port))) {
        die('Failed to establish connection');
    } else {
        if (!ssh2_auth_password($con, $ssh_user, $ssh_pass)) {
            die('Failed to authenticate');
        } else {
            if (!($stream = ssh2_exec($con, $cmd))) {
                die('Unable to execute command');
            } else {
                stream_set_blocking($stream, true);
                $data = "";
                while ($buf = fread($stream,4096)) {
                    $data .= $buf;
                }
                fclose($stream);
            }
        }
    }
}
?>
