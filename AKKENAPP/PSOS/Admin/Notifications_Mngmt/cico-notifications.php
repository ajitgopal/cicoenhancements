<?php

   	require("global.inc");
	require("activewidgets.php");
	require("functions.php");
	require("Menu.inc");
	require("class.Notifications.inc");
	$menu=new EmpMenu();
	$menu->showHeader("admin","Notifications&nbsp;Management","41|1");
	global $db;
	
	$cico_viewmode = "save";
	$mode = 'cico';
	$pageTitle = "Notification Management";
	
	$objNotifications = new Notifications();
	$cicoNotDetails 	= $objNotifications->getNotification($mode);

	if($cicoNotDetails['viewmode'] == 'edit'){
		$cico_viewmode = "edit";
	}
	
	/*---------------------OUTGOING MAIL CONFIGRATION SETUP QUERY-------------------*/

	$sqlEmail = "SELECT sno, server_type, disname, mailid FROM notification_email_setup WHERE status = 'ACTIVE'";
	$resEmail = mysql_query($sqlEmail,$db);

	/*---------------------OUTGOING MAIL CONFIGRATION SETUP QUERY ENDS--------------*/
	
?>
<?php include("header.inc.php");?>
<link rel="stylesheet" href="/BSOS/ListMenu/css/listMenu.css">
<script type="text/javascript">

var sform_clean;
var used_submit = 0;
$(function() {
   	sform_clean = $("form").serialize(); 
});

var onBeforeUnloadFired = false;
window.onbeforeunload = confirmExit;
function resetOnBeforeUnloadFired() {
	onBeforeUnloadFired = false;
}
 function confirmExit(e) {
	var sform_dirty = $("form").serialize();
	if (sform_clean != sform_dirty)
	{	
		if(!onBeforeUnloadFired){
			if (/MSIE (\d+\.\d+);/.test(navigator.userAgent))  {
				e = window.event || e; 
				if ((e.clientX < 0) || (e.clientY < 0) || window.event.altKey == true)
				{ 
					e.returnValue = 'Your changes, that is not saved. \nClick on "Leave this " to close the window without saving changes. \nCick on "Stay on this page" to go back and save the changes.'; 
					onBeforeUnloadFired = true;
					window.onbeforeunload = null; 
					setTimeout("enableBeforeUnloadHandler()", "100"); 
				}
			} 
			else
				return 'Your changes, that is not saved. \nClick on "Leave this " to close the window without saving changes. \nCick on "Stay on this page" to go back and save the changes.';
				onBeforeUnloadFired = true;
				window.onbeforeunload = null;
				setTimeout("enableBeforeUnloadHandler()", "200"); 
		}
	}
}
window.setTimeout("resetOnBeforeUnloadFired()", 1000);
function enableBeforeUnloadHandler() { window.onbeforeunload = confirmExit; }

</script>
<script src="/BSOS/ListMenu/js/listMenu.js"></script>	
<script type="text/javascript" src="/BSOS/scripts/ui.core-min.js"></script>
<script type="text/javascript" src="/BSOS/scripts/ui.dropdownchecklist-min.js"></script>
<script language="javascript" src="scripts/validatefields.js"></script>
<script type="text/javascript" src="/BSOS/scripts/AkkuBi/jquery.slimscroll.js"></script> 
<style>
	.ActionMenuLeftFixed{position:inherit}
	.subcontent{height: auto;}
	#sidebar{z-index:99;}
	.credlink{font-size: 12px;color:#474c4f;font-weight:bold;}
	.credlink:hover{color: #095ba1;font-weight: bold;text-decoration: none;}
	#emprow{display: none;}
	.fa-info-circle{cursor: pointer;}
	/*-- Pop up CSS  - Ankit--*/
	.akkenNewClose i.fa-2x:before{ color:#89979f !important; font-size:20px;}
	.preloaderTempl{ position:absolute; margin-left:-16px; margin-top:-16px; top:50%; left:50%; }
	.notificationmodal-wrapper{ top:50% !important; left:50% !important; margin-top:-90px !important; margin-left:-277px !important;  }
	.assDetail 
	{
	    display: none;
	    padding: 0px;
	    border: 1px solid #ccc !important;
	    border-top: none;
	}
	.assDetail table tr th{ font-size:13px;}
	#mainbody {
		padding-bottom: 50px;
	}
	/*-- Pop up CSS  - Ankit--*/
.notifiM{ border:solid 1px #ccc; border-radius:4px; margin-top:10px; width:100%; float:left; margin-right:20px;}
.notifiHed{ border-bottom:solid 1px #ccc; padding:5px 10px; line-height:24px;}
.notifiHedCon{font-size:13px; float:left; font-weight:bold; line-height:30px;}
.massCommuEmailTbl{padding:10px 5px 15px 5px;}
.massCommuEmailTbl table tr th, .massCommuEmailTbl table tr td{ font-size:13px; text-align:left; }
.massCommuEmailTbl table tr td a:hover{ font-weight:normal;}
.notifiBtnActive a{background:#3eb8f0; padding:2px 8px; border-radius:4px; color:#fff;border:solid 1px #3eb8f0; font-weight:bold;text-decoration:none}
.notifiBtnDeActive a{background:#dbdbdb; padding:2px 8px; border-radius:4px; color:#fff;border:solid 1px #dbdbdb; font-weight:bold;text-decoration:none}
.notifiBtnActive a:hover, .notifiBtnDeActive a:hover{ font-weight:bold !important}
/*.notifiAddBtn a .fa-user-plus:before{ color:#fff; margin-right:4px;}*/
.fa-edit, .fa-pencil-square-o {
    content: "\f044";
    color: #3aa03a;
}
@media screen and (max-width: 1030px) {
 .notifiM{ width:45%;}
}
.headborder
{
	border-bottom: #cccccc solid 1px;
	padding: 6px 0px;
}
.TimeSheetNotBorder{border:solid 1px #ccc; border-radius:4px;margin-bottom: 10px}
.maingridbuttonspad{ padding:0px 0px 5px 5px;}
.listMenuRContent{ margin-top:65px;}
.TimeSheetNotHed{border-bottom:solid 1px #ccc;color: #656565;font-size: 18px;font-weight: normal;text-decoration: none;
padding: 10px 12px;}

</style>
</head>
<body>
	</table>
	<div class="container-fluid">
		<div class="row">
			<?php require("notification_sidenav.php"); ?> 
			<div class="col-md-9 ms-sm-auto col-lg-10">
				<form name="nform" method="post" id="nform" action="update_settings.php">
					<div class="position-sticky bg-white" style="top: 3rem; box-shadow: 0 6px 6px -6px rgb(0 0 0 / 20%);">
						<?php 
						$buttons = ['Update'=>['class'=>'btn-primary','icon'=>'fa-history fa-flip-horizontal','jsAction'=>'doCicoNotificationUpdate();']];
						echo $html->dashboardHeader($pageTitle,$buttons); ?>
					</div>
					
					<div><?php
					 if(isset($msg))
					 {
						if($msg=="updated")
							echo "<center><font class=sfontstyle>CICO Notification Settings Updated Successfully.</font></center>";
					 }
					 ?>  
				   </div>
					
					<?php
					//getting the activer server type
					$sqlSel = "SELECT server_type FROM notification_email_setup WHERE status = 'Active' GROUP BY server_type";
					$resSel = mysql_query($sqlSel,$db);
					$rowSel = mysql_fetch_assoc($resSel);

					//getting data for active server type
					$sqlData = "SELECT * FROM notification_email_setup WHERE status = 'Active'";
					$resData = mysql_query($sqlData,$db);

					$emailSelectedVal = "SELECT email_account_sno FROM notifications_settings where mod_id = '".$mode."' AND status = 1 
										AND notify_status = 'ACTIVE'";
					$emailData = mysql_query($emailSelectedVal,$db);
					$rowEmail = mysql_fetch_row($emailData);
					?>
					 
					<div class="row mb-3 align-items-center">
						<label class="col-2 col-form-label text-end">From Email Setup:</label>
						<div class="col-4">
							<select id="cico_EmailSetupSno" name="cico_EmailSetupSno" <?php if($cicoNotDetails['status']==0){echo "disabled=disabled"; } ?> class="form-select" aria-label="Default select example">
								
								<?php
								if($rowSel['server_type'] == 2) 
								{
									while ($rowData = mysql_fetch_assoc($resData)) 
									{
										if($rowEmail[0] == '') 
										{
											if($rowData['is_default'] == 'Yes') 
											{
												$selected = "selected";
											}
											else
											{
												$selected = "";
											}
										}
										else
										{
											if($rowData['is_default'] == 'Yes' && $rowEmail[0] == $rowData['sno']) 
											{
												$selected = "selected";
											}
											else if($rowEmail[0] == $rowData['sno']) 
											{
												$selected = "selected";
											}
											else
											{
												$selected = "";
											}
										}
										echo "<option value='".$rowData['sno']."' $selected>".$rowData['disname'].' - '.$rowData['mailid']."</option>";
									}
								}
								elseif($rowSel['server_type'] == 1)
								{
									$rowData = mysql_fetch_assoc($resData);
									echo "<option value='".$rowData['sno']."' selected>".$rowData['disname'].' - '.$rowData['mailid']."</option>";
								}
								?>
							</select>
							
						</div>
					</div>
					<div class="row mb-3 align-items-center">
						<label class="col-2 col-form-label text-end">Notify To Employees:</label>
						<div class="col-auto">
							<div class="form-check form-check-inline">
								<!-- <input class="form-check-input" type="checkbox" id="PrimaryEmail" value="option1"> -->
								<input type="checkbox" id="cico_primaryemail" value="e" name="cico_notifymode[]" <?php if(in_array('e',$cicoNotDetails['notify_mode'])){ echo "checked=true";}else if($cicoNotDetails['status']==0){echo "disabled=disabled";}?> class="form-check-input cico_block_fields">
								<label class="form-check-label" for="PrimaryEmail">Primary Email</label>
							</div>
							<div class="form-check form-check-inline">
								<!-- <input class="form-check-input" type="checkbox" id="SMS" value="option2"> -->
								<input type="checkbox" id="cico_sms" value="s" name="cico_notifymode[]" <?php if(in_array('s',$cicoNotDetails['notify_mode'])){ echo "checked=true";}else if($cicoNotDetails['status']==0){echo "disabled=disabled";}?> class="form-check-input cico_block_fields">
								<label class="form-check-label" for="SMS">SMS <i class="fa fa-info-circle fa-lg" style="vertical-align: top;" title="This is to remind you have not Clocked In yet for 'shift date' 'shift time'. Please Clocked In as soon as possible."></i></label>
							</div>
						</div>
					</div>
					
					<div class="card">
						<div class="card-header d-flex justify-content-between align-items-center">
							<h5 class="card-title mb-0">Employee CICO Template (ESS)</h5>
						</div>
						<div class="table-responsive">
							<table class="table table-striped table-hover mb-0 align-middle">
								<thead>
									<tr>
										<th scope="col">Template Name</th>
										<th scope="col">Template Type</th>
										<th scope="col">Created By</th>
										<th scope="col">Created Date</th>
										<th scope="col"></th>
										<th scope="col">Notify To ESS</th>
									</tr>
								</thead>
								<tbody>
									
									<?php
									$sqlClockInTemp = "SELECT nt.id, u.name as crtduser, DATE_FORMAT(CONVERT_TZ(nt.cdate,'SYSTEM','EST5EDT'),'%m/%d/%Y') as cdate1, nt.mod_id, nt.template_name, nt.template_type, nt.is_default, nt.is_overwrite FROM 
									notifications_templates nt LEFT JOIN users u ON u.username = nt.cuser WHERE 
									nt.status = 'ACTIVE' AND nt.mod_id = '".$mode."' AND nt.template_type = 'ClockIn' AND 
									nt.is_default IN('1', '0') ORDER BY nt.id asc";
									
									$resClockInTemp = mysql_query($sqlClockInTemp);
									$ClockInTemp = mysql_fetch_assoc($resClockInTemp);
									
									if($ClockInTemp['is_default'] == '1') {
										$crtduser = 'System';
									} else {
										$crtduser = $ClockInTemp['crtduser'];
									}
									?>
									
									<tr id="<?php echo $ClockInTemp['id']; ?>">
										<td><a href="javascript:void(0);" id='temp_<?php echo $ClockInTemp['id']; ?>' onClick='previewTmpl(<?php echo $ClockInTemp['id']; ?>,"<?php echo $ClockInTemp['template_type']; ?>");'><?php echo stripslashes($ClockInTemp['template_name']); ?></a></td>
										<td><?php echo stripslashes($ClockInTemp['template_type']); ?></td>
										<td id="crtduser"><?php echo stripslashes($crtduser); ?></td>
										<td><?php echo $ClockInTemp['cdate1']; ?></td>
										<td><a href="javascript:void(0);" onclick="addEditTmpl(<?php echo $ClockInTemp['id']; ?>,'<?php echo $ClockInTemp['template_type']; ?>');" style="text-decoration:none;"><i class="fa-solid fa-pencil"></i></a></td>
										<td>
											<!-- <div class="form-check form-switch">
												<input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
											</div> -->
											<div <?php echo($ClockInTemp['is_overwrite']== 0)?'class=notifiBtnDeActive': 'class=notifiBtnActive'; ?> id="notify<?php echo $ClockInTemp['id']; ?>"><a href="javascript:void(0);" data-notifystatus="<?php echo($ClockInTemp['is_overwrite']== 0)?'OFF':'ON';?>" onclick="updateciconotify('<?php echo $ClockInTemp['template_type']; ?>','<?php echo $ClockInTemp['id']; ?>');"><?php echo($ClockInTemp['is_overwrite']== 0)?'OFF': 'ON'; ?></a></div>
										</td>
									</tr>
									
									<?php
									$sqlClockOutTemp = "SELECT nt.id, u.name as crtduser, DATE_FORMAT(CONVERT_TZ(nt.cdate,'SYSTEM','EST5EDT'),'%m/%d/%Y') as cdate1, nt.mod_id, nt.template_name, nt.template_type, nt.is_default, nt.is_overwrite FROM 
									notifications_templates nt LEFT JOIN users u ON u.username = nt.cuser WHERE 
									nt.status = 'ACTIVE' AND nt.mod_id = '".$mode."' AND nt.template_type = 'ClockOut' AND 
									nt.is_default IN('1', '0') ORDER BY nt.id asc";
									
									$resClockOutTemp = mysql_query($sqlClockOutTemp);
									$ClockOutTemp = mysql_fetch_assoc($resClockOutTemp);
									
									if($ClockOutTemp['is_default'] == '1') {
										$crtduser = 'System';
									} else {
										$crtduser = $ClockOutTemp['crtduser'];
									}
									?>
									
									<tr id="<?php echo $ClockOutTemp['id']; ?>">
										<td><a href="javascript:void(0);" id='temp_<?php echo $ClockOutTemp['id']; ?>' onClick='previewTmpl(<?php echo $ClockOutTemp['id']; ?>,"<?php echo $ClockOutTemp['template_type']; ?>");'><?php echo stripslashes($ClockOutTemp['template_name']); ?></a></td>
										<td><?php echo stripslashes($ClockOutTemp['template_type']); ?></td>
										<td><?php echo stripslashes($crtduser); ?></td>
										<td><?php echo $ClockOutTemp['cdate1']; ?></td>
										<td><a href="javascript:void(0);" onclick="addEditTmpl(<?php echo $ClockOutTemp['id']; ?>,'<?php echo $ClockOutTemp['template_type']; ?>');" style="text-decoration:none;"><i class="fa-solid fa-pencil"></i></a></td>
										<td>
											<div <?php echo($ClockOutTemp['is_overwrite']== 0)?'class=notifiBtnDeActive': 'class=notifiBtnActive'; ?> id="notify<?php echo $ClockOutTemp['id']; ?>"><a href="javascript:void(0);" data-notifystatus="<?php echo($ClockOutTemp['is_overwrite']== 0)?'OFF':'ON';?>" onclick="updateciconotify('<?php echo $ClockOutTemp['template_type']; ?>','<?php echo $ClockOutTemp['id']; ?>');"><?php echo($ClockOutTemp['is_overwrite']== 0)?'OFF': 'ON'; ?></a></div>
										</td>
									</tr>
									
								</tbody>
							</table>
						</div>
					</div>
					
					<!--placement emp template details-->
					<input id="cust_temp_body" type="hidden" name="cust_temp_body" value="">
					<input id="cust_temp_sub" type="hidden" name="cust_temp_sub" value="">
					<input id="cust_temp_header" type="hidden" name="cust_temp_header" value="">
					<input id="cust_temp_signature" type="hidden" name="cust_temp_signature" value="">
					<input id="emp_temp_body" type="hidden" name="emp_temp_body" value="">
					<input id="emp_temp_sub" type="hidden" name="emp_temp_sub" value="">
					<input id="emp_temp_header" type="hidden" name="emp_temp_header" value="">
					<input id="emp_temp_signature" type="hidden" name="emp_temp_signature" value="">
					<input type="hidden" name="cico_viewmode" id="cico_viewmode" value="<?php echo $cico_viewmode; ?>">
					<input type="hidden" name="cico_notify_id" id="cico_notify_id" value="">
					<input type="hidden" name="cico_notify_id" id="cico_notify_id" value="">
					<input type="hidden" name="cust_temp_selected_columns" id="cust_temp_selected_columns" value="">
					<input type="hidden" name="emp_temp_selected_columns" id="emp_temp_selected_columns" value="">
					<input type="hidden" name="page_from" id="page_from" value="cico">

					<!--passing saved email setup sno -->
					<input type="hidden" name="cico_oldEmailSno" id="cico_oldEmailSno" value="<?php echo $rowEmail[0]; ?>">
					
				</form>
			</div>
		</div>
	</div>

<?php require("footer.inc.php");?>
</body>
</html>