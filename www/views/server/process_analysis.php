<?php 
include_once('../../base/Db.php');
include('../../views/graph.php');

//если нужно прогнать тестовый вариант
if( isset($_POST['test']) )
{
	$ch = curl_init();
	$url = "http://oslik.egerev.me:9000/:mercury:json:md/tvrain.ru/export/rss/all.xml";
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
	$content = curl_exec($ch);
	curl_close($ch);
	$f = array();
	$f = json_decode($content, true);
	foreach ($f['items'] as $current_item) {
		echo "<br>";
		echo $current_item['title'];
		echo "<br>";
		echo $current_item['content'];
		echo "<br>";

	}
	//echo json_encode($f['items']);
} 

else {

	//слова из словаря
	$all_collocation = array();
	//получение слов для выбранного словаря
	$all_collocation = URL_D::GetAllWords();

	//получение списка опрашиваемых адресов из БД
	$bad_listrss = Graph::getListRssFeed();
	//приводим полученные данные в читаемый вид
	$listrss = json_decode($bad_listrss, true);

	// все наши адреса ресурсов
	$out = array_keys($listrss); 

	$i = 0;
	//проход по всем адресам из БД
	$ii = 0;
	foreach ($listrss as $item){
		if ($ii == 2) break;
		$ii++;
		//---------
		//вывод текущего адреса ерсурса
		echo '<br>';
		echo '<br>';
		echo $out[$i];
		echo '<br>';
		//получаем массив из json запрашиваемого новостного ресурса
		$articles = URL_D::mercury_request($out[$i]);

		//проход по массиву словарей для конкретного адреса
		foreach ($item as $list){	
			echo "<br>";
			echo "Dictionary: ".$list['dictionary'];

			//уровень влияния ресурса
			$effect = $list['effect'];

			//достать конкретный словарь
			$collocation = URL_D::GetCurrentDictionary(
				$list['dictionary'],
				$all_collocation);

			//обработать статьи
			$evaluation = URL_D::DataProcessing(
				$articles, 
				$collocation,
				$effect);

			//ввести в базу данных полученный балл;
			Graph::InsertResultAnalisys(
				$list['dictionary'],
				$list['resource'], 
				$evaluation);
		}	
		$i++;
	}
}


/**
* class to work with mercury and remote server
*/
class URL_D{	

	//получение массива item-ов с ресурса
	public static function mercury_request($rssfeed){
		$ch = curl_init();
		$url = "http://oslik.egerev.me:9000/:mercury:json:md/".$rssfeed;
		echo "<br>";
		echo $url."|";
		echo "<br>";
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
		$content = curl_exec($ch);
		curl_close($ch);
		$f = array();
		$f = json_decode($content, true);
		return $f;		
	}

	//получение всех слов из словаря
	public static function GetAllWords(){
		$all_collacation = Graph::getWordFromDictionary();
		return json_decode($all_collacation, true);
	}
	//получение слов из текущего словаря
	public static function GetCurrentDictionary($id,  $all_collacation){
		return $all_collacation[$id];
	}

	//обработка словарей ресурса
	public static function DataProcessing($articles, $specific_collocation, $effect){
		
		//полученные баллы за все статьи
		$opinion = array();
		//итоговый балл
		$final_evaluation = 0;	

		//только первые 4 статьи
		$i = 0;
		//проход по всем статьям
		foreach ($articles['items'] as $current_item) {
			//---------
			echo '<br>';
			echo "current article";
			echo '<br>';
			echo $current_item['title'];
			echo '<br>';
			// echo $current_item['content'];
			// echo '<br>';

			if ($i==5) break;
			$i++;
			//полученный балл за статью
			$intermediate_evaluation = 0;

			//текст текущей статьи
			$content = $current_item['content'];

			//заголовок текущей статьи
			$title = $current_item['title'];

			//обработать заголовок и прибавить балл, так как он важен
			//если в заголовке есть упомниния, то проверять статью
			$intermediate_evaluation = 
			AnalysisAlgorithm::handle_text(
				$title,
				$specific_collocation,
				true);


			echo "баллы за заголовок:";			
			echo $intermediate_evaluation;
			echo '<br>';

			//обработать текст статьи
			$intermediate_evaluation += 
			AnalysisAlgorithm::handle_text(
				$content,
				$specific_collocation,
				false);


			echo "баллы за заголовок и содержимое:";			
			echo $intermediate_evaluation;
			echo '<br>';

			array_push($opinion, $intermediate_evaluation);
		}

		//обработать массив полученных баллов
		//подредавтировать в зависимости от влияния ресурса
		foreach ($opinion as $key) {
			$final_evaluation += $key;
		}

		echo "Итоговый балл статье: ";		
		//echo  $final_evaluation / (count($opinion)+1);
		echo  $final_evaluation;
		echo '<br>';
		//делим на количество элементов в массиве
		return $final_evaluation / (count($opinion)+1);
	}
}

class AnalysisAlgorithm{
	public static function handle_text($text, $collocation, $bool){

		//количество набранных баллов
		$score = 0;

		if($bool) echo "-проверка заголовка".'<br>';
		else echo "-проверка всей статьи".'<br>';

		$out = array_keys($collocation); 
		$i=0;
		foreach ($collocation as $key) {			
			if ($bool && $out[$i] != 1){
				//если проверяем заголовок, то только по первой группе 
				break;
			} else {
				foreach ($key as $value) {
					$val = mb_stripos(" ".$text, $value['collocation'], 0, 'UTF-8');
					if($val) {
						echo "есть вхождения".$value['collocation'].$value['rating'].'<br>';
						$score += $value['rating'];
					}
				}				
			}	
			$i++;			
		}

		//длина проверяемого текста
		$len_content = iconv_strlen($text, 'UTF-8');

		//мутим тут что то со score и len_count, чтобы получить оценку
		//пока что просто отправляем score

		//$final_score = $len_content / $score;

		//если гадость в заголовке, то балл выше
		//if ($bool) $final_score *= 2;

		//отправляем финальный балл проверки
		return $score;
	}
}
?>