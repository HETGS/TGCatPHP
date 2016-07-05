<?php
ob_start( "ob_gzhandler" );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title> TGCat - Download Stage Area </title> 
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<meta http-equiv="refresh" content="30">
<?php include "styleInc.php" ?>
</head>

<body>
<script type="text/javascript" src="wz_tooltip.js"></script>
<script type="text/javascript" src="tip_followscroll.js"></script>
<script type="text/javascript" src="superMessage.js"></script>
<?php
 //error_reporting( E_ALL );
 //ini_set( "display_errors",1);

require "tgDatabaseConnect.php";
require "tgNotifications.php";
require "tgMenu.php";

$STATUS_COLORS = Array( "ERROR" => "#FF0000",
			"PROCESSING" => "#cc4400",
			"COMPLETED" => "#33aa00",
			"HOLD" => "#FFAA00",
			"QUEUED" => "#39B1FF",
			"WAITING" => "#39B1FF",
			);

$mydownloads = split( ",",$_COOKIE['tgpkgs'] );
$rowicon = "<img style='position:relative;border:0px;top:2px;padding-right:3px;padding-left:3px'";

print "<div id='page'>\n";
print "<h1> Current Available Packages</h1>";
$q = 'select 
group_concat( distinct( o.object) order by o.object asc separator ", " ) as objects,
group_concat( distinct( o.obsid ) order by o.obsid asc separator ", " ) as obsids,
group_concat( distinct( k.types ) order by k.types asc separator ", " ) as types,
q.*,
date_format( q.queue_time, "%m/%d/%y-%H:%i" ) as queue_time
from pkgque as q 
left join pkgkart as k using( pkgid ) 
left join obsview as o using( id ) 
where status in ( "ERROR","OVERSZ","PROCESSING","COMPLETED","QUEUED","HOLD","WAITING" )
and ( unix_timestamp( now() ) - unix_timestamp( q.queue_time ) ) <= 86400
group by pkgid
order by pkgid desc,queue_time desc';
$q = mysql_query( $q );

print "<div id='downloadSummary'>\n";
print "<center>\n";
print "<table id='downloadTable'>\n";
print "<tr><th></th><th>pkg-id</th><th>queue time</th><th>tag</th><th>status</th><th>size(kB)</th><th>objects</th><th>obsids</th><th>file-types</th></tr>\n";
$count = 0;
while ( $row = mysql_fetch_assoc( $q ) ){

  //
  // record the location of the package file
  //
  $pkgfile = "tmp/$row[temp_dir]/tgcat.tar.gz";

  //
  // check for existance before printing info
  //
  if ( $row['status'] == "COMPLETED" && ! file_exists( $pkgfile ) ){ continue; }
  
  //
  // if this pkgid is in our cookies columns then indicate to user
  // that is the case
  //
  if ( in_array( $row['pkgid'], $mydownloads ) ){
    print "<tr class='mydownload'>
<td style='text-align:center;'>
<a href='#' title='package $row[pkgid] was queued by you'>
$rowicon src='image/user.png'>
</a>
</td>\n";
  }
  else {
    print "<tr><td></td>\n";
  }  

  //
  // the pkgid column will have icon indicators telling
  // of the status and providing links for downloading
  //
  print "<td>";
  $displayF = $row['status'];
  if ( $displayF == "COMPLETED" ){
    $a = "<a style='color:" . $STATUS_COLORS[$row['status']] . ";' title='click to download package $row[pkgid]' href='$pkgfile'>";
    print "$a$rowicon src='image/download.png'></a>";
    $displayF = "$a<b> $displayF </b></a>";
  }
  else if ( $displayF == "PROCESSING" ){
    print "$rowicon src='image/package_processing.gif'>";
  }
  else if ( $displayF == "QUEUED" || $displayF == "WAITING" ){
    print "$rowicon src='image/time.png'>";
  }
  else if ( $displayF == "ERROR" ){
    print "$rowicon src='image/action_delete.png'>";
  }
  //
  // pkgid and other columns ( coloring the status )
  //
  print "$row[pkgid] </td>
<td> $row[queue_time] </td>
<td> $row[tag] </td>
";  
  print "<td style='color:" . $STATUS_COLORS[$row['status']] . ";'>$displayF</td>\n";

  //
  // format the number
  //
  print "<td>";
  if ( ! $row['size'] ) { print "-"; }
  else { print number_format($row['size']); }
  print "</td>\n";

  //
  // variable length columns should be truncated
  // to make the page more visible
  // 
  $displayF = $row['objects'];
  if ( strlen( $displayF ) > 15 ){ 
    $displayF = "<a href='#' title='$displayF'>" . substr( $displayF, 0, 12 ) . "...(" . count( split(",",$displayF) ) . ") </a>"; 
  }
  print "<td> $displayF </td>\n";

  $displayF = $row['obsids'];
  if ( strlen( $displayF ) > 10 ){ 
    $displayF = "<a href='#' title='$displayF'>" . substr( $displayF, 0, 7 ) . "...(" . count( split(",",$displayF) ) . ")</a>"; 
  }
  print "<td> $displayF </td>\n";
  
  $displayF = $row['types'];
  if ( strlen( $displayF ) > 10 ){ 
    $displayF = "<a href='#' title='$displayF'>" . substr( $displayF, 0, 7 ) . "...(" . count( split(",",$displayF) ) . ")</a>"; 
  }
  print "<td> $displayF </td>\n";

  //
  // done with this row
  //
  print "</tr>";
  
  $count ++;

 }  
print "</table>";
//print "<br><i style='font-size:12px'>--<br>for completed packages click status column for download</i>";
print "</center>";
print "</div>";

print "</div>\n";

initMainMenu();
generateFileMenu();
generateHelpMenu();
generateQuickSearchBar();
generateHelpTopicsMenu();
finalizeMainMenu();

$s = "";
if ( $count > 1 || $count == 0 ){ $s = "s"; }
statusMessage( "$count package$s currently staged; note that packages stage only ~1 day; this page auto-refreshes every 30s (last: " . date("H:i:s") . ")" );

?>

</body>
</html>
