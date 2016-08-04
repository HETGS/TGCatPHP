/* 
Copyright (C) 2010 Massachusetts Institute of Technology 

This software was developed by the MIT Kavli Institute for
Astrophysics and Space Research under contract SV3-73016 from the
Smithsonian Institution.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA

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

