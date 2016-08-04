<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title> TGCat - Data </title> 
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<?php include "styleInc.php" ?>
<script type="text/javascript" src="highlight.js"></script>
<!-- <script type="text/javascript" src="datapage.js"></script> -->
</head>

<body style='background:url( "image/light_pattern_bg.png" );'> <!--  onload='load()'> -->
<!-- <script type="text/javascript" src="wz_tooltip.js"></script> -->

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

$qid = $_REQUEST['q'];
$type = $_REQUEST['t'];
$ids = $_REQUEST['i'];
$tc = $_REQUEST['c'];

require "tgMainLib.php";

if ( $type == "D" ){
  generateDownloadWindow( $_REQUEST['q'], $tc, "visible", $ids );
 }
else if ( $type == "C" ){
  if ( $tc == "o" ){
    generateColumnSelectorExtractions( $qid, $ids, "visible" );
  }
  elseif ( $tc == "s" ) {
    generateColumnSelectorSource( $qid, $ids, "visible" );
  }
}
 else if ( $type == "T" ){
   generateTagWindow( $qid, "", "visible" );
 }
 else if ( $type == "F" ){
   generateFilterWindow( $qid, $ids, "", "visible" );
 }
?>

</body>
</html>
