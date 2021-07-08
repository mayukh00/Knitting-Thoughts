<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ES_Import_Subscribers {
	/**
	 * ES_Import_Subscribers constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
	}

	/**
	 * Import Contacts
	 *
	 * @since 4.0,0
	 *
	 * @modify 4.3.1
	 *
	 * @modfiy 4.4.4 Moved importing code section to maybe_start_import method.
	 */
	public function import_callback() {

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$submit = ig_es_get_post_data( 'submit' );
		if ( $submit ) {
			$import_contacts_nonce = ig_es_get_post_data( 'import_contacts' );
			if ( ! isset( $_POST['import_contacts'] ) || ! wp_verify_nonce( sanitize_key( $import_contacts_nonce ), 'import-contacts' ) ) {
				$message = __( "Sorry, you do not have permission to import contacts.", 'email-subscribers' );
				ES_Common::show_message( $message, 'error' );
			}

			if ( isset( $_FILES["file"] ) ) {

				$max_upload_size = $this->get_max_upload_size();
				if ( is_uploaded_file( $_FILES["file"]["tmp_name"] ) ) {

					$tmp_file = $_FILES["file"]["tmp_name"];
					$file     = $_FILES['file']['name'];

					$ext = strtolower( substr( $file, strrpos( $file, "." ), ( strlen( $file ) - strrpos( $file, "." ) ) ) );

					if ( $ext == ".csv" ) {
						$file_size 		 = $_FILES['file']['size'];

						// Check if CSV file size is less than or equal to max upload size.
						if( $file_size <= $max_upload_size ) {
							if ( ! ini_get( "auto_detect_line_endings" ) ) {
								ini_set( "auto_detect_line_endings", '1' );
							}

							$statuses        = ES_Common::get_statuses_key_name_map();
							$es_email_status = ig_es_get_post_data( 'es_email_status' );

							$status = '';
							if ( in_array( $es_email_status, array_keys( $statuses ) ) ) {
								$status = $es_email_status;
							}

							if ( ! empty( $status ) ) {

								$lists = ES()->lists_db->get_id_name_map();

								$list_id = ig_es_get_post_data( 'list_id' );

								if ( ! in_array( $list_id, array_keys( $lists ) ) ) {
									$list_id = '';
								}

								if ( ! empty( $list_id ) ) {

									$delimiter = $this->get_delimiter( $tmp_file );

									$handle = fopen( $tmp_file, 'r' );

									// Get Headers
									$headers = array_map( 'trim', fgetcsv( $handle, 0, $delimiter ) );

									$existing_contacts_email_id_map = ES()->contacts_db->get_email_id_map();

									$existing_contacts = array();
									if ( count( $existing_contacts_email_id_map ) > 0 ) {
										$existing_contacts = array_keys( $existing_contacts_email_id_map );
										$existing_contacts = array_map( 'strtolower', $existing_contacts );
									}

									$invalid_emails_count = $imported_subscribers_count = $existing_contacts_count = 0;
									$emails               = array();

									$values            = $place_holders = $contacts_data = array();
									$current_date_time = ig_get_current_date_time();

									$headers_column_count = count( $headers );
									while ( ( $data = fgetcsv( $handle, 0, $delimiter ) ) !== false ) {

										$data = array_map( 'trim', $data );
										$data_column_count = count( $data );

										// Verify if number of headers columns are equal to number of data columns.
										if( $headers_column_count !== $data_column_count ) {
										    $invalid_emails_count ++;
											continue;
										}

										$data = array_combine( $headers, $data );

										$email = isset( $data['Email'] ) ? strtolower( sanitize_email( trim( $data['Email'] ) ) ) : '';

										if ( empty( $email ) || ! filter_var( $email, FILTER_VALIDATE_EMAIL )) {
											$invalid_emails_count ++;
											continue;
										}

										if ( ! in_array( $email, $existing_contacts ) ) {

											// Convert emoji characters to equivalent HTML entities to avoid WordPress sanitization error in SQL query while bulk inserting contacts.
											$name      = isset( $data['Name'] ) ? ES_Common::handle_emoji_characters( sanitize_text_field(trim( $data['Name'] ) ) ) : '';
											$first_name = isset( $data['First Name'] ) ? ES_Common::handle_emoji_characters( sanitize_text_field( trim( $data['First Name'] ) ) ) : '';
											$last_name = isset( $data['Last Name'] ) ? ES_Common::handle_emoji_characters( sanitize_text_field( trim( $data['Last Name'] ) ) ) : '';

											// If we don't get the first_name & last_name, consider Name field.
											// If name empty, get the name from Email
											if ( empty( $first_name ) && empty( $last_name ) ) {

												if ( empty( $name ) ) {
													$name = ES_Common::get_name_from_email( $email );
												}

												$names      = ES_Common::prepare_first_name_last_name( $name );
												$first_name = sanitize_text_field( $names['first_name'] );
												$last_name  = sanitize_text_field( $names['last_name'] );
											}

											$guid = ES_Common::generate_guid();

											$contacts_data[ $imported_subscribers_count ]['first_name'] = $first_name;
											$contacts_data[ $imported_subscribers_count ]['last_name']  = $last_name;
											$contacts_data[ $imported_subscribers_count ]['email']      = $email;
											$contacts_data[ $imported_subscribers_count ]['source']     = 'import';
											$contacts_data[ $imported_subscribers_count ]['status']     = 'verified';
											$contacts_data[ $imported_subscribers_count ]['hash']       = $guid;
											$contacts_data[ $imported_subscribers_count ]['created_at'] = $current_date_time;

											$existing_contacts[] = $email;

										    $emails[] = $email;

										    $imported_subscribers_count ++;
										} else {
											$existing_contacts_count ++;
										}
									}

									$message = '';
									$response_status = 'error';
									if ( count( $emails ) > 0 ) {

									    $response_status  = 'success';

										ES()->contacts_db->bulk_insert( $contacts_data );

										$contact_ids = ES()->contacts_db->get_contact_ids_by_emails( $emails );
										if ( count( $contact_ids ) > 0 ) {
											ES()->lists_contacts_db->remove_contacts_from_lists( $contact_ids, $list_id );
											ES()->lists_contacts_db->do_import_contacts_into_list( $list_id, $contact_ids, $status, 1, $current_date_time );
										}

										$message = sprintf( __( 'Total %d contacts have been imported successfully!', 'email-subscribers' ), $imported_subscribers_count );

									}

                                    if ( $existing_contacts_count > 0 ) {
                                        $message .= " ";
                                        $message .= sprintf( __( '%d contact(s) are already exists.', 'email-subscribers' ), $existing_contacts_count );
                                    }

                                    if ( $invalid_emails_count > 0 ) {
                                        $message .= " ";
                                        $message .= sprintf( __( '%d contact(s) are invalid.', 'email-subscribers' ), $invalid_emails_count );
                                    }

									fclose( $handle );
                                    
									ES_Common::show_message( $message, $response_status );

								} else {
									$message = __( "Error: Please Select List", 'email-subscribers' );
									ES_Common::show_message( $message, 'error' );
								}
							} else {
								$message = __( "Error: Please select status", 'email-subscribers' );
								ES_Common::show_message( $message, 'error' );
							}
						} else {
							$message = sprintf( __( 'The file you are trying to upload is larger than %s. Please upload a smaller file.', 'email-subscribers' ), esc_html( size_format( $max_upload_size ) ) );
							ES_Common::show_message( $message, 'error' );
						}
					} else {
						$message = __( "Error: Please Upload only CSV File", 'email-subscribers' );
						ES_Common::show_message( $message, 'error' );
					}
				} else {
					if( ! empty( $_FILES['file']['error'] ) ) {
						switch( $_FILES['file']['error'] ) {
							case 1: //uploaded file exceeds the upload_max_filesize directive in php.ini
								$message = sprintf( __( 'The file you are trying to upload is larger than %s. Please upload a smaller file.', 'email-subscribers' ), esc_html( size_format( $max_upload_size ) ) );
								break;
							default: //a default error, just in case!  :)
								$message = __( 'There was a problem with your upload.', 'email-subscribers' );
								break;
						}
					} else {
						$message = __( 'Error: Please Upload File', 'email-subscribers' );
					}

					ES_Common::show_message( $message, 'error' );
				}
			} else {
				$message = __( "Error: Please Upload File", 'email-subscribers' );
				ES_Common::show_message( $message, 'error' );
			}
		}

		$this->prepare_import_subscriber_form();

	}

	public function prepare_import_subscriber_form() {
		$max_upload_size = $this->get_max_upload_size();
		?>

		<div class="tool-box">
			<div class="meta-box-sortables ui-sortable bg-white shadow-md ml-12 mr-8 mt-6 rounded-lg">
				<form class="ml-5 mr-4 text-left pt-4 mt-2 item-center" method="post" name="form_addemail" id="form_addemail" action="#" enctype="multipart/form-data">
					<table class="max-w-full form-table">
						<tbody>

							<tr class="border-b  border-gray-100">
								<th scope="row" class="w-3/12 pt-3 pb-8 text-left">
									<label for="tag-image"><span class="block ml-6 pr-4 text-sm font-medium text-gray-600 pb-1">
										<?php _e( 'Select CSV file', 'email-subscribers' ); ?>
										</span>
										<p class="italic text-xs font-normal text-gray-400 mt-2 ml-6 leading-snug"><?php echo sprintf( __( 'File size should be less than %s', 'email-subscribers' ), esc_html( size_format( $max_upload_size ) ) ); ?></p>
										<p class="italic text-xs font-normal text-gray-400 mt-2 ml-6 leading-snug">
										<?php _e( 'Check CSV structure', 'email-subscribers' ); ?>
										<a class="font-medium" target="_blank" href="<?php echo plugin_dir_url( __FILE__ ) . '../../admin/partials/sample.csv'; ?>"><?php _e( 'from here', 'email-subscribers' ); ?></a></p></label>
									</th>
									<td class="w-9/12 pb-3 ">
										<input class="ml-12" type="file" name="file" id="file"/>
									</td>
								</tr>
								<tr class="border-b border-gray-100">
									<th scope="row" class="w-3/12 pt-3 pb-8 text-left">
										<label for="tag-email-status"><span class="block ml-6 pr-4 text-sm font-medium text-gray-600 pb-2">
											<?php _e( 'Select status', 'email-subscribers' ); ?> </span><p></p>
										</label>
									</th>
									<td class="w-9/12 pb-3">
										<select class="relative form-select shadow-sm border border-gray-400 w-1/3 ml-12" name="es_email_status" id="es_email_status">
											<?php echo ES_Common::prepare_statuses_dropdown_options(); ?>
										</select>
									</td>
								</tr>
								<tr class="border-b border-gray-100">
									<th scope="row" class="w-3/12 pt-3 pb-8 text-left">
										<label for="tag-email-group"><span class="block ml-6 pr-4 text-sm font-medium text-gray-600 pb-2">
											<?php _e( 'Select list', 'email-subscribers' ); ?>
										</label>
									</th>
									<td class="w-9/12 pb-3">
										<select class="relative form-select shadow-sm border border-gray-400 w-1/3 ml-12"
										<select name="list_id" id="list_id">
											<?php echo ES_Common::prepare_list_dropdown_options(); ?>
										</select>
									</td>
								</tr>
							</tbody>
						</table>
						<p style="padding-top:10px;">
							<?php wp_nonce_field( 'import-contacts', 'import_contacts' ); ?>
							<input type="submit" name="submit" class="cursor-pointer ig-es-primary-button px-4 py-2 ml-6 mr-2 my-4" value="<?php _e( "Import", 'email-subscribers' ); ?>" />
						</p>
					</form>
				</div>

				<?php
			}

	/**
	 * Show import contacts
	 *
	 * @since 4.0.0
	 */
	public function import_subscribers_page() {

		$audience_tab_main_navigation = array();
		$active_tab                   = 'import';
		$audience_tab_main_navigation = apply_filters( 'ig_es_audience_tab_main_navigation', $active_tab, $audience_tab_main_navigation );

		?>

		<div class="wrap max-w-full mt-1 font-sans">
			<header class="ml-12 wp-heading-inline">

			<div class="mt-2">
				<h2 class="text-2xl font-medium text-gray-800 sm:leading-9 sm:truncate">
					<span class="text-base font-normal leading-7 text-indigo-600 sm:leading-9 sm:truncate"> <a href="admin.php?page=es_subscribers"><?php _e( 'Audience', 'email-subscribers' ); ?> </a> </span> <svg fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 mt-1 inline-block align-middle"><path d="M9 5l7 7-7 7"></path></svg> <?php _e('Import Contacts', 'email-subscribers'); ?>
					<?php
					ES_Common::prepare_main_header_navigation( $audience_tab_main_navigation );
					?>
				</div>
			</h2>

		</header>

		<div class="ml-12 mr-8"><hr class="wp-header-end"></div>
		<?php $this->import_callback(); ?>
	</div>

	<?php
	}

	/**
	 * Get CSV file delimiter
	 *
	 * @param $file
	 * @param int $check_lines
	 *
	 * @return mixed
	 *
	 * @since 4.3.1
	 */
	public function get_delimiter( $file, $check_lines = 2 ) {

		$file = new SplFileObject( $file );

		$delimiters = array( ',', '\t', ';', '|', ':' );
		$results    = array();
		$i          = 0;
		while ( $file->valid() && $i <= $check_lines ) {
			$line = $file->fgets();
			foreach ( $delimiters as $delimiter ) {
				$regExp = '/[' . $delimiter . ']/';
				$fields = preg_split( $regExp, $line );
				if ( count( $fields ) > 1 ) {
					if ( ! empty( $results[ $delimiter ] ) ) {
						$results[ $delimiter ] ++;
					} else {
						$results[ $delimiter ] = 1;
					}
				}
			}
			$i ++;
		}

		if ( count( $results ) > 0 ) {

			$results = array_keys( $results, max( $results ) );

			return $results[0];
		}

		return ',';

	}

	/**
	 * Method to get max upload size
	 *
	 * @return int $max_upload_size
	 *
	 * @since 4.4.6
	 */
	public function get_max_upload_size() {

		$max_upload_size    = 2097152; // 2MB.
		$wp_max_upload_size = wp_max_upload_size();
		$max_upload_size    = min( $max_upload_size, $wp_max_upload_size );

		return apply_filters( 'ig_es_max_upload_size', $max_upload_size );
	}

}

