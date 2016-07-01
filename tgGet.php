<?php

require "tgDatabaseConnect.php";

// test of cvs method by dph - no change to source.

//
// the parameters id and file will be required
//
$f = $_REQUEST['file'];
$i = $_REQUEST['id'];
$s = $_REQUEST['src'];

if ( ! ( $i && $f ) ){
  print "ERROR, must supply file name and id";
  exit;
 }

$q = "SELECT concat(arc,'/',type,'/','tgcid_',id,'_',value) AS path FROM files WHERE id=$i AND value='$f'";
$r = mysql_fetch_assoc(mysql_query($q)) or die(mysql_error());
if ( ! ( $r || $r['path'] ) ){
  print "WARNING, no such file $f for id $id";
  exit;
 }

$src = $_SERVER['REMOTE_ADDR'];
if ( $s ) { $src = $src . ";" . $s; }

$fname = getcwd() . "/archive/$r[path]";
$size = filesize($fname);
$sent = 0;
$blocksize = (2 << 20);
$handle = fopen($fname,"r");
if ( $f == "evt0.par" ){
  header( "Content-Type: text/plain" );
}
else {
  header( "Content-Type: application/octet-stream" );
}
header( "Content-Disposition: attachment; filename=tgcid_${i}_$f" );
header( "Content-length: " . $size*1024 );

while( $sent < $size ){
  echo fread($handle,$blocksize);
  $sent += $blocksize;
 }
//
// only after file is entirely retrieved should we add it to the list
// works only for larger files
//
$q = "INSERT INTO pkgkart SET id=$i,file='$f',source='$src'";
mysql_query($q) or die( mysql_error());

exit(0);
?>
