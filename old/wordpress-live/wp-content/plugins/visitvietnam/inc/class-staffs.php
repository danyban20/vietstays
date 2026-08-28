<?php 

class vvStaffs{

	public function __construct(){
	}

	public function get_actions($action = ''){
		if($action == 'delete_customer' && GET_Request('id') > 0){
			$this->delete_staff(GET_Request('id'));
		}elseif($action == 'staffs_autocomplete'){
			$this->staffs_autocomplete();
		}elseif($action == 'resend_staff_welcome_email'){
			$this->resend_staff_welcome_email();
		}
	}

	public function post_actions($action = ''){
		if($action == 'save_staff'){
			$this->save_staff();
		}
	}


	public function staffs_autocomplete(){
		global $wpdb;

		header('Content-Type: application/json');

		$search_term = GET_Request('q');

		$args 		= ['number' => 10, 'exclude' => [get_current_user_id()]];
		//$args['exclude'] = [get_current_user_id()];
		$m_query 	= [];

		//array_push($m_query,array('key' => 'vv_user_level', 'value' => 'admin', 'compare' => 'NOT LIKE' ));
		array_push($m_query,array('key' => 'vv_user_level', 'value' => 'staff', 'compare' => 'LIKE' ));

		array_push($m_query,array('key' => 'vv_user_level', 'value' => 'staff', 'compare' => 'LIKE' ));

		if($search_term != ''){
			$args['search'] = '*' . esc_attr( $search_term ) . '*';
			array_push($m_query,array('key' => 'user_name', 'value' => trim($search_term), 'compare' => 'LIKE' ));
			array_push($m_query,array('key' => 'last_name', 'value' => trim($search_term), 'compare' => 'LIKE' ));
		}


		if(count($m_query) > 0) $args['meta_query'] = array_merge(array('relation' => 'OR'), $m_query);

		$args = [
		    'search' => '*'.$search_term.'*', // Add wildcard for partial matches
		    'search_columns' => ['user_email'], // Search specifically in the email column
		    'number' => 10,
		    'exclude' => [get_current_user_id()]
		];

		//echo print_r_pre($args);


		$query_staff = new WP_User_Query($args);	

		$staffs_found = $query_staff->get_results();

	    $result = [];

		if(count($staffs_found) > 0){
			foreach($staffs_found as $staff){
				$firstname = get_user_meta($staff->ID,'first_name',true);
				$lastname = get_user_meta($staff->ID,'last_name',true);
				$user_id = $staff->ID;

				$html = '<div>';
				$html .= '<a href="#" data-firstname="'.$firstname.'" data-lastname="'.$lastname.'" data-user_id="'.$staff->ID.'" data-email="'.$staff->user_email.'" >';
				$html .= $staff->user_email.' - '.$firstname.' '.$lastname;
				$html .= '</a>';
				$html .= '</div>';

                $staff_img       = get_user_meta($user_id,'staff_avatar',true);
                if($staff_img > 0){
                    $staff_img = wp_get_attachment_url($staff_img);
                }
                if($staff_img == ''){
                    $staff_img = get_avatar_url($user_id, 64 );
                }

				array_push($result, array('value' => $staff->ID, 'firstname' => $firstname, 'lastname' => $lastname, 'text' => $staff->user_email, 'html' => $html,'staff_img' => $staff_img));
			}	
		}


	    // Reset post data
	    wp_reset_postdata();

		echo vv_jsonEncode($result);
			


		die();
	}
	public function save_staff($redirect = true){

		global $wpdb, $logged_user_role;

		$user_id 		= intval(POST_Request('user_id'));
		$email 			= trim(POST_request('email'));
		$firstname 		= trim(POST_request('firstname'));
		$lastname 		= trim(POST_request('lastname'));

		$error_msg 		= '';
		$success_msg 	= '';
		$status 		= 'error';

		if(isEmailFormat($email)){

			$where = " WHERE user_email LIKE '%".strtolower($email)."%' ";

			if($user_id > 0){
				$where .= " AND ID != ".$user_id;
			}

			$num_rows = gArrayItem($wpdb->get_row("SELECT COUNT(*) as num_rows FROM ".$wpdb->staffs."  ".$where),'num_rows');
			if($num_rows > 0){
				$error_msg .= '<div>Email address is already used.</div>';
			}
		}else{
			$error_msg .= '<div>Invalid Email address.</div>';
		}

		if($error_msg == ''){

			$userdata 						= [];
			$userdata['user_email'] 		= $email;
			$userdata['display_name'] 		= $firstname.' '.$lastname;
			$userdata['user_nicename'] 		= strtolower(str_replace(" ","-",$firstname.'-'.$lastname));
			$userdata['first_name'] 		= $firstname;
			$userdata['last_name'] 			= $lastname;

			if($user_id > 0){
				$is_new = false;

				$userdata['ID'] = $user_id;

				wp_update_user($userdata);
				$success_msg = 'Staff Updated';

			}else{
				$random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );

				$userdata['user_login'] 	= $email;
				$userdata['user_pass']		= $random_password;
				$userdata['role'] 			= 'subscriber';

				$return = wp_insert_user($userdata);			
				if(!is_wp_error($return)){
					$is_new = true;
					$user_id 		= $return;
					$success_msg 	= 'Staff Added';
				}else{
					$error_msg = $return->get_error_message();
				}
				$staff_password = $random_password;
			}
			if($user_id > 0){

				$user_address = json_decode(get_user_meta($user_id,'user_address',true),true);
				if(!is_array($user_address)) $user_address = [];

				$user_address['prefix'] = trim(POST_request('prefix'));
				$user_address['suffix'] = trim(POST_request('suffix'));
				$user_address['company_name'] = trim(POST_request('company_name'));
				$user_address['title'] = trim(POST_request('title'));
				$user_address['web_url'] = trim(POST_request('web_url'));
				$user_address['address_1'] = trim(POST_request('address_1'));
				$user_address['address_2'] = trim(POST_request('address_2'));
				$user_address['city'] = trim(POST_request('city'));
				$user_address['country'] = trim(POST_request('country'));
				$user_address['state'] = trim(POST_request('state'));
				$user_address['postalcode'] = trim(POST_request('postalcode'));
				$user_address['phone_number'] = trim(POST_request('phone_number'));

				update_user_meta($user_id,'user_address',vv_jsonEncode($user_address));
				update_user_meta($user_id,'vv_user_host',POST_request('host_id'));


				if(trim(POST_request('newpassword')) != '') wp_set_password(trim(POST_request('newpassword')), $user_id);


				update_user_meta($user_id,'vv_user_level','staff');
				vvRoles::set_user_roles( $user_id, [ vvRoles::ROLE_OPERATIONS_STAFF, vvRoles::ROLE_GUEST ] );

				//die(POST_request('staff_position'));
				update_user_meta($user_id,'vv_staff_position',POST_request('staff_position'));
				if(POST_request('user_level') == 'ambassador'){

					$row = $wpdb->get_row("SELECT * FROM $wpdb->staffmeta WHERE meta_key = 'vv_user_promo_code' AND  meta_value = '".POST_request('promo_code')."' AND user_id != ".$user_id,ARRAY_A);
					if(gArrayItem($row,'umeta_id') > 0){
						$error_msg .= "<div>Promo code is alread used.</div>";
					}else{
						update_user_meta($user_id,'vv_user_promo_code',POST_request('promo_code'));
						update_user_meta($user_id,'vv_user_promo_code_discount',POST_request('promo_code_discount'));
					}

				}
				$file = gArrayItem($_FILES,'user_photo');


				if (gArrayItem($file,'error') !== UPLOAD_ERR_OK) {
				}else{

					require_once ABSPATH . 'wp-admin/includes/file.php';
					require_once ABSPATH . 'wp-admin/includes/media.php';
					require_once ABSPATH . 'wp-admin/includes/image.php';

					$upload_overrides = ['test_form' => false];

					// Upload file to media library
					$movefile = wp_handle_upload($file, $upload_overrides);
					if ($movefile && !isset($movefile['error'])) {
						$filename = $movefile['file'];
						$wp_filetype = wp_check_filetype($filename);
						$attachment = [
							'post_mime_type' => $wp_filetype['type'],
							'post_title'     => sanitize_file_name($filename),
							'post_content'   => '',
							'post_status'    => 'inherit'
						];

						$attach_id = wp_insert_attachment($attachment, $filename);
						$attach_data = wp_generate_attachment_metadata($attach_id, $filename);
						wp_update_attachment_metadata($attach_id, $attach_data);

						update_user_meta($user_id, 'staff_avatar', $attach_id);
					}
				}
				if($is_new){
					$this->send_new_staff_notification($user_id,$staff_password);
				}
			}
		}

		if($redirect){
			if($error_msg != '') 	setErrorMsg($error_msg);
			if($success_msg != '') 	setSuccessMsg($success_msg);
			if(get_current_user_id() == $user_id){
				$redirect_url = vv_admin_self_profile_url( $user_id );
			}else{
				$redirect_url = vv_admin_url('staffs/edit').'?id='.$user_id;		
				
			}
			wp_redirect($redirect_url);
			die();
		}else{
			return ['status' => $status, 'success_msg' => $success_msg, 'error_msg' => $error_msg, 'user_id' => $user_id];
		}

	}
	public function resend_staff_welcome_email(){
		$staff_id = GET_Request('id');
		$this->send_new_staff_notification($staff_id,'');
		setSuccessMsg('Welcome Email sent.');
		wp_redirect(vv_admin_url('staffs'));
		die();
	}
	public function send_new_staff_notification($staff_id = 0,$staff_password = 'Your password'){
		$email_templates_class = new vvEmailTemplates;
		$template 	= $email_templates_class->get_email_template_by_code('staff_welcome');
		$subject 	= trim(gArrayItem($template,'subject'));
		$body 		= gArrayItem($template,'body');

		$host_id = get_user_meta($staff_id,'vv_user_host',true);

		$staff = get_user_by('ID',$staff_id);

		$host = get_user_by('ID',$host_id);



		//echo $user->user_email;

		if($subject != '' && $body != '' && $host && $staff){

			$email_templates_class = new vvEmailTemplates;

			$staffdata = $staff->data;
 			//die
			$staff_position = ucwords(str_replace("_"," ",get_user_meta($staff->ID,'vv_staff_position',true)));
			$data = array();
			$data['FIRSTNAME'] 		= get_user_meta($staff->ID,'first_name',true);
			$data['LASTNAME'] 		= get_user_meta($staff->ID,'last_name',true);
			$data['USERNAME'] 		= $staffdata->user_login;
			$data['HOST_FIRSTNAME'] = get_user_meta($host->ID,'first_name',true);
			$data['HOST_LASTNAME'] 	= get_user_meta($host->ID,'last_name',true);
			$data['EMAIL'] 			= $user->user_email;
			$data['PASSWORD'] 		= $staff_password;
			$data['STAFF_POSITION'] = $staff_position;
			$data['LOGIN_LINK']  	= vv_admin_url();
			$data['TASK_LINK']  	= vv_admin_url('tasks/edit/').'?id='.$task_id;

			$subject 	= $email_templates_class->replace_email_tokens($subject,$data);
			$body 		= $email_templates_class->replace_email_tokens($body,$data);

			$sender = vv_get_config('email_sender_name').' <'.vv_get_config('email_sender').'>';


			send_email($sender, '', $staffdata->user_email, $subject, $body);

			//die($body);
			return $body;

		}else{
			die('Coul not send email.Invalid email message/staff');
		}

	}
	public function save_staff_xx($redirect = true){

		global $wpdb, $logged_user_role;

		$task_id		= intval(POST_Request('task_id'));
		$task_title 	= trim(POST_Request('task_title'));
		$staff_id 		= trim(POST_request('staff_id'));
		$firstname 		= trim(POST_request('firstname'));
		$lastname 		= trim(POST_request('lastname'));

		$error_msg 		= '';
		$success_msg 	= '';
		$status 		= 'error';

		if(isEmailFormat($email)){

			$where = " WHERE task_email LIKE '%".strtolower($email)."%' ";

			if($task_id > 0){
				$where .= " AND ID != ".$task_id;
			}

			$num_rows = gArrayItem($wpdb->get_row("SELECT COUNT(*) as num_rows FROM ".$wpdb->staffs."  ".$where),'num_rows');
			if($num_rows > 0){
				$error_msg .= '<div>Email address is already used.</div>';
			}
		}else{
			$error_msg .= '<div>Invalid Email address.</div>';
		}

		if($error_msg == ''){

			$taskdata = array(
				'post_title'    => $task_title,
				'post_content'  => $description,
				'post_status'   => 'publish', // 'draft', 'pending', 'private', 'publish'
				'post_author'   => get_current_user_id(),
				'post_type'     => 'task', // Change to your CPT slug
				'post_category' => array(0), // Not required for CPTs unless taxonomy is attached
			);
			if($task_id > 0){
				$userdata['ID'] = $task_id;

				wp_update_user($userdata);
				$success_msg = 'Staff Updated';

			}else{
				$random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );

				$userdata['user_login'] 	= $email;
				$userdata['user_pass']		= $random_password;
				$userdata['role'] 			= 'subscriber';

				$return = wp_insert_post($taskdata);			
			}
			if($user_id > 0){

				$user_address = json_decode(get_user_meta($user_id,'user_address',true),true);
				if(!is_array($user_address)) $user_address = [];

				$user_address['prefix'] = trim(POST_request('prefix'));
				$user_address['suffix'] = trim(POST_request('suffix'));
				$user_address['company_name'] = trim(POST_request('company_name'));
				$user_address['title'] = trim(POST_request('title'));
				$user_address['web_url'] = trim(POST_request('web_url'));
				$user_address['address_1'] = trim(POST_request('address_1'));
				$user_address['address_2'] = trim(POST_request('address_2'));
				$user_address['city'] = trim(POST_request('city'));
				$user_address['country'] = trim(POST_request('country'));
				$user_address['state'] = trim(POST_request('state'));
				$user_address['postalcode'] = trim(POST_request('postalcode'));
				$user_address['phone_number'] = trim(POST_request('phone_number'));

				update_user_meta($user_id,'user_address',vv_jsonEncode($user_address));
				update_user_meta($user_id,'vv_user_host',POST_request('host_id'));


				if(trim(POST_request('newpassword')) != '') wp_set_password(trim(POST_request('newpassword')), $user_id);


				update_user_meta($user_id,'vv_user_level','staff');
				vvRoles::set_user_roles( $user_id, [ vvRoles::ROLE_OPERATIONS_STAFF, vvRoles::ROLE_GUEST ] );

				//die(POST_request('staff_position'));
				update_user_meta($user_id,'vv_staff_position',POST_request('staff_position'));
				if(POST_request('user_level') == 'ambassador'){

					$row = $wpdb->get_row("SELECT * FROM $wpdb->staffmeta WHERE meta_key = 'vv_user_promo_code' AND  meta_value = '".POST_request('promo_code')."' AND user_id != ".$user_id,ARRAY_A);
					if(gArrayItem($row,'umeta_id') > 0){
						$error_msg .= "<div>Promo code is alread used.</div>";
					}else{
						update_user_meta($user_id,'vv_user_promo_code',POST_request('promo_code'));
						update_user_meta($user_id,'vv_user_promo_code_discount',POST_request('promo_code_discount'));
					}

				}
				$file = gArrayItem($_FILES,'user_photo');


				if (gArrayItem($file,'error') !== UPLOAD_ERR_OK) {
				}else{

					require_once ABSPATH . 'wp-admin/includes/file.php';
					require_once ABSPATH . 'wp-admin/includes/media.php';
					require_once ABSPATH . 'wp-admin/includes/image.php';

					$upload_overrides = ['test_form' => false];

					// Upload file to media library
					$movefile = wp_handle_upload($file, $upload_overrides);
					if ($movefile && !isset($movefile['error'])) {
						$filename = $movefile['file'];
						$wp_filetype = wp_check_filetype($filename);
						$attachment = [
							'post_mime_type' => $wp_filetype['type'],
							'post_title'     => sanitize_file_name($filename),
							'post_content'   => '',
							'post_status'    => 'inherit'
						];

						$attach_id = wp_insert_attachment($attachment, $filename);
						$attach_data = wp_generate_attachment_metadata($attach_id, $filename);
						wp_update_attachment_metadata($attach_id, $attach_data);

						update_user_meta($user_id, 'staff_avatar', $attach_id);
					}
				}
			}
		}

		if($redirect){
			if($error_msg != '') 	setErrorMsg($error_msg);
			if($success_msg != '') 	setSuccessMsg($success_msg);
			if(get_current_user_id() == $user_id){
				$redirect_url = vv_admin_self_profile_url( $user_id );
			}else{
				$redirect_url = vv_admin_url('staffs/edit').'?id='.$user_id;		
				
			}
			wp_redirect($redirect_url);
			die();
		}else{
			return ['status' => $status, 'success_msg' => $success_msg, 'error_msg' => $error_msg, 'user_id' => $user_id];
		}

	}

	public function register($data){

		global $wpdb;

		$user_id = 0;

		$email 		= trim(gArrayItem($data,'email'));
		$firstname 	= trim(gArrayItem($data,'firstname'));
		$lastname 	= trim(gArrayItem($data,'lastname'));
		$password 	= trim(gArrayItem($data,'password'));

		if($password == '') $password = wp_generate_password();

		$error_msg = array();

		if(!isEmailFormat($email)){
			array_push($error_msg,'Invalid Email.');
		}else{
			$staffs = $wpdb->get_results("SELECT * FROM $wpdb->staffs WHERE user_email LIKE '".$email."' ");
			if(count($staffs) > 0){
				$user_id = $staffs[0]->ID;
				array_push($error_msg,'Email already used.');
			}
		}

		if(count($error_msg) > 0){
		}else{

			$user_id = wp_create_staff($email,$password,$email);

			if($user_id > 0){


				update_user_meta($user_id,'user_name',$firstname);
				update_user_meta($user_id,'last_name',$lastname);

			

				$email_templates_class = new vvEmailTemplates;
				$email_templates_class->send_registrattion_email($user_id, $password);
				
				return ['status' => 1, 'user_id' => $user_id];	

			}

		}

		return ['status' => 0, 'error_msg' => $error_msg, 'user_id' => $user_id];
	}

	public function get_staffs($filter = array()){


		global $wpdb;


		$m_query = [];
		$args = [];
		//$args['exclude'] = get_current_user_id();

		if(gArrayItem($filter,'srch') != ''){

			$search_term  = $filter['srch'];

			if(strpos($search_term,'@') !== false){
				$args['search'] = '*' . esc_attr( $search_term ) . '*';
				$args['search_columns'] = array( 
							                'user_email',
											);

			}else{
				array_push($m_query,array('key' => 'user_name', 'value' => trim($search_term), 'compare' => 'LIKE' ));
				array_push($m_query,array('key' => 'last_name', 'value' => trim($search_term), 'compare' => 'LIKE' ));
			}


		}
		if ( gArrayItem( $filter, 'host' ) !== '' && gArrayItem( $filter, 'host' ) !== null ) {
			array_push( $m_query, array( 'key' => 'vv_user_host', 'value' => trim( $filter['host'] ), 'compare' => 'LIKE' ) );
		}

		if(gArrayItem($filter,'vv_user_level') != '' && gArrayItem($filter,'vv_user_level') != 'staff'){
			array_push($m_query,array('key' => 'vv_user_level', 'value' => trim($filter['vv_user_level']), 'compare' => 'LIKE' ));
		}elseif(gArrayItem($filter,'vv_user_level') == 'customer'){
			array_push($m_query,array('key' => 'vv_user_level', 'value' => 'customer', 'compare' => 'LIKE' ));
			array_push($m_query,array('key' => 'vv_user_level', 'compare' => 'NOT EXISTS' ));
		}elseif(gArrayItem($filter,'vv_user_level') == 'staff'){
			array_push($m_query,array('key' => 'vv_user_level', 'value' => 'staff', 'compare' => 'LIKE' ));
		}else{
		}

		if(is_array(gArrayItem($filter,'ids'))){
			$args['include'] = $filter['ids'];
		}




		if(count($m_query) > 0) $args['meta_query'] = array_merge(array('relation' => 'AND'), $m_query);

		//$args['role'] = 'subscriber';

		//echo print_r_pre($args);

		$query_staff = new WP_User_Query($args);	

		//echo print_r_pre($query_staff);

		$total_staffs 	= $query_staff->get_total();
		$staffs 			= $query_staff->get_results();

		if(!is_array($staffs)) $staffs = array();

		$staffs_array = array_map(fn($staff) => get_object_vars($staff), $staffs);

		for($i = 0; $i < count($staffs_array); $i++){
			$staffs_array[$i]['firstname'] = get_user_meta($staffs_array[$i]['ID'],'user_name',true);
			$staffs_array[$i]['lastname'] = get_user_meta($staffs_array[$i]['ID'],'last_name',true);
		}


		return ['total_staffs' => $total_staffs, 'staffs' => $staffs_array];

	}

	public function get_admins($filter = []){
        $filter = array_merge($filter,['per_page' => 100, 'pgnum' => 1, 'return_total' => 0, 'vv_user_level' => 'admin']);
        $data   = $this->get_staffs($filter);

        for($i = 0; $i < count($data['staffs']); $i++){

        	$firstname = get_user_meta($data['staffs'][$i]['ID'],'user_name',true);
        	$lastname = get_user_meta($data['staffs'][$i]['ID'],'last_name',true);

        	$data['staffs'][$i]['firstname'] = $firstname;
        	$data['staffs'][$i]['lastname'] = $lastname;

        }


        return $data;

	}

	public function get_staff($id = 0){
		global $wpdb;

		$staff = $wpdb->get_row("SELECT * FROM ".$wpdb->staffs." WHERE ID = ".$id, ARRAY_A);
		if($staff){
			$staff['firstname'] = get_user_meta($staff['ID'],'user_name',true);
			$staff['lastname'] = get_user_meta($staff['ID'],'last_name',true);
		}

		return $staff;
	}

	public function count_staffs($user_id){
		global $wpdb;

		return intval(gArrayItem($wpdb->get_row("SELECT COUNT(*) as num_rows FROM vv_staffs WHERE user_id = ".$user_id),'num_rows'));
	}

	public function delete_staff($user_id = 0){
		global $wpdb;
		require_once(ABSPATH.'wp-admin/includes/staff.php');

		if($user_id == get_current_user_id()){
			setErrorMsg('You cannot delete your own account.');
			wp_redirect(vv_admin_url('customers'));
		}

		$user_level = get_user_meta($user_id,'vv_user_level',true);


		wp_delete_staff($user_id);

		if($user_level == 'admin'){
			setSuccessMsg('Admin Deleted Successfully');
			wp_redirect(vv_admin_url('admin'));
		}else{
			setSuccessMsg('Customer Deleted Successfully');
			wp_redirect(vv_admin_url('customers'));
		}
	}


	public function send_welcome_message($user_id = 0){

	}
}