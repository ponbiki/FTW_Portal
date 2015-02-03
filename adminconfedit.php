<?php
require_once 'header.php';
require_once 'conninfo.php';

if (!$loggedin) {
    header("Location: index.php");
}
if (!$admin) {
    header("Location: index.php");
}

$page = "Configuration Edit";

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
                $( "#radio" ).buttonset();
            });
        </script>
'));

echo $navigation;

echo $logo;

bar($page);

$error = $deldomain = $newhost = '';

if (!($con = ssh2_connect($server, $port))) {
    die('Failed to establish connection');
} else {
    if (!ssh2_auth_password($con, $ssh_user, $ssh_pass)) {
        die('Failed to authenticate');
    } else {
        if (!($inifile = ssh2_scp_recv($con, $_SESSION['confpath'], "tmp/{$_SESSION['conffile']}" ))) {
            die('Unable to get file');
        }
    }
}

$ini_array = (parse_ini_file("tmp/{$_SESSION['conffile']}", true));

foreach ($ini_array as $category => $value) {
    if ($category == "hostname") {
        foreach ($value as $domain_name) {
            $domains[] = $domain_name;
        }
        sort($domains);
    }
}
?>

<div id="tabs">
    <ul>
        <li><a href="#tabs-add" title="Add Domain">Add Domain</a></li>
        <li><a href="#tabs-purge" title="Clear Cache">Clear Cache</a></li>
        <li><a href="#tabs-del" title="Remove Domain">Remove Domain</a></li>
        <li><a href="#tabs-err" title="Pretty Error Pages">Error Pages</a></li>
    </ul>
    <div id="tabs-del">
        <form method='post' action='confedit.php'>
                <table>
<?php
foreach ($domains as $domain) {
?>
                    <tr title="<?php echo $domain ?>">
                        <td>
                            <label>
                                <span style="float:left;"><?php echo $domain ?></span>
                                <span style="float:right;">
                                <input type='radio' name='deldomain' value='<?php echo $domain ?>' />
                                </span>
                            </label>
                        </td>
                    </tr>
 <?php
}
?>
                    <tr title="Remove Domain">
                        <td>
                            <label>
                                <span style="float:left;">
                                    <?php echo $error ?>
                                </span>
                                <span style="float:right;">
                                    <input type="submit" value="Remove" />
                                </span>
                            </label>
                        </td>
                    </tr>
                </table>
                <input type='hidden' name='formid' value='delform' />
        </form>
    </div>
    <div id="tabs-add">
        <form method='post' action='confedit.php'>
            <table>
                <tr title="New Domain">
                    <td>
                        <label>
                            <span style='float:left;'>
                                Domain Name: 
                            </span>
                        </label>
                    </td>
                    <td>
                        <label>
                            <span style='float:right;'>
                                <input type='text' maxlength='253' name='newhost' value="" />
                            </span>
                        </label>
                    </td>
                </tr>
                <tr title="Add Domain">
                    <td>
                        <label>
                            <span style='float:left;'>
                                <?php echo $error ?>
                            </span>
                        </label>
                    </td>
                    <td>
                        <label>
                            <span style='float:right;'>
                                <input type="submit" value="Add" />
                            </span>
                        </label>
                    </td>
                </tr>
            </table>
            <input type='hidden' name='formid' value='addform' />
        </form>
    </div>
    <div id="tabs-purge">
        <form method='post' action='confedit.php'>
                <table>
<?php
foreach ($domains as $domain) {
?>
                    <tr title="<?php echo $domain ?>">
                        <td>
                            <label>
                                <span style="float:left;"><?php echo $domain ?></span>
                                <span style="float:right;">
                                <input type='radio' name='purgecache' value='<?php echo $domain ?>' />
                                </span>
                            </label>
                        </td>
                    </tr>
 <?php
}
?>
                    <tr title="Purge Cache">
                        <td>
                            <label>
                                <span style="float:left;">
                                    <?php echo $error ?>
                                </span>
                                <span style="float:right;">
                                    <input type="submit" value="Purge" />
                                </span>
                            </label>
                        </td>
                    </tr>
                </table>
                <input type='hidden' name='formid' value='purgeform' />
        </form>
    </div>
    <div id="tabs-err">
        <form method="post" action="confedit.php">
            <table>
                <tr title="Error Pages Select">
                    <td>
                        <span style="float:left;">Pretty Error Pages: </span>
                    </td>
                    <td>
                        <div id="radio" style="float:right;">
                            <input type="radio" id="radio1" name="on"
                                   title="On"><label for="radio1">On</label>
                            <input type="radio" id="radio2" name="on"
                                   title="Off" checked="checked"><label for="radio2">Off</label>
                        </div>
                    </td>
                </tr>
                <tr title="Set Error Pages">
                    <td>
                        <label>
                            <span style='float:left;'>
                                <?php echo $error ?>
                            </span>
                        </label>
                    </td>
                    <td>
                        <label>
                            <span style='float:right;'>
                                <input type="submit" value="Set" />
                            </span>
                        </label>
                    </td>
                </tr>
            </table>
            <input type="hidden" name="formid" value="errpages" />
        </form>
    </div>
</div>

<?php
echo "<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;<span"
    . " style='color:BurlyWood;font-size:12pt;font-weight:bold'>$error</span><br />";

tail();
?>
