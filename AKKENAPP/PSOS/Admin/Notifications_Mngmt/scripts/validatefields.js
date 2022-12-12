/*	
		Created Date : Aug 22, 2014.
		Created By   : Vipul
		Purpose      : To Update notifications_settings table for credential module.	
	*/ 
	
	
/* Function to enable/disable module specific settings block */
function toggleDisplay(field) {
	
	fieldname = field.id;
	disable_block_id = fieldname+"-block";

	if (field.checked) {			
		document.getElementById(fieldname).value = "1";		
		//This will enable all the children of the div		
		$("#credentials-block").find("input,button,textarea,span").removeAttr("disabled");	
		$("#people_dropdown").removeAttr("disabled");	
		$("#list_of_emails1").removeAttr("disabled");		

		if($('#people').is(':checked')){
			$("#people_dropdown").attr("style","display:in-line");
			$("#addremovelink").attr("style","display:in-line");			
		}else{
			$("#people_dropdown").attr("style","display:none");	
			$("#addremovelink").attr("style","display:none");	
		}
		if($("#notifymode_email").is(':checked')){
		   $("#emailtemplink").attr("style","display:in-line");
		}

		//new added
		$('#cred_EmailSetupSno').removeAttr("disabled");
	}
	else{	
		var x = confirm("Disabling, Credential Management Notification will stop sending notifications. Click on OK to continue or Click on Cancel to return");
	if(x){
			document.getElementById(fieldname).value = "0";		
					
			//This will disable all the children of the div
			$("#notifymode input[type=checkbox]").each(function () {
				$(this).attr("checked", false);
			});
			$("#subscribersmode input[type=checkbox]").each(function () {
				$(this).attr("checked", false);
			});
			$("#frequencymode input[type=checkbox]").each(function () {
				$(this).attr("checked", false);
			});
			$("#credentialsmode input[type=checkbox]").each(function () {
				$(this).attr("checked", false);
			});
			$("#people_dropdown").hide();
			
			$("#reminder").attr("checked", false);
			$("#send_reminder").hide();
			$("#selectedids").val('');
			$("#send_reminder_val").val(0);
			$("#reminder_body").val('');
			$("#reminder_sub").val('');
			
			$("#credentials-block").find("input,button,textarea,span").attr("disabled", "disabled");		
			$("#addremovelink").attr("style","display:none");
		
			//new added
			$('#cred_EmailSetupSno').prop('disabled', 'disabled');

		}else{
			field.checked = true;
		}
	}
}
// ACA Compliance Code for Checkbox
function toggleACACompliance(field) {
	
	fieldname = field.id;
	disable_block_id = fieldname+"-block";

	if (field.checked) {			
		document.getElementById(fieldname).value = "1";		
		//This will enable all the children of the div		
		$("#acacompliance-block").find("input,button,textarea,span").removeAttr("disabled");	
		$("#people_dropdown").removeAttr("disabled");	
		$("#list_of_emails1").removeAttr("disabled");	
		if($('#people').is(':checked')){
			$("#people_dropdown").attr("style","display:in-line");
			$("#addremovelink").attr("style","display:in-line");			
		}else{
			$("#people_dropdown").attr("style","display:none");	
			$("#addremovelink").attr("style","display:none");	
		}		
	}
	else{	
		var x = confirm("Disabling, ACA Compliance Notification will stop sending notifications. Click on OK to continue or Click on Cancel to return");
	if(x){
			document.getElementById(fieldname).value = "0";		
					
			//This will disable all the children of the div
			$("#acacompliance-block").find("input,button,textarea,span").attr("disabled", "disabled");		
			$("#addremovelink").attr("style","display:none");	
		}else{
			field.checked = true;
		}
	}
}
function doCredUpdate()
{
	window.onbeforeunload = null;
	var sform_dirty = $("form").serialize();
	if (sform_clean == sform_dirty) 
	{
		alert("There are no Changes to update");
	}
	else
	{
		form				= document.nform;
		var notify_to_error_flag 	= false; 
		var candemp_error_flag 		= false;
		
		// Credentials Associated With Option Check
		var cred_cand 	= $('#check_cand').is(':checked');
		var cred_emp 	= $('#check_emp').is(':checked');
		var cred 	= $('#credentials').is(':checked');
		var chk1		= true;
		if(cred){
			if(!cred_cand && !cred_emp){
				candemp_error_flag = true;
			}
			
			var c = $('#people').is(':checked');			
			if(c){
				var val = $("#selectedids").val();
				
				if(val==''){
					notify_to_error_flag = true;
				}
			}
			
			if (candemp_error_flag || notify_to_error_flag) {
				if (candemp_error_flag) {
					alert("Please select credentials(Candidates/Employees) to be considered for sending notifications");
				}
				if (notify_to_error_flag) {
					alert("There are no Employees(Internal Direct) selected");
				}
				chk1 = false;
				return;
			}else{			
				chk1 = true;
			}
		}
		if(chk1){
			form.submit();			
		}		
	}
}

function doAobUpdate()
{
	window.onbeforeunload = null;
	var sform_dirty = $("form").serialize();
	if (sform_clean == sform_dirty) 
	{
		alert("There are no Changes to update");
	} 
	else 
	{
		form		= document.nform;
		var aob_notify_to_error_flag = false;
		var aob_not 	= $('#aob_notification').is(':checked');
		var chk2		= true;
		if(aob_not){
			var ap = $('#aob_people').is(':checked');
			if(ap){
				var aob_val = $("#aob_selectedids").val();				
				if(aob_val==''){
					aob_notify_to_error_flag = true;
				}
			}
			if(aob_notify_to_error_flag) {
				alert("There are no Employees(Internal Direct) selected for AOB Notification");
				chk1 = false;
				return;
			}else{			
				chk2 = true;
			}			
		}
		if(chk2){
			form.submit();			
		}
	}
}

function doPlacementUpdate()
{
	window.onbeforeunload = null;
	var sform_dirty = $("form").serialize();
	if (sform_clean == sform_dirty) 
	{
		alert("There are no Changes to update");
	} 
	else 
	{
		form=document.nform;
		var placement_notify_to_error_flag = false;
		var placement_not 	= $('#placement_notification').is(':checked');
		var chk3		= true;
		if(placement_not){
			var cust 	= $('#placement_custemail').is(':checked');
			var emp 	= $('#placement_primaryemail').is(':checked');
			var sms 	= $('#placement_sms').is(':checked');
			if(cust==false && emp==false && emp==sms){
				placement_notify_to_error_flag = true;
			}else if(cust==true){
				var reportto 	= document.getElementById("placement_reportto");
				var contact 	= document.getElementById("placement_contact");			
				if(reportto.checked==false && contact.checked==false){
					placement_notify_to_error_flag = true;
				}else{
					placement_notify_to_error_flag = false;
				}
			}else if(emp==true){
				var pemail = document.getElementById("placement_primaryemail");
				if(pemail.checked==false){
					placement_notify_to_error_flag = true;
				}else{
					placement_notify_to_error_flag = false;
				}
			}else if(sms==true){
				placement_notify_to_error_flag = false;				
			}
			else{
				chk3 = true;
			}			
		}
		if(chk3){
			form.submit();			
		}		
	}
}

//Update the form fields 
function doUpdate()
{
	window.onbeforeunload = null;
	var sform_dirty = $("form").serialize();

	if (sform_clean == sform_dirty) 
	{
		alert("There are no Changes to update");
	} 
	else 
	{
		form=document.nform;
		var valid = false ;
		var notify_to_error_flag = false; 
		var candemp_error_flag = false;
		var aob_notify_to_error_flag = false;
		var placement_notify_to_error_flag = false;
		
		// Credentials Associated With Option Check
		var cred_cand 		= $('#check_cand').is(':checked');
		var cred_emp 		= $('#check_emp').is(':checked');
		var cred 		= $('#credentials').is(':checked');
		var aob_not 		= $('#aob_notification').is(':checked');
		var placement_not 	= $('#placement_notification').is(':checked');
		var aca_not 		= $('#aca_notification').is(':checked');
		var chk1		= true;
		var chk2		= true;
		var chk3		= true;
		var chk4		= true;

		if(cred){
			if(!cred_cand && !cred_emp){
				candemp_error_flag = true;
			}
			
			var c = $('#people').is(':checked');			
			if(c){
				var val = $("#selectedids").val();
				
				if(val==''){
					notify_to_error_flag = true;
				}
			}
			
			if (candemp_error_flag || notify_to_error_flag) {
				if (candemp_error_flag) {
					alert("Please select credentials(Candidates/Employees) to be considered for sending notifications");
				}
				if (notify_to_error_flag) {
					alert("There are no Employees(Internal Direct) selected");
				}
				chk1 = false;
				return;
			}else{			
				chk1 = true;
			}
		}
		if(aob_not){
			var ap = $('#aob_people').is(':checked');
			if(ap){
				var aob_val = $("#aob_selectedids").val();				
				if(aob_val==''){
					aob_notify_to_error_flag = true;
				}
			}
			if(aob_notify_to_error_flag) {
				alert("There are no Employees(Internal Direct) selected for AOB Notification");
				chk1 = false;
				return;
			}else{			
				chk2 = true;
			}			
		}
		if(placement_not){
			var cust 	= $('#placement_custemail').is(':checked');
			var emp 	= $('#placement_primaryemail').is(':checked');
			var sms 	= $('#placement_sms').is(':checked');
			if(cust==false && emp==false && emp==sms){
				placement_notify_to_error_flag = true;
			}else if(cust==true){
				var reportto 	= document.getElementById("placement_reportto");
				var contact 	= document.getElementById("placement_contact");			
				if(reportto.checked==false && contact.checked==false){
					placement_notify_to_error_flag = true;
				}else{
					placement_notify_to_error_flag = false;
				}
			}else if(emp==true){
				var pemail = document.getElementById("placement_primaryemail");
				if(pemail.checked==false){
					placement_notify_to_error_flag = true;
				}else{
					placement_notify_to_error_flag = false;
				}
			}else if(sms==true){
				placement_notify_to_error_flag = false;				
			}
			else{
				chk3 = true;
			}			
		}
		if(aca_not)
		{
			chk4	= true;
		}
		if(chk1 && chk2 && chk3 && chk4){
			form.submit();			
		}		
 	}	
}

/* Function to enable/disable AOB module specific settings block */
function aobtoggleDisplay(field) {
	fieldname = field.id;
	disable_block_id = fieldname+"-block";
	if (field.checked) {			
		document.getElementById(fieldname).value = "1";		
		//This will enable all the children of the div		
		$("#aob-block").find("input,button,textarea,span").removeAttr("disabled");	
		$("#aob_people_dropdown").removeAttr("disabled");	
		$("#list_of_emails1").removeAttr("disabled");	
		if($('#aob_people').is(':checked')){
			$("#aob_people_dropdown").attr("style","display:in-line");
			$("#aob_addremovelink").attr("style","display:in-line");			
		}else{
			$("#aob_people_dropdown").attr("style","display:none");	
			$("#aob_addremovelink").attr("style","display:none");	
		}	

		//new added
		$('#aob_EmailSetupSno').removeAttr("disabled");
	}
	else{
		var x = confirm("Disabling, AOB Management Notification will stop sending notifications. Click on OK to continue or Click on Cancel to return");
		if(x){
			document.getElementById(fieldname).value = "0";				
			//This will disable all the children of the div
			$("#notifymode input[type=checkbox]").each(function () {
                $(this).attr("checked", false);
            });
			$("#aob_people").attr("checked", false);
			$("#aob_selectednames").attr("style","display:none");
			$("#aob_selectedids").val('');
			$("#aob-block").find("input,button,textarea,span").attr("disabled", "disabled");		
			$("#aob_addremovelink").attr("style","display:none");
			$("#aob_people_dropdown").attr("style","display:none");	

			//new added
			$('#aob_EmailSetupSno').prop('disabled', 'disabled');
		}else{
			field.checked = true;
		}
	}
}

//function to auto check customer related fields
function checkCustomerlist(){
	var cust = document.getElementById("placement_custemail");
	var j = document.getElementById("placement_reportto");
	var c = document.getElementById("placement_contact");
	if(cust.checked==true){
		$("#custrow").show();
		j.disabled=false;
		j.checked=true;
		c.disabled=false;
		c.checked=true;		
	}
	else{
		$("#custrow").hide();
		j.checked=false;
		j.disabled=true;
		c.checked=false;
		c.disabled=true;
	}
}

//function to auto check employee related field
function checkEmployeelist(){
	var emp = document.getElementById("placement_primaryemail");
	if(emp.checked==true){
		$("#emprow").show();
		$("#emprow").css('word-wrap','break-word');
		$("#emprow").css('display','table-row');
		emp.checked=true;
		emp.disabled=false;
	}
	else{
		$("#emprow").hide();
		document.getElementById("placement_primaryemail").removeAttribute("checked");
		emp.disabled=true;
	}
}

/* Function to enable/disable Placement module specific settings block */
function placementtoggleDisplay(field) {
	fieldname = field.id;
	disable_block_id = fieldname+"-block";
	
	if (field.checked) {
		document.getElementById(fieldname).value = "1";
		//This will enable all the children of the div
		$("#placement_custemail").removeAttr("disabled");
		$("#placement_primaryemail").removeAttr("disabled");
		$("#placement_sms").removeAttr("disabled");
		//enableTempLink('c');
		//enableTempLink('e');
		//enableTempLink();
		//$("#notification_templates").show();

		//new added
		$("#placement_EmailSetupSno").removeAttr("disabled");
	}
	else{
		var x = confirm("Disabling, Placement Management Notification will stop sending notifications. Click on OK to continue or Click on Cancel to return");
		if(x){
			document.getElementById(fieldname).value = "0";
			//This will unchecked all the child of the div
			$(".placement_block_fields").each(function () {
                $(this).attr("checked", false);
            });
			$("#emprow").hide();
			$("#custrow").hide();			
			//This will disable all the child of the div
			$("#placements-block").find("input,button,textarea,span").attr("disabled", "disabled");
			//enableTempLink('c');
			//enableTempLink('e');
			//enableTempLink();
			//$("#notification_templates").hide();

			//new added
			$('#placement_EmailSetupSno').prop('disabled', 'disabled');
		}else{
			field.checked = true;
		}
	}
}

function checkCustemail(){
	var cust 	= document.getElementById("placement_custemail");
	var j 		= document.getElementById("placement_reportto");
	var c 		= document.getElementById("placement_contact");
	if(j.checked==false && c.checked==false){
		cust.checked=false;
	}else{
		cust.checked=true;
	}
}

function checkEmpemail(){
	var emp 	= document.getElementById("placement_empemail");
	var p 		= document.getElementById("placement_primaryemail");
	if(p.checked==false){
		emp.checked=false;
	}else{
		emp.checked=true;
	}
}

function enableTempLink(){	
	var p = document.getElementById("placement_notification");

	
	if(p.checked==true) 
	{
		$("#notification_templates").show();
	}
	else if(p.checked==false) 
	{
		$("#notification_templates").hide();
	}
}

/*New Functions added for Mass Communication*/
function addEditTmpl(id,module) 
{
	var v_width  = 950;
	var v_heigth = 650;
	var top1=(window.screen.availHeight-v_heigth)/2;
	var left1=(window.screen.availWidth-v_width)/2;

	var parms = "width="+v_width+"px,";
	parms += "height="+v_heigth+"px,";
	parms += "left="+left1+"px,";
	parms += "top="+top1+"px,";
	parms += "statusbar=no,";
	parms += "menubar=no,";
	parms += "scrollbars=yes,";
	parms += "dependent=yes,";
	parms += "resizable=yes";

	if(module == 'Customer') 
	{
		top.remattachfiles_cand=window.open("cust_email_tmpl.php?ecamptype="+id,"newView",parms);	
	}
	else if(module == 'Employee') 
	{
		top.remattachfiles_cand=window.open("emp_email_tmpl.php?ecamptype="+id,"newView",parms);	
	}
	else if(module == 'Approve') 
	{
		top.remattachfiles_cand=window.open("timesheet_email_tmpl.php?ecamptype="+id+"&module="+module,"newView",parms);	
	}
	else if(module == 'Rejected') 
	{
		top.remattachfiles_cand=window.open("timesheet_email_tmpl.php?ecamptype="+id+"&module="+module,"newView",parms);	
	}
	else if(module == 'Submitted') 
	{
		top.remattachfiles_cand=window.open("timesheet_email_tmpl.php?ecamptype="+id+"&module="+module,"newView",parms);	
	}
	else if(module == 'ACA') 
	{
		top.remattachfiles_cand=window.open("aca_email_tmpl.php?ecamptype="+id,"newView",parms);
	}
	else if(module == 'ClockIn' || module == 'ClockOut') 
	{
		top.remattachfiles_cand=window.open("cico_email_tmpl.php?ecamptype="+id+"&module="+module,"newView",parms);
	}
	top.remattachfiles_cand.focus();
}

function removeTmpl(id) 
{
	if(id!='')
	{
		$.ajax({
		 	type: 'POST',
		 	async: false,
		 	url: 'tmpl_function.php', 
		 	data: 'id='+id+'&mode=delete',
		 	success: function(result)
		 	{
		 		if(result!='')
				{
					alert("Template has been deleted successfully");
					$("tr#"+id).remove();
				}
			}
		});
	}
}

function previewTmpl(id,module) 
{
	var v_width  = 950;
	var v_heigth = 650;
	var top1=(window.screen.availHeight-v_heigth)/2;
	var left1=(window.screen.availWidth-v_width)/2;

	var parms = "width="+v_width+"px,";
	parms += "height="+v_heigth+"px,";
	parms += "left="+left1+"px,";
	parms += "top="+top1+"px,";
	parms += "statusbar=no,";
	parms += "menubar=no,";
	parms += "scrollbars=yes,";
	parms += "dependent=yes,";
	parms += "resizable=yes";

	top.remattachfiles_cand=window.open("showPreviewTmpl.php?tmplId="+id+"&module="+module,"newView",parms);	
	top.remattachfiles_cand.focus();
}

function editPreviewTmpl(id,module) 
{
	var v_width  = 800;
	var v_heigth = 500;
	var top1=(window.screen.availHeight-v_heigth)/2;
	var left1=(window.screen.availWidth-v_width)/2;

	var parms = "width="+v_width+"px,";
	parms += "height="+v_heigth+"px,";
	parms += "left="+left1+"px,";
	parms += "top="+top1+"px,";
	parms += "statusbar=no,";
	parms += "menubar=no,";
	parms += "scrollbars=yes,";
	parms += "dependent=yes,";
	parms += "resizable=yes";

	if(module == 'Customer') 
	{
		window.location.href = 'cust_email_tmpl.php?ecamptype='+id;		
	}
	else if(module == 'Employee') 
	{
		window.location.href = 'emp_email_tmpl.php?ecamptype='+id;		
	}
	else if(module == 'Approve') 
	{
		window.location.href = "timesheet_email_tmpl.php?ecamptype="+id+"&module="+module;	
	}
	else if(module == 'Rejected') 
	{
		window.location.href = "timesheet_email_tmpl.php?ecamptype="+id+"&module="+module;	
	}
	else if(module == 'Submitted') 
	{
		window.location.href = "timesheet_email_tmpl.php?ecamptype="+id+"&module="+module;	
	}
	else if(module == 'ACA') 
	{
		top.remattachfiles_cand=window.open("aca_email_tmpl.php?ecamptype="+id,"newView",parms);
	}
	else if(module == 'ClockIn' || module == 'ClockOut') 
	{
		top.remattachfiles_cand=window.open("cico_email_tmpl.php?ecamptype="+id+"&module="+module,"newView",parms);
	}
}

function defaultTmplSave(id) 
{
	var v_width  = 950;
	var v_heigth = 230;
	var top1=(window.screen.availHeight-v_heigth)/2;
	var left1=(window.screen.availWidth-v_width)/2;
	var templ_win=window.open("saveTemplate.php?ecamptype="+id,'',"width="+v_width+"px,height="+v_heigth+"px,left="+left1+"px,top="+top1+"px,resizable=yes,statusbar=no,menubar=no,scrollbars=yes,dependent=no");
	templ_win.focus();
}

function getTempFieldEnable(val)
{
	if(val=='updatetmpl')
		document.getElementById('newtmpfld').style.display = 'none';
	else
		document.getElementById('newtmpfld').style.display = '';	
}

function isSameObject(sform_clean, sform_dirty) 
{
   return JSON.stringify(sform_clean) == JSON.stringify(sform_dirty);
}

function save_cust_emp_template(id) 
{
	var today = new Date();
	var dd = today.getDate();

	var mm = today.getMonth()+1; 
	var yyyy = today.getFullYear();
	if(dd<10) 
	{
	    dd='0'+dd;
	} 

	if(mm<10) 
	{
	    mm='0'+mm;
	} 
	var today_date = mm+'/'+dd+'/'+yyyy;

	form=document.compose;
	var flag = true;
	
	tinyMCE.triggerSave();
	var matter2 		= document.getElementById("matter2").value;
	var serializeData 	= $(form).serializeArray();
	var template_name 	= document.getElementById("template_name").value;
	var is_default 		= document.getElementById("is_default").value;
	var template_type	= document.getElementById("template_type").value;
	var ecamptype	= document.getElementById("ecamptype").value;

	if(template_name.trim() == '') 
	{
		alert('Please enter Template Name');
		return;
	}

	if(is_default == '1')
	{
		defaultTmplSave(id);
	}
	else
	{
	  	if(flag==true)
		{
			$.ajax({
			 	type: 'POST',
			 	async: false,
			 	url: 'cust_emp_store.php', 
			 	data: serializeData,
			 	success: function(result)
			 	{
			 		if(result!='')
					{
						var res = result.split("|");
						if(res[0] == 'Updated') 
						{
							alert('Template Updated Successfully.!');
							if(template_type == 'Customer') 
							{
								window.location.href = 'cust_email_tmpl.php?ecamptype='+id;
							}
							else if(template_type == 'Employee') 
							{
								window.location.href = 'emp_email_tmpl.php?ecamptype='+id;
							}
							window.opener.document.getElementById('temp_'+id).innerHTML = template_name;
						}
						else if(res[0] == 'Exists') 
						{
							if(is_default == 2)
							{
								var is_defaultVal = 1;
							}
							if(template_type == 'Customer') 
							{
								alert('Template Name Already Exists.!');
								document.getElementById("is_default").value = is_defaultVal;
							}
							else if(template_type == 'Employee') 
							{
								alert('Template Name Already Exists.!');
								document.getElementById("is_default").value = is_defaultVal;
							}
						}
						else if(res[0] == 'Saved') 
						{
							alert('Template Saved Successfully.!');
							if(template_type == 'Customer') 
							{
								window.location.href = 'cust_email_tmpl.php?ecamptype='+res[1];
								window.opener.$('#custtable > tbody').append($("<tr id='"+res[1]+"'><td align='center'><a href='javascript:void(0);' id='temp_"+res[1]+"' onclick=\"previewTmpl('"+res[1]+"',\'Customer\');\">"+res[2]+"<td align='center'>Customer</td><td align='center'>"+res[3]+"</td><td align='center'>"+today_date+"</td><td class='notifiIconGap' align='left'><a href='javascript:void(0);' onclick=\"addEditTmpl("+res[1]+",\'Customer\');\"><i class='fa fa-pencil-square-o fa-lg'></i></a>&nbsp;<a href='javascript:void(0);' onclick=\"removeTmpl("+res[1]+");\"><i class='fa fa-trash fa-lg' alt='Delete' title='Delete'></i></a></td></tr>"));
							}
							else if(template_type == 'Employee') 
							{
								window.location.href = 'emp_email_tmpl.php?ecamptype='+res[1];
								window.opener.$('#emptable > tbody').append($("<tr id='"+res[1]+"'><td align='center'><a href='javascript:void(0);' id='temp_"+res[1]+"' onclick=\"previewTmpl('"+res[1]+"',\'Employee\');\">"+res[2]+"<td align='center'>Employee</td><td align='center'>"+res[3]+"</td><td align='center'>"+today_date+"</td><td class='notifiIconGap' align='left'><a href='javascript:void(0);' onclick=\"addEditTmpl("+res[1]+",\'Employee\');\"><i class='fa fa-pencil-square-o fa-lg'></i></a>&nbsp;<a href='javascript:void(0);' onclick=\"removeTmpl("+res[1]+");\"><i class='fa fa-trash fa-lg' alt='Delete' title='Delete'></i></a></td></tr>"));
							}
						}
					}
				}
			});
		} 
		else return;
	}
}		
function save_aca_emp_template(id) 
{
	var today = new Date();
	var dd = today.getDate();

	var mm = today.getMonth()+1; 
	var yyyy = today.getFullYear();
	if(dd<10) 
	{
	    dd='0'+dd;
	} 

	if(mm<10) 
	{
	    mm='0'+mm;
	} 
	var today_date = mm+'/'+dd+'/'+yyyy;

	form=document.compose;
	var flag = true;

	tinyMCE.triggerSave();
	var matter2 		= document.getElementById("matter2").value;
	var serializeData 	= $(form).serializeArray();
	var template_name 	= document.getElementById("template_name").value;
	var template_subject= document.getElementById("subject").value;
	var template_type	= document.getElementById("template_type").value;
	var ecamptype		= document.getElementById("ecamptype").value;

	if(template_name.trim() == '') 
	{
		alert('Please enter Template Name');
		return;
	}

  	if(flag==true)
	{
		$.ajax({
		 	type: 'POST',
		 	async: false,
		 	url: 'aca_emp_store.php', 
		 	data: serializeData,
		 	success: function(result)
		 	{
		 		if(result!='')
				{
					if(result == 'Updated') 
					{
						alert('Template Updated Successfully.!');
						window.location.href = 'aca_email_tmpl.php?ecamptype='+id;
						window.opener.document.getElementById('temp_'+id).innerHTML = template_name;
					}
				}
			}
		});
	} 
	else return;
}	

/*New Functions added for Mass Communication*/

/*New Functions added for SMS Template*/
function addEditSMSTmpl(id) 
{
	var v_width  = 800;
	var v_heigth = 290;
	var top1=(window.screen.availHeight-v_heigth)/2;
	var left1=(window.screen.availWidth-v_width)/2;

	var parms = "width="+v_width+"px,";
	parms += "height="+v_heigth+"px,";
	parms += "left="+left1+"px,";
	parms += "top="+top1+"px,";
	parms += "statusbar=no,";
	parms += "menubar=no,";
	parms += "scrollbars=yes,";
	parms += "dependent=yes,";
	parms += "resizable=yes";

	top.remattachfiles_cand=window.open("emp_sms_tmpl.php?ecamptype="+id,"newView",parms);	
	top.remattachfiles_cand.focus();
}

function SMSWinOpen()
{
	var v_width  = 960;
	var v_heigth = 525;
	var top1=(window.screen.availHeight-v_heigth)/2;
	var left1=(window.screen.availWidth-v_width)/2;

	var form=document.forms[0];
	var cttype=form.acttype.value;
	var added1=form.added1.value;

	var added1 = document.getElementById("added1").value;
	filename = 'manage_sms_emp_fields.php';
	
	var url="/include/"+filename+"?acttype=all&added="+added1;
	remote=window.open(url,'listSMSinfo','width='+v_width+'px,height='+v_heigth+'px,statusbar=no,menubar=no,resize=no,left='+left1+'px,top='+top1+'px,scrollbars=yes');
	remote.focus();
}

function save_sms_template(id) {

	var today = new Date();
	var dd = today.getDate();

	var mm = today.getMonth()+1; 
	var yyyy = today.getFullYear();
	if(dd<10) 
	{
	    dd='0'+dd;
	} 

	if(mm<10) 
	{
	    mm='0'+mm;
	} 
	var today_date = mm+'/'+dd+'/'+yyyy;

	form=document.sms;
	var flag = true;
	var serializeData 	= $(form).serializeArray();

	var sms_matter 		= document.getElementById("sms_matter").value;
	var matterLen 		= sms_matter.length;
	var len 			= lengthofFields(sms_matter);
	var spltLen 		= len.split("|");
	var staticMatterLen = matterLen-parseInt(spltLen[1]);
	var finalMatterLen	= parseInt(staticMatterLen)+parseInt(spltLen[0]);

	if(finalMatterLen > 300)
	{
		alert("Maximum character length is 300 only. This can be calculated based on the dynamic values of the fields selected and characters entered.");
		return;
	}

	var template_name 	= document.getElementById("template_name").value;
	var ecamptype		= document.getElementById("ecamptype").value;

	if(template_name.trim() == '') 
	{
		alert('Please enter Template Name');
		return;
	}

  	if(flag==true)
	{
		$.ajax({
		 	type: 'POST',
		 	async: false,
		 	url: 'sms_temp_store.php', 
		 	data: serializeData,
		 	success: function(result)
		 	{
		 		if(result!='')
				{
					var res = result.split("|");
					if(res[0] == 'Updated') 
					{
						alert('Template Updated Successfully.!');
						window.location.href = 'emp_sms_tmpl.php?ecamptype='+id;
						window.opener.document.getElementById('tempSMS_'+id).innerHTML = template_name;
					}
					else if(res[0] == 'Exists') 
					{
						alert('Template Name Already Exists.!');
					}
					else if(res[0] == 'Saved') 
					{
						alert('Template Saved Successfully.!');
						window.location.href = 'emp_sms_tmpl.php?ecamptype='+res[1];
						window.opener.$('#smstable > tbody').append($("<tr id='tmplSMS_"+res[1]+"'><td align='center'><a href='javascript:void(0);' id='tempSMS_"+res[1]+"' onclick=\"previewSMSTmpl('"+res[1]+"');\">"+res[2]+"<td align='center'>Employee</td><td align='center'>"+res[3]+"</td><td align='center'>"+today_date+"</td><td class='notifiIconGap' align='left'><a href='javascript:void(0);' onclick=\"addEditSMSTmpl("+res[1]+");\"><i class='fa fa-pencil-square-o fa-lg'></i></a>&nbsp;<a href='javascript:void(0);' onclick=\"removeSMSTmpl("+res[1]+");\"><i class='fa fa-trash fa-lg' alt='Delete' title='Delete'></i></a></td></tr>"));
						}
					}
				}
		});
	} 
	else return;
}

function removeSMSTmpl(id) 
{
	if(id!='')
	{
		$.ajax({
		 	type: 'POST',
		 	async: false,
		 	url: 'sms_temp_store.php', 
		 	data: 'id='+id+'&mode=delete',
		 	success: function(result)
		 	{
		 		if(result!='')
				{
					alert("Template has been deleted successfully");
					$("#tmplSMS_"+id).remove();
				}
			}
		});
	}
}

function lengthofFields(matter) {

	var fieldLength = 0;
	var remVarLen 	= 0;

	/*Employee Name*/
	var empCnt 		= (matter.match(/{{@employee_name}}/g) || []).length;
	var empName 	= includesFind(matter,'{{@employee_name}}');
	if(empName == true) 
	{
		if(empCnt > 1) 
		{
			var empNameLenght = 30 * empCnt;
			remVarLen = remVarLen+18 * empCnt;
		} 
		else 
		{
			var empNameLenght = 30;
			remVarLen = remVarLen+18;		
		}
		fieldLength = fieldLength+empNameLenght;
	}
	
	/*Assignment Name*/
	var assgnCnt 	= (matter.match(/{{@assignment_title}}/g) || []).length;
	var assgnTitle 	= includesFind(matter,'{{@assignment_title}}');
	if(assgnTitle == true) 
	{
		if(assgnCnt > 1) 
		{
			var assgnTitleLenght = 30 * assgnCnt;
			remVarLen = remVarLen+21 * assgnCnt;
		} 
		else 
		{
			var assgnTitleLenght = 30;
			remVarLen = remVarLen+21;		
		}
		fieldLength = fieldLength+assgnTitleLenght;
	}

	/*Assignment ID*/
	var assgnIDCnt 	= (matter.match(/{{@assignment_id}}/g) || []).length;
	var assgnID 	= includesFind(matter,'{{@assignment_id}}');
	if(assgnID == true) 
	{
		if(assgnIDCnt > 1) 
		{
			var assgnIDLenght = 10 * assgnIDCnt;
			remVarLen = remVarLen+18 * assgnIDCnt;
		} 
		else 
		{
			var assgnIDLenght = 10;
			remVarLen = remVarLen+18;
		}
		fieldLength = fieldLength+assgnIDLenght;
	}

	/*Company*/
	var cmpyCnt 	= (matter.match(/{{@company}}/g) || []).length;
	var cmpyName 	= includesFind(matter,'{{@company}}');
	if(cmpyName == true) 
	{
		if(cmpyCnt > 1) 
		{
			var cmpyNameLenght = 30 * cmpyCnt;
			remVarLen = remVarLen+12 * cmpyCnt;
		} 
		else 
		{
			var cmpyNameLenght = 30;
			remVarLen = remVarLen+12;
		}
		fieldLength = fieldLength+cmpyNameLenght;
	}

	/*Job Locaion*/
	var joblocCnt 		= (matter.match(/{{@job_location}}/g) || []).length;
	var jobLocation 	= includesFind(matter,'{{@job_location}}');
	if(jobLocation == true) 
	{
		if(joblocCnt > 1) 
		{
			var jobLocationLenght = 50 * joblocCnt;
			remVarLen = remVarLen+17 * joblocCnt;
		} 
		else 
		{
			var jobLocationLenght = 50;
			remVarLen = remVarLen+17;
		}
		fieldLength = fieldLength+jobLocationLenght;
	}

	/*Assignment Start Date*/
	var strtdteCnt 	= (matter.match(/{{@start_date}}/g) || []).length;
	var startDate 	= includesFind(matter,'{{@start_date}}');
	if(startDate == true) 
	{
		if(strtdteCnt > 1) 
		{
			var startDateLenght = 10 * strtdteCnt;
			remVarLen = remVarLen+15 * strtdteCnt;
		} 
		else 
		{
			var startDateLenght = 10;
			remVarLen = remVarLen+15;
		}
		fieldLength = fieldLength+startDateLenght;
	}

	/*Assignment End Date*/
	var enddteCnt 	= (matter.match(/{{@end_date}}/g) || []).length;
	var endDate 	= includesFind(matter,'{{@end_date}}');
	if(endDate == true) 
	{
		if(enddteCnt > 1) 
		{
			var endDateLenght = 10 * enddteCnt;
			remVarLen = remVarLen+13 * enddteCnt;
		} 
		else 
		{
			var endDateLenght = 10;
			remVarLen = remVarLen+13;
		}
		fieldLength = fieldLength+endDateLenght;
	}

	/*Shift Name*/
	var shftNmeCnt 	= (matter.match(/{{@shift_name}}/g) || []).length;
	var shiftName 	= includesFind(matter,'{{@shift_name}}');
	if(shiftName == true) 
	{
		if(shftNmeCnt > 1) 
		{
			var shiftNameLenght = 30 * shftNmeCnt;
			remVarLen = remVarLen+15 * shftNmeCnt;
		} 
		else 
		{
			var shiftNameLenght = 30;
			remVarLen = remVarLen+15;
		}
		fieldLength = fieldLength+shiftNameLenght;
	}

	/*Shift Start Date*/
	var shftStrtDteCnt 	= (matter.match(/{{@shift_start_date}}/g) || []).length;
	var shiftsDate 		= includesFind(matter,'{{@shift_start_date}}');
	if(shiftsDate == true) 
	{
		if(shftStrtDteCnt > 1) 
		{
			var shiftsDateLenght = 8 * shftStrtDteCnt;
			remVarLen = remVarLen+21 * shftStrtDteCnt;
		} 
		else 
		{
			var shiftsDateLenght = 8;
			remVarLen = remVarLen+21;
		}
		fieldLength = fieldLength+shiftsDateLenght;
	}

	/*Shift End Date*/
	var shftEndDteCnt 	= (matter.match(/{{@shift_end_date}}/g) || []).length;
	var shifteDate 		= includesFind(matter,'{{@shift_end_date}}');
	if(shifteDate == true) 
	{
		if(shftEndDteCnt > 1) 
		{
			var shifteDateLenght = 8 * shftEndDteCnt;
			remVarLen = remVarLen+19 * shftEndDteCnt;
		} 
		else 
		{
			var shifteDateLenght = 8;
			remVarLen = remVarLen+19;
		}
		fieldLength = fieldLength+shifteDateLenght;
	}
	return fieldLength+'|'+remVarLen;
}

function previewSMSTmpl(id) 
{
	var v_width  = 600;
	var v_heigth = 290;
	var top1=(window.screen.availHeight-v_heigth)/2;
	var left1=(window.screen.availWidth-v_width)/2;

	var parms = "width="+v_width+"px,";
	parms += "height="+v_heigth+"px,";
	parms += "left="+left1+"px,";
	parms += "top="+top1+"px,";
	parms += "statusbar=no,";
	parms += "menubar=no,";
	parms += "scrollbars=yes,";
	parms += "dependent=yes,";
	parms += "resizable=yes";

	top.remattachfiles_cand=window.open("showPreviewSMSTmpl.php?tmplId="+id,"newView",parms);	
	top.remattachfiles_cand.focus();
}

function editPreviewSMSTmpl(id) 
{
	window.location.href = 'emp_sms_tmpl.php?ecamptype='+id;		
}

function includesFind(container, value) 
{
	var returnValue = false;
	var pos = container.indexOf(value);
	if (pos >= 0) {
		returnValue = true;
	}
	return returnValue;
}
/*New Functions added for SMS Template*/
function save_timesheet_template(id) 
{
	var today = new Date();
	var dd = today.getDate();

	var mm = today.getMonth()+1; 
	var yyyy = today.getFullYear();
	if(dd<10) 
	{
	    dd='0'+dd;
	} 

	if(mm<10) 
	{
	    mm='0'+mm;
	} 
	var today_date = mm+'/'+dd+'/'+yyyy;

	form=document.compose;
	var flag = true;
	
	tinyMCE.triggerSave();
	var matter2 		= document.getElementById("matter2").value;
	var serializeData 	= $(form).serializeArray();
	var template_name 	= document.getElementById("template_name").value;
	var is_default 		= document.getElementById("is_default").value;
	var template_type	= document.getElementById("template_type").value;
	var ecamptype	= document.getElementById("ecamptype").value;
	if(template_name.trim() == '') 
	{
		alert('Please enter Template Name');
		return;
	}

	  	if(flag==true)
		{
			$.ajax({
			 	type: 'POST',
			 	async: false,
			 	url: 'timesheet_expense_store.php', 
			 	data: serializeData,
			 	success: function(result)
			 	{
			 		if(result!='')
					{
						var res = result.split("|");
						if(res[0] == 'Updated') 
						{
							alert('Template Updated Successfully.!');
							if(template_type == 'Approve') 
							{
								window.location.href = 'timesheet_email_tmpl.php?ecamptype='+id+'&module='+template_type;
							}
							else if(template_type == 'Submitted') 
							{
								window.location.href = 'timesheet_email_tmpl.php?ecamptype='+id+'&module='+template_type;
							}
							else
							{
								window.location.href = 'timesheet_email_tmpl.php?ecamptype='+id+'&module='+template_type;
							}
							window.opener.document.getElementById('temp_'+id).innerHTML = template_name;
						}
						
					}
				}
			});
		} 
		else return;
}
function updatenotify(module,userid)
{
	var status = "";
	var message = "";
	var overwrite = "";
	if(module == "Approve")
	{
		
		status = $("#notify"+userid+" a").attr("data-notifystatus");
		if(status == "ON")
		{
			message = "disabled";
			status = 0;
			
		}else{

			message = "enabled";
			status = 1;
			
		}
		if(confirm("Timesheet Approval Notification Sent to CSS user(s) will be "+message+"\n Click on OK to Continue and Cancel to return"))
		{
			updateTimesheetNotify(module,status,'update',userid);
				
		}

		
	}else{

		status = $("#notify"+userid+" a").attr("data-notifystatus");
		if(status == "ON")
		{
			message = "will not";
			status = 0;
			
		}else{

			message = "will";
			status = 1;
			
		}

		if(module == "Submitted")
		{
			if(confirm("The Submitted Timesheet Notification "+message+" be sent to ESS User(s).\n Click on OK to Continue and Cancel to return"))
			{
				updateTimesheetNotify(module,status,'update',userid);
			}

		}else{


			if(confirm("The Rejected Timesheet Notification "+message+" be sent to ESS User(s).\n Click on OK to Continue and Cancel to return"))
			{
				updateTimesheetNotify(module,status,'update',userid);
			}
		}
		
	}
		
}
function updateTimesheetNotify(module,is_overwrite,mode,teml_id)
{
		form=document.compose;
		var serializeData 	= $(form).serializeArray();
		$.ajax({
	 	type: 'POST',
	 	async: false,
	 	url: 'timesheet_expense_store.php', 
	 	data:{module:module,is_overwrite:is_overwrite,mode:mode,teml_id:teml_id},
	 	success:function(response)
	 	{
	 		if(is_overwrite == 0)
	 		{
	 			$("#notify"+response).removeClass().addClass("notifiBtnDeActive");
				$("#notify"+response+" a").attr("data-notifystatus","OFF").html("OFF");
	 		}else{
	 			$("#notify"+response).removeClass().addClass("notifiBtnActive");
				$("#notify"+response+" a").attr("data-notifystatus","ON").html("ON");
	 		}
	 	}
	 	});
}
/* Function to enable/disable ACA module specific settings block */
function acatoggleDisplay(field) {
	fieldname = field.id;
	disable_block_id = fieldname+"-block";
	
	if (field.checked) {
		document.getElementById(fieldname).value = "1";
	}
	else{
		var x = confirm("Disabling, ACA Management Notification will stop sending notifications. Click on OK to continue or Click on Cancel to return");
		if(x){
			field.checked = false;
		}else{
			field.checked = true;
		}
	}
}

/* Function for enable Task Notification specific settings block*/
function tasktoggleDisplay(field)
{
	fieldname = field.id;
	if (field.checked) {
		document.getElementById(fieldname).value = "1";
		$("#notifymode").removeAttr("disabled");
		$("#notify_people").removeAttr("disabled");
		$("#task_EmailSetupSno").removeAttr("disabled");
	}
	else{
		var x = confirm("Disabling, Task Notification will stop sending notifications. Click on OK to continue or Click on Cancel to return");
		if(x){
			document.getElementById(fieldname).value = "0";	
			$("#notifymode").attr("checked", false);
			$("#notifymode").prop('disabled', 'disabled');
			$("#notify_people").attr("checked", false);
			$("#notify_people").prop('disabled', 'disabled');
			$('#task_EmailSetupSno').prop('disabled', 'disabled');
		}else{
			field.checked = true;
		}
	}
}

function doTaskUpdate()
{
	window.onbeforeunload = null;


	form=document.compose;
	var flag = true;
	tinyMCE.triggerSave();
	var matter2 		= document.getElementById("matter2").value;
	var tmpl_subject 	= document.getElementById("subject").value;

	if(tmpl_subject.trim() == '') 
	{
		alert('Please enter Subject');
		$("#subject").focus();
		return;
	}
	else
	{
		var serializeData 	= $(form).serializeArray();
		$('#autopreloader').show();
		$('.newLoader').show();
		console.log(serializeData);
		$.ajax({
		 	type: 'POST',
		 	async: false,
		 	url: 'update_task_notify_settings.php', 
		 	data: serializeData,
		 	success: function(result)
		 	{
		 		$('#autopreloader').hide();
		      	$('.newLoader').hide();
		      	if(result!= '') 
		      	{
		      		window.location.href = '/BSOS/Admin/Notifications_Mngmt/task_notifications.php?msg=updated';
		      	}
			}
		});
	}
	
	
}

function updateInvoiceCSSNotify(teml_id)
{
	var status = "";
	var message = "";
	var overwrite = "";

	status = $("#notify"+teml_id+" a").attr("data-notifystatus");
	if(status == "ON")
	{
		message = "disabled";
		status = 0;
		
	}else{

		message = "enabled";
		status = 1;
		
	}
	if(confirm("Invoice Notification Sent to CSS user(s) will be "+message+"\n Click on OK to Continue and Cancel to return"))
	{
		updateeInvoiceCSSUserNotify(status,'update',teml_id);
			
	}
}

function updateeInvoiceCSSUserNotify(is_overwrite,mode,deliver_teml_id)
{
		form=document.compose;
		$.ajax({
	 	type: 'POST',
	 	async: false,
	 	url: 'inv_css_email_store.php', 
	 	data:{is_overwrite:is_overwrite,mode:mode,deliver_teml_id:deliver_teml_id},
	 	success:function(response)
	 	{
	 		if(is_overwrite == 0)
	 		{
	 			$("#notify"+response).removeClass().addClass("notifiBtnDeActive");
				$("#notify"+response+" a").attr("data-notifystatus","OFF").html("OFF");
				 $('#is_overwrite').val("0");
	 		}else{
	 			$("#notify"+response).removeClass().addClass("notifiBtnActive");
				$("#notify"+response+" a").attr("data-notifystatus","ON").html("ON");
				 $('#is_overwrite').val("1");

	 		}
	 	}
	 	});
}

function doCicoNotificationUpdate()
{
	window.onbeforeunload = null;
	var sform_dirty = $("form").serialize();
	if (sform_clean == sform_dirty) 
	{
		alert("There are no Changes to update");
	} 
	else 
	{
		form=document.nform;
		var cico_notify_to_error_flag = false;
		var placement_not 	= true;
		var chk3		= true;
		if(placement_not){
			var emp 	= $('#cico_primaryemail').is(':checked');
			var sms 	= $('#cico_sms').is(':checked');
			if(emp==false && emp==sms){
				cico_notify_to_error_flag = true;
			}else if(emp==true){
				var pemail = document.getElementById("cico_primaryemail");
				if(pemail.checked==false){
					cico_notify_to_error_flag = true;
				}else{
					cico_notify_to_error_flag = false;
				}
			}else if(sms==true){
				cico_notify_to_error_flag = false;				
			}
			else{
				chk3 = true;
			}			
		}
		if(chk3){
			form.submit();			
		}		
	}
}

function updateciconotify(module,userid)
{
	var status = "";
	var message = "";
	var overwrite = "";
	if(module == "ClockIn")
	{
		
		status = $("#notify"+userid+" a").attr("data-notifystatus");
		if(status == "ON")
		{
			message = "disabled";
			status = 0;
			
		}else{

			message = "enabled";
			status = 1;
			
		}
		if(confirm("Employee Clock In Template will be "+message+"\n Click on OK to Continue and Cancel to return"))
		{
			updateCICONotify(module,status,'update',userid);
		}

		
	} else if(module == "ClockOut"){

		status = $("#notify"+userid+" a").attr("data-notifystatus");
		if(status == "ON")
		{
			message = "disabled";
			status = 0;
			
		}else{

			message = "enabled";
			status = 1;
			
		}

		if(confirm("Employee Clock Out Template will be "+message+"\n Click on OK to Continue and Cancel to return"))
		{
			updateCICONotify(module,status,'update',userid);
		}
		
	} else {
		alert("Module not defined");
		return false;
	}
		
}

function updateCICONotify(module,is_overwrite,mode,teml_id)
{
		form=document.compose;
		var serializeData 	= $(form).serializeArray();
		$.ajax({
	 	type: 'POST',
	 	async: false,
	 	url: 'cico_store.php', 
	 	data:{module:module,is_overwrite:is_overwrite,mode:mode,teml_id:teml_id},
	 	success:function(response)
	 	{
	 		if(is_overwrite == 0)
	 		{
	 			$("#notify"+response).removeClass().addClass("notifiBtnDeActive");
				$("#notify"+response+" a").attr("data-notifystatus","OFF").html("OFF");
	 		}else{
	 			$("#notify"+response).removeClass().addClass("notifiBtnActive");
				$("#notify"+response+" a").attr("data-notifystatus","ON").html("ON");
	 		}
	 	}
	 	});
}

function save_cico_template(id) 
{
	var today = new Date();
	var dd = today.getDate();

	var mm = today.getMonth()+1; 
	var yyyy = today.getFullYear();
	if(dd<10) 
	{
	    dd='0'+dd;
	} 

	if(mm<10) 
	{
	    mm='0'+mm;
	} 
	var today_date = mm+'/'+dd+'/'+yyyy;

	form=document.compose;
	var flag = true;
	
	tinyMCE.triggerSave();
	var matter2 		= document.getElementById("matter2").value;
	var serializeData 	= $(form).serializeArray();
	var template_name 	= document.getElementById("template_name").value;
	var is_default 		= document.getElementById("is_default").value;
	var template_type	= document.getElementById("template_type").value;
	var ecamptype	= document.getElementById("ecamptype").value;
	if(template_name.trim() == '') 
	{
		alert('Please enter Template Name');
		return;
	}

	if(flag==true)
	{
		$.ajax({
			type: 'POST',
			async: false,
			url: 'cico_store.php', 
			data: serializeData,
			success: function(result)
			{
				if(result!='')
				{
					var res = result.split("|");
					if(res[0] == 'Updated') 
					{
						window.location.href = 'cico_email_tmpl.php?ecamptype='+id+'&module='+template_type;
						window.opener.document.getElementById('temp_'+id).innerHTML = template_name;
					}
					
				}
			}
		});
	} 
	else return;
}