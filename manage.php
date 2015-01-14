<?php
require_once 'header.php';
require_once 'conninfo.php';

if (!$loggedin) {
    header("Location: index.php");
}
if (!$admin) {
    header("Location: index.php");
}

$page = "User Management";

htmlheader($page, $page, array());

echo $navigation;

echo $logo;

bar($page);

$error = '';

$res = $mysqli->query("SELECT username FROM users");
$cguser_array = $res->fetch_all();
$res->free();

if (isset($_POST['formid'])) {
    if ($_POST['formid'] === 'adduser') {
        $addusername = filter_input(INPUT_POST, 'addusername', FILTER_SANITIZE_STRING);
        $pass1 = filter_input(INPUT_POST, 'pass1', FILTER_SANITIZE_STRING);
        $pass2 = filter_input(INPUT_POST, 'pass2', FILTER_SANITIZE_STRING);
        if (($addusername == '')||($pass1 == '')||($pass2 == '')) {
            $error = "Not all fields were entered.<br />";
        } else {
            if ($pass1 !== $pass2) {
                $error = "Passwords entered do not match!<br />";
            } else {
                $token = md5("$salt1$pass1$salt2");
                $res = $mysqli->query("INSERT INTO users (username, password) VALUES ('$addusername', '$token')");
                if (!$res) {
                    die('Error: ('.$mysqli->errno.') '.$mysqli->error);
                } else {
                    echo "$addusername has been added!<br />";
                    unset($_POST);
                }
            }
        }
        header('Refresh:5');
    }
}
?>
<div style="clear: both;">
<form method='post' action='manage.php' style="float:left;">
    <table>
        <tr>
            <td>
                <label>
                    <span style='float:left;'>
                        Add a new user: 
                    </span>
                </label>
            </td>
            <td>
                <label>
                    <span style='float:right;'>
                        <input type='text' maxlength='253' name='addusername' value="" />
                    </span>
                </label>
            </td>
        </tr>
        <tr>
            <td>
                <label>
                    <span style='float:left;'>
                        Password: 
                    </span>
                </label>
            </td>
            <td>
                <label>
                    <span style='float:right;'>
                        <input type='password' maxlength='253' name='pass1' value="" />
                    </span>
                </label>
            </td>
        </tr>
        <tr>
            <td>
                <label>
                    <span style='float:left;'>
                        Re-Enter Password: 
                    </span>
                </label>
            </td>
            <td>
                <label>
                    <span style='float:right;'>
                        <input type='password' maxlength='253' name='pass2' value="" />
                    </span>
                </label>
            </td>
        </tr>
        <tr>
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
    <input type='hidden' name='formid' value='adduser' />
</form>

<form method='post' action='manage.php' style="float:right;">
    <table>
        <tr>
            <td>
                <label>
                    <span style="float:left;">
                        Change Password For: 
                    </span>
                </label>
            </td>
            <td>
                <label>
                    <span style="float:right;">
                        <select name='cguser'>
<?php
foreach ($cguser_array as $cgusers) {
    foreach ($cgusers as $cguser) {
        echo "<option>$cguser</option>\n";
    }
}
?>
                        </select>
                    </span>
                </label>
             </td>
        </tr>
        <tr>
            <td>
                <label>
                    <span style='float:left;'>
                        Password: 
                    </span>
                </label>
            </td>
            <td>
                <label>
                    <span style='float:right;'>
                        <input type='password' maxlength='253' name='cgpass1' value="" />
                    </span>
                </label>
            </td>
        </tr>
        <tr>
            <td>
                <label>
                    <span style='float:left;'>
                        Re-Enter Password: 
                    </span>
                </label>
            </td>
            <td>
                <label>
                    <span style='float:right;'>
                        <input type='password' maxlength='253' name='cgpass2' value="" />
                    </span>
                </label>
            </td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: right;">
                <input type="submit" value="Submit" />
            </td>
        </tr>
    </table>
    <input type='hidden' name='formid' value='changepass' />
</form>
</div>
<?php

echo "<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;<span"
    . " style='color:BurlyWood;font-size:12pt;font-weight:bold'>$error</span><br />";

tail();
?>