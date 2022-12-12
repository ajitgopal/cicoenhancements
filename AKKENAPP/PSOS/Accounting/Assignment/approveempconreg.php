<meta http-equiv="X-UA-Compatible" content="IE=Edge"/>
<?php
   	$page115=$page15; 
  	$daypay = 'No';
	if(isset($_REQUEST['daypay'])){
		$dpay= $_REQUEST['daypay'];
		$daypay = ($dpay=='on'|| $dpay=='checked')?'Yes':'No';
	}
	$federal_payroll = 'No';
	if(isset($_REQUEST['federal_payroll'])){
		$fedpay= $_REQUEST['federal_payroll'];
		$federal_payroll = ($fedpay=='on'|| $fedpay=='checked')?'Yes':'No';
	}
	$nonfederal_payroll = 'No';
	if(isset($_REQUEST['nonfederal_payroll'])){
		$nonfedpay= $_REQUEST['nonfederal_payroll'];
		$nonfederal_payroll = ($nonfedpay=='on'|| $nonfedpay=='checked')?'Yes':'No';
	}
	$contractid = '';
	if(isset($_REQUEST['projcode'])){
		$contractid = $_REQUEST['projcode'];
	}
	$classification = '';
	if(isset($_REQUEST['joclassification'])){
		$classification = $_REQUEST['joclassification'];
	}
	
    $worksiteCode = $worksitecode;
	$licode = $wali;
	// assign of updated username start here
	// When we create a "New User" from user Management already open assignment username name is not updating. For this reason we are fecthing the sno of the emp_list and then fetching the username.
	require("global.inc");
	$recno_data = Array();
	$recno_data = explode("|",$recno);
	$query = "Select username from emp_list where sno=".$recno_data[3];
	$result_query = mysql_query($query,$db);
	$result = mysql_fetch_row($result_query);
	$conusername = $result[0];
	$emp_sno_val = $recno_data[3];
	// assign of updated username end here
	$conusernameASS = $conusername;
	require_once('waitMsg.inc');
	require_once("dispfunc.php");

	require_once($akken_psos_include_path.'commonfuns.inc');
	require_once("multipleRatesClass.php");
	$ratesObj=new multiplerates();

	require_once('nusoap.php');
	//require_once("../../Include/class.NetPayroll.php");	

	require_once("syncHRfuncs.php");
	require_once("madisonfuns.php");
	require_once("cand_dataConv.php");

	$page152=explode("|",$page15);
	$jotype=getManage($page152[2]);
	
	require_once('shift_schedule/hrm_schedule_db.php');
	$objScheduleDetails	= new EmployeeSchedule();
	
	require_once('shift_schedule/crm_schedule_db.php');			
	$objCRMScheduleDetails	= new CandidateSchedule();
	
	/* including shift schedule class file for converting Minutes to DateTime / DateTime to Minutes */
	require_once('shift_schedule/class.schedules.php');
	$objSchSchedules	= new Schedules();

	/*
		Perdiem Shift Scheduling Class file
	*/
	require_once('perdiem_shift_sch/Model/class.hrm_perdiem_sch_db.php');
	$hrmPerdiemShiftSchObj = new HRMPerdiemShift();

	require_once("class.Notifications.inc");
	$placementNotification = new Notifications();
	//Get the shift scheduling old/new display status
	$schedule_display = $sm_enabled_option;

	require_once("class.Aca.php");

	// Class For ACA Information
	$acaDet = new Aca();
	
	
	$timeslotvalues = implode("|",$_SESSION['sm_form_data_array'.$candrn]);	

	$smDeletedTimeslot ="";
	if (count($_SESSION['smDeletedTimeslot'.$candrn]) >0) {
		$smDeletedTimeslot =implode("|",$_SESSION['smDeletedTimeslot'.$candrn]);	
	}
	//$timeslotvalues = $sm_form_data;
	// Getting the Shift Name Sno  for this table shift_setup >> sno
	$shift_id = '0';
	$sm_plcmnt_array=explode("|",$timeslotvalues);
	$sm_req_cnt	= count($sm_plcmnt_array);
	for($i=0;$i<$sm_req_cnt;$i++)
	{
		$smVal = trim($sm_plcmnt_array[$i]);
		if($smVal != "")
		{
			$smValExp = explode("^",$smVal);
			$shift_id = $smValExp[6];
		}
	}	

	$shift_snos = $sm_sel_shifts; //getting selected shift name snos for inserting previous date data

	// Assignment Shift Name/ Time when shift scheduling disabled
	$shift_st_time = '';
	$shift_et_time = '';
	if(SHIFT_SCHEDULING_ENABLED=='N')
	{
		// Shift details for Assignment
		$shift_det  	= $new_shift_name;
		$shift_info 	= explode("|", $shift_det);
		$shift_id 	= $shift_info[0];
		$shift_st_time 	= $shift_time_from;
		$shift_et_time 	= $shift_time_to;
	}
			
	//The below code is written -
	$selectShiftId = "SELECT sno,shiftid,pusername,ustatus FROM hrcon_jobs WHERE pusername='".$hdnAssid."'";

	$resultShift = mysql_query($selectShiftId,$db);

	$rowShift = mysql_fetch_assoc($resultShift);
	$oldShiftSno = $rowShift['shiftid'];
	$oldAssignStatus = $rowShift['ustatus'];
	if ($rowShift['shiftid'] == $shift_id ) {
		$Update_shift_id = $shift_id;
		
	}else{
		$Update_shift_id = $rowShift['shiftid'];
		
	}

	//Getting pusername from placement jobs to check shiftid
	$selectPlcShiftId = "SELECT sno,shiftid,pusername FROM placement_jobs WHERE pusername='".$hdnAssid."'";

	$resultPlcShift = mysql_query($selectPlcShiftId,$db);

	$rowPlcShift = mysql_fetch_assoc($resultPlcShift);
	$plctShiftSno = $rowPlcShift['shiftid'];


	//to handle the page load shift id problem
		if ($shift_type == "perdiem") {
			if ($active_perdiem_shiftid == "") {
				$shift_id = '';
				$shift_type ='';
			}else if ($active_perdiem_shiftid == $perdiem_shiftid) {
				$shift_id = $perdiem_shiftid;
			}else{
				$shift_id = $active_perdiem_shiftid;
			}			
		}

		if ($shift_id == "" && $shift_id == "0") {
			$shift_type = "";
		}

        if(isset($hdnbt_details)) {
            $burdenTypeDetails = $hdnbt_details;
        } else {
            $burdenTypeDetails = "";
        }
        if(isset($hdnbi_details)) {
            $burdenItemDetails = $hdnbi_details;
        } else {
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
        
	$jlist = explode('|',$recno);
	// This code modified for Mapping empcon_jobs to hrcon_jobs.
	$hr_sno=$jlist[0];


	$aque="select count(1) from assignment_schedule where contactsno like '".$hr_sno."|%' and modulename='HR->Assignments' AND invapproved='active'";
	$ares=mysql_query($aque,$db);
	$approw=mysql_fetch_row($ares);

	$eque="select count(1) from hrcon_jobs where sno='".$hr_sno."'";
	$eres=mysql_query($eque,$db);
	$erow=mysql_fetch_row($eres);
	if($erow[0]==0 || $approw[0]==0)
	{
		if($erow[0]==0)
			print "<script>alert('The Assignment has been processed by other User. Please check the Assignments for the recent changes on this Assignment.');";
		else
			print "<script>alert('We are unable to process your request. Please try again.');";
		print "var parwin = window.opener.location.href; window.opener.location.href = parwin; window.close(); </script>";
		exit;
	}

	$page15=$page115;
	$conusername = $conusernameASS;

	$jtype="";


	$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todate=date("m/d/Y",$thisday);

	if($page11[0] != "")
		$disp_name=$page11[0];
	if($page11[1] != "")
		$disp_name.=" ".$page11[1];
	if($page11[2] != "")
		$disp_name.=" ".$page11[2];

	if(isset($astatus) && $astatus != "")
		$org_astatus = $astatus;
	else
		$org_astatus = $cancelstatus;
	
	$sm_status = "";
	if($astatus == "closed" || $astatus == "cancel")
	{
		$sm_status = ($org_astatus=="closed") ? "closed" : "cancel";
	}
	else
	{
		//If savestatus variable is empty then user clicked on approve - rajesh
		if(empty($savestatus))
		{
			$sm_status = ($org_astatus=="") ? "approve" : $org_astatus;
		}
		else{
			$sm_status = ""; // for bypassing candidates shift schedule slot busy updates - rajesh
		}
	}
	

	// Checking if the employee data exists in madison_paydata table. If not we need to insert a record for the employee as the assignment or employee record created from data migration.
	$mpque="select count(1) from madison_paydata where paydata_emp_username='$conusername'";
	$mpres=mysql_query($mpque,$db);
	$mprow=mysql_fetch_row($mpres);

	if($mprow[0]==0)
		PrepareMadisonPayData("Hiring",$conusername,"","","");

	//Modified Time Updation in Employee table
	EmpModifiedUpdate('username',$conusername);	

	//for textbox values in comission
	$commissionVisitedArray	= array();
	$commval1		= "";
	$ratetype1		= "";
	$paytype1		= "";
	$roleName1 		= "";
	$roleOverWrite1		= "";	
	$roleEDisable1		= "";
	$commIndex		= 0;
	$commKey		= "";
	$arrayCount		= array();

	require_once("assignmentSession.php");

	$emp_val	= explode(',',$empvalues);
	for($k=0; $k<count($emp_val); $k++) {
		if($emp_val[$k] != 'noval') {
			$commvalues	= explode("^",$emp_val[$k]);
			$emp_values[$k]	= $commvalues[0];
		}
	}

	if(count($emp_values) > 0) {
		$counter = 0;
		foreach($emp_values as $key=>$keyValue)	{
			$commKey	= $keyValue;

			if(in_array($keyValue,$commissionVisitedArray))	{
				array_push($commissionVisitedArray,$keyValue);

				$arrayCount		= array_count_values($commissionVisitedArray);
				$dynemp.$keyValue	= $arrayCount[$keyValue]-1;
			}
			else {
				array_push($commissionVisitedArray,$keyValue);
				$dynemp.$keyValue	= 0;
			}

			$indexVal	= $dynemp.$keyValue;

			if($counter == 0) {
				$commval1	= $commval[$commKey][$indexVal];
				$ratetype1	= $ratetype[$commKey][$indexVal];
				$paytype1	= $paytype[$commKey][$indexVal];
				$roleName1	= $roleName[$commKey][$indexVal];
				$roleOverWrite1	= $roleOverWrite[$commKey][$indexVal];
				$roleEDisable1	= $roleEDisable[$commKey][$indexVal];
			}
			else {
				$commval1	.=",".$commval[$commKey][$indexVal];
				$ratetype1	.=",".$ratetype[$commKey][$indexVal];
				$paytype1	.=",".$paytype[$commKey][$indexVal];
				$roleName1	.=",".$roleName[$commKey][$indexVal];
				$roleOverWrite1	.=",".$roleOverWrite[$commKey][$indexVal];
				$roleEDisable1	.=",".$roleEDisable[$commKey][$indexVal];
			}

			$counter = $counter+1;
		}
	}

	$page1511	= explode("|",$page15);
	$ratesCountVal	= $page1511[87];
	$page1511[51]	= implode(",",$commissionVisitedArray);
	$page15_imp	= implode("|",$page1511);

	$page15		= $page15_imp;
	$commissionValues	= $commval1."|".$ratetype1."|".$paytype1."|".$assignStatus."|".$roleName1."|".$roleOverWrite1."|".$roleEDisable1;
	unset($_SESSION['commission_session'.$ACC_AS_SESSIONRN]);

	$_SESSION['commission_session'.$ACC_AS_SESSIONRN] = $commissionValues;

	$assign_mulrates = getAssignmentSession($rateType,$billableR,$mulpayRateTxt,$payratePeriod,$payrateCurrency,$mulbillRateTxt,$billratePeriod,$billrateCurrency,$taxableR,$mulRatesVal);
	$assignment_mulrates = $assign_mulrates; 

	$elements=explode("|",$page15);
	$job_title=$elements[4];
	$getPusername="";

	$WeekIntArray=array("Sunday"=>1,"Monday"=>2,"Tuesday"=>3,"Wednesday"=>4,"Thursday"=>5,"Friday"=>6,"Saturday"=>7);

	if($elements[14]=="parttime" || $elements[14]=="fulltime")
	{
		$schdet1 = $elements[14];

		foreach($defweekday as $key=>$val)
		{
			$Timefrom="fr_hour".$key;
			$TimeTo="to_hour".$key;
			$weekday=$val;
			$AddweakArray="add".$weekday."week";
			$AddweakInt=($key+1);
			$ChkVal="daycheck".$key;

			if($$ChkVal!="Y" && (trim($$Timefrom)!="" && trim($$TimeTo)!=""))
				$schdet1 .="|^AkkenSplit^||^AkkSplitCol^||^AkkSplitCol^|$AddweakInt|^AkkSplitCol^|".$$Timefrom."|^AkkSplitCol^|".$$TimeTo;

			if(is_array($$AddweakArray))
			{
				foreach($$AddweakArray as $Addkey=>$Addval)
				{
					$AddTimefrom="newSchdFrom".$Addkey;
					$AddTimeTo="newSchdTo".$Addkey;

					if($addchkSchedule[$Addkey]!="Y" && (trim($$AddTimefrom)!="" && trim($$AddTimeTo)!=""))
					{
						$Addweekday=$WeekIntArray[$Addval];
						$schdet1 .="|^AkkenSplit^||^AkkSplitCol^||^AkkSplitCol^|$Addweekday|^AkkSplitCol^|".$$AddTimefrom."|^AkkSplitCol^|".$$AddTimeTo;
					}
				}
			}
		}

		if(is_array($adddate))
		{
			foreach($adddate as $AddDatekey=>$AddDateval)
			{
				$AddDateTimefrom="newSchdFrom".$AddDatekey;
				$AddDateTimeTo="newSchdTo".$AddDatekey;

				if($addchkSchedule[$AddDatekey]!="Y"  && (trim($$AddDateTimefrom)!="" && trim($$AddDateTimeTo)!=""))
				{
					$InsdateArr=explode("/",$AddDateval);
					$Insdate=$InsdateArr[2]."-".$InsdateArr[0]."-".$InsdateArr[1]." 00:00:00";
					$AddDateweekday=$WeekIntArray[$addDateweek[$AddDatekey]];
					$schdet1 .="|^AkkenSplit^||^AkkSplitCol^|$AddDateval|^AkkSplitCol^|$AddDateweekday|^AkkSplitCol^|".$$AddDateTimefrom."|^AkkSplitCol^|".$$AddDateTimeTo;
				}
			}
		}

		///////////////Week schedule update/////////////////////////////////////////////////////////////////
		if(is_array($Updweek))
		{
			foreach($Updweek as $Updkey=>$Updval)
			{
				$UpdTimefrom="UpdSchdFrom".$Updkey;
				$UpdTimeTo="UpdSchdTo".$Updkey;

				if($UpdchkSchedule[$Updkey]!="Y" && (trim($$UpdTimefrom)!="" && trim($$UpdTimeTo)!=""))
				{
					$Updweekday=$WeekIntArray[$Updval];
					$schdet1 .="|^AkkenSplit^|$Updkey|^AkkSplitCol^||^AkkSplitCol^|$Updweekday|^AkkSplitCol^|".$$UpdTimefrom."|^AkkSplitCol^|".$$UpdTimeTo;
				}
			}
		}

		//////////////  Date Schedule Update/////////////////////////////////
		if(is_array($Upddate))
		{
			foreach($Upddate as $UpdDatekey=>$UpdDateval)
			{
				$UpdDateTimefrom="UpdSchdFrom".$UpdDatekey;
				$UpdDateTimeTo="UpdSchdTo".$UpdDatekey;

				if($UpdchkSchedule[$UpdDatekey]!="Y"  && (trim($$UpdDateTimefrom)!="" && trim($$UpdDateTimeTo)!=""))
				{
					$UpddateArr=explode("/",$UpdDateval);
					$Upddate_sch=$UpddateArr[2]."-".$UpddateArr[0]."-".$UpddateArr[1]." 00:00:00";
					$UpdDateweekday=$WeekIntArray[$UpdDateweek[$UpdDatekey]];
					$schdet1 .="|^AkkenSplit^|$UpdDatekey|^AkkSplitCol^|$UpdDateval|^AkkSplitCol^|$UpdDateweekday|^AkkSplitCol^|".$$UpdDateTimefrom."|^AkkSplitCol^|".$$UpdDateTimeTo;
				}
			}
		}
	}
	
	 /* TLS-01202018 */

	$schdet=$schdet1;
	session_update("schdet");

	if($page15 != "")
	{
		if($cancelstatus != "")
			$astatus	= $cancelstatus;

		$page152	= explode("|",$page15);
		$createdate	= explode("^AKK^",$page152[59]);
		$page152[59]	= $createdate[1];

		$jlist 		= explode('|',$recno);
		$hr_sno=$jlist[0];


		$emp_listsno 	= $jlist[3];
		$jotype		= getManage($page152[2]);
		$jtype		= $page152[36];

		$jobtypeIsDirect= "YES";
		$candidateStatus= "";
		$currentStatusOfAss= "";

		$sql_loc	= "SELECT contact_manage.serial_no
				FROM
					contact_manage,
					hrcon_compen
				WHERE
					contact_manage.status != 'BP'
				AND
					hrcon_compen.location = contact_manage.serial_no
				AND
					hrcon_compen.username = '".$username."'
				AND
					hrcon_compen.ustatus = 'active'";

		$res_loc	= mysql_query($sql_loc,$db);
		$fetch_loc	= mysql_fetch_row($res_loc);
		$loc_user	= $fetch_loc[0];

		if($jlist[2] == "pending")
			$currentStatusOfAss	= "pending";

		$close_que	= "SELECT posid,jtype FROM hrcon_jobs WHERE sno='".$hr_sno."'";
		$close_res	= mysql_query($close_que,$db);
		$close_row	= mysql_fetch_row($close_res);

		$jobPosSno	= $close_row[0];
		$prevJobType	= $close_row[1];

		// get the hrcon_jobs record of that job //

		$que		= "SELECT contactsno,appno FROM assignment_schedule WHERE contactsno LIKE '".$hr_sno."|%' AND modulename='HR->Assignments' AND invapproved='active'";

		$res		= mysql_query($que,$db);
		$approw		= mysql_fetch_row($res);

		$varsno		= explode("|",$approw[0]);
		$hrsno		= $varsno[0];
		$empsno		= $varsno[1];
		$vvid 		= $hrsno."|".$empsno ;

		$mode_rate_type = "hrcon";	
		$type_order_id 	= $hrsno;	
		$ratesObj->doBackupExistingRates();

		if($acceptJobtitle == 'YES')
		{
			$que1	= "UPDATE hrcon_compen SET designation='".$job_title."' WHERE username='".$conusername."' AND ustatus = 'active'";
			mysql_query($que1,$db);

			$que2	= "UPDATE empcon_compen SET designation='".$job_title."' WHERE username='".$conusername."'";
			mysql_query($que2,$db);
		}

		//funtion to get the classid from department fun in commonfuns.inc
		$classid	= displayDepartmentName($page152[88],true);
		$hr_insert_id	= "";
		$assignStatus	= "active";
		$Assignment_Job_Type = getManage($page152[2]); //To get the job type

		$page152[36] 	= "OP";
		$projectStatus	= $page152[36];

		$assqry 	= "SELECT pusername,assign_no FROM hrcon_jobs WHERE sno = ".$hrsno;
		$assres 	= mysql_query($assqry,$db);
		$assrow 	= mysql_fetch_row($assres);
		$assg_pusername = $assrow[1];

		if($assg_pusername == "")
			$assg_pusername	= get_Assginment_Seq();

		$pusername_jobs 	= $assg_pusername;
		
		$get_hishrcon_jobs	= "SELECT sno FROM his_hrcon_jobs WHERE pusername = '".$pusername_jobs."' AND ustatus IN ('active','closed','cancel','pending')";
		$res_hishrcon_jobs 	= mysql_query($get_hishrcon_jobs,$db);
		$row_hishrcon_jobs 	= mysql_fetch_row($res_hishrcon_jobs);
		$hishrcon_jobs_sno	= $row_hishrcon_jobs[0];

		/*Query for Inserting the Deleted hrcon person Details into his_persons_assignment table*/	
		$selectPersonlist = "SELECT sno FROM persons_assignment WHERE asgnid = '".$hrsno."' AND asgn_mode='hrcon'";
		$resultperson = mysql_query($selectPersonlist);
		if(mysql_num_rows($resultperson)>0){
			$his_person_insert	= "INSERT INTO his_persons_assignment(asgnid,asgn_mode,person_id,cuser,cdate,muser,mdate) SELECT '".$hishrcon_jobs_sno."','hrcon', person_id, cuser, cdate,'".$username."',NOW() FROM persons_assignment WHERE asgnid = '".$hrsno."' AND asgn_mode='hrcon'";
			mysql_query($his_person_insert,$db);
		}

		

		$upd_hrcon_jobs		= "DELETE FROM hrcon_jobs WHERE sno='".$hrsno."'";
		mysql_query($upd_hrcon_jobs,$db);


        /*Query for retrieving the hrcon_burden_details data*/		
		$hrcon_query 	=  "SELECT sno, hrcon_jobs_sno, ratemasterid, ratetype, bt_id, bi_id FROM hrcon_burden_details WHERE hrcon_jobs_sno = '".$hrsno."'";
		$hrcon_res 		= mysql_query($hrcon_query,$db);
		$hrcon_rows	= mysql_fetch_row($hrcon_res);
		
		$del_person	= "DELETE FROM persons_assignment WHERE asgnid='".$hrsno."' AND asgn_mode='hrcon'";
		mysql_query($del_person,$db);



		/*Query for Inserting the Deleted hrcon burden details into his_hrcon_burden_details table*/	
		
        $OldBudenHis = "SELECT sno FROM his_hrcon_burden_details WHERE hrcon_jobs_sno ='".$hishrcon_jobs_sno."'";
        $OldBudenResultHis = mysql_query($OldBudenHis);
            
        if(mysql_num_rows($OldBudenResultHis)==0){	
			$his_comm_insert	= "INSERT INTO his_hrcon_burden_details(hrcon_jobs_sno, ratemasterid, ratetype, bt_id, bi_id) SELECT '".$hishrcon_jobs_sno."', ratemasterid, ratetype, bt_id, bi_id FROM hrcon_burden_details WHERE hrcon_jobs_sno = '".$hrsno."'";
			mysql_query($his_comm_insert,$db);
		}
			$del_burden_details1	= "DELETE FROM hrcon_burden_details WHERE hrcon_jobs_sno='".$hrsno."'";
			mysql_query($del_burden_details1,$db);
		
		
        
		if($schedule_display == 'NEW')
		{
			$previousDate = date("Y-m-d",strtotime("-2 months",strtotime(date("Y-m-d"))));
			
			//deleting the hrcon and empcon time frame details for past date which are not selected
			$del_hrcon_tf_sql = "DELETE FROM hrconjob_sm_timeslots WHERE pid='".$hrsno."' AND shift_date < '".$previousDate."' AND sm_sno NOT IN (".$shift_snos.")";
			mysql_query($del_hrcon_tf_sql,$db);			
			
			//deleting the hrcon and empcon time frame details
			$smHrOldRefId = $hrsno;
			$del_hrcon_tf_sql = "DELETE FROM hrconjob_sm_timeslots WHERE pid='".$hrsno."' AND shift_date >= '".$previousDate."'";
			mysql_query($del_hrcon_tf_sql,$db);
			
		}
		
		/////////////////// UDF migration to customers ///////////////////////////////////
		include_once('custom/custome_save.php');		
		$directEmpUdfObj = new userDefinedManuplations();
		
		/////////////////// UDF migration to customers ///////////////////////////////////

		$updassignment_array	= array("invapproved" => "backup", "appno" => "appno='".$approw[1]."'", "userid" => "", "modulename" => "", "contactsno" => "");
		updateAssignmentSchedule($updassignment_array);
		
		if($schedule_display == 'OLD')
		{
			$query	= "DELETE FROM empcon_tab WHERE tabsno='".$approw[1]."' AND coltype='assign'";
			mysql_query($query,$db);
		}
		
		
		/*Query for retrieving the assign_commission data*/
		
		$assign_query 		=  "SELECT sno, username, assignid, assigntype, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput, shift_id FROM assign_commission WHERE assignid = '".$hrsno."' AND shift_id='".$shift_id."'";
		$assign_res 		= mysql_query($assign_query,$db);
		$assign_rows		= mysql_fetch_row($assign_res);
		
		
		/*Query for Inserting the Deleted commissions into his_assign_commission table
		
			Need to delete the history records due to conflict with hrcon_jobs sno when updating rates from 
			Accountiung >> Assignmennt >> Add/Update Rates
		*/
		$delete_Comm_his = "DELETE FROM his_assign_commission WHERE assignid = '".$hrsno."' AND shift_id='".$shift_id."' AND assigntype='H'";
		mysql_query($delete_Comm_his,$db);
		// END

		$his_comm_insert	= "INSERT INTO his_assign_commission(username, assignid, assigntype, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput, shift_id) SELECT username, '".$hishrcon_jobs_sno."', assigntype, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput, shift_id FROM assign_commission WHERE assignid = '".$hrsno."' AND shift_id='".$shift_id."' AND assigntype='H'";
		
		mysql_query($his_comm_insert,$db);
		
		$delcommqry 	= "DELETE FROM assign_commission WHERE assignid = '".$hrsno."' AND assigntype = 'H' AND shift_id='".$shift_id."'";
		$resdelcomm 	= mysql_query($delcommqry,$db);		


		$query 		=  "SELECT candid,username,ctype,sno FROM candidate_list WHERE candid = concat('emp','".$emp_listsno."')";
		$res 		= mysql_query($query,$db);
		$can_rows	= mysql_fetch_row($res);
		$cand_username 	= $can_rows[1];
		$cand_sno 	= $can_rows[3];
		$candtype	= 'Employee';

		$billRateValue	= explode("^^",$page152[24]);
		$payRateValue	= explode("^^",$page152[25]);
		$payDet		= explode("^^",$page152[29]);
		
		if($hcloseasgn == "Y")
			closeDirectJobAssignments($henddate);		

		if($jotype == "Direct" && $astatus != "cancel")
		{
			$jobtypeIsDirect	= "YES";
			$astatus 		= "closed";
			if(!empty($savestatus))//If user clicks on save instead of approve - rajesh
			{ 
				$astatus	= "active";
			}
		}
		else if($jotype == "Temp/Contract to Direct" && $astatus == "closed")
		{
			$jobtypeIsDirect	= "YES";
		}
		else if($jotype == "Internal Direct")
		{
			$candidateStatus	= 'Active';
			$queemp			= "SELECT username FROM emp_list WHERE sno='".$emp_listsno."'";
			$rsemp			= mysql_query($queemp,$db);
			$resemp			= mysql_fetch_array($rsemp);

			$query 			= "SELECT candid,username,sno FROM candidate_list WHERE candid = concat('emp','".$emp_listsno."')";
			$res 			= mysql_query($query,$db);
			$can_rows 		= mysql_fetch_row($res);
			$cand_username 		= $can_rows[1];
			$cand_sno 		= $can_rows[2];

			$cand_prof		= "UPDATE candidate_prof SET availsdate='inactive' WHERE username='".$cand_username."'";
			mysql_query($cand_prof,$db);
		}

		//To calculate the margin and markup..
		$SendString		= $payDet[0]."|".$payRateValue[0]."|".$billRateValue[0]."|".$page152[26]."|".$page152[27]."|".$page152[28]."|".$page152[92];
		if(RATE_CALCULATOR=='N'){
                $RetString		= comm_calculate_Active($SendString);
		$RetString_Array	= explode("|",$RetString);
		$payRateValue[0]	= $RetString_Array[1];
		$billRateValue[0]	= $RetString_Array[2];
		$page152[26]		= $RetString_Array[3];
		$page152[27]		= $RetString_Array[4];
		$page152[28]		= $RetString_Array[5];    
                }
                
		if($astatus == "closed" || $astatus == "cancel")
		{
			if($astatus == "closed")
				$candidateStatus	= "Closed";
			else
				$candidateStatus	= "Cancelled";


			$moduleqry 	= "SELECT ustatus,posid FROM hrcon_jobs WHERE sno = '".$hrsno."'" ;
			$moduleres 	= mysql_query($moduleqry,$db);
			$modulerow 	= mysql_fetch_array($moduleres);

			if($modulerow[0] == "pending" && $astatus == "cancel")
			{
				$UpdQry 	= "UPDATE posdesc SET closepos = closepos-1 WHERE posid= '".$modulerow[1]."'";
				$ResUpd 	= mysql_query($UpdQry,$db);

				$UpdQry 	= "UPDATE hotjobs SET closepos = closepos-1 WHERE req_id= '".$modulerow[1]."' AND status != 'BP'";
				$ResUpd 	= mysql_query($UpdQry,$db);
				
				if($schedule_display == 'NEW')
				{
					// UPDATES JOB ORDER SHIFT SCHEDULING STATUS BASED ON POSITIONS FILLED
					$objCRMScheduleDetails->updateShiftStatus($modulerow[1]);	
				}				
			}

			$ustatus=$astatus;

			//if assignment is closed or cancel then date_ended is added 
			$date_ended_col		= "''";
			$reasonSno = 0;
			if($assgPStatus == "closed" || $assgPStatus == "cancel")
			{
				if(trim($page152[16]) != "0-0-0" && $page152[16] != "")
					$date_ended_col	= $page152[16];
				else
					$date_ended_col	= "NOW()";
			}

			if($candidateStatus	== "Closed"){
				$reasonSno = $close_reason;
			}elseif($candidateStatus == "Cancelled"){
				$reasonSno = $cancel_reason;
			}

			if($payDet[1] == "open")
				$payOpenVal = "Y";
			else if($payDet[1] == "rate")
				$payOpenVal = "N";

			if($payDet[2] == "open")
				$billOpenVal = "Y";
			else if($payDet[2] == "rate")
				$billOpenVal = "N";

			//If job type is internal direct or direct no need to insert per diem values in database.
			if($Assignment_Job_Type == "Internal Direct" || $Assignment_Job_Type == "Direct")
			{
				$page152[71] 	= "0.00";
				$page152[72] 	= "0.00";
				$page152[73] 	= "0.00";
				$page152[74] 	= "";
				$page152[75] 	= "";
				$page152[76] 	= "N";
				$page152[77] 	= "N";
			}

			$page152[79] = ($page152[76] == 'Y') ? $page152[79] : '0.00';//Checking whether it is billable or not--Raj

			// Removes NON-ASCII characters
			$page152[45] = preg_replace('/[^(\x20-\x7F)\n]/',' ', $page152[45]);
			
			// Handles non-printable characters
			$page152[45] = preg_replace('/&#[5-6][0-9]{4};/',' ', $page152[45]);
			if ($shift_id == "" || $shift_id == "0") {
				$shift_type = "";
			}
			$qs="'','".$conusername."','".$page152[2]."','".$page152[3]."','".$page152[4]."','".$page152[5]."','".$page152[6]."','".$page152[7]."','".$page152[8]."','".$page152[9]."','".$page152[10]."','".$page152[11]."','".$page152[12]."','".$page152[13]."','".$page152[14]."','".$page152[15]."','".$page152[16]."','".$page152[17]."','".$page152[18]."','".$page152[19]."','".$page152[20]."','".$page152[21]."','".$billRateValue[0]."','".$billRateValue[2]."','".$billRateValue[1]."','".$payRateValue[0]."','".$payRateValue[2]."','".$payRateValue[1]."','".$page152[30]."','".$page152[31]."','".$page152[32]."','".$page152[33]."','".$page152[34]."','".$page152[35]."','".$page152[36]."','".$page152[37]."','".$page152[38]."','".$page152[39]."','".$page152[40]."','".$page152[41]."','".addslashes($page152[42])."','".addslashes($page152[43])."','".$page152[44]."','".$page152[45]."','".$page152[46]."','".$page152[47]."','{$ustatus}',now(),'".$page152[26]."','".$page152[27]."','".$page152[28]."','".$payDet[0]."','".$payOpenVal."','".$billOpenVal."','".$payDet[3]."','".$payDet[4]."','".$jobPosSno."','".$assgnment_ownerid."','".$page152[52]."','".$page152[53]."','".$page152[54]."','".$page152[55]."','".$page152[56]."','".$page152[57]."','".$page152[58]."','".$page152[59]."','".addslashes($page152[60])."','".$loc_user."','".$page152[61]."','".$page152[62]."','".$page152[63]."','".$page152[64]."','".$page152[65]."','".$page152[66]."','".addslashes($page152[67])."','".addslashes($page152[68])."','".$page152[69]."','".$username."',NOW(),'".$page152[70]."','".$page152[71]."','".$page152[72]."','".$page152[73]."','".$page152[74]."','".$page152[75]."','".$page152[76]."','".$page152[77]."','".$page152[79]."','".$page152[80]."','".$assg_pusername."',".$date_ended_col.",'".$cand_sno."','".$pusername_jobs."','".$page152[88]."','".$classid."','".$attention."','".$corp_code."','".$page152[91]."','".$schedule_display."','".$page152[92]."','".$shift_id."','".$worksiteCode."','".$shift_st_time."','".$shift_et_time."','".$reasonSno."','".$shift_type."','".$daypay."','".$licode."','".$federal_payroll."','".$nonfederal_payroll."','".$contractid."','".$classification."','".$assignment_timesheet_layout_preference."'";
			$query="insert into hrcon_jobs (sno,username,jotype,catid,project,refcode,posstatus,vendor,contact,client,manager,endclient,s_date,exp_edate,posworkhr,iterms,e_date,reason,hired_date,rate,rateper,rateperiod,bamount,bcurrency,bperiod,pamount,pcurrency,pperiod,pterms,otrate,ot_currency,ot_period,placement_fee,placement_curr,jtype,bill_contact,bill_address,wcomp_code,imethod,tsapp,bill_req,service_terms,hire_req,notes,addinfo,avg_interview,ustatus,udate,burden,margin,markup,calctype,prateopen,brateopen,prateopen_amt,brateopen_amt,posid,owner,otprate_amt,otprate_period,otprate_curr,otbrate_amt,otbrate_period,otbrate_curr,payrollpid,cdate,notes_cancel,offlocation,double_prate_amt,double_prate_period,double_prate_curr,double_brate_amt,double_brate_period,double_brate_curr,po_num,department,job_loc_tax,muser,mdate,date_placed,diem_lodging,diem_mie,diem_total,diem_currency,diem_period,diem_billable,diem_taxable,diem_billrate,madison_order_id,assign_no,date_ended,candidate,pusername,deptid,classid,attention,corp_code, industryid,schedule_display,bill_burden,shiftid,worksite_code,starthour,endhour,reason_id,shift_type,daypay,licode,federal_payroll,nonfederal_payroll,contractid,classification,ts_layout_pref) values (".$qs.")";
			$hrres=mysql_query($query,$db);

			if($hrres)
			{
				$hrsno		= mysql_insert_id($db);
				
				//Update Worksite_id to hrcon_jobs from contact_manage--Mohan
				$cm_qry = "select serial_no from contact_manage where loccode='".$worksiteCode."'";
				$rescm_qry = mysql_query($cm_qry,$db);
				$worksite_id=mysql_fetch_row($rescm_qry);
				$updateWorksiteid = "UPDATE hrcon_jobs SET worksite_id = '".$worksite_id[0]."' WHERE sno='".$hrsno."'";
				
                                saveBurdenDetails($burdenTypeDetails,$burdenItemDetails,'hrcon_burden_details','hrcon_jobs_sno','insert',$hrsno);
                                saveBillBurdenDetails($bill_burdenTypeDetails,$bill_burdenItemDetails,'hrcon_burden_details','hrcon_jobs_sno','insert',$hrsno);
				$jobSno		= $hrsno;
				if($studentlistids !=''){
					$personids = explode(',',$studentlistids);
					foreach ($personids as $personid) {
						if (($hrsno !="" && $hrsno !="0") && ($personid !="" && $personid !="0")) {
							$insertPersons = "INSERT INTO persons_assignment (asgnid,asgn_mode,person_id,cuser,cdate,muser,mdate) VALUES('".$hrsno."','hrcon','".$personid."','".$username."',NOW(),'".$username."',NOW())";
							mysql_query($insertPersons,$db);
						}		
					}
				}
				if ($shift_type == "perdiem" && $schedule_display == 'NEW') {
					if ($delperdiem_shiftid !="") {
						$hrmPerdiemShiftSchObj->delPerdiemShiftFrmHrconJob($assg_pusername,$candrn);

						$oldtimeslotvalues = $hrmPerdiemShiftSchObj->insNewHrconJobPerdiemShift($hrsno,$assg_pusername,$candrn,$username,$conusername,'','');

					}else{

						if (count($_SESSION['deletedAssignPerdiemShiftSch'.$candrn])>0) {
							$delTimeslotStr = $hrmPerdiemShiftSchObj->delPerdiemAssignShiftDetails($smHrOldRefId,$candrn,$conusername,'hrcon');

							if ($delTimeslotStr !="" ) {
								$objScheduleDetails->updAvailableEmpForDeletedTimeSlots($username, $conusername, $delTimeslotStr, $assg_pusername);
							}
						}
						$oldtimeslotvalues = $hrmPerdiemShiftSchObj->updtPerdiemShiftOldHrconJobSnoToNewSno($smHrOldRefId,$hrsno,$assg_pusername,$candrn,$conusername);

						if (($oldShiftSno != $shift_id) || (count($_SESSION['modifiedAssignPerdiemShiftSch'.$candrn])>0)) {

							$objScheduleDetails->insCandEmpTimeSlots($conusername,"hrcon_sm_timeslots",$assg_pusername,$oldtimeslotvalues,$username);
							$objScheduleDetails->updBusyEmpTimeSlots($username, $conusername, $oldtimeslotvalues, $assg_pusername);
							//Dumping data into Candidate Shift Schedule from Employee Shift Schedule from the last date to future dates only.
							$previousDate = date("Y-m-d",strtotime("-2 months",strtotime(date("Y-m-d"))));
							$objScheduleDetails->deleteNInsertDataIntoCandidateFromEmployeeScheduleTable($emp_sno_val, $conusername,$previousDate);
						}
					}
					$hrmPerdiemShiftSchObj->doCleanUpShiftData();
				}
				else if($schedule_display == 'NEW')
				{
					$objScheduleDetails->insHrEmpConJobsTimeSlots($username,'hrconjob_sm_timeslots',$hrsno,$timeslotvalues);
					//Updates RefId (pid) in past shift schedules
					$objScheduleDetails->updateRefIdInPastDates($username,'hrconjob_sm_timeslots',$smHrOldRefId,$hrsno);
				}
				
				/////////////////// UDF migration to customers ///////////////////////////////////
				include_once('custom/custome_save.php');		
				$directEmpUdfObj = new userDefinedManuplations();
				$directEmpUdfObj->insertUserDefinedData($hrsno, 7);
				/////////////////////////////////////////////////////////////////////////////////
				
				$hrcon_jobs_sno 	= $hrsno;
				$ccid 			= $hrsno."|";
				$hrass_id		= $hrsno;
				$hr_insert_id		= $hrsno;
				$assignRate_id 		= $ccid;

				$assignment_array	= array("modulename" => "HR->Assignments", "pusername" => $assg_pusername, "userid" => $conusername, "contactsno" => $ccid, "invapproved" => $ustatus);
				$tabappiont_ass_id	= insertAssignmentSchedule($assignment_array);

				// insert new tab settings
				$schdet1 		= explode("|^AkkenSplit^|",$schdet);
				$asd 			= count($schdet1);
				for ($i=1; $i<$asd; $i++)
				{
					$newarray 	= explode("|^AkkSplitCol^|",$schdet1[$i]);
					$InsdateArr	= explode("/",$newarray[1]);
					$Insdate	= $InsdateArr[2]."-".$InsdateArr[0]."-".$InsdateArr[1]." 00:00:00";
					if($schedule_display == 'OLD')
					{
						$sheQry	= "INSERT INTO hrcon_tab (sno,tabsno,starthour,endhour,wdays,sch_date,coltype) VALUES ('','".$tabappiont_ass_id."','".$newarray[3]."','".$newarray[4]."','".$newarray[2]."','".$Insdate."','assign')";
						mysql_query($sheQry,$db);
					}

				}

				if($page152[49] == "nav")
				{
					if($commission_session	== "")
						$commission_session	= $commissionValues;

					$commissionData		= explode("|",$commission_session);
					$empno			= explode(",",$page152[51]);
					$emptxt			= explode(",",$commissionData[0]);
					$rateval		= explode(",",$commissionData[1]);
					$payval			= explode(",",$commissionData[2]);
					$rolename		= explode(",",$commissionData[4]);
					$roleoverwrite		= explode(",",$commissionData[5]);
					$roleEDisable		= explode(",",$commissionData[6]);

					for($i=0; $i <= $page152[50]; $i++) {
						if(substr($empno[$i],0,3) == "emp") {
							$empno[$i]	= str_replace("emp","",$empno[$i]);
							$typeOfPerson	= "E";
						}
						else {
							$typeOfPerson = "A";
						}

						if($rolename[$i] != '') {
							$comm_insert	= "INSERT INTO assign_commission(sno, username, assignid, assigntype, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput, shift_id) VALUES('', '".$username."', '".$hrsno."', 'H', '".$empno[$i]."', '$typeOfPerson', '".$emptxt[$i]."', '".$payval[$i]."', '".$rateval[$i]."', '".$rolename[$i]."', '".$roleoverwrite[$i]."', '".$roleEDisable[$i]."','".$shift_id."')";
							mysql_query($comm_insert, $db);
						}
					}
				}

				// Insert ACA employee Information
				$job_type = getManage($page152[2]);

				if($job_type=='Internal Direct' && ACA_ENABLED=='Y')
				{
					$acaDet->checkEmpDetailsforACA($conusername,"");
				}

				$query  =  "UPDATE emp_list e, hrcon_jobs hj SET e.emp_jtype='', e.cur_project = hj.project, e.rate = hj.pamount  WHERE e.username=hj.username AND hj.ustatus IN ('closed','cancel') AND hj.pusername='".$pusername_jobs."' ";
		    		mysql_query($query,$db);
			}
		}
		else
		{
			$todayDate 		= date("m-d-Y");
			
			//setting the variable values based on the save/approve clicked - rajesh

			// changed $ustatus		= "pending"; because of mapping empcon_jobs to hrcon_jobs 
			$ustatus		= "pending";
			$assg_status_val	= "pending";
			$jtype_val		= $page152[36];
			$candidateStatus	= "Needs Approval";
			
			if(empty($savestatus))
			{ 
				$assg_status_val	= "approved";
				$jtype_val		= $page152[36];
				$candidateStatus	= "Active";
				// hrcon_jobs making as active when click on approve
				$ustatus		= "active";
			}

			if($payDet[1] == "open")
				$payOpenVal = 'Y';
			else if($payDet[1] == "rate")
				$payOpenVal = 'N';

			if($payDet[2] == "open")
				$billOpenVal = 'Y';
			else if($payDet[2] == "rate")
				$billOpenVal = 'N';

			$projectStatus	= "OP";

			//If job type is internal direct or direct no need to insert per diem values in database.

			//To get the job type
			$Assignment_Job_Type = getManage($page152[2]);
			if($Assignment_Job_Type=="Internal Direct" || $Assignment_Job_Type=="Direct")
			{
				$page152[71] = "0.00";
				$page152[72] = "0.00";
				$page152[73] = "0.00";
				$page152[74] = "";
				$page152[75] = "";
				$page152[76] = "N";
				$page152[77] = "N";
			}

			//Checking whether it is billable or not -- Raj
			$page152[79] = ($page152[76] == 'Y') ? $page152[79] : '0.00';		
			
			// Removes NON-ASCII characters
			$page152[45] = preg_replace('/[^(\x20-\x7F)\n]/',' ', $page152[45]);
			
			// Handles non-printable characters
			$page152[45] = preg_replace('/&#[5-6][0-9]{4};/',' ', $page152[45]);

			if ($shift_id == "" || $shift_id == "0") {
				$shift_type = "";
			}

			$qs = "'','".$conusername."','".$page152[2]."','".$page152[3]."','".$page152[4]."','".$page152[5]."','".$page152[6]."','".$page152[7]."','".$page152[8]."','".$page152[9]."','".$page152[10]."','".$page152[11]."','".$page152[12]."','".$page152[13]."','".$page152[14]."','".$page152[15]."','".$page152[16]."','".$page152[17]."','".$page152[18]."','".$page152[19]."','".$page152[20]."','".$page152[21]."','".$billRateValue[0]."','".$billRateValue[2]."','".$billRateValue[1]."','".$payRateValue[0]."','".$payRateValue[2]."','".$payRateValue[1]."','".$page152[30]."','".$page152[31]."','".$page152[32]."','".$page152[33]."','".$page152[34]."','".$page152[35]."','".$jtype_val."','".$page152[37]."','".$page152[38]."','".$page152[39]."','".$page152[40]."','".$page152[41]."','".addslashes($page152[42])."','".addslashes($page152[43])."','".$page152[44]."','".$page152[45]."','".$page152[46]."','".$page152[47]."','{$ustatus}',now(),'".$page152[26]."','".$page152[27]."','".$page152[28]."','".$payDet[0]."','".$payOpenVal."','".$billOpenVal."','".$payDet[3]."','".$payDet[4]."','".$jobPosSno."','".$assgnment_ownerid."','".$page152[52]."','".$page152[53]."','".$page152[54]."','".$page152[55]."','".$page152[56]."','".$page152[57]."','".$page152[58]."','".$page152[59]."','".addslashes($page152[60])."','".$loc_user."','".$page152[61]."','".$page152[62]."','".$page152[63]."','".$page152[64]."','".$page152[65]."','".$page152[66]."','".addslashes($page152[67])."','".addslashes($page152[68])."','".$page152[69]."','".$username."',NOW(),'".$page152[70]."','".$page152[71]."','".$page152[72]."','".$page152[73]."','".$page152[74]."','".$page152[75]."','".$page152[76]."','".$page152[77]."','".$page152[79]."','".$page152[80]."','".$assg_pusername."','".$cand_sno."','".$pusername_jobs."','".$page152[88]."','".$classid."','".$attention."','".$corp_code."','".$page152[91]."','".$schedule_display."','".$page152[92]."','".$shift_id."','".$worksiteCode."','".$shift_st_time."','".$shift_et_time."','0','".$shift_type."','".$daypay."','".$licode."','".$federal_payroll."','".$nonfederal_payroll."','".$contractid."','".$classification."','".$assignment_timesheet_layout_preference."'";
			$query	= "INSERT INTO hrcon_jobs (sno,username,jotype,catid,project,refcode,posstatus,vendor,contact,client,manager,endclient,s_date,exp_edate,posworkhr,iterms,e_date,reason,hired_date,rate,rateper,rateperiod,bamount,bcurrency,bperiod,pamount,pcurrency,pperiod,pterms,otrate,ot_currency,ot_period,placement_fee,placement_curr,jtype,bill_contact,bill_address,wcomp_code,imethod,tsapp,bill_req,service_terms,hire_req,notes,addinfo,avg_interview,ustatus,udate,burden,margin,markup,calctype,prateopen,brateopen,prateopen_amt,brateopen_amt,posid,owner,otprate_amt,otprate_period,otprate_curr,otbrate_amt,otbrate_period,otbrate_curr,payrollpid,cdate,notes_cancel,offlocation,double_prate_amt,double_prate_period,double_prate_curr,double_brate_amt,double_brate_period,double_brate_curr,po_num,department,job_loc_tax,muser,mdate,date_placed,diem_lodging,diem_mie,diem_total,diem_currency,diem_period,diem_billable,diem_taxable,diem_billrate,madison_order_id,assign_no,candidate,pusername,deptid,classid,attention,corp_code, industryid,schedule_display,bill_burden,shiftid,worksite_code,starthour,endhour,reason_id,shift_type,daypay,licode,federal_payroll,nonfederal_payroll,contractid,classification,ts_layout_pref) VALUES(".$qs.")";
			$hrres	= mysql_query($query,$db);			

			
			if($hrres)
			{
				$hrass_id	= mysql_insert_id($db);
				
				//Update Worksite_id to hrcon_jobs from contact_manage--Mohan
				$cm_qry = "select serial_no from contact_manage where loccode='".$worksiteCode."'";
				$rescm_qry = mysql_query($cm_qry,$db);
				$worksite_id=mysql_fetch_row($rescm_qry);
				$updateWorksiteid = "UPDATE hrcon_jobs SET worksite_id = '".$worksite_id[0]."' WHERE sno='".$hrass_id."'";
				mysql_query($updateWorksiteid,$db);
				
                                saveBurdenDetails($burdenTypeDetails,$burdenItemDetails,'hrcon_burden_details','hrcon_jobs_sno','insert',$hrass_id);
                                saveBillBurdenDetails($bill_burdenTypeDetails,$bill_burdenItemDetails,'hrcon_burden_details','hrcon_jobs_sno','insert',$hrass_id);
				$jobSno			= $hrass_id;
				$hrcon_jobs_sno 	= $hrass_id;
				$hr_insert_id		= $hrass_id;
				if($studentlistids !=''){
					$personids = explode(',',$studentlistids);
					foreach ($personids as $personid) {
						if (($hrass_id !="" && $hrass_id !="0") && ($personid !="" && $personid !="0")) {
							$insertPersons = "INSERT INTO persons_assignment (asgnid,asgn_mode,person_id,cuser,cdate,muser,mdate) VALUES('".$hrass_id."','hrcon','".$personid."','".$username."',NOW(),'".$username."',NOW())";
							mysql_query($insertPersons,$db);
						}
							
					}
				}
				if ($shift_type == "perdiem" && $schedule_display == 'NEW') {
					if (count($_SESSION['deletedAssignPerdiemShiftSch'.$candrn])>0) {
						$delTimeslotStr = $hrmPerdiemShiftSchObj->delPerdiemAssignShiftDetails($smHrOldRefId,$candrn,$conusername,'hrcon');

						if ($delTimeslotStr !="" ) {
							$objScheduleDetails->updAvailableEmpForDeletedTimeSlots($username, $conusername, $delTimeslotStr, $assg_pusername);
						}
					}

					if ($delperdiem_shiftid !="") {
						$hrmPerdiemShiftSchObj->delPerdiemShiftFrmHrconJob($assg_pusername,$candrn);
						
						$oldtimeslotvalues = $hrmPerdiemShiftSchObj->insNewHrconJobPerdiemShift($hrass_id,$assg_pusername,$candrn,$username,$conusername,'','');

					}else if($active_perdiem_shiftid !="" && ($perdiem_shiftid == "" || $perdiem_shiftid == "0")){
						$oldtimeslotvalues = $hrmPerdiemShiftSchObj->insNewHrconJobPerdiemShift($hrass_id,$assg_pusername,$candrn,$username,$conusername,'','');
					}else{
						$oldtimeslotvalues = $hrmPerdiemShiftSchObj->updtPerdiemShiftOldHrconJobSnoToNewSno($smHrOldRefId,$hrass_id,$assg_pusername,$candrn);
					}
					
					if (($oldShiftSno != $shift_id) ||(count($_SESSION['modifiedAssignPerdiemShiftSch'.$candrn])>0) && ($oldtimeslotvalues !=""))
					{
						
						$objScheduleDetails->insCandEmpTimeSlots($conusername,"hrcon_sm_timeslots",$assg_pusername,$oldtimeslotvalues,$username);
						$objScheduleDetails->updBusyEmpTimeSlots($username, $conusername, $oldtimeslotvalues, $assg_pusername);
						//Dumping data into Candidate Shift Schedule from Employee Shift Schedule from the last date to future dates only.
						$previousDate = date("Y-m-d",strtotime("-2 months",strtotime(date("Y-m-d"))));
						$objScheduleDetails->deleteNInsertDataIntoCandidateFromEmployeeScheduleTable($emp_sno_val, $conusername,$previousDate);
					}elseif (($oldAssignStatus != $ustatus) && ($oldtimeslotvalues !="")) {
						
						$objScheduleDetails->insCandEmpTimeSlots($conusername,"hrcon_sm_timeslots",$assg_pusername,$oldtimeslotvalues,$username);
						$objScheduleDetails->updBusyEmpTimeSlots($username, $conusername, $oldtimeslotvalues, $assg_pusername);
						//Dumping data into Candidate Shift Schedule from Employee Shift Schedule from the last date to future dates only.
						$previousDate = date("Y-m-d",strtotime("-2 months",strtotime(date("Y-m-d"))));
						$objScheduleDetails->deleteNInsertDataIntoCandidateFromEmployeeScheduleTable($emp_sno_val, $conusername,$previousDate);
					}
					$hrmPerdiemShiftSchObj->doCleanUpShiftData();
				}
				else if($schedule_display == 'NEW')
				{
					$objScheduleDetails->insHrEmpConJobsTimeSlots($username,'hrconjob_sm_timeslots',$hrass_id,$timeslotvalues);
					//Updates RefId (pid) in past shift schedules
					$objScheduleDetails->updateRefIdInPastDates($username,'hrconjob_sm_timeslots',$smHrOldRefId,$hrass_id);
				}
				
				/////////////////// UDF migration to customers ///////////////////////////////////
				include_once('custom/custome_save.php');		
				$directEmpUdfObj = new userDefinedManuplations();
				//Changed to store the hrconjobs->sno in cdf assignments table rec_id - vilas.B
				$directEmpUdfObj->insertUserDefinedData($hrass_id, 7);
				/////////////////////////////////////////////////////////////////////////////////


				$ccid 			= $hrass_id."|";
				$getPusername 		= $pusername_jobs;
				$assignRate_id 		= $ccid;

				$assignment_array	= array("modulename" => "HR->Assignments", "pusername" => $pusername_jobs, "userid" => $conusername, "contactsno" => $ccid, "invapproved" => 'active');
				$tabappiont_ass_id	= insertAssignmentSchedule($assignment_array);
				
				if($schedule_display == 'OLD')
				{
					$query 		= "DELETE FROM empcon_tab WHERE tabsno='".$approw[1]."' AND coltype='assign'";
					mysql_query($query,$db);
				}

				// insert new tab settings
				$schdet1	= explode("|^AkkenSplit^|",$schdet);
				$asd 		= count($schdet1);
				for ($i=1; $i<$asd; $i++)
				{
					$newarray	= explode("|^AkkSplitCol^|",$schdet1[$i]);
					$InsdateArr	= explode("/",$newarray[1]);
					$Insdate	= $InsdateArr[2]."-".$InsdateArr[0]."-".$InsdateArr[1]." 00:00:00";
					
					if($schedule_display == 'OLD')
					{
						$sheQry	= "INSERT INTO empcon_tab (sno,tabsno,starthour,endhour,wdays,sch_date,coltype) VALUES ('','".$tabappiont_ass_id."','".$newarray[3]."','".$newarray[4]."','".$newarray[2]."','".$Insdate."','assign')";
						mysql_query($sheQry,$db);					

						$sheQry	= "INSERT INTO hrcon_tab (sno,tabsno,starthour,endhour,wdays,sch_date,coltype) VALUES ('','".$tabappiont_ass_id."','".$newarray[3]."','".$newarray[4]."','".$newarray[2]."','".$Insdate."','assign')";
						mysql_query($sheQry,$db);
					}

				}

				// insert commission
				if($page152[49] == "nav")
				{
					if($commission_session == "")
						$commission_session	= $commissionValues;
						
					$commissionData		= explode("|",$commission_session);
					$empno			= explode(",",$page152[51]);
					$emptxt			= explode(",",$commissionData[0]);
					$rateval		= explode(",",$commissionData[1]);
					$payval			= explode(",",$commissionData[2]);
					$rolename		= explode(",",$commissionData[4]);
					$roleoverwrite		= explode(",",$commissionData[5]);
					$roleEDisable		= explode(",",$commissionData[6]);

					for($i=0; $i <= $page152[50]; $i++) {
						if(substr($empno[$i],0,3) == "emp") {
							$empno[$i]	= str_replace("emp","",$empno[$i]);
							$typeOfPerson	= "E";
						}
						else {
							$typeOfPerson	="A";
						}

						if($rolename[$i] != '') 
						{

							$comm_insert	= "INSERT INTO assign_commission(sno, username, assignid, assigntype, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput,shift_id) VALUES('', '".$username."', '".$hrass_id."', 'H', '".$empno[$i]."', '$typeOfPerson', '".$emptxt[$i]."', '".$payval[$i]."', '".$rateval[$i]."', '".$rolename[$i]."', '".$roleoverwrite[$i]."', '".$roleEDisable[$i]."','".$shift_id."')";
							mysql_query($comm_insert,$db);
						}
					}
				}
				//Updating emp_list jtype once pending assignment is approved
				$query  =  "UPDATE emp_list e, hrcon_jobs hj SET e.emp_jtype=hj.jtype, e.cur_project = hj.project, e.rate = hj.pamount  WHERE e.username=hj.username AND hj.ustatus IN ('active') AND hj.pusername='".$pusername_jobs."' ";
			    	mysql_query($query,$db);
			}
			//placement notification
			if($hrass_id!=''){
				$placementNotification->sendAssignmentNotifications($hrass_id);
			}	

			// Insert ACA employee Information
			$job_type = getManage($page152[2]);
				
			if($job_type=='Internal Direct' && ACA_ENABLED=='Y')
			{
				$acaDet->checkEmpDetailsforACA($conusername,"");
			}		
			//executes the function only when click on the approve - rajesh
			if(empty($savestatus))
			{
				checkOBAssignments('closed');
			}
		}

		$queemp		= "SELECT username FROM emp_list WHERE sno='".$emp_listsno."'";
		$rsemp		= mysql_query($queemp,$db);
		$resemp		= mysql_fetch_array($rsemp);

		$jtype_qry	= "SELECT jtype,DATE_ADD(MAX(DATE_FORMAT(STR_TO_DATE(e_date,'%m-%d-%Y'),'%Y-%m-%d')),INTERVAL 1 DAY),MAX(DATE_FORMAT(STR_TO_DATE(if(e_date='0-0-0','00-00-0000',e_date),'%m-%d-%Y'),'%Y-%m-%d')),DATE_ADD(MAX(DATE_FORMAT(exp_edate,'%Y-%m-%d')),INTERVAL 1 DAY),MAX(DATE_FORMAT(exp_edate,'%Y-%m-%d')) FROM hrcon_jobs WHERE username = '".$resemp[0]."' AND ustatus = 'active' GROUP BY username";

		$jtype_res 	= mysql_query($jtype_qry,$db);
		$jtype_row 	= mysql_fetch_row($jtype_res);

		// If the employee is on client project, need to get the max of end date from the assingments.
		if($jtype_row[0] == "OB")
		{
			// ON  BENCH
			$avail = "immediate";
		}
		else
		{
			//ON PROJECT
			if( ($jtype_row[2] == "0000-00-00" ) || ($jtype_row[2] == ''))
			{
				if( ($jtype_row[4] == "0000-00-00" ) || ($jtype_row[4] == '')) 
					$avail = "immediate";
				else
					$avail = $jtype_row[3];
			}
			else
			{
				$avail = $jtype_row[1];   //we will get the date in YYYY-MM-DD format
			}
		}

		//executes the function only when click on the approve - rajesh
		if(empty($savestatus))
		{
			//update the candidate avail date
			$sql_profque = "UPDATE candidate_prof SET availsdate='".$avail."' WHERE username='".$cand_username."'";
			mysql_query($sql_profque,$db);
	
			if($jotype == 'Internal Direct')
			{
				$que		= "UPDATE candidate_prof SET availsdate='inactive' WHERE username='$cand_username'";
				mysql_query($que,$db);
			}
		}
		if($jobtypeIsDirect == "YES")
		{
			$query		= "SELECT username,name FROM emp_list WHERE sno='".$jlist[3]."'";
			$res		= mysql_query($query,$db);
			$data		= mysql_fetch_row($res);

			if($hterminate == "Y")
			{
				$sterdate	= explode("-",$hterdate);
				$tdate		= $sterdate[2]."-".$sterdate[0]."-".$sterdate[1];

				$que		= "UPDATE emp_list SET empterminated='Y',tdate='$tdate',lstatus='ACTIVE',show_crm='Y' WHERE sno=".$emp_listsno;
				mysql_query($que,$db);

				$qrySelHrconCompen = "SELECT sno FROM hrcon_compen WHERE ustatus='active' AND username='".$conusername."'";
				$resSelHrconCompen = mysql_query($qrySelHrconCompen, $db);
	
				if(mysql_num_rows($resSelHrconCompen) > 0) {
					
					$rowSelHrconCompen = mysql_fetch_array($resSelHrconCompen);
	
					/* Making the active record as backup */
					$qryUpdHrconCompen = "UPDATE hrcon_compen SET ustatus='backup', udate=NOW() WHERE sno = '".$rowSelHrconCompen[0]."' AND ustatus='active'";
					mysql_query($qryUpdHrconCompen, $db);
	
					/* Inserting and Updating data into hrcon_compen table for maintaining the history in Employee >> Compensation tab */
					$qryInsHrconCompen = "INSERT INTO hrcon_compen(username, emp_id, dept, date_hire, location, status, salary, std_hours, over_time, rev_period, increment, bonus, designation, salper, shper, effect_date, ustatus, udate, emptype, job_type, emp_crm, pay_assign, benchrate, benchperiod, benchcurrency, ot_period, ot_currency, timesheet, posworkhr, double_rate_amt, double_brate_curr, double_rate_period, assign_double, sep_check, assign_bench, assign_overtime, diem_lodging, diem_mie, diem_total, diem_currency, diem_period, diem_billable, diem_taxable, diem_pay_assign, diem_billrate, bonus_billable, bonus_billrate, wcomp_code, assign_wcompcode, classid, emp_rehire_date, emp_terminate_date, paygroupcode, paycodesno,modified_user) SELECT username, emp_id, dept, date_hire, location, status, salary, std_hours, over_time, rev_period, increment, bonus, designation, salper, shper, effect_date, 'active', NOW(), emptype, job_type, emp_crm, pay_assign, benchrate, benchperiod, benchcurrency, ot_period, ot_currency, timesheet, posworkhr, double_rate_amt, double_brate_curr, double_rate_period, assign_double, sep_check, assign_bench, assign_overtime, diem_lodging, diem_mie, diem_total, diem_currency, diem_period, diem_billable, diem_taxable, diem_pay_assign, diem_billrate, bonus_billable, bonus_billrate, wcomp_code, assign_wcompcode, classid, '', '".$tdate."', paygroupcode, paycodesno,'".$username."' FROM hrcon_compen WHERE ustatus='backup' AND sno='".$rowSelHrconCompen[0]."'";
					mysql_query($qryInsHrconCompen, $db);
				
					/* Deleting and Inserting data into empcon_compen table for Employee >> Compensation tab */

					$qryDelEmpconCompen = "DELETE FROM empcon_compen WHERE username='".$conusername."'";
					mysql_query($qryDelEmpconCompen, $db);
			
					$qryInsEmpconCompen = "INSERT INTO empcon_compen(username, emp_id, dept, date_hire, location, status, salary, std_hours, over_time, rev_period, increment, bonus, designation, salper, shper, emptype, job_type, emp_crm, pay_assign, benchrate, benchperiod, benchcurrency, ot_period, ot_currency, timesheet, posworkhr, double_rate_amt, double_brate_curr, double_rate_period, assign_double, sep_check, assign_bench, assign_overtime, diem_lodging, diem_mie, diem_total, diem_currency, diem_period, diem_billable, diem_taxable, diem_pay_assign, diem_billrate, bonus_billable, bonus_billrate, wcomp_code, assign_wcompcode, classid, emp_rehire_date, emp_terminate_date, paygroupcode, paycodesno) SELECT username, emp_id, dept, date_hire, location, status, salary, std_hours, over_time, rev_period, increment, bonus, designation, salper, shper, emptype, job_type, emp_crm, pay_assign, benchrate, benchperiod, benchcurrency, ot_period, ot_currency, timesheet, posworkhr, double_rate_amt, double_brate_curr, double_rate_period, assign_double, sep_check, assign_bench, assign_overtime, diem_lodging, diem_mie, diem_total, diem_currency, diem_period, diem_billable, diem_taxable, diem_pay_assign, diem_billrate, bonus_billable, bonus_billrate, wcomp_code, assign_wcompcode, classid, emp_rehire_date, emp_terminate_date, paygroupcode, paycodesno FROM hrcon_compen WHERE ustatus='active' AND username='".$conusername."'";
					mysql_query($qryInsEmpconCompen, $db);
				}
				if($hterdate == date("m-d-Y"))
				{
					removeJobBoardIds($emp_listsno);

					$cand_prof	= "UPDATE candidate_list a,candidate_prof b set b.availsdate='inactive',a.status='ACTIVE' WHERE a.username=b.username AND a.username='".$cand_username."'";
					mysql_query($cand_prof,$db);

					$qued		= "SELECT emailuser FROM EmailAcc WHERE username='".$data[0]."'";
					$resd		= mysql_query($qued,$db);
					$rowd		= mysql_fetch_row($resd);

					$eaccname	= $companyuser."|".$data[0];

					require("sysDBmail.inc");
					$que		= "UPDATE mailbox SET suspended='Y' WHERE username='".$eaccname."'";
					mysql_query($que,$sysdb);
					mysql_close($sysdb);

					$queryState	= "SELECT status FROM users WHERE username='".$data[0]."'";
					$rsState	= mysql_query($queryState,$db);
					$rowState 	= mysql_fetch_row($rsState);
					$rowStateCount 	= mysql_num_rows($rsState);
                                        
                                        // check whether the user is ESS user or not
                                        $isESSUserQuery = "SELECT * FROM users WHERE type IN ('sp','PE','consultant') AND usertype='' AND username='".$data[0]."'";
                                        $isESSUserRes = mysql_query($isESSUserQuery,$db);
                                        $isESSUserCount = mysql_num_rows($isESSUserRes);

                                        $restrictESSUserAccDea="NO";
                                       
                                        if(DEFAULT_AKKUPAY=='Y' && $isESSUserCount>0){
                                            $restrictESSUserAccDea="YES";
                                        }
                                        
					if($rowState!='DA' && $rowStateCount != 0)
					{
						$queUserStatus	= "SELECT MAX(activated) FROM userstatus WHERE username = '".$data[0]."' GROUP BY username";
						$resUserStatus	= mysql_query($queUserStatus,$db);
						$rowUserStatus 	= mysql_fetch_row($resUserStatus);
                                                
                                                // Restrict the account deactivation for ESS user when akkupay is enabled
                                                if($restrictESSUserAccDea=="NO"){
                                                    $queUserStatus	= "UPDATE userstatus SET deactivated = now(),deactive_user='".$username."' WHERE activated = '".$rowUserStatus[0]."' AND username = '".$data[0]."'";
                                                    mysql_query($queUserStatus,$db);
                                                }

						updateSysTotalUsers();
					}
                                        // Restrict the account deactivation for ESS user when akkupay is enabled
                                        if($restrictESSUserAccDea=="NO"){
                                            $query= "UPDATE users SET status='DA' WHERE username='".$data[0]."'";
                                            mysql_query($query,$db);

                                            if($data[0] == $username)
                                            {
                                                    $sess_id	= session_id();
                                                    $session_dir	= ini_get("session.save_path");
                                                    unlink($session_dir."/sess_".$sess_id);
                                                    delFolder($WDOCUMENT_ROOT);
                                                    clearCookies($_COOKIE,"");
                                                    foreach($_SESSION as $key=>$value  )
                                                            unset($_SESSION[$key]);
                                                    print "<script language=javascript>window.location.href='/BSOS/Home/logout.php?comp=true';</script>";
                                                    exit;
                                            }
                                        }
				}
			}
		}

		//Updating of the candidate status based on the assignment status as In process/Closed
		$managesno		= getManageSno($candidateStatus,'interviewstatus');

		$sel_place_jobs 	= "SELECT pj.sno,
						pj.seqnumber,
						hj.notes_cancel
					FROM
						placement_jobs pj,
						hrcon_jobs hj
					WHERE
						pj.username=hj.username
					AND
						pj.assign_no=hj.assign_no
					AND
						hj.sno='".$hr_insert_id."'";
		$res_place_jobs 	= mysql_query($sel_place_jobs,$db);
		$fetch_place_jobs	= mysql_fetch_array($res_place_jobs);

		if($getNotesType>0)
			$NotesType 	= getManage($getNotesType);

		if($getCandStatus>0)
		{
			if($cand_username != "")
			{
				if($astatus == "active")
				{
					$clque	= "UPDATE candidate_list SET cl_status='$getCandStatus',ctype='Employee',muser='$username',mtime=NOW() WHERE username='$cand_username'";
					mysql_query($clque,$db);
				}
				else
				{
					if($hr_insert_id>0)
					{
						$aque	= "SELECT COUNT(1) FROM hrcon_jobs WHERE ustatus='active' AND username='$conusername' AND sno!=$hr_insert_id";
						$ares	= mysql_query($aque,$db);
						$arow	= mysql_fetch_row($ares);
					}
					else
					{
						$arow[0] = 0;
					}

					if($arow[0]== 0)
					{
						$clque	= "UPDATE candidate_list SET cl_status='$getCandStatus',ctype='Employee',muser='$username',mtime=NOW() WHERE username='$cand_username'";
						mysql_query($clque,$db);
					}
				}
			}
		}

		if($getJobStatus>0 && $jobPosSno>0)
		{
			$aque		= "SELECT COUNT(1) FROM hrcon_jobs WHERE ustatus='active' AND posid='$jobPosSno'";
			$ares		= mysql_query($aque,$db);
			$arow		= mysql_fetch_row($ares);

			if($arow[0] == 0)
			{
				if(empty($savestatus))//Updating the job status only when approve the assignement - rajesh
				{
					$joque="UPDATE posdesc SET posstatus='$getJobStatus',muser='$username',mdate=NOW() WHERE posid='$jobPosSno'";
					mysql_query($joque,$db);
				}
			}
		}

		if(trim($getNotesNew)!= "")
		{
			if($cand_sno>0)
			{
				// Removes NON-ASCII characters
				$getNotesNew	= preg_replace('/[^(\x20-\x7F)\n]/',' ', $getNotesNew);
				
				// Handles non-printable characters
				$getNotesNew	= preg_replace('/&#[5-6][0-9]{4};/',' ', $getNotesNew);
	
				$ique		="INSERT INTO notes (contactid,cuser,type,cdate,notes,notes_subtype) VALUES ($cand_sno,$username,'cand',NOW(),'".addslashes($getNotesNew)."','$getNotesType')";
				mysql_query($ique,$db);

				$nid 		= mysql_insert_id($db);

				$ique		= "INSERT INTO cmngmt_pr (con_id,username,tysno,title,sdate,subject,lmuser,subtype) VALUES ('cand$cand_sno','$username',$nid,'Notes',NOW(),'".addslashes($getNotesNew)."','$username','".addslashes($NotesType)."')";
				mysql_query($ique,$db);
			}

			if($jobPosSno>0)
			{
				// Removes NON-ASCII characters
				$getNotesNew	= preg_replace('/[^(\x20-\x7F)\n]/',' ', $getNotesNew);
				
				// Handles non-printable characters
				$getNotesNew	= preg_replace('/&#[5-6][0-9]{4};/',' ', $getNotesNew);
	
				$ique		= "INSERT INTO notes (contactid,cuser,type,cdate,notes,notes_subtype) VALUES ($jobPosSno,$username,'req',NOW(),'".addslashes($getNotesNew)."','$getNotesType')";
				mysql_query($ique,$db);

				$nid 		= mysql_insert_id($db);

				$ique		= "INSERT INTO cmngmt_pr (con_id,username,tysno,title,sdate,subject,lmuser,subtype) VALUES ('req$jobPosSno','$username',$nid,'Notes',NOW(),'".addslashes($getNotesNew)."','$username','".addslashes($NotesType)."')";
				mysql_query($ique,$db);
			}
		}
		/*
		[#874210] Active Assignment Showing Needs Approval on Candidate
		Don't Remove this code.
		Use Case:
			Place a Employee/Candidate on a job order without shift,
			In Accounting >> Assignment, the assignment status will be in Need Approval,
			Now open the assignment and add shift to that assignment and click on Save.
			Now in Assignment Grid click on (Update Status) or Open Assignment click on Approve.
			In this Use Case in Manage Submission Screen >> Placement Status >> still showing as Assignment - Need Approval.	
			
			Code added by SARANESH AR.
		*/
		$res_upd	= "UPDATE resume_status SET status='".$managesno."' WHERE res_id='".$cand_sno."' AND req_id='".$jobPosSno."' AND seqnumber='".$fetch_place_jobs[1]."' AND shift_id= '0'";
		mysql_query($res_upd,$db);

		if ($plctShiftSno != $oldShiftSno) {
			$res_upd	= "UPDATE resume_status SET status='".$managesno."' WHERE res_id='".$cand_sno."' AND req_id='".$jobPosSno."' AND seqnumber='".$fetch_place_jobs[1]."' AND shift_id= '".$plctShiftSno."'";
			mysql_query($res_upd,$db);
		}else if ($oldShiftSno != $Update_shift_id) {
			$res_upd	= "UPDATE resume_status SET status='".$managesno."' WHERE res_id='".$cand_sno."' AND req_id='".$jobPosSno."' AND seqnumber='".$fetch_place_jobs[1]."' AND shift_id= '".$oldShiftSno."'";
			mysql_query($res_upd,$db);
		}

		/* END  */

		$res_upd	= "UPDATE resume_status SET status='".$managesno."' WHERE res_id='".$cand_sno."' AND req_id='".$jobPosSno."' AND seqnumber='".$fetch_place_jobs[1]."' AND shift_id= '".$Update_shift_id."'";
		mysql_query($res_upd,$db);
		
		$res_upd	= "UPDATE placement_jobs SET assg_status='".$candidateStatus."',offlocation='".$loc_user."',notes_cancel='".$fetch_place_jobs[2]."',e_date='".$page152[16]."', reason='".$page152[17]."', reason_id='".$reasonSno."' WHERE username='".$conusername."' and posid='".$jobPosSno."' AND candidate=concat('cand',".$cand_sno.") AND sno='".$fetch_place_jobs[0]."'";
		
		mysql_query($res_upd,$db);

		$exphrempIds 		= explode("|",$assignRate_id);
		$expDefaultDyna		= explode("^DefaultRates^",$assignment_mulrates);

		$mode_rate_type 	= "hrcon";
		$type_order_id 		= $exphrempIds[0];
		$ratesObj->defaultRatesInsertion($expDefaultDyna[1],$in_ratesCon);
		$ratesObj->multipleRatesAsgnInsertion($expDefaultDyna[0]);


		$tmpEDate 		= explode("-",$page152[16]);
		$assignment_e_date 	= $tmpEDate[2]."-".sprintf("%02d",$tmpEDate[0])."-".sprintf("%02d",$tmpEDate[1]);
		$assignment_e_date 	= date("Y-m-d",strtotime($assignment_e_date));

		//updating the employee time frame slots with busy based on the time slots
		if(($sm_status == 'approve' || $sm_status == 'active') && $schedule_display == 'NEW')
		{
			if($jotype == "Direct")
			{
				if($hcloseasgn != "Y")
				{
					$get_assgn_tf_sql 	= "SELECT	DATE_FORMAT(shift_date,'%m/%d/%Y'),
												shift_starttime,
												shift_endtime,
												event_no,
												event_group_no,
												shift_status,
												sm_sno,
												no_of_positions
										FROM  hrconjob_sm_timeslots
										WHERE pid = '".$hr_insert_id."' AND shift_date >= '".$assignment_e_date."'
										ORDER BY shift_date ASC								
										";
					
					$get_assgn_tf_res	= mysql_query($get_assgn_tf_sql, $db);
					if (mysql_num_rows($get_assgn_tf_res) > 0) 
					{
						while ($row	= mysql_fetch_array($get_assgn_tf_res)) 
						{
							$seldateval = $row[0];			

							//convert into minutes					
							$fromTF 	= $objSchSchedules->getMinutesFrmDateTime($row[1]);
							$toTF 		= $objSchSchedules->getMinutesFrmDateTime($row[2]);
							$recNo		= $row[3];
							$slotGrpNo	= $row[4];
							$shiftStatus	= $row[5];
							$shiftNameSno	= $row[6];
							$shiftPosNum	= $row[7];
							
							$shiftSetupDetails = $objSchSchedules->getShiftNameColorBySno($shiftNameSno);
							list($shiftName,$shiftColor) = explode("|",$shiftSetupDetails);

							$timeslotvalues .= $seldateval."^".$fromTF."^".$toTF."^".$recNo."^".$slotGrpNo."^".$shiftStatus."^".$shiftName."^".$shiftColor."^".$shiftNumPos."|";
						}
						$timeslotvalues = substr($timeslotvalues,0,strlen($timeslotvalues)-1);
					}
					
					$objScheduleDetails->updAvailableEmpTimeSlots($username, $conusername, $timeslotvalues, $assg_pusername,$assignment_e_date);
					//Dumping data into Candidate Shift Schedule from Employee Shift Schedule from the end date to future dates only.
					$objScheduleDetails->deleteNInsertDataIntoCandidateFromEmployeeScheduleTable($emp_sno_val, $conusername,$assignment_e_date);
				}				
			}
			else
			{
				if ($smTimeslotChangedflag == "Y") {
					$objScheduleDetails->insCandEmpTimeSlots($conusername,"hrcon_sm_timeslots",$assg_pusername,$timeslotvalues,$username);
				}
				if ($smDeletedTimeslot !="") {
					$objScheduleDetails->updAvailableEmpForDeletedTimeSlots($username, $conusername, $smDeletedTimeslot, $assg_pusername);
				}
				$objScheduleDetails->updBusyEmpTimeSlots($username, $conusername, $timeslotvalues, $assg_pusername);
				//Dumping data into Candidate Shift Schedule from Employee Shift Schedule from the last date to future dates only.
				$previousDate = date("Y-m-d",strtotime("-2 months",strtotime(date("Y-m-d"))));
				$objScheduleDetails->deleteNInsertDataIntoCandidateFromEmployeeScheduleTable($emp_sno_val, $conusername,$previousDate);

				// If shift type is Perdiem then checking any Cancel and Re-assigned shift are there for the assignment then making then available from Busy status.
				if ($shift_type == "perdiem") {
					$hrmPerdiemShiftSchObj->oldShiftStrAry = array();
					$hrmPerdiemShiftSchObj->prepareCancelReassignShiftStrFromPerdiemSelQry($assg_pusername,$conusername);
					$timeSlotsData = implode("|", $hrmPerdiemShiftSchObj->oldShiftStrAry);
					if ($timeSlotsData !="") {
						$objScheduleDetails->updAvailableEmpForDeletedTimeSlots($username, $conusername, $timeSlotsData, $assg_pusername);
					}
				}
			}
		}
		else if(($sm_status == 'cancel' || $sm_status == 'closed') && $schedule_display == 'NEW')
		{
			if ($shift_type == "perdiem") {
				$select = "SELECT hpss.shift_startdate AS startDate,hpss.shift_starttime AS startTime,
				hpss.shift_enddate AS endDate,hpss.shift_endtime AS endTime,hpss.split_shift AS splitShift,
				hpss.no_of_shift_position AS noOfShifts,hpss.shift_id AS shiftSno,ss.shiftname AS shiftName,
				ss.shiftcolor AS shiftColor
		 		FROM hrconjob_perdiem_shift_sch hpss 
		 		LEFT JOIN shift_setup ss ON (ss.sno = hpss.shift_id)
		 		WHERE hpss.pusername='".$assg_pusername."' AND hpss.shift_startdate >= '".$assignment_e_date."' 
		 		ORDER BY hpss.shift_startdate ASC ";
				$resultSel = mysql_query($select,$db);
				while ($rowSel = mysql_fetch_array($resultSel)) {
					$hrmPerdiemShiftSchObj->prepareOldShiftStrFromPerdiemSelQry($rowSel,$conusername);
				}

				$timeslotvalues = implode("|", $hrmPerdiemShiftSchObj->oldShiftStrAry);
			}else{
				$get_assgn_tf_sql 	= "SELECT	DATE_FORMAT(shift_date,'%m/%d/%Y'),
													shift_starttime,
													shift_endtime,
													event_no,
													event_group_no,
													shift_status,
													sm_sno,
													no_of_positions
											FROM  hrconjob_sm_timeslots
											WHERE pid = '".$hr_insert_id."' AND shift_date >= '".$assignment_e_date."'
											ORDER BY shift_date ASC								
											";
						
				$get_assgn_tf_res	= mysql_query($get_assgn_tf_sql, $db);
				if (mysql_num_rows($get_assgn_tf_res) > 0) 
				{
					while ($row	= mysql_fetch_array($get_assgn_tf_res)) 
					{
						$seldateval = $row[0];			

						//convert into minutes					
						$fromTF 	= $objSchSchedules->getMinutesFrmDateTime($row[1]);
						$toTF 		= $objSchSchedules->getMinutesFrmDateTime($row[2]);
						$recNo		= $row[3];
						$slotGrpNo	= $row[4];
						$shiftStatus	= $row[5];
						$shiftNameSno	= $row[6];
						$shiftPosNum	= $row[7];
						
						$shiftSetupDetails = $objSchSchedules->getShiftNameColorBySno($shiftNameSno);
						list($shiftName,$shiftColor) = explode("|",$shiftSetupDetails);

						$timeslotvalues .= $seldateval."^".$fromTF."^".$toTF."^".$recNo."^".$slotGrpNo."^".$shiftStatus."^".$shiftName."^".$shiftColor."^".$shiftNumPos."|";
					}
					$timeslotvalues = substr($timeslotvalues,0,strlen($timeslotvalues)-1);
				}
			}
			$objScheduleDetails->updAvailableEmpTimeSlots($username, $conusername, $timeslotvalues, $assg_pusername,$assignment_e_date);
			//Dumping data into Candidate Shift Schedule from Employee Shift Schedule from the end date to future dates only.			
			$objScheduleDetails->deleteNInsertDataIntoCandidateFromEmployeeScheduleTable($emp_sno_val, $conusername,$assignment_e_date);

			// If shift type is Perdiem then checking any Cancel and Re-assigned shift are there for the assignment then making then available from Busy status.
			if ($shift_type == "perdiem") {
				$hrmPerdiemShiftSchObj->oldShiftStrAry = array();
				$hrmPerdiemShiftSchObj->prepareCancelReassignShiftStrFromPerdiemSelQry($assg_pusername,$conusername);
				$timeSlotsData = implode("|", $hrmPerdiemShiftSchObj->oldShiftStrAry);
				if ($timeSlotsData !="") {
					$objScheduleDetails->updAvailableEmpForDeletedTimeSlots($username, $conusername, $timeSlotsData, $assg_pusername);
				}
			}

		}
	}

	if(PAYROLL_PROCESS_BY_MADISON == 'MADISON' && $madisonassid != '')
		UpdateAssigmentData($conusername,$madisonassid);

	if(DEFAULT_SYNCHR=="Y")
		syncHR_Data($conusername);
		
	// Vertex Payroll Add Taxes	
	//addVertexTaxesToAssignment($emp_listsno, $hdnTaxAssid, $chkState, $chkLocal, $chkCounty, $chkSchool,$hdnGeoCode,$hdnState,$hdnCounty,$hdnLocal);

	session_unregister($_SESSION["page15".$ACC_AS_SESSIONRN]);
	session_unregister($_SESSION["page215".$ACC_AS_SESSIONRN]);
	session_unregister("command");
	session_unregister("relocate");
	session_unregister("notes");
	session_unregister("resname");
	session_unregister("schdet");
	session_unregister($_SESSION["assignment_mulrates".$ACC_AS_SESSIONRN]);

	$temp_arr = explode("|",$recno);
	// This code is modified for Mapping empcon_jobs to hrcon_jobs enhancement.
	if($astatus == "closed" || $astatus == "cancel")
	{	
		$temp_arr[2] = ($org_astatus=="closed" || $astatus == "closed") ? "closed" : "cancelled";
		if($Assignment_Job_Type=="Direct"){
			if($org_astatus=="pending" || $org_astatus=="active") {
				if ($astatus == "closed") {
					$temp_arr[2] = 'closed';
				}else if ($astatus == "cancel") {
					$temp_arr[2] = 'cancelled';
				}
			}
		}
		//$temp_arr[2] = ($org_astatus=="pending" || "active") ? "closed" : "cancelled";
		$recno =  $hr_insert_id."|".$temp_arr[1]."|".$temp_arr[2]."|".$temp_arr[3];
	}
	else
	{
		$temp_arr[2] = ($org_astatus=="") ? "approved" : $temp_arr[2];
		
		//setting approved value if user clicks on approve - rajesh
		if($org_astatus == "active" && empty($savestatus))
		{
			$temp_arr[2] = "approved";
		}

		if(!empty($savestatus))
		{
			$ustatus	= "pending";
			$recno 		=  $hr_insert_id."|".$temp_arr[1]."|".$ustatus."|".$temp_arr[3];	
			
		}
		else
		{
			if($Assignment_Job_Type=="Direct")
				$recno =  $hr_insert_id."|".$temp_arr[1]."|".$ustatus."|".$temp_arr[3];	
			else
				$recno =  $hr_insert_id."|".$temp_arr[1]."|".$temp_arr[2]."|".$temp_arr[3];
		}
	}

	function addVertexTaxesToAssignment($empid,$assid, $stateTax, $localTax, $countyTax, $schoolTax,$geoCode,$state,$county,$local)
	{
		global $username,$maildb,$db,$exemptchkState,$exemptchkCounty,$exemptchkLocal,$exemptchkSchool;

		$OBJ_NET_PAYROLL = new NetPayroll("Assignment");
		$OBJ_NET_PAYROLL->setArrayData($geoCode);

		$stateArr = array();
		$countyArr = array();
		$localArr = array();
		$schArr = array();

		$stateArr = array_merge($OBJ_NET_PAYROLL->stateGENArray,$OBJ_NET_PAYROLL->stateERArray,$OBJ_NET_PAYROLL->stateEEArray);
		$countyArr = array_merge($OBJ_NET_PAYROLL->countyGENArray,$OBJ_NET_PAYROLL->countyEEArray,$OBJ_NET_PAYROLL->countyERArray);
		$localArr = array_merge($OBJ_NET_PAYROLL->localGENArray,$OBJ_NET_PAYROLL->localEEArray,$OBJ_NET_PAYROLL->localERArray);
		$schArr = $OBJ_NET_PAYROLL->schoolArray;

		if(count($stateArr) > 0)
			insertNewTaxes($stateArr,'State',$stateTax,$empid,$assid,$exemptchkState);		 

		if(count($countyArr) > 0)
			insertNewTaxes($countyArr,'County',$countyTax,$empid,$assid,$exemptchkCounty);		 

		if(count($localArr) > 0)
			insertNewTaxes($localArr,'Local',$localTax,$empid,$assid,$exemptchkLocal);		 

		if(count($schArr) > 0)
			insertNewTaxes($schArr,'School',$schoolTax,$empid,$assid,$exemptchkSchool);

		updateGeoCodeforAssignments($geoCode,$state,$county,$local,$empid,$assid,$schArr,$schoolTax);
	}
		
	function insertNewTaxes($federalArr,$type,$entityArray = array(),$empId,$assId,$entityExempt)
	{
		global $maildb,$db, $username,$hdnGeoCode,$lstLocFilSts,$txtLocStateWH,$txtLocPexempt,$txtLocPamt,$txtLocSexempt,$txtLocSamt,$chkNonResident,$lstJuriIntr;
			
		$geoList = $hdnGeoCode;
		$que="select username from emp_list where sno=".$empId;
		$res=mysql_query($que,$db);
		$row=mysql_fetch_row($res);
		$empUserName=$row[0];

		foreach($federalArr as $fedTax)
		{
			$sqlTaxCheck = "SELECT sno FROM vprt_taxhan WHERE geo='".$fedTax->GEO."' AND schdist='".$fedTax->SCHDIST."' AND startdate='".$fedTax->START_DATE."' AND stopdate='".$fedTax->STOP_DATE."' AND taxid=".$fedTax->TAXID;
			$resTaxCheck = mysql_query($sqlTaxCheck,$db);
			$rowTaxCheck = mysql_fetch_row($resTaxCheck);
				
			if(mysql_num_rows($resTaxCheck) <= 0)
			{
				$sqlTax = "INSERT INTO vprt_taxhan ( taxid, geo, schdist, taxname, startdate, stopdate, taxtype) VALUES ('".$fedTax->TAXID."', '".$fedTax->GEO."', '".$fedTax->SCHDIST."', '".$fedTax->TAXNAME."', '".$fedTax->START_DATE."', '".$fedTax->STOP_DATE."', '".$type."')";
				mysql_query($sqlTax,$db);
				$taxID = mysql_insert_id($db);

				if(count($fedTax->FilingSatuses->Filing_Status)>0)
				{
					foreach($fedTax->FilingSatuses->Filing_Status as $fillingStatus)
					{
						$sqlFilling = "INSERT INTO vprt_valfill ( taxsno, filing_desc, filing_stat, startdate, stopdate )VALUES ( '".$taxID."', '".$fillingStatus->FILSTAT_DESC."', '".$fillingStatus->FILING_STAT."', '".$fillingStatus->START_DATE."', '".$fillingStatus->STOP_DATE."')";
						mysql_query($sqlFilling,$db);
					}					
				}
			}
			else
			{
				$taxID = $rowTaxCheck[0];
			}

			if(in_array($fedTax->TAXID.'_'.$fedTax->SCHDIST,$entityArray))
				$apply = 'Y';				
			else
				$apply = 'N';	

			if(in_array($fedTax->TAXID.'_'.$fedTax->SCHDIST,$entityExempt))
				$applyEx = 'Y';				
			else
				$applyEx = 'N';							
				
			if($assId != '')
			{
				$updQueApply = "UPDATE vprt_taxhan_emp_apply SET status='B' where assid='".$assId."' AND taxsno='".$taxID."'  AND empid='".$empUserName."'";
				mysql_query($updQueApply,$db);

				$insApply = "INSERT INTO vprt_taxhan_emp_apply (empid,assid,taxsno,apply,status,cuser,cdate,muser,mdate,exempt) VALUES ('".$empUserName."', '".$assId."','".$taxID."', '".$apply."', 'A', '".$username."', NOW(), '".$username."', NOW(),'".$applyEx."')";
				mysql_query($insApply,$db);
					
				if($type=='State' && $fedTax->TAXID=='450')
				{
					$updQueApply = "UPDATE vprt_tax_emp_us_setup SET status='B' where assid='".$assId."' AND taxsno='".$taxID."'  AND empid='".$empUserName."'";
					mysql_query($updQueApply,$db);
	
					$sqlEmpSetup = "INSERT INTO vprt_tax_emp_us_setup ( empid, assid, taxsno, fillsno, awh, pexempt, pamount, sexempt, samount, nr_cert, jur_int_treat, status , udate )VALUES ('".$empUserName."', '".$assId."', '".$taxID."', '".$lstLocFilSts."', '".$txtLocStateWH."', '".$txtLocPexempt."', '".$txtLocPamt."', '".$txtLocSexempt."', '".$txtLocSamt."', '".$chkNonResident."', '".$lstJuriIntr."', 'A', NOW())";
					mysql_query($sqlEmpSetup,$db);
				}
			}
		}
	}
		
	function updateGeoCodeforAssignments($geoVal,$stateVal='',$countyVal='',$localVal='',$empid,$assid,$schArr,$schoolTax)
	{
		global $maildb,$db;

		$schdVal='';
		if($assid != '') 
		{
			foreach($schArr as $schTax)
			{
				if(in_array($schTax->TAXID."_".$schTax->SCHDIST,$schoolTax))
					$schdVal = $schTax->SCHDIST;
			}

			$updateHrconJobs = "UPDATE hrcon_jobs SET vprt_GeoCode = '".trim($geoVal)."', vprt_State = '".trim($stateVal)."', vprt_County = '".trim($countyVal)."', vprt_Local = '".trim($localVal)."', vprt_schdist = '".trim($schdVal)."' WHERE ustatus!='backup'  AND pusername ='".$assid."'";
			mysql_query($updateHrconJobs,$db);

		}
	}
	
unset($_SESSION['editShiftTotalArrayValues'.$candrn]);
unset($_SESSION['editShiftTotalDateArrayValues'.$candrn]);
unset($_SESSION['editShiftTotalDateSlotGrpArrayValues'.$candrn]);
unset($_SESSION['editShiftSchDatesTotalArrayValues'.$candrn]);
unset($_SESSION['editShiftSchDatesTotalArrayValues']);
unset($_SESSION['sm_form_data_array'.$candrn]);
unset($_SESSION['sm_form_data_array']);
unset($_SESSION['deletedAssignPerdiemShiftSch'.$candrn]);

?>
<html>
<head>
<title>Update Assignment</title>
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

<style>
.timegrid{ width:2.042%;}
@media screen\0 {	
	/* IE only override */
.summaryform-formelement{ height:18px; font-size:11px !important; }
a.crm-select-link:link{ font-size:11px !important; }
a.edit-list:link{ font-size:10px !important;}
.summaryform-bold-close-title{ font-size:9px !important;}
.center-body { text-align:left !important;}
.crmsummary-jocomp-table td{ font-size:9px ; text-align:left !important;}
.summaryform-nonboldsub-title{ font-size:9px}
#smdatetable{ font-size:11px !important;}
.summaryform-formelement{ text-align:left !important; vertical-align:middle}
.crmsummary-content-title{ text-align:left !important}
.crmsummary-edit-table td{ text-align:left !important}
.summaryform-bold-title{ font-size:10px !important;}
.summaryform-nonboldsub-title{ font-size:10px !important;}
}
@media screen and (-webkit-min-device-pixel-ratio:0) { 
    /* Safari only override */
    ::i-block-chrome,.timegrid { width:2.07%; }
	::i-block-chrome,.timehead { width:4%;}   
}
</style>
</head>

<script language=javascript>
document.onkeydown = function()
{
	if(window.event && window.event.keyCode == 116) 
		window.event.keyCode = 505;

	if(window.event && window.event.keyCode == 505) 
		return false; 
}
</script>
<body>
</body>
</html>

<?php
if($hr_insert_id=="") 
{
	echo "<script>alert('We are unable to process your request. Please try again. If the problem persists, please contact Akken Customer Success Team');window.close();</script>";
}
else
{
	echo "<script>
	if(window.opener)
	{
		try 
		{
			window.opener.doGridSearch('search');
			location.href = 'getnewconreg.php?command=emphire&addr=new&rec=$recno&assignid=$madisonassid';
			
		}
		catch(e)
		{
			if('".$source."' == 'timesheet')
			{
				//window.opener.getMultipleRateTimesheetAssgn('".$hdnAssid."', '".$rowid."');
				window.opener.getNewEmployeeAlertTS('1','');
			}
			else
			{
				var parwin = window.opener.location.href;
				window.opener.location.href = parwin;
			}
			window.close();
		}
		window.focus();
	}
	</script>	
	";
}
?>
