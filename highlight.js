/*
####################################################
####################################################
##
##  FILENAME: highlight.js
##
##  DESCRIPTION: 
##    Contains functions for adding and removing
##    highlighting in query results list etc. 
##    (See tgData.php and tgRequests.php.)
##
####################################################
####################################################
*/

var HIGHLIGHT_DOWN = 0;
var HIGHLIGHT_ARRAY = new Array();
var HIGHLIGHTER = 0;
var CURRENT_XSELECT;

browser = navigator.appName;
if ( browser == "Netscape" ){
	MOUSE_BUTTON=0;
}
else {
	MOUSE_BUTTON=1;
}

function load_highlight()
{
	var table = document.getElementById("dTable");
	var data_rows = table.getElementsByTagName("tr");
	for (i=0;i<data_rows.length;i++)
	{	
		var elem = data_rows[i];
		elem.onmousedown = highlight_down;
		elem.onmouseup   = highlight_up;
		elem.onmouseover = highlight_over;
	}
	var field = table.getElementsByTagName("input");	
	for (i = 0; i < field.length; i++ )
	{
		if ( field[i].checked == true ){
			do_toggle( field[i].value );
			do_toggle( field[i].value );
		}
	}	
	
}

function highlight_down(event)
{	
	try{
	  if ( event.which == null ){
	    var button=event.button;
	  }
	  else { 
	    var button=event.which;
	  }
	  var cell=event.target.parentNode;
	}
	catch(err){
	  var button=window.event.button;
	  var cell=window.event.srcElement;
	}
	if ( cell.nodeType == 3 ){
	  cell=cell.parentNode;
	}
	if ( button != 1 ){ return true; }
	cell.parentNode.onmouseup = highlight_up;
	var id = cell.id.split("_")[1];
	HIGHLIGHT_DOWN = 1;
	toggle_check( id );
	return false;
}

function highlight_over(event)
{
	if ( HIGHLIGHT_DOWN == 1 )
	{
		var cell = this;
		var id = cell.id.split("_")[1];
		toggle_check( id );
		HIGHLIGHTER = 1;
	}
	return false;
}

function highlight_up(event)
{
	HIGHLIGHT_DOWN = 0;
	HIGHLIGHT_ARRAY = [];
	HIGHLIGHTER = 0;
	return false;
}

function toggle_check( dsid )
{

	if ( dsid == HIGHLIGHT_ARRAY[HIGHLIGHT_ARRAY.length-1] )
	{
		return false;
	}	
	else if ( dsid == HIGHLIGHT_ARRAY[HIGHLIGHT_ARRAY.length-2] )
	{
		this_id = HIGHLIGHT_ARRAY.pop();
	}
	else
	{
		this_id = dsid;
		HIGHLIGHT_ARRAY.push( dsid );
	}
	/*alert( 'running check toggle on ' + dsid );*/
	do_toggle( this_id );
}

function do_toggle( id )
{
	var checkbox = document.getElementById( "ds_"+id);
	if ( checkbox.checked == false )
	{ 
		checkbox.checked = true;
		var elem = document.getElementById( "tr_"+id );
		//alert( elem.className );
		elem.className = elem.className + "_highlight";  
		//alert( elem.className );
	}
	else                            
	{ 
		checkbox.checked = false; 
		var elem = document.getElementById( "tr_"+id );
		/*alert( elem.className );*/
		elem.className = elem.className.replace("_highlight","");
	}
}

function check_toggle( id )
{
	var checkbox = document.getElementById( "ds_"+id);
	if ( checkbox.checked == true )
	{ 
		checkbox.checked = true;
		var elem = document.getElementById( "tr_"+id );
		elem.className = elem.className + "_highlight";  
	}
	else                            
	{ 
		checkbox.checked = false; 
		var elem = document.getElementById( "tr_"+id );
		elem.className = elem.className.replace("_highlight","");
	}

}

function check_all()
{	
	var table = document.getElementById("dTable");
	var field = table.getElementsByTagName("input");
	
	/*alert( field );*/
	for (i = 0; i < field.length; i++ )
	{
		do_toggle(field[i].value);
	}	
}

function xselect(rowname)
{
	if ( CURRENT_XSELECT ){
		var cur = document.getElementById("tr_"+CURRENT_XSELECT);
		cur.className = cur.className.replace( "_xselected","" );
	}
	var row = document.getElementById("tr_"+rowname);
	if ( row.className.match("_") ){
		row.className = row.className.replace("_","_xselected_");
	}
	else {
		row.className = row.className + "_xselected";
	}
	CURRENT_XSELECT=rowname;
}

if(!Array.indexOf){
  Array.prototype.indexOf = function(obj){
    for(var i=0; i<this.length; i++){
      if(this[i]==obj){
	return i;
      }
    }
    return -1;
  }
 }

function getNext(){
        myIndex = ALL_IDS_ORDERED.indexOf(CURRENT_XSELECT);
	if ( myIndex == ALL_IDS_ORDERED.length-1 ){
	    myIndex = -1;
	}
      	return ALL_IDS_ORDERED[myIndex+1];
}

function getPrev(){
       myIndex = ALL_IDS_ORDERED.indexOf(CURRENT_XSELECT);
       if ( myIndex == 0 ){ 
 	   myIndex = ALL_IDS_ORDERED.length;
       }
       return ALL_IDS_ORDERED[myIndex-1];
}
