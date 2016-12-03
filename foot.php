<div id="footer">
<?php
if (isset($_SESSION['usermast'])) {
	if ($_SESSION['user_id'] == '1') {
?>
	<div id="loginbutton" onclick="location.href='logout.php';"><a href="logout.php" class="formsubmit">log out</a></div>
	<div id="adminbutton" onclick="location.href='user.php';"><a href="user.php" class="formsubmit">admin</a></div>
<?php
	} else {
		if (isset($user_bool)) {
?>
        <div id="loginbutton" onclick="location.href='logout.php';"><a href="logout.php" class="formsubmit">log out</a></div>
<?php
		}
	}
} else {
?>
	<div id="loginbutton" onclick="location.href='login.php';"><a href="login.php" class="formsubmit">login + register</a></div>
<?php
}
?>
</div>
