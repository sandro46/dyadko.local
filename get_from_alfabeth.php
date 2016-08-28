<?php

include 'simplehtmldom/simple_html_dom.php';
$dom = new simple_html_dom;
$cats = file_get_contents('alfabet.json');
$cats = json_decode($cats,1);
var_dump($cats);

// $ch = curl_init("http://www.dyadko.ru/webcat.php?ExpandTreeItem");
// curl_setopt($ch, CURLOPT_HEADER, 1); // читать заголовок
// curl_setopt($ch,CURLOPT_COOKIE,'PHPSESSID=ivf4ucpdsdkj0kq7a1ur69od10; _ym_uid=1472328117594142991; _ym_isad=2; _ym_visorc_29237600=w');
// curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:47.0) Gecko/20100101 Firefox/47.0');
// curl_setopt($ch,CURLOPT_REFERER,'http://www.dyadko.ru/');
// curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
// curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
// curl_setopt ($ch, CURLOPT_POST, 1);
// curl_setopt ($ch, CURLOPT_POSTFIELDS, array('id'=>'37331'));
// $result = curl_exec($ch); // выполняем запрос curl
// curl_close($ch);
// echo $result;
 ?>
