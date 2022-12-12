<?php 
	require("global.inc");
	require("dispfunc.php");
	require("comp_dataConv.php");
	require("cont_dataConv.php");
	require($akken_psos_include_path.'commonfuns.inc');
	require_once($akken_psos_include_path.'defaultTaxes.php');
	require_once("multipleRatesClass.php");
	$ratesObj = new multiplerates();
	include_once('custom/custome_save.php');
	$udmObj = new userDefinedManuplations();				

	$OBJ_Comp_Role = new CommissionRoles($db, $username, 'comp', 'CRMCompany');
	$coinfo=explode("|",$companyinfo);
	$opinfo=explode("|",$opprinfo);
   	$coinfo[0] = preg_replace('/(\\\+)/m', '',$coinfo[0]);
	$coinfo[0] = addslashes($coinfo[0]);
	//To get the sno of the company
	$sno = $_REQUEST['addr'];	
    	$mode_rate_type = "company"; 
		
	// Get custom defined values in the grid
	if($companyMode == 'Edit'){
		
		//Query for getting the customized Fields
		$query_order_column = "SELECT cgucols.column_order,cgcols.id, cgcols.custom_form_modules_id, cgcols.column_name, cgcols.db_col_name, cgcols.ref_table, cgcols.ref_column_name, cgcols.ref_target_column_name, cfm.primary_table, cgcols.grid_logic, cgcols.db_col_type, cgcols.col_alias, cgcols.search_logic, cgcols.grid_logic_with_leftjoin, cgcols.allow_leftjoin
									FROM udv_grids cg
									LEFT JOIN udv_grids_usercolumns cgucols ON cgucols.custom_grid_id = cg.id
									LEFT JOIN udv_grid_columns cgcols ON cgcols.id = cgucols.custom_grid_column_id
									LEFT JOIN udf_form_modules cfm ON cfm.module_id = cgcols.custom_form_modules_id
									WHERE cg.`custom_form_modules_id` = 2 
									AND cg.cuser =$username
									AND cgcols.udfstatus =1
									AND cgcols.allow_on_grid =1
									ORDER BY cgucols.column_order";								
		$query_order_query=mysql_query($query_order_column,$db);
		
		$index_arr			= array();	//Array declared for maintaining column name of the Customized columns
		$udf_cols			= array();	//Array for storing the column name of the UDF columns
		
		if(mysql_num_rows($query_order_query) > 0){
						
			while($result = mysql_fetch_array($query_order_query)){	
				
				//If condition for filtering the UDF columns
				if($result['db_col_name'] == 'sno' && $result['ref_column_name'] == 'rec_id')
				{					
					//Getting the UDF column name and order id of the column
					$index_arr[$result['ref_target_column_name']] = $result['column_order']+1;
					$udf_cols[] = ",t3.".$result['ref_target_column_name'];
				}	
				else
				{
					//Getting the columns other than UDF
					$index_arr[$result['db_col_name']] = $result['column_order']+1;			
				}
			}					
		}else{			
		
			//Array for the default Grid Columns	
			$Default_grid = array('cname','phone','city','state','accessto','compstatus','owner','cdate','mdate');
	
			//Converting the Array to start the Key from 1 instead of 0
			$key_val_change = array_combine(range(1, count($Default_grid)), array_values($Default_grid));
			
			//flipping the Array to change keys to values
			$index_arr  = array_flip($key_val_change);
		}
		
		
		
	}
	// Ends here
		
	function getColVal($sno){
		global $username,$db;
		$sql="SELECT
		staffoppr_cinfo.sno,
		staffoppr_cinfo.cname,
		staffoppr_cinfo.phone,
		staffoppr_cinfo.state,
		IF (staffoppr_cinfo.accessto = 'ALL', 'Public',
		IF (staffoppr_cinfo.accessto = '".$username."', 'Private', 'Share')),
		users.name,
		'',
		staffoppr_cinfo.owner,
		".tzRetQueryStringDTime("staffoppr_cinfo.cdate","Date","/").",
		staffoppr_cinfo.acc_comp,
		".tzRetQueryStringDTime("staffoppr_cinfo.mdate","Date","/").",
		staffoppr_cinfo.city,
		manage.name
		FROM users,staffoppr_cinfo LEFT JOIN manage ON staffoppr_cinfo.compstatus=manage.sno
		WHERE users.username = staffoppr_cinfo.owner AND staffoppr_cinfo.status = 'ER'
		AND (staffoppr_cinfo.owner = '".$username."' OR FIND_IN_SET( '".$username."', staffoppr_cinfo.accessto ) >0 OR staffoppr_cinfo.accessto = 'ALL') and crmcompany='Y' and staffoppr_cinfo.sno='".$sno."'"; 	
		$rs = mysql_query($sql,$db);
		//$numrows = mysql_num_rows($rs);
		$row = mysql_fetch_array($rs);
		$rowRes['company_value'] = $row[1];
		$rowRes['main_phone_value'] = $row[2];
		$rowRes['city_value'] = $row['city'];		
		$rowRes['state_value'] = $row[3];
		$rowRes['type_value'] = $row[4];
		$rowRes['status_value'] = $row[12];
		$rowRes['owner_value'] 	= $row[5];
		$rowRes['created_value'] = $row[8];		
		$rowRes['modified_value'] = $row[10];
		$rowRes['sno'] = $row[0];	//Get Contact sno	
		return $rowRes;
	}


	//get details and update the grid  without refresh...-prasadd
	function getGridNewRows($sno,$index_arr)
	{
		global $username,$maildb,$db,$user_timezone;

		$tot_comp_data = array();

		$sql="SELECT
		staffoppr_cinfo.sno,
		staffoppr_cinfo.cname,
		staffoppr_cinfo.phone,
		staffoppr_cinfo.state,
		IF (staffoppr_cinfo.accessto = 'ALL', 'Public',IF (staffoppr_cinfo.accessto = '".$username."', 'Private', 'Share')),
		users.name,'',staffoppr_cinfo.owner,".tzRetQueryStringDTime("staffoppr_cinfo.cdate","Date","/").",staffoppr_cinfo.acc_comp,".tzRetQueryStringDTime("staffoppr_cinfo.mdate","Date","/").", staffoppr_cinfo.city,manage.name
		FROM users,staffoppr_cinfo LEFT JOIN manage ON staffoppr_cinfo.compstatus=manage.sno
		WHERE users.username = staffoppr_cinfo.owner AND staffoppr_cinfo.status = 'ER'
		AND (staffoppr_cinfo.owner = '".$username."' OR FIND_IN_SET( '".$username."', staffoppr_cinfo.accessto ) >0 OR staffoppr_cinfo.accessto = 'ALL') and crmcompany='Y' and staffoppr_cinfo.sno='".$sno."'"; 		
		$rs = mysql_query($sql,$db);
		$numrows = mysql_num_rows($rs);
		$row = mysql_fetch_array($rs);
		if($numrows > 0)
		{
			$tot_comp_data[0] = "<input type=checkbox name=auids[] OnClick=chk_clearTop() value='".$row[0]."|".$row[7]."|".$row[5]."|0|".html_tls_specialchars($row[1],ENT_QUOTES)."|".$row[9]."'>";
			$tot_comp_data[1]=convertSpecCharsGlobal($row[1]);
			$tot_comp_data[2]=convertSpecCharsGlobal($row[2]);
			$tot_comp_data[3]=convertSpecCharsGlobal($row[11]);
			$tot_comp_data[4]=convertSpecCharsGlobal($row[3]);
			$tot_comp_data[5]=convertSpecCharsGlobal($row[4]);
			$tot_comp_data[6]=convertSpecCharsGlobal($row[12]);
			$tot_comp_data[7]=convertSpecCharsGlobal($row[5]);
			$tot_comp_data[8]=convertSpecCharsGlobal($row[8]);
			$tot_comp_data[9]=convertSpecCharsGlobal($row[10]);
			$tot_comp_data[10]='';
			$tot_comp_data[11]="viewcompanySummary.php?addr=".$row[0];
            
			
			$grid = $tot_comp_data[0]."|akkenSplit|";
			$grid.=$tot_comp_data[1]."|akkenSplit|";
			$grid.=$tot_comp_data[2]."|akkenSplit|";
			$grid.=$tot_comp_data[3]."|akkenSplit|";
			$grid.=$tot_comp_data[4]."|akkenSplit|";
			$grid.=$tot_comp_data[5]."|akkenSplit|";
			$grid.=$tot_comp_data[6]."|akkenSplit|";
			$grid.=$tot_comp_data[7]."|akkenSplit|";
			$grid.=$tot_comp_data[8]."|akkenSplit|";
			$grid.=$tot_comp_data[9]."|akkenSplit|";
			$grid.=$tot_comp_data[10]."|akkenSplit|";
			$grid.=$tot_comp_data[11]."|akkenSplit|";
            $impArr = getColVal($sno);			
			return $impArr;			
		}
		else
		{
			return "";
		}
	}

	//Function for updating contacts like source or spouse or reports to into Customer contact
	function updateAccSpouseSourceReport1($contSno,$opprSno)
	{
		    global $username,$maildb,$db;
    
		    $spouse_upd="update staffacc_contact a,staffoppr_contact b set a.fname=b.fname,a.muser=b.muser,a.mdate=b.mdate where a.sno='".$contSno."' and b.sno='".$opprSno."'";
		    mysql_query($spouse_upd,$db);
		    return;
	}

	//Function for inserting contacts like source or spouse or reports to into Customer contact
	function insertAccSpouseSourceReport1($opprSno)
	{
		    global $username,$maildb,$db;
    
		    $spouse_que="insert into staffacc_contact(fname,mname,lname,createdby,status,cdate,acccontact,owner,muser,mdate,deptid) select fname,mname,lname,'".$username."',status,NOW(),'N','".$username."','".$username."',NOW(),deptid from staffoppr_contact where sno='".$opprSno."'";
		    mysql_query($spouse_que,$db);
		    $spouse_id=mysql_insert_id($db);
		    return $spouse_id;
	}

	//------------------For Updating the Customer Company Details(while updating a CRM Company)------------START
	function updateExistAccCompany($Crm_Comp_Sno,$comp_burden_details)
	{
		global $username,$maildb,$db;

		$sel_qry="select '','',state,country,zip from staffoppr_cinfo where sno='".$Crm_Comp_Sno."' and acc_comp!='0'";
		$rel_qry=mysql_query($sel_qry,$db);
		$fth_qry=mysql_fetch_row($rel_qry);

		//Checking the state is available in state_codes table or not.
		$getstateVal = explode("|AKKENEXP|",getStateIdAbbr($fth_qry[2],$fth_qry[3]));

		$accStateId = $getstateVal[2];

		if($getstateVal[2] == 0)
			$accState = addslashes($getstateVal[0]);
		else
			$accState = addslashes($getstateVal[1]);

		$que="update staffacc_cinfo a,staffoppr_cinfo b set a.ceo_president=b.ceo_president,a.cfo=b.cfo,a.sales_purchse_manager=b.sales_purchse_manager,a.cname=b.cname,a.curl=b.curl,a.address1=b.address1,a.address2=b.address2,a.city=b.city,a.stateid='".$accStateId."',a.country=b.country,a.zip=b.zip,a.ctype=b.ctype,a.csize=b.csize,a.nloction=b.nloction,a.nbyears=b.nbyears,a.nemployee=b.nemployee,a.com_revenue=b.com_revenue,a.federalid=b.federalid,a.bill_req=b.bill_req,a.service_terms=b.service_terms,a.compowner=b.compowner,a.compbrief=b.compbrief,a.compsummary=b.compsummary,a.compstatus=b.compstatus,a.dress_code=b.dress_code,a.tele_policy=b.tele_policy,a.smoke_policy=b.smoke_policy,a.parking=b.parking,a.park_rate=b.park_rate,a.directions=b.directions,a.culture=b.culture,a.phone=b.phone,a.fax=b.fax,a.industry=b.industry,a.keytech=b.keytech,a.department=b.department,a.siccode=b.siccode,a.csource=b.csource,a.ticker=b.ticker,a.alternative_id=b.alternative_id,a.phone_extn=b.phone_extn,a.address_desc=b.address_desc,a.state='".$accState."', a.muser='".$username."', a.mdate=NOW() where b.sno=a.crm_comp and b.acc_comp=a.sno and b.sno='".$Crm_Comp_Sno."'";
		mysql_query($que,$db);
		
		$seqry = "select a.sno from staffacc_cinfo a,staffoppr_cinfo b where b.sno=a.crm_comp and b.acc_comp=a.sno and b.sno='".$Crm_Comp_Sno."'";
		$resqry = mysql_query($seqry,$db);
		$rowqry = mysql_fetch_row($resqry);
		$csno = $rowqry[0];
		
				
		//Updating the selected burden details to the accounting customer
		updLocBurdenDetails($csno,'acc',$comp_burden_details);

		//Dumping crm company data into hrcon_w4, empcon_w4 and new_w4 tables -- kumar raju k.
		dumpW4TableData($Crm_Comp_Sno,''); //Send these ( $opprCompId = staffoppr_cinfo.sno and $accCompId = staffacc_cinfo.username ) values to function.

		return;
	}

	function insUpdAccComp($arg)
	{
		global $username,$maildb,$db;

		$fth_qry[1] = $arg;

		$oppr_qry="SELECT username,sno FROM staffacc_cinfo WHERE crm_comp='".$fth_qry[1]."'";
		$oppr_exe_qry=mysql_query($oppr_qry,$db);
		$oppr_fch_qry=mysql_fetch_row($oppr_exe_qry);

		//Getting state and country form crm company table.
		$sel_opprQry = "SELECT state,country,zip FROM staffoppr_cinfo WHERE sno='".$fth_qry[1]."'";
		$sel_opprRes = mysql_query($sel_opprQry,$db);
		$sel_opprRow = mysql_fetch_row($sel_opprRes);

		//Checking the state is available in state_codes table or not.
		$getstateVal = explode("|AKKENEXP|",getStateIdAbbr($sel_opprRow[0],$sel_opprRow[1]));

		$accStateId = $getstateVal[2];

		if($getstateVal[2] == 0)
			$accState = addslashes($getstateVal[0]);
		else
			$accState = addslashes($getstateVal[1]);

		if($oppr_fch_qry[0]=="")
		{
			$query="insert into staffacc_list (nickname, approveuser, status, dauser, dadate, stime, type, bpsaid)  values ('','".$username."','ACTIVE','','0000-00-00',NOW(),'".$type."','')";
			mysql_query($query,$db);
                        $lastInsertId = mysql_insert_id($db);//Update username of the last inserted record
                        $manusername="acc".$lastInsertId;
                
                        $updateque="update staffacc_list set username='".$manusername."' where sno=".$lastInsertId;
                        mysql_query($updateque,$db);


			$qry_acccus="insert into staffacc_cinfo (username,ceo_president,cfo,sales_purchse_manager,cname,curl,address1,address2,city,stateid,country,zip,ctype,csize,nloction, nbyears,nemployee,com_revenue,federalid,bill_req,service_terms,compowner,compbrief,compsummary, compstatus,dress_code,tele_policy,smoke_policy,parking,park_rate,directions,culture,phone,fax,industry,keytech,department,siccode,csource,ticker,crm_comp,alternative_id,phone_extn,address_desc,state) select '".$manusername."', ceo_president , cfo , sales_purchse_manager , cname , curl , address1 , address2 , city , '".$accStateId."' , country , zip , ctype , csize , nloction , nbyears , nemployee , com_revenue , federalid ,  bill_req,service_terms,compowner,compbrief,compsummary,compstatus,dress_code,tele_policy,smoke_policy,parking, park_rate,directions,culture,phone,fax,industry,keytech,department,siccode,csource,ticker,sno,alternative_id,phone_extn,address_desc,'".$accState."' from staffoppr_cinfo where sno='".$fth_qry[1]."'";
			mysql_query($qry_acccus,$db);
			$qry_acccus_id=mysql_insert_id($db);

			$qry_upd_acc="update staffacc_cinfo set customerid='".$qry_acccus_id."', muser='".$username."', mdate=NOW() where sno='".$qry_acccus_id."'";
			mysql_query($qry_upd_acc,$db);
			$retVal = $manusername. "|". $qry_acccus_id;

			$hrmDeptID = getOwnerDepartment();
			setDefaultAccInfoAcc("customer",$db,$qry_acccus_id,$hrmDeptID);
			setDefaultEntityTaxes('Customer', $qry_acccus_id, $sel_opprRow[2], $hrmDeptID);

			$qry_upd_oppr="update staffoppr_cinfo set acc_comp='".$qry_acccus_id."' where sno='".$fth_qry[1]."'";
			mysql_query($qry_upd_oppr,$db);
		}
		else
		{
			$que="update staffacc_cinfo a,staffoppr_cinfo b set a.ceo_president=b.ceo_president,a.cfo=b.cfo,a.sales_purchse_manager=b.sales_purchse_manager,a.cname=b.cname,a.curl=b.curl,a.address1=b.address1,a.address2=b.address2,a.city=b.city,a.stateid='".$accStateId."',a.country=b.country,a.zip=b.zip,a.ctype=b.ctype,a.csize=b.csize,a.nloction=b.nloction,a.nbyears=b.nbyears,a.nemployee=b.nemployee,a.com_revenue=b.com_revenue,a.federalid=b.federalid,a.bill_req=b.bill_req,a.service_terms=b.service_terms,a.compowner=b.compowner,a.compbrief=b.compbrief,a.compsummary=b.compsummary,a.compstatus=b.compstatus,a.dress_code=b.dress_code,a.tele_policy=b.tele_policy,a.smoke_policy=b.smoke_policy,a.parking=b.parking,a.park_rate=b.park_rate,a.directions=b.directions,a.culture=b.culture,a.phone=b.phone,a.fax=b.fax,a.industry=b.industry,a.keytech=b.keytech,a.department=b.department,a.siccode=b.siccode,a.csource=b.csource,a.ticker=b.ticker,a.alternative_id=b.alternative_id,a.phone_extn=b.phone_extn ,a.address_desc=b.address_desc,a.state='".$accState."', a.muser='".$username."', a.mdate=NOW() where b.sno=a.crm_comp and b.acc_comp=a.sno and b.sno='".$fth_qry[1]."'";
			mysql_query($que,$db);
			$retVal = $oppr_fch_qry[0]."|".$oppr_fch_qry[1];
		}
		return $retVal;
	}
	//------------------For Updating the Customer Company Details(while updating a CRM Company)------------END

	$Other_Field1="";
	$Rate_Field="";

	$compRoleVal = serialize($compcommEntity)."^|^".serialize($compcommRole)."^|^".serialize($compcommval)."^|^".serialize($compratetype)."^|^".serialize($comppaytype)."^|^".serialize($comproleOverWrite)."^|^".serialize($comproleEDisable);

	if($coinfo[44] != '')
	{
		$Other_Field=explode("^",$coinfo[44]);
		$Other_Field1=str_replace("-","|",$Other_Field[0]);
		$Rate_Field=$Other_Field[1];
	}

	if($coinfo[32]=="public")
	{
		$coinfo[32]='ALL';
	}
	else if($coinfo[32]=="private")
	{
		$coinfo[32]=$coinfo[31];
	}
	else if($coinfo[32]=="share")//if it is share
	{
		if($coinfo[50]=='Yes')
			$coinfo[47]=$coinfo[51].",".$coinfo[47];						

		if($coinfo[47]!='')
		{	
			$accessto = trim($coinfo[47], ",");    // shared employees list
			$compacc=trim(implode(",",array_unique(explode(",",$username.",".$accessto))),",");  
			$coinfo[32]=$compacc;  
		}
        else
		{
			$compacc=trim(implode(",",array_unique(explode(",",$username.",".$coinfo[31]))),",");  
			$coinfo[32]=$compacc;
		}
	}

	if($aa=="add")
	{
		$rateRowVals 		= $coinfo[55];
		$rateRowValsShifts	= $shiftIndexStr;
		$shiftsSelected		= $selectedcustomshifts;
		$sloc = explode("-",$coinfo[49]);
		
		if($comp_sum!=true)
		{	
			if($coinfo[15]!='' || $coinfo[0]!='' || $coinfo[1]!='' || $coinfo[2]!='' || $coinfo[3]!='' ||  $coinfo[4]!='' || $coinfo[5]!='' || $coinfo[6]!='' || $coinfo[7]!='' || $coinfo[8]!='0' || $coinfo[9]!='' || $coinfo[10]!='' || $coinfo[11]!='' || $coinfo[12]!='' || $coinfo[13]!='' || $coinfo[14]!='' || $coinfo[18]!='' || $coinfo[19]!='' || $coinfo[20]!='' || $coinfo[23]!='' || $coinfo[24]!='' || $coinfo[25]!='0' || $coinfo[26]!='0' || $coinfo[27]!='' || $coinfo[28]!='' || $_SESSION['oppsno'.$Rnd]!='' || $coinfo[50]!='')
			{
				$que1="insert into staffoppr_cinfo (sno,approveuser,industry,cname,curl,address1,address2,city,state,country,zip,ctype,csize,nloction,nbyears,nemployee,com_revenue,federalid,status,accessto,phone,fax,keytech,siccode,ticker,csource,department,parent,muser,mdate,cdate,owner,compowner,compbrief,compsummary,compstatus,bill_contact,bill_address,bill_req,service_terms,dress_code,tele_policy,smoke_policy,parking,park_rate,directions,culture, alternative_id,phone_extn,address_desc,deptid,ts_layout_pref) values ('','".$username."','".$coinfo[15]."','".$coinfo[0]."','".$coinfo[1]."','".$coinfo[2]."','".$coinfo[3]."','".$coinfo[4]."','".$coinfo[5]."','".$coinfo[6]."','".$coinfo[7]."','".$coinfo[8]."','".$coinfo[9]."','".$coinfo[10]."','".$coinfo[11]."','".$coinfo[12]."','".$coinfo[13]."','".$coinfo[14]."','ER','".$coinfo[32]."','".$coinfo[18]."','".$coinfo[19]."','".$coinfo[37]."','".$coinfo[23]."','".$coinfo[24]."','".$coinfo[25]."','".$coinfo[52]."','".$coinfo[27]."','".$username."',NOW(),NOW(),'".$coinfo[31]."','".$coinfo[34]."','".$coinfo[35]."','".$coinfo[36]."','".$coinfo[30]."','".$coinfo[48]."','".$sloc[1]."','".addslashes($coinfo[39])."','".addslashes($coinfo[40])."','".$coinfo[41]."','".$coinfo[42]."','".$coinfo[43]."','".$Other_Field1."','".$Rate_Field."','".$coinfo[45]."','".$coinfo[46]."','".$coinfo[50]."','".$coinfo[51]."','".$coinfo[53]."','".$coinfo[54]."','".$company_timesheet_layout_preference."')";
				mysql_query($que1,$db);
				$csno=mysql_insert_id($db);
				
				//Getting the Burden Details
				if(!isset($chkbtUserOverwrite) || $chkbtUserOverwrite == '')
				{
					$chkbtUserOverwrite = 'NO';
				}
					
				if(!isset($chkbillbtUserOverwrite) || $chkbillbtUserOverwrite == '')
				{
					$chkbillbtUserOverwrite = 'NO';
				}
				$burdenType_exp = explode("|",$burdenType);
				$bill_burdenType_exp = explode("|",$bill_burdenType);
				$comp_burden_details = $burdenType_exp[0]."|".$bill_burdenType_exp[0]."|".$chkbtUserOverwrite."|".$chkbillbtUserOverwrite;
				
				//Updating the selected burden details to the crm company
				updLocBurdenDetails($csno,'crm',$comp_burden_details);
				
				
				//Inserting user defined data while creating new company
				$udmObj->insertUserDefinedData($csno, 2); 
				
				$grid = getGridNewRows($csno);// to get new grid row...-prasadd
				

				if($coinfo[28]!='')
				{
					// Removes NON-ASCII characters
					$coinfo[28] = preg_replace('/[^(\x20-\x7F)\n]/',' ', $coinfo[28]);
				
					// Handles non-printable characters
					$coinfo[28] = preg_replace('/&#[5-6][0-9]{4};/',' ', $coinfo[28]);
					
					$Notes_Sql="INSERT INTO notes(contactid,cuser,type,notes,cdate) VALUES ('".$csno."', '".$username."','com','".$coinfo[28]."',NOW())";
					mysql_query($Notes_Sql,$db);
				}
				$OBJ_Comp_Role->updateEntityRoles($compRoleVal, $csno, 'insert');
				
				$type_order_id = $csno;
				$ratesObj->multipleRatesInsertion($rateType,$billableR,$mulpayRateTxt,$payratePeriod,$payrateCurrency,$mulbillRateTxt,$billratePeriod,$billrateCurrency,$taxableR);
				
				if(SHIFT_SCHEDULING_ENABLED=="Y") //executes only when shift selected
				{
					$ratesObj->multipleRatesInsertionForShifts($shiftrateType,$billableRShift,$mulpayRateTxtShift,$payratePeriodShift,$payrateCurrencyShift,$mulbillRateTxtShift,$billratePeriodShift,$billrateCurrencyShift,$taxableRShift);
				}
			}
			$opprsno = explode(",",$_SESSION['oppsno'.$Rnd]);
			$opprsnoLength = count($opprsno);
			for($i=0;$i<$opprsnoLength;$i++)
			{
				$updopprContacts = "update oppr_contacts set csno='".$csno."'where oppr_id='".$opprsno[$i]."'";
				mysql_query($updopprContacts,$db);
			}
			
		}
		else
		{

			$que="update staffoppr_cinfo set industry ='".$coinfo[15]."',cname='".$coinfo[0]."',curl='".$coinfo[1]."',address1='".$coinfo[2]."',address2='".$coinfo[3]."',city='".$coinfo[4]."',state ='".$coinfo[5]."',country ='".$coinfo[6]."',zip='".$coinfo[7]."',ctype='".$coinfo[8]."',csize='".$coinfo[9]."',nloction='".$coinfo[10]."',nbyears='".$coinfo[11]."',nemployee='".$coinfo[12]."',com_revenue='".$coinfo[13]."',federalid='".$coinfo[14]."',phone='".$coinfo[18]."',fax='".$coinfo[19]."',keytech='".$coinfo[20]."',siccode='".$coinfo[23]."',ticker='".$coinfo[24]."',csource='".$coinfo[25]."',department='".$coinfo[52]."',parent='".$coinfo[27]."',muser='".$username."',mdate=NOW(),crmcompany='Y',bill_contact='".$coinfo[48]."',bill_address='".$sloc[1]."',alternative_id ='".$coinfo[52]."',phone_extn='".$coinfo[53]."',address_desc='".$coinfo[55]."',deptid='".$coinfo[54]."',ts_layout_pref='".$company_timesheet_layout_preference."' where sno='".$formercmp_id."'";
			mysql_query($que,$db);
			
			//Getting the Burden Details
			if(!isset($chkbtUserOverwrite) || $chkbtUserOverwrite == '')
			{
				$chkbtUserOverwrite = 'NO';
			}
				
			if(!isset($chkbillbtUserOverwrite) || $chkbillbtUserOverwrite == '')
			{
				$chkbillbtUserOverwrite = 'NO';
			}
			$burdenType_exp = explode("|",$burdenType);
			$bill_burdenType_exp = explode("|",$bill_burdenType);
			$comp_burden_details = $burdenType_exp[0]."|".$bill_burdenType_exp[0]."|".$chkbtUserOverwrite."|".$chkbillbtUserOverwrite;
			
			//Updating the selected burden details to the company
			updLocBurdenDetails($formercmp_id,'crm',$comp_burden_details);

			$grid = getGridNewRows($con_compid);// to get updated grid row-prasadd
			
			//updating customer company details from crm company
			updateExistAccCompany($con_compid,$comp_burden_details);

			// for updating the cname and phone in the tasklist table
			$companysno="com".$con_compid;

			UpdateTaskList($companysno,'CRM->Companies');

			$csno=$formercmp_id;
			$OBJ_Comp_Role->updateEntityRoles($compRoleVal, $csno, 'update');
		}

		//set parent as sno of this company for all of its childrens
		if($Divisions!='' && $csno!='')
		{
  		    $ChildComps=explode(",",$Divisions);
			$cnt=count($ChildComps);
			for($i = 0;$i < $cnt ;$i++)
			{
				$Update_Div="update staffoppr_cinfo set parent='$csno'  where sno='".$ChildComps[$i]."'";
				mysql_query($Update_Div,$db);
			}
			session_unregister("Divisions");
		}

		//adding a new company details for a existing oppr
		//adding a new company details for a existing contact
		$opprque="update staffoppr_oppr set csno='".$csno."' where csno='0' and sno in (".$_SESSION['oppsno'.$Rnd].") and oppr_status='ACTIVE'";
		mysql_query($opprque,$db);

		if($_SESSION['oppsno'.$Rnd] != "")
		{
			$oppr_qry="SELECT csno,notes,cuser,stime FROM staffoppr_oppr WHERE sno IN (".$_SESSION['oppsno'.$Rnd].") AND notes!='' AND oppr_status='ACTIVE'
			UNION
			SELECT oppr.csno,his.notes,his.cuser,his.stime FROM staffoppr_oppr_his his, staffoppr_oppr oppr WHERE his.parid=oppr.sno AND his.notes!=oppr.notes AND his.parid IN (".$_SESSION['oppsno'.$Rnd].") AND his.notes!='' AND his.oppr_status='ACTIVE' GROUP BY his.notes";
       		$oppr_res=mysql_query($oppr_qry,$db);

			if($oppr_res)
			{
				$Mquery= "SELECT sno FROM manage WHERE type='Notes' AND name='Opportunity'";
				$MCurr_Res=mysql_query($Mquery,$db);
				$MCurr_Row=mysql_fetch_row($MCurr_Res);
				while($oppr_row=mysql_fetch_row($oppr_res))
				{
					// Removes NON-ASCII characters
					$oppr_row[1] = preg_replace('/[^(\x20-\x7F)\n]/',' ', $oppr_row[1]);
				
					// Handles non-printable characters
					$oppr_row[1] = preg_replace('/&#[5-6][0-9]{4};/',' ', $oppr_row[1]);
					
					$Notes_Sql= "INSERT INTO notes(contactid,cuser,type,notes,cdate,notes_subtype) VALUES ('".$oppr_row[0]."', '".$oppr_row[2]."','com','".addslashes($oppr_row[1])."','".$oppr_row[3]."','".$MCurr_Row[0]."')";
					mysql_query($Notes_Sql,$db);
					$rid=mysql_insert_id($db);

					$compid = 'com'.$oppr_row[0];

					$sqlAct="INSERT INTO cmngmt_pr(con_id,username,tysno,title,sdate,subject,lmuser,subtype) VALUES('".$compid."','".$oppr_row[2]."','".$rid."','Notes','".$oppr_row[3]."','".addslashes($oppr_row[1])."','".$oppr_row[2]."','Opportunity')";
					$resAct=mysql_query($sqlAct,$db);
				}
			}
		}

		//adding a new company details for a existing contact
		$que="update staffoppr_contact set csno='".$csno."' where csno='0' and sno in (".$_SESSION['insno1'.$Rnd].")";
		mysql_query($que,$db);

		//Changing the accessto for the contacts of this company using staffoppr_cinfo.sno -- kumar raju.
		$updQry="UPDATE staffoppr_contact SET accessto='".$coinfo[32]."' WHERE csno='".$csno."' AND status='ER' AND accessto!='".$username."'";
		mysql_query($updQry,$db);

		//For updating Company search column
		if($csno!='')
	        updatecomp_search($csno);

		//For updating contact search column
		updatecont_search($_SESSION['insno1'.$Rnd]);

		// Updating the company information for that contact in address book
		$que="update contacts set buswebsite='".$coinfo[1]."',offadd='".$coinfo[2]." ".$coinfo[3]."',offcity='".$coinfo[4]."',offstate='".$coinfo[5]."',offzip='".$coinfo[7]."',offcou='".$coinfo[6]."',company='".$coinfo[0]."',cphone='".$coinfo[18]."',cfax='".$coinfo[19]."' where type='prospect' and typeid in (".$_SESSION['insno1'.$Rnd].")";
		mysql_query($que,$db);

		session_unregister("comppageudf".$Rnd);
		
		session_unregister("edit_company".$Rnd);
		session_unregister("insno1".$Rnd);
		session_unregister("formercmp_id");
		
		session_unregister("comp_rates_details".$Rnd);
		session_unregister("comp_rates_shift_details".$Rnd);

		$que_crmcon="select csno,sno,IF(TRIM(CONCAT_WS(' ',fname,mname,lname))='',email,TRIM(CONCAT_WS(' ',fname,mname,lname))) from staffoppr_contact where csno='".$csno."' and status='ER'";
		$res_crmcon=mysql_query($que_crmcon,$db);
		while($row = mysql_fetch_row($res_crmcon))
			$hiddentxt.= "^".dispfdb($row[0])."|".dispfdb($row[1])."|".dispfdb($row[2]);

		if($mod=="contact")
		{
			$location=$coinfo[0];
			Header("Location:/BSOS/Marketing/Lead_Mngmt/regmanage.php?error=success&sno=$csno&location=".urlencode($location))."&Rnd=".$Rnd;
		}
		else
		{
			//Regarding Billing Information in Comapnies new/Edit------START(ANIL)
			$comp_qry = "select cname,address1,address2,city,state,status from staffoppr_cinfo where sno = ".$csno;
			$comp_res = mysql_query($comp_qry,$db);
			$comp_fetch = mysql_fetch_row($comp_res);

			$comp_addrs = '';
			if($comp_fetch[1] != '')
				$comp_addrs .= addslashes($comp_fetch[1]);
			if($comp_fetch[2] != '')
				$comp_addrs .= " ".addslashes($comp_fetch[2]);
			if($comp_fetch[3] != '')
				$comp_addrs .= " ".addslashes($comp_fetch[3]);
			if($comp_fetch[4] != '')
				$comp_addrs .= " ".addslashes($comp_fetch[4]);

			$comp_addr = dispfdb($comp_addrs);

			$comp_names = dispfdb(addslashes($comp_fetch[0]));
			$comp_status = $comp_fetch[5];

			$sel_cont = "select TRIM(CONCAT_WS('',fname,' ',lname,'(',IF(email='',nickname,email),')')),sno,fname,mname,lname from staffoppr_contact where csno =".$csno;
			$sel_res = mysql_query($sel_cont,$db);
			while($sel_row = mysql_fetch_row($sel_res))
			{
				$sel_name = $sel_row[2]." ".$sel_row[3]." ".$sel_row[4];
				$sel_contact .= html_tls_specialchars(addslashes($sel_name))."|";
				$sel_contact_sno .= $sel_row[1]."|";
			}
			$list_cont = trim($sel_contact,"|");
			$list_cont_sno = trim($sel_contact_sno,"|");
			//---------------END---------------------------

			//Parent information (Anil)start
			$par_qry = "select sno,cname,address1,address2,city,status,state from staffoppr_cinfo where sno = ".$csno;
			$par_res = mysql_query($par_qry,$db);
			$par_row = mysql_fetch_row($par_res);

			$par_var = '';
			if($par_row[2] != '')
				$par_var .= html_tls_specialchars(addslashes($par_row[2]));			   
			if($par_row[3] != '')
				$par_var .= ", ".html_tls_specialchars(addslashes($par_row[3]));			   
			if($par_row[4] != '')
				$par_var .= ", ".html_tls_specialchars(addslashes($par_row[4]));		   
			if($par_row[6] != '')
				$par_var .= " ".html_tls_specialchars(addslashes($par_row[6]));
			$par_data = $par_var;
			$par_status = $par_row[5];
			$par_cname  = html_tls_specialchars(addslashes($par_row[1]));
			
			if($par_cname == '' && $par_data == '')
				$par_cname = 'no address';

			$job_par = $par_cname." ".$par_data;

			if($jocomp_divs == 'yes')
			{
				if($Comp_Divisions == '')
				{
					$existlist = '';
					$query = "select sno from staffoppr_cinfo where parent = '".$Compsno."'";
					$res = mysql_query($query,$db);
					while($data= mysql_fetch_row($res))
					{
						if($existlist=='')
							$existlist=$data[0];
						else
							$existlist.=",".$data[0];   
					}

					if($existlist != '')
						$Comp_Divisions = $existlist.",".$csno;
					else
						$Comp_Divisions = $csno;
				}	
				else
				{
					$Comp_Divisions .= ",".$csno;
				}
			}

			if($joloc_divs == 'yes')
			{
				if($Jobloc_Comp_Divisions == '')
				{
					$existlist = '';
					$query = "select sno from staffoppr_cinfo where parent = '".$Compsno."'";
					$res = mysql_query($query,$db);
					while($data= mysql_fetch_row($res))
					{
						if($existlist=='')
							$existlist=$data[0];
						else
							$existlist.=",".$data[0];   
					}

					if($existlist != '')
						$Jobloc_Comp_Divisions = $existlist.",".$csno;
					else
						$Jobloc_Comp_Divisions = $csno;
				}
				else
				{
					$Jobloc_Comp_Divisions .=  ",".$csno;
				}
			}

			//Query to update contact when coming from placement page and JOb order page
			if($typecomp!='' && $contSno!='') 
			{
				$upd_sql="UPDATE staffoppr_contact SET fcompany=concat(fcompany,',',csno),csno='".$csno."',fcompany=TRIM(',' FROM fcompany) WHERE sno='".$contSno."'";
				mysql_query($upd_sql,$db);
				$set='add';
			}
			//End

			print "<script>
			if(window.opener.location.href.indexOf(\"Marketing/Candidates/revconreg6.php\")>0 || window.opener.location.href.indexOf(\"Marketing/Candidates/conreg6.php\")>0 || window.opener.location.href.indexOf(\"Activities/nonxmlhl/Candidates/conreg6.php\")>0 || window.opener.location.href.indexOf(\"nonxmlhl/Candidates/conreg6.php\")>0)
			{
				form=window.opener.document.conreg;
				Company=form.company;
				form.company.value='$csno';
				form.company_name.value='$coinfo[0]';
				form.name.value='$coinfo[0]';
				form.city.value='$coinfo[4]';
				form.state.value='$coinfo[5]';
				form.country.value='$coinfo[6]';
				self.close();
			}
			else if(window.opener.location.href.indexOf(\"Marketing/Candidates/revconreg10.php\")>0 || window.opener.location.href.indexOf(\"Marketing/Candidates/conreg10.php\")>0 ||  window.opener.location.href.indexOf(\"nonxmlhl/Candidates/conreg10.php\")>0)
			{
				form=window.opener.document.conreg;
				form.company1.value='$csno';
				form.company_name.value='$coinfo[0]';
				self.close();
			}
			else if(window.opener.location.href.indexOf('Sales/Req_Mngmt/jobSummary.php') > 0 || window.opener.location.href.indexOf('Sales/Req_Mngmt/joborder1.php') > 0 || window.opener.location.href.indexOf('Activities/nonxmlhr/neworder6.php') > 0 || window.opener.location.href.indexOf('Collaboration/Email/nonxmlhr/neworder1.php') > 0)
 			{

              	form=window.opener.document.conreg;
				Company=form.eclient;
				len = Company.options.length;
				Company.options.length = len + 1 ;
				Company.options[len].text ='$coinfo[0]';
				Company.options[len].value ='$csno';
				Company.options.selectedIndex = len;
				var mangers=window.opener.document.conreg.managers.value;
				window.opener.document.conreg.managers.value=mangers+'$hiddentxt';
				window.opener.getManager();
				Company.disabled=false;
			    Company.focus();
				self.close();
			}
			else if(window.opener.location.href.indexOf('Sales/Req_Mngmt/createjoborder.php') > 0 ||  window.opener.location.href.indexOf('Sales/Req_Mngmt/editjoborder.php') > 0 ||  window.opener.location.href.indexOf('Sales/Req_Mngmt/Joborder/editjoborder.php') > 0  ||  window.opener.location.href.indexOf('Admin/Jobp_Mngmt/Joborder/editjoborder.php') > 0)
			{
				form=window.opener.document.conreg;
				var toList = parent.window.opener.document.conreg;
				var url = 'companyResp.php?typecomp='+'$typecomp'+'&comsno='+'$csno'+'&contsno='+'$contSno'+'&set=$set';
				parent.window.opener.DynCls_Ajax_result(url,'$typecomp','','handlenewjob()');
				if(window.opener.document.getElementById('compcrfmstatus').value == 1)
					window.opener.joCompanyRateTypes('$csno', 'company');
				self.close();
			}
			else if(window.opener.location.href.indexOf('Marketing/Opportunities/addOpportunity.php') > 0 ||  window.opener.location.href.indexOf('Marketing/Opportunities/editOpportunity.php') > 0 )
			{
				form=window.opener.document.opprreg;
				var toList = window.opener.document.opprreg; 
				var url = 'companyResp.php?typecomp='+'$typecomp'+'&comsno='+'$csno'+'&contsno='+'$contSno'+'&set=$set';
				parent.window.opener.DynCls_Ajax_result(url,'$typecomp','','opprnewjob()');
				self.close();
			}			
			else if(window.opener.location.href.indexOf('/BSOS/Sales/Req_Mngmt/placement.php') >=0)
			{
				form=window.opener.document.resume;
				var toList = parent.window.opener.document.resume;
				var url = 'companyResp.php?typecomp='+'$typecomp'+'&comsno='+'$csno'+'&contsno='+'$contSno'+'&set=$set';
				parent.window.opener.DynCls_Ajax_result(url,'$typecomp','','handlenew()');
				self.close();
			}
			else if(window.opener.location.href.indexOf('Marketing/Lead_Mngmt/contactSummary.php') > 0)
			{
				try
				{
					window.opener.document.getElementById('newcomp'+'$CRM_FormerComp_Cnt').innerHTML='| <a class=crmsummary-contentlnk href=javascript:viewCrmCompany(\'$con_compid\',\'ER\')>$coinfo[0]</a>';"; 
					session_unregister('CRM_FormerComp_Cnt');
					print " self.close();
				}
				catch(err)
				{
					self.close();
				}
			}
			else if(window.opener.location.href.indexOf('/Marketing/Companies/')>=0)
			{
				if('$chk_comp' == 'yes')
				{
					var toList = window.opener.document.markreqman;
					toList.company_sno.value = '$csno';
					var oDiv = window.opener.document.getElementById('disp_comp');
					var odiv_chg = window.opener.document.getElementById('chgid');
					var odiv_compchg = window.opener.document.getElementById('comp_chgid');

					str = '$csno';
					str1 = '$comp_addr';
					str2 = '$comp_status';
					
					if('$comp_addr' == '')
					{
						oDiv.innerHTML = \"<a href=javascript:comp_func(".$csno.",'".$comp_status."') class=crm-select-link>".$comp_names."</a>\";					   
						odiv_compchg.innerHTML = '<span class=summaryform-formelement>(</span> <a class=crm-select-link href=javascript:bill_comp()>change </a>&nbsp;<a href=javascript:bill_comp()><img class=remind-delete-align src=/BSOS/images/crm/icon-srch.gif width=17 height=16  border=0 align=middle></a><span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class=crm-select-link href=javascript:do_compAdd()>new</a><span class=summaryform-formelement>&nbsp;)&nbsp;</span>';
					}
					else
					{
						oDiv.innerHTML = \"<a href=javascript:comp_func(".$csno.",'".$comp_status."') class=crm-select-link>".$comp_names."</a><span class=summaryform-formelement>&nbsp;-&nbsp;</span><span class=summaryform-nonboldsub-title>".$comp_addr."</span>\";					
						odiv_compchg.innerHTML = '<span class=summaryform-formelement>(</span> <a class=crm-select-link href=javascript:bill_comp()>change </a>&nbsp;<a href=javascript:bill_comp()><img class=remind-delete-align src=/BSOS/images/crm/icon-srch.gif width=17 height=16  border=0 align=middle></a><span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class=crm-select-link href=javascript:do_compAdd()>new</a><span class=summaryform-formelement>&nbsp;)&nbsp;</span>';
					}

					var oDiv_contact = window.opener.document.getElementById('disp');

					str3 = '$list_cont';
					var aColors = str3.split('|');
					var stack = new Array;
					stack = aColors;
					contact_size  = stack.length

					str4 = '$list_cont_sno';
					var bColors = str4.split('|');
					var cont_sno = new Array;
					cont_sno = bColors;
					contactsno_size  = cont_sno.length

					if('$list_cont_sno' == '')
					{
						toList.contact_sno.value = '';
						oDiv_contact.innerHTML = '<input type=hidden name=list_contact><a class=crm-select-link href=javascript:bill_cont() id=disp>select contact</a>';
						odiv_chg.innerHTML = '<a href=javascript:bill_cont()><img class=remind-delete-align src=/BSOS/images/crm/icon-srch.gif width=17 height=16  border=0 align=middle></a><span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class=crm-select-link href=javascript:donew_add()>new&nbsp;contact</a>';
					}
					else
					{
						toList.contact_sno.value = '';
						newselect = '<select name=list_contact onchange=chg_fun(this.value)>';
						newselect += '<option value=0>---Select Contact---</option>';

						for(i=0;i<contactsno_size;i++)
							newselect += '<option value='+cont_sno[i]+'>'+stack[i]+'</option>';

						newselect += '</select>&nbsp;';

						oDiv_contact.innerHTML = '';
						odiv_chg.innerHTML = '<span class=summaryform-formelement>(&nbsp;</span>'+newselect+'<a class=crm-select-link href=javascript:bill_cont()>change </a>&nbsp;<a href=javascript:bill_cont()><img class=remind-delete-align src=/BSOS/images/crm/icon-srch.gif width=17 height=16  border=0 align=middle></a><span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class=crm-select-link href=javascript:donew_add()>new</a><span class=summaryform-formelement>&nbsp;)&nbsp;</span>';
					}
					self.close();
				}
				else if('$new_par' == 'yes')
				{
					var toList = parent.window.opener.document.markreqman;
					var parent_chk = toList.parent.value
					toList.parent.value = '$csno';

					var oDiv_parent = window.opener.document.getElementById('par_current');
					var oDiv_new = window.opener.document.getElementById('newchk');

					oDiv_parent.innerHTML =\"<span class=summaryform-formelement>".$par_cname."&nbsp;".$par_data."</span>\";

					if(parent_chk == 0)
						oDiv_new.innerHTML ='<span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class=crm-select-link href=javascript:reset_editpar()>reset parent</a>';

					self.close();
				}
				else if('$new_divs' == 'yes')
				{ 
				    var toList = parent.window.opener.document.markreqman;
					str = '$csno';
					str1 = '$test_divs';
					window.opener.div_chk(str,str1);	
					self.close();
				}
				else if('$edit_divs' == 'yes')
				{ 
				    var toList = parent.window.opener.document.markreqman;
					str = '$csno';
					str1 = '$div_addr';
					window.opener.div_editchk(str,str1);	
					self.close();
				}
				else
				{
					var parwin=window.opener.location.href;
					if(window.opener.location.href.indexOf(\"/Marketing/Companies/Companies.php/\")== -1)
					{
						try
						{
							if(\"$grid\"!='')
								window.opener.doGridSearch('search');								
							else
								window.opener.doGridSearch('search');
						}
						catch(err)
						{
							if(window.opener && window.opener.doGridSearch!=undefined)
							{
								//this is commented out as this makes the summary window to minimize.
							}
							else
							{
								window.opener.location.href=parwin;
							}
						}
					}
					else
					{
						window.opener.location.href=parwin;
					}
				}
		}
		else if(window.opener)
		{ 
			var parwin=window.opener.location.href;
			window.opener.location.href=parwin;
		}
		window.location.href = '/BSOS/Marketing/Companies/companySummary.php?addr=$csno&candrn=$Rnd&Rnd=$Rnd';
		</script>";
		}
		exit();
	}
	else if($aa=="update")
	{
		$sloc 			= explode("-",$coinfo[49]);
		$rateRowVals 		= $coinfo[57];
		$rateRowValsShifts	= $shiftIndexStr;
		$shiftsSelected		= $selectedcustomshifts;
		
		$que="update staffoppr_cinfo set  industry ='".$coinfo[15]."',cname='".$coinfo[0]."',curl='".$coinfo[1]."',address1='".$coinfo[2]."',address2='".$coinfo[3]."',city='".$coinfo[4]."',state ='".$coinfo[5]."',country ='".$coinfo[6]."',zip='".$coinfo[7]."',ctype='".$coinfo[8]."',csize='".$coinfo[9]."',nloction='".$coinfo[10]."',nbyears='".$coinfo[11]."',nemployee='".$coinfo[12]."',com_revenue='".$coinfo[13]."',federalid='".$coinfo[14]."',phone='".$coinfo[18]."',fax='".$coinfo[19]."',keytech='".$coinfo[37]."',siccode='".$coinfo[23]."',ticker='".$coinfo[24]."',csource='".$coinfo[25]."',department='".$coinfo[54]."',parent='".$coinfo[27]."',muser='".$username."',compstatus='".$coinfo[30]."',owner='".$coinfo[31]."',compowner='".$coinfo[34]."',accessto='".$coinfo[32]."',compbrief='".$coinfo[35]."',compsummary='".$coinfo[36]."',bill_req='".$coinfo[39]."',service_terms='".addslashes($coinfo[40])."',dress_code='".$coinfo[41]."',tele_policy='".$coinfo[42]."',smoke_policy='".$coinfo[43]."',parking='".$Other_Field1."',park_rate='".$Rate_Field."',directions='".$coinfo[45]."',culture='".$coinfo[46]."',bill_contact='".$coinfo[48]."',bill_address='".$sloc[1]."',alternative_id ='".$coinfo[52]."',mdate=NOW(),phone_extn ='".$coinfo[53]."',address_desc='".$coinfo[55]."',deptid='".$coinfo[56]."',ts_layout_pref='".$company_timesheet_layout_preference."' where sno='".$addr."'";
		mysql_query($que,$db);
		
		//Getting the Burden Details
		if(!isset($chkbtUserOverwrite) || $chkbtUserOverwrite == '')
		{
			$chkbtUserOverwrite = 'NO';
		}
			
		if(!isset($chkbillbtUserOverwrite) || $chkbillbtUserOverwrite == '')
		{
			$chkbillbtUserOverwrite = 'NO';
		}
		$burdenType_exp = explode("|",$burdenType);
		$bill_burdenType_exp = explode("|",$bill_burdenType);
		$comp_burden_details = $burdenType_exp[0]."|".$bill_burdenType_exp[0]."|".$chkbtUserOverwrite."|".$chkbillbtUserOverwrite;
		
		//Updating the selected burden details to the company
		updLocBurdenDetails($addr,'crm',$comp_burden_details);
		
		//Inserting user defined data while creating new company
		$udmObj->insertUserDefinedData($addr, 2); 
		// to update the udf in customer tables
		$udfCustSql = "select acc_comp from staffoppr_cinfo where sno = $addr";
		$udfCustResult = mysql_query($udfCustSql, $db);
		if($udfCustResult)
		{
			$udfCustRow = mysql_fetch_assoc($udfCustResult);
			if($udfCustRow['acc_comp']!=0){
				$udmObj->insertUserDefinedData($udfCustRow['acc_comp'], 6);	
			}
		}
		$OBJ_Comp_Role->updateEntityRoles($compRoleVal, $addr, 'update');

		//updating customer company details from crm company
		updateExistAccCompany($addr,$comp_burden_details);

		// for updating the cname and phone in the tasklist table
		$companysno="com".$addr;

		UpdateTaskList($companysno,'CRM->Companies');

		//to be called to get updation level details and update grid accordingly...--prasadd
		$grid = getGridNewRows($addr,$index_arr);
		
		/////////////////////////////////////////////
		//imploding to get all the CDF columns comma seperated
		if($udf_cols !='')
			$implode_udf = implode("",$udf_cols);	
		else
			$implode_udf = "";
		
		//Query for getting the updated Company data which is used for Company Grid
		$sql_data="select staffoppr_cinfo.cname, staffoppr_cinfo.phone, staffoppr_cinfo.city, staffoppr_cinfo.state, IF(staffoppr_cinfo.accessto='ALL','Public',IF(staffoppr_cinfo.accessto='1','Private','Share')) as accessto, t1.name as compstatus, t2.name as owner, DATE_FORMAT(CONVERT_TZ(staffoppr_cinfo.cdate,'SYSTEM','EST5EDT'),'%m/%d/%Y') as cdate, DATE_FORMAT(CONVERT_TZ(staffoppr_cinfo.mdate,'SYSTEM','EST5EDT'),'%m/%d/%Y') as mdate, staffoppr_cinfo.address1, staffoppr_cinfo.address2, staffoppr_cinfo.alternative_id, (SELECT CONCAT(address1, ' ', address2, ' ', city, ' ', state) FROM staffoppr_location billcomp WHERE billcomp.sno = staffoppr_cinfo.bill_address) as bill_address, (select concat(fname, ' ', lname) bilname from staffoppr_contact where staffoppr_contact.sno = staffoppr_cinfo.bill_contact) as bill_contact, staffoppr_cinfo.compbrief, staffoppr_cinfo.compowner, staffoppr_cinfo.com_revenue, staffoppr_cinfo.csize, t4.name as csource, staffoppr_cinfo.compsummary, t5.name as ctype, t6.country_abbr as country, t7.name as approveuser, staffoppr_cinfo.sno, staffoppr_cinfo.department, staffoppr_cinfo.directions, staffoppr_cinfo.dress_code, staffoppr_cinfo.fax, staffoppr_cinfo.federalid, t8.deptname as deptid, staffoppr_cinfo.industry, staffoppr_cinfo.phone_extn, t9.name as muser, staffoppr_cinfo.nemployee, staffoppr_cinfo.nloction, staffoppr_cinfo.culture, staffoppr_cinfo.parking, staffoppr_cinfo.park_rate, t10.billpay_code as bill_req, staffoppr_cinfo.keytech, staffoppr_cinfo.service_terms, staffoppr_cinfo.siccode, staffoppr_cinfo.smoke_policy, staffoppr_cinfo.tele_policy, staffoppr_cinfo.ticker, staffoppr_cinfo.curl, staffoppr_cinfo.nbyears, staffoppr_cinfo.zip ".$implode_udf." 
		FROM staffoppr_cinfo 
		LEFT JOIN manage t1 ON staffoppr_cinfo.compstatus = t1.sno 
		LEFT JOIN users t2 ON staffoppr_cinfo.owner = t2.username 
		LEFT JOIN udf_form_details_companie_values t3 ON staffoppr_cinfo.sno = t3.rec_id 
		LEFT JOIN manage t4 ON staffoppr_cinfo.csource = t4.sno 
		LEFT JOIN manage t5 ON staffoppr_cinfo.ctype = t5.sno 
		LEFT JOIN countries t6 ON staffoppr_cinfo.country = t6.sno 
		LEFT JOIN users t7 ON staffoppr_cinfo.approveuser = t7.username 
		LEFT JOIN department t8 ON staffoppr_cinfo.deptid = t8.sno 
		LEFT JOIN users t9 ON staffoppr_cinfo.muser = t9.username 
		LEFT JOIN bill_pay_terms t10 ON staffoppr_cinfo.bill_req = t10.billpay_termsid
		LEFT JOIN staffoppr_location t11 ON staffoppr_cinfo.bill_address = t11.sno		 
		WHERE staffoppr_cinfo.sno='".$sno."' AND staffoppr_cinfo.status= 'ER' AND (staffoppr_cinfo.owner = '1' OR FIND_IN_SET( '1', staffoppr_cinfo.accessto ) >0 OR staffoppr_cinfo.accessto = 'ALL') AND staffoppr_cinfo.crmcompany='Y' AND 1=1 ORDER BY staffoppr_cinfo.mdate DESC ";
		
		$rs_data = mysql_query($sql_data,$db);
		$numrows = mysql_num_rows($rs_data);
		$row_data = mysql_fetch_assoc($rs_data);


		//To get the matching Keys from selected cutomized columns and the updated Company data
		$common_keys = array_intersect_key($row_data,$index_arr);
		
		//Array declared for storing the final output 
		$newArray	= array();
		
		//This matches the keys and returns result as Key from $index_arr and Value from  $common_keys
		foreach( $index_arr as $origKey => $value ){
		  // New key that we will insert into $newArray with
		  $newKey = stripslashes($common_keys[$origKey]);
		  $newArray[$value]= convertSpecCharsCRM($newKey);
		}

		//Converting the final Ouput Array to JSON format
		$griddata =  array2json($newArray);
		
		/////////////////////////////////////////////
			
		if($coinfo[28]!='')
		{
			// Removes NON-ASCII characters
			$coinfo[28] = preg_replace('/[^(\x20-\x7F)\n]/',' ', $coinfo[28]);
				
			// Handles non-printable characters
			$coinfo[28] = preg_replace('/&#[5-6][0-9]{4};/',' ', $coinfo[28]);
			
			//insert the notes data into 'notes' table
			$Notes_Sql="INSERT INTO notes(contactid,cuser,type,notes,cdate) VALUES ('".$addr."', '".$username."','com','".$coinfo[28]."',NOW())";
			mysql_query($Notes_Sql,$db);
		}

		//For updating Company search column
		updatecomp_search($addr);
	
		$varsnos='';
		$query="select sno,concat_ws(' ',fname,mname,lname),wphone,status,ctype from staffoppr_contact where  status='ER' and csno='".$addr."'";
		$res=mysql_query($query,$db);
		$update_contact_name="";
		$update_contact_phone="";
 		$update_contact_done="no";

		while($row=mysql_fetch_row($res))
		{
			if($varsnos=='')
				$varsnos="'".$row[0]."'";
			else
				$varsnos=",'".$row[0]."'";

			if($row[3]=='ER' &&	$row[4]=='DF' && $update_contact_done=="no")
			{
				$update_contact_name=$row[1];
				$update_contact_done="yes";
			}
		}

		if($varsnos!='')
		{
			$query="update contacts set company='".$coinfo[0]."',offadd='".$coinfo[2]."',offcity='".$coinfo[4]."',offstate ='".$coinfo[5]."',offcou ='".$coinfo[6]."',offzip='".$coinfo[7]."',cphone='".$coinfo[18]."',cfax='".$coinfo[19]."' where typeid in(".$varsnos.")";
			mysql_query($query,$db);
		}

		session_unregister("comppageudf".$Rnd);
		
		session_unregister("edit_company".$Rnd);
		session_unregister("insno1".$Rnd);
		session_unregister("comp_rates_details".$Rnd);
		session_unregister("comp_rates_shift_details".$Rnd);

		//updatnig the candidate_work  table info .....
		$cand_work="update candidate_work set cname='".$coinfo[0]."',city='".$coinfo[4]."' ,state='".$coinfo[5]."',country='".$coinfo[6]."' where csno=".$addr;
		mysql_query($cand_work,$db);

		//modifying the page6 value i.e adding the modified company info
		if($Row!="")
		{
			$page=explode("^",$_SESSION[candpage6.$candrn]);
 			$len=count($page);
			$temp="";
	 		for($i = 0;$i < $len ;$i++)
			{
				if($i==$Row)
				{
					$Tarr=explode("|",$page[$i]);
					if($temp=="")
						$temp.=$coinfo[0]."|".$Tarr[1]."|".$Tarr[2]."|".$Tarr[3]."|".$Tarr[4]."|".$coinfo[4]."|".$coinfo[5]."|".$coinfo[6]."|".$Tarr[8]."|".$Tarr[9];
					else
						$temp.="^".$coinfo[0]."|".$Tarr[1]."|".$Tarr[2]."|".$Tarr[3]."|".$Tarr[4]."|".$coinfo[4]."|".$coinfo[5]."|".$coinfo[6]."|".$Tarr[8]."|".$Tarr[9];
				}
				else
				{
					if($temp=="")
						$temp=$page[$i];
					else
						$temp.="^".$page[$i];
				}
			}
			$_SESSION[candpage6.$candrn]=$temp;
		}
		
		$type_order_id = $addr;
		$ratesObj->multipleRatesInsertion($rateType,$billableR,$mulpayRateTxt,$payratePeriod,$payrateCurrency,$mulbillRateTxt,$billratePeriod,$billrateCurrency,$taxableR);
		
		
		if(SHIFT_SCHEDULING_ENABLED=="Y") //executes only when shift selected
		{
			$ratesObj->multipleRatesInsertionForShifts($shiftrateType,$billableRShift,$mulpayRateTxtShift,$payratePeriodShift,$payrateCurrencyShift,$mulbillRateTxtShift,$billratePeriodShift,$billrateCurrencyShift,$taxableRShift);
		}

		$que_crmcone="select csno,sno,IF(TRIM(CONCAT_WS(' ',fname,mname,lname))='',email,TRIM(CONCAT_WS(' ',fname,mname,lname))) from staffoppr_contact where csno='".$addr."' and status='ER'";
		$res_crmcone=mysql_query($que_crmcone,$db);
		while($rowedit = mysql_fetch_row($res_crmcone))
		{
			$hiddentxts.= "^".dispfdb($rowedit[0])."|".dispfdb($rowedit[1])."|".dispfdb($rowedit[2]);
		}

		if($mod=="contact")
		{
		    print "<script>if(window.opener) window.close();</script>";
		}
		else
		{
			?>
			<script>
			var module = "<?php echo $module;?>";
		
			try
			{
			//storing the new page6 value
			if(window.opener.location.href.indexOf("Marketing/Candidates/revconreg6.php")>0 || window.opener.location.href.indexOf("Marketing/Candidates/conreg6.php")>0)
			{
				var form=window.opener.document.conreg;
				var candrn='<?php echo $candrn;?>';
				var com='candpage6'+candrn;
				for(i=0;i<form.elements.length;i++)
				{
					if(form.elements[i].name==com)
					{
						form.elements[i].value=	"<?=html_tls_entities($_SESSION[candpage6.$candrn])?>";
						break;
					}
				}
			}
			else if(window.opener.location.href.indexOf('Sales/Req_Mngmt/joborder1.php') > 0 || window.opener.location.href.indexOf('Activities/nonxmlhr/neworder6.php') > 0 || window.opener.location.href.indexOf('Collaboration/Email/nonxmlhr/neworder1.php') > 0)
			{
				if(window.name == "editcust")
				{
					var toList = window.opener.document.conreg.eclient;
					var eid = toList.selectedIndex;
					toList.options[eid].text  ="<?php echo $coinfo[0]; ?>";
					toList.options[eid].value  ="<?php echo $addr; ?>";
					var cid="<?php echo $addr; ?>";
					var managers=window.opener.document.conreg.managers.value;

					var sincou=managers.split("^");
					var newMang=new Array();
					if(managers!='')
					{
						for(i=0;i<sincou.length;i++)
						{
							ssincou=sincou[i].split("|");
							if(ssincou[0]!=cid)
								newMang.push(sincou[i]);
						}
					}
					managers=newMang.join("^");
					window.opener.document.conreg.managers.value=managers+"<?php echo $hiddentxts; ?>";
					window.opener.getManager();
					toList.focus();
				}
				self.close();
			}

			var parent=window.opener;

			//If coming from companies.
			if((parent) && (window.opener.location.href.indexOf("Marketing/Companies/")>=0))
			{
				if(window.opener.location.href.indexOf("Marketing/Companies/Companies.php/")== -1)
				{
				
					try
					{ 
					
						var company_value = "<?php echo $grid[company_value];?>";
						var phone_value = "<?php echo $grid[main_phone_value];?>";
						var city_value = "<?php echo $grid[city_value];?>";
						var state_value = "<?php echo $grid[state_value];?>";
						var type_value = "<?php echo $grid[type_value];?>";
						var status_value = "<?php echo $grid[status_value];?>";
						var created_value = "<?php echo $grid[created_value];?>";
						var owner_value = "<?php echo $grid[owner_value];?>";
						var modified_value = "<?php echo $grid[modified_value];?>";
		
						//Sending the Contact sno and the json format data to RefreshGridByLine function in order to update Company
						window.opener.RefreshGridByLine(<?php echo $sno ?>, <?php echo $griddata ?>);	
					}
					catch(err)
					{ 
						if(parent && parent.doGridSearch!=undefined)
						window.location.href="editmanage.php?addr=<?php echo $addr;?>&upst=yes&Rnd=<?=$Rnd?>&candrn=<?=$candrn?>&comp_stat=<? echo $comp_stat?>&par_stat=<?=$par_stat?>&module="+module;							
					}
				}
			}

			if('<?php echo $changeowner; ?>' == 'Yes' && '<?php echo $ownershare; ?>' == 'private')
				self.close();
			else
				window.location.href="editmanage.php?addr=<?php echo $addr;?>&upst=yes&Rnd=<?=$Rnd?>&candrn=<?=$candrn?>&comp_stat=<? echo $comp_stat?>&par_stat=<?=$par_stat?>&module="+module;
			}
			catch(e)
			{
				if('<?php echo $changeowner; ?>' == 'Yes' && '<?php echo $ownershare; ?>' == 'private')
					self.close();
				else
					window.location.href="editmanage.php?addr=<?php echo $addr;?>&upst=yes&Rnd=<?=$Rnd?>&candrn=<?=$candrn?>&comp_stat=<? echo $comp_stat?>&par_stat=<?=$par_stat?>&module="+module;
			
			}
			</script>
			<?php
		}
	}
	else if($aa=="delete")
	{
		$companyinfo=$companyinfo;
		$opprinfo=$opprinfo;
		$_SESSION['edit_company'.$Rnd]=$companyinfo."||".$_SESSION['oppsno'.$Rnd];
		$_SESSION["comp_rates_details".$Rnd] = $selratesdata;
		$_SESSION["comp_rates_shift_details".$Rnd] = $cap_separated_custom_shift_rates;
	
		$addrval=explode("|",$addr1);
		$addr=$addrval[1];  // for Company sno
	
		$addrc=$addrval[0];  // staffoppr_contact sno
	
		$que="update staffoppr_contact set status='INACTIVE' where sno='".$addrc."'";
		mysql_query($que,$db);
	
		$que1="update contacts set status='INACTIVE' where userid='".$username."' and type='prospect' and typeid='".$addrc."'";
		mysql_query($que1,$db);

		$_SESSION['compRole'.$Rnd] = serialize($compcommEntity)."^|^".serialize($compcommRole)."^|^".serialize($compcommval)."^|^".serialize($compratetype)."^|^".serialize($comppaytype)."^|^".serialize($comproleOverWrite)."^|^"."^|^".serialize($compcommissionLevel);

		$url="editmanage.php?addr=".$addr."&Row=".$Row."&Rnd=".$Rnd."&candrn=".$candrn."&newcust=".$newcust;
		$url1="addcompany.php?cfrm=newRepeat&Rnd=".$Rnd."&typecomp=".$typecomp."&contSno=".$contSno;

		if($addr>0)
		{
			$update_contact_name="";
			$query="select sno,concat_ws(' ',fname,mname,lname),wphone,status,ctype from staffoppr_contact where status='ER' and  csno='".$addr."'";
			$res=mysql_query($query,$db);
			$totalcontacts=mysql_num_rows($res);
			while($row=mysql_fetch_row($res))
			{
				if($row[4]=='DF')
				{
					$update_contact_name=$row[1];
					break;
				}
			}
		}
	}
?>
<script language=javascript>
if('<?php echo $addr; ?>'>0) //cmng from edit
{
	window.location.href="<?php echo $url?>";
}
else
{
	var openloc="<?php echo $url1?>";
	window.location.href=openloc;
}
</script>
