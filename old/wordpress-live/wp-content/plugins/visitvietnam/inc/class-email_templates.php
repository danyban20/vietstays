<?php 

class vvEmailTemplates{

	public function __construct(){
	}

	public function get_actions($action = ''){
		if($action == 'delete-email_template' && GET_Request('id') > 0){
			$this->delete_email_template(GET_Request('id'));
		}elseif($action == 'get_email_templates_json'){
			$this->get_email_templates_json();
		}elseif($action == 'resend_registration_email' && GET_Request('u') > 0){
			die($this->send_registrattion_email(GET_Request('u')));
		}
	}

	public function post_actions($action = ''){
		//die($action);
		if($action == 'email_template-save'){
			$this->save_email_template();
		}
	}

	public function save_email_template(){
		global $wpdb;

		$code = sanitize_text_field( POST_Request( 'code' ) );
		if ( $code === '' ) {
			setErrorMsg( 'Missing email template code.' );
			wp_redirect( vv_admin_url( 'settings/email-templates' ) );
			die();
		}

		$locale = POST_Request( 'locale' );
		if ( class_exists( 'vvI18n' ) ) {
			$locale = vvI18n::normalize_locale( $locale );
		} elseif ( $locale === '' ) {
			$locale = 'en';
		}

		$data = $this->filter_row_data( [
			'code'    => $code,
			'locale'  => $locale,
			'name'    => wp_unslash( POST_Request( 'name' ) ),
			'subject' => wp_unslash( POST_Request( 'subject' ) ),
			'body'    => wp_unslash( POST_Request( 'body' ) ),
		] );

		$email_template = $this->get_email_template_row( $code, $locale, false );
		$row_id         = $this->get_template_row_id( $email_template );
		$pk             = $this->get_primary_key_column();

		if ( $row_id > 0 ) {
			unset( $data['code'] );
			$result = $wpdb->update( 'vv_email_templates', $data, [ $pk => $row_id ] );
		} else {
			$result = $wpdb->insert( 'vv_email_templates', $data );
		}

		if ( $result === false ) {
			setErrorMsg( 'Failed to save email template: ' . $wpdb->last_error );
		} else {
			setSuccessMsg( 'Email Template Updated' );
		}

		wp_redirect( vv_admin_url( 'settings/email-templates' ) . '#email_template_' . rawurlencode( $code ) );
		die();
	}

	public function get_template_row_id( $template ) {
		if ( ! is_array( $template ) ) {
			return 0;
		}
		$id = intval( gArrayItem( $template, 'email_id' ) );
		if ( $id <= 0 ) {
			$id = intval( gArrayItem( $template, 'ID' ) );
		}
		return $id;
	}

	public function get_primary_key_column() {
		$columns = $this->get_table_columns();
		if ( in_array( 'email_id', $columns, true ) ) {
			return 'email_id';
		}
		if ( in_array( 'ID', $columns, true ) ) {
			return 'ID';
		}
		return 'email_id';
	}

	public function get_table_columns() {
		global $wpdb;

		static $columns = null;
		if ( is_array( $columns ) ) {
			return $columns;
		}

		$columns = $wpdb->get_col( 'DESCRIBE vv_email_templates', 0 );
		if ( ! is_array( $columns ) ) {
			$columns = [];
		}

		return $columns;
	}

	public function filter_row_data( array $data ) {
		$columns = array_flip( $this->get_table_columns() );
		$body_key = isset( $columns['body'] ) ? 'body' : ( isset( $columns['message'] ) ? 'message' : 'body' );

		if ( isset( $data['body'] ) && $body_key !== 'body' ) {
			$data[ $body_key ] = $data['body'];
			unset( $data['body'] );
		}

		return array_intersect_key( $data, $columns );
	}

	public function get_template_body( $template ) {
		if ( ! is_array( $template ) ) {
			return '';
		}
		$body = gArrayItem( $template, 'body' );
		if ( trim( (string) $body ) === '' ) {
			$body = gArrayItem( $template, 'message' );
		}
		return $body;
	}

	public function get_email_templates($filter = array()){
		global $wpdb;

		$where = " 1 ";
		foreach($filter as $index => $value){
			$where .= " AND ".$index." = '".$value."' ";
		}
		
		$email_templates = $wpdb->get_results("SELECT * FROM vv_email_templates WHERE ".$where,ARRAY_A);

		for($i = 0; $i < count($email_templates); $i++){
			$email_templates[$i]['name'] = stripslashes($email_templates[$i]['name']);
		}

		return $email_templates;
	}

	function get_email_templates_json(){

		$filter = array();
		if(GET_Request('apartment_id') > 0) $filter['apartment_id'] = GET_Request('apartment_id');

		$data = $this->get_email_templates($filter);



		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($data);
	
		die();		
	}

	public function get_email_template($id = 0){
		global $wpdb;

		$id = intval( $id );
		if ( $id <= 0 ) {
			return [];
		}

		$pk = $this->get_primary_key_column();
		$email_template = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM vv_email_templates WHERE {$pk} = %d", $id ),
			ARRAY_A
		);

		return is_array( $email_template ) ? $email_template : [];
	}

	public function get_email_template_row( $code = '', $locale = 'en', $fallback = true ) {
		global $wpdb;

		$code = sanitize_text_field( $code );
		if ( $code === '' ) {
			return [];
		}

		if ( class_exists( 'vvI18n' ) ) {
			$locale = vvI18n::normalize_locale( (string) $locale );
		}

		$pk         = $this->get_primary_key_column();
		$columns    = $this->get_table_columns();
		$has_locale = in_array( 'locale', $columns, true );

		if ( $has_locale ) {
			$email_template = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM vv_email_templates WHERE code = %s AND locale = %s ORDER BY {$pk} DESC LIMIT 1",
					$code,
					$locale
				),
				ARRAY_A
			);
			if ( is_array( $email_template ) && ! empty( $email_template ) ) {
				return $email_template;
			}
		}

		if ( ! $fallback || ! $has_locale ) {
			if ( ! $has_locale ) {
				return $this->get_email_template_by_code( $code, 'en' );
			}
			return [];
		}

		return $this->get_email_template_row( $code, 'en', false );
	}

	public function get_email_template_by_code( $code = '', $locale = null ) {
		if ( $locale === null && class_exists( 'vvI18n' ) ) {
			$locale = vvI18n::current_locale();
		}
		if ( class_exists( 'vvI18n' ) ) {
			$locale = vvI18n::normalize_locale( (string) $locale );
		} else {
			$locale = 'en';
		}

		$template = $this->get_email_template_row( $code, $locale, true );
		if ( ! empty( $template ) ) {
			return $template;
		}

		global $wpdb;
		$pk = $this->get_primary_key_column();
		$email_template = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM vv_email_templates WHERE code = %s ORDER BY {$pk} DESC LIMIT 1",
				sanitize_text_field( $code )
			),
			ARRAY_A
		);

		return is_array( $email_template ) ? $email_template : [];
	}


	public function count_email_templates($email_templates_id){
		global $wpdb;

		return intval(gArrayItem($wpdb->get_row("SELECT COUNT(*) as num_rows FROM vv_email_templates WHERE email_templates_id = ".$email_templates_id),'num_rows'));
	}

	public function delete_email_template($id = 0){
		global $wpdb;

		$email_template = $this->get_email_template($id);
		$row_id = $this->get_template_row_id( $email_template );
		if ( $row_id > 0 ) {
			$pk = $this->get_primary_key_column();
			$wpdb->delete( 'vv_email_templates', [ $pk => $row_id ] );

			setSuccessMsg('Email Template Deleted Successfully');
			wp_redirect(vv_admin_url('email_templates/?apartment='.gArrayItem($email_template,'apartment_id')));
			die();
		}
	}

	public function replace_email_tokens($text = '', $data = []){

		foreach($data as $i => $v){
			$text = str_replace('%'.strtoupper($i).'%',$v,$text);
		}

		return $text;

	}

	/**
	 * Send an email using a vv_email_templates row (by code).
	 *
	 * @param string $code Template code.
	 * @param string $to   Recipient email.
	 * @param array  $data Token replacements (%KEY% => value).
	 * @return bool
	 */
	public function send_by_code( $code, $to, array $data = [], $locale = null ) {
		$template = $this->get_email_template_by_code( $code, $locale );
		$subject  = trim( gArrayItem( $template, 'subject' ) );
		$body     = trim( $this->get_template_body( $template ) );
		$to       = trim( (string) $to );

		if ( $subject === '' || $body === '' || $to === '' ) {
			return false;
		}

		$subject = $this->replace_email_tokens( $subject, $data );
		$body    = $this->replace_email_tokens( $body, $data );

		$sender = vv_get_config( 'email_sender' );
		if ( $sender === '' ) {
			$sender = get_bloginfo( 'admin_email' );
		}

		send_email( $sender, $sender, $to, $subject, $body, false );
		return true;
	}


	public function send_registrattion_email($user_id, $user_password = ''){

		$template 	= $this->get_email_template_by_code('user_registration');
		$subject 	= trim(gArrayItem($template,'subject'));
		$body 		= $this->get_template_body($template);


		$user = get_user_by('ID',$user_id);

		//echo $user->user_email;

		if($subject != '' && $body != '' && $user){

			$data = array();
			$data['FIRSTNAME'] 	= get_user_meta($user_id,'first_name',true);
			$data['LASTNAME'] 	= get_user_meta($user_id,'last_name',true);
			$data['EMAIL'] 		= $user->user_email;
			$data['USERNAME'] 	= $user->user_login;
			$data['PASSWORD'] 	= $user_password;

			$subject 	= $this->replace_email_tokens($subject,$data);
			$body 		= $this->replace_email_tokens($body,$data);

			$sender = vv_get_config('email_sender_name').' <'.vv_get_config('email_sender').'>';


			send_email($sender, '', $user->user_email, $subject, $body);

			return $body;
		}

		return 'error';
	}

	public function send_booking_confirmation_to_user($booking_id = 0){

		$booking_class 		= new vvBookings;
		$apartment_class 	= new vvApartments;
		$district_class 	= new vvDistricts;

		$booking = $booking_class->get_booking($booking_id);
		if(gArrayItem($booking,'ID') > 0){

			$apartment  = $apartment_class->get_apartment($booking['apartment_id']);
			$district   = $district_class->get_district($booking['district_id']);

			$template 	= $this->get_email_template_by_code('user_booking_confirmation');
			$subject 	= trim(gArrayItem($template,'subject'));
			$body 		= $this->get_template_body($template);

			$user_id 	= gArrayItem($booking,'user_id');		
			$user 		= get_user_by('ID',$user_id);

			if($subject != '' && $body != '' && $user){

				$data = array();
				$data['FIRSTNAME'] 			= get_user_meta($user_id,'first_name',true);
				$data['LASTNAME'] 			= get_user_meta($user_id,'last_name',true);
				$data['EMAIL'] 				= $user->user_email;
				$data['BOOKING_NUM'] 		= vv_get_booking_num($booking);
				$data['BOOKING_LINK'] 		= vv_get_booking_link($booking);
				$data['CHECK-IN_DATE'] 		= date("M j,Y",$booking['check_in_date']);
				$data['CHECK-OUT_DATE'] 	= date("M j,Y",$booking['check_out_date']);
				$data['BOOKING_TABLE']		= vv_get_booking_tables_html($booking);
				$data['APARTMENT_NAME']		= gArrayItem($apartment,'name');
				$data['DISTRICT_NAME'] 		= gArrayItem($district,'name');



				$subject 	= $this->replace_email_tokens($subject,$data);
				$body 		= $this->replace_email_tokens($body,$data);

				$sender = vv_get_config('email_sender_name').' <'.vv_get_config('email_sender').'>';


				send_email($sender, '', $user->user_email, $subject, $body);

				return $body;
			}
		}

 
		return 'error';
	}


}