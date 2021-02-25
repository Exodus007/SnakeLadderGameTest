<?php
require_once('config.php');


$shareURL = $_GET['shareURL'];


header("Location: http://www.myspace.com/Modules/PostTo/Pages/?u=" . $shareURL);

?>