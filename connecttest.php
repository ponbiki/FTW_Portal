<?php
// http://kvz.io/blog/2007/07/24/make-ssh-connections-with-php/
require_once('conninfo.php');

$command = "ls /usr/local/etc/ftw";

if (!($con = ssh2_connect($server, $port))) {
    die('Failed to establish connection');
} else {
    if (!ssh2_auth_password($con, $ssh_user, $ssh_pass)) {
        die('Failed to authenticate');
    } else {
        if (!($stream = ssh2_exec($con, $command))) {
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

echo "<pre>"; echo $data; echo "</pre>";
?>