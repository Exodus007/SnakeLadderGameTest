<?php
class Site {
	var $siteID;
	var $folderPath;
	
	var $games;
	
	
	public function Site($siteID) {
		global $filesPath;
		
		$this->siteID = $siteID;
		
		$this->folderPath = "$filesPath/site_$siteID";
		
		if(!file_exists($this->folderPath)) mkdir($this->folderPath, 0777, true);
		
		$this->games = array();
	}
	
	public function readGames() {
		$this->games = array();
		
		$directory = opendir($this->folderPath);
		
		while(($fileName = readdir($directory)) !== false) {
			if(substr($fileName, 0, 5) != 'game_') continue;
			
			$gameID = substr($fileName, 5);
			
			$game = new Game($this->siteID, $gameID);
			$game->readRooms();
			
			$this->games[] = $game;
		}
		
		closedir($directory);
	}
	
	public function removeInactivePlayers() {
		for($i=0;$i<count($this->games);$i++) {
			$this->games[$i]->removeInactivePlayers();
		}
	}
}
?>