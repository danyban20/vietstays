<?php 

class vvRooms{

	public function __construct(){
	}

	public function get_actions($action = ''){
		if($action == 'delete-room' && GET_Request('id') > 0){
			$this->delete_room(GET_Request('id'));
		}
	}

	public function post_actions($action = ''){
		if($action == 'vv_save_room'){
			$this->save_room();
		}
	}

	public function save_room(){
		$room_id 		= POST_Request('room_id');
		$apartment_id 	= POST_Request('apartment_id');
		$name 			= POST_Request('name');
		$adults 		= POST_Request('adults');
		$children 		= POST_Request('children');
		$babies 		= POST_Request('babies');
		$extra 			= POST_Request('extra');
		$pricing 		= POST_Request('pricing');

		if(!is_array($pricing)) $pricing = array();

		global $wpdb;

		$data = ['apartment_id' => $apartment_id, 'name' => $name, 'adults' => $adults, 'children' => $children, 'babies' => $babies, 'extra' => $extra, 'pricing' => json_encode($pricing), 'datemodified' => date("Y-m-d H:i:s")];

		if($room_id > 0){

			$wpdb->update('vv_rooms',$data,['ID' => $room_id]);

			setSuccessMsg('Room Updated');
		}else{
			$data['dateadded'] = date("Y-m-d H:i:s");

			$wpdb->insert('vv_rooms',$data);



			$room_id = $wpdb->insert_id;


			setSuccessMsg('Room Added');
		}

		wp_redirect(vv_admin_url('rooms/edit/?id='.$room_id));
		die();

	}

	public function get_rooms($filter = array()){
		global $wpdb;

		$where = " 1 ";
		foreach($filter as $index => $value){
			$where .= " AND ".$index." = '".$value."' ";
		}
		
		$rooms = $wpdb->get_results("SELECT * FROM vv_rooms WHERE ".$where,ARRAY_A);

		return $rooms;
	}

	public function get_room($id = 0){
		global $wpdb;

		$room = $wpdb->get_row("SELECT * FROM vv_rooms WHERE ID = ".$id, ARRAY_A);

		return $room;
	}

	public function count_rooms($room_id){
		global $wpdb;

		return intval(gArrayItem($wpdb->get_row("SELECT COUNT(*) as num_rows FROM vv_rooms WHERE room_id = ".$room_id),'num_rows'));
	}

	public function delete_room($id = 0){
		global $wpdb;

		$room = $this->get_room($id);
		if(gArrayItem($room,'ID') > 0){
			$wpdb->delete('vv_rooms',['ID' => $id]);

			setSuccessMsg('Room Deleted Successfully');
			wp_redirect(vv_admin_url('rooms/?apartment='.gArrayItem($room,'apartment_id')));
			die();
		}
	}

}