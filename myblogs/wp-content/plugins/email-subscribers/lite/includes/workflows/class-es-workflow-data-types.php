<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @class ES_Data_Types
 * @since 2.4.6
 */
class ES_Workflow_Data_Types extends ES_Workflow_Registry {

	/** @var array */
	static $includes;

	/** @var array  */
	static $loaded = array();

	/**
	 * @return array
	 */
	static function load_includes() {
		return apply_filters(
			'ig_es_data_types_includes',
			array(
				'user' => 'ES_Data_Type_User',
			)
		);
	}

	/**
	 * @param $data_type_id
	 * @return Data_Type|false
	 */
	static function get( $data_type_id ) {
		return parent::get( $data_type_id );
	}

	/**
	 * @param string    $data_type_id
	 * @param Data_Type $data_type
	 */
	static function after_loaded( $data_type_id, $data_type ) {
		$data_type->set_id( $data_type_id );
	}
}
