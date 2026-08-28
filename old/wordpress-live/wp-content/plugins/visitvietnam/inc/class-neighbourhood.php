<?php 

class vvNeighbourhoods{
	public function __construct(){
	}

	public function get_actions($action = ''){
		if($action == 'delete-neighbourhood' && GET_Request('id') > 0){
			$this->delete_neighbourhood(GET_Request('id'));
		}elseif($action == 'neighbourhoods_autocomplete'){
			$this->neighbourhoods_autocomplete();
		}
	}

	public function post_actions($action = ''){
		if($action == 'save_neighbourhood'){
			$this->save_neighbourhood();
		}
	}

	public function save_neighbourhood(){
		global $wpdb;

		$neighbourhood_id 		= POST_Request('neighbourhood_id');
		$name 				= POST_Request('name');
		$district_id 		= POST_Request('district_id');
		$facilities 		= POST_Request('facilities');
		$security_features 	= POST_Request('security_features') ?? [];

		if($neighbourhood_id > 0){
			wp_update_post([
			    'ID'         => $neighbourhood_id,
			    'post_title' => $name,
			    'post_parent' => $district_id,
			]);
			setSuccessMsg('Neighbourhood Updated');
		}else{
			$neighbourhood_id = wp_insert_post([
			    'post_title'    => $name,
			    'post_type'     => 'neighbourhood',
			    'post_parent' => $district_id,
			    'post_status'   => 'publish'
			]);

			if($neighbourhood_id > 0) setSuccessMsg('Neighbourhood Added');
		}

		if($neighbourhood_id > 0){
		    $main_image_id = vv_handle_uploaded_image('main_image', $neighbourhood_id);
		    $header_bg_image_id = vv_handle_uploaded_image('header_bg_image', $neighbourhood_id);

			if($main_image_id > 0) 		update_post_meta($neighbourhood_id,'main_image',$main_image_id);
			if($header_bg_image_id > 0) update_post_meta($neighbourhood_id,'header_bg_image',$header_bg_image_id);

			update_post_meta($neighbourhood_id,'facilities',$facilities);
			update_post_meta($neighbourhood_id,'security_features',$security_features);
			update_post_meta($neighbourhood_id,'header_text',POST_Request('header_text'));


		}

		wp_redirect(vv_admin_url('neighbourhoods/edit').'?id='.$neighbourhood_id);
		die();

	}



	public function get_neighbourhoods($filter = array()){
		global $wpdb;


		$where 		= " post_type = 'neighbourhood' AND post_status = 'publish' ";
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
		$neighbourhoods 	= $wpdb->get_results($sql,ARRAY_A);

		for($i = 0; $i < count($neighbourhoods); $i++){
			$neighbourhoods[$i]['post_title'] = stripslashes($neighbourhoods[$i]['post_title']);
		}

		if(GET_Request('debug') == 1) {
			echo print_r_pre($neighbourhoods);
		}

		if($return_total){
			$total_rows = gArrayItem($wpdb->get_row("SELECT COUNT(*) as num_rows FROM $wpdb->posts a WHERE ".$where, ARRAY_A),'num_rows');			
			return ['total_rows' => $total_rows, 'neighbourhoods' => $neighbourhoods];
		}else{
			return $neighbourhoods;
		}
	}

	public function get_neighbourhood($id = 0){
		global $wpdb;

		$neighbourhood = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE ID = ".intval($id), ARRAY_A);
		if(gArrayItem($neighbourhood,'ID') > 0){
			$neighbourhood['post_title'] = stripslashes($neighbourhood['post_title']);
		}
		return $neighbourhood;
	}



	public function delete_neighbourhood($id = 0){
		global $wpdb;

		$wpdb->delete('vv_neighbourhoods',['neighbourhood_id' => $id]);

		setSuccessMsg('Neighbourhood Deleted Successfully');
		wp_redirect(vv_admin_url('neighbourhoods'));
		die();
	}


	public function neighbourhoods_autocomplete(){
		global $wpdb;

		header('Content-Type: application/json');

		$search_term = trim(GET_Request('q'));


		$search_term = esc_sql($search_term);
	    $post_type = esc_sql('neighbourhood');
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

		foreach($results as $neighbourhood){

			array_push($result, array('value' => $neighbourhood->ID, 'text' => $neighbourhood->post_title));
		}	

		echo vv_jsonEncode($result);
			


		die();
	}

}