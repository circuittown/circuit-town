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
include("common.php");
include("head.php"); 
?>
<div id="main">
<?php include("h1.php"); ?>
<?php include("find.php"); ?>
<div style="margin-top:20px;">
	<h3 style="display:inline;">hello andy</h3> &nbsp; [ <a href="area.php" class="usersubmit">areas</a> ]
	<div class="brbr"></div>
        
        <table width="600" cellpadding="0" cellspacing="0" border="0">
<?php
$q = "select user, pass, user_mast_id, approved, handle from user_mast order by user_mast_id, approved";
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
        if ($row['approved'] == "no") {
                $cdata = "[ <a href=\"approve-user.php?user_mast_id=" . $row['user_mast_id'] . "\"" . $class . ">&#10003;</a> ]";
        } else {
                $cdata = "";
        }
	$cirqclass = "cirqs_" . $row['user_mast_id'];
	if (empty($class)) {
		$class = " class=\"showdetails\"";
	} else {
		$class = substr_replace($class, " showdetails\"", -1, 0);
	}
	$passss = md5($row['pass']);
	$passss = substr($passss, 0, 14);
?>
                <tr valign="top"<?php print $style; ?>>
                        <td style="padding:3px;">[ <a href="delete-user.php?user_mast_id=<?php print $row['user_mast_id']; ?>"<?php print $class; ?>>x</a> ]</td>
                        <td style="padding:3px;"><?php print stripslashes($row['handle']); ?></td>
                        <td style="padding:3px;"><?php print stripslashes($row['user']); ?></td>
                        <td style="padding:3px;"><?php print $passss; ?></td>
                        <td style="padding:3px;"><?php print $row['approved']; ?></td>
                        <td style="padding:3px; text-align:right;"><?php print $cdata; ?></td>
			<td style="padding:3px; text-align:right;">[ <a href="javascript:void(0);" data-id="<?php print $row['user_mast_id']; ?>"<?php print $class; ?>>C</a> ]</td>
                </tr>
<?php
	$cq = "select circuit_id, circuit, area_id, approved, is_subarea, colour, user_mast_id from circuit where user_mast_id = :user_mast_id";
	$cqparams = array(':user_mast_id' => $row['user_mast_id']);
	try {
	        $cstmt = $db->prepare($cq);
	        $result = $cstmt->execute($cqparams);
		$numrows = $cstmt->rowCount();
	} catch(PDOException $ex) {
	        die("Failed to run query: " . $ex->getMessage());
	}
	$t = 1;
	while ($crow = $cstmt->fetch()) {
		if ($t == 1) {
?>
		<tr valign="top"<?php print $style; ?> id="<?php print $cirqclass; ?>" class="hidden">
			<td colspan="7">
			<table cellpadding="0" cellspacing="0" border="0">
<?php
		}
		if ($crow['is_subarea'] == "yes") {
			$aq = "select subareas.subarea, areas.area from subareas inner join areas on areas.area_id=subareas.area_id where subareas.subarea_id = :subarea_id";
			$aqparams = array(':subarea_id' => $crow['area_id']);
			try {
	                        $astmt = $db->prepare($aq);
	                        $result = $astmt->execute($aqparams);
	                } catch(PDOException $ex) {
	                        die("Failed to run query: " . $ex->getMessage());
	                }
	                $arow = $astmt->fetch(); 
			$carea = $arow['area'] . ", " . $arow['subarea'];
		} else {
			$aq = "select area from areas where area_id = :area_id";
			$aqparams = array(':area_id' => $crow['area_id']); 
			try {
                                $astmt = $db->prepare($aq);
                                $result = $astmt->execute($aqparams);
                        } catch(PDOException $ex) {
                                die("Failed to run query: " . $ex->getMessage());
                        }
                        $arow = $astmt->fetch();
			$carea = $arow['area'];
		}
		$ccq = "select problem from circuit_problems where circuit_id = :circuit_id";
		$ccqparams = array(':circuit_id' => $crow['circuit_id']);
		try {
	                $ccstmt = $db->prepare($ccq);
	                $result = $ccstmt->execute($ccqparams);
	                $numprobs = $ccstmt->rowCount();
	        } catch(PDOException $ex) {
	                die("Failed to run query: " . $ex->getMessage());
	        }
		if ($numprobs < 2) {
			$npl = $numprobs . " problem";
		} else {
			$npl = $numprobs . " problems";
		}
?>
				<tr valign="top">
					<td style="font-size:.8em; padding:3px;">[ <a href="delete-cirq.php?circuit_id=<?php print $crow['circuit_id']; ?>"<?php print $class; ?>>x</a> ]</td>
					<td style="font-size:.8em; padding:3px;"><?php print $crow['circuit']; ?></td>
					<td style="font-size:.8em; padding:3px;"><?php print $carea; ?></td>
					<td style="font-size:.8em; padding:3px;"><div style="background-color:<?php print $crow['colour']; ?>;">&nbsp;&nbsp;&nbsp;</div></td>
					<td style="font-size:.8em; padding:3px;"><?php print $npl; ?></td>
				</tr>
<?php
		if ($t == $numrows) {
?>
			</table>
			</td>
		</tr>
<?php
		}
		$t++;
	}
        $x++;
}
?>
        </table>
</div>
<?php include("foot.php"); ?>
</div>
<script>
	$(".showdetails").click(function(){        
		$(".hidden").hide();
		var id = $(this).attr('data-id');
		$("#cirqs_"+id).show();
        });
</script>
<?php
		if (isset($_GET['user_mast_id'])) {
?>
<script>
	$("#cirqs_<?php print $_GET['user_mast_id']; ?>").show();
</script>
<?php
		}
?>
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