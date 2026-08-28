<?php
/*
Plugin Name: visitVietnam
Plugin URI: http://www.Wiise.no
Description: Custom Plugin for visitvietnam.no
Author: Wiise.no
Version: 2.0.0
Author URI: http://www.wiise.no
*/

if(!session_id()) session_start();

include('inc/functions.php');
include('inc/functions-countries.php');
include('inc/class-facilities.php');
include('inc/class-promocodes.php');
include('inc/class-security_features.php');
include('inc/class-features.php');
include('inc/class-districts.php');
include('inc/class-tasks.php');
include('inc/class-neighbourhoods.php');
include('inc/class-cities.php');
include('inc/class-apartments.php');
include('inc/class-apartments-wizard.php');
include('inc/class-apartment-platform.php');
include('inc/class-rooms.php');
include('inc/class-settings.php');
include('inc/class-booking.php');
include('inc/class-foods.php');
include('inc/class-cleaners_checklist.php');
include('inc/class-roles.php');
include('inc/class-host-applications.php');
include('inc/class-conversions.php');
include('inc/class-guest-portal.php');
include('inc/class-users.php');
include('inc/class-staffs.php');
include('inc/class-email_templates.php');
include('inc/class-email-template-seeder.php');
include('inc/class-i18n.php');
include('inc/class-frontend.php');
include('inc/class-invoices.php');

global $vv_db_version;
$vv_db_version = '1.0';

if(!class_exists('visitVietnam')){
	class visitVietnam{

		var $end_imgs = array();
		var $plugin_url;
		var $amember_api_key = '';
		var $apartment_platform_class;
		var $apartments_wizard_class;
		var $apartment_class;
		var $district_class;
		var $tasks_class;
		var $neighbourhood_class;
		var $facility_class;
		var $promocodes_class;
		var $security_features_class;
		var $features_class;
		var $city_class;
		var $room_class;
		var $settings_class;
		var $booking_class;
		var $foods_class;
		var $users_class;
		var $staffs_class;
		var $email_templates_class;
		var $cleaners_checklist_class;
		var $views_path;
		var $frontend_class;
		var $invoice_class;
		var $host_applications_class;
		var $guest_portal_class;
		var $conversions_class;

		public function __construct(){

			$this->district_class = new vvDistricts;
			$this->tasks_class = new vvTasks;
			$this->neighbourhood_class = new vvNeighbourhoods;
			$this->facility_class = new vvFacilities;
			$this->features_class = new vvFeatures;
			$this->security_features_class = new vvSecurityFeatures;
			$this->city_class = new vvCities;
			$this->apartment_class = new vvApartments;
			$this->apartments_wizard_class = new vvApartmentsWizard;
			$this->apartment_platform_class = new vvApartmentPlatform;
			$this->room_class = new vvRooms;
			$this->settings_class = new vvSettings;
			$this->booking_class = new vvBookings;
			$this->foods_class = new vvFoods;
			$this->users_class = new vvUsers;
			$this->staffs_class = new vvStaffs;
			$this->email_templates_class = new vvEmailTemplates;
			$this->cleaners_checklist_class = new vvCleanersChecklists;
			$this->frontend_class = new vvFrontend;
			$this->promocodes_class= new vvPromoCodes;
			$this->invoice_class = new vvInvoices;
			$this->host_applications_class = new vvHostApplications;
			$this->guest_portal_class = new vvGuestPortal;
			$this->conversions_class = new vvConversions;

    		add_action('init', array($this, 'init'));
    		add_action('admin_init', array($this, 'admin_init'));
			add_action('admin_head', array($this,'plugin_header'));


		}
    	public function init(){
			
			if(!session_id()) session_start();

			vv_init_configs();

			$this->maybe_seed_host_email_templates();
											
			add_action('admin_menu', array($this,'vv_menu'));
			
			$this->create_post_type();


			$this->init_frontend();
			$this->init_admin();
			$this->init_user_portal();

    	}

		function maybe_seed_host_email_templates() {
			$action = GET_Request( 'vv_action' );
			if ( $action === '' ) {
				$action = GET_Request( 'action' );
			}
			if ( $action !== 'seed_host_email_templates' ) {
				return;
			}

			if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
				wp_die( 'You must be logged in as an administrator to run this script.' );
			}

			$overwrite = GET_Request( 'overwrite' ) === '1';
			$results   = vvEmailTemplateSeeder::seed( $overwrite );
			vvEmailTemplateSeeder::render_seed_response( $results, $overwrite );
			die();
		}

		
		function vv_menu() {
			add_menu_page('Visit Vietnam', 'Visit Vietnam', 'administrator', vv_admin_url(), null, 'dashicons-calendar-alt', 10 );
			//add_submenu_page('visitvietnam','visitVietnam Settings', 'Settings', 'manage_options', 'visitvietnam-settings', array($this,'settings_page') );
		}
		


		function init_admin(){
			global $logged_user, $logged_user_role;

			$uri = gArrayItem($_SERVER,'REQUEST_URI');
			if(strpos($uri,'/vv-admin') !== false){

				$this->views_path = plugin_dir_path(__FILE__).'admin/views/';



				vv_admin_gatekeeper();

				$logged_user_id 	= get_current_user_id();
				if(intval($logged_user_id) > 0){
					$logged_user 		= get_user_by('ID',$logged_user_id);
					$logged_user_role 	= vv_get_user_role($logged_user);
				}

				if($_POST) $this->post_actions();
				$this->get_actions();

				$tmp = explode("/vv-admin/",$uri);
				$path = gArrayItem($tmp,1);
				$tmp2 = explode("?",$path);
				$admin_file = rtrim(gArrayItem($tmp2,0),'/');

				if ( preg_match( '#^partner/(.*)$#', $admin_file, $partner_match ) ) {
					$admin_file = 'partners/' . $partner_match[1];
				}

				$standalone_routes = [
					'account'   => 'admins/account',
					'invoices'  => 'admins/account',
					'dashboard' => 'dashboard/dashboard',
				];

				if ( isset( $standalone_routes[ $admin_file ] ) ) {
					$admin_file = $standalone_routes[ $admin_file ];
				} elseif ( $admin_file === 'account/list' || $admin_file === 'invoices/list' ) {
					$admin_file = 'admins/account';
				} elseif ( strpos( $admin_file, '/' ) === false && strpos( $admin_file, 'settings' ) === false ) {
					$admin_file .= '/list';
				}

				if($admin_file == 'apartments') 					$admin_file = 'apartments/list';
				elseif($admin_file == 'booking') 					$admin_file = 'booking/list';
				elseif($admin_file == 'locations/cities') 			$admin_file = 'locations/cities/list';
				elseif($admin_file == 'locations/districts') 		$admin_file = 'locations/districts/list';
				elseif($admin_file == 'locations/neighbourhoods') 	$admin_file = 'locations/neighbourhoods/list';
				elseif($admin_file == 'facilities') 				$admin_file = 'facilities/list';
				elseif($admin_file == 'features') 					$admin_file = 'features/list';
				elseif($admin_file == 'foods') 						$admin_file = 'foods/list';
				elseif($admin_file == 'cleaners') 					$admin_file = 'cleaners/checklist';
				elseif($admin_file == 'settings') 					$admin_file = 'settings/general';
				elseif($admin_file == 'users') 						$admin_file = 'users/list';
				elseif($admin_file == 'admins') 					$admin_file = 'admins/list';
				elseif($admin_file == 'ambassadors') 				$admin_file = 'ambassadors/list';
				elseif($admin_file == 'partners') 					$admin_file = 'partners/list';
				elseif($admin_file == 'host-applications') 			$admin_file = 'host-applications/list';
				elseif($admin_file == 'building-requests') 		$admin_file = 'building-requests/list';
				elseif($admin_file == 'coming-soon') 					$admin_file = 'coming-soon/list';
				elseif($admin_file == 'apartments/edit-old') 		$admin_file = 'apartments/edit-old';
				elseif($admin_file == 'apartments/edit') 			$admin_file = 'apartments/edit-old';
				elseif($admin_file == 'apartments/add-old') 		$admin_file = 'apartments/add-old';
				elseif($admin_file == 'apartments/add') 		$admin_file = 'apartments/add';
				elseif($admin_file == 'apartments/manage') 	$admin_file = 'apartments/manage';
				elseif($admin_file == 'dashboard') 					$admin_file = 'dashboard/dashboard';
				elseif($admin_file == '') 							$admin_file = 'invoices/list';


				if($admin_file == 'login/list'){
					include("admin/login.php");
				}else{
					//echo $admin_file;
					if(!file_exists($this->views_path.$admin_file.'.php')){
						//setErrorMsg('Invalid URL');
						$admin_file = 'not-found';
					}
					include("admin/layout.php");	
				}

				die();

			}
		}

		function init_user_portal(){
			$uri = gArrayItem( $_SERVER, 'REQUEST_URI' );
			if ( strpos( $uri, '/vv-users' ) === false ) {
				return;
			}

			$plugin_path = plugin_dir_path( __FILE__ );
			$views_path  = $plugin_path . 'user/views/';
			$is_login    = ( strpos( $uri, '/login' ) !== false );

			if ( $_POST ) {
				$action = POST_Request( 'action' );
				if ( $action === '' ) {
					$action = POST_Request( 'vv_action' );
				}
				$this->guest_portal_class->post_actions( $action );
			}

			$action = GET_Request( 'action' );
			if ( $action === '' ) {
				$action = GET_Request( 'vv_action' );
			}
			$this->guest_portal_class->get_actions( $action );

			if ( $is_login ) {
				if ( is_user_logged_in() ) {
					wp_redirect( vv_sanitize_guest_redirect( GET_Request( 'redirect_to' ) ) );
					die();
				}
				include $plugin_path . 'user/login.php';
				die();
			}

			vv_users_gatekeeper();

			$booking_num = trim( GET_Request( 'booking' ) );
			$user_file   = 'bookings/list';

			if ( $booking_num !== '' ) {
				$user_file = 'booking/detail';
			} else {
				$tmp     = explode( '/vv-users/', $uri );
				$path    = trim( gArrayItem( $tmp, 1 ), '/' );
				$tmp2    = explode( '?', $path );
				$segment = rtrim( gArrayItem( $tmp2, 0 ), '/' );

				if ( $segment === 'booking' || $segment === 'bookings' ) {
					$user_file = 'bookings/list';
				} elseif ( $segment !== '' ) {
					$user_file = $segment;
				}
			}

			if ( ! file_exists( $views_path . $user_file . '.php' ) ) {
				$user_file = 'not-found';
			}

			$user_view_file = $views_path . $user_file . '.php';
			include $plugin_path . 'user/layout.php';
			die();
		}

		function init_frontend(){

			if ( $_POST ) {
				$action = POST_Request( 'action' );
				if ( $action === '' ) {
					$action = POST_Request( 'vv_action' );
				}
				$this->frontend_class->post_actions( $action );
				$this->host_applications_class->post_actions( $action );
				if ( isset( $this->conversions_class ) ) {
					$this->conversions_class->post_actions( $action );
				}
			}

			$action = GET_Request('action');
			if($action == '') $action = GET_Request('vv_action');

			$this->frontend_class->get_actions($action);

			$this->host_applications_class->get_actions($action);
			if ( isset( $this->conversions_class ) ) {
				$this->conversions_class->get_actions( $action );
			}

			/*			
			if(strpos(gArrayItem($_SERVER,'REQUEST_URI'),'/apartment/') !== false){

				$uri = $_SERVER['REQUEST_URI'];
				if(strpos($uri,'?') !== false){
					list($uri,$extra) = explode("?",$uri);
				}

				$tmp = explode("/apartment/",$uri);

				$code = rtrim(gArrayItem($tmp,1),'/');


				$apartment = $this->apartment_class->get_apartment_by_slug($code);


				include('front/apartment.php');

				exit();

			}
			*/

		}


    	public function create_post_type(){
			
			$pluginfolder = plugins_url( '' , __FILE__ );
			
			/*
			register_post_type('apartment', 
				array(
					'label' => 'Apartments',
					'description' => '',
					'public' => true,
					'show_ui' => true,
					'capability_type' => 'page',
					'hierarchical' => true,
					'rewrite' => array('slug' => 'apartment'),
					'supports' => array('title'),
					'exclude_from_search' => true,
					'show_in_nav_menus' => false,
					)
				);	
			*/
			register_post_type('city', 
				array(
					'label' => 'Cities',
					'description' => '',
					'public' => true,
					'show_ui' => true,
					'capability_type' => 'page',
					'hierarchical' => true,
					'rewrite' => array('slug' => 'city'),
					'supports' => array('title'),
					'exclude_from_search' => true,
					'show_in_nav_menus' => true,
					)
				);	

			register_post_type('district', 
				array(
					'label' => 'Districts',
					'description' => '',
					'public' => true,
					'show_ui' => true,
					'capability_type' => 'page',
					'hierarchical' => true,
					'rewrite' => array('slug' => 'district'),
					'supports' => array('title', 'page-attributes'),
					'exclude_from_search' => true,
					'show_in_nav_menus' => false,
					)
				);	

			register_post_type('neighbourhood', 
				array(
					'label' => 'Buildings',
					'description' => '',
					'public' => true,
					'show_ui' => false,
					'capability_type' => 'page',
					'hierarchical' => true,
					'rewrite' => array('slug' => 'building'),
					'supports' => array('title', 'page-attributes'),
					'exclude_from_search' => true,
					'show_in_nav_menus' => false,
					)
				);	


    	}

		function post_actions(){
			$action = POST_Request('action');
			if($action == '') $action = POST_Request('vv_action');


			if($action == 'login'){
				$login 		= POST_Request('login');
				$password 	= POST_Request('password');

				$user = wp_signon(['user_login' => $login, 'user_password' => $password]);

				if ( $user && ! is_wp_error( $user ) ) {
					if ( ! vv_host_can_access_admin( $user->ID ) ) {
						wp_logout();
						setErrorMsg( 'Your host account is not activated yet. Please check your email for the activation link from Vietstays.' );
						wp_redirect( vv_login_url( 'host' ) );
						die();
					}

					setSuccessMsg('You have successfully logged in.');
					wp_redirect(vv_admin_url().'dashboard');
				}else{
					setErrorMsg('Invalid Username/Password.');
					wp_redirect( vv_login_url( 'host' ) );
				}
				
			}

			$this->district_class->post_actions($action);
			$this->tasks_class->post_actions($action);
			$this->neighbourhood_class->post_actions($action);
			$this->city_class->post_actions($action);
			$this->facility_class->post_actions($action);
			$this->promocodes_class->post_actions($action);
			$this->security_features_class->post_actions($action);
			$this->features_class->post_actions($action);
			$this->apartment_class->post_actions($action);
			if ( isset( $this->apartments_wizard_class ) ) {
				$this->apartments_wizard_class->post_actions( $action );
			}
			if ( isset( $this->apartment_platform_class ) ) {
				$this->apartment_platform_class->post_actions( $action );
			}
			$this->room_class->post_actions($action);
			$this->settings_class->post_actions($action);
			$this->booking_class->post_actions($action);
			$this->foods_class->post_actions($action);
			$this->users_class->post_actions($action);
			$this->staffs_class->post_actions($action);
			$this->email_templates_class->post_actions($action);
			$this->cleaners_checklist_class->post_actions($action);
			$this->invoice_class->post_actions($action);
			$this->host_applications_class->post_actions($action);
			if ( isset( $this->conversions_class ) ) {
				$this->conversions_class->post_actions( $action );
			}

			if ( class_exists( 'vvI18n' ) ) {
				vvI18n::handle_admin_post( $action );
			}

		}

		public function get_actions(){
			$action = GET_Request('action');
			if($action == '') $action = GET_Request('vv_action');

			if($action == 'logout'){
				if ( function_exists( 'vv_is_impersonating_host' ) && vv_is_impersonating_host() ) {
					$this->users_class->stop_impersonating_host();
				}

		        wp_logout();

		        setSuccessMsg('You have successfully logged out');
		        wp_redirect( vv_login_url( 'host' ) );
				
			}

			$this->district_class->get_actions($action);
			$this->tasks_class->get_actions($action);
			$this->neighbourhood_class->get_actions($action);
			$this->city_class->get_actions($action);
			$this->facility_class->get_actions($action);
			$this->promocodes_class->get_actions($action);
			$this->security_features_class->get_actions($action);
			$this->features_class->get_actions($action);
			$this->apartment_class->get_actions($action);
			if ( isset( $this->apartments_wizard_class ) ) {
				$this->apartments_wizard_class->get_actions( $action );
			}
			$this->room_class->get_actions($action);
			$this->settings_class->get_actions($action);
			$this->booking_class->get_actions($action);
			$this->foods_class->get_actions($action);
			$this->users_class->get_actions($action);
			$this->staffs_class->get_actions($action);
			$this->email_templates_class->get_actions($action);
			$this->cleaners_checklist_class->get_actions($action);
			$this->invoice_class->get_actions($action);
			if ( isset( $this->conversions_class ) ) {
				$this->conversions_class->get_actions( $action );
			}

			if ( class_exists( 'vvI18n' ) ) {
				vvI18n::handle_admin_get( $action );
			}

		}

			  		
    	public function admin_init(){			

    	}

		function plugin_header() {
			$pluginfolder = plugins_url( '' , __FILE__ );

			global $post_type;

		}




	}
	
}


if(class_exists('visitVietnam')){
	global $visitVietnam;	

	$visitVietnam = new visitVietnam();

}



?>