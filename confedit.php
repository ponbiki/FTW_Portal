<?php
require_once 'conninfo.php';
require_once 'header.php';

if (!$loggedin) {
    header("Location: index.php");
}

$page = "Configuration Edit";

unset($info);
$info = array();
unset($error);
$error = array();

$deldomain = $newhosts = $cookiename = $cookiepath = $cookiedomain = $cookieinfo = $purgecache = "";

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
        if (isset($_POST['deldomain'])) {        
            foreach ($_POST['deldomain'] as $deldomain_dirty) {
                $deldomains[] = filter_var($deldomain_dirty, FILTER_SANITIZE_STRING);
            }
        }
        if (empty($deldomains)) {
            $error[] = "No domain was selected for deletion.";
        } else {
            foreach ($deldomains as $deldomain) {
                if (!in_array($deldomain, $domains)) {
                    $error[] = "$deldomain is not an exisitng hostname";
                    break;
                }
            }
            $errors = array_filter($error);
            if (empty($error)) {
                $domains = array_diff(array_merge($domains, $deldomains),
                        array_intersect($domains, $deldomains));
                sort($domains);
                $ini_array['hostname'] = array_diff($ini_array['hostname'], $deldomains);
                if (count($ini_array['hostname']) < 1) {
                    $error[] = "You must have at least one active domain. "
                            . "If you require assistance, please contact support";
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
                            $info[] = "$deldomain has been deleted.";
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
                                stream_set_blocking($stream1, true);
                                $data = '';
                                while ($buf = fread($stream1,4096)) {
                                    $data .= $buf;
                                }
                                fclose($stream1); //repeat 2 more times
                            }
                        }
                    }
                }
            }
        }
    } elseif ($_POST['formid'] === 'addform') {
        $newhostclean = filter_input(INPUT_POST, 'newhost', FILTER_SANITIZE_STRING);
        $host_validate = '/([0-9a-z-]+\.)?[0-9a-z-]+\.[a-z]{2,7}/';
        $splitter = '/\s+/';
        if ($newhostclean == "") {
            $error[] = "No domain name was entered";
        } else {
            $newhosts = preg_split($splitter, $newhostclean);
            foreach ($newhosts as $newhost) {
                if (!preg_match($host_validate, $newhost)) {
                    $error[] = "$newhost is not a valid domain name";
                    break;
                } elseif (in_array($newhost, $domains)) {
                    $error[] = "$newhost is already being accelerated by FTW!";
                    break;
                }
            }
            $errors = array_filter($error);
            if (empty($errors)) {
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
                foreach ($newhosts as $newhost) {
                    array_push($ini_array['hostname'], $newhost);
                    array_push($domains, $newhost);
                }
                sort($domains);
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
                foreach ($newhosts as $newhost) {
                    $info[] = "$newhost has been added.";
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
                    $dir = "/home/ftwportal/conf";
                    $time = mktime();
                    $command1 = "touch $dir/stinkypinky"; /* temp placeholder */
                    if(!($stream1 = ssh2_exec($con, $command1))) {
                        die('Unable to execute command');
                    } else {
                        stream_set_blocking($stream1, true);
                        $data = '';
                        while ($buf = fread($stream1,4096)) {
                            $data .= $buf;
                        }
                        fclose($stream1); //repeat 2 more times
                    }
                }
            }
        }
    } elseif ($_POST['formid'] === 'exceptform') {
        $cookiename = filter_input(INPUT_POST, 'cookiename', FILTER_SANITIZE_STRING);
        $cookiepath = filter_input(INPUT_POST, 'cookiepath', FILTER_SANITIZE_STRING);
        $cookiedomain = filter_input(INPUT_POST, 'cookiedomain', FILTER_SANITIZE_STRING);
        $cookieinfo = filter_input(INPUT_POST, 'cookieinfo', FILTER_SANITIZE_STRING);
        if ($cookiename == "" || $cookiedomain == "") {
            $error[] = "At a minimum, rule name and cookie domain need to be enetered.";
        } else {
            $to = 'supportteam@nyi.net';
            $subject = "New caching exception request for {$_SESSION['user']}";
            $message = "Client: {$_SESSION['user']} \n\nName: $cookiename"
                    . " \n\nPath: $cookiepath \n\nDomain: $cookiedomain"
                    . " \n\nExtra Info: $cookieinfo \n\nYour friendly neighborhood FTWPortal\n";
            $from = "From: ftwportal@nyi.net\r";
            mail($to, $subject, $message, $from);
            $info[] = "Request for $cookiename sent!";
            unset($_POST);
        }
    } elseif ($_POST['formid'] === 'purgeform') {
        foreach ($_POST['purgecache'] as $purgecache_dirty) {
            $purgecachearr[] = filter_var($purgecache_dirty, FILTER_SANITIZE_STRING);
        }
        foreach ($purgecachearr as $purgecache) {
            if (!in_array($purgecache, $domains)) {
                $error[] = "$purgecache is not an existing hostname";
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
                    $info[] = "The cache for $purgecache is being cleared";
                }
                unset($_POST);
            }
        }
    } elseif ($_POST['formid'] === 'errform') {
        $info[] = "You matter to us!";
        unset($_POST);
    } elseif ($_POST['formid'] === 'sslform') {
        $info[] = "Secure is the way to go!";
        unset($_POST);
    }
}

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
                $( "#radio" ).buttonset();
            });
            $(function() {
                $( "input[type=checkbox], a, button" ).button();
            });
            $(function() {
                $( "#dialog" ).dialog({
                    autoOpen: false,
                    show: {
                        effect: "blind",
                        duration: 1000
                    },
                    hide: {
                        effect: "fold",
                        duration: 1000
                    }
                });
                $( "#opener" ).click(function() {
                $( "#dialog" ).dialog( "open" );
                });
            });
            $(function() {
                $( "#dialog2" ).dialog({
                    autoOpen: false,
                    show: {
                        effect: "blind",
                        duration: 1000
                    },
                    hide: {
                        effect: "fold",
                        duration: 1000
                    }
                });
                $( "#opener2" ).click(function() {
                    $( "#dialog2" ).dialog( "open" );
                });
            });;
            //$(window).load(function() {
            $(document).ready(function() {
                setTimeout(function() {
                    $( ".notify" ).fadeOut(400, function () {
                        //$( ".notify" ).remove();
                        $( ".notify" ).css({"visibility":"hidden",display:"block"}).slideUp();
                    });
                }, 3500);
            });
        </script>
'));

echo $navigation;

echo $logo;

bar($page);

if (!empty($info)) {
    info($info);
}

if (!empty($error)) {
    error($error);
}
?>

<div id="tabs">
    <ul>
        <li><a href="#tabs-dom" title="Manage Domains">Manage Domains</a></li>
        <li><a href="#tabs-purge" title="Clear Cache">Clear Cache</a></li>
        <li><a href="#tabs-err" title="Pretty Error Pages">Error Pages</a></li>
        <li><a href="#tabs-cookie" title="Cookie Exceptions">Cookie Exceptions</a></li>
    </ul>
    <div id="tabs-dom">
        <div id="accordion">
            <h3 title="Add Domains">Add Domains</h3>
            <div id="add">
                <form method='post' action='confedit.php'>
                    <table>
                        <tr title="New Domain">
                            <td style="float:left;padding-top:.75em;padding-right:.75em;">
                                Add Domain: 
                            </td>
                            <td style="float:right;">
                                <input type='text' maxlength='253' name='newhost' placeholder="Enter New Domain Name" />
                            </td>
                        </tr>
                        <tr title="Add Domain">
                            <td style="float:left;padding-top:.75em;">
                                <div id="dialog2" title="Tip">
                                    <p>Multiple whitespace separated domains may be added at the same time (e.g. spaces, tabs, newlines, etc.).</p>
                                </div>
                                <a id="opener2" class="ui-state-default ui-corner-all" title="Add Domain Help" style="width:19px;height:19px;padding:0px;margin:0px;font-size:1pt;">
                                    <span class="ui-icon ui-icon-help"></span>
                                </a>
                            </td>
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
                        <tr title='<?=$domain?>'>
                            <td style="float:left;">
                                <input type="checkbox" id="<?=$check?>" name="deldomain[]" value="<?=$domain?>"/>
                                <label for="<?=$check?>">
                                    <?=$domain?>
                                </label>
                            </td>
                        </tr>
 <?php
}
    ?>
                        <tr><td></td></tr>
                        <tr><td></td></tr>
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
            <h3 title="Add SSL Domains">Add SSL Domains</h3>
            <div id="ssladd">
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
                                <!--<label>-->
                                    <!---<span style="float:left;">-->
                                        <div id="dialog" title="Upload PEM">
                                            <p>Upload your PEM file here</p>
                                            <input type="file" name="pemul" />
                                        </div>
                                        <button type="button" id="opener">PEM Uploader</button>
                                    <!--</span>-->
                                <!--</label>-->
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
                <tr title="<?=$domain?>">
                    <td style="float:left;">
                        <input type="checkbox" id="<?=$check2?>" name="purgecache[]" value="<?=$domain?>" />
                        <label for="<?=$check2?>">
                            <?php echo $domain ?>
                        </label>
                    </td>
                </tr>
 <?php
}
?>
                <tr><td></td></tr>
                <tr><td></td></tr>
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
tail();
?>
