<?php
error_reporting( E_ALL & ~E_NOTICE );
ini_set( "display_errors", 1 );

include("common.php");
include("head.php");
?>
<script>
$(document).ready(function() {
	$(".takeoffhoser").click(function(){
                var c = confirm("are you sure you want to delete this comment?");
                return c; //you can just return c because it will be true or false
        });
        $(".ctakeoffhoser").click(function(){
                var d = confirm("are you sure you want to delete this comment?");
                return d; //you can just return c because it will be true or false
        });
        $(".takeoffcard").click(function(){
                var e = confirm("are you sure you want to delete this scorecard?");
                return e; //you can just return c because it will be true or false
        });
});
</script>
<div id="main">
<?php include("h1.php"); ?>
<?php include("find.php"); ?>
	<div id="cirq">
<?php
if (isset($_GET["circuit_id"])) {
	$circuit_id = $_GET["circuit_id"];
	$q = "select circuit, area_id, is_subarea, colour, user_mast_id from circuit where circuit_id = :circuit_id";
	$qparams = array(':circuit_id' => $circuit_id);
        try {
                $stmt = $db->prepare($q);
                $result = $stmt->execute($qparams);
        } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
        }
        while ($row = $stmt->fetch()) {
		$circuit = strtolower($row['circuit']);
		if ($row['is_subarea'] == "yes") {
		        $subarea_id = $row['area_id'];
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
			$area = strtolower($arow['area']);
		} else {
		        $area_id = $row['area_id'];
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
		if (isset($subarea)) {
			$mystr = " at " . "<a href=\"circuit.php?area_id=" . $area_id . "\" class=\"register\">" . $area . "</a>, " . $subarea;
		} else {
			$mystr = " at " . "<a href=\"circuit.php?area_id=" . $area_id . "\" class=\"register\">" . $area . "</a>";
		}
		$ccq = "select cc_id, comment, user_mast_id from cirq_comments where circuit_id = :circuit_id";
                $ccqparams = array(':circuit_id' => $circuit_id);
                try {
			$ccstmt = $db->prepare($ccq);
			$result = $ccstmt->execute($ccqparams);
			$cccount = $ccstmt->rowCount();
		} catch(PDOException $ex) {
			die("Failed to run query: " . $ex->getMessage());
		}
		$ccc = $cccount;
		if ($ccc > 0) {
			$ccal = "<a href=\"javascript:void(0);\" class=\"showccomments\">";;
			$ccar = "</a>";
			$ccplus = " <span class=\"pcc\">(" . $ccc . ")</span>";
		} else {
			$ccal = "";
                        $ccar = "";
                        $ccplus = "";
		}
?>
		<h3 style="display:inline-block; margin-top:10px;"><?php print $ccal . $circuit . $ccar; ?></h3><?php print $ccplus; ?><br>
<?php
		if ($ccc > 0) {
			$ccq = "select cirq_comments.user_mast_id, cirq_comments.right_now, user_mast.handle, cirq_comments.comment, cirq_comments.cc_id from cirq_comments inner join user_mast on cirq_comments.user_mast_id=user_mast.user_mast_id where cirq_comments.circuit_id = :circuit_id";
			$ccqparams = array(':circuit_id' => $circuit_id);
			try {
				$ccstmt = $db->prepare($ccq);
				$result = $ccstmt->execute($ccqparams);
			} catch(PDOException $ex) {
				die("Failed to run query: " . $ex->getMessage());
			}
			$f = 0;
			while ($ccrow = $ccstmt->fetch()) {
				$time = strtotime($ccrow['right_now']);
				$cmy_format = date("M j, Y", $time);
				if ($ccrow['user_mast_id'] == $_SESSION['user_id'] || $_SESSION['user_id'] == '1') {
					$ccomm = "<span class=\"removefromlist\"> [ <a href=\"delete-cc.php?cc_id=" . $ccrow['cc_id'] . "\" class=\"ctakeoffhoser\">x</a> ] </span> ";
				} else {
					$ccomm = "";
				}
?>
		<div class="dicks">
			<div><span class="problemcomment"><?php print $cmy_format; ?> - <b><?php print $ccrow['handle']; ?></b></span></div>
			<div><span class="problemcomment"><?php print $ccomm; print nl2br($ccrow['comment']); ?></span></div>
			<div style="margin-bottom:5px;"></div>
		</div>
<?php
			}
		}
?>
		<div style="margin-bottom:16px;"><?php print $mystr; ?></div>
		<div style="margin-left:30px; float:left;">
		<table>
			<tr>
				<td style="padding-left:3px; padding-right:3px;">&nbsp;</td>
				<td style="padding-left:3px; padding-right:3px;"><h4>name</h4></td>
				<td style="padding-left:3px; padding-right:3px;"><h4>par</h4></td>
			</tr>
<?php
		$pq = "select cp_id, problem, par from circuit_problems where circuit_id = :circuit_id order by problem_order";
		$pqparams = array(':circuit_id' => $circuit_id);
	        try {
	                $pstmt = $db->prepare($pq);
	                $result = $pstmt->execute($pqparams);
	        } catch(PDOException $ex) {
	                die("Failed to run query: " . $ex->getMessage());
	        }
		$y = 0;
		$addparopt = 0;
		$addpar = 0;
	        while ($prow = $pstmt->fetch()) {
			$pcq = "select count(pc_id) as count from problem_comments where problem = :problem and circuit_id = :circuit_id";
			$pcqparams = array(':problem' => $prow['problem'], ':circuit_id' => $circuit_id);
			try {
				$pcstmt = $db->prepare($pcq);
				$result = $pcstmt->execute($pcqparams);
			} catch(PDOException $ex) {
				die("Failed to run query: " . $ex->getMessage());
			}
			$pcrow = $pcstmt->fetch();
			$pcc = $pcrow['count'];
			if ($pcc > 0) {
				$pcchtml = " <span class=\"pcc\">(" . $pcc . ")</span>";
				$pccal = "<a href=\"javascript:void(0);\" data-id=\"" . $prow['cp_id'] . "\" class=\"showpcomments\">";
				$pccar = "</a>";
			} else {
				$pcchtml = "";
				$pccal = "";
				$pccar = "";
			}
			$needle = "(optional)";
			$isneedle = strstr($prow['problem'], $needle);
                        if (strlen($isneedle) > 6) {
				$zip = "o";
                        } else {
				$z = $y + 1;
				$zip = $z;
				$addpar = $addpar + $prow['par'];
			}
			$addparopt = $addparopt + $prow['par'];
?>
			<tr>
                                <td style="padding-left:3px; padding-right:3px; text-align:right;"><?php print $zip; ?>.</td>
				<td style="padding-left:3px; padding-right:3px;"><?php print $pccal; print $prow['problem']; print $pccar; print $pcchtml; ?></td>
				<td style="padding-left:3px; padding-right:3px; text-align:center;"><?php print $prow['par']; ?></td>
			</tr>
<?php
			if ($pcc > 0) {
				$pcq = "select problem_comments.user_mast_id, problem_comments.right_now, user_mast.handle, problem_comments.comment, problem_comments.pc_id from problem_comments inner join user_mast on problem_comments.user_mast_id=user_mast.user_mast_id where problem_comments.problem = :problem and problem_comments.circuit_id = :circuit_id";
                                $pcqparams = array(':problem' => $prow['problem'], ':circuit_id' => $circuit_id);
                                try {
                                        $pcstmt = $db->prepare($pcq);
                                        $result = $pcstmt->execute($pcqparams);
                                } catch(PDOException $ex) {
                                        die("Failed to run query: " . $ex->getMessage());
                                }
				$s = 0;
                                while ($pcrow = $pcstmt->fetch()) {
					if ($s == 0) {
?>
			<tr class="titty" id="titty_<?php print $prow['cp_id']; ?>">
				<td colspan="3" style="padding-left:13px;">
<?php
					}
					$time = strtotime($pcrow['right_now']);
					$my_format = date("M j, Y", $time);
					if ($pcrow['user_mast_id'] == $_SESSION['user_id'] || $_SESSION['user_id'] == '1') {
						$dcomm = "<span class=\"removefromlist\"> [ <a href=\"delete-pc.php?pc_id=" . $pcrow['pc_id'] . "\" class=\"takeoffhoser\">x</a> ] </span> ";
					} else {
						$dcomm = "";
					}
?>
				<div class="boobies">
					<div><span class="problemcomment"><?php print $my_format; ?> - <b><?php print $pcrow['handle']; ?></b></span></div>
					<div><span class="problemcomment"><?php print $dcomm; print nl2br($pcrow['comment']); ?></span></div>
					<div style="margin-bottom:5px;"></div>
				</div>
<?php
					$s++;
				}
?>
				</td>
			</tr>
<?php
			}
			if (strlen($isneedle) < 6) {
				$y++;
			}
		} // end while problem row
		if ($addpar != $addparopt) {
			$addedpar = "total par with options: " . $addparopt . "<br>total par without options: " . $addpar;
		} else {
			$addedpar = "total par: " . $addpar;
		}
?>
			<tr>
				<td colspan="3" style="font-weight:bold; font-size:.9em; text-align:right; padding-right:12px;"><?php print $addedpar; ?></td>
			</tr>
		</table>
		</div>
		<div class="clearfix"></div>
	</div>
        <div id="rightshit">
                <div class="logincolorthing">
<?php
		$coq = "select adjective, english, css from colour where colour = :colour";
		$coqparams = array(':colour' => $row['colour']);
		try {
                        $costmt = $db->prepare($coq);
                        $result = $costmt->execute($coqparams);
                } catch(PDOException $ex) {
                        die("Failed to run query: " . $ex->getMessage());
                }
		$corow = $costmt->fetch();
		$uq = "select handle, weight, height, ape from user_mast where user_mast_id = :user_mast_id";
		$uqparams = array(':user_mast_id' => $row['user_mast_id']);
		try {
                        $ustmt = $db->prepare($uq);
                        $result = $ustmt->execute($uqparams);
                } catch(PDOException $ex) {
                        die("Failed to run query: " . $ex->getMessage());
                }
                $urow = $ustmt->fetch();
		if ($row['colour'] == "black") {
			$xtrastyle = " color:#9C9281;";
		} elseif ($row['colour'] == "blue") {
			$xtrastyle = " color:#000000;";
		} else {
			 $xtrastyle = "";
		}
		if (empty($urow['height'])) {
			$urow['height'] = "<i>no info</i>";
		}
		if (empty($urow['weight'])) {
                        $urow['weight'] = "<i>no info</i>";
                } else {
			if (stripos($urow['weight'], "lb") < 1) {
				$urow['weight'] = $urow['weight'] . "lbs";
			}
		}
		if (empty($urow['ape'])) {
                        $urow['ape'] = "<i>no info</i>";
                }
?>
			<div style="height:25px;">
				<div id="colour" style="background-color:<?php print $corow['css']; ?>;<?php print $xtrastyle; ?>">
				<?php print strtolower($corow['adjective']); ?>
	                        </div>
				<div id="cadj">this circuit is <?php print lcfirst($row['colour']); ?> for <?php print lcfirst($corow['english']); ?></div>
			</div>
			<div class="clearfix" style="width:100%; text-align:right;">
				posted by: <a href="javascript:void(0);" class="showustats"><strong><?php print $urow['handle']; ?></strong></a>
				<div id="user_stats">height: <?php print $urow['height']; ?><br>
					weight: <?php print $urow['weight']; ?><br>
					ape: <?php print $urow['ape']; ?><br>
				</div>
			</div>
                </div>
<?php
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
		if (isset($_SESSION["usermast"])) {
?>
		<div style="margin-top:10px; width:100%; text-align:right; margin-left:-10px;"><a href="add-score.php?circuit_id=<?php print $circuit_id; ?>" class="register">add scorecard</a></div>
<?php
			if (in_array($_SESSION['user_id'], $authusers)) {
?>
		<div style="margin-top:1px; width:100%; text-align:right; margin-left:-10px;"><a href="update-circuit.php?circuit_id=<?php print $circuit_id; ?>" class="register">edit circuit</a></div>
<?php
			}
		}
?>
        </div>
	<div class="clearfix"></div>
	<div style="width:60%; margin-top:20px;">
<?php
		$cardq = "select card_id, circuit_id, user_mast_id, right_now, comment, card, coursepar, userpar from cards where circuit_id = :circuit_id order by overunder, coursepar desc, userpar";
		$cardqparams = array(':circuit_id' => $circuit_id);
		try {
                        $cardstmt = $db->prepare($cardq);
                        $result = $cardstmt->execute($cardqparams);
			$numcard = $cardstmt->rowCount();
                } catch(PDOException $ex) {
                        die("Failed to run query: " . $ex->getMessage());
                }
		if ($numcard > 0) {
			if ($numcard == 1) {
				$kjhg = "there is 1 scorecard for " . $circuit;
			} else {
				$kjhg = "there are " . $numcard . " scorecards for " . $circuit;
			}
?>
		<h5><?php print $kjhg; ?>:</h5>
		<table>
			<tr>
                                <td style="padding-left:3px; padding-right:3px; font-size:.9em;"><b>name:</b></td>
                                <td style="padding-left:3px; padding-right:3px; font-size:.9em;"><b>posted:</b></td> 
                                <td style="padding-left:3px; padding-right:3px; font-size:.9em;"><b>circuit</b></td>  
                                <td style="padding-left:3px; padding-right:3px; font-size:.9em;"><b>score</b></td>
				<td style="padding-left:3px; padding-right:3px; font-size:.9em;"><b>o/u</b></td>
				<td style="padding-left:3px; padding-right:3px; font-size:.9em;">&nbsp;</td>
				<td style="padding-left:3px; padding-right:3px; font-size:.9em;">&nbsp;</td>
                        </tr>
<?php
			$g = 0;
	                while ($cardrow = $cardstmt->fetch()) {
				if ($g % 2 == 0) {
					$altcstyle = " style=\"background-color:#8d8373;\"";
				} else {
					$altcstyle = "";
				}
				$gq = "select handle from user_mast where user_mast_id = :user_mast_id";
				$gqparams = array(':user_mast_id' => $cardrow['user_mast_id']);
				try {
					$gstmt = $db->prepare($gq);
					$result = $gstmt->execute($gqparams);
					$gnumrows = $gstmt->rowCount();
				} catch(PDOException $ex) {
					die("Failed to run query: " . $ex->getMessage());
				}
				$grow = $gstmt->fetch();
				$cardhandle = $grow['handle'];
				$time = strtotime($cardrow['right_now']);
				$my_format = date("M j, Y", $time);
				$ou = $cardrow['userpar'] - $cardrow['coursepar'];
				if ($ou > 0) {
					$ou = "+" . $ou;
				}
				if ($_SESSION['user_id'] == $cardrow['user_mast_id'] || $_SESSION['user_id'] == '1') {	
					$deletecard = "<span class=\"removefromlist\"> [ <a href=\"delete-card.php?circuit_id=" . $circuit_id . "&card_id=" . $cardrow['card_id'] . "\" class=\"takeoffcard\">x</a> ] </span>";
				} else {
					$deletecard = "";
				}
?>
			<tr<?php print $altcstyle; ?>>
				<td style="padding:3px; font-size:.9em;"><?php print $cardhandle; ?></td>
				<td style="padding:3px; font-size:.9em;"><?php print $my_format; ?></td>
				<td style="padding:3px; font-size:.9em; text-align:center;"><?php print $cardrow['coursepar']; ?></td>
				<td style="padding:3px; font-size:.9em; text-align:center;"><?php print $cardrow['userpar']; ?></td>
				<td style="padding:3px; font-size:.9em; text-align:center;"><?php print $ou; ?></td>
				<td style="padding:3px; font-size:.9em;"><a href="javascript:void(0);" class="showcard" data-id="<?php print $cardrow['card_id']; ?>" id="smb_<?php print $cardrow['card_id']; ?>">see more</a></td>
				<td style="padding:3px; font-size:.9em; text-align:center;"><?php print $deletecard; ?></td>
			</tr>
			<tr class="morecard" id="card_id_<?php print $cardrow['card_id']; ?>">
				<td style="font-size:.9em;" colspan="7">
				<table style="width:100%; padding:0; margin:0;">
<?php
				list($problem, $ratedpar, $par) = unserialize($cardrow['card']);
				$key = 0;
				foreach ($problem as $value) {
					$thisou = $par[$key] - $ratedpar[$key];
					if ($thisou > 0) {
						$thisou = "+" . $thisou;
					}
?>
					<tr<?php print $altcstyle; ?>>
						<td style="padding:3px; text-align:right;"><?php print $key+1; ?>.</td>
						<td style="padding:3px;"><?php print $value; ?></td>
						<td style="padding:3px; font-weight:bold; text-align:center;"><?php print $ratedpar[$key]; ?></td>
						<td style="padding:3px; text-align:center;"><?php print $par[$key]; ?></td>
						<td style="padding:3px; text-align:center;"><?php print $thisou; ?></td>
					</tr>
<?php
					$key++;
				} // end cards
?>
					<tr<?php print $altcstyle; ?>>
                                                <td style="padding:3px;" colspan="2">
						<table style="width:100%;">
							<tr style="vertical-align:middle">
								<td><div style="max-width:180px; font-size:.9em;"><i><?php print nl2br($cardrow['comment']); ?></i></div></td>
								<td style="text-align:right; width:auto;"><b>totals:</b></td>
							</tr>
						</table>
                                                <td style="padding:3px; font-weight:bold; text-align:center;"><?php print $cardrow['coursepar']; ?></td>
                                                <td style="padding:3px; text-align:center;"><?php print $cardrow['userpar']; ?></td>
                                                <td style="padding:3px; text-align:center;"><?php print $ou; ?></td>
                                        </tr>						
				</table>
				</td>
			</tr>
<?php
				$g++;
			} // end card row while
?>
		</table>
		<div class="clearfix"></div>
<?php
		} // end if cards
?>
	</div>
<?php
		include("parlegendhide.php");
	}
} else {
        print "no circuit";
}
include("foot.php"); ?>
<script>
	$(".showpcomments").click(function(){        
		var id = $(this).attr('data-id');
		if ($('#titty_'+id).is(":hidden")) {
			$(".titty").hide();
			$(".dicks").hide();
			$("#titty_"+id).show();
		} else {
			$("#titty_"+id).hide();
		}
        });
	$(".showccomments").click(function(){
                if ($(".dicks").is(":hidden")) {
			$(".titty").hide();
                        $(".dicks").show();
                } else {
                        $(".dicks").hide();
                }
        });
	$(".showcard").click(function(){
		var id = $(this).attr('data-id');
		if ($('#card_id_'+id).is(":hidden")) {
			$("#card_id_"+id).show();
			$("#smb_"+id).text('see less');
		} else {
			$("#card_id_"+id).hide();
			$("#smb_"+id).text('see more');
		}
	});
	$(".showustats").click(function(){
                if ($("#user_stats").is(":hidden")) {
                        $("#user_stats").show();
                } else {
                        $("#user_stats").hide();
                }
        });
</script>
<?php
if (isset($_GET['cp_id'])) {
?>
<script>
	if($("#titty_<?php print $_GET['cp_id']; ?>").length > 0) {
		$("#titty_<?php print $_GET['cp_id']; ?>").show();
	}
</script>
<?php
}
if (isset($_GET['cc'])) {
?>
<script>
        if($(".dicks").length > 0) {
                $(".dicks").show();
        }
</script>
<?php
}
if (isset($_GET['card_id'])) {
?>
<script>
	var cardid = '<?php print $_GET['card_id']; ?>';
	cardid = parseInt(cardid);
	if ($('#card_id_'+cardid).is(":hidden")) {
		$("#card_id_"+cardid).show();
		var offset = $("#card_id_"+cardid).offset();
		offset.left -= 20;
		offset.top -= 20;
		$('html, body').animate({
			scrollTop: offset.top,
			scrollLeft: offset.left
		});
		$("#smb_"+cardid).text('see less');
	}
</script>
<?php
}
?>
</body>
</html>