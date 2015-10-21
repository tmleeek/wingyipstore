<?php
// Create Form class
class CreateForm {

  // Declare a class member variable
  var $page;
  //var $url;
  //var $hash;

  // The constructor function
  function CreateForm()
  {
    $this->page = '';
  }

  // Generates the top of the page
  //function setFormValues($termUrl,$md)
  //	{
    
  //	}

  // Generates the top of the page
  function addHeader($title,$acsurl,$termUrl,$md,$pareq)
  {
    $this->page .= <<<EOD
<html>
<head>
<title>$title</title>
<script language="Javascript">
function submitForm(){
document.acsform.submit();
	                 }
</script>
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="-1">
</head>

<!-- <body onLoad="submitForm()"> -->
<body>
<h1 align="center">$title</h1>
<form name="acsform" action=$acsurl method="POST">

<input type="hidden" name="PaReq" value="$pareq">
<input type="hidden" name="TermUrl" value="$termUrl">
<input type="hidden" name="MD" value="$md">
<input type="submit" value="Submit">
EOD;
  }

  // Adds some more text to the page
  function addContent($content)
  {
    $this->page .= $content;
  }

  // Generates the bottom of the page
  function addFooter($year, $copyright)
  {
    $this->page .= <<<EOD
<div align="center">&copy; $year $copyright</div>
</body>
</html>
EOD;
  }

  // Gets the contents of the page
  function get()
  {
    return $this->page;
  }
}