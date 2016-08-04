<?php

####################################################
####################################################
##
##  FILENAME: tgRequest.php
##
##  DESCRIPTION: 
##    Form for Serendipitous Source Extractions.
##
##  AUTHOR: Emma Reishus
##
####################################################
####################################################

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
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html>

<head>
<title> TGCat - Extraction Request</title> 
<?php include "styleInc.php"; ?>
</head>

<body>

<div id='page'>
<form name='request' action='tgRequestSubmit.php' method='post'> 
<h1> Serendipitous Source Extraction </h1>

<div class='search' style='test-align:left'>
<p> 
If you find an observation with a source which has a visible
grating spectrum which has not been extracted, you may give us
the information in the following form and the extraction will
typically appear within one or two days. <i>Please</i> do a 
<a href="tgSearch.php?t=C">Cone Search</a> first to verify that the
source is not in the current catalog.
<br><br>
Enter Chandra ObsID.
</p>
Obsid: <INPUT TYPE='text' NAME='obsid' MAXLENGTH='20' SIZE=20 VALUE=''>

<br><br>

<p>

For coordinates, enter sky pixels (X and Y) or celestial coordinates (RA and Dec). 
RA and Dec may be given in decimal degrees (dd.dddd, [+/-]dd.dddd) or in sexagesimal notation, 
(hh:mm:ss.sss, [+/-]dd:mm:ss.sss).

							    
</p>

<SELECT NAME='coordinates'>
<OPTION value="sp">Sky pixels
<OPTION value="cc">Celestial Coordinates
</SELECT>
X or RA: <INPUT TYPE='text' NAME='coor1' MAXLENGTH='20' SIZE=20 VALUE=''>
Y or DEC: <INPUT TYPE='text' NAME='coor2' MAXLENGTH='20' SIZE=20 VALUE=''>
<br><br>
Exact? <input type='checkbox' name='exact' checked value='Y'>
      If unchecked, we will run a source-detection algorithm to determine
  the source centroid. <br>Otherwise, we will extract on the given
  coordinates.<br><br>

<p>
Enter object name. Please use the common name or primary SIMBAD name (if available). If
there is no identification, use "unkown" and we will follow the
IAU-approved CXOU convention. Identifier name is case insensitive. 
</p>
Target: <INPUT TYPE='text' NAME='objName' MAXLENGTH='30' SIZE='20' value=''>
<br><br>

<p> Optional: recieve a notification when your request has been completed. </p>
Email: <input type='text' name='email' maxlength='40' size='30' value =''>

<br><br>

<p> Is there anything non-standard we should consider when extracting the spectrum? </p>

Additional comments:
<br>
<textarea name='comments' cols=45 rows=3></textarea>
<br>

</div>

<div class='searchSubmit'>
<input  id='bs' type='submit'>
<input  id='bc' type='reset' >
</div>

<br>

</form>
</div>

<?php
initMainMenu();
generateFileMenu();
generateHelpMenu();
generateQuickSearchBar();
finalizeMainMenu();

statusMessage( "submit a source extraction request" );
?>

</body>
</html>