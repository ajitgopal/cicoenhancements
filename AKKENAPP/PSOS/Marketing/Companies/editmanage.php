<?php
	$Cmpsno=$_GET[addr];
	require("global.inc");
	require("Menu.inc");
	require("dispfunc.php");
	require("functions.php");
	require($app_inc_path."displayoptions.php");
	require($akken_psos_include_path.'commonfuns.inc');
	require_once("multipleRatesClass.php");
	 
	$menu=new EmpMenu();
	$addr=$Cmpsno;
	
	//object used for getting the session rates if exists 
	$objMRT = new ManageRateTypes();
	
	//For getting the multiple rates
	$ratesObj = new multiplerates();
	$mode_rate_type = "company";
	$type_order_id = $addr;
	
	$OBJ_Comp_Role = new CommissionRoles($db, $username, 'comp', 'CRMCompany');

	// Fetching Dynamic UdfColNames
	$sql = "select t1.id, element_name, element_lable, t1.element, required, default_opt, auto_complete
			 from udf_form_details t1 left join udf_form_details_order t4 on t4.custom_form_details_id = t1.id
			  where t1.module  = 2 and t1.status = 'Active' order by t4.ele_order asc";

	 $result = mysql_query($sql);
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
	$comp_qry = "select sno,cname,address1,address2,city,status,state from staffoppr_cinfo where sno = ".$addr;
	$comp_res = mysql_query($comp_qry,$db);
	$comp_row = mysql_fetch_row($comp_res);

	$pass_var = '';
	if($comp_row[2] != '')
		$pass_var .= $comp_row[2];
	if($comp_row[3] != '')
		$pass_var .= " ".$comp_row[3];
	if($comp_row[4] != '')
		$pass_var .= " ".$comp_row[4];
	if($comp_row[6] != '')
		$pass_var .= " ".stripslashes($comp_row[6]);
	
	$pass_var = $comp_row[2]." ".$comp_row[3]." ".$comp_row[4]." ".$comp_row[6]; 
	$pass_newvar = $pass_var;
	$pass_cname = $comp_row[1];
	$status_parent = $comp_row[5];

	$sel_parqry = "select cname,address1,address2,city, state from staffoppr_cinfo where sno=".$addr;
	$sel_par_res = mysql_query($sel_parqry,$db);
	$sel_par_fetch =mysql_fetch_row($sel_par_res);
	 
	if($sel_par_fetch[0] != '')
		$pass_sel .= $sel_par_fetch[0];
	if($sel_par_fetch[1] != '')
		$pass_sel .= " ".$sel_par_fetch[1];
	if($sel_par_fetch[2] != '')
		$pass_sel .= ", ".$sel_par_fetch[2];
	if($sel_par_fetch[3] != '')
		$pass_sel .= ", ".$sel_par_fetch[3];

	$sel_cont = "select TRIM(CONCAT_WS('',fname,' ',lname,'(',IF(email='',nickname,email),')')),sno,fname,mname,lname from staffoppr_contact where csno =".$comp_row[0];
	$sel_res = mysql_query($sel_cont,$db);
	$sel_contact = "";
	$sel_contact_sno = "";
	while($sel_row = mysql_fetch_row($sel_res))
	{
		$sel_name = $sel_row[2]." ".$sel_row[3]." ".$sel_row[4];
		$sel_contact .= $sel_name."|";
		$sel_contact_sno .= $sel_row[1]."|";
	}

	$list_cont = trim($sel_contact,"|");
	$list_cont_sno = trim($sel_contact_sno,"|");

	function sele($a,$b)
	{
		if($a==$b)
			return "selected";
		else
			return "";
	}

	function getUser($uname)
	{
	 global $maildb,$db;
	 $User="SELECT name FROM users WHERE username='".$uname."'";
	 $Res_user=mysql_query($User,$db);
	 $Data_user=mysql_fetch_row($Res_user);
	 return $Data_user[0];
	}

	//dynamic session variables

	$compRandSession='edit_company'.$Rnd;
	$Cont_Sess='insno1'.$Rnd;
	$Oppr_Sess='oppsno'.$Rnd;

	//this is the query for getting  all the active users
	require_once($akken_psos_include_path.'class.getOwnersList.php');
	$ownersObj = new getOwnersList();
	$Users_Array = $ownersObj->getOwners();	
	
    $User_nos=implode(",",array_keys($Users_Array));
    $uersCnt=count($Users_Array);

	$que="select accessto,owner from staffoppr_cinfo where sno='".$addr."'";
	$res=mysql_query($que,$db);
	$row=mysql_fetch_row($res);
	
    if($row[0]==$username)
       $comp_Share_Type='private';

   else if($row[0]=='ALL')
      $comp_Share_Type='public';
	else
	{
	  $comp_Share_Type='share';
	  $emplist=$row[0]; //added by swapna for existing employee list.
	}


	//this will be when we r addinf a contact and coming back to this page
	if(($$compRandSession)!="" && $_SESSION['edit_company'.$Rnd]!="|")
	{ 
    	$accto=$row[0];
        $page11=explode("|",$_SESSION['edit_company'.$Rnd]);
				
		$ceo=html_tls_entities(stripslashes($page11[15]));
        $com_name=html_tls_entities(stripslashes($page11[0]));
        $com_url=html_tls_entities($page11[1]);
        $com_addr1=html_tls_entities(stripslashes($page11[2]));
        $com_addr2=html_tls_entities(stripslashes($page11[3]));
        $com_city=html_tls_entities(stripslashes($page11[4]));
        $com_state=html_tls_entities(stripslashes($page11[5]));
        $com_country=html_tls_entities(stripslashes($page11[6]));
        $com_zip=html_tls_entities(stripslashes($page11[7]));
		$com_industry=html_tls_entities(stripslashes($page11[15]));
		$com_keywords=html_tls_entities(stripslashes($page11[17]));
		$com_keytechs=html_tls_entities(stripslashes($page11[20]));
        $cfo=html_tls_entities(stripslashes($page11[16]));
        $sales_man=html_tls_entities(stripslashes($page11[17]));
        $com_type=html_tls_entities(stripslashes($page11[8]));
        $com_size=html_tls_entities(stripslashes($page11[9]));
        $no_com_loc=html_tls_entities(stripslashes($page11[10]));
        $no_years=html_tls_entities(stripslashes($page11[11]));
        $no_emps=html_tls_entities(stripslashes($page11[12]));
        $com_rev=html_tls_entities(stripslashes($page11[13]));
        $fed_id=html_tls_entities(stripslashes($page11[14]));
        $phone=html_tls_entities(stripslashes($page11[18]));
        $fax=html_tls_entities(stripslashes($page11[19]));
		$com_compnotes=html_tls_entities(stripslashes($page11[28]));
		$s_status=$page11[30];
		$c_owner=$page11[31];
		$comp_Share_Type=$page11[32];

		$c_ownership=$page11[34];
		$c_brief=$page11[35];
		$c_summary=$page11[36];
		$s_tags=$page11[37];

		$work_code=$page11[38];
		$bill_req=$page11[39];
		$ser_terms=$page11[40];
		$d_code=$page11[41];
		$t_policy=$page11[42];
		$s_policy=$page11[43];
		$parking=$page11[44];
		$emplist=$page11[47];
		if($parking != '')
		{
			$Other_Field1=explode("^",$parking);
			$Other_Field=explode("-",$Other_Field1[0]);
			$p_rate=$Other_Field1[1];
		}
		$directions=$page11[45];
		$info_cul=$page11[46];
		$billcontact=$page11[48];
		$bill_loc=$page11[49];

		$customer_id=$page11[50];
		$alternate_id=html_tls_entities(stripslashes($page11[51]));
		$phone_extn=html_tls_entities(stripslashes($page11[52]));
		
		$deptid=html_tls_entities(stripslashes($page11[55]));

		if($billcontact!=0)
		{
			$que2="select TRIM(CONCAT_WS('',fname,' ',lname,'(',IF(email='',nickname,email),')')),status,fname,mname,lname,address1,address2,city,state,csno from staffoppr_contact where sno='".$billcontact."'";
			$res2=mysql_query($que2,$db);
			$row2=mysql_fetch_row($res2);
			$billcont = $row2[2]." ".$row2[3]." ".$row2[4];
			$billcont_stat=$row2[1];
			$billcompany=stripslashes($row2[9]);
		}

		$sicCode=html_tls_entities(stripslashes($page11[23]));
		$tickerSymbol=html_tls_entities(stripslashes($page11[24]));
		$comp_Source=$page11[25];
		$department=$page11[53];
		$parentNo=$page11[27];
		if($parentNo!=0)
		{
    		$que2="select cname,status,address1,address2,city,state from staffoppr_cinfo where sno='".$parentNo."'";
    		$res2=mysql_query($que2,$db);
            $row2=mysql_fetch_row($res2);
            $parentName = $row2[0];
			$parent_addr = '';
			if($row2[0] != '')
			{
			  $parent_addr .= $row2[0];
			}
			if($row2[2] != '')
			{
			  $parent_addr .= " ".$row2[2];
			}
			if($row2[3] != '')
			{
			  $parent_addr .= " ".$row2[3];
			}
			if($row2[4] != '')
			{
			  $parent_addr .= ", ".$row2[4];
			}
			if($row2[5] != '')
			{
			  $parent_addr .= ", ".stripslashes($row2[5]);
			}
			
			if($parent_addr == '')
			{
			 $parent_addr = 'no address';
			}
			
			$parent_status = $row2[1];
        }
		//this will be when we r adding a contact and coming back to this page
		if($page11[29]=="editmanage" || $page11[29]=="editcontact")
        {
           //all the contact nos which were added
			$insno=$page11[56];
			$oppsno=$page11[57];
		}
		else
        {
            $insno=$page11[46];
			$oppsno=$page11[47];
		}


		if($insno!="")
        {
		   if($insno!='0')
			{
				if( ($_SESSION['insno1'.$Rnd])=="")
					$_SESSION['insno1'.$Rnd]=$insno;
				else
					$_SESSION['insno1'.$Rnd].=",".$insno;
			}
		}
		if($contactsList!="" && $contactsList!=0)
		{
			$_SESSION['insno1'.$Rnd]=$contactsList.",".$_SESSION['insno1'.$Rnd];
		}
        else
        {
			$que2="select sno from staffoppr_contact where status='ER' and csno='".$addr."'";
            $res2=mysql_query($que2,$db);
            while($row2=mysql_fetch_row($res2))
            {
                if( ($_SESSION['insno1'.$Rnd])=="")
                    $_SESSION['insno1'.$Rnd]=$row2[0];
                else
                    $_SESSION['insno1'.$Rnd].=",".$row2[0];

            }
        }
		
		if($oppsno!="")
		{
			if($oppsno!='0')
			{
				if(($_SESSION['oppsno'.$Rnd])=="")
					$_SESSION['oppsno'.$Rnd]=$oppsno;
				else
					$_SESSION['oppsno'.$Rnd].=",".$oppsno;
			}
		}
		else
		{
			$opp_que="select sno from staffoppr_oppr  where  csno='".$addr."' and oppr_status='ACTIVE'";
			$opp_res=mysql_query($opp_que,$db);
			while($data=mysql_fetch_row($opp_res))
			{
				if(($_SESSION['oppsno'.$Rnd])=="")
					$_SESSION['oppsno'.$Rnd]=$data[0];
				else
					$_SESSION['oppsno'.$Rnd].=",".$data[0];
			}
		}
		
		$compRoles = $_SESSION['compRole'.$Rnd];
	}
	else
	{
		$que3="select industry,'divisions','keywords',cname,curl,address1,address2,city,state,country,zip,ctype,csize,nloction,nbyears,nemployee,com_revenue,federalid,accessto,'','','','','',phone,fax,keytech,siccode,ticker,department,csource,parent,compbrief,compsummary,'',bill_req,service_terms,dress_code,tele_policy,smoke_policy,parking,park_rate,directions,culture,compowner,compstatus,owner,accessto,approveuser,bill_contact,bill_address,acc_comp,alternative_id,phone_extn,address_desc, deptid,ts_layout_pref from staffoppr_cinfo where sno='".$addr."'";
      	$res3=mysql_query($que3,$db);
    	$row3=mysql_fetch_row($res3);

		$com_industry=html_tls_entities(stripslashes($row3[0]));
        $com_name=html_tls_entities(stripslashes($row3[3]));
        $com_url=html_tls_entities(stripslashes($row3[4]));
        $com_addr1=html_tls_entities(stripslashes($row3[5]));
        $com_addr2=html_tls_entities(stripslashes($row3[6]));
        $com_city=html_tls_entities(stripslashes($row3[7]));
        $com_state=html_tls_entities(stripslashes($row3[8]));
        $com_country=html_tls_entities(stripslashes($row3[9]));
        $com_zip=html_tls_entities(stripslashes($row3[10]));
        $phone=html_tls_entities(stripslashes($row3[24]));
        $fax=html_tls_entities(stripslashes($row3[25]));
		$com_keytechs=html_tls_entities(stripslashes($row3[26]));

        $com_keywords=html_tls_entities(stripslashes($row3[2]));
        $com_type=html_tls_entities(stripslashes($row3[11]));
        $com_size=html_tls_entities(stripslashes($row3[12]));
        $no_com_loc=html_tls_entities(stripslashes($row3[13]));
        $no_years=html_tls_entities(stripslashes($row3[14]));
        $no_emps=html_tls_entities(stripslashes($row3[15]));
        $com_rev=html_tls_entities(stripslashes($row3[16]));
        $fed_id=html_tls_entities(stripslashes($row3[17]));

		$sicCode=html_tls_entities(stripslashes($row3[27]));
		$tickerSymbol=html_tls_entities(stripslashes($row3[28]));
		$department=stripslashes($row3[29]);
		$comp_Source=stripslashes($row3[30]);
		$parentNo=stripslashes($row3[31]);

		$com_compnotes='';
		$accto=stripslashes($row3[18]);

		$c_brief=stripslashes($row3[32]);
		$c_summary=stripslashes($row3[33]);
		$s_tags=stripslashes($row3[26]);

		$work_code=$row3[34];
		$bill_req=$row3[35];
		$ser_terms=$row3[36];
		$d_code=$row3[37];
		$t_policy=$row3[38];
		$s_policy=$row3[39];
		$directions=$row3[42];
		$info_cul=$row3[43];
		$c_ownership=$row3[44];
		$s_status=$row3[45];
		$c_owner=$row3[46];
		$parking=$row3[40];
		if($parking != '')
		{
			$Other_Field=explode("|",$parking);
		}
		$p_rate=$row3[41];

		$billcontact=$row3[49];
		$bill_loc=$row3[50];
		$customer_id=$row3[51];
		$alternate_id=html_tls_entities($row3[52]);
		$phone_extn=html_tls_entities($row3[53]);
		$adress_desc=html_tls_entities($row3[54]);		
		$deptid=html_tls_entities($row3[55]);
		$timesheet_layout_preference=html_tls_entities($row3[56]);

		if($billcontact!=0)
		{
			$que2="select TRIM(CONCAT_WS('',fname,' ',lname,'(',IF(email='',nickname,email),')')),status,fname,mname,lname,address1,address2,city,state,csno from staffoppr_contact where sno='".$billcontact."'";
			$res2=mysql_query($que2,$db);
			$row2=mysql_fetch_row($res2);
			$billcont=$billcont = $row2[2]." ".$row2[3]." ".$row2[4];
			$billcont_stat=$row2[1];
			$billcompany=$row2[9];
        }

		if($parentNo!=0)
		{
    		$que2="select cname,status,address1,address2,city,state from staffoppr_cinfo where sno='".$parentNo."'";
    		$res2=mysql_query($que2,$db);
            $row2=mysql_fetch_row($res2);
            $parentName = $row2[0];
			$parent_addr = '';
			if($row2[0] != '')
			{
			  $parent_addr .= $row2[0];
			}
			if($row2[2] != '')
			{
			  $parent_addr .= " ".$row2[2];
			}
			if($row2[3] != '')
			{
			  $parent_addr .= " ".$row2[3];
			}
			if($row2[4] != '')
			{
			  $parent_addr .= ", ".$row2[4];
			}
			if($row2[5] != '')
			{
			  $parent_addr .= ", ".$row2[5];
			}
			
			if($parent_addr == '')
			{
			 $parent_addr = 'no address';
			}
			$parent_status = $row2[1];
        }
		if($row3[47]=='ALL')
		  $row3[47]='Public';
		if($row3[47]==$row3[48])
		  $row3[47]='Private';
		$c_share=$row3[47];
        $que2="select sno from staffoppr_contact where status='ER' and csno='".$addr."'";
        $res2=mysql_query($que2,$db);
        while($row2=mysql_fetch_row($res2))
        {
            if(($_SESSION['insno1'.$Rnd])=="")
                $_SESSION['insno1'.$Rnd]=$row2[0];
            else
                $_SESSION['insno1'.$Rnd].=",".$row2[0];
        }

		$opp_que="select sno from staffoppr_oppr  where  csno='".$addr."' and oppr_status='ACTIVE'";
        $opp_res=mysql_query($opp_que,$db);
        while($data=mysql_fetch_row($opp_res))
        {
            if(($_SESSION['oppsno'.$Rnd])=="")
                $_SESSION['oppsno'.$Rnd]=$data[0];
            else
                $_SESSION['oppsno'.$Rnd].=",".$data[0];
        }
		$compRoles = $OBJ_Comp_Role->getEntityRoles($addr,'Company','comp');
    }

	if($bill_loc>0 && ($billcontact==0 || $billcontact==""))
	{
		$que2="select csno from staffoppr_location where sno='".$bill_loc."' and ltype in ('com','loc')";
		$res2=mysql_query($que2,$db);
		$row2=mysql_fetch_row($res2);
		$billcompany=$row2[0];
	}


	$_SESSION['oppsno'.$Rnd]=trim(implode(",",array_unique(explode(",",($_SESSION['oppsno'.$Rnd]) ) ) ) );

	if($_SESSION['oppsno'.$Rnd]!='')
	{
		$opp_que="select ".tzRetQueryStringDTime('mdate','DateTime','/').",name,stage,ammount,DATE_FORMAT(cdate,'%m/%d/%Y'),sno,notes,csno,probability,currency from staffoppr_oppr where csno='".$addr."' and sno in (".$_SESSION['oppsno'.$Rnd].") and oppr_status='ACTIVE'";
		$opp_res=mysql_query($opp_que,$db);
		$opp_rows=mysql_num_rows($opp_res);
	}

	$contacts="";

	$_SESSION['insno1'.$Rnd]=trim(implode(",",array_unique(explode(",",($_SESSION['insno1'.$Rnd]) ) ) ) );

	if(($_SESSION['insno1'.$Rnd])!="")
    {
	    $que2="select sno,prefix,nickname,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,csno,owner,status,dauser,dadate,stime,cat_id,bpsaid,accessto,ctype from staffoppr_contact where status='ER' and csno='".$addr."' and sno in (".($_SESSION['insno1'.$Rnd]).") and (FIND_IN_SET('".$username."',accessto)>0 or owner='".$username."' or accessto='ALL')";
        $res2=mysql_query($que2,$db);
        while($row2=mysql_fetch_row($res2))
        {
        	if($contacts=="")
        		$contacts="|||".$row2[2]."|".$row2[3]."|".$row2[4]."|".$row2[5]."|".$row2[6]."|".$row2[7]."|".$row2[8]."|".$row2[9]."|".$row2[10]."|".$row2[11]."|".$row2[12]."|".$row2[13]."|".$row2[0]."|".$row2[14]."|".$row2[22];
        	else
        		$contacts.="^|||".$row2[2]."|".$row2[3]."|".$row2[4]."|".$row2[5]."|".$row2[6]."|".$row2[7]."|".$row2[8]."|".$row2[9]."|".$row2[10]."|".$row2[11]."|".$row2[12]."|".$row2[13]."|".$row2[0]."|".$row2[14]."|".$row2[22];
        }
    }
    if($contacts!="")
    {
		$tok1=explode("^",$contacts);
		for($i=0;$i<count($tok1);$i++)
		{
			$fdata[$i]=explode("|",$tok1[$i]);
			$name1=html_tls_entities($fdata[$i][4])." ".html_tls_entities($fdata[$i][5])." ".html_tls_entities($fdata[$i][6]);
			$email1=html_tls_entities($fdata[$i][7]);
			$phone1=html_tls_entities($fdata[$i][8]);
			$ytitle1=html_tls_entities($fdata[$i][13]);

			$contacttype=getManage($fdata[$i][17]);

			$arr[$i]=$name1."|".$ytitle1."|".$contacttype."|".$phone1."|".$fdata[$i][15]."|".$fdata[$i][14]."|".$fdata[$i][16];
			$arry[$i]=explode("|",$arr[$i]);
		}
    }

	$spl_Attribute = (PAYROLL_PROCESS_BY_MADISON=='MADISON') ? 'udCheckNull ="YES" ' : '';
	
	/*=========Dynamic validation for Companies Starts==============*/
	$reqAliasArr = array('Company Name'=>'cname', 'Company Type'=>'ctype', 'HRM Department'=>'HRMDepartments','City' => 'city','State' => 'state','Main Phone'=>'phone','Address 1'=>'address1','Zip'=>'zip','Company Source'=>'compsource','Website'=>'curl');
	
	$getRequiredSql = "SELECT column_name, element_required, element_alias FROM udv_grid_columns WHERE custom_form_modules_id = 2 AND element_required = 'yes'";
	$getRequiredResult = mysql_query($getRequiredSql, $db);
	$userFieldsArr = array();
	$reqArr = array();
	while($getRequiredRow = mysql_fetch_assoc($getRequiredResult))
	{
		$userFieldsArr[] = $getRequiredRow[element_alias];
		$userFieldsAlias[$getRequiredRow[element_alias]] = $getRequiredRow[element_alias];
		if($reqAliasArr[$getRequiredRow[element_alias]] != '')
		{
			$reqArr[] = $reqAliasArr[$getRequiredRow[element_alias]];
		}	
	}
	$reqArrStr = implode(",", $reqArr);
	
	function getRequiredStar($field, $fieldsArr, $id=null)
	{
		$str = "";
		if(in_array($field, $fieldsArr))
		{
			if(PAYROLL_PROCESS_BY_MADISON=='MADISON' || TRICOM_REPORTS=='Y'){
				$mandatory_madison = "<font class=sfontstyle>&nbsp;*</font></span>";
			} else {
			$str = "<span id='udr_".$id."'><font class=sfontstyle>&nbsp;*</font></span>";
			}
		}
		return $str;
	}
	/*=================Validation Ends Here=========================*/
	
	
//Getting the burden details of the company
$get_comp_burden_data = getLocBurdenDetails($addr,'crm');
$comp_burden_data = implode("|",$get_comp_burden_data);
//Getting the Pay/Bill burden details with html form elements for selecting the pay/bill burden types
$comp_bt_details_str = getBurdenDetailsFormElements('crmcomp',$comp_burden_data);
$comp_bt_details_exp = explode("^^AKKENBTDETAILS^^",$comp_bt_details_str);
$pay_bt_str = $comp_bt_details_exp[0];
$bill_bt_str = $comp_bt_details_exp[1];

$oppr_con_query = "SELECT GROUP_CONCAT(contact_id) FROM oppr_contacts WHERE csno = '".$addr."' AND status = 'ACTIVE' ";
$oppr_con_res = mysql_query($oppr_con_query,$db);
$oppr_id = mysql_fetch_row($oppr_con_res);
?>

<html>
<head>
<title>Edit Company</title>
<meta content="IE=edge" http-equiv="X-UA-Compatible">
<meta content="text/html; charset=UTF-8" http-equiv="content-type">
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/crm-summary.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/candidatesInfo_tab.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/CandidatesCustomTab.css">
<link href="/BSOS/css/grid.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/filter.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/site.css" type=text/css rel=stylesheet>
<link href="/BSOS/css/crm-editscreen.css" type=text/css rel=stylesheet>

<?php getJQLibs(['jquery']); ?>
<script type="text/javascript" src="/BSOS/scripts/eraseSessionVars.js"></script>
<script src=/BSOS/scripts/tabpane.js></script>
<script language=javascript src="/BSOS/scripts/dynamicElementCreatefun.js"></script>
<script language=javascript src="/BSOS/scripts/commission_roles.js"></script>
<script language=javascript src=scripts/validatesup.js></script>
<script language=javascript src="/BSOS/scripts/validatecheck.js"></script>
<script language=javascript src="/BSOS/scripts/crmLocations.js"></script>
<script language=javascript src="scripts/validate_ajax.js"></script>
<script language="javascript" src="/BSOS/scripts/jQuery.js"></script>
<script type="text/javascript" src="/BSOS/scripts/jquery-1.8.3.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/shift_schedule/jquery.modalbox.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="/BSOS/css/sphinx_modalbox.css" />
<script language=javascript src="/BSOS/scripts/multiplerates.js"></script>
<script language=javascript src="/BSOS/scripts/manageCompanyRates.js"></script>
<script language=javascript src="/BSOS/scripts/billrate.js"></script>
<script language=javascript src="/BSOS/scripts/calMarginMarkuprates.js"></script>
<script>var madison='<?=PAYROLL_PROCESS_BY_MADISON;?>';
var tricom_rep='<?=TRICOM_REPORTS;?>';</script>
<script type="text/javascript">
function appContactsComp(id,ContactName,Title,ContactType,PhoneNumber){
	try
	{
		var tableId = document.getElementById("compContList");

		var tabletr = document.createElement("tr");
		tabletr.setAttribute("class", "panel-table-content-new");
		tabletr.setAttribute("valign", "top");

		var company_sno = $("#addr").val();

		var tabletd1 = document.createElement("td");
		var hyperlink0 = document.createElement("a");
		var font0 = document.createElement("font");
		var conname = document.createTextNode(ContactName);
		hyperlink0.setAttribute("href","javascript:editCon('"+id+"','"+company_sno+"')");
		font0.setAttribute("class","linkrow");
		font0.appendChild(conname);
		hyperlink0.appendChild(font0);
		tabletd1.appendChild(hyperlink0);

		var tabletd2 = document.createElement("td");
		var font1 = document.createElement("font");
		var contitle = document.createTextNode(Title);
		font1.setAttribute("class","summarytext");
		font1.appendChild(contitle);
		tabletd2.appendChild(font1);

		var tabletd3 = document.createElement("td");
		var font2 = document.createElement("font");
		var contype = document.createTextNode(ContactType);
		font2.setAttribute("class","summarytext");
		font2.appendChild(contype);
		tabletd3.appendChild(font2);

		var tabletd4 = document.createElement("td");
		var font3 = document.createElement("font");
		var conphone = document.createTextNode(PhoneNumber);
		font3.setAttribute("class","summarytext");
		font3.appendChild(conphone);
		tabletd4.appendChild(font3);


		var tabletd5 = document.createElement("td");
		var hyperlink1 = document.createElement("a");
		var font4 = document.createElement("font");
		var del = document.createTextNode("Delete");

		hyperlink1.setAttribute("href","javascript:delCon('"+id+"','"+company_sno+"','1')");
		font4.setAttribute("class","linkrow");
		font4.appendChild(del);
		hyperlink1.appendChild(font4);
		tabletd5.appendChild(hyperlink1);

		tabletr.appendChild(tabletd1);
		tabletr.appendChild(tabletd2);
		tabletr.appendChild(tabletd3);
		tabletr.appendChild(tabletd4);
		tabletr.appendChild(tabletd5);

		tableId.appendChild(tabletr);
		console.log(tableId);
	}
	catch(e)
	{
		console.log(e);
	}

}	
</script>
<?php
if(PAYROLL_PROCESS_BY_MADISON=='MADISON' || TRICOM_REPORTS=='Y')
	echo "<script language=javascript src=/BSOS/scripts/formValidation.js></script>";
?>
<style>
.managesymb{ margin-top:3px; }
.closebtnstyle{ float:left; *margin-top:3px; vertical-align:middle; }
</style>
<style>
.timegrid{ width:2.06% !important; display:block; overflow:hidden}
#multipleRatesTab table tr td{padding:3px 8px 2px 10px; white-space:nowrap;}
#multipleRatesTab table tr td span select{margin-left:14px }
#multipleRatesTab .crmsummary-jocomp-table .disabled_user_input_field{
      background:rgb(235, 235, 228) !important;
}
#multipleRatesTab table tr td:first-child, #multipleRatesShiftsTab table tr td:first-child{ width:20% !important;}

#multipleRatesShiftsTab table tr td{padding:3px 8px 2px 10px; white-space:nowrap;}
#multipleRatesShiftsTab table tr td span select{margin-left:14px }
#multipleRatesShiftsTab .crmsummary-jocomp-table .disabled_user_input_field{
      background:rgb(235, 235, 228) !important;
}

panel-table-content-new .summaryform-bold-title { font-size:12px; font-weight:bold;}

#modal-wrapper
{
	<?php
		if(SHIFT_SCHEDULING_ENABLED=="Y")
		{
			/* echo "height: 570px; margin-left: -252px; margin-top: -285px !important;width: 504px;";//380 */
		}
		else
		{
			/* echo "height: 240px; margin-left: -252px; margin-top: -120px;width: 504px;"; */
		}
	?>
	
}
#modal-glow{ width:100%; position:fixed;}

@media screen\0 {	
.summaryform-formelement{ font-size:12px !important; }
a.crm-select-link:link{ font-size:12px !important; }
a.edit-list:link{ font-size:12px !important;}
.summaryform-bold-close-title{ font-size:12px !important;}
.center-body { text-align:left !important;}
.crmsummary-jocomp-table td{ font-size:12px !important ; text-align:left !important;}
.summaryform-nonboldsub-title{ font-size:12px}
#smdatetable{ font-size:12px !important;}
.summaryform-formelement{ text-align:left !important; vertical-align:middle}
.crmsummary-content-title{ text-align:left !important}
.crmsummary-edit-table td{ text-align:left !important}
.summaryform-bold-title{ font-size:12px !important;}
.summaryform-nonboldsub-title{ font-size:12px !important;}


}
#attributeSearch{
	<?php
		if(SHIFT_SCHEDULING_ENABLED=="Y")
		{
			echo "height:565px !important;";
		}
		else
		{
			echo "height:237px !important;";
		}
	?>
}	
@media screen and (-webkit-min-device-pixel-ratio:0) {
    /* Safari only override */
    .custom_rate_remove_button img{ top:13px;}
      #attributeSearch{
	<?php
		if(SHIFT_SCHEDULING_ENABLED=="Y")
		{
			echo "height:555px !important;";
		}
		else
		{
			echo "height:227px !important;";
		}
	?>
    }
}
@media all and (-ms-high-contrast: none), (-ms-high-contrast: active) {
    #attributeSearch{
	<?php
	if(SHIFT_SCHEDULING_ENABLED=="Y")
	{
		echo "height:555px !important;";
	}
	else
	{
		echo "height:231px !important;";
	}
	?>
    }
}
.ssremovepad table tr td{ pad ding-left:0px; pa dding-right:0px}
a.crm-select-linkNewBg:hover{color:#3AB0FF}
.custom_defined_main_table td{text-align: left !important; vertical-align: middle;}
.innertbltd-custom table td {vertical-align: middle !important;}
.custom_defined_head_td{width: 120px !important; padding:0px !important;}
.managesymb{ margin-top:2px; }
.closebtnstyle{ float:left; *margin-top:3px; vertical-align:middle; margin-top:2px; }

.modal-wrapperNew
{
	/* top:50% !important;
	left:50% !important;
	margin-top:-111px !important;
	margin-left:-250px !important; */
	
}
@media all and (-ms-high-contrast: none), (-ms-high-contrast: active) {
    #attributeSearch{
	<?php
		if(SHIFT_SCHEDULING_ENABLED=="Y")
		{
			echo "height:565px !important;";
		}
		else
		{
			echo "height:231px !important;";
		}
	?>
        
    }
}

.cdfCustTextArea {
    width: 392px !important;
}
/* .crmsummary-jocomp-table select[name="bill_loc"], .crmsummary-jocomp-table select[name="bill_cons"], .crmsummary-jocomp-table select[name="billreq"], .crmsummary-jocomp-table select[name="burdenType"], .crmsummary-jocomp-table textarea[name="servterms"]{ width:250px !important; padding:6px 4px; border:solid 1px #ccc; border-radius:4px;} */
.crmsummary-jocomp-table select[name="bill_cons"]{ width:180px !important}

</style>
</head>

<body class="center-body"   <?  if($page11[55] == 'yes'){ ?>onLoad="class_Toggle(mntcmnt1,'DisplayBlock','DisplayNone','1','compHierarchy')" <? }?>>
<form action=navapp.php method=post name='markreqman' id='markreqman'>
<input type=hidden name="userreq" id="userreq" value="<?php echo $reqArrStr;?>">
<input type=hidden name=Rnd value="<?php echo $Rnd; ?>">
<input type=hidden name=addr id=addr value="<?php echo $addr;?>">
<input type=hidden name=companyinfo value="">
<input type=hidden name="selratesdata" value="" id="selratesdata">
<input type="hidden" id="cap_separated_custom_shift_rates" name="cap_separated_custom_shift_rates" value="" />
<input type="hidden" id="cap_separated_custom_shift_rateids" name="cap_separated_custom_shift_rateids" value="" />
<input type=hidden name=comFrom value="">
<input type=hidden name="compUdfVal" id="compUdfVal" value=''>
<input type='hidden' name='dynamicUdfCol'  value="<?php echo $colNames; ?> "/>
<input type=hidden name=opprinfo value="">
<input type=hidden name='aa' id='aa' value="">
<input type=hidden name=prate value="">
<input type=hidden name=parking value="">
<input type=hidden name=billcomp value="">
<input type=hidden name=billcont value="">
<input type=hidden name=parentcomp value="">
<input type=hidden name=mainuser value="<?php echo $username;?>">
<input type=hidden name='owner' id='owner' value="" />
<input type=hidden name=insno value="<?php echo $_SESSION['insno1'.$Rnd]; ?>">
<input type=hidden name='addr1' id='addr1' value="" />
<input type=hidden name=accto value="<?php echo html_tls_entities($accto); ?>">
<input type=hidden name=Row value="<?php echo html_tls_entities($Row)?>">
<input type=hidden name=existlist value="<?php echo html_tls_entities($existlist)?>">
<input type="hidden" name="candrn" id="candrn" value="<?php echo html_tls_entities($candrn); ?>">
<input type=hidden name=newcust value="<?php echo html_tls_entities($newcust); ?>">
<input type=hidden name=emplist value="<?php echo html_tls_entities($emplist);?>">
<input type=hidden name=ownerVal value="<? echo html_tls_entities($row[1]);?>">
<input type=hidden name=shareVal  value="<? echo  html_tls_entities($comp_Share_Type);?>">
<input type=hidden name=changeowner>
<input type=hidden name=chk_comp value="<?php echo html_tls_entities($chk_comp);?>">
<input type="hidden" name="typecomp">
<input type="hidden" name="contSno">
<input type=hidden name=comp_stat  value="<? echo html_tls_entities($comp_stat)?>">
<input type="hidden" name="ownershare">

<input type=hidden name=pass_newvar  value="<? echo dispfdb(trim(trim($pass_newvar,".")));?>">
<input type=hidden name=list_cont  value="<? echo html_tls_specialchars(addslashes($list_cont));?>">
<input type=hidden name=list_cont_sno  value="<? echo html_tls_entities($list_cont_sno)?>">
<input type=hidden name=com_status_field  value="<? echo html_tls_entities($comp_row[5])?>">
<input type=hidden name=pass_cname  value="<? echo html_tls_entities($pass_cname)?>">

<input type=hidden name=par_stat  value="<? echo html_tls_entities($par_stat)?>">
<input type=hidden name=status_parent  value="<? echo html_tls_entities($status_parent)?>">
<input type=hidden name=status_parent  value="<? echo html_tls_entities($status_parent)?>">
<input type="hidden" name="mode_type" id="mode_type" value="company">
<input type=hidden name=pass_sel  value="<? echo html_tls_entities($pass_sel)?>">
<input type="hidden" name="companyMode" id="companyMode" value="Edit">
<input type="hidden" name="page_from" id="page_from" value="crmcompany" />	
<input type="hidden" name="oppr_con_ids" id="oppr_con_ids" value="<?php echo $oppr_id[0];?>" />
<input type="hidden" name="module" id="module" value="<?php echo $module;?>" />

<div id="main">
<td valign=top align=center>
<table width=99% cellpadding=0 cellspacing=0 border=0>
	<div id="content">
	<tr>
	<td>
		<table width=100% cellpadding=0 cellspacing=0 border=0>
    		<tr>
    			<td colspan=2><font class=modcaption>&nbsp;&nbsp;<?php echo stripslashes($com_name);?></font></td>
    		</tr>
    		<tr>
    			<td colspan=2><font class=bstrip>&nbsp;</font></td>
    		</tr>
		</table>
	</td>
	</tr>
	</div>


	<div id="grid_form">
	<tr>
	<td>

	<table border="0" width="100%" cellspacing="5" cellpadding="0" bgcolor="white">
	<tr>
		<td width=100% valign=top align=center>
		<div class="tab-pane" id="tabPane1">
		<script type="text/javascript">tp1 = new WebFXTabPane( document.getElementById( "tabPane1" ) );</script>
			<div class="tab-page" id="tabPage11">
			<h2 class="tab">Summary</h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage11" ),"companySummary.php?addr=<?php echo $addr?>&Row=<?php echo $Row?>&Rnd=<?=$Rnd?>&candrn=<?=$candrn?>&newcust=<?=$newcust?>&comp_stat=<? echo $comp_stat?>&par_stat=<?=$par_stat?>&chkAuth=<?=$_GET['chkAuth']?>&module=<?=$module?>");</script>
			</div>
			<div class="tab-page" id="tabPage12">
			<h2 class="tab">Edit</h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage12" ) );</script>
			<?php require("edittable.php");?>
			</div>
   		</div>
		<script>
		tp1.setSelectedIndex(1);
		//Load the burden items string when the page loads
		getLocPayBurdenString(document.getElementById('burdenType'));// To load bydefault burden type string
		getLocBillBurdenString(document.getElementById('bill_burdenType'));// To load bydefault burden type string
		</script>
		</td>
	</tr>
	</table>
	</td>
	</tr>
	</div>
</tr>
</table>
</div>
</form>
<input type="hidden" id="payrate_calculate_confirmation" value="" />
<input type="hidden" id="billrate_calculate_confirmation" value="" />
<input type="hidden" id="cap_separated_custom_rates" value="" />
<input type="hidden" id="payrate_calculate_confirmation_window_onblur" value="" />
<input type="hidden" id="billrate_calculate_confirmation_window_onblur" value="" />
<input type=hidden name="confirmstate" id="confirmstate" value="edit">	
</body>
</html>
<script>
$(window).focus(function() {
    if(document.getElementById('payrate_calculate_confirmation_window_onblur').value != ""){
        document.getElementById('payrate_calculate_confirmation').value = document.getElementById('payrate_calculate_confirmation_window_onblur').value;
    }
    if(document.getElementById('billrate_calculate_confirmation_window_onblur').value != ""){
        document.getElementById('billrate_calculate_confirmation').value = document.getElementById('billrate_calculate_confirmation_window_onblur').value;
    }
});
</script>