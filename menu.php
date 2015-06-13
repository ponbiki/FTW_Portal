<?php
require_once 'conninfo.php';
require_once 'header.php';

if (!$loggedin) {
    header("Location: index.php");
}

$page = "Menu";

unset($info);
$info = array();
unset($error);
$error = array();

$conf = "";

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
    //if (@$extension['extension'] === "ini") {
    if (@$extension['extension'] === "cfg") {
        //$ini[] = $extension['filename'];
        $cfg[] =$extension['filename'];
    }
}

$res = $mysqli->query("SELECT conf FROM confs WHERE username='{$_SESSION['user']}'");
//$dbiniarray = $res->fetch_all();
$dbcfgarray = $res->fetch_all();
$res->free();
$mysqli->close();

//foreach ($dbiniarray as $dbia) {
foreach ($dbcfgarray as $dbca) {
    //foreach ($dbia as $ia) {
    foreach ($dbca as $ca) {
        //$dbini[] = $ia;
        $dbcfg[] = $ca;
    }
}

//$confavail = array_intersect($dbini, $ini);
$confavail = array_intersect($dbcfg, $cfg);
if (isset($_POST['conf'])) {
    $conf = filter_input(INPUT_POST, 'conf', FILTER_SANITIZE_STRING);
    if (!in_array($conf, $confavail)) {
        $error[] = "You must select a valid configuration!";
    } else {
        //$_SESSION['confpath'] = "$dir/$conf.ini";
        $_SESSION['confpath'] = "$dir/$conf.cfg";
        //$_SESSION['conffile'] = "$conf.ini";
        $_SESSION['conffile'] = "$conf.cfg";
        header('Location: confedit.php');
    }
}

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
            $(function() {
                $( "#user" ).selectmenu();
            });
            $(document).ready(function() {
                setTimeout(function() {
                    $( ".notify" ).fadeOut(1000, function () {
                        //$( ".notify" ).remove();
                        $( ".notify" ).css({"visibility":"hidden",display:"block"}).slideUp();
                    });
                }, 3500);
            });
        </script>'
    ));

echo $navigation;

echo $logo;

bar($page);

if (!empty($info)) {
    info($info);
}

if (!empty($error)) {
    error($error);
}
?>
<div id="tabs">
    <ul>
        <li><a href="#tabs-conf" title="Select Configuration File">Select Conf File</a></li>
        <li><a href="#tabs-usr" title="User Options">User Options</a></li>
    </ul>
    <div id="tabs-conf">
        <form method='post' action='menu.php'>
            <table>
                <tr title="Configuration File">
                    <td style="float:left;margin-top:8px;margin-right:8px;">
                        Edit: 
                    </td>
                    <td style="float:right;">
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
                    <td style="float:left;">
                    </td>
                    <td style="float:right;">
                        <input type="submit" value="Select" />
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <div id="tabs-usr">
        <form method="post" action="menu.php">
            <table>
                <tr title="User">
                    <td style="float:left;margin-top:8px;margin-right:8px;">
                        User: 
                    </td>
                    <td style="float:right;">
                        <select name="user" id="user" style="width:200px;">
                            <option><?=$_SESSION['user']?></option>
                        </select>
                    </td>
                </tr>
                <tr title="select">
                    <td style="float:left;">
                    </td>
                    <td style="float:right;">
                        <input type="submit" value="Go" />
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

<?php
tail();
?>
