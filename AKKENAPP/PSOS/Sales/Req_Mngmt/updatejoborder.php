<?php

	
	require("global.inc");  
	require("../../Include/JP_PreferenceFunc.php");//Function for Hotjob expire date updation 

	$Compsno = $company;
	$contsno = $jocontact;

	$reptsno = $jrtcontact_sno;
	$jloc = explode("-",$jrt_loc);
	$job_location = $jloc[1];

	$bill_contact = $billcontact_sno;
	$bloc = explode("-",$bill_loc);
	$bill_address = $bloc[1];

	require("functions.php");
	require_once("multipleRatesClass.php");
	$ratesObj = new multiplerates();

	include_once('custom/custome_save.php');
	$udmObj = new userDefinedManuplations();
	
        $burden_status = getBurdenStatus();
	//Admin->JobPosting url
	$jobPostFlag="";
	if($jobposprw=='yes'){
	$jobPostFlag ='&jobposprw=yes';
	}
        
	//Bug id:3280
	//Last modified by prasadd(09/09/2008)  special chars problem job orders main screen...
	function gridcell($text)
	{
		$text=preg_replace("/(\n|\t|\r|\b)*/","",$text);
		return html_tls_specialchars(trim($text),ENT_QUOTES);
	}

	//Make The Relation For Contact And Company In any Case...
	$Update_Contact="UPDATE staffoppr_contact set csno='".$Compsno."' where sno='".$contsno."' AND csno=0";
	mysql_query($Update_Contact,$db);

	$p2=explode("|",$CRM_Joborder_Page2);

	$job_shift_id  = "";
	$shift_st_time = "";
	$shift_et_time = "";
		
	if(SHIFT_SCHEDULING_ENABLED == 'N')
	{
		// Shift details for Jobordre
		$shift_det  = $new_shift_name;
		$shift_info = explode("|", $shift_det);
		$job_shift_id 	= $shift_info[0];
		$shift_st_time = $shift_time_from;
		$shift_et_time = $shift_time_to;
	}
	
	//If shift scheduling is enabled, then save the schedule data to new tables (shift scheduling tables)
	$jo_shift_type = "";
	if(SHIFT_SCHEDULING_ENABLED == 'Y')
	{
		/* including shift schedule class file for converting Minutes to DateTime / DateTime to Minutes */
		require_once('shift_schedule/class.schedules.php');
		$objSchSchedules	= new Schedules();
		
		$sm_num_pos_total = $objSchSchedules->getShiftsNumberOfPositions($posid,$sm_form_data,$p2[32]);
		
		// Update Number of Positions in Job Order
		$p2[32] = $sm_num_pos_total;	
		$shiftSnoArr	 = array();
		if($shift_type == "perdiem")
		{
			$sm_active_sno = $sm_sel_perdiem_shifts;
			$shiftSnoArr	= explode(",", $sm_sel_perdiem_shifts);
		}
		
		if($sm_active_sno!="")
		{
			if(count(explode(',',$sm_active_sno))>0)
			{
				if($shift_type == "perdiem")
				{
					$jo_shift_type = "perdiem";
				}
				else
				{
					$jo_shift_type = "regular";	
				}
			}
		}		
		if ($jo_shift_type == "") {
			$jo_shift_type = $shift_type;
		}
		/*
			Perdiem Shift Scheduling Class file
		*/
		require_once('perdiem_shift_sch/Model/class.crm_perdiem_sch_db.php');
		$crmPerdiemShiftSchObj = new CRMPerdiemShift();

		//Delete the existing perdiem shift info incase user changed from perdiem to regular
		$shift_type_sel_qry = "SELECT shift_type FROM posdesc WHERE posid='".$posid."'";
		$shift_type_rs = mysql_query($shift_type_sel_qry,$db);
		if(mysql_num_rows($shift_type_rs)>0)
		{
			$shift_type_row = mysql_fetch_row($shift_type_rs);
			$prev_jo_shift_type = $shift_type_row[0];
			if($prev_jo_shift_type=="perdiem" && ($prev_jo_shift_type!=$jo_shift_type))
			{
				$crmPerdiemShiftSchObj->deletePerdiemDetails($posid,'joborder');
			}
		}

		// Deleting the existing perdiem shift incase user delete the shift 
		if ($delperdiem_shiftid !="") {
			$crmPerdiemShiftSchObj->deletePerdiemDetails($posid,'joborder',$delperdiem_shiftid);
		}
	}
	
	$rateRowVals = $p2[90];
	$deptid_jo = $p2[91];

	if($wsjl!="")
	{
		$p2[57]=$wsjl;
	}
	else
	{
		$p2[57]="";
	}

	$pp1=explode("^",$p2[2]);
	$pp2=explode("^",$p2[3]);
	$pp3=explode("^",$p2[4]);
	$openPos=((int)$p2[32]-(int)$p2[33]);
	$openPos=((int)$openPos>0)?$openPos:0;
	$acceessto=explode("^",$p2[30]);
	$mode_rate_type = "joborder";
	$type_order_id = $posid;

	$JobType= $p2[59];

	if($JobType=='Direct' || $JobType=='Internal Direct')
	{
		$p2[81]='0.00';
		$p2[83]='0.00';
		$p2[82]='0.00';
		$p2[84]='';
		$p2[85]='';	
		$p2[86]	='N';
		$p2[87]	='N';
	}

	if($jobshare_id!="")
		$jobacc=trim(implode(",",array_unique(explode(",",$username.",".$p2[15].",".$jobshare_id))),",");
	else
		$jobacc=$username.",".$p2[15];
 	
	if($acceessto[0]=="Public")
		$access="all";
	else if($acceessto[0]=="Private")
	 	$access=$p2[15];
	else if($acceessto[0]=="Share")
	{
		$temp_access=explode(",",$acceessto[1]);
		if(count($temp_access)<=1)
			$access=$jobacc;
		else
		{
			if((count($temp_access))==2 && ($temp_access[0]==$p2[15]))
				$access="all";
			else
				$access=$acceessto[1];
		}	
	} 

	$posque="update posdesc set ";
	
	$allowedTags='<a><p><span><div><h1><h2><h3><h4><h5><h6><img><map><area><hr><br><br /><ul><ol><li><dl><dt><dd><table><tr><td><em><b><u><i><strong><font><del><ins><sub><sup><quote><blockquote><pre> <address><code><cite><embed><object><strike><caption><center><thead><tbody>';
	
	
	$p2[5] = strip_tags(stripslashes($_POST['posdesc']),$allowedTags);
	$p2[6] = strip_tags(stripslashes($_POST['requirements']),$allowedTags);
	
	// Removes NON-ASCII characters
	$p2[5] = preg_replace('/[^(\x20-\x7F)\n]/',' ',  $p2[5]);
				
	// Handles non-printable characters
	$p2[5] = preg_replace('/&#[5-6][0-9]{4};/',' ', $p2[5]);
	
	$p2[6] = preg_replace('/[^(\x20-\x7F)\n]/',' ',  $p2[6]);
	
	$p2[93] = preg_replace('/[^(\x20-\x7F)\n]/',' ',  $p2[93]);
	$p2[94] = preg_replace('/[^(\x20-\x7F)\n]/',' ',  $p2[94]);
				
	// Handles non-printable characters
	$p2[6] = preg_replace('/&#[5-6][0-9]{4};/',' ', $p2[6]);
	
	$p2[93] = preg_replace('/&#[5-6][0-9]{4};/',' ', $p2[93]);
	$p2[94] = preg_replace('/&#[5-6][0-9]{4};/',' ', $p2[94]);
	
	if($p2[36]=="yes")
		$posque=$posque."posdesc='".mysql_real_escape_string($p2[5])."',requirements='".mysql_real_escape_string($p2[6])."',education= '".$p2[7]."',experience='".$p2[8]."',meta_keywords='".$p2[93]."',meta_desc='".$p2[94]."',";
	if($p2[37]=="yes")
		$posque=$posque."conmethod='".$p2[0]."',posjo='".$p2[1]."',avg_interview='".$p2[11]."',addinfo='".$p2[12]."',";

	////////////////// Schedule   Records /////////////////////////////////
	if($schdtogle=="yes")
		$posque=$posque."posworkhr='".$Hrstype."',posstartdate='".$syear."-".$smonth."-".$sday."',responsedate='".$eyear."-".$emonth."-".$eday."', duedate = '".$duedateyear."-".$duedatemonth."-".$duedateday."', ";

	$backupJobOrder='';

	//------Start of Code for  Post JobOrder to Web Site check box--sandhya----- 
	if(chkUserPref($crmpref,'23')) 
	{
		$sqlPostjob="select post_job_chk,posted_date,posted_status from posdesc where  posid='".$posid."'  ";
		$resPOstjob=mysql_query($sqlPostjob,$db);
		$postWebData=mysql_fetch_row($resPOstjob);
			
		if($postWebData[0]=="N" && $postWebData[1]!="0000-00-00 00:00:00" && $p2[80]=="Y")
			$backupJobOrder="YES";
		else
			$backupJobOrder="NO";
				
		if($p2[80]=="Y") 
		{
			if($postWebData[2]=="P" || $postWebData[2]=="R" ||  $postWebData[2]=="E" || $postWebData[2]=="RM" )
				$posque=$posque."post_job_chk='".$p2[80]."',";
			else
				$posque=$posque."post_job_chk='".$p2[80]."',posted_date=NOW(),";
		}
		else
		{
			$posque=$posque."post_job_chk='".$p2[80]."',";
		}
	}
        
        if(!array_key_exists('ref_bonus_amount',$_POST)){
            $ref_bonus_amount=0;
        }
    
	$posque=$posque."posstatus='".$p2[13]."',jostage='".$p2[14]."',postype='".$p2[16]."',postitle='".$p2[29]."',owner='".$p2[15]."',accessto='".trim($access,",")."',refcode='".$p2[31]."',no_of_pos='".$p2[32]."',openpos='".$openPos."',closepos='".$p2[33]."',catid='". $p2[34]."',contact='".$contsno."',posreportto='".$reptsno."',muser='".$username."',mdate=now(),company='".$Compsno."',location='".$job_location."',billingto='".$bill_contact."',bill_address='".$bill_address."',wcomp_code='".$workcode."' ,bill_req='".addslashes($p2[61])."',service_terms ='".addslashes($p2[62])."',joblocation='".$p2[57]."',sourcetype='".$p2[70]."',deptid='".$p2[91]."',industryid='".$p2[95]."',ref_bonus_amount='".$ref_bonus_amount."',shiftid='".$job_shift_id."',starthour='".$shift_st_time."',endhour='".$shift_et_time."',questionnaire_group_id='".$_POST['questionnaireGroup']."',shift_type='".$jo_shift_type."',ts_layout_pref='".$joborder_timesheet_layout_preference."' where posid='".$posid."'"; 
		
	//Function expire date details updation in hotjobs based on no. of positions
	UpdateExpireDate_FilledJO('posdesc',$posid);

	//For updating regarding column in tasklist table
	$tsk_userid="req".$posid;
	UpdateTaskList($tsk_userid,'CRM->Joborder'); 

	$SendString=$p2[44]."|".$p2[22]."|".$p2[18]."|".$p2[45]."|".$p2[46]."|".$p2[56]."|".$p2[96];
        if(RATE_CALCULATOR=='N'){
            $RetString=comm_calculate_Active($SendString);
            $RetString_Array=explode("|",$RetString);
            $p2[22]=$RetString_Array[1];
            $p2[18]=$RetString_Array[2];
            $p2[45]=$RetString_Array[3];
            $p2[46]=$RetString_Array[4];
            $p2[56]=$RetString_Array[5];
        }
	$comp_margin="select netmargin,mode  from margin_setup";
	$res_margin=mysql_query($comp_margin,$db);
		$udmObj->insertUserDefinedData($posid, 4); 
	$comp_fetch=mysql_fetch_row($res_margin);

	if($p2[59] == 'Direct' || $p2[59] == 'Internal Direct')
		$ratesObj->doBackupExistingRates();

	//////// Inseritng into Skills ////////////////////////////////////////////////////////////
	if($skillstogle=="yes")
	{
		if($skillname!="" || $usedid!="" || $levelid!="" || $skillyears!="")
		{
			$que="insert into req_skills(sno,rid,skill_name,last_used,skill_level,expe) values('','".$posid."','".$skillname."','".$usedid."','".$levelid."','".$skillyears."')";
			mysql_query($que,$db);
		}

		if(count($addname)>0)
		{
			foreach($addname as $key=>$sval)
			{
				if($addname[$key]!="" || $addlused[$key]!="" || $addslevel[$key]!="" || $addsexp[$key]!="")
				{
				$que="insert into req_skills(sno,rid,skill_name,last_used,skill_level,expe,manage_skills_id) values('','".$posid."','".$addname[$key]."','".$addlused[$key]."','".$addslevel[$key]."','".$addsexp[$key]."','".$addsmsid[$key]."')";
					mysql_query($que,$db);
				}
			}
		}

		//Update the Skills
		$updskillsid_array=explode("|^|",trim($updskillsid));
		if(count($updskillsid_array)>0 && trim($updskillsid)!="")
		{
			foreach($updskillsid_array as $key=>$val)
			{
				$que="update req_skills set skill_name='".$updname[$val]."',last_used='".$updlused[$val]."',skill_level='".$updslevel[$val]."',expe='".$updsexp[$val]."'  where sno='".$val."'";
				mysql_query($que,$db);			
			}
		} 

		//Delete the  Skills
		$delskill_array=explode("|^|",trim($delskill));
		if(count($delskill_array)>0 && trim($delskill)!="")
		{
			foreach($delskill_array as $key=>$val)
			{
				$que="delete from req_skills where sno='".$val."'";
				mysql_query($que,$db);
			}
		}
	}
	////////////////// End of the Skills  ///////////////////////////////////////////////////////////////////////

	// Skill Management Enhancement.
	$delete = "DELETE FROM req_skill_cat_spec WHERE  posid='".$posid."'";
	mysql_query($delete,$db);
	if ($skilldeptids !="") {
		$skilldeptids = explode(',',$skilldeptids);
		foreach ($skilldeptids as  $skilldeptid) {
			$insert = "INSERT INTO req_skill_cat_spec SET posid='".$posid."',dept_cat_spec_id='".$skilldeptid."',type='joskilldept'";
			mysql_query($insert,$db);
		}

	}

	if ($skillcatgryids !="") {
		$skillcatgryids = explode(',',$skillcatgryids);
		foreach ($skillcatgryids as  $skillcatgryid) {
			
			$insert = "INSERT INTO req_skill_cat_spec SET posid='".$posid."',dept_cat_spec_id='".$skillcatgryid."',type='jobskillcat'";
			mysql_query($insert,$db);
		}
	}

	if ($skillspltyids !="") {
		$skillspltyids = explode(',',$skillspltyids);
		foreach ($skillspltyids as  $skillspltyid) {
			
			$insert = "INSERT INTO req_skill_cat_spec SET posid='".$posid."',dept_cat_spec_id='".$skillspltyid."',type='jobskillspeciality'";
			mysql_query($insert,$db);
		}
	}


	if($p2[35]=="yes")		
	{
		$p2[88] = ($p2[86] == 'Y') ? $p2[88] : '0.00';//Checking whether it is billable or not--Raj

		//This is for getting the burden type and item ids from the formatted string
		if($burden_status == 'yes'){
			saveBurdenDetails($hdnbt_details,$hdnbi_details,'posdesc_burden_details','posid','update',$posid);
			saveBillBurdenDetails($bill_hdnbt_details,$bill_hdnbi_details,'posdesc_burden_details','posid','update',$posid);
		}else{
			delBurdenDetails('posdesc_burden_details','posid', $posid);
		}

		$que="update req_pref set brateopen='".$p2[17]."',bamount='".$p2[18]."',bperiod='".$p2[19]."',bcurrency='".$p2[20]."',prateopen='".$p2[21]."',pamount='".$p2[22]."',pperiod='".$p2[23]."',pcurrency='".$p2[24]."',compmargin='".$comp_fetch[0]."',compmarginmode='".$comp_fetch[1]."',currmarginmode='".$p2[26]."',brateopen_amt='".$p2[27]."',prateopen_amt='".$p2[28]."',salary ='".$p2[41]."',salary_currency ='".$p2[42]."',salary_period ='".$p2[43]."',calctype='".$p2[44]."',burden='".$p2[45]."',margin='".$p2[46]."',otrate='".$p2[47]."',ot_period='".$p2[48]."',ot_currency='".$p2[49]."',placement_fee='".$p2[50]."',placement_curr='".$p2[51]."',imethod='".$p2[52]."',iterms='".$p2[53]."' ,pterms='".$p2[54]."',tsapp='".$p2[55]."',markup='".$p2[56]."' ,sal_type='".$p2[60]."',sal_range_to='".$p2[58]."',otprate_amt='".$p2[63]."',otprate_period='".$p2[64]."',otprate_curr='".$p2[65]."',otbrate_amt='".$p2[66]."',otbrate_period='".$p2[67]."',otbrate_curr='".$p2[68]."',payrollpid='".$p2[69]."',double_prate_amt='".$p2[71]."',double_prate_period='".$p2[72]."',double_prate_curr='".$p2[73]."',double_brate_amt='".$p2[74]."',double_brate_period='".$p2[75]."',double_brate_curr='".$p2[76]."',po_num='".$p2[77]."',department='".$p2[78]."',job_loc_tax='".$p2[79]."',diem_lodging = '".$p2[81]."', diem_mie ='".$p2[82]."', diem_total = '".$p2[83]."', diem_period='".$p2[84]."', diem_currency='".$p2[85]."',diem_billable='".$p2[86]."',diem_taxable='".$p2[87]."',diem_billrate='".$p2[88]."',bill_burden='".$p2[96]."' where posid='".$posid."'";  
		mysql_query($que,$db);	

		$ratesObj->defaultRatesInsertion($mulRatesVal,$in_ratesCon);
		$ratesObj->multipleRatesInsertion($rateType,$billableR,$mulpayRateTxt,$payratePeriod,$payrateCurrency,$mulbillRateTxt,$billratePeriod,$billrateCurrency,$taxableR);
	}

	if($p2[38]=="yes")	
	{
		$que="update req_pref set wtravle='".$pp1[0]."',ptravle='".$pp1[1]."',tcomments='".$pp1[2]."',wlocate='".$pp2[0]."',city='".$pp2[1]."',state='".$pp2[2]."',country='".$pp2[3]."',lcomments='".$p7[7]."',tmax='".$pp3[0]."',dmax='".$pp3[1]."',ccomments='".$pp3[2]."' where posid='".$posid."'";
		mysql_query($que,$db);
	}

	// WE NEED TO EXECUTE UPDATE AT THE END TO FIRE THE TRIGGER.
	mysql_query($posque,$db);

	/* Calling Stored procedure for to update oppr_service details : start */
	$qry="call Oppr_Services_Update('','".$posid."','".$p2[29]."','".$p2[16]."','".$p2[91]."','edit','".$username."')";
	mysql_query($qry,$db);
	/* Calling Stored procedure for to update oppr_service details : end */
	
	//If shift scheduling is disabled, then save the schedule data to old tables (existing one)
	if(SHIFT_SCHEDULING_ENABLED == 'N')
	{
		//////////////////////////Start of the Schedule ///////////////////////////////////////////////////////////////////////////////////////////////
		if($schdtogle=="yes")
		{
			//////////////////////////// Start of the Schedule ////////////////////////////////////////////////////////

			$WeekIntArray=array("Sunday"=>1,"Monday"=>2,"Tuesday"=>3,"Wednesday"=>4,"Thursday"=>5,"Friday"=>6,"Saturday"=>7);
			if(strtolower($Hrstype)!=strtolower($FullPartTimeRecId) && $FullPartTimeRecId != "")
			{
				$PrevDelQry="delete from req_schedule where posid='".$posid."'";
				mysql_query($PrevDelQry,$db);
			}
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

					if($$ChkVal!="Y" && strtolower($Hrstype)!=strtolower($FullPartTimeRecId) && $FullPartTimeRecId != "")
					{
						$sheQry="INSERT INTO req_schedule (sno,posid,starthour,endhour,wdays,sch_date) VALUES ('','".$posid."','".$$Timefrom."','".$$TimeTo."','".$AddweakInt."','')";
						mysql_query($sheQry,$db);
					}

					foreach($$AddweakArray as $Addkey=>$Addval)
					{	
						$AddTimefrom="newSchdFrom".$Addkey;
						$AddTimeTo="newSchdTo".$Addkey;

						if($addchkSchedule[$Addkey]!="Y")
						{
							$Addweekday=$WeekIntArray[$Addval];
							$AddsheQry="INSERT INTO req_schedule (sno,posid,starthour,endhour,wdays,sch_date) VALUES ('','".$posid."','".$$AddTimefrom."','".$$AddTimeTo."','".$Addweekday."','')";
							mysql_query($AddsheQry,$db);
						}
					}
				}

				foreach($adddate as $AddDatekey=>$AddDateval)
				{
					$AddDateTimefrom="newSchdFrom".$AddDatekey;
					$AddDateTimeTo="newSchdTo".$AddDatekey;

					if($addchkSchedule[$AddDatekey]!="Y")
					{
						$InsdateArr=explode("/",$AddDateval);
						$Insdate=$InsdateArr[2]."-".$InsdateArr[0]."-".$InsdateArr[1]." 00:00:00";
						$AddDateweekday=$WeekIntArray[$addDateweek[$AddDatekey]];
						$AddDatesheQry="INSERT INTO req_schedule (sno,posid,starthour,endhour,wdays,sch_date) VALUES ('','".$posid."','".$$AddDateTimefrom."','".$$AddDateTimeTo."','".$AddDateweekday."','".$Insdate."')";
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
						$UpdsheQry="update req_schedule set starthour='".$$UpdTimefrom."',endhour='".$$UpdTimeTo."',sch_date='' where sno='".$Updkey."'";
						mysql_query($UpdsheQry,$db);
					}
					else if($UpdchkSchedule[$Updkey]=="Y") 
					{
					   $DelQry="delete from req_schedule where sno='".$Updkey."'";
					   mysql_query($DelQry,$db);
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
						$UpdDatesheQry="update req_schedule set starthour='".$$UpdDateTimefrom."',endhour='".$$UpdDateTimeTo."' where sno='".$UpdDatekey."'";
						mysql_query($UpdDatesheQry,$db);
					}
					else if($UpdchkSchedule[$UpdDatekey]=="Y") 
					{
					   $DateDelQry="delete from req_schedule where sno='".$UpdDatekey."'";
					   mysql_query($DateDelQry,$db);
					}
				}	
				////////////End of the Update//////////////////////////////////////////////////////	
			}
			else if($customcheckall=="Y") 
			{
			   $DateDelQry="delete from req_schedule where posid='".$posid."'";
			   mysql_query($DateDelQry,$db);
			}
		}	////////////////////End Of the  Schedule ////////////////////////////////////////////////////////////////////////////////
	} // end if SHIFT_SCHEDULING_ENABLED = 'N' 
	
	if($p2[39]!="")
 	{
		$notesup="(Unfilled Lost) Reason: ".$p2[40]."-". $p2[39];
 		$que = "INSERT INTO notes(sno,contactid,cuser,type,notes,cdate) VALUES ('','".$posid."', '".$username."','req','".addslashes($notesup)."',now())";
		mysql_query($que,$db);
 	}

	$posdes_que="select contact from posdesc where posid=".$posid;
	$posdes_res=mysql_query($posdes_que,$db);
	$pos_row=mysql_fetch_array($posdes_res);

	$sel_contact="SELECT CONCAT_WS(' ',staffoppr_contact.fname,if(staffoppr_contact.mname='',' ',staffoppr_contact.mname),staffoppr_contact.lname),staffoppr_contact.ytitle,staffoppr_cinfo.cname,staffoppr_contact.wphone FROM staffoppr_contact LEFT JOIN staffoppr_cinfo ON staffoppr_contact.csno = staffoppr_cinfo.sno WHERE staffoppr_contact.sno='".$pos_row[0]."' and staffoppr_contact.status='ER' and (FIND_IN_SET('$username',staffoppr_contact.accessto)>0 OR staffoppr_contact.owner='$username' OR staffoppr_contact.accessto='ALL')";
	$res_contact=mysql_query($sel_contact,$db);
	$row_contact=mysql_fetch_row($res_contact);
	$comp_contact=html_tls_entities(addslashes($row_contact[0]));
	$title_contact=html_tls_entities(addslashes($row_contact[1]));
	$comp_name=html_tls_entities(addslashes($row_contact[2]));
	$phone_contact=html_tls_entities(addslashes($row_contact[3]));

	$contactDiv="<font class='modcaption1'>Contact:</font>";
	if($comp_contact!="")
		$contactDiv.="&nbsp;<a href='javascript:opencontacts(".$pos_row[0].")'><font class='labels1'><strong>$comp_contact</strong></font></a> ";
	if($title_contact!='') 
		$contactDiv.="&nbsp;|&nbsp;<font class='modcaption2'>$title_contact</font>";
	if($comp_name!='') 
		$contactDiv.="&nbsp;|&nbsp;<font class='modcaption1'>$comp_name</font>";

	$modeUpdadd="update";
	if($BillInfo_Toggle=="yes")
		require("commissionAdd.php");

	//Calling functions to update hotjobs and apijobs get hotjobs sno as hotjobs.sno=api_jobs.req_id
	$sqlhotjobs="SELECT sno from hotjobs where req_id='".$posid."' and status !='BP'";
	$reshotjobs=mysql_query($sqlhotjobs,$db);
	$hotres=mysql_fetch_row($reshotjobs);
	$hotjobOldSno=$hotres[0];

	Crmjobs2AdminjobsUpdate($posid,$p2[80],$backupJobOrder,$hotjobOldSno);
	Crmjobs2ApijobsUpdate($posid,$p2[80],$backupJobOrder,$hotjobOldSno);

	UpdatePosdescData($posid); 
	
	//If shift scheduling is enabled, then save the schedule data to new tables (shift scheduling tables)
	if (SHIFT_SCHEDULING_ENABLED == "Y" && $shift_type == "perdiem") 
	{

		$crmPerdiemShiftSchObj->updtPerdiemJoShiftDetails($posid,$candrn);
		$crmPerdiemShiftSchObj->updtPosdescPerdiemJoShiftExpEndDates($posid,$candrn);

		//INSERTING COMPANY RATES OR ADMIN RATES FOR SELECTED SHIFTS
		$shiftSnoArr = array_unique($shiftSnoArr);

		foreach($shiftSnoArr AS $key=>$shiftSno)
		{
			$shift_rates_data = "sm_rates_".$shiftSno;			
			if($$shift_rates_data!="|" && $$shift_rates_data!="")
			{
				$objSchSchedules->updateShiftRates($posid,$$shift_rates_data,$shiftSno,$mode_rate_type);
			}
		}
		//END

		unset($_SESSION['editJoPerdiemShiftSch'.$candrn]);
		unset($_SESSION['editJoPerdiemShiftPagination'.$candrn]);
		unset($_SESSION['modifiedJoPerdiemShiftSch'.$candrn]);
		unset($_SESSION['deletedJoPerdiemShiftSch'.$candrn]);
	}
	else if(SHIFT_SCHEDULING_ENABLED == "Y" && $shift_type == "regular")
	{	
		//SHIFT SCHEDULING TABLE INSERTION START
		$previousDate = date("Y-m-d",strtotime("-2 months",strtotime(date("Y-m-d"))));
		
		/* including crm shift schedule class file for common functions */
		require_once('shift_schedule/crm_schedule_db.php');
		$objScheduleDetails	= new CandidateSchedule();
		
		$del_sm_jo_tf_sql = "DELETE FROM posdesc_sm_timeslots WHERE pid = '".$posid."' AND shift_date >= '".$previousDate."'";
		$del_sm_jo_tf_res = mysql_query($del_sm_jo_tf_sql, $db);

		$del_sm_tim_slot_pos = "DELETE FROM sm_timeslot_positions WHERE pid = '".$posid."' AND shift_date >= '".$previousDate."' AND type='posdesc'";
		mysql_query($del_sm_tim_slot_pos, $db);

		$sm_jo_array=explode("|",$sm_form_data);
		$sm_req_cnt	= count($sm_jo_array);		
		
		//forming array to insert based on single/recurrence 
		$insertArrayData = array();
		$shiftSnoArr	 = array();
		for($i=0;$i<$sm_req_cnt;$i++)
		{
			$smVal = trim($sm_jo_array[$i]);
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
				$shiftName = $smValExp[6];
				$shiftColor = $smValExp[7];
				$shiftSNo = $smValExp[8];
				$shiftNumPos = $smValExp[9];
				if($slotGrpNo == "")
				{
					$slotGrpNo = 0;
				}
				if($shiftStatus == "")
				{
					$shiftStatus = 'available';
				}
				$shiftCandid = $smValExp[10];

				$insertArrayData[$recNo][] = array($smAvailDate,$smAvailFromDate,$smAvailToDate,$slotGrpNo,$shiftStatus,$shiftName,$shiftColor,$shiftSNo,$shiftNumPos,$shiftCandid);
				array_push($shiftSnoArr,$shiftSNo);
				
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
				$smShiftName = $timeSlotDetails[5]; 
				$smShiftColor = $timeSlotDetails[6];
				$smShiftSNo = $timeSlotDetails[7];
				$smShiftNumPos = $timeSlotDetails[8];
				
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
			
				$sm_jo_tf_sql	= "INSERT INTO posdesc_sm_timeslots
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
										'".$posid."',
										'".$smAvailDate."',
										'".$smAvailFromDate."',
										'".$smAvailToDate."',								
										'".$smEventType."',
										'".$smEventNo."',
										'".$smEventGrpNo."',
										'".$smShiftStatus."',
										'".$smShiftSNo."',
										'".$smShiftNumPos."',
										'".$username."',
										NOW(),
										'".$username."',
										NOW()							
										)";

				$sm_jo_tf_res	= mysql_query($sm_jo_tf_sql, $db);		
				// This function is used to insert the candidate for that particular position.
				$shift_sno = mysql_insert_id();

				$candidates = explode(',',$timeSlotDetails[9]);
				if ($smShiftNumPos == 0) {
					$smShiftNumPosView = 1;
				}else{
					$smShiftNumPosView = $smShiftNumPos;
				}
				for ($sl=0; $sl <= (int)$smShiftNumPosView-1; $sl++) { 
					$candId ='';
					$candId = $candidates[$sl] ? $candidates[$sl] : '';
					//Toget the re-assign cand to make position #ff0000 red color
					$shift_color_code = '';
					$selectcl = "SELECT REPLACE(cl.candid,'emp','') AS empid,el.username AS empusername 
					FROM `candidate_list` cl
					LEFT JOIN emp_list el ON (REPLACE(cl.candid,'emp','') = el.sno)
					WHERE cl.username='".$candId."' AND cl.ctype='Employee'";
					$resultcl = mysql_query($selectcl);
					if (mysql_num_rows($resultcl)) {
						$rowcl = mysql_fetch_assoc($resultcl);
						$selectReassign = "SELECT sno FROM reassign_sm_timeslots WHERE to_username='".$rowcl['empusername']."' AND posid = '".$posid."' AND shift_starttime='".$smAvailFromDate."' AND shift_endtime='".$smAvailToDate."' AND sm_sno='".$smShiftSNo."'";
						$resultre = mysql_query($selectReassign,$db);
						if (mysql_num_rows($resultre)>0) {
							$shift_color_code = '#ff0000';
						}
					}
					// End here
					$position = $sl+1;
					$insertNoOfPosition = "INSERT INTO sm_timeslot_positions (sno,sm_timeslote_sno,pid,candid,type,position,shift_date,shift_starttime,shift_endtime,sm_sno,color_code) VALUES ('','".$shift_sno."','".$posid."','".$candId."','posdesc','".$position."','".$smAvailDate."','".$smAvailFromDate."','".$smAvailToDate."','".$smShiftSNo."','".$shift_color_code."')";
					mysql_query($insertNoOfPosition, $db);
				}

			}
			if($setFlat == 1)
			{
				$tempVar++;
			}
		}
		
		//SHIFT SCHEDULING TABLE INSERTION END

		// JOB ORDER SHIFT SCHEDULING STATUS UPDATE - START
		//$objScheduleDetails->updateShiftStatus($posid);
		$objScheduleDetails->updateShiftStatusByName($posid);
		// JOB ORDER SHIFT SCHEDULING STATUS UPDATE - END
		 // end if SHIFT_SCHEDULING_ENABLED = 'Y'

		//INSERTING COMPANY RATES OR ADMIN RATES FOR SELECTED SHIFTS
		$shiftSnoArr = array_unique($shiftSnoArr);

		foreach($shiftSnoArr AS $key=>$shiftSno)
		{
			$shift_rates_data = "sm_rates_".$shiftSno;			
			if($$shift_rates_data!="|" && $$shift_rates_data!="")
			{
				$objSchSchedules->updateShiftRates($posid,$$shift_rates_data,$shiftSno,$mode_rate_type);
			}
		}
		//END
		
	}

	//
	if ($shift_type == "regular") {
		$selSmTime = "SELECT sno FROM posdesc_sm_timeslots WHERE pid='".$posid."'";
		$resultSmTime = mysql_query($selSmTime,$db);
		if (mysql_num_rows($resultSmTime)==0) {
			$update = "UPDATE posdesc SET shift_type='' WHERE posid='".$posid."' ";
			mysql_query($update,$db);
		}
	}

	if ($shift_type == "perdiem") {
		$selSmTime = "SELECT sno FROM jo_perdiem_shift_sch WHERE posid='".$posid."'";
		$resultSmTime = mysql_query($selSmTime,$db);
		if (mysql_num_rows($resultSmTime)==0) {
			$update = "UPDATE posdesc SET shift_type='' WHERE posid='".$posid."' ";
			mysql_query($update,$db);
		}
	}


	//start of code for getting grid data for the new job order.
	//Added Video Interviews
	$sql	= "SELECT
			posdesc.posid,
			".tzRetQueryStringSelBoxDate("posstartdate","Date","/").",
			postitle,
			staffoppr_cinfo.cname,
			posdesc.jonumber,
			jobtype.name jobType,
			IF(no_of_pos - closepos <0,0,no_of_pos - closepos),
			IF(posdesc.accessto='all','Public',IF(posdesc.accessto='all','Public',IF(posdesc.accessto!='".$username."','Share', 'Private'))),
			status.name status,
			sourcetype.name sourcetype,
			users.name owner,
			".tzRetQueryStringDTime("posdesc.stime","Date","/").",
			".tzRetQueryStringDTime("posdesc.mdate","Date","/").",
			posdesc.owner,
			posdesc.sub_int_count,
			".tzRetQueryStringSelBoxDate('posdesc.duedate','Date','/').",
			posdesc.refcode,
			req_pref.po_num,
			loc.city,loc.state,
			posdesc.video_interview_count
		FROM posdesc
		LEFT JOIN staffoppr_cinfo ON (posdesc.company=staffoppr_cinfo.sno)
		LEFT JOIN staffoppr_cinfo loc ON (posdesc.location=loc.sno)
		LEFT JOIN manage jobtype ON (posdesc.postype=jobtype.sno) AND jobtype.type = 'jotype'
		LEFT JOIN manage status ON (posdesc.posstatus=status.sno) AND status.type = 'jostatus'
		LEFT JOIN manage sourcetype ON (posdesc.sourcetype=sourcetype.sno and sourcetype.type='josourcetype')
		LEFT JOIN users ON (posdesc.owner=users.username), req_pref
		WHERE req_pref.posid = posdesc.posid AND posdesc.posid={$posid}"; 
		
		
	$rs 	= mysql_query($sql,$db);
	$row 	= mysql_fetch_row($rs);

	// For displaying the total Inquiries Count
	$rque="select posid,resumeid,seqnumber from reqresponse where posid = {$posid} and par_id='0'";
	$rres=mysql_query($rque,$db);
	while($rrow=mysql_fetch_row($rres))
	{
		$que="select count(1) from process_mail_headers where folder='ReqResponses' and  subject like '%".$rrow[2]."%' ";
		$res=mysql_query($que,$db);
		$row1=mysql_fetch_row($res);

		$subcnt += $row1[0];
		$idsubvals=explode(",",$rrow[1]);
		$cand_count += count($idsubvals);
	}

	if($cand_count == 0)
		$grid_candidates = " ";
	else
		$grid_candidates = "<a href='javascript:showCand($posid)'>$cand_count</a>";

	// For displaying the total Submissions Count
	$queSunCntQue="select posid,count(posid) submissions_count from reqresponse where posid = {$posid} and par_id='0' group by posid";
	$queSunCntRes=mysql_query($queSunCntQue,$db);
	$queSunCntRow=mysql_fetch_array($queSunCntRes) ;
	$rescount = $queSunCntRow['submissions_count'];

	if($rescount==0)
	{
		$grid_submissions = " ";
	}
	else
	{
		if($subcnt == 0)
			$grid_submissions = "<a href='javascript:link($posid)'>$rescount</a>";
		else
			$grid_submissions = "<a href='javascript:link($posid)'>$rescount</a>&nbsp;&nbsp;&nbsp;<a href='javascript:subInqiries($posid)' style='color:red'>($subcnt new)</a>";
	}

	// For displaying the Count of the candidates for Interview
	$que_inter="select req_id,count(req_id) from resume_status where req_id = {$posid} and status='Interview' group by req_id";
	$res_inter=mysql_query($que_inter,$db);
	$row_inter=mysql_fetch_row($res_inter) ;
	$cands_intw = $row_inter[1];
	if($cands_intw ==0)
		$grid_intws = " ";
	else
		$grid_intws = gridcell($cands_intw);
	
	//Query for getting Custom Grid Columns
	$query_order_column = "SELECT cgucols.column_order,cgcols.id, cgcols.custom_form_modules_id, cgcols.column_name, cgcols.db_col_name, cgcols.ref_table, cgcols.ref_column_name, cgcols.ref_target_column_name, cfm.primary_table, cgcols.grid_logic, cgcols.db_col_type, cgcols.col_alias, cgcols.search_logic, cgcols.grid_logic_with_leftjoin, cgcols.allow_leftjoin
	FROM udv_grids cg
	LEFT JOIN udv_grids_usercolumns cgucols ON cgucols.custom_grid_id = cg.id
	LEFT JOIN udv_grid_columns cgcols ON cgcols.id = cgucols.custom_grid_column_id
	LEFT JOIN udf_form_modules cfm ON cfm.module_id = cgcols.custom_form_modules_id
	WHERE cg.`custom_form_modules_id` =4
	AND cg.cuser =$username
	AND cgcols.udfstatus =1
	AND cgcols.allow_on_grid =1
	ORDER BY cgucols.column_order";

	$query_order_query=mysql_query($query_order_column,$db);
	
	$index_arr		= array();	//Array declared for maintaining column name of the Customized columns
	$udf_cols		= array();	//Array for storing the column name of the UDF columns
	
	
	if(mysql_num_rows($query_order_query)>0){
	
		while($query_order_result=mysql_fetch_array($query_order_query)){
			//If condition for filtering the UDF columns
			if($query_order_result['db_col_name'] == 'posid' && $query_order_result['ref_column_name'] == 'rec_id')
			{	
				//Getting the UDF column name and order id of the column
				$index_arr[$query_order_result['ref_target_column_name']] = $query_order_result['column_order']+1;
				$udf_cols[] = ",t14.".$query_order_result['ref_target_column_name'];
			}	
			else
			{	
				//else for Getting the columns other than UDF
				$Column_Name = trim($query_order_result['column_name']);
				
				$replace_column = str_replace(' ', '_', $Column_Name);	//Replace space with Underscore	
				
				//Condition for converting as it is having special characters 
				if($replace_column == 'Company_Other_Info_/_Culture')
				{
					$replace_column = 'Company_Other_Info_Culture';
				}
				//Condition for converting as it is having special characters 
				if($replace_column == 'Ref._code')
				{
					$replace_column = 'Ref_code';
				}
				
				$index_arr[$replace_column] = $query_order_result['column_order']+1; 
			}
										
		}
	
	}	
	else
	{
		//Array for default grid columns	
		$Default_grid = array('Job_Order_Id','Status','Job_Title','Company_Name','Position_City','Position_State','Ref_code','PO_Number','Due_Date','Openings','Submissions','Candidates','Interviews','Video_Interviews','Type','Source_Type','Job_Type','Owner','Start_Date','Created_Date','Modified_Date');
		
		//Converting the Array to start the Key from 1 instead of 0	
		$key_val_change = array_combine(range(1, count($Default_grid)), array_values($Default_grid));
		
		//flipping the Array to change keys to values
		$index_arr  = array_flip($key_val_change);
	}
	
	//Imploding the UDF columns as string inorder to pass in the query if there are any UDF columns
	if($udf_cols !='')
		$implode_udf = implode("",$udf_cols);	
	else
		$implode_udf = "";

	//Query for getting the updated data for Job Orders
	$sql_data = "select
						posdesc.posid as Job_Order_Id,
						t7.name as Status,
						posdesc.postitle as Job_Title,
						t1.cname as Company_Name ,
						t2.city as Position_City,
						t3.state as Position_State,
						posdesc.refcode as Ref_code,
						t4.po_num as PO_Number,
						DATE_FORMAT(posdesc.duedate,'%m/%d/%Y') as Due_Date,
						IF(posdesc.no_of_pos - posdesc.closepos < 0, 0, posdesc.no_of_pos - posdesc.closepos) Openings,
						IF(posdesc.sub_sub_count = 0, posdesc.sub_sub_count, CONCAT('', posdesc.sub_sub_count, '')) AS Submissions,
						IF(posdesc.sub_cand_count = 0, 0, CONCAT('', posdesc.sub_cand_count , '')) AS Candidates,
						posdesc.sub_int_count as Interviews,
						IF(posdesc.video_interview_count = 0, 0, CONCAT('', posdesc.video_interview_count , '')) AS Video_Interviews,
						IF(posdesc.accessto='ALL','Public',IF(posdesc.accessto='1','Private','Share')) as Type,
						t5.name as Source_Type,
						t6.name as Job_Type,
						t8.name as Owner,
						DATE_FORMAT(posdesc.posstartdate,'%m/%d/%Y') as Start_Date,
						DATE_FORMAT(CONVERT_TZ(posdesc.stime,'SYSTEM','EST5EDT'),'%m/%d/%Y') as Created_Date,
						DATE_FORMAT(CONVERT_TZ(posdesc.mdate,'SYSTEM','EST5EDT'),'%m/%d/%Y') as Modified_Date,
						(IF(bamount=0,'',bamount)) AS Bill_Rate,
						t4.department as Billing_Department,
						t4.payrollpid as Billing_Pay_Role_Provider_ID,
						(SELECT billpay_code FROM bill_pay_terms, req_pref WHERE bill_pay_terms.billpay_termsid = req_pref.pterms AND req_pref.posid = posdesc.posid AND bill_pay_terms.billpay_status='active') as Billing_Payment_Terms,
						posdesc.service_terms as Billing_Service_Terms,
						t9.billpay_code as Billing_Terms,
						t4.tsapp as Billing_Timesheet_Approval,
						t10.code as Billing_Workers_Comp_Code,
						t11.name as Category,
						t4.tmax as Commute,
						t1.directions as Company_Directions,
						t1.dress_code as Company_Dress_Code,
						t1.phone as Company_Main_Phone,
						t1.phone_extn as Company_Main_Phone_Ext,
						t1.culture as Company_Other_Info_Culture,
						t1.parking as Company_Parking,
						t1.smoke_policy as Company_Smoking_Policy,
						t1.compsummary as Company_Summary,
						t1.tele_policy as Company_Telecommuting_Policy,
						(select concat(fname, ' ', lname) bilname from staffoppr_contact where staffoppr_contact.sno = posdesc.contact) as Contact,
						t13.name as Created_By,
						posdesc.education as Description_Education,
						posdesc.posdesc as Description_Position_Summary,
						posdesc.requirements as Description_Requirement,
						posdesc.experience as Description_Years_of_Exp,
						(IF(double_brate_amt=0,'',double_brate_amt)) AS Double_Time_Bill_Rate,
						(IF(double_prate_amt=0,'',double_prate_amt)) AS Double_Time_Pay_Rate,
						DATE_FORMAT(posdesc.responsedate,'%m/%d/%Y') as Expected_End_Date,
						posdesc.closepos as Filled,
						t15.deptname as HRM_Department,
						t16.name as Industry,
						(select concat(fname, ' ', lname) bilname from staffoppr_contact where staffoppr_contact.sno = posdesc.posreportto) as Job_Reports_To,
						t17.name as Modified_By,
						( SELECT COUNT( 1 ) FROM hrcon_jobs WHERE hrcon_jobs.posid =posdesc.posid AND hrcon_jobs.ustatus = 'active' AND jtype!='' AND jotype!=0) as On_Assignment ,
						(IF(otbrate_amt=0,'',otbrate_amt)) AS Over_Time_Bill_Rate,
						(IF(otprate_amt=0,'',otprate_amt)) AS Over_Time_Pay_Rate,
						(IF(pamount=0,'',pamount)) AS Pay_Rate,
						(SELECT COUNT(1) FROM resume_status rs, manage mng WHERE mng.sno = rs.status AND mng.type = 'interviewstatus' AND mng.name = 'Pending Placement' AND rs.req_id = posdesc.posid) AS Pending_Placement,
						(IF(placement_fee=0,'',placement_fee)) AS Placement_Fee,
						t4.wlocate as Relocation,
						(IF(sal_type='range', IF(salary=0 && sal_range_to=0, '', CONCAT(salary,' - ', sal_range_to)), IF(salary=0,'',salary))) AS Salary,
						posdesc.posworkhr as Schedule_Hours,
						(SELECT CONCAT('', COUNT(1), '') FROM candidate_list LEFT JOIN short_lists ON short_lists.candid = candidate_list.sno WHERE short_lists.reqid=posdesc.posid AND candidate_list.status='ACTIVE' AND candidate_list.sno NOT IN (select res_id from resume_status, manage WHERE manage.sno = resume_status.status AND req_id=posdesc.posid AND manage.name NOT IN ('Closed','Cancelled') AND resume_status.pstatus!='A') AND candidate_list.avail!='inactive') AS 'Short_List' ,
						(SELECT GROUP_CONCAT(skill_name) FROM req_skills WHERE req_skills.rid = posdesc.posid GROUP BY rid ) as Skills,
						t4.wtravle as Travel,
						posdesc.company as comp_id,
						t19.name as Stage {$implode_udf},
						posdesc.no_of_pos as Positions_Requested
				from
					posdesc 
				LEFT JOIN staffoppr_cinfo t1 ON posdesc.company = t1.sno 
				LEFT JOIN staffoppr_location t2 ON posdesc.location = t2.sno 
				LEFT JOIN staffoppr_location t3 ON posdesc.location = t3.sno 
				LEFT JOIN req_pref t4 ON posdesc.posid = t4.posid 
				LEFT JOIN manage t5 ON posdesc.sourcetype = t5.sno 
				LEFT JOIN manage t6 ON posdesc.postype = t6.sno 
				LEFT JOIN manage t7 ON posdesc.posstatus = t7.sno 
				LEFT JOIN users t8 ON posdesc.owner = t8.username 
				LEFT JOIN bill_pay_terms t9 ON posdesc.bill_req = t9.billpay_termsid 
				LEFT JOIN workerscomp t10 ON posdesc.wcomp_code = t10.workerscompid 
				LEFT JOIN manage t11 ON posdesc.catid = t11.sno 
				LEFT JOIN staffoppr_contact t12 ON t12.sno = posdesc.contact 
				LEFT JOIN users t13 ON posdesc.username = t13.username 
				LEFT JOIN udf_form_details_joborder_values t14 ON t14.rec_id = posdesc.posid 
				LEFT JOIN department t15 ON posdesc.deptid = t15.sno 
				LEFT JOIN manage t16 ON posdesc.industryid = t16.sno 
				LEFT JOIN users t17 ON posdesc.muser = t17.username 
				LEFT JOIN req_skills t18 ON t18.rid = posdesc.posid
				LEFT JOIN manage t19 ON posdesc.jostage = t19.sno
				where posdesc.posid={$posid}";

	$rs_data = mysql_query($sql_data,$db);
	$row_data = mysql_fetch_assoc($rs_data);

	//Condition for Position Summary as it having div and styles in script
	if($row_data['Description_Position_Summary']){	
		
		$Row_Description_Position_Summary = trim(strip_tags($row_data['Description_Position_Summary']));
		
		$row_data_desc_space = (preg_replace('/[^A-Za-z0-9\. -]/', '', $Row_Description_Position_Summary));
		$row_data['Description_Position_Summary'] = str_replace('nbsp', '', $row_data_desc_space);
	}
	//Condition for Description Requirement as it having div and styles in script
	if($row_data['Description_Requirement']){	
		
		$Row_Description_Requirement = trim(strip_tags($row_data['Description_Requirement']));
		$row_data_space = preg_replace('/[^A-Za-z0-9\. -]/', '', $Row_Description_Requirement);
		$row_data['Description_Requirement'] = str_replace('nbsp', '', $row_data_space);
		
	}
	
	//Conditions, because default date format is displaying

	if($row_data['Start_Date']=="00/00/0000"){	
		$row_data['Start_Date']  =  "";
	}
	if($row_data['Due_Date']=="00/00/0000"){	
		$row_data['Due_Date']  =  "";
	}
	if($row_data['Expected_End_Date']=="00/00/0000"){	
		$row_data['Expected_End_Date']  =  "";
	}
	
	//condition to display 0 in Restore view
	if(mysql_num_rows($query_order_query)<=0){
		if($row_data['Openings']=="0"){	
			$row_data['Openings']  =  "";
		}
		if($row_data['Submissions']=="0"){	
			$row_data['Submissions']  =  "";
		}
		if($row_data['Candidates']=="0"){	
			$row_data['Candidates']  =  "";
		}
		if($row_data['Interviews']=="0"){	
			$row_data['Interviews']  =  "";
		}
		if($row_data['Video_Interviews']=="0"){	
			$row_data['Video_Interviews']  =  "";
		}
	}
	
	//Condition for getting link for company
	if($row_data['Company_Name']){	
		$row_data['Company_Name']  = "<a href='javascript:showComp(".$row_data['comp_id'].")'>".$row_data['Company_Name']."</a>";
	}
	//Condition for getting link for Submission
	if($row_data['Submissions']){	
		$row_data['Submissions']  = "<a href='javascript:link(".$row_data['Job_Order_Id'].")'>".$row_data['Submissions']."</a>";
	}	
	//Condition for getting link for Candidates
	if($row_data['Candidates']){	
		$row_data['Candidates']  = "<a href='javascript:showCand(".$row_data['Job_Order_Id'].")'>".$row_data['Candidates']."</a>";
	}
	//Condition as underline is not coming for short list when it is "0"
	if($row_data['Short_List'] == '0'){	
		$row_data['Short_List']  = "0 ";
	}
	//Condition for getting link for Short List
	if($row_data['Short_List']){	
		$row_data['Short_List']  = "<a href='javascript:showShortList(".$row_data['Job_Order_Id'].")'>".$row_data['Short_List']."</a>";
	}
	
	
	//To get the matching Keys for default columns and the updated Joborder data
	$common_keys = array_intersect_key($row_data,$index_arr);
	
	
	
	$newArray	= array();	//Array declared for storing the final output 
	
	//This matches the keys and returns result as Key from $index_arr and Value from  $common_keys	
	foreach( $index_arr as $origKey => $valuess ){
		// New key that we will insert into $newArray with
		$newKey = stripslashes($common_keys[$origKey]);

		if($origKey != 'Company_Name' && $origKey != 'Submissions' && $origKey != 'Candidates' && $origKey != 'Short_List')
			$newArray[$valuess]= convertSpecCharsCRM($newKey);
		else
			$newArray[$valuess]= $newKey;
	}
	
	//Converting the final Ouput Array to JSON format
	$griddata =  array2json($newArray);
	$tot_job_data[0] 	= "<input type=checkbox name=auids[] OnClick=chk_clearTop() value='".$row[0]."|".$row[13]."|".$row[7]."|".gridcell($row[2])."|"."0"."'>";
	
	$tot_job_data[1]	= convertSpecCharsGlobal($row[4]); 	//Job ID
	$tot_job_data[2]	= convertSpecCharsGlobal($row[2]); 	// Job Title
	$tot_job_data[3]	= convertSpecCharsGlobal($row[3]); 	//Company Name	
	$tot_job_data[4]	= convertSpecCharsGlobal($row[18]); 	//City
	$tot_job_data[5]	= convertSpecCharsGlobal($row[19]);	//State
	$tot_job_data[6]	= convertSpecCharsGlobal($row[16]);	//REF Code
	$tot_job_data[7]	= convertSpecCharsGlobal($row[17]);	//PO Number		
	$tot_job_data[8]	= ($row[15]=="00/00/0000")? "" : $row[15]; //Due Date
	$tot_job_data[9]	= $row[6]; 				// Openings
	$tot_job_data[10]	= $grid_submissions; 			//Submissions
	$tot_job_data[11]	= $grid_candidates; 			//Candidates	
	$tot_job_data[12]	= $row[14]; 				//Interviews	
	$tot_job_data[13]	= $row[20]; 				//Video Interviews
	$tot_job_data[14]	= convertSpecCharsGlobal($row[7]);	//Type
	$tot_job_data[15]	= convertSpecCharsGlobal($row[9]);	//SourceType
	$tot_job_data[16]	= convertSpecCharsGlobal($row[5]); 	//Job Type
	$tot_job_data[17]	= convertSpecCharsGlobal($row[8]); 	//Status
	$tot_job_data[18]	= convertSpecCharsGlobal($row[10]);	//Owner
	$tot_job_data[19]	= ($row[1]=="00/00/0000" || $row[1]=='')?'':$row[1]; 	//Start 
	$tot_job_data[20]	= ($row[11]=="00/00/0000" || $row[11]=='')?'':$row[11];	//Create Date
	$tot_job_data[21]	= ($row[12]=="00/00/0000" || $row[11]=='')?'':$row[12];	//Modified Date
	$tot_job_data[22]	= "";	
	$tot_job_data[23]	= "redirectjob.php?addr=".$row[0];		




	$grid[0]			= $tot_job_data[0];
	$grid[$joborderid_index]	= $tot_job_data[1];
	$grid[$title_index]		= $tot_job_data[2];
	$grid[$companyname_index]	= $tot_job_data[3];	
	$grid[$city_index]		= $tot_job_data[4]; 
	$grid[$state_index]		= $tot_job_data[5]; 	
	$grid[$refcode_index]		= $tot_job_data[6];
	$grid[$ponumber_index] 		= $tot_job_data[7];
	$grid[$duedate_index]		= $tot_job_data[8];
	$grid[$openings_index]		= $tot_job_data[9];
	$grid[$submissions_index]	= $tot_job_data[10];
	$grid[$candidates_index]	= $tot_job_data[11];
	$grid[$interviews_index]	= $tot_job_data[12];
	$grid[$videointerviews_index]	= $tot_job_data[13];
	$grid[$type_index]		= $tot_job_data[14];
	$grid[$sourcetype_index]	= $tot_job_data[15];
	$grid[$jtype_index]		= $tot_job_data[16];
	$grid[$status_index]		= $tot_job_data[17];
	$grid[$owner_index]		= html_tls_specialchars(addslashes(trim(eregi_replace(" +", " ", $tot_job_data[18]))),ENT_QUOTES);
	
	$grid[$cdate_index]		= $tot_job_data[19];
	$grid[$mdate_index]		= $tot_job_data[20];


	$grid1 	=  array2json($grid);	
?>
<script>
	var placement_name='<?php echo $placement_name;?>';
	var jobAPostFlag = '<?php echo $jobPostFlag ;?>';
	var JobPostFlg  ='<?php echo $jobposprw; ?>';
	var addre='<?php echo $admaddr;?>';
	var module = '<?php echo $module;?>';
	try
		{
	if(window.opener.location.href.indexOf("showreqpagesub.php") >=0 || window.opener.location.href.indexOf("showreqpagesub1.php") >=0)
	{
		form=window.opener.document.conreg;
		var replaceDiv="<?php echo $contactDiv;?>";
		window.opener.document.getElementById("joborder_contact").innerHTML=replaceDiv;
		var posid='<?php echo $posid;?>';
		var candrn='<?php echo $candrn;?>';
		if(JobPostFlg=='yes'){
		 	window.location.href="jobSummary.php?addr="+addre+"&candrn="+candrn+"&ustat=success"+jobAPostFlag+"&module="+module;	
		}else{
			window.location.href="jobSummary.php?addr="+posid+"&candrn="+candrn+"&ustat=success"+jobAPostFlag+"&module="+module;	
		}
		
	}
	else if(window.opener.location.href.indexOf("/Marketing/Candidates/revconreg0.php") != -1 )
	{
		var wdp = window.opener.document;
		var wdpfunc = window.opener;
		if((typeof wdp.conreg.summarypage=='object') && (typeof wdpfunc.Ajax_result=='function' || typeof wdpfunc.Ajax_result=='object') && (wdp.conreg.summarypage.value=='summary'))
		{
			var posid = '<?php echo $posid;?>';
			var candrn = '<?php echo $candrn;?>';
			var addr = wdp.conreg.addr1.value;
			var conid = wdpfunc.document.forms[0].con_id.value;
			var candrn = wdpfunc.document.forms[0].candrn.value;

			if(wdp.conreg.panel_frm.value == "Appcand") // checking for applied or matching candidates
				wdpfunc.Ajax_result('cand_summary_resp.php?rtype=Applied&addr='+addr+'&conid='+conid+'&candrn='+candrn+"&module="+module,'Applied','','mntcmnt6');
			else
				wdpfunc.Ajax_result('cand_summary_resp.php?rtype=joborder&addr='+addr+'&conid='+conid+'&candrn='+candrn+"&module="+module,'joborder','','mntcmnt5');
			window.location.href="jobSummary.php?addr="+posid+"&candrn="+candrn+"&ustat=success&module="+module;
		}
	}
	else if(window.opener.location.href.indexOf("/Marketing/Candidates/matchingjobs.php") != -1 )
	{
		var wdp=window.opener.opener.document;
		var wdpfunc = window.opener.opener;
		if((typeof wdp.conreg.summarypage=='object') && (typeof wdpfunc.Ajax_result=='function' || typeof wdpfunc.Ajax_result=='object') && (wdp.conreg.summarypage.value=='summary'))
		{
			var posid = '<?php echo $posid;?>';
			var candrn = '<?php echo $candrn;?>';
			var addr = wdp.conreg.addr1.value;
			var conid = wdpfunc.document.forms[0].con_id.value;
			var candrn = wdpfunc.document.forms[0].candrn.value;
				
			wdpfunc.Ajax_result('cand_summary_resp.php?rtype=joborder&addr='+addr+'&conid='+conid+'&candrn='+candrn+"&module="+module,'joborder','','mntcmnt5');
			window.location.href="jobSummary.php?addr="+posid+"&candrn="+candrn+"&ustat=success&module="+module;
		}
	}
	else if(window.opener.location.href.indexOf('/Companies/companySummary.php') !=-1 )
	{
		var wdp = window.opener.document;
		var wdpfunc = window.opener;
		if((typeof wdp.compreg.summarypage=='object') && (typeof wdpfunc.Ajax_result=='function' || typeof wdpfunc.Ajax_result=='object') && (wdp.compreg.summarypage.value=='summary'))
		{
			var posid = '<?php echo $posid;?>';
			var candrn = '<?php echo $candrn;?>';
			var comid=wdp.compreg.compid.value;	
			var candrn=wdp.compreg.candrn.value; 
			wdpfunc.Ajax_result('comp_summary_update.php?rtype=joborders&comid='+comid+'&candrn='+candrn,'joborders','','mntcmnt5');
			window.location.href="jobSummary.php?addr="+posid+"&candrn="+candrn+"&ustat=success&module="+module;
		}
	}
	else if(window.opener.location.href.indexOf('/Companies/allJobOrders.php') !=-1 )
	{
		var wdp=window.opener.opener.document;
		var wdpfunc = window.opener.opener;
		if((typeof wdp.compreg.summarypage=='object') && (typeof wdpfunc.Ajax_result=='function' || typeof wdpfunc.Ajax_result=='object') && (wdp.compreg.summarypage.value=='summary'))
		{
			var posid = '<?php echo $posid;?>';
			var candrn = '<?php echo $candrn;?>';
			var comid=wdp.compreg.compid.value;	
			var candrn=wdp.compreg.candrn.value; 
			wdpfunc.Ajax_result('comp_summary_update.php?rtype=joborders&comid='+comid+'&candrn='+candrn+"&module="+module,'joborders','','mntcmnt5');
			window.location.href="jobSummary.php?addr="+posid+"&candrn="+candrn+"&ustat=success&module="+module;
		}
	}
	else if(window.opener.location.href.indexOf('/Lead_Mngmt/contactSummary.php') !=-1 )
	{
		var wdp = window.opener.document;
		var wdpfunc = window.opener;
		var addr=wdpfunc.document.getElementById('supreg').addr.value;
		if((typeof wdpfunc.document.getElementById('supreg').summarypage=='object') && (typeof wdpfunc.Ajax_result=='function' || typeof wdpfunc.Ajax_result=='object') && (wdpfunc.document.getElementById('supreg').summarypage.value=='summary'))
		{
			var posid = '<?php echo $posid;?>';
			var candrn = '<?php echo $candrn;?>';
			wdpfunc.Ajax_result('contact_summary_det.php?ord=4&rtype=joborder&addr='+addr+"&module="+module,'activities','','mntcmnt5');
			window.location.href="jobSummary.php?addr="+posid+"&candrn="+candrn+"&ustat=success&module="+module;
		}
	}
	else if(window.opener.location.href.indexOf('/Marketing/Lead_Mngmt/allJobOrders.php') !=-1 )
	{
		var wdp=window.opener.opener.document;
		var wdpfunc = window.opener.opener;
		var addr=wdpfunc.document.getElementById('supreg').addr.value;
		if((typeof wdpfunc.document.getElementById('supreg').summarypage=='object') && (typeof wdpfunc.Ajax_result=='function' || typeof wdpfunc.Ajax_result=='object') && (wdpfunc.document.getElementById('supreg').summarypage.value=='summary'))
		{
			var posid = '<?php echo $posid;?>';
			var candrn = '<?php echo $candrn;?>';
			wdpfunc.Ajax_result('contact_summary_det.php?ord=4&rtype=joborder&addr='+addr+"&module="+module,'activities','','mntcmnt5');
			window.location.href="jobSummary.php?addr="+posid+"&candrn="+candrn+"&ustat=success&module="+module;
		}
	}
	else
	{
		if(placement_name=="placement")
			window.opener.location.href=window.opener.location.href;

		var posid='<?php echo $posid;?>';
		var candrn='<?php echo $candrn;?>';

	  	if(window.opener.location.href.indexOf('BSOS/Sales/Req_Mngmt/reqman.php') > 0) 
		{
			var grid = new Array();
			grid = ["<?=$grid[0] ?>","<?=$grid[1]?>","<?=$grid[2]?>",
			 "<?=$grid[3]?>","<?=$grid[4]?>","<?=$grid[5]?>", 
			 "<?=$grid[6]?>","<?=$grid[7]?>","<?=$grid[8]?>","<?=$grid[9]?>",
			  "<?=$grid[10]?>","<?=$grid[11]?>",
			   "<?=$grid[12]?>","<?=$grid[13]?>","<?=$grid[14]?>","<?=$grid[15]?>",
			    "<?=$grid[16]?>","<?=$grid[17]?>","<?=$grid[18]?>",
			     "<?=$grid[19]?>","<?=$grid[20]?>","<?=$grid[21]?>","<?=$grid[22]?>"];
			
			
			window.opener.updateGridForJobOrder(posid,<?php echo $griddata; ?>);
		}
		if(JobPostFlg=='yes'){
			window.location.href="jobSummary.php?addr="+addre+"&candrn="+candrn+"&ustat=success"+jobAPostFlag+"&module="+module;
		}else{
			window.location.href="jobSummary.php?addr="+posid+"&candrn="+candrn+"&ustat=success"+jobAPostFlag+"&module="+module;	
		}
	}

	var changeowne = '<?php echo $changeowner; ?>';
	var shareowne = '<?php echo $ownershare; ?>';
	if(changeowne == 'Yes' && shareowne == 'Private')
	{
		var parwin=window.parent.opener.location.href;
		var parentWin = window.parent.opener;

		if(parentWin && parentWin.doGridSearch!=undefined)
			parentWin.doGridSearch('reset');
		else
			window.opener.location.href=parwin;
		window.close();
	}
	else
	{
		if(JobPostFlg=='yes'){
			window.location.href="jobSummary.php?addr="+addre+"&candrn="+candrn+"&ustat=success"+jobAPostFlag+"&module="+module;	
		}else{
			window.location.href="jobSummary.php?addr="+posid+"&candrn="+candrn+"&ustat=success"+jobAPostFlag+"&module="+module;	
		}
	}
	}
	catch(e)
	{
		var posid='<?php echo $posid;?>';
		var candrn='<?php echo $candrn;?>';
		
		var changeowne = '<?php echo $changeowner; ?>';
		var shareowne = '<?php echo $ownershare; ?>';
		if(changeowne == 'Yes' && shareowne == 'Private')
		{			
			window.close();
		}
		else
		{
			if(JobPostFlg=='yes'){
				window.location.href="jobSummary.php?addr="+addre+"&candrn="+candrn+"&ustat=success"+jobAPostFlag+"&module="+module;	
			}else{
				window.location.href="jobSummary.php?addr="+posid+"&candrn="+candrn+"&ustat=success"+jobAPostFlag+"&module="+module;	
			}
				
		}
	}
</script>