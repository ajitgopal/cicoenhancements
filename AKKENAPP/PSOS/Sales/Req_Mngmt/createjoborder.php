<?php
/*
*
*
Modified Date: September 29, 2015.
Modified By  : Rajesh kumar V.
Purpose		 : Added select2 jquery libraries and css files for providing search functionality to the job locations select list.
Task Id		 : #800717 [IMI -Multiple Locations]
*
*
*/
    require("global.inc");
    require("dispfunc.php");
    require("functions.php");
    require("displayoptions.php");
    require_once($akken_psos_include_path.'commonfuns.inc');
    require_once("multipleRatesClass.php");
    require("Menu.inc");
    require_once("../../Admin/Manage_Questionnaire/custom_fields_functions.php");
    $menu=new EmpMenu();
    $displaytypes = $objMRT->displayPayType();
    $burden_status = getBurdenStatus();
	/* Including common shift schedule class file */
	require_once('shift_schedule/class.schedules.php');
	$objSchSchedules	= new Schedules(); //Creating object for the included class file

	$addr=$addr1;
	$candrn = strtotime("now");
	$ratesObj = new multiplerates();
	$temp_addr=$addr;

	$typeOfManage="jostatus','jostage','jocategory','jotype','prefix','suffix','contacttype','category','compsource','department','comptype','compstatus','joindustry";
	$manage_table_values=getManageTypes();

	//_do_Clear_Session();   // To clear All Session varaibles
	session_unregister("Jobloc_Comp_Divisions");
	session_unregister("Comp_Divisions");
	$Comp_Divisions='';
	$Jobloc_Comp_Divisions='';

	if($neworder=="yes")
	{
		session_unregister("CRM_Joborder_Page2");
		session_unregister("page3");
		session_unregister("page5");
		session_unregister("page7");
		session_unregister("page6");
		session_unregister("listpage_ses");
	}

	/**/
	$compoppr_id = '';
	if(isset($job_oprid))
	{
		$compoppr_id = $job_oprid;
		if($job_oprid > 0)
		{
			$oppr_ser_sql = "select jo_type,dept_id,jo_title from oppr_services where status='N' and oppr_id='".$job_oprid."' and jo_type!=0";
			$oppr_ser_res = mysql_query($oppr_ser_sql,$db);			
			$opr_result   = mysql_fetch_row($oppr_ser_res);
			$opr_jo_type  = $opr_result[0];
			$opr_dept_id  = $opr_result[1];
			$opr_jo_title  = $opr_result[2];
		}
	}

	/**/

	$ContryOptions=getcountryNames(0);
	$que1="select sno,name from manage where type='jostage' and  parent=0  order by name";
	$res1=mysql_query($que1,$db) or die(mysql_error());
	while($dd1=mysql_fetch_row($res1))
	{
		if(getSel("New",$dd1[1]))
			$unfilled=$dd1[0];
	}
	$DispTimes=display_SelectBox_Times();

	$msg="";

	//Query to get the employees
	$que="SELECT e.username as username, e.name as name FROM emp_list e LEFT JOIN hrcon_compen h ON (h.username = e.username) LEFT JOIN manage m ON (m.sno = h.emptype) WHERE e.lstatus != 'DA' AND e.lstatus != 'INACTIVE' AND e.empterminated !='Y' AND h.ustatus = 'active' AND m.type = 'jotype' AND m.status='Y' AND m.name IN ('Internal Direct', 'Internal Temp/Contract') AND h.job_type <> 'Y' ORDER BY e.name";
    //$que="select username,name from users where users.status!='DA' and users.type in ('sp','PE','subcon','consultant') order by users.name";
    $res=mysql_query($que,$db);

	$padding_style = "style='padding-left:271px;'";
	if(strpos($_SERVER['HTTP_USER_AGENT'],'Gecko') > -1)
	{
		$rightflt = "style= 'width:67px;'";
		$inner_rightflt = "style= 'width:35px;'";
		$style_table = "style='padding:0px; margin:0px;'";
		$padding_style = "style='padding-left:210px;'";
	}

	/* Start for auto populating the company details for job order from Company screen */
	$onbodyload = "";
	if($frompage == 'client' )
	{		
		$res1 = getCRMClientInfo($username,$db);
		$num_rows1 = mysql_num_rows($res1);
		
		if($num_rows1 == 0)
		{
			$que2 = "SELECT staffacc_cinfo.sno, staffacc_cinfo.username FROM staffacc_contact, staffacc_contactacc, staffacc_cinfo 
WHERE staffacc_contact.status='ER' AND staffacc_contact.csno=staffacc_cinfo.sno AND staffacc_contact.sno=staffacc_contactacc.`con_id`
AND staffacc_contactacc.`username` = ".$username;
			$res2=mysql_query($que2,$db);
			$num_rows2 = mysql_num_rows($res2);
			
			if($num_rows2 > 0)
			{
				$dd2 = mysql_fetch_array($res2);
				addCRMCompanies($dd2[1],$username,$db);
				$res1 = getCRMClientInfo($username,$db);
			}
		}
		$dd1=mysql_fetch_array($res1);
		
		$company_info = $dd1;
		$contid = $dd1['contid'];
		
		$que1 = "SELECT crmcomp.sno,crmcomp.cname,crmcomp.address1,crmcomp.address2,crmcomp.city,crmcomp.state,crmcomp.curl,crmcomp.phone ,
			crmcomp.country , crmcomp.zip ,crmcomp.ticker,crmcomp.department ,crmcomp.keytech ,crmcomp.industry, crmcomp.ctype ,crmcomp.fax , 
			crmcomp.csize,crmcomp.nloction ,crmcomp.nbyears , crmcomp.nemployee ,crmcomp.com_revenue ,crmcomp.federalid ,crmcomp.siccode ,
			crmcomp.csource, staffacc_cinfo.username
			FROM staffacc_cinfo,staffacc_contact,staffacc_contactacc,users, staffoppr_cinfo AS crmcomp
			WHERE staffacc_cinfo.username=staffacc_contact.username 
			AND staffacc_contactacc.status='ACTIVE' AND staffacc_contact.sno=staffacc_contactacc.con_id 
			AND staffacc_contactacc.username=users.username AND users.`username` = ".$username."
			AND crmcomp.sno = staffacc_cinfo.`crm_comp`";
		$res1=mysql_query($que1,$db);
		$num_rows = mysql_num_rows($res1); 
		
		if($num_rows > 0) 
		 {
		   $dd1=mysql_fetch_row($res1); 
		   $pass_var = $dd1[2]." ".$dd1[3]." ".$dd1[4]."."; 
		   $pass_newvar = $dd1[1]." ".$pass_var;
			//sending addr1,addr2,city,state,country,zip,phone so split themm and get what ever needed
		   $compaddr= settype($dd1[2], "string")."^^CompSplit^^".$dd1[3]."^^CompSplit^^".$dd1[4]."^^CompSplit^^".$dd1[5]."^^CompSplit^^".$dd1[8]."^^CompSplit^^".$dd1[9]."^^CompSplit^^".$dd1[7];   
		   $fcomp = 'jobcompany';
		   $comp_name=str_replace('"','|Akkendbquote|',$dd1[1]);
		   $addr_comp=str_replace('"','|Akkendbquote|',$compaddr);
		   $compname=str_replace("'",'|Akkensiquote|',$comp_name);
		   $addrcomp=str_replace("'",'|Akkensiquote|',$addr_comp);
		   $sel_count="SELECT count(*) FROM staffoppr_contact WHERE staffoppr_contact.csno='".$dd1[0]."'";
		   $res_count=mysql_query($sel_count,$db);
		   $fetch_count=mysql_fetch_row($res_count);
			$onBodyLoad ="onload=\"alertPopup1('".$dd1[0]."','".$fcomp."','".html_tls_specialchars(addslashes($compname))."','".html_tls_specialchars(addslashes($addrcomp))."','".$fetch_count[0]."')\"";
		 }
	 }/* END */
	/* Start for auto populating the company details for job order from Company screen */
	if($frompage == 'company' ){
		$que1 = "select sno,cname,address1,address2,city,state,curl,phone ,country , zip ,ticker,department ,keytech ,industry, ctype ,fax , csize,nloction ,nbyears , nemployee ,com_revenue ,federalid ,siccode ,csource,ts_layout_pref from staffoppr_cinfo where sno=".$compid;
		$res1=mysql_query($que1,$db);
		$num_rows = mysql_num_rows($res1); 
		
		if($num_rows > 0) 
		 {
		   $dd1=mysql_fetch_row($res1); 
		   $fetch_outside_ts_layout_pref = $dd1[24];
		   $pass_var = $dd1[2]." ".$dd1[3]." ".$dd1[4]."."; 
		   $pass_newvar = $dd1[1]." ".$pass_var;
			//sending addr1,addr2,city,state,country,zip,phone so split themm and get what ever needed
		   $compaddr= settype($dd1[2], "string")."^^CompSplit^^".$dd1[3]."^^CompSplit^^".$dd1[4]."^^CompSplit^^".$dd1[5]."^^CompSplit^^".$dd1[8]."^^CompSplit^^".$dd1[9]."^^CompSplit^^".$dd1[7];   
		   $fcomp = 'jobcompany';
		   $comp_name=str_replace('"','|Akkendbquote|',$dd1[1]);
		   $addr_comp=str_replace('"','|Akkendbquote|',$compaddr);
		   $compname=str_replace("'",'|Akkensiquote|',$comp_name);
		   $addrcomp=str_replace("'",'|Akkensiquote|',$addr_comp);
		   $sel_count="SELECT count(*) FROM staffoppr_contact WHERE staffoppr_contact.csno='".$dd1[0]."'";
		   $res_count=mysql_query($sel_count,$db);
		   $fetch_count=mysql_fetch_row($res_count);
			$onBodyLoad ="onload=\"alertPopup1('".$dd1[0]."','".$fcomp."','".html_tls_specialchars(addslashes($compname))."','".html_tls_specialchars(addslashes($addrcomp))."','".$fetch_count[0]."')\"";
		 }
	 }/* END */
	 /* Start for auto populating the contact details for job order from Contact screen */
	 else if($frompage == 'contact')   
	 { 
		$que1="select CONCAT('staffoppr-',staffoppr_contact.sno),TRIM(CONCAT_WS(' ',staffoppr_contact.fname,staffoppr_contact.mname,staffoppr_contact.lname)) as names,CONCAT_WS(' ',staffoppr_cinfo.address1,staffoppr_cinfo.city,staffoppr_cinfo.state) ,staffoppr_contact.csno,staffoppr_cinfo.cname as cnames,IF(staffoppr_contact.nickname = '',staffoppr_contact.email, CONCAT(staffoppr_contact.nickname,'(',staffoppr_contact.email,')') ), staffoppr_contact.status  from staffoppr_contact LEFT JOIN staffoppr_cinfo ON staffoppr_contact.csno=staffoppr_cinfo.sno where staffoppr_contact.status='ER' and staffoppr_contact.crmcontact='Y' and staffoppr_contact.sno=".$contid;
		$res1=mysql_query($que1,$db);
		$num_rows = mysql_num_rows($res1);
		if($num_rows > 0) 
		{		
			$dd1=mysql_fetch_row($res1);
			
			if(empty($dd1[3]))
			{
				$pass_var = $dd1[2]; 
				$pass_newvar = $dd1[1]." ".$pass_var;
				$fcomp = 'refcontact'; 
				$con_name=str_replace('"','|Akkendbquote|',$dd1[1]);
				$addr_val=str_replace('"','|Akkendbquote|',$dd1[2]);
				$contname=str_replace("'",'|Akkensiquote|',$con_name);
				$addrval=str_replace("'",'|Akkensiquote|',$addr_val);
				$CompName=str_replace('"','|Akkendbquote|',$dd1[4]);
				$CompName=str_replace("'",'|Akkensiquote|',$CompName);	
				$dd1[4] = dispfdb($dd1[4]);
				$conemail = $dd1[5];					
						
				$onBodyLoad = "onload=\"alertPopup('".$dd1[0]."','".$fcomp."','".html_tls_specialchars(addslashes($contname))."','".$dd1[3]."','','".html_tls_specialchars(addslashes($CompName))."');";
				$onBodyLoad .= "info_pass('".$contid."','".html_tls_specialchars(addslashes($contname))."','".$dd1[3]."','".$dd1[2]."','".$dd1[6]."','".html_tls_specialchars(addslashes($CompName))."','jrt');";
				$onBodyLoad .= "info_pass('".$contid."','".html_tls_specialchars(addslashes($contname))."','".$dd1[3]."','".$dd1[2]."','".$dd1[6]."','".html_tls_specialchars(addslashes($CompName))."','bill');\"";
				
				//echo $onBodyLoad;
			}
			else
			{
				$que1 = "select sno,cname,address1,address2,city,state,curl,phone ,country , zip ,ticker,department ,keytech ,industry, ctype ,fax , csize,nloction ,nbyears , nemployee ,com_revenue ,federalid ,siccode ,csource from staffoppr_cinfo where sno=".$dd1[3];
				$res1=mysql_query($que1,$db);
				$num_rows = mysql_num_rows($res1); 
				
				if($num_rows > 0) 
				{
				   $dd1=mysql_fetch_row($res1); 
				   $pass_var = $dd1[2]." ".$dd1[3]." ".$dd1[4]."."; 
				   $pass_newvar = $dd1[1]." ".$pass_var;
					//sending addr1,addr2,city,state,country,zip,phone so split themm and get what ever needed
				   $compaddr= settype($dd1[2], "string")."^^CompSplit^^".$dd1[3]."^^CompSplit^^".$dd1[4]."^^CompSplit^^".$dd1[5]."^^CompSplit^^".$dd1[8]."^^CompSplit^^".$dd1[9]."^^CompSplit^^".$dd1[7];   
				   $fcomp = 'jobcompany';
				   $comp_name=str_replace('"','|Akkendbquote|',$dd1[1]);
				   $addr_comp=str_replace('"','|Akkendbquote|',$compaddr);
				   $compname=str_replace("'",'|Akkensiquote|',$comp_name);
				   $addrcomp=str_replace("'",'|Akkensiquote|',$addr_comp);
				   $sel_count="SELECT count(*) FROM staffoppr_contact WHERE staffoppr_contact.csno='".$dd1[0]."'";
				   $res_count=mysql_query($sel_count,$db);
				   $fetch_count=mysql_fetch_row($res_count);
					$onBodyLoad ="onload=\"alertPopup1('".$dd1[0]."','".$fcomp."','".html_tls_specialchars(addslashes($compname))."','".html_tls_specialchars(addslashes($addrcomp))."','".$fetch_count[0]."')\"";
				}
			}
		}
	 }
	/* END */
	//Start code for populating Comission roles
	$strDirectInternal = "'BR', 'PR', 'MN', 'MP'"; //Direct or Internal Direct
	$strConTempContact = "'RR'"; //Internal Temp or Temp/Contract
	$strConTempToDirect = "''"; //Temp Contact to Direct

	if($strCon != "")
		$condition = " rp.commissionType NOT IN (".$strCon.") OR ";
		
		
$queryRoles ="SELECT sno,roletitle  FROM company_commission WHERE status ='active' ORDER BY roletitle,commission_default"; 


	$queryDirectInternal = $queryRoles;
	$resDirectInternal  = mysql_query($queryDirectInternal,$db);
	$lstDirectInternal = $lstTempContact = $lstTempToDirect = "";
	while($rowDirectInternal = mysql_fetch_row($resDirectInternal))
	{
		if($lstDirectInternal == '')
			$lstDirectInternal = $rowDirectInternal[0]."^".$rowDirectInternal[1];
		else
			$lstDirectInternal .= "|Akkensplit|".$rowDirectInternal[0]."^".$rowDirectInternal[1];
	}
	
	////---Added Functionality To Auto Select Worker Comp Code When Burdern is selected---///
	$sts_sel = "SELECT autoset_workercomp,payburden_required,billburden_required FROM burden_management";
	$sts_res = mysql_query($sts_sel, $db);
	$sts_rec = mysql_fetch_array($sts_res);
	$autowcc_status = $sts_rec['autoset_workercomp'];
	$payburden_status = $sts_rec['payburden_required'];
	$billburden_status = $sts_rec['billburden_required'];	
	////---End of Code---///
	
	
	//End code for populating Comission roles
	
$reqAliasArr = array('Job Type'=>'jobtype', 'Job Title'=>'jobtitle', 'No. Positions'=>'pos', 'Company'=>'company', 'Pay Rate'=>'comm_payrate', 'Bill Rate'=>'comm_billrate', 'Placement Fee'=>'pfee', 'Start Date'=>'smonth|sday|syear', 'Expected End Date'=>'emonth|eday|eyear', 'Position Summary'=>'posdesc', 'Salary'=>'amount_val', 'Job Location'=>'jrt_loc', 'Status'=>'status', 'Category'=>'jobcat', 'Contact'=>'contact', 'Job Reports To'=>'jrtcontact_sno', 'HRM Department'=>'deptjoborder', 'Industry'=>'joindustryid','Stage'=>'stage','Description Requirement' => 'requirements','Billing Address'=>'billcompany_sno','Billing Contact'=>'billcontact_sno');
if($frompage == 'client')
	$getRequiredSql = "SELECT column_name, element_required, element_alias FROM udv_grid_columns WHERE custom_form_modules_id = 4 AND element_required = 'yes' AND element_alias NOT IN ('Pay Rate', 'Bill Rate', 'Placement Fee', 'Salary')";
else
	$getRequiredSql = "SELECT column_name, element_required, element_alias FROM udv_grid_columns WHERE custom_form_modules_id = 4 AND element_required = 'yes'";
$getRequiredResult = mysql_query($getRequiredSql);
/* TLS-01202018 */
$userFieldsArr = array();
$reqArr = array();
while($getRequiredRow = mysql_fetch_assoc($getRequiredResult))
{
	$userFieldsArr[] = $getRequiredRow[element_alias];
	$userFieldsAlias[$getRequiredRow[element_alias]] = $getRequiredRow[element_alias];
	if($reqAliasArr[$getRequiredRow[element_alias]] != '')
	{
		$reqArr[] = $reqAliasArr[$getRequiredRow[element_alias]];
	}	
}
$reqArrStr = implode(",", $reqArr);

function getRequiredStar($field, $fieldsArr, $id=null)
{
	$str = "";
	if(in_array($field, $fieldsArr))
	{
		$str = "<span id='udr_".$id."'><font class=sfontstyle>&nbsp;*</font></span>";
	}
	return $str;
}

$get_bt_list_sql	= "SELECT  bt.sno, bt.burden_type_name, bt.ratetype FROM burden_types bt WHERE bt.bt_status = 'Active'";
$get_bt_list_rs		= mysql_query($get_bt_list_sql, $db);
$arr_burden_type	= array();

while ($row = mysql_fetch_object($get_bt_list_rs)) {

	$arr_burden_type[$row->sno]['burden_type']	= $row->burden_type_name;
	$arr_burden_type[$row->sno]['rate_type']	= $row->ratetype;
}

//StartDate, DueDate and ExpectedEndDate years, 20 years for past years and 10 years to future.
$startYear = $dueYear = $expectedEndYear = displayPastFutureYears();
?>
<?php include('header.inc.php') ?>
<title>New Job Order</title>
<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/candidatesInfo_tab.css">
<link href="/BSOS/css/grid.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/filter.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/site.css" type=text/css rel=stylesheet>
<link href="/BSOS/css/crm-editscreen.css" type=text/css rel=stylesheet>
<link type="text/css" rel="stylesheet" href="/BSOS/css/crm-summary.css">
<link rel="stylesheet" href="/BSOS/popupmessages/css/popup_message.css" media="screen" type="text/css">
<!-- <link type="text/css" rel="stylesheet" href="/BSOS/css/select2.css"> -->
<link type="text/css" rel="stylesheet" href="/BSOS/css/select2_loc.css">
<script type="text/javascript">var rate_calculator='<?php echo RATE_CALCULATOR;?>'</script>
<script type="text/javascript">var onLoadModeCheck = 'NoMode';</script>
<script type="text/javascript">var MarkupCheck = '';</script>
<script type="text/javascript">var refBonusManage = '<?php echo REFERRAL_BONUS_MANAGE;?>';</script>
<script language="JavaScript" src="/BSOS/scripts/common_ajax.js"></script>
<script language=javascript src="/BSOS/scripts/multiplerates.js"></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popupMsgArray.js"></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popup-message.js"></script>
<script language=javascript src=/BSOS/scripts/tabpane.js></script>
<script language=javascript src=/BSOS/scripts/validaterresume.js></script>
<script language=javascript src="scripts/validateact.js"></script>
<script language=javascript src="scripts/validatesup.js"></script>
<script language=javascript src="/BSOS/scripts/common.js"></script>
<script language=javascript src="/BSOS/scripts/dynamicElementCreatefun.js"></script>
<script language=javascript src="/BSOS/scripts/validatecheck.js"></script>
<script language=javascript src=scripts/validatenewsubmanage.js></script>
<script language=javascript src=/BSOS/scripts/commonact.js></script>
<script language=javascript src="/BSOS/scripts/manageCompanyRates.js"></script>
<?php 
// UOM rates 
$timeOptions = getNewRateTypes(""); ?>
<?php
if($frompage == 'client')
{
?>
<script language=javascript src=/BSOS/Client/Req_Mngmt/scripts/JoborderScreen.js></script>
<?php
}else{
?>
<script language=javascript src=scripts/JoborderScreen.js></script>
<?php
}
?>
<script language=javascript src=scripts/commission.js></script>
<script language=javascript src=scripts/joborder_ajax_resp.js></script>
<script language=javascript src=scripts/billrate.js></script>
<script language=javascript src="/BSOS/scripts/schedule.js"></script>
<script language=javascript src="/BSOS/scripts/calMarginMarkuprates.js"></script>
<script>var madison='<?=PAYROLL_PROCESS_BY_MADISON;?>'
var tricom_rep='<?=TRICOM_REPORTS;?>'
</script>
<script type="text/javascript" src="/BSOS/scripts/crmLocations.js"></script>
<?php getJQLibs(['jquery','jqueryUI']);?>

<!-- loads modalbox css -->
<link rel="stylesheet" type="text/css" media="all" href="/BSOS/css/shift_schedule/calschdule_modalbox.css" />

<!-- Perdiem Shift Scheduling -->
<link type="text/css" rel="stylesheet" href="/BSOS/css/perdiem_shift_sch/perdiemShifts.css">
<script type="text/javascript" src="/BSOS/scripts/perdiem_shift_sch/PerdiemShiftSch.js"></script>


<!-- loads some utilities (not needed for your developments) -->
<?php getCSSLibs(['jqueryUI','jqueryUITheme','jqueryUIStructure']);?>
<link rel="stylesheet" type="text/css" href="/BSOS/css/shift_schedule/schCalendar.css">

<!-- loads jquery ui -->
<script type="text/javascript" src="/BSOS/scripts/shift_schedule/schCal_timeframe.js"></script>

<!-- <script type="text/javascript" src="/BSOS/scripts/select2.js"></script> -->
<script type="text/javascript" src="/BSOS/scripts/RateCalculator.js"></script>
<script type="text/javascript" src="/BSOS/scripts/AkkuBi/jquery.slimscroll.js"></script>
<link type="text/css" rel="stylesheet" href="/BSOS/css/gigboard/select2_V_4.0.3.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/gigboard/gigboardCustom.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/select2_shift.css">
<script type="text/javascript" src="/BSOS/scripts/gigboard/select2_V_4.0.3.js"></script>
<script type="text/javascript" src="/BSOS/Admin/Manage_Questionnaire/scripts/jobQuestionnaire.js"></script>
<script language="javascript">
// Shift Color Codes Dropdown script starts here
$(document).ready(function() {
	  function formatColor(shift){
	  	if(shift.title!='')
	  	{
	  		var $shift = $(
		  '<span style="background-color: '+shift.title+'" class="color-label"></span><span>' + shift.text + '</span>'
			);
			return $shift;
	  	}
	  	else
	  	{
	  		var $shift = $('<span>' + shift.text + '</span>');
			return $shift;
	  	}
		
	  };
	 
	  $('#new_shift_name').select2({
		width: "150px",
		placeholder: "Select Shift",
		minimumResultsForSearch: -1,
		templateResult: formatColor,
		templateSelection: formatColor
	  });  
 });
// Shift Color Codes Dropdown script ends here

function getManagedSkills()
{
	var v_width  = 670;
	isOpera = !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0;
	if(isOpera) 
    {
        var v_heigth = 580;
    }
	else
	{
		var v_heigth = 520;
	}
	
	var top1=(window.screen.availHeight-v_heigth)/2;
	var left1=(window.screen.availWidth-v_width)/2;
	
	selectedskillsids = document.getElementById("selectedskillsids").value;
	selecteddeptids = document.getElementById("skilldeptids").value;
	selectedcatgryids = document.getElementById("skillcatgryids").value;
	selectedspltyids = document.getElementById("skillspltyids").value;
	var parms = "width="+v_width+"px,";
	parms += "height="+v_heigth+"px,";
	parms += "left="+left1+"px,";
	parms += "top="+top1+"px,";
	parms += "statusbar=no,";
	parms += "menubar=no,";
	parms += "scrollbars=yes,";		
	parms += "dependent=yes,";
	parms += "resizable=yes";
		
	top.remattachfiles_cand=window.open("/PSOS/Marketing/Candidates/skill_selector.php?from=createjoborder&selected_skills="+selectedskillsids+"&tag=joborder&skilldeptids="+selecteddeptids+"&skillcatgryids="+selectedcatgryids+"&skillspltyids="+selectedspltyids,"addskill",parms);	
	top.remattachfiles_cand.focus();
}

function removeJobOrderSkills(trId,skillid)
{
	if (typeof skillid == 'undefined') {
		skillid ="";
	}
	var row = document.getElementById(trId);
	var table = row.parentNode;
	while ( table && table.tagName != 'TABLE' )
		table = table.parentNode;
	if ( !table )
		return;
	table.deleteRow(row.rowIndex);
	if (skillid !="undefined" && skillid !="") {
		var skillids = document.getElementById("selectedskillsids").value;
		var Skillidarray = skillids.split(',');
		for(var i = 0; i < Skillidarray.length; i++)
		{
		   console.log(Skillidarray[i]);
		   if (Skillidarray[i] == skillid) {
		   		Skillidarray.splice(i, 1);
		   };
		}
		document.getElementById("selectedskillsids").value=Skillidarray;
	}
}
function ismaxlength(obj,MaxLen){
	 var mlength = MaxLen;
	if (obj.getAttribute && obj.value.length>mlength)
		obj.value=obj.value.substring(0,mlength)
}
var questionnaire_itm_enabled = "<?php echo JOB_QUESTIONNAIRE_ENABLED; ?>";
 if(questionnaire_itm_enabled=='Y'){
$(document).ready(function () {
            $(function () {
                $('#customfieldsscroll').slimscroll({
                    width: '400px%',
                    height: 'auto',
                    disableFadeOut: false,
                    railVisible: true,
                    size: '6px',
                    color: '#2f2f2f',
                    railColor: '#2f2f2f',
                    railOpacity: 0.2
                }).parent().css({
                    'float': 'left'
                });
            });
       
            
        var isSelectQuestionExists = document.getElementsByClassName('selectSingleQue');
        if (isSelectQuestionExists.length > 0) {
            $(".selectSingleQue").select2();
        }
            $(".questionnaireGroup").select2();
            $('.question').click(function () {

                if ($(this).next().is(':hidden') != true) {
                    $(this).removeClass('active');
                    $(this).next().slideUp("normal");
                } else {
                    $('.question').removeClass('active');
                    $('.answer').slideUp('normal');
                    if ($(this).next().is(':hidden') == true) {
                        $(this).addClass('active');
                        $(this).next().slideDown('normal');
                    }
                }
            });

            $('.answer').hide();

            $('.expand').click(function (event)
            {
                $('.question').next().slideDown('normal');
                {
                    $('.question').addClass('active');
                }
            });

            $('.collapse').click(function (event)
            {
                $('.question').next().slideUp('normal');
                {
                    $('.question').removeClass('active');
                }
            });
        var isMultiQuestionExists = document.getElementsByClassName('multiquestion');
        if (isMultiQuestionExists.length > 0) {
            $(".multiquestion").select2({
                multiple: true
            });
        }

            });
}
</script>
<script src="/BSOS/tinymce/jscripts/tiny_mce/tiny_mce_src.js">
	disabled();
	</script>
<script language="javascript" type="text/javascript">
tinyMCE.init({
		theme : "advanced",
		mode : "exact",
		elements : "posdesc,requirements",
		default_font_name : "Arial",
		default_font_size : "10pt",
		default_font_color : "black",
		default_font_styles : "margin: 0px; font-family: Arial; font-size: 10pt; color: black",
		plugins : "autolink,lists,layer,advimage,advlink,iespell,inlinepopups,searchreplace,print,paste,directionality,noneditable,visualchars,nonbreaking,xhtmlxtras,advlist",
		height:"300px"
	});
</script>
<style>
.jocomp-back .crmsummary-jocomp-table select[name="workcode"]:focus{
    box-shadow: 0px 0px 3px 0px #3eb8f0;
	border: 1px solid #3eb8f0 !important
}

.select2-container.select2-container-multi.required.selCdfCheckVal{width: 250px !important;}
.summarytext input[type="checkbox"]{margin: 5px 2px !important;}
.timegrid{ width:2.06% !important; display:block; overflow:hidden}

.panel-table-content-new .summaryform-bold-title { font-size:12px; font-weight:bold;}
.crmsummary-jocomp-table input[name="jobtitle"],.crmsummary-jocomp-table input[name="refcode"], .crmsummary-jocomp-table select[name="workcode"],.crmsummary-jocomp-table input[name="po_num"],.crmsummary-jocomp-table input[name="joborder_dept"],
.crmsummary-edit-table input[name="refcode"],.crmsummary-jocomp-table select[name="workcode"]{width:250px !important;}
#attribute-selector .scroll-area {width: 1000px;}

<?php
if($contsum==true && $frompage=='contact'){?>
/* #modal-wrapper{height: 240px; margin-left: -170px; margin-top: -120px; width:800px;left:205px !important;} */
<?php }else{?>
/* #modal-wrapper{height: 240px; margin-left: -170px; margin-top: -120px; width:800px;} */
<?php
}?> 
#modal-glow{ width:100%; position:fixed;}

.summaryform-formelement_chrome{
    margin-left:6px !important;
}
.billable_blockNew td{
 padding-right:0px !important;
padding-left:0px !important;
}
.billable_blockNew .summaryform_margin_left{
    padding-left:10px !important;
}

@media screen\0 {	
.summaryform-formelement{ height:18px; font-size:12px !important; }
a.crm-select-link:link{ font-size:12px !important; }
a.edit-list:link{ font-size:12px !important;}
.summaryform-bold-close-title{ font-size:12px !important;}
.center-body { text-align:left !important;}
.crmsummary-jocomp-table td{ font-size:12px !important ; text-align:left !important;}
.summaryform-nonboldsub-title{ font-size:12px !important;}
#smdatetable{ font-size:12px !important;}
.summaryform-formelement{ text-align:left !important; vertical-align:middle}
.crmsummary-content-title{ text-align:left !important}
.crmsummary-edit-table td{ text-align:left !important}
.summaryform-bold-title{ font-size:12px !important;}
.summaryform-nonboldsub-title{ font-size:12px !important;}


}

.ssremovepad table tr td{ pad ding-left:0px; pa dding-right:0px}
a.crm-select-linkNewBg:hover{color:#3AB0FF}
.custom_defined_main_table td{text-align: left !important; vertical-align: middle;}
.innertbltd-custom table td {vertical-align: middle !important;}
.custom_defined_head_td{width: 120px !important; padding:0px !important;}
.managesymb{ margin-top:7px; }
.closebtnstyle{ float:left; *margin-top:3px; vertical-align:middle; margin-top:2px; }
/* Added following styles by swetha on 08-16-16 */
#sel_perdiem{
     margin-left:0px !important;   
    }
    .payratetypeNew{ margin-left:0px; }
#payratetype{margin-left:0px;}
@media screen and (-ms-high-contrast: active), (-ms-high-contrast: none) {
 #readMoreShiftData{float: left !important; margin-left: 50% !important;}
}
.crmsummary-jocomp-table .fa-calculator:before{color: #138dc5;font-size:14px;}

.cdfJoborderBlk input[type="text"]{ width:250px;}
.cdfJoborderBlk textarea{ 
	resize: none; 
	width: 385px;
	border: 1px solid #ccc !important;
	border-radius: 3px !important;
	color: #474c4f !important;
	font-size: 12px !important;
	margin-left: 2px !important;
}
.cdfJoborderBlk .selCdfCheckVal{ 
	width:300px !important;
	
 }
 .cdfJoborderBlk .selCdfCheckVal .select2-search-choice div{ 
	font-size: 12px;
 }
 
 .select2-results .select2-result-label {
	font-size: 12px !important;
 }
 .JoAssignPerdiemEditModal-wrapper{position: fixed !important; width:450px !important; height: 265px !important; margin-left: -250px !important;}
.JoAssignPerdiemEditModal-wrapper .scroll-area{width: 440px !important;}

body.perdiemnoscroll {
    overflow: hidden;
}
 .dynamic-tab-pane-control.tab-pane input:focus, .dynamic-tab-pane-control.tab-pane select:focus{border: 1px solid #3eb8f0;box-shadow: 0 0 3px 0 #3eb8f0;}
 /*  Perdiem Shift Scheduling Model Box CSS  */
/* .JoPerdiemEditModal-wrapper{position: fixed !important; width:620px !important; height: 455px !important; margin-left: -320px !important; margin-top:-228px !important; top: 50% !important; }
.JoPerdiemEditModal-wrapper .scroll-area{width: 620px !important;} */

 </style>
    <?php if(JOB_QUESTIONNAIRE_ENABLED=='Y') { ?>     
        <style>
.question{background: #fff; padding-left:5px;cursor: pointer;font-weight:normal;line-height:26px; font-size:14px;}  
.answer{padding-top:0px;padding-bottom:10px;margin-left:27px;background: #fff;line-height:24px;}
.active { }
.questDynM{ font-size:13px; line-height:20px; background:#fff; border-radius:4px; padding:10px 5px;}
.questExpCollap{ padding-right:20px; text-align:right; margin-bottom:10px; }
.questExpCollap a{ font-weight:bold; font-size:12px; text-decoration:underline;}
.questExpCollap a:hover{ font-weight:bold; text-decoration:none; }
.questDynM .question i{transition:0.1s; margin:0px 5px; }
.questDynM .question.active{ font-weight:normal;}
.questDynM .question.active i{transform: rotate(90deg);}
#candStages{ padding:6px 4px; border:solid 1px #ccc; width:350px; border-radius:4px;}
.questDynM .select2Blk{ border:solid 1px #ccc; padding:5px; width:95%; border-radius:4px;}
.questHedNew{float:left; padding-left:10px; font-weight:bold; font-size:14px;}
.quesInputWid input[type="text"], .quesInputWid textarea, .quesInputWid select{ width:350px; padding:6px 4px; border:solid 1px #ccc; border-radius:4px;}
</style>
    <?php } ?>

</head>
<body <?php echo $onBodyLoad;?>>
<input type=hidden id="updskillsid" name="updskillsid" value="">
	<?php
		if($frompage == 'client')
			$action_url = '/BSOS/Client/Req_Mngmt/saveorder.php';
			else
				$action_url = 'saveorder.php';
		?>
	<form method=post name='conreg' id='conreg' action="<?=$action_url?>">
		<div class="modal-header">
			<?php
             $Header_name_strip=explode("|","fa fa-floppy-o~Save|fa fa-times~Close");
             $Header_links_strip=explode("|","javascript:savejoborder(this)|javascript:window.close()");

			 $Header_heading_strip="&nbsp;Job Order";
		     $menu->showHeadingStrip1($Header_name_strip,$Header_links_strip,$Header_heading_strip);
			?>
		</div>
		<!--All snos-->
<input type="hidden" id="compoppr_id" name="compoppr_id" value="<?php echo $compoppr_id; ?>" />
<input type=hidden name="Compuser" id="Compuser" value="<?php echo $dd1[24]?>">
<input type=hidden name="userreq" id="userreq" value="<?php echo $reqArrStr;?>">
<input type=hidden name="posid" id="posid" value="">
<input type=hidden name='summarypage' id='summarypage' value="">
<input type=hidden name="Compsno" id="Compsno" value="<?=$company_info['acc_comp']?>">
<input type=hidden name="crm_Compsno" id="crm_Compsno" value="<?=$company_info['csno']?>">
<input type=hidden name="contval" id="contval" value="">
<input type=hidden name="reptval" id="reptval" value="<?=$company_info['con_id']?>" >
<input type=hidden name="job_location" id="job_location" value="">
<input type=hidden name="bill_contact">
<input type=hidden name="bill_address">
<input type=hidden name="jobloc_bill_contact">
<input type=hidden name="jobloc_bill_address">
<!--All the other-->
<input type=hidden name='url' id='url' value="" />
<input type=hidden name='dest' id='dest' value="" />
<input type=hidden name=newskill>
<input type=hidden name=skills value="<?php echo dispfdb($skills);?>">
<input type=hidden name=clientsno>
<input type=hidden name=ldndis>
<input type=hidden name=sno_staff value="<?php echo $scli[1];?>">
<input type=hidden name=message1 value="<?php echo $message1;?>">
<input type=hidden name=stat>
<input type=hidden name=con_id value="<?php echo $con_id;?>">
<input type=hidden name=fdate>
<input type=hidden name=savestat>
<input type=hidden name=contsum value="<?php echo $contsum;?>">
<input type=hidden name=contid value="<?php echo $contid;?>">
<input type=hidden id="delskill" name="delskill" value="">
<input type=hidden name="jobwindowstatus" id="jobwindowstatus" value="new">
<input type=hidden name=conttoggle> 
<input type=hidden name=repttoggle>
<input type=hidden name=contemailstatus>
<input type=hidden name=reptemailstatus>
<input type=hidden name=BillInfo_Toggle >
<input type="hidden" name="CRM_Joborder_Page2" value="">
<input type="hidden" name="page2" value="">
<input type="hidden" name="amount" value="">
<input type="hidden" name="pamount" value="">
<input type="hidden" name="billr" value="">
<input type="hidden" name="payr" value="">
<input type="hidden" name="emplist" value="">
<!-- job order new and edit togle related-->
<input type=hidden name=binfotogle value="">
<input type=hidden name=descrtogle value="">
<input type=hidden name=hrptogle value="">
<input type=hidden name=questionnaireToggle value="">
<input type=hidden name=tandrtogle value="">
<input type=hidden name=Comp_Toggle value=''>	
<input type=hidden name=Jobloc_Toggle value=''>
<input type=hidden name="skillstogle" id="skillstogle" value="">
<input type=hidden name="schdtogle" id="schdtogle" value="">
<!-- job order new and edit unfilledlost related-->
<input type=hidden name=unfilled value="<?php echo $unfilled; ?>">
<input type=hidden name=unfillednotes value="">
<input type=hidden name=unfilleddata value="">
<input type=hidden name=contfrmrval>
<input type=hidden name=reptfrmrval>
<input type=hidden name="contactData">
<input type=hidden name="reporttoData">
<!--company and job order related-->
<input type=hidden name=parking_vals>
<input type=hidden name=prate_val>
<input type=hidden name=jobloc_parking_vals>
<input type=hidden name=jobloc_prate_val>
<input type=hidden name=divisions>	
<!--HIddens for alerts-->
<input type=hidden name=Contact_Alert value=''>
<input type=hidden name=Company_Alert value=''>
<input type=hidden name=BillContact_Alert value=''>
<input type=hidden name=BillAddress_Alert value=''>
<input type=hidden name=Report_Alert value=''>
<input type=hidden name=Job_Alert value=''>
<input type=hidden name=REPT_Alert value=''>
<!--For Relations-->
<input type=hidden name=Comp_Cont_Relation value=''>
<input type=hidden name=Report_Location_Relation value=''>
<!--hiddens for names -->
<input type=hidden name='Compname' id='Compname' value="">
<input type=hidden name='Con_Name' id='Cont_Name' value="">
<input type=hidden name='Report_Name' id='Report_Name' value="">
<input type=hidden name='Jobloc_Name' id='Jobloc_Name' value="">
<input type=hidden name='Billcomp_Name' id='Billcomp_Name' value="">
<input type=hidden name='Billcont_Name' id='Billcont_Name' value="">
<input type=hidden name='Jobloc_Billcomp_Name' id='Jobloc_Billcomp_Name' value="">
<input type=hidden name='Jobloc_Billcont_Name' id='Jobloc_Billcont_Name' value="">
<input type=hidden name=addurl_value value=''>
<input type=hidden name=contold_data value=''>
<input type=hidden name=reptold_data value=''>
<input type=hidden name=compold_data value=''>
<input type=hidden name=joblocold_data value=''>
<input type=hidden name=contnew_data value=''>
<input type=hidden name=reptnew_data value=''>
<input type=hidden name="Report_Category_Sno" id="Report_Category_Sno" value="<?php echo Report_Category_Sno;?>">
<input type=hidden name="Contact_Category_Sno" id="Contact_Category_Sno" value="<?php echo Contact_Category_Sno;?>">
<input type="hidden" name="chkCompAccess" id="chkCompAccess" value="<?php echo $chkCompAccess; ?>">
<input type="hidden" name="chkJobLocAccess" id="chkJobLocAccess" value="<?php echo $chkJobLocAccess; ?>">
<input type="hidden" name="chkContAccess" id="chkContAccess" value="<?php echo $chkContAccess; ?>">
<input type="hidden" name="chkReportToAccess" id="chkReportToAccess" value="<?php echo $chkReportToAccess; ?>">
<input type="hidden" name="selectcaseType" id="selectcaseType" value="">
<input type="hidden" name="newcaseType" id="newcaseType" value="">
<input type="hidden" name="mulRatesVal" id="mulRatesVal" value="">
<input type="hidden" name="hdnDefaultDeptID" id="hdnDefaultDeptID" value="<?php echo getDefaultHRMDepartment();?>">
<input type="hidden" name="newcaseReportTo" id="newcaseReportTo" value="">
<!--<input type="hidden" name="roleData" id="roleData" value="<?php echo html_tls_entities($lstDirectInternal."|^AKKEN^|".$lstTempContact."|^AKKEN^|".$lstTempToDirect,ENT_QUOTES); ?>"> -->
<input type="hidden" name="roleData" id="roleData" value="<?php echo html_tls_entities($lstDirectInternal,ENT_QUOTES); ?>">
<input type="hidden" name="hdnRoleCount" id="hdnRoleCount" value="">
<input type="hidden" name="hdnJobType" id="hdnJobType" value="">
<input type="hidden" name="hdnreportflag" id="hdnreportflag" value="">
<input type="hidden" name="frompage" id="frompage" value="<?php echo $frompage; ?>">
<input type="hidden" name="sm_form_data" id="sm_form_data" value="" />
<input type="hidden" name="neworder" id="neworder" value="<?php echo $neworder;?>" />
<input type="hidden" name="sm_enabled_option" id="sm_enabled_option" value="<?php echo SHIFT_SCHEDULING_ENABLED; ?>" />
<input type="hidden" name="mode_type" id="mode_type" value="joborder">
<input type="hidden" name="compcrfmstatus" id="compcrfmstatus" value="1">
<input type="hidden" name="contactcrfmstatus" id="contactcrfmstatus" value="1">
<input type="hidden" name="comploccrfmstatus" id="comploccrfmstatus" value="1">

<input type="hidden" name="skilldeptids" id="skilldeptids" value="" />
<input type="hidden" name="skillcatgryids" id="skillcatgryids" value="" />
<input type="hidden" name="skillspltyids" id="skillspltyids" value="" />

<input type="hidden" name="theraphySourceEnable" id="theraphySourceEnable" value="<?php echo THERAPY_SOURCE_ENABLED;?>" />

<!--hiddens for Shift Name/ Time -->
<input type="hidden" name="shift_time_from" id="shift_time_from" value="" />
<input type="hidden" name="shift_time_to" id="shift_time_to" value="" />

<input type="hidden" name="candrn" id="candrn" value="<?php echo $candrn;?>">

<input type="hidden" name="new_jo_shift_snos" id="new_jo_shift_snos" value="">

<div class="form-container">
	<?php
	if($frompage != 'client')
	{
	?>
		<fieldset>
			<legend class="card-title">Settings</legend>
			<div class="settings-back">
				<table>
					<tr>
						<td>
							<table class="align-middle">
								<tr>
									<td>
									   <span id="leftflt"><span class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Status']))?$userFieldsAlias['Status']:'Status';echo getRequiredStar('Status', $userFieldsArr);?></span></span>
									</td>
									<td>
										<select class="summaryform-formelement" name=status id=Status >
											<option value="">Select</option>
					<?php
					$que1="select sno,name from manage where type='jostatus' order by name";
					$res1=mysql_query($que1,$db) or die(mysql_error());
					while($dd1=mysql_fetch_row($res1))
						print "<option  value=".$dd1[0]." ".getSel("Open",$dd1[1]).">".$dd1[1]."</option>";
					?>
										</select>
										<?php if(EDITLIST_ACCESSFLAG){ ?>
										<a href="javascript:doManage('Status','status');" class="edit-list">edit list</a>
										<?php } ?>
				  </td>
				</tr>
				<tr>
					<td>
						<span id="leftflt"><span class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Stage']))?$userFieldsAlias['Stage']:'Stage:';echo getRequiredStar('Stage', $userFieldsArr);?></span></span></td>
					<td>
    				  <select name="stage" class="summaryform-formelement" id="stage" onChange="unfill()" style="width:150px">
						<option value="">--select--</option>
						<?php
						$que1="select sno,name from manage where type='jostage' and  parent=0  order by name";
						$res1=mysql_query($que1,$db) or die(mysql_error());
						while($dd1=mysql_fetch_row($res1))
							print "<option  value=".$dd1[0]." ".getSel("New",$dd1[1])." >".$dd1[1]."</option>";
						?>
						</select> <?php if(EDITLIST_ACCESSFLAG){ ?> 
						<a href="javascript:doManage('Job Stage','stage');" class="edit-list">edit list</a> <?php } ?>
					</td>
				</tr>	
				<tr>
					<td>    				
					<span id="leftflt"><span class="crmsummary-content-title">&nbsp;Share:</span></span></td>
					<td>
					<select class="summaryform-formelement" name="shareval" onchange=changeShare()>
					<option value="Public">Public</option>
					<option value="Private">Private</option>
					<option value="Share">Share</option>
					</select>					
				</td>
			</tr>				
				<tr>
				 <td>
				   <span id="leftflt"><span class="crmsummary-content-title">&nbsp;Owner:</span></span></td>
				 <td>						
				 <select class="summaryform-formelement" name="owner" style="width:200px" disabled="disabled">
      				<?php 
					
					//Query to display all the active users in alphabetical order
						$Users_Sql="select us.username,us.name,us.userid  from users us  LEFT JOIN sysuser su ON us.username = su.username  WHERE su.crm!='NO' AND us.status != 'DA' AND us.type in ('sp','PE')  AND us.name!='' ORDER BY us.name";
						$Users_Res=mysql_query($Users_Sql,$db);
					
						$Users_Array=array();
						while($Users_Data=mysql_fetch_row($Users_Res))
						{
						 $Users_Array[$Users_Data[0]]=$Users_Data[1];								
						}
						$User_nos=implode(",",array_keys($Users_Array));
						
						
						$uersCnt=count($Users_Array);
					foreach($Users_Array as $UserNo=>$uname)
					{?>
						<option value="<?php echo $UserNo;?>" <?=getSel($username,$UserNo)?>><?=html_tls_specialchars($uname,ENT_QUOTES)?></option>
					<? }?>
					
					?>
					</select>
					</td>
				</tr>					
				<tr>
					<td>
					<span id="leftflt"><span class="crmsummary-content-title">&nbsp;Source Type:</span></span></td>
					<td>
    				<select class="summaryform-formelement" name="jsourcetype" id="jsourcetype" style="width:150px">
    				<option value="">--select--</option>
					<?php
						$que1="select sno,name from manage where type='josourcetype' order by name";
						$res1=mysql_query($que1,$db) or die(mysql_error());
						while($dd1=mysql_fetch_row($res1))
							print "<option  value=".$dd1[0]." ".getSel("",$dd1[1]).">".$dd1[1]."</option>";
					?>
					</select> <?php if(EDITLIST_ACCESSFLAG){ ?> 
					<a href="javascript:doManage('JobSourcetype','jsourcetype');" class="edit-list">edit list</a> <?php } ?>
					</td>
				</tr>
			</table>
			</td>
					<?php 
						if(chkUserPref($crmpref,'23')) 
						{
							$website_style= '';
							$johotjob_style= '';
							
							//for hot job  marking displaying
							if(chkUserPref($crmpref,'42')) 
								$johotjob_style= '';
							else
								$johotjob_style=  "style='display:none'";
						}
						else
						{
							$website_style=  "style='display:none'";
							$johotjob_style=  "style='display:none'";
						}

						
					?>		
						<td valign="top">
						<div class="form-check form-check-inline" <?php echo  $website_style; ?>><input type="checkbox" name="chkwebsite" id="chkwebsite" class="form-check-input" onClick="validateJoPosting('posting','chkwebsite','chkhotjob');" >
							<label class="form-check-label">Post Job Order to Web Site</label>
						</div>
						<br/>
						<div class="form-check form-check-inline" <?php echo  $johotjob_style; ?>>
							<input type="checkbox" class="form-check-input" name="chkhotjob" id="chkhotjob" onClick="validateJoPosting('hotjob','chkwebsite','chkhotjob');">
							<label class="form-check-label">Mark as Hot Job</label>
						</div>
						
						
					</td>
				</tr>
			
			</table>
		</div>
	</fieldset>
	<?php
	}
	?>
		<table class="align-middle">
            <tr>
                <td align=center>
                <?php
                /**
                * Include the file to generate user defined fields.
                */
                if($frompage != 'client')
                {
                    $mod = 4;
                    include($app_inc_path."custom/getcustomfields.php");
                }
                ?>
                </td>
            </tr>
        </table>

		<fieldset>
			<legend><font class="afontstyle">Job Order Information</font></legend>
			<div class="form-back crmsummary-jocomp-table">
				<table class="crmsummary-edit-table align-middle">
					<tr id="jobtype-data" class="seljob-type" >
						<td class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Job Type']))?$userFieldsAlias['Job Type']:'Job Type';?> <font class=sfontstyle>*</font>
						</td>
                            <td>
                            <?php
                            if($frompage == 'client')
                            {
                            ?>
                                <select name="jobtype" id="jobtype"  onChange="Jobtype('')" class="summaryform-formelement">
                                <option value="">--select--</option>
                                <?php $sel_manage="SELECT name,sno FROM manage WHERE type='jotype' and name NOT IN('Internal Direct','Internal Temp/Contract') order by name";
                                $res_manage=mysql_query($sel_manage,$db);
                                while($fetch_manage=mysql_fetch_row($res_manage))
                                    echo "<option value='".$fetch_manage[1]."'>".$fetch_manage[0]."</option>";?>
                                </select>
                            <?php
                            }
                            else
                            {
                            ?>
                                    <select name="jobtype" id="jobtype"  onChange="Jobtype('')" class="summaryform-formelement">

                                    <option value="">--select--</option>
                                    <?php 
                                    if(isset($job_oprid) && isset($opr_jo_type))
                                    {
                                    	$jtyp = $opr_jo_type;
                                    }
                                    else
                                    {
                                    	$jtyp = '';
                                    }
                                    echo setManageTypes($manage_table_values["jotype"],$jtyp);
                                    ?>
                                    </select>
                            <?php
                            }
                            ?>
                             <span id="crm-joborder-formback-msg" class="seljob-font">&nbsp;&nbsp;<?php echo $msg;?></span>
                            </td>
                    </tr>
			<tr>
				<td class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['HRM Department']))?$userFieldsAlias['HRM Department']:'HRM Department';echo getRequiredStar('HRM Department', $userFieldsArr);?></td>
				<td align=left>
				<?php 
				if(isset($job_oprid) && isset($opr_dept_id))
                {
                	$hm_id = $opr_dept_id;
                }
                else
                {
                	$hm_id  = '';
                }

                if ($frompage == 'client') {
                	
                	$getDepartmentId = "SELECT staffacc_cinfo.sno, staffacc_cinfo.username,ca.deptid FROM staffacc_contact, staffacc_contactacc, staffacc_cinfo 
						LEFT JOIN Client_Accounts ca ON (ca.typeid=staffacc_cinfo.sno AND ca.status='active' AND ca.clienttype='CUST')
						WHERE staffacc_contact.status='ER' AND staffacc_contact.csno=staffacc_cinfo.sno AND staffacc_contact.sno=staffacc_contactacc.`con_id`
						AND staffacc_cinfo.acccompany = 'Y' AND staffacc_contactacc.`username` ='".$username."'";
					$resDepartmnetId = mysql_query($getDepartmentId,$db);
					$roeDepartmentId = mysql_fetch_array($resDepartmnetId);

					if ($hm_id == '') {
						$hm_id = $roeDepartmentId[2];
					}
                }

				departmentSelBox('deptjoborder', $hm_id, 'summaryform-formelement','','','','yes');
				?>
				</td>
			</tr>
			<tr>
				<td class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Industry']))?$userFieldsAlias['Industry']:'Industry';echo getRequiredStar('Industry', $userFieldsArr);?></td>
				<td align=left>
				<select name="joindustryid" id="joindustryid" class="summaryform-formelement">
				<option value="">--select--</option>
				<?php 
					echo setManageTypes($manage_table_values["joindustry"],'');
       			?>
				</select> <?php if(EDITLIST_ACCESSFLAG){ ?> 
				<a href="javascript:doManage('joindustry','joindustryid');" class="edit-list">edit list</a> <?php } ?>
				</td>
			</tr>
			<tr>
				<td class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Category']))?$userFieldsAlias['Category']:'Category';echo getRequiredStar('Category', $userFieldsArr);?></td>
				<td align=left>
				<select name="jobcat" id="jobcat" class="summaryform-formelement">
				<option value="">--select--</option>
				<?php
					echo setManageTypes($manage_table_values["jocategory"],'');
       			?>
				</select> <?php if(EDITLIST_ACCESSFLAG){ ?> 
				<a href="javascript:doManage('jocategory','jobcat');" class="edit-list">edit list</a> <?php } ?>				</td>
			</tr>
			<?php
			if(isset($job_oprid) && isset($opr_jo_title))
        	{
        		$jtitle = $opr_jo_title;
        	}
        	else
            {
            	$jtitle  = '';
            }

			/* Theraphy Source :: checking Theraphy Source is Enable or not  */
			if(THERAPY_SOURCE_ENABLED=="Y"){
				
			    if(JOBORDER_TITLES == 'TRUE')
			    {

			    ?>
			    <tr>
				    <td class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Job Title']))?$userFieldsAlias['Job Title']:'Job Title';echo getRequiredStar('Job Title', $userFieldsArr);?></td>
				    <input name="jobtitleid" id="jobtitleid"  type="hidden"  value="">
				    <td>
					<span id="jobtitlespan" class="afontstyle"><?php echo $jtitle; ?></span>
					<input name="jobtitle" id="jobtitle" class="summaryform-formelement" type="hidden" size=40 maxsize=150 maxlength=150 value="<?php echo $jtitle; ?>" readonly="readonly">
					<span id="jobtitlelinkspan">
						<a href="javascript:doSelectJTitles();" class="edit-list">Select Title</a>
					</span>
				    </td>
			    </tr>
			    <?php
			    }
			    else
			    {	
			     
				if($frompage=='client'){
				    ?>
				    <tr>
				    <td class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Job Title']))?$userFieldsAlias['Job Title']:'Job Title';echo getRequiredStar('Job Title', $userFieldsArr);?></td>
				    <td>
					<span id="jobtitlespan" class="afontstyle"></span>
					<input name="jobtitle" id="jobtitle" class="summaryform-formelement" type="hidden" size=40 maxsize=150 maxlength=150 value="" readonly>
					<span id="jobtitlelinkspan">
						<a href="javascript:doSelectJTitles();" class="edit-list">Select Title</a>
					</span>
				    </td>
			    </tr>
				    <?php    
				}else{
				    ?>
				    <tr>
				    <td class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Job Title']))?$userFieldsAlias['Job Title']:'Job Title';echo getRequiredStar('Job Title', $userFieldsArr);?></td>
				    <td>

					<input name="jobtitle" id="jobtitle" class="summaryform-formelement" type=text size=40 maxsize=150 maxlength=150 value="<?php echo $jtitle; ?>"> 
				    </td>
			    </tr>
				    <?php
				}
			     
				
			    }
			
			    
			
			}else{			
				if(JOBORDER_TITLES == 'TRUE')
				{

				?>
				<tr>
				    <td class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Job Title']))?$userFieldsAlias['Job Title']:'Job Title';echo getRequiredStar('Job Title', $userFieldsArr);?></td>
				    <td>
					<span id="jobtitlespan" class="afontstyle"><?php echo $jtitle; ?></span>
					<input name="jobtitle" id="jobtitle" class="summaryform-formelement" type="hidden" size=40 maxsize=150 maxlength=150 value="<?php echo $jtitle; ?>" readonly="readonly">
					<span id="jobtitlelinkspan">
					    <a href="javascript:doSelectJTitles();" class="edit-list">Select Title</a>
					</span>
				    </td>
				</tr>
				<?php
				}
				else
				{
				?>
				<tr>
					<td class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Job Title']))?$userFieldsAlias['Job Title']:'Job Title';echo getRequiredStar('Job Title', $userFieldsArr);?></td>
					<td>

					    <input name="jobtitle" id="jobtitle" class="summaryform-formelement" type=text size=40 maxsize=150 maxlength=150 value="<?php echo $jtitle; ?>"> 
					</td>
				</tr>
				<?php
				}
			}	
			?>
			<tr>
				<td class="crmsummary-content-title">Ref. Code</td>
				<td align=left><input name="refcode" id="refcode" class="summaryform-formelement" type=text size=40 maxsize=25 maxlength=25 value=""></td>
			</tr>
			<tr>
				<td class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['No. Positions']))?$userFieldsAlias['No. Positions']:'No. Positions';echo getRequiredStar('No. Positions', $userFieldsArr);?></td>
				<td align=left><input name="pos" id="pos" class="summaryform-formelement" type=text size=3 maxsize=3 maxlength=3 value="">&nbsp;&nbsp;&nbsp;<span class="summaryform-nonboldsub-title">Filled</span>&nbsp;<input name="posfilled" id="posfilled" class="summaryform-formelement" type=text size=3 maxsize=3 maxlength=3 value=""></td>
			</tr>
			
			<tr>
				<td width="220" class="crmsummary-content-title">Ultigigs Timesheet Layout</td>
				<td>
					<select name="joborder_timesheet_layout_preference" id="joborder_timesheet_layout_preference">
						<option value="" <?php if($fetch_outside_ts_layout_pref == "") { echo "selected"; } ?>>--- Select Template ---</option>
						<option value="Regular" <?php if($fetch_outside_ts_layout_pref == "Regular") { echo "selected"; } ?>>Regular</option>
						<option value="TimeInTimeOut" <?php if($fetch_outside_ts_layout_pref == "TimeInTimeOut") { echo "selected"; } ?>>Time In &amp; Time Out</option>
						<option value="Clockinout" <?php if($fetch_outside_ts_layout_pref == "Clockinout") { echo "selected"; } ?>>Clock In &amp; Out</option>
					</select>
					
				</td>
			</tr>
			
		  </table>
	</div>


	<div class="form-back">
	<table width="99%" border="0" class="crmsummary-edit-table">
	<tr>
		<td width="130" class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Company']))?$userFieldsAlias['Company']:'Company';echo (!empty($mandatory_madison))? $mandatory_madison:getRequiredStar('Company', $userFieldsArr);?></td>
		<td>
			<?php
			if($frompage == 'client')
			{
				echo '<span class="summaryform-formelement">'.$company_info['cnames'].'</span>';
			?>
				<div style="display:none">
					<span id='company-change'>
						<a class="crm-select-link" href="javascript:parent_popup('jobcompany')">select company</a>&nbsp;
						<a href="javascript:parent_popup('jobcompany')"><i class="fa fa-search fa-lg"></i></a>
					<span class="summaryform-formelement">&nbsp;|&nbsp;</span>
						<a href="javascript:newScreen('company','jobcompany');" class="edit-list">new company</a>
					</span>
				</div>
			<?php
			}
			else
			{
			?>
				<span id='company-change'>
					<a class="crm-select-link" href="javascript:parent_popup('jobcompany')">select company</a>&nbsp;
					<a href="javascript:parent_popup('jobcompany')"><i class="fa fa-search fa-lg"></i></a>
				<span class="summaryform-formelement">&nbsp;|&nbsp;</span>
				<a href="javascript:newScreen('company','jobcompany');" class="edit-list">new company</a>
				</span>
			<?php
			}
			?>

		</td>
		<input type="hidden" name="company" id='company' value="">
		<input type='hidden' name='comprows' id='comprows' value='0'>
		<input type="hidden" name="compname" id="compname" value="">

	</tr>
	</table>
	</div>

	<div class="form-back">
	<table width="99%" border="0" class="crmsummary-edit-table">
	<tr>
		<td width="130" class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Contact']))?$userFieldsAlias['Contact']:'Contact';echo getRequiredStar('Contact', $userFieldsArr);?></td>
		<td>
			<span id="contact-change"><a class="crm-select-link" href="javascript:contact_popup1('refcontact')">select contact</a>&nbsp;<a href="javascript:contact_popup1('refcontact')"><i class="fa fa-search fa-lg"></i></a><span class="summaryform-formelement">&nbsp;|&nbsp;</span><a href="javascript:newScreen('contact','refcontact');" class="edit-list">new contact</a></span>
		</td>

		<input type="hidden" name="contact" id="contact" value="">
		<input type='hidden' name='controws' id='controws' value='0'>
		<input type="hidden" name="conname" value="">

	</tr>
	</table>
	</div>

	<?php
		$jrtcontact=$job_fetch[3];
		$jrt_loc=$job_fetch[4];
	
	if($jrtcontact!=0)
	{
		$que2="select TRIM(CONCAT_WS('',fname,' ',lname,'(',IF(email='',nickname,email),')')),status,fname,mname,lname,address1,address2,city,state,csno from staffoppr_contact where sno='".$jrtcontact."'";
		$res2=mysql_query($que2,$db);
		$row2=mysql_fetch_row($res2);
		$jrtcont=$row2[2]." ".$row2[3]." ".$row2[4];
		$jrtcont_stat=$row2[1];
		$jrtcompany=$row2[9];
	}
	else if($jrt_loc>0 && ($jrtcontact==0 || $jrtcontact==""))
	{
		$que2="select csno from staffoppr_location where sno='".$jrt_loc."' and ltype in ('com','loc')";
		$res2=mysql_query($que2,$db);
		$row2=mysql_fetch_row($res2);
		$jrtcompany=$row2[0];
	}
	?>

	<div class="form-back crmsummary-jocomp-table">
	<table width="99%" border="0" class="crmsummary-edit-table">
	<tr>
		<input type="hidden" name="jrtcompany_sno" id="jrtcompany_sno" value="<?php echo $jrtcompany;?>">
		<input type="hidden" name="joborder_jobloc" id="joborder_jobloc" value="<?php echo $jrtcompany;?>">
		<td width=130 class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Job Location']))?$userFieldsAlias['Job Location']:'Job Location';echo getRequiredStar('Job Location', $userFieldsArr);?></td>
		<td class="editJbLocation"><input type=text name=wsjl id=wsjl size=20 value=''>&nbsp;&nbsp;<span id="jrtdisp_comp"><input type="hidden" name="jrt_loc" id="jrt_loc" value=""><a class="crm-select-link" href="javascript:bill_jrt_comp('jrt')">select company</a>&nbsp;</span>&nbsp;<span id="jrtcomp_chgid">&nbsp;</span></td>
	</tr>
	</table>
	</div>

	<div class="form-back">
	<table width="99%" border="0" class="crmsummary-edit-table">
	<tr>
		<input type="hidden" name="jrtcontact_sno" id="jrtcontact_sno" value="<?php echo $jrtcontact;?>">
		<td width="130" class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Job Reports To']))?$userFieldsAlias['Job Reports To']:'Job Reports To';echo getRequiredStar('Job Reports To', $userFieldsArr);?></td>
		<td>
		<?php
		if($jrtcontact=='0' ||  $jrtcontact=='')
		{
			?>
			<span id="jrtdisp">
			<a class="crm-select-link" href="javascript:bill_jrt_cont('jrt')">select contact</a></span>
			&nbsp;<span id="jrtchgid"><a href="javascript:bill_jrt_cont('jrt')"><i class="fa fa-search fa-lg"></i>
</a><span class="summaryform-formelement">&nbsp;|&nbsp;</span>
			<a class="crm-select-link" href="javascript:donew_add('jrt')">new&nbsp;contact</a>
			</span>
			<?
		}
		else
		{
			?>
			<span id="jrtdisp"><a class="crm-select-link" href="javascript:contact_func('<?php echo $jrtcontact;?>','<?php echo $jrtcont_stat;?>','jrt')"><?php echo dispfdb($jrtcont);?></a></span>
			&nbsp;<span id="jrtchgid">
			<span class=summaryform-formelement>(</span><a class=crm-select-link href=javascript:bill_jrt_cont('jrt')>change </a>
			&nbsp;<a href=javascript:bill_jrt_cont('jrt')><i class="fa fa-search fa-lg"></i></a>
			<span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class=crm-select-link href=javascript:donew_add('jrt')>new</a>
			<span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class="crm-select-link" href="javascript:removeContact('jrt')">remove&nbsp;</a>
			<span class=summaryform-formelement>&nbsp;)&nbsp;</span>
			</span>
			<?
		}
		?>
		<input type="hidden" name="jrt" id="jrt" value="">
		</td>
	</tr>
	<?php
		// Condition to check UDF will display for only Frontoffice

		if($frompage != 'client')
		{
			if (!isset($_GET['chkAuth']) && $_GET['chkAuth'] != 'admin') {
	?>
		<tr>
				<td align=center colspan="4" style="bgcolor: grey">
				<?php
				/**
				* Include the file to generate user defined fields.
				*
				*/
				$mod = 1;
				include_once("custom/getcustomfields.php");
				?>
				</td>
		</tr>
	<?php 		}
		}
	?>	
	</table>
	</div>
	
	<?php
        	if($frompage == 'client')
	{
	?>
		<div id='client-jborder-billinfo'>
			<div class="form-back accordion-item">
		<table width="100%" border="0" class="crmsummary-edit-tablemin" id="crm-joborder-billinginfoDiv-Table">		
			<tr>
				<td width="120" class="crmsummary-content-title">
				 <div id="crm-joborder-billinginfoDiv-plus1"><a style='text-decoration: none;' href="javascript:classToggle('billinginfo','plus')"><span class="crmsummary-content-title">Billing Information</span></a></div>
					<div id="crm-joborder-billinginfoDiv-minus1" class="DisplayNone"><a  style='text-decoration: none;' href="javascript:classToggle('billinginfo','minus')"><span class="crmsummary-content-title">Billing Information</span></a></div>
				
				</td>
				<td>
			<td>
				 <span id="rightflt" <?php echo $rightflt;?>>
				 <span class="summaryform-bold-close-title" id="crm-joborder-billinginfoDiv-close" style="display:none;width:auto;"><a style='text-decoration: none;' onClick="classToggle('billinginfo','minus')"  href="#crm-joborder-billinginfoDiv-plus">close</a></span>
				 <span class="summaryform-bold-close-title" id="crm-joborder-billinginfoDiv-open" style="width:auto;"><a style='text-decoration: none;' onClick="classToggle('billinginfo','plus')"  href="#crm-joborder-billinginfoDiv-minus">open</a></span>

                                    <div class="form-opcl-btnleftside"><div align="left"></div></div>
                                    <div id="crm-joborder-billinginfoDiv-plus"><span onClick="classToggle('billinginfo','plus')" class="form-op-txtlnk crmsummary-process-mousepointer"><b>+</b></span></div>
                                    <div id="crm-joborder-billinginfoDiv-minus"  class="DisplayNone"><span onClick="classToggle('billinginfo','minus')" class="form-cl-txtlnk crmsummary-process-mousepointer"><b>-</b></span></div>
                                    <div class="form-opcl-btnrightside"><div align="left"></div></div>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <!-- Tabbed pane for billing info start-->
					<div class="jocomp-back DisplayNone align-middle" id="crm-joborder-billinginfoDiv" name="crm-joborder-billinginfoDiv" style=" padding-left: 5px;">
						<div class="jocomp-back DisplayBlock" id="crm-joborder-billinginfoDiv2" name="crm-joborder-billinginfoDiv2" style=" padding-left: 0px;">
							<!-- Bill Rate tab start-->
                        <span id=rate_avail style="display:">
                            <table width="100%" border="0" class="crmsummary-jocomp-table">
                                <tr>
                                    <td width="100" class="summaryform-bold-title">Regular Bill Rate</td>
                                    <td>
										<div class="d-flex align-items-center">
											<div class="form-check form-check-inline" id="leftflt">
												<input type="radio" name="billratetype" value="rate" class="form-check-input summaryform-formelement" checked>
												<label class="form-check-label">Rate</label>&nbsp;&nbsp;
											</div>
											
											<span id="leftflt">
											<!--The hidden variable prevbillratevalue is used for passing the Bill Rate value to function  promptOnChangeBillRate()-->
											<input type="hidden" name="prevbillratevalue" id="prevbillratevalue" value="">
																					<input name="comm_billrate" type="text" id="comm_billrate" value="" size=10 maxlength="9" class="summaryform-formelement" onblur="javascript:calculateClientComBillRates();" onkeyup="javascript:calculateClientComBillRates();" onkeypress="return blockNonNumbers(this, event, true, false);" <?php if(RATE_CALCULATOR=='N'){ ?> onChange="promptOnChangeBillRate();" <?php } ?> >
											</span> 
											<span id="leftflt">&nbsp;&nbsp;
												<!--UOM: get dynamic rate types-->
												<select name="billrateper"  id="billrateper" onclick="getPreviousRate(this);"  onChange="change_Period('billrateCli');"  class="summaryform-formelement">
												<?php echo $timeOptions;?>
												</select>
											</span>
											<span id="leftflt">&nbsp;&nbsp;
												<select name="billratecur" id="billratecur" class="summaryform-formelement">
													<?php
													displayCurrency('');
													?>
												</select>
											</span>
										</div>
										<div class="clearfix"></div>
										<div class="d-flex align-items-center mt-2">
											<div class="form-check form-check-inline">
												<input name="billratetype" type="radio" value="open" class="summaryform-formelement">
												<label class="form-check-label">Open</label>&nbsp; 
											</div>
											<input name="comm_open_billrate" type="text" id="comm_open_billrate" value="" size=38 class="summaryform-formelement" />
										</div>
                                    </td>
                                </tr>
                            </table>
                        </span>
                        <!-- rate tab end-->
                    </div>

                    <div class="jocomp-back"  style=" padding-left: 0px;">
                        <table width="100%" border="0" class="crmsummary-jocomp-table" >
                            <tr>
                                <td colspan="2">
                                    <span class="billInfoNoteStyle">
                                        Note : Rates are auto populated based on Regular (Pay/Bill) Rates/UOM. You can edit to over ride.
                                    </span>
                                </td>
                            </tr>
                            <tr id="overtime_rate_bill" style="display:">
                                <?php
                                $arr = $objMRT->getRateTypeById(2);
                                if ($arr['beditable'] == 'N') {
                                    $bdisable = ' disabled="disabled"';
                                    $bdisabled_user_input_field = ' disabled_user_input_field';
                                } else {
                                    $bdisable = '';
                                    $bdisabled_user_input_field = '';
                                }
                                ?>
                                <td width="13%" class="summaryform-bold-title" nowrap><?php echo $arr['name']; ?> Bill Rate</td>
                                <td width="87%"><span id="leftflt">
                                        <input name="otrate_bill" type="text" id="otrate_bill" value="" size=10 maxlength="9" class="summaryform-formelement<?php echo $bdisabled_user_input_field;?>" <?php echo $bdisable; ?>><input type="hidden" id="otrate_bill_hidden" value="<?php echo $arr['bvalue']; ?>">
                                    </span>
                                    <span id="leftflt">&nbsp;&nbsp;
                                        <select name="otper_bill" id="otper_bill"  onclick="getPreviousRate(this);"  onChange="change_Period('ottimebillrateCli');" class="summaryform-formelement<?php echo $bdisabled_user_input_field;?>" <?php $bdisable; ?>>
                                           <!--UOM: get dynamic rate types-->
                                            <?php echo $timeOptions; ?>
                                        </select>
                                    </span>
                                    <span id="leftflt">&nbsp;&nbsp;
                                        <select name="otcurrency_bill" id="otcurrency_bill" class="summaryform-formelement<?php echo $bdisabled_user_input_field;?>" <?php echo $bdisable ?>>
                                            <?php
                                            displayCurrency('');
                                            ?>
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            <tr id="db_time_billrate" style="display:">
                                <?php
                                $arr = $objMRT->getRateTypeById(3);
                                if ($arr['beditable'] == 'N') {
                                    $bdisable = ' disabled="disabled"';
                                    $bdisabled_user_input_field = ' disabled_user_input_field';
                                } else {
                                    $bdisable = '';
                                    $bdisabled_user_input_field = '';
                                }
                                ?>
                                <td width="13%" class="summaryform-bold-title" nowrap><?php echo $arr['name']; ?> Bill Rate</td>
                                <td width="87%">
                                    <span id="leftflt">
                                        <input name="db_time_bill" type="text" id="db_time_bill" value="" size=10 maxlength="9" class="summaryform-formelement<?php echo $bdisabled_user_input_field;?>" <?php echo $bdisable; ?>><input type="hidden" id="db_time_bill_hidden" value="<?php echo $arr['bvalue']; ?>">
                                    </span>
                                    <span id="leftflt">&nbsp;&nbsp;
                                        <select name="db_time_billper" id="db_time_billper"  onclick="getPreviousRate(this);"  onChange="change_Period('dbtimebillrateCli');" class="summaryform-formelement<?php echo $bdisabled_user_input_field;?>" <?php echo $bdisable; ?>>
                                           <!--UOM: get dynamic rate types-->
                                            <?php echo $timeOptions; ?>
                                        </select>
                                    </span>
                                    <span id="leftflt">&nbsp;&nbsp;
                                        <select name="db_time_billcurr" id="db_time_billcurr" class="summaryform-formelement<?php echo $bdisabled_user_input_field;?>" <?php echo $bdisable; ?>>
                                            <?php
                                            displayCurrency('');
                                            ?>
                                        </select>
                                    </span>
                                </td>
                            </tr>
                            <input type="hidden" name="otrate">
                            <input type="hidden" name="otper">
                            <input type="hidden" name="otcurrency">
                            <tr>
                                <td width="167" class="summaryform-bold-title">PO Number</td>
                                <td>
                                    <span id="leftflt"><input class="summaryform-formelement" type="text" name="po_num" size=20 value="" maxlength="255"></span></td>
                            </tr>
                            <tr style="display:none">
							<?php
							$cus_username="select username from staffacc_cinfo where sno='".$billcompany."'";
							$cus_username_res=mysql_query($cus_username,$db);
							$cust_username=mysql_fetch_row($cus_username_res);
							$custusername=$cust_username[0];
							
							?>
                            <input type="hidden" name="billcompany_sno" id="billcompany_sno" value="<?php echo $billcompany; ?>">
							<input type="hidden" name="billcompany_username" id="billcompany_username" value="<?php echo $custusername;?>">
                            <td class="summaryform-bold-title"><?php echo (!empty($userFieldsAlias['Billing Address']))?$userFieldsAlias['Billing Address']:'Billing Address';echo getRequiredStar('Billing Address', $userFieldsArr);?></td>
                            <td><span id="billdisp_comp">&nbsp;<input type="hidden" name="bill_loc" id="bill_loc"><a class="crm-select-link" href="javascript:bill_jrt_comp('bill')">select company</a>&nbsp;</span>&nbsp;<span id="billcomp_chgid">&nbsp;</span>
                                <?php
                                if ($billcontact > 0 || $bill_loc > 0) {
                                    ?>
                                    <script>getCRMLocations('<?php echo $billcompany; ?>','<?php echo $billcontact; ?>','<?php echo $bill_loc; ?>','bill');</script>
                                    <?php
                                }
                                ?>
                            </td>
                            </tr>
                            <tr style="display:none">
                            <input type="hidden" name="billcontact_sno" id="billcontact_sno" value="<?php echo $billcontact; ?>">
                            <td width="167" class="summaryform-bold-title"><?php echo (!empty($userFieldsAlias['Billing Contact']))?$userFieldsAlias['Billing Contact']:'Billing Contact';echo getRequiredStar('Billing Contact', $userFieldsArr);?></td>
                            <td>

                                <span id="billdisp"><a class="crm-select-link" href="javascript:contact_func('<?php echo $billcontact; ?>','<?php echo $billcont_stat; ?>','bill')"><?php echo $billcont; ?></a></span>
                                &nbsp;<span id="billchgid"><span class=summaryform-formelement>(</span> <a class=crm-select-link href=javascript:bill_jrt_cont('bill')>change </a>&nbsp;<a href=javascript:bill_jrt_cont('bill')><i class="fa fa-search fa-lg"></i></a><span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class=crm-select-link href=javascript:donew_add('bill')>new</a><span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class="crm-select-link" href="javascript:removeContact('bill')">remove&nbsp;</a><span class=summaryform-formelement>&nbsp;)&nbsp;</span></span>
                            </td>
                            </tr>

                        </table>
                    </div>
                </div>
            
	<?php
	}
	else
	{
	?>
				<div class="form-back accordion-item">
		<table width="100%" border="0" class="crmsummary-edit-tablemin" id="crm-joborder-billinginfoDiv-Table">
                    <tr>
                        <td width="120" class="crmsummary-content-title">
                            <div id="crm-joborder-billinginfoDiv-plus1"><a style='text-decoration: none;' href="javascript:classToggle('billinginfo','plus')"><span class="crmsummary-content-title">Billing Information</span></a></div>
                            <div id="crm-joborder-billinginfoDiv-minus1" class="DisplayNone"><a  style='text-decoration: none;' href="javascript:classToggle('billinginfo','minus')"><span class="crmsummary-content-title">Billing Information</span></a></div>
                        </td>
                        <td align="right">&nbsp;</td>
                        <td>
                            <span id="rightflt" <?php echo $rightflt; ?>>
                                <span class="summaryform-bold-close-title" id="crm-joborder-billinginfoDiv-close" style="display:none;width:auto;"><a style='text-decoration: none;width:auto;' onClick="classToggle('billinginfo','minus')"  href="#crm-joborder-billinginfoDiv-plus">close</a></span>
                                <span class="summaryform-bold-close-title" id="crm-joborder-billinginfoDiv-open" style="width:auto;"><a style='text-decoration: none;width:auto;' onClick="classToggle('billinginfo','plus')"  href="#crm-joborder-billinginfoDiv-minus">open</a></span>
                                <div class="form-opcl-btnleftside"><div align="left"></div></div>
                                <div id="crm-joborder-billinginfoDiv-plus"><span onClick="classToggle('billinginfo','plus')" class="form-op-txtlnk crmsummary-process-mousepointer"><b>+</b></span></div>
                                <div id="crm-joborder-billinginfoDiv-minus"  class="DisplayNone"><span onClick="classToggle('billinginfo','minus')" class="form-cl-txtlnk crmsummary-process-mousepointer"><b>-</b></span></div>
                                <div class="form-opcl-btnrightside"><div align="left"></div></div>
                            </span>
                        </td>
                    </tr>
		</table>
            </div>
        <!-- Tabbed pane for billing info start-->
        <div class="jocomp-back DisplayNone" id="crm-joborder-billinginfoDiv" name="crm-joborder-billinginfoDiv" style=" padding-left: 5px;">
            <div class="jocomp-back DisplayNone" id="crm-joborder-billinginfoDiv_tab" name="crm-joborder-billinginfoDiv_tab"  <?php echo $style_table; ?>>
                <table width="100%" border="0" class="crmsummary-jocomp-table">
                    <tr>
                        <td  height="22" colspan="2">
                            <span class="crmsummary-content-title" id=rate_cond><b>Rates</b></span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="jocomp-back DisplayNone" id="crm-joborder-billinginfoDiv2" name="crm-joborder-billinginfoDiv2" <?php echo $style_table;?>>
            <input type="hidden" name='src_status' value="rates">
             <!-- Rate tab start-->
            <span id=rate_avail style="display:block">
            <table class="table align-middle crmsummary-jocomp-table">
                <?php if(RATE_CALCULATOR=='Y'){ ?>
                        <tr>
                            <td colspan="2" style="padding-bottom:6px;">
                                <span class="billInfoNoteStyle">
                                    Note: Pay Rate or Bill Rate is calculated using Margin as the default. To calculate using Markup leave Margin blank.
                                </span>
                            </td>
                        </tr>
                        <?php } ?>
                <tr>
                    <td width="14%" class="summaryform-bold-title" >Regular <?php echo (!empty($userFieldsAlias['Pay Rate'])) ? $userFieldsAlias['Pay Rate'] : 'Pay Rate'; echo getRequiredStar('Pay Rate', $userFieldsArr); ?><?php if(TRICOM_REPORTS=='Y'){echo $mandatory_madison;}?></td>
						<td valign="middle"> 
							<div class="row g-2 align-items-center">
								<?php if(RATE_CALCULATOR=='Y'){ ?>
								<div class="col-auto">
									<span id="payrate_calculator" style="cursor:pointer;display:none;">
										<i class="fa fa-calculator" onclick="javascript:payrateCalculatorFunc();" aria-hidden="true"></i>
									</span>
								</div>
								<?php } ?>
								<div class="col-auto">
									<div class="ps-0 form-check form-check-inline">
										<input name="payratetype" id="payratetype"  type="radio" onClick="javascript:calrate()" value="rate" checked class="payratetypeNew form-check-input">
										<label class="form-check-label">Rate</label>
									</div>
								</div>
								<div class="col-auto">
									<!--The hidden variable prevpayratevalue is used for passing the Pay Rate value to function  promptOnChangePayRate()-->
									<input type="hidden" name="prevpayratevalue" id="prevpayratevalue" value="">						
									<input name="comm_payrate" id="comm_payrate" type="text" value="" size="10" maxlength="9" class="summaryform-formelement" onfocus="javascript:confirmPayRateAutoCalculatoin('comm_payrate', 'calculateComPayRates');" onkeyup="javascript:calculateComPayRates();" onkeypress="return blockNonNumbers(this, event, true, false);" onblur="clearConfirmPayRateAutoCalculatoin();" <?php if(RATE_CALCULATOR=='N'){ ?> onchange="promptOnChangePayRate();" <?php } ?> >
								</div>
								<div class="col-auto">
									<!--UOM: get dynamic rate types-->
									<select name="payrateper" id="payrateper" onclick="getPreviousRate(this);" onChange="change_Period('billrate');" class="summaryform-formelement">
										<?php echo $timeOptions;?>
									</select>
								</div>
								<div class="col-auto">
									<select name="payratecur" id="payratecur"  onChange="change_PeriodNew('billrate');" class="summaryform-formelement">
										<?php displayCurrency($elements[3]); ?>
									</select>
									
								</div>
								<div class="col-auto" id='reg_pay_Bill_NonBill'>
									<div class="form-check form-check-inline">
										<input name="payrateBillOpt" id="payrateBillOpt"  class="form-check-input" type="radio" value="Y" <?php if ($displaytypes[0]['value'] == 'B') { echo 'checked="checked"'; } ?> class="BillableRates">
										<label class="form-check-label">Billable</label>
									</div>
									<div class="form-check form-check-inline">
										<input name="payrateBillOpt" id="payrateBillOpt" class="form-check-input" type="radio" value="N" <?php if ($displaytypes[0]['value'] == 'NB') { echo 'checked="checked"'; } ?> class="BillableRates">
										<label class="form-check-label">Non-Billable</label>
									</div>
								</div>
							</div>
							
							<div class="d-flex align-items-center mt-2">
								<div class="form-check form-check-inline">  
									<input name="payratetype" id="payratetype"  type="radio" value="open"   onClick="javascript:calrate()" class="form-check-input">
									<label class="form-check-label">Open</label>
								</div>
								<input name="comm_open_payrate" type="text" id="comm_open_payrate" value="" size=38 class="summaryform-formelement" style="margin-top:2px">
							</div>
						</td>
					</tr>
                <tr id="burden-rate" >
                    <td class="summaryform-bold-title">Pay&nbsp;Burden&nbsp;<?php echo ($payburden_status=='Y')? "<span style='color:red'>*</span>": "" ?></td>
                    <td class=summarytext>
                     <input type="hidden" id="manage_burden_status" value="<?php echo $burden_status;?>">
                     <input type="hidden" name='autosetwcc' id="autosetwcc" value="<?php echo $autowcc_status;?>"/>
                     <input type="hidden" name='payburdenstatus' id="payburdenstatus" value="<?php echo $payburden_status;?>"/>
                <?php 
                if($burden_status == 'yes'){	
                	if($autowcc_status == 'Y')
                	{
                		$Addfunction = "AutoWCChangeAction(this,'joborder',true);";
                	}else{
                		$Addfunction ="";
                	}	
                	
                ?>

                    <input type="hidden" name="btdefaultchk" id="btdefaultchk" value="0" />
                    <input type="hidden" name="hdnbi_details" id="hdnbi_details" value="" />
                    <input type="hidden" name="hdnbt_details" id="hdnbt_details" value="" />
                    <input type="hidden" name="hdnTotalBurdenPerc" id="hdnTotalBurdenPerc" value="0" />
                    <input type="hidden" name="hdnTotalBurdenFlat" id="hdnTotalBurdenFlat" value="0" />
                    <input type="hidden" name="comm_burden" id="comm_burden" value="" />

                    <div class="BTContainer">
                        <div>
                            <select name="burdenType" id="burdenType" <?php if(RATE_CALCULATOR=='Y'){?> onchange="doNoCalculateMarkup(this);BTChangeAction(this,'joborder');<?php echo $Addfunction;?>"  <?php }else{ ?> onchange="BTChangeAction(this,'joborder');<?php echo $Addfunction;?>" <?php } ?>>
                            <?php
                            if($payburden_status == 'Y')
                            {?>
                            <option value="">--Select Pay Burden--</option>
                            <?php
                        	}
                            foreach ($arr_burden_type as $sno => $burden) {
                                if ($burden['rate_type'] == 'payrate') {
                            ?>
                                <option value="<?php echo $sno; ?>"><?php echo $burden['burden_type']; ?></option>
                            <?php
                                }
                            }
                            ?>
                            </select>
                        </div>
                        <div style="vertical-align:middle;">
                            <b><span id="burdenItemsStr" class="summaryform-formelement">0%</span></b>
                        </div>
                    </div>
              <?php
            	}
                else
                {
                    ?>
                    <div class="BTContainer">
                        <div>
                            <input type="text" name="comm_burden" id="comm_burden" <?php if(RATE_CALCULATOR=='Y'){?> onkeyup="doNoCalculateMarkup(this);calculatebtmargin();"  <?php }else{ ?> onkeyup="calculatebtmargin();" <?php } ?> maxlength="9" size="10" onkeypress="return blockNonNumbers(this, event, true, false);"/>
                        </div>
                        <div> 
                            <b><span class="summaryform-formelement">%</span></b>
                        </div>
                    </div>
                <?php
                }
                ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" class="summaryform-bold-title">Regular <?php echo (!empty($userFieldsAlias['Bill Rate'])) ? $userFieldsAlias['Bill Rate'] : 'Bill Rate';  echo getRequiredStar('Bill Rate', $userFieldsArr); ?><?php if(TRICOM_REPORTS=='Y'){echo $mandatory_madison;}?></td>
                    <td>
						
					<div class="d-flex align-items-center mt-2">
                        <?php if(RATE_CALCULATOR=='Y'){ ?>
                        <span id="billrate_calculator" style="float:left;cursor:pointer;display:none;"><i class="fa fa-calculator" onclick="javascript:billrateCalculatorFunc();" aria-hidden="true"></i>&nbsp;&nbsp;</span>
                        <?php } ?>
						
                        <div class="form-check form-check-inline" id="leftflt">
							<input type="radio" name="billratetype" id="billratetype" value="rate"onClick="javascript:calrate()" class="form-check-input" checked> 
							<label class="form-check-label">Rate</label>
						</div>
						
                        <span id="leftflt" style="margin-left: 5px;margin-top: 1px;">
						<!--The hidden variable prevbillratevalue is used for passing the Bill Rate value to function  promptOnChangeBillRate()-->
						<input type="hidden" name="prevbillratevalue" id="prevbillratevalue" value="">

                            <input name="comm_billrate" type="text" id="comm_billrate" value="" size=10 maxlength="9" class="summaryform-formelement" onfocus="javascript:confirmBillRateAutoCalculatoin('comm_billrate');" onkeyup="javascript:calculateComBillRates();" onkeypress="return blockNonNumbers(this, event, true, false);" onblur="clearConfirmBillRateAutoCalculatoin();" <?php if(RATE_CALCULATOR=='N'){ ?> onChange="promptOnChangeBillRate();" <?php } ?> >
                        </span>
                        <span id="leftflt" style="margin-left: 5px;">
                            <select name="billrateper" id="billrateper" onclick="getPreviousRate(this);"  onChange="change_Period('payrate');" class="summaryform-formelement">
                             <!--UOM: get dynamic rate types-->
								<?php echo $timeOptions;?>
                            </select>
                        </span>
                        <span id="leftflt" style="margin-left: 5px; margin-top: -4px;">
                            <select name="billratecur" id="billratecur" onChange="change_PeriodNew('payrate');" class="summaryform-formelement"><?php displayCurrency(COMPANY_CURRENCY); ?></select>
							
							<span id='reg_bill_Tax_NonTax'>
								<div class="form-check form-check-inline">
									<input class="form-check-input" name="billrateTaxOpt" id="billrateTaxOpt" type="radio" value="Y" <?php if ($displaytypes[1]['value'] == 'T') { echo 'checked="checked"'; } ?> >
									<label class="form-check-label">Taxable</label>
								</div>
								
								<div class="form-check form-check-inline">
									<input class="form-check-input" name="billrateTaxOpt" id="billrateTaxOpt"  type="radio" value="N" <?php if ($displaytypes[1]['value'] == 'NT') { echo 'checked="checked"'; } ?> >
									<label class="form-check-label">Non-Taxable</label>
								</div>
							</span>
						
                        </span>
						</div>
							<div class="clearfix"></div>
					<div class="d-flex align-items-center mt-2">
							<div class="form-check form-check-inline">
								<input class="form-check-input" name="billratetype" id="billratetype" type="radio" onClick="javascript:calrate()" value="open">
								<label class="form-check-label">Open</label>
							</div>
							&nbsp;<input name="comm_open_billrate" type="text" id="comm_open_billrate" value="" size=38 class="summaryform-formelement">
                    </div></td>
                </tr>
                <tr id="burden-rate" >

                    <td class="summaryform-bold-title">Bill Burden&nbsp;<?php echo ($billburden_status=='Y')? "<span style='color:red'>*</span>": "" ?></td>
                    <td class=summarytext>
                    	 <input type="hidden" name='billburdenstatus' id="billburdenstatus" value="<?php echo $billburden_status;?>"/>
                        <?php 
                            if($burden_status == 'yes'){
                        ?>
                        <input type="hidden" name="bill_btdefaultchk" id="bill_btdefaultchk" value="0" />
                        <input type="hidden" name="bill_hdnbi_details" id="bill_hdnbi_details" value="" />
                        <input type="hidden" name="bill_hdnbt_details" id="bill_hdnbt_details" value="" />
                        <input type="hidden" name="bill_hdnTotalBurdenPerc" id="bill_hdnTotalBurdenPerc" value="0" />
                        <input type="hidden" name="bill_hdnTotalBurdenFlat" id="bill_hdnTotalBurdenFlat" value="0" />
                        <input type="hidden" name="comm_bill_burden" id="comm_bill_burden" value="" />
                        
                        <div class="BTContainer">
                            <div>                        
                                <select name="bill_burdenType" id="bill_burdenType" <?php if(RATE_CALCULATOR=='Y'){?> onchange="doNoCalculateMarkup(this);BillBTChangeAction(this,'joborder');"  <?php }else{ ?> onchange="BillBTChangeAction(this,'joborder');" <?php } ?>>
                                    <?php
                                       echo $existingBurdenOpt;
                                       if($billburden_status == 'Y')
                                       {
                                    ?>
                                    <option value="">--Select Bill Burden--</option>
                                    <?php
                                		}
                                    foreach ($arr_burden_type as $sno => $burden) {
                                        if ($burden['rate_type'] == 'billrate') {
                                        ?>
                                        <option value="<?php echo $sno; ?>"><?php echo $burden['burden_type']; ?></option>
                                        <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div style="vertical-align:middle;">
                                <b><span id="bill_burdenItemsStr" class="summaryform-formelement">0%</span></b>
                            </div>				
                        </div>
                        <?php 
                            } else {
                               ?>
                            <div class="BTContainer">
                                <div>
                                    <input type="text" name="comm_bill_burden" id="comm_bill_burden" <?php if(RATE_CALCULATOR=='Y'){?> onkeyup="doNoCalculateMarkup(this);calculatebillbtmargin();" <?php }else{ ?> onkeyup="calculatebillbtmargin();" <?php } ?>  value="0" maxlength="9" size="10" onkeypress="return blockNonNumbers(this, event, true, false);"/>
                                </div>
                                <div>
                                    <b><span class="summaryform-formelement">%</span></b>
                                </div>
                            </div>
                        <?php
                            }
                        ?>
                    </td>
                </tr>
                <?php if(RATE_CALCULATOR=='Y'){ ?>
                    <tr id="marg-rate" >
                    <td class="summaryform-bold-title">Margin&nbsp;</td>
                    <td>
                        <span id="margin_calculator" style="cursor:pointer;display:none;"><i class="fa fa-calculator" onclick="marginCalculatorFunc(this);" aria-hidden="true"></i>&nbsp;&nbsp;</span>
                        <input type=hidden  size="10" maxlength="9" onkeypress="return blockNonNumMarginMarkup(this, event);" name=comm_margin id=comm_margin class="summaryform-formelement"><span class="summaryform-formelement" style="display:none;" id="comm_margin_span">0.00</span>&nbsp;<span  class="summaryform-formelement"><b>%</b></span>&nbsp;<span style="display:none;" class="summaryform-formelement">|</span>&nbsp;<span style="display:none;"class="summaryform-formelement"><b><span style="display:none;" id="margincost">$0.00</span></b></span>
                        <?php
                        $qry = "select netmargin from margin_setup where sno=1";
                        $qry_res = mysql_query($qry, $db);
                        $qry_row = mysql_fetch_row($qry_res);
                        ?>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="summaryform-formelement">(Company&nbsp;Min&nbsp;Margin:&nbsp;<?= $qry_row[0]; ?>%)</span>
                    </td>
                </tr>
                <tr id="markup-rate" >
                    <td class="summaryform-bold-title">Markup&nbsp;</td>
                    <td>
                        <span id="markup_calculator" style="cursor:pointer;display:none;"><i class="fa fa-calculator" onclick="markupCalculatorFunc(this);"aria-hidden="true"></i>&nbsp;&nbsp;</span>
                        <input name="comm_markup" type="hidden" id="comm_markup" value="" size="10" maxlength="9" onkeypress="return blockNonNumMarginMarkup(this, event);" class="summaryform-formelement"><span style="display:none;" class="summaryform-formelement" id="comm_markup_span">0.00</span>&nbsp;<span  class="summaryform-formelement" id="comm_markup_span_perc"><b>%</b></span>
                    </td>
                </tr>
                <?php } else{ ?>
                  <tr id="marg-rate" >
                    <td class="summaryform-bold-title">Margin&nbsp;</td>
                    <td>
                        <input type=hidden  maxlength=10 size=10 name=comm_margin id=comm_margin class="summaryform-formelement" readonly><span class="summaryform-formelement" id="comm_margin_span">0.00</span>&nbsp;<span class="summaryform-formelement"><b>%</b></span>&nbsp;<span class="summaryform-formelement">|</span>&nbsp;<span class="summaryform-formelement"><b><span id="margincost">$0.00</span></b></span>
                        <?php
                        $qry = "select netmargin from margin_setup where sno=1";
                        $qry_res = mysql_query($qry, $db);
                        $qry_row = mysql_fetch_row($qry_res);
                        ?>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="summaryform-formelement">(Company&nbsp;Min&nbsp;Margin:&nbsp;<?= $qry_row[0]; ?>%)</span>
                    </td>
                </tr>
                <tr id="markup-rate" >
                    <td class="summaryform-bold-title">Markup&nbsp;</td>
                    <td><input name="comm_markup" type="hidden" id="comm_markup" value="" size=10 class="summaryform-formelement" readonly><span class="summaryform-formelement" id="comm_markup_span">0.00</span>&nbsp;<span class="summaryform-formelement" id="comm_markup_span_perc"><b>%</b></span>
                    </td>
                </tr>  
                <?php } ?>
                <tr>
                    <td colspan="2">
                        <span class="billInfoNoteStyle">
                            Note : Rates are auto populated based on Regular (Pay/Bill) Rates/UOM. You can edit to over ride.
                        </span>
                    </td>
                </tr>
                </table>
            </span>
            <!-- rate tab end-->
            </div>
            <div class="jocomp-back jocomp_width" <?php echo $style_table;?>>
                <table width="100%" border="0"  class="crmsummary-jocomp-table">
                    <tr id="overtime_rate_pay" style="display:''">
                        <?php
                        $arr = $objMRT->getRateTypeById(2);
                        if ($arr['peditable'] == 'N') {
                            $pdisable = ' disabled="disabled"';
                            $pdisabled_user_input_field = ' disabled_user_input_field';
                        } else {
                            $pdisable = '';
                            $pdisabled_user_input_field = '';
                        }
                        if ($arr['beditable'] == 'N') {
                            $bdisable = ' disabled="disabled"';
                            $bdisabled_user_input_field = ' disabled_user_input_field';
                        } else {
                            $bdisable = '';
                            $bdisabled_user_input_field = '';
                        }
                        ?>
                        <td width="13%" class="summaryform-bold-title"><?php echo $arr['name'];?> Pay Rate<?php if(TRICOM_REPORTS=='Y'){echo $mandatory_madison;}?></td>
                        <td width="87%">
                            <span id="leftflt">
                                <input name="otrate_pay" type="text" id="otrate_pay" value="" size=10 maxlength="9" class="summaryform-formelement<?php echo $pdisabled_user_input_field?>" <?php echo $pdisable;?> onkeypress="return blockNonNumbers(this, event, true, false);"><input type="hidden" id="otrate_pay_hidden" value="<?php echo $arr['pvalue'];?>" >
                            </span>
                            <span id="leftflt">&nbsp;&nbsp;
                                <select name="otper_pay" id="otper_pay"  onclick="getPreviousRate(this);"  onChange="change_Period('overtimepayrate');" class="summaryform-formelement<?php echo $pdisabled_user_input_field?>" <?php echo $pdisable;?>>
                                    <!--UOM: get dynamic rate types-->
									<?php echo $timeOptions;?>
                                </select>
                            </span>
                            <span id="leftflt">&nbsp;&nbsp;
                                <select name="otcurrency_pay"   onChange="change_PeriodNew('overtimepayratecur');"  id="otcurrency_pay" class="summaryform-formelement<?php echo $pdisabled_user_input_field?>" <?php echo $pdisable;?>>
                                    <?php
                                    displayCurrency('');
                                    ?>
                                </select>
                            </span>
					<span id='ot_pay_Bill_NonBill'>
						<div class="form-check form-check-inline">
                            <input name="OvpayrateBillOpt" id="OvpayrateBillOpt"  type="radio" value="Y" <?php if($arr['poption'] == "B"){ echo ' checked="checked"'; }else { echo $pdisable; }?> class="BillableRates form-check-input">
							<label class="form-check-label">Billable</label>
						</div>
						<div class="form-check form-check-inline">
                            <input name="OvpayrateBillOpt" id="OvpayrateBillOpt"  type="radio" value="N" <?php if($arr['poption'] == "NB"){ echo ' checked="checked"'; }else { echo $pdisable; }?> class="BillableRates form-check-input">
							<label class="form-check-label">Non-Billable</label>
						</div>
					</span>
                        </td>
                    </tr>

                    <tr id="overtime_rate_bill" style="display:''">
                        <td width="13%" class="summaryform-bold-title"><?php echo $arr['name'];?> Bill Rate<?php if(TRICOM_REPORTS=='Y'){echo $mandatory_madison;}?></td>
                        <td width="87%">
                            <span id="leftflt">
                                <input name="otrate_bill" type="text" id="otrate_bill" value="" size=10 maxlength="9" class="summaryform-formelement<?php echo $bdisabled_user_input_field;?>" <?php echo $bdisable;?> onkeypress="return blockNonNumbers(this, event, true, false);"><input type="hidden" id="otrate_bill_hidden" value="<?php echo $arr['bvalue'];?>">
                            </span>
                            <span id="leftflt">&nbsp;&nbsp;
                                <select name="otper_bill" onclick="getPreviousRate(this);"  onChange="change_Period('overtimebillrate');"   id="otper_bill" class="summaryform-formelement<?php echo $bdisabled_user_input_field;?>" <?php echo $bdisable;?>>
                                    <!--UOM: get dynamic rate types-->
									<?php echo $timeOptions;?>
                                </select>
                            </span>
                            <span id="leftflt">&nbsp;&nbsp;
                                <select name="otcurrency_bill" id="otcurrency_bill"  onChange="change_PeriodNew('overtimebillratecur');" class="summaryform-formelement<?php echo $bdisabled_user_input_field;?>" <?php echo $bdisable;?>>
                                    <?php
                                    displayCurrency('');
                                    ?>
                                </select></span>
					<span id='ot_bill_Tax_NonTax'>
						<div class="form-check form-check-inline">
                           <input name="OvbillrateTaxOpt" id="OvbillrateTaxOpt" class="form-check-input" type="radio" value="Y" <?php if($arr['boption'] == "T"){ echo ' checked="checked"'; }else { echo $bdisable; }?>>
							<label class="form-check-label">Taxable</label>
						</div>
						<div class="form-check form-check-inline">
                            <input name="OvbillrateTaxOpt" id="OvbillrateTaxOpt" class="form-check-input" type="radio" value="N" <?php if($arr['boption'] == "NT"){ echo ' checked="checked"'; }else { echo $bdisable; }?>>
							<label class="form-check-label">Non-Taxable</label>
						</div>
					</span>
                        </td>
                    </tr>

                    <tr id="db_time_payrate" style="display:''">
                        <?php
                        $arr = $objMRT->getRateTypeById(3);
                        if ($arr['peditable'] == 'N') {
                            $pdisable = ' disabled="disabled"';
                            $pdisabled_user_input_field = ' disabled_user_input_field';
                        } else {
                            $pdisable = '';
                            $pdisabled_user_input_field = '';
                        }
                        if ($arr['beditable'] == 'N') {
                            $bdisable = ' disabled="disabled"';
                            $bdisabled_user_input_field = ' disabled_user_input_field';
                        } else {
                            $bdisable = '';
                            $bdisabled_user_input_field = '';
                        }
                        ?>
                        <td width="13%" class="summaryform-bold-title" nowrap><?php echo $arr['name'];?> Pay Rate<?php if(TRICOM_REPORTS=='Y'){echo $mandatory_madison;}?></td>
                        <td width="87%">
                            <span id="leftflt">
                                <input name="db_time_pay" type="text" id="db_time_pay" value="" size=10 maxlength="9" class="summaryform-formelement<?php echo $pdisabled_user_input_field?>" <?php echo $pdisable;?> onkeypress="return blockNonNumbers(this, event, true, false);"><input type="hidden" id="db_time_pay_hidden" value="<?php echo $arr['pvalue'];?>">
                            </span>
                            <span id="leftflt">&nbsp;&nbsp;
                                <select name="db_time_payper" id="db_time_payper" onclick="getPreviousRate(this);"  onChange="change_Period('dbtimepayrate');" class="summaryform-formelement<?php echo $pdisabled_user_input_field?>" <?php echo $pdisable;?>>
                                    <!--UOM: get dynamic rate types-->
									<?php echo $timeOptions;?>
                                </select>
                            </span>
                            <span id="leftflt">&nbsp;&nbsp;
                                <select name="db_time_paycur"  onChange="change_PeriodNew('dbtimepayratecur');" id="db_time_paycur" class="summaryform-formelement<?php echo $pdisabled_user_input_field?>" <?php echo $pdisable;?>>
                                    <?php
                                    displayCurrency('');
                                    ?>
                                </select></span>
								<span id='dt_pay_Bill_NonBill'>
									<div class="form-check form-check-inline">
										<input name="DbpayrateBillOpt" id="DbpayrateBillOpt" type="radio" value="Y" <?php if($arr['poption'] == "B"){ echo ' checked="checked"'; }else { echo $pdisable; }?> class="BillableRates form-check-input">
										<label class="form-check-label">Billable</label>
									</div>
									<div class="form-check form-check-inline">
										<input name="DbpayrateBillOpt" id="DbpayrateBillOpt" type="radio" value="N" <?php if($arr['poption'] == "NB"){ echo ' checked="checked"'; }else { echo $pdisable; }?> class="BillableRates form-check-input">
										<label class="form-check-label">Non-Billable</label>
									</div>
								</span>
                        </td>
                    </tr>

                    <tr id="db_time_billrate" style="display:''">
                        <td width="13%" class="summaryform-bold-title" nowrap><?php echo $arr['name'];?> Bill Rate<?php if(TRICOM_REPORTS=='Y'){echo $mandatory_madison;}?></td>
                        <td width="87%">
                            <span id="leftflt">
                                <input name="db_time_bill" type="text" id="db_time_bill" value="" size=10 maxlength="9" class="summaryform-formelement<?php echo $bdisabled_user_input_field;?>" <?php echo $bdisable;?> onkeypress="return blockNonNumbers(this, event, true, false);"><input type="hidden" id="db_time_bill_hidden" value="<?php echo $arr['bvalue']; ?>">
                            </span>
                            <span id="leftflt">&nbsp;&nbsp;
                                <select name="db_time_billper" onclick="getPreviousRate(this);"  onChange="change_Period('dbtimebillrate');" id="db_time_billper" class="summaryform-formelement<?php echo $bdisabled_user_input_field;?>" <?php echo $bdisable;?>>
                                    <!--UOM: get dynamic rate types-->
									<?php echo $timeOptions;?>
                                </select>
                            </span>
                            <span id="leftflt">&nbsp;&nbsp;
                                <select name="db_time_billcurr" onChange="change_PeriodNew('dbtimebillratecur');" id="db_time_billcurr" class="summaryform-formelement<?php echo $bdisabled_user_input_field;?>" <?php echo $bdisable;?>>
                                    <?php
                                    displayCurrency('');
                                    ?>
                                </select>
                            </span>
					<span id='db_bill_Tax_NonTax'>
						<div class="form-check form-check-inline">
                            <input class="form-check-input" name="DbbillrateTaxOpt" id="DbbillrateTaxOpt"  type="radio" value="Y" <?php if($arr['boption'] == "T"){ echo ' checked="checked"'; }else { echo $bdisable; }?>>
							<label class="form-check-label">Taxable</label>
						</div>
						<div class="form-check form-check-inline">
                            <input class="form-check-input" name="DbbillrateTaxOpt" id="DbbillrateTaxOpt"  type="radio" value="N" <?php if($arr['boption'] == "NT"){ echo ' checked="checked"'; }else { echo $bdisable; }?>>
							<label class="form-check-label">Non-Taxable</label>
						</div>
					</span>
			    
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2" align="left" style="padding:0px">
						<!-- shift rates dynamic function file path include\manage_pay_rates.php -->
                            <div id="multipleRatesTab"></div>
                            <input type="hidden" id="selectedcustomratetypeids" value=""/>
                        </td>
                    </tr>

                    <tr id="custom_rate_type_tr" style="display:none;">
                        <td colspan="2">
                            <a class="crm-select-link" href="javascript:addRateTypes();">Select Custom Rate</a>
                        </td>
                    </tr>
              <input type="hidden" name="otrate">
              <input type="hidden" name="otper">
              <input type="hidden" name="otcurrency">
               <tr class="jocomp-back DisplayNone" id="crm-joborder-billinginfoDiv1" name="crm-joborder-billinginfoDiv1" style=" padding-left: 5px;">
                   <td width="13%" class="summaryform-bold-title" valign="top">
                       <?php echo (!empty($userFieldsAlias['Salary']))?$userFieldsAlias['Salary']:'Salary';echo getRequiredStar('Salary', $userFieldsArr);?>
                   </td>

                   <td><!-- salary direct -->
                        <span id="salary_direct" style="display:none">
                            <div class="form-check form-check-inline" id="leftflt">
								<input name="rangetype" id="rangetype" class="form-check-input"  type="radio"  value="amount" checked> 
								<label class="form-check-label">Amount</label>
								&nbsp;
                            </div>
                            <span id="leftflt">
                                <input name="amount_val" type="text" id="amount_val" value="" size=10 maxlength="9" class="summaryform-formelement" onkeypress="return blockNonNumbers(this, event, true, false);">
                            </span>
                            <span id="leftflt" >&nbsp;&nbsp;
			    <select name="amountper" onClick="getPreviousRate(this);" onChange="change_Period('amountper');" id="amountper" class="summaryform-formelement">
		            <!--UOM: get dynamic rate types-->
				 <?php  $amountOptions = getNewRateTypes('YEAR');
                                        echo  $amountOptions ;?> 
                              </select>
                            </span>
                            <span id="leftflt">&nbsp;&nbsp;
                                <select name="amountcur" id="amountcur" class="summaryform-formelement">
                                    <?php
                                    displayCurrency('');
                                    ?>
                                </select>
                            </span>
                            <br />
                            <div style="line-height:26px">&nbsp;</div>
                            <div class="form-check form-check-inline" id="leftflt">
                                <input name="rangetype" id="rangetype" class="form-check-input" type="radio"  value="range">
                                <label class="form-check-label">Range</label>&nbsp;&nbsp;&nbsp;
                            </div>
                            <span id="leftflt">
                                <input name="range_max" type="text" id="range_max" value="" size=10 maxlength="9" class="summaryform-formelement">&nbsp;<span class="summaryform-formelement">to</span>&nbsp;<input name="range_min" type="text" id="range_min" value="" size=10 maxlength="9" class="summaryform-formelement">
                            </span>
                            <span id="leftflt" >&nbsp;&nbsp;
			    <select name="rangeper" id="rangeper" onClick="getPreviousRate(this);" onChange="change_Period('rangeper');" class="summaryform-formelement">
		            <!--UOM: get dynamic rate types-->
                            <?php $ranOptions = getNewRateTypes('YEAR');
                                    echo  $ranOptions ;?> 
                                </select>
                            </span>
                            <span id="leftflt">&nbsp;&nbsp;
                                <select name="rangecur" id="rangecur" class="summaryform-formelement">
                                    <?php displayCurrency(''); ?>
                                </select>
                            </span>
                            <br />
                            <div style="line-height:20px">&nbsp;</div>
							 <div class="form-check form-check-inline">
								<input name="rangetype" id="rangetype" type="radio" value="open"  class="form-check-input">
								<label class="form-check-label">Open</label>
							</div>
							
							&nbsp;&nbsp;&nbsp;
                            <input name="open_salary" type="text" id="open_salary" value="" size=38 class="summaryform-formelement">
                        </span>

                        <!-- Salary check-->
                        <span id="salary_others" style="display:''">
                            <span id="leftflt">
                                <input name="salary" type="text" id="salary" value="" size=10 maxlength="9" class="summaryform-formelement" onkeypress="return blockNonNumbers(this, event, true, false);">
                            </span>
                            <span id="leftflt">&nbsp;&nbsp;
			    <select name="salaryper" onClick="getPreviousRate(this);" onChange="change_Period('salary');" id="salaryper" class="summaryform-formelement">
			    <!--UOM: get dynamic rate types-->
                             <?php  $rateOptions = getNewRateTypes('YEAR');
                                    echo  $rateOptions ;?> 
                                </select>
                            </span>
                            <span id="leftflt">&nbsp;&nbsp;
                                <select name="salarycur" id="salarycur" class="summaryform-formelement">
                                    <?php
                                    displayCurrency('');
                                    ?>
                                </select>
                            </span>
                        </span>
                    </td>
            </tr>
				<tr id="overtime_rate_pay_direct" valign="middle" style="display:none;">
                <?php
                $arr = $objMRT->getRateTypeById(2);
                ?>
                <td width="13%" class="summaryform-bold-title"><?php echo $arr['name'];?> Pay Rate<?php if(TRICOM_REPORTS=='Y'){echo $mandatory_madison;}?></td>
                <td width="87%">
                    <span id="leftflt">
                        <input name="otrate_pay_direct" type="text" id="otrate_pay_direct" value="" size=10 maxlength="9" class="summaryform-formelement" />
                    </span>
                    <span id="leftflt">&nbsp;&nbsp;
                        <select name="otper_pay_direct"  onClick="getPreviousRate(this);" onChange="change_Period('otpaydirect');" id="otper_pay_direct" class="summaryform-formelement">
                        <!--UOM: get dynamic rate types-->
			<?php echo $timeOptions;?>
                        </select>
                    </span>
                    <span id="leftflt">&nbsp;&nbsp;
                        <select name="otcurrency_pay_direct" id="otcurrency_pay_direct" class="summaryform-formelement"/>
                            <?php
                            displayCurrency('');
                            ?>
                        </select>
                    </span>
						<div class="form-check form-check-inline ms-1 mt-2">
							<input name="OvpayrateBillOpt_direct" id="OvpayrateBillOpt_direct" class="form-check-input" type="radio" value="Y" <?php if($arr['poption'] == "B"){ echo ' checked="checked"'; }?>/>
							<label class="form-check-label">Billable</label>
						</div>
						<div class="form-check form-check-inline ms-1 mt-2">
							<input name="OvpayrateBillOpt_direct" id="OvpayrateBillOpt_direct"  class="form-check-input" type="radio" value="N" <?php if($arr['poption'] == "NB"){ echo ' checked="checked"'; }?>/>
							<label class="form-check-label">Non-Billable</label>
						</div>
                </td>
            </tr>

            <tr id="db_time_payrate_direct" style="display:none;">
                <?php
                $arr = $objMRT->getRateTypeById(3);
                ?>
                <td width="13%" class="summaryform-bold-title" nowrap><?php echo $arr['name'];?> Pay Rate<?php if(TRICOM_REPORTS=='Y'){echo $mandatory_madison;}?></td>
                <td width="87%">
                    <span id="leftflt">
                    <input name="db_time_pay_direct" type="text" id="db_time_pay_direct" value="" size=10 maxlength="9" class="summaryform-formelement" />
                    </span>
                    <span id="leftflt">&nbsp;&nbsp;
		    <select name="db_time_payper_direct" onClick="getPreviousRate(this);" onChange="change_Period('dbpaydirect');" id="db_time_payper_direct" class="summaryform-formelement">
		    <!--UOM: get dynamic rate types-->
			<?php echo $timeOptions; ?>
		    </select>
                    </span>
                    <span id="leftflt">&nbsp;&nbsp;
                        <select name="db_time_paycur_direct" id="db_time_paycur_direct" class="summaryform-formelement">
                            <?php
                            displayCurrency('');
                            ?>
                        </select>
						</span>
						<div class="form-check form-check-inline ms-1 mt-2">
							<input name="DbpayrateBillOpt_direct" id="DbpayrateBillOpt_direct"  class="form-check-input" type="radio" value="Y" <?php if($arr['poption'] == "B"){ echo ' checked="checked"'; }?>/>
							<label class="form-check-label">Billable</label>
						</div>
						<div class="form-check form-check-inline ms-1 mt-2">
							<input name="DbpayrateBillOpt_direct" id="DbpayrateBillOpt_direct"  class="form-check-input" type="radio" value="N" <?php if($arr['poption'] == "NB"){ echo ' checked="checked"'; }?>/>
							<label class="form-check-label">Non-Billable</label>
						</div>
                </td>
            </tr>

            <tr id="billable_block" style="display:''">
                <td colspan="2" align="left" style="padding-left:0px; "  >
                    <table class="crmsummary-jocomp-table billable_blockNew">
                        <tr>
                            <td align="left" class="summaryform-bold-title summaryform_margin_left">Per Diem</td>
                            <td align="left">
								<div class="row align-items-end g-2">
									<div class="col-auto">
										<label class="form-label">Lodging:</label>
										<input type="text" name="txt_lodging" id="txt_lodging" size="5" class="form-control" onBlur="javascript:calculatePerDiem();" />
									</div>
									<div class="col-auto">
										<label class="form-label">M&amp;IE:</label>
										<input type="text" name="txt_mie" id="txt_mie"  size="5" class="form-control" onBlur="javascript:calculatePerDiem();"/>
									</div>
									<div class="col-auto">
										<label class="form-label">Total:</label>
										<input type="text" name="txt_total" id="txt_total"  size="10" class="form-control" onBlur="javascript:calculatePerDiem();" />
									</div>
									<div class="col-auto">
										<select name="sel_perdiem" onClick="getPreviousRate(this);" onChange="change_Period('selperdiem');" id="sel_perdiem" class="form-select">
											<!--UOM: get dynamic rate types-->
											<?php echo $timeOptions ?>
										</select> 
									</div>
									<div class="col-auto">
										<select name="sel_perdiem2" id="sel_perdiem2" class="form-select">
											<?php
											displayCurrency('');
											?>
										</select>
									</div>
									<div class="col-auto">
										<div class="form-check form-check-inline">
											<input class="form-check-input" type="radio" name="radio_taxabletype" id="radio_taxabletype" value="Y" >
											<label class="form-check-label">Taxable</label>
										</div>

										<div class="form-check form-check-inline">
											<input class="form-check-input" type="radio" name="radio_taxabletype" id="radio_taxabletype" value="N" checked="checked">
											<label class="form-check-label">Non-Taxable</label>
										</div>
									</div>
								</div>
							</td>
                        </tr>
                        <tr>
                            <td width="14%" align="left" valign="top" class="summaryform-bold-title summaryform_margin_left">&nbsp;</td>
                            <td align="left" valign="middle" colspan="5">
                                <div class="form-check form-check-inline">
                                    <input class="" type="radio" name="radio_billabletype" class="form-check-input" id="radio_billabletype" value="Y" onClick="javascript:showBillDiv(this,'txt_total');" />
									<label class="form-check-label">Billable</label>
								</div>
                                <div align="left" id="bill_Div" style="float:left; display: none;">
                                    &nbsp;<input type="text" name="diem_billrate" id="diem_billrate" size="8" maxlength="9" onBlur="javascript:isNumbervalidation(this,'Billrate');" value="" class="summaryform-formelement" />
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="radio_billabletype" id="radio_billabletype" value="N" checked="checked" onClick="javascript:showBillDiv(this);" />
									<label class="form-check-label">Non-Billable</label>
								</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr id="jobloc_deduct" style="display:block"> <td width="13%" class="summaryform-bold-title" nowrap>&nbsp;</td>
            <td width="87%">
                <span id="leftflt" class="summaryform-bold-title">
                    <input type="checkbox" name="use_jobloc_deduct" value='Y'>&nbsp;Use Job Location for Applicable Taxes
                </span>

            </td>
            </tr>
            <tr>
                <td width="13%" class="summaryform-bold-title"><?php echo (!empty($userFieldsAlias['Placement Fee']))?$userFieldsAlias['Placement Fee']:'Placement Fee';echo getRequiredStar('Placement Fee', $userFieldsArr, 'pfee');?></td>
             <td width="87%">
                <span id="leftflt">
                    <input name="pfee" type="text" id="pfee" value="" size=10 maxlength="9" class="summaryform-formelement">
                </span>
                <span id="leftflt">&nbsp;&nbsp;
                   <select name="pcurrency" id="pcurrency" class="summaryform-formelement">
                   <?php
                       displayCurrency('');
                   ?>
                   </select>
                </span>
             </td>
            </tr>
            <tr id="fieldForReferralBonus">
                
            </tr>
            <tr>
                <td colspan="2">
                    <span id="leftflt">
                        <span class="summaryform-bold-title">Commission&nbsp;/&nbsp;Splits</span>
                    </span>
                </td>
            </tr>
	    <tr>
		<td colspan="2">
				<table border="0" class="crmsummary-editsections-table_noline align-middle" width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="105" class="summaryform-nonboldsub-title" style="border-bottom: 0px solid #ddd;">Add Person:</td>
					<td style=" padding-left:10px" colspan="3">
					<div class="d-flex align-items-center">
				<span id="leftflt">
				    <select name="addemp" class="summaryform-formelement setcommrolesize" onChange="addCommission('newrow')">
					<option selected  value="">--select employee--</option>
					<?php
					while ($row = mysql_fetch_row($res))
					    print '<option ' . compose_sel($elements[13], $row[0]) . ' value="' . 'emp' . $row[0] . '|' . stripslashes($row[1]) . '">' . stripslashes($row[1]) . '</option>';
					?>
				    </select>
				</span>
						<span class="summaryform-formelement">&nbsp;|&nbsp;</span><a class="crm-select-link" href="javascript:parent_popup('comcontact')"><strong>select</strong> contact</a>&nbsp;<a href="javascript:parent_popup('comcontact')"><i class="fa fa-search fa-lg"></i></a><span class="summaryform-formelement">&nbsp;|&nbsp;</span><a class="crm-select-link" href="javascript:newConScreen('contact','comcontact')">new&nbsp;contact</a>
						</div>
			    </td>
			</tr>
			<tbody id="commissionRows">
			</tbody>
		    </table>
		<input type="hidden" name="empvalues">
		</td>
	    </tr>
	    <tr>
		<td colspan="2">
		    <span class="commRolesNoteStyle">
			    Note : If no role is selected for an employee, such records will not be saved.
		    </span>
		</td>
	    </tr>
            <tr>
                <td class="summaryform-bold-title" nowrap="nowrap">Payroll Provider ID#</td>
                <td>
                <span id="leftflt"><input class="summaryform-formelement" type="text" size=20 name="payrollid" maxlength="20"></span>		</td>
            </tr>
            <tr>
                <td class="summaryform-bold-title" nowrap="nowrap">Workers Comp Code<?php if(TRICOM_REPORTS=='Y'){echo $mandatory_madison;}?></td>
                <td>
                <span id="leftflt">
                <select class="summaryform-formelement" name="workcode" id="workcode" style="width:210px">
                    <!-- <option value=""> -- Select (Code-Title-State) -- </option> -->
                    <?php
                            getDispWCOptions($contact_fetch[7]); // Displaying workers compensation code
                    ?>
                    </select>
                    <?php
                        if(ENABLE_MANAGE_LINKS == 'Y')
                            echo '&nbsp;<a href="javascript:doAddWorkersCompCode(\'workcode\')" class="crm-select-link">Add</a>';
                    ?>
                    </span>
                </td>

            </tr>
            <tr>
                    <td width="167" class="summaryform-bold-title">Payment Terms</td>
                    <td>
                    <?php
                     $BillPay_Sql = "SELECT billpay_termsid, billpay_code FROM bill_pay_terms WHERE billpay_status = 'active' AND billpay_type = 'PT' ORDER BY billpay_code";
                     $BillPay_Res = mysql_query($BillPay_Sql,$db);
                    ?>
                    <select name="pterms" id="pterms" style="width:210px;">
                            <option value=""> -- Select -- </option>
                            <?php
                            while($BillPay_Data = mysql_fetch_row($BillPay_Res))
                            {
                            ?>
                                    <option value="<?=$BillPay_Data[0];?>" title="<?=html_tls_specialchars(stripslashes($BillPay_Data[1]),ENT_QUOTES);?>"><?=stripslashes($BillPay_Data[1]);?></option>
                            <?php
                            }
                            ?>
                    </select>
                    <?php
                            if(ENABLE_MANAGE_LINKS == 'Y')
                                    echo '&nbsp;&nbsp;&nbsp;<a href="javascript:doManageBillPayTerms(\'Payment\',\'pterms\')" class="edit-list">Manage</a>';
                    ?>

                    </td>
            </tr>
            <!-- <tr id="crm-joborder-billinginfotimesheet" name="crm-joborder-billinginfotimesheet">
                    <td class="summaryform-bold-title">Timesheet Approval</td>
                    <td>
                    <span id="leftflt" class="summaryform-nonboldsub-title"><input class="summaryform-formelement" type="radio" name="tapproval" id="tapproval" value="Manual" checked="checked">Manual</span>
                    <span id="leftflt" class="summaryform-nonboldsub-title">&nbsp;&nbsp;&nbsp;<input class="summaryform-formelement" type="radio" name="tapproval" id="tapproval" value="Online">Online</span>
                    <span id="leftflt" class="summaryform-nonboldsub-title">&nbsp;&nbsp;&nbsp;<input class="summaryform-formelement" type="radio" name="tapproval" value="Eemail">Email</span> 		</td>
            </tr> -->
            <span name="crm-joborder-billinginfotimesheet" id="crm-joborder-billinginfotimesheet" style="visibility: hidden;">
            	<span id="leftflt" class="summaryform-nonboldsub-title"><input class="summaryform-formelement" type="radio" name="tapproval" id="tapproval" value="Manual" checked="checked">Manual</span>
            	<span id="leftflt" class="summaryform-nonboldsub-title">&nbsp;&nbsp;&nbsp;<input class="summaryform-formelement" type="radio" name="tapproval" id="tapproval" value="Online">Online</span>
            </span>
            <tr>
                    <td width="167" class="summaryform-bold-title">PO Number</td>
                    <td>
                    <span id="leftflt"><input class="summaryform-formelement" type="text" name="po_num" size=20 value="" maxlength="255"></span></td>
            </tr>
            <tr>
                    <td width="167" class="summaryform-bold-title">Department</td>
                    <td>
                    <span id="leftflt"><input class="summaryform-formelement" type="text" name="joborder_dept" size=20 value="" maxlength="255"></span></td>
            </tr>

            <tr>
			        <?php
							$cus_username="select username from staffacc_cinfo where sno='".$billcompany."'";
							$cus_username_res=mysql_query($cus_username,$db);
							$cust_username=mysql_fetch_row($cus_username_res);
							$custusername=$cust_username[0];
							
							?>
                    <input type="hidden" name="billcompany_sno" id="billcompany_sno" value="<?php echo $billcompany;?>">
					<input type="hidden" name="billcompany_username" id="billcompany_username" value="<?php echo $custusername;?>">
                    <td class="summaryform-bold-title"><?php echo (!empty($userFieldsAlias['Billing Address']))?$userFieldsAlias['Billing Address']:'Billing Address';echo getRequiredStar('Billing Address', $userFieldsArr);?></td>
                    <td><span id="billdisp_comp">&nbsp;<input type="hidden" name="bill_loc" id="bill_loc"><a class="crm-select-link" href="javascript:bill_jrt_comp('bill')">select company</a>&nbsp;</span>&nbsp;<span id="billcomp_chgid">&nbsp;</span>
                    <?
                    if($billcontact>0 || $bill_loc>0)
                    {
                            ?>
                            <script>getCRMLocations('<?php echo $billcompany;?>','<?php echo $billcontact;?>','<?php echo $bill_loc;?>','bill');</script>
                            <?
                    }
                    ?>
                    </td>
            </tr>

            <tr>
                    <input type="hidden" name="billcontact_sno" id="billcontact_sno" value="<?php echo $billcontact;?>">
                    <td width="167" class="summaryform-bold-title"><?php echo (!empty($userFieldsAlias['Billing Contact']))?$userFieldsAlias['Billing Contact']:'Billing Contact';echo getRequiredStar('Billing Contact', $userFieldsArr);?></td>
                    <td>
                    <?
                    if($billcontact==0)
                    {
                            ?>
                            <span id="billdisp"><a class="crm-select-link" href="javascript:bill_jrt_cont('bill')" id="disp">select contact</a></span>
                            &nbsp;<span id="billchgid"><a href="javascript:bill_jrt_cont('bill')"><i class="fa fa-search fa-lg"></i></a><span class="summaryform-formelement">&nbsp;|&nbsp;</span><a class="crm-select-link" href="javascript:donew_add('bill')">new&nbsp;contact</a></span>
                            <?
                    }
                    else
                    {
                            ?>
                            <span id="billdisp"><a class="crm-select-link" href="javascript:contact_func('<?php echo $billcontact;?>','<?php echo $billcont_stat;?>','bill')"><?php echo $billcont;?></a></span>
                            &nbsp;<span id="billchgid"><span class=summaryform-formelement>(</span> <a class=crm-select-link href=javascript:bill_jrt_cont('bill')>change </a>&nbsp;<a href=javascript:bill_jrt_cont('bill')><i class="fa fa-search fa-lg"></i></a><span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class=crm-select-link href=javascript:donew_add('bill')>new</a><span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class="crm-select-link" href="javascript:removeContact('bill')">remove&nbsp;</a><span class=summaryform-formelement>&nbsp;)&nbsp;</span></span>
                            <?
                    }
                    ?>
                    </td>
            </tr>

		<tr>
            <td class="summaryform-bold-title" style="border-bottom:0px solid #DDDDDD" valign="top">Billing Terms</td>
            <td style="border-bottom:0px solid #DDDDDD">
			<?php
			 $BillPay_Sql = "SELECT billpay_termsid, billpay_code FROM bill_pay_terms WHERE billpay_status = 'active' AND billpay_type = 'BT' ORDER BY billpay_code";
			 $BillPay_Res = mysql_query($BillPay_Sql,$db);
			?>
			<select name="term_billing" id="term_billing" style="width:210px;">
				<option value=""> -- Select -- </option>
				<?php  
				while($BillPay_Data = mysql_fetch_row($BillPay_Res))
				{ 
				?>
					<option value="<?=$BillPay_Data[0];?>" title="<?=html_tls_specialchars(stripslashes($BillPay_Data[1]),ENT_QUOTES);?>"><?=stripslashes($BillPay_Data[1]);?></option>
				<?php 
				}
				?>
			</select>
			<?php 
				if(ENABLE_MANAGE_LINKS == 'Y')
					echo '&nbsp;&nbsp;&nbsp;<a href="javascript:doManageBillPayTerms(\'Billing\',\'term_billing\')" class="edit-list">Manage</a>';
			?>
			</td>
        </tr>
		<tr>
            <td class="summaryform-bold-title" style="border-bottom:0px solid #DDDDDD" valign="top">Service Terms</td>
            <td style="border-bottom:0px solid #DDDDDD"><textarea name="serv_term" rows="5" cols="64"></textarea></td>
        </tr>				 
		</table>
		</div>
		</div>
			
		<!-- Tabbed pane for billing info end-->
	<?php
	}
	?>
			<div class="form-back accordion-item">
		<table width="100%" border="0" class="crmsummary-edit-tablemin" id="crm-joborder-scheduleDiv-Table">		
			<tr>
				<td width="120" class="crmsummary-content-title">
				   <div id="crm-joborder-scheduleDiv-plus1"><a style='text-decoration: none;' href="javascript:classToggle('schedule','plus')"><span class="crmsummary-content-title">Schedule</span></a></div>
					<div id="crm-joborder-scheduleDiv-minus1" class="DisplayNone"><a  style='text-decoration: none;' href="javascript:classToggle('schedule','minus')"><span class="crmsummary-content-title">Schedule</span></a></div>				
				</td>
				<td>
			<td>
				<span id="rightflt" <?php echo $rightflt;?>>
				<span class="summaryform-bold-close-title" id="crm-joborder-scheduleDiv-close" style="display:none;width:auto;"><a style='text-decoration: none;width:auto;' onClick="classToggle('schedule','minus')"  href="#crm-joborder-scheduleDiv-plus">close</a></span>
				
				<span class="summaryform-bold-close-title" id="crm-joborder-scheduleDiv-open" style="width:auto;"><a style='text-decoration: none;width:auto;' onClick="classToggle('schedule','plus')"  href="#crm-joborder-scheduleDiv-minus"> open</a></span>
				
				<div class="form-opcl-btnleftside"><div align="left"></div></div>
				<div id="crm-joborder-scheduleDiv-plus"><span onClick="classToggle('schedule','plus')" class="form-op-txtlnk crmsummary-process-mousepointer"><b>+</b></span></div>
				<div id="crm-joborder-scheduleDiv-minus"  class="DisplayNone"><span onClick="classToggle('schedule','minus')" class="form-cl-txtlnk crmsummary-process-mousepointer"><b>-</b></span></div>
				<div class="form-opcl-btnrightside"><div align="left"></div></div>
				</span>	
				</td>

			</tr>
		</table>
		</div>
		<div class="jocomp-back DisplayNone" id="crm-joborder-scheduleDiv" name="crm-joborder-scheduleDiv" <?php echo $style_table;?>>
		<table width="100%" border="0" class="crmsummary-jocomp-table" cellpadding="0" cellspacing="0">		
		   <tr>			
			<td class="summaryform-bold-title"><?php echo (!empty($userFieldsAlias['Start Date']))?$userFieldsAlias['Start Date']:'Start Date';echo getRequiredStar('Start Date', $userFieldsArr);?></td>
		    <td colspan="3" align="left" style=" text-align:left"><select name="smonth" id="smonth" class="summaryform-formelement">
									<option value="">Month</option>
									<option  value="1">January</option>
									<option  value="2">February</option>
									<option  value="3">March</option>
									<option  value="4">April</option>
									<option  value="5">May</option>
									<option  value="6">June</option>
									<option  value="7">July</option>
									<option  value="8">August</option>
									<option  value="9">September</option>
									<option  value="10">October</option>
									<option  value="11">November</option>
									<option  value="12">December</option>
									</select>
									<select name="sday" id="sday" class="summaryform-formelement">
									<option value="">Day</option>
									<option  value="1">01</option>
									<option  value="2">02</option>
									<option  value="3">03</option>
									<option  value="4">04</option>
									<option  value="5">05</option>
									<option  value="6">06</option>
									<option  value="7">07</option>
									<option  value="8">08</option>
									<option  value="9">09</option>
									<option  value="10">10</option>
									<option  value="11">11</option>
									<option  value="12">12</option>
									<option  value="13">13</option>
									<option  value="14">14</option>
									<option  value="15">15</option>
									<option  value="16">16</option>
									<option  value="17">17</option>
									<option  value="18">18</option>
									<option  value="19">19</option>
									<option  value="20">20</option>
									<option  value="21">21</option>
									<option  value="22">22</option>
									<option  value="23">23</option>
									<option  value="24">24</option>
									<option  value="25">25</option>
									<option  value="26">26</option>
									<option  value="27">27</option>
									<option  value="28">28</option>
									<option  value="29">29</option>
									<option  value="30">30</option>
									<option  value="31">31</option>
									</select>
									<select name="syear" id="syear" class="summaryform-formelement">
										<OPTION VALUE="">Year</option>
							                        <?php echo $startYear;?>
									</select>
									<span id="josdatecal"><input type="hidden" name="josdatenew" id="josdatenew" value="" /><script language='JavaScript'> new tcal ({'formname':'conreg','controlname':'josdatenew'});</script></span>
									</td>
          </tr>
		<?php
		if($frompage != 'client')
		{
		?>
		<!-- Code for Due Date //-->
		   <tr>			
			<td width="119" class="summaryform-bold-title">Due&nbsp;Date</td>
		    <td colspan="3"><select name="duedatemonth" id="duedatemonth" class="summaryform-formelement">
									<option value="">Month</option>
									<option  value="1">January</option>
									<option  value="2">February</option>
									<option  value="3">March</option>
									<option  value="4">April</option>
									<option  value="5">May</option>
									<option  value="6">June</option>
									<option  value="7">July</option>
									<option  value="8">August</option>
									<option  value="9">September</option>
									<option  value="10">October</option>
									<option  value="11">November</option>
									<option  value="12">December</option>
							</select>
									<select name="duedateday" id="duedateday" class="summaryform-formelement">
									<option value="">Day</option>
									<option  value="1">01</option>
									<option  value="2">02</option>
									<option  value="3">03</option>
									<option  value="4">04</option>
									<option  value="5">05</option>
									<option  value="6">06</option>
									<option  value="7">07</option>
									<option  value="8">08</option>
									<option  value="9">09</option>
									<option  value="10">10</option>
									<option  value="11">11</option>
									<option  value="12">12</option>
									<option  value="13">13</option>
									<option  value="14">14</option>
									<option  value="15">15</option>
									<option  value="16">16</option>
									<option  value="17">17</option>
									<option  value="18">18</option>
									<option  value="19">19</option>
									<option  value="20">20</option>
									<option  value="21">21</option>
									<option  value="22">22</option>
									<option  value="23">23</option>
									<option  value="24">24</option>
									<option  value="25">25</option>
									<option  value="26">26</option>
									<option  value="27">27</option>
									<option  value="28">28</option>
									<option  value="29">29</option>
									<option  value="30">30</option>
									<option  value="31">31</option>
									</select>
									<select name="duedateyear" id="duedateyear" class="summaryform-formelement">
										<OPTION VALUE="">Year</option>
				                        			<?php echo $dueYear;?>
									</select>
									<span id="joduedatecal"><input type="hidden" name="joduedatenew" id="joduedatenew" value="" /><script language='JavaScript'> new tcal ({'formname':'conreg','controlname':'joduedatenew'});</script></span>
									</td>
          </tr>
		  <!-- End of Due Date //-->
		<?php
		}
		?>
		  		
		<tr class="jocomp-back DisplayNone" id="crm-joborder-scheduleDiv1" name="crm-joborder-scheduleDiv1">
			<td width="119" class="summaryform-bold-title"><?php echo (!empty($userFieldsAlias['Expected End Date']))?$userFieldsAlias['Expected End Date']:'Expected End Date';echo getRequiredStar('Expected End Date', $userFieldsArr);?></td>
			 <td colspan="3"><select name="emonth" id="emonth" class="summaryform-formelement">
									<option value="">Month</option>
									<option  value="1">January</option>
									<option  value="2">February</option>
									<option  value="3">March</option>
									<option  value="4">April</option>
									<option  value="5">May</option>
									<option  value="6">June</option>
									<option  value="7">July</option>
									<option  value="8">August</option>
									<option  value="9">September</option>
									<option  value="10">October</option>
									<option  value="11">November</option>
									<option  value="12">December</option>
									</select>
									<select id="eday" name="eday" class="summaryform-formelement">
									<option value="">Day</option>
									<option  value="1">01</option>
									<option  value="2">02</option>
									<option  value="3">03</option>
									<option  value="4">04</option>
									<option  value="5">05</option>
									<option  value="6">06</option>
									<option  value="7">07</option>
									<option  value="8">08</option>
									<option  value="9">09</option>
									<option  value="10">10</option>
									<option  value="11">11</option>
									<option  value="12">12</option>
									<option  value="13">13</option>
									<option  value="14">14</option>
									<option  value="15">15</option>
									<option  value="16">16</option>
									<option  value="17">17</option>
									<option  value="18">18</option>
									<option  value="19">19</option>
									<option  value="20">20</option>
									<option  value="21">21</option>
									<option  value="22">22</option>
									<option  value="23">23</option>
									<option  value="24">24</option>
									<option  value="25">25</option>
									<option  value="26">26</option>
									<option  value="27">27</option>
									<option  value="28">28</option>
									<option  value="29">29</option>
									<option  value="30">30</option>
									<option  value="31">31</option>
									</select>
									<select id="eyear" name="eyear" class="summaryform-formelement">
										<OPTION VALUE="">Year</option>
				                        			<?php echo $expectedEndYear	; ?>
									</select>
									<span id="joedatecal"><input type="hidden" name="joedatenew" id="joedatenew" value="" /><script language='JavaScript'> new tcal ({'formname':'conreg','controlname':'joedatenew'});</script></span>
									</td>
          </tr>
			<tr id="shiftname_time" <?php if(SHIFT_SCHEDULING_ENABLED == 'Y') { echo ' style="display:none" '; } ?> class="shiftnameCls">
                <td  class="summaryform-bold-title">Shift Name/ Time</td>
                <td  colspan="3">
                    <select name="new_shift_name" id="new_shift_name" class="summaryform-formelement" onChange="" style="width:128px !important;">
                    <option value="0|0">Select Shift</option>
                    <?php 
                    	$selShiftsQry = "SELECT sno,shiftname,shiftcolor FROM shift_setup WHERE shiftstatus='active' ORDER BY shiftname ASC ";
                    	$selShiftsRes = mysql_query($selShiftsQry,$db);
                    	if(mysql_num_rows($selShiftsRes)>0)
                    	{
	                    	while($selShiftsRow = mysql_fetch_array($selShiftsRes))
	                    	{
	                    ?>
	                        <option value="<?php echo $selShiftsRow['sno'].'|'.$selShiftsRow['shiftname']; ?>" title="<?php echo $selShiftsRow['shiftcolor'];?>"><?php echo $selShiftsRow['shiftname'];?></option>
                     <?php 
		                    }
		                }
	                    ?>
                    </select>
                    <select name="shift_start_time" id="shift_start_time" class="summaryform-formelement" onChange="">
                    <option value="0">Start Time</option>               
                    <?php echo display_Shift_Times(); ?>
                    </select>
                    <select name="shift_end_time" id="shift_end_time" class="summaryform-formelement" onChange="">
                    <option value="0">End Time</option>
                    <?php echo display_Shift_Times(); ?>
                    </select>
                  </td>                      
			</tr>
			<tr id="shiftname_note" <?php if(SHIFT_SCHEDULING_ENABLED == 'Y') { echo ' style="display:none" '; } ?>>
	                <td colspan="3">
	                    <span class="billInfoNoteStyle">Note : Shift details are auto populated in placement & assignment(s).</span>
	                </td>
	         </tr>
		<tr id="sch_hours">
			<td  class="summaryform-bold-title">Hours</td>
			<td colspan="3">
					<select name="Hrstype" id="Hrstype" class="summaryform-formelement" onChange="checkCustom(this.value)">
					<option  value="fulltime">Full Time&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
					<option  value="parttime">Part Time</option>
					</select>						   					
			</td>		
		</tr>

		<!-- OLD SHIFT SCHEDULING START-->
		<tr id='crm-joborder-hourscustom' <?php if(SHIFT_SCHEDULING_ENABLED == 'Y') { echo " style='display:none' "; } ?>>
		   <td colspan="4" class="ssremovepad" style="padding:0px">
		    <table border="0" width=100% cellspacing="0" cellpadding="0" id="crm-joborder-Tablehours">
			  <tr>
			    <td width="119"></td>
			    <td width="9%"></td>
			    <td width="12%"></td>
			    <td width="9%"></td>
			    <td width="2%"></td>
			   
			 <td width="9%" style="padding-left:0px; text-align:right"><span id="crm-joborder-custom_deleteall"><a href="#crm-joborder-custom_deleteall" class="edit-list" onClick="javascript:DelselectSchAll()">delete selected</a></span>
				 </td>
			    <td width="2%"><input type="checkbox" name="customcheckall" id="customcheckall" value="Y" class="summaryform-formelement" onClick="selectSchAll()" style="margin-top:4px;">	</td>
               
				<td colspan="2" ></td>
			    </tr>
			  <tr id="defRowday0">
  <td ></td>  
   <td class="summaryform-bold-title">Sunday</td>
   <td class="summaryform-bold-title">&nbsp;<input type=hidden name="defweekday[0]" id="defweekday[0]" value="Sunday"></td>
   <td ><select name="fr_hour0" id="fr_hour0" class="summaryform-formelement">
					<?php echo $DispTimes;?>
					</select></td>
  <td class="summaryform-bold-title">To</td>
  <td ><select name='to_hour0' id="to_hour0" class="summaryform-formelement">
    <?php echo $DispTimes;?>
  </select></td>
  <td><input type="checkbox" name="daycheck0" id="daycheck0" value="Y" class="summaryform-formelement " onClick="childSchAll();"></td>
  <td colspan="2" class="summaryform-bold-title" ></td>
			  </tr>
  <tbody id="JoborderAddTable-Sunday"></tbody>
  <tr id="defRowday1">
  <td></td>  
   <td class="summaryform-bold-title">Monday</td>
   <td class="summaryform-bold-title">&nbsp;<input type=hidden name="defweekday[1]" id="defweekday[1]" value="Monday"></td>
   <td><select name='fr_hour1' id='fr_hour1' class="summaryform-formelement">
	<?php echo $DispTimes;?>
	</select></td>
   <td class="summaryform-bold-title">To</td>
   <td><select name='to_hour1' id='to_hour1' class="summaryform-formelement">
     <?php echo $DispTimes;?>
   </select></td>
   <td><input type="checkbox" name="daycheck1" id="daycheck1" class="summaryform-formelement" value="Y" onClick="childSchAll();"></td>
   <td colspan="2" class="summaryform-bold-title"></td>
  </tr>
   <tbody id="JoborderAddTable-Monday"></tbody>
<tr id="defRowday2">
  <td></td>  
   <td  class="summaryform-bold-title">Tuesday</td>
   <td  class="summaryform-bold-title">&nbsp;<input type=hidden name="defweekday[2]" id="defweekday[2]" value="Tuesday"></td>
   <td><select name='fr_hour2' id="fr_hour2" class="summaryform-formelement">
		<?php echo $DispTimes;?>
		</select></td>


  <td class="summaryform-bold-title">To</td>
  <td><select name='to_hour2' id='to_hour2' class="summaryform-formelement">
    <?php echo $DispTimes;?>
  </select></td>
  <td ><input type="checkbox" name="daycheck2" id="daycheck2" class="summaryform-formelement" value="Y" onClick="childSchAll();"></td>
  <td colspan="2" class="summaryform-bold-title"></td>
		</tr>
		<tbody id="JoborderAddTable-Tuesday"></tbody>
<tr id="defRowday3">
  <td></td>  
   <td  class="summaryform-bold-title">Wednesday</td>
   <td  class="summaryform-bold-title">&nbsp;<input type=hidden name="defweekday[3]" id="defweekday[3]" value="Wednesday"></td>
   <td ><select name='fr_hour3' id="fr_hour3" class="summaryform-formelement">
		<?php echo $DispTimes;?>
		</select></td>
  <td class="summaryform-bold-title">To</td>
  <td ><select name='to_hour3' id='to_hour3' class="summaryform-formelement">
    <?php echo $DispTimes;?>
  </select></td>
  <td ><input type="checkbox" name="daycheck3" id="daycheck3" value="Y" class="summaryform-formelement" onClick="childSchAll();"></td>
  <td colspan="2" class="summaryform-bold-title"></td>
		</tr>
		<tbody id="JoborderAddTable-Wednesday"></tbody>
		<tr  id="defRowday4">
		  <td></td>  
		   <td   class="summaryform-bold-title">Thursday</td>
		   <td   class="summaryform-bold-title">&nbsp;<input type=hidden name="defweekday[4]" id="defweekday[4]" value="Thursday"></td>
		   <td><select name='fr_hour4' id="fr_hour4" class="summaryform-formelement">
			<?php echo $DispTimes;?>
			</select></td>
		  <td class="summaryform-bold-title">To</td>
		  <td><select name='to_hour4' id='to_hour4' class="summaryform-formelement">
			<?php echo $DispTimes;?>
		  </select></td>
		  <td><input type="checkbox" name="daycheck4" id="daycheck4" class="summaryform-formelement" value="Y" onClick="childSchAll();"></td>
		  <td colspan="2" class="summaryform-bold-title"></td>
				</tr>
		<tbody id="JoborderAddTable-Thursday"></tbody>
		<tr  id="defRowday5">
  <td></td>  
   <td  class="summaryform-bold-title">Friday</td>
   <td  class="summaryform-bold-title">&nbsp;<input type=hidden name="defweekday[5]" id="defweekday[5]" value="Friday"></td>
   <td><select name='fr_hour5' id='fr_hour5' class="summaryform-formelement">
		<?php echo $DispTimes;?>
		</select></td>
	<td class="summaryform-bold-title">To</td>
	<td><select name='to_hour5' id='to_hour5' class="summaryform-formelement">
	  <?php echo $DispTimes;?>
	</select></td>
	<td><input type="checkbox" name="daycheck5" id="daycheck5" class="summaryform-formelement" value="Y" onClick="childSchAll();"></td>
	<td colspan="2" class="summaryform-bold-title"></td>
		</tr>
		<tbody id="JoborderAddTable-Friday"></tbody>
		<tr  id="defRowday6">
  			<td ></td>  
			<td  class="summaryform-bold-title">Saturday</td>
			<td  class="summaryform-bold-title">&nbsp;<input type=hidden name="defweekday[6]" id="defweekday[6]" value="Saturday"></td>
			<td ><select name='fr_hour6' id="fr_hour6" class="summaryform-formelement">
					<?php echo $DispTimes;?>
					</select></td>
				  <td class="summaryform-bold-title">To</td>
				  <td ><select name='to_hour6' id='to_hour6' class="summaryform-formelement">
                    <?php echo $DispTimes;?>
                  </select></td>
				  <td><input type="checkbox" name="daycheck6" id="daycheck6" value="Y" class="summaryform-formelement" onClick="childSchAll();"></td>
				  <td colspan="2" class="summaryform-bold-title"></td>
		</tr>
		<tbody id="JoborderAddTable-Saturday"></tbody>
		 <tbody id="JoborderAddTable" align="left"></tbody>
		<tr>
		  <td class="crmsummary-jocompmin"></td>
		  <td  class="crmsummary-jocompmin" >
				    <select name="custaddrow_day" id="custaddrow_day" class="summaryform-formelement">					
						<option value=''>--select--</option>
						<option value='Sunday'>Sunday</option>
						<option value='Monday'>Monday</option>
						<option value='Tuesday'>Tuesday</option>
						<option value='Wednesday'>Wednesday</option>
						<option value='Thursday'>Thursday</option>
						<option value='Friday'>Friday</option>
						<option value='Saturday'>Saturday</option>
					</select></td>
		  <td  class="crmsummary-jocompmin"><input type="text" name='custaddrow_date' id='custaddrow_date' value="" class="summaryform-formelement" size="10" maxlength="10" readonly>
		  <script language='JavaScript'> new tcal ({'formname':'conreg','controlname':'custaddrow_date'});</script>
		  </td>
		  <td class="crmsummary-jocompmin">
					<select name='custaddrowfr_hour' id='custaddrowfr_hour' class="summaryform-formelement">
					<?php echo $DispTimes;?>
					</select></td>
		  <td class="crmsummary-jocompmin summaryform-bold-title">To</td>
		  <td class="crmsummary-jocompmin"><select name='custaddrowto_hour' id='custaddrowto_hour' class="summaryform-formelement">
            <?php echo $DispTimes;?>
          </select></td>
		  <td colspan="3" class="crmsummary-jocompmin" nowrap="nowrap"><a  href="#crm-joborder-scheduleDiv-minus" onClick="javascript:ScheduleRowCall()" class="crm-select-link crm-select-linkNewBg" >Add Row</a></td>
		  
		</tr>
			</table>		    </td>
		  </tr>
		<!-- OLD SHIFT SCHEDULING END-->
		
		
		<!-- NEW SHIFT SCHEDULING START-->
		<tr id="sch_calendar" <?php if(SHIFT_SCHEDULING_ENABLED == 'N') { echo ' style="display:none" '; } ?>>
			<td class="summaryform-bold-title" colspan="3">
				<?php echo $objSchSchedules->displayShiftScheduleWithAddLink('jo_shiftsch', 'No','joborder'); ?>
			</td>
		</tr>
	

		<tr style="height: 10px;"><td colspan="3"></td></tr>
		<tr>
			<td colspan="3" id="joTimeFrameData">
			<input type="hidden" name="sm_module" id="sm_module" value="joborder" />
			<input type="hidden" name="sm_shiftmode" id="sm_shiftmode" value="AddIndShifts" />
			<?php
			unset($_SESSION['newShiftTotalArrayValues']);
			$shift_schedule_module = "jobsummary";
			$displayTimeFrameGrid = 'N';
			include($app_inc_path."shift_schedule/timeFrameView.php");
			?>			
			<script>
			if($("#getcalsel_dates").val() == "")
			{
				$("#dateSelGridDiv").hide();
				$("#shiftschAddEdit").hide();
				$("#jo_shiftsch").prop("checked",false);
			}
			</script>
		</td>
		</tr>
		<?php if(SHIFT_SCHEDULING_ENABLED == 'Y') { ?>
		<!-- Perdiem Shift Scheduling Start -->
		<tr style="height: 10px;"><td colspan="3"></td></tr>
		<tr>
			<td class="summaryform-bold-title" colspan="3" style="padding:0px;">
				<div id="perdiemShiftSchedule" style="display:none;">

				</div>
			</td>	
		</tr>
		<tr style="height: 10px;"><td colspan="3"></td></tr>
		<!-- Perdiem Shift Scheduling END -->
		<?php } ?>
		<!-- NEW SHIFT SCHEDULING END-->		
		
		</table>
		
		</div>
			<div class="form-back accordion-item">
			<table width="100%" border="0" class="crmsummary-edit-tablemin" id="crm-joborder-descriptionDiv-Table">		
				<tr>
				<td width="120" class="crmsummary-content-title">
					<div id="crm-joborder-descriptionDiv-plus1"><a style='text-decoration: none;' href="javascript:classToggle('description','plus')"><span class="crmsummary-content-title">Description</span></a></div>
					<div id="crm-joborder-descriptionDiv-minus1" class="DisplayNone"><a  style='text-decoration: none;' href="javascript:classToggle('description','minus')"><span class="crmsummary-content-title">Description</span></a></div>			
				
				</td>
				<td>
			<td>
					<span id="rightflt" <?php echo $rightflt;?>>
					<span class="summaryform-bold-close-title" id="crm-joborder-descriptionDiv-close" style="display:none;width:auto;"><a style='text-decoration: none;width:auto;' onClick="classToggle('description','minus')"  href="#crm-joborder-descriptionDiv-plus" >close</a></span>					
					<span class="summaryform-bold-close-title" id="crm-joborder-descriptionDiv-open" style="width:auto;"><a style='text-decoration: none;width:auto;' onClick="classToggle('description','plus')"  href="#crm-joborder-descriptionDiv-minus"> open</a></span>
					
					<div class="form-opcl-btnleftside"><div align="left"></div></div>
					<div id="crm-joborder-descriptionDiv-plus"><span onClick="classToggle('description','plus')" class="form-op-txtlnk crmsummary-process-mousepointer"><b>+</b></span></div>
					<div id="crm-joborder-descriptionDiv-minus"  class="DisplayNone"><span onClick="classToggle('description','minus')" class="form-cl-txtlnk crmsummary-process-mousepointer"><b>-</b></span></div>
					<div class="form-opcl-btnrightside"><div align="left"></div></div>
					</span>
			</td>
			</tr>
		</table>
		</div>
		<div class="jocomp-back DisplayNone" id="crm-joborder-descriptionDiv" name="crm-joborder-descriptionDiv" <?php echo $style_table;?>>
		<table width="100%" border="0" class="crmsummary-jocomp-table">		
		<tr>
			<td width="120" class="summaryform-bold-title">Meta Keywords<br/>(for SEO)</td>
			<td><span class="summaryform-formelement"><textarea name="meta_keywords" id="meta_keywords" cols="68" rows="2" maxlength="50" onkeyup="return ismaxlength(this,50);" onKeyDown="return ismaxlength(this,50);"></textarea></span><br/><i class="summaryform-formelement">(Max 50 characters allowed) A comma separated list of your most important keywords for this job page that will be written as META keywords so that candidates searching for these keywords online will find this job opening to apply.</i></td>
		</tr>
		<tr>
			<td width="120" class="summaryform-bold-title">Meta Description<br/>(for SEO)</td>
			<td><span class="summaryform-formelement"><textarea name="meta_desc" id="meta_desc" cols="68" rows="2" maxlength="160" onkeyup="return ismaxlength(this,160);" onKeyDown="return ismaxlength(this,160);"></textarea></span><br/><i class="summaryform-formelement">(Max 160 characters allowed) The META description for this job page. This is not the job description. Search engines use this to find this job page to display to candidates searching for similar jobs.</i></td>
		</tr>
		<tr>
			<td width="120" class="summaryform-bold-title"><?php echo (!empty($userFieldsAlias['Position Summary']))?$userFieldsAlias['Position Summary']:'Position Summary';echo getRequiredStar('Position Summary', $userFieldsArr);?></td>
			<td><span class="summaryform-formelement">
			  <textarea name="posdesc" id="posdesc" cols="67" rows="10" style="height:300px;"></textarea>
			</span></td>
		</tr>	
		<tr>
			<td width="120" class="summaryform-bold-title">Requirements<?php echo getRequiredStar('Description Requirement', $userFieldsArr);?></td>
			<td><textarea class="form-control" name="requirements" id="requirements" cols="67" rows="10"></textarea></td>
		</tr>	
		<tr>
			<td width="120" class="summaryform-bold-title">Education</td>
			<td><span class="summaryform-formelement"><input name="education"  type="text" id="education"/>
			</span></td>
		</tr>
		<tr>
			<td width="120" class="summaryform-bold-title">Years of Experience</td>
			<td><span class="summaryform-formelement"><input name="experience"  type="text" id="experience"/>
			</span></td>
			
		</tr>		
		</table>
		</div>
					<div class="form-back accordion-item">
			<table width="100%" border="0" class="crmsummary-edit-tablemin" id="crm-joborder-skillsDiv-Table">		
				<tr>
				<td width="120" class="crmsummary-content-title">
					<div id="crm-joborder-skillsDiv-plus1"><a style='text-decoration: none;' href="javascript:classToggle('skills','plus')"><span class="crmsummary-content-title">Skills</span></a></div>
					<div id="crm-joborder-skillsDiv-minus1" class="DisplayNone"><a  style='text-decoration: none;' href="javascript:classToggle('skills','minus')"><span class="crmsummary-content-title">Skills</span></a></div>					
				</td>
				<td>
			<td>
				 <span id="rightflt" <?php echo $rightflt;?>>
				  <span class="summaryform-bold-close-title" id="crm-joborder-skillsDiv-close" style="display:none;width:auto;"><a style='text-decoration: none;width:auto;' onClick="classToggle('skills','minus')"  href="#crm-joborder-skillsDiv-plus">close</a></span>
				 <span class="summaryform-bold-close-title" id="crm-joborder-skillsDiv-open" style="width:auto;"><a style='text-decoration: none;' onClick="classToggle('skills','plus')"  href="#crm-joborder-skillsDiv-minus">open</a></span>
                     <div class="form-opcl-btnleftside"><div align="left"></div></div>
                     <div id="crm-joborder-skillsDiv-plus" ><span onClick="classToggle('skills','plus')" class="form-op-txtlnk crmsummary-process-mousepointer"><b>+</b></span></div>
					 <div id="crm-joborder-skillsDiv-minus" class="DisplayNone"><span onClick="classToggle('skills','minus')" class="form-cl-txtlnk crmsummary-process-mousepointer" ><b>-</b></span></div>
                     <div class="form-opcl-btnrightside"><div align="left"></div></div>
                 </span>			</td>
			</tr>
		</table>
		</div>
		<div class="jocomp-back DisplayNone" id="crm-joborder-skillsDiv" name="crm-joborder-skillsDiv" <?php echo $style_table;?>>
			<?php
			if($frompage == 'client')
			{
			?>
			<table width="100%" border="0" class="crmsummary-jocomp-table">		
				<tr>
				<td colspan="2"><span class="summaryform-bold-title">Primary</span><span class="summaryform-formelement">(used in candidate search)</span>&nbsp;&nbsp;&nbsp;<a href="#crm-joborder-skillsDiv" onClick="javascript:delSkill()" class="edit-list">Delete&nbsp;selected</a></td>
				</tr>
				<tr>
					<td colspan="4" align="left" style="border-bottom: 0px solid #ddd;">
					<!---=======================================================-->
					
					<table width=99% border=0 cellpadding=0 cellspacing=0 >
						<tbody  id='mainSkillTable'>
						<tr id="mainHeader"><td width=4%><input type=checkbox name=selChk id=selChk onClick='selAll()' ></td><td width=15% align=left class="summaryform-bold-title">Skill Name</td>
						<td width=13% align=left class="summaryform-bold-title">Last Used</td>
						<td width=13% align=left class="summaryform-bold-title">Skill Level</td>
						<td width=8% align=left class="summaryform-bold-title">Years of Experience</td>
						 <td><div id='NotesAdd' class="smform-txtlnk"  style="cursor:pointer" onClick="javascript:addSkillsRow('newrow')" onMouseOver="changeOverColor('NotesAdd')" onMouseOut="changeOutColor('NotesAdd')" >Add</div></td>
						<td width="47%" align='left'>&nbsp;&nbsp;&nbsp;&nbsp; </td>
						</tr>				
						 
								 <tr><td colspan=6 align="left">
								</td>
								 </tr>
								<tbody id="skillTable" align="left"></tbody>
					  </tbody></table>
						  <!---=======================================================-->
					</td>
				</tr>
			</table>
			<?php
			}
			else
			{			
			?>
			<table width="100%" border="0" class="crmsummary-jocomp-table">		
				<tr>
					<td colspan="2">
						<span class="summaryform-bold-title">Primary</span>
						<span class="summaryform-formelement">(used in candidate search)</span>
					<!--             
					 &nbsp;&nbsp;&nbsp;
						    <a href="#crm-joborder-skillsDiv" onClick="javascript:delSkill()" class="edit-list">Delete&nbsp;selected</a>
					-->
					</td>
				</tr>	
				<!-- Skill Management Enhancement -->
				<tr>
					<td colspan="8">
						<fieldset align="left" style="margin:10px 0px">
							<legend><font class="afontstyle">Departments</font></legend>
	              			<div class=" afontstyle skilsmanageNew" id="skillDepartment"> 
	              				
	              			</div>
	              			
	                	</fieldset>
	            	</td>
				</tr>

				<tr>
					<td colspan="8">
						<fieldset align="left" style="margin:10px 0px">
							<legend><font class="afontstyle">Categories</font></legend>
	              			<div class=" afontstyle skilsmanageNew" id="skillCategories"> 
	              				
	              			</div>
	              			
	                	</fieldset>
	            	</td>
				</tr>
				
				<tr>
					<td colspan="8">
						<fieldset align="left" style="margin:10px 0px">
							<legend><font class="afontstyle">Specialties</font></legend>
	              			<div class=" afontstyle skilsmanageNew" id="skillSpecialities"> 
	              				
	              			</div>
	              			
	                	</fieldset>
	            	</td>
				</tr>
				<tr>
					
					<td colspan="4" align="left" style="border-bottom: 0px solid #ddd;">
					<!---=======================================================-->
					<fieldset align="left" style="margin:10px 0px">
						<legend><font class="afontstyle">Skills</font></legend>
						<table width="99%" border="0" cellpadding="0" cellspacing="0" id="joborderskills" >
							<tbody  id='mainSkillTable'>
								<tr id="mainHeader">
									<!--<td width=4%><input type=checkbox name=selChk id=selChk onClick='selAll()' ></td>-->
									<td></td>
									<td width=15% align=left class="summaryform-bold-title">Skill Name</td>
									<td width=13% align=left class="summaryform-bold-title">Last Used</td>
									<td width=13% align=left class="summaryform-bold-title">Skill Level</td>
									<td width=8% align=left class="summaryform-bold-title">Years of Experience</td>
									<td width="51%">
										<!--<div id='NotesAdd' class="smform-txtlnk"  style="cursor:pointer" onClick="javascript:addSkillsRow('newrow')" onMouseOver="changeOverColor('NotesAdd')" onMouseOut="changeOutColor('NotesAdd')" >Add</div>-->
										<a href="javascript:getManagedSkills()" class="edit-list">Add/Edit&nbsp;Skills/Departments/Categories/Specialties</a>
										<input type="hidden" id="jototskills" value="1">
										<input type="hidden" id="selectedskillsids" value="">
										<input type="hidden" id="removedskillsids" value="">
									</td>
									<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp; </td>
								</tr>
								<!--<tbody id="skillTable" align="left"></tbody>-->
							</tbody>					
						</table>
				</fieldset>
				  		<!---=======================================================-->			
				  	</td>
					</tr>
			</table>
						
		<?php
			}
			?>
			
		</div>
		<div class="form-back accordion-item">
	<table width="100%" border="0" class="crmsummary-edit-tablemin" id="crm-joborder-hrprocessDiv-Table">		
		<tr>
				<td width="120" class="crmsummary-content-title">
					<div id="crm-joborder-hrprocessDiv-plus1"><a style='text-decoration: none;' href="javascript:classToggle('hrprocess','plus')"><span class="crmsummary-content-title">Hiring Process</span></a></div>
					<div id="crm-joborder-hrprocessDiv-minus1" class="DisplayNone"><a  style='text-decoration: none;' href="javascript:classToggle('hrprocess','minus')"><span class="crmsummary-content-title">Hiring Process</span></a></div>				
				
				</td>
				<td>
			<td>
				 <span id="rightflt" <?php echo $rightflt;?>>
				     <span class="summaryform-bold-close-title" id="crm-joborder-hrprocessDiv-close"  style="display:none;width:auto;"><a style='text-decoration: none;' onClick="classToggle('hrprocess','minus')"  href="#crm-joborder-hrprocessDiv-plus">close</a></span>
					 <span class="summaryform-bold-close-title" id="crm-joborder-hrprocessDiv-open" style="width:auto;"><a style='text-decoration: none;' onClick="classToggle('hrprocess','plus')"  href="#crm-joborder-hrprocessDiv-minus">open</a></span>
                     <div class="form-opcl-btnleftside"><div align="left"></div></div>
                     <div id="crm-joborder-hrprocessDiv-plus"><span onClick="classToggle('hrprocess','plus')" class="form-op-txtlnk crmsummary-process-mousepointer"><b>+</b></span></div>
					 <div id="crm-joborder-hrprocessDiv-minus" class="DisplayNone"><span onClick="classToggle('hrprocess','minus')" class="form-cl-txtlnk crmsummary-process-mousepointer"><b>-</b></span></div>
                     <div class="form-opcl-btnrightside"><div align="left"></div></div>
                 </span>			</td>
			</tr>
		</table>
		</div>
		<div class="jocomp-back DisplayNone" id="crm-joborder-hrprocessDiv" name="crm-joborder-hrprocessDiv" <?php echo $style_table;?>>
		<table width="100%" border="0" class="crmsummary-jocomp-table align-middle">		
		<tr>
			<td width="120" class="summaryform-bold-title">Contact Method</td>
			<td>
			 	
				<div class="form-check form-check-inline">
					<input name="hpcmphone" class="form-check-input" type="checkbox" id="hpcmphone" value="phone">
					<label class="form-check-label">Phone</label>
				</div>
				<div class="form-check form-check-inline">
					<input class="form-check-input" name="hpcmmobile" type="checkbox" id="hpcmmobile" value="Mobile">
					<label class="form-check-label">Mobile</label>
				</div>
				<div class="form-check form-check-inline">
					<input class="form-check-input" name="hpcmfax" type="checkbox" id="hpcmfax" value="Fax">
					<label class="form-check-label">Fax</label>
				</div>
				<div class="form-check form-check-inline">
					<input class="form-check-input" name="hpcmemail" type="checkbox" id="hpcmemail" value="Email">
					<label class="form-check-label">Email</label>
				</div>			
			</td>
		</tr>
		<tr>
			<td valign="top"><span class="summaryform-bold-title"><?php echo (!empty($userFieldsAlias['Requirements']))?$userFieldsAlias['Requirements']:'Requirements';echo getRequiredStar('Requirements', $userFieldsArr);?></span></td>
			<td>
			<div class="d-flex align-items-center">
				<div class="form-check form-check-inline">
					<input class="form-check-input" name="hprresume" type="checkbox" id="hprresume" value="Resume">
					<label class="form-check-label">Resume </label>
				</div>
				<div class="form-check form-check-inline">
					<input class="form-check-input" name="hprpinterview" type="checkbox" id="hprpinterview" value="Pinterview">
					<label class="form-check-label">Phone Interview </label>
				</div>	
				<div class="form-check form-check-inline">
					<input class="form-check-input" name="hprinterview" type="checkbox" id="hprinterview" value="Interview">
					<label class="form-check-label">Interview</label>
				</div>
				<span class="summaryform-formelement">(avg #<input name="hpraverage" type="text" class="form-control" id="hpraverage" value="" size=15 maxlength="2">
				)</span>
			</div>
			<div class="clearfix"></div>
			<div class="d-flex align-items-center mt-2">
				<div class="form-check form-check-inline">
					<input class="form-check-input" name="hprbcheck" type="checkbox" id="hprbcheck" value="Bcheck">
					<label class="form-check-label">Background Check</label>
				</div>
				<div class="form-check form-check-inline">
					<input class="form-check-input" name="hprdscreen" type="checkbox" id="hprdscreen" value="Dscreen">
					<label class="form-check-label">Drug Screen</label>
				</div>
				<div class="form-check form-check-inline">
					<input class="form-check-input" name="hprphysical" type="checkbox" id="hprphysical" value="Physical">
					<label class="form-check-label">Physical</label>
				</div>
				<div class="form-check form-check-inline">
					<input class="form-check-input" name="hprgclearance" type="checkbox" id="hprgclearance" value="Gclearance">
					<label class="form-check-label">Govt Clearance</label>
				</div>
			</div>
			<div class="clearfix"></div>
				<div class="d-flex align-items-center mt-2">
					<div class="form-check form-check-inline">
						<input class="form-check-input" name="hpraddinfocb" type="checkbox" id="hpraddinfocb" value="Addinfo">
						<label class="form-check-label">Additional Info </label>
					</div>
					<input name="hprainfotb" type=text class="summaryform-formelement" id="hprainfotb" value="" size=50 maxlength=100 maxsize=100>
				</div>
			</td>
		</tr>			
		</table>
		</div>
                <?php if(JOB_QUESTIONNAIRE_ENABLED=='Y') { ?>
		<div class="form-back accordion-item">
		<table width="100%" border="0" class="crmsummary-edit-tablemin" id="crm-joborder-questionnaireDiv-Table">		
			<tr>
				<td width="120" class="crmsummary-content-title">
					<div id="crm-joborder-questionnaireDiv-plus1"><a style='text-decoration: none;' href="javascript:classToggle('questionnaire','plus')"><span class="crmsummary-content-title">Questionnaire</span></a></div>
					<div id="crm-joborder-questionnaireDiv-minus1" class="DisplayNone"><a  style='text-decoration: none;' href="javascript:classToggle('questionnaire','minus')"><span class="crmsummary-content-title">Questionnaire</span></a></div>				
				
				</td>
				<td>
			<td>
				 <span id="rightflt" <?php echo $rightflt;?>>
				     <span class="summaryform-bold-close-title" id="crm-joborder-questionnaireDiv-close"  style="display:none;width:auto;"><a style='text-decoration: none;' onClick="classToggle('questionnaire','minus')"  href="#crm-joborder-questionnaireDiv-plus">close</a></span>
					 <span class="summaryform-bold-close-title" id="crm-joborder-questionnaireDiv-open" style="width:auto;"><a style='text-decoration: none;' onClick="classToggle('questionnaire','plus')"  href="#crm-joborder-questionnaireDiv-minus">open</a></span>
                     <div class="form-opcl-btnleftside"><div align="left"></div></div>
                     <div id="crm-joborder-questionnaireDiv-plus"><span onClick="classToggle('questionnaire','plus')" class="form-op-txtlnk crmsummary-process-mousepointer"><b>+</b></span></div>
					 <div id="crm-joborder-questionnaireDiv-minus" class="DisplayNone"><span onClick="classToggle('questionnaire','minus')" class="form-cl-txtlnk crmsummary-process-mousepointer"><b>-</b></span></div>
                     <div class="form-opcl-btnrightside"><div align="left"></div></div>
                 </span>			</td>
			</tr>
		</table>
		</div>
		<div class="jocomp-back DisplayNone" id="crm-joborder-questionnaireDiv" name="crm-joborder-questionnaireDiv" <?php echo $style_table;?>>
		<table width="100%" border="0" class="crmsummary-jocomp-table">		
		<tr>
			<td width="120" class="summaryform-bold-title">Select Group</td>
			<td>
                            <select class="summaryform-formelement" style="width:350px !important;" onchange="getQuestionsByGroupId(this.value);" name="questionnaireGroup" id="questionnaireGroup">
                            <option value=""> -- Select Questionnaire Group-- </option>
                            <?php  
                                  echo  getActiveQuestionnaireGroups();
                            ?>
                            </select>
			</td>
		</tr>
                <tr id="displayQuestByGroupId">
                    
                </tr>
               </table>
		</div>
                <?php } ?>
             
		<div class="form-back accordion-item">
		<table width="100%" border="0" class="crmsummary-edit-tablemin" id="crm-joborder-relocationDiv-Table">		
			<tr>
				<td width="200" class="crmsummary-content-title">
					<div id="crm-joborder-relocationDiv-plus1"><a style='text-decoration: none;' href="javascript:classToggle('relocation','plus')"><span class="crmsummary-content-title">Travel/Relocation Requirements</span></a></div>
					<div id="crm-joborder-relocationDiv-minus1" class="DisplayNone"><a  style='text-decoration: none;' href="javascript:classToggle('relocation','minus')"><span class="crmsummary-content-title">Travel/Relocation Requirements</span></a></div>				
				</td>
				<td>
			
				 <span id="rightflt" <?php echo $rightflt;?>>
				  <span class="summaryform-bold-close-title" id="crm-joborder-relocationDiv-close" style="display:none;width:auto;"><a style='text-decoration: none;width:auto;' onClick="classToggle('relocation','minus')"  href="#crm-joborder-relocationDiv-plus">close</a></span>					
					 <span class="summaryform-bold-close-title" id="crm-joborder-relocationDiv-open" style="width:auto;"><a style='text-decoration: none;width:auto;' onClick="classToggle('relocation','plus')"  href="#crm-joborder-relocationDiv-minus">open</a></span>
                     <div class="form-opcl-btnleftside"><div align="left"></div></div>
                     <div id="crm-joborder-relocationDiv-plus" ><span onClick="classToggle('relocation','plus')" class="form-op-txtlnk crmsummary-process-mousepointer"><b>+</b></span></div>
					 <div id="crm-joborder-relocationDiv-minus" class="DisplayNone"><span onClick="classToggle('relocation','minus')" class="form-cl-txtlnk crmsummary-process-mousepointer"><b>-</b></span></div>
                     <div class="form-opcl-btnrightside"><div align="left"></div></div>
                 </span>			</td>
			</tr>
		</table>
		</div>
		<div class="jocomp-back DisplayNone" id="crm-joborder-relocationDiv" name="crm-joborder-relocationDiv" <?php echo $style_table;?>>
		<table width="100%" border="0" class="crmsummary-jocomp-table align-middle">		
		<tr>
			<td valign="top"><span class="summaryform-bold-title">Travel</span></td>
			<td valign="baseline">
			
			<div class="form-check form-check-inline">
				<input class="form-check-input" type="radio" name="trtravel" id="trtravel" value="Yes">
				<label class="form-check-label">Yes </label>
			</div>
			<div class="form-check form-check-inline">
				<input class="form-check-input" name="trtravel" id="trtravel" type="radio" checked value="No">&nbsp;
				<label class="form-check-label">No &nbsp;|</label>
			</div>
				<input name="trtravelpercentage" type="text" id="trtravelpercentage" value="" size=5>
			<span class="summaryform-bold-title">&nbsp;&nbsp;% &nbsp;&nbsp;|</span>
			<span class="summaryform-bold-title">Other &nbsp;</span>&nbsp;
				<input name="trtravelother" type="text" id="trtravelother" value="" size=29>
		    </td>
		</tr>
		<tr>
			<td valign="top"><span class="summaryform-bold-title">Relocation</span></td>
			<td valign="middle">
			<div class="form-check form-check-inline">
				<input class="form-check-input" type="radio" name="trrelocation" id="trrelocation"  value="Yes">
				<label class="form-check-label">Yes </label>
			</div>
			
			<div class="form-check form-check-inline">
				<input class="form-check-input" type="radio" name="trrelocation" id="trrelocation" checked value="No">&nbsp;
				<label class="form-check-label">No &nbsp;|</label>
			</div>
			<span class="summaryform-bold-title">City</span>
			<input name="trrelocationcity" type="text" id="trrelocationcity" value="" size=10>
			<span class="summaryform-bold-title">&nbsp;State </span>
			<input name="trrelocationstate" type="text" id="trrelocationstate" value="" size=5>
			<span class="summaryform-bold-title">&nbsp;Country </span>
			 <select name="trrelocationcounty" id="trrelocationcounty">
			 <option selected value=''>--Select--</option>
			 <?php echo $ContryOptions;?>
			 </select>
    	      </td>
		</tr>

		<tr>
			<td valign="top"><span class="summaryform-bold-title">Commute</span></td>
			<td valign="middle">
				<input name="trcommutehrs" type="text" id="trcommutehrs" value="" size=5>
				<span class="summaryform-bold-title" >&nbsp;hrs&nbsp;| &nbsp; </span>
				<input name="trcommutemiles" type="text" id="trcommutemiles" value="" size=5>
				<span class="summaryform-bold-title" >&nbsp;miles &nbsp;| &nbsp;</span>
				<span class="summaryform-bold-title">Other &nbsp; </span>
				<input name="trcommuteother" type="text" id="trcommuteother" value="" size=32>
			</td>
		</tr>
		</table>
		</div>
</fieldset>
</div>
</form>
<input type="hidden" id="payrate_calculate_confirmation" value="yes" />
<input type="hidden" id="billrate_calculate_confirmation" value="yes" />
<input type="hidden" id="cap_separated_custom_rates" value="" />
<input type="hidden" id="payrate_calculate_confirmation_window_onblur" value="" />
<input type="hidden" id="billrate_calculate_confirmation_window_onblur" value="" />
<input type=hidden name="confirmstate" id="confirmstate" value="edit">
<!-- new similar separate fields added for payrate and billrate calculation using calculator-->
<input type="hidden" id="payrate_new_calculate_confirmation_window_onblur" value="" />
<input type="hidden" id="billrate_new_calculate_confirmation_window_onblur" value="" />
<input type="hidden" id="previous_pay_rate" value="" />
<input type="hidden" id="previous_bill_rate" value="" />
<?php include('footer.inc.php') ?>
<script type="text/javascript" src="/BSOS/scripts/shift_schedule/jquery.modalbox.js"></script>
<link type="text/css" rel="stylesheet" href="/BSOS/css/gigboard/select2_V_4.0.3.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/shift_schedule/shiftcolors.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/gigboard/gigboardCustom.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/select2_shift.css">
<script type="text/javascript" src="/BSOS/scripts/gigboard/select2_V_4.0.3.js"></script>
<script>
setFormObject("document.conreg");
defultFullTime();
multipleRatesStr = "<?php echo $ratesObj->getMultipleRatesType();?>";
displayBlankSkills();
</script>
<script>
$(window).focus(function() {
    if(document.getElementById('payrate_calculate_confirmation_window_onblur').value != ""){
        document.getElementById('payrate_calculate_confirmation').value = document.getElementById('payrate_calculate_confirmation_window_onblur').value;
    }
    if(document.getElementById('billrate_calculate_confirmation_window_onblur').value != ""){
        document.getElementById('billrate_calculate_confirmation').value = document.getElementById('billrate_calculate_confirmation_window_onblur').value;
    }
});
if(document.getElementById('compoppr_id').value != "")
{
	$(window).bind("load", function() {
		Jobtype('');
	});
}
//function to handle the error notifications for all rates.
$(document).on('keypress', "input[type='text']",function (e) {
	
	if ($(this).hasClass("RateErrorInput")) {
		var key;
		var isCtrl = false;
		var keychar;
		var regExp;
		var flag = false;
	
		if(window.event) {
				key = e.keyCode;
				isCtrl = window.event.ctrlKey
		}
		else if(e.which) {
				key = e.which;
				isCtrl = e.ctrlKey;
		}
		if (isNaN(key)){
			flag = true;
		}
		keychar = String.fromCharCode(key);
		// check for backspace or delete, or if Ctrl was pressed
		if (key == 8 || isCtrl)
		{
			flag = true;
		}
		regExp = /\d/;			
		if (regExp.test(keychar) || flag==true) {
			$(this).removeClass("RateErrorInput");
		}		
	}
});

$(document).on("change", ".BillableRates" , function() {
	var chkBillable = $(this).val();
	if (chkBillable == "N") {
		//console.log($(this).attr('id'));
		if ($(this).attr('id')=="payrateBillOpt") {
			if ($("#comm_payrate").hasClass("RateErrorInput")) {
				$("#comm_payrate").removeClass("RateErrorInput")
			}
			if ($("#comm_billrate").hasClass("RateErrorInput")) {
				$("#comm_billrate").removeClass("RateErrorInput")
			}			
		}
		else
		if ($(this).attr('id')=="OvpayrateBillOpt") {
			if ($("#otrate_pay").hasClass("RateErrorInput")) {
				$("#otrate_pay").removeClass("RateErrorInput")
			}
			if ($("#otrate_bill").hasClass("RateErrorInput")) {
				$("#otrate_bill").removeClass("RateErrorInput")
			}	
		}
		else
		if ($(this).attr('id')=="DbpayrateBillOpt") {
			if ($("#db_time_pay").hasClass("RateErrorInput")) {
				$("#db_time_pay").removeClass("RateErrorInput")
			}
			if ($("#db_time_bill").hasClass("RateErrorInput")) {
				$("#db_time_bill").removeClass("RateErrorInput")
			}				
		}
		else{
			var custRateId = $(this).attr('id');
			var custRateIndx = custRateId.substring(12,custRateId.length);
			var custPayRateName = "mulpayRateTxt"+custRateIndx;
			var custBillRateName = "mulbillRateTxt"+custRateIndx;
			console.log(custRateIndx);
			
			$("input[name='"+custPayRateName+"'").removeClass("RateErrorInput");
			$("input[name='"+custBillRateName+"'").removeClass("RateErrorInput");
		}
	}
});

</script>
</body>
</html>
<?php
	function addCRMCompanies($comsno,$username,$db)
	{
		require("cont_dataConv.php");
		require("comp_dataConv.php");
		
		$CRM_Comp_SNo_SDataUpdate_Array=array();
		$sel_cus="SELECT sno,username FROM staffacc_cinfo WHERE username='".$comsno."'";
		$res_sel_cus=mysql_query($sel_cus,$db);
		$fetch_sel=mysql_fetch_row($res_sel_cus);
		
		$qry_acccus="INSERT INTO staffoppr_cinfo  (sno,approveuser,cdate,owner,mdate,muser,ceo_president,cfo,sales_purchse_manager,cname,curl,address1,address2,city,state ,country,zip,ctype,csize,nloction,nbyears,nemployee,com_revenue,federalid, bill_req, service_terms,accessto,acc_comp,compowner,compbrief,compsummary,compstatus,dress_code,tele_policy,smoke_policy,parking, park_rate,directions,culture,phone,fax,industry,keytech,department,siccode,csource,ticker,alternative_id,customerid,phone_extn) 
		SELECT  '' ,'".$username."',Now(),'".$username."',NOW(),'".$username."', ceo_president , cfo , sales_purchse_manager , cname , curl , address1 , address2 , city , state , country , zip , ctype , csize , nloction , nbyears , nemployee , com_revenue , federalid , bill_req , service_terms,'ALL',sno,compowner,compbrief, compsummary,compstatus,dress_code,tele_policy,smoke_policy,parking, park_rate,directions,culture,phone,fax,industry,keytech, department,siccode,csource,ticker,alternative_id,sno,phone_extn FROM staffacc_cinfo WHERE username='".$comsno."'";
		mysql_query($qry_acccus,$db);
		$cus_id=mysql_insert_id($db);
		array_push($CRM_Comp_SNo_SDataUpdate_Array,$cus_id);
		
		$upd_acccomp="UPDATE staffacc_cinfo SET crm_comp='".$cus_id."', muser='".$username."', mdate=NOW() WHERE username='".$comsno."'";
		mysql_query($upd_acccomp,$db);	
		
		$acc_cus="SELECT sno,email FROM staffacc_contact WHERE username='".$comsno."' and acccontact='Y'";
		$res_acc_cus=mysql_query($acc_cus,$db);
		$acc_cus_rows=mysql_num_rows($res_acc_cus);
		for($a=0;$a<$acc_cus_rows;$a++)
		{
			$fetch_acc=mysql_fetch_row($res_acc_cus);

			$rel_cont="SELECT sno,acc_cont,csno FROM staffoppr_contact WHERE acc_cont=".$fetch_acc[0];
			$res_cont=mysql_query($rel_cont,$db);
			$res_num_rows=mysql_num_rows($res_cont);
			if($res_num_rows == 0)
			{
				$query3="INSERT INTO staffoppr_contact(sno,approveuser,stime,owner,mdate,muser,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,acc_cont, accessto,csno,cat_id,department,certifications,codes,keywords,messengerid,address1,address2,city,state,country,zipcode,sourcetype,prefix,suffix,ctype,wphone_extn, hphone_extn,other_extn,other_info,email_2,email_3,description,importance, source_name,reportto_name,spouse_name) select  '' ,'".$username."',Now(),'".$username."',NOW(),'".$username."',fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,sno,'','".$cus_id."',cat_id,department,certifications,codes,keywords, messengerid,address1,address2,city,state,country,zipcode,sourcetype,prefix,suffix,ctype,wphone_extn,hphone_extn,other_extn,other_info,email_2,email_3,description, importance,source_name,reportto_name,spouse_name FROM staffacc_contact WHERE sno='".$fetch_acc[0]."'";
				mysql_query($query3,$db);
				$con_id=mysql_insert_id($db);

				//Checking relation for CRM company and Customer company (if available dumping CRM contacts to Customer contacts)
				covertCrmToAcc($cus_id,$con_id);

				$upd_acccont="UPDATE staffacc_contact SET crm_cont='".$con_id."' WHERE sno='".$fetch_acc[0]."'";
				mysql_query($upd_acccont,$db);	

				$queu="SELECT sno,email FROM staffoppr_contact WHERE sno='".$con_id."'";
				$resu=mysql_query($queu,$db);
				$datau=mysql_fetch_array($resu);

				$getcount=getContactDomain($datau[1]);

				if($getcount!='ALL')
					$accto=$username;
				else
					$accto='ALL';

				$que1="UPDATE staffoppr_contact SET accessto='".$accto."' WHERE sno=".$datau[0];
				mysql_query($que1,$db);				

				//For updating contact search column
				updatecont_search($con_id);
			}
			else
			{
				$res_fetch=mysql_fetch_row($res_cont);
				$upd_contact="UPDATE staffoppr_contact SET csno='".$cus_id."' WHERE sno='".$res_fetch[0]."'";
				mysql_query($upd_contact,$db);
				

				//For updating contact search column
				updatecont_search($res_fetch[0]);
			}		
		}

		//For updating company search data
		updatecomp_search_data($CRM_Comp_SNo_SDataUpdate_Array); 

		billContactAddress($comsno);
	}
	
	function getCRMClientInfo($username,$db)
	{
		$que1 = "SELECT CONCAT('staffoppr-',staffoppr_contact.sno),TRIM(CONCAT_WS(' ',staffoppr_contact.fname,staffoppr_contact.mname,
staffoppr_contact.lname)) AS NAMES,CONCAT_WS(' ',staffoppr_cinfo.address1,staffoppr_cinfo.city,staffoppr_cinfo.state) ,
staffoppr_contact.csno,staffoppr_cinfo.cname AS cnames,
IF(staffoppr_contact.nickname = '',staffoppr_contact.email, CONCAT(staffoppr_contact.nickname,'(',staffoppr_contact.email,')') ), 
staffoppr_contact.status, staffoppr_contact.sno as contid, staffacc_contactacc.con_id, staffoppr_cinfo.acc_comp
FROM staffoppr_contact, staffacc_contactacc, staffoppr_cinfo 
WHERE staffoppr_contact.status='ER' AND 
staffoppr_contact.crmcontact='Y' AND 
staffoppr_contact.csno=staffoppr_cinfo.sno AND
staffoppr_contact.acc_cont=staffacc_contactacc.`con_id` AND 
staffacc_contactacc.`username` = ".$username;
		$res1=mysql_query($que1,$db);
		
		return $res1;
	}
?>