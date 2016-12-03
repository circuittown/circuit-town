<?php
if (!empty($_GET["user_id"])) {
	include("common.php");
	$q = "select user from user_mast where user_mast_id = :user_mast_id";
	$qparamas = array(':user_mast_id' => $_GET["user_id"]);
	try {
	        $stmt = $db->prepare($q);
	        $result = $stmt->execute($qparamas);
	} catch(PDOException $ex) {
	        die("Failed to run query: " . $ex->getMessage());
	}
	$row = $stmt->fetch();
	$email = $row["user"];
	function gen_rand_string($length = 36) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}
		return $randomString;
	}
	$randstr = gen_rand_string();
	$subject = "circuit town reset password";
	$headers = 'From: pander@circuit.town' . "\r\n" ;
	$headers .='Reply-To: '. $email . "\r\n" ;
	$headers .='X-Mailer: PHP/' . phpversion();
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";

	$message = "this is a request to reset your circuit.town password.<div style=\"margin-bottom:10px;\"></div>";
	$message .= "do not reply to this email.<div style=\"margin-bottom:10px;\"></div>";
	$message .= "if you did not request this reset, simply ignore this email.<br>";
	$message .= "otherwise click the link below to reset your password:<br>";
	$message .= "http://www.circuit.town/resetpass.php?randstr=" . $randstr . "&user_id=" . $_GET["user_id"];

	// sending
	mail($email, $subject, $message, $headers);

	$update = "update user_mast set reset = :reset where user_mast_id = :user_mast_id";
	$updateparams = array(':reset' => $randstr, ':user_mast_id' => $_GET["user_id"]);
	try {
                $stmt = $db->prepare($update);
                $result = $stmt->execute($updateparams);
        } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
        }
	$loginmsg = "we sent you an email.";
	$loginmsg = urlencode($loginmsg);
	header("location:login.php?error=" . $loginmsg);       
        exit;
} else {
	header("location:index.php");
	exit;
}
?>