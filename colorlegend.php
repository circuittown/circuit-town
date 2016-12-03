		<div id="colorlegend">
			<ul style="display:block;">
<?php
/*
mysql> describe colour;
+-----------+--------------+------+-----+---------+----------------+
| Field     | Type         | Null | Key | Default | Extra          |
+-----------+--------------+------+-----+---------+----------------+
| colour_id | int(11)      | NO   | PRI | NULL    | auto_increment |
| colour    | varchar(255) | NO   |     |         |                |
| adjective | varchar(255) | NO   |     | NULL    |                |
| english   | varchar(255) | NO   |     | NULL    |                |
+-----------+--------------+------+-----+---------+----------------+
4 rows in set (0.01 sec)
*/
	$cqq = "select colour, adjective, english from colour where colour != 'pink' order by colour_id";
	try {
	        $cqqstmt = $db->prepare($cqq);
	        $result = $cqqstmt->execute();
	} catch(PDOException $ex) {
	        die("Failed to run query: " . $ex->getMessage());
	}
	while ($cqqrow = $cqqstmt->fetch()) {	
?>
				<li style="padding:3px; display:inline; float:left;"><div style="display:inline-block; background-color:<?php print $cqqrow['colour']; ?>;">&nbsp;&nbsp;&nbsp;</div> <?php print strtolower($cqqrow['english']); ?></li>
<?php
	}
?>
			</ul>
			<div class="clearfix"></div>
			<span style="color:#ffffff; font-size:.9em;"><a href="javascript:void(0);" id="colorlgrade">show grade comparison</a></span>
			<div id="gradewhore">
				<div class="cwgs"><div style="display:inline-block; background-color:white;">&nbsp;&nbsp;&nbsp;</div> children don't need your dogma</div>
				<div class="cwgs"><div style="display:inline-block; background-color:yellow;">&nbsp;&nbsp;&nbsp;</div> VB - V0 &#8776; 2 - 4</div>
				<div class="cwgs"><div style="display:inline-block; background-color:orange;">&nbsp;&nbsp;&nbsp;</div> V0 - V1 &#8776; 4+ - 5</div>
				<div class="cwgs"><div style="display:inline-block; background-color:blue;">&nbsp;&nbsp;&nbsp;</div> V2 - V4 &#8776; 5+ - 6c</div>
				<div class="cwgs"><div style="display:inline-block; background-color:red;">&nbsp;&nbsp;&nbsp;</div> V5 - V7 &#8776; 6c+ - 7b</div>
				<div class="cwgs"><div style="display:inline-block; background-color:black;">&nbsp;&nbsp;&nbsp;</div> V8+ &#8776; &gt;7b+</div>
				<div class="cwgs"><div style="display:inline-block; background-color:deeppink;">&nbsp;&nbsp;&nbsp;</div> <i>you don't circuit pink</i></div>
			</div>
		</div>
<script>
$("#colorlgrade").click(function() {
	if ($('#gradewhore').is(":hidden")) {
		$("#gradewhore").show();
		$("#colorlgrade").text("i feel like a whore");
	} else {
		$("#gradewhore").hide();
		$("#colorlgrade").text("show grade comparison");
	}
});
</script>
