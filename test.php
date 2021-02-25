<?php

require_once('config.php');
require_once('common.php');


echo "Looking for files......<br />\n";

if(!file_exists($filesPath)) {
	echo "The folder $filesPath does not exist. Please check the filesPath parameter.<br />\n";
	exit;
} else {
	echo "Files found.<br />\n";
}


echo "Trying to create directories......<br />\n";

mkdir("$filesPath/directory");

if(!file_exists("$filesPath/directory")) {
	echo "Directories cannot be created inside the folder $filesPath. Please make sure that the web server can create directories in this folder.<br />\n";
	exit;
} else {
	echo "Create directory succeeded.<br />\n";
}


echo "Trying to create files......<br />\n";

if(!touch("$filesPath/directory/file")) {
	echo "Files cannot be created inside the folder $filesPath. Please make sure that the web server can write files to this folder and subfolders.<br />\n";
	exit;
} else {
	echo "Create file succeeded.<br />\n";
}


echo "Trying to open file......<br />\n";

$file = fopen("$filesPath/directory/file", 'r+');

if($file === false) {
	echo "Cannot open file in read write mode. Please make sure that the web server have full access to the files in the folder at $filesPath and subfolders.<br />\n";
	exit;
} else {
	echo "Open succeeded.<br />\n";
}


echo "Trying to write to file......<br />\n";

if(!fwrite($file, 'text')) {
	echo "Cannot write file. Please make sure that the web server have full access to the files in the folder at $filesPath and subfolders.<br />\n";
	exit;
} else {
	echo "Write succeeded.<br />\n";
}


echo "Trying to read from file......<br />\n";

if(fseek($file, 0, SEEK_SET) == -1 || ($output = fread($file, 4)) === false) {
	echo "Cannot read file. Please make sure that the web server have full access to the files in the folder at $filesPath and subfolders.<br />\n";
	exit;
} else {
	echo "Read succeeded.<br />\n";
}

fclose($file);


echo "Trying to delete file......<br />\n";

if(!unlink("$filesPath/directory/file")) {
	echo "Cannot delete file. Please make sure that the web server have full access to the files in the folder at $filesPath and subfolders.<br />\n";
	exit;
} else {
	echo "Delete succeeded.<br />\n";
}


echo "Trying to delete directory......<br />\n";

if(!rmdir("$filesPath/directory")) {
	echo "Cannot delete directory. Please make sure that the web server have full access to the files in the folder at $filesPath and subfolders.<br />\n";
	exit;
} else {
	echo "Delete succeeded.<br />\n";
}


echo "Looking for pictures folder......<br />\n";

if(!file_exists($picturesPath)) {
	echo "The folder $picturesPath does not exist. Please check the picturesPath parameter.<br />\n";
	exit;
} else {
	echo "Pictures folder found.<br />\n";
}


echo "Trying to create files......<br />\n";

if(!touch("$picturesPath/file")) {
	echo "Files cannot be created inside the folder $picturesPath. Please make sure that the web server can write files to this folder.<br />\n";
	exit;
} else {
	echo "Create file succeeded.<br />\n";
}


echo "Trying to open file......<br />\n";

$file = fopen("$picturesPath/file", 'r+');

if($file === false) {
	echo "Cannot open file in read write mode. Please make sure that the web server have full access to the files in the folder at $picturesPath.<br />\n";
	exit;
} else {
	echo "Open succeeded.<br />\n";
}


echo "Trying to write to file......<br />\n";

if(!fwrite($file, 'text')) {
	echo "Cannot write file. Please make sure that the web server have full access to the files in the folder at $picturesPath.<br />\n";
	exit;
} else {
	echo "Write succeeded.<br />\n";
}


echo "Trying to read from file......<br />\n";

if(fseek($file, 0, SEEK_SET) == -1 || ($output = fread($file, 4)) === false) {
	echo "Cannot read file. Please make sure that the web server have full access to the files in the folder at $picturesPath.<br />\n";
	exit;
} else {
	echo "Read succeeded.<br />\n";
}

fclose($file);


echo "Trying to delete file......<br />\n";

if(!unlink("$picturesPath/file")) {
	echo "Cannot delete file. Please make sure that the web server have full access to the files in the folder at $picturesPath.<br />\n";
	exit;
} else {
	echo "Delete succeeded.<br />\n";
}


echo "<br />EVERYTHING SEEMS TO WORK FINE<br />\n";

?>