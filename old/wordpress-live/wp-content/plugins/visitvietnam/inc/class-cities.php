<?php 

class vvCities{
	public function __construct(){
	}

	public function get_actions($action = ''){
		if($action == 'delete-city' && GET_Request('id') > 0){
			$this->delete_city(GET_Request('id'));
		}
	}

	public function post_actions($action = ''){
		if($action == 'vv_save_city'){
			$this->save_city();
		}
	}

	public function save_city(){
		global $wpdb;
		$names = POST_Request('name');

		if(is_array($names)){
			foreach($names as $i => $v){
				if($i > 0){
					$wpdb->update('vv_cities',['name' => $v,'datemodified' => date("Y-m-d H:i:s")],['city_id' => $i]);
				}else{
					if(trim($v) != ''){
						$wpdb->insert('vv_cities',['name' => $v, 'dateadded' => date("Y-m-d H:i:s"), 'datemodified' => date("Y-m-d H:i:s")]);
					}
				}
			}
			setSuccessMsg('Facilities Saved');
		}else{
			setErrorMsg('Invalid Data');
		}
		wp_redirect(vv_admin_url('locations/cities'));
		die();

	}

	public function get_cities($filter = array()){
		global $wpdb;

		$where 		= " post_type = 'city' AND post_status = 'publish' ";
		$orderby 	= " ORDER BY a.post_title ASC ";
		$limit 		= "";

		$per_page 		= gArrayItem($filter,'per_page') ? gArrayItem($filter,'per_page') : 20;
		$pgnum 			= gArrayItem($filter,'pgnum') ? gArrayItem($filter,'pgnum') : 1;
		$return_total	= intval(gArrayItem($filter,'return_total'));
		$total_rows 	= 0;

		
		if($per_page == 'all'){
			$limit 		= '';
		}else{
			$offset 	= $per_page * ($pgnum - 1);
			$limit  	= " LIMIT ".$per_page." OFFSET ".$offset;
		}


		$sql 		= "SELECT * FROM $wpdb->posts a WHERE ".$where." ".$orderby." ".$limit;
		$cities = $wpdb->get_results($sql,ARRAY_A);

		if(GET_Request('debug') == 1) {
			echo $sql;
			echo print_r_pre($cities);
		}

		if($return_total){
			$total_rows = gArrayItem($wpdb->get_row("SELECT COUNT(*) as num_rows FROM $wpdb->posts a WHERE ".$where, ARRAY_A),'num_rows');			
			return ['total_rows' => $total_rows, 'cities' => $cities];
		}else{
			return $cities;
		}
	}


	public function get_city($id = 0){
		global $wpdb;

		$city = $wpdb->get_row("SELECT * FROM vv_cities WHERE city_id = ".intval($id), ARRAY_A);
		$city['name'] = stripslashes($city['name']);

		return $city;
	}



	public function delete_city($id = 0){
		global $wpdb;

		$wpdb->delete('vv_cities',['city_id' => $id]);

		setSuccessMsg('City Deleted Successfully');
		wp_redirect(vv_admin_url('locations/cities'));
		die();
	}

}