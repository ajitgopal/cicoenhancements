<?php
	require_once('global.inc');
	require($akken_psos_include_path.'commonfuns.inc');
	require_once('timesheet/class.timeintimeout.php');
	require("class.Notifications.inc");

	$XAJAX_ON	= "YES";
	$XAJAX_MOD	= "ClockInClockOutTimeSheets";

	global $db;
	$GridHS	= true;

	require_once('Menu.inc');

	$titleTxt = "Clock In & Out";
	$menu=new EmpMenu();
	$menu->showHeader("accounting",$titleTxt,"1|1");
	 
	$objTimeInTimeOut	= new TimeInTimeOut($db);
	$layout_preference	= $objTimeInTimeOut->getTSLayoutPreference();
	
	if(!isset($val) || $val == "")
	{
		if ($servicedate =="") {
			$servicedate = date("m/d/Y",mktime(0,0,0,date("m"),date("d"),date("Y")));
		}
		if ($servicedateto =="") {
			$servicedateto = date("m/d/Y",mktime(0,0,0,date("m"),date("d"),date("Y")));
		}		
	}
	else
	{
		if($val=="serv")
		{
			$thisday1=$t1;
			$servicedate=date("m/d/Y",$t1);                 
			$servicedateto=$t2;
			$t21=explode("/",$t2);
			$thisday2= mktime (0,0,0,$t21[0],$t21[1],$t21[2]);
			$todaf=date("Y-m-d",$thisday2);
			$tod=date("Y-m-d",$t1);     
			$sno=$addr1;  
            $servicedatedefault= $servicedate;//Time Sheet Load Optimization
		}
		else if($val=="servto")
		{
			$servicedate=$t1;                         
			$servicedateto=date("m/d/Y",$t2);  
			$t11=explode("/",$t1);
			$thisday1= mktime (0,0,0,$t11[0],$t11[1],$t11[2]);
			$todaf=date("Y-m-d",$t2);
			$tod=date("Y-m-d",$thisday1);
			$sno=$addr1;
            $servicedatedefault= $servicedate;//Time Sheet Load Optimization
		}
		else
		{	
			if ($servicedate !="") {
				$servicedate=date("m/d/Y",$servicedate);
			}else{
				$servicedate = date("m/d/Y",mktime(0,0,0,date("m"),date("d"),date("Y")));
			}

			if ($servicedateto !="") {
				$servicedateto=date("m/d/Y",$servicedateto);
			}else{
				$servicedateto = date("m/d/Y",mktime(0,0,0,date("m"),date("d"),date("Y")));
			} 
			$t11=explode("/",$servicedate);
			$thisday1= mktime (0,0,0,$t11[0],$t11[1],$t11[2]);
			$tod=date("Y-m-d",$thisday1);
			$t21=explode("/",$servicedateto);				 
			$thisday2= mktime (0,0,0,$t21[0],$t21[1],$t21[2]);
			$todaf=date("Y-m-d",$thisday2);
            $servicedatedefault= $servicedate;//Time Sheet Load Optimization
		}
    }
    //echo $servicedate." => ".$servicedateto;
	$txtsdate = date("m/d/Y",mktime(0,0,0,date("m"),date("d"),date("Y")));
	$txtedate = date("m/d/Y",mktime(0,0,0,date("m"),date("d"),date("Y")));
	
	//Checking lcount for outgoing mail setup - class file already declared in class.Notifications.inc file which is included at the top
	$getlockmlcnt 	= $placementmailSetup->getSetupMailIDNlCnt("cico");
	$explodeData 	= explode('^',$getlockmlcnt);
	$lcount 		= $explodeData[1];
?>
<?php require("header.inc.php") ?>
<script type="text/javascript" src="/BSOS/scripts/common.js"></script>
<script type="text/javascript" src="/BSOS/scripts/preferences.js"></script>
<script type="text/javascript" src="/BSOS/scripts/common_ajax.js"></script>
<script type="text/javascript" src="/BSOS/scripts/date_format.js"></script>
<script type="text/javascript" src="/BSOS/scripts/ts_menu.js"></script>
<script type="text/javascript" src="scripts/validatetimefax.js"></script>
<script type="text/javascript" src="scripts/validatecico.js"></script>
<script type="text/javascript">

function openNewWindow()
{
	var v_heigth = 600;
	var v_width	= 1200;
	var form	= document.timesheet;

	var tmode		= gridActData[gridRowId][15];
	var ts_sno		= gridActData[gridRowId][19];
	var result		= gridActData[gridRowId][20];	
	var edit_ts		= gridActData[gridRowId][21];
	
	if(result!="" && result!=null)
	{
		var url = "cico_showfxdet.php?sno="+result+"&tmode="+tmode+"&openerType=Default&ts_sno="+ts_sno+"&edit_ts="+edit_ts;

		var name	= "savedtimesheet";
		var top1	= (window.screen.availHeight-v_heigth)/2;
		var left1	= (window.screen.availWidth-v_width)/2;
		var remoter	= window.open(url, name, "width="+v_width+"px,height="+v_heigth+"px,resizable=yes,scrollbars=yes,left="+left1+"px,top="+top1+"px,status=0");
		remoter.focus();
	}
}


var pageName	= 'MainGrid';

</script>
<style>
.active-column-6 .active-box-resize {}
.active-column-6 {width: 90px;}
.dynsndiv {	width: 100%;height:100%;top:0px;z-index:9998;position:fixed !important;	filter:alpha(opacity=50);background-color:#000;	opacity:0.55;}
div#tcal{ z-index:9999}
.alert-ync-text-mt{ color:#474c4f}
.fa.fa-calendar{margin-left: 5px;}
.titleNewPad{ padding-top:10px;}
</style>
</script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popupMsgArray.js"></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popup-message.js"></script>
<script type="text/javascript" src="/BSOS/scripts/calendar.js"></script>
<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="/BSOS/css/calendar.css">
<link rel="stylesheet" href="/BSOS/popupmessages/css/popup_message.css" media="screen" type="text/css">
<link rel="stylesheet" href="/BSOS/css/popup_styles.css" media="screen" type="text/css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/timeSheetselect2.css">
<style type="text/css">
.checkmark{ top:0px}
.active-column-0{ width:40px !important}
</style>
<form action="empfaxhis.php" name="timesheet" method="post">
<input type=hidden name="aa" id="aa">
<input type=hidden name="addr" id="addr" value=<?php echo $addr;?>>
<input type=hidden name="t1" id="t1" value=<?=$tod?>>
<input type=hidden name="t2" id="t2" value=<?=$todaf?>>
<input type=hidden name="val" id="val">
<input type="hidden" name="getApproveStatus" id="getApproveStatus">
<input type="hidden" name="getempusername" id="getempusername">
<input type="hidden" name="details" id="details">
<input type="hidden" name="rsdate" id="rsdate">
<input type="hidden" name="redate" id="redate">
<input type=hidden name="history" id="history" value="no">
<input type=hidden name="Par_Timesheet_Val" id="Par_Timesheet_Val" value="<?php echo $Par_Timesheet_Val;?>">
<input type=hidden name="Approve_Status" id="Approve_Status">
<!-- passing hidden value for lcount for checking in the js function to send notification mail -->
<input type='hidden' name='lcount' id='lcount'value='<?php echo $lcount;?>' >
<div id="tque"></div>
<div id="oque"></div>
	<div id="main">
		<td valign="top" align="center">
			<table width="100%" cellpadding="0" cellspacing="0" border="0" class="ProfileNewUI" align="center">
				<div id="content">
					<tr>
						<td class="titleNewPad">
							<table width="100%" cellpadding="0" cellspacing="0" border="0" class="defaultTopRange">								
								<tr>
									<td>
										<font class="modcaption">Clock In & Out&nbsp;</font><br>
										<span style="margin-left:16px;">Select HRM Department 
											<?php 
											$dep_qry = getDepartments('cico_filter_dpt', '', 'cico_filter_dpt','','','','yes','','','yes');
											$res_department =  mysql_query($dep_qry,$db);
											?>
											
											<select name="cico_filter_dpt[]" id="cico_filter_dpt" multiple=multiple style="width:265px" class='afontstyle'>
												<?php
												while($srow=mysql_fetch_row($res_department))
												{
													$selected = in_array($srow[0], $cico_filter_dpt) ? 'selected="selected"' : '';
													echo "<option value='".$srow[0]."' ".$selected.">".$srow[1]."</option>";
												}
												?>
											</select>
											<?php getJQLibs(['multipleselect']);?>
											<script>
												$(function () {
													$('#cico_filter_dpt').multipleSelect({placeholder: "All", width: 263, filter: true,maxHeight: 120});
												});
											</script>
											<style>
											.ms-drop ul {
												text-align:left!important;
											}
											.ms-choice span {
												margin-left:1px!important;
											}
											</style>
										<span>
									</td>
								
									<td align="right">
									
										
										
										<span> From</span>
										<span class="TMEDateBg"><input type="text" size="10"  maxlength="10" name="servicedate" id="servicedate" readonly="read"  value="<?php echo $servicedate;?>"><script language='JavaScript'>new tcal ({'formname':window.form,'controlname':'servicedate'});</script>
										<span class="FontSize-16 TMEPadLR-6">To</span><input type="text" size="10" name="servicedateto" id="servicedateto" readonly="read" maxlength="10" value="<?php echo $servicedateto;?>"><script language='JavaScript'>new tcal ({'formname':window.form,'controlname':'servicedateto'});</script></span><span class="TMEDateViewBtn"><a href=javascript:DateCheck('servicedate','servicedateto')><i class="fa fa-eye fa-lg"></i> View</font></a></span>
									</td>
									<td ><font class="bstrip">&nbsp;</font></td>
								</tr>       
                            </table>
						</td>
					</tr>
					<tr>
						<td colspan="2"><font class="bstrip">&nbsp;</font></td>
						<td ><font class="bstrip">&nbsp;</font></td>
					</tr>
				</div>
				
				<?php
				//getting clock in template id
				$sqlClockInTempQuery1 = "SELECT nt.id as temp_id FROM notifications_templates nt LEFT JOIN users u ON u.username = nt.muser WHERE 
					nt.template_type = 'ClockIn' AND nt.mod_id = 'cico' AND nt.status = 'ACTIVE'";
				$sqlClockInTempQuery  = mysql_query($sqlClockInTempQuery1,$db);
				$resClockInTemp  = mysql_fetch_assoc($sqlClockInTempQuery);
				$clockIn_TempId = $resClockInTemp['temp_id'];
				
				//getting clock out template id
				$sqlClockOutTempQuery1 = "SELECT nt.id as temp_id FROM notifications_templates nt LEFT JOIN users u ON u.username = nt.muser WHERE 
					nt.template_type = 'ClockOut' AND nt.mod_id = 'cico' AND nt.status = 'ACTIVE'";
				$sqlClockOutTempQuery  = mysql_query($sqlClockOutTempQuery1,$db);
				$resClockOutTemp  = mysql_fetch_assoc($sqlClockOutTempQuery);
				$clockOut_TempId = $resClockOutTemp['temp_id'];
				
				?>
	
				<div id="topheader">
					<tr class="NewGridTopBg">
					<?php
						
						$menuname	= $ntime_rules."fa-newspaper-o~New&nbsp;Timesheet&nbsp";
						$menuname.= "|fa-clock-o~Send&nbsp;Reminders|droplist";
						
						$name	= explode("|",$menuname);
							
						$linkdata = "javascript:newClockinout();|javascript:;|<a href=\"javascript:doCicoNotify('".$clockIn_TempId."');\">Notify Employee for Clock In</a>~<a href=\"javascript:doCicoNotify('".$clockOut_TempId."');\">Notify Employee for Clock Out</a>";
						
					
						$link	= explode("|",$linkdata);
						$heading="";
						$menu->showMainGridHeadingStrip1($name,$link,$heading);
					?>
					</tr>
				</div>
				<div id="grid_form">
				  <tr>
					<td>
					
					<?php
					//fetching categories list
					$search_cat_list	= "<select class=gridserbox id=aw-column4 name=aw-column4 onChange=doSearchResetCat()><option value=''>All</option>";
					$scque		= "select sno,name from manage where type='jocategory' order by name";
					$scres		= mysql_query($scque,$db);

					while($scrow = mysql_fetch_row($scres)) {
						$search_cat_list.= "<option value='".$scrow[1]."' title='".$scrow[1]."'>".$scrow[1]."</option>";
					}

					$search_cat_list.="</select>";
					?>
					
					<script>
							var gridHeadCol = ["<label class='container-chk'><input type=checkbox name=chk id=chk onClick=mainChkBox_ProcessedRecords(this,document.forms[0],'auids[]')><span class='checkmark'></span></label>",
							"<?php echo getEntityDispHeading('ID', 'Employee Name');?>",
							"Primary Phone",
							"Assignment&nbsp;ID",
							"Category",
							"Job&nbsp;Title",
							"<?php echo getEntityDispHeading('ID', 'Customer Name');?>",
							"Work&nbsp;Location",
							"Shift Time",
							"Shift Status",
							"Start&nbsp;Date",
							"Time",
							"End&nbsp;Date",
							"Time",
							"Total&nbsp;Hours",
							"Timesheet&nbsp;Status",
							"Saved/Submitted&nbsp;On",
							"Modified&nbsp;Date",
							"Modified&nbsp;By"];
							var gridHeadData = ["",
							"<input class=gridserbox type=text name=aw-column1 id=aw-column1 size=15>",
							"<input class=gridserbox type=text name=aw-column2 id=aw-column2 size=15>",
							"<input class=gridserbox type=text name=aw-column3 id=aw-column3 size=15>",
							"<?php echo $search_cat_list;?>",
							"<input class=gridserbox type=text name=aw-column5 id=aw-column5 size=15>",
							"<input class=gridserbox type=text name=aw-column6 id=aw-column6 size=15>",
							"<input class=gridserbox type=text name=aw-column7 id=aw-column7 size=15>",
							"<input class=gridserbox type=text name=aw-column8 id=aw-column8 size=15>",
							"<select class=gridserbox id=aw-column9 name=aw-column9 onChange=doSearchResetCat()><option value=''>All</option><option value='Active'>Active</option><option value='Re-assigned'>Re-assigned</option><option value='Cancelled'>Cancelled</option></select>",
							"<input class=gridserbox type=text name=aw-column10 id=aw-column10 size=15>",
							"<input class=gridserbox type=text name=aw-column11 id=aw-column11 size=15>",
							"<input class=gridserbox type=text name=aw-column12 id=aw-column12 size=15>",
							"<input class=gridserbox type=text name=aw-column13 id=aw-column13 size=15>",
							"<input class=gridserbox type=text name=aw-column14 id=aw-column14 size=15>",
							"<select class=gridserbox id=aw-column15 name=aw-column15 onChange=doSearchResetCat()><option value=''>All</option><option value='Saved'>Saved</option><option value='ER'>Submitted</option></select>",
							"<input class=gridserbox type=text name=aw-column16 id=aw-column16 size=15>",
							"<input class=gridserbox type=text name=aw-column17 id=aw-column17 size=15>",
							"<input class=gridserbox type=text name=aw-column18 id=aw-column18 size=15>"];
							var gridActCol = ["","","","","","","","","","","","","","","","","","",""];
							var gridActData = [];
							var gridValue = "Accounting_ClockInClockOutTimeSheets";
							gridSortCol=16;
							gridSort="DESC";
							gridForm=document.forms[0];
							gridSearchResetColumn="";
							initGrids(19);
							gridExtraFields = new Array();
							gridExtraFields['servicedate']='<?php echo $servicedate;?>';
							gridExtraFields['servicedateto']='<?php echo $servicedateto;?>';
							gridExtraFields['cico_filter_dpt']='<?php echo (count($cico_filter_dpt) > 0) ? implode(",", $cico_filter_dpt) : ""; ?>';
                            				gridExtraFields['servicedatesearched']='<?php echo $servicedatedefault;?>';
							xajax_gridData(gridSortCol,gridSort,gridPage,gridRecords,gridSearchType,gridSearchFields,gridExtraFields);
					</script>
					</td>
				  </tr>
       			 </div>
			</table>
		</td>
	</div>
<tr>
<?php
	$menu->showFooter();
?>
</tr>
</form>
<div id="dynsndiv" class="dynsndiv" style="display:none;"></div>
<?php require("footer.inc.php") ?>
<script type="text/javascript">
$('a').each(function()
{
	if($(this).text() == 'Update Status')
		$(this).attr('class', 'link6 timeupdatestatus');
});
</script>
</body>
</html>