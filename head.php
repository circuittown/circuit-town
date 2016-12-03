<?php
$sitename = "circuit.town - bouldering circuits";
if(session_id() == '') {
	session_start();
}
function email2handle($email) {
	$atpos = strpos($email, "@");
	$handle = substr($email, 0, $atpos);
	return $handle;
}
if (isset($_SESSION['logged_in_oh_yeah'])) {
	if ($_SESSION['logged_in_oh_yeah'] == "perhaps") {
		$username = $_SESSION['usermast'];
		$user_mast_id = $_SESSION['user_id'];
		$hq = "select handle from user_mast where user_mast_id = :user_mast_id";
		$hqparams = array(':user_mast_id' => $user_mast_id);
		try {
                        $stmt = $db->prepare($hq);
                        $result = $stmt->execute($hqparams);
                } catch(PDOException $ex) {
                        die("Failed to run query: " . $ex->getMessage());
                }
                $row = $stmt->fetch();
		$userhandle = $row['handle'];
		$title = $sitename . " - " . $userhandle;
		$user_bool = 1;
	}
}
if (isset($user_bool)) {
        $title = $title;
} else {
        $title = $sitename;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php print $title; ?></title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<!--[if IE]>
	<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	<!--[if IE 7]>
	<link rel="stylesheet" href="ie7.css" type="text/css" media="screen">
	<![endif]-->
	<link rel="stylesheet" href="style.css" type="text/css" media="screen">
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
	<link rel="icon" href="favicon.ico" type="image/x-icon">
	<link rel="image_src" href="circuit_town.png">
	<script src="http://code.jquery.com/jquery-1.10.2.js"></script>
	<script src="http://code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
	<script src="js/jquery.cookie.js" type="text/javascript"></script>
</head>
<?php 
$mypage = $_SERVER['PHP_SELF'];
$mypage = substr(strrchr($mypage, '/'), 1);
?>
<body>
