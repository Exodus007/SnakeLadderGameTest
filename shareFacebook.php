<?php
require_once('config.php');


$action = $_GET['action'];
$playerRanks = $_GET['playerRanks'];
$playerSelfIndex = $_GET['playerSelfIndex'];
$gameName = $_GET['gameName'];
$gameDescription = $_GET['gameDescription'];
$gamePictureURL = $_GET['gamePictureURL'];
$shareURL = $_GET['shareURL'];
$playerCustomShareText = $_GET['playerCustomShareText'];


$playerRanks = explode(',', $playerRanks);

$shareText = getTextBasedOnResult($playerRanks, $playerSelfIndex, $shareWinText, $shareDrawText, $shareRankText);


if($action == 'share') {
	if($playerCustomShareText != null) $shareText = $playerCustomShareText;
	$shareText = preg_replace('/#\[RANK\]/', $playerRanks[$playerSelfIndex] + 1, $shareText);
	$shareText = preg_replace('/#\[GAMENAME\]/', $gameName, $shareText);

	if($shareFacebookAppID != null && $shareFacebookAppID != '') {
		session_start();
		
		$_SESSION['novelgames_playerRanks'] = implode(',', $playerRanks);
		$_SESSION['novelgames_playerSelfIndex'] = $playerSelfIndex;
		$_SESSION['novelgames_gameName'] = $gameName;
		$_SESSION['novelgames_gameDescription'] = $gameDescription;
		$_SESSION['novelgames_gamePictureURL'] = $gamePictureURL;
		$_SESSION['novelgames_shareURL'] = $shareURL;
		
		$isHTTPS = isset($_SERVER['HTTPS']) || $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https';
		
		$redirectURL = $isHTTPS ? 'https://' : 'http://';
		$redirectURL .= $_SERVER['SERVER_NAME'];
		$redirectURL .= $isHTTPS ? ($_SERVER['SERVER_PORT'] == '443' ? '' : ':' . $_SERVER['SERVER_PORT']) : ($_SERVER['SERVER_PORT'] == '80' ? '' : ':' . $_SERVER['SERVER_PORT']);
		$redirectURL .= $_SERVER['REQUEST_URI'];
		
		$redirectURL = preg_replace('/action=[a-z]+/', 'action=challenge', $redirectURL);
		
		$url = 'http://www.facebook.com/dialog/feed';
		$url .= '?app_id=' . $shareFacebookAppID;
		$url .= '&redirect_uri=' . urlencode($redirectURL);
		$url .= '&picture=' . urlencode($gamePictureURL);
		$url .= '&display=popup';
		$url .= '&link=' . urlencode($shareURL);
		$url .= '&name=' . urlencode($shareText);
		$url .= '&caption=' . urlencode($gameName);
		$url .= '&description=' . urlencode($gameDescription);
		$url .= '&actions=' . urlencode('{"name":"' . $shareFacebookActionText . '","link":"' . $shareURL . '"}');
	} else {
		$url = 'http://www.facebook.com/sharer.php';
		$url .= '?t=' . urlencode($shareText);
		$url .= '&u=' . urlencode($shareURL);
	}

	header("Location: $url");
	
} else if($action == 'challenge' && $shareFacebookEnableChallenge) {
	session_start();
	
	$playerRanks = explode(',', $_SESSION['novelgames_playerRanks']);
	$playerSelfIndex = $_SESSION['novelgames_playerSelfIndex'];
	$gameName = $_SESSION['novelgames_gameName'];
	$gameDescription = $_SESSION['novelgames_gameDescription'];
	$gamePictureURL = $_SESSION['novelgames_gamePictureURL'];
	$shareURL = $_SESSION['novelgames_shareURL'];
		
	$isHTTPS = isset($_SERVER['HTTPS']) || $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https';
	
	$redirectURL = $isHTTPS ? 'https://' : 'http://';
	$redirectURL .= $_SERVER['SERVER_NAME'];
	$redirectURL .= $isHTTPS ? ($_SERVER['SERVER_PORT'] == '443' ? '' : ':' . $_SERVER['SERVER_PORT']) : ($_SERVER['SERVER_PORT'] == '80' ? '' : ':' . $_SERVER['SERVER_PORT']);
	$redirectURL .= $_SERVER['REQUEST_URI'];
	
	$redirectURL = preg_replace('/action=[a-z]+/', 'action=close', $redirectURL);
	
	$shareFacebookRequestText = getTextBasedOnResult($playerRanks, $playerSelfIndex, $shareFacebookRequestWinText, $shareFacebookRequestDrawText, $shareFacebookRequestRankText);

	$shareFacebookRequestText = preg_replace('/#\[RANK\]/', $playerRanks[$playerSelfIndex] + 1, $shareFacebookRequestText);
	$shareFacebookRequestText = preg_replace('/#\[GAMENAME\]/', $gameName, $shareFacebookRequestText);
	
	$url = 'http://www.facebook.com/dialog/apprequests';
	$url .= '?app_id=' . $shareFacebookAppID;
	$url .= '&redirect_uri=' . urlencode($redirectURL);
	$url .= '&display=popup';
	$url .= '&title=' . urlencode($shareFacebookRequestTitle);
	$url .= '&message=' . urlencode($shareFacebookRequestText);
	$url .= '&data=' . urlencode('{"shareURL":"' . $shareURL . '"}');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Facebook Share</title>
<style type="text/css">
html { height: 100% }
body { font-family: Arial; background-color: #FFFFFF; margin: 0px; padding: 0px; height: 100% }
.main { position:relative; top: 50%; margin: -150px auto 0px; border:1px solid #CCCCCC; width:300px; height:300px }
h1 { color: #1C2A47; font-size: 18px; margin-top: 120px; text-align: center }
.buttons { position:absolute; left:-1px; bottom:-1px; border: 1px solid #CCCCCC; width: 300px; height: 32px; background-color: #F2F2F2; text-align: center; padding-top: 8px}
a { font-size: 12px; font-weight: bold; text-decoration: none; padding: 5px }
.challenge { background-color: #5E77AA; color: #FFFFFF; border: 1px solid #29447E; margin-right: 5px }
.close { background-color: #ECECEC; color: #333333; border: 1px solid #999999 }
</style>
</head>
<body>
<div class="main">
	<h1><?php echo $shareFacebookThanksText?></h1>
	<div class="buttons">
		<a class="challenge" href="<?php echo $url; ?>"><?php echo $shareFacebookChallengeText?></a>
		<a class="close" href="javascript:window.close()"><?php echo $shareFacebookCloseText?></a>
	</div>
</div>
</body>
</html>
<?php
} else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Facebook Share</title>
</head>
<body onload="window.close()">
</body>
</html>
<?php
}


function getTextBasedOnResult($ranks, $playerIndex, $winText, $drawText, $rankText) {
	$countRanks = count($ranks);
	
	$isDraw = true;
	
	for($i=0;$i<$countRanks;$i++) {
		if($ranks[$i] != 0) {
			$isDraw = false;
			break;
		}
	}
	
	if($isDraw) return $drawText;
	
	if($ranks[$playerIndex] != 0) return $rankText;
	
	for($i=0;$i<$countRanks;$i++) {
		if($i == $playerIndex) continue;
		if($ranks[$i] == 0) return $rankText;
	}
	
	return $winText;
}
?>