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
	require_once("shift_schedule/crm_schedule_db.php");
        require_once("../../Admin/Manage_Questionnaire/custom_fields_functions.php");
	$posid=$posid;
        $jobposprewid = $posid;
         //From Admin->Joposting and CRM->joborders
        if($jobposprw =='yes'){
          $jobPosQuery = 'SELECT req_id FROM hotjobs WHERE sno="'.$posid.'"'; 
          $jobpos_res=mysql_query($jobPosQuery,$db);
	   $jobpos_row=mysql_fetch_row($jobpos_res); 
           $posid=$jobpos_row[0];
        }
	require("Menu.inc");
	$menu=new EmpMenu();

	require_once("multipleRatesClass.php");
	$ratesObj = new multiplerates();
	
	/* Including common shift schedule class file */
	require_once('shift_schedule/class.schedules.php');
	$objSchSchedules	= new Schedules(); //Creating object for the included class file

	$mode_rate_type = "joborder";
	$type_order_id = $posid;
	
        $burden_status = getBurdenStatus();
        
	session_unregister("Jobloc_Comp_Divisions");
	session_unregister("Comp_Divisions");
	$Jobloc_Comp_Divisions='';
	$Comp_Divisions='';
	session_unregister("CRM_Joborder_Page2");

	$ratesDefaultVal = $ratesObj->getDefaultMutipleRates();

	$typeOfManage="jostatus','jostage','jocategory','jotype','prefix','suffix','contacttype','category','compsource','department','comptype','compstatus','josourcetype','joindustry";
	
	$que="SELECT a.posstatus,a.jostage,a.owner,a.accessto,a.postype,a.catid,a.postitle,a.refcode,a.no_of_pos,
	a.closepos,a.company,a.location,a.contact,a.posreportto,a.owner,IF(a.accessto='all','Public',
	IF(a.accessto!=owner,'Share','Private')),a.messageid,a.joblocation,a.wcomp_code,a.bill_req,a.service_terms,
	a.sourcetype,b.department,a.jonumber,a.post_job_chk ,".tzRetQueryStringDTime("a.posted_date","Date","/").",
	a.posted_status,IF(a.refresh_date='0000-00-00 00:00:00','',".tzRetQueryStringDTime("a.refresh_date","Date","/")."),
	".tzRetQueryStringDTime("a.remove_date","Date","/").",".tzRetQueryStringDTime("a.expire_date","Date","/").",
	IF(a.expire_date!='0000-00-00 00:00:00' and a.expire_date < NOW(),'Expired',''),
	IF(b.diem_lodging='0.00','',b.diem_lodging), IF(b.diem_mie='0.00','',b.diem_mie),
	IF(b.diem_total='0.00','',b.diem_total), b.diem_period, b.diem_currency, b.diem_billable, b.diem_taxable, b.diem_billrate,b.po_num, a.deptid, a.bill_address, a.billingto, a.hotjob_chk,b.burden, a.industryid, b.bill_burden, b.bamount, b.pamount,a.stime,a.shiftid,a.starthour,a.endhour,a.questionnaire_group_id,a.shift_type,a.ts_layout_pref
	FROM posdesc a,req_pref b
	WHERE a.posid='".$posid."' AND a.posid=b.posid";
	$JobDetExc=mysql_query($que,$db);
	$JobDet=mysql_fetch_row($JobDetExc);

	$wsjl = $JobDet[17];
	$unfilled=$JobDet[1];
	$shift_type = $JobDet[54];
	$timesheet_layout_preference = $JobDet[55];

	//For getting the sharetype of joborder and assigning that value for a hiddenvariable
	if($JobDet[3]==$username)
	    $job_share_type='Private';
	else if($JobDet[3]=='all')
	    $job_share_type='Public';
	else{
		$job_share_type='Share';
	        $job_share_users=$JobDet[3];
	}
   
	//if selected copy setting owner to loggedin user and share to public
	if($copyjoborder != 'yes') 
	{
		$jcopyowner = $JobDet[2];
		$jcopyshareval = $JobDet[15];
	}
	else
	{
		$jcopyowner = $username;
		$jcopyshareval = 'Public';
	}

	//For source link
	$message1=$JobDet[16];
	if($message1 !=0)
	{
		$src_val="yes";
		$Msg_id=$message1;
	}

	//Getting contact information
	if(($JobDet[12]!='' && $JobDet[12]!='0'))
	{
		$Cont_sql="SELECT sno,CONCAT_WS(' ',fname,mname,lname),csno,cat_id,accessto,status from staffoppr_contact where sno='".$JobDet[12]."' order by fname, mname, lname";
		$Cont_res = mysql_query($Cont_sql,$db);
		$Cont_data = mysql_fetch_row($Cont_res);

		$Cont_Name = $Cont_data[1];
		$Cont_No=$Cont_data[0];
		$Cont_csno=$Cont_data[2];
		$contactCatSno = $Cont_data[3];
				
		$chkContAccess = chkAvailAccessTo($Cont_data[4],$Cont_data[5],$username);
	}

	if($Cont_csno>0)
	{
		$Cont_Opt=0;
		$Compsnos=$Cont_csno;

		//$Select_Sql="select sno,CONCAT_WS(' ',fname,mname,lname),csno,accessto,status from staffoppr_contact where csno = '".$Compsnos."' order by fname, mname, lname";
		
		$Select_Sql="SELECT sno,CONCAT_WS(' ',fname,mname,lname),csno,accessto,status FROM staffoppr_contact WHERE csno = '".$Compsnos."' AND status='ER' AND crmcontact='Y' ORDER BY fname, mname, lname";
		
		$Select_Res=mysql_query($Select_Sql,$db);
		while($Select_Data=mysql_fetch_row($Select_Res))
		{
			$chkSBAccess = chkAvailAccessTo($Select_Data[3],$Select_Data[4],$username);				
			$selected='';
			if($Select_Data[0]==$Cont_No)
				$selected='selected';
			$Cont_options.="<option lang='".$chkSBAccess."' title='".$Select_Data[1]."' value='".$Select_Data[0]."' ".$selected.">".$Select_Data[1]."</option>";		   
			$Cont_Opt++;
		}

		if($Cont_Opt==0 || $Cont_Opt==1)
			$Cont_options="<input type=hidden name=jocontact id=jocontact value=".$Cont_No.">";
		else
			$Cont_options="<select class='summaryform-formelement' name='jocontact' id='jocontact' onchange=showcontactdata(this.value,'contact')><option value=''>--select--</option>".$Cont_options."</select><span class='summaryform-formelement'>&nbsp;|&nbsp</span>";
	}

	//Getting company information
    if($JobDet[10]!='' && $JobDet[10]!='0')
    {
		$Comp_sql="SELECT sno,cname,address1,address2,city,state,zip,parent,country,accessto,status from staffoppr_cinfo where sno = '".$JobDet[10]."'";
		$Comp_res = mysql_query($Comp_sql,$db);
		$Comp_data = mysql_fetch_row($Comp_res);

		$Comp_addr1=$Comp_data[2];
		$Comp_addr2=$Comp_data[3];
		$Comp_city=$Comp_data[4];
		$Comp_state=$Comp_data[5];
		$Comp_zip=$Comp_data[6];
		$Comp_cntry=$Comp_data[8];
		$Comp_No=$Comp_data[0];
		$Comp_Name=$Comp_data[1];
		$Comp_Parent=$Comp_data[7];
				
		$chkCompAccess = chkAvailAccessTo($Comp_data[9],$Comp_data[10],$username);
	}

	$manage_table_values=getManageTypes();

	$ContryOptions=getcountryNames(0);
	function addrValue($row1,$row2,$row3,$row4,$row5)
	{   
		$comp_addr="";		
		if($row1!='')
			$comp_addr=$row1;
		if($row2!='')
		{
			if($comp_addr!='')
				$comp_addr.=",".$row2;
			else
				$comp_addr=$row2;			
		}

		if($row3!='')
		{
			if($comp_addr!='')
				$comp_addr.=",".$row3;
			else
				$comp_addr=$row3;			
		}	 

		if($row4!='')
		{
			if($comp_addr!='')
				$comp_addr.=",".$row4;
			else
				$comp_addr=$row4;			
		}

		if($row5!='')
		{
			if($comp_addr!='')
				$comp_addr.="  ".$row5;
			else
				$comp_addr=$row5;			
		}
		return($comp_addr); 
   }
   	/*
	This function used to get the no of position as to load.
	*/
	function loadItrationNo($sessionShift){
		$count=0;
		$target=30;
		$total=0;
		$numbers = $sessionShift;
		foreach($numbers as $key){
			$shiftdate = explode('^', $key);
		    if($total < $target) {
		        $total = $total+$shiftdate[8];
		        $count++;
		    }
		    else
		    {  
		        break;
		    }
		}
		return $count;
	}

	$DispTimes=display_SelectBox_Times();

	$que_users="SELECT e.username as username, e.name as name FROM emp_list e LEFT JOIN hrcon_compen h ON (h.username = e.username) LEFT JOIN manage m ON (m.sno = h.emptype) WHERE e.lstatus != 'DA' AND e.lstatus != 'INACTIVE' AND e.empterminated !='Y' AND h.ustatus = 'active' AND m.type = 'jotype' AND m.status='Y' AND m.name IN ('Internal Direct', 'Internal Temp/Contract') AND h.job_type <> 'Y' ORDER BY e.name";
	
    $res_users=mysql_query($que_users,$db);

	$padding_style = "style='padding-left:271px;'";
	if(strpos($_SERVER['HTTP_USER_AGENT'],'Gecko') > -1)
	{
		$rightflt = "style= 'width:66px;'";
		$inner_rightflt = "style= 'width:35px;'";
		$style_table = "style='padding:0px; margin:0px;'";
		$padding_style = "style='padding-left:310px;'";
	}

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
	
	
	
	// Condition to check UDF will display for only Frontoffice
	if(isset($_GET['chkAuth'])){
		$chkAuth = $_GET['chkAuth'];
	}else{
		$chkAuth = '';
	}

	
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
//Getting the burden type and item details


if ($burden_status == 'yes') {

	$burden_details_sel	= "SELECT CONCAT(t1.bt_id,'|',t2.burden_type_name) bt_details, GROUP_CONCAT(CONCAT(t1.bi_id,'^',t3.burden_item_name,'^',t3.burden_value,'^',t3.burden_mode,'^',t3.ratetype,'^',t3.max_earned_amnt,'^',t3.billable_status) SEPARATOR '|') bi_details, t1.ratetype FROM posdesc_burden_details t1 JOIN burden_types t2 ON t2.sno = t1.bt_id JOIN burden_items t3 ON t3.sno = t1.bi_id WHERE t1.posid = '".$posid."' GROUP BY t1.bt_id";
	$burden_details_res	= mysql_query($burden_details_sel, $db);

	while ($row = mysql_fetch_object($burden_details_res)) {

		$burden_details_rec[$row->ratetype]['bt_details']	= $row->bt_details;
		$burden_details_rec[$row->ratetype]['bi_details']	= $row->bi_details;
	}

	$bt_details = $burden_details_rec['payrate']['bt_details'];
	$bi_details = $burden_details_rec['payrate']['bi_details'];
	$bt_exists_flag = 0;
	$existingBurdenOpt = "";
	if($bt_details != "")
	{
		$bt_exists_flag = 1;
		$bt_details_exp = explode("|",$bt_details);
		$bt_sno = $bt_details_exp[0];
		$bt_name = $bt_details_exp[1];
		//forming the burden type str
		$edit_bt_detail_str = $bt_sno."|".$bt_name;
		//Forming the Burden Item Deteails
		$retutnStr = "";
		$totalBurdenVal = 0;
		$flatBurdenVal = "";
		$biDetailsStr = "";
		$bi_details_exp = explode("|",$bi_details);
		foreach($bi_details_exp as $ind_bi_items)
		{
			$ind_bi_items_exp = explode("^",$ind_bi_items);
			$suf = "";
			if($ind_bi_items_exp[3] == 'percentage')
			{
				$suf = "%";
				$totalBurdenVal += $ind_bi_items_exp[2];
			}
			else
			{
				$flatBurdenVal .= $ind_bi_items_exp[2]."^";
			}
			$retutnStr .= $ind_bi_items_exp[1]."-".$ind_bi_items_exp[2].$suf." + ";
			$biDetailsStr .= $ind_bi_items_exp[0]."^".$ind_bi_items_exp[1]."^".$ind_bi_items_exp[2]."^".$ind_bi_items_exp[3]."^".$ind_bi_items_exp[4]."^".$ind_bi_items_exp[5]."^".$ind_bi_items_exp[6]."|";
		}

		$retutnStr = substr($retutnStr,0,strlen($retutnStr)-2);
		$flatBurdenVal = substr($flatBurdenVal,0,strlen($flatBurdenVal)-1);
		$biDetailsStr = substr($biDetailsStr,0,strlen($biDetailsStr)-1);
		$edit_bi_detail_str = $retutnStr."|".$totalBurdenVal."|".$flatBurdenVal."^^BURDENITEMSPLIT^^".$biDetailsStr;
		$edit_existing_bi_str = $biDetailsStr;
	}
	else if($JobDet[44] != "" && $JobDet[44] != 0)
	{
		$existingBurdenOpt = '<option value="old|joborder|'.$posid.'" selected>Older Burden</option>';
	}

	$bt_bill_details	= $burden_details_rec['billrate']['bt_details'];
	$bi_bill_details	= $burden_details_rec['billrate']['bi_details'];

	$btbill_exists_flag		= 0;
	$existingBillBurdenOpt	= "";

	if ($bt_bill_details != "") {

		$btbill_exists_flag	= 1;
		$bt_bill_details	= explode("|", $bt_bill_details);

		$bt_billsno		= $bt_bill_details[0];
		$bt_billname	= $bt_bill_details[1];

		//forming the burden type str
		$edit_bt_billdetail_str	= $bt_billsno."|".$bt_billname;

		//Forming the Bill Burden Item Details
		$returnStr			= '';
		$billBurdenTotal	= 0;
		$flatBillBurden		= '';
		$billDetailsStr		= '';
		$bi_bill_details	= explode("|", $bi_bill_details);

		foreach ($bi_bill_details as $ind_bill_items) {

			$suffix	= '';
			$ind_bill_items_exp	= explode("^", $ind_bill_items);

			if($ind_bill_items_exp[3] == 'percentage') {

				$suffix	= '%';
				$billBurdenTotal	+= $ind_bill_items_exp[2];

			} else {

				$flatBillBurden	.= $ind_bill_items_exp[2]."^";
			}

			$returnStr		.= $ind_bill_items_exp[1]."-".$ind_bill_items_exp[2].$suffix." + ";
			$billDetailsStr	.= $ind_bill_items_exp[0]."^".$ind_bill_items_exp[1]."^".$ind_bill_items_exp[2]."^".$ind_bill_items_exp[3]."^".$ind_bill_items_exp[4]."^".$ind_bill_items_exp[5]."^".$ind_bill_items_exp[6]."|";
		}

		$returnStr		= substr($returnStr,0,strlen($returnStr)-2);
		$flatBillBurden	= substr($flatBillBurden,0,strlen($flatBillBurden)-1);
		$billDetailsStr	= substr($billDetailsStr,0,strlen($billDetailsStr)-1);

		$edit_bi_bill_detail	= $returnStr."|".$billBurdenTotal."|".$flatBillBurden."^^BURDENITEMSPLIT^^".$billDetailsStr;
		$edit_existing_bill_str	= $billDetailsStr;

	} elseif ($JobDet[46] != "" && $JobDet[46] != 0) {

		$existingBillBurdenOpt	= '<option value="old|joborder|'.$posid.'" selected>Older Burden</option>';
	}
}

$get_bt_list_sql	= "SELECT bt.sno, bt.burden_type_name, bt.ratetype FROM burden_types bt WHERE bt.bt_status = 'Active'";
$get_bt_list_rs		= mysql_query($get_bt_list_sql,$db);
$arr_burden_type	= array();

while ($row = mysql_fetch_object($get_bt_list_rs)) {

	$arr_burden_type[$row->sno]['burden_type']	= $row->burden_type_name;
	$arr_burden_type[$row->sno]['rate_type']	= $row->ratetype;
}

//To get burden type id based on location.
$get_bt_id_location_que =   "SELECT burden_type from staffoppr_location where sno=".$JobDet[11];
$get_bt_id_location_rs  =   mysql_query($get_bt_id_location_que,$db);
$bt_id_location_row =   mysql_fetch_row($get_bt_id_location_rs);
$chk_bt =   false;
if($bt_sno  ==  $bt_id_location_row[0])
{
    $chk_bt = true;
}

//StartDate and EndDate years, 20 years for past years and 10 years to future.
$startYear = $dueYear = $expectedEndYear = displayPastFutureYears();
?>
<?php include('header.inc.php') ?>
<title><?php if($copyjoborder != 'yes') echo "Edit Job Order"; else echo "New Job Order"; ?></title>
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/crm-summary.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/candidatesInfo_tab.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/CandidatesCustomTab.css">
<link href="/BSOS/css/grid.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/filter.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/site.css" type=text/css rel=stylesheet>
<link href="/BSOS/css/crm-editscreen.css" type=text/css rel=stylesheet>
<link rel="stylesheet" href="/BSOS/popupmessages/css/popup_message.css" media="screen" type="text/css">
<!-- <link type="text/css" rel="stylesheet" href="/BSOS/css/select2.css"> -->
<link type="text/css" rel="stylesheet" href="/BSOS/css/select2_loc.css">
<script type="text/javascript">var rate_calculator='<?php echo RATE_CALCULATOR;?>'</script>
<script type="text/javascript">var onLoadModeCheck = 'onload';</script>
<script type="text/javascript">var MarkupCheck = '';</script>
<script type="text/javascript">var refBonusManage = '<?php echo REFERRAL_BONUS_MANAGE;?>';</script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popupMsgArray.js"></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popup-message.js"></script>
<script language=javascript src=/BSOS/scripts/tabpane.js></script>
<script language=javascript src=/BSOS/scripts/validaterresume.js></script>

<script language=javascript src="scripts/validateact.js"></script>
<script language=javascript src="scripts/validatesup.js"></script>
<script language=javascript src="scripts/validatemarkreqman.js"></script>
<script language=javascript src="/BSOS/scripts/common.js"></script> 
<script language=javascript src="/BSOS/scripts/dynamicElementCreatefun.js"></script>
<script language=javascript src="/BSOS/scripts/validatecheck.js"></script>

<script language=javascript src=scripts/validatenewsubmanage.js></script>
<script language=javascript src=/BSOS/scripts/commonact.js></script>
<script language="JavaScript" src="/BSOS/scripts/common_ajax.js"></script>
<script language=javascript src=scripts/JoborderScreen.js></script>
<script language=javascript src=scripts/commission.js></script>
<script language=javascript src=scripts/joborder_ajax_resp.js></script>
<script language=javascript src=scripts/billrate.js></script>
<script language=javascript src="/BSOS/scripts/schedule.js"></script>

<script language=javascript src="/BSOS/scripts/calMarginMarkuprates.js"></script>
<script>var madison='<?=PAYROLL_PROCESS_BY_MADISON;?>';
var tricom_rep='<?=TRICOM_REPORTS;?>';</script>
<script language=javascript src="scripts/validatejb.js"></script>
<script language=javascript src="/BSOS/scripts/multiplerates.js"></script>
<script language=javascript src="/BSOS/scripts/crmLocations.js"></script>
<?php getJQLibs(['jquery','jqueryUI']);?>
<script type="text/javascript" src="/BSOS/scripts/eraseSessionVars.js"></script>

<!-- loads modalbox css -->
<link rel="stylesheet" type="text/css" media="all" href="/BSOS/css/shift_schedule/calschdule_modalbox.css" />

<!-- loads jquery & jquery modalbox -->
<script src="/BSOS/scripts/jquery-1.8.3.js"></script>
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
<!-- Perdiem Shift Scheduling -->
<link type="text/css" rel="stylesheet" href="/BSOS/css/perdiem_shift_sch/perdiemShifts.css">
<script type="text/javascript" src="/BSOS/scripts/perdiem_shift_sch/PerdiemShiftSch.js"></script>

<script type="text/javascript">
/* Added autopreloader function to show loading icon, until the page completely loads */
$(window).load(function(){
	$('#autopreloader').fadeOut('slow',function(){$(this).remove();});
	$('.newLoader').fadeOut('slow',function(){$(this).remove();});
        onLoadModeCheck = 'NoMode';
}); 
</script>
<?php
// UOM rates
$timeOptions = getNewRateTypes(""); 
?>
<?php
//Make the date fields disabled only when the shift scheduling is enabled
if(SHIFT_SCHEDULING_ENABLED == 'Y')
{
?>
<script type="text/javascript">
	
	$(document).ready(function() {
		// Disables Job Order Start Date/Due Date/Expected End Date/Hours Section		
		if($('#jo_shiftsch').attr("checked")){
			if($("#smonth").length){
				$('#smonth').attr('disabled', true);
				$('#sday').attr('disabled', true);
				$('#syear').attr('disabled', true);
				$('#josdatecal').hide();
			}
			if($("#duedatemonth").length){
				$('#duedatemonth').attr('disabled', true);
				$('#duedateday').attr('disabled', true);
				$('#duedateyear').attr('disabled', true);
				$('#joduedatecal').hide();
			}
			if($("#emonth").length){
				$('#emonth').attr('disabled', true);
				$('#eday').attr('disabled', true);
				$('#eyear').attr('disabled', true);
				$('#joedatecal').hide();
			}
			if($("#Hrstype").length){
				$('#Hrstype').attr('disabled', true);
			}
		}
		showhideShiftLegends();
	});
</script>
<?php
}
?>

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
	
	function removeJobOrderSkills(trId,joskillid,skillid)
	{	
		
		if (typeof skillid == 'undefined'){
			skillid = joskillid;
		}
		var row = document.getElementById(trId);
		var table = row.parentNode;
		while ( table && table.tagName != 'TABLE' )
			table = table.parentNode;
		if ( !table )
			return;
		table.deleteRow(row.rowIndex);
		deleteJobOrderSkill(joskillid);	
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
	
	function deleteJobOrderSkill(joskillid)
	{
		//alert("Please delete skill from Job order"+joskillid);
		$.ajax({url:"/PSOS/Marketing/Candidates/getcatsSkills.php?action=deljoborderskills&skillid="+joskillid,success:function(result){
		  return false;
		}});
	}
	function ismaxlength(obj,MaxLen){
		 var mlength = MaxLen;
		if (obj.getAttribute && obj.value.length>mlength)
			obj.value=obj.value.substring(0,mlength)
	}
	function openCandWindow(sno,str,panel)
	{
		var posid=document.conreg.posid.value;
		var inact=sno;
		document.getElementById("panel_type").value = panel;
		if(str=='candactive')
			result="review.php?cno="+sno+"&posid="+posid+"&dest=0";
		else
			result="/BSOS/Marketing/Candidates/viewcanddetails.php?type=cand&posid="+posid+"&addr="+inact;
		var v_width  = 1000;
		var v_heigth = 700;
		var top1=(window.screen.availHeight-v_heigth)/2;
		var left1=(window.screen.availWidth-v_width)/2;
		remoteres=window.open(result,"","width="+v_width+"px,height="+v_heigth+"px,resizable=yes,statusbar=no,menubar=no,scrollbars=yes,left="+left1+"px,top="+top1+"px");
		remoteres.focus();	
}
 var questionnaire_itm_enabled = "<?php echo JOB_QUESTIONNAIRE_ENABLED; ?>";
 if(questionnaire_itm_enabled=='Y'){
$(document).ready(function () {
            $(function () {
                $('#customfieldsscroll').slimscroll({
                    width: '100%',
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

	function closeJobOrderWindow() {

		eraseSessionVars("joborders", "<?=$candrn?>");
		window.close();
	}
</script>
<style type="text/css">
#multipleRatesTab table tr td span select{margin-left:14px }
.panel-table-content-new .summaryform-bold-title { font-size:12px; font-weight:bold;}
/* #modal-wrapper{height: 240px; margin-left: -170px; margin-top: -120px; width:800px;} */
#modal-glow{ width:100%; position:fixed;}
.summaryform-formelement_role {
    height: 31px !important;
    margin: -2px 0 !important;
}
#sel_perdiem, #sel_perdiem2{
    font-size:12px !important;
}
.billable_blockNew td{
 padding-right:5px !important;
 padding-left:0px !important;

}
.billable_blockNew .summaryform_margin_left{
    padding-left:10px !important;
}
.disabled_user_input_field{
    background:rgb(235, 235, 228) !important;
}
.timehead{ width:4%; display:block; overflow:hidden}
.timeheadPosi{position:absolute; top:0px;text-align:center;  }
.timegrid{ width:2.040% !important; display:block; overflow:hidden}

@media screen\0 {
	.timegrid{ width:2.030% !important; display:block; overflow:hidden}
	.summaryform-formelement{ height:18px; font-size:12px !important; }
	a.edit-list:link{ font-size:12px !important;}
	.summaryform-bold-close-title{ font-size:12px !important;}
	.center-body { text-align:left !important;}
	.crmsummary-jocomp-table td{ font-size:12px !important ; text-align:left !important;}
	#smdatetable{ font-size:12px !important;}
	.summaryform-formelement{ text-align:left !important; vertical-align:middle}
	.crmsummary-content-title{ text-align:left !important}
	.crmsummary-edit-table td{ text-align:left !important}
	.summaryform-bold-title{ font-size:12px !important;}
	.crmsummary-jocomp-table td.smshiftnamesclass{ font-size:12px !important ; text-align:left !important;}
	.commvalclass{ height:inherit !important}
}

@media screen and (-webkit-min-device-pixel-ratio:0) {
    /* Safari only override */
	.custom_rate_remove_button img{ top:13px;}
	::i-block-chrome,.timegrid {width:2.05% !important; }
	::i-block-chrome,.timehead { width:3.5% !important; }  
	::i-block-chrome,.sstabelwidth { width:99% !important;  }  
}
a.crm-select-linkNewBg:hover{color:#3AB0FF}
.custom_defined_main_table td{text-align: left !important; vertical-align: middle;}
.innertbltd-custom table td {vertical-align: middle !important;}
.custom_defined_head_td{width: 120px !important; padding:0px !important;}
.managesymb{ margin-top:7px; }
.closebtnstyle{ float:left; *margin-top:3px; vertical-align:middle; margin-top:2px; }
.modalDialog_contentDiv{height:auto !important; top:25% !important; position: fixed !important; }
.alert-ync-container{ padding-bottom: 10px !important; height: inherit !important; }

 @media screen and (-ms-high-contrast: active), (-ms-high-contrast: none) {  
 .modalDialog_contentDiv{height:auto !important; top:25% !important; position: absolute !important; }
 #readMoreShiftData{float: left !important; margin-left: 50% !important;}
}
.mloadDiv #autopreloader { position: fixed; left: 0; top: 0; z-index: 99999; width: 100%; height: 100%; overflow: visible; 
opacity:0.35;background:#000000 !important; }
.newLoader{position:absolute; top:50%; left:50%;z-index:99999;margin-left:-67px;margin-top:-67px;width:135px;height:135px;}
.mloadDiv .newLoader img{border-radius:69px !important;}
.crmsummary-jocomp-table .fa-calculator:before{color: #138dc5;font-size:14px;}
.cdfJoborderBlk input[type="text"]{ width:300px;}
.cdfJoborderBlk textarea{ 
	width: 476px;
	border: 1px solid #bfbfbf !important;
	border-radius: 3px !important;
	color: #474c4f !important;
	font-family: Arial,Helvetica,sans-serif !important;
	font-size: 12px !important;
	margin-left: 2px !important;
}
.cdfJoborderBlk .selCdfCheckVal{ 
	width:250px !important;
	
 }
 .cdfJoborderBlk .selCdfCheckVal .select2-search-choice div{ 
	font-family: Arial,Helvetica,Verdana,sans serif ;
	font-size: 12px;
 }

 .select2-results .select2-result-label {
	font-family: Arial !important;
	font-size: 12px !important;
 }
 #commissionRows select, #commissionRows select{height: 30px !important;}
 .cdfAutoSuggest input[type="text"]{width:250px !important;}
.jocomp-back .crmsummary-jocomp-table input[name="po_num"], .jocomp-back .crmsummary-jocomp-table input[name="joborder_dept"],.jocomp-back .crmsummary-jocomp-table select[name="bill_loc"],.jocomp-back .crmsummary-jocomp-table select[name="bill_cons"] {width:250px !important;}
.cdfCheckUlLl input[name="cust_jobcommission[]"],.cdfCheckUlLl input[name="cust_remarket[]"]{margin-top:4px !important;}
.JoAssignPerdiemEditModal-wrapper{position: fixed !important; width:620px !important; height: 365px !important; margin-left: -320px !important;}
.JoAssignPerdiemEditModal-wrapper .scroll-area{width: 600px !important;}
	
/* .JoPerdiemEditModal-wrapper{position: fixed !important; width:620px !important; height: 455px !important; margin-left: -320px !important; margin-top:-228px !important; top: 50% !important; }
.JoPerdiemEditModal-wrapper .scroll-area{width: 620px !important;} */
.scroll-area{width: 1000px !important;} 	
body.perdiemnoscroll {
    overflow: hidden;
}
.shiftPagiNation{ height:40px; line-height:40px; margin:0px; clear:both; padding:10px; background:#f1fbff; margin-top:5px;}
.shiftNextPrevious{ line-height:18px;}
 </style>
<?php if(JOB_QUESTIONNAIRE_ENABLED=='Y') { ?>     
<style>
	.question{background: #fff; padding-left:5px;cursor: pointer;font-weight:normal;line-height:26px; font-size:14px;}  
	.answer{padding-top:0px;padding-bottom:10px;margin-left:27px;background: #fff;line-height:24px;}
	.active { }

	.question{background: #fff; padding-left:5px;cursor: pointer;font-weight:normal;line-height:26px; font-size:14px;}  
	.answer{padding-top:0px;padding-bottom:10px;margin-left:27px;background: #fff;line-height:24px;}
	.active { }
	.questDynM{ font-size:13px; line-height:20px; background:#fff; border-radius:4px; padding:10px 5px; border:solid 1px #ccc;}
	.questExpCollap{ padding-right:10px; text-align:right; margin-bottom:10px; padding-bottom:10px; border-bottom:solid 1px #ccc; }
	.questExpCollap a{ font-weight:bold; font-size:12px; text-decoration:underline;}
	.questExpCollap a:hover{ font-weight:bold; text-decoration:none; }
	.questDynM .question i{transition:0.1s; margin:0px 5px; }
	.questDynM .question.active{ font-weight:normal;}
	.questDynM .question.active i{transform: rotate(90deg);}
	#candStages{ padding:6px 4px; border:solid 1px #ccc; width:350px; border-radius:4px;}
	.questDynM .select2Blk{ border:solid 1px #ccc; padding:5px; width:95%; border-radius:4px;}
	.questHedNew{float:left; padding-left:10px; font-weight:bold; font-size:13px;}
	.quesInputWid input[type="text"], .quesInputWid textarea, .quesInputWid select{ width:350px; padding:6px 4px; border:solid 1px #ccc; border-radius:4px;}
</style>
<?php } ?>
</head>
<body>
	<div class="mloadDiv"><div id="autopreloader"></div>
	<div class="newLoader"><img src="../../../BSOS/images/akkenloading_big.gif"></div> 
	<input type="hidden" id="payrate_calculate_confirmation" value="" />
	<input type="hidden" id="billrate_calculate_confirmation" value="" />
	<input type="hidden" id="payrate_calculate_confirmation_window_onblur" value="" />
	<input type="hidden" id="billrate_calculate_confirmation_window_onblur" value="" />

	<input type="hidden" id="payrate_new_calculate_confirmation_window_onblur" value="" />
	<input type="hidden" id="billrate_new_calculate_confirmation_window_onblur" value="" />
	<input type="hidden" id="previous_pay_rate" value="" />
	<input type="hidden" id="previous_bill_rate" value="" />

	<input type=hidden name="confirmstate" id="confirmstate" value="edit">
	<form method=post name=conreg id='conreg' action="<?php if($copyjoborder != 'yes') echo 'updatejoborder.php'; else echo 'saveorder.php'; ?>">
	<input type='hidden' name='chkAuth' id ='chkAuth' value ="<?=$chkAuth?>" />	
	<input type="hidden" name="compcrfmstatus" id="compcrfmstatus" value="1">
	<input type="hidden" name="contactcrfmstatus" id="contactcrfmstatus" value="1">
	<input type="hidden" name="comploccrfmstatus" id="comploccrfmstatus" value="1">
	<input type="hidden" name="copy_joborder" id="copy_joborder" value="<?php echo $copyjoborder;?>">
	<input type="hidden" name="neworder" id="neworder" value="" />
	<input type="hidden" name="module" id="module" value="<?php echo $module;?>" />
	<div class="tab-pane" id="tabPane1">
		<script type="text/javascript">tp1 = new WebFXTabPane( document.getElementById( "tabPane1" ) );</script>
		<?php if($copyjoborder != 'yes') 
		{ 
		?>
			<div class="tab-page" id="tabPage01">
				<h2 class="tab" onClick="return valspchar(0,'edit','summary','<?php echo $module;?>');">Summary</h2>
				<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage01" ) );</script>
			</div>
			<div class="tab-page" id="tabPage12">
			<h2 class="tab" onClick="return valspchar(1,'edit','summary','<?php echo $module;?>');">Edit</h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage12" ) );</script>
                
        <?php 
		    //From Admin->Joposting and CRM->joborders
		    if($jobposprw=='yes'){ ?>
                        <div class="tab-page" id="tabPage13">
                        <h2 class="tab">Job Posting Preview</h2>
                        <script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage13" ) , "/BSOS/Admin/Jobp_Mngmt/Joborder/joborderpreview.php?addr=<?php echo $jobposprewid;?>&candrn=<?php echo $candrn;?>" );</script>
                        </div>
                    <?php }  ?>
		<?php 
		} 
		else 
		{ 
		?>
			<div class="tab-page" id="tabPage12">
			<h2 class="tab">New</h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage12" ) );</script>
			<script> var copyjorder='<?php echo $copyjoborder; ?>'; </script>
		<?php 
		} ?>	
		<table class="table">
			<tr class="NewGridTopBg">
			<?php 
			if($copyjoborder != 'yes') 
			{
				$Header_name_strip=explode("|","fa fa-clone ~Update".(($src_val=="yes")?"|documentedit.gif~Source":"")."|fa fa-files-o ~Copy"."|fa fa-times~Close");
				$Header_links_strip=explode("|","javascript:updateJobOrderOwner(this);".(($src_val=="yes")?"|javascript:doSource($posid,$Msg_id)":"")."|javascript:CopyJobOrder()"."|javascript:closeJobOrderWindow()");
				$Header_heading_strip="&nbsp;Job Order";
				$menu->showHeadingStrip1($Header_name_strip,$Header_links_strip,$Header_heading_strip);
			}
			else
			{
				$Header_name_strip=explode("|","fa fa-floppy-o ~Save|fa fa-times~Close");
				$Header_links_strip=explode("|","javascript:savejoborder(this)|javascript:closeJobOrderWindow()");
				$Header_heading_strip="&nbsp;Job Order";
				$menu->showHeadingStrip1($Header_name_strip,$Header_links_strip,$Header_heading_strip);
			}
			?>
			</tr>
		</table>	
	

<!--All snos-->
<input type=hidden name="userreq" id="userreq" value="<?php echo $reqArrStr;?>">
<input type=hidden name="candrn" id="candrn" value="<?php echo $candrn;?>">
<input type=hidden name='summarypage' id='summarypage' value="">
<input type='hidden' name='copyjorder' id='copyjorder' value="<?php if($copyjoborder == 'yes') echo "yes"; ?>">
<!-- From Admin->Joposting and CRM->joborders-->
<input type="hidden" name="jobposprw" id="jobposprw" value="<?php echo  $jobposprw;?>" />
<input type=hidden name="admaddr" id="admaddr" value="<?php echo $jobposprewid ;?>">    
<input type=hidden name="addr" id="addr" value="<?php echo $posid;?>">     

                           <!-- the End--> 
<input type=hidden name="posid" id="posid" value="<?php if($copyjoborder == 'yes') echo $JobDet[23]; else echo $posid; ?>">
<input type=hidden name="Compsno" id="Compsno" value="<?=$Comp_No;?>">
<input type=hidden name="job_location" id="job_location" value="<?=$Jobloc_No;?>">
<input type=hidden name="contval" id="contval" value="<?=$Cont_No;?>">
<input type=hidden name="reptval" id="reptval" value="<?=$Contreport_No;?>">
<input type=hidden name="bill_contact">
<input type=hidden name="bill_address">
<input type=hidden name="jobloc_bill_contact">
<input type=hidden name="jobloc_bill_address">
<input type="hidden" name="panel_type" id="panel_type" value="" >
<!--All the other-->
<input type=hidden name=url>
<input type=hidden name=dest>
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
<input type=hidden id="updskillsid" name="updskillsid" value="">
<input type=hidden id="delskill" name="delskill" value="">
<input type=hidden name="jobwindowstatus" id="jobwindowstatus" value="edit">
<input type=hidden name=ownerVal value="<? echo $jcopyowner;?>">
<input type=hidden name=jobshare value="<? echo $job_share_type;?>">
<input type=hidden name=jobshare_id value="<? echo $job_share_users;?>">
<input type=hidden name="contactData">
<input type=hidden name="reporttoData">
<input type="hidden" name="CRM_Joborder_Page2" value="">
<input type="hidden" name="amount" value="">
<input type="hidden" name="pamount" value="">
<input type="hidden" name="billr" value="">
<input type="hidden" name="payr" value="">
<input type="hidden" name="emplist" value="">
<input type="hidden" name="ownershare" value="">
<input type="hidden" name="changeowner" value="">

<!-- job order new and edit togle related-->
<input type=hidden name=binfotogle value="no">
<input type=hidden name=descrtogle value="no">
<input type=hidden name=hrptogle value="no">
<input type=hidden name=questionnaireToggle value="no">
<input type=hidden name=tandrtogle value="no">
<input type=hidden name="skillstogle" id="skillstogle" value="">
<input type=hidden name="schdtogle" id="schdtogle" value="">
<input type=hidden name=conttoggle> 
<input type=hidden name=repttoggle>
<input type=hidden name=Comp_Toggle value=''>	
<input type=hidden name=Jobloc_Toggle value=''>
<input type=hidden name=contemailstatus>
<input type=hidden name=reptemailstatus>
<input type=hidden name=BillInfo_Toggle >
<!-- job order new and edit unfilledlost related-->
<input type=hidden name=unfilled value="<?php echo $unfilled; ?>">
<input type=hidden name=unfillednotes value="">
<input type=hidden name=unfilleddata value="">
<!--company and job order related-->
<input type=hidden name=parking_vals>
<input type=hidden name=prate_val>
<input type=hidden name=jobloc_parking_vals>
<input type=hidden name=jobloc_prate_val>
<input type=hidden name=divisions>	
<!-- relation related-->
<input type=hidden name=Comp_Cont_Relation value=''>
<input type=hidden name=Report_Location_Relation value=''>
<!--hiddens for names--->
<input type=hidden name='Compname' id='Compname' value="<?=html_tls_specialchars($Comp_Name,ENT_QUOTES);?>">
<input type=hidden name='Con_Name' id='Cont_Name' value="<?=html_tls_specialchars($Cont_Name,ENT_QUOTES);?>">
<input type=hidden name='Report_Name' id='Report_Name' value="<?=html_tls_specialchars($Contreport_Name,ENT_QUOTES);?>">
<input type=hidden name='Jobloc_Name' id='Jobloc_Name' value="<?=html_tls_specialchars($Jobloc_Name,ENT_QUOTES);?>">
<input type=hidden name='Billcomp_Name' id='Billcomp_Name' value="">
<input type=hidden name='Billcont_Name' id='Billcont_Name' value="">
<input type=hidden name='Jobloc_Billcomp_Name' id='Jobloc_Billcomp_Name' value="">
<input type=hidden name='Jobloc_Billcont_Name' id='Jobloc_Billcont_Name' value="">

<!--HIddens for alerts-->
<input type=hidden name=Contact_Alert value=''>
<input type=hidden name=Company_Alert value=''>
<input type=hidden name=BillContact_Alert value=''>
<input type=hidden name=BillAddress_Alert value=''>
<input type=hidden name=Report_Alert value=''>
<input type=hidden name=Job_Alert value=''>
<input type=hidden name=REPT_Alert value=''>

<input type=hidden name=contfrmrval value="<? echo $Contfrmr_vals;?>">
<input type=hidden name=reptfrmrval value="<? echo $reptfrmr_vals;?>">
<input type=hidden name=placement_name value=''>
<input type=hidden name=addurl_value value=''>
<input type=hidden name=contold_data value=''>
<input type=hidden name=reptold_data value=''>
<input type=hidden name=compold_data value=''>
<input type=hidden name=joblocold_data value=''>
<input type=hidden name=contnew_data value=''>
<input type=hidden name=reptnew_data value=''>
<!--Hiddens for updation-->
<input type=hidden name=company_id value="<?= $JobDet[10] ?>">
<input type=hidden name=job_location_id id="job_location_id" value="<?= $JobDet[11] ?>">
<input type="hidden" name="hdnreportflag" id="hdnreportflag" value="<?= $JobDet[11] ?>">
<input type=hidden name="postweb_status" value="<?php echo $JobDet[24];?>">
<input type=hidden name="Report_Category_Sno" id="Report_Category_Sno" value="<?php echo Report_Category_Sno;?>">
<input type=hidden name="Contact_Category_Sno" id="Contact_Category_Sno" value="<?php echo Contact_Category_Sno;?>">
<input type="hidden" name="chkCompAccess" id="chkCompAccess" value="<?php echo $chkCompAccess; ?>">
<input type="hidden" name="chkJobLocAccess" id="chkJobLocAccess" value="<?php echo $chkJobLocAccess; ?>">
<input type="hidden" name="chkContAccess" id="chkContAccess" value="<?php echo $chkContAccess; ?>">
<input type="hidden" name="chkReportToAccess" id="chkReportToAccess" value="<?php echo $chkReportToAccess; ?>">
<input type="hidden" name="selectcaseType" id="selectcaseType" value="">
<input type="hidden" name="newcaseType" id="newcaseType" value="">
<input type="hidden" name="mulRatesVal" id="mulRatesVal" value="">
<input type="hidden" name="newcaseReportTo" id="newcaseReportTo" value="">
<input type="hidden" name="hdnDefaultDeptID" id="hdnDefaultDeptID" value="<?php echo getDefaultHRMDepartment();?>">
<!--<input type="hidden" name="roleData" id="roleData" value="<?php echo html_tls_entities($lstDirectInternal."|^AKKEN^|".$lstTempContact."|^AKKEN^|".$lstTempToDirect,ENT_QUOTES); ?>"> -->

<input type="hidden" name="roleData" id="roleData" value="<?php echo html_tls_entities($lstDirectInternal,ENT_QUOTES); ?>"> 
<input type="hidden" name="hdnRoleCount" id="hdnRoleCount" value="">
<input type="hidden" name="hdnJobType" id="hdnJobType" value="<?php echo $JobDet[4]; ?>">
<input type="hidden" name="sm_form_data" id="sm_form_data" value="" />
<input type="hidden" name="sm_enabled_option" id="sm_enabled_option" value="<?php echo SHIFT_SCHEDULING_ENABLED; ?>" />
<input type="hidden" name="theraphySourceEnable" id="theraphySourceEnable" value="<?php echo THERAPY_SOURCE_ENABLED;?>" />
<input type="hidden" name="mode_type" id="mode_type" value="joborder">
<!--hiddens for Shift Name/ Time -->
<input type="hidden" name="shift_time_from" id="shift_time_from" value="" />
<input type="hidden" name="shift_time_to" id="shift_time_to" value="" />

<div class="form-container">
	<fieldset>
		<legend><font class="afontstyle">Settings&nbsp;&nbsp;</font></legend>
		<div class="settings-back">
			<table class="table">
			<tr>
			<td width="450">
			<table class="table">
				<tr>
					<td width="18%">				
					<span id="leftflt"><span class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Status']))?$userFieldsAlias['Status']:'Status';echo getRequiredStar('Status', $userFieldsArr);?>:</span></span></td>
					<td>
						<select class="summaryform-formelement form-select w-250" name=status id=status >
						<option value="">--select--</option>
						<?php 
							echo setManageTypes($manage_table_values["jostatus"],$JobDet[0])
						?>
						</select> <?php if(EDITLIST_ACCESSFLAG){ ?> 
						<a href="javascript:doManage('Status','status');" class="edit-list">edit list</a> 
						<?php } ?>
					</td>	
					</tr>
					<tr>
					<td width="20%">
					<span id="leftflt"><span class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Stage']))?$userFieldsAlias['Stage']:'Stage:';echo getRequiredStar('Stage', $userFieldsArr);?></span></span></td>
					<td>
						<select name="stage" class="summaryform-formelement form-select w-250" id="stage"  onChange="unfill()">
						<option value="">--select--</option>
						<?php 
							echo setManageTypes($manage_table_values["jostage"],$JobDet[1]);
						?>
						</select> <?php if(EDITLIST_ACCESSFLAG){ ?> 
						<a href="javascript:doManage('Job Stage','stage');" class="edit-list">edit list</a> <?php } ?>
					 </td>
					</tr>
					<tr>
						<td>					
						<span id="leftflt"><span class="crmsummary-content-title">Share:</span></span></td>
						<td>
						<select class="summaryform-formelement form-select w-250" name="shareval" onchange=changeShare() <?php if($username!=$jcopyowner && $copyjoborder != 'yes') echo "disabled"; ?>>
							<option value="Private" <?php echo getSel($jcopyshareval,"Private");?>>Private</option>
							<?php if($JobDet[15]!="Share"){?>
							  <option value="Share" <?php echo getSel($jcopyshareval,"Share");?>>Share</option>
							  <?}else{?>
							  <option value="Share" <?php echo getSel($jcopyshareval,"Share");?>>Shared</option><?}?>
							  <option value="Public" <?php echo getSel($jcopyshareval,"Public");?>>Public</option>
						</select>
						<?php if($jcopyshareval=="Share" && $username==$jcopyowner){?>	
						<span class="summaryform-formelement">|</span><span id="jo_shared_type">
			<a href="javascript:SharedPopupWin('/BSOS/Sales/Req_Mngmt/contactShare.php?addr=<?=$posid?>','shareEmps','450','400')" class="crmsummary-contentlnk">view list</a></span>
					 <? } if($jcopyshareval=="Share" && $username!=$jcopyowner){?>	
						<span class="summaryform-formelement">|</span><span id="jo_shared_type">
			<a href="javascript:SharedPopupWin('/BSOS/Sales/Req_Mngmt/viewShareEmp.php?addr=<?=$posid?>','shareEmps','450','400')" class="crmsummary-contentlnk">view list</a></span>
					 <? }?>
						&nbsp;&nbsp;
					</span>
					</td>
					</tr>									
					<tr>
					<td>
					<span id="leftflt"><span class="crmsummary-content-title">Owner</span></span></td>
						<td>
										<select class="summaryform-formelement form-select w-250" name="owner" <?php if($username!=$jcopyowner && $copyjoborder != 'yes') echo "disabled"; ?>>
										<?php
											require_once($akken_psos_include_path.'class.getOwnersList.php');
											$ownersObj = new getOwnersList();
											$Users_Array = $ownersObj->getOwners();
											foreach($Users_Array as $key => $val){
												echo "<option value='".$key."' ".getSel($jcopyowner,$key).">".html_tls_specialchars($val,ENT_QUOTES)."</option>";
											}
										?>
										</select>
									</td>
					</tr>
					<tr>
					 <td>				
					  <span id="leftflt"><span class="crmsummary-content-title">Source Type:</span></span></td>
						<td>
							<select name="jsourcetype" class="summaryform-formelement form-select w-250" id="jsourcetype">
							<option value="">--select--</option>
							<?php 
							echo setManageTypes($manage_table_values["josourcetype"],$JobDet[21]);
							?>
							</select> <?php if(EDITLIST_ACCESSFLAG){ ?> 
							<a href="javascript:doManage('JobSourcetype','jsourcetype');" class="edit-list">edit list</a> <?php } ?>
						</td>
						</tr>

				</table>
			</td>
				 <td width="30" align="center"><img src="../../images/vline.GIF" width="1" height="100" /></td>
				 <td valign="top" width='500' class='editJobOrder'>   
				<?php 
				if(chkUserPref($crmpref,'23')) 
				{
					$ForcePostJobFl = 'N';
					if($JobDet[27]!='')
						$lastposted_date=$JobDet[27]; //refreshed date
					else
						$lastposted_date=$JobDet[25]; //posted date

					if($JobDet[26] == "E" ||  $JobDet[30]=="Expired")
					{
						$ForcePostJobFl = 'Y';
						$websiteMessage="<a class='btn btn-outline-success mb-2' href=javascript:posttoweb($posid,'post'); ><font class=editlinkrow1>Post Job Order to Web Site</font></a><font size=1 color='#70787b' style='vertical-align:top'> [last posted: ".$lastposted_date."]</font>&nbsp;";
					}
					else if($JobDet[26] == "P")
					{
						$websiteMessage="<font size=1 color='#70787b'>Posted to Web Site[".$JobDet[25]."]</font>&nbsp;<a href=javascript:posttoweb($posid,'remove');  class=edit-list><font style='vertical-align:middle'>remove</font></a>";
					}
					else if($JobDet[26] == "R")
					{
						$websiteMessage="<font size=1 color='#70787b'>Refreshed[".$JobDet[27]."]</font>&nbsp;<a href=javascript:posttoweb($posid,'remove');><font class='editlinkrow1' style='vertical-align:middle'>remove</font></a>";
					}					
					else if($JobDet[26] == "RM")
					{
						$ForcePostJobFl = 'Y';
						$websiteMessage="<a class='btn btn-outline-success mb-2' href=javascript:posttoweb($posid,'post');><font class='editlinkrow1'>Post Job Order to Web Site</font></a><font size=1 color='#70787b' style='vertical-align:top'> [last posted: ".$lastposted_date."]</font>";
					}
					else
					{
						$ForcePostJobFl = 'Y';
						$websiteMessage="<a class='btn btn-outline-success mb-2' href=javascript:posttoweb($posid,'post');><font class='editlinkrow1' >Post Job Order to Web Site</font></a>";
					}

					if($copyjoborder != 'yes') 
					{
						echo "<span id='postweblink'>$websiteMessage</span>	<br />";
					}
					else
					{ 
						if(chkUserPref($crmpref,'23')) 
						{
							$website_style= '';
							$johotjob_style= '';

							//for hot job displaying when copy job order 
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
						<span <?php echo  $website_style; ?>>
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="checkbox" name="chkwebsite" id="chkwebsite"onClick="validateJoPosting('posting','chkwebsite','chkhotjob');">
								<label class="form-check-label">
								Post Job Order to Web Site</label>
							</div>
						</span>
						<br/>
						<span <?php echo $johotjob_style; ?>>
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="checkbox" name="chkhotjob" id="chkhotjob"  onClick="validateJoPosting('hotjob','chkwebsite','chkhotjob');">
								<label class="form-check-label">Mark as Hot Job</label>
							</div>
						</span>	
						<?php
					}
				}
				else
				{
					?>
					<span style='display:none'>
						<div class="form-check form-check-inline">
							<input class="form-check-input" type="checkbox" name="chkwebsite" id="chkwebsite" onClick="validateJoPosting('posting','chkwebsite','chkhotjob');">
							<label class="form-check-label">Post Job Order to Web Site</label>
						</div>
					</span>
					<br/>
					<span style='display:none'>
						<div class="form-check form-check-inline">
							<input class="form-check-input" type="checkbox" name="chkhotjob" id="chkhotjob" onClick="validateJoPosting('hotjob','chkwebsite','chkhotjob');">
							<label class="form-check-label">Mark as Hot Job</label>
						</div>
					</span>	
					<?php
				}

				if($copyjoborder != 'yes') 
				{
					//hot job marking/unmarking link displaying based on user preference
					if(chkUserPref($crmpref,'23') && chkUserPref($crmpref,'42'))
					{	
						//Check whether preference for filled_chk is set. if set and closed postitions = no of pos then show the alert when clicked on mark as hot job. S=Need to check
						$filledChksql = "select filled_chk from jobposting_pref";
						$filledChkres = mysql_query($filledChksql,$db);
						$filledChkData = mysql_fetch_row($filledChkres);
						$filledChkRows = mysql_num_rows($filledChkres);
						if($filledChkRows > 0)
						{
							if($filledChkData[0] == "Y")
							{
								$positionsOpen = $JobDet[8]-$JobDet[9];
								if($positionsOpen == 0 && $JobDet[8] > 0)
								{
									$ForcePostJobFl = 'S'; // this is when closed and opening positions are equal then alert the user.
								}
							}
						}

						if($JobDet[43] == 'N')
						{
							echo "<span id='hotjoblink'><a class='btn btn-outline-success mb-2' href=javascript:johotjob(".$posid.",'mark','".$ForcePostJobFl."');><font class='editlinkrow1'>Mark as Hot Job</font></a></span><br/>";
						}
						else if($JobDet[43] == 'Y')
						{
							echo "<span id='hotjoblink'><a class='btn btn-outline-success mb-2' href=javascript:johotjob(".$posid.",'unmark','".$ForcePostJobFl."');><font class='editlinkrow1'>Unmark Hot Job</font></a></span></br>";
						}

					}

					if(DEFAULT_DATAFRENZY_ACCESS == 'Y')
					{
						$jobBoardMsg=JobOrder_postJobBoards($posid); //this function is in global_fun.inc
						echo "<span id='postjblink'>$jobBoardMsg</span>";
						echo "<br/>";
					}
					if(DEFAULT_RAM_ACCESS == 'Y')
					{
						$canSourceMsg=jobOrderSearchResumes($JobDet[23]); //this function is in global_fun.inc
						echo "<span id='postjblink'>$canSourceMsg</span>";
					}
				}
				?> 	
				</td>
			</tr>
			</table>
		</div>
	</fieldset>
	<div class="form-back EditJb-Pad">
	<?php
	/**
	* Include the file to generate user defined fields.
	*
	*/
		$mod = 4;
		include_once($app_inc_path."custom/getcustomfields.php");
	
	?>
	</div>  
	<fieldset class="editMargin-T">
		<legend>Job Order Information</legend>
		<div class="form-back EditJb-Pad editJbInfo">
			<table class="table crmsummary-edit-table align-middle">		
				<tr id="jobtype-data" class="seljob-type" >
					<td width="140" class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Job Type']))?$userFieldsAlias['Job Type']:'Job Type';?> <font class=sfontstyle>*</font></td>
					<td>
					<select name="jobtype" id="jobtype"  onChange="Jobtype('edit')" class="summaryform-formelement form-select w-250">
					<option value="">--select--</option>
					<?php echo setManageTypes($manage_table_values["jotype"],$JobDet[4]);?>
					</select>
					<span id="crm-joborder-formback-msg" class="seljob-font">&nbsp;&nbsp;<?php echo $msg;?></span>
					</td>
				</tr>
				<tr>
					<td class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['HRM Department']))?$userFieldsAlias['HRM Department']:'HRM Department';echo getRequiredStar('HRM Department', $userFieldsArr);?></td>
					<td align=left>
						<?php departmentSelBox('deptjoborder', $JobDet[40], 'summaryform-formelement','','','','yes');?>
					</td>
				</tr>
				<tr> 
					<td class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Industry']))?$userFieldsAlias['Industry']:'Industry';echo getRequiredStar('Industry', $userFieldsArr);?></td>
					<td align=left>
					<select name="joindustryid" id="joindustryid" class="summaryform-formelement form-select w-250">
					<option value="">--select--</option>
					<?php 
						echo setManageTypes($manage_table_values["joindustry"],$JobDet[45]);
					?>
					</select> <?php if(EDITLIST_ACCESSFLAG){ ?> 
					<a href="javascript:doManage('joindustry','joindustryid');" class="edit-list">edit list</a> <?php } ?>	
					</td>
				</tr>
				<tr>
					<td class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Category']))?$userFieldsAlias['Category']:'Category';echo getRequiredStar('Category', $userFieldsArr);?></td>
					<td align=left>
					<select name="jobcat" id="jobcat" class="summaryform-formelement form-select w-250">
					<option value="">--select--</option>
					<?php 
							echo setManageTypes($manage_table_values["jocategory"],$JobDet[5]);
					?>
					</select> <?php if(EDITLIST_ACCESSFLAG){ ?> 
					<a href="javascript:doManage('jocategory','jobcat');" class="edit-list">edit list</a> <?php } ?>
					</td>
				</tr>
				<?php
				/* Theraphy Source :: checking Theraphy Source is Enable or not  */
				if(THERAPY_SOURCE_ENABLED=="Y"){
					if(JOBORDER_TITLES == 'TRUE'){
						?>
					<tr>
						<td class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Job Title']))?$userFieldsAlias['Job Title']:'Job Title';echo getRequiredStar('Job Title', $userFieldsArr);?></td>
						<td>
							<?php
							if(empty($JobDet[6]))
							{
							?>
								<span id="jobtitlespan" class="afontstyle"><?php echo html_tls_specialchars($JobDet[6],ENT_QUOTES);?></span>
								<input name="jobtitle" id="jobtitle" class="summaryform-formelement" type="hidden" size=40 maxsize=150 maxlength=150 value="<?php echo html_tls_specialchars($JobDet[6],ENT_QUOTES);?>" readonly>					
								<span id="jobtitlelinkspan">
									<a href="javascript:doSelectJTitles();" class="edit-list">Select Title</a>
								</span>
							<?php
							}
							else
							{
							?>
								<span id="jobtitlespan" class="afontstyle"><?php echo html_tls_specialchars($JobDet[6],ENT_QUOTES);?></span>
								<input name="jobtitle" id="jobtitle" class="summaryform-formelement" type="hidden" size=40 maxsize=150 maxlength=150 value="<?php echo html_tls_specialchars($JobDet[6],ENT_QUOTES);?>" readonly>					
								<span id="jobtitlelinkspan">
									<a href="javascript:doSelectJTitles();" class="edit-list">Change Title</a>&nbsp;|&nbsp;<a href="javascript:removeTitle()" class="edit-list">Remove Title</a>
								</span>
							<?php
							}
							?>
						</td>				
					</tr>
					<?php

					}
					else
					{
					?>
					<tr>
						<td class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Job Title']))?$userFieldsAlias['Job Title']:'Job Title';echo getRequiredStar('Job Title', $userFieldsArr);?></td>
						<td><input name="jobtitle" id="jobtitle" class="summaryform-formelement form-control-plaintext" type=text size=40 maxsize=150 maxlength=150 value="<?php echo html_tls_specialchars($JobDet[6],ENT_QUOTES);?>"></td>
					</tr>
					<?php
					}

				}else{
					if(JOBORDER_TITLES == 'TRUE')
					{

					?>
					<tr>
						<td class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Job Title']))?$userFieldsAlias['Job Title']:'Job Title';echo getRequiredStar('Job Title', $userFieldsArr);?></td>
						<td>
							<?php
							if(empty($JobDet[6]))
							{
							?>
								<span id="jobtitlespan" class="afontstyle"><?php echo html_tls_specialchars($JobDet[6],ENT_QUOTES);?></span>
								<input name="jobtitle" id="jobtitle" class="summaryform-formelement" type="hidden" size=40 maxsize=150 maxlength=150 value="<?php echo html_tls_specialchars($JobDet[6],ENT_QUOTES);?>" readonly>					
								<span id="jobtitlelinkspan">
									<a href="javascript:doSelectJTitles();" class="edit-list">Select Title</a>
								</span>
							<?php
							}
							else
							{
							?>
								<span id="jobtitlespan" class="afontstyle"><?php echo html_tls_specialchars($JobDet[6],ENT_QUOTES);?></span>
								<input name="jobtitle" id="jobtitle" class="summaryform-formelement" type="hidden" size=40 maxsize=150 maxlength=150 value="<?php echo html_tls_specialchars($JobDet[6],ENT_QUOTES);?>" readonly>					
								<span id="jobtitlelinkspan">
									<a href="javascript:doSelectJTitles();" class="edit-list">Change Title</a>&nbsp;|&nbsp;<a href="javascript:removeTitle()" class="edit-list">Remove Title</a>
								</span>
							<?php
							}
							?>
						</td>				
					</tr>
					<?php
					}
					else
					{
					?>
					<tr>
						<td class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Job Title']))?$userFieldsAlias['Job Title']:'Job Title';echo getRequiredStar('Job Title', $userFieldsArr);?></td>
						<td><input name="jobtitle" id="jobtitle" class="summaryform-formelement form-control-plaintext" type=text size=40 maxsize=150 maxlength=150 value="<?php echo html_tls_specialchars($JobDet[6],ENT_QUOTES);?>"></td>
					</tr>
					<?php
					}
				}	
				?>
				<tr>
					<td class="crmsummary-content-title">Ref. Code</td>
					<td align=left><input name="refcode" id="refcode" class="summaryform-formelement form-control w-250" type=text size=40 maxsize=25 maxlength=25 value="<?php echo html_tls_specialchars($JobDet[7],ENT_QUOTES);?>"></td>
				</tr>
				<?php if($copyjoborder != 'yes') { ?>			
				<tr>
					<td class="crmsummary-content-title">Job Order ID#</td>
					<td align=left><span class="summaryform-formelement"> <?php echo $JobDet[23]; ?></span></td>
				</tr>
				<?php } ?>
				<tr>
					<td class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['No. Positions']))?$userFieldsAlias['No. Positions']:'No. Positions';echo getRequiredStar('No. Positions', $userFieldsArr);?></td>
					<td align=left><input name="pos" id="pos" class="summaryform-formelement" type=text size=3 maxsize=3 maxlength=3 value="<?php echo html_tls_specialchars($JobDet[8],ENT_QUOTES);?>">&nbsp;&nbsp;&nbsp;<span class="summaryform-nonboldsub-title">Filled</span>&nbsp;<input name="posfilled" id="posfilled" class="summaryform-formelement" type=text size=3 maxsize=3 maxlength=3 value="<?php if($copyjoborder != 'yes') echo html_tls_specialchars($JobDet[9],ENT_QUOTES); ?>"></td>
				</tr>			

                <tr>
					<td width="220" class="crmsummary-content-title">Ultigigs Timesheet Layout</td>
					<td>
						<select name="joborder_timesheet_layout_preference" id="joborder_timesheet_layout_preference">
							<option value="" <?php if($timesheet_layout_preference == "") { echo "selected"; } ?>>--- Select Template ---</option>
							<option value="Regular" <?php if($timesheet_layout_preference == "Regular") { echo "selected"; } ?>>Regular</option>
							<option value="TimeInTimeOut" <?php if($timesheet_layout_preference == "TimeInTimeOut") { echo "selected"; } ?>>Time In &amp; Time Out</option>
							<option value="Clockinout" <?php if($timesheet_layout_preference == "Clockinout") { echo "selected"; } ?>>Clock In &amp; Out</option>
						</select>
					</td>
				</tr>				
				
			  </table>
		</div>

		<div class="form-back">
			<table class="table crmsummary-edit-table align-middle">
			<tr>
				<td width="140" class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Company']))?$userFieldsAlias['Company']:'Company';echo (!empty($mandatory_madison))? $mandatory_madison:getRequiredStar('Company', $userFieldsArr);?></td>
				<td>

				<input type="hidden" name="company" id='company' value="<?=$JobDet[10];?>">
				<input type='hidden' name='comprows' id='comprows'  value='0'>
				<input type="hidden" name="compname" id='compname'  value="<?=html_tls_specialchars($Comp_Name,ENT_QUOTES);?>">

				<span id='company-change'>
				<?php
				if($Comp_No=='')
				{
					print "<a class=crm-select-link href=javascript:parent_popup('jobcompany')>select company</a>&nbsp;<a href=javascript:parent_popup('jobcompany')><i class='fa fa-search fa-lg'></i></a><span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class=crm-select-link href=javascript:newScreen('company','jobcompany')>new company</a></span>";
				}
				else
				{
					if($chkCompAccess == "No")
						print "<span id=comp_name class=summaryform-formelement>".html_tls_specialchars($Comp_Name,ENT_QUOTES)."</span>";
					else
						print "<a class=crm-select-link href=javascript:viewSummary('company','".$Comp_No."','".$module."')><span id=comp_name>".html_tls_specialchars($Comp_Name,ENT_QUOTES)."</span></a>";

					print "<span class='summaryform-nonboldsub-title'>&nbsp;(&nbsp;</span><a href=javascript:parent_popup('jobcompany') class='edit-list'>change</a>&nbsp;<a href=javascript:parent_popup('jobcompany')><i class='fa fa-search fa-lg'></i></a>";
					print "<span class='summaryform-formelement'>&nbsp;|&nbsp;</span><a href=javascript:newScreen('company','jobcompany'); class='edit-list'>new</a>&nbsp;<span class='summaryform-nonboldsub-title'>)</span>&nbsp;&nbsp;<span id='crm-joborder-companyDiv-span-remove'><a href=javascript:RemoveSelectedItem('jobcompany','') class='edit-list' title='Remove Company'>remove</a></span>";
				}
				?>
				</span>
				</td>
			</tr>
			</table>
		</div>
		<div class="form-back">
			<table class="table crmsummary-edit-table align-middle">
			<tr>
				<td width="140" class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Contact']))?$userFieldsAlias['Contact']:'Contact';echo getRequiredStar('Contact', $userFieldsArr);?></td>
				<td>

				<input type="hidden" name="contact" id="contact" value="<?=$JobDet[12];?>">
				<input type='hidden' name='controws' id='controws' value='0'>
				<input type="hidden" name="conname" value="<?=html_tls_specialchars($Cont_Name,ENT_QUOTES);?>">

				<span id="contact-change">
				<?php
				if($Cont_No=='')
				{
					print "<a class=crm-select-link href=javascript:contact_popup1('refcontact')>select contact</a>&nbsp;<a href=javascript:contact_popup1('refcontact')><i class='fa fa-search fa-lg'></i></a><span class=summaryform-formelement>&nbsp;|&nbsp;</span><a href=javascript:newScreen('contact','refcontact'); class=edit-list>new contact</a>";
				}
				else
				{
					if($chkContAccess == "No")
						print "<span id='conname-change' class='summaryform-formelement'>".html_tls_specialchars($Cont_Name,ENT_QUOTES)."</span>";
					else
						print "<span id='conname-change'><a class=crm-select-link href=javascript:viewSummary('contact','".$Cont_No."','".$module."')>".html_tls_specialchars($Cont_Name,ENT_QUOTES)."</a></span>";

					print "<span class='summaryform-nonboldsub-title'>&nbsp;(&nbsp;</span>".$Cont_options;
					print "<span id='Change_new'><a href=javascript:contact_popup1('refcontact') class='edit-list'>change</a>&nbsp;<a href=javascript:contact_popup1('refcontact')><i class='fa fa-search fa-lg'></i></a>";
					print "<span class='summaryform-formelement'>&nbsp;|&nbsp;</span><a href=javascript:newScreen('contact','refcontact'); class='edit-list'>new</a><span class='summaryform-nonboldsub-title'>&nbsp;)&nbsp;</span><span id='contactRemove'>&nbsp;&nbsp;<a href=javascript:RemoveSelectedItem('refcontact','Yes') class='edit-list' title='Remove Contact'>remove</a></span>";
				}
				?>
				</span>
				</td>
			</tr>
			</table>
			<?php
					$billcontact = $JobDet[42];
					$bill_loc = $JobDet[41];
					if ($billcontact != 0) {
						$que2 = "select TRIM(CONCAT_WS('',fname,' ',lname,'(',IF(email='',nickname,email),')')),status,fname,mname,lname,address1,address2,city,state,csno,accessto from staffoppr_contact where sno='" . $billcontact . "'";
						$res2 = mysql_query($que2, $db);
						$row2 = mysql_fetch_row($res2);
						$billcont = $billcont = $row2[2] . " " . $row2[3] . " " . $row2[4];
						$billcont_stat = $row2[1];
						$billcompany = $row2[9];
						$chkBILLContAccess = chkAvailAccessTo($row2[10], $row2[1], $username);
					} else if ($bill_loc > 0 && ($billcontact == 0 || $billcontact == "")) {
						$que2 = "select csno from staffoppr_location where sno='" . $bill_loc . "' and ltype in ('com','loc')";
						$res2 = mysql_query($que2, $db);
						$row2 = mysql_fetch_row($res2);
						$billcompany = $row2[0];
					}
				?>
			<input type="hidden" name="billcontact_sno" id="billcontact_sno" value="<?php echo $billcontact;?>">
		</div>
		<?php
			$jrtcontact=$JobDet[13];
			$jrt_loc=$JobDet[11];
			if($jrtcontact!=0)
		{
			$que2="select TRIM(CONCAT_WS('',fname,' ',lname,'(',IF(email='',nickname,email),')')),status,fname,mname,lname,address1,address2,city,state,csno,accessto from staffoppr_contact where sno='".$jrtcontact."'";
			$res2=mysql_query($que2,$db);
			$row2=mysql_fetch_row($res2);
			$jrtcont=$row2[2]." ".$row2[3]." ".$row2[4];
			$jrtcont_stat=$row2[1];
			$jrtcompany=$row2[9];
			$chkJRTContAccess = chkAvailAccessTo($row2[10],$row2[1],$username);
		}
		else if($jrt_loc>0 && ($jrtcontact==0 || $jrtcontact==""))
		{
			$que2="select csno from staffoppr_location where sno='".$jrt_loc."' and ltype in ('com','loc')";
			$res2=mysql_query($que2,$db);
			$row2=mysql_fetch_row($res2);
			$jrtcompany=$row2[0];
		}
		?>
		<div class="form-back editJbLocation">
			<table class="table crmsummary-edit-table align-middle">
			<tr class="locationSelect">
				<input type="hidden" name="jrtcompany_sno" id="jrtcompany_sno" value="<?php echo $jrtcompany;?>">
				<td width=140 class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Job Location']))?$userFieldsAlias['Job Location']:'Job Location';echo getRequiredStar('Job Location', $userFieldsArr);?></td>
				<td><input type=text name=wsjl size=20 value='<?php echo $wsjl;?>'>&nbsp;&nbsp;<span id="jrtdisp_comp"><input type="hidden" name="jrt_loc" id="jrt_loc" value=""><a class="crm-select-link" href="javascript:bill_jrt_comp('jrt')">select company</a>&nbsp;</span></span>&nbsp;<span id="jrtcomp_chgid">&nbsp;</span></td>
			</tr>
			</table>
		</div>
		<div class="form-back editJbReport">
			<table class="table crmsummary-edit-table align-middle">
			<tr>
				<input type="hidden" name="jrtcontact_sno" id="jrtcontact_sno" value="<?php echo $jrtcontact;?>">
				<td width="140" class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Job Reports To']))?$userFieldsAlias['Job Reports To']:'Job Reports To';echo getRequiredStar('Job Reports To', $userFieldsArr);?></td>
				<td>
				<?php
				if($jrtcontact=='0' ||  $jrtcontact=='')
				{
					?>
					<span id="jrtdisp">
					<a class="crm-select-link" href="javascript:bill_jrt_cont('jrt')">select contact</a></span>
					&nbsp;<span id="jrtchgid"><a href="javascript:bill_jrt_cont('jrt')"><i class='fa fa-search fa-lg'></i></a>
					<span class="summaryform-formelement">&nbsp;|&nbsp;</span>
					<a class="crm-select-link" href="javascript:donew_add('jrt')">new&nbsp;contact</a>
					</span>
					<?
				}
				else 
				{ 
					?>
					<span id="jrtdisp">
						<?php
						if($chkJRTContAccess == "No")
						{
							echo "<span class=summaryform-formelement>".dispfdb($jrtcont)."</span>";
						}
						else
						{
						?>
							<a class="crm-select-link" href="javascript:contact_func('<?php echo $jrtcontact;?>','<?php echo $jrtcont_stat;?>','jrt')"><?php echo dispfdb($jrtcont);?></a>
						<?php
						}
						?>
					</span>
					&nbsp;<span id="jrtchgid">
					<span class=summaryform-formelement>(</span><a class=crm-select-link href=javascript:bill_jrt_cont('jrt')>change </a>
					&nbsp;<a href=javascript:bill_jrt_cont('jrt')><i class='fa fa-search fa-lg'></i></a>
					<span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class=crm-select-link href=javascript:donew_add('jrt')>new</a>
					<span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class="crm-select-link" href="javascript:removeContact('jrt')">remove&nbsp;</a>
					<span class=summaryform-formelement>&nbsp;)&nbsp;</span>
					</span>
					<?
				}
				?>
				</td>
			</tr>
			</table>
		</div>
		
        <div class="form-back accordion-item" >
            <table width="100%" border="0" class="crmsummary-edit-tablemin" id="crm-joborder-billinginfoDiv-Table">
                <tr>
                    <td width="140" class="crmsummary-content-title">
                        <div id="crm-joborder-billinginfoDiv-plus1"><a style='text-decoration: none;' href="javascript:classToggle('billinginfo','plus')"><span class="crmsummary-content-title">Billing Information</span></a></div>
                        <div id="crm-joborder-billinginfoDiv-minus1" class="DisplayNone"><a  style='text-decoration: none;' href="javascript:classToggle('billinginfo','minus')"><span class="crmsummary-content-title">Billing Information</span></a></div>
                    </td>
                    <td>&nbsp;</td>
                    <td>
                        <span id="rightflt" <?php echo $rightflt; ?>>
                            <span class="summaryform-bold-close-title" id="crm-joborder-billinginfoDiv-close" style="display:none;width:auto;"><a style='text-decoration: none;' onClick="classToggle('billinginfo','minus')"  href="#crm-joborder-billinginfoDiv-plus">close</a></span>
                            <span class="summaryform-bold-close-title" id="crm-joborder-billinginfoDiv-open" style="width:auto;"><a style='text-decoration: none;' onClick="classToggle('billinginfo','plus')"  href="#crm-joborder-billinginfoDiv-minus">open</a></span>
                            <div class="form-opcl-btnleftside"><div align="left"></div></div>
                            <div id="crm-joborder-billinginfoDiv-plus"><span onClick="classToggle('billinginfo','plus')" class="form-op-txtlnk crmsummary-process-mousepointer" id="billinfotab" ><b>+</b></span></div>
                            <div id="crm-joborder-billinginfoDiv-minus"  class="DisplayNone"><span onClick="classToggle('billinginfo','minus')" class="form-op-txtlnk crmsummary-process-mousepointer"><b>-</b></span></div>
                            <div class="form-opcl-btnrightside"><div align="left"></div></div>
                        </span>
                    </td>
                </tr>
            </table>
        </div>
            <!--Billing information tabbed pane starts -->
        <div class="jocomp-back DisplayNone" id="crm-joborder-billinginfoDiv" name="crm-joborder-billinginfoDiv">
            <div class="jocomp-back DisplayNone" id="crm-joborder-billinginfoDiv_tab" name="crm-joborder-billinginfoDiv_tab" <?php echo $style_table;?>>
                <table class="crmsummary-jocomp-table table">
                    <tr>
                        <th id="rate_cond" colspan="2">
                            Rates
                        </th>
                    </tr>
                 </table>
            </div>
            <div class="jocomp-back DisplayNone" id="crm-joborder-billinginfoDiv2" name="crm-joborder-billinginfoDiv2"  <?php echo $style_table;?>>
                <input type="hidden" name='src_status' value="rates">
									<!-- Rate tab start-->
								<span id=rate_avail style="display:block">
									<table class="table crmsummary-jocomp-table">
										<?php if(RATE_CALCULATOR=='Y'){ ?>
										<tr>
											<td colspan="2">
												<span class="billInfoNoteStyle">
													Note: Pay Rate or Bill Rate is calculated using Margin as the default. To calculate using Markup leave Margin blank.
												</span>
											</td>
										</tr>
										<?php } ?>
										<tr>
											<td width="13%" class="summaryform-bold-title">
											   Regular <?php echo (!empty($userFieldsAlias['Pay Rate'])) ? $userFieldsAlias['Pay Rate'] : 'Pay Rate'; echo getRequiredStar('Pay Rate', $userFieldsArr); ?><?php if(TRICOM_REPORTS=='Y'){echo $mandatory_madison;}?>
											</td>
											<td>
												<div class="row align-items-center">
													<?php if(RATE_CALCULATOR=='Y'){ ?>
													<div class="col-auto">
														<a role="button" id="payrate_calculator" style="display:none;" onclick="javascript:payrateCalculatorFunc(this);">
															<i class="fa fa-calculator" aria-hidden="true"></i>
														</a>
													</div>
													<?php } ?>
													<div class="col-auto">
														<div class="form-check">
															<input class="form-check-input" name="payratetype" id="payratetype" type="radio" value="rate" checked>
															<label class="form-check-label" for="payratetype">Rate</label>
														</div>
													</div>
													<div class="col-auto">
														<!--The hidden variable prevpayratevalue is used for passing the Pay Rate value to function  promptOnChangePayRate()-->
														<input type="hidden" name="prevpayratevalue" id="prevpayratevalue" value="<?php echo $JobDet[48]; ?>">
														<input name="comm_payrate" id="comm_payrate" type="text" value="" size=10 maxlength="9" class="summaryform-formelement" onfocus="javascript:confirmPayRateAutoCalculatoin('comm_payrate', 'calculateComPayRates');" onkeyup="javascript:calculateComPayRates();" onkeypress="return blockNonNumbers(this, event, true, false);" onblur="clearConfirmPayRateAutoCalculatoin();" <?php if(RATE_CALCULATOR=='N'){ ?>  onChange="promptOnChangePayRate();" <?php } ?>>
													</div>
													<div class="col-auto">
														<!--UOM: get dynamic rate types-->
														<select name="payrateper" id="payrateper" onclick="getPreviousRate(this);" onChange="change_Period('billrate');" onChange="change_Period('billrate');" class="summaryform-formelement">
														<?php echo $timeOptions;?>
														</select>
													</div>
													<div class="col-auto">
														<select name="payratecur" id="payratecur" onChange="change_PeriodNew('billrate');" class="form-select">
															<?php
															displayCurrency('');
															?>
														</select>
													</div>
													<div class="col-auto">
														<div id='reg_pay_Bill_NonBill'>
															<div class="form-check form-check-inline">
																<input class="form-chekc-input" name="payrateBillOpt" id="payrateBillOpt"  type="radio" value="Y" <?php if ($ratesDefaultVal['Regular'][0] != "") echo getChk($ratesDefaultVal['Regular'][0], "Y"); else echo "checked"; ?>>
																<label class="form-chekc-label" for="payrateBillOpt">Billable</label>
															</div>
															<div class="form-check form-check-inline">
																<input class="form-chekc-input" name="payrateBillOpt" id="payrateBillOpt" type="radio" value="N" <?php echo getChk($ratesDefaultVal['Regular'][0], "N"); ?>><label class="form-chekc-label" for="payrateBillOpt">Non-Billable</label>
															</div>
														</div>
													</div>
												</div>
												<div class="d-flex align-items-center gap-3 mt-1">
													<div class="form-check">
														<input name="payratetype" id="payratetype" type="radio" value="open" class="form-check-input" >
														<label class="form-check-label">Open</label>
													</div>
													<input name="comm_open_payrate" type="text" id="comm_open_payrate" value="" size=38 class="summaryform-formelement" style="margin-top:2px" >
												</div>
											</td>
										</tr>
										<tr id="burden-rate" >
											<td>Pay Burden <?php echo ($payburden_status=='Y')? "<span style='color:red'>*</span>": "" ?></td>
											<td>
											   <input type="hidden" id="manage_burden_status" value="<?php echo $burden_status;?>">
												<input type="hidden" name='autosetwcc' id="autosetwcc" value="<?php echo $autowcc_status;?>"/>
												<input type="hidden" name='payburdenstatus' id="payburdenstatus" value="<?php echo $payburden_status;?>"/>
												<?php
												if ($burden_status == 'yes') {
													if($autowcc_status == 'Y')
													{
														$Addfunction = "AutoWCChangeAction(this,'joborder',true);";
													}else{
														$Addfunction ="";
													}	
													?>
													<input type="hidden" name="btdefaultchk" id="btdefaultchk" value="0" />
													<input type="hidden" name="hdnbi_details" id="hdnbi_details" value="<?php echo $edit_existing_bi_str; ?>" />
													<input type="hidden" name="hdnbt_details" id="hdnbt_details" value="<?php echo $edit_bt_detail_str; ?>" />
													<input type="hidden" name="edithdn_bt_str" id="edithdn_bt_str" value="<?php echo $edit_bt_detail_str; ?>" />
													<input type="hidden" name="edithdn_bi_str" id="edithdn_bi_str" value="<?php echo $edit_bi_detail_str; ?>" />
													<input type="hidden" name="hdnTotalBurdenPerc" id="hdnTotalBurdenPerc" value="0" />
													<input type="hidden" name="hdnTotalBurdenFlat" id="hdnTotalBurdenFlat" value="0" />
													<input type="hidden" name="comm_burden" id="comm_burden" value="" />
													<div class="BTContainer">
														<div>
															<select name="burdenType" id="burdenType" <?php if(RATE_CALCULATOR=='Y'){?> onchange="doNoCalculateMarkup(this);BTChangeAction(this,'joborder');<?php echo $Addfunction;?>" <?php } else{ ?> onchange="BTChangeAction(this,'joborder');<?php echo $Addfunction;?>" <?php } ?>>
															<?php 
															if($payburden_status == 'Y')
															{
															?>
															 <option value="">--Select Pay Burden--</option>
															<?php
															}
															echo $existingBurdenOpt;
															foreach ($arr_burden_type as $sno => $burden) {
																if ($burden['rate_type'] == 'payrate') {
																	?>
																	<option value="<?php if ($bt_sno == $sno) {
																		echo "existing";
																		} else {
																			echo $sno;
																		} ?>" <?php if ($bt_sno == $sno) echo "selected"; ?>><?php echo $burden["burden_type"]; ?></option>
																		<?php
																	}
																}
																?>
															</select>
														</div>
														<div>
															<span id="burdenItemsStr" class="summaryform-formelement">&nbsp;</span>
														</div>
													</div>
												<?php
											} else {
												$chk_bt = false;
												?>
													<div class="BTContainer">
														<div>
															<input type="text" name="comm_burden" id="comm_burden" value="<?php echo $JobDet[44]; ?>" <?php if(RATE_CALCULATOR=='Y'){?> onkeyup="doNoCalculateMarkup(this);calculatebtmargin();" <?php } else{ ?> onkeyup="calculatebtmargin();" <?php } ?>  maxlength="9" size="10" onkeypress="return blockNonNumbers(this, event, true, false);"/>
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
											<td>
											   Regular <?php echo (!empty($userFieldsAlias['Bill Rate'])) ? $userFieldsAlias['Bill Rate'] : 'Bill Rate'; echo getRequiredStar('Bill Rate', $userFieldsArr); ?><?php if(TRICOM_REPORTS=='Y'){echo $mandatory_madison;}?>
											</td>
											<td>
												<div class="row align-items-center">
													<?php if(RATE_CALCULATOR=='Y'){ ?>
													<div class="col-auto">
														<span id="billrate_calculator" style="float:left;cursor:pointer;display:none;">
															<i class="fa fa-calculator" onclick="javascript:billrateCalculatorFunc(this);" aria-hidden="true"></i>
														</span>
													</div>
													<?php } ?>
													<div class="col-auto">
														<input type="radio" name="billratetype" id="billratetype" value="rate" class="form-check-input" checked>
														<label class="form-check-label" for="billratetype">Rate</label>
													</div>
													<div class="col-auto">
														<!--The hidden variable prevbillratevalue is used for passing the Bill Rate value to function  promptOnChangeBillRate()-->
														<input type="hidden" name="prevbillratevalue" id="prevbillratevalue" value="<?php echo $JobDet[47]; ?>">
														<input name="comm_billrate" type="text" id="comm_billrate" value="" size=10 maxlength="9" class="summaryform-formelement" onfocus="javascript:confirmBillRateAutoCalculatoin('comm_billrate');" onkeyup="javascript:calculateComBillRates();" onkeypress="return blockNonNumbers(this, event, true, false);" onblur="clearConfirmBillRateAutoCalculatoin();" <?php if(RATE_CALCULATOR=='N'){ ?> onChange="promptOnChangeBillRate();" <?php } ?> >
													</div>
													<div class="col-auto">
														<!--UOM: get dynamic rate types-->
														<select name="billrateper" id="billrateper" onclick="getPreviousRate(this);"  onChange="change_Period('payrate');" class="summaryform-formelement">
														<?php echo $timeOptions;?>
														</select>
													</div>
													<div class="col-auto">
														<select name="billratecur" id="billratecur" onChange="change_PeriodNew('payrate');" class="summaryform-formelement">
															<?php
															displayCurrency('');
															?>
														</select>
													</div>
													<div class="col-auto" id='reg_bill_Tax_NonTax'>
														<div class="form-check form-check-inline">
															<input class="form-check-input" name="billrateTaxOpt" id="billrateTaxOpt"  type="radio" value="Y" <?php if ($ratesDefaultVal['Regular'][1] != "") echo getChk($ratesDefaultVal['Regular'][1], "Y"); else echo "checked"; ?>>
															<label class="form-check-label">Taxable</label>
														</div>
														<div class="form-check form-check-inline">
															<input class="form-check-input" name="billrateTaxOpt" id="billrateTaxOpt"  type="radio" value="N" <?php echo getChk($ratesDefaultVal['Regular'][1], "N"); ?>>
															<label class="form-check-label">Non-Taxable</label>
														</div>
													</div>
												</div>

												<div class="d-flex align-items-center gap-3 mt-1">
												<span style="float:left"><input name="billratetype" id="billratetype" type="radio" value="open" class="summaryform-formelement"></span><span class="summaryform-formelement" style="float:left; margin-right:5px; line-height:20px;">Open</span><span style="float:left"><input name="comm_open_billrate" type="text" id="comm_open_billrate" value="" size=38 class="summaryform-formelement"></span></div>
											</td>
										</tr>
										<tr id="burden-rate" >
											<td class="summaryform-bold-title">Bill Burden <?php echo ($billburden_status=='Y')? "<span style='color:red'>*</span>": "" ?></td>
												 <input type="hidden" name='billburdenstatus' id="billburdenstatus" value="<?php echo $billburden_status;?>"/>
											<td>
												<?php
												if ($burden_status == 'yes') {
													?>
													<input type="hidden" name="bill_btdefaultchk" id="bill_btdefaultchk" value="0" />
													<input type="hidden" name="bill_hdnbi_details" id="bill_hdnbi_details" value="<?php echo $edit_existing_bill_str; ?>" />
													<input type="hidden" name="bill_hdnbt_details" id="bill_hdnbt_details" value="<?php echo $edit_bt_billdetail_str; ?>" />
												<input type="hidden" name="bill_edithdn_bt_str" id="bill_edithdn_bt_str" value="<?php echo $edit_bt_billdetail_str; ?>" />
													<input type="hidden" name="bill_edithdn_bi_str" id="bill_edithdn_bi_str" value="<?php echo $edit_bi_bill_detail; ?>" />
													<input type="hidden" name="bill_hdnTotalBurdenPerc" id="bill_hdnTotalBurdenPerc" value="0" />
													<input type="hidden" name="bill_hdnTotalBurdenFlat" id="bill_hdnTotalBurdenFlat" value="0" />
													<input type="hidden" name="comm_bill_burden" id="comm_bill_burden" value="" />
													<div class="BTContainer">
														<div>
															<select name="bill_burdenType" id="bill_burdenType" <?php if(RATE_CALCULATOR=='Y'){?> onchange="doNoCalculateMarkup(this);BillBTChangeAction(this,'joborder');" <?php } else{ ?> onchange="BillBTChangeAction(this,'joborder');" <?php } ?>>
																<?php 
																if($billburden_status == 'Y')
																{
																?>
																<option value="">--Select Bill Burden--</option>
																<?php
																}
																echo $existingBillBurdenOpt;

																foreach ($arr_burden_type as $sno => $burden) {

																	if ($burden['rate_type'] == 'billrate') {
																		?>
																		<option value="<?php if ($bt_billsno == $sno) {
																echo "existing";
															} else {
																echo $sno;
															} ?>" <?php if ($bt_billsno == $sno) echo "selected"; ?>><?php echo $burden["burden_type"]; ?></option>
															<?php
														}
													}
													?>
															</select>
														</div>
														<div style="vertical-align:middle;">
															<b><span id="bill_burdenItemsStr" class="summaryform-formelement">&nbsp;</span></b>
														</div>
													</div>
														<?php
													} else {
														?>
													<div class="BTContainer">
														<div>
															<input type="text" name="comm_bill_burden" id="comm_bill_burden" value="<?php echo $JobDet[46]; ?>" maxlength="9" size="10" <?php if(RATE_CALCULATOR=='Y'){?> onkeyup="doNoCalculateMarkup(this);calculatebillbtmargin();" <?php } else{ ?> onkeyup="calculatebillbtmargin();" <?php } ?> onkeypress="return blockNonNumbers(this, event, true, false);"/>
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
												<input type=hidden  size="10" maxlength="9" value="" onkeypress="return blockNonNumMarginMarkup(this, event);" name=comm_margin  id=comm_margin class="summaryform-formelement"><span class="summaryform-formelement" style="display:none;" id="comm_margin_span">0.00</span>&nbsp;<span class="summaryform-formelement"><b>%</b></span>&nbsp;<span style="display:none;" class="summaryform-formelement">|</span>&nbsp;<span style="display:none;" class="summaryform-formelement"><b><span style="display:none;" id="margincost">$0.00</span></b></span>
												 <?php
													$qry = "select netmargin from margin_setup where sno=1"; 
													$qry_res = mysql_query($qry,$db);
													$qry_row = mysql_fetch_row($qry_res); 
												?>
										&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="summaryform-formelement">(Company&nbsp;Min&nbsp;Margin:&nbsp;<?=$qry_row[0];?>%)</span>
											</td>
										</tr>
										<tr id="markup-rate" >
											<td class="summaryform-bold-title">Markup&nbsp;</td>
											<td>
												<span id="markup_calculator" style="cursor:pointer;display:none;"><i class="fa fa-calculator" onclick="markupCalculatorFunc(this);"aria-hidden="true"></i>&nbsp;&nbsp;</span>
												<input name="comm_markup" type="hidden" id="comm_markup" value="" size="10" maxlength="9" onkeypress="return blockNonNumMarginMarkup(this, event);" class="summaryform-formelement"><span class="summaryform-formelement" style="display:none;" id="comm_markup_span">0.00</span>&nbsp;<span  class="summaryform-formelement" id="comm_markup_span_perc"><b>%</b></span>
											</td>
										</tr>
										 <?php } else { ?>
											 <tr id="marg-rate" >
											<td class="summaryform-bold-title">Margin&nbsp;</td>
											<td><input type=hidden  maxlength=10 size=10 name=comm_margin id=comm_margin class="summaryform-formelement" readonly><span class="summaryform-formelement" id="comm_margin_span">0.00</span>&nbsp;<span class="summaryform-formelement"><b>%</b></span>&nbsp;<span class="summaryform-formelement">|</span>&nbsp;<span class="summaryform-formelement"><b><span id="margincost">$0.00</span></b></span>
												 <?php
													$qry = "select netmargin from margin_setup where sno=1"; 
													$qry_res = mysql_query($qry,$db);
													$qry_row = mysql_fetch_row($qry_res); 
												?>
										&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="summaryform-formelement">(Company&nbsp;Min&nbsp;Margin:&nbsp;<?=$qry_row[0];?>%)</span>
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


							<div class="jocomp-back"  <?php echo $style_table;?>>
								<table class="table table-borderless align-middle crmsummary-jocomp-table">
									<tr id="overtime_rate_pay" style="display:">
									<?php
										$strCurry = displayCurrencySelOpt('');
										$arr = $objMRT->getRateTypeById(2);
										if ($arr['peditable'] == 'N') {
											$pdisabled_user_input_field = ' disabled_user_input_field';
											$pdisable = ' disabled="disabled"';
										} else {
											$pdisabled_user_input_field = '';
											$pdisable = '';
										}
										if ($arr['beditable'] == 'N') {
											$bdisabled_user_input_field = ' disabled_user_input_field';
											$bdisable = ' disabled="disabled"';
										} else {
											$bdisabled_user_input_field = '';
											$bdisable = '';
										}
										?>
										<td class="summaryform-bold-title"><?php echo $arr['name']; ?> Pay Rate<?php if(TRICOM_REPORTS=='Y'){echo $mandatory_madison;}?></td>
										<td>
											<span id="leftflt"><input type="text" class="summaryform-formelement<?php echo $pdisabled_user_input_field;?>" maxlength="9" size="10" value="" id="otrate_pay" name="otrate_pay" <?php echo $pdisable; ?>><input type="hidden" id="otrate_pay_hidden" value="<?php echo $arr['pvalue']; ?>"></span>
											<span id="leftflt">&nbsp;&nbsp;
								<!--UOM: get dynamic rate types-->
												<select class="summaryform-formelement<?php echo $pdisabled_user_input_field;?>"  onclick="getPreviousRate(this);"  onChange="change_Period('overtimepayrate');"  id="otper_pay" name="otper_pay" <?php echo $pdisable; ?>>
													<?php echo $timeOptions; ?>
												</select>
											</span>
											<span id="leftflt">&nbsp;&nbsp;
												<select class="summaryform-formelement<?php echo $pdisabled_user_input_field;?>" onChange="change_PeriodNew('overtimepayratecur');" id="otcurrency_pay" name="otcurrency_pay" <?php echo $pdisable; ?>>
													<?php echo $strCurry; ?>
												</select>
											</span>
											<span id='ot_pay_Bill_NonBill'>
												<div class="form-check form-check-inline">
													<input class="form-check-input" name="OvpayrateBillOpt" id="OvpayrateBillOpt"  type="radio" value="Y" <?php if($ratesDefaultVal['OverTime'][0] == 'Y' || $ratesDefaultVal['OverTime'][0] == ''){echo ' checked="checked"';}else{ echo $pdisable;}?>>
													<label class="form-check-label" for="OvpayrateBillOpt">Billable</label>
												</div>
												<div class="form-check form-check-inline">
													<input class="form-check-input" name="OvpayrateBillOpt" id="OvpayrateBillOpt"  type="radio" value="N" <?php if($ratesDefaultVal['OverTime'][0] == 'N'){echo ' checked="checked"';}else{ echo $pdisable;}?>>
													<label class="form-check-label" for="OvpayrateBillOpt">Non-Billable</label>
												</div>
											</span>
										</td>
									</tr>
									<tr id="overtime_rate_bill" style="display:">
										<td class="summaryform-bold-title"><?php echo $arr['name']; ?> Bill Rate<?php if(TRICOM_REPORTS=='Y'){echo $mandatory_madison;}?></td>
										<td>
											<span id="leftflt"><input type="text" class="summaryform-formelement<?php echo $bdisabled_user_input_field;?>" maxlength="9" size="10" value="" id="otrate_bill" name="otrate_bill" <?php echo $bdisable; ?>><input type="hidden" id="otrate_bill_hidden" value="<?php echo $arr['bvalue']; ?>">
											</span>
											<span id="leftflt">&nbsp;&nbsp;
								<!--UOM: get dynamic rate types-->
												<select class="summaryform-formelement<?php echo $bdisabled_user_input_field;?>" onclick="getPreviousRate(this);"  onChange="change_Period('overtimebillrate');" id="otper_bill" name="otper_bill" <?php echo $bdisable; ?>>
													<?php echo $timeOptions; ?>
												</select>
											</span>
											<span id="leftflt">&nbsp;&nbsp;
												<select class="summaryform-formelement<?php echo $bdisabled_user_input_field;?>" id="otcurrency_bill" onChange="change_PeriodNew('overtimebillratecur');" name="otcurrency_bill" <?php echo $bdisable; ?>>
													<?php echo $strCurry; ?>
												</select>
											</span>
											<span id='ot_bill_Tax_NonTax'>
												<div class="form-check form-check-inline">
													<input class="form-check-input" name="OvbillrateTaxOpt" id="OvbillrateTaxOpt"  type="radio" value="Y" <?php if($ratesDefaultVal['OverTime'][1] == 'Y' || $ratesDefaultVal['OverTime'][1] == ''){echo ' checked="checked"';}else{ echo $bdisable;}?>>
													<label class="form-check-label" for="OvbillrateTaxOpt">Taxable</label>
												</div>
												<div class="form-check form-check-inline">
													<input class="form-check-input" name="OvbillrateTaxOpt" id="OvbillrateTaxOpt"  type="radio" value="N" <?php if($ratesDefaultVal['OverTime'][1] == 'N'){echo ' checked="checked"';}else{ echo $bdisable;}?>>
													<label class="form-check-label" for="OvbillrateTaxOpt">Non-Taxable</label>
												</div>
											</span>
										</td>
									</tr>
									<tr id="db_time_payrate" style="display:">
										<?php
										$arr = $objMRT->getRateTypeById(3);
										if ($arr['peditable'] == 'N') {
											$pdisabled_user_input_field = ' disabled_user_input_field';
											$pdisable = ' disabled="disabled"';
										} else {
											$pdisabled_user_input_field = '';
											$pdisable = '';
										}
										if ($arr['beditable'] == 'N') {
											$bdisabled_user_input_field = ' disabled_user_input_field';
											$bdisable = ' disabled="disabled"';
										} else {
											$bdisabled_user_input_field = '';
											$bdisable = '';
										}
										?>
										<td class="summaryform-bold-title"><?php echo $arr['name']; ?> Pay Rate<?php if(TRICOM_REPORTS=='Y'){echo $mandatory_madison;}?></td>
										<td>
											<span id="leftflt"><input type="text" class="summaryform-formelement<?php echo $pdisabled_user_input_field;?>" maxlength="9" size="10" value="" id="db_time_pay" name="db_time_pay" <?php echo $pdisable; ?>><input type="hidden" id="db_time_pay_hidden" value="<?php echo $arr['pvalue']; ?>"></span>
											<span id="leftflt">&nbsp;&nbsp;
								<!--UOM: get dynamic rate types-->
												<select class="summaryform-formelement<?php echo $pdisabled_user_input_field;?>"  onclick="getPreviousRate(this);"  onChange="change_Period('dbtimepayrate');" id="db_time_payper" name="db_time_payper" <?php echo $pdisable; ?>>
													<?php echo $timeOptions; ?>
												</select>
											</span>
											<span id="leftflt">&nbsp;&nbsp;
												<select class="summaryform-formelement<?php echo $pdisabled_user_input_field;?>" id="db_time_paycur" onChange="change_PeriodNew('dbtimepayratecur');" name="db_time_paycur" <?php echo $pdisable; ?>>
													<?php echo $strCurry; ?>
												</select>
											</span>
											<span id='dt_pay_Bill_NonBill'>
												<div class="form-check form-check-inline">
													<input class="form-check-input" name="DbpayrateBillOpt" id="DbpayrateBillOpt"  type="radio" value="Y" <?php if($ratesDefaultVal['DoubleTime'][0] == 'Y' || $ratesDefaultVal['DoubleTime'][0] == ''){echo ' checked="checked"';}else{ echo $pdisable;}?>>
													<label class="form-check-label" for="DbpayrateBillOpt">Billable</label>
												</div>
												<div class="form-check form-check-inline">
													<input class="form-check-input" name="DbpayrateBillOpt" id="DbpayrateBillOpt"  type="radio" value="N" <?php if($ratesDefaultVal['DoubleTime'][0] == 'N'){echo ' checked="checked"';}else{ echo $pdisable;}?>>
													<label class="form-check-label" for="DbpayrateBillOpt">Non-Billable</label>
												</div>
											</span>
										</td>
									</tr>
									<tr id="db_time_billrate" style="display:">
										<td class="summaryform-bold-title"><?php echo $arr['name']; ?> Bill Rate<?php if(TRICOM_REPORTS=='Y'){echo $mandatory_madison;}?></td>
										<td>
											<span id="leftflt"><input type="text" class="summaryform-formelement<?php echo $bdisabled_user_input_field;?>" maxlength="9" size="10" value="" id="db_time_bill" name="db_time_bill" <?php echo $bdisable; ?>><input type="hidden" id="db_time_bill_hidden" value="<?php echo $arr['bvalue']; ?>"></span>
											<span id="leftflt">&nbsp;&nbsp;
												<select class="summaryform-formelement<?php echo $bdisabled_user_input_field;?>" onclick="getPreviousRate(this);"  onChange="change_Period('dbtimebillrate');" id="db_time_billper" name="db_time_billper" <?php echo $bdisable; ?>><?php echo $timeOptions; ?></select>
											</span>
											<span id="leftflt">&nbsp;&nbsp;
												<select class="summaryform-formelement<?php echo $bdisabled_user_input_field;?>" id="db_time_billcurr" onChange="change_PeriodNew('dbtimebillratecur');" name="db_time_billcurr" <?php echo $bdisable; ?>><?php echo $strCurry; ?></select>
											</span>
											<span id='db_bill_Tax_NonTax'>
												<div class="form-check form-check-inline">
													<input class="form-check-input" name="DbbillrateTaxOpt" id="DbbillrateTaxOpt"  type="radio" value="Y" <?php if($ratesDefaultVal['DoubleTime'][1] == 'Y' || $ratesDefaultVal['DoubleTime'][1] == ''){echo ' checked="checked"';}else{ echo $bdisable;}?>>
													<label class="form-check-label" for="DbbillrateTaxOpt">Taxable</label>
												</div>
												<div class="form-check form-check-inline">
													<input class="form-check-input" name="DbbillrateTaxOpt" id="DbbillrateTaxOpt"  type="radio" value="N" <?php if($ratesDefaultVal['DoubleTime'][1] == 'N'){echo ' checked="checked"';}else{ echo $bdisable;}?>>
													<label class="form-check-label" for="DbbillrateTaxOpt">Non-Taxable</label>
												</div>
											</span>
										</td>
									</tr>
									<tr>
									  <td colspan="2" style="padding:0px">
										  <div id="multipleRatesTab"></div>
										  <input type="hidden" id="selectedcustomratetypeids" value="">
									  </td>
									</tr>
									<tr id="custom_rate_type_tr" style="display:none">
										<td colspan="2">
											<a class="crm-select-link" href="javascript:addRateTypes();">Select Custom Rate</a>
										</td>
									</tr>
									  <input type="hidden" name="otrate">
									  <input type="hidden" name="otper">
									  <input type="hidden" name="otcurrency">
									<tr class="jocomp-back DisplayNone" id="crm-joborder-billinginfoDiv1" name="crm-joborder-billinginfoDiv1">
										<td class="summaryform-bold-title" valign="top"><?php echo (!empty($userFieldsAlias['Salary']))?$userFieldsAlias['Salary']:'Salary';echo getRequiredStar('Salary', $userFieldsArr);?></td>
										<td>
											<!-- salary direct -->
											<span id="salary_direct" style="display:none">
												<div class="row align-items-center mb-3">
													<div class="col-auto">
														<input class="form-check-input" name="rangetype" id="rangetype1" type="radio"  value="amount" checked>
														<label class="form-check-label">Amount</label>
													</div>
													<div class="col-auto">
														<input name="amount_val" type="text" id="amount_val" value="" size=10 maxlength="9" class="form-control w-auto" onkeypress="return blockNonNumbers(this, event, true, false);">
													</div>
													<div class="col-auto">
														<!--UOM: get dynamic rate types-->
														<select name="amountper" id="amountper" onClick="getPreviousRate(this);" onChange="change_Period('amountper');" class="form-control w-auto">
															<?php $salAmountRates = getNewRateTypes('YEAR'); echo $salAmountRates ; ?>       
														</select>
													</div>
													<div class="col-auto">
														<select name="amountcur" id="amountcur" class="form-control w-auto">
															<?php
															displayCurrency('');
															?>
														</select>
													</div>
												</div>
												<div class="row align-items-center mb-3">
													<div class="col-auto">
														<input class="form-check-input" name="rangetype" type="radio"  value="range" id="rangetype2">
														<label class="form-check-label">Range</label>
													</div>
													<div class="col-auto">
														<input class="form-control w-auto" name="range_max" type="text" id="range_max" value="" size=10 maxlength="9">
														<span class="summaryform-formelement">to</span>
														<input class="form-control w-auto" name="range_min" type="text" id="range_min" value="" size=10 maxlength="9">
													</div>
													<div class="col-auto">
														<!--UOM: get dynamic rate types-->
														<select name="rangeper" id="rangeper" onClick="getPreviousRate(this);" onChange="change_Period('rangeper');" class="form-select w-auto">
															<?php  $salRangeRates = getNewRateTypes('YEAR');
															echo $salRangeRates ; ?>
														</select>
													</div>
													<div class="col-auto">
														<select name="rangecur" id="rangecur" class="form-select w-auto">
															<?php
															displayCurrency('');
															?>
														</select>
													</div>
												</div>
												<div class="row align-items-center mb-3">
													<div class="col-auto">
														<div class="form-check">
															<input class="form-check-input" name="rangetype" id="rangetype3" type="radio" value="open">
															<label class="form-check-label" for="rangetype3">Open</label>
														</div>
													</div>
													<div class="col-auto">
														<input class="form-control w-auto" name="open_salary" type="text" id="open_salary" value="" size=38>
													</div>
													<div class="col-auto">
														<!-- Salary check-->
														<span id="salary_others" style="display:">
															<input name="salary" type="text" id="salary" value="" size=10 maxlength="9" class="form-control w-auto" onkeypress="return blockNonNumbers(this, event, true, false);">
														</span>
													</div>
													<div class="col-auto">
														<!--UOM: get dynamic rate types-->
														<select name="salaryper" id="salaryper" onClick="getPreviousRate(this);" onChange="change_Period('salary');"  class="form-select w-auto">
															<?php  $salPerRates = getNewRateTypes('YEAR'); echo $salPerRates ; ?>
														</select>
													</div>
													<div class="col-auto">
														<select name="salarycur" id="salarycur" class="form-select w-auto">
															<?php
															displayCurrency('');
															?>
														</select>
													</div>
												</div>
											</span>
										</td>
									</tr>
								<tr id="overtime_rate_pay_direct" style="display:none;">
									<td class="summaryform-bold-title">OverTime Pay Rate<?php if(TRICOM_REPORTS=='Y'){echo $mandatory_madison;}?></td>
									<td>
										<span id="leftflt">
											<input type="text" class="summaryform-formelement" maxlength="9" size="10" value="" id="otrate_pay_direct" name="otrate_pay_direct">
										</span>
										<span id="leftflt">&nbsp;&nbsp;
										<!--UOM: get dynamic rate types-->
											<select class="summaryform-formelement" id="otper_pay_direct" onClick="getPreviousRate(this);" onChange="change_Period('otpaydirect');" name="otper_pay_direct">
												<?php echo $timeOptions;?>
											</select>
										</span>
										<span id="leftflt">&nbsp;&nbsp;
											<select class="summaryform-formelement" id="otcurrency_pay_direct" name="otcurrency_pay_direct">
												<?php echo $strCurry;?>
											</select>
										</span>
							<span id='ot_pay_Bill_NonBill'>
								<div class="form-check form-check-inline">
									<input class="form-check-input" name="OvpayrateBillOpt_direct" id="OvpayrateBillOpt_direct"  type="radio" value="Y" <?php if($ratesDefaultVal['OverTime'][0] == 'Y' || $ratesDefaultVal['OverTime'][0] == ''){echo ' checked="checked"';}?>>
									<label class="form-check-label">Billable</label>
								</div>

								<div class="form-check form-check-inline">
									<input class="form-check-input" name="OvpayrateBillOpt_direct" id="OvpayrateBillOpt_direct"  type="radio" value="N" <?php if($ratesDefaultVal['OverTime'][0] == 'N'){echo ' checked="checked"';}?>>
									<label class="form-check-label">Non-Billable</label>
								</div>
							</span>
									</td>
								</tr>
								<tr id="db_time_payrate_direct" style="display:none;">
									<td class="summaryform-bold-title">DoubleTime Pay Rate<?php if(TRICOM_REPORTS=='Y'){echo $mandatory_madison;}?></td>
									<td>
										<div class="row gx-2 align-items-center">
											<div class="col-auto">
												<input name="db_time_pay_direct" type="text" id="db_time_pay_direct" value="" size=10 maxlength="9" class="form-control">
											</div>
											<div class="col-auto">
												<!--UOM: get dynamic rate types-->
												<select name="db_time_payper_direct" onClick="getPreviousRate(this);" onChange="change_Period('dbpaydirect');" id="db_time_payper_direct" class="form-select">
													<?php echo $timeOptions;?>
												</select>
											</div>
											<div class="col-auto">
												<select name="db_time_paycur_direct" id="db_time_paycur_direct" class="form-select"><?php echo $strCurry;?></select>
											</div>
											<div class="col-auto">
												<div class="form-check form-check-inline">
													<input class="form-check-input" name="DbpayrateBillOpt_direct" id="DbpayrateBillOpt_direct"  type="radio" value="Y" <?php if($ratesDefaultVal['DoubleTime'][0] == 'Y' || $ratesDefaultVal['DoubleTime'][0] == ''){echo ' checked="checked"';}?>>
													<label class="form-check-label">Billable</label>
												</div>
												<div class="form-check form-check-inline">
													<input class="form-check-input" name="DbpayrateBillOpt_direct" id="DbpayrateBillOpt_direct"  type="radio" value="N" <?php if($ratesDefaultVal['DoubleTime'][0] == 'N'){echo ' checked="checked"';}?>>
													<label class="form-check-label">Non-Billable</label>
												</div>
											</div>
										</div>
									</td>
								</tr>
								<tr id="jobloc_deduct" style="display:"> <td class="summaryform-bold-title" nowrap>&nbsp;</td>
									<td>
										<span id="leftflt" class="summaryform-bold-title"><input type="checkbox" name="use_jobloc_deduct" value='Y'>&nbsp;Use Job Location for Applicable Taxes</span>
									</td>
								</tr>
									<!-- Added by vijaya to add per diem fields- 11/11/2008 //-->
								<tr id="billable_block" style="display:block">
									<td align="left" colspan="3">
										<table class="table crmsummary-jocomp-table billable_blockNew">
											<tr>
												<td width="15%" align="left" valign="top" class="summaryform-bold-title summaryform_margin_left">&nbsp;</td>
												<td width="7%" align="left" valign="top" class="summaryform-bold-title">Lodging</td>
												<td width="6%" align="left" valign="top" class="summaryform-bold-title">M&amp;IE</td>
												<td width="5%" align="left" valign="top" class="summaryform-bold-title">Total</td>
												<td width="22%" align="left" valign="top" class="summaryform-bold-title">&nbsp;</td>
												<td width="50%" align="left" class="summaryform-bold-title" valign="top">&nbsp;</td>
											</tr>
											<tr>
												<td  width="15%" align="left" class="summaryform-bold-title summaryform_margin_left">Per Diem</td>
												<td align="left"><input type="text" name="txt_lodging" id="txt_lodging" value="<?php echo $JobDet[31]; ?>" size="5" class="summaryform-formelement" onBlur="javascript:calculatePerDiem();"/></td>
												<td align="left"><input type="text" name="txt_mie" id="txt_mie" value="<?php echo $JobDet[32]; ?>" size="5" class="summaryform-formelement" onBlur="javascript:calculatePerDiem();" /></td>
												<td align="left"><input type="text" name="txt_total" id="txt_total" value="<?php echo $JobDet[33]; ?>" size="10" class="summaryform-formelement" onBlur="javascript:calculatePerDiem();"/></td>
												<td align="left">
													<!--UOM: get dynamic rate types-->
													<select name="sel_perdiem" id="sel_perdiem" onClick="getPreviousRate(this);" onChange="change_Period('selperdiem');" class="summaryform-formelement">

													<?php echo  getNewRateTypes($JobDet[34]); ?>
													</select>
													<select name="sel_perdiem2" id="sel_perdiem2" class="summaryform-formelement">
														<?php
														displayCurrency($JobDet[35]);
														?>
													</select>
												</td>
												<td align="left"  valign="middle" >
													<!-- Modified following lines by swetha, removed summaryform-formelement class in input field to align properly --->
													<div class="form-check form-check-inline">
														<input class="form-check-input" type="radio" name="radio_taxabletype" id="radio_taxabletype" value="Y" <?php echo getChk('Y', $JobDet[37]); ?>>
														<label class="form-check-label">Taxable</label>
													</div>
													<div class="form-check form-check-inline">
														<input class="form-check-input" type="radio" name="radio_taxabletype" id="radio_taxabletype" value="N" <?php echo getChk('N', $JobDet[37]); ?>>
														<label class="form-check-label">Non-Taxable</label>
													</div>
												</td>
											</tr>
											<tr>
												<td width="15%" align="left" valign="top" class="summaryform-bold-title summaryform_margin_left">&nbsp;</td>
												<td align="left" valign="middle" colspan="5">
													<div class="d-flex align-items-center gap-3">
														<div class="form-check form-check-inline">
															<input class="form-check-input" type="radio" name="radio_billabletype" id="radio_billabletype" value="Y"  <?php echo getChk('Y', $JobDet[36]); ?> onClick="javascript:showBillDiv(this,'txt_total');" />
															<label class="form-check-label">Billable</label>
														</div>
														<?php
														$style = ($JobDet[36] == "Y") ? 'style="float:left;"' : 'style="float:left; display: none;"';
														?>
														<div id="bill_Div" <?php echo $style; ?>>
															<input type="text" name="diem_billrate" id="diem_billrate" size="8" maxlength="9" onBlur="javascript:isNumbervalidation(this,'Billrate');" value="<?php echo $JobDet[38]; ?>" class="form-control" />
														</div>
														<div class="form-check form-check-inline">
															<input class="form-check-input" type="radio" name="radio_billabletype" id="radio_billabletype" value="N" <?php echo getChk('N', $JobDet[36]); ?> onClick="javascript:showBillDiv(this);" />
															<label class="form-check-label">Non-Billable</label>
														</div>
													</div>
												</td>
											</tr>
										</table>
									</td>
								</tr>
									<!-- End of per diem //-->
								<tr>
									<td class="summaryform-bold-title"><?php echo (!empty($userFieldsAlias['Placement Fee']))?$userFieldsAlias['Placement Fee']:'Placement Fee';echo getRequiredStar('Placement Fee', $userFieldsArr, 'pfee');?></td>
									<td>
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
									<th colspan="2">
										Commission / Splits
									</th>
								</tr>
								<tr>
									<td colspan="2">
										<table id="tblRolesId" class="table align-middle">
											<thead>
												<tr>
													<td width="174" class="summaryform-nonboldsub-title summaryrow">Add Person:</td>
													<td class="colspannext" colspan="5">
														<div class="d-flex gap-3 align-items-center">
															<select id="addemp" name="addemp" class="summaryform-formelement setcommrolesize" onChange="addCommission('newrow')">
																<option selected  value="">--select employee--</option>
																<?php
																while ($row_users = mysql_fetch_row($res_users))
																	print '<option ' . compose_sel($elements[13], $row_users[0]) . ' value="' . 'emp' . $row_users[0] . '|' . $row_users[1] . '">' . stripslashes($row_users[1]) . '</option>';
																?>
															</select>
															<nav class="nav gap-3">
																<a class="link-primary" href="javascript:parent_popup('comcontact')">Select Contact <i class='fa-solid fa-search'></i></a>
																<a class="link-primary" href="javascript:newConScreen('contact','comcontact')">New Contact</a>
															</nav>
														</div>
													</td>
												</tr>
											</thead>
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
									<td><span id="leftflt"><input class="summaryform-formelement" type="text" size=20 name="payrollid" maxlength="20"></span></td>
								</tr>
									<tr>
										<td class="summaryform-bold-title" nowrap="nowrap">Workers Comp Code<?php if(TRICOM_REPORTS=='Y'){echo $mandatory_madison;}?></td>
										<td>
											<span id="leftflt">
											<select class="summaryform-formelement" name="workcode" id="workcode">
											<option value=""> -- Select (Code-Title-State) -- </option>
											<?php
													getDispWCOptions($JobDet[18]); // Displaying workers compensation code
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
										<td width="13%" class="summaryform-bold-title">Payment Terms</td>
										<td>

										<?php
										 $BillPay_Sql = "SELECT billpay_termsid, billpay_code FROM bill_pay_terms WHERE billpay_status = 'active' AND billpay_type = 'PT' ORDER BY billpay_code";
										 $BillPay_Res = mysql_query($BillPay_Sql,$db);
										?>
										<select name="pterms" id="pterms">
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
								   <!--  <tr  id="crm-joborder-billinginfotimesheet" name="crm-joborder-billinginfotimesheet">
										<td class="summaryform-bold-title">Timesheet Approval</td>
										<td>
											 Modified following lines by swetha, removed summaryform-formelement class in input field to align properly 
										<span id="leftflt" class="summaryform-nonboldsub-title"><input class="" type="radio" name="tapproval" id="tapproval" value="Manual" checked="checked">Manual</span>
										<span id="leftflt" class="summaryform-nonboldsub-title">&nbsp;&nbsp;&nbsp;<input class="" type="radio" name="tapproval" id="tapproval" value="Online">Online</span>

										</td>
									</tr>-->
									<span name="crm-joborder-billinginfotimesheet" id="crm-joborder-billinginfotimesheet" style="visibility: hidden;">
										<span id="leftflt" class="summaryform-nonboldsub-title"><input class="summaryform-formelement" type="radio" name="tapproval" id="tapproval" value="Manual" checked="checked">Manual</span>
										<span id="leftflt" class="summaryform-nonboldsub-title">&nbsp;&nbsp;&nbsp;<input class="" type="radio" name="tapproval" id="tapproval" value="Online">Online</span>
						   </span>
									<tr>
										<td class="summaryform-bold-title">PO Number</td>
										<td>
										<span id="leftflt"><input class="summaryform-formelement" type="text" name="po_num" size=20 value="<? echo html_tls_specialchars(stripslashes($JobDet[39])); ?>" maxlength="255"></span></td>
									</tr>
									<tr>
										<td class="summaryform-bold-title">Department</td>
										 <td>
											 <span id="leftflt">
												<input class="summaryform-formelement" type="text" name="joborder_dept" size=20 value="<?php echo html_tls_specialchars(stripslashes($JobDet[22]),ENT_QUOTES);?>" maxlength="255">
										   </span>
										 </td>
									</tr>
									<tr>
									<?php
											$cus_username="select username from staffacc_cinfo where sno='".$billcompany."'";
											$cus_username_res=mysql_query($cus_username,$db);
											$cust_username=mysql_fetch_row($cus_username_res);
											$custusername=$cust_username[0];

											?>
										<input type="hidden" name="billcompany_sno" id="billcompany_sno" value="<?php echo $billcompany; ?>">
										<input type="hidden" name="billcompany_username" id="billcompany_username" value="<?php echo $custusername;?>">
										<td class="summaryform-bold-title"><?php echo (!empty($userFieldsAlias['Billing Address']))?$userFieldsAlias['Billing Address']:'Billing Address';echo getRequiredStar('Billing Address', $userFieldsArr);?></td>
										<td><span id="billdisp_comp">&nbsp;<input type="hidden" name="bill_loc" id="bill_loc"><a class="crm-select-link" href="javascript:bill_jrt_comp('bill')">select company</a>&nbsp;</span></span>&nbsp;<span id="billcomp_chgid">&nbsp;</span>
											<?php
											if ($billcontact > 0 || $bill_loc > 0) {
												?>
												<script>getCRMLocations('<?php echo $billcompany; ?>','<?php echo $billcontact; ?>','<?php echo $bill_loc; ?>','bill');</script>
												<?php
											}
											?>
										</td>
									</tr>
									<tr>
										<td class="summaryform-bold-title"><?php echo (!empty($userFieldsAlias['Billing Contact']))?$userFieldsAlias['Billing Contact']:'Billing Contact';echo getRequiredStar('Billing Contact', $userFieldsArr);?></td>
										<td>
											<?php
											if ($billcontact == 0) {
												?>
												<span id="billdisp"><a class="crm-select-link" href="javascript:bill_jrt_cont('bill')" id="disp">select contact</a></span>
												&nbsp;<span id="billchgid"><a href="javascript:bill_jrt_cont('bill')"><i class='fa fa-search fa-lg'></i></a><span class="summaryform-formelement">&nbsp;|&nbsp;</span><a class="crm-select-link" href="javascript:donew_add('bill')">new&nbsp;contact</a></span>
												<?php
											} else {
												?>
												<span id="billdisp">
												<?php
												if ($chkBILLContAccess == "No") {
													echo "<span class=summaryform-formelement>" . $billcont . "</span>";
												} else {
													?>
														<a class="crm-select-link" href="javascript:contact_func('<?php echo $billcontact; ?>','<?php echo $billcont_stat; ?>','bill')"><?php echo $billcont; ?></a>
														<?php
													}
													?>
												</span>
												&nbsp;<span id="billchgid"><span class=summaryform-formelement>(</span> <a class=crm-select-link href=javascript:bill_jrt_cont('bill')>change </a>&nbsp;<a href=javascript:bill_jrt_cont('bill')><i class='fa fa-search fa-lg'></i></a><span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class=crm-select-link href=javascript:donew_add('bill')>new</a><span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class="crm-select-link" href="javascript:removeContact('bill')">remove&nbsp;</a><span class=summaryform-formelement>&nbsp;)&nbsp;</span></span>
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
											$BillPay_Res = mysql_query($BillPay_Sql, $db);
											?>
											<select name="term_billing" id="term_billing">
												<option value=""> -- Select -- </option>
												<?php
												while ($BillPay_Data = mysql_fetch_row($BillPay_Res)) {
													?>
													<option value="<?=stripslashes($BillPay_Data[0]);?>" title="<?=html_tls_specialchars(stripslashes($BillPay_Data[1]),ENT_QUOTES);?>"><?=stripslashes($BillPay_Data[1]);?></option>
													<?php
												}
												?>
											</select>
											<?php
											if (ENABLE_MANAGE_LINKS == 'Y')
												echo '&nbsp;&nbsp;&nbsp;<a href="javascript:doManageBillPayTerms(\'Billing\',\'term_billing\')" class="edit-list">Manage</a>';
											?>
										</td>
									</tr>
									<tr>
										<td class="summaryform-bold-title" style="border-bottom:0px solid #DDDDDD" valign="top">Service Terms</td>
										<td style="border-bottom:0px solid #DDDDDD"><textarea name="serv_term" rows="5" cols="64"><?php echo stripslashes($JobDet[20]);?></textarea></td>
									</tr>
								</table>
							</div>
						</div>
                <!--Billing information tabbed pane ends -->
		<div class="form-back accordion-item">
		<table width="100%" border="0" class="crmsummary-edit-tablemin" id="crm-joborder-scheduleDiv-Table">
                    <tr>
                        <td width="140" class="crmsummary-content-title">
                            <div id="crm-joborder-scheduleDiv-plus1"><a style='text-decoration: none;' href="javascript:classToggle('schedule','plus')"><span class="crmsummary-content-title">Schedule</span></a></div>
                            <div id="crm-joborder-scheduleDiv-minus1" class="DisplayNone" style="width:auto;"><a  style='text-decoration: none;' href="javascript:classToggle('schedule','minus')"><span class="crmsummary-content-title">Schedule</span></a></div>
                        </td>
                        <td>
                            <span id="rightflt" <?php echo $rightflt; ?>>
                                <span class="summaryform-bold-close-title" id="crm-joborder-scheduleDiv-close" style="display:none;width:auto;"><a style='text-decoration: none;' onClick="classToggle('schedule','minus')"  href="#crm-joborder-scheduleDiv-plus">close</a></span>
                                <span class="summaryform-bold-close-title" id="crm-joborder-scheduleDiv-open" style="width:auto;"><a style='text-decoration: none;' onClick="classToggle('schedule','plus')"  href="#crm-joborder-scheduleDiv-minus"> open</a></span>
                                <div class="form-opcl-btnleftside"><div align="left"></div></div>
                                <div id="crm-joborder-scheduleDiv-plus"><span onClick="classToggle('schedule','plus')" class="form-op-txtlnk crmsummary-process-mousepointer" ><b>+</b></span></div>
                                <div id="crm-joborder-scheduleDiv-minus"  class="DisplayNone"><span onClick="classToggle('schedule','minus')" class="form-cl-txtlnk crmsummary-process-mousepointer"><b>-</b></span></div>
                                <div class="form-opcl-btnrightside"><div align="left"></div></div>
                            </span>
                        </td>
                    </tr>
		</table>
		</div>
		<div class="jocomp-back DisplayNone" id="crm-joborder-scheduleDiv" name="crm-joborder-scheduleDiv"  <?php echo $style_table;?>>
							<table class="table table-borderless align-middle crmsummary-jocomp-table">
								<tr>
									<td width="150px" class="summaryform-bold-title"><?php echo (!empty($userFieldsAlias['Start Date'])) ? $userFieldsAlias['Start Date'] : 'Start Date';echo getRequiredStar('Start Date', $userFieldsArr); ?></td>
									<td colspan="3"><select name="smonth" id="smonth" class="summaryform-formelement">
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
							</select><span id="josdatecal"><input type="hidden" name="josdatenew" id="josdatenew" value="" /><script language='JavaScript'> new tcal ({'formname':'conreg','controlname':'josdatenew'});</script></span></td>
								</tr>
								<!-- Code for Due Date //-->
								<tr>
									<td class="summaryform-bold-title">Due&nbsp;Date</td>
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
							<span id="joduedatecal"><input type="hidden" name="joduedatenew" id="joduedatenew" value="" /><script language='JavaScript'> new tcal ({'formname':'conreg','controlname':'joduedatenew'});</script></span></td>
								</tr>
								<!-- End of Due Date //-->
								<tr class="jocomp-back DisplayNone" id="crm-joborder-scheduleDiv1" name="crm-joborder-scheduleDiv1">
									<td  class="summaryform-bold-title"><?php echo (!empty($userFieldsAlias['Expected End Date'])) ? $userFieldsAlias['Expected End Date'] : 'Expected End Date';echo getRequiredStar('Expected End Date', $userFieldsArr); ?></td>
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
											<?php echo $expectedEndYear;?>
							</select><span id="joedatecal"><input type="hidden" name="joedatenew" id="joedatenew" value="" /><script language='JavaScript'> new tcal ({'formname':'conreg','controlname':'joedatenew'});</script></span></td>
								</tr>
								<tr id="shiftname_time" <?php if(SHIFT_SCHEDULING_ENABLED == 'Y') { echo ' style="display:none" '; } ?> class="shiftnameCls">
									<td  class="summaryform-bold-title">Shift Name/ Time</td>
									<td  colspan="3">
										<select name="new_shift_name" id="new_shift_name" class="summaryform-formelement" onChange="" style="width:128px;">
										<option value="0|0">Select Shift</option>
										<?php 
											$selShiftsQry = "SELECT sno,shiftname,shiftcolor FROM shift_setup WHERE shiftstatus='active' ORDER BY shiftname ASC";
											$selShiftsRes = mysql_query($selShiftsQry,$db);
										if(mysql_num_rows($selShiftsRes)>0)
										{	
											while($selShiftsRow = mysql_fetch_array($selShiftsRes))
											{
												$selected = "";
												if($selShiftsRow['sno'] == $JobDet[50])
												{
													$selected = "selected=selected";
												}
											?>
											<option value="<?php echo $selShiftsRow['sno'].'|'.$selShiftsRow['shiftname']; ?>" <?php echo $selected;?> title="<?php echo $selShiftsRow['shiftcolor'];?>"><?php echo $selShiftsRow['shiftname'];?></option>
										 <?php 
											}
										}
										?>
										</select>
										<select name="shift_start_time" id="shift_start_time" class="summaryform-formelement" onChange="">
										<option value="0">Start Time</option>
										<?php echo display_Shift_Times($JobDet[51]); ?>
										</select>
										<select name="shift_end_time" id="shift_end_time" class="summaryform-formelement" onChange="">
										<option value="0">End Time</option>
										<?php echo display_Shift_Times($JobDet[52]); ?>
										</select>
									  </td>                      
								</tr>
								<tr id="shiftname_note" <?php if(SHIFT_SCHEDULING_ENABLED == 'Y') { echo ' style="display:none" '; } ?>>
									<td colspan="2">
										<span class="billInfoNoteStyle">Note : Shift details are auto populated in placement & assignment(s).</span>
									</td>
								</tr>
								<tr id="sch_hours">
									<td  class="summaryform-bold-title">Hours</td>
									<td  colspan="3"><input type=hidden name="FullPartTimeRecId" id="FullPartTimeRecId" value="">
										<select name="Hrstype" id="Hrstype" class="summaryform-formelement" onChange="checkCustom(this.value)">
											<option  value="fulltime">Full Time&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
											<option  value="parttime">Part Time</option>
										</select>
									</td>                         
								</tr>

				<!-- OLD SHIFT SCHEDULING START-->
				<tr id='crm-joborder-hourscustom' style='display:none'>
				   <td colspan="4" style="padding:0px">
					   <table id="crm-joborder-Tablehours" class="table">
							<tr>
							  <td width="150px"></td>
							  <td width="9%"></td>
							  <td width="12%"></td>
							  <td width="9%"></td>
							  <td width="2%"></td>
							  <td width="9%"><span id="crm-joborder-custom_deleteall"><a href="#crm-joborder-custom_deleteall" class="edit-list" onClick="javascript:DelselectSchAll()">delete selected</a></td>
							  <td width="2%"><input type="checkbox" name="customcheckall" id="customcheckall" value="Y" class="summaryform-formelement" onClick="selectSchAll()"></td>
							  <td colspan="2" >&nbsp;</td>

							</tr>
					  <tr id="defRowday0" style="display:none;">
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
						  <td ><input type="checkbox" name="daycheck0" id="daycheck0" value="Y" class="summaryform-formelement" onClick="childSchAll();"></td>
					  <td colspan="2" class="summaryform-bold-title"></td>
								  </tr>
					  <tbody id="JoborderAddTable-Sunday"></tbody>
					<tr id="defRowday1" style="display:none;">
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
				<tr id="defRowday2" style="display:none;">
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
				<tr id="defRowday3" style="display:none;">
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
				<tr  id="defRowday4" style="display:none;">
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
				<tr  id="defRowday5" style="display:none;">
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
				<tr  id="defRowday6" style="display:none;">
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
				  <td></td>
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
							<select name='custaddrowfr_hour' id='custaddrowfr_hour' class="summaryform-formelement ">
							<?php echo $DispTimes;?>
							</select></td>
				  <td class="crmsummary-jocompmin summaryform-bold-title">To</td>
				  <td class="crmsummary-jocompmin"><select name='custaddrowto_hour' id='custaddrowto_hour' class="summaryform-formelement">
					<?php echo $DispTimes;?>
				  </select></td>
				  <td colspan="2" class="crmsummary-jocompmin" nowrap="nowrap"><a  href="#crm-joborder-scheduleDiv-minus" onClick="javascript:ScheduleRowCall()" class="crm-select-link" >Add Row</a></td>
				  <td class="summaryform-bold-title"></td>
				</tr>
					</table></td>
				</tr> 
				<!-- OLD SHIFT SCHEDULING END-->
				<!-- NEW SHIFT SCHEDULING START-->
				<tr id="sch_calendar" <?php if(SHIFT_SCHEDULING_ENABLED == 'N') { echo ' style="display:none" '; } ?>>
					<td class="summaryform-bold-title" nowrap width="25%">
						<?php 
							echo $objSchSchedules->displayShiftScheduleWithAddLink('jo_shiftsch', 'No','joborder',$shift_type); 
							$smshiftLegendSnos = $objSchSchedules->findShiftsAssoc($posid,'jobsummary','');
						?>
					</td>
					<?php
						$objCandidateSchedule = new CandidateSchedule();													
						$flag = $objCandidateSchedule->isScheduleExists($posid,'jobsummary');
						if($copyjoborder != 'yes')
						if($flag)
						{
						?>
						<td class="summaryform-bold-title" colspan="1" align="right" style="text-align:right !important">
							<a style="text-decoration:none;cursor:pointer; padding-top:3px;" id="view_past_schedules">
								<span class="linkrow" onclick="javascript:displayTimeFrameWindow('<?php echo $posid; ?>','viewall','jobsummary')">View All/History</span>
							</a>
						</td>
						<?php 
						}
					?>	
				</tr>

				<tr>
					<td colspan="3" id="joTimeFrameData">
					<input type="hidden" name="sm_module" id="sm_module" value="joborder" />
					<input type="hidden" name="sm_shiftmode" id="sm_shiftmode" value="EditIndShifts" />
					<input type="hidden" name="shift_schedule_module" id="shift_schedule_module" value="jobsummary" >
					<input type="hidden" name="sm_jo_shift_sno" id="sm_jo_shift_sno" value="<?php echo $smshiftLegendSnos;?>" >

					<?php
					//GETTING SM AVAILABILITY TIMNEFRAME DETAILS

					$shift_schedule_module = "jobsummary";
					// displaying the shift from job order created date.
					$previousDate = date("Y-m-d",strtotime("-2 months",strtotime($JobDet[49])));
					?>
					<input type="hidden" name="sm_jo_start_date" id="sm_jo_start_date" value="<?=$previousDate?>" />
					<?php		
					//$previousDate = date("Y-m-d",strtotime("-2 months",strtotime(date("Y-m-d"))));
					$schDatesArrayStr = $objCandidateSchedule->getTimeFrameDetails($posid,$shift_schedule_module,$previousDate,'',$copyjoborder); //return schedule shifts seperated by | symbol
					$schDatesArray = array();
					unset($_SESSION['editShiftTotalArrayValues'.$candrn]);
					$_SESSION['editShiftTotalArrayValues'.$candrn] = array();
					if($schDatesArrayStr != "")
					{
						$schDatesArray = explode("|",$schDatesArrayStr);
					}
					$getDisplayDatesCount = count($schDatesArray);
					$displayTimeFrameGrid = 'Y';
					$defaultColorCode = '#456BDB';
					$busyStatusColor = '#04B431';
					include($app_inc_path."shift_schedule/timeFrameView.php");
					?>
					<script>
					if($("#getcalsel_dates").val() == "")
					{
						$("#dateSelGridDiv").hide();
						$("#shiftschAddEdit").hide();
						$("#jo_shiftsch").prop("checked",false);
					}else{
						//shift_type
						$('#shift_type').addClass("disabledDiv");
					}
					loadAllHiddenShiftdata('<?php echo $posid;?>','<?php echo $i;?>','N','<?php echo $candrn;?>','<?php echo $dateDisplayArrayCount;?>');
					</script>
					</td>
				</tr>
				<tr id="sch_calendar_per" <?php if(SHIFT_SCHEDULING_ENABLED == 'N') { echo ' style="display:none" '; } ?>>
					<td class="summaryform-bold-title" colspan="3" style="padding:0px;">
						<input type="hidden" name="delperdiem_shiftid" id="delperdiem_shiftid" value="" >
						<input type="hidden" name="active_perdiem_shiftid" id="active_perdiem_shiftid" value="" >
						<div id="perdiemShiftSchedule" class="perdiShitSchBg"  style="display:none;">
							<?php 
								if ($shift_type == "perdiem") {
									$paginationFrom = "joborder";
									$pagiType = "edit";
									$doPagiFromId=1;
									$doPagiFor = "Next";
									$pagingFrom ="jobOrderPage";
									$copyjoborder = $copyjoborder;
									$posid = $posid; // just for clarity assigning same variable
									unset($_SESSION['editJoPerdiemShiftSch'.$candrn]);
									unset($_SESSION['editJoPerdiemShiftPagination'.$candrn]);
									unset($_SESSION['modifiedJoPerdiemShiftSch'.$candrn]);
									unset($_SESSION['editJoPerdiemShiftNameColor'.$candrn]);
									// Preparing the Session for 0,35 days default to dispaly
									include_once($app_inc_path.'perdiem_shift_sch/View/paginationCRMPerdiemShifts.php');
								}
							?>
						</div>
					</td>	
				</tr>	
				<?php if ($shift_type == "perdiem") { ?>		
					<script>				
						$("#jo_shiftsch").prop("checked",true);		
						$("#shiftschAddEdit").show();	
						$("#perdiemShiftSchedule").show();
						//shift_type
						$('#shift_type').addClass("disabledDiv");
						//$('#shiftschAddEdit').prop('disabled', 'disabled');	
						$('#dateSelGridDiv').hide();
						$('#active_perdiem_shiftid').val($('#sm_sel_perdiem_shifts').val());
					</script>
				<?php
				}
				?>
				<!-- NEW SHIFT SCHEDULING END-->
				</table>
							<input type="hidden" value="<?php echo $posid; ?>" id="assign_id_to_get_custom_rates" />
							<input type="hidden" value="<?php echo $mode_rate_type; ?>" id="moderate_type" />
						</div>
			<script>
			if(document.conreg.jobtype!="")
			{
                            Jobtype('onload');
                            customRateTypes(<?php echo $posid;?>,  '<?php echo $mode_rate_type;?>');
			}
			</script>
			<?php
			$SelQry = "SELECT ma.sno FROM multiplerates_joborder jo, multiplerates_master ma WHERE jo.status = 'ACTIVE' AND jo.joborderid = '".$type_order_id."' AND jo.jo_mode='joborder' AND ma.status='ACTIVE' AND ma.default_status='N' AND ma.rateid=jo.ratemasterid GROUP BY ma.sno";
			$resQry = mysql_query($SelQry);
			$rateValuesArray = array();
			while($recQry = mysql_fetch_assoc($resQry))
			{
                            ?>
			<script type="text/javascript">
					selectedprtidsarray.push(<?php echo $recQry['sno'];?>);
			</script>
			<?php
			$rateValuesArray[] = $recQry['sno'];
			}
			if(!empty($rateValuesArray))
			{
			?>
			<script type="text/javascript">
					pushAddEditRateRowArray('<?php echo implode(',', $rateValuesArray);?>');
			document.getElementById('selectedcustomratetypeids').value = '<?php echo implode(',', $rateValuesArray);?>';
			</script>
			<?php
			}
			?>
		<div class="form-back accordion-item">
		<table width="100%" border="0" class="crmsummary-edit-tablemin" id="crm-joborder-descriptionDiv-Table">		
			<tr>
				<td width="140" class="crmsummary-content-title">
					<div id="crm-joborder-descriptionDiv-plus1"><a style='text-decoration: none;' href="javascript:classToggle('description','plus')"><span class="crmsummary-content-title">Description</span></a></div>
					<div id="crm-joborder-descriptionDiv-minus1" class="DisplayNone"><a  style='text-decoration: none;' href="javascript:classToggle('description','minus')"><span class="crmsummary-content-title">Description</span></a></div>			
				
					</td>
					<td>
				<td>
				 <span id="rightflt" <?php echo $rightflt;?>>
				 
				 <span class="summaryform-bold-close-title" id="crm-joborder-descriptionDiv-close" style="display:none;width:auto;"><a style='text-decoration: none;' onClick="classToggle('description','minus')"  href="#crm-joborder-descriptionDiv-plus">close</a></span>					
					 <span class="summaryform-bold-close-title" id="crm-joborder-descriptionDiv-open" style="width:auto;"><a style='text-decoration: none;' onClick="classToggle('description','plus')"  href="#crm-joborder-descriptionDiv-minus"> open</a></span>
					 
                     <div class="form-opcl-btnleftside"><div align="left"></div></div>
                     <div id="crm-joborder-descriptionDiv-plus"><span onClick="classToggle('description','plus')" class="form-op-txtlnk crmsummary-process-mousepointer" ><b>+</b></span></div>
					 <div id="crm-joborder-descriptionDiv-minus"  class="DisplayNone"><span onClick="classToggle('description','minus')" class="form-cl-txtlnk crmsummary-process-mousepointer"><b>-</b></span></div>
                     <div class="form-opcl-btnrightside"><div align="left"></div></div>
                 </span>
			</td>
			</tr>
		</table>
		</div>
		<div class="jocomp-back DisplayNone" id="crm-joborder-descriptionDiv" name="crm-joborder-descriptionDiv"  <?php echo $style_table;?>>
		<table width="100%" border="0" class="crmsummary-jocomp-table">		
		<tr>
			<td width="140" class="summaryform-bold-title">Meta Keywords<br/>(for SEO)</td>
			<td><span class="summaryform-formelement"><textarea name="meta_keywords" id="meta_keywords" cols="68" rows="2" maxlength="50" onkeyup="return ismaxlength(this,50);" onKeyDown="return ismaxlength(this,50);"></textarea></span><br/><i class="summaryform-formelement">(Max 50 characters allowed) A comma separated list of your most important keywords for this job page that will be written as META keywords so that candidates searching for these keywords online will find this job opening to apply.</i></td>
		</tr>
		<tr>
			<td width="140" class="summaryform-bold-title">Meta Description<br/>(for SEO)</td>
			<td><span class="summaryform-formelement"><textarea name="meta_desc" id="meta_desc" cols="68" rows="2" maxlength="160" onkeyup="return ismaxlength(this,160);" onKeyDown="return ismaxlength(this,160);"></textarea></span><br/><i class="summaryform-formelement">(Max 160 characters allowed) The META description for this job page. This is not the job description. Search engines use this to find this job page to display to candidates searching for similar jobs.</i></td>
		</tr>
		<tr>
		<?php 
		$possumdesc= stripslashes($userFieldsArr);
		$possumdesc = utf8_decode($possumdesc);
		$possumdesc = html_tls_entities($possumdesc, ENT_QUOTES); ?>
								<td width="140" class="summaryform-bold-title"><?php echo (!empty($userFieldsAlias['Position Summary']))?$userFieldsAlias['Position Summary']:'Position Summary';echo getRequiredStar('Position Summary', $possumdesc);?></td>
								<td><span class="summaryform-formelement" id="posdesc1"><textarea name="posdesc" id="posdesc" cols="68" rows="10" style="height:300px;"></textarea>
								</span></td>
							</tr>	
							<tr>
								<td width="140" class="summaryform-bold-title">Requirements<?php echo getRequiredStar('Description Requirement', $userFieldsArr);?></td>
								<td>
									<span class="summaryform-formelement" id="requirements1">
										<textarea name="requirements" id="requirements" cols="68" rows="10" style="height:300px;"></textarea>
									</span>
								</td>
							</tr>
							<tr>
								<td width="140" class="summaryform-bold-title">Education</td>
								<td><span class="summaryform-formelement"><input name="education"  type="text" id="education"/>
								</span></td>
							</tr>
							<tr>
								<td width="140" class="summaryform-bold-title">Years of Experience</td>
								<td><span class="summaryform-formelement"><input name="experience"  type="text" id="experience"/>
								</span></td>
							</tr>		
							</table>
						</div>
				<div class="form-back accordion-item">
		<table width="100%" border="0" class="crmsummary-edit-tablemin" id="crm-joborder-skillsDiv-Table">		
			<tr>
				<td width="140" class="crmsummary-content-title">
					<div id="crm-joborder-skillsDiv-plus1"><a style='text-decoration: none;' href="javascript:classToggle('skills','plus')"><span class="crmsummary-content-title">Skills</span></a></div>
					<div id="crm-joborder-skillsDiv-minus1" class="DisplayNone"><a  style='text-decoration: none;' href="javascript:classToggle('skills','minus')"><span class="crmsummary-content-title">Skills</span></a></div>					
				
				</td>
				<td>
			<td>
				 <span id="rightflt" <?php echo $rightflt;?>>
				 
				 <span class="summaryform-bold-close-title" id="crm-joborder-skillsDiv-close" style="display:none;width:auto;"><a style='text-decoration: none;' onClick="classToggle('skills','minus')"  href="#crm-joborder-skillsDiv-plus">close</a></span>					
					 <span class="summaryform-bold-close-title" id="crm-joborder-skillsDiv-open" style="width:auto;"><a style='text-decoration: none;' onClick="classToggle('skills','plus')"  href="#crm-joborder-skillsDiv-minus"> open</a></span>
					 
                     <div class="form-opcl-btnleftside"><div align="left"></div></div>
                     <div id="crm-joborder-skillsDiv-plus" ><span onClick="classToggle('skills','plus')" class="form-op-txtlnk crmsummary-process-mousepointer"><b>+</b></span></div>
					 <div id="crm-joborder-skillsDiv-minus" class="DisplayNone"><span onClick="classToggle('skills','minus')" class="form-cl-txtlnk crmsummary-process-mousepointer" ><b>-</b></span></div>
                     <div class="form-opcl-btnrightside"><div align="left"></div></div>
                 </span>
			</td>
			</tr>
		</table>
		</div>
		<div class="jocomp-back DisplayNone" id="crm-joborder-skillsDiv" name="crm-joborder-skillsDiv"  <?php echo $style_table;?>>
		<table width="100%" border="0" class="crmsummary-jocomp-table">		
							<tr>
								<td colspan="2">
									<span class="summaryform-bold-title">Primary</span>
									<span class="summaryform-formelement">(used in candidate search)</span>
								</td>
							</tr>	
							<!-- Skill Management Enhancement -->
							<tr>
								<td colspan="8">
									<fieldset align="left" style="margin:10px 0px">
										<legend><font class="afontstyle">Departments</font></legend>
										<div class=" afontstyle skilsmanageNew" id="skillDepartment"> 
											<?php
												$allDepartments = "";
												$allDepartmentids = "";
												$selectdept = "SELECT GROUP_CONCAT(dept_cat_spec_id) AS depids FROM req_skill_cat_spec qs 
												WHERE qs.posid='".$posid."' AND qs.type='joskilldept'";
												$resultDept = mysql_query($selectdept,$db);

												if (mysql_num_rows($resultDept)>0) {
													$rowDept = mysql_fetch_assoc($resultDept);
													$alldeptids = explode(',',$rowDept['depids']);
													foreach ($alldeptids as $alldeptid) {

														$selectDeprt = "SELECT sno,deptname FROM department WHERE sno='".$alldeptid."' AND `status`='Active' ";
														$resultdept = mysql_query($selectDeprt);
														$rowDepts = mysql_fetch_assoc($resultdept);

														$allDepartments.= " <span>".$rowDepts['deptname']."</span> , ";
													}
													$allDepartments = substr($allDepartments,0,-2);
													$allDepartmentids = $rowDept['depids'];
												}

												echo $allDepartments;

											?>
										</div>

									</fieldset>
								</td>
							</tr>
							<input type="hidden" name="skilldeptids" id="skilldeptids" value="<?=$allDepartmentids?>" />
							<tr>
								<td colspan="8">
									<fieldset align="left" style="margin:10px 0px">
										<legend><font class="afontstyle">Categories</font></legend>
										<div class=" afontstyle skilsmanageNew" id="skillCategories"> 
											<?php
												$allCategories = "";
												$selectcatg = "SELECT GROUP_CONCAT(dept_cat_spec_id) AS catgyids FROM req_skill_cat_spec qs 
												WHERE posid='".$posid."' AND qs.type='jobskillcat'";
												$resultCatgy = mysql_query($selectcatg,$db);

												if (mysql_num_rows($resultCatgy)>0) {
													$rowCatg = mysql_fetch_assoc($resultCatgy);
													$allCatgryids = explode(',',$rowCatg['catgyids']);
													foreach ($allCatgryids as $allCatgryid) {

														$selectcatgry = "SELECT sno,name FROM manage WHERE sno='".$allCatgryid."' AND type='jobskillcat' ";

														$resultcatg = mysql_query($selectcatgry);
														$rowcatg = mysql_fetch_assoc($resultcatg);

														$allCategories.= " <span>".$rowcatg['name']."</span> , ";

													}
													$allCategories = substr($allCategories,0,-2);
													$allCategoriesids = $rowCatg['catgyids'];
												}

												echo $allCategories;

											?>
										</div>

									</fieldset>
								</td>
							</tr>

							<input type="hidden" name="skillcatgryids" id="skillcatgryids" value="<?=$allCategoriesids?>" />
							<tr>
								<td colspan="8">
									<fieldset align="left" style="margin:10px 0px">
										<legend><font class="afontstyle">Specialties</font></legend>
										<div class=" afontstyle skilsmanageNew" id="skillSpecialities"> 
											<?php
												$allSpecialities = "";
												$allSpecialityids = "";
												$selectsplty = "SELECT GROUP_CONCAT(dept_cat_spec_id) AS spltyids FROM req_skill_cat_spec qs 
												WHERE posid='".$posid."' AND qs.type='jobskillspeciality'";
												$resultSplty = mysql_query($selectsplty,$db);

												if (mysql_num_rows($resultSplty)>0) {
													$rowSplty = mysql_fetch_assoc($resultSplty);
													$allSpltyids = explode(',',$rowSplty['spltyids']);		
													foreach ($allSpltyids as $allSpltyid) {
														$selectSplty = "SELECT sno,name FROM manage WHERE sno='".$allSpltyid."' AND type='jobskillspeciality' ";
														$resultsplty = mysql_query($selectSplty);
														$rowSpl = mysql_fetch_assoc($resultsplty);

														$allSpecialities.= " <span>".$rowSpl['name']."</span> , ";
													}
													$allSpecialities = substr($allSpecialities,0,-2);
													$allSpecialityids = $rowSplty['spltyids'];
												}

												echo $allSpecialities;
											?>

										</div>          			
									</fieldset>
								</td>
							</tr>
							<input type="hidden" name="skillspltyids" id="skillspltyids" value="<?=$allSpecialityids?>" />
							<tr>
								<td colspan="4" align="left" style="border-bottom: 0px solid #ddd;">
								<fieldset align="left" style="margin:10px 0px">
								<legend><font class="afontstyle">Skills</font></legend>
								<table class="table" id="joborderskills">
									<tbody  id='mainSkillTable'>
									<tr id="mainHeader">

										<td></td>
										<td width=15% align=left class="summaryform-bold-title">Skill Name</td>
										<td width=13% align=left class="summaryform-bold-title">Last Used</td>
										<td width=13% align=left class="summaryform-bold-title">Skill Level</td>
										<td width=8% align=left class="summaryform-bold-title">Years of Experience</td>
										<td width="51%">

											<a href="javascript:getManagedSkills()" class="edit-list">Add/Edit&nbsp;Skills/Departments/Categories/Specialties</a>
											<input type="hidden" id="jototskills" value="1">
											<input type="hidden" id="selectedskillsids" value="">
										</td>
										<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp; </td>

									</tr>

									<tbody id="skillTable" align="left" class="align-middle"></tbody>
								  </tbody></table>
									</fieldset>	
								</td></tr>
							</table>
						</div>
				<div class="form-back accordion-item">
		<table width="100%" border="0" class="crmsummary-edit-tablemin" id="crm-joborder-hrprocessDiv-Table">		
			<tr>
				<td width="140" class="crmsummary-content-title">
					<div id="crm-joborder-hrprocessDiv-plus1"><a style='text-decoration: none;' href="javascript:classToggle('hrprocess','plus')"><span class="crmsummary-content-title">Hiring Process</span></a></div>
					<div id="crm-joborder-hrprocessDiv-minus1" class="DisplayNone"><a  style='text-decoration: none;' href="javascript:classToggle('hrprocess','minus')"><span class="crmsummary-content-title">Hiring Process</span></a></div>				
				</td>
				<td>
			<td>
				 <span id="rightflt" <?php echo $rightflt;?>>
				 
				 <span class="summaryform-bold-close-title" id="crm-joborder-hrprocessDiv-close"  style="display:none;width:auto;"><a style='text-decoration: none;' onClick="classToggle('hrprocess','minus')"  href="#crm-joborder-hrprocessDiv-plus">close</a></span>					
					 <span class="summaryform-bold-close-title" id="crm-joborder-hrprocessDiv-open" style="width:auto;"><a style='text-decoration: none;' onClick="classToggle('hrprocess','plus')"  href="#crm-joborder-hrprocessDiv-minus"> open</a></span>
					 
                     <div class="form-opcl-btnleftside"><div align="left"></div></div>
                     <div id="crm-joborder-hrprocessDiv-plus"><span onClick="classToggle('hrprocess','plus')" class="form-op-txtlnk crmsummary-process-mousepointer" ><b>+</b></span></div>

					 <div id="crm-joborder-hrprocessDiv-minus" class="DisplayNone"><span onClick="classToggle('hrprocess','minus')" class="form-cl-txtlnk crmsummary-process-mousepointer" ><b>-</b></span></div>
                     <div class="form-opcl-btnrightside"><div align="left"></div></div>
                 </span>
			</td>
			</tr>
		</table>
		</div>
		<div class="jocomp-back DisplayNone" id="crm-joborder-hrprocessDiv" name="crm-joborder-hrprocessDiv"  <?php echo $style_table;?>>
		<table width="100%" border="0" class="crmsummary-jocomp-table">		
		<tr>
			<td width="140" class="summaryform-bold-title">Contact Method</td>
			<td>
			 	
				<div class="form-check form-check-inline">
					<input class="form-check-input" name="hpcmphone" type="checkbox" id="hpcmphone" value="phone">
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
			
			<div class="form-check form-check-inline">
				<input class="form-check-input" name="hprresume" type="checkbox" id="hprresume" value="Resume">
				<label class="form-check-label">Resume </label>
			</div>
			
			<div class="form-check form-check-inline">
				<input class="form-check-input" name="hprpinterview" type="checkbox" id="hprpinterview" value="Pinterview">
				<label class="form-check-label">Phone Interview </label>
			</div>
			
			<div class="form-check form-check-inline">
				<span class="d-flex align-items-center">
				<input class="form-check-input" name="hprinterview" type="checkbox" id="hprinterview" value="Interview">
				<label class="form-check-label">Interview (avg #<input name="hpraverage" type="text" id="hpraverage" value="" size=15 maxlength="2">)</label>
				</span>
			</div>
			
			
			
			<div class="d-flex align-items-center mt-1">	
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
			
			
			<div class="form-check form-check-inline mt-1">
				<span class="d-flex align-items-center">
					<input class="form-check-input" name="hpraddinfocb" type="checkbox" id="hpraddinfocb" value="Addinfo">
					<label class="form-check-label">Additional Info 
					<input name="hprainfotb" type=text class="summaryform-formelement" id="hprainfotb" value="" size=50 maxlength=100 maxsize=100></label>
				</span>
			</div>
			
			</td>
		</tr>			
		</table>
		</div>
                            <?php if(JOB_QUESTIONNAIRE_ENABLED=='Y') { ?>
                            <div class="form-back accordion-item">
		<table width="100%" border="0" class="crmsummary-edit-tablemin" id="crm-joborder-questionnaireDiv-Table">		
			<tr>
				<td width="140" class="crmsummary-content-title">
					<div id="crm-joborder-questionnaireDiv-plus1"><a style='text-decoration: none;' href="javascript:classToggle('questionnaire','plus')"><span class="crmsummary-content-title">Questionnaire</span></a></div>
					<div id="crm-joborder-questionnaireDiv-minus1" class="DisplayNone"><a  style='text-decoration: none;' href="javascript:classToggle('questionnaire','minus')"><span class="crmsummary-content-title">Questionnaire</span></a></div>				
				</td>
				<td>
			<td>
				 <span id="rightflt" <?php echo $rightflt;?>>
				 
				 <span class="summaryform-bold-close-title" id="crm-joborder-questionnaireDiv-close"  style="display:none;width:auto;"><a style='text-decoration: none;' onClick="classToggle('questionnaire','minus')"  href="#crm-joborder-questionnaireDiv-plus">close</a></span>					
					 <span class="summaryform-bold-close-title" id="crm-joborder-questionnaireDiv-open" style="width:auto;"><a style='text-decoration: none;' onClick="classToggle('questionnaire','plus')"  href="#crm-joborder-questionnaireDiv-minus"> open</a></span>
					 
                     <div class="form-opcl-btnleftside"><div align="left"></div></div>
                     <div id="crm-joborder-questionnaireDiv-plus"><span onClick="classToggle('questionnaire','plus')" class="form-op-txtlnk crmsummary-process-mousepointer" ><b>+</b></span></div>

					 <div id="crm-joborder-questionnaireDiv-minus" class="DisplayNone"><span onClick="classToggle('questionnaire','minus')" class="form-cl-txtlnk crmsummary-process-mousepointer" ><b>-</b></span></div>
                     <div class="form-opcl-btnrightside"><div align="left"></div></div>
                 </span>
			</td>
			</tr>
		</table>
		</div>
                            
                            <div class="jocomp-back DisplayNone" id="crm-joborder-questionnaireDiv" name="crm-joborder-questionnaireDiv"  <?php echo $style_table;?>>
		<table width="100%" border="0" class="crmsummary-jocomp-table">		
		<tr>
			<td width="140" class="summaryform-bold-title">Select Group</td>
			<td>
                            <select class="summaryform-formelement" onchange="getQuestionsByGroupId(this.value);" name="questionnaireGroup" id="questionnaireGroup">
                            <option value=""> -- Select Questionnaire Group -- </option>
                            <?php   $groupId = $JobDet['53'];
                                  echo  getActiveQuestionnaireGroups($groupId);
                            ?>
                            </select>
			</td>
		</tr>
                <tr id="displayQuestByGroupId">
                    <?php echo  displayQuestionsBasedOnGroupIdEditNCreateJob($groupId); ?>
                </tr>
                
               </table>
		</div>
                            <?php } ?>
				<div class="form-back crmTravelInput accordion-item">
		<table width="100%" border="0" class="crmsummary-edit-tablemin" id="crm-joborder-relocationDiv-Table">		
			<tr>
				<td width="200" class="crmsummary-content-title">
					<div id="crm-joborder-relocationDiv-plus1"><a style='text-decoration: none;' href="javascript:classToggle('relocation','plus')"><span class="crmsummary-content-title">Travel/Relocation Requirements</span></a></div>
					<div id="crm-joborder-relocationDiv-minus1" class="DisplayNone"><a  style='text-decoration: none;' href="javascript:classToggle('relocation','minus')"><span class="crmsummary-content-title">Travel/Relocation Requirements</span></a></div>				

				</td>
				<td>
			<td>
				 <span id="rightflt" <?php echo $rightflt;?>>
				 
				 <span class="summaryform-bold-close-title" id="crm-joborder-relocationDiv-close" style="display:none;width:auto;"><a style='text-decoration: none;' onClick="classToggle('relocation','minus')"  href="#crm-joborder-relocationDiv-plus">close</a></span>
				<span class="summaryform-bold-close-title" id="crm-joborder-relocationDiv-open" style="width:auto;"><a style='text-decoration: none;' onClick="classToggle('relocation','plus')"  href="#crm-joborder-relocationDiv-minus">open</a></span>
					 
                     <div class="form-opcl-btnleftside"><div align="left"></div></div>
                     <div id="crm-joborder-relocationDiv-plus" ><span onClick="classToggle('relocation','plus')" class="form-op-txtlnk crmsummary-process-mousepointer"><b>+</b></span></div>
					 <div id="crm-joborder-relocationDiv-minus" class="DisplayNone"><span onClick="classToggle('relocation','minus')" class="form-cl-txtlnk crmsummary-process-mousepointer" ><b>-</b></span></div>
                     <div class="form-opcl-btnrightside"><div align="left"></div></div>
                 </span>
			</td>
			</tr>
		</table>
		</div>
		<div class="jocomp-back DisplayNone" id="crm-joborder-relocationDiv" name="crm-joborder-relocationDiv"  <?php echo $style_table;?>>
		<table width="100%" border="0" class="crmsummary-jocomp-table">		
		<tr valign="middle">
			<td valign="baseline"><span class="summaryform-bold-title">Travel</span></td>
			<td valign="baseline">
			
			<div class="form-check form-check-inline">
				<input class="form-check-input" type="radio" name="trtravel" id="trtravel"  value="Yes">
				<label class="form-check-label">Yes </label>
			</div> 
			
			<div class="form-check form-check-inline">
				<input class="form-check-input" name="trtravel" id="trtravel" type="radio" checked value="No">&nbsp;
				<label class="form-check-label">No &nbsp;|&nbsp;
				</label>
			</div>				
			
			<div class="form-check form-check-inline">
				<input name="trtravelpercentage" type="text" id="trtravelpercentage" value="" size=5>
				<label class="form-check-label"> % &nbsp;&nbsp;| 
					Other &nbsp;&nbsp;
					<input name="trtravelother" type="text" id="trtravelother" value="" size=29>
				</label>
			</div>
			</td>
		</tr>
		<tr valign="middle">
			<td valign="baseline"><span class="summaryform-bold-title">Relocation</span></td>
			<td valign="baseline">
			
			<div class="form-check form-check-inline">
				<input class="form-check-input" type="radio" name="trrelocation" id="trrelocation" value="Yes">
				<label class="form-check-label">Yes </label>
			</div>
			
			<div class="form-check form-check-inline">
				<input class="form-check-input" type="radio" name="trrelocation" id="trrelocation" checked value="No"> &nbsp;
				<label class="form-check-label">No &nbsp;|&nbsp; City</label>
			</div>
			
			<input name="trrelocationcity" type="text" id="trrelocationcity" value="" size=10>
			&nbsp;State 
			
			<input name="trrelocationstate" type="text" id="trrelocationstate" value="" size=5>&nbsp;
			Country &nbsp;
			<select name="trrelocationcounty" id="trrelocationcounty">
				<option selected value=''>--Select--</option>
				<?php echo $ContryOptions;?>
			</select>
    		</td> 
		</tr>

		<tr valign="middle">
			<td valign="baseline"><span class="summaryform-bold-title">Commute</span></td>
			<td valign="baseline">
			<span class="summaryform-formelement"><input name="trcommutehrs" type="text" id="trcommutehrs" value="" size=5 summaryform-formelement>
			</span>
			<span class="summaryform-bold-title" >&nbsp;hrs&nbsp;| &nbsp; </span>
			<span class="summaryform-formelement"><input name="trcommutemiles" type="text" id="trcommutemiles" value="" size=5 summaryform-formelement>
			</span>
			<span class="summaryform-bold-title" >&nbsp;miles &nbsp;| &nbsp;</span>
			<span class="summaryform-bold-title">Other </span>
		<span class="summaryform-formelement"><input name="trcommuteother" type="text" id="trcommuteother" value="" size=32 summaryform-formelement>
		</span>
		</td>
		</tr>
		</table>
		</div>
	</fieldset>
</div>
</form>	
		</div>
	</div>
	</td>
  </tr>
</table>
<?php 
if($copyjoborder != 'yes') 
	print "<script>tp1.setSelectedIndex(1);setFormObject('document.conreg');</script>"; 
else 
	print "<script>tp1.setSelectedIndex(0);setFormObject('document.conreg');</script>";
?>

<?
if(($jrtcontact>0 || $jrt_loc>0) && ($billcontact>0 || $bill_loc>0))
	print "<script>getBothLocations('".$jrtcompany."','".$jrtcontact."','".$jrt_loc."','jrt','".$billcompany."','".$billcontact."','".$bill_loc."','bill', 1);</script>";
else if(($jrtcontact>0 || $jrt_loc>0) && ($billcontact==0 || $billcontact=="" || $bill_loc==0 || $bill_loc==""))
	print "<script>getCRMLocations('".$jrtcompany."','".$jrtcontact."','".$jrt_loc."','jrt',1);</script>";
else if(($billcontact>0 || $bill_loc>0) && ($jrtcontact==0 || $jrtcontact=="" || $jrt_loc==0 || $jrt_loc==""))
	print "<script>getCRMLocations('".$billcompany."','".$billcontact."','".$bill_loc."','bill',1);</script>";
?>
<input type="hidden" id="cap_separated_custom_rates" value="" />
<script>
    classToggle('billinginfo','plus');
    classToggle('billinginfo','minus');
    classToggle('schedule','plus');
    classToggle('schedule','minus');
    classToggle('description','plus');
    classToggle('description','minus');
    var jrt_bill_loc = window.document.getElementById('jrt_loc');
    var sbloc = jrt_bill_loc.value.split("-");
    if(sbloc[0]=="loc" || sbloc[0]=="com")
    {
        <?php
        if($chk_bt)
        {?>
                preLoadBurdenType(sbloc[1],'CRM');
        <?
        }
        ?>
    }
$(window).focus(function() {
    if(document.getElementById('payrate_calculate_confirmation_window_onblur').value != ""){
        document.getElementById('payrate_calculate_confirmation').value = document.getElementById('payrate_calculate_confirmation_window_onblur').value;
    }
    if(document.getElementById('billrate_calculate_confirmation_window_onblur').value != ""){
        document.getElementById('billrate_calculate_confirmation').value = document.getElementById('billrate_calculate_confirmation_window_onblur').value;
    }
});
</script>
<script type="text/javascript">    
	$(document).ready(function(){
		document.getElementById("jrt_loc").style.visibility = "visible";
		$("#jrt_loc").select2();
	});
</script>
<script type="text/javascript" src="/BSOS/scripts/shift_schedule/jquery.modalbox.js"></script>
<link type="text/css" rel="stylesheet" href="/BSOS/css/gigboard/select2_V_4.0.3.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/shift_schedule/shiftcolors.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/gigboard/gigboardCustom.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/select2_shift.css">
<script type="text/javascript" src="/BSOS/scripts/gigboard/select2_V_4.0.3.js"></script>
<style type="text/css">
.timegridcontainer div{ flex:inherit !important;}
.timegrid {width: 2% !important;}
</style>
</div>
<?php require("footer.inc.php");?>
</body>
</html>