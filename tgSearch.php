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
