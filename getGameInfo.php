<?php
require_once('config.php');
require_once('common.php');
require_once('Game.php');
require_once('Room.php');
require_once('Player.php');
require_once('Table.php');


$siteID = $_POST['siteID'];
$gameID = $_POST['gameID'];


if(!isset($siteID) || !isset($gameID)) exit;


$game = new Game($siteID, $gameID);
$game->readRooms();
$game->removeInactivePlayers();


echo '<GAMEINFO>';

for($i=0;$i<count($game->rooms);$i++) {
	echo '<ROOMINFO roomID="' . ($i + 1) . '" roomName="' . htmlspecialchars($game->rooms[$i]->roomName) . '" roomCapacity="' . $game->rooms[$i]->roomCapacity . '" noOfPlayers="' . count($game->rooms[$i]->players) . '" />';
}

echo '</GAMEINFO>';

?>