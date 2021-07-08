<?php
/**
 * Action to send an email to provided email address.
 *
 * @author      Icegram
 * @since       4.5.3
 * @version     1.0
 * @package     Email Subscribers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @class ES_Action_Send_Email_Abstract
 * 
 * @since 4.5.3
 */
abstract class ES_Action_Send_Email_Abstract extends ES_Workflow_Action {

	/**
	 * Load admin props.
	 * 
	 * @since 4.5.3 
	 */
	public function load_admin_details() {
		$this->group = __( 'Email', 'email-subscribers' );
	}

	/**
	 * Load fields.
	 * 
	 * @since 4.5.3
	 */
	public function load_fields() {
		$send_to = new ES_Text();
		$send_to->set_name( 'ig-es-send-to' );
		$send_to->set_title( __( 'Send To', 'email-subscribers' ) );
		$send_to->set_description( __( 'Enter emails here or use variable such as {{EMAIL}}. Multiple emails can be separated by commas.', 'email-subscribers' ) );
		$send_to->set_placeholder( __( 'E.g. {{EMAIL}}, admin@example.com', 'email-subscribers' ) );
		$send_to->set_required();

		$subject = new ES_Text();
		$subject->set_name( 'ig-es-email-subject' );
		$subject->set_title( __( 'Email subject', 'email-subscribers' ) );
		$subject->set_required();

		$this->add_field( $send_to );
		$this->add_field( $subject );
	}
}
