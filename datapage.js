var SKIPCHECK=0;

function load (){
  load_highlight();
  togglers = document.getElementsByName( "togS" );
  for ( i=0; i<togglers.length; i++ ){
    /*alert( 'applying toggler func to toggler ' + togglers[i] );*/
    togglers[i].onclick = check_all;
	}
  /*
    update the downloader to use the javascript
    enabled routing after apply
  */
  jsindarr = document.getElementsByName("dlusedjs");
  for ( i=0; i<jsindarr.length; i++ ){
    jsindarr[i].value='1';	
  }
  slhref = document.getElementById("openColumnSelect");
  if ( slhref )
    {
      slhref.onclick = show_selector;
      slhref.href = "#";
    }
  sclose = document.getElementById("selector_close");
  if ( sclose )
    {
      document.getElementById("selector_close").href = "#";
      document.getElementById("selector_close").onclick = hide_selector;
    }
  dlhref = document.getElementById("openDownloadWindow" );
  if ( dlhref )
    {
      dlhref.onclick = show_downloader;
      dlhref.href = "#";
    }
  dclose = document.getElementById("downloader_close");
  if ( dclose )
    {
      document.getElementById("downloader_close").href = "#";
      document.getElementById("downloader_close").onclick = hide_downloader;
    }
  tagref = document.getElementById("openTagWindow");
  if ( tagref )
    {
      tagref.onclick = show_tagger;
      tagref.href = "#";
    }
  tagclose = document.getElementById("tagger_close");
  if ( tagclose )
    {
      document.getElementById("tagger_close").href = "#";
      document.getElementById("tagger_close").onclick = hide_tagger;
    }
  fref = document.getElementById("openFilterWindow");
  if ( fref )
    {
      fref.onclick = show_filter;
      fref.href = "#";
    }
  fclose = document.getElementById("openFilterWindow");
  if ( fref )
    {
      document.getElementById("filter_close").href = "#";
      document.getElementById("filter_close").onclick = hide_filter;
    }
  
  /* alert(document.getElementById("dlusedjs").value ); */
}

function show_selector() {
  document.getElementById("selbg").style.visibility = "visible";
  document.getElementById("selb").style.visibility = "visible";
  return false;
}
function hide_selector() {
  document.getElementById("selbg").style.visibility = "hidden";
  document.getElementById("selb").style.visibility = "hidden";
  return false;
}
function show_tagger() {
  SKIPCHECK=1;
  document.getElementById("tagbg").style.visibility = "visible";
  document.getElementById("tagb").style.visibility = "visible";
  return false;
}
function show_filter() {
  SKIPCHECK=1;
  document.getElementById("filterbg").style.visibility = "visible";
  document.getElementById("filterb").style.visibility = "visible";
  return false;
}
function hide_tagger() {
  document.getElementById("tagbg").style.visibility = "hidden";
  document.getElementById("tagb").style.visibility = "hidden";
  return false;
}
function hide_filter() {
  document.getElementById("filterbg").style.visibility = "hidden";
  document.getElementById("filterb").style.visibility = "hidden";
  return false;
}
function show_downloader() {
  ids = checkForm();
  if ( ids ){
    document.getElementById("dlbg").style.visibility = "visible";
    document.getElementById("dlb").style.visibility = "visible";
    document.getElementById("ids").value = ids;
    return false;
  }
  return false;
}
function hide_downloader() {
  document.getElementById("dlbg").style.visibility = "hidden";
  document.getElementById("dlb").style.visibility = "hidden";
  return false;
}

function checkForm(form) {
  go = 0;
  ids = "";
  mdsc = document.getElementsByName( "dsc[]" );
  for ( i=0; i<mdsc.length; i++ ){
    if ( mdsc[i].checked )
      {
	go = 1;
	ids = ids  + mdsc[i].value + ",";
      }
  }
  if ( ! go ) {
    superMessage( 'Please select at least one row', 1);
    return false;
  }
  ids = ids.substr( 0, ids.length-1 );
  return ids;
}	
function submitForm(form) {
  if ( ! SKIPCHECK ){
    c = checkForm(form);
  }
  else {
    c = 1;
  }
  if ( c ){
    return true;
  }	
  return false;
}

function fluxTip( id ){
	Tip( 'Flux Spectrum -<br><img src="png/tgcid_'+id+'_summary_flux_overview.png" height="200" width="350">', FADEIN,200,FADEOUT,200,DELAY,200,CLICKCLOSE,'true',BGCOLOR,'#FFFFFF',BORDERCOLOR,'#000000',FONTCOLOR,'#000000',ABOVE,'true' );
}

function vvTip( review, comment ){
	Tip( '<p style="width:300px;"><b>Review Status:</b> '+review+'<br><b>Comments:</b>'+comment+'</p>',FADEIN,200,FADEOUT,200,DELAY,200,CLICKCLOSE,'true',BGCOLOR,'#FFFFFF',BORDERCOLOR,'#000000',FONTCOLOR,'#000000',ABOVE,'true' );
}
	
function iTip( srcid ){
	Tip( '<img src="surveypng/tgSid_'+srcid+'_survey_panel.png"', WIDTH,660,FADEIN,200,FADEOUT,200,DELAY,200,CLICKCLOSE,'true',BGCOLOR,'#FFFFFF',BORDERCOLOR,'#000000',FONTCOLOR,'#000000',ABOVE,'true' );
}
