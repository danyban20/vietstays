<?php 

class vvInvoices{
	public function __construct(){
	}

	public function get_actions($action = ''){
		if($action == 'delete-invoice' && GET_Request('id') > 0){
			$this->delete_invoice(GET_Request('id'));
		}elseif($action == 'get_invoice_foods_json'){
			$this->get_invoice_foods_json();
		}elseif($action == 'upload_invoice_image'){
			$this->upload_image();
		}elseif($action == 'get_invoice_images_html' && GET_Request('id') > 0){
			$invoice = $this->get_invoice(GET_Request('id'))	;
			$this->get_invoice_images_html($invoice);
			die();
		}elseif($action == 'invoice_autocomplete'){
			$this->invoice_autocomplete();
		}
	}

	public function post_actions($action = ''){
		if($action == 'vv_save_invoice'){
			$this->save_invoice();
		}elseif($action == 'add_new_invoices'){
			$this->add_new_invoices();
		}
	}

	public function save_invoice(){
		global $wpdb;

		$invoice_id 			= POST_Request('invoice_id');
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

		if($status == '') $status = 'active';

		$url_slug1 	= $url_slug;
		$ctr 		= 0;
		do{
			$sql = "SELECT COUNT(*) as num_rows FROM vv_invoices WHERE url_slug = '".$url_slug."'  AND ID != ".intval($invoice_id);
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
				'status' => $status,
				'datemodified' => date("Y-m-d H:i:s")
				];

		if(trim($display_name) != '') $data['display_name'] = $display_name;

		//die(print_r_pre($data));

		if($invoice_id > 0){

			$wpdb->update('vv_invoices',$data,['ID' => $invoice_id]);


			$is_new = false;
			setSuccessMsg('Invoice Updated');
		}else{
			$data['dateadded'] = date("Y-m-d H:i:s");

			$wpdb->insert('vv_invoices',$data);

			$invoice_id = $wpdb->insert_id;

			$is_new = true;
			if($invoice_id > 0){
				setSuccessMsg('Invoice Added');
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
				$wpdb->delete('vv_invoice_discounts',['apt_discount_id' => gArrayItem($discount_id,$i)]);
			}elseif($discount[$i] != ''){
				$data = ['invoice_id' => $invoice_id, 'datestart' => date("Y-m-d",strtotime(gArrayItem($discount_start,$i))), 'dateend' => date("Y-m-d",strtotime(gArrayItem($discount_end,$i))), 'discount' => $discount[$i]];

				//die(print_r_pre($data));

				if(gArrayItem($discount_id,$i) > 0){
					$wpdb->update('vv_invoice_discounts',$data,['apt_discount_id' => gArrayItem($discount_id,$i)]);
				}else{
					$wpdb->insert('vv_invoice_discounts', $data);
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
				$wpdb->delete('vv_invoice_foods',['apt_food_id' => gArrayItem($apt_food_id,$i)]);
			}elseif($food_name[$i] != ''){
				$data = ['invoice_id' => $invoice_id, 'food_id' => gArrayItem($food_id,$i), 'price' => gArrayItem($food_price,$i), 'datemodified' => date("Y-m-d H:i:s")];


				if(gArrayItem($apt_food_id,$i) > 0){
					$wpdb->update('vv_invoice_foods',$data,['apt_food_id' => gArrayItem($apt_food_id,$i)]);
				}else{
					$data['dateadded'] = date("Y-m-d H:i:s");
					$wpdb->insert('vv_invoice_foods', $data);
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

		$invoice = $this->get_invoice($invoice_id);
		if(gArrayItem($invoice,'post_id') == 0){

			$post_id = wp_insert_post(['post_type' => 'invoice','post_title' => $invoice['name'],'post_status' => 'publish']);
			if($post_id > 0){
				$d['post_id'] = $post_id;
			}
		}

		$wpdb->update('vv_invoices',$d,['ID' => $invoice_id]);

		$this->update_invoice_number($invoice_id);


		wp_redirect(vv_admin_url('invoices/edit/?id='.$invoice_id));
		die();

	}


	public function add_new_invoices(){
		global $wpdb;

		$user_id 		= POST_Request('user_id');
		$users_class 	= new vvUsers;
		$user 			= $users_class->get_user($user_id);

		if(gArrayItem($user,'ID') > 0){
			$num_invoices = POST_Request('num_invoices');

			for($i = 0; $i < $num_invoices; $i++){
				$data = ['name' => 'New Invoice', 
						'user_id' => $user_id, 
						'pricing' => '[]',
						'status' => 'draft',
						'dateadded' => date("Y-m-d H:i:s"),
						'datemodified' => date("Y-m-d H:i:s")
						];


				$wpdb->insert('vv_invoices',$data);

			}


			setSuccessMsg($num_invoices.' invoices added for '.$user['user_email']);
		}else{
			setErrorMsg('User not found');
		}

		wp_redirect(vv_admin_url('users'));
		die();

	}



	public function get_invoices($filter = array()){
		global $wpdb;

		$debug = 0;

		if($debug == 1) {
			echo print_r_pre($filter);
		}

		$where 		= " 1 ";
		$orderby 	= " ORDER BY a.dateadded DESC ";
		$limit 		= "";

		$per_page 		= (gArrayItem($filter,'per_page') > 0) ? gArrayItem($filter,'per_page') : 20;
		$pgnum 			= (gArrayItem($filter,'pgnum') > 0) ? gArrayItem($filter,'pgnum') : 1;
		$return_total	= intval(gArrayItem($filter,'return_total'));
		$total_rows 	= 0;

		$select = (gArrayItem($filter,'select') != '') ? $filter['select'] : '*';

		if(gArrayItem($filter,'srch') != ''){
			$srch = $filter['srch'];

			$where .= " AND ( invoice_num LIKE '%".$srch."%' ) ";

		}


		if(gArrayItem($filter,'user_id') > 0) 		$where .= " AND a.user_id = ".$filter['user_id'];

		$status = gArrayItem($filter,'status');
		if($status == 'all'){
		}elseif($status != ''){
			$where .= " AND a.status = '".$status."' ";
		}else{
			$where .= " AND a.status = 'active' ";
		}



		if(gArrayItem($filter,'count_only') == 1){
			$return_total 	= 1;
			$invoices 	= [];
		}else{
			$offset 	= $per_page * ($pgnum - 1);
			
			if(gArrayItem($filter,'per_page') != 'all') $limit  	= " LIMIT ".$per_page." OFFSET ".$offset;

			$sql 		= "SELECT ".$select." FROM vv_invoices a WHERE ".$where." ".$orderby." ".$limit;
			$invoices = $wpdb->get_results($sql,ARRAY_A);

			if($debug == 1) {
				echo $sql;
				echo print_r_pre($invoices);
			}
		}

		if($return_total){
			$total_rows = gArrayItem($wpdb->get_row("SELECT COUNT(*) as num_rows FROM vv_invoices a WHERE ".$where, ARRAY_A),'num_rows');			
			return ['total_rows' => $total_rows, 'invoices' => $invoices];
		}else{
			return $invoices;
		}
	}


	public function get_invoice($id = 0){
		global $wpdb;

		$invoice = $wpdb->get_row("SELECT * FROM vv_invoices WHERE ID = ".intval($id), ARRAY_A);
		if(gArrayItem($invoice,'ID') > 0){
			$invoice['name'] = stripslashes($invoice['name']);
		}

		return $invoice;
	}



	public function get_invoice_by_num($invoice_num = ''){
		global $wpdb;

		$invoice = $wpdb->get_row("SELECT * FROM vv_invoices WHERE invoice_num = '".$invoice_num."' ", ARRAY_A);
		if(gArrayItem($invoice,'ID') > 0){
			$invoice['name'] = stripslashes($invoice['name']);
		}

		return $invoice;
	}

	public function get_num_invoices($user_id){
		global $wpdb;
		return intval(gArrayItem($wpdb->get_row("SELECT COUNT(*) as num_invoices FROM vv_invoices WHERE user_id = ".$user_id, ARRAY_A), 'num_invoices'));
	}




	public function update_invoice_number($invoice_id = 0){
		$invoice = $this->get_invoice($invoice_id);

		if(gArrayItem($invoice,'invoice_num') == ''){
			global $wpdb;

			$date = $invoice['dateadded'];

			if($date == '0000-00-00 00:00:00') $date = $invoice['datemodified'];

			$invoice_num = 'APT'.date("Ymd",strtotime($date)).$invoice_id;
			$wpdb->update('vv_invoices',['invoice_num' => $invoice_num],['ID' => $invoice_id]);
		}
	}



	public function delete_invoice($id = 0){
		global $wpdb;

		$wpdb->delete('vv_invoices',['ID' => $id]);

		setSuccessMsg('Invoice Deleted Successfully');
		wp_redirect(vv_admin_url('invoices'));
		die();
	}

	public function upload_image(){
		global $wpdb;

		$invoice_id = GET_Request('id');
		$invoice = $this->get_invoice($invoice_id);

		if(gArrayItem($invoice,'ID') > 0){


			$images = vv_get_invoice_images($invoice);

			$image = gArrayItem($_FILES,'file');
			if(gArrayItem($image,'tmp_name') !== ''){

				$upload = wp_handle_upload( 
					$_FILES[ 'file' ], 
					array( 'test_form' => false ) 
				);

				if(empty( $upload[ 'error' ] ) ) {

					// it is time to add our uploaded image into WordPress media library
					$attachment_id = wp_insert_attachment(
						array(
							'guid'           => $upload[ 'url' ],
							'post_mime_type' => $upload[ 'type' ],
							'post_title'     => basename( $upload[ 'file' ] ),
							'post_content'   => '',
							'post_status'    => 'inherit',
						),
						$upload[ 'file' ]
					);

					if( is_wp_error( $attachment_id ) || ! $attachment_id ) {
						wp_die( 'Upload error.' );
					}

					// update medatata, regenerate image sizes
					require_once( ABSPATH . 'wp-admin/includes/image.php' );

					wp_update_attachment_metadata(
						$attachment_id,
						wp_generate_attachment_metadata( $attachment_id, $upload[ 'file' ] )
					);


					$thumb = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );


					if($thumb != ''){
						array_push($images,['thumb' => $thumb, 'image_id' => $attachment_id,'order' => (count($images) + 1)]);
						$wpdb->update('vv_invoices',['images' => json_encode($images)],['ID' => $invoice_id]);

						echo print_r_pre($images);
					}

				}		
			}
		}
		die();
	}

	function get_invoice_images_html($invoice = []){
		$images = vv_get_invoice_images($invoice);
	    //echo '<pre>'.print_r($images,true).'</pre>';
	    if(count($images) > 0){ 
	        ?>
	        <ul id="imagesTable">
	            <?php 
	            foreach($images as $img){
	                if(gArrayItem($img,'thumb') != ''){
	                    $primary_class  = '';
	                    $primary_url    = vv_admin_url().'?action=set_invoice_primary_img&id='.$invoice['ID'].'&img='.$img['image_id'];
	                    $primary_click  = "return confirm('Set as Primary Image?')";
	                    if(gArrayItem($img,'order') == -100){
	                        $primary_class  = 'text-success';
	                        $primary_url    = '#';
	                        $primary_click  = '';
	                    }

	                    $caption = gArrayItem($img,'caption');
	                    ?>
	                    <li class="filtr-item invoice_img mb-1 bg-white" data-category="" data-sort="">
	                    	<input type="hidden" name="images_url[]" value="<?php echo $img['thumb'] ?>" >
	                    	<input type="hidden" name="images_id[]" value="<?php echo $img['image_id'] ?>" >
	                        <div class="border p-2">
	                            <div class="row">
	                                <div class="col-6 pr-1">
		                                <a href="<?php echo $img['thumb'] ?>" class="invoice_img-thumb" data-toggle="lightbox"  >
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
		                                    	<a href="<?php echo vv_admin_url().'?action=delete_invoice_image&id='.$invoice['ID'].'&img='.$img['image_id'] ?>" title="Delete" class="delete_img"  onclick="return deleteImage(this)"  ><i class="fa fa-trash" style="font-size:18px"></i></a>
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

	function delete_invoice_image($invoice_id = 0, $image_id = 0){
		$invoice = $this->get_invoice($invoice_id);
		$images = vv_get_invoice_images($invoice);

		$found = false;
		$images2 = [];
		foreach($images as $img){
			if(gArrayItem($img,'image_id') == $image_id){
				$found = true;
			}else{
				array_push($images2,$img);
			}
		}

		if($found){
			global $wpdb;
			$wpdb->update('vv_invoices',['images' => json_encode($images2)],['ID' => $invoice_id]);
		}
		die();
	}




	function invoice_autocomplete(){
		$q = GET_Request('q');

		$filter = [];
		$filter['select'] = 'ID, display_name';
		$filter['srch'] = $q;
		$filter['per_page'] = 20;
		$filter['return_total'] = 0;

		$invoices = $this->get_invoices($filter);

		for($i = 0; $i < count($invoices); $i++){
			$invoices[$i]['value'] = $invoices[$i]['ID'];
			$invoices[$i]['text'] = stripslashes($invoices[$i]['display_name']);
		}

		header("Content-Type: application/json");
		echo json_encode($invoices);
		die();
	}


}