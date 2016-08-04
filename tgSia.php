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

//
// send header indicating xml votable
//
header( "Content-Type: text/xml;content=x-votable" );

require "tgDatabaseConnect.php";
require "voTable.php";
// 
// implementation of the VO Simple Image Access protocol
// for tgcat
//
//
// DEFAULTS:
//
$colsdef = Array( 1 => "image_title,image_acref,obsid,ra,decl,image_format,image_naxes,image_naxis,image_scale",
		  2 => "image_title,image_acref,obsid,ra,decl,image_format,object,instrument,grating,image_naxes,image_naxis,image_scale,image_filesize",
		  3 => "image_title,image_acref,obsid,ra,decl,image_format,object,instrument,grating,exposure,mjdateobs,image_naxes,image_naxis,image_scale,image_filesize",
		  );
$fmtdef = "image/fits,image/png,image/jpg,image/gif,text/html";
$graphicdef = "image/png";
//
// get required params:
//
//
$pos  = $_REQUEST['POS'];
$size = $_REQUEST['SIZE'];
//
// optional parameters:
//
$verb = $_REQUEST['VERB'];
$fmt  = $_REQUEST['FORMAT'];
//
// metadata is a special case
//
if ( ! $verb ){ $verb = 1; }
$cols = split( ",", $colsdef[$verb] );
if ( $fmt == "METADATA" ){
  voMetaData( $cols, 'image' );
  exit;
}
//
// if not present we must return an error
//
if ( ! preg_match( '/\d\.*\d*,+|-*\d\.*\d*/',$pos ) ){
  voError('invalid input parameter for POS');
  exit;
}
if ( preg_match( '/\d\.*\d*,\d\.*\d*/', $size ) ){
  $sizes = split( ",", $size );
  $sizeRA = $sizes[0];
  $sizeDEC = $sizes[1];
}
else if ( is_numeric( $size ) ){
  $sizeRA = $size;
  $sizeDEC = $size;
}
else {
  voError('invalid input parameter for SIZE');
  exit;
}

if ( ! $fmt || $fmt == "ALL" ){ $fmt = $fmtdef; }
if ( $fmt == "GRAPHIC" ){ $fmt = $graphicdef; }

$fmt = split( ",", $fmt );
//
// handle the GRAPHIC spec
//
for ( $i=0; $i<count($fmt); $i++ ){
  if ( preg_match( '/^GRAPHIC/',$fmt[$i] ) ){
    $graphic = split( "-",$fmt[$i] );   
    $fmt[$i] = $graphic[1];
    for ( $j=$i; $j<count($fmt); $j++ ){
      $fmt[$j] = "image/" . $fmt[$j];
    }
  }
}

//
// separate RA and DEC
//
$radec = split( ",", $pos );
$ra  = $radec[0];
$dec = $radec[1];

//
// build our simple image location query
//
$q = "SELECT 
o.id,
o.obsid,
o.ra,
o.decl,
o.object,
o.instrument,
o.grating,
o.exposure,
concat(o.object,' [',o.instrument,'-',o.grating,']') as image_title,
'2' as image_naxes,
'0.0' as image_naxis,
'NA' as image_acref,
'0.0' as image_scale,
'0.0' as image_format,
date_format(from_unixtime( unix_timestamp( o.date_obs ) + 0.5*(o.exposure) ),'%Y-%j %H:%i:%s' ) as mjdateobs,
f.size as image_filesize,
f.arc,
f.checksum
FROM obsview AS o
LEFT JOIN files AS f
using ( id )
WHERE f.type='evt2' AND f.name='file' AND ( ra > ($ra - $sizeRA) ) AND ( ra < ($ra + $sizeRA) ) AND ( decl > ($dec - $sizeDEC) ) AND ( decl < ($dec + $sizeDEC) )";

//print "$q<br>";
$q = mysql_query( $q ) or die( voError('unkown database error') );

voTableHeader( preg_replace( '/=|&/',' ',$_SERVER['QUERY_STRING'] ), $cols, 'image' );

while ( $row = mysql_fetch_assoc( $q ) ){

  foreach ( $fmt as $fmtType ){

    voImageTableRow( $fmtType, $row, $cols );

  }
  
}

voTableFooter();
?>
