<?php 
error_reporting(E_ALL & ~E_NOTICE);
ini_set( "display_errors", 1 );

if (!empty($_POST['pass'])) {
	if (!empty($_POST['passagain'])) {
		if ($_POST['pass'] == $_POST['passagain']) {
			include("common.php");
			$user_mast_id = $_POST['user_mast_id'];
			$q = "select user, pass, reset, handle from user_mast where user_mast_id = :user_mast_id";
			$qparams = array(':user_mast_id' => $user_mast_id);
	                try {
	                        $stmt = $db->prepare($q);
	                        $result = $stmt->execute($qparams);
	                } catch(PDOException $ex) {
	                        die("Failed to run query: " . $ex->getMessage());
	                }
	                $row = $stmt->fetch();
	                if ($row['reset'] == $_POST['randstr']) {
				$update = "update user_mast set reset = '', pass = :newpass where user_mast_id = :user_mast_id";
	                        $updateparams = array(':newpass' => $_POST['pass'], ':user_mast_id' => $user_mast_id);
				try {
	                                $stmt = $db->prepare($update);
	                                $result = $stmt->execute($updateparams);
	                        } catch(PDOException $ex) {
	                                die("Failed to run query: " . $ex->getMessage());
	                        }
				header("location:login.php");
				exit;
	                }			
		}
	}
}
if (!empty($_GET['user_id'])) {
	if (!empty($_GET['randstr'])) {
		include("common.php");
		$user_mast_id = $_GET['user_id'];
		$q = "select user, pass, reset, handle from user_mast where user_mast_id = :user_mast_id";
		$qparams = array(':user_mast_id' => $user_mast_id);
		try {
		        $stmt = $db->prepare($q);
		        $result = $stmt->execute($qparams);
		} catch(PDOException $ex) {
		        die("Failed to run query: " . $ex->getMessage());
		}
		$row = $stmt->fetch();
		if ($row['reset'] != $_GET['randstr']) {
			$error = "a pox on your family";
		} else {
			// bang
		}
	} else {
		$error = "a plague of mice";
	}
} else {
	$error = "a plague of locusts";
}

include("common.php");
include("head.php"); 
?>
<script src="js/jquery.pwstrength.js" type="text/javascript" charset="utf-8"></script>
<script>
	function validatelogin() {
		var x=document.forms["login"]["user"].value;
		var atpos=x.indexOf("@");
		var dotpos=x.lastIndexOf(".");
		if (atpos<1 || dotpos<atpos+2 || dotpos+2>=x.length) {
			alert("not a valid e-mail address");
			document.forms["login"]["user"].focus();
			return false;
		}
		var x=document.forms["login"]["pass"].value;
                if (x==null || x=="" || x=="password") {
                        alert("please enter a good password.");
                        document.forms["login"]["pass"].focus();         
                        return false;
                }
		var x=document.forms["login"]["passagain"].value;
		var y=document.forms["login"]["pass"].value;
                if (x!=y) {
                        alert("passwords do not match.");
                        document.forms["login"]["passagain"].focus();
                        return false;
                }
	}
$(function() {
    $("#txtConfirmPassword").keyup(function() {
        var password = $("#txtNewPassword").val();
        var oldpassword = $("#txtConfirmPassword").val();
        $("#divCheckPasswordMatch").html(password == $(this).val() ? "passwords match" : "passwords do not match!");
    });

});
jQuery(function($) { $('#txtNewPassword').pwstrength(); });
</script>
<div id="main">
<?php include("h1.php"); ?>
<?php include("find.php"); ?>
<div id="newshit">
<?php
if (isset($error)) {
	print $error;
} else {
?>
	<h4>reset password for <?php print strtolower($row['handle']); ?></h4>
	<div id="login">
	<form method="post" action="resetpass.php" name="login" onsubmit="return validatelogin()">
		<input type="password" name="pass" placeholder="new password" id="txtNewPassword" data-indicator="pwindicator"> &nbsp; <span id="pwindicator"><span class="label"></span></span><div class="brbr"></div>

		<input type="password" name="passagain" placeholder="new password again" id="txtConfirmPassword"> &nbsp;<span class="registrationFormAlert" id="divCheckPasswordMatch"></span><div class="brbr"></div>
		<input type="hidden" name="user_mast_id" value="<?php print $user_mast_id; ?>">
		<input type="hidden" name="randstr" value="<?php print $_GET['randstr']; ?>">
		<button type="submit" id="loginbutton" style="margin:12px;">reset password</button>
	</form>
	</div><br>
<?php
	
}
?>
</div>
<?php include("foot.php"); ?>
<script>

</script>
</body>
</html>