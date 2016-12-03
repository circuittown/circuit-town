<?php
session_start();
if (isset($_SESSION["usermast"])) {
	include("common.php");
	$timeout = 60 * 30; // In seconds, i.e. 30 minutes.
        $fingerprint = md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
        if (    (isset($_SESSION['last_active']) && $_SESSION['last_active']<(time()-$timeout))
             || (isset($_SESSION['fingerprint']) && $_SESSION['fingerprint']!=$fingerprint)
             || isset($_GET['logout'])
            ) {
            setcookie(session_name(), '', time()-3600, '/');
            session_destroy();
        }
        session_regenerate_id();
        $_SESSION['last_active'] = time();
        $_SESSION['fingerprint'] = $fingerprint;
	if (isset($_GET["card_id"])) {
		$cq = "select user_mast_id from cards where card_id = :card_id";
                $cqparams = array(':card_id' => $_GET["card_id"]);
		try {
			$cstmt = $db->prepare($cq);
			$result = $cstmt->execute($cqparams);
		} catch(PDOException $ex) {
			die("Failed to run query: " . $ex->getMessage());
		}
                $crow = $cstmt->fetch();
		if ($crow['user_mast_id'] == $_SESSION['user_id'] || $_SESSION['user_id'] == '1') {
			$circuit_id = $_GET['circuit_id'];
			$delete = "delete from cards where card_id = :card_id";
			$deleteparams = array(':card_id' => $_GET["card_id"]);
			try {
                                $stmt = $db->prepare($delete);
                                $result = $stmt->execute($deleteparams);
                        } catch(PDOException $ex) {
                                die("Failed to run query: " . $ex->getMessage());
                        }
			header("location: circuit_detail.php?circuit_id=" . $circuit_id);
			exit;
		} else {
			//no permissions
			header("location: index.php");
			exit;
		}
	} else {
		//no GET
		header("location: index.php");
		exit;
	}
} else {
	// no session
	header("location:login.php");
	exit;
}
?>