<?php 
	$getpage15 = "page15".$HRM_HM_SESSIONRN;
	$page15 = $$getpage15;
	$getpage215 = "page215".$HRM_HM_SESSIONRN;
	$page215 = $$getpage215;
	$licode = $wali;
	//$getsm_form_data = "sm_form_data".$HRM_HM_SESSIONRN;
	$page15_PersonList = "person_list_ids".$HRM_HM_SESSIONRN;

	$person_id = $studentlistids;

	$sm_enabled_option_data = "sm_enabled_option".$HRM_HM_SESSIONRN;
	$schedule_display = $$sm_enabled_option_data;
	
	$page115=stripslashes($page15);
	$page2115=stripslashes($page215);	

	require("global.inc");
	require("dispfunc.php");
	require_once($akken_psos_include_path.'commonfuns.inc');
	require_once("multipleRatesClass.php");
	$ratesObj=new multiplerates();
	
	/* including shift schedule class file for converting Minutes to DateTime / DateTime to Minutes */
	require_once('shift_schedule/class.schedules.php');
	$objSchSchedules	= new Schedules();
	
	/* TLS-01202018 */
	$page11=explode("|",$_SESSION["page1".$HRM_HM_SESSIONRN]);
	$conusername = $_SESSION["conusername".$HRM_HM_SESSIONRN];
	$HREM_Assgschedule = $_SESSION["HREM_Assgschedule".$HRM_HM_SESSIONRN];
	
	//$sm_form_data = $$getsm_form_data;
	//$timeslotvalues = $sm_form_data;
	$timeslotvalues = implode("|",$_SESSION['sm_form_data_array'.$HRM_HM_SESSIONRN]);	
	$sm_form_data = $timeslotvalues;
	$shift_snos = $sm_sel_shifts; //getting selected shift name snos for inserting previous date data
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

	$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todate=date("m/d/Y",$thisday);	

	$page15=$page115;
	$page215=$page2115;
	$page1521=explode("|",$page15);
	$ratesCountVal = $page1521[87];
	$assignType=$page1521[36];
	$consultant_jobs_snoval=$page1521[11];

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

	//to handle the page load shift id problem
		if ($shift_type == "perdiem") {
			$shift_id = $perdiem_shiftid;
		}
        
	function createNewDataForHR($page15,$page215,$conusername,$db,$assgStage,$consult_sno,$HREM_Assgschedule,$astatus,$timeslotvalues,$burdenTypeDetails,$burdenItemDetails,$schedule_display,$bill_burdenTypeDetails,$bill_burdenItemDetails,$shift_id=NULL,$person_id=NULL,$worksitecode=NULL,$shift_st_time,$shift_et_time,$daypay,$licode,$federal_payroll,$nonfederal_payroll,$contractid,$classification,$assignment_timesheet_layout_preference='')
	{
		global $frommainpage,$username,$jobSno,$typeofassign,$commission_session,$loc_user,$commissionValues,$attention,$shift_type,$HRM_HM_SESSIONRN;

		$page152=explode("|",addslashes($page15));

		$createdate=explode("^AKK^",$page152[59]);
		$page152[59]=$createdate[1];
		$vvid=$consult_sno;

		$close_que="select posid from consultant_jobs where sno='".$consult_sno."'";
		$close_res=mysql_query($close_que,$db);
		$close_row=mysql_fetch_row($close_res);
		$jobPosSno=$close_row[0];

		$que="update consultant_jobs set notes='".$page152[45]."',muser='".$username."',mdate=NOW()  where username='".$conusername."' and jtype='OP' and sno='".$consult_sno."'";
		mysql_query($que,$db);

		$updassignment_array=array("modulename" => "HR->Assignments","userid" => "", "contactsno" => "contactsno='".$consult_sno."'", "invapproved" => 'backup' ,"appno" => "");
		updateAssignmentSchedule($updassignment_array);	

		if($frommainpage=="All")
			$ProjectStatus=$page215;
		else
			$ProjectStatus=$page152[36];

		$billRateValue=explode("^^",$page152[24]);
		$payRateValue=explode("^^",$page152[25]);
		$payDet=explode("^^",$page152[29]);

		if($payDet[1]=="open")
			$payOpenVal='Y';
		else if($payDet[1]=="rate")
			$payOpenVal='N';

		if($payDet[2]=="open")
			$billOpenVal='Y';
		else if($payDet[2]=="rate")
			$billOpenVal='N';

		$assqry = "select pusername,owner,posid from consultant_jobs where sno = ".$consult_sno;
		$assres = mysql_query($assqry,$db);
		$assrow = mysql_fetch_row($assres);
		$selpuser = $assrow[0];
		$ownerid = $assrow[1];
		$jobPosSno=$assrow[2];
		
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

		$page152[79] = ($page152[76]=='Y') ? $page152[79] : '0.00';//Checking whether it is billable or not--Raj
		$classid=displayDepartmentName($page152[88],true);//funtion to get the classid from department fun in commonfuns.inc

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

		if($ownerid == "")
		   $ownerid = $username;

		if($assgStage=="New")
		{
			$qs="'','".$conusername."','".$page152[2]."','".$page152[3]."','".$page152[4]."','".$page152[5]."','".$page152[6]."','".$page152[7]."','".$page152[8]."','".$page152[9]."','".$page152[10]."','".$page152[11]."','".$page152[12]."','".$page152[13]."','".$page152[14]."','".$page152[15]."','".$page152[16]."','".$page152[17]."','".$page152[18]."','".$page152[19]."','".$page152[20]."','".$page152[21]."','".$billRateValue[0]."','".$billRateValue[2]."','".$billRateValue[1]."','".$payRateValue[0]."','".$payRateValue[2]."','".$payRateValue[1]."','".$page152[30]."','".$page152[31]."','".$page152[32]."','".$page152[33]."','".$page152[34]."','".$page152[35]."','".$ProjectStatus."','".$page152[37]."','".$page152[38]."','".$page152[39]."','".$page152[40]."','".$page152[41]."','".addslashes($page152[42])."','".addslashes($page152[43])."','".$page152[44]."','".$page152[45]."','".$page152[46]."','".$page152[47]."','".$page152[59]."','".$page152[26]."','".$page152[27]."','".$page152[28]."','".$payDet[0]."','".$payOpenVal."','".$billOpenVal."','".$payDet[3]."','".$payDet[4]."','".$jobPosSno."','".$ownerid."','".$page152[52]."','".$page152[53]."','".$page152[54]."','".$page152[55]."','".$page152[56]."','".$page152[57]."','".$page152[58]."','".$page152[60]."','".$loc_user."','".$page152[61]."','".$page152[62]."','".$page152[63]."','".$page152[64]."','".$page152[65]."','".$page152[66]."','".addslashes($page152[67])."','".addslashes($page152[68])."','".$page152[69]."','".$username."',NOW(),'".$page152[71]."','".$page152[72]."','".$page152[73]."','".$page152[74]."','".$page152[75]."','".$page152[76]."','".$page152[77]."','".$page152[79]."','".$page152[88]."','".$classid."','".$attention."','".$page152[90]."','".$page152[91]."','".$schedule_display."','".$page152[92]."','".$shift_id."','".$worksitecode."','".$shift_st_time."','".$shift_et_time."','".$shift_type."','".$daypay."','".$licode."','".$federal_payroll."','".$nonfederal_payroll."','".$contractid."','".$classification."','".$assignment_timesheet_layout_preference."'";
			$query="insert into consultant_jobs (sno,username,jotype,catid,project,refcode,posstatus,vendor,contact,client,manager,endclient,s_date,exp_edate,posworkhr,iterms,e_date,reason,hired_date,rate,rateper,rateperiod,bamount,bcurrency,bperiod,pamount,pcurrency,pperiod,pterms,otrate,ot_currency,ot_period,placement_fee,placement_curr,jtype,bill_contact,bill_address,wcomp_code,imethod,tsapp,bill_req,service_terms,hire_req,notes,addinfo,avg_interview,cdate,burden,margin,markup,calctype,prateopen,brateopen,prateopen_amt,brateopen_amt,posid,owner,otprate_amt,otprate_period,otprate_curr,otbrate_amt,otbrate_period,otbrate_curr,payrollpid,notes_cancel,offlocation,double_prate_amt,double_prate_period,double_prate_curr,double_brate_amt,double_brate_period,double_brate_curr,po_num,department,job_loc_tax,muser,mdate,diem_lodging,diem_mie,diem_total,diem_currency,diem_period,diem_billable,diem_taxable,diem_billrate,deptid,classid,attention,corp_code,industryid,schedule_display,bill_burden,shiftid,worksite_code,starthour,endhour,shift_type,daypay,licode,federal_payroll,nonfederal_payroll,contractid,classification,ts_layout_pref) values(".$qs.")";
			
			mysql_query($query,$db);
			$consultant_jobs_sno=mysql_insert_id($db);
                        saveBurdenDetails($burdenTypeDetails,$burdenItemDetails,'consultant_burden_details','consultant_jobs_sno','insert',$consultant_jobs_sno);
                        saveBillBurdenDetails($bill_burdenTypeDetails,$bill_burdenItemDetails,'consultant_burden_details','consultant_jobs_sno','insert',$consultant_jobs_sno);
			
            if($person_id !=''){
				$personids = explode(',',$person_id);
				foreach ($personids as $personid) {
					$insertPersons = "INSERT INTO persons_assignment (asgnid,asgn_mode,person_id,cuser,cdate,muser,mdate) VALUES('".$consultant_jobs_sno."','consultant','".$personid."','".$username."',NOW(),'".$username."',NOW())";
					mysql_query($insertPersons,$db);
				}
			}
			//When the shift scheduling display is new, then only perform the action on new shift scheduling tables

			if($schedule_display == 'NEW' && $shift_type == "regular")
			{
				insUpdateConsultantJobsTimeSlots($db,'consultantjob_sm_timeslots',$consultant_jobs_sno,$timeslotvalues,'insert');
			}
		}
		else
		{
			$query="update consultant_jobs set jotype='".$page152[2]."',catid='".$page152[3]."',project='".$page152[4]."',refcode='".$page152[5]."',posstatus='".$page152[6]."',vendor='".$page152[7]."',contact='".$page152[8]."',client='".$page152[9]."',manager='".$page152[10]."',endclient='".$page152[11]."',s_date='".$page152[12]."',exp_edate='".$page152[13]."',posworkhr='".$page152[14]."',iterms='".$page152[15]."',e_date='".$page152[16]."',reason='".$page152[17]."',hired_date='".$page152[18]."',rate='".$page152[19]."',rateper='".$page152[20]."',rateperiod='".$page152[21]."',bamount='".$billRateValue[0]."',bcurrency='".$billRateValue[2]."',bperiod='".$billRateValue[1]."',pamount='".$payRateValue[0]."',pcurrency='".$payRateValue[2]."',pperiod='".$payRateValue[1]."',pterms='".$page152[30]."',otrate='".$page152[31]."',ot_currency='".$page152[32]."',ot_period='".$page152[33]."',placement_fee='".$page152[34]."',placement_curr='".$page152[35]."',jtype='".$ProjectStatus."',bill_contact='".$page152[37]."',bill_address='".$page152[38]."',wcomp_code='".$page152[39]."',imethod='".$page152[40]."',tsapp='".$page152[41]."',bill_req='".addslashes($page152[42])."',service_terms='".addslashes($page152[43])."',hire_req='".$page152[44]."',notes='".$page152[45]."',addinfo='".$page152[46]."',avg_interview='".$page152[47]."',cdate='".$page152[59]."',burden='".$page152[26]."',margin='".$page152[27]."',markup='".$page152[28]."',calctype='".$payDet[0]."',prateopen='".$payOpenVal."',brateopen='".$billOpenVal."',prateopen_amt='".$payDet[3]."',brateopen_amt='".$payDet[4]."',posid='".$jobPosSno."',owner='".$ownerid."',otprate_amt='".$page152[52]."',otprate_period='".$page152[53]."',otprate_curr='".$page152[54]."',otbrate_amt='".$page152[55]."',otbrate_period='".$page152[56]."',otbrate_curr='".$page152[57]."',payrollpid='".$page152[58]."',notes_cancel='".$page152[60]."',offlocation='".$loc_user."',double_prate_amt='".$page152[61]."',double_prate_period='".$page152[62]."',double_prate_curr='".$page152[63]."',double_brate_amt='".$page152[64]."',double_brate_period='".$page152[65]."',double_brate_curr='".$page152[66]."',po_num='".addslashes($page152[67])."',department='".addslashes($page152[68])."',job_loc_tax='".$page152[69]."',muser='".$username."',mdate=NOW(),diem_lodging='".$page152[71]."',diem_mie='".$page152[72]."',diem_total='".$page152[73]."',diem_currency='".$page152[74]."',diem_period='".$page152[75]."',diem_billable='".$page152[76]."',diem_taxable='".$page152[77]."',diem_billrate='".$page152[79]."',deptid='".$page152[88]."',classid='".$classid."',attention='".$attention."',corp_code='".$page152[90]."',industryid='".$page152[91]."',schedule_display='".$schedule_display."',bill_burden='".$page152[92]."',shiftid='".$shift_id."',worksite_code='".$worksitecode."',starthour='".$shift_st_time."',endhour='".$shift_et_time."',shift_type='".$shift_type."',daypay = '".$daypay."',licode = '".$licode."',federal_payroll = '".$federal_payroll."',nonfederal_payroll = '".$nonfederal_payroll."',contractid = '".$contractid."',classification = '".$classification."',ts_layout_pref = '".$assignment_timesheet_layout_preference."' where username='".$conusername."' and sno='".$consult_sno."'";
			mysql_query($query,$db);
			$consultant_jobs_sno=$consult_sno;

			if($person_id !=''){
				mysql_query("DELETE FROM persons_assignment WHERE asgnid='".$consult_sno."' AND asgn_mode='consultant'",$db);
				$personids = explode(',',$person_id);
				foreach ($personids as $personid) {
					$insertPersons = "INSERT INTO persons_assignment (asgnid,asgn_mode,person_id,cuser,cdate,muser,mdate) VALUES('".$consult_sno."','consultant','".$personid."','".$username."',NOW(),'".$username."',NOW())";
					mysql_query($insertPersons,$db);
				}
			}
            saveBurdenDetails($burdenTypeDetails,$burdenItemDetails,'consultant_burden_details','consultant_jobs_sno','update',$consultant_jobs_sno);
            saveBillBurdenDetails($bill_burdenTypeDetails,$bill_burdenItemDetails,'consultant_burden_details','consultant_jobs_sno','update',$consultant_jobs_sno);
			//When the shift scheduling display is new, then only perform the action on new shift scheduling tables
			if ($shift_type == "perdiem" && $schedule_display == 'NEW') 
			{
				/*
					Perdiem Shift Scheduling Class file
				*/
				require_once('perdiem_shift_sch/Model/class.hrm_perdiem_sch_db.php');
				$hrmPerdiemShiftSchObj = new HRMPerdiemShift();

				//Deleting the dates if anything exists
				if (count($_SESSION['deletedAssignPerdiemShiftSch'.$HRM_HM_SESSIONRN])>0) {
					$hrmPerdiemShiftSchObj->delPerdiemAssignShiftDetails($consultant_jobs_sno,$HRM_HM_SESSIONRN,$conusername,'consultant');
				}				
	
				$hrmPerdiemShiftSchObj->updtPerdiemShiftConsultantJobSno($consultant_jobs_sno,$assg_pusername,$HRM_HM_SESSIONRN);


			}
			else if($schedule_display == 'NEW')
			{
				insUpdateConsultantJobsTimeSlots($db,'consultantjob_sm_timeslots',$consultant_jobs_sno,$timeslotvalues,'update');
			}
		}

		/////////////////// UDF migration to customers ///////////////////////////////////
		include_once('custom/custome_save.php');		
		$directEmpUdfObj = new userDefinedManuplations();
		$directEmpUdfObj->insertUserDefinedData($consultant_jobs_sno, 8);

		/////////////////////////////////////////////////////////////////////////////////
		
		$jobSno=$consultant_jobs_sno;

		$st_dat = explode('-',$page152[12]);
		$end_dat = explode('-',$page152[16]);
		$st_ts =  mktime(0, 0, 0, $st_dat[0], $st_dat[1], $st_dat[2]);
		$end_ts = mktime(0, 0, 0, $end_dat[0], $end_dat[1], $end_dat[2]);

		if($assgStage=="New")
		{
			$assg_pusername=get_Assginment_Seq();

			$que="update consultant_jobs set pusername='".$assg_pusername."',muser='".$username."',mdate=NOW(),assign_no='".$assg_pusername."' where sno=".$consultant_jobs_sno;
			mysql_query($que,$db);

			if ($schedule_display == 'NEW' && $shift_type == "perdiem") 
			{
				require_once("perdiem_shift_sch/Model/class.hrm_perdiem_sch_db.php");
				$objPerdiemJobScheduleDetails = new HRMPerdiemShift();

				$selDataArry = $_SESSION['newAssignPerdiemShiftSch'.$HRM_HM_SESSIONRN];
				$selDateArry = $_SESSION['newAssignPerdiemShiftPagination'.$HRM_HM_SESSIONRN];

				$consultant_data = $objPerdiemJobScheduleDetails->insNewConsultantJobPerdiemShift($consultant_jobs_sno,$assg_pusername,$HRM_HM_SESSIONRN,$username,$conusername,$selDataArry,$selDateArry);
			}
		}
		else
		{
			$sel_pusername="SELECT pusername FROM consultant_jobs WHERE sno='".$jobSno."'";
			$res_pusername=mysql_query($sel_pusername,$db);
			$fetch_puser=mysql_fetch_row($res_pusername);
			$assg_pusername=$fetch_puser[0];
		}

		$assignment_array=array("modulename" => "HR->Assignments", "pusername" => $assg_pusername, "userid" => $conusername, "contactsno" => $jobSno, "invapproved" => 'active',"startdate" => $st_ts,"enddate" => $end_ts,"title" => "");
		$tabappiont_ass_id=insertAssignmentSchedule($assignment_array);

		$schdet1 = explode("|^AkkenSplit^|",$HREM_Assgschedule);
		$asd =count($schdet1);
		for ($i=1;$i<$asd;$i++)
		{
			$newarray = explode("|^AkkSplitCol^|",$schdet1[$i]);
			$schdt=explode("/",$newarray[1]);
			$sch_date=$schdt[2]."-".$schdt[0]."-".$schdt[1]." 00:00:00";
			if($schedule_display == 'OLD')
			{
				$sheQry="insert into consultant_tab (sno,consno,starthour,endhour,wdays,sch_date,coltype) VALUES ('','".$tabappiont_ass_id."','".$newarray[3]."','".$newarray[4]."','".$newarray[2]."','".$sch_date."','assign')";
				mysql_query($sheQry,$db);
			}

		}

		if($assgStage != "New")
		{	
			$comm_delete	= "DELETE FROM assign_commission WHERE assignid = '".$consult_sno."' AND assigntype = 'C' ";
			mysql_query($comm_delete, $db);
		}

		if($commission_session == "" || $commission_session == "||||||")
			$commission_session	= $commissionValues;

		if($commission_session != "")
		{
			$commissionData		= explode("|",$commission_session);
			$commDataLength		= count($commissionData);
			$empno			= explode(",",$commissionData[7]);
			$emptxt			= explode(",",$commissionData[0]);
			$rateval		= explode(",",$commissionData[1]);
			$payval			= explode(",",$commissionData[2]);
			$rolename		= explode(",",$commissionData[4]);
			$roleoverwrite		= explode(",",$commissionData[5]);
			$roleEDisable		= explode(",",$commissionData[6]);

			for($i=0; $i<=$commDataLength; $i++) {
				if(substr($empno[$i],0,3) == "emp") {
					$empno[$i]	= str_replace("emp","",$empno[$i]);
					$typeOfPerson	= "E";
				}
				else {
					$typeOfPerson	= "A";
				}

				if($rolename[$i] != '') {
					$comm_insert	= "INSERT INTO assign_commission(sno, username, assignid, assigntype, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput,shift_id) VALUES('', '".$username."', '".$consultant_jobs_sno."', 'C', '".$empno[$i]."', '".$typeOfPerson."', '".$emptxt[$i]."', '".$payval[$i]."', '".$rateval[$i]."', '".$rolename[$i]."', '".$roleoverwrite[$i]."', '".$roleEDisable[$i]."','".$shift_id."')";
					mysql_query($comm_insert, $db);
				}
			}
		}

		return $jobSno;
	}
	//Function end

	if($dest==15)
	{
		$spt=explode("|",$page15);
		$que1="select sno,name,type from manage where sno='".$spt[2]."'";
		$res=mysql_query($que1,$db);
		$rs=mysql_fetch_array($res);
		$jobtypeIsDirect="";
		$typeofassign=$rs[1];

		if($rs[1]=="Direct")
			$jobtypeIsDirect="YES";

		$sql_loc="SELECT contact_manage.serial_no FROM contact_manage, consultant_compen WHERE contact_manage.status != 'BP' AND consultant_compen.location = contact_manage.serial_no AND consultant_compen.username = '".$username."'";
		$res_loc=mysql_query($sql_loc,$db);
		$fetch_loc=mysql_fetch_row($res_loc);
		$loc_user=$fetch_loc[0];
    
		if(isset($cmfrom))
		{
			$spt=explode("|",$page151);

			$que1="select sno,name,type from manage where sno='".$spt[2]."'";
			$res=mysql_query($que1,$db);
			$rs=mysql_fetch_array($res);
			$jobtypeIsDirect="";

			if($rs[1]=="Direct")
				$jobtypeIsDirect="YES";

			$cque="select count(1) from consultant_jobs where username='".$conusername."' and jtype='OP' and sno='".$consult_sno."'";
			$cres=mysql_query($cque,$db);
			$crow=mysql_fetch_row($cres);

			if($crow[0]==0)
			{
				Header("Location:newconreg15.php?ustat=error&addr=old");
				exit();
			}
			Header("Location:newconreg15.php?ustat=success&addr=old");
			exit();
		}

		//for getting the comission values from assignment screen-----start
		$commissionVisitedArray=array();
		$empval1	= "";
		$commval1	= "";
		$ratetype1	= "";
		$paytype1	= "";
		$roleName1	= "";
		$roleOverWrite1 = "";
		$roleEDisable1	= "";
		$commIndex	= 0;
		$commKey	= "";

		$arrayCount	= array();
		$emp_val	= explode(',',$empvalues);

		for($k=0;$k<count($emp_val);$k++)
		{
			if($emp_val[$k]!='noval')
			{
				$commvalues	= explode("^",$emp_val[$k]);
				$emp_values[$k]	= $commvalues[0];
			}
		}

		if(count($emp_values) > 0)
		{
			$counter	= 0;
			foreach($emp_values as $key => $keyValue) {
				$commKey	= $keyValue;

				if(in_array($keyValue, $commissionVisitedArray)) {
					array_push($commissionVisitedArray, $keyValue);
					$arrayCount		= array_count_values($commissionVisitedArray);
					$dynemp.$keyValue	= $arrayCount[$keyValue]-1;
				}
				else {
					array_push($commissionVisitedArray, $keyValue);
					$dynemp.$keyValue	= 0;
				}

				$indexVal	= $dynemp.$keyValue;

				if($counter == 0)
				{
					$empval1	= $commKey;
					$commval1	= $commval[$commKey][$indexVal];
					$ratetype1	= $ratetype[$commKey][$indexVal];
					$paytype1	= $paytype[$commKey][$indexVal];
					$roleName1	= $roleName[$commKey][$indexVal];
					$roleOverWrite1	= $roleOverWrite[$commKey][$indexVal];
					$roleEDisable1	= $roleEDisable[$commKey][$indexVal];
				}
				else
				{
					$empval1	.= ",".$commKey;
					$commval1	.= ",".$commval[$commKey][$indexVal];
					$ratetype1	.= ",".$ratetype[$commKey][$indexVal];
					$paytype1	.= ",".$paytype[$commKey][$indexVal];
					$roleName1	.= ",".$roleName[$commKey][$indexVal];
					$roleOverWrite1	.= ",".$roleOverWrite[$commKey][$indexVal];
					$roleEDisable1	.= ",".$roleEDisable[$commKey][$indexVal];
				}

				$counter	= $counter+1;
			}
		}

		$page1511	= explode("|",$page15);
		$ratesCountVal	= $page1511[87];

		require_once('assignmentSession.php');
		$page1511[51]	= implode(",",$commissionVisitedArray);
		$page15_imp	= implode("|",$page1511);
		$page15		= $page15_imp;

		$commissionValues	= $commval1."|".$ratetype1."|".$paytype1."|".$assignstatus."|".$roleName1."|".$roleOverWrite1."|".$roleEDisable1."|".$empval1;
		
		$_SESSION["commission_session".$HRM_HM_SESSIONRN]	= $commissionValues;
		$commission_session					= $commissionValues;

		$assign_mulrates	= getAssignmentSession($rateType,$billableR,$mulpayRateTxt,$payratePeriod,$payrateCurrency,$mulbillRateTxt,$billratePeriod,$billrateCurrency,$taxableR,$mulRatesVal);

		$page151=$page15;
		$elements=explode("|",$page151);

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
			////////////End of the Update//////////////////////////////////////////////////////
		}

		
			$_SESSION["HREM_Assgschedule".$HRM_HM_SESSIONRN]=$schdet1;
			$HREM_Assgschedule = $_SESSION["HREM_Assgschedule".$HRM_HM_SESSIONRN];
		

			$assignment_mulrates=$assign_mulrates;
			$_SESSION["assignment_mulrates".$HRM_HM_SESSIONRN]=$assignment_mulrates;
			
		

		$candidateStatus="";
		
		if($assignstatus=="Update")
		{
			if($newAssCreate=="YES")
			{
				$empcon_sno="";
				$consult_sno="";
			}

			$jobSno = createNewDataForHR($page15,$page215,$conusername,$db,$assgStatus,$conjob_sno,$HREM_Assgschedule,'',$timeslotvalues,$burdenTypeDetails,$burdenItemDetails,$schedule_display,$bill_burdenTypeDetails,$bill_burdenItemDetails,$shift_id,$person_id,$worksitecode,$shift_st_time,$shift_et_time,$daypay,$licode,$federal_payroll,$nonfederal_payroll,$contractid,$classification,$assignment_timesheet_layout_preference);
		}
        else if($assignstatus=="New")
		{
			$jobSno = createNewDataForHR($page15,$page215,$conusername,$db,$assgStage,$conjob_sno,$HREM_Assgschedule,$astatus,$timeslotvalues,$burdenTypeDetails,$burdenItemDetails,$schedule_display,$bill_burdenTypeDetails,$bill_burdenItemDetails,$shift_id,$person_id,$worksitecode,$shift_st_time,$shift_et_time,$daypay,$licode,$federal_payroll,$nonfederal_payroll,$contractid,$classification,$assignment_timesheet_layout_preference);
		}

		$mode_rate_type = "consultant";	
		$type_order_id = $page1511[48];	
		$ratesObj->doBackupExistingRates();

		$expDefaultDyna=explode("^DefaultRates^",$assignment_mulrates);
		$type_order_id = $jobSno;
		$ratesObj->defaultRatesInsertion($expDefaultDyna[1],$in_ratesCon);
		$ratesObj->multipleRatesAsgnInsertion($expDefaultDyna[0]);
	}

	if($empStatusVal!="")
	{
		
                $page215=$empStatusVal;
                $_SESSION["page215"]=$page215;


		//For registering page15 values.
		
                $page15=$pageDetVal;
                $_SESSION["page15"] =$page15 ;
		
	}
	
	//function to insert time schedule management data into consultant jobs time slots
	function insUpdateConsultantJobsTimeSlots($db,$table_name,$psno,$timeSlotsData,$calltype)
	{
		global $username, $objSchSchedules, $shift_snos;

		if($calltype == 'update')
		{
			$previousDate = date("Y-m-d",strtotime("-2 months",strtotime(date("Y-m-d"))));

			//deleting the consultant, hrcon and empcon time frame details for past date which are not selected
			$del_sm_conjob_tf_sql = "DELETE FROM ".$table_name." WHERE pid='".$psno."' AND shift_date < '".$previousDate."' AND sm_sno NOT IN (".$shift_snos.")";
			mysql_query($del_sm_conjob_tf_sql,$db);

			$del_sm_conjob_tf_sql = "DELETE FROM ".$table_name." WHERE pid = '".$psno."' AND shift_date >= '".$previousDate."'";
			$del_sm_conjob_tf_res = mysql_query($del_sm_conjob_tf_sql, $db);
		}
		$sm_plcmnt_array=explode("|",$timeSlotsData);
		$sm_req_cnt	= count($sm_plcmnt_array);
		
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
				$shiftNameSno = $smValExp[6];
				$shiftPosNum = $smValExp[7];

				if($slotGrpNo == "")
				{
					$slotGrpNo = 0;
				}
				if($shiftStatus == "")
				{
					$shiftStatus = 'available';
				}
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
				$smShiftNameSno	= $timeSlotDetails[5];
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
				
			}
			if($setFlat == 1)
			{
				$tempVar++;
			}
		}
		
	}
?>
<html>
<head>
<title>Assignments</title>
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

<script language=javascript>
var emp = '<? echo $data[1];?>';
var tday ='<? echo $todate; ?>';
var compname ='<? echo $compname1[0];  ?>';

document.onkeydown = function()
{		
	if(window.event && window.event.keyCode == 116) 
		window.event.keyCode = 505;

	if(window.event && window.event.keyCode == 505) 
		return false; 
}

function displayAlert1(disp)
{
	if(disp == "ok")
	{
		<?php
		if(isset($assignstatus))
		{
			if(isset($frommainpage))
			{
				?>
				window.opener.location.href=window.opener.location.href; window.opener.focus();
				<?php
			}
			else
			{
				?>
				self.close(); window.opener.location.href=window.opener.location.href; window.opener.focus();
				<?php
			}
		}
		else
		{
			?>
			self.close(); window.opener.location.href=window.opener.location.href; window.opener.focus();
			<?php
		}
		?>
	}
}	
</script>	
<?php
if(isset($assignstatus))
{
	if(isset($frommainpage))
		Header("Location:newconreg15.php");
	else if($assignstatus=="Update")
		print "<script>alert('Assignment updated Successfully'); self.close(); window.opener.location.href=window.opener.location.href; window.opener.focus();</script>";
	else
		print "<script>alert('Assigned Successfully'); self.close(); window.opener.location.href=window.opener.location.href; window.opener.focus();</script>";
}
else
{
	print "<script>alert('Assigned Successfully'); self.close(); window.opener.location.href=window.opener.location.href; window.opener.focus();</script>";
}
?>
<body>
</body>
</html>