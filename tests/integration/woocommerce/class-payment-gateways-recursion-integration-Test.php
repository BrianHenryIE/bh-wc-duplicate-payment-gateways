<?php
/**
 * With the plugin's hooks registered, constructing `WC_Payment_Gateways` must not re-enter its own constructor.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\Settings;
use Codeception\TestCase\WPTestCase;
use WC_Payment_Gateways;

/**
 * @coversNothing
 */
class Payment_Gateways_Recursion_Integration_Test extends WPTestCase {

	protected function tearDown(): void {
		unset( $_GET['page'], $_GET['tab'], $_GET['duplicates'] );
		parent::tearDown();
	}

	/**
	 * The plugins.php "Gateways" link opened `wc-settings&tab=checkout&duplicates={plugin-slug}`, and a
	 * `woocommerce_payment_gateways` filter then called `WC()->payment_gateways()` while WooCommerce was still
	 * inside `WC_Payment_Gateways::__construct()`, before `WC_Payment_Gateways::$_instance` was set: infinite recursion.
	 */
	public function test_no_recursion_on_payments_page_with_duplicates_parameter(): void {

		( new Settings() )->add_new_duplicate( 'duplicable_test_gateway_2', Duplicable_Test_Gateway::class );

		$_GET['page']       = 'wc-settings';
		$_GET['tab']        = 'checkout';
		$_GET['duplicates'] = 'woocommerce';

		$depth = 0;
		$guard = function ( array $gateways ) use ( &$depth ): array {
			++$depth;
			if ( $depth > 1 ) {
				throw new \RuntimeException( 'woocommerce_payment_gateways filter re-entered.' );
			}
			return $gateways;
		};
		add_filter( 'woocommerce_payment_gateways', $guard, 1 );

		// Reset the singleton so the constructor runs again.
		$instance_property = new \ReflectionProperty( WC_Payment_Gateways::class, '_instance' );
		$instance_property->setValue( null, null );

		WC_Payment_Gateways::instance();

		remove_filter( 'woocommerce_payment_gateways', $guard, 1 );

		$this->assertEquals( 1, $depth );
	}
}
