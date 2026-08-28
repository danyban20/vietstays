<?php 

class vvPromoCodes{
	public function __construct(){
	}

	public function get_actions($action = ''){
		if($action == 'delete-promocode' && GET_Request('id') > 0){
			$this->delete_promocode(GET_Request('id'));
		}
	}

	public function post_actions($action = ''){
		if($action == 'save_promocode'){
			$this->save_promocode();
		}
	}

	public function save_promocode(){

		global $wpdb;

		$promocode_id 	= intval(POST_Request('promocode_id'));
		$code 			= strtoupper(POST_Request('code'));
		$discount 		= POST_Request('discount');
		$apartment_ids 	= POST_Request('apartment_ids');
		$ambassador_id 	= POST_Request('ambassador_id');
		$status 		= POST_Request('status');

		$error_msg = '';

		$data = [];
		$data['code'] 			= $code;
		$data['discount'] 		= $discount;
		$data['apartment_ids'] 	= json_encode($apartment_ids);
		$data['ambassador_id'] 	= $ambassador_id;
		$data['status']			= $status;
		$data['datemodified']   = date("Y-m-d H:i:s");


		$promocode = $this->get_promocode_by_code($code, $promocode_id);

		if(gArrayItem($promocode,'ID') > 0){
			setErrorMsg( vv__( 'Promo Code is already Used.' ) );
		}else{
			if($promocode_id > 0){

				$wpdb->update('vv_promocodes',$data,['ID' => $promocode_id]);

				setSuccessMsg( vv__( 'Promo Code Updated.' ) );
			}else{

				$data['dateadded'] = date("Y-m-d H:i:s");
				$result = $wpdb->insert('vv_promocodes',$data);

				if ($result === false) {
				    echo 'Insert failed: ' . $wpdb->last_error;
				    die();
				}else{
					
				}

				setSuccessMsg( vv__( 'Promo Code Added' ) );
			}

			wp_redirect(vv_admin_url('settings/promocodes'));
			die();
		}




	}

	public function get_promocodes($filter = array()){
		global $wpdb;

		$debug = 0;



		$where 		= " 1 ";
		$orderby 	= " ORDER BY a.dateadded DESC ";
		$limit 		= "";

		$per_page 		= gArrayItem($filter,'per_page') ? gArrayItem($filter,'per_page') : 20;
		$pgnum 			= gArrayItem($filter,'pgnum') ? gArrayItem($filter,'pgnum') : 1;
		$return_total	= intval(gArrayItem($filter,'return_total'));
		$total_rows 	= 0;


		if(gArrayItem($filter,'ambassador_id') > 0){
			$where .= " AND a.ambassador_id = ".$filter['ambassador_id'];
		}

		$status = gArrayItem($filter,'status');
		if($status == 'all'){
		}elseif($status != ''){
			$where .= " AND a.status = '".$status."' ";
		}else{
			$where .= " AND a.status = 'active' ";
		}







		if(gArrayItem($filter,'count_only') == 1){
			$return_total 	= 1;
			$promocodes 	= [];
		}else{
			$offset 	= $per_page * ($pgnum - 1);
			$limit  	= " LIMIT ".$per_page." OFFSET ".$offset;

			$sql 		= "SELECT * FROM vv_promocodes a WHERE ".$where." ".$orderby." ".$limit;
			$promocodes = $wpdb->get_results($sql,ARRAY_A);

			if($debug == 1) {
				echo $sql;
				echo print_r_pre($promocodes);
			}
		}

		if($return_total){
			$total_rows = gArrayItem($wpdb->get_row("SELECT COUNT(*) as num_rows FROM vv_promocodes a WHERE ".$where, ARRAY_A),'num_rows');			
			return ['total_rows' => $total_rows, 'promocodes' => $promocodes];
		}else{
			return $promocodes;
		}

	}

	public function get_promocode($id = 0){
		global $wpdb;

		$promocode = $wpdb->get_row("SELECT * FROM vv_promocodes WHERE promocode_id = ".intval($id), ARRAY_A);
		$promocode['name'] = stripslashes($promocode['name']);

		return $promocode;
	}

	public function get_promocode_by_code($code = '', $id = 0){
		global $wpdb;

		$promocode = $wpdb->get_row("SELECT * FROM vv_promocodes WHERE UCASE(code) = ".strtoupper($code)." AND ID != ".$id, ARRAY_A);
		$promocode['name'] = stripslashes($promocode['name']);

		return $promocode;
	}


	public function delete_promocode($id = 0){
		global $wpdb;

		$wpdb->delete('vv_promocodes',['promocode_id' => $id]);

		setSuccessMsg( vv__( 'Promo Code Deleted Successfully' ) );
		wp_redirect(vv_admin_url('settings/promocodes'));
		die();
	}

}