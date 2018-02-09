<?php
require_once("common.php");
require_once('common_lib.php');
    $areas = ctownapi('GET', "{$ctownapi_host}/api/getAreas");

foreach ($areas as $foo => $bar) {
?>
      <option value="circuit.php?area_id=<?php print $bar['area_id']; ?>"><?php print $bar['area']; ?></option>
<?php
}
?>
