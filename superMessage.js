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
