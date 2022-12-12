<meta http-equiv="X-UA-Compatible" content="IE=Edge"/>
<?php
	require("global.inc");
	require_once("dispfunc.php");
	require_once("multipleRatesClass.php");
	$ratesObj=new multiplerates();

	require_once("Menu.inc");
	$menu=new EmpMenu();
	$Supusr = superusername();
	$conusername = $_SESSION["conusername".$HRM_HM_SESSIONRN];
	$con_id="con".$recno;
	$hisdet="All";
	if($addr=="reload")
	{
		/* TLS-01202018 */
		$_SESSION["page15".$HRM_HM_SESSIONRN]=$tpage15;
		$addr="old";
	}

	if($assign=="edit")
	{
		
		$que_str="";
		$table="consultant";

		$cque="select sno,username,jotype,catid,project,refcode,posstatus,vendor,contact,client,manager,endclient,".tzRetQueryStringSTRTODate("s_date","%m-%d-%Y","Date","-").",". tzRetQueryStringSelBoxDate("exp_edate","YMDDate","-").",posworkhr,iterms,".tzRetQueryStringSTRTODate("e_date","%m-%d-%Y","Date","-").",reason,". tzRetQueryStringSelBoxDate("hired_date","YMDDate","-").",rate,rateper,rateperiod,'','',bamount,bcurrency,bperiod,pamount,pcurrency,pperiod,pterms,otrate,ot_currency,ot_period,placement_fee,placement_curr,jtype,bill_contact,bill_address,wcomp_code,imethod,tsapp,bill_req,service_terms,hire_req,notes,addinfo,avg_interview,burden,margin,markup,calctype,prateopen,brateopen,prateopen_amt,brateopen_amt,CONCAT_WS('^AKK^',".tzRetQueryStringDTime("cdate","Date","/").",cdate),otprate_amt,otprate_period,otprate_curr,otbrate_amt,otbrate_period,otbrate_curr,payrollpid,offlocation,double_prate_amt,double_prate_period,double_prate_curr,double_brate_amt,double_brate_period,double_brate_curr, po_num,department,job_loc_tax,date_placed,diem_lodging,diem_mie,diem_total,diem_currency,diem_period,diem_billable,diem_taxable,diem_billrate,deptid,attention,corp_code,industryid,schedule_display,bill_burden,worksite_code,daypay,licode,federal_payroll,nonfederal_payroll,contractid,classification,ts_layout_pref from ".$table."_jobs where sno='".$conjob_sno."'".$que_str;
		$cres=mysql_query($cque,$db);
		$crow=mysql_fetch_row($cres);
                $schedule_display = $crow[87];
                //retrieving  worksite code from the consultant table
                $worksitecode = $crow[89];
                $daypay = $crow[90];
				$licode = $crow[91];
				$federal_payroll = $crow[92];
				$nonfederal_payroll = $crow[93];
				$contractid = $crow[94];
				$classification = $crow[95];
				$assignment_timesheet_layout_preference = $crow[96];
		//if in case the value is empty then override it to old by default
		if($schedule_display == "" || $schedule_display == null)
		{
			$schedule_display = 'OLD';
		}
		
		//Condition for assignment type when it comes from closing placement
		if($crow[36]=="" && $crow[2]!="")
		{
			$typeOfAss=getManage($crow[2]);
			if($typeOfAss!="Internal Direct")
			    $crow[36]="OP";
			else
			    $crow[36]="AS";
		}
        
		if($crow[52]=='Y')
		    $payFinal="open";
		else
		    $payFinal="rate";
	
		if($crow[53]=='Y')
		    $billFinal="open";
		else
		    $billFinal="rate";
	
		$_SESSION["page15".$HRM_HM_SESSIONRN]=$crow[0]."|".$crow[1]."|".$crow[2]."|".$crow[3]."|".$crow[4]."|".$crow[5]."|".$crow[6]."|".$crow[7]."|".$crow[8]."|".$crow[9]."|".$crow[10]."|".$crow[11]."|".$crow[12]."|".$crow[13]."|".$crow[14]."|".$crow[15]."|".$crow[16]."|".$crow[17]."|".$crow[18]."|".$crow[19]."|".$crow[20]."|".$crow[21]."|hire||".$crow[24]."^^".$crow[26]."^^".$crow[25]."|".$crow[27]."^^".$crow[29]."^^".$crow[28]."|".$crow[48]."|".$crow[49]."|".$crow[50]."|".$crow[51]."^^".$payFinal."^^".$billFinal."^^".$crow[54]."^^".$crow[55]."|".$crow[30]."|".$crow[31]."|".$crow[32]."|".$crow[33]."|".$crow[34]."|".$crow[35]."|".$crow[36]."|".$crow[37]."|".$crow[38]."|".$crow[39]."|".$crow[40]."|".$crow[41]."|".$crow[42]."|".$crow[43]."|".$crow[44]."|".$crow[45]."|".$crow[46]."|".$crow[47]."|".$crow[0];
		
		//Query for getting comission values
		$quec		= "SELECT
					person, IF(co_type!='' AND comm_calc!='',FORMAT(amount,2),'') AS amount, co_type, comm_calc, type, roleid, overwrite, enableUserInput
				FROM
					assign_commission
				WHERE
					assignid = '".$crow[0]."'
					AND assigntype = 'C'
				ORDER BY
					sno";
		$cresc		= mysql_query($quec, $db);
		$numRowscomm	= mysql_num_rows($cresc);
	
		$comSnos	= "";
		$comVals	= "";
		$comRate	= "";
		$comFee		= "";
		$comRoleid	= "";
		$comOverwrite	= "";
		$roleEDisable	= "";
		
		$counter	= 0;
		$varConact	= ",";	
	
		while($crowc=mysql_fetch_row($cresc)) {
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
				$roleEDisable	= $crowc[7];
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
				$roleEDisable	.= $varConact.$crowc[7];
			}		
		}
	
		$numRowscomm1	= $numRowscomm-1;
	
		if($numRowscomm == "0" || $numRowscomm == "")
			$statusComm	= "";
		else
			$statusComm	= "nav";
		
		$mode_rate_type		= "consultant";
		$moderate_type		= "consultant"; //To fetch the values in assignment.php, we are using this.
		$type_order_id		= $crow[0];
		$assignment_rates	= $ratesObj->displayMultipleRates();
		$ratesDefaultArr	= $ratesObj->getDefaultMutipleRates();

		$_SESSION["assignment_mulrates".$HRM_HM_SESSIONRN]	= $assignment_rates."^DefaultRates^";

		//Appending the comission values
		$_SESSION["page15".$HRM_HM_SESSIONRN]	.= "|".$statusComm."|".$numRowscomm1."|".$comSnos."|".$crow[57]."|".$crow[58]."|".$crow[59]."|".$crow[60]."|".$crow[61]."|".$crow[62]."|".$crow[63]."|".$crow[56]."|".$crow[64]."|".$crow[65]."|".$crow[66]."|".$crow[67]."|".$crow[68]."|".$crow[69]."|".$crow[70]."|".$crow[71]."|".$crow[72]."|".$crow[73]."|".$crow[74]."|".$crow[75]."|".$crow[76]."|".$crow[77]."|".$crow[78]."|".$crow[79]."|".$crow[80]."|".$crow[81]."||".$crow[82]."||".$ratesDefaultArr['Regular'][0]."|".$ratesDefaultArr['Regular'][1]."|".$ratesDefaultArr['OverTime'][0]."|".$ratesDefaultArr['OverTime'][1]."|".$ratesDefaultArr['DoubleTime'][0]."|".$ratesDefaultArr['DoubleTime'][1]."||".$crow[83]."|".$crow[84]."|".$crow[85]."|".$crow[86]."|".$schedule_display."|".$crow[88]."|".$crow[91];
		
		$commissionValues	= $comVals."|".$comRate."|".$comFee."||".$comRoleid."|".$comOverwrite."|".$roleEDisable;
		$_SESSION['commission_session'.$HRM_HM_SESSIONRN]	= $commissionValues;

		//session_register("assignment_mulrates".$HRM_HM_SESSIONRN);
		$elements	= explode("|",$_SESSION[page15.$HRM_HM_SESSIONRN]);
	}
	else
	{
		$_SESSION["assignment_mulrates".$HRM_HM_SESSIONRN] = "";
		$schedule_display = 'OLD';
		//Assign the shift scheduling display stattus new shift scheduling is enabled
		if(SHIFT_SCHEDULING_ENABLED == 'Y') 
		{
			$schedule_display = 'NEW';
		}
	}

	function sel($a,$b)
	{
		if($a==$b)
			return "checked";
		else
			return "";
	}

	function sele($a,$b)
	{
		if($a==$b)
			return "selected";
		else
			return "";
	}

	$assid="";
	if($assign=="edit")
	{
		$assid=$assid1;
	}
	else
	{
		// below code is to display ASSID for timesheet/expenses.
		$query_closed="select pusername from consultant_jobs where username='".$_SESSION["conusername".$HRM_HM_SESSIONRN]."' and jtype='OP' and sno='".$conjob_sno."'";

		$result_closed=mysql_query($query_closed,$db);
		while($row_closed=mysql_fetch_row($result_closed))
		{
			if($closed_assids=="")
			    $closed_assids=$row_closed[0];
			else
			    $closed_assids.="','".$row_closed[0];
		}
	
		$query="select pusername from consultant_jobs where username='".$_SESSION["conusername".$HRM_HM_SESSIONRN]."' and pusername not in ('".$closed_assids."') and jtype='OP' and sno='".$conjob_sno."' group by username";
	
		$result=mysql_query($query,$db);
		$rows=mysql_num_rows($result);
		$close_flag=0;
		if($rows!=0)
		{
			$close_flag=1;
			$row=mysql_fetch_row($result);
			$assid=$row[0];
			if($row[0]=="")
			{
				$query_new="select max(sno) from consultant_jobs";
				$result_new=mysql_query($query_new,$db);
				$rows_new=mysql_num_rows($result_new);
				if($rows_new!=0)
				{
					$row_new=mysql_fetch_row($result_new);
					$newid=$row_new[0]+1;
					$assid="ASS".$newid;
				}
				else
				{
					$assid="ASS1";
				}
			}
		}
		else
		{
			$close_flag=0;
			$query_new="select max(sno) from consultant_jobs";
			$result_new=mysql_query($query_new,$db);
			$rows_new=mysql_num_rows($result_new);
			if($rows_new!=0)
			{
				$row_new=mysql_fetch_row($result_new);
				$newid=$row_new[0]+1;
				$assid="ASS".$newid;
			}
			else
			{
			   $assid="ASS1";
			}
		}
	}

	// end for ASSID
	$queryc="select count(1) from consultant_list where lstatus != 'DA' and estatus='update' and roles like '%+$eeid%' and username='".$_SESSION["conusername".$HRM_HM_SESSIONRN]."'";
	$resultc=mysql_query($queryc,$db);
	$rowsc=mysql_fetch_row($resultc);
	
	$spl_Attribute = (PAYROLL_PROCESS_BY_MADISON=='MADISON') ? 'udCheckNull ="YES" ' : '';
?>
<html>
<head>
<title>Hiring Management</title>
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/tab.css">
<script src=/BSOS/scripts/tabpane.js></script>
<script language=javascript src=/BSOS/scripts/validatehresume.js></script>
<script language=javascript src=/BSOS/scripts/validatehhr.js></script>
<script language=javascript src=scripts/validateasn.js></script>
<script language=javascript src="/BSOS/scripts/jquery-min.js"></script>
<script>
	var madison = '<?=PAYROLL_PROCESS_BY_MADISON;?>';
	var syncHRDefault = '<?php echo DEFAULT_SYNCHR; ?>';
	var akkupayroll = '<?php echo DEFAULT_AKKUPAY; ?>';
</script>
<?php
if(PAYROLL_PROCESS_BY_MADISON=='MADISON')
	echo "<script language=javascript src=/BSOS/scripts/formValidation.js></script>";
?>
<script language=javascript>
function doStatus(val)
{
	form=document.conreg;
	var hrmhmsessionrn=form.hrmhmsessionrn.value;
	form.action="redirectassignment.php?HRM_HM_SESSIONRN="+hrmhmsessionrn;
	form.submit();
}
function doJobChange()
{
   form=document.conreg;
   if(form.cstatus.value!=0)
   {
        alert("Employee is under approval state,you can't close the Assignment'");
        form.astatus.options[0].selected=true;

   }
}
function doChkClose(val)
{
  form=document.conreg;
  var chk,status;
  chk=form.closechk.value;

   if(chk==1 && val==1)
   {
        alert("Please Change the Status to Closed and Update.");
        form.astatus.focus();
   }
}
</script>

 <style>
.timegrid{ width:2.042% !important}
.smdaterowclass td, .timehead{ font-size:11px !important;}
.cdfAutoSuggest select{min-width: 250px !important;}
.cdfJoborderBlk .select2-container, .cdfJoborderBlk .select2-drop, .cdfJoborderBlk .select2-search, .cdfJoborderBlk .select2-search input {
    width: 250px !important;
}
/* #multipleRatesTab .crmsummary-jocomp-table select, #multipleRatesTab .crmsummary-jocomp-table input{width: 100px !important;min-width: 100px !important;} */
@media screen\0 {	
	/* IE only override */
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
.smdaterowclass td, .timehead{ font-size:11px !important;}
.managesymb { padding-top: 3px !important;}
}

@media screen and (-webkit-min-device-pixel-ratio:0){ 
    /* Safari only override */
     ::i-block-chrome,.timegrid {width:2.08% !important; }
	 ::i-block-chrome,.timehead { width:3.5% !important; }  
	 ::i-block-chrome,.sstabelwidth { width:99.52% !important; }  
	 
}

.managesymb { margin: 2px 4px !important; }
.closebtnstyle{ float:left; margin-top:1px; *margin-top:3px; vertical-align:middle; }


.modalDialog_contentDiv{
    height: 300px !important;
    left: 50% !important;
    margin-left: -350px !important;
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

#modal-wrapper{
	width: 950px !important;
}
.sspadnew table td{
	padding: 0.5rem !important;
}
#attribute-selector .scroll-area{
	width: 950px !important;
}

</style>
        


</head>
<body>
<?php
if(count($elements)==1)
{?>

	<table width=99% cellpadding=0 cellspacing=0 border=0>
	<div id="content">
	<tr>
		<td colspan="2">
		<table width=99% cellpadding=0 cellspacing=0 border=0>
    		<tr>
    			<td colspan=2><font class=bstrip>&nbsp;</font></td>
    		</tr>
    		<tr>
    			<td colspan=2><font class=modcaption>&nbsp;&nbsp;Assignments</font></td>
     		</tr>
    		<tr>
    			<td colspan=2><font class=bstrip>&nbsp;</font></td>
    		</tr>
		</table>
		</td>
	</tr>
	</div>

	<div id="topheader">
	<tr>
	<?php
		$name=explode("|","close.gif~Close");
		$link=explode("|","javascript:window.close()");
		$heading="user.gif~Assignments";
		$menu->showHeadingStrip1($name,$link,$heading);
	?>
	</tr>
	</div>

	<div id="grid_form">
	<?php
	$query="select jtype,notes from empcon_jobs where username='".$username."'";
	$res=mysql_query($query,$db);
	$row=mysql_fetch_row($res);

		if($elements[0]=='AS')
		$status='Administrative Staff';
		else if($elements[0]=='OB')
			$status='On Bench';
		else if($elements[0]=="OV")
			$status='On Vacation';
		else
		$status="Not Assigned";

	?>
	<tr class=tr2bgcolor width="98%">
		<td width="20%" ><font class=afontstyle>&nbsp;Assignment Type</font></td>
		<td><font class=afontstyle>&nbsp;<?php echo $status;?></font></td>
	</tr>
	<tr class=tr1bgcolor width="98%">
    	<td width="20%"><font class=afontstyle>&nbsp;Notes&nbsp;</font></td>
    	<td>&nbsp;<font class=afontstyle><?php echo $assnotes;?></font></td>
	</tr>
	<div id="botheader">
	<tr>
	<?php
		$name=explode("|","close.gif~Close");
		$link=explode("|","javascript:window.close()");
		$heading="user.gif~Assignments";
		$menu->showHeadingStrip1($name,$link,$heading);
	?>
	</tr>
	</div>
	<?php

}
else
{
?>
<form method=post name=conreg id=conreg action=redirectassignment.php>
<input type=hidden name=assign id="assign" value='<?php echo $assign; ?>'>
<input type=hidden name=uappno value='<?php echo $appno; ?>'>
<input type=hidden name=url>
<input type=hidden name=dest>
<input type=hidden name=daction value='storeresume.php'>
<input type=hidden name=page15<?php echo $HRM_HM_SESSIONRN; ?> value='<?php echo dispTextdb($_SESSION["page15".$HRM_HM_SESSIONRN]);?>'>
<input type=hidden name=page13<?php echo $HRM_HM_SESSIONRN; ?> value='<?php echo dispTextdb($_SESSION["page13".$HRM_HM_SESSIONRN]);?>'>
<input type=hidden name=page215<?php echo $HRM_HM_SESSIONRN; ?> value='<?php echo $_SESSION["page215".$HRM_HM_SESSIONRN];?>'>
<input type=hidden name=addr value="<?php echo $addr;?>">
<input type=hidden name=cstatus value="<?php echo $rowsc[0];?>">
<input type=hidden name=assignstatus>
<input type=hidden name=hireempname value="<?php echo dispTextdb($hireemployee_name);?>">
<input type=hidden name=hrmhmsessionrn id=hrmhmsessionrn value="<?php echo $HRM_HM_SESSIONRN; ?>">
<input type="hidden" name="theraphySourceEnable" id="theraphySourceEnable" value="<?php echo THERAPY_SOURCE_ENABLED;?>" />
<?php
	$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todate=date("m/d/Y",$thisday);
?>
<input type=hidden name=dateval value="<?php echo $todate;?>">
<input type=hidden name=currentdate value="<?php echo $todate;?>">
<input type="hidden" name='Supuser' value="<?php echo $Supusr;?>">
<input type="hidden" name='usernme' value="<?php echo $_SESSION["conusername".$HRM_HM_SESSIONRN];?>">
<input type=hidden name=hdnassign id=hdnassign value='<?php echo $assign; ?>'>
<input type=hidden name="sm_form_data<?php echo $HRM_HM_SESSIONRN; ?>" id="sm_form_data<?php echo $HRM_HM_SESSIONRN; ?>" value="">
<input type="hidden" name="sm_enabled_option<?php echo $HRM_HM_SESSIONRN; ?>" id="sm_enabled_option<?php echo $HRM_HM_SESSIONRN; ?>" value="<?php echo $schedule_display; ?>" />
<input type=hidden name=hrcon_recno id=hrcon_recno value='<?php echo $conjob_sno; ?>'>
<div id="main">
<td valign=top align=center>
<table width=99% cellpadding=0 cellspacing=0 border=0>
	<div id="content">
	<tr>
		<td>
		<table width=99% cellpadding=0 cellspacing=0 border=0>
		<tr>
			<td colspan=2><font class=bstrip>&nbsp;</font></td>
		</tr>
		<tr>
			<td><font class=modcaption>&nbsp;&nbsp;<?php echo dispTextdb($hireemployee_name); ?></font></td>
		</tr>
		<tr>
			<td colspan=2><font class=bstrip>&nbsp;</font></td>
		</tr>
		</table>
		</td>
	</tr>
	<?php
	if($ustat=="success")
		print "<tr><td><font class=afontstyle4>&nbsp;Employee Assignments have been updated Sucessfully.</font></td></tr>";

	if($error == "E")
	{
		print "<tr><td><font class=afontstyle4>&nbsp;Already Expenses has been submitted by this Employee. You Can not change the Customer at this time.</font></td></tr>";
	}
	else if($error == "T")
	{
		print "<tr><td><font class=afontstyle4>&nbsp;Already Timesheets has been submitted by this Employee. You Can not change the Customer at this time.</font></td></tr>";
	}

	?>
	</div>

	<div id="grid_form">
    <?php
        $modfrom="hiring";
        $compenTabvalues=explode("|",$_SESSION["page13".$HRM_HM_SESSIONRN]);
        $compenLocationId = $compenTabvalues[3];
	$company_sno = $compenTabvalues[59];
	$candidateName=stripslashes($hireemployee_name);
    $pos_que="select posid,pusername,username,shift_type from consultant_jobs where sno='".$conjob_sno."'";
    $pos_res=mysql_query($pos_que,$db);
    $pos_row=mysql_fetch_row($pos_res);
    $refer_chk_row = $pos_row;// refer_bonus_manage
    $jobPosVal = $pos_row[0];
	if($assign!="edit")
	{
		$assignmentStatus = "newassignment";	
		$mode="newassign";
		//$elements[36] = "OP";
		$_SESSION["HRHM_Assgschedule".$HRM_HM_SESSIONRN]=""; //If we are creating new assignement, making schedule session null 
		if(PAYROLL_PROCESS_BY_MADISON == "MADISON")
			$elements[21] = "HOUR";
	}	
	else
	{
		$assignmentStatus = "editassignment";	
		$mode="editassign";
	}	
	$assg_disable="";
	$showAssignid=$pos_row[1];
	$_SESSION["commission_session".$HRM_HM_SESSIONRN]	= $commissionValues;
	$mod = 8;
	$apprn = $HRM_HM_SESSIONRN;
	$assignment_mulrates = $_SESSION["assignment_mulrates".$HRM_HM_SESSIONRN];
            
        // Referral Bonus Manage
        $referral_bonus_exists ='';
        $que_ref="SELECT bonus_amount FROM cand_refer WHERE ref_id ='".$refer_chk_row[2]."' AND req_id ='".$refer_chk_row[0]."'";
        $res_ref=mysql_query($que_ref,$db);
        $fetch_ref_recs=  mysql_num_rows($res_ref);
        if($fetch_ref_recs>0){
           $referral_bonus_exists='YES';
           $ref_bonus_row=mysql_fetch_row($res_ref);
        }
        
	require($app_inc_path."assignment.php");
    ?>
	</div>
</table>
</td>
</div>
</form>
<?php
}
?>
</body>
</html>
