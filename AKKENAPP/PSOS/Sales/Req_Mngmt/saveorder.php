<?php

	require("global.inc");
	require("functions.php");
	require("../../Include/JP_PreferenceFunc.php"); 

	$Compsno = $company;
	$contsno = $jocontact;
        $jobposprew = $jobposprw;
        

	$reptsno = $jrtcontact_sno;
	$jloc = explode("-",$jrt_loc);
	$job_location = $jloc[1];

	$bill_contact = $billcontact_sno;
	$bloc = explode("-",$bill_loc);
	$bill_address = $bloc[1];
	
	// Shift Name/ Time on Joborder
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
	

	require_once("multipleRatesClass.php");
	$ratesObj = new multiplerates();
	include_once('custom/custome_save.php');
	$udmObj = new userDefinedManuplations();
        
	$burden_status = getBurdenStatus();

	function gridcell($text)
	{
		$text=preg_replace("/(\n|\t|\r|\b)*/","",$text);
		return html_tls_specialchars(trim($text),ENT_QUOTES);
	}

	$p2=explode("|",$CRM_Joborder_Page2);
	
	$copy_posid = $posid; //Copy PosId for Perdiem Shifts insertion
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

		if($shift_type == "perdiem")
		{
			$sm_active_sno = $sm_sel_perdiem_shifts;
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

	}

	$mode_rate_type = "joborder";
	$rateRowVals = $p2[90];
	$deptid_jo = $p2[91];

	if($wsjl!="")
	{
		$p2[57]=$wsjl;
	}
	else
	{
		/*if($job_location>0)
		{
			$jlque="SELECT CONCAT(city,' ',state) FROM staffoppr_location WHERE sno=$job_location";
			$jlres=mysql_query($jlque,$db);
			$jlrow=mysql_fetch_row($jlres);
			$p2[57]=$jlrow[0];
		}*/
		$p2[57]="";
	}

	$JobType= $p2[59];
	if($JobType=='Direct' || $JobType == 'Internal Direct')
	{
		$p2[81]='0.00';
		$p2[82]='0.00';
		$p2[83]='0.00';
		$p2[84]='';
		$p2[85]='';		
		$p2[86]	='N'; 
		$p2[87]	='N';
	}

	$openPos=((int)$p2[32]-(int)$p2[33]);
	$openPos=((int)$openPos>0)?$openPos:0;
	$acceessto=explode("^",$p2[30]);
	$vcount=1;

	if($acceessto[0]=="Public")
	{
		$access="all";
	}
	else if($acceessto[0]=="Private")
	{
		$access=$p2[15];
	}
	else if($acceessto[0]=="Share")
	{		
		$access1 = $p2[15].",".trim($acceessto[1],",");//appending owner to the share data.
		$access = trim(implode(",",array_unique(explode(",",$access1))),",");//remove duplicate value.
	}
		
	if($p2[80]=="Y") //IF Post Job Order to Web Site checked
	{
		$jobPostDate=date("Y-m-d H:i:s");
		$posted_status = 'P';
	}
	else
	{
		$jobPostDate="";
		$posted_status="NP";
	}

	if($p2[89] != '')
		$p2[89] = $p2[89];
	else
		$p2[89] = 0;

	
	$allowedTags='<a><p><span><div><h1><h2><h3><h4><h5><h6><img><map><area><hr><br><br /><ul><ol><li><dl><dt><dd><table><tr><td><em><b><u><i><strong><font><del><ins><sub><sup><quote><blockquote><pre><address><code><cite><embed><object><strike><caption><center><thead><tbody>';
	
	
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
	
        if(!array_key_exists('ref_bonus_amount',$_POST)){
            $ref_bonus_amount=0;
        }
		
	$que="INSERT INTO posdesc(posid,username,conmethod,posjo,posdesc,requirements,education,experience,avg_interview,addinfo,posstatus,jostage,postype,postitle,owner,accessto,refcode,no_of_pos,openpos,closepos,catid,contact,posreportto,posworkhr,posstartdate,responsedate,status,stime,muser,mdate,vcount,company,location,billingto,bill_address,wcomp_code,bill_req,service_terms,joblocation,sourcetype,post_job_chk,posted_date,posted_status,duedate,copy_joborderid,deptid,meta_keywords,meta_desc,industryid,ref_bonus_amount,shiftid,starthour,endhour,questionnaire_group_id,shift_type,ts_layout_pref) VALUES ('','".$username."','".$p2[0]."','".$p2[1]."','".mysql_real_escape_string($p2[5])."','".mysql_real_escape_string($p2[6])."','".$p2[7]."','".$p2[8]."','".$p2[11]."','".$p2[12]."','".$p2[13]."','".$p2[14]."','".$p2[16]."','".$p2[29]."','".$p2[15]."','".trim($access,",")."','".$p2[31]."','".$p2[32]."','".$openPos."','".$p2[33]."','".$p2[34]."','".$contsno."','".$reptsno."','".$Hrstype."','".$syear."-".$smonth."-".$sday."','".$eyear."-".$emonth."-".$eday."','approve',now(),'".$username."',now(),'".$vcount."','".$Compsno."','".$job_location."','".$bill_contact."','".$bill_address."','".$workcode."','".addslashes($p2[61])."','".addslashes($p2[62])."','".$p2[57]."','".$p2[70]."','".$p2[80]."','".$jobPostDate."','".$posted_status."','".$duedateyear."-".$duedatemonth."-".$duedateday."','".$p2[89]."','".$p2[91]."','".addslashes($p2[93])."','".addslashes($p2[94])."','".$p2[95]."','".$ref_bonus_amount."','".$job_shift_id."','".$shift_st_time."','".$shift_et_time."','".$_POST['questionnaireGroup']."','".$jo_shift_type."','".$joborder_timesheet_layout_preference."')";
	mysql_query($que,$db);
	$posid=mysql_insert_id($db);

	if($compoppr_id != "" && $posid > 0)
	{
		$qry="call Oppr_Services_Update('".$compoppr_id."','".$posid."','".$p2[29]."','".$p2[16]."','".$p2[91]."','create','".$username."')";
		mysql_query($qry,$db);
	}

	$udmObj->insertUserDefinedData($posid, 4);
	/*
	//Insert UDF values to search
	$sql1 = "SELECT 	`element_lable` as col FROM udf_form_details where module = 4";
	$result1 = mysql_query($sql1, $db);
	while($row1 = mysql_fetch_array($result1))
	{
		$UDFCols[] = $row1['col'];
	}
	$col = implode(',', $UDFCols);
	
	$udfSql = "select ".$col." from udf_form_details_joborder_values where rec_id = '".$posid."'";
	$udfResult = mysql_query($udfSql,$db);
	$row_udf=mysql_fetch_row($udfResult);
	
	$contents .= " ".implode(" ", $row_udf);
	
	$sql="update search_data set profile_data= concat(profile_data," ",'".addslashes($contents)."') where uid='".$posid."'";
	mysql_query($sql,$db);
	*/
	$pp1=explode("^",$p2[2]);
	$pp2=explode("^",$p2[3]);
	$pp3=explode("^",$p2[4]);

	//Function expire date details updation in hotjobs based on no. of positions

	/*$expqry = "select filled_chk from jobposting_pref";

	$expi_res = mysql_query($expqry,$db);

	$expir_row = mysql_fetch_row($expi_res);

	$numrows = mysql_num_rows($expi_res);

	if($numrows > 0)

	{

		if($expir_row[0] == "Y")

			UpdateExpireDate_FilledJO('posdesc',$posid);

	}*/
	//---------Inserting the  jobnumber--------------------
	$sqlJoNum="UPDATE posdesc SET  jonumber='".$posid."' where  posid='".$posid."'";
	$resJoNum=mysql_query($sqlJoNum,$db);
	
	//////////////////////////// Start of the notes ////////////////////////////////////////////////////////
 	if($p2[39]!="")
 	{
		$notesup="(Unfilled Lost) Reason: ".$p2[40]."-". $p2[39];
 		$que = "INSERT INTO notes(sno,contactid,cuser,type,notes,cdate) VALUES ('','".$posid."', '".$username."','req','".addslashes($notesup)."',now())"; 		
		mysql_query($que,$db);
 	}
	
	//If shift scheduling is disabled, then save the schedule data to old tables (existing one)
	if(SHIFT_SCHEDULING_ENABLED == 'N')
	{
		//////////////////////////// Start of the Schedule ////////////////////////////////////////////////////////
		$WeekIntArray=array("Sunday"=>1,"Monday"=>2,"Tuesday"=>3,"Wednesday"=>4,"Thursday"=>5,"Friday"=>6,"Saturday"=>7);
		//////////////////////////// Start of the Schedule For Copy JO ////////////////////////////////////////////////////////
		if($copyjorder == "yes")
		{
			if($schdtogle == "yes")
			{
				if($customcheckall!="Y")
				{
					$cnt = 0;
					$jobScheduleArr = array();
					foreach($Updweek as $Updkey=>$Updval)
					{	
						$UpdTimefrom="UpdSchdFrom".$Updkey;
						$UpdTimeTo="UpdSchdTo".$Updkey;
							
						if($UpdchkSchedule[$Updkey]!="Y")
						{
							$jobScheduleArr[$cnt] = array($$UpdTimefrom,$$UpdTimeTo,$Updval,'');
							$cnt++;
						}
					}				
					foreach($Upddate as $UpdDatekey=>$UpdDateval)
					{
						$UpdDateTimefrom="UpdSchdFrom".$UpdDatekey;
						$UpdDateTimeTo="UpdSchdTo".$UpdDatekey;

						if($UpdchkSchedule[$UpdDatekey]!="Y")
						{
							$UpddateArr=explode("/",$UpdDateval);
							$Upddate_sch=$UpddateArr[2]."-".$UpddateArr[0]."-".$UpddateArr[1]." 00:00:00";
							$jobScheduleArr[$cnt] = array($$UpdDateTimefrom,$$UpdDateTimeTo,$UpdDateweek[$UpdDatekey],$Upddate_sch);
							$cnt++;
						}
					}
					foreach($jobScheduleArr as $key => $val)
					{
						$sheQry="INSERT INTO req_schedule(sno,posid,starthour,endhour,wdays,sch_date) VALUES('','".$posid."','".$val[0]."','".$val[1]."','".$WeekIntArray[$val[2]]."','".$val[3]."')";
						mysql_query($sheQry,$db);
					}
				}
			}
			else
			{
				$sheQry="INSERT INTO req_schedule(sno,posid,starthour,endhour,wdays,sch_date) VALUES(SELECT '','".$posid."',starthour,endhour,wdays,sch_date FROM req_schedule WHERE posid='".$addr."')";
				mysql_query($sheQry,$db);
			}
		}	
		//////////////////////////// End of the Schedule For Copy JO ////////////////////////////////////////////////////////

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

				if($$ChkVal!="Y" && $copyjorder != "yes" && $JobType!="Direct")
				{
					$sheQry="INSERT INTO req_schedule (sno,posid,starthour,endhour,wdays,sch_date) VALUES ('','".$posid."','".$$Timefrom."','".$$TimeTo."','".$AddweakInt."','')";
					mysql_query($sheQry,$db);
				}
				else if($$ChkVal!="Y" && $copyjorder == "yes" && strtolower($Hrstype) != strtolower($FullPartTimeRecId) && $schdtogle == "yes")
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
		}
		/////////////////////End Of the  Schedule ////////////////////////////////////////////////////////////////////////////////
	} // end if for SHIFT_SCHEDULING_ENABLED = 'N'
 
	if($skillname!="" || $usedid!="" || $levelid!="" || $skillyears!="")
	{
		$que="insert into req_skills(sno,rid,skill_name,last_used,skill_level,expe) values('','".$posid."','".$skillname."','".$usedid."','".$levelid."','".$skillyears."')";
		mysql_query($que,$db);
	}

	// Skill Management Enhancement

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


	//Inseritng into Skills
	if($copyjorder == 'yes')
	{
		if(count($updname)>0)
		{
			foreach($updname as $key=>$sval)
			{
				if($updname[$key]!="" || $updlused[$key]!="" || $updslevel[$key]!="" || $updsexp[$key]!="")
				{
				$que="insert into req_skills(sno,rid,skill_name,last_used,skill_level,expe,manage_skills_id) values('','".$posid."','".$updname[$key]."','".$updlused[$key]."','".$updslevel[$key]."','".$updsexp[$key]."','".$msid[$key]."')";
					mysql_query($que,$db);
				}
			}
		}
	}

	if(count($addname)>0)
	{
		foreach($addname as $key=>$sval)
		{
			if($addname[$key]!="" || $addlused[$key]!="" || $addslevel[$key]!="" || $addsexp[$key]!="")
			{
			$que="insert into req_skills(sno,rid,skill_name,last_used,skill_level,expe, manage_skills_id) values('','".$posid."','".$addname[$key]."','".$addlused[$key]."','".$addslevel[$key]."','".$addsexp[$key]."','".$addsmsid[$key]."')";
				mysql_query($que,$db);
			}
		}
	} 

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
	$p2[88]=($p2[86] == 'Y') ? $p2[88] : '0.00';//Checking whether it is billable or not--Raj

	$comp_margin="select netmargin,mode  from margin_setup";
	$res_margin=mysql_query($comp_margin,$db);
 	$comp_fetch=mysql_fetch_row($res_margin);
	
	//This is for getting the burden type and item ids from the formatted string
        if($burden_status == 'yes'){
            saveBurdenDetails($hdnbt_details,$hdnbi_details,'posdesc_burden_details','posid','insert',$posid);
            saveBillBurdenDetails($bill_hdnbt_details,$bill_hdnbi_details,'posdesc_burden_details','posid','insert',$posid);
        }else{
            delBurdenDetails('posdesc_burden_details','posid', $posid);
        }
	
	$que_pref="INSERT INTO req_pref(sno,username,posid,wtravle,ptravle,tcomments,wlocate,city,state,country,tmax,dmax,ccomments,brateopen,bamount,bperiod,bcurrency,prateopen,pamount,pperiod,pcurrency,currmarginmode,brateopen_amt,prateopen_amt,compmargin,compmarginmode,salary ,salary_currency,salary_period,calctype,burden,margin,otrate,ot_period,ot_currency,placement_fee,placement_curr,imethod,iterms ,pterms,tsapp,markup,sal_type,sal_range_to,otprate_amt,otprate_period,otprate_curr,otbrate_amt,otbrate_period,otbrate_curr,payrollpid,double_prate_amt,double_prate_period,double_prate_curr,double_brate_amt,double_brate_period,double_brate_curr,po_num,department,job_loc_tax,diem_lodging,diem_mie,diem_total,diem_period,diem_currency,diem_billable,diem_taxable,diem_billrate,bill_burden) VALUES('','".$username."','".$posid."','".$pp1[0]."','".$pp1[1]."','".$pp1[2]."','".$pp2[0]."','".$pp2[1]."','".$pp2[2]."','".$pp2[3]."','".$pp3[0]."','".$pp3[1]."','".$pp3[2]."','".$p2[17]."','".$p2[18]."','".$p2[19]."','".$p2[20]."','".$p2[21]."','".$p2[22]."','".$p2[23]."','".$p2[24]."','".$p2[26]."','".$p2[27]."','".$p2[28]."','".$comp_fetch[0]."','".$comp_fetch[1]."','".$p2[41]."','".$p2[42]."','".$p2[43]. "','".$p2[44]. "','".$p2[45]. "','".$p2[46]. "','".$p2[47]. "','".$p2[48]. "','".$p2[49]. "','".$p2[50]. "','".$p2[51]. "','".$p2[52]. "','".$p2[53]. "','".$p2[54]. "','".$p2[55]. "','".$p2[56]. "','".$p2[60]."','".$p2[58]."','".$p2[63]."','".$p2[64]."','".$p2[65]."','".$p2[66]."','".$p2[67]."','".$p2[68]."','".$p2[69]."','".$p2[71]."','".$p2[72]."','".$p2[73]."','".$p2[74]."','".$p2[75]."','".$p2[76]."','".$p2[77]."','".addslashes($p2[78])."','".$p2[79]."','".$p2[81]."','".$p2[82]."','".$p2[83]."','".$p2[84]."','".$p2[85]."','".$p2[86]."','".$p2[87]."','".$p2[88]."','".$p2[96]."')";
	mysql_query($que_pref,$db);

	foreach ($arrlist_page as $v)
	{
		$insertlist=explode('|',$v);
		if($insertlist[1]=="oppr")
			$que="insert into resume_history(sno,cli_name,appuser,appdate,notes,type,muser,mdate ) values('','".$scli[1]."','".$username."',now(),'".$insertlist[0]."','".$insertlist[1]."','".$username."',now())";
		else if($insertlist[1]=="req")
			$que="insert into resume_history(sno,req_id,appuser,appdate,notes,type,muser,mdate ) values('','".$posid."','".$username."',now(),'".$insertlist[0]."','".$insertlist[1]."','".$username."',now())";
		else if($insertlist[1]=="no check")
			$que="insert into resume_history(sno,req_id,appuser,appdate,notes,muser,mdate ) values('','".$posid."','".$username."',now(),'".$insertlist[0]."','".$username."',now())";
		mysql_query($que,$db);
	}

	//Updating the status of posdesc
	$que="update posdesc set status='approve' where posid='".$posid."'";
	mysql_query($que,$db);

	if($BillInfo_Toggle=="yes")
		require("commissionAdd.php");

	if($p2[36]=="no")
	{
		$sel_postdesc_pref="SELECT posdesc,requirements,education,experience,meta_keywords,meta_desc from posdesc where posid='".$p2[89]."'";
		$que_postdesc_pref=mysql_query($sel_postdesc_pref,$db);
		$fetch_postdesc_pref=mysql_fetch_array($que_postdesc_pref);

		$update_postdesc_pref = "UPDATE posdesc SET posdesc='".addslashes($fetch_postdesc_pref[0])."',requirements='".addslashes($fetch_postdesc_pref[1])."',education='".addslashes($fetch_postdesc_pref[2])."',experience='".addslashes($fetch_postdesc_pref[3])."',meta_keywords='".addslashes($fetch_postdesc_pref[4])."',meta_desc='".addslashes($fetch_postdesc_pref[5])."' where posid='".$posid."'";
		mysql_query($update_postdesc_pref,$db);
	}

	if($p2[37]=="no")
	{
		$sel_postdesc_pref = "SELECT conmethod,posjo,avg_interview,addinfo from posdesc where posid='".$p2[89]."'";
		$que_postdesc_pref = mysql_query($sel_postdesc_pref,$db);
		$fetch_postdesc_pref = mysql_fetch_array($que_postdesc_pref);

		$update_postdesc_pref = "UPDATE posdesc SET conmethod='".$fetch_postdesc_pref[0]."',posjo='".$fetch_postdesc_pref[1]."',avg_interview='".$fetch_postdesc_pref[2]."',addinfo='".$fetch_postdesc_pref[3]."' where posid='".$posid."'";
		mysql_query($update_postdesc_pref,$db);
	}

	if($schdtogle=="")
	{
		$sel_postdesc_pref = "SELECT posworkhr,posstartdate,responsedate,duedate from posdesc where posid='".$p2[89]."'";
		$que_postdesc_pref = mysql_query($sel_postdesc_pref,$db);
		$fetch_postdesc_pref = mysql_fetch_array($que_postdesc_pref);

		$update_postdesc_pref = "UPDATE posdesc SET posstartdate='".$fetch_postdesc_pref[1]."',responsedate='".$fetch_postdesc_pref[2]."',duedate='".$fetch_postdesc_pref[3]."' where posid='".$posid."'";
		mysql_query($update_postdesc_pref,$db);

	}
		
	if($p2[35] == 'no')
	{
		$comm_insert = "INSERT INTO assign_commission(sno,username,assignid,assigntype,person,type,amount,comm_calc,co_type,roleid,overwrite,enableUserInput) SELECT '','".$username."','".$posid."',assigntype,person,type,amount,comm_calc,co_type,roleid,overwrite,enableUserInput FROM assign_commission WHERE assignid = '".$p2[89]."' AND  assigntype='JO'";

	mysql_query($comm_insert,$db);

	   $sel_req_pref = "SELECT brateopen , bamount , bperiod , bcurrency , prateopen , pamount , pperiod , pcurrency , compmargin , compmarginmode , currmarginmode , brateopen_amt , prateopen_amt , salary , salary_currency , salary_period , calctype , burden , margin , otrate , ot_period , ot_currency , placement_fee , placement_curr , imethod , iterms , pterms , tsapp , markup , sal_type , sal_range_to , otprate_amt , otprate_period , otprate_curr , otbrate_amt , otbrate_period , otbrate_curr , payrollpid , double_prate_amt , double_prate_period , double_prate_curr , double_brate_amt , double_brate_period , double_brate_curr , po_num , department , job_loc_tax , diem_lodging , diem_mie , diem_total , diem_period , diem_currency , diem_billable , diem_taxable, bill_burden from req_pref where posid = '".$p2[89]."'";
	   $que_req_pref = mysql_query($sel_req_pref,$db);
	   $fetch_req_pref = mysql_fetch_array($que_req_pref);

		$update_req_pref = "UPDATE req_pref SET brateopen='".$fetch_req_pref[0]."',bamount='".$fetch_req_pref[1]."',bperiod='".$fetch_req_pref[2]."',bcurrency='".$fetch_req_pref[3]."',prateopen='".$fetch_req_pref[4]."',pamount='".$fetch_req_pref[5]."',pperiod='".$fetch_req_pref[6]."',pcurrency='".$fetch_req_pref[7]."',compmargin='".$comp_fetch[8]."',compmarginmode='".$comp_fetch[9]."',currmarginmode='".$fetch_req_pref[10]."',brateopen_amt='".$fetch_req_pref[11]."',prateopen_amt='".$fetch_req_pref[12]."',salary ='".$fetch_req_pref[13]."',salary_currency ='".$fetch_req_pref[14]."',salary_period ='".$fetch_req_pref[15]."',calctype='".$fetch_req_pref[16]."',burden='".$fetch_req_pref[17]."',margin='".$fetch_req_pref[18]."',otrate='".$fetch_req_pref[19]."',ot_period='".$fetch_req_pref[20]."',ot_currency='".$fetch_req_pref[21]."',placement_fee='".$fetch_req_pref[22]."',placement_curr='".$fetch_req_pref[23]."',imethod='".$fetch_req_pref[24]."',iterms='".$fetch_req_pref[25]."' ,pterms='".$fetch_req_pref[26]."',tsapp='".$fetch_req_pref[27]."',markup='".$fetch_req_pref[28]."' , sal_type='".$fetch_req_pref[29]."',sal_range_to='".$fetch_req_pref[30]."',otprate_amt='".$fetch_req_pref[31]."',otprate_period='".$fetch_req_pref[32]."',otprate_curr='".$fetch_req_pref[33]."',otbrate_amt='".$fetch_req_pref[34]."',otbrate_period='".$fetch_req_pref[35]."',otbrate_curr='".$fetch_req_pref[36]."',payrollpid='".$fetch_req_pref[37]."',double_prate_amt='".$fetch_req_pref[38]."',double_prate_period='".$fetch_req_pref[39]."',double_prate_curr='".$fetch_req_pref[40]."',double_brate_amt='".$fetch_req_pref[41]."',double_brate_period='".$fetch_req_pref[42]."',double_brate_curr='".$fetch_req_pref[43]."',po_num='".addslashes($fetch_req_pref[44])."',department='".$fetch_req_pref[45]."',job_loc_tax='".$fetch_req_pref[46]."',diem_lodging='".$fetch_req_pref[47]."',diem_mie='".$fetch_req_pref[48]."',diem_total='".$fetch_req_pref[49]."',diem_period='".$fetch_req_pref[50]."',diem_currency='".$fetch_req_pref[51]."',diem_billable='".$fetch_req_pref[52]."',diem_taxable='".$fetch_req_pref[53]."',bill_burden='".$fetch_req_pref[54]."' where posid='".$posid."'";
		mysql_query($update_req_pref,$db);
	}

	if($p2[38]=="no")
	{
		$sel_req_pref = "SELECT wtravle , ptravle , tcomments , wlocate , city , state , country , lcomments , tmax , dmax , ccomments from req_pref where posid = '".$p2[89]."'";
		$que_req_pref = mysql_query($sel_req_pref,$db);
		$fetch_req_pref = mysql_fetch_array($que_req_pref);

		$update_req_pref = "UPDATE req_pref SET wtravle='".$fetch_req_pref[0]."',ptravle='".$fetch_req_pref[1]."',tcomments='".$fetch_req_pref[2]."',wlocate='".$fetch_req_pref[3]."',city='".$fetch_req_pref[4]."',state='".$fetch_req_pref[5]."',country='".$fetch_req_pref[6]."',lcomments='".$fetch_req_pref[7]."',tmax='".$fetch_req_pref[8]."',dmax='".$fetch_req_pref[9]."',ccomments='".$fetch_req_pref[10]."' where posid='".$posid."'";
		mysql_query($update_req_pref,$db);
	}

	if($skillstogle=="")
	{
		$qs_skill = "INSERT into req_skills(sno,rid,skill_name,last_used,skill_level,expe) SELECT '','".$posid."',skill_name,last_used,skill_level,expe from req_skills where rid = '".$p2[89]."'"; 
		mysql_query($qs_skill,$db);
	}

	//Functions to Add joborders to  hotjobs and apijobs 

	Crmjobs2AdminjobsInsert($posid,$p2[80]);  //Add to hotjobs
	$hjsnoSql = "SELECT sno FROM hotjobs where req_id = '".$posid."' and status !='BP'";	
	$hjsnors = mysql_query($hjsnoSql,$db);
	$hjRow = mysql_fetch_row($hjsnors);
	$hjsno = $hjRow[0];

	if($p2[80]=="Y")  //IF Post Job Order to Web Site checkbox checked
	{
		Crmjobs2ApijobsInsert($posid,$p2[80]); //Add to apijobs
	}

	//Markign the job order as Hot job when post and mark is checked
	if($p2[80] == "Y" && $p2[92] == "Y")
	{
		$sqlUpdPosdesc="UPDATE posdesc SET hotjob_chk='Y'  WHERE posid='".$posid."'";
		$resUpdPosdesc=mysql_query($sqlUpdPosdesc,$db);
		
		$sqlUpdHotJobs="UPDATE hotjobs hjob,posdesc pdsc SET hjob.hotjob_chk = pdsc.hotjob_chk WHERE pdsc.posid = hjob.req_id AND hjob.status!='BP' AND pdsc.posid = '".$posid."'";
		$resUpdHotJobs=mysql_query($sqlUpdHotJobs,$db);
		
		$sqlGetHotJobs="SELECT sno FROM  hotjobs WHERE req_id='".$posid."' AND status!='BP'  ";
		$resGetHotJobs=mysql_query($sqlGetHotJobs,$db);
		$HotJobsData=mysql_fetch_row($resGetHotJobs);
		$hotjobSno=$HotJobsData[0];
		
		$sqlUpdApiJobs="UPDATE api_jobs SET hotjob_chk='Y' WHERE req_id='".$hotjobSno."'";
		$resUpdApiJobs=mysql_query($sqlUpdApiJobs,$db);
	}
	
		
	
	
	if($copyjorder != 'yes')
	{
		$type_order_id = $posid;
		$ratesObj->defaultRatesInsertion($mulRatesVal,$in_ratesCon);
		$ratesObj->multipleRatesInsertion($rateType,$billableR,$mulpayRateTxt,$payratePeriod,$payrateCurrency,$mulbillRateTxt,$billratePeriod,$billrateCurrency,$taxableR);
	}
	else
	{
		if($p2[35]=="no")
		{
			$type_order_id = $posid;
			$ratesObj->insertRatesSelAsgn($p2[89],'joborder');
		}
		else
		{
			$type_order_id = $posid;
			$ratesObj->defaultRatesInsertion($mulRatesVal,$in_ratesCon);
			$ratesObj->multipleRatesInsertion($rateType,$billableR,$mulpayRateTxt,$payratePeriod,$payrateCurrency,$mulbillRateTxt,$billratePeriod,$billrateCurrency,$taxableR);
		}
	}	
	//If shift scheduling is enabled, then save the schedule data to new tables (shift scheduling tables)
	if(SHIFT_SCHEDULING_ENABLED == 'Y')
	{
		//SHIFT SCHEDULING TABLE INSERTION START
		$shiftSnoArr	 = array();
		if($jo_shift_type == "regular")
		{
			/* including crm shift schedule class file for common functions */
			require_once('shift_schedule/crm_schedule_db.php');
			$objScheduleDetails	= new CandidateSchedule();
			
			$sm_jo_array=explode("|",$sm_form_data);
			$sm_req_cnt	= count($sm_jo_array);		
			
			//forming array to insert based on single/recurrence 
			$insertArrayData = array();
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
				
					$insertArrayData[$recNo][] = array($smAvailDate,$smAvailFromDate,$smAvailToDate,$slotGrpNo,$shiftStatus,$shiftName,$shiftColor,$shiftSNo,$shiftNumPos);
					
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
											'available',
											'".$smShiftSNo."',
											'".$smShiftNumPos."',
											'".$username."',
											NOW(),
											'".$username."',
											NOW()							
											)";

					$sm_jo_tf_res	= mysql_query($sm_jo_tf_sql, $db);

					$shift_sno = mysql_insert_id();
					if ($smShiftNumPos == 0) {
						$smShiftNumPosView = 1;
					}else{
						$smShiftNumPosView = $smShiftNumPos;
					}
					for ($sl=0; $sl <= (int)$smShiftNumPosView-1; $sl++) { 
						$position = $sl+1;
						$insertNoOfPosition = "INSERT INTO sm_timeslot_positions (sno,sm_timeslote_sno,pid,candid,type,position,shift_date,shift_starttime,shift_endtime,sm_sno) VALUES ('','".$shift_sno."','".$posid."','','posdesc','".$position."','".$smAvailDate."','".$smAvailFromDate."','".$smAvailToDate."','".$smShiftSNo."')";
						mysql_query($insertNoOfPosition, $db);
					}
				}
				if($setFlat == 1)
				{
					$tempVar++;
				}
			}	
			
			//SHIFT SCHEDULING TABLE INSERTION END	
			$shiftSnoArr = array_unique($shiftSnoArr);
			//Updating using hidden rates string
			foreach($shiftSnoArr AS $key=>$shiftSno)
			{
				$shift_rates_data = "sm_rates_".$shiftSno;			
				if($$shift_rates_data!="|" && $$shift_rates_data!="")
				{
					$objSchSchedules->updateShiftRates($posid,$$shift_rates_data,$shiftSno,$mode_rate_type);
				}
			}
			//END
			
			// JOB ORDER SHIFT SCHEDULING STATUS UPDATE - START
			$objScheduleDetails->updateShiftStatus($posid);
			// JOB ORDER SHIFT SCHEDULING STATUS UPDATE - END
			$selectRegular = "SELECT sno FROM posdesc_sm_timeslots WHERE pid='".$posid."' ";
			$resultRegular = mysql_query($selectRegular,$db);
			if (mysql_num_rows($resultRegular)==0) {
				$updateposdesc = "UPDATE posdesc SET shift_type='' WHERE posid='".$posid."'";
				mysql_query($updateposdesc,$db);
			}

		}
		else if($jo_shift_type == "perdiem")
		{
		
			/* including crm shift schedule class file for common functions */
			require_once('perdiem_shift_sch/Model/class.crm_perdiem_sch_db.php');
			$objPerdiemScheduleDetails = new CRMPerdiemShift();
			$objPerdiemScheduleDetails->insNewJobPerdiemShift($posid,$copy_posid, $candrn, $username,$copy_joborder);

			// Perdiem change for inserting the shift rates if exists
			if($sm_active_sno!="")
			{
				$shiftSnoArr = explode(',',$sm_active_sno);
			}

			//SHIFT SCHEDULING TABLE INSERTION END	
			$shiftSnoArr = array_unique($shiftSnoArr);
			//Updating using hidden rates string
			foreach($shiftSnoArr AS $key=>$shiftSno)
			{
				$shift_rates_data = "sm_rates_".$shiftSno;			
				if($$shift_rates_data!="|" && $$shift_rates_data!="")
				{
					$objSchSchedules->updateShiftRates($posid,$$shift_rates_data,$shiftSno,$mode_rate_type);
				}
			}
			//END

			$selectPerdiem = "SELECT sno FROM jo_perdiem_shift_sch WHERE posid='".$posid."' ";
			$resultPerdiem = mysql_query($selectPerdiem,$db);
			if (mysql_num_rows($resultPerdiem)==0) {
				$updateposdesc = "UPDATE posdesc SET shift_type='' WHERE posid='".$posid."'";
				mysql_query($updateposdesc,$db);
			}
		}


	} // end if SHIFT_SCHEDULING_ENABLED = 'Y'
	
	
	
	//This is for Updating Grid with newly added record..
	$sql="SELECT posdesc.posid,".tzRetQueryStringSelBoxDate("posstartdate","Date","/").",postitle,staffoppr_cinfo.cname,posdesc.jonumber,jobtype.name jobType,IF(no_of_pos - closepos <0,0,no_of_pos - closepos),IF(posdesc.accessto='all','Public',IF(posdesc.accessto='".$username."','Private', IF(locate(',',posdesc.accessto)>0,'Share','Private'))),
		status.name status,sourcetype.name sourcetype,users.name owner,".tzRetQueryStringDTime("posdesc.stime","Date","/").",".tzRetQueryStringDTime("posdesc.mdate","Date","/").",posdesc.owner,posdesc.sub_int_count,".tzRetQueryStringSelBoxDate('posdesc.duedate','Date','/').",posdesc.refcode,req_pref.po_num, loc.city,loc.state FROM posdesc
		LEFT JOIN staffoppr_cinfo ON (posdesc.company=staffoppr_cinfo.sno)
		LEFT JOIN staffoppr_cinfo loc ON (posdesc.location=loc.sno)
		LEFT JOIN manage jobtype ON (posdesc.postype=jobtype.sno) AND jobtype.type = 'jotype'
		LEFT JOIN manage status ON (posdesc.posstatus=status.sno) AND status.type = 'jostatus'
		LEFT JOIN manage sourcetype ON (posdesc.sourcetype=sourcetype.sno and sourcetype.type='josourcetype')
		LEFT JOIN users ON (posdesc.owner=users.username),req_pref WHERE req_pref.posid = posdesc.posid AND posdesc.posid={$posid} "; 
	$rs = mysql_query($sql,$db);
	$row = mysql_fetch_array($rs);

	$tot_job_data[0] = "<input type=checkbox name=auids[] OnClick=chk_clearTop() value='".$row[0]."|".$row[13]."|".$row[7]."|".gridcell($row[2])."|"."0"."'>";
	$row[1] = ($row[1]=="00/00/0000")? "" : $row[1]; //Don't display start date if it is 00/00/0000
	$row[14] = ($row[14]=="0")? "" : $row[14]; //Don't display submissions with zero value.
	$tot_job_data[1]=convertSpecCharsGlobal($row[4]); //Job ID
	$tot_job_data[2]=convertSpecCharsGlobal($row[2]);//Job Title
	$tot_job_data[3]=convertSpecCharsGlobal($row[3]); //Company Name
	$tot_job_data[4]=convertSpecCharsGlobal($row[18]); // City
	$tot_job_data[5]=convertSpecCharsGlobal($row[19]); // State
	$tot_job_data[6]=convertSpecCharsGlobal($row[16]);// REF Code
	$tot_job_data[7]=convertSpecCharsGlobal($row[17]); // PO Number
	$tot_job_data[8]=($row[15]=="00/00/0000")? "" : $row[15]; //Due Date
	$tot_job_data[9]=$row[6]; // Openings
	$tot_job_data[10]=convertSpecCharsGlobal($row[14]); // Submissions
	$tot_job_data[11]=""; //Candidates		 
	$tot_job_data[12]=""; //Interviews
	$tot_job_data[13]=convertSpecCharsGlobal($row[7]);//Type
	$tot_job_data[14]=convertSpecCharsGlobal($row[9]);//SourceType
	$tot_job_data[15]=convertSpecCharsGlobal($row[5]); //Job Type
	$tot_job_data[16]=convertSpecCharsGlobal($row[8]); //Status
	$tot_job_data[17]=convertSpecCharsGlobal($row[10]);//Owner
	$tot_job_data[18]=convertSpecCharsGlobal($row[1]);//Start
	$tot_job_data[19]=convertSpecCharsGlobal($row[11]);//Created Date
	$tot_job_data[20]=convertSpecCharsGlobal($row[12]);//Modified Date
	$tot_job_data[21]="";
	$tot_job_data[22]="redirectjob.php?addr=".$row[0];

	$grid = $tot_job_data[0]."|akkenSplit|";
	$grid.=$tot_job_data[1]."|akkenSplit|";
	$grid.=$tot_job_data[2]."|akkenSplit|";
	$grid.=$tot_job_data[3]."|akkenSplit|";
	$grid.=$tot_job_data[4]."|akkenSplit|";
	$grid.=$tot_job_data[5]."|akkenSplit|";
	$grid.=$tot_job_data[6]."|akkenSplit|";
	$grid.=$tot_job_data[7]."|akkenSplit|";
	$grid.=$tot_job_data[8]."|akkenSplit|";
	$grid.=$tot_job_data[9]."|akkenSplit|";
	$grid.=$tot_job_data[10]."|akkenSplit|";
	$grid.=$tot_job_data[11]."|akkenSplit|";
	$grid.=$tot_job_data[12]."|akkenSplit|";
	$grid.=$tot_job_data[13]."|akkenSplit|";
	$grid.=$tot_job_data[14]."|akkenSplit|";
	$grid.=$tot_job_data[15]."|akkenSplit|";
	$grid.=$tot_job_data[16]."|akkenSplit|";
	$grid.=html_tls_specialchars(addslashes(trim(eregi_replace(" +", " ", $tot_job_data[17]))),ENT_QUOTES)."|akkenSplit|";//For Owner
	$grid.=$tot_job_data[18]."|akkenSplit|";
	$grid.=$tot_job_data[19]."|akkenSplit|";
	$grid.=$tot_job_data[20]."|akkenSplit|";
	$grid.=$tot_job_data[21]."|akkenSplit|";
	$grid.=$tot_job_data[22]."|akkenSplit|";

	//end of code for getting grid data for the new job order.	

	session_unregister("CRM_Joborder_Page2");
	session_unregister("page3");
	session_unregister("page5");
	session_unregister("clientsno");
	session_unregister("listpage_ses");
	session_unregister("page7");
?>
<html>
<body>
<script language="javascript">
var module = '<?php echo $module;?>';

if(window.opener.location.href.indexOf('/BSOS/Client/reqman.php') > 0)
{
	window.location.href="/BSOS/Client/Req_Mngmt/redirectjob.php?addr=<?=$posid?>";
}
else if(window.opener.location.href.indexOf('/Admin/Jobp_Mngmt/jobman.php') > 0)
{
	window.location.href="/BSOS/Admin/Jobp_Mngmt/Joborder/redirectjob.php?addr=<?=$hjsno?>";
}
else if(window.opener.location.href.indexOf('Marketing/Candidates/revconreg0.php') > 0)
{
	//this is for candidates summary page
	var wdp=window.opener;
	var addr=wdp.document.getElementById('conreg').addr1.value;
	{
		var conid=window.opener.document.getElementById('conreg').con_id.value;
		var candrn=window.opener.document.getElementById('conreg').candrn.value;

		wdp.Ajax_result('cand_summary_resp.php?candrn='+candrn+'&conid='+conid+"&module="+module,'cand_joborder','rtype=joborder&addr='+addr,'mntcmnt5');
		window.close();
	}
}
else if(window.opener.location.href.indexOf('/Marketing/Lead_Mngmt/allJobOrders.php') > 0)
{
	//this is for contacts summary page 
	var wdp=window.opener.opener;
	var addr=wdp.document.getElementById('supreg').addr.value;
	if((typeof wdp.document.getElementById('supreg').summarypage=='object') && (typeof wdp.Ajax_result=='function' || typeof wdp.Ajax_result=='object') && (wdp.document.getElementById('supreg').summarypage.value=='summary'))
	{
		wdp.Ajax_result('contact_summary_det.php?ord=4&rtype=joborder&addr='+addr+"&module="+module,'activities','','mntcmnt5');
		//window.opener.location.reload(true);
		window.close();
	}
}
else if(window.opener.location.href.indexOf('Marketing/Lead_Mngmt/contactSummary.php') > 0)
{
	var wdp=window.opener;
	var addr=wdp.document.getElementById('supreg').addr.value;
	if((typeof wdp.document.getElementById('supreg').summarypage=='object') && (typeof wdp.Ajax_result=='function' || typeof wdp.Ajax_result=='object') && (wdp.document.getElementById('supreg').summarypage.value=='summary'))
	{
		wdp.Ajax_result('contact_summary_det.php?ord=4&rtype=joborder&addr='+addr+"&module="+module,'activities','','mntcmnt5');
		window.close();
	}
}
else if(window.opener.location.href.indexOf('Marketing/Companies/companySummary.php') > 0)
{
	//this is for job orders
	var wdp=window.opener;
	if((typeof wdp.document.getElementById('compreg').summarypage=='object') && (typeof wdp.Ajax_result=='function' || typeof wdp.Ajax_result=='object') && (wdp.document.getElementById('compreg').summarypage.value=='summary'))
	{
		var comid=wdp.document.getElementById('compreg').compid.value;
		wdp.Ajax_result('comp_summary_update.php?rtype=joborders&comid='+comid+"&module="+module,'joborders','','mntcmnt5');
		window.location.href="redirectjob.php?addr=<?=$posid?>&dest=<?=$dest?>&module="+module;
	}	
}
else if(window.opener.location.href.indexOf('/Marketing/Companies/allJobOrders.php') > 0)
{
	//opening a job order from all joborders company summary page
	var wdp=window.opener.opener;
	if((typeof wdp.document.getElementById('compreg').summarypage=='object') && (typeof wdp.Ajax_result=='function' || typeof wdp.Ajax_result=='object') && (wdp.document.getElementById('compreg').summarypage.value=='summary'))
	{
		var comid=wdp.document.getElementById('compreg').compid.value;	
		wdp.Ajax_result('comp_summary_update.php?rtype=joborders&comid='+comid+"&module="+module,'joborders','','mntcmnt5');
		window.location.href="redirectjob.php?addr=<?=$posid?>&dest=<?=$dest?>&module="+module;
		window.opener.location.reload();	
	}
}
else if(window.opener.location.href.indexOf('/Sales/Req_Post/reqman.php') > 0)
{
	window.location.href="/BSOS/Sales/Req_Post/orderdetails.php?addr=<?php echo $posid; ?>";
}
else if(window.opener.location.href.indexOf('/BSOS/Marketing/Candidates/jobordersearchdetails.php') > 0)
{
	var posid="<?php echo $posid;?>";
	try
	{
		window.opener.opener.submission_popup(posid,'');
		window.opener.close();
		window.close();
	} 
	catch(e)
	{
		window.opener.location.reload(); self.close();
	}
}
else
{
	if(window.opener.location.href.indexOf('/Sales/Req_Mngmt/reqman.php') > 0)
	{
		try
		{
			var parentWin = window.parent.opener;
			parentWin.doGridSearch('search');
			window.opener.ajaxAddNewRow("<?=$grid?>");			
		}
		catch(e)
		{
			if(window.opener)
			{		
			}
		}

		window.location.href="/BSOS/Sales/Req_Mngmt/redirectjob.php?addr=<?php echo $posid; ?>";
	}
	if(window.opener.location.href.indexOf("/BSOS/Marketing/Opportunities/editOpportunity.php") > -1)
	{
			window.opener.location.reload();
	}
         var jobPosAdmin = "<?php echo $jobposprew ; ?>";
         if(jobPosAdmin=='yes'){
            window.location.href="/BSOS/Sales/Req_Mngmt/redirectjob.php?addr=<?php echo $admaddr; ?>&jobposprw=yes&module="+module;
        }else{
            window.location.href="/BSOS/Sales/Req_Mngmt/redirectjob.php?addr=<?php echo $posid; ?>&module="+module;    
        }
	
}
</script>
</body>
</html>