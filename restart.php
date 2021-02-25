<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Novel Games Lobby Control Panel</title>
<style type="text/css">
<!--

body { 
	background-color:#FFFFFF;
	font-family: Arial;
	font-size: 12px;
	color: #333333;
}

form {
	width: 300px;
	margin-left: auto;
	margin-right:auto;
	margin-top:100px;
}

.fieldName {
	border: 1px solid #333333;
	width: 80px;
	height: 20px;
	padding: 5px;
}

.fieldValue {
	margin-left: 91px;
	margin-top: -32px;
	margin-bottom: -1px;
	border: 1px solid #333333;
	width: 200px;
	height: 20px;
	padding:5px;
}

input[type="text"] ,
input[type="password"]  {
	border: 1px solid #333333;
	width: 200px;
}

input[type="submit"] {
	margin-top: 20px;
	margin-left: 75px;
	border: 1px solid #333333;
	width:150px;
}

-->
</style>
</head>
<body>
	<form method="post" action="restartSubmit.php">
		<div class="fieldName">Username</div>
		<div class="fieldValue"><input type="text" name="username" /></div>
		<div class="fieldName">Password</div>
		<div class="fieldValue"><input type="password" name="password" /></div>
		<div><input type="submit" value="Restart Lobby" /></div>
	</form>
</body>
</html>