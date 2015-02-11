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
                $( "#accordion" ).accordion({
                    collapsible: true,
                    heightStyle: "content"
                });
            });
            $(function() {
                $( "#tabs" ).tabs();
            });
            $(function() {
                $( "input[type=submit], a, button" ).button()
            });
            $(function() {
                $( "input[type=file], a, button" ).button()
            });
            $(function() {
                $( "#radio" ).buttonset();
            });
            $(function() {
                $( "input[type=checkbox], a, button" ).button();
            });
        </script>
'));

echo $navigation;

echo $logo;

bar($page);

$error = $deldomain = $newhost = $cookiename = $cookiepath = $cookiedomain = $cookieinfo = $purgecache = "";

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

$ini_array = (parse_ini_file("tmp/{$_SESSION['conffile']}", true));

foreach ($ini_array as $category => $value) {
    if ($category == "hostname") {
        foreach ($value as $domain_name) {
            $domains[] = $domain_name;
        }
        sort($domains);
    }
}

if (isset($_POST['formid'])) {
    if ($_POST['formid'] === 'delform') {
        foreach ($_POST['deldomain'] as $deldomain_dirty) {
            $deldomains[] = filter_var($deldomain_dirty, FILTER_SANITIZE_STRING);
        }
        foreach ($deldomains as $deldomain){
            if (!in_array($deldomain, $domains)) {
                echo "$delomain is not an exisitng hostname<br />";
            }
        }
        $ini_array['hostname'] = array_diff($ini_array['hostname'], $deldomains);
        if (count($ini_array['hostname']) < 1) {
            $error = "You must have at least one active domain<br />"
                    . "If you need assistance, please contact support<br />";
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
                        $data = '';
                        while ($buf = fread($stream, 4096)) {
                            $data .= $buf;
                        }
                        fclose($stream);
                    }
                }
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
                    fwrite($fh, $text) or die('Could not write to temp file');
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
                foreach ($deldomains as $deldomain) {
                    echo "$deldomain has been deleted.<br />";
                }
                unset($_POST);
            }
            if(!($con = ssh2_connect($server, $port))) {
                die('Failed to establish connection');
            } else {
                if(!(ssh2_auth_password($con, $ssh_user, $ssh_pass))) {
                    die('Failed to authenticate');
                } else {
                    // $command1 = "sudo lbconfig";
                    // $command2 = "sudo lbsync local";
                    // $command3 = "sudo lbsync";
                    $command1 = "touch $dir/boogieoogie"; /* temp placeholder */
                    if(!($stream1 = ssh2_exec($con, $command1))) {
                        die('Unable to execute command');
                    } else {
                        stream_set_blocking($stream, true);
                        $data = '';
                        while ($buf = fread($stream,4096)) {
                            $data .= $buf;
                        }
                        fclose($stream1); //repeat 2 more times
                    }
                }
            }
            header('Refresh: 3');
        }
    } elseif ($_POST['formid'] === 'addform') {
        $newhost = filter_input(INPUT_POST, 'newhost', FILTER_SANITIZE_STRING);
        $host_validate = '/([0-9a-z-]+\.)?[0-9a-z-]+\.[a-z]{2,7}/';
        if (!preg_match($host_validate, $newhost)) {
            echo "$newhost is not a valid domain name<br />";
        } else {
            if (in_array($newhost, $domains)) {
                $error = "This domain is already being accelerated by FTW!<br />";
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
                    // $command1 = "sudo lbconfig";
                    // $command2 = "sudo lbsync local";
                    // $command3 = "sudo lbsync";
                    $command1 = "touch $dir/stinkypinky"; /* temp placeholder */
                    if(!($stream1 = ssh2_exec($con, $command1))) {
                        die('Unable to execute command');
                    } else {
                        stream_set_blocking($stream, true);
                        $data = '';
                        while ($buf = fread($stream,4096)) {
                            $data .= $buf;
                        }
                        fclose($stream1); //repeat 2 more times
                    }
                }
            }
            header('Refresh: 3');
        }
    } elseif ($_POST['formid'] === 'exceptform') {
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
            unset($_POST);
            header('Refresh: 3');
        }
    } elseif ($_POST['formid'] === 'purgeform') {
        foreach ($_POST['purgecache'] as $purgecache_dirty) {
            $purgecachearr[] = filter_var($purgecache_dirty, FILTER_SANITIZE_STRING);
        }
        foreach ($purgecachearr as $purgecache) {
            if (!in_array($purgecache, $domains)) {
                echo "$purgecache is not an existing hostname<br />";
            }
        }
        if (!($con = ssh2_connect($server, $port))) {
            die('Failed to establish connection');
        } else {
            if(!(ssh2_auth_password($con, $ssh_user, $ssh_pass))) {
                die('Failed to authenticate');
            } else {
                $dir = "/home/ftwportal/conf";
                $command = "sudo touch $dir/muhaha.txt"; // place holder
                // $command = "sudo lbrun ban host $purgecache"; /* real command needs set for array*/
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
                foreach ($purgecachearr as $purgecache) {
                    echo "The cache for $purgecache is being cleared<br />";
                }
                unset($_POST);
            }
            header('Refresh: 3');
        }
    } elseif ($_POST['formid'] === 'errform') {
        echo "You matter to us!<br />";
        unset($_POST);
        header('Refresh: 3');
    } elseif ($_POST['formid'] === 'sslform') {
        echo "Secure is the way to go!<br />";
        unset($_POST);
        header('Refresh: 3');
    }
}
?>
<div id="tabs">
    <ul>
        <li><a href="#tabs-dom" title="Manage Domains">Manage Domains</a></li>
        <li><a href="#tabs-purge" title="Clear Cache">Clear Cache</a></li>
        <li><a href="#tabs-err" title="Pretty Error Pages">Error Pages</a></li>
        <li><a href="#tabs-ssl" title="Add SSL Domain">SSL Domains</a>
        <li><a href="#tabs-cookie" title="Cookie Exceptions">Cookie Exceptions</a></li>
    </ul>
    <div id="tabs-dom">
        <div id="accordion">
            <h3 title="Add Domains">Add Domains</h3>
            <div id="add">
                <form method='post' action='confedit.php'>
                    <table>
                        <tr title="New Domain">
                            <td style="float:left;">
                                Add Domain: 
                            </td>
                            <td style="float:right;">
                                <input type='text' maxlength='253' name='newhost' placeholder="Enter New Domain Name" />
                            </td>
                        </tr>
                        <tr title="Add Domain">
                            <td style="float:left;"> </td>
                            <td style="float:right;">
                                <input type="submit" value="Add" />
                            </td>
                        </tr>
                    </table>
                    <input type='hidden' name='formid' value='addform' />
                </form>
            </div>
            <h3 title="Delete Domains">Delete Domains</h3>
            <div id="del">
                <form method='post' action='confedit.php'>
                    <table>
<?php
$x =0;
foreach ($domains as $domain) {
    $check = "check" .++$x;
?>
                        <tr title='<?php echo $domain; ?>'>
                            <td style="float:right;">
                                <input type="checkbox" id="<?php echo $check; ?>" name="deldomain[]" value="<?php echo $domain; ?>"/>
                                <label for="<?php echo $check; ?>">
                                    <?php echo $domain; ?>
                                </label>
                            </td>
                        </tr>
 <?php
}
    ?>
                        <tr><td></td></tr>
                        <tr><td></td></tr>
                        <tr title='Remove Domain'>
                            <td style="float:right;">
                                <input type="submit" value="Remove" />
                            </td>
                        </tr>
                    </table>
                        <input type='hidden' name='formid' value='delform' />
                </form>
            </div>
        </div>
    </div>
    <div id="tabs-cookie">
        <form method="post" action="confedit.php">
            <table>
                <tr title="Pass Cookie">
                    <td>
                        <label>
                            <span style="float:left;font-weight:bold;">
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
                <tr title="Cookie Name">
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
                                <input type='text' maxlength='253' name='cookiename' value="" placeholder="Enter a Rule Name"/>
                            </span>
                        </label>
                    </td>
                </tr>
                <tr title="Cookie Path">
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
                                <input type='text' maxlength='253' name='cookiepath' value="" placeholder="Optional: Enter the Directory Path"/>
                            </span>
                        </label>
                    </td>
                </tr>
                <tr title="Cookie Domain">
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
                                <input type='text' maxlength='253' name='cookiedomain' value="" placeholder="Enter the Domain or Subdomain"/>
                            </span>
                        </label>
                    </td>
                </tr>
                <tr title="Other Info">
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
                                <input type='text' maxlength='253' name='cookieinfo' value="" placeholder="Enter Any Extra Information"/>
                            </span>
                        </label>
                    </td>
                </tr>
                <tr title="Request">
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
            <table>
<?php
$y = 1000;
foreach ($domains as $domain) {
    $check2 = "check" .++$y;
?>
                <tr title="<?php echo $domain ?>">
                    <td style="float:right;">
                        <input type="checkbox" id="<?php echo $check2; ?>" name="purgecache[]" value="<?php echo $domain ?>" />
                        <label for="<?php echo $check2; ?>">
                            <?php echo $domain ?>
                        </label>
                    </td>
                </tr>
 <?php
}
?>
                <tr><td></td></tr>
                <tr><td></td></tr>
                <tr title="Purge Cache">
                    <td style="float:right;">
                        <input type="submit" value="Purge" />
                    </td>
                </tr>
            </table>
            <input type='hidden' name='formid' value='purgeform' />
        </form>
    </div>
    <div id="tabs-err">
        <form method="post" action="confedit.php">
            <table>
                <tr title="Error Pages Select">
                    <td>
                        <span style="float:left;">
                            Pretty Error Pages: 
                        </span>
                    </td>
                    <td>
                        <div id="radio" style="float:right;">
                            <input type="radio" id="radio1" name="on"
                                   title="On"><label for="radio1">On</label>
                            <input type="radio" id="radio2" name="on"
                                   title="Off" checked="checked"><label for="radio2">Off</label>
                        </div>
                    </td>
                </tr>
                <tr title="Set Error Pages">
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
                                <input type="submit" value="Set" />
                            </span>
                        </label>
                    </td>
                </tr>
            </table>
            <input type="hidden" name="formid" value="errform" />
        </form>
    </div>
    <div id="tabs-ssl">
        <form method="post" action="confedit.php" enctype="multipart/form-data">
            <table>
                <tr title="Add SSL Domain">
                    <td>
                        <label>
                            <span style="float:left;">
                                Add SSL Domain: 
                            </span>
                        </label>
                    </td>
                    <td>
                        <label>
                            <span style="float:right;">
                                <input type='text' maxlength='253' name='ssldomain' value="" placeholder="Enter SSL Domain"/>
                            </span>
                        </label>
                    </td>
                </tr>
                <tr title="Add PEM">
                    <td>
                        <label>
                            <span style="float:left;">
                                Paste PEM: 
                            </span>
                        </label>
                    </td>
                    <td>
                        <label>
                            <span style="float:right;">
                                <textarea cols="25" rows="10" name="pem" placeholder="Please Paste Your PEM Here"></textarea>
                            </span>
                        </label>
                    </td>
                </tr>
                <tr title="Add SSL Domain">
                    <td>
                        <label>
                            <span style="float:left;">
                                <input type="file" name="pemul" />
                            </span>
                        </label>
                    </td>
                    <td>
                        <label>
                            <span style="float:right;">
                                <input type="submit" value="Submit" />
                            </span>
                        </label>
                    </td>
                </tr>
            </table>
            <input type="hidden" name="formid" value="sslform" />
        </form>
    </div>
</div>
<?php

echo "<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;<span"
    . " style='color:BurlyWood;font-size:12pt;font-weight:bold'>$error</span><br />";

tail();
?>
