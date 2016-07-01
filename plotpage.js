PLOTTED=false;

function setPlotted(){
	PLOTTED=true;
}

function verifyPlotted(){
	if ( ! PLOTTED ){
		superMessage( 'You must start a plotting session first ( select "Custom Plotting" -> "Open Plotter" )',3 );
		return false;
	}	
	else {
		return true;
	}
}
