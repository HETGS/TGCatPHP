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

  //ob_start( "ob_gzhandler" );
  //error_reporting( E_ALL );
  //ini_set( "display_errors",1);

  //
  // Get referer for records
  //
if ( ! $_SERVER['HTTP_REFERER'] ){ $typeExt = "-CLI"; }
//
// DEFAULTS
//
$colsdef = Array( 1 => "id,obsid,object,ra,decl,prev_link",
		  2 => "id,obsid,object,ra,decl,instrument,grating,exposure",
		  3 => "id,obsid,object,ra,decl,instrument,grating,exposure,date_obs,meg_band,heg_band,leg_band,letg_acis_band,zeroth_order",
		  );

//
// get the qid requested for TEXT table printing
//
$qid = $_REQUEST['q'];
$out = $_REQUEST['OUTPUT'];
$tab = $_REQUEST['TABLE'];
$name= $_REQUEST['NAME'];
$cols= $_REQUEST['COLS'];
//
// REQUIRED for NVO compliance
//
$ra  = $_REQUEST['RA'];
$dec = $_REQUEST['DEC'];
$sr  = $_REQUEST['SR'];
//
// OPTIONAL but easy
//
$verb= $_REQUEST['VERB'];

if ( ! $verb ){ $verb = 1; }

require "tgDatabaseConnect.php";
require "tgQuery.php";
require "voTable.php";

$q = new Query;

if ( $qid ){
  $q->initQuery( $qid );
}
else if ( $name ){
  if ( ! $tab ){ $tab = 's'; }
  $q->initNewQuery( "NAME$typeExt", $tab, tgcatNameSearch( $name ), $cols );
}   
else if ( is_numeric($ra) && is_numeric($dec) && is_numeric($sr) ){
  if ( ! $srunit ){ $srunit = "degrees"; }
  $q->initNewQuery( "VOTABLE$typeExt", 'o', tgcatCoordinateSearch( $ra, $dec, $sr, $srunit ), $colsdef[ $verb ], null, null );
  if ( ! $out ){ $out = "V"; }
}
else {
  header( "Content-Type: text/xml;content=x-votable" );
  voError( "invalid arguments" );
  exit;
}

switch ( $out ){
 case "H":
   $q->setTableType( "HTML" );
   $q->runQuery();
   break;
 case  "V":
   header( "Content-Type: text/xml;content=x-votable" );
   $q->setTableType( "VOTABLE" );
   voTableHeader( $q->getQueryId(), $q->getColumns(), 'cone' );
   $q->runQuery();
   voTableFooter();
   break;
 default:
   header( "Content-Type: text/plain" );
   $q->setTableType( "TEXT" );
   $q->runQuery();
}

?>