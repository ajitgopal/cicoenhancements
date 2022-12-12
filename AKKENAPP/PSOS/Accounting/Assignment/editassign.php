<?php  
	require("global.inc");
	require("dispfunc.php");
	 getJQLibs(['jquery']);
	require("Menu.inc");
	$menu=new EmpMenu();

	$Supusr = superusername();
	$page15 = $_SESSION[page15.$ACC_AS_SESSIONRN];
	$page215 = $_SESSION[page215.$ACC_AS_SESSIONRN];
	$apprn = $ACC_AS_SESSIONRN;
	if(isset($copyasign)){
		$copyasign = $copyasign;
	}else{
		$copyasign ="no";
	}
	$elements=explode("|",$page15);
	
        // saved worksite code retrieval
        $worksitecode =  $elements[94];  
        $daypay =  $elements[95];
        $licode =  $elements[96];
        $federal_payroll =  $elements[97];
        $nonfederal_payroll = $elements[98];
        $contractid =  $elements[99];
        $classification = $elements[100];
		$assignment_timesheet_layout_preference = $elements[101];
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
	$candidateNameSno=$elements[1];
	$que="select name, ".getEntityDispName("sno","name")." from emp_list where username='".$elements[1]."'";
	$res=mysql_query($que,$db);
	$row=mysql_fetch_row($res);
	$candidateName=stripslashes($row[1]);
	$conusername1=$elements[1];

	$spl_Attribute = (PAYROLL_PROCESS_BY_MADISON=='MADISON') ? 'udCheckNull ="YES" ' : '';


	$sque="SELECT jcs.acc_chk,jcs.acc_sel,jcs.acca_chk,jcs.coc_chk,jcs.coc_sel,jcs.coca_chk,jcs.coj_chk,jcs.coj_sel,jcs.coja_chk,jcs.con_chk,jcs.con_sel,jcs.cona_chk,jcs.clc_chk,jcs.clc_sel,jcs.clca_chk,jcs.clj_chk,jcs.clj_sel,jcs.clja_chk,jcs.cln_chk,jcs.cln_sel,jcs.clna_chk,jcs.mdate FROM job_cand_status jcs";
	$sres=mysql_query($sque,$db);
	$srow=mysql_fetch_array($sres);
	$jcscou=mysql_num_rows($sres);

	if($srow['coja_chk']=="Y")
		$jo_stat1="<select name=jostat1 id=jostat1>";
	else
		$jo_stat1="<select name=jostat1 id=jostat1 disabled>";

	if($srow['clja_chk']=="Y")
		$jo_stat2="<select name=jostat2 id=jostat2>";
	else
		$jo_stat2="<select name=jostat2 id=jostat2 disabled>";

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
		$cand_stat1="<select name=candstat1 id=candstat1>";
	else
		$cand_stat1="<select name=candstat1 id=candstat1 disabled>";

	if($srow['clca_chk']=="Y")
		$cand_stat2="<select name=candstat2 id=candstat2>";
	else
		$cand_stat2="<select name=candstat2 id=candstat2 disabled>";

	if($srow['acca_chk']=="Y")
		$cand_stat3="<select name=candstat3 id=candstat3>";
	else
		$cand_stat3="<select name=candstat3 id=candstat3 disabled>";

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

		$note_sno = str_replace('"', '', $note_row[0]);
		$note_name = str_replace('"', '', $note_row[1]);
		$srow['con_sel'] = str_replace('"', '', $srow['con_sel']);
		$srow['cln_sel'] = str_replace('"', '', $srow['cln_sel']);

		$note_stat1.="<option value=".$note_sno." ".sele($note_sno,$srow['con_sel']).">".$note_name."</option>";
		$note_stat2.="<option value=".$note_sno." ".sele($note_sno,$srow['cln_sel']).">".$note_name."</option>";
	}

	$note_stat1.="</select>";
	$note_stat2.="</select>";
	
	//Defining a variable for showing mandatory SyncHR star marks from this page only.
	$showMandatoryAstrik = "Y";
	
	$schedule_display = "NEW";
	//get the shift scheduling display mode whether it is old./new associated to the assignment
	if(SHIFT_SCHEDULING_ENABLED=='N')
	{
		$schedule_display = "OLD";
	}
?>
<?php include('header.inc.php') ?>
<title>Assignments</title>
<style>
@media all and (-ms-high-contrast: none), (-ms-high-contrast: active) {
   .summaryform-formelement{height: 33px !important;}
}
#multipleRatesTab .crmsummary-jocomp-table input[type=text], #multipleRatesTab .crmsummary-jocomp-table select{width: 100px !important;min-width: 100px !important;}
.crmsummary-jocomp-table select[name="perbill"]{width: 85px !important;}
.crmsummary-jocomp-table select[name="paybill"]{width: 65px !important;}
.alert-ync-container, .alert-ync, .alert-ync-container{ height:99%; min-height:99%}
.alert-w-chckbox-chkbox-uanas-moz, .alert-w-chckbox-chkbox-uanas-ie{ overflow-y: auto; overflow-x: hidden;}
#DHTMLSuite_modalBox_iframe{ display:none !important}
.panel-table-content-new .summaryform-bold-title { font-size:12px; font-weight:bold;}
#modal-glow{ width:100%; position:fixed;}
@media screen\0 {	
	/* IE only override */
.managesymb { padding-top: 3px !important;}
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
.alert-ync-text {
    font-size: 14px !important;
    margin-top: 0 !important;
}
.alert-ync-text span {
    font-weight: normal !important;
}
@media screen and (-ms-high-contrast: active), (-ms-high-contrast: none) {
 #readMoreShiftData{float: left !important; margin-left: 50% !important;}
}
.cdfCustTextArea {
    width: 390px !important;
}

.cdfJoborderBlk .select2-container, .cdfJoborderBlk .select2-drop, .cdfJoborderBlk .select2-search, .cdfJoborderBlk .select2-search input {
    width: 250px !important;
}
.cdfAutoSuggest select{min-width: 250px !important;}
.summarytext input[type="checkbox"]{margin: 5px 2px!important;}
.maingridbuttonspad{ padding:0px;}
.subPadL-0{padding: 0px !important;}
#reg_pay_Bill_NonBill > input[name="payrateBillOpt"],#reg_bill_Tax_NonTax > input[name="billrateTaxOpt"],#ot_pay_Bill_NonBill > input[name="OvpayrateBillOpt"],#ot_bill_Tax_NonTax > input[name="OvbillrateTaxOpt"],#dt_pay_Bill_NonBill > input[name="DbpayrateBillOpt"],#db_bill_Tax_NonTax > input[name="DbbillrateTaxOpt"],#leftflt input[name="radio_billabletype"]{margin-top:10px !important;}
#commissionRows .summaryform-formelement-commrole input[type="text"]{width:95px !important;}
#commissionRows .summaryform-formelement .managesymb{margin-top:10px !important;}
@media screen and (-ms-high-contrast: active), (-ms-high-contrast: none) {
#commissionRows .summaryform-formelement-commrole input[type="text"]{width:93px !important;}
}

</style>
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/tab.css">
<link rel="stylesheet" type="text/css" media="screen" href="/BSOS/css/sphinx_modalbox.css" />
<link href="/BSOS/css/MyProfileCustomStyles.css" rel="stylesheet" type="text/css">
<script src=/BSOS/scripts/tabpane.js></script>
<script language=javascript src=/BSOS/scripts/validatehresume.js></script>
<script language=javascript src=/BSOS/scripts/validatehhr.js></script>
<script language=javascript src=scripts/validateasn.js></script>
<script language=javascript src=/BSOS/scripts/validateaempresume.js></script>
<script language=javascript src="/BSOS/scripts/jquery-min.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/jquery.modalbox.js"></script>
<script type="text/javascript" src="/BSOS/scripts/shift_schedule/jquery.modalbox.js"></script> 
<?php
if($copyasign == "yes"){
?>
<script type="text/javascript" src="/BSOS/scripts/validateremphr.js"></script>
<?php } ?>
<script>
	var madison='<?=PAYROLL_PROCESS_BY_MADISON;?>';
	var syncHRDefault = '<?php echo DEFAULT_SYNCHR; ?>';
         var akkupayroll = '<?php echo DEFAULT_AKKUPAY ;?>';
		  var tricom_rep='<?=TRICOM_REPORTS;?>';
		  var symmetry='<?=SS_ENABLED;?>'; 
</script>

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
</script>
<script type="text/javascript">
/* Added autopreloader function to show loading icon, until the page completely loads */
$(window).load(function(){
	$('#autopreloader').fadeOut('slow',function(){$(this).remove();});
	$('.newLoader').fadeOut('slow',function(){$(this).remove();});
});
</script>
<?php
if(PAYROLL_PROCESS_BY_MADISON=='MADISON')
	echo "<script language=javascript src=/BSOS/scripts/formValidation.js></script>";
?>
<style>
.timegrid{ width:2.052% !important}


@media screen\0 {	
	/* IE only override */
.summaryform-formelement{ height:18px; font-size:11px !important; }
a.crm-select-link:link{ font-size:11px !important; }
a.edit-list:link{ font-size:10px !important;}
.summaryform-bold-close-title{ font-size:9px !important;}
.center-body { text-align:left !important;}
.crmsummary-jocomp-table td{ text-align:left !important;}
.summaryform-nonboldsub-title{ font-size:9px}
#smdatetable{ font-size:11px !important;}
.summaryform-formelement{ text-align:left !important; vertical-align:middle}
.crmsummary-content-title{ text-align:left !important}
.crmsummary-edit-table td{ text-align:left !important}
.summaryform-nonboldsub-title{ font-size:10px !important;}
.smdaterowclass td, .timehead{ font-size:11px !important;}
}
@media screen and (-webkit-min-device-pixel-ratio:0) { 
    /* Safari only override */
    ::i-block-chrome,.timegrid { width:2.07%; }
	::i-block-chrome,.timehead { width:4%;}   
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
.mloadDiv #autopreloader { position: fixed; left: 0; top: 0; z-index: 99999; width: 100%; height: 100%; overflow: visible; 
opacity:0.35;background:#000000 !important;}
.mloadDiv .newLoader{position:absolute; top:50%; left:50%;z-index:99999;margin-left:-67px;margin-top:-67px;width:150px;height:140px;}
.mloadDiv .newLoader img{border-radius:69px !important;}
.modalDialog_transparentDivs{z-index: 99998;}
</style>
</head>

<body onload="javascript:window.focus();hideElements('onload');">
<form method=post name=conreg id=conreg action=newconreg15.php>
<input type=hidden name=url>
<input type=hidden name=dest>
<input type=hidden name=daction value='storeresume.php'>
<input type=hidden name=page15 value='<?php echo $page15;?>'>
<input type=hidden name=page13 value='<?php echo $page13;?>'>
<input type=hidden name=page215 value='<?php echo $page215;?>'>
<input type=hidden name=addr value="<?php echo $addr;?>">
<input type=hidden name=Page215ass value='<?php echo $Page215ass;?>'>
<input type=hidden name=conusername id=conusername value='<?php echo $conusername;?>'>
<input type=hidden name=assignment value='<?php echo "accountactiveassign";?>'>
<input type=hidden name=hireempname value="<?php echo  dispTextdb($row[0]);?>">
<input type=hidden name=madisonassid value='<?php echo $madisonassid;?>'>
<input type=hidden name=acceptJobtitle>
<input type=hidden name='hdnassign' id='hdnassign' value="Edit" />
<input type=hidden name='assign' id='assign' value=<?php echo $assign;?>> 
<input type=hidden name='hterminate' id='hterminate' value="" />
<input type=hidden name='hcloseasgn' id='hcloseasgn' value="" />
<input type=hidden name='hterdate' id='hterdate' value="" />
<input type=hidden name='henddate' id='henddate' value="" />

<input type=hidden name='getJobStatus' id='getJobStatus' value="" />
<input type=hidden name='getCandStatus' id='getCandStatus' value="" />
<input type=hidden name='getNotesNew' id='getNotesNew' value="" />
<input type=hidden name='getNotesType' id='getNotesType' value="" />

<?php
	$pid_array = explode('|',$elements[0]);
	$pid = $pid_array[0];
?>
<!-- This hidden variable is used for Rate On shift Enhancement to get the hrcon_jobs sno  -->
<input type=hidden name='hrcon_recno' id='hrcon_recno' value="<?php echo $pid;?>" />

<?php
	$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todate=date("m/d/Y",$thisday);
?>
<input type=hidden name=dateval value="<?php echo $todate;?>">
<input type="hidden" name='Supuser' value="<?php echo $Supusr;?>">
<input type="hidden" name='usernme' value="<?php echo $conusername1;?>">
<input type="hidden" name="hdnDropVal" id="hdnDropVal" value="">
<input type="hidden" name="ACC_AS_SESSIONRN" id="ACC_AS_SESSIONRN" value="<?php echo $ACC_AS_SESSIONRN;?>">
<input type="hidden" name="source" id="source" value="<?php echo $source;?>">
<input type="hidden" name="rowid" id="rowid" value="<?php echo $rowid;?>">
<input type=hidden name="sm_form_data" id="sm_form_data" value="" />
<input type="hidden" name="sm_enabled_option" id="sm_enabled_option" value="<?php echo $schedule_display; ?>" />
<input type="hidden" name="theraphySourceEnable" id="theraphySourceEnable" value="<?php echo THERAPY_SOURCE_ENABLED;?>" />
<div id="main">
<div class="mloadDiv"><div id="autopreloader"></div>
<div class="newLoader"><img src="../../../BSOS/images/akkenloading_big.gif"></div>
<td valign=top align=center>
<table width=100% cellpadding=0 cellspacing=0 border=0>
	<div id="content">
	<tr>
		<td>
		<table width=100% cellpadding=0 cellspacing=0 border=0>
			<tr>
			<td colspan=2><font class=bstrip>&nbsp;</font></td>
		</tr>
		</table>
		</td>
	</tr>
	</div>

	<div id="grid_form">
	<table border="0" width="100%" cellspacing="5" cellpadding="0">
	<tr>
	<td width=100% valign=top align=center>
	<div class="tab-pane" id="tabPane2">	
		<?php
		$recassignVal=explode("|",$recno);
        //to pass employee id for populating work site codes in assignments
        $candID = $recassignVal[3];
		//if($recassignVal[2] == 'closed' || $recassignVal[2] == 'cancelled' ){

			$pos_que="select posid,owner,ustatus,pusername,username from hrcon_jobs where sno='".$recassignVal[0]."'";
			$modfrom="updateasgmt";
		/*}else{
			$pos_que="select posid,owner,assg_status,pusername,username from empcon_jobs where sno='".$recassignVal[0]."'";
			
		}*/
		$pos_res=mysql_query($pos_que,$db);
		$pos_row=mysql_fetch_row($pos_res);
                $refer_chk_row = $pos_row;//referral Bonus manage
		$jobPosVal = $pos_row[0];
		$assignmentStatus = $pos_row[2];
		$assg_disable="";
		$mode="editassign";
		$showAssignid=$pos_row[3];
		$mod = 7;
		$apprn = $ACC_AS_SESSIONRN;
		$assignment_mulrates = $_SESSION[assignment_mulrates.$ACC_AS_SESSIONRN];
		if($copyasign == 'yes'){
		$enames="";
		$query="SELECT username,name, ".getEntityDispName("sno","name")." FROM emp_list WHERE lstatus != 'DA' AND lstatus != 'INACTIVE' AND (empterminated!='Y' || UNIX_TIMESTAMP(IF(tdate='' || tdate IS NULL,NOW(),tdate))>UNIX_TIMESTAMP(NOW())) ORDER BY name";
				$result=mysql_query($query,$db);
				while($myrow=mysql_fetch_row($result))
				{
		            $enames.="<option value='".$myrow[0]."' ".sele($myrow[0],$candidateNameSno).">".$myrow[2]."</option>";			
				}

		}
                
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
	</td>
	</tr>
	</table>
	</div>
</table>
</td>
</div>
<input type="hidden" name="assgnment_ownerid" value="<?php echo $pos_row[1] ?>">
</form>
</div>
<?php include('footer.inc.php') ?>
</body>
</html>