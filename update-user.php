<?php 
include("common.php");
session_start();
if (isset($_SESSION["usermast"])) {
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
	if (!empty($_POST['user_mast_id'])) {
		$user_mast_id = $_POST['user_mast_id'];
		if ($_POST['gochangepass'] != "no") {
			if (!empty($_POST['pass'])) {
				if (!empty($_POST['oldpass'])) {
					$pq = "select pass from user_mast where user_mast_id = :user_mast_id";
					$pqparams = array(':user_mast_id' => $user_mast_id);
					try {
						$pstmt = $db->prepare($pq);
						$result = $pstmt->execute($pqparams);
					} catch(PDOException $ex) {
						die("Failed to run query: " . $ex->getMessage());
					}
					$prow = $pstmt->fetch();
					if ($prow['pass'] != $_POST['oldpass']) {
						$_SESSION["passchangeerr"] = "your old password was wrong.";
						header("location:index.php");
						exit;
					} else {
						$update = "update user_mast set pass = :pass where user_mast_id = :user_mast_id";
						$updateparams = array(':pass' => $_POST['pass'], ':user_mast_id' => $user_mast_id);
						try {
	                                                $ustmt = $db->prepare($update);
	                                                $result = $ustmt->execute($updateparams);
	                                        } catch(PDOException $ex) {
	                                                die("Failed to run query: " . $ex->getMessage());
	                                        }
						$_SESSION["passchangesuccess"] = "your password has been changed.";
					}
				}
			}
		}
		if (!empty($_POST['newheight'])) {
                        $newheight = $_POST['newheight'];
                } else {  
                        $newheight = "";
                }
                if (!empty($_POST['newweight'])) {
                        $newweight = $_POST['newweight'];
                } else {
                        $newweight = "";
                }
                if (!empty($_POST['newweight_kg'])) {   
                        $newweight_kg = $_POST['newweight_kg'];
                } else {
                        $newweight_kg = "";
                }
                if (!empty($_POST['newape'])) {
                        $newape = $_POST['newape'];
                } else {
                        $newape = "";
                }
                $update = "update user_mast set height = :height, weight = :weight, ape = :ape, weightkg = :weightkg where user_mast_id = :user_mast_id";
                $updateparams = array(':height' => $newheight, ':weight' => $newweight, ':ape' => $newape, ':weightkg' => $newweight_kg, ':user_mast_id' => $user_mast_id);
                try {
                        $hestmt = $db->prepare($update);
                        $result = $hestmt->execute($updateparams);
                } catch(PDOException $ex) {
                        die("Failed to run query: " . $ex->getMessage());
                }
		if (!empty($_POST['newhandle'])) {
			$newhandle = $_POST['newhandle'];
			$hq = "select handle from user_mast where handle = :handle";
			$hqparams = array(':handle' => $newhandle);
			try {
				$hstmt = $db->prepare($hq);
				$result = $hstmt->execute($hqparams);
			} catch(PDOException $ex) {
				die("Failed to run query: " . $ex->getMessage());
			}
			$hrow = $hstmt->fetch();
			if ($hrow) {
				if ($newhandle == $_SESSION['handle']) {
					header("location:index.php");
					exit;
				}
				$_SESSION["passchangeerr"] = "someone is currently using the handle: " . $newhandle;
				header("location:index.php");
				exit;
			} else {
				$update = "update user_mast set handle = :handle where user_mast_id = :user_mast_id";
				$updateparams = array(':handle' => $newhandle, ':user_mast_id' => $user_mast_id);
				try {
					$hstmt = $db->prepare($update);
					$result = $hstmt->execute($updateparams);
				} catch(PDOException $ex) {
					die("Failed to run query: " . $ex->getMessage());
				}
				$_SESSION['handle'] = $newhandle;
				$_SESSION["handlesuccess"] = "your handle has been changed.";
			}
		}
		header("location:index.php");
                exit;
	} else {
		header("location:index.php");
		exit;
	}
} else {
	header("location:login.php");
	exit;
}