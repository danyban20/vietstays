<?php 

class vvFeatures{
	public function __construct(){
	}

	public function get_actions($action = ''){
		if($action == 'delete-feature' && GET_Request('id') > 0){
			$this->delete_feature(GET_Request('id'));
		}
	}

	public function post_actions($action = ''){
		if($action == 'vv_save_feature'){
			$this->save_feature();
		}
	}

	public function save_feature(){

		global $wpdb;
		$feature_ids = POST_Request('feature_id');
		$names = POST_Request('name');
		$icons = POST_Request('icon');


		if(is_array($names)){
			for($i = 0; $i < count($feature_ids); $i++){

				if($feature_ids[$i] > 0){
					$wpdb->update('vv_features',['name' => $names[$i], 'icon' => $icons[$i], 'datemodified' => date("Y-m-d H:i:s")], ['feature_id' => $feature_ids[$i]]);
				}else{
					if(trim($names[$i]) != ''){
						$wpdb->insert('vv_features',['name' => $names[$i], 'icon' => $icons[$i], 'dateadded' => date("Y-m-d H:i:s"), 'datemodified' => date("Y-m-d H:i:s")]);
					}
				}

			}
			setSuccessMsg('Features Saved');
		}else{
			setErrorMsg('Invalid Data');
		}
		wp_redirect(vv_admin_url('features'));
		die();


	}

	public function get_features($filter = array()){
		global $wpdb;

		$where = " 1 ";
		foreach($filter as $index => $value){
			$where .= " AND ".$index." = '".$value."' ";
		}
		
		$features = $wpdb->get_results("SELECT * FROM vv_features WHERE ".$where." ORDER BY name",ARRAY_A);
		for($i = 0; $i < count($features); $i++){
			$features[$i]['name'] = stripslashes($features[$i]['name']);
		}



		return $features;
	}

	public function get_feature($id = 0){
		global $wpdb;

		$feature = $wpdb->get_row("SELECT * FROM vv_features WHERE feature_id = ".intval($id), ARRAY_A);
		$feature['name'] = stripslashes($feature['name']);

		return $feature;
	}



	public function delete_feature($id = 0){
		global $wpdb;

		$wpdb->delete('vv_features',['feature_id' => $id]);

		setSuccessMsg('Feature Deleted Successfully');
		wp_redirect(vv_admin_url('features'));
		die();
	}

}