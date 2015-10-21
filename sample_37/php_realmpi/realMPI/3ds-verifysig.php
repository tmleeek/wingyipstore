<?
/*

Pay and Shop Limited (payandshop.com) - Licence Agreement.
© Copyright and zero Warranty Notice.


Merchants and their internet, call centre, and wireless application
developers (either in-house or externally appointed partners and
commercial organisations) may access payandshop.com technical
references, application programming interfaces (APIs) and other sample
code and software ("Programs") either free of charge from
www.payandshop.com or by emailing info@payandshop.com. 

payandshop.com provides the programs "as is" without any warranty of
any kind, either expressed or implied, including, but not limited to,
the implied warranties of merchantability and fitness for a particular
purpose. The entire risk as to the quality and performance of the
programs is with the merchant and/or the application development
company involved. Should the programs prove defective, the merchant
and/or the application development company assumes the cost of all
necessary servicing, repair or correction.

Copyright remains with payandshop.com, and as such any copyright
notices in the code are not to be removed. The software is provided as
sample code to assist internet, wireless and call center application
development companies integrate with the payandshop.com service.

Any Programs licensed by Pay and Shop to merchants or developers are
licensed on a non-exclusive basis solely for the purpose of availing
of the Pay and Shop payment solution service in accordance with the
written instructions of an authorised representative of Pay and Shop
Limited. Any other use is strictly prohibited.

Dated October 2001.

*/

$pasres = $_POST['PaRes'];
$md = $_POST['MD'];

//-----Base 64 Decode and Decrypt $md here

$valuearray = split("&",$md);

foreach ($valuearray as $postvalue) {
   list($field,$content) = split("=",$postvalue);
   echo "Field: $field <br />\n";
   echo "Content: $content <br />\n";
   $formatarray[$field] = $content;
}

echo $formatarray['orderid'];
echo $formatarray['cardnumber'];


$parentElements = array();
$TSSChecks = array();
$currentElement = 0;
$currentTSSCheck = "";

$currency = $formatarray['currency'];
$amount = $formatarray['amount'];
$cardnumber = $formatarray['cardnumber'];
$cardname = $formatarray['cardname'];
$cardtype = $formatarray['cardtype'];
$expdate = $formatarray['expdate'];
$orderid = $formatarray['orderid'];
echo "Expiry Date $expdate<br>";
echo "Card Name $cardname<br>";
// change these to the ones supplied by payandshop.com 
$merchantid = "";
$secret = "";
$account = "";

//$RESPONSE_TIMESTAMP="";


$timestamp = strftime("%Y%m%d%H%M%S");
mt_srand((double)microtime()*1000000);

// creating the hash.
$tmp = "$timestamp.$merchantid.$orderid.$amount.$currency.$cardnumber";
$md5hash = md5($tmp);
$tmp = "$md5hash.$secret";
$md5hash = md5($tmp);

// fire up the xml parser...
$xml_parser = xml_parser_create();
xml_set_element_handler($xml_parser, "startElement", "endElement");
xml_set_character_data_handler($xml_parser, "cDataHandler");


// generate the request xml.
$xml = "<request type='3ds-verifysig' timestamp='$timestamp'>
	<merchantid>$merchantid</merchantid>
	<account>$account</account>
	<orderid>$orderid</orderid>
	<amount currency='$currency'>$amount</amount>
	<card> 
		<number>$cardnumber</number>
		<expdate>$expdate</expdate>
		<type>$cardtype</type> 
		<chname>$cardname</chname> 
	</card>
	<autosettle flag='1'/>
	<md5hash>$md5hash</md5hash>
	<tssinfo>
		<address type=\"billing\">
			<country>ie</country>
		</address>
	</tssinfo>
        <pares>$pasres</pares>
</request>";
    

// send it to payandshop.com
$ch = curl_init();    
curl_setopt($ch, CURLOPT_URL, "https://epage.payandshop.com/epage-3dsecure.cgi");
curl_setopt($ch, CURLOPT_POST, 1); 
curl_setopt($ch, CURLOPT_USERAGENT, "payandshop.com php version 0.9"); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // this line makes it work under https
$response = curl_exec ($ch);     
curl_close ($ch); 

// fix it up good.
$response = eregi_replace ( "[[:space:]]+", " ", $response );
$response = eregi_replace ( "[\n\r]", "", $response );

// parse the response xml
if (!xml_parse($xml_parser, $response)) {
    die(sprintf("XML error: %s at line %d",
           xml_error_string(xml_get_error_code($xml_parser)),
           xml_get_current_line_number($xml_parser)));
}

//print $TSSChecks["3202"];

// garbage collect the parser.
xml_parser_free($xml_parser);



// startElement() - called when an open element tag is found.
// creates a variable on the fly contructed of all the parent elements
// joined together with an underscore. So the following xml:
//
// <response><something>Owen</something></response>
//
// would create two variables:
// $RESPONSE and $RESPONSE_SOMETHING

function startElement($parser, $name, $attrs) {
    global $parentElements;
	global $currentElement;
	global $currentTSSCheck;
	
	array_push($parentElements, $name);
	$currentElement = join("_", $parentElements);

	foreach ($attrs as $attr => $value) {
		if ($currentElement == "RESPONSE_TSS_CHECK" and $attr == "ID") {
			$currentTSSCheck = $value;
		}

		$attributeName = $currentElement."_".$attr;
		// print out the attributes..
		//print "$attributeName\n";

		global $$attributeName;
		$$attributeName = $value;
	}

	// uncomment this line to see the names of all the variables you can 
	// see in the response.
	// print $currentElement;

}

// cDataHandler() - called when the parser encounters any text that's 
// not an element. Simply places the text found in the variable that 
// was last created. So using the XML example above the text "Owen"
// would be placed in the variable $RESPONSE_SOMETHING

function cDataHandler($parser, $cdata) {
	global $currentElement;
	global $currentTSSCheck;
	global $TSSChecks;

	if ( trim ( $cdata ) ) { 
		if ($currentTSSCheck != 0) {
			$TSSChecks["$currentTSSCheck"] = $cdata;
		}

		global $$currentElement;
		$$currentElement = $cdata;
	}
	
}

// endElement() - called when the closing tag of an element is found. 
// just removes that element from the array of parent elements.

function endElement($parser, $name) {
    global $parentElements;
	global $currentTSSCheck;

	$currentTSSCheck = 0;
	array_pop($parentElements);
}

?>

Timestamp: <? echo $RESPONSE_TIMESTAMP ?>
<br>
Result: <? echo $RESPONSE_RESULT ?>
<br>
Message: <? echo $RESPONSE_MESSAGE ?>
<br>
STATUS: <? echo $RESPONSE_THREEDSECURE_STATUS ?>
<br>
<?
$cavv = $RESPONSE_THREEDSECURE_CAVV;
$xid = $RESPONSE_THREEDSECURE_XID;
$eci = $RESPONSE_THREEDSECURE_ECI;
$result = $RESPONSE_THREEDSECURE_STATUS;
echo $result;
 

if (($RESPONSE_RESULT == "00") && ($RESPONSE_THREEDSECURE_STATUS == "A"))
{
echo 'The PaRes Validated but the cardholder was not enrolled - There is a liability shift';
echo '<br>';
echo 'Will go ahead with the auth....';
echo '<br>';
}

$timestamp = strftime("%Y%m%d%H%M%S");
mt_srand((double)microtime()*1000000);

// I'm using a random number as the orderid - you probably have a better way.
//$orderid = $timestamp."-".mt_rand(1, 999);
//$orderid2 = "testMPI1";

// creating the hash.
$tmp = "$timestamp.$merchantid.$orderid.$amount.$currency.$cardnumber";
$md5hash = md5($tmp);
$tmp = "$md5hash.$secret";
$md5hash = md5($tmp);

// fire up the xml parser...
$xml_parser = xml_parser_create();
xml_set_element_handler($xml_parser, "startElement", "endElement");
xml_set_character_data_handler($xml_parser, "cDataHandler");


// generate the request xml.
$xml = "<request timestamp='$timestamp' type='auth'>
        <merchantid>$merchantid</merchantid>
        <account>$account</account>
        <orderid>$orderid</orderid>
        <amount currency='$currency'>$amount</amount>
        <card>
                <number>$cardnumber</number>
                <expdate>$expdate</expdate>
                <type>$cardtype</type>
                <chname>$cardname</chname>
        </card>
        <mpi>
        <cavv>$cavv</cavv>
        <xid>$xid</xid>
        <eci>$eci</eci>
        </mpi>
        <autosettle flag='1'/>
        <md5hash>$md5hash</md5hash>
        <tssinfo>
                <address type=\"billing\">
                        <country>ie</country>
                </address>
        </tssinfo>
        <pares>$pasres</pares>
</request>";

// send it to payandshop.com
$ch = curl_init();    
curl_setopt($ch, CURLOPT_URL, "https://epage.payandshop.com/epage-remote.cgi");
curl_setopt($ch, CURLOPT_POST, 1); 
curl_setopt($ch, CURLOPT_USERAGENT, "payandshop.com php version 0.9"); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // this line makes it work under https
$response = curl_exec ($ch);     
curl_close ($ch); 

// fix it up good.
$response = eregi_replace ( "[[:space:]]+", " ", $response );
$response = eregi_replace ( "[\n\r]", "", $response );

// parse the response xml
if (!xml_parse($xml_parser, $response)) {
    die(sprintf("XML error: %s at line %d",
           xml_error_string(xml_get_error_code($xml_parser)),
           xml_get_current_line_number($xml_parser)));
}

// garbage collect the parser.
xml_parser_free($xml_parser);

?>

Timestamp: <? echo $RESPONSE_TIMESTAMP ?>
<br>
Result: <? echo $RESPONSE_RESULT ?>

