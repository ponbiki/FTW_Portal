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

htmlheader($page, $page, array('
             <script src="js/jquery.js"></script>
        <script src="js/jquery-ui.js"></script>
        <script>
            $(function() {
                $( "#tabs" ).tabs(/*{
                    event: "mouseover"
                }*/);
            });
            $(function() {
                $( "input[type=submit], a, button" )
                .button()
            });
            $(function() {
                $( "#user" ).selectmenu();
            });
        </script>
'));

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
    } else {
        if ($_POST[formid === 'changepass']) {
            if (!in_array($cguser, $cgusers)) {
                $error = "Please enter a valid username.<br />";
            } else {
                //do stuff   on web5cp....password filter relax
                //addnewsite script
                //on this no domain message for del box
            }
        }
    }
}
?>
<div id="tabs">
    <ul>
        <li><a href="#tabs-addusr">Add User</a></li>
        <li><a href="#tabs-delusr">Delete User</a></li>
        <li><a href="#tabs-chgpwd">Change Password</a></li>
    </ul>
    <div id="tabs-addusr">
        <form method='post' action='manage.php'>
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
    </div>
    <div id="tabs-delusr"></div>
    <div id="tabs-chgpwd">
        <form method='post' action='manage.php'>
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
                                <select name='cguser' id="user" style="width:200px;">
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
</div>
<?php

echo "<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;<span"
    . " style='color:BurlyWood;font-size:12pt;font-weight:bold'>$error</span><br />";

tail();
?>