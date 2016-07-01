<?php

  //
  // Get columns that the user has chosen to save
  // in cookie form, rather than the defaults
  //

function getCookieColumnSpecs( $tableCode )
{  
  $cols = "";
  if ( $_COOKIE["${tableCode}columns"] )
    {
      $cols = $_COOKIE["${tableCode}columns"];
      if ( $cols == "NOCOLS" ) { $cols = ""; }
    }
  if ( $_POST['used_selector'] ) 
    {
      $cols = "";
      foreach ( array_keys($_POST) as $c )
	{
	  if ( preg_match( "/^show_.*/", $c ) )
	    {
	      $c = preg_replace( "/show_/","",$c );
	      $cols = $cols . "$c,";
	    }
	}	    
      $cols = rtrim($cols,",");
    }
  return $cols;
}

function parseDetectorGrating( $post )
{
  
  $myselins = array();
  $myselgra = array();
  
  //print_r( $post );

  foreach ( $post as $item )
    {
      //for now we dont have the sub-instrument category
      switch ( $item )
	{
	case "ACIS-I":
	  $myst = 'instrument = "ACIS"';
	  array_push($myselins,$myst);
	  break;
	case "ACIS-S":
	  $myst = 'instrument = "ACIS"';
	  array_push($myselins,$myst);
	  break;
	case "HRC-I":
	  $myst = 'instrument = "HRC"';
	  array_push($myselins,$myst);
	  break;
	case "HRC-S":
	  $myst = 'instrument = "HRC"';
	  array_push($myselins,$myst);
	  break;
	case "LETG":
	  $myst = 'grating = "LETG"';
	  array_push($myselgra,$myst);
	  break;
	case "HETG":
	  $myst = 'grating = "HETG"';
	  array_push($myselgra,$myst);
	  break;
	}
    }

  $sel1 = join(" OR ",$myselins);
  $sel2 = join(" OR ",$myselgra);

  $w = "";
  if ( count($myselins) > 0 ){
    $w .= "($sel1)";
  }
  if ( count($myselgra) > 0 ){
    if ( $w ){
      $w .= " AND ";
    }
    $w .= "($sel2)";
  }
    
  return $w;
}

function tgcatCoordinateSearch( $my_ra, $my_dec, $my_rad, $my_unit ){
    //
    // defaults for radius match
    //
    if ( ! $my_rad ) { $my_rad = 2; $my_unit = "arcmin"; }


    if ( preg_match( "/\d+\ \d+\ \d+/", $my_ra ) || preg_match( "/\d+:\d+:\d+/",   $my_ra ) )
      {
	//
	// if not degrees asssume it is sexigessimal    
	// 
	if     ( preg_match( "/\d+\ \d+\ \d+/", $my_ra ))  $delim = " ";   
	elseif ( preg_match( "/\d+:\d+:\d+/",   $my_ra ))  $delim = ":"; 
     
	$my_ra = degrees ( $my_ra, $delim, True );
      }
    if ( preg_match( "/\d+\ \d+\ \d+/", $my_dec ) || preg_match( "/\d+:\d+:\d+/",   $my_dec ) )
      {
	//
	// if not degrees asssume it is sexigessimal
	//
	if     ( preg_match( "/\d+\ \d+\ \d+/", $my_dec ))  $delim = " ";   
	elseif ( preg_match( "/\d+:\d+:\d+/",   $my_dec ))  $delim = ":"; 
      
	$my_dec = degrees ( $my_dec, $delim, False );
      }

    // make the where statement
    $where = generateSearchRadiusWhere( $my_ra, $my_dec, $my_rad, $my_unit );
    return $where;
}

//
// function to return a where condition from ra,dec, and 
// search radius. this will be used in search by name with
// simbad and search by coord
function generateSearchRadiusWhere( $ra, $dec, $rad, $unit )
{
  switch ( $unit )
    {
    case "degrees":
      $search_rad = $rad;
      break;
    case "arcmin":
      $search_rad = $rad/60.0;
      break;
    }
  
  $select_whrc = "( sqrt( power( (ra - $ra)*cos(decl-$dec), 2 ) ) + sqrt( power( decl - $dec,2 ) ) ) < $search_rad";

  return $select_whrc;
}


function generateTypeWhere( $searchType, $types ){
  $where = "";
  if ( $searchType == "primary" )
    {
      $searchType = "or";
      $type_select = "pType";
    }
  else
    {
      $type_select = "other_types";
    }
  foreach ( $types as $t )
    { 
      $where .= "( $type_select like \"$t,%\" ".
	"or $type_select like \"%, $t,%\" ".
	"or $type_select like \"%, $t\" ".
	"or $type_select like \"$t\" ) ".
	"$searchType"; 
    }
  $where = rtrim( $where,"$searchType" );
  if ( $where == "" ){ $where = "srcid=-1"; }
  return $where;
}

function tgcatNameSearch( $names, $simbad = false, $simbad_rad = 2, $simbad_unit = "arcmin", $ned = false  ){
  // 
  // if name was not specified in the field
  // then return all sources
  //
  //print "NAMES INSIDE-PRE CHECK:<br>\n";
  //print_r( $names );

  //if( $simbad) { print "USING SIMBAD"; }
  //else { print "NOT"; }

  if ( ! $names || $names == "" || ( count( $names ) == 1 && ! $names[0] ) ){    
    $names = Array( 0=> "%" );
  }
  
  if ( ! is_array( $names ) ){
    $names = Array( 0=> $names );
  }

  //print "NAMES INSIDE-POST CHECK:<br>\n";
  //print_r( $names );

  $where = "";
  foreach ( $names as $name )
    {
      $name = trim( $name );
      //
      // if not using SIMBAD simply check the name against our database 
      // target column
      //
      if ( !$name ){ continue; }

      $where .= " OR ( object like \"%$name%\"";
      $where .= " OR simbad_ID like \"%$name%\"";      
      //
      // collapse any spaces and check that as well
      //
      $more_select = preg_replace( '/\s+/','', $name );
      
      $where .= " OR object like \"%$more_select%\"";
      $where .= " OR simbad_ID like \"%$more_select%\"";      

      $more_select = preg_replace( '/\s+/',' ', $name );
  
      $where .= " OR object like \"%$more_select%\"";
      $where .= " OR simbad_ID like \"%$more_select%\"";      

      $where = $where . " )";
      
      if ( $simbad ){
	//
	// check to see if there are regexpression being
	// attempted in the search
	//
	if ( preg_match( "/\%/", $name) ){
	  $search = "query%20id%20wildcard%20";
	  $name = preg_replace( "/\%/", "*", $name );
	}
	else {
	  $search = "query%20id%20";
	}
	//
	// prepare the name for passing with GET data
	//
	$name = prepare_for_http( $name );
	$search = "$search$name";
	//
	// submit the query to SIMBAD and return lines of the form
	// ra(deg),dec(deg),{primary cat name}
	//
	$simbad_query = "http://simbad.harvard.edu/simbad/sim-script?".
	  "script=output%20console=off%20script=off%0a".
	  "%20format%20object%20%22%25COO(d;A,D),%25IDLIST(1)%22%0a".
	  "$search";
	//
	// grab and parse results
	//
	$results = fopen("$simbad_query","r");
	if ( $results ){
	  $contents = stream_get_contents($results,1024);
	  fclose($results);
	}       
	//print "<!-- \n SIMBAD QUERY STRING: $simbad_query \n RESULTS: $contents \n -->\n";
	//
	// split on lines 
	//
	$simbad_array = split("\n", rtrim($contents) );
	//
	// right now, take only the first result's ra,dec
	//
	foreach ( $simbad_array as $info_line ){
	  $info_array = split(",",$info_line);	    
	  //print "<!-- split line : $info_line for preprocessing -->\n";
	  $my_ra =  $info_array[0];
	  $my_dec = $info_array[1];
	  //print "<!-- RA: $my_ra DEC: $my_dec -->\n";
	  if ( ! ( is_numeric($my_ra) && is_numeric($my_dec) ) ){ continue; }
	  //print "<!-- preprocessed and matched line $info_line -->\n";
	  // generate the where condition
	  $where = " $where or ( " . generateSearchRadiusWhere( $my_ra, $my_dec, $simbad_rad, $simbad_unit ) ." )";
	}
      }
      if ( $ned ) { // NED search when it starts working...
	//
	// check to see if there are regexpression being
	// attempted in the search
	//
	if ( preg_match( "/\%$/", $name) ) { $extended = "yes"; }
	else { $extended="no"; }    
	$name = urlencode( preg_replace( "/\%/", "", $name ) );	  
	$ned = "http://nedwww.ipac.caltech.edu/cgi-bin/nph-objsearch?".
	  "objname=$name&extend=$extended&out_csys=Equatorial&".
	  "out_equinox=J2000.0&of=xml_posn&list_limit=0&img_stamp=NO";
	//
	// grab and parse results
	//
	$results = fopen ($ned,"r");
	if ( $results ){
	  $contents = stream_get_contents($results,-1);
	  fclose($results);
	}    
	//print "<!-- NED QUERY STRING:\n$ned \nRESULTS:\n $contents \n-->";	  
      }
    }  
    $where = ltrim($where,"OR ");
    return $where;
}
	

function processNewQueryForm( $q ){
  //
  // The necessary variables to be set
  // in this function
  //
  $tableCode = "o";
  $where = "1";
  $sort = "";
  //
  // if there is a limit on the rows
  //
  $limitRows = $_POST['limitRowNumber'];
  //
  // The type is to be stored in a hidden
  // form input of the following definition
  //
  $type = $_POST['queryType'];
  //
  // First check to see if it is quick to set
  // the following search type based on reg-exp's
  //
  if ( $type == "QUICK" ){
    $quickfield = $_REQUEST['quicksearch'];
    //
    // see if it matches a targ name ( any letters )
    //
    if ( preg_match('/[a-zA-Z]+/',$quickfield ) )
      {
 	$type = "NAME";
	$_POST['targ'] = $quickfield;
      }
    //
    // see if it matches coords ( numbers with decimals or
    // +/-
    //
    elseif ( preg_match( '/\d+\ *(\+|\-)\d+/', $quickfield ) )
      {
	if ( preg_match( '/\+/', $quickfield )){ $split = "+"; }
	else { $split = "-"; }
	$type = "COORD";
	$myradec = split("[$split]",$quickfield);
	$_POST["RA"]  = $myradec[0];
	$_POST["DEC"] = "$split" . $myradec[1];
      }
    //
    // see if obsid match ( just numbers )
    //
    elseif ( preg_match( '/\d+/', $quickfield ) )
      {
	$type = "OBSID";
	$_POST["obsid"] = $quickfield;
      }
    //
    // if it is the wildcard give back a number of EXTRACTIONS
    //
    elseif ( $quickfield == "%" )
      {
	$type = "OBSID";
	$_POST['limitrows'] = 100;
      }
    //
    // if it is empty or doesn't match the above, return
    // a 'google' I am feeling lucky random
    else
      {
	$type = "NAME";
	$limitRows = 1;
	$sort = "rand()";
      }
  }
  //
  // Now process and generate the where clauses
  // and define the type of table
  //
  switch ( $type ){
   
    //
    // query on object Type. This should return a source
    // table ( tableCode "s" )
    // 
  case "TYPE":      #----------- TYPE -------------------------------------------------#

    //
    // source table
    //
    $tableCode = "s";
    
    $where = "";

    $searchType = $_POST['type_radio'];
    $types = $_POST['types'];
    
    $where = generateTypeWhere( $searchType, $types );

    break;
    
    //
    // obsid type should return an extractions table
    //
  case "OBSID":     #----------- OBSID -------------------------------------------------#               

    //
    // obsid table
    //
    $tableCode = "o";

    $obsids =  trim( $_POST['obsid']  );

    //
    // parse a file if necessary
    $file_obsids="";
    if ( $_FILES['obsid_upfile']['tmp_name'] ){
      $handle = fopen( $_FILES['obsid_upfile']['tmp_name'] , "r");
      $file_obsids = fread( $handle, $_FILES['obsid_upfile']['size'] );
      fclose($handle);
      if ( preg_match( "/[a-zA-Z]+/",$file_obsids)  )
	{ 
	  $file_obsids = "-1";
	}
      $file_obsids = preg_replace( "/\ +/","", $file_obsids);
      $obsids = $obsids . " " . trim( $file_obsids );
    }

    //
    // this allows us to add obsids dynamically
    // and not return errors for no obsids
    //
    $where = "( -1 )"; 
    
    if ( ! $obsids ) { $obsids = "%"; }
    
    $obsids = rtrim($obsids);    
    $obsids = rtrim($obsids,",");
    $obsids = preg_replace( "/\s+/"," ", $obsids);   
    $obsar = split("[, ]",$obsids);
    
    $wh = join("\" or obsid like \"",$obsar);
    $wh = "( obsid like \"$wh\" )";
    
    $where = "$wh and $where";

    //
    // obsid search allows specification of detector
    // and gratings combinations to be included/excluded
    //
    $detGrat = parseDetectorGrating( $_POST );
    //print_r( $detGrat );
    if ( $detGrat ) {  $where = "$where and ( $detGrat )"; }
    //print_r( $where );
    break;

    //
    // name search will return a source
    // table matching 'name' to object 
    //
  case "NAME":      #----------- NAME  -------------------------------------------------#               

    //
    // source table
    //
    $tableCode = "s";
    
    $names = trim( $_POST['targ'] );  
    //
    // parse a file if necessary
    //
    $file_obsids="";
    if ( $_FILES['source_upfile']['tmp_name'] ){
      $handle = fopen( $_FILES['source_upfile']['tmp_name'] , "r");
      $file_names = fread( $handle, $_FILES['source_upfile']['size'] );
      fclose($handle);
      $names = $names . "\n" . trim( $file_names );
    }
    //
    // names are separated by new lines. Go through
    // and add them to the where clause
    //
    $names = split("[\n]",$names );

    $simbad = false;
    if ( $_POST['simbad'] ){ $simbad = true; }
    
    //print "NAMES BEFORE:<br>\n";
    //print_r( $names );
    $where = tgcatNameSearch( $names, $simbad, $_REQUEST['radius'], $_REQUEST['radcho'] );
    //print "<br>WHERE CLAUSE AFTER:<br>\n$where";

    break;

    //
    // coord should return source table
    // matching the input coords to within a
    // specified radius
    //
  case "COORD":   #-----------------------------COORD--------------------------------#

    //
    // source table
    //
    $tableCode = "s";

    $my_ra =   trim( $_POST['RA']     );
    $my_dec =  trim( $_POST['DEC']    );
    $my_rad =  trim( $_POST['radius'] );
    $my_unit = trim( $_POST['radcho'] );
    $my_radec_field = trim( $_POST['coord_multi'] );
       
    $_where = Array();
    //
    // check for file upload
    //
    $file_obsids="";
    if ( $_FILES['coord_upfile']['tmp_name'] ){
      $handle = fopen( $_FILES['coord_upfile']['tmp_name'] , "r");
      $file_coords = fread( $handle, $_FILES['coord_upfile']['size'] );
      fclose($handle);
      $my_radec_field .= trim( $file_coords );
    }
    //
    // now process the field/file
    //
    //print_r( $my_radec_field );
    if ( $my_radec_field ){
      foreach ( split("[\n]",$my_radec_field ) as $line ){
	$line = trim( $line );
	$line = preg_replace( '/,*\ *\+/',',+',$line );
	$line = preg_replace( '/,*\ *\-/',',-',$line );
	if ( ! preg_match( "[,]",$line ) && ! preg_match( "[+|-]", $line ) ){
	  $line = preg_replace( '/\ +/',',+',$line );
	}	
	$_radec = split( ",", $line );
	$_ra  = trim($_radec[0]);
	$_dec = trim($_radec[1]);
	array_push( $_where, tgcatCoordinateSearch( $_ra, $_dec, $my_rad, $my_unit ) );
      }
    }    
    //
    // process the standard input fields
    //
    if ( $my_ra && $my_dec ){ 
      array_push( $_where , tgcatCoordinateSearch( $my_ra, $my_dec, $my_rad, $my_unit ) );
    }
    //print_r( $_where );      

    $where = implode( " OR ", $_where );

    //print_r( $where );
    
    break;

    //
    // spectral properties search
    // should return an extractions
    // table matching the specified
    // count/flux rates
    //
  case "SPECPROP":    
    //
    // extractions table
    //
    $tableCode= "o";

    $sw = Array();
    // get the goods
    if ( is_numeric( $_POST['min_hegb_rate'] )){ 
      array_push( $sw, "s.count_rate >=".$_POST['min_hegb_rate'] ); 
      $js = 1;
    }
    if ( is_numeric(  $_POST['max_hegb_rate'] )){ 
      array_push( $sw, "s.count_rate <=".$_POST['max_hegb_rate'] ); 
      $js = 1;
    }
    if ( is_numeric( $_POST['min_megb_rate'] )){ 
      array_push( $sw, "a.count_rate >=".$_POST['min_megb_rate'] ); 
      $ja = 1;
    }
    if ( is_numeric( $_POST['max_megb_rate'] )){ 
      array_push( $sw, "a.count_rate <=".$_POST['max_megb_rate'] ); 
      $ja = 1;
    }
    if ( is_numeric( $_POST['min_legb_rate'] )){ 
      array_push( $sw, "d.count_rate >=".$_POST['min_legb_rate'] ); 
      $jd = 1;
    }
    if ( is_numeric( $_POST['max_legb_rate'] )){ 
      array_push( $sw, "d.count_rate <=".$_POST['max_legb_rate'] ); 
      $jd = 1;
    }
    if ( is_numeric( $_POST['min_atom'] )){
      array_push( $sw, "g.".$_POST['atom_scale']." >=".$_POST['min_atom'] ); 
      $jg = 1;
    }
    if ( is_numeric( $_POST['max_atom'] )){
      array_push( $sw, "g.".$_POST['atom_scale']." <=".$_POST['max_atom'] ); 
      $jg = 1;
    }

    $sq = "select distinct(s.id) from spec_prop as s ";
    $swh = "where ";
    if ( $js ) { 
      array_push($sw,"s.label='heg_band' and s.flag=0"); 
    }
    if ( $ja ) { 
      $sq .= "left join spec_prop as a on ( s.id=a.id ) "; 
      array_push($sw,"a.label='meg_band' and a.flag=0"); 
    }
    if ( $jd ) { 
      $sq .= "left join spec_prop as d on ( s.id=d.id ) "; 
      array_push($sw,"d.label='letgs_band' and d.flag=0");
    }
    if ( $jg ) { 
      $sq .= "left join spec_prop as g on ( s.id=g.id ) "; 
      array_push($sw,"g.label='".$_POST['atom']."' and g.flag=0");
    }   
    
    $sq .= "where " . join(" and ",$sw);

    $sqq = mysql_query( $sq );

    $in_obs = "";
    while ( $row = mysql_fetch_array( $sqq ) )
      {
	$in_obs .= "$row[0],";
      }
    $in_obs = rtrim( $in_obs, "," );
    if ( !$in_obs ) { $in_obs = "-10"; }
        
    $where = "id in ( $in_obs )";
    
    break;

    //
    // other should return an extractions table
    // matching chosen fields
    //
  case "OTHER":
   
    // look through all fields begining with fieldcho
    $fieldchos = preg_grep( "/^fieldcho/", array_keys($_POST) );
   
    // loop through and build where from values
    $where = "";
    foreach ( $fieldchos as $my_field )
      {
	$ind = preg_replace("/fieldcho_/","",$my_field);
	
	$my_field_val = $_POST["field_val_$ind"];
	$my_field_name = $_POST["$my_field"];
	$my_cond = $_POST["field_cond_$ind"];
	$pind = $ind-1;
	$andor = $_POST["field_andor_$pind"];
	
	if ( $my_field_val )
	  {
	    $where = $where . " $andor $my_field_name $my_cond \"$my_field_val\"" ;
	  }
	
      }
   if ( $where == "" )
     {
       $where = 0;
     }
   
   break;

   //
   // other should return an source table
   // matching chosen fields
   //
  case "OTHERSOURCE":
    
    //
    // set the table code to soure
    //
    $tableCode = "s";
    
    // look through all fields begining with fieldcho
    $fieldchos = preg_grep( "/^fieldcho/", array_keys($_POST) );
   
    // loop through and build where from values
    $where = "";
    foreach ( $fieldchos as $my_field )
      {
	$ind = preg_replace("/fieldcho_/","",$my_field);
	
	$my_field_val = $_POST["field_val_$ind"];
	$my_field_name = $_POST["$my_field"];
	$my_cond = $_POST["field_cond_$ind"];
	$pind = $ind-1;
	$andor = $_POST["field_andor_$pind"];
	
	if ( $my_field_val )
	  {
	    $where = $where . " $andor $my_field_name $my_cond \"$my_field_val\"" ;
	  }
	
      }
   if ( $where == "" )
     {
       $where = 0;
     }
   
   break;

  }
  
  //
  // see if there may be saved columns from cookies
  // for this user
  //
  $cols = getCookieColumnSpecs( $tableCode );
  //
  // now we can initialize the query
  //
  //print( "$type, $tableCode, $where, $cols, $sort, $limitRows" );
  $q->initNewQuery( $type, $tableCode, $where, $cols, $sort, $limitRows );

  if ( $tableCode == 's' ){
    $ocols = getCookieColumnSpecs( 'o' );                                                                                         
    if ( $ocols ){ $q->setTableCode('o');$q->setColumns($ocols);$q->setTableCode($tableCode); }                                    
  }
  else {
    $scols = getCookieColumnSpecs( 's' );                                                                                          
    if ( $scols ){ $q->setTableCode('s');$q->setColumns($scols);$q->setTableCode($tableCode); } 
  }
}

?>
