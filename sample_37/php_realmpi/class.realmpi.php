<?php

class Realex {


	function createRequest($array) {
	global $xml;
	
	/* defaults (there is no default for 'url' or 'content') */

	/* for each argument set in the array, overwrite default */
	while(list($k,$v) = each($array)) {
		$$k = $v;
	}

	$parentElements = array();
	$TSSChecks = array();
	$currentElement = 0;
	$currentTSSCheck = "";

	$timestamp = strftime("%Y%m%d%H%M%S");
	mt_srand((double)microtime()*1000000);

// creating the hash.
	$tmp = "$timestamp.$merchantid.$orderid.$amount.$currency.$cardnumber";
	$md5hash = md5($tmp);
	$tmp = "$md5hash.$secret";
	$md5hash = md5($tmp);

// generate the request xml.
	$xml = "<request type='auth' timestamp='$timestamp'>
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
				<autosettle flag='$autosettleflag'/>
				<md5hash>$md5hash</md5hash>
				<tssinfo>
					<address type='billing'>
						<country>ie</country>
					</address>
				</tssinfo>
			</request>";

}

function send() {
	global $xml;
	$RealMPIURL = "https://epage.payandshop.com/epage-remote.cgi";

// send it to payandshop.com
	echo "/home/owen/tgz/curl-7.9.2/src/curl -s -m 120 -d \"$xml\" $RealMPIURL -L";
	exec("/home/owen/tgz/curl-7.9.2/src/curl -s -m 120 -d \"$xml\" $RealMPIURL -L", $return_message_array, $return_number);
	for ($i = 0; $i < count($return_message_array); $i++) {
   		$response = $response.$return_message_array[$i];
	}
	echo $response;

}

function jjj() {

// fire up the xml parser...
$xml_parser = xml_parser_create();
xml_set_element_handler($xml_parser, "startElement", "endElement");
xml_set_character_data_handler($xml_parser, "cDataHandler");


// or the integrated cURL way.... (if you've compiled cURL into PHP)
//$ch = curl_init();
//curl_setopt($ch, CURLOPT_URL, "https://epage.payandshop.com/epage-remote.cgi");
//curl_setopt($ch, CURLOPT_POST, 1);
//curl_setopt($ch, CURLOPT_USERAGENT, "payandshop.com php version 0.9");
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
//$response = curl_exec ($ch);
//curl_close ($ch);

// fix it up good.
$response = eregi_replace ( "[[:space:]]+", " ", $response );
$response = eregi_replace ( "[\n\r]", "", $response );

// parse the response xml
if (!xml_parse($xml_parser, $response)) {
die(sprintf("XML error: %s at line %d",
xml_error_string(xml_get_error_code($xml_parser)),
xml_get_current_line_number($xml_parser)));
}

print $TSSChecks["3202"];

// garbage collect the parser.
xml_parser_free($xml_parser);

}


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

}

?>
