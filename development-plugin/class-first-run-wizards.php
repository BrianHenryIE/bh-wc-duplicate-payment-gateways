<?php
/**
 * Stop WooCommerce redirecting to its onboarding screens.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\Development_Plugin;

/**
 * Filters to disable the WooCommerce setup wizard, task list, etc.
 */
class First_Run_Wizards {

	/**
	 * Add actions.
	 */
	public function register_hooks(): void {
		add_action( 'plugins_loaded', array( $this, 'woocommerce' ), 0 );
	}

	/**
	 * Disable the WooCommerce onboarding.
	 *
	 * @hooked plugins_loaded
	 *
	 * @see https://github.com/bakerdotdev/Disable-WooCommerce-Setup-Wizard/blob/main/disable-woocommerce-setup-wizard.php
	 */
	public function woocommerce(): void {
		add_filter( 'woocommerce_enable_setup_wizard', '__return_false', 0 );
		add_filter( 'woocommerce_prevent_automatic_wizard_redirect', '__return_true', 0 );
		add_filter( 'woocommerce_task_list_hidden', '__return_true', 0 );
		remove_action( 'admin_init', 'wc_admin_redirect_to_home', 20 );
	}
}
