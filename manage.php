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
$mysqli->close();

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
                        <input type='text' maxlength='253' name='username' value="" />
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