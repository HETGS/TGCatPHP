<?php

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


  //
  // open a connection to the database
  //
$host = "localhost";
$db   = "tgcat_db";
$user = "tgcat_web";
$pass = "We8C@_S..l";
$link = mysql_connect($host, $user, $pass) or die("Could not connect to MySQL: ".mysql_error());
mysql_select_db($db, $link) or die("Could not select database: ".mysql_error());

?>
