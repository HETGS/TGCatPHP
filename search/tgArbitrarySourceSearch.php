<h1> Arbitrary Source Field Search </h1>

<input type='hidden' name='queryType' value='OTHERSOURCE'>
<div class='search' style='text-align:left'>
<p>
This search allows the user to search by any column that exists in the main
source table for very specific searches not covered by the other searches.
For example, to search for sources for which TGCat has more than 10 extractions
in the archive one can select "num_extractions" from the drop down list paired
with the ">" option and enter "10" in the field
</p>

<table>
<?php
require "tgDatabaseConnect.php";

$q = mysql_query( "SHOW COLUMNS IN sourceview" );

$farray = Array();
$fieldsOp = "";
while ( $field = mysql_fetch_assoc( $q ) ){
  array_push( $farray, $field['Field'] );
  $fieldsOp .= "<OPTION> $field[Field] </OPTION>\n";
}

for ( $ind=1; $ind<=15; $ind++ ){
  print "
<tr><td><SELECT NAME='fieldcho_$ind'>
$fieldsOp
</SELECT></td>
<td border=1><input type=radio name=field_cond_$ind value='=' checked > = </td>
<td><input type=radio name=field_cond_$ind value='like' > like </td>
<td border=1><input type=radio name=field_cond_$ind value='>'> > </td>
<td><input type=radio name=field_cond_$ind value='<' > < </td>
<td><input type=text name='field_val_$ind' size=20></td>
<td border=1><input type=radio name='field_andor_$ind' value='and' checked> and </td>
<td><input type=radio name='field_andor_$ind' value='or' >or</td>
</tr>\n
";
}
?>
</table>

</div>
