<?php
require('savemails.inc');
require('customMailConfig.php');
$placementmailSetup = new customMailSetup();

class Notifications
{ 
	function getNotification($mode)
	{
		global $db;
		//Query to Get the values from database for placement notification management screen

		$notification_setting_query = "select id, cuser, mod_id, notify_mode, notify_time, status, notify_to, notify_people, credentials_assoc_with, notify_status from notifications_settings where notify_status = 'ACTIVE' and mod_id = '".$mode."'";
		
		$res_details = mysql_query($notification_setting_query,$db);
		if(mysql_num_rows($res_details) > 0) 
		{		
			$row_details = mysql_fetch_array($res_details);
			$notification_details = array();

			$notification_details['viewmode']					= "edit";
			$notification_details['id']							= $row_details['id'];
			$notification_details['cuser']						= $row_details['cuser'];
			$notification_details['status']						= $row_details['status'];
			$notification_details['notify_arr']					= $row_details['notify_mode'];
			$notification_details['notify_mode'] 				= explode(",",$notification_details['notify_arr']);
			$notification_details['subscribers_arr']			= $row_details['notify_to'];
			$notification_details['subscribers'] 				= explode(",",$notification_details['subscribers_arr']);
			$notification_details['assoc_with_arr']				= $row_details['credentials_assoc_with'];
			$notification_details['assocs']						= explode(",",$notification_details['assoc_with_arr']);		
			$notification_details['notify_status'] 				= $row_details['notify_status'];
		}
		return $notification_details;
	}

	//function to save plcament notification details 
	function insertPlacementNotifications($insertData) 
	{
		global $db,$username;

		//checking if db value for emailSetup id is equal to value selected from dropdown
		if($insertData['placement_EmailSetupSno'] != $insertData['placement_oldEmailSno']) 
		{
			echo $sqlUpd = "INSERT INTO his_notifications_settings(mod_id,email_account_sno,notify_status,mdate,muser) 
					VALUES('placement','".$insertData['placement_oldEmailSno']."','Backup',NOW(),'".$username."')";
			mysql_query($sqlUpd,$db);
		}

		//To insert new record in notifications_settings table.
		$ins_query = "insert into notifications_settings(mod_id, email_account_sno, notify_mode, notify_time, status, notify_to, 
					notify_people, credentials_assoc_with, cdate, cuser, mdate, muser) values('".$insertData['module']."', 
					'".$insertData['placement_EmailSetupSno']."', '".$insertData['notifymode_list']."', '', 
					'".$insertData['notification']."', '".$insertData['subscribers_list']."', '', '', NOW(), '".$username."', NOW(), 
					'".$username."')";
		mysql_query($ins_query,$db);
	}

	//function ro update status and insert placement notification with status active
	function updatePlacementNotification($updateData) 
	{
		global $db,$username;
		// Find any active records
		$sel_query = "SELECT COUNT(1) FROM notifications_settings WHERE notify_status='ACTIVE' and mod_id='placement'";
		$sel_res = mysql_query($sel_query,$db);
		$sel_row = mysql_fetch_row($sel_res);

		if($sel_row[0]>0)
		{
			//checking if db value for emailSetup id is equal to value selected from dropdown
			if($updateData['placement_EmailSetupSno'] != $updateData['placement_oldEmailSno']) 
			{
				echo $sqlUpd = "INSERT INTO his_notifications_settings(mod_id,email_account_sno,notify_status,mdate,muser) 
						VALUES('placement','".$updateData['placement_oldEmailSno']."','Backup',NOW(),'".$username."')";
				mysql_query($sqlUpd,$db);
			}

			//Update any active record as backup before inserting any new record with status active
			$upd_query = "update notifications_settings set notify_status='BACKUP',muser='".$username."',mdate=NOW() WHERE notify_status='ACTIVE' and mod_id='placement'";
			mysql_query($upd_query,$db);
		}

		//Insert updated values to the table		
		//If Placement Notification Management is enabled then will run the query with current POST values.
		
		if($updateData['notification']) 
		{
			$ins_query = "insert into notifications_settings(mod_id, email_account_sno, notify_mode, notify_time, status, notify_to, notify_people, notify_status, credentials_assoc_with, cdate, cuser, send_reminder) values('".$updateData['module']."', '".$updateData['placement_EmailSetupSno']."', '".$updateData['notifymode_list']."', '','".$updateData['notification']."', 
				'".$updateData['subscribers_list']."', '', 'ACTIVE', '', NOW(), '".$username."', '')";
			mysql_query($ins_query,$db);
		}
	}

	//function to get Customer report to query
	function getcustReportto($asgnid){

		if(SHIFT_SCHEDULING_ENABLED=='Y')
		{
			$shiftNameQry = "LEFT JOIN shift_setup ss ON ss.sno = es.sm_sno";
		}
		else
		{
			$shiftNameQry = "LEFT JOIN shift_setup ss ON ss.sno = ej.shiftid";
		}

		$cust_reportto_query = "SELECT IF(ej.project!='', ej.project, 'Assignment Name not available') AS assignment_title, sc.email AS email, CONCAT('oppr','',so.sno) as username, ej.sno as id, IF(hg.esms='N','',hg.mser_domain) AS smsdomain, IF(hg.mobile IS NULL, '', hg.mobile) AS mobile,m.name AS jotype, el.name AS emp_name, m1.name AS category, ej.pusername AS assignment_id, ej.ustatus AS status, IF(comp.cname!='',comp.cname,'Customer name not available') AS company, CONCAT(sc.fname,' ',sc.lname) AS contact, IF(sc.fname!='',CONCAT(sc.fname,' ',sc.lname), 'Job reports to not available') AS jobreportsto,  CONCAT(loc.address1,' ',loc.address2,' ',loc.city,' ',loc.state,' ',loc.zipcode) AS job_location, IF(ej.bamount!='',CONCAT(ej.bamount,' ', ej.bcurrency,' per ', ej.bperiod),'Bill Rate not available') AS billrate,  IF(ej.otbrate_amt!='',CONCAT(ej.otbrate_amt,' ',ej.otbrate_curr,' per ',ej.otbrate_period),'Over time Bill Rate not available') AS overrate, IF(ej.double_brate_amt!='',CONCAT(ej.double_brate_amt,' ',ej.double_brate_curr,' per ',ej.double_brate_period),'Double time bill rate not available') AS doublerate, IF(MIN(es.shift_starttime)!='',DATE_FORMAT(MIN(es.shift_starttime),'%H:%i:%s'),'Shift Start time not available') AS shift_start_date, IF(MAX(es.shift_endtime)!='',DATE_FORMAT(MAX(shift_endtime),'%H:%i:%s'),'Shift End time not available') AS shift_end_date, IF(ss.shiftname!='', ss.shiftname,'Shift Name not available') AS  shiftname, IF(CONCAT_WS('-',wc.code,wc.title,wc.state)!='--',CONCAT_WS('-',wc.code,wc.title,wc.state),'Work comp code not available') as workcompcode, IF(ej.refcode!='',ej.refcode,'Reference code not available') AS ref_code, IF(ej.s_date!='0-0-0' && ej.s_date!='',DATE_FORMAT(str_to_date(ej.s_date,'%m-%d-%Y'),'%m-%d-%Y'), 'Assignment Start Date not available') AS start_date, IF(ej.e_date!='0-0-0' && ej.e_date!='',DATE_FORMAT(str_to_date(ej.e_date,'%m-%d-%Y'),'%m-%d-%Y'), 'Assignment End Date not available') AS end_date, '',TIME_FORMAT(ej.starthour,'%h:%i %p') AS starthour ,TIME_FORMAT(ej.endhour,'%h:%i %p') AS endhour, hg.email as empEmail, hg.wphone as empPhone, hg.wphone_extn as phoneExt, hg.address1 as empAddress1, hg.address2 as empAddress2, hg.city as empCity, hg.zip as empZipCode, hg.state as empOtherState,  hg.stateid, CONCAT(stc.state_name,'-',stc.state_abbr) as empState
			FROM hrcon_jobs ej
				LEFT JOIN emp_list el ON ej.username=el.username
				LEFT JOIN hrcon_general hg ON ej.username=hg.username
				LEFT JOIN hrcon_compen hc ON hc.username=el.username 
				LEFT JOIN staffacc_contact sc ON ej.manager=sc.sno
				LEFT JOIN staffacc_contact sc1 ON ej.contact=sc1.sno
				LEFT JOIN staffoppr_contact so ON so.acc_cont = sc.sno
				LEFT JOIN staffacc_cinfo comp ON ej.client=comp.sno
				LEFT JOIN staffacc_location loc ON ej.endclient = loc.sno
				LEFT JOIN hrconjob_sm_timeslots es ON ej.sno=es.pid
				LEFT JOIN manage m ON ej.jotype=m.sno AND m.type='jotype'
				LEFT JOIN manage m1 ON ej.catid=m1.sno AND m1.type='jocategory'
				LEFT JOIN workerscomp wc ON wc.workerscompid = ej.wcomp_code AND wc.status='active'
				LEFT JOIN state_codes stc ON stc.state_id = hg.stateid
				$shiftNameQry		
			WHERE /*ej.ustatus = 'active'*/
			el.lstatus != 'DA'
			AND hc.ustatus='active'
			AND ej.jtype!='' 
			AND ej.jotype!='0'
			AND ej.sno IN (".$asgnid.")
			GROUP BY ej.sno";
		return $cust_reportto_query;
	}

	//function to get employee email query
	function getempEmail($asgnid){

		if(SHIFT_SCHEDULING_ENABLED=='Y')
		{
			$shiftNameQry = "LEFT JOIN shift_setup ss ON ss.sno = es.sm_sno";
		}
		else
		{
			$shiftNameQry = "LEFT JOIN shift_setup ss ON ss.sno = ej.shiftid";
		}

		$emp_email_query = "SELECT ej.project AS assignment_title,
		el.email AS email,
		hg.username AS username,
		ej.sno AS id,
		IF(hg.esms = 'N', '', hg.mser_domain) AS smsdomain,
		IF(hg.mobile IS NULL, '', hg.mobile) AS mobile,
		m.name AS jotype,
		el.name AS emp_name,
		m1.name AS category,
		IF(ej.pusername != '', ej.pusername, 'Assignment Id not available')
		  AS assignment_id,
		comp.cname AS company,
		CONCAT(sc.fname, ' ', sc.lname) AS contact,
		IF(sc1.fname != '',
		  CONCAT(sc1.fname, ' ', sc1.lname),
		  'Job reports to not available')
		  AS jobreportsto,
		CONCAT(loc.address1,
		      ' ',
		      loc.address2,
		      ' ',
		      loc.city,
		      ' ',
		      loc.state,
		      ' ',
		      loc.zipcode)
		  AS job_location,
		IF(ej.pamount != '',
		  CONCAT(ej.pamount,
		         ' ',
		         ej.pcurrency,
		         ' per ',
		         ej.pperiod),
		  'Pay Rate not available')
		  AS payrate,
		IF(ej.otprate_amt != '',
		  CONCAT(ej.otprate_amt,
		         ' ',
		         ej.otprate_curr,
		         ' per ',
		         ej.otprate_period),
		  'Over Time Pay Rate not available')
		  AS overrate,
		IF(ej.double_prate_amt != '',
		  CONCAT(ej.double_prate_amt,
		         ' ',
		         ej.double_prate_curr,
		         ' per ',
		         ej.double_prate_period),
		  'Double Time Pay Rate not available')
		  AS doublerate,
		if(MIN(es.shift_starttime) != '',
		  DATE_FORMAT(MIN(es.shift_starttime), '%H:%i:%s'),
		  'Shift start time not available')
		  AS shift_start_date,
		if(MAX(es.shift_endtime) != '',
		  DATE_FORMAT(MAX(shift_endtime), '%H:%i:%s'),
		  'Shift End time not available')
		  AS shift_end_date,
		IF(ss.shiftname != '', ss.shiftname, 'Shift Name not available')
		  AS shiftname,
		CONCAT('emp', '', el.sno) AS empid,
		IF(CONCAT_WS('-',
		            wc.code,
		            wc.title,
		            wc.state) != '--',
		  CONCAT_WS('-',
		            wc.code,
		            wc.title,
		            wc.state),
		  'Work comp code not available')
		  AS workcompcode,
		IF(ej.refcode != '', ej.refcode, 'Reference code not available')
		  AS ref_code,
		IF(ej.s_date != '0-0-0' && ej.s_date != '',
		  DATE_FORMAT(str_to_date(ej.s_date, '%m-%d-%Y'), '%m-%d-%Y'),
		  'Assignment Start Date not available')
		  AS start_date,
		IF(ej.e_date != '0-0-0' && ej.e_date != '',
		  DATE_FORMAT(str_to_date(ej.e_date, '%m-%d-%Y'), '%m-%d-%Y'),
		  'Assignment End Date not available')
		  AS end_date,
		IF(ej.notes != '', ej.notes, 'Notes not available') AS notes,
		TIME_FORMAT(ej.starthour, '%h:%i %p') AS starthour,
		TIME_FORMAT(ej.endhour, '%h:%i %p') AS endhour,
		comp.phone AS main_phone_number,
		comp.phone_extn AS phone_extension,
		hg.wphone AS empPhone,
		hg.wphone_extn as phoneExt,
		hg.email AS empEmail,
		hg.address1 as empAddress1, 
		hg.address2 as empAddress2,
		hg.city AS empCity,
		hg.zip AS empZipCode,
		hg.state AS empOtherState,
		hg.stateid,
		CONCAT(stc.state_name, '-', stc.state_abbr) AS empState,
		IF(ej.exp_edate != '0-0-0' && ej.exp_edate != '', DATE_FORMAT(ej.exp_edate,'%m-%d-%Y'), '') 
		AS expEnd_date, pd.posdesc as jobOrder_desc, sc.wphone as contact_phone_number, sc.wphone_extn as contact_phone_number_extn
			FROM hrcon_jobs ej
		LEFT JOIN emp_list el ON ej.username = el.username
		LEFT JOIN hrcon_general hg ON ej.username = hg.username
		LEFT JOIN hrcon_compen hc ON hc.username = el.username
		LEFT JOIN hrcon_w4 hw ON (el.username = hw.username)
		LEFT JOIN hrcon_personal hp ON hp.username = el.username
		LEFT JOIN staffacc_contact sc ON ej.contact = sc.sno
		LEFT JOIN staffacc_contact sc1 ON ej.manager = sc1.sno
		LEFT JOIN staffacc_cinfo comp ON ej.client = comp.sno
		LEFT JOIN staffacc_location loc ON ej.endclient = loc.sno
		LEFT JOIN posdesc pd ON ej.posid = pd.posid
		LEFT JOIN hrconjob_sm_timeslots es ON ej.sno = es.pid
		LEFT JOIN manage m ON ej.jotype = m.sno AND m.type = 'jotype'
		LEFT JOIN manage m1 ON ej.catid = m1.sno AND m1.type = 'jocategory'
		LEFT JOIN state_codes stc ON stc.state_id = hg.stateid
		LEFT JOIN workerscomp wc ON wc.workerscompid = ej.wcomp_code AND wc.status = 'active'
		$shiftNameQry
		WHERE /*ej.ustatus='active'*/
		hg.ustatus='active'
		AND hw.ustatus='active'
		AND hp.ustatus='active'
		AND ej.sno IN (".$asgnid.")
		AND el.lstatus NOT IN ('DA','INACTIVE')
		GROUP BY ej.sno";
		return $emp_email_query;
	}

	//function to get customer contact query
	function getcustContact($asgnid){

		if(SHIFT_SCHEDULING_ENABLED=='Y')
		{
			$shiftNameQry = "LEFT JOIN shift_setup ss ON ss.sno = es.sm_sno";
		}
		else
		{
			$shiftNameQry = "LEFT JOIN shift_setup ss ON ss.sno = ej.shiftid";
		}

		$cust_contact_query = "SELECT IF(ej.project!='', ej.project, 'Assignment Name not available') AS assignment_title, sc1.email AS email, CONCAT('oppr','',so.sno) AS username, ej.sno AS id, IF(hg.esms='N','',hg.mser_domain) AS smsdomain, IF(hg.mobile IS NULL, '', hg.mobile) AS mobile,m.name AS jotype, el.name AS emp_name, m1.name AS category, ej.pusername AS assignment_id, ej.ustatus AS status,comp.cname AS company, CONCAT(sc1.fname,' ',sc1.lname) AS contact, IF(sc.fname!='',CONCAT(sc.fname,' ',sc.lname), 'Job reports to not available') AS jobreportsto, CONCAT(loc.address1,' ',loc.address2,' ',loc.city,' ',loc.state,' ',loc.zipcode) AS job_location, IF(ej.bamount!='',CONCAT(ej.bamount,' ', ej.bcurrency,' per ', ej.bperiod),'Bill rate not available') AS billrate, IF(ej.otbrate_amt!='',CONCAT(ej.otbrate_amt,' ',ej.otbrate_curr,' per ',ej.otbrate_period),'Over time Bill rate not available') AS overtime, IF(ej.double_brate_amt!='',CONCAT(ej.double_brate_amt,' ',ej.double_brate_curr,' per ',ej.double_brate_period),'Double time Bill rate not available') AS double_rate, if(MIN(es.shift_starttime)!='',DATE_FORMAT(MIN(es.shift_starttime),'%H:%i:%s'),'Shift Start time not available') AS shift_start_date, IF(MAX(es.shift_endtime)!='',DATE_FORMAT(MAX(shift_endtime),'%H:%i:%s'),'Shift End time not available') AS shift_end_date, IF(ss.shiftname!='',ss.shiftname,'Shift Name not available') AS  shiftname, IF(CONCAT_WS('-',wc.code,wc.title,wc.state)!='--',CONCAT_WS('-',wc.code,wc.title,wc.state),'Work comp code not available') as workcompcode, IF(ej.refcode!='',ej.refcode,'Reference code not available') AS ref_code, IF(ej.s_date!='0-0-0' && ej.s_date!='',DATE_FORMAT(str_to_date(ej.s_date,'%m-%d-%Y'),'%m-%d-%Y'), 'Assignment Start Date not available') AS start_date, IF(ej.e_date!='0-0-0' && ej.e_date!='',DATE_FORMAT(str_to_date(ej.e_date,'%m-%d-%Y'),'%m-%d-%Y'), 'Assignment End Date not available') AS end_date,'',TIME_FORMAT(ej.starthour,'%h:%i %p') AS starthour ,TIME_FORMAT(ej.endhour,'%h:%i %p') AS endhour, hg.email as empEmail, hg.wphone as empPhone, hg.wphone_extn as phoneExt, hg.address1 as empAddress1, hg.address2 as empAddress2, hg.city as empCity, hg.zip as empZipCode, hg.state as empOtherState,  hg.stateid, CONCAT(stc.state_name,'-',stc.state_abbr) as empState
			FROM hrcon_jobs ej
				LEFT JOIN emp_list el ON ej.username=el.username
				LEFT JOIN hrcon_general hg ON ej.username=hg.username
				LEFT JOIN hrcon_compen hc ON hc.username=el.username 
				LEFT JOIN staffacc_contact sc1 ON ej.contact=sc1.sno
				LEFT JOIN staffacc_contact sc ON ej.manager=sc.sno
				LEFT JOIN staffoppr_contact so ON so.acc_cont = sc1.sno
				LEFT JOIN staffacc_cinfo comp ON ej.client=comp.sno
				LEFT JOIN staffacc_location loc ON ej.endclient = loc.sno
				LEFt JOIN hrconjob_sm_timeslots es ON ej.sno=es.pid
				LEFT JOIN manage m ON ej.jotype=m.sno AND m.type='jotype'
				LEFT JOIN manage m1 ON ej.catid=m1.sno AND m1.type='jocategory'
				LEFT JOIN workerscomp wc ON wc.workerscompid = ej.wcomp_code AND wc.status='active'
				LEFT JOIN state_codes stc ON stc.state_id = hg.stateid
				$shiftNameQry
			WHERE /*ej.ustatus = 'active'*/
			 el.lstatus != 'DA'
			AND hc.ustatus='active'
			AND ej.jtype!='' 
			AND ej.jotype!='0'
			AND ej.sno IN (".$asgnid.")
			GROUP BY ej.sno";
		return $cust_contact_query;
	}

	//function to get employee sms query
	function getempSMS($asgnid){

		if(SHIFT_SCHEDULING_ENABLED=='Y')
		{
			$shiftNameQry = "LEFT JOIN shift_setup ss ON ss.sno = es.sm_sno";
		}
		else
		{
			$shiftNameQry = "LEFT JOIN shift_setup ss ON ss.sno = ej.shiftid";
		}
		$emp_sms_query = "SELECT ej.project AS assignment_title, el.email AS email, hg.username as username, ej.sno as id, IF(hg.esms='N','',hg.mser_domain) AS smsdomain, IF(hg.mobile IS NULL, '', hg.mobile) AS mobile,m.name AS jotype, el.name AS emp_name, m1.name AS category, IF(ej.pusername!='',ej.pusername,'Assignment ID not available') AS assignment_id, comp.cname AS company, CONCAT(sc.fname,' ',sc.lname) AS contact, IF(sc1.fname!='',CONCAT(sc1.fname,' ',sc1.lname),'Job Reports to not available') AS jobreportsto, CONCAT(loc.address1,' ',loc.address2,' ',loc.city,' ',loc.state,' ',loc.zipcode) AS job_location, IF(ej.pamount!='',CONCAT(ej.pamount,' ', ej.pcurrency,' per ', ej.pperiod),'Pay Rate not available') AS payrate, IF(ej.otprate_amt!='',CONCAT(ej.otprate_amt,' ',ej.otprate_curr,' per ',ej.otprate_period),'Over Time Pay Rate not available') AS overrate, IF(ej.double_prate_amt!='',CONCAT(ej.double_prate_amt,' ',ej.double_prate_curr,' per ',ej.double_prate_period),'Double Time Pay Rate not available') AS doublerate, if(MIN(es.shift_starttime)!='',DATE_FORMAT(MIN(es.shift_starttime),'%H:%i:%s'),'Shift start time not available') AS shift_start_date, if(MAX(es.shift_endtime)!='',DATE_FORMAT(MAX(shift_endtime),'%H:%i:%s'),'Shift End time not available') AS shift_end_date,  IF(ss.shiftname!='',ss.shiftname,'Shift Name not available') AS  shiftname, CONCAT('emp','',el.sno) as empid, IF(CONCAT_WS('-',wc.code,wc.title,wc.state)!='--',CONCAT_WS('-',wc.code,wc.title,wc.state),'Work comp code not available') as workcompcode, IF(ej.refcode!='',ej.refcode,'Reference code not available') AS ref_code, IF(ej.s_date!='0-0-0' && ej.s_date!='',DATE_FORMAT(str_to_date(ej.s_date,'%m-%d-%Y'),'%m-%d-%Y'), 'Assignment Start Date not available') AS start_date, IF(ej.e_date!='0-0-0' && ej.e_date!='',DATE_FORMAT(str_to_date(ej.e_date,'%m-%d-%Y'),'%m-%d-%Y'), 'Assignment End Date not available') AS end_date, IF(ej.notes!='',ej.notes,'Notes not available') AS notes,TIME_FORMAT(ej.starthour,'%h:%i %p') AS starthour ,TIME_FORMAT(ej.endhour,'%h:%i %p') AS endhour, comp.phone AS main_phone_number, comp.phone_extn AS phone_extension,
		hg.wphone AS empPhone,
       	hg.wphone_extn as phoneExt,
       	hg.email AS empEmail,
       	hg.address1 as empAddress1, 
       	hg.address2 as empAddress2,
       	hg.city AS empCity,
       	hg.zip AS empZipCode,
       	hg.state AS empOtherState,
       	hg.stateid,
       	CONCAT(stc.state_name, '-', stc.state_abbr) AS empState,
       IF(ej.exp_edate != '0-0-0' && ej.exp_edate != '', DATE_FORMAT(ej.exp_edate,'%m-%d-%Y'), '') 
       AS expEnd_date, pd.posdesc as jobOrder_desc, sc.wphone as contact_phone_number, sc.wphone_extn as contact_phone_number_extn
			FROM hrcon_jobs ej
				LEFT JOIN emp_list el ON ej.username=el.username
				LEFT JOIN hrcon_general hg ON ej.username=hg.username
				LEFT JOIN hrcon_compen hc ON hc.username=el.username
				LEFT JOIN hrcon_w4 hw ON (el.username=hw.username) 
				LEFT JOIN hrcon_personal hp ON hp.username=el.username 
				LEFT JOIN staffacc_contact sc ON ej.contact=sc.sno
				LEFT JOIN staffacc_contact sc1 ON ej.manager=sc1.sno
				LEFT JOIN staffacc_cinfo comp ON ej.client=comp.sno
				LEFT JOIN staffacc_location loc ON ej.endclient = loc.sno
				LEFT JOIN posdesc pd ON ej.posid = pd.posid
				LEFT JOIN hrconjob_sm_timeslots es ON ej.sno=es.pid
				LEFT JOIN manage m ON ej.jotype=m.sno AND m.type='jotype'
				LEFT JOIN manage m1 ON ej.catid=m1.sno AND m1.type='jocategory'
				LEFT JOIN workerscomp wc ON wc.workerscompid = ej.wcomp_code AND wc.status='active'
				LEFT JOIN state_codes stc ON stc.state_id = hg.stateid
				$shiftNameQry
			WHERE /*ej.ustatus='active'*/
			hg.ustatus='active' 
			AND hw.ustatus='active' 
			AND hp.ustatus='active'
			AND ej.sno IN (".$asgnid.")
			AND el.lstatus NOT IN ('DA','INACTIVE')
			GROUP BY ej.sno";
		return $emp_sms_query;
	}

	/** Parse database driven email body by replacing custom variables with dynamic values. **/
	function parseEmailBody($email_body,$reminder_emails) 
	{
		$email_body = str_replace("{{@job_type}}", $reminder_emails['jotype'], $email_body);
		$email_body = str_replace("{{@employee_name}}", $reminder_emails['emp_name'], $email_body);
		$email_body = str_replace("{{@name}}", $reminder_emails['emp_name'], $email_body);
		$email_body = str_replace("{{@category}}", $reminder_emails['category'], $email_body);
		$email_body = str_replace("{{@assignment_id}}", $reminder_emails['assignment_id'], $email_body);
		$email_body = str_replace("{{@status}}", $reminder_emails['status'], $email_body);
		$email_body = str_replace("{{@company}}", $reminder_emails['company'], $email_body);
		$email_body = str_replace("{{@contact}}", $reminder_emails['contact'], $email_body);
		$email_body = str_replace("{{@job_reportto}}", $reminder_emails['jobreportsto'], $email_body);
		$email_body = str_replace("{{@job_location}}", $reminder_emails['job_location'], $email_body);
		$email_body = str_replace("{{@pay_rate}}", $reminder_emails['payrate'], $email_body);
		$email_body = str_replace("{{@bill_rate}}", $reminder_emails['billrate'], $email_body);
		$email_body = str_replace("{{@doubletime}}", $reminder_emails['doublerate'], $email_body);
		$email_body = str_replace("{{@overtime}}", $reminder_emails['overrate'], $email_body);
		if(SHIFT_SCHEDULING_ENABLED=='Y')
		{
			$email_body = str_replace("{{@shift_start_date}}", $reminder_emails['shift_start_date'], $email_body);
			$email_body = str_replace("{{@shift_end_date}}", $reminder_emails['shift_end_date'], $email_body);
		}
		else
		{
			$email_body = str_replace("{{@shift_start_date}}", $reminder_emails['starthour'], $email_body);
			$email_body = str_replace("{{@shift_end_date}}", $reminder_emails['endhour'], $email_body);
		}
		$email_body = str_replace("{{@shift_name}}", $reminder_emails['shiftname'], $email_body);
		$email_body = str_replace("{{@ref_code}}", $reminder_emails['ref_code'], $email_body);
		$email_body = str_replace("{{@start_date}}", $reminder_emails['start_date'], $email_body);
		$email_body = str_replace("{{@end_date}}", $reminder_emails['end_date'], $email_body);
		$email_body = str_replace("{{@workcom_code}}", $reminder_emails['workcompcode'], $email_body);
		$email_body = str_replace("{{@assignment_title}}", $reminder_emails['assignment_title'], $email_body);
		$email_body = str_replace("{{@notes}}", $reminder_emails['notes'], $email_body);

		/**************For Employee Template - Additional Fields-Start***********/
		if($reminder_emails['phone_extension'] != '' && $reminder_emails['main_phone_number'] != '') 
		{
			$phoneNumber = $reminder_emails['phone_extension'].'-'.$reminder_emails['main_phone_number'];
		}
		elseif($reminder_emails['phone_extension'] == '' && $reminder_emails['main_phone_number'] != '') 
		{
			$phoneNumber = $reminder_emails['main_phone_number'];
		}
		elseif($reminder_emails['phone_extension'] == '' && $reminder_emails['main_phone_number'] == '') 
		{
			$phoneNumber = 'N/A';
		}
		$email_body = str_replace("{{@company_phone_number}}", $phoneNumber, $email_body);

		//Expected End Date Condition - Employe template
		if($reminder_emails['expEnd_date'] == '') 
		{
			$expEndDate = 'N/A';
		}
		else 
		{
			$expEndDate = $reminder_emails['expEnd_date'];
		}
		$email_body = str_replace("{{@expected_end_date}}", $expEndDate, $email_body);

		//Job Order Description Condition - Employe template
		if($reminder_emails['jobOrder_desc'] == '') 
		{
			$jobOdrDesc = 'N/A';
		}
		else 
		{
			$jobOdrDesc = strip_tags($reminder_emails['jobOrder_desc']);
		}
		$email_body = str_replace("{{@job_order_description}}", $jobOdrDesc, $email_body);
		/**************For Employee Template - Additional Fields-END***********/

		/**************For Customer Template - Additional Fields***********/
		if($reminder_emails['empEmail'] == '') 
		{
			$empEmailVal = 'N/A';
		}
		else 
		{
			$empEmailVal = $reminder_emails['empEmail'];
		}
		$email_body = str_replace("{{@emp_email}}", $empEmailVal, $email_body);

		if($reminder_emails['phoneExt'] != '' && $reminder_emails['empPhone'] != '') 
		{
			$empPhoneNumber = $reminder_emails['phoneExt'].'-'.$reminder_emails['empPhone'];
		}
		elseif($reminder_emails['phoneExt'] == '' && $reminder_emails['empPhone'] != '') 
		{
			$empPhoneNumber = $reminder_emails['empPhone'];
		}
		elseif($reminder_emails['phoneExt'] == '' && $reminder_emails['empPhone'] == '') 
		{
			$empPhoneNumber = 'N/A';
		}
		$email_body = str_replace("{{@emp_phone_number}}", $empPhoneNumber, $email_body);

		if($reminder_emails['empAddress1'] != '' && $reminder_emails['empAddress2'] != '') 
		{
			$empAdrs = $reminder_emails['empAddress1'].', '.$reminder_emails['empAddress2'];
		}
		elseif($reminder_emails['empAddress1'] != '' && $reminder_emails['empAddress2'] == '') 
		{
			$empAdrs = $reminder_emails['empAddress1'];
		}
		elseif($reminder_emails['empAddress1'] == '' && $reminder_emails['empAddress2'] != '') 
		{
			$empAdrs = $reminder_emails['empAddress2'];
		}
		elseif($reminder_emails['empAddress1'] == '' && $reminder_emails['empAddress2'] == '') 
		{
			$empAdrs = 'N/A';
		}
		$email_body = str_replace("{{@emp_address}}", $empAdrs, $email_body);

		if($reminder_emails['empCity'] == '') 
		{
			$empCityVal = 'N/A';
		}
		else 
		{
			$empCityVal = $reminder_emails['empCity'];
		}
		$email_body = str_replace("{{@emp_city}}", $empCityVal, $email_body);

		if($reminder_emails['empZipCode'] == '') 
		{
			$empZipCodeVal = 'N/A';
		}
		else 
		{
			$empZipCodeVal = $reminder_emails['empZipCode'];
		}
		$email_body = str_replace("{{@emp_zipcode}}", $empZipCodeVal, $email_body);

		if($reminder_emails['stateid'] == 0) 
		{
			$empStateVal = $reminder_emails['empOtherState'];
			if($empStateVal == '')
			{
				$empStateVal = 'N/A';
			}
		}
		else 
		{
			$empStateVal = $reminder_emails['empState'];
			if($empStateVal == '')
			{
				$empStateVal = 'N/A';
			}
		}
		$email_body = str_replace("{{@emp_state}}", $empStateVal, $email_body);
		/**************For Customer Template - Additional Fields-END***********/

		/**************Contact Main Phone Number***********/
		if($reminder_emails['contact_phone_number_extn'] != '' && $reminder_emails['contact_phone_number'] != '') 
		{
			$contactPhoneNumber = $reminder_emails['contact_phone_number_extn'].'-'.$reminder_emails['contact_phone_number'];
		}
		elseif($reminder_emails['contact_phone_number_extn'] == '' && $reminder_emails['contact_phone_number'] != '') 
		{
			$contactPhoneNumber = $reminder_emails['contact_phone_number'];
		}
		elseif($reminder_emails['contact_phone_number_extn'] == '' && $reminder_emails['contact_phone_number'] == '') 
		{
			$contactPhoneNumber = 'N/A';
		}
		$email_body = str_replace("{{@contact_phone_number}}", $contactPhoneNumber, $email_body);
		/**************Contact Main Phone Number-END***********/

		$email_body = str_replace('\n','<br>',$email_body);

		return $email_body;
	}

	/** Parse database driven email subject by replacing custom variables with dynamic values. **/
	function parseEmailSub($email_sub,$reminder_emails){
		if($reminder_emails['job_location']=='Job location not available'){
			$reminder_emails['job_location']='';
		}else{
			$reminder_emails['job_location']=$reminder_emails['job_location'];
		}

		if($reminder_emails['start_date']=='Assignment Start Date not available'){
			$reminder_emails['start_date']='';
		}else{
			$reminder_emails['start_date']=$reminder_emails['start_date'];
		}

		if($reminder_emails['end_date']=='Assignment End Date not available'){
			$reminder_emails['end_date']='';
		}else{
			$reminder_emails['end_date']=$reminder_emails['end_date'];
		}

		$email_sub = str_replace("{{@job_location}}", $reminder_emails['job_location'], $email_sub);
		$email_sub = str_replace("{{@employee_name}}", $reminder_emails['emp_name'], $email_sub);
		$email_sub = str_replace("{{@assignment_title}}", $reminder_emails['assignment_title'], $email_sub);
		$email_sub = str_replace("{{@start_date}}", $reminder_emails['start_date'], $email_sub);
		$email_sub = str_replace("{{@end_date}}", $reminder_emails['end_date'], $email_sub);
	    return $email_sub;
	}

	//function to update template details - From Assignment
	function updatecusttemplate($custPlacementData) 
	{
		global $db,$username; 
		// Find any active records
		$sel_query = "SELECT COUNT(1) as count,id FROM notifications_templates WHERE id = ".$custPlacementData['teml_id']." and 
					status='ACTIVE' and mod_id='placement'";
		$sel_res = mysql_query($sel_query,$db);
		$sel_row = mysql_fetch_row($sel_res);
	
		if($sel_row[0] > 0)
		{
			//Calling Trigger for Taking Backup Record
			$cust_temp 		= stripslashes($custPlacementData['cust_temp']);
			$cust_subject	= stripslashes($custPlacementData['cust_subject']);

			//Insert updated values to the table		
			//If Placement Notification Management is enabled then will run the query with current POST values.
			$ins_cust_query = "update notifications_templates set 
			template_name = '".mysql_real_escape_string(trim($custPlacementData['template_name']))."', 
			is_overwrite = '".$custPlacementData['is_overwrite']."', 
			email_subject = '".mysql_real_escape_string(trim($cust_subject))."', 
			email_body = '".mysql_real_escape_string(trim($cust_temp))."', email_merger_columns = '".$custPlacementData['cust_selcols']."', 
			email_signature_id = '".$custPlacementData['cust_sign']."', email_header_id = '".$custPlacementData['cust_header']."', 
			locations = '".$custPlacementData['location']."', departments = '".$custPlacementData['department']."', 
			muser = '".$username."', mdate = NOW() where id = ".$custPlacementData['teml_id']."";

			mysql_query($ins_cust_query,$db);
		}
		return;
	}

	//function to update template details - From Assignment
	function updateemptemplate($empPlacementData) 
	{
		global $db,$username;
		// Find any active records
		$sel_query = "SELECT COUNT(1) as count,id FROM notifications_templates WHERE id = ".$empPlacementData['teml_id']." and 
					status='ACTIVE' and mod_id='placement'";
		$sel_res = mysql_query($sel_query,$db);
		$sel_row = mysql_fetch_row($sel_res);
	
		if($sel_row[0] > 0)
		{
			//Calling Trigger for Taking Backup Record
			$emp_temp = stripslashes($empPlacementData['emp_temp']);
			$emp_subject = stripslashes($empPlacementData['emp_subject']);

			//Insert updated values to the table		
			//If Placement Notification Management is enabled then will run the query with current POST values.
			$ins_cust_query = "update notifications_templates set 
			template_name = '".mysql_real_escape_string(trim($empPlacementData['template_name']))."', 
			is_overwrite = '".$empPlacementData['is_overwrite']."', 
			email_subject = '".mysql_real_escape_string(trim($emp_subject))."', 
			email_body = '".mysql_real_escape_string(trim($emp_temp))."', 
			email_merger_columns = '".$empPlacementData['emp_selcols']."', 
			email_signature_id = '".$empPlacementData['emp_sign']."', email_header_id = '".$empPlacementData['emp_header']."', 
			locations = '".$empPlacementData['location']."', departments = '".$empPlacementData['department']."', 
			muser = '".$username."', mdate = NOW() where id = ".$empPlacementData['teml_id']."";
			mysql_query($ins_cust_query,$db);
		}
		return;
	}

	function gettingFromMailAddress() 
	{
		global $db,$username;

		$sqlEmail = "SELECT fname, lname, email from hrcon_general where username = '".$username."' AND ustatus = 'active'";

		$resEmail  = mysql_query($sqlEmail,$db);

		if(mysql_num_rows($resEmail) > 0)
		{
			$rowEmail = mysql_fetch_row($resEmail);		
			$fromAddress 	  = $rowEmail[2];	///primary email//////////
			$fromName		  = $rowEmail[0].' '.$rowEmail[1];
			$fromEmailAddress = $fromName.' <'.$fromAddress.'>';
		}
		return $fromEmailAddress;
	}

	//function to send Notifications to Customers - From Assignment
	function sendCustNotifications($asgnids,$template_id,$onetime_edit) 
	{
		global $db,$companyuser,$maindb,$username,$maildb,$curtime_header,$placementmailSetup,$smtp;
		
		$companyuser=strtolower($companyuser);

		$main_query 			= ""; //variable for main query.
		$cust_reportto_query 	= ""; //variable for customer reportto query.
		$cust_contact_query 	= ""; //variable for customer contact query.
		$cust_query 			= ""; //variable for customer query.
		$email_flag 			= 'N'; //default value for email alert in notification table.
		$sms_flag 				= 'N'; //default value for sms alert in notification table.
		$popup_flag 			= 'N'; // //default value for popup alert in notification table.

		//QUERY TO FETCH NOTIFICATION SETTINGS DATA	
		$notification_settings="select status from notifications_settings  where status='1' and mod_id='placement' and notify_status = 'ACTIVE'";
		$notification_result=mysql_query($notification_settings,$db);
		if(mysql_num_rows($notification_result)==1)
		{
			//$rrow = mysql_fetch_assoc($notification_result);

			//echo $istempchange = ($_SESSION['istempchange'] !='')?$_SESSION['istempchange']:1;
			if($onetime_edit == 1)
			{
				$selTemp = "SELECT email_subject, email_body, email_merger_columns FROM notifications_templates WHERE id = ".$template_id."";
				$resTemp = mysql_query($selTemp);
				$rowTemp = mysql_fetch_assoc($resTemp);

				//$cust_body 				=	htmlspecialchars_decode($rowTemp['email_body']);
				$cust_body 					=	$rowTemp['email_body'];
				$cust_body 					= 	stripslashes($cust_body);
				$cust_sub 					=	stripslashes($rowTemp['email_subject']);
				$cust_selected_columns_list =	$rowTemp['email_merger_columns'];
				$cust_selected_columns_val 	= 	explode("|",$cust_selected_columns_list);
			} 
			else 
			{
				$cust_body         			 = $_SESSION['templatedata'][$template_id]['cust_temp'];
				$cust_body         			 = stripslashes($cust_body);
				$cust_sub       			 = stripslashes($_SESSION['templatedata'][$template_id]['cust_subject']);
				$cust_selected_columns_list  = $_SESSION['templatedata'][$template_id]['cust_selcols'];
				$cust_selected_columns_val   = explode("|",$cust_selected_columns_list);
			}		

			//Conditions to check selected time frequency. According to that will create a phrase for query. if multiple then will create a phrase with OR condition.

			$cust_reportto_query 	= $this->getcustReportto($asgnids);
			$cust_contact_query 	= $this->getcustContact($asgnids);
					
			if($cust_reportto_query != "") 
			{
				$cust_query = $cust_reportto_query;
			}

			if($cust_contact_query != "") 
			{
				$cust_query = $cust_contact_query;
			}
			
			if($cust_reportto_query != "" && $cust_contact_query != "") 
			{
				$cust_query = $cust_reportto_query." UNION ".$cust_contact_query;
			}

			if($cust_query !="") 
			{
				$cust_main_query = $cust_query;
			}

			// VARIABLES USED TO AVOID MULTIPLE ENTRIES IN NOTIFICATIONS TABLE					
			$temp_notification_placement_ids = array(); //Assignment IDs with notification id stored temporarily.

			//Last inserted id generated from notifications table stored temporarily.
			$temp_notification_psno = "";

			//Customer Email Notification
				
			if($cust_main_query !="")
			{
				$cust_result_email = mysql_query($cust_main_query,$db);
				//LOOP TO INSERT INTO NOTIFICATION TABLE
				$assignmentIds = explode(',', $asgnids);
			    //$total = count($assignmentIds);

			   	//getting email address and email setup sno from fucntion
				$combinedSetUpData = $placementmailSetup->getSetupMailIDNlCnt('placement');
				$explodedSetUpData = explode('^',$combinedSetUpData);

				$from 		= $explodedSetUpData[2];
			    $lcntVal 	= $explodedSetUpData[1];
			    $setupSno	= $explodedSetUpData[3];
			    
			    $i=0;  
			    $total = mysql_num_rows($cust_result_email);
			    
			    while($cust_email_array = mysql_fetch_array($cust_result_email))
				{
					$percent = intval($i/$total * 100);
					// AVOID MULTIPLE ENTRIES IN NOTIFICATIONS TABLE
					if(!isset($temp_notification_placement_ids[$cust_email_array['email']."_".$cust_email_array['assignment_id']]))
					{
						if($cust_email_array['username']!='') 
						{
							if($cust_sub != '') 
							{
								$subject = $this->parseEmailSub($cust_sub,$cust_email_array); //parse the dynamic variables of email subject
							}
							
						
							if($cust_body != '') 
							{
								$matter = $this->parseEmailBody($cust_body,$cust_email_array);//parse the dynamic variables of email body
								$matter  = str_replace('\n','<br>',$matter);							
							}
							

							$matter 				= "<div style='font-family: arial; font-size: 10pt;'>".stripslashes($matter)."<br><br></div>";

							$matter=str_replace("../../logo.php","/BSOS/logo.php",str_replace("href=\"/#id","href=\"#id",str_replace("href=\"/#top\"","href=\"#top\"",stripslashes($matter))));

							$toEmail 	= $cust_email_array['email'];
							$to_array	= array($cust_email_array['email']);

							$mailtype	= "text/html";
							$mailheaders= array("Date: $curtime_header","From: $from","To: $toEmail","Subject: $subject","MIME-Version: 1.0");

							$email_matter = prepareBody($matter,$mailheaders,$mailtype);

							if($lcntVal < 5) 
							{
								$smtpRes 	= $placementmailSetup->getSetUpNSendMail($from,$to_array,$mailheaders,$email_matter,$setupSno);

								if($smtpRes == 1)
							    {
							    	$this->savePlacementNotificationMailsInActivities($cust_email_array['username'], $cust_email_array['email'], $subject, $matter, $from);
							    	$resP = 'Success';
								}
								else
								{
									//$resP = 'Failure';
									$module = 'placement';
									$errMsg = "SMTP Server Authorization Failed ".$smtp->error;
									$errMsgDesc = $smtp->error_message;

									$type = $placementmailSetup->getCustomType($explodedSetUpData[4]);
									
									$sqlIns = "INSERT INTO notification_email_logs(server_type, module, cuser, cdate, status, error_msg, toaddr, fromaddr, error_msg_desc) VALUES('".$type."', '".$module."', '".$username."', NOW(), 'Failed', '".mysql_real_escape_string($errMsg)."', '".mysql_real_escape_string($cust_email_array['email'])."', '".mysql_real_escape_string($from)."', '".mysql_real_escape_string($errMsgDesc)."')";
									mysql_query($sqlIns,$db);

									if($explodedSetUpData[4] == 2) 
									{
										if($lcntVal == 4) 
										{
											$lcntVal = $lcntVal+1;
											$sqlUpd = "UPDATE notification_email_setup SET lockm = 'ERR',mdate=NOW(), lcount = ".($lcntVal)." WHERE 
													sno = '$setupSno'";
											$errStatus = "Blckd";
										}
										else
										{
											$lcntVal = $lcntVal+1;
											$sqlUpd = "UPDATE notification_email_setup SET lockm = 'No', lcount = ".($lcntVal)." WHERE 
													sno = '$setupSno'";
										}
										mysql_query($sqlUpd,$db);
										//$resP 	= 'Failure'.'^^'.$errStatus;
										$status = 'Failure';
									}
									elseif($explodedSetUpData[4] == 1) 
									{
										$status 	= 'Failure';
										$errStatus 	= "";
									}									
									$resP 	= $status.'^^'.$errStatus;
								}
							}
							else
							{
								$resP = 'Blocked';
								//lockm column is blocked in custom_email_setup setup
							}
						}
					}
					// END OF BLOCK TO AVOID MULTIPLE ENTRIES IN NOTIFICATIONS TABLE					
					$cust_email_array = "";
					$matter=''; 
					$subject='';
					echo ".".$percent;
			        flush();    
			        ob_flush();
			        sleep(1);
			       $i++;
				}
			}
		}
		session_unregister('templatedata');
		return '^^'.$resP;
	}

	//function to send Notifications to Employee - From Assignment
	function sendEmpNotifications($asgnids,$template_id,$onetime_edit) 
	{
		global $db,$companyuser,$maindb,$username,$maildb,$curtime_header,$placementmailSetup,$smtp;

		$companyuser=strtolower($companyuser);
		
		$main_query 			= ""; //variable for main query.
		$emp_sms_query 			= ""; //variable for employee sms query.
		$emp_email_query 		= ""; //variable for employee email query.
		$emp_query 				= ""; //variable for employee query.

		//QUERY TO FETCH NOTIFICATION SETTINGS DATA	
		$notification_settings="select status from notifications_settings  where status='1' and mod_id='placement' and notify_status = 'ACTIVE'";
		$notification_result=mysql_query($notification_settings,$db);
		if(mysql_num_rows($notification_result)==1)
		{
			//$rrow = mysql_fetch_assoc($notification_result);		

			if($onetime_edit == 1)
			{
				$selTemp = "SELECT email_subject, email_body, email_merger_columns FROM notifications_templates WHERE id = ".$template_id."";
				$resTemp = mysql_query($selTemp);
				$rowTemp = mysql_fetch_assoc($resTemp);

				//$emp_body 				=	htmlspecialchars_decode($rowTemp['email_body']);
				$emp_body 					=	$rowTemp['email_body'];
				$emp_body 					= 	stripslashes($emp_body);
				$emp_sub 					=	stripslashes($rowTemp['email_subject']);
				$emp_selected_columns_list  =	$rowTemp['email_merger_columns'];
				$emp_selected_columns_val 	= 	explode("|",$emp_selected_columns_list);	
			} 
			else 
			{
				$emp_body         			= $_SESSION['templatedata'][$template_id]['emp_temp'];
				$emp_body         			= stripslashes($emp_body);
				$emp_sub       			 	= stripslashes($_SESSION['templatedata'][$template_id]['emp_subject']);
				$emp_selected_columns_list  = $_SESSION['templatedata'][$template_id]['emp_selcols'];
				$emp_selected_columns_val  	= explode("|",$emp_selected_columns_list);
			}		

			/* Conditions to check selected time frequency. According to that will create a phrase for query. if multiple then will create a phrase with OR condition.*/

			/* Condition to check flags for Email , SMS , Popup */

			$email_flag = 'N';
			$sms_flag 	= 'Y';
			$popup_flag = 'N';
			
			$emp_sms_query 		= $this->getempSMS($asgnids);
			$emp_email_query 	= $this->getempEmail($asgnids);	
								
			if($emp_sms_query != "") 
			{
				$emp_query = $emp_sms_query;
			}

			if($emp_email_query != "") 
			{
				$emp_query = $emp_email_query;
			}
			
			if($emp_sms_query != "" && $emp_email_query != "") 
			{
				$emp_query = $emp_sms_query." UNION ".$emp_email_query;
			}

			if($emp_query!="")
			{
				$emp_main_query = $emp_query;
			}

			// VARIABLES USED TO AVOID MULTIPLE ENTRIES IN NOTIFICATIONS TABLE					
			$temp_notification_placement_ids = array(); //Assignment IDs with notification id stored temporarily.

			//Last inserted id generated from notifications table stored temporarily.
			$temp_notification_psno = "";

				//Customer Email Notification
				
			if($emp_main_query !="")
			{
				$emp_result_email = mysql_query($emp_main_query,$db);
				//LOOP TO INSERT INTO NOTIFICATION TABLE
				$assignmentIds = explode(',', $asgnids);
			    //$total = count($assignmentIds);

			    //getting email address and email setup sno from fucntion
				$combinedSetUpData = $placementmailSetup->getSetupMailIDNlCnt('placement');
				$explodedSetUpData = explode('^',$combinedSetUpData);

				$from 		= $explodedSetUpData[2];
			    $lcntVal 	= $explodedSetUpData[1];
			    $setupSno	= $explodedSetUpData[3];

			    $i=0;
			    $total = mysql_num_rows($emp_result_email);
			    
			    while($emp_email_array = mysql_fetch_array($emp_result_email))
				{	
					$percent = intval($i/$total * 100);			
					// AVOID MULTIPLE ENTRIES IN NOTIFICATIONS TABLE
					if(!isset($temp_notification_placement_ids[$emp_email_array['email']."_".$emp_email_array['assignment_id']]))
					{
						if($emp_email_array['username']!='') 
						{
							if($emp_sub != '') 
							{
								$subject = $this->parseEmailSub($emp_sub,$emp_email_array); //parse the dynamic variables of email subject
							}
							

							if($emp_body != '') 
							{
								$matter = $this->parseEmailBody($emp_body,$emp_email_array);//parse the dynamic variables of email body
								$insMatter = $matter;
								$matter  = str_replace('\n','<br>',$matter);
							}
							

							// Message content for SMS - Max 160 chars in SMS						
							$sms_matter = $this->getSMSText($emp_email_array['assignment_title'],$emp_email_array['assignment_id'],$emp_email_array['company'],$emp_email_array['job_location'],$emp_email_array['start_date'],$emp_email_array['end_date']);

							$matter = "<div style='font-family: arial; font-size: 10pt;'>".stripslashes($matter)."<br><br></div>";

							$matter=str_replace("../../logo.php","/BSOS/logo.php",str_replace("href=\"/#id","href=\"#id",str_replace("href=\"/#top\"","href=\"#top\"",stripslashes($matter))));

							$toEmail 	= $emp_email_array['email'];
							$to_array	= array($toEmail);

							$mailheaders= array("Date: $curtime_header","From: $from","To: $toEmail","Subject: $subject","MIME-Version: 1.0");
							$mailtype		= "text/html";
							$email_matter	= prepareBody($matter,$mailheaders,$mailtype);

							if($lcntVal < 5) 
							{
								$smtpRes = $placementmailSetup->getSetUpNSendMail($from,$to_array,$mailheaders,$email_matter,$setupSno);
								
								if($smtpRes == 1)
							    {
							    	$this->savePlacementNotificationMailsInActivities($emp_email_array['empid'], $emp_email_array['email'], $subject, $matter, $from);
							    	$resP = 'Success';
								}
								else
								{
									//$resP 	= 'Failure';
									$module = 'placement';
									$errMsg = "SMTP Server Authorization Failed ".$smtp->error;
									$errMsgDesc = $smtp->error_message;

									$type = $placementmailSetup->getCustomType($explodedSetUpData[4]);

									$sqlIns = "INSERT INTO notification_email_logs(server_type, module, cuser, cdate, status, error_msg, toaddr, fromaddr, error_msg_desc) VALUES('".$type."', '".$module."', '".$username."', NOW(), 'Failed', '".mysql_real_escape_string($errMsg)."', '".mysql_real_escape_string($emp_email_array['email'])."', '".mysql_real_escape_string($from)."', '".mysql_real_escape_string($errMsgDesc)."')";
									mysql_query($sqlIns,$db);

									if($explodedSetUpData[4] == 2) 
									{
										if($lcntVal == 4) 
										{
											$lcntVal = $lcntVal+1;
											$sqlUpd  = "UPDATE notification_email_setup SET lockm = 'ERR',mdate=NOW(), lcount = ".($lcntVal)." WHERE 
													sno = '$setupSno'";
											$errStatus = "Blckd";
										}
										else
										{
											$lcntVal = $lcntVal+1;
											$sqlUpd  = "UPDATE notification_email_setup SET lockm = 'No', lcount = ".($lcntVal)." WHERE 
													sno = '$setupSno'";
										}
										mysql_query($sqlUpd,$db);
										//$resP = 'Failure'.'^^'.$errStatus;
										$status = 'Failure';
									}
									elseif($explodedSetUpData[4] == 1) 
									{
										$status 	= 'Failure';
										$errStatus 	= "";
									}
									$resP 	= $status.'^^'.$errStatus;
								}
							}
							else
							{
								$resP = 'Blocked';
								//lockm column is blocked in custom_email_setup setup
							}

							//Call to procedure for inserting to notifications table of akken_notifications.
							$akken_insert_notification = "call akken_notifications.insertNotification ('".$companyuser."','placement','".$emp_email_array['id']."','','','Active','".mysql_real_escape_string($sms_matter)."',@psno)";
							$res = mysql_query($akken_insert_notification,$db);
							$res = mysql_query("select @psno as psno",$db);
							$fetch_psno_result = mysql_fetch_array($res);
							$psno = $fetch_psno_result[0];

							//Update the flags in the table 
							$alert_type_set_query = "UPDATE akken_notifications.notifications SET alert = '".$popup_flag."', email = '".$email_flag."', sms = '".$sms_flag."' WHERE sno = ".$psno."";
							mysql_query($alert_type_set_query,$db);

							$temp_notification_placement_ids[$emp_email_array['email']."_".$emp_email_array['assignment_id']] = $psno; // set this to the current Assignment id
							// Get Notification ID based on Assignment ID set earlier
							$temp_notification_psno = $temp_notification_placement_ids[$emp_email_array['email']."_".$emp_email_array['assignment_id']];
														
							if(!empty($temp_notification_psno)) 
							{
								//Call to procedure for inserting to notifications_list table of akken_notifications.
								$akken_insert_notificationList = "call akken_notifications.insertNotificationList(".$temp_notification_psno.",'".$emp_email_array['username']."','','".$emp_email_array['mobile']."','".$emp_email_array['smsdomain']."');";
								mysql_query($akken_insert_notificationList,$db);  
							}
						}
					}
					// END OF BLOCK TO AVOID MULTIPLE ENTRIES IN NOTIFICATIONS TABLE					
					$emp_email_array = "";
					$matter=''; 
					$subject='';
					echo ".".$percent;
			        flush();    
			        ob_flush();
			        sleep(1);
			       $i++;
				}
			}
		}
		session_unregister('templatedata');
		return '^^'.$resP;
	}

	//function to send Notifications to Customers
	function custNotificationstemplate(){
		global $db,$companyuser, $maindb;
		$companyuser=strtolower($companyuser);		
		$custTemplate 			= 	array();

		//QUERY TO FETCH NOTIFICATION SETTINGS DATA	
		$notification_settings="select status, notify_to from notifications_settings  where status='1' and mod_id='placement' and notify_status = 'ACTIVE'";
		$notification_result=mysql_query($notification_settings,$db);
		if(mysql_num_rows($notification_result)==1)
		{
			$rrow = mysql_fetch_assoc($notification_result);

			$selCustTemp =  "SELECT  email_body as reminder_body, email_subject as reminder_sub, email_merger_columns as 
							cust_temp_selected_columns FROM notifications_templates WHERE status='ACTIVE' AND mod_id='placement' 
							AND is_default = '1' AND template_type = 'Customer'";
			$resCustTemp =  mysql_query($selCustTemp,$db);
			$rowCustTemp = mysql_fetch_assoc($resCustTemp);
		
			$cust_body 					=	stripslashes($rowCustTemp['reminder_body']);
			$cust_sub 					=	$rowCustTemp['reminder_sub'];
			$cust_selected_columns_list =	$rowCustTemp['cust_temp_selected_columns'];
			$notifyList 				=	$rrow['notify_to'];
			$notifyArr 					= 	explode(",",$notifyList);
			$custTemplate 				= 	array();
			if($cust_selected_columns_list!=''){
				$custTemplate['selcols'] = $cust_selected_columns_list;
			}		

			if(in_array(1, $notifyArr)){
				if($cust_sub != '') 
				{ 
					$custTemplate['subject'] = $cust_sub;
				} 
				else 
				{  
					//$custTemplate['subject'] = "Assignment - {{@assignment_title}} - {{@job_location}}";
					$custTemplate['subject'] = $cust_email_array['employee_name']." placed on ".$cust_email_array['assignment_title'];
				}
			
				if($cust_body != '') 
				{
					$matter 	= $cust_body;
					$matter  	= str_replace('<br>','\n',$matter);
					$custTemplate['matter'] =  $matter;	
				} 
				else 
				{
					// Message content for Email
					$matter ='<table class="mceItemTable"><tbody><tr><td><div>Hello '.$cust_email_array['contact'].',</div><!--==MSG==HEADER==SEPARATOR==--><div id="CRM_ECAM_GREET">&nbsp;</div><div>Employee Name: '.$cust_email_array['employee_name'].'</div><div>Assignment Name: '.$cust_email_array['assignment_title'].'</div><div>Job Location: '.$cust_email_array['job_location'].'</div><div>Job Reports to: '.$cust_email_array['jobreportsto'].'</div><div>Assignment Start Date: '.$cust_email_array['start_date'].'</div><div>Assignment End Date: '.$cust_email_array['end_date'].'</div><br><div>Thanks,</div></td></tr></tbody></table>';

					// replaces <br> tags with newline character as same data used for mail and popup.
					$custTemplate['matter']  = str_replace('\n','<br>',$matter);
					
				}
			}
		}
		return $custTemplate;
	}

	//function to send Notifications to Employee
	function empNotificationstemplate(){
		global $db,$companyuser, $maindb;
		$companyuser=strtolower($companyuser);

		$empTemplate 			= 	array();

		//QUERY TO FETCH NOTIFICATION SETTINGS DATA	
		$notification_settings="select status, notify_to from notifications_settings where status='1' and mod_id='placement' and 
								notify_status = 'ACTIVE'";
		$notification_result=mysql_query($notification_settings,$db);

		if(mysql_num_rows($notification_result)==1)
		{
			$rrow = mysql_fetch_assoc($notification_result);

			$selEmpTemp =  "SELECT  email_body as emp_temp_body, email_subject as emp_temp_sub, email_merger_columns as 
							emp_temp_selected_columns FROM notifications_templates WHERE status='ACTIVE' AND mod_id='placement' 
							AND is_default = '1' AND template_type = 'Employee'";
			$resEmpTemp =  mysql_query($selEmpTemp,$db);
			$rowEmpTemp = mysql_fetch_assoc($resEmpTemp);
		
			$emp_body 					=	stripslashes($rowEmpTemp['emp_temp_body']);
			$emp_sub 					=	$rowEmpTemp['emp_temp_sub'];
			$emp_selected_columns_list 	=	$rowEmpTemp['emp_temp_selected_columns'];
			$notifyList 				=	$rrow['notify_to'];
			$notifyArr 					= 	explode(",",$notifyList);
			$empTemplate 				= 	array();
			if($emp_selected_columns_list!='') 
			{
				$empTemplate['selcols'] = $emp_selected_columns_list;
			}		

			if(in_array(2, $notifyArr)) 
			{
				if($emp_sub != '') 
				{ 
					$empTemplate['subject'] = $emp_sub; 
				} 
				
			
				if($emp_body != '') 
				{
					$matter 	= $emp_body;
					$matter  	= str_replace('<br>','\n',$matter);
					$empTemplate['matter'] =  $matter;	
				} 
				
			}
		}
		return $empTemplate;
	}

	//function to send Notifications to Customers
	function sendCustomerNotifications($custsub, $custtemp, $selcols, $asgnids){
		global $db,$companyuser, $maindb, $username;
		$companyuser=strtolower($companyuser);
		
		$main_query 			= ""; //variable for main query.
		$cust_reportto_query 	= ""; //variable for customer reportto query.
		$cust_contact_query 	= ""; //variable for customer contact query.
		$cust_query 			= ""; //variable for customer query.
		$email_flag 			= 'N'; //default value for email alert in notification table.
		$sms_flag 				= 'N'; //default value for sms alert in notification table.
		$popup_flag 			= 'N'; // //default value for popup alert in notification table.

		//QUERY TO FETCH NOTIFICATION SETTINGS DATA	
		$notification_settings="select status,notify_to,notify_mode from notifications_settings  where status='1' and mod_id='placement' and notify_status = 'ACTIVE'";
		$notification_result=mysql_query($notification_settings,$db);
		if(mysql_num_rows($notification_result)==1)
		{
			$rrow = mysql_fetch_assoc($notification_result);
			$cust_body 					=	stripslashes($custtemp);
			$cust_sub 					=	$custsub;
			$cust_selected_columns_list =	$selcols;
			$cust_selected_columns_val 	= 	explode("|",$cust_selected_columns_list);
			$notifyList 				=	$rrow['notify_to'];
			$notify_to_val    			= 	explode(',',$notifyList);
			$notifymodeList 			=	$rrow['notify_mode'];
			$notify_mode_val    		= 	explode(',',$notifymodeList);			

			$email_flag = 'Y';
			$sms_flag 	= 'N';
			$popup_flag = 'N';
			
			if(in_array('1',$notify_to_val))
			{
				if(in_array("r",$notify_mode_val))
				{
					$cust_reportto_query = $this->getcustReportto($asgnids);
				}					
				if(in_array("c",$notify_mode_val))
				{
					$cust_contact_query = $this->getcustContact($asgnids);
				}
									
				if($cust_reportto_query != ""){
					$cust_query = $cust_reportto_query;
				}

				if($cust_contact_query != ""){
					$cust_query = $cust_contact_query;
				}
				
				if($cust_reportto_query != "" && $cust_contact_query != ""){
					$cust_query = $cust_reportto_query." UNION ".$cust_contact_query;
				}
			}

			if($cust_query !="")
				$cust_main_query = $cust_query;

			// VARIABLES USED TO AVOID MULTIPLE ENTRIES IN NOTIFICATIONS TABLE					
			$temp_notification_placement_ids = array(); //Assignment IDs with notification id stored temporarily.

			//Last inserted id generated from notifications table stored temporarily.
			$temp_notification_psno = "";
			//Customer Email Notification
				
			if($cust_main_query !="")
			{
				$cust_result_email = mysql_query($cust_main_query,$db);
				//LOOP TO INSERT INTO NOTIFICATION TABLE
				while($cust_email_array = mysql_fetch_array($cust_result_email))
				{			
					// AVOID MULTIPLE ENTRIES IN NOTIFICATIONS TABLE
					if(!isset($temp_notification_placement_ids[$cust_email_array['email']."_".$cust_email_array['assignment_id']]))
					{
						if($cust_email_array['username']!=''){
							if($cust_sub != ''){ //customised email subject
								$subject = $this->parseEmailSub($cust_sub,$cust_email_array); //parse the dynamic variables of email subject
							}
						
							if($cust_body != ''){//customised email template content
								$matter = $this->parseEmailBody($cust_body,$cust_email_array);//parse the dynamic variables of email body
								$matter  = str_replace('\n','<br>',$matter);							
							}

							// Message content for SMS - Max 160 chars in SMS
							$sms_matter = "";				

							//Call to procedure for inserting to notifications table of akken_notifications.
							$akken_insert_notification = "call akken_notifications.insertNotification ('".$companyuser."','placement','".$cust_email_array['id']."','".mysql_real_escape_string($subject)."','".mysql_real_escape_string($matter)."','Active','".mysql_real_escape_string($sms_matter)."',@psno)";
							$res = mysql_query($akken_insert_notification,$db);
							$res = mysql_query("select @psno as psno",$db);
							$fetch_psno_result = mysql_fetch_array($res);
							$psno = $fetch_psno_result[0];
							$efrom = getFromEmailID($username);
							$this->savePlacementNotificationMailsInActivities($cust_email_array['username'], $cust_email_array['email'], $subject, $matter, $efrom);

							//Update the flags in the table 
							$alert_type_set_query = "UPDATE akken_notifications.notifications SET alert = '".$popup_flag."', email = '".$email_flag."', sms = '".$sms_flag."' WHERE sno = ".$psno."";
							mysql_query($alert_type_set_query,$db);

							$temp_notification_placement_ids[$cust_email_array['email']."_".$cust_email_array['assignment_id']] = $psno; // set this to the current Assignment id
							// Get Notification ID based on Assignment ID set earlier
							$temp_notification_psno = $temp_notification_placement_ids[$cust_email_array['email']."_".$cust_email_array['assignment_id']];
														
							if(!empty($temp_notification_psno)){
								//Call to procedure for inserting to notifications_list table of akken_notifications.
								$akken_insert_notificationList = "call akken_notifications.insertNotificationList(".$temp_notification_psno.",'".$cust_email_array['username']."','".$cust_email_array['email']."','".$cust_email_array['mobile']."','".$cust_email_array['smsdomain']."');";
								mysql_query($akken_insert_notificationList,$db);  
							}
						}
					}
					// END OF BLOCK TO AVOID MULTIPLE ENTRIES IN NOTIFICATIONS TABLE					
					$cust_email_array = "";
					$matter=''; 
					$subject='';
				}
			}
		}
		return;
	}

	//function to send Notifications to Employee
	function sendEmployeeNotifications($empsub, $emptemp, $selcols, $asgnids){
		global $db,$companyuser, $maindb, $username;
		$companyuser=strtolower($companyuser);
		
		$main_query 			= ""; //variable for main query.
		$emp_reportto_query 	= ""; //variable for employee reportto query.
		$emp_contact_query 		= ""; //variable for employee contact query.
		$emp_query 				= ""; //variable for employee query.
		$email_flag 			= 'N'; //default value for email alert in notification table.
		$sms_flag 				= 'N'; //default value for sms alert in notification table.
		$popup_flag 			= 'N'; // //default value for popup alert in notification table.

		//QUERY TO FETCH NOTIFICATION SETTINGS DATA	
		$notification_settings="select status,notify_to,notify_mode from notifications_settings  where status='1' and mod_id='placement' and notify_status = 'ACTIVE'";
		$notification_result=mysql_query($notification_settings,$db);
		if(mysql_num_rows($notification_result)==1)
		{
			$rrow = mysql_fetch_assoc($notification_result);
			$emp_body 					=	stripslashes($emptemp);
			$emp_sub 					=	$empsub;
			$emp_selected_columns_list 	=	$selcols;
			$emp_selected_columns_val 	= 	explode("|",$emp_selected_columns_list);
			$notifyList 				=	$rrow['notify_to'];
			$notify_to_val    			= 	explode(',',$notifyList);
			$notifymodeList 			=	$rrow['notify_mode'];
			$notify_mode_val    		= 	explode(',',$notifymodeList);			

			$email_flag = 'Y';
			$sms_flag 	= 'Y';
			$popup_flag = 'N';
			
			if(in_array('2',$notify_to_val)) 
			{
				if(in_array("s",$notify_mode_val))
				{
					$emp_sms_query = $this->getempSMS($asgnids);
				}
				
				if(in_array("e",$notify_mode_val))
				{
					$emp_email_query = $this->getempEmail($asgnids);				
				}
						      
				if($emp_sms_query!= ""){
					$emp_query = $emp_sms_query;
				}

				if($emp_email_query != ""){
					$emp_query = $emp_email_query;
				}
				
				if($emp_sms_query != "" && $emp_email_query != ""){
					$emp_query = $emp_sms_query." UNION ".$emp_email_query;
				}
			}

			if($emp_query !="")
				$emp_main_query = $emp_query;

			// VARIABLES USED TO AVOID MULTIPLE ENTRIES IN NOTIFICATIONS TABLE					
			$temp_notification_placement_ids = array(); //Assignment IDs with notification id stored temporarily.

			//Last inserted id generated from notifications table stored temporarily.
			$temp_notification_psno = "";

			//Employee Email Notification
			
			if($emp_main_query !="")
			{
				$emp_result_email = mysql_query($emp_main_query,$db);
				//LOOP TO INSERT INTO NOTIFICATION TABLE
				while($emp_email_array = mysql_fetch_array($emp_result_email))
				{				
					// AVOID MULTIPLE ENTRIES IN NOTIFICATIONS TABLE
					if(!isset($temp_notification_placement_ids[$emp_email_array['email']."_".$emp_email_array['assignment_id']]))
					{
						if($emp_email_array['username']!=''){
							if($emp_sub != ''){ //empomised email subject
								$subject = $this->parseEmailSub($emp_sub,$emp_email_array); //parse the dynamic variables of email subject
							}
						
							if($emp_body != ''){//empomised email template content
								$matter = $this->parseEmailBody($emp_body,$emp_email_array);//parse the dynamic variables of email body
								$matter  = str_replace('\n','<br>',$matter);
							}

							// Message content for SMS - Max 160 chars in SMS
							$sms_matter = $this->getSMSText($emp_email_array['assignment_title'],$emp_email_array['assignment_id'],$emp_email_array['company'],$emp_email_array['job_location'],$emp_email_array['start_date'],$emp_email_array['end_date']);

							//Call to procedure for inserting to notifications table of akken_notifications.
							$akken_insert_notification = "call akken_notifications.insertNotification ('".$companyuser."','placement','".$emp_email_array['id']."','".mysql_real_escape_string($subject)."','".mysql_real_escape_string($matter)."','Active','".mysql_real_escape_string($sms_matter)."',@psno)";
							$res = mysql_query($akken_insert_notification,$db);
							$res = mysql_query("select @psno as psno",$db);
							$fetch_psno_result = mysql_fetch_array($res);
							$psno = $fetch_psno_result[0];
							$efrom = getFromEmailID($username);
							$this->savePlacementNotificationMailsInActivities($emp_email_array['empid'], $emp_email_array['email'], $subject, $matter, $efrom);	

							//Update the flags in the table 
							$alert_type_set_query = "UPDATE akken_notifications.notifications SET alert = '".$popup_flag."', email = '".$email_flag."', sms = '".$sms_flag."' WHERE sno = ".$psno."";
							mysql_query($alert_type_set_query,$db);

							$temp_notification_placement_ids[$emp_email_array['email']."_".$emp_email_array['assignment_id']] = $psno; // set this to the current Assignment id
							// Get Notification ID based on Assignment ID set earlier
							$temp_notification_psno = $temp_notification_placement_ids[$emp_email_array['email']."_".$emp_email_array['assignment_id']];
														
							if(!empty($temp_notification_psno)){
								//Call to procedure for inserting to notifications_list table of akken_notifications.
								$akken_insert_notificationList = "call akken_notifications.insertNotificationList(".$temp_notification_psno.",'".$emp_email_array['username']."','".$emp_email_array['email']."','".$emp_email_array['mobile']."','".$emp_email_array['smsdomain']."');";
								mysql_query($akken_insert_notificationList,$db);  
							}
						}
					}
					// END OF BLOCK TO AVOID MULTIPLE ENTRIES IN NOTIFICATIONS TABLE					
					$emp_email_array = "";
					$matter=''; 
					$subject='';
				}
			}
		}
		return;
	}

	//function to send notifications to assignment when it is approved
	function sendAssignmentNotifications($asgnid){
		
		global $db,$companyuser, $maindb, $username;
		$companyuser=strtolower($companyuser);
		
		$notify_to_list = "";
		$notify_to_val = array();
		$credentials_assoc_with = array();
		
		$main_query 			= ""; //variable for main query.
		$cust_reportto_query 	= ""; //variable for customer reportto query.
		$cust_contact_query 	= ""; //variable for customer contact query.
		$emp_sms_query 			= ""; //variable for employee sms query.
		$emp_email_query 		= ""; //variable for employee email query.
		$cust_query 			= ""; //variable for customer query.
		$emp_query 				= ""; //variable for employee query.
		$email_flag 			= 'N'; //default value for email alert in notification table.
		$sms_flag 				= 'N'; //default value for sms alert in notification table.
		$popup_flag 			= 'N'; // //default value for popup alert in notification table.

		//QUERY TO FETCH NOTIFICATION SETTINGS DATA	
		$notification_settings="select status, notify_mode, notify_to from notifications_settings  where status='1' and mod_id='placement' and notify_status = 'ACTIVE'";
		$notification_result=mysql_query($notification_settings,$db);
		if(mysql_num_rows($notification_result)==1)
		{
			$rrow = mysql_fetch_assoc($notification_result);

			$notify_to_list  	= $rrow['notify_to'];				
			$notify_to_val 		= explode(",",$notify_to_list);
			$notify_mode_list  	= $rrow['notify_mode'];
			$notify_mode_val 	= explode(",",$notify_mode_list);

			//Getting Default Customer Template from New table-notifications_template
			$selDCustTemp =  "SELECT  email_body as reminder_body, email_subject as reminder_sub, email_merger_columns as 
							cust_temp_selected_columns FROM notifications_templates WHERE status='ACTIVE' AND mod_id='placement' 
							AND is_default = '1' AND template_type = 'Customer'";
			$resDCustTemp =  mysql_query($selDCustTemp,$db);
			$rowDCustTemp = mysql_fetch_assoc($resDCustTemp);
	
			$cust_body 					=	stripslashes($rowDCustTemp['reminder_body']);
			$cust_sub 					=	$rowDCustTemp['reminder_sub'];
			$cust_selected_columns_list =	$rowDCustTemp['cust_temp_selected_columns'];
			$cust_selected_columns_val 	= 	explode("|",$cust_selected_columns_list);

			//Getting Default Employee Template from New table-notifications_template
			$selDEmpTemp =  "SELECT  email_body as emp_temp_body, email_subject as emp_temp_sub, email_merger_columns as 
							emp_temp_selected_columns FROM notifications_templates WHERE status='ACTIVE' AND mod_id='placement' 
							AND is_default = '1' AND template_type = 'Employee'";
			$resDEmpTemp =  mysql_query($selDEmpTemp,$db);
			$rowDEmpTemp = mysql_fetch_assoc($resDEmpTemp);

			$emp_body 					=	stripslashes($rowDEmpTemp['emp_temp_body']);
			$emp_sub 					=	$rowDEmpTemp['emp_temp_sub'];
			$emp_selected_columns_list	=	$rowDEmpTemp['emp_temp_selected_columns'];
			$emp_selected_columns_val 	= 	explode("|",$emp_selected_columns_list);				

			/* Conditions to check selected time frequency. According to that will create a phrase for query. if multiple then will create a phrase with OR condition.*/

			/* Condition to check flags for Email */

			if(strpos($rrow['notify_mode'],'r')!==false)
				$email_flag = 'Y';
			if(strpos($rrow['notify_mode'],'s')!==false)
				$sms_flag = 'Y';
			if(strpos($rrow['notify_mode'],'c')!==false)
				$email_flag = 'Y';
			if(strpos($rrow['notify_mode'],'e')!==false)
				$email_flag = 'Y';

			if(in_array('1',$notify_to_val))
			{
				if(in_array("r",$notify_mode_val))
				{
					$cust_reportto_query = $this->getcustReportto($asgnid);
				}
				
				if(in_array("c",$notify_mode_val))
				{
					$cust_contact_query = $this->getcustContact($asgnid);
				}
								
				if($cust_reportto_query != ""){
					$cust_query = $cust_reportto_query;
				}

				if($cust_contact_query != ""){
					$cust_query = $cust_contact_query;
				}
				
				if($cust_reportto_query != "" && $cust_contact_query != ""){
					$cust_query = $cust_reportto_query." UNION ".$cust_contact_query;
				}
			}
			if(in_array('2',$notify_to_val)) 
			{
				if(in_array("s",$notify_mode_val))
				{
					$emp_sms_query = $this->getempSMS($asgnid);
				}
				
				if(in_array("e",$notify_mode_val))
				{
					$emp_email_query = $this->getempEmail($asgnid);			
				}
							  
				if($emp_sms_query!= ""){
					$emp_query = $emp_sms_query;
				}

				if($emp_email_query != ""){
					$emp_query = $emp_email_query;
				}
				
				if($emp_sms_query != "" && $emp_email_query != ""){
					$emp_query = $emp_sms_query." UNION ".$emp_email_query;
				}
			}

			if($cust_query !="")
				$cust_main_query = $cust_query;

			if($emp_query!="")
				$emp_main_query = $emp_query;

			// VARIABLES USED TO AVOID MULTIPLE ENTRIES IN NOTIFICATIONS TABLE					
			$temp_notification_placement_ids = array(); //Assignment IDs with notification id stored temporarily.

			//Last inserted id generated from notifications table stored temporarily.
			$temp_notification_psno = "";

			//Customer Email Notification
			if($cust_main_query !="")
			{
				$cust_result_email = mysql_query($cust_main_query,$db);
				//LOOP TO INSERT INTO NOTIFICATION TABLE
				while($cust_email_array = mysql_fetch_array($cust_result_email))
				{				
					// AVOID MULTIPLE ENTRIES IN NOTIFICATIONS TABLE
					if(!isset($temp_notification_placement_ids[$cust_email_array['email']."_".$cust_email_array['assignment_id']]))
					{
						if($cust_email_array['username']!=''){
							if($cust_sub != ''){ //customised email subject
								$subject = $this->parseEmailSub($cust_sub,$cust_email_array); //parse the dynamic variables of email subject			
							}
						
							if($cust_body != ''){//customised email template content
								$matter = $this->parseEmailBody($cust_body,$cust_email_array);//parse the dynamic variables of email body
								$matter  = str_replace('\n','<br>',$matter);						
							}

							// Message content for SMS - Max 160 chars in SMS
							$sms_matter = "";								

							//Call to procedure for inserting to notifications table of akken_notifications.
							$akken_insert_notification = "call akken_notifications.insertNotification ('".$companyuser."','placement','".$cust_email_array['id']."','".mysql_real_escape_string($subject)."','".mysql_real_escape_string($matter)."','Active','".mysql_real_escape_string($sms_matter)."',@psno)";

							$res = mysql_query($akken_insert_notification,$db);
							$res = mysql_query("select @psno as psno",$db);
							$fetch_psno_result = mysql_fetch_array($res);
							$psno = $fetch_psno_result[0];
							$efrom = getFromEmailID($username);
							$this->savePlacementNotificationMailsInActivities($cust_email_array['username'], $cust_email_array['email'], $subject, $matter, $efrom);

							//Update the flags in the table 
							$alert_type_set_query = "UPDATE akken_notifications.notifications SET alert = '".$popup_flag."', email = '".$email_flag."', sms = '".$sms_flag."' WHERE sno = ".$psno."";
							mysql_query($alert_type_set_query,$db);

							$temp_notification_placement_ids[$cust_email_array['email']."_".$cust_email_array['assignment_id']] = $psno; // set this to the current Assignment id
							// Get Notification ID based on Assignment ID set earlier
							$temp_notification_psno = $temp_notification_placement_ids[$cust_email_array['email']."_".$cust_email_array['assignment_id']];
														
							if(!empty($temp_notification_psno)){
								//Call to procedure for inserting to notifications_list table of akken_notifications.
								$akken_insert_notificationList = "call akken_notifications.insertNotificationList(".$temp_notification_psno.",'".$cust_email_array['username']."','".$cust_email_array['email']."','".$cust_email_array['mobile']."','".$cust_email_array['smsdomain']."');";
								mysql_query($akken_insert_notificationList,$db);
							}
						}
					}
					// END OF BLOCK TO AVOID MULTIPLE ENTRIES IN NOTIFICATIONS TABLE
					$cust_email_array = "";
					$matter=''; 
					$subject='';
				}
			}

			//Employee Email Notifications
			if($emp_main_query !="")
			{
				$emp_result_email = mysql_query($emp_main_query,$db);
				//LOOP TO INSERT INTO NOTIFICATION TABLE
				while($emp_email_array = mysql_fetch_array($emp_result_email))
				{			
					// AVOID MULTIPLE ENTRIES IN NOTIFICATIONS TABLE
					if(!isset($temp_notification_placement_ids[$emp_email_array['email']."_".$emp_email_array['assignment_id']]))
					{
						if($emp_email_array['username']!=''){
							if($emp_sub != ''){ //customised email subject
							$subject = $this->parseEmailSub($emp_sub,$emp_email_array); //parse the dynamic variables of email subject
						
							}
						
							if($emp_body != ''){//customised email template content
								$matter = $this->parseEmailBody($emp_body,$emp_email_array);//parse the dynamic variables of email body
								$matter  = str_replace('\n','<br>',$matter);						
							}

							// Message content for SMS - Max 160 chars in SMS
							$sms_matter = $this->getSMSText($emp_email_array['assignment_title'],$emp_email_array['assignment_id'],$emp_email_array['company'],$emp_email_array['job_location'],$emp_email_array['start_date'],$emp_email_array['end_date']);

							//Call to procedure for inserting to notifications table of akken_notifications.
							$akken_insert_notification = "call akken_notifications.insertNotification ('".$companyuser."','placement','".$emp_email_array['id']."','".mysql_real_escape_string($subject)."','".mysql_real_escape_string($matter)."','Active','".mysql_real_escape_string($sms_matter)."',@psno)";
							$res = mysql_query($akken_insert_notification,$db);
							$res = mysql_query("select @psno as psno",$db);
							$fetch_psno_result = mysql_fetch_array($res);
							$psno = $fetch_psno_result[0];
							$efrom = getFromEmailID($username);
							$this->savePlacementNotificationMailsInActivities($emp_email_array['empid'], $emp_email_array['email'], $subject, $matter, $efrom);

							//Update the flags in the table
							$alert_type_set_query = "UPDATE akken_notifications.notifications SET alert = '".$popup_flag."', email = '".$email_flag."', sms = '".$sms_flag."' WHERE sno = ".$psno."";
							mysql_query($alert_type_set_query,$db);

							$temp_notification_placement_ids[$emp_email_array['email']."_".$emp_email_array['assignment_id']] = $psno; // set this to the current Assignment id
							// Get Notification ID based on Assignment ID set earlier
							$temp_notification_psno = $temp_notification_placement_ids[$emp_email_array['email']."_".$emp_email_array['assignment_id']];
														
							if(!empty($temp_notification_psno)){
								//Call to procedure for inserting to notifications_list table of akken_notifications.
								$akken_insert_notificationList = "call akken_notifications.insertNotificationList(".$temp_notification_psno.",'".$emp_email_array['username']."','".$emp_email_array['email']."','".$emp_email_array['mobile']."','".$emp_email_array['smsdomain']."');";
								mysql_query($akken_insert_notificationList,$db);
							}
						}
					}
					// END OF BLOCK TO AVOID MULTIPLE ENTRIES IN NOTIFICATIONS TABLE					
					$emp_email_array 	= "";
					$matter 			= '';
					$subject			= '';
				}
			}
		}
		return;
	}

	// Saves mail details in activities grid
	public function savePlacementNotificationMailsInActivities($recipientUsername, $recipientEmail, $subject, $message, $efrom)
	{
		global $username, $db;		
		
		$folder 	= "sentmessages";
		$stats 		= "Active"; 
		$attach 	=  'N'; 
		$mailtype 	= "text/html";
	
		//$efrom = getFromEmailID($username);
		$cc = "";
		$bcc = "";
		$sno = "";
			
		$app_emp_username = $recipientUsername;
		$to = $recipientEmail;
		$last_id = mail_insert($folder, $efrom, $to, $cc, $bcc, $message, $subject, '', '', $attach, $mailtype, $sent, $stats, $cmnid);

		/* Getting From, To, CC email addresses into $fromaddcoll, $toaddcoll and $ccaddcoll variable for Activity Tracking */
		$fromaddcoll 	= $efrom;
		$toaddcoll 		= $to;
		$ccaddcoll 		= $cc;

		/* Getting BCC email addresses into $ccaddcoll variable if we have email addresses in BCC for Activity Tracking */
		if($bcc != "")
			$ccaddcoll .= ",".$bcc;
		
		/* Getting Employee and Candidate username and sno to make relation with activities */
		$conid = $app_emp_username;
		
		/* Inserting into contact_email and contact_email_body tables to get content in Activities Screen */
		$cmid = cm_mail_insert($conid, $subject, $message, $mailtype, $fromaddcoll, $toaddcoll, $ccaddcoll, '');

		/* Inserting into cmngmt_pr table to get content in Activities Grid */
		$cmnid = cmngmt_ins($conid, $cmid, 'REmail', $subject, '');

		/* Getting cmngmt_pr table inserted id and updating the mail_headers table for making relation between Activities and Emails */
		if($cmnid != "")
		{
			$updMailHeaders = "UPDATE mail_headers SET conid=".mysql_real_escape_string($cmnid)." WHERE mailid = ".$last_id;
			mysql_query($updMailHeaders, $db);
		}
		
		// Storing Sender's Details
		$last_id_sender = mail_insert($folder, $efrom, $to, $cc, $bcc, $message, $subject, '', '', $attach, $mailtype, $sent, $stats, $cmnid);

		$conid_sender = 'emp'.$username;
		/* Inserting into contact_email and contact_email_body tables to get content in Activities Screen */
		$cmid_sender = cm_mail_insert($conid_sender, $subject, $message, $mailtype, $fromaddcoll, $toaddcoll, $ccaddcoll, '');

		/* Inserting into cmngmt_pr table to get content in Activities Grid */
		$cmnid_sender = cmngmt_ins($conid_sender, $cmid_sender, 'Email', $subject, '');

		/* Getting cmngmt_pr table inserted id and updating the mail_headers table for making relation between Activities and Emails */
		if($cmnid_sender != "")
		{
			$updMailHeaders = "UPDATE mail_headers SET conid=".mysql_real_escape_string($cmnid_sender)." WHERE mailid = ".$last_id_sender;
			mysql_query($updMailHeaders, $db);
		}
		return;
	}

	//function to get SMS text
	public function getSMSText($assignment_title,$assignment_id,$company,$job_location,$start_date,$end_date){

		if($assignment_title=='Assignment Name not available'){
			$assignment_title = '';
		}else{
			$assignment_title = $assignment_title;
		}
		if($company=='Customer name not available'){
			$company = '';
		}else{
			$company = $company;
		}
		if($job_location=='Job location not available'){
			$job_location = '';
		}else{
			$job_location = $job_location;
		}
		if($start_date=='Assignment Start Date not available'){
			$start_date = '';
		}else{
			$start_date = $start_date;
		}
		if($end_date=='Assignment End Date not available'){
			$end_date = '';
		}else{
			$end_date = $end_date;
		}

		$sms_matter = "Placed on ".$assignment_title."-".$assignment_id." at ".$company." - ".$job_location.". ".$start_date." and ".$end_date;
		return $sms_matter;
	}

	//Inserting Template Data Fot Placement Notification
	function updateNotificationTemplates($updateData) 
	{	
		global $db,$username;

		// Customer Record Query
		$sel_query = "SELECT COUNT(1) FROM notifications_templates WHERE status='ACTIVE' AND mod_id='placement' AND 
					id='".$updateData['temp_id']."'";
		$sel_res = mysql_query($sel_query,$db);
		$sel_row = mysql_fetch_row($sel_res);

		if($sel_row[0] > 0)
		{
			$ins_query = "update notifications_templates set 
			template_name = '".mysql_real_escape_string(trim($updateData['template_name']))."', 
			is_overwrite =  '".$updateData['is_overwrite']."', 
			email_subject = '".mysql_real_escape_string(trim($updateData['temp_subject']))."', 
			email_body = '".mysql_real_escape_string(trim($updateData['temp_body']))."', 
			email_signature_id = '".$updateData['temp_signature_id']."',email_header_id = '".$updateData['temp_header_id']."', 
			email_merger_columns = '".$updateData['temp_selected_columns']."', locations = '".$updateData['location']."', 
			departments = '".$updateData['department']."', status = 'ACTIVE', mdate = NOW(), muser = '".$username."' 
			Where id='".$updateData['temp_id']."'";
			mysql_query($ins_query,$db);
		}
	} 

	function insertNotificationTemplates($insertData) 
	{
		global $db,$username;

		$sqlInsCheck = "SELECT COUNT(1) FROM notifications_templates WHERE status='ACTIVE' AND mod_id='placement' AND 
					template_name='".mysql_real_escape_string(trim($insertData['template_name']))."' AND 
					template_type='".mysql_real_escape_string(trim($insertData['template_type']))."'";
		$resInsCheck = mysql_query($sqlInsCheck,$db);
		$rowInsCheck = mysql_fetch_row($resInsCheck);

		if($rowInsCheck[0] > 0)
		{
			return 1;
		}
		else 
		{
		//To insert new record in notifications_templates table.
		$ins_query = "insert into notifications_templates(mod_id, template_name, template_type, is_default, is_overwrite, email_subject, email_body, email_signature_id, email_header_id, email_merger_columns, locations, departments, status, cdate, cuser,mdate,muser) values('".$insertData['module']."', '".mysql_real_escape_string(trim($insertData['template_name']))."', 
			'".$insertData['template_type']."', '0', '".$insertData['is_overwrite']."', 
			'".mysql_real_escape_string(trim($insertData['temp_subject']))."', 
			'".mysql_real_escape_string(trim($insertData['temp_body']))."', '".$insertData['temp_signature_id']."', 
			'".$insertData['temp_header_id']."', '".$insertData['temp_selected_columns']."', '".$insertData['location']."', 
			'".$insertData['department']."', 'ACTIVE', NOW(), '".$username."', '', '')";
		mysql_query($ins_query,$db);
		return 0;
		}
	}

	//Inserting SMS Template Data Fot Placement Notification
	function updateSMSNotificationTemplate($updateSMSData) 
	{	
		global $db,$username;

		$selSMS_query = "SELECT COUNT(1) FROM sms_templates WHERE status='ACTIVE' AND id='".$updateSMSData['temp_id']."'";
		$selSMS_res = mysql_query($selSMS_query,$db);
		$selSMS_row = mysql_fetch_row($selSMS_res);

		if($selSMS_row[0] > 0)
		{
			$insSMS_query = "update sms_templates set 
			template_name = '".mysql_real_escape_string(trim($updateSMSData['template_name']))."', 
			sms_body = '".mysql_real_escape_string(trim($updateSMSData['sms_body']))."', 
			sms_merger_columns = '".$updateSMSData['sms_selected_columns']."', status = 'ACTIVE', mdate = NOW(), muser = '".$username."' 
			Where id='".$updateSMSData['temp_id']."'";
			mysql_query($insSMS_query,$db);
		}
	} 

	function insertSMSNotificationTemplate($insertSMSData) 
	{
		global $db,$username;

		$sqlSMSInsCheck = "SELECT COUNT(1) FROM sms_templates WHERE status='ACTIVE' AND 
					template_name='".mysql_real_escape_string(trim($insertSMSData['template_name']))."' AND 
					template_type='".mysql_real_escape_string(trim($insertSMSData['template_type']))."'";
		$resSMSInsCheck = mysql_query($sqlSMSInsCheck,$db);
		$rowSMSInsCheck = mysql_fetch_row($resSMSInsCheck);

		if($rowSMSInsCheck[0] > 0)
		{
			return 1;
		}
		else 
		{
			$insSMSQuery = "INSERT INTO sms_templates(mod_id, template_name, template_type, is_default, sms_body, sms_merger_columns, status, 
						cdate, cuser,mdate,muser) VALUES('".$insertSMSData['module']."', 
						'".mysql_real_escape_string(trim($insertSMSData['template_name']))."', 
						'".$insertSMSData['template_type']."', '0', '".mysql_real_escape_string(trim($insertSMSData['sms_body']))."', 
						'".$insertSMSData['sms_selected_columns']."', 'ACTIVE', NOW(), '".$username."', '', '')";
			mysql_query($insSMSQuery,$db);
			return 0;
		}
	}

	//Update Template Data For Timesheet Notification
	function updateTimesheetNotificationTemp($updateData,$template_type) 
	{	
		global $db,$username;

		// Customer Record Query
		$sel_query = "SELECT COUNT(1) FROM notifications_templates WHERE status='ACTIVE' AND mod_id='".$template_type."' AND 
					id='".$updateData['temp_id']."'";
		$sel_res = mysql_query($sel_query,$db);
		$sel_row = mysql_fetch_row($sel_res);

		if($sel_row[0] > 0)
		{
			$ins_query = "update notifications_templates set 
			template_name = '".mysql_real_escape_string(trim($updateData['template_name']))."', 
			is_overwrite =  '".$updateData['is_overwrite']."', 
			email_subject = '".mysql_real_escape_string(trim($updateData['temp_subject']))."', 
			email_body = '".mysql_real_escape_string(trim($updateData['temp_body']))."', 
			email_signature_id = '".$updateData['temp_signature_id']."',email_header_id = '".$updateData['temp_header_id']."', 
			email_merger_columns = '".$updateData['temp_selected_columns']."', locations = '".$updateData['location']."', 
			departments = '".$updateData['department']."', status = 'ACTIVE', mdate = NOW(), muser = '".$username."' 
			Where id='".$updateData['temp_id']."'";
			mysql_query($ins_query,$db);
		}
	} 

	//Update Template Notification Status For Timesheet Notification
	function updateTimesheetNotificationStatus($updateData,$template_type) 
	{	
		global $db,$username;

		$record_id = "";
		// Customer Record Query
		$sel_query = "SELECT COUNT(1),id FROM notifications_templates WHERE status='ACTIVE' AND mod_id='".$template_type."' AND 
					id='".$updateData['temp_id']."'";
		$sel_res = mysql_query($sel_query,$db);
		$sel_row = mysql_fetch_row($sel_res);

		if($sel_row[0] > 0)
		{
			$ins_query = "update notifications_templates set 
			is_overwrite =  '".$updateData['is_overwrite']."', 
			status = 'ACTIVE', mdate = NOW(), muser = '".$username."' 
			Where id='".$updateData['temp_id']."'";
			mysql_query($ins_query,$db);
			$record_id = $sel_row[1]; 
		}
		return $record_id;
	}
	//Updating Template Data For ACA Notification
	function updateACANotificationTemplates($updateData) 
	{	
		global $db,$username;

		// ACA Record Query
		$sel_query = "SELECT COUNT(1) FROM notifications_templates WHERE status='ACTIVE' AND mod_id='ACA' AND 
					id='".$updateData['temp_id']."'";
		$sel_res = mysql_query($sel_query,$db);
		$sel_row = mysql_fetch_row($sel_res);

		if($sel_row[0] > 0)
		{
			$update_query = "update notifications_templates set 
			template_name = '".mysql_real_escape_string(trim($updateData['template_name']))."', 
			email_subject = '".mysql_real_escape_string(trim($updateData['temp_subject']))."', 
			email_body = '".mysql_real_escape_string(trim($updateData['temp_body']))."'
			Where id='".$updateData['temp_id']."'";
			mysql_query($update_query,$db);
		}
	} 

	//function to update status and insert task notification with status active
	function updateTaskNotificationTemp($updateData) 
	{
		global $db,$username;
		// Find any active records
		$sel_query = "SELECT COUNT(1) FROM notifications_settings WHERE notify_status='ACTIVE' and mod_id='task'";
		$sel_res = mysql_query($sel_query,$db);
		$sel_row = mysql_fetch_row($sel_res);

		if($sel_row[0]>0)
		{
			//checking if db value for emailSetup id is equal to value selected from dropdown
			if($updateData['task_EmailSetupSno'] != $updateData['task_oldEmailSno']) 
			{
				$sqlUpd = "INSERT INTO his_notifications_settings(mod_id,email_account_sno,notify_status,mdate,muser) 
						VALUES('task','".$updateData['task_oldEmailSno']."','Backup',NOW(),'".$username."')";
				mysql_query($sqlUpd,$db);
			}

			//Update any active record as backup before inserting any new record with status active
			$upd_query = "update notifications_settings set notify_status='BACKUP',muser='".$username."',mdate=NOW() WHERE notify_status='ACTIVE' and mod_id='task'";
			mysql_query($upd_query,$db);
		}

		//Insert updated values to the table		
		//If Placement Notification Management is enabled then will run the query with current POST values.
		
		if($updateData['task_notification']) 
		{
			$ins_query = "insert into notifications_settings(mod_id, email_account_sno, notify_mode,notify_time, status, notify_to, notify_status, cdate, cuser, send_reminder) values('".$updateData['module']."', '".$updateData['task_EmailSetupSno']."', '".$updateData['notifymode']."', '' ,'".$updateData['task_notification']."','".$updateData['notify_people']."', 'ACTIVE',NOW(), '".$username."', '')";
			mysql_query($ins_query,$db);
		}

		// Customer Record Query
		$sel_query = "SELECT COUNT(1) FROM notifications_templates WHERE status='ACTIVE' AND mod_id='".$updateData['module']."' AND 
					id='".$updateData['temp_id']."'";
		$sel_res = mysql_query($sel_query,$db);
		$sel_row = mysql_fetch_row($sel_res);

		if($sel_row[0] > 0)
		{
			$upd_query = "UPDATE notifications_templates SET 
			email_subject = '".mysql_real_escape_string(trim($updateData['temp_subject']))."', 
			email_body = '".mysql_real_escape_string(trim($updateData['temp_body']))."', 
			email_signature_id = '".$updateData['temp_signature_id']."',email_header_id = '".$updateData['temp_header_id']."',
			email_merger_columns='".$updateData['temp_selected_columns']."',
			status = 'ACTIVE', mdate = NOW(), muser = '".$username."' 
			WHERE id='".$updateData['temp_id']."'";
			mysql_query($upd_query,$db);
		}
		return "Success";
	}
	//Updating Template Data For Invoice Deliver Notification for CSS usess
	function updateInvoiceDeliverNotificationTemp($updateData, $template_type)
	{
		global $db,$username;

		// Customer Record Query
		$sel_query = "SELECT COUNT(1) FROM notifications_templates WHERE status='ACTIVE' AND mod_id='".$template_type."' AND 
					id='".$updateData['temp_id']."'";
		$sel_res = mysql_query($sel_query,$db);
		$sel_row = mysql_fetch_row($sel_res);

		if($sel_row[0] > 0)
		{
			$upd_query = "update notifications_templates set 
			template_name = '".mysql_real_escape_string(trim($updateData['template_name']))."', 
			is_overwrite =  '".$updateData['is_overwrite']."', 
			email_subject = '".mysql_real_escape_string(trim($updateData['temp_subject']))."', 
			email_body = '".mysql_real_escape_string(trim($updateData['temp_body']))."', 
			email_signature_id = '".$updateData['temp_signature_id']."',email_header_id = '".$updateData['temp_header_id']."',
			status = 'ACTIVE', mdate = NOW(), muser = '".$username."' , manual_notify='".$updateData['manual_notify']."'
			where id='".$updateData['temp_id']."'";
			mysql_query($upd_query,$db);
		}
		return $sel_query;
	}

	//Updating Template Data For Invoice Download Notification for internal users
	function updateInvoiceDownloadNotificationTemp($updateData, $template_type,$template_type2,$is_overwrite)
	{
		global $db,$username;

		$invoice_css_download_oldsno = $updateData['invoice_css_download_oldsno'];
		$invoice_css_download_sno 	 = $updateData['invoice_css_download_sno'];
		$emp_list_id				 = $updateData['emp_list_id'];
		$notify_people				 = $updateData['notify_people'];
		$notify_type				 = $updateData['notify_type'];
		$notify_roles				 = $updateData['notify_roles'];

		// Customer Record Query
		$sel_query = "SELECT COUNT(1) FROM notifications_templates WHERE status='ACTIVE' AND mod_id='".$template_type."' AND 
					id='".$updateData['temp_id']."'";
		$sel_res = mysql_query($sel_query,$db);
		$sel_row = mysql_fetch_row($sel_res);

		if($sel_row[0] > 0)
		{
			$upd_query = "update notifications_templates set 
			is_overwrite =  '".$is_overwrite."',
			email_subject = '".mysql_real_escape_string(trim($updateData['inv_download_subject']))."', 
			email_body = '".mysql_real_escape_string(trim($updateData['msg_body']))."', 
			status = 'ACTIVE', mdate = NOW(), muser = '".$username."', manual_notify='".$updateData['manual_notify']."' 
			where id='".$updateData['temp_id']."'";
			mysql_query($upd_query,$db);
		}

	
		$inv_down_que = "SELECT count(1) FROM notifications_settings WHERE mod_id ='".$template_type."' AND status = 1 AND 
					notify_status = 'ACTIVE'";
		$inv_down_res = mysql_query($inv_down_que,$db);
		$inv_down_row = mysql_fetch_row($inv_down_res);

		
		$inv_delvr_que = "SELECT count(1) FROM notifications_settings WHERE mod_id ='".$template_type2."' AND status = 1 AND 
					notify_status = 'ACTIVE'";
		$inv_delvr_res = mysql_query($inv_delvr_que,$db);
		$inv_delvr_row = mysql_fetch_row($inv_delvr_res);

		if($inv_delvr_row[0] > 0) 
		{
			//getting backup record of active record present in db before inserting a new record
			$updQuery = "UPDATE notifications_settings SET notify_status = 'BACKUP',muser = '".$username."', mdate=NOW() WHERE 
						notify_status = 'ACTIVE' and mod_id ='".$template_type2."'";
			mysql_query($updQuery,$db);

			//inserting new record after taking backup
		

		      $sqlUpd = "INSERT INTO notifications_settings(mod_id, email_account_sno, notify_mode, status, notify_status, credentials_assoc_with, cdate, cuser, mdate, muser, notify_roles) VALUES('".$template_type2."','".$invoice_css_download_sno."',
				'',1,'ACTIVE','',NOW(),1,NOW(),1,'".$notify_roles."')";
				mysql_query($sqlUpd,$db);
		}
		
		if($inv_down_row[0] > 0) 
		{
			//checking if db value for emailSetup id is equal to value selected from dropdown
			if($invoice_css_download_sno != $invoice_css_download_oldsno) 
			{
				$sqlUpd = "INSERT INTO his_notifications_settings(mod_id,email_account_sno,notify_status,mdate,muser,notify_people,notify_type,notify_roles) 
						VALUES('".$template_type."','".$invoice_css_download_oldsno."','Backup',NOW(),'".$username."','".$notify_people."','".$notify_type."','".$notify_roles."')";
				mysql_query($sqlUpd,$db);
			}

			//getting backup record of active record present in db before inserting a new record
			$updQuery = "UPDATE notifications_settings SET notify_status = 'BACKUP',muser = '".$username."', mdate=NOW() WHERE 
						notify_status = 'ACTIVE' and mod_id ='".$template_type."'";
			mysql_query($updQuery,$db);
			
			//inserting new record after taking backup
			$sqlIns = "INSERT INTO notifications_settings(mod_id, email_account_sno, notify_mode, status, notify_people, notify_status, credentials_assoc_with, cdate, cuser, mdate, muser, notify_type,notify_roles) VALUES('".$template_type."','".$invoice_css_download_sno."',
				'',1,'".$notify_people."','ACTIVE','',NOW(),1,NOW(),1,'".$notify_type."','".$notify_roles."')";
			mysql_query($sqlIns,$db);	
		}
	}

	//Update Template Notification Status For CSS users Invoice Notification
	function updateInvoiceNotificationStatus($updateData,$template_type) 
	{	
		global $db,$username;

		$record_id = "";
		$sel_query = "SELECT COUNT(1),id FROM notifications_templates WHERE status='ACTIVE' AND mod_id='".$template_type."' AND 
					id='".$updateData['temp_id']."'";

		$sel_res = mysql_query($sel_query,$db);
		$sel_row = mysql_fetch_row($sel_res);
		
		if($sel_row[0] >0)
		{
			$ins_query = "update notifications_templates set 
			is_overwrite =  '".$updateData['is_overwrite']."', 
			status = 'ACTIVE', mdate = NOW(), muser = '".$username."', manual_notify='".$updateData['manual_notify']."'
			Where id='".$updateData['temp_id']."'";
			$bb = mysql_query($ins_query,$db);
			$sss= mysql_affected_rows($bb);
			$record_id = $sel_row[1]; 
		}
		return $record_id;
	}
	
	//function to save cico notification details 
	function insertCICONotifications($insertData) 
	{
		global $db,$username;

		//checking if db value for emailSetup id is equal to value selected from dropdown
		if($insertData['cico_EmailSetupSno'] != $insertData['cico_oldEmailSno']) 
		{
			$sqlUpd = "INSERT INTO his_notifications_settings(mod_id,email_account_sno,notify_status,mdate,muser) 
					VALUES('cico','".$insertData['cico_oldEmailSno']."','Backup',NOW(),'".$username."')";
					
			//echo $sqlUpd."<br><br>";
			mysql_query($sqlUpd,$db);
		}

		//To insert new record in notifications_settings table.
		$ins_query = "insert into notifications_settings(mod_id, email_account_sno, notify_mode, notify_time, status, notify_to, 
					notify_people, credentials_assoc_with, cdate, cuser, mdate, muser) values('".$insertData['module']."', 
					'".$insertData['cico_EmailSetupSno']."', '".$insertData['notifymode_list']."', '', 
					'".$insertData['notification']."', '".$insertData['subscribers_list']."', '', '', NOW(), '".$username."', NOW(), 
					'".$username."')";
		mysql_query($ins_query,$db);
		//echo $ins_query; exit;
	}

	//function for update status and insert cico notification with status active
	function updateCICONotification($updateData) 
	{
		global $db,$username;
		// Find any active records
		$sel_query = "SELECT COUNT(1) FROM notifications_settings WHERE notify_status='ACTIVE' and mod_id='cico'";
		$sel_res = mysql_query($sel_query,$db);
		$sel_row = mysql_fetch_row($sel_res);

		if($sel_row[0]>0)
		{
			//checking if db value for emailSetup id is equal to value selected from dropdown
			if($updateData['cico_EmailSetupSno'] != $updateData['cico_oldEmailSno']) 
			{
				echo $sqlUpd = "INSERT INTO his_notifications_settings(mod_id,email_account_sno,notify_status,mdate,muser) 
						VALUES('cico','".$updateData['cico_oldEmailSno']."','Backup',NOW(),'".$username."')";
				mysql_query($sqlUpd,$db);
			}

			//Update any active record as backup before inserting any new record with status active
			$upd_query = "update notifications_settings set notify_status='BACKUP',muser='".$username."',mdate=NOW() WHERE notify_status='ACTIVE' and mod_id='cico'";
			mysql_query($upd_query,$db);
		}

		//Insert updated values to the table		
		//If CICO Notification Management is enabled then will run the query with current POST values.
		if($updateData['notification']) 
		{
			$ins_query = "insert into notifications_settings(mod_id, email_account_sno, notify_mode, notify_time, status, notify_to, notify_people, notify_status, credentials_assoc_with, cdate, cuser, send_reminder) values('".$updateData['module']."', '".$updateData['cico_EmailSetupSno']."', '".$updateData['notifymode_list']."', '','".$updateData['notification']."', 
				'".$updateData['subscribers_list']."', '', 'ACTIVE', '', NOW(), '".$username."', '')";
			mysql_query($ins_query,$db);
		}
	}
	
	//Update Template Data For CICO Notification
	function updateCICONotificationTemp($updateData,$template_type) 
	{	
		global $db,$username;

		// Customer Record Query
		$sel_query = "SELECT COUNT(1) FROM notifications_templates WHERE status='ACTIVE' AND mod_id='".$template_type."' AND 
					id='".$updateData['temp_id']."'";
		$sel_res = mysql_query($sel_query,$db);
		$sel_row = mysql_fetch_row($sel_res);

		if($sel_row[0] > 0)
		{
			$ins_query = "update notifications_templates set 
			template_name = '".mysql_real_escape_string(trim($updateData['template_name']))."', 
			is_overwrite =  '".$updateData['is_overwrite']."', 
			email_subject = '".mysql_real_escape_string(trim($updateData['temp_subject']))."', 
			email_body = '".mysql_real_escape_string(trim($updateData['temp_body']))."', 
			email_signature_id = '".$updateData['temp_signature_id']."',email_header_id = '".$updateData['temp_header_id']."', 
			email_merger_columns = '".$updateData['temp_selected_columns']."', locations = '".$updateData['location']."', 
			departments = '".$updateData['department']."', status = 'ACTIVE', mdate = NOW(), muser = '".$username."' 
			Where id='".$updateData['temp_id']."'";
			mysql_query($ins_query,$db);
		}
	} 
	
	//Update Template Notification Status For CICO Notification
	function updateCICONotificationStatus($updateData,$template_type) 
	{	
		global $db,$username;

		$record_id = "";
		// Customer Record Query
		$sel_query = "SELECT COUNT(1),id FROM notifications_templates WHERE status='ACTIVE' AND mod_id='".$template_type."' AND 
					id='".$updateData['temp_id']."'";
		$sel_res = mysql_query($sel_query,$db);
		$sel_row = mysql_fetch_row($sel_res);

		if($sel_row[0] > 0)
		{
			$ins_query = "update notifications_templates set 
			is_overwrite =  '".$updateData['is_overwrite']."', 
			status = 'ACTIVE', mdate = NOW(), muser = '".$username."' 
			Where id='".$updateData['temp_id']."'";
			mysql_query($ins_query,$db);
			$record_id = $sel_row[1]; 
		}
		return $record_id;
	}
	
	//function to update cico template details - From CICO dashboard
	function updateCICOtemplate($cicoData) 
	{
		global $db,$username;
		// Find any active records
		$sel_query = "SELECT COUNT(1) as count,id FROM notifications_templates WHERE id = ".$cicoData['teml_id']." and 
					status='ACTIVE' and mod_id='cico'";
		$sel_res = mysql_query($sel_query,$db);
		$sel_row = mysql_fetch_row($sel_res);
	
		if($sel_row[0] > 0)
		{
			$cico_temp = stripslashes($cicoData['cico_temp']);
			$cico_subject = stripslashes($cicoData['cico_subject']);

			//Insert updated values to the table		
			$temp_query = "update notifications_templates set 
			template_name = '".mysql_real_escape_string(trim($cicoData['template_name']))."', 
			is_overwrite = '".$cicoData['is_overwrite']."', 
			email_subject = '".mysql_real_escape_string(trim($cico_subject))."', 
			email_body = '".mysql_real_escape_string(trim($cico_temp))."', 
			email_signature_id = '".$cicoData['cico_sign']."', email_header_id = '".$cicoData['cico_header']."', 
			muser = '".$username."', mdate = NOW() where id = ".$cicoData['teml_id']."";
			mysql_query($temp_query,$db);
		}
		return;
	}
	
	//function to send Notifications to Employee - From CICO dashboard
	function sendCICONotifications($asgnids,$template_id,$onetime_edit) 
	{
		global $db,$companyuser,$maindb,$username,$maildb,$curtime_header,$placementmailSetup,$smtp;
		
		$companyuser=strtolower($companyuser);
		
		//QUERY TO FETCH NOTIFICATION SETTINGS DATA	
		$notification_settings="select status,notify_mode from notifications_settings  where status='1' and mod_id='cico' and notify_status = 'ACTIVE'";
		$notification_result=mysql_query($notification_settings,$db);
		
		
		if(mysql_num_rows($notification_result)==1)
		{
			if($onetime_edit == 1)
			{
				$selTemp = "SELECT email_subject, email_body, email_merger_columns,template_type FROM notifications_templates WHERE id = ".$template_id."";
				$resTemp = mysql_query($selTemp, $db);
				$rowTemp = mysql_fetch_assoc($resTemp);

				$emp_body 					=	$rowTemp['email_body'];
				$emp_body 					= 	stripslashes($emp_body);
				$emp_sub 					=	stripslashes($rowTemp['email_subject']);
				$emp_selected_columns_list  =	$rowTemp['email_merger_columns'];
				$emp_selected_columns_val 	= 	explode("|",$emp_selected_columns_list);	
				$emp_template_type 		= $rowTemp['template_type'];
			} 
			else 
			{
				$emp_body         			= $_SESSION['templatedata'][$template_id]['cico_temp'];
				$emp_body         			= stripslashes($emp_body);
				$emp_sub       			 	= stripslashes($_SESSION['templatedata'][$template_id]['cico_subject']);
				$emp_selected_columns_list  = $_SESSION['templatedata'][$template_id]['cico_selcols'];
				$emp_selected_columns_val  	= explode("|",$emp_selected_columns_list);
				$emp_template_type 		= $_SESSION['templatedata'][$template_id]['template_type'];
			}	
			
			$nsettings_row = mysql_fetch_assoc($notification_result);
			$notify_mode = explode(",", $nsettings_row['notify_mode']);
			
			$email_flag = 'N';
			$sms_flag 	= 'Y';
			$popup_flag = 'N';
			
			// VARIABLES USED TO AVOID MULTIPLE ENTRIES IN NOTIFICATIONS TABLE					
			$temp_notification_placement_ids = array(); //Assignment IDs with notification id stored temporarily.

			//Last inserted id generated from notifications table stored temporarily.
			$temp_notification_psno = "";
			
			$data_array = array();
			//$empSql = "SELECT sno,username,email,other_phone,emp_fname AS first_name,emp_lname AS last_name FROM emp_list where sno IN (".$asgnid.")";
			$empSql = "SELECT e.sno,e.username,e.email,e.emp_fname AS first_name,e.emp_lname AS last_name,IF(hg.mobile IS NULL, '', hg.mobile) AS mobile,IF(hg.esms='N','',hg.mser_domain) AS smsdomain FROM emp_list e 
            LEFT JOIN hrcon_general hg ON (e.username = hg.username) 
            WHERE e.sno IN (".$asgnids.")";
			$empQuery = mysql_query($empSql,$db);
			while($emp_email_array = mysql_fetch_assoc($empQuery))
			{
				$data_array[$emp_email_array['sno']] = $emp_email_array;
			}
			
			//echo "result: ".$empSql; exit;
			
			$empIds = explode(',', $asgnids);
			
			if(count($empIds) > 0)
			{
				//getting email address and email setup sno from fucntion
				$combinedSetUpData = $placementmailSetup->getSetupMailIDNlCnt('cico');
				$explodedSetUpData = explode('^',$combinedSetUpData);

				$from 		= $explodedSetUpData[2];
			    $lcntVal 	= $explodedSetUpData[1];
			    $setupSno	= $explodedSetUpData[3];

			    $i=0;
			    $total = count($empIds);
				$shift_dates_array = explode(",",$_SESSION['cicoSessionShiftDates']);
				$shift_times_array = explode(",",$_SESSION['cicoSessionShiftTimes']);
				
				foreach($empIds as $empId)
				{
					$percent = intval($i/$total * 100);	
					
					if($data_array[$empId]['username']!='') 
					{
						//parse the dynamic variables of email subject
						if($emp_sub != '') 
						{
							$subject = str_replace("{{@first_name}}",$data_array[$empId]['first_name'],$emp_sub); 
							$subject = str_replace("{{@last_name}}",$data_array[$empId]['last_name'],$subject); 
							$subject = str_replace("{{@shift_date}}",$shift_dates_array[$i],$subject); 
							$subject = str_replace("{{@shift_time}}",$shift_times_array[$i],$subject); 
						}
						
						//parse the dynamic variables of email body
						if($emp_body != '') 
						{
							$matter  = str_replace("{{@first_name}}",$data_array[$empId]['first_name'],$emp_body); 
							$matter  = str_replace("{{@last_name}}",$data_array[$empId]['last_name'],$matter);
							$matter  = str_replace("{{@shift_date}}",$shift_dates_array[$i],$matter);
							$matter  = str_replace("{{@shift_time}}",$shift_times_array[$i],$matter);
							$insMatter = $matter;
							$matter  = str_replace('\n','<br>',$matter);
						}
						
						// Message content for SMS - Max 160 chars in SMS	
                        $mode_template_type = ($emp_template_type == "ClockIn") ? "Clocked In" : "Clocked Out";					
						$sms_matter = "This is to remind you have not ".$mode_template_type." yet for ".$shift_dates_array[$i]." ".$shift_times_array[$i].". Please ".$mode_template_type." as soon as possible.";
						
						$matter = "<div style='font-family: arial; font-size: 10pt;'>".stripslashes($matter)."<br><br></div>";

						$matter=str_replace("../../logo.php","/BSOS/logo.php",str_replace("href=\"/#id","href=\"#id",str_replace("href=\"/#top\"","href=\"#top\"",stripslashes($matter))));
						
						$toEmail 	= $data_array[$empId]['email'];
						$to_array	= array($toEmail);
						
						$mailheaders= array("Date: $curtime_header","From: $from","To: $toEmail","Subject: $subject","MIME-Version: 1.0");
						$mailtype		= "text/html";
						$email_matter	= prepareBody($matter,$mailheaders,$mailtype);
						
						if($lcntVal < 5) 
						{
							if(in_array("e", $notify_mode)) //if email enabled in template
							{
								$smtpRes = $placementmailSetup->getSetUpNSendMail($from,$to_array,$mailheaders,$email_matter,$setupSno);
								
								if($smtpRes == 1)
								{
									//$this->savePlacementNotificationMailsInActivities($emp_email_array['sno'], $emp_email_array['email'], $subject, $matter, $from);
									$resP = 'Success';
									//$resP = $from."^^".$toEmail."^^".$setupSno;
								}
								else
								{
									$module = 'cico';
									$errMsg = "SMTP Server Authorization Failed ".$smtp->error;
									$errMsgDesc = $smtp->error_message;

									$type = $placementmailSetup->getCustomType($explodedSetUpData[4]);

									$sqlIns = "INSERT INTO notification_email_logs(server_type, module, cuser, cdate, status, error_msg, toaddr, fromaddr, error_msg_desc) VALUES('".$type."', '".$module."', '".$username."', NOW(), 'Failed', '".mysql_real_escape_string($errMsg)."', '".mysql_real_escape_string($data_array[$empId]['email'])."', '".mysql_real_escape_string($from)."', '".mysql_real_escape_string($errMsgDesc)."')";
									mysql_query($sqlIns,$db);
									
									if($explodedSetUpData[4] == 2) 
									{
										if($lcntVal == 4) 
										{
											$lcntVal = $lcntVal+1;
											$sqlUpd  = "UPDATE notification_email_setup SET lockm = 'ERR',mdate=NOW(), lcount = ".($lcntVal)." WHERE 
													sno = '$setupSno'";
											$errStatus = "Blckd";
										}
										else
										{
											$lcntVal = $lcntVal+1;
											$sqlUpd  = "UPDATE notification_email_setup SET lockm = 'No', lcount = ".($lcntVal)." WHERE 
													sno = '$setupSno'";
										}
										mysql_query($sqlUpd,$db);
										$status = 'Failure';
									}
									elseif($explodedSetUpData[4] == 1) 
									{
										$status 	= 'Failure';
										$errStatus 	= "";
									}
									$resP 	= $status.'^^'.$errStatus;
								}
							}
							else
							{
								$resP = 'DisTemp';
							}
						}
						else
						{
							$resP = 'Blocked';
						}
						
						if(in_array("s", $notify_mode)) //if sms enabled in template
						{
							//Call to procedure for inserting to notifications table of akken_notifications.
							$akken_insert_notification = "call akken_notifications.insertNotification ('".$companyuser."','cico','".$data_array[$empId]['sno']."','','','Active','".mysql_real_escape_string($sms_matter)."',@psno)";
							$res = mysql_query($akken_insert_notification,$db);
							$res = mysql_query("select @psno as psno",$db);
							$fetch_psno_result = mysql_fetch_array($res);
							$psno = $fetch_psno_result[0];

							//Update the flags in the table 
							$alert_type_set_query = "UPDATE akken_notifications.notifications SET alert = '".$popup_flag."', email = '".$email_flag."', sms = '".$sms_flag."' WHERE sno = ".$psno."";
							mysql_query($alert_type_set_query,$db);

							//$temp_notification_placement_ids[$emp_email_array['email']."_".$emp_email_array['assignment_id']] = $psno; // set this to the current Assignment id
							// Get Notification ID based on Assignment ID set earlier
							//$temp_notification_psno = $temp_notification_placement_ids[$emp_email_array['email']."_".$emp_email_array['assignment_id']];
							$temp_notification_psno = $psno;
							
							//if(!empty($temp_notification_psno)) 
							if(!empty($temp_notification_psno))
							{
								//Call to procedure for inserting to notifications_list table of akken_notifications.
								$akken_insert_notificationList = "call akken_notifications.insertNotificationList(".$temp_notification_psno.",'".$data_array[$empId]['username']."','','".$data_array[$empId]['mobile']."','".$data_array[$empId]['smsdomain']."');";
								mysql_query($akken_insert_notificationList,$db);  
							}
						}
					}
					
					//$emp_email_array = "";
					$matter=''; 
					$subject='';
					echo ".".$percent;
			        flush();    
			        ob_flush();
			        sleep(1);
			        $i++;
				}
			}
	
		}
		session_unregister('templatedata');
		
		if($_SESSION['cicoSessionShiftDates']!="") {
			unset($_SESSION['cicoSessionShiftDates']);
		}
		
		if($_SESSION['cicoSessionShiftTimes']!="") {
			unset($_SESSION['cicoSessionShiftTimes']);
		}
		return '^^'.$resP;
	} //main function
}
?>
