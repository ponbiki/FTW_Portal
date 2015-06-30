<?php
/*
 * a programmer walks into a bar and says "give me 2.3 root beers."
 * the bartender says, "that's a root beer float."
 * the programmer say, "make it a double."
 */
require_once 'header.php';
require_once 'conninfo.php';

if ($loggedin) {
    if ($admin) {
        header('Location: manage.php');
        exit();
    } else {
        header('Location: menu.php');
        exit();
    }
}

$page = "FTW Log In";

unset($error);
$error = array();

$user = $pass = '';

if (isset($_POST['user'])) {
    $user = filter_input(INPUT_POST, 'user', FILTER_SANITIZE_STRING);
    $pass = filter_input(INPUT_POST, 'pass', FILTER_SANITIZE_STRING);
    if ($user == "" || $pass == "") {
        $error[] = "Not all fields were entered";
    } else {
        $token = hash('sha512', "$salt1$pass$salt2");
        $res = $mysqli->query("SELECT username,password,admin,company FROM users "
                . "WHERE username='$user' AND password='$token'");
        if ($res->num_rows < 1) {
            $res->free();
            $error[] = "Username/Password invalid";
        } else {
            $_SESSION['user'] = $user;
            $_SESSION['pass'] = $token;
            $row = $res->fetch_array(MYSQLI_ASSOC);
            if ($row['admin'] !== 'Y') {
                $_SESSION['company'] = $row['company'];
                header('Location: menu.php');
                exit();
            } else {
                $res->free();
                $_SESSION['admin'] = $user;
                header('Location: manage.php');
                exit();
            }
        }
    }
    $mysqli->close();
}

htmlheader($page, $page, array('
        <script src="js/jquery.js"></script>
        <script src="js/jquery-ui.js"></script>
        <script>
            $(function() {
                $( "input[type=submit], a, .jbutton" ).button()
            });
            $(document).ready(function() {
                setTimeout(function() {
                    $( ".notify" ).fadeOut(1000, function () {
                        //$( ".notify" ).remove();
                        $( ".notify" ).css({"visibility":"hidden",display:"block"}).slideUp();
                    });
                }, 3500);
            });
        </script>
'));

?>
    <div style="clear: both;">
        <form method='post' action='index.php' class='nyicp'>
            <h1><img src='img/NYI.png' class='nyiloginlogo' alt='NYI' title='NYI' />
                <img src='img/ftw_logo.png' class='ftwloginlogo' alt='NYI' title='FTW Portal' />
            </h1>
            <label title="Username">
                <span>Username: </span>
                <input id='user' type='text' maxlength='24' name='user' value='' placeholder='Enter Username'/>
            </label>
            <label title="Password">
                <span>Password: </span>
                <input id='pass' type='password' maxlength='24' name='pass' value='' placeholder='Enter Password'/>
            </label>
            <label title="Login">
                <span>&nbsp;</span>
                <input type='submit' value='Login' id="jbutton" />
            </label>
        </form>
        <div style='display:block;width:auto;margin-left:20%;margin-right:20%;'>
<?php
if (!empty($error)) {
    error($error);
}
?>
        </div>
    </div>

<?php

tail();
