/*

The calling PHP script must set the following variable before this include
based on the return reciept from isis plotting script

vpMinX/vpMaxX  -  the viewport min and max X pixels
vpMinY/vpMaxY  -  the viewport min and max Y pixels
PstartX/PstopX - plot min and max X in plot units
PstartY/PstopY - plot min and max Y in plot units

make sure to keep the plot in a div parent of the id="plot"

should also check to see if mouse button is 1 for the square

*/
/*
var plot_container="plot";
var xminN = "xmin";
var xmaxN = "xmax";
var yminN = "ymin";
var ymaxN = "ymax";

var peE = document.getElementById(plot_container);
var xminE = document.getElementById(xminN);
var xmaxE = document.getElementById(xmaxN);
var yminE = document.getElementById(yminN);
var ymaxE = document.getElementById(ymaxN);
*/

ARROW_MOUSE_DOWN = 0;

function insert_arrows()
{

	var fields = {
	xmina:{f:"xmin",d:"xmax"},
	xmaxa:{f:"xmax",d:"xmin"},
	ymina:{f:"ymin",d:"ymax"},
	ymaxa:{f:"ymax",d:"ymin"},	
	}
	
	for ( var i in fields )
	{
		var ione = fields[i]["f"]

		var inhtml      = "<table class='plt_arrows'><tr><td> "
		inhtml = inhtml + "<img style='vertical-align:middle' "
		inhtml = inhtml + "src='image/arrow195_u_b.gif' id='a" + ione + "' "
		inhtml = inhtml + "height='20px' width='20px' border='0px' "  			
		inhtml = inhtml + "onmousedown='start_inc(" + '"'
		inhtml = inhtml + ione
		inhtml = inhtml + '"' + ");' onmouseup='stop_it();'> "
		inhtml = inhtml + "</td><td> "
		inhtml = inhtml + "<img style='vertical-align:middle' "
		inhtml = inhtml + "src='image/arrow195_d_b.gif'  id='b" + ione + "' "
		inhtml = inhtml + "height='20px' width='20px' border='0px' "  			
		inhtml = inhtml + "onmousedown='start_dec(" + '"'
		inhtml = inhtml + ione
		inhtml = inhtml + '"' + ");' onmouseup='stop_it();'> "
		inhtml = inhtml + "</td></tr></table>"

		document.getElementById(i).innerHTML = inhtml
	}
	
}

function start_inc( f )
{		

	ARROW_MOUSE_DOWN = 1
	
	CURRENT_ARROW_FOCUS = f

	inc_it()

}

function inc_it()
{
	if ( ARROW_MOUSE_DOWN )
	{
		window.setTimeout( "inc_it()", 100 )
		increment( CURRENT_ARROW_FOCUS )	
	}
}

function start_dec( f )
{	
	ARROW_MOUSE_DOWN = 1
	
	CURRENT_ARROW_FOCUS = f

	dec_it()

}

function dec_it()
{
	if ( ARROW_MOUSE_DOWN )
	{
		window.setTimeout( "dec_it()", 100 )
		decrement( CURRENT_ARROW_FOCUS )	
	}
}

function stop_it()
{
	ARROW_MOUSE_DOWN = 0
}

function increment( f )
{
	var i = document.getElementById(f).value
	i = Number(i) + 0.1
	i = Math.round(i*100)/100
	document.getElementById(f).value = i
}
	
function decrement( f )
{
	var i = document.getElementById(f).value
	i = Number(i) - 0.1
	i = Math.round(i*100)/100
	document.getElementById(f).value = i
}

/*
function mouseDown(posx,posy)
{

  if ( ! isdown )
    {
    d2.clear();
    myx = posx;
    myy = posy;
    isdown = 1;	
    }
    
}

function mouseOut()
{
  return false;
}

function mouseUp(posx,posy)
{
    d2.drawRect(myx,myy,posx-myx,posy-myy);
    d2.paint(); 
    isdown = 0;  
}
function mouseMove(posx,posy)
{
    if ( isdown )
    {
        d2.clear();
        d2.setColor("#00FF00");
	L = getLimits(myx,myy,posx,posy);
	d2.drawRect(L[0],L[1],L[2],L[3]);
	d2.paint();

	update_fields(L)
	document.pos.posx.value = posx;
	document.pos.posy.value = posy;
    }
    return false;
}

function getLimits(x,y,x2,y2)
{
  if ( x > maxx ) { x = maxx }
  if ( x2 > maxx ) { x2 = maxx }
  if ( x < minx ) { x = minx }
  if ( x2 < minx ) { x2 = minx }
  if ( y > maxy ) { y = maxy }
  if ( y2 > maxy ) { y2 = maxy }
  if ( y < miny ) { y = miny }
  if ( y2 < miny ) { y2 = miny }

  w = Math.abs(x2 - x);
  h = Math.abs(y2 - y);
  sx = x;
  sy = y;
  if ( x2 < x ) { sx = x2 }
  if ( y2 < y ) { sy = y2 }

  return([sx,sy,w,h]);

}
*/
/*

update the limit fields. xFactor is ( plot unit/pixel ) or:

abs(PstartX - PstopX)/(pixelStart - pixelStop)

where PstartX is the left most plot position in plotunits and
pixelStart is the left moset position in pixels


function update_fields( pa )
{
	xminE.value = xFactor*(PstartX + (pa[0]-left) )
	xmaxE.value = xFactor*(PstartX + ( (pa[0]-left) + pa[2] ) )
	yminE.value = yFactor*(PstartY + (pa[1]-top) )
	ymaxE.value = yFactor*(PstartY + ( (pa[1]-top) + pa[3] ) )
}

pe = peE
top = 0
while ( a )
  {
    pe = top + pe.offsetTop
    pe = pe.offsetParent
  }
pe = peE
left = 0
while ( a )
  {
    pe = left + pe.offsetLeft
    pe = pe.offsetParent
  }

minx = vpMinX + left
maxx = vpMaxX + left
miny = vpMinY + top
maxy = xpMaxY + top
xFactor = Math.abs(PstartX - PstopX)/(Math.abs(vpMinX-vpMaxX))
yFactor = Math.abs(PstartY - PstopY)/(Math.abs(vpMinY-vpMayY))

var d2 = new jsGraphics(plot_container);
*/