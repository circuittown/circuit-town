	<div id="find">
		<h2>areas</h2>
		<form name="none" action="#" style="padding-top:7px;">
		<select name="URL" onchange="window.location.href= this.form.URL.options[this.form.URL.selectedIndex].value" id="finderselect">
			<option value="#">- select an area -</option>
<?php
include("finder.php");
?>
		</select>
		</form>
		<div id="add" onclick="location.href='newcircuit.php';"><strong><a href="newcircuit.php" class="adding">+</a></strong></div>
	</div>
