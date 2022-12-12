<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor. 
 */

class htmlGenerator {
    function getBModal($attributes = []) {
		$bg_gray = (isset($attributes['bg_gray'])) ? 'bg-gray' :"";
		$md_scroll = (isset($attributes['scroll'])) ? 'modal-dialog-scrollable' :"";
		//$md_center = (isset($attributes['center'])) ? "m-0":'modal-dialog-centered';
		$md_center ='modal-dialog-centered';
		$attributes['id'] = isset($attributes['id']) ? str_replace(' ', '_', $attributes['id']) : "";
		//$iframe_close =  isset($attributes['iframe_close']) ? "closeIframeModal();" : "javascript:window.close();";
		//$wind_close = 	isset($attributes['closeAction']) ? $attributes['closeAction'] : "javascript:window.close();closeIframeModal();";
		$wind_close = !empty($attributes['closeAction']) ? $attributes['closeAction'] : "javascript:window.close();closeIframeModal();"; //javascript:window.close();
		$modal_close = 	($attributes['closeAction'] == 'void(0)') ? "data-bs-dismiss='modal'":"";
		$extracalss = 	isset($attributes['extraclass']) ? $attributes['extraclass'] : "";
		$modalSize = isset($attributes['modalsize']) ? $attributes['modalsize'] : "modal-fullscreen";
        $html = "<button type='button' class='btn btn-primary d-none' data-bs-toggle='modal' data-bs-target='#{$attributes['id']}Modal' id='submit{$attributes['id']}'>{$attributes['title']}</button>
                        <div class='modal fade modal-primary' id='{$attributes['id']}Modal' data-bs-backdrop='static' data-bs-keyboard='false' tabindex='-1' aria-labelledby='{$attributes['id']}ModalLabel' aria-hidden='true'>
                          <div class='modal-dialog {$md_center} {$md_scroll} {$modalSize}'>
                            <div class='modal-content'>
                              <div class='modal-header'>
                                <h5 class='modal-title' id='{$attributes['id']}ModalLabel'>{$attributes['title']}</h5>
                                <button type='button' class='btn-close btn-close-white'  {$modal_close} aria-label='Close' onclick='{$wind_close}'></button>
                              </div>
                              <div class='modal-body {$bg_gray} {$extracalss}' id='{$attributes['id']}Frame'></div>
                              <div class='modal-footer'>";
		if(isset($attributes['buttons'])){
			foreach($attributes['buttons'] as $key=>$val){
			$html .="<button type='button' ";
			$tagAttr = "";
				foreach($val as $attr=>$aval){
					$tagAttr .= " {$attr}='{$aval}' ";		
				}
				$html .=" {$tagAttr} >{$key}</button>";		
			}
		}else{
			$html .="<button type='button' class='btn btn-primary'  {$modal_close} onclick='{$wind_close}'>Cancel</button>";
            $html .= isset($attributes['saveAction'])?"<button type='button' class='btn btn-success'  onclick='{$attributes['saveAction']}'>Save</button>":"";
		}
		$html .="</div></div></div></div>";
        return $html;
    }

	 function getModalHeader($attributes = []) {
	 	$bg_gray = (isset($attributes['bg_gray'])) ? 'bg-gray-200' :"";
		$md_scroll = (isset($attributes['scroll'])) ? 'modal-dialog-scrollable' :"";
//		$md_center = (isset($attributes['center'])) ? "m-0":'modal-dialog-centered';
		$md_center ='modal-dialog-centered';
		$attributes['id'] = isset($attributes['id']) ? str_replace(' ', '_', $attributes['id']) : "";
		$iframe_close =  isset($attributes['iframe_close']) ? "closeIframeModal();" : "javascript:window.close();";
		$wind_close = !empty($attributes['closeAction']) ? $attributes['closeAction'] : "javascript:window.close();closeIframeModal();"; //javascript:window.close();
		$md_iframe = (isset($attributes['noiframe'])) ? "" :"show d-block";
		$modal_close = 	($attributes['closeAction'] == 'void(0)') ? "data-bs-dismiss='modal'":"";
		$extracalss = 	isset($attributes['extraclass']) ? $attributes['extraclass'] : "";
		$modalSize = isset($attributes['modalsize']) ? $attributes['modalsize'] : "modal-fullscreen";
		$nextprev = "";
		if(isset($attributes['nextprev'])){
			$nextprev = " <button class='sumBtn' type='button' name='sprev' id='sprev' value='<< Prev' onClick=\"javascript:prevGridRec({$attributes['nextprev']['total']},'{$attributes['nextprev']['file']}');\"><i class='fas fa-angle-left'></i></button> <button class='sumBtn' type='button' name='snext' id='snext' value='Next >>' onClick=\"javascript:nextGridRec({$attributes['nextprev']['total']},'{$attributes['nextprev']['file']}');\"><i class='fas fa-angle-right'></i></button> ";
		}
		
		$navMenu = "";
		if(isset($attributes['navMenu'])){
			$navMenu = $this->generateMenuNav($attributes['navMenu']);
		}
		

        $html = "<button type='button' class='btn btn-primary d-none' data-bs-toggle='modal' data-bs-target='#{$attributes['id']}Modal' id='submit{$attributes['id']}'>{$attributes['title']}</button>
		<div class='modal fade {$md_iframe} modal-primary' id='{$attributes['id']}Modal' data-bs-backdrop='static' data-bs-keyboard='false'  tabindex='-1' aria-labelledby='{$attributes['id']}ModalLabel' aria-hidden='true'>
                          <div class='modal-dialog {$md_center} {$md_scroll} {$modalSize}'>
                            <div class='modal-content'>
                              <div class='modal-header border-0 gap-3'>
                                <h5 class='modal-title' id='{$attributes['id']}ModalLabel'>{$attributes['title']}</h5>
								{$nextprev}
                                <button type='button' class='btn-close btn-close-white' {$modal_close} aria-label='Close' onclick='{$wind_close}' ></button>
                              </div>
							  {$navMenu}
							  <div class='modal-body {$bg_gray} {$extracalss}'>";
                              
        return $html;
    }

	 function getModalFooter($attributes = []) {
	 	$iframe_close =  isset($attributes['iframe_close']) ? "closeIframeModal();" : "javascript:window.close();";
	 	$wind_close = !empty($attributes['closeAction']) ? $attributes['closeAction'] : "javascript:window.close();closeIframeModal();"; //javascript:window.close();
		$extracalss = 	isset($attributes['extraclass']) ? $attributes['extraclass'] : "";
		$modal_close = 	($attributes['closeAction'] == 'void(0)') ? "data-bs-dismiss='modal'":"";
		$modal_title =  isset($attributes['title']) ? $attributes['title'] : "Save";
        $html = "</div>";
		if($attributes){
		    $html .= "<div class='modal-footer {$extracalss}'>";
			if(isset($attributes['buttons'])){
				foreach($attributes['buttons'] as $key=>$val){
				$title_icon = explode('~',$key);
				if(isset($title_icon[2]) && $title_icon[2]=='anchor'){
					$html .="<a ";
				}else{
					$html .="<button ";
				}					
				$tagAttr = "";
					foreach($val as $attr=>$aval){
						$aval = ($attr == 'class') ? 'btn btn-lg '.$aval : $aval;
						$hasType = (isset($val['type'])) ? 1 : 0;
						$tagAttr .= " {$attr}='{$aval}' ";
					}
					$html .= (!$hasType) ? " type='button' ": '';
					$icon = ($title_icon[0])? "<i class='fas fa-solid {$title_icon[0]}'></i>":"";
					$html .=" {$tagAttr} >{$icon} {$title_icon[1]}";	
					if(isset($title_icon[2]) && $title_icon[2]=='anchor'){
						$html .="</a>";
					}else{
						$html .="</button>";
					}
				}
			}else{
				$html .="<button type='button' class='btn btn-dark' {$modal_close}  onclick='{$wind_close}'><i class='fas fa-solid fa-times'></i> Cancel</button>";
				/*$html .= isset($attributes['saveAction'])?"<button type='button' class='nav-link nav-link-success'  onclick='{$attributes['saveAction']}'><i class='fas fa-save'></i> Save</button>":"";*/
				$html .= isset($attributes['saveAction'])?"<button type='button' class='btn btn-success'  onclick='{$attributes['saveAction']}'><i class='fas fa-solid fa-save'></i>{$modal_title}</button>":"";
				
			}
			$html .="</div>";
		}
		$html .="</div></div></div>";
        return $html;
         
    }

   function generateMenuNav($attributes=[]){

		$titlesLinks = $this->getModuleTitlesFiles($attributes['module'],$attributes['action']);
		$attributes = array_merge($attributes,$titlesLinks);
		$titles = $attributes['titles'];
		$submenu = $attributes['submenu'];
		if(isset($attributes['params']))
		{
			foreach($attributes['params'] as $key=>$val){
				$param_key = $key;
				$param_val = $val;
			}
		}
		
		$filename = $file_nums = [];
		if(isset($attributes['files'])){
			$files = $attributes['files'];
			$f = 0;
			foreach($files as $key=>$val){
				$tempname = explode('#',$key);	
				$filename[] = $tempname[0];
				$file_nums = $val;	
				$f++;
			}			
		}
		$leftArrow = $rightArrow = $nav_scroll= "";
		$fcounts = count($titles);
		//To show right and left arrows
		if($fcounts > 12){
			$leftArrow = "<div class='nav-scroller-pagination leftArrow'><span class='material-icons-outlined'>arrow_back_ios_new</span></div>";
			$rightArrow = "<div class='nav-scroller-pagination next rightArrow'><span class='material-icons-outlined'>arrow_forward_ios_new</span></div>";
			$nav_scroll = "nav-scroller";
		}		

		$html = "<div class='{$nav_scroll} nav-scroller-pagination-controls-enabled bg-white shadow-sm' id='{$attributes['module']}'>{$leftArrow}<div class='nav-scroller-container'><div class='nav-scroller-nav-list'><ul class='nav nav-underline innerWrapper mx-2' aria-label='Secondary navigation'>";
		for($i=0; $i < $fcounts; $i++){
			$paramkey = $param_key;
			$curr_file = (empty($file_nums)) ? $i : $file_nums[$i] ;
			$active = ($attributes['active'] == $curr_file) ? "text-success active":"";
			if($i == 0){
				$singlefile = $filename[$i];
				$singleParam = is_array($param_val) ? $param_val[$i]: $param_val;
			}
			$filenames = isset($filename[$i])? $filename[$i] : $singlefile;
			$param_values = (is_array($param_val) && isset($param_val[$i]))? $param_val[$i] : $singleParam;
			$page_href = ($param_key == 'href') ? "{$filenames}{$file_nums[$i]}.php{$param_values}":str_replace('pageno',$file_nums[$i],$param_values);
			if(isset($attributes['leave']) &&  $attributes['leave'] == $file_nums[$i] ){
				$param_key = "href";
				$page_href = "#";
			}	
			if(isset($submenu[$i])){
				$smenu = $this->generateSubMenu($submenu[$i],$page_href);
				$html .= "<li class='nav-item dropdown'><a class='nav-link dropdown-toggle {$active}' data-bs-toggle='dropdown' aria-current='page' {$param_key}={$page_href} role='button'>{$titles[$i]}</a>{$smenu}</li>";
			}else{
				$html .= "<li class='nav-item'><a class='nav-link {$active}' aria-current='page' {$param_key}={$page_href} role='button'>{$titles[$i]}</a></li>";
			}		
			
			$param_key = $paramkey;
		}
		$html .= "</ul></div></div>{$rightArrow}</div>";
		return $html;
   }

   function generateSubMenu($submenu,$page_href){
	$subHtml = "<ul class='dropdown-menu'>";
	foreach($submenu as $sitem){
		$navitem = preg_replace("/[^a-zA-Z0-9]+/", "", $sitem);
		$subHtml .= "<li><a class='dropdown-item' rel='m_PageScroll2id' href='{$page_href}#{$navitem}'>{$sitem}</a></li>"; 	
	}
	$subHtml .= "</ul>";
	return $subHtml;
   }

   function getModuleTitlesFiles($module,$action='edit'){
   		$titlesLinks = [];
		switch ($module) {
			case 'candidates':
				$titlesLinks =	["titles"=>["Summary","Candidate Info","Contact Info","Introduction","Skills","Education","Experience","Preferences","Affiliations","AddInfo","References","Credentials","Availability"],"files"=>["revconreg"=>[0,1,2,3,4,5,6,7,8,9,10,14,15]]];
				if($action == 'add'){
					$insert = ['Resume'=>'11'];
					array_shift($titlesLinks["titles"]);
					array_shift($titlesLinks["files"]["revconreg"]);
					$titlesLinks["titles"] = array_merge(array_slice($titlesLinks["titles"], 0, 10), array_keys($insert), array_slice($titlesLinks["titles"], 10));
					$titlesLinks["files"]["revconreg"] = array_merge(array_slice($titlesLinks["files"]["revconreg"], 0, 10), array_values($insert), array_slice($titlesLinks["files"]["revconreg"], 10));
				}
				break;
			case 'contacts':
				$titlesLinks =	["titles"=>["Summary","Edit"],"files"=>["contactSummary"=>[],"editmanage"=>[]]];
				break;
			case 'timesheets':
				$titlesLinks =	["titles"=>["All Time Sheets","Approved","Deleted"],"files"=>["timesheets"=>[],"aptimesheets"=>[],"aptimesheets#2"=>[]]];
				break;
			case 'profile_timesheets':
				$titlesLinks =	["titles"=>["All Time Sheets","Submitted","Approved","Rejected"],"files"=>["timesheets"=>[],"timesheets#2"=>[],"history"=>[],"timesheets#3"=>[]]];
			break;
			case 'expenses':
				$titlesLinks =	["titles"=>["All Expenses","Submitted","Approved","Rejected","Deleted"],"files"=>["expenses"=>[],"expenses#1"=>[],"apexpenses"=>[],"expenses#3"=>[],"apexpenses#4"=>[]]];
				break;
			case 'profile_expenses':
				$titlesLinks =	["titles"=>["All Expenses","Submitted","Approved","Rejected"],"files"=>["expense"=>[],"expense#2"=>[],"approveex"=>[],"expense#3"=>[]]];
				break;
			case 'profile_assignments':
				$titlesLinks =	["titles"=>["Active","Closed"],"files"=>["jobs"=>[],"jobs#1"=>[]]];
				break;
			case 'client_assignments':
				$titlesLinks =	["titles"=>["Active","Closed"],"files"=>["assignment"=>[],"assignment#1"=>[]]];
				break;
			case 'profile_benefits':
				$titlesLinks =	["titles"=>["Earned","Other"],"files"=>["benefits"=>[],"benefits#1"=>[]]];
				break;
			case 'reports':
				$titlesLinks =	["titles"=>["Filters","Order","Sort","Header/Footer"],"files"=>["viewfilter"=>[],"viewsort"=>[],"viewcolsort"=>[],"viewheader"=>[]]];
				break;
			case 'knowledgecenter':
				$titlesLinks =	["titles"=>["All Documents","My FAQ","My Favorites"],"files"=>["resman"=>[],"mypending"=>[],"removefavorite"=>[]]];
				break;
			case 'joborders':
				$titlesLinks =	["titles"=>["Job Order Details","Edit"],"files"=>["jobSummary"=>[],"editjoborder"=>[]]];
				if($action == 'preview'){
					$titlesLinks["titles"][] = "Job Posting Preview"; 
					$titlesLinks["files"] = array_merge($titlesLinks["files"],["/BSOS/Admin/Jobp_Mngmt/Joborder/joborderpreview"=>[]]); 
				}
				break;
			case 'companies':
				$titlesLinks =	["titles"=>["Company Details","Edit"],"files"=>["companySummary"=>[],"editmanage"=>[]]];
				break;
			case 'applicantTracking':
				$titlesLinks =	["titles"=>["Profile Data","HR Data","Resume","Activities"],"files"=>["revconreg1"=>[],"revconreg19"=>[],"revconreg16"=>[],"viewact"=>[]]];
				break;
			case 'allactivities':			
				if($action){
					$titlesLinks =	["titles"=>["Activities"],"files"=>["viewact"=>[]]];				
				}else{
					$titlesLinks =	["titles"=>["View","Edit","Activities"],"files"=>["viewtable"=>[],"editmanage"=>[],"viewact"=>[]]];		
				}				
				break;
			default:
				break;
		}
		return $titlesLinks;
   }
   
   
	function createFormElements($ele_type,$attributes=[]){
		 switch($ele_type)
		{
			case 'select':
			   $str = $this->form_select($attributes);
			break;
			case 'input':
			   $str = $this->form_input($attributes);
			break;			
			case 'textarea':
			   $str = $this->form_textarea($attributes);
			break;
			case 'upload':
			   $str = $this->form_upload($attributes);
			break;
			case 'switch':
			   $str = $this->form_switch($attributes);
			break;
		}
		return $str;
	}
    
	function form_select($params) {
		if(isset($params['eleAttr'])){
			$attributes = $params['eleAttr'];
			foreach($attributes as $akey=>$aval){
				$attr .=  " {$akey}='{$aval}' "; 
			}
		}
		if(isset($params['pills'])){
			$dataContainer = "data-dropdown-parent='{$params['container']}'";
			$class = "class='form-select custom-select-pills'";
		}else{
			$dataContainer = "data-container='{$params['container']}'";
			$class = "class='form-select custom-select'";
		}
		$sel_vals = [];
		if(isset($params['options'])){
			if(is_array($params['options'])){
				$sel_vals = $params['options'];
			}else{
				$sel_vals = $this->sqlToArray($params['options']);
			}
		}
		$label = '';
		if(isset($params['lableAttr'])){
			$label = $this->generateLabel($params['lableAttr']);
		}
		$str = "{$label}<select {$attr} {$class} {$dataContainer}><option value=''>Select</option>";
		foreach($sel_vals as $skey=>$sval){
			$selected = (isset($params['selected']) && ($skey == $params['selected'])) ? 'selected':''; 
			$str .= "<option value='{$skey}' {$selected}>{$sval}</option>";
		}
		$str .= "</select>";
		return $str;
	}

	function form_input($params) {
		if(isset($params['eleAttr'])){
			$attributes = $params['eleAttr'];
			foreach($attributes as $akey=>$aval){
				$attr .=  " {$akey}='{$aval}' "; 
			}
		}
		$class = (isset($params['extraClass'])) ? "class='form-control {$params['extraClass']}'" : "class='form-control'";
		$label = '';
		if(isset($params['lableAttr'])){
			$label = $this->generateLabel($params['lableAttr']);
		}
		$str = "{$label}<input {$attr} {$class}>";
		return $str;
	}

	function form_textarea($params) {
		if(isset($params['eleAttr'])){
			$attributes = $params['eleAttr'];
			foreach($attributes as $akey=>$aval){
				$attr .=  " {$akey}='{$aval}' "; 
			}
		}
		$class = (isset($params['extraClass'])) ? "class='form-control {$params['extraClass']}'" : "class='form-control'";
		$label = '';
		if(isset($params['lableAttr'])){
			$label = $this->generateLabel($params['lableAttr']);
		}
		$str = "{$label}<textarea {$attr} {$class}>{$params['content']}</textarea>";
		return $str;
	}
	
	function form_upload($params){
		return $str;
	}

	function form_switch($params){
		$params['lableAttr']['for'] = $params['eleAttr']['id'];
		$params['lableAttr']['class'] = 'switch-label';
		$params['eleAttr']['class'] = 'switch-input';
		$params['eleAttr']['type'] = 'checkbox';
		$switch_chk = $this->createFormElements('input',$params);
		$str = "<div class='switch'>{$switch_chk}</div>";
		return $str;
	}

	function sqlToArray($qry)
	{
		global $db;
		$result = mysql_query($qry, $db);
		while($row = mysql_fetch_array($result))
		{
			$arr[$row[0]] = $row[1];
		}
		return $arr;
	}

	function generateLabel($params){
		$lb_class = (isset($params['class'])) ? "class='{$params['class']} {$params['extraClass']}'" : "class='form-label {$params['extraClass']}'";
		$label = '';
		if(isset($params['label'])){
			$label = $params['label'].':';
		 }
		$str = "<label {$lb_class}>{$label}</label>";
		return $str;
	}
	
	function prepareAttributes(){

	}

	function get_times ($data=[]) {
		$data['default'] =isset($data['default'])?$data['default']:'';
		$data['interval'] =isset($data['interval'])?$data['interval']: '+30 minutes';
		$current = strtotime('00:00');
		$end = strtotime('23:59');
		$output =  "<select class='form-select custom-select' name='{$data['name']}' id='{$data['name']}'><option value=''>Select</option>";
		while ($current <= $end) {
			$time = date('H:i', $current);
			$sel = ($time == $data['default']) ? ' selected' : '';

			$output .= "<option value=\"{$time}\"{$sel}>" . date('h.i A', $current) .'</option>';
			$current = strtotime($data['interval'], $current);
		}
		$output .=  "</select>";
		return $output;
	}

	function getDocIcon($doc){
     $img_ic = ['jpeg','jpg','jpe','tiff','tif','png','gif','bmp'];
     $pdf_ic = ['pdf'];
     $doc_ic = ['odt','doc','dot','docx','dotx','docm','dotm','ppt','pot','pps','ppa','pptx','potx','ppsx','ppam','pptm','potm','ppsm'];
     $xls_ic = ['xlm','xlc','xlw','xls','xlt','xla','xlsx','xltx','xlsm','xltm','xlam','xlsb'];
     $text_ic = ['txt','text','conf','def','list','log','in','dif','html','htm'];
     
     $ext =  pathinfo($doc, PATHINFO_EXTENSION);
     if(in_array($ext,$img_ic)){
         $req_icon = "<i class='fa-solid fa-file-image text-warning'></i>";
     }elseif(in_array($ext,$pdf_ic)){
         $req_icon = "<i class='fa-solid fa-file-pdf text-danger'></i>";
     }elseif(in_array($ext,$doc_ic)){
         $req_icon = "<i class='fa-solid fa-file-word text-primary'></i>";
     }elseif(in_array($ext,$xls_ic)){
         $req_icon = "<i class='fa-solid fa-file-excel text-success'></i>";
     }elseif(in_array($ext,$text_ic)){
         $req_icon = "<i class='fa-solid fa-file-text text-primary'></i>";
     }else{
         $req_icon = "<i class='fa-solid fa-file-lines text-primary'></i>";
     }
     return $req_icon;
 }

 function navbarPrimary($btn_on,$act_name){
 		global $def_reports_domain,$def_appsvr_domain,$logo_path,$home_head,$crmpref,$marketingpref,$salespref,$supportpref,$hrmpref,$collaborationpref,$accountingpref,$analyticspref,$dashboardpref,$adminpref,$myprofilepref,$companyuser,$last_login,$empstatus,$crmcust,$marketingcust,$salescust,$supportcust,$hrmcust,$collaborationcust,$accountingcust,$analyticscust,$dashboardcust,$admincust,$myprofilecust,$akkubiDashboardpref,$myreferencespref,$myreferencescust,$mysharedjobspref,$mysharedjobscust,$db;
		$menu = "<link href='/BSOS/css/topmenu.css' rel='stylesheet' type='text/css'>
					  <script type='text/javascript' src='/BSOS/scripts/topmenu.js'></script>
					  <script src='/BSOS/scripts/downloadarray.js'></script>
					  <script src='/BSOS/scripts/newfeaturedownmenu.js'></script>";
		$que="select image_data,image_type from company_logo";
						$res=mysql_query($que,$db);
						$row=mysql_fetch_assoc($res);
						// Read image path, convert to base64 encoding
						$imageData = base64_encode($row['image_data']);

						// Format the image SRC:  data:{mime};base64,{data};
						$src = "data: {$row['image_type']};base64,{$imageData}";
	
						$selectedMainMenuName = $btn_on;
						if($btn_on != 'admin')
						{
						$selectSubMenuName = strtolower(str_replace(' ','',$mod_title));
						}
						require("dropmenu.php"); 

		$menu .= "
    <div id='global' class='navbar-nav ms-auto UserMenuNew'>
	<div class='nav-item dropdown'>
	  <a class='nav-link dropdown-toggle d-flex align-items-center' href='#' id='MyAccountDropdown' role='button' data-bs-toggle='dropdown' aria-expanded='false'>
	    <img src=/BSOS/images/profile_icon_bg.png  class='userprofilepicNew' width='40' height='40'>
	      <span>{$act_name}</span>
	  </a>
	  <ul class='dropdown-menu dropdown-menu-end' aria-labelledby='MyAccountDropdown'>
	    <li><a class='dropdown-item' href='javascript:openeDeskpopup()'>eDesk Management</a></li>
	    <li><a class='dropdown-item' href='http://support.akkencloud.com/' target=_blank'>Go To Help Center</a></li>
	    <li><a class='dropdown-item' href='{$def_appsvr_domain}/BSOS/Home/changepassword.php'>Change Password</a></li>
	    <li><a class='dropdown-item' href='{$def_appsvr_domain}/BSOS/Home/logout.php?comp=true'>Logout</a></li>
	  </ul>
	</div>
    </div></div>";
	echo $menu;
 }

 function pageHeader($pageTitle,$navitems){
	 $headers = "</head><body><div class='container-fluid page-header'>
	 <div class='row'>
		 <div class='col'>
			 <h1>{$pageTitle}</h1>
		 </div>
	 </div>
	</div>
	<div class='container-fluid bg-gray-200'>
		<div class='row'>
			<div class='col'>
				<ul class='nav nav-tabs' id='myTab' role='tablist'>";
		foreach($navitems as $key=>$val){
			$act = $curpage = '';
			if($val['active']){
				$act = 'active';
				$curpage = "aria-current='page'";
			}
			$headers .= "<li class='nav-item' role='presentation'>
			<a class='nav-link {$act}' {$curpage} href='{$val['link']}'>{$key}</a></li>";
		}
		$headers .= "</ul></div></div></div>";
	return $headers;
 }

 
 /* vp */
 function dashboardHeader($pageTitle,$buttons){
	 $headers = "<div class='d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom'>
	 <h1 class='h5 mb-0 text-primary'>{$pageTitle}</h1>
	<div class='d-flex gap-3 justify-content-end'>";
		foreach($buttons as $key=>$val){
			$jsaction = !empty($val['jsAction']) ? $val['jsAction'] : "";
			$headers .= "<button type='button' class='btn btn-lg {$val['class']}' onclick='{$jsaction}'><i class='fa-solid {$val['icon']}'></i> {$key}</button>";
		}
		$headers .= "</div></div>";
	return $headers;
 }
 
 function dashboardLeftNav($acrditems=[]){
	 /* $lftNav = '<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar px-0">
	 <div class="position-sticky sidebar-sticky"><div class="accordion accordion-flush" id="accordionSidebar">'; */
	 /* for($i=0; $i < $fcounts; $i++){ 
	 $acrditems = ['Hiring1'=>['id'=>'hiring','iconClass'=>'material-icons','icon'=>'person_search','navlabel'=>['Paperless Onboarding1','eSkills1','Sterling1','Asurint1'],'navlink'=>['Paperless Onboarding1','eSkills1','Sterling1','Asurint1']]];*/
	 
/* $acrditems = [
'Hiring'=>[
	'id'=>'hiring','iconClass0'=>'material-icons','icon0'=>'person_search',
	'navlabel'=>[[
		'Paperless Onboarding0'=>[
			'id'=>'hiring0','iconClass'=>'material-icons','icon'=>'person_search',
			'navlabel'=>[[
				'Paperless Onboarding1'=>['navlabel'=>[
					'Paperless Onboarding2','eSkills2','Sterling2','Asurint2'
				],
				'navlink'=>[
					'21#','22#','23#','24#'
				]]],
				'eSkills1','Sterling1','Asurint1'
			],
			'navlink'=>[
				'11#','12#','13#','14#'
			]]],
'eSkills0','Sterling0','Asurint0'],
'navlink'=>['00#','01#','02#','03#'],'element'=>'a','action'=>['onclick="myfunction();"','onchange="myfunction1();"','onmouseover="myfunction2();"','']]]; */


$lftNav = '<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar px-0">
	 <div class="position-sticky sidebar-sticky">';
	 
	 foreach($acrditems as $key=>$val){
		 /* $lftNav .= "<div class='accordion-item'><h2 class='accordion-header bg-transparent' id='{$val['id']}'>";
		 $lftNav .= "<button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#collapse{$val['id']}' aria-expanded='false' aria-controls='collapse{$val['id']}'>";
		 $lftNav .= "<span class='{$val['iconClass']}'>{$val['icon']}</span> {$key}</button></h2>";
		 $lftNav .= "<div id='collapse{$val['id']}' class='accordion-collapse collapse show' aria-labelledby='{$val['id']}' data-bs-parent='#accordionSidebar'>"; */
		 $lftNav .= "<ul class='nav nav-pills flex-column'>";
		 
		for($i=0; $i < count($val['navlabel']); $i++){
			$lnkCls = ($i==0)?"mm-active":"";
			$lnkAnc = ($i==0)?"true":"false";
			
			$lftNav .= "<li class='nav-item {$lnkCls}'>";
			$nvLnk = (isset($val['navlink'][$i]))? $val['navlink'][$i]:"#";
			$navAry = $val['navlabel'][$i];
			$navlnkAry = $val['navlink'][$i];
			if(is_array($val['navlabel'][$i])){															
				foreach($val['navlabel'][$i] as $skey=>$sval){
					
					if($val['element'] == 'a'){
						$lftNav .= "<a class='nav-link' aria-expanded='{$lnkAnc}' href='{$sval['navlink'][$i]}'>{$skey}</a>";
					}else{
						$lftNav .= "<button class='nav-link' aria-expanded='{$lnkAnc}' {$val['action'][$i]}>{$skey}</button>";
					}
					
					$lftNav .= $this->generatelftSubMenu($sval['navlabel'],$sval['navlink'],$sval['element'],$sval['action']);
				}			
			}else{
				if($val['element'] == 'a')
					$lftNav .= "<a class='nav-link' href='{$nvLnk}'>{$val['navlabel'][$i]}</a>";
				else
					$lftNav .= "<button class='nav-link' aria-expanded='{$lnkAnc}' {$val['action'][$i]}>{$val['navlabel'][$i]}</button>";
			}
			$lftNav .= "</li>";
		}
			$lftNav .= "</ul>";
	 }
	
		$lftNav .= "</div></nav>";
	return $lftNav;
 }
 
 function generatelftSubMenu($subMenuitems=[],$subMenulnks=[],$subMelmnt,$subMenuactn=[]){
	 	
	$subHtml = "<ul class='list-unstyled'>";
	
	 for($i=0; $i < count($subMenuitems); $i++){
		 $lnkAnc = ($i==0)?"true":"false";
		 
		 if(is_array($subMenuitems[$i])){														
			foreach($subMenuitems[$i] as $skey=>$sval){
				$subHtml .= "<li class='nav-item'>";
				if($subMelmnt == 'a')
					$subHtml .= "<a class='nav-link' href='{$subMenulnks[$i]}'>{$skey}</a>";
				else
					$subHtml .= "<button class='nav-link' aria-expanded='{$lnkAnc}' {$subMenuactn[$i]}>{$skey}</button>";
				
				$subHtml .= $this->generatelftSubMenu1($sval['navlabel'],$sval['navlink'],$sval['element'],$sval['action']);
				$subHtml .= "</li>";
			}			
		}else{
			$subHtml .= "<li class='nav-item'>";
			if($subMelmnt == 'a')
				$subHtml .= "<a class='nav-link' href='{$subMenulnks[$i]}'>{$subMenuitems[$i]}</a>";
			else
				$subHtml .= "<button class='nav-link' aria-expanded='{$lnkAnc}' {$subMenuactn[$i]}>{$subMenuitems[$i]}</button>";
			
			$subHtml .= "</li>";
		}
	 }
	 $subHtml .= "</ul>"; 
	return $subHtml;
 }
 
 function generatelftSubMenu1($subMenuitems=[],$subMenulnks=[],$subMelmnt,$subMenuactn=[]){
	 $subHtml = "<ul class='list-unstyled'>";
	 for($i=0; $i < count($subMenuitems); $i++){
		 $lnkAnc = ($i==0)?"true":"false";
		$subHtml .= "<li class='nav-item'>";
		if($subMelmnt == 'a')
			$subHtml .= "<a class='nav-link' href='{$subMenulnks[$i]}'>{$subMenuitems[$i]}</a>";
		else
			$subHtml .= "<button class='nav-link' aria-expanded='{$lnkAnc}' {$subMenuactn[$i]}>{$subMenuitems[$i]}</button>";
		$subHtml .= "</li>";
	 }
	$subHtml .= "</ul>";
	return $subHtml;
 }

 function actionButtons($btnitems=[]){
	$btns = "<div class='col'><div class='d-flex gap-3 justify-content-end'>";
	foreach($btnitems as $key=>$val){
		$jsaction = !empty($val['jsAction']) ? $val['jsAction'] : "";
		$btns .= "<button type='button' class='btn {$val['class']}' onclick='{$jsaction}'><i class='fa-solid {$val['icon']}'></i> {$key}</button>";
	}
	$btns .= "</div></div>";
	return $btns;
 }


 function actionlftButtons($btnitems=[]){
	$btns = "<div class='col'><div class='d-flex gap-3 justify-content-start'>";
	foreach($btnitems as $key=>$val){
		$jsaction = !empty($val['jsAction']) ? $val['jsAction'] : "";
		$btns .= "<button type='button' class='btn {$val['class']}' onclick='{$jsaction}'><i class='fa-solid {$val['icon']}'></i> {$key}</button>";
	}
	$btns .= "</div></div>";
	return $btns;
 }
}

$html = new htmlGenerator();