<?php
session_start();
//creating session variable for passing shift dates/times to template
if(isset($cicoTempSessionType) && $cicoTempSessionType == 1) {
	unset($_SESSION['cicoSessionShiftDates']);
	unset($_SESSION['cicoSessionShiftTimes']);
	
	$_SESSION['cicoSessionShiftDates'] = urldecode($cicoSessionShiftDates);
	$_SESSION['cicoSessionShiftTimes'] = urldecode($cicoSessionShiftTimes);
	//echo urldecode($cicoSessionShiftDates)."@".urldecode($cicoSessionShiftTimes);
	//exit;
}
?>