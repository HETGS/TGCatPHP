<?php
ob_start( "ob_gzhandler" );
require "tgMenu.php";
require "tgNotifications.php";
$guide = $_REQUEST['guide'];
if ( ! $guide ){ $guide = "help/tgcat_help_intro.html"; }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title> TGCat - Help </title> 
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<?php include "styleInc.php" ?>
</head>

<body>

<div id='page'>  
    <?php
if ( file_exists( $guide ) && ! preg_match( "/\.\./", $guide ) && preg_match( "/^help/", $guide ) ){
  include "$guide"; 
}
else {
  $guide = "Not Found";
  print "<center><div id='about'><h3>guide not found</h3></div></center>";
}
?>
</div>


<?php
initMainMenu();
generateFileMenu();
generateHelpTopicsMenu();
generateHelpMenu();
finalizeMainMenu();
statusMessage( "You are currently browsing guide: $guide" ); 
?>

</body>
</html>
