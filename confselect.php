<?php
require_once 'conninfo.php';
require_once 'header.php';

if (!$loggedin) {
    header("Location: index.php");
}

$page = "Configuration Selection";

htmlheader($page, $page, array('
             <script src="js/jquery.js"></script>
        <script src="js/jquery-ui.js"></script>
        <script>
            $(function() {
                $( "#tabs" ).tabs();
            });
            $(function() {
                $( "input[type=submit], a, button" )
                .button()
            });
            $(function() {
                $( "#conf" ).selectmenu();
            });
        </script>'
    ));

echo $navigation;

echo $logo;

bar($page);

$error = $conf = "";

$dir = "/home/ftwportal/conf";
$command = "ls $dir";

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
    if (@$extension['extension'] === "ini") {
        $ini[] = $extension['filename'];
    }
}

$res = $mysqli->query("SELECT conf FROM confs WHERE username='{$_SESSION['user']}'");
$dbiniarray = $res->fetch_all();
$res->free();
$mysqli->close();

foreach ($dbiniarray as $dbia) {
    foreach ($dbia as $ia) {
        $dbini[] = $ia;
    }
}

$confavail = array_intersect($dbini, $ini);

?>
<div id="tabs">
    <ul>
        <li><a href="#tabs-conf" title="Select Configuration File">Select Conf File</a></li>
    </ul>
    <div id="tabs-conf">
        <form method='post' action='confselect.php'>
            <table>
                <tr title="Configuration File">
                    <td style="text-align: left;">Edit: </td>
                    <td style="text-align: right;">
                        <select name='conf' id="conf" style="width:200px;">
<?php
foreach ($confavail as $choice) {
    echo "<option>$choice</option>\n";
}
?>
                        </select>
                     </td>
                </tr>
                <tr title="Select">
                    <td><?php echo $error ?></td>
                    <td style="text-align: right;">
                        <input type="submit" value="Select" />
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

<?php
if (isset($_POST['conf'])) {
    $conf = filter_input(INPUT_POST, 'conf', FILTER_SANITIZE_STRING);
    if (!in_array($conf, $confavail)) {
        echo "You must select a valid choice!<br />";
    } else {
        $_SESSION['confpath'] = "$dir/$conf.ini";
        $_SESSION['conffile'] = "$conf.ini";
        header('Location: confedit.php');
    }
}

echo "<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;<span"
    . " style='color:BurlyWood;font-size:12pt;font-weight:bold'>$error</span><br />";

tail();
?>