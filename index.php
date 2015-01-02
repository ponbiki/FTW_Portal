<?php
require 'header.php';

if ($loggedin) header("Location: x.php");

$page = "FTW Log In";

htmlheader($page, $page, array());

echo $logo;

bar($page);

$salt1 = "qm&h*";
$salt2 = "pg!@";

$error = $user = $pass = '';

/*
if (isset($_POST['user'])) {
    $user = sanitizeString($_POST['user']);
    $pass = sanitizeString($_POST['pass']);

    if ($user == "" || $pass == "") {
        $error = "Not all fields were entered<br />";
    } else {
        $token = md5("$salt1$pass$salt2");
        $query = "SELECT username,password FROM users
            WHERE username='$user' AND password='$token'";

        if (mysql_num_rows(queryMysql($query)) == 0) {
            $error = "Username/Password invalid<br />";
        } else {
            $_SESSION['user'] = $user;
            $_SESSION['pass'] = $token;
            $query2 = "SELECT admin FROM users WHERE username='$user'";
            $sql = queryMysql($query2);
            $isadmin = mysql_fetch_row($sql);
            if ($isadmin[0] == "Y")
                $_SESSION['admin'] = $admin;
            die(header("Location: "));
        }
    }
}
 */

?>
        <form method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
            <table style="float: left;">
                <tr>
                    <td style="text-align: left;">Username</td><td style="text-align: right;">
                        <input type='text' maxlength='24' name='user' value="" /></td>
                </tr><tr>
                    <td style="text-align: left;">Password</td><td style="text-align: right;">
                        <input type='password' maxlength='24' name='pass' value="" /></td>
                </tr><tr>
                    <td style="text-align: left;"><?php echo $error; ?></td>
                    <td style="text-align: right;"><input type="submit" value="Login" /></td>
            </tr>
            </table>
        </form>
<?php
if (isset($_POST['user'])) {
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    if ($user == "" || $pass == "") {
        $error = "Not all fields were entered";
    }  else {
        die(header("Location: confselect.php"));
    }
} 
tail();
?>