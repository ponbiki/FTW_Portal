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
        if (!($inifile = ssh2_scp_recv($con, $_SESSION['confpath'], 'tmp/'.$_SESSION['conffile'] ))) {
            die('Unable to get file');
        }
        fclose($stream);
    }
}

$ini_array = parse_ini_file('tmp/'.$_SESSION['conffile'], true);

foreach ($ini_array as $category => $value) {
    if ($category == "hostname") {
        foreach ($value as $domain_name) {
            $domains[] = $domain_name;
        }
    }
}

if (isset($_POST['formid'])) {
    if ($_POST['formid'] == 'delform') {
        echo "Oh no!  Are you sure you want to remove ".$_POST['deldomain']."?<br />";
    }
}

?>

<form method='post' action='confedit.php'>
    <table style="float:left;">
<?php
foreach ($domains as $domain) {
?>
        <tr>
            <td>
                <label>
                    <span style="float:left;"><?php echo $domain ?></span>
                    <span style="float:right;">
                        <input type="radio" name="deldomain" value="<?php echo $domain ?>" />
                    </span>
                </label>
            </td>
        </tr>
 <?php
}
?>
        <tr>
            <td>
                <label>
                    <span style="float:left;">
                        <?php echo $error; ?>
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

<form method='post' action='confedit.php'>
    <table style='float:right;'>
        <tr>
            <td>
                <label>
                    <span style='float:left;'>
                        Add a new hostname
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
        <tr>
            <td>
                <label>
                    <span style='float:left;'>
                        <?php echo $hosterror ?>
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
<?php



tail();
?>
