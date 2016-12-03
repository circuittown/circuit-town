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
	if (isset($_POST["circuit_id"])) {
		$cq = "select circuit_id, circuit, area_id, colour, is_subarea, user_mast_id from circuit where circuit_id = :circuit_id";
                $cqparams = array(':circuit_id' => $_POST["circuit_id"]);
		try {
			$cstmt = $db->prepare($cq);
			$result = $cstmt->execute($cqparams);
		} catch(PDOException $ex) {
			die("Failed to run query: " . $ex->getMessage());
		}
                $crow = $cstmt->fetch();
	}
        if ($_SESSION['user_id'] == $crow['user_mast_id']) {
// this is the owner
                if (isset($_POST["circuit_id"])) {
                        $circuit_id = $_POST["circuit_id"];
			if (isset($_POST["user_mast_id"])) {
				$user_mast_id = $_POST["user_mast_id"];
				$cq = "select au_id from allowed_users where circuit_id = :circuit_id and user_mast_id = :user_mast_id";
				$cqparams = array(':circuit_id' => $circuit_id, ':user_mast_id' => $user_mast_id);
				try {
					$cstmt = $db->prepare($cq);
					$result = $cstmt->execute($cqparams);
				} catch(PDOException $ex) {
					die("Failed to run query: " . $ex->getMessage());
				}
				$crow = $cstmt->fetch();
				if (!$crow) {
					if ($user_mast_id != $_SESSION['user_id']) {
						$insert = "insert into allowed_users (circuit_id, user_mast_id) values (:circuit_id, :user_mast_id)";
						$insertparams = array(':circuit_id' => $circuit_id, ':user_mast_id' => $user_mast_id);
						try {
							$stmt = $db->prepare($insert);
							$result = $stmt->execute($insertparams);
						} catch(PDOException $ex) {
							die("Failed to run query: " . $ex->getMessage());
						}
					}
				}
			}
			header("location:update-circuit.php?circuit_id=" . $circuit_id);
			exit;
                }
        }
}
?>