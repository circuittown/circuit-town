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
                if (isset($_GET["user_mast_id"])) {
                        #delete user and shit
                        $user_mast_id = $_GET["user_mast_id"];
			include("common.php");
			$delete = "delete from allowed_users where user_mast_id = :user_mast_id";
                        $deleteparams = array(':user_mast_id' => $user_mast_id);
                        try {
                                $stmt = $db->prepare($delete);
                                $result = $stmt->execute($deleteparams);
                        } catch(PDOException $ex) {
                                die("Failed to run query: " . $ex->getMessage());
                        }
			$delete = "delete from problem_comments where user_mast_id = :user_mast_id";
                        $deleteparams = array(':user_mast_id' => $user_mast_id);
                        try {
                                $stmt = $db->prepare($delete);
                                $result = $stmt->execute($deleteparams);
                        } catch(PDOException $ex) {
                                die("Failed to run query: " . $ex->getMessage());
                        }
                        $delete = "delete from user_mast where user_mast_id = :user_mast_id";
			$deleteparams = array(':user_mast_id' => $user_mast_id);
			try {
                                $stmt = $db->prepare($delete);
                                $result = $stmt->execute($deleteparams);
                        } catch(PDOException $ex) {
                                die("Failed to run query: " . $ex->getMessage());
                        }
                        header("location:user.php");
                        exit;
                }
        }
}
?>