<?php
// php -c D:\openserver\OpenServer\modules\php\PHP-5.6\php.ini parse_content.php
include 'simplehtmldom/simple_html_dom.php';
include 'config.php';
include 'lib.php';
$dom = new simple_html_dom;

$sql = "UPDATE `category` c
set batch_id = null
WHERE batch_id is not null and date_parsed is null";
db()->query($sql);

$sql = "SELECT *
        FROM  account
        WHERE last_used < DATE_ADD(current_timestamp, INTERVAL 1800 SECOND)
        ORDER BY last_used
        LIMIT 1";
$cookie = db()->query($sql)->fetch();
// echo $sql."\n";
// $cookie = $cookie[0];
// var_dump($cookie['cookie']); die;
$sql = "UPDATE account
        SET last_used = current_timestamp
        WHERE id = {$cookie['id']}";
// echo $sql;
db()->query($sql);
if(!$cookie){die('No avalible account at the moment');}
// var_dump($cookie); die;
// блок загрузки контента категорий в БД
$cntParse = 1;
// $sql = "SELECT * FROM category WHERE iscontent='Y'";
$batch_id = time();
$batch_id = substr((string)$batch_id, -6);
// die;

// $sql = "UPDATE category
//         SET batch_id='$batch_id'
//         where id IN(
//           select t.id from (
//               select c.id from category c
//               LEFT JOIN item i ON i.cid = c.id
//               where c.iscontent='Y'
//                     and i.cid is null
//                     and c.date_parsed is null
//                     and c.img_uri is null
//                     and batch_id is null
//           ) t
//         )LIMIT 100";


$sql = "UPDATE category
        SET batch_id='$batch_id'
        where id IN(
          select t.id from (
            select tc.id from tmp_cat tc
            join category c ON tc.id=c.id
            where c.batch_id is null
                  and c.date_parsed is null
                  and c.iscontent='Y'
          ) t
        )LIMIT 10";
// echo $sql."\n";
$r = db()->query($sql);
echo "batch id is $batch_id \n";

$sql = "SELECT c.*
        FROM category c
        where batch_id = '$batch_id'";
$stmt = db()->query($sql);
$cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
// var_dump($cats); die;
if($cats) foreach($cats AS $cat){
  // var_dump($cat);
  if($cntParse >= 300) die("Limit parse has been exceeding.");
  sleep(1);
  $result = http_query('http://www.dyadko.ru/webcat.php?g='.$cat['id'], $cookie['cookie']);
  echo date('d.m.Y H:i:s')."\n\r";
  var_dump('http://www.dyadko.ru/webcat.php?g='.$cat['id']);
  $dom->load($result);
  $img = $dom->find('#pimg',0)->src;
  // var_dump(preg_match('/webcat/', $img));
  if(!preg_match('/webcat/', $img)){ continue; }
  $stmt = db()->prepare("UPDATE category SET img_uri=? WHERE id=?");
  $stmt->execute(array($img,$cat['id']));
  $trs = $dom->find('table.tbl tr[name]');
  if(count($trs) < 1) {echo "No trs \n";continue;}
  foreach($trs as $tr){
    $schema_code = ($tr->find('td', 1)->innertext);
    $dataCatUrl = ($tr->find('td', 2)->find('a',0)->href);
    $description = $tr->find('td', 3)->innertext;
    // var_dump($dataCatUrl);
    sleep(1);
    $res = http_query('http://www.dyadko.ru/'.$dataCatUrl, $cookie['cookie']);
    $cntParse++;
    $alert = preg_match('/Работа сайта ограничена - обратитесь к администрации/', $res);
    // var_dump($alert);
    if($alert) die('Alert access denied');
    // echo $res;die;
    $dom2 = new simple_html_dom;
    $dom2->load($res);
    $table = $dom2->find("table[id=PriceHolder]",0);
    if(count($table) < 1) {echo "No table \n";continue;}
    $trss = ($table->find('tr[id*=ptr]'));
    if(count($trss) < 1) {echo "No trss \n";continue;}
    // var_dump(count($trss));die;
    $catQueue = array();
    foreach ($trss as $t) {
      $vendor = strip_tags($t->find('td', 0)->innertext);
      $vendor = preg_replace('%&nbsp;| +%', '', $vendor);
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
      echo "add in array with current cat\n";
      $catQueue[] = array(
        $cat['id'], ($name ? $name : 'null'), ($vendor ? $vendor : 'null'), ($schema_code ? $schema_code : 'null'),
        ($price ? $price : 'null'), ($datail_code ? $datail_code : 'null'), ($storage ? $storage : 'null'),
        ($balance ? $balance : 'null'), ($description ? $description : 'null'), ($deliveryDays ? $deliveryDays : 'null'),   ($addDays ? $addDays : 'null'), ($actual ? $actual : 'null')
      );


    }
    echo "add content in DB\n";
    $sql = "INSERT INTO item
            (cid, name, vendor, schema_code, price, detail_code, storage, balance, description, delivery_days, add_days, actual_date)
            VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = db()->prepare($sql);
    foreach ($catQueue as $params) {
      $stmt->execute($params);
    }

  }

  echo "Mark parsed catewgory\n";
  $sql = "UPDATE category
          SET date_parsed = current_timestamp
          WHERE id = '{$cat['id']}'";
  $r = db()->query($sql);


}
echo "End programm\n";
 ?>
