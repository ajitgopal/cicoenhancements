<?php 
	require("global.inc");

	require("dispfunc.php");
	require("comp_dataConv.php");
	require("cont_dataConv.php");
	require("createEmpFromCand.php");
	require("madisonfuns.php");
	require_once($akken_psos_include_path.'defaultTaxes.php');	
	require_once($akken_psos_include_path."CandHrmVenClass.php");
	
	$coinfo=explode("|",addslashes($companyinfo));
	$coinfo[0] = preg_replace('/(\\\+)/m', '',$coinfo[0]);
	$coinfo[0] = addslashes($coinfo[0]);
	$Other_Field1="";
	$Rate_Field="";
	$exp_contact=explode(",",$_SESSION['insno1'.$Rnd]);

	if(isset($compUdfVal)){ 
		$udfvalexp = explode("|",$compUdfVal);
		$newUdfVal = array();    
		foreach($udfvalexp as $key=>$value){
			$newUdfVal[] = 	$value;    	      
		}  
		$_SESSION['comppageudf'.$Rnd] = $newUdfVal;
	}
	$clientType="CUST";
	$bill_pay_terms_field = "bill_req";
	if($venfrm=='yes')
	{
		$clientType="CV";
		$bill_pay_terms_field = "ven_bill_terms";
	}	

	//For storing the value in type column of staffacc_cinfo table
	$custcvType = ((isset($venfrm) && $venfrm=='yes') && (isset($crm_select) && $crm_select=='no') && (isset($addr) && $addr!="")) ? "BOTH" : $clientType;
	$staffacc_cinfo_type = "";

	$getClaassId = "SELECT department_accounts.classid FROM department_accounts WHERE deptid = '".$coinfo[63]."' AND status = 'ACTIVE'";
	$resClaassId = mysql_query($getClaassId,$db);
	$rowClaassId = mysql_fetch_array($resClaassId);
	
	$coinfo[61] = $rowClaassId[0];
	
	if($venfrm != 'yes')
	{
		$getCompanyTaxes = $coinfo[43];
		$coinfo[43] = "";
		$updateTax = "";
		$custClassID = $coinfo[61];
		$venClassID = 0;
	}
	else
	{
		$updateTax = "tax='".$coinfo[43]."',";
		$custClassID = 0;
		$venClassID = $coinfo[61];
	}
	
	for($i=0; $i<count($exp_contact); $i++)
	{
		$exp_sep=explode("^^",$exp_contact[$i]);
		if($exp_sep[0]=='oppr')
		{
			if($_SESSION['oppr_ses'.$Rnd]=="")
				$_SESSION['oppr_ses'.$Rnd]=$exp_sep[1];
			else
				$_SESSION['oppr_ses'.$Rnd]=$_SESSION['oppr_ses'.$Rnd].",".$exp_sep[1];
		}
		else if($exp_sep[0]=='acc')
		{
			if($_SESSION['acc_ses'.$Rnd]=="")
				$_SESSION['acc_ses'.$Rnd]=$exp_sep[1];
			else
				$_SESSION['acc_ses'.$Rnd]=$_SESSION['acc_ses'.$Rnd].",".$exp_sep[1];
		}
	}
	
	$_SESSION['oppr_ses'.$Rnd] = implode(",",array_unique(explode(",",trim($_SESSION['oppr_ses'.$Rnd], ","))));
	$_SESSION['acc_ses'.$Rnd] = implode(",",array_unique(explode(",",trim($_SESSION['acc_ses'.$Rnd], ","))));

	//Secondary Billing Contacts Change
	$sec_bill_cons_str = 0;
	if(count($sec_bill_cons)>0)
	{
		$sec_bill_cons_str = implode(",", $sec_bill_cons);
	}

	function syncBilling($accno, $crmno)
	{
		global $db;

		$cque="SELECT ac.crm_cont FROM staffacc_contact ac LEFT JOIN staffacc_cinfo ai ON ac.sno=ai.bill_contact WHERE ai.sno=$accno";
		$cres=mysql_query($cque,$db);
		$crow=mysql_fetch_row($cres);
		$bill_contact=$crow[0];

		$lque="SELECT al.sno, al.ltype, al.crm_loc FROM staffacc_location al LEFT JOIN staffacc_cinfo ac ON al.sno = ac.bill_address WHERE ac.sno=$accno";
		$lres=mysql_query($lque,$db);
		$lrow=mysql_fetch_row($lres);
		if($lrow[2]>0)
		{
			$bill_address=$lrow[2];
		}
		else
		{
			if($lrow[1]=="com")
				$uque="UPDATE staffacc_location al, staffoppr_location ol, staffoppr_cinfo oc, staffacc_cinfo ac SET al.crm_loc = ol.sno WHERE ol.ltype='com' AND ol.csno=oc.sno AND ac.crm_comp = oc.sno AND al.ltype='com' AND al.csno=ac.sno AND al.sno=".$lrow[0];
			else if($lrow[1]=="con")
				$uque="UPDATE staffacc_location al, staffoppr_location ol, staffoppr_contact oc, staffacc_contact ac SET al.crm_loc = ol.sno WHERE ol.ltype='con' AND ol.csno=oc.sno AND ac.crm_cont = oc.sno AND al.ltype='con' AND al.csno=ac.sno AND al.sno=".$lrow[0];
			mysql_query($uque,$db);

			$que="SELECT crm_loc FROM staffacc_location WHERE sno=".$lrow[0];
			$res=mysql_query($que,$db);
			$row=mysql_fetch_row($res);
			$bill_address=$row[0];
		}

		if($crmno>0)
		{
			if($bill_address>0)
				mysql_query("UPDATE staffoppr_cinfo SET bill_address=$bill_address WHERE sno=$crmno",$db);

			if($bill_contact>0)
				mysql_query("UPDATE staffoppr_cinfo SET bill_contact=$bill_contact WHERE sno=$crmno",$db);
		}
	}

	function delopprcont($csno)
	{
		global $maildb,$db;
		$delqry = "delete from staffoppr_contact where csno = '".$csno."'"; 
		mysql_query($delqry,$db);
	}

	function companyRel($crmid,$accid)
	{
		global $maildb,$db,$username;

		//For making relation with staffoppr_cinfo and staffacc_cinfo
		$up_qry = "update staffoppr_cinfo set acc_comp = '".$accid."',customerid='".$accid."' where sno = ".$crmid;
		mysql_query($up_qry,$db);

		$up_qry = "update staffacc_cinfo set crm_comp = '".$crmid."', muser='".$username."', mdate=NOW() where sno = ".$accid;
		mysql_query($up_qry,$db);

		return;
	}

	function contactrelations($sourid,$repid,$compsno)
	{
		global $maildb,$db,$username;

		$queryCrmContExist = mysql_query("SELECT sno FROM staffoppr_contact WHERE acc_cont='".$sourid."'", $db);
		if(@mysql_num_rows($queryCrmContExist)== 0)
		{
			$acc_sel = "select email,prefix,fname,mname,lname,csno,wphone,hphone,mobile,fax,other,ytitle,ctype,suffix,nickname,cat_id,source,department,certifications,codes,keywords,messengerid,spouse,address1,address2,city,state,country,zipcode,sourcetype,reportto,wphone_extn,hphone_extn,other_extn,other_info,email_2,email_3,description,importance,source_name,reportto_name,spouse_name from staffacc_contact where sno='".$sourid."'";
			$acc_res = mysql_query($acc_sel,$db);
			$acc_row = mysql_fetch_row($acc_res);

			$accessto=getContactDomain($acc_row[0]);
			if($accessto!='ALL')
				$acctoval=$username;
			else
				$acctoval='ALL';

			$oppr_ins = "insert into staffoppr_contact (prefix,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,csno,ctype,accessto,suffix,nickname,cat_id,source,department,certifications,codes,keywords,messengerid,spouse,address1,address2,city,state,country,zipcode,owner,mdate,muser,sourcetype,reportto,acc_cont,stime,approveuser,wphone_extn,hphone_extn,other_extn,other_info,email_2,email_3,description,importance,source_name,reportto_name,spouse_name,deptid) select prefix,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,'".$compsno."',ctype,'".$acctoval."',suffix,nickname,cat_id,source,department,certifications,codes,keywords,messengerid,'".$exp_repid[1]."',address1,address2,city,state,country,zipcode,owner,mdate,muser,sourcetype,reportto,'".$sourid."',NOW(),'".$username."',wphone_extn,hphone_extn,other_extn,other_info,email_2,email_3,description,importance,source_name,reportto_name,spouse_name,deptid from staffacc_contact where sno='".$sourid."'";
			mysql_query($oppr_ins,$db);
			$oppr_ins_id = mysql_insert_id($db);

			//maintaning relation with contact tables
			$upcontqry = "update staffacc_contact set crm_cont = '".$oppr_ins_id."' where sno=".$sourid;
			mysql_query($upcontqry,$db);

			//Updating crm contact search data
			updatecont_search($oppr_ins_id);

			return $oppr_ins_id;
		}
		else
		{
			$rowCrmContExist = mysql_fetch_assoc($queryCrmContExist);
			return $rowCrmContExist['sno'];
		}
	}

	//function to add or update candidate with the company, and making relation to them
	function addCandidateToCompany($candusername,$resEmp)
	{
		global $maildb,$db;

		$empIds=$_SESSION['VconsultingEmpIds'];	 
		$empIds = (trim($empIds)=='')?$resEmp:$empIds.','.$resEmp; //Add contact candidates also

		$qryupdate= "update emp_list set lstatus = 'ACTIVE' where find_in_set(username,'".$empIds."') AND lstatus='DA'"; 
		mysql_query($qryupdate,$db);

		//This is to update the employee tax type to C-to-C - vijaya
		//Checking ustatus equal to active in hrcon_w4 table -- kumar raju k.
		$qryupdate_w4 = "UPDATE hrcon_w4 SET tax = 'C-to-C' WHERE find_in_set(username,'".$empIds."') and ustatus='active'";
		mysql_query($qryupdate_w4,$db);

		$qryupdate_ew4 = "UPDATE empcon_w4 SET tax = 'C-to-C' WHERE find_in_set(username,'".$empIds."')";
		mysql_query($qryupdate_ew4,$db);

		$qryupdate_nw4 = "UPDATE net_w4 SET tax = 'C-to-C' WHERE find_in_set(username,'".$empIds."') and ustatus='active'";
		mysql_query($qryupdate_nw4,$db);

		if(explode(',',$empIds)!=false)
		{
			$empIds=explode(',',$empIds);
			$empIdsCount=count($empIds);
		}
		else
		{
			$empIdsCount=1;
			$empIds[0]=$empIds;
		}

		for($i=0;$i<$empIdsCount;$i++)
		{
			$qrysel_cand="SELECT c.username FROM emp_list e, candidate_list c WHERE e.username ='".$empIds[$i]."' AND c.candid=concat('emp',e.sno) AND e.lstatus!='INACTIVE'";
			$res_cand = mysql_query($qrysel_cand,$db);
			$row_cand = mysql_fetch_row($res_cand);
			if($empIds[$i] && $row_cand[0])
			{				
				$qrychk="SELECT count(1) FROM vendorsubcon WHERE subid='".$row_cand[0]."' AND venid='".$candusername."' AND empid='".$empIds[$i]."'";
				$reschk=mysql_query($qrychk,$db);
				$rowschk=mysql_fetch_row($reschk);
				if(!$rowschk[0]>0)
				{
					$quevendsub="insert into vendorsubcon (sno,subid,venid,empid) values('','".$row_cand[0]."','".$candusername."','".$empIds[$i]."')";
					mysql_query($quevendsub,$db);

					$getSnoQry = "SELECT sno FROM staffacc_cinfo WHERE username = '".$candusername."'";
					$getSnoRes = mysql_query($getSnoQry,$db);
					$getSnoRow = mysql_fetch_row($getSnoRes);

					setVendorId($getSnoRow[0],'CV');
				}
                		/* sanghamitra:accounting to crm  - vendor details -- update the recruiter contact in candidate if vendor is created through accounting vendors ***/
				
			    	$crmHrmVenObj   = new CandHrmVendorClass();
		        	$crmHrmVenObj->updateRecruiterContFrmHrm($candusername,$row_cand[0]);
                         }else{ 
			               /* sanghamitra:accounting to crm  - vendor details -- update the recruiter contact in candidate if vendor is created through accounting vendors ***/
				       $attchedCandQry="SELECT v.subid,cl.supid,cl.username FROM vendorsubcon v LEFT JOIN candidate_list cl ON v.subid=cl.username WHERE v.venid ='".$candusername."' AND cl.status='ACTIVE'" ;	
				       $attchedCandRes = mysql_query($attchedCandQry,$db);
				       while($attchedCand = mysql_fetch_assoc($attchedCandRes))
		                       { 
				               if($attchedCand['supid'] == 0 ){
					               $crmHrmVenObj   = new CandHrmVendorClass();					        
					               $crmHrmVenObj->updateRecruiterContFrmHrm($candusername,$attchedCand['username']);
					        }
			                }
		           }
		}
	}

	if($coinfo[31] != '')
	{
		$Other_Field=explode("^",$coinfo[31]);
		$Other_Field1=str_replace("-","|",$Other_Field[0]);
		$Rate_Field=$Other_Field[1];
	}

	if($aa=="add")
	{ 
	
		if($comp_sum!=true)
		{		   
			if($coinfo[15]!='0' || $coinfo[0]!='' || $coinfo[1]!='' || $coinfo[2]!='' || $coinfo[3]!='' ||  $coinfo[4]!='' || $coinfo[5]!='' || $coinfo[6]!='0' || $coinfo[7]!='0' || $coinfo[8]!='' || $coinfo[9]!='' || $coinfo[10]!='' || $coinfo[11]!='' || $coinfo[12]!='' || $coinfo[13]!='' || $coinfo[14]!='' || $coinfo[18]!='' || $coinfo[19]!='' || $coinfo[20]!='' || $coinfo[23]!='' || $coinfo[24]!='' || $coinfo[25]!='' || $coinfo[26]!='' || $coinfo[27]!='' || $coinfo[28]!='' || $coinfo[29]!='' || $coinfo[30]!='' || $coinfo[31]!='' || $coinfo[32]!='' || $coinfo[33]!='' || $coinfo[34]!='0' || $coinfo[35]!='0' || $coinfo[16]!='0' || $coinfo[17]!='' || $coinfo[21]!='' || $coinfo[22]!='' || $_SESSION['oppsno'.$Rnd]!='' || $_SESSION['insno1'.$Rnd]!='' || $coinfo[38]!='')
			{
				if($coinfo[5] != '')
				{
					$state_arr = explode('^',$coinfo[5]);
					if($state_arr[0] == 'Other')
						$opprState = addslashes($coinfo[47]);
					else
						$opprState = addslashes($state_arr[0]);
				}
				else
				{
					$opprState = "";
				}

				$bill_req_value="";
				if($bill_pay_terms_field == "ven_bill_terms")
					$bill_req_value=",bill_req='".addslashes($coinfo[56])."'";
				
				 $que="update staffoppr_cinfo set industry ='".$coinfo[12]."',cname='".$coinfo[0]."',curl='".$coinfo[1]."',address1='".$coinfo[2]."',address2='".$coinfo[3]."',city='".$coinfo[4]."',state ='".$opprState."',country ='".$coinfo[7]."',zip='".$coinfo[6]."',ctype='".$coinfo[16]."',csize='".$coinfo[21]."',nloction='".$coinfo[13]."',nbyears='".$coinfo[14]."',nemployee='".$coinfo[11]."',com_revenue='".$coinfo[9]."',federalid='".$coinfo[19]."',phone='".$coinfo[8]."',fax='".$coinfo[10]."',keytech='".$coinfo[25]."',siccode='".$coinfo[17]."',ticker='".$coinfo[20]."',csource='".$coinfo[15]."',department='".$coinfo[44]."',muser='".$username."',compowner='".$coinfo[18]."',compbrief='".$coinfo[23]."',compsummary='".$coinfo[24]."'".$bill_req_value.",service_terms='".$coinfo[27]."',dress_code='".$coinfo[28]."',tele_policy='".$coinfo[29]."',smoke_policy='".$coinfo[30]."',parking='".$Other_Field1."',park_rate='".$Rate_Field."',directions='".$coinfo[32]."',culture='".$coinfo[33]."',alternative_id='".$coinfo[38]."', mdate=NOW(),phone_extn='".$coinfo[39]."',address_desc='".$coinfo[45]."',alternative_id='".$coinfo[38]."',ts_layout_pref='".$customer_timesheet_layout_preference."' where sno='".$srnum."'";
				mysql_query($que,$db);

				array_push($CRM_Comp_SNo_SDataUpdate_Array,$srnum);

				
				//No. of rows for accounts checking for relations
				$cust_sno=0;
				$crmrows = 0;
				if(!empty($srnum))
				{
					$crmacccomp = "select sno,username from staffacc_cinfo where crm_comp = '".$srnum."'";
					$crmqry = mysql_query($crmacccomp,$db);
					$crmrows = mysql_num_rows($crmqry);
					$crm_fecth=mysql_fetch_row($crmqry);
					$manusername=$crm_fecth[1];
				}
				
				if($crmrows == 0)
				{				 
					$query="insert into staffacc_list (nickname, approveuser, status, dauser, dadate, stime, type, bpsaid)  values ('','".$username."','ACTIVE','','0000-00-00',NOW(),'".$type."','')";
					mysql_query($query,$db);
                                        $last_list_id=mysql_insert_id($db); //Update username of the last inserted record
                                        $manusername="acc".$last_list_id;

                                        $upd_staffacc_list="UPDATE staffacc_list SET username='".$manusername."' WHERE sno='".$last_list_id."'";
                                        mysql_query($upd_staffacc_list,$db);

					$addState = '';

					if($coinfo[5] != '')
					{
						$state_arr = explode('^',$coinfo[5]);
						if($state_arr[0] == 'Other')
							$accState = ",'".addslashes($coinfo[47])."'";
						else
							$accState = ",'".addslashes($state_arr[0])."'";
						$accStateId = $state_arr[1];
						$addState = ",state";
					}
					else
					{
						$accState = "";
						$accStateId = "";
					}

					$billing_req_field="";
					$billing_req_val="";
					if($venfrm=='yes')
					{
						$billing_req_field=", bill_req";
						$billing_req_val=",'".$coinfo[56]."'";
					}

					$bill_loc_mod = end(explode("-", $bill_loc));
					
					$que1="insert into staffacc_cinfo (username,industry,cname,curl,address1,address2,city,stateid,country,zip,ctype,csize,nloction,nbyears,nemployee,com_revenue,federalid,phone,fax,keytech,siccode,ticker,csource,muser,mdate,owner,compowner,compbrief,compsummary,bill_contact,bill_address,".$bill_pay_terms_field.",service_terms,dress_code,tele_policy,smoke_policy, parking,park_rate,directions,culture,alternative_id,phone_extn,corp_code,tax,department,address_desc,billing_default,madison_customerid".$addState.$billing_req_field.",inv_method,inv_terms,cust_classid,vend_classid,templateid,attention,type,compstatus,inv_delivery_option,inv_email_templateid,sec_bill_contact,ts_layout_pref) values ('".$manusername."','".$coinfo[12]."','".$coinfo[0]."','".$coinfo[1]."','".$coinfo[2]."','".$coinfo[3]."','".$coinfo[4]."','".$accStateId."','".$coinfo[7]."','".$coinfo[6]."','".$coinfo[16]."','".$coinfo[21]."','".$coinfo[13]."','".$coinfo[14]."','".$coinfo[11]."','".$coinfo[9]."','".$coinfo[19]."','".$coinfo[8]."','".$coinfo[10]."','".$coinfo[25]."','".$coinfo[17]."','".$coinfo[20]."','".$coinfo[15]."','".$username."',NOW(),'".$username."','".$coinfo[18]."','".$coinfo[23]."','".$coinfo[24]."','".$billcontact_sno."','".$bill_loc_mod."','".addslashes($coinfo[26])."','".$coinfo[27]."','".$coinfo[28]."','".$coinfo[29]."','".$coinfo[30]."','".$Other_Field1."','".$Rate_Field."','".$coinfo[32]."','".$coinfo[33]."','".$coinfo[38]."','".$coinfo[39]."','".$coinfo[40]."','".$coinfo[43]."','".$coinfo[44]."','".$coinfo[45]."','".$coinfo[46]."','".$coinfo[48]."'".$accState.$billing_req_val.",'".$coinfo[57]."','".$coinfo[58]."','".$custClassID."','".$venClassID."','".$inv_temp."','".$attention."','".$custcvType."','".$compstatus."','".$coinfo[66]."','".$coinfo[67]."','".$sec_bill_cons_str."','".$customer_timesheet_layout_preference."')";

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
					
					//Updating the selected burden details to the accountign customer
					updLocBurdenDetails($csno,'acc',$comp_burden_details);
					
					$client_Acc=array("loc_id"=>$coinfo[49],"taxes_pay_acct"=>$coinfo[50],"acct_receive"=>$coinfo[51],"acct_payable"=>$coinfo[52],"typeid"=>$csno,"clienttype"=>$clientType,"acct_miscIncome"=>$coinfo[53],"acct_miscExpense"=>$coinfo[54],"deptid"=>$coinfo[63]);
					ClientAccoutnsTrans($client_Acc);

					if((($crm_select=='' && $srnum == '') || ($crm_select=='yes' && is_numeric($srnum))) && $clientType == "CV")
						setDefaultAccInfoAcc("customer",$db,$csno,$coinfo[63]);	

					setDefaultEntityTaxes('Customer', $csno, $coinfo[6], $coinfo[63]);

					$up_qry_acc = "update staffacc_cinfo set customerid = '".$csno."', muser='".$username."', mdate=NOW() where sno = '".$csno."'";
					mysql_query($up_qry_acc,$db);

					$up_qry_oppr = "update staffoppr_cinfo set customerid = '".$csno."' where sno = '".$srnum."'";
					mysql_query($up_qry_oppr,$db);					
					
					
					/////////////////// UDF migration to customers ///////////////////////////////////
					include_once('custom/custome_save.php');		
					$directEmpUdfObj = new userDefinedManuplations();
					$directEmpUdfObj->insertUserDefinedData($csno, 6);
					/////////////////////////////////////////////////////////////////////////////////
					
					// Sunil commented this code : if condition $_SESSION['insno1'.$Rnd]
					if($checkedcrm != "Y")
					{	
					
						$crm_cont="";
						$u_name = $_SESSION['username']; 
						if($srnum == '')
							$srnum = 0;
							
						$que2="select sno,suffix,nickname,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,csno,status,cat_id,accessto,ctype,wphone_extn,hphone_extn,other_extn from temp_staffoppr_contact where status='ER' and csno='".$srnum."' and username='".$u_name."' and crmcontact ='Y'";
						$res2=mysql_query($que2,$db);
						while($row2=mysql_fetch_row($res2))
						{
							if($crm_cont == "")
								$crm_cont = $row2[0];
							else
								$crm_cont .= ",".$row2[0];
						}
						
						//For dumping crm contacts data in to accountings
					
						$cont_qry = "select prefix,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,ctype,suffix,nickname,cat_id,source,department,certifications,codes,keywords,messengerid,spouse,address1,address2,city,state,country,zipcode,sourcetype,reportto,sno,wphone_extn,hphone_extn,other_extn,other_info,email_2,email_3,description,importance,source_name,reportto_name,spouse_name,sno from temp_staffoppr_contact where sno in (".$crm_cont.") and username='".$u_name."' and acc_cont='0' and status='ER'";
						$cont_res = mysql_query($cont_qry,$db);
						while($cont_row = mysql_fetch_row($cont_res))
						{
							$getstateVal = explode("|AKKENEXP|",getStateIdAbbr($cont_row[25],$cont_row[26]));
							$accConStateId = $getstateVal[2];
							if($getstateVal[2] == 0)
								$accConState = ",'".addslashes($getstateVal[0])."'";
							else
								$accConState = ",'".addslashes($getstateVal[1])."'";

							$addState = ",state";
							$hrmDeptID = getOwnerDepartment();							
						
							
							$que="insert into staffacc_contact (username,prefix,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,ctype,accessto,createdby,cdate,suffix,nickname,cat_id,source,department,certifications,codes,keywords,messengerid,spouse,address1,address2,city,stateid,country,zipcode,owner,mdate,muser,sourcetype,reportto,wphone_extn,hphone_extn,other_extn,other_info,email_2,email_3,description,importance,source_name,reportto_name,spouse_name,deptid".$addState.",csno) values ('".$manusername."','".$cont_row[0]."','".$cont_row[1]."','".$cont_row[2]."','".$cont_row[3]."','".$cont_row[4]."','".$cont_row[5]."','".$cont_row[6]."','".$cont_row[7]."','".$cont_row[8]."','".$cont_row[9]."','".$cont_row[10]."','".$cont_row[11]."','ALL','".$username."',now(),'".$cont_row[12]."','".$cont_row[13]."','".$cont_row[14]."','".$cont_row[15]."','".$cont_row[16]."','".$cont_row[17]."','".$cont_row[18]."','".$cont_row[19]."','".$cont_row[20]."','".$cont_row[21]."','".$cont_row[22]."','".$cont_row[23]."','".$cont_row[24]."','".$accConStateId."','".$cont_row[26]."','".$cont_row[27]."','".$username."',now(),'".$username."','".$cont_row[28]."','".$cont_row[29]."','".$cont_row[31]."','".$cont_row[32]."','".$cont_row[33]."','".$cont_row[34]."','".$cont_row[35]."','".$cont_row[36]."','".$cont_row[37]."','".$cont_row[38]."','".$cont_row[39]."','".$cont_row[40]."','".$cont_row[41]."','".$hrmDeptID."'".$accConState.",$csno)";
							mysql_query($que,$db);
							$acc_contid = mysql_insert_id($db); 

							//maintaning relation with contact tables
							$upcontqry = "update staffacc_contact set crm_cont = '".$cont_row[30]."', csno = ".$csno." where sno=".$acc_contid;
							mysql_query($upcontqry,$db);

							$upcontqry = "update staffoppr_contact set acc_cont  = '".$acc_contid."' where sno=".$cont_row[30];
							mysql_query($upcontqry,$db);
							
							if(!empty($billcontact_sno) && ($billcontact_sno == $cont_row[30]))
								$bill_contact_new = $acc_contid;							
							
						}
						
						$delQry = "delete from temp_staffoppr_contact where username='".$u_name."'";
						mysql_query($delQry,$db);
						
						
						$upd_custbill = "update staffacc_cinfo set bill_contact = ".$bill_contact_new." where sno = ".$csno;
						mysql_query($upd_custbill,$db);
						
					}
				
					$up_qry = "update staffoppr_cinfo set acc_comp = '".$csno."' where sno = ".$srnum;
					mysql_query($up_qry,$db);

					$up_qry = "update staffacc_cinfo set crm_comp = '".$srnum."', muser='".$username."', mdate=NOW() where sno = ".$csno;
					mysql_query($up_qry,$db);
				
					
					if($chk_bilcompaddr == 'frm_crm')
					{
						$bill_loc_mod = explode("-", $bill_loc);
						
						
						if($bill_loc_mod[0] == 'com' || $bill_loc_mod[0] == 'loc')
							$sel_loc_comp = "select csno from staffoppr_location where sno = ".$bill_loc_mod[1];
						else
							$sel_loc_comp = "SELECT t1.csno FROM staffoppr_contact t1, staffoppr_location t2 WHERE t1.sno = t2.csno AND t2.sno = ".$bill_loc_mod[1];
						
						$res_loc_comp = mysql_query($sel_loc_comp, $db);
						$rec_loc_comp = mysql_fetch_array($res_loc_comp);
						
						insertAllCRMLocations_to_Accounting($srnum, $csno, $username, $db);					
						
						if($rec_loc_comp[0] != $srnum)
						{
							$loc_arr = explode("-", $bill_loc);
							
							if($bill_loc_mod[0] == 'com' || $bill_loc_mod[0] == 'loc')
								$sel_comp_loc = "SELECT t2.sno, t2.acc_comp FROM staffoppr_location t1, staffoppr_cinfo t2 WHERE t2.sno = t1.csno AND t1.sno = ".$loc_arr[1]." AND t1.ltype = '".$loc_arr[0]."'";
							else
								$sel_comp_loc = "SELECT t3.sno, t3.acc_comp FROM staffoppr_contact t1, staffoppr_location t2, staffoppr_cinfo t3 WHERE t1.sno = t2.csno AND t1.csno = t3.sno AND t2.sno = ".$loc_arr[1]." AND t2.ltype = '".$loc_arr[0]."'";
								
							$res_comp_loc = mysql_query($sel_comp_loc,$db);
							$rec_comp_loc = mysql_fetch_array($res_comp_loc);
							
							if(empty($rec_comp_loc['acc_comp']))
							{
								$qry_acccus="insert into staffacc_list(nickname,approveuser,status,dauser,dadate,stime) values('','".$username."','ACTIVE','','0000-00-00',NOW())";
								mysql_query($qry_acccus,$db);
                                                                $last_list_id=mysql_insert_id($db); //Update username of the last inserted record
                                                                $manusername_bill="acc".$last_list_id;

                                                                $upd_staffacc_list="UPDATE staffacc_list SET username='".$manusername_bill."' WHERE sno='".$last_list_id."'";
                                                                mysql_query($upd_staffacc_list,$db);
								
								$qry_acccus="insert into staffacc_cinfo (username,ceo_president,cfo,sales_purchse_manager,cname,curl,address1,address2,city,state,country,zip,ctype,csize,nloction,nbyears,nemployee,com_revenue,federalid,bill_contact,bill_address,bill_req,service_terms,compowner,compbrief,compsummary,compstatus,dress_code,tele_policy,smoke_policy,parking,park_rate,directions,culture, phone,fax,industry,keytech,department,siccode,csource,ticker,alternative_id,crm_comp,phone_extn,cust_classid,ts_layout_pref) select  '".$manusername_bill."',ceo_president,cfo, sales_purchse_manager,cname,curl,address1,address2,city,state,country,zip,ctype,csize,nloction,nbyears,nemployee,com_revenue, federalid,bill_contact,bill_address,bill_req,service_terms,compowner,compbrief,compsummary,compstatus,dress_code,tele_policy, smoke_policy,parking, park_rate,directions,culture,phone,fax,industry,keytech, department,siccode,csource,ticker,alternative_id,sno,phone_extn,'".DEFAULT_DEPT_CLASS."',ts_layout_pref from staffoppr_cinfo where sno='".$rec_comp_loc['sno']."'"; 
								
								mysql_query($qry_acccus,$db);
								
								$bill_cus_id=mysql_insert_id($db);
								
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
								
								//Updating the selected burden details to the accounting customer
								updLocBurdenDetails($bill_cus_id,'acc',$comp_burden_details);
					
								setDefaultAccInfoAcc("customer",$db,$bill_cus_id);
								setDefaultEntityTaxes('Customer', $bill_cus_id, $fetch_acccomp[2], $deptid);// added
					
								$upd_opprcinfo="UPDATE staffoppr_cinfo SET acc_comp=".$bill_cus_id.", customerid='".$bill_cus_id."' WHERE sno=".$rec_comp_loc['sno'];
								mysql_query($upd_opprcinfo,$db);
					
								$upd_acc_cinfo="UPDATE staffacc_cinfo SET customerid='".$bill_cus_id."', muser='".$username."', mdate=NOW() WHERE sno=".$bill_cus_id;
								mysql_query($upd_acc_cinfo,$db);
								
								$sel_acc_loc = "select sno from staffacc_location where csno = ".$bill_cus_id." and ltype = 'com'";
								$res_acc_loc = mysql_query($sel_acc_loc,$db);
								$rec_acc_loc = mysql_fetch_array($res_acc_loc);
								
								$billcontact_sno_bill = insertAllCRMContacts_to_Accounting($rec_comp_loc['sno'], $bill_cus_id, $manusername_bill, $username, $billcontact_sno, $db);
								insertAllCRMLocations_to_Accounting($rec_comp_loc['sno'], $bill_cus_id, $username, $db);
								
								if($bill_loc_mod[0] == 'com')
								{
									$upd_custbill = "update staffacc_cinfo set bill_contact = ".$billcontact_sno_bill.", bill_address = ".$rec_acc_loc['sno']." where sno = ".$csno;
									mysql_query($upd_custbill,$db);
									
									$upd_custbill = "update staffacc_cinfo set bill_contact = ".$billcontact_sno_bill.", bill_address = ".$rec_acc_loc['sno']." where sno = ".$bill_cus_id;
									mysql_query($upd_custbill,$db);
								}
								else if($bill_loc_mod[0] == 'loc')
								{
									$bill_address_location = getBillLocation($bill_loc_mod[1]);
									
									$upd_custbill = "update staffacc_cinfo set bill_contact = ".$billcontact_sno_bill.", bill_address = ".$bill_address_location." where sno = ".$csno;
									mysql_query($upd_custbill,$db);
									
									$upd_custbill = "update staffacc_cinfo set bill_contact = ".$billcontact_sno_bill.", bill_address = ".$bill_address_location." where sno = ".$bill_cus_id;
									mysql_query($upd_custbill,$db);
								}
								else
								{
									$sel_billcon_loc = "SELECT t3.sno FROM staffoppr_contact t1, staffoppr_location t2, staffacc_location t3 WHERE t2.csno = t1.sno AND t1.acc_cont != 0 AND t3.csno = t1.acc_cont AND t2.sno = ".$bill_loc_mod[1]." AND t3.ltype = 'con'";
									$res_billcon_loc = mysql_query($sel_billcon_loc, $db);
									$rec_billcon_loc = mysql_fetch_array($res_billcon_loc);
									
									$upd_custbill = "update staffacc_cinfo set bill_contact = ".$billcontact_sno_bill.", bill_address = ".$rec_billcon_loc['sno']." where sno = ".$csno;
									mysql_query($upd_custbill,$db);
									
									$upd_custbill = "update staffacc_cinfo set bill_contact = ".$billcontact_sno_bill.", bill_address = ".$rec_billcon_loc['sno']." where sno = ".$bill_cus_id;
									mysql_query($upd_custbill,$db);
								}
								
							}
							else
							{
								
								$sel_acc_loc = "select sno from staffacc_location where csno = ".$rec_comp_loc['acc_comp']." and ltype = 'com'";
								$res_acc_loc = mysql_query($sel_acc_loc,$db);
								$rec_acc_loc = mysql_fetch_array($res_acc_loc);
								
								$sel_acc_con_sno = "select acc_cont from staffoppr_contact where sno = ".$billcontact_sno;
								$res_acc_con_sno = mysql_query($sel_acc_con_sno, $db);
								$rec_acc_con_sno = mysql_fetch_array($res_acc_con_sno);
								
								if($bill_loc_mod[0] == 'com')
								{
									$upd_custbill = "update staffacc_cinfo set bill_contact = ".$rec_acc_con_sno['acc_cont'].",  bill_address = ".$rec_acc_loc['sno']." where sno = ".$csno;
								}
								else if($bill_loc_mod[0] == 'loc')
								{
									$bill_address_location = getBillLocation($bill_loc_mod[1]);
									$upd_custbill = "update staffacc_cinfo set bill_contact = ".$rec_acc_con_sno['acc_cont'].",  bill_address = ".$bill_address_location." where sno = ".$csno;
								}
								else
								{
									$sel_billcon_loc = "SELECT t3.sno FROM staffoppr_contact t1, staffoppr_location t2, staffacc_location t3 WHERE t2.csno = t1.sno AND t1.acc_cont != 0 AND t3.csno = t1.acc_cont AND t2.sno = ".$bill_loc_mod[1]." AND t3.ltype = 'con'";
									$res_billcon_loc = mysql_query($sel_billcon_loc, $db);
									$rec_billcon_loc = mysql_fetch_array($res_billcon_loc);
									$upd_custbill = "update staffacc_cinfo set bill_contact = ".$rec_acc_con_sno['acc_cont'].",  bill_address = ".$rec_billcon_loc['sno']." where sno = ".$csno;
									
								}
								
								mysql_query($upd_custbill,$db);
							}
						}
						else
						{
							if($bill_loc_mod[0] == 'com')
							{
								$sel_acc_loc = "SELECT t2.sno as bill_loc_sno FROM staffacc_cinfo t1, staffacc_location t2 WHERE t2.csno = t1.sno AND t2.ltype = 'com' AND t1.sno = ".$csno;						
								$res_acc_loc = mysql_query($sel_acc_loc,$db);
								$rec_acc_loc = mysql_fetch_array($res_acc_loc);
								$upd_custbill = "update staffacc_cinfo set bill_address = ".$rec_acc_loc['bill_loc_sno']." where sno = ".$csno;
							}
							else if($bill_loc_mod[0] == 'loc')
							{
								$bill_address_location = getBillLocation($bill_loc_mod[1]);
								$upd_custbill = "update staffacc_cinfo set bill_address = ".$bill_address_location." where sno = ".$csno;
							}
							else
							{
								$sel_billcon_loc = "SELECT t3.sno FROM staffoppr_contact t1, staffoppr_location t2, staffacc_location t3 WHERE t2.csno = t1.sno AND t1.acc_cont != 0 AND t3.csno = t1.acc_cont AND t2.sno = ".$bill_loc_mod[1]." AND t3.ltype = 'con'";
								$res_billcon_loc = mysql_query($sel_billcon_loc, $db);
								$rec_billcon_loc = mysql_fetch_array($res_billcon_loc);
								$upd_custbill = "update staffacc_cinfo set bill_address = ".$rec_billcon_loc['sno']." where sno = ".$csno;
							}
							
							mysql_query($upd_custbill,$db);
						}
					}
					
					$cust_sno = $csno;
				}
				else
				{
				
					if($coinfo[5] != '')
					{
						$state_arr = explode('^',$coinfo[5]);
						if($state_arr[0] == 'Other')
							$accState = addslashes($coinfo[47]);
						else
							$accState = addslashes($state_arr[0]);
						$accStateId = $state_arr[1];
					}
					else
					{
						$accState = "";
						$accStateId = "";
					}

					$bill_req_value=$coinfo[26];
					if($bill_pay_terms_field == "ven_bill_terms")
						$bill_req_value=",bill_req='".$coinfo[56]."'";

					if($custcvType == "BOTH")
						$staffacc_cinfo_type = ",type='BOTH'";

					$bill_loc_mod = end(explode("-", $bill_loc));

					$que="update staffacc_cinfo set  industry ='".$coinfo[12]."',cname='".$coinfo[0]."',curl='".$coinfo[1]."',address1='".$coinfo[2]."',address2='".$coinfo[3]."',city='".$coinfo[4]."',stateid ='".$accStateId."',country ='".$coinfo[7]."',zip='".$coinfo[6]."',ctype='".$coinfo[16]."',csize='".$coinfo[21]."',nloction='".$coinfo[13]."',nbyears='".$coinfo[14]."',nemployee='".$coinfo[11]."',com_revenue='".$coinfo[9]."',federalid='".$coinfo[19]."',phone='".$coinfo[8]."',fax='".$coinfo[10]."',keytech='".$coinfo[25]."',siccode='".$coinfo[17]."',ticker='".$coinfo[20]."',csource='".$coinfo[15]."',muser='".$username."',compowner='".$coinfo[18]."',compbrief='".$coinfo[23]."',compsummary='".$coinfo[24]."',bill_contact='".$billcontact_sno."',bill_address='".$bill_loc_mod."',".$bill_pay_terms_field."='".$coinfo[26]."',service_terms='".$coinfo[27]."',dress_code='".$coinfo[28]."',tele_policy='".$coinfo[29]."',smoke_policy='".$coinfo[30]."',parking='".$Other_Field1."',park_rate='".$Rate_Field."',directions='".$coinfo[32]."',culture='".$coinfo[33]."', alternative_id='".$coinfo[38]."', mdate=NOW(),phone_extn='".$coinfo[39]."',corp_code='".$coinfo[40]."',".$updateTax."department='".$coinfo[44]."',address_desc='".$coinfo[45]."',billing_default='".$coinfo[46]."',state='".$accState."'".$bill_req_value.",inv_method='".$coinfo[57]."',inv_terms='".$coinfo[58]."',cust_classid='".$custClassID."',vend_classid='".$venClassID."',templateid='".$inv_temp."',attention='".$attention."'".$staffacc_cinfo_type.",inv_delivery_option='".$coinfo[66]."',inv_email_templateid='".$coinfo[67]."',sec_bill_contact='".$sec_bill_cons_str."',ts_layout_pref='".$customer_timesheet_layout_preference."' where username='".$crm_fecth[1]."'";
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
					
					//Updating the selected burden details to the accounting customer
					updLocBurdenDetails($crm_fecth[0],'acc',$comp_burden_details);

					$client_Acc=array("loc_id"=>$coinfo[49],"taxes_pay_acct"=>$coinfo[50],"acct_receive"=>$coinfo[51],"acct_payable"=>$coinfo[52],"typeid"=>$crm_fecth[0],"clienttype"=>$clientType,"acct_miscIncome"=>$coinfo[53],"acct_miscExpense"=>$coinfo[54],"deptid"=>$coinfo[63]);
					ClientAccoutnsTrans($client_Acc);

					if((($crm_select=='' && $srnum == '') || ($crm_select=='yes' && is_numeric($srnum))) && $clientType == "CV")	
						setDefaultAccInfoAcc("customer",$db,$crm_fecth[0],$coinfo[63]);

				    setDefaultEntityTaxes('Customer', $crm_fecth[0], $coinfo[6],$coinfo[63]);

					if(PAYROLL_PROCESS_BY_MADISON == 'MADISON') 			
					{
						$query="update staffacc_cinfo set madison_customerid='".$coinfo[48]."', muser='".$username."', mdate=NOW() where username='".$crm_fecth[1]."'";
						mysql_query($query,$db);
					}

					//For making relation with staffoppr_cinfo and staffacc_cinfo
					if($venfrm == 'yes')
					{
						$up_qry = "UPDATE staffoppr_cinfo SET customerid='".$crm_fecth[0]."' WHERE sno = ".$srnum;
						mysql_query($up_qry,$db);
					}
					/**sanghamitra: set the acc_comp and crm_comp for consulting vendors and customers **/
					 
					$up_qry = "update staffoppr_cinfo set acc_comp = '".$crm_fecth[0]."',customerid='".$crm_fecth[0]."' where sno = ".$srnum;
					mysql_query($up_qry,$db);

					$up_qry = "update staffacc_cinfo set crm_comp = '".$srnum."', muser='".$username."', mdate=NOW() where sno = ".$crm_fecth[0];
					mysql_query($up_qry,$db);
					
					$cust_sno = $crm_fecth[0];
				}

				syncBilling($cust_sno, $srnum);
				if($checkedcrm != "Y" || $checkedcrm== ""){
				
					$sel_crmQry = "SELECT prefix, fname, mname, lname, email, wphone, hphone, mobile, fax, other, ytitle, ctype, suffix, nickname, cat_id, source, department, certifications, codes, keywords, messengerid, spouse, address1, address2, city, state, country, zipcode, sourcetype, reportto, sno, wphone_extn, hphone_extn, other_extn, other_info, email_2, email_3, description, importance, source_name, reportto_name, spouse_name, acc_cont FROM temp_staffoppr_contact WHERE status='ER' AND username ='".$manusername."' AND csno='".$srnum."' AND crmcontact ='Y'";
					
					$sel_crmRes = mysql_query($sel_crmQry,$db);
					$sel_crmNum = mysql_num_rows($sel_crmRes);
					if($sel_crmNum > 0)
					{
						while($sel_crmRow = mysql_fetch_row($sel_crmRes))
						{
							if($sel_crmRow[42] == 0)
							{
								$getstateVal = explode("|AKKENEXP|",getStateIdAbbr($sel_crmRow[25],$sel_crmRow[26]));
								$accConStateId = $getstateVal[2];

								if($getstateVal[2] == 0)
									$accConState = ",'".addslashes($getstateVal[0])."'";
								else
									$accConState = ",'".addslashes($getstateVal[1])."'";

								$addState = ",state";
								$hrmDeptID = getOwnerDepartment();
								
								$que="insert into staffacc_contact (username,prefix,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,ctype,accessto,createdby,cdate,suffix,nickname,cat_id,source,department,certifications,codes,keywords,messengerid,spouse,address1,address2,city,stateid,country,zipcode,owner,mdate,muser,sourcetype,reportto,wphone_extn,hphone_extn,other_extn,other_info,email_2,email_3,description,importance,source_name,reportto_name,spouse_name,deptid".$addState.",csno) values ('".$manusername."','".$sel_crmRow[0]."','".$sel_crmRow[1]."','".$sel_crmRow[2]."','".$sel_crmRow[3]."','".$sel_crmRow[4]."','".$sel_crmRow[5]."','".$sel_crmRow[6]."','".$sel_crmRow[7]."','".$sel_crmRow[8]."','".$sel_crmRow[9]."','".$sel_crmRow[10]."','".$sel_crmRow[11]."','ALL','".$username."',now(),'".$sel_crmRow[12]."','".$sel_crmRow[13]."','".$sel_crmRow[14]."','".$sel_crmRow[15]."','".$sel_crmRow[16]."','".$sel_crmRow[17]."','".$sel_crmRow[18]."','".$sel_crmRow[19]."','".$sel_crmRow[20]."','".$sel_crmRow[21]."','".$sel_crmRow[22]."','".$sel_crmRow[23]."','".$sel_crmRow[24]."','".$accConStateId."','".$sel_crmRow[26]."','".$sel_crmRow[27]."','".$username."',now(),'".$username."','".$sel_crmRow[28]."','".$sel_crmRow[29]."','".$sel_crmRow[31]."','".$sel_crmRow[32]."','".$sel_crmRow[33]."','".$sel_crmRow[34]."','".$sel_crmRow[35]."','".$sel_crmRow[36]."','".$sel_crmRow[37]."','".$sel_crmRow[38]."','".$sel_crmRow[39]."','".$sel_crmRow[40]."','".$sel_crmRow[41]."','".$hrmDeptID."'".$accConState.",$srnum)"; 
								mysql_query($que,$db);
								$acc_contid = mysql_insert_id($db); 

								//maintaning relation with contact tables
								$upcontqry = "update staffacc_contact set crm_cont = '".$sel_crmRow[30]."' where sno=".$acc_contid;
								mysql_query($upcontqry,$db);

								$upcontqry = "update staffoppr_contact set acc_cont  = '".$acc_contid."' where sno=".$sel_crmRow[30];
								mysql_query($upcontqry,$db);
							}
						}
						
					}
				}

				if($getCompanyTaxes != "")
					addTaxesToCustomer($cust_sno, $getCompanyTaxes, 'CompanyTax', '', $coinfo[59]);

				if($coinfo[55] != "")
					addTaxesToCustomer($cust_sno, $coinfo[55], 'Discount', '', $coinfo[60]);
			}
			
			// Copied from else part to insert customer to company
			// When user check for ADD Crm Content in New Page
			
			if($coinfo[36] == "Y" && $checkedcrm == "Y")
			{
				//For dumping accounting contacts data in to crm
				
			
				if($coinfo[5] != '')
				{
					$state_arr = explode('^',$coinfo[5]);
					if($state_arr[0] == 'Other')
						$opprState = addslashes($coinfo[47]);
					else
						$opprState = addslashes($state_arr[0]);
				}
				else
				{
					$opprState = "";
				}

				$que2="insert into staffoppr_cinfo (sno,approveuser,industry,cname,curl,address1,address2,city,state,country,zip,ctype,csize,nloction,nbyears,nemployee,com_revenue,federalid,status,accessto,phone,fax,keytech,siccode,ticker,csource,department,parent,muser,mdate,cdate,owner,compowner,compbrief,compsummary,compstatus,bill_contact,bill_address,bill_req,service_terms,dress_code,tele_policy,smoke_policy,parking,park_rate,directions,culture,alternative_id,phone_extn,address_desc,deptid,ts_layout_pref)  values ('','".$username."','".$coinfo[12]."','".$coinfo[0]."','".$coinfo[1]."','".$coinfo[2]."','".$coinfo[3]."','".$coinfo[4]."','".$opprState."','".$coinfo[7]."','".$coinfo[6]."','".$coinfo[16]."','".$coinfo[21]."','".$coinfo[13]."','".$coinfo[14]."','".$coinfo[11]."','".$coinfo[9]."','".$coinfo[19]."','ER','ALL','".$coinfo[8]."','".$coinfo[10]."','".$coinfo[25]."','".$coinfo[17]."','".$coinfo[20]."','".$coinfo[15]."','".$coinfo[44]."','','".$username."',NOW(),NOW(),'".$username."','".$coinfo[18]."','".$coinfo[23]."','".$coinfo[24]."','".$compstatus."','".$crmbillcontid."','".$crmbilladdid."','".addslashes($coinfo[26])."','".$coinfo[27]."','".$coinfo[28]."','".$coinfo[29]."','".$coinfo[30]."','".$Other_Field1."','".$Rate_Field."','".$coinfo[32]."','".$coinfo[33]."','".$coinfo[38]."','".$coinfo[39]."','".$coinfo[45]."','".$coinfo[63]."','".$customer_timesheet_layout_preference."')";
				mysql_query($que2,$db);
				$csno1=mysql_insert_id($db);
				
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
				updLocBurdenDetails($csno1,'crm',$comp_burden_details);
				
				/////////////////// UDF migration to customers ///////////////////////////////////
				include_once('custom/custome_save.php');		
				$directEmpUdfObj = new userDefinedManuplations();
				$directEmpUdfObj->insertUserDefinedData($csno1, 2);
				/////////////////////////////////////////////////////////////////////////////////

				array_push($CRM_Comp_SNo_SDataUpdate_Array,$csno1);
				
				//Sunil written code start from here 
				$selTempQry = "select * from temp_staffoppr_contact where username='".$_SESSION['username']."' AND status='ER'";
				$selTempQryres = mysql_query($selTempQry,$db);
				$tempSno = '';$lastInsertId = '';
				
				if(mysql_num_rows($selTempQryres) > 0 ){
				    $inti = 0;
					while($cont_row = mysql_fetch_array($selTempQryres)){
						$acc_contid = '';
						$accUsername = "acc".$csno1;
						$insAccCon = "insert into staffacc_contact (username,prefix,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,csno,ctype,accessto,suffix,nickname,cat_id,source,department,certifications,codes,keywords,messengerid,spouse,address1,address2,city,state,country,zipcode,owner,mdate,muser,sourcetype,reportto,wphone_extn,hphone_extn,other_extn,other_info,email_2,email_3,description,importance,source_name,reportto_name,spouse_name,deptid) values('".$manusername."','".$cont_row['prefix']."','".$cont_row['fname']."','".$cont_row['mname']."','".$cont_row['lname']."','".$cont_row['email']."','".$cont_row['wphone']."','".$cont_row['hphone']."','".$cont_row['mobile']."','".$cont_row['fax']."','".$cont_row['other']."','".$cont_row['ytitle']."',$csno,'".$cont_row['ctype']."','".$cont_row['accessto']."','".$cont_row['suffix']."','".$cont_row['nickname']."','".$cont_row['cat_id']."','".$cont_row['source']."','".$cont_row['department']."','".$cont_row['certifications']."','".$cont_row['codes']."','".$cont_row['keywords']."','".$cont_row['messengerid']."','".$cont_row['spouse']."','".$cont_row['address1']."','".$cont_row['address2']."','".$cont_row['city']."','".$cont_row['state']."','".$cont_row['country']."','".$cont_row['zipcode']."','".$cont_row['owner']."','".$cont_row['mdate']."','".$cont_row['muser']."','".$cont_row['sourcetype']."','".$cont_row['reportto']."','".$cont_row['wphone_extn']."','".$cont_row['hphone_extn']."','".$cont_row['other_extn']."','".$cont_row['other_info']."','".$cont_row['email_2']."','".$cont_row['email_3']."','".$cont_row['description']."','".$cont_row['importance']."','".$cont_row['source_name']."','".$cont_row['reportto_name']."','".$cont_row['spouse_name']."','".$cont_row['deptid']."')";
						
						$res = mysql_query($insAccCon,$db);
						$affectedRows = mysql_affected_rows();
						
						if($affectedRows > 0 ){							
							$acc_contid = mysql_insert_id(); 					
							$lastInsertId .= $acc_contid.",";
						}
						$inti++;
					}
					$lastInsertId = rtrim($lastInsertId,",");
					
					// Deleting the temp records from temp_staffoppr_contact
					$delQry = "delete from temp_staffoppr_contact where username='".$_SESSION['username']."' AND status='ER'";
					$delQryRes = mysql_query($delQry,$db);
				}
				$cont_qry = "SELECT prefix,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,ctype,suffix,nickname,cat_id,source,department,certifications,codes,keywords,messengerid,spouse,address1,address2,city,state,country,zipcode,sourcetype,reportto,sno,wphone_extn,hphone_extn,other_extn,other_info,email_2,email_3,description,importance,source_name,reportto_name,spouse_name FROM staffacc_contact WHERE sno IN (".$lastInsertId.") AND crm_cont='0'";
				
				
				$cont_res = mysql_query($cont_qry,$db);
				while($cont_row = mysql_fetch_row($cont_res))
					 contactrelations($cont_row[30],'',$csno1);
				
				// Sunil Commented this code, coz we are pulling the data based on lastInsertIds	
				//$sel_accQry = "SELECT prefix, fname, mname, lname, email, wphone, hphone, mobile, fax, other, ytitle, ctype, suffix, nickname, cat_id, source, department, certifications, codes, keywords, messengerid, spouse, address1, address2, city, state, country, zipcode, sourcetype, reportto, sno, wphone_extn, hphone_extn, other_extn, other_info, email_2, email_3, description, importance, source_name, reportto_name, spouse_name, crm_cont FROM staffacc_contact WHERE sno IN (".$_SESSION['acc_ses'.$Rnd].") AND acccontact ='Y'";
				$sel_accQry = "SELECT prefix, fname, mname, lname, email, wphone, hphone, mobile, fax, other, ytitle, ctype, suffix, nickname, cat_id, source, department, certifications, codes, keywords, messengerid, spouse, address1, address2, city, state, country, zipcode, sourcetype, reportto, sno, wphone_extn, hphone_extn, other_extn, other_info, email_2, email_3, description, importance, source_name, reportto_name, spouse_name, crm_cont FROM staffacc_contact WHERE sno IN (".$lastInsertId.") AND acccontact ='Y'";
				
				$sel_accRes = mysql_query($sel_accQry,$db);
				$sel_accNum = mysql_num_rows($sel_accRes);
				if($sel_accNum > 0)
				{
					while($sel_accRow = mysql_fetch_row($sel_accRes))
					{
						if($sel_accRow[42] == 0)
						{
							$que = "insert into staffoppr_contact (prefix,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,csno,ctype,accessto,suffix,nickname,cat_id,source,department,certifications,codes,keywords,messengerid,spouse,address1,address2,city,state,country,zipcode,owner,mdate,muser,sourcetype,reportto,stime,approveuser,wphone_extn,hphone_extn,other_extn,other_info,email_2,email_3,description,importance,source_name,reportto_name,spouse_name,deptid) select prefix,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,'".$crmbilladdid."',ctype,'".$acctoval."',suffix,nickname,cat_id,source,department,certifications,codes,keywords,messengerid,spouse,address1,address2,city,state,country,zipcode,owner,mdate,muser,sourcetype,reportto,NOW(),'".$username."',wphone_extn,hphone_extn,other_extn,other_info,email_2,email_3,description,importance,source_name,reportto_name,spouse_name,deptid from staffacc_contact where sno='".$sel_accRow[30]."'";
							mysql_query($que,$db);

							$acc_contid = mysql_insert_id($db); 

							//maintaning relation with contact tables
							$upcontqry = "update staffacc_contact set crm_cont = '".$acc_contid."' where sno=".$sel_accRow[30];
							mysql_query($upcontqry,$db);

							$upcontqry = "update staffoppr_contact set acc_cont  = '".$sel_accRow[30]."' where sno=".$acc_contid;
							mysql_query($upcontqry,$db);
						}
					}
				}

				//For making relation with staffoppr_cinfo and staffacc_cinfo
				if($venfrm == 'yes')
				{
					$up_qry = "UPDATE staffoppr_cinfo SET customerid='".$csno."' WHERE sno = ".$csno1;
					mysql_query($up_qry,$db);
				} 
				/**sanghamitra: set the acc_comp and crm_comp for consulting vendors and customers **/
				$up_qry = "update staffoppr_cinfo set acc_comp = '".$csno."', customerid='".$csno."' where sno = ".$csno1;
				mysql_query($up_qry,$db);

				$up_qry = "update staffacc_cinfo set crm_comp = '".$csno1."', muser='".$username."', mdate=NOW() where sno = ".$csno;
				mysql_query($up_qry,$db);
				

				syncBilling($csno, $csno1);
			}
		}
		else
		{
			
		 
			if($coinfo[36] == "Y" && $checkedcrm == "Y")
			{
				$query="insert into staffacc_list (nickname, approveuser, status, dauser, dadate, stime, type, bpsaid)  values ('','".$username."','ACTIVE','','0000-00-00',NOW(),'".$type."','')";
				mysql_query($query,$db);
                                $lastInsertId = mysql_insert_id($db);
				$manusername="acc".$lastInsertId;

				$updateque="update staffacc_list set username='".$manusername."' where sno=".$lastInsertId;
				mysql_query($updateque,$db);

				if($coinfo[5] != '')
				{
					$state_arr = explode('^',$coinfo[5]);
					if($state_arr[0] == 'Other')
						$accState = ",'".addslashes($coinfo[47])."'";
					else
						$accState = ",'".addslashes($state_arr[0])."'";
					$accStateId = $state_arr[1];
					$addState = ",state";
				}
				else
				{
					$accState = "";
					$accStateId = "";
				}

				$que1="insert into staffacc_cinfo (username,industry,cname,curl,address1,address2,city,stateid,country,zip,ctype,csize,nloction,nbyears,nemployee,com_revenue,federalid,phone,fax,keytech,siccode,ticker,csource,muser,mdate,owner,compowner,compbrief,compsummary,bill_contact,bill_address,".$bill_pay_terms_field.",service_terms,dress_code,tele_policy,smoke_policy, parking,park_rate,directions,culture,alternative_id,phone_extn,corp_code,tax,department,address_desc,billing_default,madison_customerid".$addState.",inv_method,inv_terms,cust_classid,vend_classid,templateid,attention,type,compstatus,inv_delivery_option,inv_email_templateid,sec_bill_contact,ts_layout_pref) values ('".$manusername."','".$coinfo[12]."','".$coinfo[0]."','".$coinfo[1]."','".$coinfo[2]."','".$coinfo[3]."','".$coinfo[4]."','".$accStateId."','".$coinfo[7]."','".$coinfo[6]."','".$coinfo[16]."','".$coinfo[21]."','".$coinfo[13]."','".$coinfo[14]."','".$coinfo[11]."','".$coinfo[9]."','".$coinfo[19]."','".$coinfo[8]."','".$coinfo[10]."','".$coinfo[25]."','".$coinfo[17]."','".$coinfo[20]."','".$coinfo[15]."','".$username."',NOW(),'".$username."','".$coinfo[18]."','".$coinfo[23]."','".$coinfo[24]."','".$coinfo[34]."','".$coinfo[35]."','".addslashes($coinfo[26])."','".$coinfo[27]."','".$coinfo[28]."','".$coinfo[29]."','".$coinfo[30]."','".$Other_Field1."','".$Rate_Field."','".$coinfo[32]."','".$coinfo[33]."','".$coinfo[38]."','".$coinfo[39]."','".$coinfo[40]."','".$coinfo[43]."','".$coinfo[44]."','".$coinfo[45]."','".$coinfo[46]."','".$coinfo[48]."'".$accState.",'".$coinfo[57]."','".$coinfo[58]."','".$custClassID."','".$venClassID."','".$inv_temp."','".$attention."','".$custcvType."','".$compstatus.",'".$coinfo[66]."','".$coinfo[67]."','".$sec_bill_cons_str."','".$customer_timesheet_layout_preference."')";
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
				
				//Updating the selected burden details to the accounting customer
				updLocBurdenDetails($csno,'acc',$comp_burden_details);

				$client_Acc=array("loc_id"=>$coinfo[49],"taxes_pay_acct"=>$coinfo[50],"acct_receive"=>$coinfo[51],"acct_payable"=>$coinfo[52],"typeid"=>$csno,"clienttype"=>$clientType,"acct_miscIncome"=>$coinfo[53],"acct_miscExpense"=>$coinfo[54],"deptid"=>$coinfo[63]);
				ClientAccoutnsTrans($client_Acc);

				if((($crm_select=='' && $srnum == '') || ($crm_select=='yes' && is_numeric($srnum))) && $clientType == "CV")	
					setDefaultAccInfoAcc("customer",$db,$csno,$coinfo[63]);

				setDefaultEntityTaxes('Customer', $csno, $coinfo[6], $coinfo[63]);

				$up_qry_acc = "update staffacc_cinfo set customerid = '".$csno."', muser='".$username."', mdate=NOW() where sno = '".$csno."'";
				mysql_query($up_qry_acc,$db);
				 
				if($coinfo[5] != '')
				{
					$state_arr = explode('^',$coinfo[5]);
					if($state_arr[0] == 'Other')
						$opprState = addslashes($coinfo[47]);
					else
						$opprState = addslashes($state_arr[0]);
				}
				else
				{
					$opprState = "";
				}
				 
				$que2="insert into staffoppr_cinfo (sno,approveuser,industry,cname,curl,address1,address2,city,state,country,zip,ctype,csize,nloction,nbyears,nemployee,com_revenue,federalid,status,accessto,phone,fax,keytech,siccode,ticker,csource,department,parent,muser,mdate,cdate,owner,compowner,compbrief,compsummary,compstatus,bill_contact,bill_address,bill_req,service_terms,dress_code,tele_policy,smoke_policy,parking,park_rate,directions,culture,alternative_id,phone_extn,address_desc,deptid,ts_layout_pref)  values ('','".$username."','".$coinfo[12]."','".$coinfo[0]."','".$coinfo[1]."','".$coinfo[2]."','".$coinfo[3]."','".$coinfo[4]."','".$opprState."','".$coinfo[7]."','".$coinfo[6]."','".$coinfo[16]."','".$coinfo[21]."','".$coinfo[13]."','".$coinfo[14]."','".$coinfo[11]."','".$coinfo[9]."','".$coinfo[19]."','ER','ALL','".$coinfo[8]."','".$coinfo[10]."','".$coinfo[25]."','".$coinfo[17]."','".$coinfo[20]."','".$coinfo[15]."','".$coinfo[44]."','','".$username."',NOW(),NOW(),'".$username."','".$coinfo[18]."','".$coinfo[23]."','".$coinfo[24]."','".$compstatus."','".$crmbillcontid."','".$crmbilladdid."','".addslashes($coinfo[26])."','".$coinfo[27]."','".$coinfo[28]."','".$coinfo[29]."','".$coinfo[30]."','".$Other_Field1."','".$Rate_Field."','".$coinfo[32]."','".$coinfo[33]."','".$coinfo[38]."','".$coinfo[39]."','".$coinfo[45]."','".$coinfo[63]."','".$customer_timesheet_layout_preference."')";
				mysql_query($que2,$db);
				$csno1=mysql_insert_id($db);
				
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
				updLocBurdenDetails($csno1,'crm',$comp_burden_details);

				array_push($CRM_Comp_SNo_SDataUpdate_Array,$csno1);

				//When he select Add to CRM contacts, We need to dump the temp table to staffacc_contact first.
				
				
				$cont_qry = "SELECT prefix,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,ctype,suffix,nickname,cat_id,source,department,certifications,codes,keywords,messengerid,spouse,address1,address2,city,state,country,zipcode,sourcetype,reportto,sno,wphone_extn,hphone_extn,other_extn,other_info,email_2,email_3,description,importance,source_name,reportto_name,spouse_name FROM staffacc_contact WHERE sno IN (".$_SESSION['acc_ses'.$Rnd].") AND crm_cont='0'";
				$cont_res = mysql_query($cont_qry,$db);
				while($cont_row = mysql_fetch_row($cont_res))
					 contactrelations($cont_row[30],'',$csno1);

				$sel_accQry = "SELECT prefix, fname, mname, lname, email, wphone, hphone, mobile, fax, other, ytitle, ctype, suffix, nickname, cat_id, source, department, certifications, codes, keywords, messengerid, spouse, address1, address2, city, state, country, zipcode, sourcetype, reportto, sno, wphone_extn, hphone_extn, other_extn, other_info, email_2, email_3, description, importance, source_name, reportto_name, spouse_name, crm_cont FROM staffacc_contact WHERE sno IN (".$_SESSION['acc_ses'.$Rnd].") AND acccontact ='Y'";
				$sel_accRes = mysql_query($sel_accQry,$db);
				$sel_accNum = mysql_num_rows($sel_accRes);
				if($sel_accNum > 0)
				{
					while($sel_accRow = mysql_fetch_row($sel_accRes))
					{
						if($sel_accRow[42] == 0)
						{
							$que = "insert into staffoppr_contact (prefix,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,csno,ctype,accessto,suffix,nickname,cat_id,source,department,certifications,codes,keywords,messengerid,spouse,address1,address2,city,state,country,zipcode,owner,mdate,muser,sourcetype,reportto,stime,approveuser,wphone_extn,hphone_extn,other_extn,other_info,email_2,email_3,description,importance,source_name,reportto_name,spouse_name,deptid) select prefix,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,'".$crmbilladdid."',ctype,'".$acctoval."',suffix,nickname,cat_id,source,department,certifications,codes,keywords,messengerid,spouse,address1,address2,city,state,country,zipcode,owner,mdate,muser,sourcetype,reportto,NOW(),'".$username."',wphone_extn,hphone_extn,other_extn,other_info,email_2,email_3,description,importance,source_name,reportto_name,spouse_name,deptid from staffacc_contact where sno='".$sel_accRow[30]."'";
							mysql_query($que,$db);

							$acc_contid = mysql_insert_id($db); 

							//maintaning relation with contact tables
							$upcontqry = "update staffacc_contact set crm_cont = '".$acc_contid."' where sno=".$sel_accRow[30];
							mysql_query($upcontqry,$db);

							$upcontqry = "update staffoppr_contact set acc_cont  = '".$sel_accRow[30]."' where sno=".$acc_contid;
							mysql_query($upcontqry,$db);
						}
					}
				}

				//For making relation with staffoppr_cinfo and staffacc_cinfo
				if($venfrm == 'yes')
				{
					$up_qry = "UPDATE staffoppr_cinfo SET customerid='".$csno."' WHERE sno = ".$csno1;
					mysql_query($up_qry,$db);
				} 
				/**sanghamitra: set the acc_comp and crm_comp for consulting vendors and customers **/
	
				$up_qry = "update staffoppr_cinfo set acc_comp = '".$csno."', customerid='".$csno."' where sno = ".$csno1;
				mysql_query($up_qry,$db);

				$up_qry = "update staffacc_cinfo set crm_comp = '".$csno1."', muser='".$username."', mdate=NOW() where sno = ".$csno;
				mysql_query($up_qry,$db);
				

				syncBilling($csno, $csno1);
			}
			else
			{
				$query="insert into staffacc_list (nickname, approveuser, status, dauser, dadate, stime, type, bpsaid)  values ('','".$username."','ACTIVE','','0000-00-00',NOW(),'".$type."','')";
				mysql_query($query,$db);
                                $lastInsertId = mysql_insert_id($db);
				$manusername="acc".$lastInsertId;

				$updateque="update staffacc_list set username='".$manusername."' where sno=".$lastInsertId;
				mysql_query($updateque,$db);

				if($coinfo[5] != '')
				{
					$state_arr = explode('^',$coinfo[5]);
					if($state_arr[0] == 'Other')
						$accState = ",'".addslashes($coinfo[47])."'";
					else
						$accState = ",'".addslashes($state_arr[0])."'";
					$accStateId = $state_arr[1];
					$addState = ",state";
				}
				else
				{
					$accState = "";
					$accStateId = "";
				}

				$que1="insert into staffacc_cinfo (username,industry,cname,curl,address1,address2,city,stateid,country,zip,ctype,csize,nloction,nbyears,nemployee,com_revenue,federalid,phone,fax,keytech,siccode,ticker,csource,muser,mdate,owner,compowner,compbrief,compsummary,bill_contact,bill_address,".$bill_pay_terms_field.",service_terms,dress_code,tele_policy,smoke_policy, parking,park_rate,directions,culture,alternative_id,phone_extn,corp_code,tax,department,address_desc,billing_default,madison_customerid".$addState.",inv_method,inv_terms,cust_classid,vend_classid,templateid,attention,type,compstatus,inv_delivery_option,inv_email_templateid,sec_bill_contact,ts_layout_pref) values ('".$manusername."','".$coinfo[12]."','".$coinfo[0]."','".$coinfo[1]."','".$coinfo[2]."','".$coinfo[3]."','".$coinfo[4]."','".$accStateId."','".$coinfo[7]."','".$coinfo[6]."','".$coinfo[16]."','".$coinfo[21]."','".$coinfo[13]."','".$coinfo[14]."','".$coinfo[11]."','".$coinfo[9]."','".$coinfo[19]."','".$coinfo[8]."','".$coinfo[10]."','".$coinfo[25]."','".$coinfo[17]."','".$coinfo[20]."','".$coinfo[15]."','".$username."',NOW(),'".$username."','".$coinfo[18]."','".$coinfo[23]."','".$coinfo[24]."','".$coinfo[34]."','".$coinfo[35]."','".addslashes($coinfo[26])."','".$coinfo[27]."','".$coinfo[28]."','".$coinfo[29]."','".$coinfo[30]."','".$Other_Field1."','".$Rate_Field."','".$coinfo[32]."','".$coinfo[33]."','".$coinfo[38]."','".$coinfo[39]."','".$coinfo[40]."','".$coinfo[43]."','".$coinfo[44]."','".$coinfo[45]."','".$coinfo[46]."','".$coinfo[48]."'".$accState.",'".$coinfo[57]."','".$coinfo[58]."','".$custClassID."','".$venClassID."','".$inv_temp."','".$attention."','".$custcvType."','".$compstatus."','".$coinfo[66]."','".$coinfo[67]."','".$sec_bill_cons_str."','".$customer_timesheet_layout_preference."')";
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
				
				//Updating the selected burden details to the accounting customer
				updLocBurdenDetails($csno,'acc',$comp_burden_details);

				$client_Acc=array("loc_id"=>$coinfo[49],"taxes_pay_acct"=>$coinfo[50],"acct_receive"=>$coinfo[51],"acct_payable"=>$coinfo[52],"typeid"=>$csno,"clienttype"=>$clientType,"acct_miscIncome"=>$coinfo[53],"acct_miscExpense"=>$coinfo[54],"deptid"=>$coinfo[63]);
				ClientAccoutnsTrans($client_Acc);

				if((($crm_select=='' && $srnum == '') || ($crm_select=='yes' && is_numeric($srnum))) && $clientType == "CV")	
					setDefaultAccInfoAcc("customer",$db,$csno,$coinfo[63]);

				setDefaultEntityTaxes('Customer',$csno, $coinfo[6], $coinfo[63]);

				$up_qry_acc = "update staffacc_cinfo set customerid = '".$csno."', muser='".$username."', mdate=NOW() where sno = '".$csno."'";
				mysql_query($up_qry_acc,$db);
			}

			if($getCompanyTaxes != "")
				addTaxesToCustomer($csno, $getCompanyTaxes, 'CompanyTax', '', $coinfo[59]);

			if($coinfo[55] != "")
				addTaxesToCustomer($csno, $coinfo[55], 'Discount', '', $coinfo[60]);
		}

		//Checking the condition for Consulting Vendors and updating the candidate with company--Kiran
		if($venfrm=='yes')
			addCandidateToCompany($manusername,$resEmp);

		if($manusername != "")
		{
			//Dumping accounting company data into hrcon_w4, empcon_w4 and new_w4 tables -- kumar raju k.
			dumpW4TableData('',$manusername); //Send these ( $opprCompId = staffoppr_cinfo.sno and $accCompId = staffacc_cinfo.username ) values to function.
		}

		//adding a new company details for a existing oppr
		if($_SESSION['acc_ses'.$Rnd]!="")
		{
			//adding a new company details for a existing contact
			$que="update staffacc_contact set username='".$manusername."', csno='".$csno."' where csno='0' and sno in (".$_SESSION['acc_ses'.$Rnd].")";
			mysql_query($que,$db);
		}

		if($csno1!="")
		{
			if(!empty($_SESSION['oppr_ses'.$Rnd]))
			{
				$que="update staffoppr_contact set csno='".$csno1."' ,accessto = '".$username."' where csno='0' and sno in (".$_SESSION['oppr_ses'.$Rnd].")";
			}
			else
			{
				$que="update staffoppr_contact set csno='".$csno1."' ,accessto = '".$username."' where csno='0' and acc_cont in (".$_SESSION['acc_ses'.$Rnd].")";
			}
			mysql_query($que,$db);
		}

		$accuser=$manusername;
		$acccontactSnos="";

		$sel_accres="SELECT sno FROM staffacc_contact WHERE username='".$manusername."'";
		$res_accres=mysql_query($sel_accres,$db);
		$accconcatVar="";
		while($fetch_accres=mysql_fetch_row($res_accres))
		{
			$acccontactSnos.=$accconcatVar.$fetch_accres[0];
			$accconcatVar=",";
		}	

		$con_sno=rtrim($acccontactSnos,',');
		
		//For updating Company search column
        updatecomp_search_data($CRM_Comp_SNo_SDataUpdate_Array);
		
		//For updating contact search column
		$Contact_Sno_Array=explode(",",$_SESSION['oppr_ses'.$Rnd]);
        updatecont_search_data($Contact_Sno_Array);

		if($typecomp=="")
		{
			if($chk_comp == 'yes')
			{
				//Regarding Billing Information in Comapnies new/Edit
				$comp_qry = "select cname,address1,address2,city,state from staffacc_cinfo where sno = ".$csno;
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
				$comp_status = "";

				$sel_cont = "select TRIM(CONCAT_WS('',fname,' ',lname,'(',IF(email='',nickname,email),')')),sno,fname,mname,lname from staffacc_contact where username ='".$manusername."'";
				$sel_res = mysql_query($sel_cont,$db);
				while($sel_row = mysql_fetch_row($sel_res))
				{
					$sel_name = $sel_row[2]." ".$sel_row[3]." ".$sel_row[4];
					$sel_contact .= html_tls_specialchars(addslashes($sel_name))."|";
					$sel_contact_sno .= $sel_row[1]."|";
				}
				$list_cont = trim($sel_contact,"|");
				$list_cont_sno = trim($sel_contact_sno,"|");
			}

			session_unregister("edit_company".$Rnd);
			session_unregister("oppsno".$Rnd);
			session_unregister("insno1".$Rnd);
			session_unregister("acc_ses".$Rnd);
			session_unregister("oppr_ses".$Rnd);
			session_unregister("cmp_id");

			if($chk_comp == "yes")
			{
				print "<script> 
				var toList = window.opener.document.markreqman;
				toList.company_sno.value = '$csno';
				toList.chk_bilcompaddr.value = 'frm_acc';
				var oDiv = window.opener.document.getElementById('disp_comp');
				var odiv_chg = window.opener.document.getElementById('chgid');
				var odiv_compchg = window.opener.document.getElementById('comp_chgid');
				window.opener.document.getElementById('hdncompanyfrom').value= 'acc';
				str = '$manusername';
				str1 = '$comp_addr';
				str2 = '$comp_status';

				if('$comp_addr' == '')
				{
					oDiv.innerHTML = \"<a href=javascript:comp_func('".$manusername."','".$comp_status."') class=crm-select-link><strong>".$comp_names."</strong></a>\";					   
					odiv_compchg.innerHTML = '<span class=summaryform-formelement>(</span> <a class=crm-select-link href=javascript:bill_comp()><strong>change</strong> </a>&nbsp;<a href=javascript:bill_comp()><img class=remind-delete-align src=/BSOS/images/crm/icon-srch.gif width=17 height=16  border=0 align=middle></a><span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class=crm-select-link href=javascript:do_compAdd()><strong>new</strong></a><span class=summaryform-formelement>&nbsp;)&nbsp;</span>';
				}
				else
				{
					oDiv.innerHTML = \"<a href=javascript:comp_func('".$manusername."','".$comp_status."') class=crm-select-link><strong>".$comp_names."</strong></a><span class=summaryform-formelement>&nbsp;-&nbsp;</span><span class=summaryform-nonboldsub-title>".$comp_addr."</span>\";
					odiv_compchg.innerHTML = '<span class=summaryform-formelement>(</span> <a class=crm-select-link href=javascript:bill_comp()><strong>change</strong> </a>&nbsp;<a href=javascript:bill_comp()><img class=remind-delete-align src=/BSOS/images/crm/icon-srch.gif width=17 height=16  border=0 align=middle></a><span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class=crm-select-link href=javascript:do_compAdd()><strong>new</strong></a><span class=summaryform-formelement>&nbsp;)&nbsp;</span>';
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
					oDiv_contact.innerHTML = '<input type=hidden name=list_contact><a class=crm-select-link href=javascript:bill_cont() id=disp><strong>select</strong> contact</a>';
					odiv_chg.innerHTML = '<a href=javascript:bill_cont()><img class=remind-delete-align src=/BSOS/images/crm/icon-srch.gif width=17 height=16  border=0 align=middle></a><span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class=crm-select-link href=javascript:donew_add()><strong>new</strong>&nbsp;contact</a>';
				}
				else
				{
					toList.contact_sno.value = '';
					newselect = '<select name=list_contact onchange=chg_fun(this.value)>';
					newselect += '<option value=0>---Select Contact---</option>';
					for(i=0;i<contactsno_size;i++)
					{
						newselect += '<option value='+cont_sno[i]+'>'+stack[i]+'</option>';
					}
					newselect += '</select>&nbsp;';

					oDiv_contact.innerHTML = '';
					odiv_chg.innerHTML = '<span class=summaryform-formelement>(&nbsp;</span>'+newselect+'<a class=crm-select-link href=javascript:bill_cont()><strong>change</strong> </a>&nbsp;<a href=javascript:bill_cont()><img class=remind-delete-align src=/BSOS/images/crm/icon-srch.gif width=17 height=16  border=0 align=middle></a><span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class=crm-select-link href=javascript:donew_add()><strong>new</strong></a><span class=summaryform-formelement>&nbsp;)&nbsp;</span>';
				}
				window.close();</script>";
			}
			else
			{
				print "<script>if(window.opener){ var parwin=window.opener.location.href; window.opener.location.href=parwin; } window.close();</script>";
			}
		}
		else
		{
			session_unregister("edit_company".$Rnd);
			session_unregister("oppsno".$Rnd);
			session_unregister("insno1".$Rnd);
			session_unregister("acc_ses".$Rnd);
			session_unregister("oppr_ses".$Rnd);
			session_unregister("cmp_id");
		}
		
	}
	else if($aa=="update")
	{
	  
		$isZipChanged = false;
		if($addr=="")
		{
			if($coinfo[5] != '')
			{
				$state_arr = explode('^',$coinfo[5]);
				if($state_arr[0] == 'Other')
					$opprState = addslashes($coinfo[47]);
				else
					$opprState = addslashes($state_arr[0]);
			}
			else
			{
				$opprState = "";
			}

			$que="update staffoppr_cinfo set industry ='".$coinfo[12]."',cname='".$coinfo[0]."',curl='".$coinfo[1]."',address1='".$coinfo[2]."',address2='".$coinfo[3]."',city='".$coinfo[4]."',state ='".$opprState."',country ='".$coinfo[7]."',zip='".$coinfo[6]."',ctype='".$coinfo[16]."',csize='".$coinfo[21]."',nloction='".$coinfo[13]."',nbyears='".$coinfo[14]."',nemployee='".$coinfo[11]."',com_revenue='".$coinfo[9]."',federalid='".$coinfo[19]."',phone='".$coinfo[8]."',fax='".$coinfo[10]."',keytech='".$coinfo[25]."',siccode='".$coinfo[17]."',ticker='".$coinfo[20]."',csource='".$coinfo[15]."',department='".$coinfo[44]."',muser='".$username."',compowner='".$coinfo[18]."',compbrief='".$coinfo[23]."',compsummary='".$coinfo[24]."',bill_req='".addslashes($coinfo[26])."',service_terms='".$coinfo[27]."',dress_code='".$coinfo[28]."',tele_policy='".$coinfo[29]."',smoke_policy='".$coinfo[30]."',parking='".$Other_Field1."',park_rate='".$Rate_Field."',directions='".$coinfo[32]."',culture='".$coinfo[33]."',alternative_id='".$coinfo[38]."',mdate=NOW(),phone_extn='".$coinfo[39]."',address_desc='".$coinfo[45]."',ts_layout_pref='".$customer_timesheet_layout_preference."' where sno='".$srnum."'"; 
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
			
			//Updating the selected burden details to the crm company
			updLocBurdenDetails($srnum,'crm',$comp_burden_details);

			array_push($CRM_Comp_SNo_SDataUpdate_Array,$srnum);
					/////////////////// UDF migration to customers ///////////////////////////////////
					include_once('custom/custome_save.php');		
					$directEmpUdfObj = new userDefinedManuplations();
					if($srnum!=0){
						$directEmpUdfObj->insertUserDefinedData($srnum, 2);
					}
					/////////////////////////////////////////////////////////////////////////////////	
			
		}
		else
		{
			if($coinfo[36] == "Y" && $checkedcrm == "Y")
		    {
				if($coinfo[5] != '')
				{
					$state_arr = explode('^',$coinfo[5]);
					if($state_arr[0] == 'Other')
						$opprState = addslashes($coinfo[47]);
					else
						$opprState = addslashes($state_arr[0]);
				} 
				else
				{
					$opprState = "";
				} 

				$seqry = "select sno from staffacc_cinfo where username ='".$addr."'";
				$resqry = mysql_query($seqry,$db);
				$rowqry = mysql_fetch_row($resqry);
				$csno = $rowqry[0];

				$chk_crmQry = "select sno from staffoppr_cinfo where acc_comp = '".$rowqry[0]."'";
				$chk_crmRes = mysql_query($chk_crmQry,$db);
				$chk_crmNum = mysql_num_rows($chk_crmRes);

				if($chk_crmNum > 0)
				{
					$chk_crmRow = mysql_fetch_row($chk_crmRes);

					$que="update staffoppr_cinfo set industry ='".$coinfo[12]."',cname='".$coinfo[0]."',curl='".$coinfo[1]."',address1='".$coinfo[2]."',address2='".$coinfo[3]."',city='".$coinfo[4]."',state ='".$opprState."',country ='".$coinfo[7]."',zip='".$coinfo[6]."',ctype='".$coinfo[16]."',csize='".$coinfo[21]."',nloction='".$coinfo[13]."',nbyears='".$coinfo[14]."',nemployee='".$coinfo[11]."',com_revenue='".$coinfo[9]."',federalid='".$coinfo[19]."',phone='".$coinfo[8]."',fax='".$coinfo[10]."',keytech='".$coinfo[25]."',siccode='".$coinfo[17]."',ticker='".$coinfo[20]."',csource='".$coinfo[15]."',department='".$coinfo[44]."',muser='".$username."',compowner='".$coinfo[18]."',compbrief='".$coinfo[23]."',compsummary='".$coinfo[24]."',bill_req='".addslashes($coinfo[26])."',service_terms='".$coinfo[27]."',dress_code='".$coinfo[28]."',tele_policy='".$coinfo[29]."',smoke_policy='".$coinfo[30]."',parking='".$Other_Field1."',park_rate='".$Rate_Field."',directions='".$coinfo[32]."',culture='".$coinfo[33]."',alternative_id='".$coinfo[38]."',mdate=NOW(),phone_extn='".$coinfo[39]."',address_desc='".$coinfo[45]."',ts_layout_pref='".$customer_timesheet_layout_preference."' where sno='".$chk_crmRow[0]."'";
					mysql_query($que,$db);
					$csno1 = $chk_crmRow[0];
					
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
					updLocBurdenDetails($csno1,'crm',$comp_burden_details);
				}
				else
				{
					$que2="insert into staffoppr_cinfo (sno,approveuser,industry,cname,curl,address1,address2,city,state,country,zip,ctype,csize,nloction,nbyears,nemployee,com_revenue,federalid,status,accessto,phone,fax,keytech,siccode,ticker,csource,department,parent,muser,mdate,cdate,owner,compowner,compbrief,compsummary,compstatus,bill_contact,bill_address,bill_req,service_terms,dress_code,tele_policy,smoke_policy,parking,park_rate,directions,culture,alternative_id,phone_extn,address_desc, deptid,ts_layout_pref)  values ('','".$username."','".$coinfo[12]."','".$coinfo[0]."','".$coinfo[1]."','".$coinfo[2]."','".$coinfo[3]."','".$coinfo[4]."','".$opprState."','".$coinfo[7]."','".$coinfo[6]."','".$coinfo[16]."','".$coinfo[21]."','".$coinfo[13]."','".$coinfo[14]."','".$coinfo[11]."','".$coinfo[9]."','".$coinfo[19]."','ER','ALL','".$coinfo[8]."','".$coinfo[10]."','".$coinfo[25]."','".$coinfo[17]."','".$coinfo[20]."','".$coinfo[15]."','".$coinfo[44]."','','".$username."',NOW(),NOW(),'".$username."','".$coinfo[18]."','".$coinfo[23]."','".$coinfo[24]."','".$compstatus."','".$billcontid."','".$crmbilladdid."','".addslashes($coinfo[26])."','".$coinfo[27]."','".$coinfo[28]."','".$coinfo[29]."','".$coinfo[30]."','".$Other_Field1."','".$Rate_Field."','".$coinfo[32]."','".$coinfo[33]."','".$coinfo[38]."','".$coinfo[39]."','".$coinfo[45]."','".$coinfo[63]."','".$customer_timesheet_layout_preference."')";
					mysql_query($que2,$db);
					$csno1=mysql_insert_id($db);
					
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
					updLocBurdenDetails($csno1,'crm',$comp_burden_details);
				}
				/////////////////// UDF migration to customers ///////////////////////////////////
					include_once('custom/custome_save.php');		
					$directEmpUdfObj = new userDefinedManuplations();
					if($csno1!=0){
						$directEmpUdfObj->insertUserDefinedData($csno1, 2);
					}
					/////////////////////////////////////////////////////////////////////////////////	
				array_push($CRM_Comp_SNo_SDataUpdate_Array,$csno1);

				if($coinfo[5] != '')
				{
					$state_arr = explode('^',$coinfo[5]);
					if($state_arr[0] == 'Other')
						$accState = addslashes($coinfo[47]);
					else
						$accState = addslashes($state_arr[0]);
					$accStateId = $state_arr[1];
				}
				else
				{
					$accState = "";
					$accStateId = "";
				}

				if($custcvType == "BOTH")
					$staffacc_cinfo_type = ",type='BOTH'";

				$queryCustZip = mysql_query("SELECT sno, zip FROM staffacc_cinfo WHERE username='".$addr."'", $db);
				$rowCustZip = mysql_fetch_assoc($queryCustZip);

				if(trim($rowCustZip['zip']) != trim($coinfo[6]) && $rowCustZip['sno'] != '' && $rowCustZip['sno'] > 0)
				 	$isZipChanged = true;

				$que="update staffacc_cinfo set industry='".$coinfo[12]."',cname='".$coinfo[0]."',curl='".$coinfo[1]."',address1='".$coinfo[2]."',address2='".$coinfo[3]."',city='".$coinfo[4]."',stateid ='".$accStateId."',country ='".$coinfo[7]."',zip='".$coinfo[6]."',ctype='".$coinfo[16]."',csize='".$coinfo[21]."',nloction='".$coinfo[13]."',nbyears='".$coinfo[14]."',nemployee='".$coinfo[11]."',com_revenue='".$coinfo[9]."',federalid='".$coinfo[19]."',phone='".$coinfo[8]."',fax='".$coinfo[10]."',keytech='".$coinfo[25]."',siccode='".$coinfo[17]."',ticker='".$coinfo[20]."',csource='".$coinfo[15]."',muser='".$username."',compowner='".$coinfo[18]."',compbrief='".$coinfo[23]."',compsummary='".$coinfo[24]."',bill_contact='".$coinfo[34]."',bill_address='".$coinfo[35]."',".$bill_pay_terms_field."='".$coinfo[26]."',service_terms='".$coinfo[27]."',dress_code='".$coinfo[28]."',tele_policy='".$coinfo[29]."',smoke_policy='".$coinfo[30]."',parking='".$Other_Field1."',park_rate='".$Rate_Field."',directions='".$coinfo[32]."',culture='".$coinfo[33]."', alternative_id='".$coinfo[38]."',mdate=NOW(),phone_extn='".$coinfo[39]."',corp_code='".$coinfo[40]."',".$updateTax."department='".$coinfo[44]."',address_desc='".$coinfo[45]."',billing_default='".$coinfo[46]."',state='".$accState."',inv_method='".$coinfo[57]."',inv_terms='".$coinfo[58]."',cust_classid='".$custClassID."',vend_classid='".$venClassID."',templateid='".$inv_temp."',attention='".$attention."'".$staffacc_cinfo_type.",inv_delivery_option='".$coinfo[66]."',inv_email_templateid='".$coinfo[67]."',sec_bill_contact='".$sec_bill_cons_str."',ts_layout_pref='".$customer_timesheet_layout_preference."' where username='".$addr."'";
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
				
				//Updating the selected burden details to the accounting customer
				updLocBurdenDetails($csno,'acc',$comp_burden_details);

				$client_Acc=array("loc_id"=>$coinfo[49],"taxes_pay_acct"=>$coinfo[50],"acct_receive"=>$coinfo[51],"acct_payable"=>$coinfo[52],"typeid"=>$coinfo[37],"clienttype"=>$clientType,"acct_miscIncome"=>$coinfo[53],"acct_miscExpense"=>$coinfo[54],"deptid"=>$coinfo[63]);
				ClientAccoutnsTrans($client_Acc);

				if((($crm_select=='' && $srnum == '') || ($crm_select=='yes' && is_numeric($srnum))) && $clientType == "CV")	
					setDefaultAccInfoAcc("customer",$db,$coinfo[35],$coinfo[63]);						

				if(PAYROLL_PROCESS_BY_MADISON == 'MADISON') 			
				{
					$query="update staffacc_cinfo set madison_customerid='".$coinfo[48]."', muser='".$username."', mdate=NOW() where username='".$addr."'";
					mysql_query($query,$db);	
				}

				//For making relation with staffoppr_cinfo and staffacc_cinfo
				if($venfrm == 'yes')
				{
					$up_qry = "UPDATE staffoppr_cinfo SET customerid='".$rowqry[0]."' WHERE sno = ".$csno1;
					mysql_query($up_qry,$db);
				}
				/**sanghamitra: set the acc_comp and crm_comp for consulting vendors and customers **/
	
				$up_qry = "update staffoppr_cinfo set acc_comp = '".$rowqry[0]."', customerid='".$rowqry[0]."' where sno = ".$csno1;
				mysql_query($up_qry,$db);

				$up_qry = "update staffacc_cinfo set crm_comp = '".$csno1."' where sno = ".$rowqry[0];
				mysql_query($up_qry,$db);
					

				//if($_SESSION['acc_ses'.$Rnd] == "")
				//{
					$crm_accont="";
					$que2="select sno,suffix,nickname,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,csno,username,cat_id,accessto,ctype,wphone_extn,hphone_extn,other_extn from staffacc_contact where username='".$addr."' and acccontact='Y' and crm_cont='0'";
					$res2=mysql_query($que2,$db);
					while($row2=mysql_fetch_row($res2))
					{
						if($crm_accont == "")
							$crm_accont = $row2[0];
						else
							$crm_accont .= ",".$row2[0];
					}

					$_SESSION['acc_ses'.$Rnd]=$crm_accont;
				//}	

				//For dumping accounting contacts data in to crm
			        $cont_qry = "SELECT prefix,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,ctype,suffix,nickname,cat_id,source,department,certifications,codes,keywords,messengerid,spouse,address1,address2,city,state,country,zipcode,sourcetype,reportto,sno,wphone_extn,hphone_extn,other_extn,other_info,email_2,email_3,description,importance,source_name,reportto_name,spouse_name FROM staffacc_contact WHERE sno in (".$crm_accont.") AND crm_cont='0'";
				$cont_res = mysql_query($cont_qry,$db);
				while($cont_row = mysql_fetch_row($cont_res))
				{
					//Maintaning email check and relations
					contactrelations($cont_row[30],'',$csno1);
				}

				syncBilling($csno, $csno1);
			}
			else
			{
				if($coinfo[5] != '')
				{
					$state_arr = explode('^',$coinfo[5]);
					if($state_arr[0] == 'Other')
						$accState = addslashes($coinfo[47]);
					else
						$accState = addslashes($state_arr[0]);
					$accStateId = $state_arr[1];
				}
				else
				{
					$accState = "";
					$accStateId = "";
				}

				$attention = trim($attention);

				if($custcvType == "BOTH")
					$staffacc_cinfo_type = ",type='BOTH'";

				$queryCustZip = mysql_query("SELECT sno, zip FROM staffacc_cinfo WHERE username='".$addr."'", $db);
				$rowCustZip = mysql_fetch_assoc($queryCustZip);

				if(trim($rowCustZip['zip']) != trim($coinfo[6]) && $rowCustZip['sno'] != '' && $rowCustZip['sno'] > 0)
					$isZipChanged = true;

				$que="update staffacc_cinfo set industry ='".$coinfo[12]."',cname='".$coinfo[0]."',curl='".$coinfo[1]."',address1='".$coinfo[2]."',address2='".$coinfo[3]."',city='".$coinfo[4]."',stateid ='".$accStateId."',country ='".$coinfo[7]."',zip='".$coinfo[6]."',ctype='".$coinfo[16]."',csize='".$coinfo[21]."',nloction='".$coinfo[13]."',nbyears='".$coinfo[14]."',nemployee='".$coinfo[11]."',com_revenue='".$coinfo[9]."',federalid='".$coinfo[19]."',phone='".$coinfo[8]."',fax='".$coinfo[10]."',keytech='".$coinfo[25]."',siccode='".$coinfo[17]."',ticker='".$coinfo[20]."',csource='".$coinfo[15]."',muser='".$username."',compowner='".$coinfo[18]."',compbrief='".$coinfo[23]."',compsummary='".$coinfo[24]."',bill_contact='".$coinfo[34]."',bill_address='".$coinfo[35]."',".$bill_pay_terms_field."='".$coinfo[26]."',service_terms='".$coinfo[27]."',dress_code='".$coinfo[28]."',tele_policy='".$coinfo[29]."',smoke_policy='".$coinfo[30]."',parking='".$Other_Field1."',park_rate='".$Rate_Field."',directions='".$coinfo[32]."',culture='".$coinfo[33]."',alternative_id='".$coinfo[38]."', mdate=NOW(),phone_extn='".$coinfo[39]."',corp_code='".$coinfo[40]."',".$updateTax."department='".$coinfo[44]."',address_desc='".$coinfo[45]."',billing_default='".$coinfo[46]."',state='".$accState."',inv_method='".$coinfo[57]."',inv_terms='".$coinfo[58]."',cust_classid='".$custClassID."',vend_classid='".$venClassID."',templateid='".$inv_temp."',attention='".$attention."'".$staffacc_cinfo_type.",inv_delivery_option='".$coinfo[66]."',inv_email_templateid='".$coinfo[67]."',sec_bill_contact='".$sec_bill_cons_str."',ts_layout_pref='".$customer_timesheet_layout_preference."' where username='".$addr."'";
				mysql_query($que,$db);				

				$client_Acc=array("loc_id"=>$coinfo[49],"taxes_pay_acct"=>$coinfo[50],"acct_receive"=>$coinfo[51],"acct_payable"=>$coinfo[52],"typeid"=>$coinfo[37],"clienttype"=>$clientType,"acct_miscIncome"=>$coinfo[53],"acct_miscExpense"=>$coinfo[54],"deptid"=>$coinfo[63]);
			 	ClientAccoutnsTrans($client_Acc);

				if((($crm_select=='' && $srnum == '') || ($crm_select=='yes' && is_numeric($srnum))) && $clientType == "CV")	
					setDefaultAccInfoAcc("customer",$db,$coinfo[35],$coinfo[63]);

				$seqry = "select sno from staffacc_cinfo where username ='".$addr."'";
				$resqry = mysql_query($seqry,$db);
				$rowqry = mysql_fetch_row($resqry);
				
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
				
				//Updating the selected burden details to the accounting customer
				updLocBurdenDetails($rowqry[0],'acc',$comp_burden_details);

				if(PAYROLL_PROCESS_BY_MADISON == 'MADISON') 			
				{
					$query="update staffacc_cinfo set madison_customerid='".$coinfo[48]."', muser='".$username."', mdate=NOW() where username='".$addr."'";
					mysql_query($query,$db);
				}
			}

			//Checking the condition for Consulting Vendors and updating the candidate with company--Kiran
			if($venfrm=='yes')
				addCandidateToCompany($addr,$resEmp);			

			if($addr != "")
			{
				//Dumping accounting company data into hrcon_w4, empcon_w4 and new_w4 tables -- kumar raju k.
				dumpW4TableData('',$addr); //Send these ( $opprCompId = staffoppr_cinfo.sno and $accCompId = staffacc_cinfo.username ) values to function.
			}
		}

		//adding a new company details for a existing oppr
		if($_SESSION['acc_ses'.$Rnd]!="")
		{
			//adding a new company details for a existing contact
			$que="update staffacc_contact set username='".$addr."',csno='".$rowCustZip['sno']."' where csno='0' and sno in (".$_SESSION['acc_ses'.$Rnd].")";
			mysql_query($que,$db);
		}

		if($csno1!="")
		{
			if(!empty($_SESSION['oppr_ses'.$Rnd]))
			{
				$que="update staffoppr_contact set csno='".$csno1."',accessto = '".$username."'  where csno='0' and sno in (".$_SESSION['oppr_ses'.$Rnd].")";
			}
			else
			{
				$que="update staffoppr_contact set csno='".$csno1."',accessto = '".$username."'  where csno='0' and acc_cont in (".$_SESSION['acc_ses'.$Rnd].")";
			}
			mysql_query($que,$db);
		}

		$accuser=$addr;
		$acccontactSnos="";
		$sel_accres="SELECT sno FROM staffacc_contact WHERE username='".$addr."'";
		$res_accres=mysql_query($sel_accres,$db);
		$accconcatVar="";
		while($fetch_accres=mysql_fetch_row($res_accres))
		{
			$acccontactSnos.=$accconcatVar.$fetch_accres[0];
			$accconcatVar=",";
		}

		//For updating Company search column
        updatecomp_search_data($CRM_Comp_SNo_SDataUpdate_Array);

		//For updating contact search column
		$Contact_Sno_Array=explode(",",$_SESSION['oppr_ses'.$Rnd]);
        updatecont_search_data($Contact_Sno_Array);

		$con_sno=rtrim($acccontactSnos,',');
		
					/////////////////// UDF migration to customers ///////////////////////////////////
					include_once('custom/custome_save.php');		
					$directEmpUdfObj = new userDefinedManuplations();
					$sql1 = "select sno from staffacc_cinfo where username = '".$addr."'";
					$result1 = mysql_query($sql1, $db);
					$row1 = mysql_fetch_row($result1);
					$udf_csno = $row1[0];
					$directEmpUdfObj->insertUserDefinedData($udf_csno, 6);
					/////////////////////////////////////////////////////////////////////////////////		

		if(PAYROLL_PROCESS_BY_MADISON == 'MADISON')
		{
			$madison_sno_que = "select sno from staffacc_cinfo where username = '".$addr."'";
			$madison_sno_res = mysql_query($madison_sno_que,$db);
			$madison_sno_row = mysql_fetch_row($madison_sno_res);
			UpdateMadisonCustId($madison_sno_row[0]);
		}

		$getQry = "SELECT sno FROM staffacc_cinfo WHERE username = '".$addr."'";
		$resQry = mysql_query($getQry,$db);
		$rowQry = mysql_fetch_row($resQry);

		addTaxesToCustomer($rowQry[0], $getCompanyTaxes, 'CompanyTax', $TaxVal, $coinfo[59]);
		addTaxesToCustomer($rowQry[0], $coinfo[55], 'Discount', $DisVal, $coinfo[60]);
		addVertexTaxesToCustomer($rowQry[0], $chkState, $chkLocal, $chkCounty, $chkSchool, $geoList,$hdnDropVal, $isZipChanged);
		/* Adding Roles to Customers */

		// Delete Old Assign_comission and insert new at below code
		$deleteAssign = "DELETE FROM assign_commission WHERE assignid ='".$rowQry[0]."' AND assigntype='ACC'";
		mysql_query($deleteAssign,$db);
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
		$staffAcc_sno = $rowQry[0];
		$shift_id = 0;
		$emp_val = explode(',',$empvalues);
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

				//if($counter == 0) {
				$commval1	=$commval[$commKey][$indexVal];
				$ratetype1	=$ratetype[$commKey][$indexVal];
				$paytype1	=$paytype[$commKey][$indexVal];
				$roleName1	=$roleName[$commKey][$indexVal];
				$roleOverWrite1	=$roleOverWrite[$commKey][$indexVal];
				$roleEDisable1	= 'N';
				//}
				

				$counter = $counter+1;

				/* -------------------------------------- */
				$commissionData		= explode("|",$commissionValues);
				$empno			= $commKey;
				$emptxt			= $commval1;
				$rateval		= $ratetype1;
				$payval			= $paytype1;
				$rolename		= $roleName1;
				$roleoverwrite	= $roleOverWrite1;
				$roleEDisable	= $roleEDisable1;

				if(substr($empno,0,3) == "emp"){
					$empno	= str_replace("emp","",$empno);
					$typeOfPerson	= "E";
				}
				else {
					$typeOfPerson	= "A";
				}

				if($rolename != '') {
					$comm_insert	= "INSERT INTO assign_commission(sno, username, assignid, assigntype, person, type, amount, comm_calc, co_type, roleid, overwrite, enableUserInput,shift_id) VALUES('', '".$username."', '".$staffAcc_sno."', 'ACC', '".$empno."', '$typeOfPerson', '".$emptxt."', '".$payval."', '".$rateval."', '".$rolename."', '".$roleoverwrite."', '".$roleEDisable."','".$shift_id."')";
					mysql_query($comm_insert, $db);
				}

				/* --------------------------------------- */
			}
		}
		/* END */

		session_unregister("edit_company".$Rnd);
		session_unregister("oppsno".$Rnd);
		session_unregister("insno1".$Rnd);
		session_unregister("acc_ses".$Rnd);
		session_unregister("oppr_ses".$Rnd);
        session_unregister("cmp_id");

		if($typecomp=='')
		{
			?>
			<script>
			var parent=window.opener;
			if((parent) && (window.opener.location.href.indexOf("Home/home.php")>=0  || window.opener.location.href.indexOf("Collaboration/Tasks/gettask.php")>=0 || window.opener.location.href.indexOf("/Sales/Placement/viewdetails.php")>=0))
			{
				window.location.href="addacccompany.php?addr=<?php echo $addr;?>&srnum=<?php echo $addr;?>&newComp=yes&crm_select=no&display=yes&upst=yes&Rnd=<?=$Rnd?>&candrn=<?=$candrn?>&comp_stat=<? echo $comp_stat?>&par_stat=<?=$par_stat?>&statval=<?php echo $statval; ?>";
			}
			else if((parent) && (window.opener.location.href.indexOf("Accounting/clients/")>=0))
			{
				var Company_0 = "<?php echo $addr;?>";
				var Company_Name_1 = "<?php echo $coinfo[0];?>";
				var Contact_name_2="<?php echo $update_contact_done=='yes'?gridcell($update_contact_name):''?>";
				var Phone_3 ="<?php echo $coinfo[18];?>";
				var State_4 = "<?php echo $coinfo[5];?>";
				var Stage_5 = "<?php echo $opinfo[2];?>";
				var Share_type_6  = "";
				var Owner_7 = "";
				var Editpath_8="";

				if(parent && parent.formCompanyRow!=undefined)
					parent.formCompanyRow(Company_0,Company_Name_1, Contact_name_2, Phone_3, State_4, Stage_5, Share_type_6, Owner_7, Editpath_8 );

				window.location.href="addacccompany.php?addr=<?php echo $addr;?>&srnum=<?php echo $addr;?>&newComp=yes&crm_select=no&display=yes&upst=yes&Rnd=<?=$Rnd?>&candrn=<?=$candrn?>&comp_stat=<? echo $comp_stat?>&par_stat=<?=$par_stat?>&statval=<?php echo $statval; ?>&corp_codeval=<? echo $coinfo[40];?>&edit_acc=<?php echo $edit_acc;?>";
			}
			else if((parent) && (window.opener.location.href.indexOf("Accounting/suppliers/")>=0))
			{
				var Company_0 = "<?php echo $addr;?>";
				var Company_Name_1 = "<?php echo $coinfo[0];?>";
				var Contact_name_2="<?php echo $update_contact_done=='yes'?gridcell($update_contact_name):''?>";
				var Phone_3 ="<?php echo $coinfo[18];?>";
				var State_4 = "<?php echo $coinfo[5];?>";
				var Stage_5 = "<?php echo $opinfo[2];?>";
				var Share_type_6  = "";
				var Owner_7 = "";
				var Editpath_8="";

				if(parent && parent.formCompanyRow!=undefined)
				{
					parent.formCompanyRow(Company_0,Company_Name_1, Contact_name_2, Phone_3, State_4, Stage_5, Share_type_6, Owner_7, Editpath_8 );
				}
				<?php 
				if($edit_acc == "no")
				{
					echo "var temploc = window.opener.location.href; \n window.opener.location.href = temploc";
				}
				?>	
				window.location.href="addacccompany.php?addr=<?php echo $addr;?>&srnum=<?php echo $addr;?>&newComp=yes&crm_select=no&display=yes&upst=yes&venfrm=yes&edit_acc=yes&Rnd=<?=$Rnd?>&candrn=<?=$candrn?>&comp_stat=<? echo $comp_stat?>&par_stat=<?=$par_stat?>&statval=<?php echo $statval; ?>&corp_codeval=<? echo $coinfo[40];?>";
			}
			else if(window.opener.location.href.indexOf("/Analytics/Reports/SalesCompanies")>=0)
			{
				window.close();
			}
			</script>
			<?php
		}
	}

	if(($typecomp=='jobcompany' || $typecomp=='company1' || $typecomp=='billcompany') && $contSno!='')
	{
		$upd_con="UPDATE staffacc_contact SET username='".$accuser."' WHERE sno='".$contSno."'";
		mysql_query($upd_con,$db);
		$con_sno=$contSno;
		$set='add';
	}

	if($typecomp=='comcontact')
	{
		$sel_con="SELECT staffacc_contact.sno,TRIM(CONCAT_WS('',staffacc_contact.fname,' ',staffacc_contact.lname)), staffacc_cinfo.cname FROM staffacc_contact,staffacc_cinfo WHERE staffacc_contact.sno in(".$con_sno.") AND staffacc_cinfo.username = staffacc_contact.username";
		$res_con=mysql_query($sel_con,$db);
		$comm_rows=mysql_num_rows($res_con);
		if($comm_rows > 1)
		{
			$commissionRows="";
			$commissionConcat="";
			while($fetch_con=mysql_fetch_row($res_con))
			{
				$con_name=str_replace('"','|Akkendbquote|',$fetch_con[1]);
				$contname=str_replace("'",'|Akkensiquote|',$con_name);
				$comp_name=str_replace('"','|Akkendbquote|',$fetch_con[2]);
				$compname=str_replace("'",'|Akkensiquote|',$comp_name);
				$commisno=$fetch_con[0];
				$commissionRows.=$commissionConcat.$contname."|".$compname."|".$commisno;
				$commissionConcat="^";
			}	
		}
		else
		{
			$fetch_con=mysql_fetch_row($res_con);
			$con_name=str_replace('"','|Akkendbquote|',$fetch_con[1]);
			$contname=str_replace("'",'|Akkensiquote|',$con_name);
			$comp_name=str_replace('"','|Akkendbquote|',$fetch_con[2]);
			$compname=str_replace("'",'|Akkensiquote|',$comp_name);
			$commisno=$fetch_con[0];
		}	
	}

	if($typecomp=='refcontact' || $typecomp=='reportcontact' || $typecomp=='billcontact' ||  $typecomp=='vencontact' || $typecomp=='comcontact')
	{
		print "<script>
		if(window.opener.location.href.indexOf('/BSOS/HRM/Hiring_Mngmt/newconreg15.php') >=0 || window.opener.location.href.indexOf('/BSOS/HRM/Employee_Mngmt/newconreg15.php') >=0 || window.opener.location.href.indexOf('/BSOS/HRM/Consultant_Leads/conreg21.php') >=0 || window.opener.location.href.indexOf('/BSOS/HRM/Consultant_Leads/revconreg21.php') >=0 || window.opener.location.href.indexOf('/BSOS/Accounting/Assignment/approveassignment.php') >=0 || window.opener.location.href.indexOf('/BSOS/Accounting/Assignment/newassign.php') >=0 || window.opener.location.href.indexOf('/BSOS/Accounting/Assignment/editassign.php') >=0 || window.opener.location.href.indexOf('/BSOS/HRM/Employee_Mngmt/redirectassignment.php') >=0  || window.opener.location.href.indexOf('/BSOS/HRM/Hiring_Mngmt/redirectassignment.php') >=0)
		{
			form=window.opener.document.forms[0];
			var toList = parent.window.opener.document.forms[0];
			if('$typecomp'!='comcontact')
			{
				var url = '/include/contactResp.php';
				var rtype='rtype';
				var content = 'typecom=$typecomp&comsno=$accuser&accsno=$con_sno';
				parent.window.opener.DynCls_Ajax_result(url,rtype,content,'handlenew()');
			}
			else
		    {
				if('$con_sno'!='0' && '$con_sno'!='')
				{
					if('$comm_rows' > 1 )
						window.opener.multipleCommission('$accuser','$typecomp','$commissionRows','$con_sno');
					else
						window.opener.win('$accuser','$typecomp','$contname','$con_sno','','$compname');
				}	
				else
				{
					alert('No Contact is selected to add to Commission');
				}
			}
			if('$typecomp'!='reportcontact')
			{
				if(window.opener.document.getElementById('acccontactcrfmstatus').value == 1 || window.opener.document.getElementById('acccompcrfmstatus').value == 1)
				{
					parent.window.opener.acccompanyRateTypes('$accuser','company');
				}
			}
			window.close();
		}
		</script>";
	}
	else if($typecomp=='jobcompany' || $typecomp=='company1' || $typecomp=='billcompany')
	{
	  	print "<script>
		if(window.opener.location.href.indexOf('/BSOS/HRM/Hiring_Mngmt/newconreg15.php') >=0 || window.opener.location.href.indexOf('/BSOS/HRM/Employee_Mngmt/newconreg15.php') >=0 || window.opener.location.href.indexOf('/BSOS/HRM/Consultant_Leads/conreg21.php') >=0 || window.opener.location.href.indexOf('/BSOS/HRM/Consultant_Leads/revconreg21.php') >=0 || window.opener.location.href.indexOf('/BSOS/Accounting/Assignment/approveassignment.php') >=0 || window.opener.location.href.indexOf('/BSOS/Accounting/Assignment/newassign.php') >=0 || window.opener.location.href.indexOf('/BSOS/Accounting/Assignment/editassign.php') >=0 || window.opener.location.href.indexOf('/BSOS/HRM/Employee_Mngmt/redirectassignment.php') >=0 || window.opener.location.href.indexOf('/BSOS/HRM/Hiring_Mngmt/redirectassignment.php') >=0)
		{
			form=window.opener.document.forms[0];
			var toList = parent.window.opener.document.forms[0];
			var url = '/include/companyResp.php';
			var rtype='rtype';
			var content = 'typecomp=$typecomp&comsno=$accuser&contsno=$contSno&set=$set';
			parent.window.opener.DynCls_Ajax_result(url,rtype,content,'handlenew()');
			window.close();
		}
		</script>";
	}
	else if($typecomp=='vendorcompany')
	{			
		if($crm_fecth[1] != '')
			$vendorusername = $crm_fecth[1];
		else
			$vendorusername = $addr;
		if($coinfo[0] == '')
			$coinfo[0] = '&nbsp; ';
		print "<script>
		if(window.opener.location.href.indexOf('/BSOS/HRM/Hiring_Mngmt/newconreg15.php') >=0 || window.opener.location.href.indexOf('/BSOS/HRM/Employee_Mngmt/newconreg15.php') >=0 || window.opener.location.href.indexOf('/BSOS/HRM/Consultant_Leads/conreg21.php') >=0 || window.opener.location.href.indexOf('/BSOS/HRM/Consultant_Leads/revconreg21.php') >=0 || window.opener.location.href.indexOf('/BSOS/Accounting/Assignment/approveassignment.php') >=0 || window.opener.location.href.indexOf('/BSOS/Accounting/Assignment/newassign.php') >=0 || window.opener.location.href.indexOf('/BSOS/Accounting/Assignment/editassign.php') >=0 || window.opener.location.href.indexOf('/BSOS/HRM/Employee_Mngmt/redirectassignment.php') >=0 || window.opener.location.href.indexOf('/BSOS/HRM/Hiring_Mngmt/redirectassignment.php') >=0)
		{				
			window.opener.document.getElementById('vencontact-change').innerHTML = \"<A class=crm-select-link href=javascript:viewAccCustomers('vendorcompany','$vendorusername');>$coinfo[0]</A>\";
			window.close();
		}
		</script>";
	}

	function addTaxesToCustomer($customerSno, $selectedIds, $chkType, $oldIds, $taxDiscClass)
	{
		global $username, $maildb,$db;

		$selIdsArray = $oldIdsArray = $curIdsArray = $taxDiscClsArray = $getTaxDiscClsIds = array();
		if($selectedIds != "")
		{
			$selectedIds = str_replace("'", "", str_replace("\\", "", $selectedIds));
			$selIdsArray = explode(",", $selectedIds);
		}

		if($oldIds != "")
		{
			$oldIds = str_replace("'", "", str_replace("\\", "", $oldIds));
			$oldIdsArray = explode(",", $oldIds);
		}

		if($taxDiscClass != "")
		{
			$taxDiscClass = str_replace("'", "", str_replace("\\", "", $taxDiscClass));
			$taxDiscClsArray = explode(",", $taxDiscClass);
			$cntTaxDiscClsIds = count($taxDiscClsArray);

			for($i=0; $i<$cntTaxDiscClsIds; $i++)
			{
				$expTaxDiscClsIds = explode("^AKKTCLS^",$taxDiscClsArray[$i]);
				$getTaxDiscClsIds[$expTaxDiscClsIds[0]] = $expTaxDiscClsIds[1];
			}
		}

		$curIdsArray = array_values(array_unique(array_merge($selIdsArray, $oldIdsArray)));
		$countCurIds = count($curIdsArray);

		for($i=0; $i < $countCurIds; $i++)
		{
			if(in_array($curIdsArray[$i], $oldIdsArray) && !in_array($curIdsArray[$i], $selIdsArray))
			{
				$updQry = "UPDATE customer_discounttaxes SET status = 'backup', muser = '".$username."', mdate = NOW() WHERE customer_sno = '".$customerSno."' AND type = '".$chkType."' AND status = 'active' AND tax_discount_id = '".$curIdsArray[$i]."'";
				mysql_query($updQry,$db);
			}

			if(!in_array($curIdsArray[$i], $oldIdsArray) && in_array($curIdsArray[$i], $selIdsArray))
			{
				$duplicate_tax_check = "select count(*) as taxcount from customer_discounttaxes 
				where tax_discount_id = '".$curIdsArray[$i]."' AND status = 'active' and customer_sno = '".$customerSno."' AND type= '".$chkType."' AND classid = '".$getTaxDiscClsIds[$curIdsArray[$i]]."' ";
				
				$tax_count_check = mysql_query($duplicate_tax_check,$db);
				$tax_rows = mysql_fetch_row($tax_count_check);
				
				if($tax_rows[0] == 0){				
				
					$insQry = "INSERT INTO customer_discounttaxes(tax_discount_id, customer_sno, cuser, muser, cdate, mdate, type, status, classid) VALUES('".$curIdsArray[$i]."', '".$customerSno."', '".$username."', '".$username."', NOW(), NOW(), '".$chkType."', 'active', '".$getTaxDiscClsIds[$curIdsArray[$i]]."')";
					mysql_query($insQry,$db);
				}
			}

			if(in_array($curIdsArray[$i], $oldIdsArray) && in_array($curIdsArray[$i], $selIdsArray))
			{
				$updQry = "UPDATE customer_discounttaxes SET muser = '".$username."', mdate = NOW(), classid = '".$getTaxDiscClsIds[$curIdsArray[$i]]."' WHERE customer_sno = '".$customerSno."' AND type = '".$chkType."' AND status = 'active' AND tax_discount_id = '".$curIdsArray[$i]."'";
				mysql_query($updQry,$db);
			}
		}
	}

	function addVertexTaxesToCustomer($customerSno, $stateTax, $localTax, $countyTax, $schoolTax, $geoList,$hdnDropVal, $isZipChanged = false)
	{
		global $username, $maildb,$db,$exemptchkState,$exemptchkCounty,$exemptchkLocal,$exemptchkSchool;
			
		$OBJ_NET_PAYROLL = new NetPayroll("Customer");
		$OBJ_NET_PAYROLL->setArrayData($geoList);

		$stateArr = $countyArr = $localArr = $schArr = array();
		$stateArr = array_merge($OBJ_NET_PAYROLL->stateGENArray,$OBJ_NET_PAYROLL->stateERArray,$OBJ_NET_PAYROLL->stateEEArray);
		$countyArr = array_merge($OBJ_NET_PAYROLL->countyGENArray,$OBJ_NET_PAYROLL->countyEEArray,$OBJ_NET_PAYROLL->countyERArray);
		$localArr = array_merge($OBJ_NET_PAYROLL->localGENArray,$OBJ_NET_PAYROLL->localEEArray,$OBJ_NET_PAYROLL->localERArray);
		$schArr = $OBJ_NET_PAYROLL->schoolArray;

		if(count($stateArr) > 0)
			insertNewTaxes($stateArr, $geoList, 'State',$stateTax,$customerSno,$exemptchkState);		 

		if(count($localArr) > 0)
			insertNewTaxes($localArr, $geoList, 'Local',$localTax,$customerSno,$exemptchkLocal);	 

		if(count($countyArr) > 0)
			insertNewTaxes($countyArr, $geoList, 'County',$countyTax,$customerSno,$exemptchkCounty);		 

		if(count($schArr) > 0)
			insertNewTaxes($schArr, $geoList, 'School',$schoolTax,$customerSno,$exemptchkSchool);
			
		list($stateVal,$countyVal,$localVal) = explode("-",$hdnDropVal);
		updateGeoCodeforCompany($geoList,$stateVal,$countyVal,$localVal,$customerSno);
				
		if($isZipChanged)
		{
			setAssignmentTaxes($customerSno);
		}		
	}

	function insertNewTaxes($federalArr,$geoList, $type, $entityArray=array(), $custId,$entityExempt=array())
	{
		global $maildb,$db, $username,$hdntaxdetailArr;

		foreach($federalArr as $fedTax)
		{
			$sqlTaxCheck = "SELECT sno FROM vprt_taxhan WHERE geo='".$fedTax->GEO."' AND schdist='".$fedTax->SCHDIST."' AND startdate='".$fedTax->START_DATE."' AND stopdate='".$fedTax->STOP_DATE."' AND taxid=".$fedTax->TAXID;
			$resTaxCheck = mysql_query($sqlTaxCheck,$db);
			$rowTaxCheck = mysql_fetch_row($resTaxCheck);

			if(mysql_num_rows($resTaxCheck) <= 0)
			{
				$sqlTax = "INSERT INTO vprt_taxhan ( taxid, geo, schdist, taxname, startdate, stopdate, taxtype) VALUES ('".$fedTax->TAXID."', '".$fedTax->GEO."', '".$fedTax->SCHDIST."', '".$fedTax->TAXNAME."', '".$fedTax->START_DATE."', '".$fedTax->STOP_DATE."', '".$type."')";
				mysql_query($sqlTax,$db);
				$taxID = mysql_insert_id($db);

				if(count($fedTax->FilingSatuses->Filing_Status)>0)
				{
					foreach($fedTax->FilingSatuses->Filing_Status as $fillingStatus)
					{
						$sqlFilling = "INSERT INTO vprt_valfill ( taxsno, filing_desc, filing_stat, startdate, stopdate )VALUES ( '".$taxID."', '".$fillingStatus->FILSTAT_DESC."', '".$fillingStatus->FILING_STAT."', '".$fillingStatus->START_DATE."', '".$fillingStatus->STOP_DATE."')";
						mysql_query($sqlFilling,$db);
					}					
				}
			}
			else
			{
				$taxID = $rowTaxCheck[0];
			}

			if(in_array($fedTax->TAXID.'_'.$fedTax->SCHDIST,$entityArray))
				$apply = 'Y';				
			else
				$apply = 'N';	
					
			if(in_array($fedTax->TAXID.'_'.$fedTax->SCHDIST,$entityExempt))
				$applyEx = 'Y';				
			else
				$applyEx = 'N';			
				
			if($custId != '')
			{
				$updQueApply = "UPDATE vprt_taxhan_cust_apply SET status='B' where taxsno='".$taxID."' AND custid=".$custId;
				$resUpd = mysql_query($updQueApply,$db);

				$insApply = "INSERT INTO vprt_taxhan_cust_apply (custid,taxsno,apply,status,cuser,cdate,muser,mdate,exempt) VALUES ('".$custId."', '".$taxID."', '".$apply."', 'A', '".$username."', NOW(), '".$username."', NOW(),'".$applyEx."')";					
				$resIns = mysql_query($insApply,$db);
			}					
		}

		$sqlCustLocID =	"SELECT d.loc_id FROM Client_Accounts ca INNER JOIN department d ON ca.deptid=d.sno	WHERE typeid=".$custId." AND ca.status='active'";
		$resCustLocID = mysql_query($sqlCustLocID,$db);
		$rowCustLocID = mysql_fetch_row($resCustLocID);

		$locID = $rowCustLocID[0];

		/* code for handling Tax description screen*/
		$splitData = explode("||AKKENSPLIT||",$hdntaxdetailArr);
		
		if(count($splitData)>0)
		{
			for($ii=0; $ii < count($splitData); $ii++)
			{
				$splitElement = explode("^^ElementSplit^^",$splitData[$ii]);
				$splitTaxID = explode("_",$splitElement[0]);

				$sqlTaxCheck1 = "SELECT sno FROM vprt_taxhan WHERE geo = '".$splitElement[7]."' AND taxid =".$splitTaxID[0];
				$resTaxCheck1 = mysql_query($sqlTaxCheck1,$db);
				$rowTaxCheck1 = mysql_fetch_row($resTaxCheck1);
					
				if(mysql_num_rows($resTaxCheck1) > 0)
				{
					$sqlTaxRates = "SELECT sno FROM vprt_taxhan_loc_setup WHERE taxsno=".$rowTaxCheck1[0]." AND status='A' AND locid=".$locID;
					$resTaxRates = mysql_query($sqlTaxRates,$db);
					$rowTaxSno = mysql_fetch_row($resTaxRates);

					$sqlSetLoc = "UPDATE vprt_taxhan_loc_setup SET status = 'B' WHERE taxsno=".$rowTaxCheck1[0]." AND locid=".$locID;
					mysql_query($sqlSetLoc,$db);

					$sqlSetLoc = "INSERT INTO vprt_taxhan_loc_setup (locid, taxsno, label, deacription, agency, taxid, account, status, cuser, cdate, muser, mdate)VALUES ('".$locID."', '".$rowTaxCheck1[0]."', '".$splitElement[1]."', '".$splitElement[2]."', '".$splitElement[3]."', '".$splitElement[4]."', '".$splitElement[5]."', 'A', '".$username."', NOW(), '".$username."', NOW())";
					mysql_query($sqlSetLoc,$db);
					$lastLocID = mysql_insert_id($db);

					if($rowTaxSno[0] !='')
					{
						$updateRates = "UPDATE vprt_taxhan_loc_rates SET status = 'B' WHERE tax_setup_sno=".$rowTaxSno[0];
						mysql_query($updateRates,$db);
					}

					$splitDate = explode("^^DateSplit^^",$splitElement[6]);

					for($jj=0; $jj<count($splitDate); $jj++)
					{
						$splitDateVal = explode("|SPLIT|",$splitDate[$jj]);
						if($splitDateVal[0] != '')
						{
							$sqlSetRates = "INSERT INTO vprt_taxhan_loc_rates ( tax_setup_sno, startdate, enddate, rate, status, cuser, cdate, muser, mdate)VALUES ('".$lastLocID."', '".$splitDateVal[0]."', '".$splitDateVal[1]."', '".$splitDateVal[2]."', 'A', '".$username."',  NOW(),'".$username."',  NOW())";	
							mysql_query($sqlSetRates,$db);
						}
					}
				}			
			}
		}
	}
		
	function updateGeoCodeforCompany($geoVal,$stateVal='',$countyVal='',$localVal='',$manageID)
	{
		global $maildb,$db;

		if($manageID != '')
		{
			$updateManage = "UPDATE staffacc_cinfo SET vprt_GeoCode = '".trim($geoVal)."', vprt_State = '".trim($stateVal)."', vprt_County = '".trim($countyVal)."', vprt_Local = '".trim($localVal)."' WHERE sno =".$manageID; 
			mysql_query($updateManage,$db);
		}	
	}	
	
	function insertAllCRMContacts_to_Accounting($crmcompanyid, $acc_companyid, $manusername, $username, $crm_bill_contact_loc_sno, $db)
	{
		$bill_contact_new = 0;
		
		$sel_all_contacts = "select sno from staffoppr_contact where csno = ".$crmcompanyid;		
		
		$res_all_contacts = mysql_query($sel_all_contacts, $db);		
		while($rec_all_contacts = mysql_fetch_array($res_all_contacts))
		{
			$ins_acccont = "insert into staffacc_contact (username,prefix,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,ctype,accessto,createdby,cdate,suffix,nickname,cat_id,source,department,certifications,codes,keywords,messengerid,spouse,address1,address2,city,stateid,country,zipcode,owner,mdate,muser,sourcetype,reportto,wphone_extn,hphone_extn,other_extn,other_info,email_2,email_3,description,importance,source_name,reportto_name,spouse_name,deptid,state, csno) SELECT '".$manusername."',prefix,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,ctype,accessto,'".$username."',now(),suffix,nickname,cat_id,source,department,certifications,codes,keywords,messengerid,spouse,address1,address2,
city,stateid,country,zipcode,owner,mdate,muser,sourcetype,reportto,wphone_extn,hphone_extn,other_extn,other_info,
email_2,email_3,description,importance,source_name,reportto_name,spouse_name,deptid,state,'".$acc_companyid."' FROM staffoppr_contact WHERE sno = '".$rec_all_contacts['sno']."'";

			mysql_query($ins_acccont,$db);
			$acc_contid = mysql_insert_id($db);
			
			//maintaning relation with contact tables
			$upcontqry = "update staffacc_contact set crm_cont = '".$rec_all_contacts['sno']."' where sno=".$acc_contid;
			mysql_query($upcontqry,$db);
			
			$upcontqry = "update staffoppr_contact set acc_cont  = '".$acc_contid."' where sno=".$rec_all_contacts['sno'];
			mysql_query($upcontqry,$db);
			
			if(!empty($crm_bill_contact_loc_sno) && ($crm_bill_contact_loc_sno == $rec_all_contacts['sno']))
				$bill_contact_new = $acc_contid;
		}
		
		return $bill_contact_new;
	}
	
	function insertAllCRMLocations_to_Accounting($crmcompanyid, $acc_companyid, $username, $db)
	{		
		$ins_all_locations = "INSERT INTO staffacc_location (title, address1, address2, city, state, country, zipcode, STATUS, ltype, cuser, cdate, muser, mdate, crm_loc, csno, burden_type, bt_overwrite)
SELECT title, address1, address2, city, state, country, zipcode, STATUS, ltype, ".$username.", NOW(), ".$username.", NOW(), sno, ".$acc_companyid.", burden_type, bt_overwrite FROM staffoppr_location WHERE csno = ".$crmcompanyid." AND ltype IN ('loc')";
		
		mysql_query($ins_all_locations);
	}
	
	function getBillLocation($crm_bill_address)
	{
		$sel_bill_address = "select sno from staffacc_location where crm_loc = ".$crm_bill_address. " and ltype = 'loc'";
		$res_bill_address = mysql_query($sel_bill_address);
		$rec_bill_address = mysql_fetch_array($res_bill_address);
		
		return $rec_bill_address['sno'];
	}
?>