<?php
require("global.inc");

require("Menu.inc");

$menu=new EmpMenu();
header("Content-type: text/html; charset=".CONVERT_DEFAULT_MAIL_CHAR);
$CharSet_mail=AssignEmailCharset($charset);

function parseStaticEmailBody($email_body,$reminder_emails) 
{
	
	$email_body = str_replace("{{@job_type}}", 'Direct', $email_body);
	$email_body = str_replace("{{@employee_name}}", 'Brad Example', $email_body);
	$email_body = str_replace("{{@name}}", 'Brad Example', $email_body);
	$email_body = str_replace("{{@category}}", '', $email_body);
	$email_body = str_replace("{{@assignment_id}}", 'ASGN100114', $email_body);
	$email_body = str_replace("{{@status}}", 'On Assignment', $email_body);
	$email_body = str_replace("{{@company}}", 'Career Consulting', $email_body);
	$email_body = str_replace("{{@contact}}", 'Mark Smith', $email_body);
	$email_body = str_replace("{{@job_reportto}}", 'Mark Smith', $email_body);
	$email_body = str_replace("{{@job_location}}", 'Dover NH 234567', $email_body);
	$email_body = str_replace("{{@pay_rate}}", '6.00 USD per HOUR', $email_body);
	$email_body = str_replace("{{@bill_rate}}", '8.00 USD per HOUR', $email_body);
	$email_body = str_replace("{{@doubletime}}", '10.00 USD per HOUR', $email_body);
	$email_body = str_replace("{{@overtime}}", '8.80 USD per HOUR', $email_body);
	$email_body = str_replace("{{@shift_start_date}}", '10:00:00', $email_body);
	$email_body = str_replace("{{@shift_end_date}}", '16:00:00', $email_body);
	$email_body = str_replace("{{@shift_name}}", 'A Shift', $email_body);
	$email_body = str_replace("{{@ref_code}}", 'Reference code not available', $email_body);
	$email_body = str_replace("{{@start_date}}", '07-01-2018', $email_body);
	$email_body = str_replace("{{@end_date}}", 'Assignment End Date not available', $email_body);
	$email_body = str_replace("{{@workcom_code}}", '23gnh-23gnh-N/A', $email_body);
	$email_body = str_replace("{{@assignment_title}}", 'Material Supplier', $email_body);
	$email_body = str_replace("{{@notes}}", 'Notes not available', $email_body);
	$email_body = str_replace("{{@company_phone_number}}", '+603-234-657-9876', $email_body);
	$email_body = str_replace("{{@emp_phone_number}}", '23 456-7000', $email_body);
	$email_body = str_replace("{{@emp_email}}", 'john@gmail.com', $email_body);
	$email_body = str_replace("{{@emp_address}}", '123, Broke Suit, NJ', $email_body);
	$email_body = str_replace("{{@emp_city}}", 'Montana', $email_body);
	$email_body = str_replace("{{@emp_state}}", 'Utah-UT', $email_body);
	$email_body = str_replace("{{@emp_zipcode}}", '030630', $email_body);
	$email_body = str_replace("{{@expected_end_date}}", '12-10-2010', $email_body);
	$email_body = str_replace("{{@job_order_description}}", 'Employee Profile Summary', $email_body);
	$email_body = str_replace("{{@contact_phone_number}}", '+619-325-921-1890', $email_body);
	

	$email_body = str_replace('\n','<br>',$email_body);
	return $email_body;
}

function parseStaticEmailSub($email_sub,$reminder_emails){
		
	$email_sub = str_replace("{{@job_location}}", 'Dover NH 234567', $email_sub);
	$email_sub = str_replace("{{@employee_name}}", 'Brad Example', $email_sub);
	$email_sub = str_replace("{{@assignment_title}}", 'Material Supplier', $email_sub);
	$email_sub = str_replace("{{@start_date}}", '07-01-2018', $email_sub);
	$email_sub = str_replace("{{@end_date}}", 'Assignment End Date not available', $email_sub);
	$email_sub = str_replace("{{@assignment_company_name}}", 'New Horizon Inc', $email_sub);
	
    return $email_sub;
}

function parseStaticTimesheetEmailBody($email_body,$reminder_emails)
{
	$email_body = str_replace("{{@employee_name}}", 'Brad Example', $email_body);
	$email_body = str_replace("{{@start_date}}", '11/05/2018', $email_body);
	$email_body = str_replace("{{@end_date}}", '11/09/2018', $email_body);
	$email_body = str_replace("{{@customer_login_url}}", '<a href="https://login.akken.com/" target="_blank">Click here</a>', $email_body);
	$email_body = str_replace('\n','<br>',$email_body);
	return $email_body;
}

function parseStaticTimesheetEmailSub($email_sub,$reminder_emails)
{
	$email_sub = str_replace("{{@employee_name}}", 'Brad Example', $email_sub);
	$email_sub = str_replace("{{@assignment_company_name}}", 'New Horizon Inc', $email_sub);
	$email_sub = str_replace("{{@submitted_start_date}}",'11/05/2018', $email_sub);
	$email_sub = str_replace("{{@submitted_end_date}}",'11/09/2018', $email_sub);
	return $email_sub;
}

function parseStaticCICOEmailBody($email_body)
{
	$email_body = str_replace("{{@first_name}}", 'Brad ', $email_body);
	$email_body = str_replace("{{@last_name}}", ' Example', $email_body);
	$email_body = str_replace("{{@shift_date}}", '11/28/2022', $email_body);
	$email_body = str_replace("{{@shift_time}}", '10AM - 5PM', $email_body);
	$email_body = str_replace('\n','<br>',$email_body);
	return $email_body;
}

function parseStaticCICOEmailSub($email_sub)
{
	$email_sub = str_replace("{{@first_name}}", 'Brad ', $email_sub);
	$email_sub = str_replace("{{@last_name}}", ' Example', $email_sub);
	$email_sub = str_replace("{{@shift_date}}", '11/28/2022', $email_sub);
	$email_sub = str_replace("{{@shift_time}}", '10AM - 5PM', $email_sub);
	return $email_sub;
}

$sql = "SELECT mod_id,template_name, template_type, is_default, email_subject as subject, email_body as mail_body, 					email_signature_id as 
		signature, email_header_id as mail_header, email_merger_columns as notification_section FROM notifications_templates 
		WHERE id = '".$tmplId."'";

$res = mysql_query($sql);
$row = mysql_fetch_assoc($res);

$body 		= parseStaticEmailBody($row['mail_body'],$row);
$subject 	= parseStaticEmailSub($row['subject'],$row);

if($row['mod_id'] == "timesheet")
{
	$body 		= parseStaticTimesheetEmailBody($row['mail_body'],$row);
	$subject 	= parseStaticTimesheetEmailSub($row['subject'],$row);
}

if($row['mod_id'] == "cico")
{
	$body 		= parseStaticCICOEmailBody($row['mail_body'],$row);
	$subject 	= parseStaticCICOEmailSub($row['subject'],$row);
}

?>
<html>
<head>
<script language=javascript src=scripts/validatefields.js></script>
<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<style type="text/css">
.maingridbuttonspad{ padding:0px;}
.ProfileNewUI, .ProfileNewUI .hfontstyle{ font-size:13px;}
.previewBdy div{font-size:13px; line-height:20px}
.mandatFieldColor{ color:#ff0000; font-size:16px;}
.msgBdyBdr{ border:solid 1px #ccc; border-radius:4px; padding:2px 4px}
.msgSubBdr{ border-top:solid 1px #ccc; padding:0px;}
.mand_note{color:#ff0000; font-size:13px; font-weight: 600;}
/*.note{font-size:14px;}*/
</style>
<title>Template Preview</title>
<body>
<table border="0" width="100%" cellspacing="0" cellpadding="0" class="ProfileNewUI">
	<tr>
	  	<td width=100% valign=top align=left>
			<table width=100% cellpadding=0 cellspacing=0 border=0 class="ProfileNewUI">
				<tr class="NewGridTopBg">
        <?php
			$Header_name=explode("|","fa fa-clone~Edit|fa-ban~Cancel");
			$Header_link=explode("|","javascript:editPreviewTmpl($tmplId,'$module');|javascript:window.close();");
			$Header_heading="&nbsp;".$module." Template";

			$menu->showHeadingStrip1($Header_name,$Header_link,$Header_heading);
			?>		
        </tr>
				<tr>
					<td width="100%">
						<table border="0" width="98%" align="center" cellspacing="0" cellpadding="6" class="ProfileNewUI previewBdy">
							<!--New Tr Added for Template name-->
							<tr>
								<td valign=top align=left width=18%><font class=hfontstyle>Template Name: <span class="mandatFieldColor">*</span></font></td>
								<td valign=top width=80%><span><?php echo stripslashes($row['template_name']); ?></span></td> 
							</tr>
                            <tr>
                            <td colspan="2" class="msgSubBdr"></td>
                            </tr> 
							<!--New Tr Added for Template name-->
                            
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
								<td valign=top width=100%><?php echo stripslashes($body); ?></td>
							 </tr>                            
                            </table>                            
                            </td>
                            </tr>
                             <tr>
                            <!-- <td colspan="2"><font class= "mand_note">Note:</font> <font class="note"> Click here holds the Assignment Job location <b>Login URL</b> value from the Admin >>Website Management>> Contact Us Page.</font> </td> -->
                            <?php if($module=='Approve' || $module== 'Rejected'){?>
                             <td colspan="2"><font class= "mand_note">Note:</font><font> Click here holds the <b>Login URL</b> value from the Admin >>Website Management>> Contact Us Page.<br> If the value is empty then login.akken.com will be displayed.</font> </td>
                            <?php }?>
                            </tr>
							
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</body>
</html>