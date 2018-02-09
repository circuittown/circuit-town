<?php 
error_reporting( E_ALL & ~E_NOTICE );
ini_set( "display_errors", 1 );

require("common.php");
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
		$_SESSION["step2_form"] = serialize($_POST);
		$_SESSION["deliver_me"] = "step2.php";
	} else {
		$_SESSION["deliver_me"] = "step2.php";
	}
        header("location:login.php");
        exit;
}
if (isset($_SESSION["step2_form"])) {
	$_POST = unserialize($_SESSION["step2_form"]);
	unset($_SESSION["step2_form"]);
}
if (isset($_POST["circ-name"])) {
        $circname = $_POST["circ-name"];
        $plist = $_POST["plist"];
	$area_id = $_POST["area_id"];
	if (isset($_POST["subarea_id"])) {
		$subarea_id = $_POST["subarea_id"];
		$cq = "select circuit from circuit where circuit = :circname and is_subarea = 'yes' and area_id = :subarea_id";
		$cqparams = array(':circname' => $circname, ':subarea_id' => $subarea_id);
		$cq2 = "select circuit from circuit where circuit = :circname and is_subarea = 'no' and area_id = :area_id";
		$cq2params = array(':circname' => $circname, ':area_id' => $area_id);
	} else {
	        $cq = "select circuit from circuit where circuit = :circname and is_subarea = 'no' and area_id = :area_id";
		$cqparams = array(':circname' => $circname, ':area_id' => $area_id);
        }
	// error checking
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
	if (isset($cq2)) {
		try {
			$cstmt2 = $db->prepare($cq2);
			$result = $cstmt2->execute($cq2params);
		} catch(PDOException $ex) {
			die("Failed to run query: " . $ex->getMessage());
		}
		$crow2 = $cstmt2->fetch();
	        if ($crow2['circuit']) {
	                $error = "there is already a circuit at this area using this name";
	        }
	}
} else {
        header("location:http://www.google.com");
        exit;
}
include("head.php"); 
?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
<script>
        $(function() {
                $( "#sortable" ).sortable();
                $( "#sortable" ).disableSelection();
        });
</script>
<script>
	function validateForm() {
		var x=document.forms["newcircuit"]["circname"].value;
		if (x==null || x=="") {
			alert("all circuits must have a name.");
			document.forms["newcircuit"]["circname"].focus();
			return false;
		}	
	}
</script>
<div id="main">
<?php include("h1.php"); ?>
<?php include("find.php"); ?>
<div style="margin-top:20px; width:560px;">
<?php
if (isset($error)) {
?>
        <strong>sorry!</strong> <?php print $error; ?>
<?php } else { ?>
        <form method="post" action="step3.php" name="newcircuit" onsubmit="return validateForm()">
        <span class="circtitle">here's your circuit</span> at <select name="area">
<?php
        $q = "select area, area_id from areas where approved = 'yes' order by TRIM(LEADING 'the ' FROM LOWER(`area`))";
	try {
	        $stmt = $db->prepare($q);
	        $result = $stmt->execute();
	} catch(PDOException $ex) {
	        die("Failed to run query: " . $ex->getMessage());
	}
	while ($row = $stmt->fetch()) {
	        if (isset($area_id)) {
	                if ($row['area_id'] == $area_id) {
	                        $selected = " selected";
	                } else {
	                        $selected = "";
	                }
	        } else {
	                $selected = "";
	        }
	        $selectarea = stripslashes($row['area']);
	        $selectarea = strtolower($selectarea);
?>
                                <option value="area_id=<?php print $row['area_id']; ?>"<?php print $selected; ?>><?php print $selectarea; ?></option>
<?php
	        $thisareaid = $row['area_id'];
	        $saq = "select subarea, subarea_id from subareas where area_id = :thisareaid order by TRIM(LEADING 'the ' FROM LOWER(`subarea`))";
	        $saqparams = array(':thisareaid' => $thisareaid);
	        try {
	                $sastmt = $db->prepare($saq);
	                $result = $sastmt->execute($saqparams);
	        } catch(PDOException $ex) {
	                die("Failed to run query: " . $ex->getMessage());
	        }
	        while ($sarow = $sastmt->fetch()) {
	                if (isset($subarea_id)) {
	                        if ($sarow['subarea_id'] == $subarea_id) {
	                                $selected = " selected";
	                        } else {
	                                $selected = "";
	                        }
	                } else {
	                        $selected = "";
	                }
	                $selectsubarea = stripslashes($sarow['subarea']);
	                $selectsubarea = strtolower($selectsubarea);
?>
                                <option value="subarea_id=<?php print $sarow['subarea_id']; ?>"<?php print $selected; ?>>- <?php print $selectsubarea; ?></option>
<?php
	        }
	}
?>
        </select><div class="brbr"></div>

        <input type="text" name="circname" value="<?php print $circname; ?>"><div class="brbr"></div>
        
        click and drag the problems in the list to reorder them. you can delete, edit, and add problems in the next step. <a href="#" onclick="document.getElementById('resubmit').submit();" class="register">click here</a> to start over.<div class="brbr"></div>
        
        <div style="margin-left:40px; width:200px;">
<?php
	$plist = trim($plist);
	if (substr($plist, 0, 1) == 1) {
		$textonly = preg_replace("/\d+\s*[-\\.)]/",", ",$plist);
	        $textonly = substr_replace($textonly, '', 0, 2);
	        $explist = explode(', ', $textonly);
	} else {
	        $explist = explode(', ', $plist);
	}
?>
		<ol id="sortable" style="cursor:move;">
<?php
	$x = 1;
	foreach($explist as $item) {
	        $item = trim($item);
?>
                        <li id="problem_<?php print $x; ?>"><input type="hidden" name="problem[]" value="<?php print $item; ?>"><?php print $item; ?></li>
<?php
	        $x++;
	}
	$y = $x - 1;
?>
                </ol>
        </div>
        <input type="hidden" name="area_id" value="<?php print $area_id; ?>">
<?php
	if (isset($subarea_id)) {
?>
        <input type="hidden" name="subarea_id" value="<?php print $subarea_id; ?>">
<?php
	}
?>
	<div style="margin-top:14px; width:40%; background-color:#665D50; padding-top:5px; padding-left:5px; padding-right:5px; overflow:hidden;">
	<div class="circtitle" style="margin-bottom:5px;">difficulty</div>
<?php
        if (isset($_SESSION['user_id'])) {
                $userid = $_SESSION['user_id'];
                $blackcountq = "select count(cards.card_id) from cards inner join circuit on cards.circuit_id = circuit.circuit_id where cards.user_mast_id = :user_id and circuit.colour = 'black'";
                $blackcountqparams = array(':user_id' => $userid);
                try {
                        $blackcountstmt = $db->prepare($blackcountq);
                        $result = $blackcountstmt->execute($blackcountqparams);
                } catch(PDOException $ex) {
                        die("Failed to run query: " . $ex->getMessage());
                }
                $blackcountrow = $blackcountstmt->fetch();
                $blackcount = $blackcountrow['count(cards.card_id)'];
                if ($blackcount > 2) {
                        $coq = "select colour, adjective, english from colour order by colour_id";
                }
        }
        if (!isset($coq)) {
        	$coq = "select colour, adjective, english from colour where colour != 'pink' order by colour_id";
        }
	try {
		$costmt = $db->prepare($coq);
		$result = $costmt->execute();
	} catch(PDOException $ex) {
		die("Failed to run query: " . $ex->getMessage());
	}
	while ($corow = $costmt->fetch()) {
		if ($corow['colour'] == "black") {
                        $xtrastyle = " color:#9C9281;";
                } else {
                         $xtrastyle = "";
                }
		if ($corow['adjective'] == "Assez difficile") {
			$checked = "checked=\"checked\"";
		} else {
			$checked = "";
		}
?>
		<div style="margin-bottom:5px; background-color:<?php print $corow['colour']; ?>;<?php print $xtrastyle; ?>"><label style="vertical-align:middle; display:inline;"for="<?php print $corow['colour']; ?>"><input id="<?php print $corow['colour']; ?>" type="radio" name="colour" value="<?php print $corow['colour']; ?>" style="margin-right:-246px; margin-bottom:10px; vertical-align:middle; display:inline;"<?php print $checked; ?>></input> <?php print $corow['adjective']; ?></label></div>
<?php
	}
?>
		<div style="height:1px;">&nbsp;</div>
	</div>
        <button type="submit" id="mybutton4">create circuit</button>
        </form>
        <form action="newcircuit.php" method="post" id="resubmit">
                <input type="hidden" name="circ-name" value="<?php print $circname; ?>">
                <input type="hidden" name="plist" value="<?php print $plist; ?>">
<?php
	if (isset($subarea_id)) {
?>
                <input type="hidden" name="subarea_id" value="<?php print $subarea_id; ?>">
<?php
	} else {
?>
		<input type="hidden" name="area_id" value="<?php print $area_id; ?>">
<?php
	}
?>
        </form>
<?php } 
include("colorlegend.php");
?>
</div>
<div style="padding-bottom:80px;"></div>
<?php include("foot.php"); ?>
</div>
</body>
</html>