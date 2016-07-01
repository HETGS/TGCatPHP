/*
####################################################
####################################################
##
##  FILENAME: formchanges.js
##
##  DESCRIPTION: 
##    Contains functions for controlling form 
##    for plotting re saved and default parameters
##    (see tgGUI.php).
##
##  AUTHOR: Emma Reishus
##
####################################################
####################################################
*/

//
// disables/enables use saved button based on avaliability
//
function addSave(){
      var form = document.forms['plt_opt'];
      var element = document.getElementById('chngtoSaved');
      if(form.elements['save'].checked == true) {
	element.disabled = '';	
      }
      else if(! getCookie('tgPlotParams')) {
        element.disabled = 'disabled';
      }
    }    

//
// loads the user's saved parameters into the form
//
function setSaved() { 
      var form = document.forms['plt_opt'];
      var c = getCookie('tgPlotParams').split(',');
      if(! c ) { 
	alert("No saved parameters found!");
	return; 
      }
      var plot = form.elements['plot'];
      for(var i = 0; i < plot.length; i++) {
	if( plot.options[i].value == c[0] ) {
	  plot.selectedIndex = i;
	}
      }
      var xunit = form.elements['xunit'];
      for(var i = 0; i < xunit.length; i++) {
	if( xunit.options[i].value == c[1] ) {
	  xunit.selectedIndex = i;
	}
      }      
      var yunit = form.elements['yunit'];     
      for(var i = 0; i < yunit.length; i++) {
	if( yunit.options[i].value == c[2] ) {
	  yunit.selectedIndex = i;
	}
      }      
      if( c[3] ) { form.elements['xlog'].checked = true; }
      else { form.elements['xlog'].checked = false; }
      if( c[4] ) { form.elements['ylog'].checked = true; }
      else { form.elements['ylog'].checked = false; }
      if( c[5] ) { form.elements['errbar'].checked = true; }
      else { form.elements['errbar'].checked = false; }
      if(form.elements['MEG+1']) { 
	if( c[6] ) { form.elements['MEG+1'].checked = true; }
	else { form.elements['MEG+1'].checked = false; }
      }
      if(form.elements['MEG-1']) { 
	if( c[7] ) { form.elements['MEG-1'].checked = true; }
	else { form.elements['MEG-1'].checked = false; }
      }      
      if(form.elements['HEG+1']) { 
	if( c[8] ) { form.elements['HEG+1'].checked = true; }
	else { form.elements['HEG+1'].checked = false; }
      }  
      if(form.elements['HEG-1']) { 
	if( c[9] ) { form.elements['HEG-1'].checked = true; }
	else { form.elements['HEG-1'].checked = false; }
      }      
      if(form.elements['LEG+1']) { 
	if( c[10] ) { form.elements['LEG+1'].checked = true; }
	else { form.elements['LEG+1'].checked = false; }
      }  
      if(form.elements['LEG-1']) { 
	if( c[11] ) { form.elements['LEG-1'].checked = true; }
	else { form.elements['LEG-1'].checked = false; }
      }
      if( (c[10] || c[11]) && form.elements['MEG+1'] ) { // saved LEG but now HEG
	form.elements['MEG+1'].checked = true;
	form.elements['MEG-1'].checked = true;
	form.elements['HEG+1'].checked = false;
	form.elements['HEG-1'].checked = false;
      }
      if( (c[6] || c[7] || c[8] || c[9]) && form.elements['LEG+1'] ) { // saved HEG but now LEG
	form.elements['LEG+1'].checked = true;
	form.elements['LEG-1'].checked = true;
      }
      if( c[12] ) { form.elements['combine'].checked = true; }
      else { form.elements['combine'].checked = false; }
      form.elements['xmin'].value = c[13];
      form.elements['xmax'].value = c[14];
      form.elements['ymin'].value = c[15];
      form.elements['ymax'].value = c[16];
      if( c[17] ) { form.elements['bin'].checked = true; }
      else { form.elements['bin'].checked = false; }
      form.elements['bin1'].value = c[18];
      form.elements['bin2'].value = c[19];

      if( c[20] ) { form.elements['H-like'].checked = true; } 
      else { form.elements['H-like'].checked = false; }
      if( c[21] ) { form.elements['He-like'].checked = true; } 
      else { form.elements['He-like'].checked = false; } 
      if( c[22] ) { form.elements['Fe'].checked = true; } 
      else { form.elements['Fe'].checked = false; } 
      form.elements['shift'].value = c[23]; 
    }

//
// loads default parameters into the form
//
function resetDefaults() {
      var form = document.forms['plt_opt'];
      var elems = form.elements;
      var i = 0;
      for( i=0 ; i< elems.length; i++) {
	if( elems[i].type == "checkbox" ) {
		elems[i].checked=true;
	}
	if( elems[i].type == "text"  ) {
		elems[i].value = "";
	}
      }
      if( form.elements['HEG+1'] ) { 
	form.elements['HEG+1'].checked = false; 
	form.elements['HEG-1'].checked = false; 
      }
      form.elements['plot'].selectedIndex = 0;
      form.elements['xunit'].selectedIndex = 0;
      form.elements['yunit'].selectedIndex = 0;
      form.elements['bin1'].value = "2";
      form.elements['bin2'].value = "4";
      form.elements['save'].checked = false;
      form.elements['H-like'].checked = false;
      form.elements['He-like'].checked = false;
      form.elements['Fe'].checked = false;
      form.elements['shift'].value = "0.0";      
    } 

//
// gets cookie of name set from php
//
function getCookie(name) {  
      var value = document.cookie;
      var start = value.indexOf(" " + name + "=");
      if (start == -1) { start = value.indexOf(name + "="); }
      if (start == -1) { value = null; }
      else {
	start = value.indexOf("=", start) + 1;
	var end = value.indexOf(";", start);
	if (end == -1) { end = value.length; }
	value = unescape(value.substring(start,end));
      }
      return value;
    }  

        
