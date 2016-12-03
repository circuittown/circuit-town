<?php 
if (isset($_GET["area_id"])) {
	$area_id = $_GET["area_id"];
	session_start();
	$_SESSION['area_id'] = $area_id;
} else {
	header("location:http://google.com");
	exit;
}
include("common.php");
include("head.php"); 
?>
<script>
	function validateForm() {
		var x=document.forms["sub2"]["areaname"].value;
		if (x==null || x=="") {
			alert("please enter a name of a sub-area.");
			document.forms["sub2"]["areaname"].focus();
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
$cq = "select area from areas where area_id = :area_id";
$cqparams = array(':area_id' => $area_id);
try {
        $cstmt = $db->prepare($cq);
        $result = $cstmt->execute($cqparams);
} catch(PDOException $ex) {
        die("Failed to run query: " . $ex->getMessage());
}
$crow = $cstmt->fetch();
$area = strtolower($crow['area']);
$area = stripslashes($area);
?>
		<div style="width:500px;">ok let's add a new sub-area at <?php print $area; ?></div>
	</div>
	<div id="new-area-form">
	<form method="post" action="subarea2.php" name="sub2" onsubmit="return validateForm()">
		<div id="area-f-left">
		sub-area name
		</div>
		<div id="area-f-right">
		<input type="text" name="areaname" class="areaname">
		<input type="hidden" name="area_id" value="<?php print $area_id; ?>">
		</div>
	<button type="submit" id="mybutton3" class="clearfix">submit</button>
	</form>
	</div>
<?php include("foot.php"); ?>
</div>
</body>
</html>