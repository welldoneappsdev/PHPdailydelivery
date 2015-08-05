<?php
	$server = 'localhost';
	$user = 'baxobeat_friends';
	$pass = 'asrar123';
	$db_name = 'baxobeat_daily';


	$linkdb = mysql_connect($server, $user, $pass);
	

	$select_db = mysql_select_db($db_name);
	
?>