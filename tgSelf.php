<?php
/*
Copyright (C) 2010 Massachusetts Institute of Technology 

This software was developed by the MIT Kavli Institute for
Astrophysics and Space Research under contract SV3-73016 from the
Smithsonian Institution.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA
*/


  //error_reporting( E_ALL );
  //ini_set( "display_errors",1);
require "tgMenu.php";
require "tgMainLib.php";
require "tgNotifications.php";
require "tgDatabaseConnect.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html>

<head>
<title> My TGCat</title> 
<?php include "styleInc.php"; ?>
</head>

<body>

<div id='page'>
<h1>Your Recent Queries</h1>
<?php
  //$myQids = split( ",", $_COOKIE['qids'] );
$myCurrQid = $_COOKIE['myCurrQid'];
$myQids = $_COOKIE['qids'];
$myQids = split( ",", $myQids );
if ( $myCurrQid && ! in_array( $myCurrQid, $myQids ) ){
  array_push( $myQids, $myCurrQid );
}
$myQidsString ="'". implode( "','", $myQids ) . "'";

$q = "SELECT * FROM query WHERE qid in ( $myQidsString ) ORDER BY iqid DESC";
$q = mysql_query( $q );

$rowicon = "<img style='position:relative;border:0px;top:2px;padding-right:3px;padding-left:3px'";
print "<div id='downloadSummary'>\n";
print "<center>\n";
print "<table id='downloadTable'>\n";
print "<tr><th title='unique identifier for query'>qid</th><th title='time query was created'>queue time</th><th title='type of query, see help documentation'>type</th><th title='current table for query ( s=source, o=extractions )'>table</th><th title='user description of query ( Actions->Tag query )'>description</th><th title='number of records returned'>num recs</th></tr>\n";
$count = 0;
while ( $row = mysql_fetch_assoc( $q ) ){
  $tableCode = $row['ctc'];
  if ( $row["${tableCode}ids"] ){
    $num_recs = count( split( ",", $row["${tableCode}ids"] ) );
  }
  else {
    continue;
    $num_recs = "<font style='color:#ff0000'>0</font>";
  }
  $description=$row['description'];
  if ( ! $description ){ $description = "-"; }
  if ( strlen( $description ) > 40 ){ $description = "<a title='$description'>". substr( $description,0,40 ) . "...</a>"; }
  if ( $row['qid'] == $myCurrQid ){
    print "<tr class='mydownload'>";
  }
  else {
    print "<tr>\n";
  }
  print "<td style='text-align:left;padding-right:0px;'><a href='tgData.php?q=$row[qid]'>$rowicon src='image/search.png'> $row[iqid] </a></td>\n";
  print "<td> $row[time] </td>\n";
  print "<td> $row[type] </td>\n";
  print "<td> $row[ctc] </td>\n";
  print "<td> $description </td>\n";
  print "<td> $num_recs </td>\n";
  print "</tr>\n";
  $count ++;
 }

print "</table>";
print "</center>";
print "</div>";
  
?>
</div>

<?php
initMainMenu();
generateFileMenu();
generateHelpMenu();
generateQuickSearchBar();
finalizeMainMenu();

statusMessage( "showing $count most recent queries" );
?>

</body>
</html>
