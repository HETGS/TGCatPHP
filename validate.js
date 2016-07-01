/* 
validate will ensure that RA in the search for is
between appropriate values
*/

function validate()
{

	if ( ! document.submitCOORD )
	{
		return true;
  	}
	rastr = document.submitCOORD.RA.value
	decstr =  document.submitCOORD.DEC.value
	re = /-?[0-9]*/
	foundra = rastr.match(re)
	founddec = decstr.match(re)

	foundra = parseInt( foundra );
	founddec = parseInt( founddec );

	if ( ( ( foundra > 360) || ( foundra < 0 ) ) || ( ( founddec < -90 ) || ( founddec > 90 ) ) )
	{
		alert ("RA must be between 0 and 360, Dec between -90 and 90")
		return false;
	}
	else
	{	   
		return true
	}		

}	

