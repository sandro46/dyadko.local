<?php
include 'simplehtmldom/simple_html_dom.php';
include 'config.php';
include 'lib.php';

$sql = "
			SELECT * FROM category
";
$stmt = db()->query($sql);
$cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
$res = array();
getChildCat(3324, $cats, $res);
// var_dump($res);

$stmt = db()->query("TRUNCATE TABLE tmp_cat");
$sql = "INSERT INTO tmp_cat(id)
				VALUES(?)";
$stmt = db()->prepare($sql);
// $res = db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
// var_dump($res);
foreach($res as $val){
	// var_dump(array($val));
	$stmt->execute(array($val));
}

//
// $dataCatUrl = 'index.php?cpn=QoyKZbD5ny%2FG%2FXCQsBtCY9V%2BrsOfG2npuF4G3SV7CLxDVphFsZcw84%2F0MOagdiawjbqkPcminXIrSKMkIvwYSw%3D%3D';
// $res = http_query('http://www.dyadko.ru/'.$dataCatUrl, '_ym_uid=1472328117594142991; user_id=0pt77icnfcfjn3j7bfcvfjc352; PHPSESSID=aehak7cbnnuhpnr49f72k9mn17; _ym_isad=2; _ym_visorc_29237600=w');
//
// $alert = preg_match('/Работа сайта ограничена - обратитесь к администрации/', $res);
// // echo($res);die;
// if($alert) die('Alert access denied');
// // echo $res;die;
// $dom2 = new simple_html_dom;
// $dom2->load($res);
// $table = $dom2->find("table[id=PriceHolder]",0);
// if(count($table) < 1) {echo "No table \n";}
// $trss = ($table->find('tr[id*=ptr]'));
// if(count($trss) < 1) {echo "No trss \n";}
// $catQueue = array();
// foreach ($trss as $t) {
// 	// echo '123';
// 	$vendor = strip_tags($t->find('td', 0)->innertext);
// 	$vendor = preg_replace('%&nbsp;| +%', '', $vendor);
// 	var_dump($vendor);
// 	$datail_code = trim(strip_tags($t->find('td', 1)->innertext));
// 	$datail_code = preg_replace('%&nbsp;| +%', '', $datail_code);
// 	// var_dump($datail_code);
// 	$name = trim(strip_tags($t->find('td', 2)->innertext));
// 	$name = preg_replace('%&nbsp;+%', '', $name);
// 	// var_dump($name);
// 	$deliveryDays = $t->find('td', 3)->find('span.cDeliveryDays',0)->innertext;
// 	// var_dump($deliveryDays);
// 	$addDays = $t->find('td', 3)->find('span.cAddDays',0)->innertext;
// 	$addDays = preg_replace('%[^0-9]+%', '', $addDays);
// 	// var_dump($addDays);
// 	$storage = trim($t->find('td', 4)->innertext);
// 	// var_dump($storage);
// 	$actual = trim($t->find('td', 5)->innertext);
// 	// var_dump($actual);
// 	$balance = trim($t->find('td', 6)->innertext);
// 	// var_dump($balance);
// 	$price = trim($t->find('td', 7)->innertext);
// 	$price = preg_replace('%[^0-9]+%', '', $price);
// 	// var_dump($price);
// 	echo "add in array with current cat\n";
// 	$catQueue[] = array(
// 		$cat['id'], ($name ? $name : 'null'), ($vendor ? $vendor : 'null'), ($schema_code ? $schema_code : 'null'),
// 		($price ? $price : 'null'), ($datail_code ? $datail_code : 'null'), ($storage ? $storage : 'null'),
// 		($balance ? $balance : 'null'), ($description ? $description : 'null'), ($deliveryDays ? $deliveryDays : 'null'),   ($addDays ? $addDays : 'null'), ($actual ? $actual : 'null')
// 	);
// }
// var_dump($catQueue);
 ?>
