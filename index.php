<?php
require_once('header.php');
htmlHeader("FTW Portal");
?>
<div class="row">
<div class="span12"><img src="img/ftw_logo.png" name="FTW Portal logo" title="FTW Portal logo" /></div>
</div>
<div class="row">
    <div class="span4"></div>
<div class="span4">
<form method="post" action="" class="table-hover">
    <h1><img src="img/NYI.png" class="loginlogo" alt="NYI" title="NYI" />
        Control Panel Log In
        <span><?php echo $error; ?></span>
    </h1>
    <label>
        <span>Username :</span>
        <input id='user' type='text' maxlength='24' name='user' value="" />
    </label>
    <label>
        <span>Password :</span>
        <input id='pass' type='password' maxlength='24' name='pass' value="" />
    </label>
    <label>
        <span>&nbsp;</span>
        <input type='submit' class='button' value='Login' />
    </label>
</form>
</div>
    <div class="span4"></div>
</div>
<?php
tail();
?>