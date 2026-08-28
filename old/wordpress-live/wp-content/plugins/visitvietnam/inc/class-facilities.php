<?php 

class vvFacilities{
	public function __construct(){
	}

	public function get_actions($action = ''){
		if($action == 'delete-facility' && GET_Request('id') > 0){
			$this->delete_facility(GET_Request('id'));
		}
	}

	public function post_actions($action = ''){
		if($action == 'vv_save_facility'){
			$this->save_facility();
		}
	}

	public function save_facility(){

		global $wpdb;
		$names = POST_Request('name');
		$types = POST_Request('type');

		if(is_array($names)){
			foreach($names as $i => $v){
				if(intval($i) > 0){
					$data = ['name' => $v, 'type' => $types[$i], 'datemodified' => date("Y-m-d H:i:s")];
					$wpdb->update('vv_facilities',$data, ['security_feature_id' => $i]);
				}else{
					if(trim($v) != ''){
						$data = ['name' => $v,  'type' => $types['new'], 'dateadded' => date("Y-m-d H:i:s"), 'datemodified' => date("Y-m-d H:i:s")];
						$wpdb->insert('vv_facilities',$data);
					}
				}
				$ctr++;
			}			setSuccessMsg('Facilities Saved');
		}else{
			setErrorMsg('Invalid Data');
		}
		wp_redirect(vv_admin_url('settings/facilities'));
		die();


	}

	public function get_facilities($filter = array()){
		global $wpdb;

		$where = " 1 ";

		if(gArrayItem($filter,'name') != '') $where .= " AND name LIKE '%".$filter['name']."%' ";
		if(gArrayItem($filter,'type') != '') $where .= " AND `type` = '".$filter['type']."' ";
		
		$facilities = $wpdb->get_results("SELECT * FROM vv_facilities WHERE ".$where." ORDER BY `type`, name",ARRAY_A);
		for($i = 0; $i < count($facilities); $i++){
			$facilities[$i]['name'] = stripslashes($facilities[$i]['name']);
		}



		return $facilities;
	}

	public function get_facility($id = 0){
		global $wpdb;

		$facility = $wpdb->get_row("SELECT * FROM vv_facilities WHERE facility_id = ".intval($id), ARRAY_A);
		$facility['name'] = stripslashes($facility['name']);

		return $facility;
	}



	public function delete_facility($id = 0){
		global $wpdb;

		$wpdb->delete('vv_facilities',['facility_id' => $id]);

		setSuccessMsg('Facility Deleted Successfully');
		wp_redirect(vv_admin_url('settings/facilities'));
		die();
	}



}