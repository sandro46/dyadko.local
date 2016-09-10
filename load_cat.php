<?php
include 'simplehtmldom/simple_html_dom.php';
include 'config.php';
include 'lib.php';



//блок закачки категорий в БД
$stmt = db()->prepare("INSERT INTO category (id,pid,name,iscontent) VALUES (?,?,?,?)");
$cats = file_get_contents( __DIR__.'/B_counter.json');
$cats = json_decode($cats,1);
// var_dump(count($cats));
  // include __DIR__.'/cats.php';

  foreach($cats as $item){
    var_dump(count($item));
    foreach($item as $key=>$val){
      // if($val['iscontent']=='N') continue;
      // var_dump($val);
      $stmt -> execute(array($val['cid'],$val['pid'],$val['title'],$val['iscontent']));
      // break;
    }
    // break;
    //
  }

 ?>
