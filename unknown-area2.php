<?php 
if (isset($_POST["country"])) {
	$country_id = $_POST["country"];
	session_start();
	$_SESSION['country_id'] = $country_id;
} else {
	header("location:index.php");
	exit;
}
include("common.php");
include("head.php"); 
?>
<script>
	function validateForm() {
		var x=document.forms["unknown3"]["areaname"].value;
		if (x==null || x=="") {
			alert("please enter a name.");
			document.forms["unknown3"]["areaname"].focus();
			return false;
		}	
	}
</script>
<div id="main">
<?php include("h1.php"); ?>
<?php include("find.php"); ?>
	<div id="unknown">
		<h3>unknown area</h3><br>
<?php
$cq = "select country from country where country_id = :country_id";
$cqparams = array(':country_id' => $country_id);
try {
        $cstmt = $db->prepare($cq);
        $result = $cstmt->execute($cqparams);
} catch(PDOException $ex) {
        die("Failed to run query: " . $ex->getMessage());
}
$crow = $cstmt->fetch();
$country = $crow['country'];
if ($country == "United States") {
	$country = "the United States";
}
?>
		<div style="width:500px;">ok let's add a new area in <?php print $country; ?></div>
	</div>
	<div style="margin-top:10px; margin-left:10px; width:400px;">
        please use the most common name for the area. if there is a main area for the area you're adding please add it first, or select the main area from the menu below:
        <form style="padding-top:10px;">
        <select name="subarea" onchange="window.location.href= this.form.subarea.options[this.form.subarea.selectedIndex].value">
                <option value="#">- MAIN AREA -</option>
<?php
$fq = "select area, area_id from areas where approved = 'yes' and country_id = :country_id order by TRIM(LEADING 'the ' FROM LOWER(`area`))";
$fqparams = array(':country_id' => $country_id);
try {
        $fstmt = $db->prepare($fq);
        $result = $fstmt->execute($fqparams);
} catch(PDOException $ex) {
        die("Failed to run query: " . $ex->getMessage());
}
while ($frow = $fstmt->fetch()) {
        $selectarea = stripslashes($frow['area']);
        $selectarea = strtolower($selectarea);
?>
                <option value="subarea.php?area_id=<?php print $frow['area_id']; ?>"><?php print $selectarea; ?></option>
<?php
}
?>
        </select>
	</form>
	<div id="new-area-form">
	<form method="post" action="unknown-area3.php" name="unknown3" onsubmit="return validateForm()">
		<div id="area-f-left">
		area name
		</div>
		<div id="area-f-right">
		<input type="text" name="areaname" class="areaname">
		<input type="hidden" name="country_id" value="<?php print $country_id; ?>">
		</div>
	<button type="submit" id="mybutton3" class="clearfix">step 3</button>
	</form>
	</div>
<?php include("foot.php"); ?>
</div>
</body>
</html>