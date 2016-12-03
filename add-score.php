<?php 
include("common.php");
session_start();
if (!isset($_SESSION["usermast"])) {
	if (!empty($_GET['circuit_id'])) {
		$_SESSION['deliver_me'] = "add-score.php?circuit_id=" . $_GET['circuit_id'];
	}
        header("location:login.php");
        exit;
} else {
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
	if (!empty($_POST["circuit_id"])) {
		if (!empty($_POST["circuit_id"])) { $circuit_id = $_POST["circuit_id"]; } else { $error = "circuit_id"; }
		$user_mast_id = $_SESSION['user_id'];
		$right_now = date('Y-m-d H:i:s');
		if (!empty($_POST["comment"])) { $comment = $_POST["comment"]; } else { $comment = ""; }
		if (!empty($_POST["coursepar"])) { $coursepar = $_POST["coursepar"]; } else { $error = "coursepar"; }
		if (!empty($_POST["problem"])) { $problem = $_POST["problem"]; } else { $error = "problem"; }
		if (!empty($_POST["par"])) { $par = $_POST["par"]; } else { $error = "par"; } 
		if (!empty($_POST["ratedpar"])) { $ratedpar = $_POST["ratedpar"]; } else { $error = "ratedpar"; }
		if (isset($_POST["overunder_total_i"])) { $overunder_total = $_POST["overunder_total_i"]; } else { $error = "overunder_total_i"; }
		if (!isset($error)) {
			//here we go
			$numpars = count($par);
			$userpar = 0;
			$tratedpar = 0;
			foreach( $par as $key => $n ) {
				if ($n != 0) {
					$ppar[] = $n;
					$pproblem[] = $problem[$key];
					$pratedpar[] = $ratedpar[$key];
					$userpar = $userpar + $n;
					$tradedpar = $tradedpar + $ratedpar[$key];
				}
			}
			$card = serialize(array($pproblem, $pratedpar, $ppar));
			$insert = "insert into cards (circuit_id, user_mast_id, right_now, comment, card, coursepar, userpar, overunder) values (:circuit_id, :user_mast_id, :right_now, :comment, :card, :coursepar, :userpar, :overunder)";
			$insertparams = array(':circuit_id' => $circuit_id, ':user_mast_id' => $user_mast_id, ':right_now' => $right_now, ':comment' => $comment, ':card' => $card, ':coursepar' => $tradedpar, ':userpar' => $userpar, ':overunder' => $overunder_total);
			try {
                                $stmt = $db->prepare($insert);
                                $result = $stmt->execute($insertparams);
                        } catch(PDOException $ex) {
                                die("Failed to run query: " . $ex->getMessage());
                        }
			$insertid = $db->lastInsertId();
			$auq = "select allowed_users.user_mast_id, user_mast.handle from allowed_users inner join user_mast on allowed_users.user_mast_id=user_mast.user_mast_id where allowed_users.circuit_id = :circuit_id";
	                $auqparams = array(':circuit_id' => $circuit_id);
	                try {
	                        $austmt = $db->prepare($auq);
	                        $result = $austmt->execute($auqparams);
	                } catch(PDOException $ex) {
	                        die("Failed to run query: " . $ex->getMessage());
	                }
	                while ($aurow = $austmt->fetch()) {
	                        $authusers[] = $aurow['user_mast_id'];
	                        $authhandles[] = $aurow['handle'];
	                }
	                if (!in_array($user_mast_id, $authusers)) {
///////////////////
				$uq = "select user, handle from user_mast where user_mast_id = :user_mast_id";
				$uqparams = array(':user_mast_id' => $row['user_mast_id']);
				try {
	                                $ustmt = $db->prepare($uq);
	                                $result = $ustmt->execute($uqparams);
	                        } catch(PDOException $ex) {
	                                die("Failed to run query: " . $ex->getMessage());
	                        }
	                        $urow = $ustmt->fetch();
				$email = $urow['user'];
				$subject = "new scorecard added to your circuit";
				$headers = 'From: pander@circuit.town' . "\r\n" ;
				$headers .='Reply-To: '. $email . "\r\n" ;
				$headers .='X-Mailer: PHP/' . phpversion();
				$headers .= "MIME-Version: 1.0\r\n";
				$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";

				$message = "hello,<div style=\"margin-bottom:10px;\"></div>";
				$message .= "please do not reply to this email.<div style=\"margin-bottom:10px;\"></div>";
				$message .= "click the link below to view this scorecard:<br>";
				$message .= "http://www.circuit.town/circuit_detail.php?circuit_id=" . $circuit_id . "&card_id=" . $insertid;

				// sending
				#mail($email, $subject, $message, $headers);
			}
			header("location: circuit_detail.php?circuit_id=" . $circuit_id . "&card_id=" . $insertid);
			exit;
		} else {
			print $error; exit;
		}
	}
	include("head.php");
?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
<script>
$(document).ready(function() {
	$(document).on('submit','#newlist',function(){
		var bool = 1;
		$('.mypars').each(function() {
			if( $(this).val().length === 0 ) {
				bool = 2;
			}	
		});	
		if (bool != 1) {
			return false;
		} else {
			return true;
		}
	});
});
</script>
<div id="main">
<?php include("h1.php"); ?>
<?php include("find.php"); ?>
        <div id="newshit">
		<h3 id="mydear">adding a scorecard</h3>
<?php
	if(isset($_GET["circuit_id"])) {
		$circuit_id = $_GET["circuit_id"];
		$cq = "select circuit_id, circuit, area_id, colour, is_subarea, user_mast_id from circuit where circuit_id = :circuit_id";
		$cqparams = array(':circuit_id' => $circuit_id);
		try {
                        $cstmt = $db->prepare($cq);
                        $result = $cstmt->execute($cqparams);
                } catch(PDOException $ex) {
                        die("Failed to run query: " . $ex->getMessage());
                }
                $crow = $cstmt->fetch();
		$ownerid = $crow['user_mast_id'];
		$oq = "select handle from user_mast where user_mast_id = :owner";
		$oqparams = array(':owner' => $ownerid);
		try {
                        $ostmt = $db->prepare($oq);
                        $result = $ostmt->execute($oqparams);
                } catch(PDOException $ex) {
                        die("Failed to run query: " . $ex->getMessage());
                }
		$orow = $ostmt->fetch();
		$owner = $orow['handle'];
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
                        $subarea = strtolower($arow['subarea']);
                        $area_id = $arow['area_id'];
                        $aq = "select area from areas where area_id = :area_id";
                        $aqparams = array(':area_id' => $area_id);
                        try {
                                $aqstmt = $db->prepare($aq);
                                $result = $aqstmt->execute($aqparams);
                        } catch(PDOException $ex) {
                                die("Failed to run query: " . $ex->getMessage());
                        }
                        $arow = $aqstmt->fetch();
                        $area = strtolower($arow['area']) . ", " . $subarea;
                } else {
                        $area_id = $crow['area_id'];
                        $aq = "select area from areas where area_id = :area_id";
                        $aqparams = array(':area_id' => $area_id);
                        try {
                                $aqstmt = $db->prepare($aq);
                                $result = $aqstmt->execute($aqparams);
                        } catch(PDOException $ex) {
                                die("Failed to run query: " . $ex->getMessage());
                        }
                        $arow = $aqstmt->fetch();
                        $area = strtolower($arow['area']);
                }
		$colour = $crow['colour'];
			
?>
		<script>
			$('#mydear').append(': <?php print $crow['circuit']; ?>').show();
		</script>
		<strong>located at: <?php print $area; ?></strong><div class="brbr"></div>

		<div style="font-size:.8em; margin-bottom:10px; width:290px;">please enter a number of attempts for each problem on the circuit. you cannot submit an incomplete scorecard. 
		to option a prolbem out type "0", this only works if you've climbed problems marked "(optional)." <b>if you could not finish a problem, it should be marked 2 over par.</b></div>

	        <form method="post" action="add-score.php" id="newlist">
		<div style="width:auto; float:left;">
			<table>
                                <tr>
                                        <td style="padding-left:3px; padding-right:3px;">&nbsp;</td>
                                        <td style="padding-left:10px; padding-right:3px;"><h4>name</h4></td>
                                        <td style="padding-left:3px; padding-right:3px;"><h4>par</h4></td>
					<td style="padding-left:3px; padding-right:3px;"></td>
					<td style="padding-left:3px; padding-right:3px; width:20px;"></td>
                                </tr>
<?php
		$pq = "select cp_id, problem, par from circuit_problems where circuit_id = :circuit_id order by problem_order";
                $pqparams = array(':circuit_id' => $circuit_id);
                try {
                        $pstmt = $db->prepare($pq);
                        $result = $pstmt->execute($pqparams);
			$howmany = $pstmt->rowCount();
                } catch(PDOException $ex) {
                        die("Failed to run query: " . $ex->getMessage());
                }
                $y = 1;
		$totalpar = 0;
		$ocount = 0;
                while ($prow = $pstmt->fetch()) {
			if ($totalpar == 0) {
				$tpid = " id=\"firstpar\"";
			} else {
				$tpid = "";
			}
			$needle = "(optional)";
                        $isneedle = strstr($prow['problem'], $needle);
                        if (strlen($isneedle) > 6) {
				$ocount = $ocount + 1;
                                $zip = "o";
                        } else {
                                $zip = $y;
                        }
?>
				<tr style="background-color:#8d8373;">
                                        <td style="padding:3px; text-align:right;"><?php print $zip; ?>.</td>
                                        <td style="padding:3px; padding-left:10px; padding-right:10px;"><?php print $prow['problem']; ?><input id="bp_<?php print $prow['cp_id']; ?>" type="hidden" name="problem[]" value="<?php print $prow['problem']; ?>"></td>
                                        <td style="padding:3px; text-align:center;" id="ou_<?php print $prow['cp_id']; ?>"><?php print $prow['par']; ?><input type="hidden" name="ratedpar[]" value="<?php print $prow['par']; ?>"></td>
                                        <td style="padding:5px; text-align:center;"><input type="text" name="par[]" class="mypars poofpar_<?php print $prow['cp_id']; ?>" style="width:15px; padding:0; margin:0; height:12px; font-size:0.7em" data-id="<?php print $prow['cp_id']; ?>"<?php print $tpid; ?>></td>
					<td style="padding:3px; text-align:center;" id="overunder_<?php print $prow['cp_id']; ?>" class="overunder" data-id="<?php print $prow['cp_id']; ?>"></td>
                                </tr>
<?php
			if (strlen($isneedle) < 6) {
				$totalpar = $totalpar + $prow['par'];
				$y++;
			}
		}
?>
				<tr style="background-color:#8d8373;">
                                        <td style="padding:3px; text-align:right;">&nbsp;</td>
                                        <td style="padding:3px; padding-left:10px; padding-right:10px; text-align:right; font-weight:bold;">total score:</td>
                                        <td style="padding:3px; text-align:center; font-weight:bold;"><?php print $totalpar; ?></td>
                                        <td style="padding:3px; text-align:center; font-weight:bold;" id="totaladdup"></td>
					<td style="padding:3px; text-align:center; font-weight:bold;" id="overunder_total"></td>
                                </tr>
			</table>
			<input type="hidden" name="overunder_total_i" id="overunder_total_i" value="0">
			<div style="margin-top:155px; margin-bottom:-90px;"><textarea name="comment" placeholder="comment on this scorecard" style="font-size:.9em; width:263px; height:40px;"></textarea></div>
		</div>
		<div id="ncrightlist">
	                <div class="logincolorthing">
<?php
	        $coq = "select adjective, english from colour where colour = :colour";
	        $coqparams = array(':colour' => $colour);
	        try {
	        	$costmt = $db->prepare($coq);
	        	$result = $costmt->execute($coqparams);
	        } catch(PDOException $ex) {
	        	die("Failed to run query: " . $ex->getMessage());
	        }
	        $corow = $costmt->fetch();
?>
	                        <div style="height:25px;">
	                                <div id="colour" style="background-color:<?php print $colour; ?>;">
	                                <?php print strtolower($corow['adjective']); ?>
	                                </div>
	                                <div id="cadj">this circuit is <?php print lcfirst($colour); ?> for <?php print lcfirst($corow['english']); ?></div>
	                        </div>
	                </div>
			<div style="margin-top:10px; width:100%; text-align:right; margin-left:-10px;"><a href="circuit_detail.php?circuit_id=<?php print $circuit_id; ?>" class="register">view circuit</a></div>
		</div>
	        <input type="hidden" name="circuit_id" value="<?php print $circuit_id; ?>">		
		<input type="hidden" name="coursepar" value="<?php print $totalpar; ?>">
		<input type="hidden" name="howmanynoopt" value="<?php print $howmany-$ocount; ?>">
		<input type="hidden" name="howmany" value="<?php print $howmany; ?>">
        </div>
        <div class="clearfix"></div>
        <button type="submit" id="mybutton" style="width:auto; float:left; padding-right:5px; padding-left:5px; margin-top:20px; margin-bottom:20px;"> post scorecard </button>
	</form>
        <div class="clearfix"></div>
<?php 
	}
	include("foot.php"); ?>
</div>
<script>
$("#firstpar").focus();
var zero = 0;
var firedonce = 0;
var lastoptid = 0;
var ocount = <?php print $ocount; //?>;
$(".mypars").blur(function() {
	firedonce = firedonce+1;
	var total = 0;
	var outotal = 0;
	var thisval = parseInt($(this).val());
        if (isNaN(thisval) || thisval < 0) { // value is not a number
                var id = $(this).attr('data-id');
                var thispar = $("#ou_"+id).text();
                var nullpar = parseInt(thispar)+2;
                $(this).val(nullpar);
		var id = $(this).attr('data-id');
                var str = $("#bp_"+id).val();
                var n = str.indexOf("(optional)");
                if (n > 0) {
			zero = zero+1;
		}
        } else if (thisval == 0) { // value is 0
		var id = $(this).attr('data-id');
		var str = $("#bp_"+id).val();
		var n = str.indexOf("(optional)");
		if (n > 0) { // value is 0 but optional
			if (zero == 0) { // no more options to burn
				if (lastoptid != 0) {
					$(this).val(0);
					lastoptpar = $("#ou_"+lastoptid).text();
					$(".poofpar_"+lastoptid).val(lastoptpar);
				} else { // options to burn
					$(this).val(0);
				}
			} else {
				$(this).val(0);
			}
		} else { // value is 0 but NOT optional
			if (zero > 0) { // we got options to burn
				$(this).val(0);
				zero = zero-1;
				var id = $(this).attr('data-id');
				lastoptid = id;
			} else { // there are no options taken
				var thispar = $("#ou_"+id).text();
	                        thispar = parseInt(thispar);
				$(this).val(thispar);
			}
		}
	} else { // value is > 0
		var id = $(this).attr('data-id');
                var str = $("#bp_"+id).val();
                var n = str.indexOf("(optional)");
                if (n > 0) { // number > 0 but optional
			zero = zero+1;
		}
	}
	$('.mypars').each(function() {
		var thisval = parseInt($(this).val());
		if(!isNaN(thisval)) { // val is a number
			thisval = parseInt($(this).val());
			total = total + thisval;          
			var id = $(this).attr('data-id');
			var thispar = $("#ou_"+id).text();
			thispar = parseInt(thispar);
			if (thisval == 0) { // this val is 0
				var ou = thisval;
			} else { // this val is > 0
				var ou = thisval - thispar;
			}
			$("#overunder_"+id).text(ou);
		}
	});
	$('.overunder').each(function() {
		var thisval = parseInt($(this).text());
                if(!isNaN(thisval)) { // val is a nummber
			thisval = parseInt($(this).text());
			outotal = outotal + thisval;
			if (outotal > 0) {
				$("#overunder_total").text("+"+outotal);
			} else {
				$("#overunder_total").text(outotal);
			}
			$("#overunder_total_i").val(outotal);
		}
	});
	$("#totaladdup").text(total);
});
</script>
</body>
</html>
<?php
}
?>