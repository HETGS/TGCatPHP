<?php
##############################################################################
##
## TGCAT WEBPAGE DOCUMENT
##
## TITLE: tgcat_data.php
##
## DESCRIPTION:
##   
##        this script generates the main datapage for TGCat queries
##        calling make_table from the tgcat_data_table.php library
##
## REVISION:
##
##     v 1.0 Arik Mitschang
##
##############################################################################

$type = $_REQUEST['t'];
$qid = $_REQUEST['q'];
$ids = $_REQUEST['ids'];
$tableCode = $_REQUEST['c'];
$email = $_REQUEST['email'];
$ucode = $_REQUEST['ucode'];
$hasscript = $_REQUEST['dlusedjs'];
$tag = $_REQUEST['tag'];

require "tgDatabaseConnect.php";

$q = mysql_query( "SELECT count(*) as deny FROM pkgque WHERE ucode='$ucode'" );
$qr = mysql_fetch_assoc( $q );
if ( $qr['deny'] ) {
  header( "Location: tgData.php?q=$qid" );
  exit;
 }
$tgids = Array();

if ( $type == "B" )
  {
    if ( $tableCode == "s" ){
      $myids = Array();
      $q = "SELECT id FROM obsview WHERE srcid IN ( $ids )";
      $q = mysql_query( $q );
      while ( $row = mysql_fetch_assoc( $q ) ){
	array_push( $myids, $row['id'] );
      }
    }
    else{
      $myids = split(",",$ids );    
    }
    
    $myprods = Array();

    foreach ( array_keys( $_POST ) as $prod )
      {
 	if ( preg_match( "/prod_.*/",$prod ) )
 	  {
	    $split = split( '_', $prod );	    
 	    array_push( $myprods, $split[1] );
 	  }
      }
    foreach ( $myids as $myid )
      {
 	$tgids["$myid"] = $myprods;
      }
    if ( count( $myprods ) == 0 ){ 
      $tgids = 0;
    }    
  }
else
  {
    foreach ( array_keys( $_POST ) as $kk ){ 
      
      $kl = split('_',$kk); 
      
      $key = "$kl[1]";
      
      if ( ! array_key_exists($key,$tgids) ) 
	{ 
	  $tgids["$kl[1]"] = array(); 
	}
      
      array_push( $tgids["$kl[1]"], $kl[0] ); 
    }
  }

if ( $tgids )
  {
    //
    // make the new pkgque entry and get the primary id from it   
    //
    $qinsert = "INSERT INTO pkgque SET queue_time=now(),request_ip='$_SERVER[REMOTE_ADDR]',ucode='$ucode'";
    if ( $email ) { $qinsert = $qinsert . ",email='$email'"; setcookie( "tgNotAddr", $email, time() + 365*86400 ); }
    if ( $tag ) { $qinsert = $qinsert . ",tag='". mysql_real_escape_string($tag) . "'"; }

    mysql_query($qinsert)  or die("Could not insert into pkgque: " . mysql_error());
    $PKGID = mysql_insert_id();
    
    $qinsert = "INSERT INTO pkgkart (pkgid,id,types) VALUES ";
    foreach ( array_keys($tgids) as $item )
      {
	foreach ( $tgids[$item] as $product )
	  {
	    $qinsert = $qinsert . "( $PKGID,$item,'$product'),";
	  }
      }    

    $qinsert = rtrim($qinsert,",");   
    mysql_query($qinsert)  or die("Could not insert into pkgkart ($qinsert): " . mysql_error());

    #$qsize = "select sum(size) from pkgkart as p left join files as f on ( p.id=f.id and p.types=f.type ) where pkgid=$PKGID and name='file'";
    #$qsh = mysql_query($qsize) or die( "Could not determine size of pkg: " . mysql_error());
    #$qsize = mysql_fetch_array($qsh);
    #$qsize = $qsize[0];
    #
    # do not limit queue size limits ( Dave's request 4/2/10 )
    #

    $qsize = -1;
    $MAXDOWNLOADSIZE = 1;
    
    $MAXSIZE = $MAXDOWNLOADSIZE;
    if ( $qsize < $MAXSIZE ){     
      $qinsert = "UPDATE pkgque SET status='QUEUED' WHERE pkgid=$PKGID";
      mysql_query($qinsert)  or die("Could not update pkgque to QUEUED: " . mysql_error());
      $q = "UPDATE query SET downloads=if( downloads is null, $PKGID, concat( downloads, ',', $PKGID ) ) WHERE qid='$qid'";
      mysql_query( $q ) or die("Could not tie download with query" . mysql_error());
    }
    else
    {
      $qinsert = "UPDATE pkgque SET status='OVERSZ',size=$qsize WHERE pkgid=$PKGID";
      mysql_query($qinsert)  or die("Could not set status for package" . mysql_error());
    }
    //
    // set a cookie if we can adding the package
    // to the list for this user
    //
    setcookie( "tgpkgs", rtrim( $PKGID . "," . $_COOKIE['tgpkgs'], "," ), time() + 1*86400 );
  }
if ( $hasscript ){
  header( "Location: tgData.php?q=$qid&n=DQ&p=$PKGID" );
 }
else {
  header( "Location: tgData.php?q=$qid&n=DQ&p=$PKGID" );
  //print "welcome to the no script download confirmation page:
  //your query id is: $qid
  //your package id is: $PKGID
  //";
}
?>
