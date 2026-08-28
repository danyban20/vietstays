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
		$facilities        = POST_Request( 'facilities' );
		$security_features = POST_Request( 'security_features' );
		$facilities        = array_values( array_filter( array_map( 'intval', is_array( $facilities ) ? $facilities : [] ) ) );
		$security_features = array_values( array_filter( array_map( 'intval', is_array( $security_features ) ? $security_features : [] ) ) );

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

		if ( $neighbourhood_id > 0 ) {
			$main_image_id      = vv_handle_uploaded_image( 'main_image', $neighbourhood_id );
			$header_bg_image_id = vv_handle_uploaded_image( 'header_bg_image', $neighbourhood_id );

			if ( $main_image_id > 0 ) {
				update_post_meta( $neighbourhood_id, 'main_image', $main_image_id );
			}
			if ( $header_bg_image_id > 0 ) {
				update_post_meta( $neighbourhood_id, 'header_bg_image', $header_bg_image_id );
			}

			update_post_meta( $neighbourhood_id, 'facilities', $facilities );
			update_post_meta( $neighbourhood_id, 'security_features', $security_features );
			update_post_meta( $neighbourhood_id, 'header_text', POST_Request( 'header_text' ) );
			update_post_meta( $neighbourhood_id, 'building_gallery', $this->parse_building_gallery_post( $neighbourhood_id ) );

			$building_request_id = intval( POST_Request( 'building_request_id' ) );
			if ( $building_request_id > 0 && class_exists( 'vvApartmentsWizard' ) ) {
				vvApartmentsWizard::complete_building_request( $building_request_id, $neighbourhood_id, 'Building created from host request.' );
			}
		}

		wp_redirect(vv_admin_url('locations/neighbourhoods/edit').'?id='.$neighbourhood_id);
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

	/**
	 * Building photo gallery for host wizard (Vietstays-managed).
	 *
	 * @return array<int, array{id:int,label:string,url:string,host_count:int}>
	 */
	public function get_building_gallery_items( $building_id, $include_usage = false ) {
		$building_id = intval( $building_id );
		if ( $building_id <= 0 ) {
			return [];
		}

		$out     = [];
		$seen    = [];
		$gallery = get_post_meta( $building_id, 'building_gallery', true );
		if ( is_array( $gallery ) ) {
			foreach ( $gallery as $item ) {
				$attachment_id = intval( is_array( $item ) ? gArrayItem( $item, 'id' ) : $item );
				if ( $attachment_id <= 0 || isset( $seen[ $attachment_id ] ) ) {
					continue;
				}
				$url = wp_get_attachment_image_url( $attachment_id, 'medium' );
				if ( ! $url ) {
					continue;
				}
				$seen[ $attachment_id ] = true;
				$row = [
					'id'    => $attachment_id,
					'label' => gArrayItem( $item, 'label', 'Building' ),
					'url'   => $url,
				];
				if ( $include_usage ) {
					$row['host_count'] = $this->get_gallery_image_host_count( $building_id, $attachment_id );
				}
				$out[] = $row;
			}
		}

		$legacy = [
			'main_image'      => 'Facade',
			'header_bg_image' => 'Lobby',
		];
		foreach ( $legacy as $meta_key => $default_label ) {
			$attachment_id = intval( get_post_meta( $building_id, $meta_key, true ) );
			if ( $attachment_id <= 0 || isset( $seen[ $attachment_id ] ) ) {
				continue;
			}
			$url = wp_get_attachment_image_url( $attachment_id, 'medium' );
			if ( ! $url ) {
				continue;
			}
			$seen[ $attachment_id ] = true;
			$row = [
				'id'    => $attachment_id,
				'label' => $default_label,
				'url'   => $url,
			];
			if ( $include_usage ) {
				$row['host_count'] = $this->get_gallery_image_host_count( $building_id, $attachment_id );
			}
			$out[] = $row;
		}

		return $out;
	}

	public function get_gallery_image_host_count( $building_id, $attachment_id ) {
		global $wpdb;

		$building_id   = intval( $building_id );
		$attachment_id = intval( $attachment_id );
		if ( $building_id <= 0 || $attachment_id <= 0 ) {
			return 0;
		}

		$rows = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT building_gallery_json FROM vv_apartments WHERE building_id = %d AND building_gallery_json IS NOT NULL AND building_gallery_json != ''",
				$building_id
			)
		);

		$count = 0;
		foreach ( (array) $rows as $json ) {
			$ids = json_decode( $json, true );
			if ( ! is_array( $ids ) ) {
				continue;
			}
			if ( in_array( $attachment_id, array_map( 'intval', $ids ), true ) ) {
				$count++;
			}
		}

		return $count;
	}

	private function parse_building_gallery_post( $neighbourhood_id ) {
		$gallery       = [];
		$existing_ids  = POST_Request( 'building_gallery_id' );
		$existing_labs = POST_Request( 'building_gallery_label' );

		if ( is_array( $existing_ids ) ) {
			foreach ( $existing_ids as $i => $attachment_id ) {
				$attachment_id = intval( $attachment_id );
				if ( $attachment_id <= 0 ) {
					continue;
				}
				$label = trim( (string) gArrayItem( $existing_labs, $i, '' ) );
				$gallery[] = [
					'id'    => $attachment_id,
					'label' => $label !== '' ? $label : 'Building',
				];
			}
		}

		if ( ! empty( $_FILES['building_gallery_upload']['name'] ) && is_array( $_FILES['building_gallery_upload']['name'] ) ) {
			$files = $_FILES['building_gallery_upload'];
			$count = count( $files['name'] );
			for ( $i = 0; $i < $count; $i++ ) {
				if ( empty( $files['name'][ $i ] ) ) {
					continue;
				}
				$_FILES['building_gallery_single'] = [
					'name'     => $files['name'][ $i ],
					'type'     => $files['type'][ $i ],
					'tmp_name' => $files['tmp_name'][ $i ],
					'error'    => $files['error'][ $i ],
					'size'     => $files['size'][ $i ],
				];
				$new_labels = POST_Request( 'building_gallery_new_label' );
				$new_label  = is_array( $new_labels ) ? trim( (string) gArrayItem( $new_labels, $i, '' ) ) : '';
				if ( $new_label === '' ) {
					$new_label = ucwords( str_replace( [ '-', '_' ], ' ', pathinfo( $files['name'][ $i ], PATHINFO_FILENAME ) ) );
				}
				$attachment_id = vv_handle_uploaded_image( 'building_gallery_single', $neighbourhood_id );
				if ( $attachment_id > 0 ) {
					$gallery[] = [
						'id'    => intval( $attachment_id ),
						'label' => $new_label,
					];
				}
			}
		}

		return $gallery;
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