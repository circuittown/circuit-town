<?php
error_reporting( E_ALL & ~E_NOTICE );
ini_set( "display_errors", 1 );
include("common.php");
include("head.php");
?>
<div id="main">
<?php include("h1.php"); ?>
<?php include("find.php"); ?>
	<div id="area">
	showing circuits for:
<?php
if (isset($_GET["area_id"])) {
	$area_id = $_GET["area_id"];
	$q = "select area from areas where area_id = :area_id";
	$qparams = array(':area_id' => $area_id);
	try {
		$stmt = $db->prepare($q);
		$result = $stmt->execute($qparams);
	} catch(PDOException $ex) {
		die("Failed to run query: " . $ex->getMessage());
	}
	while ($row = $stmt->fetch()) {
		$circuitarea = strtolower($row['area']);
?>
		<h3 style="margin-top:10px;"><?php print $circuitarea; ?></h3>
<?php
	}
	$q = "select circuit_id, circuit, area_id, is_subarea, colour from circuit where area_id = :area_id and is_subarea = 'no'";
	$qparams = array(':area_id' => $area_id);
	$num_rows = 0;
        try {
                $stmt = $db->prepare($q);     
                $result = $stmt->execute($qparams);
		$num_rows = $stmt->rowCount();
        } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
        }
	$sq = "select subarea_id from subareas where area_id = :area_id";
	$sqparams = array(':area_id' => $area_id);
	try {
                $sstmt = $db->prepare($sq);
                $result = $sstmt->execute($sqparams);
        } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
        }
	while ($srow = $sstmt->fetch()) {
		$ssq = "select circuit from circuit where area_id = :subarea_id and is_subarea = 'yes'";
		$ssqparams = array(':subarea_id' => $srow['subarea_id']);
		try {
	                $ssstmt = $db->prepare($ssq);
	                $result = $ssstmt->execute($ssqparams);
			$ssnr = $ssstmt->rowCount();
	        } catch(PDOException $ex) {      
	                die("Failed to run query: " . $ex->getMessage());
	        }
		$num_rows = $num_rows + $ssnr;		
	}
	$c = 0;
	if ($num_rows > 1 || $num_rows == 0) {
		$plural = "s";
		$are = "are";
	} else {
		$plural = "";
		$are = "is";
	}
?>
		<div style="margin-bottom:5px; margin-top:5px;"><i>there <?php print $are; ?> <?php print $num_rows; ?> circuit<?php print $plural; ?> at <?php print $circuitarea; ?></i></div>
		<table style="margin-left:17px;">
<?php
        while ($row = $stmt->fetch()) {
		$npq = "select count(cp_id) as count from circuit_problems where circuit_id = :circuit_id";
	        $npqparams = array(':circuit_id' => $row['circuit_id']);
	        try {
	                $npstmt = $db->prepare($npq);
	                $result = $npstmt->execute($npqparams);
	        } catch(PDOException $ex) {
	                die("Failed to run query: " . $ex->getMessage());
	        }
	        $nprow = $npstmt->fetch();
	        $numprobs = $nprow['count'];
		$circname = $row['circuit'];
		if ($numprobs < 2) {
                        $npl = $numprobs . " problem";
                } else {
                        $npl = $numprobs . " problems";
                }
		$csq = "select css from colour where colour = :colour";
		$csqparams = array(':colour' => $row['colour']);
		try {
			$csstmt = $db->prepare($csq);
			$result = $csstmt->execute($csqparams);
		} catch(PDOException $ex) {
			die("Failed to run query: " . $ex->getMessage());
		}
		$csrow = $csstmt->fetch();
		$csscolour = $csrow['css'];
?>
			<tr valign="top">
				<td style="padding:3px;"><a href="circuit_detail.php?circuit_id=<?php print $row['circuit_id']; ?>" class="register"><?php print $circname; ?></a></td>
				<td style="padding:3px;"><div style="background-color:<?php print $csscolour; ?>;">&nbsp;&nbsp;&nbsp;</div></td>
				<td style="padding:3px;"><?php print $npl; ?></td>
			</tr>
<?php
		$c++;
	}
?>
		</table>
<?php
	$sq = "select subarea, subarea_id from subareas where area_id = :area_id";
	$sqparams = array(':area_id' => $area_id);
        try {
                $stmt = $db->prepare($sq);
                $result = $stmt->execute($sqparams);
        } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
        }
        while ($srow = $stmt->fetch()) { // while subarea row
		$saname = $srow['subarea'];
		$sa_id = $srow['subarea_id'];
		$saq = "select circuit_id, circuit, colour from circuit where area_id = :subarea_id and is_subarea = 'yes'";
		$saqparams = array(':subarea_id' => $sa_id);
		try {
			$saqstmt = $db->prepare($saq);
			$result = $saqstmt->execute($saqparams);
			$sanum_rows = $saqstmt->rowCount();
		} catch(PDOException $ex) {
			die("Failed to run query: " . $ex->getMessage());
		}
		$x = 0;
		$hasstuff = 0;
		while ($sarow = $saqstmt->fetch()) {
			$npq = "select count(cp_id) as count from circuit_problems where circuit_id = :circuit_id";
	                $npqparams = array(':circuit_id' => $sarow['circuit_id']);
	                try {
	                        $npstmt = $db->prepare($npq);
	                        $result = $npstmt->execute($npqparams);
	                } catch(PDOException $ex) {
	                        die("Failed to run query: " . $ex->getMessage());
	                }
	                $nprow = $npstmt->fetch();
	                $numprobs = $nprow['count'];
			if ($numprobs < 2) {
				$npl = $numprobs . " problem";
			} else {
				$npl = $numprobs . " problems";
			}
			$hasstuff = 1;
			if ($x == 0) {
				if ($sanum_rows > 1) {
	                                $plural = "s";
	                                $are = "are";
	                        } else {
	                                $plural = "";
	                                $are = "is";
	                        }
?>
		<div style="margin-top:7px; margin-left:20px;"><span style="font-size: 1.2em; color: #27221C; text-shadow: 1px 1px #443D33; font-weight:bold;"><?php print $srow['subarea']; ?></span> (<?php print $sanum_rows; ?> circuit<?php print $plural; ?>)</div>
		<table style="margin-left:36px;">
<?php
			}
			$csq = "select css from colour where colour = :colour";
	                $csqparams = array(':colour' => $sarow['colour']);
	                try {
	                        $csstmt = $db->prepare($csq);
	                        $result = $csstmt->execute($csqparams);
	                } catch(PDOException $ex) {
	                        die("Failed to run query: " . $ex->getMessage());
	                }
	                $csrow = $csstmt->fetch();
	                $csscolour = $csrow['css'];
?>
			<tr valign="top">
                                <td style="padding:3px;"><a href="circuit_detail.php?circuit_id=<?php print $sarow['circuit_id']; ?>" class="register"><?php print $sarow['circuit'];; ?></a></td>
                                <td style="padding:3px;"><div style="background-color:<?php print $csscolour; ?>;">&nbsp;&nbsp;&nbsp;</div></td>
                                <td style="padding:3px;"><?php print $npl; ?></td>
                        </tr>
<?php
			$c++;
			$x++;
		}
		if ($hasstuff == 1) {
?>
		</table>
<?php
		}
	}
	if ($c == 0) {
?>
		<div class="brbr"></div><i>there are no circuits yet for this area <a href="newcircuit.php?area_id=<?php print $area_id; ?>" class="register">maybe you should add one.</a></i>
<?php
	}
	include("colorlegend.php");
} else {
        print "no area";
} 
?>
	</div>
<?php include("foot.php"); ?>
</div>
</body>
</html>