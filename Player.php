<?php
class Player {
	var $siteID, $gameID, $roomID, $playerUID;
	var $folderPath, $dataFilePath, $messagesFilePath;
	
	var $playerName;
	var $lastActivityTime;
	var $extraKey;
	
	var $playerID, $playerEmail, $playerUsername, $playerPictureURL, $playerFacebookUserID;
	
	var $lastProcessedMessageID;
	
	var $dataFileHandle, $dataLockCount;
	var $messagesFileHandle, $messagesLockCount;
	
	
	public function Player($siteID, $gameID, $roomID, $playerUID) {
		global $filesPath;
		
		$this->siteID = $siteID;
		$this->gameID = $gameID;
		$this->roomID = $roomID;
		$this->playerUID = $playerUID;
		
		$this->folderPath = "$filesPath/site_$siteID/game_$gameID/room_$roomID/player_$playerUID";
		$this->dataFilePath = $this->folderPath . '/data';
		$this->messagesFilePath = $this->folderPath . '/messages';
		
		$this->dataLockCount = 0;
		$this->messagesLockCount = 0;
	}
	
	public function create() {
		mkdir($this->folderPath, 0777, true);
		
		touch($this->dataFilePath);
		touch($this->dataFilePath . '.lock');
		touch($this->messagesFilePath);
		touch($this->messagesFilePath . '.lock');
		
		$messagesInfo->startID = 0;
		$messagesInfo->endID = 0;
		$messagesInfo->messages = array();
		
		file_put_contents($this->messagesFilePath, serialize($messagesInfo));
	}
	
	public function destroy() {
		if(!$this->lockData()) return;
		if(!$this->lockMessages()) return;
		
		unlink($this->dataFilePath);
		unlink($this->messagesFilePath);
		
		if(!unlink($this->messagesFilePath . '.lock') && file_exists($this->messagesFilePath . '.lock')) {
			$this->unlockMessages();
			while(!unlink($this->messagesFilePath . '.lock') && file_exists($this->messagesFilePath . '.lock')) {
				sleep(1);
			}
		}
		
		if(!unlink($this->dataFilePath . '.lock') && file_exists($this->dataFilePath . '.lock')) {
			$this->unlockData();
			while(!unlink($this->dataFilePath . '.lock') && file_exists($this->dataFilePath . '.lock')) {
				sleep(1);
			}
		}
		
		rmdir($this->folderPath);
	}
	
	public function lockData() {
		$this->dataLockCount++;
		if($this->dataLockCount > 1) return true;
		
		$this->dataFileHandle = fopen($this->dataFilePath . '.lock', 'r+');
		if($this->dataFileHandle === false) return false;
		
		if(!flock($this->dataFileHandle, LOCK_EX)) return false;
		
		if(!file_exists($this->dataFilePath)) return false;
		
		return true;
	}
	
	public function unlockData() {
		$this->dataLockCount--;
		
		if($this->dataLockCount <= 0) {
			$this->dataLockCount = 0;
			
			fclose($this->dataFileHandle);
		}
	}
	
	public function lockMessages() {
		$this->messagesLockCount++;
		if($this->messagesLockCount > 1) return true;
		
		$this->messagesFileHandle = fopen($this->messagesFilePath . '.lock', 'r+');
		if($this->messagesFileHandle === false) return false;
		
		if(!flock($this->messagesFileHandle, LOCK_EX)) return false;
		
		if(!file_exists($this->messagesFilePath)) return false;
		
		return true;
	}
	
	public function unlockMessages() {
		$this->messagesLockCount--;
		
		if($this->messagesLockCount <= 0) {
			$this->messagesLockCount = 0;
			
			fclose($this->messagesFileHandle);
		}
	}
	
	public function saveInfo() {
		$data->playerName = $this->playerName;
		$data->lastActivityTime = $this->lastActivityTime;
		$data->extraKey = $this->extraKey;
		
		$data->playerID = $this->playerID;
		$data->playerEmail = $this->playerEmail;
		$data->playerUsername = $this->playerUsername;
		$data->playerPictureURL = $this->playerPictureURL;
		$data->playerFacebookUserID = $this->playerFacebookUserID;
		
		$data->lastProcessedMessageID = $this->lastProcessedMessageID;
		
		file_put_contents($this->dataFilePath, serialize($data));
	}
	
	public function loadInfo() {
		$data = unserialize(file_get_contents($this->dataFilePath));
		
		$this->playerName = $data->playerName;
		$this->lastActivityTime = $data->lastActivityTime;
		$this->extraKey = $data->extraKey;
		
		$this->playerID = $data->playerID;
		$this->playerEmail = $data->playerEmail;
		$this->playerUsername = $data->playerUsername;
		$this->playerPictureURL = $data->playerPictureURL;
		$this->playerFacebookUserID = $data->playerFacebookUserID;
		
		$this->lastProcessedMessageID = $data->lastProcessedMessageID;
	}
	
	public function updateLastActivityTime() {
		$this->lastActivityTime = time();
	}
	
	public function sendMessage($message) {
		$this->lockMessages();
		
		$messagesInfo = unserialize(file_get_contents($this->messagesFilePath));
		
		$messagesInfo->endID++;
		$messagesInfo->messages[] = $message;
		
		file_put_contents($this->messagesFilePath, serialize($messagesInfo));
		
		$this->unlockMessages();
	}
	
	public function sendPlayerJoinedRoomMessage($player) {
		$message = '<PLAYERJOINEDROOM playerName="' . htmlspecialchars($player->playerName) . '" playerUID="' . $player->playerUID . '" playerPictureURL="' . ($player->playerPictureURL != null ? htmlspecialchars($player->playerPictureURL) : '') . '" playerFacebookUserID="' . ($player->playerFacebookUserID != null ? htmlspecialchars($player->playerFacebookUserID) : '') . '" />';
		
		$this->sendMessage($message);
	}
	
	public function sendSelfDisconnectedMessage() {
		$message = '<DISCONNECTED />';
		
		$this->sendMessage($message);
	}
	
	public function sendPlayersDisconnectedMessage($players) {
		$playerUIDs = array();
		
		for($i=0;$i<count($players);$i++) {
			$playerUIDs[] = $players[$i]->playerUID;
		}
		
		$message = '<PLAYERDISCONNECTED playerUID="' . implode(',', $playerUIDs) . '" />';
		
		$this->sendMessage($message);
	}
	
	public function sendPlayerOpenedTableMessage($player, $table) {
		$message = '<PLAYEROPENEDTABLE playerUID="' . $player->playerUID . '" tableUID="' . $table->tableUID . '" possibleNoOfPlayers="' . implode(',', $table->possibleNoOfPlayers) . '" ' . ($table->tableID == null ? '' : 'tableID="' . $table->tableID . '"') . '/>';
		
		$this->sendMessage($message);
	}
	
	public function sendPlayerJoinedTableMessage($player, $table) {
		$message = '<PLAYERJOINEDTABLE playerUID="' . $player->playerUID . '" tableUID="' . $table->tableUID . '" />';
		
		$this->sendMessage($message);
	}
	
	public function sendPlayerLeftTableMessage($player, $tableUID) {
		$message = '<PLAYERLEFTTABLE playerUID="' . $player->playerUID . '" tableUID="' . $tableUID . '" />';
		
		$this->sendMessage($message);
	}
	
	public function sendRobotJoinedTableMessage($table) {
		$noOfRobots = 0;
		
		for($i=0;$i<count($table->playerUIDs);$i++) {
			if($table->playerUIDs[$i] == 'robot') $noOfRobots++;
		}
		
		$message = '<ROBOTJOINEDTABLE tableUID="' . $table->tableUID . '" noOfRobots="' . $noOfRobots . '" />';
		
		$this->sendMessage($message);
	}
	
	public function sendInviteMessage($player, $table) {
		$message = '<INVITE playerUID="' . $player->playerUID . '" tableUID="' . $table->tableUID . '" />';
		
		$this->sendMessage($message);
	}
	
	public function sendPlayingStartedMessage($table, $randomSeeds) {
		$message = '<PLAYINGSTARTED tableUID="' . $table->tableUID . '" randomSeeds="' . implode(',', $randomSeeds) . '" />';
		
		$this->sendMessage($message);
	}
	
	public function sendPlayerViewedTableMessage($player, $table) {
		$message = '<PLAYERVIEWEDTABLE playerUID="' . $player->playerUID . '" tableUID="' . $table->tableUID . '" />';
		
		$this->sendMessage($message);
	}
	
	public function sendGameMessage($playIndex, $message) {
		$message = '<GAMEMESSAGERECEIVED playIndex="' . $playIndex . '" message="' . htmlspecialchars($message) . '" />';
		
		$this->sendMessage($message);
	}
	
	public function sendUpdateTimeMessage($playIndex, $playerIndex, $time) {
		$message = '<PLAYERTIMEUPDATED playIndex="' . $playIndex . '" playerIndex="' . $playerIndex . '" time="' . $time . '" />';
		
		$this->sendMessage($message);
	}
	
	public function sendChatMessage($message, $fromPlayerIndex) {
		$message = '<CHATMESSAGERECEIVED message="' . htmlspecialchars($message) . '" fromPlayerIndex="' . $fromPlayerIndex . '" />';
		
		$this->sendMessage($message);
	}
	
	public function getMessages($messageID) {
		$this->lockMessages();
		
		$theMessages = array();
		
		$messagesInfo = unserialize(file_get_contents($this->messagesFilePath));
		
		if($messageID < $messagesInfo->endID) {
			for($i=$messageID-$messagesInfo->startID;$i<count($messagesInfo->messages);$i++) {
				$theMessages[] = $messagesInfo->messages[$i];
			}
			
			$messagesInfo->startID = $messageID;
			$messagesInfo->messages = $theMessages;
			
			file_put_contents($this->messagesFilePath, serialize($messagesInfo));
		}
		
		$this->unlockMessages();
		
		return $theMessages;
	}
}
?>