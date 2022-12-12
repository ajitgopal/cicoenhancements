<?php 
	$page151=$page15;
	$page2151=$page215;
	$close_addrVal=$addr;
    $refJoborderDetArray = explode('|',$addr);//referral Bonus passing placed job order
    $refBonusJOID = $refJoborderDetArray[0];
    $licode=$wali; 	
	require("global.inc");

	require("bulk_placement/class.bulkPlacement.inc");
	$bulkplaceObj = new bulkPlacements();
	require("functions.php");
	require("crm_acc_Locations.inc");

	require_once('waitMsg.inc'); //added by Swapna for Processing please wait message
	require_once($akken_psos_include_path.'commonfuns.inc');
	require_once($akken_psos_include_path.'defaultTaxes.php');
	require_once("multipleRatesClass.php");
	$ratesObj = new multiplerates();
	
	//Credentials Class file for HRM module
	require_once('credential_management/hrm_credentials_db.php');
	$objHRMCredentials	= new HRMCredentials();
	
	//This is to store the display of shift schedule while placing whether the cnadidate is placed based on new shift schedule or old. By default old schedule display.
	$schedule_display = 'OLD';
	



	//Perform the action/include only when new shift scheduling is enabled
	if(SHIFT_SCHEDULING_ENABLED == 'Y') 
	{
		/* including shift schedule class file for converting Minutes to DateTime / DateTime to Minutes */
		require_once('shift_schedule/class.schedules.php');
		$objSchSchedules	= new Schedules();

		/* including crm shift schedule class file for common functions */
		require_once('shift_schedule/crm_schedule_db.php');
		$objScheduleDetails	= new CandidateSchedule();

		/* including crm perdiem shift schedule class file for common functions */
		require_once("perdiem_shift_sch/Model/class.crm_perdiem_sch_db.php");
		$objPerdiemScheduleDetails = new CRMPerdiemShift();
		
		//override the schedule display if new shift schedule is enabled while placing
		$schedule_display = 'NEW';
	}
	$addr=$close_addrVal; require("../../Include/JP_PreferenceFunc.php");//Function for Hotjob expire date updation 

	$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todate=date("m/d/Y",$thisday);
	$date_today=date("n/j/Y",$thisday);
	
	//stripping the slashes as this variable is getting with addslashes already - 26-Sep-2014.
	$page151 = stripslashes($page151);

	$page152=explode("|",$page151);

	$contractid = $page152[92];
	$classification = $page152[93];
	
	function shiftdate_sort($a, $b) {
		
		return strtotime($a) - strtotime($b);
	}
	
	/*
		Bulk placement Start 
	*/

		/*
			Default Sequence Number
		*/
		$seqnumber = strtotime("now");		
		
		/*
			Inserting into bulk place batch by default process Stage in N (No), 
			i.e  N (NO) => The Cron wont pick the record from bulk place batch to process.
		*/
		$joborderid = $posid;
		$shiftId="0";
		$bulk_shift_id = "0";
		$insert_batch = "INSERT INTO bulk_place_batches (comp_id,process,posid,shift_id,cdate,cuser,mdate) VALUES ('".$companyuser."','N','".$joborderid."','".$shiftId."',NOW(),'".$username."',NOW())";
		mysql_query($insert_batch,$maindb) or die(mysql_error());
		/*
			Getting the Last Insert Id of Bulk Place Batch Table.
		*/
		$batch_id = mysql_insert_id($maindb);
		/*
			Inserting Referance records into Bulk Place Master Batch.
		*/
		$insert_batch = "INSERT INTO bulk_placement_master_batch (batch_id,process,posid,seqnumber,cdate,cuser,muser,mdate) VALUES ('".$batch_id."','N','".$joborderid."','".$seqnumber."',NOW(),'".$username."','".$username."',NOW())";
		mysql_query($insert_batch,$db) or die(mysql_error());
		/*
			Getting the Last Insert Id of Bulk Place Master Batch Table.
		*/
		$master_batch_id = mysql_insert_id($db);
		$candidsArry = explode(",", $selCandShiftIds);
		foreach ($candidsArry as $key => $eachCandSno) {

			$mycandAry = explode("^",$eachCandSno);
			$bulkeachcandSno = $mycandAry[0];
			$shiftId = $mycandAry[1];
			if(array_key_exists("2",$mycandAry)) { $resumesno = $mycandAry[2]; }else{$resumesno = 0;};

			if(SHIFT_SCHEDULING_ENABLED == 'N') 
			{
				$shift_det = explode("|",$new_shift_name);
				$shiftId  = $shift_det[0];	
			}	
			if ($shiftId == "") {
				$shiftId =0;
			}
			if ($resumesno == "") {
				$resumesno =0;
			}
			if ($bulkeachcandSno !="" && $bulkeachcandSno !="0") {
				/*
					Inserting candidates records into Bulk Place Queue.
				*/
				
				if($resumesno > 0){
					$resumesno_cond = "AND resume_sno = '".$resumesno."'";
					 $seqno_by_resumesno = "SELECT seqnumber FROM resume_status WHERE sno='".$resumesno."' AND req_id ='".$joborderid."'";
					$result_seqno = mysql_query($seqno_by_resumesno,$db);
					$result_seq = mysql_fetch_row($result_seqno);
					$seqnumber = $result_seq[0];
					$seqnumber_cond = "";
				}else{
					$seqnumber_cond = "AND seqnumber = '".$seqnumber."'";
					$resumesno_cond = "";
				}				
				$selectQueue = "SELECT sno FROM bulk_placement_queue WHERE cand_id='".$bulkeachcandSno."' AND posid='".$joborderid."' AND shift_id='".$shiftId."' ".$resumesno_cond." ".$seqnumber_cond."";
				$resultQueue = mysql_query($selectQueue,$db);
				
				if (mysql_num_rows($resultQueue)==0) {
				
				$insert_bulkplace = "INSERT INTO bulk_placement_queue (master_batch_id,cand_id,posid,shift_id,status,muser,mdate,resume_sno,seqnumber) VALUES ('".$master_batch_id."','".$bulkeachcandSno."','".$joborderid."','".$shiftId."','Intiated','".$username."',NOW(),'".$resumesno."','".$seqnumber."')"; 
				mysql_query($insert_bulkplace,$db) or die(mysql_error());
				
					if($resumesno > 0){
											
						$com_con_on_posdesc = "select company, contact from posdesc where  posid='".$joborderid."'";
						$result_com_con = mysql_query($com_con_on_posdesc,$db);
						$com_con = mysql_fetch_row($result_com_con);
						
						$manage_sno_qry = "select sno  from manage where name = 'Processing Placement' and type='interviewstatus' limit 1";
						$result_manage_sno = mysql_query($manage_sno_qry,$db);
						$manage_sno = mysql_fetch_row($result_manage_sno);
						
						$res_upd="UPDATE resume_status SET status='".$manage_sno[0]."',appdate=CURRENT_DATE(),muser='".$username."',mdate=NOW() WHERE sno='".$resumesno."'";
						mysql_query($res_upd,$db);
						
						$insert_resume_his = "Insert into resume_history (cli_name, req_id, res_id, comp_id, appuser, appdate, status, type, muser, mdate, seqnumber,shift_id)
						values ('".$com_con[1]."', '".$joborderid."', '".$bulkeachcandSno."', '".$com_con[0]."', '".$username."', now(), '".$manage_sno[0]."', 'cand', '".$username."', now(), '".$seqnumber."','".$shiftId."')";
						mysql_query($insert_resume_his,$db);
					}
					
					/*
						Calling Submission Procedure to make candidate/Employee to Submit on the Job Order.
					*/
					if($resumesno == 0 || $resumesno = ""){
						mysql_query("CALL bulk_submission_proc('".$joborderid."','".$bulkeachcandSno."','".$username."','Submitted','".$seqnumber."','Processing Placement','".$shiftId."')",$db);
					}	
				}	
			}
		}

		if (($shiftId !="" && $shiftId !="0")) {
			$bulk_shift_id = $shiftId; 
			$updateShift = "UPDATE bulk_place_batches SET shift_id='".$shiftId."' WHERE sno='".$batch_id."'";
			mysql_query($updateShift,$maindb);
		}

	/*
		END
	*/



	$sm_form_data_shift_id = array();
	$timeslotvalues = "";
	
	//$timeslotvalues = $sm_form_data;
	$shift_snos = $sm_sel_shifts; //getting selected shift name snos for inserting previous date data
	// Getting the Shift Name Sno  for this table shift_setup >> sno
	$shift_id = '0';
	if(SHIFT_SCHEDULING_ENABLED == 'Y')
	{
		if($shift_type=='regular')
		{
			// Loop to get shift data for the selected shift sno
			$sm_form_data_session_val = $_SESSION['sm_form_data_array'.$candrn];
			$sm_form_data_implode = implode("|", $sm_form_data_session_val);
			$sm_plcmnt_array=explode("|",$sm_form_data_implode);
			$sm_req_cnt	= count($sm_plcmnt_array);
			for($i=0;$i<$sm_req_cnt;$i++)
			{
				$smVal = trim($sm_plcmnt_array[$i]);
				if($smVal != "")
				{
					$smValExp = explode("^",$smVal);
					if ($smValExp[6] == $shift_snos) {
						$shift_id = $smValExp[6];
						//if (!in_array($sm_plcmnt_array[$i], $sm_form_data_shift_id)) {
							array_push($sm_form_data_shift_id, $sm_plcmnt_array[$i]);
						//}				
					}
				}
			}	
			
			$timeslotvalues = implode("|", $sm_form_data_shift_id);
			$sm_form_data = implode("|", $sm_form_data_shift_id);
			// Loop to get shift data for the selected shift sno
		}
		else if($shift_type=='perdiem')
		{
			$shift_id 		= $sm_sel_perdiem_shifts;
			$shift_snos		= $sm_sel_perdiem_shifts;
			$sm_sel_shifts  = $sm_sel_perdiem_shifts;
			$timeslotvalues = generateTimeSlotsFromSession($userid,$id_req);
		}
	}

	// Positions on placement perdiem shifts
	$nextPlacePositon = $nextPos;
	// Placement Shift Name/ Time Information
	$shift_st_time = '';
	$shift_et_time = '';
	if(SHIFT_SCHEDULING_ENABLED == 'N') 
	{
		$shift_det = explode("|",$new_shift_name);
		$shift_id  = $shift_det[0];
		$shift_st_time = $shift_time_from;
		$shift_et_time = $shift_time_to;

	}

	if ($bulk_shift_id !="0" && $shift_id == "0") {
		$shift_id = $bulk_shift_id;
	}

	$typeofJobtype=$page152[44];
	$assignment_name=$page152[21];
	$job_title=$page152[2];
	$rateRowVals = $page152[82];	
	
	if($typeofJobtype == 'Direct')
	{
		$joindustryid = $page152[74];
		$comm_bill_burden = $page152[75];
	}
	else
	{
		$joindustryid = $page152[85];
		$comm_bill_burden = $page152[86];
	}

	if($page152[84] == '' || is_null($page152[84]))
		$page152[84] = $deptjoborder;

	if($page152[44]=='Direct')
	{	
		// This code is For Direct Job Order >> WorkSite Code
    		$page152[88] = $page152[77];
    	}

	if($page152[44]=='Direct' || $page152[44]=='Internal Direct')
	{
		$page152[74]='0.00';
		$page152[75]='0.00';
		$page152[76]='0.00';
		$page152[77]='';
		$page152[78]='';
		$page152[79]='N';
		$page152[80]='N';
	}

	$page152[81] = ($page152[79] == 'Y') ? $page152[81] : '0.00';

	if($assignment_name == '' && $job_title != '')
		$assignment_name = $job_title;

	$page152[21]=$assignment_name;

	function addrValue($row1,$row2,$row3,$row4,$row5,$row6,$row7)
	{
		$comp_addr="";

		//billingcompany
		if($row1!='')
			$comp_addr=$row1;

		//address1
		if($row2!='')
			$comp_addr = ($comp_addr=="") ? $row2 : $comp_addr.=",".$row2;

		//address2
		if($row3!='')
			$comp_addr = ($comp_addr=="") ? $row3 : $comp_addr.=",".$row3;

		//city
		if($row4!='')
			$comp_addr = ($comp_addr=="") ? $row4 : $comp_addr.=",".$row4;

		//state
		if($row5!='')
			$comp_addr = ($comp_addr=="") ? $row5 : $comp_addr.=",".$row5;

		//Country
		if($row6!='')
			$comp_addr = ($comp_addr=="") ? $row6 : $comp_addr.=",".$row6;

		//zip
		if($row7!='')
			$comp_addr = ($comp_addr=="") ? $row7 : $comp_addr.=",".$row7;

		return($comp_addr);
	}

	if(isset($hdnbt_details)){
		$burdenTypeDetails  =   $hdnbt_details;//burden_type_details
	}else{
		$burdenTypeDetails = "";
	}
	if(isset($hdnbi_details)){
		$burdenItemDetails  =   $hdnbi_details;//burden_item_details
	}else{
		$burdenItemDetails = "";
	}

	if(isset($bill_hdnbt_details)){
		$bill_burdenTypeDetails  =   $bill_hdnbt_details;//bill_burden_type_details
	}else{
		$bill_burdenTypeDetails = "";
	}
	if(isset($bill_hdnbi_details)){
		$bill_burdenItemDetails  =   $bill_hdnbi_details;//bill_burden_item_details
	}else{
		$bill_burdenItemDetails = "";
	}
        
?>
<html>
<head>
<title>Bulk Placement</title>
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/grid.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/filter.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/site.css" type=text/css rel=stylesheet>
<link href="/BSOS/css/crm-editscreen.css" type=text/css rel=stylesheet>
<link type="text/css" rel="stylesheet" href="/BSOS/css/crm-summary.css">
<link rel="stylesheet" href="/BSOS/popupmessages/css/popup_message.css" media="screen" type="text/css">
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popupMsgArray.js"></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popup-message.js"></script>
<script language=javascript src="/BSOS/scripts/validateassignment.js"></script>
<script language=javascript src="/BSOS/scripts/dynamicElementCreatefun.js"></script>
<script language=javascript src="/BSOS/scripts/tabpane.js"></script>
<script language=javascript src="/BSOS/scripts/place_schedule.js"></script>
</head>
	
<?php
	$sinaddr=explode("|",$addr);
	$id_req=$sinaddr[0];
	$id=$sinaddr[1];
	$corp_code= 0;
	$sub_seqnumber=$seqnumber;	
	
	$pos_que="SELECT contact,type,postitle,company FROM posdesc where posid='".$id_req."'";
	$res_pos_que=mysql_query($pos_que,$db);
	$pos_fetch=mysql_fetch_row($res_pos_que);	
	$postitle=$pos_fetch[2];

	//Getting the emailid of the contact
	if($pos_fetch[1]=="staffoppr")
	{
		$oppr_que="SELECT email,sno FROM staffoppr_contact where sno='".$pos_fetch[0]."'";
	}
	else
	{
		$oppr_que="SELECT email,sno FROM staffacc_contact where sno='".$pos_fetch[0]."' and staffacc_contact.username!=''";
	}

	$res_oppr=mysql_query($oppr_que,$db);
	$oppr_fetch=mysql_fetch_row($res_oppr);
		
	// END

	$page152=explode("|",$page151);
	
	if($page152[84] == '' || is_null($page152[84]))
		$page152[84] = $deptjoborder;

	$deptId_jo = $page152[84];

	if($page152[44]=='Direct')
	{	
		// This code is For Direct Job Order >> WorkSite Code 
    		$page152[88] = $page152[77];
    	}

	if($page152[44]=='Direct' || $page152[44]=='Internal Direct')
	{    	
		$page152[25]=$page152[18];
		$page152[26]=$page152[19];
		$page152[27]=$page152[20];

		$page152[74]='0.00';
		$page152[75]='0.00';
		$page152[76]='0.00';
		$page152[77]='';
		$page152[78]='';
		$page152[79]='N';
		$page152[80]='N';
	}
	
	$getComConLocId = 0;
	$getBillComConLocId = 0;
    
	$vendorCompany =(substr($page152[87],0,6)=="vencom")?explode("vencom",$page152[87]):'';
    $crmCompany    =(substr($page152[7],0,3)=="com")?explode("com",$page152[7]):'';

	$type =(substr($page152[5],0,3)=="ven")?((($vendorCompany[1] == $crmCompany[1]) && ($vendorCompany[1] !='') && ($crmCompany[1] !=''))?'BOTH':'CUST'):'CUST';
	
	if(substr($page152[6],0,3)=="con")
	{
		$convalue=explode("con",$page152[6]);
		
		if($convalue[1]!="" && $convalue[1]!="0")
		{
			$con_sel="SELECT acc_cont,csno,sno FROM staffoppr_contact WHERE sno='".$convalue[1]."'";
			$res_con=mysql_query($con_sel,$db);
			$fetch_con=mysql_fetch_row($res_con);

			if($fetch_con[0]=="0")
			{   
                
				$values=CreateAccountingCustomers($fetch_con[1],$fetch_con[2],$username,$type);
				$page152[6]=$values[1];
				$getComConLocId = $values[0];
			}
			else
			{
				$sel_acc_con="SELECT sno FROM staffacc_contact WHERE sno='".$fetch_con[0]."' and staffacc_contact.username!=''";
				$res_acc_con=mysql_query($sel_acc_con,$db);
				$fetch_acc_con=mysql_fetch_row($res_acc_con);
				$page152[6]=$fetch_acc_con[0];
			}
		}
	}

	if(substr($page152[8],0,3)=="rep")
	{
		$convalue=explode("rep",$page152[8]);
		if($convalue[1]!="" && $convalue[1]!="0")
		{
			$con_sel="SELECT acc_cont,csno,sno FROM staffoppr_contact WHERE sno='".$convalue[1]."'";
			$res_con=mysql_query($con_sel,$db);
			$fetch_con=mysql_fetch_row($res_con);

			if($fetch_con[0]=="0")
			{ 
				
				$values=CreateAccountingCustomers($fetch_con[1],$fetch_con[2],$username,$type);
				$page152[8]=$values[1];
			}
			else
			{
				$sel_con="SELECT sno FROM staffacc_contact WHERE sno='".$fetch_con[0]."' and staffacc_contact.username!=''";
				$res_con=mysql_query($sel_con,$db);
				$fetch_con=mysql_fetch_row($res_con);
				$page152[8]=$fetch_con[0];
			}
		}
	}

	if(substr($page152[34],0,4)=="bill")
	{
		$convalue=explode("bill",$page152[34]);
		if($convalue[1]!="" && $convalue[1]!="0")
		{
			$con_sel="SELECT acc_cont,csno,sno FROM staffoppr_contact WHERE sno='".$convalue[1]."'";
			$res_con=mysql_query($con_sel,$db);
			$fetch_con=mysql_fetch_row($res_con);

			if($fetch_con[0]=="0")
			{  
				$values=CreateAccountingCustomers($fetch_con[1],$fetch_con[2],$username,$type);
				$page152[34]=$values[1];
			}
			else
			{
				$sel_acc_con="SELECT sno FROM staffacc_contact WHERE sno='".$fetch_con[0]."' and staffacc_contact.username!=''";
				$res_acc_con=mysql_query($sel_acc_con,$db);
				$fetch_acc_con=mysql_fetch_row($res_acc_con);
				$page152[34]=$fetch_acc_con[0];
			}
		}
	}

	if(substr($page152[5],0,3)=="ven")
	{
		$convalue=explode("ven",$page152[5]);
		if($convalue[1]!="" && $convalue[1]!="0")
		{
			$con_sel="SELECT acc_cont,csno,sno FROM staffoppr_contact WHERE sno='".$convalue[1]."'";
			$res_con=mysql_query($con_sel,$db);
			$fetch_con=mysql_fetch_row($res_con);

			if($fetch_con[0]=="0")
			{
				$values=CreateAccountingCustomers($fetch_con[1],$fetch_con[2],$username,'CV');
				$page152[5]=$values[1];
				$page152[46]=$values[2];
			}
			else
			{
				$sel_con="SELECT sno,username FROM staffacc_contact WHERE sno='".$fetch_con[0]."' and staffacc_contact.username!=''";
				$res_con=mysql_query($sel_con,$db);
				$fetch_con=mysql_fetch_row($res_con);
				$page152[5]=$fetch_con[0];
				$page152[46]=$fetch_con[1];
			}
		}
	}

	if(substr($page152[7],0,3)=="com")
	{
		$convalue=explode("com",$page152[7]);
		if($convalue[1]!="" && $convalue[1]!="0")
		{
			$comp_sel="SELECT acc_comp FROM staffoppr_cinfo WHERE sno='".$convalue[1]."'";
			$res_comp=mysql_query($comp_sel,$db);
			$fetch_comp=mysql_fetch_row($res_comp);

			if($fetch_comp[0]=="0")
			{
				$con_sel="SELECT sno FROM staffoppr_contact WHERE csno='".$convalue[1]."'";
				$res_con=mysql_query($con_sel,$db);
				$fetch_con=mysql_fetch_row($res_con);
                
				
				$values=CreateAccountingCustomers($convalue[1],$fetch_con[0],$username,$type);
				$page152[7]=$values[0];
				$getComConLocId = $values[0];
			}
			else
			{
				$sel_comp="SELECT staffacc_cinfo.sno FROM staffacc_cinfo,staffoppr_cinfo WHERE staffoppr_cinfo.acc_comp = staffacc_cinfo.sno AND staffacc_cinfo.type IN ('CUST', 'BOTH') AND staffoppr_cinfo.sno='".$convalue[1]."'";
				$res_comp=mysql_query($sel_comp,$db);
				$fetch_comp=mysql_fetch_row($res_comp);
				$page152[7]=$fetch_comp[0];
				$getComConLocId = $fetch_comp[0];
			}
		}

		$corp_code = getCorporationCode($convalue[1]);
	}
      
	if(substr($page152[10],0,3)=="job")
	{
		$sjobloc=explode("job",$page152[10]);
		$convalue=explode("-",$sjobloc[1]);
		
		if($convalue[1]!="" && $convalue[1]!="0")
		{
			if($convalue[0]=="con")
			{
				$con_sel="SELECT c.acc_cont,c.csno,c.sno FROM staffoppr_contact c LEFT JOIN staffoppr_location l ON l.csno=c.sno WHERE l.ltype='con' AND l.sno='".$convalue[1]."'";
				$res_con=mysql_query($con_sel,$db);
				$fetch_con=mysql_fetch_row($res_con);

				if($fetch_con[0]=="0")
				{   
			      
					$values=CreateAccountingCustomers($fetch_con[1],$fetch_con[2],$username,$type);
					$acc_conid=$values[1];
				}
				else
				{
					$sel_acc_con="SELECT sno FROM staffacc_contact WHERE sno='".$fetch_con[0]."' and staffacc_contact.username!=''";
					$res_acc_con=mysql_query($sel_acc_con,$db);
					$fetch_acc_con=mysql_fetch_row($res_acc_con);
					$acc_conid=$fetch_acc_con[0];
				}
				
				$page152[10] = getACCLocation($acc_conid,"con");
			}
			else if($convalue[0]=="com")
			{
				 $comp_sel="SELECT c.acc_comp, c.sno FROM staffoppr_cinfo c LEFT JOIN staffoppr_location l ON l.csno=c.sno WHERE l.ltype='com' AND l.sno='".$convalue[1]."'";
				$res_comp=mysql_query($comp_sel,$db);
				$fetch_comp=mysql_fetch_row($res_comp);
				
                                
				if($fetch_comp[0]=="0")
				{
					$con_sel="SELECT sno FROM staffoppr_contact WHERE csno='".$fetch_comp[1]."'";
					$res_con=mysql_query($con_sel,$db);
					$fetch_con=mysql_fetch_row($res_con);
                   
					$values=CreateAccountingCustomers($fetch_comp[1],$fetch_con[0],$username,$type);
					$acc_comid=$values[0];
				}
				else
				{					
					$sel_comp="SELECT staffacc_cinfo.sno FROM staffacc_cinfo,staffoppr_cinfo WHERE staffoppr_cinfo.acc_comp = staffacc_cinfo.sno AND staffacc_cinfo.type IN ('CUST', 'BOTH') AND staffoppr_cinfo.sno='".$fetch_comp[1]."'";
					$res_comp=mysql_query($sel_comp,$db);
					$fetch_comp=mysql_fetch_row($res_comp);
					$acc_comid=$fetch_comp[0];
				}
				$page152[10] = getACCLocation($acc_comid,"com");			
			}
			else
			{
				$page152[10] = createACCLocation($convalue[1],"loc");
				
			}
		}
	}
       
	if(substr($page152[35],0,7)=="billcom")
	{
		$sbillloc=explode("billcom",$page152[35]);
		$convalue=explode("-",$sbillloc[1]);
		if($convalue[1]!="" && $convalue[1]!="0")
		{
			
			if($convalue[0]=="con")
			{
				$con_sel="SELECT c.acc_cont,c.csno,c.sno FROM staffoppr_contact c LEFT JOIN staffoppr_location l ON l.csno=c.sno WHERE l.ltype='con' AND l.sno='".$convalue[1]."'";
				$res_con=mysql_query($con_sel,$db);
				$fetch_con=mysql_fetch_row($res_con);

				if($fetch_con[0]=="0")
				{  
			        
					$values=CreateAccountingCustomers($fetch_con[1],$fetch_con[2],$username,$type);
					$acc_conid=$values[1];
				}
				else
				{
					$sel_acc_con="SELECT sno FROM staffacc_contact WHERE sno='".$fetch_con[0]."' and staffacc_contact.username!=''";
					$res_acc_con=mysql_query($sel_acc_con,$db);
					$fetch_acc_con=mysql_fetch_row($res_acc_con);
					$acc_conid=$fetch_acc_con[0];
				}
				
				$getBillComConLocId = $acc_conid;
				$page152[35] = getACCLocation($acc_conid,"con");
			}
			else if($convalue[0]=="com")
			{
				$comp_sel="SELECT c.acc_comp, c.sno FROM staffoppr_cinfo c LEFT JOIN staffoppr_location l ON l.csno=c.sno WHERE l.ltype='com' AND l.sno='".$convalue[1]."'";
				$res_comp=mysql_query($comp_sel,$db);
				$fetch_comp=mysql_fetch_row($res_comp);

				if($fetch_comp[0]=="0")
				{
					$con_sel="SELECT sno FROM staffoppr_contact WHERE csno='".$fetch_comp[1]."'";
					$res_con=mysql_query($con_sel,$db);
					$fetch_con=mysql_fetch_row($res_con);
					
					$values=CreateAccountingCustomers($fetch_comp[1],$fetch_con[0],$username,$type);
					$acc_comid=$values[0];
				}
				else
				{
					$sel_comp="SELECT staffacc_cinfo.sno FROM staffacc_cinfo,staffoppr_cinfo WHERE staffoppr_cinfo.acc_comp = staffacc_cinfo.sno AND staffacc_cinfo.type IN ('CUST', 'BOTH') AND staffoppr_cinfo.sno='".$fetch_comp[1]."'";
					$res_comp=mysql_query($sel_comp,$db);
					$fetch_comp=mysql_fetch_row($res_comp);
					$acc_comid=$fetch_comp[0];
				}

				$getBillComConLocId = $acc_comid;
				$page152[35] = getACCLocation($acc_comid,"com");
			}
			else
			{
				$page152[35] = createACCLocation($convalue[1],"loc");
				$getBillComConLocId = $page152[35];
			}
			
			updateComBillAddress($convalue[0],$getBillComConLocId,$getComConLocId);
		}
		else if($sbillloc[1] == "") {
			$sel_acccomloc = "SELECT sno FROM staffacc_location WHERE csno='".$getComConLocId."' AND ltype='com' AND status='A'";
			$res_acccomloc = mysql_query($sel_acccomloc,$db);
			$fetch_acccomloc = mysql_fetch_row($res_acccomloc);

			$updcinfo_qry = "UPDATE staffacc_cinfo SET bill_address = '".$fetch_acccomloc[0]."' WHERE sno ='".$getComConLocId."'";
			mysql_query($updcinfo_qry,$db);
		}
	}
    
	// if billing information came 0 then customers billing address and biling contact keeping for that assignment
	if($page152[34] == '0' || $page152[34] == '' || $page152[34] == 'bill' || $page152[34] == 'bill0')
		$page152[34] = $page152[6];

	if(($page152[35] == '0' || $page152[35] == '' || $page152[35] == 'billcom') && $page152[7]>0)
		$page152[35] = getACCLocation($page152[7],"com");
	

	//Function to update selected billing address of a company
	function updateComBillAddress($loctype, $locid, $comconlocid){
		global $maildb,$db;
		
		if($loctype == 'com') {
			$sel_acccomloc = "SELECT sno FROM staffacc_location WHERE csno='".$locid."' AND ltype='com' AND status='A'";
			$res_acccomloc = mysql_query($sel_acccomloc,$db);
			$fetch_acccomloc = mysql_fetch_row($res_acccomloc);
		}
		else if($loctype == 'con') {
			$sel_acccont = "SELECT csno FROM staffacc_contact WHERE sno='".$locid."'";
			$res_acccont = mysql_query($sel_acccont,$db);
			$fetch_acccont = mysql_fetch_row($res_acccont);
			
			$sel_acccomloc = "SELECT sno FROM staffacc_location WHERE csno='".$fetch_acccont[0]."' AND ltype='com' AND status='A'";
			$res_acccomloc = mysql_query($sel_acccomloc,$db);
			$fetch_acccomloc = mysql_fetch_row($res_acccomloc);
		}
		else {
			$sel_accloc = "SELECT csno FROM staffacc_location WHERE sno='".$locid."' AND ltype='loc' AND status='A'";
			$res_accloc = mysql_query($sel_accloc,$db);
			$fetch_accloc = mysql_fetch_row($res_accloc);
			
			$sel_acccomloc = "SELECT sno FROM staffacc_location WHERE csno='".$fetch_accloc[0]."' AND ltype='com' AND status='A'";
			$res_acccomloc = mysql_query($sel_acccomloc,$db);
			$fetch_acccomloc = mysql_fetch_row($res_acccomloc);
		}
		
		$updcinfo_qry = "UPDATE staffacc_cinfo SET bill_address = '".$fetch_acccomloc[0]."' WHERE sno ='".$comconlocid."'";
		mysql_query($updcinfo_qry,$db);
	}

	//Function to get CORPORATION CODE
	function getCorporationCode($id) {

		GLOBAL $db;

		$corp_code	= 0;

		//Query to get corp_code
		$corp_query	= "SELECT
						a.corp_code
					FROM
						staffoppr_cinfo o, staffacc_cinfo a
					WHERE
						a.crm_comp=o.sno AND o.acc_comp=a.sno AND o.sno='".$id."'";

		$corp_result	= mysql_query($corp_query, $db);

		$corp_row	= mysql_fetch_row($corp_result);

		if (isset($corp_row[0]) && !empty($corp_row[0]))
		$corp_code	= $corp_row[0];

		return $corp_code;
	}

	//Function to create accounting customers from crm
	function CreateAccountingCustomers($ids,$consno,$username,$type)
	{
		global $maildb,$db,$deptId_jo;

		//Query to check wheather company already exists in accounting
		$sel_acccomp="SELECT acc_comp, deptid, zip FROM staffoppr_cinfo WHERE sno='".$ids."'";
		$res_acccomp=mysql_query($sel_acccomp,$db);
		$fetch_acccomp=mysql_fetch_row($res_acccomp);

		$cus_id=$fetch_acccomp[0];
		$deptid =$fetch_acccomp[1];

		if($fetch_acccomp[0]=='0' || trim($fetch_acccomp[0])=='')
		{
			$qry_acccus="insert into staffacc_list (nickname,approveuser,status,dauser,dadate,stime) values ('','".$username."','ACTIVE','','0000-00-00',NOW())";
			mysql_query($qry_acccus,$db);
			$last_list_id=mysql_insert_id($db);//Update username of the last inserted record
			$manusername="acc".$last_list_id;

			$upd_staffacc_list="UPDATE staffacc_list SET username='".$manusername."' WHERE sno='".$last_list_id."'";
			mysql_query($upd_staffacc_list,$db);

			if($ids!="0" && $ids!='')
				$qry_acccus="insert into staffacc_cinfo (username,ceo_president,cfo,sales_purchse_manager,cname,curl,address1,address2,city,state,country,zip,ctype,csize,nloction,nbyears,nemployee, com_revenue,federalid,bill_req,service_terms,compowner,compbrief,compsummary,compstatus,dress_code,tele_policy,smoke_policy, parking,park_rate,directions,culture,phone,fax,industry,keytech,department,siccode,csource,ticker,crm_comp,alternative_id,phone_extn,cust_classid,vend_classid,type,ts_layout_pref) select  '".$manusername."',ceo_president,cfo,sales_purchse_manager,cname,curl,address1,address2,city,state,country,zip,ctype,csize,nloction,nbyears, nemployee,com_revenue,federalid,bill_req,service_terms,compowner,compbrief,compsummary,compstatus,dress_code,tele_policy,smoke_policy,parking,park_rate,directions,culture,phone,fax,industry,keytech,department,siccode,csource,ticker,sno,alternative_id,phone_extn,'".DEFAULT_DEPT_CLASS."','".DEFAULT_DEPT_CLASS."','".$type."',ts_layout_pref from staffoppr_cinfo where sno='".$ids."'";
			else
				$qry_acccus="insert into staffacc_cinfo (username) values('".$manusername."')";
			mysql_query($qry_acccus,$db);
			$cus_id=mysql_insert_id($db);
			//Update the Accounting customer burden type details from companies table when placed
			if($ids!="0" && $ids!='') 
			{
				$loc_upd = "UPDATE staffacc_location t1, staffoppr_location t2 SET t1.burden_type = t2.burden_type, t1.bt_overwrite = t2.bt_overwrite, t1.bill_burden_type = t2.bill_burden_type, t1.bill_bt_overwrite = t2.bill_bt_overwrite WHERE t1.csno = '".$cus_id."' AND t2.csno = '".$ids."' AND t2.ltype = 'com' AND t1.ltype = 'com'";
				mysql_query($loc_upd,$db);
			}
                        
			setDefaultAccInfoAcc("customer",$db,$cus_id,$deptid);
			setDefaultEntityTaxes('Customer', $cus_id, $fetch_acccomp[2], $deptid);

			//if($type != "CV")
			//{
				$upd_opprcinfo="UPDATE staffoppr_cinfo SET acc_comp='".$cus_id."' WHERE sno='".$ids."'";
				mysql_query($upd_opprcinfo,$db);

				$qry_upd_acc="update staffacc_cinfo set customerid='".$cus_id."', muser='".$username."', mdate=NOW() where sno='".$cus_id."'";
				mysql_query($qry_upd_acc,$db);
			//}
		}
		else
		{
			$sel_acc_user="SELECT username FROM staffacc_cinfo WHERE sno='".$fetch_acccomp[0]."'";
			$res_sel_user=mysql_query($sel_acc_user,$db);
			$fetch_user=mysql_fetch_row($res_sel_user);
			$manusername=$fetch_user[0];
			$cus_id=$fetch_acccomp[0];
		}
		
		/////////////////// UDF migration to customers ///////////////////////////////////
		include_once('custom/custome_save.php');		
		$directEmpUdfObj = new userDefinedManuplations();
		$directEmpUdfObj->insertCrmCompToAcc(6, $cus_id, $ids);
		/////////////////////////////////////////////////////////////////////////////////
		
		$cli_id=0;
		if($consno>0)
		{
			$qry_acccon="insert into staffacc_contact (username,prefix,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,ctype,assignto,createdby,cdate,suffix,nickname,csno,cat_id,department,certifications,codes,keywords,messengerid,address1,address2,city,state,country,zipcode,sourcetype,crm_cont,wphone_extn,hphone_extn,other_extn,source_name,spouse_name,reportto_name,deptid,maincontact) select '".$manusername."',prefix,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,ctype,'',approveuser,NOW(),suffix,nickname,'".$cus_id."',cat_id,department,certifications,codes,keywords,messengerid,address1,address2,city,state,country,zipcode,sourcetype,sno,wphone_extn,hphone_extn,other_extn,source_name,spouse_name,reportto_name,deptid,maincontact from staffoppr_contact where sno='".$consno."'";
			$res_acccon=mysql_query($qry_acccon,$db);
			$cli_id=mysql_insert_id($db);

			$upd_opprcon="UPDATE staffoppr_contact SET acc_cont='".$cli_id."' WHERE sno='".$consno."'";
			mysql_query($upd_opprcon,$db);

			updateExistAccCompany($ids);
		}

		$qur_values=array($cus_id,$cli_id,$manusername);
		return($qur_values);
	}

	function updateExistAccCompany($Crm_Comp_Sno)
	{
		global $username,$maildb,$db,$deptId_jo;

		$sel_qry="select bill_contact,bill_address,state,country,acc_comp from staffoppr_cinfo where sno='".$Crm_Comp_Sno."' and acc_comp!='0'";
		$rel_qry=mysql_query($sel_qry,$db);
		$fth_qry=mysql_fetch_row($rel_qry);

		if($fth_qry[1]!="0" && $fth_qry[1]!="")
		{
			$selloc_qry="select csno from staffoppr_location where ltype='com' AND status='A' AND sno='".$fth_qry[1]."'";
			$relloc_qry=mysql_query($selloc_qry,$db);
			$fthloc_qry=mysql_fetch_row($relloc_qry);
			
			$retVal = insUpdAccComp($fthloc_qry[0]);
			$arrRetVal = explode("|",$retVal);
			$qry_acccus_id = $arrRetVal[1];
			$manusername = $arrRetVal[0];
		}
		else
		{
			$qry_acccus_id="0";
		}

		//Checking the state is available in state_codes table or not.
		$getstateVal = explode("|AKKENEXP|",getStateIdAbbr($fth_qry[2],$fth_qry[3]));

		$accStateId = $getstateVal[2];

		if($getstateVal[2] == 0)
			$accState = addslashes($getstateVal[0]);
		else
			$accState = addslashes($getstateVal[1]);

		if($fth_qry[0]!="0" && $fth_qry[0]!="")
		{
			//Query to select the source ,reportto and spouse from staffoppr_contact table
			$oppr_qry="SELECT source,spouse,reportto,acc_cont,state,country FROM staffoppr_contact WHERE sno='".$fth_qry[0]."'";
			$oppr_exe_qry=mysql_query($oppr_qry,$db);
			$oppr_fch_qry=mysql_fetch_row($oppr_exe_qry);

			//Checking the state is available in state_codes table or not.
			$getstateVal = explode("|AKKENEXP|",getStateIdAbbr($oppr_fch_qry[4],$oppr_fch_qry[5]));

			$accConStateId = $getstateVal[2];

			if($getstateVal[2] == 0)
				$accConState = addslashes($getstateVal[0]);
			else
				$accConState = addslashes($getstateVal[1]);

			if($oppr_fch_qry[3]=='0')
			{
				if($manusername=="")
				{
					$query="insert into staffacc_list (nickname, approveuser, status, dauser, dadate, stime, type, bpsaid)  values ('','".$username."','ACTIVE','','0000-00-00',NOW(),'".$type."','')";
					mysql_query($query,$db);
                                        $last_list_id=mysql_insert_id($db);//Update username of the last inserted record
                                        $manusername="acc".$last_list_id;

                                        $upd_staffacc_list="UPDATE staffacc_list SET username='".$manusername."' WHERE sno='".$last_list_id."'";
                                        mysql_query($upd_staffacc_list,$db);

					$qry_acccus="insert into staffacc_cinfo (username) values ('".$manusername."')";
					mysql_query($qry_acccus,$db);

					$qry_acccus_id=mysql_insert_id($db);
					$deptid = getOwnerDepartment();
					setDefaultAccInfoAcc("customer",$db,$qry_acccus_id,$deptid);
					setDefaultEntityTaxes('Customer', $qry_acccus_id, '', $deptid);

					$qry_upd_acc="update staffacc_cinfo set customerid='".$qry_acccus_id."', muser='".$username."', mdate=NOW() where sno='".$qry_acccus_id."'";
					mysql_query($qry_upd_acc,$db);
				}
			///////////////////// UDF migration to customers ///////////////////////////////////
			//include_once('custom/custome_save.php');		
			//$directEmpUdfObj = new userDefinedManuplations();
			//$directEmpUdfObj->insertCrmCompToAcc(6, $qry_acccus_id, $ids);
			///////////////////////////////////////////////////////////////////////////////////

				$qry_acccon="insert into staffacc_contact( username,prefix,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,ctype,assignto,createdby,cdate,suffix,nickname,csno,cat_id,department,certifications,codes,keywords,messengerid,address1,address2,city,stateid,country,zipcode,sourcetype,crm_cont,wphone_extn,hphone_extn,other_extn,source_name,spouse_name,reportto_name,state,deptid,maincontact) select  '".$manusername."',prefix,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,ctype,'',approveuser,NOW(),suffix,nickname,'".$qry_acccus_id."',cat_id,department,certifications,codes,keywords,messengerid,address1,address2,city,'".$accConStateId."',country,zipcode,sourcetype,sno,wphone_extn,hphone_extn,other_extn, source_name,spouse_name,reportto_name,'".$accConState."', deptid,maincontact  from staffoppr_contact where sno='".$fth_qry[0]."'";
				mysql_query($qry_acccon,$db);
				$qry_acccon_id=mysql_insert_id($db);

				$upd_acccon="update staffoppr_contact set acc_cont='".$qry_acccon_id."' where sno='".$fth_qry[0]."'";
				mysql_query($upd_acccon,$db); 

				$upd_opprcon="update staffacc_contact set crm_cont='".$fth_qry[0]."' where sno='".$qry_acccon_id."'";
				mysql_query($upd_opprcon,$db); 
			}
			else
			{
				$qry_acccon="update staffacc_contact a,staffoppr_contact b set a.nickname=b.nickname,a.prefix=b.prefix,a.fname=b.fname,a.mname=b.mname,a.lname=b.lname,a.email=b.email,a.cat_id=b.cat_id,a.wphone=b.wphone,a.hphone=b.hphone,a.mobile=b.mobile,a.fax=b.fax,a.other=b.other,a.ytitle=b.ytitle,a.ctype=b.ctype,a.department=b.department,a.certifications=b.certifications,a.codes=b.codes,a.keywords=b.keywords,a.messengerid=b.messengerid,a.suffix=b.suffix,a.address1=b.address1,a.address2=b.address2,a.city=b.city,a.stateid='".$accConStateId."',a.country=b.country,a.zipcode=b.zipcode, a.sourcetype=b.sourcetype,a.muser=b.muser,a.mdate=b.mdate,a.wphone_extn=b.wphone_extn,a.hphone_extn=b.hphone_extn,a.other_extn=b.other_extn, a.source_name=b.source_name,a.spouse_name=b.spouse_name,a.reportto_name=b.reportto_name,a.state='".$accConState."' where b.sno=a.crm_cont and b.sno='".$fth_qry[0]."' and a.sno='".$oppr_fch_qry[3]."'";
				mysql_query($qry_acccon,$db);
				$qry_acccon_id=$oppr_fch_qry[3];
			}
		}
		else
		{
			$qry_acccon_id="0";
		}

		$que="update staffacc_cinfo a,staffoppr_cinfo b set a.ceo_president=b.ceo_president,a.cfo=b.cfo,a.sales_purchse_manager=b.sales_purchse_manager,a.cname=b.cname,a.curl=b.curl,a.address1=b.address1,a.address2=b.address2,a.city=b.city,a.stateid='".$accStateId."',a.country=b.country,a.zip=b.zip,a.ctype=b.ctype,a.csize=b.csize,a.nloction=b.nloction,a.nbyears=b.nbyears,a.nemployee=b.nemployee,a.com_revenue=b.com_revenue,a.federalid=b.federalid,a.bill_contact='".$qry_acccon_id."',a.bill_address='".$qry_acccus_id."',a.bill_req=b.bill_req,a.service_terms=b.service_terms,a.compowner=b.compowner,a.compbrief=b.compbrief,a.compsummary=b.compsummary,a.compstatus=b.compstatus,a.dress_code=b.dress_code,a.tele_policy=b.tele_policy,a.smoke_policy=b.smoke_policy,a.parking=b.parking,a.park_rate=b.park_rate,a.directions=b.directions,a.culture=b.culture,a.phone=b.phone,a.fax=b.fax,a.industry=b.industry,a.keytech=b.keytech,a.department=b.department,a.siccode=b.siccode,a.csource=b.csource,a.ticker=b.ticker,a.alternative_id=b.alternative_id,a.phone_extn = b.phone_extn,a.state='".$accState."', a.muser='".$username."', a.mdate=NOW() where b.sno=a.crm_comp and b.acc_comp=a.sno and b.sno='".$Crm_Comp_Sno."'";
		mysql_query($que,$db);

		if($Crm_Comp_Sno != "" && $Crm_Comp_Sno != 0)
			getUpdateDefaultBilling($Crm_Comp_Sno, 2);

		return;
	}

	function insUpdAccComp($arg)
	{
		global $username,$maildb,$db,$deptId_jo;

		$fth_qry[1] = $arg;

		$oppr_qry="SELECT username,sno FROM staffacc_cinfo WHERE crm_comp='".$fth_qry[1]."'";
		$oppr_exe_qry=mysql_query($oppr_qry,$db);
		$oppr_fch_qry=mysql_fetch_row($oppr_exe_qry);

		//Getting state and country form crm company table.
		$sel_opprQry = "SELECT state,country, deptid, zip FROM staffoppr_cinfo WHERE sno='".$fth_qry[1]."'";
		$sel_opprRes = mysql_query($sel_opprQry,$db);
		$sel_opprRow = mysql_fetch_row($sel_opprRes);
		$deptid = $sel_opprRow[2];

		//Checking the state is available in state_codes table or not.
		$getstateVal = explode("|AKKENEXP|",getStateIdAbbr($sel_opprRow[0],$sel_opprRow[1]));

		$accStateId = $getstateVal[2];

		if($getstateVal[2] == 0)
			$accState = addslashes($getstateVal[0]);
		else
			$accState = addslashes($getstateVal[1]);

		if($oppr_fch_qry[0]=="")
		{
			$queGetClass = "SELECT department_accounts.classid FROM  department_accounts WHERE department_accounts.deptid = '".$deptId_jo."' AND department_accounts.status = 'ACTIVE'";
			$resClass =  mysql_query($queGetClass,$db);
			$rowClass = mysql_fetch_row($resClass);	

			$query="insert into staffacc_list (nickname, approveuser, status, dauser, dadate, stime, type, bpsaid)  values ('','".$username."','ACTIVE','','0000-00-00',NOW(),'".$type."','')";
			mysql_query($query,$db);
                        $last_list_id=mysql_insert_id($db);//Update username of the last inserted record
                        $manusername="acc".$last_list_id;

			$upd_staffacc_list="UPDATE staffacc_list SET username='".$manusername."' WHERE sno='".$last_list_id."'";
			mysql_query($upd_staffacc_list,$db);

			$qry_acccus="insert into staffacc_cinfo  (username,ceo_president,cfo,sales_purchse_manager,cname,curl,address1,address2,city,stateid,country,zip,ctype,csize,nloction, nbyears,nemployee,com_revenue,federalid,bill_req,service_terms,compowner,compbrief,compsummary, compstatus,dress_code,tele_policy,smoke_policy,parking,park_rate,directions,culture,phone,fax,industry,keytech,department,siccode,csource,ticker,crm_comp,alternative_id,phone_extn,state,cust_classid,vend_classid,ts_layout_pref) select  '".$manusername."', ceo_president , cfo , sales_purchse_manager , cname , curl , address1 , address2 , city , '".$accStateId."' , country , zip , ctype , csize , nloction , nbyears , nemployee , com_revenue , federalid , bill_req, service_terms, compowner,compbrief, compsummary,compstatus,dress_code, tele_policy,smoke_policy,parking, park_rate,directions,culture,phone,fax,industry,keytech,department,siccode,csource,ticker,sno,alternative_id,phone_extn,'".$accState."','".$rowClass[0]."','".$rowClass[0]."',ts_layout_pref from staffoppr_cinfo where sno='".$fth_qry[1]."'";
			mysql_query($qry_acccus,$db);
			$qry_acccus_id=mysql_insert_id($db);
			//Update the Accounting customer burden type details from companies table when placed
			$loc_upd = "UPDATE staffacc_location t1, staffoppr_location t2 SET t1.burden_type = t2.burden_type, t1.bt_overwrite = t2.bt_overwrite, t1.bill_burden_type = t2.bill_burden_type, t1.bill_bt_overwrite = t2.bill_bt_overwrite WHERE t1.csno = '".$qry_acccus_id."' AND t2.csno = '".$fth_qry[1]."' AND t2.ltype = 'com' AND t1.ltype = 'com'";
			mysql_query($loc_upd,$db);
			setDefaultAccInfoAcc("customer",$db,$qry_acccus_id,$deptid);
			setDefaultEntityTaxes('Customer', $qry_acccus_id, $sel_opprRow[3], $deptid);		

			$qry_upd_acc="update staffacc_cinfo set customerid='".$qry_acccus_id."', muser='".$username."', mdate=NOW() where sno='".$qry_acccus_id."'";
			mysql_query($qry_upd_acc,$db);

			$qry_upd_oppr="update staffoppr_cinfo set acc_comp='".$qry_acccus_id."' where sno='".$fth_qry[1]."'";
			mysql_query($qry_upd_oppr,$db);

			$retVal = $manusername. "|". $qry_acccus_id;
		}
		else
		{
			$que="update staffacc_cinfo a,staffoppr_cinfo b set a.ceo_president=b.ceo_president,a.cfo=b.cfo,a.sales_purchse_manager=b.sales_purchse_manager,a.cname=b.cname,a.curl=b.curl,a.address1=b.address1,a.address2=b.address2,a.city=b.city,a.stateid='".$accStateId."',a.country=b.country,a.zip=b.zip,a.ctype=b.ctype,a.csize=b.csize,a.nloction=b.nloction,a.nbyears=b.nbyears,a.nemployee=b.nemployee,a.com_revenue=b.com_revenue,a.federalid=b.federalid,a.bill_contact=a.bill_contact,a.bill_address=a.bill_address, a.bill_req=b.bill_req,a.service_terms=b.service_terms,a.compowner=b.compowner,a.compbrief=b.compbrief,a.compsummary=b.compsummary,a.compstatus=b.compstatus,a.dress_code=b.dress_code,a.tele_policy=b.tele_policy,a.smoke_policy=b.smoke_policy,a.parking=b.parking,a.park_rate=b.park_rate,a.directions=b.directions,a.culture=b.culture,a.phone=b.phone,a.fax=b.fax,a.industry=b.industry,a.keytech=b.keytech,a.department=b.department,a.siccode=b.siccode,a.csource=b.csource,a.ticker=b.ticker,a.alternative_id=b.alternative_id,a.phone_extn = b.phone_extn,a.state='".$accState."', a.muser='".$username."', a.mdate=NOW() where b.sno=a.crm_comp and b.acc_comp=a.sno and b.sno='".$fth_qry[1]."'";
			mysql_query($que,$db);
			$retVal = $oppr_fch_qry[0]."|".$oppr_fch_qry[1];
		}
		return $retVal;
	}

	function updateAssign($page152,$conusername,$candtype,$id_req,$empvalues,$commval,$ratetype,$paytype,$confirmToClose,$userid,$attention,$roleName,$roleOverWrite,$corp_code, $timeslotvalues, $burdenTypeDetails, $burdenItemDetails,$joindustryid,$schedule_display, $comm_bill_burden, $bill_burdenTypeDetails, $bill_burdenItemDetails, $roleEDisable,$shift_id=NULL,$shift_st_time,$shift_et_time,$shift_type,$candrn,$rate_on_submission,$licode,$daypay,$federal_payroll,$nonfederal_payroll,$contractid,$classification,$placement_timesheet_layout_preference='')
	{
		global $username,$maildb,$db,$Emp_Name,$emp_assig_sno,$hr_assig_sno,$assignment_name,$acceptJobtitle,$job_title,$common_proj,$loc_user,$companyuser,$maindb,$sub_seqnumber,$master_batch_id;

		if(SHIFT_SCHEDULING_ENABLED == 'Y')
		{
			global $objScheduleDetails;
		}
		
		$assg_pusername='';

		$SendString=$page152[50]."|".$page152[25]."|".$page152[22]."|".$page152[51]."|".$page152[52]."|".$page152[53]."|".$comm_bill_burden;
		if(RATE_CALCULATOR=='N'){
            $RetString=comm_calculate_Active($SendString);
            $RetString_Array=explode("|",$RetString);
            $page152[25]=$RetString_Array[1];
            $page152[22]=$RetString_Array[2];
            $page152[51]=$RetString_Array[3];
            $page152[52]=$RetString_Array[4];
            $page152[53]=$RetString_Array[5];	
        }
        $assignment_name=$page152[21];
		$job_title=$page152[2];

		if($assignment_name == '' && $job_title != '')
			$assignment_name = $job_title;

		$page152[21]=$assignment_name;

		$sdate=explode('-',$page152[11]);
		$start_date=$sdate[2]."-".$sdate[0]."-".$sdate[1];
		$edate=explode('-',$page152[15]);
		$end_date=$edate[2]."-".$edate[0]."-".$edate[1];
		$exp_date=explode('-',$page152[12]);
		$expdate=$exp_date[2]."-".$exp_date[0]."-".$exp_date[1];
		$hire_date=explode('-',$page152[16]);
		$hiredate=$hire_date[2]."-".$hire_date[0]."-".$hire_date[1];

		if($page152[44]=='Direct' || $page152[44]=='Internal Direct')
		{
			$payassign='N';
		}
		else
		{
			if($page152[28]=='Y')
				$payassign='N';
			else
				$payassign='Y';
		}

		if($page152[0]!='')
			$jobtype='Y';
		else
			$jobtype='N';

		
		if($page152[44]=='Internal Direct')
			$viewInCrm='N';
		else
			$viewInCrm='Y';

		if ($shift_id == "0" || $shift_id =="") {
			$shift_type="";
		}
		if(SS_ENABLED == 'Y'){

			if($daypay=='Yes' || $daypay=='on'){
	 			$daypay = 'Yes';
	 		}
	 		else{
	 			$daypay = 'No';
	 		}

	 		if($federal_payroll =='Yes' || $federal_payroll=='on'){
	 			$federal_payroll = 'Yes';
	 		}else{
	 			$federal_payroll = 'No';
	 		}

	 		if($nonfederal_payroll =='Yes' || $nonfederal_payroll=='on'){
	 			$nonfederal_payroll = 'Yes';
	 		}else{
	 			$nonfederal_payroll = 'No';
		
	 		}
		}
		$quevalue="'','".$master_batch_id."','".$id_req."','".$page152[0]."','".$page152[1]."','".addslashes($page152[2])."','".addslashes($page152[3])."','".$page152[4]."','".$page152[5]."','".$page152[6]."','".$page152[7]."','".$page152[8]."','".$page152[9]."','".$page152[10]."','".$page152[11]."','".$expdate."','".$page152[13]."','".$page152[14]."','".$page152[15]."','".$hiredate."','".addslashes($page152[17])."','".$page152[18]."','".$page152[19]."','".$page152[20]."','".addslashes($page152[21])."','".$page152[22]."','".$page152[23]."','".$page152[24]."','".$page152[25]."','".$page152[26]."','".$page152[27]."','".$page152[28]."','".$page152[29]."','".$page152[30]."','".$page152[31]."','".$page152[32]."','".$page152[33]."','".$page152[34]."','".$page152[35]."','".addslashes($page152[36])."','".$page152[37]."','".$page152[38]."','".addslashes($page152[39])."','".addslashes($page152[40])."','".$page152[41]."','".addslashes($page152[42])."','".addslashes($page152[43])."','',NOW(),'".addslashes($page152[45])."','".addslashes($page152[47])."','".addslashes($page152[48])."','".$page152[50]."','".$page152[51]."','".$page152[52]."','".$page152[53]."','".$page152[54]."','".$page152[55]."','".$page152[56]."','".$page152[57]."','".$username."',now(),'".$page152[58]."','".$page152[59]."','".$page152[60]."','".$page152[61]."','".$page152[62]."','".$page152[63]."','".addslashes($page152[64])."','".$loc_user."','".$page152[65]."','".$page152[66]."','".$page152[67]."','".$page152[68]."','".$page152[69]."','".$page152[70]."','".addslashes($page152[71])."','".addslashes($page152[72])."','".$page152[73]."','OP','".$username."',NOW(),NOW(), '".$page152[74]."','".$page152[75]."','".$page152[76]."','".$page152[77]."','".$page152[78]."','".$page152[79]."','".$page152[80]."','".$page152[81]."','".$page152[83]."','".$page152[84]."','".addslashes($attention)."','".$corp_code."','".$joindustryid."','".$schedule_display."','".$comm_bill_burden."','".$shift_id."','".$shift_st_time."','".$shift_et_time."','pending','".$page152[88]."','".$shift_type."','".$rate_on_submission."','".$daypay."','".$licode."','".$federal_payroll."','".$nonfederal_payroll."','".$contractid."','".$classification."','".$placement_timesheet_layout_preference."'";
		 $que="INSERT INTO bulk_placement_jobs(sno,batch_id,posid,jotype,catid,postitle,refcode,posstatus,vendor,contact,client,manager,candidate,endclient,s_date,exp_edate,posworkhr,timesheet,e_date,hired_date,reason,rate,rateperiod,rateper,project,bamount,bperiod,bcurrency,pamount,pperiod,pcurrency,emp_prate,otrate,ot_period,ot_currency,placement_fee,placement_curr,bill_contact,bill_address,wcomp_code,imethod,tsapp,bill_req,service_terms,hire_req,addinfo,notes,commision,rtime,avg_interview,iterms,pterms,calctype,burden,margin,markup,prateopen_amt,brateopen_amt,prateopen,brateopen,owner,cdate,otprate_amt,otprate_period,otprate_curr,otbrate_amt,otbrate_period,otbrate_curr,payrollpid,offlocation,double_prate_amt,double_prate_period,double_prate_curr,double_brate_amt,double_brate_period, double_brate_curr,po_num,department,job_loc_tax,jtype,muser,mdate,date_placed,diem_lodging,diem_mie,diem_total,diem_period,diem_currency,diem_billable,diem_taxable,diem_billrate,classid,deptid,attention,corp_code, industryid,schedule_display,bill_burden,shiftid,starthour,endhour,ustatus,worksite_code,shift_type,rate_on_submission,daypay,licode,federal_payroll,nonfederal_payroll,contractid,classification,ts_layout_pref) VALUES(".$quevalue.")"; 

		mysql_query($que,$db);
		$ass_id=mysql_insert_id($db);
                    
		if($ass_id != 0)
		{
			saveBurdenDetails($burdenTypeDetails,$burdenItemDetails,'bulk_place_burden_details','bulk_place_sno','insert',$ass_id);
			saveBillBurdenDetails($bill_burdenTypeDetails,$bill_burdenItemDetails,'bulk_place_burden_details','bulk_place_sno','insert',$ass_id);
			if(SHIFT_SCHEDULING_ENABLED == 'Y' && $shift_type == "perdiem")
			{
				//inserting the time slots data for perdiem schedule management for placement job
				insPerdiemPlacementTimeSlots($db,'bulk_place_perdiem_shift_sch',$ass_id,$id_req,$userid,$assg_pusername);
			}
		}

		/////////////////// UDF migration to customers ///////////////////////////////////
		include_once('custom/custome_save.php');		
		$directEmpUdfObj = new userDefinedManuplations();
		$directEmpUdfObj->insertUserDefinedData($ass_id, 11);

		/////////////////////////////////////////////////////////////////////////////////
		
		$insid=$ass_id;
		$ccid=$ass_id;
		$appno = $ass_id;
		$emptypeVal="";
		if($page152[44] != "Direct")
		{
			$emptypeVal=$page152[49];
			if($page152[44] == "Temp/Contract to Direct")
				$emptypeVal=getManageSno("Temp/Contract","jotype");
		}

		if($page152[84] != '' && $page152[84] != 0)
		{
			$deptID = $page152[84];
		}
		else
		{
			$queryDept = "SELECT sno FROM department WHERE deflt = 'Y'";
			$resDept = mysql_query($queryDept, $db);
			$rowDept = mysql_fetch_assoc($resDept);	
			if($rowDept['sno'] != '')
				$deptID = $rowDept['sno'];
			else
				$deptID = 1;
		}

		$getDepLoc = "SELECT loc_id FROM department WHERE status = 'ACTIVE' AND sno = '".$deptID."'";
		$resDepLoc = mysql_query($getDepLoc,$db);
		$rowDepLoc = mysql_fetch_array($resDepLoc);
		$locationID = $rowDepLoc['loc_id'];

		if($empvalues!='')
			$emp_val=explode(',',$empvalues);

		for($k=0;$k<count($emp_val);$k++)
		{
			if($emp_val[$k]!='noval')
			{
				$commvalues=explode("|",$emp_val[$k]);
				$emp_values[$k]=$commvalues[0];
			}
		}

		$i=0;
		$commissionVisitedArray=array();
		$commIndex=0;
		$commKey="";
		$arrayCount=array();
		if($shift_id=="")
			$shift_id_for_comm = 0;
		else
			$shift_id_for_comm = $shift_id;
		foreach($emp_values as $key=>$keyValue)
		{
			$commKey=$keyValue;
			if(in_array($keyValue,$commissionVisitedArray))
			{
				array_push($commissionVisitedArray,$keyValue);
				$arrayCount=array_count_values($commissionVisitedArray);
				$dynemp.$keyValue=$arrayCount[$keyValue]-1;
			}	
			else
			{
				array_push($commissionVisitedArray,$keyValue);
				$dynemp.$keyValue=0;
			}

			$indexVal		= $dynemp.$keyValue;
			$roleOverwrite		= $roleOverWrite[$commKey][$indexVal];
			$enableUserInput	= $roleEDisable[$commKey][$indexVal];

			//Inserting of commission if contact is CRM contact
			if(substr($commKey,0,10) == 'staffoppr-')
			{
				$convalue=explode('staffoppr-',$commKey);
				$con_sel="SELECT acc_cont,csno,sno FROM staffoppr_contact WHERE sno='".$convalue[1]."'";
				$res_con=mysql_query($con_sel,$db);
				$fetch_con=mysql_fetch_row($res_con);
				if($fetch_con[0]=='0')
				{
					$values=CreateAccountingCustomers($fetch_con[1],$fetch_con[2],$username,'CUST');
					$acccon=$values[1];
				}
				else
				{
					$sel_con="SELECT sno FROM staffacc_contact WHERE sno='".$fetch_con[0]."' and staffacc_contact.username!=''";
					$res_con=mysql_query($sel_con,$db);
					$fetch_con=mysql_fetch_row($res_con);
					$acccon=$fetch_con[0];
				}

				if(!empty($roleName[$commKey][$indexVal]))
				{
					$comm_insert	= "INSERT INTO bulk_place_assign_commission(sno, username, bulk_place_sno, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput,shift_id) VALUES('', '".$username."', '".$ass_id."', '".$acccon."', 'A', '".$commval[$commKey][$indexVal]."', '".$paytype[$commKey][$indexVal]."', '".$ratetype[$commKey][$indexVal]."', '".$roleName[$commKey][$indexVal]."', '".$roleOverwrite."', '".$enableUserInput."','".$shift_id_for_comm."')";
					mysql_query($comm_insert, $db);
				}
			}
			else if(substr($commKey,0,3) == 'emp')
			{
				$empno = explode("emp",$commKey);

				if(!empty($roleName[$commKey][$indexVal]))
				{
					$comm_insert	= "INSERT INTO bulk_place_assign_commission(sno, username, bulk_place_sno, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput,shift_id) VALUES('', '".$username."', '".$ass_id."', '".$empno[1]."', 'E', '".$commval[$commKey][$indexVal]."', '".$paytype[$commKey][$indexVal]."', '".$ratetype[$commKey][$indexVal]."', '".$roleName[$commKey][$indexVal]."', '".$roleOverwrite."', '".$enableUserInput."','".$shift_id_for_comm."')";
					mysql_query($comm_insert, $db);

				}
			}
			else
			{
				$acccon	= $commKey;

				if(!empty($roleName[$commKey][$indexVal]))
				{
					$comm_insert	= "INSERT INTO bulk_place_assign_commission(sno, username, bulk_place_sno, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput,shift_id) VALUES('', '".$username."', '".$ass_id."', '".$acccon."', 'A', '".$commval[$commKey][$indexVal]."', '".$paytype[$commKey][$indexVal]."', '".$ratetype[$commKey][$indexVal]."', '".$roleName[$commKey][$indexVal]."', '".$roleOverwrite."', '".$enableUserInput."','".$shift_id_for_comm."')";
					mysql_query($comm_insert, $db);
				}
			}
			$i++;
		}
		
		return($appno);
	}	

	//function to insert perdiem time schedule management data from placement
	function insPerdiemPlacementTimeSlots($db,$table, $psno,$id_req,$cand_id,$pusername)
	{
		global $username, $objSchSchedules, $objPerdiemScheduleDetails, $shift_snos,$candrn,$nextPlacePositon,$perdiemPlacementFrm,$sm_sel_perdiem_shifts;

		$timeSlotsDataArr = array();
		$timeSlotsDateArr = array();

		$timeSlotsDateArr = $_SESSION['editPlacementPerdiemShiftPagination'.$candrn];

		$placementInsShiftFullAry = array();
		$splitShiftInsertDoneAry = array();

		$placementInsShiftFullAry = $_SESSION['editPlacementPerdiemShiftSch'.$candrn];

		$maxDate = max(array_values($timeSlotsDateArr));
		$maxDateStr =date("Y-m-d",strtotime($maxDate));
		
		
        if($shift_snos!='' || !empty($shift_snos))
        {
			for($s=1; $s<= count($timeSlotsDateArr); $s++) 
			{ 
				$selDate = $timeSlotsDateArr[$s];			
				$timeSlotsDataArr = $_SESSION['editPlacementPerdiemShiftSch'.$candrn][$selDate];
				
				foreach ($timeSlotsDataArr as $shiftkey => $ShiftValue) 
				{
			
					for ($l=0; $l < count($ShiftValue); $l++)
					{	
						$perdiemSno = $ShiftValue[$l]['perdiemSno'];
						$shiftStDate = date("Y-m-d",strtotime($ShiftValue[$l]['startDate']));
						$avail_shiftStDate = date("m/d/Y",strtotime($ShiftValue[$l]['startDate']));
						$shiftStTime = getMintoTime($ShiftValue[$l]['startTime']);
						$shiftEdDate = date("Y-m-d",strtotime($ShiftValue[$l]['endDate']));
						$shiftEdTime = getMintoTime($ShiftValue[$l]['endTime']);
						$splitShift  = $ShiftValue[$l]['splitShift'];
						$noOfShift   = $ShiftValue[$l]['noOfShifts'];
						$shiftSno    = $ShiftValue[$l]['shiftSno'];	
						$parentSplitShift = $ShiftValue[$l]['parentSplitShift'];
						$childSplitShift = $ShiftValue[$l]['childSplitShift'];
						$displayDateForbulkPlace = $ShiftValue[$l]['displayDateForbulkPlace'];
						$recNo = 0;
						$slotGrpNo = 0;
						$shiftNumPos = 0;
						$shiftStatus = 'busy';
						
						list($shiftName,$shiftColor) = explode("|",$shiftSetupDetails);
						$nextPlacePositon = $objPerdiemScheduleDetails->getNextPositionOnPlacement($id_req , $shiftSno);
						$perdiem_timeslotvalues .= $avail_shiftStDate."^".$ShiftValue[$l]['startTime']."^".$ShiftValue[$l]['endTime']."^".$recNo."^".$slotGrpNo."^".$shiftStatus."^".$shiftName."^".$shiftColor."^".$shiftNumPos."|";

						if($splitShift == "Y")
						{

							if (!in_array($parentSplitShift,$splitShiftInsertDoneAry) && !in_array($childSplitShift,$splitShiftInsertDoneAry)) {
								if (!in_array($parentSplitShift,$splitShiftInsertDoneAry)){
									array_push($splitShiftInsertDoneAry, $parentSplitShift);
								}
								if (!in_array($childSplitShift,$splitShiftInsertDoneAry)){
									array_push($splitShiftInsertDoneAry, $childSplitShift);
								}
								$parDiv = explode("_",$parentSplitShift);
								$childDiv = explode("_",$childSplitShift);

								$parSdate = $parDiv[0];
								$parShiftSno = $parDiv[1];
								$parIndexNo = $parDiv[2];

								$childSdate = $childDiv[0];
								$childShiftSno = $childDiv[1];
								$childIndexNo = $childDiv[2];

								$shiftStDate = $placementInsShiftFullAry[$parSdate][$parShiftSno][$parIndexNo]['startDate'];
								$shiftStDate = date("Y-m-d",strtotime($shiftStDate));
								$shiftStTime = $placementInsShiftFullAry[$parSdate][$parShiftSno][$parIndexNo]['startTime'];
								$shiftStTime = getMintoTime($shiftStTime);
								
								$shiftEdDate = $placementInsShiftFullAry[$childSdate][$childShiftSno][$childIndexNo]['startDate'];
								$shiftEdDate = date("Y-m-d",strtotime($shiftEdDate));
								$shiftEdTime = $placementInsShiftFullAry[$childSdate][$childShiftSno][$childIndexNo]['endTime'];
								$shiftEdTime = getMintoTime($shiftEdTime);
						
								if($table=='bulk_place_perdiem_shift_sch' && $displayDateForbulkPlace == "Y")
								{
									$place_shifts_que = "INSERT INTO bulk_place_perdiem_shift_sch (`sno`, `bulk_place_sno`,`jo_sch_sno`,`posid`,`no_of_shift_position`, `shift_startdate`,`shift_enddate`,`shift_starttime`,`shift_endtime`,`split_shift`,`shift_id`,`cdate`,`cuser`,`mdate`,`muser`) 
										VALUES('','".$psno."','".$perdiemSno."','".$id_req."','".$nextPlacePositon."','".$shiftStDate."','".$shiftEdDate."','".$shiftStTime."','".$shiftEdTime."','".$splitShift."','".$shiftSno."',NOW(),'".$username."',NOW(),'".$username."')";
									$result = mysql_query($place_shifts_que,$db);
								}
							}
						}
						else
						{
							if($table=='bulk_place_perdiem_shift_sch' && $displayDateForbulkPlace == "Y")
							{
								$place_shifts_que = "INSERT INTO bulk_place_perdiem_shift_sch (`sno`, `bulk_place_sno`,`jo_sch_sno`,`posid`,`no_of_shift_position`, `shift_startdate`,`shift_enddate`,`shift_starttime`,`shift_endtime`,`split_shift`,`shift_id`,`cdate`,`cuser`,`mdate`,`muser`) 
									VALUES('','".$psno."','".$perdiemSno."','".$id_req."','".$nextPlacePositon."','".$shiftStDate."','".$shiftEdDate."','".$shiftStTime."','".$shiftEdTime."','".$splitShift."','".$shiftSno."',NOW(),'".$username."',NOW(),'".$username."')";
								mysql_query($place_shifts_que,$db);
							}
						}	
					}
				}
			}

			if($perdiemPlacementFrm != "perdiemParticalPlace")
			{
				if (!empty($sm_sel_perdiem_shifts)) {

					$perdiemSelShiftArray = explode(",",$sm_sel_perdiem_shifts);
					foreach ($perdiemSelShiftArray as $key => $perdiemSelShiftSno) {

						$selectSplit = "SELECT DISTINCT(split_shift) FROM jo_perdiem_shift_sch WHERE posid='".$id_req."' AND shift_id='".$perdiemSelShiftSno."' ";
						$resultSplit = mysql_query($selectSplit,$db);

						$rowSplit = mysql_fetch_row($resultSplit);

						$splitShiftCheck = $rowSplit[0];
						if ($splitShiftCheck == "Y") {
							$greaterthenEqualTo = "=";
						}else{
							$greaterthenEqualTo = "";
						}

						$select = "SELECT COUNT(1) AS opencount, q.sno,q.no_of_shift_position,q.shift_startdate 
						FROM (SELECT DISTINCT jpss.sno,jscd.shift_position_no,IF((jpss.no_of_shift_position=1)&&(jscd.shift_position_no IS NULL),2,jpss.no_of_shift_position ) AS no_of_shift_position,jpss.shift_startdate
						FROM jo_perdiem_shift_sch AS jpss 
						LEFT JOIN jo_perdiem_shift_sch_detail AS jscd ON (jpss.posid = jscd.posid AND jpss.sno = jscd.jo_sch_sno) 
						WHERE jpss.posid = '".$id_req."' AND jpss.shift_startdate >".$greaterthenEqualTo." '".$maxDateStr."' AND jpss.shift_id='".$perdiemSelShiftSno."'
						GROUP BY jpss.shift_startdate ,jscd.shift_position_no 
						ORDER BY jscd.filled_startdate) AS q 
						GROUP BY q.shift_startdate 
						HAVING opencount < q.no_of_shift_position ORDER BY q.shift_startdate ASC";

						$result = mysql_query($select,$db);
						if (mysql_num_rows($result)>0) {

							if($table=='bulk_place_perdiem_shift_sch')
							{
								while ($row = mysql_fetch_array($result)) {
									$perdiemSno = $row['sno'];
									$nextPositonNo = '';
									$nextPositonNo = findPerdiemOpenPosNo($perdiemSno);
								
									$insPastDatesJobSql = "INSERT INTO ".$table." (`sno`, `bulk_place_sno`,`jo_sch_sno`,`posid`,`no_of_shift_position`, `shift_startdate`,`shift_enddate`,`shift_starttime`,`shift_endtime`,`split_shift`,`shift_id`,`cdate`,`cuser`,`mdate`,`muser`) SELECT '', '".$psno."',`sno`,`posid`,'".$nextPositonNo."',`shift_startdate`,`shift_enddate`,`shift_starttime`,`shift_endtime`,`split_shift`,`shift_id`, now(), '".$username."', now(),'".$username."'
									FROM jo_perdiem_shift_sch WHERE sno = '".$perdiemSno."' ";
									mysql_query($insPastDatesJobSql,$db);
								}
							}
						}	
					}
				}
			}

			//updtExpSEdatesPerdiemShifts($db,$table,$pusername,$psno);
   	 	}

	}

	
	function findPerdiemOpenPosNo($perdiemSno='')
	{
		global $db,$username,$nextPlacePositon;

		$openPosNo = $nextPlacePositon;
		$select = "SELECT MIN(open_pos_sno) AS open_pos_num FROM perdiem_open_pos WHERE open_pos_sno NOT IN (SELECT shift_position_no FROM jo_perdiem_shift_sch_detail WHERE jo_sch_sno='".$perdiemSno."') LIMIT 0,1";
		$result = mysql_query($select,$db);
		if (mysql_num_rows($result)>0) {
			$row = mysql_fetch_row($result);
			$openPosNo = $row[0];
		}
		return $openPosNo;
	}

	//function to insert perdiem time schedule management data from placement
	function generateTimeSlotsFromSession($cand_id,$posid)
	{
		global $db,$username, $objSchSchedules, $objPerdiemScheduleDetails, $shift_snos, $candrn;
		$oldShiftStrAry = array();
		$perdiem_timeslotvalues = "";

		$timeSlotsDataArr = array();
		$timeSlotsDateArr = array();
		$splitShiftInsertDoneAry = array();
		$placementjobInsShiftFullAry = array();

		$timeSlotsDateArr = $_SESSION['editPlacementPerdiemShiftPagination'.$candrn];
		$placementjobInsShiftFullAry = $_SESSION['editPlacementPerdiemShiftSch'.$candrn];
		//insert the past date if available for the job order into respective tables 
		$maxDate = max(array_values($timeSlotsDateArr));
		$maxDateStr =date("Y-m-d",strtotime($maxDate));
		$max_event_group_no_of_cand = "SELECT MAX(event_group_no) AS max_event_group_no FROM candidate_sm_timeslots WHERE username='".$cand_id."'";
		$max_event_group_no_of_cand_result = mysql_query($max_event_group_no_of_cand,$db);
		$max_event_group_no = mysql_fetch_assoc($max_event_group_no_of_cand_result);
		$tempEventGroupNo 	= $max_event_group_no["max_event_group_no"]+1;

        if($shift_snos!='' || !empty($shift_snos))
        {

			for($s=1; $s<= count($timeSlotsDateArr); $s++) 
			{ 
				$selDate = $timeSlotsDateArr[$s];			
				$timeSlotsDataArr = $_SESSION['editPlacementPerdiemShiftSch'.$candrn][$selDate];
				
				foreach ($timeSlotsDataArr as $shiftkey => $ShiftValue) 
				{
			
					for ($l=0; $l < count($ShiftValue); $l++)
					{	

						$shiftStDate = date("Y-m-d",strtotime($ShiftValue[$l]['startDate']));
						$avail_shiftStDate = date("m/d/Y",strtotime($ShiftValue[$l]['startDate']));
						$shiftStTime = getMintoTime($ShiftValue[$l]['startTime']);
						$shiftEdDate = date("Y-m-d",strtotime($ShiftValue[$l]['endDate']));
						$avail_shiftEdDate = date("m/d/Y",strtotime($ShiftValue[$l]['endDate']));
						$shiftEdTime = getMintoTime($ShiftValue[$l]['endTime']);
						$splitShift  = $ShiftValue[$l]['splitShift'];
						$noOfShift   = $ShiftValue[$l]['noOfShifts'];
						$shiftSno    = $ShiftValue[$l]['shiftSno'];	
						$parentSplitShift = $ShiftValue[$l]['parentSplitShift'];
                    	$childSplitShift = $ShiftValue[$l]['childSplitShift'];

						$recNo = 1;
						$slotGrpNo = 0;
						$shiftNumPos = 0;
						$shiftStatus = 'available';
						$oldShiftStr ='';
						$shiftSetupDetails = $objSchSchedules->getShiftNameColorBySno($shiftSno);

						list($shiftName,$shiftColor) = explode("|",$shiftSetupDetails);

						//$perdiem_timeslotvalues .= $avail_shiftStDate."^".$ShiftValue[$l]['startTime']."^".$ShiftValue[$l]['endTime']."^".$recNo."^".$slotGrpNo."^".$shiftStatus."^".$shiftSno."^".$noOfShift."|";
						//02/11/2019^630^1170^0^0^available^2^10^
						if ($splitShift == "Y") 
						{	if (!in_array($parentSplitShift,$splitShiftInsertDoneAry) && !in_array($childSplitShift,$splitShiftInsertDoneAry)) {
								if (!in_array($parentSplitShift,$splitShiftInsertDoneAry)){
									array_push($splitShiftInsertDoneAry, $parentSplitShift);
								}
								if (!in_array($childSplitShift,$splitShiftInsertDoneAry)){
									array_push($splitShiftInsertDoneAry, $childSplitShift);
								}
								$parDiv = explode("_",$parentSplitShift);
								$childDiv = explode("_",$childSplitShift);

								$parSdate = $parDiv[0];
								$parShiftSno = $parDiv[1];
								$parIndexNo = $parDiv[2];

								$childSdate = $childDiv[0];
								$childShiftSno = $childDiv[1];
								$childIndexNo = $childDiv[2];

								$shiftStDate = $placementjobInsShiftFullAry[$parSdate][$parShiftSno][$parIndexNo]['startDate'];
								$shift_StDate = date("m/d/Y",strtotime($shiftStDate));
								$shiftStTime = $placementjobInsShiftFullAry[$parSdate][$parShiftSno][$parIndexNo]['startTime'];								
								
								$shiftEdDate = $placementjobInsShiftFullAry[$childSdate][$childShiftSno][$childIndexNo]['startDate'];
								$shift_EdDate = date("m/d/Y",strtotime($shiftEdDate));
								$shiftEdTime = $placementjobInsShiftFullAry[$childSdate][$childShiftSno][$childIndexNo]['endTime'];

								$oldShiftStr = $shift_StDate."^".$shiftStTime.'^1439^'.$recNo.'^'.$tempEventGroupNo."^".$shiftStatus."^".$shiftSno."^".$noOfShift."^";
								array_push($oldShiftStrAry, $oldShiftStr);

								$oldShiftStr =$shift_EdDate.'^0^'.$shiftEdTime.'^'.$recNo.'^'.$tempEventGroupNo."^".$shiftStatus."^".$shiftSno."^".$noOfShift."^";
								array_push($oldShiftStrAry, $oldShiftStr);
								$tempEventGroupNo = $tempEventGroupNo+1;
							}							
						}else{
							//$oldShiftStr =$shiftStDate.'^'.$shiftStTime.'^'.$shiftEdTime.'^0^0^'.$shiftName.'^'.$shiftColor.'^'.$noOfShift;
							$oldShiftStr = $avail_shiftStDate."^".$ShiftValue[$l]['startTime']."^".$ShiftValue[$l]['endTime']."^".$recNo."^".$slotGrpNo."^".$shiftStatus."^".$shiftSno."^".$noOfShift."^";
							array_push($oldShiftStrAry, $oldShiftStr);
						}
					}
				}
			}

			if($perdiemPlacementFrm != "perdiemParticalPlace")
			{
				if ($splitShift == "Y") {
					$greaterthenEqualTo = "=";
				}else{
					$greaterthenEqualTo = "";
				}

				$selAllTimeslotsQry = "SELECT jo.shift_startdate AS startDate,jo.shift_starttime AS startTime,
					jo.shift_enddate AS endDate,jo.shift_endtime AS endTime,jo.split_shift AS splitShift,
					jo.no_of_shift_position AS noOfShifts,jo.shift_id AS shiftSno,ss.shiftname AS shiftName,
					ss.shiftcolor AS shiftColor
			 		FROM jo_perdiem_shift_sch jo 
			 		LEFT JOIN shift_setup ss ON (ss.sno = jo.shift_id)
			 		WHERE jo.shift_startdate >".$greaterthenEqualTo." '".$maxDateStr."' AND jo.posid='".$posid."' AND jo.shift_id='".$shift_snos."'";

			 	$selAllTimeslotsRes = mysql_query($selAllTimeslotsQry,$db);

			 	if (mysql_num_rows($selAllTimeslotsRes)>0) {
			 		
					while ($selAllTimeslotsRow = mysql_fetch_array($selAllTimeslotsRes)) {
						
						$shiftStDate 	= $selAllTimeslotsRow['startDate'];
						$shiftStDateVal    = date('m/d/Y',strtotime($shiftStDate));						
						$shiftEdDate 	= $selAllTimeslotsRow['endDate'];
						$shiftEdDateVal    = date('m/d/Y',strtotime($shiftEdDate));
						$shiftStTime    = getTimeToMin($selAllTimeslotsRow['startTime']);
						$shiftEdTime    = getTimeToMin($selAllTimeslotsRow['endTime']);
						$splitShift 	= $selAllTimeslotsRow['splitShift'];
						$shiftSno 		= $selAllTimeslotsRow['shiftSno'];
						$noOfShift 		= $selAllTimeslotsRow['noOfShifts'];
						$shiftName 		= $selAllTimeslotsRow['shiftName'];
						$shiftColor 	= $selAllTimeslotsRow['shiftColor'];
						$shiftStatus 	= "available";

						$recNo = 0;
						$slotGrpNo = 0;
						$shiftNumPos = 0;
						$oldShiftStrVal='';
						if ($splitShift == "Y"){

							$oldShiftStrVal = $shiftStDateVal."^".$shiftStTime."^1439^".$recNo."^".$tempEventGroupNo."^".$shiftStatus."^".$shiftSno."^".$noOfShift."^";
							array_push($oldShiftStrAry, $oldShiftStrVal);

							$oldShiftStrVal = $shiftEdDateVal."^0^".$shiftEdTime."^".$recNo."^".$tempEventGroupNo."^".$shiftStatus."^".$shiftSno."^".$noOfShift."^";
							array_push($oldShiftStrAry, $oldShiftStrVal);

							$tempEventGroupNo = $tempEventGroupNo+1;
						}else{
							$oldShiftStrVal = $shiftStDateVal."^".$shiftStTime."^".$shiftEdTime."^".$recNo."^".$slotGrpNo."^".$shiftStatus."^".$shiftSno."^".$noOfShift."^";
							array_push($oldShiftStrAry, $oldShiftStrVal);
						}	

						
					}
				}
			}
			if(count($oldShiftStrAry)>0)
			{
				$perdiem_timeslotvalues =  implode("|", $oldShiftStrAry);
			}
   	 	}
   	 	return $perdiem_timeslotvalues;
	}

	function getMintoTime($sentMinutesVal='')
	{
		$hoursVal		= sprintf("%02d",floor($sentMinutesVal/60));
		$minVal			= sprintf("%02d",round($sentMinutesVal%60));

		return $hoursVal.':'.$minVal.':00';
	}
	function getTimeToMin($hours_24='')
	{
		$timeAry = explode(":", $hours_24);
		$timehrs = (int)$timeAry[0]*60;
		$minStr = (int)$timehrs+(int)$timeAry[1];

		return $minStr;
	}

	//function to insert time schedule management data from placement
	function insPlacementTimeSlots($db,$table_name,$psno,$timeSlotsData,$id_req,$candidate='')
	{
		global $username, $objSchSchedules, $shift_snos;

		$sm_plcmnt_array=explode("|",$timeSlotsData);
		$sm_req_cnt	= count($sm_plcmnt_array);
		
        if($shift_snos!='' || !empty($shift_snos))
        {
			//insert the past date if available for the job order into respective tables
			$previousDate = date("Y-m-d",strtotime("-2 months",strtotime(date("Y-m-d"))));

			if($table_name=='hrconjob_sm_timeslots')
			{
				$insPastDatesJobSql = "INSERT INTO ".$table_name."
				SELECT '', '".$psno."', shift_date, shift_starttime, shift_endtime,event_type, event_no, event_group_no, shift_status, sm_sno, no_of_positions,'','".$username."', now(), '".$username."', now()
				FROM posdesc_sm_timeslots WHERE pid = '".$id_req."' AND shift_date > '".$previousDate."' AND sm_sno IN (".$shift_snos.")";
			}
			else
			{
				$insPastDatesJobSql = "INSERT INTO ".$table_name."
				SELECT '', '".$psno."', shift_date, shift_starttime, shift_endtime,event_type, event_no, event_group_no, shift_status, sm_sno, no_of_positions, '".$username."', now(), '".$username."', now()
				FROM posdesc_sm_timeslots WHERE pid = '".$id_req."' AND shift_date > '".$previousDate."' AND sm_sno IN (".$shift_snos.")";
			}				
			mysql_query($insPastDatesJobSql,$db);
        }
		
		//forming array to insert based on single/recurrence 
		$insertArrayData = array();
		for($i=0;$i<$sm_req_cnt;$i++)
		{
			$smVal = trim($sm_plcmnt_array[$i]);
			if($smVal != "")
			{
				//splitting the date, from time and to time
				$smValExp = explode("^",$smVal);
				$smAvailDate = date("Y-m-d",strtotime($smValExp[0]));

				//as we get minutes only for from and to. so add to the date gives us the from and to date
				$smAvailFromDate	= $objSchSchedules->getDateTimeFrmMinutes($smAvailDate, $smValExp[1]);
				$smAvailToDate		= $objSchSchedules->getDateTimeFrmMinutes($smAvailDate, $smValExp[2]);

				$recNo = $smValExp[3];
				$slotGrpNo = $smValExp[4];
				$shiftStatus = $smValExp[5];
				if($slotGrpNo == "")
				{
					$slotGrpNo = 0;
				}
				if($shiftStatus == "")
				{
					$shiftStatus = 'available';
				}
				if($table_name != 'placement_sm_timeslots')
				{
					$shiftStatus = 'busy';
				}

				$shiftNameSno = $smValExp[6];
				$shiftPosNum = $smValExp[7];

				$insertArrayData[$recNo][] = array($smAvailDate,$smAvailFromDate,$smAvailToDate,$slotGrpNo,$shiftStatus,$shiftNameSno,$shiftPosNum);
				
			}
		}
		
		$tempVar = 1; // for recurrence numbers auto increment for each recurrence
		foreach($insertArrayData as $recNo=>$timeSlotArray)
		{
			$setFlat = 0;
			foreach($timeSlotArray as $timeSlotDetails)
			{
				$smAvailDate = $timeSlotDetails[0];
				$smAvailFromDate = $timeSlotDetails[1];
				$smAvailToDate = $timeSlotDetails[2];
				$smEventGrpNo = $timeSlotDetails[3];
				$smShiftStatus = $timeSlotDetails[4];
				$smShiftNameSno = $timeSlotDetails[5];
				$smShiftPosNum = $timeSlotDetails[6];
				
				if($recNo != 0)
				{
					$smEventType = 'recurrence';
					$smEventNo = $tempVar;
					$setFlat = 1;
				}
				else
				{
					$smEventType = 'single';
					$smEventNo = 0;
				}
			$selectOldRecords = "SELECT count(1),GROUP_CONCAT(sno) AS snos FROM ".$table_name." WHERE shift_date='".$smAvailDate."' AND shift_starttime='".$smAvailFromDate."' AND shift_endtime='".$smAvailToDate."' AND pid='".$psno."' ";
			$resultOld = mysql_query($selectOldRecords, $db);

			if (mysql_num_rows($resultOld) > 0) {
				$rowOld = mysql_fetch_assoc($resultOld);
				if($rowOld['snos']!=''){
				$deleteOld = "DELETE FROM ".$table_name." WHERE sno IN (".$rowOld['snos'].") ";
				mysql_query($deleteOld, $db);
                                }
			}
			$sm_plcmnt_tf_sql	= "INSERT INTO ".$table_name."
										(
										sno,									
										pid,
										shift_date,
										shift_starttime,
										shift_endtime,								
										event_type,
										event_no,
										event_group_no,
										shift_status,
										sm_sno,
										no_of_positions,
										cuser,
										ctime,
										muser,
										mtime
										)
										VALUES 
										(
										'',									
										'".$psno."',
										'".$smAvailDate."',
										'".$smAvailFromDate."',
										'".$smAvailToDate."',								
										'".$smEventType."',
										'".$smEventNo."',
										'".$smEventGrpNo."',
										'".$smShiftStatus."',
										'".$smShiftNameSno."',
										'".$smShiftPosNum."',
										'".$username."',
										NOW(),
										'".$username."',
										NOW()							
										)";

				$sm_plcmnt_tf_res	= mysql_query($sm_plcmnt_tf_sql, $db);	
				if(!empty($candidate) && $table_name == "placement_sm_timeslots"){

					// updating the Candidate who placed for the joborder shifts and the shift position
					$shiftNewDate = date("Y-m-d", strtotime($smAvailDate));
					$selectPosid = "SELECT sno,no_of_positions FROM posdesc_sm_timeslots WHERE shift_date='".$shiftNewDate."' AND shift_starttime='".$smAvailFromDate."' AND shift_endtime='".$smAvailToDate."' AND sm_sno='".$smShiftNameSno."' AND pid = '".$id_req."'";
					$resultPosid = mysql_query($selectPosid,$db);
					while($rowPosid = mysql_fetch_array($resultPosid)){

						//Queries to update the number of positions
						$position=1;
						$position = $objSchSchedules->getFilledPosPerShift($id_req,$smShiftNameSno,'placement');
						if($position == 0){
							$position=1;
						}
						if($rowPosid[1] == 0){
							$position=1;
						}
						$updatePosdes = "UPDATE sm_timeslot_positions SET candid='".$candidate."',color_code='#04B431' WHERE shift_date='".$shiftNewDate."' AND shift_starttime='".$smAvailFromDate."' AND shift_endtime='".$smAvailToDate."' AND sm_sno='".$smShiftNameSno."' AND sm_timeslote_sno='".$rowPosid[0]."' AND position='".$position."' AND pid = '".$id_req."' ";
						//AND position='".$shiftPosId."'" 
						mysql_query($updatePosdes,$db);
					}	
				}
			}
			if($setFlat == 1)
			{
				$tempVar++;
			}
		}
		
	}

	function updateShiftRates($refId,$shift_rates_data,$shift_setup_sno,$module)
	{
		global $username,$db;
		$rateTypeArray = array("rate1","rate2","rate3");
		
		if($module =="bulkplacement")
		{
			
			$mode_rate_type		= $module;
			$customratesexplode 	= explode("|", $shift_rates_data);
			$selectedRatesData	= $customratesexplode[0];
			$selectedMasterRateIds	= $customratesexplode[1];
			
			$shiftRatesDataArr	= explode("^^CRSPLIT^^", $selectedRatesData);
			array_pop($shiftRatesDataArr); // to remove the last ^^CRSPLIT^^
			
			if($module =="bulkplacement")
			{
				$field_set 		= " bulk_place_multiplerates (bulk_place_sno,ratemasterid,ratetype,rate,period,currency,billable,taxable, cuser,cdate,shiftid)";
				$table_rates 		= " bulk_place_multiplerates mj,multiplerates_master mr";
				$where_cond=" mj.bulk_place_sno='".$refId."' AND mj.ratemasterid = mr.rateid AND mr.default_status='N' AND mj.shiftid='".$shift_setup_sno."'";
			
			}
			
			$del_rates = "DELETE mj.* FROM ".$table_rates." WHERE ".$where_cond;
			mysql_query($del_rates,$db);
			
			foreach($shiftRatesDataArr AS $key=>$val)
			{
				$customRateDataArr 	= explode("^^sno^^",$val);
				$rateSno		= $customRateDataArr[0];
				$customRataData		= explode("^",$customRateDataArr[1]);
				
				$payAmount	= $customRataData[0];
				$payPeriod	= $customRataData[1];
				$payCurrency	= $customRataData[2];
				$payBillable	= $customRataData[3];
				
				$billAmount	= $customRataData[4];
				$billPeriod	= $customRataData[5];
				$billCurrency	= $customRataData[6];
				$billTaxable	= $customRataData[7];
				
				if($payBillable == "B")
				{						
					$payBillable = "Y";
				}
				else
				{
					$payBillable = "N";
				}
				if($billTaxable == "T")
				{						
					$billTaxable = "Y";
				}
				else
				{
					$billTaxable = "N";
				}
				
				$rateType	= getRateTypeById($rateSno);
				$value_set_payrate	="";
				$value_set_billrate	="";

				$value_set_payrate	= "('".$refId."','".$rateType."','payrate','".$payAmount."','".$payPeriod."','".$payCurrency."','".$payBillable."','N','".$username."',NOW(),'".$shift_setup_sno."')";
				$value_set_billrate	= "('".$refId."','".$rateType."','billrate','".$billAmount."','".$billPeriod."','".$billCurrency."','".$payBillable."','".$billTaxable."','".$username."',NOW(),'".$shift_setup_sno."')";
	
				$ins_payrate = "INSERT INTO ".$field_set." VALUES ".$value_set_payrate." ";
				
				mysql_query($ins_payrate,$db);

				$ins_billrate = "INSERT INTO ".$field_set." VALUES ".$value_set_billrate." ";
				
				mysql_query($ins_billrate,$db);
				
				if($rateType=="rate1" || $rateType=="rate2" || $rateType=="rate3")
				{
					updateMasterTables($refId,$rateType,$module,$customRataData);
					if (in_array($rateType, $rateTypeArray)) {
						array_splice($rateTypeArray, array_search($rateType, $rateTypeArray ), 1);
					}
				}
			}
		}
		if (!empty($rateTypeArray)) {
			resetMasterTables($refId,$rateTypeArray,$module);
		}
		
	}
	
	function updateMasterTables($refId,$rateType,$module,$customRataData)
	{
		global $db;
		$payAmount	= $customRataData[0];
		$payPeriod	= $customRataData[1];
		$payCurrency	= $customRataData[2];
		$payBillable	= $customRataData[3];
		
		$billAmount	= $customRataData[4];
		$billPeriod	= $customRataData[5];
		$billCurrency	= $customRataData[6];
		$billTaxable	= $customRataData[7];
		if($rateType =="rate1")
		{
			$setValues = "SET pamount = '".$payAmount."', pcurrency = '".$payCurrency."', pperiod = '".$payPeriod."',bamount = '".$billAmount."', bcurrency = '".$billCurrency."', bperiod = '".$billPeriod."'";
		}
		if($rateType =="rate2")
		{
			$setValues = "SET otprate_amt = '".$payAmount."', otprate_period = '".$payPeriod."', otprate_curr = '".$payCurrency."',otbrate_amt = '".$billAmount."', otbrate_period = '".$billPeriod."', otbrate_curr = '".$billCurrency."'";
		}
		if($rateType =="rate3")
		{
			$setValues = "SET double_prate_amt = '".$payAmount."', double_prate_curr = '".$payCurrency."', double_prate_period = '".$payPeriod."',double_brate_amt = '".$billAmount."', double_brate_curr = '".$billCurrency."', double_brate_period = '".$billPeriod."'";
		}

		if($module == "bulkplacement" )
		{
			$updQuery = "UPDATE bulk_placement_jobs $setValues WHERE sno='".$refId."'";
		}
		
		mysql_query($updQuery, $db);
	}

	function resetMasterTables($refId,$rateTypeArray,$module)
	{
		global $db;
		foreach ($rateTypeArray as $rateType) {
			
			$selectRateBillTax = "SELECT mr.sno, mr.name, mr.pvalue, mr.poption, mr.bvalue, mr.boption, mr.peditable, mr.beditable, mr.rateid FROM multiplerates_master mr WHERE mr.rateid ='".$rateType."'";
			$resultRateBillTax = mysql_query($selectRateBillTax, $db);

			$rowRateBillTax = mysql_fetch_assoc($resultRateBillTax);

			if ($rowRateBillTax['poption'] == 'B') {
				$payBillable	= 'Y';
			}else{
				$payBillable	= 'N';
			}

			if ($rowRateBillTax['boption'] == 'T') {
				$billTaxable	= 'Y';
			}else{
				$billTaxable	= 'N';
			}

			$payAmount	= 0;
			$payPeriod	= 'HOUR';
			$payCurrency	= 'USD';
			$billAmount	= 0;
			$billPeriod	= 'HOUR';
			$billCurrency	= 'USD';
			if($rateType =="rate1")
			{
				$setValues = "SET pamount = '".$payAmount."', pcurrency = '".$payCurrency."', pperiod = '".$payPeriod."',bamount = '".$billAmount."', bcurrency = '".$billCurrency."', bperiod = '".$billPeriod."'";
			}
			if($rateType =="rate2")
			{
				$setValues = "SET otprate_amt = '".$payAmount."', otprate_period = '".$payPeriod."', otprate_curr = '".$payCurrency."',otbrate_amt = '".$billAmount."', otbrate_period = '".$billPeriod."', otbrate_curr = '".$billCurrency."'";
			}
			if($rateType =="rate3")
			{
				$setValues = "SET double_prate_amt = '".$payAmount."', double_prate_curr = '".$payCurrency."', double_prate_period = '".$payPeriod."',double_brate_amt = '".$billAmount."', double_brate_curr = '".$billCurrency."', double_brate_period = '".$billPeriod."'";
			}

			if($module == "bulkplacement" )
			{
				$updQuery = "UPDATE bulk_placement_jobs $setValues WHERE sno='".$refId."'";
			}
			
			mysql_query($updQuery, $db);
		}

	}

	function getRateTypeById($rtId)
	{
		global $db;
		$selQry 	= "SELECT rateid FROM multiplerates_master WHERE sno=".$rtId."";
		$selQryRes 	= mysql_query($selQry, $db);		
		
		if (mysql_num_rows($selQryRes) > 0) {			
			$selQryRowRes = mysql_fetch_array($selQryRes);			
			return $selQryRowRes['rateid'];
		} else {
			return false;
		}
	}

	function defaultRatesInsertion($type_order_id,$ratesStr,$in_ratesCon,$shiftId)
	{
		global $maildb,$db,$username,$mode_rate_type;

		$table_rates = "";
		$wherecond = "";
		$field_set = "";

		if($mode_rate_type == "bulkplacement")
		{
			$field_set = "bulk_place_multiplerates (bulk_place_sno,ratemasterid,ratetype,rate,period,currency,billable,taxable,cuser,cdate,shiftid)";
			$table_rates = "bulk_place_multiplerates";
			$wherecond = " AND bulk_place_sno='".$type_order_id."' AND shiftid='".$shiftId."' ";
		}

		$rate_ids_arr = array();

		$sel_rate_ids="SELECT rateid,name FROM multiplerates_master WHERE status='ACTIVE' AND default_status='Y' AND ".$in_ratesCon." ORDER BY name ";
		$res_rate_ids=mysql_query($sel_rate_ids,$db);
		while($fetch_rates=mysql_fetch_row($res_rate_ids))
			$rate_ids_arr[$fetch_rates[1]] = "'".$fetch_rates[0]."'";

		$del_rates = "DELETE FROM ".$table_rates." WHERE ratemasterid IN (".implode(",",$rate_ids_arr).") ".$wherecond;
		mysql_query($del_rates,$db);

		$exp_rates = explode("|^|",$ratesStr);
		$count_rates=count($exp_rates);
		for($i=0;$i<$count_rates;$i++)
		{
			$exp_pay_bill_rates =  explode("|",$exp_rates[$i]);

			$value_set = "";
			$value_set = "('".$type_order_id."',".$rate_ids_arr[$exp_pay_bill_rates[6]].",'".$exp_pay_bill_rates[5]."','".$exp_pay_bill_rates[0]."','".$exp_pay_bill_rates[1]."','".$exp_pay_bill_rates[2]."','".$exp_pay_bill_rates[3]."','".$exp_pay_bill_rates[4]."','".$username."',NOW(),'".$shiftId."')";

			$ins_rates = "INSERT INTO ".$field_set." VALUES ".$value_set;
			mysql_query($ins_rates,$db);
		}	
	}

	function multipleRatesInsertion($type_order_id,$rateType,$billableR,$mulpayRateTxt,$payratePeriod,$payrateCurrency,$mulbillRateTxt,$billratePeriod,$billrateCurrency,$taxableR,$shiftId)
	{
		global $maildb,$db,$username,$mode_rate_type,$type_order_id,$rateRowVals;

		$table_rates = "";
		$where_cond="";
		$upd_status = "";

		$expRateRowVals = explode(',',$rateRowVals);

		if($mode_rate_type == "bulkplacement")
		{
			$field_set = "bulk_place_multiplerates (bulk_place_sno,ratemasterid,ratetype,rate,period,currency,billable,taxable,cuser,cdate,shiftid)";
			$table_rates = "bulk_place_multiplerates mj,multiplerates_master mr";
			
			$where_cond=" mj.bulk_place_sno='".$type_order_id."' AND mj.ratemasterid = mr.rateid AND mr.default_status='N' AND mj.shiftid='".$shiftId."'";
		}
		

		$del_rates = "DELETE mj.* FROM ".$table_rates." WHERE ".$where_cond;
		mysql_query($del_rates,$db);

		$count_rate = count($expRateRowVals);
		for($i=0;$i<=$count_rate;$i++)	
		{
			$rowValueSel = $expRateRowVals[$i];
			if(trim($rateType[$rowValueSel])!= "")
			{
				$value_set_payrate="('".$type_order_id."','".$rateType[$rowValueSel]."','payrate','".$mulpayRateTxt[$rowValueSel]."','".$payratePeriod[$rowValueSel]."','".$payrateCurrency[$rowValueSel]."','".$billableR[$rowValueSel]."','N','".$username."',NOW(),'".$shiftId."')";
				$value_set_billrate="('".$type_order_id."','".$rateType[$rowValueSel]."','billrate','".$mulbillRateTxt[$rowValueSel]."','".$billratePeriod[$rowValueSel]."','".$billrateCurrency[$rowValueSel]."','".$billableR[$rowValueSel]."','".$taxableR[$rowValueSel]."','".$username."',NOW(),'".$shiftId."')";

				$ins_payrate = "INSERT INTO ".$field_set." VALUES ".$value_set_payrate;
				mysql_query($ins_payrate,$db);

				$ins_billrate = "INSERT INTO ".$field_set." VALUES ".$value_set_billrate;
				mysql_query($ins_billrate,$db);
			}
		}
	}

	function defaultZeroRatesInsertion($bulk_place_sno,$rateTypeArray,$shiftId)
	{
		global $username,$db;

		if (!empty($rateTypeArray)) {
			foreach ($rateTypeArray as $key => $rateVal) {

				$select = "SELECT ratemasterid FROM bulk_place_multiplerates WHERE ratemasterid='".$rateVal."' AND  bulk_place_sno='".$bulk_place_sno."' AND shiftid='".$shiftId."' ";
				$result = mysql_query($select,$db);
				if (mysql_num_rows($result)==0) {					

					$selectRateBillTax = "SELECT mr.sno, mr.name, mr.pvalue, mr.poption, mr.bvalue, mr.boption, mr.peditable, mr.beditable, mr.rateid FROM multiplerates_master mr WHERE mr.rateid ='".$rateVal."'";
					$resultRateBillTax = mysql_query($selectRateBillTax, $db);

					$rowRateBillTax = mysql_fetch_assoc($resultRateBillTax);

					if ($rowRateBillTax['poption'] == 'B') {
						$payBillable	= 'Y';
					}else{
						$payBillable	= 'N';
					}

					if ($rowRateBillTax['boption'] == 'T') {
						$billTaxable	= 'Y';
					}else{
						$billTaxable	= 'N';
					}

					$payAmount	= 0;
					$payPeriod	= 'HOUR';
					$payCurrency	= 'USD';
					$billAmount	= 0;
					$billPeriod	= 'HOUR';
					$billCurrency	= 'USD';

					$insertZeroPay = "INSERT INTO bulk_place_multiplerates (bulk_place_sno,ratemasterid,ratetype,rate,period,currency,billable,taxable, cuser,cdate,shiftid) VALUES ('".$bulk_place_sno."','".$rateVal."','payrate','".$payAmount."','".$payPeriod."','".$payCurrency."','".$payBillable."','N','".$username."',NOW(),'".$shiftId."')";
					mysql_query($insertZeroPay, $db);
					$insertZeroBill = "INSERT INTO bulk_place_multiplerates (bulk_place_sno,ratemasterid,ratetype,rate,period,currency,billable,taxable, cuser,cdate,shiftid) VALUES ('".$bulk_place_sno."','".$rateVal."','billrate','".$billAmount."','".$billPeriod."','".$billCurrency."','".$payBillable."','".$billTaxable."','".$username."',NOW(),'".$shiftId."')";
					mysql_query($insertZeroBill, $db);
				}
			}

		}
	}

	$srow = array();
	$candtype = '';
	$candidateId='';
	$candempId='';
	$candlist_user ='';
	$supid = '';
	$wotc_mja_status = '';
	$loc_user='';
	$jobtypeChk	= "";
	$conusername='0';
	$page152[83] = displayDepartmentName($page152[84],true);

	// Updating Assignment data..
	$appno=updateAssign($page152,$uid,$candtype,$id_req,$empvalues,$commval,$ratetype,$paytype,$confirmToClose,$userid,$attention,$roleName,$roleOverWrite,$corp_code, $timeslotvalues, $burdenTypeDetails, $burdenItemDetails,$joindustryid,$schedule_display, $comm_bill_burden, $bill_burdenTypeDetails, $bill_burdenItemDetails, $roleEDisable,$shift_id,$shift_st_time,$shift_et_time,$shift_type,$candrn,$rate_on_submission,$licode,$daypay,$federal_payroll,$nonfederal_payroll,$contractid,$classification,$placement_timesheet_layout_preference);

	$expappnoVal = $appno;
	$shiftSno	= $shift_id;
	$place_id = $expappnoVal;
	/* Rates and Shift Rates */
	$smShifts = explode(",",$sm_sel_shifts);
	$rateTypeAry = array("rate1","rate2","rate3");
	if (count($smShifts)>0) {
		foreach ($smShifts as $key => $shiftSno) {
			$shift_rates_data 	= "sm_rates_".$shiftSno;
			if($$shift_rates_data!="|" && $$shift_rates_data!="")
			{
				$mode_rate_type = "bulkplacement";
				$type_order_id =$appno;
				updateShiftRates($type_order_id,$$shift_rates_data,$shiftSno,$mode_rate_type);
				defaultZeroRatesInsertion($type_order_id,$rateTypeAry,$shiftSno);
			}
			else
			{
				$mode_rate_type = "bulkplacement";
				$type_order_id =$appno;	
				defaultRatesInsertion($type_order_id,$mulRatesVal,$in_ratesCon,$shiftSno);
				multipleRatesInsertion($type_order_id,$rateType,$billableR,$mulpayRateTxt,$payratePeriod,$payrateCurrency,$mulbillRateTxt,$billratePeriod,$billrateCurrency,$taxableR,$shiftSno);
				defaultZeroRatesInsertion($type_order_id,$rateTypeAry,$shiftSno);
			}
		}
	}else{
		$mode_rate_type = "bulkplacement";
		$type_order_id =$appno;	
		defaultRatesInsertion($type_order_id,$mulRatesVal,$in_ratesCon,'0');
		multipleRatesInsertion($type_order_id,$rateType,$billableR,$mulpayRateTxt,$payratePeriod,$payrateCurrency,$mulbillRateTxt,$billratePeriod,$billrateCurrency,$taxableR,'0');
		defaultZeroRatesInsertion($type_order_id,$rateTypeAry,'0');
	}

	//Inserting the OLD shift scheduling if the settings is in disabled mode
	$WeekIntArray=array("Sunday"=>1,"Monday"=>2,"Tuesday"=>3,"Wednesday"=>4,"Thursday"=>5,"Friday"=>6,"Saturday"=>7);
	if(SHIFT_SCHEDULING_ENABLED == 'N') 
	{
		if($customcheckall!="Y")
		{
			foreach($defweekday as $key=>$val)
			{
				$Timefrom="fr_hour".$key;
				$TimeTo="to_hour".$key;
				$weekday=$val;
				$AddweakArray="add".$weekday."week";
				$AddweakInt=($key+1);
				$ChkVal="daycheck".$key;

				if($$ChkVal!="Y" && (trim($$Timefrom)!="" && trim($$TimeTo)!=""))
				{
					

					$sheQry="INSERT INTO bulk_placement_tab (sno,bulk_place_sno,starthour,endhour,wdays,sch_date,coltype) VALUES ('','".$place_id."','".$$Timefrom."','".$$TimeTo."','".$AddweakInt."','','assign')";
					mysql_query($sheQry,$db);
				}

				foreach($$AddweakArray as $Addkey=>$Addval)
				{
					$AddTimefrom="newSchdFrom".$Addkey;
					$AddTimeTo="newSchdTo".$Addkey;

					if($addchkSchedule[$Addkey]!="Y" && (trim($$AddTimefrom)!="" && trim($$AddTimeTo)!=""))
					{
						$Addweekday=$WeekIntArray[$Addval];

						
						$AddsheQry="INSERT INTO bulk_placement_tab (sno,bulk_place_sno,starthour,endhour,wdays,sch_date,coltype) VALUES ('','".$place_id."','".$$AddTimefrom."','".$$AddTimeTo."','".$Addweekday."','','assign')";
						mysql_query($AddsheQry,$db);
					}
				}
			}

			foreach($adddate as $AddDatekey=>$AddDateval)
			{
				$AddDateTimefrom="newSchdFrom".$AddDatekey;
				$AddDateTimeTo="newSchdTo".$AddDatekey;

				if($addchkSchedule[$AddDatekey]!="Y"  && (trim($$AddDateTimefrom)!="" && trim($$AddDateTimeTo)!=""))
				{
					$InsdateArr=explode("/",$AddDateval);
					$Insdate=$InsdateArr[2]."-".$InsdateArr[0]."-".$InsdateArr[1]." 00:00:00";
					$AddDateweekday=$WeekIntArray[$addDateweek[$AddDatekey]];

					
					$AddDatesheQry="INSERT INTO bulk_placement_tab (sno,bulk_place_sno,starthour,endhour,wdays,sch_date,coltype) VALUES ('','".$place_id."','".$$AddDateTimefrom."','".$$AddDateTimeTo."','".$AddDateweekday."','".$Insdate."','assign')";
					mysql_query($AddDatesheQry,$db);
				}
			}

			///////////////Week schedule update/////////////////////////////////////////////////////////////////
			foreach($Updweek as $Updkey=>$Updval)
			{
				$UpdTimefrom="UpdSchdFrom".$Updkey;
				$UpdTimeTo="UpdSchdTo".$Updkey;

				if($UpdchkSchedule[$Updkey]!="Y" && (trim($$UpdTimefrom)!="" && trim($$UpdTimeTo)!=""))
				{
					$Updweekday=$WeekIntArray[$Updval];

					$UpdsheQry="INSERT INTO bulk_placement_tab (sno,bulk_place_sno,starthour,endhour,wdays,sch_date,coltype) VALUES ('','".$place_id."','".$$UpdTimefrom."','".$$UpdTimeTo."','".$Updweekday."','','assign')";
					mysql_query($UpdsheQry,$db);
				}
			}

			//////////////  Date Schedule Update/////////////////////////////////

			foreach($Upddate as $UpdDatekey=>$UpdDateval)
			{
				$UpdDateTimefrom="UpdSchdFrom".$UpdDatekey;
				$UpdDateTimeTo="UpdSchdTo".$UpdDatekey;

				if($UpdchkSchedule[$UpdDatekey]!="Y"  && (trim($$UpdDateTimefrom)!="" && trim($$UpdDateTimeTo)!=""))
				{
					$UpddateArr=explode("/",$UpdDateval);
					$Upddate_sch=$UpddateArr[2]."-".$UpddateArr[0]."-".$UpddateArr[1]." 00:00:00";
					$UpdDateweekday=$WeekIntArray[$UpdDateweek[$UpdDatekey]];

					$UpdDatesheQry="INSERT INTO bulk_placement_tab (sno,bulk_place_sno,starthour,endhour,wdays,sch_date,coltype) VALUES ('','".$place_id."','".$$UpdDateTimefrom."','".$$UpdDateTimeTo."','".$UpdDateweekday."','".$Upddate_sch."','assign')";
					mysql_query($UpdDatesheQry,$db);
				}
			}
			////////////End of the Update//////////////////////////////////////////////////////
		}
	}
	
	//unset the session with the sequence number if anything exists
	if(SHIFT_SCHEDULING_ENABLED == 'Y' && $shift_type=='perdiem')
	{
		unset($_SESSION['editPlacementPerdiemShiftSch'.$candrn]);
		unset($_SESSION['editPlacementPerdiemShiftPagination'.$candrn]);	
	}
	/*
		Once the bulk placement queue is done then we will Update the Process Stage to S (Start).
	 	Once the Process Stage is in S then our Cron will pick the record and it will do the placement process.
	*/

	$bulkplaceObj->updateBulkPlaceProcesStage('S',$batch_id,$maindb);
	$bulkplaceObj->updateMasterBulkPlaceProcesStage('S',$batch_id,$db);
?>
<body>	
<script type="text/javascript">
	window.opener.openManageSubmissionScreen('<?php echo $joborderid;?>','joborder');
	window.close();	
</script>
</body>
</html> 