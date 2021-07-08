<?php
/**
 * Admin workflow actions metabox
 *
 * @author      Icegram
 * @since       4.4.1
 * @version     1.0
 * @package     Email Subscribers
 */

/**
 * Worfklow object
 *
 * @var ES_Workflow $workflow
 *
 * Workflow Action objects
 * @var ES_Workflow_Action[] $actions
 *
 * Action select box value
 * @var array $action_select_box_value
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="ig-es-actions-container">

	<?php if ( is_array( $actions ) ) : ?>
		<?php $n = 1; ?>
		<?php
		foreach ( $actions as $action ) : // phpcs:ignore
			ES_Workflow_Admin::get_view(
				'action',
				array(
					'workflow'                 => $workflow,
					'action'                   => $action,
					'action_number'            => $n,
					'action_select_box_values' => $action_select_box_values,
				)
			);
			?>
			<?php $n++; ?>
		<?php endforeach; ?>
	<?php endif; ?>
	<div class="ig-es-action-template">
		<?php
			// Render blank action template.
			ES_Workflow_Admin::get_view(
				'action',
				array(
					'workflow'                 => $workflow,
					'action'                   => false,
					'action_number'            => false,
					'action_select_box_values' => $action_select_box_values,
				)
			);
			?>
	</div>

	<?php if ( empty( $actions ) ) : ?>

		<div class="js-ig-es-no-actions-message">
			<p><?php echo __( 'No actions found. Click the <strong>+ Add Action</strong> to create an action.', 'email-subscribers' ); // phpcs:ignore ?></p>
		</div>

	<?php endif; ?>
</div>

<div class="ig-es-metabox-footer">
	<button type="button" class="js-ig-es-add-action inline-flex justify-center rounded-md border border-transparent px-4 py-1.5 bg-white text-sm leading-5 font-medium text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:shadow-outline-blue transition ease-in-out duration-150"><?php echo esc_html__( '+ Add Action', 'email-subscribers' ); ?></button>
</div>
