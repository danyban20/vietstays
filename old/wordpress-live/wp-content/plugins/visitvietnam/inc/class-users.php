<?php 

class vvUsers{

	public function __construct(){
	}

	public function get_actions($action = ''){
		if($action == 'delete_customer' && GET_Request('id') > 0){
			$this->delete_user(GET_Request('id'));
		}elseif($action == 'users_autocomplete'){
			$this->users_autocomplete();
		}elseif($action == 'login_as_host'){
			$this->login_as_host();
		}elseif($action == 'stop_impersonating_host'){
			$this->stop_impersonating_host();
		}
	}

	public function post_actions($action = ''){
		if($action == 'save_user'){
			$this->save_user();
		}
	}


	public function users_autocomplete(){
		global $wpdb;

		header('Content-Type: application/json');

		$search_term = GET_Request('q');

		$args 		= ['number' => 10];
		$m_query 	= [];

		array_push($m_query,array('key' => 'vv_user_level', 'value' => 'admin', 'compare' => 'NOT LIKE' ));

		if($search_term != ''){
			$args['search'] = '*' . esc_attr( $search_term ) . '*';
			array_push($m_query,array('key' => 'first_name', 'value' => trim($search_term), 'compare' => 'LIKE' ));
			array_push($m_query,array('key' => 'last_name', 'value' => trim($search_term), 'compare' => 'LIKE' ));
		}


		if(count($m_query) > 0) $args['meta_query'] = array_merge(array('relation' => 'OR'), $m_query);

		$args = [
		    'search' => '*'.$search_term.'*', // Add wildcard for partial matches
		    'search_columns' => ['user_email'], // Search specifically in the email column
		    'number' => 10
		];


		$query_user = new WP_User_Query($args);	

		$users_found = $query_user->get_results();

	    $result = [];

		if(count($users_found) > 0){
			foreach($users_found as $user){
				$firstname = get_user_meta($user->ID,'first_name',true);
				$lastname = get_user_meta($user->ID,'last_name',true);

				$html = '<div>';
				$html .= '<a href="#" data-firstname="'.$firstname.'" data-lastname="'.$lastname.'" data-user_id="'.$user->ID.'" data-email="'.$user->user_email.'" >';
				$html .= $user->user_email.' - '.$firstname.' '.$lastname;
				$html .= '</a>';
				$html .= '</div>';

				array_push($result, array('value' => $user->ID, 'firstname' => $firstname, 'lastname' => $lastname, 'text' => $user->user_email, 'html' => $html));
			}	
		}


	    // Reset post data
	    wp_reset_postdata();

		echo vv_jsonEncode($result);
			


		die();
	}

	public function login_as_host() {
		if ( ! vv_admin_can_impersonate_host() ) {
			setErrorMsg( 'You do not have permission to log in as a host.' );
			wp_redirect( vv_admin_url( 'partners' ) );
			die();
		}

		$host_id = intval( GET_Request( 'user_id' ) );
		$host    = $host_id > 0 ? get_user_by( 'ID', $host_id ) : false;

		if ( ! $host ) {
			setErrorMsg( 'Invalid host account.' );
			wp_redirect( vv_admin_url( 'partners' ) );
			die();
		}

		$user_level = get_user_meta( $host_id, 'vv_user_level', true );
		$is_host    = ( $user_level === 'partner' );
		if ( ! $is_host && class_exists( 'vvRoles' ) ) {
			$is_host = vvRoles::get_primary_backend_role( $host_id ) === vvRoles::ROLE_HOST;
		}

		if ( ! $is_host ) {
			setErrorMsg( 'This user is not a host account.' );
			wp_redirect( vv_admin_url( 'partners' ) );
			die();
		}

		$admin_id = get_current_user_id();
		if ( ! session_id() ) {
			session_start();
		}

		$_SESSION['vv_impersonator_id'] = $admin_id;

		wp_clear_auth_cookie();
		wp_set_current_user( $host_id );
		wp_set_auth_cookie( $host_id, true );
		do_action( 'wp_login', $host->user_login, $host );

		setSuccessMsg( 'You are now viewing the portal as ' . $host->display_name . '.' );
		wp_redirect( vv_admin_url( 'dashboard' ) );
		die();
	}

	public function stop_impersonating_host() {
		if ( ! session_id() ) {
			session_start();
		}

		$admin_id = vv_get_impersonator_user_id();
		$admin    = $admin_id > 0 ? get_user_by( 'ID', $admin_id ) : false;

		if ( ! $admin ) {
			unset( $_SESSION['vv_impersonator_id'] );
			setErrorMsg( 'No admin session found to return to.' );
			wp_redirect( vv_login_url( 'host' ) );
			die();
		}

		unset( $_SESSION['vv_impersonator_id'] );

		wp_clear_auth_cookie();
		wp_set_current_user( $admin_id );
		wp_set_auth_cookie( $admin_id, true );
		do_action( 'wp_login', $admin->user_login, $admin );

		setSuccessMsg( 'Returned to your admin account.' );
		wp_redirect( vv_admin_url( 'partners' ) );
		die();
	}

	public function save_user($redirect = true){

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

			$num_rows = gArrayItem($wpdb->get_row("SELECT COUNT(*) as num_rows FROM ".$wpdb->users."  ".$where),'num_rows');
			if($num_rows > 0){
				$error_msg .= '<div>Email address is already used.</div>';
			}
		}else{
			$error_msg .= '<div>Invalid Email address.</div>';
		}

		if($error_msg == ''){

			$userdata 					= [];
			$userdata['user_email'] 	= $email;
			$userdata['display_name'] 	= $firstname.' '.$lastname;
			$userdata['user_nicename'] 	= strtolower(str_replace(" ","-",$firstname.'-'.$lastname));
			$userdata['first_name'] 	= $firstname;
			$userdata['last_name'] 		= $lastname;

			if($user_id > 0){
				$userdata['ID'] = $user_id;

				wp_update_user($userdata);
				$success_msg = 'User Updated';

			}else{
				$random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );

				$userdata['user_login'] 	= $email;
				$userdata['user_pass']		= $random_password;
				$userdata['role'] 			= 'subscriber';

				$return = wp_insert_user($userdata);			
				if(!is_wp_error($return)){
					$user_id 		= $return;
					$success_msg 	= 'User Added';
				}else{
					$error_msg = $return->get_error_message();
				}

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

				if ( POST_request( 'admin_locale' ) !== '' && class_exists( 'vvI18n' ) ) {
					vvI18n::save_user_locale_preference( $user_id, POST_request( 'admin_locale' ) );
				}

				if ( POST_request( 'host_currency' ) !== '' ) {
					vv_save_user_host_currency( $user_id, POST_request( 'host_currency' ) );
				}

				if(trim(POST_request('newpassword')) != '') wp_set_password(trim(POST_request('newpassword')), $user_id);


				if($logged_user_role == 'administrator'){
					$assigned_roles = POST_request('user_roles');
					if ( is_array( $assigned_roles ) && count( $assigned_roles ) > 0 ) {
						vvRoles::set_user_roles( $user_id, $assigned_roles );
					} else {
						$single = POST_request('user_level');
						if ( $single !== '' ) {
							vvRoles::set_user_roles( $user_id, [ $single ] );
						} else {
							vvRoles::set_user_roles( $user_id, [ vvRoles::ROLE_GUEST ] );
						}
					}
				}

                if(POST_request('user_level') == 'ambassador' || vvRoles::user_has_role( $user_id, vvRoles::ROLE_AMBASSADOR ) ){

					$row = $wpdb->get_row("SELECT * FROM $wpdb->usermeta WHERE meta_key = 'vv_user_promo_code' AND  meta_value = '".POST_request('promo_code')."' AND user_id != ".$user_id,ARRAY_A);
					if(gArrayItem($row,'umeta_id') > 0){
						$error_msg .= "<div>Promo code is alread used.</div>";
					}else{
						update_user_meta($user_id,'vv_user_promo_code',POST_request('promo_code'));
						update_user_meta($user_id,'vv_user_promo_code_discount',POST_request('promo_code_discount'));
					}

				}
				if(POST_request('user_level') == 'partner' || vvRoles::user_has_role( $user_id, vvRoles::ROLE_HOST ) ){
					update_user_meta( $user_id, 'user_role', 'host' );
					if ( get_user_meta( $user_id, 'status', true ) === '' ) {
						update_user_meta( $user_id, 'status', 'active' );
					}

					$host_verification_status = POST_request('host_verification_status');
					if ( $host_verification_status === '' ) {
						$host_verification_status = 'pending';
					}
					update_user_meta($user_id,'host_verification_status', $host_verification_status );
					update_user_meta($user_id,'about_host_title',POST_request('about_host_title'));
					update_user_meta($user_id,'about_host',POST_request('about_host'));

					if ( in_array( $host_verification_status, [ 'verified', 'superhost-verified' ], true ) ) {
						update_user_meta( $user_id, 'status', 'active' );
						delete_user_meta( $user_id, 'vv_host_application_pending' );
					} elseif ( $host_verification_status === 'unverified' ) {
						update_user_meta( $user_id, 'status', 'rejected' );
					} elseif ( $host_verification_status === 'pending' ) {
						update_user_meta( $user_id, 'status', 'pending-approval' );
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

						update_user_meta($user_id, 'user_avatar', $attach_id);
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
				$redirect_url = vv_admin_url('users/edit').'?id='.$user_id;		
				
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
			$users = $wpdb->get_results("SELECT * FROM $wpdb->users WHERE user_email LIKE '".$email."' ");
			if(count($users) > 0){
				$user_id = $users[0]->ID;
				array_push($error_msg,'Email already used.');
			}
		}

		if(count($error_msg) > 0){
		}else{

			$user_id = wp_create_user($email,$password,$email);

			if($user_id > 0){


				update_user_meta($user_id,'first_name',$firstname);
				update_user_meta($user_id,'last_name',$lastname);

				vvRoles::add_user_role( $user_id, vvRoles::ROLE_GUEST );
				update_user_meta( $user_id, 'user_role', 'guest' );
				update_user_meta( $user_id, 'status', 'active' );

				$email_templates_class = new vvEmailTemplates;
				$email_templates_class->send_registrattion_email($user_id, $password);
				
				return ['status' => 1, 'user_id' => $user_id];	

			}

		}

		return ['status' => 0, 'error_msg' => $error_msg, 'user_id' => $user_id];
	}

	public function get_users($filter = array()){


		global $wpdb;


		$m_query = [];
		$args = [];
		$args['exclude'] = get_current_user_id();

		if(gArrayItem($filter,'srch') != ''){

			$search_term  = $filter['srch'];

			if(strpos($search_term,'@') !== false){
				$args['search'] = '*' . esc_attr( $search_term ) . '*';
				$args['search_columns'] = array( 
							                'user_email',
											);

			}else{
				array_push($m_query,array('key' => 'first_name', 'value' => trim($search_term), 'compare' => 'LIKE' ));
				array_push($m_query,array('key' => 'last_name', 'value' => trim($search_term), 'compare' => 'LIKE' ));
			}


		}

		if(gArrayItem($filter,'vv_user_level') != '' && gArrayItem($filter,'vv_user_level') != 'customer'){
			array_push($m_query,array('key' => 'vv_user_level', 'value' => trim($filter['vv_user_level']), 'compare' => 'LIKE' ));
		}elseif(gArrayItem($filter,'vv_user_level') == 'customer'){
			array_push($m_query,array('key' => 'vv_user_level', 'value' => 'customer', 'compare' => 'LIKE' ));
			array_push($m_query,array('key' => 'vv_user_level', 'compare' => 'NOT EXISTS' ));
		}else{
		}

		if(is_array(gArrayItem($filter,'ids'))){
			$args['include'] = $filter['ids'];
		}




		if(count($m_query) > 0) $args['meta_query'] = array_merge(array('relation' => 'OR'), $m_query);

		//$args['role'] = 'subscriber';

		//echo print_r_pre($args);

		$query_user = new WP_User_Query($args);	

		//echo print_r_pre($query_user);

		$total_users 	= $query_user->get_total();
		$users 			= $query_user->get_results();

		if(!is_array($users)) $users = array();

		$users_array = array_map(fn($user) => get_object_vars($user), $users);

		for($i = 0; $i < count($users_array); $i++){
			$users_array[$i]['firstname'] = get_user_meta($users_array[$i]['ID'],'first_name',true);
			$users_array[$i]['lastname'] = get_user_meta($users_array[$i]['ID'],'last_name',true);
		}


		return ['total_users' => $total_users, 'users' => $users_array];

	}

	public function get_admins( $filter = [] ) {
		$filter = array_merge(
			[
				'per_page'     => 100,
				'pgnum'        => 1,
				'return_total' => 1,
				'srch'         => '',
			],
			$filter
		);

		$admin_ids = [];

		$meta_args = [
			'fields'     => 'ID',
			'number'     => -1,
			'meta_query' => [
				[
					'key'     => 'vv_user_level',
					'value'   => 'admin',
					'compare' => 'LIKE',
				],
			],
		];

		$role_args = [
			'fields' => 'ID',
			'role'   => 'Administrator',
			'number' => -1,
		];

		$search_term = trim( gArrayItem( $filter, 'srch' ) );
		if ( $search_term !== '' ) {
			if ( strpos( $search_term, '@' ) !== false ) {
				$meta_args['search']         = '*' . esc_attr( $search_term ) . '*';
				$meta_args['search_columns'] = [ 'user_email' ];
				$role_args['search']         = '*' . esc_attr( $search_term ) . '*';
				$role_args['search_columns'] = [ 'user_email' ];
			} else {
				$name_query = [
					'relation' => 'OR',
					[
						'key'     => 'first_name',
						'value'   => $search_term,
						'compare' => 'LIKE',
					],
					[
						'key'     => 'last_name',
						'value'   => $search_term,
						'compare' => 'LIKE',
					],
				];
				$meta_args['meta_query'] = [
					'relation' => 'AND',
					[
						'key'     => 'vv_user_level',
						'value'   => 'admin',
						'compare' => 'LIKE',
					],
					$name_query,
				];
				$role_args['meta_query'] = $name_query;
			}
		}

		foreach ( [ $meta_args, $role_args ] as $query_args ) {
			$query = new WP_User_Query( $query_args );
			foreach ( $query->get_results() as $user_id ) {
				$admin_ids[] = intval( $user_id );
			}
		}

		$admin_ids = array_values( array_unique( array_filter( $admin_ids ) ) );

		if ( empty( $admin_ids ) ) {
			return [ 'total_users' => 0, 'users' => [] ];
		}

		$per_page = intval( $filter['per_page'] );
		$pgnum    = max( 1, intval( $filter['pgnum'] ) );
		$args     = [
			'include' => $admin_ids,
			'orderby' => 'display_name',
			'order'   => 'ASC',
		];

		if ( $per_page > 0 ) {
			$args['number'] = $per_page;
			$args['offset'] = ( $pgnum - 1 ) * $per_page;
		}

		$query_user = new WP_User_Query( $args );
		$users      = $query_user->get_results();

		if ( ! is_array( $users ) ) {
			$users = [];
		}

		$users_array = array_map( fn( $user ) => get_object_vars( $user ), $users );

		for ( $i = 0; $i < count( $users_array ); $i++ ) {
			$user_id = intval( $users_array[ $i ]['ID'] );
			$users_array[ $i ]['firstname'] = get_user_meta( $user_id, 'first_name', true );
			$users_array[ $i ]['lastname']  = get_user_meta( $user_id, 'last_name', true );

			if ( get_user_meta( $user_id, 'vv_user_level', true ) === '' && class_exists( 'vvRoles' ) ) {
				vvRoles::get_user_roles( $user_id );
			}
		}

		return [
			'total_users' => count( $admin_ids ),
			'users'       => $users_array,
		];
	}

	public function get_user($id = 0){
		global $wpdb;

		$user = $wpdb->get_row("SELECT * FROM ".$wpdb->users." WHERE ID = ".$id, ARRAY_A);
		if($user){
			$user['firstname'] = get_user_meta($user['ID'],'first_name',true);
			$user['lastname'] = get_user_meta($user['ID'],'last_name',true);
		}

		return $user;
	}

	public function count_users($user_id){
		global $wpdb;

		return intval(gArrayItem($wpdb->get_row("SELECT COUNT(*) as num_rows FROM vv_users WHERE user_id = ".$user_id),'num_rows'));
	}

	public function delete_user($user_id = 0){
		global $wpdb;
		require_once(ABSPATH.'wp-admin/includes/user.php');

		if($user_id == get_current_user_id()){
			setErrorMsg('You cannot delete your own account.');
			wp_redirect(vv_admin_url('customers'));
		}

		$user_level = get_user_meta($user_id,'vv_user_level',true);


		wp_delete_user($user_id);

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