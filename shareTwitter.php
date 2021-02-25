<?php
require_once('config.php');


$playerRanks = $_GET['playerRanks'];
$playerSelfIndex = $_GET['playerSelfIndex'];
$gameName = $_GET['gameName'];
$shareURL = $_GET['shareURL'];
$playerCustomShareText = $_GET['playerCustomShareText'];


$playerRanks = explode(',', $playerRanks);

$shareText = getTextBasedOnResult($playerRanks, $playerSelfIndex, $shareWinText, $shareDrawText, $shareRankText);

if($playerCustomShareText != null) $shareText = $playerCustomShareText;
$shareText = preg_replace('/#\[RANK\]/', $playerRanks[$playerSelfIndex] + 1, $shareText);
$shareText = preg_replace('/#\[GAMENAME\]/', $gameName, $shareText);

if(strlen($shareText) + strlen($shareURL) + 1 > 140) {
	$shareText = substr($shareText, 0, 140 - strlen($shareURL) - 1);
}
			
$shareText .= ' ' . $shareURL;


header("Location: http://twitter.com/home?status=" . urlencode($shareText));


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