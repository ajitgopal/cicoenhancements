<?php  
	$page151=$page15;
	$page2151=$page215;
	$close_addrVal=$addr;
        $refJoborderDetArray = explode('|',$addr);//referral Bonus passing placed job order
        $refBonusJOID = $refJoborderDetArray[0];
        $licode=$wali; 	
	require("global.inc");
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

	function shiftdate_sort($a, $b) {
		
		return strtotime($a) - strtotime($b);
	}


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
		$page152[85] = $page152[74];
    		$page152[86] = $page152[75];
    		$page152[87] = $page152[76];
    		$page152[88] = $page152[77];
    		$page152[89] = $page152[78];
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
	//echo '<pre>';
	//print_r($page152);
	//exit();
	$contractid = $page152[92];
	$classification = $page152[93];
	

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
<title>New Placement</title>
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/grid.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/filter.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/site.css" type=text/css rel=stylesheet>
<link href="/BSOS/css/crm-editscreen.css" type=text/css rel=stylesheet>
<link type="text/css" rel="stylesheet" href="/BSOS/css/crm-summary.css">
<link rel="stylesheet" href="/BSOS/popupmessages/css/popup_message.css" media="screen" type="text/css">
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popupMsgArray.js"></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popup-message.js"></script>
<script language=javascript src=/BSOS/scripts/validateassignment.js></script>
<script language=javascript src="/BSOS/scripts/dynamicElementCreatefun.js"></script>
<script language=javascript src="/BSOS/scripts/tabpane.js"></script>
<script language=javascript src=/BSOS/scripts/place_schedule.js></script>
</head>
	
<?php
	$sinaddr=explode("|",$addr);
	$id_req=$sinaddr[0];
	$id=$sinaddr[1];
	$corp_code= 0;

	if($psource=="reactivate" && $pusername!="")
	{
		$pque="SELECT sno FROM placement_jobs WHERE pusername='".$pusername."' AND posid='".$id_req."' AND candidate='".$candidateVal."'";
		$pres=mysql_query($pque,$db);
		$prow=mysql_fetch_row($pres);
		$place_id=$prow[0];

		if($place_id!="")
		{
			mysql_query("DELETE FROM assign_commission WHERE assignid = '".$place_id."' AND assigntype = 'P' AND shift_id='".$shift_id."'",$db);
			mysql_query("DELETE FROM placement_jobs WHERE sno='".$place_id."'",$db);
			//Delete from respective tables based on the shift scheduling settings
			if(SHIFT_SCHEDULING_ENABLED == 'Y') 
			{
				mysql_query("DELETE FROM placement_sm_timeslots WHERE pid='".$place_id."'",$db);
			}
			if(SHIFT_SCHEDULING_ENABLED == 'N') 
			{
				mysql_query("DELETE FROM placement_tab WHERE consno='".$place_id."' AND coltype='assign'",$db);
			}
           		mysql_query("DELETE FROM placement_burden_details WHERE placement_jobs_sno='".$place_id."'",$db);
		}
	}

	$sm_form_data_shift_id = array();
	$timeslotvalues = "";
	$perdiem_timeslotvalues = "";
	
	//$timeslotvalues = $sm_form_data;
	$shift_snos = $sm_sel_shifts; //getting selected shift name snos for inserting previous date data
	// Getting the Shift Name Sno  for this table shift_setup >> sno
	$shift_id = '0';
	// Loop to get shift data for the selected shift sno
	if(SHIFT_SCHEDULING_ENABLED == 'Y')
	{		
		if($shift_type=='regular')
		{
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
			//$timeslotvalues = generateTimeSlotsFromSession($userid,$id_req);
			$overrideshifttimeslotempcand = "yes";
		}
	}

	// When Shift management is disabled getting shift id
	if(SHIFT_SCHEDULING_ENABLED == 'N') 
	{
		$shift_det = explode("|",$new_shift_name);
		$shift_id  = $shift_det[0];
		$shift_st_time = $shift_time_from;
		$shift_et_time = $shift_time_to;
	}

	// Positions on placement
	$nextPlacePositon = $nextPos;


	//Deactivating the user account for API user
	if($candid != "")
	{
		if(substr($candid,0,3) == "con")
		{
			$convalue=explode("con",$candid);
			$conqry = "select username from consultant_list where serial_no = '".$convalue[1]."'";
			$conres = mysql_query($conqry,$db);
			$connumrows = mysql_num_rows($conres);
			$conrow = mysql_fetch_row($conres);

			if($connumrows > 0)
				DeactivateApiUser($conrow[0]);
		}
	}

	$sub_seqnumber=$seqnumber;	

	//Checking for duplicate placements
	$manageplc_sno=getManageSno('Placed','interviewstatus');
	$cand_val = $candidateVal;

	$dup_Query = "SELECT sno,shift_id,seqnumber,res_id,req_id FROM resume_history WHERE seqnumber= '".$sub_seqnumber."'AND shift_id = '".$shift_id."' AND req_id = '".$id_req."' AND res_id = '".$id."' AND status='".$manageplc_sno."' ";
	$resDup_Query = mysql_query($dup_Query,$db);
	$resDup_Count = mysql_num_rows($resDup_Query);


if($resDup_Count>0){
	echo "<script>
	alert('Selected Candidate/Employee has been already placed. Please check in Hiring/Employee Management');
	opener.location.reload();
	window.close();
	</script>";
	exit();
}
    
	if($place_link=="place_cand")
	{
		// checking this condition when candidate is placing directly from candidate submission page
		$sinval=$id;

		//Selecting the required fields from the posdesc table.
		$pos_que="SELECT contact,type,postitle FROM posdesc where posid='".$id_req."'"; 
		$res_pos_que=mysql_query($pos_que,$db);
		$pos_fetch=mysql_fetch_row($res_pos_que);
		$etitle="Placed for the Job Order ".$pos_fetch[2];

		//Getting the emailid of the contact
		if($pos_fetch[1]=="staffoppr")
			$oppr_que="SELECT email,sno FROM staffoppr_contact where sno='".$pos_fetch[0]."'";
		else
			$oppr_que="SELECT email,sno FROM staffacc_contact where sno='".$pos_fetch[0]."' and staffacc_contact.username!=''";		
		$res_oppr=mysql_query($oppr_que,$db);
		$oppr_fetch=mysql_fetch_row($res_oppr);
		$oppr_id="oppr".$oppr_fetch[1];
		// When we click on Place from "Search for Candidates" Screen this code will work
		$selval_temp = "cand".$sinval;

		$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
		$msgunumber=$thisday;
		$sub_seqnumber=$msgunumber;	
		/*
			Fixed the Submission missing issue, 
			Fixed the issue showing the multiple records for same candidate with same seqnumber with sub_status as A and P in reqresponse
			This code is Modified By SARANESH.AR
		*/
		
		$manage_sno=getManageSno('Placed','interviewstatus');


		$chk_reqsno="select sno from reqresponse where resumeid='".$selval_temp."' AND  posid='".$id_req."' AND sub_status='A' AND par_id='-1' AND shift_id = '".$shift_id."' AND seqnumber=".$msgunumber;
		$chk_reqsno_res=mysql_query($chk_reqsno,$db);
		$chk_sno_cnt=mysql_num_rows($chk_reqsno_res); 
		$chk_reqsno_fet=mysql_fetch_row($chk_reqsno_res);

		if($chk_sno_cnt=="0")
		{	
			$ins_response="INSERT INTO reqresponse(sno,emailid,posid,resumeid,rdate,seqnumber,par_id,username,stage,sub_status,mdate,muser,shift_id) values ('','".addslashes($oppr_fetch[0])."','".$id_req."','".$selval_temp."',NOW(),'".$msgunumber."','0','".$username."','".$manage_sno."','P',NOW(),'".$username."','".$shift_id."')";
			mysql_query($ins_response,$db);
		}
		else
		{			
			$upd_reqres="UPDATE reqresponse SET seqnumber = '".$msgunumber."',sub_status='P', par_id='0',stage='".$manage_sno."',rdate=NOW(),muser='".$username."',mdate=NOW() WHERE posid='".$id_req."' AND resumeid='".$selval_temp."' AND sno ='".$chk_reqsno_fet[0]."' AND shift_id = '".$shift_id."'";
			mysql_query($upd_reqres,$db);
		}
		
		$con_que1="select contact,company from posdesc where posid='".$id_req."'";
		$result1=mysql_query($con_que1,$db);
		$result1_arr=mysql_fetch_row($result1);
		// Inserting and Updating in resume_status
		$que="SELECT status FROM resume_status WHERE req_id='".$id_req."' AND res_id='".$id."' AND pstatus='A' AND shift_id = '".$shift_id."' AND seqnumber=".$msgunumber;
		$res=mysql_query($que,$db);
		$chk_rums_sts_cnt=mysql_num_rows($res); 
		$row=mysql_fetch_row($res);
		if($chk_rums_sts_cnt=="0")
		{
			$ique="INSERT INTO resume_status(sno,res_id,req_id,appuser,appdate,status,pstatus,seqnumber,muser,mdate,shift_id) values ('','".$id."','".$id_req."','".$username."',CURRENT_DATE(),'".$manage_sno."','P','".$msgunumber."','".$username."',NOW(),'".$shift_id."')";
			mysql_query($ique,$db);
			
			$his_que="insert into resume_history(sno,cli_name,req_id,res_id,comp_id,appuser,appdate,status,notes,type,muser,mdate,seqnumber,shift_id) VALUES  ('','".$result1_arr[0]."','".$id_req."','".$id."','".$result1_arr[1]."','".$username."',now(),'".$manage_sno."','','cand','".$username."',now(),'".$msgunumber."','".$shift_id."')";
			mysql_query($his_que,$db);
		}
		else
		{
			
			$mang_name=getmanagename($row[0]);
			if($mang_name=="Applied")
			{						
				$que_upd="UPDATE resume_status SET status='".$managesno."',seqnumber='".$msgunumber."', pstatus='P',muser='".$username."',mdate=NOW() WHERE res_id='".$id."' AND req_id='".$id_req."' AND shift_id = '".$shift_id."'"; 
				mysql_query($que_upd,$db);

				$his_que="INSERT INTO resume_history(sno,cli_name,req_id,res_id,comp_id,appuser,appdate,status,notes,type,muser,mdate,seqnumber,shift_id) VALUES  ('','".$result1_arr[0]."','".$id_req."','".$id."','".$result1_arr[1]."','".$username."',now(),'".$managesno."','','cand','".$username."',now(),'".$msgunumber."','".$shift_id."')";
				mysql_query($his_que,$db);						
			}
		}

		// For updating submission and candidates count.
		updateTotalSubmissionsCount($id_req);
		updateTotalCandSubmissionsCount($id_req);

		//Inserting events into the activites
		$ins_con_event="INSERT INTO contact_event(sno,con_id,username,etype,etitle,enotes,sdate) values('','".$oppr_id."','".$username."','Event','".addslashes($etitle)."','".addslashes($etitle)."',NOW())";
		$res_con_event=mysql_query($ins_con_event,$db);
		$cmngt_id=mysql_insert_id($db);

		$ins_cmngt="INSERT INTO cmngmt_pr(sno,con_id,username,tysno,title,sdate,subject,lmuser) values('','".$oppr_id."','".$username."','".$cmngt_id."','Event',NOW(),'".addslashes($etitle)."','".$username."')";
		mysql_query($ins_cmngt,$db);
	
		
		for($i=0;$i<count($sinval);$i++)
		{
			$conevent_id="cand".$sinval[$i];
			$ins_con_event="INSERT INTO contact_event(sno,con_id,username,etype,etitle,enotes,sdate) values('','".$conevent_id."','".$username."','Placed','".addslashes($etitle)."','".addslashes($etitle)."',NOW())";
			$res_con_event=mysql_query($ins_con_event,$db);
			$cmngt_id=mysql_insert_id($db);

			$ins_cmngt="INSERT INTO cmngmt_pr(sno,con_id,username,tysno,title,sdate,subject,lmuser) values('','".$conevent_id."','".$username."','".$cmngt_id."','Placed',NOW(),'".addslashes($etitle)."','".$username."')";
			mysql_query($ins_cmngt,$db);

			/*
				Code comment because added the changes in above => Inserting and Updating in resume_status
				This code is Modified By SARANESH.AR
			*/

			/*$que="select count(*) from resume_status where req_id='".$id_req."' and res_id='".$sinval[$i]."' AND seqnumber='".$msgunumber."' AND shift_id = '".$shift_id."'";
			$res=mysql_query($que,$db);
			$row=mysql_fetch_row($res);
			if($row[0]==0)
			{
				$ique="INSERT INTO resume_status(sno,res_id,req_id,appuser,appdate,status,pstatus,seqnumber,muser,mdate,shift_id) values ('','".$sinval[$i]."','".$id_req."','".$username."',CURRENT_DATE(),'".$manage_sno."','P','".$msgunumber."','".$username."',NOW(),'".$shift_id."')";
				mysql_query($ique,$db);

				$con_que1="select contact,company from posdesc where posid='".$id_req."'";
				$result1=mysql_query($con_que1,$db);
				$result1_arr=mysql_fetch_row($result1);
				$his_que="insert into resume_history(sno,cli_name,req_id,res_id,comp_id,appuser,appdate,status,notes,type,muser,mdate,seqnumber,shift_id) values  ('','".$result1_arr[0]."','".$id_req."','".$sinval[$i]."','".$result1_arr[1]."','".$username."',now(),'".$manage_sno."','','cand','".$username."',now(),'".$msgunumber."','".$shift_id."')";
				mysql_query($his_que,$db);
			}
			else
			{
				$que="select status from resume_status where req_id='".$id_req."' and res_id='".$sinval[$i]."' AND seqnumber='".$msgunumber."' AND shift_id = '".$shift_id."'";  
				$res=mysql_query($que,$db);
				$row=mysql_fetch_row($res);
				$mang_name=getmanagename($row[0]);
				if($mang_name=="Applied")
				{						
					$que_upd="update resume_status set status='".$managesno."', pstatus='P',muser='".$username."',mdate=NOW() where res_id='".$sinval[$i]."' and req_id='".$id_req."' AND seqnumber='".$msgunumber."' AND shift_id = '".$shift_id."'"; 
					mysql_query($que_upd,$db);

					$con_que1="select contact,company from posdesc where posid='".$id_req."'";
					$result1=mysql_query($con_que1,$db);
					$result1_arr=mysql_fetch_row($result1);

					$his_que="insert into resume_history(sno,cli_name,req_id,res_id,comp_id,appuser,appdate,status,notes,type,muser,mdate,seqnumber,shift_id) values  ('','".$result1_arr[0]."','".$id_req."','".$sinval[$i]."','".$result1_arr[1]."','".$username."',now(),'".$managesno."','','cand','".$username."',now(),'".$msgunumber."','".$shift_id."')";
					mysql_query($his_que,$db);						
				}
			}*/
		}			
	}
	/*
		END
	*/
	$upd_candjobs="update candidate_appliedjobs set status='placed' where candidate_id ='".$id."' and req_id ='".$id_req."' and (status='applied' OR status='submitted')";	
	mysql_query($upd_candjobs,$db);	

	//Function to update the consultant applied jobs once candidate is submitted
	updConAppliedJobStatus($id,$id_req,'placed');

	$sql="update candidate_list set mtime=NOW(),muser='".$username."' where sno='".$id."'";
	mysql_query($sql,$db);

	$sql="update posdesc set mdate=NOW(),muser='".$username."' where posid='".$id_req."'";
	mysql_query($sql,$db);

	$managesno=getManageSno('Needs Approval','interviewstatus');

	$where_shift_cond = "";
	if(isset($sm_sel_shifts) && !empty($sm_sel_shifts)){
	 	$where_shift_cond = "AND shift_id IN (".$sm_sel_shifts.")";
	}

	/*To Update the resume status table based on the Shift Id(for data before shift schedule enhancements)*/
	
	/*$shifts_resume_status = "select count(req_id) from resume_status WHERE res_id='".$id."' 
		AND req_id='".$id_req."' AND seqnumber='".$sub_seqnumber."' AND shift_id = '0'";*/

		$shifts_resume_status = "select count(req_id) from resume_status WHERE res_id='".$id."' 
		AND req_id='".$id_req."' AND seqnumber='".$sub_seqnumber."' AND shift_id = '".$shift_id."'";
		$shifts_resume_status_res =mysql_query($shifts_resume_status,$db);
		$shifts_resume_count = mysql_num_rows($shifts_resume_status_res);
		$shifts_resume_arr=mysql_fetch_row($shifts_resume_status_res);
		
		
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
		
		
		
	if($shifts_resume_arr[0] > 0)		
	{
		$res_upd="UPDATE resume_status SET status='".$managesno."',pstatus='P',appdate=CURRENT_DATE(),muser='".$username."',mdate=NOW() WHERE res_id='".$id."' AND req_id='".$id_req."' AND seqnumber='".$sub_seqnumber."' AND shift_id = '".$shift_id."'";
		mysql_query($res_upd,$db);
		
	}else if(!empty($where_shift_cond)){
			
		$res_upd="UPDATE resume_status SET status='".$managesno."',pstatus='P',appdate=CURRENT_DATE(),muser='".$username."',mdate=NOW() WHERE res_id='".$id."' AND req_id='".$id_req."' AND seqnumber='".$sub_seqnumber."' ".$where_shift_cond."";
		mysql_query($res_upd,$db);	
	}


	$shifts_resume_check = "select count(req_id) from resume_status WHERE res_id='".$id."' 
	AND req_id='".$id_req."' AND seqnumber='".$sub_seqnumber."' AND shift_id = '".$shift_id."'";
	$shifts_resume_check_res =mysql_query($shifts_resume_check,$db);
	$shifts_resume_check_count = mysql_num_rows($shifts_resume_check_res);
	$shifts_resume_chk_arr=mysql_fetch_row($shifts_resume_check_res);
		
	if($shifts_resume_chk_arr[0] == 0)		
	{
		$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
		$msgunumber=$thisday;
		$sub_seqnumber=$msgunumber;	
	
	
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
	
		$ins_response="INSERT INTO reqresponse(sno,emailid,posid,resumeid,rdate,seqnumber,par_id,username,stage,sub_status,muser,mdate,shift_id) values ('','".addslashes($oppr_fetch[0])."','".$id_req."','cand".$id."',NOW(),'".$msgunumber."','0','".$username."','".$managesno."','NE','".$username."',NOW(),'".$shift_id."')";
		mysql_query($ins_response,$db);
		$reqresponseId=mysql_insert_id($db);
	
	
		$ique="INSERT INTO resume_status(sno,res_id,req_id,appuser,appdate,status,pstatus,seqnumber,muser,mdate,shift_id) values ('','".$id."','".$id_req."','".$username."',CURRENT_DATE(),'".$managesno."','P','".$msgunumber."','".$username."',NOW(),'".$shift_id."')";
		mysql_query($ique,$db);

		$his_que="insert into resume_history(sno,cli_name,req_id,res_id,comp_id,appuser,appdate,status,notes,type,muser,mdate,seqnumber,shift_id) values  ('','".$pos_fetch[0]."','".$id_req."','".$id."','".$pos_fetch[3]."','".$username."',now(),'".$managesno."','','cand','".$username."',now(),'".$msgunumber."','".$shift_id."')";
		mysql_query($his_que,$db);
	}
	
	if($place_link!="place_cand")
	{
		$manageplc_sno=getManageSno('Placed','interviewstatus');
		$con_que1="select contact,company from posdesc where posid='".$id_req."'";
		$result1=mysql_query($con_que1,$db);
		$result1_arr=mysql_fetch_row($result1);

		$his_que="insert into resume_history (sno,cli_name,req_id,res_id,comp_id,appuser,appdate,status,notes,type,muser,mdate,seqnumber,shift_id) values ('','".$result1_arr[0]."','".$id_req."','".$id."','".$result1_arr[1]."','".$username."',now(),'".$manageplc_sno."','','cand','".$username."',now(),'".$sub_seqnumber."','".$shift_id."')"; 
		mysql_query($his_que,$db);	
	}
	// END
	//For updating Interview Count.
	updateTotalInterviewsCount($id_req);

	$page152=explode("|",$page151);
	
	if($page152[84] == '' || is_null($page152[84]))
		$page152[84] = $deptjoborder;

	$deptId_jo = $page152[84];

	if($page152[44]=='Direct')
	{	
		// This code is For Direct Job Order >> WorkSite Code 
		$page152[85] = $page152[74];
    		$page152[86] = $page152[75];
    		$page152[87] = $page152[76];
    		$page152[88] = $page152[77];
    		$page152[89] = $page152[78];
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

	$sque  = "select candid,ctype,sno,supid,username,wotc_mja_status,tcc_status from candidate_list where username='".$userid."'";
	$sres = mysql_query($sque,$db);
	$srow = mysql_fetch_row($sres);

	$candtype = $srow[1];
	$candidateId=$srow[2];
	$candempId=$srow[0];
	$candlist_user = $srow[4];
	$supid = $srow[3];
	$wotc_mja_status = $srow[5];
	$tcc_status = $srow[6];

	// For Employees..
	$sql_loc="SELECT contact_manage.serial_no FROM contact_manage, hrcon_compen WHERE contact_manage.status != 'BP' AND hrcon_compen.location = contact_manage.serial_no AND hrcon_compen.username = '".$username."' AND hrcon_compen.ustatus = 'active'";
	$res_loc=mysql_query($sql_loc,$db);
	$fetch_loc=mysql_fetch_row($res_loc);
	$loc_user=$fetch_loc[0];

	$jobtypeChk	= "";
	
	//This variables are used for Perdiem Shift Scheduling timeslots
	$perdiem_candtype='';
	$perdiem_uid='';
	$perdiem_userid='';
	$perdiem_timeslotvalues='';
	$perdiem_instype='';
	$perdiem_id_req='';
	//End 

	if($candtype == "Employee")
	{
		$uid=$srow[0];
		$str_emp=substr($srow[0], 0, 7);

		if($str_emp!='empprof')
			$que = "update emp_list set estatus = 'newreq', approveuser = '".$username."' where sno = ".trim(str_replace('emp','',$srow[0]));
		else
			$que = "update empprofile_list set estatus = 'newreq' where sno = ".trim(str_replace('empprof','',$srow[0]));
		mysql_query($que,$db);

		//Modified Time Updation in Employee table
		EmpModifiedUpdate('username',$uid);

		if($page152[44]=='Direct' || $page152[44]=='Internal Direct')
			$jobtypeChk=$page152[44];

		if($page152[44]=='Direct')
			$typeOfPlacementForEmplyoee="DIRECT";
	}
	else
	{
		if($page152[44]=='Direct')
			$typeOfPlacement="DIRECT";

		// For My Candidates..
		if($srow[0] == "")
		{
			$uid = createConsultant($srow[2],$srow[4]);

			// For Realtion with Vendors in Accounting..
			CreateAccount($uid,$id_req,$uname,$userid);
			$cand_VenId=$uid;
			
			if(SHIFT_SCHEDULING_ENABLED == 'Y' && $shift_type == "regular") 
			{
				//update candidate time slots with the shift status busy and insert into consultant table from candidate record
				updCandTimeSlots_insFrmCandToConsultant($db,$userid,$uid,$timeslotvalues,'insert',$id_req);
			}

			if ($shift_type == "perdiem") {

				$perdiem_candtype= 'MyCandidates';
				$perdiem_uid= $uid;
				$perdiem_userid= $userid;
				$perdiem_timeslotvalues= $timeslotvalues;
				$perdiem_instype='insert';
				$perdiem_id_req= $id_req;
			}
		}
		else
		{
			// For Candidates..
			$uid = $srow[0];

			$chk_qry="SELECT count(1) FROM consultant_list WHERE username = '".$uid."'";
			$chkres=mysql_query($chk_qry,$db);
			$chkrow=mysql_fetch_row($chkres);

			if($chkrow[0]==0)
			{
				$uid = createConsultant($srow[2],$srow[4]);
				CreateAccount($uid,$id_req,$uname,$userid);
				$cand_VenId=$uid;
				
				if(SHIFT_SCHEDULING_ENABLED == 'Y' && $shift_type == "regular") 
				{
					//update candidate time slots with the shift status busy and insert into consultant table from candidate record
					updCandTimeSlots_insFrmCandToConsultant($db,$userid,$uid,$timeslotvalues,'insert',$id_req);
				}

				if ($shift_type == "perdiem") {

					$perdiem_candtype= 'Candidates';
					$perdiem_uid= $cand_VenId;
					$perdiem_userid= $userid;
					$perdiem_timeslotvalues= $timeslotvalues;
					$perdiem_instype='insert';
					$perdiem_id_req= $id_req;
				}
			}
			else
			{
				UpdateConsultant($uid,$id_req,$uname,$userid);
				$cand_VenId=$uid;
				
				if(SHIFT_SCHEDULING_ENABLED == 'Y' && $shift_type == "regular") 
				{
					//update candidate time slots with the shift status busy and insert into consultant table from candidate record
					updCandTimeSlots_insFrmCandToConsultant($db,$userid,$uid,$timeslotvalues,'update',$id_req);
				}

				if ($shift_type == "perdiem") {

					$perdiem_candtype= 'Candidates';
					$perdiem_uid= $cand_VenId;
					$perdiem_userid= $userid;
					$perdiem_timeslotvalues= $timeslotvalues;
					$perdiem_instype='update';
					$perdiem_id_req= $id_req;
				}
			}			
		}

		checkvenrel($supid,$userid,$page152[46],$cand_VenId);
		$conusername=$uid;
		//Checking consultant w4 records if no records found then inserting record into it.
		checkConsultantw4Records($conusername);
	}

	// To make relation ship with vendorsubcon and candidates and consultants/employees
	function checkvenrel($supid,$userid,$venid,$cand_VenId)
	{
		global $maildb,$db;

		if($supid!=0)
		{
			$ven_query = "select count(venid) from vendorsubcon where subid='".$userid."' and venid='".$venid."'";
			$ven_res = mysql_query($ven_query,$db);
			$ven_row = mysql_fetch_row($ven_res);
			if($ven_row[0] == 0)
			{
				$que = "insert into vendorsubcon (sno, subid, venid, empid) values('','".$userid."','".$venid."','".$cand_VenId."')";
				mysql_query($que,$db);
			}
		}
	}

	function checkConsultantw4Records($conusername)
	{
		global $username,$db;

		// Checking consultant_w4 is having record or not
		$consult_w4 = "select username from consultant_w4 where username='".$conusername."'";
		$consult_w4_res = mysql_query($consult_w4,$db);
		if(mysql_num_rows($consult_w4_res)>0){
		}else{
			$instconsw4="INSERT INTO consultant_w4 (username,tax) VALUES('".$conusername."','W-2')";
			mysql_query($instconsw4,$db);
		}

	}

	//To get classid from the department_accounts table with department id
	$page152[83] = displayDepartmentName($page152[84],true);

	// Updating Assignment data..
	$appno=updateAssign($page152,$uid,$candtype,$id_req,$empvalues,$commval,$ratetype,$paytype,$confirmToClose,$userid,$attention,$roleName,$roleOverWrite,$corp_code, $timeslotvalues, $burdenTypeDetails, $burdenItemDetails,$joindustryid,$schedule_display, $comm_bill_burden, $bill_burdenTypeDetails, $bill_burdenItemDetails, $roleEDisable,$shift_id,$shift_st_time,$shift_et_time,$shift_type,$candrn,$licode,$daypay,$federal_payroll,$nonfederal_payroll,$contractid,$classification,$placement_timesheet_layout_preference);
	
	if(SHIFT_SCHEDULING_ENABLED == 'Y') 
	{
		if($candtype != "Employee" && $shift_type == "perdiem")
		{	
			$expappnoVal1 = explode("|",$appno);
			$placementjobSno = $expappnoVal1[3];
			
			$perdiem_timeslotvalues = $objPerdiemScheduleDetails->placementPerdiemSelQryForOldShiftStr($placementjobSno,$candrn,$perdiem_userid,'candidate_sm_timeslots');
			//update candidate time slots with the shift status busy and insert into consultant table from candidate record
			updCandTimeSlots_insFrmCandToConsultant($db,$perdiem_userid,$perdiem_uid,$perdiem_timeslotvalues,$perdiem_instype,$perdiem_id_req);
		}
		//updating the consultant time slot assignment numbers 
		updConsTimeSlotsAssNo($db,$uid);
	}

	$expappnoVal = explode("|",$appno);
	$shiftSno		= $shift_id;
	$shift_rates_data 	= "sm_rates_".$shiftSno;	
	if($candtype == "Employee") 
	{
		if($$shift_rates_data!="|" && $$shift_rates_data!="")
		{
												
			$type_order_id 	= $expappnoVal[2];

			$mode_rate_type = "hrcon";

			$objSchSchedules->updateShiftRates($type_order_id,$$shift_rates_data,$shiftSno,$mode_rate_type);
		}
		else
		{										
			$mode_rate_type = "hrcon";
			$type_order_id = $expappnoVal[2];
						
			$ratesObj->defaultRatesInsertion($mulRatesVal,$in_ratesCon);
			$ratesObj->multipleRatesInsertion($rateType,$billableR,$mulpayRateTxt,$payratePeriod,$payrateCurrency,$mulbillRateTxt,$billratePeriod,$billrateCurrency,$taxableR);
		}
	}
	else
	{
		if($$shift_rates_data!="|" && $$shift_rates_data!="")
		{
			$mode_rate_type = "consultant";
			$type_order_id 	= $expappnoVal[2];
			$objSchSchedules->updateShiftRates($type_order_id,$$shift_rates_data,$shiftSno,$mode_rate_type);
		}
		else{
			$mode_rate_type = "consultant";

			$type_order_id = $expappnoVal[2];	
			$ratesObj->defaultRatesInsertion($mulRatesVal,$in_ratesCon);
			$ratesObj->multipleRatesInsertion($rateType,$billableR,$mulpayRateTxt,$payratePeriod,$payrateCurrency,$mulbillRateTxt,$billratePeriod,$billrateCurrency,$taxableR);
		}
	}

	if($$shift_rates_data!="|" && $$shift_rates_data!="")
	{
		$mode_rate_type = "placement";
		$type_order_id = $expappnoVal[3];
		$objSchSchedules->updateShiftRates($type_order_id,$$shift_rates_data,$shiftSno,$mode_rate_type);
	}
	else
	{
		$mode_rate_type = "placement";
		$type_order_id = $expappnoVal[3];	
		$ratesObj->defaultRatesInsertion($mulRatesVal,$in_ratesCon);
		$ratesObj->multipleRatesInsertion($rateType,$billableR,$mulpayRateTxt,$payratePeriod,$payrateCurrency,$mulbillRateTxt,$billratePeriod,$billrateCurrency,$taxableR);
	}
	//Function expire date details updation inhotjobs bases on no. of positions
	UpdateExpireDate_FilledJO('posdesc',$id_req);// prasadd (04/10/2008)

	// For Activities..
	userHistory($page152,$uid,$candtype,$uname);

	// Called Functions..
	function createConsultant($sid,$suser)
	{
		global $username,$maildb,$db,$objHRMCredentials;

		$canduser=$suser;

		//insert into consultant_list 
		$ins_con_list="INSERT INTO consultant_list(serial_no, approveuser, astatus, stime, accessto,mtime,muser) VALUES('','".$username."','backup',NOW(),'".$username."',NOW(),'".$username."')";
		mysql_query($ins_con_list,$db);
		$lastins_consultid=mysql_insert_id($db);

		$userlead="con".$lastins_consultid;

		//Getting recruiter id is there r not for candidate
		$rec_que = "SELECT supid from candidate_list WHERE username = '".$canduser."'";
		$q1 = mysql_query($rec_que,$db);
		$chksup_res=mysql_fetch_array($q1);
		$chksup_id=$chksup_res['supid'];
        	$v_business_name=$v_city=$v_add1=$v_add2=$v_state=$v_city='';
		if($chksup_id != '0' && $chksup_id != ""){
		    $tax="C-to-C";
		    /** sanghamitra:getting recruiter company details **/
		    $rec_com_que = "SELECT com.cname as bname,com.address1 as add1,com.address2 as add2,com.city as city,com.state as state,com.zip as zip from staffoppr_contact con LEFT JOIN staffoppr_cinfo com on con.csno = com.sno LEFT JOIN manage t1 ON com.compstatus = t1.sno WHERE con.sno= '".$chksup_id."' AND t1.name LIKE '%vendor%' AND t1.type='compstatus'";
		    $q2 = mysql_query($rec_com_que,$db);
			$chkcom_res=mysql_fetch_array($q2);
			
		    $v_business_name=$chkcom_res['bname'];
			$v_city=$chkcom_res['city'];
			$v_add1=$chkcom_res['add1'];
			$v_add2=$chkcom_res['add2'];
			$v_state=$chkcom_res['state'];
			$v_zip=$chkcom_res['zip'];
		}else{
		    $tax="W-2";
		}    
		

		$query1 = "SELECT country,state from candidate_general WHERE username = '".$canduser."'";
		$query1_res = mysql_query($query1,$db);
		$res = mysql_fetch_array($query1_res);

		$state_result = explode('|AKKENEXP|',getStateIdAbbr($res[1],$res[0]));
		$getstateid=$state_result[2];
		$getstatename=$state_result[1];

		//updating state abbr and state id in table - ANKIT
		if($getstateid != '0') {
			$StateAbbrSql = "SELECT state_abbr from state_codes WHERE state_name = '$getstatename' OR state_id = '$getstateid'";
	        $StateAbbrRes = mysql_query($StateAbbrSql,$db);
	        $StateAbbrRow = mysql_fetch_assoc($StateAbbrRes);

	        $AbbrState = $StateAbbrRow['state_abbr'];
		} else {
			$AbbrState = $getstatename;
		}

		$sql_que = "INSERT INTO consultant_general (sno, username, fname, mname, lname, email, profiletitle, address1, address2, city, stateid, country, zip, wphone, hphone, mobile, fax, other, prefix, cphone, cmobile, cfax, cemail, wphone_extn, hphone_extn, other_extn, state,alternate_email,other_email) SELECT '','".$userlead."',fname,mname,lname,email,profiletitle, address1, address2, city, '".$getstateid."', country, zip, IF(wphone='---','',wphone), IF(hphone='---','',hphone), IF(mobile='--','',mobile), IF(fax='--','',fax), other, prefix, cphone, cmobile, cfax, cemail, wphone_extn, hphone_extn, other_extn, '".addslashes($AbbrState)."',alternate_email,other_email FROM candidate_general  WHERE username = '".$canduser."'";
		mysql_query($sql_que,$db);

		$sql_que = "INSERT INTO consultant_prof (sno, username, objective, summary, pstatus, ifother, addinfo, availsdate, availedate) SELECT '','".$userlead."', objective, summary, pstatus, ifother, addinfo, if(availsdate='immediate','immediate',date_format(availsdate,'%m-%d-%Y')), if(availedate='immediate','immediate',date_format(availedate,'%m-%d-%Y')) FROM candidate_prof WHERE username = '".$canduser."'";
		mysql_query($sql_que,$db);

		$sql_que = "INSERT INTO consultant_skills (sno, username, skillname, lastused, skilllevel, skillyear, manage_skills_id) SELECT '','".$userlead."', skillname, lastused, skilllevel, skillyear, manage_skills_id FROM candidate_skills WHERE username = '".$canduser."'";
		mysql_query($sql_que,$db);

		$sql_que = "INSERT INTO consultant_skill_cat_spec (sno, username, dept_cat_spec_id, type) SELECT '','".$userlead."', dept_cat_spec_id, type FROM candidate_skill_cat_spec WHERE username = '".$canduser."'";
		mysql_query($sql_que,$db);

		$sql_que = "INSERT INTO consultant_edu (sno, username, heducation, educity, edustate, educountry, edudegree_level, edudate) SELECT '','".$userlead."', heducation, educity, edustate, educountry, edudegree_level, edudate FROM candidate_edu WHERE username = '".$canduser."'";
		mysql_query($sql_que,$db);

		$sql_que = "INSERT INTO consultant_work (sno, username, cname, city, state, country, ftitle, sdate, edate, wdesc, compensation_beginning, leaving_reason) SELECT '','".$userlead."', cname, city, state, country, ftitle, sdate, edate, wdesc, compensation_beginning, leaving_reason FROM candidate_work WHERE username = '".$canduser."'";
		mysql_query($sql_que,$db);

		$sql="SELECT sno, username, desirejob, desirelocation, desirestatus, city, state, country, amount, currency, period from candidate_pref WHERE username = '".$canduser."'";
		$result=mysql_query($sql,$db);
		$row=mysql_fetch_assoc($result);
		$c_rate=$row['amount'];

        $sql = "INSERT INTO consultant_desire (sno, username, desirejob, desirestatus, desireamount, desiretype, mpayment, desirelocation) VALUES ('', '".$userlead."', '".$row['desirejob']."', '".$row['desirestatus']."', '".$row['amount']."', '".$row['currency']."', '".$row['period']."', '".$row['desirelocation']."')";
		mysql_query($sql,$db);

		$sql = "INSERT INTO consultant_location (sno, username, city, state, country) VALUES ('', '".$userlead."', '".addslashes($row['city'])."', '".addslashes($row['state'])."', '".$row['country']."')";
		mysql_query($sql,$db);

		$sql_que = "INSERT INTO consultant_aff (sno, username, affcname, affrole, affsdate, affedate) SELECT '','".$userlead."', affcname, affrole, affsdate, affedate FROM candidate_aff WHERE username = '".$canduser."'";
		mysql_query($sql_que,$db);
                
        $sql_cand_que = "SELECT '','".$userlead."', name, company, title, phone, email, rship, secondary, mobile ,notes ,doc_id  FROM candidate_ref WHERE username = '".$canduser."'";
        $sql_cand_row = mysql_query($sql_cand_que,$db);
        while($row_sub_ref=mysql_fetch_row($sql_cand_row))
		{
			$ins_doc_query= "INSERT INTO contact_doc (sno, con_id, username, title, docname, body, sdate, doctype) SELECT '','".$userlead."',username, title, docname, body, NOW(), doctype FROM contact_doc WHERE con_id = '".$canduser."' AND sno='".$row_sub_ref[11]."'"; 
			mysql_query($ins_doc_query,$db);
			$contact_doc_id= mysql_insert_id($db);

			$sql_que_main = "INSERT INTO consultant_ref (sno, username, name, company, title, phone, email, rship, secondary, mobile, notes, doc_id) VALUES('','".$userlead."','".addslashes($row_sub_ref[2])."','".addslashes($row_sub_ref[3])."','".addslashes($row_sub_ref[4])."','".$row_sub_ref[5]."','".$row_sub_ref[6]."','".$row_sub_ref[7]."','".$row_sub_ref[8]."','".$row_sub_ref[9]."','".addslashes($row_sub_ref[10])."','".$contact_doc_id."')";
			mysql_query($sql_que_main,$db);
		}
		
		$que="select * from candidate_skills where username='".$canduser."'";
		$res_sub=mysql_query($que,$db);
		while($row_sub=mysql_fetch_row($res_sub))
		{
			if($conskills=="")
				$conskills=$row_sub[2]."(".$row_sub[5].")";
			else
				$conskills.=", ".$row_sub[2]."(".$row_sub[5].")";
		}

		$sql="select CONCAT_WS(' ',fname,mname,lname),email from candidate_general where username='".$canduser."'";
		$result=mysql_query($sql,$db);
		$row=mysql_fetch_row($result);

		$sql="select pstatus,availsdate from candidate_prof where username='".$canduser."'";
		$result1=mysql_query($sql,$db);
		$row1=mysql_fetch_row($result1);

		$sql = "UPDATE consultant_list SET username='".$userlead."', name='".addslashes($row[0])."', email='".addslashes($row[1])."', status='".$row1[0]."', avail='".$row1[1]."', rate='".$c_rate."', skills='".addslashes($conskills)."' WHERE serial_no='".$lastins_consultid."'";
		mysql_query($sql,$db);

		$que="insert into consultant_status(sno,username,arrivaldate,ssndate) values('','".$userlead."','','')";
		mysql_query($que,$db);

		//Candidate Information to consultant tables
		$cand_w4 = "select username from candidate_w4 where username='".$canduser."'";
		$cand_w4_res = mysql_query($cand_w4,$db);
		if(mysql_num_rows($cand_w4_res)>0){
			 $que="insert into consultant_w4(sno,username,tax,fstatus,tnum,fwh,swh,sswh,mwh,cfwh,cswh,csswh,cmwh,aftaw,aftaw_curr,caftaw,astaw,castaw,tstatetax,federal_exempt,state_withholding,companycode,fsstatus,state_exempt,multijobs_spouseworks,qualify_child_amt,other_dependents_amt,claim_dependents_total,other_income_amt,deduction_amt,alt_filling_status) SELECT '','".$userlead."',tax,fstatus,tnum,fwh,swh,sswh,mwh,cfwh,cswh,csswh,cmwh,aftaw,aftaw_curr,caftaw,astaw,castaw,tstatetax,federal_exempt,state_withholding,companycode,fsstatus,state_exempt,multijobs_spouseworks,qualify_child_amt,other_dependents_amt,claim_dependents_total,other_income_amt,deduction_amt,alt_filling_status FROM candidate_w4 WHERE username = '".$canduser."' ";
		}
		else{
		
			/*sanghamitra :Changed the query to insert vendor details (bussiness name,city ,address, state,zip)to consultant_w4 */
		    $que="insert into consultant_w4(sno,username,tax,fstatus,tnum,fwh,swh,sswh,mwh,cfwh,cswh,csswh,cmwh,business_name,address1,address2,city,state,zip) values('','".$userlead."','".$tax."','','','','','','','','','','','".addslashes($v_business_name)."','".addslashes($v_add1)."','".addslashes($v_add2)."','".addslashes($v_city)."','".addslashes($v_state)."','".$v_zip."')";
		}
		mysql_query($que,$db);
		
		// Checking consultant_w4 is having record or not
		$cand_w4 = "select username from consultant_w4 where username='".$userlead."'";
		$cand_w4_res = mysql_query($cand_w4,$db);
		if(mysql_num_rows($cand_w4_res)>0){
		}else{
			$instconsw4="INSERT INTO consultant_w4 (username,tax) VALUES('".$userlead."','W-2')";
			mysql_query($instconsw4,$db);
		}

		$cand_dep = "select username from candidate_deposit where username='".$canduser."'";
		$cand_dep_res = mysql_query($cand_dep,$db);
		if(mysql_num_rows($cand_dep_res)>0){
			$que="insert into consultant_deposit(sno,username,bankname,name,bankrtno,bankacno,delivery_method,acc1_type,acc1_payperiod,acc1_amt,acc2_name,acc2_bankname,acc2_bankrtno,acc2_bankacno,acc2_type,acc2_payperiod,acc2_amt) SELECT '','".$userlead."',bankname,name,bankrtno,bankacno,delivery_method,acc1_type,acc1_payperiod,acc1_amt,acc2_name,acc2_bankname,acc2_bankrtno,acc2_bankacno,acc2_type,acc2_payperiod,acc2_amt FROM candidate_deposit WHERE username = '".$canduser."' ";
			 mysql_query($que,$db);
		}
		
		$cand_per = "select username from candidate_personal where username='".$canduser."'";
		$cand_per_res = mysql_query($cand_per,$db);
		if(mysql_num_rows($cand_per_res)>0){
			 $que="insert into consultant_personal(sno,username,d_birth,ssn,ssn_hash) SELECT '','".$userlead."',d_birth,ssn,ssn_hash FROM candidate_personal WHERE username = '".$canduser."' ";
			 mysql_query($que,$db);
		}
		
		$cand_emer = "select username from candidate_emergency where username='".$canduser."'";
		$cand_emer_res = mysql_query($cand_emer,$db);
		if(mysql_num_rows($cand_emer_res)>0){
			 $que="insert into consultant_emergency(sno,username,fname,lname,relation,pphone,sphone) SELECT '','".$userlead."',fname,lname,relation,pphone,sphone FROM candidate_emergency WHERE username = '".$canduser."' ";
			 mysql_query($que,$db);
		}
		
		//Candidate Credentials to Consultant Credentials
		$objHRMCredentials->insertDataFrmCandToConsultTable($canduser, $userlead, $lastins_consultid, 'insert');

		// for Resume..
		$resume_que = "select sno from con_resumes where username='".$canduser."'";
		$resume_res=mysql_query($resume_que,$db);
		$resume_cnt = mysql_num_rows($resume_res);
		if( $resume_cnt > 0 )
		{
			$ins_que = "insert into con_resumes(sno, username, res_name, type, status, added, markadd, filetype, filesize, filecontent) SELECT '', '".$userlead."', res_name, 'con', status, added, markadd, filetype, filesize, filecontent FROM con_resumes WHERE username='".$canduser."' ";
			mysql_query($ins_que,$db);
		}

		$sql="update candidate_list set candid='".$userlead."' where username='".$canduser."'";
		mysql_query($sql,$db);

		return $userlead;
	}

	function CreateAccount($conusername,$id_req,$uname,$userid)
	{
		global $username,$maildb,$db,$typeOfPlacement;

		$que_con="select count(*) from applicants where astatus not in ('HREJ','backup') and username='".$conusername."'";
		$res_con=mysql_query($que_con,$db);
		$row_con=mysql_fetch_row($res_con);
		if($row_con[0]==0)
		{
			$que="select * from candidate_skills where username='".$userid."'";
			$reb=mysql_query($que,$db);

			while($row_sub=mysql_fetch_row($reb))
			{
				if($conskills=="")
					$conskills=$row_sub[2]."(".$row_sub[5].")";
				else
					$conskills.=", ".$row_sub[2]."(".$row_sub[5].")";
			}

			$sql="select CONCAT_WS(' ',fname,mname,lname),email from candidate_general where username='".$userid."'";
			$result=mysql_query($sql,$db);
			$row=mysql_fetch_row($result);

			$sql="select pstatus,availsdate from candidate_prof where username='".$userid."'";
			$result1=mysql_query($sql,$db);
			$row1=mysql_fetch_row($result1);

			$sql="SELECT amount,desirelocation from candidate_pref WHERE username = '".$userid."'";
			$result2=mysql_query($sql,$db);
			$row2=mysql_fetch_assoc($result2);

			$sql="SELECT wotc_mja_status,tcc_status from candidate_list WHERE username = '".$userid."'";
			$result3=mysql_query($sql,$db);
			$row3=mysql_fetch_assoc($result3);

			if($typeOfPlacement=='DIRECT')
				$astus='hire';

			$sql = "INSERT INTO applicants (serial_no, username, name, email, jobtitle, status, avail, rate, plocation, skills, reffered, astatus, apmark, con_person, type, recruiter, hr, admin, stime, wotc_mja_status,tcc_status) VALUES ('', '".$conusername."', '".addslashes($row[0])."', '".addslashes($row[1])."', '".$id_req."', '".$row1[0]."', '".$row1[1]."', '".$row2[0]."', '".$row2[1]."', '".addslashes($conskills)."', '', '".$astus."', '".$username."', '', 'con', '', '$uname"."+F', '', NOW(), '".$row3['wotc_mja_status']."','".$row3['tcc_status']."')";
			mysql_query($sql,$db);
			$app_id = mysql_insert_id($db);

			if($typeOfPlacement!='DIRECT')
			{
				/////////////////// UDF Values for HRM Hiring Management Records when candidate placed ///////
				include_once('custom/custome_save.php');		
				$directEmpUdfObj = new userDefinedManuplations();
				$directEmpUdfObj->insertHrmApplicants(3, $app_id, $conusername);
				/////////////////////////////////////////////////////////////////////////////////
			}

			$que="insert into apphistory(sno,username,notes,sdate,appuser,status) select '',username,'Forwarded as a Employee for Hiring from Requirements',CURRENT_DATE,'".$username."','Forwarded' from candidate_list where username='".$userid."'";
			mysql_query($que,$db);
		}
	}

	function updateAssign($page152,$conusername,$candtype,$id_req,$empvalues,$commval,$ratetype,$paytype,$confirmToClose,$userid,$attention,$roleName,$roleOverWrite,$corp_code, $timeslotvalues, $burdenTypeDetails, $burdenItemDetails,$joindustryid,$schedule_display, $comm_bill_burden, $bill_burdenTypeDetails, $bill_burdenItemDetails, $roleEDisable,$shift_id=NULL,$shift_st_time,$shift_et_time,$shift_type,$candrn,$licode,$daypay,$federal_payroll,$nonfederal_payroll,$contractid,$classification,$placement_timesheet_layout_preference='')
	{
		global $username,$maildb,$db,$Emp_Name,$emp_assig_sno,$hr_assig_sno,$assignment_name,$acceptJobtitle,$job_title,$common_proj,$loc_user,$companyuser,$maindb,$sub_seqnumber;

		if(SHIFT_SCHEDULING_ENABLED == 'Y')
		{
			global $objScheduleDetails;
		}
		
		$assg_pusername=get_Assginment_Seq();

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

		if($candtype == "Employee")
		{
			$str_emp=substr($conusername,0,7);
			if($str_emp!='empprof')
				$sel_que = "select username,sno from emp_list where sno = ".trim(str_replace('emp','',$conusername));
			else
				$sel_que = "select username,sno from empprofile_list where sno = ".trim(str_replace('empprof','',$conusername));
			$res_que = mysql_query($sel_que,$db);
			$emp_user = mysql_fetch_row($res_que);

			if ($shift_id =="" || $shift_id == "0") {
				$shift_type = "";
			}

			if(SHIFT_SCHEDULING_ENABLED == 'N') 
			{
				//Queries to update the number of positions
				$que="select closepos from posdesc where posid='".$id_req."'";
				$res=mysql_query($que,$db);
				$row=mysql_fetch_row($res);
				$cl_pos=$row[0];
	
				$que="update posdesc set closepos='".($cl_pos+1)."' where posid='".$id_req."'";
				mysql_query($que,$db);
	
				$que_hjobs="update hotjobs set closepos='".($cl_pos+1)."' where req_id ='".$id_req."' and status!='BP'"; 
				mysql_query($que_hjobs,$db);
				$shift_type = "";
			}

			$sel_app = "select username,jobtitle from applicants where username='".$emp_user[0]."' OR username like '%($emp_user[0])'";
			$res_app = mysql_query($sel_app,$db);
			$fetch_app = mysql_fetch_row($res_app);
			$num_app=mysql_num_rows($res_app);

			if($num_app!=0)
			{
				if($fetch_app[1]=='')
					$upd_app="update applicants set jobtitle='".$id_req."' where username='".$emp_user[0]."' OR username like '%($emp_user[0])'";
				else
					$upd_app="update applicants set jobtitle=concat(jobtitle,',','".$id_req."') where username='".$emp_user[0]."' OR username like '%($emp_user[0])'";
			}
			else
			{
				$upd_app="insert into applicants(serial_no,username,name,email,jobtitle,status,avail,rate,plocation,skills,reffered ,astatus,apmark,con_person,type,recruiter,hr,admin,stime) values ('','".$emp_user[0]."','','','".$id_req."','','','".$page152[4]."','','','','hire','".$username."','','PE','','','',NOW())";
			}
			mysql_query($upd_app,$db);
			$emp_id = $emp_user[1];

			$projectStatus="OP";
			$assignStatus="pending";

			$que="'','".$emp_user[0]."','".$id_req."','".$page152[0]."','".$page152[1]."','".addslashes($page152[2])."','".addslashes($page152[3])."','".$page152[4]."','".$page152[5]."','".$page152[6]."','".$page152[7]."','".$page152[8]."','".$page152[9]."','".$page152[10]."','".$page152[11]."','".$expdate."','".$page152[13]."','".$page152[14]."','".$page152[15]."','".$hiredate."','".addslashes($page152[17])."','".$page152[18]."','".$page152[19]."','".$page152[20]."','".addslashes($page152[21])."','".$page152[22]."','".$page152[23]."','".$page152[24]."','".$page152[25]."','".$page152[26]."','".$page152[27]."','".$page152[28]."','".$page152[29]."','".$page152[30]."','".$page152[31]."','".$page152[32]."','".$page152[33]."','".$page152[34]."','".addslashes($page152[35])."','".$page152[36]."','".$page152[37]."','".addslashes($page152[38])."','".addslashes($page152[39])."','".addslashes($page152[40])."','".addslashes($page152[41])."','".addslashes($page152[42])."','".addslashes($page152[43])."','".$assg_pusername."',NOW(),'".addslashes($page152[45])."','".$assignStatus."','closing placement','progress','".addslashes($projectStatus)."','".addslashes($page152[47])."','".addslashes($page152[48])."','".$page152[50]."','".$page152[51]."','".$page152[52]."','".$page152[53]."','".$page152[54]."','".$page152[55]."','".$page152[56]."','".$page152[57]."','".$username."',now(),'".$page152[58]."','".$page152[59]."','".$page152[60]."','".$page152[61]."','".$page152[62]."','".$page152[63]."','".addslashes($page152[64])."','".$loc_user."','".$page152[65]."','".$page152[66]."','".$page152[67]."','".$page152[68]."','".$page152[69]."','".$page152[70]."','".addslashes($page152[71])."','".addslashes($page152[72])."','".$page152[73]."','".$username."',NOW(),NOW(),'".$page152[74]."','".$page152[75]."','".$page152[76]."','".$page152[77]."','".$page152[78]."','".$page152[79]."','".$page152[80]."','".$page152[81]."','".$assg_pusername."','".$page152[83]."','".$page152[84]."','".addslashes($attention)."','".$corp_code."','".$joindustryid."','".$schedule_display."','".$comm_bill_burden."','".$shift_id."','',NOW(),'".$shift_st_time."','".$shift_et_time."','".$shift_type."','".$page152[88]."','".$daypay."','".$licode."','".$federal_payroll."','".$nonfederal_payroll."','".$contractid."','".$classification."','".$placement_timesheet_layout_preference."'";
			

		$ins_hrcon = "INSERT INTO hrcon_jobs (sno,username,posid,jotype,catid,postitle,refcode,posstatus,vendor,contact,client,manager,candidate,endclient,s_date,exp_edate,posworkhr,timesheet,e_date,hired_date,reason,rate,rateperiod,rateper,project,bamount,bperiod,bcurrency,pamount,pperiod,pcurrency,emp_prate,otrate,ot_period,ot_currency,placement_fee,placement_curr,bill_contact,bill_address,wcomp_code,imethod,tsapp,bill_req,service_terms,hire_req,addinfo,notes,pusername,rtime,avg_interview,ustatus,modulename,assg_type,jtype,iterms,pterms,calctype,burden,margin,markup,prateopen_amt,brateopen_amt,prateopen,brateopen,owner,cdate,otprate_amt,otprate_period,otprate_curr,otbrate_amt,otbrate_period,otbrate_curr,payrollpid,offlocation,double_prate_amt,double_prate_period,double_prate_curr,double_brate_amt,double_brate_period,double_brate_curr,po_num,department,job_loc_tax,muser,mdate,date_placed,diem_lodging,diem_mie,diem_total,diem_period,diem_currency,diem_billable,diem_taxable,diem_billrate,assign_no,classid,deptid,attention,corp_code, industryid,schedule_display,bill_burden,shiftid,assg_status,udate,starthour,endhour,shift_type,worksite_code,daypay,licode,federal_payroll,nonfederal_payroll,contractid,classification,ts_layout_pref)  values(".$que.")";
		

			$hres	= mysql_query($ins_hrcon,$db);
			$hr_id 	= mysql_insert_id($db);
			
			//Update Worksite_id to hrcon_jobs from contact_manage--Mohan
			$cm_qry = "select serial_no from contact_manage where loccode='".$page152[88]."'";
			$rescm_qry = mysql_query($cm_qry,$db);
			$worksite_id=mysql_fetch_row($rescm_qry);
			$updateWorksiteid = "UPDATE hrcon_jobs SET worksite_id = '".$worksite_id[0]."' WHERE sno='".$hr_id."'";
			mysql_query($updateWorksiteid,$db);
			/////////////////// UDF migration to customers ///////////////////////////////////
			include_once('custom/custome_save.php');		
			$directEmpUdfObj = new userDefinedManuplations();
			//$directEmpUdfObj->insertCrmJOToAssgn(7, $emp_id, $id_req);
			//Changed to store the hrconjobs->sno in cdf assignments table rec_id - vilas.B
			$directEmpUdfObj->insertUserDefinedData($hr_id, 7);
			/////////////////////////////////////////////////////////////////////////////////
			
			if($hr_id != 0)
			{
				saveBurdenDetails($burdenTypeDetails,$burdenItemDetails,'hrcon_burden_details','hrcon_jobs_sno','insert',$hr_id);
				saveBillBurdenDetails($bill_burdenTypeDetails,$bill_burdenItemDetails,'hrcon_burden_details','hrcon_jobs_sno','insert',$hr_id);
				if(SHIFT_SCHEDULING_ENABLED == 'Y')
				{
					if($shift_type=='regular')
					{
						insPlacementTimeSlots($db,'hrconjob_sm_timeslots',$hr_id,$timeslotvalues,$id_req);
					}
					else if($shift_type=='perdiem')
					{
						//inserting the time slots data for perdiem schedule management for placement job
						insPerdiemPlacementTimeSlots($db,'hrconjob_perdiem_shift_sch',$hr_id,$id_req,$userid,$assg_pusername);
					}
				}
			}
			
			setDefaultEntityTaxes('Assignment',$page152[7],0,0,$emp_user[0],$assg_pusername);

			$ccid=$hr_id."|";

			$place_que = "INSERT INTO placement_jobs (sno,username,posid,jotype,catid,postitle,refcode,posstatus,vendor,contact,client,manager,candidate,endclient,s_date,exp_edate,posworkhr,timesheet,e_date,hired_date,reason,rate,rateperiod,rateper,project,bamount,bperiod,bcurrency,pamount,pperiod,pcurrency,emp_prate,otrate,ot_period,ot_currency,placement_fee,placement_curr,bill_contact,bill_address,wcomp_code,imethod,tsapp,bill_req,service_terms,hire_req,addinfo,notes,pusername,rtime,avg_interview,assg_status,jtype,iterms,pterms,calctype,burden,margin,markup,prateopen_amt,brateopen_amt,prateopen,brateopen,owner,cdate,otbrate_amt,otbrate_period,otbrate_curr,otprate_amt,otprate_period,otprate_curr,payrollpid,offlocation,double_prate_amt,double_prate_period,double_prate_curr,double_brate_amt,double_brate_period,double_brate_curr,po_num,department,job_loc_tax,muser,mdate,date_placed,diem_lodging,diem_mie,diem_total,diem_period,diem_currency,diem_billable,diem_taxable,diem_billrate,assign_no,seqnumber,deptid,attention,corp_code, industryid,schedule_display,bill_burden,shiftid,starthour,endhour,shift_type,worksite_code,daypay,licode,federal_payroll,nonfederal_payroll,contractid,classification,ts_layout_pref) SELECT '',username,posid,jotype,catid,postitle,refcode,posstatus,vendor,contact,client,manager,'".$userid."',endclient,s_date,exp_edate,posworkhr,timesheet,e_date,hired_date,reason,rate,rateperiod,rateper,project,bamount,bperiod,bcurrency,pamount,pperiod,pcurrency,emp_prate,otrate,ot_period,ot_currency,placement_fee,placement_curr,bill_contact,bill_address,wcomp_code,imethod,tsapp,bill_req,service_terms,hire_req,addinfo,notes,pusername,rtime,avg_interview,'Needs Approval',jtype,iterms,pterms,calctype,burden,margin,markup,prateopen_amt,brateopen_amt,prateopen,brateopen,owner,cdate,otbrate_amt,otbrate_period,otbrate_curr,otprate_amt,otprate_period,otprate_curr,payrollpid,offlocation,double_prate_amt,double_prate_period,double_prate_curr,double_brate_amt,double_brate_period,double_brate_curr,po_num,department,
			job_loc_tax,'".$username."',NOW(),date_placed,diem_lodging,diem_mie,diem_total,diem_period,diem_currency,diem_billable,diem_taxable,diem_billrate,assign_no,'".$sub_seqnumber."',deptid,attention,corp_code, industryid,schedule_display,bill_burden,shiftid,starthour,endhour,shift_type,worksite_code,daypay,licode,federal_payroll,nonfederal_payroll,contractid,classification,ts_layout_pref FROM hrcon_jobs WHERE sno='".$hr_id."'";
			mysql_query($place_que,$db);
			$place_id=mysql_insert_id($db);
			
			if($place_id != 0)
			{
				saveBurdenDetails($burdenTypeDetails,$burdenItemDetails,'placement_burden_details','placement_jobs_sno','insert',$place_id);
				saveBillBurdenDetails($bill_burdenTypeDetails,$bill_burdenItemDetails,'placement_burden_details','placement_jobs_sno','insert',$place_id);
				if(SHIFT_SCHEDULING_ENABLED == 'Y')
				{
					//inserting the time slots data for schedule management for placement job
					if($shift_type=='regular')
					{
						insPlacementTimeSlots($db,'placement_sm_timeslots',$place_id,$timeslotvalues,$id_req,$userid);
					}
					else if($shift_type=='perdiem')
					{
						//inserting the time slots data for perdiem schedule management for placement job
						insPerdiemPlacementTimeSlots($db,'placement_perdiem_shift_sch',$place_id,$id_req,$userid,$assg_pusername);
					}
					
				}
			}
			

			$upd_place="UPDATE placement_jobs set pusername='".$assg_pusername."',muser='".$username."',mdate=NOW() where sno='".$place_id."'";
			mysql_query($upd_place,$db);

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

				$indexVal           = $dynemp.$keyValue;
				$roleOverwrite      = $roleOverWrite[$commKey][$indexVal];
				$enableUserInput    = $roleEDisable[$commKey][$indexVal];

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

						$comm_insert	= "INSERT INTO assign_commission(sno, username, assignid, assigntype, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput,shift_id) VALUES('', '".$username."', '".$place_id."', 'P', '".$acccon."', 'A', '".$commval[$commKey][$indexVal]."', '".$paytype[$commKey][$indexVal]."', '".$ratetype[$commKey][$indexVal]."', '".$roleName[$commKey][$indexVal]."', '".$roleOverwrite."', '".$enableUserInput."','".$shift_id_for_comm."')";
						mysql_query($comm_insert, $db);
						
						$comm_insert	= "INSERT INTO assign_commission(sno, username, assignid, assigntype, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput,shift_id) VALUES('', '".$username."', '".$hr_id."', 'H', '".$acccon."', 'A', '".$commval[$commKey][$indexVal]."', '".$paytype[$commKey][$indexVal]."', '".$ratetype[$commKey][$indexVal]."', '".$roleName[$commKey][$indexVal]."', '".$roleOverwrite."', '".$enableUserInput."','".$shift_id_for_comm."')";
						mysql_query($comm_insert, $db);
					}

				}
				else if(substr($commKey,0,3) == 'emp')
				{
					$empno		= explode("emp",$commKey);

					if(!empty($roleName[$commKey][$indexVal]))
					{
						$comm_insert	= "INSERT INTO assign_commission(sno, username, assignid, assigntype, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput,shift_id) VALUES('', '".$username."', '".$place_id."', 'P', '".$empno[1]."', 'E', '".$commval[$commKey][$indexVal]."', '".$paytype[$commKey][$indexVal]."', '".$ratetype[$commKey][$indexVal]."', '".$roleName[$commKey][$indexVal]."', '".$roleOverwrite."', '".$enableUserInput."','".$shift_id_for_comm."')";
						mysql_query($comm_insert, $db);
						
						$comm_insert	= "INSERT INTO assign_commission(sno, username, assignid, assigntype, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput, shift_id) VALUES('', '".$username."', '".$hr_id."', 'H', '".$empno[1]."', 'E', '".$commval[$commKey][$indexVal]."', '".$paytype[$commKey][$indexVal]."', '".$ratetype[$commKey][$indexVal]."', '".$roleName[$commKey][$indexVal]."', '".$roleOverwrite."', '".$enableUserInput."','".$shift_id_for_comm."')";
						mysql_query($comm_insert, $db);
					}

				}
				else 
				{
					$acccon	= $commKey;

					if(!empty($roleName[$commKey][$indexVal]))
					{
						$comm_insert	= "INSERT INTO assign_commission(sno, username, assignid, assigntype, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput,shift_id) VALUES('', '".$username."', '".$place_id."', 'P', '".$acccon."', 'A', '".$commval[$commKey][$indexVal]."', '".$paytype[$commKey][$indexVal]."', '".$ratetype[$commKey][$indexVal]."', '".$roleName[$commKey][$indexVal]."', '".$roleOverwrite."', '".$enableUserInput."','".$shift_id_for_comm."')";
						mysql_query($comm_insert, $db);
						
						$comm_insert	= "INSERT INTO assign_commission(sno, username, assignid, assigntype, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput,shift_id) VALUES('', '".$username."', '".$hr_id."', 'H', '".$empno."', 'A', '".$commval[$commKey][$indexVal]."', '".$paytype[$commKey][$indexVal]."', '".$ratetype[$commKey][$indexVal]."', '".$roleName[$commKey][$indexVal]."', '".$roleOverwrite."', '".$enableUserInput."','".$shift_id_for_comm."')";
						mysql_query($comm_insert, $db);
					}

				}
				$i++;
			}


			$insid=$hr_id;

			$vque="select s_date,e_date,project from hrcon_jobs where username='".$emp_user[0]."'";
			$vres=mysql_query($vque,$db);
			$vrow=mysql_fetch_row($vres);

			$sdt=$vrow[0];
			$edt=$vrow[1];
			$aproject=$vrow[2];

			$ssd=explode("-",$sdt);
			$month=$ssd[0];
			$day=$ssd[1];
			$year=$ssd[2];

			$usd=explode("-",$edt);
			$umonth=$usd[0];
			$uday=$usd[1];
			$uyear=$usd[2];
			$startday=mktime (0,0,0,$month,$day,$year);

			if(($umonth=="Month") || ($uday=="Day") || ($uyear=="Year"))
				$endday="nodate";
			else
				$endday=mktime (0,0,0,$umonth,$uday,$uyear);

			$date=$day."/".$month."/".$year;
			$end_date=$uday."/".$umonth."/".$uyear;

			if($aproject=="")
				$ptitle="Project";
			else
				$ptitle=$aproject;

			$assignment_array=array("modulename" => "HR->Assignments", "pusername" => $assg_pusername, "userid" => $emp_user[0], "contactsno" => $ccid, "invapproved"=> 'active', "startdate" => $sdt, "enddate" => $edt, "title" => $ptitle);		
			$hremp_id=insertAssignmentSchedule($assignment_array);

			$appno=$hremp_id."||".$insid."|".$place_id;
		}
		else
		{
			if($page152[44]=='Internal Direct')
				$viewInCrm='N';
			else
				$viewInCrm='Y';

			if ($shift_id =="" || $shift_id == "0") {
				$shift_type = "";
			}

			if(SHIFT_SCHEDULING_ENABLED == 'N') 
			{
				$que="select closepos from posdesc where posid='".$id_req."'";
				$res=mysql_query($que,$db);
				$row=mysql_fetch_row($res);
				$cl_pos=$row[0];
	
				$que="update posdesc set closepos='".($cl_pos+1)."' where posid='".$id_req."'";
				mysql_query($que,$db);
	
				$que_hjobs="update hotjobs set closepos='".($cl_pos+1)."' where req_id ='".$id_req."' and status!='BP'"; 
				mysql_query($que_hjobs,$db);
				$shift_type = "";
			}

				
			$quevalue="'','".$conusername."','".$id_req."','".$page152[0]."','".$page152[1]."','".addslashes($page152[2])."','".addslashes($page152[3])."','".$page152[4]."','".$page152[5]."','".$page152[6]."','".$page152[7]."','".$page152[8]."','".$page152[9]."','".$page152[10]."','".$page152[11]."','".$expdate."','".$page152[13]."','".$page152[14]."','".$page152[15]."','".$hiredate."','".addslashes($page152[17])."','".$page152[18]."','".$page152[19]."','".$page152[20]."','".addslashes($page152[21])."','".$page152[22]."','".$page152[23]."','".$page152[24]."','".$page152[25]."','".$page152[26]."','".$page152[27]."','".$page152[28]."','".$page152[29]."','".$page152[30]."','".$page152[31]."','".$page152[32]."','".$page152[33]."','".$page152[34]."','".$page152[35]."','".addslashes($page152[36])."','".$page152[37]."','".$page152[38]."','".addslashes($page152[39])."','".addslashes($page152[40])."','".$page152[41]."','".addslashes($page152[42])."','".addslashes($page152[43])."','',NOW(),'".addslashes($page152[45])."','".addslashes($page152[47])."','".addslashes($page152[48])."','".$page152[50]."','".$page152[51]."','".$page152[52]."','".$page152[53]."','".$page152[54]."','".$page152[55]."','".$page152[56]."','".$page152[57]."','".$username."',now(),'".$page152[58]."','".$page152[59]."','".$page152[60]."','".$page152[61]."','".$page152[62]."','".$page152[63]."','".addslashes($page152[64])."','".$loc_user."','".$page152[65]."','".$page152[66]."','".$page152[67]."','".$page152[68]."','".$page152[69]."','".$page152[70]."','".addslashes($page152[71])."','".addslashes($page152[72])."','".$page152[73]."','OP','".$username."',NOW(),NOW(), '".$page152[74]."','".$page152[75]."','".$page152[76]."','".$page152[77]."','".$page152[78]."','".$page152[79]."','".$page152[80]."','".$page152[81]."','".$page152[83]."','".$page152[84]."','".addslashes($attention)."','".$corp_code."','".$joindustryid."','".$schedule_display."','".$comm_bill_burden."','".$shift_id."','".$shift_st_time."','".$shift_et_time."','".$shift_type."','".$page152[88]."','".$daypay."','".$licode."','".$federal_payroll."','".$nonfederal_payroll."','".$contractid."','".$classification."','".$placement_timesheet_layout_preference."'";
			$que="insert into consultant_jobs(sno,username,posid,jotype,catid,postitle,refcode,posstatus,vendor,contact,client,manager,candidate,endclient,s_date,exp_edate,posworkhr,timesheet,e_date,hired_date,reason,rate,rateperiod,rateper,project,bamount,bperiod,bcurrency,pamount,pperiod,pcurrency,emp_prate,otrate,ot_period,ot_currency,placement_fee,placement_curr,bill_contact,bill_address,wcomp_code,imethod,tsapp,bill_req,service_terms,hire_req,addinfo,notes,commision,rtime,avg_interview,iterms,pterms,calctype,burden,margin,markup,prateopen_amt,brateopen_amt,prateopen,brateopen,owner,cdate,otprate_amt,otprate_period,otprate_curr,otbrate_amt,otbrate_period,otbrate_curr,payrollpid,offlocation,double_prate_amt,double_prate_period,double_prate_curr,double_brate_amt,double_brate_period, double_brate_curr,po_num,department,job_loc_tax,jtype,muser,mdate,date_placed,diem_lodging,diem_mie,diem_total,diem_period,diem_currency,diem_billable,diem_taxable,diem_billrate,classid,deptid,attention,corp_code, industryid,schedule_display,bill_burden,shiftid,starthour,endhour,shift_type,worksite_code,daypay,licode,federal_payroll,nonfederal_payroll,contractid,classification,ts_layout_pref) values(".$quevalue.")"; 

			mysql_query($que,$db);
			$ass_id=mysql_insert_id($db);
                        
			if($ass_id != 0)
			{
				saveBurdenDetails($burdenTypeDetails,$burdenItemDetails,'consultant_burden_details','consultant_jobs_sno','insert',$ass_id);
				saveBillBurdenDetails($bill_burdenTypeDetails,$bill_burdenItemDetails,'consultant_burden_details','consultant_jobs_sno','insert',$ass_id);
				if(SHIFT_SCHEDULING_ENABLED == 'Y')
				{
					//inserting the time slots data for schedule management for consultant job
					if($shift_type=='regular')
					{
						insPlacementTimeSlots($db,'consultantjob_sm_timeslots',$ass_id,$timeslotvalues,$id_req);
					}
					else if($shift_type=='perdiem')
					{
						//inserting the time slots data for perdiem schedule management for placement job
						insPerdiemPlacementTimeSlots($db,'consultantjob_perdiem_shift_sch',$ass_id,$id_req,$userid,$assg_pusername);
					}
				}
			}

			/////////////////// UDF migration to customers ///////////////////////////////////
		include_once('custom/custome_save.php');		
		$directEmpUdfObj = new userDefinedManuplations();
		$directEmpUdfObj->insertUserDefinedData($ass_id, 8);

		/////////////////////////////////////////////////////////////////////////////////
			
			$insid=$ass_id;
			$ccid=$ass_id;

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

			$asque="SELECT appno FROM assignment_schedule WHERE userid='".$conusername."' AND modulename='HR->Compensation'";
			$asres=mysql_query($asque,$db);
			while($asrow=mysql_fetch_row($asres))
			{
				$dque="DELETE FROM assignment_schedule WHERE userid='".$conusername."'";
				mysql_query($dque,$db);
				if(SHIFT_SCHEDULING_ENABLED == 'N') 
				{
					$dque="DELETE FROM consultant_tab WHERE consno='".$asrow[0]."' AND coltype='compen'";
					mysql_query($dque,$db);
				}
			}
                        // Added this condition to set the paygroupcode value from placements to hiring
                        
                        if(DEFAULT_AKKUPAY == 'Y'){
                            $paygroupcode_Query = "SELECT paycodesno,paygroupcode FROM consultant_compen WHERE username='".$conusername."'";
                            $paygroupcode_Resp = mysql_query($paygroupcode_Query,$db);
                            $paygroupcode_Count = mysql_num_rows($paygroupcode_Resp);
                            if($paygroupcode_Count>0){
                                $paygroup=mysql_fetch_row($paygroupcode_Resp);
                                if($paygroup[0]!='0' && $paygroup[1]!=''){
                                    $paygroupcodesno = $paygroup[0];
                                    $paygroupcode = $paygroup[1];
                                }else{
                                    $paygroupcodesno = 0;
                                    $paygroupcode = '';
                                }
                            }else{
                                $paygroupcodesno = 0;
                               $paygroupcode = ''; 
                            }
                        }else{
                               $paygroupcodesno = 0;
                               $paygroupcode = '';  
                        }

			$dque="DELETE FROM consultant_compen WHERE username='".$conusername."'";
			mysql_query($dque,$db);
			
			/* We are making Empleoyee >> Compensation Tab --> Employee Type as 0, if "Same as Job Type in Assignments" is Y (enabled) */
			if($jobtype == 'Y')
				$page152[49] = 0;

			$consult_compen="INSERT INTO consultant_compen (sno,username,emp_id,date_hire,location,status,salary,salper,shper,std_hours,over_time,ot_period,ot_currency ,emptype,timesheet,pay_assign,job_type,posworkhr,designation,emp_crm,diem_lodging,diem_mie,diem_total,diem_period,diem_currency,diem_billable,diem_taxable,diem_billrate,dept,assign_wcompcode,assign_overtime,assign_bench,assign_double,diem_pay_assign,paygroupcode,paycodesno,wcomp_code) values('','".$conusername."','".$emp_id."',if('".$page152[16]."'!='0-0-0','".$page152[16]."',date_format(now(),'%c-%d-%Y')),'".$locationID."','active','','HOUR','USD','".$page152[13]."','','HOUR','USD','".$page152[49]."','".$page152[14]."','".$payassign."','".$jobtype."','fulltime','".addslashes($job_title)."','".$viewInCrm."', '".$page152[74]."','".$page152[75]."','".$page152[76]."','".$page152[77]."','".$page152[78]."','".$page152[79]."','".$page152[80]."','".$page152[81]."','".$deptID."','".$payassign."','".$payassign."','".$payassign."','".$payassign."','".$payassign."','".$paygroupcode."','".$paygroupcodesno."','{$page152[36]}')";
			mysql_query($consult_compen,$db);
			$compen_id_sno=mysql_insert_id($db);
			
			/*Updating the Class ID for consultant_compen table using department table */
			$updCccon_CompenClassId = "UPDATE consultant_compen t1, department t2
			SET t1.company_id = t2.company_id
			WHERE t1.dept = t2.sno AND t1.sno = {$compen_id_sno}"; 	
			mysql_query($updCccon_CompenClassId,$db);

			if($aproject=="")
				$ptitle="Project";
			else
				$ptitle=$aproject;

			$assignment_array=array("modulename" => "HR->Compensation", "pusername" => "", "userid" => $conusername, "contactsno" => $compen_id_sno, "invapproved" => 'active', "startdate" => "", "enddate" => "", "title" => $ptitle);
			$compen_id=insertAssignmentSchedule($assignment_array);

			$upd_consu="UPDATE consultant_jobs set pusername='".$assg_pusername."',muser='".$username."',mdate=NOW(),assign_no='".$assg_pusername."' where sno='".$ass_id."'";
			mysql_query($upd_consu,$db);
			$hremp_id=$ass_id;

			$place_que = "INSERT INTO placement_jobs (sno,username,posid,jotype,catid,postitle,refcode,posstatus,vendor,contact,client,manager,candidate,endclient,s_date,exp_edate,posworkhr,timesheet,e_date,hired_date,reason,rate,rateperiod,rateper,project,bamount,bperiod,bcurrency,pamount,pperiod,pcurrency,emp_prate,otrate,ot_period,ot_currency,placement_fee,placement_curr,bill_contact,bill_address,wcomp_code,imethod,tsapp,bill_req,service_terms,hire_req,addinfo,notes,commision,rtime,avg_interview,iterms,pterms,calctype,burden,margin,markup,prateopen_amt,brateopen_amt,prateopen,brateopen,owner,assg_status,cdate,otbrate_amt,otbrate_period,otbrate_curr,otprate_amt,otprate_period,otprate_curr,payrollpid,offlocation,double_prate_amt,double_prate_period,double_prate_curr,double_brate_amt,double_brate_period, double_brate_curr,po_num,department,job_loc_tax,jtype,muser,mdate,date_placed,diem_lodging,diem_mie,diem_total,diem_period,diem_currency,diem_billable,diem_taxable,diem_billrate,assign_no,seqnumber,deptid,attention,corp_code, industryid,schedule_display,bill_burden,shiftid,starthour,endhour,shift_type,worksite_code,daypay,licode,federal_payroll,nonfederal_payroll,contractid,classification,ts_layout_pref) SELECT '',username,posid,jotype,catid,postitle,refcode,posstatus,vendor,contact,client,manager,'".$userid."',endclient,s_date,exp_edate,posworkhr,timesheet,e_date,hired_date,reason,rate,rateperiod,rateper,project,bamount,bperiod,bcurrency,pamount,pperiod,pcurrency,emp_prate,otrate,ot_period,ot_currency,placement_fee,placement_curr,bill_contact,bill_address,wcomp_code,imethod,tsapp,bill_req,service_terms,hire_req,addinfo,notes,commision,rtime,avg_interview,iterms,pterms,calctype,burden,margin,markup,prateopen_amt,brateopen_amt,prateopen,brateopen,owner,'Needs Approval',cdate,otbrate_amt,otbrate_period,otbrate_curr,otprate_amt,otprate_period,otprate_curr,payrollpid,offlocation,double_prate_amt,double_prate_period,double_prate_curr,double_brate_amt,double_brate_period,double_brate_curr,po_num,department,job_loc_tax,jtype,'".$username."',NOW(),date_placed,diem_lodging,diem_mie,diem_total,diem_period,diem_currency,diem_billable,diem_taxable,diem_billrate,assign_no,'".$sub_seqnumber."',deptid,attention,corp_code, industryid,schedule_display,bill_burden,shiftid,starthour,endhour,shift_type,worksite_code,daypay,licode,federal_payroll,nonfederal_payroll,contractid,classification,ts_layout_pref FROM consultant_jobs WHERE sno='".$ass_id."'"; 			
			mysql_query($place_que,$db);
			$place_id=mysql_insert_id($db);
			
			if($place_id != 0)
			{
				saveBurdenDetails($burdenTypeDetails,$burdenItemDetails,'placement_burden_details','placement_jobs_sno','insert',$place_id);
				saveBillBurdenDetails($bill_burdenTypeDetails,$bill_burdenItemDetails,'placement_burden_details','placement_jobs_sno','insert',$place_id);
				if(SHIFT_SCHEDULING_ENABLED == 'Y') 
				{
					if($shift_type=='regular')
					{
						//inserting the time slots data for schedule management for placement job
						insPlacementTimeSlots($db,'placement_sm_timeslots',$place_id,$timeslotvalues,$id_req,$userid);
					}
					else if($shift_type=='perdiem')
					{
						//inserting the time slots data for perdiem schedule management for placement job
						insPerdiemPlacementTimeSlots($db,'placement_perdiem_shift_sch',$place_id,$id_req,$userid,$assg_pusername);
					}
				}
			}
			
		
			$upd_place="UPDATE placement_jobs set pusername='".$assg_pusername."' where sno='".$place_id."'";
			mysql_query($upd_place,$db);

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
						$comm_insert	= "INSERT INTO assign_commission(sno, username, assignid, assigntype, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput,shift_id) VALUES('', '".$username."', '".$ass_id."', 'C', '".$acccon."', 'A', '".$commval[$commKey][$indexVal]."', '".$paytype[$commKey][$indexVal]."', '".$ratetype[$commKey][$indexVal]."', '".$roleName[$commKey][$indexVal]."', '".$roleOverwrite."', '".$enableUserInput."','".$shift_id_for_comm."')";
						mysql_query($comm_insert, $db);

						$comm_insert	= "INSERT INTO assign_commission(sno, username, assignid, assigntype, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput,shift_id) VALUES('', '".$username."', '".$place_id."', 'P', '".$acccon."', 'A', '".$commval[$commKey][$indexVal]."', '".$paytype[$commKey][$indexVal]."', '".$ratetype[$commKey][$indexVal]."', '".$roleName[$commKey][$indexVal]."', '".$roleOverwrite."', '".$enableUserInput."','".$shift_id_for_comm."')";
						mysql_query($comm_insert, $db);
					}
				}
				else if(substr($commKey,0,3) == 'emp')
				{
					$empno		= explode("emp",$commKey);

					if(!empty($roleName[$commKey][$indexVal]))
					{
						$comm_insert	= "INSERT INTO assign_commission(sno, username, assignid, assigntype, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput,shift_id) VALUES('', '".$username."', '".$ass_id."', 'C', '".$empno[1]."', 'E', '".$commval[$commKey][$indexVal]."', '".$paytype[$commKey][$indexVal]."', '".$ratetype[$commKey][$indexVal]."', '".$roleName[$commKey][$indexVal]."', '".$roleOverwrite."', '".$enableUserInput."','".$shift_id_for_comm."')";
						mysql_query($comm_insert, $db);

						$comm_insert	= "INSERT INTO assign_commission(sno, username, assignid, assigntype, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput,shift_id) VALUES('', '".$username."', '".$place_id."', 'P', '".$empno[1]."', 'E', '".$commval[$commKey][$indexVal]."', '".$paytype[$commKey][$indexVal]."', '".$ratetype[$commKey][$indexVal]."', '".$roleName[$commKey][$indexVal]."', '".$roleOverwrite."', '".$enableUserInput."','".$shift_id_for_comm."')";
						mysql_query($comm_insert, $db);
					}
				}
				else
				{
					$acccon		= $commKey;

					if(!empty($roleName[$commKey][$indexVal]))
					{
						$comm_insert	= "INSERT INTO assign_commission(sno, username, assignid, assigntype, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput,shift_id) VALUES('', '".$username."', '".$ass_id."', 'C', '".$acccon."', 'A', '".$commval[$commKey][$indexVal]."', '".$paytype[$commKey][$indexVal]."', '".$ratetype[$commKey][$indexVal]."', '".$roleName[$commKey][$indexVal]."', '".$roleOverwrite."', '".$enableUserInput."','".$shift_id_for_comm."')";
						mysql_query($comm_insert, $db);

						$comm_insert	= "INSERT INTO assign_commission(sno, username, assignid, assigntype, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput,shift_id) VALUES('', '".$username."', '".$place_id."', 'P', '".$acccon."', 'A', '".$commval[$commKey][$indexVal]."', '".$paytype[$commKey][$indexVal]."', '".$ratetype[$commKey][$indexVal]."', '".$roleName[$commKey][$indexVal]."', '".$roleOverwrite."', '".$enableUserInput."','".$shift_id_for_comm."')";
						mysql_query($comm_insert, $db);
					}
				}
				$i++;
			}

			if($aproject=="")
				$ptitle="Project";
			else
				$ptitle=$aproject;

			$assignment_array=array("modulename" => "HR->Assignments", "pusername" => $assg_pusername, "userid" => $conusername, "contactsno" => $ccid, "invapproved" => 'active', "startdate" => $page152[11], "enddate" => $page152[15] , "title" => $ptitle);
			$hremp_id=insertAssignmentSchedule($assignment_array);	

			$appno=$hremp_id."|".$compen_id."|".$insid."|".$place_id;
		}

		if(SHIFT_SCHEDULING_ENABLED == 'Y') 
		{

			$que = "select closepos from posdesc where posid='".$id_req."'";
			$res = mysql_query($que,$db);
			$row = mysql_fetch_row($res);
			$cl_pos = $row[0];

			// UPDATES JOB ORDER SHIFT SCHEDULING STATUS BASED ON POSITIONS FILLED

			if($shift_type !='perdiem')
			{
				//$objScheduleDetails->updateShiftStatus($id_req);
				$objScheduleDetails->updateShiftStatusByName($id_req);

				//Getting filled positions based on the shift schedules
				$getNumFilledPos = $objScheduleDetails->getJobFilledPosPerShift($id_req, $place_id);

				if($getNumFilledPos > 0)
				{
					
					$que="update posdesc set closepos='".($getNumFilledPos+$cl_pos)."' where posid='".$id_req."'";
					mysql_query($que,$db);
		
					$que_hjobs="update hotjobs set closepos='".($getNumFilledPos+$cl_pos)."' where req_id ='".$id_req."' and status!='BP'"; 
					mysql_query($que_hjobs,$db);
				}
				else {
					$que="update posdesc set closepos='".($cl_pos+1)."' where posid='".$id_req."'";
					mysql_query($que,$db);
		
					$que_hjobs="update hotjobs set closepos='".($cl_pos+1)."' where req_id ='".$id_req."' and status!='BP'"; 
					mysql_query($que_hjobs,$db);
				}
			}		
		}

		$selHotjobsSno="select sno from hotjobs where req_id ='".$id_req."' and status!='BP'"; 
		$selHotjobsSnoRes = mysql_query($selHotjobsSno,$db);
		$selHotjobsSnoRow=mysql_fetch_row($selHotjobsSnoRes);

		$que_apijobs="update api_jobs set closepos='".($cl_pos+1)."' where req_id ='".$selHotjobsSnoRow[0]."' and status!='BP'"; 
		mysql_query($que_apijobs,$db);
		
		return($appno);
	}
        
	$WeekIntArray=array("Sunday"=>1,"Monday"=>2,"Tuesday"=>3,"Wednesday"=>4,"Thursday"=>5,"Friday"=>6,"Saturday"=>7);

	if($candtype == "Employee")
	{
		$table="empcon_tab";
		$tbl_field="(sno,tabsno,starthour,endhour,wdays,sch_date,coltype)";
	}
	else
	{
		$table="consultant_tab";
		$tbl_field="(sno,consno,starthour,endhour,wdays,sch_date,coltype)";
	}

	$appnospl=explode("|",$appno);
	$appno=$appnospl[0];
	$compenId=$appnospl[1];
	$commission_id=$appnospl[2];
	$place_id=$appnospl[3];
	
	//Inserting the OLD shift scheduling if the settings is in disabled mode
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
					if($page152[44]=='Direct' && $candtype == "Employee")
					{
						if($compenId!="")
						{
							$sheQry="INSERT INTO hrcon_tab".$tbl_field." VALUES ('','".$compenId."','".$$Timefrom."','".$$TimeTo."','".$AddweakInt."','','compen')";
							mysql_query($sheQry,$db);

							$sheQry="INSERT INTO hrcon_tab".$tbl_field." VALUES ('','".$appno."','".$$Timefrom."','".$$TimeTo."','".$AddweakInt."','','assign')";
							mysql_query($sheQry,$db);

							$sheQry="INSERT INTO ".$table." ".$tbl_field." VALUES ('','".$compenId."','".$$Timefrom."','".$$TimeTo."','".$AddweakInt."','','compen')";
							mysql_query($sheQry,$db);
						}
						else
						{
							$sheQry="INSERT INTO hrcon_tab".$tbl_field." VALUES ('','".$appno."','".$$Timefrom."','".$$TimeTo."','".$AddweakInt."','','assign')";
							mysql_query($sheQry,$db);
						}
					}

					$sheQry="INSERT INTO ".$table." ".$tbl_field." VALUES ('','".$appno."','".$$Timefrom."','".$$TimeTo."','".$AddweakInt."','','assign')";
					mysql_query($sheQry,$db);

					$sheQry="INSERT INTO placement_tab (sno,consno,starthour,endhour,wdays,sch_date,coltype) VALUES ('','".$place_id."','".$$Timefrom."','".$$TimeTo."','".$AddweakInt."','','assign')";
					mysql_query($sheQry,$db);
				}

				foreach($$AddweakArray as $Addkey=>$Addval)
				{
					$AddTimefrom="newSchdFrom".$Addkey;
					$AddTimeTo="newSchdTo".$Addkey;

					if($addchkSchedule[$Addkey]!="Y" && (trim($$AddTimefrom)!="" && trim($$AddTimeTo)!=""))
					{
						$Addweekday=$WeekIntArray[$Addval];

						if($page152[44]=='Direct' && $candtype == "Employee")
						{
							if($compenId!="")
							{
								$sheQry="INSERT INTO hrcon_tab".$tbl_field." VALUES ('','".$compenId."','".$$AddTimefrom."','".$$AddTimeTo."','".$Addweekday."','','compen')";
								mysql_query($sheQry,$db);

								$sheQry="INSERT INTO hrcon_tab".$tbl_field." VALUES ('','".$appno."','".$$AddTimefrom."','".$$AddTimeTo."','".$Addweekday."','','assign')";
								mysql_query($sheQry,$db);

								$AddsheQry="INSERT INTO ".$table." ".$tbl_field." VALUES ('','".$compenId."','".$$AddTimefrom."','".$$AddTimeTo."','".$$Addweekday."','','compen')";
								mysql_query($AddsheQry,$db);
							}
							else
							{
								$sheQry="INSERT INTO hrcon_tab".$tbl_field." VALUES ('','".$appno."','".$$AddTimefrom."','".$$AddTimeTo."','".$Addweekday."','','assign')";
								mysql_query($sheQry,$db);
							}
						}

						$AddsheQry="INSERT INTO ".$table." ".$tbl_field." VALUES ('','".$appno."','".$$AddTimefrom."','".$$AddTimeTo."','".$Addweekday."','','assign')";
						mysql_query($AddsheQry,$db);

						$AddsheQry="INSERT INTO placement_tab (sno,consno,starthour,endhour,wdays,sch_date,coltype) VALUES ('','".$place_id."','".$$AddTimefrom."','".$$AddTimeTo."','".$Addweekday."','','assign')";
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

					if($page152[44]=='Direct' && $candtype == "Employee")
					{
						if($compenId!="")
						{
							$sheQry="INSERT INTO hrcon_tab".$tbl_field." VALUES ('','".$compenId."','".$$AddDateTimefrom."','".$$AddDateTimeTo."','".$AddDateweekday."','".$Insdate."','compen')";
							mysql_query($sheQry,$db);

							$sheQry="INSERT INTO hrcon_tab".$tbl_field." VALUES ('','".$appno."','".$$AddDateTimefrom."','".$$AddDateTimeTo."','".$AddDateweekday."','".$Insdate."','assign')";
							mysql_query($sheQry,$db);

							$AddsheQry="INSERT INTO ".$table." ".$tbl_field." VALUES ('','".$compenId."','".$$AddDateTimefrom."','".$$AddDateTimeTo."','".$$AddDateweekday."','".$Insdate."','compen')";
							mysql_query($AddsheQry,$db);
						}
						else
						{
							$sheQry="INSERT INTO hrcon_tab".$tbl_field." VALUES ('','".$appno."','".$$AddDateTimefrom."','".$$AddDateTimeTo."','".$AddDateweekday."','".$Insdate."','assign')";
							mysql_query($sheQry,$db);
						}
					}

					$AddDatesheQry="INSERT INTO ".$table." ".$tbl_field." VALUES ('','".$appno."','".$$AddDateTimefrom."','".$$AddDateTimeTo."','".$AddDateweekday."','".$Insdate."','assign')";
					mysql_query($AddDatesheQry,$db);

					$AddDatesheQry="INSERT INTO placement_tab (sno,consno,starthour,endhour,wdays,sch_date,coltype) VALUES ('','".$place_id."','".$$AddDateTimefrom."','".$$AddDateTimeTo."','".$AddDateweekday."','".$Insdate."','assign')";
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

					if($page152[44]=='Direct' && $candtype == "Employee")
					{
						if($compenId!="")
						{
							$sheQry="INSERT INTO hrcon_tab".$tbl_field." VALUES ('','".$compenId."','".$$UpdTimefrom."','".$$UpdTimeTo."','".$Updweekday."','','compen')";
							mysql_query($sheQry,$db);

							$sheQry="INSERT INTO hrcon_tab".$tbl_field." VALUES ('','".$appno."','".$$UpdTimefrom."','".$$UpdTimeTo."','".$Updweekday."','','assign')";
							mysql_query($sheQry,$db);

							$AddsheQry="INSERT INTO ".$table." ".$tbl_field." VALUES ('','".$compenId."','".$$UpdTimefrom."','".$$UpdTimeTo."','".$$Updweekday."','','compen')";
							mysql_query($AddsheQry,$db);
						}
						else
						{
							$sheQry="INSERT INTO hrcon_tab".$tbl_field." VALUES ('','".$appno."','".$$UpdTimefrom."','".$$UpdTimeTo."','".$Updweekday."','','assign')";
							mysql_query($sheQry,$db);
						}
					}

					$UpdsheQry="INSERT INTO ".$table." ".$tbl_field." VALUES ('','".$appno."','".$$UpdTimefrom."','".$$UpdTimeTo."','".$Updweekday."','','assign')";
					mysql_query($UpdsheQry,$db);

					$UpdsheQry="INSERT INTO placement_tab (sno,consno,starthour,endhour,wdays,sch_date,coltype) VALUES ('','".$place_id."','".$$UpdTimefrom."','".$$UpdTimeTo."','".$Updweekday."','','assign')";
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

					if($page152[44]=='Direct' && $candtype == "Employee")
					{
						if($compenId!="")
						{
							$sheQry="INSERT INTO hrcon_tab".$tbl_field." VALUES ('','".$compenId."','".$$UpdDateTimefrom."','".$$UpdDateTimeTo."','".$UpdDateweekday."','".$Upddate_sch."','compen')";
							mysql_query($sheQry,$db);

							$sheQry="INSERT INTO hrcon_tab".$tbl_field." VALUES ('','".$appno."','".$$UpdDateTimefrom."','".$$UpdDateTimeTo."','".$UpdDateweekday."','".$Upddate_sch."','assign')";
							mysql_query($sheQry,$db);

							$AddsheQry="INSERT INTO ".$table." ".$tbl_field." VALUES ('','".$compenId."','".$$UpdDateTimefrom."','".$$UpdDateTimeTo."','".$$UpdDateweekday."','".$Upddate_sch."','compen')";
							mysql_query($AddsheQry,$db);
						}
						else
						{
							$sheQry="INSERT INTO hrcon_tab".$tbl_field." VALUES ('','".$appno."','".$$UpdDateTimefrom."','".$$UpdDateTimeTo."','".$UpdDateweekday."','".$Upddate_sch."','assign')";
							mysql_query($sheQry,$db);
						}
					}

					$UpdDatesheQry="INSERT INTO ".$table." ".$tbl_field." VALUES ('','".$appno."','".$$UpdDateTimefrom."','".$$UpdDateTimeTo."','".$UpdDateweekday."','".$Upddate_sch."','assign')";
					mysql_query($UpdDatesheQry,$db);

					$UpdDatesheQry="INSERT INTO placement_tab (sno,consno,starthour,endhour,wdays,sch_date,coltype) VALUES ('','".$place_id."','".$$UpdDateTimefrom."','".$$UpdDateTimeTo."','".$UpdDateweekday."','".$Upddate_sch."','assign')";
					mysql_query($UpdDatesheQry,$db);
				}
			}
			////////////End of the Update//////////////////////////////////////////////////////
		}
	}


	if(($page152[44]=='Direct' || $page152[44]=='Internal Direct') && $candtype == "Employee")
	{
		$str_emp=substr($candempId,0,7);
		if($str_emp!='empprof')
			$sel_que = "select username,sno from emp_list where sno = ".trim(str_replace('emp','',$candempId));
		else
			$sel_que = "select username,sno from empprofile_list where sno = ".trim(str_replace('empprof','',$candempId));
		$res_que = mysql_query($sel_que,$db);
		$emp_user = mysql_fetch_row($res_que);
    
		$jtype_qry = "SELECT jtype,if(e_date='0-0-0','0000-00-00',DATE_ADD( MAX( STR_TO_DATE( e_date,'%m-%d-%Y' )),INTERVAL 1 DAY )),if(e_date='0-0-0','0000-00-00',MAX( STR_TO_DATE(  e_date,'%m-%d-%Y'))) FROM hrcon_jobs WHERE username = '".$emp_user[0]."' AND ustatus='active' GROUP BY username";
		$jtype_res = mysql_query($jtype_qry, $db);
		$jtype_row = mysql_fetch_row($jtype_res);

		// If the employee is on client project, need to get the max of end date from the assingments.
		if( $jtype_row[0]  == "OP" || $jtype_row[0] == "OV" || $jtype_row[0] == "AS")
		{
			//ON PROJECT
			if( ($jtype_row[2] == "0000-00-00" ) || ($jtype_row[2] == ''))
				$avail = "immediate";
			else
				$avail = $jtype_row[1];   //we will get the date in YYYY-MM-DD format
		}
		else if($jtype_row[0] == "OB")
		{
			// ON  BENCH
			$avail = "immediate";
		}
	}

	if($typeOfPlacement=="DIRECT")
	{
		createNewEmployee($db,$conusername,$commission_id,$appno,$timeslotvalues, $burdenTypeDetails, $burdenItemDetails,$bill_burdenTypeDetails,$bill_burdenItemDetails,$refBonusJOID);

		$location="";
		$roles="";

		$sque="select CONCAT(' ',hrcon_general.fname,' ',IF(hrcon_general.mname='','',concat(hrcon_general.mname,' ')),hrcon_general.lname),hrcon_general.email,hrcon_prof.pstatus,hrcon_prof.ifother,hrcon_desire.desiretype,hrcon_desire.desireamount,hrcon_desire.mpayment,'',hrcon_general.zip,hrcon_compen.location, hrcon_general.address1, hrcon_general.address2, hrcon_general.city, hrcon_general.stateid, hrcon_general.country, hrcon_general.wphone, hrcon_general.wphone_extn, hrcon_general.hphone, hrcon_general.hphone_extn, hrcon_general.mobile from hrcon_general LEFT JOIN hrcon_prof ON hrcon_general.username=hrcon_prof.username LEFT JOIN hrcon_desire ON hrcon_general.username=hrcon_desire.username LEFT JOIN hrcon_compen ON (hrcon_compen.username=hrcon_general.username and hrcon_compen.ustatus = 'active') where hrcon_general.ustatus='active' AND hrcon_general.username='".$conusername."'";
		$sres=mysql_query($sque,$db);
		$srow=mysql_fetch_row($sres);

		$lque="select city from hrcon_location where username='".$conusername."'";
		$lres=mysql_query($lque,$db);
		while($lrow=mysql_fetch_row($lres))
		{
			if($location=="")
				$location=$lrow[0];
			else
				$location.=", ".$lrow[0];
		}

		if($location=="")
			$location="Any Where";

		$skill="";
		$rque="select skillname,skillyear from hrcon_skills where username='".$conusername."'";
		$rres=mysql_query($rque,$db);
		while($rrow=mysql_fetch_row($rres))
		{
			if($skill=="")
				$skill=$rrow[0]."(".$rrow[1].")";
			else
				$skill.=", ".$rrow[0]."(".$rrow[1].")";
		}

		$homeday=mktime(0,0,0,date("m"),date("d"),date("Y"));
		$sysdate=getdate($homeday);
		$yy=$sysdate["year"];
		$mm=$sysdate["mon"];
		$dd=$sysdate["mday"];
		$today=$mm."-".$dd."-".$yy;

		if($srow[2]=="other")
			$srow[2]=$srow[3];

		$eque="insert into emp_list(sno, username, approveuser, name, email, status, rate, location, skills, roles, cur_project, client_name, estatus, avail, astatus, lstatus, restatus, stime, type, show_crm,mtime,muser, address1, address2, city, stateid, coutnry, primary_phone, primary_phone_ext, secondry_phone, secondry_phone_ext, other_phone, wotc_mja_status,tcc_status) values('','".$conusername."','".$username."','".addslashes($srow[0])."','".addslashes($srow[1])."','".$srow[2]."','".$srow[5]." ".$srow[4]."/".$srow[6]."','".$location."','".addslashes($skill)."','','".addslashes($jobtitle)."','".$supplier_id."','','".$today."','','','ACTIVE',NOW(),'".$emp_type."','".$showInCrmOrNot."',now(),'".$username."','".addslashes($srow[10])."','".addslashes($srow[11])."','".addslashes($srow[12])."','".addslashes($srow[13])."','".addslashes($srow[14])."','".addslashes($srow[15])."','".addslashes($srow[16])."','".addslashes($srow[17])."','".addslashes($srow[18])."', '".addslashes($srow[19])."', '".addslashes($wotc_mja_status)."','".$tcc_status."')";
		
		$res=mysql_query($eque,$db);
		$idu=mysql_insert_id($db);
		$conr="emp".$idu;

		// checking w4 records there or not for the employee. if not inserting new record.
		checkHrconw4Records($conusername);
		//To update the newly introduced columns in emp_list fr system performance
		emp_list_update($conusername);

		$sql="UPDATE candidate_list cl, emp_list el set cl.candid='".$conr."',cl.ctype= IF(cl.owner='".$username."','My Consultant','Consultant'),cl.emplstatus=el.lstatus, cl.empsno='".$idu."' WHERE  cl.username='".$candlist_user."' AND el.sno = '".$idu."'";
		mysql_query($sql,$db);

		/////////////////// UDF migration to customers ///////////////////////////////////
		include_once('custom/custome_save.php');		
		$directEmpUdfObj = new userDefinedManuplations();
		$directEmpUdfObj->insertDirectEmployees(3, $idu, $conr);
		/////////////////////////////////////////////////////////////////////////////////
		
		//Modified Time Updation in Employee table
		EmpModifiedUpdate('sno',$idu);	

		setDefaultEntityTaxes('Employee', $conusername, $srow[8], $srow[9]);
	}

	function createNewEmployee($db,$conusername,$commission_id,$appno,$timeSlotsData, $burdenTypeDetails, $burdenItemDetails,$bill_burdenTypeDetails,$bill_burdenItemDetails,$refBonusJOID)
	{
		global $username,$cand_username,$tabappiont_compen_id,$jobSno,$objHRMCredentials,$candidateVal,$userid;

		$appuser=$conusername;

		$query="insert into apphistory (username,notes,sdate,appuser,status) values ('".$conusername."','Forwarded to User Management to create User Account',CURRENT_DATE,'".$username."','Hired')";
		mysql_query($query,$db);

		$que="select MAX(sno) from emp_list";
		$res=mysql_query($que,$db);
		$row=mysql_fetch_row($res);
		$emp_id=($row[0]+1);

		$table="hrcon";

		if($typeuser != "PE")
		{
			$que="update vendorsubcon set empid='".$appuser."' where subid='".$cand_username."'";
			mysql_query($que,$db);
		}

		$que="insert into ".$table."_general(sno,username,fname,mname,lname,email,hintq,answer,profiletitle,address1,address2,city ,stateid,country,zip,wphone,hphone,mobile,fax,ustatus,udate,wphone_extn,hphone_extn,other_extn,state,alternate_email,other_email) select '','".$appuser."',fname,mname,lname,email,hintq,answer,profiletitle,address1,address2,city,stateid,country,zip,hphone,wphone,mobile,fax,'active',NOW(),wphone_extn,hphone_extn,other_extn,state,alternate_email,other_email from consultant_general where username='".$appuser."'";
		mysql_query($que,$db);

		$que="insert into ".$table."_prof (sno, username, objective, summary, pstatus, ifother, addinfo, avail, ustatus, udate ) select '','".$appuser."',objective,summary,pstatus,ifother,addinfo, availsdate ,'active',NOW() from consultant_prof where username='".$appuser."'";
		mysql_query($que,$db);

		$que="insert into ".$table."_status(sno,username,arrivaldate,ssndate,ustatus,udate) select '','".$appuser."',arrivaldate,ssndate,'active',NOW() from consultant_status  where username='".$appuser."'";
		mysql_query($que,$db);

		$que="insert into ".$table."_skills(sno,username,skillname,lastused,skilllevel,skillyear,ustatus,udate,manage_skills_id) select '','".$appuser."',skillname,lastused,skilllevel,skillyear,'active',NOW(),manage_skills_id from consultant_skills where username='".$appuser."'";
		mysql_query($que,$db);

		$que="insert into ".$table."_skill_cat_spec(sno,username,dept_cat_spec_id,type) select '','".$appuser."',dept_cat_spec_id,type from consultant_skill_cat_spec where username='".$appuser."'";
		mysql_query($que,$db);

		$que="insert into ".$table."_location(sno,username,city,state,country,ustatus,udate) select '','".$appuser."',city,state,country,'active',NOW() from consultant_location where username='".$appuser."'";
		mysql_query($que,$db);

		$que="insert into ".$table."_edu(sno,username,heducation,educity,edustate,educountry,edudegree_level,edudate,ustatus,udate ) select '','".$appuser."',heducation,educity,edustate,educountry,edudegree_level,edudate,'active',NOW() from consultant_edu where username='".$appuser."'";
		mysql_query($que,$db);

		$que="insert into ".$table."_desire(sno,username,desirejob,desirestatus,desireamount,desiretype,mpayment,desirelocation, ustatus,udate) select '','".$appuser."',desirejob,desirestatus,desireamount,desiretype,mpayment,desirelocation,'active',NOW() from consultant_desire where username='".$appuser."'";
		mysql_query($que,$db);

		$que="insert into ".$table."_aff(sno,username,affcname,affrole,affsdate,affedate,ustatus,udate) select '','".$appuser."',affcname,affrole,affsdate,affedate,'active',NOW() from consultant_aff where username='".$appuser."'";
		mysql_query($que,$db);

		$que="insert into ".$table."_ref(sno,username,name,company,title,phone,email,rship,secondary,mobile,ustatus,udate,notes,doc_id) select '','".$appuser."',name,company,title,phone,email,rship,secondary,mobile,'active',NOW(),notes,doc_id from consultant_ref where username='".$appuser."'";
		mysql_query($que,$db);

		$que="insert into ".$table."_report(sno,username,empname,description,ustatus,udate) select '','".$appuser."',empname,description,'active',NOW() from consultant_report where username='".$appuser."'";
		mysql_query($que,$db);
		//As per Requirement granted column name changed to max_allowed
		$que="insert into ".$table."_benifit(sno,username,eartype,max_allowed,used,avail,rollover,ustatus,udate) select '','".$appuser."',eartype,max_allowed,used,avail,rollover,'active',NOW() from consultant_benifit where username='".$appuser."'";
		mysql_query($que,$db);

		/*$que="insert into ".$table."_compen (sno, username, emp_id, dept, date_hire, location, status, salary, std_hours,
		over_time, rev_period, increment, bonus, designation, salper, shper, emptype, job_type, pay_assign, benchrate,
		benchperiod, benchcurrency, ot_period, ot_currency,timesheet,posworkhr,ustatus,diem_lodging,diem_mie,diem_total,
		diem_period,diem_currency, diem_billable,diem_taxable,diem_billrate) SELECT '','".$appuser."','".$emp_id."',
		if(dept!='',dept,'1'),if(date_hire!='0-0-0',date_hire,date_format(now(),'%c-%d-%Y')),location,status,salary,
		std_hours,over_time,rev_period,increment,bonus,designation,salper,shper,emptype,job_type,pay_assign,
		benchrate,benchperiod,benchcurrency,ot_period,ot_currency,timesheet,posworkhr,'active',diem_lodging,
		diem_mie,diem_total,diem_period,diem_currency,diem_billable,diem_taxable,diem_billrate
		FROM consultant_compen WHERE username='".$appuser."'"; 
		mysql_query($que,$db);
		$compensation_hr_id=mysql_insert_id($db);

		$upd_classid = "UPDATE	".$table."_compen t1,department t2 SET t1.classid = t2.classid WHERE t1.dept = t2.sno AND t1.sno = '".$compensation_hr_id."'"; 	
		mysql_query($upd_classid,$db);*/

		$que="insert into ".$table."_deduct(sno,username,type,title,amount,description,taxtype,compcon,ustatus,udate,deduction_code,deductionsno,start_date,stop_date,frequency,calc_method,modified_user,approved_user) select '','".$appuser."',type,title,amount,description,taxtype,compcon,'active',NOW(),deduction_code,deductionsno,start_date,stop_date,frequency,calc_method,'".$username."',0 from consultant_deduct where username='".$appuser."'";
		mysql_query($que,$db);

		$que="insert into ".$table."_expense(sno,username,type,title,amount,description,taxtype,compcon,ustatus,udate,modified_user,approved_user) select '','".$appuser."',type,title,amount,description,taxtype,compcon,'active',NOW(),'".$username."',0 from consultant_expense where username='".$appuser."'";
		mysql_query($que,$db);

		$que="insert into ".$table."_dependents(sno,username,fname,mname,lname,ssn,bdate,relation,pphone,sphone,gender,empadd,address1,address2,city,state,country,zip,ustatus,udate,modified_user,approved_user) select '','".$appuser."',fname,mname,lname,ssn,bdate,relation,pphone,sphone,gender,empadd,address1,address2,city,state,country,zip,'active',NOW(),'".$username."',0 from consultant_dependents where username='".$appuser."'";
		mysql_query($que,$db);

		$cand_emer = "select username from candidate_emergency where username='".$userid."'";
		$cand_emer_res = mysql_query($cand_emer,$db);
		if(mysql_num_rows($cand_emer_res)>0){
			  $que="insert into ".$table."_emergency(sno,username,fname,lname,relation,pphone,sphone,ustatus,udate,modified_user,approved_user) SELECT '','".$appuser."',fname,lname,relation,pphone,sphone,'active',NOW(),'".$username."',0 FROM candidate_emergency WHERE username = '".$userid."' ";
			 mysql_query($que,$db);
		}else{
			$que="insert into ".$table."_emergency(sno,username,fname,mname,lname,relation,pphone,sphone,address1,address2,city,state,country,zip,location,ustatus,udate,modified_user,approved_user ) select '','".$appuser."',fname,mname,lname,relation,pphone,sphone,address1,address2,city,state,country,zip,location,'active',NOW(),'".$username."',0 from consultant_emergency where username='".$appuser."'";
			mysql_query($que,$db);
		}
                $que="insert into ".$table."_garnishments(sno,username,type,title,amount,description,taxtype,compcon,ustatus,udate,deduction_code,deductionsno,start_date,stop_date,garnishment_type,garnishsno,docket_no,date_issued,sdu_statecode,sdu_caseid,frequency,calc_method,modified_user,approved_user) select '','".$appuser."',type,title,amount,description,taxtype,compcon,'active',NOW(),deduction_code,deductionsno,start_date,stop_date,garnishment_type,garnishsno,docket_no,date_issued,sdu_statecode,sdu_caseid,frequency,calc_method,'".$username."',0 from consultant_garnishments where username='".$appuser."'";
		mysql_query($que,$db);
                
                 $que="insert into ".$table."_contrib(sno,username,title,amount,description,ustatus,udate,contrib_code,contribsno,start_date,stop_date,frequency,contrib_calc_method,akku_limit,akku_maxamount,akku_maxbasis,akku_contribperiod,akku_overrideamt,override_sdate,override_edate,taxtype,modified_user,approved_user) select '','".$appuser."',title,amount,description,'active',NOW(),contrib_code,contribsno,start_date,stop_date,frequency,contrib_calc_method,akku_limit,akku_maxamount,akku_maxbasis,akku_contribperiod,akku_overrideamt,override_sdate,override_edate,taxtype,'".$username."',0 from consultant_contrib where username='".$appuser."'";
		mysql_query($que,$db);
                

		/*$que="insert into ".$table."_jobs(sno,username,client,project,manager,s_date,e_date,rate,tsapp,jtype,pusername,rateper,otrate,ustatus,udate,imethod,iterms,pterms,sagent,commision,co_type,endclient,tweeks,tdays,notes,rtime,assg_status,posid,jotype,catid,postitle,refcode,posstatus,vendor,contact,candidate,exp_edate,hired_date,posworkhr,timesheet,bamount,bcurrency,bperiod,pamount,pcurrency,pperiod,emp_prate,reason,rateperiod,ot_period,ot_currency,placement_fee,placement_curr,bill_contact,bill_address,wcomp_code,bill_req,service_terms,hire_req,addinfo,avg_interview,calctype,burden,markup,margin,prateopen,brateopen,prateopen_amt,brateopen_amt,owner,cdate,otbrate_amt,otbrate_period,otbrate_curr,otprate_amt,otprate_period,otprate_curr,payrollpid,offlocation,double_prate_amt,double_prate_period,double_prate_curr,double_brate_amt,double_brate_period, double_brate_curr,po_num,department,job_loc_tax,muser,mdate,date_placed,assign_no,deptid,attention,corp_code) select '','".$appuser."',client, project, manager, s_date, e_date, rate, tsapp, '', pusername, rateper, otrate,'active',NOW(),imethod, iterms, pterms, sagent, commision, co_type, endclient, tweeks, tdays, notes, rtime,'pending', posid, jotype, catid, postitle, refcode, posstatus, vendor, contact, candidate, exp_edate, hired_date, posworkhr, timesheet, bamount, bcurrency, bperiod, pamount, pcurrency, pperiod, emp_prate, reason, rateperiod, ot_period, ot_currency, placement_fee, placement_curr, bill_contact, bill_address, wcomp_code, bill_req, service_terms, hire_req, addinfo, avg_interview,calctype,burden,markup,margin,prateopen,brateopen,prateopen_amt,brateopen_amt,owner,cdate,otbrate_amt,otbrate_period,otbrate_curr,otprate_amt,otprate_period,otprate_curr,payrollpid,offlocation,double_prate_amt,double_prate_period,double_prate_curr,double_brate_amt,double_brate_period, double_brate_curr,po_num,department,job_loc_tax,'".$username."',NOW(),date_placed,assign_no,deptid,attention,corp_code from consultant_jobs where username='".$appuser."'"; 
		mysql_query($que,$db);
		$ass_id=mysql_insert_id($db);

		$jobSno=$ass_id;

		$queryHrcon = "SELECT sno, client, username, pusername FROM hrcon_jobs WHERE sno = ".$jobSno;
		$sqlHrcon = mysql_query($queryHrcon,$db);
		$rowHrcon = mysql_fetch_assoc($sqlHrcon);

		setDefaultEntityTaxes('Assignment', $rowHrcon['client'], 0, 0, $rowHrcon['username'], $rowHrcon['pusername']);

		$hhid=$ass_id;

		$que="insert into assign_commission(sno,username,assignid,assigntype,person,type,co_type,comm_calc,amount,roleid,overwrite) select '', '".$username."', '".$ass_id."', 'H', person, type, co_type, comm_calc ,amount, roleid, overwrite from assign_commission where assignid='".$commission_id."' and assigntype='C'";
		mysql_query($que,$db);*/

		//Candidate Information to consultant tables
		$cand_w4 = "select username from candidate_w4 where username='".$userid."'";
		$cand_w4_res = mysql_query($cand_w4,$db);
		if(mysql_num_rows($cand_w4_res)>0){
			  $que="insert into ".$table."_w4(sno,username,tax,fstatus,tnum,fwh,swh,sswh,mwh,cfwh,cswh,csswh,cmwh,aftaw,aftaw_curr,caftaw,astaw,castaw,tstatetax,federal_exempt,state_withholding,companycode,fsstatus,state_exempt,ustatus,udate,modified_user,approved_user,fed_over_type,multijobs_spouseworks,qualify_child_amt,other_dependents_amt,claim_dependents_total,other_income_amt,deduction_amt,alt_filling_status) SELECT '','".$appuser."',tax,fstatus,tnum,fwh,swh,sswh,mwh,cfwh,cswh,csswh,cmwh,aftaw,aftaw_curr,caftaw,astaw,castaw,tstatetax,federal_exempt,state_withholding,companycode,fsstatus,state_exempt,'active',NOW(),'".$username."',0,fed_over_type,multijobs_spouseworks,qualify_child_amt,other_dependents_amt,claim_dependents_total,other_income_amt,deduction_amt,alt_filling_status FROM candidate_w4 WHERE username = '".$userid."' ";
		}
		else{
			/** sanghamitra : Changed the query to insert other vendor details (bussiness name,city ,address, state,zip)in hrcon_w4 **/
			$que="insert into ".$table."_w4(sno,username,tax,fstatus,tnum,fwh,swh,sswh,mwh,cfwh,cswh,csswh,cmwh,ustatus,udate,business_name,address1,address2,city,state,zip,tstatetax,state_withholding,fsstatus,fed_over_type,modified_user,approved_user,multijobs_spouseworks,qualify_child_amt,other_dependents_amt,claim_dependents_total,other_income_amt,deduction_amt,alt_filling_status) select '','".$appuser."',consultant_w4.tax,consultant_w4.fstatus,consultant_w4.tnum,consultant_w4.fwh,consultant_w4.swh,consultant_w4.sswh,consultant_w4.mwh,consultant_w4.cfwh,consultant_w4.cswh,consultant_w4.csswh,consultant_w4.cmwh,'active',NOW(),consultant_w4.business_name,consultant_w4.address1,consultant_w4.address2,consultant_w4.city,consultant_w4.state,consultant_w4.zip,consultant_w4.tstatetax,consultant_w4.state_withholding,consultant_w4.fsstatus,consultant_w4.fed_over_type,'".$username."',0,consultant_w4.multijobs_spouseworks,consultant_w4.qualify_child_amt,consultant_w4.other_dependents_amt,consultant_w4.claim_dependents_total,consultant_w4.other_income_amt,consultant_w4.deduction_amt,consultant_w4.alt_filling_status from consultant_w4 LEFT JOIN consultant_prof ON consultant_w4.username=consultant_prof.username where consultant_w4.username='".$appuser."'";
		}
		mysql_query($que,$db);
		
		$cand_dep = "select username from candidate_deposit where username='".$userid."'";
		$cand_dep_res = mysql_query($cand_dep,$db);
		if(mysql_num_rows($cand_dep_res)>0){
			  $que="insert into ".$table."_deposit(sno,username,bankname,name,bankrtno,bankacno,delivery_method,acc1_type,acc1_payperiod,acc1_amt,acc2_name,acc2_bankname,acc2_bankrtno,acc2_bankacno,acc2_type,acc2_payperiod,acc2_amt,ustatus,udate,modified_user,approved_user) SELECT '','".$appuser."',bankname,name,bankrtno,bankacno,delivery_method,acc1_type,acc1_payperiod,acc1_amt,acc2_name,acc2_bankname,acc2_bankrtno,acc2_bankacno,acc2_type,acc2_payperiod,acc2_amt,'active',NOW(),'".$username."',0 FROM candidate_deposit WHERE username = '".$userid."' ";
			 mysql_query($que,$db);
		}else{
			 $que="insert into ".$table."_deposit(sno,username,bankname,name,bankrtno,bankacno,ustatus,udate,modified_user,approved_user) values('','".$appuser."','','','','','active',now(),'".$username."',0)";
			mysql_query($que,$db);	
		}
		
		$cand_per = "select username from candidate_personal where username='".$userid."'";
		$cand_per_res = mysql_query($cand_per,$db);
		if(mysql_num_rows($cand_per_res)>0){
			  $que="insert into ".$table."_personal(sno,username,d_birth,ssn,ssn_hash,ustatus,udate,modified_user,approved_user) SELECT '','".$appuser."',d_birth,ssn,ssn_hash,'active',NOW(),'".$username."',0 FROM candidate_personal WHERE username = '".$userid."' ";
			 mysql_query($que,$db);
		}else{
			 $que="insert into ".$table."_personal(sno,username,d_birth,b_city,b_state,b_country,ssn,ssn_hash,m_status,eef,d_death,height,hper ,weight,wper,ustatus,udate,hp_gender,modified_user,approved_user) select '','".$appuser."',d_birth,b_city,b_state,b_country,ssn,ssn_hash,m_status,eef,d_death,height,hper,weight,wper,'active',NOW(),cp_gender,'".$username."',0 from consultant_personal where username='".$appuser."'";
			mysql_query($que,$db);
		}
		//END
			

		$que="insert into ".$table."_work(sno,username,cname,city,state,country,ftitle,sdate,edate,wdesc,ustatus,udate, compensation_beginning, leaving_reason) select '','".$appuser."',cname,city,state,country,ftitle,sdate,edate,wdesc,'active',NOW(), compensation_beginning, leaving_reason from consultant_work where username='".$appuser."'";
		mysql_query($que,$db);

					
		
		//Consultant Credentials Info Insertion into Hrcon Credentials
		$objHRMCredentials->insertDataFrmConsultToHrconTable($appuser, $appuser, $emp_id);

		$table="empcon";

		$que="insert into ".$table."_general(sno,username,fname,mname,lname,email,hintq,answer,profiletitle,address1,address2 ,city,stateid,country,zip,wphone,hphone,mobile,fax,wphone_extn,hphone_extn,other_extn,state,alternate_email,other_email) select '','".$appuser."',fname,mname,lname,email,hintq,answer,profiletitle,address1,address2,city,stateid,country,zip,hphone,wphone,mobile,fax,wphone_extn,hphone_extn,other_extn,state,alternate_email,other_email from consultant_general where username='".$appuser."'";
		mysql_query($que,$db);

        $que="insert into ".$table."_prof (sno, username, objective, summary, pstatus, ifother, addinfo, avail ) select '','".$appuser."',objective,summary,pstatus,ifother,addinfo, availsdate from consultant_prof where username='".$appuser."'";
		mysql_query($que,$db);

		$que="insert into ".$table."_status(sno,username,arrivaldate,ssndate) select '','".$appuser."',arrivaldate,ssndate from consultant_status  where username='".$appuser."'";
		mysql_query($que,$db);

		$que="insert into ".$table."_skill_cat_spec(sno,username,dept_cat_spec_id,type) select '','".$appuser."',dept_cat_spec_id,type from consultant_skill_cat_spec where username='".$appuser."'";
		mysql_query($que,$db);

		$que="insert into ".$table."_skills(sno,username,skillname,lastused,skilllevel,skillyear,manage_skills_id) select '','".$appuser."',skillname,lastused,skilllevel,skillyear,manage_skills_id from consultant_skills where username='".$appuser."'";
		mysql_query($que,$db);

		$que="insert into ".$table."_location(sno,username,city,state,country) select '','".$appuser."',city,state,country from consultant_location where username='".$appuser."'";
		mysql_query($que,$db);

		$que="insert into ".$table."_edu(sno,username,heducation,educity,edustate,educountry,edudegree_level,edudate) select '','".$appuser."',heducation,educity,edustate,educountry,edudegree_level,edudate from consultant_edu where username='".$appuser."'";
		mysql_query($que,$db);

		$que="insert into ".$table."_desire(sno,username,desirejob,desirestatus,desireamount,desiretype,mpayment,desirelocation) select '','".$appuser."',desirejob,desirestatus,desireamount,desiretype,mpayment,desirelocation from consultant_desire where username='".$appuser."'";
		mysql_query($que,$db);

		$que="insert into ".$table."_aff(sno,username,affcname,affrole,affsdate,affedate) select '','".$appuser."',affcname,affrole,affsdate,affedate from consultant_aff where username='".$appuser."'";
		mysql_query($que,$db);

		$que="insert into ".$table."_ref(sno,username,name,company,title,phone,email,rship,secondary,mobile,notes,doc_id) select '','".$appuser."',name,company,title,phone,email,rship,secondary,mobile,notes,doc_id from consultant_ref where username='".$appuser."'";
		mysql_query($que,$db);

		$que="insert into ".$table."_report(sno,username,empname,description) select '','".$appuser."',empname,description from consultant_report where username='".$appuser."'";
		mysql_query($que,$db);

		$que="insert into ".$table."_work(sno,username,cname,city,state,country,ftitle,sdate,edate,wdesc, compensation_beginning, leaving_reason) select '','".$appuser."',cname,city,state,country,ftitle,sdate,edate,wdesc, compensation_beginning, leaving_reason from consultant_work where username='".$appuser."'";
		mysql_query($que,$db);
		//As per Requirement granted column name changed to max_allowed
		$que="insert into ".$table."_benifit(sno,username,eartype,max_allowed,used,avail,rollover) select '','".$appuser."',eartype,max_allowed,used,avail,rollover from consultant_benifit where username='".$appuser."'";
		mysql_query($que,$db);

		/*$que="insert into ".$table."_compen (sno, username, emp_id, dept, date_hire, location, status, salary, std_hours, over_time, rev_period, increment, bonus, designation, salper, shper, emptype, job_type, pay_assign, benchrate, benchperiod, benchcurrency, ot_period, ot_currency, timesheet,posworkhr,diem_lodging,diem_mie,diem_total,diem_period,diem_currency,diem_billable,diem_taxable,diem_billrate) select '','".$appuser."','".$emp_id."',dept,if(date_hire!='0-0-0',date_hire,date_format(now(),'%c-%d-%Y')),location,status,salary,std_hours,over_time,rev_period,increment,bonus,designation,salper,shper,emptype,job_type,pay_assign,benchrate,benchperiod,benchcurrency,ot_period,ot_currency,timesheet,posworkhr,diem_lodging,diem_mie,diem_total,diem_period,diem_currency,diem_billable,diem_taxable,diem_billrate from consultant_compen where username='".$appuser."'";
		mysql_query($que,$db);
		$compensation_em_id=mysql_insert_id($db);

		$upd_classid = "UPDATE	".$table."_compen t1,department t2 SET t1.classid = t2.classid WHERE t1.dept = t2.sno AND t1.sno = '".$compensation_em_id."'"; 	
		mysql_query($upd_classid,$db);

		$assignment_array=array("modulename" => "HR->Compensation", "pusername" => "", "userid" => $appuser, "contactsno" => $compensation_hr_id."|".$compensation_em_id, "invapproved" => 'active', "startdate" => "", "enddate" => "", "title" => "Project");	
		$compen_id=insertAssignmentSchedule($assignment_array);

		$que="insert into hrcon_tab(sno, tabsno, starthour, endhour, wdays, sch_date, coltype) select '', '".$compen_id."', starthour, endhour, wdays, sch_date,coltype from consultant_tab where consno='".$appno."' and coltype='assign'";
		mysql_query($que,$db);

		$que="insert into empcon_tab(sno, tabsno, starthour, endhour, wdays, sch_date, coltype) select '', '".$compen_id."', starthour, endhour, wdays, sch_date,coltype from consultant_tab where consno='".$appno."' and coltype='assign'";
		mysql_query($que,$db);*/
		
		/* Function is called to dump data from consultant_compen table to hrcon_compen and empcon_compen tables for Employee >> Compensation Tab -- Kumar Raju K. */
		insDataFrmConsultIntoHrcon_Empcon_CompenTables($db,$appuser,$appno,$emp_id);

		$que="insert into ".$table."_deduct(sno,username,type,title,amount,description,taxtype,compcon,deduction_code,deductionsno,start_date,stop_date,frequency,calc_method) select '','".$appuser."',type,title,amount,description,taxtype,compcon,deduction_code,deductionsno,start_date,stop_date,frequency,calc_method from consultant_deduct where username='".$appuser."'";
		mysql_query($que,$db);

		$que="insert into ".$table."_expense(sno,username,type,title,amount,description,taxtype,compcon) select '','".$appuser."',type,title,amount,description,taxtype,compcon from consultant_expense where username='".$appuser."'";
		mysql_query($que,$db);

		$que="insert into ".$table."_dependents(sno,username,fname,mname,lname,ssn,bdate,relation,pphone,sphone,gender,empadd,address1,address2,city,state,country,zip) select '','".$appuser."',fname,mname,lname,ssn,bdate,relation,pphone,sphone,gender,empadd,address1,address2,city,state,country,zip from consultant_dependents where username='".$appuser."'";
		mysql_query($que,$db);

		$cand_emer = "select username from candidate_emergency where username='".$userid."'";
		$cand_emer_res = mysql_query($cand_emer,$db);
		if(mysql_num_rows($cand_emer_res)>0){
			  $que="insert into ".$table."_emergency(sno,username,fname,lname,relation,pphone,sphone) SELECT '','".$appuser."',fname,lname,relation,pphone,sphone FROM candidate_emergency WHERE username = '".$userid."' ";
			 mysql_query($que,$db);
		}else{
			$que="insert into ".$table."_emergency(sno,username,fname,mname,lname,relation,pphone,sphone,address1,address2  ,city,state,country,zip,location) select '','".$appuser."',fname,mname,lname,relation,pphone,sphone,address1,address2,city,state,country,zip,location from consultant_emergency where username='".$appuser."'";
			mysql_query($que,$db);
		}
                $que="insert into ".$table."_garnishments(sno,username,type,title,amount,description,taxtype,compcon,deduction_code,deductionsno,start_date,stop_date,garnishment_type,garnishsno,docket_no,date_issued,sdu_statecode,sdu_caseid,frequency,calc_method) select '','".$appuser."',type,title,amount,description,taxtype,compcon,deduction_code,deductionsno,start_date,stop_date,garnishment_type,garnishsno,docket_no,date_issued,sdu_statecode,sdu_caseid,frequency,calc_method from consultant_garnishments where username='".$appuser."'";
		mysql_query($que,$db);
                
                $que="insert into ".$table."_contrib(sno,username,title,amount,description,contrib_code,contribsno,start_date,stop_date,frequency,contrib_calc_method,akku_limit,akku_maxamount,akku_maxbasis,akku_contribperiod,akku_overrideamt,override_sdate,override_edate,taxtype) select '','".$appuser."',title,amount,description,contrib_code,contribsno,start_date,stop_date,frequency,contrib_calc_method,akku_limit,akku_maxamount,akku_maxbasis,akku_contribperiod,akku_overrideamt,override_sdate,override_edate,taxtype from consultant_contrib where username='".$appuser."'";
		mysql_query($que,$db);

		/*$needApprovalAcc="pending";
		$modulename="hiring mngmt";

		$que="insert into ".$table."_jobs(sno, username, client, project, manager, s_date, e_date, rate, tsapp, jtype, pusername, rateper, otrate, imethod, iterms, pterms, sagent, commision, co_type, endclient, tweeks, tdays, notes, rtime, assg_status, posid, jotype, catid, postitle, refcode, posstatus, vendor, contact, candidate, exp_edate, hired_date, posworkhr, timesheet, bamount, bcurrency, bperiod, pamount, pcurrency, pperiod, emp_prate, reason, rateperiod, ot_period, ot_currency, placement_fee, placement_curr, bill_contact, bill_address, wcomp_code, bill_req, service_terms, hire_req, addinfo, avg_interview,modulename,calctype,burden,markup,margin,prateopen,brateopen,prateopen_amt,brateopen_amt,owner,cdate,otbrate_amt,otbrate_period,otbrate_curr,otprate_amt,otprate_period,otprate_curr,payrollpid,offlocation,double_prate_amt,double_prate_period,double_prate_curr,double_brate_amt,double_brate_period, double_brate_curr,po_num,department,job_loc_tax,muser,mdate,date_placed,diem_lodging,diem_mie,diem_total,diem_period,diem_currency,diem_billable,diem_taxable,diem_billrate,assign_no,deptid,attention,corp_code) select '','".$appuser."',client, project, manager, s_date, e_date, rate, tsapp, 'OP', pusername, rateper, otrate,imethod, iterms, pterms, sagent, commision, co_type, endclient, tweeks, tdays, notes, rtime,'".$needApprovalAcc."', posid, jotype, catid, postitle, refcode, posstatus, vendor, contact, candidate, exp_edate, hired_date, posworkhr, timesheet, bamount, bcurrency, bperiod, pamount, pcurrency, pperiod, emp_prate, reason, rateperiod, ot_period, ot_currency, placement_fee, placement_curr, bill_contact, bill_address, wcomp_code, bill_req, service_terms, hire_req, addinfo, avg_interview,'".$modulename."',calctype,burden,markup,margin,prateopen,brateopen,prateopen_amt,brateopen_amt,owner,cdate,otbrate_amt,otbrate_period,otbrate_curr,otprate_amt,otprate_period,otprate_curr,payrollpid,offlocation,double_prate_amt,double_prate_period,double_prate_curr,double_brate_amt,double_brate_period, double_brate_curr,po_num,department,job_loc_tax,'".$username."',NOW(),date_placed,diem_lodging,diem_mie,diem_total,diem_period,diem_currency,diem_billable,diem_taxable,diem_billrate,assign_no,deptid,attention,corp_code  from consultant_jobs where username='".$appuser."'";
		mysql_query($que,$db);
		$assemp_id=mysql_insert_id($db);
	
		/////////////////// UDF migration to customers ///////////////////////////////////
		//$mysql = "select posid from consultant_jobs where username='".$appuser."'";
		//$myresult = mysql_query($mysql, $db);
		//$row = mysql_fetch_assoc($myresult);
		//include_once('custom/custome_save.php');		
		//$directEmpUdfObj = new userDefinedManuplations();
		//$directEmpUdfObj->insertCrmJOToAssgn(7, $assemp_id, $row['posid']);
	
		include_once('custom/custome_save.php');		
		$directEmpUdfObj = new userDefinedManuplations();
		//$directEmpUdfObj->insertCrmJOToAssgn(7, $emp_id, $id_req);
		$directEmpUdfObj->insertUserDefinedData($assemp_id, 7);

		/////////////////////////////////////////////////////////////////////////////////

		$eeid=$assemp_id;
		$ccid=$hhid."|".$eeid;

		$que="insert into assign_commission(sno,username,assignid,assigntype,person,type,co_type,comm_calc,amount,roleid,overwrite) select '', '$username', '$assemp_id', 'E', person, type, co_type, comm_calc, amount, roleid, overwrite from assign_commission where assignid='".$commission_id."' and assigntype='C'";
		mysql_query($que,$db);

		// INserted into tabappoint for showing multiple assignments, but needs to show the calendar with hrcon_jobs
		
		$vque="select s_date,e_date,project from consultant_jobs where username='".$appuser."'";
		$vres=mysql_query($vque,$db);
		$vrow=mysql_fetch_row($vres);
		$sdt=$vrow[0];
		$edt=$vrow[1];
		$aproject=$vrow[2];

		$ssd=explode("-",$sdt);
		$month=$ssd[0];
		$day=$ssd[1];
		$year=$ssd[2];

		$usd=explode("-",$edt);
		$umonth=$usd[0];
		$uday=$usd[1];
		$uyear=$usd[2];

		$startday=mktime (0,0,0,$month,$day,$year);
		if(($umonth=="Month") || ($uday=="Day") || ($uyear=="Year"))
			$endday="nodate";
		else
			$endday=mktime (0,0,0,$umonth,$uday,$uyear);

		$date=$day."/".$month."/".$year;
		$end_date=$uday."/".$umonth."/".$uyear;

		$atitle="Project";

		$sel_assid_jobs="SELECT pusername FROM ".$table."_jobs WHERE sno='".$assemp_id."'";
		$res_assid_jobs=mysql_query($sel_assid_jobs,$db);
		$fetch_assid_jobs=mysql_fetch_row($res_assid_jobs);

		$assignment_array=array("modulename" => "HR->Assignments", "pusername" => $fetch_assid_jobs[0], "userid" => $appuser, "contactsno" => $ccid, "invapproved" => 'active', "startdate" => $date, "enddate" => $end_date, "title" => $atitle);			
		$hremp_id=insertAssignmentSchedule($assignment_array);	

		$que="insert into hrcon_tab(sno, tabsno, starthour, endhour, wdays, sch_date, coltype) select '', '".$hremp_id."', starthour, endhour, wdays, sch_date,coltype from consultant_tab where consno='".$appno."' and coltype='assign'";
		mysql_query($que,$db);

		$que="insert into empcon_tab(sno, tabsno, starthour, endhour, wdays, sch_date, coltype) select '', '".$hremp_id."', starthour, endhour, wdays, sch_date,coltype from consultant_tab where consno='".$appno."' and coltype='assign'";
		mysql_query($que,$db);*/
		
		/* Function is called to dump data from consultant_jobs table to hrcon_jobs and empcon_jobs tables for Employee >> Assignments Tab -- Kumar Raju K. */
		// referral Bonus manage
                $emp_jobdata = array();
                $emp_jobdata[0] = $emp_id;
                $emp_jobdata[1] = $refBonusJOID;
                insDataFrmConsultIntoHrcon_Empcon_JobTables($db,$appuser,$commission_id,$appno,$timeSlotsData, $burdenTypeDetails, $burdenItemDetails,$bill_burdenTypeDetails,$bill_burdenItemDetails,$shift_id,$emp_jobdata);

		//Candidate Information to consultant tables
		$cand_w4 = "select username from candidate_w4 where username='".$userid."'";
		$cand_w4_res = mysql_query($cand_w4,$db);
		if(mysql_num_rows($cand_w4_res)>0){
			  $que="insert into ".$table."_w4(sno,username,tax,fstatus,tnum,fwh,swh,sswh,mwh,cfwh,cswh,csswh,cmwh,aftaw,aftaw_curr,caftaw,astaw,castaw,tstatetax,federal_exempt,state_withholding,companycode,fsstatus,state_exempt,multijobs_spouseworks,qualify_child_amt,other_dependents_amt,claim_dependents_total,other_income_amt,deduction_amt,alt_filling_status) SELECT '','".$appuser."',tax,fstatus,tnum,fwh,swh,sswh,mwh,cfwh,cswh,csswh,cmwh,aftaw,aftaw_curr,caftaw,astaw,castaw,tstatetax,federal_exempt,state_withholding,companycode,fsstatus,state_exempt,multijobs_spouseworks,qualify_child_amt,other_dependents_amt,claim_dependents_total,other_income_amt,deduction_amt,alt_filling_status FROM candidate_w4 WHERE username = '".$userid."' ";
		}
		else{
		 	/** sanghamitra : Changed the query to insert other vendor details (bussiness name,city ,address, state,zip)in empcon_w4 **/
			$que="insert into ".$table."_w4(sno,username,tax,fstatus,tnum,fwh,swh,sswh,mwh,cfwh,cswh,csswh,cmwh,business_name,address1,address2,city,state,zip,tstatetax,state_withholding,fsstatus,fed_over_type,multijobs_spouseworks,qualify_child_amt,other_dependents_amt,claim_dependents_total,other_income_amt,deduction_amt,alt_filling_status) select '','".$appuser."',consultant_w4.tax,consultant_w4.fstatus,consultant_w4.tnum,consultant_w4.fwh,consultant_w4.swh,consultant_w4.sswh,consultant_w4.mwh,consultant_w4.cfwh,consultant_w4.cswh,consultant_w4.csswh,consultant_w4.cmwh,consultant_w4.business_name,consultant_w4.address1,consultant_w4.address2,consultant_w4.city,consultant_w4.state,consultant_w4.zip,consultant_w4.tstatetax,consultant_w4.state_withholding,consultant_w4.fsstatus,consultant_w4.fed_over_type,consultant_w4.multijobs_spouseworks,consultant_w4.qualify_child_amt,consultant_w4.other_dependents_amt,consultant_w4.claim_dependents_total,consultant_w4.other_income_amt,consultant_w4.deduction_amt,consultant_w4.alt_filling_status from consultant_w4 LEFT JOIN consultant_prof ON consultant_w4.username=consultant_prof.username where consultant_w4.username='".$appuser."'";

		}
		mysql_query($que,$db);

		$cand_dep = "select username from candidate_deposit where username='".$userid."'";
		$cand_dep_res = mysql_query($cand_dep,$db);
		if(mysql_num_rows($cand_dep_res)>0){
			  $que="insert into ".$table."_deposit(sno,username,bankname,name,bankrtno,bankacno,delivery_method,acc1_type,acc1_payperiod,acc1_amt,acc2_name,acc2_bankname,acc2_bankrtno,acc2_bankacno,acc2_type,acc2_payperiod,acc2_amt) SELECT '','".$appuser."',bankname,name,bankrtno,bankacno,delivery_method,acc1_type,acc1_payperiod,acc1_amt,acc2_name,acc2_bankname,acc2_bankrtno,acc2_bankacno,acc2_type,acc2_payperiod,acc2_amt FROM candidate_deposit WHERE username = '".$userid."' ";
			 mysql_query($que,$db);
		}else{
			$que="insert into ".$table."_deposit(sno,username,bankname,name,bankrtno,bankacno) values('','".$appuser."','','','','')";
			mysql_query($que,$db);
		}
		
		$cand_per = "select username from candidate_personal where username='".$userid."'";
		$cand_per_res = mysql_query($cand_per,$db);
		if(mysql_num_rows($cand_per_res)>0){
			  $que="insert into ".$table."_personal(sno,username,d_birth,ssn,ssn_hash) SELECT '','".$appuser."',d_birth,ssn,ssn_hash FROM candidate_personal WHERE username = '".$userid."' ";
			 mysql_query($que,$db);
		}else{
			$que="insert into ".$table."_personal(sno,username,d_birth,b_city,b_state,b_country,ssn,ssn_hash,m_status,eef,d_death,height,hper ,weight,wper,ep_gender) select '','".$appuser."',d_birth,b_city,b_state,b_country,ssn,ssn_hash,m_status,eef,d_death,height,hper,weight,wper,cp_gender from consultant_personal where username='".$appuser."'";
			mysql_query($que,$db);
		}
		//END
			

		
		$que="insert into ".$table."_work(sno,username,cname,city,state,country,ftitle,sdate,edate,wdesc, compensation_beginning, leaving_reason) select '','".$appuser."',cname,city,state,country,ftitle,sdate,edate,wdesc, compensation_beginning, leaving_reason from consultant_work where username='".$appuser."'";
		mysql_query($que,$db);

	

		$table="net";
		
		/*sanghamitra : Changed the query to insert other vendor details (bussiness name,city ,address, state,zip) in net_w4 */		
		$que="insert into ".$table."_w4(sno,username,tax,fstatus,tnum,fwh,swh,sswh,mwh,cfwh,cswh,csswh,cmwh,ustatus,udate,business_name,address1,address2,city,state,zip,tstatetax,state_withholding,fsstatus,fed_over_type,multijobs_spouseworks,qualify_child_amt,other_dependents_amt,claim_dependents_total,other_income_amt,deduction_amt,alt_filling_status) select '','".$appuser."',consultant_w4.tax,consultant_w4.fstatus,consultant_w4.tnum,consultant_w4.fwh,consultant_w4.swh,consultant_w4.sswh,consultant_w4.mwh,consultant_w4.cfwh,consultant_w4.cswh,consultant_w4.csswh,consultant_w4.cmwh,'active',NOW(),consultant_w4.business_name,consultant_w4.address1,consultant_w4.address2,consultant_w4.city,consultant_w4.state,consultant_w4.zip,consultant_w4.tstatetax,consultant_w4.state_withholding,consultant_w4.fsstatus,consultant_w4.fed_over_type,consultant_w4.multijobs_spouseworks,consultant_w4.qualify_child_amt,consultant_w4.other_dependents_amt,consultant_w4.claim_dependents_total,consultant_w4.other_income_amt,consultant_w4.deduction_amt,consultant_w4.alt_filling_status from consultant_w4 LEFT JOIN consultant_prof ON consultant_w4.username=consultant_prof.username where consultant_w4.username='".$appuser."'";
		mysql_query($que,$db);
		//As per Requirement granted column name changed to max_allowed
		$que="insert into ".$table."_benifit(sno,username,eartype,max_allowed,used,avail,rollover,ustatus,udate,extrahrs) select '','".$appuser."',eartype,max_allowed,used,avail,rollover,'active',NOW(),0 from consultant_benifit where username='".$appuser."'";
		mysql_query($que,$db);

		$que="insert into ".$table."_deduct(sno,username,type,title,amount,description,taxtype,compcon,ustatus,udate,deduction_code,deductionsno,start_date,stop_date,frequency,calc_method) select '','".$appuser."',type,title,amount,description,taxtype,compcon,'active',NOW(),deduction_code,deductionsno,start_date,stop_date,frequency,calc_method from consultant_deduct where username='".$appuser."'";
		mysql_query($que,$db);

		$que="insert into ".$table."_expense(sno,username,type,title,amount,description,taxtype,compcon,ustatus,udate) select '','".$appuser."',type,title,amount,description,taxtype,compcon,'active',NOW() from consultant_expense where username='".$appuser."'";
		mysql_query($que,$db);
                
                $que="insert into ".$table."_garnishments(sno,username,type,title,amount,description,taxtype,compcon,ustatus,udate,deduction_code,deductionsno,start_date,stop_date,garnishment_type,garnishsno,docket_no,date_issued,sdu_statecode,sdu_caseid,frequency,calc_method) select '','".$appuser."',type,title,amount,description,taxtype,compcon,'active',NOW(),deduction_code,deductionsno,start_date,stop_date,garnishment_type,garnishsno,docket_no,date_issued,sdu_statecode,sdu_caseid,frequency,calc_method from consultant_garnishments where username='".$appuser."'";
		mysql_query($que,$db);
                
                 $que="insert into ".$table."_contrib(sno,username,title,amount,description,ustatus,udate,contrib_code,contribsno,start_date,stop_date,frequency,contrib_calc_method,akku_limit,akku_maxamount,akku_maxbasis,akku_contribperiod,akku_overrideamt,override_sdate,override_edate,taxtype) select '','".$appuser."',title,amount,description,'active',NOW(),contrib_code,contribsno,start_date,stop_date,frequency,contrib_calc_method,akku_limit,akku_maxamount,akku_maxbasis,akku_contribperiod,akku_overrideamt,override_sdate,override_edate,taxtype from consultant_contrib where username='".$appuser."'";
		mysql_query($que,$db);

		$cque="SELECT COUNT(1) FROM hrcon_personal WHERE username='".$appuser."' AND ustatus='active'";
		$cres=mysql_query($cque,$db);
		$crow=mysql_fetch_row($cres);
		if($crow[0]==0)
		{
			$ique="INSERT INTO hrcon_personal (username,ustatus,udate) VALUES ('".$appuser."','active',NOW())";
			mysql_query($ique,$db);
		}

		$cque="SELECT COUNT(1) FROM empcon_personal WHERE username='".$appuser."'";
		$cres=mysql_query($cque,$db);
		$crow=mysql_fetch_row($cres);
		if($crow[0]==0)
		{
			$ique="INSERT INTO hrcon_personal (username) VALUES ('".$appuser."')";
			mysql_query($ique,$db);
		}
		
		//Consultant Credentials Info Insertion into Empcon Credentials
		$objHRMCredentials->insertDataFrmConsultToEmpconTable($appuser, $appuser, $emp_id);
		if(SHIFT_SCHEDULING_ENABLED == 'Y') 
		{
			//insert into employee time slots for shift management from consultant to employee
			insTimeSlotsFrmConsultantTohrcon($db,$appuser);
		}
		
	}

	function checkHrconw4Records($conusername)
	{
		global $username,$db;

		// checking hrcon_w4 is having record for the employee or not
		$selhrcon_w4 = "select username from hrcon_w4 where username='".$conusername."' AND ustatus='active'";
		$res_hrcon_w4 = mysql_query($selhrcon_w4,$db);
		if(mysql_num_rows($res_hrcon_w4)>0){
		}else{
			$insthrconw4="INSERT INTO hrcon_w4 (username,tax,ustatus,udate) VALUES ('".$conusername."','W-2','active',NOW())";
    		mysql_query($insthrconw4,$db);
		}

		// checking hrcon_w4 is having record for the employee or not
		$selempcon_w4 = "select username from empcon_w4 where username='".$conusername."'";
		$res_empcon_w4 = mysql_query($selempcon_w4,$db);
		if(mysql_num_rows($res_empcon_w4)>0){
		}else{
			$instempconw4="INSERT INTO empcon_w4 (username,tax)VALUES('".$conusername."','W-2')";
    		mysql_query($instempconw4,$db);
		}

	}

	function userHistory($page152,$conusername,$contype,$uname)
	{
		global $username,$maildb,$db;

		$tnotes="\nCustomer	 :  ".$page152[1]."\nStart Date	 :  ".$page152[2]."\nEnd Date  	 :  ".$page152[3]."\nRegular Rate	 :  ".$page152[4]."\nOver Time Rate Per Hour 	 :  ".$page152[6]."\nTime Sheet Approval	 :  ".$page152[7]."\nPayment Terms 	 :  ".$page152[8]." Days \nInvoice Method	 :  ".$page152[9]."\nInvoice Terms 	 :  ".$page152[10]."\nSale Agent	 :  ".$page152[11]."\nCommision  	 :  ".$page152[12]."\nProject Name  	 :  ".$page152[13]."\nCompany Type 	 :  ".$page152[14]."\nClient  	 :  ".$page152[15]."\nManager 	 :  ".$page152[16]."\nStatus	 	 :  ".$page152[17];

		if($contype=="Employee")
			$ctype="New Assignment";
		else
			$ctype="New Placement";

		$que="INSERT INTO tasklist ( sno, type, title, startdate, duedate, taskstatus, priority, percom, notes, rem, remdate, datecom, totwork, actwork, companies, mileage, billinfo, cuser, sendto, datecreated, description, status, contactsno, modulename) VALUES ('','Reminder','".$ctype."',NOW(),'','In Progress','','','".$tnotes."','','','','','','','','','".$uname."','',NOW(),'','new','".$conusername."','Req_Mngmt')";
		mysql_query($que,$db);
		$taskid=mysql_insert_id($db);

		$que="insert into cmngmt_pr (sno, con_id, username, tysno, title, sdate, subject,  lmuser) values('','".$conusername."','".$uname."','".$taskid."','Task',NOW(),'".$ctype."','".$uname."')";
		mysql_query($que,$db);
	}

	function UpdateConsultant($conusername,$id_req,$uname,$canduser)
	{
		global $username, $maildb,$db,$typeOfPlacement,$supid,$objHRMCredentials;

		

		$sel_query = "SELECT cg.fname, cg.mname, cg.lname, cg.email, cg.profiletitle, cg.address1, cg.address2, cg.city, cg.state, cg.country, cg.zip, cg.wphone, cg.hphone, cg.mobile, cg.fax, cg.other, cg.prefix, cg.cphone, cg.cmobile, cg.cfax, cg.cemail, cg.wphone_extn, cg.hphone_extn, cg.other_extn, cg.stateid, cg.alternate_email, cg.other_email, cl.wotc_mja_status, cl.tcc_status FROM candidate_general cg LEFT JOIN candidate_list cl ON cl.username=cg.username WHERE cg.username = '".$canduser."'";
		$res_query = mysql_query($sel_query,$db);
		$cand_data = mysql_fetch_assoc($res_query);

		$wotc_mja_status = $cand_data['wotc_mja_status'];
		$tcc_status 	 = $cand_data['tcc_status'];

		$state_result = explode('|AKKENEXP|',getStateIdAbbr($cand_data['state'],$cand_data['country']));
		$getstateid=$state_result[2];
		$getstatename=$state_result[1];

		$name = $cand_data['fname']." ".$cand_data['mname']." ".$cand_data['lname'];
		$email = $cand_data['email'];

		$upd_qry = "UPDATE consultant_general SET fname='".$cand_data['fname']."', mname='".$cand_data['mname']."', lname='".$cand_data['lname']."', email='".$cand_data['email']."', profiletitle='".$cand_data['profiletitle']."', address1='".$cand_data['address1']."', address2='".$cand_data['address2']."', city='".$cand_data['city']."', state='".addslashes($getstatename)."', country='".$cand_data['country']."', zip='".$cand_data['zip']."', wphone='".$cand_data['wphone']."', hphone='".$cand_data['hphone']."', mobile='".$cand_data['mobile']."', fax='".$cand_data['fax']."', other='".$cand_data['other']."', prefix='".$cand_data['prefix']."', cphone='".$cand_data['cphone']."', cmobile='".$cand_data['cmobile']."', cfax='".$cand_data['cfax']."', cemail='".$cand_data['cemail']."',wphone_extn='".$cand_data['wphone_extn']."',hphone_extn='".$cand_data['hphone_extn']."',other_extn='".$cand_data['other_extn']."',stateid='".$getstateid."',alternate_email='".$cand_data['alternate_email']."',other_email='".$cand_data['other_email']."' WHERE username = '".$conusername."'";
		mysql_query($upd_qry,$db);

		$sel_query = "SELECT objective, summary, pstatus, ifother, addinfo, availsdate, availedate FROM candidate_prof WHERE username = '".$canduser."'";
		$res_query = mysql_query($sel_query,$db);
		$cand_data = mysql_fetch_assoc($res_query);

		$status = $cand_data['pstatus'];
		$avail = $cand_data['availsdate'];

		$upd_qry = "UPDATE consultant_prof SET objective='".$cand_data['objective']."', summary='".$cand_data['summary']."', pstatus='".$cand_data['pstatus']."', ifother='".$cand_data['ifother']."', addinfo='".$cand_data['addinfo']."', availsdate='".$cand_data['availsdate']."', availedate='".$cand_data['availedate']."' WHERE username = '".$conusername."'";
		mysql_query($upd_qry,$db);

		$del_sql="DELETE FROM consultant_skills where username = '".$conusername."'";
		mysql_query($del_sql,$db);

		$sql_que = "INSERT INTO consultant_skills (sno, username, skillname, lastused, skilllevel, skillyear, manage_skills_id) SELECT '','".$conusername."', skillname, lastused, skilllevel, skillyear, manage_skills_id FROM candidate_skills WHERE username = '".$canduser."'";
		mysql_query($sql_que,$db);

		$del_sql="DELETE FROM consultant_skill_cat_spec where username = '".$conusername."'";
		mysql_query($del_sql,$db);

		$sql_que = "INSERT INTO consultant_skill_cat_spec (sno, username, dept_cat_spec_id, type) SELECT '','".$conusername."', dept_cat_spec_id, type FROM candidate_skill_cat_spec WHERE username = '".$canduser."'";
		mysql_query($sql_que,$db);

		$del_sql="DELETE FROM consultant_edu  where  username = '".$conusername."'";
		mysql_query($del_sql,$db);

		$sql_que = "INSERT INTO consultant_edu (sno, username, heducation, educity, edustate, educountry, edudegree_level, edudate) SELECT '','".$conusername."', heducation, educity, edustate, educountry, edudegree_level, edudate FROM candidate_edu WHERE username = '".$canduser."'";
		mysql_query($sql_que,$db);

		$del_sql="DELETE FROM consultant_work where  username = '".$conusername."'";
		mysql_query($del_sql,$db);

		$sql_que = "INSERT INTO consultant_work (sno, username, cname, city, state, country, ftitle, sdate, edate, wdesc, compensation_beginning, leaving_reason) SELECT '','".$conusername."', cname, city, state, country, ftitle, sdate, edate, wdesc, compensation_beginning, leaving_reason FROM candidate_work WHERE username = '".$canduser."'";
		mysql_query($sql_que,$db);

		$sel_query = "SELECT desirejob, desirelocation, desirestatus, city, state, country, amount, currency, period FROM candidate_pref WHERE username = '".$canduser."'";
		$res_query = mysql_query($sel_query,$db);
		$cand_data = mysql_fetch_assoc($res_query);

		$rate = $cand_data['amount']." ".$cand_data['currency']."/".$cand_data['period'];

		$upd_qry = "UPDATE consultant_desire SET desirejob='".$cand_data['desirejob']."', desirestatus='".$cand_data['desirestatus']."', desireamount='".$cand_data['amount']."', desiretype='".$cand_data['currency']."', mpayment='".$cand_data['period']."', desirelocation='".$cand_data['desirelocation']."' WHERE username = '".$conusername."'";
		mysql_query($upd_qry,$db);

		$upd_qry = "UPDATE consultant_location SET city='".$cand_data['city']."', state='".$cand_data['state']."', country='".$cand_data['country']."' WHERE username = '".$conusername."'";
		mysql_query($upd_qry,$db);

		$del_sql="DELETE FROM consultant_aff  where  username = '".$conusername."'";
		mysql_query($del_sql,$db);

		$sql_que = "INSERT INTO consultant_aff (sno, username, affcname, affrole, affsdate, affedate) SELECT '','".$conusername."', affcname, affrole, affsdate, affedate FROM candidate_aff WHERE username = '".$canduser."'";
		mysql_query($sql_que,$db);

		//Candidate Information with POB details to Consultant tables
		$cand_w4 = "select * from candidate_w4 where username='".$canduser."'";
		$cand_w4_res = mysql_query($cand_w4,$db);
		if(mysql_num_rows($cand_w4_res)>0){
			$cand_w4_row = mysql_fetch_assoc($cand_w4_res);
			$que="update consultant_w4 set tax='".mysql_real_escape_string($cand_w4_row['tax'])."',fstatus='".mysql_real_escape_string($cand_w4_row['fstatus'])."',tnum='".mysql_real_escape_string($cand_w4_row['tnum'])."',fwh='".mysql_real_escape_string($cand_w4_row['fwh'])."',swh='".mysql_real_escape_string($cand_w4_row['swh'])."',sswh='".mysql_real_escape_string($cand_w4_row['sswh'])."',mwh='".mysql_real_escape_string($cand_w4_row['mwh'])."',cfwh='".mysql_real_escape_string($cand_w4_row['cfwh'])."',cswh='".mysql_real_escape_string($cand_w4_row['cswh'])."',csswh='".mysql_real_escape_string($cand_w4_row['csswh'])."',cmwh='".mysql_real_escape_string($cand_w4_row['cmwh'])."',aftaw='".mysql_real_escape_string($cand_w4_row['aftaw'])."',aftaw_curr='".mysql_real_escape_string($cand_w4_row['aftaw_curr'])."',caftaw='".mysql_real_escape_string($cand_w4_row['caftaw'])."',astaw='".mysql_real_escape_string($cand_w4_row['astaw'])."',castaw='".mysql_real_escape_string($cand_w4_row['castaw'])."',tstatetax='".mysql_real_escape_string($cand_w4_row['tstatetax'])."',federal_exempt='".mysql_real_escape_string($cand_w4_row['federal_exempt'])."',state_withholding='".mysql_real_escape_string($cand_w4_row['state_withholding'])."',companycode='".mysql_real_escape_string($cand_w4_row['companycode'])."',fsstatus='".mysql_real_escape_string($cand_w4_row['fsstatus'])."',state_exempt='".mysql_real_escape_string($cand_w4_row['state_exempt'])."',multijobs_spouseworks='".mysql_real_escape_string($cand_w4_row['multijobs_spouseworks'])."',qualify_child_amt='".mysql_real_escape_string($cand_w4_row['qualify_child_amt'])."',other_dependents_amt='".mysql_real_escape_string($cand_w4_row['other_dependents_amt'])."',claim_dependents_total='".mysql_real_escape_string($cand_w4_row['claim_dependents_total'])."',other_income_amt='".mysql_real_escape_string($cand_w4_row['other_income_amt'])."',deduction_amt='".mysql_real_escape_string($cand_w4_row['deduction_amt'])."',alt_filling_status='".mysql_real_escape_string($cand_w4_row['alt_filling_status'])."' WHERE username = '".$conusername."' ";
			mysql_query($que,$db);
		}else{

             //Getting recruiter id is there r not for candidate
			$rec_que = "SELECT supid from candidate_list WHERE username = '".$canduser."'";
			$q1 = mysql_query($rec_que,$db);
			$chksup_res=mysql_fetch_array($q1);
			$chk_supid=$chksup_res['supid'];

			if($chk_supid != '0' && $chk_supid != "")
			{
				$rec_com_que = "SELECT com.cname as bname,com.address1 as add1,com.address2 as add2,com.city as city,com.state as state,com.zip as zip from staffoppr_contact con LEFT JOIN staffoppr_cinfo com on con.csno = com.sno LEFT JOIN manage t1 ON com.compstatus = t1.sno WHERE con.sno= '".$chk_supid."' AND t1.name LIKE '%vendor%' AND t1.type='compstatus'";
			    $q2 = mysql_query($rec_com_que,$db);
				$chkcom_res=mysql_fetch_array($q2);
				
			    $v_business_name=$chkcom_res['bname'];
				$v_city=$chkcom_res['city'];
				$v_add1=$chkcom_res['add1'];
				$v_add2=$chkcom_res['add2'];
				$v_state=$chkcom_res['state'];
				$v_zip=$chkcom_res['zip'];

				$upd_tax_que="update consultant_w4 set tax = 'C-to-C', business_name='".addslashes($v_business_name)."', 
				address1='".addslashes($v_add1)."', address2='".addslashes($v_add2)."', city='".addslashes($v_city)."', state='".addslashes($v_state)."', zip='".$v_zip."' WHERE username = '".$conusername."'";
				mysql_query($upd_tax_que,$db);

		    }
		}
		
		// Checking consultant_w4 record there are not
		$selectw4 = "SELECT * FROM consultant_w4 WHERE username = '".$conusername."'";
	    $resultw4 = mysql_fetch_row($selectw4,$db);
	    if (mysql_num_rows($resultw4)>0) {
	    	# code...
	    }else{
	    	// Inserting default values
	    	$insertw4 = "INSERT INTO consultant_w4 (username,tax) ('".$conusername."','W-2')";
	    	mysql_query($insertw4,$db);
	    }
	    // END

		$cand_dep = "select username from candidate_deposit where username='".$canduser."'";
		$cand_dep_res = mysql_query($cand_dep,$db);
		if(mysql_num_rows($cand_dep_res)>0){
			
			$cand_dep_del_sql="DELETE FROM consultant_deposit where username = '".$conusername."'";
			mysql_query($cand_dep_del_sql,$db);
		
			$que="insert into consultant_deposit(sno,username,bankname,name,bankrtno,bankacno,delivery_method,acc1_type,acc1_payperiod,acc1_amt,acc2_name,acc2_bankname,acc2_bankrtno,acc2_bankacno,acc2_type,acc2_payperiod,acc2_amt) SELECT '','".$conusername."',bankname,name,bankrtno,bankacno,delivery_method,acc1_type,acc1_payperiod,acc1_amt,acc2_name,acc2_bankname,acc2_bankrtno,acc2_bankacno,acc2_type,acc2_payperiod,acc2_amt FROM candidate_deposit WHERE username = '".$canduser."' ";
			mysql_query($que,$db);
		}
		
		$cand_per = "select username from candidate_personal where username='".$canduser."'";
		$cand_per_res = mysql_query($cand_per,$db);
		if(mysql_num_rows($cand_per_res)>0){
			
			$cand_per_del_sql="DELETE FROM consultant_personal where username = '".$conusername."'";
			mysql_query($cand_per_del_sql,$db);
			
			$que="insert into consultant_personal(sno,username,d_birth,ssn,ssn_hash) SELECT '','".$conusername."',d_birth,ssn,ssn_hash FROM candidate_personal WHERE username = '".$canduser."' ";
			mysql_query($que,$db);
		} 
		
		$cand_emer = "select username from candidate_emergency where username='".$canduser."'";
		$cand_emer_res = mysql_query($cand_emer,$db);
		if(mysql_num_rows($cand_emer_res)>0){
			
			$cand_emer_del_sql="DELETE FROM consultant_emergency where username = '".$conusername."'";
			mysql_query($cand_emer_del_sql,$db);
			
			$que="insert into consultant_emergency(sno,username,fname,lname,relation,pphone,sphone) SELECT '','".$conusername."',fname,lname,relation,pphone,sphone FROM candidate_emergency WHERE username = '".$canduser."' ";
			mysql_query($que,$db);
		}
		$del_sql="DELETE FROM consultant_ref where  username = '".$conusername."'";
		mysql_query($del_sql,$db);

                // added New code to update consultant_ref including the attachments
                $sql_cand_que = "SELECT '','".$conusername."', name, company, title, phone, email, rship, secondary, mobile ,notes ,doc_id  FROM candidate_ref WHERE username = '".$canduser."'";
                $sql_cand_res = mysql_query($sql_cand_que,$db);
                while($row_sub_ref=mysql_fetch_row($sql_cand_res))
		{   
                   
                    $checkcontact = "SELECT sno, con_id FROM contact_doc  WHERE con_id = '".$conusername."' AND sno='".$row_sub_ref[11]."'";
                    $checkcontactres = mysql_query($checkcontact,$db);
                    if(mysql_num_rows($checkcontactres)>0){
                        $checkcontactrow = mysql_fetch_row($checkcontactres); 
                        $sql_que_main = "INSERT INTO consultant_ref (sno, username, name, company, title, phone, email, rship, secondary, mobile, notes, doc_id) VALUES('','".$conusername."','".addslashes($row_sub_ref[2])."','".addslashes($row_sub_refrow_sub[3])."','".addslashes($row_sub_ref[4])."','".$row_sub_ref[5]."','".$row_sub_ref[6]."','".$row_sub_ref[7]."','".$row_sub_ref[8]."','".$row_sub_ref[9]."','".addslashes($row_sub_ref[10])."','".$checkcontactrow[0]."')";
                        mysql_query($sql_que_main,$db);
                    }else {
                     
                        $ins_doc_query= "INSERT INTO contact_doc (sno, con_id, username, title, docname, body, sdate, doctype) SELECT '','".$conusername."',username, title, docname, body, NOW(), doctype FROM contact_doc WHERE con_id = '".$canduser."' AND sno='".$row_sub_ref[11]."'"; 
                        mysql_query($ins_doc_query,$db);
                        $contact_doc_id= mysql_insert_id($db);

                        $sql_que_main = "INSERT INTO consultant_ref (sno, username, name, company, title, phone, email, rship, secondary, mobile, notes, doc_id) VALUES('','".$conusername."','".addslashes($row_sub_ref[2])."','".addslashes($row_sub_refrow_sub[3])."','".addslashes($row_sub_ref[4])."','".$row_sub_ref[5]."','".$row_sub_ref[6]."','".$row_sub_ref[7]."','".$row_sub_ref[8]."','".$row_sub_ref[9]."','".addslashes($row_sub_ref[10])."','".$contact_doc_id."')";
                        mysql_query($sql_que_main,$db);
                    } 
                }
		
		$que = "select * from candidate_skills where username='".$canduser."'";
		$res_sub = mysql_query($que,$db);
		while($row_sub=mysql_fetch_row($res_sub))
		{
			if($conskills=="")
				$conskills=$row_sub[2]."(".$row_sub[5].")";
			else
				$conskills.=", ".$row_sub[2]."(".$row_sub[5].")";
		}

		$chk_qry="SELECT count(1) FROM consultant_list WHERE username = '".$conusername."'";
		$chkres=mysql_query($chk_qry,$db);
		$chkrow=mysql_fetch_row($chkres);
		if($chkrow[0]==0)
			$upd_qry = "INSERT INTO consultant_list (serial_no, username, name, email, status, avail, rate, skills,   astatus, stime,accessto) VALUES ('', '".$conusername."', '".$name."', '".$email."', '".$status."', '".$avail."', '".$rate."', '".$conskills."','backup', NOW(), '".$username."')";
		else
			$upd_qry ="UPDATE consultant_list SET name='".$name."', email='".$email."', status='".$status."', avail='".$avail."',rate='".$rate."', skills='".$conskills."', astatus='backup' WHERE username = '".$conusername."'";
		mysql_query($upd_qry,$db);

		$app_qry="SELECT jobtitle FROM applicants WHERE username = '".$conusername."'";
		$appres=mysql_query($app_qry,$db);
		$appnum=mysql_num_rows($appres);

		if($appnum==0)
		{
			$con_que1 = "INSERT INTO applicants (serial_no, username, name, email, jobtitle, status, avail, rate, plocation, skills, reffered, astatus, apmark, con_person, type, recruiter, hr, admin, stime, wotc_mja_status, tcc_status) SELECT '',username,name,email,'".$id_req."',status,avail,rate,plocation,skills,reffered,if('".$typeOfPlacement."'!='DIRECT','','hire'),'".$username."','','con','','+F','',NOW(),'".$wotc_mja_status."','".$tcc_status."' FROM consultant_list WHERE username='".$conusername."'";
			mysql_query($con_que1,$db);

			$app_id = mysql_insert_id($db);		
		}
		else
		{
			$new_jobtile="";
			while($approw=mysql_fetch_row($appres))
			{
				if($new_jobtile=="")
					$new_jobtile=$approw[0];
				else
					$new_jobtile=$new_jobtile.",".$approw[0];
			}
			$new_jobtile=$new_jobtile.",".$id_req;
			$con_que1 = "update applicants set jobtitle='".$new_jobtile."',name='".$name."', email='".$email."', status='".$status."', avail='".$avail."',rate='".$rate."', skills='".$conskills."', wotc_mja_status='".$wotc_mja_status."', tcc_status='".$tcc_status."' WHERE username='".$conusername."'";
			mysql_query($con_que1,$db);

			// Select Serial Number for CDF rec_id in udf_form_details_applicant_values
			$selAppIdQry = "SELECT serial_no FROM applicants WHERE username='".$conusername."'";
			$selAppIdRes = mysql_query($selAppIdQry,$db);
			$selAppIdRow = mysql_fetch_array($selAppIdRes);
			$app_id		 = $selAppIdRow['serial_no'];
		}

		if($app_id!='')
		{
			if($typeOfPlacement!='DIRECT')
			{
				/* UDF Values for HRM Applicant Tracking Records from CRM Candidates */
				include_once('custom/custome_save.php');		
				$directEmpUdfObj = new userDefinedManuplations();
				$directEmpUdfObj->insertHrmConsultApplicants(3, $app_id, $conusername);
			}
		}

		$con_que2 = "INSERT INTO apphistory (sno, username, notes, sdate, appuser, status) VALUES ('','".$conusername."','Forwarded as Independent Consultant to Hiring.',CURRENT_DATE,'".$username."','Forwarded')";
		mysql_query($con_que2,$db);
		
		//Candidate Credentials to Consultant Credentials
		$lastins_consultid	= $objHRMCredentials->getApplicantSerialNo($conusername);
		$objHRMCredentials->insertDataFrmCandToConsultTable($canduser, $conusername, $lastins_consultid, 'update');
	}
	
	/* Dumping data from consultant_compen table to hrcon_compen and empcon_compen tables for Employee >> Compensation Tab -- Kumar Raju K. */
	function insDataFrmConsultIntoHrcon_Empcon_CompenTables($db,$appuser,$appno,$emp_id)
	{
		global $username;

		$selConsult_Query = "SELECT sno FROM consultant_compen WHERE username='".$appuser."'";
		$resConsult_Query = mysql_query($selConsult_Query,$db);
		$resConsult_Count = mysql_num_rows($resConsult_Query);
		
		if($resConsult_Count > 0) {

			while($rowConsult_Query = mysql_fetch_row($resConsult_Query)) {

				/*Dumping data from consultant_compen table to hrcon_compen table for Employee Compensation Tab */
				$insHrcon_CompenQuery = "INSERT INTO hrcon_compen (sno, username, emp_id, dept, date_hire, location, status,
					salary, std_hours, over_time, rev_period, increment, bonus, designation, salper, shper,
					emptype, job_type, pay_assign, benchrate, benchperiod, benchcurrency, ot_period,
					ot_currency, timesheet, posworkhr, ustatus, diem_lodging, diem_mie, diem_total,
					diem_period, diem_currency, diem_billable, diem_taxable, diem_billrate,paygroupcode,paycodesno,modified_user,approved_user)
					SELECT '', '".$appuser."', '".$emp_id."', if(dept!='',dept,'1'),
					if(date_hire!='0-0-0',date_hire,date_format(now(),'%c-%d-%Y')), location, status, salary,
					std_hours, over_time, rev_period, increment, bonus, designation, salper, shper, emptype,
					job_type, pay_assign, benchrate, benchperiod, benchcurrency, ot_period, ot_currency,
					timesheet, posworkhr, 'active', diem_lodging, diem_mie, diem_total, diem_period,
					diem_currency, diem_billable, diem_taxable, diem_billrate,paygroupcode,paycodesno,'".$username."',0
					FROM consultant_compen WHERE sno = '".$rowConsult_Query[0]."'";
				mysql_query($insHrcon_CompenQuery,$db);
	
				/*Getting Last Inserted ID from the hrcon_compen table*/
				$hrconCompen_ID = mysql_insert_id($db);

				/*Updating the Class ID for hrcon_compen table using department table */
				$updHrcon_CompenClassId = "UPDATE hrcon_compen t1, department t2
					SET t1.classid = t2.classid
					WHERE t1.dept = t2.sno AND t1.sno = '".$hrconCompen_ID."'"; 	
				mysql_query($updHrcon_CompenClassId,$db);
				
				/*Dumping data from consultant_compen table to empcon_compen table for Employee Compensation Tab */
				$insEmpcon_CompenQuery = "INSERT INTO empcon_compen (sno, username, emp_id, dept, date_hire,
					location, status, salary, std_hours, over_time, rev_period, increment, bonus, designation,
					salper, shper, emptype, job_type, pay_assign, benchrate, benchperiod, benchcurrency, ot_period,
					ot_currency, timesheet, posworkhr, diem_lodging, diem_mie, diem_total, diem_period,
					diem_currency, diem_billable, diem_taxable, diem_billrate,paygroupcode,paycodesno)
					SELECT '', '".$appuser."', '".$emp_id."', if(dept!='',dept,'1'),
					if(date_hire!='0-0-0',date_hire,date_format(now(),'%c-%d-%Y')), location, status, salary,
					std_hours, over_time, rev_period, increment, bonus, designation, salper, shper, emptype,
					job_type, pay_assign, benchrate, benchperiod, benchcurrency, ot_period, ot_currency,
					timesheet, posworkhr, diem_lodging, diem_mie, diem_total, diem_period, diem_currency,
					diem_billable, diem_taxable, diem_billrate,paygroupcode,paycodesno
					FROM consultant_compen WHERE sno = '".$rowConsult_Query[0]."'";
				mysql_query($insEmpcon_CompenQuery,$db);

				/*Getting Last Inserted ID from the empcon_compen table*/
				$empconCompen_ID = mysql_insert_id($db);

				/*Updating the Class ID for empcon_compen table using department table */
				$updEmpcon_CompenClassId = "UPDATE empcon_compen t1, department t2
					SET t1.classid = t2.classid
					WHERE t1.dept = t2.sno AND t1.sno = '".$empconCompen_ID."'"; 	
				mysql_query($updEmpcon_CompenClassId,$db);

				/*Preparing the array to create assignment schedule records for Compensation tab using hrcon_compen.sno and empcon_compen.sno values */
				$compensation_Array = array("modulename" => "HR->Compensation", "pusername" => "", "userid" => $appuser, "contactsno" => $hrconCompen_ID."|".$empconCompen_ID, "invapproved" => 'active', "startdate" => "", "enddate" => "", "title" => "Project");

				/*Calling insertAssignmentSchedule function to dump data from the above array into assignment_schedule table */
				$compenAssignSchedule_ID = insertAssignmentSchedule($compensation_Array);
				
				if(SHIFT_SCHEDULING_ENABLED == 'N') 
				{
					/*Dumping data from consultant_tab table to hrcon_tab table for Compensation */
					$insHrcon_TabQuery = "INSERT INTO hrcon_tab(sno, tabsno, starthour, endhour, wdays, sch_date, coltype)
					SELECT '', '".$compenAssignSchedule_ID."', starthour, endhour, wdays, sch_date, coltype
					FROM consultant_tab WHERE consno = '".$appno."' AND coltype = 'assign'";
					mysql_query($insHrcon_TabQuery,$db);

					/*Dumping data from consultant_tab table to empcon_tab table for Compensation */
					$insEmpcon_TabQuery = "INSERT INTO empcon_tab(sno, tabsno, starthour, endhour, wdays, sch_date, coltype)
					SELECT '', '".$compenAssignSchedule_ID."', starthour, endhour, wdays, sch_date, coltype
					FROM consultant_tab WHERE consno = '".$appno."' AND coltype = 'assign'";
					mysql_query($insEmpcon_TabQuery,$db);
				}

			}
		}
	}
	
	/* Dumping data from consultant_jobs table to hrcon_jobs and empcon_jobs tables for Employee >> Assignments Tab -- Kumar Raju K. */
	function insDataFrmConsultIntoHrcon_Empcon_JobTables($db,$appuser,$commission_id,$appno,$timeSlotsData, $burdenTypeDetails,$burdenItemDetails,$bill_burdenTypeDetails,$bill_burdenItemDetails,$shift_id=NULL,$emp_jobdata)
	{
		global $username;
                
                 // Udpating referral related data (assignment id etc.,) in cand_refer table for direct Job Type
                $employee_id = $emp_jobdata[0];
                $placedJobOrderID = $emp_jobdata[1];
                $ref_status_PQ=getManageSno('Pending Qualifications','referral_status');
                $ref_status_PP=getManageSno('Pending Payments','referral_status');
                $candref_query = "SELECT ref_id,req_id,assign_id,emp_id,username,sno,bonus_calc_type,bonus_amount FROM cand_refer WHERE ref_id='".$appuser."' AND req_id='".$placedJobOrderID."' AND referral_status='0'";
                $candref_query_res=mysql_query($candref_query,$db);
                $candref_query_count=mysql_num_rows($candref_query_res);
                if($candref_query_count==1){
                    $candref_row=  mysql_fetch_row($candref_query_res);
                }
                // code related referral ends here //

		$selConsult_Query = "SELECT sno, s_date, e_date, pusername, posid FROM consultant_jobs WHERE username = '".$appuser."'";
		$resConsult_Query = mysql_query($selConsult_Query,$db);
		$resConsult_Count = mysql_num_rows($resConsult_Query);
		
		if($resConsult_Count > 0) {

			while($rowConsult_Query = mysql_fetch_array($resConsult_Query)) {
				
				
				$jtype_hrcon		= "OP";
				$ustatus_hrcon		= "pending";
				$modulename_hrcon 	= "hiring mngmt";

				/*Dumping data from consultant_jobs table to hrcon_jobs table for Employee Assignments Tab */
				$insHrcon_JobsQuery = "INSERT INTO hrcon_jobs(sno, username, client, project, manager, s_date, e_date,
					rate, tsapp, jtype, pusername, rateper, otrate, ustatus, udate, imethod, iterms, pterms, sagent,
					commision, co_type, endclient, tweeks, tdays, notes, rtime, assg_status, posid, jotype, catid,
					postitle, refcode, posstatus, vendor, contact, candidate, exp_edate, hired_date, posworkhr,
					timesheet, bamount, bcurrency, bperiod, pamount, pcurrency, pperiod, emp_prate, reason,
					rateperiod, ot_period, ot_currency, placement_fee, placement_curr, bill_contact, bill_address,
					wcomp_code, bill_req, service_terms, hire_req, addinfo, avg_interview, calctype, burden, markup,
					margin, prateopen, brateopen, prateopen_amt, brateopen_amt, owner, cdate, otbrate_amt,
					otbrate_period, otbrate_curr, otprate_amt, otprate_period, otprate_curr, payrollpid,
					offlocation, double_prate_amt, double_prate_period, double_prate_curr, double_brate_amt,
					double_brate_period, double_brate_curr, po_num, department, job_loc_tax, muser, mdate, date_placed,
					assign_no, deptid, attention, corp_code, industryid,schedule_display,bill_burden,shiftid,modulename,starthour,endhour,shift_type,worksite_code,daypay,licode,federal_payroll,nonfederal_payroll,contractid,classification,ts_layout_pref)
					SELECT '', '".$appuser."', client, project, manager, s_date, e_date, rate, tsapp, '".$jtype_hrcon."', pusername,
					rateper, otrate, '".$ustatus_hrcon."', NOW(),imethod, iterms, pterms, sagent, commision, co_type,
					endclient, tweeks, tdays, notes, rtime,'', posid, jotype, catid, postitle, refcode,
					posstatus, vendor, contact, candidate, exp_edate, hired_date, posworkhr, timesheet, bamount,
					bcurrency, bperiod, pamount, pcurrency, pperiod, emp_prate, reason, rateperiod,
					ot_period, ot_currency, placement_fee, placement_curr, bill_contact, bill_address,
					wcomp_code, bill_req, service_terms, hire_req, addinfo, avg_interview, calctype, burden,
					markup, margin, prateopen, brateopen, prateopen_amt, brateopen_amt, owner, cdate, otbrate_amt,
					otbrate_period, otbrate_curr, otprate_amt, otprate_period, otprate_curr, payrollpid,
					offlocation, double_prate_amt, double_prate_period, double_prate_curr, double_brate_amt,
					double_brate_period, double_brate_curr, po_num, department, job_loc_tax, '".$username."',
					NOW(), date_placed, assign_no, deptid, attention, corp_code, industryid,schedule_display,bill_burden,shiftid,'".$modulename_hrcon."',starthour,endhour,shift_type,worksite_code,daypay,licode,federal_payroll,nonfederal_payroll,contractid,classification,ts_layout_pref
					FROM consultant_jobs WHERE sno = '".$rowConsult_Query['sno']."'";
				mysql_query($insHrcon_JobsQuery,$db);

				/*Getting Last Inserted ID from the hrcon_jobs table*/
				$hrconJobs_ID = mysql_insert_id($db);
				
				if($hrconJobs_ID != 0)
				{
					saveBurdenDetails($burdenTypeDetails,$burdenItemDetails,'hrcon_burden_details','hrcon_jobs_sno','insert',$hrconJobs_ID);
					saveBillBurdenDetails($bill_burdenTypeDetails,$bill_burdenItemDetails,'hrcon_burden_details','hrcon_jobs_sno','insert',$hrconJobs_ID);
					if(SHIFT_SCHEDULING_ENABLED == 'Y') 
					{
						
						if($shift_type=='regular')
						{
							//inserting the time slots data for schedule management for placement job
							insPlacementTimeSlots($db,'hrconjob_sm_timeslots',$hrconJobs_ID,$timeSlotsData,$id_req);
						}
						else if($shift_type=='perdiem')
						{
							//inserting the time slots data for perdiem schedule management for placement job
							insPerdiemPlacementTimeSlots($db,'hrconjob_perdiem_shift_sch',$hrconJobs_ID,$id_req,$userid,$rowConsult_Query['pusername']);
						}
					}
				}
				
				/*Getting details from hrcon_jobs table and setting Default Entity Taxes for Employee */
				$selhrcon_Query = "SELECT sno, client, username, pusername, worksite_code FROM hrcon_jobs WHERE sno = ".$hrconJobs_ID;
				$reshrcon_Query = mysql_query($selhrcon_Query,$db);
				$rowhrcon_Query = mysql_fetch_assoc($reshrcon_Query);
				//Update Worksite_id to hrcon_jobs from contact_manage--Mohan
				$cm_qrys = "select serial_no from contact_manage where loccode='".$rowhrcon_Query['worksite_code']."'";
				$rescm_qrys = mysql_query($cm_qrys,$db);
				$worksite_ids=mysql_fetch_row($rescm_qrys);
				$updateWorksiteids = "UPDATE hrcon_jobs SET worksite_id = '".$worksite_ids[0]."' WHERE sno='".$hrconJobs_ID."'";
				mysql_query($updateWorksiteids,$db); 

				/*Calling setDefaultEntityTaxes function to set Default Entity Taxes for Employee */
				setDefaultEntityTaxes('Assignment', $rowhrcon_Query['client'], 0, 0, $rowhrcon_Query['username'], $rowhrcon_Query['pusername']);

				/*Dumping data from assign_commission of consultant table to assign_commission of hrcon table for Employee*/
				$insHrconAssign_CommissionQuery = "INSERT INTO assign_commission(sno, username, assignid, assigntype,
					person, type, co_type, comm_calc, amount, roleid, overwrite, enableUserInput,shift_id)
					SELECT '', '".$username."', '".$hrconJobs_ID."', 'H', person, type, co_type, comm_calc, amount,
					roleid, overwrite, enableUserInput, shift_id 
					FROM assign_commission WHERE assignid = '".$commission_id."' AND assigntype = 'C'";
				mysql_query($insHrconAssign_CommissionQuery, $db);
				

				include_once('custom/custome_save.php');		
				$directEmpUdfObj = new userDefinedManuplations();
				$directEmpUdfObj->insertUserDefinedData($hrconJobs_ID, 7);
			
								
				/*Getting consultant_jobs table s_date and e_date, to insert into assignment_schedule table. also converting the format */
				$assignment_StartDate = explode("-", $rowConsult_Query['s_date']);
				$assignment_EndDate = explode("-", $rowConsult_Query['e_date']);

				$assign_SDate = $assignment_StartDate[1]."/".$assignment_StartDate[0]."/".$assignment_StartDate[2];
				$assign_EDate = $assignment_EndDate[1]."/".$assignment_EndDate[0]."/".$assignment_EndDate[2];

				/*Preparing the array to create assignment schedule records for Assignments tab using hrcon_jobs.sno and empcon_jobs.sno values */
				$assignments_Array = array("modulename" => "HR->Assignments", "pusername" => $rowConsult_Query['pusername'], "userid" => $appuser, "contactsno" => $hrconJobs_ID."|", "invapproved" => 'active', "startdate" => $assign_SDate, "enddate" => $assign_EDate, "title" => "Project");

				/*Calling insertAssignmentSchedule function to dump data from the above array into assignment_schedule table */
				$jobsAssignSchedule_ID = insertAssignmentSchedule($assignments_Array);
				
				if(SHIFT_SCHEDULING_ENABLED == 'N') 
				{
					/*Dumping data from consultant_tab table to hrcon_tab table for Assignments */
					$insAssignHrcon_TabQuery = "INSERT INTO hrcon_tab(sno, tabsno, starthour, endhour, wdays, sch_date, coltype)
					SELECT '', '".$jobsAssignSchedule_ID."', starthour, endhour, wdays, sch_date, coltype
					FROM consultant_tab WHERE consno = '".$appno."' AND coltype = 'assign'";
					mysql_query($insAssignHrcon_TabQuery,$db);

					/*Dumping data from consultant_tab table to empcon_tab table for Assignments */
					$insAssignEmpcon_TabQuery = "INSERT INTO empcon_tab(sno, tabsno, starthour, endhour, wdays, sch_date, coltype)
					SELECT '', '".$jobsAssignSchedule_ID."', starthour, endhour, wdays, sch_date, coltype
					FROM consultant_tab WHERE consno = '".$appno."' AND coltype = 'assign'";
					mysql_query($insAssignEmpcon_TabQuery,$db);
				}
                                // referral updates code start here//
                                if(($candref_query_count==1)){
                                    if(($rowConsult_Query['posid']==$candref_row[1]) && ($candref_row[2]==null || $candref_row[2]=='') && $candref_row[3]=='0')
                                    {   
                                        if((strtolower($candref_row[6])=='0-days' || strtolower($candref_row[6])=='0-hours') &&  ($candref_row[7]!=0 || $candref_row[7]!='0.00')){
                                            
                                            $candreferupsql="UPDATE cand_refer set emp_id='".$employee_id."', assign_id ='".$rowConsult_Query['pusername']."' ,referral_status='".$ref_status_PP."', hire_date=NOW(),qualified_date=NOW(),status_upd_date=NOW() WHERE sno='".$candref_row[5]."'";
                                            mysql_query($candreferupsql,$db);
                                            
                                        }else{
                                        $candreferupsql="UPDATE cand_refer set emp_id='".$employee_id."', assign_id ='".$rowConsult_Query['pusername']."' ,referral_status='".$ref_status_PQ."', hire_date=NOW(),status_upd_date=NOW() WHERE sno='".$candref_row[5]."'";
                                        mysql_query($candreferupsql,$db); 
                                        }
                                        
                                    }
                                }
                                // code ends here related to referral updates //

			}
                        
                        // Udpate the remaining data of same applicant record with dsisqulaified referrals as applicant become employee
                        $ref_status_DR=getManageSno('Disqualified Referrals','referral_status');
                        
                        $referral_que = "SELECT ref_id,req_id,assign_id,emp_id,username FROM cand_refer WHERE ref_id='".$appuser."' AND referral_status='0'";
                        $referral_que_res=mysql_query($referral_que,$db);
                        $referral_que_count=mysql_num_rows($referral_que_res);
                        if($referral_que_count>0){
                                    
                            $candrefersql="UPDATE cand_refer set emp_id='0', assign_id =NULL, referral_status='".$ref_status_DR."', hire_date=NULL, status_upd_date=NOW(), status_notes='Candidate is not placed on referred job order' WHERE ref_id='".$appuser."' AND referral_status='0'";
                            mysql_query($candrefersql,$db);
                        }
                        
                        
		}
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
			//mysql_query($insPastDatesJobSql,$db);
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
	
	//function to update the candidate records time slots with the shift status busy and insert into consultant table
	function updCandTimeSlots_insFrmCandToConsultant($db,$candusername,$pusername,$timeSlotsData,$calltype,$id_req)
	{
		global $username, $objSchSchedules;
		
		if(!empty($calltype) && $calltype == 'update') 
		{

			$del_query	= "DELETE FROM consultant_sm_timeslots WHERE username = '".$pusername."'";
			mysql_query($del_query, $db);
		}

		//UPDATING THE CANDIDATE SHIFTS BASED ON THE PLACEMENT SHIFTS
		$placement_tf_array = explode("|",$timeSlotsData);
		$placement_tf_cnt	= count($placement_tf_array);	
		$placementSlotsArray = array();
		$busySlotsArray = array();
		if($placement_tf_cnt > 0)	
		{
			//getting the candidate time slots details 
			$previousDate = date("Y-m-d",strtotime("-2 months",strtotime(date("Y-m-d"))));
			$get_cand_sm_tf_sql = "SELECT DATE_FORMAT(shift_date,'%m/%d/%Y'),shift_starttime ,shift_endtime,shift_status,sno,event_group_no,placed_posids FROM   candidate_sm_timeslots WHERE username = '".$candusername."' AND shift_date >= '".$previousDate."' ORDER BY shift_date ASC";
			$get_cand_sm_tf_res = mysql_query($get_cand_sm_tf_sql,$db);
			$cand_sm_tf_rowcnt = mysql_num_rows($get_cand_sm_tf_res);
			$candidatesSlotsArray = array();	
			if($cand_sm_tf_rowcnt > 0) 
			{
				while($smtfrow = mysql_fetch_row($get_cand_sm_tf_res))
				{
					$seldateval = $smtfrow[0];			

					//convert into minutes
					$fromTF 	= $objSchSchedules->getMinutesFrmDateTime($smtfrow[1]);
					$toTF 		= $objSchSchedules->getMinutesFrmDateTime($smtfrow[2]);
					$shiftStatus	= $smtfrow[3];
					$smsno		= $smtfrow[4];
					$slotGrpNo	= $smtfrow[5];
					$placed_posids	= $smtfrow[6];

					$candidatesSlotsArray[$seldateval][$smsno] = $fromTF."^".$toTF."^".$slotGrpNo."^".$shiftStatus."^".$placed_posids;
				}			
			}
			
			
			//iterating through the placement time slots
			for($i=0;$i<$placement_tf_cnt;$i++)
			{
				$smVal = trim($placement_tf_array[$i]);
				if($smVal != "")
				{
					//splitting the date, from time and to time
					$smValExp = explode("^",$smVal);
					$seldateval = $smValExp[0];
					$fromTF = $smValExp[1];
					$toTF = $smValExp[2];				
					$shiftStatus = $smValExp[5];
					//if placement date exists in candidates slot then check for shifts
					if(array_key_exists($seldateval,$candidatesSlotsArray))
					{
						//iterating  through the candidate shifts of particular date
						foreach($candidatesSlotsArray[$seldateval] as $candsmsno=>$v)
						{
							$candExp = explode("^",$v);
							$candFR = $candExp[0];
							$candTO = $candExp[1];
							$slotGrpNo = $candExp[2];
							$candSt = $candExp[3];
							$placed_posids = $candExp[4];
							
							// if any of the shift of candidates falls in between the placement shift (partial match) then update that candidate shift.
							/*
							Ex : Placement Shift : 7AM to 3PM
								 Candidate Shits : 1=>4AM to 10AM, 2=> 2PM to 5PM. These both shifts will make as busy
												   Left shift check : (7AM >= 4AM and 7AM < 10PM )
												   Right shift check : (3PM > 4AM and 3PM <= 10PM)
												   Any of these condition satisfying then we make particular shift as busy. Check placement shift in candidate.
							*/
							if( (($fromTF >= $candFR && $fromTF < $candTO) || ($toTF > $candFR && $toTF <= $candTO)) || (($candFR >= $fromTF && $candFR < $toTF) || ($candTO > $fromTF && $candTO <= $toTF)))
							{
								$smEventType = 'single';
								$smEventNo = 0;
								$smEventGrpNo = 0;
								$smAvailDate = $seldateval;
								$fl = 0;
								if($candFR >= $fromTF && $candTO <= $toTF) // 1
								{
									$up_cand_tf_sql = "UPDATE candidate_sm_timeslots SET shift_status='busy',mtime=NOW(),muser='".$username."',placed_posids=IF(placed_posids='','".$id_req."',concat_ws(',',placed_posids,'".$id_req."')) WHERE sno = '".$candsmsno."'";
									$up_cand_tf_res = mysql_query($up_cand_tf_sql,$db);
								} // 1
								else if($fromTF >= $candFR && $toTF <= $candTO) //2 - split
								{	
									$fl = 1;
									if($fromTF != $candFR)
									{
										//1split
										$smAvailFromDate	= $objSchSchedules->getDateTimeFrmMinutes($smAvailDate, $candFR);
										$smAvailToDate		= $objSchSchedules->getDateTimeFrmMinutes($smAvailDate, $fromTF);

										if($placed_posids != "")
										{
											$smShiftStatus = 'busy';
											$newid_req = $placed_posids;
										}
										else
										{
											$smShiftStatus = 'available';
											$newid_req = $id_req;
											$smEventGrpNo = $slotGrpNo;
										}										
										$tfData = $seldateval."^".$smAvailFromDate."^".$smAvailToDate."^".$smEventType."^".$smEventNo."^".$smEventGrpNo."^".$smShiftStatus;
										
										$newcandsmsno = insCandTimeSlots($db,$candusername,$tfData,$newid_req);
										$candidatesSlotsArray[$seldateval][$newcandsmsno] = $candFR."^".$fromTF."^".$smEventGrpNo."^".$smShiftStatus."^".$placed_posids;
									}
									$smEventGrpNo = 0;

									//2split - busy
									$smAvailFromDate	= $objSchSchedules->getDateTimeFrmMinutes($smAvailDate, $fromTF);
									$smAvailToDate		= $objSchSchedules->getDateTimeFrmMinutes($smAvailDate, $toTF);

									//$smShiftStatus = 'busy';
									if($placed_posids != "")
									{
										$smShiftStatus = 'busy';
										$newid_req = $placed_posids.",".$id_req;
									}
									else
									{
										$smShiftStatus = 'busy';
										$newid_req = $id_req;
									}			
									
									$tfData = $seldateval."^".$smAvailFromDate."^".$smAvailToDate."^".$smEventType."^".$smEventNo."^".$smEventGrpNo."^".$smShiftStatus;
									
									$newcandsmsno = insCandTimeSlots($db,$candusername,$tfData,$newid_req);
									$candidatesSlotsArray[$seldateval][$newcandsmsno] = $fromTF."^".$toTF."^".$smEventGrpNo."^".$smShiftStatus."^".$placed_posids;
										
									
									if($toTF != $candTO)
									{
										//3split
										$smAvailFromDate	= $objSchSchedules->getDateTimeFrmMinutes($smAvailDate, $toTF);
										$smAvailToDate		= $objSchSchedules->getDateTimeFrmMinutes($smAvailDate, $candTO);

										//$smShiftStatus = 'available';
										if($placed_posids != "")
										{
											$smShiftStatus = 'busy';
											$newid_req = $placed_posids;
										}
										else
										{
											$smShiftStatus = 'available';
											$newid_req = $id_req;
										}	
										
										$tfData = $seldateval."^".$smAvailFromDate."^".$smAvailToDate."^".$smEventType."^".$smEventNo."^".$smEventGrpNo."^".$smShiftStatus;
										
										$newcandsmsno = insCandTimeSlots($db,$candusername,$tfData,$newid_req);
										$candidatesSlotsArray[$seldateval][$newcandsmsno] = $toTF."^".$candTO."^".$smEventGrpNo."^".$smShiftStatus."^".$placed_posids;										
									}
									unset($candidatesSlotsArray[$seldateval][$candsmsno]);
								} //2
								else if($candFR <= $fromTF && $candTO > $fromTF && $candTO <= $toTF) //3 - split
								{
									$fl = 1;
									if($fromTF != $candFR)
									{
										//1split
										$smAvailFromDate	= $objSchSchedules->getDateTimeFrmMinutes($smAvailDate, $candFR);
										$smAvailToDate		= $objSchSchedules->getDateTimeFrmMinutes($smAvailDate, $fromTF);

										//$smShiftStatus = 'available';
										if($placed_posids != "")
										{
											$smShiftStatus = 'busy';
											$newid_req = $placed_posids;
										}
										else
										{
											$smShiftStatus = 'available';
											$newid_req = $id_req;
											$smEventGrpNo = $slotGrpNo;
										}	
										
										$tfData = $seldateval."^".$smAvailFromDate."^".$smAvailToDate."^".$smEventType."^".$smEventNo."^".$smEventGrpNo."^".$smShiftStatus;
										
										$newcandsmsno = insCandTimeSlots($db,$candusername,$tfData,$newid_req);
										$candidatesSlotsArray[$seldateval][$newcandsmsno] = $candFR."^".$fromTF."^".$smEventGrpNo."^".$smShiftStatus."^".$placed_posids;
										
									}
									$smEventGrpNo = 0;
									
									//2split - busy
									$smAvailFromDate	= $objSchSchedules->getDateTimeFrmMinutes($smAvailDate, $fromTF);
									$smAvailToDate		= $objSchSchedules->getDateTimeFrmMinutes($smAvailDate, $candTO);

									//$smShiftStatus = 'busy';
									if($placed_posids != "")
									{
										$smShiftStatus = 'busy';
										$newid_req = $placed_posids.",".$id_req;
									}
									else
									{
										$smShiftStatus = 'busy';
										$newid_req = $id_req;
									}
									
									$tfData = $seldateval."^".$smAvailFromDate."^".$smAvailToDate."^".$smEventType."^".$smEventNo."^".$smEventGrpNo."^".$smShiftStatus;
									
									$newcandsmsno = insCandTimeSlots($db,$candusername,$tfData,$newid_req);
									$candidatesSlotsArray[$seldateval][$newcandsmsno] = $fromTF."^".$candTO."^".$smEventGrpNo."^".$smShiftStatus."^".$placed_posids;
									unset($candidatesSlotsArray[$seldateval][$candsmsno]);
								
								} //3
								else if($candFR >= $fromTF && $candTO >= $toTF && $candFR < $toTF) //4 - split
								{
									$fl = 1;

									//1split - busy
									$smAvailFromDate	= $objSchSchedules->getDateTimeFrmMinutes($smAvailDate, $candFR);
									$smAvailToDate		= $objSchSchedules->getDateTimeFrmMinutes($smAvailDate, $toTF);

									//$smShiftStatus = 'busy';
									if($placed_posids != "")
									{
										$smShiftStatus = 'busy';
										$newid_req = $placed_posids.",".$id_req;
									}
									else
									{
										$smShiftStatus = 'busy';
										$newid_req = $id_req;
									}
									
									$tfData = $seldateval."^".$smAvailFromDate."^".$smAvailToDate."^".$smEventType."^".$smEventNo."^".$smEventGrpNo."^".$smShiftStatus;
									
									$newcandsmsno = insCandTimeSlots($db,$candusername,$tfData,$newid_req);
									$candidatesSlotsArray[$seldateval][$newcandsmsno] = $candFR."^".$toTF."^".$smEventGrpNo."^".$smShiftStatus."^".$placed_posids;

									//2split
									$smAvailFromDate	= $objSchSchedules->getDateTimeFrmMinutes($smAvailDate, $toTF);
									$smAvailToDate		= $objSchSchedules->getDateTimeFrmMinutes($smAvailDate, $candTO);

									//$smShiftStatus = 'available';
									$smEventGrpNo = $slotGrpNo;
									
									if($placed_posids != "")
									{
										$smShiftStatus = 'busy';
										$newid_req = $placed_posids;
									}
									else
									{
										$smShiftStatus = 'available';
										$newid_req = $id_req;
									}
									
									$tfData = $seldateval."^".$smAvailFromDate."^".$smAvailToDate."^".$smEventType."^".$smEventNo."^".$smEventGrpNo."^".$smShiftStatus;
									
									$newcandsmsno = insCandTimeSlots($db,$candusername,$tfData,$newid_req);
									$candidatesSlotsArray[$seldateval][$newcandsmsno] = $toTF."^".$candTO."^".$smEventGrpNo."^".$smShiftStatus."^".$placed_posids;
									unset($candidatesSlotsArray[$seldateval][$candsmsno]);
								
								} //4
								
								if($fl == 1)
								{
									$del_cand_tf_sql = "DELETE FROM candidate_sm_timeslots WHERE sno = '".$candsmsno."'";
									$del_cand_tf_res = mysql_query($del_cand_tf_sql,$db);
								}
								
							}
						}
					}				
				}
			}
		}
		//END OF CANDIDATE UPDATING SHIFTS
		
		//insert the data from candidate to consultant table
		$ins_cand_consultant_sql = "INSERT INTO consultant_sm_timeslots 
									SELECT  '',
											'".$pusername."',
											shift_date,
											shift_starttime,
											shift_endtime,								
											event_type,
											event_no,
											event_group_no,
											shift_status,
											placed_posids,
											'".$username."',
											NOW(),
											'".$username."',
											NOW()
										FROM candidate_sm_timeslots 
										WHERE username = '".$candusername."'
											";
		$ins_cand_consultant_res = mysql_query($ins_cand_consultant_sql,$db);

	}
	
	//function to insert the time slots from consultant to hrcon tables
	function insTimeSlotsFrmConsultantTohrcon($db,$pusername)
	{
		global $username;
		
		//insert the data from candidate to consultant table
		$ins_cand_consultant_sql = "INSERT INTO hrcon_sm_timeslots 
									SELECT  '',
											'".$pusername."',
											shift_date,
											shift_starttime,
											shift_endtime,								
											event_type,
											event_no,
											event_group_no,
											shift_status,
											'',
											'".$username."',
											NOW(),
											'".$username."',
											NOW()
										FROM consultant_sm_timeslots 
										WHERE username = '".$pusername."'
											";
		$ins_cand_consultant_res = mysql_query($ins_cand_consultant_sql,$db);
		
	}
	
	function insCandTimeSlots($db,$candusername,$tfData,$id_req)
	{
		global $username;
		
		$timeSlotDetails = explode("^",$tfData);
		$smAvailDate = date("Y-m-d",strtotime($timeSlotDetails[0]));
		$smAvailFromDate = $timeSlotDetails[1];
		$smAvailToDate = $timeSlotDetails[2];
		$smEventType = $timeSlotDetails[3];
		$smEventNo = $timeSlotDetails[4];
		$smEventGrpNo = $timeSlotDetails[5];
		$smShiftStatus = $timeSlotDetails[6];
		if($smShiftStatus != 'busy')
		{
			$id_req = '';
		}
		$sm_cand_tf_sql	= "INSERT INTO candidate_sm_timeslots
										(
										sno,									
										username,
										shift_date,
										shift_starttime,
										shift_endtime,								
										event_type,
										event_no,
										event_group_no,
										shift_status,
										placed_posids,
										cuser,
										ctime,
										muser,
										mtime
										)
										VALUES 
										(
										'',									
										'".$candusername."',
										'".$smAvailDate."',
										'".$smAvailFromDate."',
										'".$smAvailToDate."',								
										'".$smEventType."',
										'".$smEventNo."',
										'".$smEventGrpNo."',
										'".$smShiftStatus."',
										'".$id_req."',
										'".$username."',
										NOW(),
										'".$username."',
										NOW()							
										)";

		$sm_cand_tf_res	= mysql_query($sm_cand_tf_sql, $db);
		$candsmson=mysql_insert_id($db);
		
		return $candsmson;
		
	}
	
	//function to update the assigment no in consultant time slots table
	function updConsTimeSlotsAssNo($db,$conusername)
	{
		//GETTING ASSIGNMENT NO.S FROM THE CONSULTANT JOBS TO REPLACED THE PLACED POSIDS WITH ASS NO.S
		$get_cons_assgno_sql = "SELECT posid,pusername FROM consultant_jobs WHERE username = '".$conusername."'";
		$get_cons_assgno_res = mysql_query($get_cons_assgno_sql,$db);
		$toreplace_array = array();
		while($assgnorow = mysql_fetch_row($get_cons_assgno_res))
		{
			if($assgnorow[0] != '0' && $assgnorow[0] != "")
			{
				$toreplace_array[$assgnorow[0]] = $assgnorow[1];
			}
		}
		
		foreach($toreplace_array as $posidkey=>$assgnoval)
		{
			$upd_posid_assgno_sql = "UPDATE consultant_sm_timeslots SET placed_assgno = REPLACE(placed_assgno,'".$posidkey."','".$assgnoval."') WHERE username = '".$conusername."' AND FIND_IN_SET('".$posidkey."',placed_assgno) ";
			$upd_posid_assgno_res = mysql_query($upd_posid_assgno_sql,$db);
		}
	}

	//function to insert perdiem time schedule management data from placement
	function insPerdiemPlacementTimeSlots($db,$table, $psno,$id_req,$cand_id,$pusername)
	{
		global $username, $objSchSchedules, $objPerdiemScheduleDetails, $shift_snos,$candrn,$nextPlacePositon,$perdiemPlacementFrm;

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
						$nextPositonNo = $nextPlacePositon;
						if($perdiemPlacementFrm == "perdiemParticalPlace"){
							$nextPositonNo = $nextPlacePositon;
						}else{
							$nextPositonNo = findPerdiemOpenPosNo($perdiemSno);
						}

						$recNo = 0;
						$slotGrpNo = 0;
						$shiftNumPos = 0;
						$shiftStatus = 'busy';

						$shiftSetupDetails = $objSchSchedules->getShiftNameColorBySno($shiftSno);

						list($shiftName,$shiftColor) = explode("|",$shiftSetupDetails);

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
						
								if($table=='placement_perdiem_shift_sch')
								{
									$place_shifts_que = "INSERT INTO placement_perdiem_shift_sch (`sno`, `placementjob_sno`,`candid`,`pusername`,`no_of_shift_position`, `shift_startdate`,`shift_enddate`,`shift_starttime`,`shift_endtime`,`split_shift`,`shift_id`,`cdate`,`cuser`,`mdate`,`muser`) 
										VALUES('','".$psno."','".$cand_id."','".$pusername."','".$nextPositonNo."','".$shiftStDate."','".$shiftEdDate."','".$shiftStTime."','".$shiftEdTime."','".$splitShift."','".$shiftSno."',NOW(),'".$username."',NOW(),'".$username."')";
									$result = mysql_query($place_shifts_que,$db);

									$insert_place_slots_que = "INSERT INTO jo_perdiem_shift_sch_detail (sno,jo_sch_sno,posid,shift_position_no,candid,pusername,filled_startdate,filled_enddate,filled_start_time,filled_end_time,split_shift,shift_id,cdate,cuser,mdate,muser) VALUES ('','".$perdiemSno."','".$id_req."','".$nextPositonNo."','".$cand_id."','".$pusername."','".$shiftStDate."','".$shiftEdDate."','".$shiftStTime."','".$shiftEdTime."','".$splitShift."','".$shiftSno."',NOW(),'".$username."',NOW(),'".$username."')";
									mysql_query($insert_place_slots_que, $db);
								}
								else if($table=='consultantjob_perdiem_shift_sch')
								{
									$consultant_job_shifts_que = "INSERT INTO consultantjob_perdiem_shift_sch (`sno`, `consultantjob_sno`,`pusername`,`no_of_shift_position`, `shift_startdate`,`shift_enddate`,`shift_starttime`,`shift_endtime`,`split_shift`,`shift_id`,`cdate`,`cuser`,`mdate`,`muser`) 
									VALUES('','".$psno."','".$pusername."','".$nextPositonNo."','".$shiftStDate."','".$shiftEdDate."','".$shiftStTime."','".$shiftEdTime."','".$splitShift."','".$shiftSno."',NOW(),'".$username."',NOW(),'".$username."')";
									$result = mysql_query($consultant_job_shifts_que,$db);
								}
								else if($table == "hrconjob_perdiem_shift_sch")
								{
									$hrcon_job_shifts_que = "INSERT INTO hrconjob_perdiem_shift_sch (`sno`, `hrconjob_sno`,`pusername`,`no_of_shift_position`, `shift_startdate`,`shift_enddate`,`shift_starttime`,`shift_endtime`,`split_shift`,`shift_id`,`cdate`,`cuser`,`mdate`,`muser`) 
										VALUES('','".$psno."','".$pusername."','".$nextPositonNo."','".$shiftStDate."','".$shiftEdDate."','".$shiftStTime."','".$shiftEdTime."','".$splitShift."','".$shiftSno."',NOW(),'".$username."',NOW(),'".$username."')";
									$result = mysql_query($hrcon_job_shifts_que,$db);
								}
							}
						}
						else
						{
							if($table=='placement_perdiem_shift_sch')
							{
								$place_shifts_que = "INSERT INTO placement_perdiem_shift_sch (`sno`, `placementjob_sno`,`candid`,`pusername`,`no_of_shift_position`, `shift_startdate`,`shift_enddate`,`shift_starttime`,`shift_endtime`,`split_shift`,`shift_id`,`cdate`,`cuser`,`mdate`,`muser`) 
									VALUES('','".$psno."','".$cand_id."','".$pusername."','".$nextPositonNo."','".$shiftStDate."','".$shiftEdDate."','".$shiftStTime."','".$shiftEdTime."','".$splitShift."','".$shiftSno."',NOW(),'".$username."',NOW(),'".$username."')";
								$result = mysql_query($place_shifts_que,$db);


								$insert_place_slots_que = "INSERT INTO jo_perdiem_shift_sch_detail (sno,jo_sch_sno,posid,shift_position_no,candid,pusername,filled_startdate,filled_enddate,filled_start_time,filled_end_time,split_shift,shift_id,cdate,cuser,mdate,muser) VALUES ('','".$perdiemSno."','".$id_req."','".$nextPositonNo."','".$cand_id."','".$pusername."','".$shiftStDate."','".$shiftEdDate."','".$shiftStTime."','".$shiftEdTime."','".$splitShift."','".$shiftSno."',NOW(),'".$username."',NOW(),'".$username."')";
								mysql_query($insert_place_slots_que, $db);
							}
							else if($table == "consultantjob_perdiem_shift_sch")
							{
								$consultant_job_shifts_que = "INSERT INTO consultantjob_perdiem_shift_sch (`sno`, `consultantjob_sno`,`pusername`,`no_of_shift_position`, `shift_startdate`,`shift_enddate`,`shift_starttime`,`shift_endtime`,`split_shift`,`shift_id`,`cdate`,`cuser`,`mdate`,`muser`) 
									VALUES('','".$psno."','".$pusername."','".$nextPositonNo."','".$shiftStDate."','".$shiftEdDate."','".$shiftStTime."','".$shiftEdTime."','".$splitShift."','".$shiftSno."',NOW(),'".$username."',NOW(),'".$username."')";
								$result = mysql_query($consultant_job_shifts_que,$db);
							}
							else if($table == "hrconjob_perdiem_shift_sch")
							{
								$hrcon_job_shifts_que = "INSERT INTO hrconjob_perdiem_shift_sch (`sno`, `hrconjob_sno`,`pusername`,`no_of_shift_position`, `shift_startdate`,`shift_enddate`,`shift_starttime`,`shift_endtime`,`split_shift`,`shift_id`,`cdate`,`cuser`,`mdate`,`muser`) 
									VALUES('','".$psno."','".$pusername."','".$nextPositonNo."','".$shiftStDate."','".$shiftEdDate."','".$shiftStTime."','".$shiftEdTime."','".$splitShift."','".$shiftSno."',NOW(),'".$username."',NOW(),'".$username."')";
								$result = mysql_query($hrcon_job_shifts_que,$db);
							}
						}	
					}
				}
			}
			if ($_REQUEST['placeOnOpenPos'] == "Y") {
				
				insPerdiemOpenPosPlacementTimeSlots($table,$psno,$id_req,$cand_id,$pusername,$maxDateStr,$shift_snos);

			}
			else if($perdiemPlacementFrm != "perdiemParticalPlace")
			{
				$selectSplit = "SELECT DISTINCT(split_shift) FROM jo_perdiem_shift_sch WHERE posid='".$id_req."' AND shift_id='".$shift_snos."' ";
				$resultSplit = mysql_query($selectSplit,$db);

				$rowSplit = mysql_fetch_row($resultSplit);

				$splitShiftCheck = $rowSplit[0];
				if ($splitShiftCheck == "Y") {
					$greaterthenEqualTo = "=";
				}else{
					$greaterthenEqualTo = "";
				}

				if($table=='placement_perdiem_shift_sch')
				{

					$insPastDatesJobSql = "INSERT INTO ".$table." (`sno`, `placementjob_sno`,`candid`,`pusername`,`no_of_shift_position`, `shift_startdate`,`shift_enddate`,`shift_starttime`,`shift_endtime`,`split_shift`,`shift_id`,`cdate`,`cuser`,`mdate`,`muser`) SELECT '', '".$psno."','".$cand_id."','".$pusername."', no_of_shift_position,`shift_startdate`,`shift_enddate`,`shift_starttime`,`shift_endtime`,`split_shift`,`shift_id`, now(), '".$username."', now(),'".$username."'
					FROM jo_perdiem_shift_sch WHERE posid = '".$id_req."' AND shift_startdate >".$greaterthenEqualTo." '".$maxDateStr."' AND shift_id IN (".$shift_snos.") AND no_of_shift_position>='".$nextPlacePositon."'";

					$insPastDatesJobSql1 = "INSERT INTO jo_perdiem_shift_sch_detail (sno,jo_sch_sno,posid,shift_position_no,candid,pusername,filled_startdate,filled_enddate,filled_start_time,filled_end_time,split_shift,shift_id,cdate,cuser,mdate,muser) SELECT '', sno,posid,'".$nextPlacePositon."','".$cand_id."','".$pusername."',`shift_startdate`,`shift_enddate`,`shift_starttime`,`shift_endtime`,`split_shift`,`shift_id`, now(), '".$username."', now(),'".$username."' FROM jo_perdiem_shift_sch WHERE posid = '".$id_req."' AND shift_startdate >".$greaterthenEqualTo." '".$maxDateStr."' AND shift_id IN (".$shift_snos.") AND no_of_shift_position>='".$nextPlacePositon."'";
					mysql_query($insPastDatesJobSql1,$db);

				}
				else if($table=='consultantjob_perdiem_shift_sch')
				{
					$insPastDatesJobSql = "INSERT INTO ".$table." (`sno`, `consultantjob_sno`,`pusername`,`no_of_shift_position`, `shift_startdate`,`shift_enddate`,`shift_starttime`,`shift_endtime`,`split_shift`,`shift_id`,`cdate`,`cuser`,`mdate`,`muser`) SELECT '', '".$psno."','".$pusername."', no_of_shift_position,`shift_startdate`,`shift_enddate`,`shift_starttime`,`shift_endtime`,`split_shift`,`shift_id`, now(), '".$username."', now(),'".$username."'
					FROM jo_perdiem_shift_sch WHERE posid = '".$id_req."' AND shift_startdate >".$greaterthenEqualTo." '".$maxDateStr."' AND shift_id IN (".$shift_snos.") AND no_of_shift_position>='".$nextPlacePositon."'";
				}
				else if($table=='hrconjob_perdiem_shift_sch')
				{
					$insPastDatesJobSql = "INSERT INTO ".$table." (`sno`, `hrconjob_sno`,`pusername`,`no_of_shift_position`, `shift_startdate`,`shift_enddate`,`shift_starttime`,`shift_endtime`,`split_shift`,`shift_id`,`cdate`,`cuser`,`mdate`,`muser`,`shift_note`) SELECT '', '".$psno."','".$pusername."', no_of_shift_position,`shift_startdate`,`shift_enddate`,`shift_starttime`,`shift_endtime`,`split_shift`,`shift_id`, now(), '".$username."', now(),'".$username."', ''
					FROM jo_perdiem_shift_sch WHERE posid = '".$id_req."' AND shift_startdate >".$greaterthenEqualTo." '".$maxDateStr."' AND shift_id IN (".$shift_snos.") AND no_of_shift_position>='".$nextPlacePositon."'";
				}				
				mysql_query($insPastDatesJobSql,$db);
			}

			updtExpSEdatesPerdiemShifts($db,$table,$pusername,$psno);
   	 	}

	}

	function updtExpSEdatesPerdiemShifts($db,$table,$pusername,$psno)
	{
		global $username;		

		$perdiemTable='';
		$mainTable='';
		$whereCond = " pusername='".$pusername."'";
		if($table == "hrconjob_perdiem_shift_sch"){
			$mainTable="hrcon_jobs";
			$whereCond = "sno='".$psno."'";
		}elseif ($table == "consultantjob_perdiem_shift_sch") {
			$mainTable="consultant_jobs";
			$whereCond = "sno='".$psno."'";
		}else if ($table == "placement_perdiem_shift_sch") {
			$mainTable="placement_jobs";
			$whereCond = "sno='".$psno."'";
		}
			$expSEdate = array();
		if ($mainTable !="") {
			
			$delete = "DELETE FROM ".$table." WHERE pusername='".$pusername."' AND (shift_startdate ='1969-12-31' OR shift_enddate ='1969-12-31' ) " ;
			mysql_query($delete,$db);

			$select = "SELECT MIN(shift_startdate) AS shiftExpSEdate FROM ".$table." WHERE pusername='".$pusername."' 
					UNION ALL 
					SELECT MAX(shift_enddate) AS shiftExpSEdate FROM ".$table." WHERE pusername='".$pusername."' ";
			$result = mysql_query($select,$db);

			if (mysql_num_rows($result)>0) {
				
				while ($row = mysql_fetch_array($result)) {
					array_push($expSEdate, $row['shiftExpSEdate']);
				}

				$start_date = $expSEdate[0];
				$exp_end_date = $expSEdate[1];
				
				$selectExpEndDate = "SELECT DATE_FORMAT(exp_edate,'%Y-%m-%d') AS expEdate,e_date,s_date FROM ".$mainTable." WHERE ".$whereCond;
    			$resultExpDate = mysql_query($selectExpEndDate,$db);
    			$rowExpDate = mysql_fetch_assoc($resultExpDate);

		        $sdateAry = explode("-",$rowExpDate['s_date']);
		        $sDate = date("Y-m-d",strtotime($sdateAry[2].'-'.$sdateAry[0].'-'.$sdateAry[1]));

		        $checkEdate = "N";
		        $insertEdate = "N";
		        $insertExpEdate = "N";
		        $eDate='';
		        if ($rowExpDate['e_date'] !="0-0-0" && $rowExpDate['e_date'] !="") {
		            $edateAry = explode("-",$rowExpDate['e_date']);
		            $day = $edateAry[1];
		            $month = $edateAry[0];
		            $year = $edateAry[2];
		            $checkEdate = "Y";
		            $eDate = date("Y-m-d",strtotime($year.'-'.$month.'-'.$day));
		        }
		        if(strtotime($sDate) < strtotime($start_date)){

		            $s_Date = date("m-d-Y",strtotime($start_date));
		            $hrupdateExpDate = "UPDATE ".$mainTable." SET s_date = '".$s_Date."' WHERE ".$whereCond;
		            mysql_query($hrupdateExpDate,$db);
		        }else if (strtotime($start_date) < strtotime($sDate)) {
		        	$s_Date = date("m-d-Y",strtotime($start_date));
		            $hrupdateExpDate = "UPDATE ".$mainTable." SET s_date = '".$s_Date."' WHERE ".$whereCond;
		            mysql_query($hrupdateExpDate,$db);
		        }

		        if($checkEdate =="Y" && (strtotime($exp_end_date) > strtotime($eDate))){
		            
		            $insertEdate = "Y";
		            $e_Date = date("m-d-Y",strtotime($exp_end_date));		            
		            $hrupdateExpDate = "UPDATE ".$mainTable." SET e_date = '".$e_Date."' WHERE ".$whereCond;
		            mysql_query($hrupdateExpDate,$db);
		        }

		        if ($rowExpDate['expEdate'] =="0000-00-00" && $insertEdate == "Y") {

		           $hrupdateExpDate = "UPDATE ".$mainTable." SET e_date = '0-0-0', exp_edate = '".$exp_end_date."' WHERE ".$whereCond;
		            mysql_query($hrupdateExpDate,$db);
		            $insertExpEdate = "Y";
		        }

		        if($insertExpEdate == "N" && (strtotime($exp_end_date) > strtotime($rowExpDate['expEdate']))) {
		        	
		        	$hrupdateExpDate = "UPDATE ".$mainTable." SET exp_edate = '".$exp_end_date."' WHERE ".$whereCond;
		        	mysql_query($hrupdateExpDate,$db);
		        }
		        else if ($insertExpEdate == "N" && (strtotime($exp_end_date) < strtotime($rowExpDate['expEdate'])))
		        {
		        	$hrupdateExpDate = "UPDATE ".$mainTable." SET exp_edate = '".$exp_end_date."' WHERE ".$whereCond;
		        	mysql_query($hrupdateExpDate,$db);
		        }
		    }
		}
	}

	function insPerdiemOpenPosPlacementTimeSlots($table,$plchrsno,$posid,$candid,$pusername,$maxDateStr,$shiftSnos)
	{
		global $username,$db,$perdiemPlacementFrm,$nextPlacePositon;

		$objPerdiemCRMScheduleDetails = new CRMPerdiemShift();
		$maxFillPosCount = $objPerdiemCRMScheduleDetails->getMaxPositionCount($posid,$shiftSnos);

		$selectSplit = "SELECT DISTINCT(split_shift) FROM jo_perdiem_shift_sch WHERE posid='".$posid."' AND shift_id='".$shiftSnos."' LIMIT 0,1";
		$resultSplit = mysql_query($selectSplit,$db);

		$rowSplit = mysql_fetch_row($resultSplit);

		$splitShiftCheck = $rowSplit[0];
		if ($splitShiftCheck == "Y") {
			$greaterthenEqualTo = "=";
		}else{
			$greaterthenEqualTo = "";
		}
		$select = "SELECT COUNT(1) AS opencount, q.sno,q.no_of_shift_position,q.shift_startdate FROM 
				(SELECT DISTINCT jpss.sno,jscd.shift_position_no,IF((jpss.no_of_shift_position=1)&&(jscd.shift_position_no IS NULL),2,jpss.no_of_shift_position ) AS no_of_shift_position,jpss.shift_startdate
				FROM jo_perdiem_shift_sch AS jpss 
				LEFT JOIN jo_perdiem_shift_sch_detail AS jscd ON (jpss.posid = jscd.posid AND jpss.sno = jscd.jo_sch_sno) 
				WHERE jpss.posid = '".$posid."' AND jpss.shift_startdate >".$greaterthenEqualTo." '".$maxDateStr."' AND jpss.shift_id='".$shiftSnos."'
				GROUP BY jpss.shift_startdate ,jscd.shift_position_no 
				ORDER BY jscd.filled_startdate) AS q 
				GROUP BY q.shift_startdate 
				HAVING opencount < q.no_of_shift_position ORDER BY q.shift_startdate ASC";
		$select1 ="SELECT q.sno,q.shift_startdate,q.no_of_shift_position,COUNT(1) AS opencount 
				FROM (SELECT  DISTINCT jpss.sno,jpss.shift_startdate,jpss.no_of_shift_position
				FROM jo_perdiem_shift_sch AS jpss
				LEFT JOIN jo_perdiem_shift_sch_detail AS jscd ON (jpss.posid = jscd.posid AND jpss.sno = jscd.jo_sch_sno)
				WHERE jpss.posid='".$posid."' 
				AND jpss.shift_id='".$shiftSnos."'
				AND jpss.shift_startdate >".$greaterthenEqualTo." '".$maxDateStr."'  
				GROUP BY jpss.shift_startdate,jscd.shift_position_no 
				ORDER BY jscd.filled_startdate) AS q
				GROUP BY q.sno 
				HAVING opencount < q.no_of_shift_position 
				ORDER BY q.shift_startdate ASC";
		$result = mysql_query($select,$db);
		if (mysql_num_rows($result)>0) {
			$psno = $plchrsno;
			$id_req = $posid;
			$cand_id = $candid;
			while ($row = mysql_fetch_array($result)) {
				
				$perdiemSno = $row['sno'];
				$nextPositonNo = '';
				$nextPositonNo = findPerdiemOpenPosNo($perdiemSno);

				if($perdiemPlacementFrm != "perdiemParticalPlace")
				{
					if($table=='placement_perdiem_shift_sch')
					{

						$insPastDatesJobSql = "INSERT INTO ".$table." (`sno`, `placementjob_sno`,`candid`,`pusername`,`no_of_shift_position`, `shift_startdate`,`shift_enddate`,`shift_starttime`,`shift_endtime`,`split_shift`,`shift_id`,`cdate`,`cuser`,`mdate`,`muser`) SELECT '', '".$psno."','".$cand_id."','".$pusername."', '".$nextPositonNo."',`shift_startdate`,`shift_enddate`,`shift_starttime`,`shift_endtime`,`split_shift`,`shift_id`, now(), '".$username."', now(),'".$username."'
						FROM jo_perdiem_shift_sch WHERE sno = '".$perdiemSno."' ";

						$insPastDatesJobSql1 = "INSERT INTO jo_perdiem_shift_sch_detail (sno,jo_sch_sno,posid,shift_position_no,candid,pusername,filled_startdate,filled_enddate,filled_start_time,filled_end_time,split_shift,shift_id,cdate,cuser,mdate,muser) SELECT '', sno,posid,'".$nextPositonNo."','".$cand_id."','".$pusername."',`shift_startdate`,`shift_enddate`,`shift_starttime`,`shift_endtime`,`split_shift`,`shift_id`, now(), '".$username."', now(),'".$username."' FROM jo_perdiem_shift_sch WHERE sno = '".$perdiemSno."'";
						mysql_query($insPastDatesJobSql1,$db);

					}
					else if($table=='consultantjob_perdiem_shift_sch')
					{
						$insPastDatesJobSql = "INSERT INTO ".$table." (`sno`, `consultantjob_sno`,`pusername`,`no_of_shift_position`, `shift_startdate`,`shift_enddate`,`shift_starttime`,`shift_endtime`,`split_shift`,`shift_id`,`cdate`,`cuser`,`mdate`,`muser`) SELECT '', '".$psno."','".$pusername."', no_of_shift_position,`shift_startdate`,`shift_enddate`,`shift_starttime`,`shift_endtime`,`split_shift`,`shift_id`, now(), '".$username."', now(),'".$username."'
						FROM jo_perdiem_shift_sch WHERE sno='".$perdiemSno."' ";
					}
					else if($table=='hrconjob_perdiem_shift_sch')
					{
						$insPastDatesJobSql = "INSERT INTO ".$table." (`sno`, `hrconjob_sno`,`pusername`,`no_of_shift_position`, `shift_startdate`,`shift_enddate`,`shift_starttime`,`shift_endtime`,`split_shift`,`shift_id`,`cdate`,`cuser`,`mdate`,`muser`,`shift_note`) SELECT '', '".$psno."','".$pusername."', no_of_shift_position,`shift_startdate`,`shift_enddate`,`shift_starttime`,`shift_endtime`,`split_shift`,`shift_id`, now(), '".$username."', now(),'".$username."', ''
						FROM jo_perdiem_shift_sch WHERE sno='".$perdiemSno."'";
					}		
					mysql_query($insPastDatesJobSql,$db);
				}
			}
		}			
	}

	function findPerdiemOpenPosNo_backup($perdiemSno='')
	{
		global $db,$username,$nextPlacePositon;

		$objPerdiemCRMScheduleDetails = new CRMPerdiemShift();

		$openPosNo = $nextPlacePositon;
		$perdiemShiftDetailInfoAry = array();
		$perdiemShiftDetailInfoAry = $objPerdiemCRMScheduleDetails->selJoPerdiemShiftDetailDateInfoByParSno($perdiemSno);

		$select = "SELECT sno,no_of_shift_position FROM jo_perdiem_shift_sch WHERE sno='".$perdiemSno."'";
		$result = mysql_query($select,$db);
		if (mysql_num_rows($result)>0) {
			$row = mysql_fetch_row($result);
			$noOfShiftpos = $row[1];

			for ($i=1; $i <=$noOfShiftpos; $i++) { 
				$myFilledPerdiemArry = array();
				
				if (count($perdiemShiftDetailInfoAry[$i]) >0) {
					$myFilledPerdiemArry = prepareFilledPosFullArry($perdiemShiftDetailInfoAry[$i]);
					
					if (count($myFilledPerdiemArry)>0){

					}else{
						$openPosNo = $i;
						break;
					}
				}else{
					$openPosNo = $i;
					break;
				}
			}
		}
		return $openPosNo;
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

	function prepareFilledPosFullArry($filledPosArry){
		$filledArray=array();
		$shiftRangeAry = array();
		$init15MintimeslotsArray = array("0","15","30","45","60","75","90","105","120","135","150","165","180","195","210","225","240","255","270","285","300","315","330","345","360","375","390","405","420","435","450","465","480","495","510","525","540","555","570","585","600","615","630","645","660","675","690","705","720","735","750","765","780","795","810","825","840","855","870","885","900","915","930","945","960","975","990","1005","1020","1035","1050","1065","1080","1095","1110","1125","1140","1155","1170","1185","1200","1215","1230","1245","1260","1275","1290","1305","1320","1335","1350","1365","1380","1395","1410","1425","1439");
		foreach ($filledPosArry as $Poskey =>$fillArryVal) {

			$SMintime = $fillArryVal['startTimeMins'];
			$EMintime = $fillArryVal['endTimeMins'];
			$candName = $fillArryVal['candName'];
			$candId   = $fillArryVal['candId'];

			if ($SMintime > $EMintime) {				
				$subEndtimeMin  = 1439;
				$firstShiftRangeArr = range(array_search($SMintime,$init15MintimeslotsArray), array_search($subEndtimeMin,$init15MintimeslotsArray));

				$subStarttimeMin = 0;

				$secondShiftRangeArr = range(array_search($subStarttimeMin,$init15MintimeslotsArray), array_search($EMintime,$init15MintimeslotsArray));

				$shiftRangeAry = array_merge($firstShiftRangeArr,$secondShiftRangeArr);
			}
			else
			{
				$shiftRangeAry = range(array_search($SMintime,$init15MintimeslotsArray), array_search($EMintime,$init15MintimeslotsArray));
			}

			foreach ($shiftRangeAry as $key => $value) {
					$filledArray[$value]=$candName.'^'.$candId;			
			}		
		}
		return $filledArray;
	}

	//function to insert perdiem time schedule management data from placement
	function generateTimeSlotsFromSession($cand_id,$posid)
	{
		global $db,$username, $objSchSchedules, $objPerdiemScheduleDetails, $shift_snos, $candrn,$perdiemPlacementFrm;
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
					
					foreach ($ShiftValue as $l => $ShiftValues) {
						
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
							if ($avail_shiftStDate !="12/31/1969") {
								$oldShiftStr = $avail_shiftStDate."^".$ShiftValue[$l]['startTime']."^".$ShiftValue[$l]['endTime']."^".$recNo."^".$slotGrpNo."^".$shiftStatus."^".$shiftSno."^".$noOfShift."^";
								array_push($oldShiftStrAry, $oldShiftStr);
							}						
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

	//unset the session with the sequence number if anything exists
	if(SHIFT_SCHEDULING_ENABLED == 'Y' && $shift_type=='perdiem')
	{
		unset($_SESSION['editPlacementPerdiemShiftSch'.$candrn]);
		unset($_SESSION['editPlacementPerdiemShiftPagination'.$candrn]);	
	}
?>

<body>
<script language=javascript>
document.onkeydown = function()
{
	if(window.event && window.event.keyCode == 116) 
		window.event.keyCode = 505;

	if(window.event && window.event.keyCode == 505) 
		return false; 
}

<?php

if($place_link=="place_cand")
	echo "var parwin=window.opener.location.href; window.opener.location.href=parwin;  window.location.href='showreqpagesub1.php?frm=canddetails&addr='+$id_req+'&navpage=joborder'+'&place_link=place_cand';";
else
	echo "var parwin=window.opener.location.href; window.opener.location.href=parwin; window.close();";
?>
</script>
</body>
</html> 