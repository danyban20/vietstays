<?php 

class vvFoods{

	public function __construct(){
	}

	public function get_actions($action = ''){
		if($action == 'delete-food' && GET_Request('id') > 0){
			$this->delete_food(GET_Request('id'));
		}elseif($action == 'get_foods_json'){
			$this->get_foods_json();
		}elseif($action == 'delete-food_category' && GET_Request('id') > 0){
			$this->delete_food_category(GET_Request('id'));
		}
	}

	public function post_actions($action = ''){
		if($action == 'vv_save_food'){
			$this->save_food();
		}elseif($action == 'vv_save_food_categories'){
			$this->save_food_categories();
		}
	}

	public function save_food(){

		global $wpdb;

		$food_id = POST_Request('food_id');
		$name = POST_Request('name');
		$categories = POST_Request('category');


		if(!is_array($categories)) $categories = array();


		$data 					= array();
		$data['name'] 			= $name;
		$data['price'] 			= POST_Request('price');
		$data['category'] 		= '|'.implode('|,|',$categories).'|';
		$data['datemodified'] 	= date("Y-m-d H:i:s");


		if($food_id > 0){
			$wpdb->update('vv_foods',$data,['food_id' => $food_id]);
		}else{
			$data['dateadded'] = date("Y-m-d H:i:s");
			$wpdb->insert('vv_foods');

			$food_id = $wpdb->insert_id;
		}

		$image = gArrayItem($_FILES,'file-input');
		if(gArrayItem($image,'tmp_name') !== '' && $food_id > 0){

			$upload = wp_handle_upload( 
				$_FILES[ 'file-input' ], 
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
					$wpdb->update('vv_foods',['image' => $thumb, 'image_id' => $attachment_id],['food_id' => $food_id]);
				}

			}		
		}


		setSuccessMsg('Foods Updated');
		wp_redirect(vv_admin_url('foods'));
		die();

	}

	public function get_foods($filter = array()){
		global $wpdb;

		$where = " 1 ";
		$join = '';

		if(gArrayItem($filter,'category')) $where .= " AND a.category LIKE '%|".$filter['category']."|%' ";

		if(gArrayItem($filter,'apartment_id') > 0){
			$join .= " LEFT JOIN vv_apartment_foods b ON a.food_id = b.food_id ";
			$where .= " AND b.apartment_id = ".$filter['apartment_id'];
		}
		
		$foods = $wpdb->get_results("SELECT * FROM vv_foods a ".$join." WHERE ".$where,ARRAY_A);

		for($i = 0; $i < count($foods); $i++){
			$foods[$i]['name'] = stripslashes($foods[$i]['name']);
		}

		return $foods;
	}



	function get_foods_json(){

		$filter = array();
		if(GET_Request('food_cat') > 0) $filter['category'] = GET_Request('food_cat');

		$data = $this->get_foods($filter);



		header('Content-Type: application/json; charset=utf-8');
		echo json_encode(['status' => 1, 'data' => $data]);
	
		die();		
	}

	public function get_food($id = 0){
		global $wpdb;

		$food = $wpdb->get_row("SELECT * FROM vv_foods WHERE food_id = ".$id, ARRAY_A);

		return $food;
	}

	public function count_foods($food_id){
		global $wpdb;

		return intval(gArrayItem($wpdb->get_row("SELECT COUNT(*) as num_rows FROM vv_foods WHERE food_id = ".$food_id),'num_rows'));
	}

	public function delete_food($id = 0){
		global $wpdb;

		$food = $this->get_food($id);
		if(gArrayItem($food,'food_id') > 0){
			$wpdb->delete('vv_foods',['food_id' => $id]);

			setSuccessMsg('Food Deleted Successfully');
			wp_redirect(vv_admin_url('foods/?apartment='.gArrayItem($food,'apartment_id')));
			die();
		}
	}

	public function get_food_categories($filter = []){
		global $wpdb;

		$where = " 1 ";
		foreach($filter as $index => $value){
			$where .= " AND ".$index." = '".$value."' ";
		}
		
		$categories = $wpdb->get_results("SELECT * FROM vv_foods_categories WHERE ".$where,ARRAY_A);

		for($i = 0; $i < count($categories); $i++){
			$categories[$i]['name'] = stripslashes($categories[$i]['name']);
		}

		return $categories;
	}

	public function save_food_categories(){
		global $wpdb;
		$names = POST_Request('name');

		if(is_array($names)){
			foreach($names as $i => $v){
				if($i > 0){
					$wpdb->update('vv_foods_categories',['name' => $v,'datemodified' => date("Y-m-d H:i:s")],['food_category_id' => $i]);
				}else{
					if(trim($v) != ''){
						$wpdb->insert('vv_foods_categories',['name' => $v, 'dateadded' => date("Y-m-d H:i:s"), 'datemodified' => date("Y-m-d H:i:s")]);
					}
				}
			}
			setSuccessMsg('Food Categories Saved');
		}else{
			setErrorMsg('Invalid Data');
		}
		wp_redirect(vv_admin_url('foods-categories/'));
		die();
	}

	public function delete_food_category($id = 0){
		global $wpdb;

		$wpdb->delete('vv_foods_categories',['food_category_id' => $id]);

		setSuccessMsg('Category Deleted Successfully');
		wp_redirect(vv_admin_url('foods-categories/'));
		die();
	}



}