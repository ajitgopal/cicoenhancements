<?php
require("global.inc");
require("class.Notifications.inc");
$placementNot = new Notifications();


$selidArr 	= explode(',',$selids);
$msg 		= '';
 
$msg = $placementNot->sendCICONotifications($selids,$notid,$onetime_edit);
echo $msg;
?>