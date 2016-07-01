implements("isis_fancy_plots");

% Most recent update: Oct. 10, 2008

% Big block of plotting functions, to allow you to do all sorts of 
% XSPEC type plotting, and then some.  Plot parameters can be input as 
% either a structure, or pairs of associative keywords and parameter
% values, or as qualifiers.

% You'll note that the code below is uglier than it needs to be, and 
% could probably be reduced in length by a fair bit.

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

% Public Functions in This File.  Usage message when function is called
% without arguments.

% Plot_Unit        : My variation on ISIS's plot_unit.  Same choices as
%                    in ISIS, but this saves a variable that the below 
%                    routines will use.
% plot_counts      : Plot background subtracted data as counts/bin,
%                    with choices of three kinds of residuals, or none 
%                    at all.
% plot_data        : Main plotting routine, to plot background sub-
%                    tracted data in detector counts space.  Counts
%                    per second per Plot_Unit, with choices of three
%                    kinds of residuals, or none at all.
% plot_fit_model   : An hplot of just the background subtracted model.
% plot_residuals   : A plot of just the data residuals
% plot_unfold      : A plot of the unfolded spectra, or energy/freq./
%                    wavelength times unfolded spectra.
% plotxy           : Simple x-y (o)plots with error bars.
% pg_color         : Make a few nice colors for pgplot
% pg_info          : Writes out useful info about plotting choices.
% msg              : A message function used a lot below.
% open_print       : A version of open_plot that invokes pg_color first
% close_print      : A version of close_plot that will then display the 
%                    hardcopy via a chosen system utility (e.g., gv)
% nice_size        : An autoset of resize/set_outer_viewport that works
%                    well for a "typical" printed plot. Subject to 
%                    change without any notification!
% nice_width       : An autoset of plot widths that works well for 
%                    papers and presentations (especially on Macs)
% set_plot_labels  : If one first sets the pg_font variable, and then 
%                    runs set_plot_labels, the font style can be changed
%                    for all axis labels.
% write_plot       : Write the data from the plot functions to ASCII
%                    files
% sov              : An abbreviation of set_outer_viewport

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

% Public Variables That Affect This File.  

% d_width, m_width, r_width : Line widths for data, model, residuals
% de_width, me_width        : Line widths for data & model error bars
% user_unit_x, user_unit_y  : Strings with user defined labels
% xnolabel, ynolabel        : Turn plotting of x/y labels off if != 0 
% ebar_term_length_x        : Length of little caps on x error bars
% ebar_term_length_y        : Length of little caps on y error bars
% z_not_v                   : X-axis is redshift, not velocity, if !=0
% pg_font                   : pgplot label font used (default \\fr)
% pd_mean_not_sum           : If !=0 (default=0), plot_data for combined
%                             data is total counts/mean exposure (not
%                             summed exposure)
% use_con_flux              : If !=0 (default=0), use convolved model
%                             flux in plot_unfold.
% no_reset                  : If !=0 (default=0), don't reset plot, so
%                             multiplots can be done with these routines

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

% Private Functions in This File - Used for the Plotting Internals 
%
% set_plot_defaults     : This sets the plot defaults via internal
%                         variables listed further below.
% set_plot_parms_struct : Ultimately all plot choices are passed via
%                         a structure, using this subroutine
% set_plot_parms_assoc  : For those who wish to pass plot arguments via
%                         associative array keywords
% set_plot_parms        : Parse the input to the above routines
% multiplotit           : Set up two panes for residual plotting
% set_my_width          : Set  plotting widths via my public width
%                         variables -- d_width, m_width, r_width
% make_data             : Create the data to be plotted
% make_counts           : Create the data to be plotted as counts/bin
% make_flux             : Create the flux corrected data to be plotted
% res_set               : Begin set-up of the residuals plot
% xerplot               : Put a bar at 0 or 1 across the residuals plot 
% datplot_err           : Plot the data error bars
% resplot_err           : Plot the residuals error bars
% hp_loop               : Sets index ranges for hplot so disjoint bits
%                       : of data, model, get plotted separately
% hp_loopit             : Does the actual hplotting for disjoint bits
% resplot               : Driver for the residual plots
% p_range_label         : Set up the plot ranges and labels
% vaxis                 : Make a redshift or velocity x-axis
% p_reset               : Reset the plot defaults
% write_plot_head       : Makes the header for ASCII files containing
%                         data from the plot functions

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
%
% Awm Set Isis_Reverse_Channels to 1
%
private variable Isis_Reverse_Channels = 1;

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
% My own public variables to allow me to set data, model, and residual
% line widths, etc., in my custom plotting functions

public variable d_width=1, m_width=1, r_width=1;
public variable de_width=1, re_width=1, no_reset=0;
public variable xnolabel = 0, ynolabel = 0;
public variable user_unit_x = "X", user_unit_y = "Y";
public variable user_label_x = "", user_label_y = "";
public variable user_scale_x = 1., user_scale_y = 1;
public variable ebar_term_length_x = 0, ebar_term_length_y = 0;
public variable z_not_v=0;
public variable pg_font="\\fr";
public variable pd_mean_not_sum = 0;
public variable use_con_flux = 0;

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

public define msg(str_array)
{
   () = printf("\n");
   foreach(str_array)
   {
      variable str = ();
      () = printf(" %s\n", str);
   }
   () = printf("\n");
   return;
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


static variable pd = struct{ type, index, file, xaxis, yaxis, raxis, 
   dlo, dhi, dval, derr, mlo, mhi, mval, res, res_m, res_p };

private variable parm_s = 
                    struct{ dcol, decol, mcol, rcol, recol, dsym, rsym,
                            yrng, xrng, res, power, oplt, bkg, mcomp };

private variable parms = ["dcol","decol", "mcol","rcol","recol", "dsym",
                          "rsym","yrng","xrng","res","power","oplt",
                          "bkg","mcomp"];

private variable indx, dcol, decol, mcol, rcol, recol, dsym, rsym, 
                yrng, xrng, res, power, oplt, bkg, mcomp;

private variable mcol_df, res_df, power_df, mcomp_df, col_df, style_df, 
                 oplt_df, bkg_df;

private variable parm_a = Assoc_Type[];

public variable popt = @parm_s; 

private variable plopt=NULL;

private variable refplt = [&plot, &oplot];
private variable hrefplt = [&hplot, &ohplot];
private variable slangref;

private variable s_unit = "kev", x_unit="", vzero=NULL;
private variable zlo, zhi, clight=2.99792458e5;
private variable s_scl = Assoc_Type[Float_Type];
private variable y_scl = Assoc_Type[Float_Type];

s_scl["ev"]=1.e-3; s_scl["kev"]=1.; s_scl["mev"]=1.e3; 
  s_scl["gev"]=1.e6; s_scl["tev"]=1.e9;
s_scl["hz"]=4.118e-18; s_scl["khz"]=4.118e-15; 
  s_scl["mhz"]=4.118e-12; s_scl["ghz"]=4.118e-9;
s_scl["angstrom"]=1.; s_scl["a"]=1.,s_scl["nm"]=10.;  s_scl["um"]=1.e4;
  s_scl["mm"]=1.e7; s_scl["cm"]=1.e8; s_scl["m"]=1.e10;
s_scl["psd_leahy"]=1.,s_scl["psd_rms"]=1.;
s_scl["user_a"]=user_scale_x; s_scl["user_kev"]=user_scale_x;

y_scl["ev"]=1.; y_scl["kev"]=1.; y_scl["mev"]=1.; 
  y_scl["gev"]=1.; y_scl["tev"]=1.;
y_scl["hz"]=1.; y_scl["khz"]=1., y_scl["mhz"]=1.; y_scl["ghz"]=1.;
y_scl["angstrom"]=1.; y_scl["a"]=1.,y_scl["nm"]=1.; y_scl["um"]=1.;
  y_scl["mm"]=1.; y_scl["cm"]=1.; y_scl["m"]=1.;
y_scl["psd_leahy"]=1.,y_scl["psd_rms"]=1.;
y_scl["user_a"]=user_scale_y; y_scl["user_kev"]=user_scale_y;

private variable ssk, ssw, ssh, vlbl, redlbl;

private variable ak = Assoc_Type[String_Type], 
                 sk=Assoc_Type[String_Type], lbl;

private variable lk = ["ev","kev","mev","gev","tev",
                      "hz","khz","mhz","ghz",
                      "psd_leahy","psd_rms",
                      "angstrom","a","nm","um","mm","cm","m"];

ak["ev"] = "eV"; ak["kev"] = "keV"; ak["mev"] = "MeV"; 
ak["gev"] = "GeV"; ak["tev"] = "TeV";
ak["hz"] = "Hz"; ak["khz"] = "kHz"; ak["mhz"] = "MHz"; ak["ghz"] = "GHz";
ak["angstrom"] = "\\A"; ak["a"] = "\\A"; ak["nm"] = "nm";
ak["um"] = "\\gmm"; ak["mm"] = "mm"; ak["cm"] = "cm"; ak["m"] = "m";
ak["psd_leahy"]= "Hz"; ak["psd_rms"]= "Hz";

private variable xlbl = Assoc_Type[String_Type];
private variable ylbl = Assoc_Type[Array_Type];
private variable rlbl;

public define set_plot_labels()
{
   ssk = pg_font+" Energy ";
   ssw = pg_font+" Wavelength ";
   ssh = pg_font+" Frequency ";

   sk["ev"] = ssk; sk["kev"] = ssk; sk["mev"] = ssk; 
     sk["gev"] = ssk; sk["tev"] = ssk;
   sk["hz"] = ssh; sk["khz"] = ssh; sk["mhz"] = ssh; sk["ghz"] = ssh;
   sk["angstrom"] = ssw; sk["a"] = ssw; sk["nm"] = ssw;
     sk["um"] = ssw; sk["mm"] = ssw; sk["cm"] = ssw; sk["m"] = ssw;
   sk["psd_leahy"]= ssh; sk["psd_rms"]= ssh;

   foreach(lk)
   {
      lbl = ();
      xlbl[lbl] = sk[lbl]+"("+ak[lbl]+")";
      ylbl[lbl] = [pg_font+["Counts s\\u-1\\d "+ak[lbl]+"\\u-1\\d",
                   ak[lbl]+"\\u-1\\d Photons cm\\u-2\\d s\\u-1\\d "
                          +ak[lbl]+"\\u-1\\d",
                   "Photons cm\\u-2\\d s\\u-1\\d "+ak[lbl]+"\\u-1\\d",
                   ak[lbl]+" Photons cm\\u-2\\d s\\u-1\\d "+ak[lbl]+"\\u-1\\d",
                   ak[lbl]+"\\u2\\d Photons cm\\u-2\\d s\\u-1\\d "
                          +ak[lbl]+"\\u-1\\d",
                   "Counts/bin/"+ak[lbl],
                   "Counts/bin",
                   ak[lbl]+" Counts/bin",
                   ak[lbl]+"\\u2\\d Counts/bin"],
                   "photons"];
   }

   ylbl["psd_leahy"][5] = pg_font+" Power (Leahy)/f";
   ylbl["psd_rms"][5] = pg_font+" Power/f (RMS\\u2\\d/Hz\\u2\\d)";
   ylbl["psd_leahy"][6] = pg_font+" Power (Leahy)";
   ylbl["psd_rms"][6] = pg_font+" Power (RMS\\u2\\d/Hz)";
   ylbl["psd_leahy"][7] = pg_font+" f \\x Power (Leahy)";
   ylbl["psd_rms"][7] = pg_font+" f \\x Power (RMS\\u2\\d)";
   ylbl["psd_leahy"][8] = pg_font+" f\\u2\\d \\x Power (Leahy)";
   ylbl["psd_rms"][8] = pg_font+" f\\u2\\d \\x Power (RMS\\u2\\d \\. Hz)";
   ylbl["psd_rms"][9] = "psd";
   ylbl["psd_leahy"][9] = "psd";

   rlbl = [pg_font+"\\gx",pg_font+"\\gx\\u2",pg_font+"Ratio"];
   rlbl = [rlbl,rlbl];

   vlbl = pg_font+" Velocity  (km s\\u-1\\d)";
   redlbl = pg_font+" Redshift  (z)";
}

set_plot_labels();

public define new_plot_labels()
{
   variable str,lbl,xlbls,ylbls,ly,rlbls,lr,nvlbl,nredlbl;

   str=[" new_plot_labels(lbl;xlbl=string,ylbl=[string,],...);","",
   " Changes the default labels for plot_counts, plot_data, plot_unfold",
   " for the different units set by Plot_Unit. pg_font public variable will",
   " be *prepended* to all inputs.  Works with qualifiers only!!!",
   " Only inputs to be changed are required.  Use set_plot_labels() to",
   " restore defaults.","",
   " Inputs:","",
   "   lbl    : angstrom,a,nm,um,cm,mm,m,ev,kev,mev,gev,tev,hz,khz,mhz,",
   "            ghz,user_kev,user_a,psd_leahy, or psd_rms",
   "            Note: ergs, watts, mjy are set via user_kev or user_a",
   "   xlbl   : String with new X-axis label",
   "   ylbl   : String *array* (up to 9 elements) with new Y-axis labels.",
   "            Order of the array *must* be: ",
   "             [plot_data, plot_unfold(power=0->3),plot_counts(power=0->3)]",
   "   reslbl : String *array* (up to 3 elements) with new Y-axis labels for",
   "            residuals.  Order of the the array *must* be: ",
   "             [chi, chi^2, ratio]",
   "   vlbl   : String with new Doppler velocity X-axis label",
   "   redlbl : String with new Redshift X-axis label"];

   switch(_NARGS)
   {
      case 1:
      lbl = ();
      lbl = strlow(lbl);
      xlbls=qualifier("xlbl",NULL);      
      ylbls=qualifier("ylbl",NULL);   
      rlbls=qualifier("reslbl",NULL);
      nvlbl=qualifier("vlbl",NULL);      
      nredlbl=qualifier("redlbl",NULL);  
   }
   {
      msg(str);
      return;
   }

   if(xlbls != NULL)
   {
      xlbls=string([xlbls][0]); 
      xlbl[lbl] = pg_font+xlbls;
   }

   if(ylbls != NULL)
   {
      ylbls = [ylbls];
      ly = length(ylbls);
      if(ly>9){ ly = 9; }   

      ylbl[lbl][[0:ly-1]] = pg_font+ylbls[[0:ly-1]];
   }

   if(rlbls != NULL)
   {
      rlbls = [rlbls];
      lr = length(rlbls);
      if(lr>3){ lr = 3; }   

      rlbl[[0:lr-1]] = pg_font+rlbls[[0:lr-1]];
      rlbl[[4:4+lr-1]] = pg_font+rlbls[[0:lr-1]];
   }

   if(nvlbl != NULL)
   {
      nvlbl=string([nvlbl][0]); 
      vlbl = pg_font+nvlbl;
   }

   if(nredlbl != NULL)
   {
      nredlbl=string([nredlbl][0]); 
      redlbl = pg_font+nredlbl;
   }
}


style_df = 4;  % Default symbol is a circle, if current default is a dot
mcol_df = 2;   % Default model color red
res_df = 0;    % Default residual is none at all!
power_df = 1;  % Default unfolded spectrum is Photons/cm^2/sec/Unit
oplt_df = 0;   % Default is to erase the old plot
bkg_df = 0;    % Default is to not plot the background
mcomp_df = 0;  % Default is don't plot model components

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

private define set_plot_defaults(indx_ln)
{
   plopt = get_plot_options;
   col_df = plopt.color;
   if(plopt.point_style==1){ style_df = plopt.point_style; }
   bkg = Integer_Type[indx_ln]; bkg[*] = bkg_df;
   dcol = Integer_Type[indx_ln]; dcol[*] = col_df;
   decol = Integer_Type[indx_ln]; decol[*] = col_df;
   mcol = Integer_Type[indx_ln]; 
   if( get_fit_fun() != NULL and get_fit_fun() != "bin_width(1)" )
   {
      mcol[*] = mcol_df;
   }
   else
   {
      mcol[*] = 0;
   }
   rcol = Integer_Type[indx_ln]; rcol[*] = col_df;
   recol = Integer_Type[indx_ln]; recol[*] = col_df;
   dsym = Integer_Type[indx_ln]; dsym[*] = style_df;
   rsym = Integer_Type[indx_ln]; rsym[*] = style_df;
   yrng = [NULL];
   xrng = [NULL];
   res = [res_df];
   power = [power_df];
   oplt = [oplt_df];
   mcomp = [mcomp_df];

   parm_s.dcol = dcol;
   parm_s.decol = decol;
   parm_s.mcol = mcol;
   parm_s.rcol = rcol;
   parm_s.recol = recol;
   parm_s.dsym = dsym;
   parm_s.rsym = rsym;
   parm_s.yrng = yrng;
   parm_s.xrng = xrng;
   parm_s.res = res;
   parm_s.power = power;
   parm_s.oplt = oplt;
   parm_s.bkg = bkg;
   parm_s.mcomp = mcomp;

   parm_a["dcol"] = dcol;
   parm_a["decol"] = decol;
   parm_a["mcol"] = mcol;
   parm_a["rcol"] = rcol;
   parm_a["recol"] = recol;
   parm_a["dsym"] = dsym;
   parm_a["rsym"] = rsym;
   parm_a["yrng"] = yrng;
   parm_a["xrng"] = xrng;
   parm_a["res"] = res;
   parm_a["power"] = power;
   parm_a["oplt"] = oplt;
   parm_a["bkg"] = bkg;
   parm_a["mcomp"] = mcomp;
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

% The below, in fact, is the way all parameters will ultimately be set,
% and hence has all the error checking.

private define set_plot_parms_struct( pstruct, indx_ln )
{
   variable list, lparm, parmde=0, parmre=0;

   foreach(parms)
   {
      list = ();
      if(qualifier_exists(list))
      {
         set_struct_field(pstruct,list,qualifier(list,NULL));
      }
      if(struct_field_exists( pstruct, list ))
      {
         if(typeof(get_struct_field( pstruct, list )) != Null_Type)
         {
            if(list=="xrng" or list=="yrng")
            {
               set_struct_field(parm_s,list,get_struct_field(pstruct,list));
            }
            else
            {
               set_struct_field(parm_s,list,[get_struct_field(pstruct,list)]);
            }
            if(list=="decol") { parmde=1; }
            if(list=="recol") { parmre=1; }
         }
      }
   }

   % Transfer over to the variables

   lparm = min( [ length(parm_s.dcol), indx_ln ] );
   dcol[[0:lparm-1]] = parm_s.dcol[[0:lparm-1]];

   decol = @dcol;   % Default error bar colors to data
   if( parmde == 1 )
   {
      lparm = min( [ length(parm_s.decol), indx_ln ] );
      decol[[0:lparm-1]] = parm_s.decol[[0:lparm-1]];
   }

   lparm = min( [ length(parm_s.mcol), indx_ln ] );
   mcol[[0:lparm-1]] = parm_s.mcol[[0:lparm-1]];

   lparm = min( [ length(parm_s.rcol), indx_ln ] );
   rcol[[0:lparm-1]] = parm_s.rcol[[0:lparm-1]];

   recol = @rcol;   % Default error bar colors to residuals
   if( parmre == 1 )
   {
      lparm = min( [ length(parm_s.recol), indx_ln ] );
      recol[[0:lparm-1]] = parm_s.recol[[0:lparm-1]];
   }

   lparm = min( [ length(parm_s.dsym), indx_ln ] );
   dsym[[0:lparm-1]] = parm_s.dsym[[0:lparm-1]];

   lparm = min( [ length(parm_s.rsym), indx_ln ] );
   rsym[[0:lparm-1]] = parm_s.rsym[[0:lparm-1]];

   lparm = min( [ length(parm_s.bkg), indx_ln ] );
   bkg[[0:lparm-1]] = parm_s.bkg[[0:lparm-1]];

   yrng = parm_s.yrng;
   xrng = parm_s.xrng;
   res = parm_s.res[0]; 
   power = parm_s.power[0];
   mcomp = parm_s.mcomp[0]; 
   oplt = parm_s.oplt[0];

   % Defaults for screw ups on input

   if( power !=0 and power != 1 and power != 2 and power !=3 )
   {
      power = power_df;
   }
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

private define set_plot_parms_assoc( args, indx_ln )
{
   variable largs = length(args);
   variable lkeys = (largs-1)/2;
   variable i,key, keyde=0, keyre=0;
   
   for(i=0; i<=lkeys-1; i++)
   {
      key = args[2*i+1].value;

      if(key=="decol") { keyde=1; }
      if(key=="recol") { keyre=1; }

      if(assoc_key_exists( parm_a, key ))
      {  if(key == "yrng" or key == "xrng")
         {
            parm_a[key] = args[2*i+2].value;
         }
         else
         {
            parm_a[key] = [args[2*i+2].value];
         }
      }
   }

   % Transfer over to the variables

   parm_s.dcol = parm_a["dcol"];
   if(keyde)
   {
      parm_s.decol = parm_a["decol"];
   }
   else
   {
      parm_s.decol = @parm_s.dcol;
   }
   parm_s.mcol = parm_a["mcol"];
   parm_s.rcol = parm_a["rcol"];
   if(keyre)
   {
      parm_s.recol = parm_a["recol"];
   }
   else
   {
      parm_s.recol = @parm_s.rcol;
   }
   parm_s.dsym = parm_a["dsym"];
   parm_s.rsym = parm_a["rsym"];
   parm_s.yrng = parm_a["yrng"];
   parm_s.xrng = parm_a["xrng"];
   parm_s.res = parm_a["res"]; 
   parm_s.power = parm_a["power"]; 
   parm_s.mcomp = parm_a["mcomp"]; 
   parm_s.oplt = parm_a["oplt"];
   parm_s.bkg = parm_a["bkg"];

   set_plot_parms_struct(parm_s,indx_ln);
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

private define set_plot_parms(nargs,args,indx_ln)
{
   if(nargs > 1)
   {
      if(typeof(args[1].value) == String_Type)
      {
         % Presume it is an associative array argument list

         set_plot_parms_assoc(args,indx_ln);
      }
      else if(is_struct_type(args[1].value))
      {
         % Presume arguments are passed in a structure

         set_plot_parms_struct(args[1].value,indx_ln);
      }
   }
   else
   {
      set_plot_parms_struct(parm_s,indx_ln;;__qualifiers()); % Set defaults
   }
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

private define pd_set(li,indx,rval)
{
   variable pfield, lindx, i, a;

   lindx = length(indx);
   variable list=[ "index", "file", "dlo", "dhi", "dval", 
                   "derr", "mlo", "mhi", "mval"];
   
   foreach pfield (list)
   {
      set_struct_field( pd, pfield, Array_Type[li] );
   }

   pd.file[0] = String_Type[lindx];
   pd.xaxis = "NONE";
   pd.yaxis = "NONE";
   pd.raxis = "NONE";

   pd.res = NULL;
   pd.res_m = NULL;
   pd.res_p = NULL;

   if(rval>=1 and rval<=3)
   {
      pd.res = Array_Type[lindx];
      pd.res_m = Array_Type[lindx];
      pd.res_p = Array_Type[lindx];
   }
   else if(rval>=4 and rval<=6)
   {
      pd.res = Array_Type[1];
      pd.res_m = Array_Type[1];
      pd.res_p = Array_Type[1];
   }

   i=0;
   loop(li)
   {
      if(li==1 and lindx > li)
      {
         pd.index[0] = indx;
         loop(lindx)
         {
            a = get_data_info(indx[i]);
            pd.file[0][i] = a.file+"/part="+string(a.part)
                                     +"/order="+string(a.order);
            i++;
         }
      }
      else
      {
         pd.index[i] = [indx[i]];
         a = get_data_info(indx[i]);
         pd.file[i] = [a.file+"/part="+string(a.part)
                                  +"/order="+string(a.order)];
         i++;
      }
   }
}

%%%%%%%%%%%%%%%%%%%%%%%%

private define p_reset()
{
   set_plot_options(plopt);
   if(no_reset==0)
   {
      multiplot(1);
   }
}  

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

private define multiplotit(res)
{
   if( res != 0 )
   {
      multiplot([3,1]);
   }
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

private define vaxis(bin_lo,bin_hi)
{
   if( length(where(["ev","kev","mev","gev","tev",
                     "hz","khz","mhz","ghz","user_kev",
                     "psd_leahy","psd_rms"]==s_unit))==1 or
       length(where(["ev","kev","mev","gev","tev",
                     "hz","khz","mhz","ghz"]==x_unit))==1 )
   {
      zhi = (vzero-bin_lo)/bin_lo;
      zlo = (vzero-bin_hi)/bin_hi;

      if(z_not_v==0)
      {
         bin_lo = ((zlo+1)^2-1)/((zlo+1)^2+1)*clight;
         bin_hi = ((zhi+1)^2-1)/((zhi+1)^2+1)*clight;
         xlabel(vlbl);
      }
      else
      {
         bin_lo=zlo;
         bin_hi=zhi;
         xlabel(redlbl);
      }
   }
   else
   {
      zlo = (bin_lo-vzero)/vzero;
      zhi = (bin_hi-vzero)/vzero;

      if(z_not_v==0)
      {
         bin_lo = ((zlo+1)^2-1)/((zlo+1)^2+1)*clight;
         bin_hi = ((zhi+1)^2-1)/((zhi+1)^2+1)*clight;
         xlabel(vlbl);
      }
      else
      {
         bin_lo=zlo;
         bin_hi=zhi;
         xlabel(redlbl);
      }
   }  
   return bin_lo, bin_hi;
}     

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

private define set_my_width(width)
{
   if(width != NULL)
   {
      set_line_width(int(width));
   }
   else
   {
      set_line_width(1);
   }   
}

%%%%%%%%%%%%%%%%%%%

private define I(n)
{
   return n;
}

private variable _revr = [&I, &reverse], _rev;

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

private define make_data(indx,res,modc,bkgon)
{
   variable data, dnote, modl=NULL, bgd, bkg;
   variable expos, iw, bin_lo, bin_hi, ders, mers;
   variable resd=NULL, mres=NULL, pres=NULL;
   variable tdata = 0., tmodl = 0., ters = 0., tders = 0., tbkg=0.;
   variable texpos=0., perunit, iindx;

   _rev = _revr[0];

   variable meth = strtok(get_fit_statistic,";=");
   meth = meth[length(meth)-1];

   iindx = [indx];

   foreach(iindx)
   {
      indx = ();

      data = get_data_counts(indx);
      dnote = get_data_info(indx).notice;

      if(modc > 0) 
      {
         modl = get_model_counts(indx).value;
         mers = @modl;
      }

      bgd = get_back(indx);

      if(bgd == NULL)
      {
         bgd = Float_Type[length(data.value)];
      }

      expos = get_data_info(indx).arfs[0];
      if(expos != 0)
      {
         expos = get_arf_exposure(expos);
         if(expos <= 0.) 
         {
            expos = get_data_exposure(indx);
         }
      }
      else
      {
         expos = get_data_exposure(indx);
      }

      texpos += expos;

      if( (length(where(lk[[0:10]] == s_unit)) > 0) or (s_unit == "user_kev"))
      {
         _rev = _revr[1];
         bin_lo = _A(data.bin_hi)/s_scl[s_unit];
         bin_hi = _A(data.bin_lo)/s_scl[s_unit];
      }
      else
      {
         bin_lo = (data.bin_lo)/s_scl[s_unit];
         bin_hi = (data.bin_hi)/s_scl[s_unit];
      }

      if( bkgon == 0 )
      {
         bkg = bgd;
      }
      else
      {
         bkg = 0.*bgd;
      }
      data.value = @_rev(data.value-bkg);

      dnote = @_rev(dnote);

      if( meth == "gehrels" )
      {
         ders = @_rev( (1. + sqrt(data.err^2+0.75))^2 + bgd );
      }
      else
      {
         ders = @_rev( data.err^2 + bgd );
      }

      if(modc > 0) 
      {
         mers = @_rev(modl+bgd); 
         modl = @_rev(modl-bkg);
         tmodl += modl;
      }

      tdata += data.value;
      tders += ders;
      tbkg += @_rev(bkg);

      if( meth == "model" and modc > 0 )
      {
         ters += mers;
      }
      else
      {
         ters += ders;
      }
   }

   ters = sqrt(ters);
   tders = sqrt(tders);
   perunit = ( bin_hi - bin_lo )*texpos;

   if(res <= 0 or modc == 0)
   {
      iw = where(dnote !=0);
   }
   else if(res == 1 or res == 2 or res == 4 or res == 5)
   {
      iw = where(dnote != 0 and ters > 0.);
   }
   else
   {
      if( meth == "model" and modc > 0 )
      {
         iw = where(dnote != 0 and tmodl - ters > 0.);
      }
      else
      {
         iw = where(dnote != 0 and tmodl > 0.);
      }
   }

   if(modc > 0)
   {
      switch(res)
      {
       case 3 or case 6:
         resd = ones(length(tdata))*1.;
         mres = @resd;
         pres = @resd;
       
         if( meth == "model" )
         {
            resd[iw] = tdata[iw]/tmodl[iw];
            mres[iw] = tdata[iw]/(tmodl[iw]-ters[iw]);
            pres[iw] = tdata[iw]/(tmodl[iw]+ters[iw]);
         }
         else
         {
            resd[iw] = tdata[iw]/tmodl[iw];
            mres[iw] = (tdata[iw]-ters[iw])/tmodl[iw];
            pres[iw] = (tdata[iw]+ters[iw])/tmodl[iw];
         }
      }
      {
       case 1 or case 2 or case 4 or case 5:
         resd = ones(length(tdata))*1.;
         mres = @resd;
         pres = @resd;
         resd[iw] = (tdata[iw] - tmodl[iw] ) / ters[iw];
         mres[iw] = resd[iw] - 1.;
         pres[iw] = resd[iw] + 1.;
     
         if(res == 2 or res == 5)
         {
            resd = sign(resd) * resd^2;
            mres = sign(mres) * mres^2;
            pres = sign(pres) * pres^2;
         }   

         if(meth=="cash")
         {
            resd[iw]=sign(resd[iw])*2
                          *(tmodl[iw]-tdata[iw]+(tdata[iw]+tbkg[iw])
                          *log( (tdata[iw]+tbkg[iw]) /
                                (tmodl[iw]+tbkg[iw])  ) );
            mres[iw]=sign(resd[iw])*2
                        *(tmodl[iw]-tdata[iw]-ters[iw]
                        +(tdata[iw]+ters[iw]+tbkg[iw])
                          *log( (tdata[iw]+ters[iw]+tbkg[iw]) /
                                (tmodl[iw]+tbkg[iw])  ) );
            pres[iw]=sign(resd[iw])*2
                        *(tmodl[iw]-tdata[iw]+ters[iw]
                        +(tdata[iw]-ters[iw]+tbkg[iw])
                          *log( (tdata[iw]-ters[iw]+tbkg[iw]) /
                                (tmodl[iw]+tbkg[iw])  ) );
         }

      }
   }


   if(pd_mean_not_sum !=0)
   {
	perunit=perunit/length(iindx);
   }
   return bin_lo,bin_hi,tdata/perunit*y_scl[s_unit],
          tmodl/perunit*y_scl[s_unit],tders/perunit*y_scl[s_unit],
          resd,mres,pres,iw;
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

private define make_counts(indx,res,power,modc,bkgon)
{
   variable data, dnote, modl=NULL, iw, bin_lo, bin_hi, ers, pow;
   variable bgd, bs, ds;
   variable tdata=0., tmodl=0., ters=0., iindx;
   variable meth = strtok(get_fit_statistic,";=");
   meth = meth[length(meth)-1];
   _rev = _revr[0];
      
   iindx = [indx];
  
   foreach(iindx)
   {
      indx = ();

      data = get_data_counts(indx);
      dnote = get_data_info(indx).notice;

      bgd = get_back(indx);

      if(bgd == NULL)
      {
         bgd = Float_Type[length(data.value)];
      }

      if((length(where(lk[[0:10]]==s_unit)) > 0) or (s_unit=="user_kev"))
      {
         _rev = _revr[1];
         bin_lo = _A(data.bin_hi)/s_scl[s_unit];
         bin_hi = _A(data.bin_lo)/s_scl[s_unit];
      }
      else
      {
         bin_lo = (data.bin_lo)/s_scl[s_unit];
         bin_hi = (data.bin_hi)/s_scl[s_unit];
      }

      pow = (bin_lo+bin_hi)/2.;
      switch(power)
      {
       case 0:
       pow = 1./pow;
      }
      {
       case 1:
       pow = ones( length(pow) );
      }
      {
       case 3:
       pow = pow^2;
      }

      if( bkgon == 0 )
      {
         data.value = @_rev(data.value-bgd)*pow;
      }
      else
      {
         data.value = @_rev(data.value)*pow;
      }

      if(modc > 0) 
      {
         modl = get_model_counts(indx).value;
         if(bkgon == 0)
         {
            modl = @_rev(modl-bgd)*pow;
         }
         else
         {
            modl = @_rev(modl)*pow;
         }
         tmodl += modl;
      }

      dnote = @_rev(dnote);

      if( meth == "gehrels" )
      {
         ers = @_rev( (1.+sqrt(data.err^2+0.75))^2 + bgd );
      }
      else
      {
         ers = @_rev( data.err^2 + bgd );
      }

      ers = ers * pow^2;

      tdata += data.value;
      ters += ers;
   }

   if(res <= 0 or modc == 0)
   {
      iw = where(dnote !=0);
   }
   else if(res == 1 or res == 2)
   {
      iw = where(dnote != 0 and ters > 0.);
   }
   else
   {
      iw = where(dnote != 0 and tmodl > 0.);
   }

   return bin_lo, bin_hi, tdata*y_scl[s_unit], 
          tmodl*y_scl[s_unit], sqrt(ters)*y_scl[s_unit], iw;
}

%%%%%%%%%%%%%%%%%%%%%%%%%%

private define hp_loop(iw)
{
   variable wiw, ja, jb;
   wiw = where( (iw - shift(iw,-1)) > 1 );
   if(length(wiw) != 0)
   {
      ja = [0,wiw];
      jb = [wiw-1,length(iw)-1];
   }
   else
   {
      ja = [0];
      jb = [length(iw)-1];
   }
   return ja, jb;
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

private define hp_loopit(xl,xh, y, color, iw, ja, jb, ioff)
{
   pointstyle(-1);
   variable jj, diw;
   variable minx,maxx,miny,maxy,plt_opts;

   plt_opts=get_plot_options;
   minx=plt_opts.xmin;
   maxx=plt_opts.xmax;
   miny=plt_opts.ymin;
   maxy=plt_opts.ymax;

   jj = 0;
   loop(length(ja))
   {
      diw = iw[[ja[jj]:jb[jj]]];
      if( (length(where(xl[diw]>=minx and xl[diw]<=maxx)) >= 1) and
          (length(where(xh[diw]>=minx and xh[diw]<=maxx)) >= 1) and
          (length(where(y[diw]>=miny and y[diw]<=maxy)) >= 1)     )
      {
         ohplot( xl[diw], xh[diw], y[diw], color );
      }
      jj++;
   }
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

private define make_flux(indx,bin_lo, bin_hi, data, ers, iw, power, modc)
{
   variable mbin_lo=NULL, mbin_hi=NULL, fdata, frst_modl, fmodl=NULL, 
       fers=NULL, fget, iwm=Integer_Type[0], iwv, pow, powm, jj, ja, jb,
       tdata=0., tmc = 0, tmodl=0., ters=0., twght=0., wght, gdi, gdcu, gdcb, 
       iindx, expos;

   (bin_lo,bin_hi,tdata,tmc,ters,iw) = make_counts(indx,0,power,modc,0);

   _rev = _revr[0];

   if((length(where(lk[[0:10]] == s_unit)) > 0) or (s_unit == "user_kev"))
   {
      _rev = _revr[1];
   }

   iindx = [indx];

   foreach(iindx)
   {
      indx = ();

      flux_corr(indx);
      wght = get_flux_corr_weights(indx);

      gdi = get_data_info(indx);
      gdcb = get_data_counts(indx);

      if( length(gdi.notice) !=  length(gdi.rebin) )
      {
         rebin_data(indx,0);
      }
      gdcu = get_data_counts(indx);

      if( length(gdi.notice) !=  length(gdi.rebin) )
      {
         rebin_data(indx,gdi.rebin);
         ignore(indx);
         notice_list(indx,gdi.notice_list);
      }

      twght += rebin(gdcb.bin_lo,gdcb.bin_hi,gdcu.bin_lo,gdcu.bin_hi,
                     wght)/(gdcb.bin_hi-gdcb.bin_lo);

      if( modc != 0 )
      {
         fmodl = get_model_flux(indx);

         if(indx==iindx[0])
         {
            frst_modl = @fmodl;

            if((length(where(lk[[0:10]]==s_unit)) > 0) or 
                                     (s_unit=="user_kev"))
            {
               mbin_lo = _A(fmodl.bin_hi);
               mbin_hi = _A(fmodl.bin_lo);
            }
            else
            {
               mbin_lo = fmodl.bin_lo;
               mbin_hi = fmodl.bin_hi;
            }

            mbin_lo = mbin_lo/s_scl[s_unit];
            mbin_hi = mbin_hi/s_scl[s_unit];

            %% Need to be more clever here.  Temporary kludge
  
            (ja,jb) = hp_loop(iw);
            _for(0,length(ja)-1,1)
            {
               jj = ();
               iwv = iw[[ja[jj]:jb[jj]]];
               iwm = [ iwm, where( (mbin_lo >= min(bin_lo[iwv])) and
                                   (mbin_hi <= max(bin_hi[iwv]))  ) ];
            }

            powm = (mbin_lo+mbin_hi)/2.;
            switch(power)
            {
             case 0:
             powm = 1./powm;
            }
            {
             case 1:
             powm = ones( length(powm) );
            }
            {
             case 3:
             powm = powm^2;
            }
         }
         else
         {
            fmodl.value=rebin(frst_modl.bin_lo,frst_modl.bin_hi,
                              fmodl.bin_lo,fmodl.bin_hi,fmodl.value);
         }
 
         fmodl.value = @_rev( fmodl.value );
         fmodl.value = fmodl.value / (mbin_hi-mbin_lo);
         fmodl.value[iwm] = fmodl.value[iwm]*powm[iwm];
         tmodl += fmodl.value;
      }
   }
   variable ni = length(iindx);
   twght = @_rev(twght);

   if(use_con_flux == 0)
   {
      return  mbin_lo, mbin_hi, 
              tdata/twght/(bin_hi-bin_lo), tmodl/ni*y_scl[s_unit], 
              ters/twght/(bin_hi-bin_lo), iwm;
   }
   else
   {
      return  bin_lo, bin_hi, 
              tdata/twght/(bin_hi-bin_lo), tmc/twght/(bin_hi-bin_lo), 
              ters/twght/(bin_hi-bin_lo), iw;
   }
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

private define res_set(yrng,res)
{
   variable meth = strtok(get_fit_statistic,";=");
   meth = meth[length(meth)-1];

   ylin;
   if (length(yrng) > 2)
   {
      if((yrng[2]!=NULL) && (yrng[2]<=0))
      {
         ylin;
      }
      if(length(yrng)==4)
      {
         yrange(yrng[2],yrng[3]);
      }
      else
      {
         yrange(yrng[2],NULL);
      }
   }
   else
   {
      yrange();
   }
 
   if(ynolabel==0)
   {
      ylabel(rlbl[abs(res)-1]);
      if(meth=="cash" and res!=3 and res!=6)
      {
         ylabel(pg_font+"\\gDC");
      }
   }
   else
   {
      ylabel("");
   }
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

private define xerplot(res,bin_lo,bin_hi,iw)
{
   variable meth = strtok(get_fit_statistic,";=");
   meth = meth[length(meth)-1];

   set_line_width(2);

   variable xer,xero,blo;

   if ( res == 3 or res == 6 )
   {
      xer = 1.;
   }
   else
   {
      xer = 0.;
   }
  
   xero = @bin_lo[iw];
   xero[*] = xer;

   if( min(bin_lo) <= 0 )
   {
      blo = -9.e31;
   }
   else
   {
      blo = 5.e-32;
   }
   ohplot( [blo,bin_lo[iw],5.e31],
           [bin_lo[iw[0]],bin_hi[iw],9.e31],
           [xer,xero,xer], 1);

   set_my_width(r_width);
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

private define datplot_err(bin_lo,bin_hi,data,ers,iw,i)
{
   if(de_width==0 or decol[i]==0) return;

   _pgslw(int(de_width));
   _pgsci(int(decol[i]));

   variable xx,yp,yy,ym;

   xx = (bin_lo[iw]+bin_hi[iw])/2.;
   yy = data[iw];
   yp = ers[iw];
   ym = yy - yp;
   yp += yy;

   if(get_plot_options().logx) 
   { 
      xx[where(xx <= 0)] = 1.e-32;
      xx = log10(xx); 
   }
   if(get_plot_options().logy) 
   { 
      ym[where(ym <= 0)] = 1.e-32;
      yp[where(yp <= 0)] = 2.e-32;
      ym = log10(ym); 
      yp = log10(yp); 
   }

   () = _pgerry(length(xx),xx,ym,yp,ebar_term_length_y);
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

private define resplot_err(bin_lo,bin_hi,resd,mres,pres,iw,i)
{
   if(re_width==0 or recol[i]==0) return;

   _pgslw(int(re_width));
   
   variable xx,xm,xp,yy,ym,yp;

   xx = (bin_lo[iw]+bin_hi[iw])/2.;
   xm = bin_lo[iw];
   xp = bin_hi[iw];

   yy = resd[iw];
   ym = mres[iw];
   yp = pres[iw];

   if(get_plot_options().logx) 
   { 
      xm[where(xm <= 0)] = 1.e-32;
      xx[where(xm <= 0)] = 2.e-32;
      xp[where(xp <= 0)] = 3.e-32;
      xx = log10(xx); 
      xm = log10(xm); 
      xp = log10(xp); 
   }

   _pgsci(int(recol[i]));
   () = _pgerrx(length(xx),xm,xp,yy,ebar_term_length_x);
   _pgsci(int(recol[i]));
   () = _pgerry(length(xx),xx,ym,yp,ebar_term_length_y);
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

private define resplot(xrng,yrng,res,indx_ln,indx,rsym,rcol)
{
   variable bin_lo,bin_hi,data,modl,ers,resd,mres,pres,iw, i, iindx, ja, jb;
   variable meth = strtok(get_fit_statistic,";=");
   meth = meth[length(meth)-1];

   res_set(yrng,res);

   if( abs(res) > 3)
   {
      iindx = indx;
      indx_ln = 1;
   }
   
   i = 0;
   loop(indx_ln)
   {
      if( abs(res) <= 3 ){ iindx = indx[i]; }

      (bin_lo,bin_hi,data,modl,ers,resd,mres,pres,iw) = 
                                            make_data(iindx,abs(res),1,0);
      if(vzero!=NULL)
      {
         (bin_lo,bin_hi) = vaxis(bin_lo,bin_hi);   
      }

      set_my_width(r_width);

      if(abs(res) ==2 or abs(res) ==5 or rsym[i]==0)
      {
         pointstyle(1);
         point_size(0.1);
      }
      else
      {
         pointstyle(rsym[i]);
         point_size(plopt.point_size);
      }
      connect_points(0);
      slangref = refplt[min([i+int(abs(oplt)),1])];
      @slangref( (bin_lo[iw]+bin_hi[iw])/2., resd[iw], 0);

      pd.raxis = get_plot_options().ylabel;
      pd.xaxis = get_plot_options().xlabel;
      if(i<=length(pd.dlo)-1)
      {
         pd.dlo[i] = bin_lo[iw];
         pd.dhi[i] = bin_hi[iw];
      }
      pd.res[i] = resd[iw];
      pd.res_m[i] = mres[iw];
      pd.res_p[i] = pres[iw];

      if(abs(res)==2 or abs(res)==5 or meth=="cash")
      {
         connect_points(-1);
         (ja,jb) = hp_loop(iw);
         hp_loopit(bin_lo, bin_hi, resd, rcol[i], iw, ja, jb, 2);
      }
      else if(abs(res)==1 or abs(res)==3 or abs(res)==4 or abs(res)==6)
      {
         resplot_err(bin_lo,bin_hi,resd,mres,pres,iw,i);

         point_size(plopt.point_size);
         pointstyle(rsym[i]);
         connect_points(0);
         set_my_width(r_width);

         if(rsym[i] != 0)
         {
            slangref = refplt[1];
            @slangref( (bin_lo[iw]+bin_hi[iw])/2., resd[iw], rcol[i] );	
         }
         else
         { 
            pointstyle(1);
            connect_points(1);
            (ja,jb) = hp_loop(iw);
            hp_loopit(bin_lo, bin_hi, resd, rcol[i], iw, ja, jb, 2);
         }
      }

      linestyle(2);
      connect_points(-1);

      if(i==0)
      { 
         xerplot(abs(res),bin_lo,bin_hi,iw);
      }

      linestyle(1);

      i++;
   }
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

private define p_range_label(res,power)
{
   % Very important!  I presume plot_bin_integral throughout these 
   % plotting functions!!!

   plot_bin_integral;

   multiplotit(res);

   if( xnolabel == 0 )
   {
      xlabel(xlbl[s_unit]);
   }
   else
   {
      xlabel("");
   }

   if(vzero!=NULL)
   {
      xlin;
   }

   if(length(xrng)>1)
   {
      if((xrng[0]!=NULL) && (xrng[0]<=0))
      {
         xlin;
      }
      xrange(xrng[0],xrng[1]);
   }

   plopt = get_plot_options;

   if( ynolabel == 0 )
   {
      ylabel(ylbl[s_unit][power]);
   }
   else
   {
      ylabel("");
   }

   if (length(yrng) > 1)
   {
      if((yrng[0]!=NULL) && (yrng[0]<=0))
      {
         ylin;
      }
      yrange(yrng[0],yrng[1]);
   }
   else
   {
      yrange();
   }

   return plopt;
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

% The beginning of the public plotting functions!!!

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

public define nice_size()
{
   variable v = struct{ xmin, xmax, ymin, ymax };
   resize(30,0.9);
   v.xmin = 0.13;
   v.xmax = 0.65;
   v.ymin = 0.1;
   v.ymax = 0.65;
   set_outer_viewport(v);
}

public define nice_width()
{
   set_frame_line_width(3);
   set_line_width(2);
   d_width=3; de_width=2;
   r_width=3; re_width=2;
   m_width=2;
}

public define msg(str_array)
{
   () = printf("\n");
   foreach(str_array)
   {
      variable str = ();
      () = printf(" %s\n", str);
   }
   () = printf("\n");
   return;
}

public define pg_color()
{
% Define a decent green color for pgplot

_pgshls(17,255,0.3,0.6);

% Define a decent brown color for pgplot

_pgshls(18,150,0.35,0.4);

% Define a pink color for pgplot

_pgshls(19,120,0.75,0.75);

% Define a dark yellow color for pgplot

_pgshls(20,180,0.45,0.65);
}

private variable fname = String_Type[20];

public define open_print(a)
{
   variable id;
   variable fname_piece = strchop(a,'/',0);
   variable npiece = length(fname_piece);
   if( npiece == 2 )
   {
      id = open_plot(a);
      fname[id] = fname_piece[0];
   }
   else if( npiece > 2 )
   {
      id = open_plot(a);
      fname[id] = strjoin(fname_piece[[0:npiece-2]],"/");
   }
   else
   {
      print(" Need to specify a plot device.");
      return;
   } 
   pg_color;
   return id;
}

public define close_print()
{
   variable a,b;
   switch(_NARGS)
   {
    case 1:
      a = ();
      close_plot(a);
      return;
   }
   {
    case 2:
      (a,b) = ();
      close_plot(a);
      () = system(b+" "+fname[a]+" &");
      return;
   }
   {
      print("Incorrect arguments.");
      return;
   }
}

public define pg_info()
{
variable str=[
" # COLOR   SYMBOL    # COLOR       SYMBOL             # COLOR SYMBOL"," ",
" 1 default dot       9 yllw green  mdot              17 green filled circle",
" 2 red     plus     10 green+cyan  fancy square      18 brown filled star",
" 3 green   cross    11 indigo      diamond           19 pink  big square",
" 4 blue    circle   12 purple      star           20-27 gold  sized circles",
" 5 cyan    cross    13 red+magenta solid triangle 28-31       left/right/",
" 6 magenta square   14 dark grey   hollow cross               up/down arrows",
" 7 yellow  triangle 15 light grey  star of david 32-127       ASCII symbols",
" 8 orange  earth    16 black       filled square", 
" "," Negative symbol #'s give filled symbols with that many sides",
" Latter colors defined with pg_color();",
" "," pointstyle(#)      : To choose the above.",
" connect_points(#)  : -1 = line only / 0 = points only / 1 = both ",
" linestyle(#)       : (1) full line, (2) dashed, (3) dot-dash, ",
"                      (4) dotted, (5) dash-dot-dot-dot"," ",
" line_or_color(#)   : plot(x,y,##), ## = linestyle(##), if # = 0", 
"                                    ## = color, if # != 0 (default)",
" "," set_line_width(#)  : To set the widths of plotted lines",
" set_frame_line_width(#) : To set the frame width",
" charsize(#) : To set character sizes in plot",
" point_size(#) : To set data point sizes in plot"," ",
" xlabel(), ylabel(), title(); with \\\\u, \\\\d, \\\\g = up, down, greek",
"   \\\\fn, \\\\fr, \\\\fi, \\\\fs = normal, roman, italic, and script fonts",
"   \\\\A = Angstrom, \\\\. = center dot, \\\\x = multiplication,"," ",
" Global variables for custom plotting. *MY ROUTINES ONLY* (see isis> nowak_plots;)",
" ",
"  d_width, m_width, r_width : Line width for data, model, and residuals",
"  de_width, re_width        : Line width for data and residual error bars (0=none)",
"                              (For my routines, above *must* be set this way, ",
"                              not via set_line_width)",
"  xnolabel, ynolabel        : =0 by default, and labels are added to plots. ",
"                              Set to !=0 to remove labels (but numbers remain!)",
"",
"  user_unit_x, user_unit_y  : Strings with user defined labels (see Plot_Unit)",
"  ebar_term_length_x        : Relative length of little caps on x error bars",
"  ebar_term_length_y        : Relative length of little caps on y error bars",
"  z_not_v                   : If !=0, use redshift on X-axis for Velocity plots",
"  no_reset                  : If !=0, you can set multiplot(), and with mpane()",
"                              use the custom routines",
"",
"  multiplot advice          : One pane at a time!  Don't hop back and forth!"];
msg(str);
}

public define nowak_plots()
{
   variable str =[
" The following are the custom plotting routines that I have written:","",
"  Plot_Unit        : My variation on ISIS's plot_unit.  Same choices as ",
"                     in ISIS (plus a few more), but it saves a variable ",
"                     that the below routines will use.",
"  plot_counts      : Plot background subtracted data as counts/bin.", 
"  plot_data        : Main plotting routine, to plot background subtracted",
"                     in detector counts space:  Counts/second/Plot_Unit.",
"  plot_fit_model   : An hplot of just the background subtracted model.",
"  plot_residuals   : A plot of just the data residuals.",
"  plot_unfold      : A plot of the unfolded spectra, or energy/freq./",
"                     wavelength times unfolded spectra.",
"  plotxy           : Simple x-y (o)plots with error bars.",
"  pg_color         : Make some nice pgplot colors (device must first be open).",
"  open_print       : A version of open_plot that invokes pg_color first.",
"  close_print      : A version of close_plot that will then display the",
"                     hardcopy via a chosen system utility (e.g., gv)",
"  nice_size        : An autoset of resize/set_outer_viewport that works",
"                     well for a \"typical\" printed plot. Subject to change",
"                     without any notification!",
"  nice_width       : An autoset of plot width variables that works well",
"                     for papers and presentations",
"  set_plot_labels  : If one first sets the pg_font variable, and then runs",
"                     set_plot_labels, the font style can be changed for",
"                     all axis labels.",
"  new_plot_labels  : Redefine axis labels."];
msg(str);
}

alias("pg_info","pginfo");
alias("nowak_plots","nowakplots");
alias("nowak_plots","nowakplot");
alias("nowak_plots","nowak_plot");

%%%%%%%%%%%%%%%%%%%%%%%%%%

public define Plot_Unit( )
{
 if( _NARGS !=1 and _NARGS !=2 and _NARGS !=3 )
 {
variable str = [
"Plot_Unit(xunit [, yunit, vzero]);"," ",
" Change the plotting units to \"xunit\", as for the ISIS command plot_unit;",
" however, a variable will be stored for use in my custom plotting routines",
" (e.g., plot_counts, plot_data, plot_unfold). Case insensitive."," ",
" Available units:","",
"      eV, keV, MeV, GeV, TeV,",
"      Angstrom, A, nm, um, mm, cm, m,",
"      Hz, kHz, MHz, GHz,",
"      mJy   (**y-unit**! See below!)",
"      ergs  (**y-unit**! See below!)",
"      watts (**y-unit**! See below!)",
"      psd_leahy, psd_rms  (**y-units**! See below!)",
"      user_a   (user defined x-unit, scaling as Angstrom)",
"      user_kev (user defined x-unit, scaling as keV)","",
" **NOTE**: user_a/kev will affect *all* of plot_counts, plot_data, and",
"   plot_unfold (whether that is your intention or not!); mJy/ergs/watts",
"   will affect only plot_unfold, psd_* are for plot_counts, but will also",
"   affect plot_data/plot_unfold.","",
" Fundamentally, power=1 corresponds to photons/cm^2/s/xunit (plot_unfold),",
"   or Counts/bin (plot_counts), with higher (lower) powers multiplying ",
"   (dividing) by xunit. plot_data is always Counts/sec/xunit (\"power\" ",
"   has no effect)."," ",
" mJy: y-unit for plot_unfold/power=2 is mJy.  x-unit defaults to Hz, unless",
"      a valid choice is made from the eV, Hz or A group using \"xunit\", e.g.,",
"      Plot_Unit(\"GHz\",\"mjy\") [Plot_Unit(\"mjy\")==Plot_Unit(\"Hz\",\"mjy\")]",
" ",
" ergs: y-unit for plot_unfold/power=3 (xunit=keV, etc.) or power=1 (xunit=A, etc.)",
"       is ergs/cm^2/sec. x-unit defaults to keV unless a valid choice is made", 
"       from the eV/Hz group or the A group, e.g. Plot_Unit(\"a\",\"ergs\").",
"       [Plot_Unit(\"ergs\")==Plot_Unit(\"kev\",\"ERGS\")]"," ",
" watts: The MKS equivalent of the ergs unit, with similar behavior.","",
" vzero: If set, the x-axis becomes velocity units, in km/s, assuming a",
"        Doppler shift; vzero is the reference point (units of \"xunit\").",
"        \"xunit\" and/or \"yunit\" are still required (to determine x-axis,",
"        \"power\" scalings/y-axis, etc.) If public variable z_not_v !=0,",
"        then the x-axis becomes redshift (z) instead of velocity."," ",
" psd_leahy/psd_rms are for use with SITAR timing routines, and plot",
"   Power Spectra in Leahy or (RMS/Hz)^2 units vs. Hz, using plot_counts.","",
" User Units - Plot_Unit(\"user_a\"); Plot_Unit(\"user_kev\"); :","",
"  user_unit_x/y are public string variables defined by the user, *before*",
"    invoking Plot_Unit(\"user_*\").  The X-label will be \"user_unit_x\",",
"    and the Y-labels will be: [user_unit_y+\"/\"+user_unit_x,",
"    [\"\",user_unit_x,user_unit_x\\\\u2\]+user_unit_y].  These choices",
"    can be overriden by setting: user_label_x and user_label_y","",
"  user_scale_x/y are public Float_Type variables, defined *before* using",
"    Plot_Unit(\"user_*\"), that will rescale the x/y values for *all*",
"    plot types (plot_counts, plot_data, plot_unfold).", 
"              *Note*: the X-scale gets applied first!",
"    user_scale_x is the \"keV per new unit\" or \"A per new unit\" scaling",
"    user_scale_y multiplies *all y-values the same*, i.e., combinations of:",
"     user_scale_y X (cts/new_unit/s, photons/cm^2/s/new_unit, cts/bin)", 
"                  X (1/new_unit, 1, new_unit, new_unit^2)","",
"  user_label_x/y are public string variables, which if set, override the",
"    automatic choices of the routines when using user units.  Note: these",
"    custom x/y-axis labels will be applied to *all* plot routine variations",
"    *identically*.  To make custom choices for individual plot/power types,",
"    use the new_plot_labels() routine."," ",
" User unit example.  To obtain mJy vs. GHz:","",
"   isis> user_label_x = \"Frequency (Hz)\";",
"   isis> user_label_y = \"I\\\\d\\\\gn\\\\u (mJy)\";",
"   isis> user_scale_x = 4.118e-9 % keV *per* GHz;",
"   isis> user_scale_y = 0.6626;  % valid any X-unit, using power=2/user_kev",
"   isis> Plot_Unit(\"user_kev\");",
"   isis> plot_unfold([data ids],\"power\",2,...);"];
msg(str);
return;
 }
 else if(_NARGS==1)
 {
    s_unit = ();
    x_unit="";
    vzero=NULL;
 }
 else if(_NARGS==2)
 {
    (x_unit, s_unit) = ();

    if(typeof(s_unit) == String_Type)
    {
       x_unit = strlow(x_unit);
       vzero=NULL;
       if(s_unit=="")
       {
          s_unit = x_unit;
          x_unit="";
          vzero=NULL;
       }
    }
    else
    {
       vzero=s_unit;
       s_unit=x_unit;
       x_unit = "";
    }
 }
 else
 {
    (x_unit, s_unit, vzero) = ();
    x_unit = strlow(x_unit);
 }

 if(length(where(x_unit==["mjy","ergs","watts","psd_leahy","psd_rms",
                            "user_kev","user_a"]))==1)
 {
    s_unit=x_unit;  % Presume user screw up, and transfer x_unit to s_unit
    x_unit="";
 }

 s_unit = strlow(s_unit);

 if(s_unit == "psd_leahy" or s_unit == "psd_rms")
 {
    plot_unit("kev");
 }
 else if(s_unit == "user_a")
 {
    plot_unit("a");

    if(user_label_x != "")
    {
       xlbl["user_a"] = pg_font+user_label_x;
    }
    else{ xlbl["user_a"] = pg_font+" "+user_unit_x; };

    if(user_label_y != "")
    {
       ylbl["user_a"] = [pg_font+[user_label_y,user_label_y,
                                  user_label_y,user_label_y,
                                  user_label_y,user_label_y,
                                  user_label_y,user_label_y,
                                  user_label_y],"user"];
    }
    else
    {
       ylbl["user_a"] = [pg_font+[" "+user_unit_y,
                                  " "+user_unit_y+"/"+user_unit_x,
                                  " "+user_unit_y,
                                  " "+user_unit_x+" "+user_unit_y,
                                  " "+user_unit_x+"\\u2\\d "+user_unit_y,
                                  " "+user_unit_y+"/"+user_unit_x,
                                  " "+user_unit_y,
                                  " "+user_unit_x+" "+user_unit_y,
                                  " "+user_unit_x+"\\u2\\d "+user_unit_y,
                                  " "+user_unit_y+"/"+user_unit_x],
                                  "user"];
    }
    s_scl["user_a"]=user_scale_x; 
    y_scl["user_a"]=user_scale_y; 
    power_df=1;
 }
 else if(s_unit == "user_kev")
 {
    plot_unit("kev");

    if(user_label_x != "")
    {
       xlbl["user_kev"] = pg_font+user_label_x;
    }
    else{ xlbl["user_kev"] = pg_font+" "+user_unit_x; }

    if(user_label_y != "")
    {
       ylbl["user_kev"] = [pg_font+[user_label_y,user_label_y,
                                    user_label_y,user_label_y,
                                    user_label_y,user_label_y,
                                    user_label_y,user_label_y,
                                    user_label_y],"user"];
    }
    else
    {
       ylbl["user_kev"] = [pg_font+[" "+user_unit_y,
                                    " "+user_unit_y+"/"+user_unit_x,
                                    " "+user_unit_y,
                                    " "+user_unit_x+" "+user_unit_y,
                                    " "+user_unit_x+"\\u2\\d "+user_unit_y,
                                    " "+user_unit_y+"/"+user_unit_x,
                                    " "+user_unit_y,
                                    " "+user_unit_x+" "+user_unit_y,
                                    " "+user_unit_x+"\\u2\\d "+user_unit_y],
                                   "user"];
    }
    s_scl["user_kev"]=user_scale_x;
    y_scl["user_kev"]=user_scale_y;
    power_df=1;
 }
 else if(s_unit == "mjy")
 {
    if(length(where(["angstrom","a","nm","um","micron","mm","cm","m"]==x_unit))==1)
    {
       if(x_unit!="um" and x_unit!="micron")
       {
          plot_unit(x_unit);
       }
       else
       {
          x_unit="um";
          plot_unit("nm");
       }

       s_scl["user_a"] = s_scl[x_unit];
       y_scl["user_a"]=0.6626;

       xlbl["user_a"] = sk[x_unit]+"("+ak[x_unit]+")";
       ylbl["user_a"] = [pg_font+[
                  "Counts s\\u-1\\d "+ak[x_unit]+"\\u-1\\d",
                  "I\\d\\gn\\u"+["/"+sk[x_unit]+"\\u2\\d  (mJy/"+ak[x_unit]+"\\u2\\d)",
                                 "/"+sk[x_unit]+"  (mJy/"+ak[x_unit]+")",
                                 "  (mJy)",
                                 " \\x"+sk[x_unit]+"  (mJy\\."+ak[x_unit]+")"],
                          "Counts/bin/"+ak[x_unit],
                          "Counts/bin",
                          ak[x_unit]+" Counts/bin",
                          ak[x_unit]+"\\u2\\d Counts/bin"],"mjy"];
       s_unit="user_a";
    }
    else if(length(where(["ev","kev","mev","gev","tev",
                            "hz","khz","mhz","ghz"]==x_unit))!=1)
    {
       x_unit="hz";
    }

    if(length(where(["ev","kev","mev","gev","tev",
                            "hz","khz","mhz","ghz"]==x_unit))==1)
    {
       plot_unit(x_unit);
       s_scl["user_kev"] = s_scl[x_unit];
       y_scl["user_kev"]=0.6626;

       xlbl["user_kev"] = sk[x_unit]+"("+ak[x_unit]+")";
       ylbl["user_kev"] = [pg_font+[
                  "Counts s\\u-1\\d "+ak[x_unit]+"\\u-1\\d",
                  "I\\d\\gn\\u"+["/"+sk[x_unit]+"\\u2\\d  (mJy/"+ak[x_unit]+"\\u2\\d)",
                                 "/"+sk[x_unit]+"  (mJy/"+ak[x_unit]+")",
                                 "  (mJy)",
                                 " \\x"+sk[x_unit]+"  (mJy\\."+ak[x_unit]+")"],
                          "Counts/bin/"+ak[x_unit],
                          "Counts/bin",
                          ak[x_unit]+" Counts/bin",
                          ak[x_unit]+"\\u2\\d Counts/bin"],"mjy"];
       s_unit="user_kev";
    }
 }
 else if(s_unit == "ergs")
 {
    if(length(where(["angstrom","a","nm","um","micron","mm","cm","m"]==x_unit))==1)
    {
       if(x_unit!="um" and x_unit!="micron")
       {
          plot_unit(x_unit);
       }
       else
       {
          x_unit="um";
          plot_unit("nm");
       }

       s_scl["user_a"] = s_scl[x_unit];
       y_scl["user_a"]=1.986e-8/s_scl[x_unit];

       xlbl["user_a"] = sk[x_unit]+"("+ak[x_unit]+")";

       ylbl["user_a"]=[pg_font+["Counts s\\u-1\\d "+ak[x_unit]+"\\u-1\\d",
        "F\\d\\gl\\u  (ergs cm\\u-2\\d s\\u-1\\d "+ak[x_unit]+"\\u-1\\d)",
        "\\glF\\d\\gl\\u  (ergs cm\\u-2\\d s\\u-1\\d)",
        "\\gl\\u2\\dF\\d\\gl\\u  ("+ak[x_unit]+" ergs cm\\u-2\\d s\\u-1\\d)",
        "\\gl\\u3\\dF\\d\\gl\\u  ("+
           ak[x_unit]+"\\u2\\d ergs cm\\u-2\\d s\\u-1\\d)",
        "Counts/bin/"+ak[x_unit],
        "Counts/bin",
        ak[x_unit]+" Counts/bin",
        ak[x_unit]+"\\u2\\d Counts/bin"],"ergs"];

       s_unit="user_a";
    }
    else if(
       length(where(["ev","kev","mev","gev","tev",
                     "hz","khz","mhz","ghz"]==x_unit))
       !=1 )
    {
       x_unit="kev";
    }

    if(length(where(["ev","kev","mev","gev","tev",
                     "hz","khz","mhz","ghz"]==x_unit))==1)
    {
       plot_unit(x_unit);
       s_scl["user_kev"] = s_scl[x_unit];
       y_scl["user_kev"]=1.602e-9*s_scl[x_unit];
       xlbl["user_kev"] = sk[x_unit]+"("+ak[x_unit]+")";
       ylbl["user_kev"]=[pg_font+["Counts s\\u-1\\d "+ak[x_unit]+"\\u-1\\d",
         "F\\d\\gn\\u/\\gn\\u-2\\d  (ergs cm\\u-2\\d s\\u-1\\d "
         +ak[x_unit]+"\\u-3\\d)",
         "F\\d\\gn\\u/\\gn  (ergs cm\\u-2\\d s\\u-1\\d "+ak[x_unit]+"\\u-2\\d)",
         "F\\d\\gn\\u (ergs cm\\u-2\\d s\\u-1\\d "+ak[x_unit]+"\\u-1\\d)",
         "\\gnF\\d\\gn\\u  (ergs cm\\u-2\\d s\\u-1\\d)",
         "Counts/bin/"+ak[x_unit],
         "Counts/bin",
         ak[x_unit]+" Counts/bin",
          ak[x_unit]+"\\u2\\d Counts/bin"],"ergs"];

         s_unit="user_kev";
    }
 }
 else if(s_unit == "watts")
 {
    if(length(where(["angstrom","a","nm","um","micron","mm","cm","m"]==x_unit))==1)
    {
       if(x_unit!="um" and x_unit!="micron")
       {
          plot_unit(x_unit);
       }
       else
       {
          x_unit="um";
          plot_unit("nm");
       }
       s_scl["user_a"] = s_scl[x_unit];
       y_scl["user_a"]=1.986e-11/s_scl[x_unit];

       xlbl["user_a"] = sk[x_unit]+"("+ak[x_unit]+")";
       ylbl["user_a"]=[pg_font+["Counts s\\u-1\\d "+ak[x_unit]+"\\u-1\\d",
              "F\\d\\gl\\u  (Watts m\\u-2\\d "+ak[x_unit]+"\\u-1\\d)",
              "\\glF\\d\\gl\\u  (Watts m\\u-2\\d )",
              "\\gl\\u2\\dF\\d\\gl\\u  ("+ak[x_unit]+" Watts m\\u-2\\d)",
              "\\gl\\u3\\dF\\d\\gl\\u  ("+
                 ak[x_unit]+"\\u2\\d Watts m\\u-2\\d)",
              "Counts/bin/"+ak[x_unit],
              "Counts/bin",
              ak[x_unit]+" Counts/bin",
              ak[x_unit]+"\\u2\\d Counts/bin"],"watts"];

       s_unit="user_a";
    }
    else if(
       length(where(["ev","kev","mev","gev","tev",
                     "hz","khz","mhz","ghz"]==x_unit))
       !=1 )
    {
       x_unit="kev";
    }

    if(length(where(["ev","kev","mev","gev","tev",
                     "hz","khz","mhz","ghz"]==x_unit))==1)
    {
       plot_unit(x_unit);
       s_scl["user_kev"] = s_scl[x_unit];
       y_scl["user_kev"]=1.602e-12*s_scl[x_unit];

       xlbl["user_kev"] = sk[x_unit]+"("+ak[x_unit]+")";

       ylbl["user_kev"]=[pg_font+["Counts s\\u-1\\d "+ak[x_unit]+"\\u-1\\d",
              "F\\d\\gn\\u/\\gn\\u-2\\d  (Watts m\\u-2\\d "
               +ak[x_unit]+"\\u-3\\d)",
              "F\\d\\gn\\u/\\gn  (Watts m\\u-2\\d "+ak[x_unit]+"\\u-2\\d)",
              "F\\d\\gn\\u (Watts m\\u-2\\d "+ak[x_unit]+"\\u-1\\d)",
              "\\gnF\\d\\gn\\u  (Watts m\\u-2\\d)",
              "Counts/bin/"+ak[x_unit],
              "Counts/bin",
              ak[x_unit]+" Counts/bin",
              ak[x_unit]+"\\u2\\d Counts/bin"],"watts"];

       s_unit="user_kev";
    }
 }
 else
 {
    if(s_unit!="um" and s_unit!="micron")
    {
       plot_unit(s_unit);
    }
    else
    {
       s_unit="um";
       plot_unit("nm");
    }

   power_df=1;
 }
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

public define plot_counts()
{
   % Usage message:

variable str = [
"plot_counts([indx],pstruct);  % pstruct = struct{ dcol, mcol, rcol, ...}",
"plot_counts([indx],\"dcol\",[dcol],\"mcol\",[mcol],\"rcol\",[rcol],...);",
"plot_counts([indx];dcol=[dcol],mcol=[mcol],rcol=[rcol],...);",
" ", 
"  Plot background subtracted data, model, and residuals as counts/bin",
"  Residuals are units of chi, chi2, or ratio, and will be based upon whether",
"  one chooses sigma=model, data, or gehrels in set_fit_statistic();",
"  (data error bars only affected by the latter two)"," ",
"  Options below refer to structure variables/associative keys/qualifiers","",
"   indx = data index (or array of indices; *any* negative value = combine *all* data)",
"   dcol = (pgplot) color of data (or array of color values)",
"   decol= (pgplot) color of data error bars (or array of color values)",
"   mcol = color for model (or array of color values)",
"          0 => No model plotted",
"   rcol = color for residuals (or array of color values)",
"   recol= color for residual error bars (or array of color values)",
"   dsym = (pgplot) symbol number for data",
"          0 => histogram plot",
"   rsym = symbol number for residuals",
"          0 => histogram plot",
"   yrng = (Array *or* List) Y-limits for the data/model+(optional) residuals",
"   xrng = (Array *or* List) X-limits for the data/model/residuals",
"          Note: To autoscale, set NULL in *list* to autoscale that value",
"   oplt = 0 (default) for new plot, !=0 for overplotting",
"   res  = 0 (default), no residuals; 1, 2, or 3 = chi, chi2, or ratio residuals",
"          4, 5, or 6 for chi, chi2, or ratio residuals for combined data sets",
"   power= 0, 1 (default), 2, or 3 for Counts/bin X ",
"         (1/Unit, 1, Unit, Unit^2), respectively"];

   variable i,args,indx_ln, indx_lng, iindx;
   variable data,bin_lo,bin_hi,modl,pres,mres,resd,ers;
   variable modc=0;
   variable iw;
   variable diw,ja,jb;

   power_df=1;

   if(_NARGS > 0)
   {
      args = __pop_args(_NARGS);
      indx_ln = length(args[0].value);
      if(indx_ln == 1 and min([args[0].value]) < 1)
      {
         args[0].value = [args[0].value][0];
         args[0].value = -typecast(combination_members(-args[0].value),
                                   Integer_Type);
         indx_ln = length(args[0].value);
      }
      indx = args[0].value;
      set_plot_defaults(indx_ln);
   }
   else
   {
      msg(str);
      return;
   }

   set_plot_parms(_NARGS,args,indx_ln;;__qualifiers());

   %  And let the plotting begin ...

   plopt = p_range_label(res,power+5);

   % DATA/MODEL PLOTS -

   indx_lng = indx_ln;
   if( min([indx]) < 0 )
   {
      indx_lng = 1;
   }

   pd_set(indx_lng,abs([indx]),res);
   pd.type="plot_counts";

   i = 0;
   loop(indx_lng)
   {
      iindx = abs(indx[i]);
      if( indx_lng == 1) iindx = abs(indx);

      (bin_lo, bin_hi, data, modl, ers, iw) = 
                       make_counts(iindx,res,power,mcol[i],bkg[i]);

      if(ylbl[s_unit][9]!="user" and ylbl[s_unit][9]!="psd")
      {
	data = data/y_scl[s_unit];
        ers = ers/y_scl[s_unit];
        modl = modl/y_scl[s_unit];
      }

      if(vzero!=NULL)
      {
         (bin_lo,bin_hi) = vaxis(bin_lo,bin_hi);
      }

      if( dsym[i] == 0 )
      {
         pointstyle(1);
         point_size(0.1);
      }
      else
      {
         pointstyle(dsym[i]);
         point_size(plopt.point_size);
      }
      connect_points(0);
      slangref = refplt[min([i+int(abs(oplt)),1])];
      @slangref( (bin_lo[iw]+bin_hi[iw])/2., data[iw], dcol[i]);
      point_size(plopt.point_size);

      pd.dlo[i] = bin_lo[iw];
      pd.dhi[i] = bin_hi[iw];
      pd.dval[i] = data[iw];
      pd.derr[i] = ers[iw];
      pd.yaxis = get_plot_options().ylabel;
      pd.xaxis = get_plot_options().xlabel;

      datplot_err(bin_lo,bin_hi,data,ers,iw,i);

      set_my_width(d_width);

      if(dsym[i] != 0)
      {
         connect_points(0);
         pointstyle(dsym[i]);
         slangref = refplt[1];
         @slangref( (bin_lo[iw]+bin_hi[iw])/2., data[iw], dcol[i] );
      }
      else
      {  
         pointstyle(1);
         connect_points(-1);
         (ja,jb) = hp_loop(iw);
         hp_loopit( bin_lo, bin_hi, data, dcol[i], iw, ja, jb, 0);
      }

      set_my_width(m_width);

      if(mcol[i] != 0)
      {
         connect_points(-1);
         (ja,jb) = hp_loop(iw);
         hp_loopit(bin_lo, bin_hi, modl, mcol[i], iw, ja, jb, 0); 
         pd.mlo[i] = bin_lo[iw];
         pd.mhi[i] = bin_hi[iw];
         pd.mval[i] = modl[iw];
      }

      i++;
   }

   % RESIDUAL PLOTS -

   if( res > 0 )
   {
      resplot(xrng,yrng,res,indx_ln,abs(indx),rsym,rcol);
   }

   p_reset;

   return;
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

public define plot_data()
{
   % Usage message:

variable str = [
"plot_data([indx],pstruct);  % pstruct = struct{ dcol, mcol, rcol, ...}",
"plot_data([indx],\"dcol\",[dcol],\"mcol\",[mcol],\"rcol\",[rcol],...);",
"plot_data([indx];dcol=[dcol],mcol=[mcol],rcol=[rcol],...);",
" ", 
"  Plot background subtracted data, model, and residuals in detector space",
"  Residuals are units of chi, chi2, or ratio, and will be based upon whether",
"  one chooses sigma=model, data, or gehrels in set_fit_statistic();",
"  (data error bars only affected by the latter two)"," ",
"  Options below refer to structure variables/associative keys/qualifiers","",
"   indx = data index (or array of indices,*any* negative value = combine *all* data)",
"   dcol = (pgplot) color of data (or array of color values)",
"   decol= (pgplot) color of data error bars (or array of color values)",
"   mcol = color for model (or array of color values)",
"          0 => No model plotted",
"   rcol = color for residuals (or array of color values)",
"   recol= color for residual error bars (or array of color values)",
"   dsym = (pgplot) symbol number for data",
"          0 => histogram plot",
"   rsym = symbol number for residuals",
"          0 => histogram plot",
"   yrng = (Array *or* List) Y-limits for the data/model+(optional) residuals",
"   xrng = (Array *or* List) X-limits for the data/model/residuals",
"          Note: To autoscale, set NULL in *list* to autoscale that value",
"   oplt = 0 (default) for new plot, !=0 for overplotting",
"   res  = 0 (default), no residuals; 1, 2, or 3 = chi, chi2, or ratio residuals",
"          4, 5, or 6 for chi, chi2, or ratio residuals for combined data sets"];

   variable i,args,indx_ln, indx_lng, iindx;
   variable data,bin_lo,bin_hi,modl,pres,mres,resd,ers;
   variable modc=0;
   variable iw;
   variable diw,ja,jb;

   if(_NARGS > 0)
   {
      args = __pop_args(_NARGS);
      indx_ln = length(args[0].value);
      if(indx_ln == 1 and min([args[0].value]) < 1)
      {
         args[0].value = [args[0].value][0];
         args[0].value = -typecast(combination_members(-args[0].value),
                                   Integer_Type);
         indx_ln = length(args[0].value);
      }
      indx = args[0].value;
      set_plot_defaults(indx_ln);
   }
   else
   {
      msg(str);
      return;
   }

   set_plot_parms(_NARGS,args,indx_ln;;__qualifiers());

   %  And let the plotting begin ...

   plopt = p_range_label(res,0);

   % DATA/MODEL PLOTS - 

   indx_lng = indx_ln;
   if( min([indx]) < 0 )
   {
      indx_lng = 1;
   }

   pd_set(indx_lng,abs([indx]),res);
   pd.type = "plot_data";

   i = 0;
   loop(indx_lng)
   {
      iindx = abs(indx[i]);
      if( indx_lng == 1) iindx = abs(indx);

      (bin_lo, bin_hi, data, modl, ers, , , , iw) = 
                       make_data(iindx,res,mcol[i],bkg[i]);

      if(ylbl[s_unit][9]!="user" and ylbl[s_unit][9]!="psd")
      {
	data = data/y_scl[s_unit];
        ers = ers/y_scl[s_unit];
        modl = modl/y_scl[s_unit];
      }

      if(vzero!=NULL)
      {
         (bin_lo,bin_hi) = vaxis(bin_lo,bin_hi);
      }

      if( dsym[i] == 0 )
      {
         pointstyle(1);
         point_size(0.1);
      }
      else
      {
         pointstyle(dsym[i]);
         point_size(plopt.point_size);
      }
      connect_points(0);
      slangref = refplt[min([i+int(abs(oplt)),1])];
      @slangref( (bin_lo[iw]+bin_hi[iw])/2., data[iw], dcol[i]);
      point_size(plopt.point_size);

      pd.dlo[i] = bin_lo[iw];
      pd.dhi[i] = bin_hi[iw];
      pd.dval[i] = data[iw];
      pd.derr[i] = ers[iw];
      pd.yaxis = get_plot_options().ylabel;
      pd.xaxis = get_plot_options().xlabel;

      datplot_err(bin_lo,bin_hi,data,ers,iw,i);

      set_my_width(d_width);

      if(dsym[i] != 0)
      {
         connect_points(0);
         pointstyle(dsym[i]);
         slangref = refplt[1];
         @slangref( (bin_lo[iw]+bin_hi[iw])/2., data[iw], dcol[i] );
      }
      else
      {  
         connect_points(-1);
         (ja,jb) = hp_loop(iw);
         hp_loopit( bin_lo, bin_hi, data, dcol[i], iw, ja, jb, 0);
      }

      set_my_width(m_width);

      if(mcol[i] != 0)
      {
         connect_points(-1);
         (ja,jb) = hp_loop(iw);
         hp_loopit(bin_lo, bin_hi, modl, mcol[i], iw, ja, jb, 0); 
         pd.mlo[i] = bin_lo[iw];
         pd.mhi[i] = bin_hi[iw];
         pd.mval[i] = modl[iw];
      }

      i++;
   }

   % RESIDUAL PLOTS -

   if( res > 0 )
   {
      resplot(xrng,yrng,res,indx_ln,abs(indx),rsym,rcol);
   }

   p_reset;

   return;
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

public define plot_fit_model()
{
   % Usage message:

variable str = [
"plot_fit_model([indx],pstruct);  % pstruct = struct{ mcol, ...}",
"plot_fit_model([indx],\"mcol\",[mcol],...);",
"plot_fit_model([indx];mcol=[mcol],...);",
" ", "  Plot background subtracted model in detector space",
"  ",
"  Options below refer to structure variables/associative keys/qualifiers","",
"   indx = data index (or array of indices, *any* negative value = combine *all* data)",
"   mcol = color for model (or array of color values)",
"   yrng = (Array *or* list) Y-limits for the model",
"   xrng = (Array *or* list) X-limits for the model"];

   variable i,args,indx_ln;
   variable data,bin_lo,bin_hi,modl,ers;
   variable iw,ja,jb,jj,diw;

   if(_NARGS > 0)
   {
      args = __pop_args(_NARGS);
      indx_ln = length(args[0].value);
      if(indx_ln == 1 and min([args[0].value]) < 1)
      {
         args[0].value = [args[0].value][0];
         args[0].value = -typecast(combination_members(-args[0].value),
                                   Integer_Type);
         indx_ln = length(args[0].value);
      }
      indx = args[0].value;
      set_plot_defaults(indx_ln);
   }
   else
   {
      msg(str);
      return;
   }

   set_plot_parms(_NARGS,args,indx_ln;;__qualifiers());

   %  And let the plotting begin ...

   plopt = p_range_label(res,0);

   % Loop over the model plots

   i = 0;
   loop(indx_ln)
   {
      ( bin_lo, bin_hi, data, modl, ers, , , , iw ) = 
                        make_data(indx[i],res,1,bkg[i]);

      if(ylbl[s_unit][9]!="user" and ylbl[s_unit][9]!="psd")
      {
	data = data/y_scl[s_unit];
        ers = ers/y_scl[s_unit];
        modl = modl/y_scl[s_unit];
      }

      if(vzero!=NULL)
      {
         (bin_lo,bin_hi) = vaxis(bin_lo,bin_hi);
      }

      connect_points(-1);
      pointstyle(1);
      set_my_width(m_width);

      (ja,jb) = hp_loop(iw);
      jj = 0;
      loop(length(ja))
      {
         diw = iw[[ja[jj]:jb[jj]]];
         slangref = hrefplt[min([i+jj,1])];
         @slangref( bin_lo[diw], bin_hi[diw], modl[diw], mcol[i] ); 
         jj++;
      }
      i++;
   }

   p_reset;

   return;
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

public define plot_residuals()
{
   % Usage message:

variable str = [
"plot_residuals([indx],pstruct);  % pstruct = struct{ rcol, ...}",
"plot_residuals([indx],\"rcol\",[rcol],...);",
"plot_residuals([indx];rcol=[rcol],...);",
" ", "  Plot model fit residuals",
"  Residuals are units of chi, chi2, or ratio, and will be based upon whether",
"  one chooses sigma=model, data, or gehrels in set_fit_statistic();",
"  (data error bars only affected by the latter two)"," ",
"  Options below refer to structure variables/associative keys/qualifiers","",
"   indx = data index (or array of indices, *any* negative value = combine *all* data)",
"   rcol = color for residuals (or array of color values)",
"   recol= color for residual error bars (or array of color values)",
"   rsym = symbol number for residuals",
"          0 => histogram plot",
"   yrng = (Array *or* list) Y-limits for the data/model+(optional) residuals",
"   xrng = (Array *or* list) X-limits for the data/model/residuals",
"          Note: To autoscale ranges, set NULL in *list* to autoscale that value",
"   res  = 1 (default), 2, or 3 = chi, chi2, or ratio residuals"];

   variable i,args,indx_ln,pdsi;
   variable data,bin_lo,bin_hi,expos,modl,pres,mres,resd,ers;
   variable iw,ja,jb;

   if(_NARGS > 0)
   {
      args = __pop_args(_NARGS);
      indx_ln = length(args[0].value);
      if(indx_ln == 1 and min([args[0].value]) < 1)
      {
         args[0].value = [args[0].value][0];
         args[0].value = -typecast(combination_members(-args[0].value),
                                   Integer_Type);
         indx_ln = length(args[0].value);
      }
      indx = args[0].value;
      set_plot_defaults(indx_ln);
   }
   else
   {
      msg(str);
      return;
   }

   set_plot_parms(_NARGS,args,indx_ln;;__qualifiers());

   res = abs(res);
   if( res == 0 ) res=1;

   if(res>3)
   {
      pdsi = 1;
   }
   else
   {
      pdsi = indx_ln;
   }

   %  And let the plotting begin ...

    if (length(yrng) > 1 and length(yrng) < 4)
   {
      yrng = {yrng[0],yrng[1],yrng[0],yrng[1]};
   }

   plopt = p_range_label(0,0);

   pd_set(pdsi,abs(indx),res);
   pd.type="plot_residuals";

   % RESIDUAL PLOTS -

   resplot(xrng,yrng,res,indx_ln,abs(indx),rsym,rcol);

   p_reset;

   return;
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%

public define plot_unfold()
{
   variable i,args,indx_ln,indx_lng,iindx;
   variable data,bin_lo,bin_hi,expos,modl,resd,mres,pres,ers;
   variable fdata,fmodl,mbin_lo,mbin_hi,fers;
   variable iw,iwm,ja,jb;

   if(ylbl[s_unit][9]=="mjy")
   {
      power_df=2;
   }

   if(ylbl[s_unit][9]=="ergs" or ylbl[s_unit][9]=="watts")
   {
      if(s_unit=="user_a")
      {
         power_df=1;
      }
      else if(s_unit=="user_kev")
      {
         power_df=3;
      }
   }

   % Usage message

variable str = [
"plot_unfold([indx],pstruct);  % pstruct = struct{ dcol, mcol, rcol, ...}",
"plot_unfold([indx],\"dcol\",[dcol],\"mcol\",[mcol],\"rcol\",[rcol],...);",
"plot_unfold([indx];dcol=[dcol],mcol=[mcol],rcol=[rcol],...);",
" ", "  Plot background subtracted data, model, and residuals in flux space",
"  Residuals are units of chi, chi2, or ratio, and will be based upon whether",
"  one chooses sigma=model, data, or gehrels in set_fit_statistic();",
"  (data error bars only affected by the latter two)"," ",
"  Options below refer to structure variables/associative keys/qualifiers","",
"   indx = data index (or array of indices, *any* negative value = combine *all* data)",
"   dcol = (pgplot) color of data (or array of color values)",
"   decol= (pgplot) color of data error bars (or array of color values)",
"   mcol = color for model (or array of color values)",
"   rcol = color for residuals (or array of color values)",
"   recol= color for residual error bars (or array of color values)",
"   dsym = (pgplot) symbol number for data",
"   rsym = symbol number for residuals",
"   yrng = (Array *or* List) Y-limits for the data/model+(optional) residuals",
"   xrng = (Array *or* List) X-limits for the data/model/residuals",
"          Note: To autoscale ranges, set NULL in *list* for that value",
"   oplt = 0 (default) for new plot, !=0 for overplotting",
"   res  = 0 (default), no residuals; 1, 2, or 3 = chi, chi2, or ratio residuals",
"          4, 5, or 6 for chi, chi2, or ratio residuals for combined data sets",
"   power= 0, 1 (usual default), 2 (mJy default), or 3 (ergs vs. energy default)",
"          for Unit vs. Photons/cm^2/s/Unit X (1/Unit, 1, Unit, Unit^2), respectively.",
"          (power is always fundamentally is based on Photons/cm^2/s/Unit X ....,",
"          even when mJy, ergs, or Watts is chosen as the y-unit.)",
" ",
"  Note: Model flux is: ( \int dE S(E) )/dE, while data is",
"  (C(h) - B(h))/(\int R(h,E) A(E) dE)/dh/t, where A(E) is effective area,",
"  R(h,E) is RMF, C(h)/B(h) are total/background counts. Thus, the data",
"  and model will match best only in the limit of a delta function RMF,",
"  and in fact might look different than the residuals (which is the",
"  only proper comparison between data and model, anyhow)."]; 

   if(_NARGS > 0)
   {
      args = __pop_args(_NARGS);
      indx_ln = length(args[0].value);
      if(indx_ln == 1 and min([args[0].value]) < 1)
      {
         args[0].value = [args[0].value][0];
         args[0].value = -typecast(combination_members(-args[0].value),
                                   Integer_Type);
         indx_ln = length(args[0].value);
      }
      indx = args[0].value;
      set_plot_defaults(indx_ln);
   }
   else
   {
      msg(str);
      return;
   }

   set_plot_parms(_NARGS,args,indx_ln;;__qualifiers());

   %  And let the plotting begin ...
   
   plopt = p_range_label(res,power+1);

   % DATA/MODEL PLOTS - 

   indx_lng = indx_ln;
   if( min([indx]) < 0 )
   {
      indx_lng = 1;
   }

   pd_set(indx_lng,abs([indx]),res);
   pd.type = "plot_unfold";

   i = 0;
   loop(indx_lng)
   {
      iindx = abs(indx[i]);
      if( indx_lng == 1) iindx = abs(indx);

      ( bin_lo, bin_hi, data, , ers, , , , iw ) = 
                        make_data(iindx, res, mcol[i], bkg[i]);
      ( mbin_lo, mbin_hi, fdata, fmodl, fers, iwm ) = 
               make_flux(iindx, bin_lo, bin_hi, data, ers, iw, power, mcol[i]);

      if(vzero!=NULL)
      {
         (bin_lo,bin_hi) = vaxis(bin_lo,bin_hi);
         (mbin_lo,mbin_hi) = vaxis(mbin_lo,mbin_hi);

      }

      if(dsym[i]==0)
      {
         point_style(1);
         point_size(0.1);
      }
      else
      {
         point_style(dsym[i]);
         point_size(plopt.point_size);
      }
      connect_points(0);
      slangref = refplt[min([i+int(abs(oplt)),1])];
      @slangref( (bin_lo[iw]+bin_hi[iw])/2., fdata[iw], dcol[i]);
      point_size(plopt.point_size);

      pd.dlo[i] = bin_lo[iw];
      pd.dhi[i] = bin_hi[iw];
      pd.dval[i] = fdata[iw];
      pd.derr[i] = fers[iw];
      pd.yaxis = get_plot_options().ylabel;
      pd.xaxis = get_plot_options().xlabel;
 
      datplot_err(bin_lo,bin_hi,fdata,fers,iw,i);

      set_my_width(d_width);
      if(dsym[i] != 0)
      {
         connect_points(0);
         pointstyle(dsym[i]);
         slangref = refplt[1];
         @slangref( (bin_lo[iw]+bin_hi[iw])/2., fdata[iw], dcol[i] );
      }
      else
      {  
         pointstyle(1);
         connect_points(-1);
         (ja,jb) = hp_loop(iw);
         hp_loopit( bin_lo, bin_hi, fdata, dcol[i], iw, ja, jb, 0);
      }

      set_my_width(m_width);
      if(mcol[i] != 0)
      {
         connect_points(1);
         (ja,jb) = hp_loop(iwm);
         hp_loopit( mbin_lo, mbin_hi, fmodl, mcol[i], iwm, ja, jb, 0); 
         pd.mlo[i] = mbin_lo[iwm];
         pd.mhi[i] = mbin_hi[iwm];
         pd.mval[i] = fmodl[iwm];
      }

      i++;
   }

   % RESIDUAL PLOTS - 
   if( res > 0 )
   {
      resplot(xrng,yrng,res,indx_ln,abs(indx),rsym,rcol);
   }

   p_reset;   

   return;
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
%                                %
%   Data File Output Functions   %
%                                %
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

private define write_plot_head(fp,head,i,nf,mdr,nd,nr,nm)
{
   variable lf = length(pd.file),ilf=0,nllchk;
   if(head != 0)
   {
      () = fprintf(fp,"# Plot Type : %s \n", pd.type);
      () = fprintf(fp,"# \n");
      () = fprintf(fp,"# X-axis    : %s \n", pd.xaxis);
      () = fprintf(fp,"# Y-Axis    : %s \n", pd.yaxis);
      () = fprintf(fp,"# Residual  : %s \n", pd.raxis);
      () = fprintf(fp,"# \n");

      switch(mdr)
      {case 1:
         nllchk=@pd.dval;
      }
      {case 2:
         nllchk=@pd.file;
      }
      {case 3:
         nllchk=@pd.mval;
      }
      {case 4:
         nllchk=@pd.dval;
      }

      if(nf==1)
      {
         if(lf>1)
         {
            loop(lf)
            {
               if(nllchk[ilf][0]!=NULL)
               {
                  () = fprintf(fp,"# Index- %3i,  Data File: %s \n", 
                                  pd.index[ilf][0], pd.file[ilf][0]);
               }
               ilf++;
	    }
	 }
         else
         {
            lf = length(pd.file[0]);
            loop(lf)
            {
               () = fprintf(fp,"# Index- %3i,  Data File: %s \n", 
                               pd.index[0][ilf], pd.file[0][ilf]);
               ilf++;
	    }
         }
      }
      else
      {
         () = fprintf(fp,"# Index- %3i,  Data File: %s \n", 
                         pd.index[i][0], pd.file[i][0]);
      }         

      () = fprintf(fp,"# \n");

      switch(mdr)
      {case 1:
         ()=fprintf(fp,"# DATA_COLS: %3i sets (X 2 columns - Data, Error)\n",nd);     
         ()=fprintf(fp,"# RES_COLS : %3i sets (X 3 columns - Mean, Mean-1Sigma, Mean+1Sigma)\n",nr);
      }
      {case 2:
         ()=fprintf(fp,"# RES_COLS : %3i sets (X 3 columns - Mean, Mean-1Sigma, Mean+1Sigma)\n",nr);
      }        
      {case 3:
         ()=fprintf(fp,"# MOD_COLS : %3i sets (X 1 column  - Value) \n",nm);     
      }        
      {case 4:
         ()=fprintf(fp,"# DATA_COLS: %3i sets (X 2 columns - Data, Error)\n",nd);     
         ()=fprintf(fp,"# MOD_COLS : %3i sets (X 1 column  - Value) \n",nm);     
         ()=fprintf(fp,"# RES_COLS : %3i sets (X 3 columns - Mean, Mean-1Sigma, Mean+1Sigma)\n",nr);
      }

      () = fprintf(fp,"# \n");
   }
}


static variable fpd=File_Type[0], fpm=File_Type[0]; 

public define write_plot()
{
   variable str,root,head;
   str=[" write_plot(root; head=0);","",
   " Creates ASCII files with the data from the last plot made (with any",
   " plot device) using plot_counts, plot_data, plot_residuals, or ",
   " plot_unfold.  Data will be stored in columns in the following order-",
   " bin_lo, bin_hi, data_value, data_error (single column), model, ",
   " mean residual, residual-1sigma, residual+1sigma.  Important notes:",
   "",
   "   The X-axis and Y-axis units will reflect those of the plot. The",
   "   binning and noticed data ranges will reflect data filters applied",
   "   within ISIS, but not the (usually more limited) plot range filers.",
   "",
   "   Each individual, uncombined (in both data *and* residuals) data",
   "   set will be written to a separate ASCII file. (For plot_residuals,",
   "   the number of files output is solely determined by whether or not",
   "   the residuals are combined.)","",
   "   Plots combining the data and/or the residuals will place all",
   "   data indices in the same file, writing all the data/error",
   "   columns first, then all the residual/-/+ columns next, each set",
   "   ordered identically to the order input to the plot command.","",
   "   Combined data or residuals will only write out the chosen",
   "   combinations, not the individual pieces.","",
   "   Plots made from plot_unfold will create separate files for the",
   "   data and the unfolded model, as they are on different grids.",
   "   The model file will begin with the appropriate bin_lo, bin_hi.","",
   "   Files created from plot_residuals will only output the bin_lo/hi",
   "   grid and the residual/-/+ columns, not the data or model.","",
   "   If the model or residuals were not plotted, they will not be",
   "   written to the ASCII file.","",
   " Inputs:","",
   "   root- A string with the base file name.  Outputs will be-- ",
   "         root_#.dat, root_#.mod, root_#.res.  The _# portion will",
   "         only be appended if there are multiple, uncombined (for both",
   "         data and residuals) data sets.  # = the data set index as",
   "         stored by ISIS. The .mod file will only be created when",
   "         using plot_unfold, and the .res file will only be created",
   "         when using plot_residuals.  ***This will overwrite old",
   "         files if \"root\" is not a unique name.***","",
   "   head- Set as a qualifier only.  If !=0 (default=1), the ASCII data",
   "         files will have useful header information appended.",
   "         Otherwise, the files will just contain columns of data."];

   switch(_NARGS)
   {
      case 1:
      root = ();
      root = string(root);
   }
   {
      msg(str);
      return;
   }

   head=qualifier("head",1);

   variable i=0, ndata=0, nres=0, nmod=0, nd=1, nr=1, nfiles;
   variable fdnames, fmnames, frnames;
   variable fst, f=struct{mlo,mhi,mval};
   
   if( pd.dval[0] != NULL )
   {
      ndata = length(pd.dval);
   }

   if( pd.res[0] != NULL )
   {
      nres = length(pd.res);
   }

   if( nres==1 or ndata==1 )
   {
       nfiles=1;
       nd=ndata;
       nr=nres;
   }
   else
   {
      nfiles=max([ndata,nres]);
   }

   if(nfiles==1)
   {
      fdnames=root+[".dat"];
      fmnames=root+[".mod"];
      frnames=root+[".res"];
   }
   else
   {
      fdnames=String_Type[0], fmnames=@fdnames, frnames=@fdnames;
      loop(nfiles)
      {
         fdnames=[fdnames,root+"_"+string(pd.index[i][0])+".dat"];
         fmnames=[fmnames,root+"_"+string(pd.index[i][0])+".mod"];
         frnames=[frnames,root+"_"+string(pd.index[i][0])+".res"];
         i++;
      }
   } 

   fpd=File_Type[nfiles], fpm=@fpd;

   variable bs="writecol(fp", pb=",pd.", ib="[i]", db, mb, j, mopen;

   for(i=0;i<nfiles;i++)
   {
      if(pd.type=="plot_unfold")
      {
         fpd[i] = fopen(fdnames[i],"w");
         write_plot_head(fpd[i],head,i,nfiles,1,nd,nr,nmod);

         db=bs+"d"+"["+string(i)+"]"+pb+"dlo"+"["+string(i)+"]"+pb
                      +"dhi"+"["+string(i)+"]";
         if(nfiles==1)
         {
            for(j=0;j<ndata;j++)
            {
               db+=pb+"dval["+string(j)+"]";
               db+=pb+"derr["+string(j)+"]";
            }
            for(j=0;j<nres;j++)
            {
               db+=pb+"res["+string(j)+"]";
               db+=pb+"res_m["+string(j)+"]";
               db+=pb+"res_p["+string(j)+"]";
            }
         }
         else
         {
            db+=pb+"dval"+"["+string(i)+"]";
            db+=pb+"derr"+"["+string(i)+"]";
            if(pd.res[i]!=NULL)
            {
               db+=pb+"res"+"["+string(i)+"]";
               db+=pb+"res_m"+"["+string(i)+"]";
               db+=pb+"res_p"+"["+string(i)+"]";
            }
	 }

         db+=");";
         eval(db,"isis_fancy_plots");
         () = fclose(fpd[i]);
    
         mopen=0; nmod=0;
         if(nfiles==1)
         {
            for(j=0;j<length(pd.mlo);j++)
            {
	       if(pd.mlo[j][0]!=NULL){ mopen=1; nmod++; };
            }
	 }
         else if(pd.mlo[i][0]!=NULL)
         {
            mopen=1; 
            nmod=1;
         }

         if(mopen)
         {
            fpm[i] = fopen(fmnames[i],"w");

            write_plot_head(fpm[i],head,i,nfiles,3,nd,nr,nmod);

            mb=bs+"m"+"["+string(i)+"]"+pb+"mlo"+"["+string(i)+"]"+pb
                         +"mhi"+"["+string(i)+"]";

            if(nfiles==1)
            {
               fst=-1;
               for(j=0;j<ndata;j++)
               {
                  if(pd.mlo[j][0]!=NULL)
                  {  
                     if(fst==-1)
                     {
                        fst=j;
                        f.mlo=pd.mlo[j];
                        f.mhi=pd.mhi[j];
                        f.mval=pd.mval[j];
                     }
                     else
                     {
                        pd.mval[j]=rebin(f.mlo,f.mhi,pd.mlo[j],pd.mhi[j],
                                     pd.mval[j]*(pd.mhi[j]-pd.mlo[j]))/
                                     (f.mhi-f.mlo);
                        pd.mlo[j]=f.mlo;
                        pd.mhi[j]=f.mhi;
                     }       
                     mb+=pb+"mval["+string(j)+"]";
                  }
               }
            }
            else
            {
               mb+=pb+"mval"+"["+string(i)+"]";
            }
            mb+=");";
            eval(mb,"isis_fancy_plots");
            () = fclose(fpm[i]);
         }
      }
      else if(pd.type=="plot_residuals")
      {
         fpd[i] = fopen(frnames[i],"w");
         write_plot_head(fpd[i],head,i,nfiles,2,nd,nr,nmod);

         db=bs+"d"+"["+string(i)+"]"+pb+"dlo"+"["+string(i)+"]"+pb
                      +"dhi"+"["+string(i)+"]";
         if(nfiles==1)
         {
            for(j=0;j<nres;j++)
            {
               db+=pb+"res["+string(j)+"]";
               db+=pb+"res_m["+string(j)+"]";
               db+=pb+"res_p["+string(j)+"]";
            }
         }
         else
         {
            db+=pb+"res"+"["+string(i)+"]";
            db+=pb+"res_m"+"["+string(i)+"]";
            db+=pb+"res_p"+"["+string(i)+"]";
	 }

         db+=");";
         eval(db,"isis_fancy_plots");
         () = fclose(fpd[i]);
      }
      else
      {

         nmod=0;
         if(nfiles==1)
         {
            for(j=0;j<length(pd.mlo);j++)
            {
	       if(pd.mlo[j][0]!=NULL){ nmod++; };
            }
	 }
         else if(pd.mlo[i][0]!=NULL)
         {
            nmod=1;
         }

         fpd[i] = fopen(fdnames[i],"w");
         write_plot_head(fpd[i],head,i,nfiles,4,nd,nr,nmod);

         db=bs+"d"+"["+string(i)+"]"+pb+"dlo"+"["+string(i)+"]"+pb
                      +"dhi"+"["+string(i)+"]";
         if(nfiles==1)
         {
            for(j=0;j<ndata;j++)
            {
               db+=pb+"dval["+string(j)+"]";
               db+=pb+"derr["+string(j)+"]";
            }
            fst=-1;
            for(j=0;j<ndata;j++)
            {
               if(pd.mlo[j][0]!=NULL)
               {  
                  if(fst==-1)
                  {
                     fst=j;
                     f.mlo=pd.mlo[j];
                     f.mhi=pd.mhi[j];
                     f.mval=pd.mval[j];
                  }
                  else
                  {
                     pd.mval[j]=rebin(f.mlo,f.mhi,pd.mlo[j],pd.mhi[j],
                                  pd.mval[j]*(pd.mhi[j]-pd.mlo[j]))/
                                  (f.mhi-f.mlo);
                     pd.mlo[j]=f.mlo;
                     pd.mhi[j]=f.mhi;
                  }       
                  db+=pb+"mval["+string(j)+"]";
               }
            }

            for(j=0;j<nres;j++)
            {
               db+=pb+"res["+string(j)+"]";
               db+=pb+"res_m["+string(j)+"]";
               db+=pb+"res_p["+string(j)+"]";
            }
         }
         else
         {
            db+=pb+"dval"+"["+string(i)+"]";
            db+=pb+"derr"+"["+string(i)+"]";
            if(pd.res[i]!=NULL)
            {
               db+=pb+"res"+"["+string(i)+"]";
               db+=pb+"res_m"+"["+string(i)+"]";
               db+=pb+"res_p"+"["+string(i)+"]";
            }
	 }

         db+=");";
         eval(db,"isis_fancy_plots");

         () = fclose(fpd[i]);
      }
  }
}



%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
%                                                % 
%     Definitions for Simple plotxy Function     %
%                                                %
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

private define set_plot_parms_struct_II( pstruct )
{
   variable list, lparm, parmde=0, parmre=0;

   foreach(parms)
   {
      list = ();
      if(qualifier_exists(list))
      {
         set_struct_field(pstruct,list,qualifier(list,NULL));
      }
      if(struct_field_exists( pstruct, list ))
      {
         if(typeof(get_struct_field( pstruct, list )) != Null_Type)
         {
            if(list=="xrng" or list=="yrng")
            {
               set_struct_field( parm_s,list,get_struct_field(pstruct,list));
            }
            else
            {
               set_struct_field( parm_s,list,[get_struct_field(pstruct,list)]);
            }
            if(list=="decol") { parmde=1; }
            if(list=="recol") { parmre=1; }
         }
      }
   }

   % Transfer over to the variables

   dcol[0] = parm_s.dcol[0];

   decol = @dcol;   % Default error bar colors to data
   if( parmde == 1 )
   {
      decol[0] = parm_s.decol[0];
   }

   dsym[0] = parm_s.dsym[0];

   yrng = parm_s.yrng;
   xrng = parm_s.xrng;
   oplt = parm_s.oplt[0];
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

private define set_plot_parms_assoc_II( args )
{
   variable largs = length(args);
   variable ivars = 0;

   do
   {
      ivars++;
   }
   while ( typeof(args[ivars].value) != String_Type );

   variable lkeys = (largs-ivars)/2;
   variable i,key, keyde=0, keyre=0;
   
   for(i=0; i<=lkeys-1; i++)
   {
      key = args[2*i+ivars].value;

      if(key=="decol") { keyde=1; }
      if(key=="recol") { keyre=1; }

      if(assoc_key_exists( parm_a, key ))
      {
         parm_a[key] = [args[2*i+ivars+1].value];
      }
   }

   % Transfer over to the variables

   parm_s.dcol = parm_a["dcol"];
   if(keyde)
   {
      parm_s.decol = parm_a["decol"];
   }
   else
   {
      parm_s.decol = @parm_s.dcol;
   }

   parm_s.dsym = parm_a["dsym"];
   parm_s.yrng = parm_a["yrng"];
   parm_s.xrng = parm_a["xrng"];

   set_plot_parms_struct_II(parm_s);

   return ivars;
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

private define set_plot_parms_II(nargs,args)
{
   variable ivars=nargs;

   if(nargs > 2)
   {
      if(typeof(args[nargs-2].value) == String_Type)
      {
         % Presume it is an associative array argument list

         ivars = set_plot_parms_assoc_II(args);
      }
      else if(is_struct_type(args[nargs-1].value))
      {
         % Presume arguments are passed in a structure

         set_plot_parms_struct_II(args[nargs-1].value);

         ivars = nargs-1;
      }
      else
      {
         set_plot_parms_struct_II(parm_s;;__qualifiers());
      }
   }
   else
   {
      set_plot_parms_struct_II(parm_s;;__qualifiers());
   }
   return ivars;
}

%%%%%%%%%%%%%%%

public define plotxy()
{
   variable args, ivars, x, xx, xm, xp, y, yy, ym, yp;
   plopt = get_plot_options;

variable str = [
" plotxy(x,xm,xp,y,ym,yp,pstruct); % pstruct = struct{dcol, decol, xrng, ...}",
" plotxy(x,xm,xp,y,ym,yp,\"dcol\",dcol,\"decol\",decol,...);",
" plotxy(x,xm,xp,y,ym,yp;dcol=dcol,decol=decol,...);"," ",
" Also accepts:"," ", 
" plotxy(x,y,options);",
" plotxy(x,xm,xp,y,options); ",
" plotxy(x,,,y,ym,yp,options); ",
" ", 
"   Plot simple x,y plots with error bars. xm = x-dx, xp = x+dx, etc."," ",
"   Options below refer to structure variables/associative keys/qualifiers:",
" ",
"    dcol = (pgplot) color of data",
"    decol= (pgplot) color of data",
"    dsym = (pgplot) symbol number for data",
"    yrng = (Array *or* List) Y-limits for the data",
"    xrng = (Array *or* List) X-limits for the data",
"           Note: To autoscale ranges, set NULL in *list* for that value",
"    oplt = 0 (default) for new plot, !=0 for overplotting"];

   if(_NARGS >0)
   {
      args = __pop_args(_NARGS);
      set_plot_defaults(1);
   }
   else
   { 
      msg(str);
      return;
   }

   ivars = set_plot_parms_II(_NARGS,args;;__qualifiers);

   if(ivars==2)
   {
      x = args[0].value;
      y = args[1].value;
      xm = NULL; xp = NULL; ym = NULL; yp = NULL;
   }
   else if(ivars==4)
   {
      x = args[0].value;
      xm = args[1].value;
      xp = args[2].value;
      y = args[3].value;
      ym = NULL; yp = NULL;
   }
   else if(ivars==6)
   {
      x = args[0].value;
      xm = args[1].value;
      xp = args[2].value;
      y = args[3].value;
      ym = args[4].value;
      yp = args[5].value;
   }
   else
   {
      msg(str);
      return;
   }

   if(length(xrng)>1)
   {
      if((xrng[0]!=NULL) && (xrng[0]<=0))
      {
         xlin;
      }
      xrange(xrng[0],xrng[1]);
   }
   if(length(yrng)>1)
   {
      if((yrng[0]!=NULL) && (yrng[0]<=0))
      {
         ylin;
      }

      yrange(yrng[0],yrng[1]);
   }

   set_line_width(int(d_width));
   point_style(dsym[0]);

   if(oplt==0)
   {
      plot(x,y,int(dcol[0]));
   }
   else
   {
      oplot(x,y,int(dcol[0]));
   }

   xx = @x*1.;
   yy = @y*1.;

   if(get_plot_options().logx) 
   { 
      xx[where(xx <= 0)] = 1.5e-32;
      xx = log10(xx);
   }
   if(get_plot_options().logy) 
   { 
      yy[where(yy <= 0)] = 1.5e-32;
      yy = log10(yy);
   }

   if( xm != NULL or xp != NULL )
   {
      if( xm == NULL ) xm = x;
      if( xp == NULL ) xp = x;

      xm=1.*xm; xp=1.*xp;

      if(get_plot_options().logx) 
      { 
         xm[where(xm <= 0)] = 1.e-32;
         xp[where(xp <= 0)] = 2.e-32;

         xm = log10(xm); 
         xp = log10(xp); 
      }

      _pgslw(int(de_width));
      _pgsci(int(decol[0]));
      () = _pgerrx(length(x),xm,xp,yy,ebar_term_length_x);
   }

   if( ym != NULL or yp != NULL )
   {
      if( ym == NULL ) ym = y;
      if( yp == NULL ) yp = y;

      ym=1.*ym; yp=1.*yp;

      if(get_plot_options().logy) 
      { 
         ym[where(ym <= 0)] = 1.e-32;
         yp[where(yp <= 0)] = 2.e-32;

         ym = log10(ym); 
         yp = log10(yp); 
      }

      _pgslw(int(de_width));
      _pgsci(int(decol[0]));
     () = _pgerry(length(x),xx,ym,yp,ebar_term_length_y);
   }

   set_line_width(int(d_width));
   point_style(dsym[0]);

   oplot(x,y,int(dcol[0]));
   color(dcol[0]);
}

%%%%%%%%%%%%%%%%%%%

public define sov()
{
   variable v=struct{xmin,xmax,ymin,ymax};
   switch(_NARGS)
   {
    case 4:
      (v.xmin,v.xmax,v.ymin,v.ymax)=();
      set_outer_viewport(v);
      return;
   }
   {
      variable str = [
      " sov(xmin,xmax,ymin,ymax); ","",
      "  Equivalent to:",
      "     isis> v=struct{xmin,xmax,ymin,ymax};",
      "     isis> v.xmin=xmin;",
      "     isis> v.xmax=xmax;",
      "     isis> v.ymin=ymin;",
      "     isis> v.ymax=ymax;",
      "     isis> set_outer_viewport(v);"];
      msg(str);
   }
}

%%%%%%%%%%%%%%%%%%%%%%%%
%                      %
%   More aliases ...   %
%                      %
%%%%%%%%%%%%%%%%%%%%%%%%

alias("plot_data","plot_bsub_all");
alias("plot_fit_model","plot_bsub_model");
alias("plot_counts","plot_bsub_counts");
alias("write_plot","writeplot");

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


% Last Updated: July 29, 2007

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

% Public Functions in This File.  Usage message (almost always) when 
% function is called without arguments.

% keV_note      : Notice positive valued bins (cued from *first* data
%                 set, based upon energy cuts.  (Positive value 
%                 restriction ties into radio data functions later on.)  
%                 Also works on combined datasets.
% keV_note_sum  : Same as above, but cues off of summed data.
% chan_note     : Notice positive valued bins based upon channels
%
%  !!! The following will also group for combined datasets !!!
%
% grppha        : Rebin data from lowest keV bound to a min counts/bin
% grppha_cut    : Rebin data from specified keV bound to a min counts/bin
% grppha_min    : ... and add in a min number of channels per bin
% grppha_sn     : ... from specified bound, to a min S/N per bin
% grppha_sn_min : ... from specified bound, to a min S/N per bin, & 
%                 minimum number of channels per bin
% group         : A simplified version that combines all of the above
%
%  !!!  These only work on one data set at a time !!!
%
% i2x_grp       : Turn an ISIS grouping into a GRPPHA/XSPEC one 
% write_x_grp   : ... take that grouping and apply it to a file
% apply_i2x_grp : Do the above two in one fell swoop
% x2i_grp       : Turn an XSPEC/GRPPHA grouping into an ISIS one
% apply_x2i_grp : ... and apply it in one fell swoop
   
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

public define keV_note()
{
   variable a,b,c,data,bin_list,bin_list_a,dindx;
   bin_list = Integer_Type[0];
   switch(_NARGS)
   {
    case 3:
      (a,b,c) = ();
      a = [a];
      if ( min(b) != 0. ) b = _A(b); else return 1;
      if ( min(c) != 0. ) c = _A(c); else return 1;
      b = [b];
      c = [c];
      if ( length(c) != length(b)) return 1;
      data = get_data_counts(a[0]);

      variable i=0;
      loop(length(b))
      {
         bin_list_a = where( data.value > 0. and data.bin_lo >= c[i]
                           and data.bin_hi < b[i] );
         bin_list = [bin_list,bin_list_a];
         i++;
      }

      foreach(a)
      {
         dindx = ();
         ignore(dindx);
         notice_list(dindx,bin_list);
      }
      return;
   }
   {
      variable str = [" keV_note([a],[minkev1,minkev2,...],[maxkev1,maxkev2,...]);"," ",
      "   Exclusively notice data between energy ranges [minkev1:maxkev1],",
      "   [minkev2:maxkev2],..., for data sets [a], where the data sets are",
      "   on a common grid. Note that for binned data minkev < E.bin_lo *and*",
      "   E.bin_hi <= maxkev to be noticed. Bins with zero *in the **first** data set*",
      "   cause that bin to be excluded in all data sets."];
      msg(str);
      return;
   }
} 

%%%%%%%%%%%%%%%%%%%%%%%%%%%%

public define keV_note_sum()
{
   variable a,b,c,data,datasum=0,bin_list,bin_list_a,dindx;
   bin_list = Integer_Type[0];
   switch(_NARGS)
   {
    case 3:
      (a,b,c) = ();
      a = [a];
      if ( min(b) != 0. ) b = _A(b); else return 1;
      if ( min(c) != 0. ) c = _A(c); else return 1;
      b = [b];
      c = [c];
      if ( length(c) != length(b)) return 1;
      data = get_data_counts(a[0]);

      foreach(a)
      {
         dindx = ();
         datasum += get_data_counts(dindx).value;
      }

      variable i=0;
      loop(length(b))
      {
         bin_list_a = where( datasum > 0. and data.bin_lo >= c[i]
                           and data.bin_hi < b[i] );
         bin_list = [bin_list,bin_list_a];
         i++;
      }

      foreach(a)
      {
         dindx = ();
         ignore(dindx);
         notice_list(dindx,bin_list);
      }
      return;
   }
   {
      variable str = [" keV_note([a],[minkev1,minkev2,...],[maxkev1,maxkev2,...]);"," ",
      "   Exclusively notice data between energy ranges [minkev1:maxkev1],",
      "   [minkev2:maxkev2],..., for data sets [a], where the data sets are",
      "   on a common grid. Note that for binned data minkev < E.bin_lo *and*",
      "   E.bin_hi <= maxkev to be noticed.  Bins with 0 counts **in the sum**",
      "   are ignored"];
      msg(str);
      return;
   }
} 

%%%%%%%%%%%%%%%%%%%%%%%%%

public define chan_note()
{
   variable a,b,c,data,bin_list,bin_list_a;
   bin_list = Integer_Type[0];
   switch(_NARGS)
   {
    case 3:
      (a,b,c) = ();
      if ( min(b) < 0 ) return 1;
      if ( min(c) < 0 ) return 1;
      b = [b];
      c = [c];
      if ( length(c) != length(b)) return 1;
      data = get_data_counts(a);
      ignore(a);
      variable i;
      if(Isis_Reverse_Channels == 1)
      {
         i=length(b)-1;
         loop(length(b))
         {
            bin_list = [bin_list,[c[i]:b[i]:-1]];
            i--;
         }
         bin_list = length(data.value) -1 - bin_list;
         print("");
         print("Selecting Based on ENERGY Channels (first channel is 0)");
         print("");
      }
      else
      {
         i=0;
         loop(length(b))
         {
            bin_list = [bin_list,[b[i]:c[i]]];
            i++;
         }
         print("");
         print("Selecting Based on WAVELENGTH Channels (first channel is 0)");
         print("");
      }
      notice_list(a,bin_list);
      return;
   }
   {
      variable str = [" chan_note(a,[minchan1,minchan2,...],[maxchan1,maxchan2,...]);"," ",
      "   Exclusively notice data between channel ranges [minchan1:maxchan1],",
      "   [minchan2:maxchan2],..., for data set a.  Channel numbers are based upon",
      "   GROUPED values (i.e., as in XSPEC)"];
      msg(str);
      return;
   }
} 

alias("keV_note", "kev_note"); 
alias("keV_note", "keVnote");
alias("keV_note","kevnote");
alias("keV_note_sum", "kev_note_sum"); 
alias("keV_note_sum", "keVnote_sum");
alias("keV_note_sum","kevnote_sum");
alias("keV_note_sum","kevnotesum");
alias("chan_note","channote");
alias("chan_note","chan_not");
alias("chan_note","channot");

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

%  Begin a series of grouping functions, to mimic ftools a bit

%%%%%%%%%%%%%%%%%%%%%%%%%%

public define grppha_min()
{
   variable a,minc,mincc,minkev;
   switch(_NARGS)
   {
    case 4:
      (a,minc,mincc,minkev)=();
       a = [a];
   }
   {
      variable str = [" grppha_min(a,mincounts,minchans,minkev);", " ",
       "   Rebin (possibly combined) data sets, from a specified lower keV bound,",
       "   to a minimum counts per bin and a minimum number of channels per bin.",
       "   First bins, below the minkev cutoff, are left unbinned."," ",
       "   a = indices of data sets.  Data sets with negative indices will",
       "       be grouped, but not included in the calculation of minimum counts",
       "   mincounts = minimum counts per bin",
       "   minkev = lower energy bound for grouping start"];
      msg(str);
      return;
   }

   variable la = length(a);
   variable s = Struct_Type[la];

   variable ii;
   for (ii=0; ii <= la-1; ii++)
   {
      rebin_data(abs(a[ii]),0);
      s[ii] = get_data_counts(abs(a[ii]));
   }

   variable len = length(s[0].value);
   variable idx = Integer_Type[len] + 1;
   variable sn = 1;
   variable ssum = 0.;
   variable nchan = 0;

   variable i;
   for (i=len-1; i >=0; i--)
   {
      idx[i] = sn*idx[i];

      for (ii=la-1; ii >= 0; ii--)
      {
         if(a[ii]>0)
         {
            ssum += s[ii].value[i];
         }
      }

      nchan++;

      if( (_A(s[0].bin_hi[i]) <= minkev) or (ssum >= minc and nchan >= mincc) )
      {
         sn = sn*(-1);
         ssum = 0.;
         nchan = 0;
      }
   }

   for (ii=la-1; ii >= 0; ii--)
   {
      rebin_data(abs(a[ii]),idx);
   }
   return;
}

%%%%%%%%%%%%%%%%%%%%%%%%%%

public define grppha_cut()
{
   variable a,minc,minkev;
   switch(_NARGS)
   {
    case 3:
      (a,minc,minkev)=();
      grppha_min(a,minc,1,minkev);
      return;
   }
   {
      variable str = [" grppha_cut(a,mincounts,minkev);", " ",
       "   Rebin (possibly combined) data sets, from a specified lower keV bound,",
       "   to a minimum counts per bin. First bins, below the minkev cutoff,",
       "   are left unbinned."," ",
       "   a = indices of data sets.  Data sets with negative indices will",
       "       be grouped, but not included in the calculation of minimum counts",
       "   mincounts = minimum counts per bin",
       "   minkev = lower energy bound for grouping start"];
      msg(str);
      return;
   }
}

%%%%%%%%%%%%%%%%%%%%%%%

public define grppha()
{
   variable a,mincounts;
   switch(_NARGS)
   {
    case 2:
      (a,mincounts)=();
      grppha_min(a,mincounts,1,0.);
      return;
   }
   {
      variable str = [" grppha(a,mincounts);"," ",
       "   Rebin data set, from its lower keV bound, to a minimum counts", 
       "   per bin.","",
       "   a = indices of data sets.  Data sets with negative indices will",
       "       be grouped, but not included in the calculation of minimum counts",
       "   mincounts = minimum counts per bin"];
      msg(str);
      return;
   }
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%



public define grppha_sn_min()
{
   variable a,minsn,minchan,minkev;
   switch(_NARGS)
   {
    case 4:
      (a,minsn,minchan,minkev)=();
      a = [a];
   }
   {
      variable str = [" grppha_sn_min([a,b,...],minsn,minchan,minkev);", " ",
       "   Rebin (possibly combined) data sets, from a specified lower keV bound,",
       "   to a minimum signal-to-noise in the combined dataset, and a minimum",
       "   number of channels per bin (number of channels for one dataset).",
       "   First bins, below the minkev cutoff, are left unbinned.",
       "   Reads the background data set, if it exists, and presumes the total",
       "   noise goes as: ",
       "      sqrt( (Total Counts) + (Back Counts)*(Back Scale*Exp. Ratio)^2 )"," ",
       "   [a,b,...] = indices of data sets. Negative indices are grouped,",
       "               but not used in calculation of S/N.  Datasets must",
       "               be on the same starting grid.",
       "   minsn = minimum signal-to-noise ratio per bin",
       "   minchan = minimum number of channels per bin",
       "   minkev = lower energy bound for grouping start"];
      msg(str);
      return;
   }
   
   variable la = length(a);
   variable s = Struct_Type[la], b = Array_Type[la], scl = Float_Type[la];
   variable bfile, bscl, bexp, dscl, dexp, nchan=0;

   variable ii;
   for (ii=0; ii <= la-1; ii++)
   {
      rebin_data(abs(a[ii]),0);
      s[ii] = get_data_counts(abs(a[ii]));
      b[ii] = get_back(abs(a[ii]));
      scl[ii] = 1.;

      if(b[ii] == NULL)
      {
         b[ii] = Float_Type[length(s[ii].value)];
      }
      else
      { 
         dscl = get_data_backscale(abs(a[ii]))[0];
         dexp = get_data_exposure(abs(a[ii]));

         bfile = get_data_info(abs(a[ii])).bgd_file;
         if(bfile != NULL and bfile != "#_define_bgd()")
         {
            bexp = fits_read_key(bfile,"EXPOSURE");
            bscl = fits_read_key(bfile,"BACKSCAL");

            if(bexp == NULL or bscl == NULL or bexp == 0 or bscl == 0)
            {
               scl[ii] = 1.;
            }
            else 
            {
               scl[ii]= (dexp*dscl)/(bexp*bscl);
            }
         }
         else
         {
            scl[ii] = 1;
         }

      }
   }

   variable len = length(s[0].value);
   variable idx = Integer_Type[len] + 1;
   variable sn = 1;
   variable ssum = 0.;
   variable bsum = 0.;
   variable bsum_scl = 0.;

   variable i;
   for (i=len-1; i >=0; i--)
   {
      idx[i] = sn*idx[i];

      for (ii=0; ii <= la-1; ii++)
      {
         if(a[ii]>0)
         {
            ssum += s[ii].value[i];
            bsum += b[ii][i];
            bsum_scl += b[ii][i]*scl[ii];
         }
      }

      nchan++;

      if (_A(s[0].bin_hi[i]) <= minkev) 
      {
         sn = sn*(-1);
         ssum = 0.;
         bsum = 0.;
         bsum_scl = 0.;
         nchan=0;
      }

      if(ssum > 0){
         if ( (ssum-bsum) / sqrt(ssum+bsum_scl) >= minsn and nchan >= minchan)
         {
            sn = sn*(-1);
            ssum = 0.;
            bsum = 0.;
            bsum_scl = 0.;
            nchan=0;
         }
      }
   }

   foreach(a)
   {
      ii = ();
      rebin_data(abs(ii),idx);
   }
   return;
}


%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


public define grppha_sn_min_array()
{
   variable a,minsn,minchan,minkev,lchans,ichans=0;
   switch(_NARGS)
   {
    case 4:
      (a,minsn,minchan,minkev)=();
      a = [a];
      minchan = [minchan];
      minkev = [minkev];
      lchans = length(minchan);
      if(lchans != length(minkev))
      {
	 print("minchan and minkev arrays have different lengths");
         return;
      }
   }
   {
      variable str = [" grppha_sn_min([a,b,...],minsn,[minchan],[minkev]);", " ",
       "   Rebin (possibly combined) data sets, from a specified *array* of lower",
       "   keV bounds, to a minimum signal-to-noise in the combined dataset, and an",
       "   *array* of minimum number of channels per bin (number of channels for one",
       "   dataset).  The [minchan] array corresponds to the [minkev] array, and the",
       "   latter is assumed to be in energ ascending order.  First bins, below the",
       "   minkev cutoff, are left unbinned.  This reads the background data set, if", 
       "   it exists, and presumes the total noise goes as: ",
       "      sqrt( (Total Counts) + (Back Counts)*(Back Scale*Exp. Ratio)^2 )"," ",
       "   [a,b,...] = indices of data sets. Negative indices are grouped,",
       "               but not used in calculation of S/N.  Datasets must",
       "               be on the same starting grid.",
       "   minsn     = minimum signal-to-noise ratio per bin",
       "   [minchan] = array of minimum number of channels per bin",
       "   [minkev]  = array of lower energy bounds for grouping start/changes"];
      msg(str);
      return;
   }
   
   variable la = length(a);
   variable s = Struct_Type[la], b = Array_Type[la], scl = Float_Type[la];
   variable bfile, bscl, bexp, dscl, dexp, nchan=0;

   variable ii;
   for (ii=0; ii <= la-1; ii++)
   {
      rebin_data(abs(a[ii]),0);
      s[ii] = get_data_counts(abs(a[ii]));
      b[ii] = get_back(abs(a[ii]));
      scl[ii] = 1.;

      if(b[ii] == NULL)
      {
         b[ii] = Float_Type[length(s[ii].value)];
      }
      else
      { 
         dscl = get_data_backscale(abs(a[ii]))[0];
         dexp = get_data_exposure(abs(a[ii]));

         bfile = get_data_info(abs(a[ii])).bgd_file;
         if(bfile != NULL and bfile != "#_define_bgd()")
         {
            bexp = fits_read_key(bfile,"EXPOSURE");
            bscl = fits_read_key(bfile,"BACKSCAL");

            if(bexp == NULL or bscl == NULL or bexp == 0 or bscl == 0)
            {
               scl[ii] = 1.;
            }
            else 
            {
               scl[ii]= (dexp*dscl)/(bexp*bscl);
            }
         }
         else
         {
            scl[ii] = 1;
         }

      }
   }

   variable len = length(s[0].value);
   variable idx = Integer_Type[len] + 1;
   variable sn = 1;
   variable ssum = 0.;
   variable bsum = 0.;
   variable bsum_scl = 0.;

   variable i;
   for (i=len-1; i >=0; i--)
   {
      idx[i] = sn*idx[i];

      for (ii=0; ii <= la-1; ii++)
      {
         if(a[ii]>0)
         {
            ssum += s[ii].value[i];
            bsum += b[ii][i];
            bsum_scl += b[ii][i]*scl[ii];
         }
      }

      nchan++;

      if (_A(s[0].bin_hi[i]) <= minkev[0]) 
      {
         sn = sn*(-1);
         ssum = 0.;
         bsum = 0.;
         bsum_scl = 0.;
         nchan=0;
      }

      if(ssum > 0){

         ichans = max( where( minkev <  _A(s[0].bin_hi[i]) ) );

         if ( (ssum-bsum) / sqrt(ssum+bsum_scl) >= minsn and nchan >= minchan[ichans])
         {
            sn = sn*(-1);
            ssum = 0.;
            bsum = 0.;
            bsum_scl = 0.;
            nchan=0;
         }
      }
   }

   foreach(a)
   {
      ii = ();
      rebin_data(abs(ii),idx);
   }
   return;
}

%%%%%%%%%%%%%%%%%%%%%%%%%

public define grppha_sn()
{
   variable a,minsn,minkev;
   switch(_NARGS)
   {
    case 3:
      (a,minsn,minkev)=();
      grppha_sn_min(a,minsn,1,minkev);
      return;
   }
   {
      variable str = [" grppha_sn([a,b,...],minsn,minkev);", " ",
       "   Rebin (possibly combined) data sets, from a specified lower keV bound,",
       "   to a minimum signal-to-noise in the combined dataset. First bins, below",
       "   the minkev cutoff, are left unbinned.  Reads the background data set,",
       "   if it exists, and presumes the total noise goes as: ",
       "      sqrt( (Total Counts) + (Back Counts)*(Back Scale*Exp. Ratio)^2 )"," ",
       "   [a,b,...] = indices of data sets. Negative indices are grouped,",
       "               but are not used in calculation of S/N.  Datasets must",
       "               be on the same starting grid.",
       "   minsn = minimum signal-to-noise ratio per bin",
       "   minkev = lower energy bound for grouping start"];
      msg(str);
      return;
   }
}

%%%%%%%%%%%%%%%%%%%%%

public define group()
{
   variable ind=Integer_Type[0],kev,chan,sn;

   switch(_NARGS)
   {
    (_NARGS>=1):
      variable i, list = __pop_list(_NARGS);
      _for i (0,length(list)-1,1)
      {
         ind = [ind,list[i]];
      }
   }
   {
      variable str = [" group([a,b,...]; qualifiers);", " ",
       "   Rebin (possibly combined) data sets, from specified lower keV bounds (optional),",
       "   to a minimum signal-to-noise (default=4.5) *and* a minimum number of channels",
       "   (default=1) in each grouped bin. Any channels below the minum keV cutoff are",
       "   left unbinned.  The background data set, if it exists, is included in the S/N",
       "   calculation. Group presumes that the total noise goes as: ","",
       "      sqrt( (Total Counts) + (Back Counts)*(Back Scale*Exp. Ratio)^2 )"," ",
       " Inputs:","",
       "   [a,b,...] = indices of data sets. Negative indices are grouped but are not used",
       "               in calculation of S/N.  Datasets must be on the same starting grid.",
       " ",
       " Qualifiers:","",
       "   sn   = minimum signal-to-noise ratio per bin (default=4.5)",
       "   kev  = single value or array of lower bounds for the start of each minimum",
       "          number of channels specification (default=0)",
       "   chan = minimum number of channels (default=1) required per bin, starting at the",
       "          corresponding specified keV bound, and proceeding to higher energies"];
      msg(str);
      return;
   }
      sn = qualifier("sn",4.5);
      kev = qualifier("kev",0);
      kev = [kev];
      chan = qualifier("chan",1);
      chan = [chan];
      if(length(chan) != length(kev))
      {
         () = printf("\n keV and minimum channel array have different lengths. \n\n");
         return;
      }
      grppha_sn_min_array(ind,sn,chan,kev);
      return;

}

%%%%%%%%%%%%%%%%%%%%%%%

public define i2x_grp()
{
   switch(_NARGS)
   {
    case 1:
      variable i, lb, ub, id, gdi, lgdi, iw, liw;

      id = ();
      gdi = [reverse(get_data_info(id).rebin),5];
      lgdi = length(gdi)-1;

      variable grp = Integer_Type[lgdi];

      iw = [where( shift(gdi,-1) != gdi )];
      liw = length(iw);
      iw[liw-1] = lgdi;

      grp[iw[[0:liw-2]]] = 1;

      foreach i ([0:liw-2])
      {
         lb = iw[i];
         ub = iw[i+1]-1;
         if(ub>lb)
         {
            grp[[lb+1:ub]] = -1;
         }
      }
      
      return grp;
   }
   {
      variable str = [" grouping = i2x_grp(id);"," ",
      "   For any dataset, id, grouped in ISIS, create an *energy ordered*",
      "   grouping vector that follows the grppha conventions.  (ISIS groups",
      "   flagged with 0, however, become *noticed* groups of 1.)"];
      msg(str);
      return;
   }
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%

public define write_x_grp()
{
   switch(_NARGS)
   {
    case 2:
      variable id, grouping, file, fp, colnum;

      (id, grouping) = ();
      file = get_data_info(id).file;

      fp = fits_open_file(file+"[SPECTRUM]","w");
      () = _fits_get_colnum(fp, "grouping", &colnum);
      () = _fits_write_col(fp,colnum,1,1,grouping);
      fits_close_file(fp);

      return;
   }
   {
      variable str = [" write_x_grp(id,grouping);"," ",
      "   Write an XSPEC style grouping to the *file* associated with id.",
      "   *Grouping vector must follow grppha conventions and be energy ordered.*"," ",
      "   See: grouping = i2x_grp(id);"];
      msg(str);
      return;
   }
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

public define apply_i2x_grp()
{
   switch(_NARGS)
   {
    case 1:
      variable id, grp;

      id = ();
      grp = i2x_grp(id);
      write_x_grp(id,grp);

      return;
   }
   {
      variable str = [" apply_i2x_grp(id);"," ",
      "   Take the ISIS defined grouping from dataset id, create an XSPEC style",
      "   grouping based upon that, and then write that grouping to the",
      "   associated file"];
      msg(str);
      return;
   }
}

%%%%%%%%%%%%%%%%%%%%%%%

public define x2i_grp()
{
   switch(_NARGS)
   {
    case 1:
      variable id, file, grp, lgrp, iw, liw, sn, i;

      id = ();
      file = get_data_info(id).file;
      grp = fits_read_col(file,"grouping");

      lgrp = length(grp);
      iw = where(grp == 1);
      liw = length(iw);
      iw = [iw,lgrp];

      sn = 1;
      foreach i ([0:liw-1])
      {
         grp[[iw[i]:max([iw[i],iw[i+1]-1])]] = sn;
         sn = -1*sn;
      }

      return reverse(grp);
   }
   {
      variable str = [" grouping = x2i_grp(id);"," ",
      "   Read an XSPEC style *energy ordered* grouping, associated with the",
      "   dataset indicated by id, and convert it to a *wavelength ordered* ISIS",
      "   style grouping suitable for use in rebinning, i.e., ",
      "      isis> rebin_data(indx,grouping);"];
      msg(str);
      return;
   }
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

public define apply_x2i_grp()
{
   switch(_NARGS)
   {
    case 1:
      variable id, grp; 

      id = ();
      grp = x2i_grp(id);
      rebin_data(id,grp);

      return;
   }
   {
      variable str = [" apply_x2i_grp(id);"," ",
      "   Take the XSPEC style grouping from the *file* associated with",
      "   dataset id, convert it to an ISIS style, wavelength ordered",
      "   grouping, and apply it to dataset id."];
      msg(str);
      return;
   }
}

