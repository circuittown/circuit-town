<?php 
include("common.php");
if (isset($_POST["circname"])) {
	$colour = $_POST["colour"];
        $circname = $_POST["circname"];
        $problem = $_POST["problem"];
        $area = $_POST["area"];
        if (strpos($area,'subarea') !== false) {
                $subarea_id = str_replace("subarea_id=", "", $area);
        } else {
                $area_id = str_replace("area_id=", "", $area);
        }
        if (isset($subarea_id)) {
                $idq = "select area_id from subareas where subarea_id = :subarea_id";
		$idqparams = array(':subarea_id' => $subarea_id);
		try {
			$idstmt = $db->prepare($idq);
			$result = $idstmt->execute($idqparams);
		} catch(PDOException $ex) {
			die("Failed to run query: " . $ex->getMessage());
		}
		$idrow = $idstmt->fetch();
                $area_id = $idrow['area_id'];
                $cq = "select circuit from circuit where circuit = :circname and is_subarea = 'yes' and area_id = :subarea_id";
		$cqparams = array(':circname' => $circname, ':subarea_id' => $subarea_id);
        } else {
                $cq = "select circuit from circuit where circuit = :circname and is_subarea = 'no' and area_id = :area_id";
		$cqparams = array(':circname' => $circname, ':area_id' => $area_id);
        }
        // idiot checking
	try {
                $cstmt = $db->prepare($cq);
                $result = $cstmt->execute($cqparams);
        } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
        }
        $crow = $cstmt->fetch();
	if ($crow['circuit']) {
		$error = "there is already a circuit at this area using this name";
	}
} else {
        header("location:index.php");
        exit;
}
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
        header("location:login.php");
        exit;
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

});
</script>
<div id="main">
<?php include("h1.php"); ?>
<?php include("find.php"); ?>
<div style="margin-top:20px; width:auto;">
<?php
if (isset($error)) {
?>
	<strong>sorry!</strong> <?php print $error; ?>
<?php
} else {
?>
        <h3>new circuit: <?php print $circname; ?></h3>
<?php
	$aq = "select area from areas where area_id = :area_id";
	$aqparams = array(':area_id' => $area_id);
	try {
		$aqstmt = $db->prepare($aq);
		$result = $aqstmt->execute($aqparams);
	} catch(PDOException $ex) {
		die("Failed to run query: " . $ex->getMessage());
	}
	$arow = $aqstmt->fetch();
	$area = $arow['area'];
	$area = strtolower($area);
	if (isset($subarea_id)) {
	        $saq = "select subarea from subareas where subarea_id = :subarea_id";
		$saqparams = array(':subarea_id' => $subarea_id);
                try {
                        $sastmt = $db->prepare($saq);
                        $result = $sastmt->execute($saqparams);
                } catch(PDOException $ex) {
                        die("Failed to run query: " . $ex->getMessage());
                }
		$sarow = $sastmt->fetch();
		$sarea = $sarow['subarea'];
		$sarea = strtolower($sarea);
	        $xtra = ", " . $sarea;
	        $cinsert = "insert into circuit (circuit, area_id, approved, is_subarea, colour, user_mast_id) values (:circname, :subarea_id, 'yes', 'yes', :colour, :user_mast_id)";
		$cinsertparams = array(':circname' => $circname, ':subarea_id' => $subarea_id, ':colour' => $colour, ':user_mast_id' => $_SESSION['user_id']);
	} else {
		$xtra = "";
		$cinsert = "insert into circuit (circuit, area_id, approved, is_subarea, colour, user_mast_id) values (:circname, :area_id, 'yes', 'no', :colour, :user_mast_id)";
		$cinsertparams = array(':circname' => $circname, ':area_id' => $area_id, ':colour' => $colour, ':user_mast_id' => $_SESSION['user_id']);
	}
	try {
                $cistmt = $db->prepare($cinsert);
                $result = $cistmt->execute($cinsertparams);
        } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
        }
	$cicuit_id = $db->lastInsertId();
	$circuit_id = $cicuit_id;
	$aui = "insert into allowed_users (circuit_id, user_mast_id) values (:circuit_id, :user_mast_id)";
	$auiparams = array(':circuit_id' => $circuit_id, ':user_mast_id' => $user_mast_id);
	try {
                $auistmt = $db->prepare($aui);
                $result = $auistmt->execute($auiparams);
        } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
        }
?>
        <strong>located at: <?php print $area; ?><?php print $xtra; ?></strong><div class="brbr"></div>

	<form method="post" action="update-circuit.php" id="newlist">
        <div style="width:auto; margin-left:40px; float:left;">
		<div style="font-size:.8em; font-style:italic; max-width:180px; margin-bottom:10px;">
		below is your circuit. you can rearrange problems, add problems and delete problems. each problem is given a default par of 2. "par" is the average number of attempts 
		for a climber performing at the level of the circuit's difficulty.
                </div>
		<ol id="sortable" style="cursor:move;">
<?php
	$x = 1;
	$numprobs = count($problem);
	foreach( $problem as $key => $n ) {
		$problem = $n;
		$insert = "insert into circuit_problems (problem, circuit_id, problem_order) values (:problem, :cicuit_id, :x)";
		$insertparams = array(':problem' => $problem, ':cicuit_id' => $cicuit_id, ':x' => $x);
		try {
			$istmt = $db->prepare($insert); 
			$result = $istmt->execute($insertparams);
			$cp_id = $db->lastInsertId();
		} catch(PDOException $ex) {
			die("Failed to run query: " . $ex->getMessage());
		}
		if ($x == $numprobs) {
			$jsx = $x;
		}
?>
                        <li id="problem_<?php print $x; ?>"><input type="hidden" name="problem[]" value="<?php print $problem; ?>">
<span class="removefromlist"> [ <a href="javascript:void(0);" data-id="problem_<?php print $x; ?>" class="takeoffhoser">x</a> ] </span><span class="p_rob" id="p_rob_<?php print $x; ?>"><?php print $problem; ?></span>
<div id="ppar"> &nbsp; &nbsp; <span class="parnumber_<?php print $x; ?>">2</span> <span class="removefromlist"> [ <a href="javascript:void(0);" data-id="<?php print $x; ?>" class="plusoffhoser">+
</a> ] </span><span class="removefromlist"> [ <a href="javascript:void(0);" data-id="<?php print $x; ?>" class="minusoffhoser">-</a> ] </span></div>
<input type="hidden" name="par[]" value="2" id="hidepar_<?php print $x; ?>"></li>
<?php
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
                $coq = "select adjective, english from colour where colour = :colour";
                $coqparams = array(':colour' => $colour);
                try {
                        $costmt = $db->prepare($coq);
                        $result = $costmt->execute($coqparams);
                } catch(PDOException $ex) {
                        die("Failed to run query: " . $ex->getMessage());
                }
                $corow = $costmt->fetch();
		$greet[] = ", thanks!";
		$greet[] = ", and don't think we didn't notice.";
		$greet[] = ", cheers!";
		$greet[] = ", death to everyone";
		$greet[] = ", a winner in the coming apocalypse";
		$greet[] = ", a nobody";
		$greet[] = ", you're really somethin'";
		$greet[] = ", a hero";
		$greet[] = ", everyone's favorite!";
		$greet[] = ", private eye";
		$greet[] = ", esquire";
		$greet[] = ", a haggard old person with addictive tendencies";
		$greet[] = ", the next president of the United States";
		$greet[] = ", the next prime minister of Canada";
		$rand = rand(0, 12);
		if ($colour == "black") {
                        $xtrastyle = " color:#9C9281;";
                } else {
                         $xtrastyle = "";
                }
?>
                        <div style="height:25px;">
                                <div id="colour" style="background-color:<?php print $colour; ?>;<?php print $xtrastyle; ?>">
                                <?php print strtolower($corow['adjective']); ?>
                                </div>
                                <div id="cadj">this circuit is <?php print lcfirst($colour); ?> for <?php print lcfirst($corow['english']); ?></div>
                        </div>
                        <div class="clearfix" style="width:100%; text-align:right; margin-top:5px;">posted by: <em><strong>you</strong></em><?php print $greet[$rand]; ?></div>
                </div>
		<div style="margin-top:10px; width:100%; text-align:right; margin-left:-10px;"><a href="circuit_detail.php?circuit_id=<?php print $circuit_id; ?>" class="register">view circuit</a></div>
        </div>
	<div class="clearfix"></div>
	<input type="hidden" name="circuit_id" value="<?php print $cicuit_id; ?>">
	<button type="submit" id="mybutton" style="width:auto; float:left; padding-right:5px; padding-left:5px; margin-top:20px;">update circuit</button><button type="button" id="mybutton" class="goback" style="margin-left:10px; width:auto; float:left; padding-right:5px; padding-left:5px; margin-top:20px;">done</button>
	<div class="clearfix"></div>
	</form>
<?php
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
			$("#problem_"+jsx).after('<li id="problem_'+(jsx+1)+'"><input type="hidden" name="problem[]" value="'+value+'"><span class="removefromlist"> [ <a href="javascript:void(0);" data-id="problem_'+(jsx+1)+'" class="takeoffhoser">x</a> ] </span><span class="p_rob" id="p_rob_'+(jsx+1)+'">'+value+'</span><div id="ppar"> &nbsp; &nbsp; <span class="parnumber_'+(jsx+1)+'">2</span> <span class="removefromlist"> [ <a href="javascript:void(0);" class="plusoffhoser" data-id="'+(jsx+1)+'">+</a> ] </span><span class="removefromlist"> [ <a href="javascript:void(0);" class="minusoffhoser" data-id="'+(jsx+1)+'">-</a> ] </span></div><input id="hidepar_'+(jsx+1)+'" type="hidden" name="par[]" value="2"></li>');
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
$(".goback").click(function() {
        window.location = 'update-circuit.php?circuit_id=<?php print $circuit_id; ?>';
});
</script>
</body>
</html>