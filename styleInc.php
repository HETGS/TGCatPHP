<?php
##############################################################################
##
## TGCAT WEBPAGE DOCUMENT
##
## TITLE: style_inc.php
##
## DESCRIPTION:
##
##      this script reads COOKIE or POST data to determine
##      if there is a user style set, defaults if there is 
##      none to the default style ( default.css ). The output
##      is an ( one or more ) html style include directive(s)
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


$mystyle = "default.css";

if ( $_GET['style'] )
  {
    $my_maybe_style =  $_GET['style'] . ".css";
    if ( file_exists("style/$my_maybe_style") )
      {
	$mystyle = $my_maybe_style;
	//print "<!-- using get specified style: $mystyle -->\n";
	setcookie("cstyle","$mystyle",time() + 365*86400);
	//print "<!-- set cookie for style persistancy -->\n";
      }
    else
      {
	print "<!-- could not include style: $my_maybe_style -->\n";
      }
  }
elseif ( $_COOKIE['cstyle'] )
  {
    $mystyle = $_COOKIE['cstyle'];
    print "<!-- using user defined style: $mystyle -->\n";
  }
else 
  {
    print "<!-- defaulting to style: $mystyle -->\n";
  }

print "<link rel='stylesheet' type='text/css' href='style/menu.css'>
<link rel='stylesheet' type='text/css' href='style/page.css'>
<link rel='stylesheet' type='text/css' href='style/geom.css'>
<link rel='stylesheet' type='text/css' href='style/$mystyle'>";
//
// include the IE fix for menus
//
print "
<!--[if IE]>
<style type='text/css' media='screen'>  
  #menu ul li a { height:1%; width:100%; width:230px; margin:0px; word-wrap:break-word;}
  #menu ul li a#quickSearchMenu { width:300px; }
  #menu ul li a#main { width:120px; }
  #menu ul li { background:url('../image/grey70.png' ); }
  #menu span.leaf { display:normal;background:none; }
</style>
<![endif]-->
";

?>
