<?php 
error_reporting( E_ALL & ~E_NOTICE );
ini_set( "display_errors", 1 );

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
                        #update user and shit
			include("common.php");
                        $user_mast_id = $_GET["user_mast_id"];
                        $update = "update user_mast set approved = 'yes' where user_mast_id = $user_mast_id";
			$updateparams = array(':user_mast_id' => $user_mast_id);
			try {
			        $ustmt = $db->prepare($update);
			        $result = $ustmt->execute($updateparams);
			} catch(PDOException $ex) {
			        die("Failed to run query: " . $ex->getMessage());
			}
			// no approval email is being sent here. 
			// you will send these manually from andyklier@gmail.com
                        header("location:user.php");
                        exit;
                }
        }
}
?>