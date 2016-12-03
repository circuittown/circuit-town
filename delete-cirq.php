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
		if ($crow['is_subarea'] == "yes") {
                        $subarea_id = $crow['area_id'];
                        $aq = "select subarea, area_id from subareas where subarea_id = :subarea_id";
                        $aqparams = array(':subarea_id' => $subarea_id);
                        try {
                                $aqstmt = $db->prepare($aq);
                                $result = $aqstmt->execute($aqparams);
                        } catch(PDOException $ex) {
                                die("Failed to run query: " . $ex->getMessage());
                        }
                        $arow = $aqstmt->fetch();
                        $area_id = $arow['area_id'];
                } else {
                        $area_id = $crow['area_id'];
                }
	}
        if ($_SESSION['user_id'] == $crow['user_mast_id'] || $_SESSION['user_id'] == '1') {
                if (isset($_GET["circuit_id"])) {
                        $circuit_id = $_GET["circuit_id"];
			$uq = "select user_mast_id from circuit where circuit_id = :circuit_id";
			$uqparams = array(':circuit_id' => $circuit_id);
			try {
                                $ustmt = $db->prepare($uq);
                                $result = $ustmt->execute($uqparams);
                        } catch(PDOException $ex) {
                                die("Failed to run query: " . $ex->getMessage());
                        }
			# get user_mast_id for circuit
			$urow = $ustmt->fetch();
			$cuid = $urow['user_mast_id'];
			$delete = "delete from circuit_problems where circuit_id = :circuit_id";
                        $deleteparams = array(':circuit_id' => $circuit_id);
                        try {
                                $stmt = $db->prepare($delete);
                                $result = $stmt->execute($deleteparams);
                        } catch(PDOException $ex) {
                                die("Failed to run query: " . $ex->getMessage());
                        }
			# delete circuit problems
			$delete = "delete from allowed_users where circuit_id = :circuit_id";
                        $deleteparams = array(':circuit_id' => $circuit_id);
                        try {
                                $stmt = $db->prepare($delete);
                                $result = $stmt->execute($deleteparams);
                        } catch(PDOException $ex) {
                                die("Failed to run query: " . $ex->getMessage());
                        }
                        # delete allowed users
                        $delete = "delete from circuit where circuit_id = :circuit_id";
			$deleteparams = array(':circuit_id' => $circuit_id);
			try {
                                $stmt = $db->prepare($delete);
                                $result = $stmt->execute($deleteparams);
                        } catch(PDOException $ex) {
                                die("Failed to run query: " . $ex->getMessage());
                        }
			# delete scorecards
			$delete = "delete from cards where circuit_id = :circuit_id";
                        $deleteparams = array(':circuit_id' => $circuit_id);
                        try {
                                $stmt = $db->prepare($delete);
                                $result = $stmt->execute($deleteparams);
                        } catch(PDOException $ex) {
                                die("Failed to run query: " . $ex->getMessage());
                        }
			# delete circuit
			if (isset($_GET["fromupdate"])) {
				header("location:circuit.php?area_id=" . $area_id);
				exit;
			}
			if (isset($_GET["fromindex"])) {
				header("location:index.php");
				exit;
			}
			header("location:user.php?user_mast_id=" . $cuid);
                        exit;
                }
        }
}
?>