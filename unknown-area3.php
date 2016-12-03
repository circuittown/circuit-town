<?php 
session_start();
if (!isset($_SESSION["usermast"])) {
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
	if (!empty($_POST)) {
                $_SESSION["ua_form"] = serialize($_POST);
                $_SESSION["deliver_me"] = "unknown-area3.php";
        } else {
                $_SESSION["deliver_me"] = "unknown-area.php";
        }
        header("location:login.php");
        exit;        
} else {
	include("common.php");
	if (isset($_SESSION["ua_form"])) {
		$_POST = unserialize($_SESSION["ua_form"]);
		unset($_SESSION["ua_form"]);
	}
	if (isset($_POST["areaname"])) {
	        $areaname = $_POST["areaname"];
	        $country_id = $_POST["country_id"];
	} else if (isset($_GET["areaname"])) {
	        $areaname = $_GET["areaname"];
	        $areaname = urldecode($areaname);
	        $country_id = $_GET["country_id"];
	} else { 
	        header("location:index.php");
	        exit;
	}
        $qq = "select area from areas where area = '$areaname'";
	$qqparams = array(':areaname' => $areaname);
	try {
                $qstmt = $db->prepare($qq);
                $result = $qstmt->execute($qqparams);
        } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
        }
        $qrow = $qstmt->fetch();
        if ($qrow['area']) {
                $error = "this area already exists.";
        } else {
                // insert sub-area
                $inserter = $_SESSION['user_id'];
                $insert = "insert into areas (area, country_id, user_mast_id, approved) values (:areaname, :country_id, :inserter, 'yes')";
		$insertparams = array(':areaname' => $areaname, ':country_id' => $country_id, ':inserter' => $inserter);
		try {
			$stmt = $db->prepare($insert);
			$result = $stmt->execute($insertparams);
                        $area_id = $db->lastInsertId();
		} catch(PDOException $ex) {
			die("Failed to run query: " . $ex->getMessage());
		}		
        }
}
include("common.php");
include("head.php"); 
?>
<div id="main">
<?php include("h1.php"); ?>
<?php include("find.php"); ?>
<div style="margin-top:20px;">
<?php
if (isset($error)) {
?>
        <h3><?php print $error; ?></h3>
<?php } else { ?>
        <h3>area found</h3><br>
        
        thanks for contributing to our database!<br>
	you may now begin adding circuits to <i><a href="newcircuit.php?area_id=<?php print $area_id; ?>" style="color:#2F2A24; text-decoration: underline;"><?php print $areaname; ?></a></i>.
<?php } ?>    
</div>
<?php include("foot.php"); ?>
</div>
</body>
</html>