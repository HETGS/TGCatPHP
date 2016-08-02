<h1> Name Search </h1>

<input type='hidden' name='queryType' value='NAME'>
   
<div class='search' style='text-align:left'>
<p>
       Search by object name. Identifier name is case insensitive. when not using SIMBAD, only the 
       official tgcat object keyword is matched (spaces are compacted automatically). 
       Wildcard % matches <i>zero</i> or more occurances of any character       
</p>

Target list
<br>
<textarea name='targ' cols=45 rows=6></textarea>
<br><br>

<p> 
A file containing one target per line can be searched in addition to
the above input field
</p>
Target File: 
<input type='hidden' name='MAX_FILE_SIZE' value=100>
<input type='File' name='source_upfile' size='40'>

<br><br>

<p>
Simbad can be use to resolve the above targets to coordinates
for a more flexible matching. Please note that using wildcards
in a simbad search may take some time
</p>
Use SIMBAD?
<INPUT type='checkbox' name='simbad' value='useSimbad'> 
rad: <INPUT TYPE='text' NAME='radius' MAXLENGTH='5' SIZE='5' value='2'>
Radius Units:
<SELECT NAME='radcho'>
<OPTION>arcmin
<OPTION>degrees
</SELECT>

<br><br>

<p>
If no exact match is found, but close matches are detected, these will be 
displayed instead. 
</p>
Turn this option off? <input type='checkbox' name='matches'>

</div>