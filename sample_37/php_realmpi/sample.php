<?php

include "class.realmpi.php";

$realex = new Realex;

$realex->createRequest(array(
	"merchantid" => "ccentre",
	"secret" => "secret",
	"account" => "call centre",
	"orderid" => "owentestphp-0701-01",
	"amount" => "2000",
	"currency" => "EUR",
	"cardnumber" => "4242424242424242",
	"cardname" => "Owen O Byrne",
	"cardtype" => "visa",
	"expdate" => "0104",
	"autosettleflag" => "1",
));

$response = $realex->send();

?>


