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


if(!isset($siteID) || !isset($gameID) || !isset($roomID) || !isset($playerUID)) {
	echo '<INVALIDINPUT />';
	exit;
}


$shouldLockRoom = false;

$messages = array();

for($i=0;true;$i++) {
	if(!isset($parameters["message$i"])) break;
	
	$message = $parameters["message$i"];
	
	parse_str($message, $message);
	
	if(get_magic_quotes_gpc()) {
		foreach($message as $key => $value) {
			$message[$key] = stripslashes($value);
		}
	}
	
	switch($message['type']) {
		case 'leaveRoom':
		case 'openTable':
		case 'joinTable':
		case 'leaveTable':
		case 'invite':
		case 'robotJoinTable':
		case 'startPlaying':
			$shouldLockRoom = true;
	}
	
	$messages[] = $message;
}


if($shouldLockRoom) {
	$room = new Room($siteID, $gameID, $roomID);

	$room->lock();
	$room->loadInfo();

	$player = $room->getPlayer($playerUID);

	if($player == null || !$player->lockData()) {
		echo '<DISCONNECTED />';
		$room->unlock();
		exit;
	}
} else {
	$player = new Player($siteID, $gameID, $roomID, $playerUID);

	if(!$player->lockData()) {
		echo '<DISCONNECTED />';
		exit;
	}
	
	$player->loadInfo();
}

$player->updateLastActivityTime();
$player->saveInfo();


foreach($messages as $message) {
	if($message['messageID'] <= $player->lastProcessedMessageID) continue;
	
	switch($message['type']) {
		case 'leaveRoom':
			$player->lastActivityTime = 0;
			break;
			
		case 'openTable':
			$table = $room->openTable($player, explode(',', $message['possibleNoOfPlayers']), $message['tableID']);

			if($table != null) {
				$player->sendMessage('<OPENTABLERESULT success="true" tableUID="' . $table->tableUID . '" />');
			} else {
				$player->sendMessage('<OPENTABLERESULT success="false" />');
			}
			break;
			
		case 'joinTable':
			$success = $room->joinTable($player, $message['tableUID']);

			if($success) {
				$player->sendMessage('<JOINTABLERESULT success="true" />');
			} else {
				$player->sendMessage('<JOINTABLERESULT success="false" />');
			}
			break;
			
		case 'leaveTable':
			$success = $room->leaveTable($player, $message['tableUID']);

			if($success) {
				$player->sendMessage('<LEAVETABLERESULT tableUID="' . $message['tableUID'] . '" success="true" />');
			} else {
				$player->sendMessage('<LEAVETABLERESULT tableUID="' . $message['tableUID'] . '" success="false" />');
			}
			break;
			
		case 'invite':
			$success = $room->playerInvitesPlayer($player, $message['invitedPlayerUID']);

			if($success) {
				$player->sendMessage('<INVITERESULT success="true" />');
			} else {
				$player->sendMessage('<INVITERESULT success="false" />');
			}
			break;
		
		case 'robotJoinTable':
			$success = $room->robotJoinTable($message['tableUID']);

			if($success) {
				$player->sendMessage('<ROBOTJOINTABLERESULT success="true" />');
			} else {
				$player->sendMessage('<ROBOTJOINTABLERESULT success="false" />');
			}
			break;
			
		case 'startPlaying':
			$success = $room->startPlaying($player, $message['tableUID']);

			$player->sendMessage('<STARTPLAYINGRESULT success="' . ($success ? 'true' : 'false') . '" />');
			
			break;
			
		case 'sendGameMessage':
			$table = new Table($siteID, $gameID, $roomID, $message['tableUID']);

			$table->lockData();
			$table->loadInfo();

			$table->sendGameMessage($message['playIndex'], $message['message'], $playerUID);

			$table->unlockData();
			
			break;
			
		case 'updateTime':
			$table = new Table($siteID, $gameID, $roomID, $message['tableUID']);

			$table->lockData();
			$table->loadInfo();

			$table->sendUpdateTimeMessage($message['playIndex'], $message['playerIndex'], $message['time'], $playerUID);

			$table->unlockData();
			
			break;
			
		case 'sendChatMessage':
			$table = new Table($siteID, $gameID, $roomID, $message['tableUID']);

			$table->lockData();
			$table->loadInfo();

			$table->sendChatMessage($message['message'], $playerUID);

			$table->unlockData();
			
			break;
			
		case 'gameEnded':
			$table = new Table($siteID, $gameID, $roomID, $message['tableUID']);

			if($table->lockData()) {
				$table->loadInfo();
				$table->unlockData();
				
				for($i=0;$i<count($table->playingPlayerInfos);$i++) {
					$playerNames[] = $table->playingPlayerInfos[$i]->playerName;
					$playerEmails[] = $table->playingPlayerInfos[$i]->playerEmail;
					$playerIDs[] = $table->playingPlayerInfos[$i]->playerID;
					$playerUsernames[] = $table->playingPlayerInfos[$i]->playerUsername;
					$playerFacebookUserIDs[] = $table->playingPlayerInfos[$i]->playerFacebookUserID;
					
					if($table->playingPlayerInfos[$i]->playerUID == $playerUID) $playerIndex = $i;
				}
				
				recordResult($siteID, $gameID, $roomID, $message['tableUID'], $table->tableID, $message['playIndex'], $playerNames, $playerEmails, $playerIDs, $playerUsernames, $playerFacebookUserIDs, explode(',', $message['ranks']), $playerIndex, $message['customExtra']);
				
				$player->sendMessage('<GAMEENDEDRESULT success="true" />');
			} else {
				$player->sendMessage('<GAMEENDEDRESULT success="false" />');
			}
			break;
	}
	
	$player->lastProcessedMessageID = $message['messageID'];
	$player->saveInfo();
}


$player->unlockData();


if($shouldLockRoom) {
	$room->removeInactivePlayers();

	$room->unlock();
}


echo '<SENDMESSAGESRESULT lastProcessedMessageID="' . $player->lastProcessedMessageID . '" />';
?>