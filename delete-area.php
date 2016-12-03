<?php
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
        if ($_SESSION['user_id'] == '1') {
                if (isset($_GET["area_id"])) {
			include("common.php");
                        $area_id = $_GET["area_id"];
			$select = "select subarea_id from subareas where area_id = :area_id";
			$selectparams = array(':area_id' => $area_id);
			try {
			        $stmt = $db->prepare($select);
			        $result = $stmt->execute($selectparams);
			} catch(PDOException $ex) {
			        die("Failed to run query: " . $ex->getMessage());
			}
			# loop thru all subareas for area
			while ($row = $stmt->fetch()) {
				$select = "select circuit_id from circuit where is_subarea = 'yes' and area_id = :area_id";
	                        $selectparams = array(':area_id' => $row['subarea_id']);
	                        try {
	                                $cdstmt = $db->prepare($select);
	                                $result = $cdstmt->execute($selectparams);
	                        } catch(PDOException $ex) {
	                                die("Failed to run query: " . $ex->getMessage());
	                        }
				# loop thru subarea circuits
	                        while ($cdrow = $cdstmt->fetch()) {
					$delete = "delete from allowed_users where circuit_id = :circuit_id";
                                        $deleteparams = array(':circuit_id' => $cdrow['circuit_id']);
                                        try {
                                                $dstmt = $db->prepare($delete);
                                                $result = $dstmt->execute($deleteparams);
                                        } catch(PDOException $ex) {
                                                die("Failed to run query: " . $ex->getMessage());
                                        }
                                        # delete from allowed users
					$delete = "delete from circuit_problems where circuit_id = :circuit_id";     
                                        $deleteparams = array(':circuit_id' => $cdrow['circuit_id']);
                                        try {                 
                                                $dstmt = $db->prepare($delete);
                                                $result = $dstmt->execute($deleteparams);
                                        } catch(PDOException $ex) {              
                                                die("Failed to run query: " . $ex->getMessage());
                                        }
					# delete all problems from circuit
				}
				$delete = "delete from circuit where is_subarea = 'yes' and area_id = :area_id";
				$deleteparams = array(':area_id' => $row['subarea_id']);
				try {
					$dstmt = $db->prepare($delete);
					$result = $dstmt->execute($deleteparams);
				} catch(PDOException $ex) {
					die("Failed to run query: " . $ex->getMessage());
				}
				# delete subarea circuits
			}
			$select = "select circuit_id from circuit where is_subarea = 'no' and area_id = :area_id";
			$selectparams = array(':area_id' => $area_id);
			try {
				$cdstmt = $db->prepare($select);
				$result = $cdstmt->execute($selectparams);
			} catch(PDOException $ex) {
				die("Failed to run query: " . $ex->getMessage());
			}
			# loop thru area circuits
			while ($cdrow = $cdstmt->fetch()) {
				$delete = "delete from circuit_problems where circuit_id = :circuit_id";
				$deleteparams = array(':circuit_id' => $cdrow['circuit_id']);
				try {
					$dstmt = $db->prepare($delete);
					$result = $dstmt->execute($deleteparams);
				} catch(PDOException $ex) {
					die("Failed to run query: " . $ex->getMessage());
				}
				# delete all problems from circuit
				$delete = "delete from allowed_users where circuit_id = :circuit_id";
                                $deleteparams = array(':circuit_id' => $cdrow['circuit_id']);
                                try {
                                        $dstmt = $db->prepare($delete);
                                        $result = $dstmt->execute($deleteparams);
                                } catch(PDOException $ex) {
                                        die("Failed to run query: " . $ex->getMessage());
                                }
                                # delete from allowed users
			}
			$delete = "delete from circuit where is_subarea = 'no' and area_id = :area_id";     
                        $deleteparams = array(':area_id' => $area_id);   
                        try {
                                $stmt = $db->prepare($delete);
                                $result = $stmt->execute($deleteparams);
                        } catch(PDOException $ex) {
                                die("Failed to run query: " . $ex->getMessage());
                        }
			# delete area circuits
                        $delete = "delete from areas where area_id = :area_id";
                        $delete2 = "delete from subareas where area_id = :area_id";
			$deleteparams = array(':area_id' => $area_id);
			try {
			        $stmt = $db->prepare($delete);
			        $result = $stmt->execute($deleteparams);
			} catch(PDOException $ex) {
			        die("Failed to run query: " . $ex->getMessage());
			}
			# delete areas
			try {
			        $stmt = $db->prepare($delete2);
			        $result = $stmt->execute($deleteparams); 
			} catch(PDOException $ex) {
			        die("Failed to run query: " . $ex->getMessage());
			}
			#delete subareas
                        header("location:area.php");
                        exit;
                } else if (isset($_GET["subarea_id"])) {
			include("common.php");
                        $subarea_id = $_GET["subarea_id"];
			$select = "select circuit_id from circuit where is_subarea = 'yes' and area_id = :area_id";
                        $selectparams = array(':area_id' => $subarea_id);
                        try {
                                $cdstmt = $db->prepare($select);
                                $result = $cdstmt->execute($selectparams);
                        } catch(PDOException $ex) {
                                die("Failed to run query: " . $ex->getMessage());
                        }
                        # loop thru area circuits for subarea
                        while ($cdrow = $cdstmt->fetch()) {
                                $delete = "delete from circuit_problems where circuit_id = :circuit_id";
                                $deleteparams = array(':circuit_id' => $cdrow['circuit_id']);
                                try {
                                        $dstmt = $db->prepare($delete);
                                        $result = $dstmt->execute($deleteparams);
                                } catch(PDOException $ex) {
                                        die("Failed to run query: " . $ex->getMessage());
                                }
                                # delete all problems from circuit
                        }
			$delete = "delete from circuit where is_subarea = 'yes' and area_id = :area_id";
                        $deleteparams = array(':area_id' => $subarea_id);
                        try {
                                $stmt = $db->prepare($delete);
                                $result = $stmt->execute($deleteparams);
                        } catch(PDOException $ex) {
                                die("Failed to run query: " . $ex->getMessage());
                        }
			# delete circuits for subarea
                        $delete = "delete from subareas where subarea_id = :subarea_id";
			$deleteparams = array(':subarea_id' => $subarea_id);  
                        try {
                                $stmt = $db->prepare($delete);
                                $result = $stmt->execute($deleteparams);
                        } catch(PDOException $ex) {
                                die("Failed to run query: " . $ex->getMessage());
                        }
			# delete subarea
                        header("location:area.php");
                        exit;
                }
        }
}
?>