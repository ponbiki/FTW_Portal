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

$error = $user = $pass = '';
?>
    <div style="clear: both;">
        <form method='post' action='index.php' class='nyicp'>
            <h1><img src='img/NYI.png' class='nyiloginlogo' alt='NYI' title='NYI' />
                <img src='img/ftw_logo.png' class='ftwloginlogo' alt='NYI' title='NYI' />
                <span><?php echo $error; ?></span>
            </h1>
            <label>
                <span>Username: </span>
                <input id='user' type='text' maxlength='24' name='user' value='' />
            </label>
            <label>
                <span>Password: </span>
                <input id='pass' type='password' maxlength='24' name='pass' value='' />
            </label>
            <label class="nyicp">
                <span>&nbsp;</span>
                <input type='submit' value='Login' id="button" />
            </label>
        </form>
    </div>
<?php
if (isset($_POST['user'])) {
    $user = filter_input(INPUT_POST, 'user', FILTER_SANITIZE_STRING);
    $pass = filter_input(INPUT_POST, 'pass', FILTER_SANITIZE_STRING);
    if ($user == "" || $pass == "") {
        $error = "Not all fields were entered";
    } else {
        $token = hash('sha512', "$salt1$pass$salt2");
        $res = $mysqli->query("SELECT username,password,admin,company FROM users "
                . "WHERE username='$user' AND password='$token'");
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