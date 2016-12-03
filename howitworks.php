<?php 
error_reporting(E_ALL & ~E_NOTICE);
ini_set( "display_errors", 1 );
include("common.php");
include("head.php"); 
?>
<div id="main">
<?php include("h1.php"); ?>
<?php include("find.php"); ?>
<div id="newshit">
	<h1 style="display:inline;">either</h1> &#8674; <h2 style="display:inline; cursor:pointer;" id="createnew">create a new circuit</h2>
	<div class="brbr"></div>
	<div style="margin-bottom:-40px;">circuits can be graded with one of 6 colors as seen below:</div>
<?php include("colorlegend.php"); ?>
	<div style="margin-top:20px; max-width:420px;">
		you will give each problem a par number (between 1 and 5) according to the problem's difficulty, relative to the circuit.<div style="margin-bottom:5px;"></div>
	</div>

	<div class="brbr"></div>

	<div style="background-color:#3F3830; padding:20px; max-width:400px; color:#ffffff; -moz-border-radius: 10px; border-radius: 10px; font-size:.9em;">
		<h4 class="parh4">par</h4> is the average number of attempts for a climber performing at the level of a given circuit's difficulty. "par for the course," as one might say. for example, problems 
		more suited to a <div style="display:inline-block; background-color:red; padding:1px; padding-right:3px; padding-left:2px;">red</div> or 
		<div style="display:inline-block; background-color:black; padding:1px; padding-right:3px; padding-left:2px;">black</div> circuit will have a higher par in 
		a <div style="display:inline-block; background-color:blue; padding:1px; padding-right:3px; padding-left:2px;">blue</div> circuit.
		<div class="brbr"></div>
		<i>each problem is given a default par of 2.</i><Br>
	</div>

	<div class="brbr clearfix"></div>

	<div style="margin-bottom:20px; max-width:420px;">adding "(optional)" to a problem will allow climbers to option out other problems on the circuit by climbing this one, or get a better score by adding this problem to the circuit.</div>

        <div><h1 style="display:inline;">or</h1> &#8674; <h2 style="display:inline; cursor:pointer;" id="startclimbing">start climbing circuits</h2></div>

	<div style="float:left; margin-top:20px; max-width:220px; margin-right:40px;">
		<h4>rules &amp; regs</h4>
		<div style="font-size:.9em;">
		<b>consider this example circuit:</b><br>
		similar to golf, if you climb "crap arete" in 1 try you score a 1 (-1), or a "birdie".<div style="margin-bottom:5px;"></div>
		<b>if you cannot do a problem your score on that problem is two over par.</b> therefore, you would only try "crap arete" a 4th try out of pride or desperation.<div style="margin-bottom:5px;"></div>
		at the end you would add up your score for all problems, and get your total score.<div style="margin-bottom:5px;"></div>
		problems marked "(optional)" can take the place of any other problem on the circuit, or can be climbed in addition to the other problems for a better total score.
		</div>
	</div>
	<div style="float:left; margin-top:20px;">
		<div style="width:12px; height:12px; background-color:red; display:inline-block;"></div> <h4 style="display:inline;">bob's warmup circuit</h4><Br>
		<ol style="margin-left:20px;">
			<li>crap arete <div style="float:right;">par 2</div></li>
			<li>what's left of less <div style="float:right;">par 1</div></li>
			<li>left el murray <div style="float:right;">par 2</div></li>
			<li>center el murray <div style="float:right;">par 2</div></li>
			<li>right el murray <div style="float:right;">par 3</div></li>
			<li>mushroom roof <div style="float:right;">par 3</div></li>
			<li style="list-style-type:lower-alpha;" value="15">hueco in her head (optional) <div style="padding-left:20px; float:right;">par 4</div></li>
			<li style="list-style-type:none; padding-top:5px;">
				<div style="float:right;"><b>total par without options: 13</b></div><br>
				<div style="float:right;"><b>total par with options: 17</b></div>
			</li>
		</ol>
	</div>
	<div class="clearfix" style="margin-bottom:20px;"></div>
</div>
<?php include("foot.php"); ?>
<script>
$('#createnew').on('mouseover', function() {
	$("#add").css("background-color", "red");
});
$('#createnew').on('mouseout', function() {
        $("#add").css("background-color", "#3D3831");
});
$('#createnew').on('click', function() {
	window.location = "newcircuit.php";
});
$('#startclimbing').on('mouseover', function() {
        $("#finderselect").css("background-color", "red");
});
$('#startclimbing').on('mouseout', function() {
        $("#finderselect").css("background-color", "");
});
$('#startclimbing').on('click', function(event) {
	window.location = "index.php";
});
</script>
</body>
</html>