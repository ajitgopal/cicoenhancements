var xtreeresult     = "";
var xtreemove       = "";
var tree            = "";
var selectedFldId   = "";
var ses_exp_rlink   = "";
var SesExpMsgText   = "There was a problem retrieving the data:\n";
var SesExpText      = "May be your session is expired";
var emailRegExp     = /^[a-z0-9!#$%&'*+/=/?_`~-]+(?:\.[a-z0-9!#$%&'*+/=/?_`~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/i;

function createCookie(name, value, days)
{
        if(value == 'NaN')
            value   = 0;
    
        var val = parseInt(value)+parseInt(1);
    
        if (days) {
            var date = new Date();
            date.setTime(date.getTime()+(days*24*60*60*1000));
            var expires = date.toGMTString();
        }
        else
            var expires = "";
    
        AC_FS_Cookie(name,parseInt(val),expires,"/",false);
}

function funclose()
{
        var str=window.name;
        try   
        {    
                if(typeof window.opener!="undefined")
                if((window.opener.closed && str.search(/MENU_/)==-1)|| window.opener.name=="akkenlogout")
                    window.close();
        }
        catch(e)   
        {   
                a=1;    
        }
}

window.onfocus = funclose;

<!--

//Disable right mouse click Script
var message="Function Disabled!";

///////////////////////////////////
function clickIE4()
{
    if (event.button==2)
    {
           alert(message);
            return false;
    }
}

function clickNS4(e)
{
    if (document.layers||document.getElementById&&!document.all)
    {
        if (e.which==2 || e.which==3)
        {
            alert(message);
            return false;
        }
    }
}

if (document.layers)
{
    document.captureEvents(Event.MOUSEDOWN);
    document.onmousedown=clickNS4;
}
else if (document.all&&!document.getElementById)
{
    document.onmousedown=clickIE4;
}
//document.oncontextmenu=new Function("alert(message);return false")
document.oncontextmenu=new Function("return false");
// -->

//disabling all header links
function disablelinks(objtag)
{
        var link=objtag.document.getElementsByTagName("a");
        var linkcount=link.length;
        for(var i =0; i < linkcount; i++)
        {
                var objmenu     = document.getElementsByTagName("a")[i].parentNode.parentNode.parentNode;
                var objcomposemenu = document.getElementsByTagName("a")[i];
                if(objmenu.id=="toplink" || objcomposemenu.id=="sendlink")
                {
                        document.getElementsByTagName("a")[i].disabled = true;
                        document.getElementsByTagName("a")[i].style.cursor='default';
                        document.getElementsByTagName("a")[i].removeAttribute('href');
                }
        }
}

function doSearch()
{
	var daytwo      = document.select.sday.value;
	var monthtwo    = document.select.smonth.value;
	var yeartwo     = document.select.syear.value;
	var totday1;
	var motnhArr    = new Array(0,31,28,31,30,31,30,31,31,30,31,30,31);	//month we get from 1 to 12

	if((yeartwo%4==0) && ((yeartwo%400==0) || (!(yeartwo%100==0))))//leapyear
		motnhArr[2]=29;

	totday1         = motnhArr[monthtwo];

	if(daytwo>totday1)
	{
		alert("Please enter valid date");
		return;
	}
	else
        	document.select.submit();
}

function doValidate()
{
        var totday;
	var field   = document.quickevent.title;
	document.quickevent.title.value = trimAll(field.value);
	var cat     = jQuery("#type").val();
	var str     = jQuery("#title").val();
	var re_date = /^\s*(\d{1,2})\/(\d{1,2})\/(\d{2,4})\s*$/;
	re_date.exec(jQuery("#txtsdate").val());
	var day     = Number(RegExp.$2),mon = Number(RegExp.$1),yr = Number(RegExp.$3);
	var re_time = /^\s*(\d{1,2})\:(\d{1,2})\s*$/
	re_time.exec(jQuery("#lststime").val());
	var shr     = Number(RegExp.$1),smn = Number(RegExp.$2);
	var intval  = Number(jQuery("#interval").val());
	if(intval == 60)
	{
		var ehr = shr+1;
		var emn = smn;	
	}
	else
	{
		if((smn+intval)<60)
		{
			var ehr = shr;
			var emn = smn+intval;
		}
		else
		{
			var ehr = shr+1;
			var emn = (smn+intval)-60;
		}
	}
	smn     = (smn == 0) ? "00" : smn;
	emn     = (emn == 0) ? "00" : emn;

	var cp  = (document.quickevent.colprivate.checked == true) ? "YES" : "NO";

	if(str == "")
	{
		alert("The Event Name Field is Empty. Please enter the Event Name");
		field.focus();
	}

	if(str != "" && validateCaps(field,"Event Name"))
	{
		var len     = str.length;
		if(str.indexOf(' ')==0)
		{
			alert("The Event field  contains space at the beginning.\n\nPlease re-enter your Event.");
			field.focus();
			field.select();
			return;
		}
		else if(str.lastIndexOf(' ')==len-1)
		{
			alert("The Event field  contains space at the end.\n\nPlease re-enter your Event.");
			field.focus();
			field.select();
			return;
		}
		else
		{
			if(cat == "" || cat == null)
			{
				alert("You need to select category");
				return;
			}
			else
			{
				var motnhArr=new Array(0,31,28,31,30,31,30,31,31,30,31,30,31);	//month we get from 1 to 12
				if((yr%4==0) && ((yr%400==0) || (!(yr%100==0))))//leapyear
					motnhArr[2]=29;
				totday=motnhArr[mon];

				if(day>totday)
				{
					alert("Please enter valid date");
					return;
				}
				else
				{
					if((shr > ehr) || (shr == ehr && smn > emn) || (shr == ehr && smn == emn))
					{
						alert("Endtime must be greater than Start time");	
					}
					else
					{
						var data  = "title="+str+"&day="+day+"&mon="+mon+"&year="+yr+"&shr="+shr+"&smn="+smn+"&ehr="+ehr+"&emn="+emn+"&cat="+cat+"&colprivate="+cp;
						DynCls_Ajax_result('saveappointquick.php','rtype',data,"QuickMeetingResp()");
					}
				}
                        }
		}
	}
}

function QuickMeetingResp()
{
	var form    = document.quickevent;
	if(window.location.href.indexOf("iCalendar.php") > -1 )
	{
		var field   = document.quickevent.title;
		var view    = jQuery("#viewtype").val();
		var evntId  = (view == 'month') ? DynCls_Ajx_responseTxt+"_M_0.001" : (view == 'week') ? DynCls_Ajx_responseTxt+"_W_0.001" : DynCls_Ajx_responseTxt;
		var name    = jQuery("#title").val();
		var date    = jQuery("#txtsdate").val();
		var re_time = /^\s*(\d{1,2})\:(\d{1,2})\s*$/
		re_time.exec(jQuery("#lststime").val());
		var shr     = Number(RegExp.$1),smn = Number(RegExp.$2);
		var intval  = Number(jQuery("#interval").val());

		if(intval == 60)
		{
			var ehr = shr+1;
			var emn = smn;	
		}
		else
		{
			if((smn+intval)<60)
			{
				var ehr = shr;
				var emn = smn+intval;
			}
			else
			{
				var ehr = shr+1;
				var emn = (smn+intval)-60;
			}
		}
		smn = (smn == 0) ? "00" : smn;
		emn = (emn == 0) ? "00" : emn;	

		var sampm   = ((shr<12) ? "AM" : "PM");
		var eampm   = ((ehr<12) ? "AM" : "PM");
		var shour   = (shr<12) ? shr : (shr-12);
		var ehour   = (ehr<12) ? ehr : (ehr - 12);
		var strtTime= shour+":"+smn+" "+sampm;
		var endTime = ehour+":"+emn+" "+eampm;
		var start   = getDateFromStrings(date, strtTime);
		var end     = getDateFromStrings(date, endTime);

		if(jQuery('#group_calendar').val() == jQuery('#actual_usr').val())
		{
			var newev = {name: name,startTime: start,endTime: end,allDay: false,group:{groupId: 0,name: 'Personal Calendar'},eventId: evntId};
			ical.addEvent(newev);
		}
	}
	form.title.value = "";
	form.colprivate.checked = false;
}



function addappointmentQuickMore()
{
	document.quickevent.title.value=trimAll(document.quickevent.title.value);
	var cat = jQuery("#type").val();
	var str = jQuery("#title").val();
	var re_date = /^\s*(\d{1,2})\/(\d{1,2})\/(\d{2,4})\s*$/;
	re_date.exec(jQuery("#txtsdate").val());
	var day = Number(RegExp.$2),mon = Number(RegExp.$1),yr = Number(RegExp.$3);
	var re_time = /^\s*(\d{1,2})\:(\d{1,2})\s*$/
	re_time.exec(jQuery("#lststime").val());
	var shr = Number(RegExp.$1),smn = Number(RegExp.$2);
	var intval = Number(jQuery("#interval").val());
	if(intval == 60)
	{
		var ehr = shr+1;
		var emn = smn;	
	}
	else
	{
		if((smn+intval)<60)
		{
			var ehr = shr;
			var emn = smn+intval;
		}
		else
		{
			var ehr = shr+1;
			var emn = (smn+intval)-60;
		}
	}
	smn = (smn == 0) ? "00" : smn;
	emn = (emn == 0) ? "00" : emn;
	var private =  "";

	if(document.quickevent.colprivate.checked)

		private =  "YES";

	addappointmentQuickMore1(mon,day,yr,shr,smn,ehr,emn,str,cat,private);

}

function addappointmentQuickMore1(mon,day,year,shr,smn,ehr,emn,title,cat,private) //for add appointment or edit the appointment
{
	var v_heigth    = 600;
	var v_width     = 1000;
	var top         =(window.screen.availHeight-v_heigth)/2;
	var left        =(window.screen.availWidth-v_width)/2;
	var url         = "/BSOS/Collaboration/Scheduler/quickAddMore.php?title="+title+"&cat="+cat+"&year="+year+"&day="+day+"&mon="+mon+"&shr="+shr+"&smn="+smn+"&ehr="+ehr+"&emn="+emn+"&private="+private;
        
        remote          = window.open(url,"schedule","width="+v_width+"px,height="+v_heigth+"px,statusbar=no,menubar=no,scrollbars=yes,dependent=yes,resizable=yes,left="+left+",top="+top);
        try{
            remote.focus();
	}
	catch(e){}
}

function doOpenForDefaultEditor()
{
	var v_heigth    = 420;
	var v_width     = 780;
	var top         =(window.screen.availHeight-v_heigth)/2;
	var left        =(window.screen.availWidth-v_width)/2;
        remote          = window.open("/BSOS/Collaboration/Email/editoroptions.php","Editor","width="+v_width+"px,height="+v_heigth+"px,statusbar=no,menubar=no,scrollbars=yes,resizable=no,hotkeys=no"+",left="+left+",top="+top);
        remote.focus();
}

function doCompose()
{
	var v_heigth    = 600;
	var v_width     = 1000;
	remoter         = window.open("/BSOS/Collaboration/Email/Compose.php","con","width="+v_width+"px,height="+v_heigth+"px,statusbar=no,menubar=no,scrollbars=yes,resizable=no,left=100,top=60,dependent=yes");
	remoter.focus();
}

function changebackg()
{
        /* var foldlist    = document.getElementById('chngbgclr');
        if(typeof foldlist == 'object')
                foldlist.style.backgroundColor  = '#FFFFFF'; */
}

function openSetup()
{
        var v_heigth    = 600;
        var v_width     = 1000;
        remoter         = window.open("/BSOS/Collaboration/Email/emailsetup.php","con"," width=900,height=300,statusbar=no,menubar=no,scrollbars=yes,resizable=no,left=100,top=60,dependent=yes");
        remoter.focus();
}

function openRules()
{
        var v_heigth    = 600;
        var v_width     = 1000;
        remoter         = window.open("/BSOS/Collaboration/Email/filter.php","con","width=900,height=350,statusbar=no,menubar=no,scrollbars=yes,resizable=no,left=100,top=60,dependent=yes");
        remoter.focus();
}

//fot trimming the string
String.prototype.ltrim=function()
{
	return this.replace(/^\s+/,"");
}

String.prototype.rtrim=function()
{
	return this.replace(/\s+$/,"");
}

Array.prototype.inArray = function(search_term) {

        var i = this.length;
        if (i > 0) {
            do {
		if (this[i] === search_term) {
                        return true;
		}
            } while (i--);
        }
        return false;
}

//Common Email Validation Funcation Added By Sandeep Kumar Ganacahry
function checkemail(email,field)
{
	var str     = email.value
	var filter  = emailRegExp;
	if(str!="")
	{
		if (filter.test(str))
			return true;
		else
		{
			alert("Enter a valid Email Address in " + field + " field");
			email.focus();
			return false;
		}
	}
	return true;
}

function validateCaps(field,name)
{
	var str     = field.value;
	for (var i = 0; i < str.length; i++)
	{
		var ch = str.substring(i, i + 1);
		if ( ch == "^" || ch == "|" )
		{
			alert(name + " does not accept | and ^ characters. Please re-enter " + name + ".");
			field.focus();
			field.select();
   			return false;
		}
	}
	return true;
}

function doSubmit()
{
        document.quickevent.title.value=trimAll(document.quickevent.title.value);
	var str=document.quickevent.title.value;
	var field=document.quickevent.title;
	var dayone=document.quickevent.day.value;
	var monthone=document.quickevent.month.value;
	var yearone=document.quickevent.year.value;
	var totday;

	if(str=="")
	{
		alert("The Event Name Field is Empty. Please enter the Event Name");
		field.focus();
		return false;
	}
	if(str!="" && validateCaps(field,"Event Name"))
	{
		var len     = str.length;
		if(str.indexOf(' ')==0)
		{
			alert("The Event field  contains space at the beginning.\n\nPlease re-enter your Event.");
			field.focus();
			field.select();
		}
		if(str.lastIndexOf(' ')==len-1)
		{
			alert("The Event field  contains space at the end.\n\nPlease re-enter your Event.");
			field.focus();
			field.select();
		}

                var motnhArr    = new Array(0,31,28,31,30,31,30,31,31,30,31,30,31);	//month we get from 1 to 12
                if((yearone%4==0) && ((yearone%400==0) || (!(yearone%100==0))))//leapyear
                        motnhArr[2] = 29;

                totday  = motnhArr[monthone];
                if(dayone>totday)
                {
                        alert("Please enter valid date");
                }
                document.quickevent.submit();
        }
        else
                return false;
}

// Displaying number in number format.
function bill_number_format( val )
{
	var oNumberObject   = new Number(val);	
	return oNumberObject.toFixed(2);
}
function setCookies(c_name,value,expiredays) {
        var exdate=new Date()
        exdate.setDate(exdate.getDate()+expiredays)
        var expires = ((expiredays==null) ? "" : exdate);
        AC_FS_Cookie(c_name,value,expires,"/",false);
} 

function getCookies(c_name) {
        if (document.cookie.length>0) {
            c_start=document.cookie.indexOf(c_name + "=")
			
            if (c_start!=-1) { 
                c_start=c_start + c_name.length+1 
                c_end=document.cookie.indexOf(";",c_start)
                if (c_end==-1) c_end=document.cookie.length
                    return unescape(document.cookie.substring(c_start,c_end))
            } 
        }
        return null
}

onload=function(){
	
	try{
		var sourceID = (document.getElementById("sourcid") != null) ? document.getElementById("sourcid").value:"";
		var rtype = (document.getElementById("sourctype") != null) ? document.getElementById("sourctype").value:"";
		var cookiecheck = getCookies("notesAssChk");
		var module = (document.getElementById("frm_module") != null) ? document.getElementById("frm_module").value:"";

		if(document.getElementById("notesAssChk") != null)
            document.getElementById("notesAssChk").checked = getCookies("notesAssChk")==1? true : false;

		if(cookiecheck==1)
			{	
			var url         = "/BSOS/Include/getSummaryNotes.php?module="+module;	
			var assCheck    = true;
			if(rtype == "candidate"){
				var NotesDisBoxName = "dispNotesNew";
				var NotesHideBoxName = "dispNotes";
			} else {
				var NotesDisBoxName = "allNotesNew";
				var NotesHideBoxName = "allNotes";
			}		
			if(rtype == "company"){compHierarchyFlag = false;}else{compHierarchyFlag = true;}
			displayAssocSummaryNotes(sourceID, rtype, NotesDisBoxName, NotesHideBoxName, module);
			//var content     = "noteAjaxFlag=true&sourceID="+sourceID+"&showAssociate="+assCheck+"&rtype="+rtype+"&compHierarchyFlag="+compHierarchyFlag;
			//var funname     = "returnSummaryNotesDisplay('"+NotesDisBoxName+"', '"+NotesHideBoxName+"')";
			document.getElementById("dynsndiv").style.display   = "none";
			//DynCls_Ajax_result(url,rtype,content,funname);
			}
	}catch(e){}

}
function displayAssocSummaryNotes(sourceID, rtype, NotesDisBoxName, NotesHideBoxName, module)
{	
	var url         = "/BSOS/Include/getSummaryNotes.php?module="+module;	
	var assCheck    = compHierarchyFlag = false;
	
	try{
	setCookies("notesAssChk", document.getElementById("notesAssChk").checked? 1 : 0, 100);
	if(document.getElementById("notesAssChk").checked == true)
        {
		assCheck    = true;
	}

	
	try{
		if(document.getElementById('ChkCompanyHierarchy').checked == true){
			compHierarchyFlag = true;
		}
	}catch(e){}
	
	var content     = "noteAjaxFlag=true&sourceID="+sourceID+"&showAssociate="+assCheck+"&rtype="+rtype+"&compHierarchyFlag="+compHierarchyFlag;
	var funname     = "returnSummaryNotesDisplay('"+NotesDisBoxName+"', '"+NotesHideBoxName+"','"+module+"')";
	document.getElementById("pmsg").innerHTML   = "<span style='font-size:12px; font-weight:bold; padding-left:50px;'>Processing, please wait...</span><br /><br />";
	
	DynCls_Ajax_result(url,rtype,content,funname);
	}catch(e){}
}

function displayPageSummaryNotes(sourceID, rtype, NotesDisBoxName, NotesHideBoxName, plimit, module)
{	
	var url         = "/BSOS/Include/getSummaryNotes.php?module="+module;	
	var assCheck    = compHierarchyFlag = false;
	if(document.getElementById("notesAssChk").checked == true)
        {
		assCheck    = true;
	} else {assCheck    = false;}
	try{
		if(document.getElementById('ChkCompanyHierarchy').checked == true){
			compHierarchyFlag = true;
		}
	}catch(e){}
	
	var content     = "noteAjaxFlag=true&sourceID="+sourceID+"&showAssociate="+assCheck+"&rtype="+rtype+"&compHierarchyFlag="+compHierarchyFlag+"&startlimit="+plimit;
    var funname     = "returnSummaryNotesDisplay('"+NotesDisBoxName+"', '"+NotesHideBoxName+"')";
	document.getElementById("pmsg").innerHTML   = "<span style='font-size:12px; font-weight:bold; padding-left:50px;'>Processing, please wait...</span><br /><br />";
	DynCls_Ajax_result(url,rtype,content,funname);
}



function returnSummaryNotesDisplay(NotesDisBoxName, NotesHideBoxName)
{
	DynCls_Ajx_responseTxt=DynCls_Ajx_responseTxt.replace(new RegExp('&lt;br&nbsp;/&gt;',"g"),'<br>');	
	CurrData = DynCls_Ajx_responseTxt;	
	if(trim(CurrData)!="|^AKKEN^|")
	{
		CurrData=CurrData.replace(new RegExp('&lt;br>',"g"),'<br>');
		CurrData=CurrData.replace(new RegExp('&lt;br&nbsp;/&gt;',"g"),'<br>');
		CurrData = CurrData.replace(/\\/g, ''); 
		try{
			document.getElementById(NotesHideBoxName).innerHTML = '';
			document.getElementById("pmsg").innerHTML = '';
		}
		catch(e){}		

		document.getElementById(NotesDisBoxName).innerHTML=unescape(CurrData);
		document.getElementById(NotesDisBoxName).style.textAlign = 'left';
		highlighttext(NotesDisBoxName);
	}
	else
	{
		return;	
	}
}

function openAssocWindow()
{
	var result = window.open("/include/search_select_assoc.php","Assoc","width=900px,height=550px,statusbar=no,menubar=no,scrollbars=yes,dependent=yes,resizable=yes");
	result.focus();
}
/*
Used to open a popup window for merging.
type  -- which Screen(contacts, candidates, Joborders, Companies)
frm   -- Location(0->CRM, 1->Admin)[To Check the constraints]
id    -- Id of the record
*/
function openMergeWindow(type, frm)
{
	var cnt = 0;
	var e   = document.getElementsByName('auids[]');

	for (var i=0; i < e.length; i++)
	{
		if (e[i].name == "auids[]" && e[i].checked)
		{
			cnt++;
		}
	}
	if(cnt < 2)
	{
		alert("Merging requires a minimum of 2 records. Please select the records that need to be merged.");
		return;
	}
	if(type=='Consultants')
	{
		doa2mCheck();
	}
        else
	{
		var result = window.open("/BSOS/Include/merge.php?type="+type+"&utype="+frm,"Merge","width=1250px,height=650px,statusbar=no,menubar=no,scrollbars=yes,dependent=yes,resizable=no");
		result.focus();
	}	
}

function doa2mCheck()
{
	valCands        = valSelected1();
	var checktype   = "CRM_CANDS";	
	DynCls_Ajax_result("/BSOS/HRM/Consultant_Leads/updatecrmrecords.php?valCands="+valCands,checktype,"rtype="+checktype,"geta2mAjx_responseTxt()");
}

function geta2mAjx_responseTxt()
{
	var Ajx_responseTxt = DynCls_Ajx_responseTxt;
	var callbackStr     = /akken/;
	var callbackStrFlag = callbackStr.test(Ajx_responseTxt);
	if(!callbackStrFlag)
	{
		var reThreeNums = /[1-9]/;
		var upd_cands   = reThreeNums.test(Ajx_responseTxt);
		if(upd_cands)
		{
			alert('You have selected Applicant records that are in different stages. Please select applicants of pipeline stage to use the "Merge" feature.');
			return false;
		}
		else
		{
			var result = window.open("/BSOS/Include/merge.php?type=Consultants&utype=0","Merge","width=1250px,height=650px,statusbar=no,menubar=no,scrollbars=yes,dependent=yes,resizable=no");
			result.focus();
		}			
	}
}

function openNoteResourceWindow(type, id, status, module)
{
	var result  = "";
	var v_heigth    = 600;
	var v_width     = 1050;

	//Get module for hiding the crm notes
	if(module == 'Admin_Contacts' || module == 'Admin_Companies' || module == 'Admin_Candidates' || module == 'Admin_JobOrders'){
		var flag = true;
	}

	if(type == 'oppr')
	{ 
		if(flag == true){module = 'Admin_Contacts';}
		result  = '/BSOS/Marketing/Lead_Mngmt/reviewContact.php?addr='+id+'&contasno='+id+'&sesvar=new&module='+module;
	}
	else if(type == 'com')
	{
		if(flag == true){module = 'Admin_Companies';}
		result = '/BSOS/Marketing/Companies/viewcompanySummary.php?addr='+id+'&module='+module;		
	}
	else if(type == 'req')
	{
		if(flag == true){module = 'Admin_JobOrders';}
		v_width     = 1250;	
		result = "/BSOS/Sales/Req_Mngmt/redirectjob.php?addr="+id+'&module='+module;		
	}
	else if(type == 'cand')
	{
		if(flag == true){module = 'Admin_Candidates';}
		var inact="cand"+id;
		v_width     = 1150;	
		if(status == 'CANDACTIVE')
			result  = "/BSOS/Marketing/Candidates/getconreg.php?resstat=review&dest=0&cno="+id+'&module='+module;
		else
			result  = "/BSOS/Sales/Req_Mngmt/review.php?cno="+id+"&dest=0&candstat=INACTIVE&module="+module;		
	}
        
	if(result != "")
	{
		var top1        = (window.screen.availHeight-v_heigth)/2;
		var left1       = (window.screen.availWidth-v_width)/2;
		remote_resource = window.open(result,"","width="+v_width+"px,height="+v_heigth+"px,resizable=yes,statusbar=no,menubar=no,scrollbars=yes,left="+left1+"px,top="+top1+"px,dependent=yes");
		remote_resource.focus();
	}
}

// Validating crm groups drop list when public/share group selected in case of private canidate/conact record.
function validateCRMGroupType(cgname)
{
	try
	{
		if(crm_groups == 'Y'){
			var k = 0;
			var groupFlag = true;
			tempGroupIndex = new Array();
			if(document.getElementById("candOrcontactAccessTo").value == 'Private'){
				$('#'+cgname+' :selected').each(function(i, selected){
					var groupLable = $(selected).attr("label");
					if(groupLable == 'Public' || groupLable == 'Share'){
						groupFlag = false;
						tempGroupIndex[k++] = $(selected).attr("index");
					}
				});
			}
			if(!groupFlag)
                        {
				if(!confirm("Record marked as private would not be visible in selected group(s) to other users")){
					return false;
				}
				else
					return true;
			}
			else
				return true;
		}else
			return true;
	} catch(e){}
}

//Function to populate the burden items details of selected pay burden type for contacts/companites and customers pages
function getLocPayBurdenString(obj)
{        
        var burdenTypeId    =  obj.value;
	var BIStr           = "";
	if(burdenTypeId != "" && burdenTypeId != "0")
	{
		var burdenTypeStrArray  =   burdenTypeId.split("|");
		BIStr                   = burdenTypeStrArray[1];
		if(BIStr == "")
		{
			BIStr           = "No Burden Item's exists for the selected Burden Type.";
		}		
	}
        document.getElementById('burdenItemsStr').innerHTML     = BIStr;
}

//Function to populate the burden items details of selected bill burden type for contacts/companites and customers pages
function getLocBillBurdenString(obj)
{        
        var burdenTypeId    = obj.value;
	var BIStr           = "";
	if(burdenTypeId != "" && burdenTypeId != "0")
	{
		var burdenTypeStrArray  = burdenTypeId.split("|");
		BIStr = burdenTypeStrArray[1];
		if(BIStr == "")
		{
			BIStr   = "No Burden Item's exists for the selected Burden Type.";
		}		
	}
        document.getElementById('billburdenItemsStr').innerHTML = BIStr;
}

function showAssignment(TSE,hrcon_sno,hrcon_status,emp_sno,module) //TSE => THERAPY_SOURCE_ENABLED
{
    
	var astatus = "";
	if(hrcon_status == "active") {
		astatus = "approved";
	} else if(hrcon_status == "cancel") {
		astatus = "cancelled";
	} else {
		astatus = hrcon_status;
	}
	
	rec=hrcon_sno+"|15|"+astatus+"|"+emp_sno;
	
	var v_width  = window.screen.availWidth * 0.85;
	var v_heigth = 700;
	var top1=(window.screen.availHeight-v_heigth)/2;
	var left1=(window.screen.availWidth-v_width)/2;
	result="/BSOS/Accounting/Assignment/getnewconreg.php?rec="+rec;
	var op=window.open(result,"comp","width="+v_width+"px,height="+v_heigth+"px,resizable=no,statusbar=no,menubar=no,scrollbars=yes,left="+left1+"px,top="+top1+"px,dependent=yes");
}

function showCustomer(x,module)
{
    var v_width  = window.screen.availWidth * 0.85;
	var v_heigth = 700;
	var top1=(window.screen.availHeight-v_heigth)/2;
	var left1=(window.screen.availWidth-v_width)/2;
	result="/BSOS/Accounting/clients/addacccompany.php?addr="+x+"&srnum="+x+"&newComp=yes&crm_select=no&sesn=no&edit_acc=yes";
    var op=window.open(result,"comp","width="+v_width+"px,height="+v_heigth+"px,resizable=no,statusbar=no,menubar=no,scrollbars=yes,left="+left1+"px,top="+top1+"px,dependent=yes");
}

function showEmp(x,module,aca_user_access)
{	
	var result = x;
	var v_width  = window.screen.availWidth * 0.85;
	var v_heigth = 620;
	remote=window.open("/BSOS/HRM/Employee_Mngmt/getnewconreg.php?command=emphire&addr=new&rec="+result,"HRM_Employee_Mngmt","width="+v_width+"px,height="+v_heigth+"px,statusbar=no,menubar=no,scrollbars=yes,left=30,top=30,dependent=yes");
	remote.focus();
}


