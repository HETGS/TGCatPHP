<?php
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
<title> TGCat : Announcements</title> 
<?php include "styleInc.php"; ?>
</head>

<body>

<div id='page' style='min-width:600px;'>

<h1> All Announcements </h1>

<div id='tgcat_news'>
<?php
$q = mysql_query( 'select * from news order by post_date desc' );
while ( $row = mysql_fetch_assoc( $q ) ){
  print "<i>$row[post_date]</i> - posted by $row[poster]\n";
  print "<p>$row[content]</p>";
}
?>
</div>

</center>
</div>

<?php
initMainMenu();
generateFileMenu();
generateHelpMenu();
generateQuickSearchBar();
finalizeMainMenu();

statusMessage( "TGCat news in order of post date" );
?>

</body>
</html>
