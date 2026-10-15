<?php
/**
 * `is_safe_to_duplicate()` creates anonymous subclasses the same way `Payment_Gateways::add_gateways()` does.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\API_Interface;
use Codeception\TestCase\WPTestCase;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Payment_Gateways_List_UI
 */
class Payment_Gateways_List_UI_Deprecation_WPUnit_Test extends WPTestCase {

	/**
	 * `is_callable( 'parent::__construct' )` is deprecated since PHP 8.2.
	 *
	 * @covers ::is_safe_to_duplicate
	 */
	public function test_is_safe_to_duplicate_no_deprecations(): void {

		$sut = new Payment_Gateways_List_UI( $this->makeEmpty( API_Interface::class ), new Gateway_Duplicability() );

		$deprecations = array();
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler
		set_error_handler(
			function ( int $errno, string $errstr ) use ( &$deprecations ): bool {
				$deprecations[] = $errstr;
				return true;
			},
			E_DEPRECATED
		);

		try {
			ob_start();
			$sut->print_column_for_duplicate_ui( new Duplicable_Test_Gateway() );
			ob_end_clean();
		} finally {
			restore_error_handler();
		}

		$this->assertEmpty( $deprecations, implode( PHP_EOL, $deprecations ) );
	}
}
