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
	if (isset($_GET["circuit_id"])) {
		$cq = "select circuit_id, circuit, area_id, colour, is_subarea, user_mast_id from circuit where circuit_id = :circuit_id";
                $cqparams = array(':circuit_id' => $_GET["circuit_id"]);
		try {
			$cstmt = $db->prepare($cq);
			$result = $cstmt->execute($cqparams);
		} catch(PDOException $ex) {
			die("Failed to run query: " . $ex->getMessage());
		}
                $crow = $cstmt->fetch();
	}
        if ($_SESSION['user_id'] == $crow['user_mast_id'] || $_SESSION['user_id'] == '1') {
                if (isset($_GET["circuit_id"])) {
			if (isset($_GET["color"])) {
	                        $circuit_id = $_GET["circuit_id"];
				$update = "update circuit set colour = :colour where circuit_id = :circuit_id";
				$updateparams = array(':colour' => $_GET["color"], ':circuit_id' => $circuit_id);
				try {
	                                $stmt = $db->prepare($update);
	                                $result = $stmt->execute($updateparams);
	                        } catch(PDOException $ex) {
	                                die("Failed to run query: " . $ex->getMessage());
	                        }
				header("location:update-circuit.php?circuit_id=" . $circuit_id);
	                        exit;
			}
                }
        }
}
?>