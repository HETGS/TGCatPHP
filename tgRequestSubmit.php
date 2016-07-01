<?php
####################################################
####################################################
##
##  FILENAME: tgRequestSubmit.php
##
##  DESCRIPTION: 
##    Processes the Serendipitous Source Extractions
##    including error checking and submitting a 
##    file via email.
##
##  AUTHOR: Emma Reishus
##
####################################################
####################################################

// error_reporting( E_ALL );
// ini_set( "display_errors",1);
ob_start( "ob_gzhandler" );

require "tgMainLib.php";
require "tgDatabaseConnect.php";
require "tgProcessors.php";
require "tgNotifications.php";
require "tgMenu.php";

//
// globals, values from form
//

$obsid=$_POST['obsid'];
$coord=$_POST['coordinates'];
$xra=$_POST['coor1'];
$ydec=$_POST['coor2'];
$name=$_POST['objName'];

//
// tests all the parts of the submission to make sure the user has not
// entered anything invalid or incorrectly
// returns a string which is empty if all feilds are valid
// otherwise the string contains chars corresponding to different 
// errors so the user can get specific feedback
//
function testValidSubmission()
{

  // get required globals
  global $coord,$obsid,$name,$xra,$ydec;

  $result = '';

  if($obsid=='' || $name=='' || $xra=='' || $ydec=='') {
    $result .= 'a';
  } 
  if($coord==='sp'){
    if($xra != '' && !is_numeric($xra)) {
      $result .= 'b';
    }
    if(is_numeric($xra) && $xra + 0.0 < 0) {
      $result .= 'o';
    }
    if($ydec != '' && !is_numeric($ydec)) {
      $result .= 'c';
    }    
    if(is_numeric($ydec) && $ydec + 0.0 < 0) {
      $result .= 'p';
    }
  }
  else {
    if($xra != ''){
      $ra=correct_format_ra($xra);
      if($ra != ''){
	if(!is_numeric($xra)) {
	  $result .= 'k';
	}
	if($xra + 0.0 < 0 || $xra + 0.0 > 360) {
	  $result .= 'i';
	}	
      }
      if(strpbrk($result,'ki')){
	$result .= $ra;
      }
    }    
    if($ydec != ''){
      $dec=correct_format_dec($ydec);
      if($dec != '') {
	if(!is_numeric($ydec)) {
	  $result .= 'n';
	}
	if($ydec + 0.0 < -90 || $ydec + 0.0 > 90) {
	  $result .= 'j';
	} 
      }
      if(strpbrk($result,'nj')){
	$result .= $dec;
      }
    }
  }
  if($obsid != '' && (string)(int)$obsid !== $obsid) { //must be int not just num...
    $result .= 'l';
  }

  return $result;

}

//
// Checks the format for RA in the Sexagesimal coordinate system
//
function correct_format_ra($ra)
{

  $result = '';
  if(strlen($ra) < 5 || strlen($ra) > 15) {
    $result .= 'm';
  }  

  $arr=preg_split('/[: ]+/',$ra);

  if(count($arr)!=3){
    $result .= 'm';
  }
  else{ 

    $hh=$arr[0];
    $mm=$arr[1];
    $ss=$arr[2]; 
    
    // uncomment the next line for debugging:
    //print "ra: " . $hh . ":" . $mm . ":" . $ss;

    if(strstr($hh,'.')!==FALSE || strstr($mm,'.')!==FAlSE) {
      $result .= 'd';
    }  
    
    if((int)$hh!=$hh || (int)$mm!=$mm || !is_numeric($ss)) {
      $result .= 'd';
    }
    if((int)$hh < 0 || (int)$hh > 24 || (int)$mm < 0 || (int)$mm > 59 || $ss + 0.0 < 0.0 || $ss + 0.0 >= 60.0) {
      $result .= 'e';
    }
  }

  return $result;

}

//
// Checks the format for DEC in the Sexagesimal coordinate system
//
function correct_format_dec($dec)
{  

  $result = '';  
  if(strlen($dec) < 5 || strlen($dec) > 15) {
    $result .= 'f';
  }

  $shift=0;
   if($dec[0] == '+' || $dec[0]=='-'){
     $shift=1;
   }

  $arr=preg_split('/[: ]+/',substr($dec,$shift));

  if(count($arr)!=3){
    $result.= 'f';
  }
  else {
    $dd=$arr[0];
    $mm=$arr[1];
    $ss=$arr[2];

    // uncomment the next line for debugging:
    //print "<br>dec: ". $dec[0] . " " . $dd . ":" . $mm . ":" . $ss;

    if((int)$dd < 0) { //ie --5:mm:ss is given
      $result .='f';
    }    
    if(strstr($dd,'.')!==FALSE || strstr($mm,'.')!==FAlSE) {
      $result .= 'g';
    }
    if((int)$dd!=$dd || (int)$mm!=$mm || !is_numeric($ss)) {
      $result .= 'g';
    }
    if((int)$dd > 90 || (int)$mm < 0 || (int)$mm > 59 || $ss + 0.0 < 0.0 || $ss + 0.0 >= 60.0) {
      $result .= 'h';
    }
  }

  return $result;

}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<!-- <!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd"> -->
<html>
<head>
<title> TGCat - Extraction Submission </title>

<?php include "styleInc.php"?>

</head>
<body>

<div id='page' style='min-width:800px;'>

<?php

$status="source extraction request ";

// get the string of chars to indicate incorrect form data
$errors = testValidSubmission();

// no errors with form data
if($errors === '') {

  // 2013.10.18 dph --- inserted conversion to decimal, simply because the tgcat_admin
  // side does not (yet) convert from sexagesimal to decimal.

  if (!is_numeric( $xra ) )
    {
      $cdelim = ":";
      if ( !strpos( $xra, $cdelim ) )
	{
	  $cdelim = " ";
	}
      $xra = degrees( $xra, $cdelim, 1 );
    }

  if (!is_numeric( $ydec ) )
    { 
      $cdelim = ":";
      if ( !strpos( $ydec, $cdelim ) )
	{
	  $cdelim = " ";
	}
      $ydec = degrees( $ydec, $cdelim, 0 );
    }
  // *** end dph edit

  //
  // get form values that were not needed previously
  //
  $exact="No";
  if(isset($_POST['exact']) && $_POST['exact']=="Y"){
    $exact="Yes";
  }
  $user_email=$_POST['email'];
  $comments=$_POST['comments'];
  
  // tgcat email subject
  $subject="Source Extraction Request: " . $obsid;

  //
  // write file content and email message 
  // mostly same information, but with some differences for file parsing
  //
  $content="Obsid: " . $obsid . " \\ ";
  $message="Obsid: " . $obsid . " \n";

  if($coord=='sp') {
    $content.="Sky pixels: X=" . $xra . " Y=" . $ydec . " \\ ";
    $message.="Sky pixels: X=" . $xra . " Y=" . $ydec . " \n";
  }
  else {
    $content.="Celestial coordinates: RA=" . $xra . " DEC=" . $ydec . " \\ ";
    $message.="Celestial coordinates: RA=" . $xra . " DEC=" . $ydec . " \n";
  }

  $content.="Exact: " . $exact . " \\ Target: " . $name . " \\ ";
  $message.="Exact: " . $exact . " \nTarget: " . $name . " \n";

  if($comments!=''){  
    $content.="Comments: " . $comments . " \\ ";
  }
  $message.="Comments: " . $comments . " \n";
  if($user_email!='') {
    $content.="Email: " . $user_email . " \\ ";
  }
  $message.="Email: " . $user_email . " \n";

  //
  // create file
  //
  $id = uniqid();
  $filename = "tmp/request_" . $id . ".txt";
  $fp = fopen($filename, 'w');
  chmod($filename,0777);
  fwrite($fp,$content);
  fclose($fp);

  $from = "tgcat@space.mit.ed";

  $to="dph@space.mit.edu";
  //$to="emmakarinr@gmail.com";
  //$to="tgcat@space.mit.edu"; 
 
  // puts content in html compatible form 
  $content = nl2br($content);  
  
  //*** Uniqid Session ***//  
  $random_hash = md5(uniqid(time()));  
  
  //
  // create email headers
  //
  $headers = "";  
  $headers .= "From: ".$from."\nReply-To: ".$to."";  
  
  $headers .= "MIME-Version: 1.0\n";  
  $headers .= "Content-Type: multipart/mixed; boundary=\"".$random_hash."\"\n\n";  
  $headers .= "This is a multi-part message in MIME format.\n";  
  
  //
  // include email message
  //
  $headers .= "--".$random_hash."\n";  
  $headers .= "Content-type: text; charset=utf-8\n";  
  $headers .= "Content-Transfer-Encoding: 7bit\n\n";  
  $headers .= $message."\n\n";  

  //
  // attach file
  //
  $content = chunk_split(base64_encode(file_get_contents($filename)));  
  $headers .= "--".$random_hash."\n";  
  $headers .= "Content-Type: application/octet-stream; name=\"".$filename."\"\n";  
  $headers .= "Content-Transfer-Encoding: base64\n";  
  $headers .= "Content-Disposition: attachment; filename=\"" . "request_" . $id . ".txt" . "\"\n\n";  
  $headers .= $content."\n\n";  
  
  if(mail($to,$subject,null,$headers)) { 

    $status .= "successful";

    print "<h1> Form Submission Successful </h1><div class='search' style='wdith:900px'><input type='hidden' name='request' value='TEST'>
  <p>If there are no errors, the extraction will typically appear within one to two days.</p> If you entered your
email correctly, you will be notified of the request's completion.<br>Otherwise, check back in one to two days for
the extraction.";
  }
  else { 
    // email not sent successfully, show there was submission error
    $errors = ' ';
  }

  // remove file
  unlink($filename);

 }

//
// remove duplicate chars from $errors
//
$errors=preg_replace('{(.)\1+}','$1',$errors);

//
// check for errors with the form data or submission
//
if($errors !== '') {

  $status .= "unsuccessful";  

  // tell user there were errors
  if( strlen($errors) > 1 ) {
    $num="were errors";
  }
  else {
    $num="was an error";
  }
  print "<h1> Form Submission Unsuccessful </h1><div class='search' style='width:900px'><input type='hidden' name='request' value='TEST'>
<p><i> There " . $num . " with your submission: </i></p>";
  if( $errors === ' ' ) { // error wasn't with form, but with email
    print "Sorry, something went wrong in the submission process. Please try again later.<br><br>";
  } 
  else { 
    //
    // error was with form, instruct user to make changes
    //
    print "Hit the back arrow to edit your submission and  make any necessary changes. <br> 
Or click cancel to return to the home page. <br><br>";
  }
 
  //
  // not all required fields filled in
  //
  if( strstr($errors,'a') !== FALSE ) {

    print "<p> Some of the required fields were left blank. </p>";

    $arr[] = array($obsid,"Obsid");
    $arr[] = array($xra,"X or RA");
    $arr[] = array($ydec,"Y or DEC");
    $arr[] = array($name,"Target");

    foreach($arr as $key=>$val) {
      if($val[0]=='') {
	print "Please fill in a value for \"" . $val[1] . "\". <br><br>";
      }
    }

  }

  //
  // sky pixels were invalid
  //
  if( strpbrk($errors,'bcop') !== FALSE) {    
    
    print "<p> The coordinates (Sky pixels) X and Y must be non-negative real numbers. </p>";

    if(strpbrk($errors,'bc')) {
      if(strstr($errors,'b')!==FALSE && strstr($errors,'c') !==FALSE) {
	$arr=array("s","X and Y are not");    
      }
      else if(strstr($errors,'b')!==FALSE) {
	$arr=array("","X is not a");
      }
      else {
	$arr=array("","Y is not a");
      }

      print "The given value" . $arr[0] . " for " . $arr[1] . "  real number" . $arr[0] . ".<br><br>";
       
    }
    if(strpbrk($errors,'op')){
      if(strstr($errors,'o')!==FALSE && strstr($errors,'p') !==FALSE) {
	$arr=array("s","X and Y are");    
      }
      else if(strstr($errors,'o')!==FALSE) {
	$arr=array("","X is a");
      }
      else {
	$arr=array("","Y is a");
      }

      print "The given value" . $arr[0] . " for " . $arr[1] . " negative number" . $arr[0] . ".<br><br>";
    
    }
  }

  //
  // celestial coordinates in sexagesimal were invalid
  //
  if(strpbrk($errors,'mdefghijkn')!==FALSE) {
      
    print "<p> Celestial Coordinates must follow one of two forms. <br><br>
When given in Sexagesimal, RA and DEC must fit these specifications:
RA must follow the form hh[: ]mm[: ]ss.ddd and DEC must 
follow the form [-+]hh[: ]mm[: ]ss.ddd. RA is in the form of hours:minutes:seconds, 
where hours is an integer ranging from 00 to 24, minutes is an integer ranging from 
00 to 59, and seconds is a real number ranging from 0.0 to 60.0. DEC is of the form 
degrees:minutes:seconds, where degrees is an integer ranging from -90 to 90, minutes 
is an integer ranging from 0 to 60, and second is a real number ranging from 0.0 to 60.0. <br><br>
When given in decimal degrees, both RA and DEC must be real numbers. RA must be in the
range 0.0 to 360.0 and DEC must be in the range -90.0 to 90.0.</p><br>"; 

    /* flags are triggered so that a formatting issue which leads to type or range errors 
     will not trigger multiple print statements, fixing formatting issues can cause these
     other errors to be fixed as well */
    $ra_flag=True;
    $dec_flag=True;
      
    print "<i>For sexagesimal</i>: <br><ul>";
      
    // error on format
    if(strstr($errors,'m') || strstr($errors,'f')){
	
      if(strstr($errors,'m') && strstr($errors,'f')){
	$format_x = array("s","RA and DEC do");
	$ra_flag=False;
	$dec_flag=False;
      }
      if(strstr($errors,'m') && $ra_flag){
	$format_x = array("","RA does");
	$ra_flag=False;
      }
      if(strstr($errors,'f') && $dec_flag) {
	$format_x = array("","DEC does");
	$dec_flag=False;
      }
	
      print "<li>" . $format_x[1] . " not follow the correct format. </li><br>";
	
    } 
      
    // error on type of $hh/$dd,$mm, or $ss (can sometimes apply to formatting issues such as $hh=--5)
    if(strstr($errors,'d') || strstr($errors,'g')){
	
      if(strstr($errors,'d') && $ra_flag){
	$type_x = array("","RA is");
      }
      if(strstr($errors,'g') && $dec_flag) {
	$type_x = array("","DEC is");
      }    
      if(strstr($errors,'d') && strstr($errors,'g') && $dec_flag && $ra_flag){
	$type_x = array("s","RA and DEC are");
      }
	
      if($type_x != '') { // $type_x may not have been initialized if a flag was False
	print "<li>" . $type_x[1] . " not of the correct type. </li><br>";
      }
	
    }      
      
    // error on range of $hh/$dd,$mm, or $ss (must be correct type)
    if(strstr($errors,'e') || strstr($errors,'h')){
      
      if(strstr($errors,'e') && $ra_flag){
	$range_x = array("","RA is");
      }
      if(strstr($errors,'h') && $dec_flag) {
	$range_x = array("","DEC is");
      }      
      if(strstr($errors,'e') && strstr($errors,'h') && $ra_flag && $dec_flag){
	$range_x = array("s","RA and DEC are");
      }
	
      if($range_x != '') {// $range_x may not have been initialized if a flag was False
	print "<li>" . $range_x[1] . " in the correct format but not in the range of possible values. </li><br>";
      }
	
    }

    print "</ul><i>For decimal degrees</i>: <br><ul> ";

    // not correct type
    if(strstr($errors,'k') || strstr($errors,'n')) {
      
      if(strstr($errors,'k') && strstr($errors,'n')) {
	$arr=array("s","RA and DEC are not"); 
      }     
      else if(strstr($errors,'k')) {
	$arr=array("","RA is not a");
      }
      else {
	$arr=array("","DEC is not a");
      }
      
      print "<li>" . $arr[1] . " real number" . $arr[0] . ". </li><br>";
	
    }
      
    // not correct range, only if type is correct
    if(strstr($errors,'i') || strstr($errors,'j')) {
      
      if(strstr($errors,'i') && strstr($errors,'j')) {
	$arr=array("s","RA and DE are"); 
      }     
      else if(strstr($errors,'i')) {
	$arr=array("","RA is");
      }
      else {
	$arr=array("","DE is");
      }
	
      print "<li>" . $arr[1] . " not in the correct range" . $arr[0] . ". </li><br>";
	
    }
    print "</ul>";
      
  }
  
  //
  // Obsid was invalid
  //
  if(strstr($errors,'l')) {
    print "<p> The Obsid must be an integer. </p>";
  }
 }

?>

</div>

</div>

<?php

if($errors === ''){
  print "<form name='test' action='tgRequest.php'><div class='searchSubmit'><input type='submit' value='New Request'>";
}
 else {
   print "<form name='test' action='http://tgcat.mit.edu/dev/'><div class='searchSubmit'><input type='submit' value='Cancel'>";
 }

?>

<br><br><br><br>
</div>
</form>

<?php
initMainMenu();
generateFileMenu();
generateHelpMenu();
generateQuickSearchBar();
finalizeMainMenu();

statusMessage( $status );
?>


</body>
</html>