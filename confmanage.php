<?php
require_once 'header.php';
require_once 'conninfo.php';

if (!$loggedin) {
    header("Location: index.php");
}
if (!$admin) {
    header("Location: index.php");
}

$page = "Config File Management";

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
        </script>
'));

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


if (isset($_POST['formid'])) {
    if ($_POST['formid'] === 'confselect') {
        $conf = filter_input(INPUT_POST, 'conf', FILTER_SANITIZE_STRING);
    }
}
?>

<div id="tabs">
    <ul>
        <!--<li><a href="#tabs-new" title="New Config File">New Config</a></li>-->
        <li><a href="#tabs-mng" title="Manage Config File">Manage Conf</a></li>
        <li><a href="#tabs-assoc" title="Associate Conf With User">Associate Conf</a></li>
    </ul>
    <!--<div id="tabs-new">
        <form method="post" action="confmanage.php">
            <table>
                <tr title="Configuration Name">
                    <td>
                        <span style="float:left;">Name: </span>
                    </td>
                    <td>
                        <span style="float:right;">
                            <input title="Example: eccouncil" type="text"
                                   maxlength="253" name="confname" value="" />
                        </span>
                    </td>
                </tr>
                <tr title="Configuration Host">
                    <td>
                        <span style="float:left;">Host: </span>
                    </td>
                    <td>
                        <span style="float:right;">
                            <input title="Example: eccouncil.org" type="text"
                                   maxlength="253" name="confhost" value="" />
                        </span>
                    </td>
                </tr>
                <tr title="Configuration Hostnames">
                    <td>
                        <span style="float:left;">Hostnames: </span>
                    </td>
                    <td>
                        <span style='float:right;'>
                            <input title='Example: eccouncil.org (multiples space separated)' type='text'
                                   maxlength='253' name='confhostnames' value='' />
                        </span>
                    </td>
                </tr>
                <tr title='Configuration SSLHostnames (optional)'>
                    <td>
                        <span style='float:left;'>SSLHostnames: </span>
                    </td>
                    <td>
                        <span style='float:right;'>
                            <input title='Example: eccouncil.org (optionnal - multiples space separated)' type='text'
                                   maxlength='253' name='confsslhostnames' value='' />
                        </span>
                    </td>
                </tr>
            </table>
        </form>
    </div>-->
    <div id='tabs-mng'>
        <form method='post' action='confmanage.php'>
            <table>
                <tr title="Configuration File">
                    <td style="float:left;">Edit: </td>
                    <td style="float:right;">
                        <select name="conf" id="conf" style="width:200px;">
<?php
foreach ($ini as $choice) {
    echo "<option>$choice</option>\n";
}
?>
                        </select>
                    </td>
                </tr>
                <tr title="Select">
                    <td style="float:left;"><?php echo $error ?></td>
                    <td style="float:right;">
                        <input type="submit" value="Select" />
                    </td>
                </tr>
            </table>
            <input type="hidden" name="formid" value="confselect" />
        </form>
    </div>
</div>

<?php

echo "<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;<span"
    . " style='color:BurlyWood;font-size:12pt;font-weight:bold'>$error</span><br />";

tail();
?>