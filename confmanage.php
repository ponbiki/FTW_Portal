<?php
require_once 'header.php';
require_once 'conninfo.php';

if (!$loggedin) {
    header("Location: index.php");
}
if (!$admin) {
    header("Location: index.php");
}

$page = "Config File Management";

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

?>

<div id="tabs">
    <ul>
        <li><a href="#tabs-new" title="New Config File">New Config</a></li>
        <li><a href="#tabs-mng" title="Manage Config File">Manage Config</a></li>
    </ul>
    <div id="tabs-new">
        <form method="post" action="confmanage.php">
            
        </form>
    </div>
</div>

<?php
tail();
?>