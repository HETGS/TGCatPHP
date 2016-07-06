<?php
  //  error_reporting( E_ALL );
  //  ini_set( "display_errors",1);
ob_start( "ob_gzhandler" );
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title> TGCat - Extraction Summary </title> 
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<?php include "styleInc.php" ?>
</head>

<body>
<script type="text/javascript" src="wz_tooltip.js"></script>
<script type="text/javascript" src="tip_followscroll.js"></script>
<script type="text/javascript" src="tip_centerwindow.js"></script>
<script type="text/javascript" src="superMessage.js"></script>
<script type="text/javascript" src="plotpage.js"></script>
<?php
 //error_reporting( E_ALL );
 //ini_set( "display_errors",1);
require "tgMainLib.php";
require "tgDatabaseConnect.php";
require "tgMenu.php";
require "tgNotifications.php";
$id = $_REQUEST['i'];
$sid = $_REQUEST['s'];

//
// generate our random file string
//

// *** dph ***
// problems here w/ the root name? combined data plot dumps fail...

$fr = $_GET['fr'];

if( $fr && $_GET['d'] && $_GET['r'] )  // for multiple plots
  { 
    $d = $_GET['d'];
    $r = $_GET['r'];
    $dr = array($d,$r);
    $froot = "multiple$d$r/$fr"; 
  }
 else
   {
     $dr = FALSE;
     if ($fr) {
       $froot = $fr;    // set by tgProcessors.php if a combined plot
     }
     else
       {  
	 $froot = time() . "T" . mt_rand();   // for single plots
       }
   }


if ( $sid ){ 
  //
  // get source ids for multi preview
  //
  $q = mysql_fetch_assoc(mysql_query("SELECT group_concat( distinct(id) ) as id FROM obsview WHERE srcid in ( $sid )"));
  $id = $q['id'];
 } 
//
// the info we get from obsview will be different
// depending on what kind of preview we are looking
// at
//

// old band coluns:

// heg_band as 'heg_band(c/s)',                                                                         
//  meg_band as 'meg_band(c/s)',                                                                         
//  leg_band as 'leg_band(c/s)',                                                                         
//  letg_acis_band as 'letg_acis_band(c/s)',                                                             


if ( $d || $r ) {
  $eType = "single"; // multiple singles plotted (NOT combined)

  $infoCols = "id,srcid,obsid,review,obi,target,object,simbad_ID,
               instrument,grating,exposure as 'exposure(s)',ra,decl,heg_band as 'heg_band(c/s)',
               meg_band as 'meg_band(c/s)',leg_band as 'leg_band(c/s)',letg_acis_band as 'letg_acis_band(c/s)',
               zeroth_order as 'zero_order(c/s)',readmode,datamode,proc_date,zo_method,date_obs";
 }
elseif ( preg_match( '/,/',$id )) {
  $eType = "combined" ;  // many, summed into one plot

  $infoCols = "'Multi Preview' as object,
                group_concat( obsid ) as obsid,
                group_concat( distinct(id) ) as ids,
                group_concat( distinct(srcid) ) as srcids,
                group_concat( distinct( instrument ) ) as instruments,
                group_concat( distinct( grating ) ) as gratings,
                sum( exposure ) as 'total_exposure(s)',
                avg(ra) as 'ra',
                avg(decl) as 'decl',
                avg(heg_band) as 'heg_band(c/s)',
                avg(meg_band) as 'meg_band(c/s)',
                avg(leg_band) as 'leg_band(c/s)',
                avg(letg_acis_band) as 'letg_acis_band(c/s)',
                avg(zeroth_order) as 'zeroth_order(c/s)',
                from_unixtime( avg( unix_timestamp( proc_date ) ) ) as 'proc_date',
                from_unixtime( avg( unix_timestamp( date_obs ) ) ) as 'date_obs'";
}
 else {
   $eType = "single"; // just plotting one.
   $infoCols = "id,srcid,obsid,review,obi,target,object,simbad_ID,
               instrument,grating,exposure as 'exposure(s)',ra,decl,heg_band as 'heg_band(c/s)',
               meg_band as 'meg_band(c/s)',leg_band as 'leg_band(c/s)',letg_acis_band as 'letg_acis_band(c/s)',
               zeroth_order as 'zero_order(c/s)',readmode,datamode,proc_date,zo_method,date_obs";
 }

$type = $_REQUEST['t'];
if ( ! $type ){ $type="P"; }
if ( $type=="C" ){ print "<script type='text/javascript'>PLOTTED=true;</script>\n"; }

$q = mysql_query( "select $infoCols from obsview where id in ( $id )" ) or die( mysql_error() );
$info = mysql_fetch_assoc( $q );
?>

<div id='page'>
<?php
 //print "<!--\n";
 //print_r( $info );
 //print "\n-->";

print "
<h1> $info[object] </h1>
<div style='position:absolute;width:99.5%;bottom:26px;top:100px;margin:0px;'>
<div id='infoPane' style='position:absolute;left:0px;width:300px;'>
<i> $eType extraction product </i>
<table>
";
foreach ( array_keys( $info ) as $key ){  
  $displayF = preg_replace( '/,/',', ',$info[$key] );
  if ( strlen( $displayF ) > 25 ){ 
    $displayF = "<a href='#' title='$displayF'>" . substr( $displayF, 0, 22 ) . "...</a>"; 
  }
  switch ( $key ){
  case "review":
    $displayF = $REVIEW_CODES[$info[$key]];
    break;
  default:
    if ( is_numeric( $info[$key] ) && preg_match( '/\./',$info[$key] ) ){
      print "<!-- is numeric -->";
      $displayF = sprintf( '%.5e',$info[$key] );
    }
    if ( $key == "ra" || $key == "decl" ){
      $displayF = sprintf( '%.3f',$info[$key] );
    }
  }

  if ( $key == 'obsid'){
    print "<tr><th> $key </th><td> <a href='http://cda.harvard.edu/chaser/startViewer.do?menuItem=details&obsid=$displayF' target='_blank'> $displayF </a> </td></tr>\n";
  } elseif ($key == 'simbad_ID') {
    print "<tr><th> $key </th><td> <a href='http://simbad.harvard.edu/simbad/sim-id?Ident=$displayF' target='_blank'> $displayF </a> </td></tr>\n";
  } elseif($key == 'review'){
    print "<tr><th> $key </th><td> <a href='tgPrev.php?i=".$id."&amp;m=V' target='imageWin'> $displayF </a> </td></tr>\n";
  } else{
    print "<tr><th> $key </th><td> $displayF </td></tr>\n";
  }
}

$usesaved=$_GET['usesaved'];

print "
</table>
<br>

</div>

<div id='imagePane2' style='position:absolute;left:312px;right:0px;height:100%;z-index:0;'>";

//uncomment next line for debugging
//print "<script type='text/javascript'>alert('in tgPlot, $froot');</script>";

print"<iframe ALLOWTRANSPARENCY='true' style='z-index:0;position:relative;display:block;height:100%;width:100%;border:0px;' src='tgPrev.php?i=$id&amp;m=$type&amp;d=$d&amp;r=$r&amp;f=$fr&amp;usesaved=$usesaved' id='imagePreview' name='imageWin'></iframe>";

print "
</div>

</div>
";

?>

</div>

<?php
initMainMenu();
generateFileMenu();
generatePlotViewMenu( $id, $froot, $dr );
generateHelpTopicsMenu();
generateHelpMenu();
//generateQuickSearchBar();
finalizeMainMenu();
$msg = "";
if ( $info['review'] == "w" ){ $msg = "This extraction has a <b>warning</b> associated with it"; }
statusMessage( $msg );
?>

<script type='text/javascript'>

function closePreview(){
  document.getElementById('dbg').style.visibility='hidden';
  document.getElementById('imagePreviewFancy').style.visibility='hidden'; 
  document.getElementById('imagePreviewImage').src='';
  var img = document.getElementById('imagePreviewImage'); 
  var button = document.getElementById( 'sizeButton' );
  img.style.maxHeight = "90%";    
  button.innerHTML = "full size";  
  return false;
}
function showFullSize(){
  var img = document.getElementById('imagePreviewImage'); 
  var button = document.getElementById( 'sizeButton' );
  var container = document.getElementById( 'imagePreviewFancy' );

  if ( img.style.maxHeight == "none" ){
    img.style.maxHeight = "90%";    
    container.style.overflow="none";
    button.innerHTML = "full size";
  }
  else {
    img.style.maxHeight = "none";
    container.style.overflow="auto";
    button.innerHTML = "shrink";    
  }
  return false;
}
  
</script>

<div id='dbg' class='dialogBg' style='visibility:hidden;z-index:100;'></div>
<div id='imagePreviewFancy' 
class='dialog' 
style='visibility:hidden;padding-top:10px;z-index:200;text-align:center;color:#ffffff;overflow:auto;bottom:0px;'
>
<a href='#' onClick="return closePreview();">close</a>
<a href='#' 
onclick='TagToTip("imagePreviewText",STICKY,true,FADEIN,200,CLOSEBTN,true,CENTERWINDOW,true,WIDTH,700,OFFSETY,300,FONTSIZE,"18px",FADEOUT,300,CENTERALWAYS,true,BGCOLOR,"#EEEEEE",FONTCOLOR,"#444444",TITLEBGCOLOR,"#777777",BORDERCOLOR,"#777777",CLOSEBTNCOLORS,["#DDDDDD","#000000","#222222","#ffffff"],TEXTALIGN,"center");return false;'>
description
</a>
<a href='#' id='sizeButton' onClick="return showFullSize();">full size</a>
<br>
<div id='imagePreviewText'></div>
<img id='imagePreviewImage' 
style='border:8px solid #dddddd;max-height:85%;margin-bottom:8px;margin-top:8px;' >
<br>
</div>

</body>
</html>
