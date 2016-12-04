<?php 
error_reporting(E_ALL & ~E_NOTICE);
ini_set( "display_errors", 1 );
include("common.php");
include("head.php"); 
?>
<script src="js/jquery.pwstrength.js" type="text/javascript" charset="utf-8"></script>
<script>
	function validateregister() {
        if($('#txtNewPassword:visible').length == 0) return true;
                var x=document.forms["register"]["passagain"].value;
                var y=document.forms["register"]["pass"].value;
                if (x!=y) {
                        alert("passwords do not match.");
                        document.forms["register"]["passagain"].focus();
                        return false;
                }
        }
$(document).ready(function() {
        $(".confirmdelete").click(function() {
                var c = confirm("are you sure you want to delete this circuit?");
                return c; //you can just return c because it will be true or false
        });
	$(function() {
		$("#txtConfirmPassword").keyup(function() {
			var password = $("#txtNewPassword").val();
			var oldpassword = $("#txtConfirmPassword").val();
			$("#divCheckPasswordMatch").html(password == $(this).val() ? "passwords match" : "passwords do not match!");
		});
	});
	jQuery(function($) { $('#txtNewPassword').pwstrength(); });
});
</script>
<div id="main">
<?php include("h1.php"); ?>
<?php include("find.php"); ?>
<div id="newshit">
<?php
session_start();
if (isset($_SESSION["usermast"])) {
?>
	<form name="register" method="post" action="update-user.php" id="TheSafeZone" onsubmit="return validateregister()">
	<input type="hidden" name="user_mast_id" value="<?php print $_SESSION["user_id"]; ?>">
	<input type="hidden" id="gochangepass" name="gochangepass" value="no">
	<div id="handleholder">logged in: <h3 id="showhandlebox" style="display:inline-block; cursor:pointer;"><?php print $_SESSION["handle"]; ?></h3><div id="newrig"><input name="newhandle" type="text" class="smallinput" value="<?php print $_SESSION["handle"]; ?>"></div></div>
	<?php print $_SESSION["username"]; ?><br>
	<div id="weight_height">
<?php
	$hwaq = "select height, weight, ape, weightkg from user_mast where user_mast_id = :user_mast_id";
        $hwaqparams = array(':user_mast_id' => $_SESSION['user_id']);
        try {
                $hwastmt = $db->prepare($hwaq);
                $result = $hwastmt->execute($hwaqparams);
        } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
        }
	$hwarow = $hwastmt->fetch();
?>
		<input class="smallinput" name="newheight" type="text" placeholder="your height" style="margin-left:0;" value="<?php print $hwarow['height']; ?>"><div class="minibr"></div>

		<input id="newweight_lb" class="smallinput" name="newweight" type="text" placeholder="weight (lb)" style="margin-left:0; width:47px;" value="<?php print $hwarow['weight']; ?>"><span style="margin-left:-16px; font-size:.7em; color:#666666;">lb</span> &nbsp;&nbsp;&nbsp;<input id="newweight_kg" class="smallinput" name="newweight_kg" type="text" placeholder="weight" style="margin-left:0; width:47px;" value="<?php print $hwarow['weightkg']; ?>"><span style="margin-left:-20px; font-size:.7em; color:#666666;">kg</span><div class="minibr"></div>

		<input class="smallinput" name="newape" type="text" placeholder="your 'ape index'" style="margin-left:0;" value="<?php print $hwarow['ape']; ?>"><div class="minibr"></div>
	</div>
	<div id="showpasschange" style="font-size:.8em;"><a href="javascript:void(0);" class="register" id="linkholder">change password</a></div>
	<div id="passwordholder">
		<input class="smallinput" type="password" name="oldpass" placeholder="old password" id="txtOldPassword">
		<div style="margin-bottom:5px;"></div>		
		<input class="smallinput" type="password" name="pass" placeholder="new password" id="txtNewPassword" data-indicator="pwindicator"> &nbsp; <span id="pwindicator"><span class="label"></span></span>
		<div style="margin-bottom:5px;"></div>
		<input class="smallinput" type="password" name="passagain" placeholder="new password again" id="txtConfirmPassword"> &nbsp;<span class="registrationFormAlert" id="divCheckPasswordMatch"></span>
	</div>
	<button type="submit" id="mybutton5"> change info </button>
	<div class="brbr clearfix"></div>
<?php
	if (isset($_SESSION["passchangeerr"])) {
?>
	<h4><?php print $_SESSION["passchangeerr"]; ?></h4>
	<div class="brbr"></div>
<?php
		unset($_SESSION["passchangeerr"]);
	}
	if (isset($_SESSION["passchangesuccess"])) {
?>
        <h4><?php print $_SESSION["passchangesuccess"]; ?></h4>
        <div class="brbr"></div>
<?php
                unset($_SESSION["passchangesuccess"]);
        }
	if (isset($_SESSION["handlesuccess"])) {
?>
        <h4><?php print $_SESSION["handlesuccess"]; ?></h4>
        <div class="brbr"></div>
<?php
                unset($_SESSION["handlesuccess"]);
        }
?>
	</form>
	<table>
<?php
        $cq = "select circuit_id, circuit, area_id, approved, is_subarea, colour, user_mast_id from circuit where user_mast_id = :user_mast_id order by circuit_id desc";
        $cqparams = array(':user_mast_id' => $_SESSION['user_id']);
        try {
                $cstmt = $db->prepare($cq);
                $result = $cstmt->execute($cqparams);
                $numrows = $cstmt->rowCount();
        } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
        }
	$usercirqlimit = 5;
        $t = 1;
        while ($crow = $cstmt->fetch()) {
		if ($t >= $usercirqlimit) {
			$hidden = " class=\"hidden\"";
		} else {
			$hidden = "";
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
		$csq = "select css from colour where colour = :colour";
		$csqparams = array(':colour' => $crow['colour']);
		try {
                        $csstmt = $db->prepare($csq);
                        $result = $csstmt->execute($csqparams);
                } catch(PDOException $ex) {
                        die("Failed to run query: " . $ex->getMessage());
                }
		$csrow = $csstmt->fetch();
		$csscolour = $csrow['css'];
?>
		<tr valign="top"<?php print $hidden; ?>>
			<td style="font-size:.8em; padding:3px;">[ <a href="delete-cirq.php?circuit_id=<?php print $crow['circuit_id']; ?>&fromindex=1" class="usersubmit confirmdelete">x</a> ]</td>
			<td style="font-size:.8em; padding:3px;"><a href="circuit_detail.php?circuit_id=<?php print $crow['circuit_id']; ?>" class="register"><?php print strtolower($crow['circuit']); ?></a></td>
			<td style="font-size:.8em; padding:3px;"><?php print strtolower($carea); ?></td>
			<td style="font-size:.8em; padding:3px;"><div style="background-color:<?php print $csscolour; ?>;">&nbsp;&nbsp;&nbsp;</div></td>
			<td style="font-size:.8em; padding:3px;"><?php print $npl; ?></td>
			<td style="font-size:.8em; padding:3px;"><a href="update-circuit.php?circuit_id=<?php print $crow['circuit_id']; ?>" class="register">edit</a></td>
			<td></td>
		</tr>
<?php
		$t++;
        }
	$eq = "select circuit_id from allowed_users where user_mast_id = :user_mast_id";
	$eqparams = array(':user_mast_id' => $_SESSION['user_id']);
	 try {
                $estmt = $db->prepare($eq);
                $result = $estmt->execute($eqparams);
                $enumrows = $estmt->rowCount();
        } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
        }
	$t = $t-1;
        while ($erow = $estmt->fetch()) {
		if ($t >= $usercirqlimit) {
                        $hidden = " class=\"hidden\"";
                } else {
                        $hidden = "";
                }
		$gq = "select circuit_id, circuit, area_id, approved, is_subarea, colour, user_mast_id from circuit where circuit_id = :circuit_id";
		$gqparams = array(':circuit_id' => $erow['circuit_id']);
		try {  
			$gstmt = $db->prepare($gq);
			$result = $gstmt->execute($gqparams);
			$gnumrows = $gstmt->rowCount();
		} catch(PDOException $ex) {
			die("Failed to run query: " . $ex->getMessage());
		}
		$grow = $gstmt->fetch();
		if ($grow['user_mast_id'] != $_SESSION['user_id']) {
			$oq = "select handle from user_mast where user_mast_id = :user_mast_id";
			$oqparams = array(':user_mast_id' => $grow['user_mast_id']);
			try {
	                        $ostmt = $db->prepare($oq);
	                        $result = $ostmt->execute($oqparams);
	                        $onumrows = $ostmt->rowCount();
	                } catch(PDOException $ex) {
	                        die("Failed to run query: " . $ex->getMessage());
	                }
	                $orow = $ostmt->fetch();
			$growhandle = $orow['handle'];
			if ($grow['is_subarea'] == "yes") {
	                        $aq = "select subareas.subarea, areas.area from subareas inner join areas on areas.area_id=subareas.area_id where subareas.subarea_id = :subarea_id";
	                        $aqparams = array(':subarea_id' => $grow['area_id']);
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
	                        $aqparams = array(':area_id' => $grow['area_id']);
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
	                $ccqparams = array(':circuit_id' => $grow['circuit_id']);
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
			$csq = "select css from colour where colour = :colour";
	                $csqparams = array(':colour' => $grow['colour']);
	                try {
	                        $csstmt = $db->prepare($csq);
	                        $result = $csstmt->execute($csqparams);
	                } catch(PDOException $ex) {
	                        die("Failed to run query: " . $ex->getMessage());
	                }
	                $csrow = $csstmt->fetch();
	                $csscolour = $csrow['css'];
// printing circuits user edits but does not own.
?>
		<tr valign="top" style="background-color:#8d8373;"<?php print $hidden; ?>>
			<td style="font-size:.8em; padding:3px;">&nbsp;</td>
                        <td style="font-size:.8em; padding:3px;"><?php print strtolower($grow['circuit']); ?></td>
                        <td style="font-size:.8em; padding:3px;"><?php print strtolower($carea); ?></td>
                        <td style="font-size:.8em; padding:3px;"><div style="background-color:<?php print $csscolour; ?>;">&nbsp;&nbsp;&nbsp;</div></td>
                        <td style="font-size:.8em; padding:3px;"><?php print $npl; ?></td>
                        <td style="font-size:.8em; padding:3px;"><a href="update-circuit.php?circuit_id=<?php print $erow['circuit_id']; ?>" class="register">edit</a></td>
                        <td style="font-size:.8em; padding:3px;"><?php print strtolower($growhandle); ?></td>
		</tr>
<?php
			$t++;
		}
	}
?>
	</table>
<?php
	if (strlen($hidden) > 6) {
?>
	<span class="showmore" style="font-size:.8em;"><a href="javascript:void(0);" id="smuc" class="qwregister">show more</a></span>
	<span class="showless"><a href="javascript:void(0);" id="sluc" class="qwregister">show less</a></span>
<?php
	}
}
?>
	<div class="brbr"></div>
	<h3>recent circuits</h3>
	<table>
<?php
$p = 0;
$rcq = "select circuit_id, circuit, area_id, approved, is_subarea, colour, user_mast_id from circuit order by circuit_id desc";
try {
        $rcstmt = $db->prepare($rcq);
        $result = $rcstmt->execute();
} catch(PDOException $ex) {
        die("Failed to run query: " . $ex->getMessage());
}
while ($rcrow = $rcstmt->fetch()) {
	if ($rcrow['is_subarea'] == "yes") {
		$aq = "select subareas.subarea, areas.area from subareas inner join areas on areas.area_id=subareas.area_id where subareas.subarea_id = :subarea_id";
		$aqparams = array(':subarea_id' => $rcrow['area_id']);
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
                $aqparams = array(':area_id' => $rcrow['area_id']);
                try {
                	$astmt = $db->prepare($aq);
                	$result = $astmt->execute($aqparams);
                } catch(PDOException $ex) {
                	die("Failed to run query: " . $ex->getMessage());
                }
                $arow = $astmt->fetch();
                $carea = $arow['area'];
	}
	$oq = "select handle from user_mast where user_mast_id = :user_mast_id";
	$oqparams = array(':user_mast_id' => $rcrow['user_mast_id']);
	try {
		$ostmt = $db->prepare($oq);
		$result = $ostmt->execute($oqparams);
		$onumrows = $ostmt->rowCount();
	} catch(PDOException $ex) {
		die("Failed to run query: " . $ex->getMessage());
	}
	$orow = $ostmt->fetch();
	$rcrowhandle = $orow['handle'];
        $ccq = "select problem from circuit_problems where circuit_id = :circuit_id";
        $ccqparams = array(':circuit_id' => $rcrow['circuit_id']);
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
	if ($p > 14) {
		$fdsa = " class=\"fdsahidden\"";
	} else {
		$fdsa = "";
	}
	$csq = "select css from colour where colour = :colour";
	$csqparams = array(':colour' => $rcrow['colour']);
	try {
		$csstmt = $db->prepare($csq);
		$result = $csstmt->execute($csqparams);
	} catch(PDOException $ex) {
		die("Failed to run query: " . $ex->getMessage());
	}
	$csrow = $csstmt->fetch();
	$csscolour = $csrow['css'];
?>
		<tr valign="top"<?php print $fdsa; ?>>
<?php
	if ($_SESSION["user_id"] == 1) {
?>
			<td style="font-size:.8em; padding:3px;">[ <a href="delete-cirq.php?circuit_id=<?php print $rcrow['circuit_id']; ?>&fromindex=1" class="usersubmit confirmdelete">x</a> ]</td>
<?php
	}
?>
                        <td style="font-size:.8em; padding:3px;"><a href="circuit_detail.php?circuit_id=<?php print $rcrow['circuit_id']; ?>" class="register"><?php print strtolower($rcrow['circuit']); ?></a></td>
                        <td style="font-size:.8em; padding:3px;"><?php print strtolower($carea); ?></td>
                        <td style="font-size:.8em; padding:3px;"><div style="background-color:<?php print $csscolour; ?>;">&nbsp;&nbsp;&nbsp;</div></td>
                        <td style="font-size:.8em; padding:3px;"><?php print $npl; ?></td>
                        <td style="font-size:.8em; padding:3px;"><i><?php print strtolower($rcrowhandle); ?></i></td>
			<td style="font-size:.8em; padding:3px;"><b>editors:</b><?php
	$eq = "select user_mast_id from allowed_users where circuit_id = :circuit_id order by au_id";
        $eqparams = array(':circuit_id' => $rcrow['circuit_id']);
         try {
                $estmt = $db->prepare($eq);
                $result = $estmt->execute($eqparams);
                $enumrows = $estmt->rowCount();
        } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
        }
	$k = 0;
        while ($erow = $estmt->fetch()) {
	        $gq = "select handle from user_mast where user_mast_id = :user_mast_id";
	        $gqparams = array(':user_mast_id' => $erow['user_mast_id']);
	        try {
	        	$gstmt = $db->prepare($gq);
	        	$result = $gstmt->execute($gqparams);
	        	$gnumrows = $gstmt->rowCount();
	        } catch(PDOException $ex) {
	        	die("Failed to run query: " . $ex->getMessage());
	        }
	        $grow = $gstmt->fetch();
		if ($k > 0) {
			print ", ";
		}
		print strtolower($grow['handle']);
		$k++;
	}
?></td>
                </tr>
<?php
	$p++;
}
?>
	</table>
<?php
if (strlen($fdsa) > 6) {
?>
        <span class="qwshowmore" style="font-size:.8em;"><a href="javascript:void(0);" id="qwsmuc" class="qwregister">show more</a></span>
        <span class="qwshowless"><a href="javascript:void(0);" id="qwsluc" class="qwregister">show less</a></span>
<?php
}
// recent scorecards
$scq = "select card_id, circuit_id, user_mast_id, right_now, comment, card, coursepar, userpar from cards order by card_id desc";
try {
        $scstmt = $db->prepare($scq);
        $result = $scstmt->execute();
	$cardcount = $scstmt->rowCount();
} catch(PDOException $ex) {
        die("Failed to run query: " . $ex->getMessage());
}
if ($cardcount > 0) {
?>
	<div class="brbr"></div>
        <h3>recent scorecards</h3>
	<table>
<?php
	$e = 0;
	while ($scrow = $scstmt->fetch()) {
		$gq = "select handle, height, weight, ape from user_mast where user_mast_id = :user_mast_id";
		$gqparams = array(':user_mast_id' => $scrow['user_mast_id']);
		try {
			$gstmt = $db->prepare($gq);
			$result = $gstmt->execute($gqparams);
			$gnumrows = $gstmt->rowCount();
		} catch(PDOException $ex) {
			die("Failed to run query: " . $ex->getMessage());
		}
		$grow = $gstmt->fetch();
		$cardhandle = $grow['handle'];
		$kjhg = $grow['weight'] . $grow['height'] . $grow['ape'];
		if (strlen($kjhg) > 3) {
			$stats = " (";
			if (!empty($grow['weight'])) {
				$stats .= $grow['weight'];
				$sw = 1;
			}
			if (!empty($grow['height'])) {
				$sh = 1;
				if ($sw == 1) {
					$stats .= ", ";
				}
                                $stats .= $grow['height'];
                        }
			if (!empty($grow['ape'])) {
                                if ($sh == 1) {
                                        $stats .= ", ";
                                }
                                $stats .= $grow['ape'];
                        }
			$stats .= ")";
		} else {
			$stats = "";
		}
		$time = strtotime($scrow['right_now']);
		$my_format = date("M j, Y", $time);
		$ou = $scrow['userpar'] - $scrow['coursepar'];
		if ($ou > 0) {
			$ou = "+" . $ou;
		}
		$cq = "select circuit, is_subarea, area_id, colour from circuit where circuit_id = :circuit_id";
		$cqparams = array(':circuit_id' => $scrow['circuit_id']);
		try {
                        $cqstmt = $db->prepare($cq);
                        $result = $cqstmt->execute($cqparams);
                        $cqnumrows = $gstmt->rowCount();
                } catch(PDOException $ex) {
                        die("Failed to run query: " . $ex->getMessage());
                }
		$cqrow = $cqstmt->fetch();
		if ($cqrow['is_subarea'] == "yes") {
	                $aq = "select subareas.subarea, areas.area from subareas inner join areas on areas.area_id=subareas.area_id where subareas.subarea_id = :subarea_id";
	                $aqparams = array(':subarea_id' => $cqrow['area_id']);
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
	                $aqparams = array(':area_id' => $cqrow['area_id']);
	                try {
	                        $astmt = $db->prepare($aq);
	                        $result = $astmt->execute($aqparams);
	                } catch(PDOException $ex) {
	                        die("Failed to run query: " . $ex->getMessage());
	                }
	                $arow = $astmt->fetch();
	                $carea = $arow['area'];
	        }
		$cirq = $cqrow['circuit'];
		if (strlen($cirq) > 18) {
			$cirq = substr($cirq, 0, 18) . "...";
		}
		if ($e > 14) {
	                $qwer = " class=\"qwerhidden\"";
	        } else {
	                $qwer = "";
	        }
		$csq = "select css from colour where colour = :colour";
	        $csqparams = array(':colour' => $cqrow['colour']);
	        try {
	                $csstmt = $db->prepare($csq);
	                $result = $csstmt->execute($csqparams);
	        } catch(PDOException $ex) {
	                die("Failed to run query: " . $ex->getMessage());
	        }
	        $csrow = $csstmt->fetch();
	        $csscolour = $csrow['css'];
?>
		<tr<?php print $qwer; ?>>
			<td style="padding:3px; font-size:.9em;"><?php print $my_format; ?></td>
			<td style="padding:3px; font-size:.9em;"><b><?php print $cardhandle; ?></b><?php print $stats; ?></td>
			<td style="padding:3px; font-size:.7em;"><i><?php print $carea; ?></i></td>
			<td style="padding:3px; font-size:.8em;"><div style="display:inline-block; width:8px; height:8px; background-color:<?php print $csscolour; ?>"></div> <b><?php print $cirq; ?></b></td>
			<td style="padding:3px; font-size:.9em; text-align:center;"><?php print $scrow['coursepar']; ?></td>
			<td style="padding:3px; font-size:.9em; text-align:center;"><b><?php print $scrow['userpar']; ?></b></td>
			<td style="padding:3px; font-size:.9em; text-align:center;"><b><?php print $ou; ?></b></td>
			<td style="padding:3px; font-size:.9em;"><a href="circuit_detail.php?circuit_id=<?php print $scrow['circuit_id']; ?>&card_id=<?php print $scrow['card_id']; ?>" class="showcard">see more</a></td>
		</tr>
<?php
		$e++;
	}
?>
	</table>
<?php
	if (strlen($qwer) > 6) {
?>
        <span class="ershowmore" style="font-size:.8em;"><a href="javascript:void(0);" id="ersmuc" class="qwregister">show more</a></span>
        <span class="ershowless"><a href="javascript:void(0);" id="ersluc" class="qwregister">show less</a></span>
<?php
	}
}
include("colorlegend.php");
?>
</div>
<?php include("foot.php"); ?>
<script>
	$("#smuc").click(function(){        
		$(".showmore").hide();
		$(".hidden").show();
		$(".showless").show();
        });
	$("#sluc").click(function(){
                $(".showmore").show();
                $(".hidden").hide();
		$(".showless").hide();
        });
	$("#qwsmuc").click(function(){
                $(".qwshowmore").hide();
                $(".fdsahidden").show();
                $(".qwshowless").show();
        });
        $("#qwsluc").click(function(){
                $(".qwshowmore").show();
                $(".fdsahidden").hide();
                $(".qwshowless").hide();
        });
	$("#ersmuc").click(function(){
                $(".ershowmore").hide();
                $(".qwerhidden").show();
                $(".ershowless").show();
        });
        $("#ersluc").click(function(){
                $(".ershowmore").show();
                $(".qwerhidden").hide();
                $(".ershowless").hide();
        });
	$("#showhandlebox").click(function() {
		$("#newrig").css("display", "inline-block");
		$("#weight_height").css("display", "inline");
		$("#showhandlebox").hide();
		$("#mybutton5").show();
	});
	$("#showpasschange").click(function() {
		if ($('#passwordholder').is(":hidden")) {
			$("#linkholder").text("don't change password");
			$("#gochangepass").val("yes");
			$("#passwordholder").show();		
			$("#mybutton5").show();
		} else {
			if ($('#newrig').is(":hidden")) {
				$("#mybutton5").hide();
			}
			$("#txtNewPassword").val("");
			$("#txtConfirmPassword").val("");
			$("#txtOldPassword").val("");
			$("#passwordholder").hide();
			$("#linkholder").text("change password");
			$("#gochangepass").val("no");
		}
        });
	$("#newweight_lb").keyup(function() {
	        var lbs = $("#newweight_lb").val();
	        var kgs = lbs / 2.20462262185;
	        kgs = kgs.toFixed(1);
	        $("#newweight_kg").val(kgs);
	});
	$("#newweight_kg").keyup(function() {
	        var kgs = $("#newweight_kg").val();
	        var lbs = kgs / 0.45359237;
	        lbs = lbs.toFixed(1);
	        $("#newweight_lb").val(lbs);
	});
</script>
</body>
</html>
