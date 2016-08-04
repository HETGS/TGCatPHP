<?php
##############################################################################
##
## TGCAT WEBPAGE DOCUMENT
##
## TITLE: PlotCBar.php 
##
## DESCRIPTION:
##
##      this script creates the GUI portion of the plotting
##      interface for TGCat and should be included in the plotting
##      page using a php include. The entire form is contained
##      herein and the sumbit button sends POST data to the 
##      "do_plot.php" script to be plotted.
##
## REVISION:
##
##     -v 1.0 Arik Mitschang (Author)
##            Emma Reishus
##
##
##############################################################################

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


#########################################
# 
# This is the plot customization bar for the
# TGCat plotting tool
#
# Should contain:
#    x limits
#    y limits
#    binning
#    
#

print "\n<script type='text/javascript' src='tg_plot.js'></script>\n";

#-- make the div element --#
//print "<div class=pcbar>\n";

if($_REQUEST['d'] ) { 
  $d = '&d='.$_REQUEST['d'];
 }
if($_REQUEST['r'] ) { 
  $r = '&r='.$_REQUEST['r'];
 }

#-- initialize the form --#
print "<form id='plt_opt' action='tgRunPlot.php?fr=$fr$d$r' target='custom_plot' method='POST'>\n";

#-- make table of x/y-limits --#
$i_size=4;

?>

<style type=text/css >
  table.plot_table td {padding-right: 5px; padding-bottom:2px;}
</style>

<center>
<br>
<p>


<?php 

#--first set the defaults --#

$plot_counts_x_s = 'selected';
$xunit_xev       = 'selected';
$yunit_photons   = 'selected';
$xlog            = 'checked';
$ylog            = 'checked';
$errbar          = 'checked';
$heg_plus_one    = '';
$heg_minus_one   = '';
$meg_plus_one    = 'checked';
$meg_minus_one   = 'checked';
$leg_plus_one    = 'checked';
$leg_minus_one   = 'checked';
$combine         = 'checked';
$bin             = 'checked';
//$bin_one         = 2;
$bin_one         = 1.e-8; // very small to avoid bug in fancy_plots.
$bin_two         = 4;
$hlike           = '';
$helike          = '';
$fe              = '';
$shift           = 0.0;

// $c = $_COOKIE['tgPlotParams'];

//   print "<script type='text/javascript'> alert(' $c ') </script>";

#-- if usesaved is selected need to collect data from the cookies --#

if ( $_COOKIE['tgPlotParams'] && ($usesaved || $_GET['usesaved'] )) {

  $saved_params = explode(',',$_COOKIE['tgPlotParams']);
  $param['plot'] = $saved_params[0];
  $param['xunit'] = $saved_params[1];
  $param['yunit'] = $saved_params[2];
  if($saved_params[3]) { $param['xlog'] = 'checked'; }
  else { $param['xlog'] = ''; }   
  if($saved_params[4]) { $param['ylog'] = 'checked'; }
  else { $param['ylog'] = ''; } 
  if($saved_params[5]) { $param['errbar'] = 'checked'; }
  else { $param['errbar'] = ''; }  

  # only set the ones we need based on detector and gratings
  if ( $DETECTOR == "ACIS" && $GRATING == "HETG" ) {
    if($saved_params[6] || $saved_params[7] || $saved_params[8] || $saved_params[9]) {
      if($saved_params[6]) { $param['MEG+1'] = 'checked'; }
      else { $param['MEG+1'] = ''; }    
      if($saved_params[7]) { $param['MEG-1'] = 'checked'; }
      else { $param['MEG-1'] = ''; }    
      if($saved_params[8]) { $param['HEG+1'] = 'checked'; }
      else { $param['HEG+1'] = ''; }    
      if($saved_params[9]) { $param['HEG-1'] = 'checked'; }
      else { $param['HEG-1'] = ''; }
    }
    else {
      if( $saved_params[10] || $saved_params[11] ) {
	$param['MEG+1'] = 'checked';
	$param['MEG-1'] = 'checked'; 
	$param['HEG+1'] = ''; 
	$param['HEG-1'] = '';
      }
    }
  }
  else {
    if($saved_params[6] || $saved_params[7] || $saved_params[8] || $saved_params[9]) {
      $param['LEG+1'] = 'checked';
      $param['LEG-1'] = 'checked'; 
    }
    else {
      if( $saved_params[10] || $saved_params[11] ) {
	if($saved_params[10]) { $param['LEG+1'] = 'checked'; }
	else { $param['LEG+1'] = ''; }
	if($saved_params[11]) { $param['LEG-1'] = 'checked'; }
	else { $param['LEG-1'] = ''; }
      }
    }
  }  

  if($saved_params[12]) { $param['combine'] = 'checked'; }
  else { $param['combine'] = ''; }
  $param['xmin'] = $saved_params[13];
  $param['xmax'] = $saved_params[14];
  $param['ymin'] = $saved_params[15];
  $param['ymax'] = $saved_params[16];
  if($saved_params[17]) { $param['bin'] = 'checked'; }
  else { $param['bin'] = ''; } 
  $param['bin1'] = $saved_params[18];
  $param['bin2'] = $saved_params[19];

  if($saved_params[20]) { $param['H-like'] = 'checked'; }
  else { $param['H-like'] = ''; }
  if($saved_params[21]) { $param['He-like'] = 'checked'; }
  else { $param['He-like'] = ''; }
  if($saved_params[22]) { $param['Fe'] = 'checked'; }
  else { $param['Fe'] = ''; }
  if($saved_params[23]) { $param['shift'] = $saved_params[23]; }
  else { $param['shift'] = 0.0; }

  #-- set the plot type selection based on the cookie value --#  

  switch( $param['plot'] ){ 
  case "Counts/X/s":
    $plot_counts_x_s = 'selected';
    break;
  case "Counts/Bin":
    $plot_counts_bin = 'selected';
    break;
  case "Fgam":
    if ($DETECTOR != 'HRC') {
        $plot_fgam = 'selected';
    }
    else {
      $plot_counts_x_s = 'selected';
    }
    break;
  case "FgamX":
    if ($DETECTOR != 'HRC') {
        $plot_fgamx = 'selected';
    }
    else {
      $plot_counts_x_s = 'selected';
    }
    break;
  case "Fx": 
    if ($DETECTOR != 'HRC') {
       $plot_fx = 'selected';
    }
    else {
      $plot_counts_x_s = 'selected';
    }
    break;
  case "XFx":
    if ($DETECTOR != 'HRC') {
       $plot_xfx = 'selected';
    }
    else {
      $plot_counts_x_s = 'selected';
    }
    break;
  default:
    $plot_counts_x_s = 'selected';
    break;
  }  

  #-- set the xunit selection based on the cookie value --#

  switch( $param['xunit'] ){
  case "kEv":
     $xunit_kev = 'selected';
     break;
  case "A":
     $xunit_a   = 'selected';
     break;
  case "nm":
    $xunit_nm  = 'selected';
    break;
  case "micron":
    $xunit_micron = 'selected';
    break;
  case "mm":
    $xunit_mm = 'selected';
    break;
  case "cm":
    $xunit_cm = 'selected';
    break;
  case "m":
    $xunit_m  = 'selected';
    break;
  case "eV":
    $xunit_ev = 'selected';
    break;
  case "MeV":
    $xunit_mev = 'selected';
    break;
  case "GeV":
    $xunit_gev = 'selected';
    break;    
  case "TeV":
    $xunit_tev = 'selected';
    break; 
  case "Hz":
    $xunit_hz  = 'selected';
    break;
  case "kHz":
    $xunit_khz = 'selected';
    break;
  case "MHz":
    $xunit_mhz = 'selected';
    break;
  case "GHz":
    $xunit_ghz = 'selected';
    break;
  default: 
    $xunit_kev = 'selected';  
    break;
  }

#-- now the yunit - for HRC this can only be photons - for ACIS we'll get the value from the cookie --#           

  if ( $DETECTOR != 'HRC' && ! $plot_counts_x_s != 'selected' && $plot_counts_bin != 'selected') {

    switch($param['yunit']){
    case "Photons":
      $yunit_photons='selected';
      break;
    case "ergs":
      $yunit_ergs = 'selected';
      break;
    case "Watts":
      $yunit_watts = 'selected';
      break;
    case "mJy":
      $yunit_mjy   = 'selected';
      break;
    default:
      $yunit_photons='selected';
      break;
    }
  }
  else {
    $yunit_photons='selected';
  }

  #-- now the rest of the values can be retrieved from the cookie as is --#

  $xlog = $param['xlog'];
  $ylog = $param['ylog'];
  $errbar = $param['errbar'];
  $meg_plus_one = $param['MEG+1'];
  $meg_minus_one = $param['MEG-1'];
  $heg_plus_one = $param['HEG+1'];
  $heg_minus_one = $param['HEG-1'];
  $leg_plus_one = $param['LEG+1'];
  $leg_minus_one = $param['LEG-1'];
  $combine = $param['combine'];
  $xmin = $param['xmin'];
  $xmax = $param['xmax'];
  $ymin = $param['ymin'];
  $ymax = $param['ymax'];
  $bin =  $param['bin'];
  $bin_one = $param['bin1'];
  $bin_two = $param['bin2'];
  $hlike = $param['H-like'];
  $helike = $param['He-like'];
  $fe = $param['Fe'];
  $shift = $param['shift'];
  
}

#--  if no cookie use default values --#

else {
                                                                                                                                                                                    
  $plot_counts_x_s = 'selected';
  $xunit_xev       = 'selected';
  $yunit_photons   = 'selected';
  $xlog            = 'checked';
  $ylog            = 'checked';
  $errbar          = 'checked';  
  $heg_plus_one    = '';
  $heg_minus_one   = '';
  $meg_plus_one    = 'checked';
  $meg_minus_one   = 'checked';
  $leg_plus_one    = 'checked';
  $leg_minus_one   = 'checked';
  $combine         = 'checked';
  $bin             = 'checked';
  //  $bin_one         = 2;
  $bin_one         = 1.e-8;
  $bin_two         = 4;
  $hlike           = '';
  $helike          = '';
  $fe              = '';
  $shift           = '0.0';
 
 }

#-- set up the form based on detector and grating --#

print "Plot Type: 
       <select id=plotTypeSelect name=plot onchange='grayOutY();' >";
print  "<option value='Counts/X/s' title='counts/sec/X unit' $plot_counts_x_s onchange='grayOutY();' > Counts/&chi;/s </option>
       <option value='Counts/Bin' title='counts/bin' $plot_counts_bin onchange='grayOutY();'> Counts/Bin </option>";


if ( $DETECTOR != "HRC" ) 
  {
    print"
  <option value=Fgam  title='photons/area/sec'        $plot_fgam onchange='grayOutY();'>F&gamma;       </option>
  <option value=FgamX title='photons/area/sec/X unit' $plot_fgamx onchange='grayOutY();'>F&gamma;/&chi; </option>
  <option value=Fx    title='Energy/area/sec/X unit'  $plot_fx onchange='grayOutY();'>F&chi;         </option>
  <option value=XFx   title='Energy/area/sec'         $plot_xfx onchange='grayOutY();'>&Chi;F&chi;    </option>
";
  }
print "
</select>

X-Units:
<select name=xunit>
  <option value='keV' title='keV' $xunit_kev >          keV </option>
  <option value='A' title='A' $xunit_a >                A </option>
  <option value='nm' title='nm' $xunit_nm >             nm </option>
  <option value='micron' title='micron' $xunit_micron > micron </option>
  <option value='mm' title='mm' $xunit_mm >              mm </option>
  <option value='cm' title='cm' $xunit_cm >             cm </option>
  <option value='m' title='m' $xunit_m >                m </option>
  <hr>
  <option value='eV' title='eV' $xunit_ev >             eV </option>
  <option value='MeV' title='MeV' $xunit_mev >          MeV </option>
  <option value='GeV' title='GeV' $xunit_gev >          GeV </option>
  <option value='TeV' title='TeV' $xunit_tev >          TeV </option>
  <option value='Hz' title='Hz' $xunit_hz >             Hz </option>
  <option value='kHz' title='kHz' $xunit_khz >          kHz </option>
  <option value='MHz' title='MHz' $xunit_mhz >          MHz </option>
  <option value='GHz' title='GHz' $xunit_ghz >          GHz </option>
</select>
";
print "Y-Scale:
<select name=yunit id=yscale >
  <option value='Photons' title='Photons' $yunit_photons > Photons </option>";

if ( $DETECTOR != "HRC" )
  {
    print"
  <option value='ergs' title='ergs' $yunit_ergs >          ergs </option>
  <option value='Watts' title='Watts' $yunit_watts >       Watts </option>
  <option value='mJy' title='mJy' $yunit_mjy >             mJy </option>";
  }
print "
</select>
</p>

<p>
<input name=xlog type=checkbox $xlog > XLog
<input name=ylog type=checkbox $ylog > YLog
<input name=errbar type=checkbox $errbar >ErrorBars
</p>

<p>";

if ( $GRATING == "HETG" ){
print "
MEG Orders:
<input type=checkbox name=MEG+1 $meg_plus_one > +1
<input type=checkbox name=MEG-1 $meg_minus_one > -1 
HEG Orders:
<input type=checkbox name=HEG+1 $heg_plus_one >+1 
<input type=checkbox name=HEG-1 $heg_minus_one >-1
";
 }
elseif ( $GRATING == "LETG" ) {
print "
LEG Orders:
<input type=checkbox name=LEG+1 $leg_plus_one > +1
<input type=checkbox name=LEG-1 $leg_minus_one > -1
";
}
print "
<input type='checkbox' name='combine' title='combine selected datasets' $combine >Combine
</p>

<p>";

print " <table class=plt_arrows style='display:inline'><tr>
<td>Xmin:</td>
<td><input type=text id=xmin name=xmin size=4 value=$xmin></td> 
<td id=xmina></td> 
</tr>
</table>
<table class=plt_arrows style='display:inline'><tr>
<td>Xmax:</td>
<td> 
<input type=text id=xmax name=xmax size=4 value=$xmax></td> 
<td id=xmaxa></td> 
</tr>
</table>
<table class=plt_arrows style='display:inline'><tr>
<td>Ymin:</td>
<td> 
<input type=text id=ymin name=ymin size=4 value=$ymin></td> 
<td id=ymina></td> 
</tr>
</table>
<table class=plt_arrows style='display:inline'><tr>
<td>Ymax:</td>
<td> 
<input type=text id=ymax name=ymax size=4 value=$ymax></td> 
<td id=ymaxa></td> 
</tr>
</table>
</p>

<p>
Bin <input type=checkbox name=bin $bin>
min-S/N: <input type=text name=bin1 size=4 value='$bin_one'>
min-Ch: <input type=text name=bin2 size=4 value='$bin_two'>
</p>";

print "Mark feature locations: <br><p>
Lines:  <input type=checkbox name=H-like $hlike> H-like
        <input type=checkbox name=He-like $helike> He-like
        <input type=checkbox name=Fe $fe> Fe
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Redshift: <input type=text name=shift size=4 value='$shift'> (v/c)
</p>";

print "<input type=hidden name=id value=$id>\n";
print "<input type=hidden name=grating value=$GRATING>\n";
print "<input type=hidden name=detector value=$DETECTOR>\n";
print "<input type=hidden name=doplot value=1>\n";

#-- option to save the current form parameters upon submission --#
print "Save Current Parameters<input type=checkbox name='save' title='will save on replot'><br><br>";

print "<input type='button' value='Reset to Default Parameters' name='default_button' id='chngtoDefault' onclick='resetDefaults()' > ";
if ( $_COOKIE['tgPlotParams'] ) {
  print "<input type='button' value='Use Saved Parameters' name='saved_button' id='chngtoSaved' onclick='setSaved()' > ";
 }
 else {
   print "<input type='button' value='Use Saved Parameters' name='saved_button' id='chngtoSaved' onclick='setSaved()' disabled >";
 }
?>

<br><br> 
<input type=Submit value='Replot' id='applybutton' width=50 onclick='addSave()'>
</form>

<script type='text/javascript' src='formchanges.js'></script>

</center>
<br>

<script type="text/javascript">
insert_arrows();
grayOutY();
function grayOutY( )
{
  var Psel = document.getElementById("plotTypeSelect").options;
  var openf=true;
  if ( document.getElementById("plotTypeSelect").options[0].selected || document.getElementById("plotTypeSelect").options[1].selected )
    {
      openf=false;
      document.getElementById("yscale").disabled=true;
      document.getElementById("yscale").options[0].selected=true;      
    }
  else
    {
      document.getElementById("yscale").disabled=false;
    }
  /*alert( Psel + "\nSel 1 (cnt/X/s): " + Psel[0].selected + "\nSel 2 (cnt/bin): " + Psel[1].selected + "\nFinal verdict (true=enable): " + openf);*/
}
  

</script>
