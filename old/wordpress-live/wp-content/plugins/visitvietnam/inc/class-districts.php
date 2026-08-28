<?php 

class vvDistricts{
	public function __construct(){
	}

	public function get_actions($action = ''){
		if($action == 'delete-district' && GET_Request('id') > 0){
			$this->delete_district(GET_Request('id'));
		}elseif($action == 'districts_autocomplete'){
			$this->districts_autocomplete();
		}
	}

	public function post_actions($action = ''){
		if($action == 'save_district'){
			$this->save_district();
		}
	}

	public function save_district(){
		global $wpdb;

		$district_id 		= POST_Request('district_id');
		$name 				= POST_Request('name');
		$city_id 			= POST_Request('city_id');

		if($district_id > 0){
			wp_update_post([
			    'ID'         => $district_id,
			    'post_title' => $name,
			]);
			setSuccessMsg('District Updated');
		}else{
			$district_id = wp_insert_post([
			    'post_title'    => $name,
			    'post_type'     => 'district',
			    'post_status'   => 'publish'
			]);

			if($district_id > 0) setSuccessMsg('District Added');
		}

		if($district_id > 0){
		    $main_image_id = vv_handle_uploaded_image('main_image', $district_id);
		    $header_bg_image_id = vv_handle_uploaded_image('header_bg_image', $district_id);

			if($main_image_id > 0) 		update_post_meta($district_id,'main_image',$main_image_id);
			if($header_bg_image_id > 0) update_post_meta($district_id,'header_bg_image',$header_bg_image_id);

			update_post_meta($district_id,'city',$city_id);
			update_post_meta($district_id,'header_text',POST_Request('header_text'));

			/*
			$locations = POST_Request('locations');
			if(is_array($locations)){

			    $d_locations = $this->get_districts(['per_page' => 'all', 'parent' => $district_id]);
			    // DELETE 
			    foreach($d_locations as $d_location){

			    	$found = false;
					foreach($locations as $i => $v){
						if($i > 0 && $i == $d_location['ID']) $found = true;
					}

					if($found == false){
						wp_delete_post($d_location['ID']);
					}
				}

				// ADD/UPDATE
				foreach($locations as $i => $v){
					if($i > 0){
						wp_update_post([
						    'ID'         => $i,
						    'post_title' => $v
						]);
					}else{
						$d_location_id = wp_insert_post([
						    'post_title'    => $v,
						    'post_type'     => 'district',
						    'post_status'   => 'publish',
						    'post_parent' 	=> $district_id
						]);
					}
				}

			}
			*/

		}

		wp_redirect(vv_admin_url('locations/districts/edit').'?id='.$district_id);
		die();

	}



	public function get_districts($filter = array()){
		global $wpdb;


		$where 		= " post_type = 'district' AND post_status = 'publish' ";
		$orderby 	= " ORDER BY a.post_title ASC ";

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

		if ( gArrayItem( $filter, 'parent' ) !== 'all' ) {
			$where .= ' AND post_parent = ' . intval( gArrayItem( $filter, 'parent' ) );
		}

		$city_id = intval( gArrayItem( $filter, 'city_id' ) );
		if ( $city_id > 0 ) {
			$where .= $wpdb->prepare(
				" AND EXISTS (
					SELECT 1 FROM {$wpdb->postmeta} pm
					WHERE pm.post_id = a.ID
					AND pm.meta_key = 'city'
					AND pm.meta_value = %s
				) ",
				(string) $city_id
			);
		}

		$sql 		= "SELECT * FROM $wpdb->posts a ".$join." WHERE ".$where." ".$orderby." ".$limit;
		$districts 	= $wpdb->get_results($sql,ARRAY_A);


		if(GET_Request('debug') == 1) {
			echo $sql;
			echo print_r_pre($districts);
		}

		$location_class = new vvNeighbourhoods;
		for($i = 0; $i < count($districts); $i++){

			if(gArrayItem($filter,'exclude_neighbourhoods') == 1){
				$districts[$i]['neighbourhoods'] = [];
			}else{
				$r = $location_class->get_neighbourhoods(['per_page' => 'all', 'district_id' => $districts[$i]['ID'], 'orderby' => 'post_title ASC']);
				$districts[$i]['neighbourhoods'] = $r;
			}
		}

		if(GET_Request('debug') == 1) {
			echo print_r_pre($districts);
		}

		if($return_total){
			$total_rows = gArrayItem($wpdb->get_row("SELECT COUNT(*) as num_rows FROM $wpdb->posts a WHERE ".$where, ARRAY_A),'num_rows');			
			return ['total_rows' => $total_rows, 'districts' => $districts];
		}else{
			return $districts;
		}
	}

	public function get_district($id = 0){
		global $wpdb;

		$district = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE ID = ".intval($id), ARRAY_A);
		if(gArrayItem($district,'ID') > 0){
			$location_class = new vvNeighbourhoods;

			$district['post_title'] = stripslashes($district['post_title']);
			$district['neighbourhoods'] 	= $location_class->get_neighbourhoods(['per_page' => 'all', 'parent' => $district['ID'], 'orderby' => 'post_title ASC']);
		}
		return $district;
	}



	public function delete_district($id = 0){
		global $wpdb;

		$wpdb->delete('vv_districts',['district_id' => $id]);

		setSuccessMsg('District Deleted Successfully');
		wp_redirect(vv_admin_url('districts'));
		die();
	}


	public function districts_autocomplete(){
		global $wpdb;

		header('Content-Type: application/json');

		$search_term = trim(GET_Request('q'));


		$search_term = esc_sql($search_term);
	    $post_type = esc_sql('district');
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

		foreach($results as $district){

			array_push($result, array('value' => $district->ID, 'text' => $district->post_title));
		}	

		echo vv_jsonEncode($result);
			


		die();
	}

}