<?php
  	require("global.inc");
	require("Menu.inc");
	require("functions.php");
	require($akken_psos_include_path.'commonfuns.inc');
	
	require("displayoptions.php");
	$objMRT = new ManageRateTypes();
	
	$menu=new EmpMenu();

	if($new_divs == 'yes')
	{
	   $test_divs = $Divisions;
	}
	
	$OBJ_Comp_Role = new CommissionRoles($db, $username, 'comp', 'CRMCompany');
	
	// Fetching Dynamic UdfColNames 
	$sql = "select t1.id, element_name, element_lable, t1.element, required, default_opt, auto_complete
			 from udf_form_details t1 left join udf_form_details_order t4 on t4.custom_form_details_id = t1.id
			  where t1.module  = 2 and t1.status = 'Active' order by t4.ele_order asc";

	 $result = mysql_query($sql);
	if(mysql_num_rows($result) > 0)
	{
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
	 
	if($cfrm=="new")
	{
		session_unregister("edit_company".$Rnd);
		session_unregister("insno1".$Rnd);
		session_unregister("oppsno".$Rnd);
		session_unregister("Divisions");		
		session_unregister("comppageudf".$Rnd);
		session_unregister("comp_rates_details".$Rnd);
		$Divisions = '';
		$cfrm="";
	}

	function sele($a,$b)
	{
		if($a==$b)
			return "selected";
		else
			return "";
	}
	if($_SESSION['edit_company'.$Rnd]!='')
	{ 
		$page11=explode("|",$_SESSION['edit_company'.$Rnd]);
		
		$ceo=$page11[15];
		$com_name=$page11[0];
		$com_url=$page11[1];
		$com_addr1=$page11[2];
		$com_addr2=$page11[3];
		$com_city=$page11[4];
		$com_state=$page11[5];
		$com_country=$page11[6];
		$com_zip=$page11[7];

		$cfo=$page11[16];
		$industry=$page11[15];
		$com_keywords=$page11[17];
		$com_keytechs=$page11[20];
		$com_compnotes=$page11[22];
		$com_type=$page11[8];
		$com_size=$page11[9];
		$no_com_loc=$page11[10];
		$no_years=$page11[11];
		$no_emps=$page11[12];
		$com_rev=$page11[13];
		$fed_id=$page11[14];
		$phone=$page11[18];
		$fax=$page11[19];

		$sicCode=$page11[23];
		$tickerSymbol=$page11[24];
		$comp_Source=$page11[25];
		$department=$page11[26];
		$parentNo=$page11[27];
		$com_compnotes=$page11[28];
		$s_status=$page11[30];
		$c_owner=$page11[31];
		$c_share=$page11[32];
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
		$alternate_id=$page11[51];
		$phone_extn=$page11[52];
		$HRMDepartments=$page11[55];

		if($billcontact!=0)
		{
			$que2="select TRIM(CONCAT_WS('',fname,' ',lname,'(',IF(email='',nickname,email),')')),status,fname,mname,lname,csno from staffoppr_contact where sno='".$billcontact."'";
			$res2=mysql_query($que2,$db);
			$row2=mysql_fetch_row($res2);
			$billcont = $row2[2]." ".$row2[3]." ".$row2[4];
			$billcont_stat=$row2[1];
			$billcompany=$row2[5];
		}


		if($bill_loc!='' && ($billcontact==0 || $billcontact==""))
		{
			if(is_numeric($bill_loc)){
				$billloc	=	$bill_loc;				
			}else{
				$billlocArr	=	explode('-',$bill_loc);
				$billloc	=	$billlocArr[1];
			}
			$que2="select csno from staffoppr_location where sno='".$billloc."' and ltype in ('com','loc')";
			$res2=mysql_query($que2,$db);
			$row2=mysql_fetch_row($res2);
			$billcompany=$row2[0];
		}

		if($parentNo!=0)
		{
			$que2="select cname,status,address1,address2,city,state from staffoppr_cinfo where sno='".$parentNo."'";
			$res2=mysql_query($que2,$db);
			$row2=mysql_fetch_row($res2);
			$parentName = $row2[0];
			$parent_addr = '';
			if($row2[2] != '')
			  $parent_addr .= $row2[2];
			if($row2[3] != '')
			  $parent_addr .= " ".$row2[3];
			if($row2[4] != '')
			  $parent_addr .= " ".$row2[4];
			if($row2[5] != '')
			  $parent_addr .= " ".$row2[5];
			$parent_status=$row2[1];
		}

		if($page11[28]=="addcompany" || $page11[28]=="addcontact")
		{
			$insno=$page11[29];
			$oppsno=$page11[47];
		}
		else if($page11[26]=="addcompany" || $page11[26]=="addcontact")
		{
			$insno=$page11[27];
			$oppsno=$page11[28];
		}
		else
		{
			$insno=$page11[28];
			$oppsno=$page11[29];
		}

		if($page11[29]=="addcompany" || $page11[29]=="addcontact")
		{
			$insno=$page11[56];
			$oppsno=$page11[57];
		}
		else if($page11[27]=="addcompany" || $page11[27]=="addcontact")
		{
			$insno=$page11[28];
			$oppsno=$page11[29];
		}
		else if($page11[26]=="addcompany_edit_add")
		{
			$insno=$page11[56];
			$oppsno=$page11[29];//neeed to test
		}
		else
		{
			$insno=$page11[29];
			$oppsno=$page11[47];
		}
	
		if($insno!=0)
		{
			if($_SESSION['insno1'.$Rnd]=="")
				$_SESSION['insno1'.$Rnd]=$insno;
			else
				$_SESSION['insno1'.$Rnd].=",".$insno;
		}
		if($contactsList!="" && $contactsList!=0)
		{
			$_SESSION['insno1'.$Rnd]=$contactsList.",".$_SESSION['insno1'.$Rnd];
		}

		$_SESSION['insno1'.$Rnd]= trim($_SESSION['insno1'.$Rnd], ",");
	
		$contacts="";

		$_SESSION['oppsno'.$Rnd]=trim($oppsno, ",");

		if($_SESSION['oppsno'.$Rnd]!='')
		{
			 $opp_que="select sno,otype,lead,stage,ammount,notes,csno,name  from staffoppr_oppr where csno='0' and sno in (".$_SESSION['oppsno'.$Rnd].") and oppr_status='ACTIVE'";
			$opp_res=mysql_query($opp_que,$db);
			$opp_rows=mysql_num_rows($opp_res);
		}

		if($_SESSION['insno1'.$Rnd]!="")
		{
			$que2="select sno,suffix,nickname,fname,mname,lname,email,wphone,hphone,mobile,fax,other,ytitle,csno,approveuser,status,dauser,dadate,stime,cat_id,bpsaid,accessto,ctype from staffoppr_contact where status='ER' and csno='0' and sno in (".$_SESSION['insno1'.$Rnd].")";
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
					$name1=html_tls_entities($fdata[$i][4],ENT_QUOTES)." ".html_tls_entities($fdata[$i][5],ENT_QUOTES)." ".html_tls_entities($fdata[$i][6],ENT_QUOTES);
					$email1=html_tls_entities($fdata[$i][7],ENT_QUOTES);
					$phone1=html_tls_entities($fdata[$i][8],ENT_QUOTES);
					$ytitle1=html_tls_entities($fdata[$i][13],ENT_QUOTES);
	
					$contacttype=getManage($fdata[$i][17]);
					$arr[$i]=$name1."|".$ytitle1."|".$contacttype."|".$phone1."|".$fdata[$i][15]."|".$fdata[$i][14]."|".$fdata[$i][16];
					$arry[$i]=explode("|",$arr[$i]);
				}
		}
		
		$compRoles = $_SESSION['compRole'.$Rnd];
	}
	else
	{
		$c_owner=$username;
		$c_share="public";
		$compRoles = "";
	}

	//This loop comes when we add a former company from Contacts Summary
 	if($frmcontsum==true)
 	{
		session_unregister('CRM_FormerComp_Cnt');
		session_unregister('formercmp_id');

		$comp_que="select cname from staffoppr_cinfo where staffoppr_cinfo.sno=".$cmp_id;
		$comp_res=mysql_query($comp_que,$db);
		$comp_row=mysql_fetch_row($comp_res);
		$com_name=$comp_row[0];
	}

	$Users_Array=array();
	$Users_Sql="select us.username,us.name,su.crm from users us LEFT JOIN sysuser su ON (us.username = su.username AND su.crm!='NO') WHERE us.status != 'DA' AND us.type in ('sp','PE','consultant') AND us.name!='' ORDER BY us.name";
	$Users_Res=mysql_query($Users_Sql,$db);
	while($Users_Data=mysql_fetch_row($Users_Res))
		$Users_Array[$Users_Data[0]]=$Users_Data[1];

	$User_nos=implode(",",array_keys($Users_Array));
	$uersCnt=count($Users_Array);

	//Session to track the former companies when comming from Lead_Mngmt/contactSummary.php

	//get former company id

	$formercmp_id = $formercmp_id;
	
	
	 /* TLS-01202018 */
	if(isset($_SESSION["CRM_FormerComp_Cnt"]))
	{
		$CRM_FormerComp_Cnt = $CRM_FormerComp_Cnt;
	}
	else
	{
		$CRM_FormerComp_Cnt = $cnt;
	}
	session_update("formercmp_id");
	session_update("CRM_FormerComp_Cnt");


	if(strpos($_SERVER['HTTP_USER_AGENT'],'Gecko') > -1)
		$rightflt = "style='width:35px;'";
	
    if(TRICOM_REPORTS=='Y'){
	$spl_Attribute = (TRICOM_REPORTS=='Y') ? 'udCheckNull ="YES" ' : '';	
	}else{
	$spl_Attribute = (PAYROLL_PROCESS_BY_MADISON=='MADISON') ? 'udCheckNull ="YES" ' : '';
	}
	
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
	
	//Getting the Pay/Bill burden details with html form elements for selecting the pay/bill burden types
	$comp_bt_details_str = getBurdenDetailsFormElements('crmcomp','');
	$comp_bt_details_exp = explode("^^AKKENBTDETAILS^^",$comp_bt_details_str);
	$pay_bt_str = $comp_bt_details_exp[0];
	$bill_bt_str = $comp_bt_details_exp[1];
		
?>
<?php include('header.inc.php') ?>
<title>Add Company</title>
<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/candidatesInfo_tab.css">
<link href="/BSOS/css/grid.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/filter.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/site.css" type=text/css rel=stylesheet>
<link href="/BSOS/css/crm-editscreen.css" type=text/css rel=stylesheet>
<link type="text/css" rel="stylesheet" href="/BSOS/css/crm-summary.css">
<script type="text/javascript" language="javascript" src=/BSOS/scripts/tabpane.js></script>
<script type="text/javascript" language="javascript" src="scripts/validatesup.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/validatecheck.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/crmLocations.js"></script>
<script type="text/javascript" language="javascript" src="scripts/validate_ajax.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/dynamicElementCreatefun.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/commission_roles.js"></script>
<?php getJQLibs(['jquery']); ?>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/shift_schedule/jquery.modalbox.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/multiplerates.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/manageCompanyRates.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/billrate.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/calMarginMarkuprates.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="/BSOS/css/sphinx_modalbox.css" />
<script type="text/javascript" language="javascript">var madison='<?=PAYROLL_PROCESS_BY_MADISON;?>'
var tricom_rep='<?=TRICOM_REPORTS;?>';
</script>
<style type="text/css">
	.not-active {
 		pointer-events: none;
 		cursor: default;
		}
</style>
<script type="text/javascript">
function appContactsComp(id,ContactName,Title,ContactType,PhoneNumber){
	try
	{
		
		var classexist = $("#compContList").find("tr").hasClass("hthbgcolor");
		
		var company_sno = $("#addr").val();
		if(company_sno=="")
		{
			company_sno=0;
		}

		if(!classexist)
		{	
			$("#mainContListTable").find("tbody").remove();


			var maintableId = document.getElementById("mainContListTable");
			var createTable = document.createElement("table");
			createTable.setAttribute("width","100%");
			createTable.setAttribute("border","0");
			createTable.setAttribute("cellpadding","2");
			createTable.setAttribute("cellspacing","0");
			

			var tbody = document.createElement("tbody");
			tbody.setAttribute("id","compContList");

			var tabletrh = document.createElement("tr");
			tabletrh.setAttribute("class", "hthbgcolor");

			
			var tabletdh0 = document.createElement("td");
			var thfont0 = document.createElement("font");
			var thconname = document.createTextNode("Contact Name");
			tabletdh0.setAttribute("width","25%");
			thfont0.setAttribute("class","afontstyle");
			thfont0.appendChild(thconname);
			tabletdh0.appendChild(thfont0);

			var tabletdh1 = document.createElement("td");
			var thfont1 = document.createElement("font");
			var thcontitle = document.createTextNode("Title");
			tabletdh1.setAttribute("width","25%");
			thfont1.setAttribute("class","afontstyle");
			thfont1.appendChild(thcontitle);
			tabletdh1.appendChild(thfont1);

			var tabletdh2 = document.createElement("td");
			var thfont2 = document.createElement("font");
			var thcontype = document.createTextNode("Contact Type");
			tabletdh2.setAttribute("width","10%");
			thfont2.setAttribute("class","afontstyle");
			thfont2.appendChild(thcontype);
			tabletdh2.appendChild(thfont2);

			var tabletdh3 = document.createElement("td");
			var thfont3 = document.createElement("font");
			var thconphone= document.createTextNode("Phone Number");
			tabletdh3.setAttribute("width","20%");
			thfont3.setAttribute("class","afontstyle");
			thfont3.appendChild(thconphone);
			tabletdh3.appendChild(thfont3);

			var tabletdh4 = document.createElement("td");
			var thfont4 = document.createElement("font");
			tabletdh4.setAttribute("width","10%");
			tabletdh4.appendChild(thfont4);

			var tabletdh5 = document.createElement("td");
			var thfont5 = document.createElement("font");
			tabletdh5.setAttribute("width","10%");
			tabletdh5.appendChild(thfont5);

			var tabletr = document.createElement("tr");
			tabletr.setAttribute("class", "panel-table-content-new");

			
			var tabletd1 = document.createElement("td");
			var font0 = document.createElement("font");
			var conname = document.createTextNode(ContactName);
			font0.setAttribute("class","summarytext");
			font0.appendChild(conname);
			tabletd1.appendChild(font0);

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
			var edit = document.createTextNode("Edit");

			hyperlink1.setAttribute("href","javascript:editCon('"+id+"','"+company_sno+"')");
			hyperlink1.setAttribute("class","not-active");
			font4.setAttribute("class","linkrow");
			font4.appendChild(edit);
			hyperlink1.appendChild(font4);
			tabletd5.appendChild(hyperlink1);

			var tabletd6 = document.createElement("td");
			var hyperlink2 = document.createElement("a");
			var font5 = document.createElement("font");
			var del = document.createTextNode("Delete");

			hyperlink2.setAttribute("href","javascript:delCon('"+id+"','"+company_sno+"','1')");
			hyperlink2.setAttribute("class","not-active");
			font5.setAttribute("class","linkrow");
			font5.appendChild(del);
			hyperlink2.appendChild(font5);
			tabletd6.appendChild(hyperlink2);

			tabletrh.appendChild(tabletdh0);
			tabletrh.appendChild(tabletdh1);
			tabletrh.appendChild(tabletdh2);
			tabletrh.appendChild(tabletdh3);
			tabletrh.appendChild(tabletdh4);
			tabletrh.appendChild(tabletdh5);
			tabletr.appendChild(tabletd1);
			tabletr.appendChild(tabletd2);
			tabletr.appendChild(tabletd3);
			tabletr.appendChild(tabletd4);
			tabletr.appendChild(tabletd5);
			tabletr.appendChild(tabletd6);

			
			tbody.appendChild(tabletrh);
			tbody.appendChild(tabletr);
			createTable.appendChild(tbody);

			maintableId.appendChild(createTable);
		}	
		else{

			var tableId = document.getElementById("compContList");
			var tabletr = document.createElement("tr");
			tabletr.setAttribute("class", "panel-table-content-new");

			var tabletd1 = document.createElement("td");
			var font0 = document.createElement("font");
			var conname = document.createTextNode(ContactName);
			font0.setAttribute("class","summarytext");
			font0.appendChild(conname);
			tabletd1.appendChild(font0);

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
			var edit = document.createTextNode("Edit");

			hyperlink1.setAttribute("href","javascript:editCon('"+id+"','"+company_sno+"')");
			hyperlink1.setAttribute("class","not-active");
			font4.setAttribute("class","linkrow");
			font4.appendChild(edit);
			hyperlink1.appendChild(font4);
			tabletd5.appendChild(hyperlink1);

			var tabletd6 = document.createElement("td");
			var hyperlink2 = document.createElement("a");
			var font5 = document.createElement("font");
			var del = document.createTextNode("Delete");

			hyperlink2.setAttribute("href","javascript:delCon('"+id+"','"+company_sno+"','1')");
			hyperlink2.setAttribute("class","not-active");
			font5.setAttribute("class","linkrow");
			font5.appendChild(del);
			hyperlink2.appendChild(font5);
			tabletd6.appendChild(hyperlink2);


			tabletr.appendChild(tabletd1);
			tabletr.appendChild(tabletd2);
			tabletr.appendChild(tabletd3);
			tabletr.appendChild(tabletd4);
			tabletr.appendChild(tabletd5);
			tabletr.appendChild(tabletd6);

			tableId.appendChild(tabletr);

			}


		
	}
	catch(e)
	{
		console.log(e);
	}

}	
</script>
<?php
if(PAYROLL_PROCESS_BY_MADISON=='MADISON' || TRICOM_REPORTS=='Y')
	echo "<script type='text/javascript' language=javascript src=/BSOS/scripts/formValidation.js></script>";
?>
<style>
.timegrid{ width:2.06% !important; display:block; overflow:hidden}
#multipleRatesTab table tr td{padding:3px 8px 2px 10px; white-space:nowrap;}
#multipleRatesTab table tr td span select{margin-left:14px }
#multipleRatesShiftsTab table tr td{padding:3px 8px 2px 10px; white-space:nowrap;}
#multipleRatesShiftsTab table tr td span select{margin-left:14px }
.summarytext input[name="zip"]{width: 150px !important;min-width: 150px !important;}
.summarytext input[name="phone_extn"]{width: 35px !important;min-width: 35px !important;}
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
.ssremovepad table tr td{ pad ding-left:0px; pa dding-right:0px}
a.crm-select-linkNewBg:hover{color:#3AB0FF}
.custom_defined_main_table td{text-align: left !important; vertical-align: middle;}
.innertbltd-custom table td {vertical-align: middle !important;}
.custom_defined_head_td{width: 120px !important; padding:0px !important;}
.managesymb{ margin-top:2px; }
.closebtnstyle{ float:left; *margin-top:3px; vertical-align:middle; margin-top:2px; }
/* .modal-wrapperNew
{
	top:50% !important;
	left:50% !important;
	margin-top:-111px !important;
	margin-left:-250px !important;
	
} */

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

.cdfCustTextArea {
    width: 444px !important;
}
.dynamic-tab-pane-control.tab-pane input:focus, .dynamic-tab-pane-control.tab-pane select:focus{border: 1px solid #3eb8f0;box-shadow: 0 0 3px 0 #3eb8f0;}
</style>

</head>
<body class="center-body" <? if($page11[52] == 'yes'){ ?> onLoad ="class_Toggle(mntcmnt1,'DisplayBlock','DisplayNone',1,'compHierarchy')" <? }?>>
<form method=post name=markreqman id="markreqman">
<input type=hidden name="userreq" id="userreq" value="<?php echo $reqArrStr;?>">
<input type=hidden name=Rnd value="<?php echo $Rnd; ?>">
<input type=hidden name=companyinfo value="">
<input type=hidden name="compUdfVal" id="compUdfVal" value=''>
<input type='hidden' name='dynamicUdfCol'  value="<?php echo $colNames; ?> "/>   
<input type=hidden name=opprinfo value="">
<input type=hidden name=aa value="">
<input type=hidden name=prate value="">
<input type=hidden name=parking value="">
<input type=hidden name=billcomp value="">
<input type=hidden name=billcont value="">
<input type=hidden name=parentcomp value="">
<input type=hidden name=insno value="<?php echo $insno1; ?>">
<input type=hidden name=mainuser value="<?php echo $username;?>">
<input type=hidden name=owner>
<input type=hidden name=addr id="addr" value="<?php echo $addr;?>">
<input type=hidden name=addr1>
<input type=hidden name=frm value="<?php echo $frm;?>">
<input type=hidden name=cfrm value="<?php echo $cfrm;?>">
<input type=hidden name=mod value="<?php echo $mname;?>">
<input type=hidden name=newcust value="<? echo $newcust;?>">
<input type=hidden name=frmcontsum value="<?php echo $frmcontsum;?>">
<input type=hidden name=cmp_id value="<?php echo $formercmp_id;?>">
<input type=hidden name=comp_sum value="<?php echo $comp_sum;?>">
<input type=hidden name=con_compid value="<?php echo $con_compid;?>">
<input type=hidden name=cnt value="<?php echo $CRM_FormerComp_Cnt;?>">
<input type=hidden name=emplist value="<?php echo $emplist;?>">
<input type=hidden name=chk_comp value="<?php echo $chk_comp;?>">
<!--used when coming from billing company in job orders-->
<input type=hidden name=DIVID value="<?=$DIVID?>">
<input type=hidden name=ownerVal value="<? echo $c_owner?>">
<input type=hidden name=shareVal  value="<? echo  $c_share?>">
<input type=hidden name=typecomp value="<?php if($typecomp=="") echo $DIVID; else echo $typecomp;?>">
<input type=hidden name=contSno value="<?php echo $contSno;?>">
<input type=hidden name=new_par value="<?php echo $new_par;?>">
<input type=hidden name=new_divs value="<?php echo $new_divs;?>">
<input type=hidden name=test_divs value="<?php echo $test_divs;?>">
<input type=hidden name=edit_divs value="<?php echo $edit_divs;?>">
<input type=hidden name=div_addr value="<?php echo $div_addr;?>">
<input type=hidden name=jocomp_par value="<?php echo $jocomp_par;?>">
<input type=hidden name=joloc_par value="<?php echo $joloc_par;?>">

<input type=hidden name=jocomp_divs value="<?php echo $jocomp_divs;?>">
<input type=hidden name=Compsno value="<?php echo $Compsno;?>">
<input type=hidden name=CmngFrom value="<?php echo $CmngFrom;?>">
<input type=hidden name=joloc_divs value="<?php echo $joloc_divs;?>">
<input type="hidden" name="companyMode" id="companyMode" value="New">
<input type="hidden" name="mode_type" id="mode_type" value="company">
<input type="hidden" name="selratesdata" id="selratesdata" value="">
<input type="hidden" name="cap_separated_custom_shift_rates" id="cap_separated_custom_shift_rates" value="">
<input type="hidden" name="cap_separated_custom_shift_rateids" id="cap_separated_custom_shift_rateids" value="">
<input type=hidden name=comFrom value="">
<input type="hidden" name="page_from" id="page_from" value="crmcompany" />	

<div id="grid_form">
	<table class="ProfileNewUI" align="center">
	<tr>
	  <td width=100% valign=top align=left>
		<div class="tab-pane" id="tabPane1">
		<script type="text/javascript" language="javascript">tp1 = new WebFXTabPane( document.getElementById( "tabPane1" ) );</script>
			<div class="tab-page" id="tabPage11">
			<h2 class="tab">Company</h2>
			<script  type="text/javascript" language="javascript">tp1.addTabPage( document.getElementById( "tabPage11" ) );</script>

			<table width=100% cellpadding=0 cellspacing=0 border=0>
			<tr class="NewGridTopBg">
			<?php
			$name=explode("|","fa fa-floppy-o~Save|fa fa-times~Close");
			$link=explode("|","javascript:validatepage(this)|javascript:window.close()");
			$heading=$head;
			$menu->showHeadingStrip1($name,$link,$heading);
			?>
			</tr>
			</table>
<div class="form-container">
	<fieldset>
		<legend><font class="afontstyle">Settings</font></legend>
		<div class="settings-back">

    	<table width="100%" cellpadding="1" cellspacing="1" border="0">
	        <tr>
				<td>
				<span id="leftflt"><span class="crmsummary-content-title">Status</span>
      				<select class="summaryform-formelement" name="sstatus" style='width:130px'>
      				<option value=0>--select--</option>
 					<?php
				    $Ctype_Sql="select sno,name from manage where type='compstatus' order by name";
					$Ctype_Res=mysql_query($Ctype_Sql,$db);
					while($Ctype_Data=mysql_fetch_row($Ctype_Res))
					{
						if($_SESSION['edit_company'.$Rnd] == '')
						{
							$Ctype_Zsql="select sno from manage where type='compstatus' and name='Prospect'";
							$Ctype_Rsql=mysql_query($Ctype_Zsql,$db);
							if(mysql_num_rows($Ctype_Rsql)>0)
							{
								$Ctype_field=mysql_fetch_row($Ctype_Rsql);
								echo "<option value='$Ctype_Data[0]' ".sele($Ctype_Data[0],$Ctype_field[0]).">".html_tls_specialchars($Ctype_Data[1],ENT_QUOTES)."</option>";
							}
							else
							{
								echo "<option value='$Ctype_Data[0]' ".sele($Ctype_Data[0],$s_status).">".html_tls_specialchars($Ctype_Data[1],ENT_QUOTES)."</option>";
							}
					    }
						else
						{
							echo "<option value='$Ctype_Data[0]' ".sele($Ctype_Data[0],$s_status).">".html_tls_specialchars($Ctype_Data[1],ENT_QUOTES)."</option>";
						}
					}
					?>
      				</select> <?php if(EDITLIST_ACCESSFLAG){ ?>
    				<a href="javascript:doManage('Company Status','sstatus');" class="edit-list">edit list</a>
				&nbsp;&nbsp;&nbsp; <?php } ?>
				</span>
				<span id="leftflt"><span class="crmsummary-content-title">Owner</span>
   				<select class="summaryform-formelement" name="cowner" id="cowner" style='width:180px'  disabled="disabled">
				<?php
				foreach($Users_Array as $UserNo=>$UName)
				{
					?>
					<option value="<?=$UserNo?>" <?=sele($c_owner,$UserNo)?>><?=html_tls_specialchars($UName,ENT_QUOTES)?></option>
					<?
				}
				?>
      			</select>
    			&nbsp;&nbsp;&nbsp;
				</span>
				<span id="leftflt"><span class="crmsummary-content-title">Share</span>
				<select class="summaryform-formelement" name="cshare" id='cshare' onChange="doShare1(this.value)">
				<option value="private" <? echo sele($c_share,'private')?>>Private</option>
				<option value="share" <? echo sele($c_share,'share')?>>Share</option>
				<option value="public" <? echo sele($c_share,'public');?>>Public</option>
				</select>
				</span>
				</td>
			</tr>
			</table>
		</div>
	</fieldset>
	<table width="100%" cellpadding="1" cellspacing="1" border="0">
		<tr>
			<td align=center>
			<?php
			/**
			* Include the file to generate user defined fields.
			*
			*/
			$mod = 2;
			include($app_inc_path."custom/getcustomfields.php");
			?>
			</td>
		</tr>
	</table>
	<fieldset>
		<legend><font class="afontstyle">Company Information&nbsp;&nbsp;</font></legend>
		<div class="form-back">
		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="crmsummary-edit-table align-middle">
			<tr class="summaryrow">
				<td colspan="4" class="summarytext">
					<div class="row mb-3">
						<div class="col-3">
							<label class="form-label"><?php echo (!empty($userFieldsAlias['Company Name']))?$userFieldsAlias['Company Name']:'Company Name';echo getRequiredStar('Company Name', $userFieldsArr);?><?=$mandatory_madison;?></label>
							<input class="form-control" type=text name=cname size=54 maxlength=100 value="<?php echo html_tls_entities(stripslashes($com_name),ENT_QUOTES);?>"  setName='Company name' <?php echo $spl_Attribute?>>
						</div>
						<div class="col-3">
							<label class="form-label"><?php echo (!empty($userFieldsAlias['Website']))?$userFieldsAlias['Website']:'Website';echo getRequiredStar('Website', $userFieldsArr);?><?=$mandatory_madison;?></label>
							<input class="summaryform-formelement" type=text name=curl size=53 maxsize=100 maxlength=100 value="<?php echo html_tls_entities($com_url,ENT_QUOTES);?>" >
						</div>
					</div>
					<div class="row mb-3">
						<div class="col-3">
							<label class="form-label"><?php echo (!empty($userFieldsAlias['Address 1']))?$userFieldsAlias['Address 1']:'Address 1';echo getRequiredStar('Address 1', $userFieldsArr);?><?=$mandatory_madison;?>: <span class="text-muted">(main)</span></label>
							<?php if($frmcontsum=="true"){?>
							<span id="sameascont"><a class="edit-list" href="javascript:popContactDetails()">same as contact</a></span>
							<?}?>
							<input class="form-control" type=text name=address1 size=54 maxlength=100 value="<?php echo html_tls_entities(stripslashes($com_addr1),ENT_QUOTES);?>" setName='Address 1' <?php echo $spl_Attribute?>>
						</div>
						<div class="col-3">
							<label class="form-label">Address 2</label>
							<input class="form-control" type=text name=address2 size=53 maxlength=100 value="<?php echo html_tls_entities(stripslashes($com_addr2),ENT_QUOTES);?>">
						</div>
					</div>
					<div class="row">
						<div class="col-3">
							<label class="form-label">City<?php echo (!empty($userFieldsAlias['City']))?getRequiredStar('City', $userFieldsArr):$mandatory_madison;?></label>
							<input class="form-control" type=text name=city size=25 maxlength=50 value="<?php echo html_tls_entities(stripslashes($com_city),ENT_QUOTES);?>" setName='city' <?php echo $spl_Attribute?>>
						</div>
						<div class="col-3">
							<label class="form-label">State<?php echo (!empty($userFieldsAlias['State']))?getRequiredStar('State', $userFieldsArr):$mandatory_madison;?></label>
							<input class="form-control" type=text name=state size=15 maxlength=20 value="<?php echo html_tls_entities(stripslashes($com_state),ENT_QUOTES);?>" setName='state' <?php echo $spl_Attribute?>>
						</div>
						<div class="col-3">
							<label class="form-label"><?php echo (!empty($userFieldsAlias['Zip']))?$userFieldsAlias['Zip']:'Zip';echo getRequiredStar('Zip', $userFieldsArr);?><?=$mandatory_madison;?></label>
							<input class="form-control" type=text name=zip size=10 maxlength="<?php echo (PAYROLL_PROCESS_BY_MADISON=='MADISON')?'5':'20'?>" value="<?php echo html_tls_entities(stripslashes($com_zip),ENT_QUOTES);?>"  setName='Zip' <?php echo $spl_Attribute?>>
						</div>
						<div class="col-3">
							<label class="form-label">Country:</label>
							<select class="form-select" name="country">
								<option selected value=0>Select</option>
								<?php echo getCountryNames($com_country); ?>
							</select>
						</div>
					</div>
				</td>
			</tr>
			<tr class="summaryrow">
				<td width="162" class="crmsummary-content-title"><div class="space_15px">&nbsp;</div>Customer ID#</td>
				<td class="summarytext"><div class="space_15px">&nbsp;</div>&nbsp;&nbsp;&nbsp;<input type=hidden name="compcustid" value="<? echo  $customer_id; ?>" /></span></td>
				<td class="crmsummary-content-title"><div class="space_15px">&nbsp;</div>Company Revenue</td>
				<td class="summarytext"><div class="space_15px">&nbsp;</div><input class="summaryform-formelement" type=text name=com_revenue size=32 maxsize=50 maxlength=50 value="<?php echo html_tls_entities(stripslashes($com_rev),ENT_QUOTES);?>" >&nbsp;&nbsp;&nbsp;</span></td>
			</tr>
			<tr class="summaryrow">
				<td width="100"  class="crmsummary-content-title summarytext">Main Phone<?php echo (!empty($userFieldsAlias['Main Phone']))?getRequiredStar('Main Phone', $userFieldsArr):$mandatory_madison;?></td>
				<td class="summarytext"><input class="summaryform-formelement" type=text name=phone size=16 maxlength=30 value="<?php echo html_tls_entities(stripslashes($phone),ENT_QUOTES);?>"  setName='phone' <?php echo (PAYROLL_PROCESS_BY_MADISON=='MADISON') ? "udCheckPhNum='YES' ".$spl_Attribute :'';?>><span class="crmsummary-content-title">&nbsp;ext.&nbsp;&nbsp;</span><input class="summaryform-formelement" size=8 maxlength="<?php echo (PAYROLL_PROCESS_BY_MADISON=='MADISON')?'4':'16'?>" type="text" name="phone_extn" value="<?=html_tls_entities(stripslashes($phone_extn),ENT_QUOTES);?>"></span></td>				
				<td valign="middle" class="crmsummary-content-title">No. Employees</td>
				<td class="summarytext"><input class="summaryform-formelement" type=text name=nemp size=32 maxlength=50 value="<?php echo html_tls_entities(stripslashes($no_emps),ENT_QUOTES);?>">&nbsp;&nbsp;&nbsp;</span></td>
			</tr>
			<tr class="summaryrow">
				<td width="100" class="crmsummary-content-title">Fax Number</td>
				<td class="summarytext"><input class="summaryform-formelement" type=text name=fax size=32 maxlength=50 value="<?php echo html_tls_entities(stripslashes($fax),ENT_QUOTES);?>">&nbsp;&nbsp;&nbsp;</span></td>			
				<td class="crmsummary-content-title">No.Locations</td>
				<td class="summarytext"><input class="summaryform-formelement" type=text name=nloc size=32 maxsize=50 maxlength=50 value="<?php echo html_tls_entities(stripslashes($no_com_loc),ENT_QUOTES);?>">&nbsp;&nbsp;&nbsp;</span></td>
			</tr>
			<tr class="summaryrow">
				<td class="crmsummary-content-title">Industry</td>
				<td class="summarytext"><input class="summaryform-formelement" type=text name=industry size=32 maxsize=50 maxlength=255 value="<?php echo html_tls_entities(stripslashes($industry),ENT_QUOTES);?>">&nbsp;&nbsp;&nbsp;</span></td>				
				<td class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Company Source']))?$userFieldsAlias['Company Source']:'Company Source';echo getRequiredStar('Company Source', $userFieldsArr);?></td>
				<td class="summarytext">
    				<select name=compsource class="summaryform-formelement" >
					<option value=0>--select--</option>
    				<?php
					$CSrc_Sql="select sno,name from manage where type='compsource' order by name";
					$CSrc_Res=mysql_query($CSrc_Sql,$db);
					while($CSrc_Data=mysql_fetch_row($CSrc_Res))
					{
						echo "<option value='$CSrc_Data[0]'".sele($comp_Source,$CSrc_Data[0]).">".html_tls_specialchars($CSrc_Data[1],ENT_QUOTES)."</option>";
					}
					?>
    				</select> <?php if(EDITLIST_ACCESSFLAG){ ?>
				<a href="javascript:doManage('Company Source','compsource');" class="edit-list">edit list</a> <?php } ?>
				</td>
			</tr>
			<tr class="summaryrow">
				<td class="crmsummary-content-title">Year Founded</td>
				<td class="summarytext"><input class="summaryform-formelement" type=text name=nyb size=32 maxsize=50 maxlength=50 value="<?php echo html_tls_entities(stripslashes($no_years),ENT_QUOTES);?>" >&nbsp;&nbsp;&nbsp;</span></td>               
    				<td class="crmsummary-content-title">SIC Code</td>
				<td class="summarytext"><input class="summaryform-formelement" type=text name=siccode size=32 maxsize=50 maxlength=50 value="<?php echo html_tls_entities(stripslashes($sicCode),ENT_QUOTES);?>">&nbsp;&nbsp;&nbsp;</span></td>
			</tr>
			<tr class="summaryrow"> 
				<td class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Company Type']))?$userFieldsAlias['Company Type']:'Company Type';echo getRequiredStar('Company Type', $userFieldsArr);?></td>
				<td class="summarytext">
    				<select class="summaryform-formelement" name=ctype>
					<option value=0>--select--</option>
					<?php
					$Ctype_Sql="select sno,name from manage where type='comptype' order by name";
					$Ctype_Res=mysql_query($Ctype_Sql,$db);
					while($Ctype_Data=mysql_fetch_row($Ctype_Res))
					{
						echo "<option value='$Ctype_Data[0]'".sele($Ctype_Data[0],$com_type).">".html_tls_specialchars($Ctype_Data[1],ENT_QUOTES)."</option>";
					}
					?>
    				</select> <?php if(EDITLIST_ACCESSFLAG){ ?>
				<a href="javascript:doManage('Company Type','ctype');" class="edit-list">edit list</a> <?php } ?>
				</td>				
				<td class="crmsummary-content-title">Federal ID</td>
				<td class="summarytext"><input class="summaryform-formelement" type=text name=federalid size=32 maxlength=50 value="<?php echo html_tls_entities(stripslashes($fed_id),ENT_QUOTES);?>" setName='federalid'>&nbsp;&nbsp;&nbsp;</span></td>
			</tr>
			<tr class="summaryrow">
				<td class="crmsummary-content-title">Company Ownership</td>
				<td class="summarytext"><input class="summaryform-formelement" type=text name=cownership size=32 maxsize=50 maxlength=50 value="<?php echo html_tls_entities(stripslashes($c_ownership),ENT_QUOTES);?>" >&nbsp;&nbsp;&nbsp;</span></td>
				<td valign="middle" class="crmsummary-content-title">Company Size</td>
				<td class="summarytext"><input class="summaryform-formelement" type=text name=csize size=32 maxsize=50 maxlength=50 value="<?php echo $com_size;?>">&nbsp;&nbsp;&nbsp;</span></td>
			</tr>
			<tr class="summaryrow">
				<td class="crmsummary-content-title">Ticker Symbol</td>
				<td class="summarytext"><input class="summaryform-formelement" type=text name=ticker size=32 maxsize=50 maxlength=50 value="<?php echo html_tls_entities(stripslashes($tickerSymbol),ENT_QUOTES);?>" >&nbsp;&nbsp;&nbsp;</span></td>
				<td valign="middle" class="crmsummary-content-title">Alternative ID#</td>
				<td class="summarytext"><input class="summaryform-formelement" type=text name=comalternateid size=32 maxsize=255 maxlength=255 value="<?php echo $alternate_id;?>">&nbsp;&nbsp;&nbsp;</span></td>
			</tr>
			<tr class="summaryrow">
				<td class="crmsummary-content-title">Department<?php if(PAYROLL_PROCESS_BY_MADISON=='MADISON'){ echo $mandatory_madison; }?></td>
				<td class="summarytext"><input class="summaryform-formelement" type=text name="departmentname" id="departmentname" size=32 maxlength=255 value="<?php  echo html_tls_entities(stripslashes($page11['53']),ENT_QUOTES); ?>" <?php if(PAYROLL_PROCESS_BY_MADISON=='MADISON'){?> setName='Department' <?php echo $spl_Attribute; }?>>&nbsp;&nbsp;&nbsp;</span></td>
				<td class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['HRM Department']))?$userFieldsAlias['HRM Department']:'HRM Department';echo getRequiredStar('HRM Department', $userFieldsArr);?></td>				
				<td class="summarytext">
				<?php echo departmentSelBox('HRMDepartments', $page11[55], 'summaryform-formelement','','','style="width:205px;"','yes','');?>
				<input class="summaryform-formelement" type=hidden name="addressdesc" id="addressdesc" size=32  maxlength=255 value="<?php  echo html_tls_entities(stripslashes($page11['54']),ENT_QUOTES); ?>" >&nbsp;&nbsp;&nbsp;</span></td>
			</tr>			
			<tr class="summaryrow">
			  <td colspan="4" class="summarytext">
			  <table width="100%" border="0" cellpadding="0" cellspacing="0" class="align-middle">
				<?php echo $OBJ_Comp_Role->entityRoleDisplayHTML_Company($compRoles);?>
			  </table>	
			  </td>
			</tr>
			<tr>
				<td valign="top"><div class="space_15px">&nbsp;</div><div class="crmsummary-content-title">Company Brief</div><span class="summaryform-nonboldsub-title">(internal notes)</span></td>
				<td colspan="3"><div class="space_15px">&nbsp;</div><textarea name="cbrief" rows="2" cols="68"><?php echo html_tls_entities(stripslashes($c_brief),ENT_QUOTES);?></textarea></td>
			</tr>
			<tr>
				<td valign="top"><div class="crmsummary-content-title"><nobr>Company Summary</nobr></div><span class="summaryform-nonboldsub-title">(for job orders)</span></td>
				<td colspan="3"><textarea name="csummary" rows="2" cols="68" ><?php echo html_tls_entities(stripslashes($c_summary),ENT_QUOTES);?></textarea></td>
			</tr>
			<tr>
				<td valign="top"><div class="crmsummary-content-title">Search Tags</div><span class="summaryform-nonboldsub-title">(search keywords)</span></td>
				<td colspan="3"><textarea name="stags" rows="2" cols="68"><?php echo html_tls_entities(stripslashes($s_tags),ENT_QUOTES);?></textarea></td>
			</tr>
			<tr>
				<td valign="top"><div class="crmsummary-content-title">Notes</div><span class="summaryform-nonboldsub-title">(displays on summary)</span></td>
				<td colspan="3"><textarea name="compnotes" rows="2" cols="68"><?php echo html_tls_entities(stripslashes($com_compnotes),ENT_QUOTES);?></textarea></td>
			</tr>
			
			<tr>
				<td valign="top" class="summaryform-bold-title">Ultigigs Timesheet Layout</td>
				<td>
					<select name="company_timesheet_layout_preference" id="company_timesheet_layout_preference">
						<option value="">--- Select Template ---</option>
						<option value="Regular">Regular</option>
						<option value="TimeInTimeOut">Time In &amp; Time Out</option>
						<option value="Clockinout">Clock In &amp; Out</option>
					</select>
				</td>
			</tr>
			
			</table>
			</div>
		<div class="form-back accordion-item">
		<table width="100%" border="0" class="crmsummary-edit-tablemin table table-borderless align-middle mb-0" id="compHierarchy">
			<tr>
				<td width="160" class="crmsummary-content-title"><a style='text-decoration: none;' onClick="classToggle(mntcmnt1,'DisplayBlock','DisplayNone',1,'compHierarchy')" href="#hideExp1"> <span class="crmsummary-content-title" id="company_Hierarcyid">Company Hierarchy</span></a></td>
				<td>
				 <span id="rightflt" <?php echo $rightflt;?>>
                     <div class="form-opcl-btnleftside"><div align="left"></div></div>
                     <div><a onClick="classToggle(mntcmnt1,'DisplayBlock','DisplayNone',1,'compHierarchy')" class="form-cl-txtlnk" href="#hideExp1"><b><div id='hideExp1'>+</div></b></a></div>
                     <div class="form-opcl-btnrightside"><div align="left"></div></div>
                 </span>
				</td>
			</tr>
		</table>
		</div>
		<div class="DisplayNone" id="mntcmnt1">
		<table width="100%" class="crmsummary-jocomp-table table table-borderless align-middle">
              <tr>
                <input type="hidden" name="parent"  value="<?=$parentNo?>"  />
                 <td valign="top" width="160" class="summaryform-bold-title">Parent</td>
                 <td><span class="summaryform-formelement">By default created company is the parent.</span><br />
				 <? if($parentNo==0 || $parentNo=='') { ?><span id = "disp_parent"><a class="crm-select-link" href="javascript:parent_popup()" >select company</a></span><? }else { ?> <span id="disp_parent"><a class="crm-select-link" href="javascript:comp_func('<?php echo $parentNo;?>','<?php echo $parent_status;?>')" id="disp_parent"><?php echo $parentName;?></a>
				 <? if($parent_addr != ''){ ?><span class=summaryform-formelement>&nbsp;-&nbsp;</span><span class=summaryform-nonboldsub-title> <?php echo $parent_addr;?> </span><? }?></span><? } ?>&nbsp;<span class="summaryform-font"><a href="javascript:parent_popup()"><i class="fa fa-search"></i></a></span>
				 </td>
              </tr>
			  <tr>
				<td valign="top" class="summaryform-bold-title">Divisions</td>
				<td><a class="crm-select-link" href="javascript:openWin('/BSOS/Marketing/Companies/addDivisions.php?compsno=<?=$addr?>&CmngFrom=New&typecomp=<?php echo $typecomp;?>','addDiv')">add division</a>&nbsp;<span class="summaryform-font"><a href="javascript:openWin('/BSOS/Marketing/Companies/addDivisions.php?compsno=<?=$addr?>&CmngFrom=New&typecomp=<?php echo $typecomp;?>','addDiv')" ><i class="fa fa-search"></i></a>
				<span class="summaryform-formelement">
                <div id='expdivisions' style='width:560px;height:120px;overflow:auto;display:<?php echo ($Divisions!='')?'block':'none';?>'>
				<?php
				if($Divisions!='')
				{
					$divids=explode(",",$Divisions);
					$cnt=count($divids);
					for($i=0;$i < $cnt;$i++)
					{
						$add_sql="select  sno,address1,address2,city,state,country,zip,cname from staffoppr_cinfo where sno='$divids[$i]'";
						$add_res=mysql_query($add_sql,$db);
						$add_rows=mysql_num_rows($add_res);
						$add_data=mysql_fetch_row($add_res);
						$parAddr='';
						if($add_data[7]!='')
						  $parAddr=$add_data[7];
						if($add_data[1]!='')
						  $parAddr .=" ".$add_data[1];
						if($add_data[2]!='')
						  $parAddr.=",".$add_data[2];
						if($add_data[3]!='')
						  $parAddr.=",".$add_data[3];
                        if($add_data[4]!='')
						  $parAddr.=",".$add_data[4];
					 	if($add_data[5]!=0)
						  $parAddr.=",".getCountry($add_data[5]);
						if($add_data[6]!='')
						  $parAddr.=",".$add_data[6];

					  	if($parAddr=='')
							$parAddr='no address';

						 //check for sub divisions
						  $CheckChild_sql="select sno from staffoppr_cinfo where parent=$add_data[0]";
						  $CheckChild_res=mysql_query($CheckChild_sql,$db);
						  $checkrows=mysql_num_rows($CheckChild_res);
						   if($checkrows>0)
							  $Icon='/BSOS/images/crm/icon-div.gif';
						   else
							  $Icon='/BSOS/images/crm/icon-branch.gif';
							echo "<a class=remind-delete-align href=javascript:del_divs('$add_data[0]','$addr')><img src='/BSOS/images/crm/icon-delete.gif' width=10 height=9 title='' border=0 align=left></a><img src='/BSOS/images/email/L.png' valign=top><img src= $Icon valign=middle>&nbsp;&nbsp<a href=# onclick=javascript:openWin('/BSOS/Marketing/Companies/viewcompanySummary.php?addr=$divids[$i]','cmpsummary') class='crmsummary-contentlnk '>".html_tls_specialchars($parAddr,ENT_QUOTES)."</a><br/>";

							divTree($divids[$i],'&nbsp;&nbsp;',0);
					}
				}
				?>
				</div>&nbsp;&nbsp;
				 </td>
              </tr>
            </table>
			</div>
		<div class="form-back accordion-item">
		<table width="100%" border="0" class="crmsummary-edit-tablemin table table-borderless align-middle mb-0" id="billinginfo">
			<tr>
				<td width="160" class="crmsummary-content-title"><a style='text-decoration: none;' onClick="classToggle(mntcmnt2,'DisplayBlock','DisplayNone',2,'billinginfo')" href="#hideExp2"> <span class="crmsummary-content-title" id="company_billinginform">Billing Information</span></a> </td>
				<td>
				 <span id="rightflt" <?php echo $rightflt;?>>
                     <div class="form-opcl-btnleftside"><div align="left"></div></div>
                     <div><a onClick="classToggle(mntcmnt2,'DisplayBlock','DisplayNone',2,'billinginfo')" class="form-cl-txtlnk" href="#hideExp2"><b><div id='hideExp2'>+</div></b></a></div>
                     <div class="form-opcl-btnrightside"><div align="left"></div></div>
                 </span>
				</td>
			</tr>
		</table>
		</div>
		<div class="DisplayNone" id="mntcmnt2">
		<table width="100%" class="crmsummary-jocomp-table table table-borderless align-middle">
			<tr>
			    <?php
				$cus_username="select username from staffacc_cinfo where sno='".$billcompany."'";
                $cus_username_res=mysql_query($cus_username,$db);
                $cust_username=mysql_fetch_row($cus_username_res);
                $custusername=$cust_username[0];
				
				?>
				<input type="hidden" name="billcompany_sno" id="billcompany_sno" value="<?php echo $billcompany;?>">
				<input type="hidden" name="billcompany_username" id="billcompany_username" value="<?php echo $custusername;?>">
				<td width="160" class="summaryform-bold-title">Default Billing Address</td>
				<td><span id="billdisp_comp"><input type="hidden" name="bill_loc" id="bill_loc"><a class="crm-select-link" href="javascript:bill_jrt_comp('bill')">select company</a>&nbsp;</span></span>&nbsp;<span id="billcomp_chgid">&nbsp;</span>
				<?
				if($billcontact>0 || $bill_loc!='')
				{
					?>
					<script type="text/javascript" language="javascript">getCRMLocations('<?php echo $billcompany;?>','<?php echo $billcontact;?>','<?php echo $bill_loc;?>','bill');</script>
					<?
				}
				?>
				</td>
			</tr>
			<tr>
				<input type="hidden" name="billcontact_sno" id="billcontact_sno" value="<?php echo $billcontact;?>">
				<td width="160" class="summaryform-bold-title">Default Billing Contact<?php if(TRICOM_REPORTS=='Y'){ echo $mandatory_madison; }?></td>
				<td>
				<?
				if($billcontact==0)
				{
					?>
					<span id="billdisp"<?php if(TRICOM_REPORTS=='Y'){?> setName='Billing Contact' <?php echo $spl_Attribute; }?>><a class="crm-select-link" href="javascript:bill_jrt_cont('bill')" id="disp">select contact</a></span>
					&nbsp;<span id="billchgid"><a href="javascript:bill_jrt_cont('bill')"><i class="fa fa-search"></i></a><span class="summaryform-formelement">&nbsp;|&nbsp;</span><a class="crm-select-link" href="javascript:donew_add('bill')">new&nbsp;contact</a></span>
					<?
				}
				else 
				{ 
					?>
					<span id="billdisp"><a class="crm-select-link" href="javascript:contact_func('<?php echo $billcontact;?>','<?php echo $billcont_stat;?>','bill')" id="disp"><?php echo $billcont;?></a></span>
					&nbsp;<span id="billchgid"><span class=summaryform-formelement>(</span> <a class=crm-select-link href=javascript:bill_jrt_cont('bill')>change </a>&nbsp;<a href=javascript:bill_jrt_cont('bill')><i class="fa fa-search"></i></a><span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class=crm-select-link href=javascript:donew_add('bill')>new</a><span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class="crm-select-link" href="javascript:removeContact('bill')">remove&nbsp;</a><span class=summaryform-formelement>&nbsp;)&nbsp;</span></span>
					<?
				}
				?>
				</td>
			</tr>
			<tr class="summaryrow">
                 <td valign="top" class="summaryform-bold-title">Payment Terms<?php if(TRICOM_REPORTS=='Y'){ echo $mandatory_madison; }?></td>
                 <td class="summarytext">
				 <?php
					 $BillPay_Sql = "SELECT billpay_termsid, billpay_code FROM bill_pay_terms WHERE billpay_status = 'active' AND billpay_type = 'PT' ORDER BY billpay_code";
					 $BillPay_Res = mysql_query($BillPay_Sql,$db);
					?>
					<select name="billreq" id="billreq" style="width:210px;" <?php if(TRICOM_REPORTS=='Y'){?> setName='Payment Terms' <?php echo $spl_Attribute; }?>>>
					<option value=""> -- Select -- </option>
					<?php  
					while($BillPay_Data = mysql_fetch_row($BillPay_Res))
				 	{ 
						?>
						<option value="<?=$BillPay_Data[0];?>" <?php echo sele($bill_req,$BillPay_Data[0]); ?> title='<?=html_tls_specialchars(stripslashes($BillPay_Data[1]),ENT_QUOTES);?>'><?=stripslashes($BillPay_Data[1]);?></option>
						<?php 
					}
					?>
					</select>
					<?php 
					if(ENABLE_MANAGE_LINKS == 'Y')
						echo '&nbsp;&nbsp;&nbsp;<a href="javascript:doManageBillPayTerms(\'Payment\',\'billreq\')" class="edit-list">Manage</a>';
					?>
            	</td>
            </tr>
  			<tr>
                 <td valign="top" class="summaryform-bold-title">Service Terms</td>
                 <td><textarea name="servterms" rows="2" cols="64"><?php echo html_tls_entities(stripslashes($ser_terms),ENT_QUOTES);?></textarea></span>
            </td>
              </tr>
			<tr>
				<td valign="top" class="summaryform-bold-title">Pay Burden</td>
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
				<td valign="top" class="summaryform-bold-title">Bill Burden</td>
				<td><?php echo $bill_bt_str; ?>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<b><span id="billburdenItemsStr" style="font-weight:bold;font-size: 12px;">&nbsp;</span></b>
				</td>
			</tr>
			<tr>
				<td valign="top" class="summaryform-bold-title">Set Default Rate(s)</td>
				<td><a class="link-primary" href="javascript:showCompanyRates();">select rates</a></td>
			</tr>
			<tr>
				<td valign="top"></td>
				<td align="left" style="padding:0px">
					<div id="multipleRatesTab">
					<?php
						$customratetypeids = "";
						if($_SESSION['comp_rates_details'.$Rnd]!='' && $_SESSION['comp_rates_details'.$Rnd]!='|')
						{
							$customratesexplode 	= explode("|", $_SESSION['comp_rates_details'.$Rnd]);
							$customratetypeids	= $customratesexplode[1];	
							echo $objMRT->displayCompanyRates($_SESSION['comp_rates_details'.$Rnd]);
							$custratessnoexplode	= explode(",",$customratesexplode[1]);
						
							foreach($custratessnoexplode as $custratesnos)
							{
								?>
									<script type="text/javascript" language="javascript">
										pushSelectedPayRateidsArray(<?php echo $custratesnos;?>);
									</script>
								<?php
							}
							?>
							<script type="text/javascript" language="javascript">
								pushAddEditRateRowArray('<?php echo $customratetypeids; ?>');
							</script>
					<?php
						}
					?>
					</div>
					<input type="hidden" id="selectedcustomratetypeids" name="selectedcustomratetypeids" value="<?php echo $customratetypeids; ?>"/>
				</td>
			</tr>
			<tr>
				<td valign="top"></td>
				<td align="left" style="padding:0px">
					<input type="hidden" id="selectedcustomshifts" name="selectedcustomshifts" value=""/>
					<input type="hidden" id="shiftIndexStr" name="shiftIndexStr" value=""/>
					<?php
					//echo "session data".$_SESSION["comp_rates_shift_details".$Rnd];
					?>
					<div id="multipleRatesShiftsTab">
					<?php					
					if(isset($_SESSION["comp_rates_shift_details".$Rnd]) && $_SESSION['comp_rates_shift_details'.$Rnd]!='' && $_SESSION['comp_rates_shift_details'.$Rnd]!='|')
					{
						echo $objMRT->displayCompanyShiftRates($_SESSION['comp_rates_shift_details'.$Rnd]);
						//print_r($objMRT->shiftIdArr);
						foreach($objMRT->shiftIdArr AS $key=>$val)
						{
							echo "<script type='text/javascript' language='javascript'>pushSelectedShiftidsArray($val);</script>";
						}
						echo "<script type='text/javascript' language='javascript'>document.getElementById('selectedcustomshifts').value = '".implode('^', $objMRT->shiftAndItRatesArr)."';buildShiftIndexesFromClass();</script>";
					}					
					?>
					</div>	
				</td>
			</tr>
			
		</table>
			</div>
		<div class="form-back accordion-item">
		<table width="100%" border="0" class="crmsummary-edit-tablemin table table-borderless align-middle mb-0" id="compculture">
			<tr>
				<td width="250" class="crmsummary-content-title"><a style='text-decoration: none;' onClick="classToggle(mntcmnt3,'DisplayBlock','DisplayNone',3,'compculture')" href="#hideExp3"> <span class="crmsummary-content-title" id="company_cultureonbordinfo">Company Culture/Onboarding Information</span></a> </td>
				<td>
				 <span id="rightflt" <?php echo $rightflt;?>>
                     <div class="form-opcl-btnleftside"><div align="left"></div></div>
                     <div><a onClick="classToggle(mntcmnt3,'DisplayBlock','DisplayNone',3,'compculture')" class="form-cl-txtlnk" href="#hideExp3"><b><div id='hideExp3'>+</div></b></a></div>
                     <div class="form-opcl-btnrightside"><div align="left"></div></div>
                 </span>
				</td>
			</tr>
		</table>
		</div>
		<div class="DisplayNone" id="mntcmnt3">
		<table width="100%" class="crmsummary-jocomp-table table table-borderless align-middle">
              <tr>
                 <td width="160" class="summaryform-bold-title">Dress Code</td>
 				<td><input class="summaryform-formelement" type=text name=dcode size=54 maxsize=54 maxlength=54 value="<?php echo html_tls_entities(stripslashes($d_code),ENT_QUOTES);?>">&nbsp;&nbsp;&nbsp;</span></td>
             </tr>
              <tr>
                 <td class="summaryform-bold-title">Telecommuting Policy</td>
                 <td><input class="summaryform-formelement" type=text name=tpolicy size=54 maxsize=54 maxlength=54 value="<?php echo html_tls_entities(stripslashes($t_policy),ENT_QUOTES);?>">&nbsp;&nbsp;&nbsp;</span>
            </td>
              </tr>
			 <tr>
                 <td class="summaryform-bold-title">Smoking Policy</td>
                 <td><input class="summaryform-formelement" type=text name=spolicy size=54 maxsize=54 maxlength=54 value="<?php echo html_tls_entities(stripslashes($s_policy),ENT_QUOTES);?>">&nbsp;&nbsp;&nbsp;</span>
            </td>
              </tr>
			 <tr>
                 <td>Parking<div class="form-text">(check all that apply)</div></td>
                 <td>
					 <div class="form-check form-check-inline">
						 <input class="form-check-input" type="checkbox" name="parking1" value="free" <?php echo sent_check($Other_Field[0],"free"); ?>>
						 <label class="form-check-label">Free</label>
					 </div>
					 <div class="form-check form-check-inline">
						<input class="form-check-input" type="checkbox" name="parking2" value="onsite" <?php echo sent_check($Other_Field[1],"onsite"); ?>>
						<label class="form-check-label">On Site</label>
					 </div>
					 <div class="form-check form-check-inline">
						 <input class="form-check-input" type="checkbox" name="parking3" value="offsite" <?php echo sent_check($Other_Field[2],"offsite"); ?>>
						 <label class="form-check-label">Off Site</label>
					 </div>
					 <div class="form-check form-check-inline">
						 <input class="form-check-input" type="checkbox" name="parking4" value="lspaces" <?php echo sent_check($Other_Field[3],"lspaces"); ?>>
						 <label class="form-check-label">Limited Spaces</label>
					 </div>
					 <div class="form-check form-check-inline">
						 <input class="form-check-input" type="checkbox" name="parking5" value="pspaces" <?php echo sent_check($Other_Field[4],"pspaces"); ?>>
						 <label class="form-check-label">Plenty of Spaces</label>
					 </div>
					 <br />
					 <div class="form-check form-check-inline">
						 <div class="d-flex align-items-center">
							 <input class="form-check-input" type="checkbox" name="parking6" value="prate" <?php echo sent_check($Other_Field[5],"prate"); ?>>
							 <label class="form-check-label">
								 Rate ( $ <input class="form-control form-control-sm" type="text" size=5 name="prateval" value="<?php echo html_tls_entities(stripslashes($p_rate),ENT_QUOTES);?>"> )
							 </label>
						</div>
					 </div>
					 <div class="form-check form-check-inline">
						 <input class="form-check-input" type="checkbox" name="parking7" value="validate" <?php echo sent_check($Other_Field[6],"validate"); ?>>
						 <label class="form-check-label">Validate</label>
					 </div>
					 <div class="form-check form-check-inline">
						 <input class="form-check-input" type="checkbox" name="parking8" value="public" <?php echo sent_check($Other_Field[7],"public"); ?>>
						 <label class="form-check-label">Public</label>
					 </div>
	            </td>
              </tr>
			<tr>
                 <td valign="top" class="summaryform-bold-title">Directions</td>
                 <td><textarea name="directions" rows="2" cols="64"><?php echo html_tls_entities(stripslashes($directions),ENT_QUOTES);?></textarea></span>
            </td>
              </tr>
			 <tr>
                 <td valign="top" class="summaryform-bold-title">Other Info/Culture</td>
                 <td><textarea name="infocul" rows="2" cols="64"><?php echo html_tls_entities(stripslashes($info_cul),ENT_QUOTES);?></textarea></span>
            </td>
            </tr>
            </table>
			</div><br /><br />

			<fieldset>
			<legend><font class=afontstyle>Company Contacts</font></legend>
			<table width=100% cellpadding=3 cellspacing=0 border=0>
			<tr valign=top>
			<td align=right>
				<a  href="javascript:doAddCon()" tabindex=28><font class=linkrow>Add Contact</font></a>
			</td>
			</tr>
            <tr>
				<td align=center>
			    <table width=100% cellpadding=0 cellspacing=1 border=0 class="table table-borderless align-middle" id='mainContListTable'>
			  		<?php
					if(count($arry)>0)
					{
						print "<tr><td><table width=100% border=0 cellpadding=2 cellspacing=0 id='compContList'><tr class=hthbgcolor><td width=25%><font class=afontstyle>Contact Name</font></td><td width=25%><font class=afontstyle>Title</font></td><td width=10%><font class=afontstyle>Contact Type</font></td><td width=20%><font class=afontstyle>Phone Number</font></td><td width=10%><font class=afontstyle>&nbsp;</font></td><td width=10%><font class=afontstyle>&nbsp;</font></td></tr>";
						for($j=0;$j<count($arry);$j++)
						{
							print "<tr class='panel-table-content-new'>";
                            for($i=0;$i<4;$i++)
							{
                                if($arry[$j][2]=="--")
                                   $arry[$j][2]="";
								print "<td><font class=summarytext>".$arry[$j][$i]."</font></td>";
                            }
                                print "<td><a href=javascript:editCon('".$arry[$j][4]."','".$arry[$j][5]."')><font class=linkrow>Edit</font></a></td><td><a href=javascript:delCon('".$arry[$j][4]."','".$arry[$j][5]."','".$arry[$j][6]."')><font class=linkrow>Delete</font></a></td></tr>";
						}
						print "</table></td></tr>";
					}
					else
					{
						print "<tr><td align=center><font class=afontstyle >No Contacts are available.</font></td></tr>";
					}
					?>
				</table>
				</td>
			</tr>
			</table>
			</fieldset>

			
		<br />
		</div>

		<table width=100% cellpadding=0 cellspacing=0 border=0>
		<tr class="NewGridBotBg">
		<?php
		
		?>
		</tr>

		<tr>
			<td><font class=afontstyle></font></td>
		</tr>
		</table>
		</div>
        <script type="text/javascript" language="javascript">tp1.setSelectedIndex(0);</script>
	</div>
	</td>
	</tr>
	</table>
</div>
</form>
<input type="hidden" id="payrate_calculate_confirmation" value="yes" />
<input type="hidden" id="billrate_calculate_confirmation" value="yes" />
<input type="hidden" id="cap_separated_custom_rates" value="" />
<input id="payrate_calculate_confirmation_window_onblur" type="hidden" value="">
<input id="billrate_calculate_confirmation_window_onblur" type="hidden" value="">
<input type=hidden name="confirmstate" id="confirmstate" value="add">	
<script type="text/javascript" language="javascript">
$(window).focus(function()
{
    if(document.getElementById('payrate_calculate_confirmation_window_onblur').value != ""){
        document.getElementById('payrate_calculate_confirmation').value = document.getElementById('payrate_calculate_confirmation_window_onblur').value;
    }
    if(document.getElementById('billrate_calculate_confirmation_window_onblur').value != ""){
        document.getElementById('billrate_calculate_confirmation').value = document.getElementById('billrate_calculate_confirmation_window_onblur').value;
    }
});
</script>
<?php include('footer.inc.php') ?>
</body>
</html>