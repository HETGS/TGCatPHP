<h1> Arbitrary Extraction Field Search </h1>

<input type='hidden' name='queryType' value='OTHER'>
<div class='search' style='text-align:left'>
<p>
This search allows the user to search by any column that exists in the main
extractions table for very specific searches not covered by the previous searches.
For example, to search for all newly added or reprocessed extractions on can
search on "proc_date" choose the "like" option then enter YYYY-MM using the
current year and month
</p>

<table>
<?php
require "tgDatabaseConnect.php";

$q = mysql_query( "SHOW COLUMNS IN obsview" );

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
