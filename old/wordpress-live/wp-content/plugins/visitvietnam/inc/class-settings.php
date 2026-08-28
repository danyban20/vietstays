<?php 

class vvSettings{

	public function __construct(){
	}

	public function get_actions($action = ''){
		if($action == 'delete-setting' && GET_Request('id') > 0){
			$this->delete_setting(GET_Request('id'));
		}elseif($action== 'update_currency'){
			$host_currency = get_user_meta(get_current_user_id(),'vv_host_currency',true);
			$this->update_currency($host_currency);
		}

	}

	public function post_actions($action = ''){
		if($action == 'vv_save_setting'){
			$this->save_setting();
		}elseif($action== 'update_currency'){
			$this->update_currency();
		}elseif($action == 'save_currency_settings'){
			$this->save_currency_settings();
		}elseif($action == 'save_default_currency'){
			$this->save_default_currency();
		}elseif($action == 'save_email_settings'){
			$this->save_email_settings();
		}elseif($action == 'test_smtp'){
			$this->wcrm_test_smtp();
		}
	}

	public function save_setting(){

		update_option('facilities',POST_Request('facilities'));

		setSuccessMsg('Settings Updates');
		wp_redirect(vv_admin_url('settings'));
		die();

	}

	public function get_settings($filter = array()){
		global $wpdb;

		$where = " 1 ";
		foreach($filter as $index => $value){
			$where .= " AND ".$index." = '".$value."' ";
		}
		
		$settings = $wpdb->get_results("SELECT * FROM vv_settings WHERE ".$where,ARRAY_A);

		return $settings;
	}

	public function get_setting($id = 0){
		global $wpdb;

		$setting = $wpdb->get_row("SELECT * FROM vv_settings WHERE ID = ".$id, ARRAY_A);

		return $setting;
	}

	public function count_settings($setting_id){
		global $wpdb;

		return intval(gArrayItem($wpdb->get_row("SELECT COUNT(*) as num_rows FROM vv_settings WHERE setting_id = ".$setting_id),'num_rows'));
	}

	public function delete_setting($id = 0){
		global $wpdb;

		$setting = $this->get_setting($id);
		if(gArrayItem($setting,'ID') > 0){
			$wpdb->delete('vv_settings',['ID' => $id]);

			setSuccessMsg('Setting Deleted Successfully');
			wp_redirect(vv_admin_url('settings/?apartment='.gArrayItem($setting,'apartment_id')));
			die();
		}
	}

	public function update_currency($baseCurrency = 'USD',$redirect= true){
		// Your API key from https://www.exchangerate-api.com
		$apiKey = "c2472e29b9fdd622ccb1c4e7";

		// Base currency (e.g., USD, EUR, PHP, etc.)

		// API endpoint
		$url = "https://v6.exchangerate-api.com/v6/$apiKey/latest/$baseCurrency";

		// Initialize cURL
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// Execute request
		$response = curl_exec($ch);

		// Close cURL
		curl_close($ch);

		// Decode JSON response
		$data = json_decode($response, true);

		// Check if request was successful
		if ($data && isset($data['conversion_rates'])) {
			// Example: convert USD → PHP
			$conversion_rates  = gArrayItem($data,'conversion_rates');
			if(is_array($conversion_rates)){
				update_option('vv_conversion_rates_'.$baseCurrency,json_encode($conversion_rates));

			 // $VND = gArrayItem($conversion_rates,'VND');
			}
			
			$usdToPhp= $data['conversion_rates']['PHP'];
			$conversion_rates;
			$VND = gArrayItem($conversion_rates,'VND');


			//echo "1 $baseCurrency = $usdToPhp PHP\n";

			// You can loop through all rates:
			foreach ($data['conversion_rates'] as $currency => $rate) {
				//echo "1 $baseCurrency = $rate $currency\n";
			}

			if($redirect)setSuccessMsg('COnversion Rates Updated');
		} else {
			if($redirect)setErrorMsg("Error fetching exchange rates.");
		}
		if($redirect) wp_redirect(vv_admin_url('settings/currency'))	;
		if($redirect)die();
	}
	public function save_currency_settings(){
		update_Option('site_currency',POST_Request('default'));
		$conversion_rates= json_decode(get_option('vv_conversion_rates'),true);
		if(!is_array($conversion_rates))$conversion_rates = [];

		$host_currency = get_user_meta(get_current_user_id(),'vv_host_currency',true);
		foreach($_POST as $i => $v){
			if($i != 'action') $conversion_rates[$i] = $v;
		}

		update_option('vv_conversion_rates_'.$host_currency,json_encode($conversion_rates));

		setSuccessMsg('Currency Settings Updated');
		wp_redirect(vv_admin_url('settings/currency'))	;
		die();
	}
	function wcrm_test_smtp(){

		$data['status'] = 'error';
		$data['message'] = 'Could not send test email.';

		$options['sender_email'] = POST_Request('sender_email');
		$options['sender_name'] = POST_Request('sender_name');
		$options['host_out'] = POST_Request('host');
		$options['port_out'] = POST_Request('port');
		$options['username_out'] = POST_Request('username');
		$options['password_out'] = POST_Request('password');
		$options['encryption_out'] = POST_Request('encryption');
		$options['disable_ssl_verification_out'] = POST_Request('disable_ssl');


		$send_to = POST_Request('send_to');

		$_SESSION['wcrm_test_smtp_options'] = $options;

		$subject = 'Visit VIetnam CRM Test SMTP';
		$message = 'The quick brown fox jumps over the lazy dog.';

		if(wp_mail($send_to, $subject, $message)){
			$data['status'] == 'success';
			$data['message'] = 'Test email successfully sent.';
		}

		$_SESSION['wcrm_test_smtp_options'] = null;

		header('Content-Type: application/json; charset=utf-8');
		echo vv_jsonEncode($data);		
		die();
	}
	public function save_email_settings(){

		$settings = [];
		$settings['host'] 		= POST_Request('host');
		$settings['port'] 		= POST_Request('port');
		$settings['username'] 	= POST_Request('username');
		$settings['password'] 	= POST_Request('password');
		$settings['encryption'] 	= POST_Request('encryption');

		$settings['sender_email'] 	= POST_Request('sender_email');
		$settings['sender_name'] 	= POST_Request('sender_name');
		$settings['host_out'] 		= POST_Request('host_out');
		$settings['port_out'] 		= POST_Request('port_out');
		$settings['username_out'] 	= POST_Request('username_out');
		$settings['password_out'] 	= POST_Request('password_out');
		$settings['encryption_out'] 	= POST_Request('encryption_out');
		$settings['disable_ssl_verification_out'] = intval(POST_Request('disable_ssl_verification_out'));

		update_option('vv-sending_receiving_settings',json_encode($settings));

		update_option('vv-new_service_admin_email',POST_Request('new_service_admin_email'));

		setSuccessMsg('Email Settings Updates');
		wp_redirect(vv_admin_url('settings/email'));
		die();

	}
	function save_default_currency(){
		vv_save_user_host_currency( get_current_user_id(), POST_Request( 'default_currency' ) );
		setSuccessMsg('Default Currency  Updated');
		wp_redirect(vv_admin_url('settings/currency'));
		die();
	}
}

