<?php

/**
 * Guest post-booking portal — connected to vv_bookings.
 */
class vvGuestPortal {

	public function post_actions( $action ) {
		if ( $action === 'guest_login' ) {
			$this->handle_login();
		}

		if ( $action === 'guest_passport_upload' ) {
			$this->handle_passport_upload();
		}
	}

	public function get_actions( $action ) {
		if ( $action === 'guest_logout' ) {
			wp_logout();
			setSuccessMsg( 'You have successfully logged out.' );
			wp_redirect( vv_login_url( 'guest' ) );
			die();
		}
	}

	private function handle_login() {
		$login    = trim( POST_Request( 'login' ) );
		$password = POST_Request( 'password' );

		$user = wp_signon(
			[
				'user_login'    => $login,
				'user_password' => $password,
				'remember'      => POST_Request( 'remember' ) === '1',
			],
			is_ssl()
		);

		if ( $user && ! is_wp_error( $user ) ) {
			setSuccessMsg( 'You have successfully logged in.' );
			wp_redirect( vv_sanitize_guest_redirect( POST_Request( 'redirect_to' ) ) );
			die();
		}

		setErrorMsg( 'Invalid email or password.' );
		$redirect = trim( POST_Request( 'redirect_to' ) );
		wp_redirect( $redirect !== '' ? vv_login_url( 'guest', $redirect ) : vv_login_url( 'guest' ) );
		die();
	}

	private function handle_passport_upload() {
		if ( ! is_user_logged_in() ) {
			$return = gArrayItem( $_SERVER, 'REQUEST_URI' );
			wp_redirect( $return !== '' ? vv_login_url( 'guest', $return ) : vv_login_url( 'guest' ) );
			die();
		}

		$conversion_stay = POST_Request( 'conversion_stay' ) === '1';
		$booking_num     = trim( POST_Request( 'booking_num' ) );
		$booking         = [];

		if ( ! $conversion_stay ) {
			$booking = vv_get_booking_by_num( $booking_num );
			if ( ! vv_guest_can_view_booking( $booking, get_current_user_id() ) ) {
				setErrorMsg( 'You do not have access to this booking.' );
				wp_redirect( vv_users_url() );
				die();
			}
		}

		if ( empty( $_FILES['passport_file']['name'] ) ) {
			setErrorMsg( 'Please choose a passport image or PDF to upload.' );
			wp_redirect( POST_Request( 'conversion_stay' ) === '1' ? vv_users_url( 'conversion/stay' ) : vv_get_booking_link( $booking ) );
			die();
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$attachment_id = media_handle_upload( 'passport_file', 0 );

		if ( is_wp_error( $attachment_id ) ) {
			setErrorMsg( $attachment_id->get_error_message() );
			wp_redirect( $conversion_stay ? vv_users_url( 'conversion/stay' ) : vv_get_booking_link( $booking ) );
			die();
		}

		update_user_meta( get_current_user_id(), 'vv_passport_document', $attachment_id );
		update_user_meta( get_current_user_id(), 'vv_passport_uploaded_at', current_time( 'mysql' ) );
		update_user_meta( get_current_user_id(), 'vv_checkin_status', 'passport_submitted' );

		setSuccessMsg( 'Passport uploaded successfully. Our team will review it before check-in.' );
		wp_redirect( $conversion_stay ? vv_users_url( 'conversion/stay' ) : vv_get_booking_link( $booking ) );
		die();
	}
}
