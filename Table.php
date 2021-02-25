<?php
class Table {
	var $siteID, $gameID, $roomID, $tableUID;
	var $folderPath, $dataFilePath;
	
	var $possibleNoOfPlayers;
	var $playerUIDs;
	var $viewerUIDs;
	var $isPlaying;
	var $tableID;
	var $playingPlayerInfos;
	
	var $dataFileHandle, $dataLockCount;
	
	
	public function Table($siteID, $gameID, $roomID, $tableUID) {
		global $filesPath;
		
		$this->siteID = $siteID;
		$this->gameID = $gameID;
		$this->roomID = $roomID;
		$this->tableUID = $tableUID;
		
		$this->folderPath = "$filesPath/site_$siteID/game_$gameID/room_$roomID/table_$tableUID";
		$this->dataFilePath = $this->folderPath . '/data';
		
		$this->dataLockCount = 0;
	}
	
	
	public function create() {
		mkdir($this->folderPath, 0777, true);
		
		touch($this->dataFilePath);
		touch($this->dataFilePath . '.lock');
	}
	
	public function destroy() {
		$this->lockData();
		
		unlink($this->dataFilePath);
		
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
	
	public function saveInfo() {
		if(!$this->lockData()) return;
		
		$data->possibleNoOfPlayers = $this->possibleNoOfPlayers;
		$data->playerUIDs = $this->playerUIDs;
		$data->viewerUIDs = $this->viewerUIDs;
		$data->isPlaying = $this->isPlaying;
		$data->tableID = $this->tableID;
		$data->playingPlayerInfos = $this->playingPlayerInfos;
		
		file_put_contents($this->dataFilePath, serialize($data));
		
		$this->unlockData();
	}
	
	public function loadInfo() {
		$data = unserialize(file_get_contents($this->dataFilePath));
		
		$this->possibleNoOfPlayers = $data->possibleNoOfPlayers;
		$this->playerUIDs = $data->playerUIDs;
		$this->viewerUIDs = $data->viewerUIDs;
		$this->isPlaying = $data->isPlaying;
		$this->tableID = $data->tableID;
		$this->playingPlayerInfos = $data->playingPlayerInfos;
	}
	
	public function removePlayers($players) {
		$this->lockData();
		
		$removed = false;
		
		for($i=0;$i<count($this->playerUIDs);$i++) {
			for($j=0;$j<count($players);$j++) {
				if($this->playerUIDs[$i] != $players[$j]->playerUID) continue;
			
				array_splice($this->playerUIDs, $i, 1);
				$i--;
				
				$removed = true;
				
				break;
			}
		}
		
		if($removed) {
			for($i=0;$i<count($this->playerUIDs);$i++) {
				if($this->playerUIDs[$i] != 'robot') break;
			}
			
			$firstHumanIndex = $i;
			
			if($firstHumanIndex == count($this->playerUIDs)) $this->playerUIDs = array();
			
			if(!$this->isPlaying) {
				if($firstHumanIndex < count($this->playerUIDs)) {
					array_splice($this->playerUIDs, 0, $firstHumanIndex);
					
					for($i=0;$i<$firstHumanIndex;$i++) {
						$this->playerUIDs[] = 'robot';
					}
				}
			} else {
				for($i=0;$i<count($this->playingPlayerInfos);$i++) {
					for($j=0;$j<count($players);$j++) {
						if($this->playingPlayerInfos[$i]->playerUID != $players[$j]->playerUID) continue;
						
						$this->playingPlayerInfos[$i]->disconnected = true;
						
						break;
					}
				}
			}
			
			$this->saveInfo();
		}
		
		$this->unlockData();
		
		return $removed;
	}
	
	public function removeViewers($players) {
		$this->lockData();
		
		$removed = false;
		
		for($i=0;$i<count($this->viewerUIDs);$i++) {
			for($j=0;$j<count($players);$j++) {
				if($this->viewerUIDs[$i] != $players[$j]->playerUID) continue;
			
				array_splice($this->viewerUIDs, $i, 1);
				$i--;
				
				$removed = true;
			}
		}
		
		if($removed) $this->saveInfo();
		
		$this->unlockData();
		
		return $removed;
	}
	
	public function playerJoined($player) {
		$this->lockData();
		
		if($this->isPlaying) {
			$success = false;
		} else {
			$maxNoOfPlayers = $this->possibleNoOfPlayers[count($this->possibleNoOfPlayers) - 1];
			
			if(count($this->playerUIDs) >= $maxNoOfPlayers) {
				$success = false;
			} else {
				$success = true;
				$this->playerUIDs[] = $player->playerUID;
			}
		}
		
		if($success) $this->saveInfo();
		
		$this->unlockData();
		
		return $success;
	}
	
	public function robotJoined() {
		$this->lockData();
		
		if($this->isPlaying) {
			$success = false;
		} else {
			$maxNoOfPlayers = $this->possibleNoOfPlayers[count($this->possibleNoOfPlayers) - 1];
			
			if(count($this->playerUIDs) >= $maxNoOfPlayers) {
				$success = false;
			} else {
				$success = true;
				$this->playerUIDs[] = 'robot';
			}
		}
		
		if($success) $this->saveInfo();
		
		$this->unlockData();
		
		return $success;
	}
	
	public function getCanStartPlaying() {
		if($this->isPlaying) return false;
		
		$noOfPlayers = count($this->playerUIDs);
		
		for($i=0;$i<count($this->possibleNoOfPlayers);$i++) {
			if($this->possibleNoOfPlayers[$i] == $noOfPlayers) {
				return true;
			}
		}
		
		return false;
	}
	
	public function startPlaying($room) {
		$this->lockData();
		
		$noOfPlayers = count($this->playerUIDs);
		
		$this->playingPlayerInfos = array();
		
		for($i=0;$i<$noOfPlayers;$i++) {
			$this->playingPlayerInfos[$i]->playerUID = $this->playerUIDs[$i];
			
			if($this->playerUIDs[$i] != 'robot') {
				$player = $room->getPlayer($this->playerUIDs[$i]);
				
				$this->playingPlayerInfos[$i]->playerName = $player->playerName;
				
				$this->playingPlayerInfos[$i]->playerID = $player->playerID;
				$this->playingPlayerInfos[$i]->playerEmail = $player->playerEmail;
				$this->playingPlayerInfos[$i]->playerUsername = $player->playerUsername;
				$this->playingPlayerInfos[$i]->playerFacebookUserID = $player->playerFacebookUserID;
			}
			
			$this->playingPlayerInfos[$i]->disconnected = false;
		}
		
		$this->isPlaying = true;
		
		$this->saveInfo();
		
		$this->unlockData();
	}
	
	public function playerViewed($player) {
		$this->lockData();
		
		if(!$this->isPlaying) {
			$success = false;
		} else {
			$success = true;
			$this->viewerUIDs[] = $player->playerUID;
		}
		
		if($success) $this->saveInfo();
		
		$this->unlockData();
		
		return $success;
	}
	
	public function sendGameMessage($playIndex, $message, $excludedPlayerUID) {
		$noOfPlayers = count($this->playingPlayerInfos);
		
		for($i=0;$i<$noOfPlayers;$i++) {
			if($this->playingPlayerInfos[$i]->playerUID == $excludedPlayerUID) continue;
			if($this->playingPlayerInfos[$i]->disconnected) continue;
			
			$player = new Player($this->siteID, $this->gameID, $this->roomID, $this->playingPlayerInfos[$i]->playerUID);
			$player->sendGameMessage($playIndex, $message);
		}
	}
	
	public function sendUpdateTimeMessage($playIndex, $playerIndex, $time, $excludedPlayerUID) {
		$noOfPlayers = count($this->playingPlayerInfos);
		
		for($i=0;$i<$noOfPlayers;$i++) {
			if($this->playingPlayerInfos[$i]->playerUID == $excludedPlayerUID) continue;
			if($this->playingPlayerInfos[$i]->disconnected) continue;
			
			$player = new Player($this->siteID, $this->gameID, $this->roomID, $this->playingPlayerInfos[$i]->playerUID);
			$player->sendUpdateTimeMessage($playIndex, $playerIndex, $time);
		}
	}
	
	public function sendChatMessage($message, $fromPlayerUID) {
		$noOfPlayers = count($this->playingPlayerInfos);
		
		for($i=0;$i<$noOfPlayers;$i++) {
			if($this->playingPlayerInfos[$i]->playerUID != $fromPlayerUID) continue;
			
			$fromPlayerIndex = $i;
			
			break;
		}
		
		for($i=0;$i<$noOfPlayers;$i++) {
			if($this->playingPlayerInfos[$i]->playerUID == $fromPlayerUID) continue;
			if($this->playingPlayerInfos[$i]->disconnected) continue;
			
			$player = new Player($this->siteID, $this->gameID, $this->roomID, $this->playingPlayerInfos[$i]->playerUID);
			$player->sendChatMessage($message, $fromPlayerIndex);
		}
	}
}
?>