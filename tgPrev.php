<?php
ob_start( "ob_gzhandler" );
?>
<?php
##############################################################################
##
## TGCAT WEBPAGE DOCUMENT
##
## TITLE: tgcat_imgView.php
##
## DESCRIPTION:
##
##      this is the premade image viewing GUI for the tgcat website
##      will allow viewing of one tgcat id at a time
##
## REVISION:
##
##     -v 1.0 Arik Mitschang (Author)
##            Emma Reishus
##
##############################################################################
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?php

  //error_reporting( E_ALL );
  //ini_set( "display_errors",1);

require "tgMainLib.php";
require "tgDatabaseConnect.php";
require "styleInc.php";

//
// LOCAL DATA -----------------
//

//
// parse http query
//

$id = $_GET['i'];
$img_id = $_GET['m'];

$d = $_REQUEST['d'];
$r = $_REQUEST['r'];
$fr = $_GET['f'];
$froot = "multiple$d$r/$fr";

//grab the usesaved param so plotting params will get passed on

//
// define directory where to look for images
//
$pngdir= "png";
$imdir = "png";
//
// define a colormap to use for multiple 
// extraction previews
//
$color_map = Array( 0=>"#666666",
		    1=>"#005588",
		    2=>"#886600",
		    3=>"#446600",
		    );

?>

<style type="text/css">
body { 
  background:none;
}
</style>
<script type='text/javascript'>
  var bg = parent.document.getElementById( 'dbg' );
  var fg = parent.document.getElementById( 'imagePreviewFancy' );
  var img = parent.document.getElementById( 'imagePreviewImage' );
  var txt = parent.document.getElementById( 'imagePreviewText' );

  function previewImage( image, text ){
  bg.style.visibility="visible";
  fg.style.visibility="visible";  
  txt.innerHTML=unescape( text );
  img.src=image;
  }
</script>

</head>
<body>

<div id='prevWin'>
<?php
  //
  // Preview Images
  //
if ( $img_id == "P" ) {

  $color_index = 0;
  foreach ( split_multi_id( $id ) as $myid ){

    $impre = "tgcid_${myid}_summary";
    $im_root = "$imdir/$impre";
    
    //
    // fetch some info necessary for image table querying
    //
    $q = "SELECT obsid,object,instrument,grating FROM obsview WHERE id=$myid";
    $q = mysql_query( $q )  or die( mysql_error() );
    $info = mysql_fetch_assoc( $q );
    $ins = $info['instrument'];
    $gra = $info['grating'];
    $obsid = $info['obsid'];
    $obj = $info['object'];

    if ( is_multi_id( $id ) ){
      print "<br><i>summary products for obsid $obsid</i><br>";      
    }
    
    //
    // query the image description table to get appropriate images
    // and meta data for this id
    //
    $q = "select img_id,title,description,file_sfx 
          from image where ( detector='$ins' or detector='ALL' ) 
          and ( grating='$gra' or grating='ALL' )
          order by priority desc
          ";
    $info = mysql_query( $q ) or die( mysql_error() );

    //
    // create the thumbnail image entry, they all will float left
    // so page can resize appropriately. For multi previews their
    // borders will be color coded to separate them
    //
    while ( $row = mysql_fetch_array( $info ) ){    
      $tipTitleDescription = "<b>" . rawurlencode($row['title']) . "</b><br>" . rawurlencode( $row['description'] );
      print "
<a href='tgPrev.php?i=$myid&amp;m=$row[img_id]' 
onClick='previewImage( \"${im_root}$row[file_sfx].png\",\"" . $tipTitleDescription . "\" ); return false;'>
<img class='thmb' ";
      if ( is_multi_id( $id ) ){
	print "style='float:none;border-color:$color_map[$color_index];' ";
      }
      print " src='${im_root}$row[file_sfx]_thumb.png' 
     title='view $row[title] for $obj, obsid $obsid'
     alt='$row[title]_${obj}_obsid_$obsid'></a>";
    }
    //
    // advance the color index
    //
    $color_index++;
    if ( $color_index >= count( $color_map ) ){ $color_index=0; }
  }
}
//
// File Table View
//
else if ( $img_id == "F" ) {
  
  //
  // query the file table
  //
  $q = "select o.obsid,o.object,f.* from 
        files as f left join obsview as o using(id) 
        where id in ( $id ) 
        order by obsid asc,id asc,type asc,name desc,value asc,size desc";  
  $info = mysql_query($q);
  
  print "<table id='dTable' class='dTable' style='font-size:12px;'>\n";

  //
  // store header column
  //
  $ftHeader = "<tr id='obsidHeader'><th> Fetch </th>";
  if ( is_multi_id( $id ) ){
    $ftHeader .= "<th>obsid</th><th>object</th><th>id</th>";
  }
  $ftHeader .= "<th> Type </th><th> Cat </th><th> File Name/Num </th><th> Size/Total(kB)</th><th> Checksum </th></tr>\n";

  //
  // print rows of table creating a link for anything thats
  // a file
  //
  $counter = 0;
  $lastRowId = -1;
  while ( $row = mysql_fetch_assoc( $info ) ){
    //
    // sprinkle header rows
    //
    if ( $row['id'] != $lastRowId ){ print $ftHeader; $lastRowId = $row['id']; }
    if ( $row['name'] == "total" ) { $tc = "br"; $link = ""; }
    else { 
      $tc = "tr"; 
      //
      // create the link to file for file type rows
      //
      $link = "<a href='tgGet.php?id=$row[id]&amp;file=$row[value]&amp;src=html-preview'
title='click to download file $row[value] directly'><img style='position:relative;border:0px;top:1px;' src='image/download.png'></a>"; }
    print "<tr class='$tc'><td> $link </td>";
    if ( is_multi_id( $id ) ){
      print "<td> $row[obsid] </td><td> $row[object] </td><td>$row[id]</td>";
    }
    print "<td> $row[type] </td><td> $row[name] </td><td> $row[value] </td>
<td> $row[size] </td><td> $row[checksum] </td></tr>\n";
    $counter ++;
  }
  
  print "</table>\n";

 }

else if ( $img_id == "S" ){
  $mysort = $_REQUEST["s"];
  if ( ! $mysort ){ $mysort = "wlo asc,wmid asc,whi asc"; }
  //
  // find the instrument
  //
  $det = "select distinct(instrument) as instrument from obsid where id=$id order by instrument desc";
  $detinfo = mysql_query( $det );
  $det = mysql_fetch_assoc( $detinfo );
  $det = $det['instrument'];
  //
  // if it is acis then we show fluxes
  //
  $selections = "label,wmid,wlo,whi,";
  if ( $det == "ACIS" ){
    if ( is_multi_id($id) ){
      $selections .= "avg(count_rate) as avg_count_rate,avg(photon_flux) as avg_photon_flux,avg(energy_flux) as avg_energy_flux";
    }
    else{
      $selections .= "count_rate,err_count_rate,photon_flux,err_photon_flux,energy_flux,err_energy_flux";
    }
  }
  else {
    if ( is_multi_id($id) ){
      $selections .= "avg(count_rate) as avg_count_rate";
    }
    else {
      $selections .= "count_rate,err_count_rate";
    }
  }
  //
  // construct a table header from the fields
  //
  print "<table id='dTable' class='dTable' style='font-size:12px;'>\n";
  //
  // make a col group for the label mid/lo/hi limites
  //
  print "<colgroup span='4' class='tableLabel'>";

  $tableHeader = "<tr id='obsidHeader'>";
  $tableHeader .= "<th>" . preg_replace( '/,/',"</th><th>",$selections) . "</th>";
  $tableHeader .= "</tr>\n";

  $q = "select $selections from spec_prop where id in ($id) and flag!=1 group by label,wmid order by $mysort";
  $info = mysql_query( $q );

  $counter = 0;
  while ( $row = mysql_fetch_assoc( $info ) ){
    if ( ($counter %15) == 0 ){ print $tableHeader; }
    $rtype = ($counter % 2) ? "br" : "tr";
    print "<tr class='$rtype'>";
    foreach ( array_keys($row) as $field ){
      if ( is_numeric( $row[$field] ) and ! preg_match('/^w/',$field) ){ $row[$field] = sprintf("%.5e",$row[$field]); }
      print "<td> $row[$field] </td>";
    }
    print "</tr>\n";
    $counter++;
  }
  
 }
else if ( $img_id == "C" ){
  if ( is_multi_id( $id ) ){
    $qcheck = "SELECT count( distinct(instrument) ) as icnt,count( distinct( grating ) ) as gcnt FROM obsid WHERE id in ( $id )";
    $qcheck = mysql_query( $qcheck );
    $qcheck = mysql_fetch_assoc( $qcheck );
    if ( $qcheck['icnt'] > 1 || $qcheck['gcnt'] > 1 ){
      print "WARNING: Cannot combine differing INSTRUMENT/GRATINGS";
      exit;
    }
  }
  $myid = split_multi_id( $id );
  $myid = $myid[0];
  $q = "SELECT distinct(instrument),grating FROM obsview WHERE id in ( $myid )";
  $q = mysql_query( $q )  or die( mysql_error() );
  $info = mysql_fetch_assoc( $q );
  $DETECTOR=$info['instrument'];
  $GRATING=$info['grating'];
  $FILEROOT=$froot;

   print "<div id='imgTip' style='text-align:center;top:0px;'>
  <a style='width:100%;z-index:99' id='guiControlsAnchor'><span id='title'>PLOTTING CONTROLS</span></a>
  <div>
  ";
  $usesaved = $_GET['usesaved'];

  include "tgGUI.php";
  print "</div>
  </div>
  ";
   
  //$test = print_r($_REQUEST, true);
  //uncomment next line for debugging
  //print "<script type='text/javascript'>alert('$test');</script>";

  print "<h2>Loading, please wait...</h2>";

  if( $d || $r ) { $multiple = "d=$d&r=$r&"; }
  else { $multiple = ""; }

  if( $fr ) {
    if ( $usesaved ) {
      print "<iframe name='custom_plot' id='customPlot' src='tgRunPlot.php?id=$id&det=$info[instrument]&grat=$info[grating]&${multiple}fr=$fr&usesaved=$usesaved'></iframe>";
    }
    else {
      print "<iframe name='custom_plot' id='customPlot' src='tgRunPlot.php?id=$id&det=$info[instrument]&grat=$info[grating]&${multiple}fr=$fr'></iframe>";
    }
  }

}
else if( $img_id == 0 ) {
  $q = "select id,review,comment,obsid,object from obsview where id in ( $id )";
  
  $info = mysql_query($q);
  
  while ( $row = mysql_fetch_array( $info ) ){
  
    $revstat = $row['review'];
    $cmnt = implode( " ", split( "[\n\r\t ]+",$row['comment'] ) );
    $cmnt = preg_replace( '/\"|\'/',"",$cmnt );
    if ( ! $cmnt ) { $cmnt = "None"; }
    
    print "<h2>VV report for obsid $row[obsid] - $row[object]</h2>\n";        
    print "<p style='padding-left:10px;'>\n";
    print "<b> Review Status: </b>\n$REVIEW_CODES[$revstat] <br>\n";
    print "<b> Comments: </b>\n$cmnt <br></p>\n";
    $img = "${imdir}/tgcid_$row[id]_summary_flux_overview.png";
    print "<center><img src='$img' border=0px></center>\n";
  }
 }
else {

   $q = "select title,description,file_sfx from image where img_id=$img_id";
   
   $info = mysql_query( $q );
   
   $row = mysql_fetch_array( $info );
   
   print "<div id='imgTip'><a><center><span id='title'>info</span></center>";
   print "<div><h2>$row[0]</h2>\n";
   print "<p> $row[1]</p>\n";
   print "</div></a></div>";

   $impre = "tgcid_${id}_summary";
   $im_root = "$imdir/$impre";    

   $img = "$im_root$row[2].png";
   
   $ref = preg_replace( '/&/','&amp;',$_SERVER['HTTP_REFERER'] );
   print "<br><a href='$ref'><img src='$img' style='border:0px;'></a>\n";
   print "<br><i><a href='$im_root$row[2].png' target='_blank'>view image</a> -- <a href='tgPrev.php?i=$id&amp;m=P'> go back </a> ( or click image )</i>\n";
 }

?>

</div>

</body>
</html>
