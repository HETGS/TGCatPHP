<h1>Obsid Search</h2>
<input type='hidden' name='queryType' value='OBSID'>

<div class='search' style='text-align:left'>

<p>
       Search by <i>Chandra</i> ObsID. List an arbitrary number of ObsIds in the box separated by a ",",
       newlines, or spaces . The wildcard character
       "%" will match <i>zero</i> or more numbers.
</p>

Obsid(s): <br>
<textarea name='obsid' cols=50 rows=5></textarea>
<br>

<br>
<p>
file listing one obsid per line can be used in addition to the above
input field. Wildcards may be used as well
</p>
Obsid File: 
<input type='hidden' name='MAX_FILE_SIZE' value=100>
<input type='File' name='obsid_upfile' size='40'>
<br>

<br>
<p>
Limit the type of observation below
</p>

<INPUT type='checkbox' name='sciins2' value='ACIS-S' checked>ACIS-S
<INPUT type='checkbox' name='sciins4' value='HRC-S'  checked>HRC-S
<INPUT type='checkbox' name='gratin1' value='HETG' checked>HETG
<INPUT type='checkbox' name='gratin2' value='LETG' checked>LETG


</div>
