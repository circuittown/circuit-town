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
require("common.php");
include("head.php"); 
?>
<div id="main">
<?php include("h1.php"); ?>
<?php include("find.php"); ?>
<div style="margin-top:20px;">
        <h3 style="display:inline;">hello andy</h3> &nbsp; [ <a href="user.php" class="usersubmit">users</a> ]
	<div class="brbr"></div>
        
        <table width="600" cellpadding="0" cellspacing="0" border="0">
<?php
$q = "select area, area_id, country_id, user_mast_id from areas where approved = 'yes' order by country_id, TRIM(LEADING 'the ' FROM LOWER(`area`))";
try {
        $stmt = $db->prepare($q);
        $result = $stmt->execute();
} catch(PDOException $ex) {
        die("Failed to run query: " . $ex->getMessage());
}
$x = 0;
while ($row = $stmt->fetch()) {
        if ($x % 2 > 0) {
                $style = " style=\"background-color:#3E372F; color:#ffffff;\"";
                $class = "";
        } else {
                $style = "";
                $class = " class=\"usersubmit\"";
        }
        $uq = "select handle from user_mast where user_mast_id = :user_mast_id";
	$uqparams = array(':user_mast_id' => $row['user_mast_id']);
	try {
		$ustmt = $db->prepare($uq);
		$result = $ustmt->execute($uqparams);
	} catch(PDOException $ex) {
		die("Failed to run query: " . $ex->getMessage());
	}
	$urow = $ustmt->fetch();
        $uhandle = $urow['handle'];
        $cq = "select country from country where country_id = :country_id";
        $cqparams = array(':country_id' => $row['country_id']);
        try {
                $cstmt = $db->prepare($cq);    
                $result = $cstmt->execute($cqparams);    
        } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
        }
        $crow = $cstmt->fetch();
        $country = $crow['country'];
?>
                <tr valign="top"<?php print $style; ?>>
                        <td style="padding:3px;">[ <a href="delete-area.php?area_id=<?php print $row['area_id']; ?>"<?php print $class; ?>>x</a> ]</td>
                        <td style="padding:3px; font-size:1.2em; font-weight:bold;"><?php print strtolower($row['area']); ?></td>
                        <td style="padding:3px;"><?php print $country; ?></td>
                        <td style="padding:3px;"><?php print $uhandle; ?></td>
                </tr>
<?php
        $saq = "select subarea, subarea_id, user_mast_id from subareas where area_id = :area_id order by subarea";
	$saqparams = array(':area_id' => $row['area_id']);
	try {
	        $sastmt = $db->prepare($saq);
	        $result = $sastmt->execute($saqparams);
	} catch(PDOException $ex) {
	        die("Failed to run query: " . $ex->getMessage());
	}
	while ($sarow = $sastmt->fetch()) {
		if ($x % 2 > 0) {
	                $style = " style=\"background-color:#3E372F; color:#ffffff;\"";
	                $class = "";
	        } else {
	                $style = "";
	                $class = " class=\"usersubmit\"";
	        }
		$suq = "select handle from user_mast where user_mast_id = :user_mast_id";
		$suqparams = array(':user_mast_id' => $sarow['user_mast_id']);    
		try {                                                      
			$sustmt = $db->prepare($suq);
			$result = $sustmt->execute($suqparams);
		} catch(PDOException $ex) {
			die("Failed to run query: " . $ex->getMessage());
		}    
		$surow = $sustmt->fetch();
	        $uhandle = $surow['handle'];
?>
                <tr valign="top"<?php print $style; ?>>
                        <td style="padding:3px; font-size:.7em;">[ <a href="delete-area.php?subarea_id=<?php print $sarow['subarea_id']; ?>"<?php print $class; ?>>x</a> ]</td>
                        <td style="padding:3px; font-size:.8em;">&nbsp; &nbsp; &nbsp;<?php print $sarow['subarea']; ?></td>
                        <td style="padding:3px; font-size:.8em;"><?php print $country; ?></td>
                        <td style="padding:3px; font-size:.8em;"><?php print $uhandle; ?></td>
                </tr>
<?php
        }
	$x++;
}
?>
        </table>
</div>
<?php include("foot.php"); ?>
</div>
</body>
</html>
<?php 
        } else {
                print "no access";
                exit;
        }
} else {
        print "404";
        exit;
}
?>