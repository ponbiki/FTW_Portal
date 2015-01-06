<?php
require_once 'header.php';
require_once 'conninfo.php';

if ($loggedin) {
    if ($admin) {
        header('Location: manage.php');
    } else {
        header('Location: confselect.php');
    }
}

$page = "FTW Log In";

htmlheader($page, $page, array());

echo $logo;

bar($page);

$error = $user = $pass = '';
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
                    <td style="text-align: left;"></td>
                    <td style="text-align: right;"><input type="submit" value="Login" /></td>
            </tr>
            </table>
        </form>
<?php
if (isset($_POST['user'])) {
    $user = filter_input(INPUT_POST, 'user', FILTER_SANITIZE_STRING);
    $pass = filter_input(INPUT_POST, 'pass', FILTER_SANITIZE_STRING);
    if ($user == "" || $pass == "") {
        $error = "Not all fields were entered";
    } else {
        $token = md5("$salt1$pass$salt2");
        $res = $mysqli->query("SELECT username,password,admin,company FROM users WHERE username='$user' AND password='$token'");
        if ($res->num_rows < 1) {
            $res->free();
            $error = "Username/Password invalid";
        } else {
            $_SESSION['user'] = $user;
            $_SESSION['pass'] = $token;
            $row = $res->fetch_array(MYSQLI_ASSOC);
            if ($row['admin'] !== 'Y') {
                $_SESSION['company'] = $row['company'];
                header('Location: confselect.php');
            } else {
                $res->free();
                $_SESSION['admin'] = $user;
                header('Location: manage.php');
            }
        }
    }
    $mysqli->close();
}

echo "<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;<span"
    . " style='color:BurlyWood;font-size:12pt;font-weight:bold'>$error</span><br />";

tail();
?>