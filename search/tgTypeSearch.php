<h1> Type Search </h1>
<input type='hidden' name='queryType' value='TYPE'>

<div class='search' style='text-align:left'>
<p>
       Select one or multiple object types to search for. Hold Control/Shift to select multiple in a single
       menu. The numbers besids the types indicate how many objects in the TGCat catalog are assigned to that
       type ( not only primary type )
</p>

<table cellspacing=5px>
<?php
require "tgDatabaseConnect.php";

$qcat = mysql_query( "select t.category,count(distinct(object)) from source as s left join src2type as st using( srcid ) left join type as t using( typeid ) where t.category is not null and s.reject='N' group by t.category order by t.category asc" );
$max_str_len = 22;
$mycontent_type =  "<tr>";
$tdcount = 1;
while ( $row = mysql_fetch_array($qcat) )
  {   
    $mycontent_type .= "<td>$row[0] ( $row[1] )<br><select name=types[] multiple size=6>\n";
    $qtypes = mysql_query( "select t.type,t.description,count(distinct(s.object)) from source as s left join src2type as st using( srcid ) left join type as t using( typeid ) where t.category='$row[0]' and s.reject='N' group by t.type order by t.description asc" );
    while ( $row = mysql_fetch_array($qtypes) )
      { 
	$repl = "";
	$typestring = "($row[2]) $row[1]";
	if ( strlen($row[1]) > $max_str_len ){ $repl = "..."; }
	$mycontent_type .= "<option value='$row[0]' title='$row[1]'>".substr_replace($typestring,$repl,$max_str_len)."</option>\n";
      }
    $mycontent_type .="</select></td>";
    if ( $tdcount%4 == 0 ){
      $mycontent_type .= "</tr><tr>";
    }
    $tdcount = $tdcount+1;
  }
$mycontent_type .= "</tr>";

print $mycontent_type;
?>
</table>

<br>
<p>
To search for all objects matching ALL selected types, please choose "Exclusive"<br>
To search for all objects matching ONE or MORE selected types, please choose "Inlcusive"<br>
To search for all objects whose primary type match ANY selected, please choose "Primary Type"<br>
</p>

Search Type:
<input type='radio' name='type_radio' value='and'> Exclusive
<input type='radio' name='type_radio' value='or' checked > Inclusive
<input type='radio' name='type_radio' value='primary'> Primary Type
</div>