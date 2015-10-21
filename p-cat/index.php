<?php
ini_set('display_errors', 1);
ini_set('max_execution_time', 0);

mysql_connect('localhost', 'wingyip_staging', 'jekU3r*b[D') or die('db connect error');
mysql_select_db('magento_wingyipstore_staging') or die('db select error');

function quote($str) {
  return "'" . mysql_real_escape_string($str) . "'";
}

$products   = array();
$manufactures = array();
$categories = array();
$handle = fopen('./data.csv', 'r');
$c = 0;
while ($mass = fgetcsv($handle)) {

  if (isset($products[$mass[0]])) {
    $productId = $products[$mass[0]];
  } else {
    $res = mysql_query("select entity_id from wy_catalog_product_entity where sku = " . quote(trim($mass[0])));
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

  //$taxClass = trim($mass[26]) == 3 ? 2 : 21;

  $q = "UPDATE  wy_catalog_product_entity_text SET value =  " . quote($mass[12]) . " WHERE attribute_id = 72 and entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }

  $c++;

  /*
  $val = trim(preg_replace('/(\s){2,}/', ' ', $mass[1]));

  if (isset($manufactures[$val])) {
    $manufacturesId = $manufactures[$val];
  } else {
    $res = mysql_query("select option_id from wy_eav_attribute_option_value where value = " . quote($val));
    if (!$res) {
      continue;
    }
    $row = mysql_fetch_assoc($res);
    $manufacturesId = $row['option_id'];
    $manufactures[$val] = $manufacturesId;
  }

  if (!$manufacturesId) {
    continue;
  }

  $q = "UPDATE  wy_catalog_product_entity_int SET value =  " . quote($manufacturesId) . " WHERE entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }
  */

  /*
  $q = "UPDATE  wy_catalog_product_entity_text SET value =  " . quote($mass[3]) . " WHERE attribute_id = 247 and entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }

  $q = "UPDATE  wy_catalog_product_entity_text SET value =  " . quote($mass[4]) . " WHERE attribute_id = 248 and entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }

  $q = "UPDATE  wy_catalog_product_entity_text SET value =  " . quote($mass[5]) . " WHERE attribute_id = 249 and entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }

  $q = "UPDATE  wy_catalog_product_entity_text SET value =  " . quote($mass[6]) . " WHERE attribute_id = 250 and entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }

  $q = "UPDATE  wy_catalog_product_entity_text SET value =  " . quote($mass[7]) . " WHERE attribute_id = 251 and entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }

  $q = "UPDATE  wy_catalog_product_entity_text SET value =  " . quote($mass[8]) . " WHERE attribute_id = 252 and entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }

  $q = "UPDATE  wy_catalog_product_entity_text SET value =  " . quote($mass[9]) . " WHERE attribute_id = 253 and entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }

  $q = "UPDATE  wy_catalog_product_entity_text SET value =  " . quote($mass[13]) . " WHERE attribute_id = 254 and entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }

  $q = "UPDATE  wy_catalog_product_entity_text SET value =  " . quote($mass[14]) . " WHERE attribute_id = 255 and entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }

  $q = "UPDATE  wy_catalog_product_entity_text SET value =  " . quote($mass[15]) . " WHERE attribute_id = 256 and entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }

  $q = "UPDATE  wy_catalog_product_entity_text SET value =  " . quote($mass[16]) . " WHERE attribute_id = 257 and entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }

  $q = "UPDATE  wy_catalog_product_entity_text SET value =  " . quote($mass[17]) . " WHERE attribute_id = 258 and entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }

  $q = "UPDATE  wy_catalog_product_entity_text SET value =  " . quote($mass[18]) . " WHERE attribute_id = 259 and entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }

  $q = "UPDATE  wy_catalog_product_entity_text SET value =  " . quote($mass[19]) . " WHERE attribute_id = 260 and entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }

  $q = "UPDATE  wy_catalog_product_entity_text SET value =  " . quote($mass[20]) . " WHERE attribute_id = 261 and entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }

  $q = "UPDATE  wy_catalog_product_entity_text SET value =  " . quote($mass[21]) . " WHERE attribute_id = 262 and entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }

  $q = "UPDATE  wy_catalog_product_entity_text SET value =  " . quote($mass[22]) . " WHERE attribute_id = 263 and entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }

  $q = "UPDATE  wy_catalog_product_entity_text SET value =  " . quote($mass[23]) . " WHERE attribute_id = 264 and entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }

  $q = "UPDATE  wy_catalog_product_entity_text SET value =  " . quote($mass[24]) . " WHERE attribute_id = 265 and entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }

  $q = "UPDATE  wy_catalog_product_entity_text SET value =  " . quote($mass[25]) . " WHERE attribute_id = 266 and entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }

  $q = "UPDATE  wy_catalog_product_entity_text SET value =  " . quote($mass[26]) . " WHERE attribute_id = 267 and entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }

  $q = "UPDATE  wy_catalog_product_entity_text SET value =  " . quote($mass[28]) . " WHERE attribute_id = 269 and entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }

  $q = "UPDATE  wy_catalog_product_entity_text SET value =  " . quote($mass[29]) . " WHERE attribute_id = 270 and entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }
  */

  /*
  $q = "UPDATE  wy_catalog_product_entity_decimal SET value =  " . (float)$mass[3] . " WHERE attribute_id = 75 and entity_id = $productId";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }
  */

  /*
  if (isset($categories[$mass[1]])) {
    $categoryId = $categories[$mass[1]];
  } else {
    $res = mysql_query("select entity_id from wy_catalog_category_entity_varchar where attribute_id = 41 and value = " . quote($mass[1]));
    $row = mysql_fetch_assoc($res);
    $categoryId = $row['entity_id'];
    $categories[$mass[1]] = $categoryId;
  }

  $q = "INSERT IGNORE INTO wy_catalog_product_entity_decimal values (null, 4, 80, 0, " . $productId . ", " . $mass[1] . ")";

  mysql_query($q);

  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }
  */

  //$q = "UPDATE  wy_catalog_product_entity SET sku =  " . $mass[1] . " WHERE entity_id = $productId";

  //mysql_query($q);

  /*
  if (mysql_error()) {
    die (mysql_error() . '<br>the query was<br>' . $q);
  }
  */

}
echo $c;
