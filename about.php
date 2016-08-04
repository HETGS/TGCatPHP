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
<title> TGCat : Chandra Gratings Catalog and Archive</title> 
<meta 
  http-equiv="Content-Type" 
  content="text/html; charset=utf-8">
<meta 
  name="author" 
  content="Arik W. Mitschang ( SAO )" >
<meta 
  name="keywords" 
  content="Chandra,Chandra Xray Observatory,Gratings,Gratings Catalog,Gratings Archive,Archive,ACIS,HRC,HETG,LETG,Transmission Gratings,tgcat,TGCat">
<meta 
  name="description" 
  content="TGCat The Chandra Transmission Gratings Catalog and Archive. Search, Browse, Plot, and Download Calibrated and Reviewed Gratings Spectra from all Chandra ACIS/HRC Gratings Observations">
<?php include "styleInc.php"; ?>
</head>

<body>

<div id='page'>

<center>
<div id='about'>

<h3> About TGCat </h3>
  The Chandra Grating-Data Archive and Catalog (<i><b>TG</b>Cat</i>) makes
  grating spectra easily viewable and accessible.  The browse-able
  interface to analysis-quality spectral products (binned spectra and
  corresponding response files), with the addition of summary
  graphical products and model-independent flux properties tables make
  it easy to find observations of a particular object, type of object,
  or type of observation, to quickly assess the quality and potential
  usefulness of the spectra, and to download the data and responses as
  a package.

<br><br>

  In addition to the data, portable reprocessing scripts, using CXC
  and other publicly available 
  <a href="http://space.mit.edu/cxc/analysis/tgcat/index.html"> software </a>
  (which were used to create
  the archive) are also available, facilitating standard or
  customized reprocessing from Level 1 archive data to spectra and
  responses with minimal interaction.

<br><br>

  In addition to standard products for single point-sources, we will
  add custom extractions of more complex sources, such as close or
  extended sources, and we will provide some aggregate products from
  multiple observations of the same object.

</p>
<hr width="30%">
<h3> Credits </h3>
<p style="width:70%">

  <i><b>TG</b>Cat</i> development was directed by David Huenemoerder, who is also
  responsible for reprocessing scripts.  Arik Mitschang is responsible
  for design and implementation of the database and web interface.
  For ongoing support, review, and testing we thank John Davis, Dan
  Dewey, John Houck, Herman Marshall, Doug Morgan, Joy Nichols, Mike
  Noble, Mike Nowak, and Norbert Schulz.

<br><br>

  If you use <i><b>TG</b>Cat</i> in your research, please cite  

  <a href="http://adsabs.harvard.edu/abs/2011AJ....141..129H"> Huenemoerder et al 2011. </a>

</p>
</div>
</center>
</div>

<?php
initMainMenu();
generateFileMenu();
generateHelpMenu();
generateQuickSearchBar();
finalizeMainMenu();

statusMessage( "last updated MM/DD/YYYY" );
?>

</body>
</html>
