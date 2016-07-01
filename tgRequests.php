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
