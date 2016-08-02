<h1> Coordinate Search </h1>

<input type='hidden' name='queryType' value='COORD'>
<div class='search' style='text-align:left'>
<p>
Cone search the tgcat database. RA and DEC may be specified as Degree AND/OR Sexagesimal
where the Sexagesimal coordinate is of the format <b>[+-]hh[: ]mm[: ]ss.ddd</b> (+/-  for DEC only )<br>
<!-- the following are identical RA:<br>
160.9895
10:43:57.466
+10 43 57.466
-->
</p>

RA: <input type='text' name='RA' size=20> DEC: <input type=text name='DEC' size=20>
<br><br>

<p>
Multiple coordinates may be searched using the following input box. One coordinate 
per line where RA and DEC are any of the above accepted formats, a comma separates
the RA and DEC components and a new line separates each coordinate specification
</p>
Coordinate List
<br>
<textarea name='coord_multi' cols=45 rows=6></textarea>
<br><br>

<p> 
A file with the above format may be supplied as well
</p>
Coordinate File: 
<input type='hidden' name='MAX_FILE_SIZE' value=100>
<input type='File' name='coord_upfile' size='40'>
<br><br>

<p>
All coord searches will use the same cone radius specified below
</p>
rad: <INPUT TYPE='text' NAME='radius' MAXLENGTH='5' SIZE='5' value='2'>
<SELECT NAME='radcho'>
<OPTION>arcmin
<OPTION>degrees
</SELECT>

</div>
