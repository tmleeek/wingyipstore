<?

//$md = "orderid=testorder12234&cardnumber=4242424242424242";


//*************Encrypt

$text = "orderid=testorder12234&cardnumber=4242424242424242";

echo 'original text'.$text;



$key = "This is a very secret key";

$iv_size = mcrypt_get_iv_size(MCRYPT_XTEA, MCRYPT_MODE_ECB);
$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
echo strlen($text) . "<br>";

$enc = mcrypt_encrypt(MCRYPT_XTEA, $key, $text, MCRYPT_MODE_ECB, $iv);
//echo strlen($enc) . "  MD STRING Encrypted<br>";
echo $enc . "  Text Encrypted<br>";

$md = base64_encode($text);


//************Decrypt

$md = base64_decode($md);

  
$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
$key = "This is a very secret key";
//$text = "Meet me at 11 o'clock behind the monument.";
//echo strlen($text) . "<br>";

$crypttext = mcrypt_decrypt(MCRYPT_XTEA, $key, $md, MCRYPT_MODE_ECB, $iv);
echo "$crypttext Text Decrypted<br>";



?>