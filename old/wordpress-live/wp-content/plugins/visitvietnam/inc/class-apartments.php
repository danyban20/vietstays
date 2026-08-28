<?php 

class vvApartments{
	public function __construct(){
	}

	public function get_actions($action = ''){
		if($action == 'delete-apartment' && GET_Request('id') > 0){
			$this->delete_apartment(GET_Request('id'));
		}elseif($action == 'get_apartment_foods_json'){
			$this->get_apartment_foods_json();
		}elseif($action == 'upload_apartment_image'){
			$this->upload_image();
		}elseif($action == 'delete_apartment_image' && GET_Request('id') > 0 && GET_Request('img') !== ''){
			$this->delete_apartment_image( intval( GET_Request( 'id' ) ), GET_Request( 'img' ) );
		}elseif($action == 'get_apartment_images_html' && GET_Request('id') > 0){
			$apartment = $this->get_apartment(GET_Request('id'))	;
			$this->get_apartment_images_html($apartment);
			die();
		}elseif($action == 'apartment_autocomplete'){
			$this->apartment_autocomplete();
		}
	}

	public function post_actions($action = ''){
		if($action == 'vv_save_apartment'){
			$this->save_apartment();
		}elseif($action == 'add_new_apartments'){
			$this->add_new_apartments();
		}
	}

	public function save_apartment(){
		global $wpdb;

		$apartment_id 			= POST_Request('apartment_id');
		$name 					= POST_Request('name');
		$room_number 			= POST_Request('room_number');
		$floor_number 			= POST_Request('floor_number');
		$display_name 			= POST_Request('display_name2');
		$district 				= POST_Request('district');
		$address 				= POST_Request('address');
		$address_latitude 		= POST_Request('address_latitude');
		$address_longitude 		= POST_Request('address_longitude');
		$description 			= POST_Request('description');
		$about_this 			= POST_Request('about_this');
		$about_this_short 		= POST_Request('about_this_short');
		$rooms 					= POST_Request('rooms');
		$max_guests				= POST_Request('max_guests');
		$num_beds 				= POST_Request('num_beds');
		$user_id 				= POST_Request('user_id');
		$price_daily 			= vv_currency_to_decimal(POST_Request('price_daily'));
		$ambassador_commission 	= POST_Request('ambassador_commission');
		$promocode_discount 	= POST_Request('promocode_discount');
		$check_in_time1 		= POST_Request('check_in_time1');
		$check_in_time2 		= POST_Request('check_in_time2');
		$check_out_time 		= POST_Request('check_out_time');
		$flexible_check_in 		= POST_Request('flexible_check_in');
		$allow_extension 		= POST_Request('allow_extension');
		$facilities 			= json_encode(POST_Request('facilities'));
		$security_features 		= json_encode(POST_Request('security_features'));
		$features_description	= POST_Request('features_description');
		$url_slug 				= vv_slugify(strtolower(POST_Request('url_slug')));
		$num_bathrooms 			= POST_Request('num_bathrooms');
		$area_sqm 				= POST_Request('area_sqm');
		$checkin_without_host 	= intval(POST_Request('checkin_without_host'));
		$airport_pickup 		= intval(POST_Request('airport_pickup'));
		$flexible_reservation 	= intval(POST_Request('flexible_reservation'));
		$scooter_rental 		= intval(POST_Request('scooter_rental'));
		$scooter_rental_fee 	= vv_currency_to_decimal(POST_Request('scooter_rental_fee'));
		$cleaning_fee 			= vv_currency_to_decimal(POST_Request('cleaning_fee'));
		$house_rules 			= POST_Request('house_rules');
		$property_safety 		= POST_Request('property_safety');
		$status 				= POST_Request('status');

		$building_id            = intval( POST_Request( 'building_id' ) );
		$distinguishing_feature = sanitize_text_field( POST_Request( 'distinguishing_feature' ) );
		$apartment_type         = sanitize_text_field( POST_Request( 'apartment_type' ) );
		$price_level            = sanitize_text_field( POST_Request( 'price_level' ) );
		$pricing_model          = sanitize_text_field( POST_Request( 'pricing_model' ) );
		$cleaning_fee_enabled   = POST_Request( 'cleaning_fee_enabled' ) ? 1 : 0;
		$extra_cleaning_fee     = vv_currency_to_decimal( POST_Request( 'extra_cleaning_fee' ) );
		$house_rules_selected   = POST_Request( 'house_rules_selected' );
		$assigned_staff         = POST_Request( 'assigned_staff' );

		if ( $apartment_type === '' && $rooms !== '' ) {
			$apartment_type = vvApartmentPlatform::rooms_to_type( $rooms );
		}
		if ( ! in_array( $price_level, [ 'low', 'normal', 'high' ], true ) ) {
			$price_level = 'normal';
		}
		if ( ! in_array( $pricing_model, [ 'fixed', 'seasonal' ], true ) ) {
			$pricing_model = 'fixed';
		}

		$seasonal_pricing = [];
		$seasonal_month   = POST_Request( 'seasonal_price' );
		if ( is_array( $seasonal_month ) ) {
			foreach ( $seasonal_month as $month => $price ) {
				$seasonal_pricing[ intval( $month ) ] = vv_currency_to_decimal( $price );
			}
		}

		$house_rules_json = [];
		if ( is_array( $house_rules_selected ) ) {
			$house_rules_json = array_map( 'sanitize_key', $house_rules_selected );
			$labels = [];
			foreach ( vvApartmentPlatform::get_house_rules() as $rule ) {
				if ( in_array( gArrayItem( $rule, 'id' ), $house_rules_json, true ) ) {
					$labels[] = gArrayItem( $rule, 'label' );
				}
			}
			if ( $house_rules === '' && ! empty( $labels ) ) {
				$house_rules = implode( "\n", $labels );
			}
		}

		$assigned_staff_ids = [];
		if ( is_array( $assigned_staff ) ) {
			$assigned_staff_ids = array_values( array_filter( array_map( 'intval', $assigned_staff ) ) );
		}

		if($status == '') $status = 'active';

		$url_slug1 	= $url_slug;
		$ctr 		= 0;
		do{
			$sql = "SELECT COUNT(*) as num_rows FROM vv_apartments WHERE url_slug = '".$url_slug."'  AND ID != ".intval($apartment_id);
			$found = intval(gArrayItem($wpdb->get_row($sql,ARRAY_A),'num_rows'));
			if($found == 1){
				$ctr++;
				$url_slug = $url_slug1.'-'.$ctr;
			}
		}while($found == 1);



		$checklist_id 	= POST_Request('checklist_id');
		$checklist_name = POST_Request('checklist_name');
		$checklist_qty 	= POST_Request('checklist_qty');
		$checklist_type = POST_Request('checklist_type');

		if(!is_array($checklist_id)) $checklist_id = array();

		$cleaners_checklists = [];
		for($i = 0; $i < count($checklist_id); $i++){
			array_push($cleaners_checklists, ['checklist_id' => $checklist_id[$i], 'name' => $checklist_name[$i], 'qty' => $checklist_qty[$i], 'type' => $checklist_type[$i]]);
		}


		$pricing = ['addon_days2' => POST_Request('addon_days2'), 
					'discount_3days' => POST_Request('discount_3days'), 
					'discount_5days' => POST_Request('discount_5days'), 
					'discount_7days' => POST_Request('discount_7days'),
					'discount_30days' => POST_Request('discount_30days')
				];


		$data = ['name' => $name, 
				'room_number' => $room_number, 
				'floor_number' => $floor_number,
				'district' => $district, 
				'address' => $address,
				'address_latitude' => $address_latitude,
				'address_longitude' => $address_longitude,
				'description' => $description,
				'about_this' => $about_this,
				'about_this_short' => $about_this_short,
				'url_slug' => $url_slug, 
				'rooms' => $rooms, 
				'max_guests' => $max_guests, 
				'num_bathrooms' => $num_bathrooms, 
				'num_beds' => $num_beds,
				'area_sqm' => $area_sqm, 
				'price_daily' => $price_daily, 
				'ambassador_commission' => $ambassador_commission, 
				'promocode_discount' => $promocode_discount, 
				'user_id' => $user_id, 
				'pricing' => json_encode($pricing), 
				'cleaners_checklists' => json_encode($cleaners_checklists), 
				'facilities' => $facilities,  
				'check_in_time1' => $check_in_time1,  
				'check_in_time2' => $check_in_time2,  
				'check_out_time' => $check_out_time,  
				'flexible_check_in' => $flexible_check_in,  
				'allow_extension' => $allow_extension,  
				'security_features' => $security_features,
				'features_description' => $features_description,
				'checkin_without_host' => $checkin_without_host,  
				'airport_pickup' => $airport_pickup,  
				'scooter_rental' => $scooter_rental,  
				'scooter_rental_fee' => $scooter_rental_fee,
				'flexible_reservation' => $flexible_reservation,  
				'cleaning_fee' => $cleaning_fee,
				'house_rules' => $house_rules,
				'property_safety' => $property_safety,
				'building_id' => $building_id,
				'distinguishing_feature' => $distinguishing_feature,
				'apartment_type' => $apartment_type,
				'price_level' => $price_level,
				'pricing_model' => $pricing_model,
				'seasonal_pricing' => wp_json_encode( $seasonal_pricing ),
				'cleaning_fee_enabled' => $cleaning_fee_enabled,
				'extra_cleaning_fee' => $extra_cleaning_fee,
				'house_rules_json' => wp_json_encode( $house_rules_json ),
				'assigned_staff' => wp_json_encode( $assigned_staff_ids ),
				'status' => $status,
				'datemodified' => date("Y-m-d H:i:s")
				];

		if(trim($display_name) != '') $data['display_name'] = $display_name;

		$existing = $apartment_id > 0 ? $this->get_apartment( $apartment_id ) : [];
		$validate_data = array_merge( is_array( $existing ) ? $existing : [], $data );
		if ( class_exists( 'vvApartmentPlatform' ) ) {
			$publish_check = vvApartmentPlatform::validate_publish( $validate_data, $status );
			if ( is_wp_error( $publish_check ) ) {
				setErrorMsg( $publish_check->get_error_message() );
				$redirect = $apartment_id > 0 ? vv_admin_url( 'apartments/edit/?id=' . intval( $apartment_id ) ) : vv_admin_url( 'apartments/add' );
				wp_redirect( $redirect );
				die();
			}
		}

		//die(print_r_pre($data));

		if($apartment_id > 0){

			$wpdb->update('vv_apartments',$data,['ID' => $apartment_id]);


			$is_new = false;
			setSuccessMsg('Apartment Updated');
		}else{
			$data['dateadded'] = date("Y-m-d H:i:s");

			$wpdb->insert('vv_apartments',$data);

			$apartment_id = $wpdb->insert_id;

			$is_new = true;
			if($apartment_id > 0){
				setSuccessMsg('Apartment Added');
			}else{
				//echo $wpdb->last_error;
				//die();
				setErrorMsg('An error occurred');
			}
		}


		$discount_id 	= POST_Request('discount_id');
		$discount_name = POST_Request('discount_start');
		$discount_start = POST_Request('discount_start');
		$discount_end 	= POST_Request('discount_end');
		$discount 		= POST_Request('discount');

		for($i = 0; $i < count($discount); $i++){
			if($discount[$i] == 'delete' && gArrayItem($discount_id,$i) > 0){
				$wpdb->delete('vv_apartment_discounts',['apt_discount_id' => gArrayItem($discount_id,$i)]);
			}elseif($discount[$i] != ''){
				$data = ['apartment_id' => $apartment_id, 'datestart' => date("Y-m-d",strtotime(gArrayItem($discount_start,$i))), 'dateend' => date("Y-m-d",strtotime(gArrayItem($discount_end,$i))), 'discount' => $discount[$i]];

				//die(print_r_pre($data));

				if(gArrayItem($discount_id,$i) > 0){
					$wpdb->update('vv_apartment_discounts',$data,['apt_discount_id' => gArrayItem($discount_id,$i)]);
				}else{
					$wpdb->insert('vv_apartment_discounts', $data);
				}
			}
		}

		$apt_food_id 	= POST_Request('apt_food_id');
		$food_id 		= POST_Request('food_id');
		$food_name 		= POST_Request('food_name');
		$food_price 	= POST_Request('food_price');

		if(!is_array($food_name)) $food_name = array();

		for($i = 0; $i < count($food_name); $i++){
			if($food_name[$i] == 'delete' && gArrayItem($apt_food_id,$i) > 0){
				$wpdb->delete('vv_apartment_foods',['apt_food_id' => gArrayItem($apt_food_id,$i)]);
			}elseif($food_name[$i] != ''){
				$data = ['apartment_id' => $apartment_id, 'food_id' => gArrayItem($food_id,$i), 'price' => gArrayItem($food_price,$i), 'datemodified' => date("Y-m-d H:i:s")];


				if(gArrayItem($apt_food_id,$i) > 0){
					$wpdb->update('vv_apartment_foods',$data,['apt_food_id' => gArrayItem($apt_food_id,$i)]);
				}else{
					$data['dateadded'] = date("Y-m-d H:i:s");
					$wpdb->insert('vv_apartment_foods', $data);
				}
			}
		}


		$images_url 	= POST_Request('images_url');
		$images_id 		= POST_Request('images_id');
		$images_caption = POST_Request('images_caption');

		$images = [];

		for($i = 0; $i < count($images_url); $i++){
			$images[$i]['order'] 	= $i + 1;
			$images[$i]['thumb'] 	= $images_url[$i];
			$images[$i]['image_id'] = $images_id[$i];
			$images[$i]['caption'] 	= $images_caption[$i];
		}

		$d = [];
		$d['images'] = json_encode($images);

		$apartment = $this->get_apartment($apartment_id);
		if(gArrayItem($apartment,'post_id') == 0){

			$post_id = wp_insert_post(['post_type' => 'apartment','post_title' => $apartment['name'],'post_status' => 'publish']);
			if($post_id > 0){
				$d['post_id'] = $post_id;
			}
		}

		$wpdb->update('vv_apartments',$d,['ID' => $apartment_id]);

		$this->update_apartment_number($apartment_id);

		if ( class_exists( 'vvApartmentPlatform' ) ) {
			$blocked_raw = POST_Request( 'blocked_dates' );
			$blocked_dates = [];
			if ( is_array( $blocked_raw ) ) {
				$blocked_dates = $blocked_raw;
			} elseif ( trim( (string) $blocked_raw ) !== '' ) {
				$blocked_dates = preg_split( '/[\s,]+/', trim( (string) $blocked_raw ) );
			}
			vvApartmentPlatform::save_blocked_dates( $apartment_id, $blocked_dates );
		}

		if ( class_exists( 'vvI18n' ) && is_array( POST_Request( 'i18n' ) ) ) {
			foreach ( POST_Request( 'i18n' ) as $locale => $fields ) {
				if ( ! is_array( $fields ) ) {
					continue;
				}
				$clean = [];
				foreach ( $fields as $key => $value ) {
					$clean[ $key ] = wp_unslash( $value );
				}
				vvI18n::save_apartment_i18n( $apartment_id, $locale, $clean );
			}
		}

		wp_redirect(vv_admin_url('apartments/edit/?id='.$apartment_id));
		die();

	}


	public function add_new_apartments(){
		global $wpdb;

		$user_id 		= POST_Request('user_id');
		$users_class 	= new vvUsers;
		$user 			= $users_class->get_user($user_id);

		if(gArrayItem($user,'ID') > 0){
			$num_apartments = POST_Request('num_apartments');

			for($i = 0; $i < $num_apartments; $i++){
				$data = ['name' => 'New Apartment', 
						'user_id' => $user_id, 
						'pricing' => '[]',
						'status' => 'draft',
						'dateadded' => date("Y-m-d H:i:s"),
						'datemodified' => date("Y-m-d H:i:s")
						];


				$wpdb->insert('vv_apartments',$data);

			}


			setSuccessMsg($num_apartments.' apartments added for '.$user['user_email']);
		}else{
			setErrorMsg('User not found');
		}

		wp_redirect(vv_admin_url('users'));
		die();

	}



	public function get_apartments($filter = array()){
		global $wpdb;

		$debug = 0;

		if($debug == 1) {
			echo print_r_pre($filter);
		}

		$where 		= " 1 ";
		$orderby 	= " ORDER BY a.dateadded DESC ";
		$limit 		= "";

		$per_page_raw = gArrayItem( $filter, 'per_page' );
		$pgnum        = ( gArrayItem( $filter, 'pgnum' ) > 0 ) ? intval( gArrayItem( $filter, 'pgnum' ) ) : 1;
		$fetch_all    = ( $per_page_raw === 'all' );
		$per_page     = $fetch_all ? 0 : ( intval( $per_page_raw ) > 0 ? intval( $per_page_raw ) : 20 );
		$return_total	= intval(gArrayItem($filter,'return_total'));
		$total_rows 	= 0;

		$select = (gArrayItem($filter,'select') != '') ? $filter['select'] : '*';

		if(gArrayItem($filter,'srch') != ''){
			$srch = $filter['srch'];

			$where .= " AND ( name LIKE '%".$srch."%' OR display_name LIKE '%".$srch."%' OR apartment_num LIKE '%".$srch."%' ) ";

		}


		if(gArrayItem($filter,'user_id') > 0) 		$where .= " AND a.user_id = ".$filter['user_id'];

		$status = gArrayItem($filter,'status');
		if($status == 'all'){
		}elseif($status != ''){
			$where .= " AND a.status = '".$status."' ";
		}else{
			$where .= " AND a.status = 'active' ";
		}


		if(gArrayItem($filter,'rooms') != '') $where .= " AND a.rooms >= ".$filter['rooms'];
		if(gArrayItem($filter,'adults') != '' || gArrayItem($filter,'children') != ''){
			$guests = intval(gArrayItem($filter,'adults')) + intval(gArrayItem($filter,'children'));
			$where .= " AND a.max_guests >= ".$guests;
		} 


		$district_ids 	= [];
		$locations 		= [];
		if(gArrayItem($filter,'city_id') > 0){
			$districts = vv_get_districts();
			foreach($districts as $district){
				array_push($district_ids,$district['ID']);
				$locations = vv_get_districts(['parent' => $district['ID']]);
			}
		}elseif(gArrayItem($filter,'district') > 0){
			$districts = vv_get_districts();
			array_push($district_ids,$filter['district']);
			foreach($districts as $district){
				if($district['ID'] == $filter['district']){
					$locations = vv_get_districts(['parent' => $district['ID']]);
				}
			}
		}

		if(count($locations) > 0){
			foreach($locations as $location){
				array_push($district_ids,$location['ID']);
			}
		}


		if(count($district_ids)){
			$where .= " AND a.district IN (".implode(",",$district_ids).") ";
		}
		
		if(gArrayItem($filter,'check_in_date') != '' && gArrayItem($filter,'check_out_date') != ''){

			$where2 = [];

		    $date1 = strtotime(gArrayItem($filter,'check_in_date'));
		    $date2 = strtotime(gArrayItem($filter,'check_out_date'));
		    $dates = array();
		    $ctr = 0;
		    while($date1 < $date2){
		    	array_push($where2," b.dates LIKE '%".date("Y-m-d",$date1)."%' ");
		    	$date1 = strtotime("+1 day",$date1);
		    	$ctr++;
		    	if($ctr > 30) break;
		    }

			$where3 = " AND (SELECT COUNT(*) FROM vv_bookings b WHERE a.ID = b.apartment_id AND (".implode(" OR ",$where2).")  ";

			if(is_array(gArrayItem($filter,'exclude_ids')) && count(gArrayItem($filter,'exclude_ids')) > 0){
				$where3 .= " AND b.ID NOT IN (".implode(",",gArrayItem($filter,'exclude_ids')).")";
			}

			$where3 .= " ) = 0 ";


			$where .= $where3;

			if ( class_exists( 'vvApartmentPlatform' ) ) {
				$date1 = strtotime( gArrayItem( $filter, 'check_in_date' ) );
				$date2 = strtotime( gArrayItem( $filter, 'check_out_date' ) );
				$blocked_ids = [];
				$all_apts = $wpdb->get_col( 'SELECT ID FROM vv_apartments WHERE 1' );
				if ( is_array( $all_apts ) ) {
					foreach ( $all_apts as $apt_id ) {
						if ( vvApartmentPlatform::apartment_has_blocked_range( $apt_id, gArrayItem( $filter, 'check_in_date' ), gArrayItem( $filter, 'check_out_date' ) ) ) {
							$blocked_ids[] = intval( $apt_id );
						}
					}
				}
				if ( ! empty( $blocked_ids ) ) {
					$where .= ' AND a.ID NOT IN (' . implode( ',', $blocked_ids ) . ') ';
				}
			}
		}

		$facility = gArrayItem($filter,'facility');
		if(is_array($facility) && count($facility) > 0){
			$where2 = "";
			foreach($facility as $fid){
				if($where2 != '') $where2 .= " AND ";
				$where2 .= " JSON_CONTAINS(facilities, '[\"".$fid."\"]') ";
			}
			$where .= " AND (".$where2.") ";
		}

		if(gArrayItem($filter,'count_only') == 1){
			$return_total 	= 1;
			$apartments 	= [];
		}else{
			if ( ! $fetch_all && $per_page > 0 ) {
				$offset = $per_page * ( $pgnum - 1 );
				$limit  = ' LIMIT ' . intval( $per_page ) . ' OFFSET ' . intval( $offset );
			}

			$sql 		= "SELECT ".$select." FROM vv_apartments a WHERE ".$where." ".$orderby." ".$limit;
			$apartments = $wpdb->get_results($sql,ARRAY_A);

			if($debug == 1) {
				echo $sql;
				echo print_r_pre($apartments);
			}
		}

		if($return_total){
			$total_rows = gArrayItem($wpdb->get_row("SELECT COUNT(*) as num_rows FROM vv_apartments a WHERE ".$where, ARRAY_A),'num_rows');			
			return ['total_rows' => $total_rows, 'apartments' => $apartments];
		}else{
			return $apartments;
		}
	}


	public function get_apartment($id = 0){
		global $wpdb;

		$apartment = $wpdb->get_row("SELECT * FROM vv_apartments WHERE ID = ".intval($id), ARRAY_A);
		if(gArrayItem($apartment,'ID') > 0){
			$apartment['name'] = stripslashes($apartment['name']);
		}

		return $apartment;
	}



	public function get_apartment_by_post($id = 0){
		global $wpdb;

		$apartment = $wpdb->get_row("SELECT * FROM vv_apartments WHERE post_id = ".intval($id), ARRAY_A);
		if(gArrayItem($apartment,'ID') > 0){
			$apartment['name'] = stripslashes($apartment['name']);
		}

		return $apartment;
	}
	public function get_apartment_by_slug($slug){
		global $wpdb;

		$apartment = $wpdb->get_row("SELECT * FROM vv_apartments WHERE url_slug = '".$slug."' ", ARRAY_A);
		if(gArrayItem($apartment,'ID') > 0){
			$apartment['name'] = stripslashes($apartment['name']);
			if ( class_exists( 'vvI18n' ) && strpos( gArrayItem( $_SERVER, 'REQUEST_URI' ), '/vv-admin' ) === false ) {
				$apartment = vvI18n::merge_apartment_localized( $apartment );
			}
		}

		return $apartment;
	}

	public function get_num_apartments($user_id){
		global $wpdb;
		return intval(gArrayItem($wpdb->get_row("SELECT COUNT(*) as num_apartments FROM vv_apartments WHERE user_id = ".$user_id, ARRAY_A), 'num_apartments'));
	}

	public function get_discounts($apartment_id){
		global $wpdb;

		$discounts = $wpdb->get_results("SELECT * FROM vv_apartment_discounts WHERE apartment_id = ".$apartment_id." ORDER BY datestart DESC ",ARRAY_A);
		if($discounts) return $discounts;
		else return array();
	}

	public function get_discount_val($apartment_id, $date = ''){

		if($date == '') $date = date("Y-m-d");

		$discount_val = 0;
		$discounts = $this->get_discounts($apartment_id);

		//echo print_r_pre($discounts);
		foreach($discounts as $discount){
			$datestart = date("Y-m-d",$discount['datestart']);
			$dateend   = date("Y-m-d",$discount['dateend']);

			if($datestart > 0){
				if($dateend == 0) 		$dateend = date("Y-m-d",strtotime("+100 Years"));

				//echo $date.' > '.$datestart.' && '.$date.' < '.$dateend.' && '.$discount['discount'].' > '.$discount_val.'<br>';
				if($date > $datestart && $date < $dateend && $discount['discount'] > $discount_val){
					//echo 'discounted';
					$discount_val = $discount['discount'];
				}

			}

		}

		return $discount_val;

	}

	public function get_apartment_foods($apartment_id){
		global $wpdb;

		$foods = $wpdb->get_results("SELECT a.apt_food_id, a.price, b.food_id, b.name, b.image, b.image_id  FROM vv_apartment_foods a LEFT JOIN vv_foods b ON a.food_id = b.food_iD WHERE b.food_id IS NOT NULL AND a.apartment_id = ".$apartment_id." ORDER BY b.name ASC ",ARRAY_A);
		if($foods) return $foods;
		else return array();
	}

	public function get_apartment_foods_json(){
		$apartment_id = GET_Request('apartment_id');

		$foods = $this->get_apartment_foods($apartment_id);

		header('Content-Type: application/json; charset=utf-8');
		echo json_encode(['status' => 1, 'data' => $foods]);
		die();
	}

	public function update_apartment_number($apartment_id){
		$apartment = $this->get_apartment($apartment_id);

		if(gArrayItem($apartment,'apartment_num') == ''){
			global $wpdb;

			$date = $apartment['dateadded'];

			if($date == '0000-00-00 00:00:00') $date = $apartment['datemodified'];

			$apartment_num = 'APT'.date("Ymd",strtotime($date)).$apartment_id;
			$wpdb->update('vv_apartments',['apartment_num' => $apartment_num],['ID' => $apartment_id]);
		}
	}



	public function delete_apartment($id = 0){
		global $wpdb;

		$wpdb->delete('vv_apartments',['ID' => $id]);

		setSuccessMsg('Apartment Deleted Successfully');
		wp_redirect(vv_admin_url('apartments'));
		die();
	}

	public function upload_image(){
		global $wpdb;

		$apartment_id = intval( GET_Request( 'id' ) );
		$apartment    = $this->get_apartment( $apartment_id );

		if ( gArrayItem( $apartment, 'ID' ) <= 0 ) {
			die();
		}

		$image = gArrayItem( $_FILES, 'file' );
		if ( gArrayItem( $image, 'tmp_name' ) === '' ) {
			die();
		}

		$upload = wp_handle_upload(
			$_FILES['file'],
			[ 'test_form' => false ]
		);

		if ( ! empty( $upload['error'] ) ) {
			die();
		}

		$attachment_id = wp_insert_attachment(
			[
				'guid'           => $upload['url'],
				'post_mime_type' => $upload['type'],
				'post_title'     => basename( $upload['file'] ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			],
			$upload['file']
		);

		if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
			wp_die( 'Upload error.' );
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';

		wp_update_attachment_metadata(
			$attachment_id,
			wp_generate_attachment_metadata( $attachment_id, $upload['file'] )
		);

		$thumb = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );
		if ( $thumb === '' ) {
			die();
		}

		$images_json = $wpdb->get_var(
			$wpdb->prepare( 'SELECT images FROM vv_apartments WHERE ID = %d', $apartment_id )
		);
		$images = json_decode( $images_json, true );
		if ( ! is_array( $images ) ) {
			$images = [];
		}

		$images[] = [
			'thumb'    => $thumb,
			'image_id' => $attachment_id,
			'order'    => count( $images ) + 1,
		];

		$wpdb->update(
			'vv_apartments',
			[ 'images' => wp_json_encode( $images ) ],
			[ 'ID' => $apartment_id ]
		);

		die();
	}

	function get_apartment_images_html($apartment = []){
		$images = vv_get_apartment_images($apartment);
	    //echo '<pre>'.print_r($images,true).'</pre>';
	    if(count($images) > 0){ 
	        ?>
	        <ul id="imagesTable">
	            <?php 
	            foreach($images as $img){
	                if(gArrayItem($img,'thumb') != ''){
	                    $primary_class  = '';
	                    $primary_url    = vv_admin_url().'?action=set_apartment_primary_img&id='.$apartment['ID'].'&img='.$img['image_id'];
	                    $primary_click  = "return confirm('Set as Primary Image?')";
	                    if(gArrayItem($img,'order') == -100){
	                        $primary_class  = 'text-success';
	                        $primary_url    = '#';
	                        $primary_click  = '';
	                    }

	                    $caption = gArrayItem($img,'caption');
	                    ?>
	                    <li class="filtr-item apartment_img mb-1 bg-white" data-category="" data-sort="">
	                    	<input type="hidden" name="images_url[]" value="<?php echo $img['thumb'] ?>" >
	                    	<input type="hidden" name="images_id[]" value="<?php echo $img['image_id'] ?>" >
	                        <div class="border p-2">
	                            <div class="row">
	                                <div class="col-6 pr-1">
		                                <a href="<?php echo $img['thumb'] ?>" class="apartment_img-thumb" data-toggle="lightbox"  >
		                                    <div style="background-image:url('<?php echo $img['thumb'] ?>')"></div>
		                                </a>
	                                </div>
	                                <div class="col-6 pl-1">
			                        	<div class="text-right"><a href="#" class="handle-sort" style="font-size:20px"><i class="fa fa-sort"></i></a></div>
	                                	<div class="">
		                                	<div class="form-group">
		                                		<label>Caption</label>
		                                    	<input type="text" name="images_caption[]" value="<?php echo $caption ?>" class="form-control form-control-sm" >
		                                    </div>
		                                    <div class="form-group">
		                                    	<a href="<?php echo vv_admin_url().'?action=delete_apartment_image&id='.$apartment['ID'].'&img='.$img['image_id'] ?>" title="Delete" class="delete_img"  onclick="return deleteImage(this)"  ><i class="fa fa-trash" style="font-size:18px"></i></a>
		                                    </div>
		                                </div>
	                                </div>
	                            </div>
	                            <div class="mt-2">
	                            </div>
	                        </div>
	                    </li>
	                    <?php 
	                }
	            }
	            ?>
	        </ul>
	        <?php 
	    } 
	}

	function delete_apartment_image($apartment_id = 0, $image_id = 0){
		$apartment_id = intval( $apartment_id );
		$image_id     = intval( $image_id );

		if ( $apartment_id <= 0 || $image_id <= 0 ) {
			status_header( 400 );
			die();
		}

		$apartment = $this->get_apartment( $apartment_id );
		if ( gArrayItem( $apartment, 'ID' ) <= 0 ) {
			status_header( 404 );
			die();
		}

		if ( class_exists( 'vvApartmentsWizard' ) && ! vvApartmentsWizard::can_access_apartment( $apartment ) ) {
			status_header( 403 );
			die();
		}

		$images = vv_get_apartment_images( $apartment );

		$found   = false;
		$images2 = [];
		foreach ( $images as $img ) {
			if ( intval( gArrayItem( $img, 'image_id' ) ) === $image_id ) {
				$found = true;
				wp_delete_attachment( $image_id, true );
			} else {
				$images2[] = $img;
			}
		}

		if ( $found ) {
			global $wpdb;
			$wpdb->update( 'vv_apartments', [ 'images' => wp_json_encode( $images2 ) ], [ 'ID' => $apartment_id ] );
		}

		header( 'Content-Type: application/json; charset=utf-8' );
		echo wp_json_encode( [ 'success' => $found ] );
		die();
	}




	function apartment_autocomplete(){
		$q = GET_Request('q');

		$filter = [];
		$filter['select'] = 'ID, display_name';
		$filter['srch'] = $q;
		$filter['per_page'] = 20;
		$filter['return_total'] = 0;

		$apartments = $this->get_apartments($filter);

		for($i = 0; $i < count($apartments); $i++){
			$apartments[$i]['value'] = $apartments[$i]['ID'];
			$apartments[$i]['text'] = stripslashes($apartments[$i]['display_name']);
		}

		header("Content-Type: application/json");
		echo json_encode($apartments);
		die();
	}

	public static function status_labels() {
		return [
			'active'  => 'Published',
			'pending' => 'Pending approval',
			'draft'   => 'Deactivated',
		];
	}

	public static function is_publicly_visible( $apartment ) {
		if ( ! is_array( $apartment ) || intval( gArrayItem( $apartment, 'ID' ) ) <= 0 ) {
			return false;
		}

		return gArrayItem( $apartment, 'status' ) === 'active';
	}

	public function create_from_host_application( $user_id, array $property_data, $status = 'pending' ) {
		global $wpdb;

		$user_id = intval( $user_id );
		if ( $user_id <= 0 ) {
			return 0;
		}

		$name = sanitize_text_field( gArrayItem( $property_data, 'property_name' ) );
		if ( $name === '' ) {
			return 0;
		}

		$url_slug  = vv_slugify( strtolower( $name ) );
		$url_slug1 = $url_slug;
		$ctr       = 0;
		do {
			$found = intval( $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM vv_apartments WHERE url_slug = %s',
					$url_slug
				)
			) );
			if ( $found > 0 ) {
				$ctr++;
				$url_slug = $url_slug1 . '-' . $ctr;
			}
		} while ( $found > 0 );

		$property_type = sanitize_text_field( gArrayItem( $property_data, 'property_space_type', gArrayItem( $property_data, 'property_type' ) ) );
		$rooms         = intval( gArrayItem( $property_data, 'property_rooms' ) );
		if ( $rooms <= 0 ) {
			$rooms = vvHostApplications::property_type_rooms( $property_type );
		}

		$max_guests = intval( gArrayItem( $property_data, 'property_max_guests' ) );
		if ( $max_guests <= 0 ) {
			$max_guests = max( 2, $rooms * 2 );
		}

		$num_beds      = intval( gArrayItem( $property_data, 'property_beds' ) );
		$num_bathrooms = intval( gArrayItem( $property_data, 'property_bathrooms' ) );
		$price_daily   = floatval( gArrayItem( $property_data, 'property_price_daily' ) );

		$amenities = array_map( 'intval', (array) gArrayItem( $property_data, 'property_amenities', [] ) );
		$amenities = array_values( array_filter( $amenities ) );

		$images = $this->format_application_images( gArrayItem( $property_data, 'property_images', [] ) );

		$kind_label  = vvHostApplications::place_kind_label( gArrayItem( $property_data, 'property_kind', vvHostApplications::default_property_kind() ) );
		$space_label = vvHostApplications::space_type_label( $property_type );

		$data = [
			'name'             => $name,
			'display_name'     => $name,
			'district'         => intval( gArrayItem( $property_data, 'property_district_id' ) ),
			'address'          => sanitize_textarea_field( gArrayItem( $property_data, 'property_address' ) ),
			'description'      => sanitize_textarea_field( gArrayItem( $property_data, 'property_description' ) ),
			'about_this_short' => trim( $kind_label . ' · ' . $space_label, ' ·' ),
			'url_slug'         => $url_slug,
			'rooms'            => $rooms,
			'max_guests'       => $max_guests,
			'num_beds'         => $num_beds > 0 ? $num_beds : max( 1, $rooms ),
			'num_bathrooms'    => $num_bathrooms > 0 ? $num_bathrooms : 1,
			'price_daily'      => $price_daily,
			'user_id'          => $user_id,
			'facilities'       => wp_json_encode( array_map( 'strval', $amenities ) ),
			'images'           => wp_json_encode( $images ),
			'pricing'          => '[]',
			'status'           => in_array( $status, [ 'active', 'pending', 'draft' ], true ) ? $status : 'pending',
			'dateadded'        => current_time( 'mysql' ),
			'datemodified'     => current_time( 'mysql' ),
		];

		$inserted = $wpdb->insert( 'vv_apartments', $data );
		if ( ! $inserted ) {
			return 0;
		}

		$apartment_id = intval( $wpdb->insert_id );
		if ( $apartment_id > 0 ) {
			$this->update_apartment_number( $apartment_id );
			$apartment = $this->get_apartment( $apartment_id );
			if ( is_array( $apartment ) && intval( gArrayItem( $apartment, 'post_id' ) ) === 0 ) {
				$post_id = wp_insert_post( [
					'post_type'   => 'apartment',
					'post_title'  => $name,
					'post_status' => 'publish',
				] );
				if ( $post_id > 0 ) {
					$wpdb->update( 'vv_apartments', [ 'post_id' => $post_id ], [ 'ID' => $apartment_id ] );
				}
			}
		}

		return $apartment_id;
	}

	public function sync_for_host_application_status( $user_id, $application_status ) {
		global $wpdb;

		$user_id = intval( $user_id );
		if ( $user_id <= 0 ) {
			return;
		}

		if ( in_array( $application_status, [ 'approved', 'activated' ], true ) ) {
			$wpdb->update(
				'vv_apartments',
				[
					'status'       => 'active',
					'datemodified' => current_time( 'mysql' ),
				],
				[
					'user_id' => $user_id,
					'status'  => 'pending',
				]
			);
		} elseif ( $application_status === 'rejected' ) {
			$wpdb->update(
				'vv_apartments',
				[
					'status'       => 'draft',
					'datemodified' => current_time( 'mysql' ),
				],
				[
					'user_id' => $user_id,
					'status'  => 'pending',
				]
			);
		}
	}

	private function format_application_images( $images ) {
		if ( ! is_array( $images ) ) {
			return [];
		}

		$formatted = [];
		$order     = 1;
		foreach ( $images as $image ) {
			if ( ! is_array( $image ) ) {
				continue;
			}
			$thumb = gArrayItem( $image, 'thumb' );
			if ( $thumb === '' ) {
				continue;
			}
			$formatted[] = [
				'order'    => $order,
				'thumb'    => $thumb,
				'image_id' => intval( gArrayItem( $image, 'image_id' ) ),
				'caption'  => sanitize_text_field( gArrayItem( $image, 'caption' ) ),
			];
			$order++;
		}

		return $formatted;
	}


}