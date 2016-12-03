<?php
require("common.php");
$fq = "select area, area_id from areas where approved = 'yes' order by TRIM(LEADING 'the ' FROM LOWER(`area`))";
try {
	$fstmt = $db->prepare($fq);
	$result = $fstmt->execute();
} catch(PDOException $ex) {
	die("Failed to run query: " . $ex->getMessage());
}
while ($frow = $fstmt->fetch()) {
        $selectarea = stripslashes($frow['area']);
        $selectarea = strtolower($selectarea);
?>
      <option value="circuit.php?area_id=<?php print $frow['area_id']; ?>"><?php print strtolower($selectarea); ?></option>
<?php
}
?>