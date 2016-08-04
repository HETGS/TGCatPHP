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
*/

function superMessage( message, stay ) {

stay = stay*1000;

var myWidth = 0; 
var myHeight = 0;
if( typeof( window.innerWidth ) == 'number' ) {
  //Non-IE
  myWidth = window.innerWidth;
  myHeight = window.innerHeight;
} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
  //IE 6+ in 'standards compliant mode'
  myWidth = document.documentElement.clientWidth;
  myHeight = document.documentElement.clientHeight;
} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
  //IE 4 compatible
  myWidth = document.body.clientWidth;
  myHeight = document.body.clientHeight;
}

winH = myHeight;

/* alert( 'winH is: ' + winH ); */

scrollx = window.scrollX || document.documentElement.scrollLeft;
scrolly = window.scrollY || document.documentElement.scrollTop;

/* alert( 'scrollX is: ' + scrollx +  '; scrollY is: ' + scrolly ); */

yposition = winH + scrolly - 235;
xposition = scrollx;

/* alert( 'yposition is: ' + yposition + '; xposition is: ' + xposition ); */

/* alertSize(); */

Tip( '<div><div style="position:absolute;left:60px;top:15px;padding:5px;width:190px;">'+ message + '</div><img src="image/BubbleTip.png"></div>',FIX,[xposition,yposition],BGCOLOR,'',BORDERWIDTH,0,ABOVE,true,STICKY,true,FADEIN,100,FADEOUT,3000,DURATION,stay,FOLLOWSCROLL,true );

}
