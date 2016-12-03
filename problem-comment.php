<?php 
include("common.php");
session_start();
if (!isset($_SESSION["usermast"])) {
        header("location:login.php");
        exit;
} else {
        $timeout = 60 * 30; // In seconds, i.e. 30 minutes.
        $fingerprint = md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
        if (    (isset($_SESSION['last_active']) && $_SESSION['last_active']<(time()-$timeout))
             || (isset($_SESSION['fingerprint']) && $_SESSION['fingerprint']!=$fingerprint)
             || isset($_GET['logout'])
            ) {
            setcookie(session_name(), '', time()-3600, '/');
            session_destroy();
        }
        session_regenerate_id();
        $_SESSION['last_active'] = time();
        $_SESSION['fingerprint'] = $fingerprint;
	if (!empty($_POST["comment"])) {
		$auq = "select allowed_users.user_mast_id, user_mast.handle from allowed_users inner join user_mast on allowed_users.user_mast_id=user_mast.user_mast_id where allowed_users.circuit_id = :circuit_id"; 
		$auqparams = array(':circuit_id' => $_POST["circuit_id"]);
		try {
			$austmt = $db->prepare($auq);
			$result = $austmt->execute($auqparams);
		} catch(PDOException $ex) {
			die("Failed to run query: " . $ex->getMessage());
		}
		while ($aurow = $austmt->fetch()) {
			$authusers[] = $aurow['user_mast_id'];
			$authhandles[] = $aurow['handle'];
		}
		if (in_array($_SESSION['user_id'], $authusers)) {
			if (!empty($_POST["circuit_id"])) { $circuit_id = $_POST["circuit_id"]; } else { $error = 1; }
                        if (!empty($_POST["problem"])) { $problem = $_POST["problem"]; } else { $error = 1; }
                        $user_mast_id = $_SESSION['user_id'];
                        $right_now = date('Y-m-d H:i:s');
                        if (!empty($_POST["comment"])) { $comment = $_POST["comment"]; } else { $error = 1; }
                        if (!isset($error)) {
                                $insert = "insert into problem_comments (circuit_id, problem, user_mast_id, right_now, comment) values (:circuit_id, :problem, :user_mast_id, :right_now, :comment)";
                                $insertparams = array(':circuit_id' => $circuit_id, ':problem' => $problem, ':user_mast_id' => $user_mast_id, ':right_now' => $right_now, ':comment' => $comment);
                                try {
                                        $stmt = $db->prepare($insert);
                                        $result = $stmt->execute($insertparams);
                                } catch(PDOException $ex) {
                                        die("Failed to run query: " . $ex->getMessage());
                                }
                                header("location: update-circuit.php?circuit_id=" . $circuit_id);
                                exit;
                        }
		}
	}
	include("head.php");
?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
<script>
$(document).ready(function() {

});
</script>
<div id="main">
<?php include("h1.php"); ?>
<?php include("find.php"); ?>
        <div id="newshit">
		<h3 id="mydear">update circuit</h3>
<?php
	if(isset($_POST["circuit_id"])) {
		$circuit_id = $_POST["circuit_id"];
		$cq = "select circuit_id, circuit, area_id, colour, is_subarea, user_mast_id from circuit where circuit_id = :circuit_id";
		$cqparams = array(':circuit_id' => $circuit_id);
		try {
                        $cstmt = $db->prepare($cq);
                        $result = $cstmt->execute($cqparams);
                } catch(PDOException $ex) {
                        die("Failed to run query: " . $ex->getMessage());
                }
                $crow = $cstmt->fetch();
		$ownerid = $crow['user_mast_id'];
		$oq = "select handle from user_mast where user_mast_id = :owner";
		$oqparams = array(':owner' => $ownerid);
		try {
                        $ostmt = $db->prepare($oq);
                        $result = $ostmt->execute($oqparams);
                } catch(PDOException $ex) {
                        die("Failed to run query: " . $ex->getMessage());
                }
		$orow = $ostmt->fetch();
		$owner = $orow['handle'];
		if ($crow['is_subarea'] == "yes") {
                        $subarea_id = $crow['area_id'];
                        $aq = "select subarea, area_id from subareas where subarea_id = :subarea_id";
                        $aqparams = array(':subarea_id' => $subarea_id);
                        try {
                                $aqstmt = $db->prepare($aq);
                                $result = $aqstmt->execute($aqparams);
                        } catch(PDOException $ex) {
                                die("Failed to run query: " . $ex->getMessage());
                        }
                        $arow = $aqstmt->fetch();
                        $subarea = strtolower($arow['subarea']);
                        $area_id = $arow['area_id'];
                        $aq = "select area from areas where area_id = :area_id";
                        $aqparams = array(':area_id' => $area_id);
                        try {
                                $aqstmt = $db->prepare($aq);
                                $result = $aqstmt->execute($aqparams);
                        } catch(PDOException $ex) {
                                die("Failed to run query: " . $ex->getMessage());
                        }
                        $arow = $aqstmt->fetch();
                        $area = strtolower($arow['area']) . ", " . $subarea;
                } else {
                        $area_id = $crow['area_id'];
                        $aq = "select area from areas where area_id = :area_id";
                        $aqparams = array(':area_id' => $area_id);
                        try {
                                $aqstmt = $db->prepare($aq);
                                $result = $aqstmt->execute($aqparams);
                        } catch(PDOException $ex) {
                                die("Failed to run query: " . $ex->getMessage());
                        }
                        $arow = $aqstmt->fetch();
                        $area = strtolower($arow['area']);
                }
		$colour = $crow['colour'];
		$auq = "select allowed_users.user_mast_id, user_mast.handle from allowed_users inner join user_mast on allowed_users.user_mast_id=user_mast.user_mast_id where allowed_users.circuit_id = :circuit_id";
		$auqparams = array(':circuit_id' => $circuit_id);
		try {
                        $austmt = $db->prepare($auq);
                        $result = $austmt->execute($auqparams);
                } catch(PDOException $ex) {
                        die("Failed to run query: " . $ex->getMessage());
                }
		while ($aurow = $austmt->fetch()) {
			$authusers[] = $aurow['user_mast_id'];
			$authhandles[] = $aurow['handle'];
		}
		if (in_array($_SESSION['user_id'], $authusers)) {
			
?>
		<script>
			$('#mydear').append(': <?php print $crow['circuit']; ?>').show();
		</script>
		<strong>located at: <?php print $area; ?></strong><div class="brbr"></div>

	        <form method="post" action="problem-comment.php" id="newlist">
		<div style="width:auto; float:left;">
			<h4 style="clear:both;"><?php print $_POST["problem"]; ?></h4>
			<div style="margin-top:145px;"><textarea name="comment" placeholder="comment on <?php print $_POST["problem"]; ?>" style="font-size:.9em;"></textarea></div>
		</div>
		<div id="ncrightlist">
	                <div class="logincolorthing">
<?php
	                $coq = "select adjective, english from colour where colour = :colour";
	                $coqparams = array(':colour' => $colour);
	                try {
	                        $costmt = $db->prepare($coq);
	                        $result = $costmt->execute($coqparams);
	                } catch(PDOException $ex) {
	                        die("Failed to run query: " . $ex->getMessage());
	                }
	                $corow = $costmt->fetch();
?>
	                        <div style="height:25px;">
	                                <div id="colour" style="background-color:<?php print $colour; ?>;">
	                                <?php print strtolower($corow['adjective']); ?>
	                                </div>
	                                <div id="cadj">this circuit is <?php print lcfirst($colour); ?> for <?php print lcfirst($corow['english']); ?></div>
	                        </div>
	                </div>
			<div style="margin-top:10px; width:100%; text-align:right; margin-left:-10px;"><a href="update-circuit.php?circuit_id=<?php print $circuit_id; ?>" class="register">back to edit</a></div>
			<div style="margin-top:1px; width:100%; text-align:right; margin-left:-10px;"><a href="circuit_detail.php?circuit_id=<?php print $circuit_id; ?>" class="register">view circuit</a></div>
		</div>
		<input type="hidden" name="problem" value="<?php print $_POST["problem"]; ?>">
	        <input type="hidden" name="circuit_id" value="<?php print $_POST["circuit_id"]; ?>">		
        </div>
        <div class="clearfix"></div>
        <button type="submit" id="mybutton" style="width:auto; float:left; padding-right:5px; padding-left:5px; margin-top:20px;">post comment</button>
	</form>
        <div class="clearfix"></div>
<?php 
		}
	}
	include("foot.php"); ?>
</div>
</body>
</html>
<?php
}
?>