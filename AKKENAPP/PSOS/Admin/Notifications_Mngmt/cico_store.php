<?php
require("global.inc");
require("class.Notifications.inc");
global $db;

$cicoNot 			= new Notifications();
$module_id 	= 'cico';
$templateNotification 	= array();

$is_overwrite = ($_POST['is_overwrite']!="")?$_POST['is_overwrite']:"";
$templateNotification['module']						= $module_id;
$templateNotification['temp_subject']				= $subject;
$cust_temp_body 									= stripslashes($matter2);
$templateNotification['temp_body']  				= str_replace('../../logo.php', '/BSOS/logo.php',$cust_temp_body);
$templateNotification['temp_signature_id'] 			= $mce_editor_0_footer;
$templateNotification['location']					= "";//implode(",", $location);
$templateNotification['department']					= "";//implode(",", $department);
$templateNotification['is_default']					= $is_default;
$templateNotification['is_overwrite']				= $is_overwrite;
$templateNotification['template_type']				= $template_type;
$templateNotification['template_name']				= $template_name; 
$templateNotification['temp_header_id']				= $mce_editor_0_header;
$templateNotification['temp_selected_columns']		= "";//$added1;
$templateNotification['temp_id']					= $teml_id;

if($mode == 'edit') 
	{
		$timesheetTemplateData 	= $cicoNot->updateCICONotificationTemp($templateNotification, $module_id);
		echo $msg = "Updated|''|''|''";
	}
else if($mode == 'update')
{
	$timesheetTemplateStatus = $cicoNot->updateCICONotificationStatus($templateNotification, $module_id);
	echo $timesheetTemplateStatus;
}
?>