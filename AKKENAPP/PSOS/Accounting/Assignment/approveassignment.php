<?php  
	require("global.inc");
	require("dispfunc.php");

	require("Menu.inc");
	$menu=new EmpMenu();
	$Supusr = superusername();
	$page15 = $_SESSION[page15.$ACC_AS_SESSIONRN];
	$page215 = $_SESSION[page215.$ACC_AS_SESSIONRN];

	$recVal=explode("|",$recno);
	$hrconJobSnoPerdiem = $recVal[0];
	$elements=explode("|",$page15);
	
        // saved worksite code retrieval 
        $worksitecode =  $elements[94]; 
        $daypay =  $elements[95];
        $licode =  $elements[96];		
        $federal_payroll =  $elements[97];
        $nonfederal_payroll = $elements[98];
        $contractid = $elements[99]; 
        $classification = $elements[100];
		$assignment_timesheet_layout_preference = $elements[101];
	session_unregister("schdet");

	if($page215=="OP")
	{
		session_unregister($_SESSION["Page215ass".$ACC_AS_SESSIONRN]);
		$_SESSION[Page215ass.$ACC_AS_SESSIONRN]="";

		session_unregister($_SESSION["Page215ass".$ACC_AS_SESSIONRN]);
		$_SESSION[Page215ass.$ACC_AS_SESSIONRN]="";
	}
	
	$Page215ass = $_SESSION[Page215ass.$ACC_AS_SESSIONRN];

	if($addr=="client")
		$elements[1]=$client;

	$date=explode("-",$elements[2]);
	$date1=explode("-",$elements[3]);

	for($i=1;$i<8;$i++)
		$sunc[$i]="";

	if($elements[28]!="")
	{
		$wda=explode(":",$elements[28]);
		$n=count($wda);
		for($i=0;$i<$n;$i++)
		{
			switch((int)$wda[$i])
			{
				case 1 :
					$sunc[1]=1;
					break;
				case 2 :
					$sunc[2]=2;
					break;
				case 3 :
					$sunc[3]=3;
					break;
				case 4 :
					$sunc[4]=4;
					break;
				case 5 :
					$sunc[5]=5;
					break;
				case 6 :
					$sunc[6]=6;
					break;
				case 7 :
					$sunc[7]=7;
					break;
			}
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

	$que="select name,lstatus, ".getEntityDispName("sno","name").",sno from emp_list where username='".$elements[1]."'";
	$res=mysql_query($que,$db);
	$row=mysql_fetch_row($res);
	$conusername1=$elements[1];
	$emp_lstatus = $row[1];
	$candidateName=stripslashes($row[2]);
        //to pass employee id for populating work site codes in assignments
        $candID = $row[3];
		
    if(TRICOM_REPORTS=='Y'){
	$spl_Attribute = (TRICOM_REPORTS=='Y') ? 'udCheckNull ="YES" ' : '';	
	}else{
	$spl_Attribute = (PAYROLL_PROCESS_BY_MADISON=='MADISON') ? 'udCheckNull ="YES" ' : '';
	} 
	//$spl_Attribute = (PAYROLL_PROCESS_BY_MADISON=='MADISON') ? 'udCheckNull ="YES" ' : '';

	$sque="SELECT jcs.acc_chk,jcs.acc_sel,jcs.acca_chk,jcs.coc_chk,jcs.coc_sel,jcs.coca_chk,jcs.coj_chk,jcs.coj_sel,jcs.coja_chk,jcs.con_chk,jcs.con_sel,jcs.cona_chk,jcs.clc_chk,jcs.clc_sel,jcs.clca_chk,jcs.clj_chk,jcs.clj_sel,jcs.clja_chk,jcs.cln_chk,jcs.cln_sel,jcs.clna_chk,jcs.mdate FROM job_cand_status jcs";
	$sres=mysql_query($sque,$db);
	$srow=mysql_fetch_array($sres);
	$jcscou=mysql_num_rows($sres);

	if($srow['coja_chk']=="Y")
		$jo_stat1="<select class='w-250' name=jostat1 id=jostat1>";
	else
		$jo_stat1="<select class='w-250' name=jostat1 id=jostat1 disabled>";

	if($srow['clja_chk']=="Y")
		$jo_stat2="<select class='w-250' name=jostat2 id=jostat2>";
	else
		$jo_stat2="<select class='w-250' name=jostat2 id=jostat2 disabled>";

	$jo_que="SELECT sno, name FROM manage WHERE type='jostatus' ORDER BY name";
	$jo_res=mysql_query($jo_que,$db);
	while($jo_row=mysql_fetch_row($jo_res))
	{
		if($srow['coj_sel']==0 && $jo_row[1]=="Closed")
			$srow['coj_sel'] = $jo_row[0];

		if($srow['clj_sel']==0 && $jo_row[1]=="Cancelled")
			$srow['clj_sel'] = $jo_row[0];

		$jo_stat1.="<option value=".$jo_row[0]." ".sele($jo_row[0],$srow['coj_sel']).">".$jo_row[1]."</option>";
		$jo_stat2.="<option value=".$jo_row[0]." ".sele($jo_row[0],$srow['clj_sel']).">".$jo_row[1]."</option>";
	}

	$jo_stat1.="</select>";
	$jo_stat2.="</select>";

	if($srow['coca_chk']=="Y")
		$cand_stat1="<select class='w-250' name=candstat1 id=candstat1>";
	else
		$cand_stat1="<select class='w-250' name=candstat1 id=candstat1 disabled>";

	if($srow['clca_chk']=="Y")
		$cand_stat2="<select class='w-250' name=candstat2 id=candstat2>";
	else
		$cand_stat2="<select class='w-250' name=candstat2 id=candstat2 disabled>";

	if($srow['acca_chk']=="Y")
		$cand_stat3="<select class='w-250' name=candstat3 id=candstat3>";
	else
		$cand_stat3="<select class='w-250' name=candstat3 id=candstat3 disabled>";

	$cand_que="SELECT sno, name FROM manage WHERE type='candstatus' ORDER BY name";
	$cand_res=mysql_query($cand_que,$db);
	while($cand_row=mysql_fetch_row($cand_res))
	{
		if($srow['acc_sel']==0 && $cand_row[1]=="On Assignment")
			$srow['acc_sel'] = $cand_row[0];

		if($srow['coc_sel']==0 && $cand_row[1]=="Actively Searching")
			$srow['coc_sel'] = $cand_row[0];

		if($srow['clc_sel']==0 && $cand_row[1]=="Actively Searching")
			$srow['clc_sel'] = $cand_row[0];

		$cand_stat1.="<option value=".$cand_row[0]." ".sele($cand_row[0],$srow['coc_sel']).">".$cand_row[1]."</option>";
		$cand_stat2.="<option value=".$cand_row[0]." ".sele($cand_row[0],$srow['clc_sel']).">".$cand_row[1]."</option>";
		$cand_stat3.="<option value=".$cand_row[0]." ".sele($cand_row[0],$srow['acc_sel']).">".$cand_row[1]."</option>";
	}

	$cand_stat1.="</select>";
	$cand_stat2.="</select>";
	$cand_stat3.="</select>";

	if($srow['cona_chk']=="Y")
		$note_stat1="<select name=notestat1 id=notestat1>";
	else
		$note_stat1="<select name=notestat1 id=notestat1 disabled>";

	if($srow['clna_chk']=="Y")
		$note_stat2="<select name=notestat2 id=notestat2>";
	else
		$note_stat2="<select name=notestat2 id=notestat2 disabled>";

	$note_que="SELECT sno, name FROM manage WHERE type='Notes' ORDER BY name";
	$note_res=mysql_query($note_que,$db);
	while($note_row=mysql_fetch_row($note_res))
	{
		if($srow['con_sel']==0 && $note_row[1]=="Assignment Status")
			$srow['con_sel'] = $note_row[0];

		if($srow['cln_sel']==0 && $note_row[1]=="Assignment Status")
			$srow['cln_sel'] = $note_row[0];

		$note_row[1] = str_replace('"', '', $note_row[1]);

		$note_stat1.="<option value=".$note_row[0]." ".sele($note_row[0],$srow['con_sel']).">".$note_row[1]."</option>";
		$note_stat2.="<option value=".$note_row[0]." ".sele($note_row[0],$srow['cln_sel']).">".$note_row[1]."</option>";
	}

	$note_stat1.="</select>";
	$note_stat2.="</select>";
	
	//Defining a variable for showing mandatory SyncHR star marks from this page only.
	$showMandatoryAstrik = "Y";
	//get the shift scheduling display mode whether it is old./new associated to the assignment

	$schedule_display = "OLD";

	if(SHIFT_SCHEDULING_ENABLED=="Y")
	{
		$schedule_display = "NEW";
	}

?>
<?php include("header.inc.php");?>
<title>Assignments</title>
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/tab.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/calendar.css">

<script type="text/javascript">
var asgnAlertStartDate = "<?php echo date('m/d/Y');?>";
var asgnAlertEndDay = "<?php echo date('d');?>";
var asgnAlertEndMonth = "<?php echo date('m');?>";
var asgnAlertEndYear = "<?php echo date('Y');?>"; 

var candStat1 = "<?php echo $cand_stat1;?>";
var candStat2 = "<?php echo $cand_stat2;?>";
var candStat3 = "<?php echo $cand_stat3;?>";

var joStat1 = "<?php echo $jo_stat1;?>";
var joStat2 = "<?php echo $jo_stat2;?>";

var noteStat1 = "<?php echo $note_stat1;?>";
var noteStat2 = "<?php echo $note_stat2;?>";

var jcsSetup = "<?php echo $jcscou;?>";

var candChk1 = "<?php echo $srow['coc_chk'];?>";
var candChk2 = "<?php echo $srow['clc_chk'];?>";
var candChk3 = "<?php echo $srow['acc_chk'];?>";

var joChk1 = "<?php echo $srow['coj_chk'];?>";
var joChk2 = "<?php echo $srow['clj_chk'];?>";

var noteChk1 = "<?php echo $srow['con_chk'];?>";
var noteChk2 = "<?php echo $srow['cln_chk'];?>";
var tricom_rep='<?=TRICOM_REPORTS;?>';
</script>

<script src=/BSOS/scripts/tabpane.js></script>
<script language=javascript src=/BSOS/scripts/validatehresume.js></script>
<script language=javascript src=/BSOS/scripts/validatehhr.js></script>
<script language=javascript src=scripts/validateasn.js></script>
<script language=javascript src=/BSOS/scripts/validateaempresume.js></script>
<script type="text/javascript" src="/BSOS/scripts/calendar.js"></script>
<script type="text/javascript" src="/BSOS/scripts/jquery-min.js"></script>
<script type="text/javascript">
/* Added autopreloader function to show loading icon, until the page completely loads */
$(window).load(function(){
	$('#autopreloader').fadeOut('slow',function(){$(this).remove();});
	$('.newLoader').fadeOut('slow',function(){$(this).remove();});
});
</script>
<script>
	var madison = '<?=PAYROLL_PROCESS_BY_MADISON;?>';
	var syncHRDefault = '<?php echo DEFAULT_SYNCHR; ?>';
         var akkupayroll = '<?php echo DEFAULT_AKKUPAY ;?>';
	 
</script>
<?php
if(PAYROLL_PROCESS_BY_MADISON=='MADISON' || TRICOM_REPORTS == 'Y')
	echo "<script language=javascript src=/BSOS/scripts/formValidation.js></script>";
?>
</head>
<body>
	<form method=post name='conreg' id='conreg' action=newconreg15.php>
	<input type=hidden name='url' id='url' value="" />
	<input type=hidden name='dest' id='dest' value="" />
	<input type=hidden name=daction value='storeresume.php'>
	<input type=hidden name=page15 value='<?php echo $page15;?>'>
	<input type=hidden name=page13 value='<?php echo $page13;?>'>
	<input type=hidden name=page215 value='<?php echo $page215;?>'>
	<input type=hidden name=addr value="<?php echo $addr;?>">
	<input type=hidden name=Page215ass value='<?php echo $Page215ass;?>'>
	<input type=hidden name=conusername id=conusername value='<?php echo $conusername;?>'>
	<input type=hidden name=assignment value='<?php echo "accountactiveassign";?>'>
	<input type=hidden name=hireempname value="<?php echo  $row[0];?>">
	<input type=hidden name='acceptJobtitle' id='acceptJobtitle' value="" />
	<input type=hidden name='cancelstatus' id='cancelstatus' value="" />
	<input type=hidden name='hdnassign' id='hdnassign' value="" />
	<input type=hidden name=assign id="assign" value='<?php echo $assign; ?>'>

	<input type=hidden name='hterminate' id='hterminate' value="" />
	<input type=hidden name='hcloseasgn' id='hcloseasgn' value="" />
	<input type=hidden name='hterdate' id='hterdate' value="" />
	<input type=hidden name='henddate' id='henddate' value="" />

	<input type=hidden name='getJobStatus' id='getJobStatus' value="" />
	<input type=hidden name='getCandStatus' id='getCandStatus' value="" />
	<input type=hidden name='getNotesNew' id='getNotesNew' value="" />
	<input type=hidden name='getNotesType' id='getNotesType' value="" />
	<input type="hidden" name="theraphySourceEnable" id="theraphySourceEnable" value="<?php echo THERAPY_SOURCE_ENABLED;?>" />
	<?php
		$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
		$todate=date("m/d/Y",$thisday);
	?>
	<input type=hidden name='dateval' id='dateval' value="<?php echo $todate;?>">
	<input type="hidden" name='Supuser' value="<?php echo $Supusr;?>">
	<input type="hidden" name='usernme' value="<?php echo $conusername1;?>">
	<input type="hidden" name="ACC_AS_SESSIONRN" id="ACC_AS_SESSIONRN" value="<?php echo $ACC_AS_SESSIONRN;?>">
	<input type=hidden name="sm_form_data" id="sm_form_data" value="" />
	<input type="hidden" name="sm_enabled_option" id="sm_enabled_option" value="<?php echo $schedule_display; ?>" />
	<input type=hidden name="hrcon_recno" id="hrcon_recno" value="<?php echo $hrconJobSnoPerdiem;?>" />
	<style type="text/css" >
	/* .customProfile #leftflt input[type=text],.customProfile #leftflt input[name=pfee]{width: 100px !important;min-width: 100px !important;}
	.customProfile #hide-hire-sal select{width: 100px !important;min-width: 100px !important;}
	.customProfile select[name="perbill"]{width: 85px !important;}
	.customProfile select[name="paybill"]{width: 65px !important;} */
	.alert-ync-container, .alert-ync, .alert-ync-container{ height:100% !important ; min-height:99%; overflow:hidden}
	.alert-w-chckbox-chkbox-uanas-moz, .alert-w-chckbox-chkbox-uanas-ie{ overflow-y: auto; overflow-x: hidden;}
	#DHTMLSuite_modalBox_iframe{ display:none !important}
	.select2-container.select2-container-multi.required.selCdfCheckVal{width: 250px !important;}
	.cdfAutoSuggest select{min-width: 250px !important;}
	..summaryform-formelement select[name="sel_perdiem2"], .crmsummary-jocomp-table input[name="diem_billrate"]{width: 85px !important;min-width: 85px !important;}
	.subPadL-0{padding: 0px !important;}
	@media screen\0 {	
		/* IE only override */
	.managesymb { padding-top: 3px !important;}
	}

	.managesymb { margin: 2px 4px !important; }
	.closebtnstyle{ float:left; margin-top:1px; *margin-top:3px; vertical-align:middle; }
	.alert-ync-text {
		font-family: arial !important;
		font-size: 14px !important;
		margin-top: 0 !important;
	}
	.alert-ync-text span {
		font-weight: normal !important;
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

	.cdfCustTextArea {
		width: 390px !important;
	}
	.cdfJoborderBlk .select2-container, .cdfJoborderBlk .select2-drop, .cdfJoborderBlk .select2-search, .cdfJoborderBlk .select2-search input {
		width: 300px !important;
	}
	.maingridbuttonspad{ padding:0px;}
	.summarytext input[type="checkbox"]{margin: 5px 2px!important;}
	#reg_pay_Bill_NonBill > input[name="payrateBillOpt"],#reg_bill_Tax_NonTax > input[name="billrateTaxOpt"],#ot_pay_Bill_NonBill > input[name="OvpayrateBillOpt"],#ot_bill_Tax_NonTax > input[name="OvbillrateTaxOpt"],#dt_pay_Bill_NonBill > input[name="DbpayrateBillOpt"],#db_bill_Tax_NonTax > input[name="DbbillrateTaxOpt"]{margin-top:10px !important;}
	#commissionRows .summaryform-formelement-commrole input[type="text"]{width:95px !important;}
	#commissionRows .summaryform-formelement-commrole .managesymb{margin-top:10px !important;}
	@media screen and (-ms-high-contrast: active), (-ms-high-contrast: none) {
	#leftflt input[name="radio_billabletype"]{margin-top:0px !important;}
	#bill_Div #diem_billrate{margin-top:-5px !important;}
	#commissionRows .summaryform-formelement-commrole input[type="text"]{width:93px !important;}
	}
	.ui-state-active .ui-icon, .ui-state-default .ui-icon { background : rgba(0, 0, 0, 0) none repeat scroll 0 0}
	</style>
	<div id="main">
	<div class="mloadDiv"><div id="autopreloader"></div>
	<div class="newLoader"><img src="../../../BSOS/images/akkenloading_big.gif"></div>
	<td valign=top align=center>
	<table class="table">
		<div id="grid_form">
		<table class="table">
		<tr>
			<td width=100% valign=top align=center>
			<div class="tab-pane" id="tabPane2">
			<?php
				$modfrom="approve";
				$recassignVal=explode("|",$recno);

				//$pos_que="select posid,owner,assg_status,pusername,username from empcon_jobs where sno='".$recassignVal[0]."'";
				$pos_que="select posid,owner,ustatus,pusername,username from hrcon_jobs where sno='".$recassignVal[0]."'";
				$pos_res=mysql_query($pos_que,$db);
				$pos_row=mysql_fetch_row($pos_res);
				$jobPosVal = $pos_row[0];
							$refer_chk_row = $pos_row;// refer_bonus_manage

				$mode="editassign";
				$assignmentStatus = $pos_row[2];
				$assg_disable="disabled";
				$showAssignid=$pos_row[3];
				$mod = 7;
				$apprn = $ACC_AS_SESSIONRN;
				$assignment_mulrates = $_SESSION[assignment_mulrates.$ACC_AS_SESSIONRN];

							// Referral Bonus Manage
							$referral_bonus_exists ='';
							$que_ref="SELECT bonus_amount FROM cand_refer WHERE emp_id='".$recassignVal[3]."' AND req_id ='".$refer_chk_row[0]."' AND assign_id ='".$refer_chk_row[3]."'";
							$res_ref=mysql_query($que_ref,$db);
							$fetch_ref_recs=  mysql_num_rows($res_ref);
							if($fetch_ref_recs>0){
							   $referral_bonus_exists='YES';
							   $ref_bonus_row=mysql_fetch_row($res_ref);
							}

				require($app_inc_path."assignment.php");
			?>


	<style>
	/* #DHTMLSuite_modalBox_contentDiv{
		left: 360px !important;
		}
	#DHTMLSuite_modalBox_contentDiv{
		height: 254px !important;
	}
	#DHTMLSuite_modalBox_shadowDiv{
		height: 257px !important;
	}*/

	.timegrid{ width:2.070%;}
	@media screen and (max-width: 1200px) {
	   .timegrid{ width:2.07%; }

	}

	@media screen\0 {	
		/* IE only override */
	a.crm-select-link:link{ font-size:11px !important; }
	a.edit-list:link{ font-size:10px !important;}
	.summaryform-bold-close-title{ }
	.center-body { text-align:left !important;}
	.crmsummary-jocomp-table td{  text-align:left !important;}
	.summaryform-nonboldsub-title{ }
	#smdatetable{ font-size:11px !important;}
	.summaryform-formelement{ text-align:left !important; vertical-align:middle}
	.crmsummary-content-title{ text-align:left !important}
	.crmsummary-edit-table td{ text-align:left !important}
	.summaryform-nonboldsub-title{ }
	.sstabelwidth td{ font-size:11px !important;}
	}
	@media screen and (-webkit-min-device-pixel-ratio:0) { 
		/* Safari only override */
		::i-block-chrome,.timegrid { width:2.07%; }
		::i-block-chrome,.timehead { width:4%;}   
	}
	</style>
			</td>
		</tr>
		</table>
		</div>
	</table>
	</td>
	</div>
	<input type="hidden" name="assgnment_ownerid" value="<?php echo $pos_row[1] ?>">
	</form>
<?php require("footer.inc.php");?>
</body>
</html>