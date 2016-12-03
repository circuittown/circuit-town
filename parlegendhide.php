
<div id="parlegend" style="font-size:.9em;">
<h4 class="parh4">par</h4> is the average number of attempts for a climber performing at the level of a given circuit's difficulty. "par for the course," as one might say. for example, problems more suited to a <div style="display:inline-block; background-color:red; padding:1px; padding-right:3px; padding-left:2px;">red</div> or <div style="display:inline-block; background-color:black; padding:1px; padding-right:3px; padding-left:2px;">black</div> circuit will have a higher par in a <div style="display:inline-block; background-color:blue; padding:1px; padding-right:3px; padding-left:2px;">blue</div> circuit.
<div class="brbr"></div>
<i>each problem is given a default par of 2.</i><Br>
<span id="dontshowparholder" style="font-size:.9em;"><a href="javascript:void(0);" id="dontshowpar">don't show this friendly helper anymore</a></span>
</div>
<script>
var cookieValue = $.cookie("dontshowpar");
if (cookieValue == "vagina") {
	$("#parlegend").hide();
}
$("#dontshowpar").click(function() {
	$.cookie('dontshowpar', 'vagina', { expires: 7300 });
	document.location.reload();
});
</script>
<div style="padding:10px;">&nbsp;</div>