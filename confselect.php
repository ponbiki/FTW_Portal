<?php
require_once 'conninfo.php';
require_once 'header.php';

$page = "Configuration Selection";

htmlheader("Conf Select", $meta);

echo $logo;

bar($page);

$command = "ls /home/ftwportal/conf";

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

$list = explode("\n",$data);

foreach($list as $file) {
    $extension = pathinfo($file);
    if ($extension['extension'] === "ini") {
        $ini[] = $file;
    }
}
?>
<form method='post' action=''><?php echo $error ?>
    <table style="float: left;">
        <tr>
             <td style="text-align: left;">Edit: </td><td style="text-align: right;">
                 <select name='conf'>
<?php
foreach ($ini as $choice) {
    echo "<option>$choice</option>\n";
}
?>              </select>
             </td>
        </tr><tr>
            <td></td><td style="text-align: right;"><input type="submit" value="Submit" /></td>
        </tr>
    </table>
</form>
<?php
if (isset($_POST['conf'])) {
    echo $_POST['conf']."<br />";
} else {
    echo "not yet<br />";
}

tail();
?>