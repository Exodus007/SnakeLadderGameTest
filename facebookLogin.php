<?php
require_once('config.php');
require_once('common.php');


if($shareFacebookAppID == null || $shareFacebookAppID == '' || $shareFacebookAppSecret == null || $shareFacebookAppSecret == '') {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Facebook Login</title>
<style type="text/css">
html { height: 100% }
body { display:table; font-family: Arial; font-weight: bold; color: #FF0000; background-color: #FFFFFF; margin: 0px; padding: 0px; width: 100%; height: 100% }
div { display:table-cell; text-align:center; vertical-align:middle }
</style>
</head>
<body>
<div>Error: facebookAppID and facebookAppSecret should be set</div>
</body>
</html>
<?php

exit;

}


$action = $_GET['action'];
if(!isset($action)) $action = $_POST['action'];


session_start();

if(!isset($_SESSION['novelgames_facebookState'])) $_SESSION['novelgames_facebookState'] = md5(uniqid(rand(), TRUE));


if($action == 'login') {
	$redirectURL = $isHTTPS ? 'https://' : 'http://';
	$redirectURL .= $_SERVER['SERVER_NAME'];
	$redirectURL .= $isHTTPS ? ($_SERVER['SERVER_PORT'] == '443' ? '' : ':' . $_SERVER['SERVER_PORT']) : ($_SERVER['SERVER_PORT'] == '80' ? '' : ':' . $_SERVER['SERVER_PORT']);
	$redirectURL .= $_SERVER['PHP_SELF'];
	$redirectURL .= '?action=code';
	
	$url = 'http://www.facebook.com/dialog/oauth';
	$url .= '?client_id=' . $shareFacebookAppID;
	$url .= '&redirect_uri=' . urlencode($redirectURL);
	$url .= '&response_type=code';
	$url .= '&display=popup';
	$url .= '&state=' . $_SESSION['novelgames_facebookState'];

	header("Location: $url");
	
} else if($action == 'code') {
	if($_GET['state'] != $_SESSION['novelgames_facebookState']) exit;
	
	$code = $_GET['code'];
	
	if($code != null && $code != '') {
		$redirectURL = $isHTTPS ? 'https://' : 'http://';
		$redirectURL .= $_SERVER['SERVER_NAME'];
		$redirectURL .= $isHTTPS ? ($_SERVER['SERVER_PORT'] == '443' ? '' : ':' . $_SERVER['SERVER_PORT']) : ($_SERVER['SERVER_PORT'] == '80' ? '' : ':' . $_SERVER['SERVER_PORT']);
		$redirectURL .= $_SERVER['PHP_SELF'];
		$redirectURL .= '?action=code';
		
		$url = 'https://graph.facebook.com/oauth/access_token';
		$url .= '?client_id=' . $shareFacebookAppID;
		$url .= '&client_secret=' . $shareFacebookAppSecret;
		$url .= '&code=' . $code;
		$url .= '&redirect_uri=' . urlencode($redirectURL);
		
		$response = file_get_contents($url);

		if($response != null && $response != '') {
			parse_str($response, $parameters);
			
			$accessToken = $parameters['access_token'];
			
			if($accessToken != null && $accessToken != '') {
				$url = 'https://graph.facebook.com/me?fields=id,name,picture,friends&access_token=' . $accessToken;
				$data = json_decode(file_get_contents($url));
				
				$_SESSION['novelgames_facebookUserID'] = $data->id;
				$_SESSION['novelgames_facebookUserName'] = $data->name;
				$_SESSION['novelgames_facebookUserPicture'] = $data->picture;
				$_SESSION['novelgames_facebookUserFriends'] = $data->friends->data;
			}
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Facebook Login</title>
</head>
<body onload="window.close()">
</body>
</html>
<?php

} else if($action == 'check') {
	$facebookUserID = $_SESSION['novelgames_facebookUserID'];
	$facebookUserName = $_SESSION['novelgames_facebookUserName'];
	$facebookUserPicture = $_SESSION['novelgames_facebookUserPicture'];
	$facebookUserFriends = $_SESSION['novelgames_facebookUserFriends'];
	
	unset($_SESSION['novelgames_facebookUserID']);
	unset($_SESSION['novelgames_facebookUserName']);
	unset($_SESSION['novelgames_facebookUserPicture']);
	unset($_SESSION['novelgames_facebookUserFriends']);
	
	header('Content-Type: text/xml');
	
	if($facebookUserID == null || $facebookUserID == '') {
		echo '<NOTLOGGEDIN />';
	} else {
		echo '<FACEBOOKUSERINFO>';
		
		echo '<ID>' . htmlspecialchars($facebookUserID) . '</ID>';
		echo '<NAME>' . htmlspecialchars($facebookUserName) . '</NAME>';
		echo '<PICTURE>' . htmlspecialchars($facebookUserPicture) . '</PICTURE>';
		
		$friendIDs = '';
		
		$countFacebookUserFriends = count($facebookUserFriends);
		
		for($i=0;$i<$countFacebookUserFriends;$i++) {
			if($i > 0) $friendIDs .= ',';
			
			$friendIDs .= $facebookUserFriends[$i]->id;
		}
		
		echo "<FRIENDIDS>$friendIDs</FRIENDIDS>";
		
		echo '</FACEBOOKUSERINFO>';
	}
}
?>