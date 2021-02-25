<?php
require_once('config.php');


$file = $_GET['file'];


if(!isset($file)) {
	$picturePNG = base64_decode($_POST['picturePNG']);


	touch("$picturesPath/lockFile");
	$lockFileHandle = fopen("$picturesPath/lockFile", 'r+');
	if($lockFileHandle === false || !flock($lockFileHandle, LOCK_EX) || !file_exists("$picturesPath/lockFile")) {
		echo 'success=false';
		exit;
	}


	$time = time();

	while(true) {
		$fileName = date('YmdHis', $time) . '.png';
		
		if(!file_exists("$picturesPath/$fileName")) break;
		
		$time++;
	}


	file_put_contents("$picturesPath/$fileName", $picturePNG);


	fclose($lockFileHandle);
	
	
	$isHTTPS = isset($_SERVER['HTTPS']) || $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https';

	$pictureURL = $isHTTPS ? 'https://' : 'http://';
	$pictureURL .= $_SERVER['SERVER_NAME'];
	$pictureURL .= $isHTTPS ? ($_SERVER['SERVER_PORT'] == '443' ? '' : ':' . $_SERVER['SERVER_PORT']) : ($_SERVER['SERVER_PORT'] == '80' ? '' : ':' . $_SERVER['SERVER_PORT']);
	$pictureURL .= $_SERVER['PHP_SELF'];
	$pictureURL .= '?file=' . $fileName;
	
	echo "success=true&pictureURL=" . urlencode($pictureURL);
	
} else {
	if(!preg_match('/\d{14}\.png/', $file)) exit;
	
	header('Content-Type: image/png');
	header('Content-Length: ' . filesize("$picturesPath/$file"));
	
	readfile("$picturesPath/$file");
}

?>