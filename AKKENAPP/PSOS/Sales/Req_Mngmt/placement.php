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
	require_once($akken_psos_include_path.'commonfuns.inc');
	require("displayoptions.php");
	require_once("multipleRatesClass.php");
	require_once("shift_schedule/crm_schedule_db.php");
	require("Menu.inc");
	$menu=new EmpMenu();
	$burden_status = getBurdenStatus();
	/* Including common shift schedule class file */
	require_once('shift_schedule/class.schedules.php');
	$objSchSchedules	= new Schedules(); //Creating object for the included class file

	require_once("perdiem_shift_sch/Model/class.crm_perdiem_sch_db.php");
	$objPerdiemSchedulePosDetails = new CRMPerdiemShift();

	if (isset($placementFrm)) {
		$perdiemPlacementFrm = $placementFrm;
	}else{
		$perdiemPlacementFrm = '';
	}

	$Supusr = superusername();
	if (!isset($seqnumber)) {
		$candrn = strtotime("now"); // Set the random number if doing placement from the search windows
	}else{
		$candrn = $seqnumber;
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

	function sele($a,$b)
	{
		if($a==$b)
			return "selected";
		else
			return "";
	}

	/* shift wise candidates count for Bulk placement */
	    function getShiftWiseCandCount($candShift='')
	    {
	    	// selCandIds=82^2,82^3,82^4,83^2,83^3,83^4,84^2,84^3,84^4,85^2,85^3,85^4,86^2,86^3,86^4
	    	$shiftCandCount = array();
	    	$shiftCandCountStr = "";
	    	$candShiftAry = explode(",", $candShift);
	    	foreach ($candShiftAry as $key => $candShiftAryVal) {

	    		$candShift = explode("^", $candShiftAryVal);
	    		$candVal = $candShift[0];
	    		$shiftIdVal = $candShift[1];
	    		if (!array_key_exists($shiftIdVal, $shiftCandCount)) {
	    			$shiftCandCount[$shiftIdVal]=1;
	    		}else{
	    			$incCount = $shiftCandCount[$shiftIdVal];
	    			$shiftCandCount[$shiftIdVal] = (int)$incCount + 1;
	    		}
	    	}

	    	return $shiftCandCount;
	    }
	/* shift wise candidates count for Bulk placement  */

	$connames="";
	$repnames="";
	$con_rows=0;
	
	$newRowId=0;

	//Taking default today for calander
	$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todate=date("m/d/Y",$thisday);

	//Job order sno
	$posid=explode("|",$addr);
	$pos=$posid[0];
	$shift_id = $shiftid;
	$ratesObj = new multiplerates();
	$mode_rate_type = "joborder";
	$type_order_id = $pos;
	$ratesDefaultVal = $ratesObj->getDefaultMutipleRates();

	//checking the submission rates existis or not
	$chkSubRatesFlag = false;
	if(isset($res_sno))
	{
		$chkSubRatesFlag = $ratesObj->chkSubRatesExists($res_sno);	
	}

	//checking if any submission rates exists for any of the selected submission record to get confirmation to process
	if ($place_link == "bulk_place_cand")
	{
		$chkBulkSubRatesFlag = $ratesObj->chkSubRatesExistsForSelCands($selCandIds, $pos);

		//check whether bulk place for single or multiple candiates
		if($chkBulkSubRatesFlag == true) //73^2^326,73^3^327
		{
			if(count(explode(",", $selCandIds)) == 1)
			{
				$chkSubRatesFlag = true;

				$arr=explode("^", $selCandIds);
				$res_sno=$arr[2];


			}
		}
	}
	

	if($place_link=="place_cand")
	{
		$dque = "SELECT IF(candidate_list.ctype='Employee','Employee',IF(candidate_list.owner='$username','My Candidate','Candidate')) FROM  candidate_list  WHERE candidate_list.sno='".$posid[1]."'";
		$dres=mysql_query($dque,$db);
		$cand_fet=mysql_fetch_row($dres);
		$type=$cand_fet[0]; 
	}

	if($type=="Employee")
	{
		$sque  = "select candid,ctype,sno,supid,username from candidate_list where username='".$userid."'";
		$sres = mysql_query($sque,$db);
		$srow = mysql_fetch_row($sres);
		$candtype = $srow[1];
		$typeOfCandidate = $srow[1];

		$uid=$srow[0];
		$str_emp=substr($srow[0], 0, 7);

		if($str_emp!='empprof')
			$sel_que = "select username,sno from emp_list where sno = ".trim(str_replace('emp','',$uid));
		else
			$sel_que = "select username,sno from empprofile_list where sno = ".trim(str_replace('empprof','',$uid));
		mysql_query($que,$db);
		$res_que = mysql_query($sel_que,$db);
		$emp_user = mysql_fetch_row($res_que);

		// checking any active assignments are there are not.
		$que="select count(1) from hrcon_jobs where ustatus='active' and jtype='OP' and username='$emp_user[0]'";
		$res=mysql_query($que,$db);
		$rsassign=mysql_fetch_array($res);

		// checking any active assignments are there are not.
		$que="select emptype from hrcon_compen where ustatus='active' and username='$emp_user[0]'";
		$res=mysql_query($que,$db);
		$rscompen=mysql_fetch_array($res);

		$rsassign=$rsassign[0]."|".$rscompen[0];
	}

	$job_sel="SELECT a.postitle,a.refcode,a.contact,a.posreportto,a.location,
	".tzRetQueryStringSelBoxDate("a.posstartdate","YMDDate","-").",
	".tzRetQueryStringSelBoxDate("a.responsedate","YMDDate","-")."
	,a.billingto,a.bill_address,a.postype,a.catid,a.posjo,a.addinfo,".tzRetQueryStringSelBoxDate("a.posenddate","YMDDate","-").",
	a.posworkhr,b.bamount,b.bperiod,b.bcurrency,b.pamount,b.pperiod,b.pcurrency,a.posstatus,a.company,a.avg_interview,
	b.salary,b.salary_period,b.salary_currency,b.calctype,b.burden,b.margin,b.otrate,b.ot_period,
	b.ot_currency,b.placement_fee,b.placement_curr,b.imethod,b.iterms,b.pterms,b.tsapp,a.wcomp_code,
	b.brateopen,b.prateopen,b.brateopen_amt,b.prateopen_amt,b.markup,b.otprate_amt,b.otprate_period,b.otprate_curr,
	b.otbrate_amt,b.otbrate_period,b.otbrate_curr,b.payrollpid,a.conmethod,b.double_prate_amt,b.double_prate_period,
	b.double_prate_curr,b.double_brate_amt,b.double_brate_period,b.double_brate_curr,b.po_num,b.department,
	b.job_loc_tax,a.bill_req,a.service_terms,IF(b.diem_lodging='0.00','',b.diem_lodging),IF(b.diem_mie='0.00','',b.diem_mie), 
	IF(b.diem_total='0.00','',b.diem_total),b.diem_period,b.diem_currency,b.diem_billable,b.diem_taxable,b.diem_billrate,a.deptid,a.industryid, b.bill_burden,a.stime,a.shiftid,a.starthour,a.endhour,a.shift_type,a.ts_layout_pref
	FROM posdesc a, req_pref b 
	WHERE a.posid=b.posid AND a.posid='".$posid[0]."'";
	$res_job_sel=mysql_query($job_sel,$db);
	$job_fetch=mysql_fetch_row($res_job_sel);
	$shift_type = $job_fetch[79];

	if($job_fetch[38]=='Online')
		$job_fetch[38]='Online';
	else
		$job_fetch[38]='Manual';

	//Query to get data of contact and company of a joborder
	//$contact_name="SELECT '', CONCAT_WS( ' ', fname, mname, lname) FROM staffoppr_contact WHERE status='ER' and  (FIND_IN_SET('$username',accessto)>0 OR owner='$username' OR accessto='ALL') and sno=".$job_fetch[2];
	$contact_name="SELECT '', CONCAT_WS( ' ', fname, mname, lname), accessto, status,sno FROM staffoppr_contact WHERE status='ER' AND crmcontact='Y' AND sno=".$job_fetch[2];
	$res_contact=mysql_query($contact_name,$db);
	$contact_fetch=mysql_fetch_row($res_contact);
	
	$chkContAccess = chkAvailAccessTo($contact_fetch[2],$contact_fetch[3],$username);

	//$company_name="SELECT cname,'',sno,address1,address2,city,state,'',bill_req,service_terms,acc_comp,bill_contact,bill_address FROM staffoppr_cinfo WHERE (FIND_IN_SET('$username',accessto)>0 OR owner='$username' OR accessto='ALL') and status='ER' and sno =".$job_fetch[22];
	$company_name="SELECT cname,'',sno,address1,address2,city,state,'',bill_req,service_terms,acc_comp,bill_contact,bill_address,accessto,status FROM staffoppr_cinfo WHERE status='ER' and sno =".$job_fetch[22];
	$res_comp=mysql_query($company_name,$db);
	$comp_fetch=mysql_fetch_row($res_comp);
	$comp_fetch[0] = stripslashes($comp_fetch[0]);
	$comp_fetch[1] = stripslashes($comp_fetch[1]);
	$comp_fetch[3] = stripslashes($comp_fetch[3]);
	$comp_fetch[4] = stripslashes($comp_fetch[4]);
	$comp_fetch[5] = stripslashes($comp_fetch[5]);
	$comp_fetch[6] = stripslashes($comp_fetch[6]);
	$comp_fetch[11] = stripslashes($comp_fetch[11]);
	$comp_fetch[12] = stripslashes($comp_fetch[12]);
	$chkCompAccess = chkAvailAccessTo($comp_fetch[13],$comp_fetch[14],$username);

	$jobloc_rows=mysql_num_rows($res_comp);
	$comp_rows=$jobloc_rows;

	//Replacing Billing Contact and Billing Address with Related Customer of company in Accounting Biling Address.
	/*
	$attention = "";
	if($comp_fetch[10] != '0' && $comp_fetch[10] != '') // acc_comp filed
	{
		$getAccCustBillAddr = "SELECT bill_contact,bill_address,attention FROM staffacc_cinfo WHERE sno = '".$comp_fetch[10]."'";
		$resAccCustBillAddr=mysql_query($getAccCustBillAddr,$db);
		$rowAccCustBillAddr=mysql_fetch_row($resAccCustBillAddr);

		$attention = $rowAccCustBillAddr[2];

		if($rowAccCustBillAddr[1]!='0')
		{
			$chkCrmComp = "SELECT crm_comp FROM staffacc_cinfo WHERE sno = '".$rowAccCustBillAddr[1]."'";
			$resCrmComp = mysql_query($chkCrmComp,$db);
			$rowCrmComp = mysql_fetch_row($resCrmComp);
			$job_fetch[8] = $rowCrmComp[0];
		}
		else
		{
			$job_fetch[8] = 0;
		}

		if($rowAccCustBillAddr[0] != '0')
		{
			$chkCrmCont = "SELECT crm_cont FROM staffacc_contact WHERE sno = '".$rowAccCustBillAddr[0]."'";
			$resCrmCont = mysql_query($chkCrmCont,$db);
			$rowCrmCont = mysql_fetch_row($resCrmCont);

			$job_fetch[7] = $rowCrmCont[0];
		}
		else
		{
			$job_fetch[7] = 0;
		}
	}
	else
	{
		$job_fetch[8] = $comp_fetch[12];
		$job_fetch[7] = $comp_fetch[11];
	}
	*/

	// If no Billing Contact / Address defined at Job Order then set Company - Billing

	if($job_fetch[8]==0 && $comp_fetch[12]>0)
		$job_fetch[8] = $comp_fetch[12];

	if($job_fetch[7]==0 && $comp_fetch[11]>0)
		$job_fetch[7] = $comp_fetch[11];

	//Query to get the contacts of a related company if Contact is set for Job Order -- For drop list to change the contact instead of search and select 
	if($comp_fetch[2]!='0' && trim($comp_fetch[2])!='' && $job_fetch[2]>0)
	{
		$con_sel_rows=0;
		$con_names="SELECT staffoppr_contact.sno, CONCAT_WS( ' ', staffoppr_contact.fname, staffoppr_contact.mname, staffoppr_contact.lname ),accessto,status FROM staffoppr_contact WHERE staffoppr_contact.csno='".$comp_fetch[2]."' AND staffoppr_contact.status='ER' AND staffoppr_contact.crmcontact='Y' order by staffoppr_contact.fname, staffoppr_contact.mname, staffoppr_contact.lname";
		$res_con=mysql_query($con_names,$db);
		$con_rows=mysql_num_rows($res_con);
		while($fetch_con=mysql_fetch_row($res_con))
		{
			$chkSBAccess = chkAvailAccessTo($fetch_con[2],$fetch_con[3],$username);
			$connames.="<option lang='".$chkSBAccess."' value=".$fetch_con[0]." ".compose_sel($job_fetch[2],$fetch_con[0])." >".dispfdb($fetch_con[1])." - ".$fetch_con[0]."</option>";
			$con_sel_rows++;
		}
	}

	//Query to get data of a candidate
	$candidate_name="SELECT candidate_list.sno,CONCAT_WS(' ', candidate_list.fname, candidate_list.mname, candidate_list.lname),candidate_list.status,candidate_list.supid,candid FROM candidate_list WHERE candidate_list.username='".$userid."'";
	$res_cand=mysql_query($candidate_name,$db);
	$cand_fetch=mysql_fetch_row($res_cand);

	$sdate=explode("-",$job_fetch[5]);
	$hrdate=explode("-",$job_fetch[6]);
	$edate=explode('-',$job_fetch[13]);
	$startdate=$sdate[1]."/".$sdate[2]."/".$sdate[0];
	$eenddate=$hrdate[1]."/".$hrdate[2]."/".$hrdate[0];
	$enddate=$edate[1]."/".$edate[2]."/".$edate[0];

	//Query to get the recruiter/Vendor
	$que_rec="SELECT staffoppr_contact.sno, CONCAT_WS( ' ', staffoppr_contact.fname, staffoppr_contact.mname, staffoppr_contact.lname ),staffoppr_contact.csno FROM staffoppr_contact WHERE staffoppr_contact.sno='".$cand_fetch[3]."' AND staffoppr_contact.status='ER' AND staffoppr_contact.crmcontact='Y'";
	$res_rec=mysql_query($que_rec,$db);
	$fetch_rec=mysql_fetch_row($res_rec);
	$fetch_rec_rows=mysql_num_rows($res_rec);

	$check_value=explode('-',$job_fetch[11]);
	$con_method=explode("-",$job_fetch[52]);
	$Htype=$job_fetch[14];

	///////////////////////////////////////////////////////////////// getting role data
	$strCon = "";								
	switch(getManage($job_fetch[9]))
	{
		case 'Direct':
		case 'Internal Direct':
			$strCon = "'BR', 'PR', 'MN', 'MP'";
			break;	  
		case 'Internal Temp/Contract':
		case 'Temp/Contract':
			$strCon = "'RR'"; 
			break;	  
		case 'Temp/Contract to Direct':									
			$strCon = "''";
			break;	  
		default:
			$strCon = "''";
			break;	  
	}

	if($strCon != "")
		$condition = " rp.commissionType NOT IN (".$strCon.") OR ";
// removing the conditions for the commission roles ---- Jyothi - 07/09/12
	//getRoles = "SELECT cs.sno, cs.roletitle, IFNULL(rp.amount,'') AS amount, IFNULL(rp.amountmode,'PER') AS amountmode, IFNULL(rp.commissionType,'') AS commissionType, cs.overwrite  FROM company_commission AS cs 
	//left join rates_period AS rp ON (cs.sno = rp.parentid AND rp.parenttype = 'COMMISSION' AND (IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF(DATE_FORMAT(NOW(),'%Y-%m-%d'),STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, DATE_FORMAT(NOW(),'%Y-%m-%d') BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d')))) WHERE cs.status = 'active'  AND ( ".$condition." cs.commission_default = 'Y')";
	
	 $getRoles = "SELECT cs.sno, cs.roletitle  FROM company_commission AS cs  WHERE cs.status = 'active' order by cs.roletitle, cs.commission_default";
	$resRoles = mysql_query($getRoles,$db);

	$lstRoleVal = "";
	$rolesSelectIds = array();
	while($rowRole = mysql_fetch_array($resRoles))
	{
		if($lstRoleVal == '')
			$lstRoleVal = $rowRole[0]."^".$rowRole[1];
		else
			$lstRoleVal .= "|Akkensplit|".$rowRole[0]."^".$rowRole[1];	
		$rolesSelectIds[] = $rowRole[0];
	}

	////---Added Functionality To Auto Select Worker Comp Code When Burdern is selected---///
	$sts_sel = "SELECT autoset_workercomp,payburden_required,billburden_required FROM burden_management";
	$sts_res = mysql_query($sts_sel, $db);
	$sts_rec = mysql_fetch_array($sts_res);
	$autowcc_status = $sts_rec['autoset_workercomp'];
	$payburden_status = $sts_rec['payburden_required'];
	$billburden_status = $sts_rec['billburden_required'];	
	////---End of Code---///	
			
	function DisplaySchdule($pos,$Htype)
	{
		global $username,$maildb,$db,$user_timezone;

		$RecordArray=array();
		array_push($RecordArray,$Htype);
		$query="select sno,if(DATE_FORMAT(sch_date,'%c/%e/%Y')='0/0/0000','',".tzRetQueryStringSelBoxDate("Date(sch_date)","Date","/")."),if(wdays>0,wdays,''),starthour,endhour from req_schedule where posid='".$pos."'";
		$QryExc=mysql_query($query,$db);

		if(mysql_num_rows($QryExc)>0)
		{
			while($SchRow=mysql_fetch_row($QryExc))
			{
				array_push($RecordArray,implode("|^AkkSplitCol^|",$SchRow));
			}
		}
		return implode("|^AkkenSplit^|",$RecordArray);
	}

	$retsch=DisplaySchdule($pos,$Htype);

	function addrValue($row1,$row2,$row3)
	{
		$comp_addr="";
		if($row1!='' && $row2!='')
			$comp_addr=$row1.", ".$row2;
		else if($row1!='')
			$comp_addr=$row1;
		else if($row2!='')
			$comp_addr=$row2;

		if($row3!='')
		{
			if($comp_addr!='')
				$comp_addr.=", ".$row3;
			else
				$comp_addr=$row3;
		}
		return($comp_addr);
	}

	$DispTimes=display_SelectBox_Times();

	//Taking default today for calander
	$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todate=date("m/d/Y",$thisday);

	if($job_fetch[27]=='R')
	{
		$src_status="rates";
		$rate_display="display:";
	       $hide_rate="display:";
		$margin_display="display:none";
		$markup_display="display:none"	;
	}	


	$padding = 'style="padding:160px;"';

	if(strpos($_SERVER['HTTP_USER_AGENT'],'Gecko') > -1)
	{
		$rightflt = "style= 'width:68px;'";
		$padding = 'style="padding:118px;"';
	}
	

//Getting the burden type and item details
if ($burden_status == 'yes'){
	$burden_details_sel	= "SELECT CONCAT(t1.bt_id,'|',t2.burden_type_name) bt_details, GROUP_CONCAT(CONCAT(t1.bi_id,'^',t3.burden_item_name,'^',t3.burden_value,'^',t3.burden_mode,'^',t3.ratetype,'^',t3.max_earned_amnt,'^',t3.billable_status) SEPARATOR '|') bi_details, t1.ratetype FROM posdesc_burden_details t1 JOIN burden_types t2 ON t2.sno = t1.bt_id JOIN burden_items t3 ON t3.sno = t1.bi_id WHERE t1.posid = '".$pos."' GROUP BY t1.bt_id";

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
	else if($job_fetch[28] != "" && $job_fetch[28] != 0)
	{
			$existingBurdenOpt = '<option value="old|placement|'.$posid[0].'" selected>Older Burden</option>';
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

	} elseif ($job_fetch[74] != "" && $job_fetch[74] != 0) {

		$existingBillBurdenOpt	= '<option value="old|joborder|'.$posid[0].'" selected>Older Burden</option>';
	}
}

$get_bt_list_sql = "SELECT  bt.sno, bt.burden_type_name, bt.ratetype FROM burden_types bt WHERE bt.bt_status = 'Active'";
$get_bt_list_rs = mysql_query($get_bt_list_sql,$db);
$arr_burden_type	= array();

while ($row = mysql_fetch_object($get_bt_list_rs)) {

	$arr_burden_type[$row->sno]['burden_type']	= $row->burden_type_name;
	$arr_burden_type[$row->sno]['rate_type']	= $row->ratetype;
}

//To get burden type id based on location.
$get_bt_id_location_que =   "SELECT burden_type from staffoppr_location where sno=".$job_fetch[4];
$get_bt_id_location_rs  =   mysql_query($get_bt_id_location_que,$db);
$bt_id_location_row =   mysql_fetch_row($get_bt_id_location_rs); 
$chk_bt =   false;
if($bt_sno  ==  $bt_id_location_row[0])
{
    $chk_bt = true;
}

// Keep tracks on which shift candidate is submitted.						
if(isset($shiftsnos) && !empty($shiftsnos)){
	$shift_snos = $shiftsnos;
}else{
	// Get the  shift snos on which the candidate was submitted
	$shift_snos = $objSchSchedules->getShiftsAssocWithJOCand($posid[0],$userid,'submitted',$shift_id);
	
}

//StartDate, EndDate and ExpectedEndDate years, 20 years for past years and 10 years to future.
$startYear = displayPastFutureYears($sdate[0]);
$dueYear = displayPastFutureYears($edate[0]);
$expectedEndYear = displayPastFutureYears($hrdate[0]);
$hiredYear = displayPastFutureYears($date[2]);


// Code for Worksite Code When Akkupay Enabled
if(DEFAULT_AKKUPAY=='Y')
{
	$get_dept_qry = "SELECT deptid FROM posdesc WHERE posid='".$posid[0]."'";
	$get_dept_res = mysql_query($get_dept_qry,$db);
	$get_dept_row = mysql_fetch_array($get_dept_res);
	$dept_id 	  = $get_dept_row[0];

	$get_locdet_qry = "SELECT cm.serial_no FROM department dept
						LEFT JOIN contact_manage cm ON cm.serial_no = dept.loc_id
						WHERE dept.sno='".$dept_id."'";
	$get_locdet_res = mysql_query($get_locdet_qry,$db);
	$get_locdet_row = mysql_fetch_array($get_locdet_res);
	$location_sno   = $get_locdet_row[0];
}

?>
<?php include('header.inc.php') ?>
<title>Placement <?php if($job_fetch[0] !='')echo "-".$job_fetch[0]; ?></title>
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/candidatesInfo_tab.css">
<link href="/BSOS/css/grid.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/filter.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/site.css" type=text/css rel=stylesheet>
<link href="/BSOS/css/crm-editscreen.css" type=text/css rel=stylesheet>
<link type="text/css" rel="stylesheet" href="/BSOS/css/crm-summary.css">
<link rel="stylesheet" href="/BSOS/popupmessages/css/popup_message.css" media="screen" type="text/css">
<!-- <link type="text/css" rel="stylesheet" href="/BSOS/css/select2.css"> -->
<link type="text/css" rel="stylesheet" href="/BSOS/css/select2_loc.css">
<script type="text/javascript">
	var rate_calculator='<?php echo RATE_CALCULATOR;?>';
	var onLoadModeCheck = 'onload';
	var MarkupCheck = '';
	var symmetrypayroll = '<?php echo AKKUPAY_WITH_SYMMETRY ;?>';
</script>
<script language="JavaScript" src="/BSOS/scripts/common_ajax.js"></script>
<script language=javascript src=scripts/joborderinfo.js></script>
<script language=javascript src=scripts/commission.js></script>
<script language=javascript src=scripts/validateplacement.js></script>
<script language=javascript src="/BSOS/scripts/dynamicElementCreatefun.js"></script>
<script language=javascript src="/BSOS/scripts/tabpane.js"></script>
<script language=javascript src=scripts/place_schedule.js></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popupMsgArray.js"></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popup-message.js"></script>
<script language=javascript src="/BSOS/scripts/schedule.js"></script>
<script language=javascript src="/BSOS/scripts/calMarginMarkuprates.js"></script>
<script language="JavaScript" src="/BSOS/scripts/common.js"></script>
<script language=javascript src="/BSOS/scripts/multiplerates.js"></script>
<script language=javascript src="/BSOS/scripts/crmLocations.js"></script>
<?php getJQLibs(['jquery','jqueryUI']);?>
<script language=javascript src="/BSOS/scripts/multiplerates.js"></script>
<script type="text/javascript" src="/BSOS/scripts/shift_schedule/jquery.min.js"></script> 
<!-- loads modalbox css -->
<link rel="stylesheet" type="text/css" media="all" href="/BSOS/css/shift_schedule/calschdule_modalbox.css" />

<!-- loads some utilities (not needed for your developments) -->
<?php getCSSLibs(['jqueryUI','jqueryUITheme','jqueryUIStructure']);?>
<link rel="stylesheet" type="text/css" href="/BSOS/css/shift_schedule/schCalendar.css">

<!-- loads jquery ui -->
<script type="text/javascript" src="/BSOS/scripts/shift_schedule/schCal_timeframe.js"></script>

<!-- <script type="text/javascript" src="/BSOS/scripts/select2.js"></script> -->
<script type="text/javascript" src="/BSOS/scripts/RateCalculator.js"></script>
<!-- Getting syncHR Mandatory Definded field value into Javascript Variable -->
<script type="text/javascript">
	var syncHRDefault = '<?php echo DEFAULT_SYNCHR; ?>';
</script>

<script type="text/javascript">
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

function doSelectJTitles() {

	if ($("#jotype").val() == '') {

		alert("Please select a Job Type for Placement");

	} else {
		
		/* 
			This Function used to select Job Title when Theraphy Source Enable or not 
			And also parsing the selected Custom Rate Ids.
		*/
		if(document.getElementById("theraphySourceEnable") !== null){
		   
		    var theraphySourceEnable = window.document.getElementById('theraphySourceEnable').value;
		    if(theraphySourceEnable =="Y"){

				var selectedRateValue = window.document.getElementById('selectedcustomratetypeids').value;
				var comm_payrate = window.document.getElementById('comm_payrate').value;
				var comm_billrate = window.document.getElementById('comm_billrate').value;
				var over_time_pay =  document.getElementById("otrate_pay").value;
				var over_time_bill =  document.getElementById("otrate_bill").value;
				var double_time_pay =  document.getElementById("db_time_pay").value;
				var double_time_bill =  document.getElementById("db_time_bill").value;
				if(selectedRateValue != "" || comm_payrate != "" || comm_billrate !="" || over_time_pay !="" || over_time_bill !="" || double_time_pay !="" || double_time_bill !="" ){
			    alert("Changing the Job Title will effect the rates in Billing section.");
			    var jotype	= $("#jotype").val();
			    joarray = jotype.split("|");
			    var deptjoborder = $("#deptjoborder").val();
			    var v_width  = 600;
			    var v_heigth = 440;
			    var top1	= (window.screen.availHeight-v_heigth)/2;
			    var left1	= (window.screen.availWidth-v_width)/2;

			    var url= "/BSOS/Sales/Req_Mngmt/jo_titles.php?jotype="+joarray[0]+"&deptjoborder="+deptjoborder+"&pfrom=assign";
			    var remote	= window.open(url, "parent","width="+v_width+"px,height="+v_heigth+"px,statusbar=yes,menubar=no,scrollbars=yes,left="+left1+"px,top="+top1+"px,dependent=yes,resizable=yes");

			    remote.focus();

			}else{

			    var jotype = $("#jotype").val();
			    joarray = jotype.split("|");
			    var deptjoborder = $("#deptjoborder").val();
			    var v_width  = 600;
			    var v_heigth = 440;
			    var top1 = (window.screen.availHeight-v_heigth)/2;
			    var left1 = (window.screen.availWidth-v_width)/2;

			    var url= "/BSOS/Sales/Req_Mngmt/jo_titles.php?jotype="+joarray[0]+"&deptjoborder="+deptjoborder+"&pfrom=assign";
			    var remote = window.open(url, "parent","width="+v_width+"px,height="+v_heigth+"px,statusbar=yes,menubar=no,scrollbars=yes,left="+left1+"px,top="+top1+"px,dependent=yes,resizable=yes");

			    remote.focus();
			}	
		    }else{

			 var jotype	= $("#jotype").val();
			joarray = jotype.split("|");
			var deptjoborder = $("#deptjoborder").val();
			var v_width  = 600;
			var v_heigth = 440;
			var top1	= (window.screen.availHeight-v_heigth)/2;
			var left1	= (window.screen.availWidth-v_width)/2;
			var url= "/BSOS/Sales/Req_Mngmt/jo_titles.php?jotype="+joarray[0]+"&deptjoborder="+deptjoborder+"&pfrom=assign";
			var remote	= window.open(url, "parent","width="+v_width+"px,height="+v_heigth+"px,statusbar=yes,menubar=no,scrollbars=yes,left="+left1+"px,top="+top1+"px,dependent=yes,resizable=yes");

			remote.focus();

		    }
		      
		}else{
		    var jotype	= $("#jotype").val();
		    joarray = jotype.split("|");
		    var deptjoborder = $("#deptjoborder").val();
		    var v_width  = 600;
		    var v_heigth = 440;
		    var top1	= (window.screen.availHeight-v_heigth)/2;
		    var left1	= (window.screen.availWidth-v_width)/2;

		    var url= "/BSOS/Sales/Req_Mngmt/jo_titles.php?jotype="+joarray[0]+"&deptjoborder="+deptjoborder+"&pfrom=assign";
		    var remote	= window.open(url, "parent","width="+v_width+"px,height="+v_heigth+"px,statusbar=yes,menubar=no,scrollbars=yes,left="+left1+"px,top="+top1+"px,dependent=yes,resizable=yes");

		    remote.focus();
		}	
		
	}
}

function removeTitle(){
	document.getElementById('jotitle').value	= '';
	document.getElementById('jotitlespan').innerHTML = '';
	document.getElementById('jotitlelinkspan').innerHTML = '<a href="javascript:doSelectJTitles();" class="edit-list">Select Title</a>';
}

/*
 * Therapy Source JobTitle Custom Rates
 */

function displayJobTitleCustomRates(data)
{
   
    var restr = data.split("^^^");
    var regRateSplit = restr[1].split("^^");

    document.getElementById("multipleRatesTab").innerHTML = regRateSplit[0];
    // Assigning Pay Rates
    
    /* Regular Pay Rate */
    var RegPayrate	= regRateSplit[1].split("|");
    document.getElementById("comm_payrate").value = RegPayrate[1]; 
    document.getElementById("payrateper").innerHTML = RegPayrate[2]; 
    document.getElementById("payratecur").innerHTML = RegPayrate[3];
    document.getElementById("reg_pay_Bill_NonBill").innerHTML = RegPayrate[4];

       /* OverTime Pay Rate */
    var overPayrate = regRateSplit[2].split("|");
    document.getElementById("otrate_pay").value = overPayrate[1];
    
    overtimepayids = ['otper_pay','perotrate_pay'];
    for (var ot = 0; ot < overtimepayids.length; ot++) { 
	if(document.getElementById(overtimepayids[ot]) !== null){
		document.getElementById(overtimepayids[ot]).innerHTML= overPayrate[2];
	}
    }

    overtimecurr = ['otcurrency_pay','payotrate_pay'];
    for (var otc = 0; otc < overtimecurr.length; otc++) { 
	if(document.getElementById(overtimecurr[otc]) !== null){
		document.getElementById(overtimecurr[otc]).innerHTML=overPayrate[3];
	}
    }

    document.getElementById("ot_pay_Bill_NonBill").innerHTML = overPayrate[4];
    /* DoubleTime Pay Rate */
    var doublePayrate = regRateSplit[3].split("|")
    document.getElementById("db_time_pay").value = doublePayrate[1]; 
    document.getElementById("db_time_payper").innerHTML = doublePayrate[2]; 
    document.getElementById("db_time_paycur").innerHTML = doublePayrate[3];
    document.getElementById("dt_pay_Bill_NonBill").innerHTML = doublePayrate[4];

    // Assigning Bill Rates				
    var billRegrate = regRateSplit[4].split("|");
    document.getElementById("comm_billrate").value = billRegrate[1]; 
    document.getElementById("billrateper").innerHTML = billRegrate[2]; 
    document.getElementById("billratecur").innerHTML = billRegrate[3];
    document.getElementById("reg_bill_Tax_NonTax").innerHTML = billRegrate[4];

    var billOverrate = regRateSplit[5].split("|");
    document.getElementById("otrate_bill").value = billOverrate[1]; 

    
    /* OverTime Bill Rate */
    overtimebillids = ['otper_bill','payotrate_bill','perotrate_bill']; 
    for (var otb = 0; otb < overtimebillids.length; otb++) { 
	if(document.getElementById(overtimebillids[otb]) !== null){
		document.getElementById(overtimebillids[otb]).innerHTML=billOverrate[2]; 
	}
    }

    overtimebillcurr = ['otcurrency_bill','payotrate_bill'];
    for (var otbc = 0; otbc < overtimebillcurr.length; otbc++) { 
	if(document.getElementById(overtimebillcurr[otbc]) !== null){
		document.getElementById(overtimebillcurr[otbc]).innerHTML=billOverrate[3];
	}
    }

    document.getElementById("ot_bill_Tax_NonTax").innerHTML = billOverrate[4];
    var doublebillrate = regRateSplit[6].split("|");
    document.getElementById("db_time_bill").value = doublebillrate[1]; 
    document.getElementById("db_time_billper").innerHTML = doublebillrate[2]; 
    document.getElementById("db_time_billcurr").innerHTML = doublebillrate[3];
    document.getElementById("db_bill_Tax_NonTax").innerHTML = doublebillrate[4];
    if (regRateSplit[7] !="" || regRateSplit[7] !=0) {
	    var selectedrate = regRateSplit[7];
	    document.getElementById("selectedcustomratetypeids").value =selectedrate;
	    pushAddEditRateRowArray(selectedrate);
	    var ratearray = new Array();
	    ratearray = selectedrate.split(",");
	    for(i=0;i<=ratearray.length-1;i++)
	    {
		pushSelectedPayRateidsArray(ratearray[i]);
	    } 
    }else{
    	document.getElementById("multipleRatesTab").innerHTML = '';
		addEditRateRow.length = 0;
		selectedprtidsarray.length = 0;
		document.getElementById("selectedcustomratetypeids").value = '';
    }
   if(rate_calculator=='Y'){
        var payrateVal = document.getElementById('comm_payrate').value;
        var billrateVal = document.getElementById('comm_billrate').value;
        if (payrateVal != "" && billrateVal != "")
        {
            //to convert the flat rate with the pay rate and convert it to burden percentage
            var manage_burden_status = document.getElementById('manage_burden_status').value;
            if(manage_burden_status == 'yes') //If burden management is enable.
            {
                calcBurdenPercentage(payrateVal);
            }
            if (window.location.href.indexOf('BSOS/Sales/Req_Mngmt/placement.php') > 0)
            {
                calculateMarkupMarginNew(document.resume);
            }
            
        }
        else
        {
            document.getElementById('comm_margin').value = "";
            document.getElementById('comm_margin_span').innerHTML = "0.00";
            document.getElementById('comm_markup').value = "";
            document.getElementById('comm_markup_span').innerHTML = "0.00";
            document.getElementById('margincost').innerHTML = "$0.00";
            document.getElementById('margincost').style.color = "#000000";
        }
    }else{
         calculatebtmargin();   
    }
}

//Function used to load the submission rates 
function getSubmissionRates()
{
	$.ajax({
		url:'/include/shift_schedule/checkrateonshift.php',
		type:'POST',
		data:'joborderSubmissionRates=submission&res_sno=<?=$res_sno;?>',
		success:function(data){
			if (data !="norates" && data !="") {
				displayJobTitleCustomRates(data);
			}
		}
	});
}

//Function used to prompt the user the choose submission rates/ billing rates
function promptUsertoSelRateOption()
{
	if(confirm("Do you want to continue with Submission rates to Bulk place Candidate(s)/Employee(s).\n\nClick OK to continue\nClick Cancel to continue with rates on Placement screen.")) {
		document.getElementById("rate_on_submission").value = "YES";
		$res_sno_status=1;

	}
	else {
		document.getElementById("rate_on_submission").value = "NO";
		$res_sno_status=0;
	}	
}
</script>
<script type="text/javascript">
/* Added autopreloader function to show loading icon, until the page completely loads */
$(window).on('load',function(){
	$('#autopreloader').fadeOut('slow',function(){$(this).remove();});
	$('.newLoader').fadeOut('slow',function(){$(this).remove();});
        onLoadModeCheck = 'NoMode';
});
</script>
<?php
//Make the date fields disabled only when the shift scheduling is enabled
if(SHIFT_SCHEDULING_ENABLED == 'Y')
{
?>
<script type="text/javascript">
	
	$(document).ready(function() {
		$(window).load(function() {
		var shift_snos = "<?php echo $shift_snos;?>";
		// Disables Start Date/Due Date/Expected End Date/Hours Section		
		if($('#jo_shiftsch').attr("checked")){
			if($("#smonth").length){
				$('#smonth').attr('disabled', true);
				$('#sday').attr('disabled', true);
				$('#syear').attr('disabled', true);
				$('#josdatecal').hide();
			}			
			if($("#vmonth").length){
				$('#vmonth').attr('disabled', true);
				$('#vday').attr('disabled', true);
				$('#vyear').attr('disabled', true);
				$('#jovdatecal').hide();
			}
			if($("#dmonth").length){
				$('#dmonth').attr('disabled', true);
				$('#dday').attr('disabled', true);
				$('#dyear').attr('disabled', true);
				$('#joddatecal').hide();
			}
			if($("#vtmonth").length){
				$('#vtmonth').attr('disabled', true);
				$('#vtday').attr('disabled', true);
				$('#vtyear').attr('disabled', true);
				$('#jovtdatecal').hide();
				
			}
			if($("#Hrstype").length){
				$('#Hrstype').attr('disabled', true);
			}
			if($("#notimechk").length){
				$('#notimechk').attr('disabled', true);
			}
			if($("#reason").length){
				$('#reason').attr('disabled', true);
			}
		}
		if($("#shift_type").val() == "regular")
		{
			showhideShiftLegends();
			showSelShift(shift_snos);
			<?php
			if(!$chkSubRatesFlag)
			{
				echo "getCheckNoOfShiftsInJobOeder();";
			}
			?>
			
		}
		else
		{
			//showhideShiftLegends(); - No need
			showSelPerdiemShift(shift_snos);			
			<?php
			if(!$chkSubRatesFlag)
			{
				echo "getCheckNoOfShiftsInJobOeder();";
			}
			?>
		}
		getComWorks("<?=$job_fetch[72] ?>");
	});
	});
	function getComWorks(eid){
		if(eid > 0){
			$.ajax({
				cache: false,
				url: "/BSOS/Include/getCommonData.php?atype=jobplacecand",
				type: "POST",
				data: {cid:eid,wid:$('wid').val()},
				success: function(data) 
				{
					var wwcodes = data.split('###');
					$("#worksitecode").html(wwcodes[0]);
					$("#workcode").html(wwcodes[1]);
				}
			});	
		}else{
			$("#worksitecode,#workcode").html("<option value=''>Select</option>");
		}

		var dept_id = $('#deptjoborder').val();
		var cpid= $('#projcode').val();
		var type = $('#empytype').val();
		var uname = $('#empuname').val();
		//alert(dept_id+'=== '+type+'*** '+uname);
		$.ajax({
			cache: false,
			url: "/BSOS/Include/getCommonData.php?atype=certified",
			type: "POST",
			data: {mode:'jocertified',cpid:cpid,type:type,uname:uname,dept_id:dept_id},
			success: function(data) 
			{
				var dl = data;
				$("#projcode").html(dl);
			}
		});
	}
</script>
<?php
}
?>
<style>
.cdfAutoSuggest select{min-width: 250px !important;}
.select2-container.select2-container-multi.required.selCdfCheckVal{width: 250px !important;}
.timegrid{width:2.064% !important; }
.timeheadPosi{ top:3px !important;}
.closebtnstyle{ float:left; *margin-top:3px; vertical-align:middle; }

@media screen and (-webkit-min-device-pixel-ratio:0) and (max-width: 1200px){
     .timegrid{ width:2.062% !important}
     .custom_rate_remove_button img{
         top:12px !important;
     }
}
@media screen and (-webkit-min-device-pixel-ratio:0) { 
    /* Safari 5+ ONLY */
	.custom_rate_remove_button img{
         top:12px !important;
     }
	::i-block-chrome, .timegrid{ width:2.060% !important;}
}

</style>
<style type="text/css">
.crmsummary-jocomp-table select[name="workcode"]:focus{
	box-shadow: 0px 0px 3px 0px #3eb8f0;
	border: 1px solid #3eb8f0 !important
}
.crmsummary-jocomp-table select[name="bill_burdenType"]:focus{
	box-shadow: 0px 0px 3px 0px #3eb8f0;
	border: 1px solid #3eb8f0 !important
}
.crmsummary-jocomp-table select[name="burdenType"]:focus{
	box-shadow: 0px 0px 3px 0px #3eb8f0;
	border: 1px solid #3eb8f0 !important
}
@media screen\0 {
.timegrid{width:2.044% !important; }	
.summaryform-formelement{ height:18px; font-size:11px !important; }
a.crm-select-link:link{ font-size:11px !important; }
a.edit-list:link{ font-size:10px !important;}
.summaryform-bold-close-title{ font-size:9px !important;}
.center-body { text-align:left !important;}
.crmsummary-jocomp-table td{ font-size:12px !important ; text-align:left !important;}
.summaryform-nonboldsub-title{ font-size:9px}
#smdatetable{ font-size:11px !important;}
.summaryform-formelement{ text-align:left !important; vertical-align:middle}
.crmsummary-content-title{ text-align:left !important}
.crmsummary-edit-table td{ text-align:left !important}
.summaryform-bold-title{ font-size:12px !important;}
.summaryform-nonboldsub-title{ font-size:10px !important;}
.sstabelwidth td{ font-size:10px !important; }
}
    #multipleRatesTab table tr td{padding:3px 8px 2px 10px; white-space:nowrap;}
    #multipleRatesTab table tr td span select{margin-left:14px }
    .panel-table-content-new .summaryform-bold-title { font-size:12px; font-weight:bold;}
	/*#modal-wrapper{width:800px !important;}*/
	.scroll-area{width:1000px;}
    @media screen and (-webkit-min-device-pixel-ratio:0) {
    /* Safari only override */
    <!-- ::i-block-chrome,.timegrid { width:2.07%; }-->
	.custom_rate_remove_button img{ top:13px;}
    }
a.crm-select-linkNewBg:hover{color:#3AB0FF}
.custom_defined_main_table td{text-align: left !important; vertical-align: middle;}
.innertbltd-custom table td {vertical-align: middle !important;}
.custom_defined_head_td{width: 150px !important; padding:0px !important;}
.crmsummary-editsections-table_noline table, .crmsummary-editsections-table_noline tr, .crmsummary-editsections-table_noline td{ margin:0px; padding:3px}
.alert-ync-text {
    font-family: arial !important;
    font-size: 14px !important;
    margin-top: 0 !important;
}
.alert-ync-text span {
    font-weight: normal !important;
}
.modalDialog_contentDiv{
    height: 300px !important;
    left: 50% !important;
    margin-left: -200px !important;
    margin-top: -225px !important;
    top: 50% !important;
}

.modalDialog_contentDivDynClass{
    height: 300px !important;
    left: 50% !important;
    margin-left: -200px !important;
    margin-top: -225px !important;
    top: 50% !important;
}


.modalDialog_contentDivDynClass{height:auto !important; top:25% !important; position: absolute !important; margin-top: 0px !important; }

.modalDialog_contentDiv{height:auto !important; top:25% !important; position: absolute !important; margin-top: 0px !important; }
.alert-ync-container{ padding-bottom: 10px !important; height: inherit !important; }

 @media screen and (-ms-high-contrast: active), (-ms-high-contrast: none) {  
 .modalDialog_contentDiv{height:auto !important; top:25% !important; position: absolute !important; }
}
 @media screen and (-ms-high-contrast: active), (-ms-high-contrast: none) {  
 .modalDialog_contentDivDynClass{height:auto !important; top:25% !important; position: absolute !important; }
}
@media screen and (-ms-high-contrast: active), (-ms-high-contrast: none) {  
	#readMoreShiftData{float: left !important; margin-left: 50% !important;}
}
.mloadDiv #autopreloader { position: fixed; left: 0; top: 0; z-index: 99999; width: 100%; height: 100%; overflow: visible; 
opacity:0.35;background:#000000 !important; }
.mloadDiv .newLoader{position:absolute; top:50%; left:50%;z-index:99999;margin-left:-67px;margin-top:-67px;width:135px;height:135px;}
.mloadDiv .newLoader img{border-radius:69px !important;}
.crmsummary-jocomp-table .fa-calculator:before{color: #138dc5;font-size:14px;}
.cdfCustModel .cdfCustModalbox {
	width: 504px !important;
    margin-left: -252px !important;
    margin-top: -180px !important;
	top:50% !important;
}
.cdfCustTextArea { 
	width: 424px !important;
}
.cdfJoborderBlk .select2-container, .cdfJoborderBlk .select2-drop, .cdfJoborderBlk .select2-search, .cdfJoborderBlk .select2-search input{
	width: 300px !important;
}
.fltcenter{ float: left; margin-top: 9px; }
#s2id_jrt_loc .select2-choice { border: 1px solid #fff; padding: 6px 4px; }
#s2id_jrt_loc .select2-arrow b{ margin-top: 3px; }
.modalDialog_transparentDivs{z-index: 99998}

/*
	Perdiem Shift Scheduling
*/

/* .JoAssignPerdiemEditModal-wrapper{position: fixed !important; width:620px !important; height: 365px !important; margin-left: -320px !important;}
.JoAssignPerdiemEditModal-wrapper .scroll-area{width: 600px !important;}
	
.JoPerdiemEditModal-wrapper{position: fixed !important; width:500px !important; height: 250px !important; margin-left:-250px !important; margin-top:-125px !important; top: 50% !important; left:50% !important; }
.JoPerdiemEditModal-wrapper .scroll-area{width: 500px !important;} */

body.perdiemnoscroll {
    overflow: hidden;
}

</style>
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
</head>
<body class="cdfCustModel" onLoad="hideElements();">
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

<input type="hidden" id="confirmstate" value="edit" />

<form name="resume" id="resume" method="POST">
<input type=hidden name=psource value="<?php echo $psource;?>">
<input type=hidden name=pusername value="<?php echo $pusername;?>">
<input type=hidden name=dateval value="<?php echo $todate;?>">
<input type=hidden name=addr value="<?php echo $contact_fetch[2];?>">
<input type=hidden id=posid name=posid value="<?php echo $posid[0]?>">
<input type=hidden name=Compsno>
<input type=hidden name=addnewrow>
<input type=hidden name=page15 value="" />
<input type=hidden name="page215" id="page215" value='<?php echo $page215;?>'>
<input type=hidden name=addr id="addr" value="<?php echo $addr;?>">
<input type=hidden name=recno value="<?php echo $recno;?>">
<input type=hidden name=uname value="">
<input type=hidden name=candstat value="<?php echo $candstat;?>">
<input type="hidden" name="userid" id="userid" value="<?php echo $userid; ?>">
<input type="hidden" name="stat">
<input type="hidden" name='repcon' value="<?php echo $rep_rows;?>">
<input type="hidden" name='jobcon' value="<?php echo $jobloc_rows;?>">
<input type="hidden" name='compDisplay'>
<input type="hidden" name='jobDisplay'>
<input type="hidden" name='repDisplay'>
<input type="hidden" name='confirmToClose'>
<input type="hidden" name='currentStatus' value="<?php echo $rsassign;?>">
<input type="hidden" name='typeOfCandidate' value="<?php echo $typeOfCandidate;?>">
<input type=hidden name=tday value='<?php echo $todate; ?>'>
<input type=hidden name=hireempname value="<?php echo dispTextdb($cand_fetch[1]);?>">
<input type="hidden" name='Supuser' value="<?php echo $Supusr;?>">
<input type="hidden" name='usernme'  id="empuname" value="<?php echo $emp_user[0];?>">
<input type="hidden" name='empytype' id="empytype" value="<?php echo $type;?>">
<input type="hidden" name='src_status' value="<?php echo $src_status;?>">
<input type="hidden" name="empvalues">
<input type="hidden" name='acceptJobtitle'>
<input type="hidden" name='place_link' id="place_link" value="<?php echo $place_link; ?>">
<input type="hidden" name="offset" value="<?=$offset?>">
<input type="hidden" name="candidateVal" id="candidateVal" value="<?=$candidateVal?>">
<input type="hidden" name="cand_sno" id="cand_sno" value="<?=$cand_sno?>">
<input type="hidden" name="candid" id="candid" value="<?=$candid?>">
<input type="hidden" name="seqnumber" value="<?=$seqnumber?>">
<input type="hidden" name="candrn" id="candrn" value="<?=$candrn?>">
<input type="hidden" name="mulRatesVal" id="mulRatesVal" value="">
<input type="hidden" name="hdnRoleCount" id="hdnRoleCount" value="">
<input type="hidden" name="roleData" id="roleData" value="<?php echo html_tls_entities($lstRoleVal,ENT_QUOTES);?>">
<input type=hidden name="jobwindowstatus" id="jobwindowstatus" value="edit">
<input type="hidden" name="sm_form_data" id="sm_form_data" value="" />
<input type="hidden" name="sm_enabled_option" id="sm_enabled_option" value="<?php echo SHIFT_SCHEDULING_ENABLED; ?>" />
<input type="hidden" name="mode_type" id="mode_type" value="joborder">
<input type="hidden" name="sm_sel_shifts" id="sm_sel_shifts" value="" />
<input type="hidden" name="sm_empsno" id="sm_empsno" value="<?php echo $emp_user[1];?>">
<input type="hidden" name="compcrfmstatus" id="compcrfmstatus" value="1">
<input type="hidden" name="contactcrfmstatus" id="contactcrfmstatus" value="1">
<input type="hidden" name="comploccrfmstatus" id="comploccrfmstatus" value="1"> 
<input type="hidden" id="panel_type" name="panel_type" value="" />
<input type="hidden" name="theraphySourceEnable" id="theraphySourceEnable" value="<?php echo THERAPY_SOURCE_ENABLED;?>" />
<input id="overrideshifttimeslotempcand" name="overrideshifttimeslotempcand" value="yes" type="hidden">
<!--hiddens for Shift Name/ Time -->
<input type="hidden" name="shift_time_from" id="shift_time_from" value="" />
<input type="hidden" name="shift_time_to" id="shift_time_to" value="" />
<input type="hidden" name="nextPos" id="nextPos" value="0">
<input type="hidden" name="perdiemPlacementFrm" id="perdiemPlacementFrm" value="<?php echo $perdiemPlacementFrm; ?>">

<!-- Bulk Placement Hidden -->
<input type="hidden" name="selCandShiftIds" id="selCandShiftIds" value="<?php echo $selCandIds;?>" />
<!-- Akkupay Enabled Hidden -->
<input type="hidden" name="akkupayEnable" id="akkupayEnable" value="<?php echo DEFAULT_AKKUPAY;?>">
<input type="hidden" name="symmetry" id="symmetry" value="<?php echo SS_ENABLED;?>">

<div class="modal-header">
		<?php
			if ($place_link == "bulk_place_cand") {
				$name=explode("|","fa fa-thumbs-up~Process&nbsp;Bulk&nbsp;Place|fa-ban~Close");

				$link=explode("|","javascript:doUpdate(this);|javascript:window.close()");

				$heading="&nbsp;Bulk&nbsp;Place";
				$menu->showHeadingStrip1010($name,$link,$heading);

			}else{
				$name=explode("|","fa fa-thumbs-up~Place&nbsp;Candidate|fa-ban~Cancel");

				if(!empty($emp_user[1]))
					$link=explode("|","javascript:checkPendingEmpSMSlots(this);|javascript:window.close()");
				else
					$link=explode("|","javascript:doUpdate(this);|javascript:window.close()");

				$heading="&nbsp;New&nbsp;Placement";
				$menu->showHeadingStrip1010($name,$link,$heading);
			}
		?>
</div>
<div class="modal-body">
	<?php
		/**
		* Include the file to generate user defined fields.
		*
		*/
		$mod = 4;
		$candPosid = $posid[0];
		include_once($app_inc_path."custom/getcustomfields.php");
		echo "<style> 
		ul.cdfCheckUlLl li input{ margin: 5px 6px; vertical-align: inherit; } 
		.summarytext input[type='radio'], .summarytext input[type='checkbox']{ width: 16px; }
		</style>";
	?>
	
	<fieldset>
		<table class="table align-middle">
			<tr>
				<td width="150">Status</td>
				<td colspan="4">
					<select class="summaryform-formelement form-select w-250" name="jobstatus">
						<option value="">--select--</option>
						<?php
						$jocat="";
						$jotype="";
						$joindustry="";

						$que1="select sno,name,type from manage where type='jotype' or type='jocategory' or type='jostatus' or type='joindustry' or type='joclassification' order by name";
						$res1=mysql_query($que1,$db) or die(mysql_error());
						$place_jobtype="";
						while($dd1=mysql_fetch_row($res1))
						{
							if($dd1[2]=='jotype')
							{
								$jotype.="<option  value='".$dd1[0]."|".$dd1[1]."' ".compose_sel($job_fetch[9],$dd1[0])." >".$dd1[1]."</option>";

								if($dd1[1]=='Temp/Contract')
									$jobtypeval=$dd1[0];

								if($dd1[0]==$job_fetch[9])
								{
									if($dd1[1]=='Temp/Contract' || $dd1[1]=='Internal Temp/Contract' )
									{
										$hire_sal_style="display:none";//td1
										$hire_sal_style1="display:none";//tr
										$hire_sal_style2="display:none";//td2
										$Temp_Block="display:";
										$Temp_None="display:none";
										$assign_display="display:";
										$Dir_Ass_None="display:none";
										$Dir_Int="display:none";
										$place_jobtype = $dd1[1];
									}
									else if($dd1[1]=='Direct' || $dd1[1]=='Internal Direct')
									{
										$hide_rate="display:none";
										$hire_sal_style="display:none";//td1
										$hire_sal_style1="display:";//tr
										$hire_sal_style2="display:";//td2
										$assign_display="display:none";
										$Dir_Int="display:none";
										$Dis_Over_Bill="display:none";
										$Dis_db_Bill="display:none";
										$place_jobtype = $dd1[1];
										$shiftname_time = "display:none";
										$shiftname_note = "display:none";

										if($dd1[1]=='Direct')
										{
											$Dir_Block="display:";
											$Dir_None="display:none";
											$Dir_Ass_None="display:none";
											$Dis_job_chk="display:none";
										}

										if(($job_fetch[24]=='' || $job_fetch[24]=='0.00') && ($job_fetch[18]!='' || $job_fetch[18]!='0.00'))
										{
											$job_fetch[24]=$job_fetch[18];
											$job_fetch[25]=$job_fetch[19];
											$job_fetch[26]=$job_fetch[20];
										}
									}
									else
									{
										$hire_sal_style="display:";//td1
										$hire_sal_style1="display:";//tr
										$hire_sal_style2="display:";//td2
										$Chng_Block="display:";
										$Chng_None="display:none";
										$assign_display="display:";
										$Dir_Ass_None="display:none";
										$place_jobtype = $dd1[1];
									}
								}
							}
							else if($dd1[2]=='jostatus')
							{
								$jostatus.="<option  value='".$dd1[0]."'".getSel("Filled",$dd1[1])." >".$dd1[1]."</option>";
							}
							else if($dd1[2]=='jocategory')
							{
								$jocat.="<option  value='".$dd1[0]."'".compose_sel($job_fetch[10],$dd1[0])." >".$dd1[1]."</option>";
							}
							else if($dd1[2]=='joindustry')
							{
								$joindustry.="<option  value='".$dd1[0]."'".compose_sel($job_fetch[73],$dd1[0])." >".$dd1[1]."</option>";
							}
							else if($dd1[2]=='joclassification')
							{
								$joclassification.="<option  value='".stripslashes($dd1[0])."'".sele($job_fetch[100],$dd1[0])." >".stripslashes($dd1[1])."</option>";
							}
						}
						echo $jostatus;
						?>
					</select>
					<?php if(EDITLIST_ACCESSFLAG){ ?> 
					<a href="javascript:doManage('Status','jobstatus');" class="edit-list">edit list</a>
					<?php } ?>
				</td>
			</tr>
			<?php 
			if ($place_link == "bulk_place_cand") 
			{
				//checking if any submitted candidates having the submission rates or not

			?>
			<tr>
				<input type="hidden" name="recven" id="recven" value="">
				<input type="hidden" name="candidate" id="candidate" value="">
				<input type="hidden" name="recvencom" id="recvencom" value="">
				<input type="hidden" name="rate_on_submission" id="rate_on_submission" value="NO">
			</tr>
			<?php 
			} 
			else 
			{ 
			?>
			<tr>
				<td>Candidate</td>
				<td colspan="4">
					<a href="javascript:viewSummary('candidate','<?php echo $cand_fetch[0]."|".$cand_fetch[2]?>');"><?php echo $cand_fetch[1];?></a>
					<?php
					if($fetch_rec_rows!='0')
					{
						?>
						&nbsp;<span class="summaryform-nonboldsub-title">( Recruiter/Vendor: </span> <a href="javascript:viewSummary('contact','<?php echo $fetch_rec[0]?>');" class="edit-list"><?php echo dispfdb($fetch_rec[1]);?></a>&nbsp;<span class="summaryform-formelement">&nbsp;</span><span class="summaryform-nonboldsub-title">)</span></b>
						<?php
					}
					?>
					<input type="hidden" name="recven" id="recven" value="<?php echo $fetch_rec[0];?>">
					<input type="hidden" name="candidate" id="candidate" value="<?php echo $cand_fetch[0];?>">
					<input type="hidden" name="recvencom" id="recvencom" value="<?php echo $fetch_rec[2];?>">
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td>Job Type</td>
				<td colspan="4">
					<select class="summaryform-formelement form-select w-250" name="jotype" id="jotype" onChange="hideElements();" disabled="disabled">
					<option value="">--select--</option>
					<?php echo $jotype;?>
					</select>
					<input type=hidden name='jobtypeval' id='jobtypeval' value='<?php echo $jobtypeval;?>'>
				</td>
			</tr>
			<tr>
				<td>Industry</td>
				<td colspan="4" align=left>
					<select class="summaryform-formelement form-select w-250" name="joindustryid" id="joindustryid">
					<option value="">--select--</option>
					<?php echo $joindustry;?>
					</select><?php if(EDITLIST_ACCESSFLAG){ ?> &nbsp;<a href="javascript:doManage('joindustry','joindustryid');" class="edit-list">edit list</a> <?php } ?>
				</td>
			</tr>
			<tr>
				<td>Category</td>
				<td colspan="4" align=left>
					<select class="summaryform-formelement form-select w-250" name="jocat" id="jocat">
					<option value="">--select--</option>
					<?php echo $jocat;?>
					</select><?php if(EDITLIST_ACCESSFLAG){ ?> &nbsp;<a href="javascript:doManage('jocategory','jocat');" class="edit-list">edit list</a> <?php } ?>
				</td>
			</tr>
			<?php
			if(JOBORDER_TITLES == 'TRUE')
			{
			?>
			<tr>
				<td>Job Title<?php echo $mandatory_synchr; ?></td>
				<td colspan="4">
					<?php
					if(empty($job_fetch[0]))
					{
					?>			
						<span id="jotitlespan" class="afontstyle"><?php echo $job_fetch[0];?></span>
						<input class="summaryform-formelement" type="hidden" name="jotitle" id="jotitle" size=40  maxsize=150 maxlength=150 value="<?php echo $job_fetch[0]?>" readonly>
						<input type="hidden" name="jobtitleid" id="jobtitleid" value="" />
						<span id="jotitlelinkspan">
							<a href="javascript:doSelectJTitles();" class="edit-list">Select Title</a>
						</span>&nbsp;|&nbsp;
						<a href="javascript:viewSummary('job','<?php echo $posid[0]?>');" class="edit-list">view job order</a>
					<?php
					}
					else
					{
					?>
						<span id="jotitlespan" class="afontstyle"><?php echo $job_fetch[0]?></span>
						<input class="summaryform-formelement" type="hidden" name="jotitle" id="jotitle" size=40  maxsize=150 maxlength=150 value="<?php echo $job_fetch[0]?>" readonly>
						<input type="hidden" name="jobtitleid" id="jobtitleid" value="" />
						<span id="jotitlelinkspan">
							<a href="javascript:doSelectJTitles();" class="edit-list">Change Title</a>&nbsp;|&nbsp;<a href="javascript:removeTitle()" class="edit-list">Remove Title</a>
						</span>
						&nbsp;|&nbsp;
						<a href="javascript:viewSummary('job','<?php echo $posid[0]?>');" class="edit-list">view job order</a>
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
				<td>Job Title<?php echo $mandatory_synchr; ?></td>
				<td colspan="4"><input class="summaryform-formelement form-control w-250" type="text" name="jotitle" id="jotitle" size=40  maxsize=150 maxlength=150 value="<?php echo $job_fetch[0]?>">&nbsp;<a href="javascript:viewSummary('job','<?php echo $posid[0]?>');" class="edit-list">view job order</a></td>
			</tr>
			<?php
			}
			?>
			<tr>
				<td>Ref. Code</td>
				<td colspan="4" align=left><input class="summaryform-formelement form-control w-250" type=text name=jorefcode size=40 maxsize=25 maxlength=25 value="<?php echo $job_fetch[1]?>"></td>
			</tr>
			<tr>
				<td>HRM Department</td>
				<td colspan="4" align=left>
					<?php 
					if(SS_ENABLED == 'Y'){
					departmentCompSymSelBox('deptjoborder', $job_fetch[72], 'form-select w-250 summaryform-formelement','','jobplacecand','','yes');	
					}else{
					departmentSelBox('deptjoborder', $job_fetch[72], 'form-select w-250 summaryform-formelement','','jobplacecand','','yes');
					}?>
				</td>
			</tr>
			
			<tr>
				<td>Ultigigs Timesheet Layout</td>
				<td colspan="4" align=left>
					<select name="placement_timesheet_layout_preference" id="placement_timesheet_layout_preference" class="summaryform-formelement form-select w-250">
						<option value="" <?php if($job_fetch[80] == "") { echo "selected"; } ?>>--- Select Template ---</option>
						<option value="Regular" <?php if($job_fetch[80] == "Regular") { echo "selected"; } ?>>Regular</option>
						<option value="TimeInTimeOut" <?php if($job_fetch[80] == "TimeInTimeOut") { echo "selected"; } ?>>Time In &amp; Time Out</option>
						<option value="Clockinout" <?php if($job_fetch[80] == "Clockinout") { echo "selected"; } ?>>Clock In &amp; Out</option>
					</select>
				</td>
			</tr>
			
			<tr>
				<td>Company</td>
				<?php
				if($job_fetch[22]!='0' && $comp_rows!='0')
				{
					if($chkCompAccess == 'No')
					{
						print "<td colspan='4'><span id='company-change'><span class='summaryform-formelement'>".dispfdb($comp_fetch[0]).' - '.$comp_fetch[2]."</span><span class=summaryform-nonboldsub-title>&nbsp;(&nbsp;</span><a href=javascript:parent_popup('jobcompany') class=edit-list>change</a>&nbsp;<a href=javascript:parent_popup('jobcompany')><i class='fa fa-search'></i></a><span class=summaryform-formelement></span><span class=summaryform-nonboldsub-title>)&nbsp;</span><span><a href=javascript:RemoveSelectedItem('jobcompany','') class='edit-list' title='Remove Company'>remove</a></span></span></td>";
					}
					else
					{
						print "<td colspan='4'><span id='company-change'><a class=crm-select-link href=javascript:viewSummary('company','".$comp_fetch[2]."');>".dispfdb($comp_fetch[0]).' - '.$comp_fetch[2]."</a><span class=summaryform-nonboldsub-title>&nbsp;(&nbsp;</span><a href=javascript:parent_popup('jobcompany') class=edit-list>change</a>&nbsp;<a href=javascript:parent_popup('jobcompany')><i class='fa fa-search'></i></a><span class=summaryform-formelement></span><span class=summaryform-nonboldsub-title>)&nbsp;</span><span><a href=javascript:RemoveSelectedItem('jobcompany','') class='edit-list' title='Remove Company'>remove</a></span></span></td>";
					}
				}
				else{
					print "<td colspan='4'><span id='company-change'><a class=crm-select-link href=javascript:parent_popup('jobcompany')>select company</a>&nbsp;<a href=javascript:parent_popup('jobcompany')><i class='fa fa-search'></i></a></td>";
				}
				?>
				<input type="hidden" name="company" id="company" value="<?php echo $comp_fetch[2];?>">
				<?php 
				if($comp_fetch[2]!='0' && trim($comp_fetch[2])!='')
					echo "<input type='hidden' name='comprows' id='comprows' value='1'>";
				else
					echo "<input type='hidden' name='comprows' id='comprows' value='0'>";
				?>
				<input type="hidden" name="compname" value="<?php echo dispfdb($comp_fetch[0]);?>">
			</tr>
			<tr>
				<td>Contact</td>
				<?php
				if($job_fetch[2]!='0')
				{
					$selBox = ($con_sel_rows > 1) ? "Yes" : "No";
					?>
				<td colspan='4'>
					<span id="contact-change">
					<span id="conname-change">
						<?php
						if($chkContAccess == 'No')
							echo "<span class='summaryform-formelement'>".dispfdb($contact_fetch[1])." - ".$contact_fetch[4]."</span>";
						else
						{
						?>
							<a class="crm-select-link" href="javascript:viewSummary('contact','<?php echo $job_fetch[2]?>');"><?php echo dispfdb($contact_fetch[1])." - ".$contact_fetch[4];?></a>
						<?php
						}
						?>
					</span>
					<span class="summaryform-nonboldsub-title">&nbsp;(&nbsp;</span>
					<?php
					if($job_fetch[22]!='0' && trim($comp_fetch[2])!='' && $con_sel_rows > 1)
					{
						?>
						&nbsp;<select class="summaryform-formelement" name="jocontact" onChange="showcontactdata(this.value,'contact')"><option value="">--select--</option><?php echo $connames; ?></select>
						&nbsp;<span class="summaryform-formelement">&nbsp;|&nbsp;</span>
						<?php
					}
					?>
					<a href="javascript:contact_popup1('refcontact')" class="edit-list">change</a>&nbsp;<a href="javascript:contact_popup1('refcontact')"><i class="fa fa-search"></i></a><span class="summaryform-formelement">&nbsp;|&nbsp;</span><a href="javascript:newScreen('contact','refcontact');" class="edit-list">new</a><span class="summaryform-nonboldsub-title">&nbsp;)&nbsp;</span>
					<span id="contactRemove"><a href="javascript:RemoveSelectedItem('refcontact','<?php echo $selBox;?>')" class='edit-list' title="Remove Contact">remove</a></span>
					</span>
				</td>
				<?php
				}
				else
				{
				?>
				<td colspan='4'>
					<span id="contact-change"><a class="crm-select-link" href="javascript:contact_popup1('refcontact')">select contact</a>&nbsp;<a href="javascript:contact_popup1('refcontact')"><i class="fa fa-search"></i></a><span class="summaryform-formelement">&nbsp;|&nbsp;</span><a href="javascript:newScreen('contact','refcontact');" class="edit-list">new contact</a></span>
				</td>
				<?php
				}
				?>
				<input type="hidden" name="contact" value="<?php echo $job_fetch[2];?>">
				<?php 
				if($job_fetch[2]!='0' && trim($job_fetch[2])!='')
					echo "<input type='hidden' name='controws' id='controws' value='1'>";
				else
					echo "<input type='hidden' name='controws' id='controws' value='0'>";
				?>
				<input type="hidden" name="conname" value="<?php echo dispfdb($contact_fetch[1]);?>">
			</tr>
			<?php
				$jrtcontact=$job_fetch[3];
				$jrt_loc=$job_fetch[4];

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
			<tr>
				<input type="hidden" name="jrtcompany_sno" id="jrtcompany_sno" value="<?php echo $jrtcompany;?>">
				<td>Job Location<?php echo $mandatory_synchr; ?></td>
				<td colspan='4'>
					<span id="jrtdisp_comp">
						<input type="hidden" name="jrt_loc" id="jrt_loc">
						<a href="javascript:bill_jrt_comp('jrt')">select company</a>
					</span>
					<span id="jrtcomp_chgid"></span>
				</td>
			</tr>
			<tr>
				<input type="hidden" name="jrtcontact_sno" id="jrtcontact_sno" value="<?php echo $jrtcontact;?>">
				<td>Job Reports To</td>
				<td colspan='4'>
					<?php
					if($jrtcontact=='0')
					{
					?>
					<span id="jrtdisp">
						<a href="javascript:bill_jrt_cont('jrt')">select contact</a>
					</span>
					<span id="jrtchgid">
						<a href="javascript:bill_jrt_cont('jrt')"><i class="fa fa-search"></i></a>
						<span class="summaryform-formelement"> | </span>
						<a href="javascript:donew_add('jrt')">new contact</a>
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
							echo "<span class=summaryform-formelement>".str_replace('\\', '',$jrtcont)." - ".$jrtcontact."</span>";
						}
						else
						{
						?>
							<a class="crm-select-link" href="javascript:contact_func('<?php echo $jrtcontact;?>','<?php echo $jrtcont_stat;?>','jrt')"><?php echo str_replace('\\', '',$jrtcont)." - ".$jrtcontact;
							?></a>
						<?php
						}
						?>
					</span>
					<span id="jrtchgid">
					<span class=summaryform-formelement>(</span><a class=crm-select-link href=javascript:bill_jrt_cont('jrt')>change </a>
					<a href=javascript:bill_jrt_cont('jrt')><i class="fa fa-search"></i></a>
					<span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class=crm-select-link href=javascript:donew_add('jrt')>new</a>
					<span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class="crm-select-link" href="javascript:removeContact('jrt')">remove&nbsp;</a>
					<span class=summaryform-formelement>&nbsp;)&nbsp;</span>
					</span>
					<?
				}
				?>
				</td>
			</tr>
			<?php  if(DEFAULT_AKKUPAY == 'Y'){
			 ?>
			<tr>
				<td>WorkSite Code <?php echo $mandatory_akkupay; ?></td>
			 	<td>
					<select class="summaryform-formelement form-select w-250"  name="worksitecode" id="worksitecode" onChange="javascript:showwali()">
					<option value=""> -- Select -- </option>
					<?php  if(SS_ENABLED != 'Y'){				
					if($type=="Employee")
					{
						$get_empid_qry = "SELECT sno FROM emp_list WHERE username='".$emp_user[0]."'";
						$get_empid_res = mysql_query($get_empid_qry,$db);
						$get_empid_row = mysql_fetch_array($get_empid_res);

						$workSiteCode = getworkSiteCodes('',$get_empid_row[0]);
					}
					else
					{
						$workSiteCode = getworkSiteCodesforPlacement($location_sno);
					}
									echo $workSiteCode;
	}

					?>
					</select>
				</td>
				<?php if(SS_ENABLED == 'Y'){
					$chk_flg = '';

					if($daypay=='Yes'){
						$chk_flg = 'checked';
					}
					if($federal_payroll=='Yes'){
						$fedchk_flg = 'checked';
					}
					if($nonfederal_payroll=='Yes'){
						$nonfedchk_flg = 'checked';
					}
				?>
				<script type="text/javascript">
			        $(function () {
			        	$("#colprojcode").hide();
				        $("#colclassification").hide();
				        $('#certified').val('N');

			        	if($('#nonfederal_payroll').is(':checked')){
			        		$( "#federal_payroll" ).prop( "disabled", true );
			        		$("#colprojcode").show();
			        		$("#colclassification").show();
			        		$('#certified').val('Y');
			        	}else{
			        		$( "#federal_payroll" ).prop( "disabled", false );
			        		$('#certified').val('N');
			        		//$('#projcode').val('');
			        		//$('#joclassification').val('');
			        		$("#colprojcode").hide();
				        	$("#colclassification").hide();
			        	}

			        	$('#nonfederal_payroll').change(function() {
							if($('#nonfederal_payroll').is(':checked')){
				        		$( "#federal_payroll" ).prop( "disabled", true );
				        		$("#colprojcode").show();
				        		$("#colclassification").show();
				        		$('#certified').val('Y');
				        	}else{
				        		$( "#federal_payroll" ).prop( "disabled", false );
				        		$('#certified').val('N');
				        		$('#projcode').val('');
				        		$('#joclassification').val('');
				        		$("#colprojcode").hide();
				        		$("#colclassification").hide();
				        	}
						});

						$('#federal_payroll').change(function() {
							if($('#federal_payroll').is(':checked')){
				        		$( "#nonfederal_payroll" ).prop( "disabled", true );
				        		$("#colprojcode").show();
				        		$("#colclassification").show();
				        		$('#certified').val('Y');

				        	}else{
				        		$( "#nonfederal_payroll" ).prop( "disabled", false );
				        		$('#certified').val('N');
				        		$('#projcode').val('');
				        		$('#joclassification').val('');
				        		$("#colprojcode").hide();
				        		$("#colclassification").hide();
				        	}
						});
			        	
				    });    
				</script>
				<td style="white-space:nowrap" class="crmsummary-content-title">
					<font class=afontstyle >&nbsp;Daily Pay Assignment </font>
					<input type=checkbox name="daypay" id = "daypay" <?php echo $chk_flg;?> >
				</td>
				<td style="white-space:nowrap" class="crmsummary-content-title">
					<font class=afontstyle >&nbsp;Certified Payroll - Non Federal </font>
					<input type=checkbox name="nonfederal_payroll" id = "nonfederal_payroll" <?php echo $nonfedchk_flg;?> class="certifiednonfed">
				</td>

				<td style="white-space:nowrap" class="crmsummary-content-title">
					<font class=afontstyle >&nbsp;Certified Payroll - Federal </font>
					<input type=checkbox name="federal_payroll" id = "federal_payroll" <?php echo $fedchk_flg;?> class="certifiedfed" >
				</td>

			<tr id="colprojcode" <?php echo $certified_block;?>>
				<input type="hidden" name="certified" id="certified">
				<td class="crmsummary-content-title">Project Code<?php echo $mandatory_akkupay; ?></td>
				<td>
					<select class="form-select w-250" id="projcode" name="projcode" <?=$projcode?> <?=$certifiedread_only;?>>
						<option value="">--Select--</option>
						<?php  if(SS_ENABLED== 'Y'){				
							if($type=="Employee")
							{
								$cc_query	= "SELECT cp.sno,cp.project_code FROM certified_payroll cp 
								LEFT JOIN contact_manage cm ON (cp.location_code = cm.loccode) AND cm.status!='BP'
								LEFT JOIN emp_list e ON (cm.serial_no = e.emp_locid)
								WHERE e.username='".$emp_user[0]."' AND cp.inactive!='Y' ";
								$cc_result	= mysql_query($cc_query, $db);

								while ($cc_data = mysql_fetch_row($cc_result)) {
								$cc_text	= $cc_data[1];
								if (isset($cc_data[0]) && !empty($cc_data[0]))
								echo "<option value='$cc_data[0]' title='".html_tls_specialchars($cc_text, ENT_QUOTES)."'>".html_tls_specialchars($cc_text, ENT_QUOTES).'</option>';
								}
							}
							else
							{
								$cc_query	= "SELECT cp.sno,cp.project_code FROM certified_payroll cp 
								LEFT JOIN contact_manage cm ON (cp.location_code = cm.loccode) AND cm.status!='BP'
								WHERE cm.serial_no='".$location_sno."' AND cp.inactive!='Y' ";
								$cc_result	= mysql_query($cc_query, $db);

								while ($cc_data = mysql_fetch_row($cc_result)) {
								$cc_text	= $cc_data[1];
								if (isset($cc_data[0]) && !empty($cc_data[0]))
								echo "<option value='$cc_data[0]' title='".html_tls_specialchars($cc_text, ENT_QUOTES)."'>".html_tls_specialchars($cc_text, ENT_QUOTES).'</option>';
								}
							}
						}?>
					</select>
				</td>
			</tr>
			
			<tr id="colclassification" <?php echo $certified_block;?>>
				<input type="hidden" name="classification" id="classification" value="<?php echo $joclassification;?>">
				<td width="167" class="crmsummary-content-title">Work Classification<?php echo $mandatory_akkupay; ?></td>
				<td align=left>
					<select class="summaryform-formelement form-select w-250" name="joclassification" id="joclassification" <?=$certifiedread_only;?>>
					<option value="">--Select--</option>
					<?php echo $joclassification; ?>
					</select>
					
				</td>
			</tr>

				<!-- <tr>
				<td>Category</td>
				<td colspan="4" align=left>
					<select class="summaryform-formelement form-select w-250" name="jocat" id="jocat">
					<option value="">--select--</option>
					<?php echo $jocat;?>
					</select><?php if(EDITLIST_ACCESSFLAG){ ?> &nbsp;<a href="javascript:doManage('jocategory','jocat');" class="edit-list">edit list</a> <?php } ?>
				</td>
			</tr> -->


				<?php  }?>
			</tr>
			<?php if(AKKUPAY_WITH_SYMMETRY == 'Y'){ ?>
			<input type="hidden" name="waclassli" id="waclassli" value="<?php echo $licode;?>">
			<tr id="colwali">
				<td class="crmsummary-content-title">WA L&I Class Code<?php echo $mandatory_akkupay; ?></td>
				<td colspan='4'>
					<select class="form-select w-250" id="wali" name="wali">
					</select>
				</td>
			</tr>
			<?php } ?>
			<?php } ?>
		</table>
	
		<div class="accordion accordion-style-1 mt-3">
			<div class="accordion-item">
				<h2 class="accordion-header" id="Schedule">
					<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSchedule" aria-expanded="true" aria-controls="collapseSchedule">
						Schedule
					</button>
				</h2>
				<div id="collapseSchedule" class="accordion-collapse collapse" aria-labelledby="Schedule">
					<div class="accordion-body">
						<table class="table align-middle">
						<tr>
							<td colspan="3" class="summaryform-bold-title" style="padding-left:0px">
								<table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding-left:0px">
								<tr id = "sched-start-date">
									<td width="150" class="summaryform-bold-title">Start Date<?php echo $mandatory_synchr; ?></td>
									<td colspan=2>
									<select id="smonth" name="smonth" class="summaryform-formelement">
									<option value="0">Month</option>
									<option <?php echo compose_sel("1",$sdate[1]);?> value="1">January</option>
									<option <?php echo compose_sel("2",$sdate[1]);?> value="2">February</option>
									<option <?php echo compose_sel("3",$sdate[1]);?> value="3">March</option>
									<option <?php echo compose_sel("4",$sdate[1]);?> value="4">April</option>
									<option <?php echo compose_sel("5",$sdate[1]);?> value="5">May</option>
									<option <?php echo compose_sel("6",$sdate[1]);?> value="6">June</option>
									<option <?php echo compose_sel("7",$sdate[1]);?> value="7">July</option>
									<option <?php echo compose_sel("8",$sdate[1]);?> value="8">August</option>
									<option <?php echo compose_sel("9",$sdate[1]);?> value="9">September</option>
									<option <?php echo compose_sel("10",$sdate[1]);?> value="10">October</option>
									<option <?php echo compose_sel("11",$sdate[1]);?> value="11">November</option>
									<option <?php echo compose_sel("12",$sdate[1]);?> value="12">December</option>
									</select>
									<select id="sday" name="sday" class="summaryform-formelement">
									<option value="0">Day</option>
									<option <?php echo compose_sel("01",$sdate[2]);?> value="01">01</option>
									<option <?php echo compose_sel("02",$sdate[2]);?> value="02">02</option>
									<option <?php echo compose_sel("03",$sdate[2]);?> value="03">03</option>
									<option <?php echo compose_sel("04",$sdate[2]);?> value="04">04</option>
									<option <?php echo compose_sel("05",$sdate[2]);?> value="05">05</option>
									<option <?php echo compose_sel("06",$sdate[2]);?> value="06">06</option>
									<option <?php echo compose_sel("07",$sdate[2]);?> value="07">07</option>
									<option <?php echo compose_sel("08",$sdate[2]);?> value="08">08</option>
									<option <?php echo compose_sel("09",$sdate[2]);?> value="09">09</option>
									<option <?php echo compose_sel("10",$sdate[2]);?> value="10">10</option>
									<option <?php echo compose_sel("11",$sdate[2]);?> value="11">11</option>
									<option <?php echo compose_sel("12",$sdate[2]);?> value="12">12</option>
									<option <?php echo compose_sel("13",$sdate[2]);?> value="13">13</option>
									<option <?php echo compose_sel("14",$sdate[2]);?> value="14">14</option>
									<option <?php echo compose_sel("15",$sdate[2]);?> value="15">15</option>
									<option <?php echo compose_sel("16",$sdate[2]);?> value="16">16</option>
									<option <?php echo compose_sel("17",$sdate[2]);?> value="17">17</option>
									<option <?php echo compose_sel("18",$sdate[2]);?> value="18">18</option>
									<option <?php echo compose_sel("19",$sdate[2]);?> value="19">19</option>
									<option <?php echo compose_sel("20",$sdate[2]);?> value="20">20</option>
									<option <?php echo compose_sel("21",$sdate[2]);?> value="21">21</option>
									<option <?php echo compose_sel("22",$sdate[2]);?> value="22">22</option>
									<option <?php echo compose_sel("23",$sdate[2]);?> value="23">23</option>
									<option <?php echo compose_sel("24",$sdate[2]);?> value="24">24</option>
									<option <?php echo compose_sel("25",$sdate[2]);?> value="25">25</option>
									<option <?php echo compose_sel("26",$sdate[2]);?> value="26">26</option>
									<option <?php echo compose_sel("27",$sdate[2]);?> value="27">27</option>
									<option <?php echo compose_sel("28",$sdate[2]);?> value="28">28</option>
									<option <?php echo compose_sel("29",$sdate[2]);?> value="29">29</option>
									<option <?php echo compose_sel("30",$sdate[2]);?> value="30">30</option>
									<option <?php echo compose_sel("31",$sdate[2]);?> value="31">31</option>
									</select>
									<select id="syear" name="syear" class="summaryform-formelement">
									<OPTION VALUE="0">Year</option>
									<?php
									echo $startYear;
									?>
									</select>
									<span id="josdatecal"><input type="hidden" name="josdatenew" id="josdatenew" value="" /><script language='JavaScript'> new tcal ({'formname':'resume','controlname':'josdatenew'});</script></span>
									</td>
								</tr>

								<tr id="hide-expected-date" style=";<?php echo $hide_rate;?>">
									<td width="150" class="summaryform-bold-title">Expected End Date</td>
									<td colspan=2>
									<select id="vmonth" name="vmonth" class="summaryform-formelement">
									<option value="0">Month</option>
									<option <?php echo compose_sel("1",$hrdate[1]);?> value="1">January</option>
									<option <?php echo compose_sel("2",$hrdate[1]);?> value="2">February</option>
									<option <?php echo compose_sel("3",$hrdate[1]);?> value="3">March</option>
									<option <?php echo compose_sel("4",$hrdate[1]);?> value="4">April</option>
									<option <?php echo compose_sel("5",$hrdate[1]);?> value="5">May</option>
									<option <?php echo compose_sel("6",$hrdate[1]);?> value="6">June</option>
									<option <?php echo compose_sel("7",$hrdate[1]);?> value="7">July</option>
									<option <?php echo compose_sel("8",$hrdate[1]);?> value="8">August</option>
									<option <?php echo compose_sel("9",$hrdate[1]);?> value="9">September</option>
									<option <?php echo compose_sel("10",$hrdate[1]);?> value="10">October</option>
									<option <?php echo compose_sel("11",$hrdate[1]);?> value="11">November</option>
									<option <?php echo compose_sel("12",$hrdate[1]);?> value="12">December</option>
									</select>
									<select id="vday" name="vday" class="summaryform-formelement">
									<option value="0">Day</option>
									<option <?php echo compose_sel("01",$hrdate[2]);?> value="01">01</option>
									<option <?php echo compose_sel("02",$hrdate[2]);?> value="02">02</option>
									<option <?php echo compose_sel("03",$hrdate[2]);?> value="03">03</option>
									<option <?php echo compose_sel("04",$hrdate[2]);?> value="04">04</option>
									<option <?php echo compose_sel("05",$hrdate[2]);?> value="05">05</option>
									<option <?php echo compose_sel("06",$hrdate[2]);?> value="06">06</option>
									<option <?php echo compose_sel("07",$hrdate[2]);?> value="07">07</option>
									<option <?php echo compose_sel("08",$hrdate[2]);?> value="08">08</option>
									<option <?php echo compose_sel("09",$hrdate[2]);?> value="09">09</option>
									<option <?php echo compose_sel("10",$hrdate[2]);?> value="10">10</option>
									<option <?php echo compose_sel("11",$hrdate[2]);?> value="11">11</option>
									<option <?php echo compose_sel("12",$hrdate[2]);?> value="12">12</option>
									<option <?php echo compose_sel("13",$hrdate[2]);?> value="13">13</option>
									<option <?php echo compose_sel("14",$hrdate[2]);?> value="14">14</option>
									<option <?php echo compose_sel("15",$hrdate[2]);?> value="15">15</option>
									<option <?php echo compose_sel("16",$hrdate[2]);?> value="16">16</option>
									<option <?php echo compose_sel("17",$hrdate[2]);?> value="17">17</option>
									<option <?php echo compose_sel("18",$hrdate[2]);?> value="18">18</option>
									<option <?php echo compose_sel("19",$hrdate[2]);?> value="19">19</option>
									<option <?php echo compose_sel("20",$hrdate[2]);?> value="20">20</option>
									<option <?php echo compose_sel("21",$hrdate[2]);?> value="21">21</option>
									<option <?php echo compose_sel("22",$hrdate[2]);?> value="22">22</option>
									<option <?php echo compose_sel("23",$hrdate[2]);?> value="23">23</option>
									<option <?php echo compose_sel("24",$hrdate[2]);?> value="24">24</option>
									<option <?php echo compose_sel("25",$hrdate[2]);?> value="25">25</option>
									<option <?php echo compose_sel("26",$hrdate[2]);?> value="26">26</option>
									<option <?php echo compose_sel("27",$hrdate[2]);?> value="27">27</option>
									<option <?php echo compose_sel("28",$hrdate[2]);?> value="28">28</option>
									<option <?php echo compose_sel("29",$hrdate[2]);?> value="29">29</option>
									<option <?php echo compose_sel("30",$hrdate[2]);?> value="30">30</option>
									<option <?php echo compose_sel("31",$hrdate[2]);?> value="31">31</option>
									</select>
									<select id="vyear" name="vyear" class="summaryform-formelement">
									<OPTION VALUE="0">Year</option>
									<?php
									echo $expectedEndYear;
									?>
									</select>
									<span id="jovdatecal"><input type="hidden" name="jovdatenew" id="jovdatenew" value="" /><script language='JavaScript'> new tcal ({'formname':'resume','controlname':'jovdatenew'});</script></span>
									</td>
								</tr>
								</table>
							</td>
						</tr>

						<tr>
							<td colspan="3" class="summaryform-bold-title" style="padding-left:0px">
								<table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding-left:0px">
								<tr id="sched-end-date" style=" <?php echo $Dir_None;?>">
									<td width="150" class="summaryform-bold-title">End Date</td>
									<td>
										<select id="dmonth" name="dmonth" class="summaryform-formelement">
										<option value="0">Month</option>
										<option <?php echo compose_sel("1",$edate[1]);?> value="1">January</option>
										<option <?php echo compose_sel("2",$edate[1]);?> value="2">February</option>
										<option <?php echo compose_sel("3",$edate[1]);?> value="3">March</option>
										<option <?php echo compose_sel("4",$edate[1]);?> value="4">April</option>
										<option <?php echo compose_sel("5",$edate[1]);?> value="5">May</option>
										<option <?php echo compose_sel("6",$edate[1]);?> value="6">June</option>
										<option <?php echo compose_sel("7",$edate[1]);?> value="7">July</option>
										<option <?php echo compose_sel("8",$edate[1]);?> value="8">August</option>
										<option <?php echo compose_sel("9",$edate[1]);?> value="9">September</option>
										<option <?php echo compose_sel("10",$edate[1]);?> value="10">October</option>
										<option <?php echo compose_sel("11",$edate[1]);?> value="11">November</option>
										<option <?php echo compose_sel("12",$edate[1]);?> value="12">December</option>
										</select>
										<select id="dday" name="dday" class="summaryform-formelement">
										<option value="0">Day</option>
										<option <?php echo compose_sel("01",$edate[2]);?> value="01">01</option>
										<option <?php echo compose_sel("02",$edate[2]);?> value="02">02</option>
										<option <?php echo compose_sel("03",$edate[2]);?> value="03">03</option>
										<option <?php echo compose_sel("04",$edate[2]);?> value="04">04</option>
										<option <?php echo compose_sel("05",$edate[2]);?> value="05">05</option>
										<option <?php echo compose_sel("06",$edate[2]);?> value="06">06</option>
										<option <?php echo compose_sel("07",$edate[2]);?> value="07">07</option>
										<option <?php echo compose_sel("08",$edate[2]);?> value="08">08</option>
										<option <?php echo compose_sel("09",$edate[2]);?> value="09">09</option>
										<option <?php echo compose_sel("10",$edate[2]);?> value="10">10</option>
										<option <?php echo compose_sel("11",$edate[2]);?> value="11">11</option>
										<option <?php echo compose_sel("12",$edate[2]);?> value="12">12</option>
										<option <?php echo compose_sel("13",$edate[2]);?> value="13">13</option>
										<option <?php echo compose_sel("14",$edate[2]);?> value="14">14</option>
										<option <?php echo compose_sel("15",$edate[2]);?> value="15">15</option>
										<option <?php echo compose_sel("16",$edate[2]);?> value="16">16</option>
										<option <?php echo compose_sel("17",$edate[2]);?> value="17">17</option>
										<option <?php echo compose_sel("18",$edate[2]);?> value="18">18</option>
										<option <?php echo compose_sel("19",$edate[2]);?> value="19">19</option>
										<option <?php echo compose_sel("20",$edate[2]);?> value="20">20</option>
										<option <?php echo compose_sel("21",$edate[2]);?> value="21">21</option>
										<option <?php echo compose_sel("22",$edate[2]);?> value="22">22</option>
										<option <?php echo compose_sel("23",$edate[2]);?> value="23">23</option>
										<option <?php echo compose_sel("24",$edate[2]);?> value="24">24</option>
										<option <?php echo compose_sel("25",$edate[2]);?> value="25">25</option>
										<option <?php echo compose_sel("26",$edate[2]);?> value="26">26</option>
										<option <?php echo compose_sel("27",$edate[2]);?> value="27">27</option>
										<option <?php echo compose_sel("28",$edate[2]);?> value="28">28</option>
										<option <?php echo compose_sel("29",$edate[2]);?> value="29">29</option>
										<option <?php echo compose_sel("30",$edate[2]);?> value="30">30</option>
										<option <?php echo compose_sel("31",$edate[2]);?> value="31">31</option>
										</select>
										<select id="dyear" name="dyear" class="summaryform-formelement">
										<option value="0">Year</option>
										<?php
										echo $dueYear;
										?>
										</select>
										<span id="joddatecal"><input type="hidden" name="joddatenew" id="joddatenew" value="" /><script language='JavaScript'> new tcal ({'formname':'resume','controlname':'joddatenew'});</script></span>
									</td>
									<td><span class="summaryform-bold-title">Reason</span>&nbsp;<input class="summaryform-formelement" type=text id="reason" name=reason size=40 maxsize=150 maxlength=150 value=""></td>
								</tr>
								<tr id="hide-hire-sal" style=" <?php echo $hire_sal_style1;?>">
									<td  width="150" class="summaryform-bold-title" style="border-bottom: 0px solid #ddd; ">Hired Date</td>
									<td style="border-bottom: 0px solid #ddd;">
										<select id="vtmonth" name="vtmonth" class="summaryform-formelement">
										<option value="0">Month</option>
										<option <?php echo compose_sel("1",$date[0]);?> value="1">January</option>
										<option <?php echo compose_sel("2",$date[0]);?> value="2">February</option>
										<option <?php echo compose_sel("3",$date[0]);?> value="3">March</option>
										<option <?php echo compose_sel("4",$date[0]);?> value="4">April</option>
										<option <?php echo compose_sel("5",$date[0]);?> value="5">May</option>
										<option <?php echo compose_sel("6",$date[0]);?> value="6">June</option>
										<option <?php echo compose_sel("7",$date[0]);?> value="7">July</option>
										<option <?php echo compose_sel("8",$date[0]);?> value="8">August</option>
										<option <?php echo compose_sel("9",$date[0]);?> value="9">September</option>
										<option <?php echo compose_sel("10",$date[0]);?> value="10">October</option>
										<option <?php echo compose_sel("11",$date[0]);?> value="11">November</option>
										<option <?php echo compose_sel("12",$date[0]);?> value="12">December</option>
										</select>
										<select id="vtday" name="vtday" class="summaryform-formelement">
										<option value="0">Day</option>
										<option <?php echo compose_sel("01",$date[1]);?> value="01">01</option>
										<option <?php echo compose_sel("02",$date[1]);?> value="02">02</option>
										<option <?php echo compose_sel("03",$date[1]);?> value="03">03</option>
										<option <?php echo compose_sel("04",$date[1]);?> value="04">04</option>
										<option <?php echo compose_sel("05",$date[1]);?> value="05">05</option>
										<option <?php echo compose_sel("06",$date[1]);?> value="06">06</option>
										<option <?php echo compose_sel("07",$date[1]);?> value="07">07</option>
										<option <?php echo compose_sel("08",$date[1]);?> value="08">08</option>
										<option <?php echo compose_sel("09",$date[1]);?> value="09">09</option>
										<option <?php echo compose_sel("10",$date[1]);?> value="10">10</option>
										<option <?php echo compose_sel("11",$date[1]);?> value="11">11</option>
										<option <?php echo compose_sel("12",$date[1]);?> value="12">12</option>
										<option <?php echo compose_sel("13",$date[1]);?> value="13">13</option>
										<option <?php echo compose_sel("14",$date[1]);?> value="14">14</option>
										<option <?php echo compose_sel("15",$date[1]);?> value="15">15</option>
										<option <?php echo compose_sel("16",$date[1]);?> value="16">16</option>
										<option <?php echo compose_sel("17",$date[1]);?> value="17">17</option>
										<option <?php echo compose_sel("18",$date[1]);?> value="18">18</option>
										<option <?php echo compose_sel("19",$date[1]);?> value="19">19</option>
										<option <?php echo compose_sel("20",$date[1]);?> value="20">20</option>
										<option <?php echo compose_sel("21",$date[1]);?> value="21">21</option>
										<option <?php echo compose_sel("22",$date[1]);?> value="22">22</option>
										<option <?php echo compose_sel("23",$date[1]);?> value="23">23</option>
										<option <?php echo compose_sel("24",$date[1]);?> value="24">24</option>
										<option <?php echo compose_sel("25",$date[1]);?> value="25">25</option>
										<option <?php echo compose_sel("26",$date[1]);?> value="26">26</option>
										<option <?php echo compose_sel("27",$date[1]);?> value="27">27</option>
										<option <?php echo compose_sel("28",$date[1]);?> value="28">28</option>
										<option <?php echo compose_sel("29",$date[1]);?> value="29">29</option>
										<option <?php echo compose_sel("30",$date[1]);?> value="30">30</option>
										<option <?php echo compose_sel("31",$date[1]);?> value="31">31</option>
										</select>
										<select id="vtyear" name="vtyear" class="summaryform-formelement">
										<OPTION VALUE="0">Year</option>
										<?php
										echo $hiredYear;
										?></select>
										<span id="jovtdatecal"><input type="hidden" name="jovtdatenew" id="jovtdatenew" value="" /><script language='JavaScript'> new tcal ({'formname':'resume','controlname':'jovtdatenew'});</script></span>
									</td>			
								</tr>
								</table>
							</td>
						</tr>
						<?php 
							if(SHIFT_SCHEDULING_ENABLED=='N')
							{
							?>
							<tr id="shiftname_time" style="<?php echo $shiftname_time;?>" class="shiftnameCls">
								<td class="summaryform-bold-title">Shift Name/ Time</td>
								<td colspan="3">
									<select name="new_shift_name" id="new_shift_name" class="summaryform-formelement" style="width:128px !important;">
										<option value="0|0">Select Shift</option>
										<?php 
												$selShiftsQry = "SELECT sno,shiftname,shiftcolor FROM shift_setup WHERE shiftstatus='active' ORDER BY shiftname ASC";
												$selShiftsRes = mysql_query($selShiftsQry,$db);
												if(mysql_num_rows($selShiftsRes)>0)
												{
													while($selShiftsRow = mysql_fetch_array($selShiftsRes))
													{
														$selected = "";
														if($selShiftsRow['sno'] == $job_fetch[76])
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
									<select name="shift_start_time" id="shift_start_time" class="summaryform-formelement" onChange="" style="width:100px !important;">
									<option value="0">Start Time</option>
									<?php echo display_Shift_Times($job_fetch[77]);?>
									</select>
									<select name="shift_end_time" id="shift_end_time" class="summaryform-formelement" onChange="" style="width:100px !important;">
									<option value="0">End Time</option>
									<?php echo display_Shift_Times($job_fetch[78]); ?>
									</select>
								  </td>                      
							</tr>
							<tr id="shiftname_note" style="<?php echo $shiftname_note;?>">
								<td colspan="2">
									<span class="billInfoNoteStyle">Note : Shift details are auto populated in placement & assignment(s).</span>
								</td>
							</tr>
						<?php
							} ?>
						<tr id="sced-remove-hours" style = " <?php echo $Dir_None;?>">
							<td width="150" class="summaryform-bold-title">Hours</td>
							<td width="17%"><input type=hidden name="FullPartTimeRecId" id="FullPartTimeRecId" value="">
								<select name="Hrstype" id="Hrstype" class="summaryform-formelement">								<option  value="fulltime" <?php echo compose_sel("fulltime",$job_fetch[14]);?>>Full Time</option>
								<option  value="parttime" <?php echo compose_sel("parttime",$job_fetch[14]);?>>Part Time</option>
								</select>
							</td>
							<td class="summaryform-bold-title"><div class="form-check"><input type="checkbox" class="form-check-input" name="notimechk" id="notimechk" value="notime" ><label class="form-check-label">No Timesheet</label></div></td>

						</tr>

						<!-- OLD SHIFT SCHEDULING START -->	
						<tr id='crm-joborder-hourscustom' <?php if(SHIFT_SCHEDULING_ENABLED == 'Y' || $place_jobtype == 'Direct') { echo " style='display:none' "; } ?>>
							<td colspan="3">
								<table border="0" width=100% cellspacing="0" cellpadding="0" style="border-bottom: 0px solid #ddd;" id="crm-joborder-Tablehours">
								<tr>
									<td width="150">&nbsp;</td>
									<td width="9%">&nbsp;</td>
									<td width="10%">&nbsp;</td>
									<td width="9%">&nbsp;</td>
									<td width="2%">&nbsp;</td>
									<td width="9%" margin-left="5px" id="crm-joborder-custom_deleteall" ><span><a  href="#crm-joborder-scheduleDiv-Table" class="edit-list" onClick="javascript:DelselectSchAll()">delete selected</a></span></td>
									<td width="2%"><input type="checkbox" name="customcheckall" id="customcheckall" value="Y" class="summaryform-formelement" onClick="selectSchAll()"></td>
									<td colspan="2">&nbsp;</td>
								</tr>
								<tr style="display:none" id="defRowday0">
									<td></td>
									<td class="summaryform-bold-title">Sunday</td>
									<td class="summaryform-bold-title"><input type=hidden name="defweekday[0]" id="defweekday[0]" value="Sunday">&nbsp;</td>
									<td>
										<select name="fr_hour0" id="fr_hour0" class="summaryform-formelement">
										<?php echo $DispTimes;?>
										</select>
									</td>
									<td class="summaryform-bold-title">To</font></td>
									<td>
										<select name='to_hour0' id="to_hour0" class="summaryform-formelement">
										<?php echo $DispTimes;?>
										</select>
									</td>
									<td><input type="checkbox" name="daycheck0" id="daycheck0" value="Y" class="summaryform-formelement" onClick="childSchAll();"></td>
									<td colspan="2" class="summaryform-bold-title"></td>
								</tr>
								<tbody id="JoborderAddTable-Sunday" style="border-bottom: 0px solid #ddd;"></tbody>
								<tr style="display:none" id="defRowday1">
									<td></td>
									<td class="summaryform-bold-title">Monday</td>
									<td class="summaryform-bold-title"><input type=hidden name="defweekday[1]" id="defweekday[1]" value="Monday">&nbsp;</td>
									<td>
										<select name='fr_hour1' id='fr_hour1' class="summaryform-formelement">
										<?php echo $DispTimes;?>
										</select>
									</td>
									<td class="summaryform-bold-title">To</font></td>
									<td>
										<select name='to_hour1' id='to_hour1' class="summaryform-formelement">
										<?php echo $DispTimes;?>
										</select>
									</td>
									<td><input type="checkbox" name="daycheck1" id="daycheck1" class="summaryform-formelement" value="Y" onClick="childSchAll();"></td>
									<td colspan="2" class="summaryform-bold-title"></td>
								</tr>
								<tbody id="JoborderAddTable-Monday" style="border-bottom: 0px solid #ddd;"></tbody>
								<tr style="display:none" id="defRowday2">
									<td></td>
									<td class="summaryform-bold-title">Tuesday</td>
									<td class="summaryform-bold-title"><input type=hidden name="defweekday[2]" id="defweekday[2]" value="Tuesday">&nbsp;</td>
									<td>
										<select name='fr_hour2' id="fr_hour2" class="summaryform-formelement">
										<?php echo $DispTimes;?>
										</select>
									</td>
									<td class="summaryform-bold-title">To</font></td>
									<td>
										<select name='to_hour2' id='to_hour2' class="summaryform-formelement">
										<?php echo $DispTimes;?>
										</select>
									</td>
									<td><input type="checkbox" name="daycheck2" id="daycheck2" class="summaryform-formelement" value="Y" onClick="childSchAll();"></td>
									<td colspan="2" class="summaryform-bold-title"></td>
								</tr>
								<tbody id="JoborderAddTable-Tuesday" style="border-bottom: 0px solid #ddd;"></tbody>
								<tr style="display:none" id="defRowday3">
									<td></td>
									<td class="summaryform-bold-title">Wednesday</td>
									<td class="summaryform-bold-title"><input type=hidden name="defweekday[3]" id="defweekday[3]" value="Wednesday">&nbsp;</td>
									<td>
										<select name='fr_hour3' id="fr_hour3" class="summaryform-formelement">
										<?php echo $DispTimes;?>
										</select>
									</td>
									<td class="summaryform-bold-title">To</font></td>
									<td>
										<select name='to_hour3' id='to_hour3' class="summaryform-formelement">
										<?php echo $DispTimes;?>
										</select>
									</td>
									<td ><input type="checkbox" name="daycheck3" id="daycheck3" value="Y" class="summaryform-formelement" onClick="childSchAll();"></td>
									<td colspan="2" class="summaryform-bold-title" style="border-bottom: 0px solid #ddd;"></td>
								</tr>
								<tbody id="JoborderAddTable-Wednesday" style="border-bottom: 0px solid #ddd;"></tbody>
								<tr style="display:none" id="defRowday4">
									<td></td>
									<td class="summaryform-bold-title">Thursday</td>
									<td class="summaryform-bold-title"><input type=hidden name="defweekday[4]" id="defweekday[4]" value="Thursday">&nbsp;</td>
									<td>
										<select name='fr_hour4' id="fr_hour4" class="summaryform-formelement">
										<?php echo $DispTimes;?>
										</select>
									</td>
									<td class="summaryform-bold-title">To</font></td>
									<td>
										<select name='to_hour4' id='to_hour4' class="summaryform-formelement">
										<?php echo $DispTimes;?>
										</select>
									</td>
									<td><input type="checkbox" name="daycheck4" id="daycheck4" class="summaryform-formelement" value="Y" onClick="childSchAll();"></td>
									<td colspan="2" class="summaryform-bold-title"></td>
								</tr>
								<tbody id="JoborderAddTable-Thursday" style="border-bottom: 0px solid #ddd;"></tbody>
								<tr style="display:none" id="defRowday5">
									<td></td>
									<td class="summaryform-bold-title">Friday</td>
									<td class="summaryform-bold-title"><input type=hidden name="defweekday[5]" id="defweekday[5]" value="Friday">&nbsp;</td>
									<td>
										<select name='fr_hour5' id='fr_hour5' class="summaryform-formelement">
										<?php echo $DispTimes;?>
										</select>
									</td>
									<td class="summaryform-bold-title">To</font></td>
									<td>
										<select name='to_hour5' id='to_hour5' class="summaryform-formelement">
										<?php echo $DispTimes;?>
										</select>
									</td>
									<td><input type="checkbox" name="daycheck5" id="daycheck5" class="summaryform-formelement" value="Y" onClick="childSchAll();"></td>
									<td colspan="2" class="summaryform-bold-title"></td>
								</tr>
								<tbody id="JoborderAddTable-Friday" style="border-bottom: 0px solid #ddd;"></tbody>
								<tr style="display:none" id="defRowday6">
									<td></td>
									<td class="summaryform-bold-title">Saturday</td>
									<td class="summaryform-bold-title"><input type=hidden name="defweekday[6]" id="defweekday[6]" value="Saturday">&nbsp;</td>
									<td>
										<select name='fr_hour6' id="fr_hour6" class="summaryform-formelement">
										<?php echo $DispTimes;?>
										</select>
									</td>
									<td class="summaryform-bold-title">To</font></td>
									<td>
										<select name='to_hour6' id='to_hour6' class="summaryform-formelement">
										<?php echo $DispTimes;?>
										</select>
									</td>
									<td><input type="checkbox" name="daycheck6" id="daycheck6" value="Y" class="summaryform-formelement" onClick="childSchAll();"></td>
									<td colspan="2" class="summaryform-bold-title"></td>
								</tr>
								<tbody id="JoborderAddTable-Saturday" style="border-bottom: 0px solid #ddd;"></tbody>
								<tbody id="JoborderAddTable" align="left" style="border-bottom: 0px solid #ddd;"></tbody>
								<tr style="border-bottom: 0px solid #ddd;">
									<td class="crmsummary-jocompmin"></td>
									<td class="crmsummary-jocompmin">
										<select name="custaddrow_day" id="custaddrow_day" class="summaryform-formelement">
										<option value=''>--select--</option>
										<option value='Sunday'>Sunday</option>
										<option value='Monday'>Monday</option>
										<option value='Tuesday'>Tuesday</option>
										<option value='Wednesday'>Wednesday</option>
										<option value='Thursday'>Thursday</option>
										<option value='Friday'>Friday</option>
										<option value='Saturday'>Saturday</option>
										</select>
									</td>
									<td class="crmsummary-jocompmin" nowrap><input type="text" name='custaddrow_date' id='custaddrow_date' value="" class="summaryform-formelement" size="10" maxlength="10" readonly> <script language='JavaScript'> new tcal ({'formname':'resume','controlname':'custaddrow_date'});</script>
									</td>
									<td class="crmsummary-jocompmin">
										<select name='custaddrowfr_hour' id='custaddrowfr_hour' class="summaryform-formelement">
										<?php echo $DispTimes;?>
										</select>
									</td>
									<td class="summaryform-bold-title crmsummary-jocompmin">To</td>
									<td class="crmsummary-jocompmin">
										<select name='custaddrowto_hour' id='custaddrowto_hour' class="summaryform-formelement">
										<?php echo $DispTimes;?>
										</select>
									</td>
									<td colspan="2" class="crmsummary-jocompmin" nowrap="nowrap"><a class="crm-select-link crm-select-linkNewBg" href="#crm-joborder-scheduleDiv-plus" onClick="javascript:ScheduleRowCall()">Add Row</a></td>
									<td class="crmsummary-jocompmin summaryform-bold-title"></td>
								</tr>
								</table>
							</td>
						</tr>

						<!-- OLD SHIFT SCHEDULING END -->


						<!-- NEW SHIFT SCHEDULING START -->
						<tr id="sch_calendar"  <?php if(SHIFT_SCHEDULING_ENABLED == 'N' || $place_jobtype == 'Direct') { echo " style='display:none' "; } ?>>
							<td class="summaryform-bold-title" colspan="2">
								<?php 
									$getposid = $posid[0];
									echo $objSchSchedules->displayShiftScheduleWithAddLink('jo_shiftsch', 'No','placement',$shift_type);	
									if(isset($shiftsnos) && !empty($shiftsnos)){
										$smshiftLegendSnos = $shiftsnos;
									}else if(isset($shift_id) && !empty($shift_id) && $shift_id!="0"){
										$smshiftLegendSnos = $shift_id;	
									}else{
										if($shift_type == "regular"){
											$smshiftLegendSnos = $objSchSchedules->findShiftsAssoc($getposid,'placement','');
										}
										else
										{
											$smshiftLegendSnos = $objSchSchedules->findPerdiemShiftsAssoc($getposid,'placement','');
										}

									}	

								?>
							</td>
							<?php

							$objCandidateSchedule = new CandidateSchedule();													
							$flag = $objCandidateSchedule->isScheduleExists($addr,'placements');
							if($flag)
							{
								$urlAddress = "/include/shift_schedule/viewalltimeframes.php?refid=$getposid&status=viewall&module=placements&dsptitle=".trim($cand_fetch[1]);
								$windowName = "placements";
							?>
							<td class="summaryform-bold-title" colspan="1" align="right" style="text-align:right !important">
								<a style="text-decoration:none;cursor:pointer;" id="view_past_schedules">
									<span class="linkrow" onclick="javascript:window.open('<?php echo $urlAddress;?>','<?php echo $windowName; ?>','toolbar=no, scrollbars=No, resizable=No, top=200, left=200, width=850, height=600')">View All/History</span>
								</a>
							</td>
						<?php } ?>	
						</tr>


						<tr style = " <?php echo $Dir_None;?>">
							<td colspan="3" style="padding:0px !important">

								<input type="hidden" name="sm_module" id="sm_module" value="placement" />
								<input type="hidden" name="sm_shiftmode" id="sm_shiftmode" value="EditIndShifts" />
								<input type="hidden" name="sm_plac_shift_sno" id="sm_plac_shift_sno" value="<?php echo $smshiftLegendSnos;?>" >
								<input type="hidden" name="shift_schedule_module" id="shift_schedule_module" value="placements">

								<?php
								//GETTIGN SM AVAILABILITY TIMNEFRAME DETAILS
								$shift_schedule_module = "placements";
								$previousDate = date("Y-m-d",strtotime("-2 months",strtotime($job_fetch[75])));			
								$schDatesArrayStr = $objCandidateSchedule->getTimeFrameDetails($posid[0],$shift_schedule_module,$previousDate,'','',$smshiftLegendSnos); //return schedule shifts seperated by | symbol			
								$schDatesArray = array();
								unset($_SESSION['editShiftTotalArrayValues'.$candrn]);
								$_SESSION['editShiftTotalArrayValues'.$candrn] = array();
								if($schDatesArrayStr != "")
								{
									$schDatesArray = explode("|",$schDatesArrayStr);
								}

								if (count($schDatesArray) >0) {
									$smCalmodefrom = "EditIndShifts";
								}else{
									$smCalmodefrom = "AddIndShifts";
								}

								$getDisplayDatesCount = count($schDatesArray);
								if ($place_link == "bulk_place_cand") 
								{ 
									$displayTimeFrameGrid ='N';
								}else{
									$displayTimeFrameGrid = 'Y';
								}

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
								}
								//loadAllHiddenShiftdata('<?php echo $posid[0];?>','<?php echo $i;?>','N','<?php echo $candrn;?>','<?php echo $dateDisplayArrayCount;?>');
								</script>

								<?php if ($place_link == "bulk_place_cand") { ?>
									<script type="text/javascript">
									$('[id=schCalModalBoxEditLink]').hide();
									$('[id=schCalModalBoxDeleteLink]').hide();
									$("#shiftschAddEdit").hide();
									$("#jo_shiftsch").prop("checked",true);
									</script>
								<?php 
								} if ($place_link == "bulk_place_cand" && $shiftsnos =="") {
								?>
								<script type="text/javascript">
									$("#jo_shiftsch").prop("checked",false);
								</script>
								<?php } ?>
								<input type="hidden" name="sm_calmodefrom" id="sm_calmodefrom" value="<?php echo $smCalmodefrom;?>" >
							</td>
						</tr>
						<tr>
							<td class="summaryform-bold-title" colspan="3">
								<div id="perdiemShiftSchedule" style="display:none;">
									<?php 
										if ($shift_type == "perdiem" && isset($pos)) {
											//unset the session with the sequence number if anything exists
											if($perdiemPlacementFrm == ""){
												unset($_SESSION['editPlacementPerdiemShiftSch'.$candrn]);
												unset($_SESSION['editPlacementPerdiemShiftPagination'.$candrn]);
												$objPerdiemSchedulePosDetails->checkFilledPositionsOnPlacement($pos, $smshiftLegendSnos);
											}				
											?>
												<input type='hidden' name='placeOnOpenPos' id='placeOnOpenPos' value='Y' >
											<?php
											if($perdiemPlacementFrm =="perdiemParticalPlace")
											{	
												$nextPos = $perdiemnxtpos;				
												$paginationFrom = "placement";
												$pagiType = "edit";
												$doPagiFromId=1;
												$doPagiFor = "Next";
												$pagingFrom ="placementPage";
												$posid = $pos; // just for clarity assigning same variable
												// Preparing the Session for 0,34 total 35 days default to dispaly
												include_once($app_inc_path.'perdiem_shift_sch/View/paginationCRMPerdiemShifts.php');
											}else if ($objPerdiemSchedulePosDetails->checkFilledPositionsOnPlacement($pos, $smshiftLegendSnos)) {

												$nextPos = $objPerdiemSchedulePosDetails->getNextPositionOnPlacement($pos, $smshiftLegendSnos);				
												$paginationFrom = "placement";
												$pagiType = "edit";
												$doPagiFromId=1;
												$doPagiFor = "Next";
												$pagingFrom ="placementPage";
												if (isset($place_link) && ($place_link == "bulk_place_cand")) {
													$placementMode = "bulkPlacement";
													$candCountPerShiftAry = getShiftWiseCandCount($selCandIds);
													unset($_SESSION['bulkPlaceCandCountPerShift'.$candrn]);
													$_SESSION['bulkPlaceCandCountPerShift'.$candrn] = $candCountPerShiftAry;
													unset($_SESSION['bulkPlaceCandOpenPosExists'.$candrn]);
													$_SESSION['bulkPlaceCandOpenPosExists'.$candrn] = 0;								
												}else{
													$placementMode = "regularPlacement";
												}
												$posid = $pos; // just for clarity assigning same variable
												// Preparing the Session for 0,34 total 35 days default to dispaly
												include_once($app_inc_path.'perdiem_shift_sch/View/paginationCRMPerdiemShifts.php');
											}
											else
											{
												$openPosAval = $objPerdiemSchedulePosDetails->checkOpenPositionsJoPerdiemShiftSchSnos($pos, $smshiftLegendSnos,'');
												if($openPosAval){
													$nextPos = $objPerdiemSchedulePosDetails->getNextPositionOnPlacement($pos, $smshiftLegendSnos);				
													$paginationFrom = "placement";
													$pagiType = "edit";
													$doPagiFromId=1;
													$doPagiFor = "Next";
													$pagingFrom ="placementPage";
													if (isset($place_link) && ($place_link == "bulk_place_cand")) {
														$placementMode = "bulkPlacement";
														$candCountPerShiftAry = getShiftWiseCandCount($selCandIds);
														unset($_SESSION['bulkPlaceCandCountPerShift'.$candrn]);
														$_SESSION['bulkPlaceCandCountPerShift'.$candrn] = $candCountPerShiftAry;
														unset($_SESSION['bulkPlaceCandOpenPosExists'.$candrn]);
														$_SESSION['bulkPlaceCandOpenPosExists'.$candrn] = 0;							
													}else{
														$placementMode = "regularPlacement";
													}
													$posid = $pos; // just for clarity assigning same variable
													// Preparing the Session for 0,34 total 35 days default to dispaly
													include_once($app_inc_path.'perdiem_shift_sch/View/paginationCRMPerdiemShifts.php');

												}else{

													print '<script>alert("All Placement and Open Shift Positions are filled.\nDo you want to fill partial shift position.\nGo to, Job Order Summary >> View/Place(partial) Perdiem Shifts link to fill.");window.close();</script>';
												}


											}
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
								$('#shift_type').addClass("disabledDiv");
								$('#schCalModalBoxLink').addClass("disabledDiv");	
								$('#dateSelGridDiv').hide();
								$("#nextPos").val('<?php echo $nextPos; ?>');
							</script>
						<?php
							/*$openPosExists = $_SESSION['bulkPlaceCandOpenPosExists'.$candrn];
							if ((int)$openPosExists > 0) {
								echo '<script>alert("NOTE: Number of candidates selected does not match with number of available shifts for some dates.\nThese are grayed out in the schedule window and would not be processed.");</script>';
							}*/

						}
						if ($shift_type == "regular") {	?>
							<script>		
								$('#shift_type').addClass("disabledDiv");
								$('#schCalModalBoxLink').addClass("disabledDiv");
							</script>
						<?php } ?>
						<!-- NEW SHIFT SCHEDULING END-->
						</table>
					</div>
				</div>
			</div>
			<div class="accordion-item">
				<h2 class="accordion-header" id="BillingInformation">
					<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBillingInformation" aria-expanded="false" aria-controls="collapseBillingInformation">
						Billing Information
					</button>
				</h2>
				<div id="collapseBillingInformation" class="accordion-collapse collapse" aria-labelledby="BillingInformation">
					<div class="accordion-body">
						<span id=tab_billinginfo style="display:">
							<table class="crmsummary-jocomp-table">		
								<tr>
									<th colspan="2" id=rate_cond>
										Rates
									</td>
								</tr>
							</table>
							<!--span id=rate_avail style=";<?php echo $rate_display;?>"-->
							<span id="hide_rates" style=";<?php echo $hide_rate;?>">
								<table class="crmsummary-jocomp-table">		
									<?php if(RATE_CALCULATOR=='Y'){ ?>
											<tr>
												<td colspan="2" style="padding-bottom:6px;">
													<span class="billInfoNoteStyle">
														Note : Pay Rate or Bill Rate is calculated using Margin as the default. To calculate using Markup leave Margin blank.
													</span>
												</td>
											</tr>
											<?php } ?>
									<tr>
										<td width="13%" class="summaryform-bold-title" nowrap>Regular Pay Rate<?php echo $mandatory_synchr; ?></td>
										<td>
											<div class="row align-items-center mb-3">
												<?php if(RATE_CALCULATOR=='Y'){ ?>
												<div class="col-auto">
													<span id="payrate_calculator" style="float:left;cursor:pointer;display:none;"><i class="fa fa-calculator" onclick="javascript:payrateCalculatorFunc();" aria-hidden="true"></i>&nbsp;&nbsp;</span>
												</div>
												<?php } ?>
												<div class="col-auto">
													<input name="payratetype" id="payratetype" type="radio" onClick="javascript:calrate()" value="rate" <?php echo getChk('N',$job_fetch[41]);?>>
													<label class="form-check-label" for="payratetype">Rate</label>
													<?php
													if($job_fetch[18] == '0.00' || $job_fetch[18] == '0') 
														$job_fetch[18]='';
													?>
												</div>
												<div class="col-auto">
													<!--The hidden variable prevpayratevalue is used for passing the Pay Rate value to function  promptOnChangePayRate()-->
													<input type="hidden" name="prevpayratevalue" id="prevpayratevalue" value="<?php echo $job_fetch[18]?>">

													<input name="comm_payrate" type="text" id="comm_payrate" value="<?php echo $job_fetch[18];?>" size=10 maxlength="9" class="summaryform-formelement" onfocus="javascript:confirmPayRateAutoCalculatoin('comm_payrate', 'calculateComPayRates');" onkeyup="javascript:calculateComPayRates();" onkeypress="return blockNonNumbers(this, event, true, false);" onblur="clearConfirmPayRateAutoCalculatoin();" <?php if(RATE_CALCULATOR=='N'){ ?> onChange="promptOnChangePayRate();" <?php } ?> >
												</div>
												<div class="col-auto">
													<!--UOM: get dynamic rate types-->
													<select name="payrateper" id="payrateper" onclick="getPreviousRate(this);" onChange="change_Period('billrate');" class="form-select">
														<?php $timeOptions = getNewRateTypes($job_fetch[19]);
														echo $timeOptions;?>
													</select>
												</div>
												<div class="col-auto">
													<!--UOM: get dynamic rate types-->
													<select name="payratecur" id="payratecur"   onChange="change_PeriodNew('billrate');"  class="form-select">
													<?php
													displayCurrency($job_fetch[20]);
													?>
													</select>
												</div>
												<div class="col-auto" id='reg_pay_Bill_NonBill'>
													<div class="form-check form-check-inline">
														<input name="payrateBillOpt" id="payrateBillOpt"  type="radio" value="Y" <?php if($ratesDefaultVal['Regular'][0]!="") echo getChk($ratesDefaultVal['Regular'][0],"Y"); else echo "checked";?> class="form-check-input">
														<label class="form-check-label">Billable</label>
													</div>
													<div class="form-check form-check-inline">
														<input name="payrateBillOpt" id="payrateBillOpt" type="radio" value="N" <?php echo getChk($ratesDefaultVal['Regular'][0],"N");?> class="form-check-input">
														<label class="form-check-label">Non-Billable</label>
													</div>
												</div>
											</div>
											<div style="display: none">
												<div class="row mb-3 align-items-center">
													<div class="col">
														<div class="form-check">
															<input type="checkbox" name="emppay_rates" onClick="javascript:onTimeCheck('useemp');" class="form-check-input"></input>
															<label class="form-check-label">Use employee HR rates</label>
														</div>
													</div>
												</div>
											</div>
											<div class="row mb-3 align-items-center">
												<div class="col">
													<?php if($job_fetch[43] == '0.00' || $job_fetch[43] == '0') $job_fetch[43]='';?>
													<div class="form-check form-check-inline">
														<input name="payratetype" id="payratetype" type="radio" value="open" onClick="javascript:calrate()" <?php echo getChk('Y',$job_fetch[41]);?> class="form-check-input">
														<label class="form-check-label">Open</label>
													</div>
													<input name="comm_open_payrate" type="text" id="comm_open_payrate" value="<?php echo $job_fetch[43];?>" size=38 class="form-control">
												</div>
											</div>
										</td>
									</tr>
									<tr id="burden-rate" >
										<td class="summaryform-bold-title">Pay Burden&nbsp;<?php echo ($payburden_status=='Y')? "<span style='color:red'>*</span>": "" ?></td>
										<td class="summarytext">
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
											<input type="hidden" name="hdnbi_details" id="hdnbi_details" value="<?php echo $edit_existing_bi_str; ?>" />
											<input type="hidden" name="hdnbt_details" id="hdnbt_details" value="<?php echo $edit_bt_detail_str; ?>" />
											<input type="hidden" name="edithdn_bt_str" id="edithdn_bt_str" value="<?php echo $edit_bt_detail_str; ?>" />
											<input type="hidden" name="edithdn_bi_str" id="edithdn_bi_str" value="<?php echo $edit_bi_detail_str; ?>" />
											<input type="hidden" name="hdnTotalBurdenPerc" id="hdnTotalBurdenPerc" value="0" />
											<input type="hidden" name="hdnTotalBurdenFlat" id="hdnTotalBurdenFlat" value="0" />
											<input name="comm_burden" type="hidden" id="comm_burden" value="<?php echo $job_fetch[28];
											?>" />
											<div class="BTContainer align-items-center">
												<div>                        
													<select name="burdenType" id="burdenType" <?php if(RATE_CALCULATOR=='Y'){?> onchange="doNoCalculateMarkup(this);BTChangeAction(this,'placement');<?php echo $Addfunction;?>" <?php } else{ ?> onchange="BTChangeAction(this,'placement');<?php echo $Addfunction;?>" <?php } ?>>
														<?php
														   echo $existingBurdenOpt;
													   if($payburden_status == 'Y')
													   {
														?>
														 <option value="">--Select Pay Burden--</option>
														<?php
														   }
														foreach ($arr_burden_type as $sno => $burden) {
															if ($burden['rate_type'] == 'payrate') {
															?>
															<option value="<?php if ($bt_sno==$sno){ echo "existing";} else{ echo $sno;} ?>" <?php if($bt_sno==$sno) echo "selected";  ?>><?php echo $burden['burden_type']; ?></option>
															<?php
															}
														}
														?>
													</select>
												</div>
												<div style="vertical-align:middle">
													<b><span id="burdenItemsStr" class="summaryform-formelement">&nbsp;</span></b>
												</div>				
											</div>
											<?php 
												} else {
													$chk_bt = false;
												   ?>
												<div class="BTContainer align-items-center">
													<div>
														<input type="text" name="comm_burden" id="comm_burden" value="<?php echo $job_fetch[28];?>" <?php if(RATE_CALCULATOR=='Y'){?> onkeyup="doNoCalculateMarkup(this);calculatebtmargin();" <?php } else{ ?> onkeyup="calculatebtmargin();" <?php } ?> maxlength="9" size="10" onkeypress="return blockNonNumbers(this, event, true, false);"/>
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
										<td width="13%" class="summaryform-bold-title" nowrap>Regular Bill Rate</td>
										<td>
											<div class="row mb-3 align-items-center">
												<?php if(RATE_CALCULATOR=='Y'){ ?>
												<div class="col-auto">
													<span id="billrate_calculator" style="float:left;cursor:pointer;display:none;"><i class="fa fa-calculator" onclick="javascript:billrateCalculatorFunc();" aria-hidden="true"></i>&nbsp;&nbsp;</span>
												</div>
												<?php } ?>

												<?php if($job_fetch[15] == '0.00' || $job_fetch[15] == '0') $job_fetch[15]='';?>
												<div class="col-auto">
													<input type="radio" name="billratetype" id="billratetype" value="rate" onClick="javascript:calrate()" <?php echo getChk('N',$job_fetch[40]);?> class="summaryform-formelement">
													<span class="summaryform-formelement">Rate</span>
												</div>
												<div class="col-auto">
													<!--The hidden variable prevbillratevalue is used for passing the Bill Rate value to function  promptOnChangeBillRate()-->
													<input type="hidden" name="prevbillratevalue" id="prevbillratevalue" value="<?php echo $job_fetch[15]?>">
													<input name="comm_billrate" type="text" id="comm_billrate" value="<?php echo $job_fetch[15]?>" size=10 maxlength="9" class="summaryform-formelement" onfocus="javascript:confirmBillRateAutoCalculatoin('comm_billrate');" onkeyup="javascript:calculateComBillRates();" onkeypress="return blockNonNumbers(this, event, true, false);" onblur="clearConfirmBillRateAutoCalculatoin();"  <?php if(RATE_CALCULATOR=='N'){ ?> onChange="promptOnChangeBillRate();" <?php } ?>>
												</div>
												<div class="col-auto">
													<!--UOM: get dynamic rate types-->
													<select name="billrateper" id="billrateper"  onclick="getPreviousRate(this);"  onChange="change_Period('payrate');" class="summaryform-formelement">
													<?php $timeOptions = getNewRateTypes($job_fetch[16]);
														echo $timeOptions; ?>
													</select>	
												</div>	
												<div class="col-auto">
													<!--UOM: get dynamic rate types-->
													<select name="billratecur" id="billratecur" onChange="change_PeriodNew('payrate');" class="summaryform-formelement">
													<?php
													displayCurrency($job_fetch[17]);
													?>
													</select>
												</div>
												<div class="col-auto">
													<div id='reg_bill_Tax_NonTax'>
														<div class="form-check form-check-inline">
															<input class="form-check-input" name="billrateTaxOpt" id="billrateTaxOpt"  type="radio" value="Y" <?php if($ratesDefaultVal['Regular'][1]!="") echo getChk($ratesDefaultVal['Regular'][1],"Y"); else echo "checked";?>>
															<label class="form-check-label">Taxable</label>
														</div>
														<div class="form-check form-check-inline">
															<input class="form-check-input" name="billrateTaxOpt" id="billrateTaxOpt"  type="radio" value="N" <?php echo getChk($ratesDefaultVal['Regular'][1],"N");?>>
															<label class="form-check-label">Non-Taxable</label>
														</div>
													</div>
												</div>
											</div>
											<div class="row align-items-center">
												<div class="col-auto">
													<?php if($job_fetch[42] == '0.00' || $job_fetch[42] == '0') $job_fetch[42]='';?>
													<div class="form-check">
														<input name="billratetype" id="billratetype" type="radio" onClick="javascript:calrate()" value="open" <?php echo getChk('Y',$job_fetch[40]);?> class="form-check-input">
														<label class="form-check-label">Open</label>
													</div>
												</div>
												<div class="col-auto">
													<input name="comm_open_billrate" type="text" id="comm_open_billrate" value="<?php echo $job_fetch[42];?>" size=38 class="form-control">
												</div>
											</div>
										</td>
									</tr>
									<tr id="burden-rate" >
										<td class="summaryform-bold-title">Bill Burden&nbsp;<?php echo ($billburden_status=='Y')? "<span style='color:red'>*</span>": "" ?></td>
										<input type="hidden" name='billburdenstatus' id="billburdenstatus" value="<?php echo $billburden_status;?>"/>
										<td class="summarytext">
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
											<div class="BTContainer align-items-center">
												<div>                        
													<select name="bill_burdenType" id="bill_burdenType" <?php if(RATE_CALCULATOR=='Y'){?> onchange="doNoCalculateMarkup(this);BillBTChangeAction(this,'placement');" <?php } else{ ?> onchange="BillBTChangeAction(this,'placement');" <?php } ?>>
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
															<option value="<?php if ($bt_billsno == $sno){ echo "existing"; } else { echo $sno; } ?>" <?php if($bt_billsno == $sno) echo "selected"; ?>><?php echo $burden["burden_type"]; ?></option>
														<?php
															}
														}
														?>
													</select>
												</div>
												<div style="vertical-align:middle">
													<b><span id="bill_burdenItemsStr" class="summaryform-formelement">&nbsp;</span></b>
												</div>
											</div>
											<?php 
											} else {
											?>
												<div class="BTContainer align-items-center">
													<div>
														<input type="text" name="comm_bill_burden" id="comm_bill_burden" value="<?php echo $job_fetch[74];?>" <?php if(RATE_CALCULATOR=='Y'){?> onkeyup="doNoCalculateMarkup(this);calculatebillbtmargin();" <?php } else{ ?> onkeyup="calculatebillbtmargin();" <?php } ?> maxlength="9" size="10" onkeypress="return blockNonNumbers(this, event, true, false);"/>
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
										<td width="13%" class="summaryform-bold-title">Margin&nbsp;</td>
										<?php
										if ($job_fetch[29] == '0.00' || $job_fetch[29] == '0')
											$job_fetch[29] = '';

										if ($job_fetch[44] == '0.00' || $job_fetch[44] == '0') $job_fetch[44] = ''; 

												$margin_akken = $job_fetch[29];
												$payrate_akken =$job_fetch[18];
												$billrate_akken = $job_fetch[15];
												$payburden_akken= $job_fetch[28];
												$markup_akken = $job_fetch[44];
												$billburden_akken = $job_fetch[74];
												$calculatedValues = calculateMarginMarkupIfNotExists($payrate_akken,$billrate_akken,$payburden_akken,$billburden_akken,$margin_akken,$markup_akken);
												$calculatedValuesArray = array();
												$calculatedValuesArray = explode('|',$calculatedValues);
												$job_fetch[29] = $calculatedValuesArray[4];
												$job_fetch[44] = $calculatedValuesArray[5];
												if ($job_fetch[18] != "" && $job_fetch[15] != "" && $job_fetch[28] != ""){
													$grossbillburden = ($billburden_akken / 100) * $billrate_akken;
													$margincost=(((($billrate_akken-$grossbillburden) * 100) / 100) - ((($payrate_akken * 100) / 100) + (((($payburden_akken * 100) / 100) / 100) * (($payrate_akken * 100) / 100))));
												}else {
												$margincost = "0.00";
												}

												if ($margincost == '0')
												$margincost = "0.00";

												if(!isset($job_fetch[29]))
												{
													$comm_margin_span = "0.00";
												}
												if(!isset($job_fetch[44]))
												{
													 $comm_markup_span = "0.00";
												}

										?>
										<td>
											 <span id="margin_calculator" style="cursor:pointer;display:none;"><i class="fa fa-calculator" onclick="marginCalculatorFunc(this);" aria-hidden="true"></i>&nbsp;&nbsp;</span>
											<input type=hidden size="10" maxlength="9" onkeypress="return blockNonNumMarginMarkup(this, event);" name=comm_margin id=comm_margin value="<?php echo $job_fetch[29]; ?>" class="summaryform-formelement"><span style="display:none;" class="summaryform-formelement" id="comm_margin_span"><?php echo $comm_margin_span;?></span>&nbsp;<span class="summaryform-formelement"><b>%</b></span>&nbsp;<span style="display:none;" class="summaryform-formelement">|</span>&nbsp;<span style="display:none;" class="summaryform-formelement"><b><span style="display:none;" id="margincost"><?php echo "$" . $margincost; ?></span></b></span>
										<?php
											$qry = "select netmargin from margin_setup where sno=1";
											$qry_res = mysql_query($qry, $db);
											$qry_row = mysql_fetch_row($qry_res);
										?>
										&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="summaryform-formelement">(Company&nbsp;Min&nbsp;Margin:&nbsp;<?=$qry_row[0];?>%)</span>
										</td>
									</tr>               
														<tr id="markup-rate" >
										<td class="summaryform-bold-title">Markup&nbsp;</td>
										<td>
											<span id="markup_calculator" style="cursor:pointer;display:none;"><i class="fa fa-calculator" onclick="markupCalculatorFunc(this);"aria-hidden="true"></i>&nbsp;&nbsp;</span>
											<input name="comm_markup" type="hidden" id="comm_markup" value="<?php echo $job_fetch[44]; ?>" size="10" maxlength="9" onkeypress="return blockNonNumMarginMarkup(this, event);" class="summaryform-formelement"><span style="display:none;" class="summaryform-formelement" id="comm_markup_span"><?php echo $comm_markup_span;?></span>&nbsp;<span class="summaryform-formelement" id="comm_markup_span_perc"><b>%</b></span></td>
									</tr>
													 <?php } else { ?>
														<tr id="marg-rate" >
										<td width="13%" class="summaryform-bold-title">Margin&nbsp;</td>
										<?php
										if ($job_fetch[29] == '0.00' || $job_fetch[29] == '0')
											$job_fetch[29] = '';
										if ($job_fetch[18] != "" && $job_fetch[15] != "" && $job_fetch[28] != "")
											$margincost = ($job_fetch[15] - ($job_fetch[18] + (($job_fetch[28] / 100) * $job_fetch[18])));
										else
											$margincost = "0.00";
										if ($margincost == '0')
											$margincost = "0.00";
										?>
										<td>
											<input type=hidden maxlength=10 size=10 name=comm_margin id=comm_margin value="<?php echo $job_fetch[29]; ?>" class="summaryform-formelement"><span class="summaryform-formelement" id="comm_margin_span">0.00</span>&nbsp;<span class="summaryform-formelement"><b>%</b></span>&nbsp;<span class="summaryform-formelement">|</span>&nbsp;<span class="summaryform-formelement"><b><span id="margincost"><?php echo "$" . $margincost; ?></span></b></span>
										<?php
											$qry = "select netmargin from margin_setup where sno=1";
											$qry_res = mysql_query($qry, $db);
											$qry_row = mysql_fetch_row($qry_res);
										?>
										&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="summaryform-formelement">(Company&nbsp;Min&nbsp;Margin:&nbsp;<?=$qry_row[0];?>%)</span>
										</td>
									</tr>               
									<tr id="markup-rate" >
										<td class="summaryform-bold-title">Markup&nbsp;</td>
										<?php if ($job_fetch[44] == '0.00' || $job_fetch[44] == '0') $job_fetch[44] = ''; ?>
										<td><input name="comm_markup" type="hidden" id="comm_markup" value="<?php echo $job_fetch[44]; ?>" size=10 class="summaryform-formelement"><span class="summaryform-formelement" id="comm_markup_span">0.00</span>&nbsp;<span class="summaryform-formelement" id="comm_markup_span_perc"><b>%</b></span></td>
									</tr>
													 <?php } ?>
									<tr>
										<td colspan="2" >
											<span class="billInfoNoteStyle">Note : Rates are auto populated based on Regular (Pay/Bill) Rates. You can edit to over ride.</span>
										</td>
									</tr>
								</table>
							</span>
							<!-- rate tab end-->
						</span>

						<table class="table crmsummary-jocomp-table">
						<tr id="Billing-info-assignname" style=" <?php echo $Dir_Ass_None;?>">
							<td width="150" class="summaryform-bold-title">Assignment Name</td>
							<td><span id="leftflt"><input class="summaryform-formelement" type=text name="assname" size=40 maxsize=150 maxlength=150 value=""></span></td>
						</tr>

						<?php if($job_fetch[24] == '0.00' || $job_fetch[24] == '0') $job_fetch[24]='';?>

						<tr id="hide-hire-sal2" style=" <?php echo $hire_sal_style2;?> ">
								<td width="13%" class="summaryform-bold-title">
									<span>&nbsp;Salary</span>&nbsp;&nbsp;
								</td>
								<td>
									<span id="leftflt">
										<input class="summaryform-formelement" type=text id=amount_val name=salary size=10 maxsize=8 maxlength="9" value="<?php echo $job_fetch[24];?>" onkeypress="return blockNonNumbers(this, event, true, false);">&nbsp;
							<select name="perbill"  class="summaryform-formelement">
							<!--UOM: get dynamic rate types-->

							<?php $timeOptions = getNewRateTypes($job_fetch[25]);
								echo $timeOptions;?>
							</select>
										&nbsp;<select name="paybill" class="summaryform-formelement">
										<?php
										displayCurrency($job_fetch[26]);
										?>
										</select>
									</span>
								</td>
						</tr>
						<tr id="overtime_rate_pay" style=" <?php echo $Dis_Over_Pay;?> ">
								<?php
								$arr = $objMRT->getRateTypeById(2);
								if($arr['peditable'] == 'N')
								{
									$pdisabled_user_input_field = ' disabled_user_input_field';
									$pdisable = ' disabled="disabled"';
								}
								else
								{
									$pdisabled_user_input_field = '';
									$pdisable = '';
								}
								if($arr['beditable'] == 'N')
								{
									$bdisabled_user_input_field = ' disabled_user_input_field';
									$bdisable = ' disabled="disabled"';
								}
								else
								{
									$bdisabled_user_input_field = '';
									$bdisable = '';
								}
								?>
								<td width="13%" class="summaryform-bold-title" nowrap="nowrap"><?php echo $arr['name'];?> Pay Rate</td>
								<td >
									<span id="leftflt"><input name="otrate_pay" type="text" id="otrate_pay" value="<?php echo $job_fetch[45];?>" size=10 maxlength="9" class="summaryform-formelement<?php echo $pdisabled_user_input_field;?>" <?php echo $pdisable;?>><input type="hidden" id="otrate_pay_hidden" value="<?php echo $arr['pvalue'];?>"></span>
									<span id="leftflt">&nbsp;&nbsp;
									<select name="otper_pay" id="otper_pay"   onclick="getPreviousRate(this);"  onChange="change_Period('overtimepayrate');" class="summaryform-formelement<?php echo $pdisabled_user_input_field;?>" <?php echo $pdisable; ?>>
									<!--UOM: get dynamic rate types-->

							<?php $timeOptions = getNewRateTypes($job_fetch[46]);
							 echo $timeOptions;?>
									</select>	
									</span>	
									<span id="leftflt">&nbsp;&nbsp;
									<select name="otcurrency_pay"  onChange="change_PeriodNew('overtimepayratecur');" id="otcurrency_pay" class="summaryform-formelement<?php echo $pdisabled_user_input_field;?>" <?php echo $pdisable;?>>
									<?php
									displayCurrency($job_fetch[47]);
									?>
									</select>
									</span>
									<span id='ot_pay_Bill_NonBill' class="fltcenter">
										<input name="OvpayrateBillOpt" id="OvpayrateBillOpt"  type="radio" value="Y" <?php if($ratesDefaultVal['OverTime'][0] == 'Y' || $ratesDefaultVal['OverTime'][0] == ''){echo ' checked="checked" class="BillableRates"';}else{ echo $pdisable; echo 'class="'.trim($pdisabled_user_input_field).'"';}?>><font class="afontstyle">Billable</font>
										<input name="OvpayrateBillOpt" id="OvpayrateBillOpt"  type="radio" value="N" <?php if($ratesDefaultVal['OverTime'][0] == 'N'){echo ' checked="checked" class="BillableRates"';}else{ echo $pdisable; echo 'class="'.trim($pdisabled_user_input_field).' BillableRates"';}?>><font class="afontstyle">Non-Billable</font>
									</span>
								</td>
							</tr>
						<tr id="overtime_rate_bill" style=" <?php echo $Dis_Over_Bill;?> ">
								<td width="13%" class="summaryform-bold-title" nowrap="nowrap"><?php echo $arr['name'];?> Bill Rate</td>
								<td>
										<span id="leftflt"><input name="otrate_bill" type="text" id="otrate_bill" value="<?php echo $job_fetch[48];?>" size=10 maxlength="9" class="summaryform-formelement<?php echo $bdisabled_user_input_field;?>" <?php echo $bdisable?>><input type="hidden" id="otrate_bill_hidden" value="<?php echo $arr['bvalue'];?> "></span>
										<span id="leftflt">&nbsp;&nbsp;
										<select name="otper_bill" id="otper_bill" onclick="getPreviousRate(this);"  onChange="change_Period('overtimebillrate');"   class="summaryform-formelement<?php echo $bdisabled_user_input_field;?>" <?php echo $bdisable;?>>
										 <!--UOM: get dynamic rate types-->


								<?php $timeOptions = getNewRateTypes($job_fetch[49]);
											 echo $timeOptions; ?>
								</select>

										</span>		
										<span id="leftflt">&nbsp;&nbsp;
										<select name="otcurrency_bill"  onChange="change_PeriodNew('overtimebillratecur');" id="otcurrency_bill" class="summaryform-formelement<?php echo $bdisabled_user_input_field;?>" <?php echo $bdisable;?>>
										<?php
										displayCurrency($job_fetch[50]);
										?>
										</select></span>
										<span id='ot_bill_Tax_NonTax' class="fltcenter">
											<input name="OvbillrateTaxOpt" id="OvbillrateTaxOpt"  type="radio" value="Y" <?php if($ratesDefaultVal['OverTime'][1] == 'Y' || $ratesDefaultVal['OverTime'][1] == ''){echo ' checked="checked"';}else{ echo $bdisable; echo 'class="'.trim($bdisabled_user_input_field).'"';}?>><font class="afontstyle">Taxable</font>
											<input name="OvbillrateTaxOpt" id="OvbillrateTaxOpt"  type="radio" value="N" <?php if($ratesDefaultVal['OverTime'][1] == 'N'){echo ' checked="checked"';}else{ echo $bdisable; echo 'class="'.trim($bdisabled_user_input_field).'"';}?>><font class="afontstyle">Non-Taxable</font>
										</span>
								</td>
						</tr>

						<?php if($job_fetch[30] == '0.00' || $job_fetch[30] == '0')$job_fetch[30]='';?>

						<tr id="db_time_payrate" style=" <?php echo $Dis_db_Pay;?> ">
							<?php
							$arr = $objMRT->getRateTypeById(3);
									if($arr['peditable'] == 'N')
									{
										$pdisabled_user_input_field = ' disabled_user_input_field';
										$pdisable = ' disabled="disabled"';
									}
									else
									{
										$pdisabled_user_input_field = '';
										$pdisable = '';
									}
									if($arr['beditable'] == 'N')
									{
										$bdisabled_user_input_field = ' disabled_user_input_field';
										$bdisable = ' disabled="disabled"';
									}
									else
									{
										$bdisabled_user_input_field = '';
										$bdisable = '';
									}
							?>
							<td width="13%" class="summaryform-bold-title" nowrap><?php echo $arr['name'];?> Pay Rate</td>
							<td >
								<span id="leftflt"><input name="db_time_pay" type="text" id="db_time_pay" value="<?php echo $job_fetch[53];?>" size=10 maxlength="9" class="summaryform-formelement<?php echo $pdisabled_user_input_field;?>" <?php echo $pdisable;?>><input type="hidden" id="db_time_pay_hidden" value="<?php echo $arr['pvalue'];?>"></span>
								<span id="leftflt">&nbsp;&nbsp;
											<select name="db_time_payper" id="db_time_payper" onclick="getPreviousRate(this);"  onChange="change_Period('dbtimepayrate');" class="summaryform-formelement<?php echo $pdisabled_user_input_field;?>" <?php echo $pdisable;?>>
								<!--UOM: get dynamic rate types-->


								<?php $timeOptions = getNewRateTypes($job_fetch[54]);
								echo $timeOptions;?>
								</select>	
								</span>	
								<span id="leftflt">&nbsp;&nbsp;
											<select name="db_time_paycur" id="db_time_paycur" onChange="change_PeriodNew('dbtimepayratecur');" class="summaryform-formelement<?php echo $pdisabled_user_input_field;?>" <?php echo $pdisable;?>>
								<?php
								displayCurrency($job_fetch[55]);
								?>
								</select></span>
								<span id='dt_pay_Bill_NonBill' class="fltcenter">
									<input name="DbpayrateBillOpt" id="DbpayrateBillOpt"  type="radio" value="Y" <?php if($ratesDefaultVal['DoubleTime'][0] == 'Y' || $ratesDefaultVal['DoubleTime'][0] == ''){echo ' checked="checked" class="BillableRates"';}else{ echo $pdisable; echo 'class="'.trim($pdisabled_user_input_field).'"';}?>><font class="afontstyle">Billable</font>
									<input name="DbpayrateBillOpt" id="DbpayrateBillOpt"  type="radio" value="N" <?php if($ratesDefaultVal['DoubleTime'][0] == 'N'){echo ' checked="checked" class="BillableRates"';}else{ echo $pdisable; echo 'class="'.trim($pdisabled_user_input_field).'"';}?>><font class="afontstyle">Non-Billable</font>
								</span>
							</td>
						</tr>
						<tr id="db_time_billrate" style=" <?php echo $Dis_db_Bill;?> ">
								<td width="13%" class="summaryform-bold-title" nowrap><?php echo $arr['name'];?>Bill Rate</td>
								<td>
									<span id="leftflt"><input name="db_time_bill" type="text" id="db_time_bill" value="<?php echo $job_fetch[56];?>" size=10 maxlength="9" class="summaryform-formelement<?php echo $bdisabled_user_input_field;?>" <?php echo $bdisable;?>><input type="hidden" id="db_time_bill_hidden" value="<?php echo $arr['bvalue']; ?>"></span>
									<span id="leftflt">&nbsp;&nbsp;
									<select name="db_time_billper" id="db_time_billper"  onclick="getPreviousRate(this);"  onChange="change_Period('dbtimebillrate');" class="summaryform-formelement<?php echo $bdisabled_user_input_field;?>" <?php echo $bdisable;?>>
										<!--UOM: get dynamic rate types-->

							<?php $timeOptions = getNewRateTypes($job_fetch[57]);
							  echo $timeOptions;?>
									</select>	
									</span>	
									<span id="leftflt">&nbsp;&nbsp;
										<select name="db_time_billcurr"  onChange="change_PeriodNew('dbtimebillratecur');"id="db_time_billcurr" class="summaryform-formelement<?php echo $bdisabled_user_input_field;?>" <?php echo $bdisable;?>>
										<?php
										displayCurrency($job_fetch[58]);
										?>
										</select>
									</span>
									<span id='db_bill_Tax_NonTax' class="fltcenter">
										<input name="DbbillrateTaxOpt" id="DbbillrateTaxOpt"  type="radio" value="Y" <?php if($ratesDefaultVal['DoubleTime'][1] == 'Y' || $ratesDefaultVal['DoubleTime'][1] == ''){echo ' checked="checked"';}else{ echo $bdisable; echo 'class="'.trim($bdisabled_user_input_field).'"';}?>><font class="afontstyle">Taxable</font>
										<input name="DbbillrateTaxOpt" id="DbbillrateTaxOpt"  type="radio" value="N" <?php if($ratesDefaultVal['DoubleTime'][1] == 'N'){echo ' checked="checked"';}else{ echo $bdisable; echo 'class="'.trim($bdisabled_user_input_field).'"';}?>><font class="afontstyle">Non-Taxable</font>
									</span>
								</td>
						</tr>

							<tr>
								<td colspan="2" style="padding:0px">
									<div id="multipleRatesTab"></div>
									<input type="hidden" id="selectedcustomratetypeids" value="">
								</td>
							</tr>
							<tr id="custom_rate_type_tr">
								<td colspan="2">
									<a class="crm-select-link" href="javascript:addRateTypes();">Select Custom Rate</a>
								</td>
							</tr>
						<!-- Per diem fields are added by vijaya on 11/11/2008 //-->  
						<tr id="billable_block" style="display:" >
							<td colspan="2" align="left" style="padding-left:0px;">
							<table width="100%" border="0" cellpadding="0" cellspacing="0">
							<tr>
								<td width="13%" align="left" valign="top" class="summaryform-bold-title">&nbsp;</td>
								<td width="10%" align="left" valign="top" class="summaryform-bold-title">Lodging</td>
								<td width="8%" align="left" valign="top" class="summaryform-bold-title">M&amp;IE</td>
								<td width="8%" align="left" valign="top" class="summaryform-bold-title">Total</td>
								<td width="23%" align="left" valign="top" class="summaryform-bold-title">&nbsp;</td>
								<td align="left" class="summaryform-bold-title" valign="top">&nbsp;</td>
							</tr>
							<tr>
								<td width="13%" align="left" class="summaryform-bold-title">Per Diem</td>
								<td><input type="text" name="txt_lodging" id="txt_lodging" value="<?php echo $job_fetch[64];?>" size="5" class="summaryform-formelement" onBlur="javascript:calculatePerDiem();"/></td>
								<td><input type="text" name="txt_mie" id="txt_mie" value="<?php echo $job_fetch[65];?>" size="5" class="summaryform-formelement" onBlur="javascript:calculatePerDiem();" />	 </td>
								<td><input type="text" name="txt_total" id="txt_total" value="<?php echo $job_fetch[66];?>" size="10" class="summaryform-formelement" onBlur="javascript:calculatePerDiem();"/></td>
								<td>
									<select name="sel_perdiem" id="sel_perdiem"   onClick="getPreviousRate(this);" onChange="change_Period('selperdiem');"class="summaryform-formelement">
									<!--UOM: get dynamic rate types-->

									<?php $timeOptions = getNewRateTypes($job_fetch[67]);echo $timeOptions;?>
									</select>
									<select name="sel_perdiem2" id="sel_perdiem2" class="summaryform-formelement">
									<?php
									displayCurrency($job_fetch[68]);
									?>
									</select>
								</td>
								<td align="left"  valign="middle">
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="radio" name="radio_taxabletype" id="radio_taxabletype" value="Y" <?php echo getChk('Y',$job_fetch[70]);?>>
										<label class="form-check-label" for="">Taxable</label>
									</div>
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="radio" name="radio_taxabletype" id="radio_taxabletype" value="N" <?php echo getChk('N',$job_fetch[70]);?> >
										<label class="form-check-label" for="">Non-Taxable</label>
									</div>
								</td>
							</tr>					
							<tr>
								<td width="13%" align="left" valign="top" class="summaryform-bold-title">&nbsp;</td>
								<td align="left" valign="middle" colspan="5">
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="radio" name="radio_billabletype" id="radio_billabletype" value="Y"  <?php echo getChk('Y',$job_fetch[69]);?> onClick="javascript:showBillDiv(this,'txt_total');" />
										<label class="form-check-label" for="">Billable</label>
									</div>
									<?php 
									$style = ($job_fetch[69]=="Y") ? 'style="float:left;"' : 'style="float:left; display: none;"';
									?>
									<div align="left" id="bill_Div" <?php echo $style; ?>>
									&nbsp;<input type="text" name="diem_billrate" id="diem_billrate" size="8" maxlength="9" onBlur="javascript:isNumbervalidation(this,'Billrate');" value="<?php echo $job_fetch[71];?>" class="summaryform-formelement" />
									</div>
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="radio" name="radio_billabletype" id="radio_billabletype" value="N" <?php echo getChk('N',$job_fetch[69]);?> onClick="javascript:showBillDiv(this);" />
										<label class="form-check-label">Non-Billable</label>
									</div>
								 </td>
							</tr>
							</table>
							</td>
						</tr>
						<!-- End of per diem //-->

						<input type="hidden" name="otrate">
						<input type="hidden" name="perotrate">
						<input type="hidden" name="payotrate">

						<tr id="jobloc_deduct" style=" <?php echo $Dis_job_chk;?>" >
							<td width="13%" class="summaryform-bold-title" nowrap>&nbsp;</td>
							<td><span id="leftflt" class="summaryform-bold-title"><input type="checkbox" name="use_jobloc_deduct" value='<?php echo $job_fetch[61];?>' <?php echo sent_check($job_fetch[61],'Y');?>>&nbsp;Use Job Location for Applicable Taxes</span></td>
						</tr>

						<tr>
							<td width="13%" class="summaryform-bold-title">Placement Fee</td>
							<td>
								<?php if($job_fetch[33] == '0.00' || $job_fetch[33] == '0')$job_fetch[33]='';?>
								<span id="leftflt"><input class="summaryform-formelement" type="text" size=15 name="pfee" value="<?php echo $job_fetch[33];?>" maxlength="9"></span>
								<span id="leftflt">&nbsp;&nbsp;
								<select name="payfee" class="summaryform-formelement">
								<?php
								displayCurrency($job_fetch[34]);
								?>
								</select></span>
							</td>
						</tr>
							<?php if(REFERRAL_BONUS_MANAGE=='ENABLED'){
								//Query to fetch referral Bonus
								if($candid=='' || $candid==null){
								   $candid = $cand_fetch[4];
								}
								$que_ref="SELECT bonus_amount FROM cand_refer WHERE ref_id ='".$candid."' AND req_id ='".$pos."'";
								$res_ref=mysql_query($que_ref,$db);
								$fetch_ref_recs=  mysql_num_rows($res_ref);
								if($fetch_ref_recs>0){
									$fetch_ref_row=mysql_fetch_row($res_ref); ?>
									<tr>
										<td width="13%" class="summaryform-bold-title">Referral Bonus</td>
										<td>
												<span id="leftflt"><input class="summaryform-formelement" type="text" size=15 name="ref_bonus_amount" disabled value="<?php echo $fetch_ref_row[0];?>"></span>
										</td>
									</tr>
								<?php  } } ?>
						<tr>
							<td colspan="2">
								<span id="leftflt">
								<span class="summaryform-bold-title">Commission</span></span>
							</td>
						</tr>
						<tr>
							<td colspan="2">
							<table border="0" class="crmsummary-editsections-table_noline table-mp-0" width="100%" style="margin:0px">
							<tr>
								<td width="16%" class="summaryform-nonboldsub-title">Add Person:</td>
								<td>
									<div class="d-flex align-items-center gap-1">
									<span id="leftflt">
									<select name="addemp" class="summaryform-formelement setcommrolesize" onChange="javascript:addCommission('newrow');">
									<option selected  value="">--select employee--</option>
									<?php
									$que="SELECT e.username as username, e.name as name FROM emp_list e LEFT JOIN hrcon_compen h ON (h.username = e.username) LEFT JOIN manage m ON (m.sno = h.emptype) WHERE e.lstatus != 'DA' AND e.lstatus != 'INACTIVE' AND e.empterminated !='Y' AND h.ustatus = 'active' AND m.type = 'jotype' AND m.status='Y' AND m.name IN ('Internal Direct', 'Internal Temp/Contract') AND h.job_type <> 'Y' ORDER BY e.name";

									$res=mysql_query($que,$db);
									while($row=mysql_fetch_row($res))
										print '<option '.compose_sel($elements[13],$row[0]).' value="'.'emp'.$row[0].'|'.$row[1].'">'.$row[1].'</option>';
									?>
									</select>
									</span>
									<span class="summaryform-formelement">&nbsp;|&nbsp;</span><a class="crm-select-link" href="javascript:contact_popup1('comcontact')">select contact</a>&nbsp;<a href="javascript:contact_popup1('comcontact')"><i class="fa fa-search"></i></a><span class="summaryform-formelement">&nbsp;|&nbsp;</span><a class="crm-select-link" href="javascript:newScreen('contact','comcontact')">new contact</a>
									</div>
								</td>
							</tr>
							<tr>
								<td colspan="2" class="summaryform-nonboldsub-title" style="border-bottom: 0px solid #ddd; padding-left:0px">
									<table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding-left:0px">
									<tbody id="commissionRows">
									<tr>
								<?php	
								// IF THE ROLES ARE WITH OUT SUBMISSION STATUS	 -- Jyothi 07/09/12
								if($Role_Submission != 'YES' )
								{
									$commissionSno	= "";
									$typecomm	= "";
									$comm_sel	= 	" SELECT person,
													type,
													co_type,
													comm_calc,
													IF(co_type!='' AND comm_calc!='',FORMAT(amount,2),'') AS amount,
													roleid,
													overwrite,
													enableUserInput
												FROM
													assign_commission
												WHERE
													assignid='".$posid[0]."' AND assigntype='JO' ORDER BY sno DESC";

									$comm_sel_res	= mysql_query($comm_sel,$db);
									$comm_num_rows	= mysql_num_rows($comm_sel_res);
									for($i=0;$i<$comm_num_rows;$i++)
									{
										$comm_fetch	= mysql_fetch_row($comm_sel_res);
										if($comm_fetch[1]=='E')
										{
											$sel_emp	= "SELECT name FROM emp_list WHERE username='".$comm_fetch[0]."'";
											$res_emp	= mysql_query($sel_emp,$db);
											$fetch_emp	= mysql_fetch_row($res_emp);
											$commName	= $fetch_emp[0];
											if( $commissionSno == "" )
												$commissionSno = "emp".$comm_fetch[0];
											else
												$commissionSno = "emp".$comm_fetch[0].",".$commissionSno;
											$typecomm="emp";
										}
										else
										{
											$sel_acc	= "SELECT CONCAT_WS('',staffacc_contact.fname,'',staffacc_contact.lname,IF(staffacc_cinfo.cname!='',concat('(',staffacc_cinfo.cname,')'),'')) FROM staffacc_contact LEFT JOIN staffacc_cinfo ON staffacc_contact.username = staffacc_cinfo.username AND staffacc_cinfo.type IN ('CUST', 'BOTH') WHERE staffacc_contact.sno='".$comm_fetch[0]."' and staffacc_contact.username!=''";
											$res_acc	= mysql_query($sel_acc,$db);
											$fetch_acc	= mysql_fetch_row($res_acc);
											$commName	= $fetch_acc[0];

											if($commissionSno == "")
												$commissionSno = $comm_fetch[0];
											else
												$commissionSno = $comm_fetch[0].",".$commissionSno;

											$typecomm="";	
										}	

										$comm_roletitle = '';
										if(!in_array($comm_fetch[5],$rolesSelectIds))
										{
											$role_sel	= "SELECT roletitle FROM company_commission WHERE sno =".$comm_fetch[5];
											$role_sel_res	= mysql_query($role_sel,$db);
											$role_fetch	= mysql_fetch_row($role_sel_res);									
											if($role_fetch[0] != '')
												$comm_roletitle = $role_fetch[0];		
										}					
										$rs		= "SELECT enable_details FROM company_commission WHERE sno =".$comm_fetch[5];
										$res		= mysql_query($rs,$db);
										$res_result	= mysql_fetch_row($res);
										$comm_enable_details = $res_result[0];	
										?>

										<script>
										var splitval	= "|akkenSplit|";
										addCommissionRow('<?php echo addslashes($commName);?>'+splitval+'<?php echo $comm_fetch[4];?>'+splitval+'<?php echo $comm_fetch[2];?>'+splitval+'<?php echo $comm_fetch[3];?>'+splitval+'<?php echo $comm_fetch[5];?>'+splitval+'<?php echo $comm_fetch[6];?>'+splitval+'<?php echo $comm_roletitle;?>','<?php echo $typecomm.$comm_fetch[0];?>');

										var rnval           = eval("document.forms[0].roleName"+'<?php echo $i;?>');
										var commVal         = document.getElementById("commval"+'<?php echo $i;?>');
										var rval            = eval("document.forms[0].ratetype"+'<?php echo $i;?>');
										var pval            = eval("document.forms[0].paytype"+'<?php echo $i;?>');
										var roleEDisable    = document.getElementById("roleEDisable"+'<?php echo $i;?>');
										var perflat         = document.getElementById("_perflat_"+'<?php echo $i;?>');

										if('<?php echo $comm_roletitle;?>' != '')
										{
											var oOption 	= document.createElement("option");
											oOption.appendChild(document.createTextNode('<?php echo $comm_roletitle;?>'));
											oOption.setAttribute("value", '<?php echo $comm_fetch[5];?>');
											rnval.appendChild(oOption);
										}

										commVal.value		= '<?php echo $comm_fetch[4];?>';
										rval.value	    	= '<?php echo $comm_fetch[2];?>';
										pval.value	   	= '<?php echo $comm_fetch[3];?>';
										SetSelectedIndexSelect(rnval,'<?php echo $comm_fetch[5];?>');   
										roleEDisable.value      ='<?php echo $comm_fetch[7];?>';
										perflat.style.visibility= 'visible';

										var perflat_symbol  = "";
										if('<?php echo $comm_fetch[2];?>' == '%')
										{
											perflat_symbol  = '&nbsp;<b>%</b>';
										}
										else if('<?php echo $comm_fetch[2];?>' == 'flat fee')
										{
											perflat_symbol  = '&nbsp;<b>$</b>';
										}
										perflat.innerHTML = perflat_symbol;

										commVal.disabled = false;
										if('<?php echo $comm_fetch[7];?>' == 'N')
										{
											commVal.disabled = true;
										}
										if('<?php echo $comm_enable_details;?>' == 'N')
										{
											commVal.style.visibility	= 'hidden';
											perflat.style.visibility	= 'hidden';
										}

										</script>
									<?php
									}													
									$commissionSno	= "";
									$typecomm	= "";

									$comm_sel	=" SELECT empId,
												'',
												rateType,
												commissionType,
												IF(rateType!='' AND commissionType!='',FORMAT(rate,2),'') AS rate,
												roleId,
												overWrite,
												enableUserInput
											FROM
												entity_roles
											LEFT JOIN
												entity_roledetails ON (entity_roledetails.crsno = entity_roles.crsno)
											WHERE
												entityId='".$cand_fetch[0]."' AND entityType = 'CRMCandidate'
											ORDER BY
												entity_roles.crsno DESC";

									$comm_sel_res	= mysql_query($comm_sel,$db);
									$comm_num_rows	= mysql_num_rows($comm_sel_res)+$i;

									for($i=$i; $i<$comm_num_rows; $i++)
									{
										$comm_fetch	= mysql_fetch_row($comm_sel_res);
										$sel_emp	= "SELECT name FROM emp_list WHERE username='".$comm_fetch[0]."'";
										$res_emp	= mysql_query($sel_emp,$db);
										$fetch_emp	= mysql_fetch_row($res_emp);
										$commName	= $fetch_emp[0];

										if($commissionSno == "")
											$commissionSno = "emp".$comm_fetch[0];
										else
											$commissionSno = "emp".$comm_fetch[0].",".$commissionSno;

										$typecomm	= "emp";
										$comm_roletitle = '';

										if(!in_array($comm_fetch[5],$rolesSelectIds))
										{
											$role_sel	= "SELECT roletitle FROM company_commission WHERE sno =".$comm_fetch[5];
											$role_sel_res	= mysql_query($role_sel,$db);
											$role_fetch	= mysql_fetch_row($role_sel_res);										
											if($role_fetch[0] != '')
												$comm_roletitle = $role_fetch[0];		
										}	
										$rs		= "SELECT enable_details FROM company_commission WHERE sno =".$comm_fetch[5];
										$res		= mysql_query($rs,$db);
										$res_result	= mysql_fetch_row($res);
										$comm_enable_details = $res_result[0];	
										?>
										<script>

										var splitval	= "|akkenSplit|";
										addCommissionRow('<?php echo addslashes($commName);?>'+splitval+'<?php echo $comm_fetch[4];?>'+splitval+'<?php echo $comm_fetch[2];?>'+splitval+'<?php echo $comm_fetch[3];?>'+splitval+'<?php echo $comm_fetch[5];?>'+splitval+'<?php echo $comm_fetch[6];?>'+splitval+'<?php echo $comm_roletitle;?>','<?php echo $typecomm.$comm_fetch[0];?>');

										var rnval           = eval("document.forms[0].roleName"+'<?php echo $i;?>');
										var commVal         = document.getElementById("commval"+'<?php echo $i;?>');
										var rval            = eval("document.forms[0].ratetype"+'<?php echo $i;?>');
										var pval            = eval("document.forms[0].paytype"+'<?php echo $i;?>');
										var roleEDisable    = document.getElementById("roleEDisable"+'<?php echo $i;?>');
										var perflat         = document.getElementById("_perflat_"+'<?php echo $i;?>');

										if('<?php echo $comm_roletitle;?>' != '')
										{
											var oOption = document.createElement("option");
											oOption.appendChild(document.createTextNode('<?php echo $comm_roletitle;?>'));
											oOption.setAttribute("value", '<?php echo $comm_fetch[5];?>');
											rnval.appendChild(oOption);
										}

										commVal.value   	= '<?php echo $comm_fetch[4];?>';
										rval.value	    	= '<?php echo $comm_fetch[2];?>';
										pval.value	    	= '<?php echo $comm_fetch[3];?>';
										SetSelectedIndexSelect(rnval,'<?php echo $comm_fetch[5];?>');   
										roleEDisable.value      ='<?php echo $comm_fetch[7];?>';
										perflat.style.visibility= 'visible';

										var perflat_symbol  = "";
										if('<?php echo $comm_fetch[2];?>' == '%')
										{
											perflat_symbol  = '&nbsp;<b>%</b>';
										}
										else if('<?php echo $comm_fetch[2];?>' == 'flat fee')
										{
											perflat_symbol  = '&nbsp;<b>$</b>';
										}
										perflat.innerHTML = perflat_symbol;

										commVal.disabled = false;
										if('<?php echo $comm_fetch[7];?>' == 'N')
										{
											commVal.disabled = true;
										}
										if('<?php echo $comm_enable_details;?>' == 'N')
										{
											commVal.style.visibility    	= 'hidden';
											perflat.style.visibility	= 'hidden';
										}
										</script>
										<?php									
										}													
								}
								else
								{

									$commissionSno="";
									$typecomm="";

									if($shiftid=="")
										$shiftid = 0;

									$perdiem_cond = "";
									if ($shift_type == "perdiem")
									{
										$perdiem_cond = " AND seqnumber = ".$seqnumber;
									}

									if ($place_link == "place_cand") {
										//Query to get the commissions for Place Button
										$comm_sel    = "SELECT  
											person AS empid,
											'',
											co_type AS rateType,
											comm_calc AS commissionType,
											IF(co_type!='' AND comm_calc!='',FORMAT(amount,2),'') AS rate,
											roleid,
											overwrite,
											`type`,
											enableUserInput
										FROM
											assign_commission
										WHERE
											assignid='".$pos."' AND assigntype='JO'
										ORDER BY sno";
									}else{
										$comm_sel	=" SELECT empid,
													'',
													rateType,
													commissionType,
													IF(rateType!='' AND commissionType!='',FORMAT(rate,2),'') AS rate,
													roleId,
													overWrite,
													type,
													enableUserInput
												FROM
													entity_submission_roledetails 
												WHERE
													candid='".$cand_fetch[0]."' AND posid = '".$pos."'
													AND shift_id=".$shiftid.$perdiem_cond;
									}
									


									$comm_sel_res=mysql_query($comm_sel,$db);
									$comm_num_rows=mysql_num_rows($comm_sel_res);
									for($i=0;$i<$comm_num_rows;$i++)
									{
										$comm_fetch=mysql_fetch_row($comm_sel_res);
										if($comm_fetch[7]!='A'){
											$sel_emp="SELECT name FROM emp_list WHERE username='".$comm_fetch[0]."'";
											$res_emp=mysql_query($sel_emp,$db);
											$fetch_emp=mysql_fetch_row($res_emp);
											$commName=$fetch_emp[0];

											$typecomm="emp";

										}else{				
											$tmpempId = str_replace('emp','',$comm_fetch[0]);
											$sel_emp = "SELECT CONCAT_WS('',staffacc_contact.fname,'',staffacc_contact.lname,IF(staffacc_cinfo.cname!='',concat('(',staffacc_cinfo.cname,')'),'')) as entityName
						FROM staffacc_contact LEFT JOIN staffacc_cinfo ON staffacc_contact.username = staffacc_cinfo.username AND staffacc_cinfo.type IN ('CUST', 'BOTH')
						WHERE staffacc_contact.sno='".$tmpempId."' and acccontact='Y' and staffacc_contact.username!=''";
											$res_emp=mysql_query($sel_emp,$db);
											$fetch_emp=mysql_fetch_row($res_emp);
											$commName=$fetch_emp[0];	
											$typecomm="";
										}
										if($commissionSno=="")
											$commissionSno="emp".$comm_fetch[0];
										else
											$commissionSno="emp".$comm_fetch[0].",".$commissionSno;

										$comm_roletitle = '';

										if(!in_array($comm_fetch[5],$rolesSelectIds))
										{
											$role_sel="SELECT roletitle FROM company_commission WHERE sno =".$comm_fetch[5];
											$role_sel_res=mysql_query($role_sel,$db);
											$role_fetch=mysql_fetch_row($role_sel_res);										
											if($role_fetch[0] !='')
												$comm_roletitle = $role_fetch[0];		
										}	
										$rs="SELECT enable_details FROM company_commission WHERE sno =".$comm_fetch[5];
										$res=mysql_query($rs,$db);
										$res_result=mysql_fetch_row($res);
										$comm_enable_details = $res_result[0];
										//print_r($comm_fetch);													
										?>
										<script>
											var splitval    =   "|akkenSplit|";
											addCommissionRow('<?php echo addslashes($commName);?>'+splitval+'<?php echo $comm_fetch[4];?>'+splitval+'<?php echo $comm_fetch[2];?>'+splitval+'<?php echo $comm_fetch[3];?>'+splitval+'<?php echo $comm_fetch[5];?>'+splitval+'<?php echo $comm_fetch[6];?>'+splitval+'<?php echo $comm_roletitle;?>','<?php echo $typecomm.$comm_fetch[0];?>');

											var rnval           = eval("document.forms[0].roleName"+'<?php echo $i;?>');
											var commVal         = document.getElementById("commval"+'<?php echo $i;?>');
											var rval            = eval("document.forms[0].ratetype"+'<?php echo $i;?>');
											var pval            = eval("document.forms[0].paytype"+'<?php echo $i;?>');
											var roleEDisable    = document.getElementById("roleEDisable"+'<?php echo $i;?>');
											var perflat         = document.getElementById("_perflat_"+'<?php echo $i;?>');


											if('<?php echo $comm_roletitle;?>' != '')
											{
												var oOption = document.createElement("option");
												oOption.appendChild(document.createTextNode('<?php echo $comm_roletitle;?>'));
												oOption.setAttribute("value", '<?php echo $comm_fetch[5];?>');
												rnval.appendChild(oOption);
											}

											commVal.value   = '<?php echo $comm_fetch[4];?>';
											rval.value	    = '<?php echo $comm_fetch[2];?>';
											pval.value	    = '<?php echo $comm_fetch[3];?>';
											SetSelectedIndexSelect(rnval,'<?php echo $comm_fetch[5];?>');   
											roleEDisable.value          ='<?php echo $comm_fetch[8];?>';
											perflat.style.visibility	= 'visible';

											var perflat_symbol  = "";
											if('<?php echo $comm_fetch[2];?>' == '%')
											{
												perflat_symbol  = '&nbsp;<b>%</b>';
											}

											else if('<?php echo $comm_fetch[2];?>' == 'flat fee')
											{
												perflat_symbol  = '&nbsp;<b>$</b>';
											}
											perflat.innerHTML = perflat_symbol;

											commVal.disabled = false;	
											if('<?php echo $comm_fetch[8];?>' == 'N')
											{
												commVal.disabled    = true;
											}
											if('<?php echo $comm_enable_details;?>' == 'N')
											{
												commVal.style.visibility    	= 'hidden';
												perflat.style.visibility	= 'hidden';
											}
										</script>
									<?php									
									}
								}
								?>
								</tr>
								</tbody>
								</table>
								</td>
							</tr>
							</table>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<span class="commRolesNoteStyle">
									Note : If no role is selected for an employee, such records will not be saved.
								</span>
							</td>
						</tr>
						<tr id="comm_assign" style=" <?php echo $assign_display;?>">
							<td width="13%" class="summaryform-bold-title">Assignment Name</td>
							<td><span id="leftflt"><input class="summaryform-formelement form-control w-250" type=text name="comm_assname" size=40 maxsize=150 maxlength=150 value=""></span></td>
						</tr>
						<tr>
							<td width="13%" class="summaryform-bold-title" nowrap="nowrap">Payroll Provider ID#</td>
							<td><span id="leftflt"><input class="summaryform-formelement form-control w-250" type="text" size=20 name="payrollid" value="<?php echo $job_fetch[51];?>" maxlength="20"></span></td>
						</tr>	
						<tr>
							<td width="13%" class="summaryform-bold-title" nowrap="nowrap">Workers Comp Code<?php echo $mandatory_synchr_akkupay; ?></td>			
							<td>
								<span id="leftflt">
								<input type="hidden" id="wid" value="<?=$job_fetch[39]?>">
								<select class="summaryform-formelement form-select w-250" name="workcode" id="workcode" style="width:210px"></select>
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
								<select name="pterms" id="pterms" class="form-select w-250">
								<option value=""> -- Select -- </option>
								<?php  
								while($BillPay_Data = mysql_fetch_row($BillPay_Res))
								{ 
									$BillPay_Data[1] = str_replace('\\', '', $BillPay_Data[1]);
									?>
									<option value="<?=$BillPay_Data[0];?>" <?php echo sele($job_fetch[37],$BillPay_Data[0]); ?> title='<?=$BillPay_Data[1];?>'><?=$BillPay_Data[1];?></option>
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
						<!-- <tr>
							<td width="13%" class="summaryform-bold-title">Timesheet Approval</td>
							<td>
								<span id="leftflt" class="summaryform-nonboldsub-title"><input class="summaryform-formelement" type="radio" name="tapproval" id="tapproval" value="Manual" <?php // echo getChk('Manual',$job_fetch[38]);?>>Manual</span>
								<span id="leftflt" class="summaryform-nonboldsub-title">&nbsp;&nbsp;&nbsp;<input class="summaryform-formelement" type="radio" name="tapproval" id="tapproval" value="Online" <?php // echo getChk('Online',$job_fetch[38]);?>>Online</span>
							</td>
						</tr> -->
						<span class="summaryform-bold-title" style="visibility: hidden;">
							<span id="leftflt" class="summaryform-nonboldsub-title"><input class="summaryform-formelement form-check-input" type="radio" name="tapproval" id="tapproval" value="Manual" checked="checked" <?php echo getChk('Manual',$job_fetch[38]);?>>Manual</span>
							<span id="leftflt" class="summaryform-nonboldsub-title">&nbsp;&nbsp;&nbsp;<input class="summaryform-formelement form-check-input" type="radio" name="tapproval" id="tapproval" value="Online" <?php echo getChk('Online',$job_fetch[38]);?>>Online</span>
						</span>
						<tr>
							<td width="13%" class="summaryform-bold-title">PO Number</td>
							<td><span id="leftflt"><input class="summaryform-formelement form-control w-250" type="text" name="po_num" size=20 value="<?php echo html_tls_specialchars($job_fetch[59],ENT_QUOTES);?>" maxlength="255"></span></td>
						</tr>
						<tr>
							<td width="13%" class="summaryform-bold-title">Department</td>
							<td><span id="leftflt"><input class="summaryform-formelement form-control w-250" type="text" name="place_dept" size=20 value="<?php echo html_tls_specialchars(stripslashes($job_fetch[60]),ENT_QUOTES);?>" maxlength="255"></span></td>
						</tr>



						<tr>
							<td width="13%" class="summaryform-bold-title" nowrap="nowrap">Attention</td>
							<td><span id="leftflt"><input class="summaryform-formelement form-control w-250" type="text" size=20 name="attention" id="attention" value="<?php echo html_tls_specialchars($attention,ENT_QUOTES);?>" maxlength="255"></span></td>
						</tr>

						<?php
							$billcontact=$job_fetch[7];
							$bill_loc=$job_fetch[8];

						if($billcontact!=0)
						{
							$que2="select TRIM(CONCAT_WS('',fname,' ',lname,'(',IF(email='',nickname,email),')')),status,fname,mname,lname,address1,address2,city,state,csno,accessto from staffoppr_contact where sno='".$billcontact."'";
							$res2=mysql_query($que2,$db);
							$row2=mysql_fetch_row($res2);
							$billcont=$billcont = $row2[2]." ".$row2[3]." ".$row2[4];
							$billcont_stat=$row2[1];
							$billcompany=$row2[9];

							$chkBILLContAccess = chkAvailAccessTo($row2[10],$row2[1],$username);
						}
						else if($bill_loc>0 && ($billcontact==0 || $billcontact==""))
						{
							$que2="select csno from staffoppr_location where sno='".$bill_loc."' and ltype in ('com','loc')";
							$res2=mysql_query($que2,$db);
							$row2=mysql_fetch_row($res2);
							$billcompany=$row2[0];
						}
						?>

						<tr>
						<?php
												$cus_username="select username from staffacc_cinfo where sno='".$billcompany."'";
												$cus_username_res=mysql_query($cus_username,$db);
												$cust_username=mysql_fetch_row($cus_username_res);
												$custusername=$cust_username[0];

												?>
							<input type="hidden" name="billcompany_sno" id="billcompany_sno" value="<?php echo $billcompany;?>">
								<input type="hidden" name="billcompany_username" id="billcompany_username" value="<?php echo $custusername;?>">
							<td class="summaryform-bold-title">Billing Address</td>
							<td><span id="billdisp_comp"><input type="hidden" name="bill_loc" id="bill_loc"><a class="crm-select-link" href="javascript:bill_jrt_comp('bill')">select company</a>&nbsp;</span></span>&nbsp;<span id="billcomp_chgid">&nbsp;</span></td>
						</tr>

						<tr>
							<input type="hidden" name="billcontact_sno" id="billcontact_sno" value="<?php echo $billcontact;?>">
							<td width="13%" class="summaryform-bold-title">Billing Contact</td>
							<td>
							<?php
							if($billcontact==0)
							{
								?>
								<span id="billdisp"><a class="crm-select-link" href="javascript:bill_jrt_cont('bill')">select contact</a></span>
								&nbsp;<span id="billchgid"><a href="javascript:bill_jrt_cont('bill')"><i class="fa fa-search"></i></a><span class="summaryform-formelement">&nbsp;|&nbsp;</span><a class="crm-select-link" href="javascript:donew_add('bill')">new&nbsp;contact</a></span>
								<?
							}
							else 
							{ 
								?>
								<span id="billdisp">
									<?php
									if($chkBILLContAccess == "No")
									{
										echo "<span class=summaryform-formelement>".$billcont."</span>";
									}
									else
									{
									?>
										<a class="crm-select-link" href="javascript:contact_func('<?php echo $billcontact;?>','<?php echo $billcont_stat;?>','bill')"><?php echo $billcont;?></a>
									<?php
									}
									?>
								</span>
								&nbsp;<span id="billchgid"><span class=summaryform-formelement>(</span> <a class=crm-select-link href=javascript:bill_jrt_cont('bill')>change </a>&nbsp;<a href=javascript:bill_jrt_cont('bill')><i class="fa fa-search"></i></a><span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class=crm-select-link href=javascript:donew_add('bill')>new</a><span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class="crm-select-link" href="javascript:removeContact('bill')">remove&nbsp;</a><span class=summaryform-formelement>&nbsp;)&nbsp;</span></span>
								<?
							}
							?>
							</td>
						</tr>

						<tr>
							<td width="13%" valign="top" class="summaryform-bold-title">Billing Terms </td>
							<td>
								<?php
								$BillPay_Sql = "SELECT billpay_termsid, billpay_code FROM bill_pay_terms WHERE billpay_status = 'active' AND billpay_type = 'BT' ORDER BY billpay_code";
								$BillPay_Res = mysql_query($BillPay_Sql,$db);
								?>
								<select name="billreq" id="billreq" class="form-select w-250">
								<option value=""> -- Select -- </option>
								<?php  
								while($BillPay_Data = mysql_fetch_row($BillPay_Res))
								{ 
									?>
									<option value="<?=stripslashes($BillPay_Data[0]);?>" <?php echo sele($job_fetch[62],$BillPay_Data[0]); ?> title="<?=html_tls_specialchars(stripslashes($BillPay_Data[1]),ENT_QUOTES);?>"><?=stripslashes($BillPay_Data[1]);?></option>
									<?php 
								}
								?>
								</select>
								<?php 
								if(ENABLE_MANAGE_LINKS == 'Y')
									echo '&nbsp;&nbsp;&nbsp;<a href="javascript:doManageBillPayTerms(\'Billing\',\'billreq\')" class="edit-list">Manage</a> ';
								?>				
							</td>
						</tr>
						<tr>
							<td width="13%" valign="top" class="summaryform-bold-title">Service Terms</td>
							<td><textarea name="servterms" cols="60" rows="5" id="servterms" ><?php echo dispfdb(stripslashes($job_fetch[63]));?></textarea></td>
						</tr>
						</table>
					</div>
				</div>
			</div>
			<div class="accordion-item">
				<h2 class="accordion-header" id="HiringProcess">
					<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHiringProcess" aria-expanded="false" aria-controls="collapseHiringProcess">
						Hiring Process
					</button>
				</h2>
				<div id="collapseHiringProcess" class="accordion-collapse collapse" aria-labelledby="HiringProcess">
					<div class="accordion-body">
						<table class="table">		
							<tr>
								<td width="13%" class="summaryform-bold-title">Contact Method</td>
								<td>
									<div class="form-check form-check-inline">
										<input class="form-check-input" name="hpcmphone" type="checkbox" id="hpcmphone" value="phone" <?php echo sent_check($con_method[0],'phone');?>>
										<label class="form-check-label">Phone</label>
									</div>
									<div class="form-check form-check-inline">
										<input class="form-check-input" name="hpcmmobile" type="checkbox" id="hpcmmobile" value="Mobile" <?php echo sent_check($con_method[1],'Mobile');?>>
										<label class="form-check-label">Mobile</label>
									</div>
									<div class="form-check form-check-inline">
										<input class="form-check-input" name="hpcmfax" type="checkbox" id="hpcmfax" value="Fax" <?php echo sent_check($con_method[2],'Fax');?>>
										<label class="form-check-label">Fax</label>
									</div>
									<div class="form-check form-check-inline">
										<input class="form-check-input" name="hpcmemail" type="checkbox" id="hpcmemail" value="Email" <?php echo sent_check($con_method[3],'Email');?> >
										<label class="form-check-label">Email</label>
									</div>
								</td>
							</tr>
							<tr>
								<td width="13%" valign="top"><span class="summaryform-bold-title">Requirements</span></td>
								<td>
									<div class="form-check form-check-inline">
										<input class="form-check-input" name="hrresume" type="checkbox" id="hprresume" value="Resume" <?php echo sent_check($check_value[0],'Resume');?>>
										<label class="form-check-label">Resume</label>
									</div>
									<div class="form-check form-check-inline">
										<input class="form-check-input" name="pinterview" type="checkbox" id="hprpinterview" value="Pinterview" <?php echo sent_check($check_value[1],'Pinterview');?> >
										<label class="form-check-label">Phone Interview</label>
									</div>
									<div class="form-check form-check-inline">
										<span class="d-flex align-items-center">
											<input class="form-check-input" name="interview" type="checkbox" id="hprinterview" value="Interview" <?php echo sent_check($check_value[2],'Interview');?>>
											<label class="form-check-label">
												Interview (avg #<input class="form-control form-control-sm" name="hpraverage" type="text" id="hpraverage" value="<?php echo $job_fetch[23];?>" size=15 maxlength="2">)
											</label>
										</span>
									</div>
									<div class="d-flex align-items-center mt-1">				
									<div class="form-check form-check-inline">
										<input class="form-check-input" name="backgr" type="checkbox" id="hprbcheck" value="Bcheck" <?php echo sent_check($check_value[3],'Bcheck');?>>
										<label class="form-check-label">Background Check</label>
									</div>
									<div class="form-check form-check-inline">
										<input class="form-check-input" name="drug" type="checkbox" id="hprdscreen" value="Dscreen" <?php echo sent_check($check_value[4],'Dscreen');?>>
										<label class="form-check-label">Drug Screen</label>
									</div>
									<div class="form-check form-check-inline">
										<input class="form-check-input" name="physical" type="checkbox" id="hprphysical" value="Physical" <?php echo sent_check($check_value[5],'Physical');?>>
										<label class="form-check-label">Physical</label>
									</div>
									<div class="form-check form-check-inline">
										<input class="form-check-input" name="govt" type="checkbox" id="hprgclearance" value="Gclearance" <?php echo sent_check($check_value[6],'Gclearance');?>>
										<label class="form-check-label">Govt Clearance</label>
									</div>
									</div>
									<div class="form-check form-check-inline mt-1">
										<span class="d-flex align-items-center">
										<input class="form-check-input" name="addcheck" type="checkbox" id="hpraddinfocb" value="Addinfo" <?php echo sent_check($check_value[7],'Addinfo');?>>
										<label class="form-check-label">

												Additional Info
												<input name="addinfo" type=text class="form-control form-control-sm" id="hprainfotb" value="<?php echo dispfdb($job_fetch[12]);?>" size=50 maxlength=100 maxsize=100>

										</label>
										</span>
									</div>
								</td>
							</tr>			
							</table>
					</div>
				</div>
			</div>
			<div class="accordion-item">
				<h2 class="accordion-header" id="Notes">
					<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNotes" aria-expanded="false" aria-controls="collapseNotes">
						Notes
					</button>
				</h2>
				<div id="collapseNotes" class="accordion-collapse collapse" aria-labelledby="Notes">
					<div class="accordion-body">
						<table class="table">
							<tr>
								<td width="13%" valign="top" class="crmsummary-content-title">Notes</td>
								<td><textarea rows="5" cols="60" name="notes"></textarea></td>
							</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
	</fieldset>
</div>
</form>

<input type=hidden name=candtype value="<?php echo $type;?>">

<script type="text/javascript" src="/BSOS/scripts/shift_schedule/jquery.modalbox.js"></script>
<link type="text/css" rel="stylesheet" href="/BSOS/css/gigboard/select2_V_4.0.3.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/shift_schedule/shiftcolors.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/gigboard/gigboardCustom.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/select2_shift.css">
<script type="text/javascript" src="/BSOS/scripts/gigboard/select2_V_4.0.3.js"></script>

<!-- Perdiem Shift Scheduling -->
<link type="text/css" rel="stylesheet" href="/BSOS/css/perdiem_shift_sch/perdiemShifts.css">
<!-- <style type="text/css">
.shiftCheckCust .container input:checked ~ .checkmark:after { display: block !important;}
	.shiftCheckCust .container input:checked ~ .checkmark {
    background-color: #3fb8f1 !important;
    border: solid 2px #3fb8f1 !important;
}
</style> -->
<script type="text/javascript" src="/BSOS/scripts/perdiem_shift_sch/PerdiemShiftSch.js"></script>

<script language="javascript">
	var rowCount="<?php echo (int)$newRowId+1;?>";
	var row_class="<?php echo $row_class;?>";
	setFormObject("document.resume");
	<?php 
	if(SHIFT_SCHEDULING_ENABLED == 'N') 
	{
		?>
			displayScheduledata("<?php echo $retsch;?>");
		<?php

	}

	if($place_jobtype!="Direct" && $place_jobtype!="Internal Direct")
	{
		if($chkSubRatesFlag) // loading the submission rates
		{
			echo "getSubmissionRates();";
		}
		else
		{
			echo "customRateTypes($pos, '$mode_rate_type');";
		}

		if ($place_link == "bulk_place_cand" && $chkBulkSubRatesFlag==true)
		{
			?>
			$(document).ready(function() {
				promptUsertoSelRateOption();
			});
			<?php
		}	
	}

	?>
</script>
<?php
	if($place_jobtype!="Direct" && $place_jobtype!="Internal Direct")
	{
		if($chkSubRatesFlag)
		{
			$SelQry = "SELECT ma.sno FROM multiplerates_joborder jo, multiplerates_master ma WHERE jo.status = 'ACTIVE' AND jo.joborderid = '".$res_sno."' AND jo.jo_mode='submission' AND ma.status='ACTIVE' AND ma.default_status='N' AND ma.rateid=jo.ratemasterid GROUP BY ma.sno";
		}
		else
		{
			$SelQry = "SELECT ma.sno FROM multiplerates_joborder jo, multiplerates_master ma WHERE jo.status = 'ACTIVE' AND jo.joborderid = '".$pos."' AND jo.jo_mode='".$mode_rate_type."' AND ma.status='ACTIVE' AND ma.default_status='N' AND ma.rateid=jo.ratemasterid GROUP BY ma.sno";
		}

		$resQry = mysql_query($SelQry);
		/* TLS-01202018 */
		$rateValuesArray = array();
		while($recQry = mysql_fetch_assoc($resQry))
		{
			$rateValuesArray[] = $recQry['sno'];
			?>
<script type="text/javascript">
selectedprtidsarray.push(<?php echo $recQry['sno'];?>);
</script>
<?php
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
	}

if(($jrtcontact>0 || $jrt_loc>0) && ($billcontact>0 || $bill_loc>0))
	print "<script>getBothLocations('".$jrtcompany."','".$jrtcontact."','".$jrt_loc."','jrt','".$billcompany."','".$billcontact."','".$bill_loc."','bill',1);</script>";
else if(($jrtcontact>0 || $jrt_loc>0) && ($billcontact==0 || $billcontact=="" || $bill_loc==0 || $bill_loc==""))
	print "<script>getCRMLocations('".$jrtcompany."','".$jrtcontact."','".$jrt_loc."','jrt',1);</script>";
else if(($billcontact>0 || $bill_loc>0) && ($jrtcontact==0 || $jrtcontact=="" || $jrt_loc==0 || $jrt_loc==""))
	print "<script>getCRMLocations('".$billcompany."','".$billcontact."','".$bill_loc."','bill',1);</script>";
?>

<input type="hidden" id="cap_separated_custom_rates" value="" />
<script>
var jrt_bill_loc = window.document.getElementById('jrt_loc');
var sbloc = jrt_bill_loc.value.split("-");
if(sbloc[0]=="loc" || sbloc[0]=="com")
{	
	<?php
	if($chk_bt)
	{?>
		preLoadBurdenType(sbloc[1],'CRM');    
	<?php
	}
	?>                    
}
<?php
if($burden_status == 'yes'){
?>
    BTChangeAction(document.getElementById('burdenType'),'placement',true);
    BillBTChangeAction(document.getElementById('bill_burdenType'),'placement');
<?php    
} else {
?>
    calculatebtmargin();
<?php    
}
?>

$(window).focus(function() {
    if(document.getElementById('payrate_calculate_confirmation_window_onblur').value != ""){
        document.getElementById('payrate_calculate_confirmation').value = document.getElementById('payrate_calculate_confirmation_window_onblur').value;
    }
    if(document.getElementById('billrate_calculate_confirmation_window_onblur').value != ""){
        document.getElementById('billrate_calculate_confirmation').value = document.getElementById('billrate_calculate_confirmation_window_onblur').value;
    }
});
</script>
</div>
<?php include('footer.inc.php') ?>
</body>
</html>