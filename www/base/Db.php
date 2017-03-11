<?php 
/**
* подключение и работа с БД
*/
class Db
{
	
	function __construct()
	{
		
	}

	public static function getConnection(){
		$paramsPath = 'db_params.php';
		$params = include($paramsPath);

		$dsn = "mysql:host={$params['host']};dbname={$params['dbname']}";
		$opt = array(
		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES   => false,
		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
		);
		$db = new PDO($dsn, $params['user'],$params['password'],$opt);

		return $db;	

		$pdo = new PDO($dsn, $user, $pass, $opt);
	}
}
?>