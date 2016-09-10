<?php

function db(){
	try {
	  $db = new PDO("mysql:host=localhost;dbname=dyadko", 'root', '');
	  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	  $db->exec("set names utf8");
		$db->exec("SET CHARACTER SET utf8");
	}
	catch(PDOException $e) {
	    echo $e->getMessage();
	}
	return $db;
}

function dom(){
	if(!$GLOBALS['dom']) $GLOBALS['dom'] = new simple_html_dom;
	return $GLOBALS['dom'];
}

function http_query($url, $cookie = null,  $post = 0, $postfields = null, $referer = 'http://www.dyadko.ru/'  ){
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, 0); // читать заголовок
	curl_setopt($ch,CURLOPT_COOKIE,$cookie);
	curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:47.0) Gecko/20100101 Firefox/47.0');
	curl_setopt($ch,CURLOPT_REFERER,$referer);
	curl_setopt ($ch, CURLOPT_POST, $post);
	curl_setopt ($ch, CURLOPT_POSTFIELDS, $postfields);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,5);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
	$result = curl_exec($ch); // выполняем запрос curl
	curl_close($ch);
	return $result;
}


function rekursPrase($url=null, &$cats, $parent, $title = null, $iscontent = null){
	// var_dump(123);die;
	$dom = new simple_html_dom;
	$url = str_replace('"', '', $url);
	$url = str_replace('\\', '', $url);
	preg_match('/webcat\.php\?g\=([0-9]*)/', $url, $cid);
	$curr = $cid[1];
	$cats[] = array('pid'=>$parent, 'cid'=>$curr, 'title'=>$title, 'iscontent'=>$iscontent ? 'Y' : 'N');
	if($iscontent) return;
	// if(!$cats){
	// 	$cats[$pid] = array();
	// 	// var_dump($cid); die;
	// }
	// var_dump($url);
	// echo($url."\n\r");
	$html = http_query($url); sleep(1);
	if(strlen($html)>1000) $dom->load($html); else {continue;}

	if($dom->find('div.CGroups ul li', 0)->innertext){
		// echo($url."\n\r");
		$li = $dom->find('div.CGroups ul li');
		// echo 1;die;
		// var_dump($divCat->find('ul li a')->innertext);
		foreach ($li as $key => $val) {
			var_dump($val->innertext);
			if($val->innertext){
				$title = $val->find('a', 0)->innertext;
				$href = $val->find('a', 0)->href;
				// rekursPrase($url)
				if($href) $href = TARGET_HOST.$href; else return;
				// var_dump($href);
				// echo 1;die;
				preg_match('/webcat\.php\?g\=([0-9]*)/', $href, $cid);
				// $cats[$pid][]=$cid[1];
				// echo($cid[1]."\n\r");
				// var_dump($href);
				$html2 = http_query($href);sleep(1);
				if(strlen($html2)<1000) continue;
				$dom2 = new simple_html_dom;
				$dom2->load($html2);
				if($dom2->find('table.tbl',0)->class) $iscont = 1;
				rekursPrase($href, $cats, $curr, $title, $iscont);
				// return;
			}
		}
		// return;
	}
}

function getChildCat($parent, $all, &$res){
	foreach($all as $v){
		if($v['pid']==$parent ){
			// var_dump($v['iscontent']);die;
			if($v['iscontent']=='Y') $res[] = $v["id"];
			getChildCat($v['id'], $all, $res);
		}
	}
}

 ?>
