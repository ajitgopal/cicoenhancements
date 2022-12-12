<?php

defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Assignments Controller
 * 
 * This Class will perform the action related to listing all Assignments Information. 
 * 
 * @package     Assignments
 *
 */
class Assignments extends MX_Controller {
	
    function __construct(){
               
         parent::__construct();
         $this->load->model('Assignments/Hrcon_job_model');  
          $this->load->model('Customers/Staffacc_location_model');
         $this->load->model('Contacts/Staffacc_contact_model');
    }
    
  
    public function get_user_assignments_list_api()
    {
        $response_data 	= array();
      
        $pagination     = get_pagination_details();
        $keyword        = filter_data($this->input->post('keyword'));
        $include_shifts = filter_data($this->input->post('include_shifts'));
        $field_decode   = filter_data($this->input->post('fields'));
        $field_concat   = json_decode(stripslashes($field_decode));
		$field_concat['assignment_details'][] = "ts_layout_pref";		
		
        $sort           = filter_data($this->input->post('sort')); 
        $order_by       = filter_data($this->input->post('orderby'));
        $groupby        = filter_data($this->input->post('groupby'));
        $orderby 		= 'hrcon_jobs.'.$order_by;
     
        $fields         = get_field_array($field_concat['assignment_details'],'hrcon_jobs'); //common_function_helper
        $fields         .= ','.get_field_array($field_concat['job_location'],'staffacc_location'); //common_function_helper
        $fields         .= ','.get_field_array($field_concat['job_reportsto'],'staffacc_contact'); //common_function_helper
        
        $keyword = trim($keyword);
        if(trim($include_shifts) == 'Y'){
            $assignmentsa = $this->Hrcon_job_model->get_assignments_with_shifts($this->user_id, $pagination['limit'], $pagination['offset'], $fields, $keyword, $orderby, $sort,$groupby);
            $assignmentsb = $this->Hrcon_job_model->get_assignments($this->user_id, $pagination['limit'], $pagination['offset'], $fields, $keyword, $orderby, $sort);
            
            foreach ($assignmentsa["data"] as $aindex => $assignmenta) {
                foreach ($assignmentsb["data"] as $bindex => $assignmentb) {
                    if($assignmentb["sno"] == $assignmenta["sno"])
                    {
                        unset($assignmentsb["data"][$bindex]);
                    }
                }
            }

            $assignmentsc = array_merge($assignmentsa["data"],$assignmentsb["data"]);
            $assignments['data'] = $assignmentsc;
	    $assignments['total_records'] = count($assignmentsc);
        }else{
            $assignments    = $this->Hrcon_job_model->get_assignments($this->user_id, $pagination['limit'], $pagination['offset'], $fields, $keyword, $orderby, $sort);
        
        }
        $assignments    = replace_null_with_empty_string($assignments);
        if(count($assignments['data'])>0)
        {
            $response_data['data']             = $assignments['data'];
            $response_data['total_records']    = $assignments['total_records'];
        }else{
             $response_data['data']             = '';
             $response_data['total_records']    = 0;
             $response_data['message']          = _get_message('no_record_data');
             $response_data['error']            = FALSE;
        }
       
        
        send_response($response_data);  
    }

   public function get_user_assignments_details_api()
    {
        $response_data 	= array();
	$joblocation_details=array();
        $jobreportsto_details=array();
        $field_decode   = filter_data($this->input->post('fields'));
        $field_concat   = json_decode(stripslashes($field_decode)); 
        $sno            = filter_data($this->input->post('sno'));
        $assg_fields    = get_field_array($field_concat['assignment_details'],'hrcon_jobs'); //common_function_helper
        $assg_fields   .= ',staffacc_location.ltype';  // Mandatory Field
        $assg_fields   .= ',hrcon_jobs.endclient';  // Mandatory Field
		$assg_fields   .= ',hrcon_jobs.ts_layout_pref';  // Timesheet Layout Preference
        $joblocation_fields      = get_field_array($field_concat['job_location'],'staffacc_location'); //common_function_helper
        $joblocation_fields     .= ','.get_field_array($field_concat['customer'],'staffacc_cinfo'); //common_function_helper
        $jobreportsto_fields    = get_field_array($field_concat['job_reportsto'],'staffacc_contact'); //common_function_helper
        $jobreportsto_fields   .= ',manage.name as suffix';  // Mandatory Field
        $shift_sno = filter_data($this->input->post('shift_sno'));
        $assignment_details    = $this->Hrcon_job_model->get_assignment_detail($sno, $this->user_id, $assg_fields,$shift_sno);

        $assignment_details    = replace_null_with_empty_string($assignment_details);

        
        if(!empty($assignment_details['endclient'])){
             $location_sno =$assignment_details['endclient']; 
              
            if($assignment_details['ltype'] == 'con'){
               
                $joblocation_details   = $this->Staffacc_location_model->get_job_location_by_contact($location_sno, $joblocation_fields);
            }else{ 
                $joblocation_details   = $this->Staffacc_location_model->get_job_location_by_customer($location_sno, $joblocation_fields);
            }
        }
        if(!empty($assignment_details['manager'])){
            $manager_sno  = $assignment_details['manager'];
            $jobreportsto_details   = $this->Staffacc_contact_model->get_staffacc_contact_details($manager_sno, $jobreportsto_fields);
            
        }
        
        //to replace null values with empty 
        if(count($assignment_details)>0)
              $assignment_details    = replace_null_with_empty_string($assignment_details);
        
        if(count($joblocation_details)>0)      
                 $joblocation_details    = replace_null_with_empty_string($joblocation_details);
        
        if(count($jobreportsto_details)>0)  
               $jobreportsto_details    = replace_null_with_empty_string($jobreportsto_details);
        
        if(count($assignment_details)>0 || count($joblocation_details)>0 || count($jobreportsto_details)>0 )
        {
            $response_data['data']['assignment_details']   = $assignment_details;
            $response_data['data']['joblocation_details']  = $joblocation_details;
            $response_data['data']['jobreportsto_details'] = $jobreportsto_details;
        }else{
             $response_data['data']             = '';
            
             $response_data['message']          = _get_message('no_record_data');
             $response_data['error']            = FALSE;
        }
       
        send_response($response_data);  
    }
}