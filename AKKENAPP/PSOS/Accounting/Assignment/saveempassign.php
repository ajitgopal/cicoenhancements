<?php    
	$page115=$page15; 
	require("global.inc");
        
        $worksiteCode = $worksitecode;
		$licode = $wali;
	// assign of updated username start here -- Jyothi 24/10/2014
	// When we create a "New User" from user Management already open assignment username name is not updating. For this reason we are fecthing the sno of the emp_list and then fetching the username
    if ($flag_reassign =="Yes") {
    	$selectedEmpsno = $re_assign_selectedEmpsno;
		$astatus = 'active';
    }
	$query = "Select username from emp_list where sno=".$selectedEmpsno;
	$result_query = mysql_query($query,$db);
	$result = mysql_fetch_row($result_query);
	$empname = $result[0];
	// assign of updated username end here
	$tempempname=$empname;
	require_once('waitMsg.inc'); //added by Swapna for Processing please wait message.
	require("dispfunc.php");
	require_once($akken_psos_include_path.'commonfuns.inc');
	require_once($akken_psos_include_path.'defaultTaxes.php');	
	require_once("multipleRatesClass.php");
	$ratesObj=new multiplerates();
	// Placement Notification Enhancement
	require_once('class.Notifications.inc');
	$placementNotification = new Notifications();
	// END
	$page15=$page115;
	$empname=$tempempname;

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
	//echo $daypay.'===='.$federal_payroll.'===='.$nonfederal_payroll.'===='.$contractid.'===='.$classification;
	//exit();
	
	/*
		Perdiem Shift Scheduling Class file
	*/
	require_once('perdiem_shift_sch/Model/class.hrm_perdiem_sch_db.php');
	$hrmPerdiemShiftSchObj = new HRMPerdiemShift();

	/* including shift schedule class file for converting Minutes to DateTime / DateTime to Minutes */
	require_once('shift_schedule/class.schedules.php');
	$objSchSchedules	= new Schedules();
	
	require_once('shift_schedule/hrm_schedule_db.php');
	$objScheduleDetails	= new EmployeeSchedule();	
	if ($flag_reassign =="Yes") {
		$reassign_sm_timeslots_val = array();
		if ($sm_form_grp_ary_val !="" && $sm_form_grp_ary_val !="0") {
			$grp_ary_vals = explode(",", $sm_form_grp_ary_val);
			foreach ($grp_ary_vals as $grp_value) {
				$sm_values = $_SESSION['sm_form_data_grp_array'.$candrn][$grp_value];
				foreach ($sm_values as $grp_ary_value) {
					array_push($reassign_sm_timeslots_val, $grp_ary_value);
				}				
			}
		}else{
			$smarykeyval = explode(",",$sm_form_data);
			foreach ($smarykeyval as $keyval) {
				$sm_values = $_SESSION['sm_form_data_array'.$candrn][$keyval];
				array_push($reassign_sm_timeslots_val, $sm_values);
			}
		}
		
    	$timeslotvalues = implode("|",$reassign_sm_timeslots_val);	
		$sm_form_data = implode("|",$reassign_sm_timeslots_val);
    }else{
    	$timeslotvalues = implode("|",$_SESSION['sm_form_data_array'.$candrn]);	
		$sm_form_data = implode("|",$_SESSION['sm_form_data_array'.$candrn]);
    }
	$timeslotvalues = $sm_form_data;

	// Getting the Shift Name Sno  for this table shift_setup >> sno
	$shift_id = '0';
	$sm_plcmnt_array=explode("|",$sm_form_data);
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
	if ($_REQUEST['shift_type'] == "perdiem") {
		if ($copyasign == "yes" || $flag_reassign =="Yes") {
			$shift_id = $_REQUEST['perdiem_shiftid'];
		}else{
			$shift_id = $_REQUEST['sm_active_sno'];	
		}
		
	}
	//Get the shift scheduling old/new display status
	$schedule_display = $sm_enabled_option;
	
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
        
	/*
	$hcloseasgn=$getCloseAsgn;
	$henddate=$getCloseAsgnDate;
	$hterminate=$getTerminate;
	$hterdate=$getTerminateDate;
	*/

	$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todate=date("m/d/Y",$thisday);

	if($page11[0] != "")
		$disp_name=$page11[0];
	if($page11[1] != "")
		$disp_name.=" ".$page11[1];
	if($page11[2] != "")
		$disp_name.=" ".$page11[2];

	//for textbox values in comission
	$commissionVisitedArray	= array();
	$commval1		= "";
	$ratetype1		= "";
	$paytype1		= "";
	$commIndex		= 0;
	$commKey		= "";
	$roleName1		= "";
	$roleOverWrite1		= "";
	$roleEDisable1		= "";
	$arrayCount		= array();

	$emp_val		= explode(',',$empvalues);

	require_once("assignmentSession.php");

	for($k=0; $k<count($emp_val); $k++) {
		if($emp_val[$k] != 'noval') {
			$commvalues	= explode("^",$emp_val[$k]);
			$emp_values[$k]	= $commvalues[0];
		}
	}

	if(count($emp_values) > 0) {
		$counter	= 0;
		foreach($emp_values as $key=>$keyValue) {
            		$commKey	= $keyValue;

			if(in_array($keyValue,$commissionVisitedArray)) {
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
				$commval1	=$commval[$commKey][$indexVal];
				$ratetype1	=$ratetype[$commKey][$indexVal];
				$paytype1	=$paytype[$commKey][$indexVal];
				$roleName1	=$roleName[$commKey][$indexVal];
				$roleOverWrite1	=$roleOverWrite[$commKey][$indexVal];
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

	$commissionValues	= $commval1."|".$ratetype1."|".$paytype1."|".$assignstatus."|".$roleName1."|".$roleOverWrite1."|".$roleEDisable1;
	$_SESSION['commission_session']	= $commissionValues;
	
	$assign_mulrates = getAssignmentSession($rateType,$billableR,$mulpayRateTxt,$payratePeriod,$payrateCurrency,$mulbillRateTxt,$billratePeriod,$billrateCurrency,$taxableR,$mulRatesVal);
	$assignment_mulrates = $assign_mulrates; 

	$page151=$page15;
	$elements=explode("|",$page151);

	$jobtypeIsDirect="";

	$que1="select sno,name,type from manage where sno='".$elements[2]."'";
	$res=mysql_query($que1,$db);
	$rs=mysql_fetch_array($res);
	$typeofassign=$rs[1];

	if($rs[1]=="Direct" || ($rs[1]=="Temp/Contract to Direct" && $astatus=="closed"))
		$jobtypeIsDirect="YES";

	//For schedule information
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
			{
				$schdet1 .="|^AkkenSplit^||^AkkSplitCol^||^AkkSplitCol^|$AddweakInt|^AkkSplitCol^|".$$Timefrom."|^AkkSplitCol^|".$$TimeTo;
			}
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
		////////////End of the Update//////////////////////////////////////////////////////
	}
	// Assignment Shift Name/ Time when shift scheduling disabled
	$shift_st_time = '';
	$shift_et_time = '';
	if(SHIFT_SCHEDULING_ENABLED=='N')
	{
		// Shift details for Assignment
		$shift_det  	= $new_shift_name;
		$shift_info 	= explode("|", $shift_det);
		$shift_id 		= $shift_info[0];
		$shift_st_time 	= $shift_time_from;
		$shift_et_time 	= $shift_time_to;
	}

	 /* TLS-01202018 */

	
	$schdet=$schdet1;
	session_update("schdet");
	
	$sql_loc="SELECT contact_manage.serial_no FROM contact_manage, hrcon_compen WHERE contact_manage.status != 'BP' AND hrcon_compen.location = contact_manage.serial_no AND hrcon_compen.username = '".$username."' AND hrcon_compen.ustatus = 'active'";
	$res_loc=mysql_query($sql_loc,$db);
	$fetch_loc=mysql_fetch_row($res_loc);
	$loc_user=$fetch_loc[0];

	$que="select sno,username from emp_list where username = '".$empname."'";
	$res=mysql_query($que,$db);
	$row=mysql_fetch_row($res);
	$emp_sno_val = $row[0];

	$conusername=$empname;
	$assg_pusername=get_Assginment_Seq();
	$classid=displayDepartmentName($page1511[88],true);//funtion to get the classid from department fun in commonfuns.inc

	$hr_insert_id = "";
	
	//echo "Timesheet Pref: ".$assignment_timesheet_layout_preference; exit;

	$assignRatesId = createNewDataForHR($page15,$page215,$conusername,$db,$assgStage,$astatus,$schdet,$assg_pusername, $timeslotvalues, $objScheduleDetails,$burdenTypeDetails,$burdenItemDetails,$schedule_display,$bill_burdenTypeDetails,$bill_burdenItemDetails,$shift_id,$studentlistids,$worksiteCode,$smTimeslotChangedflag,$shift_st_time,$shift_et_time,$licode,$daypay,$federal_payroll,$nonfederal_payroll,$contractid,$classification,$assignment_timesheet_layout_preference);

	if($assignRatesId!="")
	{
		$shiftSno = $shift_id;
		$shift_rates_data = "sm_rates_".$shiftSno;
		$exphrempIds =  explode("|",$assignRatesId);
		$expDefaultDyna=explode("^DefaultRates^",$assignment_mulrates);
		$hr_insert_id = $exphrempIds[0];
		if($$shift_rates_data!="|" && $$shift_rates_data!=""){

			$mode_rate_type = "hrcon";			
			$type_order_id 	= $exphrempIds[0];
			$objSchSchedules->updateShiftRates($type_order_id,$$shift_rates_data,$shiftSno,$mode_rate_type);

		}else{
			$mode_rate_type = "hrcon";	
			$hr_insert_id = $exphrempIds[0];
			$type_order_id = $exphrempIds[0];
			$ratesObj->defaultRatesInsertion($expDefaultDyna[1],$in_ratesCon);
			$ratesObj->multipleRatesAsgnInsertion($expDefaultDyna[0]);
		}
		// Placement Notification Enhancement
		if($astatus == "active" && $exphrempIds[1] != "")
		{	
			$placementNotification->sendAssignmentNotifications($exphrempIds[1]);
		}
		//END
	}

	$jtype_qry="SELECT jtype,DATE_ADD(MAX(DATE_FORMAT(STR_TO_DATE(e_date,'%m-%d-%Y'),'%Y-%m-%d')),INTERVAL 1 DAY),MAX(DATE_FORMAT(STR_TO_DATE(if(e_date='0-0-0','00-00-0000',e_date),'%m-%d-%Y'),'%Y-%m-%d')),DATE_ADD(MAX(DATE_FORMAT(exp_edate,'%Y-%m-%d')),INTERVAL 1 DAY),MAX(DATE_FORMAT(exp_edate,'%Y-%m-%d')) FROM hrcon_jobs WHERE username = '".$empname."' AND ustatus IN ('active','pending') group by username";
	$jtype_res = mysql_query($jtype_qry, $db);
	$jtype_row = mysql_fetch_row($jtype_res);

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

	$aass_qry="SELECT count(1) FROM hrcon_jobs WHERE username = '$empname' AND ustatus = 'active' AND jtype='OP'";
	$aass_res = mysql_query($aass_qry,$db);
	$aass_row = mysql_fetch_row($aass_res);
	if($aass_row[0]>0)
		$cand_status = getManageSno("On Assignment","candstatus");
	else
		$cand_status = getManageSno("Actively Searching","candstatus");

	//update the candidate avail date
	$quecan="select username from candidate_list where candid=concat('emp','".$row[0]."')";
	$rscan=mysql_query($quecan,$db);
	$rescan=mysql_fetch_array($rscan);
	$cand_username=$rescan[0];

	$clque="UPDATE candidate_list SET cl_status='$cand_status',ctype='Employee' WHERE username='".$cand_username."'";
	mysql_query($clque,$db);

	$sql_profque = "UPDATE candidate_prof set availsdate='".$avail."' WHERE username='".$cand_username."'";
	mysql_query($sql_profque,$db);

	if($typeofassign=='Internal Direct')
	{
		$que="UPDATE candidate_prof SET availsdate='inactive' WHERE username='".$cand_username."'";
		mysql_query($que,$db);	
	}

	if($jobtypeIsDirect=="YES")
	{
		$emp_listsno = $row[0];

		if($hcloseasgn=="Y")
			closeDirectJobAssignments($henddate);

		if($hterminate=="Y")
		{
			$sterdate = explode("-",$hterdate);
			$tdate = $sterdate[2]."-".$sterdate[0]."-".$sterdate[1];

			$tque="SELECT empterminated, tdate FROM emp_list WHERE sno=$emp_listsno";
			$tres=mysql_query($tque,$db);
			$trow=mysql_fetch_row($tres);

			if($trow[0]=="N" || $trow[1]=="")
			{
				$que="update emp_list set empterminated='Y',tdate='$tdate',lstatus='ACTIVE',show_crm='Y' where sno=$emp_listsno";
				mysql_query($que,$db);

				if($hterdate==date("m-d-Y"))
				{
					removeJobBoardIds($emp_listsno);

					$cand_prof="UPDATE candidate_list a,candidate_prof b set b.availsdate='inactive',a.status='ACTIVE' WHERE a.username=b.username AND a.username='".$cand_username."'";
					mysql_query($cand_prof,$db);

					$aass_qry="SELECT count(1) FROM hrcon_jobs WHERE username = '$empname' AND ustatus = 'active' AND jtype='OP'";
					$aass_res = mysql_query($aass_qry,$db);
					$aass_row = mysql_fetch_row($aass_res);
					if($aass_row[0]>0)
						$cand_status = getManageSno("On Assignment","candstatus");
					else
						$cand_status = getManageSno("Actively Searching","candstatus");

					$clque="UPDATE candidate_list SET cl_status='$cand_status',ctype='Employee' WHERE username='".$cand_username."'";
					mysql_query($clque,$db);

					$query="select username,name from emp_list where sno='".$emp_listsno."'";
					$res=mysql_query($query,$db);
					$data=mysql_fetch_row($res);

					$qued="select emailuser from EmailAcc where username='".$data[0]."'";
					$resd=mysql_query($qued,$db);
					$rowd=mysql_fetch_row($resd);

					$eaccname=$companyuser."|".$data[0];

					require("sysDBmail.inc");
					$que="update mailbox set suspended='Y' where username='".$eaccname."'";
					mysql_query($que,$sysdb);
					mysql_close($sysdb);

					$queryState="select status from users where username='".$data[0]."'";
					$rsState=mysql_query($queryState,$db);
					$rowState = mysql_fetch_row($rsState);
					$rowStateCount = mysql_num_rows($rsState);
                                        
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
						$queUserStatus="select max(activated) from userstatus where username = '".$data[0]."' group by username";
						$resUserStatus=mysql_query($queUserStatus,$db);
						$rowUserStatus = mysql_fetch_row($resUserStatus);
                                                
                                                // Restrict the account deactivation for ESS user when akkupay is enabled
                                                if($restrictESSUserAccDea=="NO"){
                                                    $queUserStatus="update userstatus set deactivated = now(),deactive_user='".$username."' where activated = '".$rowUserStatus[0]."' and username = '".$data[0]."'";
                                                    mysql_query($queUserStatus,$db);
                                                }

						updateSysTotalUsers();
					}
                                         // Restrict the account deactivation for ESS user when akkupay is enabled
                                        if($restrictESSUserAccDea=="NO"){
                                            $query="update users set status='DA' where username='".$data[0]."'";
                                            mysql_query($query,$db);
                                            
                                            if($data[0]==$username)
                                            {
                                                    $sess_id=session_id();
                                                    $session_dir=ini_get("session.save_path");
                                                    unlink($session_dir."/sess_".$sess_id);
                                                    delFolder($WDOCUMENT_ROOT);
                                                    clearCookies($_COOKIE,"");
                                                    foreach($_SESSION as $key=>$value  )
                                                            unset($_SESSION[$key]);
                                                    print "<script language=javascript>window.location.href='/BSOS/Home/logout.php?comp=true';</script>";
                                            }
                                        }
                                    }
			}
		}
	}
	
	//updating the employee time frame slots with busy based on the time slots
	if($astatus == 'active' && $typeofassign != 'Direct' && $schedule_display == 'NEW')
	{
		$objScheduleDetails->updBusyEmpTimeSlots($username, $conusername, $timeslotvalues, $assg_pusername);
		//Dumping data into Candidate Shift Schedule from Employee Shift Schedule from the last date to future dates only.
		$previousDate = date("Y-m-d",strtotime("-2 months",strtotime(date("Y-m-d"))));
		$objScheduleDetails->deleteNInsertDataIntoCandidateFromEmployeeScheduleTable($emp_sno_val, $conusername,$previousDate);
	}
	
	function createNewDataForHR($page15,$page215,$conusername,$db,$assgStatus,$astatus,$schdet,$assg_pusername,$timeslotvalues,$objScheduleDetails,$burdenTypeDetails,$burdenItemDetails,$schedule_display,$bill_burdenTypeDetails,$bill_burdenItemDetails,$shift_id=NULL,$person_id=NULL,$worksiteCode=NULL,$smTimeslotChangedflag ="N",$shift_st_time,$shift_et_time,$licode,$daypay,$federal_payroll,$nonfederal_payroll,$contractid,$classification,$assignment_timesheet_layout_preference='')
	{
		
		global $jobSno,$typeofassign,$commission_session,$loc_user,$commissionValues,$classid,$attention,$username,$from_assign_no,$from_username,$sm_form_data,$flag_reassign,$joborder_id,$noshowcheck,$noshowdescription,$candidate,$reasoncodesno;
	
		if($commission_session == "")
			$commission_session	= $commissionValues;

		$page152=explode("|",$page15);
		if ($shift_id != "" && $shift_id != "0") {
			$shift_type =  $_REQUEST['shift_type'];
		}else{
			$shift_type = "";
		}
		
		if ($page152[94] == "" || $page152[94] == "NO") {
			$page152[94] = "No";
		}

		if ($page152[95] == "" || $page152[95] == "NO") {
			$page152[95] = "No";
		}

		//To Set The Start Date And End Date For Schedule Management
		if($flag_reassign == "Yes" && $shift_type == "regular"){
			
			$sm_shift_data = array();
			if(!empty($timeslotvalues)){
				$sm_shift_data = explode('|',$timeslotvalues);
			}
			
			if(count($sm_shift_data)>0){
				$shift_dates_arr = array();
				for($j=0;$j < count($sm_shift_data);$j++)
				{
			
				// Getting the Shift Name Sno  for this table shift_setup >> sno
					
				$shift_date=explode("^",$sm_shift_data[$j]);
				//$sm_req_cnt	= count($sm_reassign_array);
				//$smVal = trim($sm_reassign_array[$j]);
				
				/*for($i=0;$i<$sm_req_cnt;$i++)
				{*/
					//$smVal = trim($sm_reassign_array[$i]);
					//$shift_date =  explode("^",$smVal);
					if(!empty($shift_date[0])){
					array_push($shift_dates_arr,$shift_date[0]);
					}
			
				//}
					usort($shift_dates_arr, "shiftdate_sort");
				//if($j>0){
					
				//}
				}
				$page152[12] = reset(str_replace('/','-', $shift_dates_arr));
				$page152[16] = end(str_replace('/','-', $shift_dates_arr));

			}

		}else if($flag_reassign == "Yes" && $shift_type == "perdiem"){
			$minMaxdateary = explode(",", $_REQUEST['sm_start_exp_end_date']);
			$page152[12] = $minMaxdateary[0];
			$page152[16] = $minMaxdateary[1];
		}
		
		if(isset($candidate) ){
			$candidate_id = $candidate;
		}else 
		{ 
			$candidate_id = "0" ;
		}
		
		if(isset($joborder_id) ){
			$posid = $joborder_id;
		}else 
		{ 
			$posid = "0" ;
		}
		
		
		
		$createdate=explode("^AKK^",$page152[59]);
		$page152[59]=$createdate[1];

		$billRateValue=explode("^^",$page152[24]);
		$payRateValue=explode("^^",$page152[25]);
		$payDet=explode("^^",$page152[29]);
		
		//To calculate the margin and markup..
		$SendString=$payDet[0]."|".$payRateValue[0]."|".$billRateValue[0]."|".$page152[26]."|".$page152[27]."|".$page152[28]."|".$page152[92];
		if(RATE_CALCULATOR=='N'){
                $RetString=comm_calculate_Active($SendString);
		$RetString_Array=explode("|",$RetString);
		$payRateValue[0]=$RetString_Array[1];
		$billRateValue[0]=$RetString_Array[2];
		$page152[26]=$RetString_Array[3];
		$page152[27]=$RetString_Array[4];
		$page152[28]=$RetString_Array[5];
                }
		if($typeofassign=='Direct')
			closeDirectJobAssignments();

		if($astatus == 'active')
		{
			$todayDate = date("m-d-Y");
			$ustatus="active";

			if($payDet[1]=="open")
				$payOpenVal='Y';
			else if($payDet[1]=="rate")
				$payOpenVal='N';

			if($payDet[2]=="open")
				$billOpenVal='Y';
			else if($payDet[2]=="rate")
				$billOpenVal='N';

			//If job type is internal direct or direct no need to insert per diem values in database.
			$Assignment_Job_Type = getManage($page152[2]); //To get the job type
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

			$page152[79] = ($page152[76] == 'Y') ? $page152[79] : '0.00';//Checking whether it is billable or not--Raj

			
			$candrn = $_REQUEST['candrn'];
			
			$qs="'','".$conusername."','".$page152[2]."','".$page152[3]."','".$page152[4]."','".$page152[5]."','".$page152[6]."','".$page152[7]."','".$page152[8]."','".$page152[9]."','".$page152[10]."','".$page152[11]."','".$page152[12]."','".$page152[13]."','".$page152[14]."','".$page152[15]."','".$page152[16]."','".$page152[17]."','".$page152[18]."','".$page152[19]."','".$page152[20]."','".$page152[21]."','".$billRateValue[0]."','".$billRateValue[2]."','".$billRateValue[1]."','".$payRateValue[0]."','".$payRateValue[2]."','".$payRateValue[1]."','".$page152[30]."','".$page152[31]."','".$page152[32]."','".$page152[33]."','".$page152[34]."','".$page152[35]."','".$page152[36]."','".$page152[37]."','".$page152[38]."','".$page152[39]."','".$page152[40]."','".$page152[41]."','".addslashes($page152[42])."','".addslashes($page152[43])."','".$page152[44]."','".$page152[45]."','".$page152[46]."','".$page152[47]."','{$ustatus}',now(),'".$page152[26]."','".$page152[27]."','".$page152[28]."','".$payDet[0]."','".$payOpenVal."','".$billOpenVal."','".$payDet[3]."','".$payDet[4]."','".$username."','".$page152[52]."','".$page152[53]."','".$page152[54]."','".$page152[55]."','".$page152[56]."','".$page152[57]."','".$page152[58]."','".$page152[59]."','".$loc_user."','".$page152[61]."','".$page152[62]."','".$page152[63]."','".$page152[64]."',NOW(),'".$page152[66]."','".addslashes($page152[67])."','".addslashes($page152[68])."','".$page152[69]."','".$username."',NOW(),'".$page152[70]."','".$page152[71]."','".$page152[72]."','".$page152[73]."','".$page152[74]."','".$page152[75]."','".$page152[76]."','".$page152[77]."','".$page152[79]."','".$page152[80]."','".$assg_pusername."','".$assg_pusername."','".$page152[88]."','".$classid."','".$attention."','".$page152[90]."','".$page152[91]."','".$schedule_display."','".$page152[92]."','".$shift_id."','".$worksiteCode."','".$candidate_id."','".$posid."','".$shift_st_time."','".$shift_et_time."','".$shift_type."','".$daypay."','".$licode."' ,'".$federal_payroll."','".$nonfederal_payroll."','".$contractid."','".$classification."','".$assignment_timesheet_layout_preference."' ";
		 	$query="insert into hrcon_jobs (sno,username,jotype,catid,project,refcode,posstatus,vendor,contact,client,manager,endclient,s_date,exp_edate,posworkhr,iterms,e_date,reason,hired_date,rate,rateper,rateperiod,bamount,bcurrency,bperiod,pamount,pcurrency,pperiod,pterms,otrate,ot_currency,ot_period,placement_fee,placement_curr,jtype,bill_contact,bill_address,wcomp_code,imethod,tsapp,bill_req,service_terms,hire_req,notes,addinfo,avg_interview,ustatus,udate,burden,margin,markup,calctype,prateopen,brateopen,prateopen_amt,brateopen_amt,owner,otprate_amt,otprate_period,otprate_curr,otbrate_amt,otbrate_period,otbrate_curr,payrollpid,cdate,offlocation,double_prate_amt,double_prate_period,double_prate_curr,double_brate_amt,double_brate_period,double_brate_curr,po_num,department,job_loc_tax,muser,mdate,date_placed,diem_lodging,diem_mie,diem_total,diem_currency,diem_period,diem_billable,diem_taxable,diem_billrate,madison_order_id,pusername,assign_no,deptid,classid,attention,corp_code, industryid,schedule_display,bill_burden,shiftid,worksite_code,candidate,posid,starthour,endhour,shift_type,daypay,licode,federal_payroll, nonfederal_payroll,contractid,classification,ts_layout_pref) values(".$qs.")";
			$hrres=mysql_query($query,$db);
			if($hrres)
			{
				$hrcon_jobs_sno=mysql_insert_id($db);
				
				//Update Worksite_id to hrcon_jobs from contact_manage--Mohan
				$cm_qry = "select serial_no from contact_manage where loccode='".$worksiteCode."'";
				$rescm_qry = mysql_query($cm_qry,$db);
				$worksite_id=mysql_fetch_row($rescm_qry);
				$updateWorksiteid = "UPDATE hrcon_jobs SET worksite_id = '".$worksite_id[0]."' WHERE sno='".$hrcon_jobs_sno."'";
				mysql_query($updateWorksiteid,$db);
				
				if ($_REQUEST['copyasign'] == 'yes') {
					$updateCopyAsignid = "UPDATE hrcon_jobs SET copy_asignid='".$_REQUEST['hdnAssid']."' WHERE sno ='".$hrcon_jobs_sno."'";
					mysql_query($updateCopyAsignid,$db);
				}
                                saveBurdenDetails($burdenTypeDetails,$burdenItemDetails,'hrcon_burden_details','hrcon_jobs_sno','insert',$hrcon_jobs_sno);
                                saveBillBurdenDetails($bill_burdenTypeDetails,$bill_burdenItemDetails,'hrcon_burden_details','hrcon_jobs_sno','insert',$hrcon_jobs_sno);
				$jobSno=$hrcon_jobs_sno;
				if($person_id !=''){
					$personids = explode(',',$person_id);
					foreach ($personids as $personid) {
						if (($hrcon_jobs_sno !="" && $hrcon_jobs_sno !="0") && ($personid !="" && $personid !="0")) {
							$insertPersons = "INSERT INTO persons_assignment (asgnid,asgn_mode,person_id,cuser,cdate,muser,mdate) VALUES('".$hrcon_jobs_sno."','hrcon','".$personid."','".$username."',NOW(),'".$username."',NOW())";
							mysql_query($insertPersons,$db);
						}
					}
				}
				if ($shift_type == "perdiem" && $schedule_display == "NEW") {

					$selPerdiemDateArry = $_SESSION['newAssignPerdiemShiftPagination'.$candrn];
					$perdiemSessionName = 'newAssignPerdiemShiftSch'.$candrn;
					
					$hrmPerdiemShiftSchObj1 = new HRMPerdiemShift();
					$copyasign = 'no';
					$copyPusername='';
					if (isset($_REQUEST['copyasign'])) {
						$copyasign = $_REQUEST['copyasign'];
					}						
					if ($copyasign == 'yes') {
						$copyPusername = $_REQUEST['hdnAssid'];
					}

					if ($flag_reassign == "Yes") {
						$selDivIdsary = explode(",", $_REQUEST['re_assign_selDivIds']);
						$oldtimeslotvalues = $hrmPerdiemShiftSchObj1->insReAssignHrconJobPerdiemShift($hrcon_jobs_sno,$from_assign_no,$assg_pusername,$candrn,$username,$conusername,$from_username,$noshowcheck,$noshowdescription,$candidate_id,$reasoncodesno,$selDivIdsary);
						
						$objScheduleDetails->updAvailableEmpForDeletedTimeSlots($username,$from_username,$oldtimeslotvalues,$from_assign_no);
						
						$objScheduleDetails->insCandEmpTimeSlots($conusername,'hrcon_sm_timeslots',$assg_pusername,$oldtimeslotvalues,$username);						
					}else{

						$oldtimeslotvalues = $hrmPerdiemShiftSchObj1->insNewHrconJobPerdiemShift($hrcon_jobs_sno,$assg_pusername,$candrn,$username,$conusername,$copyasign,$copyPusername);
						$objScheduleDetails->insCandEmpTimeSlots($conusername,'hrcon_sm_timeslots',$assg_pusername,$oldtimeslotvalues,$username);
					}
					
					// Making Employee / Candidate avaliablity as Busy 
					$objScheduleDetails->updBusyEmpTimeSlots($username, $conusername, $oldtimeslotvalues, $assg_pusername);
					//Dumping data into Candidate Shift Schedule from Employee Shift Schedule from the last date to future dates only.
					$previousDate = date("Y-m-d",strtotime("-2 months",strtotime(date("Y-m-d"))));
					$objScheduleDetails->deleteNInsertDataIntoCandidateFromEmployeeScheduleTable($emp_sno_val, $conusername,$previousDate);									
				}

				if($schedule_display == 'NEW')
				{
					$objScheduleDetails->insHrEmpConJobsTimeSlots($username,'hrconjob_sm_timeslots',$hrcon_jobs_sno,$timeslotvalues);
					
					if ($smTimeslotChangedflag == "Y") {
					
						$objScheduleDetails->insCandEmpTimeSlots($conusername,'hrcon_sm_timeslots',$assg_pusername,$timeslotvalues,$username);
					}
					
				}
				if($flag_reassign == "Yes" && $shift_type == "regular"){
					
						
					$objScheduleDetails->insReassignTimeSlots($username,'reassign_sm_timeslots',$from_assign_no,$assg_pusername,$from_username,$conusername,$timeslotvalues,$joborder_id,$noshowcheck,$noshowdescription,$candidate_id,$reasoncodesno);
				}
				
				if($schedule_display == 'NEW')
				{				
					if ($smTimeslotChangedflag == "Y") {
					
						$objScheduleDetails->insCandEmpTimeSlots($conusername,'hrcon_sm_timeslots',$assg_pusername,$timeslotvalues,$username);
					}
				}
				
				/////////////////// UDF migration to customers ///////////////////////////////////
				include_once('custom/custome_save.php');		
				$directEmpUdfObj = new userDefinedManuplations();
				//Changed to store the hrconjobs->sno in cdf assignments table rec_id - vilas.B
				$directEmpUdfObj->insertUserDefinedData($hrcon_jobs_sno, 7);
				/////////////////////////////////////////////////////////////////////////////////

				$contactSno		= $hrcon_jobs_sno."|";
				$assignRatesValue 	= $contactSno;

				setDefaultEntityTaxes('Assignment', $page152[9], 0, 0, $conusername, $assg_pusername);

				$assignment_array=array("modulename" => "HR->Assignments", "pusername" => $assg_pusername, "userid" => $conusername, "contactsno" => $contactSno, "invapproved" => 'active');
				$tabappiont_ass_id=insertAssignmentSchedule($assignment_array);

				$schdet1 = explode("|^AkkenSplit^|",$schdet);
				$asd =count($schdet1);
				for ($i=1;$i<$asd;$i++)
				{
					$newarray = explode("|^AkkSplitCol^|",$schdet1[$i]);
					$InsdateArr=explode("/",$newarray[1]);
					$Insdate=$InsdateArr[2]."-".$InsdateArr[0]."-".$InsdateArr[1]." 00:00:00";
					
					if($schedule_display == 'OLD')
					{

						$sheQry="insert into empcon_tab (sno,tabsno,starthour,endhour,wdays,sch_date,coltype) VALUES ('','".$tabappiont_ass_id."','".$newarray[3]."','".$newarray[4]."','".$newarray[2]."','".$Insdate."','assign')";
						mysql_query($sheQry,$db) or die(mysql_error());

						$sheQry="insert into hrcon_tab (sno,tabsno,starthour,endhour,wdays,sch_date,coltype) VALUES ('','".$tabappiont_ass_id."','".$newarray[3]."','".$newarray[4]."','".$newarray[2]."','".$Insdate."','assign')";
						mysql_query($sheQry,$db) or die(mysql_error());
					}

				}
			}
		
		//Added to update emp_list jtype newly added column	
	            $equery  =  "UPDATE emp_list e, hrcon_jobs hj SET e.emp_jtype='".$page152[36]."', e.cur_project = '".$page152[4]."', e.rate = '".$payRateValue[0]."'  WHERE e.username=hj.username AND hj.sno ='".$hrcon_jobs_sno."' ";
		    mysql_query($equery,$db);
		}
		else
		{
			// if job status is closed
			$ustatus="closed";

			$payDet=explode("^^",$page152[29]);

			if($payDet[1]=="open")
				$payOpenVal='Y';
			else if($payDet[1]=="rate")
				$payOpenVal='N';

			if($payDet[2]=="open")
				$billOpenVal='Y';
			else if($payDet[2]=="rate")
				$billOpenVal='N';

			//If job type is internal direct or direct no need to insert per diem values in database.
			$Assignment_Job_Type = getManage($page152[2]); //To get the job type
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
			$page152[79] = ($page152[76] == 'Y') ? $page152[79] : '0.00';//Checking whether it is billable or not--Raj
			
			$candrn = $_REQUEST['candrn'];

			$qs="'','".$conusername."','".$page152[2]."','".$page152[3]."','".$page152[4]."','".$page152[5]."','".$page152[6]."','".$page152[7]."','".$page152[8]."','".$page152[9]."','".$page152[10]."','".$page152[11]."','".$page152[12]."','".$page152[13]."','".$page152[14]."','".$page152[15]."','".$page152[16]."','".$page152[17]."','".$page152[18]."','".$page152[19]."','".$page152[20]."','".$page152[21]."','".$billRateValue[0]."','".$billRateValue[2]."','".$billRateValue[1]."','".$payRateValue[0]."','".$payRateValue[2]."','".$payRateValue[1]."','".$page152[30]."','".$page152[31]."','".$page152[32]."','".$page152[33]."','".$page152[34]."','".$page152[35]."','".$page152[36]."','".$page152[37]."','".$page152[38]."','".$page152[39]."','".$page152[40]."','".$page152[41]."','".addslashes($page152[42])."','".addslashes($page152[43])."','".$page152[44]."','".$page152[45]."','".$page152[46]."','".$page152[47]."','$ustatus',now(),'".$page152[26]."','".$page152[27]."','".$page152[28]."','".$payDet[0]."','".$payOpenVal."','".$billOpenVal."','".$payDet[3]."','".$payDet[4]."','".$username."','".$page152[52]."','".$page152[53]."','".$page152[54]."','".$page152[55]."','".$page152[56]."','".$page152[57]."','".$page152[58]."','".$page152[59]."','".$loc_user."','".$page152[61]."','".$page152[62]."','".$page152[63]."','".$page152[64]."','".$page152[65]."','".$page152[66]."','".addslashes($page152[67])."','".addslashes($page152[68])."','".$page152[69]."','".$username."',NOW(),'".$page152[70]."',NOW(),'".$page152[71]."','".$page152[72]."','".$page152[73]."','".$page152[74]."','".$page152[75]."','".$page152[76]."','".$page152[77]."','".$page152[79]."','".$page152[80]."','".$assg_pusername."','".$assg_pusername."','".$page152[88]."','".$classid."','".$attention."','".$page152[90]."','".$page152[91]."','".$schedule_display."','".$page152[92]."','".$shift_id."','".$worksiteCode."','".$shift_st_time."','".$shift_et_time."','".$shift_type."','".$daypay."','".$licode."','".$federal_payroll."','".$nonfederal_payroll."','".$contractid."','".$classification."','".$assignment_timesheet_layout_preference."'";
			$query="insert into hrcon_jobs (sno,username,jotype,catid,project,refcode,posstatus,vendor,contact,client,manager,endclient,s_date,exp_edate,posworkhr,iterms,e_date,reason,hired_date,rate,rateper,rateperiod,bamount,bcurrency,bperiod,pamount,pcurrency,pperiod,pterms,otrate,ot_currency,ot_period,placement_fee,placement_curr,jtype,bill_contact,bill_address,wcomp_code,imethod,tsapp,bill_req,service_terms,hire_req,notes,addinfo,avg_interview,ustatus,udate,burden,margin,markup,calctype,prateopen,brateopen,prateopen_amt,brateopen_amt,owner,otprate_amt,otprate_period,otprate_curr,otbrate_amt,otbrate_period,otbrate_curr,payrollpid,cdate,offlocation,double_prate_amt,double_prate_period,double_prate_curr,double_brate_amt, double_brate_period,double_brate_curr,po_num,department,job_loc_tax,muser,mdate,date_placed,date_ended,diem_lodging,diem_mie,diem_total,diem_currency,diem_period,diem_billable,diem_taxable,diem_billrate,madison_order_id,pusername,assign_no,deptid,classid,attention,corp_code,industryid,schedule_display,bill_burden,shiftid,worksite_code,starthour,endhour,shift_type,daypay,licode,federal_payroll,nonfederal_payroll,contractid,classification,ts_layout_pref) values(".$qs.")";
			$hrres=mysql_query($query,$db);
			if($hrres)
			{
				$hrcon_jobs_sno=mysql_insert_id($db);
				
				//Update Worksite_id to hrcon_jobs from contact_manage--Mohan
				$cm_qry = "select serial_no from contact_manage where loccode='".$worksiteCode."'";
				$rescm_qry = mysql_query($cm_qry,$db);
				$worksite_id=mysql_fetch_row($rescm_qry);
				$updateWorksiteid = "UPDATE hrcon_jobs SET worksite_id = '".$worksite_id[0]."' WHERE sno='".$hrcon_jobs_sno."'";
				mysql_query($updateWorksiteid,$db);
				
                                saveBurdenDetails($burdenTypeDetails,$burdenItemDetails,'hrcon_burden_details','hrcon_jobs_sno','insert',$hrcon_jobs_sno);
                                saveBillBurdenDetails($bill_burdenTypeDetails,$bill_burdenItemDetails,'hrcon_burden_details','hrcon_jobs_sno','insert',$hrcon_jobs_sno);
                		if($person_id !=''){
					$personids = explode(',',$person_id);
					foreach ($personids as $personid) {
						if (($hrcon_jobs_sno !="" && $hrcon_jobs_sno !="0") && ($personid !="" && $personid !="0")) {
							$insertPersons = "INSERT INTO persons_assignment (asgnid,asgn_mode,person_id,cuser,cdate,muser,mdate) VALUES('".$hrcon_jobs_sno."','hrcon','".$personid."','".$username."',NOW(),'".$username."',NOW())";
							mysql_query($insertPersons,$db);
						}
					}
				}
				if($shift_type == "perdiem" && $schedule_display == 'NEW'){
					$hrmPerdiemShiftSchObj1 = new HRMPerdiemShift();
					$copyasign = 'no';
					$copyPusername='';
					$oldtimeslotvalues = $hrmPerdiemShiftSchObj1->insNewHrconJobPerdiemShift($hrcon_jobs_sno,$assg_pusername,$candrn,$username,$conusername,$copyasign,$copyPusername);
					$objScheduleDetails->insCandEmpTimeSlots($conusername,'hrcon_sm_timeslots',$assg_pusername,$oldtimeslotvalues,$username);

					// Making Employee / Candidate avaliablity as Busy 
					$objScheduleDetails->updBusyEmpTimeSlots($username, $conusername, $oldtimeslotvalues, $assg_pusername);
					//Dumping data into Candidate Shift Schedule from Employee Shift Schedule from the last date to future dates only.
					$previousDate = date("Y-m-d",strtotime("-2 months",strtotime(date("Y-m-d"))));
					$objScheduleDetails->deleteNInsertDataIntoCandidateFromEmployeeScheduleTable($emp_sno_val, $conusername,$previousDate);	

				}
				
				if($shift_type == "regular" && $schedule_display == 'NEW')
				{
					$objScheduleDetails->insHrEmpConJobsTimeSlots($username,'hrconjob_sm_timeslots',$hrcon_jobs_sno,$timeslotvalues);
				
					if ($smTimeslotChangedflag == "Y") {
					
						$objScheduleDetails->insCandEmpTimeSlots($conusername,'hrcon_sm_timeslots',$assg_pusername,$timeslotvalues,$username);
					}
				}
				
				/////////////////// UDF migration to customers ///////////////////////////////////
				include_once('custom/custome_save.php');		
				$directEmpUdfObj = new userDefinedManuplations();
				$directEmpUdfObj->insertUserDefinedData($hrcon_jobs_sno, 7);
				/////////////////////////////////////////////////////////////////////////////////

				setDefaultEntityTaxes('Assignment', $page152[9], 0, 0, $conusername, $assg_pusername);		

				$jobSno=$hrcon_jobs_sno;
				$contactSno=$hrcon_jobs_sno."|";
				$assignRatesValue = $contactSno;

				$assignment_array=array("modulename" => "HR->Assignments", "pusername" => $assg_pusername, "userid" => $conusername, "contactsno" => $contactSno, "invapproved" => 'closed');
				$tabappiont_ass_id=insertAssignmentSchedule($assignment_array);

				$schdet1 = explode("|^AkkenSplit^|",$schdet);
				$asd =count($schdet1);
				for ($i=1;$i<$asd;$i++)
				{
					$newarray = explode("|^AkkSplitCol^|",$schdet1[$i]);
					$InsdateArr=explode("/",$newarray[1]);
					$Insdate=$InsdateArr[2]."-".$InsdateArr[0]."-".$InsdateArr[1]." 00:00:00";
					if($schedule_display == 'OLD')
					{
						$sheQry="insert into hrcon_tab (sno,tabsno,starthour,endhour,wdays,sch_date,coltype) VALUES ('','".$tabappiont_ass_id."','".$newarray[3]."','".$newarray[4]."','".$newarray[2]."','".$Insdate."','assign')";
						mysql_query($sheQry,$db);
					}
				}
			}
			//Added to update emp_list jtype newly added column
		   $equery  =  "UPDATE emp_list e, hrcon_jobs hj SET e.emp_jtype='".$page152[36]."', e.cur_project = '".$page152[4]."', e.rate = '".$payRateValue[0]."'  WHERE e.username=hj.username AND hj.sno ='".$hrcon_jobs_sno."' ";
		    mysql_query($equery,$db);
		}

		if($hrres && $page152[49] == "nav")
		{
			$commissionData		= explode("|",$commission_session);
			$empno			= explode(",",$page152[51]);
			$emptxt			= explode(",",$commissionData[0]);
			$rateval		= explode(",",$commissionData[1]);
			$payval			= explode(",",$commissionData[2]);
			$rolename		= explode(",",$commissionData[4]);
			$roleoverwrite		= explode(",",$commissionData[5]);
			$roleEDisable		= explode(",",$commissionData[6]);

			for($i=0; $i <= $page152[50]; $i++) {
				if(substr($empno[$i],0,3) == "emp"){
					$empno[$i]	= str_replace("emp","",$empno[$i]);
					$typeOfPerson	= "E";
				}
				else {
					$typeOfPerson	= "A";
				}

				if($rolename[$i] != '') {
					$comm_insert	= "INSERT INTO assign_commission(sno, username, assignid, assigntype, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput,shift_id) VALUES('', '".$username."', '".$hrcon_jobs_sno."', 'H', '".$empno[$i]."', '$typeOfPerson', '".$emptxt[$i]."', '".$payval[$i]."', '".$rateval[$i]."', '".$rolename[$i]."', '".$roleoverwrite[$i]."', '".$roleEDisable[$i]."','".$shift_id."')";
					mysql_query($comm_insert, $db);
				}				
			}
		}
		return $assignRatesValue;
	}
?>
<html>
<head>
<title>New Assignment</title>
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

<body>
<script language=javascript>
document.onkeydown = function()
{		
	if(window.event && window.event.keyCode == 116) 
		window.event.keyCode = 505;

	if(window.event && window.event.keyCode == 505) 
		return false; 
}
</script>

<?php
unset($_SESSION['newShiftTotalArrayValues']);
unset($_SESSION['newShiftTotaldateArrayValues']);
unset($_SESSION['newShiftTotalDateSlotGrpArrayValues']);
unset($_SESSION['newShiftSchDatesTotalArrayValues'.$candrn]);
unset($_SESSION['newShiftSchDatesTotalArrayValues']);
unset($_SESSION['sm_form_data_array'.$candrn]);
unset($_SESSION['sm_form_data_array']);
unset($_SESSION['newAssignPerdiemShiftPagination'.$candrn]);
unset($_SESSION['newAssignPerdiemShiftSch'.$candrn]);
if($hr_insert_id=="")
{
	echo "<script>alert('We are unable to process your request. Please try again. If the problem persists, please contact Akken Customer Success Team');window.close();</script>";
}
else
{
	if(isset($assign))
	{
		if($page152[55]=="closed"){
			print "<script>alert('Assignment has been closed Successfully');window.opener.location.href=window.opener.location.href;window.close();</script>";
		}
		else if($copyasign == "yes"){
			if ($fromGigboard == "yes") {
				?>
				<script type="text/javascript">
				if(window.opener.location.href.indexOf('/BSOS/Accounting/Assignment/gigboard.php') > 0){
					alert('Assignment has been created Successfully');
					window.opener.$("#gigboardcalendar").fullCalendar('removeResource');
					window.opener.$("#gigboardcalendar").fullCalendar('removeEvents');
					window.opener.$('#gigboardcalendar').fullCalendar('refetchResources');
					window.opener.$("#gigboardcalendar").fullCalendar('refetchEvents');
					window.close();
				}else{
					alert('Assignment has been created Successfully');window.opener.doGridSearch('search');window.close();
				}
				</script>
				<?php
			}else{
			?>
				<script type="text/javascript">
				if(window.opener.location.href.indexOf('/HRM/Employee_Mngmt/newconreg15.php') > 0){
					alert('Assignment has been created Successfully');window.opener.location.href=window.opener.location.href;window.close();
				}else{
					alert('Assignment has been created Successfully');window.opener.doGridSearch('search');window.close();
				}
				</script>
			<?php
			}			
		}else if ($flag_reassign == "Yes") {
			?>
			<script type="text/javascript">
				alert('Assignment has been created Successfully');window.opener.doGridSearch('search');window.close();
			</script>
			<?php
		}
		else if($assign=="edit"){
			print "<script>alert('Assignment has been updated Successfully');window.opener.location.href=window.opener.location.href;window.close();</script>";
		}
		else if($assign=="New"){
			print "<script>alert('Assignment has been created Successfully'); window.opener.location.href=window.opener.location.href;window.close();</script>";
		}
		else{
			print "<script>alert('Assignment has been created/updated Successfully');window.opener.location.href=window.opener.location.href;window.close();</script>";
		}
	}
	else
	{
		print "<script>alert('Assignment has been created / updated Successfully');window.opener.location.href=window.opener.location.href;window.close();</script>";
	}

	print "<script>window.close();</script>";
}

session_unregister("schdet");
session_unregister("assignment_mulrates");
?>
</body>
</html>