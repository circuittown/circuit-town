<?php 
include("common.php");
if (isset($_POST['handle'])) {
	$handle = $_POST['handle'];
	$myuser = $_POST['myuser'];
	$mypass = $_POST['mypass'];
	$myheight = $_POST['myheight'];
	$myweight = $_POST['myweight'];
	$myape = $_POST['myape'];
	$passagain = $_POST['passagain'];
	#check email against db and handle against db
/*
mysql> describe user_mast;
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
	$cq = "select user_mast_id, approved from user_mast where handle = :handle";
	$cqparams = array(':handle' => $handle);
	try {
		$cstmt = $db->prepare($cq);
		$result = $cstmt->execute($cqparams);
	} catch(PDOException $ex) {
		die("Failed to run query: " . $ex->getMessage());
	}
	$crow = $cstmt->fetch();
	#handle check!
	if ($crow['user_mast_id']) {
	        if ($crow['approved'] == 'yes') {
        		$handle_error = "<strong style=\"font-size:1.2em;\">sorry!</strong> the handle <strong>\"" . $handle . "\"</strong> is taken";
                }
	} else {
		#registration and email here
		$insert = "insert into user_mast (user, pass, handle, approved, height, weight, ape) values (:myuser, :mypass, :handle, 'no', :height, :weight, :ape)";
		$insertparams = array(':myuser' => $myuser, ':mypass' => $mypass, ':handle' => $handle, ':height' => $myheight, ':weight' => $myweight, ':ape' => $myape);
		try {
                        $istmt = $db->prepare($insert);
                        $result = $istmt->execute($insertparams);
                } catch(PDOException $ex) {
                        die("Failed to run query: " . $ex->getMessage());
                }
		#email
	        // variables
                $aeq = "select user from user_mast where user_mast_id = :user_mast_id";
                $aeqparams = array(':user_mast_id' => '1');
                try {
                        $aeqstmt = $db->prepare($aeq);
                        $result = $aeqstmt->execute($aeqparams);
                } catch(PDOException $ex) {
                        die("Failed to run query: " . $ex->getMessage());
                }
                $to = $aeqstmt->fetch();	        
	        $subject = "new user registration on the circuit site";
	        $headers = 'From: andy@circuit.town' . "\r\n" ;
	        $headers .='Reply-To: '. $to . "\r\n" ;
	        $headers .='X-Mailer: PHP/' . phpversion();
	        $headers .= "MIME-Version: 1.0\r\n";
	        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";

		$message = "you have a new user which requires your action\r\n";
		$message .= "user email: " . $myuser . "\r\n";
		$message .= "helpful link: http://www.circuit.town/login.php";
		$message = wordwrap($message, 70);

	        // sending
	        mail($to, $subject, $message, $headers);
	}
} else {
	header("location:index.php");
	exit;
}
include("head.php"); 
?>
<div id="main">
<?php include("h1.php"); ?>
<?php include("find.php"); ?>
	<div id="final-reg">
<?php
if (isset($handle_error)) {
?>
	<div style="width:360px;">
		<form method="post" action="register.php">
		<?php print $handle_error; ?>
		<input type="hidden" name="user" value="<?php print $myuser; ?>">
		<input type="hidden" name="pass" value="<?php print $mypass; ?>">
		<input type="hidden" name="passagain" value="<?php print $passagain; ?>">
		<input type="hidden" name="myheight" value="<?php print $myheight; ?>">
                <input type="hidden" name="myweight" value="<?php print $myweight; ?>">
                <input type="hidden" name="myape" value="<?php print $myape; ?>">
		<button type="submit" id="mybutton" style="width:100px; margin-top:-1px;">try again</button>
		</form>
	</div>
<?php
} else {
?>
	<h3>your registration is complete</h3><br>
	
	please check your email for approval confirmation.<div class="brbr"></div>

	<button type="submit" style="border:0px; width:100px; margin-top:-5px; background-color:#685D4F; -moz-border-radius: 10px; border-radius: 10px; padding:5px; width:120px; cursor: pointer; text-align:center; color:#ffffff; margin-top:6px;" onclick="window.location.href='index.php'">find circuits</button>
<?php } ?>
	</div>
<?php include("foot.php"); ?>
</div>
</body>
</html>