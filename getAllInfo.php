<?php
require_once('config.php');
require_once('common.php');
require_once('Site.php');
require_once('Game.php');
require_once('Room.php');
require_once('Player.php');
require_once('Table.php');


$directory = opendir($filesPath);

while(($file = readdir($directory)) !== false) {
	if(substr($file, 0, 5) != 'site_') continue;
	
	$siteIDs[] = substr($file, 5);
}


echo '<ALLINFOS>';

for($i=0;$i<count($siteIDs);$i++) {
	$siteID = $siteIDs[$i];
	$site = new Site($siteID);
	$site->readGames();
	$site->removeInactivePlayers();

	echo '<SITEINFO siteID="' . $siteID . '">';

	for($j=0;$j<count($site->games);$j++) {
		$game = $site->games[$j];
		
		echo '<GAMEINFO gameID="' . $game->gameID . '">';
		
		for($k=0;$k<count($game->rooms);$k++) {
			$room = $game->rooms[$k];
			
			echo '<ROOMINFO roomID="' . ($k + 1) . '" roomName="' . htmlspecialchars($room->roomName) . '" roomCapacity="' . $room->roomCapacity . '">';
			
			for($m=0;$m<count($room->players);$m++) {
				$player = $room->players[$m];
				
				echo '<PLAYERINFO playerUID="' . $player->playerUID . '" playerName="' . htmlspecialchars($player->playerName) . '" />';
			}

			for($m=0;$m<count($room->tables);$m++) {
				$table = $room->tables[$m];
				
				echo '<TABLEINFO tableUID="' . $table->tableUID . '" possibleNoOfPlayers="' . implode(',', $table->possibleNoOfPlayers) . '" playerUIDs="' . implode(',', $table->playerUIDs) . '" viewerUIDs="' . implode(',', $table->viewerUIDs) . '" isPlaying="' . ($table->isPlaying ? 'true' : 'false') . '" />';
			}
			
			echo '</ROOMINFO>';
		}
		
		echo '</GAMEINFO>';
	}

	echo '</SITEINFO>';
}

echo '</ALLINFOS>';
?>