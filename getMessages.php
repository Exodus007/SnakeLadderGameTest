<?php
require_once('config.php');
require_once('common.php');
require_once('Room.php');
require_once('Player.php');
require_once('Table.php');


$parameters = parseParameters();

if($parameters == null) {
	echo '<DISCONNECTED />';
	exit;
}


$siteID = $parameters['siteID'];
$gameID = $parameters['gameID'];
$roomID = $parameters['roomID'];
$playerUID = $parameters['playerUID'];

$messageID = $parameters['messageID'];


if(!isset($siteID) || !isset($gameID) || !isset($roomID) || !isset($playerUID)) {
	echo '<INVALIDINPUT />';
	exit;
}


$room = new Room($siteID, $gameID, $roomID);

$room->lock();
$room->loadInfo();

$player = $room->getPlayer($playerUID);

if($player == null) {
	echo '<DISCONNECTED />';

	$room->unlock();

	exit;
}

$player->lockData();
$player->updateLastActivityTime();
$player->saveInfo();
$player->unlockData();

$room->removeInactivePlayers();

$room->unlock();

		
for($i=0;$i<$inactiveTimeout/3;$i++) {
	if($player->lockMessages()) {
		$messages = $player->getMessages($messageID);
		$player->unlockMessages();
	} else {
		echo '<DISCONNECTED />';
		exit;
	}
	
	if(count($messages) == 0) {
		sleep(1);
		continue;
	}
	
	break;
}

if(count($messages) == 0) {
	echo ' ';
} else {
	echo implode('', $messages);
}

?>