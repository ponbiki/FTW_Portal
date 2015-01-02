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
        if (!($inifile = ssh2_scp_recv($con, $_SESSION['confpath'], '/tmp/'.$_SESSION['conffile'] ))) {
            die('Unable to get file');
        }
        fclose($stream);
    }
}

$ini_array = parse_ini_file('/tmp/'.$_SESSION['conffile'], true);

foreach ($ini_array as $category => $value) {
    if ($category == "hostname") {
        foreach ($value as $domain_name) {
            $domains[] = $domain_name;
        }
    }
}
?>
<form method='post' action='domaindel.php'>
    <table style="float:left;">
<?php
foreach ($domains as $domain) {
    echo <<<_END
        <tr>
            <td><label><span style="float:left;">$domain</span>
            <span style="float:right;">
                <input type="radio" name="deldomain" value="$domain" /></span>
            </label></td>
        </tr>    
_END;
}
?>
        <tr>
            <td><label><span style="float:left;"><?php echo $error; ?></span>
                    <span style="float:right;"><input type="submit" value="Remove" /></span>
                </label></td>
        </tr>
    </table>
</form>
<?php
tail();
?>
