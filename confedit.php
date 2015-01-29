<?php
require_once 'conninfo.php';
require_once 'header.php';

if (!$loggedin) {
    header("Location: index.php");
}

$page = "Configuration Edit";

htmlheader($page, $page, array('
             <script src="js/jquery.js"></script>
        <script src="js/jquery-ui.js"></script>
        <script>
            $(function() {
                $( "#tabs" ).tabs();
            });
            $(function() {
                $( "input[type=submit], a, button" )
                .button()
            });
        </script>
'));

echo $navigation;

echo $logo;

bar($page);

$error = $deldomain = $newhost = "";

if (!($con = ssh2_connect($server, $port))) {
    die('Failed to establish connection');
} else {
    if (!ssh2_auth_password($con, $ssh_user, $ssh_pass)) {
        die('Failed to authenticate');
    } else {
        if (!($inifile = ssh2_scp_recv($con, $_SESSION['confpath'], "tmp/{$_SESSION['conffile']}" ))) {
            die('Unable to get file');
        }
    }
}

$ini_array = parse_ini_file("tmp/{$_SESSION['conffile']}", true);

foreach ($ini_array as $category => $value) {
    if ($category == "hostname") {
        foreach ($value as $domain_name) {
            $domains[] = $domain_name;
        }
    }
}

if (isset($_POST['formid'])) {
    if ($_POST['formid'] === 'delform') {
        $deldomain = filter_input(INPUT_POST, 'deldomain', FILTER_SANITIZE_STRING);
        if (!in_array($deldomain, $domains)) {
            echo "Please choose an exisitng hostname<br />";
        } else {
            if (!($con = ssh2_connect($server, $port))) {
                die('Failed to establish connection');
            } else {
                if(!(ssh2_auth_password($con, $ssh_user, $ssh_pass))) {
                    die('Failed to authenticate');
                } else {
                    $dir = "/home/ftwportal/conf";
                    $time = mktime();
                    $command = "cp $dir/{$_SESSION['conffile']} $dir/{$_SESSION['conffile']}.bak";
                    if (!($stream = ssh2_exec($con, $command))) {
                        die('Unable to execute command');
                    } else {
                        stream_set_blocking($stream, true);
                        $data ='';
                        while ($buf = fread($stream,4096)) {
                            $data .= $buf;
                        }
                        fclose($stream);
                    }
                }
            }
            $ini_array['hostname'] = array_diff($ini_array['hostname'], array($deldomain));          
            if (!unlink("tmp/{$_SESSION['conffile']}")) {
                die('Unable to delete temp file');
            } else {
                $fh = fopen("tmp/{$_SESSION['conffile']}", 'w') or die('Cannot create file');
                $text = '';
                foreach ($ini_array as $key => $value) {
                    if (!is_array($value)) {
                        $text .= "$key = $value\n";
                    } else {
                        foreach ($value as $key2 => $value2) {
                            if (!is_array($value2)) {
                                $text .= $key."[] = $value2\n";
                            } else {
                                foreach ($value2 as $key3 => $value3) { // 3rd iteration untested, and not currently used
                                    $text .= $key."[".$key2."][] = $value3\n";
                                }
                            }
                        }
                    }
                }
                fwrite($fh, $text) or die('Could not write to file');
                fclose($fh);
            }
            if(!($con = ssh2_connect($server, $port))) {
                die('Failed to establish connection');
            } else {
                if(!(ssh2_auth_password($con, $ssh_user, $ssh_pass))) {
                    die('Failed to authenticate');
                } else {
                    if(!(ssh2_scp_send($con, "tmp/{$_SESSION['conffile']}", "$dir/"
                            . "{$_SESSION['conffile']}", 0644))) {
                        die('Unable to send file');
                    }
                }
            }
            if (!unlink("tmp/{$_SESSION['conffile']}")) {
                die('Could not clean up temp file');
            }
            echo "$deldomain has been deleted.<br />";
            unset($_POST);
        }
        if(!($con = ssh2_connect($server, $port))) {
            die('Failed to establish connection');
        } else {
            if(!(ssh2_auth_password($con, $ssh_user, $ssh_pass))) {
                die('Failed to authenticate');
            } else {
                $dir = "/home/ftwportal/conf";
                // $command = "sudo lbconfig && lbsync local && lbsync";
                $command = "touch $dir/boogieboogie"; /* temp placeholder command */
                if(!($stream = ssh2_exec($con, $command))) {
                    die('Unable to execute command');
                } else {
                    stream_set_blocking($stream, true);
                    $data = '';
                    while ($buf = fread($stream,4096)) {
                        $data .= $buf;
                    }
                    fclose($stream);
                }
            }
            header('Refresh: 3');
        }
    } else {
        if ($_POST['formid'] === 'addform') {
            $newhost = filter_input(INPUT_POST, 'newhost', FILTER_SANITIZE_STRING);
            $host_validate = '/([0-9a-z-]+\.)?[0-9a-z-]+\.[a-z]{2,7}/';
            if (!preg_match($host_validate, $newhost)) {
                echo "$newhost is not a valid domain name<br />";
            } else {
                if (!($con = ssh2_connect($server, $port))) {
                    die('Failed to establish connection');
                } else {
                    if (!(ssh2_auth_password($con, $ssh_user, $ssh_pass))) {
                        die('Failed to authenticate');
                    } else {
                        $dir = "/home/ftwportal/conf";
                        $time = mktime();
                        $command = "cp $dir/{$_SESSION['conffile']} $dir/{$_SESSION['conffile']}.bak";
                        if (!($stream = ssh2_exec($con, $command))) {
                            die('Unable to execute command');
                        } else {
                            stream_set_blocking($stream, true);
                            $data ='';
                            while ($buf = fread($stream,4096)) {
                                $data .= $buf;
                            }
                            fclose($stream);
                        }
                    }
                }
                array_push($ini_array['hostname'], $newhost);
                if(!unlink("tmp/{$_SESSION['conffile']}")) {
                    die('Unable to delete temp file');
                } else {
                    $fh = fopen("tmp/{$_SESSION['conffile']}", 'w') or die('Cannot create file');
                    $text = '';
                    foreach ($ini_array as $key => $value) {
                        if (!is_array($value)) {
                            $text .= "$key = $value\n";
                        } else {
                            foreach ($value as $key2 => $value2) {
                                if (!is_array($value2)) {
                                    $text .= $key."[] = $value2\n";
                                } else {
                                    foreach ($value2 as $key3 => $value3) {
                                        $text .= $key."[".$key2."][] = $value3\n";
                                    }
                                }
                            }
                        }
                    }
                    fwrite($fh, $text) or die('Could not write file');
                    fclose($fh);
                }
                if(!($con = ssh2_connect($server, $port))) {
                    die('Failed to establish connection');
                } else {
                    if(!(ssh2_auth_password($con, $ssh_user, $ssh_pass))) {
                        die('Failed to authenticate');
                    } else {
                        if(!(ssh2_scp_send($con, "tmp/{$_SESSION['conffile']}", "$dir/"
                                . "{$_SESSION['conffile']}", 0644))) {
                            die('Unable to send file');
                        }
                    }
                }
                if (!unlink("tmp/{$_SESSION['conffile']}")) {
                    die('Could not clean up temp file');
                }
                echo "$newhost has been added.<br />";
                unset($_POST);
            }
            if(!($con = ssh2_connect($server, $port))) {
                die('Failed to establish connection');
            } else {
                if(!(ssh2_auth_password($con, $ssh_user, $ssh_pass))) {
                    die('Failed to authenticate');
                } else {
                    // $command = "sudo lbconfig && lbsync local && lbsync";
                    $command = "touch $dir/stinkypinky"; /* temp placeholder */
                    if(!($stream = ssh2_exec($con, $command))) {
                        die('Unable to execute command');
                    } else {
                        stream_set_blocking($stream, true);
                        $data = '';
                        while ($buf = fread($stream,4096)) {
                            $data .= $buf;
                        }
                        fclose($stream);
                    }
                }
                header('Refresh: 3');
            }
        } else {
            if ($_POST['formid'] === 'exceptform') {
                $cookiename = filter_input(INPUT_POST, 'cookiename', FILTER_SANITIZE_STRING);
                $cookiepath = filter_input(INPUT_POST, 'cookiepath', FILTER_SANITIZE_STRING);
                $cookiedomain = filter_input(INPUT_POST, 'cookiedomain', FILTER_SANITIZE_STRING);
                $cookieinfo = filter_input(INPUT_POST, 'cookieinfo', FILTER_SANITIZE_STRING);
                if ($cookiename == "" || $cookiedomain == "") {
                    echo "At a minimum, rule name and cookie domain need to be enetered.<br />";
                } else {
                    $to = 'supportteam@nyi.net';
                    $subject = "New caching exception request for {$_SESSION['user']}";
                    $message = "Client: {$_SESSION['user']} \n\nName: $cookiename"
                            . " \n\nPath: $cookiepath \n\nDomain: $cookiedomain"
                            . " \n\nExtra Info: $cookieinfo \n\nYour friendly neighborhood FTWPortal\n";
                    $from = "From: ftwportal@nyi.net\r";
                    mail($to, $subject, $message, $from);
                    echo "Request sent!";
                }
                header('Refresh: 3');
            } else {
                if ($_POST['formid'] === 'purgeform') {
                    $purgecache = filter_input(INPUT_POST, 'purgecache', FILTER_SANITIZE_STRING);
                    if (!in_array($purgecache, $domains)) {
                        echo "Please choose an existing hostname<br />";
                    } else {
                        if (!($con = ssh2_connect($server, $port))) {
                            die('Failed to establish connection');
                        } else {
                            if(!(ssh2_auth_password($con, $ssh_user, $ssh_pass))) {
                                die('Failed to authenticate');
                            } else {
                                $dir = "/home/ftwportal/conf";
                                $command = "sudo touch $dir/muhaha.txt"; // place holder
                                // $command = "sudo lbrun ban host $purgecache"; /* real command */
                                if(!($stream = ssh2_exec($con, $command))) {
                                    die('Unable to execute command');
                                } else {
                                    stream_set_blocking($stream, true);
                                    $data = '';
                                    while ($buf = fread($stream,4096)) {
                                        $data .= $buf;
                                    }
                                    fclose($stream);
                                }
                                echo "The cache for $purgecache is being cleared<br />";
                            }
                            header('Refresh: 3');
                        }
                    }
                }
            }
        }
    }
}
?>
<div id="tabs">
    <ul>
        <li><a href="#tabs-add">Add Domain</a></li>
        <li><a href="#tabs-cookie">Cookie Exceptions</a></li>
        <li><a href="#tabs-purge">Clear Cache</a></li>
        <li><a href="#tabs-del">Remove Domain</a></li>
    </ul>
    <div id="tabs-del">
        <form method='post' action='confedit.php'>
            <div id="radio">
                <table>
<?php
foreach ($domains as $domain) {
?>
                    <tr>
                        <td>
                            <label >
                                <span style="float:left;"><?php echo $domain ?></span>
                                <span style="float:right;">
                                <input type='radio' name='deldomain' value='<?php echo $domain ?>' />
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
                                    <?php echo $error ?>
                                </span>
                                <span style="float:right;">
                                    <input type="submit" value="Remove" />
                                </span>
                            </label>
                        </td>
                    </tr>
                </table>
                <input type='hidden' name='formid' value='delform' />
            </div>
        </form>
    </div>
    <div id="tabs-add">
        <form method='post' action='confedit.php'>
            <table>
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
            <input type='hidden' name='formid' value='addform' />
        </form>
    </div>
    <div id="tabs-cookie">
        <form method="post" action="confedit.php">
            <table>
                <tr>
                    <td>
                        <label>
                            <span style="float:left;">
                                 Pass Cookie
                            </span>
                        </label>
                    </td>
                    <td>
                        <label>
                            <span style="float:right;">
                                &nbsp;
                            </span>
                        </label>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label>
                            <span style="float:left;">
                                Name: 
                            </span>
                        </label>
                    </td>
                    <td>
                        <label>
                            <span style="float:right;">
                                <input type='text' maxlength='253' name='cookiename' value="" />
                            </span>
                        </label>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label>
                            <span style="float:left;">
                                Path: 
                            </span>
                        </label>
                    </td>
                    <td>
                        <label>
                            <span style="float:right;">
                                <input type='text' maxlength='253' name='cookiepath' value="" />
                            </span>
                        </label>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label>
                            <span style="float:left;">
                                Domain: 
                            </span>
                        </label>
                    </td>
                    <td>
                        <label>
                            <span style="float:right;">
                                <input type='text' maxlength='253' name='cookiedomain' value="" />
                            </span>
                        </label>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label>
                            <span style="float:left;">
                                Other Info: 
                            </span>
                        </label>
                    </td>
                    <td>
                        <label>
                            <span style="float:right;">
                                <input type='text' maxlength='253' name='cookieinfo' value="" />
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
                                <input type="submit" value="Request" />
                            </span>
                        </label>
                    </td>
                </tr>
            </table>
            <input type='hidden' name='formid' value='exceptform' />
        </form>
    </div>
    <div id="tabs-purge">
        <form method='post' action='confedit.php'>
            <div id="radio">
                <table>
<?php
foreach ($domains as $domain) {
?>
                    <tr>
                        <td>
                            <label >
                                <span style="float:left;"><?php echo $domain ?></span>
                                <span style="float:right;">
                                <input type='radio' name='purgecache' value='<?php echo $domain ?>' />
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
                                    <?php echo $error ?>
                                </span>
                                <span style="float:right;">
                                    <input type="submit" value="Purge" />
                                </span>
                            </label>
                        </td>
                    </tr>
                </table>
                <input type='hidden' name='formid' value='purgeform' />
            </div>
        </form>
    </div>
</div>
<?php

echo "<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;<span"
    . " style='color:BurlyWood;font-size:12pt;font-weight:bold'>$error</span><br />";

tail();
?>
