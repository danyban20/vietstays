<?php

/**
 * Seeds host-application email templates into vv_email_templates.
 *
 * Trigger (administrator required):
 *   /?vv_action=seed_host_email_templates
 *   /?vv_action=seed_host_email_templates&overwrite=1
 */
class vvEmailTemplateSeeder {

	public static function template_codes() {
		return array_keys( self::defaults() );
	}

	public static function defaults() {
		return [
			'host_application_submitted' => [
				'name'    => 'Host Application Submitted',
				'subject' => 'Thank you for your Vietstays host application',
				'body'    => '<p>Hi %FULL_NAME%,</p>'
					. '<p>Thank you for your application to become a Vietstays host. We have received it and will be in touch soon.</p>'
					. '<p><strong>Application ID:</strong> %APPLICATION_REF%</p>'
					. '<p>Our team normally reviews applications within 2–3 business days.</p>'
					. '<p>You can check your application status anytime here: <a href="%STATUS_LINK%">View application status</a></p>'
					. '<p>If you have questions, contact us at <a href="mailto:%CONTACT_EMAIL%">%CONTACT_EMAIL%</a> and include your application ID.</p>',
			],
			'host_application_under_review' => [
				'name'    => 'Host Application Under Review',
				'subject' => 'Your Vietstays host application is under review',
				'body'    => '<p>Hi %FULL_NAME%,</p>'
					. '<p>Our team is now reviewing your host application. This normally takes 2–3 business days.</p>'
					. '<p><strong>Application ID:</strong> %APPLICATION_REF%</p>',
			],
			'host_application_approved' => [
				'name'    => 'Host Application Approved',
				'subject' => 'Your Vietstays host application has been approved',
				'body'    => '<p>Hi %FULL_NAME%,</p>'
					. '<p>Great news — your application to become a Vietstays host has been approved.</p>'
					. '<p>Please verify your email address and set your password to activate your host account:</p>'
					. '<p><a href="%ACTIVATION_LINK%">Activate my host account</a></p>'
					. '<p>This link is valid for 7 days.</p>'
					. '<p><strong>Application ID:</strong> %APPLICATION_REF%</p>',
			],
			'host_application_rejected' => [
				'name'    => 'Host Application Rejected',
				'subject' => 'Update on your Vietstays host application',
				'body'    => '<p>Hi %FULL_NAME%,</p>'
					. '<p>Thank you for your interest in Vietstays. After review, we are unable to approve your application at this time.</p>'
					. '%REJECTION_REASON_BLOCK%'
					. '%REJECTION_COMMENT_BLOCK%'
					. '<p>If you believe this decision was made in error, you may appeal by emailing <a href="mailto:%CONTACT_EMAIL%">%CONTACT_EMAIL%</a> and including your application ID <strong>%APPLICATION_REF%</strong>.</p>',
			],
			'host_welcome' => [
				'name'    => 'Host Welcome',
				'subject' => 'Welcome to the Vietstays host team',
				'body'    => '<p>Hi %FULL_NAME%,</p>'
					. '<p>Welcome to Vietstays! Your email is verified and your host backend account is now active.</p>'
					. '<p>You can sign in to manage apartments, bookings, and your operations team here:</p>'
					. '<p><a href="%HOST_PORTAL_LINK%">%HOST_PORTAL_LINK%</a></p>'
					. '<p>We recommend bookmarking the portal and completing your apartment setup as your first step.</p>',
			],
			'host_activation_reminder' => [
				'name'    => 'Host Activation Reminder',
				'subject' => 'Reminder: activate your Vietstays host account',
				'body'    => '<p>Hi %FULL_NAME%,</p>'
					. '<p>This is a friendly reminder to verify your email and set your password so you can access the Vietstays host portal.</p>'
					. '<p><a href="%ACTIVATION_LINK%">Activate my host account</a></p>',
			],
			'conversion_invite' => [
				'name'    => 'Conversion Invite',
				'subject' => 'Book %APARTMENT_NAME% on Vietstays — %DISCOUNT_PCT%% off your next stay',
				'body'    => '<p>Hi %GUEST_NAME%,</p>'
					. '<p>Your host at <strong>%APARTMENT_NAME%</strong> invites you to book your next stay directly on Vietstays and receive <strong>%DISCOUNT_PCT%% off</strong>.</p>'
					. '<p><a href="%CONVERSION_LINK%">Claim your discount and book now</a></p>'
					. '<p>Your personal promo code: <strong>%PROMO_CODE%</strong></p>'
					. '<p>This offer is valid for one booking and expires in 3 months.</p>'
					. '<p>Questions? Contact us at <a href="mailto:%CONTACT_EMAIL%">%CONTACT_EMAIL%</a>.</p>',
			],
		];
	}

	public static function seed( $overwrite = false ) {
		global $wpdb;

		$templates = new vvEmailTemplates();
		$results   = [];

		foreach ( self::defaults() as $code => $row ) {
			$existing    = $templates->get_email_template_by_code( $code );
			$existing_id = $templates->get_template_row_id( $existing );
			$data        = $templates->filter_row_data( [
				'code'    => $code,
				'name'    => $row['name'],
				'subject' => $row['subject'],
				'body'    => $row['body'],
			] );

			if ( $existing_id > 0 && ! $overwrite && ! self::is_empty( $existing, $templates ) ) {
				$results[] = [
					'code'   => $code,
					'action' => 'skipped',
				];
				continue;
			}

			if ( $existing_id > 0 ) {
				$pk      = $templates->get_primary_key_column();
				$updated = $wpdb->update( 'vv_email_templates', $data, [ $pk => $existing_id ] );
				$results[] = [
					'code'   => $code,
					'action' => ( $updated === false && $wpdb->last_error !== '' ) ? 'error: ' . $wpdb->last_error : 'updated',
				];
			} else {
				$inserted = $wpdb->insert( 'vv_email_templates', $data );
				$results[] = [
					'code'   => $code,
					'action' => ( $inserted === false ) ? 'error: ' . $wpdb->last_error : 'inserted',
				];
			}
		}

		return $results;
	}

	private static function is_empty( $existing, vvEmailTemplates $templates ) {
		if ( ! is_array( $existing ) || $templates->get_template_row_id( $existing ) <= 0 ) {
			return true;
		}
		return trim( (string) gArrayItem( $existing, 'subject' ) ) === ''
			|| trim( (string) $templates->get_template_body( $existing ) ) === '';
	}

	public static function render_seed_response( array $results, $overwrite = false ) {
		header( 'Content-Type: text/html; charset=utf-8' );
		echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Host email templates</title></head><body style="font-family:sans-serif;padding:24px;">';
		echo '<h1>Host email template seed</h1>';
		echo '<p>Mode: ' . ( $overwrite ? 'overwrite all' : 'insert missing or fill empty templates' ) . '</p>';
		echo '<ul>';
		foreach ( $results as $row ) {
			echo '<li><strong>' . esc_html( $row['code'] ) . '</strong>: ' . esc_html( $row['action'] ) . '</li>';
		}
		echo '</ul>';
		echo '<p><a href="' . esc_url( vv_admin_url( 'settings/email-templates' ) ) . '">Open email templates in admin</a></p>';
		echo '<p style="color:#666;font-size:13px;">Available tokens: %FULL_NAME%, %APPLICATION_REF%, %CONTACT_EMAIL%, %ACTIVATION_LINK%, %HOST_PORTAL_LINK%, %REJECTION_REASON_BLOCK%, %REJECTION_COMMENT_BLOCK%</p>';
		echo '</body></html>';
	}
}

