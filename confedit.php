<?php
require_once 'conninfo.php';
require_once 'header.php';

$page = "Configuration Edit";

htmlheader("Conf Edit", $meta);

echo $logo;

bar($page);

$command = "cat ".$_SESSION['confpath'];

if (!($con = ssh2_connect($server, $port))) {
    die('Failed to establish connection');
} else {
    if (!ssh2_auth_password($con, $ssh_user, $ssh_pass)) {
        die('Failed to authenticate');
    } else {
        // if (!($inifile = ssh2_scp_recv($con, $_SESSION[;configpath', /tmp/], )))
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



echo nl2br($data);
echo "<h1>".$_SESSION['confpath']."</h1>";
echo "<h1>".$_SESSION['conffile']."</h1>";
tail();
?>