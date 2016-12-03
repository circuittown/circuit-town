<?php 
include("common.php");
include("head.php"); 
?>
<div id="main">
<?php include("h1.php"); ?>
<?php include("find.php"); ?>
	<div id="unknown">
		<h3>unknown area</h3><br>
		<div style="width:500px;">new areas are vetted thoroughly. do not be surprised if we email you and ask directions to an area added to this site, especially if it looks good.</div>
	</div>
	<div id="new-area-form">
	<form method="post" action="unknown-area2.php">
		<div id="area-f-left">
		country
		</div>
		<div id="area-f-right" style="margin-top:-15px;">
		<select name="country">
<?php
include("getcountry.php");
$ip = $_SERVER['REMOTE_ADDR'];
$coattempt = ip_info($ip, "Country");
if (empty($coattempt)) {
	$coattempt = "United States";
}
$cq = "select country, country_id from country order by country";
try {
        $cstmt = $db->prepare($cq);
        $result = $cstmt->execute();
} catch(PDOException $ex) {
        die("Failed to run query: " . $ex->getMessage());
}
while ($crow = $cstmt->fetch()) {

	if ($crow['country'] == $coattempt) {
		$selected = " selected";
		$guessedit = 1;
	} else {
		$selected = "";
	}
?>
			<option value="<?php print $crow['country_id']; ?>"<?php print $selected; ?>><?php print $crow['country']; ?></option>
<?php
}
?>
		</select>
		</div>
	<button type="submit" id="mybutton2" class="clearfix">step 2</button>
	</form>
	</div>
<?php include("foot.php"); ?>
</div>
</body>
</html>