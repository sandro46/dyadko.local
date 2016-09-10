<?php
include 'simplehtmldom/simple_html_dom.php';
include 'config.php';
include 'lib.php';
// var_dump(__DIR__);die;
// header('Content-type	: text/plain');
$dom = new simple_html_dom;
// // PHPSESSID=ivf4ucpdsdkj0kq7a1ur69od10; _ym_uid=1472328117594142991; _ym_isad=2; _ym_visorc_29237600=w
//
$result = http_query('http://www.dyadko.ru/webcat.php');
// echo ($result);die;	
// var_dump($result); die;
$dom->load($result);
$li = $dom->find('div.CGroups ul li');
// var_dump(count($li));die;
// echo
$alfabet = array();
foreach ($li as $l) {
	$alfabet[$l->find('a', 0)->innertext] = $l->find('a', 0)->gid;
}

foreach ($alfabet as $key => $val) {
	// var_dump($val);
	sleep(1);
	$res = http_query("http://www.dyadko.ru/webcat.php?ExpandTreeItem",$post = 1, array('id'=>$val) );
	$res = json_decode($res,1);
	// var_dump($res['message']);
	$dom->load($res['message']);
	$li = $dom->find('li');
	// var_dump($li->innertext);
	$cats = array();
	foreach ($li as $k => $v) {
		// var_dump($v->find('a', 0)->innertext);
			$cats[] = array('href'=>$v->find('a', 0)->href, 'title'=>$v->find('a', 0)->innertext);
	}
	// var_dump($cats);die;
	$alfabet[$key] = array('parent'=>$val,'cats'=>$cats);
	if($key=='M') break;
}

// $alfabet = json_encode($alfabet, JSON_UNESCAPED_UNICODE);
// var_dump($alfabet);
file_put_contents(__DIR__.'/alfabet.json', json_encode($alfabet, JSON_UNESCAPED_UNICODE));
// file_put_contents('cats.json', '123'); die;

// $alfabet = file_get_contents(__DIR__.'/alfabet.json');
// $alfabet = json_decode($alfabet,1);
// var_dump($alfabet);die;

// var_dump($alfabet);die;
$resC = array();
foreach ($alfabet as $key => $val) {
	// key - родительская категория
	// val[cats] - родительская категории
	if(!$val['cats']) continue;
	// var_dump($val['cats']); die;
	foreach ($val['cats'] as $k => $v) {
		$url = TARGET_HOST.$v['href'];
		// var_dump($url);
		preg_match('/webcat\.php\?g\=([0-9]*)/', $url, $cid);
		$cats = array();
		rekursPrase($url,$cats,0,$v['title']);
		// print_r($cats);
		$resC[$cid[1]] = $cats;

		// fwrite($fp, print_r($resC,1));
		// fclose($fp);
		// var_dump($cid[1]);
		// break;
	}
	file_put_contents(__DIR__."/".$key."_counter.json", json_encode($resC,JSON_UNESCAPED_UNICODE));
}


 ?>
