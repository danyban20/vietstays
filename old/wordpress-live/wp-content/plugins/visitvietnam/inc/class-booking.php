<?php 

class vvBookings{
    private $daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    private $daysOfWeek2 = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

	public function __construct(){
	}

	public function get_actions($action = ''){
		if($action == 'delete-booking' && GET_Request('id') > 0){
			$this->delete_booking(GET_Request('id'));
		}elseif($action == 'booking-set-status' && GET_Request('id') > 0){
			$this->set_booking_status( intval( GET_Request( 'id' ) ), sanitize_key( GET_Request( 'status' ) ) );
		}elseif($action == 'get_bookings' && GET_Request('mode') == 'json'){
			$this->get_bookings_json();
		}elseif($action == 'check-booking-dates'){

			$filter = $_POST;
			$filter['exclude_ids'] = [POST_Request('booking_id')];

			$result = $this->search_apartment_bookings($filter);

			if(count($result) > 0) $data['status'] = 'available';
			else $data['status'] = 'unavailable';

			header('Content-Type: application/json; charset=utf-8');
			echo json_encode($data);

			die();
		}
	}

	public function post_actions($action = ''){
		if($action == 'booking-save'){
			$this->save_booking();
		}elseif($action == 'host_quick_booking'){
			$this->save_host_quick_booking();
		}elseif($action == 'booking-search'){
			$this->booking_search();
		}elseif($action == 'booking-add_apartment'){
			$this->booking_add_apartment();
		}
	}

	public function booking_search(){
		$booking = gArrayItem($_SESSION,'BOOKING_DATA_ADMIN');
		if(!is_array($booking)) $booking = [];

		$booking['check_in_date'] 	= POST_Request('check_in_date');
		$booking['check_out_date'] 	= POST_Request('check_out_date');
		$booking['search'] 			= POST_Request('search');
		$booking['city_id'] 		= POST_Request('city_id');
		$booking['parent_district'] = POST_Request('parent_district');
		$booking['district_id'] 	= POST_Request('district_id');
		$booking['rooms'] 			= POST_Request('rooms');
		$booking['adults'] 			= POST_Request('adults');
		$booking['children'] 		= POST_Request('children');
		$booking['max_daily_price'] = POST_Request('max_daily_price');


		$_SESSION['BOOKING_DATA_ADMIN'] = $booking;


		if(POST_Request('booking_id') > 0){
			wp_redirect(vv_admin_url('booking/edit').'?id='.POST_Request('booking_id').'&step=2');
		}else{
			wp_redirect(vv_admin_url('booking/add').'?step=2');
		}
		die();
	}

	public function booking_add_apartment(){
		$booking = gArrayItem($_SESSION,'BOOKING_DATA_ADMIN');

		$booking['apartment_id'] 	= POST_Request('apartment_id');

		$_SESSION['BOOKING_DATA_ADMIN'] = $booking;

		if(POST_Request('booking_id') > 0){
			wp_redirect(vv_admin_url('booking/edit').'?id='.POST_Request('booking_id').'&step=3');
		}else{
			wp_redirect(vv_admin_url('booking/add').'?step=3');
		}
		die();
	}

	public function search_apartment_bookings($filter = []){

		global $wpdb;

		$max_guests = intval(gArrayItem($filter,'adults')) + intval(gArrayItem($filter,'children'));

		$where = ' 1 ';

		if(gArrayItem($filter,'rooms') > 0){
			$where .= " AND a.rooms >= ".$filter['rooms'];
		}

		if($max_guests > 0){
			$where .= " AND a.max_guests >= ".$max_guests;
		}


		$district_ids 	= [];
		if(gArrayItem($filter,'district_id') > 0){
			$district_ids[] = $filter['district_id'];
		}elseif(gArrayItem($filter,'parent_district') > 0){
			$district = vv_get_district($filter['parent_district']);
			$locations = gArrayItem($district,'locations');
			if(is_array($locations) && count($locations) > 0){
				foreach($locations as $location){
					$district_ids[] = $location['ID'];
				}
			}
			if(count($district_ids) == 0) $district_ids[] = 0;
		}elseif(gArrayItem($filter,'city_id') > 0){
			$districts = vv_get_districts();
			foreach($districts as $district){
				$district_city_id = get_post_meta($district['ID'],'city',true); // GET CITY ID
				if($district_city_id == $filter['city_id']){
					$locations = gArrayItem($district,'locations');
					if(is_array($locations) && count($locations) > 0){
						foreach($locations as $location){
							$district_ids[] = $location['ID'];
						}
					}
				}
			}
			if(count($district_ids) == 0) $district_ids[] = 0;
		}

		if(count($district_ids) > 0){
			$where .= " AND a.district IN (".implode(",",$district_ids).") ";
		}


		if(gArrayItem($filter,'apartment_id') != ''){
			$where .= " AND a.ID = ".gArrayItem($filter,'apartment_id');
		}

		if(gArrayItem($filter,'search')){
			$s = $filter['search'];
			$where .= " AND ( a.apartment_num LIKE '%".$s."%' OR a.name LIKE '%".$s."%' ) ";
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
		}

		if ( gArrayItem( $filter, 'check_in_date' ) != '' && gArrayItem( $filter, 'check_out_date' ) != '' && class_exists( 'vvApartmentPlatform' ) ) {
			$ci = esc_sql( date( 'Y-m-d', strtotime( gArrayItem( $filter, 'check_in_date' ) ) ) );
			$co = esc_sql( date( 'Y-m-d', strtotime( gArrayItem( $filter, 'check_out_date' ) ) ) );
			$where .= " AND NOT EXISTS (
				SELECT 1 FROM " . vvApartmentPlatform::TABLE_BLOCKED . " bd
				WHERE bd.apartment_id = a.ID
				AND bd.block_date >= '{$ci}'
				AND bd.block_date < '{$co}'
			) ";
		}

		$sql = "SELECT a.* FROM vv_apartments a WHERE ".$where;


		$result = $wpdb->get_results($sql,ARRAY_A);

		//echo $sql;
		//echo print_r_pre($result);


		return $result;

	}

	public function save_booking($redirect = true){

		global $wpdb;

		if(POST_Request('source') == 'admin'){
			$booking = gArrayItem($_SESSION,'BOOKING_DATA_ADMIN');

			foreach($_POST as $i => $v){
				$booking[$i] = $v;
			}

			$booking['airport_pickup'] 		= POST_Request('airport_pickup');
			$booking['num_cleaning']		= POST_Request('num_cleaning');
			$booking['num_scooter_rental']	= POST_Request('num_scooter_rental');
			$booking['email']				= POST_Request('email');
			$booking['firstname']			= POST_Request('firstname');
			$booking['lastname']			= POST_Request('lastname');
			$booking['user_id']				= POST_Request('user_id');
			$booking['promo_code']			= POST_Request('promo_code');
			$booking['promo_code_added']	= POST_Request('promo_code_added');
			$booking['promo_code_discount']	= POST_Request('promo_code_discount');
			$booking['ambassador_id']		= POST_Request('ambassador_id');

			if(POST_Request('promo_code') != ''){

				$return = vv_booking_check_promo_code(['promocode' => trim(POST_Request('promo_code')), 'apartment_id' => POST_Request('apartment_id'), 'return' => true]);
				if(gArrayItem($return,'found') ){
					$booking['ambassador_id'] 			= $return['user_id'];
					$booking['promo_code'] 				= POST_Request('promo_code');
					$booking['promo_code_added'] 		= 1;
				}
			}

			if(GET_Request('isajax') == 1){
				$_SESSION['BOOKING_DATA_ADMIN'] = $booking;
				die();			
			}
		}

		$booking_id 	= POST_Request('booking_id');

		$user_id 		= intval(POST_Request('user_id'));
		$do_add 		= true;

		$status 		= 'error';
		$success_msg 	= '';
		$error_msg 		= '';

		if($user_id == 0 && POST_Request('register_user') == 1){
			$user_class = new vvUsers;
			$returnval = $user_class->register(['email' => POST_Request('email'), 'firstname' => POST_Request('firstname'), 'lastname' => POST_Request('lastname')]);

			if(intval(gArrayItem($returnval,'status')) == 0){
				if(intval(gArrayItem($returnval,'user_id')) > 0){
					$user_id = gArrayItem($returnval,'user_id');
				}else{
					$error_msg .= implode("<br>",gArrayItem($returnval,'error_msg'));					
					$do_add = false;
				}
			}else{
				$user_id = gArrayItem($returnval,'user_id');
			}

		}


		if($do_add == true){

		    $date1 = new DateTime(POST_Request('check_in_date'));
		    $date2 = new DateTime(POST_Request('check_out_date'));


		    $dates = array();
		    while($date1 < $date2){
		    	array_push($dates, $date1->format("Y-m-d"));
		    	$date1 = (new DateTime($date1->format("Y-m-d")))->modify("+1 Day");
		    }

		    $apartment_id 				= POST_Request('apartment_id');
		    $apartment_class 			= new vvApartments;
		    $apartment 					= $apartment_class->get_apartment($apartment_id);

			$data           			= vv_get_booking_pricing_data($apartment_id, POST_Request('check_in_date'), POST_Request('check_out_date'));
			$label          			= gArrayItem($data,'label');
			$subtotal       			= floatval(gArrayItem($data,'total'));
			$basic_discount 			= floatval(gArrayItem($data,'basic_discount'));
			$campaign_discount   		= floatval(gArrayItem($data,'campaign_discount'));
			$campaign_discount_desc 	= gArrayItem($data,'campaign_discount_desc');
			$promo_code_added 			= intval(gArrayItem($data,'promo_code_added'));
			$dprices2 					= gArrayItem($data,'dprices2');


		    $bookingdata 							= array();
		    $bookingdata['user_id'] 				= $user_id;
		    $bookingdata['email'] 					= POST_Request('email');
		    $bookingdata['firstname'] 				= POST_Request('firstname');
		    $bookingdata['lastname'] 				= POST_Request('lastname');
		    $bookingdata['check_in_date' ] 			= POST_Request('check_in_date');
		    $bookingdata['check_out_date' ] 		= POST_Request('check_out_date');
		    $bookingdata['adults' ] 				= POST_Request('adults');
		    $bookingdata['children' ]				= POST_Request('children');
		    $bookingdata['child_ages' ] 			= json_encode(POST_Request('child_age'));
		    $bookingdata['district_id'] 			= intval(POST_Request('district_id'));
		    $bookingdata['apartment_id'] 			= intval(POST_Request('apartment_id'));
		    $bookingdata['datemodified'] 			= date("Y-m-d H:i:s");
		    $bookingdata['dates'] 					= json_encode($dates);
		    $bookingdata['basic_discount'] 			= $basic_discount;
		    $bookingdata['campaign_discount'] 		= $campaign_discount;
		    $bookingdata['campaign_discount_desc'] 	= json_encode($campaign_discount_desc);
		    $bookingdata['ambassador_id']			= intval(POST_Request('ambassador_id'));
		    $bookingdata['ambassador_commission']	= intval(POST_Request('ambassador_commission'));
		    $bookingdata['promo_code']				= POST_Request('promo_code');
		    $bookingdata['promo_code_discount'] 	= POST_Request('promo_code_discount');
		    $bookingdata['booking_fee'] 			= POST_Request('booking_fee');
		    $bookingdata['total'] 					= 0;

		    if($booking_id > 0){
		    	$wpdb->update('vv_bookings',$bookingdata,['ID' => $booking_id]);

				$success_msg 	= 'Booking Updated';
				$is_new 		= false;
				$status 		= 'success';
		    }else{
			    $bookingdata['user_id'] 			= $user_id;
			    $bookingdata['status' ] 			= 'pending';
			    $bookingdata['dateadded'] 			= date("Y-m-d H:i:s");

			    if(!$wpdb->insert('vv_bookings',$bookingdata)){
			    	$error_msg .= 'Error: '.$wpdb->last_error;
			    }else{
				    $booking_id 	= $wpdb->insert_id;
				    $is_new 		= true;
					$success_msg 	= 'Booking Added';
					$status 		= 'success';

					if ( class_exists( 'vvConversions' ) ) {
						vvConversions::mark_booking_converted( $booking_id, gArrayItem( $bookingdata, 'promo_code' ) );
					}

					$_SESSION['BOOKING_DATA_ADMIN'] = [];

			    }
		    }

		    //die(print_r_pre($bookingdata));

		    if($booking_id > 0){

		    	$dateadded 		= date("Y-m-d H:i:s");
		    	$datemodified 	= date("Y-m-d H:i:s");

		    	$item_prices 	= POST_Request('item_price');
		    	$item_qtys 		= POST_Request('item_qty');
		    	$item_names 	= POST_Request('item_name');

		    	foreach($item_prices as $i => $price){
		    		$data = [];
		    		$data['price']			= vv_currency_to_decimal($price);
		    		$data['qty'] 			= $item_qtys[$i];
		    		$data['datemodified'] 	= $datemodified;

		    		if($is_new){
			    		$data['name'] 			= $item_names[$i];
			    		$data['booking_id'] 	= $booking_id;
			    		$data['code'] 			= 'booking-date';
			    		$data['item_type'] 		= 'booking-date';
			    		$data['dateadded']		= $dateadded;
		    			$wpdb->insert('vv_booking_items',$data);
		    		}else{
		    			$wpdb->update('vv_booking_items',$data,['item_id' => intval($i)]);
		    		}
		    	}

				if(POST_Request('airport_pickup') > 0){
					$airport_pickup_cost    = floatval(vv_get_config('airport_pickup_cost'));
					$airport_pickup 		= $wpdb->get_row("SELECT * FROM vv_booking_items WHERE booking_id = ".$booking_id." AND code = 'airport_pickup' ",ARRAY_A);
					if(gArrayItem($airport_pickup,'item_id') > 0){
						// NO CHANGES
					}else{
			    		$wpdb->insert(	'vv_booking_items', 
			    						[	'booking_id' 	=> $booking_id, 
											'code' 			=> 'airport_pickup', 
											'item_type' 	=> 'service', 
											'name' 			=> 'Airport Pick-up', 
											'qty' 			=> 1, 
											'price' 		=> $airport_pickup_cost, 
											'dateadded' 	=> $dateadded, 
											'datemodified' 	=> $datemodified
										  ]);
			    	}
				}else{
					if(!$is_new){
						$wpdb->delete('vv_booking_items',['booking_id' => $booking_id, 'code' => 'airport_pickup']);
					}
				}

				if(POST_Request('num_cleaning') > 0){
					$cleaning 		= $wpdb->get_row("SELECT * FROM vv_booking_items WHERE booking_id = ".$booking_id." AND code = 'cleaning_fee' ",ARRAY_A);
					if(gArrayItem($cleaning,'item_id') > 0){
			    		$wpdb->update(	'vv_booking_items', 
			    						[ 'qty' 			=> POST_Request('num_cleaning'), 
										  'datemodified' 	=> $datemodified
										],
										['item_id' => $cleaning['item_id']]
										);
			    	}else{
						$cleaning_fee    = floatval(gArrayItem($apartment,'cleaning_fee'));
			    		$wpdb->insert(	'vv_booking_items', 
			    						[ 'booking_id' 	=> $booking_id, 
										  'code' 		=> 'cleaning_fee', 
										  'item_type' 	=> 'service', 
										  'name' 		=> 'Extra Cleaning Service', 
										  'qty' 		=> POST_Request('num_cleaning'), 
										  'price' 		=> $cleaning_fee, 
										  'dateadded' 	=> $dateadded, 
										  'datemodified' => date("Y-m-d H:i:s")
										]);
			    	}
				}else{
					if(!$is_new){
						$wpdb->delete('vv_booking_items',['booking_id' => $booking_id, 'code' => 'cleaning_fee']);
					}
				}

				if(POST_Request('num_scooter_rental') > 0){

					$scooter_rental 		= $wpdb->get_row("SELECT * FROM vv_booking_items WHERE booking_id = ".$booking_id." AND code = 'scooter_rental' ",ARRAY_A);
					if(gArrayItem($scooter_rental,'item_id') > 0){
			    		$wpdb->update(	'vv_booking_items', 
			    						[ 'qty' 			=> POST_Request('num_scooter_rental'), 
										  'datemodified' 	=> $datemodified
										],
										['item_id' => $scooter_rental['item_id']]
										);
			    	}else{
						$scooter_rental_fee    = floatval(gArrayItem($apartment,'scooter_rental_fee'));
			    		$wpdb->insert(	'vv_booking_items', 
			    						[ 'booking_id' 	=> $booking_id, 
										  'code' 		=> 'scooter_rental', 
										  'item_type' 	=> 'service', 
										  'name' 		=> 'Scooter Rental', 
										  'qty' 		=> POST_Request('num_scooter_rental'), 
										  'price' 		=> $scooter_rental_fee, 
										  'dateadded' 	=> $dateadded, 
										  'datemodified' => date("Y-m-d H:i:s")
										]);
			    	}
				}else{
					if(!$is_new){
						$wpdb->delete('vv_booking_items',['booking_id' => $booking_id, 'code' => 'scooter_rental']);
					}
				}

		    	$food_ids = POST_Request('food_id');
		    	if(!is_array($food_ids)) $food_ids = array();

				if(!$is_new){
					// DELETE EXISTING FOODS THAT ARE NOT FOUND IN POST DATA
			    	$booking_items = $this->get_booking_items($booking_id);
			    	foreach($booking_items as $booking_item){
			    		if($booking_item['item_type'] == 'food'){
				    		$found = false;
				    		foreach($food_ids as $food_id){
				    			if($food_id == $booking_item['item_id']) $found = true;
				    		}
				    		if(!$found){
				    			$wpdb->delete('vv_booking_items',['item_id' => $booking_item['item_id']]);
				    		}
				    	}
			    	}
			    }

		    	$ctr = 0;
		    	foreach($food_ids as $food_id){
		    		$qty 		= intval(gArrayItem(POST_Request('food_qty'),$ctr));
		    		$price 		= gArrayItem(POST_Request('food_price'),$ctr);
	    			$name 		= gArrayItem(POST_Request('food_name'),$ctr);
		    		$code 		= gArrayItem(POST_Request('food_code'),$ctr);

		    		$total = $total + ($qty  * $price);

		    		if($food_id > 0){

		    			$wpdb->update('vv_booking_items',['qty' => $qty, 'price' => $price, 'datemodified' => date("Y-m-d H:i:s")], ['item_id' => $food_id]);

		    		}elseif($food_id == 'new'){

			    		$wpdb->insert('vv_booking_items', [	'booking_id' 	=> $booking_id, 
			    											'code' 			=> $code, 
			    											'item_type' 	=> 'food', 
			    											'name' 			=> $name, 
			    											'qty' 			=> $qty, 
			    											'price' 		=> vv_currency_to_decimal($price), 
			    											'dateadded' 	=> $dateadded, 
			    											'datemodified' 	=> $datemodified
			    										  ]);

		    		}

		    		$ctr++;
		    	}
				
				$this->calculate_booking_total($booking_id);		
		    }


		    //echo $error_msg;
		    //die('1');

		    // UPDATE BOOKING NUMBER
		    if($is_new){
			    $booking = $this->get_booking($booking_id);
			    $this->generate_booking_num($booking);
			}

		    if($is_new == true || POST_Request('resent_booking_confirmation') == 1){
		    	$email_templates = new vvEmailTemplates;
		    	$email_templates->send_booking_confirmation_to_user($booking_id);
		    }
		}
	    if($redirect){
	    	if($error_msg != '') 	setErrorMsg($error_msg);
	    	if($success_msg != '')	setSuccessMsg($success_msg);

	    	wp_redirect(vv_admin_url('booking'));
	    }else{
	    	return ['status' => $status, 'success_msg' => $success_msg, 'error_msg' => $error_msg, 'booking_id' => $booking_id];
	    }

		
		die();

	}

	public function generate_booking_num($booking){
		global $wpdb;

		$booking_num = date("Ym",strtotime(gArrayItem($booking,'dateadded'))).sprintf("%05d", gArrayItem($booking,'ID'));
		$wpdb->update('vv_bookings',['booking_num' => $booking_num], ['ID' => $booking['ID']]);
		return $booking_num;
	}

	public function calculate_booking_total($booking_id){
		global $wpdb;
		$booking = $this->get_booking($booking_id);

		if(gArrayItem($booking,'ID') > 0){
			$items = $this->get_booking_items($booking_id);

			$subtotal 		= 0;
			$services_total = 0;
			$foods_total 	= 0;
			foreach($items as $item){
				if(gArrayItem($item,'item_type') == 'booking-date'){
					$subtotal += ($item['qty'] * $item['price']);
				}
				if(gArrayItem($item,'item_type') == 'service'){
					$services_total += ($item['qty'] * $item['price']);
				}
				if(gArrayItem($item,'item_type') == 'food'){
					$foods_total += ($item['qty'] * $item['price']);
				}
			}

            $has_discount = false;
            if(gArrayItem($booking,'campaign_discount') > 0){
            	$campaign_discount 		= gArrayItem($booking,'campaign_discount');
            	$campaign_discount_desc = gArrayItem($booking,'campaign_discount_desc');
            	$subtotal -= $campaign_discount;
            }
            if(gArrayItem($booking,'basic_discount') > 0){
            	$basic_discount 		= gArrayItem($booking,'basic_discount');
            	$basic_discount_val 	= $subtotal * ($basic_discount/100);
            	$subtotal -= $basic_discount_val;
            }
            if(gArrayItem($booking,'promo_code_discount') > 0){
            	$promo_code_discount 		= gArrayItem($booking,'promo_code_discount');
            	$promo_code_discount_val 	= $subtotal * ($promo_code_discount/100);
            	$subtotal -= $promo_code_discount_val;
            }
            if(gArrayItem($booking,'booking_fee') > 0){
            	$booking_fee 		= gArrayItem($booking,'booking_fee');
            	$booking_fee_val 	= $subtotal * ($booking_fee/100);
            	$subtotal += $booking_fee_val;
            }

            $subtotal += $services_total + $foods_total;


            $wpdb->update('vv_bookings',['total' => $subtotal],['ID' => $booking_id]);
		}
	}


	public function get_booking_items($booking_id = 0){
		global $wpdb;

		$result = $wpdb->get_results("SELECT * FROM vv_booking_items WHERE booking_id = ".$booking_id." ORDER BY dateadded",ARRAY_A);
		if(!is_array($result)) $result = array();

		return $result;

	}
    public function getMoveBookingCalendarHtml($month = null, $year = null, $booking = [], $bookings = []) {
    	//echo print_r_pre($bookings);
    	global $wpdb;

    	$calendar_action = gArrayItem($extra_data,'action');

        if ($month === null) $month = date('m');
        if ($year === null) $year = date('Y');

        $firstDayOfMonth 	= strtotime("$year-$month-01");
        $totalDays 			= date('t', $firstDayOfMonth);
        $startDay 			= date('w', $firstDayOfMonth);
        
        $calendar_html = "<table class='moveBookingCalendar'>";
        $calendar_html .= '<thead>';
        $calendar_html .= "<tr class='calendar-header'><th colspan='7'><div>" . date('F Y', $firstDayOfMonth) . "</div></th></tr>";
        $calendar_html .= "<tr>";
        $details_html = '';
        
        foreach ($this->daysOfWeek as $day) {
            $calendar_html .= "<th>".strtoupper($day)."</th>";
        }
        $calendar_html .= "</tr>";
        $calendar_html .= '</thead>';
        $calendar_html .= '<tbody><tr>';

        for ($i = 0; $i < $startDay; $i++) {
            $calendar_html .= "<td class='empty'></td>";
        }

        for ($day = 1; $day <= $totalDays; $day++) {
            if (($i % 7) == 0) {
                $calendar_html .= "</tr><tr>";
            }

            $cdate = date("Y-m-d",mktime(0,0,0,$month,$day,$year));

            $calendar_html .= '<td class="day';
            if( $cdate == date("Y-m-d")) $calendar_html .= ' today ';

            $num_found = 0;

            foreach($bookings as $booking2){
            	$dates = json_decode(gArrayItem($booking2,'dates'),true);
            	//$calendar_html .= print_r_pre($dates);
            	if(!is_array($dates)) $dates = [];
            	foreach($dates as $date2){
            		$calendar_html .= $date2.' == '>$cdate;
            		if($date2 == $cdate) $num_found++;
            	}
            }

            if($num_found > 0) $calendar_html .= '  not-available ';

            $calendar_html .= ' num_found_'.$num_found;

            $calendar_html .=  '"  >';

            $calendar_html .= '<div class="task">';


            $calendar_html .= '<span class="daynum">'.$day.'</span>';
            if($num_found == 0){
            	$calendar_html .= '<input type="checkbox" name="schedule[]" value="'.$cdate.'">';
            }
            //$calendar_html .= date("Y-m-d",mktime(0,0,0,$month,$day,$year)).' == '.date("Y-m-d");
            $calendar_html .= '</div>';
            $calendar_html .= "</td>";
            $i++;
        }

        while (($i % 7) != 0) {
            $calendar_html .= "<td class='empty'></td>";
            $i++;
        }

        $calendar_html .= "</tr></tbody></table>";



        return $calendar_html;
    }


	public function get_bookings_json(){
		global $wpdb;

		$month 	= GET_Request('month');
		$year 	= GET_Request('year');

		$districts 	= vv_get_districts();
		$apartments = vv_get_apartments();

		if($month == '') 	$month = date("n");
		if($year == '') 	$year = date("Y");

		$start_date = mktime(0,0,0,$month,1,$year);
		$end_date = mktime(0,0,0,$month,date("t"),$year);

		$bookings = $this->get_bookings(['start_date' => $start_date, 'end_date' => $end_date]);

		$data = array();
		foreach($bookings as $booking){
			$url = vv_admin_url().'booking/edit/?id='.$booking['ID'];

			$apartment_name = '';
			foreach($apartments as $apartment){
				if($booking['apartment_id'] == $apartment['ID']) $apartment_name = $apartment['name'];
			}
			$district_name = '';
			foreach($districts as $district){
				if($booking['district_id'] == $district['ID']) $district_name = $district['post_title'];
			}

			array_push($data, [	'title' => $district_name.' - '.$apartment_name."\n".$booking['firstname'].' '.$booking['lastname'], 
								'start' => date("Y-m-d",$booking['check_in_date']), 
								'end' => date("Y-m-d",$booking['check_out_date']), 
								'url' => $url
							]);
		}
		
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($data);

		die();

	}

	public function get_bookings($filter = array()){
		global $wpdb;

		$debug 		= GET_Request('debug');

		if($debug) echo print_r_pre($filter);

		$where 		= " 1 ";
		$orderby 	= " ORDER BY a.dateadded DESC ";
		$limit 		= "";

		$per_page 		= intval( gArrayItem( $filter, 'per_page' ) );
		$pgnum 			= max( 1, intval( gArrayItem( $filter, 'pgnum' ) ) );
		$return_total	= intval(gArrayItem($filter,'return_total'));
		$total_rows 	= 0;
		$total_earnings = 0;
		$apartment_ids 	= [];
		$bookings 		= [];
		$join 			= "";

		if(gArrayItem($filter,'user_id') > 0) 				$where .= " AND a.user_id = ".$filter['user_id'];
		if ( trim( gArrayItem( $filter, 'overlap_start' ) ) != '' && trim( gArrayItem( $filter, 'overlap_end' ) ) != '' ) {
			$where .= " AND a.check_in_date <= '" . esc_sql( $filter['overlap_end'] ) . "' AND a.check_out_date > '" . esc_sql( $filter['overlap_start'] ) . "' ";
		} else {
			if(trim(gArrayItem($filter,'start_date')) != '') 	$where .= " AND a.check_in_date >= '".$filter['start_date']."' ";
			if(trim(gArrayItem($filter,'end_date')) != '') 		$where .= " AND a.check_in_date <= '".$filter['end_date']."' ";
		}

		if(trim(gArrayItem($filter,'check_in_date')) != '') 	$where .= " AND a.check_in_date >= '".$filter['check_in_date']."' ";
		if(trim(gArrayItem($filter,'check_out_date')) != '') 	$where .= " AND a.check_out_date <= '".$filter['check_out_date']."' ";

		if(trim(gArrayItem($filter,'search')) != ''){
			$srch = $filter['search'];
			$where .= " AND ( a.booking_num LIKE '%".$srch."%' OR b.name LIKE '%".$srch."%' ";

			$join 	.= " LEFT JOIN $wpdb->users d ON a.user_id = d.ID ";
			$where .= " OR d.user_email LIKE '%".$srch."%' ";

			$where .= " OR a.user_id IN ( 	SELECT user_id 
											FROM $wpdb->usermeta um1 
											WHERE  ( um1.meta_key = 'first_name' AND um1.meta_value LIKE '%".$srch."%' ) 
												OR ( um1.meta_key = 'last_name' AND um1.meta_value LIKE '%".$srch."%' )
										)";

			$where .= " OR a.firstname LIKE '%".$srch."%' OR a.lastname LIKE '%".$srch."%' OR a.email LIKE '%".$srch."%' ";
			$where .= " ) ";
		}

		if(trim(gArrayItem($filter,'ambassador_id'))  > 0) 	$where .= " AND a.ambassador_id  = ".$filter['ambassador_id'];

		$date1 = '';
		$year = gArrayItem($filter,'year');
		if($year > 0){
			if(gArrayItem($filter,'month') != ''){
				$date1 = date($year."-".$filter['month'].'-01');
			}else{
				$date1 = date($year."-01-01");
			}
			$where .= " AND a.check_in_date >= '".$date1."' AND a.check_in_date < '".date("Y-m-d",strtotime("+1 Month",strtotime($date1)))."' ";

		}else{
			if(gArrayItem($filter,'month') != ''){
				$year = date("Y");
				$date1 = date($year."-".$filter['month'].'-01');
				$where .= " AND a.check_in_date >= '".$date1."' AND a.check_in_date < '".date("Y-m-d",strtotime("+1 Month",strtotime($date1)))."' ";
			}
		}

		$district_ids 	= [];
		if(gArrayItem($filter,'district_id') > 0){
			$district_ids[] = $filter['district_id'];
		}elseif(gArrayItem($filter,'parent_district') > 0){
			$district = vv_get_district($filter['parent_district']);
			$locations = gArrayItem($district,'locations');
			if(is_array($locations) && count($locations) > 0){
				foreach($locations as $location){
					$district_ids[] = $location['ID'];
				}
			}
			if(count($district_ids) == 0) $district_ids[] = 0;
		}elseif(gArrayItem($filter,'city_id')){
			$districts = vv_get_districts();
			foreach($districts as $district){
				$district_city_id = get_post_meta($district['ID'],'city_id',true); // GET CITY ID
				if($district_city_id == $filter['city_id']){
					$locations = gArrayItem($district,'locations');
					if(is_array($locations) && count($locations) > 0){
						foreach($locations as $location){
							$district_ids[] = $location['ID'];
						}
					}
				}
			}
			if(count($district_ids) == 0) $district_ids[] = 0;
		}

		if(count($district_ids) > 0){
			$where .= " AND a.district_id IN (".implode(",",$district_ids).") ";
		}

		if(is_array(gArrayItem($filter,'apartment_ids'))){
			 $where .= " AND a.apartment_id IN (".implode(",",$filter['apartment_ids']).") ";
		}
		if(is_array(gArrayItem($filter,'exclude_ids'))){
			 $where .= " AND a.ID NOT  IN (".implode(",",$filter['exclude_ids']).") ";
		}


		if(gArrayItem($filter,'apartment_owner') > 0){
			$where .= " AND b.user_id = ".$filter['apartment_owner'];
		}

		$tab_sql = self::booking_tab_sql( sanitize_key( gArrayItem( $filter, 'booking_tab' ) ) );
		if ( $tab_sql !== '' ) {
			$where .= ' AND ' . $tab_sql;
		}

		if ( trim( gArrayItem( $filter, 'status' ) ) !== '' ) {
			$where .= " AND a.status = '" . esc_sql( sanitize_key( $filter['status'] ) ) . "' ";
		}

		if(gArrayItem($filter,'with_commissions') == 1){
			$where .= " AND a.ambassador_id > 0 AND a.ambassador_commission > 0 ";
		}



		if($return_total == 1){
			$sql = "SELECT COUNT(DISTINCT(a.ID)) as num_rows FROM vv_bookings a
					LEFT JOIN vv_apartments b ON a.apartment_id = b.ID 
					LEFT JOIN $wpdb->posts c ON a.district_id = c.ID  
					".$join."
					WHERE ".$where;

			$total_rows = gArrayItem($wpdb->get_row($sql, ARRAY_A),'num_rows');		
		}

		if(gArrayItem($filter,'return_total_earnings') == 1){
			$sql = "SELECT SUM(a.total) as total_earnings 
					FROM vv_bookings a 
					LEFT JOIN vv_apartments b ON a.apartment_id = b.ID 
					LEFT JOIN $wpdb->posts c ON a.district_id = c.ID  
					".$join."
					WHERE ".$where;
			//echo $sql;
			$total_earnings = gArrayItem($wpdb->get_row($sql, ARRAY_A),'total_earnings');			
		}

		if($per_page > 0){
			$offset 	= $per_page * ($pgnum - 1);
			$limit  	= " LIMIT ".$per_page." OFFSET ".$offset;
		}

		if(gArrayItem($filter,'return_total_only') == 1){
			$bookings = [];
		}else{
			$sql 		= "	SELECT a.*,b.name as apartment_name, b.display_name as apartment_display_name, c.post_title as district_name 
							FROM vv_bookings a 
							LEFT JOIN vv_apartments b ON a.apartment_id = b.ID 
							LEFT JOIN $wpdb->posts c ON a.district_id = c.ID 
							".$join."
							WHERE ".$where." ".$orderby." ".$limit;

			$bookings 	= $wpdb->get_results($sql,ARRAY_A);

			if($debug == 1) {
				echo $sql;
			}

		}


		if($debug == 1) {
			echo print_r_pre($bookings);
		}
		if($return_total){
			return ['total_rows' => $total_rows, 'bookings' => $bookings, 'total_earnings' => $total_earnings];
		}else{
			return $bookings;
		}
	}

	public function get_booking($id = 0){
		global $wpdb;

		$booking = $wpdb->get_row("SELECT * FROM vv_bookings WHERE ID = ".intval($id), ARRAY_A);

		return $booking;
	}



	public function count_rooms($booking_id){
		global $wpdb;

		return intval(gArrayItem($wpdb->get_row("SELECT COUNT(*) as num_rows FROM vv_rooms WHERE booking_id = ".$booking_id,ARRAY_A),'num_rows'));
	}

	public function get_num_bookings($user_id){
		global $wpdb;
		return intval(gArrayItem($wpdb->get_row("SELECT COUNT(*) as num_bookings FROM vv_bookings WHERE user_id = ".$user_id, ARRAY_A), 'num_bookings'));
	}

	public function delete_booking($id = 0){
		global $wpdb;

		$wpdb->delete('vv_bookings',['ID' => $id]);
		$wpdb->delete('vv_booking_items',['booking_id' => $id]);

		setSuccessMsg('Booking Deleted Successfully');
		wp_redirect(vv_admin_url('booking'));
		die();
	}

	public static function booking_tab_sql( $tab ) {
		$today        = esc_sql( date( 'Y-m-d' ) );
		$urgent_until = esc_sql( date( 'Y-m-d', strtotime( '+5 days' ) ) );

		switch ( $tab ) {
			case 'active':
				return "a.check_in_date <= '{$today}' AND a.check_out_date > '{$today}' AND a.status IN ('paid','confirmed','completed')";
			case 'upcoming':
				return "a.check_in_date > '{$today}' AND a.status NOT IN ('cancelled')";
			case 'past':
				return "a.check_out_date < '{$today}'";
			case 'pending':
				return "a.status = 'pending'";
			case 'urgent':
				return "a.status NOT IN ('cancelled') AND (
					(a.check_in_date >= '{$today}' AND a.check_in_date <= '{$urgent_until}')
					OR (a.status = 'pending' AND a.check_in_date <= '{$urgent_until}')
				)";
			default:
				return '';
		}
	}

	public function get_host_booking_dashboard( $host_id ) {
		global $wpdb;

		$host_id = intval( $host_id );
		if ( $host_id <= 0 ) {
			return [
				'tab_counts'     => [],
				'revenue_30'     => 0,
				'host_net_30'    => 0,
				'total_bookings' => 0,
			];
		}

		$owner_sql = ' AND b.user_id = ' . $host_id;
		$today     = date( 'Y-m-d' );
		$past_30   = date( 'Y-m-d', strtotime( '-30 days' ) );

		$tab_counts = [ 'all' => 0, 'active' => 0, 'upcoming' => 0, 'past' => 0, 'pending' => 0, 'urgent' => 0 ];
		foreach ( array_keys( $tab_counts ) as $tab ) {
			$tab_sql = self::booking_tab_sql( $tab === 'all' ? '' : $tab );
			$where   = '1' . $owner_sql;
			if ( $tab_sql !== '' ) {
				$where .= ' AND ' . $tab_sql;
			}
			$tab_counts[ $tab ] = intval(
				gArrayItem(
					$wpdb->get_row(
						"SELECT COUNT(DISTINCT a.ID) AS num_rows FROM vv_bookings a
						LEFT JOIN vv_apartments b ON a.apartment_id = b.ID
						WHERE {$where}",
						ARRAY_A
					),
					'num_rows'
				)
			);
		}

		$revenue_30 = floatval(
			gArrayItem(
				$wpdb->get_row(
					"SELECT SUM(a.total) AS total_earnings FROM vv_bookings a
					LEFT JOIN vv_apartments b ON a.apartment_id = b.ID
					WHERE 1 {$owner_sql}
					AND a.check_in_date >= '" . esc_sql( $past_30 ) . "'
					AND a.check_in_date <= '" . esc_sql( $today ) . "'
					AND a.status IN ('paid','confirmed','completed')",
					ARRAY_A
				),
				'total_earnings'
			)
		);

		$fee_pct     = floatval( vv_get_config( 'platform_fee_percent' ) );
		if ( $fee_pct <= 0 ) {
			$fee_pct = 5;
		}
		$host_net_30 = max( 0, $revenue_30 * ( 1 - ( $fee_pct / 100 ) ) );

		return [
			'tab_counts'     => $tab_counts,
			'revenue_30'     => $revenue_30,
			'host_net_30'    => $host_net_30,
			'total_bookings' => intval( $tab_counts['all'] ),
		];
	}

	public function host_can_manage_booking( $booking_id, $user_id = 0 ) {
		$user_id = intval( $user_id );
		if ( $user_id <= 0 ) {
			$user_id = get_current_user_id();
		}
		if ( $user_id <= 0 ) {
			return false;
		}
		if ( function_exists( 'vv_is_admin_role' ) && vv_is_admin_role( $user_id ) ) {
			return true;
		}
		$booking = $this->get_booking( intval( $booking_id ) );
		if ( ! is_array( $booking ) || gArrayItem( $booking, 'ID' ) <= 0 ) {
			return false;
		}
		$apartment = vv_get_apartment( intval( gArrayItem( $booking, 'apartment_id' ) ) );
		return intval( gArrayItem( $apartment, 'user_id' ) ) === $user_id;
	}

	public function set_booking_status( $booking_id, $status ) {
		global $wpdb;

		$booking_id = intval( $booking_id );
		$status     = sanitize_key( $status );
		$allowed    = [ 'pending', 'paid', 'confirmed', 'completed', 'cancelled' ];

		if ( $booking_id <= 0 || ! in_array( $status, $allowed, true ) ) {
			setErrorMsg( 'Invalid booking status update.' );
			wp_redirect( vv_admin_url( 'booking' ) );
			die();
		}

		if ( ! $this->host_can_manage_booking( $booking_id ) ) {
			setErrorMsg( 'You do not have permission to update this booking.' );
			wp_redirect( vv_admin_url( 'booking' ) );
			die();
		}

		$wpdb->update(
			'vv_bookings',
			[
				'status'       => $status,
				'datemodified' => current_time( 'mysql' ),
			],
			[ 'ID' => $booking_id ]
		);

		setSuccessMsg( 'Booking status updated.' );
		wp_redirect( vv_admin_url( 'booking' ) );
		die();
	}

	public function save_host_quick_booking() {
		global $wpdb;

		$host_id = get_current_user_id();
		if ( $host_id <= 0 ) {
			setErrorMsg( 'You must be logged in.' );
			wp_redirect( vv_login_url( 'host' ) );
			die();
		}

		$apartment_id   = intval( POST_Request( 'apartment_id' ) );
		$check_in_date  = trim( POST_Request( 'check_in_date' ) );
		$check_out_date = trim( POST_Request( 'check_out_date' ) );
		$firstname      = trim( POST_Request( 'firstname' ) );
		$lastname       = trim( POST_Request( 'lastname' ) );
		$email          = trim( POST_Request( 'email' ) );
		$phone          = trim( POST_Request( 'phone' ) );
		$adults         = max( 1, intval( POST_Request( 'adults' ) ) );
		$daily_price    = floatval( POST_Request( 'daily_price' ) );

		$apartment = vv_get_apartment( $apartment_id );
		if ( ! is_array( $apartment ) || gArrayItem( $apartment, 'ID' ) <= 0 ) {
			setErrorMsg( 'Invalid apartment.' );
			wp_redirect( vv_admin_url( 'booking' ) );
			die();
		}

		$is_admin = function_exists( 'vv_is_admin_role' ) && vv_is_admin_role( $host_id );
		if ( ! $is_admin && intval( gArrayItem( $apartment, 'user_id' ) ) !== $host_id ) {
			setErrorMsg( 'You do not have permission to add bookings for this apartment.' );
			wp_redirect( vv_admin_url( 'booking' ) );
			die();
		}

		if ( $firstname === '' || $check_in_date === '' || $check_out_date === '' || strtotime( $check_out_date ) <= strtotime( $check_in_date ) ) {
			setErrorMsg( 'Please fill in guest name and valid check-in / check-out dates.' );
			wp_redirect( vv_admin_url( 'booking' ) );
			die();
		}

		if ( $daily_price <= 0 ) {
			$daily_price = floatval( gArrayItem( $apartment, 'price_daily' ) );
		}

		$date1 = new DateTime( $check_in_date );
		$date2 = new DateTime( $check_out_date );
		$dates = [];
		while ( $date1 < $date2 ) {
			$dates[] = $date1->format( 'Y-m-d' );
			$date1->modify( '+1 day' );
		}
		$nights = max( 1, count( $dates ) );
		$total  = $daily_price * $nights;

		$bookingdata = [
			'user_id'        => 0,
			'email'          => $email,
			'firstname'      => $firstname,
			'lastname'       => $lastname,
			'check_in_date'  => $check_in_date,
			'check_out_date' => $check_out_date,
			'adults'         => $adults,
			'children'       => 0,
			'child_ages'     => '[]',
			'district_id'    => intval( gArrayItem( $apartment, 'district' ) ),
			'apartment_id'   => $apartment_id,
			'status'         => 'confirmed',
			'dateadded'      => current_time( 'mysql' ),
			'datemodified'   => current_time( 'mysql' ),
			'dates'          => wp_json_encode( $dates ),
			'total'          => $total,
		];

		if ( $phone !== '' ) {
			$bookingdata['notes'] = 'Phone: ' . $phone;
		}

		if ( ! $wpdb->insert( 'vv_bookings', $bookingdata ) ) {
			setErrorMsg( 'Could not save booking.' );
			wp_redirect( vv_admin_url( 'booking' ) );
			die();
		}

		$booking_id = intval( $wpdb->insert_id );
		$this->generate_booking_num( $this->get_booking( $booking_id ) );
		$wpdb->insert(
			'vv_booking_items',
			[
				'booking_id'   => $booking_id,
				'code'         => 'booking-date',
				'item_type'    => 'booking-date',
				'name'         => 'Accommodation',
				'qty'          => $nights,
				'price'        => $daily_price,
				'dateadded'    => current_time( 'mysql' ),
				'datemodified' => current_time( 'mysql' ),
			]
		);

		setSuccessMsg( 'Booking added.' );
		wp_redirect( vv_admin_url( 'booking' ) . '?tab=upcoming' );
		die();
	}

}