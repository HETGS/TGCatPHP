<?php

####################################################
####################################################
##
##  FILENAME: tgSecondarySearch.php
##
##  DESCRIPTION: 
##    Contains functions for searching the database 
##    on a NAME query, if no exact matches are found.
##    Looking for near string matches.
##
##  AUTHOR: Emma Reishus
##
####################################################
####################################################

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

  //error_reporting( E_ALL );
  //ini_set( "display_errors",1);

//
// returns false if no results found
// otherwise returns list of valid names which are considered close enough 
// by the function (of the form that would be submitted by the user)
//
function secondarySearch( $key ) {
  $names = "";
  $q = "SELECT o.object FROM obsview AS o";
  $objs = mysql_query( $q );
  $key = strtolower($key);

  //
  // loop through names looking for close matches
  //
  while ($row = mysql_fetch_array( $objs )) {
    $str = strtolower($row['object']);

    //
    // levenshtein function from php is highly customizable
    // see http://php.net/manual/en/function.levenshtein.php
    // if weights of insertions, replacements, and deletions
    // are to be changed (if none specified, all are 1)
    // note: cutoff point should be changed accordingly
    //    
    if( strpos( $key, "%" ) !== false && levenshtein( $key, $str, 5,  round( substr_count( $key, "%" ) / strlen( $key ) ), 5) <= 15 ) {
      //
      // more leanient when wildcards are used
      //
      $names .= $str . "\n";
    }
    else if( levenshtein( $key, $str, 5, 6, 5 ) <= 15) { 
      //
      // check entire strings
      //
      $names .= $str . "\n";
    }
    else if( strlen($key) < strlen($str) && levenshtein( $key, substr($str,0,strlen($key)), 5, 6, 5 ) <= 10) {
      //
      // check first part of names, if key is shorter
      //
      $names .= $str . "\n";
    }
    else if ( levenshtein( trim($key, "0123456789"), trim($str,"01223456790"), 5, 6, 5 ) <= 6) { 
      //
      // check similarities without including digits
      //
      $names .= $str . "\n";
    }
    else if ( soundex($key) == soundex($str) ) { 
      //
      //check for similar pronounciation
      //
      $names .= $str . "\n";
    }
  }
  if( $names ) { 
    return $names;
  }
  return FALSE;
}

