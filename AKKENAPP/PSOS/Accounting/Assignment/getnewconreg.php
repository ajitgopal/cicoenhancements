<?php
	require("global.inc");
	require_once("multipleRatesClass.php"); 
	$ratesObj=new multiplerates();
	$ACC_AS_SESSIONRN = strtotime("now");
	$_SESSION['ACC_AS_SESSIONRN'] = $ACC_AS_SESSIONRN;
	session_unregister($_SESSION["page15".$ACC_AS_SESSIONRN]);
	session_unregister($_SESSION["page215".$ACC_AS_SESSIONRN]);
	session_unregister($_SESSION["schdet".$ACC_AS_SESSIONRN]);
	unset($_SESSION["commission_session"]);
	unset($_SESSION["commission_session".$ACC_AS_SESSIONRN]);

	$recassign=explode("|",$rec);
	$ename=$edeptname;
	$val_hr=$_GET['val_hr'];

	$query="select username from emp_list where sno=".$recassign[3];
	$res2=mysql_query($query,$db);
	$udata=mysql_fetch_row($res2);
	$_SESSION[conusername.$ACC_AS_SESSIONRN]=$udata[0];
	$_SESSION[recno.$ACC_AS_SESSIONRN]=$rec;
	$que_str=" and ustatus='active'";
	if($recassign[2] != ''){
		$table="hrcon";
		$mode_rate_type = "hrcon";
		$moderate_type = "hrcon";
		if ($recassign[4] == "payref") {
			$rec = $recassign[0]."|15|".$recassign[2]."|".$recassign[3];
			$assignid = $recassign[5];
			$_SESSION[recno.$ACC_AS_SESSIONRN]=$rec;
		}
		$commissionConditions = "assignid='".$recassign[0]."' AND assigntype='H'";
	}/*else{
		$table="empcon";
		$mode_rate_type = "empcon";
		$moderate_type = "empcon";
		if ($recassign[4] == "payref") {
			$selectEmpSno = "SELECT ej.sno,ej.assg_status FROM hrcon_jobs hj LEFT JOIN empcon_jobs ej ON (ej.pusername = hj.pusername) WHERE hj.sno='".$recassign[0]."'";
			$result = mysql_query($selectEmpSno,$db);
			$row = mysql_fetch_assoc($result);
			$recassign[0] = $row['sno'];
			$recassign[2] = $row['assg_status'];
			$assignid = $recassign[5];
			$rec = $row['sno']."|15|".$row['assg_status']."|".$recassign[3];
			$_SESSION[recno.$ACC_AS_SESSIONRN]=$rec;
		}
		$commissionConditions = "assignid='".$recassign[0]."' AND assigntype='E'";
	}*/
	
	$cque="select sno,username,jotype,catid,project,refcode,posstatus,vendor,contact,client,manager,endclient,s_date,date_format(exp_edate,'%Y-%c-%d'),posworkhr,iterms,e_date,reason,date_format(hired_date,'%Y-%c-%d'),rate,rateper,rateperiod,'','',bamount,bcurrency,bperiod,pamount,pcurrency,pperiod,pterms,otrate,ot_currency,ot_period,placement_fee,placement_curr,jtype,bill_contact,bill_address,wcomp_code,imethod,tsapp,bill_req,service_terms,hire_req,notes,addinfo,avg_interview,burden,margin,markup,calctype,prateopen,brateopen,prateopen_amt,brateopen_amt,CONCAT_WS('^AKK^',".tzRetQueryStringDTime("cdate","Date","/").",cdate),otprate_amt,otprate_period,otprate_curr,otbrate_amt,otbrate_period,otbrate_curr,payrollpid,offlocation,double_prate_amt,double_prate_period,double_prate_curr,double_brate_amt,double_brate_period,double_brate_curr, po_num,department,job_loc_tax,date_placed,diem_lodging,diem_mie,diem_total,diem_currency,diem_period,diem_billable,diem_taxable,diem_billrate,madison_order_id,deptid,attention,corp_code,industryid,schedule_display,bill_burden,worksite_code,shiftid,daypay,licode,federal_payroll,nonfederal_payroll,contractid,classification,ts_layout_pref from ".$table."_jobs where sno='".$recassign[0]."'";
	$cres=mysql_query($cque,$db);
	$crow=mysql_fetch_array($cres);

	$schedule_display = $crow[88];
	$federal_payroll = $crow[94];
	$nonfederal_payroll = $crow[95];
	$contractid = $crow[96];
	$classification = $crow[97];
	//if in case the value is empty then override it to old by default
	if($schedule_display == "" || $schedule_display == null)
	{
		$schedule_display = 'OLD';
	}
	if($crow[52]=='Y')
		$payFinal="open";
	else
		$payFinal="rate";

	if($crow[53]=='Y')
		$billFinal="open";
	else
		$billFinal="rate";

	$type_order_id = $crow[0];
	$assignment_rates=$ratesObj->displayMultipleRates();
	$ratesDefaultArr = $ratesObj->getDefaultMutipleRates();
	$_SESSION[assignment_mulrates.$ACC_AS_SESSIONRN] = $assignment_rates."^DefaultRates^";

	//Condition for assignment type when it comes from closing placement
	if($crow[36]=="" && $crow[2]!="")
	{
		$typeOfAss=getManage($crow[2]);
		$crow[36]="OP";
	}

	$_SESSION[page15.$ACC_AS_SESSIONRN]=$crow[0]."|".$crow[1]."|".$crow[2]."|".$crow[3]."|".$crow[4]."|".$crow[5]."|".$crow[6]."|".$crow[7]."|".$crow[8]."|".$crow[9]."|".$crow[10]."|".$crow[11]."|".$crow[12]."|".$crow[13]."|".$crow[14]."|".$crow[15]."|".$crow[16]."|".$crow[17]."|".$crow[18]."|".$crow[19]."|".$crow[20]."|".$crow[21]."|hire||".$crow[24]."^^".$crow[26]."^^".$crow[25]."|".$crow[27]."^^".$crow[29]."^^".$crow[28]."|".$crow[48]."|".$crow[49]."|".$crow[50]."|".$crow[51]."^^".$payFinal."^^".$billFinal."^^".$crow[54]."^^".$crow[55]."|".$crow[30]."|".$crow[31]."|".$crow[32]."|".$crow[33]."|".$crow[34]."|".$crow[35]."|".$crow[36]."|".$crow[37]."|".$crow[38]."|".$crow[39]."|".$crow[40]."|".$crow[41]."|".$crow[42]."|".$crow[43]."|".$crow[44]."|".$crow[45]."|".$crow[46]."|".$crow[47]."|".$crow[0];
	
	$counter = 0;
	$varConact = ",";
	$commissionConditions.= " AND shift_id='".$crow['shiftid']."'";

	$quec		= "SELECT
				person, IF(co_type!='' AND comm_calc!='',FORMAT(amount,2),'') AS amount, co_type, comm_calc, type, roleid, overwrite, enableUserInput
			FROM
				assign_commission
			WHERE
				".$commissionConditions."
			ORDER BY
				sno DESC";
	$cresc		= mysql_query($quec,$db);
	$numRowscomm	= mysql_num_rows($cresc);

	while($crowc = mysql_fetch_row($cresc)) {
		if($counter == 0) {
			if($crowc[4] == "E")
				$comSnos	= "emp".$crowc[0];
			else
				$comSnos	= $crowc[0];

			$comVals	= $crowc[1];
			$comRate	= $crowc[2];
			$comFee		= $crowc[3];
			$comRoleid	= $crowc[5];
			$comOverwrite	= $crowc[6];
			$comEUserInput	= $crowc[7];
			$counter	= 1;
		}
		else {
			if($crowc[4] == "E")
				$comSnos	.= $varConact."emp".$crowc[0];
			else
				$comSnos	.= $varConact.$crowc[0];

			$comVals	.= $varConact.$crowc[1];
			$comRate	.= $varConact.$crowc[2];
			$comFee		.= $varConact.$crowc[3];
			$comRoleid	.= $varConact.$crowc[5];
			$comOverwrite	.= $varConact.$crowc[6];
			$comEUserInput	.= $varConact.$crowc[7];
		}
	}

	$numRowscomm1	= $numRowscomm-1;

	if($numRowscomm == "0" || $numRowscomm == "")
		$statusComm	= "";
	else
		$statusComm	= "nav";

	//Appending the comission values
	$_SESSION[page15.$ACC_AS_SESSIONRN]	.= "|".$statusComm."|".$numRowscomm1."|".$comSnos."|".$crow[57]."|".$crow[58]."|".$crow[59]."|".$crow[60]."|".$crow[61]."|".$crow[62]."|".$crow[63]."|".$crow[56]."|".$crow[64]."|".$crow[65]."|".$crow[66]."|".$crow[67]."|".$crow[68]."|".$crow[69]."|".$crow[70]."|".$crow[71]."|".$crow[72]."|".$crow[73]."|".$crow[74]."|".$crow[75]."|".$crow[76]."|".$crow[77]."|".$crow[78]."|".$crow[79]."|".$crow[80]."|".$crow[81]."||".$crow[82]."|".$crow[83]."|".$ratesDefaultArr['Regular'][0]."|".$ratesDefaultArr['Regular'][1]."|".$ratesDefaultArr['OverTime'][0]."|".$ratesDefaultArr['OverTime'][1]."|".$ratesDefaultArr['DoubleTime'][0]."|".$ratesDefaultArr['DoubleTime'][1]."||".$crow[84]."|".$crow[85]."|".$crow[86]."|".$crow[87]."|".$schedule_display."|".$crow[89]."|".$crow[90]."|".$crow[92]."|".$crow[93]."|".$federal_payroll."|".$nonfederal_payroll."|".$contractid."|".$classification."|".$crow[98];

	$commSessionValues	= $comVals."|".$comRate."|".$comFee."||".$comRoleid."|".$comOverwrite."|".$comEUserInput;

	$_SESSION['commission_session'.$ACC_AS_SESSIONRN]	= $commSessionValues;

	$_SESSION[page215.$ACC_AS_SESSIONRN]	= "OP";

	session_register("employee_name");
	session_register("page5d");
	session_register("page6d");
	session_register("from");

	$_SESSION[recno.$ACC_AS_SESSIONRN]=$_GET['rec'];
	$deskval=$_GET['deskval'];

	if($deskval==1)
	{
		$_SESSION[fromAssign.$ACC_AS_SESSIONRN]="yes";

		Header("Location:newassign.php?recno=".$_SESSION[recno.$ACC_AS_SESSIONRN]."&assign=edit&test_acc=1&hrsno=".$val_hr."&ACC_AS_SESSIONRN=".$ACC_AS_SESSIONRN);
	}
	elseif($recassign[1]==15)
	{
		if(isset($fromassignreport) && $fromassignreport == "yes")
		{
			$_SESSION[fromAssign.$ACC_AS_SESSIONRN]="yes";

			Header("Location:/include/closedassign.php?fromassignreport=yes&recno=".$rec."&assign=edit&test_acc=1&hrsno=".$recassign[0]."&ACC_AS_SESSIONRN=".$ACC_AS_SESSIONRN);
		}
		else
		{
			if($recassign[2] == "approved")
			{
				$_SESSION[fromAssign.$ACC_AS_SESSIONRN]="yes";

				if(isset($copyGig))
				{
					Header("Location:editassign.php?moderate_type=".$moderate_type."&source=".$source."&recno=".$rec."&rowid=".$rowid."&assign=edit&test_acc=1&conusername=".$_SESSION[conusername.$ACC_AS_SESSIONRN]."&madisonassid=".$assignid."&ACC_AS_SESSIONRN=".$ACC_AS_SESSIONRN.'&copyasign=yes&fromGigboard=yes');
				}
				else
				{
					Header("Location:editassign.php?moderate_type=".$moderate_type."&source=".$source."&recno=".$rec."&rowid=".$rowid."&assign=edit&test_acc=1&conusername=".$_SESSION[conusername.$ACC_AS_SESSIONRN]."&madisonassid=".$assignid."&ACC_AS_SESSIONRN=".$ACC_AS_SESSIONRN);
				}
				
			}
			else if($recassign[2] == "pending")
			{
				$_SESSION[fromAssign.$ACC_AS_SESSIONRN]="yes";
				if(isset($copyGig))
				{
					Header("Location:approveassignment.php?moderate_type=".$moderate_type."&recno=".$rec."&assign=edit&test_acc=1&conusername=".$_SESSION[conusername.$ACC_AS_SESSIONRN]."&ACC_AS_SESSIONRN=".$ACC_AS_SESSIONRN.'&copyasign=yes&fromGigboard=yes');
				}
				else
				{
					Header("Location:approveassignment.php?moderate_type=".$moderate_type."&recno=".$rec."&assign=edit&test_acc=1&conusername=".$_SESSION[conusername.$ACC_AS_SESSIONRN]."&ACC_AS_SESSIONRN=".$ACC_AS_SESSIONRN);
				}
				
			}
			else if($recassign[2] == "closed" || $recassign[2]=='cancelled' || $recassign[2]=='active')
			{
				$_SESSION[fromAssign.$ACC_AS_SESSIONRN]="yes";

				if(isset($copyGig))
				{
					Header("Location:editassign.php?moderate_type=".$moderate_type."&source=".$source."&recno=".$rec."&rowid=".$rowid."&assign=edit&test_acc=1&conusername=".$_SESSION[conusername.$ACC_AS_SESSIONRN]."&madisonassid=".$assignid."&ACC_AS_SESSIONRN=".$ACC_AS_SESSIONRN.'&copyasign=yes&fromGigboard=yes');
				}
				else
				{
					Header("Location:/include/closedassign.php?moderate_type=".$moderate_type."&recno=".$rec."&assign=edit&test_acc=1&hrsno=".$recassign[0]."&ACC_AS_SESSIONRN=".$ACC_AS_SESSIONRN);
				}
				
			}
		}
	}
	else
	{
		if($ptype != "")
			Header("Location: viewact.php?line=".$line."&con_id=".$con_id);
		else
			Header("Location: newconreg1.php?edeptname=$ename");
	}
?>