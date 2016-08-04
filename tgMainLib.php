<?php
##############################################################################
##
## TGCAT WEBPAGE DOCUMENT
##
## TITLE: tgcat_lib.php 
##
## DESCRIPTION:
##
##     the tgcat library contains data definitions for use by other
##     scipts in this site and provides the Class for database connection
##     to mysql. several other handy functions are also contained within
##
## REVISION:
##
##    -v 1.0 Arik Mitschang
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


$ZO_MAP = Array( "d" => "Dead Reckoning",
                 "f" => "findzero",
                 "e" => "Manual",
                 "t" => "tgdetect",
		 "m" => "Manual",
                 );

$REVIEW_CODES = Array( "w" => "warning",
		       "c" => "good",
		       "e" => "error",
		       );

$MAXDOWNLOADSIZE = 250*1000; # 250 mega bytes

function get_ds_selections()
{

  return $_POST['dsc'];
}

function is_multi_id( $id ){
  if ( preg_match( '/,/', $id ) ){ return 1; }
  return 0;
}

function split_multi_id( $id ){
  return split( ',',$id );
}
     

function generateTagWindow( $qid, $tag, $show ){
  print "\n";
  print "<div id='tagbg' class='dialogBg' style='visibility:$show;z-index:100;'></div>\n";  
  print "<div id='tagb'  class='dialog'   style='visibility:$show;z-index:200;'>";
  print "<center>
<div class='selectdiv'>
<p> 
Apply a description to this query to make finding in your list easier
</p>
<form name='tagQuery' action='tgData.php?q=$qid&amp;t=TQ' method='post'>
<textarea name='tag' id='tag' cols='90' rows='4'>$tag</textarea>
<br><br>
<input type='submit' value='tag'>
<a href='tgData.php?q=$qid' id='tagger_close'><button>Close</button></a>
</form>
</div>
</center>
</div>
";

}

function generateFilterWindow( $qid, $cols, $currentFilter, $show ){
  if ( ! is_array( $cols ) ){ $cols = split( ",", $cols ); }
  print "\n";
  print "<div id='filterbg' class='dialogBg' style='visibility:$show;z-index:100;'></div>\n";  
  print "<div id='filterb'  class='dialog'   style='visibility:$show;z-index:200;'>";
  print "<center>
<div class='selectdiv'>
<p> ";
  if ( $currentFilter ){
    print "Add a filter to query. Current filter is <br><b>$currentFilter</b>";
  }
  else {
    print "Apply a filter to query";
  }
print "
</p>
<form name='filterQuery' action='tgData.php?q=$qid&amp;t=FQ' method='post'>
<select name='filtercol'>";
  foreach ( $cols as $col ){
    print "<option>$col</option>";
  }
print "
<select name='filterop'>
<option>=</option><option>!=</option><option><</option><option>></option><option>Like</option>
</select>
<input type='text' size='25'  name='filtercond'>
<input type='submit' value='apply'>
<a href='tgData.php?q=$qid' id='filter_close'><button>Cancel</button></a>
</form>
</div>
</center>
</div>
";

}
  
function generateColumnSelectorExtractions( $qid, $cols, $show )
{  
  global $disabled_cols;
  $select_def_cols = "obsid,object,instrument,grating,ra,decl,exposure";
  //print_r( $cols );

  $CHECKEDC;
  if ( ! is_array( $cols ) ){ $cols = split( ",", $cols ); }
  //print_r( $cols );
  foreach ( $cols as $col ) { $CHECKEDC[$col] = "checked"; }
  foreach ( split(",",$disabled_cols) as $col ) { $CHECKEDC[$col] = "disabled"; }

  print "<div id='selbg' class='dialogBg' style='visibility:$show;z-index:100;'></div>\n";  
  print "<div id='selb'  class='dialog'   style='visibility:$show;z-index:200;'>";

  print "<center><div id='sel_cols' class='selectdiv'>\n";
  print "<center>\n";
  $action = "tgData.php?q=$qid&amp;t=CC";
  print "<form name='chooseCols' action='$action' method='post'>\n";
  print "<div class='srchHelp'> DEFAULTS: <br>\n";
  
  foreach ( split(",",$select_def_cols) as $i )
    {
      print "<input type='checkbox' name='show_$i' $CHECKEDC[$i] > $i \n";
      $ii = $ii + 1;
    }
  
  print "</div><hr>\n";
  
  print "<table><tr style='width:100%'><td>\n";
  ///
  /// data properties section
  ///
  print "<div class=srchHelp style='text-align:left'>
 DATA PROPERTIES: <br>
<input type=checkbox name=show_obi $CHECKEDC[obi] > obi <br>
<input type=checkbox name=show_readmode $CHECKEDC[readmode] > readmode <br>
<input type=checkbox name=show_datamode $CHECKEDC[datamode] > datamode <br>
<input type=checkbox name=show_proc_date $CHECKEDC[proc_date] > proc_date <br>
<input type=checkbox name=show_zo_method $CHECKEDC[zo_method] > zo_method
</div>";

  print "</td><td>\n";
  /// 
  /// detector properties
  /// 
  print "<div class=srchHelp style='text-align:left'>
 DETECTOR PROPERTIES: <br>
<input type=checkbox name=show_windowed $CHECKEDC[windowed] > windowed <br>
<input type=checkbox name=show_subarray $CHECKEDC[subarray] > subarray <br>
<input type=checkbox name=show_pileup $CHECKEDC[pileup] > pileup <br>
<input type=checkbox name=show_detector_aimpoint $CHECKEDC[detector_aimpoint] > detector_aimpoint <br>
<br>
</div>";

  print "</td><td>\n";
  ///
  /// obs properties
  ///
  print "<div class=srchHelp style='text-align:left'>
 OBS PROPERTIES: <br>
<input type=checkbox name=show_date_obs $CHECKEDC[date_obs] > date_obs <br>
<input type=checkbox name=show_phase $CHECKEDC[phase] > phase <br>
<input type=checkbox name=show_period $CHECKEDC[period] > period <br>
<input type=checkbox name=show_extended $CHECKEDC[extended] > extended <br>
<input type=checkbox name=show_comment $CHECKEDC[comment] > comment <br>
<br>
</div>";

  print "</td><td>\n";
  ///
  /// spectral band rates
  ///
  print "<div class=srchHelp style='text-align:left'>
 BROAD BAND RATES: <br>
<input type=checkbox name=show_meg_band $CHECKEDC[meg_band] > meg_band <br>
<input type=checkbox name=show_heg_band $CHECKEDC[heg_band] > heg_band <br>
<input type=checkbox name=show_leg_band $CHECKEDC[leg_band] > leg_band <br>
<input type=checkbox name=show_letg_acis_band $CHECKEDC[letg_acis_band] > letg_acis_band <br>
<input type=checkbox name=show_zeroth_order $CHECKEDC[zeroth_order] > zeroth order <br>
</div>";
  
  print "</td></tr></table>\n";
  print "<input type='hidden' name='used_selector' value='1'>\n";

  print "<br>\n";
  print "Save<input type=checkbox name=save>".
    "<input type=Submit value=Apply>\n";

  print "<a href='tgData.php?q=$qid' id='selector_close'><button>Close</button></a>\n";

  print "<a href='tgData.php?q=$qid&amp;t=DC'><button>Defaults</button></a>\n";

  print "</form>\n</center>\n</div></center></div>\n";
  
}

function generateColumnSelectorSource( $qid, $cols, $show )
{  
  global $disabled_cols;
  $select_def_cols = "object,simbad_ID,ra,decl,pType,other_types,num_extractions";
  //print_r( $cols );

  $CHECKEDC;
  foreach ( $cols as $col ) { $CHECKEDC[$col] = "checked"; }
  foreach ( split(",",$disabled_cols) as $col ) { $CHECKEDC[$col] = "disabled"; }

  print "<div id='selbg' class='dialogBg' style='visibility:$show;z-index:100;'></div>\n";  
  print "<div id='selb'  class='dialog'   style='visibility:$show;z-index:200;'>";

  print "<center><div id='sel_cols' class='selectdiv'>\n";
  print "<center>\n";
  $action = "tgData.php?q=$qid&amp;t=CC";
  print "<form name='chooseCols' action='$action' method='post'>\n";
  print "<div class='srchHelp'> DEFAULTS: <br>\n";
  
  foreach ( split(",",$select_def_cols) as $i )
    {
      print "<input type='checkbox' name='show_$i' $CHECKEDC[$i] > $i \n";
      $ii = $ii + 1;
    }
  
  print "</div><hr>\n";
  
  print "<input type='hidden' name='used_selector' value='1'>\n";

  print "<br>\n";
  print "Save<input type=checkbox name=save>".
    "<input type=Submit value=Apply>\n";

  print "<a href='tgData.php?q=$qid' id='selector_close'><button>Close</button></a>\n";

  print "<a href='tgData.php?q=$qid&amp;t=DC'><button>Defaults</button></a>\n";

  print "</form>\n</center>\n</div></center></div>\n";
  
}


function generateDownloadWindow( $qid, $tableCode, $visibility, $ids="" )
{
  //
  // generate ucode that prevents from accidental
  // requeue with reload button
  // 
  $ucode = time() . "T" . mt_rand();

  print "<div id='dlbg' class='dialogBg' style='visibility:$visibility;z-index:100'></div>";  
  print "<div id='dlb'  class='dialog'   style='visibility:$visibility;z-index:200'>";

  print "<center>\n<div id='download' class=selectdiv>\n";
  print "<center>\n";

  if ( $tableCode == "s" ){
    print "<p style='font-size:11px;font-weight:bold;width:70%;color:#cc9900;margin-top:2px;'><i>
              !!! Please note that until implementation of pre-combined extraction products, downloading
              data from a TGCat source will download all extractions related to that source ( see 
              num_extractions column ) !!!</i></p>\n";
  }
  print "<p>Please select the products you wish to download:</p>\n";
  
  $action = "tgDL.php?t=B&amp;q=$qid&amp;c=$tableCode";

  print "<form name='download' action='$action' method=post onsubmit='return verifyDownload()'>\n";
  print "
  <div class='srchHelp'> 
  <b><i>default:</i></b> <br>
  <table><tr>
  <td><input type=checkbox name=prod_pha2 $CHECKED[pha2] checked>  PHA2 ( Level 2 counts spectrum file )</td>
  <td><input type=checkbox name=prod_pha1 $CHECKED[pha2]>  PHA1 ( Column format spectrum )</td>
  </tr><tr>  
  <td><input type=checkbox name=prod_rmf $CHECKED[rmf] checked>   RMF  ( Response matrix file )</td>
  <td><input type=checkbox name=prod_arf $CHECKED[arf] checked> ARF ( Ancillary response file )</td>
  </tr></table>
  </div>
  <br>
  <div class='srchHelp'> 
  <b><i>auxillary:</i></b> <br>
  <table><tr>
  <td><input type=checkbox name=prod_evt2 $CHECKED[evt2]> EVT2 ( Level 2 event file )</td>
  <td><input type=checkbox name=prod_lc $CHECKED[lc]> LTC ( Binned light curve file ) </td>
  </tr><tr>  
  <td><input type=checkbox name=prod_obspar $CHECKED[obspar]> OBSPAR ( Observation parameter file )</td>
  <td><input type=checkbox name=prod_sum $CHECKED[sum]> SUM ( Summary image/tables )</td>
  </tr></table>
  </div>
  <input type=hidden value='$ids' id='ids' name='ids'>
  <input type=hidden value='$ucode' name='ucode'>
  <input type=hidden value='0' name='dlusedjs'>
  ";

  if ( $_COOKIE['tgNotAddr'] ){ $EMAILADDR = $_COOKIE['tgNotAddr']; }
  else { $EMAILADDR = ''; }

  print "<br>
  <a title='for notification purposes only. Click for more details' 
     href='tgHelp.php?q=$qid&amp;guide=help/tgcat_privacy.html'>
  email address:</a> <input type='entry' name='email' value='$EMAILADDR' size=35>
  <i>OR</i>
  <a title='tag for easy identification in download stage area ( 10 character limit ). Click for details'
     href='tgHelp.php?q=$qid&amp;guide=help/tgcat_download.html#tagging'>
  tag:</a><input type='entry' name='tag' size='8'><br>
  ";

  print "<br>\n";

  print "<input type=Submit value=Apply>\n";

  print "<a id='downloader_close' href='tgData.php?q=$qid'>" .
    "<button>Close</button></a>\n";
  
  print "</form></center>\n</div></center></div>\n";
  
}
 
function superMessage( $message, $stay=8 ) {
print "<script type='text/javascript'>
superMessage( '$message', $stay );
 </script>";
}
     
function selector_href( $tabl, $cols, $ids_array, $orderby )
{ 

  if ( ! $orderby )
    {
      $orderby = $_GET['orderon'];
    }

  $ids_str = implode(",",$ids_array );
  $orderby = prepare_for_http($orderby);

  return "<a id='selector_href' title='add/remove visible columns in table' href=tgcat_data.php?type=REORDER&amp;orderon=$orderby&amp;tab=$tabl&amp;cols=$cols&amp;ids=$ids_str&amp;show_sel=yes>";

}


// function to prepare input for http get method passing
// converts "bad" characters to % escaped forms
function prepare_for_http ( $whrc )
{
      $new_whrc = preg_replace( "/\ /", "%20", $whrc );
      $new_whrc = preg_replace( "/>/",  "%3E", $new_whrc );
      $new_whrc = preg_replace( "/</",  "%3C", $new_whrc );
      $new_whrc = preg_replace( "/\"/", "%22", $new_whrc );

      $url_en = urlencode($whrc );
      #print "<!--\n";
      #print "orig: $new_whrc\n";
      #print "new:  $url_en\n";
      #print "-->";
      
      return $url_en;
      
}

function create_button( $anchor, $name, $text, $style )
{

  $my_style = "";
  $endanchor = "";
  if ( ! $text ) $text = $name;
  if ( $style ) $my_style = "style='$style'";
  if ( $anchor ) { $endanchor = "</a>"; }

  echo "
$anchor <div id=$name class=button $my_style>  $text </div> $endanchor
  ";
}

// function to convert degrees to sexigesimal
// provided by Doug Morgan

function sexagesimal ($degree, $isra)
 {
   // first get the sign
   if ( $degree < 0 ) {
     $sign = '-';
     if ( $isra )
       {
	 $degree = 360 + $degree;
       }
     else
       {
	 $degree = 0 - $degree;
       }
   }
   elseif ( $degree > 0 ) $sign = '+';

   // use int to pull out the decimal values

   if ( $isra )
     {
       $hour = $degree*(24.0/360.0);
     }
   else
     {
       $hour = $degree;
     }
   
   $min = ( $hour - (int) $hour )*60;
   $hour = (int) $hour;
   $sec = ( $min - (int) $min)*60;
   $min = (int) $min;

   // create initial string

   $sec = sprintf("%06.3f",$sec);
   $min = sprintf("%02s",$min);
   $hour = sprintf("%02s",$hour);
   $sexages = "$hour:$min:$sec";
  
   // sign only used for declination ( and need '0' at start for ra values less than 10

   if ( ! $isra )   $sexages  = $sign . $sexages;

   return $sexages;

}

function degrees ( $sex, $delim, $isra )
{
  //split on the delimiter
  $sex_ar = split( "$delim", $sex );
  
  // get hours minutes and seconds
  $sexh = $sex_ar[0];
  if ( $sexh < 0 ) $sign = "-";
  else $sign = "+";
  $sexh = preg_replace("/\+*\-*/","",$sexh);

  //  print "<!-- sexh equals: $sexh -->\n";
  //$test = "5:30" + "1:14";
  //print "<!-- test = $test -->\n";
  
  $sexm = $sex_ar[1];
  $sexs = $sex_ar[2];

  // make the conversion
  $deg = 0;
  $deg = $deg + $sexm/60.0;
  $deg = $deg + $sexs/3600.0;
  if ( $isra )
    {
      $deg = $deg + $sexh;
      $deg = $deg*(360.0/24.0);
    }
  else
    {
      $deg = $deg + $sexh;
      if ( $sign == "-" )
	{
	  $deg = 0 - $deg;
	}
    }
  
  //print "<!-- DEBUG DEG: $deg -->\n";
  
  return $deg;
}

?>
