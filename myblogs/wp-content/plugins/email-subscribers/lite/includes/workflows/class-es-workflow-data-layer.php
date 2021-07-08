<?php

/**
 * @class ES_Workflow_Data_Layer
 */
class ES_Workflow_Data_Layer {

	private $data = array();

	/**
	 * @param array $data
	 */
	function __construct( $data = array() ) {

		if ( is_array( $data ) ) {
			$this->data = $data;
		}

		$this->init();
	}


	/**
	 * Initiate the data layer
	 */
	function init() {
		do_action( 'ig_es_data_layer_init' );
	}


	function clear() {
		$this->data = array();
	}


	/**
	 * Returns unvalidated data layer
	 *
	 * @return array
	 */
	function get_raw_data() {
		return $this->data;
	}


	/**
	 * @param $type
	 * @param $item
	 */
	function set_item( $type, $item ) {
		$this->data[ $type ] = $item;
	}


	/**
	 * @param string $type
	 * @return mixed
	 */
	function get_item( $type ) {

		if ( ! isset( $this->data[ $type ] ) ) {
			return false;
		}

		return ig_es_validate_data_item( $type, $this->data[ $type ] );
	}

	/**
	 * Is the data layer missing data?
	 *
	 * Data can be missing if it has been deleted e.g. if an order has been trashed.
	 *
	 * @since 4.6
	 *
	 * @return bool
	 */
	public function is_missing_data() {
		$is_missing = false;

		foreach ( $this->get_raw_data() as $data_item ) {

			if ( ! $data_item ) {
				$is_missing = true;
			}
		}

		return $is_missing;
	}

}
