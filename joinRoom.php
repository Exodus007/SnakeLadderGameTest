<?php
require_once('config.php');
require_once('common.php');
require_once('Room.php');
require_once('Player.php');
require_once('Table.php');


$parameters = parseParameters();


$siteID = $parameters['siteID'];
$gameID = $parameters['gameID'];
$roomID = $parameters['roomID'];

$playerID = $parameters['playerID'];
$playerName = $parameters['playerName'];
$playerEmail = $parameters['playerEmail'];
$playerUsername = $parameters['playerUsername'];
$playerPassword = $parameters['playerPassword'];
$playerPictureURL = $parameters['playerPictureURL'];
$playerFacebookUserID = $parameters['playerFacebookUserID'];


if(!isset($siteID) || !isset($gameID)) {
	echo '<JOINROOMRESULT success="false" />';
	exit;
}

if(!isset($roomID)) $roomID = 1;


if(isset($playerUsername) && !isset($playerID)) {
	$result = checkLogin($playerUsername, $playerPassword);
	
	if($result == null) {
		echo '<JOINROOMRESULT success="false" reason="loginFailed" />';
		exit;
	}
	
	$playerID = $result->playerID;
	$playerName = $result->playerName;
	$playerPictureURL = $result->playerPictureURL;
	$playerFacebookUserID = $result->playerFacebookUserID;
}


if(file_exists('obscene.txt')) {
	$nameMerged = preg_replace('/\s+/', '', $playerName);
	$obsceneWords = file('obscene.txt');
	$obsceneWordsCount = count($obsceneWords);

	for($i=0;$i<$obsceneWordsCount;$i++) {
		$obsceneWord = trim($obsceneWords[$i]);
		if($obsceneWord == '') continue;
		
		if(stristr($nameMerged, trim($obsceneWords[$i])) !== FALSE) {
			echo '<JOINROOMRESULT success="false" reason="nameRejected" />';
			exit;
		}
	}
}


$room = new Room($siteID, $gameID, $roomID);

$room->lock();

$room->loadInfo();
$room->removeInactivePlayers();

if($room->checkIsFull()) {
	$room->unlock();
	
	echo '<JOINROOMRESULT success="false" reason="roomFull" />';
	exit;
}

$player = $room->playerJoined($playerName, $playerID, $playerEmail, $playerUsername, $playerPictureURL, $playerFacebookUserID);

$room->unlock();


if($player == null) {
	echo '<JOINROOMRESULT success="false" />';
	
} else {
	echo '<JOINROOMRESULT success="true" playerUID="' . $player->playerUID . '" playerNameInput="' . htmlspecialchars($playerName) . '" playerName="' . htmlspecialchars($player->playerName) . '" extraKey="' . $player->extraKey . '" playerID="' . (isset($playerID) && $playerID != null ? htmlspecialchars($playerID) : '') . '" playerPictureURL="' . (isset($playerPictureURL) && $playerPictureURL != null ? htmlspecialchars($playerPictureURL) : '') . '" playerFacebookUserID="' . (isset($playerFacebookUserID) && $playerFacebookUserID != null ? htmlspecialchars($playerFacebookUserID) : '') . '">';

	for($i=0;$i<count($room->players);$i++) {
		echo '<PLAYERINFO playerUID="' . $room->players[$i]->playerUID . '" playerName="' . htmlspecialchars($room->players[$i]->playerName) . '" playerPictureURL="' . (isset($room->players[$i]->playerPictureURL) && $room->players[$i]->playerPictureURL != null ? htmlspecialchars($room->players[$i]->playerPictureURL) : '') . '" playerFacebookUserID="' . (isset($room->players[$i]->playerFacebookUserID) && $room->players[$i]->playerFacebookUserID != null ? htmlspecialchars($room->players[$i]->playerFacebookUserID) : '') . '" />';
	}

	for($i=0;$i<count($room->tables);$i++) {
		echo '<TABLEINFO tableUID="' . $room->tables[$i]->tableUID . '" possibleNoOfPlayers="' . implode(',', $room->tables[$i]->possibleNoOfPlayers) . '" playerUIDs="' . implode(',', $room->tables[$i]->playerUIDs) . '" viewerUIDs="' . implode(',', $room->tables[$i]->viewerUIDs) . '" isPlaying="' . ($room->tables[$i]->isPlaying ? 'true' : 'false') . '" ' . ($room->tables[$i]->tableID == null ? '' : 'tableID="' . $room->tables[$i]->tableID . '"') . '/>';
	}

	echo '</JOINROOMRESULT>';
}
?>