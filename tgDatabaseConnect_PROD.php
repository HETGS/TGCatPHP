<?php
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
