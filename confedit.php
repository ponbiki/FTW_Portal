<?php
require_once 'conninfo.php';
require_once 'header.php';

if (!$loggedin) {
    header("Location: index.php");
}

$page = "Configuration Edit";

htmlheader($page, $page, array(
    '        <script src="js/jquery.js"></script>
        <script src="js/jquery-ui.js"></script>
        <script>
            $(function() {
                $( "#tabs" ).tabs({
                    event: "mouseover"
                });
            });
        </script>'
));

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
                                foreach ($value2 as $key3 => $value3) {
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
            } else { //placeholder exec for the lb reload commands
                $dir = "/home/ftwportal/conf";
                $command = "touch $dir/boogieboogie";
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
            header('Refresh: 5');
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
                } else { //placeholder exec for the lb reload commands
                    $command = "touch $dir/stinkypinky";
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
                header('Refresh: 5');
            }
        }
    }
}
?>
<div id="tabs">
    <ul>
        <li><a href="#tabs-del">Remove Domain</a></li>
        <li><a href="#tabs-add">Add Domain</a></li>
    </ul>
    <div id="tabs-del">
        <form method='post' action='confedit.php'>
            <table>
<?php
foreach ($domains as $domain) {
?>
                <tr>
                    <td>
                        <label>
                            <span style="float:left;"><?php echo $domain ?></span>
                            <span style="float:right;">
                                <input type="radio" name="deldomain" value="<?php echo $domain ?>" />
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
                            </span>
                            <span style="float:right;">
                                <input class="ui-button" type="submit" value="Remove" id='button' />
                            </span>
                        </label>
                    </td>
                </tr>
            </table>
            <input type='hidden' name='formid' value='delform' />
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
                                <input class="ui-button" type="submit" value="Add" id='button' />
                            </span>
                        </label>
                    </td>
                </tr>
            </table>
            <input type='hidden' name='formid' value='addform' />
        </form>
    </div>
</div>
<?php

echo "<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;<span"
    . " style='color:BurlyWood;font-size:12pt;font-weight:bold'>$error</span><br />";

tail();
?>
