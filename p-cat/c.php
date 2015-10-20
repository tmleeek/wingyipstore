<?php
ini_set('display_errors', 1);
ini_set('max_execution_time', 0);

mysql_connect('localhost', 'wingyip_staging', 'jekU3r*b[D') or die('db connect error');
mysql_select_db('magento_wingyipstore_staging') or die('db select error');

function quote($str) {
  return "'" . mysql_real_escape_string($str) . "'";
}

$products   = array();
$categories = array();
$handle = fopen('./description.csv', 'r');

/*
$res = mysql_query("select entity_id from wy_catalog_product_entity sku =  " . quote($mass[0]));

while ($row = mysql_fetch_assoc($res)) {

  $name = trim(preg_replace('/(.*)(\(.*\))$/', '$1', $row['value']));

  $q = "UPDATE wy_catalog_product_entity_text set value = " . quote($name) . " where attribute_id = 71 and entity_id = " . $row['entity_id'];

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }
}
*/

 
while ($mass = fgetcsv($handle)) {

  if (isset($products[$mass[0]])) {
    $productId = $products[$mass[0]];
  } else {
    $res = mysql_query("select entity_id from wy_catalog_product_entity where sku =  " . quote($mass[0]));
    if (!$res) {
      continue;
    }
    $row = mysql_fetch_assoc($res);
    $productId = $row['entity_id'];
    $products[$mass[0]] = $productId;
  }

  if (!$productId) {
    continue;
  }

  /*
  if (isset($categories[$mass[1]])) {
    $categoryId = $categories[$mass[1]];
  } else {
    $res = mysql_query("select entity_id from wy_catalog_category_entity_varchar where attribute_id = 41 and value = " . quote($mass[1]));
    $row = mysql_fetch_assoc($res);
    $categoryId = $row['entity_id'];
    $categories[$mass[1]] = $categoryId;
  }

  $res = mysql_query("select entity_id from wy_catalog_product_entity sku =  " . quote($mass[0]));
  */

  $q = "UPDATE wy_catalog_product_entity_text set value = " . quote($mass[1]) . " where attribute_id = 72 and entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }
}
