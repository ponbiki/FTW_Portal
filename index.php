<?php
require 'header.php';

if ($loggedin) {
    if ($admin) {
        header('Location: adminconfselect.php');
    } else {
        header('location: confselect.php');
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
                    <td style="text-align: left;"><?php echo $error; ?></td>
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
    }  else {
        die(header("Location: confselect.php"));
    }
} 
tail();
?>