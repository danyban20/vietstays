<?php 

class vvTasks{
	public function __construct(){
	}

	public function get_actions($action = ''){
		if($action == 'delete-task' && GET_Request('id') > 0){
			$this->delete_task(GET_Request('id'));
		}elseif($action == 'tasks_autocomplete'){
			$this->tasks_autocomplete();
		}elseif($action == 'resend_task_created_notifiacation'){
			$this->resend_task_created_notifiacation();
		}
	}

	public function post_actions($action = ''){
		if($action == 'save_task'){
			$this->save_task();
		}elseif($action == 'save_task_comment'){
			$this->save_task_comment();
		}
	}

	public function save_task(){
		global $wpdb;

		$task_id		= intval(POST_Request('task_id'));
		$task_title 	= trim(POST_Request('task_title'));
		$description 	= trim(POST_Request('description'));
		$city_id 			= POST_Request('city_id');
		$staff_id 		= intval(POST_Request('staff_id'));

		$taskdata = array(
			'post_title'    => $task_title,
			'post_content'  => $description,
			'post_status'   => 'publish', // 'draft', 'pending', 'private', 'publish'
			'post_author'   => get_current_user_id(),
			'post_type'     => 'task', // Change to your CPT slug
			'post_category' => array(0), // Not required for CPTs unless taxonomy is attached
		);
		//die(print_r_pre($taskdata));
		if($task_id > 0){
			$taskdata['ID'] = $task_id;
			wp_update_post($taskdata);
			setSuccessMsg('Task Updated');
			$is_new = false;
		}else{
			$is_new = true;
			$task_id = wp_insert_post($taskdata);

			if($task_id > 0) setSuccessMsg('Task Added');
		}

		//echo 'staff_id='.$staff_id;die();
		update_post_meta($task_id,'staff_id',$staff_id);
		if($is_new){

			$this->send_new_task_notification($task_id);
		}else{
			$this->send_task_updated_notification($task_id);

		}

		wp_redirect(vv_admin_url('/tasks/edit').'?id='.$task_id);
		die();

	}
	public function save_task_comment(){
		global $wpdb;

		$comment_id		= intval(POST_Request('comment_id'));
		$task_id		= intval(POST_Request('task_id'));
		$comment 	= trim(POST_Request('comment'));

		$author_id = get_current_user_id();
		$author = get_user_by('ID',$author_id);
		$author_email = $author->data->user_email;
		$author_firstname = get_user_meta($author_id,'first_name',true);
		$author_lastname = get_user_meta($author_id,'last_name',true);


		$task = get_post($task_id);

		$commentdata = array(
			'comment_post_ID'      => $task_id, // ID of the post the comment is for
			'comment_author'       => $author_firstname.' '.$author_lastname,
			'comment_author_email' => $author_email,
			'comment_author_url'   => '',
			'comment_content'      => $comment,
			'comment_type'         => '', // Empty for standard comment
			'comment_parent'       => 0,  // 0 if not a reply
			'user_id'              => get_current_user_id(), // 0 if not logged in
			'comment_approved'     => 1, // 1 = approved, 0 = pending, 'spam' = spam
		);
		$comment_id = wp_insert_comment($commentdata);

		$this->send_new_task_comment_notification($comment_id);

		wp_redirect(vv_admin_url('/tasks/details').'?id='.$task_id);
		die();

	}

	public function resend_task_created_notifiacation(){
		$this->send_new_task_notification(GET_Request('id'));
	}
	public function send_new_task_notification($task_id = 0){
		$email_templates_class = new vvEmailTemplates;
		$template 	= $email_templates_class->get_email_template_by_code('user_new_task_notification');
		$subject 	= trim(gArrayItem($template,'subject'));
		$body 		= stripslashes(gArrayItem($template,'body'));
		$body 	    = stripslashes($body);
		$body 	    = stripslashes($body);
		$body 	    = stripslashes($body);

		$staff_id = get_post_meta($task_id,'staff_id',true);

		$staff = get_user_by('ID',$staff_id);



		//echo $user->user_email;

		if($subject != '' && $body != '' && $staff){
			$task = get_post($task_id);

			$data = array();
			$data['FIRSTNAME'] 	= get_user_meta($staff_id,'first_name',true);
			$data['LASTNAME'] 	= get_user_meta($staff_id,'last_name',true);
			$data['EMAIL'] 		= $staff->user_email;
			$data['USERNAME'] 	= $staff->user_login;
			$data['PASSWORD'] 	= $user_password;
			$data['TASK_TITLE'] = $task->post_title;
			$data['TASK_LINK']  = vv_admin_url('tasks/details/').'?id='.$task_id;

			$subject 	= $email_templates_class->replace_email_tokens($subject,$data);
			$body 		= $email_templates_class->replace_email_tokens($body,$data);

			$sender = vv_get_config('email_sender_name').' <'.vv_get_config('email_sender').'>';


			send_email($sender, '', $staff->user_email, $subject, $body);

			//die($body);
			return $body;

		}else{
			die('Invalid message/user');
		}

	}
	public function send_new_task_comment_notification($comment_id = 0){
		$email_templates_class = new vvEmailTemplates;
		$template 	= $email_templates_class->get_email_template_by_code('user_new_task_comment_notification');
		$subject 	= trim(gArrayItem($template,'subject'));
		$body 		= stripslashes(gArrayItem($template,'body'));
		$body 	    = stripslashes($body);
		$body 	    = stripslashes($body);
		$body 	    = stripslashes($body);

		$comment = get_comment($comment_id);
		$post_author = $comment->post_author;
		$author = get_user_by('ID',$post_author);

		$staff_id 	= get_post_meta($comment->comment_post_ID,'staff_id',true);
		$staff 		= get_user_by('ID',$staff_id,true);

		//echo $user->user_email;
		$task_id = $comment->comment_post_ID;
		$task = get_post($task_id);

		if($subject != '' && $body != '' && $staff && $author && $comment && $task){
			$author_firstname = get_user_meta($post_author,'first_name',true);
			$author_lastname = get_user_meta($post_author,'last_name',true);
			$task = get_post($task_id);
			$data = array();
			$data['FIRSTNAME'] 	= get_user_meta($staff_id,'first_name',true);
			$data['LASTNAME'] 	= get_user_meta($staff_id,'last_name',true);
			$data['EMAIL'] 		= $staff->user_email;
			$data['COMMENT_AUTHOR'] = $comment->comment_author;
			$data['COMMENT'] 	= $comment->comment_content;
			$data['PASSWORD'] 	= $user_password;
			$data['TASK_TITLE'] = $task->post_title;
			$data['TASK_LINK']  = vv_admin_url('tasks/details/').'?id='.$task_id;


			$subject 	= $email_templates_class->replace_email_tokens($subject,$data);
			$body 		= $email_templates_class->replace_email_tokens($body,$data);

			$sender = vv_get_config('email_sender_name').' <'.vv_get_config('email_sender').'>';


			send_email($sender, '', $staff->user_email, $subject, $body);

			//die($body);
			return $body;

		}else{
			die('Invalid message/user');
		}

	}
	public function send_task_updated_notification($task_id = 0){
		$email_templates_class = new vvEmailTemplates;
		$template 	= $email_templates_class->get_email_template_by_code('user_task_updated_notification');
		$subject 	= trim(gArrayItem($template,'subject'));
		$body 		= gArrayItem($template,'body');

		$staff_id = get_post_meta($task_id,'staff_id',true);

		$staff = get_user_by('ID',$staff_id);
		echo 'tempalte=';
		echo print_r_pre($template);



		//echo $user->user_email;

		if($subject != '' && $body != '' && $staff){
			$task = get_post($task_id);

			$data = array();
			$data['FIRSTNAME'] 	= get_user_meta($staff_id,'first_name',true);
			$data['LASTNAME'] 	= get_user_meta($staff_id,'last_name',true);
			$data['EMAIL'] 		= $staff->user_email;
			$data['USERNAME'] 	= $staff->user_login;
			$data['PASSWORD'] 	= $user_password;
			$data['TASK_TITLE'] = $task->post_title;
			$data['TASK_DESCRIPTION'] = $task->post_content;
			$data['TASK_LINK']  = vv_admin_url('tasks/details/').'?id='.$task_id;

			$subject 	= $email_templates_class->replace_email_tokens($subject,$data);
			$body 		= $email_templates_class->replace_email_tokens($body,$data);

			$sender = vv_get_config('email_sender_name').' <'.vv_get_config('email_sender').'>';


			send_email($sender, '', $user->user_email, $subject, $body);

			die($body);
			return $body;

		}else{
			die('Invalid message/user');
		}

	}
	public function get_tasks($filter = array()){
		global $wpdb;


		$where 		= " post_type = 'task' AND post_status = 'publish' ";
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

		if(gArrayItem($filter,'parent')  != 'all'){
			$where .= " AND post_parent = ".intval(gArrayItem($filter,'parent'));
		}

		if(gArrayItem($filter,'city_id') > 0){
			$join = " LEFT JOIN ".$wpdb->postmeta." b ON a.ID = b.post_id ";
			$where .= " AND b.meta_id IS NOT NULL AND b.meta_key = 'city' AND b.meta_value LIKE '".$filter['city_id']."' ";
		}

		$sql 		= "SELECT * FROM $wpdb->posts a ".$join." WHERE ".$where." ".$orderby." ".$limit;
		$tasks 	= $wpdb->get_results($sql,ARRAY_A);


		if(GET_Request('debug') == 1) {
			echo $sql;
			echo print_r_pre($tasks);
		}

		$location_class = new vvNeighbourhoods;
		for($i = 0; $i < count($tasks); $i++){

			if(gArrayItem($filter,'exclude_neighbourhoods') == 1){
			}else{
			}
		}

		if(GET_Request('debug') == 1) {
			echo print_r_pre($tasks);
		}

		if($return_total){
			$total_rows = gArrayItem($wpdb->get_row("SELECT COUNT(*) as num_rows FROM $wpdb->posts a WHERE ".$where, ARRAY_A),'num_rows');			
			return ['total_rows' => $total_rows, 'tasks' => $tasks];
		}else{
			return $tasks;
		}
	}
	public function get_comments($filter = array()){
		global $wpdb;


		$where 		= " post_type = 'task_comment' AND post_status = 'publish' ";
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

		if(gArrayItem($filter,'task_id')  != 'all'){
			$where .= " AND post_parent = ".intval(gArrayItem($filter,'task_id'));
		}

		if(gArrayItem($filter,'city_id') > 0){
			$join = " LEFT JOIN ".$wpdb->postmeta." b ON a.ID = b.post_id ";
			$where .= " AND b.meta_id IS NOT NULL AND b.meta_key = 'city' AND b.meta_value LIKE '".$filter['city_id']."' ";
		}

		$sql 		= "SELECT * FROM $wpdb->posts a ".$join." WHERE ".$where." ".$orderby." ".$limit;
		$comments 		= $wpdb->get_results($sql,ARRAY_A);


		if(GET_Request('debug') == 1) {
			echo $sql;
			echo print_r_pre($comments);
		}


		if(GET_Request('debug') == 1) {
			echo print_r_pre($comments);
		}

		if($return_total){
			$total_rows = gArrayItem($wpdb->get_row("SELECT COUNT(*) as num_rows FROM $wpdb->posts a WHERE ".$where, ARRAY_A),'num_rows');			
			return ['total_rows' => $total_rows, 'comments' => $comments];
		}else{
			return $comments;
		}
	}


	public function get_task($id = 0){
		global $wpdb;

		$task = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE ID = ".intval($id), ARRAY_A);
		if(gArrayItem($task,'ID') > 0){
			$location_class = new vvNeighbourhoods;

			$task['post_title'] = stripslashes($task['post_title']);
			$task['neighbourhoods'] 	= $location_class->get_neighbourhoods(['per_page' => 'all', 'parent' => $task['ID'], 'orderby' => 'post_title ASC']);
		}
		return $task;
	}



	public function delete_task($id = 0){
		global $wpdb;

		$wpdb->delete('vv_tasks',['task_id' => $id]);

		setSuccessMsg('Task Deleted Successfully');
		wp_redirect(vv_admin_url('tasks'));
		die();
	}


	public function tasks_autocomplete(){
		global $wpdb;

		header('Content-Type: application/json');

		$search_term = trmi(GET_Request('q'));


		$search_term = esc_sql($search_term);
	    $post_type = esc_sql('task');
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

		foreach($results as $task){

			array_push($result, array('value' => $task->ID, 'text' => $task->post_title));
		}	

		echo vv_jsonEncode($result);
			


		die();
	}

}