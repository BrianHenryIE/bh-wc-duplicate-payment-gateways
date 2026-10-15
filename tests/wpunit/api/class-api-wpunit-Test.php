<?php
/**
 * Tests for the API class against real WooCommerce gateways.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\API;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Duplicable_Test_Gateway;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Gateway_Duplicability;
use Codeception\TestCase\WPTestCase;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Duplicate_Payment_Gateways\API\API
 */
class API_WPUnit_Test extends WPTestCase {

	protected function setUp(): void {
		parent::setUp();

		add_filter(
			'woocommerce_payment_gateways',
			function ( array $gateways ): array {
				$gateways[] = Duplicable_Test_Gateway::class;
				return $gateways;
			}
		);

		$this->reload_gateways();
	}

	/**
	 * `WC_Payment_Gateways::init()` appends to the existing list, so empty it first.
	 */
	protected function reload_gateways(): void {
		WC()->payment_gateways()->payment_gateways = array();
		WC()->payment_gateways()->init();
	}

	/**
	 * @covers ::add_new_duplicate_payment_gateway
	 */
	public function test_add_new_duplicate_saves_config(): void {

		$settings = new Settings();
		$sut      = new API( $settings, new Gateway_Duplicability() );

		$result = $sut->add_new_duplicate_payment_gateway( 'duplicable_test_gateway', 'duplicable_test_gateway_2' );

		$this->assertTrue( $result['success'] );

		$configs = $settings->get_configs();
		$this->assertArrayHasKey( 'duplicable_test_gateway_2', $configs );
		$this->assertEquals( Duplicable_Test_Gateway::class, $configs['duplicable_test_gateway_2']['class_name'] );
	}

	/**
	 * @covers ::add_new_duplicate_payment_gateway
	 */
	public function test_add_new_duplicate_fails_when_id_exists(): void {

		$sut = new API( new Settings(), new Gateway_Duplicability() );

		$result = $sut->add_new_duplicate_payment_gateway( 'duplicable_test_gateway', 'cheque' );

		$this->assertFalse( $result['success'] );
	}

	/**
	 * @covers ::add_new_duplicate_payment_gateway
	 */
	public function test_add_new_duplicate_fails_when_gateway_does_not_exist(): void {

		$sut = new API( new Settings(), new Gateway_Duplicability() );

		$result = $sut->add_new_duplicate_payment_gateway( 'no_such_gateway', 'no_such_gateway_2' );

		$this->assertFalse( $result['success'] );
	}

	/**
	 * WooCommerce's cheque gateway sets `$this->id = 'cheque'` in its constructor, so a duplicate would just be
	 * another cheque gateway.
	 *
	 * @covers ::add_new_duplicate_payment_gateway
	 */
	public function test_add_new_duplicate_refuses_gateway_which_overwrites_its_id(): void {

		$settings = new Settings();
		$sut      = new API( $settings, new Gateway_Duplicability() );

		$result = $sut->add_new_duplicate_payment_gateway( 'cheque', 'cheque_2' );

		$this->assertFalse( $result['success'] );
		$this->assertArrayNotHasKey( 'cheque_2', $settings->get_configs() );
	}

	/**
	 * When the plugin providing the original gateway is deactivated, the duplicate is not loaded, but its config
	 * should still be deletable.
	 *
	 * @covers ::delete_duplicate_payment_gateway
	 */
	public function test_delete_orphaned_duplicate(): void {

		$settings = new Settings();
		$settings->add_new_duplicate( 'orphan_gateway_2', 'No_Longer_Active_Gateway' );
		update_option( 'woocommerce_orphan_gateway_2_settings', array( 'enabled' => 'yes' ) );

		$sut = new API( $settings, new Gateway_Duplicability() );

		$result = $sut->delete_duplicate_payment_gateway( 'orphan_gateway_2' );

		$this->assertTrue( $result['success'] );
		$this->assertArrayNotHasKey( 'orphan_gateway_2', $settings->get_configs() );
		$this->assertFalse( get_option( 'woocommerce_orphan_gateway_2_settings' ) );
	}

	/**
	 * @covers ::delete_duplicate_payment_gateway
	 */
	public function test_delete_refuses_original_gateway(): void {

		$sut = new API( new Settings(), new Gateway_Duplicability() );

		$result = $sut->delete_duplicate_payment_gateway( 'cheque' );

		$this->assertFalse( $result['success'] );
	}

	/**
	 * @covers ::get_plugin_gateways
	 */
	public function test_get_plugin_gateways_lists_woocommerce_core_gateways(): void {

		$sut = new API( new Settings(), new Gateway_Duplicability() );

		// It only calculates when there are duplicates.
		$sut->add_new_duplicate_payment_gateway( 'duplicable_test_gateway', 'duplicable_test_gateway_2' );

		$result = $sut->get_plugin_gateways( 'woocommerce/woocommerce.php' );

		$this->assertArrayHasKey( 'cheque', $result );
		$this->assertArrayHasKey( 'bacs', $result );
	}

	/**
	 * A duplicate whose original gateway is not loaded (e.g. its plugin was deactivated) caused
	 * "Undefined array key" warnings on every row of plugins.php.
	 *
	 * @covers ::get_plugin_gateways
	 */
	public function test_get_plugin_gateways_with_orphaned_duplicate(): void {

		$settings = new Settings();
		$settings->add_new_duplicate( 'orphan_gateway_2', 'No_Longer_Active_Gateway' );

		$sut = new API( $settings, new Gateway_Duplicability() );

		$warnings = array();
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler
		set_error_handler(
			function ( int $errno, string $errstr ) use ( &$warnings ): bool {
				$warnings[] = $errstr;
				return true;
			},
			E_WARNING | E_NOTICE | E_DEPRECATED
		);

		try {
			$result = $sut->get_plugin_gateways( 'woocommerce/woocommerce.php' );
		} finally {
			restore_error_handler();
		}

		$this->assertEmpty( $warnings, implode( PHP_EOL, $warnings ) );
		$this->assertArrayHasKey( 'cheque', $result );
	}

	/**
	 * The result was cached in a function-level `static`, shared by every instance and never updated, so newly
	 * added duplicates were not reflected.
	 *
	 * @covers ::get_plugin_gateways
	 */
	public function test_get_plugin_gateways_reflects_new_duplicates(): void {

		$settings = new Settings();
		$sut      = new API( $settings, new Gateway_Duplicability() );

		$sut->add_new_duplicate_payment_gateway( 'duplicable_test_gateway', 'duplicable_test_gateway_2' );
		$this->reload_gateways();
		$sut->get_plugin_gateways( 'woocommerce' );

		$sut->add_new_duplicate_payment_gateway( 'duplicable_test_gateway', 'duplicable_test_gateway_3' );
		$this->reload_gateways();

		// The test gateway is not in wp-content/plugins, so derive its "slug" the same way the API does.
		$test_gateway_file = (string) ( new \ReflectionClass( Duplicable_Test_Gateway::class ) )->getFileName();

		$result = $sut->get_plugin_gateways( plugin_basename( $test_gateway_file ) );

		$this->assertEquals(
			array( 'duplicable_test_gateway_2', 'duplicable_test_gateway_3' ),
			$result['duplicable_test_gateway'] ?? null
		);
	}
}
