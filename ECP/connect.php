<?php
	$host="serwer1964934.home.pl";
	$db_user="29876414_ecp";
	$db_password="Swierzak1328$";
	$db_name="29876414_ecp";
	
	try{
	$db= new PDO("mysql:host={$host};dbname={$db_name};charset=utf8",$db_user, $db_password, [
	PDO::ATTR_EMULATE_PREPARES=>false, 
	PDO::ATTR_ERRMODE=> PDO::ERRMODE_EXCEPTION
	]);
	$db -> query ('SET NAMES utf8');
	//$db -> query ('SET CHARACTER_SET utf8_unicode_ci');
	
	} catch (PDOException $PDO_error)

	{
		echo $PDO_error->getMessage();
		exit('Database error');
	}
?>