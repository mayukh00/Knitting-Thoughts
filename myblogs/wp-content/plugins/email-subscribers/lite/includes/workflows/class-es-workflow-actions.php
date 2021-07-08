<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @class ES_Workflow_Actions
 * @since 4.4.1
 */
class ES_Workflow_Actions extends ES_Workflow_Registry {

	/** @var array */
	static $includes;

	/** @var array  */
	static $loaded = array();


	/**
	 * @return array
	 */
	static function load_includes() {

		$includes = array(
			'ig_es_add_to_list'    => 'ES_Action_Add_To_List',
			'ig_es_delete_contact' => 'ES_Action_Delete_Contact',
			'ig_es_update_contact' => 'ES_Action_Update_Contact',
		);

		return apply_filters( 'ig_es_workflow_actions', $includes );
	}


	/**
	 * @param $action_name string
	 * @return Action|false
	 */
	static function get( $action_name ) {
		static::load( $action_name );

		if ( ! isset( static::$loaded[ $action_name ] ) ) {
			return false;
		}

		return static::$loaded[ $action_name ];
	}


	/**
	 * @return ES_Workflow_Action[]
	 */
	static function get_all() {
		foreach ( static::get_includes() as $name => $path ) {
			static::load( $name );
		}

		return static::$loaded;
	}


	/**
	 * @param $action_name
	 */
	static function load( $action_name ) {
		if ( static::is_loaded( $action_name ) ) {
			return;
		}

		$action   = false;
		$includes = static::get_includes();

		if ( ! empty( $includes[ $action_name ] ) ) {

			/** @var ES_Workflow_Action $action */
			$action = new $includes[ $action_name ]();
			$action->set_name( $action_name );
		}

		static::$loaded[ $action_name ] = $action;
	}

}
