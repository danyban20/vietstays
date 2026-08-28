<?php

function send_email($sender = '', $replyto = '',$receiver = '', $subject = '', $message = '', $show_flash = true){
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= "From: ".$sender." " . "\r\n";
	if($replyto != '') $headers .= "Reply-to: <".$replyto.">" . "\r\n";

	
	if(wp_mail($receiver, $subject , $message, $headers)){
		if ( $show_flash ) {
			setSuccessMsg('EMail Sent.');
		}
	}else{
		setErrorMsg('Email sending failed.');
	} 

}
if(!function_exists('gArrayItem')){
    function gArrayItem($array,$index){
        if(is_array($array) && array_key_exists($index,$array)){
            return $array[$index];
        }else{
            return '';
        }
    }
}   



function get_curl_request($url){
	$ch = curl_init();
	 
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_ENCODING, '');	
	
	$data = curl_exec($ch);
	curl_close($ch);
	return $data; 
}

function generate_random_password($n = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
 
    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
 
    return $randomString;
}

if(!function_exists('curPageURL')){
	function curPageURL() {
		$pageURL = 'http';
		if (array_key_exists('HTTPS',$_SERVER) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";
		//if ($_SERVER["SERVER_PORT"] != "80") {
			//$pageURL .= $_SERVER['HTTP_HOST'].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		//} else {
			$pageURL .= $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
		//}
		return $pageURL;
	}
}




if(!function_exists('print_r_pre')){
    function print_r_pre($data){
        return '<pre>'.print_r($data,true).'</pre>';
    }
}

if(!function_exists('get_signup_data')){
    function get_signup_data(){
        $signup_data = get_array_item($_SESSION,'signup_data');
        if(!is_array($signup_data)) $signup_data = array();
        
        return $signup_data;        
    }
}
if(!function_exists('set_signup_data')){
    function set_signup_data($data){
        $_SESSION['signup_data'] = $data;
    }
}

if(!function_exists('isEmailFormat')){
    function isEmailFormat($email){
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }        
        return true;
    }
}

function GET_Request($index){
    return gArrayItem($_GET,$index);
}

function POST_Request($index){
    return gArrayItem($_POST,$index);
}

if(!function_exists('setSuccessMsg')){
	function setSuccessMsg($msg = ''){
		if ( trim( (string) $msg ) === '' ) {
			return;
		}
	    $_SESSION['SuccessMsg'] .= '<div>'.$msg.'</div>';
	}
}

if(!function_exists('vv_flash_success_html')){
	function vv_flash_success_html( $raw = '', $show_close = true ) {
		$parts = [];
		if ( preg_match_all( '/<div>(.*?)<\/div>/s', (string) $raw, $matches ) ) {
			foreach ( $matches[1] as $part ) {
				$part = trim( wp_strip_all_tags( $part ) );
				if ( $part !== '' ) {
					$parts[] = $part;
				}
			}
		}
		if ( count( $parts ) === 0 ) {
			$text = trim( wp_strip_all_tags( (string) $raw ) );
			if ( $text !== '' ) {
				$parts[] = $text;
			}
		}

		if ( count( $parts ) === 0 ) {
			return '';
		}

		$message = esc_html( implode( ' ', $parts ) );
		$close   = $show_close ? ' <span class="btnCloseNotification">&times;</span>' : '';

		return '<div class="alert alert-success mt-2 mb-2">' . $message . $close . '</div>';
	}
}

if(!function_exists('vv_echo_flash_script')){
	function vv_echo_flash_script( $html = '' ) {
		if ( trim( (string) $html ) === '' ) {
			return;
		}
		echo '<script type="text/javascript">vvShowNotification(' . wp_json_encode( $html ) . ');</script>';
	}
}

if(!function_exists('showSuccessMsg')){
	function showSuccessMsg($is_js = true, $show_close = true){
	    if(gArrayItem($_SESSION,'SuccessMsg') != ''){
	    	$html = vv_flash_success_html( $_SESSION['SuccessMsg'], $show_close );
	        $_SESSION['SuccessMsg'] = '';

	    	if ( $html === '' ) {
	    		return;
	    	}

	    	if($is_js){
	    		vv_echo_flash_script( $html );
	    	}else{
		        echo $html;
	    	}
	    }
	}
}

if(!function_exists('setErrorMsg')){
	function setErrorMsg($msg = ''){
	    $_SESSION['ErrorMsg'] = $msg;
	}
}

if(!function_exists('showErrorMsg')){
	function showErrorMsg($is_js = true, $show_close = true){
	    if(gArrayItem($_SESSION,'ErrorMsg') != ''){
	    	$close = $show_close ? ' <span class="btnCloseNotification">&times;</span>' : '';
	    	$html  = '<div class="alert alert-danger mt-2 mb-2">' . esc_html( $_SESSION['ErrorMsg'] ) . $close . '</div>';

	    	if($is_js){
	    		vv_echo_flash_script( $html );
	    	}else{
		        echo $html;
	    	}

	        $_SESSION['ErrorMsg'] = '';
	    }
	}
}

if(!function_exists('showAlertMessages')){
	function showAlertMessages(){
		$html = '';

		if ( gArrayItem( $_SESSION, 'SuccessMsg' ) !== '' ) {
			$html .= vv_flash_success_html( $_SESSION['SuccessMsg'], true );
			$_SESSION['SuccessMsg'] = '';
		}

		if ( gArrayItem( $_SESSION, 'ErrorMsg' ) !== '' ) {
			$html .= '<div class="alert alert-danger mt-2 mb-2">' . esc_html( $_SESSION['ErrorMsg'] ) . ' <span class="btnCloseNotification">&times;</span></div>';
			$_SESSION['ErrorMsg'] = '';
		}

		if ( $html !== '' ) {
			echo 'vvShowNotification(' . wp_json_encode( $html ) . ');';
		}
	}
}


function vv_slugify($string) {
    // Convert to lowercase
    $string = strtolower($string);
    
    // Replace spaces and underscores with a dash
    $string = preg_replace('/[\s_]+/', '-', $string);
    
    // Remove all non-alphanumeric characters except dashes
    $string = preg_replace('/[^a-z0-9\-]/', '', $string);
    
    // Remove multiple consecutive dashes
    $string = preg_replace('/-+/', '-', $string);
    
    // Trim leading and trailing dashes
    $string = trim($string, '-');
    
    return $string;
}

function vv_init_configs(){
	global $vv_configs;

	$vv_configs['airport_pickup_cost'] 		= 10;
	$vv_configs['scooter_rental_cost'] 		= 15;
	$vv_configs['email_template_codes'] 	= [
		'user_registration',
		'user_booking_confirmation',
		'admin_booking_confirmation',
		'staff_welcome',
		'user_new_task_notification',
		'user_task_updated_notification',
		'user_task_new_comment_notifica',
		'user_new_task_comment_notification',
		'user_task_comment_updated_notification',
		'host_application_submitted',
		'host_application_under_review',
		'host_application_approved',
		'host_application_rejected',
		'host_welcome',
		'host_activation_reminder',
	];
	$vv_configs['email_sender']				= 'visitVietnam@wiise.no';
	$vv_configs['email_sender_name']		= 'Visit Vietnam';
	$vv_configs['user_levels']				= vvRoles::admin_assignable_roles();
}

if(!function_exists('vv_jsonEncode')){
	function vv_jsonEncode($data){
		return json_encode($data,JSON_UNESCAPED_UNICODE);
	}
}


function vv_get_config($item = ''){
	global $vv_configs;

	return gArrayItem($vv_configs,$item);
}

function vv_plugins_url(){
	return plugins_url().'/visitvietnam/';
}

function vv_admin_url($append = ''){
	return get_bloginfo('url').'/vv-admin/'.$append;
}

function vv_admin_user_edit_url( $user_id, $user_level = '' ) {
	$user_id = intval( $user_id );
	if ( $user_level === '' ) {
		$user_level = get_user_meta( $user_id, 'vv_user_level', true );
	}
	if ( $user_level === '' && class_exists( 'vvRoles' ) && vvRoles::get_primary_backend_role( $user_id ) === vvRoles::ROLE_ADMIN ) {
		$user_level = 'admin';
	} elseif ( $user_level === '' ) {
		$user_level = 'customer';
	}

	$routes = [
		'partner'    => 'partners',
		'admin'      => 'admins',
		'ambassador' => 'ambassadors',
		'staff'      => 'staffs',
		'customer'   => 'users',
	];

	$folder = gArrayItem( $routes, $user_level, 'users' );

	return vv_admin_url( $folder . '/edit' ) . '?id=' . $user_id;
}

function vv_admin_account_url() {
	return vv_admin_url( 'account' );
}

function vv_admin_self_profile_url( $user_id = 0 ) {
	$user_id = intval( $user_id );
	if ( $user_id <= 0 ) {
		$user_id = get_current_user_id();
	}
	if ( $user_id > 0 && $user_id === get_current_user_id() ) {
		return vv_admin_account_url();
	}
	return vv_admin_user_edit_url( $user_id );
}

function vv_admin_account_edit_view( $user_id = 0 ) {
	$user_id = intval( $user_id );
	if ( $user_id <= 0 ) {
		$user_id = get_current_user_id();
	}

	$user = get_user_by( 'ID', $user_id );
	if ( ! $user ) {
		return 'users/edit';
	}

	$routes = [
		'administrator' => 'admins/edit',
		'partner'       => 'partners/edit',
		'ambassador'    => 'ambassadors/edit',
		'staff'         => 'staffs/edit',
	];

	return gArrayItem( $routes, vv_get_user_role( $user ), 'users/edit' );
}

function vv_base_url(){
	return get_bloginfo('url');
}

function vv_admin_contact_email() {
	return sanitize_email( get_option( 'admin_email' ) );
}

function vv_users_url($append = ''){
	return get_bloginfo('url').'/vv-users/'.$append;	
}

/**
 * Unified sign-in page URL with optional portal pre-selection (guest|host).
 */
function vv_login_url( $portal = 'guest', $redirect_to = '' ) {
	$url = home_url( '/login/' );
	$portal = sanitize_key( $portal );
	if ( $portal === 'host' ) {
		$url = add_query_arg( 'portal', 'host', $url );
	}
	if ( trim( (string) $redirect_to ) !== '' ) {
		$url = add_query_arg( 'redirect_to', rawurlencode( $redirect_to ), $url );
	}
	return $url;
}

function vv_is_impersonating_host() {
	if ( ! session_id() ) {
		return false;
	}
	return intval( gArrayItem( $_SESSION, 'vv_impersonator_id' ) ) > 0;
}

function vv_get_impersonator_user_id() {
	if ( ! vv_is_impersonating_host() ) {
		return 0;
	}
	return intval( $_SESSION['vv_impersonator_id'] );
}

function vv_admin_can_impersonate_host() {
	if ( vv_is_impersonating_host() ) {
		return false;
	}
	if ( ! is_user_logged_in() ) {
		return false;
	}
	return vv_get_user_role( wp_get_current_user() ) === 'administrator';
}

function vv_login_as_host_url( $host_user_id ) {
	return vv_admin_url( 'partners' ) . '?' . http_build_query(
		[
			'vv_action' => 'login_as_host',
			'user_id'   => intval( $host_user_id ),
		]
	);
}

function vv_stop_impersonating_host_url() {
	return vv_admin_url( 'dashboard' ) . '?vv_action=stop_impersonating_host';
}

function vv_sanitize_guest_redirect( $redirect = '' ) {
	$redirect = trim( (string) $redirect );
	$default  = vv_users_url();

	if ( $redirect === '' ) {
		return $default;
	}

	if ( preg_match( '#/(login|vv-users/login|vv-admin/login)#i', $redirect ) ) {
		return $default;
	}

	if ( strpos( $redirect, '/vv-users' ) === 0 ) {
		return home_url( $redirect );
	}

	$parsed = wp_parse_url( $redirect );
	$home   = wp_parse_url( home_url() );

	if (
		isset( $parsed['host'], $home['host'] )
		&& strtolower( $parsed['host'] ) === strtolower( $home['host'] )
		&& isset( $parsed['path'] )
		&& strpos( $parsed['path'], '/vv-users' ) === 0
	) {
		return $redirect;
	}

	return $default;
}

function vv_users_gatekeeper(){
	if ( intval( get_current_user_id() ) === 0 ) {
		$return = gArrayItem( $_SERVER, 'REQUEST_URI' );
		$login_url = vv_login_url( 'guest' );
		if ( $return !== '' && stripos( $return, '/login' ) === false ) {
			$login_url = vv_login_url( 'guest', $return );
		}
		wp_redirect( $login_url );
		die();
	}
}

function vv_get_booking_by_num( $booking_num ) {
	global $wpdb;

	$booking_num = trim( (string) $booking_num );
	if ( $booking_num === '' ) {
		return [];
	}

	$row = $wpdb->get_row(
		$wpdb->prepare( "SELECT * FROM vv_bookings WHERE booking_num = %s LIMIT 1", $booking_num ),
		ARRAY_A
	);

	if ( ! is_array( $row ) || gArrayItem( $row, 'ID' ) <= 0 ) {
		$booking_id = vv_get_booking_id_from_num( $booking_num );
		if ( $booking_id > 0 ) {
			$booking_class = new vvBookings;
			$row = $booking_class->get_booking( $booking_id );
		}
	}

	return is_array( $row ) ? $row : [];
}

function vv_guest_can_view_booking( $booking, $user_id = 0 ) {
	$user_id = intval( $user_id );
	if ( $user_id <= 0 ) {
		$user_id = get_current_user_id();
	}

	if ( ! is_array( $booking ) || gArrayItem( $booking, 'ID' ) <= 0 || $user_id <= 0 ) {
		return false;
	}

	if ( intval( gArrayItem( $booking, 'user_id' ) ) === $user_id ) {
		return true;
	}

	$user = get_user_by( 'ID', $user_id );
	if ( ! $user ) {
		return false;
	}

	$booking_email = strtolower( trim( gArrayItem( $booking, 'email' ) ) );
	return $booking_email !== '' && strtolower( $user->user_email ) === $booking_email;
}

function vv_booking_has_practical_access( $booking ) {
	$status = strtolower( trim( gArrayItem( $booking, 'status' ) ) );
	return in_array( $status, [ 'paid', 'confirmed', 'completed' ], true );
}

function vv_admin_gatekeeper(){
	if ( intval( get_current_user_id() ) === 0 && strpos( gArrayItem( $_SERVER, 'REQUEST_URI' ), '/login' ) === false ) {
		wp_redirect( vv_login_url( 'host' ) );
		die();
	}

	if ( intval( get_current_user_id() ) > 0 && strpos( gArrayItem( $_SERVER, 'REQUEST_URI' ), '/login' ) === false ) {
		if ( ! vv_host_can_access_admin( get_current_user_id() ) && ! vv_is_impersonating_host() ) {
			wp_logout();
			setErrorMsg( 'Your host account is not activated yet. Please use the activation link from your approval email, or contact ' . vv_admin_contact_email() . '.' );
			wp_redirect( vv_login_url( 'host' ) );
			die();
		}
	}
}

function vv_is_host_email_verified( $user_id = 0 ) {
	$user_id = intval( $user_id );
	if ( $user_id <= 0 ) {
		return false;
	}
	return (bool) intval( get_user_meta( $user_id, 'vv_email_verified', true ) );
}

function vv_host_can_access_admin( $user_id = 0 ) {
	$user_id = intval( $user_id );
	if ( $user_id <= 0 ) {
		return false;
	}

	$user = get_user_by( 'ID', $user_id );
	if ( ! $user ) {
		return false;
	}

	$backend_role = vv_get_user_role( $user );
	if ( $backend_role !== 'partner' ) {
		return true;
	}

	if ( vv_get_user_account_role( $user_id ) !== 'host' ) {
		return true;
	}

	$status = vv_get_user_account_status( $user_id );
	if ( $status === 'active' && vv_is_host_email_verified( $user_id ) ) {
		return true;
	}

	$host_verification = get_user_meta( $user_id, 'host_verification_status', true );
	if ( in_array( $host_verification, [ 'verified', 'superhost-verified' ], true )
		&& $status === 'active'
		&& vv_is_host_email_verified( $user_id ) ) {
		return true;
	}

	return false;
}


function vv_get_user($user_id){
	$users_class = new vvUsers;

	return $users_class->get_user($user_id);
}

function vv_get_user_role($logged_user){
	if ( ! $logged_user || ! isset( $logged_user->ID ) ) {
		return 'customer';
	}

	$primary = vvRoles::get_primary_backend_role( $logged_user->ID );

	// Preserve legacy sidebar / route checks used across admin views.
	if ( $primary === vvRoles::ROLE_ADMIN ) {
		return 'administrator';
	}
	if ( $primary === vvRoles::ROLE_HOST ) {
		return 'partner';
	}
	if ( $primary === vvRoles::ROLE_OPERATIONS_STAFF ) {
		return 'staff';
	}
	if ( $primary === vvRoles::ROLE_SALES_SUPPORT ) {
		return 'sales_support';
	}
	if ( $primary === vvRoles::ROLE_AMBASSADOR ) {
		return 'ambassador';
	}
	if ( $primary === vvRoles::ROLE_HOST_AGENT ) {
		return 'host_agent';
	}

	return 'customer';
}

function vv_get_user_roles( $user_id = 0 ) {
	if ( $user_id <= 0 ) {
		$user_id = get_current_user_id();
	}
	return vvRoles::get_user_roles( $user_id );
}

function vv_get_user_account_role( $user_id = 0 ) {
	$user_id = intval( $user_id );
	if ( $user_id <= 0 ) {
		return '';
	}

	$role = get_user_meta( $user_id, 'user_role', true );
	if ( $role !== '' ) {
		return $role;
	}

	$legacy = get_user_meta( $user_id, 'vv_user_level', true );
	if ( $legacy === 'partner' ) {
		return 'host';
	}

	return $legacy;
}

function vv_get_user_account_status( $user_id = 0 ) {
	$user_id = intval( $user_id );
	if ( $user_id <= 0 ) {
		return '';
	}

	$status = get_user_meta( $user_id, 'status', true );
	if ( $status !== '' ) {
		return $status;
	}

	if ( get_user_meta( $user_id, 'vv_user_level', true ) === 'partner' ) {
		$host_status = get_user_meta( $user_id, 'host_verification_status', true );
		if ( $host_status === 'pending' || $host_status === '' ) {
			return 'pending-approval';
		}
		if ( in_array( $host_status, [ 'verified', 'superhost-verified' ], true ) ) {
			return 'active';
		}
		if ( $host_status === 'unverified' ) {
			return 'rejected';
		}
	}

	return '';
}

function vv_get_user_account_status_label( $status = '' ) {
	return vvHostApplications::user_status_label( $status );
}

function vv_user_has_role( $role, $user_id = 0 ) {
	if ( $user_id <= 0 ) {
		$user_id = get_current_user_id();
	}
	return vvRoles::user_has_role( $user_id, $role );
}

function vv_user_can( $capability, $user_id = 0 ) {
	if ( $user_id <= 0 ) {
		$user_id = get_current_user_id();
	}
	return vvRoles::user_can( $user_id, $capability );
}

function vv_get_foods($filter = array()){
	$food_class = new vvFoods;
	return $food_class->get_foods($filter);
}
function vv_get_food_categories($filter = array()){
	$food_class = new vvFoods;
	return $food_class->get_food_categories($filter);
}


function vv_get_cities($filter = array()){
	$city_class = new vvCities;
	return $city_class->get_cities($filter);
}

function vv_get_neighbourhoods($filter = array()){
	$neighbourhood_class = new vvNeighbourhoods;
	return $neighbourhood_class->get_neighbourhoods($filter);
}

function vv_get_neighbourhood($id = 0){
	$neighbourhood_class = new vvNeighbourhoods;
	return $neighbourhood_class->get_neighbourhood($id);
}

function vv_get_building_gallery_items( $building_id, $include_usage = false ) {
	$neighbourhood_class = new vvNeighbourhoods();
	return $neighbourhood_class->get_building_gallery_items( $building_id, $include_usage );
}

function vv_get_building_location_label( $building_id ) {
	$building_id = intval( $building_id );
	if ( $building_id <= 0 ) {
		return '';
	}

	$building = vv_get_neighbourhood( $building_id );
	if ( empty( $building['ID'] ) ) {
		return '';
	}

	$district_id = intval( gArrayItem( $building, 'post_parent' ) );
	if ( $district_id <= 0 ) {
		return '';
	}

	$district = vv_get_district( $district_id );
	$parts    = [];
	if ( ! empty( $district['post_title'] ) ) {
		$parts[] = $district['post_title'];
	}

	$city_id = intval( get_post_meta( $district_id, 'city', true ) );
	if ( $city_id <= 0 && function_exists( 'get_field' ) ) {
		$city_id = intval( get_field( 'city', $district_id ) );
	}
	if ( $city_id > 0 ) {
		global $wpdb;
		$city_name = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_title FROM {$wpdb->posts} WHERE ID = %d AND post_type = 'city' AND post_status = 'publish'",
				$city_id
			)
		);
		if ( $city_name ) {
			$parts[] = $city_name;
		}
	}

	return implode( ', ', $parts );
}


function vv_get_neighbourhood_display_name($neighbourhood_id){
    $neighbourhood_class = new vvNeighbourhoods;
    $neighbourhoods = $neighbourhood_class->get_neighbourhoods(['per_page' => 'all', 'parent' => 'all']);
	$neighbourhood_name = '';
    foreach($neighbourhoods as $neighbourhood){
        if($neighbourhood['ID'] == $neighbourhood_id){
            $neighbourhood_name = $neighbourhood['post_title'];
            if($neighbourhood['post_parent'] > 0){
            	$parent_neighbourhood = $neighbourhood_class->get_neighbourhood($neighbourhood['post_parent']);
            	if(gArrayItem($parent_neighbourhood,'post_title') != ''){
            		$neighbourhood_name = $neighbourhood_name.', '.$parent_neighbourhood['post_title'];
            	}
            }
        }
    }
    return $neighbourhood_name;
}

function vv_get_districts($filter = array()){
	$district_class = new vvDistricts;
	return $district_class->get_districts($filter);
}

function vv_get_district($id = 0){
	$district_class = new vvDistricts;
	return $district_class->get_district($id);
}


function vv_get_district_display_name($district_id){
    $district_class = new vvDistricts;
    $districts = $district_class->get_districts(['per_page' => 'all', 'parent' => 'all']);
	$district_name = '';
    foreach($districts as $district){
        if($district['ID'] == $district_id){
            $district_name = $district['post_title'];
            if($district['post_parent'] > 0){
            	$parent_district = $district_class->get_district($district['post_parent']);
            	if(gArrayItem($parent_district,'post_title') != ''){
            		$district_name = $district_name.', '.$parent_district['post_title'];
            	}
            }
        }
    }
    return $district_name;
}

function vv_get_districts_select_options($default = 0, $districts = []){
	if(count($districts) == 0) $districts = vv_get_districts();

    foreach($districts as $district){ 
        $neighbourhoods = gArrayItem($district,'neighbourhoods');
        if(count($neighbourhoods) > 0){
            foreach($neighbourhoods as $neighbourhood){
		        $html .= '<option value="'.$neighbourhood['ID'].'" data-district_label="'.$neighbourhood['post_title'].', '.$district['post_title'].'" ';
		        $html .= ($default == $neighbourhood['ID']) ? 'selected' : ''; 
		        $html .= ' >'.$district['post_title'].': '.$neighbourhood['post_title'].'</option>';
            }
        }
    } 

	return $html;
}

function vv_get_cleaners_checklists($filter = array()){
	$cleaners_checklist_class = new vvCleanersChecklists;
	return $cleaners_checklist_class->get_cleaners_checklists($filter);
}
function vv_get_apartments($filter = array()){
	$apartment_class = new vvApartments;
	return $apartment_class->get_apartments($filter);
}

function vv_get_apartment($id = 0){
	$apartment_class = new vvApartments;
	$apartment = $apartment_class->get_apartment($id);
	if ( ! is_array( $apartment ) ) {
		return $apartment;
	}
	if ( class_exists( 'vvI18n' ) && strpos( gArrayItem( $_SERVER, 'REQUEST_URI' ), '/vv-admin' ) === false ) {
		return vvI18n::merge_apartment_localized( $apartment );
	}
	return $apartment;
}

function vv_get_facilities($filter = array()){
	$facility_class = new vvFacilities;
	return $facility_class->get_facilities($filter);
}

function vv_get_security_features($filter = array()){
	$security_features_class = new vvSecurityFeatures;
	return $security_features_class->get_security_features($filter);
}

function vv_get_features($filter = array()){
	$features_class = new vvFeatures;
	return $features_class->get_features($filter);
}

function security_features(){
	
}

function vv_handle_uploaded_image($file_input_name, $post_id) {
    // Load necessary WordPress media functions
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    // Upload the image and attach it to the post
    $attachment_id = media_handle_upload($file_input_name, $post_id);

    if (is_wp_error($attachment_id)) {
    	$error_message = $attachment_id->get_error_message();
    	//echo $error_message;
        return false;
    }

    return $attachment_id;
}


function vv_get_image_array($attachment_id) {
    if (!wp_attachment_is_image($attachment_id)) {
        return false;
    }

    $image = get_post($attachment_id);

    return [
        'ID'          => $attachment_id,
        'alt'         => get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
        'title'       => $image->post_title,
        'caption'     => $image->post_excerpt,
        'description' => $image->post_content,
        'url'         => wp_get_attachment_url($attachment_id),
        'width'       => null,
        'height'      => null,
        'sizes'       => [],
    ] + vv_get_image_sizes($attachment_id);
}

function vv_get_image_sizes($attachment_id) {
    $sizes = [];
    $intermediate_sizes = get_intermediate_image_sizes();

    foreach ($intermediate_sizes as $size) {
        $image = wp_get_attachment_image_src($attachment_id, $size);
        if ($image) {
            $sizes[$size] = $image[0]; // URL
        }
    }

    // Also get original image size
    $full = wp_get_attachment_image_src($attachment_id, 'full');
    return [
        'width' => $full[1],
        'height' => $full[2],
        'sizes' => $sizes
    ];
}




function vv_fee(){
	// visit vietnam fee in %
	return 5;
}
function vv_site_currency(){
	$currency = vv_get_config('site_currency');
	if($currency == '') $currency = 'NOK';

	return $currency;
}

function vv_get_site_currencies(){
	return ['USD','VND','NOK','EUR'];
}

function vv_can_edit_user_currency( $user_id = 0 ) {
	if ( class_exists( 'vvI18n' ) ) {
		return vvI18n::can_edit_user_locale( $user_id );
	}

	$user_id = intval( $user_id );
	if ( $user_id <= 0 ) {
		return false;
	}

	global $logged_user_role;
	if ( get_current_user_id() === $user_id ) {
		return true;
	}
	return $logged_user_role === 'administrator';
}

function vv_get_user_host_currency( $user_id = 0 ) {
	$user_id = intval( $user_id );
	if ( $user_id <= 0 ) {
		$user_id = get_current_user_id();
	}

	$currency = get_user_meta( $user_id, 'vv_host_currency', true );
	if ( $currency === '' ) {
		$currency = 'VND';
	}

	return in_array( $currency, vv_get_site_currencies(), true ) ? $currency : 'VND';
}

function vv_save_user_host_currency( $user_id, $currency ) {
	$user_id = intval( $user_id );
	if ( $user_id <= 0 || ! vv_can_edit_user_currency( $user_id ) ) {
		return false;
	}

	$currency = strtoupper( trim( (string) $currency ) );
	if ( ! in_array( $currency, vv_get_site_currencies(), true ) ) {
		return false;
	}

	update_user_meta( $user_id, 'vv_host_currency', $currency );

	if ( class_exists( 'vvSettings' ) ) {
		$settings = new vvSettings();
		$settings->update_currency( $currency, false );
	}

	return true;
}

function vv_number_format($number, $show_currency = false) {

	$number = floatval($number);
	
	$currency = vv_get_config('site_currency');
	if($currency == '') $currency = 'NOK';

	if($currency == 'VND'){

	}elseif($currency == 'NOK'){
		$formatted =  number_format($number,2,',',' ');
		$formatted = str_replace(",00","",$formatted);
	}else{
		$formatted = number_format($number);
	}
	
	return ($show_currency) ? $formatted.' '.$currency : $formatted;

}

function vv_currency_to_decimal($input){
	$currency = vv_site_currency();
    // Trim and normalize spaces
    $input = trim(preg_replace('/\s+/', ' ', $input));

    // Remove currency symbols and common suffixes like hyphens
    $input = preg_replace('/[-₫kr,\s]+$/u', '', $input);

    if ($currency === 'NOK') {
        // Remove all spaces (thousands separator)
        $input = preg_replace('/[\s\x{00A0}]/u', '', $input);

        if (strpos($input, ',--') !== false) {
        	$input = str_replace(',-', '.00', $input);
    	}
        // If there's a comma, assume it's a decimal separator
        if (strpos($input, ',') !== false) {
            $input = str_replace(',', '.', $input);
        }

        $number = floatval($input);
        return number_format($number, 2, '.', '');    }

    if ($currency === 'VND') {
        $input = str_replace(['.', '₫', ' '], '', $input);
        $number = intval($input);
        return $number;
    }

    // Default fallback
    return null;
}


function vv_get_booking_num($booking){
	if(gArrayItem($booking,'booking_num') != '' ){
		return gArrayItem($booking,'booking_num');
	}else{
		$booking_class = new vvBookings;
		return $booking_class->generate_booking_num($booking);
	}
}

function vv_get_booking_id_from_num($booking_num){

	$booking_id = intval(substr($booking_num,6,strlen($booking_num)));
	return $booking_id;
}

function vv_get_booking_link($booking){
	return vv_users_url().'?booking='.vv_get_booking_num($booking);
}

function vv_get_booking_tables_html($booking = []){
	ob_start();

	$booking_class = new vvBookings;
    $booking_items = $booking_class->get_booking_items($booking['ID']);

    $apartment_class 	= new vvApartments;
    $apartment 			= $apartment_class->get_apartment($booking['apartment_id']);


    $check_in_date  	= gArrayItem($booking,'check_in_date');
    $check_out_date  	= gArrayItem($booking,'check_out_date');

	$checkInDate 		= new DateTime($check_in_date);
	$checkOutDate 		= new DateTime($check_out_date);


	$interval 		= $checkInDate->diff($checkOutDate);
	$days 			= $interval->days;


    ?>
    <div class="table-responsive table--no-card mt-4 m-b-30">
        <table class="table table-borderless table-striped table-earning items_list">
            <thead>
                <tr>
                    <th class="text-left">Apartment</th>
                    <th style="width:50px">Adults</th>
                    <th style="width:100px">Children</th>
                    <th style="width:100px">Check-in/out</th>
                    <th class="text-center" style="width:100px">Nights</th>
                    <th class="text-right" style="width:100px">Daily Price</th>
                    <th class="text-right" style="width:100px">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $subtotal = 0;
                $services = [];
                $foods = [];
                foreach($booking_items as $item){
                	if(gArrayItem($item,'item_type') == 'booking-date'){
	                    $subtotal += ($item['price'] * $item['qty']);
	                    $dates = explode("-",$item['name']);

	                    $label = vv_get_date_range_text(gArrayItem($dates,0),gArrayItem($dates,1));


	                    ?>
	                    <tr class="booking_<?php echo $item['item_id'] ?>" >
		                    <td><?php echo stripslashes(gArrayItem($booking,'apartment_name')) ?></td>
		                    <td><?php echo gArrayItem($booking,'adults') ?></td>
		                    <td><?php echo gArrayItem($booking,'children') ?></td>
		                    <td><?php echo $label ?></td>
	                        <td class="text-center"><?php echo $item['qty'] ?></td>
	                        <td class="text-right"><?php echo vv_number_format($item['price'],false) ?></td>
	                        <td class="text-right"><?php echo vv_number_format($item['price'] * $item['qty'],true) ?></td>
	                    </tr>
	                    <?php 
	                }elseif(gArrayItem($item,'item_type') == 'service'){
	                	$services[] = $item;
	                }elseif(gArrayItem($item,'item_type') == 'food'){
	                	$foods[] = $item;
	                }
                }


                ?>
            </tbody>
        </table>
    </div>
    <div class="booking_totals">
    	<div class="border mb-2 p-3 float-right">
	        <table class="">
	            <tr>
	                <td class="text-left"><b>Apartment:</b> </td>
	                <td>&nbsp;</td>
	                <td class="text-right"><?php echo vv_number_format($subtotal,true) ?></td>
	            </tr>
	            <?php
	            $has_discount = false;
	            if(gArrayItem($booking,'campaign_discount') > 0){
	            	$campaign_discount 		= gArrayItem($booking,'campaign_discount');
	            	$campaign_discount_desc = gArrayItem($booking,'campaign_discount_desc');
	            	$subtotal -= $campaign_discount;
	            	?>
		            <tr>
		                <td class="text-left">Campaign Discount: </td>
		                <td>&nbsp;</td>
		                <td class="text-right"><?php echo vv_number_format($campaign_discount,true) ?></td>
		            </tr>
	            	<?php
	            	$has_discount = true;
	            }
	            ?>
	            <?php
	            if(gArrayItem($booking,'basic_discount') > 0){
	            	$basic_discount 		= gArrayItem($booking,'basic_discount');
	            	$basic_discount_val 	= $subtotal * ($basic_discount/100);
	            	$subtotal -= $basic_discount_val;
	            	?>
		            <tr>
		                <td class="text-left">Extended Stay Discount: </td>
		                <td class="text-right"><?php echo $basic_discount ?>%</td>
		                <td class="text-right"><?php echo vv_number_format($basic_discount_val,true) ?></td>
		            </tr>
	            	<?php
	            	$has_discount = true;
	            }
	            if(gArrayItem($booking,'promo_code_discount') > 0){
	            	$promo_code_discount 		= gArrayItem($booking,'promo_code_discount');
	            	$promo_code_discount_val 	= $subtotal * ($promo_code_discount/100);
	            	$subtotal -= $promo_code_discount_val;
	            	?>
		            <tr>
		                <td class="text-left">Promo Code Discount: </td>
		                <td class="text-right"><?php echo gArrayItem($booking,'promo_code').' ('.$promo_code_discount.')' ?>%</td>
		                <td class="text-right"><?php echo vv_number_format($promo_code_discount_val,true) ?></td>
		            </tr>
	            	<?php
	            	$has_discount = true;
	            }
	            if($has_discount){
	            	?>
		            <tr>
		                <td class="text-left">Apartment Subtotal: </td>
		                <td>&nbsp;</td>
		                <td class="text-right"><?php echo vv_number_format($subtotal,true) ?></td>
		            </tr>
	            	<?php
	            }
	            if(gArrayItem($booking,'booking_fee') > 0){
	            	$booking_fee 		= gArrayItem($booking,'booking_fee');
	            	$booking_fee_val 	= $subtotal * ($booking_fee/100);
	            	$subtotal += $booking_fee_val;
	            	?>
		            <tr>
		                <td class="text-left">Booking Fee: </td>
		                <td class="text-right"><?php echo $booking_fee ?>%</td>
		                <td class="text-right"><?php echo vv_number_format($booking_fee_val,true) ?></td>
		            </tr>
	            	<?php

	            }
	            ?>
	            <tr>
	                <td class="text-left"><b>Booking Subtotal:</b> </td>
		            <td>&nbsp;</td>
	                <td class="text-right"><b><?php echo vv_number_format($subtotal,true) ?></b></td>
	            </tr>
	        </table>
	        <div style="clear: both;"></div>
	    </div>
    </div>
    <div class="booking_totals">
        <?php 
        if(count($services) > 0 || count($foods) > 0){
        	?>
        	<div class="border mb-4 p-3 float-right">
		        <table class="">
		        	<?php 
		        	if(count($services) > 0){
		        		?>
			            <tr>
			                <td class="text-left" colspan="2"><b>Services</b>: </td>
			            </tr>
			            <?php
			            foreach($services as $service){
			            	$amount = $service['qty'] * $service['price'];
			            	$subtotal += $amount;
			            	?>
			            	<tr>
			            		<td><?php echo $service['name'] ?></td>
			            		<td class="text-right"><?php echo $service['qty'] ?></td>
			            		<td class="text-right"><?php echo vv_number_format($amount,true) ?></td>
			            	</tr>
			            	<?php
			            }
			        }
		            if(count($foods) > 0){
		            	?>
			            <tr>
			                <td class="text-left" colspan="2"><b>Foods</b>: </td>
			            </tr>
		            	<?php
		            	foreach($foods as $food){
			            	$amount = $food['qty'] * $food['price'];
			            	$subtotal += $amount;
		            		?>
			            	<tr>
			            		<td><?php echo $food['name'] ?></td>
			            		<td class="text-right"><?php echo $food['qty'] ?></td>
			            		<td class="text-right"><?php echo vv_number_format($amount,true) ?></td>
			            	</tr>
		            		<?php
		            	}
		            }
			        ?>
		            <tr>
		            	<td colspan="3">&nbsp;</td>
		            </tr>
		            <tr>
		                <td><b>Total Order:</b> </td>
		                <td>&nbsp;</td>
		                <td class="text-right"><b><?php echo vv_number_format($subtotal,true) ?></b></td>
		            </tr>
		        </table>
		        <div style="clear: both;"></div>
		    </div>
        	<?php
        }
        ?>
    </div>
	<?php	
	return ob_get_clean();
}


if(!function_exists('vv_get_date_range_text')){
	function vv_get_date_range_text($start = '', $end = ''){
	    $start 	= new DateTime($start);
	    $end 	= new DateTime($end);

		if($start->format("Y") != $end->format("Y")){
			$label = $start->format("j M Y").' - '.$end->format("j M Y");
		}else{
			$label = $start->format("j M").' - '.$end->format("j M");
		}
		return $label;	
	}
}

if(!function_exists('vv_get_apartment_images')){
	function vv_get_apartment_images($apartment = []){
		$images = json_decode(gArrayItem($apartment,'images'),true);
		if(!is_array($images)) $images = array();


		for($i = 0; $i < count($images); $i++){
			for($a = 0; $a < (count($images) - 1); $a++){
				if(gArrayItem($images[$a],'order') > gArrayItem($images[$a+1],'order')){
					$tmp = $images[$a];
					$images[$a] = $images[$a+1];
					$images[$a+1] = $tmp;
				}
			}
		}

		return $images;	
	}
}

if ( ! function_exists( 'vv_count_listing_photos' ) ) {
	/**
	 * Total photos for publish validation: uploaded apartment images + selected building gallery (max 3).
	 */
	function vv_count_listing_photos( $apartment = [] ) {
		$images   = vv_get_apartment_images( $apartment );
		$apt_ids  = [];
		foreach ( $images as $img ) {
			$id = intval( gArrayItem( $img, 'image_id' ) );
			if ( $id > 0 ) {
				$apt_ids[ $id ] = true;
			}
		}

		$gallery_ids = json_decode( gArrayItem( $apartment, 'building_gallery_json' ), true );
		if ( ! is_array( $gallery_ids ) ) {
			$gallery_ids = [];
		}

		$building_count = 0;
		foreach ( $gallery_ids as $gid ) {
			$gid = intval( $gid );
			if ( $gid > 0 && ! isset( $apt_ids[ $gid ] ) ) {
				$building_count++;
			}
		}

		return count( $images ) + $building_count;
	}
}

if(!function_exists('vv_get_apartment_thumbnail')){
	function vv_get_apartment_thumbnail($apartment){
		$images = json_decode(gArrayItem($apartment,'images'),true);
		if(count($images) > 0){
			$img = gArrayItem($images[0],'thumb');
			if($img == '') $img = vv_get_image_placeholder();
			return $img;
		}else{
			return '';
		}
	}
}

if(!function_exists('vv_get_image_placeholder')){
	function vv_get_image_placeholder(){
		return vv_plugins_url().'assets/img/no-image.jpg';
	}
}

if(!function_exists('vv_get_apartment_url')){
	function vv_get_apartment_url($apartment = []){
		/*
		if(gArrayItem($apartment,'post_id') > 0){
			return get_the_permalink($apartment['post_id']);
		}
		*/
		$slug = gArrayItem($apartment,'url_slug');
		if($slug == '') $slug = gArrayItem($apartment,'ID');
		//return '#';
		return get_bloginfo('url').'/apartment/'.$slug;
	}
}

if(!function_exists('vv_get_app_admin_title')){
	function vv_get_app_admin_title(){
		return 'Visit Vietnam Admin';
	}
}

function vv_admin_meta_title( $page_title = '' ) {
	return vv_get_app_admin_title() . ' | ' . vv__( $page_title );
}

function vv_show_page_title_bar($data = []) {

	$back_link = gArrayItem($data,'back_link');
	$back_txt = gArrayItem($data,'back_txt');
	$add_link = gArrayItem($data,'add_link');
	$add_txt = gArrayItem($data,'add_txt');
	$add_attr = gArrayItem($data,'add_attr');
	$pg_title = gArrayItem($data,'pg_title');
	?>
	<div class="row">
	    <div class="col-md-9">
	        <h3 class="mb-4"><?php echo esc_html( vv__( $pg_title ) ); ?></h3>
	    </div>
	    <div class="col-md-3 text-right">
	        <?php if( $back_link != '' && $back_txt != ''){ ?>
	            <a href="<?php echo esc_url( $back_link ); ?>" class="btn btn-sm btn-secondary"><?php echo esc_html( vv__( $back_txt ) ); ?></a>
	        <?php } ?>
	        <?php if( $add_link != '' && $add_txt != ''){ ?>
	            <a href="<?php echo esc_url( $add_link ); ?>" class="btn btn-sm btn-primary" <?php echo $add_attr ?> ><?php echo esc_html( vv__( $add_txt ) ); ?></a>
	        <?php } ?>
	    </div>
	</div>
	<?php 
}



if(!function_exists('vv_get_status_name')){
	function vv_get_status_name($status = 0){
		if($status == 1) return vv__( 'Inactive' );
		else return vv__( 'Active' );
	}
}

if(!function_exists('vv_get_status_name_text')){
	function vv_get_status_name_text($status = ''){
		$status = str_replace("-"," ",$status);
		$status = str_replace("_"," ",$status);
		$status = ucwords($status);
		return vv__( $status );
	}
}


function vv_clean_phone_number($phone){
    if(strpos($phone,'(') !== false && strpos($phone,')') !== false){
        $phone = '+1'.preg_replace('/[^0-9]/', '',$phone); 
    }else{
        $phone = str_replace("(","",$phone); 
        $phone = str_replace(")","",$phone); 
        $phone = str_replace(" ","",$phone); 
        $phone = str_replace("-","",$phone); 
    }
    
    return $phone;
}


	function vv_booking_check_promo_code($filter = []){
		global $wpdb;

		$promocode = gArrayItem($filter,'promocode');

		$return 	= gArrayItem($filter,'return');

		$found 		= false;
		$user_id 	= 0;
		$discount 	= 0;

		$row = $wpdb->get_row("SELECT * FROM vv_promocodes WHERE UCASE(code) = '".strtoupper($promocode)."' ",ARRAY_A);
		if(gArrayItem($row,'ID') > 0){

			if ( gArrayItem( $row, 'status' ) === 'active' ) {
				if(gArrayItem($filter,'apartment_id')){
					$apartment_ids = json_decode($row['apartment_ids'],true);
					if(!is_array($apartment_ids)) $apartment_ids = [];
					if(count($apartment_ids) > 0){
						if(in_array($filter['apartment_id'],$apartment_ids,true)){
							$found = true;
							$apartment = vv_get_apartment($filter['apartment_id']);
							if(gArrayItem($apartment,'ID')){
								if(gArrayItem($apartment,'promocode_discount') > 0){
									$row['discount'] = $apartment['promocode_discount'];
								}
							}
						}
					}else{
						$found 		= true;
					}
				}else{
					$found 		= true;
				}
			}


			if($found == true){
				if ( class_exists( 'vvConversions' ) ) {
					$conv_check = vvConversions::validate_promo_code( $row );
					if ( is_wp_error( $conv_check ) ) {
						$found = false;
					}
				}
				$user_id 	= gArrayItem($row,'ambassador_id');
				$discount  	= gArrayItem($row,'discount');
			}
		}

		if($return){
			return ['found' => $found, 'user_id' => $user_id, 'discount' => $discount];
		}else{
			header("Content-Type: application/json");
			echo json_encode(['found' => $found, 'user_id' => $user_id, 'discount' => $discount]);
		}

		die();
	}


function vv_custom_apartment_rewrite() {
    add_rewrite_rule(
        '^apartment/([^/]+)/?$', // match /apartment/5
        'index.php?pagename=apartment&id=$matches[1]', // pass it to the apartment page with id
        'top'
    );

    add_rewrite_rule(
        '^host/([^/]+)/?$', // match /apartment/5
        'index.php?pagename=host&id=$matches[1]', // pass it to the apartment page with id
        'top'
    );

}
add_action('init', 'vv_custom_apartment_rewrite');

function vv_add_query_vars($vars) {
    $vars[] = 'id';
    $vars[] = 'host_slug';
    $vars[] = 'city_id';
    $vars[] = 'district';
    return $vars;
}
add_filter('query_vars', 'vv_add_query_vars');



function vv_basic_discount_name(){
	return 'Extended Stay Discount';
}

function vv_get_google_map_api_key(){
	return 'AIzaSyD4V8eoEHB-CrMpCgF30MBjv8lEnLmgR8U';
	//return 'AIzaSyDvsWYbgdU-eIiXdSWaVZsL8vKSChljTiY';
}

function vv_get_booking_pricing_data($apartment_id = 0, $check_in_date, $check_out_date){

	$apartment_class = new vvApartments;

	$discount_label = '';
	$discount_val = '';
	$apartment = $apartment_class->get_apartment($apartment_id);

	//echo $apartment_id;
	//echo print_r_pre($apartment);

	$pricing = json_decode(gArrayItem($apartment,'pricing'),true);
	if(!is_array($pricing)) $pricing = [];

    $discounts = $apartment_class->get_discounts(gArrayItem($apartment,'ID'));

	$checkInDate 	= new DateTime($check_in_date);
	$checkOutDate 	= new DateTime($check_out_date);


	$interval 		= $checkInDate->diff($checkOutDate);
	$days 			= $interval->days;

	$totalCost 		= 0;

	//echo $checkInDate;

	//echo print_r_pre($pricing);

	$period = new DatePeriod($checkInDate, new DateInterval('P1D'), $checkOutDate);

	$dprices 				= [];
	$campaign_discount 		= 0;
	$campaign_discount_desc	= [];
	$basic_discount 		= 0;

	$dprices2 = [];
	foreach ($period as $date) {
	    $dayOfWeek 	= (int) $date->format('w'); // 0 (Sunday) to 6 (Saturday)

	    $cost 		= gArrayItem($apartment,'price_daily');

	    if($dayOfWeek == 5 || $dayOfWeek == 6){
	    	$addon = gArrayItem($pricing,'addon_days2');
	    	if($addon > 0){
	    		$cost = $cost + ($cost * ($addon/100));
	    	}
	    }

	    //echo $dayOfWeek.' - '.$cost.'<br>';

	    $found 		= false;
	    foreach($dprices as $i => $v){
	    	if($i == $cost){
	    		$found = true;
	    		$dprices[$i]++;
	    	}
	    }
	    if(!$found) $dprices[$cost] = 1;

	    $dprices2[$date->format('Y-m-d')] = $cost;

	    //CAMPAIGN DISCOUNT
	    $c_discount = 0;
	    $date1 = $date->format('Y-m-d');
	    foreach($discounts as $d){
	    	if($date1 >= gArrayItem($d,'datestart')  && $date1 <= gArrayItem($d,'dateend')  && gArrayItem($d,'discount') > 0){
	    		if($c_discount != ''){ // MAKE SURE IT GET'S THE LOWEST DISCOUNT
	    			if($c_discount > $d['discount']){
	    				$c_discount = $d['discount']; 
	    			}
	    		}else{
	    			$c_discount = $d['discount'];
	    		}
	    	}
	    }
	    if($c_discount > 0){
	    	$campaign_discount += ($cost * ($c_discount/100));
	    	$campaign_discount_desc[] = ['date' => $date1, 'discount' => $c_discount];
	    }
	    //foreach($)
	}

	if($days >= 30){
		$discount_30days = gArrayItem($pricing,'discount_30days');
		if($discount_30days > 0){
			$basic_discount = $discount_30days;
		}
	}elseif($days >= 7){
		$discount_7days = gArrayItem($pricing,'discount_7days');
		if($discount_7days > 0){
			$basic_discount = $discount_7days;
		}
	}elseif($days >= 5){
		$discount_5days = gArrayItem($pricing,'discount_5days');
		if($discount_5days > 0){
			$basic_discount = $discount_5days;
		}
	}elseif($days >= 3){
		$discount_3days = gArrayItem($pricing,'discount_3days');
		if($discount_3days > 0){
			$basic_discount = $discount_3days;
		}
	}

	

	if($checkInDate->format("Y") != $checkOutDate->format("Y")){
		$label = $checkInDate->format("j M Y").' - '.$checkOutDate->format("j M Y");
	}else{
		$label = $checkInDate->format("j M").' - '.$checkOutDate->format("j M");
	}

	$label .= ' ('.$days.' ';
	$label .= ($days > 1) ? 'nights' : 'night';
	$label .= ')';

	$currency = vv_get_config('site_currency');
	if($currency == '') $currency = 'NOK';


	foreach($dprices as $i => $v){
		/*
		if($label != '') $label .= ', ';
		$label .= vv_number_format($i,true).' x '.$v; 
		$label .= ($v > 1) ? ' nights' : ' night';
		*/

		$totalCost += floatval($i) * intval($v);
	}


	//echo print_r_pre($dprices);

	return ['label' => $label, 
			'total' => $totalCost, 
			'basic_discount' => $basic_discount, 
			'campaign_discount' => $campaign_discount, 
			'campaign_discount_desc' => $campaign_discount_desc, 
			'dprices' => $dprices, 
			'dprices2' => $dprices2
		   ];

}


function vv_break_dprices($dprices2 = []){
		$result = [];

		$startDate = null;
		$prevDate = null;
		$prevPrice = null;

		foreach ($dprices2 as $date => $price) {
		    $price = (float)$price;
		    
		    if ($startDate === null) {
		        $startDate = $date;
		    }

		    if ($prevPrice !== null) {
		        $expectedNextDate = (new DateTime($prevDate))->modify('+1 day')->format('Y-m-d');
		        if ($price !== $prevPrice || $date !== $expectedNextDate) {
		            // Calculate days in range
		            $start = new DateTime($startDate);
		            $end = new DateTime($prevDate);
		            $numDays = $start->diff($end)->days + 1;

		            $result[] = [
		                'start' => $startDate,
		                'end'   => (new DateTime($prevDate))->modify('+1 day')->format('Y-m-d'),
		                'price' => $prevPrice,
		                'days'  => $numDays,
		            ];
		            $startDate = $date;
		        }
		    }

		    $prevPrice = $price;
		    $prevDate = $date;
		}

		// Add final range
		if ($startDate !== null) {
		    $start = new DateTime($startDate);
		    $end = new DateTime($prevDate);
		    $numDays = $start->diff($end)->days + 1;

		    $result[] = [
		        'start' => $startDate,
		        'end'   => (new DateTime($prevDate))->modify('+1 day')->format('Y-m-d'),
		        'price' => $prevPrice,
		        'days'  => $numDays,
		    ];
		}

		return $result;

}



function vv_get_sending_receiving_settings(){
	$options = json_decode(get_option('vv-sending_receiving_settings'),true);
	if(!is_array($options)) return false;
	if(count($options) == 0) return false;

	return $options;
}


function vv_date_format($date){
	return date("m/d/Y",strtotime($date));
}

add_action( 'wp_mail_failed', 'my_mail_failed_log', 10, 1 );
function my_mail_failed_log( $wp_error ) {
	error_log( 'Mail failed: ' . $wp_error->get_error_message() );
}

add_filter('pre_wp_mail', 'vv_pre_wp_mail' , 10, 2);

function vv_pre_wp_mail($null, $atts = [])
{
    if ( isset( $atts['to'] ) ) {
            $to = $atts['to'];
    }

    if ( ! is_array( $to ) ) {
            $to = explode( ',', $to );
    }

    if ( isset( $atts['subject'] ) ) {
            $subject = $atts['subject'];
    }

    if ( isset( $atts['message'] ) ) {
            $message = $atts['message'];
    }

    if ( isset( $atts['headers'] ) ) {
            $headers = $atts['headers'];
    }

    if ( isset( $atts['attachments'] ) ) {
            $attachments = $atts['attachments'];
            if ( ! is_array( $attachments ) ) {
                    $attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );
            }
    }


    $options = gArrayItem($_SESSION,'vv_test_smtp_options');
    if(!is_array($options)){
    	$options = vv_get_sending_receiving_settings();
    }

    //echo print_r_pre($options);

    if($options === false) return false;
    elseif(gArrayItem($options,'sender_email') == '' || gArrayItem($options,'host_out') == '' || gArrayItem($options,'username_out') == '' || gArrayItem($options,'password_out') == '' ){
    	return false;
    }

    
    global $phpmailer;

    // (Re)create it, if it's gone missing.
    if ( ! ( $phpmailer instanceof PHPMailer\PHPMailer\PHPMailer ) ) {
            require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
            require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
            require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
            $phpmailer = new PHPMailer\PHPMailer\PHPMailer( true );

            $phpmailer::$validator = static function ( $email ) {
                    return (bool) is_email( $email );
            };
    }

    // Headers.
    $cc       = array();
    $bcc      = array();
    $reply_to = array();

    if ( empty( $headers ) ) {
            $headers = array();
    } else {
            if ( ! is_array( $headers ) ) {
                    // Explode the headers out, so this function can take
                    // both string headers and an array of headers.
                    $tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
            } else {
                    $tempheaders = $headers;
            }
            $headers = array();

            // If it's actually got contents.
            if ( ! empty( $tempheaders ) ) {
                    // Iterate through the raw headers.
                    foreach ( (array) $tempheaders as $header ) {
                            if ( strpos( $header, ':' ) === false ) {
                                    if ( false !== stripos( $header, 'boundary=' ) ) {
                                            $parts    = preg_split( '/boundary=/i', trim( $header ) );
                                            $boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
                                    }
                                    continue;
                            }
                            // Explode them out.
                            list( $name, $content ) = explode( ':', trim( $header ), 2 );

                            // Cleanup crew.
                            $name    = trim( $name );
                            $content = trim( $content );

                            switch ( strtolower( $name ) ) {
                                    // Mainly for legacy -- process a "From:" header if it's there.
                                    case 'from':
                                            $bracket_pos = strpos( $content, '<' );
                                            if ( false !== $bracket_pos ) {
                                                    // Text before the bracketed email is the "From" name.
                                                    if ( $bracket_pos > 0 ) {
                                                            $from_name = substr( $content, 0, $bracket_pos - 1 );
                                                            $from_name = str_replace( '"', '', $from_name );
                                                            $from_name = trim( $from_name );
                                                    }

                                                    $from_email = substr( $content, $bracket_pos + 1 );
                                                    $from_email = str_replace( '>', '', $from_email );
                                                    $from_email = trim( $from_email );

                                                    // Avoid setting an empty $from_email.
                                            } elseif ( '' !== trim( $content ) ) {
                                                    $from_email = trim( $content );
                                            }
                                            break;
                                    case 'content-type':
                                            if ( strpos( $content, ';' ) !== false ) {
                                                    list( $type, $charset_content ) = explode( ';', $content );
                                                    $content_type                   = trim( $type );
                                                    if ( false !== stripos( $charset_content, 'charset=' ) ) {
                                                            $charset = trim( str_replace( array( 'charset=', '"' ), '', $charset_content ) );
                                                    } elseif ( false !== stripos( $charset_content, 'boundary=' ) ) {
                                                            $boundary = trim( str_replace( array( 'BOUNDARY=', 'boundary=', '"' ), '', $charset_content ) );
                                                            $charset  = '';
                                                    }

                                                    // Avoid setting an empty $content_type.
                                            } elseif ( '' !== trim( $content ) ) {
                                                    $content_type = trim( $content );
                                            }
                                            break;
                                    case 'cc':
                                            $cc = array_merge( (array) $cc, explode( ',', $content ) );
                                            break;
                                    case 'bcc':
                                            $bcc = array_merge( (array) $bcc, explode( ',', $content ) );
                                            break;
                                    case 'reply-to':
                                            $reply_to = array_merge( (array) $reply_to, explode( ',', $content ) );
                                            break;
                                    default:
                                            // Add it to our grand headers array.
                                            $headers[ trim( $name ) ] = trim( $content );
                                            break;
                            }
                    }
            }
    }

    // Empty out the values that may be set.
    $phpmailer->clearAllRecipients();
    $phpmailer->clearAttachments();
    $phpmailer->clearCustomHeaders();
    $phpmailer->clearReplyTos();

    // Set "From" name and email.

    // If we don't have a name from the input headers.
    if ( ! isset( $from_name ) ) {
            $from_name = $options['sender_name'];//'WordPress';
    }

    /*
     * If we don't have an email from the input headers, default to wordpress@$sitename
     * Some hosts will block outgoing mail from this address if it doesn't exist,
     * but there's no easy alternative. Defaulting to admin_email might appear to be
     * another option, but some hosts may refuse to relay mail from an unknown domain.
     * See https://core.trac.wordpress.org/ticket/5007.
     */
    if ( ! isset( $from_email ) ) {
            // Get the site domain and get rid of www.
            $sitename = wp_parse_url( network_home_url(), PHP_URL_HOST );
            if ( 'www.' === substr( $sitename, 0, 4 ) ) {
                    $sitename = substr( $sitename, 4 );
            }

            $from_email = $options['sender_email'];//'wordpress@' . $sitename;
    }


    try {
            $phpmailer->setFrom( $from_email, $from_name, false );
    } catch ( PHPMailer\PHPMailer\Exception $e ) {
            $mail_error_data                             = compact( 'to', 'subject', 'message', 'headers', 'attachments' );
            $mail_error_data['phpmailer_exception_code'] = $e->getCode();

            /** This filter is documented in wp-includes/pluggable.php */
            do_action( 'wp_mail_failed', new WP_Error( 'wp_mail_failed', $e->getMessage(), $mail_error_data ) );

            return false;
    }

    // Set mail's subject and body.
    $phpmailer->Subject = $subject;
    $phpmailer->Body    = $message;

    // Set destination addresses, using appropriate methods for handling addresses.
    $address_headers = compact( 'to', 'cc', 'bcc', 'reply_to' );

    foreach ( $address_headers as $address_header => $addresses ) {
            if ( empty( $addresses ) ) {
                    continue;
            }

            foreach ( (array) $addresses as $address ) {
                    try {
                            // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>".
                            $recipient_name = '';

                            if ( preg_match( '/(.*)<(.+)>/', $address, $matches ) ) {
                                    if ( count( $matches ) == 3 ) {
                                            $recipient_name = $matches[1];
                                            $address        = $matches[2];
                                    }
                            }

                            switch ( $address_header ) {
                                    case 'to':
                                            $phpmailer->addAddress( $address, $recipient_name );
                                            break;
                                    case 'cc':
                                            $phpmailer->addCc( $address, $recipient_name );
                                            break;
                                    case 'bcc':
                                            $phpmailer->addBcc( $address, $recipient_name );
                                            break;
                                    case 'reply_to':
                                            $phpmailer->addReplyTo( $address, $recipient_name );
                                            break;
                            }
                    } catch ( PHPMailer\PHPMailer\Exception $e ) {
                            continue;
                    }
            }
    }

    // Tell PHPMailer to use SMTP
    $phpmailer->isSMTP(); //$phpmailer->isMail();
    // Set the hostname of the mail server
    $phpmailer->Host = $options['host_out'];
    $phpmailer->SMTPAuth = true;
    // SMTP username
    $phpmailer->Username = $options['username_out'];
    // SMTP password
    $phpmailer->Password = $options['password_out'];  
    // Whether to use encryption
    $type_of_encryption = $options['encryption_out'];
    if($type_of_encryption=="none"){
        $type_of_encryption = '';  
    }
    $phpmailer->SMTPSecure = $type_of_encryption;
    // SMTP port
    $phpmailer->Port = $options['port_out'];  

    // Whether to enable TLS encryption automatically if a server supports it
    $phpmailer->SMTPAutoTLS = false;
    //enable debug when sending a test mail
    if(isset($_POST['smtp_mailer_send_test_email'])){
        $phpmailer->SMTPDebug = 4;
        // Ask for HTML-friendly debug output
        $phpmailer->Debugoutput = 'html';
    }

    //disable ssl certificate verification if checked
    if(isset($options['disable_ssl_verification_out']) && !empty($options['disable_ssl_verification_out'])){
        $phpmailer->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
    }
    // Set Content-Type and charset.

    // If we don't have a content-type from the input headers.
    if ( ! isset( $content_type ) ) {
            $content_type = 'text/plain';
    }

    /**
     * Filters the wp_mail() content type.
     *
     * @since 2.3.0
     *
     * @param string $content_type Default wp_mail() content type.
     */
    $content_type = apply_filters( 'wp_mail_content_type', $content_type );

    $phpmailer->ContentType = $content_type;

    // Set whether it's plaintext, depending on $content_type.
    if ( 'text/html' === $content_type ) {
            $phpmailer->isHTML( true );
    }

    // If we don't have a charset from the input headers.
    if ( ! isset( $charset ) ) {
            $charset = get_bloginfo( 'charset' );
    }

    /**
     * Filters the default wp_mail() charset.
     *
     * @since 2.3.0
     *
     * @param string $charset Default email charset.
     */
    $phpmailer->CharSet = apply_filters( 'wp_mail_charset', $charset );

    // Set custom headers.
    if ( ! empty( $headers ) ) {
            foreach ( (array) $headers as $name => $content ) {
                    // Only add custom headers not added automatically by PHPMailer.
                    if ( ! in_array( $name, array( 'MIME-Version', 'X-Mailer' ), true ) ) {
                            try {
                                    $phpmailer->addCustomHeader( sprintf( '%1$s: %2$s', $name, $content ) );
                            } catch ( PHPMailer\PHPMailer\Exception $e ) {
                                    continue;
                            }
                    }
            }

            if ( false !== stripos( $content_type, 'multipart' ) && ! empty( $boundary ) ) {
                    $phpmailer->addCustomHeader( sprintf( 'Content-Type: %s; boundary="%s"', $content_type, $boundary ) );
            }
    }

    if ( isset( $attachments ) && ! empty( $attachments ) ) {
            foreach ( $attachments as $attachment ) {
                    try {
                            $phpmailer->addAttachment( $attachment );
                    } catch ( PHPMailer\PHPMailer\Exception $e ) {
                            continue;
                    }
            }
    }

    /**
     * Fires after PHPMailer is initialized.
     *
     * @since 2.2.0
     *
     * @param PHPMailer $phpmailer The PHPMailer instance (passed by reference).
     */
    do_action_ref_array( 'phpmailer_init', array( &$phpmailer ) );

    // Send!
    try {
            $return =  $phpmailer->send();
		    return $return;
    } catch ( PHPMailer\PHPMailer\Exception $e ) {

            $mail_error_data                             = compact( 'to', 'subject', 'message', 'headers', 'attachments' );
            $mail_error_data['phpmailer_exception_code'] = $e->getCode();

            /**
             * Fires after a PHPMailer\PHPMailer\Exception is caught.
             *
             * @since 4.4.0
             *
             * @param WP_Error $error A WP_Error object with the PHPMailer\PHPMailer\Exception message, and an array
             *                        containing the mail recipient, subject, message, headers, and attachments.
             */
            do_action( 'wp_mail_failed', new WP_Error( 'wp_mail_failed', $e->getMessage(), $mail_error_data ) );

            return false;
    }
}



