<?php
require_once('config.php');
require_once('common.php');
require_once('Site.php');
require_once('Game.php');
require_once('Room.php');
require_once('Player.php');
require_once('Table.php');


$siteID = $_POST['siteID'];


if(!isset($siteID)) exit;


$site = new Site($siteID);
$site->readGames();
$site->removeInactivePlayers();


echo '<SITEINFO siteID="' . $siteID . '">';

for($i=0;$i<count($site->games);$i++) {
	$game = $site->games[$i];
	
	echo '<GAMEINFO gameID="' . $game->gameID . '">';
	
	for($j=0;$j<count($game->rooms);$j++) {
		$room = $game->rooms[$j];
		
		echo '<ROOMINFO roomID="' . ($j + 1) . '" roomName="' . htmlspecialchars($room->roomName) . '" roomCapacity="' . $room->roomCapacity . '">';
		
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

?>