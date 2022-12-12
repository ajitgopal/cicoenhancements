<?php
	$mailconfig_set = "YES";

	require("global.inc");
	require("dispfunc.php");
	require("xmlwriterclass.php");
	require "xmltree.inc";
	require("Menu.inc");
	
	$menu=new EmpMenu();
	header("Content-type: text/html; charset=".CONVERT_DEFAULT_MAIL_CHAR);
	$CharSet_mail=AssignEmailCharset($charset);
	
	$mode = 'save';
	if(isset($ecamptype) && is_numeric($ecamptype)){
		$mode = 'edit';
	}

	$query_header="select sno,name,status,descri from header_footer where username='".$username."' and type='header' and status in ('ER','default')";
   	$res=mysql_query($query_header,$db);
   	$rows=mysql_num_rows($res);
   	if($rows!=0)
   	{
   		$header_opt="";
   		while($fetch_header=mysql_fetch_row($res))
   		{
   			if($fetch_header[2]=="default")
   			{
   				$header_opt.="Default#".$fetch_header[0]."#";
   				$head="<table width=650><tr><td>".$fetch_header[3]."</td></tr></table>";
   			}
   			else
   			{
   				$header_opt.=$fetch_header[1]."#".$fetch_header[0]."#";
   			}
   		}
   	}
   	$header_opt=substr(trim($header_opt),0,strlen(trim($header_opt))-1);

	//getting Signature values from Database
	$query_sign="select sno,name,status from user_sign where username='".$username."'";
	$res=mysql_query($query_sign,$db);
	$rows=mysql_num_rows($res);
   	if($rows!=0)
   	{
   		$footer_opt="";
   		while($fetch_footer=mysql_fetch_row($res))
   		{
   			if($fetch_footer[2]=="default")
   			{
   				$footer_opt.="Default#".$fetch_footer[0]."#";
   				$foot="<table width=650><tr><td>".$fetch_footer[3]."</td></tr></table>";
   			}
   			else
   			{
   				$footer_opt.=$fetch_footer[1]."#".$fetch_footer[0]."#";
   			}
   		}
   	}
   	$footer_opt=substr(trim($footer_opt),0,strlen(trim($footer_opt))-1);
?>
<html>
<head>
<title><?php echo $title;?></title>
<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/new_editor_tab.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/new_editor_design.css">
<!-- <link type="text/css" rel="stylesheet" href="/BSOS/css/select2.css"> -->
<link type="text/css" rel="stylesheet" href="/BSOS/css/customselect/select2_V_4.0.3.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/customselect/CustomSelect.css">
<?php getJQLibs(['jquery','jqueryUI']);  ?>
<!-- <script type="text/javascript" src="/BSOS/scripts/select2.js"></script> -->
<script type="text/javascript" src="/BSOS/scripts/customselect/select2_V_4.0.3.js"></script>
<script src="/BSOS/scripts/getBrowserInfo.js"></script>
<script language="javascript" src="/BSOS/Marketing/Campaigns/scripts/newcampaign.js"></script>
<script src="/BSOS/scripts/tabpane.js"></script>
<script src="/BSOS/scripts/common.js"></script>
<script src="/BSOS/scripts/common_ajax.js"></script>
<script src="/BSOS/tinymce/jscripts/tiny_mce/tiny_mce_src.js"></script>
<script type="text/javascript" src="/BSOS/scripts/ui.dropdownchecklist-min.js"></script>
<link type="text/css" rel="stylesheet" href="/BSOS/css/ui.dropdownchecklist.css">


<script language="javascript" type="text/javascript">
tinyMCE.init({
	mode : 'exact',
	theme : 'advanced',
	elements : "matter2",
	height : "300",
	content_css : "/BSOS/css/submission_design.css",
	plugins : "autolink,lists,layer,advimage,advlink,iespell,spellchecker,inlinepopups,searchreplace,print,contextmenu,paste,directionality,noneditable,visualchars,nonbreaking,xhtmlxtras,advlist",
	autoresize_max_height: 300
});

//it executes after closing footer.php popup 
function dynamicFooter(val)
{
	updateFooter(val);
}
//it executes after closing header.php popup 
function dynamicHeader(val)
{
	updateHeader(val);
}
function addHeader(param)
{
	var header="<?=$header_opt?>";
	if(header!="")
		var hea=header.split("#");
	else
		var hea="";

	document.getElementById(param).options.length=0;
	var opt;

	opt=document.createElement('option');
	document.getElementById(param).options.add(opt);
	opt.text = "No Header";
	opt.value = 0;
	
	for (var i=0;i<hea.length;i+=2)
	{
		opt=document.createElement('option');
		document.getElementById(param).options.add(opt);
		opt.text = hea[i];
		opt.value = hea[i+1];		

		if(opt.text.toLowerCase()=="default")
		{
			opt.setAttribute('selected','selected');
			updateHeader(opt.value);
		}
	}
}

function addSignature(arg)
{
	var footer="<?=$footer_opt?>";

	if(footer!="")
		var foot_arr=footer.split("#");
	else
		var foot_arr="";

	document.getElementById(arg).options.length=0;
	var opt;

	opt=document.createElement('option');
	document.getElementById(arg).options.add(opt);
	opt.text = "No Signature";
	opt.value = 0;

	for (var i=0;i<foot_arr.length;i+=2)
	{
		opt=document.createElement('option');
		document.getElementById(arg).options.add(opt);
		opt.text = foot_arr[i];
		opt.value = foot_arr[i+1];

		if(opt.text.toLowerCase()=="default")
		{
			opt.setAttribute('selected','selected');
			updateFooter(opt.value);
		}
	}
}

function onloadHeaderFooter(param,param2)
{
	var eid=tinyMCE.get("matter2");
	var emailheaderid = document.getElementById("email_header_id").value;
	var emailsignatureid = document.getElementById("email_signature_id").value;
	if(typeof eid=="undefined")
	{
		setTimeout("onloadHeaderFooter('mce_editor_0_header','mce_editor_0_footer')",1000);
	}
	else
	{
		var header="<?=$header_opt?>";
		if(header!="")
			var hea=header.split("#");
		else
			var hea="";

		document.getElementById(param).options.length=0;
		var opt;

		opt=document.createElement('option');
		document.getElementById(param).options.add(opt);
		opt.text = "No Header";
		opt.value = 0;

		var headVal=opt.value;
		for (var i=0;i<hea.length;i+=2)
		{
			opt=document.createElement('option');
			document.getElementById(param).options.add(opt);			
			opt.text = hea[i];
			opt.value = hea[i+1];		
			if(emailheaderid !='' && opt.value == emailheaderid){
				opt.setAttribute('selected','selected');
				headVal = opt.value;
			} 
			else if(opt.text.toLowerCase()=="default")
			{
				opt.setAttribute('selected','selected');
				headVal = opt.value;
			}
		}
	
		var footer="<?=$footer_opt?>";
		if(footer!="")
			var foot_arr=footer.split("#");
		else
			var foot_arr="";
		document.getElementById(param2).options.length=0;
		var opt_foot;

		opt_foot=document.createElement('option');
		document.getElementById(param2).options.add(opt_foot);
		opt_foot.text = "No Signature";
		opt_foot.value = 0;
		var footVal=opt_foot.value;

		for (var i=0;i<foot_arr.length;i+=2)
		{
			opt_foot=document.createElement('option');
			document.getElementById(param2).options.add(opt_foot);
			opt_foot.text = foot_arr[i];
			opt_foot.value = foot_arr[i+1];
			if(emailsignatureid !='' && opt_foot.value == emailsignatureid){
				opt_foot.setAttribute('selected','selected');
				footVal = opt_foot.value;
			} 
			else if(opt_foot.text.toLowerCase()=="default")
			{
				opt_foot.setAttribute('selected','selected');
				footVal = opt_foot.value;
			}
		}	
		var val=headVal+"|"+footVal;
		dispHeadFooter(val);
	}
	//setselectedHeaderFooter();
}

var a=0;
var sub1="";
function setFlag1()
{
	a=a+1;
	//gettemplateData();
	return true;
}

function save_cico_template(e)
{
	var template_name 	= document.getElementById("template_name").value;
	if(template_name.trim() == '') 
	{
		alert('Please enter Template Name');
		return;
	}
	var confirmmsg = confirm("Click on OK to save your changes to the template in Notification Management and send the notification. Click on Cancel to use your changes and send a one time notification.");

	if(confirmmsg==true) 
	{
		document.getElementById('onetime_edit').value 	= 1;
	}
	if(confirmmsg==false) 
	{
		document.getElementById('onetime_edit').value 	= 0;
	}

	var form=document.compose;
	var eid=tinyMCE.get('matter2');
	var body=eid.getBody();
	var content = body.innerHTML;

	//if(confirmmsg==true) 
	//{
		form.submit();
	//}
}
</script>
<script src="/BSOS/scripts/attach_upload.js"></script>
<script src="/BSOS/scripts/placement_notificationcolumn.js"></script>

<!-- <script language="javascript" src="scripts/validatefields.js"></script> -->
<script>
var ColSelVal="<?php echo $added?>";
var Can_Job_ID_Data="<?php echo $candidateVal?>";
CampType="custtype";
setDefaultValues();
</script>
<script>

function wordStartMatcher(term, text, highlighting) {
    var myRe  = new RegExp("(?:^|\\s)" + term, "i");
    var match = myRe.exec(text);
 
    if (match != null && highlighting) {
        myRe = new RegExp("\\b" + term, "i");
        match = myRe.exec(text);
    }
 
    return match;
}

</script>
<style type="text/css">
.maingridbuttonspad{ padding:0px;}
.ProfileNewUI, .hfontstyle{ font-size:13px;}
.customEmailInputWidth input[type="text"], .customEmailInputWidth select{ padding:6px 4px; width:350px;}
.customEmailInputWidth input[type="text"]:focus{border: 1px solid #6998d1; box-shadow: 0 0 5px #6998d1;}
.mandatFieldColor{ color:#ff0000; font-size:16px;}
.removeFiltersInn {left: 322px; right:inherit}
.afontstyle1{ padding-left:0px;}
.custEmailPad{ padding:10px 0px 0px 10px;}
.addRemoveM {border: solid 1px #ccc;padding: 10px;min-height: 177px;}
.addRemoveHed {font-weight: bold;font-size: 14px;border-bottom: solid 1px #ccc;padding-bottom: 10px;}
#matter2_tbl, #matter2_ifr{height:200px !important;} 
</style>
</head>
<?php 
if($ecamptype == "compose") 
{
	?>
	<body marginheight=0 marginwidth=0 topmargin=10 leftmargin=10 style='overflow:auto' onLoad="setFlag1();setFlag()" onunload='unsetFlag()'>
	<?php
}
else
{
	?>
	<body marginheight=0 marginwidth=0 topmargin=10 leftmargin=10 style='overflow:auto' onLoad="setFlag1();return onloadHeaderFooter('mce_editor_0_header','mce_editor_0_footer'),setFlag()" onunload='unsetFlag()'>
	<?php
}
?>

<form name="compose" id="compose" action="cicotempstore.php" method="post">
<input type=hidden name="head"  id="head" value="<?php echo html_tls_specialchars($head);?>">
<input type=hidden id="foot" name="foot" value="<?php echo html_tls_specialchars($foot);?>">
<input type=hidden name=req_stat id=req_stat value="<?php echo $req_stat;?>">
<input type=hidden name=folder value="CampaignResponses">
<input type=hidden name=matter3_data id=matter3_data>
<input type=hidden name=body_data id=body_data>
<input type=hidden name=editor_check id=editor_check>
<input type=hidden name=ecamptype id="ecamptype" value="<?php echo $ecamptype;?>">
<input type=hidden name=Check_Editor_Mode id=Check_Editor_Mode value="1">
<input type=hidden name=charset id=charset value="<?php echo $CharSet_mail;?>">
<input type="hidden" name="chkFstEditMode" id="chkFstEditMode" value="N">
<input type="hidden" name="chkFstEditVal" id="chkFstEditVal" value="">
<input type="hidden" name="chkNorEditData" id="chkNorEditData" value="">
<input type="hidden" name="mode" id="mode" value="<?php echo $mode; ?>">
<input type="hidden" name="onetime_edit" id="onetime_edit" value="">



<table border="0" width="100%" cellspacing="0" cellpadding="0" class="ProfileNewUI">
<tr>
  <td width=100%  align=left>
	<table width=100% cellpadding=0 cellspacing=0 border=0>
        <tr class="NewGridTopBg">
        <?php
			if($mode == 'edit'){
				$Header_name=explode("|","fa fa-clone~Update|fa-ban~Cancel");
				$Header_link=explode("|","javascript:save_cico_template(this);|javascript:window.history.back();");
				$Header_heading="&nbsp;Edit CICO Email Template";
			}
			
			$menu->showHeadingStrip1($Header_name,$Header_link,$Header_heading);

			$notification_setting_query = "select nt.id, nt.template_name, nt.email_body, nt.email_subject, nt.email_signature_id, 
			nt.email_header_id, nt.email_merger_columns, nt.is_default, nt.is_overwrite, nt.template_type, nt.locations, nt.departments, 
			h.descri as header, s.descri as signature from notifications_templates nt LEFT JOIN header_footer h ON 
			nt.email_header_id = h.sno LEFT JOIN user_sign s ON nt.email_signature_id = s.sno 
			where nt.status = 'ACTIVE' and nt.mod_id = 'cico' and nt.id=".$ecamptype;
			

			$res_details 	= mysql_query($notification_setting_query,$db);
			$res_not 		= mysql_fetch_array($res_details);
			$cust_header 	= '';
			$cust_sign 		= '';
			$sel_dept_ids = '';
			if(!empty($res_not['email_subject'])) 
			{ 
				$email_sub = $res_not['email_subject'];
			}
			
			if(!empty($res_not['email_signature_id'])) 
			{ 
				$cust_temp_signature_id = $res_not['email_signature_id'];
			}

			if(!empty($res_not['email_header_id'])) 
			{ 
				$cust_temp_header_id = $res_not['email_header_id'];
			}

			if(!empty($res_not['header'])) 
			{ 
				$cust_header = '<span id="head"><span id="header"><table width="650"><tbody><tr><td>'.$res_not['header'].'</td></tr></tbody></table></span></span>';
			}
			
			if($res_not['email_body'] != '') 
			{
				$email_body = $res_not['email_body'];
			}

			if(!empty($res_not['signature'])) 
			{ 
			
				$cust_sign = '<span id="foot"><span id="footer"><table width="650"><tbody><tr><td>'.$res_not['signature'].'</td></tr></tbody></table></span></span>';
			}
			$sel_dept_ids = $res_not['departments'];
			$cust_columns = "";
			if($res_not['email_merger_columns'] != '') 
			{
				$cust_columns = $res_not['email_merger_columns'];
			}

			if(!empty($res_not['template_type'])) 
			{ 
				$template_type = $res_not['template_type'];
			}
		?>
		</tr>
		<input type="hidden" name="is_overwrite" id="is_overwrite" value="<?php echo $res_not['is_overwrite'] ;?>">
		<input type=hidden name="cust_header" id="cust_header" value="<?php echo html_tls_specialchars(stripslashes($cust_header));?>">
		<input type=hidden name="cust_sign" id="cust_sign" value="<?php echo html_tls_specialchars(stripslashes($cust_sign));?>">

		<input type=hidden name="status" id="status" value="<?php echo $res_not['status'];?>">
		<input type=hidden name="teml_id" id="teml_id" value="<?php echo $res_not['id'];?>">
		<input type=hidden name="is_default" id="is_default" value="<?php echo $res_not['is_default'];?>">
		<input type=hidden name="template_type" id="template_type" value="<?php echo $template_type;?>">
		<tr>
			<td class="custEmailPad">
			<table border="0" width="100%" cellspacing="0" cellpadding="4" class="ProfileNewUI customEmailInputWidth">
				<!--New Tr Added for Template name-->
				<tr>
					<td  align=left width=18%><font class=hfontstyle>Template Name: <span class="mandatFieldColor">*</span></font></td>
					<td  width=82%><input type=text id="template_name" name="template_name" size=95 value="<?php echo html_tls_entities(stripslashes($res_not['template_name']));?>" placeholder="Please Enter Template Name"></td>
				</tr>
				<tr>
					<td  align=left width=18%><font class=hfontstyle>Subject:&nbsp;&nbsp;</font></td>
					<td  width=82%><input type=text id="subject" name=subject size=95 value="<?php echo html_tls_entities(stripslashes($email_sub));?>" onKeyDown="return chkSubjectChange();"></td>
				</tr>
				
				 <tr>
					<center><table width=100% id=header_footer_tbl border=0 cellpadding="4" cellspacing="0" class="ProfileNewUI msgBdySelect">
						<tr>
							<td width=18%><font class=afontstyle>Message Body:</font></td>						
							<td><select id="mce_editor_0_header" name="mce_editor_0_header" onChange="updateHeader(this.value);"></select>&nbsp;<a href=javascript:manHeader() class="contentlnk_template">Manage&nbsp;Headers</a></td>
							<td width=4%><font class=afontstyle>&nbsp;</font></td>
							<td><select id="mce_editor_0_footer" name="mce_editor_0_footer" onChange="updateFooter(this.value);"></select>&nbsp;<a href=javascript:manFooter() class="contentlnk_template">Manage&nbsp;Signatures</a></td>
						</tr>
					</table></center>
				</tr>
				
				<tr><td height="10"></td></tr>
				<tr>
					<td width="100%" colspan="2" valign="baseline" style="padding:0px 10px">
						<!--if checkbox selected (default block)-->
						<div id="disp" style="display:block;">
							<div id="ShowPreview" style="display:block;">				
							</div>
						</div>
						<!--if checkbox selected (default block)-->
						<!--if checkbox deselected-->
						<div id="disp_s" style="display:none">
							<table border="0" cellspacing="0" cellpadding="0" width="100%">
								<tr>
									<td height="25" valign="top">
										<input type="checkbox" style="display:none;" name="cand_prof1" id="cand_prof1" unchecked>&nbsp;&nbsp;&nbsp;&nbsp;
										<a id="cand_prof1" class="prof-edit-link" href="#" onClick="check_second()" title="Show Profile Editor">Show Profile Editor</a>
									</td>
								</tr>
								<tr>
									<td><div class="line">&nbsp;</div></td>
								</tr>
							</table>
						</div>
						<!--if checkbox deselected-->
						<div id="newcompose" style="display:block;">
							<textarea id="matter2" name="matter2"  style="width:100%; height:200px; display:block;" value="hi hello" rows="60" tabindex="5" onBlur="setvalue(0)"  onMouseOut="setvalue(0)" onMouseOver="setvalue(1)" onfocus='setvalue(1)' ><?php echo html_tls_specialchars(stripslashes($email_body));?></textarea>
						</div>
					</td>
				</tr>
			
			</table>
			</td>
		</tr>
	</table>
</td>
</tr>
</table>
<?php
if($req_stat=="")
{
	?>
	<input type=hidden id=matter3 name=matter3 value="<?php echo dispfdb($table1).$table2;?>">
	<input type=hidden name=table1 value="<?php echo dispfdb($table1);?>">
	<?php
}
else
{
	?>
	<input type=hidden id=matter3 name=matter3 value='<?php echo "<div>".str_replace("<a href=","<abc ",str_replace("<input type=hidden ","<",str_replace("<a href=javascript:win","<",$table1)))."<BR>".str_replace("<a href=","<abc ",str_replace("<input type=hidden ","<",str_replace("<a href=javascript:win","<",$table2)))."</div><br>";?>'>
	<input type=hidden name=table1 value='<?php echo str_replace("<a href=","<abc ",str_replace("<input type=hidden ","<",str_replace("<a href=javascript:win","<",$table1)));?>'>
	<?php
}
?>
<input type="hidden" name="nofooter" id="nofooter">
<input type="hidden" name="noheader" id="noheader">
<input type="hidden" name="email_signature_id" id="email_signature_id" value="<?php echo $cust_temp_signature_id;?>">
<input type="hidden" name="email_header_id" id="email_header_id" value="<?php echo $cust_temp_header_id;?>">

<input type="hidden" name="empID" id="empID" value="<?php echo $empID;?>">

</form>
<script>

<?php
if($dbemailsetup!="no" && OLPVERSION<2)
{
	?>
	if(document.compose.from)
	{
		var cFrom = document.compose.from;
		for(ii = 0 ; ii < cFrom.options.length; ii++)
		{
			if(cFrom.options[ii].value == "<?php echo $fromid; ?>")
			{
				cFrom.options.selectedIndex = ii;
				break;
			}
		}
	}
	<?php
}
?>

var first_exe='Yes';
var once_exe='Yes';
var chkInstance = "N";
var ScreenHeight=screen.height;

document.getElementById("matter2").style.height=(ScreenHeight-400)+"px";

if(chkInstance=="Y") //Adding header and signature to template
{ 
	document.getElementById('newcompose').style.display="none";
	document.getElementById('preview').style.display="inline-block";
	once_exe="No";
	document.body.style.overflow = "auto";
	document.getElementById("editor_check").value=1;
} 
else if(chkInstance=="N") 
{
	document.getElementById('disp').style.display="none";
	document.getElementById('cand_prof1').checked=false;
	document.getElementById("matter3_data").value=document.compose.matter3.value;
	if((document.getElementById("head").value!="<table width=650><tr><td><font class=bstrip>&nbsp;</font></td></tr></table>" && document.getElementById("foot").value!="") || (document.getElementById("head").value!="" && document.getElementById("foot").value!=""))
		document.compose.matter3.value="<span id='head'><span id='header'>"+document.getElementById("head").value+"</span></span><br><br><span id='foot'><span id='footer'>"+document.getElementById("foot").value+"</span></span>";
	else if((document.getElementById("head").value=="<table width=650><tr><td><font class=bstrip>&nbsp;</font></td></tr></table>" && document.getElementById("foot").value!="") || (document.getElementById("head").value=="" && document.getElementById("foot").value!="")) 
		document.compose.matter3.value="<span id='foot'><span id='footer'>"+document.getElementById("foot").value+"</span></span>";
	else if(document.getElementById("head").value!="" && document.getElementById("foot").value=="")
		document.compose.matter3.value="<span id='head'><span id='header'>"+document.getElementById("head").value+"</span></span>";
	else if((document.getElementById("head").value=="<table width=650><tr><td><font class=bstrip>&nbsp;</font></td></tr></table>" && document.getElementById("foot").value=="") || (document.getElementById("head").value=="" && document.getElementById("foot").value==""))
		document.compose.matter3.value="<span id='head'></span><br><br><span id='foot'></span>";		

	if(document.getElementById('newcompose').style.display=='none' && document.getElementById('newcompose').style.display!="block")
		document.getElementById('newcompose').style.display="block";			
	document.getElementById("editor_check").value=0;	
}
</script>
</body>
</html>