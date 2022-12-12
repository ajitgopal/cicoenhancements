<?php 
	if($updASGNStat == "updASGNStat")
	{
		require("global.inc");
		require_once("syncHRfuncs.php");
		require("madisonfuns.php");
		require_once($akken_psos_include_path.'defaultTaxes.php');
		require_once("multipleRatesClass.php");
		require_once("class.Notifications.inc");
		$placementNotification 	= new Notifications();
		
		$ratesObj=new multiplerates();
		
		require_once('shift_schedule/hrm_schedule_db.php');
		$objScheduleDetails	= new EmployeeSchedule();

		/*
			Perdiem Shift Scheduling Class file
		*/
		require_once('perdiem_shift_sch/Model/class.hrm_perdiem_sch_db.php');
		$hrmPerdiemShiftSchObj = new HRMPerdiemShift();
			
		/* including shift schedule class file for converting Minutes to DateTime / DateTime to Minutes */
		require_once('shift_schedule/class.schedules.php');
		$objSchSchedules	= new Schedules();

		require_once("class.Aca.php");

		// Class For ACA Information
		$acaDet = new Aca();
	
		$aryAssignments = array();
	
		if($endDate == '') 
			$endDate = date('m-d-Y');
	
		$flagLogout = 0;
		$cancelNotes = ($assgnUpdateStatus == 'cancel') ? trim($cancelNotes) : '';
	
		$hrcon_list_sno = "";
		$concat_Var = "";
		$checkVal = "";
		$aryAssignments = @explode(',', $assgnList);
		$countAssgnments = count($aryAssignments);
	
		$hcloseasgn=$getCloseAsgn;
		$henddate=$getCloseAsgnDate;
		$hterminate=$getTerminate;
		$hterdate=$getTerminateDate;
		
		$getSelectedAssignUserName = "";
		$getTerminatedEmpUserName = "";
		$getTerminatedEmpDate = "";
		$getReHiredEmpUserName = "";
		$getAssignEmpconIds = "";
		$getAssignHrconIds = "";
	
		for($i = 0; $i < $countAssgnments; $i++)
		{
			$ary_assignments = explode('|', $aryAssignments[$i]);
	
			$assgnStatus = $ary_assignments[1];
			$emp_listsno = $ary_assignments[2];
			$flagSameJobOrder = 1;
			$setFlag = 0;
	
			if($assgnStatus == 'approved' || $assgnStatus == 'pending')
			{
				$hrconJobsSno = $ary_assignments[0];

				$queryGetHrcon = "SELECT h.username, IFNULL(IF(s_date = '0-0-0' OR h.s_date = '','0-0-0', h.s_date),'0-0-0') as tmpdate, CONCAT('cand',h.candidate) AS candidate, IF(h.s_date != '0-0-0' and h.s_date != '', DATE_FORMAT(STR_TO_DATE(h.s_date, '%m-%d-%Y'), '%Y-%m-%d'), '0-0-0') AS startDate, h.owner, h.jtype, m.name, IFNULL(h.posid,0) AS posid, h.candidate AS candidateID, h.assign_no, h.pusername FROM hrcon_jobs h, manage m WHERE h.jtype='OP' AND h.jotype = m.sno AND m.type = 'jotype' AND h.sno = ".$hrconJobsSno;
				
				$emcon_shift_qry = "SELECT hsm.sm_sno FROM hrcon_jobs as hjob INNER JOIN hrconjob_sm_timeslots as hsm ON(hjob.sno = hsm.pid) WHERE hjob.sno = '".$hrconJobsSno."' GROUP BY hjob.sno;";
				$emcon_shift_qry_res  = mysql_query($emcon_shift_qry,$db);
					
				$shift_id 	= 0;	
					
				if(mysql_num_rows($emcon_shift_qry_res) > 0){
					$shift_info_row 	=  mysql_fetch_row($emcon_shift_qry_res);
					$shift_id		 	=  $shift_info_row[0];					
				}
				
				$queryHrcon = mysql_query($queryGetHrcon, $db);
				if(mysql_num_rows($queryHrcon) > 0)
				{
					$rowHrcon 			= mysql_fetch_array($queryHrcon);
					$conusername 		= $rowHrcon['username'];
					$assgnment_ownerid 	= $rowHrcon['owner'];
					$cand_username 		= $rowHrcon['candidate'];
					$jobType 			= $rowHrcon['name'];
					$projectStatus 		= $rowHrcon['jtype'];
					$orgProjectStatus 	= $rowHrcon['jtype'];
					$posid 				= $rowHrcon['posid'];
					$assignmentNo 		= $rowHrcon['assign_no'];
					$candidateID 		= $rowHrcon['candidateID'];
					$madisonassid 		= $rowHrcon['pusername'];
					
					if($getSelectedAssignUserName == "")
						$getSelectedAssignUserName = $conusername;
					else
						$getSelectedAssignUserName .= ",".$conusername;
	
					// Checking if the employee data exists in madison_paydata table. If not we need to insert a record for the employee as the assignment or employee record created from data migration
					$mpque="select count(1) from madison_paydata where paydata_emp_username='$conusername'";
					$mpres=mysql_query($mpque,$db);
					$mprow=mysql_fetch_row($mpres);
					if($mprow[0]==0)
						PrepareMadisonPayData("Hiring",$conusername,"","","");
	
					$queryAssgnSchedule = "SELECT appno, contactsno FROM assignment_schedule WHERE userid='".$conusername."' AND modulename='HR->Assignments' AND contactsno like'".$hrconJobsSno."|%' AND invapproved='active'";
					$sqlAssgnSchedule = mysql_query($queryAssgnSchedule,$db);
					$rowAssgnSchedule = mysql_fetch_array($sqlAssgnSchedule);
	
					$appno = $rowAssgnSchedule['appno'];		
					$hrconSno = $hrconJobsSno;
					$assgn_pusername = $rowHrcon['pusername']; 
					if($assgnStatus == 'approved' && $assgnUpdateStatus == 'closed') 
					{
						$assgnRateIds = fnsetAssignmentStatus($appno, $hrconJobsSno, $assgn_pusername, $assgnStatus, $assgnUpdateStatus, $projectStatus, $jobType, $conusername, $assgnment_ownerid, $cancelNotes, $endDate, $orgProjectStatus, $assignmentNo, $rowHrcon['tmpdate'], $setAllEndDate,$shift_id,$reasonSno);
						$setFlag = 1;
					}
					else if($assgnStatus == 'approved' && $assgnUpdateStatus == 'cancel')
					{
						$assgnRateIds = fnsetAssignmentStatus($appno, $hrconJobsSno, $assgn_pusername, $assgnStatus, $assgnUpdateStatus, $projectStatus, $jobType, $conusername, $assgnment_ownerid, $cancelNotes, $endDate, $orgProjectStatus, $assignmentNo, $rowHrcon['tmpdate'], $setAllEndDate,$shift_id,$reasonSno);
						$setFlag = 1;
					}
					else if($assgnStatus == 'pending')
					{
						if($assgnStatus == 'pending' &&  $assgnUpdateStatus == 'active' && $rowHrcon['tmpdate'] == '0-0-0')
							$startDate = $selStartDate;
						else
							$startDate = $rowHrcon['tmpdate'];
	
						$setFlag = 1;
	
						if($assgnStatus == 'pending' && $assgnUpdateStatus == 'active')
						{
							if($jobType == 'Direct')
							{
								$assgnRateIds = fnsetAssignmentStatus($appno, $hrconJobsSno, $assgn_pusername, $assgnStatus, $assgnUpdateStatus, $projectStatus, $jobType, $conusername, $assgnment_ownerid,  $cancelNotes, $getDirEndDate, $orgProjectStatus, $assignmentNo, $startDate, $setAllEndDate,$shift_id,$reasonSno);
							}
							else
							{
								$assgnRateIds = fnsetAssignmentStatus($appno, $hrconJobsSno, $assgn_pusername, $assgnStatus, $assgnUpdateStatus, $projectStatus, $jobType, $conusername, $assgnment_ownerid,  $cancelNotes, $getDirEndDate, $orgProjectStatus, $assignmentNo, $startDate, $setAllEndDate,$shift_id,$reasonSno);
							}
						}
						else
						{
							
							$assgnRateIds = fnsetAssignmentStatus($appno, $hrconJobsSno, $assgn_pusername, $assgnStatus, $assgnUpdateStatus, $projectStatus, $jobType, $conusername, $assgnment_ownerid,  $cancelNotes, $endDate, $orgProjectStatus, $assignmentNo, $startDate, $setAllEndDate,$shift_id,$reasonSno);
						}
					}
	
					$expNewAssgnRateIds =  explode("^^",$assgnRateIds);
					$getNewRatesIds = explode("|",$expNewAssgnRateIds[1]);
					
					if($getAssignHrconIds == "")
						$getAssignHrconIds = $getNewRatesIds[0];
					else
						$getAssignHrconIds .= ",".$getNewRatesIds[0];
	
					/* Terminating Employee, Deactivating User Account and Updating Email Account for that employee when the option is selected in assignment update status */
					if($hterminate=="Y")
					{
						$sterdate = explode("-",$hterdate);
						$tdate = $sterdate[2]."-".$sterdate[0]."-".$sterdate[1];
	
						$tque="SELECT empterminated, tdate FROM emp_list WHERE sno=$emp_listsno";
						$tres=mysql_query($tque,$db);
						$trow=mysql_fetch_row($tres);
	
						if($trow[0]=="N" || $trow[1]=="" || $trow[0]=="Y")
						{
							$que="update emp_list set empterminated='Y',tdate='$tdate',lstatus='ACTIVE',show_crm='Y' where sno=$emp_listsno";
							mysql_query($que,$db);
	
							/* Getting Terminated Employees Username and Terminated Date */
							if($getTerminatedEmpUserName == "")
								$getTerminatedEmpUserName = $conusername;
							else
								$getTerminatedEmpUserName .= ",".$conusername;
	
							$getTerminatedEmpDate = $tdate;
	
							if($hterdate==date("m-d-Y"))
							{
								removeJobBoardIds($emp_listsno);
	
								$cand_prof="UPDATE candidate_list a,candidate_prof b set b.availsdate='inactive',a.status='ACTIVE' WHERE a.username=b.username AND a.username='".$cand_username."'";
								mysql_query($cand_prof,$db);
	
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

									if($data[0]==$username){
										$flagLogout = 1;
									}
								}
							}
						}
					}
				}
			}
			else
			{
				$hrconSno = $ary_assignments[0];
	
				$qeuryGetHrcon = "SELECT h.username, h.s_date, CONCAT('cand',h.candidate) AS candidate, IF(h.s_date != '0-0-0' and h.s_date != '', DATE_FORMAT(STR_TO_DATE(h.s_date, '%m-%d-%Y'), '%Y-%m-%d'), '0-0-0') AS startDate, h.owner, h.jtype, m.name, IFNULL(h.posid,0) AS posid, candidate AS candidateID, assign_no, h.notes_cancel, h.pusername FROM hrcon_jobs h, manage m WHERE  h.jtype='OP' AND h.jotype = m.sno AND m.type = 'jotype' AND h.sno = ".$hrconSno;
				
				$hrcon_shift_qry = "SELECT hsm.sm_sno FROM hrcon_jobs as hrjob INNER JOIN hrconjob_sm_timeslots as hsm ON(hrjob.sno = hsm.pid) WHERE hrjob.sno = '".$hrconSno."' GROUP BY hrjob.sno;";
				$hrcon_shift_qry_res  = mysql_query($hrcon_shift_qry,$db);
								
				$shift_id 	= 0;	
				if(mysql_num_rows($hrcon_shift_qry_res) > 0){
					$shift_info_row 	=  mysql_fetch_row($hrcon_shift_qry_res);
					$shift_id		 	=  $shift_info_row[0];		
				}
				
				
				$queryHrcon = mysql_query($qeuryGetHrcon, $db);
				if(mysql_num_rows($queryHrcon) > 0)
				{
					$setFlag = 1;
					$rowHrcon = mysql_fetch_array($queryHrcon);
					$conusername = $rowHrcon['username'];
					$assgnment_ownerid = $rowHrcon['owner'];
					$cand_username = $rowHrcon['candidate'];
					$jobType = $rowHrcon['name'];
					$projectStatus = $rowHrcon['jtype'];
					$orgProjectStatus = $rowHrcon['jtype'];
					$posid = $rowHrcon['posid'];
					$candidateID = $rowHrcon['candidateID'];
					$assignmentNo = $rowHrcon['assign_no'];
					$madisonassid = $rowHrcon['pusername'];
					
					if($getActTerminate=="Y") {
						mysql_query("UPDATE emp_list SET empterminated='N', tdate=NULL WHERE username='$conusername'",$db);
	
						if($getReHiredEmpUserName == "")
							$getReHiredEmpUserName = $conusername;
						else
							$getReHiredEmpUserName .= ",".$conusername;
					}
	
					// Checking if the employee data exists in madison_paydata table. If not we need to insert a record for the employee as the assignment or employee record created from data migration.
					$mpque="select count(1) from madison_paydata where paydata_emp_username='$conusername'";
					$mpres=mysql_query($mpque,$db);
					$mprow=mysql_fetch_row($mpres);
					if($mprow[0]==0)
						PrepareMadisonPayData("Hiring",$conusername,"","","");
	
					if($hrconSno && $hrconSno>0)
					{
						$queryAssgnSchedule = "SELECT appno, contactsno FROM assignment_schedule WHERE userid='".$conusername."' AND modulename='HR->Assignments' AND contactsno like'".$hrconSno."|%' AND invapproved in ('closed', 'cancel')";
						$sqlAssgnSchedule = mysql_query($queryAssgnSchedule,$db);
						$rowAssgnSchedule = mysql_fetch_array($sqlAssgnSchedule);
						$appno = $rowAssgnSchedule['appno'];
	
						if($setEndDate=="Y")
							$set_e_date = "'0-0-0'";
						else
							$set_e_date = "e_date";
						
						/* 
							Updateing hrcon_jobs pusername with _tmp.
							Because we need to re-create the hrcon_jobs and its related tables using old hrcon_jobs sno 
							with respective status which is passed by user.
						*/
						$assgn_pusername = $rowHrcon['pusername'];

						$updateTempPusername = "UPDATE hrcon_jobs SET pusername=CONCAT(pusername,'_tmp') WHERE sno=".$hrconSno." AND `pusername`='".$assgn_pusername."'";
						mysql_query($updateTempPusername, $db);
						
						mysql_query("update assignment_schedule set invapproved='backup', cuser ='".$username."',muser='".$username."',mdate=now() WHERE appno=".$appno, $db);
	
						if($jobType=="Direct"){
							$uStatus ='pending';
						}else{
							$uStatus = 'active';
						}
						
						$queryInsertHrconJobs = "INSERT INTO hrcon_jobs (username, jotype, catid, project, refcode, posstatus, vendor, contact, client, manager, endclient, s_date, exp_edate, posworkhr, iterms, e_date, reason, hired_date, rate, rateper, rateperiod, bamount, bcurrency, bperiod, pamount, pcurrency, pperiod, pterms, otrate, ot_currency, ot_period, placement_fee, placement_curr, jtype, bill_contact, bill_address, wcomp_code, imethod, tsapp, bill_req, service_terms, hire_req, notes, addinfo, avg_interview, ustatus,assg_status, udate, burden, margin, markup, calctype, prateopen, brateopen, prateopen_amt, brateopen_amt, posid, owner, otprate_amt, otprate_period, otprate_curr, otbrate_amt, otbrate_period, otbrate_curr, payrollpid, cdate, notes_cancel, offlocation, double_prate_amt, double_prate_period, double_prate_curr, double_brate_amt, double_brate_period, double_brate_curr, po_num, department, job_loc_tax, muser, mdate, date_placed, diem_lodging, diem_mie, diem_total, diem_currency, diem_period, diem_billable, diem_taxable, diem_billrate, madison_order_id, assign_no, candidate, pusername,classid,deptid,attention,corp_code,industryid,schedule_display,bill_burden,shiftid,worksite_code,copy_asignid,starthour,endhour,shift_type,daypay,worksite_id,licode,federal_payroll,nonfederal_payroll,contractid,classification,ts_layout_pref) SELECT username, jotype, catid, project, refcode, posstatus, vendor, contact, client, manager, endclient, s_date, exp_edate, posworkhr, iterms,".$set_e_date.", reason, hired_date, rate, rateper, rateperiod, bamount, bcurrency, bperiod, pamount, pcurrency, pperiod, pterms, otrate, ot_currency, ot_period, placement_fee, placement_curr, jtype, bill_contact, bill_address, wcomp_code, imethod, tsapp, bill_req, service_terms, hire_req, notes, addinfo, avg_interview, '".$uStatus."', assg_status, NOW(), burden, margin, markup, calctype, prateopen, brateopen, prateopen_amt, brateopen_amt, posid, owner, otprate_amt, otprate_period, otprate_curr, otbrate_amt, otbrate_period, otbrate_curr, payrollpid, cdate, notes_cancel, offlocation, double_prate_amt, double_prate_period, double_prate_curr, double_brate_amt, double_brate_period, double_brate_curr, po_num, department, job_loc_tax, '".$username."',  NOW(), date_placed, diem_lodging, diem_mie, diem_total, diem_currency, diem_period, diem_billable, diem_taxable, diem_billrate, madison_order_id, '".$assgn_pusername."', candidate, '".$assgn_pusername."',classid,deptid,attention,corp_code,industryid,schedule_display,bill_burden,shiftid,worksite_code,copy_asignid,starthour,endhour,shift_type,daypay,worksite_id,licode,federal_payroll,nonfederal_payroll,contractid,classification,ts_layout_pref FROM hrcon_jobs WHERE sno = ".$hrconSno;
						
						mysql_query($queryInsertHrconJobs, $db);
						$newHrconSno = mysql_insert_id($db);

						if($uStatus == 'active'){
							$query  =  "UPDATE emp_list e, hrcon_jobs hj SET e.emp_jtype=hj.jtype, e.cur_project = hj.project, e.rate = hj.pamount  WHERE e.username=hj.username AND hj.pusername='".$assgn_pusername."' ";

						mysql_query($query,$db);
						}
					
						$insBurdenDetils = "INSERT INTO hrcon_burden_details(hrcon_jobs_sno,ratemasterid,ratetype,bt_id,bi_id) SELECT ".$newHrconSno.",ratemasterid,ratetype,bt_id,bi_id FROM hrcon_burden_details WHERE hrcon_jobs_sno = ".$hrconSno;
				    		mysql_query($insBurdenDetils, $db);
	
						$his_person_insert	= "INSERT INTO persons_assignment(asgnid,asgn_mode,person_id,cuser,cdate,muser,mdate) SELECT '".$newHrconSno."','hrcon', person_id, cuser, cdate,'".$username."',NOW() FROM persons_assignment WHERE asgnid = '".$hrconSno."' AND asgn_mode='hrcon'";
						mysql_query($his_person_insert,$db);
						
						//inserting the time frame details from hrcon jobs <-> empcon jobs when reactivated
						$que="INSERT INTO hrconjob_sm_timeslots(sno, pid, shift_date, shift_starttime, shift_endtime, event_type, event_no, event_group_no, shift_status, sm_sno, no_of_positions, cuser, ctime, muser, mtime,shiftnotes) SELECT '', '".$newHrconSno."', shift_date, shift_starttime, shift_endtime, event_type, event_no, event_group_no, shift_status, sm_sno, no_of_positions, cuser, ctime, muser, mtime,shiftnotes FROM hrconjob_sm_timeslots WHERE pid = '".$hrconSno."'";
						mysql_query($que,$db);
						// Updating New Hrcon Jobs Sno to perdiem shift scheduling data.
						$updatePerdiemSch = "UPDATE hrconjob_perdiem_shift_sch SET hrconjob_sno='".$newHrconSno."' WHERE pusername='".$assgn_pusername."' ";
						mysql_query($updatePerdiemSch,$db);
						
						
							
						/////////////////// UDF migration to customers ///////////////////////////////////
						include_once('custom/custome_save.php');		
						$directEmpUdfObj = new userDefinedManuplations();
						//Changed to store the hrconjobs->sno in cdf assignments table rec_id - vilas.B
						$directEmpUdfObj->updateUDFRow(7, $hrconSno, $newHrconSno);
						/////////////////// UDF migration to customers ///////////////////////////////////
						
						$contactsno = $newHrconSno."|";
	
						$queryAssgnScheduleInsert = "INSERT INTO assignment_schedule (userid, title, startdate, enddate, contactsno, modulename, invapproved, recstatus, assign_no, cuser, muser, cdate, mdate) SELECT userid, title, startdate, enddate, '".$contactsno."', modulename, 'active', recstatus, assign_no, '".$username."', '".$username."', now(), now() FROM assignment_schedule WHERE appno = ".$appno;
						mysql_query($queryAssgnScheduleInsert, $db);
						$newAppno = mysql_insert_id($db);	
	
						$queryHrnconTab = "INSERT INTO hrcon_tab (tabsno,starthour,endhour,wdays,sch_date,coltype) SELECT '".$newAppno."',starthour,endhour,wdays,sch_date,coltype FROM hrcon_tab WHERE coltype = 'assign' AND tabsno = ".$appno;
						mysql_query($queryHrnconTab, $db);					
	
						$comm_insert	= "INSERT INTO assign_commission(username, assignid, assigntype, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput, shift_id) SELECT '".$username."', '".$newHrconSno."', 'H', person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput, shift_id FROM assign_commission WHERE assigntype = 'H' AND assignid = '".$hrconSno."'";
						mysql_query($comm_insert, $db);	

						$queryHrnconTab = "INSERT INTO empcon_tab (tabsno,starthour,endhour,wdays,sch_date,coltype) SELECT '".$newAppno."',starthour,endhour,wdays,sch_date,coltype FROM empcon_tab WHERE coltype = 'assign' AND tabsno = ".$appno;
						mysql_query($queryHrnconTab, $db);					
						
						/*Query for Inserting the Deleted commissions into his_assign_commission table*/
						
						$his_comm_insert	= "INSERT INTO his_assign_commission(sno, username, assignid, assigntype, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput, shift_id) SELECT sno, username, assignid, assigntype, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput, shift_id FROM assign_commission WHERE assignid = '".$newHrconSno."' AND assigntype = 'H'";
						mysql_query($his_comm_insert,$db);	
						
						mysql_query("DELETE FROM hrcon_tab WHERE tabsno='".$appno."' AND coltype='assign'",$db);

						// Functionality for ACA employee insertion when approving the Assignment
						if(ACA_ENABLED=='Y')
						{
							$acaDet->checkAssgnEmpDetailsforACA($newHrconSno);
						}
						
						/* This Function is used to 
							DELETE hrcon_jobs,hrconjobs_sm_timeslot,assign_commission,persons_assignment and hrcon_burden_details
							using old hrcon_jobs sno.	
						*/
						clean_up_hrcon_data($hrconSno);
					}
				}
				
				$assgnRateIds = $hrconSno."|"."^^".$contactsno;
			}
	
			if($setFlag == 1)
			{
				$jtype_qry="SELECT jtype,DATE_ADD(MAX(DATE_FORMAT(STR_TO_DATE(e_date,'%m-%d-%Y'),'%Y-%m-%d')),INTERVAL 1 DAY),MAX(DATE_FORMAT(STR_TO_DATE(if(e_date='0-0-0','00-00-0000',e_date),'%m-%d-%Y'),'%Y-%m-%d')),DATE_ADD(MAX(DATE_FORMAT(exp_edate,'%Y-%m-%d')),INTERVAL 1 DAY),MAX(DATE_FORMAT(exp_edate,'%Y-%m-%d')) FROM hrcon_jobs WHERE username = '".$conusername."' AND ustatus = 'active' GROUP BY username";
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
					if(($jtype_row[2] == "0000-00-00") || ($jtype_row[2] == ''))
					{
						if(($jtype_row[4] == "0000-00-00") || ($jtype_row[4] == '')) 
							$avail = "immediate";
						else
							$avail = $jtype_row[3];
					}
					else
					{
						$avail = $jtype_row[1];   //we will get the date in YYYY-MM-DD format
					}
				}
	
				if($candidateID==0)
				{
					$eque="SELECT sno FROM emp_list WHERE username='$conusername'";
					$eres=mysql_query($eque,$db);
					$erow=mysql_fetch_row($eres);
					$empsno=$erow[0];
	
					if($empsno>0)
					{
						$cque="SELECT sno, username FROM candidate_list WHERE candid='emp".$empsno."'";
						$cres=mysql_query($cque,$db);
						$crow=mysql_fetch_row($cres);
	
						$candidateID=$crow[0];
						$cand_username=$crow[1];
					}
				}
	
				if($getNotesType>0)
					$NotesType = getManage($getNotesType);
	
				if($getCandStatus>0)
				{
					if($cand_username!="")
					{
						if($assgnUpdateStatus=='active')
						{
							$clque="UPDATE candidate_list SET cl_status='$getCandStatus',ctype='Employee',muser='$username',mtime=NOW() WHERE username='$cand_username'";
							mysql_query($clque,$db);
						}
						else
						{
							$aque="SELECT COUNT(1) FROM hrcon_jobs WHERE ustatus='active' AND username='$conusername'";
							$ares=mysql_query($aque,$db);
							$arow=mysql_fetch_row($ares);
	
							if($arow[0]==0)
							{
								$clque="UPDATE candidate_list SET cl_status='$getCandStatus',ctype='Employee',muser='$username',mtime=NOW() WHERE username='$cand_username'";
								mysql_query($clque,$db);
							}
						}
					}
				}
	
				if($getJobStatus>0 && $posid>0)
				{
					$aque="SELECT COUNT(1) FROM hrcon_jobs WHERE ustatus='active' AND posid='$posid'";
					$ares=mysql_query($aque,$db);
					$arow=mysql_fetch_row($ares);
	
					if($arow[0]==0)
					{
						$joque="UPDATE posdesc SET posstatus='$getJobStatus',muser='$username',mdate=NOW() WHERE posid='$posid'";
						mysql_query($joque,$db);
					}
				}
	
				if(trim($getNotesNew)!="")
				{
					if($candidateID>0)
					{
						// Removes NON-ASCII characters
						$getNotesNew = preg_replace('/[^(\x20-\x7F)\n]/',' ', $getNotesNew);
						
						// Handles non-printable characters
						$getNotesNew = preg_replace('/&#[5-6][0-9]{4};/',' ', $getNotesNew);
		
						$ique="INSERT INTO notes (contactid,cuser,type,cdate,notes,notes_subtype) VALUES ($candidateID,$username,'cand',NOW(),'".addslashes($getNotesNew)."','$getNotesType')";
						mysql_query($ique,$db);
						$nid = mysql_insert_id($db);
	
						$ique="INSERT INTO cmngmt_pr (con_id,username,tysno,title,sdate,subject,lmuser,subtype) VALUES ('cand$candidateID','$username',$nid,'Notes',NOW(),'".addslashes($getNotesNew)."','$username','".addslashes($NotesType)."')";
						mysql_query($ique,$db);
					}
	
					if($posid>0)
					{
						// Removes NON-ASCII characters
						$getNotesNew = preg_replace('/[^(\x20-\x7F)\n]/',' ', $getNotesNew);
					
						// Handles non-printable characters
						$getNotesNew = preg_replace('/&#[5-6][0-9]{4};/',' ', $getNotesNew);
		
						$ique="INSERT INTO notes (contactid,cuser,type,cdate,notes,notes_subtype) VALUES ($posid,$username,'req',NOW(),'".addslashes($getNotesNew)."','$getNotesType')";
						mysql_query($ique,$db);
						$nid = mysql_insert_id($db);
	
						$ique="INSERT INTO cmngmt_pr (con_id,username,tysno,title,sdate,subject,lmuser,subtype) VALUES ('req$posid','$username',$nid,'Notes',NOW(),'".addslashes($getNotesNew)."','$username','".addslashes($NotesType)."')";
						mysql_query($ique,$db);
					}
				}
	
				$sql_profque = "UPDATE candidate_prof set availsdate='".$avail."' WHERE username='".$cand_username."'";
				mysql_query($sql_profque,$db);
	
				if($jotype=='Internal Direct')
				{
					$que="update candidate_prof set availsdate='inactive' where username='$cand_username'";
					mysql_query($que,$db);
				}
	
				if($posid!=0 && $posid)
				{
					if($assgnUpdateStatus == 'cancel')
					{
						$astatus = 'Cancelled';
					}
					else if($assgnUpdateStatus == 'closed')
					{
						$astatus = 'Closed';
					}
					else
					{
						$astatus = 'Active';
						if($jobType == "Direct")
						{
							if($assgnStatus == 'pending')
								$astatus="Closed";
							else
								$astatus="Needs Approval";
						}
					}
	
					$managesno=getManageSno($astatus,'interviewstatus');
	
					$sel_place_jobs="SELECT sno, seqnumber,e_date,shift_type,shiftid FROM placement_jobs WHERE assign_no='".$assignmentNo."' AND username='".$conusername."'";
					$res_place_jobs=mysql_query($sel_place_jobs, $db);
					$fetch_place_jobs=mysql_fetch_array($res_place_jobs);
					
					if($fetch_place_jobs[3] == "perdiem")
					{
						$placement_shift_qry = "SELECT psm.shift_id FROM placement_jobs as pjob INNER JOIN placement_perdiem_shift_sch as psm ON(pjob.sno = psm.placementjob_sno) WHERE pjob.sno = '".$fetch_place_jobs['sno']."' GROUP BY pjob.sno";
						
					}
					else
					{
						$placement_shift_qry = "SELECT psm.sm_sno FROM placement_jobs as pjob INNER JOIN placement_sm_timeslots as psm ON(pjob.sno = psm.pid) WHERE pjob.sno = '".$fetch_place_jobs['sno']."' GROUP BY pjob.sno";
						
					}
					$placement_shift_res  = mysql_query($placement_shift_qry,$db);
					
					$shift_id 	= 0;	
					
					if(mysql_num_rows($placement_shift_res) > 0){
						$shift_info_row 	=  mysql_fetch_row($placement_shift_res);
						$shift_id 			= $shift_info_row[0];
					}
					
					// Code for New shift feature for notifications
					if(SHIFT_SCHEDULING_ENABLED=='N')
					{
						$placement_shift_qry = "SELECT pjob.shiftid FROM placement_jobs as pjob WHERE pjob.sno = '".$fetch_place_jobs['sno']."'";					
						$placement_shift_res  = mysql_query($placement_shift_qry,$db);
						$shift_info_row 	=  mysql_fetch_row($placement_shift_res);
						$shift_id = $shift_info_row[0];
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
					$res_upd="UPDATE resume_status SET status='".$managesno."', muser = '".$username."', mdate = NOW() WHERE res_id='".$candidateID."' AND req_id='".$posid."' AND seqnumber='".$fetch_place_jobs['seqnumber']."' AND shift_id = '0'";
					mysql_query($res_upd,$db);
					
					$plctShiftSno = $fetch_place_jobs['shiftid'];
					if ($plctShiftSno != $shift_id) {
						$res_upd	= "UPDATE resume_status SET status='".$managesno."', muser = '".$username."', mdate = NOW() WHERE res_id='".$candidateID."' AND req_id='".$posid."' AND seqnumber='".$fetch_place_jobs['seqnumber']."' AND shift_id= '".$plctShiftSno."'";
						mysql_query($res_upd,$db);
					}
					/* END  */

				 	$res_upd="UPDATE resume_status SET status='".$managesno."', muser = '".$username."', mdate = NOW() WHERE res_id='".$candidateID."' AND req_id='".$posid."' AND seqnumber='".$fetch_place_jobs['seqnumber']."' AND shift_id = '".$shift_id."'";
					mysql_query($res_upd,$db);
	
					if($astatus == 'Cancelled' || $astatus == 'Closed'){	
						if($fetch_place_jobs['e_date'] == '0-0-0' || $fetch_place_jobs['e_date'] == ''){
								$endPlacedate = $endDate;
						}else{
							$endPlacedate = $fetch_place_jobs['e_date'];
						}
						$res_upd="UPDATE placement_jobs SET assg_status='".$astatus."',e_date='".$endPlacedate."', notes_cancel = '".$cancelNotes."',reason_id='".$reasonSno."' WHERE username='".$conusername."' and posid='".$posid."' AND candidate = '".$cand_username."' AND sno='".$fetch_place_jobs['sno']."'";
					}	
					else{
						$res_upd="UPDATE placement_jobs SET assg_status='".$astatus."' WHERE username='".$conusername."' and posid='".$posid."' AND candidate = '".$cand_username."' AND sno='".$fetch_place_jobs['sno']."'";
					}
					mysql_query($res_upd,$db);
				}
	
				$expassgnRateIds =  explode("^^",$assgnRateIds);				
				$backupRatesIds = explode("|",$expassgnRateIds[0]);
				$newRatesIds = explode("|",$expassgnRateIds[1]);
				$mode_rate_type = "hrcon";
				$type_order_id = $newRatesIds[0];
				if($assgnStatus != 'pending')
					$ratesObj->insertRatesSelAsgn($backupRatesIds[0],'hrcon');
				
	
				if($newRatesIds[0]!="")
				{
					$mode_rate_type = "hrcon";
					$type_order_id = $newRatesIds[0];
					$ratesObj->insertRatesSelAsgn($backupRatesIds[0],'hrcon');
				}
	
				$mode_rate_type = "hrcon";
				$type_order_id = $backupRatesIds[0];
				$ratesObj->doBackupExistingRates();
	
				if(PAYROLL_PROCESS_BY_MADISON == 'MADISON' && $madisonassid != '')
					UpdateAssigmentData($conusername,$madisonassid);
	
				//inserting/updating the employee time slots shift status available<->busy based on the matched shifts
				$getTFTableName = "";
				$getTFJobTableName = "";
				$timeslotvalues = "";
				if($assgnUpdateStatus=="closed" || $assgnUpdateStatus=="cancel" || $assgnUpdateStatus=="active")
				{
					$getTFTableName = "hrconjob_sm_timeslots";
					$getTFJobTableName = "hrcon_jobs";
				}
				
				if($getTFTableName != "" && $getTFJobTableName != "")
				{
	
					$get_pid_sql = "SELECT sno,schedule_display,e_date FROM ".$getTFJobTableName." WHERE username = '".$conusername."' AND assign_no = '".$assignmentNo."'";
					$get_pid_res	= mysql_query($get_pid_sql, $db);
					$get_pid_row=mysql_fetch_row($get_pid_res);
					
					$TFJobPid = $get_pid_row[0];
					
					if($TFJobPid != "" && $get_pid_row[1] == 'NEW')
					{				
						//GETTIGN SM AVAILABILITY TIMNEFRAME DETAILS			
						$previousDate = date("Y-m-d",strtotime("-2 months",strtotime(date("Y-m-d"))));
						$timeslotvalues = "";
						//Get the assignment end date
						$asg_e_date = "";						
						if($setAllEndDate == 'Y' || $get_pid_row[2] == '0-0-0' || $get_pid_row[2] == '') // if override the selected date is checked then consider that date
						{
							$asg_e_date = $endDateCheck;
						}						
						else
						{
							$tmpEDate = explode("-",$get_pid_row[2]);
							$asg_e_date = $tmpEDate[2]."-".sprintf("%02d",$tmpEDate[0])."-".sprintf("%02d",$tmpEDate[1]);
						}
						
						if($asg_e_date == "")
						{
							$asg_e_date = date("Y-m-d",strtotime("-2 months",strtotime(date("Y-m-d"))));
						}
						$assignment_e_date = date("Y-m-d",strtotime($asg_e_date));
						
						$selectShiftType = "SELECT shift_type FROM hrcon_jobs WHERE pusername='".$assignmentNo."'";
						$resultShiftType = mysql_query($selectShiftType,$db);
						$rowShiftType = mysql_fetch_row($resultShiftType);
						
						if ($rowShiftType[0] == "perdiem" ) {
							$select = "SELECT hpss.shift_startdate AS startDate,hpss.shift_starttime AS startTime,
							hpss.shift_enddate AS endDate,hpss.shift_endtime AS endTime,hpss.split_shift AS splitShift,
							hpss.no_of_shift_position AS noOfShifts,hpss.shift_id AS shiftSno,ss.shiftname AS shiftName,
							ss.shiftcolor AS shiftColor
					 		FROM hrconjob_perdiem_shift_sch hpss 
					 		LEFT JOIN shift_setup ss ON (ss.sno = hpss.shift_id)
					 		WHERE hpss.pusername='".$assignmentNo."' AND hpss.shift_startdate >= '".$assignment_e_date."' 
					 		ORDER BY hpss.shift_startdate ASC ";
							$resultSel = mysql_query($select,$db);
							$hrmPerdiemShiftSchObj->oldShiftStrAry = array();
							while ($rowSel = mysql_fetch_array($resultSel)) {
								 $hrmPerdiemShiftSchObj->prepareOldShiftStrFromPerdiemSelQry($rowSel,$conusername,$assignmentNo);
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
											FROM  ".$getTFTableName."
											WHERE pid = '".$TFJobPid."' AND shift_date >= '".$assignment_e_date."'
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

						if($assgnUpdateStatus=="closed" || $assgnUpdateStatus=="cancel")
						{
	
							$objScheduleDetails->updAvailableEmpTimeSlots($username, $conusername, $timeslotvalues, $assignmentNo, $assignment_e_date);
							//Dumping data into Candidate Shift Schedule from Employee Shift Schedule from the end date to future dates only.
	
							$objScheduleDetails->deleteNInsertDataIntoCandidateFromEmployeeScheduleTable($emp_listsno, $conusername,$assignment_e_date);
						}
						else if($assgnUpdateStatus=="active")
						{
							if($jobType == "Direct")
							{
								if($hcloseasgn != "Y")
								{
									$objScheduleDetails->updAvailableEmpTimeSlots($username, $conusername, $timeslotvalues, $assignmentNo,$assignment_e_date); 
									//Dumping data into Candidate Shift Schedule from Employee Shift Schedule from previous date to future dates only.
									$objScheduleDetails->deleteNInsertDataIntoCandidateFromEmployeeScheduleTable($emp_listsno, $conusername,$assignment_e_date);
								}
							}
							else
							{
								$objScheduleDetails->updBusyEmpTimeSlots($username, $conusername, $timeslotvalues, $assignmentNo);
								//Dumping data into Candidate Shift Schedule from Employee Shift Schedule from the last date to future dates only.
								$previousDate = date("Y-m-d",strtotime("-2 months",strtotime(date("Y-m-d"))));
								$objScheduleDetails->deleteNInsertDataIntoCandidateFromEmployeeScheduleTable($emp_listsno, $conusername,$previousDate);
							}
						}
						
						if ($rowShiftType[0] == "perdiem" ) {
							$hrmPerdiemShiftSchObj->oldShiftStrAry = array();
							$hrmPerdiemShiftSchObj->prepareCancelReassignShiftStrFromPerdiemSelQry($assignmentNo,$conusername);
							$timeSlotsData = implode("|", $hrmPerdiemShiftSchObj->oldShiftStrAry);
							if ($timeSlotsData !="") {
								$objScheduleDetails->updAvailableEmpForDeletedTimeSlots($username, $conusername, $timeSlotsData, $assignmentNo);
							}
						}
						
					} // end if posid not empty
					
				} // end of time frame updating 
			}
		}
		/* Updating the Employee Compensation data for the terminated employees */
		if(!empty($getTerminatedEmpUserName) && !empty($getTerminatedEmpDate)) {
	
			$expTerminatedEmpUserName = explode(",",$getTerminatedEmpUserName);
			$uniqueTerminatedEmpUserName = array_unique($expTerminatedEmpUserName);
			$cntTerminatedEmpUserName = count($uniqueTerminatedEmpUserName);
	
			for($k=0; $k < $cntTerminatedEmpUserName; $k++) {
				
				$qrySelHrconCompen = "SELECT sno FROM hrcon_compen WHERE ustatus='active' AND username='".$uniqueTerminatedEmpUserName[$k]."'";
				$resSelHrconCompen = mysql_query($qrySelHrconCompen, $db);
	
				if(mysql_num_rows($resSelHrconCompen) > 0) {
					
					$rowSelHrconCompen = mysql_fetch_array($resSelHrconCompen);
	
					/* Making the active record as backup */
					$qryUpdHrconCompen = "UPDATE hrcon_compen SET ustatus='backup', udate=NOW() WHERE sno = '".$rowSelHrconCompen[0]."' AND ustatus='active'";
					mysql_query($qryUpdHrconCompen, $db);
	
					/* Inserting and Updating data into hrcon_compen table for maintaining the history in Employee >> Compensation tab */
				$qryInsHrconCompen = "INSERT INTO hrcon_compen(username, emp_id, dept, date_hire, location, status, salary, std_hours, over_time, rev_period, increment, bonus, designation, salper, shper, effect_date, ustatus, udate, emptype, job_type, emp_crm, pay_assign, benchrate, benchperiod, benchcurrency, ot_period, ot_currency, timesheet, posworkhr, double_rate_amt, double_brate_curr, double_rate_period, assign_double, sep_check, assign_bench, assign_overtime, diem_lodging, diem_mie, diem_total, diem_currency, diem_period, diem_billable, diem_taxable, diem_pay_assign, diem_billrate, bonus_billable, bonus_billrate, wcomp_code, assign_wcompcode, classid, emp_rehire_date, emp_terminate_date, paygroupcode, paycodesno,modified_user,company_id) SELECT username, emp_id, dept, date_hire, location, status, salary, std_hours, over_time, rev_period, increment, bonus, designation, salper, shper, effect_date, 'active', NOW(), emptype, job_type, emp_crm, pay_assign, benchrate, benchperiod, benchcurrency, ot_period, ot_currency, timesheet, posworkhr, double_rate_amt, double_brate_curr, double_rate_period, assign_double, sep_check, assign_bench, assign_overtime, diem_lodging, diem_mie, diem_total, diem_currency, diem_period, diem_billable, diem_taxable, diem_pay_assign, diem_billrate, bonus_billable, bonus_billrate, wcomp_code, assign_wcompcode, classid, '', '".$getTerminatedEmpDate."', paygroupcode, paycodesno,'".$username."',company_id FROM hrcon_compen WHERE ustatus='backup' AND sno='".$rowSelHrconCompen[0]."'";
					mysql_query($qryInsHrconCompen, $db);
			
					/* Deleting and Inserting data into empcon_compen table for Employee >> Compensation tab */
					$qryDelEmpconCompen = "DELETE FROM empcon_compen WHERE username='".$uniqueTerminatedEmpUserName[$k]."'";
					mysql_query($qryDelEmpconCompen, $db);
			
				$qryInsEmpconCompen = "INSERT INTO empcon_compen(username, emp_id, dept, date_hire, location, status, salary, std_hours, over_time, rev_period, increment, bonus, designation, salper, shper, emptype, job_type, emp_crm, pay_assign, benchrate, benchperiod, benchcurrency, ot_period, ot_currency, timesheet, posworkhr, double_rate_amt, double_brate_curr, double_rate_period, assign_double, sep_check, assign_bench, assign_overtime, diem_lodging, diem_mie, diem_total, diem_currency, diem_period, diem_billable, diem_taxable, diem_pay_assign, diem_billrate, bonus_billable, bonus_billrate, wcomp_code, assign_wcompcode, classid, emp_rehire_date, emp_terminate_date, paygroupcode, paycodesno) SELECT username, emp_id, dept, date_hire, location, status, salary, std_hours, over_time, rev_period, increment, bonus, designation, salper, shper, emptype, job_type, emp_crm, pay_assign, benchrate, benchperiod, benchcurrency, ot_period, ot_currency, timesheet, posworkhr, double_rate_amt, double_brate_curr, double_rate_period, assign_double, sep_check, assign_bench, assign_overtime, diem_lodging, diem_mie, diem_total, diem_currency, diem_period, diem_billable, diem_taxable, diem_pay_assign, diem_billrate, bonus_billable, bonus_billrate, wcomp_code, assign_wcompcode, classid, emp_rehire_date, emp_terminate_date, paygroupcode, paycodesno FROM hrcon_compen WHERE ustatus='active' AND username='".$uniqueTerminatedEmpUserName[$k]."'";
					mysql_query($qryInsEmpconCompen, $db);
				}
			}
		}
		
		/* Updating the Employee Compensation data for the Re-Hired employees */
		if(!empty($getReHiredEmpUserName)) {
	
			$expReHiredEmpUserName = explode(",",$getReHiredEmpUserName);
			$uniqueReHiredEmpUserName = array_unique($expReHiredEmpUserName);
			$cntReHiredEmpUserName = count($uniqueReHiredEmpUserName);
	
			for($k=0; $k < $cntReHiredEmpUserName; $k++) {
				
				$qrySelHrconCompen = "SELECT sno FROM hrcon_compen WHERE ustatus='active' AND username='".$uniqueReHiredEmpUserName[$k]."'";
				$resSelHrconCompen = mysql_query($qrySelHrconCompen, $db);
	
				if(mysql_num_rows($resSelHrconCompen) > 0) {
					
					$rowSelHrconCompen = mysql_fetch_array($resSelHrconCompen);
	
					/* Making the active record as backup */
					$qryUpdHrconCompen = "UPDATE hrcon_compen SET ustatus='backup', udate=NOW() WHERE sno = '".$rowSelHrconCompen[0]."' AND ustatus='active'";
					mysql_query($qryUpdHrconCompen, $db);
	
					/* Inserting and Updating data into hrcon_compen table for maintaining the history in Employee >> Compensation tab */
				$qryInsHrconCompen = "INSERT INTO hrcon_compen(username, emp_id, dept, date_hire, location, status, salary, std_hours, over_time, rev_period, increment, bonus, designation, salper, shper, effect_date, ustatus, udate, emptype, job_type, emp_crm, pay_assign, benchrate, benchperiod, benchcurrency, ot_period, ot_currency, timesheet, posworkhr, double_rate_amt, double_brate_curr, double_rate_period, assign_double, sep_check, assign_bench, assign_overtime, diem_lodging, diem_mie, diem_total, diem_currency, diem_period, diem_billable, diem_taxable, diem_pay_assign, diem_billrate, bonus_billable, bonus_billrate, wcomp_code, assign_wcompcode, classid, emp_rehire_date, emp_terminate_date, paygroupcode, paycodesno,modified_user,company_id) SELECT username, emp_id, dept, date_hire, location, status, salary, std_hours, over_time, rev_period, increment, bonus, designation, salper, shper, effect_date, 'active', NOW(), emptype, job_type, emp_crm, pay_assign, benchrate, benchperiod, benchcurrency, ot_period, ot_currency, timesheet, posworkhr, double_rate_amt, double_brate_curr, double_rate_period, assign_double, sep_check, assign_bench, assign_overtime, diem_lodging, diem_mie, diem_total, diem_currency, diem_period, diem_billable, diem_taxable, diem_pay_assign, diem_billrate, bonus_billable, bonus_billrate, wcomp_code, assign_wcompcode, classid, NOW(), '', paygroupcode, paycodesno,'".$username."',company_id FROM hrcon_compen WHERE ustatus='backup' AND sno='".$rowSelHrconCompen[0]."'";
					mysql_query($qryInsHrconCompen, $db);
			
					/* Deleting and Inserting data into empcon_compen table for Employee >> Compensation tab */
					$qryDelEmpconCompen = "DELETE FROM empcon_compen WHERE username='".$uniqueReHiredEmpUserName[$k]."'";
					mysql_query($qryDelEmpconCompen, $db);
			
				$qryInsEmpconCompen = "INSERT INTO empcon_compen(username, emp_id, dept, date_hire, location, status, salary, std_hours, over_time, rev_period, increment, bonus, designation, salper, shper, emptype, job_type, emp_crm, pay_assign, benchrate, benchperiod, benchcurrency, ot_period, ot_currency, timesheet, posworkhr, double_rate_amt, double_brate_curr, double_rate_period, assign_double, sep_check, assign_bench, assign_overtime, diem_lodging, diem_mie, diem_total, diem_currency, diem_period, diem_billable, diem_taxable, diem_pay_assign, diem_billrate, bonus_billable, bonus_billrate, wcomp_code, assign_wcompcode, classid, emp_rehire_date, emp_terminate_date, paygroupcode, paycodesno) SELECT username, emp_id, dept, date_hire, location, status, salary, std_hours, over_time, rev_period, increment, bonus, designation, salper, shper, emptype, job_type, emp_crm, pay_assign, benchrate, benchperiod, benchcurrency, ot_period, ot_currency, timesheet, posworkhr, double_rate_amt, double_brate_curr, double_rate_period, assign_double, sep_check, assign_bench, assign_overtime, diem_lodging, diem_mie, diem_total, diem_currency, diem_period, diem_billable, diem_taxable, diem_pay_assign, diem_billrate, bonus_billable, bonus_billrate, wcomp_code, assign_wcompcode, classid, emp_rehire_date, emp_terminate_date, paygroupcode, paycodesno FROM hrcon_compen WHERE ustatus='active' AND username='".$uniqueReHiredEmpUserName[$k]."'";
					mysql_query($qryInsEmpconCompen, $db);
				}
			}
		}
		
		/* Closing all active assignments when the option Cancel or Close is selected in assignment update status */
		if($hcloseasgn=="Y" && $getSelectedAssignUserName != "" && ($assgnStatus == 'approved' || $assgnStatus == 'pending')) {
			
			$expSelectedAssignUserName = explode(",",$getSelectedAssignUserName);
			$uniqueSelectedAssignUserName = array_unique($expSelectedAssignUserName);
			$cntSelectedAssignUserName = count($uniqueSelectedAssignUserName);
			
			for($j=0; $j < $cntSelectedAssignUserName; $j++) {
				closeCancelOrCloseJobAssignments($henddate, $uniqueSelectedAssignUserName[$j], $getAssignHrconIds, $getAssignEmpconIds,$objScheduleDetails,$objSchSchedules,$reasonSno,$hrmPerdiemShiftSchObj);
			}
		}
	
	
		if(DEFAULT_SYNCHR=="Y")
			syncHR_Data($conusername);
	
		if($flagLogout == 1)
		{
			$sess_id=session_id();
			$session_dir=ini_get("session.save_path");
			unlink($session_dir."/sess_".$sess_id);
			delFolder($WDOCUMENT_ROOT);
			clearCookies($_COOKIE,"");
			foreach($_SESSION as $key=>$value  )
				unset($_SESSION[$key]);
	
			echo "logout";
		}
		else
		{
			echo "none";
		}
	}

function fnsetAssignmentStatus($appno, $hrconSno, $assgn_pusername, $assgnStatus, $assgnUpdateStatus, $projectStatus, $jobType, $conusername, $assgnment_ownerid,  $cancelNotes, $endDate, $orgProjectStatus, $assignmentNo, $startDate, $setAllEndDate,$shift_id=0,$reasonSno=0)
{
	global $maildb,$db,$username;

	// Class For ACA Information
	$acaDet = new Aca();

	if($hrconSno && $hrconSno > 0)
	{
				
		$get_hishrcon_jobs	= "SELECT sno FROM his_hrcon_jobs WHERE pusername = '".$assgn_pusername."' AND ustatus IN ('active','closed','cancel','pending') ORDER BY mdate DESC LIMIT 0,1";
		$res_hishrcon_jobs 	= mysql_query($get_hishrcon_jobs,$db);
		$row_hishrcon_jobs 	= mysql_fetch_row($res_hishrcon_jobs);
		$hishrcon_jobs_sno1	= $row_hishrcon_jobs[0];	

		/* 
			Updateing hrcon_jobs pusername with _tmp.
			Because we need to re-create the hrcon_jobs and its related tables using old hrcon_jobs sno 
			with respective status which is passed by user.
		*/

		$updateTempPusername = "UPDATE hrcon_jobs SET pusername=CONCAT(pusername,'_tmp') WHERE sno='".$hrconSno."' AND `pusername`='".$assgn_pusername."'";
		mysql_query($updateTempPusername, $db);

		/*Query for Inserting the hrcon person Details into his_persons_assignment table*/		
		$his_person_insert	= "INSERT INTO his_persons_assignment(asgnid,asgn_mode,person_id,cuser,cdate,muser,mdate) SELECT '".$hishrcon_jobs_sno1."','hrcon', person_id, cuser, cdate,'".$username."',NOW() FROM persons_assignment WHERE asgnid = '".$hrconSno."' AND asgn_mode='hrcon' ";
		mysql_query($his_person_insert,$db);
		
		/*Query for Inserting the hrcon burden details into his_hrcon_burden_details table*/
		$OldBudenHis = "SELECT sno FROM his_hrcon_burden_details WHERE hrcon_jobs_sno ='".$hishrcon_jobs_sno1."'";
        $OldBudenResultHis = mysql_query($OldBudenHis);
            
        if(mysql_num_rows($OldBudenResultHis)==0){		
			$his_comm_insert	= "INSERT INTO his_hrcon_burden_details(sno, hrcon_jobs_sno, ratemasterid, ratetype, bt_id, bi_id) SELECT sno, '".$hishrcon_jobs_sno1."', ratemasterid, ratetype, bt_id, bi_id FROM hrcon_burden_details WHERE hrcon_jobs_sno = '".$hrconSno."'";
			mysql_query($his_comm_insert,$db);
		}
	}

	if($jobType == "Direct" && $assgnUpdateStatus=="active")
	{
		$endDate = " IF(e_date='0-0-0','".$endDate."',e_date) ";
	}
	else
	{
		if($setAllEndDate=="Y")
			$endDate = ($endDate!="")  ? "'".$endDate."'" : "e_date";
		else
			$endDate = " IF(e_date='0-0-0' || e_date='','".$endDate."',e_date) ";
	}

	$assgnUpdateStatus = ($jobType == "Direct" && $assgnUpdateStatus == "active") ? "closed" : $assgnUpdateStatus;

	$queryInsertHrconJobs="INSERT INTO hrcon_jobs ( `username`, `client`, `project`, `manager`, `s_date`, `e_date`, `exp_edate`,`rate`, `tsapp`, `jtype`, `pusername`, `rateper`, `otrate`, `ustatus`, `udate`, `imethod`, `iterms`, `pterms`, `sagent`, `commision`, `co_type`, `endclient`, `tweeks`, `tdays`, `notes`, `rtime`, `assg_status`, `posid`, `jotype`, `catid`, `postitle`, `refcode`, `posstatus`, `vendor`, `contact`, `candidate`, `hired_date`, `posworkhr`, `timesheet`, `bamount`, `bcurrency`, `bperiod`, `pamount`, `pcurrency`, `pperiod`, `emp_prate`, `reason`, `rateperiod`, `ot_period`, `ot_currency`, `placement_fee`, `placement_curr`, `bill_contact`, `bill_address`, `wcomp_code`, `bill_req`, `service_terms`, `hire_req`, `addinfo`, `avg_interview`, `calctype`, `burden`, `markup`, `margin`, `brateopen`, `brateopen_amt`, `prateopen`, `prateopen_amt`, `joblocation`, `owner`, `cdate`, `otbrate_amt`, `otbrate_curr`, `otbrate_period`, `otprate_amt`, `otprate_curr`, `otprate_period`, `payrollpid`, `notes_cancel`, `offlocation`, `double_brate_amt`, `double_brate_curr`, `double_brate_period`, `double_prate_amt`, `double_prate_curr`, `double_prate_period`, `po_num`, `job_loc_tax`, `department`, `muser`, `mdate`, `date_ended`, `date_placed`, `diem_lodging`, `diem_mie`, `diem_total`, `diem_currency`, `diem_period`, `diem_billable`, `diem_taxable`, `diem_billrate`, `madison_order_id`, `assign_no`, `classid`, `deptid`, `attention`, `vprt_geocode`, `vprt_state`, `vprt_county`, `vprt_local`, `vprt_schdist`, `corp_code`, `industryid`, `schedule_display`, `bill_burden`, `shiftid`, `worksite_code`, `copy_asignid`,`starthour`,`endhour`,`reason_id`,shift_type,daypay,worksite_id,licode,federal_payroll,nonfederal_payroll,contractid,classification,ts_layout_pref)SELECT  `username`, `client`, `project`, `manager`, '".$startDate."',".$endDate.", `exp_edate`,`rate`, `tsapp`,'".$projectStatus."', '".$assgn_pusername."', `rateper`, `otrate`, '".$assgnUpdateStatus."', `udate`, `imethod`, `iterms`, `pterms`, `sagent`, `commision`, `co_type`, `endclient`, `tweeks`, `tdays`, `notes`, `rtime`, `assg_status`, `posid`, `jotype`, `catid`, `postitle`, `refcode`, `posstatus`, `vendor`, `contact`, `candidate`, `hired_date`, `posworkhr`, `timesheet`, `bamount`, `bcurrency`, `bperiod`, `pamount`, `pcurrency`, `pperiod`, `emp_prate`, `reason`, `rateperiod`, `ot_period`, `ot_currency`, `placement_fee`, `placement_curr`, `bill_contact`, `bill_address`, `wcomp_code`, `bill_req`, `service_terms`, `hire_req`, `addinfo`, `avg_interview`, `calctype`, `burden`, `markup`, `margin`, `brateopen`, `brateopen_amt`, `prateopen`, `prateopen_amt`, `joblocation`, `owner`, `cdate`, `otbrate_amt`, `otbrate_curr`, `otbrate_period`, `otprate_amt`, `otprate_curr`, `otprate_period`, `payrollpid`, '".$cancelNotes."', `offlocation`, `double_brate_amt`, `double_brate_curr`, `double_brate_period`, `double_prate_amt`, `double_prate_curr`, `double_prate_period`, `po_num`, `job_loc_tax`, `department`, '".$username."', NOW(), `date_ended`, `date_placed`, `diem_lodging`, `diem_mie`, `diem_total`, `diem_currency`, `diem_period`, `diem_billable`, `diem_taxable`, `diem_billrate`, `madison_order_id`, '".$assgn_pusername."', `classid`, `deptid`, `attention`, `vprt_geocode`, `vprt_state`, `vprt_county`, `vprt_local`, `vprt_schdist`, `corp_code`, `industryid`, `schedule_display`, `bill_burden`, `shiftid`, `worksite_code`, `copy_asignid`,`starthour`,`endhour`,'".$reasonSno."',shift_type,daypay,`worksite_id`,licode,federal_payroll,nonfederal_payroll,contractid,classification,ts_layout_pref
		FROM hrcon_jobs WHERE sno='".$hrconSno."'";
	mysql_query($queryInsertHrconJobs, $db);
	$newHrconSno = mysql_insert_id($db);

	if($assgnUpdateStatus== 'closed' || $assgnUpdateStatus== 'cancel'){
		$query  =  "UPDATE emp_list e, hrcon_jobs hj SET e.emp_jtype='', e.cur_project = hj.project, e.rate = hj.pamount  WHERE e.username=hj.username AND hj.pusername='".$assgn_pusername."' ";

    		mysql_query($query,$db);
	}
	if($assgnUpdateStatus == 'active'){
	  $query  =  "UPDATE emp_list e, hrcon_jobs hj SET e.emp_jtype=hj.jtype, e.cur_project = hj.project, e.rate = hj.pamount  WHERE e.username=hj.username AND hj.pusername='".$assgn_pusername."' ";
	  mysql_query($query,$db);
	}
	

	$insBurdenDetils = "INSERT INTO hrcon_burden_details(hrcon_jobs_sno,ratemasterid,ratetype,bt_id,bi_id) SELECT ".$newHrconSno.",ratemasterid,ratetype,bt_id,bi_id FROM hrcon_burden_details WHERE hrcon_jobs_sno = ".$hrconSno;
    	mysql_query($insBurdenDetils, $db);

	$his_person_insert	= "INSERT INTO persons_assignment(asgnid,asgn_mode,person_id,cuser,cdate,muser,mdate) SELECT '".$newHrconSno."','hrcon', person_id, cuser, cdate,'".$username."',NOW() FROM persons_assignment WHERE asgnid = '".$hrconSno."' AND asgn_mode='hrcon'";
	mysql_query($his_person_insert,$db);
	//inserting the time frame details from empcon jobs to hrcon jobs when reactivated
	$que="INSERT INTO hrconjob_sm_timeslots(sno, pid, shift_date, shift_starttime, shift_endtime, event_type, event_no, event_group_no, shift_status, sm_sno, no_of_positions, cuser, ctime, muser, mtime,shiftnotes) SELECT '', '".$newHrconSno."', shift_date, shift_starttime, shift_endtime, event_type, event_no, event_group_no, shift_status, sm_sno, no_of_positions, cuser, ctime, muser, mtime,shiftnotes FROM hrconjob_sm_timeslots WHERE pid = '".$hrconSno."'";
	mysql_query($que,$db);

	// Updating New Hrcon Jobs Sno to perdiem shift scheduling data.
	$updatePerdiemSch = "UPDATE hrconjob_perdiem_shift_sch SET hrconjob_sno='".$newHrconSno."' WHERE pusername='".$assgn_pusername."' ";
	mysql_query($updatePerdiemSch,$db);
	
	/////////////////// UDF migration to customers ///////////////////////////////////
	include_once('custom/custome_save.php');		
	$directEmpUdfObj = new userDefinedManuplations();
	//Changed to store the hrconjobs->sno in cdf assignments table rec_id - vilas.B
	$directEmpUdfObj->updateUDFRow(7, $hrconSno, $newHrconSno);
	/////////////////// UDF migration to customers ///////////////////////////////////	
	
	$contactsno = $newHrconSno."|";

	$queryAssgnSechuleUpdate = "UPDATE assignment_schedule SET invapproved='backup', cuser ='".$username."', muser='".$username."', mdate=now() WHERE appno = ".$appno;
	mysql_query($queryAssgnSechuleUpdate, $db);

	$queryAssgnScheduleInsert = "INSERT INTO assignment_schedule (userid, title, startdate, enddate, contactsno, modulename, invapproved, recstatus, assign_no, cuser, muser, cdate, mdate) SELECT userid, title, startdate, enddate, '".$contactsno."', modulename, '".$assgnUpdateStatus."', recstatus, '".$assignmentNo."', '".$username."', '".$username."', now(), now() FROM assignment_schedule WHERE appno = ".$appno;
	mysql_query($queryAssgnScheduleInsert, $db);
	$newAppno = mysql_insert_id($db);	

	$queryHrnconTab = "INSERT INTO hrcon_tab (tabsno,starthour,endhour,wdays,sch_date,coltype) SELECT '".$newAppno."',starthour,endhour,wdays,sch_date,coltype FROM hrcon_tab WHERE coltype = 'assign' AND tabsno = ".$appno;
	mysql_query($queryHrnconTab, $db);
	
	$comm_insert	= "INSERT INTO assign_commission(username, assignid, assigntype, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput,shift_id) SELECT '".$username."', '".$newHrconSno."', 'H', person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput,shift_id FROM assign_commission WHERE assigntype = 'H' AND assignid = '".$hrconSno."'";
	mysql_query($comm_insert, $db);	
	
	/*Query for Inserting the commissions into his_assign_commission table*/
	
	$his_comm_insert	= "INSERT INTO his_assign_commission(username, assignid, assigntype, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput,shift_id) SELECT username, assignid, assigntype, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput,shift_id FROM assign_commission WHERE assignid = '".$hrconSno."' AND assigntype = 'H'";
	mysql_query($his_comm_insert,$db);
	
	mysql_query("DELETE FROM hrcon_tab WHERE tabsno='".$appno."' AND coltype='assign'",$db);

	// Functionality for ACA employee insertion when approving the Assignment
	if(ACA_ENABLED=='Y')
	{
		$acaDet->checkAssgnEmpDetailsforACA($newHrconSno);
	}

	/* This Function is used to 
		DELETE hrcon_jobs,hrconjobs_sm_timeslot,assign_commission,persons_assignment and hrcon_burden_details	
		using old hrcon_jobs sno.	
	*/
	clean_up_hrcon_data($hrconSno);

	return $hrconSno."|^^".$contactsno;
}

/* Closing all active assignments when the option Cancel or Close is selected in assignment update status */
function closeCancelOrCloseJobAssignments($enddate, $conusername, $getAssignHrconIds, $getAssignEmpconIds,$objScheduleDetails,$objSchSchedules,$reasonSno,$hrmPerdiemShiftSchObj)
{
	global $maildb,$db,$username;
	
	$concatVar="";
	$appno_tab="";
	$empcon_str="";
	$hrcon_str="";
	
	if(!empty($getAssignHrconIds))
		$sel_hrcon="SELECT sno FROM hrcon_jobs WHERE ustatus='active' AND username='".$conusername."' AND jtype='OP' AND sno NOT IN (".$getAssignHrconIds.")";
	else
		$sel_hrcon="SELECT sno FROM hrcon_jobs WHERE ustatus='active' AND username='".$conusername."' AND jtype='OP'";

	$res_hrcon=mysql_query($sel_hrcon,$db);
	while($fetch_hrcon=mysql_fetch_row($res_hrcon))
	{
		$sel_tab="SELECT appno, contactsno FROM assignment_schedule WHERE userid='".$conusername."' AND modulename='HR->Assignments' AND contactsno like'".$fetch_hrcon[0]."|%' AND invapproved='active'";
		$res_tab=mysql_query($sel_tab,$db);

		$fetch_tab=mysql_fetch_row($res_tab);
		$temphr_sno = explode("|",$fetch_tab[1]);

		$appno_tab.=$concatVar.$fetch_tab[0];
		$hrcon_str.=$concatVar.$temphr_sno[0];

		$concatVar=",";
	}

	if($hrcon_str!="")
	{
		$query = "Select sno from emp_list where username=".$conusername;
		$result_query = mysql_query($query,$db);
		$result = mysql_fetch_row($result_query);		
		$emp_sno_val = $result[0];
		
		if($enddate=="")
			$todayDate = date("m-d-Y");
		else
			$todayDate = $enddate;

		$que="UPDATE hrcon_jobs SET ustatus='closed',muser='$username',mdate=NOW(),date_ended=NOW(),e_date = IF(e_date='0-0-0' || e_date='','".$todayDate."',e_date),reason_id='".$reasonSno."' WHERE username='".$conusername."' AND ustatus='active' AND sno NOT IN (".$getAssignHrconIds.")";
		mysql_query($que,$db);

		$pque="SELECT posid, candidate, assign_no,e_date,sno,shift_type FROM hrcon_jobs WHERE username='".$conusername."' AND ustatus='closed' AND sno NOT IN (".$getAssignHrconIds.")";
		$pres=mysql_query($pque,$db);
		while($prow=mysql_fetch_row($pres))
		{
			$posid=$prow[0];
			$candidateID=$prow[1];
			$cand_username="cand".$prow[1];
			$assignmentNo=$prow[2];
			$tmpEDate = explode("-",$prow[3]);
			$asg_e_date = $tmpEDate[2]."-".sprintf("%02d",$tmpEDate[0])."-".sprintf("%02d",$tmpEDate[1]);//get the end date from the hrcon jobs table
			$assignment_e_date = date("Y-m-d",strtotime($asg_e_date));
			$TFJobPid = $prow[4];
			if ($prow[5] == "perdiem" ) {
				$select = "SELECT hpss.shift_startdate AS startDate,hpss.shift_starttime AS startTime,
				hpss.shift_enddate AS endDate,hpss.shift_endtime AS endTime,hpss.split_shift AS splitShift,
				hpss.no_of_shift_position AS noOfShifts,hpss.shift_id AS shiftSno,ss.shiftname AS shiftName,
				ss.shiftcolor AS shiftColor
		 		FROM hrconjob_perdiem_shift_sch hpss 
		 		LEFT JOIN shift_setup ss ON (ss.sno = hpss.shift_id)
		 		WHERE hpss.pusername='".$assignmentNo."' AND hpss.shift_startdate >= '".$assignment_e_date."' 
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
											WHERE pid = '".$TFJobPid."' AND shift_date >= '".$assignment_e_date."'
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
			$objScheduleDetails->updAvailableEmpTimeSlots($username, $conusername, $timeslotvalues, $assignmentNo, $assignment_e_date);
			//Dumping data into Candidate Shift Schedule from Employee Shift Schedule from the end date to future dates only.

			$objScheduleDetails->deleteNInsertDataIntoCandidateFromEmployeeScheduleTable($emp_sno_val, $conusername,$assignment_e_date);

			if($posid!=0 && $posid)
			{
				$astatus = 'Closed';
				$managesno=getManageSno($astatus,'interviewstatus');

				$sel_place_jobs="SELECT sno, seqnumber,shift_type,shiftid FROM placement_jobs WHERE assign_no='".$assignmentNo."' AND username='".$conusername."'";
				$res_place_jobs=mysql_query($sel_place_jobs, $db);
				$fetch_place_jobs=mysql_fetch_array($res_place_jobs);
				
				if($fetch_place_jobs[2] == "perdiem")
				{
					$placement_shift_qry = "SELECT psm.shift_id FROM placement_jobs as pjob INNER JOIN placement_perdiem_shift_sch as psm ON(pjob.sno = psm.placementjob_sno) WHERE pjob.sno = '".$fetch_place_jobs['sno']."' GROUP BY pjob.sno";					
				}				
				else
				{
					$placement_shift_qry = "SELECT psm.sm_sno FROM placement_jobs as pjob INNER JOIN placement_sm_timeslots as psm ON(pjob.sno = psm.pid) WHERE pjob.sno = '".$fetch_place_jobs['sno']."' GROUP BY pjob.sno";
					
				}

				$placement_shift_res  = mysql_query($placement_shift_qry,$db);	

				$shift_id 	= 0;	
				
				if(mysql_num_rows($placement_shift_res) > 0){
					$shift_info_row 	= mysql_fetch_row($placement_shift_res);
					$shift_id			= $shift_info_row[0];
				}

				// Code for New shift feature for notifications
				if(SHIFT_SCHEDULING_ENABLED=='N')
				{
					$placement_shift_qry = "SELECT pjob.shiftid FROM placement_jobs as pjob WHERE pjob.sno = '".$fetch_place_jobs['sno']."'";					
					$placement_shift_res  = mysql_query($placement_shift_qry,$db);
					$shift_info_row 	=  mysql_fetch_row($placement_shift_res);
					$shift_id = $shift_info_row[0];
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
				$res_upd="UPDATE resume_status SET status='".$managesno."', muser = '".$username."', mdate = NOW() WHERE res_id='".$candidateID."' AND req_id='".$posid."' AND seqnumber='".$fetch_place_jobs['seqnumber']."' AND shift_id = '0'";
				mysql_query($res_upd,$db);
				$plctShiftSno = $fetch_place_jobs['shiftid'];
				if ($plctShiftSno != $shift_id) {
					$res_upd	= "UPDATE resume_status SET status='".$managesno."', muser = '".$username."', mdate = NOW() WHERE res_id='".$candidateID."' AND req_id='".$posid."' AND seqnumber='".$fetch_place_jobs['seqnumber']."' AND shift_id= '".$plctShiftSno."'";
					mysql_query($res_upd,$db);
				}
				/* END  */

				$res_upd="UPDATE resume_status SET status='".$managesno."', muser = '".$username."', mdate = NOW() WHERE res_id='".$candidateID."' AND req_id='".$posid."' AND seqnumber='".$fetch_place_jobs['seqnumber']."' AND shift_id = '".$shift_id."'";
				mysql_query($res_upd,$db);

				$res_upd="UPDATE placement_jobs SET assg_status='".$astatus."',reason_id='".$reasonSno."' WHERE username='".$conusername."' and posid='".$posid."' AND candidate = '".$cand_username."' AND sno='".$fetch_place_jobs['sno']."'";
				mysql_query($res_upd,$db);
			}
		}

		$updassignment_array=array("invapproved" => "closed", "appno" => "appno IN (".$appno_tab.")", "userid" => "", "modulename" => "", "contactsno" => "");
		updateAssignmentSchedule($updassignment_array);
	}
}

//Function used to clean up the hrcon_jobs info
function clean_up_hrcon_data($hrconSno)
{
	global $maildb,$db,$username;
	mysql_query("DELETE FROM hrcon_jobs WHERE sno = '".$hrconSno."'", $db);
	mysql_query("DELETE FROM hrconjob_sm_timeslots WHERE pid = '".$hrconSno."'", $db);		
	mysql_query("DELETE FROM assign_commission WHERE assigntype = 'H' AND assignid = '".$hrconSno."'", $db);
	mysql_query("DELETE FROM persons_assignment WHERE asgnid='".$hrconSno."' AND asgn_mode='hrcon'",$db);
	mysql_query("DELETE FROM hrcon_burden_details WHERE hrcon_jobs_sno = '".$hrconSno."'", $db);
}	

if($getAssignHrconIds!=''){
	$placmentNotification 	= new Notifications();
	$custTemplate 			= $placementNotification->custNotificationstemplate();
	if(count($custTemplate)>0){
		$custsub			= $custTemplate['subject'];
		$custtemp			= $custTemplate['matter'];
		$custselcols		= $custTemplate['selcols'];
	}

	$empTemplate 			= $placementNotification->empNotificationstemplate();
	if(count($empTemplate)>0){
		$empsub				= $empTemplate['subject'];
		$emptemp			= $empTemplate['matter'];
		$empselcols			= $empTemplate['selcols'];
	}
	$placementNotification->sendCustomerNotifications($custsub, $custtemp, $custselcols, $getAssignHrconIds);
	$placementNotification->sendEmployeeNotifications($empsub, $emptemp, $empselcols, $getAssignHrconIds);
}
?>
