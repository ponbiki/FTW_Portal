<?php
require_once 'header.php';
require_once 'conninfo.php';

if (!$loggedin) {
    header("Location: index.php");
}
if (!$admin) {
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
            $(function() {
                $( "#radio" ).buttonset();
            });
        </script>
'));

echo $navigation;

echo $logo;

bar($page);

$error = $deldomain = $newhost = $purgecache = "";

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
        foreach ($deldomains as $deldomain) {
            if (!in_array($deldomain, $domains)) {
                echo "$deldomain is not an existing hostname<br />";
            }
        }
        $ini_array['hostname'] = array_diff($ini_array['hostname'], $deldomains);
        if (count($ini_array['hostname']) < 1) {
            $error = "You must have at least one active domain<br />";
        } else {
            if (!($con = ssh2_connect($server, $port))) {
                die('Failed to establish connection');
            } else {
                if(!(ssh2_auth_password($con, $ssh_user, $ssh_pass))) {
                    die('Failed to authenticate');
                } else {
                    $dir = "/home/ftwportal/conf";
                    $time = mktime();
                    $command = "cp $dir/{$_SESSION['conffile']} $dir/{$_SESSION['conffile']}.$time.bak";
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
                    $fh = fopen("tmp/{$_SESSION['conffile']}", w) or die('Cannot create file.');
                    $text = '';
                    foreach ($ini_array as $key => $value) {
                        if (!is_array($value)) {
                            $text .= "$key = $value\n";
                        } else {
                            foreach ($value as $key2 => $value2) {
                                if (!is_array($value2)) {
                                    $text .= $key."[] = $value2\n";
                                } else {
                                    foreach ($value2 as $key3 => $value3) { //unused and untested 3rd iteration
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
                    if (!(ssh2_auth_password($con, $ssh_user, $ssh_pass))) {
                        die('Failed to authenticate');
                    } else {
                        if (!(ssh2_scp_send($con, "tmp/{$_SESSION['conffile']}", "$dir/"
                                . "{$_SESSION['conffile']}", 0644))) {
                            die('Unable to send file.');
                        }
                    }
                }
                if (!unlink("tmp/{$_SESSION['conffile']}")) {
                    die('Could not clean up temp file');
                }
                foreach ($deldomains as $deldomain) {
                    echo "$deldomain has been deleted<br />";
                }
                unset($_POST);
            }
            if(!($con = ssh2_connect($server, $port))) {
                die('Failed to establish connection');
            } else {
                if (!(ssh2_auth_password($con, $ssh_user, $ssh_pass))) {
                    die('Failed to authenticate.');
                } else {
                    $dir = "/home/ftwportal/conf";
                    // $command = "sudo lbconfig && lbsync local && lbsync";
                    $command = "touch $dir/boogieboogie"; /* temp placeholder command */
                    if(!($stream = ssh2_exec($con, $command))) {
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
            }
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
                foreach ($purgecachearr as $purgecache) {
                    echo "The cache for $purgecache is being cleared<br />";
                }
                unset($_POST);
            }
            header('Refresh: 3');
        }
    }
}

?>

<div id="tabs">
    <ul>
        <li><a href="#tabs-add" title="Add Domain">Add Domain</a></li>
        <li><a href="#tabs-purge" title="Clear Cache">Clear Cache</a></li>
        <li><a href="#tabs-del" title="Remove Domain">Remove Domain</a></li>
        <li><a href="#tabs-err" title="Pretty Error Pages">Error Pages</a></li>
        <li><a href="#tabs-ssl" title="Add SSL Domain">SSL Domains</a>
    </ul>
    <div id="tabs-del">
        <form method='post' action='adminconfedit.php'>
                <table>
<?php
foreach ($domains as $domain) {
?>
                    <tr title="<?php echo $domain ?>">
                        <td>
                            <label>
                                <span style="float:left;"><?php echo $domain ?></span>
                                <span style="float:right;">
                                <input type='checkbox' name='deldomain[]' value='<?php echo $domain ?>' />
                                </span>
                            </label>
                        </td>
                    </tr>
 <?php
}
?>
                    <tr title="Remove Domain">
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
        </form>
    </div>
    <div id="tabs-add">
        <form method='post' action='adminconfedit.php'>
            <table>
                <tr title="New Domain">
                    <td>
                        <label>
                            <span style='float:left;'>
                                Domain Name: 
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
                <tr title="Add Domain">
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
    <div id="tabs-purge">
        <form method='post' action='adminconfedit.php'>
                <table>
<?php
foreach ($domains as $domain) {
?>
                    <tr title="<?php echo $domain ?>">
                        <td>
                            <label>
                                <span style="float:left;"><?php echo $domain ?></span>
                                <span style="float:right;">
                                <input type='checkbox' name='purgecache[]' value='<?php echo $domain ?>' />
                                </span>
                            </label>
                        </td>
                    </tr>
 <?php
}
?>
                    <tr title="Purge Cache">
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
        </form>
    </div>
    <div id="tabs-err">
        <form method="post" action="adminconfedit.php">
            <table>
                <tr title="Error Pages Select">
                    <td>
                        <span style="float:left;">Pretty Error Pages: </span>
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
</div>

<?php
echo "<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;<span"
    . " style='color:BurlyWood;font-size:12pt;font-weight:bold'>$error</span><br />";

tail();
?>
