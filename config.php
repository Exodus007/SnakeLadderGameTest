<?php

$defaultRoomNames = null;
$defaultRoomCapacities = null;

$gameRoomNames = null;
$gameRoomCapacities = null;

$controlUsername = 'admin';
$controlPassword = 'password';

// $duplicateNameTreatment can be "addIndex", "showAll", "disconnectOld", "disconnectNew"
$duplicateNameTreatment = "addIndex";

$hashKey = "hdbhYhsgKjs9oI432HG";
$extraKeyLength = 16;

$filesPath = 'lobbyFiles';
$picturesPath = 'pictures';

$inactiveTimeout = 30;

$shareWinText = 'Won in #[GAMENAME]!';
$shareDrawText = 'Drew in #[GAMENAME]!';
$shareRankText = 'Ranked number #[RANK] in #[GAMENAME]!';
$shareFacebookAppID = 'FacebookAppID';
$shareFacebookAppSecret = 'FacebookAppSecret';
$shareFacebookActionText = 'Play Now';
$shareFacebookEnableChallenge = false;
$shareFacebookThanksText = 'Thank You for Sharing!';
$shareFacebookChallengeText = 'Challenge Your Friends to Play';
$shareFacebookCloseText = 'Close';
$shareFacebookRequestTitle = 'Select the Friends to Challenge';
$shareFacebookRequestWinText = 'I want to challenge you to play #[GAMENAME]. I just won in the game, can you beat me?';
$shareFacebookRequestDrawText = 'I want to challenge you to play #[GAMENAME]. I just drew in the game, can you beat me?';
$shareFacebookRequestRankText = 'I want to challenge you to play #[GAMENAME]. I just ranked number #[RANK] in the game, can you beat me?';

$emailSender = '';
$emailSubject = "Challenge to play #[GAMENAME]";
$emailWinContent = "Dear #[FRIENDNAME],\n\n\tI want to challenge you to play #[GAMENAME] with me, I just won the game and I bet you cannot beat me. You can play the game here:\n\n#[URL]\n\nBest Regards,\n\n#[OWNNAME]";
$emailDrawContent = "Dear #[FRIENDNAME],\n\n\tI want to challenge you to play #[GAMENAME] with me, I just drew the game and I bet you cannot beat me. You can play the game here:\n\n#[URL]\n\nBest Regards,\n\n#[OWNNAME]";
$emailRankContent = "Dear #[FRIENDNAME],\n\n\tI want to challenge you to play #[GAMENAME] with me, I just ranked number #[RANK] and I bet you cannot beat me. You can play the game here:\n\n#[URL]\n\nBest Regards,\n\n#[OWNNAME]";
$emailDefaultURL = "";


/*
implement this function if login is needed

return null if login failed
return the player ID and player name if succeed
*/
function checkLogin($username, $password) {
	$result = new stdClass();
	
	$result->playerID = '1';
	$result->playerName = $username;
	$result->playerPictureURL = null;
	
	return $result;
}

/*
implement this function to record the result if needed
*/
function recordResult($siteID, $gameID, $roomID, $tableUID, $tableID, $playIndex, $playerNames, $playerEmails, $playerIDs, $playerUsernames, $playerFacebookUserIDs, $ranks, $playerIndex, $customExtra) {
}
?>