<?php 
class Analysis
{	
	function __construct()
	{
		# code...
	}

	//получение rss всех ресурсов
	public static function getListRssFeed(){
		$db = Db::getConnection();			
		$list = array();
		$query = 'SELECT r.iddictionary, r.idresource, s.rssfeedaddress, e.effect
			FROM rssfeed r
			LEFT JOIN rssfeedsource s ON s.id = r.rssfeed
			LEFT JOIN resource e ON e.id = r.idresource
			ORDER BY s.rssfeedaddress, r.iddictionary';
		$result = $db->query($query);
		$i=0;  
		while ($row = $result->fetch()){
			$list[$row['rssfeedaddress']][$i]['dictionary'] = $row['iddictionary'];	
			$list[$row['rssfeedaddress']][$i]['resource'] = $row['idresource'];
			$list[$row['rssfeedaddress']][$i]['effect'] = $row['effect'];
			$i++;			
		}	
		return json_encode($list);
	}

	public static function getWordFromDictionary(){
		$db = Db::getConnection();			
		$list = array();
		$query = 'SELECT * 
			FROM wordsresource';
		$result = $db->query($query);
		while ($row = $result->fetch()){
			$list[$row['iddictionary']][$row['group']][$row['id']] = $row;
		}
		return json_encode($list);
	}	

	public static function InsertResultAnalisys($iddictionary, $idresource, $ball){
		// $db = Db::getConnection();
		// $query = 'INSERT INTO news ("idresource", "iddictionary", "polldate", "rating") VALUES ('.$idresource.','.$iddictionary.', NOW(), '.$ball.')';
		// $result = $db->query($query);

		/*
		$stmt = prepare('SELECT name FROM users WHERE email = :email');
		$stmt->execute(array('email' => $email));
		*/
		/*
		многократное выполнение запросов
		$stmt = $pdo->prepare('UPDATE users SET bonus = bonus + ? WHERE id = ?');
		foreach ($data as $id => $bonus)
		{
			$stmt->execute([$bonus,$id]);
		}
		получение одного поля 
		$stmt = $pdo->prepare("SELECT name FROM table WHERE id=?");
		$stmt->execute(array($id));
		$name = $stmt->fetchColumn();

		*/
	}
}
?>