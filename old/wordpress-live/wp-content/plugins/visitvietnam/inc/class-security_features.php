<?php 

class vvSecurityFeatures{
	public function __construct(){
	}

	public function get_actions($action = ''){
		if($action == 'delete-security_feature' && GET_Request('id') > 0){
			$this->delete_security_feature(GET_Request('id'));
		}
	}

	public function post_actions($action = ''){
		if($action == 'vv_save_security_feature'){
			$this->save_security_feature();
		}
	}

	public function save_security_feature(){

		global $wpdb;
		$names = POST_Request('name');
		$types = POST_Request('type');

		if(is_array($names)){
			foreach($names as $i => $v){
				if(intval($i) > 0){
					$data = ['name' => $v, 'type' => $types[$i], 'datemodified' => date("Y-m-d H:i:s")];
					$wpdb->update('vv_security_features',$data, ['security_feature_id' => $i]);
				}else{
					if(trim($v) != ''){
						$data = ['name' => $v,  'type' => $types['new'], 'dateadded' => date("Y-m-d H:i:s"), 'datemodified' => date("Y-m-d H:i:s")];
						$security_feature_id = $wpdb->insert('vv_security_features',$data);


					}
				}
			}
			setSuccessMsg('Security Features Saved');
		}else{
			setErrorMsg('Invalid Data');
		}

		wp_redirect(vv_admin_url('settings/security-features'));
		die();


	}

	public function get_security_features($filter = array()){
		global $wpdb;

		$where = " 1 ";
		if(gArrayItem($filter,'name') != '') $where .= " AND name LIKE '%".$filter['name']."%' ";
		if(gArrayItem($filter,'type') != '') $where .= " AND `type` = '".$filter['type']."' ";
		
		$security_features = $wpdb->get_results("SELECT * FROM vv_security_features WHERE ".$where." ORDER BY `type`, name",ARRAY_A);
		for($i = 0; $i < count($security_features); $i++){
			$security_features[$i]['name'] = stripslashes($security_features[$i]['name']);
		}



		return $security_features;
	}

	public function get_security_feature($id = 0){
		global $wpdb;

		$security_feature = $wpdb->get_row("SELECT * FROM vv_security_features WHERE security_feature_id = ".intval($id), ARRAY_A);
		$security_feature['name'] = stripslashes($security_feature['name']);

		return $security_feature;
	}



	public function delete_security_feature($id = 0){
		global $wpdb;

		$wpdb->delete('vv_security_features',['security_feature_id' => $id]);

		setSuccessMsg('Security Feature Deleted Successfully');
		wp_redirect(vv_admin_url('settings/security-features'));
		die();
	}

}