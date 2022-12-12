
var elehref; 

// Flags
var rule_condition1 		= false; // Consecutive Days
var rule_condition2 		= false; // Unique six/seven days
var rule_condition3 		= false; // California state
var first_row_id 		= '';

var changeEventCalled = false;

// WEEK RULE SPECIFIC
var week_dates	= new Array();
var week_days	= new Array();

var week_start_day	= '';
var week_end_day	= '';
var tstype = 'Saved';
var module = "Accounting";
var mode = 'edit';
if (module == "MyProfile" && (typeof frm_addr === 'undefined')) {
	var tstype = '';
}else if(module == "MyProfile" && frm_addr!= undefined){
	var tstype = frm_addr;
}

var cico_pipo_rule = 'N';
var rule_type = '';
var maxregularhours = 0;
var maxregularhours_perweek = 0;
var maxovertimehours_perweek = 0;

$(document).ready(function() {

	$('form').attr('autocomplete', 'off');
	// modifying
	chainNavigation();

	var el	= document.getElementById('el');

	 $("#column_select").change(function() {         
		($(this).val() == "col1") ? $("#layout_select1").addClass("erimShow") : $("#layout_select1").removeClass("erimShow");
		($(this).val() == "col2") ? $("#layout_select2").addClass("erimShow") : $("#layout_select2").removeClass("erimShow");
	});

	// Arrow UP/Down Main Function
	$("#MainTable tr.tr_clone input[type=text]" ).each( function( i, el ) {
		var iclass = $(el).attr('class');
		$(':input.'+iclass).bind('focus', function() {
			$(this).select();
		}).bind('keydown', function(e) {
			if (e.which === 40) {
				var $next = $(this).data('next');
				if ($next != null) {
					$next.select();
				}
			} else if (e.which === 38) {
				var $prev = $(this).data('prev');
				if ($prev != null) {
					$prev.select();
				}
			}
		});
	});

	var cindex	= $('#MainTable tr.tr_clone').length - 1;
	
	//for setting the positions of calenders
	$("#tcalico_0, #tcalico_1").click(function(){
		$("#tcal").css("position","fixed");
		$("#tcalShade").css("position","fixed");
	});

	cico_pipo_rule = $("#cico_pipo_rule").val();
	rule_type = $("#rule_type").val();

	if(window.location.href.indexOf('Accounting/Time_Mngmt/cico_edit') > -1)
	{
			applyDayRulesToDay();
	}
	else
	{
		rule_type = $("#rule_type").val();
		if(rule_type == "weekrule")
		{
				applyWeekRulesToWeekly();
		}
		else
		{
				applyDayRulesToWeekly();
		}
		
	}
	if(window.location.href.indexOf("BSOS/Accounting/Time_Mngmt/cico_timesheet_edit.php") > -1)
	{
		var added_rowIds = [];
		$(".tr_clone").each(function(rowId,element){

				if($.inArray($(element).data("thsno"),added_rowIds) == -1)
				{
					loadRowZebraDatePicker(rowId,true);
					added_rowIds.push($(element).data("thsno"));
				}
				else
				{
					loadRowZebraDatePicker(rowId,false);
				}
				
		});
			
	}
	else
	{
		loadZebraDatePicker();
	}
	
});

function delete_row(del_row_id)
{
	var i	= 0;
	var dayhrs	= 0;
	
	var rowid = del_row_id.split("_").pop(-1);;
	//var rowid = splitid.pop();
	$('#check_'+rowid).attr('checked', true);// checking the hidden check box which is going to delete

	var totinputs		= $("#MainTable input.chremove[type=checkbox]").length;
	var totcheckedInputs	= $("#MainTable input.chremove[type=checkbox]:checked").length;

	if (totcheckedInputs!=0) {

		if (parseInt(totinputs) == parseInt(totcheckedInputs)) {

			alert("Your timesheets must have atleast one entry. You can't delete all the entries.");
			return false;

		} else {

			$('#MainTable input.chremove[type=checkbox]').each(function() {

				if (this.checked) {

					id	= this.id;
					splitid	= id.split('_');
					cur_chk	= splitid.pop(-1);					
					
					deleted_rowid = $("#deleted_rowids").val();
					if(deleted_rowid != "")
					{
						$("#deleted_rowids").val($("#deleted_rowids").val()+","+cur_chk);
					}else{
						$("#deleted_rowids").val(cur_chk);
					}

					deleted_sno = $("#deleted_snos").val();
					var cicoSno = $("#cicosno_"+cur_chk).val();
					if(deleted_sno != "")
					{
						$("#deleted_snos").val($("#deleted_snos").val()+","+cicoSno);
					}else{
						$("#deleted_snos").val(cicoSno);
					}
					// Removing the Row from CICO
					$("tr#row_"+cur_chk).remove();

				}
				i++;
			});
		}

	} else {

		alert("You have to select atleast one timesheet entry to delete from the available list.");
		return false;
	}

	chainNavigation(rowid);
	reCalculateCICOHrs();

}

//To Handle issue in IE where popup appears for every link even for submit and cancel also
(function($) {
   $(document).ready(function() {
 
       $('a').filter(function() {
           return (/^javascript\:/i).test($(this).attr('href'));
       }).each(function() {
           var hrefscript = $(this).attr('href');
           hrefscript = hrefscript.substr(11);
           $(this).data('hrefscript', hrefscript);
       }).click(function() {
           var hrefscript = $(this).data('hrefscript');
           eval(hrefscript);
           return false;
       }).attr('href', '#');
 
   });   
})(jQuery);


function validateTime(fld_value) {

	if (fld_value == "" || fld_value.indexOf(":") < 0) {

		return false;

	} else {

		var sMinutes	= "";
		var sHours		= fld_value.split(':')[0];
		var tMinutes	= fld_value.split(':')[1];

		if (fld_value.substring(6).toUpperCase() == "AM" || fld_value.substring(6).toUpperCase() == "PM")
		sMinutes	= tMinutes.split(" ")[0];

		if (sHours == "" || isNaN(sHours) || parseInt(sHours) > 23) {

			return false;
		}

		if (sMinutes == "" || isNaN(sMinutes) || parseInt(sMinutes) > 59) {

			return false;
		}
	}

	return true;
}

//Function used to calculate the hours when entering the times in onchange event
function calculateTime(inout_rowid,frm='') {

	var pre_inflag	= false;
	var pre_outflag	= false;
	var tot_pre_hours	= 0.00;
	var rowid	= inout_rowid.split("_").pop(-1);
	//var sel_date		= $("#daily_dates_" + rowid).val();
	var start_date		= $("#csdate_" + rowid).val();
	var end_date		= $("#cedate_" + rowid).val();
	var pre_intime		= $("#pre_intime_"+rowid).val();
	var pre_outtime		= $("#pre_outtime_"+rowid).val();
	
	//validating the each time in and time out fields
	if (pre_intime != "") {

		pre_inflag	= validateTime(pre_intime);
		/* filluptime = new Date(start_date+" "+pre_intime);
		actualtime = new Date();
		if(filluptime.getTime() > actualtime.getTime())
		{
			alert("Clock-In should not exceed current time. Please enter valid time.");

			setTimeout(function() {
				document.getElementById("pre_intime_"+rowid).focus();
				//$("#pre_intime_"+rowid).focus();
			}, 0);

			return false;
		} */
		if (!pre_inflag) {

			alert("Invalid Clock-In. Please enter valid time.");

			setTimeout(function() {
				document.getElementById("pre_intime_"+rowid).focus();
				//$("#pre_intime_"+rowid).focus();
			}, 0);

			return false;
		}
	}
	else if (pre_intime == "") 
	{

		alert("Invalid Clock-In. Please enter valid time.");

		setTimeout(function() {
			document.getElementById("pre_intime_"+rowid).focus();
			//$("#pre_intime_"+rowid).focus();
		}, 0);

		return false;
	}

	if (pre_outtime != "") {

		pre_outflag	= validateTime(pre_outtime);
		/* filluptime = new Date(end_date+" "+pre_outtime);
		actualtime = new Date();
		if(filluptime.getTime() > actualtime.getTime())
		{
			alert("Clock-Out should not exceed current time. Please enter valid time.");

			setTimeout(function() {
				document.getElementById("pre_outtime_"+rowid).focus();
				//$("#pre_intime_"+rowid).focus();
			}, 0);

			return false;
		} */
		if (!pre_outflag) {
			alert("Invalid Clock-Out. Please enter valid time.");

			setTimeout(function() {
				document.getElementById("pre_outtime_"+rowid).focus();
				//$("#pre_outtime_"+rowid).focus();
			}, 0);

			return false;
		}
	}
	else if (pre_outtime == "") 
	{

		//alert("Invalid Clock-Out. Please enter valid time.");

		setTimeout(function() {
			document.getElementById("pre_outtime_"+rowid).focus();
			//$("#pre_outtime_"+rowid).focus();
		}, 0);

		return false;
	}

	//End- Validations for time in and time out fields

	if (pre_inflag && pre_outflag) {

		if (start_date == end_date && pre_intime.substring(6).toUpperCase() == "PM" && pre_outtime.substring(6).toUpperCase() == "AM") {

			alert("Please enter Clock-In & Clock-Out for the same day");		

			setTimeout(function() {
				document.getElementById("pre_outtime_"+rowid).focus();
				//$("#pre_outtime_"+rowid).focus();
			}, 0);

			return false;
		}

		var pre_in_str	= start_date + " " + pre_intime;
		var pre_out_str	= end_date + " " + pre_outtime;

		var pre_start_time	= Date.parse(pre_in_str);
		var pre_end_time	= Date.parse(pre_out_str);

		if(start_date == end_date)
		{
			var pre_start24HrsMilisec = convertTimeFrom12To24IntoMilisec(pre_intime);
			var pre_end24HrsMilisec = convertTimeFrom12To24IntoMilisec(pre_outtime);

			if (pre_end24HrsMilisec <= pre_start24HrsMilisec) {

				alert("Clock-Out Time should be greater than Clock-In Time");

				setTimeout(function() {
					document.getElementById("pre_outtime_"+rowid).focus();
					//$("#pre_outtime_"+rowid).focus();
				}, 0);

				return false;
			}
		}
		
	}

	//
	if (pre_intime != "" && pre_outtime != "") {
		// working
		// validate IN and OUT		
		tot_pre_hours	= calculateTimeInTimeOutDifference(start_date,end_date,pre_intime,pre_outtime);
		//total_hours	= parseFloat(tot_pre_hours);
		var total_sec = $("#total_hours_sec_"+rowid).val();
		total_hours	= tot_pre_hours+':'+total_sec;

		$("#total_hours_"+rowid).val(total_hours);
	}

	// Check Full
	var prev_out = '';
	var next_in = '';
	var rowday = '';
	$(".tr_clone").each(function(index) {

    	var tr_id = this.id;
    	var tr_rowid	= tr_id.split("_").pop(-1);
    	
    	var current_rowid_in = $("#pre_intime_" + tr_rowid).val();
    	var current_rowid_out = $("#pre_outtime_" + tr_rowid).val();
    	var start_date		= $("#csdate_" + tr_rowid).val();
			var end_date			= $("#cedate_" + tr_rowid).val();
    	var pre_start24HrsMilisec = convertTimeFrom12To24IntoMilisec(current_rowid_in);
			var pre_end24HrsMilisec = convertTimeFrom12To24IntoMilisec(current_rowid_out);
			
			if (prev_out !="" && rowday!="") {
				var next_in = convertTimeFrom12To24IntoMilisec(current_rowid_in);
				
				if (prev_out >= next_in && rowday == start_date) {
					alert("Clock-In hours are overlapped. Please re-enter the Clock-In hours.");
					setTimeout(function() {
						document.getElementById("pre_intime_"+rowid).focus();
						//$("#pre_intime_" + tr_rowid).focus();
					}, 0);
					return false;
				}
			}
			
		prev_out = pre_end24HrsMilisec;
		if(pre_end24HrsMilisec == 0)
		{
				prev_out = 1;
		}
		rowday = end_date;
		

	});
	var grandTotal_val='0';
	$(".tr_clone").each(function() {
    	var tr_id = this.id;
    	var tr_rowid = tr_id.split("_").pop(-1);
    	var tr_total = $("#total_hours_" + tr_rowid).val();
    	//if (grandTotal_val !='0') {
    		// Calculate Grand Total
			grandTotal_val = addTimes(grandTotal_val, tr_total);
    	//}else{
    		//grandTotal_val = tr_total;
    	//}

    });
    grandTotal_hours = parseInt(grandTotal_val)/3600;
	//grandTotal_hours	= grandTotal_val.slice(0,-3);
	//var grdval = grandTotal_hours.split(":");
	//grandTotal_hours = parseFloat(grdval[0]+'.'+grdval[1]);
	grandTotal_hours = parseFloat(parseFloat(grandTotal_hours).toFixed(2));
	$("#final_total_hours").html(grandTotal_hours);
	$("#grand_total_hours").val(grandTotal_hours);


	applyDayRulesToDay();

	return;	
}

// calculate Grand Total
function addTimes (totalHours, addHours) {

	var tot_time=0; 

	var myHours = addHours.split(':');

	hour = myHours[0]*3600;
	min = myHours[1]*60;
	sec = myHours[2];

	tot_time = parseInt(hour) + parseInt(min) + parseInt(sec);
	totalHours = parseInt(totalHours) + parseInt(tot_time);

	/*var a = (startTime || '').split(':')
	var b = (endTime || '').split(':')

	// normalize time values
	for (var i = 0; i < max; i++) {
	a[i] = isNaN(parseInt(a[i])) ? 0 : parseInt(a[i])
	b[i] = isNaN(parseInt(b[i])) ? 0 : parseInt(b[i])
	}

	// store time values
	for (var i = 0; i < max; i++) {
	times[i] = a[i] + b[i]
	}

	var hours = times[0]
	var minutes = times[1]
	var seconds = times[2]

	if (seconds >= 60) {
	var m = (seconds / 60) << 0
	minutes += m
	seconds -= 60 * m
	}

	if (minutes >= 60) {
	var h = (minutes / 60) << 0
	hours += h
	minutes -= 60 * h
	}*/

	//return ('0' + hours).slice(-2) + ':' + ('0' + minutes).slice(-2) + ':' + ('0' + seconds).slice(-2)

	return totalHours;
}


// parse a date in mm/dd/yyyy format
function parseDate(input) {	
  var parts = input.split('/');  
  return new Date(parts[2], parts[0]-1, parts[1]); // Note: months are 0-based
}

function chainNavigation(rowid) {

    if (rowid === undefined) {
		$('.rowIntime').inputmask({ alias: "datetime", placeholder: "HH:MM AM", inputFormat: "hh:MM TT", insertMode: false, showMaskOnHover: true, hourFormat: 12 }, {"oncomplete": function(){ calculateTime(this.id); }});
		$('.rowOuttime').inputmask({ alias: "datetime", placeholder: "HH:MM AM", inputFormat: "hh:MM TT", insertMode: false, showMaskOnHover: true, hourFormat: 12 }, {"oncomplete": function(){ calculateTime(this.id); }});
	}else
	{
		$("#row_"+rowid+" .rowIntime").inputmask({ alias: "datetime", placeholder: "HH:MM AM", inputFormat: "hh:MM TT", insertMode: false, showMaskOnHover: true, hourFormat: 12 }, {"oncomplete": function(){ calculateTime(this.id); }});
		$("#row_"+rowid+" .rowOuttime").inputmask({ alias: "datetime", placeholder: "HH:MM AM", inputFormat: "hh:MM TT", insertMode: false, showMaskOnHover: true, hourFormat: 12 }, {"oncomplete": function(){ calculateTime(this.id); }});

	}
	tabindexcount	= $("#tabindexcount").val();

	$("#issues").attr("tabindex", (parseInt(tabindexcount)+1));
	$("#timefile").attr("tabindex", (parseInt(tabindexcount)+2));
	$("#tabindexcount").val(parseInt(tabindexcount));



	var selects	= document.getElementsByTagName("select");

	for (var i = 0; i < selects.length; i++) {

		var sl	= selects[i];
		while (sl = sl.parentNode) {
			if(sl.nodeName.toLowerCase() === 'tr') {
				selects[i].onfocus = function() {
					this.parentRow.style.backgroundColor = '#3fb8f1';
				};
				selects[i].onblur = function() {
					this.parentRow.style.backgroundColor = '';
				};
				selects[i].parentRow = sl;
				break;
			}
		}
	}
}

function closeWindow() {

	window.close();
}

function formatNumber(val) {

	var i		= parseFloat(val);
	var minus	= "";

	if (isNaN(i)) {

		i	= 0.00;
	}

	if (i < 0) {

		minus = "-";
	}

	i	= Math.abs(i);
	i	= parseInt((i + .005) * 100);
	i	= i / 100;

	var s	= new String(i);

	if (s.indexOf(".") < 0) {

		s += ".00";
	}

	if (s.indexOf(".") == (s.length - 2)) {

		s += "0";
	}

	s	= minus + s;

	return s;
}

function calculateTimeDifference(start_time, end_time) {

	var t_hours	= 0.00;
	var diff	= (end_time - start_time)/1000/60;
	var hours	= formatNumber(String(100 + Math.floor(diff / 60)).substr(1));
	var mins	= String(100 + diff % 60).substr(1);
	var tdate	= new Date(end_time);

	mins	= formatNumber(mins/60); // Required as converting from hours:mins to decimal [1min = 1/60 or 30mins = 0.5]
	
	// Checking entered time is 11:59 PM, then adding one min to consider as 12:00 AM.
	if(tdate.getHours() == "23" && tdate.getMinutes() == "59")
	{
		mins	= parseFloat(mins) + 0.02;
	}

	if (hours != "aN" && mins != "aN") {

		t_hours	= parseFloat(hours) + parseFloat(mins);
		t_hours	= formatNumber(t_hours);
	}

	return t_hours;
}



function getUnixTimeStamp(sel_date) {

	return Date.parse(sel_date);
}

function checkForSpecialChars(field) {

	var str	= field.value;

	for (var i = 0; i < str.length; i++) {

		var ch	= str.substring(i, i + 1);

		if ((ch=="^" || ch=="|" )) {

			return true;
		}
	}

	return false;
}

function validateHours(field, name) {

	var str	= field.value;

	if (isNaN(str) || str.substring(0,1)=="-" || str.substring(0,1)=="+") {

		alert(name + " field accepts numbers and decimals only. Enter a valid time value.");

		field.focus();

		return false;
	}

	return true;
}


function convertTimeFrom12To24_1(timeStr) {
  var colon = timeStr.indexOf(':');
  var hours = timeStr.substr(0, colon),
      minutes = timeStr.substr(colon+1, 2),
      meridian = timeStr.substr(colon+4, 2).toUpperCase(); 
  
  var hoursInt = parseInt(hours, 10),
      offset = meridian == 'PM' ? 12 : 0;
  
  if (hoursInt === 12) {
    hoursInt = offset;
  } else {
    hoursInt += offset;
  }
  return hoursInt + ":" + minutes;
}

function convertTimeFrom12To24(timeVal) {
	var time = timeVal;
	var hours = Number(time.match(/^(\d+)/)[1]);
	var minutes = Number(time.match(/:(\d+)/)[1]);
	var AMPM = time.match(/\s(.*)$/)[1];
	if(AMPM == "PM" && hours<12) hours = hours+12;
	if(AMPM == "AM" && hours==12) hours = hours-12;
	var sHours = hours.toString();
	var sMinutes = minutes.toString();
	if(hours<10) sHours = "0" + sHours;
	if(minutes<10) sMinutes = "0" + sMinutes;

	return (sHours + ":" + sMinutes);
}

function convertTimeFrom12To24IntoMilisec(timeStr) {
	var minutes=0;
  var colon = timeStr.indexOf(':');
  var hours = timeStr.substr(0, colon),
      minutes = timeStr.substr(colon+1, 2),
      meridian = timeStr.substr(colon+4, 2).toUpperCase();
 
  
  var hoursInt = parseInt(hours, 10),
      offset = meridian == 'PM' ? 12 : 0;
  
  if (hoursInt === 12) {
    hoursInt = offset;
  } else {
    hoursInt += offset;
  }
  return ((hoursInt*60*60+minutes*60+0)*1000);
  //return hoursInt + ":" + minutes;
}

function calculateTimeInTimeOutDifference(StartDate,EndDate,timeIn,timeOut) { 
	
	/* Start  */
	var time = timeIn;
	var hrs = Number(time.match(/^(\d+)/)[1]);
	var mnts = Number(time.match(/:(\d+)/)[1]);

	var format = time.match(/\s(.*)$/)[1];

	if (format == "PM" && hrs < 12) hrs = hrs + 12;
	if (format == "AM" && hrs == 12) hrs = hrs - 12;

	var hours =hrs.toString();
	var minutes = mnts.toString();

	if (hrs < 10) hours = "0" + hours;
	if (mnts < 10) minutes = "0" + minutes;

	var date1 = new Date(StartDate);
	date1.setHours(hours);
	date1.setMinutes(minutes);

	var time = timeOut;
	var hrs = Number(time.match(/^(\d+)/)[1]);
	var mnts = Number(time.match(/:(\d+)/)[1]);
	var format = time.match(/\s(.*)$/)[1];
	if (format == "PM" && hrs < 12) hrs = hrs + 12;
	if (format == "AM" && hrs == 12) hrs = hrs - 12;
	var hours = hrs.toString();
	var minutes = mnts.toString();
	if (hrs < 10) hours = "0" + hours;
	if (mnts < 10) minutes = "0" + minutes;

	var date2 = new Date(EndDate);
	date2.setHours(hours);
	date2.setMinutes(minutes);

	var diff = date2.getTime() - date1.getTime();
	
	var hours = Math.floor(diff / (1000 * 60 * 60));
	diff -= hours * (1000 * 60 * 60);

	var mins = Math.floor(diff / (1000 * 60));
	diff -= mins * (1000 * 60);

	if (hours < 10) hours = "0" + hours;
	if (mins < 10) mins = "0" + mins;

	return (hours + ":" + mins);
}
var doSaveCiCoTimeCount = 0;
function doSaveCiCoTime(parid,thsno) {

	if(doSaveCiCoTimeCount == 0)
	{
		var validation = validateClockInClockOut();
		reCalculateCICOHrs();
		if (validation) {
					
			var form	= document.sheet;
			$(form).append('<input type="hidden" id="mode" name="mode" value="Update"/>');
			endshiftCheck = checkEndShiftTimesheet(form).split("|");
			if(endshiftCheck[0] == "false")
			{
				alert(endshiftCheck[1]);
				return false;
			}
			doSaveCiCoTimeCount++;
			form.action	= "/BSOS/Accounting/Time_Mngmt/savecicotime.php?timesheet=edit&parid="+ parid+"&tssno="+thsno;
			form.submit();
		}
	}
	
}

function Converttimeformat(timeIn,timeOut) {


	var time = timeIn;
	var hrs = Number(time.match(/^(\d+)/)[1]);
	var mnts = Number(time.match(/:(\d+)/)[1]);
	var format = time.match(/\s(.*)$/)[1];
	if (format == "PM" && hrs < 12) hrs = hrs + 12;
	if (format == "AM" && hrs == 12) hrs = hrs - 12;
	var hours =hrs.toString();
	var minutes = mnts.toString();
	if (hrs < 10) hours = "0" + hours;
	if (mnts < 10) minutes = "0" + minutes;

	var date1 = new Date();
	date1.setHours(hours );
	date1.setMinutes(minutes);

	var time = timeOut;
	var hrs = Number(time.match(/^(\d+)/)[1]);
	var mnts = Number(time.match(/:(\d+)/)[1]);
	var format = time.match(/\s(.*)$/)[1];
	if (format == "PM" && hrs < 12) hrs = hrs + 12;
	if (format == "AM" && hrs == 12) hrs = hrs - 12;
	var hours = hrs.toString();
	var minutes = mnts.toString();
	if (hrs < 10) hours = "0" + hours;
	if (mnts < 10) minutes = "0" + minutes;

	var date2 = new Date();
	date2.setHours(hours );
	date2.setMinutes(minutes);

	var diff = date2.getTime() - date1.getTime();

	var hours = Math.floor(diff / (1000 * 60 * 60));
	diff -= hours * (1000 * 60 * 60);

	var mins = Math.floor(diff / (1000 * 60));
	diff -= mins * (1000 * 60);

	if (hours < 10) hours = "0" + hours;
	if (mins < 10) mins = "0" + mins;

	return (hours + ":" + mins);

}

function validateClockInClockOut() {
	
	// Check Full
	var pre_inflag	= false;
	var pre_outflag	= false;
	var prev_out = '';
	var next_in = '';
	var result = true;
	var rowday = '';
	$(".tr_clone").each(function(index,e) {
    	var tr_id = this.id;
    	var tr_rowid	= tr_id.split("_").pop(-1);
    	var current_rowid_in = $("#pre_intime_" + tr_rowid).val();
    	var current_rowid_out = $("#pre_outtime_" + tr_rowid).val();
    	
    var rowid			= tr_rowid;
		//var sel_date		= $("#daily_dates_" + rowid).val();
		var start_date		= $("#csdate_" + rowid).val();
		var end_date			= $("#cedate_" + rowid).val();
		var pre_intime		= current_rowid_in;
		var pre_outtime		= current_rowid_out;
		
		//validating the each time in and time out fields
		if (pre_intime != "") {

			pre_inflag	= validateTime(pre_intime);
			if (!pre_inflag) {
				document.getElementById("pre_intime_"+rowid).focus();
				alert("Invalid Clock-In. Please enter valid time.");
				result = false;
				return false;
			}
		}
		else if (pre_intime == "")
		{
			document.getElementById("pre_intime_"+rowid).focus();
			alert("Invalid Clock-In. Please enter valid time.");
			result = false;
			return false;
		}

		if (pre_outtime != "") {

			pre_outflag	= validateTime(pre_outtime);
			if (!pre_outflag) {
				document.getElementById("pre_outtime_"+rowid).focus();
				alert("Invalid Clock-Out. Please enter valid time.");
				result = false;
				return false;
			}
		}
		else if (pre_outtime == "")
		{	
			document.getElementById("pre_outtime_"+rowid).focus();
			alert("Invalid Clock-Out. Please enter valid time.");
			result = false;
			return false;
		}
		//End- Validations for time in and time out fields

		if (pre_inflag && pre_outflag) 
		{
			if (start_date == end_date && pre_intime.substring(6).toUpperCase() == "PM" && pre_outtime.substring(6).toUpperCase() == "AM") 
			{
				document.getElementById("pre_outtime_"+rowid).focus();
				alert("Please enter Clock-In & Clock-Out for the same day");
				result = false;
				return false;
			}

			var pre_in_str	= start_date + " " + pre_intime;
			var pre_out_str	= end_date + " " + pre_outtime;

			var pre_start_time	= Date.parse(pre_in_str);
			var pre_end_time	= Date.parse(pre_out_str);

			if(start_date == end_date)
			{
					var pre_start24HrsMilisec = convertTimeFrom12To24IntoMilisec(pre_intime);
					var pre_end24HrsMilisec = convertTimeFrom12To24IntoMilisec(pre_outtime);

					if (pre_end24HrsMilisec <= pre_start24HrsMilisec) {
						document.getElementById("pre_outtime_"+rowid).focus();
						alert("Clock-Out Time should be greater than Clock-In Time");
						result = false;
						return false;
					}
			}
			
		}

		var pre_start24HrsMilisec = convertTimeFrom12To24IntoMilisec(current_rowid_in);
		var pre_end24HrsMilisec = convertTimeFrom12To24IntoMilisec(current_rowid_out);
		
		if (prev_out !="" && rowday!="") {
			var next_in = convertTimeFrom12To24IntoMilisec(current_rowid_in);
			
			if (prev_out >= next_in && rowday == start_date) {
				document.getElementById("pre_intime_"+rowid).focus();
				alert("Clock-In hours are overlapped. Please re-enter the Clock-In hours.");
				result = false;
				return false;
			}
		}
		
		prev_out = pre_end24HrsMilisec;
		if(pre_end24HrsMilisec == 0)
		{
				prev_out = 1;
		}
		rowday = end_date;
	});
	if (result === false) {
        return false;
    }else{
    	return true;
    }
}

function reCalculateCICOHrs() {

	var grandTotal_val ='0';
	$(".tr_clone").each(function() {
    	var tr_id 		= this.id;
		var rowid		= tr_id.split("_").pop(-1);
		//var sel_date	= $("#daily_dates_" + rowid).val();
		var start_date		= $("#csdate_" + rowid).val();
		var end_date			= $("#cedate_" + rowid).val();
		var pre_intime	= $("#pre_intime_"+rowid).val();
		var pre_outtime	= $("#pre_outtime_"+rowid).val();

		if (pre_intime != "" && pre_outtime != "") {
			// working
			// validate IN and OUT		
			tot_pre_hours	= calculateTimeInTimeOutDifference(start_date,end_date,pre_intime,pre_outtime);
			//total_hours	= parseFloat(tot_pre_hours);
			var total_sec = $("#total_hours_sec_"+rowid).val();
			total_hours	= tot_pre_hours+':'+total_sec;
			$("#total_hours_"+rowid).val(total_hours);

			var tr_total = total_hours;
	    	//if (grandTotal_val !='0:00') {
	    		// Calculate Grand Total
				grandTotal_val = addTimes(grandTotal_val, tr_total);
	    	//}else{
	    		//grandTotal_val = tr_total;
	    	//}
		}

	});
	//if (grandTotal_val !='0') {

		/*grandTotal_hours	= grandTotal_val.slice(0,-3);
		var grdval = grandTotal_hours.split(":");
		grandTotal_hours = parseFloat(grdval[0]+'.'+grdval[1]);
		$("#final_total_hours").html(grandTotal_hours);
		$("#grand_total_hours").val(grandTotal_hours);*/

		grandTotal_hours = parseInt(grandTotal_val)/3600;
		//grandTotal_hours	= grandTotal_val.slice(0,-3);
		//var grdval = grandTotal_hours.split(":");
		//grandTotal_hours = parseFloat(grdval[0]+'.'+grdval[1]);
		grandTotal_hours = parseFloat(parseFloat(grandTotal_hours).toFixed(2));

		if(!isNaN(grandTotal_hours))
		{
			$("#final_total_hours").html(grandTotal_hours);
			$("#grand_total_hours").val(grandTotal_hours);
		}else{
			grandTotal_hours = "0.00";
			$("#final_total_hours").html(grandTotal_hours);
			$("#grand_total_hours").val(grandTotal_hours); 
		}
		
	rule_type = $("#rule_type").val();

	if(window.location.href.indexOf('cico_edit') > -1 || window.location.href.indexOf('newcico') > -1)
	{
		
		applyDayRulesToDay();
		
	}

	if(window.location.href.indexOf('cico_timesheet_edit') > -1)
	{	
			
			if(rule_type == "weekrule")
			{
					applyWeekRulesToWeekly();
			}
			else
			{
					applyDayRulesToWeekly();
			}
	}	
}

//Function used to calculate the hours when entering the times in onchange event
function calculateEditTime(inout_rowid,thsno,frm='') { 

	var pre_inflag	= false;
	var pre_outflag	= false;
	var tot_pre_hours	= 0.00;
	var rowid	= inout_rowid.split("_").pop(-1);
	//var sel_date		= $("#daily_dates_" + rowid).val();
	var start_date		= $("#csdate_" + rowid).val();
	var end_date		= $("#cedate_" + rowid).val();
	var pre_intime		= $("#pre_intime_"+rowid).val();
	var pre_outtime		= $("#pre_outtime_"+rowid).val();

	//validating the each time in and time out fields
	if (pre_intime != "") {

		pre_inflag	= validateTime(pre_intime);
		if (!pre_inflag) {

			alert("Invalid Clock-In. Please enter valid time.");

			setTimeout(function() {
				document.getElementById("pre_intime_"+rowid).focus();
				//$("#pre_intime_"+rowid).focus();
			}, 0);

			return false;
		}
	}
	else if (pre_intime == "") 
	{

		alert("Invalid Clock-In. Please enter valid time.");

		setTimeout(function() {
			document.getElementById("pre_intime_"+rowid).focus();
			//$("#pre_intime_"+rowid).focus();
		}, 0);

		return false;
	}

	if (pre_outtime != "") {

		pre_outflag	= validateTime(pre_outtime);
		if (!pre_outflag) {
			alert("Invalid Clock-Out. Please enter valid time.");

			setTimeout(function() {
				document.getElementById("pre_outtime_"+rowid).focus();
				//$("#pre_outtime_"+rowid).focus();
			}, 0);

			return false;
		}
		
	}
	else if (pre_outtime == "") 
	{
		//alert("Invalid Clock-Out. Please enter valid time.");

		setTimeout(function() {
			document.getElementById("pre_outtime_"+rowid).focus();
			//$("#pre_outtime_"+rowid).focus();
		}, 0);

		return false;
	}
	//End- Validations for time in and time out fields

	if (pre_inflag && pre_outflag) {
			
		if (start_date == end_date && pre_intime.substring(6).toUpperCase() == "PM" && pre_outtime.substring(6).toUpperCase() == "AM") {

			alert("Please enter Clock-In & Clock-Out for the same day");		

			setTimeout(function() {
				document.getElementById("pre_outtime_"+rowid).focus();
				//$("#pre_outtime_"+rowid).focus();
			}, 0);

			return false;
		}

		var pre_in_str	= start_date + " " + pre_intime;
		var pre_out_str	= end_date + " " + pre_outtime;

		var pre_start_time	= Date.parse(pre_in_str);
		var pre_end_time	= Date.parse(pre_out_str);

		var pre_start24HrsMilisec = convertTimeFrom12To24IntoMilisec(pre_intime);
		var pre_end24HrsMilisec = convertTimeFrom12To24IntoMilisec(pre_outtime);

		if (pre_end24HrsMilisec <= pre_start24HrsMilisec) {
			if(start_date == end_date)
			{
				alert("Clock-Out Time should be greater than Clock-In Time");

				setTimeout(function() {
					document.getElementById("pre_outtime_"+rowid).focus();
					//$("#pre_outtime_"+rowid).focus();
				}, 0);

				return false;
			}
		}
	}

	//
	if (pre_intime != "" && pre_outtime != "") {
		// working
		// validate IN and OUT		
		tot_pre_hours	= calculateTimeInTimeOutDifference(start_date,end_date,pre_intime,pre_outtime);
		//total_hours	= parseFloat(tot_pre_hours);
		var total_sec = $("#total_hours_sec_"+rowid).val();
		total_hours	= tot_pre_hours+':'+total_sec;

		$("#total_hours_"+rowid).val(total_hours);
	}

	// Check Full
	var prev_out = '';
	var next_in = '';
	var prev_child_out = '';
	var next_child_in = '';
	var processCompletedThsno = [];
	var result = true;
	var element = "";
	var weekday = "";
	var rowday = ''
	$(".tr_clone").each(function(index,element) {

		if($(element).data("thsno") == thsno)
		{
			var tr_id = this.id;
			var tr_rowid	= tr_id.split("_").pop(-1);
			var current_rowid_in = $("#pre_intime_" + tr_rowid).val();
			var current_rowid_out = $("#pre_outtime_" + tr_rowid).val();
			var start_date		= $("#csdate_" + tr_rowid).val();
			var end_date		= $("#cedate_" + tr_rowid).val();
			var pre_start24HrsMilisec = convertTimeFrom12To24IntoMilisec(current_rowid_in);
			var pre_end24HrsMilisec = convertTimeFrom12To24IntoMilisec(current_rowid_out);
				
			if (prev_out !="" && rowday!="") {
				var next_in = convertTimeFrom12To24IntoMilisec(current_rowid_in);
				
				if (prev_out >= next_in &&  rowday == start_date) {
					alert("Clock-In hours are overlapped. Please re-enter the Clock-In hours.");
					setTimeout(function() {
						document.getElementById("pre_intime_"+tr_rowid).focus();
					}, 0);
					return false;
				}
			}
			
			prev_out = pre_end24HrsMilisec;
			if(pre_end24HrsMilisec == 0)
			{
					prev_out = 1;
			}
			rowday = end_date;
		}
    	
	});

	var grandTotal_val='0';
	$(".tr_clone").each(function() {
    	var tr_id = this.id;
    	var tr_rowid = tr_id.split("_").pop(-1);
    	var tr_total = $("#total_hours_" + tr_rowid).val();
    	//if (grandTotal_val !='0') {
    		// Calculate Grand Total
			grandTotal_val = addTimes(grandTotal_val, tr_total);
    	//}else{
    		//grandTotal_val = tr_total;
    	//}

    });
    grandTotal_hours = parseInt(grandTotal_val)/3600;
	//grandTotal_hours	= grandTotal_val.slice(0,-3);
	//var grdval = grandTotal_hours.split(":");
	//grandTotal_hours = parseFloat(grdval[0]+'.'+grdval[1]);
	grandTotal_hours = parseFloat(parseFloat(grandTotal_hours).toFixed(2));
	$("#final_total_hours").html(grandTotal_hours);
	$("#grand_total_hours").val(grandTotal_hours);

	rule_type = $("#rule_type").val();

	if(rule_type == "weekrule")
	{
		applyWeekRulesToWeekly();
	}
	else
	{
		applyDayRulesToWeekly();
	}
	
	return;	
}

var doUpdateCiCoTimeCount = 0;
function doUpdateCiCoTime(parid) {

	if(doUpdateCiCoTimeCount == 0)
	{		
			var validation = validateEditClockInClockOut();
			reCalculateCICOHrs();
			if (validation) {
				doUpdateCiCoTimeCount++;		
				var form	= document.sheet;
				form.action	= "/BSOS/Accounting/Time_Mngmt/updatecicotime.php?parid="+parid;
				form.submit();
			}
	}
	
}

function validateEditClockInClockOut() {
	
	// Check Full
	var pre_inflag	= false;
	var pre_outflag	= false;
	var prev_out = '';
	var next_in = '';
	var result = true;
	var rowday = '';

 	$(".tr_clone").each(function(index,e) {
 			var tr_id = this.id;
    	var tr_rowid	= tr_id.split("_").pop(-1);
    	var current_rowid_in = $("#pre_intime_" + tr_rowid).val();
    	var current_rowid_out = $("#pre_outtime_" + tr_rowid).val();
    	
	    var rowid			= tr_rowid;
			//var sel_date		= $("#daily_dates_" + rowid).val();
			var start_date		= $("#csdate_" + rowid).val();
			var end_date			= $("#cedate_" + rowid).val();
			var pre_intime		= current_rowid_in;
			var pre_outtime		= current_rowid_out;
		
			//validating the each time in and time out fields
			if (pre_intime != "") {

				pre_inflag	= validateTime(pre_intime);
				if (!pre_inflag) {
					document.getElementById("pre_intime_"+rowid).focus();
					alert("Invalid Clock-In. Please enter valid time.");
					result = false;
					return false;
				}
			}
			else if (pre_intime == "")
			{
				document.getElementById("pre_intime_"+rowid).focus();
				alert("Invalid Clock-In. Please enter valid time.");
				result = false;
				return false;
				
			}

			if (pre_outtime != "") {

				pre_outflag	= validateTime(pre_outtime);
				if (!pre_outflag) {
					document.getElementById("pre_outtime_"+rowid).focus();
					alert("Invalid Clock-Out. Please enter valid time.");
					result = false;
					return false;
					
				}
			}
			else if (pre_outtime == "")
			{	
				document.getElementById("pre_outtime_"+rowid).focus();
				alert("Invalid Clock-Out. Please enter valid time.");
				result = false;
				return false;
				
			}
			//End- Validations for time in and time out fields

			if (pre_inflag && pre_outflag) 
			{
				if (start_date == end_date && pre_intime.substring(6).toUpperCase() == "PM" && pre_outtime.substring(6).toUpperCase() == "AM") 
				{
					document.getElementById("pre_outtime_"+rowid).focus();
					alert("Please enter Clock-In & Clock-Out for the same day");
					result = false;
					return false;
					
				}

				var pre_in_str	= start_date + " " + pre_intime;
				var pre_out_str	= end_date + " " + pre_outtime;

				var pre_start_time	= Date.parse(pre_in_str);
				var pre_end_time	= Date.parse(pre_out_str);

				if(start_date == end_date)
				{
						var pre_start24HrsMilisec = convertTimeFrom12To24IntoMilisec(pre_intime);
						var pre_end24HrsMilisec = convertTimeFrom12To24IntoMilisec(pre_outtime);

						if (pre_end24HrsMilisec <= pre_start24HrsMilisec) {
							document.getElementById("pre_outtime_"+rowid).focus();
							alert("Clock-Out Time should be greater than Clock-In Time");
							result = false;
							return false;
							
						}
				}
				
			}

			var pre_start24HrsMilisec = convertTimeFrom12To24IntoMilisec(current_rowid_in);
			var pre_end24HrsMilisec = convertTimeFrom12To24IntoMilisec(current_rowid_out);
			
			if (prev_out !="" && rowday!="") {
				var next_in = convertTimeFrom12To24IntoMilisec(current_rowid_in);
				
				if (prev_out >= next_in && rowday == start_date) {
					
					document.getElementById("pre_intime_"+rowid).focus();
					alert("Clock-In hours are overlapped. Please re-enter the Clock-In hours.");
					result = false;
					return false;
					
				}
			}
			prev_out = pre_end24HrsMilisec;
			if(pre_end24HrsMilisec == 0)
			{
					prev_out = 1;
			}
			rowday = end_date;
	});

	if (result === false) 
	{
    return false;
 	}else{
 		return true;
 	}
}


function validateEditSaveClockInClockOut() {
	
	// Check Full
	var pre_inflag	= false;
	var pre_outflag	= false;
	var prev_out = '';
	var result = true;
	var rowday = '';

 	$(".tr_clone").each(function(index,e) {
	  var tr_id = this.id;
  	var tr_rowid	= tr_id.split("_").pop(-1);
  	var current_rowid_in = $("#pre_intime_" + tr_rowid).val();
  	var current_rowid_out = $("#pre_outtime_" + tr_rowid).val();
    	
	  var rowid			= tr_rowid;
		var start_date		= $("#csdate_" + rowid).val();
		var end_date		= $("#cedate_" + rowid).val();
		var pre_intime		= current_rowid_in;
		var pre_outtime		= current_rowid_out;
		
		//validating the each time in and time out fields
		if (pre_intime != "") {

			filluptime = new Date(start_date+" "+pre_intime);
			actualtime = new Date();
			if(filluptime.getTime() > actualtime.getTime())
			{
				alert("Clock-In should not exceed current time. Please enter valid time.");

				setTimeout(function() {
					document.getElementById("pre_intime_"+rowid).focus();
					//$("#pre_intime_"+rowid).focus();
				}, 0);
				result = false;
				return false;
			}
			pre_inflag	= validateTime(pre_intime);
			if (!pre_inflag) {
				document.getElementById("pre_intime_"+rowid).focus();
				alert("Invalid Clock-In. Please enter valid time.");
				result = false;
				return false;
			}
		}
		else if (pre_intime == "")
		{
			document.getElementById("pre_intime_"+rowid).focus();
			alert("Invalid Clock-In. Please enter valid time.");
			result = false;
			return false;
			
		}

		if (pre_outtime != "") {

			filluptime = new Date(end_date+" "+pre_outtime);
			actualtime = new Date();
			console.log(filluptime.getTime(),actualtime.getTime());
			if(filluptime.getTime() > actualtime.getTime())
			{
				alert("Clock-Out should not exceed current time. Please enter valid time.");

				setTimeout(function() {
					document.getElementById("pre_outtime_"+rowid).focus();
					//$("#pre_intime_"+rowid).focus();
				}, 0);
				result = false;
				return false;
			}
			
			pre_outflag	= validateTime(pre_outtime);
			if (!pre_outflag) {
				document.getElementById("pre_outtime_"+rowid).focus();
				alert("Invalid Clock-Out. Please enter valid time.");
				result = false;
				return false;
				
			}
		}else if (pre_outtime == "")
		{
			document.getElementById("pre_outtime_"+rowid).value="";
			
		}
		
		//End- Validations for time in and time out fields

		if (pre_inflag && pre_outflag) 
		{
			if (start_date == end_date && pre_intime.substring(6).toUpperCase() == "PM" && pre_outtime.substring(6).toUpperCase() == "AM") 
			{
				document.getElementById("pre_outtime_"+rowid).focus();
				alert("Please enter Clock-In & Clock-Out for the same day");
				result = false;
				return false;
				
			}

			var pre_in_str	= start_date + " " + pre_intime;
			var pre_out_str	= end_date + " " + pre_outtime;

			var pre_start_time	= Date.parse(pre_in_str);
			var pre_end_time	= Date.parse(pre_out_str);

			if(start_date == end_date)
			{
				var pre_start24HrsMilisec = convertTimeFrom12To24IntoMilisec(pre_intime);
				var pre_end24HrsMilisec = convertTimeFrom12To24IntoMilisec(pre_outtime);

				if (pre_end24HrsMilisec <= pre_start24HrsMilisec) {
					document.getElementById("pre_outtime_"+rowid).focus();
					alert("Clock-Out Time should be greater than Clock-In Time");
					result = false;
					return false;
					
				}
			}
			
		}

		var pre_start24HrsMilisec = convertTimeFrom12To24IntoMilisec(current_rowid_in);
		var pre_end24HrsMilisec = convertTimeFrom12To24IntoMilisec(current_rowid_out);
		
		if (prev_out !="" && rowday!="") {
			var next_in = convertTimeFrom12To24IntoMilisec(current_rowid_in);
			
			if (prev_out >= next_in && rowday == start_date) {
				
				document.getElementById("pre_intime_"+rowid).focus();
				alert("Clock-In hours are overlapped. Please re-enter the Clock-In hours.");
				result = false;
				return false;
				
			}
		}
		prev_out = pre_end24HrsMilisec;
		if(pre_end24HrsMilisec == 0)
		{
				prev_out = 1;
		}
		rowday = end_date;
	});

	if (result === false) 
	{
    	return false;
 	}else{
 		return true;
 	}
}

function addNewRow(){
	if(validateClockInClockOut())
	{	

			var element = $("#MainTable > tbody > tr:nth-last-child(3)");
	
			var elementId = $(element).attr("id").split("_").pop(-1);	

			var timesheetDate = $(".timesheet_date").html();

			var timesheetAssignment = $(".timesheet_details").html();

			var timesheetEDate = $("#timesheet_etdate_"+(elementId)).html();

			var hiddenDate = $("#cicodate_"+(elementId)).val();

			var hiddenAssignment = $("#cicoassid_"+(elementId)).val();

			var hiddenUsername = $("#cicousername_"+(elementId)).val();

			var hiddenEdate = $("#cedate_"+(elementId)).val();

			var shift_startdate = $("#shift_startdate_"+(elementId)).val();

			var shift_enddate = $("#shift_enddate_"+(elementId)).val();

			var elementCount = parseInt(elementId)+1;

			var hiddenClientId = "";
			if($("#client_"+elementId).length)
			{
				hiddenClientId = $("#client_"+(elementId)).val();
			}
			

			var clientId = "";

			if($(".client-id").length > 0)
			{
				clientId = $(".client-id").val();
			}

			var html = `<tr id="row_`+elementCount+`" class="tr_clone"> 
							<td class="DeletePad" width="2%" valign="top">
								<input type="hidden" id="cicosno_`+elementCount+`" name="cicosno[`+elementCount+`]" value="">						
								<input type="hidden" id="cicodate_`+elementCount+`" name="cicodate[`+elementCount+`]" value="`+hiddenDate+`">
								<input type="hidden" id="cicousername_`+elementCount+`" name="cicousername[`+elementCount+`]" value="`+hiddenUsername+`">
								<input type="hidden" id="cicostatus_`+elementCount+`" name="cicostatus[`+elementCount+`]" value="1">
								<input type="hidden" id="cicoassid_`+elementCount+`" name="cicoassid[`+elementCount+`]" value="`+hiddenAssignment+`">
								<input type="checkbox" name="daily_check[`+elementCount+`][]" id="check_`+elementCount+`" value="" class="chremove" style="margin-top:0px;display:none;">
								<span name="daily_del[`+elementCount+`][]" id="dailydel_`+elementCount+`" onclick="javascript:delete_row(this.id)"><i class="fa fa-trash fa-lg"></i></span>
								<input type="hidden" id="shift_startdate_`+elementCount+`" name="shift_startdate[`+elementCount+`]" value="`+shift_startdate+`">
								<input type="hidden" id="shift_enddate_`+elementCount+`" name="shift_enddate[`+elementCount+`]" value="`+shift_enddate+`">
								<input type="hidden" id="client_id_`+elementCount+`" name="client_id[`+elementCount+`]" value="`+hiddenClientId+`">
							</td>
							<td width="10%" valign="top" align="left">
								<div class="select2-container daily_dates akkenDateSelectWid" id="s2id_daily_dates_`+elementCount+`"  style="display:none;">
									<a href="#" class="select2-choice">   
										<span class="select2-chosen" id="select2-chosen-1">`+timesheetDate+`</span>
											<abbr class="select2-search-choice-close"></abbr>
										<input type="hidden" id="daily_dates_`+elementCount+`" name="daily_dates_`+elementCount+`" value="`+timesheetDate+`">
									</a>
								</div>
							</td>
							<td class="nowrap" style="word-break:break-all;overflow-wrap: break-word;" width="20%" valign="top" align="left">
								<span id="span_`+elementCount+`"  style="display:none;">
									<div class="select2-container daily_assignemnt akkenAssgnSelect" id="s2id_daily_assignemnt_`+elementCount+`">
										<a href="#" class="select2-choice">   
											<span class="select2-chosen" id="select2-chosen-2">`+timesheetAssignment+`
											</span>
										</a>
									</div>
								</span>
							</td>
							<td class="afontstylee" width="10%" valign="top" align="left">
								<div class="timeflex"><input type="hidden" class="csdate" name="csdate[`+elementCount+`]" id="csdate_`+elementCount+`" value="`+hiddenEdate+`" data-rowId = `+elementCount+`><span id="timesheet_stdate_`+elementCount+`" class="timesheet_stdate">`+timesheetEDate+`</span>
								<input type="text" id="pre_intime_`+elementCount+`" name="pre_intime[`+elementCount+`][0]" value="" size="10" class="rowIntime inouttime" onchange="javascript:calculateTime(this.id);" style="font-family:Arial;font-size:9pt;" tabindex="1" placeholder="HH:MM AM"></div>
							</td>
							<td class="afontstylee" width="10%" valign="top" align="left">
								<div class="timeflex"><input type="hidden" class="cedate" name="cedate[`+elementCount+`]" id="cedate_`+elementCount+`" value="`+hiddenEdate+`" data-rowId = `+elementCount+`><span id="timesheet_etdate_`+elementCount+`" class="timesheet_etdate">`+timesheetEDate+`</span>
								<input type="text" id="pre_outtime_`+elementCount+`" name="pre_outtime[`+elementCount+`][0]" value="" size="10" class="rowOuttime inouttime" onchange="javascript:calculateTime(this.id);" style="font-family:Arial;font-size:9pt;" tabindex="2" placeholder="HH:MM AM"></div>
							</td>
							<td class="afontstylee" width="7%" valign="top" align="left">
								<input type="hidden" id="total_hours_sec_`+elementCount+`" name="total_hours_sec[`+elementCount+`][0]" value="00">
								<input type="text" id="total_hours_`+elementCount+`" name="total_hours[`+elementCount+`][0]" value="" size="7" class="rowBreaktime" style="font-family:Arial;font-size:9pt;background-color:#EDE9E9;" readonly="">
							</td>
						</tr>`;
				$(html).insertAfter($("#MainTable > tbody > tr:nth-last-child(3)"));
				chainNavigation(elementCount);
				if(clientId!="")
				{
					$(".client-id").val(clientId);
				}
				
				if(elementCount > 0)
				{
					$("#csdate_"+elementCount).Zebra_DatePicker({
						format: 'm/d/Y',
						direction:[shift_startdate,shift_enddate],
						show_clear_date:false,
						always_visible:false,
						onSelect:function(){sDateChange(this)},
					});

					$("#cedate_"+elementCount).Zebra_DatePicker({
						format: 'm/d/Y',
						direction:[shift_startdate,shift_enddate],
						show_clear_date:false,
						always_visible:false,
						onSelect:function(){eDateChange(this)},
					});

				}
				else
				{
					loadZebraDatePicker();
				}
				
	}
}

function addWeekRow(element){

	var tr_id = $(element).closest("tr").attr("id");
	var tr_rowid	= tr_id.split("_").pop(-1);
	var timesheet_sno = $("#row_"+tr_rowid).data("thsno");
	var lastRowElement = $("#MainTable > tbody > tr.weekdays_"+timesheet_sno+":last");

	var lastRowId = $(lastRowElement).attr("id").split("_").pop(-1);

	if($("#pre_intime_"+lastRowId).val() != "" && $("#pre_outtime_"+lastRowId).val() != "")
	{
		
		var clockNewInOutRow = $("#MainTable > tbody > tr.weekdays_"+timesheet_sno+":last").clone();
		var elementIds = [];
			var elementValue = 0;
		$.each($("#MainTable > tbody > tr.tr_clone"),function(index,element){
			var elementValue = $(element).attr("id").match(/\d/g);
			elementIds.push(elementValue.join(""));
			row_length = Math.max(...elementIds)+1;
		});

		var emptyId = ["cicosno_","daily_check_","pre_intime_","pre_outtime_","total_hours_","check_"];
		$.each(clockNewInOutRow,function(cloneindex,cloneElement){

			$(cloneElement).find("td:first-child").find("span").attr("id",$(cloneElement).find("td:first-child").find("span").attr("id").replace(/\d+/g,row_length));
			$(cloneElement).find("td:first-child").find("span").attr("name",$(cloneElement).find("td:first-child").find("span").attr("name").replace(/\d+/g,row_length));

			$(cloneElement).find("td:first-child").find("span").show();
			$(cloneElement).find("td:nth-child(2)").html($(cloneElement).find("td:nth-child(2)").find("input"));
			$(cloneElement).find("td:nth-child(3)").children().remove();
			$(cloneElement).find("td:nth-child(4)").find("span.timesheet_stdate").attr("id",$(cloneElement).find("td:nth-child(4)").find("span.timesheet_stdate").attr("id").replace(/\d+/g,row_length));
			$(cloneElement).find("td:nth-child(4)").find("button.Zebra_DatePicker_Icon").remove();
			$(cloneElement).find("td:nth-child(4)").find("span.timesheet_stdate").html($(cloneElement).find("td:nth-child(5)").find("span.timesheet_etdate").html());

			$(cloneElement).find("td:nth-child(4)").find("input.csdate").val($(cloneElement).find("td:nth-child(5)").find("input.cedate").val());
			/*$(cloneElement).find("td:nth-child(4)").find("input[name='csdate[]']").attr("data-rowId",$(cloneElement).find("td:nth-child(4)").find("input[name='csdate[]']").attr("data-rowId").replace(/\d+/g,row_length));*/
			$(cloneElement).find("td:nth-child(5)").find("span.timesheet_etdate").attr("id",$(cloneElement).find("td:nth-child(5)").find("span.timesheet_etdate").attr("id").replace(/\d+/g,row_length));
			$(cloneElement).find("td:nth-child(5)").find("button.Zebra_DatePicker_Icon").remove();
			$(cloneElement).find("td:nth-child(5)").find("span.timesheet_stdate").html($(cloneElement).find("td:nth-child(5)").find("span.timesheet_etdate").html());
			$(cloneElement).find("td:nth-child(5)").find("input.cedate").val($(cloneElement).find("td:nth-child(5)").find("input.cedate").val());
			/*$(cloneElement).find("td:nth-child(5)").find("input[name='cedate[]']").attr("data-rowId",$(cloneElement).find("td:nth-child(5)").find("input[name='cedate[]']").attr("data-rowId").replace(/\d+/g,row_length));*/
			$(cloneElement).find("td:last-child > div.akkencustomicons").children().remove();
			$(cloneElement).attr("id","row_"+row_length);

			$(cloneElement).find("input").each(function(inputindex,hiddenInputs){
					$(hiddenInputs).attr("id",$(hiddenInputs).attr("id").replace(/\d+/g,row_length));
					$(hiddenInputs).attr("name",$(hiddenInputs).attr("name").replace(/\d+/,row_length));
					
					if (typeof $(hiddenInputs).attr("data-rowId") !== 'undefined' && $(hiddenInputs).attr("data-rowId") !== false) {
	 				   $(hiddenInputs).attr("data-rowId",$(hiddenInputs).attr("data-rowId").replace(/\d+/,row_length));
					}
			});

		});

		$(clockNewInOutRow).insertAfter($("#MainTable > tbody > tr.weekdays_"+timesheet_sno+":last"));
		$.each(emptyId,function(idindex,id){
				$("#"+id+row_length).val("");
		});
		chainNavigation(row_length);
	}else{


		if($("#pre_intime_"+lastRowId).val() == "")
		{
			alert("Invalid Clock-In. Please enter valid time.");

			setTimeout(function() {
				document.getElementById("pre_intime_"+lastRowId).focus();
				//$("#pre_intime_"+rowid).focus();
			}, 0);
			return false;
		}

		if($("#pre_outtime_"+lastRowId).val() == "")
		{
			alert("Invalid Clock-Out. Please enter valid time.");

			setTimeout(function() {
				document.getElementById("pre_intime_"+lastRowId).focus();
				//$("#pre_intime_"+rowid).focus();
			}, 0);

			return false;
		}
	}
	//loadZebraDatePicker();
	loadRowZebraDatePicker(row_length,false);
}

function applyDayRulesToDay()
{
	var cico_pipo_rule = $("#cico_pipo_rule").val();
	var timeincrements = $("#timeincrements").val();
	var rule_type = $("#rule_type").val();
	var total_hours = 0;
	var minutes_to_hours = 0;
	var regular_hours = 0.00;
	var over_time_hours = 0.00;
	var double_time_hours = 0.00;
	maxregularhours = parseFloat($("#maxregularhours").val());
	maxovertimehours = parseFloat($("#maxovertimehours").val());
	$('[id^="total_hours_"]').each(function(index,element){
			if($("#total_hours_"+index).length)
			{
				if($("#total_hours_"+index).val() != "")
				{
						hour_filter = $("#total_hours_"+index).val().split(":");
						minutes_to_hours = parseInt(hour_filter[1])/60;
						total_hour = parseInt(hour_filter[0]) + parseFloat(minutes_to_hours);
						total_hours = total_hours + parseFloat(getRoundOffMinutes_time(total_hour,timeincrements));
				}
				
			}
			if(cico_pipo_rule == "Y" && rule_type == "dayrule")
			{
					regular_hours = (total_hours > maxregularhours)?maxregularhours:total_hours;
					over_time_hours = ((total_hours-regular_hours) > maxovertimehours)?maxovertimehours:total_hours-regular_hours;
					double_time_hours = total_hours - (regular_hours + over_time_hours);//

					$("#regular_hours").html("Regular Hours: "+parseFloat(regular_hours).toFixed(2));
					$("#over_time_hours").html("Over Time Hours: "+parseFloat(over_time_hours).toFixed(2));
					$("#double_time_hours").html("Double Time Hours: "+parseFloat(double_time_hours).toFixed(2));

					$("#hours_rate1").val(parseFloat(regular_hours).toFixed(2));
					$("#hours_rate2").val(parseFloat(over_time_hours).toFixed(2));
					$("#hours_rate3").val(parseFloat(double_time_hours).toFixed(2));
			}
			else
			{	

					$("#regular_hours").html("Regular Hours: "+parseFloat(total_hours).toFixed(2));
					$("#hours_rate1").val(parseFloat(total_hours).toFixed(2));

					$("#over_time_hours").html("Over Time Hours: "+parseFloat(over_time_hours).toFixed(2));
					$("#double_time_hours").html("Double Time Hours: "+parseFloat(double_time_hours).toFixed(2));

					$("#hours_rate2").val(parseFloat(over_time_hours).toFixed(2));
					$("#hours_rate3").val(parseFloat(double_time_hours).toFixed(2));
			}
			
		
			$("#final_total_hours").html(parseFloat(total_hours).toFixed(2))
	});
}

function applyDayRulesToWeekly()
{

	var timesheet_snos = [];
	var cico_pipo_rule = $("#cico_pipo_rule").val();
	var timeincrements = $("#timeincrements").val();
	var row_total_hours = 0;
	var seventhdayrule = $("#seventhdayrule").val();
	maxregularhours = parseFloat($("#maxregularhours").val());
	maxovertimehours = parseFloat($("#maxovertimehours").val());
	maxregularhours_perweek = parseFloat($("#maxregularhours_perweek").val());
	maxovertimehours_perweek = parseFloat($("#maxovertimehours_perweek").val());
	var rows_completed_calculation = [];
	var sixth_day_over_time = 12;
	var seventh_day_over_time = 8;
	var consecutive_days = [];
	var location = $("#asglocation").val();
	$(".weekly_row").each(function(index,element){
			if($.inArray($(element).data("thsno"),timesheet_snos) == -1)
			{
				timesheet_snos.push($(element).data("thsno"));
				consecutive_days.push($(element).data("day"));
			}
	});

	if(seventhdayrule == 0)
	{
		for(var i=0;i<timesheet_snos.length;i++)
		{
				var minutes_to_hours = 0;
				var regular_hours = 0.00;
				var over_time_hours = 0.00;
				var double_time_hours = 0.00;
				var total_hours = 0;

				$(" tr[data-thsno='"+timesheet_snos[i]+"']").each(function(parentindex,parentelement){

						if($(parentelement).find(".rowBreaktime").val() != "")
						{
								hour_filter = $(parentelement).find(".rowBreaktime").val().split(":");
								minutes_to_hours = parseInt(hour_filter[1])/60;
								total_hour = parseInt(hour_filter[0]) + parseFloat(minutes_to_hours);
								total_hours = total_hours + parseFloat(getRoundOffMinutes_time(total_hour,timeincrements));
								row_total_hours = row_total_hours + parseInt(hour_filter[0]) + parseFloat(getRoundOffMinutes_time(minutes_to_hours,timeincrements));

							if(cico_pipo_rule == "Y")
							{
									regular_hours = (total_hours > maxregularhours)?maxregularhours:total_hours;
									over_time_hours = ((total_hours-regular_hours) > maxovertimehours)?maxovertimehours:total_hours-regular_hours;
									double_time_hours = total_hours - (regular_hours + over_time_hours);//
									
									$("#regular_hours_"+$(parentelement).find(".rowBreaktime").data("thsno")).html("Regular Hours: "+parseFloat(regular_hours).toFixed(2));
									$("#over_time_hours_"+$(parentelement).find(".rowBreaktime").data("thsno")).html("Over Time Hours: "+parseFloat(over_time_hours).toFixed(2));
									$("#double_time_hours_"+$(parentelement).find(".rowBreaktime").data("thsno")).html("Double Time Hours: "+parseFloat(double_time_hours).toFixed(2));

									$("#hours_rate_1_"+$(parentelement).find(".rowBreaktime").data("thsno")).val(parseFloat(regular_hours).toFixed(2));
									$("#hours_rate_2_"+$(parentelement).find(".rowBreaktime").data("thsno")).val(parseFloat(over_time_hours).toFixed(2));
									$("#hours_rate_3_"+$(parentelement).find(".rowBreaktime").data("thsno")).val(parseFloat(double_time_hours).toFixed(2));
							}
							else
							{		
								$("#regular_hours_"+$(parentelement).find(".rowBreaktime").data("thsno")).html("Regular Hours: "+parseFloat(total_hours).toFixed(2));
								$("#hours_rate_1_"+$(parentelement).find(".rowBreaktime").data("thsno")).val(parseFloat(total_hours).toFixed(2));
							
								$("#over_time_hours_"+$(parentelement).find(".rowBreaktime").data("thsno")).html("Over Time Hours: "+parseFloat(over_time_hours).toFixed(2));
								$("#double_time_hours_"+$(parentelement).find(".rowBreaktime").data("thsno")).html("Double Time Hours: "+parseFloat(double_time_hours).toFixed(2));
							
								$("#hours_rate_2_"+$(parentelement).find(".rowBreaktime").data("thsno")).val(parseFloat(over_time_hours).toFixed(2));
								$("#hours_rate_3_"+$(parentelement).find(".rowBreaktime").data("thsno")).val(parseFloat(double_time_hours).toFixed(2));
							}
							

							$("#final_total_hours").html(parseFloat(row_total_hours).toFixed(2));
						}


				});
		}
	}
	else
	{
		
		for(var i=0;i<timesheet_snos.length;i++)
		{
				var minutes_to_hours = 0;
				var regular_hours = 0.00;
				var over_time_hours = 0.00;
				var double_time_hours = 0.00;
				var total_hours = 0;
				if(consecutive_days.length == "7" && consecutive_days[0] == "Monday" && consecutive_days[consecutive_days.length-1] == "Sunday" && location == "CA")
				{		
						$(" tr[data-thsno='"+timesheet_snos[i]+"']").each(function(parentindex,parentelement){
						
								if($(parentelement).find(".rowBreaktime").val() != "")
								{
										hour_filter = $(parentelement).find(".rowBreaktime").val().split(":");
										minutes_to_hours = parseInt(hour_filter[1])/60;
										total_hour = parseInt(hour_filter[0]) + parseFloat(minutes_to_hours);
										total_hours = total_hours + parseFloat(getRoundOffMinutes_time(total_hour,timeincrements));
										row_total_hours = row_total_hours + parseInt(hour_filter[0]) + parseFloat(getRoundOffMinutes_time(minutes_to_hours,timeincrements));

									if(cico_pipo_rule == "Y")
									{		
											if(i <= 4)
											{
													regular_hours = (total_hours > maxregularhours)?maxregularhours:total_hours;
													over_time_hours = ((total_hours-regular_hours) > maxovertimehours)?maxovertimehours:total_hours-regular_hours;
													double_time_hours = total_hours - (regular_hours + over_time_hours);//
											}
											else
											{
													if(i == 5)
													{
														regular_hours = (row_total_hours > maxregularhours_perweek)?0.00:total_hours;
														over_time_hours = ((total_hours-regular_hours) > sixth_day_over_time)?sixth_day_over_time:total_hours-regular_hours;
														double_time_hours = ((total_hours-regular_hours) > sixth_day_over_time)?(total_hours - over_time_hours):0.00;
													}

													if(i == 6)
													{
														regular_hours = (row_total_hours > maxregularhours_perweek)?'0.00':total_hours;
														over_time_hours = ((total_hours-regular_hours) > seventh_day_over_time)?seventh_day_over_time:total_hours-regular_hours;
														double_time_hours = ((total_hours-regular_hours) > seventh_day_over_time)?(total_hours - over_time_hours):0.00;
													}
													
											}
										
											
											$("#regular_hours_"+$(parentelement).find(".rowBreaktime").data("thsno")).html("Regular Hours: "+parseFloat(regular_hours).toFixed(2));
											$("#over_time_hours_"+$(parentelement).find(".rowBreaktime").data("thsno")).html("Over Time Hours: "+parseFloat(over_time_hours).toFixed(2));
											$("#double_time_hours_"+$(parentelement).find(".rowBreaktime").data("thsno")).html("Double Time Hours: "+parseFloat(double_time_hours).toFixed(2));

											$("#hours_rate_1_"+$(parentelement).find(".rowBreaktime").data("thsno")).val(parseFloat(regular_hours).toFixed(2));
											$("#hours_rate_2_"+$(parentelement).find(".rowBreaktime").data("thsno")).val(parseFloat(over_time_hours).toFixed(2));
											$("#hours_rate_3_"+$(parentelement).find(".rowBreaktime").data("thsno")).val(parseFloat(double_time_hours).toFixed(2));
									}
									else
									{		
										$("#regular_hours_"+$(parentelement).find(".rowBreaktime").data("thsno")).html("Regular Hours: "+parseFloat(total_hours).toFixed(2));
										$("#hours_rate_1_"+$(parentelement).find(".rowBreaktime").data("thsno")).val(parseFloat(total_hours).toFixed(2));
									
										$("#over_time_hours_"+$(parentelement).find(".rowBreaktime").data("thsno")).html("Over Time Hours: "+parseFloat(over_time_hours).toFixed(2));
										$("#double_time_hours_"+$(parentelement).find(".rowBreaktime").data("thsno")).html("Double Time Hours: "+parseFloat(double_time_hours).toFixed(2));
									
										$("#hours_rate_2_"+$(parentelement).find(".rowBreaktime").data("thsno")).val(parseFloat(over_time_hours).toFixed(2));
										$("#hours_rate_3_"+$(parentelement).find(".rowBreaktime").data("thsno")).val(parseFloat(double_time_hours).toFixed(2));
									}
									

									$("#final_total_hours").html(parseFloat(row_total_hours).toFixed(2));
								}


							});
						
				}
				else
				{
						$(" tr[data-thsno='"+timesheet_snos[i]+"']").each(function(parentindex,parentelement){
						
							if($(parentelement).find(".rowBreaktime").val() != "")
							{
									hour_filter = $(parentelement).find(".rowBreaktime").val().split(":");
									minutes_to_hours = parseInt(hour_filter[1])/60;
									total_hour = parseInt(hour_filter[0]) + parseFloat(minutes_to_hours);
									total_hours = total_hours + parseFloat(getRoundOffMinutes_time(total_hour,timeincrements));
									row_total_hours = row_total_hours + parseInt(hour_filter[0]) + parseFloat(getRoundOffMinutes_time(minutes_to_hours,timeincrements));

								if(cico_pipo_rule == "Y")
								{
										regular_hours = (total_hours > maxregularhours)?maxregularhours:total_hours;
										over_time_hours = ((total_hours-regular_hours) > maxovertimehours)?maxovertimehours:total_hours-regular_hours;
										double_time_hours = total_hours - (regular_hours + over_time_hours);//
										
										$("#regular_hours_"+$(parentelement).find(".rowBreaktime").data("thsno")).html("Regular Hours: "+parseFloat(regular_hours).toFixed(2));
										$("#over_time_hours_"+$(parentelement).find(".rowBreaktime").data("thsno")).html("Over Time Hours: "+parseFloat(over_time_hours).toFixed(2));
										$("#double_time_hours_"+$(parentelement).find(".rowBreaktime").data("thsno")).html("Double Time Hours: "+parseFloat(double_time_hours).toFixed(2));

										$("#hours_rate_1_"+$(parentelement).find(".rowBreaktime").data("thsno")).val(parseFloat(regular_hours).toFixed(2));
										$("#hours_rate_2_"+$(parentelement).find(".rowBreaktime").data("thsno")).val(parseFloat(over_time_hours).toFixed(2));
										$("#hours_rate_3_"+$(parentelement).find(".rowBreaktime").data("thsno")).val(parseFloat(double_time_hours).toFixed(2));
								}
								else
								{		
									$("#regular_hours_"+$(parentelement).find(".rowBreaktime").data("thsno")).html("Regular Hours: "+parseFloat(total_hours).toFixed(2));
									$("#hours_rate_1_"+$(parentelement).find(".rowBreaktime").data("thsno")).val(parseFloat(total_hours).toFixed(2));
								
									$("#over_time_hours_"+$(parentelement).find(".rowBreaktime").data("thsno")).html("Over Time Hours: "+parseFloat(over_time_hours).toFixed(2));
									$("#double_time_hours_"+$(parentelement).find(".rowBreaktime").data("thsno")).html("Double Time Hours: "+parseFloat(double_time_hours).toFixed(2));
								
									$("#hours_rate_2_"+$(parentelement).find(".rowBreaktime").data("thsno")).val(parseFloat(over_time_hours).toFixed(2));
									$("#hours_rate_3_"+$(parentelement).find(".rowBreaktime").data("thsno")).val(parseFloat(double_time_hours).toFixed(2));
								}
								

								$("#final_total_hours").html(parseFloat(row_total_hours).toFixed(2));
							}


						});
				}
				
		}
	}
	
	
}

function applyWeekRulesToWeekly()
{
	var timeincrements = $("#timeincrements").val();
	var timesheet_snos = [];
	var weekly_total_hours = 0.00;
	var total_regular_hours = 0.00;
	var total_over_time_hours = 0.00;
	var total_double_time_hours = 0.00;
	maxregularhours_perweek = parseFloat($("#maxregularhours_perweek").val());
	maxovertimehours_perweek = parseFloat($("#maxovertimehours_perweek").val());
	
	$(".weekly_row").each(function(index,element){
			if($.inArray($(element).data("thsno"),timesheet_snos) == -1)
			{
				timesheet_snos.push($(element).data("thsno"));
			}
	});

	for(var i=0;i<timesheet_snos.length;i++)
	{	
			var minutes_to_hours = 0;
			var regular_hours = 0.00;
			var over_time_hours = 0.00;
			var double_time_hours = 0.00;
			var over_time_exists = 0.00;
			var regular_time_exists = 0.00;
			var total_hours = 0.00;

			$("tr[data-thsno='"+timesheet_snos[i]+"']").each(function(parentindex,parentelement)
			{		
					if($(parentelement).find(".rowBreaktime").val() != "")
					{		
							hour_filter = $(parentelement).find(".rowBreaktime").val().split(":");
							minutes_to_hours = parseInt(hour_filter[1])/60;
							total_hour = parseInt(hour_filter[0]) + parseFloat(minutes_to_hours);
							total_hours = total_hours + parseFloat(getRoundOffMinutes_time(total_hour,timeincrements));
							weekly_total_hours = weekly_total_hours + parseFloat(getRoundOffMinutes_time(total_hour,timeincrements));
							total_regular_hours = maxregularhours_perweek - weekly_total_hours;

							if(cico_pipo_rule == "Y")
							{	
								regular_hours = total_hours;
								if($("tr[data-thsno='"+timesheet_snos[i]+"']").length-1 == parentindex)
								{		
										if(weekly_total_hours < maxregularhours_perweek)
										{		
												regular_hours = total_hours;
										}

										if(weekly_total_hours > maxregularhours_perweek)
										{		
												regular_hours = 0.00;
												if(total_hours-(weekly_total_hours-maxregularhours_perweek) > 0)
												{
													regular_hours = total_hours-(weekly_total_hours-maxregularhours_perweek);
												}
												
												over_time_hours = 0.00;
												
												if(weekly_total_hours-(maxregularhours_perweek) < maxovertimehours_perweek)
												{		
														if(over_time_hours < maxovertimehours_perweek)
														{
																over_time_hours = weekly_total_hours-(maxregularhours_perweek);
																total_over_time_hours = total_over_time_hours+over_time_hours;
														}
												}
												
										}

										if(weekly_total_hours == maxregularhours_perweek)
										{		
												if(total_hours-(weekly_total_hours) > 0)
												{
													regular_hours = total_hours-(weekly_total_hours);
												}
										}
										
										if((maxregularhours_perweek+maxovertimehours_perweek) < weekly_total_hours)
										{		
												
												over_time_hours = 0.00;
												if(weekly_total_hours-(maxregularhours_perweek) < maxovertimehours_perweek)
												{
														over_time_hours = weekly_total_hours-(maxregularhours_perweek);
												}
												else
												{		
														over_time_hours = maxovertimehours_perweek-total_over_time_hours;
														total_over_time_hours = total_over_time_hours+over_time_hours;
														
												}


												double_time_hours = (weekly_total_hours-(maxregularhours_perweek+maxovertimehours_perweek+total_double_time_hours));
												total_double_time_hours = total_double_time_hours+double_time_hours;
										}

										if(double_time_hours > (maxregularhours_perweek+maxovertimehours_perweek))
										{
											double_time_hours
										}

								}
								
								$("#regular_hours_"+$(parentelement).find(".rowBreaktime").data("thsno")).html("Regular Hours: "+parseFloat(regular_hours).toFixed(2));
								$("#over_time_hours_"+$(parentelement).find(".rowBreaktime").data("thsno")).html("Over Time Hours: "+parseFloat(over_time_hours).toFixed(2));
								$("#double_time_hours_"+$(parentelement).find(".rowBreaktime").data("thsno")).html("Double Time Hours: "+parseFloat(double_time_hours).toFixed(2));

								$("#hours_rate_1_"+$(parentelement).find(".rowBreaktime").data("thsno")).val(parseFloat(regular_hours).toFixed(2));
								$("#hours_rate_2_"+$(parentelement).find(".rowBreaktime").data("thsno")).val(parseFloat(over_time_hours).toFixed(2));
								$("#hours_rate_3_"+$(parentelement).find(".rowBreaktime").data("thsno")).val(parseFloat(double_time_hours).toFixed(2));
								$("#final_total_hours").html(parseFloat(weekly_total_hours).toFixed(2));
							}
						
					}
			});

			
	}
	
}


function getRoundOffMinutes_time(hr_min, step) {
	// Converts input time in seconds
	seconds = hr_min*60*60;
	if(step != 0 && step != ''){
	var offset = seconds % (step*60); // step is in minutes
	
	if (offset >= step*30) {
		// if offset is larger than a half step, round up
		seconds += (step*60) - offset;
	} else {
		// round down
		seconds -= offset;
	}
	var output = (seconds/60)/60;

	// Exception - 11:59 PM (23 hours) rounded to 12:00 AM (next day) which is incorrect as per requirement
	// In such cases, rounded to 11:59 PM and not 12:00 AM	
	/*if (hours == "23" && output == "12:00 AM") {
		output =  "11:59 PM";
	}*/
	return output;
	}
	else {
		return hr_min;
	}
}

function loadRowZebraDatePicker(rowId,isfirstElement)
{
	var sdate ="";
	var added_sdates = [];
	
	var start_date = $("#shift_startdate_"+rowId).val();
	var end_date = $("#shift_enddate_"+rowId).val();

	if(start_date=="")
	{
		var start_date = $("#csdate_"+rowId).val();
	}

	if(end_date =="")
	{
		var end_date = $("#cedate_"+rowId).val();
	}
	
	if(isfirstElement)
	{
		$("#csdate_"+rowId).Zebra_DatePicker({
			format: 'm/d/Y',
			direction:[start_date,start_date],
			show_clear_date:false,
			always_visible:false,
			onSelect:function(){sDateChange(this)},
		});
	}
	else
	{
		$("#csdate_"+rowId).Zebra_DatePicker({
			format: 'm/d/Y',
			direction:[start_date,end_date],
			show_clear_date:false,
			always_visible:false,
			onSelect:function(){rowSDateChange(this,rowId)},
		});
	}


	$("#cedate_"+rowId).Zebra_DatePicker({
    format: 'm/d/Y',
    direction:[start_date,end_date],
    show_clear_date:false,
    always_visible:false,
    onSelect:function(){rowEDateChange(this,rowId)},
	});

}

function loadZebraDatePicker()
{
	var sdate ="";
	var added_sdates = [];
	
	$(".csdate").each(function(index,element){

		var start_date = $("#shift_startdate_"+index).val();
		var end_date = $("#shift_enddate_"+index).val();

		if(start_date=="")
		{
			var start_date = $("#csdate_"+index).val();
		}

		if(end_date =="")
		{
			var end_date = $("#cedate_"+index).val();
		}
			
		if($.inArray(start_date,added_sdates) == -1)
		{
			$(element).Zebra_DatePicker({
				format: 'm/d/Y',
				direction:[start_date,start_date],
				show_clear_date:false,
				always_visible:false,
				onSelect:function(){sDateChange(this)},
			});
  			added_sdates.push(start_date);
		}
		else
		{
			$(element).Zebra_DatePicker({
				format: 'm/d/Y',
				direction:[start_date,end_date],
				show_clear_date:false,
				always_visible:false,
				onSelect:function(){sDateChange(this)},
			});
		}
		
	});

  	$(".cedate").each(function(index,element){

  		var start_date = $("#shift_startdate_"+index).val();
		var end_date = $("#shift_enddate_"+index).val();
		if(start_date=="")
		{
			var start_date = $("#csdate_"+index).val();
		}

		if(end_date =="")
		{
			var end_date = $("#cedate_"+index).val();
		}
		
		$(element).Zebra_DatePicker({
			format: 'm/d/Y',
			direction:[start_date,end_date],
			show_clear_date:false,
			always_visible:false,
			onSelect:function(){eDateChange(this)},
		});
  	});

}

function sDateChange(element)
{
	var options = { year: 'numeric', month: 'short', day: 'numeric' };
	var startDate = new Date($(element).val());
	var inputElement = $(element)[0];
	var endDate = "";
	var endDateValue = "";
	for(var i=0;i<$(".cedate").length-1;i++){
			var element = $(".cedate")[i];
			if($(element).val() !="")
			{
				endDate = new Date($(element).val());
				endDateValue = $(element).val();
			}
	}

	if(endDate < startDate)
	{
		$("#timesheet_stdate_"+$(inputElement).data("rowid")).html(startDate.toLocaleDateString("en-US", options).replace(",",""));
		$("#timesheet_etdate_"+$(inputElement).data("rowid")).html(startDate.toLocaleDateString("en-US", options).replace(",",""));
		$("#csdate_"+$(inputElement).data("rowid")).val($("#csdate_"+$(inputElement).data("rowid")).val());
		$("#cedate_"+$(inputElement).data("rowid")).val($("#csdate_"+$(inputElement).data("rowid")).val());
	}
	else
	{
		$("#timesheet_stdate_"+$(inputElement).data("rowid")).html(endDate.toLocaleDateString("en-US", options).replace(",",""));
		$("#timesheet_etdate_"+$(inputElement).data("rowid")).html(endDate.toLocaleDateString("en-US", options).replace(",",""));
		$("#csdate_"+$(inputElement).data("rowid")).val(endDateValue);
		$("#cedate_"+$(inputElement).data("rowid")).val(endDateValue);
	}

	
	reCalculateCICOHrs();
}

function eDateChange(element)
{
	var options = { year: 'numeric', month: 'short', day: 'numeric' };
	var endDate = new Date($(element).val()).toLocaleDateString("en-US", options).replace(",","");
	var inputElement = $(element)[0];

	var startDate = new Date($("#csdate_"+$(inputElement).data("rowid")).val());
	var endDate = new Date($("#cedate_"+$(inputElement).data("rowid")).val());

	if(endDate < startDate)
	{
		$("#timesheet_etdate_"+$(inputElement).data("rowid")).html(startDate.toLocaleDateString("en-US", options).replace(",",""));
		$("#cedate_"+$(inputElement).data("rowid")).val($("#csdate_"+$(inputElement).data("rowid")).val());
	}
	else
	{
		
		$("#timesheet_etdate_"+$(inputElement).data("rowid")).html(endDate.toLocaleDateString("en-US", options).replace(",",""));
		$("#cedate_"+$(inputElement).data("rowid")).val($("#cedate_"+$(inputElement).data("rowid")).val());
	}
	

	reCalculateCICOHrs();
}


function rowSDateChange(element,rowId)
{
	var options = { year: 'numeric', month: 'short', day: 'numeric' };
	var startDate = new Date($(element).val());
	var inputElement = $(element)[0];
	var endDate = "";
	var endDateValue = "";
	var parentTrElement = $(element).closest("tr").data("thsno");
	$(".weekdays_"+parentTrElement).each(function(index,childElement){
			var elementIndex = $(childElement).find(".cedate").attr("id").match(/\d/g).join("");
			if($(childElement).find(".cedate").val() !="" && elementIndex<rowId)
			{
				endDate = new Date($(childElement).find(".cedate").val());
				endDateValue = $(childElement).find(".cedate").val();
			}
	});

	if(endDate < startDate)
	{
		$("#timesheet_stdate_"+$(inputElement).data("rowid")).html(startDate.toLocaleDateString("en-US", options).replace(",",""));
		$("#timesheet_etdate_"+$(inputElement).data("rowid")).html(startDate.toLocaleDateString("en-US", options).replace(",",""));
		$("#csdate_"+$(inputElement).data("rowid")).val($("#csdate_"+$(inputElement).data("rowid")).val());
		$("#cedate_"+$(inputElement).data("rowid")).val($("#csdate_"+$(inputElement).data("rowid")).val());
	}
	else
	{
		$("#timesheet_stdate_"+$(inputElement).data("rowid")).html(endDate.toLocaleDateString("en-US", options).replace(",",""));
		$("#timesheet_etdate_"+$(inputElement).data("rowid")).html(endDate.toLocaleDateString("en-US", options).replace(",",""));
		$("#csdate_"+$(inputElement).data("rowid")).val(endDateValue);
		$("#cedate_"+$(inputElement).data("rowid")).val(endDateValue);
	}

	
	reCalculateCICOHrs();
}

function rowEDateChange(element,rowId)
{

	var options = { year: 'numeric', month: 'short', day: 'numeric' };
	var endDate = new Date($(element).val()).toLocaleDateString("en-US", options).replace(",","");
	var inputElement = $(element)[0];

	var startDate = new Date($("#csdate_"+$(inputElement).data("rowid")).val());
	var endDate = new Date($("#cedate_"+$(inputElement).data("rowid")).val());

	if(endDate < startDate)
	{
		$("#timesheet_etdate_"+$(inputElement).data("rowid")).html(startDate.toLocaleDateString("en-US", options).replace(",",""));
		$("#cedate_"+$(inputElement).data("rowid")).val($("#csdate_"+$(inputElement).data("rowid")).val());
	}
	else
	{
		$("#timesheet_etdate_"+$(inputElement).data("rowid")).html(endDate.toLocaleDateString("en-US", options).replace(",",""));
		$("#cedate_"+$(inputElement).data("rowid")).val($("#cedate_"+$(inputElement).data("rowid")).val());
	}
	

	reCalculateCICOHrs();
}

function newClockinout(){

	var v_heigth = 600;
	var v_width	= 1200;
	var form	= document.timesheet;
	var url = "newcico.php";	
	var name	= "savedtimesheet";
	var top1	= (window.screen.availHeight-v_heigth)/2;
	var left1	= (window.screen.availWidth-v_width)/2;
	var remoter	= window.open(url, name, "width="+v_width+"px,height="+v_heigth+"px,resizable=yes,scrollbars=yes,left="+left1+"px,top="+top1+"px,status=0");
	remoter.focus();
}

function getEmp()
{
	form=document.sheet;
	form.action="newcico.php";
	form.submit();
}

var doNewSaveCiCoTime  = 0;
function doSaveNewCiCoTime() {
	var canSaveTimesheet = $("#canSave").val();
	if(doNewSaveCiCoTime  == 0 && canSaveTimesheet == 'Y')
	{		
		var validation = validateEditSaveClockInClockOut();
		reCalculateCICOHrs();
		
		if (validation) {	
			
			if(confirm("Please note saved timesheet can't be deleted. Do you want to proceed?"))
			{	
				var form	= document.sheet;
				$(form).append('<input type="hidden" id="mode" name="mode" value="Saved"/>');
				endshiftCheck = checkEndShiftTimesheet(form).split("|");
				if(endshiftCheck[0] == "false")
				{
					alert(endshiftCheck[1]);
					return false;
				}
				doNewSaveCiCoTime++;
				form.action	= "/BSOS/Accounting/Time_Mngmt/savecicotime.php?timesheet=new";
				form.submit();
			}
		}
	}
	else
	{
		alert("You can create a timesheet only for last 7 days");
		return false;
	}
	
}

var doNewSubmitCiCoTime  = 0;
function doSubmitNewCiCoTime() {

	if(doNewSubmitCiCoTime  == 0)
	{		
		var validation = validateEditClockInClockOut();
		reCalculateCICOHrs();
		if (validation) {
					
			var form	= document.sheet;
			$(form).append('<input type="hidden" id="mode" name="mode" value="Submitted"/>');
			duplicate = checkDuplicateTimesheet(form).split("|");
			if(duplicate[2] == "true")
			{
				alert(duplicate[3]);
				return false;
			}
			if(duplicate[0] == "true")
			{
				if(confirm(duplicate[1]))
				{
					doNewSubmitCiCoTime++;
					form.action	= "/BSOS/Accounting/Time_Mngmt/savecicotime.php?timesheet=new";
					form.submit();
				}
				else
				{
					return false;
				}
			}
			else
			{
				doNewSubmitCiCoTime++;
				form.action	= "/BSOS/Accounting/Time_Mngmt/savecicotime.php?timesheet=new";
				form.submit();
			}
			
		}
	}
	
}

function checkDuplicateTimesheet(formData)
{
	result = false;
	$.ajax({
		type: "POST",
		url: "./getSelectorData.php",
		data:$(formData).serializeArray(),
		dataType:"text",
		async:false,
		success:function(response){
			if(response)
			{
				result = response;
			}
			
		},
		error:function(){},
	})

	return result;
}

function checkEndShiftTimesheet(formData)
{
	result = false;
	$.ajax({
		type: "POST",
		url: "./getSelectorData.php",
		data:$(formData).serializeArray(),
		dataType:"text",
		async:false,
		success:function(response){
			if(response)
			{
				result = response;
			}
			
		},
		error:function(){},
	})

	return result;
}

function numSelected()
{
	var e = document.getElementsByName("auids[]");
	var bNone = true;
	var iFound = 0;
	for (var i=0; i < e.length; i++)
	{
		if (e[i].name == "auids[]")
		{
			bNone = false;
			if (e[i].checked == true)
				iFound++;
		}
	}
	if (bNone)
	{
		iFound = -1;
	}
	return iFound;
}

function valSelected()
{
	var e = document.getElementsByName("auids[]");
	//alert(e); 
	var bNone = true;
	var iVal = "";
	for (var i=0; i < e.length; i++)
	{
		if (e[i].name == "auids[]")
		{
			bNone = false;
			if (e[i].checked == true) {
				if(iVal=="")
					iVal=e[i].value;
				else
					iVal+=","+e[i].value;
				
				//alert(iVal);
			}
		}
	}
	if (bNone)
	{
		iVal = "";
	}
	return iVal;
}

/*function doCicoNotify(id) 
{
	numAddrs = numSelected();
	chkbox = valSelected();
	
	//alert(numAddrs +"@"+chkbox);
	if(numAddrs < 0)
	{
		alert("No employees are available to send mail.");
		return;
	}
	if(!numAddrs)
	{
		alert("Please select at least one employees to send mail");
		return;
	}
	else
	{
		empid = Array();	
		empidArr = chkbox.split(',');
		
		for(var c=0;c<empidArr.length;c++){
			if(empidArr[c]!="")
				empid.push(empidArr[c]);
		}
		var empIds = empid.toString();
		var lcount = document.getElementById("lcount").value;
		
		if(lcount == 5) 
		{
			alert('Unable to connect to your outgoing notifications email account. All failed notifications are visible in "notification Error Logs" under Admin - Notification Management');
		}
		else
		{
			var v_width  = 800;
			var v_heigth = 650;
			var top1  = (window.screen.availHeight-v_heigth)/2;
			var left1 = (window.screen.availWidth-v_width)/2;

			var parms = "width="+v_width+"px,";
			parms += "height="+v_heigth+"px,";
			parms += "left="+left1+"px,";
			parms += "top="+top1+"px,";
			parms += "statusbar=no,";
			parms += "menubar=no,";
			parms += "scrollbars=yes,";
			parms += "dependent=yes,";
			parms += "resizable=yes";

			top.remattachfiles_cand=window.open("showTemplatePreview.php?tmpId="+id+"&empTID="+empIds+"&onetime_edit=1","newView",parms);	
			top.remattachfiles_cand.focus();
		}
	} //else condition end
}*/

function doCicoNotify(id) 
{
	numAddrs = numSelected();
	chkbox = valSelected();
	//chkbox = "2|30/11/2022|10AM - 11PM,5|28/11/2022|09AM - 03PM,15||";
	
	//alert(numAddrs +"@"+chkbox); return false;
	if(numAddrs < 0)
	{
		alert("No employees are available to send mail.");
		return;
	}
	if(!numAddrs)
	{
		alert("Please select at least one employees to send mail");
		return;
	}
	else
	{
		emp_id_array = Array();	
		shift_date_array = Array();	
		shift_time_array = Array();
		UniAssignId = Array();		
		assgnstatus	= 0;
		assgnjobtype = 0;
		assgndetArr = chkbox.split(',');
		for(var c=0;c<assgndetArr.length;c++){
			assgndataArr = assgndetArr[c].split('|');
			for(var i=0;i<assgndataArr.length;i++){
				if(i == 0) {
					emp_id_array.push(assgndataArr[0]);
					shift_date_array.push(assgndataArr[1]);
					shift_time_array.push(assgndataArr[2]);
				}
			}
		}
		
		var emp_ids = emp_id_array.toString();
		var shift_dates = shift_date_array.toString();
		var shift_times = shift_time_array.toString();		
		//alert(emp_ids + "@" + shift_dates + "@" + shift_times); return false;
		
		var lcount = document.getElementById("lcount").value;
		
		if(lcount == 5) 
		{
			alert('Unable to connect to your outgoing notifications email account. All failed notifications are visible in "notification Error Logs" under Admin - Notification Management');
		}
		else
		{
			//pass shift dates, time to ajax for creating sessions
			$.ajax({
				url: "createShiftSessions.php",
				data: "cicoTempSessionType=1&&cicoSessionShiftDates="+encodeURI(shift_dates)+"&&cicoSessionShiftTimes="+encodeURI(shift_times),
				type: 'POST',
				success: function (data)
				{	
					console.log(data);
					//alert(data);
					return false;
				}
			});
			
			var v_width  = 800;
			var v_heigth = 650;
			var top1  = (window.screen.availHeight-v_heigth)/2;
			var left1 = (window.screen.availWidth-v_width)/2;

			var parms = "width="+v_width+"px,";
			parms += "height="+v_heigth+"px,";
			parms += "left="+left1+"px,";
			parms += "top="+top1+"px,";
			parms += "statusbar=no,";
			parms += "menubar=no,";
			parms += "scrollbars=yes,";
			parms += "dependent=yes,";
			parms += "resizable=yes";

			top.remattachfiles_cand=window.open("showTemplatePreview.php?tmpId="+id+"&empTID="+emp_ids+"&onetime_edit=1","newView",parms);	
			top.remattachfiles_cand.focus();
		}
		
	} //else condition end
}

function editPreviewTmpl(id,module,empID) 
{
    var v_width = window.screen.availWidth * 0.75;
    var v_heigth = 500;
	var top1  = (window.screen.availHeight-v_heigth)/2;
	var left1 = (window.screen.availWidth-v_width)/2;

	var parms = "width="+v_width+"px,";
	parms += "height="+v_heigth+"px,";
	parms += "left="+left1+"px,";
	parms += "top="+top1+"px,";
	parms += "statusbar=no,";
	parms += "menubar=no,";
	parms += "scrollbars=yes,";
	parms += "dependent=yes,";
	parms += "resizable=yes";

	window.location.href = 'cico_email_tmpl.php?ecamptype='+id+'&empID='+empID;
}

function sendCICONotifications()					
{
	var notify_status 	= 	document.getElementById("status").value;
	var notify_id 		= 	document.getElementById("temp_id").value;
	var templateType 	= 	document.getElementById("template_type").value;
	var selids 			= 	document.getElementById("assignIds").value;
	var onetime_edit 			= 	document.getElementById("onetime_edit").value;

	//console.log(notify_status+' ----- '+notify_id+' ----- '+selids+' ----- '+templateType);

	var url 	= "sendCICONotifications.php"; //sendEmpNotifications
	var content = "selids="+selids+"&notid="+notify_id+"&templateType="+templateType+"&onetime_edit="+onetime_edit;
	var rtype 	= 'bulkassignemail';
	doSendCICONotificationAjaxProgressBar(url,rtype,content);
}

function doSendCICONotificationAjaxProgressBar(url,rtype,content) 
{
	$("#body_glow_small").show();
	$("#processBar").show();
	cicoProcessBarTimer("0");

	$.ajax({
    	url: url,
    	data: content,
    	type: 'POST',
    	xhrFields: {
			onprogress: function(e) 
			{				
				if (e.target.responseText) 
				{
					var str=e.target.responseText;
					split = str.match(/(.*)\.(.*)/);
					var percent = split[2];
					cicoProcessBarTimer(percent);
				}
			}
		},
		success: function(text) 
		{
			text = text.split('^^');
			cicoProcessBarTimer('100');
			setTimeout(function() 
			{
   				var template_type = document.getElementById('template_type').value;
   				var msg ='Notification Send Successfully!';
   				var blockedMsg = 'Unable to connect to your outgoing notifications email account. All failed notifications are visible in "notification Error Logs" under Admin - Notification Management';
   				var failMsg = 'From chosen records some of email address are invalid, for such records mail has not been dispatched or setup is not setup legitimately';
   				
				if(text[1] == 'Blocked' || text[2] == 'Blckd') 
				{
					var msg = blockedMsg;
				}
				else if(text[1] == 'Success') 
				{
					var msg = 'Notification Successfully sent to Employees(s)';
				}
				else if(text[1] == 'Failure') 
				{
					var msg = failMsg;
				}
				else if(text[1] == 'DisTemp') 
				{
					var msg = 'Email notification option disabled in Template';
				}
				
				alert(msg);
				window.close();
				window.opener.location.reload(false);
  			},500);	
            //alert(text);	return false;		
		}	       
	});
	return;
}

function cicoProcessBarTimer(timer) 
{
    var elem = document.getElementById("processBarTimer");   
	var width = timer;
	elem.style.width = width + '%'; 
	elem.innerHTML = width * 1  + '%';
}
