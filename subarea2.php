<?php 
error_reporting( E_ALL & ~E_NOTICE );
ini_set( "display_errors", 1 );

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
                $_SESSION["usa_form"] = serialize($_POST);
                $_SESSION["deliver_me"] = "subarea2.php";
        } else {
		if (!empty($_GET["areaname"])) {
			$area_id = $_GET["area_id"];
		} else if (!empty($_POST["areaname"])) {
			$area_id = $_POST["area_id"];
		} else {
			header("location:index.php");
			exit;
		}
                $_SESSION["deliver_me"] = "subarea.php?area_id=" . $area_id;
        }
        header("location:login.php");
        exit; 
} else {
	if (isset($_SESSION["usa_form"])) {
                $_POST = unserialize($_SESSION["usa_form"]);
		unset($_SESSION["usa_form"]);
        }
	if (isset($_POST["areaname"])) {
	        $subareaname = $_POST["areaname"];
	        $area_id = $_POST["area_id"];
	} else if (isset($_GET["areaname"])) {
	        $subareaname = urldecode($_GET["areaname"]);
	        $area_id = $_GET["area_id"];
	} else {
	        header("location:http://google.com");
	        exit;
	}
	// check for other subareas at this area
	include("common.php");
	$qq = "select subarea from subareas where area_id = :area_id and subarea = :subareaname";
	$qqparams = array(':area_id' => $area_id, ':subareaname' => $subareaname);
	try {
	        $qstmt = $db->prepare($qq);
	        $result = $qstmt->execute($qqparams);
	} catch(PDOException $ex) {
	        die("Failed to run query: " . $ex->getMessage());
	}
	$qrow = $qstmt->fetch();
	if ($qrow['subarea']) {
		$error = "this sub-area already exists.";
	} else {
		$inserter = $_SESSION['user_id'];
		$insert = "insert into subareas (subarea, area_id, user_mast_id) values (:subareaname, :area_id, :inserter)";
		$insertparams = array(':subareaname' => $subareaname, ':area_id' => $area_id, ':inserter' => $inserter);
		try {
	                $qstmt = $db->prepare($insert);
	                $result = $qstmt->execute($insertparams);
	        } catch(PDOException $ex) {
	                die("Failed to run query: " . $ex->getMessage());
	        }
	}
}
include("common.php");
include("head.php"); 
?>
<script>
	function validateForm() {
		var x=document.forms["sub2"]["areaname"].value;
		if (x==null || x=="") {
			alert("please enter a name of a sub-area.");
			document.forms["sub2"]["areaname"].focus();
			return false;
		}	
	}
</script>
<div id="main">
<?php include("h1.php"); ?>
<?php include("find.php"); ?>
	<div id="unknown">
<?php
if (isset($error)) {
?>
		<h3><?php print $error; ?></h3><br>
<?php 
} else {
?>
		<h3>area found</h3><br>
		
		<div style="width:500px;">thanks for adding a new sub-area.</div>
	</div>
	<div id="new-area-form">
<?php
	$q = "select area from areas where area_id = :area_id";
	$qparams = array(':area_id' => $area_id);
	try {
	        $stmt = $db->prepare($q);
	        $result = $stmt->execute($qparams);
	} catch(PDOException $ex) {
	        die("Failed to run query: " . $ex->getMessage());
	}
	$row = $stmt->fetch();
	$area = strtolower($row['area']);
	$area = stripslashes($area);
?>	
		<strong style="font-size:1.1em;"><?php print $area; ?></strong><div class="brbr"></div>
<?php
	$nq = "select subarea from subareas where area_id = :area_id order by subarea";
	$nqparams = array(':area_id' => $area_id);
	try {
	        $nstmt = $db->prepare($nq);
	        $result = $nstmt->execute($nqparams);
	} catch(PDOException $ex) {
	        die("Failed to run query: " . $ex->getMessage());
	}
	while ($nrow = $nstmt->fetch()) {
		$dbsan = $nrow['subarea'];
		if ($dbsan == $subareaname) {
			$dbsan = stripslashes($dbsan);
			$dbsan = strtolower($dbsan);
			$sas = "<strong style=\"font-size:1.2em;\">- " . $dbsan . "</strong><br>";
		} else {
			$dbsan = stripslashes($dbsan);
			$dbsan = strtolower($dbsan);
			$sas = "- " . $dbsan . "<br>";
		}
?>
		&nbsp; &nbsp; &nbsp; <?php print $sas; ?>
<?php
	}
}
?>
		<div style="width:240px; margin-left:28px; margin-top:20px;" id="loginbutton" onclick="location.href='subarea.php?area_id=<?php print $area_id; ?>';"><a href="subarea.php?area_id=<?php print $area_id; ?>" class="formsubmit">add another to <?php print $area; ?></a></div>
	</div>
<?php include("foot.php"); ?>
</div>
</body>
</html>