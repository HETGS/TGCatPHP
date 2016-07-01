<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<?php
##############################################################################
##
## TGCAT WEBPAGE DOCUMENT
##
## TITLE: tgcat_trend.php
##
## DESCRIPTION:
##
##      display overall trend information gathered from the tgcat database.
##      along with a sky coverage map that shows obvservations by grating type.
##      info in several categories including:
##
##            obsids
##            files
##            zo methods
##            exposure
##
##         and spectral properties
##
## REVISION:
##
##    -v 1.0 Arik Mitschang
##
##############################################################################
?>
<html>
<head>
<?php
require "styleInc.php";
require "tgMainLib.php"; 
require "tgDatabaseConnect.php";
require "tgMenu.php";
require "tgNotifications.php";

$type = $_GET['type'];
if ( !$type ) { $type="OBSID"; }
$C = $_REQUEST['C'];
$P = $_REQUEST['P'];
$E = $_REQUEST['E'];
if ( !$C && !$P && !$E ){ $C=1; }

$STRING_TYPE = Array( "SPECPROP" => "Spectral Property",
		      "OBSID"    => "Observation",
		      );
print "       <title>TGCat : Trends data : $STRING_TYPE[$type] </title>\n";

?>
</head>

<body>
<div id='page'>

<?php
$string = "$STRING_TYPE[$type] Trends";
  //print "<h1> $string </h1>";
?>

<?php

if ( $type == "SPECPROP" )
{
  $q = "select distinct(label),wmid,wlo,whi from spec_prop";
  
  $sprop = mysql_query( $q ) or die( mysql_error() );

  print "<table class='dTable' id='trend'>\n";

  $TDspec = "th  colspan=5";
  print "<tr >
             <$TDspec><strong> properties </th>
        ";
  if ($C){ print "<$TDspec><strong> count rate (cnts/sec) </td>\n"; }
  if ($P){ print "<$TDspec><strong> photon flux (phts/cm^2/sec) </td>\n"; }
  if ($E){ print "<$TDspec><strong> energy flux (ergs/cm^2/s) </td>\n"; }

  //
  // include links here to the help page for each row ( glossary terms )
  //
  
  print "<colgroup span='5' class='tableLabel'>";
  $hrow = "<tr >\n";
  $hrow .= "
         <th >label</th>
         <th >wmid</th>
         <th >wlo</th>
         <th >whi</th>
        ";
  if ($C) { $hrow .= "
         <th></th>
         <th >min</th>
         <th >max</th>
         <th >mean</th>
         <th >stddev</th>
         ";
  }
  if ($P) { $hrow .= "
         <th></th>
         <th >min</th>
         <th >max</th>
         <th >mean</th>
         <th >stddev</th>
         ";
  }
  if ($E) { $hrow .= "
         <th></th>
         <th >min</th>
         <th >max</th>
         <th >mean</th>
         <th >stddev</th>
         ";
  }   
  $hrow .= "</tr>\n";

  $count = 0;
  while ( $row = mysql_fetch_array( $sprop ) )
    {

      $q2 = "select ".
	"min(count_rate),max(count_rate),avg(count_rate),std(count_rate), ".
	"min(photon_flux),max(photon_flux),avg(photon_flux),std(photon_flux), ".
	"min(energy_flux),max(energy_flux),avg(energy_flux),std(energy_flux) ".
	"from spec_prop as s left join obsid as o on (s.id=o.id) ".
	"where label=\"$row[0]\" and flag=0 and reject='N'";

      $props = mysql_query( $q2 ) or die( mysql_error() );
      $prop = mysql_fetch_array( $props );

      if ( ($count %15) == 0 ){ print $hrow; }
      $tr_class = ($count % 2) ? "tr" : "br";
      print "
      <tr class=$tr_class>\n";
      print "
             <td> $row[0] </td> 
             <td> $row[1] </td>  
             <td> $row[2] </td> 
             <td> $row[3] </td>
            ";
      if ($C) { print "<td style='border-right:1px solid #000000'></td>\n";
	for ( $i=0; $i<4; $i++ ){
	  print " <td> " . sprintf("%.5e",$prop[$i]) . "</td>\n";
	}
      }
      if ($P) { print "<td style='border-right:1px solid #000000'></td>\n";
	for ( $i=4; $i<8; $i++ ){
	  print " <td> " . sprintf("%.5e",$prop[$i]) . "</td>\n";
	}

      }
      if ($E) { print "<td style='border-right:1px solid #000000'></td>\n";
	for ( $i=8; $i<12; $i++ ){
	  print " <td> " . sprintf("%.5e",$prop[$i]) . "</td>\n";
	}
      }
      
      print "
      </tr>\n";
      $count ++;
    }

  print "</table>\n";
    
}
?>

<?php
if ( $type == "OBSID" ){
?>

<center>
<div id='openingBanner'>
<img src='image/tgcat_skymap.png'>
</div>
</center>
<style type='text/css'>
   table.dTable td { text-align:left; padding-right:10px; }
</style>

<?php
    $q = 'select count(*),'.
      'count(distinct(srcid)),'.
      'min(date_obs),'.
      'max(date_obs),'.
      'format(min(exposure),0),'.
      'format(max(exposure),0),'.
      'format(avg(exposure),0),' .
      'format(sum(exposure),0)' .
      'from obsid where reject="N"';
    $qs = mysql_query( $q );
    $qr = mysql_fetch_array( $qs );
    
    $TOTAL_OBS = $qr[0];

    print "<table><tr style='vertical-align:top'><td>\n";

    print "<table class=dTable>
          <tr ><th> Observations </th><th></th></tr>
          ";    
    print "
           <tr class=tr>
              <td> Total Obsids: </td>
              <td> $qr[0] </td>
           </tr>
           <tr class=br>
              <td> Distinct Sources: </td>
              <td> $qr[1] </td>
           </tr>
         ";

    $q = 'select instrument,grating,count(*) from obsid where reject="N" group by grating,instrument';
    $qs2 = mysql_query( $q );
    $count = 1;
    while ( $row = mysql_fetch_array( $qs2 ) )
      {
	$tr_class = ($count % 2) ? "tr" : "br";
	print "
           <tr class=$tr_class>
              <td>$row[0] $row[1] Observations: </td>
              <td>$row[2] </td>
           </tr>
           ";
	$count++;
      }

    $q = 'select readmode,count(*) as num from obsid where reject="N" and instrument="ACIS" group by readmode';
    $qqr = mysql_query( $q );
    while ( $row = mysql_fetch_assoc( $qqr ) ){
	$tr_class = ($count % 2) ? "tr" : "br";
	print "
           <tr class=$tr_class>
           <td> ACIS $row[readmode] Mode </td><td> $row[num] </td>
           </tr>
        ";
	$count ++;
      }
    print "</table>\n";

    print "</td><td>\n";
    
    print "<table class=dTable>
          <tr ><th> Exposure (s) </th><th></th></tr>
          ";    
    print "
           <tr class=tr>
              <td> Min Exposure: </td>
              <td> $qr[4] </td>
           </tr>
           <tr class=br>
              <td> Max Exposure: </td>
              <td> $qr[5] </td>
           </tr>
           <tr class=tr>
              <td> Mean Exposure: </td>
              <td> $qr[6] </td>
           </tr>
           <tr class=tr>
              <td> Cumulative Exposure: </td>
              <td> $qr[7] </td>
           </tr>         
          ";
    print "</table>\n";   

    print "</td><td>\n";

    print "<table class=dTable>
          <tr ><th> &nbsp; ZO method </th><th></th></tr>
          ";    
    $q = 'select zo_method,count(*) from obsid where reject="N" group by zo_method order by zo_method desc';
    $qs3 = mysql_query( $q );
    $count = 1;
    while ( $row = mysql_fetch_array( $qs3 ) )
      {
	$tr_class = ($count % 2) ? "tr" : "br";
	$zo = $row[0];
	print "
           <tr class=$tr_class>
              <td>$zo: </td>
              <td>$row[1] </td>
           </tr>
           ";
	$count++;
      }
    print "</table>\n";   

    print "</td><td>\n";

    print "<table class=dTable>
          <tr ><th> Archive </th><th></th></tr>
          ";    
    $q = 'select count( * ),sum(f.size),count(distinct(f.type)) from files as f left join obsid as o on ( f.id=o.id ) where f.name="file" and o.reject="N"';
    $qs4 = mysql_query( $q );
    $count = 1;
    $qr4 = mysql_fetch_array( $qs4 );
    $ASIZEGB = sprintf("%.2f",$qr4[1]/(1024.0*1024.0));
    $AVSZOBS  = sprintf("%.2f",($qr4[1]/$TOTAL_OBS)/(1024.0));
    $AVSZFILE = sprintf("%.2f",($qr4[1]/$qr4[0])/(1024.0));
    print "
           <tr class=tr>
              <td> Total Archived Files: </td>
              <td> $qr4[0] </td>
           </tr>
           <tr class=br>
              <td> Total Archive Size (GB): </td>
              <td> $ASIZEGB </td>
           </tr>
           <tr class=tr>
              <td> Space per Obsid (MB): </td>
              <td> $AVSZOBS </td>
           </tr>
           <tr class=br>
              <td> Ave File size (MB): </td>
              <td> $AVSZFILE </td>
           </tr>
           <tr class=tr>
              <td> Number Distinct File Type: </td>
              <td> $qr4[2] </td>
           </tr>
          ";
      
    print "</table>\n";       

    print "</table>\n";
    
    print "</td></tr></table>\n";
  }

?>

</div> <!-- page -->


<?php
initMainMenu();
generateFileMenu();
generateTrendViewMenu( $type );
generateHelpMenu();
generateQuickSearchBar();
finalizeMainMenu();

statusMessage( "you are currently viewing $string; please see the 'view' menu for more trends" );
?>

</body>
</html>
