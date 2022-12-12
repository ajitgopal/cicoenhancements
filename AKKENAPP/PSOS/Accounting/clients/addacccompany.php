<?php 
	require("global.inc"); 
	require("functions.php");
	require("dispfunc.php"); //Required this file for dispfdb function in functions.php page.
	require("../../Include/commonfuns.inc");

	require("Menu.inc");
	require_once("multipleRatesClass.php");
	require_once("nusoap.php");
	$menu=new EmpMenu();	

	//For getting the multiple rates
	$ratesObj 	= new multiplerates();
	$ACC_CUST_SESSIONRN = strtotime("now");
	$_SESSION['ACC_CUST_SESSIONRN'] = $ACC_CUST_SESSIONRN;
	$page15 = $_SESSION[page15.$ACC_CUST_SESSIONRN];
	$elements=explode("|",$page15);
	$mode_rate_type = "company";
	
	$deptAccessObj = new departmentAccess();

	// condition if customer is creating from the existing crm company
	if(isset($cfrm) && ($cfrm=="new" ||  $cfrm=="newRepeat"))
	{
		$type_order_id 	= $srnum; // existing crm comp
		
	}
	else
	{ 
		$type_order_id 	= $addr; // used when open the record from grid	
	}
   
	if($venfrm=='yes')
	{
		if($sesreg=='y')
		{
			if(isset($_SESSION['VconsultingAppuser']))
				session_unregister("VconsultingAppuser");

			if(isset($_SESSION['VconsultingEmpIds']))
				session_unregister("VconsultingEmpIds");

			session_register("VconsultingAppuser");
			session_register("VconsultingEmpIds");
			$_SESSION['VconsultingAppuser']='';
			$_SESSION['VconsultingEmpIds']='';
		}
	}

	if($new_divs == 'yes')
	   $test_divs = $Divisions;
	   
	// Sunil Written on 05-02-2014 Fr Keep the CRM Contacts in Temp table before update the form.
	if($delCrm != 1){
		if($cfrm == "new" || $cfrm == "newRepeat"){	
				$crmConFlag = 1;
				if($srnum != ''){  //for existing vendor window		
					$crmConArr = fetchCRMContacts($srnum,$_SESSION['username']);
			
				}else{ 		
					if($cfrm == "new"){ //sanghamitra : if new window for creating consulting vendor 
					    deleteTempContacts($_SESSION['username']); //delete temp contacts
					    $crmConArr = array();
				     }else{ //sanghamitra :already opened window and already contacts & candidate were added but not yet saved
				            $crmConArr = fetchTempCRMContacts($srnum,$_SESSION['username']);
					 }
					
				}					
				$_SESSION['tempCRMSno']	= $srnum;				
		}
	}else{  	  
			$crmConFlag = 1;
			$crmConArr = fetchTempCRMContacts($srnum,$_SESSION['username']);	
			$_SESSION['tempCRMSno']	= $srnum;
	}
   
	//Sunil Code Ends Here	
	if($cfrm == "new" )
	{		
		session_unregister("insno1".$Rnd);
		session_unregister("oppr_ses".$Rnd);
		session_unregister("acc_ses".$Rnd);
		session_unregister("oppsno".$Rnd);
		session_unregister("Divisions");
		session_unregister("ContactCandidates_sess");//Added by vijaya to store the deleted candidates of contacts of the CRM company.

		$Divisions = '';
		$navigateFrom = "new";
		$cfrm="";
		$page11[43] = '10.00';
		if($vendorcand!='y')
		{
			session_unregister("edit_company".$Rnd);
			$_SESSION['edit_company'.$Rnd]='';
		}
	}
	
	function fetchTempCRMContacts($srnum,$username){
		global $db;
		if($srnum != '')
			$selQry = "SELECT sno,suffix,nickname,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,csno,status,cat_id,accessto,ctype,id FROM temp_staffoppr_contact WHERE username='".$username."' AND status='ER' AND csno='".$srnum."'";
		else
			$selQry = "SELECT sno,suffix,nickname,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,csno,status,cat_id,accessto,ctype,id FROM temp_staffoppr_contact WHERE username='".$username."' AND status='ER'";
		
		$sour_res = mysql_query($selQry,$db);
		$i=0;	
		while($rowRes = mysql_fetch_row($sour_res)){            	
			$name1=html_tls_entities($rowRes[3],ENT_QUOTES)." ".html_tls_entities($rowRes[4],ENT_QUOTES)." ".html_tls_entities($rowRes[5],ENT_QUOTES);
			$email1=html_tls_entities($rowRes[6],ENT_QUOTES);
			$phone1=html_tls_entities($rowRes[7],ENT_QUOTES);
			$ytitle1=html_tls_entities($rowRes[12],ENT_QUOTES);
			$contacttype=getManage($rowRes[17]);			
			$arr[$i]=$name1."|".$ytitle1."|".$contacttype."|".$phone1."|".$rowRes[14]."|".$rowRes[13]."|".$rowRes[15]."|".$rowRes[0]."|".$rowRes[18];
			$arry[$i]=explode("|",$arr[$i]);
			$i++;
		}
		return $arry;	
	}
	function deleteTempContacts($username){
		global $db;	
		$delSql = "delete from temp_staffoppr_contact WHERE username='".$username."'";
		$resDel = mysql_query($delSql,$db);
	}	
	
	function fetchCRMContacts($srnum,$username){
		global $db;	
		$delSql = "delete from temp_staffoppr_contact WHERE username='".$username."' and csno='".$srnum."'";
		$resDel = mysql_query($delSql,$db);
		
		
		$insQry = "INSERT INTO temp_staffoppr_contact(sno,prefix,nickname,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,csno,approveuser,status,dauser,dadate,stime,cat_id,bpsaid,accessto,ctype,source,department,certifications,codes,keywords,messengerid,spouse,suffix,address1,address2,city,state,country,zipcode,owner,mdate,muser,dontcall,dontemail,fcompany,sourcetype,reportto,crmcontact,maincontact,cont_data,vcount,acc_cont,sysuserid,wphone_extn,hphone_extn,other_info,email_2,email_3,description,importance,source_name,reportto_name,spouse_name,stateid,deptid,username) 
		
		SELECT sno,prefix,nickname,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,csno,approveuser,status,dauser,dadate,stime,cat_id,bpsaid,accessto,ctype,source,department,certifications,codes,keywords,messengerid,spouse,suffix,address1,address2,city,state,country,zipcode,owner,mdate,muser,dontcall,dontemail,fcompany,sourcetype,reportto,crmcontact,maincontact,cont_data,vcount,acc_cont,sysuserid,wphone_extn,hphone_extn,other_info,email_2,email_3,description,importance,source_name,reportto_name,spouse_name,stateid,deptid,$username FROM staffoppr_contact WHERE csno = '".$srnum."' AND status='ER'";
		$res = mysql_query($insQry,$db);
		
		$selQry = "SELECT sno,suffix,nickname,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,csno,status,cat_id,accessto,ctype,id FROM temp_staffoppr_contact WHERE username='".$username."' and csno='".$srnum."' AND status='ER'";
		$sour_res = mysql_query($selQry,$db);
		$i=0;	
		while($rowRes = mysql_fetch_row($sour_res)){		    
			$name1=html_tls_entities($rowRes[3],ENT_QUOTES)." ".html_tls_entities($rowRes[4],ENT_QUOTES)." ".html_tls_entities($rowRes[5],ENT_QUOTES);
			$email1=html_tls_entities($rowRes[6],ENT_QUOTES);
			$phone1=html_tls_entities($rowRes[7],ENT_QUOTES);
			$ytitle1=html_tls_entities($rowRes[12],ENT_QUOTES);
			$contacttype=getManage($rowRes[17]);			
			$arr[$i]=$name1."|".$ytitle1."|".$contacttype."|".$phone1."|".$rowRes[14]."|".$rowRes[13]."|".$rowRes[15]."|".$rowRes[0]."|".$rowRes[18];
			$arry[$i]=explode("|",$arr[$i]);
			$i++;
		}
		return $arry;
	}

	function sele($a,$b)
	{
		if($a==$b)
			return "selected";
		else
			return "";
	}
//echo "Session value: "; print_r($_SESSION['edit_company'.$Rnd]); exit;	
	if($_SESSION['edit_company'.$Rnd]!='')
	{
		$page11=explode("|",$_SESSION['edit_company'.$Rnd]);	
	
		$parking=$page11[31];
		if($parking != '')
		{
			$exp_spd = explode("^",$page11[31]);
			$sptdd = explode("-",$exp_spd[0]);
		}

		$billcontact=$page11[34];
		$billcompany=$page11[35];
		$sec_bill_contact_snos = $page11[68];


		if($billcontact!=0)
		{
    		if($crm_select == 'yes')
			{
				$que2="select TRIM(CONCAT_WS('',fname,' ',lname,'(',IF(email='',nickname,email),')')),status,fname,mname,lname from staffoppr_contact where sno='".$billcontact."'";
				$res2=mysql_query($que2,$db);
				$row2=mysql_fetch_row($res2);
				$billcont = $row2[2]." ".$row2[3]." ".$row2[4];
				$billcont_stat=$row2[1];
			}
			else
			{
				$que2="select TRIM(CONCAT_WS('',fname,' ',lname,'')),fname,mname,lname from staffacc_contact where sno='".$billcontact."' ";
				$res2=mysql_query($que2,$db);
				$row2=mysql_fetch_row($res2);
				$billcont = $row2[1]." ".$row2[2]." ".$row2[3];
				$billcont_stat="";
			}
		}

		if($billcompany!=0)
		{
			if($crm_select == "yes")
			{
				$que2="select cname,status,address1,address2,city,state  from staffoppr_cinfo where sno='".$page11[35]."'";
				$res2=mysql_query($que2,$db);
				$row2=mysql_fetch_row($res2);

				$billcomp = '';
				if($row2[2] != '')
				{
				  $billcomp .= $row2[2];
				}
				if($row2[3] != '')
				{
				  $billcomp .= " ".$row2[3];
				}
				if($row2[4] != '')
				{
				  $billcomp .= " ".$row2[4];
				}
				if($row2[5] != '')
				{
				  $billcomp .= " ".$row2[5];
				}
				
				$bill_cname = $row2[0];
				$billcomp_stat=$row2[1];
				$billuname = $page11[35]; 
			}
			else
			{
				$que2="select cname,username,address1,address2,city,state  from staffacc_cinfo where sno='".$billcompany."'";
				$res2=mysql_query($que2,$db);
				$row2=mysql_fetch_row($res2);
				$billcomp = '';
				if($row2[2] != '')
				{
				  $billcomp .= $row2[2];
				}
				if($row2[3] != '')
				{
				  $billcomp .= " ".$row2[3];
				}
				if($row2[4] != '')
				{
				  $billcomp .= " ".$row2[4];
				}
				if($row2[5] != '')
				{
				  $billcomp .= " ".$row2[5];
				}
				
				$bill_cname = $row2[0];
				$billcomp_stat = "";
				$billuname = $row2[1]; 
			}
		}

		$insno = $page11[40];
		$oppsno = $page11[41]; 

		if($page11[5] == "0" && $page11[47] != "") //Checking the stateid, it is Other State or US State.
		{
			$page11[5] = "Other^0";
		}
		else
		{
			$page11[5] = $page11[47]."^".$page11[5];
		}

		if($insno!=0)
		{
			if($_SESSION['insno1'.$Rnd]=="")
				$_SESSION['insno1'.$Rnd]=$insno;
			else
				$_SESSION['insno1'.$Rnd].=",".$insno;
		}
		$_SESSION['insno1'.$Rnd]= trim($_SESSION['insno1'.$Rnd], ",");		

		$exp_contact=explode(",",$_SESSION['insno1'.$Rnd]);
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

		if(empty($_SESSION['acc_ses'.$Rnd]))
		$_SESSION['acc_ses'.$Rnd]=$_SESSION['insno1'.$Rnd];

		$_SESSION['oppr_ses'.$Rnd] = implode(",",array_unique(explode(",",trim($_SESSION['oppr_ses'.$Rnd], ","))));
		$_SESSION['acc_ses'.$Rnd] = implode(",",array_unique(explode(",",trim($_SESSION['acc_ses'.$Rnd], ","))));
		$contacts="";
		$candidates="";
		
		if($_SESSION['insno1'.$Rnd]!="" && $crm_editable != "yes" && $crm_select != "yes")
		{		  
			$que2="SELECT 
					stc.sno, stc.suffix, stc.nickname, stc.fname, stc.mname, stc.lname, stc.email, stc.wphone,
					stc.hphone, stc.mobile, stc.fax, stc.other, stc.ytitle, '0', stc.username, stc.cat_id, stc.accessto,
					stc.ctype, stc.crm_cont, IF(usr.userid != '','Y', 'N') AS css_user
				FROM 
					staffacc_contact stc
					LEFT JOIN staffacc_contactacc acc ON stc.sno = acc.con_id
					LEFT JOIN users usr ON acc.username = usr.username
				WHERE 
					stc.username='".$addr."' AND stc.status='ER' AND stc.acccontact='Y'";
			$res2=mysql_query($que2,$db);
			while($row2=mysql_fetch_row($res2))
			{
				if($contacts=="")
				{
					$accCrmSno .= $row2[18].",";
					$contacts="|||".$row2[2]."|".$row2[3]."|".$row2[4]."|".$row2[5]."|".$row2[6]."|".$row2[7]."|".$row2[8]."|".$row2[9]."|".$row2[10]."|".$row2[11]."|".$row2[12]."|".$row2[13]."|".$row2[0]."|".$row2[22]."|".$row2[17]."|".$row2[19];
				}else{
					$accCrmSno .= $row2[18].",";
					$contacts.="^|||".$row2[2]."|".$row2[3]."|".$row2[4]."|".$row2[5]."|".$row2[6]."|".$row2[7]."|".$row2[8]."|".$row2[9]."|".$row2[10]."|".$row2[11]."|".$row2[12]."|".$row2[13]."|".$row2[0]."|".$row2[22]."|".$row2[17]."|".$row2[19];
				}	
			}
			$que3="select sno,suffix,nickname,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,csno,status,cat_id,accessto,ctype from staffoppr_contact where status='ER' and sno in (".$accCrmSno.") and crmcontact='Y'";
			
			$res3=mysql_query($que3,$db);
			while($row2=mysql_fetch_row($res3))
			{
				if($contacts=="")
					$contacts="|||".$row2[2]."|".$row2[3]."|".$row2[4]."|".$row2[5]."|".$row2[6]."|".$row2[7]."|".$row2[8]."|".$row2[9]."|".$row2[10]."|".$row2[11]."|".$row2[12]."|".$row2[13]."|".$row2[0]."|".$row2[22]."|".$row2[17];
				else
					$contacts.="^|||".$row2[2]."|".$row2[3]."|".$row2[4]."|".$row2[5]."|".$row2[6]."|".$row2[7]."|".$row2[8]."|".$row2[9]."|".$row2[10]."|".$row2[11]."|".$row2[12]."|".$row2[13]."|".$row2[0]."|".$row2[22]."|".$row2[17];
			}
		}
		else
		{		   
			$que2="SELECT 
					stc.sno, stc.suffix, stc.nickname, stc.fname, stc.mname, stc.lname, stc.email, stc.wphone,
					stc.hphone, stc.mobile, stc.fax, stc.other, stc.ytitle, '0', stc.status, stc.cat_id, stc.accessto,
					stc.ctype, stc.crm_cont, IF(usr.userid != '','Y', 'N') AS css_user
				FROM 
					staffacc_contact stc
					LEFT JOIN staffacc_contactacc acc ON stc.sno = acc.con_id
					LEFT JOIN users usr ON acc.username = usr.username
				WHERE 
					stc.status='ER' AND stc.username='".$addr."' AND stc.acccontact='Y'";
			$res2=mysql_query($que2,$db);
			while($row2=@mysql_fetch_row($res2))
			{
				if($contacts==""){
					$accCrmCont .= $row2[18].',';
					$contacts="|||".$row2[2]."|".$row2[3]."|".$row2[4]."|".$row2[5]."|".$row2[6]."|".$row2[7]."|".$row2[8]."|".$row2[9]."|".$row2[10]."|".$row2[11]."|".$row2[12]."|".$row2[13]."|".$row2[0]."|".$row2[22]."|".$row2[17]."|".$row2[19];
				}else{
					$accCrmCont .= $row2[18].',';
					$contacts.="^|||".$row2[2]."|".$row2[3]."|".$row2[4]."|".$row2[5]."|".$row2[6]."|".$row2[7]."|".$row2[8]."|".$row2[9]."|".$row2[10]."|".$row2[11]."|".$row2[12]."|".$row2[13]."|".$row2[0]."|".$row2[22]."|".$row2[17]."|".$row2[19];
				}
			}
			$accCrmCont = rtrim($accCrmCont,',');
			print "<script type='text/javascript'> document.getElementById('opprSno').value = '".$accCrmCont."' </script>";
			//Checking a condition not to get the staffoppr_contact sno's, when staffacc_contact session exists.
			if($_SESSION['oppr_ses'.$Rnd] == '' && $_SESSION['acc_ses'.$Rnd] == '')
				$crmSnos = 	$oppsno;
			else
				$crmSnos = 	$_SESSION['oppr_ses'.$Rnd];
				
			//Removed single quotes for the variable to get all the contacts to display, when candidate is added
			$que3="select sno,suffix,nickname,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,csno,status,cat_id,accessto,ctype from staffoppr_contact where status='ER' and sno in (".$crmSnos.") and crmcontact='Y'";
			$res3=mysql_query($que3,$db);
			while($row2=mysql_fetch_row($res3))
			{
				if($contacts=="")
					$contacts="|||".$row2[2]."|".$row2[3]."|".$row2[4]."|".$row2[5]."|".$row2[6]."|".$row2[7]."|".$row2[8]."|".$row2[9]."|".$row2[10]."|".$row2[11]."|".$row2[12]."|".$row2[13]."|".$row2[0]."|".$row2[22]."|".$row2[17];
				else
					$contacts.="^|||".$row2[2]."|".$row2[3]."|".$row2[4]."|".$row2[5]."|".$row2[6]."|".$row2[7]."|".$row2[8]."|".$row2[9]."|".$row2[10]."|".$row2[11]."|".$row2[12]."|".$row2[13]."|".$row2[0]."|".$row2[22]."|".$row2[17];
			}
		}

		if($contacts!="")
		{
			$tok1=explode("^",$contacts);
			for($i=0;$i<count($tok1);$i++)
			{
				$fdata[$i]=explode("|",$tok1[$i]);
				$name1=html_tls_entities($fdata[$i][4],ENT_QUOTES)." ".html_tls_entities($fdata[$i][5],ENT_QUOTES)." ".html_tls_entities($fdata[$i][6],ENT_QUOTES);
				$email1=html_tls_entities($fdata[$i][7],ENT_QUOTES);
				$phone1=html_tls_entities($fdata[$i][8],ENT_QUOTES);
				$ytitle1=html_tls_entities($fdata[$i][13],ENT_QUOTES);

				$contacttype=getManage($fdata[$i][17]);
				$chk_cssuser=$fdata[$i][18];
				$arr[$i]=$name1."|".$ytitle1."|".$contacttype."|".$phone1."|".$fdata[$i][15]."|".$fdata[$i][14]."|".$fdata[$i][16]."|".$chk_cssuser;
				$arry[$i]=explode("|",$arr[$i]);
			}
		}
	}
	else
	{
		$c_owner=$username;
		$c_share="public";
	}
    
	

	//This loop comes when we add a former company from Contacts Summary
 	if($frmcontsum==true)
 	{
		session_unregister('cmp_id');
		$comp_que="select cname from staffoppr_cinfo where staffoppr_cinfo.sno=".$cmp_id;
		$comp_res=mysql_query($comp_que,$db);
		$comp_row=mysql_fetch_row($comp_res);
		$com_name=$comp_row[0];
		session_register('cmp_id');
	}

	$Users_Sql="select us.username,us.name,su.crm from users us,sysuser su where us.username=su.username and us.name!='' and us.status != 'DA' and su.crm !='NO' order by us.name";
	$Users_Res=mysql_query($Users_Sql,$db);

	$Users_Array=array();
	while($Users_Data=mysql_fetch_row($Users_Res))
	{
		$Users_Array[$Users_Data[0]]=$Users_Data[1];
	}

	$User_nos=implode(",",array_keys($Users_Array));
	$uersCnt=count($Users_Array);

	$sql_loc="SELECT serial_no FROM contact_manage WHERE deflt='Y'";
	$res_loc=mysql_query($sql_loc,$db);
	$fetch_loc=mysql_fetch_row($res_loc);
	$loc_user=$fetch_loc[0];

	if($_SESSION['edit_company'.$Rnd] == "")
	{
		$clientType="CUST";
		$bill_pay_terms_field="bill_req";
		if($venfrm == 'yes')
		{
			$clientType="CV";
			$bill_pay_terms_field="ven_bill_terms";
		}	

		if($srnum!="" && $statval!="")
		{
			$que1="select cname,curl,address1,address2,city,state,zip,country,phone,com_revenue,fax,nemployee,industry,nloction,nbyears,csource,ctype,siccode,compowner,federalid,ticker,csize,status,compbrief,compsummary,keytech,bill_req,service_terms, dress_code,tele_policy,smoke_policy,CONCAT_WS('^',parking,park_rate),directions,culture, bill_contact,bill_address,acc_comp,alternative_id,phone_extn,'','','','','',department,address_desc,deptid,ts_layout_pref from staffoppr_cinfo where sno='".$srnum."' and  status='".$statval."' and crmcompany = 'Y'";			
			$res1=mysql_query($que1,$db);
			$page11=mysql_fetch_row($res1);
			$page11[56] = $page11[26];
			$db_cust_timesheet_layout_preference = $page11[47];	//timesheet layout preference field		
			$getstateVal = explode("|AKKENEXP|",getStateIdAbbr($page11[5],$page11[7]));
		
			$getDepartmentId = "SELECT deptid from Client_Accounts WHERE typeid='".$page11[36]."' AND status='active' AND clienttype='CUST'";
			$resDepartmnetId = mysql_query($getDepartmentId,$db);
			$roeDepartmentId = mysql_fetch_array($resDepartmnetId);
			
			
			if ($roeDepartmentId[0])
			{
				$page11[63] = $roeDepartmentId[0];
			}
			else
			{
				$page11[63] = $page11[46];
			}
				
			if($getstateVal[2] == 0 && $getstateVal[0] != "")
			{
				$page11[5] = "Other^0";
			}
			else
			{
				$page11[5] = $getstateVal[1]."^".$getstateVal[2];
			}
			
			$page11[47] = $getstateVal[0];
				
			
			if($corp_codeval!='')           //assigning corp code value when crm company is selected or updated
				$page11[39]=$corp_codeval;
			
			if($page11[34]!=0)
			{
				$que2="select TRIM(CONCAT_WS('',fname,' ',lname,'(',IF(email='',nickname,email),')')),status,fname,mname,lname from staffoppr_contact where sno='".$page11[34]."'";
				$res2=mysql_query($que2,$db);
				$row2=mysql_fetch_row($res2);
				$billcont = $row2[2]." ".$row2[3]." ".$row2[4];
				$billcont_stat=$row2[1];
			}
				
			//for Parking info
			if($page11[31]!="")
			{
				$exp_spd = explode("^",$page11[31]);
				$sptdd = explode("|",$exp_spd[0]);
			}
			
			if($page11[35]!=0)
			{
				$que2="select cname,status,address1,address2,city,state  from staffoppr_cinfo where sno='".$page11[35]."'";
				$res2=mysql_query($que2,$db);
				$row2=mysql_fetch_row($res2);
				$billcomp = '';
				if($row2[2] != '')
				{
				  $billcomp .= $row2[2];
				}
				if($row2[3] != '')
				{
				  $billcomp .= " ".$row2[3];
				}
				if($row2[4] != '')
				{
				  $billcomp .= " ".$row2[4];
				}
				if($row2[5] != '')
				{
				  $billcomp .= " ".$row2[5];
				}
				
				$bill_cname = $row2[0];
				$billcomp_stat=$row2[1];
				$billuname = $page11[35];
			}
			if($crm_select == "yes" && $crm_editable!="yes")
			{
				$que2="select sno,suffix,nickname,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,csno,status,cat_id,accessto,ctype from staffoppr_contact where status='ER' and csno='".$srnum."' and crmcontact ='Y'";
				
				$res2=mysql_query($que2,$db);
				
				$crm_oppr="";
				
				while($row2=mysql_fetch_row($res2))
				{
					if($crm_oppr == "")
					 $crm_oppr = "oppr^^".$row2[0];
					else
					 $crm_oppr .= ",oppr^^".$row2[0];
					 
					if($contacts=="")
						$contacts="|||".$row2[2]."|".$row2[3]."|".$row2[4]."|".$row2[5]."|".$row2[6]."|".$row2[7]."|".$row2[8]."|".$row2[9]."|".$row2[10]."|".$row2[11]."|".$row2[12]."|".$row2[13]."|".$row2[0]."|".$row2[22]."|".$row2[17];
					else
						$contacts.="^|||".$row2[2]."|".$row2[3]."|".$row2[4]."|".$row2[5]."|".$row2[6]."|".$row2[7]."|".$row2[8]."|".$row2[9]."|".$row2[10]."|".$row2[11]."|".$row2[12]."|".$row2[13]."|".$row2[0]."|".$row2[22]."|".$row2[17];
				}
				
				//$_SESSION['insno1'.$Rnd] = $crm_oppr;
				
				if($contacts!="")
				{
					$tok1=explode("^",$contacts);
					for($i=0;$i<count($tok1);$i++)
					{
						$fdata[$i]=explode("|",$tok1[$i]);
						$name1=html_tls_entities($fdata[$i][4],ENT_QUOTES)." ".html_tls_entities($fdata[$i][5],ENT_QUOTES)." ".html_tls_entities($fdata[$i][6],ENT_QUOTES);
						$email1=html_tls_entities($fdata[$i][7],ENT_QUOTES);
						$phone1=html_tls_entities($fdata[$i][8],ENT_QUOTES);
						$ytitle1=html_tls_entities($fdata[$i][13],ENT_QUOTES);
				
						$contacttype=getManage($fdata[$i][17]);
						$arr[$i]=$name1."|".$ytitle1."|".$contacttype."|".$phone1."|".$fdata[$i][15]."|".$fdata[$i][14]."|".$fdata[$i][16];
						$arry[$i]=explode("|",$arr[$i]);
					}
				}
			}
		}
		else if($srnum!="" && $addr!="")
		{
			//Updating billing_default field as N, when the billing address is not equal to current company.
			$acc_updQry = "UPDATE staffacc_cinfo SET billing_default = 'N', muser='".$username."', mdate=NOW() WHERE bill_address != sno AND username='".$addr."' AND acccompany = 'Y'";
			mysql_query($acc_updQry,$db);
			
			//Getting the state and stateid from staffacc_cinfo for the accounting customers.
			//Obtained  templateid from db
			
			$que1="select sc.cname,sc.curl,sc.address1,sc.address2,sc.city,sc.stateid,sc.zip,sc.country,sc.phone,sc.com_revenue,sc.fax,sc.nemployee,sc.industry,sc.nloction,sc.nbyears,sc.csource,sc.ctype,sc.siccode,sc.compowner,sc.federalid,sc.ticker,sc.csize,sc.username,sc.compbrief,sc.compsummary,sc.keytech,".$bill_pay_terms_field.",sc.service_terms,sc.dress_code,sc.tele_policy,sc.smoke_policy,CONCAT_WS('^',sc.parking,park_rate),sc.directions, sc.culture,sc.bill_contact,sc.bill_address,sc.sno,sc.alternative_id,sc.phone_extn,sc.corp_code,'','','',sc.tax,sc.department,sc.address_desc,sc.billing_default,sc.state,sc.madison_customerid,ca.loc_id,ca.taxes_pay_acct,ca.acct_receive,ca.acct_payable,ca.acct_miscIncome,ca.acct_miscExpense,'','',sc.inv_method,sc.inv_terms,'','',sc.cust_classid,sc.vend_classid,ca.deptid,sc.templateid,sc.attention,sc.inv_delivery_option,sc.inv_email_templateid,sec_bill_contact,sc.ts_layout_pref from staffacc_cinfo sc LEFT JOIN Client_Accounts ca ON (ca.typeid=sc.sno AND ca.status='active' AND ca.clienttype='".$clientType."') WHERE sc.username='".$addr."' AND acccompany = 'Y'";
			$res1=mysql_query($que1,$db);
			$page11=mysql_fetch_array($res1);
			$db_cust_timesheet_layout_preference = $page11['ts_layout_pref'];	//timesheet layout preference field	
			
			if($clientType == 'CV' && $venfrm=='yes' && $edit_acc != 'yes')
			{
				$getDepartmentId = "SELECT ca.deptid from staffacc_cinfo sc LEFT JOIN Client_Accounts ca ON (ca.typeid=sc.sno AND ca.status='active' AND ca.clienttype='CUST') WHERE sc.username='".$addr."' AND acccompany = 'Y'";
				$resDepartmnetId = mysql_query($getDepartmentId,$db);
				$roeDepartmentId = mysql_fetch_array($resDepartmnetId);
				
				$page11[63] = $roeDepartmentId[0];
			}

			if($venfrm=='yes' && $edit_acc != 'yes')
			{
				$page11[43]="10.00";			
			}

			if($venfrm=='yes')
				$page11[61]=$page11[62];

			if($page11[5] == "0" && $page11[47] != "") //Checking the stateid, it is Other State or US State.
			   $page11[5] = "Other^0";
			else
			   $page11[5] = $page11[47]."^".$page11[5];
			
			if($page11[34]!=0)
			{
				$que2="select TRIM(CONCAT_WS('',fname,' ',lname,' ',email)),fname,mname,lname from staffacc_contact where sno='".$page11[34]."' ";
				$res2=mysql_query($que2,$db);
				$row2=mysql_fetch_row($res2);
				$billcont = $row2[1]." ".$row2[2]." ".$row2[3];
				$billcont_stat="0";
			}

			if($page11[35]!=0)
			{
				$que2="select cname,username,address1,address2,city,state  from staffacc_cinfo where sno='".$page11[35]."'";
				$res2=mysql_query($que2,$db);
				$row2=mysql_fetch_row($res2);
				$billcomp = '';
				if($row2[2] != '')
				{
				  $billcomp .= $row2[2];
				}
				if($row2[3] != '')
				{
				  $billcomp .= " ".$row2[3];
				}
				if($row2[4] != '')
				{
				  $billcomp .= " ".$row2[4];
				}
				if($row2[5] != '')
				{
				  $billcomp .= " ".$row2[5];
				}
				
				$bill_cname = $row2[0];
				$billcomp_stat="";
				$billuname = $row2[1]; 
			}

			//For Parking information
			if($page11[31]!="")
			{
				$exp_spd = explode("^",$page11[31]);
				$sptdd = explode("|",$exp_spd[0]);
			}

			if($crm_select == "no" && $crm_editable!="no")
			{
				$que2="SELECT 
					stc.sno, stc.suffix, stc.nickname, stc.fname, stc.mname, stc.lname, stc.email, stc.wphone,
					stc.hphone, stc.mobile, stc.fax, stc.other, stc.ytitle, '0', stc.username, stc.cat_id, stc.accessto,
					stc.ctype, stc.crm_cont, IF(usr.userid != '','Y', 'N') AS css_user
				FROM 
					staffacc_contact stc
					LEFT JOIN staffacc_contactacc acc ON stc.sno = acc.con_id
					LEFT JOIN users usr ON acc.username = usr.username
				WHERE 
					stc.username='".$addr."' AND stc.status='ER' AND stc.acccontact='Y'";
				$res2=mysql_query($que2,$db);

				$crm_oppr="";

				while($row2=mysql_fetch_row($res2))
				{
					if($crm_oppr == "")
					 $crm_oppr = "acc^^".$row2[0];
					else
					 $crm_oppr .= ",acc^^".$row2[0];

					if($contacts=="")
						$contacts="|||".$row2[2]."|".$row2[3]."|".$row2[4]."|".$row2[5]."|".$row2[6]."|".$row2[7]."|".$row2[8]."|".$row2[9]."|".$row2[10]."|".$row2[11]."|".$row2[12]."|".$row2[13]."|".$row2[0]."|".$row2[22]."|".$row2[17]."|".$row2[19];
					else
						$contacts.="^|||".$row2[2]."|".$row2[3]."|".$row2[4]."|".$row2[5]."|".$row2[6]."|".$row2[7]."|".$row2[8]."|".$row2[9]."|".$row2[10]."|".$row2[11]."|".$row2[12]."|".$row2[13]."|".$row2[0]."|".$row2[22]."|".$row2[17]."|".$row2[19];
				}

				if($contacts!="")
				{
					$tok1=explode("^",$contacts);
					for($i=0;$i<count($tok1);$i++)
					{
						$fdata[$i]=explode("|",$tok1[$i]);
						$name1=html_tls_entities($fdata[$i][4],ENT_QUOTES)." ".html_tls_entities($fdata[$i][5],ENT_QUOTES)." ".html_tls_entities($fdata[$i][6],ENT_QUOTES);
						$email1=html_tls_entities($fdata[$i][7],ENT_QUOTES);
						$phone1=html_tls_entities($fdata[$i][8],ENT_QUOTES);
						$ytitle1=html_tls_entities($fdata[$i][13],ENT_QUOTES);

						$contacttype=getManage($fdata[$i][17]);
						$chk_cssuser=$fdata[$i][18];
						$arr[$i]=$name1."|".$ytitle1."|".$contacttype."|".$phone1."|".$fdata[$i][15]."|".$fdata[$i][14]."|".$fdata[$i][16]."|".$chk_cssuser;
						$arry[$i]=explode("|",$arr[$i]);
					}
				}
			}
		}
		else if($srnum!="" && $addr=="" && $venfrm == 'yes')
		{
			//Updating billing_default field as N, when the billing address is not equal to current company.
			$acc_updQry = "UPDATE staffacc_cinfo SET billing_default = 'N', muser='".$username."', mdate=NOW() WHERE bill_address != sno AND username='".$srnum."' AND acccompany = 'Y'";
			mysql_query($acc_updQry,$db);

			//Getting the state and stateid from staffacc_cinfo for the accounting vendors.
			$que1="select cname,curl,address1,address2,city,stateid,zip,country,phone,com_revenue,fax,nemployee,industry,nloction, nbyears,csource,ctype,siccode,compowner,federalid,ticker,csize,username,compbrief,compsummary,keytech,".$bill_pay_terms_field.",service_terms, dress_code,tele_policy,smoke_policy,CONCAT_WS('^',parking,park_rate),directions,culture,bill_contact,bill_address,sno, alternative_id,phone_extn,corp_code,'','','',tax,department,address_desc,state,'',ts_layout_pref from staffacc_cinfo where username='".$srnum."' and acccompany = 'Y'";
			$res1=mysql_query($que1,$db);
			$page11=mysql_fetch_row($res1);
			$db_cust_timesheet_layout_preference = $page11[48];	//timesheet layout preference field	
			
			$que_client_acc="SELECT loc_id,taxes_pay_acct,acct_payable,acct_receive,deptid FROM Client_Accounts WHERE typeid='".$page11[35]."' AND status = 'active' AND clienttype='CV'";
			$res_client_acc=mysql_query($que_client_acc,$db);
			$fetch_client_acc=mysql_fetch_row($res_client_acc);

			$page11[49]=$fetch_client_acc[0];
			$page11[50]=$fetch_client_acc[1];
			$page11[51]=$fetch_client_acc[3];
			$page11[52]=$fetch_client_acc[2];
			$page11[63] = $fetch_client_acc[4];

			if($page11[5] == "0" && $page11[47] != "") //Checking the stateid, it is Other State or US State.
			   $page11[5] = "Other^0";
			else
			   $page11[5] = $page11[47]."^".$page11[5];

			if($page11[34]!=0)
			{
				$que2="select TRIM(CONCAT_WS('',fname,' ',lname,' ',email)),fname,mname,lname from staffacc_contact where sno='".$page11[34]."' ";
				$res2=mysql_query($que2,$db);
				$row2=mysql_fetch_row($res2);
				$billcont = $row2[1]." ".$row2[2]." ".$row2[3];
				$billcont_stat="0";
			}

			if($page11[35]!=0)
			{
				$que2="select cname,username,address1,address2,city,state  from staffacc_cinfo where sno='".$page11[35]."'";
				$res2=mysql_query($que2,$db);
				$row2=mysql_fetch_row($res2);
				$billcomp = '';
				if($row2[2] != '')
				{
				  $billcomp .= $row2[2];
				}
				if($row2[3] != '')
				{
				  $billcomp .= " ".$row2[3];
				}
				if($row2[4] != '')
				{
				  $billcomp .= " ".$row2[4];
				}
				if($row2[5] != '')
				{
				  $billcomp .= " ".$row2[5];
				}
				
				$bill_cname = $row2[0];
				$billcomp_stat="";
				$billuname = $row2[1]; 
			}

			//For Parking information
			if($page11[31]!="")
			{
				$exp_spd = explode("^",$page11[31]);
				$sptdd = explode("|",$exp_spd[0]);
			}

			$crm_oppr="";
			$que2="select sno,suffix,nickname,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,'0',username, cat_id,accessto,ctype from staffacc_contact where username='".$srnum."' and acccontact='Y'";
			$res2=mysql_query($que2,$db);
			while($row2=mysql_fetch_row($res2))
			{
				if($crm_oppr == "")
					$crm_oppr = "acc^^".$row2[0];
				else
					$crm_oppr .= ",acc^^".$row2[0];
					 
				if($contacts=="")
					$contacts="|||".$row2[2]."|".$row2[3]."|".$row2[4]."|".$row2[5]."|".$row2[6]."|".$row2[7]."|".$row2[8]."|".$row2[9]."|".$row2[10]."|".$row2[11]."|".$row2[12]."|".$row2[13]."|".$row2[0]."|".$row2[22]."|".$row2[17];
				else
					$contacts.="^|||".$row2[2]."|".$row2[3]."|".$row2[4]."|".$row2[5]."|".$row2[6]."|".$row2[7]."|".$row2[8]."|".$row2[9]."|".$row2[10]."|".$row2[11]."|".$row2[12]."|".$row2[13]."|".$row2[0]."|".$row2[22]."|".$row2[17];
			}

			if($contacts!="")
			{
				$tok1=explode("^",$contacts);
				for($i=0;$i<count($tok1);$i++)
				{
					$fdata[$i]=explode("|",$tok1[$i]);
					$name1=html_tls_entities($fdata[$i][4],ENT_QUOTES)." ".html_tls_entities($fdata[$i][5],ENT_QUOTES)." ".html_tls_entities($fdata[$i][6],ENT_QUOTES);
					$email1=html_tls_entities($fdata[$i][7],ENT_QUOTES);
					$phone1=html_tls_entities($fdata[$i][8],ENT_QUOTES);
					$ytitle1=html_tls_entities($fdata[$i][13],ENT_QUOTES);

					$contacttype=getManage($fdata[$i][17]);
					$arr[$i]=$name1."|".$ytitle1."|".$contacttype."|".$phone1."|".$fdata[$i][15]."|".$fdata[$i][14]."|".$fdata[$i][16];
					$arry[$i]=explode("|",$arr[$i]);
				}
			}
		}	
	}

	if($addr!="")
	{
		$sel_qury = "select sno from staffacc_cinfo where username= '".$addr."'";
		$sel_res = mysql_query($sel_qury,$db); 
		$sel_row = mysql_fetch_row($sel_res);

		$crmqry = "select acc_comp from staffoppr_cinfo where acc_comp = '".$sel_row[0]."'";
		$crmres = mysql_query($crmqry,$db);
		$crmnum = mysql_num_rows($crmres);

		if($crmnum == 0)
			$checkedcrm = "";
		else
			$checkedcrm = "Y";
		
		$cust_sno_val = $sel_row[0];
	}	

	$query_id="select sno from staffacc_cinfo where username='".$addr."' and acccompany = 'Y'";
	$res_query=mysql_query($query_id,$db);
	$fth_query=mysql_fetch_row($res_query);
	if($venfrm == 'yes')
	{
		if($edit_acc == 'yes')
			$ventiltel = "Edit Consulting Vendor";
		else
			$ventiltel = "Add Consulting Vendor";

		$AppuserId= $_SESSION['VconsultingAppuser'];
		if($_SESSION['VconsultingEmpIds']!='')
			$_SESSION['VconsultingEmpIds']=$_SESSION['VconsultingEmpIds'].",".$AppuserId;
		else
			$_SESSION['VconsultingEmpIds']=$AppuserId;

		$empIds=$_SESSION['VconsultingEmpIds'];

		$arremp = array_unique(explode(",",$empIds));
		foreach ($arremp as $emp)
		{
			if(trim($emp)!='')
				$temp_emp = ($temp_emp=='')?$emp:$temp_emp.','.$emp;
		}

		//Start of code for the display of candidates for consulting vendors--Kiran
		if($edit_acc=='yes')
		{
			//Checking added candidate is exists in vendorsubcon table or not in edit mode of consulting vendor.
			$qrysel_cand="SELECT c.username, e.username FROM emp_list e, candidate_list c WHERE find_in_set(e.username,'$temp_emp') AND c.candid=concat('emp',e.sno) AND e.lstatus!='INACTIVE'";
			$res_cand = mysql_query($qrysel_cand,$db);
			while($row_cand = mysql_fetch_row($res_cand))
			{
				$qrychk = "SELECT count(1) FROM vendorsubcon WHERE subid='".$row_cand[0]."' AND venid='".$addr."' AND empid='".$row_cand[1]."'";
				$reschk = mysql_query($qrychk,$db);
				$rowschk = mysql_fetch_row($reschk);
				if(!$rowschk[0]>0)
				{
					$quevendsub = "INSERT into vendorsubcon (sno,subid,venid,empid) VALUES('','".$row_cand[0]."','".$addr."','".$row_cand[1]."')";
					mysql_query($quevendsub,$db);

					$qryupdate_w4 = "UPDATE hrcon_w4 SET tax = 'C-to-C' WHERE ustatus='active' AND username = '".$row_cand[1]."' ";
					mysql_query($qryupdate_w4,$db);

					$qryupdate_ew4 = "UPDATE empcon_w4 SET tax = 'C-to-C' WHERE username = '".$row_cand[1]."' ";
					mysql_query($qryupdate_ew4,$db);

					$qryupdate_nw4 = "UPDATE net_w4 SET tax = 'C-to-C' WHERE ustatus='active' AND username = '".$row_cand[1]."' ";
					mysql_query($qryupdate_nw4,$db);
				}
			}

			//Dumping accounting company data into hrcon_w4, empcon_w4 and new_w4 tables -- kumar raju k.
			dumpW4TableData('',$addr); //Send these ( $opprCompId = staffoppr_cinfo.sno and $accCompId = staffacc_cinfo.username ) values to function.

			//Getting the candidates which are available in vendorsubcon table.
			$qryCand="SELECT DISTINCT e.sno,e.name,e.email,e.lstatus,e.username FROM emp_list e, vendorsubcon v WHERE v.venid='$addr' AND e.lstatus!='INACTIVE' AND e.username=v.empid";
		}
		else /* Modified by vijaya to show the candidates for the contacts of the selected CRM company */ 
		{
			//This is to show the candidates for the contacts of the selected company.	
			if($srnum!='')//Run this queries if company sno is present.
			{		
				if($edit_acc == 'no')
					$com_contact="SELECT staffoppr_contact.sno FROM staffoppr_contact LEFT JOIN staffoppr_cinfo ON staffoppr_contact.csno = staffoppr_cinfo.sno LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno = staffoppr_cinfo.acc_comp WHERE staffoppr_contact.status = 'ER' AND staffacc_cinfo.username = '".$srnum."' AND staffoppr_contact.crmcontact = 'Y'";
				else
					$com_contact="select sno from staffoppr_contact where status='ER' and csno='".$srnum."' and crmcontact ='Y'";
				$com_contactRes=mysql_query($com_contact,$db);			
				while($can_resArr = mysql_fetch_array($com_contactRes))
					$cand_ids = ($cand_ids=='')?$can_resArr[0]:$cand_ids.','.$can_resArr[0];

				//Checking the candidate is already an employee or not, changed the checking of candid equal to null in candidate_list table -- kumar raju k.
				$newqryCand = "SELECT cg.sno, CONCAT_WS(' ',cg.fname, cg.mname, cg.lname) AS name, cg.email, cg. username FROM candidate_general cg, candidate_list cl WHERE cg.username=cl.username AND cl.status='ACTIVE' AND find_in_set(cl.supid,'".$cand_ids."') AND (cl.ctype!='Employee' AND SUBSTRING(cl.candid,1,3) != 'emp')";

				if(isset($_SESSION['ContactCandidates_sess'])&& trim($_SESSION['ContactCandidates_sess'])!='')
					$newqryCand.=" AND cg.sno NOT IN (".$_SESSION['ContactCandidates_sess'].")";

				$resCand=mysql_query($newqryCand,$db);
				$m=0;
				$arrCandFromContact =array();
				while($rowCand=@mysql_fetch_array($resCand))
				{			
					$candtaxtype = 'C-to-C';	
					/* Below string format : name,taxtype, email, status, sno*/	
					//Removed appending of status column into candidates array -- kumar raju k.	
					$arrCandFromContact[$m]=array($rowCand['name'],$candtaxtype,$rowCand['email'],$rowCand['sno'],$rowCand['username']);				
					$m++;				
				}
			}
			$qryCand = "SELECT sno,name,email,lstatus,username FROM emp_list 
			WHERE find_in_set(username,'$temp_emp') AND lstatus !='INACTIVE'"; 
		}

		$k=0;
		$qryResEmp = mysql_query($qryCand,$db);	
		while($resCand = mysql_fetch_array($qryResEmp))
		{
			//Removed appending of status column into candidates array -- kumar raju k.		
			$arrCandVal[$k]=array($resCand['name'],'C-to-C',$resCand['email'],$resCand['sno']);
			$k++;
		}
		//End of code for display of candidates for consulting vendors--Kiran
	}//----------------------
	else
	{
		if($edit_acc == "yes")
			$ventiltel = "Edit Accounting Customer";
		else
			$ventiltel = "Add Accounting Customer";
	}
	 
	$corp_str="";	
	
	$Crpcode_Sql2="select MAX(LENGTH(CONCAT_WS('-',name,description))) from corp_code order by name";
	$Crpcode_Res2=mysql_query($Crpcode_Sql2,$db);
	$Crpcode_Data2=mysql_fetch_row($Crpcode_Res2);
	$corp_str=$Crpcode_Data2[0];
	 if($corp_str>=93)
	 	$wid_val="width:550px;";
/* ... .. Raj.. Checking server to change style for FF .. Raj .. ... */
	if(strpos($_SERVER['HTTP_USER_AGENT'],'Gecko') > -1){
		$rightflt = "style= 'width:35px;'";
	}//End of if(To know the server)
	
	
	 if(TRICOM_REPORTS=='Y'){
	$spl_Attribute = (TRICOM_REPORTS=='Y') ? 'udCheckNull ="YES" ' : '';	
	}else{
	$spl_Attribute = (PAYROLL_PROCESS_BY_MADISON=='MADISON') ? 'udCheckNull ="YES" ' : '';
	}
	//$spl_Attribute = (PAYROLL_PROCESS_BY_MADISON=='MADISON') ? 'udCheckNull ="YES" ' : '';
	
	$chkSessionAvail = "N";
	$sel_cont = "";
	$sel_contact="";
	$sel_contact_sno = "";
	$sel_status = "";
	if($_SESSION['edit_company'.$Rnd]!='')
	{
		$chkSessionAvail = "Y";
		
		if($_SESSION['insno1'.$Rnd] != "")
		{
			$getContIds = str_replace("acc^^","",$_SESSION['insno1'.$Rnd]);
		
			$sel_cont = "SELECT TRIM(CONCAT_WS('',fname,' ',lname,'(',IF(email='',nickname,email),')')),sno,fname,mname,lname,'0' FROM staffacc_contact WHERE sno in (".$getContIds.") AND acccontact = 'Y'";
		}
		else if($venfrm == 'yes' && ($page11[34]!="" && $page11[34]!="0"))//new consulting venor while adding candidate this wont excute..
		{
			$sel_cont = "SELECT TRIM(CONCAT_WS('',fname,' ',lname,'(',IF(email='',nickname,email),')')),sno,fname,mname,lname,status FROM staffoppr_contact WHERE status = 'ER' AND csno = '".$srnum."' AND crmcontact = 'Y'";
		}
	}
	else if($srnum!="" && $statval!="")
	{
		$sel_cont = "SELECT TRIM(CONCAT_WS('',fname,' ',lname,'(',IF(email='',nickname,email),')')),sno,fname,mname,lname,status FROM staffoppr_contact WHERE status = 'ER' AND csno = '".$srnum."' AND crmcontact = 'Y'";
	}
	else if($srnum!="" && $addr!="")
	{
		$sel_cont = "SELECT TRIM(CONCAT_WS('',fname,' ',lname,'(',IF(email='',nickname,email),')')),sno,fname,mname,lname,'0' FROM staffacc_contact WHERE username='".$addr."' AND acccontact = 'Y'";
	}
	else if($srnum!="" && $addr=="" && $venfrm == "yes")
	{
		$sel_cont = "SELECT TRIM(CONCAT_WS('',fname,' ',lname,'(',IF(email='',nickname,email),')')),sno,fname,mname,lname,'0' FROM staffacc_contact WHERE username = '".$srnum."' AND acccontact = 'Y'";
	}
	if($sel_cont!="")
	{
		$sel_res = mysql_query($sel_cont,$db);
		while($sel_row = mysql_fetch_row($sel_res))
		{
		  $sel_name = $sel_row[2]." ".$sel_row[3]." ".$sel_row[4];
		  $sel_contact .= html_tls_specialchars(addslashes($sel_name))."|";
		  $sel_contact_sno .= $sel_row[1]."|";
		  $sel_status .= $sel_row[5]."|";
		}
	}
	$list_cont = trim($sel_contact,"|");
	$list_cont_sno = trim($sel_contact_sno,"|");
	$list_status = trim($sel_status,"|");
	
// Fetching Dynamic UdfColNames
	$sql = "select t1.id, element_name, element_lable, t1.element, required, default_opt, auto_complete
			 from udf_form_details t1 left join udf_form_details_order t4 on t4.custom_form_details_id = t1.id
			  where t1.module  = 2 and t1.status = 'Active' order by t4.ele_order asc";

	 $result = mysql_query($sql, $db);
	 if(mysql_num_rows($result) > 0){
	    while($rowVal = mysql_fetch_array($result)){
			if($rowVal['auto_complete']=="Yes")
			{
				$colNames .= $rowVal['element_lable']."-".$rowVal['element']."_autoChk"."|";
			}
			else
			{
				$colNames .= $rowVal['element_lable']."-".$rowVal['element']."|";
			}	       
	    }
	    $colNames = substr($colNames,0,-1);
	 }
	 
	//Getting the burden details from company
	$comp_burden_data = '';
	//If selected the CRM company, then get the burden details from the selected crm company burden details
	if($crm_select == "yes" && $srnum != "" && $statval != "" && $navigateFrom == 'new')
	{
		$get_comp_burden_data = getLocBurdenDetails($srnum,'crm');
		$comp_burden_data = implode("|",$get_comp_burden_data);		
	}
	else if($cust_sno_val != '') // Get the accounting customer burden details when editing the customer
	{
		$get_comp_burden_data = getLocBurdenDetails($cust_sno_val,'acc');
		$comp_burden_data = implode("|",$get_comp_burden_data);
	}

	//Getting the Pay/Bill burden details with html form elements for selecting the pay/bill burden types
	$comp_bt_details_str = getBurdenDetailsFormElements('acccust',$comp_burden_data);
	$comp_bt_details_exp = explode("^^AKKENBTDETAILS^^",$comp_bt_details_str);
	$pay_bt_str = $comp_bt_details_exp[0];
	$bill_bt_str = $comp_bt_details_exp[1];
	
	
        /**sanghamitra : get the company status if its a consulting vendor company **/
	$compStatus=0;
	if($venfrm == 'yes'){
		$comStatusQue= mysql_query("select sno from manage where name LIKE '%vendor%' AND type='compstatus'",$db);
	        $comStatusRes = mysql_fetch_row($comStatusQue);
	        $compStatus = $comStatusRes[0];	
	
	} 

	$rolesSelectIds = array();

	if($strCon != "")
		$condition = " rp.commissionType NOT IN (".$strCon.") OR ";
		
		$queryRoles ="SELECT sno,roletitle  FROM company_commission WHERE status ='active' ORDER BY roletitle,commission_default"; 
		//$queryRoles = "SELECT cs.sno, cs.roletitle FROM company_commission AS cs left join rates_period AS rp ON (cs.sno = rp.parentid AND rp.parenttype = 'COMMISSION') WHERE cs.status = 'active'"; 

	/*$queryRoles = "SELECT cs.sno, cs.roletitle FROM company_commission AS cs left join rates_period AS rp ON (cs.sno = rp.parentid AND rp.parenttype = 'COMMISSION' AND (IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF(DATE_FORMAT(NOW(),'%Y-%m-%d'),STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, DATE_FORMAT(NOW(),'%Y-%m-%d') BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d')))) WHERE cs.status = 'active'"; */
	$queryDirectInternal = $queryRoles;
	$resDirectInternal  = mysql_query($queryDirectInternal,$db);
	$lstDirectInternal = $lstTempContact = $lstTempToDirect = "";
	while($rowDirectInternal = mysql_fetch_row($resDirectInternal))
	{
		$rolesSelectIds[] = $rowDirectInternal[0];
		if($lstDirectInternal == '')
			$lstDirectInternal = $rowDirectInternal[0]."^".$rowDirectInternal[1];
		else
			$lstDirectInternal .= "|Akkensplit|".$rowDirectInternal[0]."^".$rowDirectInternal[1];
	}

	$queryTempContact = $queryRoles;
	$resTempContact  = mysql_query($queryTempContact,$db);
	while($rowTempContact = mysql_fetch_row($resTempContact))
	{
		$rolesSelectIds[] = $rowTempContact[0];
		if($lstTempContact == '')
			$lstTempContact = $rowTempContact[0]."^".$rowTempContact[1];
		else
			$lstTempContact .= "|Akkensplit|".$rowTempContact[0]."^".$rowTempContact[1];
	}

	$queryTempToDirect = $queryRoles;
	$resTempToDirect  = mysql_query($queryTempToDirect,$db);
	while($rowTempToDirect = mysql_fetch_row($resTempToDirect))
	{
		$rolesSelectIds[] = $rowTempToDirect[0];
		if($lstTempToDirect == '')
			$lstTempToDirect = $rowTempToDirect[0]."^".$rowTempToDirect[1];
		else
			$lstTempToDirect .= "|Akkensplit|".$rowTempToDirect[0]."^".$rowTempToDirect[1];
	}
	//End code for populating Comission roles

	//Query to get the internal employees for roles
	$que="SELECT e.username as username, e.name as name 
			FROM emp_list e
			INNER JOIN users urs ON (urs.username = e.username)
			LEFT JOIN hrcon_compen h ON (h.username = e.username) 
			LEFT JOIN manage m ON (m.sno = h.emptype) WHERE e.lstatus != 'DA' AND e.lstatus != 'INACTIVE' AND e.empterminated !='Y' AND h.ustatus = 'active' AND m.type = 'jotype' AND m.status='Y' AND m.name IN ('Internal Direct', 'Internal Temp/Contract') AND h.job_type <> 'Y' 
			ORDER BY e.name"; 
	$res=mysql_query($que,$db);

?>
<?php include("header.inc.php");?>
<title><?php echo $ventiltel; ?></title>
<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/candidatesInfo_tab.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/CustomTab.css">
<link href="/BSOS/css/grid.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/filter.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/site.css" type=text/css rel=stylesheet>
<link href="/BSOS/css/crm-editscreen.css" type=text/css rel=stylesheet>
<link type="text/css" rel="stylesheet" href="/BSOS/css/crm-summary.css">
<link rel="stylesheet" href="/BSOS/popupmessages/css/popup_message.css" media="screen" type="text/css">
<link href="/BSOS/css/tooltip.css" rel="stylesheet" type="text/css">

<style type="text/css">
	/* #mntcmnt2 .crmsummary-jocomp-table td{font-size:12px !important;} */
	.cdfCustTextArea {width: 362px !important;}
	.cdfCustModalbox{top: 50% !important;}
	.select2-container.select2-container-multi.required.selCdfCheckVal{width: 250px !important;}
	.dynamic-tab-pane-control .tab-page{ top:0px;}
	.disabledDiv {opacity: 0.4;pointer-events: none;}
#dateSelGridDiv .notestooltiptable td {
    background: #ffffff none repeat scroll 0 0;
    border: 1px solid #d8d8d8;
}
.notestooltip {font-size:11px !important;}
a.tooltip{position: relative;}
a.tooltip span {
    background-color: #ffffff;
    border: 1px solid #a3a3a3;
    border-radius: 3px;
    box-shadow: 0 0 5px 0 #777777;
    opacity: 1;
    white-space: normal;
    left: 10px;
}

.select2-selection,.select2-dropdown,.select2-container{
	width:250px !important;
}
#commissionRows .summaryform-formelement-commrole input[type="text"]{width:0px !important;}
#commissionRows .summaryform-formelement .managesymb{margin-top:10px !important;}
@media screen and (-ms-high-contrast: active), (-ms-high-contrast: none) {
#commissionRows .summaryform-formelement-commrole input[type="text"]{width:93px !important;}
}

/*For hiding roles input*/
.summaryform-formelement.commvalclass,.summaryform-formelement.managesymb{
	display: none !important;
}	
</style>
<script type="text/javascript" src=/BSOS/scripts/tabpane.js></script>
<script type="text/javascript">var madison='<?=PAYROLL_PROCESS_BY_MADISON;?>'</script>
<script type="text/javascript" src="/BSOS/scripts/validatecommon.js"></script>
<script language=javascript src=/BSOS/scripts/validateassignment.js></script>
<script language=javascript src="/BSOS/scripts/dynamicElementCreatefun.js"></script>
<script type="text/javascript" src="scripts/validatesupacc.js"></script>
<script type="text/javascript" src="/BSOS/scripts/validatecheck.js"></script>
<script type="text/javascript" src="scripts/validate_ajax.js"></script>
<script type="text/javascript" src='/BSOS/scripts/jquery-1.8.3.js'></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popupMsgArray.js"></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popup-message.js"></script>
<script type="text/javascript" src=/BSOS/scripts/commonact.js></script>
<script type="text/javascript" src=/BSOS/scripts/calMarginMarkuprates.js></script>

<script type="text/javascript">
 var akkupayroll = '<?php echo DEFAULT_AKKUPAY ;?>';
 var tricom_rep='<?=TRICOM_REPORTS;?>'
function searchWindow()
{
	var windowName = "Search";
	var cname = "<?php echo $_GET['addr'];?>";
	var cfrm = "<?php echo $_GET['cfrm'];?>";
	var srnum = "<?php echo $_GET['srnum'];?>";
	/* var v_width  = 550; */
	var v_width  = window.screen.availWidth * 0.55; 
	var v_heigth = 300;
	var top1=(window.screen.availHeight-v_heigth)/2;
	var left1=(window.screen.availWidth-v_width)/2;	
	var opprSno =  document.getElementById('opprSno').value;	
	var edit_acc = "<?php echo $_GET['edit_acc'];?>";
	var lochref = window.location.href;
	var indexVal = lochref.indexOf('#');
	if(edit_acc != '')	
		url = "contactsearch.php?colname=searchcont&addr="+cname+"&cfrm="+cfrm+"&srnum="+srnum+'&opprSno='+opprSno
	else
		url = "contactsearch.php?colname=searchcont&addr="+cname+"&cfrm="+cfrm+"&srnum="+srnum+'&opprSno='+opprSno+"&flagTmp=1";
	
	if (indexVal > 0) {
		if(confirm("You haven't saved your changes.\nAre you sure you want to reload this page?")){
			remote=window.open(url,windowName,"width="+v_width+"px,height="+v_heigth+"px,statusbar=no,menubar=no,scrollbars=yes,left="+left1+"px,top="+top1+"px,dependent=yes,resizable=yes");
			remote.focus();	
		}else{
			return 
		}				
	}else{	
		remote=window.open(url,windowName,"width="+v_width+"px,height="+v_heigth+"px,statusbar=no,menubar=no,scrollbars=yes,left="+left1+"px,top="+top1+"px,dependent=yes,resizable=yes");
		remote.focus();	
	}

}
function displayFilterValue(crm_cont,searchvalue){  
	
	var addr = "<?php echo $addr;?>";
	var rnd = "<?php echo $Rnd;?>";
	var form=document.markreqman;	
	doSelectAddCon();
	var companydata=form.companyinfo.value;
	var srnum=form.srnum.value;
	var statval=form.statval.value;
	
	var edit_acc = "<?php echo $_GET['edit_acc'];?>";
	if(edit_acc == '') var url = "acccontactadd.php?crm_cont="+crm_cont+"&addr="+addr+"&Rnd="+rnd+"&srnum="+srnum+"&statval="+statval+"&addTempFlag=1&companyinfo="+encodeURIComponent(companydata);
	else	var url = "acccontactadd.php?crm_cont="+crm_cont+"&addr="+addr+"&Rnd="+rnd+"&srnum="+srnum+"&statval="+statval+"&companyinfo="+encodeURIComponent(companydata);	
		
	if(crm_cont != ''){	
		
 	
		 $.ajax( {
			 url: url,
			 success:function(data){				
				var getdata = data.split('|');	
				var loc = window.location.href;
				index = loc.indexOf('#');

				if (index > 0) {
				   var getredirectloc = loc.substring(0, index);
				}
				else {
				   var getredirectloc = window.location.href;
				}
				if (getdata[1] != 1) {
					
					var parwin=window.location.href;
					if (addr == '') {						
						if (srnum == '') {							
							window.location.href = parwin.substring(0,parwin.indexOf("?"))+"?cfrm=newRepeat&newComp=yes&delCrm=1&Rnd=<?=$Rnd?>";
						}
						else
						{							
							window.location.href = parwin.substring(0,parwin.indexOf("?"))+"?cfrm=newRepeat&newComp=yes&crm_stat=add&crm_select=yes&crm_editable=yes&delCrm=1&srnum="+srnum+"&Rnd=<?=$Rnd?>";
						}
					}
					else
					{	
						window.location.href = getredirectloc;
					}
				}
				else
				{
					alert(getdata[0]);
					window.location.href = getredirectloc;					
					return false;
				}
			 }
		});
	}
}

function bindSecondaryBillingContacts()
{
	var $options = $("#bill_cons > option").clone();

	//previous selected billing contacts
	var $sec_bill_contacts = $("#billsecondarychgid").val();
	console.log($sec_bill_contacts);

	$('#billsecondarychgid').find('option').remove();
	$('#billsecondarychgid').append($options);
	
	$("#billsecondarychgid option[value='0']").remove(); // removing --Select Contact --
	//current billing contact
	var $bill_cont = $("#bill_cons").val();
	if($bill_cont !=0)
	{
		$("#billsecondarychgid option[value="+$bill_cont+"]").remove(); //removing the billing contact from secondary contacts
		$("#not_in_sec_bill_cont").val($bill_cont);
	}

	$('#billsecondarychgid').select2({
		placeholder: "Select Contacts to Notify Email"/*,
		allowClear: true,
		matcher: function(term, text) { return text.toUpperCase().indexOf(term.toUpperCase())==0; } */
	});

	$('#billsecondarychgid').select2('val', $sec_bill_contacts);
} 

function onChageForBillContact()
{
	$("#bill_cons").on("change", function(){
		console.log("Billing contact has changed...");
		setSecBillContacts();
	});	
}

function setSecBillContacts()
{
	//current billing contact
	var $bill_cont = $("#bill_cons").val();
	console.log($bill_cont);
	// if($bill_cont !=0)
	// {
		$("#billsecondarychgid option[value="+$bill_cont+"]").remove(); //removing the billing contact from secondary contacts

		//append the option which exists before changes
		var $billcontact_sno = $("#not_in_sec_bill_cont").val();
		if($billcontact_sno!=0 && $billcontact_sno!="")
		{
			var optionExists = ($("#billsecondarychgid option[value="+$billcontact_sno+"]").length>0);
			if(!optionExists)
			{
				var $opt_val = $billcontact_sno;
				var $opt_txt = $("#bill_cons option[value="+$billcontact_sno+"]").text();
				$('#billsecondarychgid').append($("<option></option>").attr("value",$opt_val).text($opt_txt)); 
			}
			
		}
		$("#not_in_sec_bill_cont").val($bill_cont);
	// }
}

function clearSecondaryBillContacts()
{
	$('#billsecondarychgid').empty();
}

function SetSelectedIndexSelect(a,b)
{
	var vv;
	var chk="yes";
	for(i=0;i<a.length;i++)
	{
		if(a.options[i].value==b)
		{
			vv=i;
			a.options[i].selected=true;
			chk="no";
			break;
		}
	}
	/*if(chk=="yes")
	{
		try { a.options[a.length]=new Option(b,b); } catch(e){}
	}*/	
}

</script>
<script type="text/javascript" src="/BSOS/scripts/common_ajax.js"></script>
<script type="text/javascript" src="/BSOS/scripts/indexoff_ie.js"></script>
<?php
if($chk_bilcont == 'frm_crm' && $chk_bilcompaddr == 'frm_crm')
{	
?>
<script type="text/javascript" src="/BSOS/scripts/crmLocations.js"></script>
<?php
}
else
{
?>
<script type="text/javascript" src="/BSOS/scripts/accLocations.js"></script>
<?php
}
?>
<?php
	if(PAYROLL_PROCESS_BY_MADISON=='MADISON' || TRICOM_REPORTS=='Y')
		echo "<script type='text/javascript' src=/BSOS/scripts/formValidation.js></script>";
?>
</head>
<!-- Checking the state selected is it other or not using country in onload functionality. -->
<body class="" <? if($newComp == 'yes' && $srnum=="" && $addr == ""){ ?> onLoad ="classToggle1(mntcmnt4,'DisplayBlock','DisplayNone',4,'acccustomer','no'),savedisp()<?php if($page11[5] == "Other^0" || $page11[5] == "^0") { echo ";onCountryChange('".$page11[7]."','OL');"; } ?>" <? } else if($newComp == 'yes' && $srnum!="" && $addr == "") { ?>onLoad ="classToggle1(mntcmnt4,'DisplayBlock','DisplayNone',4,'acccustomer','no'); classToggle(mntcmnt2,'DisplayBlock','DisplayNone',2,'billinginfo'); classToggle(mntcmnt3,'DisplayBlock','DisplayNone',3,'compculture');getCompOld_Data()<?php if($page11[5] == "Other^0" || $page11[5] == "^0") { echo ";onCountryChange('".$page11[7]."','OL');"; } ?>" <? } else if($addr != ""){?>onLoad ="getComp_Data();getCompOld_Data()<?php if($page11[5] == "Other^0" || $page11[5] == "^0") { echo ";onCountryChange('".$page11[7]."','OL');"; } ?>"<? }?>>
<form method=post name=markreqman id="markreqman">
<input type=hidden name=companyinfo id="companyinfo" value="<?php echo $companyinfo;?>">

<input type=hidden name=opprinfo id="opprinfo" value="">
<input type=hidden name=aa id="aa" value="">
<input type=hidden name=prate id="prate" value="">
<input type=hidden name=parking id="parking" value="">
<input type=hidden name=billcomp id="billcomp" value="">
<input type=hidden name=billcont value="" id="billcont">
<input type=hidden name=srnum value="<?php echo $srnum;?>" id="srnum">
<input type=hidden name=statval value="<?php echo $statval;?>" id="statval">
<input type=hidden name=parentcomp value="" id="parentcomp">
<input type="hidden" name="parent"  value="parent" id="parent"/>
<input type=hidden name=insno value="<?php echo $insno1; ?>" id="insno">
<input type=hidden name=mainuser value="<?php echo $username;?>" id="mainuser">
<input type=hidden name=owner value="" id="owner">
<input type=hidden name=addr value="<?php echo $addr;?>" id="addr">
<input type=hidden name=addr1 value="" id="addr1">
<input type=hidden name=frm value="<?php echo $frm;?>" id="frm">
<input type=hidden name=cfrm value="<?php echo $cfrm;?>" id="cfrm">
<input type=hidden name=mod value="<?php echo $mname;?>" id="mod">
<input type=hidden name=newcust value="<? echo $newcust;?>" id="newcust">
<input type=hidden name=frmcontsum value="<?php echo $frmcontsum;?>" id="frmcontsum">
<input type=hidden name=cmp_id value="<?php echo $cmp_id;?>" id="cmp_id">
<input type=hidden name=comp_sum value="<?php echo $comp_sum;?>" id="comp_sum">
<input type=hidden name=con_compid value="<?php echo $con_compid;?>" id="con_compid">
<input type=hidden name=cnt value="<?php echo $cnt;?>" id="cnt">
<input type=hidden name=emplist value="<?php echo $emplist;?>" id="emplist">
<input type=hidden name=chk_comp value="<?php echo $chk_comp;?>" id="chk_comp">
<!--used when coming from billing company in job orders-->
<input type=hidden name=DIVID value="<?=$DIVID?>" id="DIVID">
<input type=hidden name=ownerVal value="<? echo $c_owner?>" id="ownerVal">
<input type=hidden name=shareVal  value="<? echo  $c_share?>" id="shareVal">
<input type=hidden name=typecomp value="<?php echo $typecomp;?>" id="typecomp">
<input type=hidden name=contSno value="<?php echo $contSno;?>" id="contSno">
<input type=hidden name=new_par value="<?php echo $new_par;?>" id="new_par">
<input type=hidden name=new_divs value="<?php echo $new_divs;?>" id="new_divs">
<input type=hidden name=test_divs value="<?php echo $test_divs;?>" id="test_divs">
<input type=hidden name=edit_divs value="<?php echo $edit_divs;?>" id="edit_divs">
<input type=hidden name=div_addr value="<?php echo $div_addr;?>" id="div_addr">
<input type=hidden name=jocomp_par value="<?php echo $jocomp_par;?>" id="jocomp_par">
<input type=hidden name=joloc_par value="<?php echo $joloc_par;?>" id="joloc_par">

<input type=hidden name=jocomp_divs value="<?php echo $jocomp_divs;?>" id="jocomp_divs">
<input type=hidden name=Compsno value="<?php echo $Compsno;?>" id="Compsno">
<input type=hidden name=CmngFrom value="<?php echo $CmngFrom;?>" id="CmngFrom">
<input type=hidden name=joloc_divs value="<?php echo $joloc_divs;?>" id="joloc_divs">
<input type="hidden" name="crm_select" value="<?=$crm_select?>" id="crm_select">
<input type="hidden" name="Crm_compsno" value="<?=$Crm_compsno?>" id="Crm_compsno"><!-- CRM Company SNO -->
<input type="hidden" name="checkedcrm" value="<?=$checkedcrm?>" id="checkedcrm">
<input type=hidden name=Comp_Old_Data value="" id="Comp_Old_Data">
<input type=hidden name=Update_Comp value="" id="Update_Comp">
<input type=hidden name=crmnum value="<?=$crmnum;?>" id="crmnum">
<input type="hidden" name="Rnd" value="<?php echo $Rnd;?>" id="Rnd">
<input type="hidden" name="chk_bilcompaddr" value="<?=$chk_bilcompaddr;?>" id="chk_bilcompaddr">
<input type="hidden" name="chk_bilcont" value="<?=$chk_bilcont;?>" id="chk_bilcont">
<input type="hidden" name="corp_code_crm" value="<?=$corp_codeval; ?>" id="corp_code_crm">
<input type="hidden" name="corp_str" value="<? echo $corp_str; ?>" id="corp_str">
<input type="hidden" name="venfrm" value="<?=$venfrm;?>" id="venfrm">
<input type="hidden" name="edit_acc" value="<?=$edit_acc;?>" id="edit_acc">
<input type="hidden" name="delcandidate" value="" id="delcandidate">
<input type="hidden" name="delcontact_candidate" value="" id="delcontact_candidate">
<input type="hidden" name="list_cont" value="<?php echo $list_cont; ?>" id="list_cont">
<input type="hidden" name="list_cont_sno" value="<?php echo $list_cont_sno; ?>" id="list_cont_sno">
<input type="hidden" name="list_status" value="<?php echo $list_status; ?>" id="list_status">
<input type="hidden" name="taxPayable" id="taxPayable" value="<?php echo $page11[50]; ?>">
<input type="hidden" name="accReceivable"  id="accReceivable" value="<?php echo $page11[51]; ?>">
<input type="hidden" name="accPayable"  id="accPayable" value="<?php echo $page11[52]; ?>">
<input type="hidden" name="IncomeVal"  id="IncomeVal" value="<?php echo $page11[53]; ?>">
<input type="hidden" name="ExpenseVal"  id="ExpenseVal" value="<?php echo $page11[54]; ?>">
<input type="hidden" name="hdncompanyfrom" id="hdncompanyfrom" value="">
<input type="hidden" name="mode_type" id="mode_type" value= "" />
<input type='hidden' name='dynamicUdfCol'  value="<?php echo $colNames; ?> "/>
<input type=hidden name="compUdfVal" id="compUdfVal" value=''>
<input type=hidden name="newCrmCustomer" id="newCrmCustomer" value='' />
<input type='hidden' name='delTempId' id='delTempId' value= '' />
<input type='hidden' name='compstatus' id='compstatus' value= '<?php echo $compStatus; ?>' />
<input type='hidden' name='addcomptocrm' id='addcomptocrm' value= '' />
<input type=hidden name=page15 value='<?php echo $page15;?>'>
<input type=hidden name=elecount>
<input type="hidden" name="empsno" value="<?php echo $elements[51];?>">
<input type="hidden" name="roleData" id="roleData" value="<?php echo html_tls_entities($lstDirectInternal,ENT_QUOTES); ?>">
<input type="hidden" name="hdnRoleCount" id="hdnRoleCount" value=""> 
<input type="hidden" name="ACC_CUST_SESSIONRN" id="ACC_CUST_SESSIONRN" value="<?php echo $ACC_CUST_SESSIONRN;?>">


<div id="main">
<td valign=top align=center class="titleNewPad">
<table class="table customProfile">
	<div id="content">
		<?php if($addr != "" && ($page11[63] !="" || $page11[63] !="0" )){ 
			
			$cust_access_dept = $page11[63];
			$userIsAccess = $deptAccessObj->getDepartmentUserAccess($username,$cust_access_dept,"'BO'");
			if (!$userIsAccess) {
				$selDept = "SELECT deptname FROM department WHERE sno='".$cust_access_dept."'";
				$resultDept = mysql_query($selDept);
				$row = mysql_fetch_row($resultDept);
				$deptName = $row[0];
				$userAccessAlert = $deptAccessObj->displayPermissionAlertMsg($deptName);
				print '<script>alert("'.$userAccessAlert.'");window.close();</script>';
			}
		}
		?>

		<?php
		$setVendorName = $com_name=html_tls_entities(stripslashes($page11[0]));
		if($venfrm == 'yes')
			$setVendorName = "Consulting Vendor - ".$setVendorName;

		if($addr != ""){?>
	<tr>
	<td>
		<table class="table">
    		<tr>
    			<td colspan=2><font class=modcaption>&nbsp;&nbsp;<?php  echo $setVendorName;?></font></td>
    		</tr>
    		<tr>
    			<td colspan=2><font class=bstrip>&nbsp;</font></td>
    		</tr>
		</table>
	</td>
	</tr>
	<?  }
	else if($venfrm == 'yes')
	{
	?>
	<tr>
	<td>
		<table class="table">
    		<tr>
    			<td colspan=2><font class=modcaption>&nbsp;&nbsp;<?php  echo "Consulting Vendor";?></font></td>
    		</tr>
    		<tr>
    			<td colspan=2><font class=bstrip>&nbsp;</font></td>
    		</tr>
		</table>
	</td>
	</tr>
	<?
	}
	?>
	</div>

	<div id="grid_form">
	<table class="table">
	<tr>
	  <td width=100% valign=top align=left>
		<div class="tab-pane" id="tabPane1">
		<script type="text/javascript">tp1 = new WebFXTabPane( document.getElementById( "tabPane1" ) );</script>
			<div class="tab-page" id="tabPage11">
			<h2 class="tab"><? if($addr!= ""){?>Edit <? }else if($venfrm=='yes') {?>Consulting Vendor<? } else {?>Customer<? } ?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage11" ) );</script>

			<table class="table">
			<?php if($srnum=="") { ?>
			<tr id="topsave" style="display:none" class="NewGridTopBg">
			<?php
			$name=explode("|","fa fa-floppy-o~Save|fa fa-times~Close");
			$link=explode("|","javascript:validatepage1(this)|javascript:window.close()");
			$heading=$head;
			$menu->showHeadingStrip1($name,$link,$heading,"left");
			?>
			</tr>
			<tr id="top2save" style="" class="NewGridTopBg">
			<?php
			$name=explode("|","fa fa-times~Close");
			$link=explode("|","javascript:window.close()");
			$heading=$head;
			$menu->showHeadingStrip1($name,$link,$heading,"left");
			?>
			</tr>
			<?php } else { ?>
			<tr class="NewGridTopBg">
			<?php
			$name=explode("|","fa fa-clone~Update|fa fa-times~Close");
			$link=explode("|","javascript:doList(this)|javascript:doClose()");
			$heading=$head;
			$menu->showHeadingStrip1($name,$link,$heading,"left");
			?>
			</tr>
			<?php } ?>
			</table>
	<div class="form-container">
		
		<div class="acc-settings-back">
		<? if($addr == ""){?>
    		<table class="crmsummary-edit-tablemin" id="acccustomer">
	        <tr>
				<td>
				<span class="d-inline-block mx-3" id="leftflt"><span class="crmsummary-content-title">Do you want to: &nbsp;&nbsp;</span><a class="acc-select-link" href="javascript:parent_popup('<?=$venfrm;?>')"><strong>Select</strong> an <strong>existing<?php if($venfrm != "yes") echo " CRM ";?></strong> Company</a>&nbsp;<a href="javascript:parent_popup('<?=$venfrm;?>')"><i class="fa fa-search"></i></a><font size="2"> |  <span id="hideExp4">+</span></font> <span id="acc1customer"><a class="acc-select-link" onClick="classToggle1(mntcmnt4,'DisplayBlock','DisplayNone',4,'acccustomer','no'),savedisp()" href="#hideExp4"><strong>Create a new</strong> <?php if($venfrm=="yes") { echo "Consulting Vendor"; } else { echo "Accounting Customer"; } ?></a></span></span>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			</table>
			<? } 
			else
			{ ?>
    		<table width="100%" cellpadding="1" cellspacing="1" id="acccustomer">
	        <tr>
				<td>
				<span id="leftflt"><span id="hideExp4"></span><span id="acc1customer"></span></span>
				</td>
			</tr>
			</table>
			<? } ?>
			</div>
	<?php
 		if(strtolower($display)=="yes")
			print "<center><font class=afontstyle4>&nbsp;Customer has been updated successfully.</font></center>";
	?>
	<div <?php if($addr=="") { ?> class="DisplayNone" <?php } ?>  id="mntcmnt4">
		<table>
			<tr>
			  	<td width=15%  class="summaryrow" colspan='2'>
					<?php
						$mod = 6;
						include($app_inc_path."custom/getcustomfields.php");
					?>
				</td>
		  	</tr>
		</table>
	<fieldset>
		<legend><font class="afontstyle">Customer Information&nbsp;&nbsp;</font></legend>

		<div class="form-back">
		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="crmsummary-edit-table">
		<?
		 /**sanghamitra: need to show the add to crm checkbox ,before for vendors company records were not storing in crm **/
			if($addr == "")
			{
			?>
			 <? if($srnum == "") {?>
				<tr>
					<td colspan="4">
					<span id="leftflt"><input type="checkbox" name="addcompanytocrm" id="addcompanytocrm" value="Y" onClick="chk_crmfun()" <? if($checkedcrm == "Y"){?>checked<? }?>> <font class="acc1-select-link">Add Customer to CRM</font></span>
					</td>
				</tr>
			<?  } ?>
			<? }
			 else
			{
			?>
			 <? if($crmnum == 0) {?>
				<tr>
					<td colspan="4">
					<span id="leftflt"><input type="checkbox" name="addcompanytocrm" id="addcompanytocrm" value="Y" onClick="chk_crmfun()" <? if($addcomptocrm == "Y" && $venfrm == 'yes'){?>checked<? }?>> <font class="acc1-select-link">Add Customer to CRM</font></span>
					</td>
				</tr>
			<? }?>
		<? 	} ?>
			<tr>
				<td colspan="4">
					<div class="d-flex align-items-center gap-3">
						<span id="leftflt">
							<div class="crmsummary-content-title mb-2"> Customer Name<?=$mandatory_madison;?> </div>
							<input class="summaryform-formelement form-control w-250" type=text id="cname" name="cname" size=54 maxsize=100 maxlength=100 value="<?php echo html_tls_entities(stripslashes($page11[0]),ENT_QUOTES); ?>" setName='Customer name' <?php echo $spl_Attribute?>>
						</span>
						<span id="leftflt">
							<div class="crmsummary-content-title mb-2">Website</div>
							<input class="summaryform-formelement form-control w-250" type=text name="curl" id="curl" size=53 maxsize=100 maxlength=100 value="<?php echo html_tls_entities(stripslashes($page11[1]),ENT_QUOTES); ?>" >
						</span>
					</div>
				</td>
			</tr>
			<tr>
				<td colspan="4">
				<div class="d-flex align-items-center gap-3">
					<span id="leftflt"><div class="crmsummary-content-title mb-2"><span class="crmsummary-content-title">Address 1<?=$mandatory_madison;?></span><span class="summaryform-nonboldsub-title">&nbsp;(main)&nbsp;&nbsp;</span><?php if($frmcontsum=="true"){?><span id="sameascont"><a class="edit-list" href="javascript:popContactDetails()">same as contact</a></span><?}?></div>
					<input class="summaryform-formelement form-control w-250" type=text name="address1" id="address1" size=54 maxsize=100 maxlength=100 value="<?php echo html_tls_entities(stripslashes($page11[2]),ENT_QUOTES); ?>" setName='Address 1' <?php echo $spl_Attribute;?>></span>
					<span id="leftflt"><div class="crmsummary-content-title mb-2">Address 2</div>
					<input class="summaryform-formelement form-control w-250" type=text name="address2" id="address2" size=53 maxsize=100 maxlength=100 value="<?php  echo html_tls_entities(stripslashes($page11[3]),ENT_QUOTES); ?>"></span>
				</div>
				</td>
			</tr>
			<tr>
				<td colspan="4">
				<div class="d-flex align-items-center gap-3">
                                    <span id="leftflt"><div class="crmsummary-content-title mb-2">City&nbsp;<?php if($mandatory_madison!=''){ echo $mandatory_madison; } else{ echo  $mandatory_akkupay ;} ?></div>
				<input class="summaryform-formelement form-control w-250" type=text name="city" id="city" size=25 maxlength=50 value="<?php echo html_tls_entities(stripslashes($page11[4]),ENT_QUOTES); ?>" setName='city' <?php echo $spl_Attribute;?>></span>
				<span id="leftflt"><div class="crmsummary-content-title mb-2">State&nbsp;<?php if($mandatory_madison!=''){ echo $mandatory_madison; } else{ echo  $mandatory_akkupay ;} ?></div>
				<!-- Displaying state as a droplist using getStateAbbr function from functions.php page -->
				<?php
					echo getStateAbbr(stripslashes($page11[5]),'w-250 summaryform-formelement','state',stripslashes($page11[47]));
				?>
				</span>
				
				<span id="leftflt"><div class="crmsummary-content-title mb-2">Zip&nbsp;<?php if($mandatory_madison!=''){ echo $mandatory_madison; } else{ echo  $mandatory_akkupay ;} ?></div>
				<input class="summaryform-formelement form-control w-250" type=text name="zip" id="zip" size=10 maxlength="<?php echo (PAYROLL_PROCESS_BY_MADISON=='MADISON')?'5':'20'?>" value="<?php echo html_tls_entities(stripslashes($page11[6]),ENT_QUOTES); ?>" setName='Zip' <?php echo (PAYROLL_PROCESS_BY_MADISON=='MADISON') ? "udCheckPhNum='YES' ".$spl_Attribute :'';?>></span>
				<span id="leftflt"><div class="crmsummary-content-title mb-2">Country</div>
				<select class="summaryform-formelement form-select w-250" name="country" id="country" onChange="onCountryChange(this.value,'OC')">
				<option selected value=0>--select--</option>
				<?php
					 echo getCountryNames($page11[7]); 
				 ?>
				</select>
				</span>
				</div>
				</td>
			</tr>
			<tr>
				<td width="100"  class="crmsummary-content-title"><div class="space_15px">&nbsp;</div>Customer ID#</td>
				<td ><div class="space_15px">&nbsp;</div><font class="summaryform-formelement"><?php  if($page11[36]!='' && $page11[36]!='0') echo html_tls_entities(stripslashes($fth_query[0]),ENT_QUOTES); ?></font><input type=hidden name="compcustid" id="compcustid" value='<? echo html_tls_entities(stripslashes($fth_query[0]),ENT_QUOTES); ?>'>&nbsp;&nbsp;&nbsp;</td>
				<td class="crmsummary-content-title"><div class="space_15px">&nbsp;</div>Customer Revenue</td>
				<td><div class="space_15px">&nbsp;</div><input class="summaryform-formelement form-control w-250" type=text name="com_revenue" id="com_revenue" size=32 maxlength=50 value="<?php  echo html_tls_entities(stripslashes($page11[9]),ENT_QUOTES); ?>" >&nbsp;&nbsp;&nbsp;</td>
			</tr>
			<tr>
				<td width="100"><div class="crmsummary-content-title">Main Phone<?=$mandatory_madison;?></div><?php if(PAYROLL_PROCESS_BY_MADISON=='MADISON') { ?><span class="summaryform-nonboldsub-title">ex: (xxx) xxx-xxxx</span><?php } ?></td>
				<td>
				<input class="summaryform-formelement form-control w-250" type=text name="phone" id="phone" size=17 maxlength=30 value="<?php echo html_tls_entities(stripslashes($page11[8]),ENT_QUOTES); ?>" setName='phone' <?php echo (PAYROLL_PROCESS_BY_MADISON=='MADISON') ? "udCheckPhNum='YES' ".$spl_Attribute :'';?>>
				<span class="crmsummary-content-title">ext.&nbsp;</span>
				<input class="summaryform-formelement form-control w-auto" size=8 maxlength="<?php echo (PAYROLL_PROCESS_BY_MADISON=='MADISON')?'4':'16'?>" type="text" name="phone_extn" id="phone_extn" value="<?=html_tls_entities(stripslashes($page11[38]),ENT_QUOTES);?>"></td>				
				<td valign="middle" class="crmsummary-content-title">No. Employees</td>
				<td><input class="summaryform-formelement form-control w-250" type=text name="nemp" id="nemp" size=32 maxlength=50 value="<?php echo html_tls_entities(stripslashes($page11[11]),ENT_QUOTES); ?>">&nbsp;&nbsp;&nbsp;</td>
			</tr>
			<tr>
				<td width="100"><div class="crmsummary-content-title">Fax Number</div><?php if(PAYROLL_PROCESS_BY_MADISON=='MADISON') { ?><span class="summaryform-nonboldsub-title">ex: (xxx) xxx-xxxx</span><?php } ?></td>
				<td><input class="summaryform-formelement form-control w-250" type=text name="fax" id="fax" size=32 maxlength=50 value="<?php echo html_tls_entities(stripslashes($page11[10]),ENT_QUOTES);?>" setName='Fax'>&nbsp;&nbsp;&nbsp;</td>
				<td class="crmsummary-content-title">No.Locations</td>
				<td><input class="summaryform-formelement form-control w-250" type=text name="nloc" id="nloc" size=32 maxlength=50 value="<?php echo html_tls_entities(stripslashes($page11[13]),ENT_QUOTES); ?>">&nbsp;&nbsp;&nbsp;</td>
			</tr>
			<tr>
				<td class="crmsummary-content-title">Industry</td>
				<td><input class="summaryform-formelement form-control w-250" type=text name="industry" id="industry" size=32 maxsize=50 maxlength=255 value="<?php  echo html_tls_entities(stripslashes($page11[12]),ENT_QUOTES); ?>">&nbsp;&nbsp;&nbsp;</td>
				<td class="crmsummary-content-title">Customer Source</td>
				<td>
					<nobr>
    				<select name="compsource" id="compsource" class="summaryform-formelement form-select w-250" >
					<option value=0>--select--</option>
    				<?php
					     $CSrc_Sql="select sno,name from manage where type='compsource' order by name";
						 $CSrc_Res=mysql_query($CSrc_Sql,$db);
						 while($CSrc_Data=mysql_fetch_row($CSrc_Res))
						 {
							echo "<option value='$CSrc_Data[0]'".sele($page11[15],$CSrc_Data[0]).">".html_tls_specialchars($CSrc_Data[1],ENT_QUOTES)."</option>"; 
						 }?>
    				</select>
    				<a href="javascript:doManage('Company Source','compsource');" class="edit-list">edit&nbsp;list</a>
					</nobr>
				</td>
			</tr>
			<tr>			
				<td class="crmsummary-content-title">Year Founded</td>
				<td><input class="summaryform-formelement form-control w-250" type=text name="nyb" id="nyb" size=32 maxsize=50 maxlength=50 value="<?php echo html_tls_entities(stripslashes($page11[14]),ENT_QUOTES);?>" >&nbsp;&nbsp;&nbsp;</td>                
    				<td class="crmsummary-content-title">SIC Code</td>
				<td><input class="summaryform-formelement form-control w-250" type=text name="siccode" id="siccode" size=32 maxsize=50 maxlength=50 value="<?php echo html_tls_entities(stripslashes($page11[17]),ENT_QUOTES); ?>">&nbsp;&nbsp;&nbsp;</td>
			</tr>
			<tr>
				<td class="crmsummary-content-title">Customer Type</td>
				<td>
    				<select class="summaryform-formelement form-select w-250" name="ctype" id="ctype">
					<option value=0>--select--</option>
					<?php
					     $Ctype_Sql="select sno,name from manage where type='comptype' order by name";
						 $Ctype_Res=mysql_query($Ctype_Sql,$db);
						 while($Ctype_Data=mysql_fetch_row($Ctype_Res))
						 {
							 echo "<option value='$Ctype_Data[0]'".sele($Ctype_Data[0],$page11[16]).">".html_tls_specialchars($Ctype_Data[1],ENT_QUOTES)."</option>"; 
						 }?>
    				</select>
    				<a href="javascript:doManage('Company Type','ctype');" class="edit-list">edit list</a></td>
				<td class="crmsummary-content-title">Federal ID</td>
				<td><input class="summaryform-formelement form-control w-250" type=text name="federalid" id="federalid" size=32 maxlength=50 value="<?php  echo html_tls_entities(stripslashes($page11[19]),ENT_QUOTES); ?>" setName='federalid'>&nbsp;&nbsp;&nbsp;</td>
			</tr>
			<tr>
				<td class="crmsummary-content-title">Customer Ownership</td>
				<td><input class="summaryform-formelement form-control w-250" type=text name="cownership" id="cownership" size=32 maxsize=50 maxlength=50 value="<?php  echo html_tls_entities(stripslashes($page11[18]),ENT_QUOTES); ?>" >&nbsp;&nbsp;&nbsp;</td>				
				<td valign="middle" class="crmsummary-content-title">Customer Size</td>
				<td><input class="summaryform-formelement form-control w-250" type=text name="csize" id="csize" size=32 maxsize=50 maxlength=50 value="<?php  echo stripslashes($page11[21]); ?>">&nbsp;&nbsp;&nbsp;</td>				
			</tr>
			<tr>
				<td class="crmsummary-content-title">Ticker Symbol</td>
				<td><input class="summaryform-formelement form-control w-250" type=text name="ticker" id="ticker" size=32 maxsize=50 maxlength=50 value="<?php  echo html_tls_entities(stripslashes($page11[20]),ENT_QUOTES); ?>" >&nbsp;&nbsp;&nbsp;</td>
				<td class="crmsummary-content-title">Alternative ID#</td>
				<td><input class="summaryform-formelement form-control w-250" type=text name="comalternateid" id="comalternateid" size=32 maxsize=255 maxlength=255 value="<?php  echo html_tls_entities(stripslashes($page11['37']),ENT_QUOTES); ?>" >&nbsp;&nbsp;&nbsp;</td>
		
			</tr>
			<tr>				
				<td class="crmsummary-content-title">CORP CODE</td>
				<td>
				<select name="corp_code" id="corp_code" class="summaryform-formelement form-select w-250" style="overflow:auto;<? echo $wid_val; ?>">
					<option value=0>--select--</option>
    				<?php
					     $Crpcode_Sql="select sno,name,description from corp_code order by name";
						 $Crpcode_Res=mysql_query($Crpcode_Sql,$db);
						 while($Crpcode_Data=mysql_fetch_row($Crpcode_Res))
						 {						   	
						   $Corpcode_txt=$Crpcode_Data[1];
						   if($Crpcode_Data[2]!='')
						      $Corpcode_txt.= " - ".$Crpcode_Data[2];
							echo "<option value='$Crpcode_Data[0]'".sele($page11[39],$Crpcode_Data[0])." title='".html_tls_specialchars($Corpcode_txt,ENT_QUOTES)."'>".html_tls_specialchars($Corpcode_txt,ENT_QUOTES)."</option>"; 
						 }?></select>&nbsp;<a href="javascript:open_corpwin()" class="edit-list">edit list</a>&nbsp;</td>
						<td class="crmsummary-content-title">Department<?=$mandatory_madison;?></td>
						<td><input class="summaryform-formelement form-control w-250" type=text name="departmentname" id="departmentname" size=32 maxsize=255 maxlength=255 value="<?php  echo html_tls_entities(stripslashes($page11['44']),ENT_QUOTES); ?>" setName='Department' <?php echo $spl_Attribute;?>>&nbsp;&nbsp;&nbsp;</td>
			</tr>
			<?php
			/*checking the condition for customer and vendor */
			if($venfrm!="yes")
			{
				$displaymadison="Madison&nbsp;ID";
				$inputtype="text";
			}
			else
			{
				$displaymadison="";
				$inputtype="hidden";
			}
			?>
			<tr>				
					 <input class="summaryform-formelement" type="hidden" name="addressdesc" id="addressdesc" size=32 maxsize=255 maxlength=255 value="<?php  echo html_tls_entities(stripslashes($page11['45']),ENT_QUOTES); ?>">
			<?php if(PAYROLL_PROCESS_BY_MADISON=='MADISON') { ?>
					  	<td class="crmsummary-content-title"><?=$displaymadison;?></td>
					  <td><input class="summaryform-formelement" type=<?=$inputtype;?> name="madisonid" id="madisonid" size=32 maxsize=255 maxlength=255 value="<?php  if($page11['48']!=0) { echo html_tls_entities(stripslashes($page11['48']),ENT_QUOTES); } ?>" >&nbsp;&nbsp;&nbsp;</td>
			<?php } else { ?>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>				
			<?php } ?>						  
			</tr>
			<?php if(PAYROLL_PROCESS_BY_MADISON=='MADISON') { ?>			
			<tr style="display:none";>				
					  <td class="crmsummary-content-title">Madison Addr ID</td>
					  <td><input class="summaryform-formelement" type=text name="madisonaddrid" id="madisonaddrid" size=32 maxsize=255 maxlength=255 value="" >&nbsp;&nbsp;&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>					  
			</tr>
			<?php } ?>	
			 <tr>
                    <td colspan="5"><span id="leftflt"><span class="summaryform-bold-title">Roles:</span></span></td>
            </tr>
        <tr>
        <td colspan="5">
            <table border="0" width=100%>
                <thead>
			<tr>
				<input type="hidden" name="empvalues" id="empvalues">
				<td width="148" class="summaryrow" style="border-bottom: 0px solid #ddd;text-align: left;">Add Person:</td>
				<td style="border-bottom: 0px solid #ddd; padding-left:0px; text-align:left !important" colspan="3">
					<span id="leftflt">
						<select name="addemp" class="summaryform-formelement setcommrolesize" onChange="addCommission('newrow')">
							<option selected  value="">--select employee--</option>
							<?php
								while($row=mysql_fetch_row($res))
									print '<option '.compose_sel($elements[13],$row[0]).' value="'.'emp'.$row[0].'|'.$row[1].'">'.stripslashes($row[1]).'</option>';
							?>
						</select>
					</span>
					<!-- <span class="summaryform-formelement">&nbsp;|&nbsp;</span>
					<a class="crm-select-link" href="javascript:contact_popup1('comcontact')"><strong>select</strong> contact</a>
					&nbsp;<a href="javascript:contact_popup1('comcontact')">
						<i alt='Search' class='fa fa-search'></i>
					</a>
					<span class="summaryform-formelement">&nbsp;|&nbsp;</span>
					<a class="crm-select-link" href="javascript:newScreen('contact','comcontact')"><strong>new</strong> contact</a> -->
				</td>
			</tr>
                </thead>
                <tbody id="commissionRows">
                <tr>
                        <?php
                        /*  Select the Roles For this customer  */
                        $sel_qury = "select sno from staffacc_cinfo where username= '".$addr."'";
						$sel_res = mysql_query($sel_qury,$db); 
						$sel_row = mysql_fetch_row($sel_res);
						$empnos = '';
                        $quec = "SELECT person, IF(co_type!='' AND comm_calc!='',FORMAT(amount,2),'') AS amount, co_type, comm_calc, type, roleid, overwrite, enableUserInput 
                        	FROM assign_commission WHERE assignid='".$sel_row[0]."' 
                        	AND assigntype='ACC' ORDER BY sno DESC";
						$cresc		= mysql_query($quec,$db);
						$numRowscomm	= mysql_num_rows($cresc);
						$p = 0;
						while($crowc = mysql_fetch_row($cresc)) {
							
							if($crowc[4] == "E")
								$comSnos	= "emp".$crowc[0];
							else
								$comSnos	= $crowc[0];

							$empno = $comSnos;
							$comVals	= $crowc[1];
							$comRate	= $crowc[2];
							$comFee		= $crowc[3];
							$roleval	= $crowc[5];
							$comOverwrite	= $crowc[6];
							$comEUserInput	= $crowc[7];
							$counter	= 1;
							
							$tmpempId	= str_replace('emp','',$empno);
								

							if(substr($empno,0,3)=='emp') {
								$emp_val	= explode("emp",$empno);
			    
								if($crowc[4] != 'A') {
									$sel_emp	= "SELECT name FROM emp_list WHERE username='".$emp_val[1]."'";
									$res_emp	= mysql_query($sel_emp,$db);
									$fetch_emp	= mysql_fetch_row($res_emp);
									$commName	= stripslashes($fetch_emp[0]);
								}
								else {
									$sel_acc	= "SELECT CONCAT_WS('',staffacc_contact.fname,'',staffacc_contact.lname,IF(staffacc_cinfo.cname!='',concat('(',staffacc_cinfo.cname,')'),'')) FROM staffacc_contact LEFT JOIN staffacc_cinfo ON staffacc_contact.username = staffacc_cinfo.username AND staffacc_cinfo.type IN ('CUST','BOTH') WHERE staffacc_contact.sno='".$emp_val[1]."' and staffacc_contact.acccontact='Y' and staffacc_contact.username!=''";
									$res_acc	= mysql_query($sel_acc,$db);
									$fetch_acc	= mysql_fetch_row($res_acc);
									$commName	= stripslashes($fetch_acc[0]);
								}
							}
							else {
								$sel_acc	= "SELECT CONCAT_WS('',staffacc_contact.fname,'',staffacc_contact.lname) FROM staffacc_contact LEFT JOIN staffacc_cinfo ON staffacc_contact.username = staffacc_cinfo.username AND staffacc_cinfo.type IN ('CUST','BOTH') WHERE staffacc_contact.sno='".$empno."' and staffacc_contact.acccontact='Y' and staffacc_contact.username!=''";
								$res_acc	= mysql_query($sel_acc,$db);
								$fetch_acc	= mysql_fetch_row($res_acc);
								$commName	= stripslashes($fetch_acc[0]);
							}

							$comm_roletitle = '';

							/*if(!in_array($roleval,$rolesSelectIds))
							{*/
							$role_sel	= "SELECT roletitle  FROM company_commission WHERE sno =".$roleval;
							$role_sel_res	= mysql_query($role_sel,$db);
							$role_fetch	= mysql_fetch_row($role_sel_res);

							if($role_fetch[0] !='')
								$comm_roletitle	= $role_fetch[0];
							//}

							$rs		= "SELECT enable_details FROM company_commission WHERE sno =".$roleval;
							$res		= mysql_query($rs,$db);
							$res_result	= mysql_fetch_row($res);
							$comm_enable_details	= $res_result[0];

							if($empno != '') {
							?>
								<script>
									var empsno	= "<?php echo $empno;?>";
									var emptext	= "<?php echo $emptxt;?>";

									var ratetxt	= "<?php echo $rateval;?>";
									var paytxt	= "<?php echo $payval;?>";
									var commname	= "<?php echo $commName;?>";
									var roletxt	= "<?php echo $roleval;?>";
									var overwritetxt= "<?php echo $overwriteval;?>";
									var euserinput	= "<?php echo $eUserInput;?>";

									if(empsno != "noval") {
										addRow(commname+"|akkenSplit|"+emptext+"|akkenSplit|"+ratetxt+"|akkenSplit|"+paytxt+"|akkenSplit|"+roletxt+"|akkenSplit|"+overwritetxt+"|akkenSplit|"+euserinput,empsno,'edit');
										var rnval	= eval("document.forms[0].roleName"+'<?php echo $p;?>');
										var rval	= eval("document.forms[0].ratetype"+'<?php echo $p;?>');
										var pval	= eval("document.forms[0].paytype"+'<?php echo $p;?>');

										if('<?php echo $comm_roletitle;?>' != '')
										{
											var oOption	= document.createElement("option");
											oOption.appendChild(document.createTextNode('<?php echo $comm_roletitle;?>'));
											oOption.setAttribute("value", roletxt);
											rnval.appendChild(oOption);
										}
										
										SetSelectedIndexSelect(rnval,roletxt);
									}

									/*if(euserinput == 'N') {
										document.getElementById("commval"+'<?php echo $p;?>').disabled	= true;
									}

									if('<?php echo $comm_enable_details;?>' == 'N') {
										document.getElementById("commval"+'<?php echo $p;?>').style.visibility	= 'hidden';
										document.getElementById("perflat_"+'<?php echo $p;?>').style.visibility	= 'hidden';
									}*/
								</script>
								<?php
								}
								$p = $p+1;
							}
                        ?>
                </tr>
                 <tr>
                    <td colspan="5"></td>
            </tr>					  
			<tr>
				<td valign="top"><div class="space_15px">&nbsp;</div><div class="crmsummary-content-title">Customer Brief</div><span class="summaryform-nonboldsub-title">(internal notes)</span></td>
				<td colspan="3"><div class="space_15px">&nbsp;</div><textarea class="form-control" name="cbrief" id="cbrief" rows="2" cols="68"><?php  echo html_tls_entities(stripslashes($page11[23]),ENT_QUOTES); ?></textarea></td>

			</tr>
			<tr>
				<td valign="top"><div class="crmsummary-content-title">Customer Summary</div><span class="summaryform-nonboldsub-title">(for job orders)</span></td>
				<td colspan="3"><textarea name="csummary" id="csummary" class="form-control" rows="2" cols="68" ><?php  echo html_tls_entities(stripslashes($page11[24]),ENT_QUOTES); ?></textarea></td>
			</tr>
			<tr>
				<td valign="top"><div class="crmsummary-content-title">Search Tags</div><span class="summaryform-nonboldsub-title">(search keywords)</span></td>
				<td colspan="3"><textarea name="stags" class="form-control" id="stags" rows="2" cols="68"><?php  echo html_tls_entities(stripslashes($page11[25]),ENT_QUOTES); ?></textarea></td>
			</tr>
			
			<tr>
				<td valign="top">Ultigigs Timesheet Layout</td>
				<td colspan="3">
					<select name="customer_timesheet_layout_preference" id="customer_timesheet_layout_preference">
						<option value="" <?php if($db_cust_timesheet_layout_preference == "") { echo "selected"; } ?>>--- Select Template ---</option>
						<option value="Regular" <?php if($db_cust_timesheet_layout_preference == "Regular") { echo "selected"; } ?>>Regular</option>
						<option value="TimeInTimeOut" <?php if($db_cust_timesheet_layout_preference == "TimeInTimeOut") { echo "selected"; } ?>>Time In &amp; Time Out</option>
						<option value="Clockinout" <?php if($db_cust_timesheet_layout_preference == "Clockinout") { echo "selected"; } ?>>Clock In &amp; Out</option>
					</select>
				</td>
			</tr>
			
			<?php
			if($srnum!="") 
			{ 
				//Users Query
				$users_que="SELECT username,name FROM users";
				$res_users=mysql_query($users_que,$db);
				$Array_Users=array();
				while($fetch_data=mysql_fetch_row($res_users))
				{
					$Array_Users[$fetch_data[0]]=$fetch_data[1];
				}
				
				$Notes_Sql="select notes,DATE_FORMAT(CONVERT_TZ(cdate,'SYSTEM','".$user_timezone[1]."'),'%c/%e/%Y %l:%i%p'),cuser,type,contactid,notes_subtype from notes where contactid='382' and type='com'";

				$Notes_Res=mysql_query($Notes_Sql,$db);
				$Notes_Text="";
				while($Notes_Data=mysql_fetch_row($Notes_Res))
				{
			
							// this is to display the subtype name 
							$subType='';
							$rowMng=mysql_fetch_row(mysql_query("SELECT name FROM manage where type='notes' and sno='".$Notes_Data[6]."'",$db));
						   if($rowMng[0]!='')
							$subType=" | ".$rowMng[0];
			
						$Notes_Text.=strtolower($Notes_Data[1]).$subType." - ".html_tls_specialchars($Array_Users[$Notes_Data[2]],ENT_QUOTES)."\n".nl2br(str_replace('&nbsp;',' ',html_tls_specialchars($Notes_Data[0],ENT_QUOTES)))."\n\n";
				}
			} 
			?>
			
			<tr>
			</table>
			</div>
			
			<div class="accordion accordion-style-1">
				<div class="accordion-item">
					<h2 class="accordion-header" id="BillingInformation">
						<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBillingInformation" aria-expanded="true" aria-controls="collapseBillingInformation">
							Billing Information
						</button>
					</h2>
					<div id="collapseBillingInformation" class="accordion-collapse collapse show" aria-labelledby="BillingInformation">
						<div class="accordion-body">
							<table class="table">
							<tr>
								<td width="164" valign="center" class="summaryform-bold-title">HRM Department</td>
								<td valign="left" colspan="2">
									<?php
									 //if new customer is created using "Create a new Accounting customer link" then auto populate the HRM department which the user belongs to

									$autopopulate_hrm_dept = (isset($crm_select) && $crm_select == 'yes' )?0:(($page11[63] !='' && $page11[63] >0)?0:1); 

										$onchange = "onChange=\"displayAccountTAXARAP('onChange','CUST','-1','<?php echo $srnum ?>','custVenClass')\"";
										//departmentSelBox('accBillDepartment', 1, '', $owner='', 'Customer', $onchange, '');

										// Added Department Access Permission.
										$deptAccessObj = new departmentAccess();
										$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");

										if($autopopulate_hrm_dept == 1){								

										 $sel_department2 = "SELECT sno FROM department WHERE status = 'Active' AND department.sno !='0' AND department.sno IN (".$deptAccesSno.") limit 1"; 
										 $res_department2 =  mysql_query($sel_department2,$db);
										 $fetch_department2 =  mysql_fetch_row($res_department2);
										 $user_department_permission = $fetch_department2[0];
										} 

										$departments_list = "SELECT sno,deptname,IF((status = 'Active'),'Active','Deleted') FROM department WHERE status = 'Active' AND department.sno !='0' AND department.sno IN (".$deptAccesSno.")"; 							



									   // $sel_user_roles_department = "SELECT sno,deptname,IF((status = 'Active'),'Active','Deleted') FROM department WHERE sno ='".$page11[63]."' OR (status = 'Active')"


										$res_department =  mysql_query($departments_list,$db);
										$dept_selBox_opt="";
										if(mysql_num_rows($res_department ) > 0)
										{


											while($fetch_department =  mysql_fetch_row($res_department))
											{

												if($autopopulate_hrm_dept == 1){ //only for Create a new Accounting customer link

													$selected = ($fetch_department[0] == $user_department_permission) ? 'selected' : '';
												}else{ 
													$selected = ($fetch_department[0] == $page11[63] ) ? 'selected' : '';
												} 

												$dept_selBox_opt.='<option value="'.$fetch_department[0].'" title="'.html_tls_specialchars($fetch_department[1],ENT_QUOTES).'" '.$selected.'>'.html_tls_specialchars($fetch_department[1],ENT_QUOTES).'</option>';
											}
										}
										else
										{
											$dept_selBox_opt.='<option value="0">No Departments available</option>';
										}

										$dept_selBox = '<select name="accBillDepartment" class="form-select w-250" id="accBillDepartment"  '.$onchange.'>';

										$dept_selBox_opt.='</select>';

										echo $dept_selBox.$dept_selBox_opt;
									?>
								</td>
							</tr>
							<?php
							if ($venfrm != 'yes')
							{
							?>
							<tr>
								<td width="164" valign="center" class="summaryform-bold-title">Attention</td>
								<td valign="left" colspan="2">					
									<input class="summaryform-formelement form-control w-250" type="text" name="attention" id="attention" size="32" maxsize="50" maxlength="50" value="<?php echo html_tls_specialchars($page11[65],ENT_QUOTES); ?>" > 
								</td>
							</tr>
							<?php
							}
							else
							{
							?>
								<input type="hidden" name="attention" id="attention" value="">
							<?php	
							}				
							?>



							<tr style="display:none">
								<td width="164" valign="center" class="summaryform-bold-title">Location</td>
								<td valign="left">
									<select name="accBillLocation" class="form-select w-250" id="accBillLocation" onChange="displayAccountTAXARAP('onChange','CUST','-1','<?php echo $srnum ?>','custVenClass')">
									<?php
										if($page11[49] == "")
											$page11[49]=$loc_user;
										 $Loccode_Sql="SELECT serial_no,heading,loccode FROM contact_manage WHERE status!='BP' order by heading";
										 $Loccode_Res=mysql_query($Loccode_Sql,$db);
										 while($Loccode_Data=mysql_fetch_row($Loccode_Res))
										 {						   	
										   if($Loccode_Data[2]!='')
											  $Loccode_txt= $Loccode_Data[2]." - ";

											$Loccode_txt.=$Loccode_Data[1];

											echo "<option value='$Loccode_Data[0]'".sele($page11[49],$Loccode_Data[0])." title='".html_tls_specialchars($Loccode_txt,ENT_QUOTES)."'>".html_tls_specialchars($Loccode_txt,ENT_QUOTES)."</option>"; 
										 }
									 ?>
									</select>
								</td>
								<td align="left" valign="top" style="width:355px">
									<a href="javascript:doManageLocations('accBillLocation','location');" class="edit-list">edit list</a>
								</td>
							</tr>
							<tr style="display:none">
								<td width="164" valign="center" class="summaryform-bold-title">Class</td>
								<td valign="left" colspan="2">
									<?php 
										$getClassFunc = getClassesSetups();

										echo clsSelBoxRtn($getClassFunc, "custVenClass", "", "", "style=\"width:210px;\"");
									?>
								</td>
							</tr>

							<?
								$billcontact=$page11[34];
								$bill_loc=$page11[35];

								if($billcontact!=0)
								{       
															if($chk_bilcont == 'frm_crm' && $chk_bilcompaddr == 'frm_crm')
									{

										$que2="SELECT CONCAT_WS( ' ', staffoppr_contact.fname, staffoppr_contact.mname, staffoppr_contact.lname ),staffoppr_contact.csno,'' FROM staffoppr_contact WHERE staffoppr_contact.sno ='".$billcontact."' AND staffoppr_contact.crmcontact='Y'";
																	$res2=mysql_query($que2,$db);
																	$row2=mysql_fetch_row($res2);
																	$billcompany=$row2[1];
																	$billcont=$row2[0];
																	$billcompany=$row2[1];
																	$billcont_stat=$row2[2];

																	$que2="SELECT temp_staffoppr_contact.csno FROM temp_staffoppr_contact WHERE temp_staffoppr_contact.csno ='".$billcompany."'";
																	$res2=mysql_query($que2,$db);
																	if(mysql_num_rows($res2)>0){

																	$que2="SELECT CONCAT_WS( ' ', temp_staffoppr_contact.fname, temp_staffoppr_contact.mname, temp_staffoppr_contact.lname ),temp_staffoppr_contact.csno,'' FROM temp_staffoppr_contact WHERE temp_staffoppr_contact.sno ='".$billcontact."' AND temp_staffoppr_contact.crmcontact='Y'";
																	$res2=mysql_query($que2,$db);
																	if(mysql_num_rows($res2)>0){
																		$row2=mysql_fetch_row($res2);
																		$billcont=$row2[0];
																		$billcompany=$row2[1];
																		$billcont_stat=$row2[2];
																	}else{
																		$que2="SELECT CONCAT_WS( ' ', staffoppr_contact.fname, staffoppr_contact.mname, staffoppr_contact.lname ),staffoppr_contact.csno,'' FROM staffoppr_contact WHERE staffoppr_contact.sno ='".$billcontact."' AND staffoppr_contact.crmcontact='Y'";
																		$res2=mysql_query($que2,$db);
																		$row2=mysql_fetch_row($res2);
																		$billcompany=$row2[1];

																		if($bill_loc>0){
																			$que2="select csno from staffoppr_location where sno='".$bill_loc."' AND csno= '".$billcontact."' AND ltype='con' AND status='A'";
																			$res2=mysql_query($que2,$db);
																			if(mysql_num_rows($res2)>0){
																				$bill_loc=0;
																			}
																		}
																		$billcont= '';
																		$billcont_stat='';
																		$billcontact=0;
																	}
																	$contactsTable = 'fromTempContTable';
																}
															}
									else
									{
										$que2="SELECT CONCAT_WS( ' ', staffacc_contact.fname, staffacc_contact.mname, staffacc_contact.lname ),staffacc_cinfo.sno,staffacc_cinfo.username FROM staffacc_contact LEFT JOIN staffacc_cinfo on staffacc_contact.username = staffacc_cinfo.username AND staffacc_cinfo.type IN ('CUST','BOTH') LEFT JOIN staffacc_list ON staffacc_list.username = staffacc_cinfo.username WHERE staffacc_contact.sno ='".$billcontact."' AND staffacc_list.status='ACTIVE' AND staffacc_contact.acccontact='Y' and staffacc_contact.username!=''";
																	$res2=mysql_query($que2,$db);
																	if(mysql_num_rows($res2)>0){
																		$row2=mysql_fetch_row($res2);
																		$billcont=$row2[0];
																		$billcompany=$row2[1];
																		$billcont_stat=$row2[2];
																	}else{
																		$billcompany=$page11[36];
																		if($bill_loc>0){
																			$que2="select csno from staffacc_location where sno='".$bill_loc."' AND csno= '".$billcontact."' AND ltype='con' AND status='A'";
																			$res2=mysql_query($que2,$db);
																			if(mysql_num_rows($res2)>0){
																				$bill_loc=0;
																			}
																		}
																		$billcont= '';
																		$billcont_stat='';
																		$billcontact=0;
																   }
															}
								}
								else if($bill_loc>0 && ($billcontact==0 || $billcontact==""))
								{       if($chk_bilcont == 'frm_crm' && $chk_bilcompaddr == 'frm_crm')
									{
																$que2="select csno from staffoppr_location where sno='".$bill_loc."' and ltype in ('com','loc')";
																$res2=mysql_query($que2,$db);
																$row2=mysql_fetch_row($res2);
																$billcompany=$row2[0];

															}else{
																$que2="select csno from staffacc_location where sno='".$bill_loc."' and ltype in ('com','loc')";
																$res2=mysql_query($que2,$db);
																$row2=mysql_fetch_row($res2);
																$billcompany=$row2[0];
															}
								}
							$cus_username="select username from staffacc_cinfo where sno='".$billcompany."'";
							$cus_username_res=mysql_query($cus_username,$db);
							$cust_username=mysql_fetch_row($cus_username_res);
							$custusername=$cust_username[0];
							?>

							<tr>
								<input type="hidden" name="billcompany_sno" id="billcompany_sno" value="<?php echo $billcompany;?>">
								<input type="hidden" name="billcompany_username" id="billcompany_username" value="<?php echo $custusername;?>">
								<td width="167" class="summaryform-bold-title">Billing Address</td>
								<td><span id="billdisp_comp"><input type="hidden" name="bill_loc" id="bill_loc"><a class="crm-select-link" href="javascript:bill_jrt_comp('bill')">select company</a>&nbsp;</span></span>&nbsp;<span id="billcomp_chgid">&nbsp;</span></td>
							</tr>

							<tr>
								<input type="hidden" name="billcontact_sno" id="billcontact_sno" value="<?php echo $billcontact;?>">
								<td width="167" class="summaryform-bold-title">Billing Contact<font class=sfontstyle><?=$mandatory_madison;?></font>
									<div id="dateSelGridDiv" class="weekrule" style="display: inline-block;">
									<a href="#" class="tooltip"><i class="fa fa-info-circle"></i><span style="width:350px"><table class="notestooltiptable" width="350px" height="40"><tbody><tr><td class="notestooltip" style="text-align:left" align="center"><div style="font-weight: normal; line-height: 22px; font-size:11px !important;">Invoices generated using "Create Invoices by Customer, Employee, PO Number, Billing Address" ONLY will be email delivered to this billing contact and Secondary billing contact(s).</div></td></tr></tbody></table></span></a></div>
								</td>
								<td>
								<?php
								if($billcontact==0)
								{
									?>
									<span id="billdisp"><a class="crm-select-link" href="javascript:bill_jrt_cont('bill')">select contact</a></span>
									&nbsp;<span id="billchgid">
									</span>
									<?
								}
								else 
								{ 
									?>
									<span id="billdisp"><a class="crm-select-link" href="javascript:contact_func('<?php echo $billcontact;?>','<?php echo $billcont_stat;?>','bill')"><?php echo $billcont;?></a></span>
									&nbsp;<span id="billchgid"><span class=summaryform-formelement>(</span> <a class=crm-select-link href=javascript:bill_jrt_cont('bill')>change </a>&nbsp;
									<span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class="crm-select-link" href="javascript:removeContact('bill')">remove&nbsp;</a><span class=summaryform-formelement>&nbsp;)&nbsp;</span></span>
									<?
								}
								?>
								</td>
							</tr>

							<tr>
							<td class="summaryform-bold-title">Secondary Billing Contact(s)</td>
							<td>
								<select name='sec_bill_cons[]' class='select2-offscreen' id=billsecondarychgid multiple=multiple>
								</select>
								<input type="hidden" name="not_in_sec_bill_cont" id="not_in_sec_bill_cont" value="">
							</td>
							<td>&nbsp;</td>
							</tr>

							<tr>
							<td class="summaryform-bold-title">Invoice Method</td>
							<td>
								<select class="summaryform-formelement form-select w-auto" id="inmethod" name="inmethod" onchange="javascript:enableEmailOptions();">
								<option value="Mail" <?php echo sele($page11[57],"Mail");?>>Mail</option>
								<option value="Fax" <?php echo sele($page11[57],"Fax");?>>Fax</option>					
								<option value="Email" <?php echo sele($page11[57],"Email");?>>Email</option>
								<option value="Print" <?php echo sele($page11[57],"Print");?>>Print</option>
								</select>&nbsp;&nbsp;
								<select class="summaryform-formelement form-select w-auto" name="iterms">
								<option value="Weekly" <?php echo sele($page11[58],"Weekly");?>>Weekly</option>
								<option value="Bi-Weekly" <?php echo sele($page11[58],"Bi-Weekly");?>>Bi-Weekly</option>
								<option value="Bi-Monthly" <?php echo sele($page11[58],"Bi-Monthly");?>>Bi-Monthly</option>
								<option value="Semi-Monthly" <?php echo sele($page11[58],"Semi-Monthly");?>>Semi-Monthly</option>
								<option value="Monthly" <?php echo sele($page11[58],"Monthly");?>>Monthly</option>
								</select>
							</td>
							<td>&nbsp;</td>
							</tr>

							<tr>

							<td class="summaryform-bold-title">Invoice Delivery Method</td>
							<td>
								<select class="summaryform-formelement form-select w-auto" id="invdelivery" name="invdelivery">
								<option value="0" <?php echo sele($page11[66],"0");?>>Invoice(s) Only</option>
								<option value="1" <?php echo sele($page11[66],"1");?>>Invoice(s) with Time Sheet(s) and Expense(s)</option>
								<option value="2" <?php echo sele($page11[66],"2");?>>Invoice(s) with Time Sheet(s), Expense(s) and their attachment(s)</option>
								<option value="3" <?php echo sele($page11[66],"3");?>>All in one PDF ( Invoice(s) , Timesheet(s) and Expense(s))</option>
								</select>
							</td>
							<td>&nbsp;</td>
							</tr>		
							<?php if(THERAPY_SOURCE_ENABLED == "Y"){ ?>		
							<tr>
								<td colspan="3">
									<p style="color:red;font-size: 12px;margin: 0 !important;text-align: left !important;">NOTE: Timesheet (Notes) attachments size should not be more than 20MB. Any attachments more than 20MB will not be sent as attachment.</p>
								</td>
							</tr>
							<? } ?>
							<tr>
							<td class="summaryform-bold-title">Invoice Email Template</td>
							<td>
								<select class="summaryform-formelement form-select w-250" id="invemailtemplate" name="invemailtemplate">
									<?php
										$selectInvTmpl = "SELECT id,template_name FROM email_template WHERE status='ACTIVE' ORDER BY id ASC";
										$resultInvTmpl = mysql_query($selectInvTmpl,$db);
										while ($rowInvTmpl = mysql_fetch_array($resultInvTmpl)) {
											echo '<option value="'.$rowInvTmpl["id"].'" '.sele($page11[67],$rowInvTmpl["id"]).'>'.$rowInvTmpl["template_name"].'</option>';
										}
									?>
								</select>
							</td>
							<td>&nbsp;</td>
							</tr>	

							<tr>
								<td valign="top" class="summaryform-bold-title">Invoice Template</td>
								<td align="left">
								<?php
								if(THERAPY_SOURCE_ENABLED == "Y") { 
									$template_qry	= "SELECT B.invtmp_sno, A.invtmp_name, B.invtmp_default, IF(invtmp_version = 0, invtmp_name, CONCAT(invtmp_name, ' (1.', invtmp_version, ')')) versioned_name FROM IT_Template_Name A, Invoice_Template B WHERE B.invtmp_template = A.invtmp_sno AND B.invtmp_status = 'ACTIVE' AND B.invtmp_manual = '0' ORDER BY invtmp_name, versioned_name";
								}else{
									$template_qry	= "SELECT B.invtmp_sno, A.invtmp_name, B.invtmp_default, IF(invtmp_version = 0, invtmp_name, CONCAT(invtmp_name, ' (1.', invtmp_version, ')')) versioned_name FROM IT_Template_Name A, Invoice_Template B WHERE B.invtmp_template = A.invtmp_sno AND B.invtmp_status = 'ACTIVE' AND B.invtmp_manual = '0' AND B.inv_tmptype_sno != '3' ORDER BY invtmp_name, versioned_name";
								}
								$template_data	= mysql_query($template_qry,$db);
								$str_options	= "<option value='0' selected>-- Select Template --</option>";

								while ($rsc = mysql_fetch_array($template_data)) {

									$opt_value	= $rsc[0];
									$opt_text	= $rsc[3];
									$opt_select	= sele($page11[64], $opt_value);
									$opt_title	= html_tls_specialchars($rsc[3], ENT_QUOTES);

									$str_options	.= "<option value='".$opt_value."' ".sele($page11[64], $opt_value)." title='".$opt_title."'>".$opt_text."</option>";
								}
								?>
								<select name="inv_temp" id="inv_temp" class="form-control w-250"><?php echo str_replace('\\','',$str_options); ?></select>
								</td>
								<td valign="center" align="left">&nbsp;</td>
							</tr>
							<tr>
								<td valign="top" class="summaryform-bold-title"><?php if($venfrm == 'yes') { echo "Billing Terms"; } else { echo "Payment Terms";if(TRICOM_REPORTS=='Y'){ echo $mandatory_madison;} } ?></td>
								<td align="left">
								<?php
								 $BillPay_Sql = "SELECT billpay_termsid, billpay_code FROM bill_pay_terms WHERE billpay_status = 'active'";
								 if($venfrm == 'yes') 
								 {
									 $BillPay_Sql .= " AND billpay_type = 'BT'";
								 }
								 else
								 {
									 $BillPay_Sql .= " AND billpay_type = 'PT'";
								 }
								 $BillPay_Sql .= " ORDER BY billpay_code";
								 $BillPay_Res = mysql_query($BillPay_Sql,$db);
								?>
								<select name="billreq" id="billreq" class="form-select w-250">
									<option value=""> -- Select -- </option>
									<?php  
									while($BillPay_Data = mysql_fetch_row($BillPay_Res))
									{ 
									?>
										<option value="<?=$BillPay_Data[0];?>" <?php echo sele($page11[26],$BillPay_Data[0]); ?> title="<?=html_tls_specialchars(stripslashes($BillPay_Data[1]),ENT_QUOTES);?>"><?=stripslashes($BillPay_Data[1]);?></option>
									<?php 
									}
									?>
								</select>
								<input type="hidden" name="bill_terms_val" id="bill_terms_val" value="<?php echo $page11[56];?>">
								</td>
								<td valign="center" align="left">&nbsp;

								</td>
							</tr>
							<tr>
								<td valign="top" class="summaryform-bold-title">Service Terms</td>
								<td colspan="2"><textarea name="servterms" class="form-control" id="servterms" rows="2" cols="64"><?php echo html_tls_entities(stripslashes($page11[27]),ENT_QUOTES); ?></textarea>
								</td>
							</tr>
							<?php
							if($venfrm == 'yes')
							{
							?>
								<tr>
									<td valign="top" class="summaryform-bold-title">Sales Tax</td>
									<td colspan="2"><input class="summaryform-formelement form-control w-250" type=text name="custtax" id="custtax" size=10 maxsize=10 maxlength=5 value="<?php if($navigateFrom=='new') echo '10.00'; else echo html_tls_entities(stripslashes($page11[43]),ENT_QUOTES); ?>">&nbsp;<font class=afontstyle><?php echo "%";?></font></td>
								</tr>
								<td id="TaxPayableAcc" style="display:none;"><input type="hidden" name="accBillTPA" id="accBillTPA" value=""></td>
								<input type="hidden" name="getDisIds" id="getDisIds" value="">
							<?php
							}
							else
							{
							?>
								<tr>
								<td colspan="3" valign="top" width="100%" style="padding-left:0px;">
									<div class="form-taxback accordion-item">
									<table width="100%" border="0" class="crmsummary-taxedit-tablemin" id="taxinfo">
										<tr>
											<td class="crmsummary-taxcontent-title" style="border-bottom:#eee;" valign="top">
												 <a style='text-decoration: none;' onClick="classToggleTax(mntcmnt5,'DisplayBlock','DisplayNone',5,'taxinfo')" href="#hideExp5"> <span class="csummaryform-bold-title" id="company_billinginform">Sales Taxes</span></a>&nbsp;&nbsp;&nbsp;<a href="javascript:doAddCompanyTaxes('tax')" class="tax-add">Select</a> 
											</td>
											<td align="right" style="border-bottom:#eee;" valign="top">
												 <span id="rightflt" <?php echo $rightflt;?>>
													 <div class="form-opcl-btnleftside"><div align="left"></div></div>
													 <div><a onClick="classToggleTax(mntcmnt5,'DisplayBlock','DisplayNone',5,'taxinfo')" class="form-cl-txtlnk" href="#hideExp5"><b><div id='hideExp5'>+</div></b></a></div>
													 <div class="form-opcl-btnrightside"><div align="left"></div></div>
												 </span>
											</td>
										</tr>
									</table>
									</div>
									<div class="DisplayNone" id="mntcmnt5">
									<?php
										if($_SESSION['edit_company'.$Rnd] == "")
										{
											$selTaxes = "SELECT ct.taxid, ct.taxname, ct.taxtype, cct.classid, ct.taxes_pay_acct FROM company_tax ct, customer_discounttaxes cct WHERE ct.status = 'active' AND ct.taxid = cct.tax_discount_id AND cct.customer_sno = '".$page11[36]."' AND cct.customer_sno != '0' AND cct.status = 'active' AND cct.type = 'CompanyTax' GROUP BY ct.taxid";
										}
										else
										{
											$selTaxes = "SELECT ct.taxid, ct.taxname, ct.taxtype, cs.sno, ct.taxes_pay_acct FROM company_tax ct LEFT JOIN class_setup cs ON (cs.sno = ct.classid AND cs.status = 'ACTIVE') WHERE ct.status = 'active' AND ct.taxid IN (".stripslashes($page11[43]).")";
										}
										$resTaxes = mysql_query($selTaxes,$db);
										$totCount = mysql_num_rows($resTaxes);
									?>
									<table width="100%" border="0" align="left" id="mainTaxTable">
									<tr>
										<td width="2%" style="border-bottom:#eee;">&nbsp;</td>
										<td style="border-bottom:#eee;" id="taxSelectedData">
										<table width="100%" border="0" align="left">
											<?php 
											if($totCount == 0)
											{ 
											?>
												<tr>
													<td align="center" style="border-bottom:#eee;"><font class=afontstyle>No taxes are available.</font></td>
												</tr>
											<?php
											}
											else
											{

											?>
												<tr>
													<td width=25% style="border-bottom:#eee;">&nbsp;<font class=afontstyle><b>Name</b></font></td>
													<td width=20% style="border-bottom:#eee;">&nbsp;<font class=afontstyle><b>Type</b></font></td>
													<td width=25% style="border-bottom:#eee;">&nbsp;<font class=afontstyle><b>Tax Payable Account</b></font></td>
													<td width=20% style="border-bottom:#eee;"><?php if(MANAGE_CLASSES == 'Y') { ?>&nbsp;<font class=afontstyle><b>Class</b></font><?php } else ?>&nbsp;</td>
													<td width=10% style="border-bottom:#eee;">&nbsp;</td>
												</tr>
											<?php
												$getTaxIds = "";

												if($page11[59] != "")
												{
													$getTaxClassIds = array();

													$TaxClassIds = stripslashes($page11[59]);
													$sptTaxClassIds = explode(",",$TaxClassIds);
													$cntTaxClassIds = count($sptTaxClassIds);

													for($i=0; $i<$cntTaxClassIds; $i++)
													{
														$expTaxClassIds = explode("^AKKTCLS^",$sptTaxClassIds[$i]);
														$getTaxClassIds[$expTaxClassIds[0]] = $expTaxClassIds[1];
													}
												}

												while($rowTaxes = mysql_fetch_row($resTaxes))
												{
													if($getTaxIds == "")
														$getTaxIds = "'".$rowTaxes[0]."'";
													else
														$getTaxIds = $getTaxIds.",'".$rowTaxes[0]."'";

													$getTaxClsSelVal = ($page11[59] != "") ? $getTaxClassIds["'".$rowTaxes[0]."'"] : $rowTaxes[3];
												?>
													<tr>
														<td align="left" style="border-bottom:#eee;"><font class=afontstyle>&nbsp;<?php echo $rowTaxes[1];?></font></td>
														<td align="left" style="border-bottom:#eee;"><font class=afontstyle>&nbsp;<?php echo getManage($rowTaxes[2]);?></font></td>
														<td align="left" style="border-bottom:#eee;"><font class=afontstyle>&nbsp;<?php echo getAccountFullNameLocDep($rowTaxes[4]);?></font></td>
														<td align="left" style="border-bottom:#eee; vertical-align:top;">
														<?php
														if(MANAGE_CLASSES == 'Y') { 
															echo clsSelBoxRtn($getClassFunc, "custTaxClass".$rowTaxes[0], $getTaxClsSelVal, "summaryform-formelement", "style=\"width:130px;\"");
															}
															else
															{
															?>
															<input type="hidden" id="<?php echo "custTaxClass".$rowTaxes[0]; ?>" name="<?php echo "custTaxClass".$rowTaxes[0]; ?>" value="<?php echo $getTaxClsSelVal; ?>">
															<?php
															}
														?>
														</td>
														<td align="left" style="border-bottom:#eee;"><a href="javascript:doRemoveTaxes('<?php echo $rowTaxes[0]; ?>','tax');" class="tax-add">remove</a></td>
													</tr>
												<?php
												}
											}
											?>
										</table>
										</td>
									</tr>
									<input type="hidden" name="getTaxIds" id="getTaxIds" value="<?php echo $getTaxIds; ?>">
									<input type="hidden" name="TaxVal"  id="TaxVal" value="<?php echo $getTaxIds; ?>">
									</table>
									</div>
								</td>
								</tr>
								<td style='display:none;'><input type="hidden" name="custtax" id="custtax" value=""></td>
								<td id="TaxPayableAcc" style="display:none;"><input type="hidden" name="accBillTPA" id="accBillTPA" value=""></td>
								<tr>
								<td colspan="3" valign="top" width="100%" style="padding-left:0px;">
									<div class="form-taxback accordion-item">
									<table width="100%" border="0" class="crmsummary-taxedit-tablemin" id="discountinfo">
										<tr>
											<td class="crmsummary-taxcontent-title" style="border-bottom:#eee;" valign="top">
												 <a style='text-decoration: none;' onClick="classToggleTax(mntcmnt6,'DisplayBlock','DisplayNone',6,'discountinfo')" href="#hideExp6"> <span class="csummaryform-bold-title" id="company_billinginform">Discounts</span></a>&nbsp;&nbsp;&nbsp;<a href="javascript:doAddCompanyTaxes('discount')" class="tax-add">Select</a> 
											</td>
											<td align="right" style="border-bottom:#eee;" valign="top">
												 <span id="rightflt" <?php echo $rightflt;?>>
													 <div class="form-opcl-btnleftside"><div align="left"></div></div>
													 <div><a onClick="classToggleTax(mntcmnt6,'DisplayBlock','DisplayNone',6,'discountinfo')" class="form-cl-txtlnk" href="#hideExp6"><b><div id='hideExp6'>+</div></b></a></div>
													 <div class="form-opcl-btnrightside"><div align="left"></div></div>
												 </span>
											</td>
										</tr>
									</table>
									</div>
									<div class="DisplayNone" id="mntcmnt6">
									<?php
										if($_SESSION['edit_company'.$Rnd] == "")
										{
											$selDiscount = "SELECT cd.discountid, cd.name, cd.type, cct.classid FROM company_discount cd, customer_discounttaxes cct WHERE cd.status = 'active' AND cd.discountid = cct.tax_discount_id AND cct.customer_sno = '".$page11[36]."' AND cct.customer_sno != '0' AND cct.status = 'active' AND cct.type = 'Discount'";
										}
										else
										{
											$selDiscount = "SELECT cd.discountid, cd.name, cd.type, cs.sno FROM company_discount cd LEFT JOIN class_setup cs ON (cs.sno = cd.classid AND cs.status = 'ACTIVE')  WHERE cd.status = 'active' AND cd.discountid IN (".stripslashes($page11[55]).")";
										}
										$resDiscount = mysql_query($selDiscount,$db);
										$totCount = mysql_num_rows($resDiscount);
									?>
									<table width="100%" border="0" align="left" id="mainDisTable">
									<tr>
										<td width="2%" style="border-bottom:#eee;">&nbsp;</td>
										<td style="border-bottom:#eee;" id="disSelectedData">
										<table width="100%" border="0" align="left">
											<?php 
											if($totCount == 0)
											{ 
											?>
												<tr>
													<td align="center" style="border-bottom:#eee;"><font class=afontstyle>No discounts are available.</font></td>
												</tr>
											<?php
											}
											else
											{
											?>
												<tr>
													<td width=25% style="border-bottom:#eee;">&nbsp;<font class=afontstyle><b>Name</b></font></td>
													<td width=45% style="border-bottom:#eee;">&nbsp;<font class=afontstyle><b>Type</b></font></td>
													<td width=20% style="border-bottom:#eee;"><?php if(MANAGE_CLASSES == 'Y') { ?>&nbsp;<font class=afontstyle><b>Class</b></font><?php } else ?>&nbsp;</td>
													<td width=10% style="border-bottom:#eee;">&nbsp;</td>
												</tr>
											<?php
												$getDisIds = "";

												if($page11[60] != "")
												{
													$getDiscClassIds = array();

													$DiscClassIds = stripslashes($page11[60]);
													$sptDiscClassIds = explode(",",$DiscClassIds);
													$cntDiscClassIds = count($sptDiscClassIds);

													for($i=0; $i<$cntDiscClassIds; $i++)
													{
														$expDiscClassIds = explode("^AKKTCLS^",$sptDiscClassIds[$i]);
														$getDiscClassIds[$expDiscClassIds[0]] = $expDiscClassIds[1];
													}
												}

												while($rowDiscount = mysql_fetch_row($resDiscount))
												{
													if($getDisIds == "")
														$getDisIds = "'".$rowDiscount[0]."'";
													else
														$getDisIds = $getDisIds.",'".$rowDiscount[0]."'";

													$getDiscClsSelVal = ($page11[60] != "") ? $getDiscClassIds["'".$rowDiscount[0]."'"] : $rowDiscount[3];
												?>
													<tr>
														<td align="left" style="border-bottom:#eee;"><font class=afontstyle>&nbsp;<?php echo $rowDiscount[1];?></font></td>
														<td align="left" style="border-bottom:#eee;"><font class=afontstyle>&nbsp;<?php echo getManage($rowDiscount[2]);?></font></td>
														<td align="left" style="border-bottom:#eee; vertical-align:top;">
														<?php
														if(MANAGE_CLASSES == 'Y') { 
															echo clsSelBoxRtn($getClassFunc, "custDiscClass".$rowDiscount[0], $getDiscClsSelVal, "summaryform-formelement", "style=\"width:130px;\"");
															}
															else
															{
															?>
															<input type="hidden" id="<?php echo "custDiscClass".$rowDiscount[0]; ?>" name="<?php echo "custDiscClass".$rowDiscount[0]; ?>" value="<?php echo $getDiscClsSelVal; ?>">
															<?php
															}
														?>
														</td>
														<td align="left" style="border-bottom:#eee;"><a href="javascript:doRemoveTaxes('<?php echo $rowDiscount[0]; ?>','discount');" class="tax-add">remove</a></td>
													</tr>
												<?php
												}
											}
											?>
										</table>
										</td>
									</tr>
									<input type="hidden" name="getDisIds" id="getDisIds" value="<?php echo $getDisIds; ?>">
									<input type="hidden" name="DisVal"  id="DisVal" value="<?php echo $getDisIds; ?>">
									</table>
									</div>
								</td>
								</tr>
							<?php
							}
							?>


							<?php
							if(MANAGE_ACCOUNTS == 'N')
								$rowDispstyle  = ' style="display:none;"';
							else
								$rowDispstyle  = ' ';
							if($venfrm == 'yes')
							{
								echo "<td id='AccountReceivable' style='display:none;'><input type='hidden' name='accBillAR' id='accBillAR' value=''></td>";
							}
							else
							{				
							?>
							<tr <?php echo $rowDispstyle; ?>>
								<td valign="center" class="summaryform-bold-title">Accounts Receivable</td>
								<td valign="left" id='AccountReceivable' style='display:block;'>
									<select name="accBillAR" id="accBillAR" class="form-select w-250">
									<option value="">-- Select --</option>
									</select>
								</td>
								<td valign="center" align="left">
								</td>
							</tr>
							<?php
							}
							if($venfrm != 'yes')
							{
								echo "<td id='AccountPayables' style='display:none;'><input type='hidden' name='accBillAP' id='accBillAP' value=''></td>";
							}
							else
							{
							?>
							<tr <?php echo $rowDispstyle; ?>>
								<td valign="center" class="summaryform-bold-title">Accounts Payable</td>
								<td valign="left" id='AccountPayables' style='display:block;'>
									<select name="accBillAP" id="accBillAP" class="form-select w-250">
									<option value="">-- Select --</option>
									</select>
								</td>
								<td valign="center" align="left">
								</td>
							</tr>
							<?php
							}
							if($venfrm == 'yes')
							{
								echo "<td id='MiscellIncomeTd' style='display:none;'><input type='hidden' name='MiscellIncome' id='MiscellIncome' value=''></td>";
							}
							else
							{
							?>
							<tr <?php echo $rowDispstyle; ?>>
								<td valign="center" class="summaryform-bold-title">Income Accounts</td>
								<td valign="left" id='MiscellIncomeTd' style='display:block;'>
									<select name="MiscellIncome" id="MiscellIncome" class="form-select w-250">
									<option value="">-- Select --</option>
									</select>
								</td>
								<td valign="center" align="left">
								</td>
							</tr>
							<?php
							}
							if($venfrm != 'yes')
							{
								echo "<td id='MiscellExpTd' style='display:none;'><input type='hidden' name='MiscellExpense' id='MiscellExpense' value=''></td>";
							}
							else
							{
							?>
							<tr <?php echo $rowDispstyle; ?>>
								<td valign="center" class="summaryform-bold-title">Expense Accounts</td>
								<td valign="left" id='MiscellExpTd' style='display:block;'>
									<select name="MiscellExpense" id="MiscellExpense" class="form-select w-250">
									<option value="">-- Select --</option>
									</select>
								</td>
								<td valign="center" align="left">
								</td>
							</tr>
							<?php
							}?>
							<tr>
								<td valign="middle" class="summaryform-bold-title">Pay Burden</td>
								<td>
								<?php echo $pay_bt_str; ?>									
								</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td>
									<b><span id="burdenItemsStr" style="font-weight:bold;font-size: 12px;">&nbsp;</span></b>
								</td>
							</tr>
							<tr>
								<td valign="middle" class="summaryform-bold-title">Bill Burden</td>
								<td><?php echo $bill_bt_str; ?></td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td>
									<b><span id="billburdenItemsStr" style="font-weight:bold;font-size: 12px;">&nbsp;</span></b>
								</td>
							</tr>
							<?php
							if(isset($crm_select))
							{
								$roc	= $ratesObj->dispCustRatesSummary($ratesObj);
								$ros	= "";
								if(SHIFT_SCHEDULING_ENABLED=="Y")
								{
									$ros	= $ratesObj->dispShiftRatesSummary($ratesObj);
								}
								if($roc != "")
								{?>
									<tr>
										<td valign="middle" class="summaryform-bold-title">Default Rate(s)</td>
										<?php echo $roc; ?>
										<?php echo $ros; ?>
									</tr>					
								<?php
								}
								if($roc != "" || $ros != "")
								{
								?>
									<tr>
										<td colspan="2">
											<span class="billInfoNoteStyle">
												Note : Above Default Rates are listed from CRM Company record.
											</span>
										</td>
									</tr>
								<?php
								}

							}?>
						</table>
						</div>
					</div>
				</div>
				<?php
				if(PAYROLL_PROCESS_BY == "VERTEX" && $edit_acc=='yes')
				{
					require_once("../../Include/class.NetPayroll.php");
					require_once("nusoap.php");

					// Get the taxes for the company
					$selectedTaxes='';
					if($addr!=''){

						$sel_qury = "select sno, vprt_GeoCode, zip from staffacc_cinfo where username= '".$addr."'";
						$sel_res = mysql_query($sel_qury,$db); 
						$sel_row = mysql_fetch_row($sel_res);

						if($sel_row[1] == '' || $sel_row[1] == '0'){

							$argElements = array("EntityType"=>'Customer',"EntityId"=>$sel_row[0],"EntityZip"=>$sel_row[2]);

							$OBJ_NET_PAYROLL_Geo = new NetPayroll();
							$sel_row[1] = $OBJ_NET_PAYROLL_Geo->setEntityGeo($argElements);				
						}

						$selLocID = "SELECT d.loc_id FROM Client_Accounts ca INNER JOIN department d ON ca.deptid=d.sno WHERE typeid=".$sel_row[0]." AND ca.status='active'";
						$resLocID = mysql_query($selLocID,$db); 
						$rowLocID = mysql_fetch_row($resLocID);

						$sqlTaxes = "SELECT taxsno,exempt from vprt_taxhan_cust_apply where apply= 'Y' AND status='A' AND custid=".$sel_row[0];
						$resTaxes = mysql_query($sqlTaxes,$db);
						if(mysql_num_rows($resTaxes)>0){
							while($rowTaxes = mysql_fetch_row($resTaxes)){
								// Get Tax ID
								$sql = "SELECT taxid,schdist from vprt_taxhan where sno=".$rowTaxes[0];
								$resSql = mysql_query($sql,$db);
								$row = mysql_fetch_row($resSql);
								if($selectedTaxes=='')
									$selectedTaxes = $row[0].'_'.$row[1].'^'.$rowTaxes[0]."^".$rowTaxes[1];
								else
									$selectedTaxes .= '|'.$row[0].'_'.$row[1].'^'.$rowTaxes[0]."^".$rowTaxes[1];
							}
						}	
					}

					$OBJ_NET_PAYROLL = new NetPayroll('Customer');
			?>		
				<input type="hidden" name="hdnDropVal" id="hdnDropVal" value="">
				<input type="hidden" name="selectedTaxes" id="selectedTaxes" value="<?php echo $selectedTaxes?>">
				<input type="hidden" name="taxSetupScreenName" id="taxSetupScreenName" value="taxSetupDet">
				<input type="hidden" name="hdntaxdetailArr" id="hdntaxdetailArr" value="">
				<input type="hidden" name="hdnLocID" id="hdnLocID" value="<?php echo $rowLocID[0];?>">	
				<div class="accordion-item">
					<h2 class="accordion-header" id="TaxSetup">
						<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTaxSetup" aria-expanded="false" aria-controls="collapseTaxSetup">
							Tax Setup
						</button>
					</h2>
					<div id="collapseTaxSetup" class="accordion-collapse collapse" aria-labelledby="TaxSetup">
						<div class="accordion-body">
							<table class="table">
								<tr>
									<td width="120" class="summaryform-bold-title">Geographic Location</td>
									<td><?php echo $OBJ_NET_PAYROLL->getGeoCode($page11[6],$sel_row[1]);?>&nbsp;&nbsp;&nbsp;</td>
								</tr>
								<tr>
									<td colspan="2" id="TaxesContainer"></td>
								</tr>
							</table>
						</div>
					</div>
				</div>
				<?php
					}
				?>
				<div class="accordion-item">
					<h2 class="accordion-header" id="CCOI">
						<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCCOI" aria-expanded="false" aria-controls="collapseCCOI">
							Customer Culture/Onboarding Information
						</button>
					</h2>
					<div id="collapseCCOI" class="accordion-collapse collapse" aria-labelledby="CCOI">
						<div class="accordion-body">
							<table class="table">
							  <tr>
								 <td width="167" class="summaryform-bold-title">Dress Code</td>
								<td><input class="summaryform-formelement form-select w-250" type=text name="dcode" id="dcode" size=54 maxsize=54 maxlength=54 value="<?php echo html_tls_entities(stripslashes($page11[28]),ENT_QUOTES); ?>">&nbsp;&nbsp;&nbsp;</td>
							 </tr>
							  <tr>
								 <td class="summaryform-bold-title">Telecommuting Policy</td>
								 <td><input class="summaryform-formelement form-select w-250" type=text name="tpolicy" id="tpolicy" size=54 maxsize=54 maxlength=54 value="<?php  echo html_tls_entities(stripslashes($page11[29]),ENT_QUOTES);?>">&nbsp;&nbsp;&nbsp;
							</td>
							  </tr>
							 <tr>
								 <td class="summaryform-bold-title">Smoking Policy</td>
								 <td><input class="summaryform-formelement form-select w-250" type=text name="spolicy" id="spolicy" size=54 maxsize=54 maxlength=54 value="<?php  echo html_tls_entities(stripslashes($page11[30]),ENT_QUOTES); ?>">&nbsp;&nbsp;&nbsp;
							</td>
							  </tr>
							 <tr>
								 <td><div class="summaryform-bold-title">Parking</div><span class="summaryform-nonboldsub-title">(check all that apply)</span></td>
								 <td>
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="checkbox" name="parking1" id="parking1" value="free" <?php  echo sent_check($sptdd[0],"free"); ?>>
										<label class="form-check-label">Free</label>
									</div>

									<div class="form-check form-check-inline">
										<input class="form-check-input" type="checkbox" name="parking2" id="parking2" value="onsite" <?php  echo sent_check($sptdd[1],"onsite"); ?>>
										<label class="form-check-label">On Site</label>
									</div>

									<div class="form-check form-check-inline">
										<input class="form-check-input" type="checkbox" name="parking3" id="parking3" value="offsite" <?php  echo sent_check($sptdd[2],"offsite"); ?>>
										<label class="form-check-label">Off Site</label>
									</div>

									<div class="form-check form-check-inline">
										<input class="form-check-input" type="checkbox" name="parking4" id="parking4" value="lspaces" <?php  echo sent_check($sptdd[3],"lspaces");?>>
										<label class="form-check-label">Limited Spaces</label>
									</div>

									<div class="form-check form-check-inline">
										<input class="form-check-input" type="checkbox" name="parking5" id="parking5" value="pspaces" <?php echo sent_check($sptdd[4],"pspaces");?>>
										<label class="form-check-label">Plenty of Spaces</label>
									</div>




								<br />
								<div class="form-check form-check-inline">
									<span class="d-flex align-items-center">
									<input class="form-check-input" type="checkbox" name="parking6" id="parking6" value="prate" <?php echo sent_check($sptdd[5],"prate"); ?>>
									<label class="form-check-label">Rate&nbsp;(&nbsp;$<input class="summaryform-formelement" type="text" size=5 name="prateval" id="prateval" value="<?php  echo html_tls_entities(stripslashes($exp_spd[1]),ENT_QUOTES); ?>">&nbsp;)</label>
									</span>
								</div>

								<div class="form-check form-check-inline">
									<input class="form-check-input" type="checkbox" name="parking7" id="parking7" value="validate" <?php  echo sent_check($sptdd[6],"validate"); ?>>
									<label class="form-check-label">Validate</label>
								</div>

								<div class="form-check form-check-inline">
									<input class="form-check-input" type="checkbox" name="parking8" id="parking8"  value="public" <?php  echo sent_check($sptdd[7],"public"); ?>>
									<label class="form-check-label">Public</label>
								</div>

							</td>
							  </tr>
							<tr>
								 <td valign="top" class="summaryform-bold-title">Directions</td>
								 <td><textarea name="directions" class="form-control" id="directions" rows="2" cols="64"><?php  echo html_tls_entities(stripslashes($page11[32]),ENT_QUOTES); ?></textarea>
							</td>
							  </tr>
							 <tr>
								 <td valign="top" class="summaryform-bold-title">Other Info/Culture</td>
								 <td><textarea name="infocul" class="form-control" id="infocul" rows="2" cols="64"><?php echo html_tls_entities(stripslashes($page11[33]),ENT_QUOTES); ?></textarea>
							</td>
							  </tr>
							</table>
						</div>
					</div>
				</div>
			</div>
	 
			<fieldset>
			<legend><font class=afontstyle>Customer Contacts</font><? if($venfrm == 'yes') { ?><font class=sfontstyle></font><?php } ?></legend>
			<table width=100% cellpadding=3 cellspacing=0 border=0>
			<tr valign=top>
			<td align=right>
				<a  href="javascript:doAddCon()" tabindex=28 ><font class=linkrow>Add Contact</font></a> &nbsp;&nbsp;
                                <?php if($newcust!='newcust'){ ?>
                                    <a class="edit-list" id='selectCRMId' href='javascript:searchWindow()'><font class=linkrow>Select CRM Contact</font></a>
                               <?php  } ?>
				
			</td>
			</tr>
            <tr>
				<td align=center>
				<table width=100% cellpadding=0 cellspacing=1 border=0>
					<?php
					$contSnos = "";
					$sep = "";

					if($crmConFlag == 1){
						$arry = $crmConArr;
					}else{
						$arry = $arry;
					}

					if(count($arry)>0)
					{
						$opprSno = '';
						$show_contact_ID = is_numeric($arry[0][4]) ? "ID - Contact Name":"Contact Name";
						print "<tr><td><table width=100% border=0 cellpadding=2 cellspacing=0><tr class=hthbgcolor><td width=25%><font class=afontstyle>".$show_contact_ID."</font></td><td width=25%><font class=afontstyle>Title</font></td><td width=10%><font class=afontstyle>Contact Type</font></td><td width=20%><font class=afontstyle>Phone Number</font></td><td width=10%><font class=afontstyle>&nbsp;</font></td><td width=10%><font class=afontstyle>&nbsp;</font></td></tr>";
						for($j=0;$j<count($arry);$j++)
						{
							$opprSno .= $arry[$j][7].',';	
							print "<tr class='panel-table-content-new'>";
							
							for($i=0;$i<4;$i++)
							{
								if($arry[$j][2]=="--"){
								   $arry[$j][2]="";
								}
								if($i == 0 && is_numeric($arry[$j][4])){
									$value = $arry[$j][4]." - ".stripslashes($arry[$j][$i]); 
								}else{
									$value = stripslashes($arry[$j][$i]);
								}
								print "<td><font class=summarytext>".$value."</font>&nbsp;</td>"; 
							}
							

							if($crmConFlag == 0) {

								print "<td><a href=javascript:editCon('".$arry[$j][4]."','".$arry[$j][5]."')><font class=linkrow>Edit</font></a></td><td>";

								if ($arry[$j][7] == 'N') {

									print "<a href=javascript:delCon('".$arry[$j][4]."','".$arry[$j][5]."','".$arry[$j][6]."')><font class=linkrow>Delete</font></a>";
								}

								print "</td></tr>";

							}else{
								 print "<td><font class=summarytext><font class=afontstyle><a href=javascript:delTempCon('".$arry[$j][8]."','".$arry[$j][7]."','".$arry[$j][5]."')>Delete</a></font></td></tr>";
								 
							}
							$contSnos .= $sep.$arry[$j][4];
							$sep = ",";
						}
						
						if($crmConFlag == 1){	                       				
						   $opprSno = rtrim($opprSno,',');				  
						}
						$crmConFlag = 0;
						
						
						
						print "<input type='hidden' name='opprSno' id='opprSno' value='".$opprSno."' />
						<input type='hidden' name='opprCmpSno' id='opprCmpSno' value='".$srnum."' />";
						print "</table></td></tr>";
						$ContAvail = 'yes';
					}
					else
					{
						print "<tr><td align=center><input type='hidden' name='opprSno' id='opprSno' value='' /><input type='hidden' name='opprCmpSno' id='opprCmpSno' value='".$srnum."'  /><font class=afontstyle >No Contacts are available.</font></td></tr>";
						$ContAvail = 'no';
					}
					?>
				</table>
				</td>
			</tr>
			</table>
			</fieldset>
			<?php			
			if($venfrm == 'yes')
			{			
			?>
			<br /><br />
			<fieldset>
			<legend><font class=afontstyle>Customer Candidates</font><font class=sfontstyle>*</font></legend>
			<table width=100% cellpadding=3 cellspacing=0 border=0>
			<tr valign=top>
			<td align=right>
				<a  href="javascript:doAddCand()" tabindex=28><font class=linkrow>Add Candidate</font></a>
			</td>
			</tr>
            <tr>
				<td align=center>
			    <table width=100% cellpadding=0 cellspacing=1 border=0>
			  		<?php
					$countCand = count($arrCandVal);
					$countContactCand = count($arrCandFromContact);		
					
					if($countCand>0 || $countContactCand>0)
					{
						//Removed status column in display level -- kumar raju k.	
						print "<tr><td><table width='100%' border='0' cellpadding='2' cellspacing='0'><tr class='hthbgcolor'><td width='25%'><font class=afontstyle>Candidate Name</font></td><td width='10%'><font class=afontstyle>Tax Type</font></td><td width='25%'><font class='afontstyle'>Email Id</font></td><td width='10%'><font class='afontstyle'>&nbsp;</font></td><td width='10%'><font class='afontstyle'>&nbsp;</font></td></tr>";

						for($j=0;$j<$countCand;$j++)
						{
							print "<tr class='panel-table-content-new'>";
							//Changed the count, due to removal of status column -- kumar raju k.
                            for($i=0;$i<3;$i++)
							{
								//Checking for the data, if data not present assigning empty for getting design -- kumar raju k.
								if($arrCandVal[$j][$i] == "")
									$arrCandVal[$j][$i] = "&nbsp;";
								
                                print "<td><font class='summarytext'>".$arrCandVal[$j][$i]."</font></td>";
                            }
							//Changed the passing value, due to removal of status column -- kumar raju k.
							
							print "<td><font class='summarytext'><a href=javascript:editCand('".$arrCandVal[$j][3]."')>Edit</a></font></td><td><font class=afontstyle><a href=javascript:delCand('".$arrCandVal[$j][3]."')>Delete</a></font></td></tr>";
						}
						for($n=0; $n<$countContactCand; $n++)
						{
							print "<tr class='panel-table-content-new'>";
							//Changed the count, due to removal of status column -- kumar raju k.
                            for($i=0;$i<3;$i++)
							{
								//Checking for the data, if data not present assigning empty for getting design -- kumar raju k.
								if($arrCandFromContact[$n][$i] == "")
									$arrCandFromContact[$n][$i] = "&nbsp;";
								
                                print "<td><font class='summarytext'>".$arrCandFromContact[$n][$i]."</font></td>";
                            }
							//Changed the passing value, due to removal of status column -- kumar raju k.
                            print "<td><font class='summarytext'><a href=javascript:editContCand('".$arrCandFromContact[$n][4]."')>Edit</a></font></td><td><font class=afontstyle><a href=javascript:delCand('".$arrCandFromContact[$n][3]."^|^".$arrCandFromContact[$n][4]."')>Delete</a></font></td></tr>";			
							$temp_cand_var = (trim($temp_cand_var)=='')?$arrCandFromContact[$n][3]:$temp_cand_var.','.$arrCandFromContact[$n][3];
						}
						echo '<input type="hidden" name="contactCandidates" id="contactCandidates" value="'.$temp_cand_var.'" />';
						print "</table></td></tr>";
						$CandAvail='yes';
					}
					else
					{
						print "<tr><td align='center'><font class='afontstyle'>No Candidates are available.</font></td></tr>";
						$CandAvail='no';
					}
					?>
				</table>
				</td>
			</tr>
			</table>
			</fieldset>
			<?php
			}
			?>
			<input type="hidden" name="contSnos" id="contSnos" value="<? echo $contSnos; ?>">
			<input type="hidden" name="ContAvail" id="ContAvail" value="<?php echo $ContAvail; ?>">
			<input type="hidden" name="CandAvail" id="CandAvail" value="<?php echo $CandAvail; ?>">
		
		</fieldset></div>
		<br />
		</div>
		<table width=100% cellpadding=0 cellspacing=0 border=0>
		<?php if($srnum=="") { ?>
		<tr id="botmsave" style="display:none" class="NewGridBotBg">
		<?php
		$name=explode("|","fa fa-floppy-o~Save|fa fa-times~Close");
		$link=explode("|","javascript:validatepage1(this)|javascript:window.close()");
		$heading=$head;
		?>
		</tr>
		
		<tr id="botm2save" style="" class="NewGridBotBg">
		<?php
		$name=explode("|","fa fa-times~Close");
		$link=explode("|","javascript:window.close()");
		$heading=$head;
		?>
		</tr>
		<?php } else { ?>
		<tr class="NewGridBotBg">
		<?php
		$name=explode("|","fa fa-clone~Update|fa fa-times~Close");
		$link=explode("|","javascript:doList(this)|javascript:doClose()");
		$heading=$head;
		?>
		</tr>
		<?php } ?>
		<tr>
			<td><font class=afontstyle></font></td>
		</tr>
		</table>
		</div>
		
		<? if($addr!=''&& $venfrm!='yes'){?>
		  <div class="tab-page" id="tabPage12">
			<h2 class="tab">Activities</h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage12" ),"viewact.php?addr=<?php echo $addr;?>&newcust=<?php echo $newcust?>&Rnd=<?php echo $Rnd;?>" );</script>
			</div>
		<? }?>	
		
        <script type="text/javascript">tp1.setSelectedIndex(0);</script>
	</div>
	</td>
	</tr>
	</table>
</div>
</div>
</form>
<script type="text/javascript">
<?php
if($ptype == "Appointment")
{
	?>
	result = "editappoint.php?addr=<?php echo $addr . '&line=' . $line . '&con_id=' . $con_id."&module_type_appoint=".$_GET['module_type_appoint']; ?>";
	editWin(result);
	<?php
}
else if($ptype == "Task")
{
	?>
	result = "edittask.php?addr=<?php echo $addr . '&sno=' . $line . '&con_id=' . $con_id."&module_type_appoint=".$_GET['module_type_appoint']; ?>";
	editWin(result);
	<?php
}
?>
</script>

<script type="text/javascript">
var contval='<?php echo $contval;?>';
if(contval!='undefined' && contval!="")
editCon(contval,'0');
</script>
<?php
if(($page11[46] == "Y" && $_SESSION['edit_company'.$Rnd]!='') || ($defaultChecked == "Y" && ($page11[34] == "0" || $page11[34] == "")))
{
	echo "<script type='text/javascript'>
	var toList = document.markreqman;
	var oDiv_contact = document.getElementById('disp');
	var odiv_chg = document.getElementById('chgid');

	str3 = '$list_cont';
	var aColors = str3.split('|');
	var stack = new Array;
	stack = aColors;
	contact_size  = stack.length;

	str4 = '$list_cont_sno';
	var bColors = str4.split('|');
	var cont_sno = new Array;
	cont_sno = bColors;
	contactsno_size  = cont_sno.length;

	str5 = '$list_status';
	var cColors = str5.split('|');
	var cont_status = new Array;
	cont_status = cColors;
	contactsno_status  = cont_status.length;

	if('$list_cont_sno' == '')
	{
		toList.contact_sno.value = '';
		oDiv_contact.innerHTML = '<input type=hidden name=list_contact><a class=crm-select-link href=javascript:bill_cont() id=disp><strong>select</strong> contact</a>';
		odiv_chg.innerHTML = '<a href=javascript:bill_cont()><i class='fa fa-search'></i></a><span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class=crm-select-link href=javascript:donew_add()><strong>new</strong>&nbsp;contact</a>';
	}
	else if(contactsno_size > 1)
	{
		toList.contact_sno.value = '';
		newselect = '<select name=list_contact ';
		for(i=0;i<contactsno_size;i++)
		{
			if(cont_sno[i]=='$page11[34]')
				var getselect = 'selected';
			else
				var getselect = '';
			newselect += 'onchange=chg_fun(this.value,\''+cont_status[i]+'\')>';
			newselect += '<option value='+cont_sno[i]+' ';
			newselect += getselect;
			newselect += '>'+stack[i]+'</option>';
		}
		newselect += '</select>&nbsp;';
		oDiv_contact.innerHTML = '';
		odiv_chg.innerHTML = '<span class=summaryform-formelement>(&nbsp;</span>'+newselect+'<a class=crm-select-link href=javascript:bill_cont()><strong>change</strong> </a>&nbsp;<a href=javascript:bill_cont()><i class='fa fa-search'></i></a><span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class=crm-select-link href=javascript:donew_add()><strong>new</strong></a><span class=summaryform-formelement>&nbsp;)&nbsp;</span>';
	}
	else
	{
		var newcontsnoval = '$list_status';
		oDiv_contact.innerHTML = '';
		odiv_chg.innerHTML = '';
		oDiv_contact.innerHTML = '<input type=hidden name=list_contact id=list_contact value='+cont_sno[0]+'><a class=crm-select-link href=javascript:contact_func('+cont_sno[0]+',\''+newcontsnoval+'\') id=disp><strong>'+stack[0]+'</strong></a></span>';
		odiv_chg.innerHTML = '&nbsp;<span id=chgid><span class=summaryform-formelement>(</span> <a class=crm-select-link href=javascript:bill_cont()><strong>change</strong> </a>&nbsp;<a href=javascript:bill_cont()><i class='fa fa-search'></i></a><span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class=crm-select-link href=javascript:donew_add()><strong>new</strong></a><span class=summaryform-formelement>&nbsp;)&nbsp;</span>';
		document.getElementById('contact_sno').value = cont_sno[0];
	}

	if('$chkSessionAvail' == 'Y' && '$statval' != 'ER')
	{
		classToggle(mntcmnt2,'DisplayBlock','DisplayNone',2,'billinginfo');
	}
	</script>";
}
?>
<script language="javascript">
window.onload=function(){
				displayAccountTAXARAP('','CUST','<?php echo $page11[61]; ?>','<?php echo $srnum ?>','custVenClass');
				};
<?php
if(PAYROLL_PROCESS_BY == "VERTEX" && $edit_acc == "yes")
		echo "QueryTaxNames('geoList','TaxesContainer');\n";
?>
</script>

<?

	
?>


<?php



?>
<?php require("footer.inc.php");?>
<script type="text/javascript">
$(document).ready(function(){
	$('#billsecondarychgid').select2({
		placeholder: "Select Contacts to Notify Email"/*,
		multiple: true,
		closeOnSelect: false,
		matcher: function(term, text) { return text.toUpperCase().indexOf(term.toUpperCase())==0; } */
	});
});

window.onload = function() {

			 <?php 
			if($chk_bilcont == 'frm_crm' && $chk_bilcompaddr == 'frm_crm')
			{	
				if($billcontact>0 || $bill_loc>0 || $billcompany>0)
					{ ?>
						getCRMLocations('<?php echo $billcompany;?>','<?php echo $billcontact;?>','<?php echo $bill_loc; ?>','bill',0,'<?php echo $contactsTable ;?>');

						bindSecondaryBillingContacts();
			<?php 	}
			}		
			else
			{

			if($billcontact>0 || $bill_loc>0 || $billcompany>0)
				{ ?>
					getACCLocations('<?php echo $billcompany; ?>','<?php echo $billcontact;?>','<?php echo $bill_loc; ?>','bill');
					bindSecondaryBillingContacts();					
				<?php 
				}
			}

			//code will execute if any session value set
			if(isset($page11[68]) && $page11[68]!="" && $page11[68]!=null)
			{ 
				?>
				var sec_bill_opt_str = "<?php echo $page11[68]; ?>";
				var sec_bill_opt_arr = sec_bill_opt_str.split(',');
				$('#billsecondarychgid').val(sec_bill_opt_arr).trigger("change");
			<?php
			}	

			?>
			


	            displayAccountTAXARAP('','CUST','<?php echo $page11[61]; ?>','<?php echo $srnum ?>','custVenClass');
				
				<?php
				if($newComp == 'yes' && $srnum=="" && $addr == "")
				{				
				?>
					
					classToggle1(mntcmnt4,'DisplayBlock','DisplayNone',4,'acccustomer','no');
					savedisp();
				<?php
				}
				if($newComp == 'yes' && $srnum!="" && $addr == "") {
				?>
					classToggle1(mntcmnt4,'DisplayBlock','DisplayNone',4,'acccustomer','no');					
					getCompOld_Data();
				<?php
				}
				else if($page11[5] == "Other^0" || $page11[5] == "^0")
				{
				?>
					onCountryChange('<?php echo $page11[7]; ?>','OL');
				<?php
				}				
				if($page11[5] == "Other^0" || $page11[5] == "^0")
				{
				?>
					onCountryChange('<?php echo $page11[7]; ?>','OL');				
				<?php
				} else if($addr != ""){
				?>
					getComp_Data();
					getCompOld_Data();
				<?php
				}
				if($page11[5] == "Other^0" || $page11[5] == "^0") {
				?>
					onCountryChange('<?php echo $page11[7]; ?>','OL');
				<?php
				} ?>				
          enableEmailOptions();
}; 
function enableEmailOptions()
{
	let invMethodVal = $("#inmethod").val();
	if(invMethodVal == "Email")
	{
		$("#invdelivery, #invemailtemplate").removeClass('disabledDiv');
	}
	else
	{
		$("#invdelivery, #invemailtemplate").addClass('disabledDiv');
	}
}
</script>
</body>
</html>