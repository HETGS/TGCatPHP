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
