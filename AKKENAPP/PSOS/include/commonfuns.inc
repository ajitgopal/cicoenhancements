<?php 
require_once("class.DepartmentAccess.inc"); 
function entitiesSelBox($selValue)
{
	global $maildb,$db;
	$query = "select staffacc_cinfo.cname,staffacc_cinfo.username, ".getEntityDispName('staffacc_cinfo.sno', 'staffacc_cinfo.cname', 1)." from staffacc_cinfo, staffacc_list WHERE staffacc_list.username = staffacc_cinfo.username AND staffacc_list.status = 'ACTIVE' AND staffacc_cinfo.type IN ('CUST', 'BOTH') order by staffacc_cinfo.cname";

	$sqlCustomers = mysql_query($query,$db);
	if(@mysql_num_rows($sqlCustomers) > 0)
	{
		print '<optgroup label="Customers" style="text-decoration:none; font-style:normal">';
		while($row = mysql_fetch_array($sqlCustomers))
		{
			print "<option value='".$row['username']."' ".optionselectedcheck($row['username'], $selValue).">".html_tls_entities(stripslashes($row[2]), ENT_QUOTES)."</option>";
		}
		print '</optgroup>';
	}

	$query = "select emp_list.name,emp_list.username, ".getEntityDispName('emp_list.sno', 'emp_list.name', 1)." from emp_list, hrcon_w4 WHERE emp_list.lstatus NOT IN('DA','INACTIVE') AND emp_list.username=hrcon_w4.username AND hrcon_w4.tax != 'C-to-C' AND hrcon_w4.ustatus = 'active'  order by emp_list.name";
	
	$sqlEmp = mysql_query($query, $db);	
	if(mysql_num_rows($sqlEmp) > 0)
	{
		print '<optgroup label="Employees" style="text-decoration:none; font-style:normal">';
		while($row = mysql_fetch_array($sqlEmp))
		{
			print "<option value='".$row['username']."' ".optionselectedcheck($row['username'], $selValue).">".html_tls_entities(stripslashes($row[2]), ENT_QUOTES)."</option>";			
		}
		print '</optgroup>';
	}	

	$query = "select reg_payee.name, reg_payee.sno, ".getEntityDispName('vendors.sno', 'reg_payee.name', 1)." from reg_payee,vendors where vendors.vendorid = reg_payee.sno AND vendors.vendortype = 'GV' AND reg_payee.status='ER' order by reg_payee.name";
	$sqlGenVendor = mysql_query($query, $db);
	if(mysql_num_rows($sqlGenVendor) > 0)
	{
		print '<optgroup label="General Vendors" style="text-decoration:none; font-style:normal">';
		while($row=mysql_fetch_array($sqlGenVendor))
		{
			$gvid = 'PE'.$row['sno'];
			print "<option value='".$gvid."' ".optionselectedcheck($gvid, $selValue).">".html_tls_entities(stripslashes($row[2]), ENT_QUOTES)."</option>";
		}
		print '</optgroup>';
	}
	
	$query = "SELECT staffacc_cinfo.cname, staffacc_cinfo.username, ".getEntityDispName('vendors.sno', 'staffacc_cinfo.cname', 1)." FROM staffacc_cinfo,staffacc_list,vendorsubcon,emp_list,hrcon_w4,vendors WHERE vendors.vendorid = staffacc_cinfo.sno AND vendors.vendortype = 'CV' AND staffacc_list.username = staffacc_cinfo.username AND vendorsubcon.venid=staffacc_cinfo.username AND emp_list.username= vendorsubcon.empid AND emp_list.username=hrcon_w4.username AND hrcon_w4.tax = 'C-to-C' AND hrcon_w4.ustatus = 'active' AND emp_list.lstatus NOT IN ('INACTIVE','DA') AND emp_list.empterminated != 'Y' group by vendorsubcon.venid";

	$sqlCVVendors = mysql_query($query, $db);
	if(mysql_num_rows($sqlCVVendors) > 0)
	{
		
		print '<optgroup label="Consulting Vendors" style="text-decoration:none; font-style:normal">';
		while($row = mysql_fetch_array($sqlCVVendors))
		{
			$cvid = $row['username'].'-CV';
			print "<option value='".$cvid."' ".optionselectedcheck($cvid, $selValue).">".html_tls_entities(stripslashes($row[2]), ENT_QUOTES)."</option>";
		}
		print '</optgroup>';
	}
	
	
	$query = "select emp_list.name,emp_list.username, ".getEntityDispName('vendors.sno', 'emp_list.name', 1)." from emp_list, hrcon_w4, vendors WHERE vendors.vendorid = emp_list.sno AND vendors.vendortype = 'CT' AND emp_list.lstatus NOT IN('DA','INACTIVE') AND emp_list.username=hrcon_w4.username AND hrcon_w4.tax = '1099' AND hrcon_w4.ustatus = 'active'  order by emp_list.name";
	
	$sqlContVendor = mysql_query($query,$db);	
	if(mysql_num_rows($sqlContVendor) > 0)
	{
		print '<optgroup label="Consultants" style="text-decoration:none; font-style:normal">';
		while($row = mysql_fetch_array($sqlContVendor))
		{
			print "<option value='".$row['username']."' ".optionselectedcheck($row['username'], $selValue).">".html_tls_entities(stripslashes($row[2]), ENT_QUOTES)."</option>";			
		}
		print '</optgroup>';
	}	 
	
}

function entityRefSeperation($payee)
{
	global $maildb,$db;
	$entitysno  = 0;
	/* TLS-01202018 */
	$result = array();
	if(eregi("(acc[0-9]*)-(CV)", $payee))
	{
		$source = 'CV';
		$temppayee = explode('-',$payee);
		$getClientSno = "SELECT sno FROM staffacc_cinfo WHERE username = '".$payee."'";
		$resCleintSno = mysql_query($getClientSno,$db);
		$rowClientSno = mysql_fetch_array($resCleintSno);		
		$payee = $temppayee[0];
		$entitysno = $rowClientSno[0];	//substr($payee, 3);
	}
	elseif(eregi("(acc[0-9])", $payee))
	{
		$source = 'CUST';
		$getClientSno = "SELECT sno FROM staffacc_cinfo WHERE username = '".$payee."'";
		$resCleintSno = mysql_query($getClientSno,$db);
		$rowClientSno = mysql_fetch_array($resCleintSno);
		$entitysno = $rowClientSno[0];	//substr($payee, 3);
	}
	elseif(eregi("(PE[0-9])", $payee))
	{
		$source = 'GV';
		$getClientSno = "SELECT sno FROM reg_payee WHERE username = '".$payee."'";
		$resCleintSno = mysql_query($getClientSno,$db);
		$rowClientSno = mysql_fetch_array($resCleintSno);
		$entitysno = $rowClientSno[0];	//substr($payee, 2);
	}
	else
		$source = 'EMP';
		
	$result[] = $payee;
	$result[] = $source;
	$result[] = $entitysno;
	return $result;
}

function optionselectedcheck($optionvalue, $selectedvalue)
{
	return ($optionvalue == $selectedvalue ? 'selected' : '');
}

// This function will be useful for returning Pay setup details, we can return different details based on $rtnType variable. Currently it will display only working hours.
function getPaySetupDetails($rtnType = 'hours')
{
	global $maildb,$db;
	$queryPaySetup = "SELECT stdhours, paydays FROM cpaysetup WHERE status='ACTIVE'";
	$sqlPaySetup = mysql_query($queryPaySetup,$db);
	$rowPaySetup = mysql_fetch_assoc($sqlPaySetup);
	if($rtnType == 'hours')
		return $workHours = number_format(number_format($rowPaySetup['stdhours'],2,'.','')/number_format($rowPaySetup['paydays'],2,'.',''),2,'.','');
		
}

// This function will be calculate Hours based on period, $workHours considered based on Payroll setup
function calcTimesheetHoursAmount($period, $amount, $hours, $workHours)
{
	$totalAmount = 0;
	if($period == "YEAR")
		$rate= $amount/($workHours*261);
	elseif($period == "MONTH")
		$rate= $amount/($workHours*(261/12));
	else if($period == "WEEK")
		$rate= $amount/($workHours*5);
	elseif($period == "DAY")
		$rate= $amount/$workHours;
	else
		$rate= $amount;

	if($period == "FLATFEE")
		$totalAmount = $amount;
	else
		$totalAmount = number_format($hours,2,'.','') * number_format($rate,2,'.','');
		
	return 	number_format($totalAmount,2,'.','');
}

function getClassParentName($parid, $name)
{
	global $maildb,$db, $selClassTitle;

	$getChild = "SELECT sno, classname, IFNULL(parent,0) AS parent FROM class_setup WHERE status = 'ACTIVE' AND sno = ".$parid;
	$resChild = mysql_query($getChild,$db);	
	if(mysql_num_rows($resChild) > 0)
	{	
		while($rowChild = mysql_fetch_array($resChild))
		{
			$selClassTitle = html_tls_entities($rowChild['classname'], ENT_QUOTES).':'.$name;
			if($rowChild['parent'] != 0)
			{		
				getClassParentName($rowChild['parent'], $selClassTitle);
			}
		}		
	}	
	
	//return $rname;
}	
	
function getClassesSetups($cond = '')
{
	global $maildb,$db, $selClassTitle;
	$aryClasses = array();
	$queryClass = "SELECT sno, classname, IFNULL(parent,0) AS parent FROM class_setup WHERE status = 'ACTIVE' ".$cond." ORDER BY classname ASC"; 		
	$sqlClass = mysql_query($queryClass, $db);
	if(mysql_num_rows($sqlClass) > 0)
	{
		while($rowClass = mysql_fetch_array($sqlClass))
		{				
			$dispName = $selClassTitle = html_tls_entities($rowClass['classname'], ENT_QUOTES);
			
			if($rowClass['parent'] != 0)	
				getClassParentName($rowClass['parent'], $dispName);		
		
			$aryClasses[$rowClass['sno']]['Name'] = $dispName;
			$aryClasses[$rowClass['sno']]['Title'] = $selClassTitle;
			
			$selClassTitle = '';			
		}		
	}
	
	return $aryClasses;
}

function classSelBox($aryClasses, $selName, $selValue,$disbled='')
{
	echo "<select name='$selName' id='$selName' class='drpdwnacc form-select w-250' {$disbled}>";
	if(count($aryClasses) > 0)
	{
		echo '<option value="0" selected>--Select--</option>';
		foreach($aryClasses as $key => $val)
		{
			$selected = ($key == $selValue) ? 'selected' : '';
			echo '<option value="'.$key.'" title="'.$val['Title'].'" '.$selected.'>'.$val['Name'].'</option>';
		}
	}
	else
	{
		echo '<option value="0">No Classes available</option>';
	}
	
	echo '</select>';
}

function classSelBoxCst($aryClasses, $selName, $selValue, $selClass)
{
	echo '<select name="'.$selName.'" id="'.$selName.'" class="drpdwnacc form-select '.$selClass.'">';
	if(count($aryClasses) > 0)
	{
		echo '<option value="0" selected>--Select--</option>';
		foreach($aryClasses as $key => $val)
		{
			$selected = ($key == $selValue) ? 'selected' : '';
			echo '<option value="'.$key.'" title="'.$val['Title'].'" '.$selected.'>'.$val['Name'].'</option>';
		}
	}
	else
	{
		echo '<option value="0">No Classes available</option>';
	}
	
	echo '</select>';
}



function clsSelBoxRtn($aryClasses, $selName, $selValue, $selClassName='drpdwnacc', $otherArgs='')
{
	$selBoxVal = '<select name="'.$selName.'" id="'.$selName.'" class="'.$selClassName.'" '.$otherArgs.'>';
	
	if(count($aryClasses) > 0)
	{
		$selBoxVal .= '<option value="0">--Select--</option>';
		foreach($aryClasses as $key => $val)
		{
			$selected = ($key == $selValue) ? 'selected' : '';
			$selBoxVal .= '<option value="'.$key.'" title="'.$val['Title'].'" '.$selected.'>'.$val['Name'].'</option>';
		}
	}
	else
	{
		$selBoxVal .= '<option value="0">No Classes available</option>';
	}
	
	$selBoxVal .= '</select>';
	
	return $selBoxVal;
}
//In timesheets  Type drop list for rates...
function getMultipleRates($hrconsno,$db)
{
	$MultipleRates  = "";
	if(trim($hrconsno) == '')
	{
		$getMultipleRates = "SELECT multiplerates_master.rateid, multiplerates_master.name, 'N','0' FROM multiplerates_master WHERE multiplerates_master.status = 'ACTIVE' AND multiplerates_master.default_status = 'Y' AND multiplerates_master.rateid != 'rate4' ORDER BY multiplerates_master.sno";
	}
	else
	{
		$getMultipleRates = "SELECT multiplerates_master.rateid, multiplerates_master.name, multiplerates_assignment.billable, hrcon_jobs.classid FROM hrcon_jobs,multiplerates_master,multiplerates_assignment WHERE hrcon_jobs.sno = '".$hrconsno."' AND hrcon_jobs.sno = multiplerates_assignment.asgnid AND multiplerates_assignment.status = 'ACTIVE' AND multiplerates_master.status = 'ACTIVE' AND multiplerates_assignment.ratemasterid != 'rate4' AND multiplerates_assignment.ratemasterid = multiplerates_master.rateid AND multiplerates_assignment.asgn_mode = 'hrcon' GROUP BY multiplerates_assignment.ratemasterid ORDER BY multiplerates_master.sno";
	}
	
	$resMultipleRates = mysql_query($getMultipleRates,$db);
	while($rowMultipleRates = mysql_fetch_row($resMultipleRates))
	{
		if($MultipleRates  == '')
			$MultipleRates = $rowMultipleRates[0]."|".$rowMultipleRates[1]."|".$rowMultipleRates[2]."|".$rowMultipleRates[3];
		else
			$MultipleRates .= "^".$rowMultipleRates[0]."|".$rowMultipleRates[1]."|".$rowMultipleRates[2]."|".$rowMultipleRates[3];
	}
	
	return $MultipleRates;
}

//getting parent departments
function getParent($dept_id,$deptname,$db)
{
	$dque = "SELECT sno,IF(depcode!='',CONCAT_WS(' - ', depcode, deptname),deptname),parent FROM department WHERE sno='".$dept_id."' AND status='Active' ORDER BY sno DESC";
	$dres = mysql_query($dque,$db);
	$drow = mysql_fetch_row($dres);
	
	if($drow[2] == "0")
		return $deptname." : ".$drow[1];
	else
	{
		$deptname=$deptname." : ".$drow[1];
		$x=getParent($drow[2],$deptname,$db);
		return $x;
	}
}

function departmentSelBox($selName, $selValue, $deptClass, $owner='', $NavFrom='',$onchange = '', $frmJO = '', $disable_sel='',$companyid='')
{
	global $maildb,$db,$username;
	$dept_user = $username;
	if($owner!='')
		$dept_user = $owner;
	
	$deptAccesSno = "";
	if($frmJO == 'yes'){
		$deptAccessObj = new departmentAccess();
		$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'FO'");
		$deptAccesSno = " AND dp.sno IN(".$deptAccesSno.") ";
		$permissionCondition = "";
	}
	else{
		if ($selName == "deptassignment") {
			$deptAccessObj = new departmentAccess();
			$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
			$deptAccesSno = " AND dp.sno IN(".$deptAccesSno.") ";
			$permissionCondition = "";
		}else{
			$deptAccessObj = new departmentAccess();
			$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'FO','BO'");
			$deptAccesSno = " AND dp.sno IN(".$deptAccesSno.") ";
			//$permissionCondition = "AND FIND_IN_SET('".$dept_user."',permission)>0";
			$permissionCondition = "";
		}
	}
	
	if(trim($selValue) == '' || $selValue == '0'){ 
		//if selected dept id not there, then take default department id.
		$sel_department = "SELECT sno FROM department WHERE deflt = 'Y'";
		$res_department =  mysql_query($sel_department,$db);
		$row_department =  mysql_fetch_row($res_department);
		$selValue = $row_department[0];
		$sel_department = "SELECT dp.sno ,dp.deptname,'' FROM staffoppr_cinfo  LEFT JOIN department dp ON staffoppr_cinfo.deptid=dp.sno WHERE  dp.status ='Active' and staffoppr_cinfo.sno={$companyid} ";
		$res_department =  mysql_query($sel_department,$db);
		$row_department =  mysql_fetch_row($res_department);
		$selValue = $row_department[0];
		if (AKKUPAY_WITH_SYMMETRY == "Y") {

			if ($companyid !="") {
				$sel_department = "SELECT dp.sno,dp.deptname,'' FROM department dp  WHERE  dp.status ='Active'  ".$permissionCondition.$deptAccesSno ;
			}else{
				$sel_department = "SELECT dp.sno,dp.deptname,'' FROM department dp  WHERE  dp.status ='Active' ".$permissionCondition.$deptAccesSno ;
				
		
			}
		}else{
			$sel_department = "SELECT dp.sno,dp.deptname,'' FROM department dp WHERE dp.status ='Active'".$permissionCondition.$deptAccesSno ;
		}
		
	}
	else{
		if (AKKUPAY_WITH_SYMMETRY == "Y") {
			if ($companyid !="") {
				$sel_department = "SELECT dp.sno,dp.deptname,IF((dp.status = 'Active' ".$permissionCondition."),'Active','Deleted') FROM department dp JOIN company_setup cs ON dp.company_id = cs.sno WHERE dp.sno ='".$selValue."' OR (dp.status = 'Active' ".$permissionCondition.")   AND cs.sno={$companyid} ".$deptAccesSno;
			}else{
				$sel_department = "SELECT dp.sno,dp.deptname,IF((dp.status = 'Active' ".$permissionCondition."),'Active','Deleted') FROM department dp JOIN company_setup cs ON dp.company_id = cs.sno WHERE dp.sno ='".$selValue."' OR (dp.status = 'Active' ".$permissionCondition.") ".$deptAccesSno;
			}
		}else{
			if ($companyid !="") {
				$sel_department = "SELECT dp.sno,dp.deptname,IF((dp.status = 'Active' ".$permissionCondition."),'Active','Deleted') FROM department dp WHERE dp.sno ='".$companyid."' OR (dp.status = 'Active' ".$permissionCondition.") ".$deptAccesSno;
			}else{
				$sel_department = "SELECT dp.sno,dp.deptname,IF((dp.status = 'Active' ".$permissionCondition."),'Active','Deleted') FROM department dp WHERE dp.sno ='".$selValue."' OR (dp.status = 'Active' ".$permissionCondition.") ".$deptAccesSno;
			}
		}
	}
	$res_department =  mysql_query($sel_department,$db);
		
	if($NavFrom != '' && $NavFrom != 'CRMBulkUpdate' && mysql_num_rows($res_department ) <= 0)
	{
		if (AKKUPAY_WITH_SYMMETRY == "Y") {
			$sel_department = "SELECT dp.sno,dp.deptname,dp.company_id FROM department dp JOIN company_setup cs ON dp.company_id = cs.sno WHERE dp.deflt = 'Y' AND cs.sno={$companyid} ";
		}else{
			$sel_department = "SELECT dp.sno,dp.deptname FROM department dp WHERE dp.deflt = 'Y'";
		}
		$res_department =  mysql_query($sel_department,$db);
	}	
	
	$dept_selBox_opt="";
	if(mysql_num_rows($res_department ) > 0)
	{
		if($NavFrom == 'CRMBulkUpdate' || $NavFrom == 'jobplacecand')
			$dept_selBox_opt.='<option value="0" selected>--Select HRM Department--</option>';
			
		//$disable_sel = '';
		while($fetch_department =  mysql_fetch_row($res_department))
		{
			if($disable_sel == "" && $fetch_department[2]=='Deleted')
				$disable_sel = 'disabled';
				
			$selected = ($fetch_department[0] == $selValue && $NavFrom != 'CRMBulkUpdate') ? 'selected' : '';
			$dept_selBox_opt.='<option value="'.$fetch_department[0].'" title="'.html_tls_specialchars($fetch_department[1],ENT_QUOTES).'" '.$selected.'>'.html_tls_specialchars($fetch_department[1],ENT_QUOTES).'</option>';
		}
	}
	else
	{
		$dept_selBox_opt.='<option value="0">No Departments available</option>';
	}
    
	$dept_selBox = '<select name="'.$selName.'" id="'.$selName.'" class="'.$deptClass.'" '.$disable_sel.' '.$onchange.'>';
	$dept_selBox_opt.='</select>';
	
	if($disable_sel == 'disabled' && $selName == 'selAccDepartment')
		$dept_selBox_opt.="<input type=hidden id=$selName name=$selName value=$selValue>";
	
	echo $dept_selBox.$dept_selBox_opt;
} 

function departmentCompSymSelBox($selName, $selValue, $deptClass, $owner='', $NavFrom='',$onchange = '', $frmJO = '', $disable_sel='')
{
	global $maildb,$db,$username;
	$dept_user = $username;
	if($owner!='')
		$dept_user = $owner;
	
	$deptAccesSno = "";
	if($frmJO == 'yes'){
		$deptAccessObj = new departmentAccess();
		$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'FO'");
		$deptAccesSno = " AND sno IN(".$deptAccesSno.") ";
		$permissionCondition = "";
	}
	else{
		if ($selName == "deptassignment") {
			$deptAccessObj = new departmentAccess();
			$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
			$deptAccesSno = " AND sno IN(".$deptAccesSno.") ";
			$permissionCondition = "";
		}else{
			$deptAccessObj = new departmentAccess();
			$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'FO','BO'");
			$deptAccesSno = " AND sno IN(".$deptAccesSno.") ";
			//$permissionCondition = "AND FIND_IN_SET('".$dept_user."',permission)>0";
			$permissionCondition = "";
		}
	}
	
	if(trim($selValue) == '' || $selValue == '0'){
		
		//if selected dept id not there, then take default department id.
		$sel_department = "SELECT sno FROM department WHERE deflt = 'Y'";
		$res_department =  mysql_query($sel_department,$db);
		$row_department =  mysql_fetch_row($res_department);
		$selValue = $row_department[0];
		
		$sel_department = "SELECT sno,deptname,'' FROM department WHERE  status ='Active'".$permissionCondition.$deptAccesSno ;
	}
	else
		$sel_department = "SELECT sno,deptname,IF((status = 'Active' ".$permissionCondition."),'Active','Deleted') FROM department WHERE sno ='".$selValue."' OR (status = 'Active' ".$permissionCondition.") ".$deptAccesSno;
		
	$res_department =  mysql_query($sel_department,$db);
		
	if($NavFrom != '' && $NavFrom != 'CRMBulkUpdate' && mysql_num_rows($res_department ) <= 0)
	{
		$sel_department = "SELECT sno,deptname,company_id FROM department WHERE deflt = 'Y'";
		$res_department =  mysql_query($sel_department,$db);
	}	
	
	$dept_selBox_opt="";
	if(mysql_num_rows($res_department ) > 0)
	{
		if($NavFrom == 'CRMBulkUpdate' || $NavFrom == 'jobplacecand')
			$dept_selBox_opt.='<option value="0" selected>--Select HRM Department--</option>';
			
		//$disable_sel = '';
		while($fetch_department =  mysql_fetch_row($res_department))
		{
			if($disable_sel == "" && $fetch_department[2]=='Deleted')
				$disable_sel = 'disabled';
				
			$selected = ($fetch_department[0] == $selValue && $NavFrom != 'CRMBulkUpdate') ? 'selected' : '';
			$dept_selBox_opt.='<option value="'.$fetch_department[0].'" title="'.html_tls_specialchars($fetch_department[1],ENT_QUOTES).'" '.$selected.'>'.html_tls_specialchars($fetch_department[1],ENT_QUOTES).'</option>';
		}
	}
	else
	{
		$dept_selBox_opt.='<option value="0">No Departments available</option>';
	}
    
	
	$dept_selBox = '<select name="'.$selName.'" id="'.$selName.'" class="form-select w-250 '.$deptClass.'" '.$disable_sel.' onchange=getComWorks(this.value)>';
	
	$dept_selBox_opt.='</select>';
	
	if($disable_sel == 'disabled' && $selName == 'selAccDepartment')
		$dept_selBox_opt.="<input type=hidden id=$selName name=$selName value=$selValue>";
	
	echo $dept_selBox.$dept_selBox_opt;
}

function displayDepartmentName($deptSno,$classid=false)
{
	global $maildb,$db,$username;
	
	if($classid==false)
	{
		$sel_department = "SELECT deptname,classid FROM department WHERE sno='".$deptSno."'";
		$res_department =  mysql_query($sel_department,$db);
		$fetch_department =  mysql_fetch_row($res_department);
		
		if($fetch_department[0]!='')
			return html_tls_specialchars($fetch_department[0],ENT_QUOTES);
		else
			return "&nbsp;";	
	}
	else
	{
		$sel_department = "SELECT classid FROM department_accounts WHERE deptid='".$deptSno."' AND status='ACTIVE'";
		$res_department =  mysql_query($sel_department,$db);
		$fetch_department =  mysql_fetch_row($res_department);

		return $fetch_department[0];
	}		
}

// Removing Same Candidate, Contact, Company or Job Order is selected in search window in respctive summary screens.
function setSummaryNotedAssocIDS($idsList, $type, $id)
{
	
	if(is_null($idsList) || !isset($idsList))
		$idsList = '';		
	
	if($idsList != '')
	{						
		$tempArray = array();
		$tempContAssoc = explode(',', $idsList);
		foreach($tempContAssoc as $val)
		{
			if(($type.$id) != $val && $val != '')
				$tempArray[] = 'A'.$val.'A';
		}
		
		$idsList = implode(',', array_unique($tempArray));
		$idsList = str_replace($type,'',$idsList);
	}	
	
	return $idsList;
}


function assNotesRecordsAppend($row, $sourceID,$module)
{
	$NotesText1 = '';
	if($row['JobOrder'] != '' || $row['Contact'] != '' || $row['Company'] != '' || $row['Candidate'] != '')
	{
		if($row['Source'] != '')
		{
			$NotesText1.="<br/><b>Source:</b> ".$row['Source'];
		}
		
		$NotesTextAss = array();
		
		$despath="/BSOS/Sales/Req_Mngmt/notes_details.php?notesOpenFrom=Summary&sno=".$row['sno']."&module=".$module;										 
		$NotesText1.="<br/><a href=\"javascript:openewWindow('".$despath."','',550,300)\"> <i class='fa fa-user-secret'></i></a>&nbsp;";
		
		$NotesText1.= "...";					
	}
	
	return $NotesText1;
}

function assNoteJobOrderContacts($hdnJobAssoc, $hdnContAssoc, $contactid = 0)
{
	global $maildb,$db, $username;
	if($hdnJobAssoc != '' && !is_null($hdnJobAssoc))
	{
		$hdnJobAssoc1 = str_replace('A','',$hdnJobAssoc);
		
		$queryJob = "SELECT IFNULL(GROUP_CONCAT(DISTINCT CONCAT('A',contact,'A')),'') AS Contacts from posdesc WHERE contact != 0 AND IFNULL(contact,'') != '' AND posid IN ($hdnJobAssoc1)";
		if($contactid > 0)
			$queryJob.= " AND contact != '".$contactid."'";
			
		$sqlJobContact = mysql_query($queryJob, $db);
		
		if(mysql_num_rows($sqlJobContact) > 0)
		{
			$rowJobContact = mysql_fetch_assoc($sqlJobContact);
			if($rowJobContact['Contacts'] != '')
			{
				$hdnContAssoc.=($hdnContAssoc != '')? ','.$rowJobContact['Contacts'] : $rowJobContact['Contacts'];
				$hdnContAssoc = explode(',',$hdnContAssoc);
				$hdnContAssoc = implode(',',array_unique($hdnContAssoc));
			}
		}	
	}	

	return $hdnContAssoc;
}

####################################################
# Function createChkListDropDown
# inputParams associative array
# output checkbox list
# input array params
# elemid = id and name for the dropdown list
# options = array of options
# selected = slected elements in array
# style = width and maxdropHeight for the dropdownlist
# Created By : Piyush R
# Created Date : April 15, 2010
####################################################

function createChkListDropDown($arrValues){
	
	$str = $selected='';
	$elemId = $arrValues['elemid'];		# the id/name for the list box element
	$valArr = $arrValues['options'];	# the array of options to be displayed in the list box
	$selVal = $arrValues['selected'];   # selected element in the options array
	$style	=  $arrValues['style'];  	# style for the drop down list, width, maxdropHeight to be set
	
	if($arrValues['callpage'] == 'contactSummary'){
		$style.= ", width: 180";
		$drpDownStyle = (strpos($_SERVER['HTTP_USER_AGENT'],'Gecko') > -1)  ? "width: 180px" : "width: 140px";
	}else{
		$style.= ", width: 225";
		$drpDownStyle = (strpos($_SERVER['HTTP_USER_AGENT'],'Gecko') > -1)  ? "width: 245px" : "width: 225px";
	}	
	if($style==''){
		$style = 'width: 150, maxDropHeight: 80';
	}	

	if($drpDownStyle!=''){
		
		$styleX= "<style>";
		$styleX.= ".ui-dropdownchecklist{";
		$styleX.= $drpDownStyle;
		$styleX.= "}";	
		$styleX.= ".ui-dropdownchecklist-dropcontainer {";
		$styleX.= $drpDownStyle;
		$styleX.= "}";
		$styleX.= "</style>";
		
		echo $styleX;
	}
	
	/* $str='<script type="text/javascript">';
	$str .='$(document).ready(function() {';
	$str .='$("#'.$elemId.'").dropdownchecklist({'.$style.'});
		});
	 </script>';*/
	 
   $elemIdNew = ($arrValues['multiple'] == 'No') ? $elemId : $elemId.'[]' ;
   $optionDisableStatus = ($arrValues['crm_groups'] == "N") ? "disabled" : "";
   $groupPublicShare = '';	
   $str .="<select class='multiple-select' multiple name='{$elemIdNew}' id='{$elemId}'>";
	foreach($valArr as $key=>$value){
		if(array_key_exists($key,$selVal)){
			$selected="selected='selected'";
		}
			$groupvalue = @explode("|", $value);
			$str .="<option value='{$key}' {$selected} {$optionDisableStatus} label='{$groupvalue[0]}'>{$groupvalue[0]}</option>";
			$selected = "";			
			if($groupvalue[1] == 'Public' || $groupvalue[1] == 'Share')
				$groupPublicShare.= $key.',';
	}
	
	$str .= "</select> <input type='hidden' name='availableGroups' id='availableGroups' value='".trim($groupPublicShare,',')."'>";
	if($arrValues['callType'] != 'Category')
		$str .= "<input type='hidden' name='otherPrivateGroupIds' id='otherPrivateGroupIds' value='".$arrValues['otherPrivateGroupIds']."'>";
		
	echo $str;
}

####################################################
# Function createChkListDropDownForFilters
# inputParams associative array
# output checkbox list
# input array params
# elemid = id and name for the dropdown list
# options = array of options
# selected = slected elements in array
# style = width and maxdropHeight for the dropdownlist
# Created By : Piyush R
# Created Date : July 02, 2010
####################################################

function createChkListDropDownForFilters($arrValues){
	
	$str = $selected='';
	$elemId = $arrValues['elemid'];		# the id/name for the list box element
	$valArr = $arrValues['options'];	# the array of options to be displayed in the list box
	$selVal = $arrValues['selected'];   # selected element in the options array
	$style	=  $arrValues['style'];  	# style for the drop down list, width, maxdropHeight to be set
	
	if($arrValues['callpage'] == 'contactSummary'){
		$style.= ", width: 120";
		$drpDownStyle = (strpos($_SERVER['HTTP_USER_AGENT'],'Gecko') > -1)  ? "width: 140px" : "width: 120px";
	}else{
		$style.= ", width: 225";
		$drpDownStyle = (strpos($_SERVER['HTTP_USER_AGENT'],'Gecko') > -1)  ? "width: 245px" : "width: 225px";
	}	
	if($style==''){
		$style = 'width: 150, maxDropHeight: 80';
	}	

	if($drpDownStyle!=''){
		
		$styleX= "<style>";
		$styleX.= ".ui-dropdownchecklist{";
		$styleX.= $drpDownStyle;
		$styleX.= "}";	
		$styleX.= ".ui-dropdownchecklist-dropcontainer {";
		$styleX.= $drpDownStyle;
		$styleX.= "}";
		$styleX.= "</style>";
		
		echo $styleX;
	}
	
	$style = str_replace('firstItemChecksAll: false','firstItemChecksAll: true',$style);
	$str='<script type="text/javascript">';
	$str .='$(document).ready(function() {';
	$str .='$("#'.$elemId.'").dropdownchecklist({'.$style.'});
		});
	 </script>';
	 
   $elemIdNew = ($arrValues['multiple'] == 'No') ? $elemId : $elemId.'[]' ;
   $optionDisableStatus = ($arrValues['crm_groups'] == "N") ? "disabled" : "";
   $groupPublicShare = '';	
   $str .="<select class=drpdwne  multiple='multiple' name='{$elemIdNew}' id='{$elemId}'>";
	foreach($valArr as $key=>$value){
		if(array_key_exists($key,$selVal)){
			$selected="selected='selected'";
		}
			$groupvalue = @explode("|", $value);
			$str .="<option value='{$key}' {$selected} {$optionDisableStatus} label='{$groupvalue[1]}'>{$groupvalue[0]}</option>";
			$selected = "";			
			if($groupvalue[1] == 'Public' || $groupvalue[1] == 'Share')
				$groupPublicShare.= $key.',';
	}
	
	$str .= "</select> <input type='hidden' name='availableGroups' id='availableGroups' value='".trim($groupPublicShare,',')."'>";
	if($arrValues['callType'] != 'Category')
		$str .= "<input type='hidden' name='otherPrivateGroupIds' id='otherPrivateGroupIds' value='".$arrValues['otherPrivateGroupIds']."'>";
		
	return $str;
}

//To get new jquery design for groups in contact/candidate edit/summary screens...-prasadd
function getCRMGroupsDropListOptions($elementId,$grouptype,$currentGroupIds,$groupIdStr,$moduleFrom="CRM"){

	global $maildb,$db,$username;
	
	$groupIdStr = ($grouptype != "") ? $groupIdStr." AND grouptype='".$grouptype."'" : $groupIdStr;
	$currentGroupsStr = (trim($currentGroupIds) != "") ? " OR (sno IN (".$currentGroupIds."))" : "";
	$accessToStr = ($moduleFrom == "CRM") ? " AND ((owner='".$username."' OR FIND_IN_SET('".$username."', accessto )>0 OR accessto='ALL')".$currentGroupsStr.") " : "";

	$group_query = "SELECT sno, name, status, IF(crmgroups.accessto='ALL','Public',IF(crmgroups.accessto='$username','Private', IF(FIND_IN_SET('".$username."', accessto )>0, 'Share','OtherPrivate'))) as shareType FROM crmgroups WHERE status = 'ACTIVE' ".$accessToStr.$groupIdStr." ORDER BY name"; 
	// CHNAGED THE STATUS !='DELETED' TO ACTIVE FOR CANDIDATE REPORT  - JYOTHI - 10-05-2012

	$group_data=mysql_query($group_query,$db);
	$rowsCount = mysql_num_rows($group_data);
	
	$values = $selVal = $valArray = array();	
	$otherPrivateGroupIds = '';
	$selectRecCount = $recordsCount = $firstRecord = 0;
	while($row = mysql_fetch_array($group_data))
	{
		$sel = getSelMulti($currentGroupIds,$row[0]);
		if($row['shareType'] != 'OtherPrivate' || $moduleFrom == "Admin"){
		
			if($row['status'] == 'ACTIVE'){
				
				if($sel=='selected'){
					$selVal[$row[0]]='selected';
					$sel='';
					$selectRecCount++;
				}
				
				if($firstRecord == 0)
				{
					if($moduleFrom!='Report'){
						//$values["0"] = "All";
						$emptyText = "Select Groups";
						$firstRecord++;
					}else if($moduleFrom=='Report'){
						$values["0"] = "ALL";
						$emptyText = "ALL";
						$firstRecord++;
					}	
				}
				
				$recordsCount++;
				$values[$row[0]] = dispfdb($row[1])."|".$row[3];
		   }
		   else{
		   		$otherPrivateGroupIds.=$row[0].',';
			}	
				
		}elseif($sel=='selected'){
			$otherPrivateGroupIds.=$row[0].',';
		}
	}
	
	if($firstRecord == 0)
		$emptyText = "No Groups Available";

	$valArray['elemid'] = $elementId;
	$valArray['options'] = $values;
	$valArray['selected'] = $selVal;
	$valArray['crm_groups'] = CRM_GROUPS;
	if($moduleFrom!='Report'){
		$valArray['style'] = 'firstItemChecksAll: false, maxDropHeight: 100, emptyText: "'.$emptyText.'"';
	}elseif($moduleFrom=='Report'){
		$valArray['style'] = 'firstItemChecksAll: true, maxDropHeight: 100, emptyText: "'.$emptyText.'"';
	}	
	$valArray['otherPrivateGroupIds'] = trim($otherPrivateGroupIds,',');
	return $valArray;
}

//pass the contact/candidate sno and get the group names comma seperated-prasadd
function getCRMGroupNames($refId, $refType, $module = 'CRM'){

	global $maildb,$db, $username;
	$accessto = ($module == "CRM") ? " AND (crmgroups.owner='$username' OR FIND_IN_SET('$username', crmgroups.accessto )>0 OR crmgroups.accessto='ALL')" : "";
	$grp_query = "SELECT crmgroups.name FROM crmgroups,crmgroup_details WHERE status = 'ACTIVE' AND crmgroup_details.groupId = crmgroups.sno AND crmgroup_details.refId ='".$refId."' AND crmgroups.grouptype = '".$refType."' ".$accessto;
	$grp_res = mysql_query($grp_query,$db);
	$grp_cnt = mysql_num_rows($grp_res);
	$groups ='';
	if($grp_cnt > 0){
		while($grp_row = mysql_fetch_row($grp_res)){
			if($groups == ''){
					$groups = $grp_row[0];
			}else{
					$groups.=", ".$grp_row[0];
			}
		}
	}
	return $groups;
}
//pass the contact/candidate sno and get the group ids comma seperated-prasadd
function getCRMGroupIds($refId, $refType){

	global $maildb,$db;
	
	if($refId != "" && $refId != 0){
		$setgroup_concat_maxlenQry = "SET SESSION group_concat_max_len=1073740800";	////////////Setting max length of group_concat
		$setRes = mysql_query($setgroup_concat_maxlenQry,$db);
		
		$grp_query = "SELECT GROUP_CONCAT(crmgroup_details.groupid) FROM crmgroup_details, crmgroups WHERE crmgroups.sno = crmgroup_details.groupId AND crmgroup_details.refId ='".$refId."' AND crmgroups.grouptype = '".$refType."'";
		$grp_res = mysql_query($grp_query,$db);
		$groupIdsRow =mysql_fetch_row($grp_res);
		
		return $groupIdsRow[0];
	}
	else
		return 0;
}

//To Update the group details while contact/candidate edit actions...-prasadd
function updateCRMGroupsDetails($refId,$groupNewIds,$groupType=NULL){
	
	global $maildb,$db;
	
	$groupIdsArr = $groupNewIds;
	$groupIdsCount = count($groupIdsArr);
	
	//$query = "DELETE FROM crmgroup_details WHERE refId = '".$refId."'";
	  $query = "DELETE gd 
			FROM crmgroup_details AS gd 
			INNER JOIN crmgroups AS grp ON gd.groupId = grp.sno  
			WHERE grp.grouptype = '".$groupType."' and gd.refId = '".$refId."'"; 
		
	mysql_query($query, $db);
	

	for($grpCount = 0; $grpCount < $groupIdsCount; $grpCount++)
	{
		if($groupIdsArr[$grpCount] != 0)
		{
		  
			$crmGroupDetailSql="INSERT INTO crmgroup_details (groupId ,refId) VALUES ('".$groupIdsArr[$grpCount]."','".$refId."')";
			mysql_query($crmGroupDetailSql,$db);
		}		
	} 
	
}

//To Update the groups muser,mdate while contact/candidate edit actions...-prasadd
function updateCRMGroups($recordGroupIds,  $archiveGroup = 'N'){

	global $maildb,$db,$username;
	
	$recordGroupIdsArr = explode(",",$recordGroupIds);
	$recordGroupIdsArr = array_unique($recordGroupIdsArr);
	$recordGroupIds = implode(",",$recordGroupIdsArr);
	
	$archiveGroupCondition = "";
	if($archiveGroup == 'Y')
		$archiveGroupCondition = ", status = 'INACTIVE'";
	
	$query = "UPDATE crmgroups SET mdate = NOW(), muser = '".$username."' ".$archiveGroupCondition." WHERE sno IN (".$recordGroupIds.")";
	mysql_query($query, $db);
	
}
//To get new jquery design for category in contact edit/summary screens...-prasadd
function getCategoryDropListOptions($elementId, $currentCatIds){

	global $maildb,$db,$username;	
	$cat_query = "SELECT sno,name FROM manage WHERE type='category' ORDER BY name";
	$cat_data=mysql_query($cat_query,$db);
	$rowsCount = mysql_num_rows($cat_data);	
	$values = $selVal = $valArray = array();
	
	if($rowsCount > 0){
		//$values["0"] = "All";
		$emptyText = "Select Categories";
	}
	else{
	
		$emptyText = "No Categories Available";
	}
	
	$selectRecCount = 0;
	while($row = mysql_fetch_array($cat_data))
	{
		$values[$row[0]] = dispfdb($row[1]);
		$sel = getSelMulti($currentCatIds,$row[0]);
		if($sel=='selected'){
			$selVal[$row[0]]='selected';
			$sel='';
			$selectRecCount++;
		}
	}

	$valArray['elemid'] = $elementId;
	$valArray['options'] = $values;
	$valArray['selected'] = $selVal;
	$valArray['multiple'] = 'No';
	$valArray['crm_groups'] = "";
	$valArray['style'] = 'firstItemChecksAll: false, maxDropHeight: 100, emptyText: "'.$emptyText.'"';
	$valArray['otherPrivateGroupIds'] = '';
	return $valArray;
}
//to get the effected groups while adding/deleting from particular groups...
function getEffectedGroupIds($recordId,$groupNewIds, $groupType){
	
	$currentGroupIds = getCRMGroupIds($recordId, $groupType);
	if(trim($currentGroupIds) != ""){
		$groupOldIds = explode(",",$currentGroupIds);
		$groupOldIdsCount = count($groupOldIds);
	}
	else
		$groupOldIdsCount = 0;
	
	if($groupOldIdsCount > 0){
		
		$groupNewOldDiffArr = array_diff($groupNewIds,$groupOldIds);
		$groupOldNewDiffArr = array_diff($groupOldIds,$groupNewIds);
		$effectedGroupIds = array_merge($groupNewOldDiffArr,$groupOldNewDiffArr);
	}else{
	
		$effectedGroupIds = $groupNewIds;
	}
	return $effectedGroupIds;
}
// Print Canddiate design preperation  START
function getCandidateSingleDataDesign($cadidateDetails)
{
	$candDesign = "";
	$candDesign .= "<table width='90%' align='center' cellpadding='2' cellspacing='1' border='0'>	";
	foreach($cadidateDetails as $key => $value){
		$candDesign .= "<tr><td colspan = 2><b>".html_tls_specialchars($key,ENT_QUOTES)."</b></td></tr>";
		foreach($value as $heading => $showVal){
			if($showVal == '')
				$showVal = '  ';
			$candDesign .= "<tr><td width='20%' valign='top'>".$heading."</td><td>".html_tls_specialchars($showVal,ENT_QUOTES)."</td></tr>";
		}
		$candDesign .= "<tr><td colspan = '2'>&nbsp;</td></tr>";			
	}
	$candDesign .= "</table>";	
	return $candDesign;
}
function getCandidateMultipleDataDesign($cadidateDetails)
{
	$candDesign = "";
	foreach($cadidateDetails as $key => $value){
		$candDesign .= "<table width='90%' align='center' cellpadding='2' cellspacing='1' border='0'>	";
		$candDesign .= "<tr><td ><b>".$key."</b></td></tr>";	
		$candDesign .= "</table>";
		$candDesign .= "<table width='90%' align='center' cellpadding='2' cellspacing='1' border='0'>	";
		$i = 0;
		foreach($value as $showRow){
			if($i == 0){
				$rowStyleOpen = '<u>';
				$rowStyleClose = '</u>';					
			}else{
				$rowStyleOpen = "";
				$rowStyleClose = '';	
			}
			$tdWidth = 100 / count($showRow);
			$candDesign .= "<tr>";		
			$colsCount = 0;		
			foreach($showRow as $showVal){	
				if($showVal == '')
					$showVal = '  ';			
				$candDesign .= "<td width = '".$tdWidth."%' valign='top'>".$rowStyleOpen.html_tls_specialchars($showVal,ENT_QUOTES).$rowStyleClose."</td>";
				$colsCount++;
			}				
			$candDesign .= "</tr>";
			$i++;
		}			
		$candDesign .= "<tr><td colspan = '".$colsCount."'>&nbsp;</td></tr>";	
		$candDesign .= "</table>";		
	}	
	return $candDesign;
}
function getCandidateDoubleDataDesign($cadidateDetails)
{
	$candDesign = "";
	$candDesign .= "<table width='90%' align='center' cellpadding='2' cellspacing='1' border='0'>";
	foreach($cadidateDetails as $key => $value){
		$candDesign .= "<tr><td colspan = 3><b>".$key."</b></td></tr>";			
		foreach($value as $heading => $showVal){
			$candDesign .= "<tr><td colspan = 3>".$heading."</td></tr>";
			foreach($showVal as $subheading => $dispVal){	
				if($dispVal == '')
					$dispVal = '  ';
				$candDesign .= "<tr><td width='5%'>&nbsp;</td><td width='25%'>".$subheading."</td><td>".html_tls_specialchars($dispVal,ENT_QUOTES)."</td></tr>";
			}
		}			
		$candDesign .= "<tr><td colspan = 3>&nbsp;</td></tr>";			
	}
	$candDesign .= "</table>";
	return $candDesign;
}
// Print Canddiate design preperation  END

//Canddiate Details For Print START
function getCandPersonalDetails($candId, $module)
{
	global $maildb,$db,$usename;
	
	$cand_query="SELECT cg.profiletitle,cg.prefix,cg.fname,cg.mname,cg.lname,cg.email,cg.alternate_email,cg.other_email,cg.jobcatid,cg.cg_source,cg.cg_sourcetype,cg.address1,cg.address2,cg.city,cg.state,cg.country,cg.zip,cg.hphone,cg.hphone_extn,cg.wphone,cg.wphone_extn,cg.mobile,cg.fax,cg.other,cg.other_extn ,cg.cphone, cg.cmobile, cg.cfax, cg.cemail, cf. objective ,cf.summary, cl.sno AS CandSno, CONCAT_WS(' ',cg.fname,cg.mname,cg.lname) AS CandFullName, department.deptname AS deptName FROM candidate_general cg, candidate_prof cf, candidate_list cl  LEFT JOIN department ON department.sno = cl.deptid WHERE  cg.username=cf.username AND cl.username = cg.username AND cg.username='".$candId."'";
	
	$sqlRes = mysql_query($cand_query, $db);
	$resRow = mysql_fetch_assoc($sqlRes);
	
	$groups = getCRMGroupNames($resRow['CandSno'],'Candidate', $module);	
	$contact_method = "";
	if($resRow['cphone'] == 'TRUE'){
	$contact_method .= ','.'Phone';
	}
	if($resRow['cmobile'] == 'TRUE'){
	$contact_method .= ','.'Mobile';
	}
	if($resRow['cfax'] == 'TRUE'){
	$contact_method .=  ','.'Fax';
	}
	if($resRow['cemail'] == 'TRUE'){
	$contact_method .=  ','.'Email';
	}
	$contact_method =  trim($contact_method,",");
	
	$personalArray = array('Candidate Info'=>array('ProfileTitle'=>$resRow['profiletitle'], 'Prefix'=>getManage($resRow['prefix']), 'First Name'=>$resRow['fname'], 'Middle Name'=>$resRow['mname'], 'Last Name'=>$resRow['lname'], 'Primary E-mail'=>$resRow['email'], 'Alternate E-mail'=>$resRow['alternate_email'], 'Other E-mail'=>$resRow['other_email'], 'Position Category'=>getManage($resRow['jobcatid']),'Groups'=>$groups, 'HRM Department'=>$resRow['deptName'], 'Source'=>$resRow['cg_source'], 'Source Type'=>getManage($resRow['cg_sourcetype'])),	
	'Contact Info'=>array('Address Line1'=>$resRow['address1'], 'Address Line2'=>$resRow['address2'], 'City'=>$resRow['city'], 'State'=>$resRow['state'], 'Country'=>getCountry($resRow['country']), 'Zip'=>$resRow['zip'], 'Primary Phone'=>$resRow['hphone'], 'Primary Phone Extension'=>$resRow['hphone_extn'], 'Secondary Phone'=>$resRow['wphone'], 'Secondary Phone Extension'=>$resRow['wphone_extn'], 'Mobile'=>$resRow['mobile'], 'Fax'=>$resRow['fax'], 'Other'=>$resRow['other'] , 'Other Phone Extension'=>$resRow['other_extn'], 'Contact Method'=>$contact_method),	
	'Introduction'=>array('Objective'=>$resRow['objective'], 'Summary'=>$resRow['summary']));
	$rtnVal['Name'] = $resRow['CandFullName'];
	$rtnVal['PersonalDetails'] = $personalArray;
	return $rtnVal;
}
function getCandCareerDetails($candId)
{
	global $maildb,$db,$usename;
			
	$skillsArray = array(array('Skill Name','Last Used','Skill Level','Years Of Experience'));
	$educationArray = array(array('Program Name','City/Town','State','Country','Degree','Completion Date'));
	$experienceArray = array(array('Company Name','Title','Description','Start Date','End Date','City','State','Country'));
	$affiliationArray = array(array('Name','Role','Start Date','End Date'));
	$refferenceArray = array(array('Name','Company','Title','Phone','Secondary Phone','Mobile','Email','Relationship'));
	
	// Skills
	$getSkills = "SELECT skillname, lastused, skilllevel, skillyear FROM candidate_skills WHERE candidate_skills.username = '".$candId."'";
	$resSkills = mysql_query($getSkills,$db);
	while($rowSkills = mysql_fetch_row($resSkills)){
		$rowSkills[3] = trim($rowSkills[3],'-');
		$skillsArray[] = $rowSkills;
	}
	// Education
	$getEducation = "SELECT heducation, educity, edustate, countries.country, edudegree_level, edudate FROM candidate_edu LEFT JOIN countries ON countries.sno = candidate_edu.educountry WHERE candidate_edu.username = '".$candId."'";
	$resEducation = mysql_query($getEducation,$db);
	while($rowEducation = mysql_fetch_row($resEducation)){
		$rowEducation[2] = trim($rowEducation[2],'-');
		$rowEducation[5] = trim($rowEducation[5],'-');
		$educationArray[] = $rowEducation;
	}
	// Experience
	$getExperience = "SELECT candidate_work.cname, candidate_work.ftitle, candidate_work.wdesc, candidate_work.sdate, candidate_work.edate, candidate_work.city, candidate_work.state, countries.country FROM candidate_work LEFT JOIN countries ON countries.sno = candidate_work.country WHERE candidate_work.username =  '".$candId."'";
	$resExperience = mysql_query($getExperience,$db);
	while($rowExperience = mysql_fetch_row($resExperience)){		
		$rowExperience[3] = trim(trim($rowExperience[3]),'-');
		$rowExperience[4] = trim(trim($rowExperience[4]),'-');
		$experienceArray[] = $rowExperience;
	}
	// Affiliations
	$getAffiliation = "SELECT affcname, affrole, affsdate, affedate FROM candidate_aff WHERE candidate_aff.username = '".$candId."'";
	$resAffiliation = mysql_query($getAffiliation,$db);
	while($rowAffiliation = mysql_fetch_row($resAffiliation)){
		$rowAffiliation[2] = trim($rowAffiliation[2],'-');
		$rowAffiliation[3] = trim($rowAffiliation[3],'-');
		$affiliationArray[] = $rowAffiliation;
	}
	// References
	$getReferences = "SELECT candidate_ref.name, candidate_ref.company, candidate_ref.title, candidate_ref.phone, candidate_ref.secondary, candidate_ref.mobile, candidate_ref.email, candidate_ref.rship FROM candidate_ref  WHERE candidate_ref.username = '".$candId."'";
	$resReferences = mysql_query($getReferences,$db);
	while($rowReferences = mysql_fetch_row($resReferences)){
		$refferenceArray[] = $rowReferences;
	}
	
	$careerArray = array();
	
	if(mysql_num_rows($resSkills) > 0)
		$careerArray['Skills'] = $skillsArray;
	if(mysql_num_rows($resEducation) > 0)
		$careerArray['Education'] = $educationArray;
	if(mysql_num_rows($resExperience) > 0)
		$careerArray['Experience'] = $experienceArray;
	if(mysql_num_rows($resAffiliation) > 0)
		$careerArray['Affiliations'] = $affiliationArray;
	if(mysql_num_rows($resReferences) > 0)
		$careerArray['References'] = $refferenceArray;
	
	return $careerArray;
}
function getCandPreferenceDetails($candId)
{
	global $maildb,$db,$usename;
	
	$CandPref_Qry = "SELECT 
						TRIM(BOTH ', ' FROM (IF(desirejob!='||',REPLACE(desirejob,'|',', '),''))) as desiredjob, TRIM(BOTH ', ' FROM (IF(desirestatus!='||',REPLACE(desirestatus,'|',', '),''))) as desiredstatus, desirelocation, TRIM(BOTH ', ' FROM (CONCAT(IF(SUBSTRING_INDEX(resourcetype,'^',1)='true','Independent Contractor',''),', ',IF(SUBSTRING_INDEX(resourcetype,'^',-1)='true','Payroll	ed Employee','')))) as resourcetype, IF(wtravle='true','Yes','No'), ptravle, tcomments, IF(wlocate='true','Yes','No'), city, state, country, lcomments, tmax, dmax, ccomments, TRIM(BOTH ', ' FROM (IF(distributename!='',REPLACE(distributename,'|',', '),''))) as distributedname, IF(amount!='',CONCAT_WS(' ',amount,currency,period),'') as desiredamount, compcomments, CONCAT_WS(' ',rperiod,rcurrency) as amountperiod, pramount, poamount, iramount, ioamount, aramount, aoamount 
					FROM candidate_pref 
					WHERE username = '".$candId."'";
	$CandPref_Res = mysql_query($CandPref_Qry,$db);
	$CandPref_Row = mysql_fetch_row($CandPref_Res);
	
	$CandProf_Qry = "SELECT 
						IF(ifother!='',CONCAT_WS(': ',LCASE(pstatus),ifother),pstatus) as presentstatus, IF((availsdate!='immediate' && availsdate!='inactive'),CAST(CONCAT_WS(' ','Available Starting:',date_format(str_to_date(availsdate,'%Y-%m-%d'),'%m/%d/%Y'),'Not Available After:',date_format(str_to_date(availedate,'%Y-%m-%d'),'%m/%d/%Y')) AS CHAR),LCASE(availsdate)) as availability
					FROM candidate_prof 
					WHERE username = '".$candId."'";
	$CandProf_Res = mysql_query($CandProf_Qry,$db);
	$CandProf_Row = mysql_fetch_row($CandProf_Res);
	
	$CandList_Qry = "SELECT 
						manage.name
					FROM candidate_list 
					LEFT JOIN manage ON manage.sno = candidate_list.cl_status
					WHERE candidate_list.username = '".$candId."'";
	$CandList_Res = mysql_query($CandList_Qry,$db);
	$CandList_Row = mysql_fetch_row($CandList_Res);						
	
	$preferenceArray = array('Preferences'=>
										array('Desired Job'=>
															array('Job Type'=>$CandPref_Row[0],
																	'Status'=>$CandPref_Row[1],
																	'Location'=>$CandPref_Row[2],
																	'Resource Type'=>$CandPref_Row[3]),
											'Status'=>array('Status'=>$CandList_Row[0],
															'Availability'=>$CandProf_Row[1],
															'Present Status'=>$CandProf_Row[0]),
											'Desired Compensation'=>array('Amount'=>$CandPref_Row[16],
																			'Comments'=>$CandPref_Row[17]),
											'Rate'=>array('Amount Period'=>$CandPref_Row[18],
															'Pay Regular Amount'=>$CandPref_Row[19],
															'Pay Over Time Amount'=>$CandPref_Row[20],
															'Min Pay Regular Amount'=>$CandPref_Row[21],
															'Min Pay Over Time Amount'=>$CandPref_Row[22],
															'Max Pay Regular Amount'=>$CandPref_Row[23],
															'Max Pay Over Time Amount'=>$CandPref_Row[24]),
											'Traveling Information'=>array('Willing to Travel'=>$CandPref_Row[4],
																			'Percentage Travel'=>$CandPref_Row[5],
																			'Travel Considerations'=>$CandPref_Row[6]),
											'Relocation Information'=>array('Willing to Relocate'=>$CandPref_Row[7],
																			'City'=>$CandPref_Row[8],
																			'State'=>$CandPref_Row[9],
																			'Country'=>getCountry($CandPref_Row[10]),
																			'Comments'=>$CandPref_Row[11]),
											'Commute Information'=>array('Time Max'=>$CandPref_Row[12],
																		'Distance Max'=>$CandPref_Row[13],
																		'Comments'=>$CandPref_Row[14]),
											'Distribution Information'=>array('Do Not Distribute To'=>$CandPref_Row[15])));
	
	return $preferenceArray;
}
function getCandAdditionalDetails($candId)
{
	global $maildb,$db,$usename;
	
	//AditionalInformation
	$getAddInformation = "SELECT addinfo AS AdditionalInformation, search_tags AS SearchTags FROM candidate_prof WHERE candidate_prof.username = '".$candId."'";
	$resAddInformation = mysql_query($getAddInformation,$db);
	$rowAddInformation = mysql_fetch_array($resAddInformation);
	
	$additionalArray = array('Additional Information'=>array('Additional Information'=>$rowAddInformation['AdditionalInformation'], 'Search Tags'=>$rowAddInformation['SearchTags']));
	
	return $additionalArray;
}
//Canddiate Details For Print END

function getConPersonalDetails($conid, $module)
{
	global $maildb,$db,$usename;
	
	$con_query="SELECT cg.profiletitle,cg.prefix,cg.fname,cg.mname,cg.lname,cg.email,cg.alternate_email,cg.other_email,cg.source,cg.sourcetype,cg.address1,cg.address2,cg.city,cg.state,cg.country,cg.zip,cg.hphone,cg.hphone_extn,cg.wphone,cg.wphone_extn,cg.mobile,cg.fax,cg.other,cg.other_extn ,cg.cphone, cg.cmobile, cg.cfax, cg.cemail, cf. objective ,cf.summary,cf.addinfo,cf.pstatus,cf.ifother,cf.availsdate,cf.availedate,cl.serial_no AS CandSno, CONCAT_WS(' ',cg.fname,cg.mname,cg.lname) AS CandFullName FROM consultant_general cg, consultant_prof cf,  consultant_list cl WHERE  cg.username=cf.username AND cl.username = cg.username AND cg.username='".$conid."'";
	$sqlRes = mysql_query($con_query, $db);
	$resRow = mysql_fetch_assoc($sqlRes);
	
	if($resRow['prefix'] == 'Sele') $resRow['prefix'] = '';
	
	//echo $con_query;	
	//print_r($resRow);
	//exit;
	$resRow['ifother'] = trim($resRow['ifother'],'-');
	
	$contact_method = "";
	if($resRow['cphone'] == 'TRUE'){
	$contact_method .= ','.'Phone';
	}
	if($resRow['cmobile'] == 'TRUE'){
	$contact_method .= ','.'Mobile';
	}
	if($resRow['cfax'] == 'TRUE'){
	$contact_method .=  ','.'Fax';
	}
	if($resRow['cemail'] == 'TRUE'){
	$contact_method .=  ','.'Email';
	}
	$contact_method =  trim($contact_method,",");
	
	if ($resRow['ifother'])
	{
		$resRow['pstatus'] = $resRow['ifother'];
	}
	
	$personalArray = array('Candidate Info'=>array('ProfileTitle'=>$resRow['profiletitle'], 'Prefix'=>$resRow['prefix'], 'First Name'=>$resRow['fname'], 'Middle Name'=>$resRow['mname'], 'Last Name'=>$resRow['lname'], 'Primary E-mail'=>$resRow['email'], 'Alternate E-mail'=>$resRow['alternate_email'], 'Other E-mail'=>$resRow['other_email'], 'Source'=>$resRow['source'], 'Source Type'=>getManage($resRow['sourcetype'])),	
	'Contact Info'=>array('Address Line1'=>$resRow['address1'], 'Address Line2'=>$resRow['address2'], 'City'=>$resRow['city'], 'State'=>$resRow['state'], 'Country'=>getCountry($resRow['country']), 'Zip'=>$resRow['zip'], 'Primary Phone'=>$resRow['hphone'], 'Primary Phone Extension'=>$resRow['hphone_extn'], 'Secondary Phone'=>$resRow['wphone'], 'Secondary Phone Extension'=>$resRow['wphone_extn'], 'Mobile'=>$resRow['mobile'], 'Fax'=>$resRow['fax'], 'Other'=>$resRow['other'] , 'Other Phone Extension'=>$resRow['other_extn'], 'Contact Method'=>$contact_method),	
	'Introduction'=>array('Objective'=>$resRow['objective'], 'Summary'=>$resRow['summary']),	
	'Status'=>array('Present Status'=>$resRow['pstatus'],'Availability'=>$resRow['availsdate']),	
	'Additional Info'=>array('Additional Information'=>$resRow['addinfo']));
	
	$rtnVal['Name'] = $resRow['CandFullName'];
	$rtnVal['PersonalDetails'] = $personalArray;
	return $rtnVal;
}
function getConCareerDetails($conid)
{
	global $maildb,$db,$usename;
	
			
	$skillsArray = array(array('Skill Name','Last Used','Skill Level','Years Of Experience'));
	$educationArray = array(array('Program Name','City/Town','State','Country','Degree','Completion Date'));
	$experienceArray = array(array('Company Name','Title','Description','Start Date','End Date','City','State','Country'));
	$affiliationArray = array(array('Name','Role','Start Date','End Date'));
	$refferenceArray = array(array('Name','Company','Title','Phone','Secondary Phone','Mobile','Email','Relationship'));
	
	$desiredlocationArray = array(array('City','State','Country','Willing to relocate'));
	$desiredjobArray = array(array('Desire job','Desire Status','Desire Amount','Desire Job Type','Monthly payment','Desire Location'));
	$statusArray = array(array('Arrival Date','ssn Date'));
	
	// Skills
	$getSkills = "SELECT skillname, lastused, skilllevel, skillyear FROM consultant_skills WHERE consultant_skills.username = '".$conid."'";
	$resSkills = mysql_query($getSkills,$db);
	while($rowSkills = mysql_fetch_row($resSkills)){
		$rowSkills[3] = trim($rowSkills[3],'-');
		$skillsArray[] = $rowSkills;
	}
	
	// Education
	$getEducation = "SELECT heducation, educity, edustate, countries.country, edudegree_level, edudate FROM consultant_edu LEFT JOIN countries ON countries.sno = consultant_edu.educountry WHERE consultant_edu.username = '".$conid."'";
	$resEducation = mysql_query($getEducation,$db);
	while($rowEducation = mysql_fetch_row($resEducation)){
		$rowEducation[2] = trim($rowEducation[2],'-');
		$rowEducation[5] = trim($rowEducation[5],'-');
		$educationArray[] = $rowEducation;
	}
	
	// Experience

	$getExperience = "SELECT consultant_work.cname, consultant_work.ftitle, consultant_work.wdesc, consultant_work.sdate, consultant_work.edate, consultant_work.city, consultant_work.state, countries.country FROM consultant_work LEFT JOIN countries ON countries.sno = consultant_work.country WHERE consultant_work.username =  '".$conid."'";
	$resExperience = mysql_query($getExperience,$db);
	while($rowExperience = mysql_fetch_row($resExperience)){		
		$rowExperience[3] = trim(trim($rowExperience[3]),'-');
		$rowExperience[4] = trim(trim($rowExperience[4]),'-');
		$experienceArray[] = $rowExperience;
	}	
	// Affiliations
	$getAffiliation = "SELECT affcname, affrole, affsdate, affedate FROM consultant_aff WHERE consultant_aff.username = '".$conid."'";
	$resAffiliation = mysql_query($getAffiliation,$db);
	while($rowAffiliation = mysql_fetch_row($resAffiliation)){
		$rowAffiliation[2] = trim($rowAffiliation[2],'-');
		$rowAffiliation[3] = trim($rowAffiliation[3],'-');
		$affiliationArray[] = $rowAffiliation;
	}
	// References
	$getReferences = "SELECT consultant_ref.name, consultant_ref.company, consultant_ref.title, consultant_ref.phone, consultant_ref.secondary, consultant_ref.mobile, consultant_ref.email, consultant_ref.rship FROM consultant_ref  WHERE consultant_ref.username = '".$conid."'";
	$resReferences = mysql_query($getReferences,$db);
	while($rowReferences = mysql_fetch_row($resReferences)){
		$refferenceArray[] = $rowReferences;
	}
	
	// Desired Job
	$getDesJob = "SELECT desirejob, desirestatus, desireamount, desiretype, mpayment, desirelocation FROM consultant_desire WHERE consultant_desire.username = '".$conid."'";
	$resDesJob = mysql_query($getDesJob,$db);
	while($rowDesJob = mysql_fetch_row($resDesJob)){
	$rowDesJob[0] = trim($rowDesJob[0],'|');
	$rowDesJob[1] = trim($rowDesJob[1],'|');
		$desiredjobArray[] = $rowDesJob;
	}
	// Desired Location
	$getDesLoc = "SELECT city, state, countries.country, wlocate FROM consultant_location LEFT JOIN countries ON countries.sno = consultant_location.country WHERE consultant_location.username = '".$conid."'";
	$resDesLoc = mysql_query($getDesLoc,$db);
	while($rowDesLoc = mysql_fetch_row($resDesLoc)){
		$desiredlocationArray[] = $rowDesLoc;
	}
	
	// Status
	//$getStatus = "SELECT arrivaldate, ssndate FROM consultant_status WHERE consultant_status.username = '".$conid."'";
	//$resStatus = mysql_query($getStatus,$db);
	//while($rowStatus = mysql_fetch_row($resStatus)){
	//	$statusArray[] = $rowStatus;
	//}
	
	$careerArray = array();
	
	if(mysql_num_rows($resSkills) > 0)
		$careerArray['Skills'] = $skillsArray;
	if(mysql_num_rows($resEducation) > 0)
		$careerArray['Education'] = $educationArray;
	if(mysql_num_rows($resExperience) > 0)
		$careerArray['Experience'] = $experienceArray;
	if(mysql_num_rows($resAffiliation) > 0)
		$careerArray['Affiliations'] = $affiliationArray;
	if(mysql_num_rows($resReferences) > 0)
		$careerArray['References'] = $refferenceArray;
		
	if(mysql_num_rows($resDesJob) > 0)
		$careerArray['Desire Job'] = $desiredjobArray;	
	if(mysql_num_rows($resDesLoc) > 0)
		$careerArray['Desire Location'] = $desiredlocationArray;	
	if(mysql_num_rows($resStatus) > 0)
		$careerArray['Status'] = $statusArray;
	
	return $careerArray;
}
//Employee Personal Detail
function getEmpPersonalDetails($empid, $module)
{
	global $maildb,$db,$usename;
	
	$con_query="SELECT hg.profiletitle,hg.fname,hg.mname,hg.lname,hg.email,hg.alternate_email,hg.other_email,hg.address1,hg.address2,hg.city,hg.state,hg.country,hg.zip,hg.hphone,hg.hphone_extn,hg.wphone,hg.wphone_extn,hg.mobile,hg.fax,hf.addinfo,hf.pstatus,hf.ifother,hf.avail, CONCAT_WS(' ',hg.fname,hg.mname,hg.lname) AS CandFullName FROM emp_list el, hrcon_general hg LEFT JOIN hrcon_prof hf ON (hg.username=hf.username AND hg.ustatus=hf.ustatus) WHERE el.username = hg.username AND hg.ustatus = 'active' AND hg.username='".$empid."'";
	
	$sqlRes = mysql_query($con_query, $db);
	$resRow = mysql_fetch_assoc($sqlRes);
	
	$resRow['hphone'] = trim($resRow['hphone'],'-');
	$resRow['wphone'] = trim($resRow['wphone'],'-');
	$resRow['mobile'] = trim($resRow['mobile'],'-');
	$resRow['fax'] = trim($resRow['fax'],'-');
	
	$resRow['ifother'] = trim($resRow['ifother'],'-');
	
	if ($resRow['ifother'])
	{
		$resRow['pstatus'] = $resRow['ifother'];
	}
		
	
	$personalArray = array('Candidate Info'=>array('ProfileTitle'=>$resRow['profiletitle'], 'First Name'=>$resRow['fname'], 'Middle Name'=>$resRow['mname'], 'Last Name'=>$resRow['lname'], 'Primary E-mail'=>$resRow['email'], 'Alternate E-mail'=>$resRow['alternate_email'], 'Other E-mail'=>$resRow['other_email']),	
	'Contact Info'=>array('Address Line1'=>$resRow['address1'], 'Address Line2'=>$resRow['address2'], 'City'=>$resRow['city'], 'State'=>$resRow['state'], 'Country'=>getCountry($resRow['country']), 'Zip'=>$resRow['zip'], 'Primary Phone'=>$resRow['wphone'], 'Primary Phone Extension'=>$resRow['hphone_extn'], 'Secondary Phone'=>$resRow['hphone'], 'Secondary Phone Extension'=>$resRow['wphone_extn'], 'Mobile'=>$resRow['mobile'], 'Fax'=>$resRow['fax']),
	'Status'=>array('Present Status'=>$resRow['pstatus'],'Availability'=>$resRow['avail']),	
	'Additional Info'=>array('Additional Information'=>$resRow['addinfo']));
	
	
	
	$rtnVal['Name'] = $resRow['CandFullName'];
	$rtnVal['PersonalDetails'] = $personalArray;
	return $rtnVal;
}

//Employee Career Details
function getEmpCareerDetails($empid)
{
	global $maildb,$db,$usename;
	
			
	$skillsArray = array(array('Skill Name','Last Used','Skill Level','Years Of Experience'));
	$educationArray = array(array('Program Name','City/Town','State','Country','Degree','Completion Date'));
	$experienceArray = array(array('Company Name','Title','Description','Start Date','End Date','City','State','Country'));
	$affiliationArray = array(array('Name','Role','Start Date','End Date'));
	$refferenceArray = array(array('Name','Company','Title','Phone','Secondary Phone','Mobile','Email','Relationship'));
	
	
	
	// Skills
	$getSkills = "SELECT skillname, lastused, skilllevel, skillyear FROM hrcon_skills WHERE hrcon_skills.username = '".$empid."'";
	$resSkills = mysql_query($getSkills,$db);
	while($rowSkills = mysql_fetch_row($resSkills)){
		$rowSkills[3] = trim($rowSkills[3],'-');
		$skillsArray[] = $rowSkills;
	}	
	
	// Education
	$getEducation = "SELECT heducation, educity, edustate, countries.country, edudegree_level, edudate FROM hrcon_edu LEFT JOIN countries ON countries.sno = hrcon_edu.educountry WHERE hrcon_edu.username = '".$empid."'";
	$resEducation = mysql_query($getEducation,$db);
	while($rowEducation = mysql_fetch_row($resEducation)){
		$rowEducation[2] = trim($rowEducation[2],'-');
		$rowEducation[5] = trim($rowEducation[5],'-');
		$educationArray[] = $rowEducation;
	}
	
	// Experience
	$getExperience = "SELECT hrcon_work.cname, hrcon_work.ftitle, hrcon_work.wdesc, hrcon_work.sdate, hrcon_work.edate, hrcon_work.city, hrcon_work.state, countries.country FROM hrcon_work LEFT JOIN countries ON countries.sno = hrcon_work.country WHERE hrcon_work.username =  '".$empid."'";
	$resExperience = mysql_query($getExperience,$db);
	while($rowExperience = mysql_fetch_row($resExperience)){		
		$rowExperience[3] = trim(trim($rowExperience[3]),'-');
		$rowExperience[4] = trim(trim($rowExperience[4]),'-');
		$experienceArray[] = $rowExperience;
	}
		
	// Affiliations
	$getAffiliation = "SELECT affcname, affrole, affsdate, affedate FROM hrcon_aff WHERE hrcon_aff.username = '".$empid."'";
	$resAffiliation = mysql_query($getAffiliation,$db);
	while($rowAffiliation = mysql_fetch_row($resAffiliation)){
		$rowAffiliation[2] = trim($rowAffiliation[2],'-');
		$rowAffiliation[3] = trim($rowAffiliation[3],'-');
		$affiliationArray[] = $rowAffiliation;
	}
	
	// References
	$getReferences = "SELECT hrcon_ref.name, hrcon_ref.company, hrcon_ref.title, hrcon_ref.phone, hrcon_ref.secondary, hrcon_ref.mobile, hrcon_ref.email, hrcon_ref.rship FROM hrcon_ref  WHERE hrcon_ref.username = '".$empid."'";
	$resReferences = mysql_query($getReferences,$db);
	while($rowReferences = mysql_fetch_row($resReferences)){
	$rowReferences[3] = trim($rowReferences[3],'-');
	$rowReferences[4] = trim($rowReferences[4],'-');
	$rowReferences[5] = trim($rowReferences[5],'-');
		$refferenceArray[] = $rowReferences;
	}
	
	$careerArray = array();
	
	if(mysql_num_rows($resSkills) > 0)
		$careerArray['Skills'] = $skillsArray;
	if(mysql_num_rows($resEducation) > 0)
		$careerArray['Education'] = $educationArray;
	if(mysql_num_rows($resExperience) > 0)
		$careerArray['Experience'] = $experienceArray;
	if(mysql_num_rows($resAffiliation) > 0)
		$careerArray['Affiliations'] = $affiliationArray;
	if(mysql_num_rows($resReferences) > 0)
		$careerArray['References'] = $refferenceArray;

	return $careerArray;
}
	
function getSequentialNumber($tabledetails, $tablefieldkey, $currnumber)
{
	global $maildb,$db;
		
	if(is_numeric($currnumber) == 1)
	{
		$currnumber = $currnumber+1;
	}
	else
	{
		$string = ereg_replace("[^0-9]", "",$currnumber);

		if($string == "") 
		{
			$currnumber = $currnumber."1";
		}
		else
		{
			$matches = $matchpos = $temparray = array();
			$getstringval = "";

			$getstringval = ereg_replace("[^0-9]", "^",$currnumber);
			$temparray = explode("^",$getstringval);

			for($i=0;$i<count($temparray);$i++)
			{
				if(is_numeric($temparray[$i]))	
					$matches[] = $temparray[$i];
			}

			$matchcount = count($matches);
			if($matchcount > 0)
			{
				$chngvalue = $matches[$matchcount-1];
				$getvalue = $chngvalue+1;
				$matchposcount = count($matchpos);
				$matchpos = strrpos($currnumber, $chngvalue);
				$currnumber = substr_replace($currnumber,$getvalue,$matchpos,strlen($chngvalue));
			}
		}
	}

	$tablename = $condition = "";
	foreach($tabledetails as $key => $value)
	{
		$tablename = $key;
		$tablefields = $value;
	}

	$tablefields[$tablefieldkey] = $currnumber;

	foreach($tablefields as $key => $value)
	{
		if($condition == "")
			$condition = " WHERE ".$key."='".$value."'";
		else 
			$condition = $condition." AND ".$key."='".$value."'";
	}

	$sql2 = "SELECT COUNT(1) FROM ".$tablename."".$condition;
	$result2 = mysql_query($sql2,$db); 
	$row2 = mysql_fetch_array($result2);

	if($row2[0] > 0)
		return getSequentialNumber($tabledetails, $tablefieldkey, $currnumber);
	else
		return $currnumber;
}

//This fun for getting dynamic -elements array based on assignment present.
function getValidAssgnElements($new_user,$rowcou,$elements,$thisday,$thisdayto,$condCk_comp,$showEmplyoees,$db,$module)
{
	$newElements = array();
	$earCountVal = 0;
	if($module != 'Client')
	{
		$que="SELECT COUNT(1) FROM hrcon_benifit WHERE username='".$new_user."' AND ustatus='active'";
		$res=mysql_query($que,$db);
		$earCount = mysql_fetch_row($res);
		$earCountVal = $earCount[0];
	}
	
	for($r=0;$r<$rowcou;$r++)
	{
		if($earCountVal == 0)
		{
			if($elements[$r][0] == "") //while adding new row  date is empty for  new row..
			{
				while($thisday<=$thisdayto)
				{
					$dynamicUstatus = " AND ((hrcon_jobs.ustatus IN ('active','closed') AND (hrcon_jobs.s_date IS NULL OR hrcon_jobs.s_date='' OR hrcon_jobs.s_date='0-0-0' OR (DATE(STR_TO_DATE(s_date,'%m-%d-%Y'))<='".date("Y-m-d",$thisday)."'))) AND (IF(hrcon_jobs.ustatus='closed',(hrcon_jobs.e_date IS NOT NULL AND hrcon_jobs.e_date<>'' AND hrcon_jobs.e_date<>'0-0-0' AND DATE(STR_TO_DATE(e_date,'%m-%d-%Y'))>='".date("Y-m-d",$thisday)."'),1)))";

					$zque = "SELECT COUNT(1) FROM hrcon_jobs WHERE ".$condCk_comp." username = '".$new_user."' AND pusername!=''".$dynamicUstatus." ".$showEmplyoees." AND hrcon_jobs.jtype!='' ORDER BY udate DESC";
					$zres=mysql_query($zque,$db);
					$zrowCount = mysql_fetch_row($zres); //for billable checkbox
						
					if($zrowCount[0] > 0)
					{
						$elements[$r][0] = date("Y-m-d",$thisday);
						break;
					}
					else
					{
						$thisday = getTimeStampByNDays("+1",$thisday);
					}
				}
			}

			if((preg_match("/range/i", $elements[$r][0])))
			{
				$dateArr = explode("-range-",$elements[$r][0]);
				$assignStartDate =$dateArr[0];
				$assignEndDate =$dateArr[1]; 
			}
			else
			{
				$assignStartDate =$elements[$r][0];
				$assignEndDate =$elements[$r][0];
			}
				
			if((preg_match("/range/i", $elements[$r][0])))
				$dynamicUstatus = " AND ((hrcon_jobs.ustatus IN ('active','closed') AND (hrcon_jobs.s_date IS NULL OR hrcon_jobs.s_date='' OR hrcon_jobs.s_date='0-0-0' OR (DATE(STR_TO_DATE(s_date,'%m-%d-%Y')) <= '".$assignEndDate."'))) AND (IF(hrcon_jobs.ustatus='closed',(hrcon_jobs.e_date IS NOT NULL AND hrcon_jobs.e_date<>'' AND hrcon_jobs.e_date<>'0-0-0' AND DATE(STR_TO_DATE(e_date,'%m-%d-%Y'))>='".$assignStartDate."'),1)))";
			else
				$dynamicUstatus = " AND ((hrcon_jobs.ustatus IN ('active','closed') AND (hrcon_jobs.s_date IS NULL OR hrcon_jobs.s_date='' OR hrcon_jobs.s_date='0-0-0' OR (DATE(STR_TO_DATE(s_date,'%m-%d-%Y'))<='".$assignStartDate."'))) AND (IF(hrcon_jobs.ustatus='closed',(hrcon_jobs.e_date IS NOT NULL AND hrcon_jobs.e_date<>'' AND hrcon_jobs.e_date<>'0-0-0' AND DATE(STR_TO_DATE(e_date,'%m-%d-%Y'))>='".$assignStartDate."'),1)))";
			$zque = "SELECT COUNT(1) FROM hrcon_jobs WHERE ".$condCk_comp." username = '".$new_user."' AND pusername!=''".$dynamicUstatus." ".$showEmplyoees." AND hrcon_jobs.jtype!='' ORDER BY udate DESC";
			$zres=mysql_query($zque,$db);
			$zrowCount = mysql_fetch_row($zres); //for billable checkbox
			
			if($zrowCount[0] > 0){
				$newElements[] = $elements[$r];
			}else{
				continue;
			}
		}else{
		
			$newElements[] = $elements[$r];
		}
	}
	return $newElements;
}
	
	function getCustomerDept($custID)
	{
		global $maildb,$db, $username;
		$queryCustDept = "SELECT deptid FROM Client_Accounts, staffacc_cinfo WHERE Client_Accounts.typeid = staffacc_cinfo.sno AND Client_Accounts.clienttype = 'CUST' AND Client_Accounts.status = 'active' AND staffacc_cinfo.username = '".$custID."'";
		$resCustDept = mysql_query($queryCustDept, $db);
		$rowCustDept = mysql_fetch_assoc($resCustDept);
		if($rowCustDept['deptid'] != 0 && $rowCustDept['deptid'] != '')
			$custDeptId = $rowCustDept['deptid'];
		else
			$custDeptId = getOwnerDepartment();					
		
		return 	$custDeptId;	
	}

	function prepareCompanyContactID($addr, $companyChildIds, $tempCompIds, $showHeirarchy, $isAdminModule = '')
	{
		global $username, $maildb,$db;
		$conemail = "";
	
		if($showHeirarchy == 'true'){
			$companyAssChildIds = $addr.','.$companyChildIds;
			$temp_compIds = $companyAssChildIds;				
			$companyAssChildIdsAry = explode(',',$companyAssChildIds);				
			for($j = 0; $j < count($companyAssChildIdsAry); $j++){
				$tempCompIds.= "com".$companyAssChildIdsAry[$j]." ,";
				$tempAssCompIds.= "A".$companyAssChildIdsAry[$j]."A ,";
			}
			$tempCompIds = trim($tempCompIds, ',');	
			$tempAssCompIds = trim($tempAssCompIds, ',');
			$CompanyCondition = "csno IN(".$companyAssChildIds.") ";
		}else{
			$tempAssCompIds = "A".$addr."A";	
			$temp_compIds =  $addr;
			$CompanyCondition = "csno = ".$addr;
		}	
		
		if($isAdminModule == 'Admin'){		
			$contactAccessCond = " ";			
		}else{
			$contactAccessCond = " and (FIND_IN_SET('".$username."',accessto)>0 or owner='".$username."' or accessto='ALL')";
		}
					
		$que="select sno,email,replace(CONCAT_WS(' ',staffoppr_contact.fname,staffoppr_contact.mname,staffoppr_contact.lname),'  ',' ') from staffoppr_contact where  status='ER' and crmcontact='Y' and ".$CompanyCondition.$contactAccessCond;
		$res=mysql_query($que,$db);
		$temp_conNameCom=Array();
	
		while($data=mysql_fetch_array($res))
		{
			$tempCompIds.=",oppr".$data[0];
			$temp_conNameCom["oppr".$data[0]]=$data[2];
	
			$conemail.= ($conemail == "") ? $data[1] : ",".$data[1];
			
			$temp_comContIDS.="A".$data[0]."A,";
		}
			
		$temp_comContIDS = trim($temp_comContIDS, ',');	
		
		$temp_name = $temp_name1 = ",";
		$boundary = $boundary1 = $temp_contactNameChk = "";
		
		
		if($temp_name != ',') {
			$temp_contactNameChk = trim($temp_name."''".$boundary,",");
			$temp_name1 = '';
		} 
		
		$temp_oldContactIDs = $temp_contactOldNameChk = "";
		$temp_cmngmtIDs = array();
	
		$temp_oldContactIDs = trim($temp_oldContactIDs, ',');
		
		if($temp_name1 != "," && $temp_name1 != ""){
			$temp_contactOldNameChk = trim($temp_name1."''".$boundary1,",");
			if($temp_name != "," )
				$temp_name.= $temp_name1;
			else
				$temp_name = $temp_name1;
				
			$boundary.= $boundary1;
		}
	
		$temp_company_contact_names = ($temp_name != ",") ? $temp_name."''".$boundary." as namec" : ", '' as namec";	
		
		$aryContactRetunResult = array();	
		$aryContactRetunResult['tempAssCompIds'] = $tempAssCompIds;
		$aryContactRetunResult['tempCompIds'] = $tempCompIds;
		$aryContactRetunResult['temp_compIds'] = $temp_compIds;
		$aryContactRetunResult['temp_company_contact_names'] = $temp_company_contact_names ;
		$aryContactRetunResult['temp_comContIDS'] = $temp_comContIDS;
		$aryContactRetunResult['temp_contactNameChk'] = $temp_contactNameChk ;
		$aryContactRetunResult['temp_conNameCom'] = $temp_conNameCom;		
		$aryContactRetunResult['temp_contactOldNameChk'] = $temp_contactOldNameChk;
		
		$aryContactRetunResult['temp_cmngmtIDs'] = '';		
		
		$aryContactRetunResult['temp_contactEmail'] = $conemail;	
		
		return $aryContactRetunResult;
	}
	
	function getCorporationNameAndDescription($corpId) {

		GLOBAL $db;

		$corp_desc	= '';

		$corp_query		= "SELECT cc.name, cc.description FROM corp_code cc WHERE cc.sno=$corpId";
		$corp_result	= mysql_query($corp_query, $db);
		$corp_data		= mysql_fetch_assoc($corp_result);

		if (isset($corp_data['name']) && !empty($corp_data['name'])) {

			$corp_desc	= $corp_data['name'];
		}

		if (isset($corp_data['description']) && !empty($corp_data['description'])) {

			$corp_desc	.= ' - ' . $corp_data['description'];
		}

		return $corp_desc;
	}
	
	function getUDFColsForExport($mod)
	{
		global $db;
		$udfCols = array();
		if($mod == 5)
		{
			$ori_mod = 3;
		}
		$sql = "select t1.id, element_name, element_lable, t1.element, required, default_opt
				from udf_form_details t1 left join udf_form_details_order t4 on t4.custom_form_details_id = t1.id
				where t1.module  = ".$ori_mod." and t1.status = 'Active' order by t4.ele_order asc";
		$result = mysql_query($sql, $db);
		while($row = mysql_fetch_assoc($result))
		{
			$udfCols[] = urldecode($row['element_name']);
		}
		$udfColString = implode(",", $udfCols);
		
		return $udfColString.",";
	}
	
	function getUDFValuesForPrint($id, $mod)
	{
		global $db;
		$udfCols = array();
		if($mod == 5 || $mod == 10)
		{
			$ori_mod = 3;
		}

		$sql = "select t1.id, element_name, element_lable, t1.element, required, default_opt
				from udf_form_details t1 left join udf_form_details_order t4 on t4.custom_form_details_id = t1.id
				where t1.module  = ".$ori_mod." and t1.status = 'Active' order by t4.ele_order asc";
		$result = mysql_query($sql, $db);
		while($row = mysql_fetch_assoc($result))
		{
			$udfCols[] = $row['element_lable']." as '".urldecode($row['element_name'])."'";
		}
		$udfColString = implode(",", $udfCols);
		if($mod == 5)
		{
			$sql = "SELECT ".$udfColString." FROM udf_form_details_employee_values t1 INNER JOIN emp_list t2 ON t1.rec_id = t2.sno AND t2.username = '".$id."'";
		}
		else if($mod == 10)
		{
			$sql = "SELECT ".$udfColString." FROM udf_form_details_jobboard_applicant_values t1 INNER JOIN consultant_list t2 ON t1.rec_id = t2.serial_no AND t2.username = '".$id."'";
		}
		$result = mysql_query($sql, $db);
		
		$udfRow['Company Defined Fields'] = mysql_fetch_assoc($result);
		return $udfRow;
	}
	/* This code as modified BY SARANESH AR. To address the issue of Payment Terms when `Due in` set as 0
	If Due in set as zero 0 then need to consider as today date.  
	[#868142] - Action Needed: question regarding same emails for separate instances in Akken
	*/
	function getCustDueDateBasedOnTerms($invoicedate,$billTermsVal){
            
       global $db;
       $invoiceduedate='';
        $getPayTermsDueDateQuery = "SELECT billpay_due FROM bill_pay_terms WHERE billpay_termsid = '".$billTermsVal."' AND billpay_status = 'active' LIMIT 1";
        $payTermsDueDateRes = mysql_query($getPayTermsDueDateQuery,$db);
        if(mysql_num_rows($payTermsDueDateRes) > 0){
            $payTermsDueDateRow = mysql_fetch_row($payTermsDueDateRes);
            if($payTermsDueDateRow[0]!=0){
                if($invoicedate==null || $invoicedate==''){
                    $newFormatedInvdate = date('Y-m-d');
                }else{
                    $newFormatedInvdate = date('Y-m-d', strtotime($invoicedate));
                }
                $invoiceduedate = date('m/d/Y', strtotime($newFormatedInvdate. ' + '.$payTermsDueDateRow[0].' days'));
            }else if ($payTermsDueDateRow[0] == "0") {
            	$invoiceduedate = date('m/d/Y');
            }else{
              $invoiceduedate='';  
            }
        }else{
            $invoiceduedate='';
        }
      	return $invoiceduedate;
   }

    function createCandGroupDropDownEnh($arrValues) {
	
	$str = $selected='';
	$elemId = $arrValues['elemid'];		# the id/name for the list box element
	$valArr = $arrValues['options'];	# the array of options to be displayed in the list box
	$selVal = $arrValues['selected'];   # selected element in the options array
	$style	=  $arrValues['style'];  	# style for the drop down list, width, maxdropHeight to be set
	
	if($arrValues['callpage'] == 'contactSummary'){
		$style.= ", width: 180";
		$drpDownStyle = (strpos($_SERVER['HTTP_USER_AGENT'],'Gecko') > -1)  ? "width: 180px" : "width: 140px";
	}else{
		$style.= ", width: 225";
		$drpDownStyle = (strpos($_SERVER['HTTP_USER_AGENT'],'Gecko') > -1)  ? "width: 245px" : "width: 225px";
	}	
	if($style==''){
		$style = 'width: 150, maxDropHeight: 80';
	}	

	if($drpDownStyle!=''){
		
		$styleX= "<style>";
		$styleX.= ".ui-dropdownchecklist{";
		$styleX.= $drpDownStyle;
		$styleX.= "}";	
		$styleX.= ".ui-dropdownchecklist-dropcontainer {";
		$styleX.= $drpDownStyle;
		$styleX.= "}";
		$styleX.= "</style>";
		
		echo $styleX;
	}
	
	if($elemId=='category') 
	{
		$placeHolder = "Select Category";
	} else 
	{
		$placeHolder = "Select Groups";
	}

	/*$str='<script type="text/javascript">';
	$str .='$(document).ready(function() {';
	$str .='$("#'.$elemId.'").select2({
			placeholder: "'.$placeHolder.'",
			allowClear: true,
			multiple: true,
        	closeOnSelect: false
			
			});
		});
	 </script>';*/
	 
   $elemIdNew = ($arrValues['multiple'] == 'No') ? $elemId : $elemId.'[]' ;
   $optionDisableStatus = ($arrValues['crm_groups'] == "N") ? "disabled" : "";
   $groupPublicShare = '';	
   $str .="<select multiple='multiple' name='{$elemIdNew}' class='custom-select w-250' id='{$elemId}'>";
	foreach($valArr as $key=>$value){
		if(array_key_exists($key,$selVal)){
			$selected="selected='selected'";
		}
			$groupvalue = @explode("|", $value);
			$str .="<option value='{$key}' {$selected} {$optionDisableStatus} label='{$groupvalue[0]}'>{$groupvalue[0]}</option>";
			$selected = "";			
			if($groupvalue[1] == 'Public' || $groupvalue[1] == 'Share')
				$groupPublicShare.= $key.',';
	}
	
	$str .= "</select> <input type='hidden' name='availableGroups' id='availableGroups' value='".trim($groupPublicShare,',')."'>";
	if($arrValues['callType'] != 'Category')
		$str .= "<input type='hidden' name='otherPrivateGroupIds' id='otherPrivateGroupIds' value='".$arrValues['otherPrivateGroupIds']."'>";
		
	echo $str;
	}
	
	function getDepartments($selName, $selValue, $deptClass, $owner='', $NavFrom='',$onchange = '', $frmJO = '', $disable_sel='',$companyid='',$isMultiple='')
	{
		global $maildb,$db,$username;
		$dept_user = $username;
		if($owner!='')
			$dept_user = $owner;
		
		$deptAccesSno = "";
		if($frmJO == 'yes'){
			$deptAccessObj = new departmentAccess();
			$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'FO'");
			$deptAccesSno = " AND dp.sno IN(".$deptAccesSno.") ";
			$permissionCondition = "";
		}
		else{
			if ($selName == "deptassignment") {
				$deptAccessObj = new departmentAccess();
				$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
				$deptAccesSno = " AND dp.sno IN(".$deptAccesSno.") ";
				$permissionCondition = "";
			}else{
				$deptAccessObj = new departmentAccess();
				$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'FO','BO'");
				$deptAccesSno = " AND dp.sno IN(".$deptAccesSno.") ";
				//$permissionCondition = "AND FIND_IN_SET('".$dept_user."',permission)>0";
				$permissionCondition = "";
			}
		}
		
		if(trim($selValue) == '' || $selValue == '0'){ 
			//if selected dept id not there, then take default department id.
			$sel_department = "SELECT sno FROM department WHERE deflt = 'Y'";
			$res_department =  mysql_query($sel_department,$db);
			$row_department =  mysql_fetch_row($res_department);
			$selValue = $row_department[0];
			$sel_department = "SELECT dp.sno ,dp.deptname,'' FROM staffoppr_cinfo  LEFT JOIN department dp ON staffoppr_cinfo.deptid=dp.sno WHERE  dp.status ='Active' and staffoppr_cinfo.sno={$companyid} ";
			$res_department =  mysql_query($sel_department,$db);
			$row_department =  mysql_fetch_row($res_department);
			$selValue = $row_department[0];
			if (AKKUPAY_WITH_SYMMETRY == "Y") {

				if ($companyid !="") {
					$sel_department = "SELECT dp.sno,dp.deptname,'' FROM department dp  WHERE  dp.status ='Active'  ".$permissionCondition.$deptAccesSno ;
				}else{
					$sel_department = "SELECT dp.sno,dp.deptname,'' FROM department dp  WHERE  dp.status ='Active' ".$permissionCondition.$deptAccesSno ;
					
			
				}
			}else{
				$sel_department = "SELECT dp.sno,dp.deptname,'' FROM department dp WHERE dp.status ='Active'".$permissionCondition.$deptAccesSno ;
			}
			
		}
		else{
			if (AKKUPAY_WITH_SYMMETRY == "Y") {
				if ($companyid !="") {
					$sel_department = "SELECT dp.sno,dp.deptname,IF((dp.status = 'Active' ".$permissionCondition."),'Active','Deleted') FROM department dp JOIN company_setup cs ON dp.company_id = cs.sno WHERE dp.sno ='".$selValue."' OR (dp.status = 'Active' ".$permissionCondition.")   AND cs.sno={$companyid} ".$deptAccesSno;
				}else{
					$sel_department = "SELECT dp.sno,dp.deptname,IF((dp.status = 'Active' ".$permissionCondition."),'Active','Deleted') FROM department dp JOIN company_setup cs ON dp.company_id = cs.sno WHERE dp.sno ='".$selValue."' OR (dp.status = 'Active' ".$permissionCondition.") ".$deptAccesSno;
				}
			}else{
				if ($companyid !="") {
					$sel_department = "SELECT dp.sno,dp.deptname,IF((dp.status = 'Active' ".$permissionCondition."),'Active','Deleted') FROM department dp WHERE dp.sno ='".$companyid."' OR (dp.status = 'Active' ".$permissionCondition.") ".$deptAccesSno;
				}else{
					$sel_department = "SELECT dp.sno,dp.deptname,IF((dp.status = 'Active' ".$permissionCondition."),'Active','Deleted') FROM department dp WHERE dp.sno ='".$selValue."' OR (dp.status = 'Active' ".$permissionCondition.") ".$deptAccesSno;
				}
			}
		}
		$res_department =  mysql_query($sel_department,$db);
			
		if($NavFrom != '' && $NavFrom != 'CRMBulkUpdate' && mysql_num_rows($res_department ) <= 0)
		{
			if (AKKUPAY_WITH_SYMMETRY == "Y") {
				$sel_department = "SELECT dp.sno,dp.deptname,dp.company_id FROM department dp JOIN company_setup cs ON dp.company_id = cs.sno WHERE dp.deflt = 'Y' AND cs.sno={$companyid} ";
			}else{
				$sel_department = "SELECT dp.sno,dp.deptname FROM department dp WHERE dp.deflt = 'Y'";
			}
			$res_department =  mysql_query($sel_department,$db);
		}	
		
		return $sel_department;
	} 
	
	require_once('class.CommissionRoles.php');
?>