<?php
####################################################
####################################################
##
##  FILENAME: tgData.php
##
##  DESCRIPTION: 
##    Contains functions for controlling form 
##    for plotting re saved and default parameters
##    (see tgGUI.php).
##
##  REVISIONS:
##    added NAME near matches search (Emma Reishus)
##
####################################################
####################################################
error_reporting( E_ALL );
//ini_set( "display_errors",1);
ob_start( "ob_gzhandler" );

require "tgMainLib.php";
require "tgDatabaseConnect.php";
require "../queryLib/query.php";
require "tgProcessors.php";
require "tgSecondarySearch.php";
require "tgNotifications.php";
require "tgMenu.php";

$query = new Query;

$result = preprocessQueryRequests( $query );

// if returned value set, set array values to be used in possible second search
if( $result ) { 
  $key = $result['targ'];
  $_REQUEST['q'] = $result['qid'];
}

$tableCode = $query->getTableCode();

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<!-- <!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd"> -->
<html>
<head>
<title> TGCat - <?php print getNiceTableNameFromCode( $tableCode );?> table </title> 
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<?php include "styleInc.php" ?>
<script type="text/javascript" src="highlight.js"></script>
<script type="text/javascript" src="datapage.js"></script>
</head>

<body onload='load()'>
<script type="text/javascript" src="wz_tooltip.js"></script>
<script type="text/javascript" src="tip_followscroll.js"></script>
<script type="text/javascript" src="superMessage.js"></script>

<?php
print "<div id='page' style='padding-top:24px;'>\n";
print "<form name='dForm' action='tgData.php?t=AC' method='post' onsubmit='return submitForm(this)'>";
$query->runQuery(); //displays the query results

if ( ! $query->isValid() ){
  if ( $query->isError() ){
    include "tgError.html";
  }
  else { 
    //
    // if user permits in name form, a search for near matches 
    // of the query string will be performed
    //
    if( ! isset( $_POST['matches'] ) ) {
      print "<span id='searching' style='display: none;'>
<i>No results found. Looking for close matches...</i></span>";
      //
      // preform second search for near matches to search terms
      //
      $near_matches = secondarySearch( $key );

      if ( $near_matches==FALSE ) { // no near search terms
	print "<script type='text/javascript'> document.getElementById('searching').style.display = 'none'; </script>";
	include "tgNoResults.html";
      }
      else {
	//
	// create new Query based on list of name(s) returned from the second search
	//
	$new_query = new Query;

	$_POST['targ'] = $near_matches;
	preprocessQueryRequests( $new_query );
	
	$new_query->runQuery(); // displays results of new query

	if( ! $new_query->isValid() ) {
	  if( $new_query->isError() ) {
	    include "tgError.html";
	  }
	  else {
	    include "tgNoResults.html";
	  }
	}
      }
    }
    else {
      include "tgNoResults.html";
    }
  }
}
print "</div>\n";

$cols = $query->getColumns();
$qid = $query->getQueryId();
$cnt = $query->getReturnedRows();

print "<input type='hidden' name='q' value='$qid'>\n";
print "<input type='hidden' name='c' value='$tableCode'>\n";
print "<input type='hidden' name='dlusedjs' value='0'>\n";

initMainMenu();
generateFileMenu();
if ( $query->isValid() ){
  generateDataViewMenu( $qid, $tableCode, $cols );
  generateDataActionMenu( $tableCode );
  generateHelpTopicsMenu();
 }
generateHelpMenu();
//generateQuickSearchBar();
finalizeMainMenu();
print "</form>";

print "<div style='position:fixed;top:30px;background:#ffffbb;color:#884400;padding-top:1px;width:100%;border:0px;border-bottom:1px solid #222222;text-align:center;font-size:24px;font-weight:bold;font-style:italic;opacity:0.85;filter:Alpha(opacity=85);'> ---- currently viewing " . getNiceTableNameFromCode( $tableCode ) . " table ---- </div>";

$notify = $query->getNotifications();
$notify = join( " ; ",$notify );
$myFilter = $query->getFilter();
if ( ! $query->isOriginal() ){
  $notify .= "; selection limited";
 }
if ( $myFilter ){
  $notify .= "; Filtering results on: " . $myFilter;
 }

//
// include the column selector tool for those with
// javascript
//
if ( $tableCode == "o" ){
  generateColumnSelectorExtractions( $qid, $cols, "hidden" );
 }
elseif ( $tableCode == "s" ){
  generateColumnSelectorSource( $qid, $cols, "hidden" );
 }  
//
// create the hidden download window
//
generateDownloadWindow( $qid, $tableCode, "hidden" );
//
// create the hidden tagging window
//
generateTagWindow( $qid, $query->getDescription(), "hidden" );
//
//
//
generateFilterWindow( $qid, $cols, $query->getFilter(), "hidden" );
//
// some messages and/or notifications
//
if ( $_REQUEST['n'] == "DQ" ){
  $pkgid = $_REQUEST['p'];
  $msg = "Your download request has been submitted! Downloads are processed in the order received, An email will be sent to you when packaging is completed indicating the location at which it can be obtained. For your reference your package ID is $pkgid<br>Thank you";
  superMessage( $msg );
  $notify .= "; <img src='image/snotify.gif'> package queued, your pkgid is $pkgid <img src='image/snotify.gif'> <a href='tgStage.php' style='color:#00FFFF'>Go to Download Area</a>";
}
if ( $_REQUEST['n'] == "T" ){
  $msg = "Query tagged";
  superMessage( $msg, 1 );
  $notify .= "; $msg";
}

statusMessage( $notify );

?>

</body>
</html>
