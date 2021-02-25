<?php
require_once('config.php');
require_once('common.php');
require_once('Room.php');
require_once('Player.php');
require_once('Table.php');


$username = $_POST['username'];
$password = $_POST['password'];


if($username == $controlUsername && $password == $controlPassword) {
	deleteDirectory($filesPath, true);
	
	$success = true;
	
} else {
	$success = false;
}


function deleteDirectory($directoryPath, $retainThisDirectory) {
	$directory = opendir($directoryPath);

	while(($file = readdir($directory)) !== false) {
		if($file == '.' || $file == '..') continue;
		
		$filePath = "$directoryPath/$file";
		
		if(is_file($filePath)) {
			$file = fopen($filePath, 'r+');
			flock($file, LOCK_EX);
			unlink($filePath);
		} else if(is_dir($filePath)) {
			deleteDirectory($filePath, false);
		}
	}

	closedir($directory);
	
	if(!$retainThisDirectory) rmdir($directoryPath);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Novel Games Lobby Control Panel</title>
<style type="text/css">
<!--

body { 
	background-color:#FFFFFF;
	font-family: Arial;
	font-size: 12px;
	color: #333333;
}

div {
	width: 300px;
	height: 60px;
	margin-left: auto;
	margin-right:auto;
	margin-top:100px;
	border: 1px solid #333333;
	text-align: center;
	padding-top:40px;
}

-->
</style>
</head>
<body>
	<div><?php echo ($success ? 'Lobby Restarted' : 'Authentication Failed')?></div>
</body>
</html>