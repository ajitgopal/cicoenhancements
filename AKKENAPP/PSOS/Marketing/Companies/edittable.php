<?php
	if(strpos($_SERVER['HTTP_USER_AGENT'],'Gecko') > -1)
		$rightflt = "style='width:35px;'"; 

	if(TRICOM_REPORTS=='Y'){
	$spl_Attribute = (TRICOM_REPORTS=='Y') ? 'udCheckNull ="YES" ' : '';	
	}else{
	$spl_Attribute = (PAYROLL_PROCESS_BY_MADISON=='MADISON') ? 'udCheckNull ="YES" ' : '';
	} 
?>
<style>
.crmsummary-edit-table td { border:0px !important; }
.summarytext input[type="checkbox"] {margin: 5px 2px !important;}
.select2-container.select2-container-multi.required.selCdfCheckVal{width: 250px !important;}
.setcommrolesize{ border-radius: 4px;font-size: 13px; padding: 6px 4px;width: 250px !important;}
.crmsummary-edit-table textarea{ width:250px;border-radius: 4px;font-size: 13px; padding: 6px 4px; border:solid 1px #ccc}
</style>
<table width=100% cellpadding=3 cellspacing=0 border=0>
    <?php
 	if(strtolower($upst)=="yes"){
		print "<tr><td align=center><font class=afontstyle4>&nbsp;Company has been updated successfully.</font></td></tr>";
		echo "<script>window.focus();</script>";
	}
	?>

    <tr class="NewGridTopBg">
	<?php
		$name=explode("|","fa fa-clone~Update|fa fa-times~Close");
		$link=explode("|","javascript:doListOwner(this)|javascript:doClose()");
		$heading="Companies";
		$menu->showHeadingStrip1($name,$link,$heading);
	?>
	</tr>
</table>
<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<div class="form-container" style="text-align:left;">
	<fieldset>
		<legend><font class="afontstyle">Settings&nbsp;&nbsp;</font></legend>
		<div class="settings-back">
<table width="100%" cellpadding="1" cellspacing="1" border="0">
	<tr>
		<td>
		<span id="leftflt"><span class="crmsummary-content-title">Status</span>
		<select class="summaryform-formelement" name="sstatus" id="sstatus" style="width:130px">
		<option value=0>--select--</option>
		<?php
		$Ctype_Sql="select sno,name from manage where type='compstatus' order by name";
		$Ctype_Res=mysql_query($Ctype_Sql,$db);
		while($Ctype_Data=mysql_fetch_row($Ctype_Res))
		{
			echo "<option value='$Ctype_Data[0]' ".sele($Ctype_Data[0],$s_status).">".html_tls_specialchars($Ctype_Data[1],ENT_QUOTES)."</option>";
		}?>
		</select> <?php if(EDITLIST_ACCESSFLAG){ ?>
		<a href="javascript:doManage('Company Status','sstatus');" class="edit-list">edit list</a> <?php } ?>
		&nbsp;&nbsp;&nbsp;</span>
		<span id="leftflt"><span class="crmsummary-content-title" >Owner</span>
		<select class="summaryform-formelement" name="cowner" id="cowner" style='width:180px'   <?php if($username!=$row[1]) echo "disabled"; ?>>
		<?php
		foreach($Users_Array as $UserNo=>$UName)
		{?>
			<option value="<?=$UserNo?>" <?=sele($c_owner,$UserNo)?>><?=html_tls_specialchars($UName,ENT_QUOTES)?></option>
		<?php } ?>
		</select>
		&nbsp;&nbsp;&nbsp;
		</span>
		<span id="leftflt"><span class="crmsummary-content-title">Share</span>
		<select class="summaryform-formelement" name="cshare" id='cshare' onChange="doShare1(this.value)" <?if($username!=$row[1]) echo "disabled"; ?>>
		<option value='private' <?=sele($comp_Share_Type,'private')?> >Private</option>
		<option value='share'  <?=sele($comp_Share_Type,'share')?>>Share</option>
		<option value='public' <?=sele($comp_Share_Type,'public')?>>Public</option>
		</select>		
		<?php if(($comp_Share_Type=='share') && ($username!=$row[1]))
		{?>
		&nbsp;<font style='font-size:7.5pt'>|</font>
		<span id="view-all-emp">
		<a href="javascript:newWin('/BSOS/Marketing/Lead_Mngmt/viewShareEmp.php?Module=company&addr=<?=$addr?>','shareEmps','450','400')" class="crmsummary-contentlnk">view list</a> </span>
		<?php }else if(($comp_Share_Type=='share') && ($username==$row[1]))
		{?>
		&nbsp;<font style='font-size:7.5pt'>|</font>
		<span id="view-all-emp">
		<a href="javascript:newWin('/BSOS/Marketing/Lead_Mngmt/contactShare.php?Module=company&addr=<?=$addr?>','shareEmps','450','400')" class="crmsummary-contentlnk">view list</a></span> &nbsp;&nbsp;&nbsp;
		<?php }?>
		</td>
	</tr>
</table>
		</div>
	</fieldset>
	
	<table width="100%" cellpadding="1" cellspacing="1" border="0">
			<tr>
				<td align=center>
				<?php
				/**
				* Include the file to generate user defined fields.
				*
				*/
				$mod = 2;
				include($app_inc_path."custom/getcustomfields.php");
				?>
				</td>
			</tr>
		</table>	
	
	<fieldset>
		<legend><font class="afontstyle">Company Information&nbsp;&nbsp;</font></legend>

		<div class="form-back">
		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="crmsummary-edit-table">
			<tr class="summaryrow">
				<td colspan="4" class="summarytext">
				<span id="leftflt"><div class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Company Name']))?$userFieldsAlias['Company Name']:'Company Name';echo getRequiredStar('Company Name', $userFieldsArr);?><?=$mandatory_madison;?></div>
				<input class="summaryform-formelement" type=text name=cname size=54 maxlength=100 value="<?php echo stripslashes($com_name);?>" setName='Company name' <?php echo $spl_Attribute?>>&nbsp;&nbsp;&nbsp;</span>
				<span id="leftflt"><div class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Website']))?$userFieldsAlias['Website']:'Website';echo getRequiredStar('Website', $userFieldsArr);?></div>
				<input class="summaryform-formelement" type=text name=curl size=53 maxsize=100 maxlength=100 value="<?php echo stripslashes($com_url);?>"></span>				</td>
			</tr>
			<tr class="summaryrow">
				<td colspan="4" class="summarytext">
				<span id="leftflt"><div class="crmsummary-content-title"><span class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Address 1']))?$userFieldsAlias['Address 1']:'Address 1';echo getRequiredStar('Address 1', $userFieldsArr);?><?=$mandatory_madison;?></span><span class="summaryform-nonboldsub-title">&nbsp;(main)&nbsp;&nbsp;</span></div>
				<input class="summaryform-formelement" type=text name=address1 size=54 maxlength=100 value="<?php echo stripslashes($com_addr1);?>" setName='Address 1' <?php echo $spl_Attribute?>>&nbsp;&nbsp;&nbsp;</span>
				<span id="leftflt"><div class="crmsummary-content-title">Address 2</div>
				<input class="summaryform-formelement" type=text name=address2 size=53 maxlength=100 value="<?php echo stripslashes($com_addr2);?>"></span>				</td>
			</tr>
			<tr class="summaryrow">
				<td colspan="4" class="summarytext">
				<span id="leftflt"><div class="crmsummary-content-title">City<?php echo (!empty($userFieldsAlias['City']))?getRequiredStar('City', $userFieldsArr):$mandatory_madison;?></div>
				<input class="summaryform-formelement" type=text name=city size=25 maxlength=50 value="<?php echo stripslashes($com_city);?>" setName='city' <?php echo $spl_Attribute?>>&nbsp;&nbsp;&nbsp;</span>
				<span id="leftflt"><div class="crmsummary-content-title">State<?php echo (!empty($userFieldsAlias['State']))?getRequiredStar('State', $userFieldsArr):$mandatory_madison;?></div>
				<input class="summaryform-formelement" type=text name=state size=15 maxlength=20 value="<?php echo stripslashes($com_state);?>" setName='state' <?php echo $spl_Attribute?>>&nbsp;&nbsp;&nbsp;</span>
				<span id="leftflt"><div class="crmsummary-content-title"><?php echo (!empty($userFieldsAlias['Zip']))?$userFieldsAlias['Zip']:'Zip';echo getRequiredStar('Zip', $userFieldsArr);?><?php echo $mandatory_madison;?></div><input class="summaryform-formelement" type=text name=zip size=10 maxlength="<?php echo (PAYROLL_PROCESS_BY_MADISON=='MADISON')?'5':'20'?>" value="<?php echo stripslashes($com_zip);?>" setName='Zip' <?php echo $spl_Attribute?>>&nbsp;&nbsp;&nbsp;</span>
				<span id="leftflt"><div class="crmsummary-content-title">Country</div>
				<select name=country class="summaryform-formelement"  id="country">
				<option selected value=0>--select--</option>
				<?php
               
				 echo getcountryNames($com_country);
				 ?>
				</select></td>
			</tr>
			<tr class="summaryrow">
				<td width="100" class="summarytext"><div class="space_15px">&nbsp;</div>Customer ID#</td>
				<td class="summarytext"><div class="space_15px">&nbsp;</div><font class="crmsummary-contentdata1"><? if($customer_id!='0' && $customer_id!='') echo  $customer_id; else echo "Currently not an accounting customer"; ?></font><input type=hidden name="compcustid" value="<? echo  $customer_id; ?>" />&nbsp;&nbsp;&nbsp;&nbsp;</td>
				<td class="summarytext"><div class="space_15px">&nbsp;</div>Company Revenue</td>
			  <td class="summarytext"><div class="space_15px">&nbsp;</div><input class="summaryform-formelement" type=text name=com_revenue size=32 maxsize=50 maxlength=50 value="<?php echo stripslashes($com_rev);?>">&nbsp;&nbsp;&nbsp;</td>
			</tr>
			<tr class="summaryrow">
				<td width="100" class="summarytext">Main Phone<?php echo (!empty($userFieldsAlias['Main Phone']))?getRequiredStar('Main Phone', $userFieldsArr):$mandatory_madison;?></td>
				<td class="summarytext"><input class="summaryform-formelement" type=text name=phone size=16 maxlength=30 value="<?php echo $phone;?>" setName='phone' <?php echo (PAYROLL_PROCESS_BY_MADISON=='MADISON') ? "udCheckPhNum='YES' ".$spl_Attribute :'';?>><span class="summarytext">&nbsp;ext.&nbsp;&nbsp;</span><input class="summaryform-formelement" size=8 maxlength="<?php echo (PAYROLL_PROCESS_BY_MADISON=='MADISON')?'4':'16'?>" type=text name=phone_extn value="<?=$phone_extn;?>"></td>				
				<td valign="middle" class="summarytext">No. Employees</td>
				<td class="summarytext"><input class="summaryform-formelement" type=text name=nemp size=32 maxlength=50 value="<?php echo $no_emps;?>">&nbsp;&nbsp;&nbsp;</td>
			</tr>
			<tr class="summaryrow">
				<td width="100" class="summarytext">Fax Number</td>
				<td class="summarytext"><input class="summaryform-formelement" type=text name=fax size=32 maxlength=50 value="<?php echo stripslashes($fax);?>">&nbsp;&nbsp;&nbsp;</td>				
				<td class="summarytext">No.Locations</td>
				<td class="summarytext"><input class="summaryform-formelement" type=text name=nloc size=32 maxsize=50 maxlength=50 value="<?php echo $no_com_loc;?>">&nbsp;&nbsp;&nbsp;</td>
			</tr>
			<tr class="summaryrow">
				<td class="summarytext">Industry</td>
				<td class="summarytext"><input class="summaryform-formelement" type=text name=industry size=32 maxsize=50 maxlength=255 value="<?php echo  $com_industry;?>">&nbsp;&nbsp;&nbsp;</td>			
				<td class="summarytext"><?php echo (!empty($userFieldsAlias['Company Source']))?$userFieldsAlias['Company Source']:'Company Source';echo getRequiredStar('Company Source', $userFieldsArr);?></td>
				<td class="summarytext">
    				<select name=compsource class="summaryform-formelement">
    				<option value=0>--select--</option>
    				<?php
					     $CSrc_Sql="select sno,name from manage where type='compsource' order by name";
						 $CSrc_Res=mysql_query($CSrc_Sql,$db);
						 while($CSrc_Data=mysql_fetch_row($CSrc_Res))
						 {
							echo "<option value='$CSrc_Data[0]'".sele($comp_Source,$CSrc_Data[0]).">".html_tls_specialchars($CSrc_Data[1],ENT_QUOTES)."</option>";
						 }?>
    				</select> <?php if(EDITLIST_ACCESSFLAG){ ?>
				<a href="javascript:doManage('Company Source','compsource');" class="edit-list">edit list</a><?php } ?> </td>
			</tr>
			<tr class="summaryrow">
			<td class="summarytext">Year Founded</td>
				<td class="summarytext"><input class="summaryform-formelement" type=text name=nyb size=32 maxsize=50 maxlength=50 value="<?php echo $no_years;?>">&nbsp;&nbsp;&nbsp;</td>                
    				<td class="summarytext">SIC Code</td>
				<td class="summarytext"><input class="summaryform-formelement" type=text name=siccode size=32 maxsize=50 maxlength=50 value="<?php echo $sicCode;?>">&nbsp;&nbsp;&nbsp;</td>
			</tr>
			<tr class="summaryrow">
				<td class="summarytext"><?php echo (!empty($userFieldsAlias['Company Type']))?$userFieldsAlias['Company Type']:'Company Type';echo getRequiredStar('Company Type', $userFieldsArr);?></td>
				<td class="summarytext">
    				<select name=ctype class="summaryform-formelement" id=ctype>
    				<option value=0>--select--</option>
                    <?php
					     $Ctype_Sql="select sno,name from manage where type='comptype' order by name";
						 $Ctype_Res=mysql_query($Ctype_Sql,$db);
						 while($Ctype_Data=mysql_fetch_row($Ctype_Res))
						 {
							echo "<option value='$Ctype_Data[0]' ".sele($Ctype_Data[0],$com_type).">".html_tls_specialchars($Ctype_Data[1],ENT_QUOTES)."</option>";
						 }?>
    				</select> <?php if(EDITLIST_ACCESSFLAG){ ?>
				<a href="javascript:doManage('Company Type','ctype');" class="edit-list">edit list</a><?php } ?> </td>				
				<td class="summarytext">Federal ID</td>
				<td class="summarytext"><input class="summaryform-formelement" type=text name=federalid size=32 maxlength=50 value="<?php echo $fed_id;?>" setName='federalid'>&nbsp;&nbsp;&nbsp;</td>
			</tr>
			<tr class="summaryrow">
			<td class="summarytext">Company Ownership</td>
				<td class="summarytext"><input class="summaryform-formelement" type=text name=cownership size=32 maxsize=50 maxlength=50 value="<?php echo html_tls_entities(stripslashes($c_ownership),ENT_QUOTES);?>" >&nbsp;&nbsp;&nbsp;</td>				
				<td valign="middle" class="summarytext">Company Size</td>
				<td class="summarytext"><input class="summaryform-formelement" type=text name=csize size=32 maxsize=50 maxlength=50 value="<?php echo $com_size;?>">&nbsp;&nbsp;&nbsp;</td>
			</tr>
			<tr class="summaryrow">
				<td class="summarytext">Ticker Symbol</td>
				<td class="summarytext"><input class="summaryform-formelement" type=text name=ticker size=32 maxsize=50 maxlength=50 value="<?php echo stripslashes($tickerSymbol);?>">&nbsp;&nbsp;&nbsp;</td>
				<td valign="middle" class="summarytext">Alternative ID#</td>
				<td class="summarytext"><input class="summaryform-formelement" type=text name=comalternateid size=32 maxsize=255 maxlength=255 value="<?php echo stripslashes($alternate_id);?>">&nbsp;&nbsp;&nbsp;</td>
			</tr>
			<tr class="summaryrow">
				<td class="summarytext">Department<?php if(PAYROLL_PROCESS_BY_MADISON=='MADISON'){ echo $mandatory_madison; }?></td>
				<td class="summarytext"><input class="summaryform-formelement" type=text name="departmentname" id="departmentname" size=32 maxlength=255 value="<?php  echo html_tls_entities(stripslashes($department),ENT_QUOTES); ?>" <?php if(PAYROLL_PROCESS_BY_MADISON=='MADISON'){?> setName='Department' <?php echo $spl_Attribute; }?>>&nbsp;&nbsp;&nbsp;</td>
				<td class="summarytext"><?php echo (!empty($userFieldsAlias['HRM Department']))?$userFieldsAlias['HRM Department']:'HRM Department';echo getRequiredStar('HRM Department', $userFieldsArr);?></td>				
				<td class="summarytext"><?php echo departmentSelBox('HRMDepartments', $deptid, 'summaryform-formelement','','','style="width:205px;"','yes','');?><input class="summaryform-formelement" type=hidden name="addressdesc" id="addressdesc" size=32 maxlength=255 value="<?php  echo html_tls_entities(stripslashes($adress_desc),ENT_QUOTES); ?>" >&nbsp;&nbsp;&nbsp;</td>
			</tr>			
			<tr class="summaryrow">
			  <td colspan="4" class="summarytext">
				<?php
					echo $OBJ_Comp_Role->entityRoleDisplayHTML_Company($compRoles);
				?>
			  </td>
			</tr>
			<tr>
				<td valign="top"><div class="space_15px">&nbsp;</div><div class="summarytext">Company Brief</div><span class="summaryform-nonboldsub-title">(internal notes)</td>
				<td colspan="3"><div class="space_15px">&nbsp;</div><textarea name="cbrief" rows="2" cols="68" ><?php echo html_tls_entities(stripslashes($c_brief),ENT_QUOTES);?></textarea></td>
			</tr>
			<tr>
				<td valign="top"><div class="summarytext">Company Summary</div><span class="summaryform-nonboldsub-title">(for job orders)</span></td>
				<td colspan="3" class="summarytext"><textarea name="csummary" rows="2" cols="68"><?php echo html_tls_entities(stripslashes($c_summary),ENT_QUOTES);?></textarea></td>
			</tr>
			
			<tr>
				<td valign="top"><div class="summarytext">Search Tags</div><span class="summaryform-nonboldsub-title">(search keywords)</span></td>
				<td colspan="3"><textarea name=stags rows="2" cols="68"><?php echo html_tls_entities(stripslashes($s_tags),ENT_QUOTES);?></textarea></td>
			</tr>
			<tr>
			  <td valign="top"><div class="summarytext">Notes</div><span class="summaryform-nonboldsub-title">(displays on summary)</span></td>
			  <td colspan="3"><textarea tabindex=50 name=compnotes cols=68 rows=2><?php echo $com_compnotes;?></textarea></td>
		  </tr>
			<?php $Notes="select notes,cuser,".tzRetQueryStringDTime("notes.cdate","DateTime","/")." from notes where contactid=".$addr."  AND notes.type='com' order by cdate desc";
				$Res_Notes=mysql_query($Notes,$db);
				$Notes_Rows=mysql_num_rows($Res_Notes);
				 if($Notes_Rows!=0)
				 {
			    ?>
			<tr>
			  <td colspan="4" valign="top">
			  <fieldset>
			  <table width="100%" border="0" cellpadding="0" cellspacing="0" class="crmsummary-edit-table">
				  <tr>
					  <td align="left" class="NotesText" valign="top" height=80><div id="compNotes"  name="compNotes" style=" width:689px;height:100%;overflow:auto; padding-left:3px;"><font style='font-size:12px;color: #666666;'>
							 <?php
							  while($Ndata=mysql_fetch_row($Res_Notes)){
									 $NotesText.="<b>".$Ndata[2]."&nbsp;&nbsp;&nbsp;".getUser($Ndata[1])." :</b><br>".html_tls_specialchars(stripslashes($Ndata[0]),ENT_QUOTES)."<br><br>";
								}
							  echo $NotesText;
							  ?>
					</font>
					</div>					</td>
				</tr>
		 </table>
			</fieldset>  
			  
			  </td>
		  </tr>
		  <?php }?>
		  
			<tr>
				<td valign="top" class="summaryform-bold-title">Ultigigs Timesheet Layout</td>
				<td class="multiple_select_div">
					<select name="company_timesheet_layout_preference" id="company_timesheet_layout_preference">
						<option value="" <?php if($timesheet_layout_preference == "") { echo "selected"; } ?>>--- Select Template ---</option>
						<option value="Regular" <?php if($timesheet_layout_preference == "Regular") { echo "selected"; } ?>>Regular</option>
						<option value="TimeInTimeOut" <?php if($timesheet_layout_preference == "TimeInTimeOut") { echo "selected"; } ?>>Time In &amp; Time Out</option>
						<option value="Clockinout" <?php if($timesheet_layout_preference == "Clockinout") { echo "selected"; } ?>>Clock In &amp; Out</option>
					</select>
				</td>
			</tr>
		  
		</table>
		</div>
		<div class="form-back accordion-item">
		<table width="100%" border="0" class="crmsummary-edit-tablemin" id="compHierarchy">
			<tr class="summaryrow">
				<td width="160" class="summarytext">
				                     <a style='text-decoration: none;' onClick="classToggle(mntcmnt1,'DisplayBlock','DisplayNone',1,'compHierarchy')" href="#hideExp1"> <span class="summarytext" id="company_Hierarcyid">Company Hierarchy</span></a> 				
				</td>
			<td>
			<td>
				 <span id="rightflt" <?php echo $rightflt;?>>
					 <div class="form-opcl-btnleftside"><div align="left"></div></div>
					 <div><a onClick="classToggle(mntcmnt1,'DisplayBlock','DisplayNone','1','compHierarchy')" class="form-cl-txtlnk" href="#hideExp1"><b><div id='hideExp1' style="width:auto;">+</div></b></a></div>
					 <div class="form-opcl-btnrightside"><div align="left"></div></div>
				 </span>
			</td>
			</tr>
		</table>
		</div>
		<div class="DisplayNone" id="mntcmnt1">
		<table width="100%" class="crmsummary-jocomp-table">
			  <tr>
					<input type="hidden" name="parent" value="<?=$parentNo?>"/>
				 <td valign="top" width="160" class="summaryform-bold-title">Parent</td>
				<td colspan="2">
				<span id='par_current'>
				
				<span class="summaryform-formelement"><?php if($parentNo == 0 || $parentNo==''){?>Current Company is the parent company<?php } else { echo $parent_addr; }?></span>
				
				</span>
				<br />
			  <a class="crm-select-link" href="javascript:parent_popup()"><strong>change</strong> parent</a>
			 &nbsp;<span class="summaryform-font"><a href="javascript:parent_popup()"><i class="fa fa-search"></i></a></span><span class="summaryform-formelement">&nbsp;|&nbsp;</span><a class="crm-select-link" href="javascript:parent_new()"><strong>create new</strong> parent</a> <span class="summaryform-formelement">&nbsp;+&nbsp;</span> <span id='newchk'></span>
			 <?php if($parentNo != 0){?>
			 <span class="summaryform-formelement" id='rem'>&nbsp;|&nbsp;</span>
			 <a class="crm-select-link" href="javascript:reset_editpar()" id='rem_par'><strong>reset</strong> parent</a>
			 <?php }else{?><span id='rem'></span><span id='rem_par'></span>
			<?php }?>	
			 
			</td>
			  </tr>
			  <tr>
				 <td valign="top" class="summaryform-bold-title">Divisions</td>
				  <td valign="top"><a class="crm-select-link" href="javascript:openWin('/BSOS/Marketing/Companies/addDivisions.php?compsno=<?=$addr?>&CmngFrom=Edit&module=<?=$module?>','addDiv')"><strong>add</strong> division</a>&nbsp;<span class="summaryform-font"><a href="javascript:openWin('/BSOS/Marketing/Companies/addDivisions.php?compsno=<?=$addr?>&CmngFrom=Edit&module=<?=$module?>','addDiv')"><i class="fa fa-search"></i></a>
				 <span class="summaryform-formelement">&nbsp;|&nbsp;</span><a class="crm-select-link" href="javascript:division_edit('<?=$addr?>')"><strong>create new</strong> company as division</a><span class="summaryform-formelement">&nbsp;+&nbsp;</span> <span class="summaryform-formelement">
				 
			<table border=0  cellpadding="0" cellspacing="0"  width="100%">
				<tr>
				 <td nowrap="nowrap">
					<?php
						$rootNode='';
						getSupParent($addr);
						$Chld_Sql="select sno  from staffoppr_cinfo where parent ='".$addr."'";
						$Child_Res=mysql_query($Chld_Sql,$db);
						$Child_Rows=mysql_num_rows($Child_Res);
						?>
					<div id='expdivisions' style='width:500px;height:120px;overflow:auto;display:<?php echo ($Child_Rows>0 ||  ($rootNode!='' && $rootNode!=$addr))?'block':'none'?>'>
					<?php
					if($Child_Rows>0 || $rootNode!='')
					{


						 $SuperPar_Sql="select sno,address1,address2,city,state,country,zip,IF(accessto='ALL','Public',IF(accessto='$username','Private',IF(FIND_IN_SET('$username',accessto)>0,'Share','None'))) access,cname from staffoppr_cinfo where  sno=".$rootNode;

						$SuperPar_Res=mysql_query($SuperPar_Sql,$db);
						$SuperPar_Data=mysql_fetch_row($SuperPar_Res);
						$SuperPar_Rows=mysql_num_rows($SuperPar_Res);
						$Super_Cols=mysql_num_fields($SuperPar_Res);
						  //that particular parent was  there in the data base
						  if($SuperPar_Rows>0)
						  {
							$parAddr='';
							$collen=2;


							$parAddr=getCompDivisionAddress($SuperPar_Data[8],$SuperPar_Data[1],$SuperPar_Data[2],$SuperPar_Data[3],$SuperPar_Data[4],getCountry($SuperPar_Data[5]),$SuperPar_Data[6]);
								  
								  $blue_icon = "<a class=remind-delete-align href=javascript:del_divlist('$SuperPar_Data[0]','$addr')><img src='/BSOS/images/crm/icon-bluex.gif' width=10 height=9 alt='' border=0 align=left></a>";

								  $del_icon='';
								   if($rootNode!=$addr)
								   { 
									$del_icon="<a class=remind-delete-align href=javascript:del_editdivs('$SuperPar_Data[0]','$addr')><img src='/BSOS/images/crm/icon-delete.gif' width=10 height=9 alt='' border=0 align=left></a>";
									}
									echo $blue_icon.$move."&nbsp;<img src='/BSOS/images/crm/icon-parent.gif' valign=middle>&nbsp;&nbsp;";

								  //showing the current company as parent
								  if($rootNode==$addr)
								   {
									  echo "<font  style='color:#000000;font-size:12px;font-weight:bold;valign=top'>".dispTextdb($parAddr)."</font><br/>";
								   }

								  else
								   {
										  //give the link for those who have the permissions on that company
										  if($SuperPar_Data[7]!='None')
										  {?>
										  <a href=#
										  onClick="javascript:openWin('/BSOS/Marketing/Companies/viewcompanySummary.php?addr=<?=$SuperPar_Data[0]?>&module=<?=$module?>','cmpsummary')" class='crmsummary-contentlnk'><?=$parAddr?></a><br/>
										  <?php }else{?>
										  <font class='crmsummary-contentlnk'><?=$parAddr?></font><br/>
										  <?php }
								  }
						  }//end of supparent-rows
						  else //that parent not existed in data base so  need to traverse from present node
						  {
							 $rootNode=$addr;
						  }


						div_editTree($rootNode,'&nbsp;&nbsp;',$addr);
					}

					?>

					</div>&nbsp;&nbsp;
				</td>
			  </tr>
		</table>
		</td>
		</tr>
		</table>
		</div>
		<div class="form-back accordion-item">
		<table width="100%" border="0" class="crmsummary-edit-tablemin" id="billinginfo">
			<tr>
				<td width="160" class="crmsummary-content-title">
                     <a style='text-decoration: none;' onClick="classToggle(mntcmnt2,'DisplayBlock','DisplayNone',2,'billinginfo')" href="#hideExp2"> <span class="crmsummary-content-title" id="company_billinginform">Billing Information</span></a> 
				</td>
				<td>
			<td>
				 <span id="rightflt" <?php echo $rightflt;?>>
                     <div class="form-opcl-btnleftside"><div align="left"></div></div>
                     <div><a onClick="classToggle(mntcmnt2,'DisplayBlock','DisplayNone','2','billinginfo')" class="form-cl-txtlnk" href="#hideExp2"><b><div id='hideExp2'>+</div></b></a></div>
                     <div class="form-opcl-btnrightside"><div align="left"></div></div>
                 </span>
			</td>
			</tr>
		</table>
		</div>
		<div class="DisplayNone" id="mntcmnt2">
		<table width="100%" class="crmsummary-jocomp-table align-middle">
			<tr>
			<?php
				$cus_username="select username from staffacc_cinfo where sno='".$billcompany."'";
                $cus_username_res=mysql_query($cus_username,$db);
                $cust_username=mysql_fetch_row($cus_username_res);
                $custusername=$cust_username[0];
				
				?>
				<input type="hidden" name="billcompany_sno" id="billcompany_sno" value="<?php echo $billcompany;?>">
				<input type="hidden" name="billcompany_username" id="billcompany_username" value="<?php echo $custusername;?>">
				<td width="160" class="summaryform-bold-title">Default Billing Address</td>
				<td>				
					<span id="billdisp_comp">&nbsp;<input type="hidden" name="bill_loc" id="bill_loc"><a class="crm-select-link" href="javascript:bill_jrt_comp('bill')">select company</a>&nbsp;</span></span>&nbsp;<span id="billcomp_chgid">&nbsp;</span>				
				</td>
			</tr>
			<tr>
				<input type="hidden" name="billcontact_sno" id="billcontact_sno" value="<?php echo $billcontact;?>">
				<td width="160" class="summaryform-bold-title">Default Billing Contact<?php if(TRICOM_REPORTS=='Y'){ echo $mandatory_madison; }?></td>
				<td>
				<?php
				if($billcontact==0)
				{
					?>
					<span id="billdisp"><a class="crm-select-link" href="javascript:bill_jrt_cont('bill')" id="disp">select contact</a></span>
					&nbsp;<span id="billchgid"><a href="javascript:bill_jrt_cont('bill')"><i class="fa fa-search"></i></a><span class="summaryform-formelement">&nbsp;|&nbsp;</span><a class="crm-select-link" href="javascript:donew_add('bill')">new&nbsp;contact</a></span>
					<?php
				}
				else 
				{ 
					?>
					<span id="billdisp"><a class="crm-select-link" href="javascript:contact_func('<?php echo $billcontact;?>','<?php echo $billcont_stat;?>','bill')"><?php echo $billcont;?></a></span>
					&nbsp;<span id="billchgid"><span class=summaryform-formelement>(</span> <a class=crm-select-link href=javascript:bill_jrt_cont('bill')>change </a>&nbsp;<a href=javascript:bill_jrt_cont('bill')><i class="fa fa-search"></i></a><span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class=crm-select-link href=javascript:donew_add('bill')>new</a><span class=summaryform-formelement>&nbsp;|&nbsp;</span><a class="crm-select-link" href="javascript:removeContact('bill')">remove&nbsp;</a><span class=summaryform-formelement>&nbsp;)&nbsp;</span></span>
					<?php
				}
				?>				
				<?php				
				if($billcontact>0 || $bill_loc>0)
				{
					?>
					<script> getCRMLocations('<?php echo $billcompany;?>','<?php echo $billcontact;?>','<?php echo $bill_loc;?>','bill');</script>
					<?php
				}
				?>
				</td>
			</tr>
		<tr>
			 <td valign="top" class="summaryform-bold-title">Payment Terms</td>
			 <td>
			   <?php
				 $BillPay_Sql = "SELECT billpay_termsid, billpay_code FROM bill_pay_terms WHERE billpay_status = 'active' AND billpay_type = 'PT' ORDER BY billpay_code";
				 $BillPay_Res = mysql_query($BillPay_Sql,$db);
				?>
				<select name="billreq" id="billreq" <?php if(TRICOM_REPORTS=='Y'){?> setName='Payment Terms' <?php echo $spl_Attribute; }?> style="width:210px;">
					<option value=""> -- Select -- </option>
					<?php  
					while($BillPay_Data = mysql_fetch_row($BillPay_Res))
					{ 
					?>
						<option value="<?=$BillPay_Data[0];?>" <?php echo sele($bill_req,$BillPay_Data[0]); ?> title="<?=html_tls_specialchars(stripslashes($BillPay_Data[1]),ENT_QUOTES);?>"><?=stripslashes($BillPay_Data[1]);?></option>
					<?php 
					}
					?>
				</select>
				<?php 
					if(ENABLE_MANAGE_LINKS == 'Y')
						echo '&nbsp;&nbsp;&nbsp;<a href="javascript:doManageBillPayTerms(\'Payment\',\'billreq\')" class="edit-list">Manage</a>';
				?>				 
			 </td>
		  </tr>
		  <tr>
			 <td valign="top" class="summaryform-bold-title">Service Terms</td>
			 <td><textarea name="servterms" rows="2" cols="64"><?php echo html_tls_entities(stripslashes($ser_terms),ENT_QUOTES);?></textarea>
			 </td>
		  </tr>
		  <tr>
			 <td valign="top" class="summaryform-bold-title">Pay Burden</td>
			 <td><?php echo $pay_bt_str; ?>
			 </td>
		  </tr>
		  <tr>
			<td>&nbsp;</td>
			<td>
				<b><span id="burdenItemsStr" style="font-weight:bold;font-size: 12px;">&nbsp;</span></b>
			</td>
		  </tr>
		  <tr>
			 <td valign="top" class="summaryform-bold-title">Bill Burden</td>
			 <td><?php echo $bill_bt_str; ?>
			 </td>
		  </tr>
		  <tr>
			<td>&nbsp;</td>
			<td>
				<b><span id="billburdenItemsStr" style="font-weight:bold;font-size: 12px;">&nbsp;</span></b>
			</td>
		  </tr>
		  <tr>
				<td valign="top" class="summaryform-bold-title">Set Default Rate(s)</td>
				<td><a class="crm-select-link" href="javascript:showCompanyRates();">select rates</a></td>
			</tr>
			
			<tr>
				<td></td>
				<td align="left" style="padding:0px">
					<input type="hidden" id="selectedcustomratetypeids" name="selectedcustomratetypeids" value=""/>
					<div id="multipleRatesTab">
					<?php
					if(isset($_SESSION["comp_rates_details".$Rnd]) && $_SESSION['comp_rates_details'.$Rnd]!='' && $_SESSION['comp_rates_details'.$Rnd]!='|')
					{						
						$customratesexplode 	= explode("|", $_SESSION['comp_rates_details'.$Rnd]);
						echo $objMRT->displayCompanyRates($_SESSION['comp_rates_details'.$Rnd]);
						
						$custratessnoexplode	= explode(",",$customratesexplode[1]);
						
						foreach($custratessnoexplode as $custratesnos)
						{
							?>
								<script type="text/javascript">
									pushSelectedPayRateidsArray(<?php echo $custratesnos;?>);
								</script>
							<?php
						}
						?>
						<script type="text/javascript">
							pushAddEditRateRowArray('<?php echo $customratesexplode[1];?>');
							document.getElementById('selectedcustomratetypeids').value = '<?php echo $customratesexplode[1];?>';
						</script>
						<?php
					}
					else
					{
					?>
						<script>
							customRateTypes(<?php echo $addr;?>, '<?php echo $mode_rate_type;?>');	
						</script>
					<?php
						
						$SelQry = "SELECT ma.sno FROM multiplerates_joborder jo, multiplerates_master ma WHERE jo.status = 'ACTIVE' AND jo.joborderid = '".$type_order_id."' AND jo.jo_mode='company' AND ma.status='ACTIVE' AND ma.rateid != 'rate4' AND ma.rateid=jo.ratemasterid GROUP BY ma.name";
						
						$resQry = mysql_query($SelQry);
						$rateValuesArray = array();
						while($recQry = mysql_fetch_assoc($resQry))
						{
						?>
							<script>
								pushSelectedPayRateidsArray(<?php echo $recQry['sno'];?>);
							</script>
						<?php
							$rateValuesArray[] = $recQry['sno'];
						}
						
						if(!empty($rateValuesArray) && $_SESSION['comp_rates_details'.$Rnd] == '')
						{
						?>
							<script type="text/javascript">
								pushAddEditRateRowArray('<?php echo implode(',', $rateValuesArray);?>');
								document.getElementById('selectedcustomratetypeids').value = '<?php echo implode(',', $rateValuesArray);?>';
							</script>
						<?php
						}
					}
					?>
					</div>
					
				</td>
			</tr>
			<tr>
				<td valign="top"></td>
				<td align="left" style="padding:0px">
					<input type="hidden" id="selectedcustomshifts" name="selectedcustomshifts" value=""/>
					<input type="hidden" id="shiftIndexStr" name="shiftIndexStr" value=""/>
					<?php
					//echo "session data".$_SESSION["comp_rates_shift_details".$Rnd];
					?>
					<div id="multipleRatesShiftsTab">
					<?php
					if(SHIFT_SCHEDULING_ENABLED=="Y")
					{
						if(isset($_SESSION["comp_rates_shift_details".$Rnd]) && $_SESSION['comp_rates_shift_details'.$Rnd]!='' && $_SESSION['comp_rates_shift_details'.$Rnd]!='|')
						{
							echo $objMRT->displayCompanyShiftRates($_SESSION['comp_rates_shift_details'.$Rnd]);
							//print_r($objMRT->shiftIdArr);
							foreach($objMRT->shiftIdArr AS $key=>$val)
							{
								echo "<script>pushSelectedShiftidsArray($val);</script>";
							}
							echo "<script>document.getElementById('selectedcustomshifts').value = '".implode('^', $objMRT->shiftAndItRatesArr)."';buildShiftIndexesFromClass();</script>";
						}
						else
						{
						?>
							<script>
								customRateShifts(<?php echo $addr;?>, '<?php echo $mode_rate_type;?>');	
							</script>
						<?php
						}
					}
					?>
					</div>					
				</td>
			</tr>
			
		</table>
		<input type="hidden" value="<?php echo $addr; ?>" id="assign_id_to_get_custom_rates" />
		<input type="hidden" value="<?php echo $mode_rate_type; ?>" id="moderate_type" />
		</div>		
		<div class="form-back accordion-item">
		<table width="100%" border="0" class="crmsummary-edit-tablemin" id="compculture">
		<tr>
			<td width="250" class="crmsummary-content-title">
                     <a style='text-decoration: none;' onClick="classToggle(mntcmnt3,'DisplayBlock','DisplayNone',3,'compculture')" href="#hideExp3"> <span class="crmsummary-content-title" id="company_cultureonbordinfo">Company Culture/Onboarding Information</span></a> 			
			</td>
			<td>
		<td>
			 <span id="rightflt" <?php echo $rightflt;?>>
				 <div class="form-opcl-btnleftside"><div align="left"></div></div>
				 <div><a onClick="classToggle(mntcmnt3,'DisplayBlock','DisplayNone','3','compculture')" class="form-cl-txtlnk" href="#hideExp3"><b><div id='hideExp3'>+</div></b></a></div>
				 <div class="form-opcl-btnrightside"><div align="left"></div></div>
			 </span>
		</td>
		</tr>
		</table>
		</div>
		<div class="DisplayNone" id="mntcmnt3">
		<table width="100%" class="crmsummary-jocomp-table align-middle">
              <tr>
                 <td width="160" class="summaryform-bold-title">Dress Code</td>
 				 <td><input class="summaryform-formelement" type=text name=dcode size=54 maxsize=54 maxlength=54 value="<?php echo html_tls_entities(stripslashes($d_code),ENT_QUOTES);?>">&nbsp;&nbsp;&nbsp;</td>
             </tr>
             <tr>
                 <td class="summaryform-bold-title">Telecommuting Policy</td>
                 <td><input class="summaryform-formelement" type=text name=tpolicy size=54 maxsize=54 maxlength=54 value="<?php echo html_tls_entities(stripslashes($t_policy),ENT_QUOTES);?>">&nbsp;&nbsp;&nbsp;
				 </td>
            </tr>
			<tr>
                 <td class="summaryform-bold-title">Smoking Policy</td>
                 <td><input class="summaryform-formelement" type=text name=spolicy size=54 maxsize=54 maxlength=54 value="<?php echo html_tls_entities(stripslashes($s_policy),ENT_QUOTES);?>">&nbsp;&nbsp;&nbsp;
                 </td>
             </tr>
			 </tr>
			 <tr>
                 <td><div class="summaryform-bold-title">Parking</div><span class="summaryform-nonboldsub-title">(check all that apply)</span></td>
                 <td>
					<div class="d-flex align-items-center">
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="checkbox" name="parking1" value="free" <?php echo sent_check($Other_Field[0],"free"); ?>>
						<label class="form-check-label">Free</label> 
					</div>
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="checkbox" name="parking2" value="onsite" <?php echo sent_check($Other_Field[1],"onsite"); ?>>
						<label class="form-check-label">On Site</label> 
					</div>
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="checkbox" name="parking3" value="offsite" <?php echo sent_check($Other_Field[2],"offsite"); ?>>
						<label class="form-check-label">Off Site</label> 
					</div>
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="checkbox" name="parking4" value="lspaces" <?php echo sent_check($Other_Field[3],"lspaces"); ?>>
						<label class="form-check-label">Limited Spaces</label> 
					</div>
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="checkbox" name="parking5" value="pspaces" <?php echo sent_check($Other_Field[4],"pspaces"); ?>>
						<label class="form-check-label">Plenty of Spaces</label> 
					</div>
					</div>
										
						<div class="form-check form-check-inline">
							<span class="d-flex align-items-center">
							<input class="form-check-input mt-2" type="checkbox" name="parking6" value="prate" <?php echo sent_check($Other_Field[5],"prate"); ?>>
							<label class="form-check-label">Rate&nbsp;(&nbsp;$<input class="summaryform-formelement" type="text" size=5 name="prateval" value="<?php echo html_tls_entities(stripslashes($p_rate),ENT_QUOTES);?>">&nbsp;)</label> 
							</span>
						</div>
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="checkbox" name="parking7" value="validate" <?php echo sent_check($Other_Field[6],"validate"); ?>>
						<label class="form-check-label">Validate</label> 
					</div>
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="checkbox" name="parking8" value="public" <?php echo sent_check($Other_Field[7],"public"); ?>>
						<label class="form-check-label">Public</label> 
					</div>
            	</td>
              </tr>
			 <tr>
                 <td valign="top" class="summaryform-bold-title">Directions</td>
                 <td><textarea name="directions" rows="2" cols="64"><?php echo html_tls_entities(stripslashes($directions),ENT_QUOTES);?></textarea>
            	</td>
             </tr>
			 <tr>
                 <td valign="top" class="summaryform-bold-title">Other Info/Culture</td>
                 <td><textarea name="infocul" rows="2" cols="64"><?php echo html_tls_entities(stripslashes($info_cul),ENT_QUOTES);?></textarea>
            	</td>
             </tr>
            </table>
			</div><br /><br />
</fieldset>

		<fieldset>
		<legend><font class=afontstyle>Company Contacts</font></legend>
		<table width=100% cellpadding=3 cellspacing=0 border=0>
		<tr valign=top>
		<td align=right>
			<a  href="javascript:doAddCon()" tabindex=56><font class=linkrow>Add Contact</font></a>
		</td>
		</tr>
		<tr>
			<td align=center>
			<table width=100% cellpadding=0 cellspacing=1 border=0>
				<?php
				if(count($arry)>0)
				{
					print "<tr valign=top><td><table width=100% border=0 cellpadding=2 cellspacing=0 id='compContList'><tr class=hthbgcolor><td width=25%><font class=afontstyle>Contact Name</font></td><td width=25%><font class=afontstyle>Title</font></td><td width=10%><font class=afontstyle>Contact Type</font></td><td width=20%><font class=afontstyle>Phone Number</font></td><td width=10%><font class=afontstyle>&nbsp;</font></td></tr>";
					for($j=0;$j<count($arry);$j++)
					{
						print "<tr class='panel-table-content-new' valign=top>";
						for($i=0;$i<4;$i++)
						{
							if($arry[$j][2]=="--")
							   $arry[$j][2]="";

							if($i==0)
							{
								$arry[$j][$i] = ($arry[$j][$i]=="") ? "-NA-" : $arry[$j][$i];
								print "<td><a href=javascript:editCon('".$arry[$j][4]."','".$arry[$j][5]."')><font class=linkrow>".stripslashes($arry[$j][$i])."</font></a></td>";
							}
							else
							{
								print "<td><font class=summarytext>".stripslashes($arry[$j][$i])."</font></td>";
							}
						}
						print "<td><a href=javascript:delCon('".$arry[$j][4]."','".$arry[$j][5]."','".$arry[$j][6]."')><font class=linkrow>Delete</font></a></td></tr>";
					}
					print "</table></td></tr>";
				}
				else
				{
					print "<tr><td align=center colspan=5><font class=afontstyle>No Contacts are available.</font></td></tr>";
				}
				?>
			</table>
			</td>
		</tr>
		</table>
		</fieldset>

		<fieldset>
		<legend><font class=afontstyle>Company Opportunity Information</font></legend>
			<table border=0 width=100% cellspacing=1 cellpadding=0>
		    <tr>
			<td align=right valign=top>
				<a  href="javascript:doAddoppr()" tabindex=57><font class=linkrow>Add Opportunity</font></a>
			</td>
			</tr>
			<tr>
				<td align=center width=100%>
			    <table width=100% cellpadding=0 cellspacing=1 border=0>
		  			<?php
					if($opp_rows>0)
					{
						print "<tr valign=top><td><table width=100% border=0 cellpadding=2 cellspacing=0><tr class=hthbgcolor><td width=15%><font class=afontstyle>Last&nbsp;Modified</font></td><td width=15%><font class=afontstyle>Opportunity&nbsp;Name</font></td><td width=15%><font class=afontstyle>Stage</font></td><td width=15%><font class=afontstyle>Amount</font></td><td width=15%><font class=afontstyle>%</font></td><td width=15%><font class=afontstyle>Exp.&nbsp;Close&nbsp;Date</font></td><td width=15%><font class=afontstyle>Notes</font></td><td width=10%>&nbsp;</td></tr>";
						while($opp_data=mysql_fetch_row($opp_res))
						{
							$opp_data[1] = ($opp_data[1]=="") ? "-NA-" : $opp_data[1];
							$opp_data[4] = ($opp_data[4]=="" || $opp_data[4]=="00/00/0000") ? "" : $opp_data[4];
							$opp_data[3] = ($opp_data[9]=="USD") ? getUSDAmountFormat($opp_data[3]) : $opp_data[3];
							$opp_data[9] = ($opp_data[3]=="") ? "" : $opp_data[9];

							print "<tr class='panel-table-content-new' valign=top>";
							if(strlen($opp_data[6])>50)
							{
								print "<td><font class=summarytext>".$opp_data[0]."</font></td><td><a href=javascript:editOppr('$opp_data[5]')><font class=linkrow>".dispTextdb1($opp_data[1])."</font></a></td><td><font class=summarytext>".dispTextdb1(getManage($opp_data[2]))."</font></td><td><font class=summarytext>".$opp_data[3]." ".$opp_data[9]."</font></td><td><font class=summarytext>".$opp_data[8]."</font></td><td><font class=summarytext>".$opp_data[4]."</font></td><td><font class=summarytext><div id='notes'".$opp_data[5]."' style='width:200px;height:100px;overflow:auto;'>".dispTextdb1($opp_data[6])."</div></font></td>";
							}
							else
							{
								print "<td><font class=summarytext>".$opp_data[0]."</font></td><td><a href=javascript:editOppr('$opp_data[5]')><font class=linkrow>".dispTextdb1($opp_data[1])."</font></a></td><td><font class=summarytext>".dispTextdb1(getManage($opp_data[2]))."</font></td><td><font class=summarytext>".$opp_data[3]." ".$opp_data[9]."</font></td><td><font class=summarytext>".$opp_data[8]."</font></td><td><font class=summarytext>".$opp_data[4]."</font></td><td><font class=summarytext>".dispTextdb1($opp_data[6])."</font></td>";
							}
							print "<td><a href=javascript:delOppr('$opp_data[5]')><font class=linkrow>Delete</font></a></td></tr>";
						}
						print "</table></td></tr>";
					}
					else
					{
						print "<tr><td align=center colspan=8><font class=afontstyle>No Opportunities  are available.</font></td></tr>";
					}
					?>

			   </table>
			 </td>
			</tr>
		</table>
		</fieldset>
	<br />
	<table width=100% cellpadding=3 cellspacing=0 border=0>
    <tr class="NewGridBotBg">
	<?php
	
	?>
	</tr>
</table>
</div>