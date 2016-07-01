<?php
  //error_reporting( E_ALL );
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
<?php include "styleInc.php"; ?>
</head>

<body>

<div id='page'>

<center>
<div id='about'>

<h3> About the New Interface</h3> <p> We have changed the web
interface to TGCat, the Chandra Grating Data Catalog and Archive
(http://tgcat.mit.edu/).  Don't worry!  The content is the same.
For a while, the old interface will be available via: <a
href='http://tgcat.mit.edu/prev/'>http://tgcat.mit.edu/prev/</a>.

<br><br> 

Why did we change the interface?  The original design did not scale
well under the addition of new features; it also transferred two
orders of magnitude more html-data than necessary.  While adding VO
support, we were motivated to make more general changes to streamline
the implementation.

<br><br>

The new interface

<div style='width:70%; text-align:left;'>
<ul>

  <li>is menu based.  This should be more familiar since menus are
    common in many modern applications.  This also frees screen
    real-estate we had used for state and action buttons.  Menus are
    scalable: new items can be added without using space on the
    screen.  Menus can be more efficient - we now have fewer clicks to
    perform some actions.

  <li>supports multiple unified "plug in" back-end interfaces, such as
    Virtual Observatory (VO) tables and ASCII tables (both supported),
    or a command line "wget" (to be provided).

  <li>has much smaller web data volume transfer - about 100 kb instead
    of 10 MB for typical queries.  Searches and sorting are much more
    efficient.

  <li>remembers user queries. Queries can also be saved and assigned an
    arbitrary label.

  <li>has an enhanced download package management interface, and direct single-file
    product downloads.

</div>
One disadvantage in a menu-based system is that relevant choices for a
context are not immediately visible.  Hopefully, our menu design is
sensible and intuitive.  We welcome your feedback.  If there are
features you cannot find or which you find confusing, please send
email to tgcat@space.mit.edu (but also look under the "Help" link).
</p>
</div>
</center>
</div>

<?php
initMainMenu();
generateFileMenu();
generateHelpMenu();
generateQuickSearchBar();
finalizeMainMenu();
?>

</body>
</html>
