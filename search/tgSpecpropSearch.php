<h1> Collated Spectral Properties Tables </h1>

<p>
Each extraction in <i>TGCat</i> includes a table of flux properties ("fprops.fits")---count rates,
   photon fluxes, or energy fluxes in  a number of bandpasses.
   These have been collated over all observations and formatted into
   FITS tables (five files, one for each grating, detector, and read mode combination)
   along with ancillary information such as the SIMBAD classification.
   These tables can be used to form spectral diagnostics, such as net line fluxes, line-to-continuum
   or hardness ratios, or line flux ratios.

   The tables and documentation can be downloaded here: 
<br>
<br>
<a href='archive/fprops_dist_data.tgz'> <bf>TGCat Collected Flux Properties</bf></a>.
</p>

<p>
For documentation only:
<br>
<br> 
<a href='archive/tgcat_fprops_file_spec.pdf'> <bf>Description of the TGCat Collected Flux Properties</bf></a>
</p>



<h1> Spectral Properties Search </h1>
<input type='hidden' name='queryType' value='SPECPROP'>
<div class='search'>

<?php
$spsz = 15;
$atoms = "Fe25,FeK,Ca19,Ar18,Ar17,S16,S15,Si14,Si13,Mg12,Mg11,Fe2x,Ne10,Ne9,Fe17,O8,O7,N7";
$atoms_op = join( "<option>",split( ",",$atoms ) );
?>

<p>
       During processing <i>TGCat</i> Obsids, there are a number of spectral properties extracted
       from the resulting products and tabulated for reference. This search allows a database
       query based on some of these properties. It may be beneficial in forming a search to
       reference the spectral properties trends table for all <i>TGCat</i> Observations 
       <a href='tgTrend.php?type=SPECPROP' target=_blank>here</a>.

       A search on broadband regions can be done below
</p>

<?php
print  "
       <table>
       <tr>
       <td>
       </td>
       <td> <i> min </i> </td>
       <td> <i> max </i> </td>
       </tr>
       <tr>
       <td> heg band count rate (cnts/s) </td>
       <td><input type=text name=min_hegb_rate size=$spsz></td>
       <td><input type=text name=max_hegb_rate size=$spsz></td>
       <tr>
       <tr>
       <td> meg band count rate (cnts/s) </td>
       <td><input type=text name=min_megb_rate size=$spsz></td>
       <td><input type=text name=max_megb_rate size=$spsz></td>
       <tr>
       <tr>
       <td> leg band count rate (cnts/s) </td>
       <td><input type=text name=min_legb_rate size=$spsz></td>
       <td><input type=text name=max_legb_rate size=$spsz></td>
       <tr>
       </table>
";
?>

<br>
<p>
Particular regions with typical lines are also extracted
automatically and indexed by atom. A description of these
regions can be found <a href='archive/tgcat_fprops_file_spec.pdf'>here</a>
</p>

<?php

print "
       <table>
       <tr>
       <td>
       atom
       </td>
       <td> <i> min </i> </td>
       <td> <i> max </i> </td>
       </tr>
       <tr>
       <td> <select name=atom ><option> $atoms_op </select>
       </td>
       <td><input type=text name=min_atom size=$spsz></td>
       <td><input type=text name=max_atom size=$spsz></td>
       <td> <select name=atom_scale >
              <option> count_rate 
              <option> photon_flux
              <option> energy_flux
            </select>
       </td>             
       <tr>
       </table>
";

?>

</div>
