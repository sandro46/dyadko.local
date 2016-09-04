<?php
// php -c D:\openserver\OpenServer\modules\php\PHP-5.6\php.ini D:\openserver\OpenServer\domains\dblock.local\parse_content.php
include 'simplehtmldom/simple_html_dom.php';
include 'config.php';
include 'lib.php';
$dom = new simple_html_dom;
//блок закачки категорий в БД
// $stmt = db()->prepare("INSERT INTO category (id,pid,name,iscontent) VALUES (?,?,?,?)");
//
//   include __DIR__.'/cats.php';
//
//   foreach($cats as $item){
//     foreach($item as $key=>$val){
//       // if($val['iscontent']=='N') continue;
//       // var_dump($val);
//       $stmt -> execute(array($val['cid'],$val['pid'],$val['title'],$val['iscontent']));
//       // break;
//     }
//     // break;
//   }

// блок загрузки контента категорий в БД
$cntParse = 1;
// $sql = "SELECT * FROM category WHERE iscontent='Y'";
$sql = "SELECT c.*
        FROM category c
        LEFT JOIN item i ON i.cid = c.id
        where i.cid is null
          and iscontent='Y'
        	and img_uri is null";
$stmt = db()->query($sql);

foreach($stmt->fetchAll() AS $cat){
  // var_dump($cat);
  if($cntParse >= 200) die("Limit parse has been exceeding.");
  sleep(1);
  $result = http_query('http://www.dyadko.ru/webcat.php?g='.$cat['id']);
  var_dump('http://www.dyadko.ru/webcat.php?g='.$cat['id']);
  $dom->load($result);
  $img = $dom->find('#pimg',0)->src;
  // var_dump(preg_match('/webcat/', $img));
  if(!preg_match('/webcat/', $img)){ continue; }
  // $stmt = db()->prepare("UPDATE category SET img_uri=? WHERE id=?");
  // $stmt->execute(array($img,$cat['id']));
  $trs = $dom->find('table.tbl tr[name]');
  if(count($trs) < 1) continue;
  foreach($trs as $tr){
    $schema_code = ($tr->find('td', 1)->innertext);
    $dataCatUrl = ($tr->find('td', 2)->find('a',0)->href);
    $description = $tr->find('td', 3)->innertext;
    // var_dump($dataCatUrl);
    sleep(1);
    $res = http_query('http://www.dyadko.ru/'.$dataCatUrl);
    $cntParse++;
    $alert = preg_match('/Работа сайта ограничена - обратитесь к администрации/', $res);
    // var_dump($alert);
    if($alert) die('Alert access denied');
    // echo $res;die;
    $dom2 = new simple_html_dom;
    $dom2->load($res);
    $table = $dom2->find("table[id=PriceHolder]",0);
    if(count($table) < 1) continue;
    $trss = ($table->find('tr[id*=ptr]'));
    if(count($trss) < 1) continue;
    // var_dump(count($trss));die;
    $catQueue = array();
    foreach ($trss as $t) {
      $vendor = strip_tags($t->find('td', 0)->innertext);
      $vendor = $datail_code = preg_replace('%&nbsp;| +%', '', $vendor);
      // var_dump($vendor);
      $datail_code = trim(strip_tags($t->find('td', 1)->innertext));
      $datail_code = preg_replace('%&nbsp;| +%', '', $datail_code);
      // var_dump($datail_code);
      $name = trim(strip_tags($t->find('td', 2)->innertext));
      $name = preg_replace('%&nbsp;+%', '', $name);
      // var_dump($name);
      $deliveryDays = $t->find('td', 3)->find('span.cDeliveryDays',0)->innertext;
      // var_dump($deliveryDays);
      $addDays = $t->find('td', 3)->find('span.cAddDays',0)->innertext;
      $addDays = preg_replace('%[^0-9]+%', '', $addDays);
      // var_dump($addDays);
      $storage = trim($t->find('td', 4)->innertext);
      // var_dump($storage);
      $actual = trim($t->find('td', 5)->innertext);
      // var_dump($actual);
      $balance = trim($t->find('td', 6)->innertext);
      // var_dump($balance);
      $price = trim($t->find('td', 7)->innertext);
      $price = preg_replace('%[^0-9]+%', '', $price);
      // var_dump($price);
      $catQueue[] = array(
        $cat['id'], ($name ? $name : 'null'), ($vendor ? $vendor : 'null'), ($schema_code ? $schema_code : 'null'),
        ($price ? $price : 'null'), ($datail_code ? $datail_code : 'null'), ($storage ? $storage : 'null'),
        ($balance ? $balance : 'null'), ($description ? $description : 'null'), ($deliveryDays ? $deliveryDays : 'null'),   ($addDays ? $addDays : 'null'), ($actual ? $actual : 'null')
      );


    }
    $sql = "INSERT INTO item
            (cid, name, vendor, schema_code, price, detail_code, storage, balance, description, delivery_days, add_days, actual_date)
            VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = db()->prepare($sql);
    foreach ($catQueue as $params) {
      $stmt->execute($params);
    }

  }
}

 ?>
