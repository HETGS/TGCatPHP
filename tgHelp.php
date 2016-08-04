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


ob_start( "ob_gzhandler" );
require "tgMenu.php";
require "tgNotifications.php";
$guide = $_REQUEST['guide'];
if ( ! $guide ){ $guide = "help/tgcat_help_intro.html"; }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title> TGCat - Help </title> 
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<?php include "styleInc.php" ?>
</head>

<body>

<div id='page'>  
    <?php
if ( file_exists( $guide ) && ! preg_match( "/\.\./", $guide ) && preg_match( "/^help/", $guide ) ){
  include "$guide"; 
}
else {
  $guide = "Not Found";
  print "<center><div id='about'><h3>guide not found</h3></div></center>";
}
?>
</div>


<?php
initMainMenu();
generateFileMenu();
generateHelpTopicsMenu();
generateHelpMenu();
finalizeMainMenu();
statusMessage( "You are currently browsing guide: $guide" ); 
?>

</body>
</html>
