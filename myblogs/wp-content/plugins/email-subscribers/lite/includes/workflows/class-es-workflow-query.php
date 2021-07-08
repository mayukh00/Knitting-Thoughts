<?php
/**
 * Query workflows based on auguements.
 *
 * @author      Icegram
 * @since       4.4.1
 * @version     1.0
 * @package     Email Subscribers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to query workflows based on arguements.
 *
 * @class Workflow_Query
 *
 * @since 4.4.1
 */
class ES_Workflow_Query {

	/**
	 * Trigger name
	 *
	 * @var string|ES_Workflow_Trigger
	 */
	public $trigger;

	/**
	 * Query arguements
	 *
	 * @var array
	 */
	public $args;

	/**
	 * Return result type
	 *
	 * @var string
	 */
	public $return = 'objects';

	/**
	 * Construct
	 */
	public function __construct() {
		$this->args = array(
			'status'   => 1,
			'type'     => 0, // Fetch only user defined workflows.
			'order'    => 'ASC',
			'order_by' => 'priority',
		);
	}


	/**
	 * Set trigger name or array of names to query.
	 *
	 * @param string|ES_Workflow_Trigger $trigger Workflow trigger object|name.
	 */
	public function set_trigger( $trigger ) {
		if ( $trigger instanceof ES_Workflow_Trigger ) {
			$this->trigger = $trigger->get_name();
		} else {
			$this->trigger = $trigger;
		}
	}

	/**
	 * Set return object
	 *
	 * @param objects|ids $return Result format ids or objects.
	 */
	public function set_return( $return ) {
		$this->return = $return;
	}


	/**
	 * Get workflows based on query arguements
	 *
	 * @return ES_Workflow[] $workflows Workflow object
	 */
	public function get_results() {

		if ( $this->trigger ) {
			$this->args['trigger_name'] = $this->trigger;
		}

		$this->args['fields'] = array();
		if ( 'ids' === $this->return ) {
			$this->args['fields'][] = 'id';
		}

		$results = ES()->workflows_db->get_workflows( $this->args );

		if ( ! $results ) {
			return array();
		}

		$workflows = array();

		foreach ( $results as $post ) {

			if ( 'ids' === $this->return ) {
				$workflows[] = $post;
			} else {
				$workflow    = new ES_Workflow( $post );
				$workflows[] = $workflow;
			}
		}

		return $workflows;
	}
}
