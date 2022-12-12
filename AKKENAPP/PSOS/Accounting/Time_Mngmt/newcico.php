<?php	
	require("global.inc");
    require_once('timesheet/class.Cico.php');
    require("Menu.inc");
    
	$menu=new EmpMenu(); 


	$efrom1 = getFromEmailID($username);

    	if(!isset($val) || $val == "")
	{
		//$startEndDates = getStartEndDatesBasedOnWeekendDay();
        $startEndDates = array(
            "StartDate"=>date("m/d/Y"),
            "EndDate"=>date("m/d/Y")
        );
		if(!isset($servicedate) || $servicedate == "")
			$servicedate = $startEndDates['StartDate'];
            $servicedateto = $servicedate;		
	}
	else
	{
		if($servicedate =="")
			$servicedate = date("m/d/Y",$val);
        else
            $servicedateto = $servicedate;
	}

	$sval=explode("/",$servicedate);
	$thisday=mktime(0,0,0,$sval[0],$sval[1],$sval[2]);

	if($valtodate =="")
	{
		if(!isset($servicedateto) || $servicedateto == "")
			$servicedateto = $startEndDates['EndDate'];
	}

	$servicedateto_Ymd = date("Y-m-d",strtotime($servicedateto));
	$servicedate_Ymd = date("Y-m-d",strtotime($servicedate));
	
	$deptAccessObj = new departmentAccess();
	$deptAccessSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");

    if($empnames=="")
    {
        $query1 = "SELECT timesheet FROM hrcon_compen WHERE ustatus != 'backup' and username ='".$username."'";
        $result1 = mysql_query($query1,$db);
        $row1 = mysql_fetch_row($result1);

        $queryin = "SELECT lstatus FROM emp_list WHERE username='".$username."'";
        $resin = mysql_query($queryin,$db);
        $rowin = mysql_fetch_row($resin);
        $sql_LJS = "select pusername,client,ustatus,date(str_to_date(e_date,'%m-%d-%Y')) from hrcon_jobs where username = '".$username."' AND hrcon_jobs.jtype != '' and pusername!='' and ustatus IN( 'active','closed','cancel') AND ts_layout_pref='Clockinout' order by udate";
        $ds_LJS  = mysql_query($sql_LJS, $db);

        $getmaxdate = "SELECT MAX(edate) FROM par_timesheet WHERE username='".$username."'";
        $maxres=mysql_query($getmaxdate,$db);
        $maxdaterow=mysql_fetch_row($maxres);

        $counthrcon = 0;
        while($countrows = mysql_fetch_row($ds_LJS))
        {
            $dateFlag = true;
            if($countrows[2]!='active')
            {
                if($countrows[3]>=$maxdaterow[0] && $countrows[3]!='' && $countrows[3]!='0-0-0')
                    $dateFlag = true;
                else 
                    $dateFlag = false;
            }

            if($dateFlag)
                $counthrcon = $counthrcon+1;
        }
        
        if($row1[0] != 'Y' && $rowin[0] != 'INACTIVE' && $rowin[0] != 'DA' && $counthrcon>0)
        {
            $new_user=$username;
        }
        else
        {
            $query="SELECT emp_list.username, emp_list.name
            FROM emp_list, hrcon_jobs, hrcon_compen, users
            WHERE emp_list.username = hrcon_jobs.username 
            AND emp_list.username = hrcon_compen.username
            AND emp_list.username =  users.username 
            AND emp_list.lstatus !='DA' 
            AND emp_list.lstatus != 'INACTIVE' 
            AND (emp_list.empterminated != 'Y' || (UNIX_TIMESTAMP(DATE_FORMAT(emp_list.tdate,'%Y-%m-%d'))-UNIX_TIMESTAMP())>0)
            AND hrcon_jobs.ustatus IN ('active','closed','cancel') 
            AND hrcon_compen.ustatus != 'backup' 
            AND hrcon_jobs.jtype != '' 
            AND hrcon_jobs.pusername != '' 
            AND hrcon_compen.timesheet != 'Y'
            AND users.usertype = '' 
			AND hrcon_jobs.ts_layout_pref = 'Clockinout'
			AND emp_list.emp_department IN (".$deptAccessSno.")
            GROUP BY emp_list.username
            ORDER BY emp_list.mtime DESC";

            $result=mysql_query($query,$db);
            while($roww = mysql_fetch_array($result))
            {
                $getmaxdate = "SELECT MAX(edate) FROM par_timesheet WHERE username='".$roww[0]."'";
                $maxres=mysql_query($getmaxdate,$db);
                $maxdaterow=mysql_fetch_row($maxres);

                $dynamicUstatus = " AND ((hrcon_jobs.ustatus IN ('active','closed') AND (hrcon_jobs.s_date IS NULL OR hrcon_jobs.s_date='' OR hrcon_jobs.s_date='0-0-0' OR (DATE(STR_TO_DATE(s_date,'%m-%d-%Y')) <= '".$servicedateto_Ymd."'))) AND (IF(hrcon_jobs.ustatus='closed',(hrcon_jobs.e_date IS NOT NULL AND hrcon_jobs.e_date<>'' AND hrcon_jobs.e_date<>'0-0-0' AND DATE(STR_TO_DATE(e_date,'%m-%d-%Y'))>='".$servicedate_Ymd."'),1)) AND (IF(hrcon_jobs.ustatus='cancel',(hrcon_jobs.e_date IS NOT NULL AND hrcon_jobs.e_date<>'' AND hrcon_jobs.e_date<>'0-0-0' AND DATE(STR_TO_DATE(e_date,'%m-%d-%Y'))>='".$servicedate_Ymd."'),1)))";
                $getActiveAssignments = "select count(1) from hrcon_jobs where username = '".$roww[0]."' and pusername!=''".$dynamicUstatus." AND hrcon_jobs.jtype!='' order by udate";
                $activeRes=mysql_query($getActiveAssignments,$db);
                $activeCount=mysql_fetch_row($activeRes);

                $dateFlag = false;
                if($activeCount[0] > 0)
                    $dateFlag = true;

                if($dateFlag)
                {
                    if(!isset($new_user))
                    {
                        $new_user=$roww[0];
                        break;
                    }
                }
            }
        }	
    }
    else
    {
        $new_user=$empnames;
    }
	
	


    //query for getting employee list for to fill time sheet 	
    $query="SELECT emp_list.username uid, emp_list.name name, ".getEntityDispName("emp_list.sno","CONCAT_WS(' ',hrcon_general.lname,hrcon_general.fname,hrcon_general.mname)")."
    FROM emp_list, hrcon_jobs, hrcon_compen, hrcon_general, users
    WHERE emp_list.username = hrcon_jobs.username
    AND emp_list.username = hrcon_compen.username
    AND emp_list.username = hrcon_general.username
    AND emp_list.username =  users.username 
    AND emp_list.lstatus != 'DA'
    AND emp_list.lstatus != 'INACTIVE'
    AND (emp_list.empterminated != 'Y' || (UNIX_TIMESTAMP(DATE_FORMAT(emp_list.tdate,'%Y-%m-%d'))-UNIX_TIMESTAMP())>0)
    AND hrcon_jobs.ustatus in('active','closed','cancel')
    AND hrcon_jobs.ustatus != ''
    AND hrcon_compen.ustatus != 'backup'
    AND hrcon_jobs.jtype != ''
    AND hrcon_jobs.pusername != ''
    AND hrcon_compen.timesheet != 'Y'
    AND hrcon_general.ustatus != 'backup'
    AND users.usertype = '' 
	AND hrcon_jobs.ts_layout_pref = 'Clockinout'
	AND emp_list.emp_department IN (".$deptAccessSno.")
    GROUP BY emp_list.username, emp_list.name
    ORDER BY emp_list.mtime DESC";

    $result=mysql_query($query,$db);
    $enames="";
    $new_first_user_selected = "";
    $new_first_user = "";
    $empCount = 0;

    while($myrow=mysql_fetch_row($result))
    {
        $getmaxdate = "SELECT MAX(edate) FROM par_timesheet WHERE username='".$myrow[0]."'";
        $maxres=mysql_query($getmaxdate,$db);
        $maxdaterow=mysql_fetch_row($maxres);
        
        $dynamicUstatus = " AND ((hrcon_jobs.ustatus IN ('active','closed','cancel') AND (hrcon_jobs.s_date IS NULL OR hrcon_jobs.s_date='' OR hrcon_jobs.s_date='0-0-0' OR (DATE(STR_TO_DATE(s_date,'%m-%d-%Y')) <= '".$servicedateto_Ymd."'))) AND (IF(hrcon_jobs.ustatus='closed',(hrcon_jobs.e_date IS NOT NULL AND hrcon_jobs.e_date<>'' AND hrcon_jobs.e_date<>'0-0-0' AND DATE(STR_TO_DATE(e_date,'%m-%d-%Y'))>='".$servicedate_Ymd."'),1)) AND (IF(hrcon_jobs.ustatus='cancel',(hrcon_jobs.e_date IS NOT NULL AND hrcon_jobs.e_date<>'' AND hrcon_jobs.e_date<>'0-0-0' AND DATE(STR_TO_DATE(e_date,'%m-%d-%Y'))>='".$servicedate_Ymd."'),1)))";
        
        $getActiveAssignments = "select count(1) from hrcon_jobs where username = '".$myrow[0]."' and pusername!=''".$dynamicUstatus." AND hrcon_jobs.jtype!='' order by udate";
		
        $activeRes=mysql_query($getActiveAssignments,$db);
        $activeCount=mysql_fetch_row($activeRes);
        
        $dateFlag = false;
        if($activeCount[0] > 0)
        {
            $dateFlag = true;
        }
        if($dateFlag)
        {
            if($empCount == 0)
            {
                $new_first_user = $myrow[0];
            }
            $empCount++;
            if($new_user == $myrow[0])
                $new_first_user_selected = $new_user;
            
            if($enames=="")
                $enames="<option value='".$myrow[0]."' ".sel($new_user,$myrow[0])." title='".html_tls_specialchars($myrow[2],ENT_QUOTES)."'>".html_tls_specialchars($myrow[2],ENT_QUOTES)."</option>";
            else
                $enames.="<option value='".$myrow[0]."' ".sel($new_user,$myrow[0])." title='".html_tls_specialchars($myrow[2],ENT_QUOTES)."'>".html_tls_specialchars($myrow[2],ENT_QUOTES)."</option>";
        }
    }

    function sel($a,$b)
	{
		if($a==$b)
			return "selected";
		else
			return "";
	}

    function count_days( $a, $b )
	{
		// First we need to break these dates into their constituent parts:
		$gd_a = getdate( $a );
		$gd_b = getdate( $b );

		// Now recreate these timestamps, based upon noon on each day
		// The specific time doesn't matter but it must be the same each day
		$a_new = mktime( 12, 0, 0, $gd_a['mon'], $gd_a['mday'], $gd_a['year'] );
		$b_new = mktime( 12, 0, 0, $gd_b['mon'], $gd_b['mday'], $gd_b['year'] );

		// Subtract these two numbers and divide by the number of seconds in a
		//  day. Round the result since crossing over a daylight savings time
		//  barrier will cause this time to be off by an hour or two.
		return round( abs( $a_new - $b_new ) / 86400 );
	}

    $timesheetObj = new AkkenTimesheets($db);	
	if ($tmode == "Saved") {
		$data = $timesheetObj->getTimesheetDetails($sno, 'Saved',$condinvoice,$conjoin,'','Clockinout');
	}else{
		$data = $timesheetObj->getTimesheetDetails($sno, 'pending',$condinvoice,$conjoin,'','Clockinout');
	}
    
    $toval=explode("/",$servicedateto);
	$thisdayto=mktime(0,0,0,$toval[0],$toval[1],$toval[2]);
	$countDays = count_days( $thisdayto, $thisday )+2; // 2 is for diff+1+range date
	$valtodate=$thisdayto;
	$val=$thisday;
	$rangeDate = date("Y-m-d",$thisday)."-range-".date("Y-m-d",$thisdayto);
	$Servicedatedisplay=date("Y-m-d",getTimeStampByNDays("-1",$thisday));

    $sql_LJS = "SELECT pusername,client,jotype,ustatus,date(str_to_date(e_date,'%m-%d-%Y')),classid FROM hrcon_jobs WHERE username = '".$new_user."' AND hrcon_jobs.jtype != ''  AND hrcon_jobs.pusername != '' AND (hrcon_jobs.ustatus = 'active' OR  (hrcon_jobs.ustatus IN ('closed') AND (hrcon_jobs.e_date is not null and hrcon_jobs.e_date<>'' and hrcon_jobs.e_date<>'0-0-0' AND date(str_to_date(e_date,'%m-%d-%Y'))>'".$Servicedatedisplay."')) OR (hrcon_jobs.ustatus IN ('cancel') AND (hrcon_jobs.e_date is not null and hrcon_jobs.e_date<>'' and hrcon_jobs.e_date<>'0-0-0' AND date(str_to_date(e_date,'%m-%d-%Y'))>'".$Servicedatedisplay."'))) ".$showEmplyoees." ORDER BY udate DESC ";
	
	$ds_LJS  = mysql_query($sql_LJS, $db);		
	while($rs_LJS  = mysql_fetch_row($ds_LJS))
	{
		$getActiveAssignments = "select count(1) from hrcon_jobs where username = '".$new_user."' and pusername!='' AND (hrcon_jobs.ustatus = 'active' OR  (hrcon_jobs.ustatus IN ('closed') AND (hrcon_jobs.e_date is not null and hrcon_jobs.e_date<>'' and hrcon_jobs.e_date<>'0-0-0' AND date(str_to_date(e_date,'%m-%d-%Y'))>='".$Servicedatedisplay."')) OR  (hrcon_jobs.ustatus IN ('cancel') AND (hrcon_jobs.e_date is not null and hrcon_jobs.e_date<>'' and hrcon_jobs.e_date<>'0-0-0' AND date(str_to_date(e_date,'%m-%d-%Y'))>='".$Servicedatedisplay."'))) AND hrcon_jobs.jtype!=''";
		$activeRes=mysql_query($getActiveAssignments,$db);
		$activeCount=mysql_fetch_row($activeRes);
		$dateFlag = false;

		if($activeCount[0] > 0)
			$dateFlag = true;
		else
			$rowcou="";

		if($dateFlag)
		{
			if($rowcou=="")
			{
				$timedata="";
				$rowcou=8;

				for($i=0;$i<$rowcou;$i++)
				{
					$zfque = "SELECT COUNT(1) FROM hrcon_jobs WHERE ".$condCk_comp." username = '".$new_user."' and pusername!='' AND (hrcon_jobs.ustatus = 'active' OR  (hrcon_jobs.ustatus IN ('closed') AND (hrcon_jobs.e_date is not null and hrcon_jobs.e_date<>'' and hrcon_jobs.e_date<>'0-0-0' AND date(str_to_date(e_date,'%m-%d-%Y'))>='".date("Y-m-d",$thisday)."')) or (hrcon_jobs.ustatus IN ('cancel') AND (hrcon_jobs.e_date is not null and hrcon_jobs.e_date<>'' and hrcon_jobs.e_date<>'0-0-0' AND date(str_to_date(e_date,'%m-%d-%Y'))>='".date("Y-m-d",$thisday)."'))) ".$showEmplyoees." AND hrcon_jobs.jtype!='' GROUP BY username";
					$zfres=mysql_query($zfque,$db);
					$zfrowCount = mysql_fetch_row($zfres); //for billable checkbox
					
					$bque="SELECT eartype FROM hrcon_benifit WHERE username='".$new_user."' AND ustatus='active'";
					$bres=mysql_query($bque,$db);
					$bearCount = mysql_num_rows($bres);

					if($zfrowCount[0] == 0 && $bearCount == 0)
						break;

					if($thisday > $thisdayto)
						break;

					if($i==0)
					{
						$elements[$i][0]=$rangeDate;
					}
					else
					{
						$elements[$i][0]=date("Y-m-d",$thisday);
						$thisday = getTimeStampByNDays("+1",$thisday);
					}
					
					$elements[$i][1]=$rs_LJS[0];
					$elements[$i][6]=$rs_LJS[1];
					$elements[$i][7]=getManage($rs_LJS[2]);
					$elements[$i][8]=$rs_LJS[5];
				}
				$rowcou = count($elements);
				$thisday=$val;
			}
			else
			{
				if(strlen($timedata)>0)
				{
					$sintime=explode("^",$timedata);
					$sintimeCount = count($sintime);
					for($i=0;$i<$sintimeCount;$i++)
						$elements[$i]=explode("|",$sintime[$i]);
				}
				else
				{
					if($countDays > 8)
						$rowcou=8;
					else
						$rowcou=$countDays; 
				}

				$newCount = count($elements);

				for($i=$newCount;$i<$rowcou;$i++)
				{
					if(!$timedata)  
					{ 				
						if($i==0)
						{
							$elements[$i][0]=$rangeDate;
						}
						else
						{
							$elements[$i][0]=date("Y-m-d",$thisday);
							$thisday = getTimeStampByNDays("+1",$thisday);
						}
						$elements[$i][1]=$rs_LJS[0];
					}
					else
					{
						$thisday = getTimeStampByNDays("+1",$thisday);
					}

					$elements[$i][6]=$rs_LJS[1];
					$elements[$i][7]=getManage($rs_LJS[2]);
				}
				$thisday=$val;

				if($elements[$rowcou-1][1] == "")
				{
					$elements[$rowcou-1][1]= $rs_LJS[0];
					$elements[$rowcou-1][8]=$rs_LJS[5];//classid
				}
			}
		}
	} 
    
	//echo '<pre>'; print_r($data);
	$rules = $timesheetObj->getCicoRules();
	include("header.inc.php");
	
	    $previous_date = date("Y-m-d",strtotime("-6 days"));
	    $canSave = "Y";
	    if(date("Y-m-d",strtotime($servicedate)) < $previous_date)
	    {
	        $canSave = "N";
	    }
?>

<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<!-- <link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css"> -->
<link type="text/css" rel="stylesheet" href="/BSOS/css/tab.css">
<!-- <link type="text/css" rel="stylesheet" href="/BSOS/css/new.css"> -->
<link type="text/css" rel="stylesheet" href="/BSOS/css/timesheet.css">
<style type="text/css">
.afontstyle {
    font-size: 13px !important;
}

.hthbgcolorr {
    background-color: #fff !important;
}

.custTime {
    line-height: 24px;
    color: #3eb8f0;
    font-size: 18px;
    font-weight: bold;
}

.custTime .hfontstyle {
    color: #3eb8f0 !important;
    font-family: arial;
    font-weight: bold;
    font-size: 13px !important;
}

.maingridbuttonspad {
    padding: 0px;
}

.NewGridTopBg .htblbgcolor {
    border-bottom: 0px;
}

.totbg {
    background-color: #f6f7f9;
    font-family: Arial;
    font-size: 14px;
    font-style: normal;
    line-height: 24px;
    border-top: solid 1px #ccc;
}

.timeflex {
    display: flex;
    align-items: center;
    white-space: nowrap;
    gap: 1rem;
}

button.Zebra_DatePicker_Icon_Inside {
    left: 227px !important;
    top: 10px !important;
    position: unset !important;
    transform:none !important;
}

.Zebra_DatePicker_Icon_Wrapper {
    display: inline-block !important;

}

.TimeSheetContM {
  margin-top: 90px !important;
}
table.CustomTimesheetNew th:nth-child(4){ text-align: left !important;}
table.CustomTimesheetNew th:nth-child(5){ text-align: left !important;}
.mloadDiv #autopreloader { position: fixed; left: 0; top: 0; z-index: 99999; width: 100%; height: 100%; overflow: visible; 
opacity:0.35;background:#000000 !important;}
.mloadDiv .newLoader{position:absolute; top:50%; left:50%;z-index:99999;margin-left:-67px;margin-top:-67px;width:150px;height:140px;}
.mloadDiv .newLoader img{border-radius:69px !important;}
</style>
<script src="/BSOS/scripts/common.js" language=javascript></script>
<script language="javascript" src=scripts/validatetimefax.js></script>
<link type="text/css" rel="stylesheet" href="/BSOS/css/gigboard/select2_V_4.0.3.css">
<link rel="stylesheet" href="/BSOS/popupmessages/css/popup_message.css" media="screen" type="text/css">
<link rel="stylesheet" type="text/css" media="screen" href="/BSOS/css/customSelectNew.css" />
<link type="text/css" rel="stylesheet" href="/BSOS/css/shift_schedule/zebra_default.css">
<link rel="stylesheet" type="text/css" href="/BSOS/css/calendar.css">
<!-- <link type="text/css" rel="stylesheet" href="/BSOS/css/timeSheetselect2.css"> -->
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popupMsgArray.js"></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popup-message.js"></script>
<!-- Validation CICO  -->
<script src="/BSOS/scripts/jquery-1.8.3.js"></script>
<script type="text/javascript" src="/BSOS/scripts/jquery.inputmask.js"></script>
<script type="text/javascript" src="/BSOS/Accounting/Time_Mngmt/scripts/validatecico.js"></script>
<script type="text/javascript" src="/BSOS/scripts/shift_schedule/zebra_datepicker.js"></script>
<script type="text/javascript" src="/BSOS/scripts/calendar.js"></script>
<script type="text/javascript" src="/BSOS/scripts/customSelectNew.js"></script>
<script src="/BSOS/scripts/date_format.js" language="javascript"></script>
<script type="text/javascript" src="/BSOS/scripts/select2_V4.0.3.js"></script>
<script type="text/javascript">
function doCancelPop() {
    self.close();
}

/* Added autopreloader function to show loading icon, until the page completely loads */
$(window).on('load',function(){		
	$('#autopreloader').delay(1000).fadeOut('slow',function(){$(this).remove();});
	$('.newLoader').delay(1000).fadeOut('slow',function(){$(this).remove();});
	//$.getScript("/BSOS/scripts/customSelectNew.js");
});
</script>
<title>Clock In & Out</title>

<body>
    <div id="body1">
        <div class="mloadDiv"><div id="autopreloader"></div>
        <div class="newLoader"><img src="/BSOS/images/akkenloading_big.gif"></div>
        <form name=sheet action=navtime.php method=post>
            <input type=hidden name=addr value="<?php echo $data[0]['username'];?>">
            <input type=hidden name=addr1 value="<?php echo $sno;?>">
            <input type=hidden name=t1 value="<?php echo $servicedate;?>">
            <input type=hidden name=t2 value="<?php echo $servicedateto;?>">
            <input type=hidden name=sdatets value="<?php echo $data[0]['sdate'];?>">
            <input type=hidden name=stscount value="<?php echo count($data); ?>">
            <input type=hidden name=cu value="<?php echo $data[0]['name'];?>">
            <input type=hidden name=statval value="statsubmitted">
            <input type=hidden name=chkedrows value="">
            <input type=hidden name=chkcount value="<?php echo count($data); ?>">
            <input type=hidden name="ts_multiple" value="<?php echo $ts_multiple;?>">
            <input type=hidden name=module value="<?php echo "Accounting"; ?>">
            <input type=hidden name="details" id="details">
            <input type=hidden name='aa' id='aa' value="" />
            <input type=hidden name='ts_sno' id='ts_sno' value="<?php echo $_GET['ts_sno'];?>" />
            <input type=hidden name="deleted_rowids" id="deleted_rowids" value="">
            <input type=hidden name="deleted_snos" id="deleted_snos" value="">
            <!--Timesheet grid load optimization -->
            <input type=hidden name='openerType' id='openerType' value="<?php echo $openerType;?>" />
            <!--Timesheet rules details -->
            <input type=hidden name='maxregularhours' id='maxregularhours'
                value="<?php echo $rules['maxregularhours'];?>" />
            <input type=hidden name='maxovertimehours' id='maxovertimehours'
                value="<?php echo $rules['maxovertimehours'];?>" />
            <input type=hidden name='cico_pipo_rule' id='cico_pipo_rule'
                value="<?php echo $rules['cico_pipo_rule'];?>" />
            <input type=hidden name='maxregularhours_perweek' id='maxregularhours_perweek'
                value="<?php echo $rules['maxregularhours_perweek'];?>" />
            <input type=hidden name='maxovertimehours_perweek' id='maxovertimehours_perweek'
                value="<?php echo $rules['maxovertimehours_perweek'];?>" />
            <input type=hidden name='roundingpref' id='roundingpref' value="<?php echo $rules['roundingpref'];?>" />
            <input type=hidden name='timeincrements' id='timeincrements'
                value="<?php echo $rules['timeincrements'];?>" />
            <input type=hidden name='rule_type' id='rule_type' value="<?php echo $rules['rule_type'];?>" />
            <input type=hidden name='canSave' id='canSave' value="<?php echo $canSave;?>" />
            <div id="main">
                <table class="ProfileNewUI CustomTimesheetNew" align="center">
                    <tr>
                        <td style="position:relative">
                            <div class="CustTimeDateRangeT">
                                <table class="ProfileNewUI SummaryTopBg">
                                    <tr>
                                        <td valign="top">
                                            <font class="modcaption TMEPadL-0 CustTimeSheetHed">Create&nbsp;Timesheet</font>
                                            <table class="table table-borderless mb-0 table-mp-0">
                                                <tbody>
                                                    <?php
                                                    if($module != 'MyProfile')
                                                    {
                                                    ?>
                                                    <tr>
                                                        <td colspan="2">
                                                            <div class="row">
                                                                <label class="col-auto col-form-label">Select an Employee to fill the Timesheet:</label>
                                                                <div class="col-auto">
                                                                    <select id="empnames" name="empnames" onChange="javascript:getEmp()" class=drpdwnacc
                                                                    tabindex="3"><?php echo $enames; ?></select>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                    }
                                                    else
                                                        echo "<input type=hidden id='empnames' name=empnames>";
                                                    ?>
                                                </tbody>
                                            </table>
                                        </td>
                                        <td>
                                            <div style="float: right">
                                                <div class=FontSize-16>Create a Time Sheet From&nbsp;</div>
                                                <div class="TMEDateBg">
                                                    <input type=text id="servicedate" size=10 maxlength="10" name=servicedate value="<?php echo $servicedate;?>" />
                                                    <script language="JavaScript">new tcal ({'formname':window.form,'controlname':'servicedate'});</script>
                                                    <span class="FontSize-16 TMEPadLR-6">To</span>
                                                    <input type=text id="servicedateto" size=10 maxlength="10" name=servicedateto value="<?php echo $servicedateto;?>"/>
                                                    <script language="JavaScript">new tcal ({'formname':window.form,'controlname':'servicedateto'});</script>
                                                </div>
                                                <div class="TMEDateViewBtn">
                                                    <a href=javascript:DateCheck('servicedate','servicedateto')> <i class="fa fa-eye fa-lg"></i>view</a>
                                                </div>    
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <div id="topheader">
                            <table width='100%' id="MainTable" style="width:100%;" class="ProfileNewUI CustomTimesheetTh CustomTimesheetInput TimeSheetContM" width="100%" cellspacing="0" cellpadding="2" border="0" align="center">
                                <tr class="NewGridTopBg">
                                    <?php
                    
                                    $name=explode("|","fa fa-floppy-o~Save|fa fa-envelope~Submit|fa fa-ban~Cancel|fa-plus-circle~Add");
                                    $link=explode("|","javascript:doSaveNewCiCoTime()|javascript:doSubmitNewCiCoTime()|javascript:doCancelPop()| javascript:addNewRow()");
                                            
                                    $heading="";
                                    $menu->showHeadingStrip1($name,$link,$heading,"left");
                                    ?>
                                </tr>
                            </table>
                        </div>
                    </tr>
                    <?php 
                                    
                        if($elements[$r][0] == "") //while adding new row  date is empty for  new row..
                        $elements[$r][0] = date("Y-m-d",$thisday);


                        if((preg_match("/range/i", $elements[$r][0])))
                        {
                            $dateArr = explode("-range-",$elements[$r][0]);
                            $assignStartDate =$dateArr[0];
                            $assignEndDate =$dateArr[1]; 
                        }
                        else
                        {
                            $assignStartDate =$elements[$r][0];
                            $assignEndDate =$elements[$r][0];
                        }
                        $dateSelOptions = "";
            
                        while($thisday<=$thisdayto)
                        {
                            $dateSelOptions.= "<option ".sel(date("Y-m-d",$thisday),$elements[$r][0])." value='".date("Y-m-d",$thisday)."'>".date("m/d/Y l",$thisday)."</option>";
            
                            if($thisday == $thisdayto)
                                $dateSelOptions.= "<option ".sel($rangeDate,$elements[$r][0])." value='".$rangeDate."'>".date("m/d/Y",$dateStart)." - ".date("m/d/Y",$thisdayto)."</option>";
                                
                            $thisday = getTimeStampByNDays("+1",$thisday);
                        }
                        $thisday=$val;
                        
                        $assignOptions = "";
            
                        if((preg_match("/range/i", $elements[$r][0])))
                            $dynamicUstatus = " AND ((hrcon_jobs.ustatus IN ('active','closed','cancel') AND (hrcon_jobs.s_date IS NULL OR hrcon_jobs.s_date='' OR hrcon_jobs.s_date='0-0-0' OR (DATE(STR_TO_DATE(s_date,'%m-%d-%Y'))<='".$assignEndDate."'))) AND (IF(hrcon_jobs.ustatus='closed',(hrcon_jobs.e_date IS NOT NULL AND hrcon_jobs.e_date<>'' AND hrcon_jobs.e_date<>'0-0-0' AND DATE(STR_TO_DATE(e_date,'%m-%d-%Y'))>='".$assignStartDate."'),1)) AND (IF(hrcon_jobs.ustatus='cancel',(hrcon_jobs.e_date IS NOT NULL AND hrcon_jobs.e_date<>'' AND hrcon_jobs.e_date<>'0-0-0' AND DATE(STR_TO_DATE(e_date,'%m-%d-%Y'))>='".$assignStartDate."'),1)))";
                        else
                            $dynamicUstatus = " AND ((hrcon_jobs.ustatus IN ('active','closed','cancel') AND (hrcon_jobs.s_date IS NULL OR hrcon_jobs.s_date='' OR hrcon_jobs.s_date='0-0-0' OR (DATE(STR_TO_DATE(s_date,'%m-%d-%Y'))<='".$assignStartDate."'))) AND (IF(hrcon_jobs.ustatus='closed',(hrcon_jobs.e_date IS NOT NULL AND hrcon_jobs.e_date<>'' AND hrcon_jobs.e_date<>'0-0-0' AND DATE(STR_TO_DATE(e_date,'%m-%d-%Y'))>='".$assignStartDate."'),1)) AND (IF(hrcon_jobs.ustatus='cancel',(hrcon_jobs.e_date IS NOT NULL AND hrcon_jobs.e_date<>'' AND hrcon_jobs.e_date<>'0-0-0' AND DATE(STR_TO_DATE(e_date,'%m-%d-%Y'))>='".$assignStartDate."'),1)))";
            
            
                        $zque = "SELECT sno, client, project, jtype, pusername,jotype, date_format(str_to_date(s_date,'%m-%d-%Y'),'%m/%d/%Y'), date_format(str_to_date(e_date,'%m-%d-%Y'),'%m/%d/%Y') FROM hrcon_jobs WHERE ".$condCk_comp." username = '".$new_user."' AND pusername!=''".$dynamicUstatus." ".$showEmplyoees." AND hrcon_jobs.jtype!='' ORDER BY udate DESC";
                        $zres=mysql_query($zque,$db);
                        $zrowCount = mysql_num_rows($zres); //for billable checkbox
            
                        $flg = '';
                        $assignment_count = 0;
                        while($zrow=mysql_fetch_row($zres))
                        {
                            $assignment_count = $assignment_count+1;
                            if($zrow[1] != '0')
                            {
                                $que = "SELECT cname, ".getEntityDispName('sno', 'cname', 1)." FROM staffacc_cinfo WHERE type IN ('CUST', 'BOTH') AND sno=".$zrow[1];
                                $res=mysql_query($que,$db);
                                $row=mysql_fetch_row($res);
                                $companyname1=$row[1];
                            }
                            else
                                $companyname1=$companyname;
                            
                            if($zrow[6] == '00/00/2000' || $zrow[6] == NULL || $zrow[6] == '')
                                $asgnStartDate = "No Start Date";		
                            else
                                $asgnStartDate = $zrow[6];
                                                    
                            if($zrow[7] == '00/00/2000' || $zrow[7] == NULL || $zrow[7] == '')
                                $asgnEndDate = "No End Date";
                            else
                                $asgnEndDate = $zrow[7];
                                
                            if($asgnStartDate == "No Start Date" && $asgnEndDate == "No End Date")
                                $startEnddate = "";
                            else
                                $startEnddate = "(".$asgnStartDate." - ".$asgnEndDate.")";
                                
                            if($zrow[3]=="AS")
                            {
                                $flg = sel("AS",$elements[$r][1]);
                                $assignOptions.= "<option ".sel("AS",$elements[$r][1])." id=".$zrow[0]."-".$zrow[1]." value='AS' title='".$companyname1." (Administrative Staff)'>".$companyname1." (Administrative Staff)</option>";
                            }
                            else if($zrow[3]=="OB")
                            {
                                $flg = sel("OB",$elements[$r][1]);
                                $assignOptions.= "<option ".sel("OB",$elements[$r][1])." id=".$zrow[0]."-".$zrow[1]." value='OB' title='".$companyname1." (On Bench)'>".$companyname1." (On Bench)</option>";
                            }
                            else if($zrow[3]=="OV")
                            {
                                $flg = sel("OV",$elements[$r][1]);
                                $assignOptions.= "<option ".sel("OV",$elements[$r][1])." id=".$zrow[0]."-".$zrow[1]." value='OV' title='".$companyname1." (On Vacation)'>".$companyname1." (On Vacation)</option>";
                            }
                            else
                            {
                                $lque="SELECT cname, ".getEntityDispName('sno', 'cname', 1)." FROM staffacc_cinfo WHERE type IN ('CUST', 'BOTH') AND  sno=".$zrow[1];
                                $lres=mysql_query($lque,$db);
                                $lrow=mysql_fetch_row($lres);
                                $clname=$lrow[1];
            
                                if($zrow[4]=="")
                                    $zrow[4]=" N/A ";
            
                                $flg = sel($zrow[4],$elements[$r][1]);
                                if($clname != '' && $zrow[2] != '')
                                    $assignOptions.="<option ".sel($zrow[4],$elements[$r][1])." id='".$zrow[0]."-".$zrow[1]."' data-client='".$zrow[1]."' title='(".$zrow[4].") ".$startEnddate."&nbsp;&nbsp;".html_tls_specialchars($clname,ENT_QUOTES)." - ".html_tls_specialchars($zrow[2],ENT_QUOTES)."' value='".$zrow[4]."'>(".$zrow[4].") ".$startEnddate."&nbsp;&nbsp;".$clname." - ".$zrow[2]."</option>";
                                else if($clname != '' && $zrow[2] == '')
                                    $assignOptions.="<option ".sel($zrow[4],$elements[$r][1])." id='".$zrow[0]."-".$zrow[1]."' data-client='".$zrow[1]."' title='(".$zrow[4].") ".$startEnddate."&nbsp;&nbsp;".html_tls_specialchars($clname,ENT_QUOTES)."' value='".$zrow[4]."'>(".$zrow[4].") ".$startEnddate."&nbsp;&nbsp;".$clname."</option>";
                                else if($clname == '' && $zrow[2] != '')
                                    $assignOptions.="<option ".sel($zrow[4],$elements[$r][1])." id='".$zrow[0]."-".$zrow[1]."' data-client='".$zrow[1]."' title='(".$zrow[4].") ".$startEnddate."&nbsp;&nbsp;".html_tls_specialchars($zrow[2],ENT_QUOTES)."' value='".$zrow[4]."'>(".$zrow[4].") ".$startEnddate."&nbsp;&nbsp;".$zrow[2]."</option>";
                                else if($clname == '' && $zrow[2] == '')
                                    $assignOptions.="<option ".sel($zrow[4],$elements[$r][1])." id='".$zrow[0]."-".$zrow[1]."' data-client='".$zrow[1]."' title='(".$zrow[4].") ".$startEnddate."' value='".$zrow[4]."'>(".$zrow[4].") ".$startEnddate."</option>";		
                            }
                        }
                        
                        //If QB preference is set, disabling week row and not showing week data range in dropdown list.
                        $dispWeekStyle="";

                    ?>
                    <tr>
                        <table id="MainTable" style="width:100%;" class="ProfileNewUI table CustomTimesheetInput"
                            cellspacing="0" cellpadding="2" border="0" align="center">
                            <tbody>
                                <tr class="hthbgcolorr">
                                    <th class="nowrap" valign="top" align="left">
                                        <font class="afontstylee"></font>
                                    </th>
                                    <th class="nowrap" valign="top" align="left">
                                        <font class="afontstylee">Date</font>
                                    </th>
                                    <th class="nowrap" valign="top" align="left">
                                        <font class="afontstylee">Assignments</font>
                                    </th>
                                    <th class="nowrap" valign="top" align="left">
                                        <font class="afontstylee">Start Date Time</font>
                                    </th>
                                    <th class="nowrap" valign="top" align="left">
                                        <font class="afontstylee">End Date Time</font>
                                    </th>
                                    <th class="nowrap" valign="top" align="left">
                                        <font class="afontstylee">Total Hours</font>
                                    </th>
                                </tr><!-- Build Rows -->
                                <tr id="row_0" class="tr_clone">
                                    <td class="DeletePad" width="2%" valign="top">
                                        <input type="hidden" id="cicosno_0" name="cicosno[0]" value="">
                                        <input type="hidden" id="cicodate_0" name="cicodate[0]" value="<?php echo $servicedate;?>">
                                        <input type="hidden" id="cicousername_0" name="cicousername[0]" value="<?php echo $new_user;?>">
                                        <input type="hidden" id="cicostatus_0" name="cicostatus[0]" value="1">
                                        <input type="hidden" id="cicoassid_0" name="cicoassid[0]" value="">
                                        <input type="checkbox" name="daily_check[0][]" id="check_0" value="" class="chremove" style="margin-top:0px;display:none;">
                                        <input type="hidden" id="shift_startdate_0" name="shift_startdate[0]" value="<?php echo $servicedate;?>">
                                        <input type="hidden" id="shift_enddate_0" name="shift_enddate[0]" value="<?php echo date("m/d/Y",strtotime($servicedate."+1 Month"));?>">
                                        <input type="hidden" id="client_id_0" name="client_id[0]" value="">
                                    </td>
                                    <td width="10%" valign="top" align="left">
                                        <div class="select2-container daily_dates akkenDateSelectWid" id="s2id_daily_dates_0"><a href="#" class="select2-choice">
                                        <span class="timesheet_date" id="select2-chosen-1"><?php echo date("m/d/y l",strtotime($servicedate));?></span><abbr class="select2-search-choice-close"></abbr>
                                        <input type="hidden" id="daily_dates_0" name="daily_dates_0" value="<?php echo date("m/d/y l",strtotime($servicedate));?>">
                                    </td>
                                    
                                    <td class="nowra timesheet_details" 
                                        <?php echo ($assignment_count>1)?'style="background-repeat:no-repeat;background-position:left 12px; padding-left: 17px;word-break:break-all;overflow-wrap: break-word" width="32%" valign="top" background="/PSOS/images/arrow-multiple-12-red.png"':''?>>
                                        <div class="select2-container daily_assignemnt akkenAssgnSelect" id="s2id_daily_assignemnt_0">
                                            <select name=client id="clientId" class="drpdwnacc client-id" style="width:350px" onChange=javascript:getAssignmentId(this); >
                                                <?php
                                                    echo $assignOptions;
                                                ?>
                                            </select>
                                        </div>
                                    </td>
                                    <td class="afontstylee" width="10%" valign="top" align="left">
                                        <div class="timeflex"><input
                                                    type="hidden" name="csdate[0]" class="csdate" id="csdate_0"
                                                    value="<?php echo $servicedate;?>"
                                                    style="width: 88px; display: inline-block; position: relative; inset: auto;"
                                                    data-rowid="0" readonly="readonly"><span
                                                class="timesheet_stdate" id="timesheet_stdate_0"><?php echo date("M d Y",strtotime($servicedate))?></span>
                                            <input type="text" id="pre_intime_0" name="pre_intime[0][0]"
                                                value="" size="10" class="rowIntime inouttime"
                                                onchange="javascript:calculateTime(this.id);"
                                                style="font-family:Arial;font-size:9pt;" tabindex="1"
                                                placeholder="HH:MM AM">
                                        </div>
                                    </td>
                                    <td class="afontstylee" width="10%" valign="top" align="left">
                                        <div class="timeflex"><input
                                                    type="hidden" class="cedate" name="cedate[0]" id="cedate_0"
                                                    value="<?php echo $servicedate;?>"
                                                    style="width: 88px; display: inline-block; position: relative; inset: auto;"
                                                    data-rowid="0" readonly="readonly"><span
                                                class="timesheet_etdate" id="timesheet_etdate_0"><?php echo date("M d Y",strtotime($servicedate))?></span>
                                            <input type="text" id="pre_outtime_0" name="pre_outtime[0][0]"
                                                value="" size="10" class="rowOuttime inouttime"
                                                onchange="javascript:calculateTime(this.id);"
                                                style="font-family:Arial;font-size:9pt;" tabindex="2"
                                                placeholder="HH:MM AM">
                                        </div>
                                    </td>
                                    <td class="afontstylee" width="7%" valign="top" align="left">
                                        <input type="hidden" id="total_hours_sec_0" name="total_hours_sec[0][0]"
                                            value="00">
                                        <input type="text" id="total_hours_0" name="total_hours[0][0]" value=""
                                            size="7" class="rowBreaktime"
                                            style="font-family:Arial;font-size:9pt;background-color:#EDE9E9;"
                                            readonly="">
                                    </td>
                                </tr><!-- Build Rows -->
                                <!-- Build Rows -->
                                <!-- Grand Total -->
                                <tr class="CustomGrandTotal bg-light">
                                    <td colspan="3" align="right">
                                        <div id="regular_hours"
                                            style="font-weight: bold;">Regular Hours:
                                           0.00</div>
                                        <input type="hidden" id="hours_rate1" name="hours[rate1]" value="0.00">
                                    </td>

                                    <td align="right">
                                        <div id="over_time_hours"
                                            style="font-weight: bold;">Over Time Hours:
                                            0.00</div>
                                        <input type="hidden" id="hours_rate2" name="hours[rate2]" value="0.00">
                                    </td>

                                    <td align="right">
                                        <div id="double_time_hours"
                                            style="font-weight: bold;">Double Time Hours:
                                            0.00</div>
                                        <input type="hidden" id="hours_rate3" name="hours[rate3]" value="0.00">
                                    </td>
                                </tr>
                                <tr class="CustomGrandTotal bg-light">
                                    <td colspan="3" class="totbg">&nbsp;</td>
                                    <td colspan="2" class="totbg" align="right"><b>Total Hours</b></td>
                                    <td class="totbg" width="10%" align="left">
                                        <div id="final_total_hours" style="font-weight:bold;">0.00</div>
                                        <input type="hidden" id="grand_total_hours" name="grand_total_hours"
                                            value="0.00">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </tr>
                    <tr>
                        <div id="botheader">
                            <table width='100%'>
                                <tr class="NewGridBotBg">
                                    <?php           
                            $name=explode("|","fa fa-floppy-o~Save|fa fa-envelope~Submit|fa fa-ban~Cancel|fa-plus-circle~Add");
                            $link=explode("|","javascript:doSaveNewCiCoTime()|javascript:doSubmitNewCiCoTime()|javascript:doCancelPop()| javascript:addNewRow()");
                            //$heading="time.gif~Submitted&nbsp;Timesheet";
                            $heading = "";
                            $menu->showHeadingStrip1($name,$link,$heading,"left");
                        ?>
                                </tr>
                            </table>
                        </div>
                    </tr>
                </table>
            </div>
        </form>
    </div>
</body>
<script>
$(document).ready(function(){
	$("#empnames").select2();
    $(".client-id").select2();
    $("#cicoassid_0").val($(".client-id").val());
    $("#client_id_0").val($(".client-id :selected").data("client"));

    $("#empnames").select2({
    
        //placeholder: "Select an Employee",
        minimumInputLength: 0,
        closeOnSelect: true,
        dropdownCssClass: 'z-index-5',
        //data:dropdownData(),
        ajax: {
            type: "POST",
            url: "getSelectorData.php",
            dataType: 'json',
            quietMillis: 500,
            delay: 500,
            
            data: function (params) {
                var customersids = $('#empnames').val();
                var queryParameters = {
                    q: params.term,
                    page: params.page,
                    getModule : module,
                    getServicedate :'<?php echo $servicedate;?>',  
                    getServicedateto :'<?php echo $servicedateto;?>',
                    selectedEmployee:customersids,
                    getEmployeeSearchVal: params
                }
                return queryParameters;
            },
            initSelection: function(element, callback) {
                alert(element);
                callback({ id: element.val(), text: element.attr('data-init-text') });
            },
            results: function (data, params) {
                params.page = params.page || 1;
                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 10) < data.count_filtered
                    }
                };
            },
            cache: true
        },
            
        language: {
            noResults: function(){
            return "No Employee Found";
            },
            /*searching: function(){
                return "<span><i class='fa fa-spin fa-spinner'></i>Searching Please Wait</span>"
            }*/
        },
        escapeMarkup: function (m) {
            return m; 
        }
    });
});


function getAssignmentId(selectElement){
    $("[id^=cicoassid_]").each(function(index,hiddenElement){
        $(hiddenElement).val($(selectElement).val());
        var selectElementId = $(selectElement).attr("id");
        $("#client_id_"+index).val($("#"+selectElementId+" :selected").data("client"));
    });
}
</script>