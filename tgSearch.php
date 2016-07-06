<?php
  //error_reporting( E_ALL );
  //ini_set( "display_errors",1);
require "tgMenu.php";
require "tgMainLib.php";
require "tgNotifications.php";
//
// get the search type
//
$searchTypes = $_REQUEST['t'];
$searchTypes = str_split( $searchTypes );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html>

<head>
<title> TGCat - Search</title> 
<?php include "styleInc.php"; ?>
</head>

<body>

<div id='page'>
<form name='search' action='tgData.php?t=NQ' method='post' enctype='multipart/form-data' onSubmit='return validate()'>
<input type='hidden' name='t' value='NQ'>
<?php

foreach ( $searchTypes as $t ){
  if ( $t == "N" ){
    include "search/tgNameSearch.php";
  }
  elseif ( $t == "T" ){
    include "search/tgTypeSearch.php";
  }
  elseif ( $t == "O" ){
    include "search/tgObsidSearch.php";
  }
  elseif ( $t == "S" ){
    include "search/tgSpecpropSearch.php";
  }
  elseif ( $t == "D" ){
    include "search/tgArbitrarySearch.php";
  }
  elseif ( $t == "X" ){
    include "search/tgArbitrarySourceSearch.php";
  }
  elseif ( $t == "C" ){
    include "search/tgCoordSearch.php";
  }
  else {
    include "search/tgSearchLinks.php";
  }
}
if ( $t ){
print "
<div class='searchSubmit'>
<input  id='bs' type='submit'>
<input  id='bc' type='reset' >
</div>
";
 }
?>

<br>

</form>
</div>

<?php
initMainMenu();
generateFileMenu();
generateHelpMenu();
generateQuickSearchBar();
generateHelpTopicsMenu();
finalizeMainMenu();

statusMessage( "search the database for sources or extractions" );
?>

</body>
</html>
