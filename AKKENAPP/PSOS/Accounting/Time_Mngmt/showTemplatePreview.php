<?php
require("global.inc");
require("Menu.inc");
require("class.Notifications.inc");

$menu = new EmpMenu();
$placementData 	= new Notifications();

header("Content-type: text/html; charset=".CONVERT_DEFAULT_MAIL_CHAR);
$CharSet_mail=AssignEmailCharset($charset);

$assgnIDs 		= explode(",",$empTID);
$cmaSepAssgnIDS = implode(",",$assgnIDs);
$singleAssignSno = $assgnIDs[0];

$shift_dates_array = explode(",",$_SESSION['cicoSessionShiftDates']);
$shift_times_array = explode(",",$_SESSION['cicoSessionShiftTimes']);

//echo $cmaSepAssgnIDS; echo "<br>"; echo "dates: ".$_SESSION['cicoSessionShiftDates']; echo "<br>"; echo "times: ".$_SESSION['cicoSessionShiftTimes']; exit;
 
//$istempchange = ($_SESSION['istempchange'] !='')?$_SESSION['istempchange']:1;

if($onetime_edit == 1) 
{
	$sql = "SELECT id, template_name, template_type, is_default, is_overwrite, email_subject as subject, email_body as mail_body, email_signature_id as signature, email_header_id as mail_header, email_merger_columns as notification_section, status FROM notifications_templates WHERE id = '".$tmpId."'";

	$res = mysql_query($sql);
	$row = mysql_fetch_assoc($res);

	$template_type 		= $row['template_type'];
	$subject       		= $row['subject'];
	$matter          		= $row['mail_body'];
	$is_overwrite 		= $row['is_overwrite'];
	$id 				= $row['id'];
	$template_name 		= $row['template_name'];
	$status 			= $row['status'];
}
else 
{	
	$template_type 		= $_SESSION['templatedata'][$tmpId]['template_type'];
	$is_overwrite 		= $_SESSION['templatedata'][$tmpId]['is_overwrite'];
	$id 				= $_SESSION['templatedata'][$tmpId]['teml_id'];
	$template_name 		= $_SESSION['templatedata'][$tmpId]['template_name'];
	$status 			= $_SESSION['templatedata'][$tmpId]['status'];
	$subject            = $_SESSION['templatedata'][$tmpId]['cico_subject'];
	$matter       = $_SESSION['templatedata'][$tmpId]['cico_temp'];	
}

if($singleAssignSno == "" || $singleAssignSno == 0) {
	echo "Please select atleast one employee";
	exit;
}

//getting first employee details for preview
$emp_qry = "select sno,username,email,other_phone,emp_fname as first_name,emp_lname as last_name from emp_list where sno='".$singleAssignSno."'";
$emp_query 	= mysql_query($emp_qry,$db);
$res_emp 	= mysql_fetch_assoc($emp_query);
//print_r($res_emp); exit;

//$email_sub = str_replace("{{@job_location}}", $reminder_emails['job_location'], $email_sub);
$subject = str_replace("{{@first_name}}",$res_emp['first_name'],$subject); 
$subject = str_replace("{{@last_name}}",$res_emp['last_name'],$subject); 
$subject = str_replace("{{@shift_date}}",$shift_dates_array[0],$subject); 
$subject = str_replace("{{@shift_time}}",$shift_times_array[0],$subject); 

$matter  = str_replace("{{@first_name}}",$res_emp['first_name'],$matter); 
$matter  = str_replace("{{@last_name}}",$res_emp['last_name'],$matter);
$matter  = str_replace("{{@shift_date}}",$shift_dates_array[0],$matter);
$matter  = str_replace("{{@shift_time}}",$shift_times_array[0],$matter);
$matter  = str_replace('\n','<br>',$matter);							
$matter  = str_replace('\n','<br>',$matter);

?>
<html>
<head>
<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<script src = "scripts/validatecico.js" language="javascript"></script>
<script type="text/javascript" src="/BSOS/scripts/jquery-1.8.3.js"></script>
<style type="text/css">
div#preloader { position: fixed; left: 0; top: 0; z-index: 9999; width: 100%; height: 100%; overflow: visible; background: url('../../images/akkenloading_big.gif') no-repeat center center; display: none; }
#body_glow_small{
	left: 0;
    position: absolute;
    top: 0;
    z-index: 9990;
    background-color: #fff;
    height: 100%;
    opacity: 0.40;
    width: 100%;
}

#processBar {
 	background-color: #ddd;
    position: absolute;
    top: 55%;
    width: 80%;
    border-radius: 20px;
    z-index: 99999;
    margin-left: 10%;
}

#processBarTimer {
  width: 0%;
  height: 20px;
  background-color: #4CAF50;
  text-align: center;
  line-height: 20px;
  color: white;
  border-radius: 20px;
}
.maingridbuttonspad{ padding:0px;}
.ProfileNewUI, .ProfileNewUI .hfontstyle{ font-size:13px;}
.previewBdy div{font-size:13px; line-height:20px}
.mandatFieldColor{ color:#ff0000; font-size:16px;}
.msgBdyBdr{ border:solid 1px #ccc; border-radius:4px; padding:2px 4px}
.msgSubBdr{ border-top:solid 1px #ccc; padding:0px;}
.afontstyle1{ padding-left:0px;}
</style>
<title>Template Preview</title>

<body>
<div id="preloader"></div>
<div id="body_glow_small" style="display: none;"></div>
<div id="processBar" style="display: none;">
  <div id="processBarTimer"></div>
</div>
<input type="hidden" name="assignIds" id="assignIds" value="<?php echo $empTID;?>" >
<table border="0" width="100%" cellspacing="0" cellpadding="0" class="ProfileNewUI">
	<tr>
	  	<td width=100% valign=top align=left>
			<table width=100% cellpadding=0 cellspacing=0 border=0 class="ProfileNewUI">
				<tr class="NewGridTopBg">
        <?php
        	if($is_overwrite == 1) 
        	{
				$Header_name=explode("|","fa fa-clone~Edit|fa-envelope~Send Email|fa-ban~Cancel");
				$Header_link=explode("|","javascript:editPreviewTmpl(".$tmpId.",'".$template_type."','".$cmaSepAssgnIDS."');|javascript:sendCICONotifications();|javascript:window.close();window.opener.location.reload(false);");
			} 
			else 
			{
				$Header_name=explode("|","fa fa-clone~Send Email|fa-ban~Cancel");
				$Header_link=explode("|","javascript:sendCICONotifications();|javascript:window.close();window.opener.location.reload(false);");
			}
			$Header_heading="&nbsp;".$template_type." Template";

			$menu->showHeadingStrip1($Header_name,$Header_link,$Header_heading);
			?>		
        </tr>
				<tr>
					<td width="100%">
						<table border="0" width="98%" align="center" cellspacing="0" cellpadding="6" class="ProfileNewUI previewBdy">
							<!--New Tr Added for Template name-->
							<tr>
								<td valign=top align=left width=18%><font class=hfontstyle>Template Name:<span class="mandatFieldColor">*</span></font></td>
								<td valign=top width=80%><span><?php echo stripslashes($template_name); ?></span></td> 
							</tr>
							<!--New Tr Added for Template name-->
						<tr>
                            <td colspan="2" class="msgSubBdr"></td>
                            </tr>
							<tr>
								<td valign=top align=left width=18%><font class=hfontstyle>Subject:&nbsp;&nbsp;</font></td>
								<td valign=top width=80%><?php echo stripslashes($subject); ?></td>
							</tr>
                            <tr>
                            <td colspan="2" style="padding-bottom:10px;"><font class=hfontstyle>Message Body:&nbsp;&nbsp;</font></td>
                            </tr>                            
                              <tr>
                            <td colspan="2" class="msgBdyBdr">
                            <table cellpadding="4" cellspacing="0" width="100%">
                            <tr>
                            <td valign=top width=80%><?php echo stripslashes($matter); ?></td>
                            </tr>                            
                            </table>
                            </td>
                            </tr>                           
							
						</table>
					</td>
					<input type="hidden" name='status' id='status' value=<?php echo $status; ?> />
					<input type="hidden" name='temp_id' id='temp_id' value=<?php echo $id; ?> />
					<input type="hidden" name='template_type' id='template_type' value=<?php echo $template_type; ?> />
					<input type="hidden" name='onetime_edit' id='onetime_edit' value=<?php echo $onetime_edit; ?> />
				</tr>
			</table>
		</td>
	</tr>
</table>
</body>
</html>