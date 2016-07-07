<?php
  error_reporting( E_ALL );
  //ini_set( "display_errors",1);
require "tgMenu.php";
require "tgMainLib.php";
require "tgNotifications.php";
require "tgDatabaseConnect.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html>

<head>
<title> TGCat : Chandra Gratings Catalog and Archive</title> 
<meta 
  http-equiv="Content-Type" 
  content="text/html; charset=utf-8">
<meta 
  name="author" 
  content="Arik W. Mitschang ( SAO )" >
<meta 
  name="keywords" 
  content="Chandra,Chandra Xray Observatory,Gratings,Gratings Catalog,Gratings Archive,Archive,ACIS,HRC,HETG,LETG,Transmission Gratings,tgcat,TGCat">
<meta 
  name="description" 
  content="TGCat The Chandra Transmission Gratings Catalog and Archive. Search, Browse, Plot, and Download Calibrated and Reviewed Gratings Spectra from all Chandra ACIS/HRC Gratings Observations">
<meta 
  name="verify-v1" 
  content="MqezOOFesfhS9fAzXB/L/jDaetqzc5tlm1cU9HiMs5I=">
<?php include "styleInc.php"; ?>
</head>

<body>

<div id='page' style='min-width:900px;'>

<center>
<br>
If you use <i><b>TG</b>Cat</i> in your research, please cite:
  <a href="http://adsabs.harvard.edu/abs/2011AJ....141..129H"
  target='_blank' title="2011 AJ, 141, 129">Huenemoerder et al. 2011 (AJ, 141, 129).</a>
</center>


<center>
<div id='openingBanner' style='position:relative;top:30px;'>
<map name="bmap" id="bmap">
    <area shape="rect" coords="10,10,250,70" alt="CXC website"
    href="http://cxc.harvard.edu/" target=_blank>
    <area shape="rect"  coords="10,200,85,240" alt="SAO"
    href="http://www.cfa.harvard.edu/sao/" target=_blank>
    <area shape="rect" coords="350,10,490,70"alt="MIT kavli
    institute" href="http://space.mit.edu/" target=_blank>
</map>
<img name="img" src="image/TGCat_banner.png" alt="TGCat Banner"
usemap="#bmap" border=none style='margin:0px;z-index:99;'>
</div>


<!-- <a id='mainQueryLink' href='tgSearch.php'>Query the archive</a>
<form action='tgData.php?t=NQ' method='POST' style='border:0px;margin:0px;padding:10px;display:inline;'>Quick Search:<input type='text' name='quicksearch' size=25><input type='hidden' name='queryType' value='QUICK'><input type='Submit' value='Go'></form>
-->


<noscript>
<p>!!TGCat has detected that javascript is either not present or
turned off in your browser. Although javascript is not required, it
is recommended for the absolute best experience!!</p></noscript>



<div id='tgcat_news' style='position:relative;top:80px;left:20px;width:90%;padding-bottom:120px;'>
<h3> TGCat Announcements [<a href='tgNews.php'>all</a>]</h3>
<?php
#$q = mysql_query( 'select * from news order by post_date desc limit 4' );
#
# use this when priority is in spacebase database
#
$q = mysql_query( 'select * from news where ( post_date + INTERVAL priority DAY - now() ) > 0 or ( extract( HOUR FROM timediff(now(),post_date))) < 168 order by priority desc,post_date desc limit 4;' );
while ( $row = mysql_fetch_assoc( $q ) ){
  print "<i>$row[post_date]</i> - posted by $row[poster]\n";
  print "<p style='background-color:#DDDDDD;color:#444444;border:1px solid #CCCCCC;'>$row[content]</p>";
}
?>

<h3> Serendipitous Source Extraction <a href='tgRequest.php'>Request Form</a></h3>


<center>
If you use <i><b>TG</b>Cat</i> in your research, please cite:
  <a href="http://adsabs.harvard.edu/abs/2011AJ....141..129H"
  target='_blank' title="2011 AJ, 141, 129">Huenemoerder et al. 2011 (AJ, 141, 129).</a>
</center>



</div>

</center>
</div>

<?php
initMainMenu();
generateFileMenu();
generateHelpMenu();
generateQuickSearchBar();
generateHelpTopicsMenu();
finalizeMainMenu();

statusMessage( "last updated 08/29/2013" );
?>

<div id='openingBottom' style='position:fixed;bottom:12px;width:100%;'>
<div id='openingBottomLeft'>
<center>
<a href="http://space.mit.edu/CXC/ISIS" 
   target='_blank'
   title='http://space.mit.edu/CXC/ISIS'>
  <font style='font-size:28px;font-weight:bold;'>ISIS</font> 
</a>
<a href="http://cxc.harvard.edu/ciao" 
   target='_blank'
   title='http://cxc.harvard.edu/ciao'>
  <img src="image/ciao_logo_navbar.gif" height=28 width=60 border=0> 
</a>
<a href="http://www.mysql.com" 
   target='_blank'
   title='http://www.mysql.com'>
  <img src="image/powered-by-mysql-125x64.png" height=28 width=50 border=0> 
</a>
<a href="http://www.php.net" 
   target='_blank'
   title='http://www.php.net'>
  <img src="image/php-med-trans.png" height=28 width=50 border=0>
</a>
<!--
For information on, and downloads of, the
TGCat software see:
<a href='http://space.mit.edu/cxc/analysis/tgcat/index.html'>
http://space.mit.edu/cxc/analysis/tgcat/index.html
</a>
-->
</div>

<div id='openingBottomMiddle'>
<center>
<i>Related Catalog Projects:</i>
<br>
<a href='http://cxc.harvard.edu/csc/' 
  target='_blank' title="The Chandra Source Catalog">CSC</a>
<a href='http://cxc.harvard.edu/XATLAS/' 
  target='_blank' title="Chandra Spectral Atlas ( HETG )">X-Atlas</a>
<a href='http://xmm.esac.esa.int/BiRD/' 
  target='_blank' title="Browsing Interface for RGS Data ( XMM )">BiRD</a>
<a href='http://hotgas.pha.jhu.edu/' 
  target='_blank' title="Chandra Grating Spectroscopy Database for Active Galactic Nuclei">HotGAS</a>
<!-- <a href='http://cxc.harvard.edu/ANCHORS/' 
  target='_blank' title="AN archive of Chandra Observations of Regions of Star formation">ANCHORS</a> -->
<a href='http://archive.stsci.edu/hlsp/' 
  target='_blank' title="The Multimission Archive at STScI (spectral atlas)">MAST</a>
</center>
</div>


<div id='openingBottomLeft'>
<center>
<a href='http://www.us-vo.org/'
   target='_blank'
   title='http://www.us-vo.org/'>
  <img src='image/nvo_logo.jpg' heigth=28 width=50 border=0>
</a>
<a href='http://nvo.stsci.edu/vor10/getRecord.aspx?id=ivo://cxc.mit/tgcat/SCS' 
   target='_blank' 
   title='Simple Cone Search Service. view full VO resource record'>
  <font style='font-size:28px;font-weight:bold;'>SCS</font> 
</a>
<a href='http://nvo.stsci.edu/vor10/getRecord.aspx?id=ivo://cxc.mit/tgcat/SIA'
   target='_blank'
   title='Simple Image Access Service. view full VO resource record'>
  <font style='font-size:28px;font-weight:bold;'>SIA</font> 
</a>
</center>
<!--
For questions and comments please contact the
administrator at <a href="mailto:tgcat@space.mit.edu">tgcat@space.mit.edu</a>
-->
</div>

<div style='clear:both'></div>
</div>


<img src='image/menuTip.png' style='position:fixed;top:36px;left:15px;width:200px;'>

</body>
</html>
