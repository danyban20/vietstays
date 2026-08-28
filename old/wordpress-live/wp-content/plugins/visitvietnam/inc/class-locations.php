<?php 

class vvLocations{
	public function __construct(){
	}

	public function get_actions($action = ''){
		if($action == 'delete-location' && GET_Request('id') > 0){
			$this->delete_location(GET_Request('id'));
		}elseif($action == 'locations_autocomplete'){
			$this->locations_autocomplete();
		}
	}

	public function post_actions($action = ''){
		if($action == 'save_location'){
			$this->save_location();
		}
	}

	public function save_location(){
		global $wpdb;

		$location_id 		= POST_Request('location_id');
		$name 				= POST_Request('name');
		$district_id 		= POST_Request('district_id');
		$facilities 		= POST_Request('facilities');
		$security_features 	= POST_Request('security_features') ?? [];

		if($location_id > 0){
			wp_update_post([
			    'ID'         => $location_id,
			    'post_title' => $name,
			    'post_parent' => $district_id,
			]);
			setSuccessMsg('Location Updated');
		}else{
			$location_id = wp_insert_post([
			    'post_title'    => $name,
			    'post_type'     => 'location',
			    'post_parent' => $district_id,
			    'post_status'   => 'publish'
			]);

			if($location_id > 0) setSuccessMsg('Location Added');
		}

		if($location_id > 0){
		    $main_image_id = vv_handle_uploaded_image('main_image', $location_id);
		    $header_bg_image_id = vv_handle_uploaded_image('header_bg_image', $location_id);

			if($main_image_id > 0) 		update_post_meta($location_id,'main_image',$main_image_id);
			if($header_bg_image_id > 0) update_post_meta($location_id,'header_bg_image',$header_bg_image_id);

			update_post_meta($location_id,'facilities',$facilities);
			update_post_meta($location_id,'security_features',$security_features);
			update_post_meta($location_id,'header_text',POST_Request('header_text'));


		}

		wp_redirect(vv_admin_url('locations/edit').'?id='.$location_id);
		die();

	}



	public function get_locations($filter = array()){
		global $wpdb;


		$where 		= " post_type = 'location' AND post_status = 'publish' ";
		$orderby 	= " ORDER BY a.post_date DESC ";

		if(gArrayItem($filter,'orderby') != '') $orderby = " ORDER BY a.".$filter['orderby'];

		$limit 		= "";
		$join 		= "";

		$per_page 		= gArrayItem($filter,'per_page') ? gArrayItem($filter,'per_page') : 20;
		$pgnum 			= gArrayItem($filter,'pgnum') ? gArrayItem($filter,'pgnum') : 1;
		$return_total	= intval(gArrayItem($filter,'return_total'));
		$total_rows 	= 0;

		
		if($per_page == 'all'){
			$limit = '';
		}else{
			$offset 	= $per_page * ($pgnum - 1);
			$limit  	= " LIMIT ".$per_page." OFFSET ".$offset;
		}

		if(gArrayItem($filter,'district_id')   > 0){
			$where .= " AND post_parent = ".intval(gArrayItem($filter,'district_id'));
		}

		$sql 		= "SELECT * FROM $wpdb->posts a ".$join." WHERE ".$where." ".$orderby." ".$limit;
		$locations 	= $wpdb->get_results($sql,ARRAY_A);

		for($i = 0; $i < count($locations); $i++){
			$locations[$i]['post_title'] = stripslashes($locations[$i]['post_title']);
		}

		if(GET_Request('debug') == 1) {
			echo print_r_pre($locations);
		}

		if($return_total){
			$total_rows = gArrayItem($wpdb->get_row("SELECT COUNT(*) as num_rows FROM $wpdb->posts a WHERE ".$where, ARRAY_A),'num_rows');			
			return ['total_rows' => $total_rows, 'locations' => $locations];
		}else{
			return $locations;
		}
	}

	public function get_location($id = 0){
		global $wpdb;

		$location = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE ID = ".intval($id), ARRAY_A);
		if(gArrayItem($location,'ID') > 0){
			$location['post_title'] = stripslashes($location['post_title']);
		}
		return $location;
	}



	public function delete_location($id = 0){
		global $wpdb;

		$wpdb->delete('vv_locations',['location_id' => $id]);

		setSuccessMsg('Location Deleted Successfully');
		wp_redirect(vv_admin_url('locations'));
		die();
	}


	public function locations_autocomplete(){
		global $wpdb;

		header('Content-Type: application/json');

		$search_term = trim(GET_Request('q'));


		$search_term = esc_sql($search_term);
	    $post_type = esc_sql('location');
	    $limit = intval(10);

	    $results = $wpdb->get_results("
	        SELECT ID, post_title 
	        FROM {$wpdb->posts}
	        WHERE post_type = '{$post_type}'
	          AND post_status = 'publish'
	          AND post_title LIKE '%{$search_term}%'
	        ORDER BY post_title ASC
	        LIMIT {$limit}
	    ");


	    $result = [];

		foreach($results as $location){

			array_push($result, array('value' => $location->ID, 'text' => $location->post_title));
		}	

		echo vv_jsonEncode($result);
			


		die();
	}

}