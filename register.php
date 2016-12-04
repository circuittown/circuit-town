<?php 
if (isset($_POST["passagain"])) {
	/* do registration */
	$passagain = $_POST["passagain"];
	$mypass = $_POST["pass"];
	$myuser = $_POST["user"];
	if ($mypass != $passagain) {
	        $error = "passwords don't match.";
	}
	$myheight = $_POST["height"];
	$myweight = $_POST["weight"];
	$myweightkg = $_POST["weight_kg"];
	$myape = $_POST["ape"];
	include("common.php");
/*
describe user_mast;
+--------------+--------------+------+-----+---------+----------------+
| Field        | Type         | Null | Key | Default | Extra          |
+--------------+--------------+------+-----+---------+----------------+
| user         | varchar(255) | NO   |     |         |                |
| pass         | varchar(255) | NO   |     | NULL    |                |
| user_mast_id | int(11)      | NO   | PRI | NULL    | auto_increment |
| approved     | varchar(255) | NO   |     | no      |                |
| handle       | varchar(255) | NO   |     | NULL    |                |
+--------------+--------------+------+-----+---------+----------------+
5 rows in set (0.00 sec)
*/
	$q = "select user from user_mast where user = '$myuser'";
	$qparams = array(':myuser' => $myuser);
        try {
                $stmt = $db->prepare($q);
                $result = $stmt->execute($qparams);
        } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
        }
	$row = $stmt->fetch();
	if ($row['user']) {
	        $error = "this email is registered.";
        }
} else {
	header("location:index.php");
	exit;
}
include("common.php");
include("head.php"); 
?>
<script>
        function validateForm() {
                var x=document.forms["reg-final"]["handle"].value;
                if (x==null || x=="") {
                        alert("all registrants must have a handle.");
                        document.forms["reg-final"]["handle"].focus();
                        return false;
                }
	}
</script>
<div id="main">
<?php include("h1.php"); ?>
<?php include("find.php"); ?>
<div id="registered">
<?php
if (isset($error)) {
?>
<h3>oops something went wrong...</h3><br>
<?php
} else {
?>
<h3>thanks for registering for <?php print $sitename; ?></h3><br>
<?php } ?>
there is an approval process for all registrations. give us a couple days to approve your registration. if it takes more than 8 hours we're probably out circuiting. you should receive an email when your registration has been approved.
</div>

<div id="reg-results">
<?php
if (isset($error)) {
?>
        <?php print $error; ?>
        
        <button type="submit" id="mybutton" style="width:100px; margin-top:-5px;" onclick="window.location.href='login.php'">try again</button>
<?php
} else {
?>
<form method="post" action="register2.php" name="reg-final" onsubmit="return validateForm()">
	<div id="new_reg_form">
		<div id="email-l">
        	<strong>user email address</strong>
		</div>
		<div id="email-r">
		<?php print $myuser; ?>
		</div>
		<div id="handle-l">
		<strong><?php print $sitename; ?> handle</strong>
		</div>
		<input type="text" value="<?php print email2handle($myuser); ?>" name="handle" class="handle-r">
		<div class="brbr"></div>

		<div style="height:20px; margin-top:20px; margin-bottom:6px;" class="clearfix">
		<input type="hidden" name="myuser" value="<?php print $myuser; ?>">
		<input type="hidden" name="mypass" value="<?php print $mypass; ?>">
		<input type="hidden" name="passagain" value="<?php print $passagain; ?>">
		<input type="hidden" name="myheight" value="<?php print $myheight; ?>">
		<input type="hidden" name="myweight" value="<?php print $myweight; ?>">
		<input type="hidden" name="myweightkg" value="<?php print $myweightkg; ?>">
		<input type="hidden" name="myape" value="<?php print $myape; ?>">
		<button type="submit" id="mybutton" style="width:200px;">complete registration</button>
		</div>
	</div>
</form>
<?php } ?>
</div>
<?php include("foot.php"); ?>
</div>
</body>
</html>