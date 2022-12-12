<?php
	/*	
		Created Date : Aug 22, 2014.
		Created By   : Vipul
		Purpose      : To Update notifications_settings table for credential module.	
	
	    Modified Date   : Mar 23rd , 2017.
		Modified By     : Kavya
		Purpose		: Need to send reminders to employees(having active assignments)when their credentials are expired .
		Ticket Id       : [#814522] 
	
	*/
	require("global.inc");
	require("class.Notifications.inc");
	global $db;

	if($page_from=="credential")
	{
		$subscribers_arr 	= $subscribers; //Notify TO
		$notifymode_list  	= implode(",",$notifymode); //Mode of Notification
		$subscribers_list 	= implode(",",$subscribers_arr);
		$credentials_assoc_with_arr = $credentials_assoc_with; //Candidates/Employees
		$credentials_assoc_with_list = implode(",",$credentials_assoc_with_arr);
		$frequency_list  	= implode(",",$frequency); //Credential(s) expiring in
		$module_id 			= 'credential';
		$people_var 		= $list_people; //Selected Internal Employees
		$reminder 			= $send_reminder; 
		$email_sub 			= $reminder_sub;
		$email_body 		= stripslashes($reminder_body);
		$email_body  		= str_replace('../../logo.php', '/BSOS/logo.php',$email_body);
		$email_signature_id = $reminder_signature;
		$email_header_id	=	$reminder_header;
	
		if(!isset($credentials)){
			$credentials = 0;
		}
		if(in_array('3',$subscribers_arr)){
			$people_list = $people_var;
		}
		else
		{
			$people_list = "";
		}

		if($viewmode == "save")
		{
				//To insert new record in notifications_settings table.
				$ins_query = "insert into notifications_settings(mod_id,email_account_sno,notify_mode,notify_time,status,notify_to,notify_people,credentials_assoc_with,cdate,cuser,mdate,muser,send_reminder,reminder_sub,reminder_body,email_signature_id,email_header_id) values('".$module_id."','".$cred_EmailSetupSno."','".$notifymode_list."','".$frequency_list."','".$credentials."','".$subscribers_list."','".$people_list."','".$credentials_assoc_with_list."',NOW(),'".$username."',NOW(),'".$username."','".$reminder."','".$email_sub."','".addslashes($email_body)."','".$email_signature_id."','".$email_header_id."')";
				mysql_query($ins_query,$db);
			
		}
		else
		{
				// Find any active records
				$sel_query = "SELECT COUNT(1) FROM notifications_settings WHERE notify_status='ACTIVE' and mod_id='credential'";
				$sel_res = mysql_query($sel_query,$db);
				$sel_row = mysql_fetch_row($sel_res);
			
				if($sel_row[0]>0)
				{

					//checking if db value for emailSetup id is equal to value selected from dropdown
					if($cred_EmailSetupSno != $cred_oldEmailSno) 
					{
						$sqlUpd = "INSERT INTO his_notifications_settings(mod_id,email_account_sno,notify_status,mdate,muser) 
								VALUES('credential','".$cred_oldEmailSno."','Backup',NOW(),'".$username."')";
						mysql_query($sqlUpd,$db);
					}

					//Update any active record as backup before inserting any new record with status active
					$upd_query = "update notifications_settings set notify_status='BACKUP',muser='".$username."',mdate=NOW() WHERE notify_status='ACTIVE' and mod_id='credential'";
					mysql_query($upd_query,$db);
				}

				//If Credential Management is enabled then will run the query with current POST values.				
				if($credentials){
					$ins_query = "insert into notifications_settings(mod_id,email_account_sno,notify_mode,notify_time,status,notify_to,notify_people,notify_status,credentials_assoc_with,cdate,cuser,mdate,muser,send_reminder,reminder_sub,reminder_body,email_signature_id,email_header_id) values('".$module_id."','".$cred_EmailSetupSno."','".$notifymode_list."','".$frequency_list."','".$credentials."','".$subscribers_list."','".$people_list."','ACTIVE','".$credentials_assoc_with_list."',NOW(),'".$cuser."',NOW(),'".$username."','".$reminder."','".$email_sub."','".addslashes($email_body)."','".$email_signature_id."','".$email_header_id."')";
				}
				mysql_query($ins_query,$db);		
		}
		Header("Location: credential_notifications.php?msg=updated");
	}
	else
	if(AOB_ENABLED=="Y" && $page_from=="aob")
	{
		//AOB notifications variables	
		$aob_subscribers_arr 	= $aob_subscribers;
		$aob_notifymode_list  	= implode(",",$aob_notifymode);
		$aob_subscribers_list 	= implode(",",$aob_subscribers_arr);
		$aob_assoc_with_arr 	= $aob_assoc_with;
		$aob_assoc_with_list 	= implode(",",$aob_assoc_with_arr);
		$aob_frequency_list  	= implode(",",$aob_frequency);
		$aob_module_id = 'aob';
		$aob_people_var = $aob_list_people;
		if(!isset($aob_notification)){
			$aob_notification = 0;
		}
		if(in_array('3',$aob_subscribers_arr)){
			$aob_people_list = $aob_people_var;
		}
		else
		{
			$aob_people_list = "";
		}

		//checking if db value for emailSetup id is equal to value selected from dropdown
		if($aob_EmailSetupSno != $aob_oldEmailSno) 
		{
			$sqlUpd = "INSERT INTO his_notifications_settings(mod_id,email_account_sno,notify_status,mdate,muser) 
					VALUES('aob','".$aob_oldEmailSno."','Backup',NOW(),'".$username."')";
			mysql_query($sqlUpd,$db);
		}
				
		if($aob_viewmode == "save"){
			if($aob_notification){			
				//To insert new record in notifications_settings table.
				$aob_ins_query = "insert into notifications_settings(mod_id,email_account_sno,notify_mode,notify_time,status,notify_to,notify_people,credentials_assoc_with,cdate,cuser,mdate,muser) values('".$aob_module_id."','".$aob_EmailSetupSno."','".$aob_notifymode_list."','".$aob_frequency_list."','".$aob_notification."','".$aob_subscribers_list."','".$aob_people_list."','".$aob_assoc_with_list."',NOW(),'".$username."',NOW(),'".$username."')";
				mysql_query($aob_ins_query,$db);
			}				
		}else{
			// Find any active records
			$aob_sel_query = "SELECT COUNT(1) FROM notifications_settings WHERE notify_status='ACTIVE' and mod_id='aob'";
			$aob_sel_res = mysql_query($aob_sel_query,$db);
			$aob_sel_row = mysql_fetch_row($aob_sel_res);
			if($aob_sel_row[0]>0)
			{
				//Update any active record as backup before inserting any new record with status active
				$aob_upd_query = "update notifications_settings set notify_status='BACKUP',muser='".$username."',mdate=NOW() WHERE notify_status='ACTIVE' and mod_id='aob'";
				mysql_query($aob_upd_query,$db);
			}
			
			//Insert updated values to the table 		
			if($aob_notification){
				$aob_ins_query = "insert into notifications_settings(mod_id,email_account_sno,notify_mode,notify_time,status,notify_to,notify_people,notify_status,credentials_assoc_with,cdate,cuser,mdate,muser) values('".$aob_module_id."','".$aob_EmailSetupSno."','".$aob_notifymode_list."','".$aob_frequency_list."','".$aob_notification."','".$aob_subscribers_list."','".$aob_people_list."','ACTIVE','".$aob_assoc_with_list."',NOW(),'".$cuser."',NOW(),'".$username."')";
			}
			mysql_query($aob_ins_query,$db);		
		}
		Header("Location: aob_notifications.php?msg=updated");
	}
	else
	if($page_from=="placement")
	{
		//Placement notifications variables
		$placementData = array();
		$placement_subscribers_arr 	= $placement_subscribers;
		$placement_notifymode_list  = implode(",",$placement_notifymode);
		$placement_subscribers_list = implode(",",$placement_subscribers_arr);
	
		if((in_array('s', $placement_notifymode) || in_array('e', $placement_notifymode)) && $placement_subscribers_list!='' && !in_array(2, $placement_subscribers_arr)){
			$placement_subscribers_list = $placement_subscribers_list.',2';
		}elseif((in_array('s', $placement_notifymode) || in_array('e', $placement_notifymode)) && $placement_subscribers_list==''){
			$placement_subscribers_list =2;
		}
	
		$placement_module_id 		= 'placement';
		if(!isset($placement_notification)){
			$placement_notification = 0;
		}
	
		$placementData['subscribers_list']			=	$placement_subscribers_list;
		$placementData['notifymode_list']			=	$placement_notifymode_list;
		$placementData['module']					=	$placement_module_id;
		$placementData['notification']				=	$placement_notification;

		$placementData['placement_EmailSetupSno']	=	$placement_EmailSetupSno;
		$placementData['placement_oldEmailSno']		=	$placement_oldEmailSno;
	
		//Placement mode
		if($placement_viewmode == "save"){		
			$placementNot 		= new Notifications();
			$placementDetails = $placementNot->insertPlacementNotifications($placementData);
		} 
		else 
		{
			$placementNot 		= new Notifications;
			$placementDetails 	= $placementNot->updatePlacementNotification($placementData);		
		}
		Header("Location: placement_notifications.php?msg=updated");
	}
	elseif($page_from=="cico")
	{
		//CICO notifications variables
		$cicoData = array();
		$cico_notifymode_list  = implode(",",$cico_notifymode);
	
		$cico_module_id 		= 'cico';
		$cico_notification = 1;
	
		$cicoData['subscribers_list']			=	"";
		$cicoData['notifymode_list']			=	$cico_notifymode_list;
		$cicoData['module']					=	$cico_module_id;
		$cicoData['notification']				=	$cico_notification;

		$cicoData['cico_EmailSetupSno']	=	$cico_EmailSetupSno;
		$cicoData['cico_oldEmailSno']		=	$cico_oldEmailSno;
	
		//Placement mode
		if($cico_viewmode == "save"){		
			$placementNot 		= new Notifications();
			$placementDetails = $placementNot->insertCICONotifications($cicoData);
		} 
		else 
		{
			$placementNot 		= new Notifications;
			$placementDetails 	= $placementNot->updateCICONotification($cicoData);		
		}
		Header("Location: cico-notifications.php?msg=updated");
	}
	
	


?>