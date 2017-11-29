<?php 
include("common.php");
session_start();
if (!isset($_SESSION["usermast"])) {
	if (!empty($_POST)) {
                $_SESSION["update_form"] = serialize($_POST);
                $_SESSION["deliver_me"] = "update-circuit.php?circuit_id=" . $_POST["circuit_id"];
        }
	if (!empty($_GET)) {
                $_SESSION["deliver_me"] = "update-circuit.php?circuit_id=" . $_GET["circuit_id"];
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
	if (!empty($_GET['circuit_id'])) { 
		$circuit_id = $_GET["circuit_id"];
		$cq = "select problem, par, cp_id from circuit_problems where circuit_id = :circuit_id order by problem_order";
		$cqparams = array(':circuit_id' => $circuit_id);
		try {
                        $cstmt = $db->prepare($cq);
                        $result = $cstmt->execute($cqparams);
                } catch(PDOException $ex) {
                        die("Failed to run query: " . $ex->getMessage());
                }
		while ($crow = $cstmt->fetch()) {
			$problem[] = $crow['problem'];
			$par[] = $crow['par'];
			$cp_id[] = $crow['cp_id'];
		}
        }
	if (!empty($_POST['circuit_id'])) { 
                $circuit_id = $_POST["circuit_id"];
        }
	include("head.php");
?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
<script>
$(document).ready(function() {
        $(function() {
                $( "#sortable" ).sortable({
                        items: "li:not(.disable_sort)",
			update: function( event, ui ) {
				formchanged = 1;
				var id = ui.item.attr("id");
				id = id.split("_");
				id = id[1];
				$("#p_rob_"+id).css('color', '#665D50');
			}
                });
                $( "#sortable" ).disableSelection();
        });
        $(window).keydown(function(event){
                if(event.keyCode == 13) {
                        event.preventDefault();
                        $(document).mouseup();
                }
        });
        $("#newlist").submit(function( event ) {
                var container = $(".smallinput");
                if (formchanged == 0) {
                        alert( "please change something." );
                        event.preventDefault();
                }
        });
	$("#deletecirq").click(function() {
        	var c = confirm("are you sure you want to delete this circuit?");
        	return c; //you can just return c because it will be true or false
	});
	$(".comfirmunshare").click(function() {
                var c = confirm("are you sure you want to remove edit privileges for this user?");
                return c; //you can just return c because it will be true or false
        });
});
</script>
<div id="main">
<?php include("h1.php"); ?>
<?php include("find.php"); ?>
        <div id="newshit">
		<h3 id="mydear">update circuit</h3>
<?php
	if(isset($circuit_id)) {
	        if (isset($_SESSION["update_form"])) {
	                $_POST = unserialize($_SESSION["update_form"]);
	                unset($_SESSION["update_form"]);
	        }
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
		$ccq = "select count(*) as count from cirq_comments where circuit_id = :circuit_id";
		$ccqparams = array(':circuit_id' => $circuit_id);
		try {
                        $ccstmt = $db->prepare($ccq);
                        $result = $ccstmt->execute($ccqparams);
                } catch(PDOException $ex) {
                        die("Failed to run query: " . $ex->getMessage());
                }
		$ccrow = $ccstmt->fetch();		
		$ccnum = $ccrow['count'];
		if ($ccnum > 0) {
			$ccaddition = " <span class=\"removefromlist\"> [ <a href=\"javascript:void(0);\" data-id=\"...\" class=\"ccoffhoser\">" . $ccnum . "</a> ] </span>";
		} else {
			$ccaddition = " <span class=\"removefromlist\"> [ <a href=\"javascript:void(0);\" data-id=\"...\" class=\"ccoffhoser\">+</a> ] </span>";
		}
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
		if (in_array($_SESSION['user_id'], $authusers)) {
						
?>
		<script>
			$('#mydear').append(': <?php print addslashes($crow['circuit']); ?>').show();
			$('#mydear').append('<?php print $ccaddition; ?>').show();
		</script>
		<strong>located at: <?php print $area; ?></strong><div class="brbr"></div>

	        <form method="post" action="update-circuit.php" id="newlist">
	        <div style="width:auto; margin-left:40px; float:left;">
	                <div style="font-size:.8em; font-style:italic; max-width:180px; margin-bottom:10px;">
	                below is your circuit. you can rearrange problems, add problems and delete problems. each problem is given a default par of 2. "par" is the average number of attempts 
			for a climber performing at the level of the circuit's difficulty.</div>
	                <ol id="sortable" style="cursor:move;">
<?php
			if (!isset($problem)) {
				$problem = $_POST["problem"];
				$par = $_POST["par"];	
			}
		        $x = 1;
		        $numprobs = count($problem);
		        foreach( $problem as $key => $n ) {
		                $thisproblem = $n;
				$parindex = $x-1;
				if ($x == $numprobs) {
					$jsx = $x;
				}
				$pcq = "select count(pc_id) as count from problem_comments where problem = :problem and circuit_id = :circuit_id";
				$pcqparams = array(':problem' => $thisproblem, ':circuit_id' => $circuit_id);
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
				} else {
					$pcchtml = "";
				}
?>
				<li id="problem_<?php print $x; ?>"><input type="hidden" name="problem[]" value="<?php print $thisproblem; ?>">
<span class="removefromlist"> [ <a href="javascript:void(0);" data-id="problem_<?php print $x; ?>" class="takeoffhoser">x</a> ] </span><span class="p_rob" id="p_rob_<?php print $x; ?>"><?php print $thisproblem; ?></span><?php print $pcchtml; ?>
<div id="ppar"> &nbsp; &nbsp; <span class="parnumber_<?php print $x; ?>"><?php print $par[$parindex]; ?></span> <span class="removefromlist"> [ <a href="javascript:void(0);" data-id="<?php print $x; ?>" class="plusoffhoser">+
</a> ] </span><span class="removefromlist"> [ <a href="javascript:void(0);" data-id="<?php print $x; ?>" class="minusoffhoser">-</a> ] </span><span class="removefromlist"> [ <a href="javascript:void(0);" data-id="<?php print $x; ?>" class="commentoffhoser">comment</a> ] </span></div>
<input type="hidden" name="par[]" value="<?php print $par[$parindex]; ?>" id="hidepar_<?php print $x; ?>"></li>
<?php
				if ($x == 1) {
					$delete = "delete from circuit_problems where circuit_id = :circuit_id";
					$deleteparams = array(':circuit_id' => $circuit_id);
					try {
						$dstmt = $db->prepare($delete);
						$result = $dstmt->execute($deleteparams);
					} catch(PDOException $ex) {
						die("Failed to run query: " . $ex->getMessage());
					}
					$nidq = "SELECT max(cp_id) as upper from circuit_problems";
	                                try {
	                                        $nidstmt = $db->prepare($nidq);
	                                        $result = $nidstmt->execute();
	                                } catch(PDOException $ex) {
	                                        die("failed to run query: " . $ex->getMessage());
	                                }
	                                $nidrow = $nidstmt->fetch();
	                                $nid = $nidrow['upper'] + 1;
				}
				$insert = "insert into circuit_problems (problem, circuit_id, problem_order, par, cp_id) values (:problem, :cicuit_id, :x, :par, :cp_id)";
				$insertparams = array(':problem' => $thisproblem, ':cicuit_id' => $circuit_id, ':x' => $x, ':par' => $par[$parindex], ':cp_id' => $nid);
				try {
                                        $istmt = $db->prepare($insert);
                                        $result = $istmt->execute($insertparams);
                                } catch(PDOException $ex) {
                                        die("failed to run query: " . $ex->getMessage());
                                }
				$cp_id[] = $nid;
				$nid = $nid + 1;
		                $x++;
		        }
?>
				<li class="disable_sort"><div id="addleft" class="addanewrig"><strong><a href="javascript:void(0);">+</a></strong></div>
	                        <div id="newrig"><input type="text" class="smallinput"></div></li>
	                </ol>
		</div>
		<div id="ncrightlist">
                <div class="logincolorthing">
                <?php
			$coq = "select adjective, english, css from colour where colour = :colour";
	                $coqparams = array(':colour' => $colour);
	                try {
	                        $costmt = $db->prepare($coq);
	                        $result = $costmt->execute($coqparams);
	                } catch(PDOException $ex) {
	                        die("Failed to run query: " . $ex->getMessage());
	                }
			$corow = $costmt->fetch();
			if ($colour == "black") {
	                        $xtrastyle = " color:#9C9281;";
			} else {
	                         $xtrastyle = "";
	                }
?>
                        <div style="min-height:25px;">
                                <div id="colour" style="background-color:<?php print $corow['css']; ?>;<?php print $xtrastyle; ?>">
                                <?php print strtolower($corow['adjective']); ?>
                                </div>
                                <div id="cadj">
					<div>this circuit is <?php print lcfirst($colour); ?> for <?php print lcfirst($corow['english']); ?></div>
<?php
			if ($ownerid == $_SESSION['user_id']) {
?>
					<div style="margin-left:-2px; margin-top:7px;">
						<table>
							<tr>
								<td style="padding-right:3px; padding-top:3px; padding-bottom:3px; font-size:.8em; color:#322E29;">change difficulty</td>
<?php
				if (in_array($_SESSION['usermast'], $_SESSION['pink_gods'])) {
					$lickq = "select colour, css from colour";
				} else {
					$lickq = "select colour, css from colour where colour != 'pink'";
				}
				try {
	                                $lickstmt = $db->prepare($lickq);
	                                $result = $lickstmt->execute();
	                        } catch(PDOException $ex) {
	                                die("Failed to run query: " . $ex->getMessage());
	                        }
	                        while($lickrow = $lickstmt->fetch()) {
?>
							<td style="padding:3px;"><div id="new_<?php print $lickrow['colour']; ?>" style="width:14px; height:14px; background-color:<?php print $lickrow['css']; ?>; cursor:pointer;" data-color="<?php print $lickrow['colour']; ?>" data-id="<?php print $circuit_id; ?>" class="changemycolor"></div></td>
<?php
				}
?>
							</tr>
						</table>
					</div>
<?php
			}
?>
				</div>
				<div class="clearfix"></div>
                        </div>
                </div>
		<div style="margin-top:10px; width:100%; text-align:right; margin-left:-10px;"><a href="circuit_detail.php?circuit_id=<?php print $circuit_id; ?>" class="register">view circuit</a></div>
<?php
			if ($_SESSION['user_id'] == $ownerid) {
?>
		<div style="margin-top:1px; width:100%; text-align:right; margin-left:-10px;"><a href="delete-cirq.php?circuit_id=<?php print $circuit_id; ?>&fromupdate=1" class="register" id="deletecirq">delete circuit</a></div>
		<div style="margin-top:1px; width:100%; text-align:right; margin-left:-10px;"><a href="javascript:void(0);" class="register" id="sharethis">share circuit</a></div>
<?php
			}
?>
		<div style="margin-top:5px; width:100%; text-align:right; margin-left:-10px; font-size:.8em;"><span style="display:inline-block; width:240px;"><b>owner:</b> <?php print $owner; ?><br>
		<b>permission to edit:</b> 
<?php
                        foreach ($authhandles as $key => $val) {
				if ($key > 0) {
					print ", ";
				}
				if ($authusers[$key] != $ownerid) {
					$authanchor = "<a href=\"unshare-cirq.php?circuit_id=" . $circuit_id . "&user_mast_id=" . $authusers[$key] . "\" class=\"register comfirmunshare\">";
					$authanchorclose = "</a>";
				} else {
					$authanchor = "";
					$authanchorclose = "";
				}
				$newval = strtolower($val);
				if ($_SESSION['user_id'] == $ownerid) {
					print $authanchor . $newval . $authanchorclose;
				} else {
					print $newval;
				}
                        }
?>
		</span></div>
		<div id="uaholder">
		<select name="userallow" id="userallow">
			<option>- select a user -</option>
<?php
			$uuq = "select handle, user_mast_id from user_mast where approved = 'yes' order by handle";
			try {
                                $uustmt = $db->prepare($uuq);
                                $result = $uustmt->execute();
                        } catch(PDOException $ex) {
                                die("Failed to run query: " . $ex->getMessage());
                        }
			while($uurow = $uustmt->fetch()) {
?>
			<option value="<?php print $uurow['user_mast_id']; ?>"><?php print $uurow['handle']; ?></option>
<?php
			}
?>
		</select>
		</div>
        </div>
        <div class="clearfix"></div>
        <input type="hidden" name="circuit_id" value="<?php print $circuit_id; ?>">
        <button type="submit" id="mybutton" style="width:auto; float:left; padding-right:5px; padding-left:5px; margin-top:20px;">update circuit</button><button type="button" id="mybutton" class="goback" style="margin-left:10px; width:auto; float:left; padding-right:5px; padding-left:5px; margin-top:20px;">cancel</button>
        <div class="clearfix"></div>
        </form>
	<form method="post" action="share-cirq.php" id="sharethiscirq">
		<input type="hidden" name="circuit_id" value="<?php print $circuit_id; ?>">
		<input id="umidsc" type="hidden" name="user_mast_id" value="">
	</form>
<?php
			$numprobs = count($problem);
			$b = 1;
                        foreach( $problem as $key => $n ) {
?>
	<form method="post" action="problem-comment.php" id="problem_comment_<?php print $b; ?>">
		<input type="hidden" name="problem" value="<?php print $n; ?>">
		<input type="hidden" name="circuit_id" value="<?php print $circuit_id; ?>">
	</form>
<?php
				$b++;
			}
?>
	<form method="post" action="cirq-comment.php" id="cirq_comment">
                <input type="hidden" name="circuit_id" value="<?php print $circuit_id; ?>">
        </form>
<?php
		} else {
			print "something went wrong. perhaps your session expired.<br>our records indicate you don't have access to this circuit.";
		}
	} else {
		print "we just got the weirdest error. you wouldn't even believe how weird.";
	}
}
?>
	</div>
<?php include("foot.php"); ?>
</div>
<script>
$(".addanewrig").click(function(){
        $("#newrig").show();
        $("#addleft").hide();
        $(".smallinput").focus();
});
var jsx = <?php print $jsx; //?>;
var formchanged = 0;
$(document).mouseup(function (e){
        var container = $(".smallinput");

        if (!container.is(e.target) && container.has(e.target).length === 0) {
                $("#newrig").hide();
                $("#addleft").show();
                var value=$.trim($(".smallinput").val());
                if(value.length>0) {
                        $("#problem_"+jsx).after('<li id="problem_'+(jsx+1)+'"><input type="hidden" name="problem[]" value="'+value+'"><span class="removefromlist"> [ <a href="javascript:void(0);" data-id="problem_'+(jsx+1)+'" class="takeoffhoser">x</a> ] </span><span class="p_rob" id="p_rob_'+(jsx+1)+'">'+value+'</span><div id="ppar"> &nbsp; &nbsp; <span class="parnumber_'+(jsx+1)+'">3</span> <span class="removefromlist"> [ <a href="javascript:void(0);" class="plusoffhoser" data-id="'+(jsx+1)+'">+</a> ] </span><span class="removefromlist"> [ <a href="javascript:void(0);" class="minusoffhoser" data-id="'+(jsx+1)+'">-</a> ] </span></div><input id="hidepar_'+(jsx+1)+'" type="hidden" name="par[]" value="3"></li>');
                        $(".smallinput").val('');
			$("#p_rob_"+(jsx+1)).css('color', '#665D50');
                        jsx = jsx+1;
                        alert
                        formchanged = 1;
                }
        }
});
$(document).on('click', ".takeoffhoser", function() {
        var id = $(this).attr('data-id');
        if (($( "#sortable li" ).size()-1)>1) {
                $("#"+id).remove();
		$(".p_rob").css('color', '#665D50');
                formchanged = 1;
        }
        var newtotalli = $( "#sortable li" ).size();
        newtotalli=newtotalli-2;
        var k = $("#sortable li").slice(newtotalli).attr('id');
        var res = k.split("_");
        jsx = parseInt(res[1]);
});
$(document).on('click', ".plusoffhoser", function() {
        var id = $(this).attr('data-id');
        var thispar = $(".parnumber_"+id).text();
        thispar = parseInt(thispar);
        if (thispar < 5) {
                thispar = thispar+1;
                formchanged = 1;
		$(".parnumber_"+id).css('color', '#665D50');
        }
        $(".parnumber_"+id).text(thispar);
        $("#hidepar_"+id).val(thispar);
});
$(document).on('click', ".minusoffhoser", function() {
        var id = $(this).attr('data-id');
        var thispar = $(".parnumber_"+id).text();
        thispar = parseInt(thispar);
        if (thispar > 1) {
                thispar = thispar-1;
                formchanged = 1;
		$(".parnumber_"+id).css('color', '#665D50');
        }
        $(".parnumber_"+id).text(thispar);
        $("#hidepar_"+id).val(thispar);
});
$("#sharethis").click(function() {
	if ($('#userallow').is(":hidden")) {
		$("#userallow").show();
	} else {
		$("#userallow").hide();
	}
});
$( "#userallow" ).change(function() {
	$("#userallow").hide();
	$("#umidsc").val($("#userallow").val());
	$("#sharethiscirq").submit();
});
$(".commentoffhoser").click(function() {
	var id = $(this).attr('data-id');
	$("#problem_comment_"+id).submit();
});
$(".ccoffhoser").click(function() {
        $("#cirq_comment").submit();
});
$(".goback").click(function() {
	window.location = 'circuit_detail.php?circuit_id=<?php print $circuit_id; ?>';
});
$(".changemycolor").click(function() {
	var id = $(this).attr('data-id');
	var color = $(this).attr('data-color');
	window.location = 'change-color.php?circuit_id='+id+'&color='+color;
});
</script>
</body>
</html>