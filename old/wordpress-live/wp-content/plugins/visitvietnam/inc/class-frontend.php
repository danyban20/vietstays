<?php 

class vvFrontend{
	public function __construct(){
	}

	public function get_actions($action = ''){
		if($action == 'get_countries_json'){
			$this->get_countries_json();
		}elseif($action == 'get_states_json'){
			$this->get_states_json();
		}elseif($action == 'vv_check_apartment_booking'){
			$this->check_apartment_booking();
		}elseif($action == 'single_apartment_booking_step1'){
			$this->single_apartment_booking_step1();
		}elseif($action == 'booking_check_user'){
			$this->booking_check_user();
		}elseif($action == 'booking_check_promo_code'){
			vv_booking_check_promo_code(['promocode' => trim(GET_Request('promo_code')), 'apartment_id' => GET_Request('apartment')]);
		}elseif($action == 'booking_search'){
			$this->booking_search();
		}elseif($action == 'search_apartments'){
			$this->search_apartments();
		}
	}

	public function post_actions($action = ''){
		if($action == 'vv_save_city'){
			$this->save_city();
		}elseif($action == 'vv_start_booking'){
			$this->start_booking();
		}elseif($action == 'vv_booking_step2'){
			$this->booking_step2();
		}
	}

	public function get_countries_json(){
		header("Content-Type: application/json");

		$countries = vv_get_countries();

		echo json_encode($countries);
		die();
	}

	public function get_states_json(){
		header("Content-Type: application/json");

		$states = vv_get_states(GET_Request('country'));

		echo json_encode($states);
		die();
	}

	public function search_apartments(){

		$this->booking_search(false);

		//echo print_r_pre($_SESSION['BOOKING_DATA_FRONT']);
		include get_template_directory() . '/inc/search-apartments-results.php';

		die();
	}


	function check_apartment_booking(){

		global $wpdb;

		$apartment_id 	= GET_Request('apartment');
		$check_in_date 	= GET_Request('check_in_date');
		$check_out_date = GET_Request('check_out_date');

		$_SESSION['check_in_date'] 	= $check_in_date;
		$_SESSION['check_out_date'] = $check_out_date;

		header("Content-Type: application/json");

		if($apartment_id > 0){


			// CHECK IF AVAILABLE
			$sql = "SELECT COUNT(*) as num_bookings FROM vv_bookings 
					WHERE apartment_id = ".$apartment_id." 
					AND check_in_date <= '".$check_in_date."' 
					AND check_out_date >= '".$check_in_date."' 
					AND check_in_date <= '".$check_out_date."' 
					AND check_out_date >= '".$check_out_date."' 
				";
			$num_bookings = gArrayItem($wpdb->get_row($sql,ARRAY_A),"num_bookings");

			if($num_bookings > 0){

				echo json_encode(["status" => "error", "error" => "Apartment not available on selected dates."]);
				die();

			}

			$data 				= vv_get_booking_pricing_data($apartment_id, $check_in_date, $check_out_date);
			$label 				= gArrayItem($data,'label');
			$totalCost 			= gArrayItem($data,'total');
			$basic_discount 	= gArrayItem($data,'basic_discount');
			$campaign_discount 	= gArrayItem($data,'campaign_discount');


            //echo print_r_pre($data);


			echo json_encode(['status' => 'success', 'label' => $label, 'total' => $totalCost, 'basic_discount' => $basic_discount, 'campaign_discount' => $campaign_discount]);

		}else{
			echo json_encode(['status' => 'error', 'error' => 'Invalid Apartment']);
		}

		die();
	}





	/*
	BOOKING
	*/

	public function booking_search($redirect= true){

		$redirect_url = get_bloginfo('url').'/apartments/';

		$booking_data = gArrayItem($_SESSION,'BOOKING_DATA_FRONT');
		if(!is_array($booking_data)) $booking_data = [];

		$datefilter = urldecode(GET_Request('datefilter'));
		if($datefilter != '' && strpos($datefilter,' - ') !== false){
			$tmp = explode(" - ",$datefilter);

			$booking_data['check_in_date'] = date("Y-m-d",strtotime($tmp[0]));
			$booking_data['check_out_date'] = date("Y-m-d",strtotime($tmp[1]));
		}

		
		$booking_data['city_id'] = intval(GET_Request('city_id'));

		$booking_data['rooms'] = GET_Request('rooms');
		$booking_data['adults'] = GET_Request('adults');
		$booking_data['children'] = GET_Request('children');
		$booking_data['facility'] = GET_Request('facility');


		$_SESSION['BOOKING_DATA_FRONT'] = $booking_data;

		if($redirect){
			wp_redirect($redirect_url);
			die();
		}
	}

	public function start_booking(){

		$booking_data = gArrayItem($_SESSION,'BOOKING_DATA_FRONT');

		if($_POST){
			foreach($_POST as $i => $v){
				$booking_data[$i] = $v;
			}
		}

		$_SESSION['BOOKING_DATA_FRONT'] = $booking_data;


		if(GET_Request('isajax') == 1) die();

		wp_redirect(get_bloginfo('url').'/booking');
		die();

	}

	public function booking_step2(){


		$booking_data = gArrayItem($_SESSION,'BOOKING_DATA_FRONT');


		$booking_data['payment_method'] = POST_Request('payment_method');
		$booking_data['airport_pickup'] = POST_Request('airport_pickup');
		$booking_data['num_cleaning']	= POST_Request('num_cleaning');

		if ( intval( POST_Request( 'promo_code_added' ) ) === 1 && POST_Request( 'promo_code' ) !== '' ) {
			$booking_data['promo_code']           = POST_Request( 'promo_code' );
			$booking_data['promo_code_added']     = 1;
			$booking_data['promo_code_discount']  = floatval( POST_Request( 'promo_code_discount' ) );
		}

		if(POST_Request('promo_code') != '' && intval( POST_Request( 'promo_code_added' ) ) !== 1 ){
			$return = vv_booking_check_promo_code(['promocode' => trim(POST_Request('promo_code')), 'apartment_id' => gArrayItem($booking_data,'apartment_id'), 'return' => true]);
			if(gArrayItem($return,'found') ){
				$booking_data['ambassador_id'] 			= $return['user_id'];
				$booking_data['promo_code_discount']	= $return['discount'];
				$booking_data['promo_code'] 			= POST_Request('promo_code');
				$booking_data['promo_code_added'] 		= 1;
			}
		}

		if(GET_Request('isajax') == 1){
			$_SESSION['BOOKING_DATA_FRONT'] = $booking_data;

			die();			
		}



		$error_msg = 'An error occurred';
		$email = POST_Request('email');
		$user_id = 0;
		$user = get_user_by('email',$email);
		if($user){
			$user_id = $user->ID;
		}else{
			// SIGNUP THE USER
			$password =  wp_generate_password();
			$_POST['newpassword'] = $password;

			$users_class = new vvUsers;
			$result = $users_class->save_user(false);
			if(gArrayItem($result,'status') == 'success'){
				$user_id = gArrayItem($result,'user_id');

				$email_templates_class = new wcrmEmailTemplates;
				$email_templates_class->send_registrattion_email($user_id, $password);

			}else{
				$error_msg = gArrayItem($result,'error_msg');
			}
		}


		if($user_id > 0){
			$apartment_id 	 = gArrayItem($booking_data,'apartment_id');
			$apartment_class = new vvApartments;
			$apartment = $apartment_class->get_apartment($apartment_id);

			$data           			= vv_get_booking_pricing_data($apartment_id, gArrayItem($booking_data,'check_in_date'), gArrayItem($booking_data,'check_out_date'));
			$label          			= gArrayItem($data,'label');
			$subtotal       			= floatval(gArrayItem($data,'total'));
			$basic_discount 			= floatval(gArrayItem($data,'basic_discount'));
			$campaign_discount   		= floatval(gArrayItem($data,'campaign_discount'));
			$campaign_discount_desc 	= gArrayItem($data,'campaign_discount_desc');
			$promo_code_added 			= intval(gArrayItem($booking_data,'promo_code_added'));
			$promo_code                 = gArrayItem($booking_data,'promo_code');
			$dprices2 					= gArrayItem($data,'dprices2');


			$promocode_discount 		= gArrayItem($booking_data,'promo_code_discount');

			if($campaign_discount > 0) $promocode_discount = 0;

			$_POST['user_id'] 								= $user_id;
			$_POST['firstname'] 							= get_user_meta($user_id,'first_name',true);
			$_POST['lastname'] 								= get_user_meta($user_id,'last_name',true);
			$_POST['check_in_date'] 						= gArrayItem($booking_data,'check_in_date');
			$_POST['check_out_date'] 						= gArrayItem($booking_data,'check_out_date');
			$_POST['adults'] 								= gArrayItem($booking_data,'num_adults');
			$_POST['children'] 								= gArrayItem($booking_data,'num_children');
			$_POST['apartment_id']							= $apartment_id;
			$_POST['district_id']							= gArrayItem($apartment,'district');
			$_POST['ambassador_id']							= gArrayItem($booking_data,'ambassador_id');
			$_POST['promo_code']							= gArrayItem($booking_data,'promo_code');
			$_POST['promo_code_discount']					= $promocode_discount;


            $result = vv_break_dprices($dprices2);
            //echo print_r_pre($result);
            //die();

            $item_names = [];
            $item_qtys = [];
            $item_prices = [];

            foreach($result as $r){
                $start = new DateTime($r['start']);
                $end = new DateTime($r['end']);

                $r_label = vv_get_date_range_text($start->format("Y-m-d"),$end->format("Y-m-d"));

                $item_names[] 	= $start->format('m/d/Y').'-'.$end->format('m/d/Y');
                $item_qtys[] 	= $r['days'];
                $item_prices[] 	= $r['price'];
            }

            $_POST['item_name'] = $item_names;
            $_POST['item_qty'] = $item_qtys;
            $_POST['item_price'] = $item_prices;


			if($campaign_discount > 0){
				$subtotal = ($subtotal - ($subtotal * ($campaign_discount/100)));
			}

			if($basic_discount > 0){
				$subtotal = ($subtotal - ($subtotal * ($basic_discount/100)));
			}

			if($promocode_discount > 0 && $promo_code !== '' && $campaign_discount == 0){
				$subtotal = ($subtotal - ($subtotal * ($promocode_discount/100)));
			}

			if(gArrayItem($booking_data,'airport_pickup')){
				$airport_pickup_cost    = floatval(vv_get_config('airport_pickup_cost'));
				$subtotal += $airport_pickup_cost;
				$_POST['airport_pickup_cost'] = $airport_pickup_cost;
			}


			if(gArrayItem($booking_data,'num_cleaning') > 0){
				$cleaning_fee 	= floatval(gArrayItem($apartment,'cleaning_fee'));
				$num_cleaning 	= intval(gArrayItem($booking_data,'num_cleaning'));
				$subtotal += ($cleaning_fee * $num_cleaning);

				$_POST['num_cleaning'] 	= $num_cleaning;
			}

			if(vv_fee() > 0){
				$_POST['booking_fee'] = vv_fee();
			}

			//echo print_r_pre($_POST);
			//die();

			$booking_class = new vvBookings;
			$result = $booking_class->save_booking(false);
			//echo print_r_pre($result);
			//die();
			if(gArrayItem($result,'status') == 'success'){
				$booking_id = intval( gArrayItem( $result, 'booking_id' ) );
				if ( $booking_id > 0 ) {
					if ( class_exists( 'vvConversions' ) && gArrayItem( $booking_data, 'promo_code' ) !== '' ) {
						vvConversions::mark_booking_converted( $booking_id, gArrayItem( $booking_data, 'promo_code' ) );
					}
					$booking = $booking_class->get_booking( $booking_id );
					unset( $_SESSION['VV_CONVERSION_ID'] );
					wp_redirect( vv_get_booking_link( $booking ) );
				} else {
					wp_redirect( vv_users_url() );
				}
				die();
			}else{
				setErrorMsg(gArrayItem($result,'error_msg'));
			}

		}else{
			$_SESSION['BOOKING_DATA_FRONT'] = $booking_data;
			setErrorMsg($error_msg);
		}


		wp_redirect(vv_base_url().'/booking');


		die();
	}

	function single_apartment_booking_step1(){

		include(get_stylesheet_directory().'/inc/single-apartment-init.php');
		include(get_stylesheet_directory().'/inc/single-apartment-booking.php');

		die();
	}


	function booking_check_user(){
		$email = trim(strtolower(GET_Request('email')));

		$user = get_user_by('email',$email);

		$userdata = [];

		if($user){
			$user_id 				= $user->ID;
			$userdata['ID'] 		= $user_id;
			$userdata['email'] 		= $email;
			$userdata['firstname'] 	= get_user_meta($user_id,'first_name',true);
			$userdata['lastname'] 	= get_user_meta($user_id,'last_name',true);

		    $user_address = json_decode(get_user_meta($user_id,'user_address',true),true);
		    if(!is_array($user_address)) $user_address = [];

		    $user_level = get_user_meta($user_id,'vv_user_level',true);
		    if($user_level == '') $user_level = 'customer';

		    $userdata['user_address'] 	= $user_address;
		    $userdata['user_level'] 	= $user_level;

		}else{
			$userdata['ID'] = 'notfound';
		}

		header("Content-Type: application/json");
		echo json_encode($userdata);

		die();
	}




}