<?php 
error_reporting( E_ALL & ~E_NOTICE );
ini_set( "display_errors", 1 );

include("common.php");
if (!empty($_GET['error'])) {
	$error = $_GET['error'];
}
if (isset($_POST["user"])) {
        $useruser = strtolower($_POST["user"]);
        $passpass = $_POST["pass"];
        session_start();

  $query = "select user, pass, user_mast_id, approved, handle from user_mast";
  try {
        $stmt = $db->prepare($query);
        $result = $stmt->execute();
  } catch(PDOException $ex) {
        die("Failed to run query: " . $ex->getMessage());
  }
  $b = 0;
  while ($row = $stmt->fetch()) {
    $dbuser = strtolower($row['user']);
    $dbuser = trim($dbuser);
    $dbpass = $row['pass'];
    if ($dbuser == $useruser) {
      if ($dbpass == $passpass) {
// LOGGED IN
        #baduser
        if ($row['approved'] == 'no') {
                $error = "this user account has not been approved by management.";
        } else {
                $fingerprint = md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
                $_SESSION['last_active'] = time();
                $_SESSION['fingerprint'] = $fingerprint;
                $username = $row['user'];
                $user_id = $row['user_mast_id'];
                $_SESSION['logged_in_oh_yeah'] = "perhaps";
                $_SESSION['usermast'] = $username;
		$_SESSION['handle'] = $row['handle'];
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $dbuser;
		$_SESSION['approved'] = $row['approved'];
		if (isset($_SESSION['deliver_me'])) {
			$deliverthine = $_SESSION['deliver_me'];
			unset($_SESSION['deliver_me']);
			header("location:" . $deliverthine);
			exit;
		} else {
	                header("location: index.php");
		}
                exit;
        }
      } else {
	$user_mast_id = $row["user_mast_id"];
	$error = "username and password do not match.";
      }
      $b++;
    }
  }
  if ($b == 0) {
    $error = "no user found with that email.";
  }
}
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
                        alert("please enter a password.");
                        document.forms["login"]["pass"].focus();         
                        return false;
                }
	}
        function validateregister() {
                var x=document.forms["register"]["user"].value;  
                var atpos=x.indexOf("@");
                var dotpos=x.lastIndexOf(".");
                if (atpos<1 || dotpos<atpos+2 || dotpos+2>=x.length) {
                        alert("not a valid e-mail address");
                        document.forms["register"]["user"].focus();      
                        return false;
                }
                var x=document.forms["register"]["pass"].value;    
                if (x==null || x=="" || x=="password") {
                        alert("please enter a password.");             
                        document.forms["register"]["pass"].focus();               
                        return false; 
                }
                var x=document.forms["register"]["passagain"].value;
		var y=document.forms["register"]["pass"].value;
                if (x!=y) {
                        alert("passwords do not match.");
                        document.forms["register"]["passagain"].focus();
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
	<div id="login">
	<h2><?php print $sitename; ?> user log in</h2><br>
<?php if (isset($error)) { ?>
        <?php print $error; ?><div class="brbr"></div>
<?php 
} 
if ($error == "username and password do not match.") {
?>
	<a href="forgotpass.php?user_id=<?php print $user_mast_id; ?>">reset your password</a><div class="brbr"></div>
<?php } ?>
	<form method="post" action="login.php" name="login" onsubmit="return validatelogin()">
		<input name="user" type="text" placeholder="email"><div class="brbr"></div>

		<input type="password" name="pass" placeholder="password"><div class="brbr"></div>

		<button type="submit" id="loginbutton" style="margin:12px;">log in</button>
	</form>
	</div><br>

	<strong>don't have an account yet?</strong><div class="brbr"></div>

	<form method="post" action="register.php" name="register" onsubmit="return validateregister()">
	<div style="float:left;">
		<h2>register</h2><br>
			<input name="user" type="text" placeholder="your email"><div class="brbr"></div>
	
			<input type="password" name="pass" placeholder="password" id="txtNewPassword" data-indicator="pwindicator"> &nbsp; <span id="pwindicator"><span class="label"></span></span><div class="brbr"></div>
	
			<input type="password" name="passagain" placeholder="password again" id="txtConfirmPassword"> &nbsp;<span class="registrationFormAlert" id="divCheckPasswordMatch"></span><div class="brbr"></div>
	
			<button type="submit" id="loginbutton" style="margin:12px;">register</button>
	</div>
	<div style="float:left; position:relative; background-color:#3F3830; padding:20px; color:#ffffff; -moz-border-radius: 10px; border-radius: 10px; margin-left:40px;">
		best estimates, please. <i>this is useful info:</i><br>
		<input id="height_ft" name="height" type="text" placeholder="your height" style="margin-left:0"><div class="brbr"></div>

		<input id="weight_lb" name="weight" type="text" placeholder="your weight (lb)" style="margin-left:0; width:33%;"> &nbsp;  <input id="weight_kg" name="weight_kg" type="text" placeholder="your weight (kg)" style="margin-left:0; width:33%;"><div class="brbr"></div>

		<input name="ape" type="text" placeholder="your 'ape index' if you don't know google it" style="margin-left:0;"><div class="brbr"></div>
	<div>
	</form>
	<div class="clearfix"></div>
</div>
<script>
$("#weight_lb").keyup(function() {
        var lbs = $("#weight_lb").val();
        var kgs = lbs / 2.20462262185;
	kgs = kgs.toFixed(1);
        $("#weight_kg").val(kgs);
});
$("#weight_kg").keyup(function() {
        var kgs = $("#weight_kg").val();
        var lbs = kgs / 0.45359237;
        lbs = lbs.toFixed(1);
        $("#weight_lb").val(lbs); 
});
</script>
</body>
</html>