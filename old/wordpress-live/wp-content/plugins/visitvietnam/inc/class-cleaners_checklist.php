<?php 

class vvCleanersChecklists{
	public function __construct(){
	}

	public function get_actions($action = ''){
		if($action == 'delete-cleaners_checklist' && GET_Request('id') > 0){
			$this->delete_cleaners_checklist(GET_Request('id'));
		}
	}

	public function post_actions($action = ''){
		if($action == 'vv_save_cleaners_checklist'){
			$this->save_cleaners_checklist();
		}
	}

	public function save_cleaners_checklist(){
		global $wpdb;
		$names = POST_Request('name');
		$types = POST_Request('type');

		if(is_array($names)){
			foreach($names as $i => $v){
				$type = gArrayItem($types,$i);
				if($i > 0){
					$wpdb->update('vv_cleaners_checklists',['name' => $v,'type' => $type, 'datemodified' => date("Y-m-d H:i:s")],['checklist_id' => $i]);
				}else{
					if(trim($v) != ''){
						$wpdb->insert('vv_cleaners_checklists',['name' => $v,'type' => $type, 'dateadded' => date("Y-m-d H:i:s"), 'datemodified' => date("Y-m-d H:i:s")]);
					}
				}
			}
			setSuccessMsg('Cleaners Checklists Saved');
		}else{
			setErrorMsg('Invalid Data');
		}
		wp_redirect(vv_admin_url('cleaners-checklist'));
		die();

	}

	public function get_cleaners_checklists($filter = array()){
		global $wpdb;

		$where = " 1 ";
		foreach($filter as $index => $value){
			$where .= " AND ".$index." = '".$value."' ";
		}
		
		$cleaners_checklists = $wpdb->get_results("SELECT * FROM vv_cleaners_checklists  WHERE  ".$where." ORDER BY name",ARRAY_A);
		for($i = 0; $i < count($cleaners_checklists); $i++){
			$cleaners_checklists[$i]['name'] = stripslashes($cleaners_checklists[$i]['name']);
		}



		return $cleaners_checklists;
	}

	public function get_cleaners_checklist($id = 0){
		global $wpdb;

		$cleaners_checklist = $wpdb->get_row("SELECT * FROM vv_cleaners_checklists WHERE cleaners_checklist_id = ".intval($id), ARRAY_A);
		$cleaners_checklist['name'] = stripslashes($cleaners_checklist['name']);

		return $cleaners_checklist;
	}



	public function delete_cleaners_checklist($id = 0){
		global $wpdb;

		$wpdb->delete('vv_cleaners_checklists',['cleaners_checklist_id' => $id]);

		setSuccessMsg('Cleaners Checklist Deleted Successfully');
		wp_redirect(vv_admin_url('cleaners-checklist'));
		die();
	}

}