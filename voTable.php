<?php

$FIELDS = Array(
		"id"             => '<FIELD name="id" datatype="char" arraysize="*" ucd="ID_MAIN"><DESCRIPTION> Unique Integer Key </DESCRIPTION></FIELD>',
		"obsid"          => '<FIELD name="obsid" datatype="char" arraysize="*" ucd="meta.id"><DESCRIPTION> Chandra Observation ID ( see cxc.harvard.edu ) </DESCRIPTION></FIELD>',
		"obi"            => '<FIELD name="obi" datatype="char" arraysize="*" ucd="meta.id"><DESCRIPTION> Onboard Interval ID</DESCRIPTION></FIELD>',
		"ra"             => '<FIELD name="ra" datatype="double" unit="degree" ucd="POS_EQ_RA_MAIN"><DESCRIPTION> Right Ascension </DESCRIPTION></FIELD>',
		"decl"           => '<FIELD name="dec" datatype="double" unit="degree" ucd="POS_EQ_DEC_MAIN"><DESCRIPTION> Declination </DESCRIPTION></FIELD>',
		"object"         => '<FIELD name="name" datatype="char" arraysize="*" ucd="meta.id"><DESCRIPTION> TGCat Preferred Object Designation </DESCRIPTION></FIELD>',
		"simbad_ID"      => '<FIELD name="simbad_primary_identifier" datatype="char" arraysize="*" ucd="meta.id"><DESCRIPTION> SIMBAD primary Identifier ( see http://simbad.u-strasbg.fr/simbad/ ) </DESCRIPTION></FIELD>',
		"instrument"     => '<FIELD name="instrument" datatype="char" arraysize="*" ucd="INST_ID"><DESCRIPTION> Chandra Instrument ( ACIS/HRC )  </DESCRIPTION></FIELD>',
		"grating"        => '<FIELD name="grating" datatype="char" arraysize="*" ucd="instr.detector"><DESCRIPTION> Chandra Grating ( HETG/LETG ) </DESCRIPTION></FIELD>',
		"readmode"       => '<FIELD name="readmode" datatype="char" arraysize="*" ucd="instr.detector"><DESCRIPTION> Readmode e.g. Timed/Continuous Clocking </DESCRIPTION></FIELD>',
		"datamode"       => '<FIELD name="datamode" datatype="char" arraysize="*" ucd="instr.detector"><DESCRIPTION> Datadmode e.g. Faint/Very Faint </DESCRIPTION></FIELD>',
		"exposure"       => '<FIELD name="exposure" datatype="double" unit="s" ucd="obs.exposure"><DESCRIPTION> Exposure Time </DESCRIPTION></FIELD>',
		"date_obs"       => '<FIELD name="observation_date" datatype="char" arraysize="*" ucd="time.creation"><DESCRIPTION> Observation Date YYYY-MM-DD HH:MM:SS </DESCRIPTION></FIELD>',
		"meg_band"       => '<FIELD name="meg_band_rate" datatype="double" unit="counts/s" ucd="phot.count"><DESCRIPTION> MEG Band Count Rate ( 1.7 - 25 Angstoms ) </DESCRIPTION></FIELD>',
		"heg_band"       => '<FIELD name="heg_band_rate" datatype="double" unit="counts/s" ucd="phot.count"><DESCRIPTION> HEG Band Count Rate ( 1.7 - 15 Angstoms ) </DESCRIPTION></FIELD>',
		"leg_band"       => '<FIELD name="leg_band_rate" datatype="double" unit="counts/s" ucd="phot.count"><DESCRIPTION> LEG Band Count Rate ( 2 - 160 Angstoms ) </DESCRIPTION></FIELD>',
		"letg_acis_band" => '<FIELD name="letg_acis_band_rate" datatype="double" unit="counts/s" ucd="phot.count"><DESCRIPTION> LEG Band Count Rate Specific to ACIS detector ( 2 - 50 Angstoms ) </DESCRIPTION></FIELD>',
		"zeroth_order"   => '<FIELD name="zeroth_order_rate" datatype="double" unit="counts/s" ucd="phot.count"><DESCRIPTION> Zeroth Order Count Rate </DESCRIPTION></FIELD>',
		"image_title"    => '<FIELD name="title" datatype="char" arraysize="*" ucd="VOX:Image_Title"><DESCRIPTION> Description of image </DESCRIPTION></FIELD>',
		"image_naxes"    => '<FIELD name="num_axes" datatype="int" ucd="VOX:Image_Naxes"><DESCRIPTION> Number of axes in image </DESCRIPTION></FIELD>',
		"image_naxis"    => '<FIELD name="num_axis" datatype="int" arraysize="*" unit="pixel" ucd="VOX:Image_Naxis"><DESCRIPTION> size of each axis in image ( x,y,z ) </DESCRIPTION></FIELD>',
		"image_scale"    => '<FIELD name="scale" datatype="double" arraysize="*" unit="degree/pixel" ucd="VOX:Image_Scale"><DESCRIPTION> Scale of each axis in Deg/Pixel </DESCRIPTION></FIELD>',
		"image_acref"    => '<FIELD name="link" datatype="char" arraysize="*" ucd="VOX:Image_AccessReference"><DESCRIPTION> Provides link to retrieve image </DESCRIPTION></FIELD>',
		"image_format"   => '<FIELD name="format" datatype="char" arraysize="*" ucd="VOX:Image_Format"><DESCRIPTION> image format </DESCRIPTION></FIELD>',
		"mjdateobs"      => '<FIELD name="MJDateObs" datatype="char" arraysize="*" ucd="VOX:Image_MJDateObs"><DESCRIPTION> Median Julian Observation Date YYYY-DDD HH:MM:SS ( Obs start + 0.5*(SUM(GTI))) </DESCRIPTION></FIELD>',
		"image_filesize" => '<FIELD name="filesize" datatype="int" unit="kB" ucd="VOX:Image_FileSize"><DESCRIPTION> file size in kilobytes </DESCRIPTION></FIELD>',
		"pType"          => '<FIELD name="primary_type" datatype="char" arraysize="*"><DESCRIPTION> Primary object type used in SIMBAD catalog </DESCRIPTION></FIELD>',
		"other_types"     => '<FIELD name="all_types" datatype="char" arraysize="*"><DESCRIPTION> All object types specified in SIMBAD catalog </DESCRIPTION></FIELD>',
		"num_extractions"=> '<FIELD name="num_extractions" datatype="int" arraysize="*"><DESCRIPTION> Number of extractions TGCat has in Archive for this Object </DESCRIPTION></FIELD>',
		"prev_link"   => '<FIELD name="link" datatype="char" arraysize="*"><DESCRIPTION> Link to TGCat preview page for this extraction </DESCRIPTION></FIELD>',
		);


function voHeader(){

print '<?xml version="1.0"?>
<VOTABLE version="1.1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xsi:noNamespaceSchemaLocation="http://www.ivoa.net/xml/VOTable/VOTable/v1.1">
<DESCRIPTION>
TGCat VOTable service. For more information visit TGCat at
   http://tgcat.mit.edu or
send inquiry to tgcat@space.mit.edu
</DESCRIPTION>
';
}

function voFooter(){
  print "</VOTABLE>\n";
}

function voTableHeader( $qid, $cols, $votype ){
  global $FIELDS;
  voHeader();
?>
<COOSYS ID="J2000" system="ICRS" epoch="J2000" equinox="J2000" />
<RESOURCE type="results" name="<?php print $qid; ?>">
<INFO name="QUERY_STATUS" value="OK"/>
<TABLE>
<DESCRIPTION><?php 
   if ( $votype == "cone" ){ ?>
Table generated from tgcat query ID <?php print "$qid\n"; ?>.
To view the HTML version of your query please see:
    http://tgcat.mit.edu/tgData.php?q=<?php print $qid; ?> 
For questions/comments please see http://tgcat.mit.edu or write
to tgcat@space.mit.edu <?php 
}
else if ( $votype == "image" ){ ?>
Table describing images for the query string <?php print "$qid\n"; ?>
For questions/comments please see http://tgcat.mit.edu or write
to tgcat@space.mit.edu <?php
} 
?> 
</DESCRIPTION>
<?php
   foreach ( $cols as $col ){
    print $FIELDS[ $col ] . "\n";
  }
?>
<DATA>
<TABLEDATA>
<?php
}

function voImageTableRow( $fmt, $info, $cols ){
  
  $info['image_format'] = $fmt;

  switch ( $fmt ){
    
  case "image/fits":    
    $info['image_title'] .= " evt2 file";
    if ( $info['instrument'] == "ACIS" ){
      $info['image_naxis'] = "8192 8192";
      $info['image_scale'] = ".000136666667";
    }
    else {
      $info['image_naxis'] = "65536 65536";
      $info['image_scale'] = ".000036597223";
    }      
    $info['image_acref'] = "http://tgcat.mit.edu/tgGet.php?id=$info[id]&file=evt2.gz&src=vo-sia";
    break;
    
  case "image/png":
    if ( $info['instrument'] == "ACIS" ){
      if ( $info['grating'] == "LETG" ){
	$info['image_naxis'] = "906 308";
	$info['image_scale'] = "0.0";
      }
      else {
	$info['image_naxis'] = "931 634";
	$info['image_scale'] = "0.0";
      }	
    }
    else {
      $info['image_naxis'] = "926 346";
      $info['image_scale'] = "0.0";
    }          
    $info['image_filesize'] = "0.0";
    $info['image_title'] .= " full field summary";
    #
    # change to site later
    #
    $info['image_acref'] = "http://tgcat.mit.edu/png/tgcid_$info[id]_summary_im-b.png";
    break;
    
  case "text/html":
    $info['image_filesize'] = "0.0";   
    $info['image_title'] .= " summary webpage";
    #
    # change to site later
    #
    $info['image_acref'] = "http://tgcat.mit.edu/tgPlot.php?t=P&i=$info[id]";
    break;

  default:
    return;
    break;  
  }
  
  $info['image_acref'] = "<![CDATA[$info[image_acref]]]>";

  print "<TR>";
  foreach ( $cols as $col ){
    print "<TD> $info[$col] </TD>";
  }
  print "</TR>\n";
  
}
   

function voTableFooter(){
?>
</TABLEDATA>
</DATA>
</TABLE>
</RESOURCE>
<?php
    voFooter();
}

function voError( $error ){
  voHeader();  
  print '<RESOURCE type="results">
<INFO ID="Error" name="Error" value="'.$error.'"/>
<INFO name="QUERY_STATUS" value="ERROR">'.$error.'</INFO>
</RESOURCE>
';
  voFooter();
}

function voMetaData( $cols, $type ){
  voTableHeader( 'METADATA', $cols, $type );
  voTableFooter();
}

?>
