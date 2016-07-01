<?php
##############################################################################
##
## TGCAT WEBPAGE DOCUMENT
##
## TITLE: do_plot.php
##
## DESCRIPTION:
##
##     this script takes plotting parameter arguments from
##     post data and interprets them into an isis script file
##     that it then executes in an isis-script shell. The output
##     is set and the html portion of this script displays
##     the image file from the tmp directory
##
## REVISION:
##     
##     -v 1.0 Arik Mitschang (Author)
##            Emma Reishus
## 
##############################################################################

  # if save checkbox checked in parameter gui need to save the plotting parameters to a cookie before the html tag  

  if ( $_POST['doplot'] && $_POST['save']){
  
    $params = "";
    $params .=  $_POST['plot'] . ","; 
    $params .=  $_POST['xunit'] . "," ;
    $params .=  $_POST['yunit'] . ",";
    $params .=  $_POST['xlog'] . ",";
    $params .=  $_POST['ylog'] . ",";
    $params .=  $_POST['errbar'] . ",";
    $params .=  $_POST['MEG+1'] . ",";
    $params .=  $_POST['MEG-1'] . ",";
    $params .=  $_POST['HEG+1'] . ",";
    $params .=  $_POST['HEG-1'] . ",";
    $params .=  $_POST['LEG+1'] . ",";
    $params .=  $_POST['LEG-1'] . ",";
    $params .=  $_POST['combine'] . ",";
    $params .=  $_POST['xmin'] . ",";
    $params .=  $_POST['xmax'] . ",";
    $params .=  $_POST['ymin'] . ",";
    $params .=  $_POST['ymax'] . ",";
    $params .=  $_POST['bin'] . ",";
    $params .=  $_POST['bin1'] . ",";
    $params .=  $_POST['bin2'] . ",";
    $params .=  $_POST['H-like'] . ",";
    $params .=  $_POST['He-like'] . ",";
    $params .=  $_POST['Fe'] . ",";
    $params .=  $_POST['shift'];

    // set the cookie to expire in 4 hours
    setcookie('tgPlotParams',$params, time() + 3600 * 4);

  }
?>


<html>
<head>
 <meta http-equiv="Pragma" content="no-cache">
 <meta http-equiv="Cache-Control" content="no-cache">
</head>
<body style='background-color:#FFFFFF;color:#329ABB;padding:0px'>
<!--[if IE]>
<style type='text/css' media='screen'>  
img { 
width: 100%;
}
</style>
<![endif]-->

<?php

$tmpdir = "tmp";

$isisscript = "/cxc/apps/bin/isis-script";
$phadir = "/cxc/data/tgcat/archive/arcA/pha2";
$arfdir = "/cxc/data/tgcat/archive/arcA/arf";
$rmfdir = "/cxc/data/tgcat/archive/arcA/rmf";

$id = $_REQUEST['id'];
$froot = $_REQUEST['fr'];
$d = $_REQUEST['d'];
$r = $_REQUEST['r'];

if ( !$froot || ! preg_match( '/[0-9]*T[0-9]*/', $froot ) ){
  print "Bad input for 'fr': $froot. Sorry!";
  exit;
 }
if( $d && $r ) { $froot  = "$tmpdir/multiple$d$r/$froot"; }
else { $froot = "$tmpdir/$froot"; }

//uncomment next line for debugging
// print "<script type='text/javascript'>alert('in tgRunPlot, froot=$froot');</script>";

// 
// check to see if we are to combine
//

if ( preg_match( '/,/',$id) ){ $id = split( ',',$id ); }
else { $id = Array( $id ); }

$tgcf = "tgcid_${id}";
$dcols  = "[4,11,10,9]";
$decols = "[5,7,6,5]";
$fontsettings = "
variable v = struct {xmin,xmax,ymin,ymax};
v.xmin=0.065;
v.xmax=0.98;
v.ymin=0.13;
v.ymax=0.97;
set_outer_viewport( v );
charsize(1);
";

if ( ! $_POST['doplot'] )
  {    
    #
    # define defaults
    #
    #print "this will be default plot";
    if ( $_GET['det'] == "ACIS" && $_GET['grat'] == "HETG" ) { $_POST['MEG-1'] = 1; $_POST['MEG+1'] = 1; }
    else { $_POST['LEG-1'] = 1; $_POST['LEG+1'] = 1; }
    if ( $_GET['det'] == "HRC" ){ $_POST['detector'] = "HRC"; }
    $_POST['plot'] = 'Counts/X/s';
    $_POST['xunit'] = "KeV";
    $_POST['combine'] = 1;
    $_POST['errbar'] = 1;
    $_POST['bin'] = 1;
    $_POST['bin1'] = 2;
    $_POST['bin2'] = 4;
    $_POST['xlog'] = 1;
    $_POST['ylog'] = 1;
    $_POST['shift'] = 0.0;

    # if cookie available and user wants to use saved parameters use those

    #print_r($_GET);
    #print_r($_COOKIE['tgPlotParams']);

    if ( $_COOKIE['tgPlotParams'] && $_GET['usesaved'] ) {

      //print_r("using saved parameters");

      $saved_params = explode(',',$_COOKIE['tgPlotParams']);

      $_POST['plot'] = $saved_params[0];
      $_POST['xunit'] = $saved_params[1];
      $_POST['yunit'] = $saved_params[2];
      $_POST['xlog'] = $saved_params[3];
      $_POST['ylog'] = $saved_params[4];
      $_POST['errbar'] = $saved_params[5];
      
      # only set the ones we need based on detector and gratings
      if ( $_GET['det'] == "ACIS" && $_GET['grat'] == "HETG" ) {
	if( $saved_params[6] || $saved_params[7] || $saved_params[8] || $saved_params[9] ) {
	  $_POST['MEG+1'] = $saved_params[6];
	  $_POST['MEG-1'] = $saved_params[7];
	  $_POST['HEG+1'] = $saved_params[8];
	  $_POST['HEG-1'] = $saved_params[9];
	}
	else {	  
	  $_POST['MEG+1'] = "checked";
	  $_POST['MEG-1'] = "checked";
	  $_POST['HEG+1'] = "";
	  $_POST['HEG-1'] = "";
	}
      }
      else {
	if( $saved_params[6] || $saved_params[7] || $saved_params[8] || $saved_params[9] ) {
	  $_POST['LEG+1'] = "checked";
	  $_POST['LEG-1'] = "checked";
	}
	else {
	  $_POST['LEG+1'] = $saved_params[10];
	  $_POST['LEG-1'] = $saved_params[11];
	}
      }
      $_POST['combine'] = $saved_params[12];
      $_POST['xmin'] = $saved_params[13];
      $_POST['xmax'] = $saved_params[14];
      $_POST['ymin'] = $saved_params[15];
      $_POST['ymax'] = $saved_params[16];
      $_POST['bin'] = $saved_params[17];
      $_POST['bin1'] = $saved_params[18];
      $_POST['bin2'] = $saved_params[19];
      $_POST['H-like'] = $saved_params[20];
      $_POST['He-like'] = $saved_params[21];
      $_POST['Fe'] = $saved_params[22];
      $_POST['shift'] = $saved_params[23];
    } 

    else {
      if($_GET['usesaved']) {
	print "no cookie found\n";
      }
    }
  }

if ( $_POST['doplot'] || 1 ) {

  $power = 1;
  switch( $_POST['plot'] ){ 
  case "Counts/X/s":
    //print_r(" doing counts plot");
    $pfunc = "plot_data";
    break;
  case "Fgam":
    //print_r(" doing fgam plot");
    $pfunc = "plot_unfold";
    $power=2;
    break;
  case "FgamX":
    //print_r(" doing fgamx plot");
    $pfunc = "plot_unfold";
    $power=1;
    break;    
  case "Fx":
    //
    // choose the correct power option
    //
    // print_r(" doing fx plot");
    if ( preg_match('/^A|nm|micron|mm|m|cm$/',$_POST['xunit']) )
      {
	$power = 0;
      }
    elseif ( preg_match('/eV|Hz$/',$_POST['xunit']) )
      {
	$power = 2;
      }
    $pfunc = "plot_unfold";
    break;
  case "XFx":
    //
    // choose the correct power option
    //
    // print_r(" doing xfx plot");
    if ( preg_match('/^A|nm|micron|mm|m|cm$/',$_POST['xunit']) )
      {
	$power = 1;
      }
    elseif ( preg_match('/eV|Hz$/',$_POST['xunit']) )
      {
	$power = 3;
      }
    $pfunc = "plot_unfold";
    break;
  default:   # Counts/bin
    // print_r(" defaulting to counts plot");
    $pfunc = "plot_counts";
    break;
  }
  //
  // the plot units to be passed to Plot_Unit function
  //
  $punits =  "\"" . $_POST['xunit'] . "\",\"" . $_POST['yunit'] . "\"";
  if ( ! $_POST['yunit'] || $_POST['yunit'] == "Photons" ) { $punits =  "\"" . $_POST['xunit'] . "\""; }

  //
  // setup the default popt ( plot options ) structure 
  //
  $xrange = Array("NULL","NULL");
  $yrange = Array("NULL","NULL");
  if ( is_numeric($_POST['xmin']) )
    {
      $xrange[0]=$_POST['xmin'];
    }
  if ( is_numeric($_POST['xmax']) )
    { 
      $xrange[1]=$_POST['xmax']; 
    }
  if ( is_numeric($_POST['ymin']) )
    {
      $yrange[0]=$_POST['ymin'];
    }
  if ( is_numeric($_POST['ymax']) )
    { 
      $yrange[1]=$_POST['ymax']; 
    }
  $xrange = "{" . join(",",$xrange ) . "}";
  $yrange = "{" . join(",",$yrange ) . "}";
  
  if ( ! $_POST['errbar'] ) { $decols = "[0,0,0,0]"; }
  $popt = "struct { dcol=$dcols, decol=$decols, dsym=[0,0,0,0], power=$power, xrng=$xrange, yrng=$yrange }";

  //
  // which data to load
  //
  $dataload = "";
  $rmf = Array();
  $arf = Array();
  $lbk = Array();
  $dc = 0;

  //
  // get the parts of data to load up 
  // 
  if ( $_POST['MEG-1'] ) { $dc++; $dataload .= "9,"; array_push($rmf,"meg_-1.rmf"); array_push( $arf,"meg_-1.arf");  }
  if ( $_POST['MEG+1'] ) { $dc++; $dataload .= "10,"; array_push($rmf,"meg_1.rmf"); array_push( $arf,"meg_1.arf");  }
  if ( $_POST['HEG-1'] ) { $dc++; $dataload .= "3,"; array_push($rmf,"heg_-1.rmf"); array_push( $arf,"heg_-1.arf");  }
  if ( $_POST['HEG+1'] ) { $dc++; $dataload .= "4,"; array_push($rmf,"heg_1.rmf"); array_push( $arf,"heg_1.arf");  }
  if ( $_POST['LEG-1'] ) {     
    $dc++; 
    if ( $_POST['detector'] == "HRC" ) {
      $dataload .= "1,"; 
      array_push($lbk,"pha2_bg_-1");      
    }
    else {
      $dataload .= "3,";
      array_push( $rmf, "leg_-1.rmf" );
      array_push( $arf, "leg_-1.arf" );
    }
  }
  if ( $_POST['LEG+1'] ) { 
    $dc++; 
    if ( $_POST['detector'] == "HRC" ) {
      $dataload .= "2,";
      array_push($lbk,"pha2_bg_1");  
    }
    else {
      $dataload .= "4,";
      array_push( $rmf, "leg_1.rmf" );
      array_push( $arf, "leg_1.arf" );
    }
  }

  $dataload = trim( $dataload, "," );
  if ( $dc > 1 ) { $dataload = "[ $dataload ]"; }
  $dataList = "[1:".$dc."]";

  //
  // combine must only combine if more than one dataset
  //  
  $combf = "";
  if ( $_POST['combine'] and $dc > 1 ) { $combf = "-"; }

  if ( (($_POST['MEG-1'] ||  $_POST['MEG+1']) && ( $_POST['HEG-1'] || $_POST['HEG+1'] )) && $_POST['combine'] )
    {
      $matchgrids = 1;
    }

  //
  // bin spec
  //
  $grppha="";
  if ( $pfunc == "plot_unfold" )
    {
      $grppha = "\ngrppha_sn_min(h,10e-8,1,0);";
    }
  if ( $_POST['bin'] )
    {
      if ( is_numeric( $_POST['bin1'] ) and is_numeric( $_POST['bin2'] ) )
	{
	  $grppha = "\ngrppha_sn_min(h,".$_POST['bin1'].",".$_POST['bin2'].",0);";
	}
    }

  //
  // make the file text
  //
  $load_data_string = "h = [ \n";
  foreach ( $id as $tid ){ $load_data_string .= "load_data( \"$phadir/tgcid_${tid}_pha2\", $dataload ),\n"; }
  $load_data_string .= "]";

  $cmdFile = "
append_to_isis_load_path( \".\" );
require(\"isis_fancy_plots\");
putenv( \"PGPLOT_BACKGROUND=white\" );
putenv( \"PGPLOT_FOREGROUND=black\" );
variable h,a,r;
$load_data_string;
";
  if ( count($rmf) >=1 ) 
    {
      $arfload = "a = [ \n";
      foreach ( $id as $tid ){ 
	$arfload .= "array_map( Integer_Type, &load_arf, \"$arfdir/tgcid_{$tid}_\" + [\"" . join("\",\"",$arf) . "\"] ),\n";
      }
      $arfload .= "]";

      $rmfload = "r = [ \n";
      foreach ( $id as $tid ){ 
	$rmfload .= "array_map( Integer_Type, &load_rmf, \"$rmfdir/tgcid_${tid}_\" + [\"" . join("\",\"",$rmf) . "\"] ),\n";
      }
      $rmfload .= "]";
     
      $cmdFile .= "$arfload;
$rmfload;
array_map( Void_Type, &assign_rsp, a, r, h );
";
    }
  if ( count($lbk) >= 1 ) 
    { 
      $lbks = "[ \n";
      foreach ( $id as $tid ){ 
	$lbks .= "\"$phadir/tgcid_${tid}_\" + [\"".join("\",\"",$lbk) . "\"],\n";
      }
      $lbks .= " ] ";

      $cmdFile .= "array_map( Void_Type, &define_back, h, $lbks );\n"; 
    }
  if ( $matchgrids ) { $cmdFile .= "match_dataset_grids( h );\n"; }
  if ( $_POST['ylog'] ) { $cmdFile .= "ylog;\n"; }
  if ( $_POST['xlog'] ) { $cmdFile .= "xlog;\n"; }

//   print "<br> punits = " . $punits . " <br>";
//   print "<br> pfunc = " . $pfunc . " <br>"
    ;
  
  $cmdFile .= "Plot_Unit( $punits );
popt = $popt; $grppha
open_plot( \"$froot.png/PNG\" );
resize( 25, 0.7 );
$fontsettings
$pfunc( ${combf}h, popt );";

  //
  // set variables for marking feature locations 
  //
  if( $_POST['H-like'] ) { $h = 1; }
  else { $h = 0; }
  if( $_POST['He-like'] ) { $he = 1; }
  else { $he = 0; }
  if( $_POST['Fe'] ) { $fe = 1; }
  else { $fe = 0; }
  if( is_numeric($_POST['shift']) ) { $shift = $_POST['shift']; }
  else { $shift = 0.0; }

$cmdFile .= "
variable PLOT_H_LIKE  = $h ;
variable PLOT_He_LIKE = $he ;
variable PLOT_Fe      = $fe ;
variable PLOT_Edges   = 0 ;
variable Redshift     = $shift;

if( PLOT_H_LIKE or PLOT_He_LIKE or PLOT_Fe )
{
  atoms( aped );

% NOTE: no K, Na in tgcat's version of the AtomDB
%
%  variable K = 19;
%  variable Na = 11 ; 
%  variable el = [C, N, O, Ne, Na, Mg, Al, Si, S, Ar, K, Ca, Fe ];
  variable el = [C, N, O, Ne, Mg, Al, Si, S, Ar, Ca, Fe ];

  variable l_H    = Integer_Type[ length( el ) ];
  variable l_Hb   = Integer_Type[ length( el ) ];
  variable l_He   = Integer_Type[ length( el ) ];
  variable l_Fe  ;

  variable i ;
  for( i=0; i<length(el); i++) l_H[ i ]  = where(trans(el[i], el[i], 4, 1))[0];
  for( i=0; i<length(el); i++) l_Hb[ i ] = where(trans(el[i], el[i], 7, 1))[0];
  for( i=0; i<length(el); i++) l_He[ i ] = where(trans(el[i], el[i]-1, 7, 1) )[0];

  l_Fe = [ where(
             trans( Fe, 17,  27, 1 ) or
             trans( Fe, 17 ,  3, 1 ) or
             trans( Fe, 16,  10, 3 ) or
             trans( Fe, 18,   3, 1 ) or
             trans( Fe, 18 ,  3, 2 ) or
             trans( Fe, 19 ,  6, 1 ) or
             trans( Fe, 20 ,  7, 1 ) or
             trans( Fe, 21 ,  7, 1 ) or
             trans( Fe, 20 ,  6, 1 ) or
             trans( Fe,  9,  13, 1 )
	  or trans( Fe, 23,      52,    1 )
	  or trans( Fe, 24,      6,     1 )
	  or trans( Fe, 19,      243,   1 )
	  or trans( Fe, 24,      8,     3 )
	  or trans( Fe, 18,      180,   1 )
	  or trans( Fe, 18,      164,   1 )
	  or trans( Fe, 23,      20,    5 )
	  or trans( Fe, 17,      59,    1 )
	  or trans( Fe, 20,      58,    1 )
	  or trans( Fe, 19,      68,    1 )
	  or trans( Fe, 18,      56,    1 )
	  or trans( Fe, 19,      11,    1 )
	  or trans( Fe, 17,      23,    1 ) 
	  or trans( Fe, 18,      5,     1 )
	  or trans( Fe, 18,      4,     1 )
	  or trans( Fe, 17,      5,     1 )
	  or trans( Fe, 18,      29,    3 )
	  or trans( Fe, 17,     118,    1 )
	  or trans( Fe, 18,     138,    1 )
	  or trans( Fe, 18,      49,    1 )
	  or trans( Fe, 18,       9,    1 )
     ) ] ; 

  variable lst = line_label_default_style;

  lst.top_frac = 0.88;  lst.bottom_frac = lst.top_frac - 0.05;
  lst.offset = 0.20;
  lst.char_height = 0.80 ;

  if ( PLOT_Fe )      plot_group( l_Fe,  3, lst, Redshift );
  if ( PLOT_He_LIKE ) plot_group( l_He,  4, lst, Redshift );
  if ( PLOT_H_LIKE )
  {
    plot_group( l_H,   2, lst, Redshift );
    plot_group( l_Hb, 13, lst, Redshift );
  }
}";
  
  $cmdFile .=  "close_plot();";
  $cmdFile .= "write_plot(\"${froot}ascii\");\n";
  
  //
  // end make file text
  //

  $h = fopen( "$froot.commands.sl","w" );  
  fwrite( $h, $cmdFile );
  fclose( $h );

  system( "$isisscript $froot.commands.sl 2> $froot.error" );
  system( "gzip -f ${froot}ascii.dat 2>> $froot.error" );

  // 
  // make zip file of all existing .gz files in the folder
  //   
  $result = -1; // to check exit value
  if( $d && $r ) {
    $cwd = getcwd();
    $multi = "multiple$d$r";

    chdir( "$cwd/tmp" );
    system( "tar -cf $multi/${d}M${r}.tar $multi/[0-9]*T[0-9]*ascii.dat.gz", $result);
    //uncomment the next line for debugging
    //print "<script type='text/javascript'>alert('TEST $result');</script>";
    chmod( "${d}M${r}.tar", 0777);
    chdir( "$cwd" );
  }

  $h = fopen( "$froot.error", "rw" );
  $errors = fread( $h, filesize( "$froot.error" ) );    
  if( $result == 2 ) { 
    fwrite( $h, "\nFATAL ERROR MAKING TAR FILE\n");
  }
  fclose( $h );

  print "<!-- <br><br>";
  print "<br><br>ISIS commands file:<br><code><pre>$cmdFile</pre></code><br>\n";
  print "ISIS Errors:<br><code><pre>$errors</pre></code><br>\n";
  print "-->\n";
 }

//print_r( $_POST );
$updatedTime = date('H:i:s');

$error_str = "INPUT ERROR:<br>";
if(! $dataload) {
  $error_str .= "Select at least one of -1, +1.<br>";
 }
if( $_POST['xmin'] && $_POST['xmax'] && $_POST['xmin'] >= $_POST['xmax'] || $_POST['ymin'] && $_POST['ymax'] &&  $_POST['ymin'] >= $_POST['ymax']) {
  $error_str .= "Max must be strictly greator than min.<br>";  
 }


if( $error_str === "INPUT ERROR:<br>" ) {
  print "<center>
<a href='$froot.png' target='_blank'>
<img style='top:0px;border:0px;margin:0px;max-height:100%;max-width:100%;' src='$froot.png' title='updated: $updatedTime'>
</a>
</center>
";
 }
 else {
   print $error_str;
 }

if ( ! $_POST['doplot'] && file_exists( "$froot.png" ) ){
  print "<!-- non-renewed plot --></body>\n</html>\n";
  exit;
 }

?>
</body>
</html>
