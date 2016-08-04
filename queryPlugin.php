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


$UNITS = Array( "ra"=>"(h:m:s)",
		"decl"=>"(d:m:s)",
		"exposure"=>"(s)",
		"date_obs"=>"(y-m-d t)",
		"proc_date"=>"(y-m-d t)",
		"meg_rate"=>"(cnts/s)",
		"heg_rate"=>"(cnts/s)",
		"leg_rate"=>"(cnts/s)",
		"letg_acis_rate"=>"(cnts/s)",
		"zeroth_order"=>"(cnts/s)",
		"meg_P_flux"=>"(phot/s/cm<sup>2</sup>)",
		"heg_P_flux"=>"(phot/s/cm<sup>2</sup>)",
		"leg_P_flux"=>"(phot/s/cm<sup>2</sup>)",
		"letg_acis_P_flux"=>"(phot/s/cm<sup>2</sup>)",
		"meg_E_flux"=>"(ergs/s/cm<sup>2</sup>)",
		"heg_E_flux"=>"(ergs/s/cm<sup>2</sup>)",
		"leg_E_flux"=>"(ergs/s/cm<sup>2</sup>)",
		"letg_acis_E_flux"=>"(ergs/s/cm<sup>2</sup>)",
		"object" => "",
		"obsid" => "",
		"other_types"=>"",
		"simbad_ID"=>"",
		"primary_type"=>"",
		"grating"=>"",
		"instrument"=>"",
		);

function getDefaultSortByTable( $tableCode )
{
  if ( $tableCode == "s" ){
    return "object asc";
  }
  else{
    return "object asc";
  }
}

function getDefaultColsByTable( $tableCode )
{
  if ( $tableCode == "s" ){
    return "object,simbad_ID,ra,decl,primary_type,other_types";
  }
  else{
    return "obsid,object,instrument,grating,ra,decl,date_obs,exposure";
  }
}

function getOtherQueryIdsInsert( $qid, $tableCode, $ids )
{
  $idsString = join( ",", $ids );
  if ( $tableCode == "s" ){ 
    $uid = "srcid"; 
    $oCode = "o";
    $oid = "id";
    $s = "$uid in ( $idsString )";
  }
  else { 
    $uid = "id"; 
    $oCode = "s";
    $oid = "srcid";
    $s = "$oid in ( SELECT DISTINCT( $oid ) FROM obsview WHERE $uid in ($idsString) )";
  }
  $q = "UPDATE query
        SET ${oCode}ids='$s'
        WHERE qid='$qid'
       ";
  return $q;
}

function getTableNameFromCode( $tableCode )
{
  if ( $tableCode == "s" ){ return "sourceview"; }
  else { return "obsview"; }
}

function getNiceTableNameFromCode( $tableCode )
{
  if ( $tableCode == "s" ){ return "source"; }
  else { return "extractions"; }
}

function getTableUniqueIdName( $tableCode ){
  if ( $tableCode == "o" ){
    return "id";
  }
  else {
    return "srcid";
  }
}

function getPrivateDataDefs( $tableCode ){
  if ( $tableCode == "o" ){
    return Array("obsid","simbad_ID","srcid","review","if(comment is null,'None',comment)");
  }
  else {
    return Array("srcid","simbad_ID");
  }
}

function processTableRow( $outputType, $tableCode, $counter, $uniqueId, $privateData, $row ){
  if ( $outputType == "VOTABLE" ){    
    processTableRowVOTABLE( $uniqueId, $row );
    return;
  }
  if ( $tableCode == "o" ){
    processObsidTableRow( $outputType, $uniqueId, $counter, $privateData[0],$privateData[1],$privateData[2],$privateData[3],$privateData[4], $row );
  }
  else {
    processSourceTableRow( $outputType, $uniqueId, $counter, $privateData, $row );
  }
}
function processObsidTableRow( $outputType, $id, $counter, $obsid, $simbad, $srcid, $review, $comment, $rowData )
{
  if ( $outputType == "HTML" ){
    if ( ( $counter % 15 ) == 0 ){
      processObsidTableHeaderHTML( 
				  $rowData
				  );
    }
    $rtype = ($counter % 2) ? "br" : "tr";
    processObsidTableRowHTML(
			     $rtype,
			     $id, 
			     $obsid, 
			     $simbad, 
			     $srcid, 
			     $review, 
			     $comment, 
			     $rowData 
			     );
  }
  else {
    $sep = "|";
    if ( $outputType == "csv" ){ $sep = ","; }
    if ( $outputType == "tab" ){ $sep = "\t"; }
    if ( $counter == 0 ){
      processObsidTableHeaderTEXT(
				  $sep,
				  $rowData
				  );
    }
    processObsidTableRowTEXT(
			     $sep,
			     $id, 
			     $obsid, 
			     $simbad, 
			     $srcid, 
			     $review, 
			     $comment, 
			     $rowData 
			     );
  }
}     

function processSourceTableRow( $outputType, $srcid, $counter, $privateData, $rowData )
{
  if ( $outputType == "HTML" ){
    if ( ( $counter % 15 ) == 0 ){
      processSourceTableHeaderHTML( 
				  $rowData
				  );
    }
    $rtype = ($counter % 2) ? "br" : "tr";
    processSourceTableRowHTML(
			      $rtype,
			      $srcid,
			      $privateData,
			      $rowData 
			      );
  }
  else {
    $sep = "|";
    if ( $outputType == "csv" ){ $sep = ","; }
    if ( $outputType == "tab" ){ $sep = "\t"; }
    if ( $counter == 0 ){
      processSourceTableHeaderTEXT( 
				   $sep,
				   $rowData
				   );
    }
    processSourceTableRowTEXT(
			      $sep,
			      $srcid, 
			      $rowData
			      );
  }
}     

function processObsidTableHeaderHTML( $data )
{
  global $UNITS;
  //
  // get the query id since it needs to be
  // present in the resorting links
  //
  $idata = split( '[|]', $data['_data'] );
  $qid = $idata[0]; 
  print "
<tr id='obsidHeader'>
<th><a name='togS' href='#'><font color='E1D7B0'>+/-</font></a></th>
<th>Links</th>";
  //
  // loop through the available keys
  // skipping if it is internal data
  //
  foreach ( array_keys( $data ) as $header ){
    if ( $header == "_data" ){ continue; }
    print "<th>$header $UNITS[$header]</th>";
  }
  print "
</tr></a>
";
}

function processObsidTableRowHTML( $rtype, $id, $obsid, $simbad, $srcid, $review, $comment, $data )
{
  global $REVIEW_CODES;
  
  $simbadurl  = urlencode( $simbad );
  $comment = implode( " ", split( "[\n\r\t ]+",$comment ) );
  $comment = preg_replace( '/\"|\'/',"",$comment );
  if ( ! $comment ) { $comment = "None"; }
  
  //
  // define the tip HTML for V&V and 
  // for The flux spectrum preview
  //
  $VVtip ="'vvTip( \"$REVIEW_CODES[$review]\",\"$comment\" );'";
  $FluxTip = "'fluxTip( $id )';";
  print "
<tr class='$rtype' id='tr_$id'>
<td>
<input type='checkbox' id='ds_$id' name='dsc[]' value='$id' onclick='check_toggle( \"$id\" );'>
</td>
<td>
  <!-- <button> <a href='http://asc.harvard.edu/cgi-gen/target_param.cgi?$obsid' target='_blank' title='obscat page'> o </a> </button> -->
  <button> <a href='http://cda.harvard.edu/chaser/startViewer.do?menuItem=details&obsid=$obsid' target='_blank' title='obscat page'> o </a> </button>
  <button> <a href='http://cxc.harvard.edu/cgi-gen/cda/bib.pl?ADS=search&amp;obsid=$obsid' target='_blank' title='publications'> p </a> </button>
  <button> <a href='tgPlot.php?t=V&amp;i=$id&amp;q=$_REQUEST[q]' onmouseover=$VVtip title='VV report' target='_blank'> v </a> </button> 
  <button> <a href='http://simbad.harvard.edu/simbad/sim-id?Ident=$simbadurl' title='simbad identifier search on $simbad' target='_blank'> s </a> </button> 
</td>
";
      foreach ( array_keys( $data ) as $column ){
	if ( $column == "_data" ){ continue; }
	$columnHTML = "$data[$column]";
	if ( $column == "ra" )  {  $columnHTML = sexagesimal($data[$column],True); }
	if ( $column == "decl" ){ $columnHTML = sexagesimal($data[$column],False); }
	if ((preg_match("/_flux/",$column) || preg_match("/_rate/",$column)) && is_numeric($data[$column])){
	  $columnHTML = sprintf("%-0.3e",$data[$column]); }
	if ( $column == "object" ){ 
	  $columnHTML = "
  <a href='tgPlot.php?t=P&amp;i=$id'
     target='_blank' onmouseover=$FluxTip>
    $data[$column]
  </a>
"; 
	}
	print "<td>$columnHTML</td>";
      }
      print "</tr>
";
}

function processTableRowVOTABLE( $id, $data ){
  print "<TR>";
  foreach ( array_keys($data) as $key ){
    if ( $key == "_data" ){ continue; }
    print "<TD>$data[$key]</TD>";
  }
  print "</TR>\n";
}

function processSourceTableHeaderHTML( $data )
{
  global $UNITS;
  //
  // get the query id since it needs to be
  // present in the resorting links
  //
  $idata = split( '[|]', $data['_data'] );
  $qid = $idata[0];
  print "
<tr id='sourceHeader'>
<th name='togS'><a href='#'><font color='E1D7B0'>+/-</font></a></th>
<th>Links</th>
";
  //
  // loop through the available keys
  // skipping if it is internal data
  //
  foreach ( array_keys( $data ) as $header ){
    if ( $header == "_data" ){ continue; }
    print "
<th>
  $header $UNITS[$header]</th>";
  }
  print "
</tr>
";
}

function processSourceTableRowHTML( $rtype, $id, $privateData, $data )
{
  global $REVIEW_CODES;
  
  $simbad = $privateData[1];
  $simbadurl = urlencode( $simbad );

  print "
<tr class='$rtype' id='tr_$id'>
<td>
<input type='checkbox' id='ds_$id' name='dsc[]' value='$id' onclick='check_toggle( \"$id\" );'>
</td>
<td>
<button> <a href='http://simbad.harvard.edu/simbad/sim-id?Ident=$simbadurl' title='simbad identifier search on $simbad' target='_blank'> s </a> </button>
<button> <a href='http://heasarc.gsfc.nasa.gov/cgi-bin/vo/datascope/jds.pl?position=$simbadurl&amp;size=0.05&amp;errorcircle=-1.0' title='NVO Datascope query on $simbad ( using size=0.05 )' target='_blank'> d </a> </button>
<button> <a href='http://xmm.esac.esa.int/BiRD/cgi-bin/rgs_log.py?object=$simbadurl&amp;simbad_name=1' title='Search BiRD(XMM) for $simbad' target='_blank'> b </a> </button>
<button> <a onMouseOver='iTip( $id )' href='surveypng/tgSid_${id}_survey_panel.png' target='_blank'>i</a> </button>
</td>
";
      foreach ( array_keys( $data ) as $column ){
	$columnHTML = "$data[$column]";
	if ( $column == "ra" ){  $columnHTML = sexagesimal($data[$column],True); }
	if ( $column == "decl" ){ $columnHTML = sexagesimal($data[$column],False); }
	if ( $column == "object" || $column == "num_extractions" ){ 
	  $columnHTML = "<a href='tgCli.php?SRCID=$id&amp;OUTPUT=F' title='view all $data[$column] extractions ( new window )' target='_blank'> $data[$column] </a>
"; 
	}
	print "<td>$columnHTML</td>";
      }
      print "</tr>
";
}

function processObsidTableHeaderTEXT( $sep,$data ){
  processTableHeaderTEXT( $sep,$data );
}
function processSourceTableHeaderTEXT( $sep,$data ){
  processTableHeaderTEXT( $sep,$data );
}
function processObsidTableRowTEXT( $sep, $id, $obsid, $simbad, $srcid, $review, $comment, $data ){
  processTableRowTEXT( $sep, $id, $data );
}
function processSourceTableRowTEXT( $sep, $id, $data ){
  processTableRowTEXT( $sep, $id, $data );
}

function processTableHeaderTEXT( $sep,$data )
{
  $myRow = "id";
  foreach ( array_keys( $data ) as $column ){
    if ( $column == "_data" ){ continue; }
    $myRow .= "${sep}$column";
  }
  print "$myRow\n";
}  
function processTableRowTEXT( $sep, $id, $data )
{
  //
  // simply print out the table bar separated
  //
  $myRow = "$id";
  foreach ( array_keys( $data ) as $column ){
    if ( $column == "_data" ){ continue; }
    $myRow .= "${sep}$data[$column]";
  }
  print "$myRow\n";
}
?>
