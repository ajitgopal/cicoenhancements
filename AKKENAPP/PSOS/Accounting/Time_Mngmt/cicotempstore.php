<?php
require("global.inc");
require("class.Notifications.inc");
$placementNot 				= new Notifications();

$cico_data = array();

$cico_data['cico_subject'] 	= $subject;
$cico_data['cico_temp'] 	= $matter2;
$cico_data['cico_sign'] 	= $mce_editor_0_footer;
$cico_data['cico_header'] 	= $mce_editor_0_header;

$cico_data['is_default'] 		= $is_default;
$cico_data['template_type'] 	= $template_type;
$cico_data['template_name'] 	= $template_name;
$cico_data['is_overwrite'] 	= $is_overwrite;
$cico_data['teml_id'] 			= $teml_id;
$cico_data['status'] 			= $status;
$id = $cico_data['teml_id'];

$templatedata[$cico_data['teml_id']]=$cico_data;

if($onetime_edit == 1) 
{ 
	$placementNot->updateCICOtemplate($cico_data);	
}
else 
{ 	
	session_register("templatedata");	
}
	
echo '<script>window.location.href = "showTemplatePreview.php?tmpId='.$id.'&empTID='.$empID.'&onetime_edit='.$onetime_edit.'"</script>';
?>