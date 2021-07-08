<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ES_IG_Redirect' ) ) {

	class ES_IG_Redirect {

		/**
		 * Redirect to icegram if require
		 *
		 * @since 4.4.1
		 */
		public static function go_to_icegram() {

			global $ig_es_tracker;

			$ig_install_url = admin_url( 'plugin-install.php?s=icegram&tab=search&type=term' );

			$action = ig_es_get_request_data( 'action' );

			$redirect_url = admin_url( 'post-new.php?post_type=ig_campaign' );
			$ig_activate_url = admin_url( 'plugins.php?plugin_status=inactive' );

			if ( 'create_campaign' === $action ) {
				$redirect_url = admin_url( 'edit.php?post_type=ig_campaign' );
				$info         = __( 'Create Onsite Campaigns Using <a href="https://wordpress.org/plugins/icegram">Icegram</a>', 'email-subscribers' );
			} elseif ( 'create_template' === $action ) {
				$redirect_url = admin_url( 'edit.php?ig_campaign&page=icegram-gallery' );
				$info         = __( 'Create Popups using <a href="https://wordpress.org/plugins/icegram">Icegram</a>', 'email-subscribers' );
			}

			$icegram_plugin = 'icegram/icegram.php';

			$active_plugins   = $ig_es_tracker::get_active_plugins();
			$inactive_plugins = $ig_es_tracker::get_inactive_plugins();

			/**
			 * If Icegram Installed & Activated
			 *  - Redirect to specific Icegram page
			 *
			 * If Icegram Installed & Not Activated
			 *  - Show Intermediate page & ask them to activate Icegram
			 *
			 * If Icegram is not installed
			 *  - Show Intermediate page & ask them to Install & activate Icegram
			 *
			 */

			if ( in_array( $icegram_plugin, $active_plugins ) ) {
				wp_safe_redirect( $redirect_url );
				exit;
			} else { ?>
                <div class="wrap" id="ig-es-container">
                    <header class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 ">
                        <div class="md:flex md:items-center md:justify-between">
                            <div class="flex-1 min-w-0">
                                <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl sm:leading-9 sm:truncate">
									<?php echo $info; ?>
                                </h2>
                            </div>

                            <div class="mt-4 flex md:mt-0 md:ml-4">
                			<span class="ml-3 shadow-sm rounded-md">
		                		<div id="ig-es-create-button" class="relative inline-block text-left">
		                       	 	<div>
		                          		<span class="rounded-md shadow-sm">

		                              		<?php
			                                if ( in_array( $icegram_plugin, $inactive_plugins ) ) {
			                                    ?> <a href="<?php echo $ig_activate_url ?>"><button type="button" class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-white text-sm leading-5 font-medium text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:shadow-outline-blue transition ease-in-out duration-150"> <?php _e( 'Activate Icegram','email-subscribers' );
			                                } else {
			                                    ?> <a href="<?php echo $ig_install_url ?>"><button type="button" class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-white text-sm leading-5 font-medium text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:shadow-outline-blue transition ease-in-out duration-150"> <?php _e( 'Install Icegram','email-subscribers' );
			                                } ?>
		                              		</button></a>
		                          		</span>
		            				</div>
		        				</div>
        					</span>
                            </div>

                    </header>

                    <main class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <section class="md:flex md:items-start md:justify-between sm:px-4 py-2 my-4 sm:px-0 rounded-lg bg-white shadow sm:grid sm:grid-cols-3">

                            <div class="flex min-w-0 mr-4 pl-1">
                                <div class="relative bg-white rounded ">
                                    <picture class="block">
                                        <img class="border-0 h-20 mx-auto my-2" src="<?php echo ES_PLUGIN_URL ?>lite/admin/images/icegram-engage-visitors-person-flow.png" alt="feature-img">
                                    </picture>
                                    <div class="pt-2 block mr-6">
                                        <h3 class="text-gray-700 font-bold md:text-base lg:text-lg">Engage Visitors</h3>
                                        <p class="md:pt-6 lg:pt-2 text-sm font-normal text-gray-600 leading-snug">
                                            Show right messages to right people at the right time in the right place. Drive people to landing pages, promotions and stop them from bouncing away.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex min-w-0 mr-4 pl-2">
                                <div class="relative bg-white rounded">
                                    <picture class="block">
                                        <img class="border-0 h-20 mx-auto my-2" src="<?php echo ES_PLUGIN_URL ?>lite/admin/images/icegram-engage-more-subscribers-and-visitors.png" alt="feature-img">
                                    </picture>
                                    <div class="pt-2 block mr-6">
                                        <h3 class="text-lg text-gray-700 font-bold md:text-base lg:text-lg">More Subscribers & Customers</h3>
                                        <p class="text-sm pt-2 font-normal text-gray-600 leading-snug">
                                            Dramatically increase opt-ins and sales. Easily run powerful onsite marketing campaigns. Marketers, owners and visitors– everyone loves Icegram!
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex min-w-0 mr-4 pl-2">
                                <div class="relative bg-white rounded">
                                    <picture class="block">
                                        <img class="border-0 h-20 mx-auto my-2" src="<?php echo ES_PLUGIN_URL ?>lite/admin/images/icegram-engage-optimize-results.png" alt="feature-img">
                                    </picture>
                                    <div class="pt-2 block mr-6">
                                        <h3 class="text-lg text-gray-700 font-bold md:text-base lg:text-lg">Optimize Results</h3>
                                        <p class="md:pt-6 lg:pt-2 lg:text-sm font-normal text-gray-600 leading-snug">
                                            Keep growing. Get everything you need to target, measure, re-target, behavior rules, personalize, split test, segment, automate and optimize.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </main>

                    <main class="max-w-7xl mx-auto sm:px-6 lg:px-8 -py-4">
                        <section class="md:flex md:items-start md:justify-between sm:px-4 py-2 my-8 sm:px-0 sm:grid sm:grid-cols-2">
                            <div class="flex min-w-0 mr-2 pl-1 mx-8 my-4">
                                <div class="relative">
                                    <picture class="block ">
                                        <img class="w-11/12 border-0 h-62 rounded-lg bg-white shadow" src="<?php echo ES_PLUGIN_URL ?>lite/admin/images/optin_form_1.png" alt="feature-img">
                                    </picture>
                                </div>
                            </div>
                            <div class="flex min-w-0 mr-2 pl-2 mx-8 my-4">
                                <div class="relative">
                                    <picture class="block">
                                        <img class="w-11/12 border-0 h-62 rounded-lg bg-white shadow" src="<?php echo ES_PLUGIN_URL ?>lite/admin/images/optin_form_2.png" alt="feature-img">
                                    </picture>
                                </div>
                            </div>
                        </section>
                    </main>
                </div>

			<?php }
		}
	}
}
