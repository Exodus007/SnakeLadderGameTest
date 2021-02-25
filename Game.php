<?php
class Game {
	var $siteID, $gameID;
	var $folderPath;
	
	var $rooms;
	
	
	public function Game($siteID, $gameID) {
		global $filesPath;
		
		$this->siteID = $siteID;
		$this->gameID = $gameID;
		
		$this->folderPath = "$filesPath/site_$siteID/game_$gameID";
		
		if(!file_exists($this->folderPath)) mkdir($this->folderPath, 0777, true);
		
		$this->rooms = array();
	}
	
	public function readRooms() {
		global $defaultRoomNames, $defaultRoomCapacities;
		global $gameRoomNames, $gameRoomCapacities;
		
		$rooms = array();
		
		if(!isset($gameRoomNames[$this->gameID])) {
			$roomNames = $defaultRoomNames;
		} else {
			$roomNames = $gameRoomNames[$this->gameID];
		}
		
		if(!isset($gameRoomCapacities[$this->gameID])) {
			$roomCapacities = $defaultRoomCapacities;
		} else {
			$roomCapacities = $gameRoomCapacities[$this->gameID];
		}
		
		if(count($roomNames) == 0) $roomNames[0] = '';
		
		for($i=0;$i<count($roomNames);$i++) {
			$rooms[$i] = new Room($this->siteID, $this->gameID, $i + 1);
			$rooms[$i]->roomName = isset($roomNames[$i]) ? $roomNames[$i] : '';
			$rooms[$i]->roomCapacity = isset($roomCapacities[$i]) ? $roomCapacities[$i] : -1;
		}
		
		$directory = opendir($this->folderPath);
		
		while(($fileName = readdir($directory)) !== false) {
			if(substr($fileName, 0, 5) != 'room_') continue;
			
			$roomID = substr($fileName, 5);
			if($roomID <= count($roomNames)) continue;
			
			$rooms[$roomID - 1] = new Room($this->siteID, $this->gameID, $roomID);
			$rooms[$roomID - 1]->roomName = '';
			$rooms[$roomID - 1]->roomCapacity = -1;
		}
		
		closedir($directory);
		
		$this->rooms = $rooms;
	}
	
	public function removeInactivePlayers() {
		for($i=0;$i<count($this->rooms);$i++) {
			if(!$this->rooms[$i]->lock()) continue;
			$this->rooms[$i]->loadInfo();
			$this->rooms[$i]->removeInactivePlayers();
			$this->rooms[$i]->unlock();
		}
	}
	
	public function getNoOfPlayers() {
		$noOfPlayers = 0;
		
		for($i=0;$i<count($this->rooms);$i++) {
			$noOfPlayers += count($this->rooms[$i]->players);
		}
		
		return $noOfPlayers;
	}
}
?>