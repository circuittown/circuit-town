<?php 
include("common.php");
if (isset($_GET["area_id"])) {
        $area_id = $_GET["area_id"];
}
if (isset($_POST["circ-name"])) {
        $circname = $_POST["circ-name"];
        $plist = $_POST["plist"];
        if (isset($_POST["subarea_id"])) {
                $subarea_id = $_POST["subarea_id"];
        } else {
		 $area_id = $_POST["area_id"];
	}
}
include("head.php"); 
?>
<script>
	function validateForm() {
		var x=document.forms["newcircuit"]["circ-name"].value;
		if (x==null || x=="") {
			alert("all circuits must have a name.");
			document.forms["newcircuit"]["circ-name"].focus();
			return false;
		}	
		var x=document.forms["newcircuit"]["plist"].value;    
                if (x==null || x=="") {
                        alert("all circuits must start with at least one problem.");
                        document.forms["newcircuit"]["plist"].focus();    
                        return false;
                }
	}
</script>
<div id="main">
<?php include("h1.php"); ?>
<?php include("find.php"); ?>
	<div id="newshit">
		<h3>adding a new circuit!</h3>
		<div class="brbr"></div>
		<form name="knownareas" method="post" action="#">
			<strong>known areas</strong> &nbsp;<select name="area" onchange="window.location.href= this.form.area.options[this.form.area.selectedIndex].value">
				<option value="#">- known areas -</option>
<?php
if (isset($_GET["subarea_id"])) {
	$subarea_id = $_GET["subarea_id"];
}
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
				<option value="newcircuit.php?area_id=<?php print $row['area_id']; ?>"<?php print $selected; ?>><?php print $selectarea; ?></option>
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
				<option value="newcircuit.php?subarea_id=<?php print $sarow['subarea_id']; ?>"<?php print $selected; ?>>- <?php print $selectsubarea; ?></option>
<?php
	}
}
?>
			</select> &nbsp; <a href="unknown-area.php" class="unknown">unknown areas</a>
			<div class="brbr"></div>
		</form>
<?php
if (isset($area_id)) {
	$aq = "select area from areas where area_id = :area_id";
	$aqparams = array(':area_id' => $area_id);
        try {
                $astmt = $db->prepare($aq);
                $result = $astmt->execute($aqparams);
        } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
        }
	$arow = $astmt->fetch();
	$area = stripslashes($arow['area']);
	$area = strtolower($area);
?>
		<strong>ok! let's add a new circuit to <?php print $area; ?></strong><div class="brbr"></div>
			
		<form method="post" action="step2.php" name="newcircuit" onsubmit="return validateForm()">
		<div id="new_circ_form">
			<label>
			<div id="circuit-name">
			<strong>circuit name:</strong>
			</div>
<?php
        if (isset($circname)) {
                $circname = " value=\"" . $circname . "\"";
                $plist = $plist;
        } else {
                $circname = "";
                $plist = "";
        }
?>
			<input type="text" name="circ-name" class="circ-name"<?php print $circname; ?>>
			</label><div class="brbr"></div>

			<label>
			<div id="instructions">list the problems in the order in which you want them to be listed, seperated by commas.<div style="margin-bottom:7px;"></div>
                        example: "crap arete, what's left of less, left el murray, center el murray, right el murray" would yield the following list:<br>
                                <div style="margin-left:20px; margin-bottom:7px;">
                                        <ol>
                                        <li>crap arete</li>
                                        <li>what's left of less</li>
                                        <li>left el murray</li>
                                        <li>center el murray</li>
                                        <li>right el murray</li>
                                </ol></div>
			please note: problems cannot have commas in the name.
                        </div>
			<textarea id="list" name="plist"><?php print $plist; ?></textarea>
			</label><div class="brbr"></div>
			
			<div style="height:20px; margin-top:20px; margin-bottom:26px;">
				<input type="hidden" name="area_id" value="<?php print $area_id; ?>">
				<button type="submit" id="mybutton">step 2</button>
			</div>
		</div>
		</form>
<?php
}
if (isset($subarea_id)) {
        $aq = "select subarea, area_id from subareas where subarea_id = :subarea_id";
	$aqparams = array(':subarea_id' => $subarea_id);
        try {
                $astmt = $db->prepare($aq);
                $result = $astmt->execute($aqparams);
        } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
        }
        $arow = $astmt->fetch();
        $subarea = strtolower($arow['subarea']);
        $subarea = stripslashes($subarea);
	$xareaid = $arow['area_id'];
	$naq = "select area from areas where area_id = :xareaid";
        $naqparams = array(':xareaid' => $xareaid);
        try {
                $nastmt = $db->prepare($naq);
                $result = $nastmt->execute($naqparams);
        } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
        }
        $narow = $nastmt->fetch();
	$area = strtolower($narow['area']);
	$area = stripslashes($area);
?>
                <strong>ok! let's add a new circuit to <?php print $area; ?>, <?php print $subarea; ?></strong><div class="brbr"></div>

                <form method="post" action="step2.php" name="newcircuit" onsubmit="return validateForm()">
                <div id="new_circ_form">
                        <label>
                        <div id="circuit-name">
                        <strong>circuit name:</strong>
                        </div>
<?php
        if (isset($circname)) {
                $circname = " value=\"" . $circname . "\"";
                $plist = $plist;
        } else {
                $circname = "";
                $plist = "";
        }
?>
                        <input type="text" name="circ-name" class="circ-name"<?php print $circname; ?>>
                        </label><div class="brbr"></div>

                        <label>
			<div id="instructions">list the problems in the order in which you want them to be listed, seperated by commas.<div style="margin-bottom:7px;"></div>        
                        example: "crap arete, what's left of less, left el murray, center el murray, right el murray" would yield the following list:<br>
                                <div style="margin-left:20px; margin-bottom:7px;">
                                        <ol>
                                        <li>crap arete</li>
                                        <li>what's left of less</li>
                                        <li>left el murray</li>
                                        <li>center el murray</li>
                                        <li>right el murray</li>
                                </ol></div>
                        please note: problems cannot have commas in the name.                                        
                        </div>
                        <textarea id="list" name="plist"><?php print $plist; ?></textarea>
                        </label><div class="brbr"></div>

                        <div style="height:20px; margin-top:20px; margin-bottom:26px;">
                                <input type="hidden" name="area_id" value="<?php print $xareaid; ?>">
				<input type="hidden" name="subarea_id" value="<?php print $subarea_id; ?>">
                                <button type="submit" id="mybutton">step 2</button>
                        </div>
                </div>
                </form>
<?php
}
?>
	</div>
<?php include("foot.php"); ?>
</div>
</body>
</html>