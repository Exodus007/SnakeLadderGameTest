<?php

error_reporting(0);

set_magic_quotes_runtime(false);

header('Cache-Control: no-cache');


function parseParameters() {
	if(get_magic_quotes_gpc()) {
		foreach($_POST as $key => $value) {
			$_POST[$key] = stripslashes($value);
		}
	}
	
	$encryptedParameters = $_POST['encryptedParameters'];
	
	if(isset($encryptedParameters)) {
		$decryptedParameters = decryptParameters($encryptedParameters);
		
		parse_str($decryptedParameters, $theParameters);
		
		if(get_magic_quotes_gpc()) {
			foreach($theParameters as $key => $value) {
				$theParameters[$key] = stripslashes($value);
			}
		}
	} else {
		foreach($_POST as $key => $value) {
			$theParameters[$key] = $value;
		}
	}
	
	if(isset($theParameters['playerUID'])) {
		$player = new Player($theParameters['siteID'], $theParameters['gameID'], $theParameters['roomID'], $theParameters['playerUID']);
		if(!$player->lockData()) return null;
		
		$player->loadInfo();
		
		$player->unlockData();
		
		$extraKey = $player->extraKey;
		
		if(!checkHash($theParameters, $extraKey)) return array();
		
	} else {
		if(!checkHash($theParameters, null)) return array();
	}
	
	return $theParameters;
}

function decryptParameters($encryptedParameters) {
	global $hashKey;
	
	$hashKeyLength = strlen($hashKey);
	
	for($i=0;$i<$hashKeyLength;$i++) {
		$encryptKey[$i] = ord(substr($hashKey, $i, 1));
	}
	
	$encryptKey[] = 0;
	
	while(count($encryptKey) % 3 != 0) {
		$encryptKey[] = 0;
	}
	
	$encryptKeyLength = count($encryptKey);
	
	$encryptedParametersLength = strlen($encryptedParameters);
	
	$result = '';
	
	for($i=0;$i<$encryptedParametersLength;$i+=2) {
		sscanf(substr($encryptedParameters, $i, 2), '%x', $charCode);
		
		$charCode += 256 - $encryptKey[(($i / 2) * 3) % $encryptKeyLength];
		$charCode %= 256;
		
		$result .= chr($charCode);
	}
	
	return $result;
}

function checkHash($theParameters, $extraKey) {
	global $hashKey;
	
	$hash = $theParameters['hash'];
	if($hash == null) return false;
	
	$i = 0;
	
	foreach($theParameters as $name => $value) {
		if($name == 'hash') continue;
		
		$sortedParameters[$i]->name = $name;
		$sortedParameters[$i]->value = $value;
		$i++;
	}
	
	usort($sortedParameters, 'sortParametersCompare');
	
	$stringToHash = '';
	
	for($i=0;$i<count($sortedParameters);$i++) {
		$stringToHash .= $sortedParameters[$i]->value;
	}
	
	$stringToHash .= $hashKey;
	if($extraKey != null) $stringToHash .= $extraKey;
	
	return $hash == md5($stringToHash);
}

function sortParametersCompare($a, $b) {
	if($a->name < $b->name) return -1;
	if($a->name > $b->name) return 1;
	return 0;
}

?>