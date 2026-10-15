<?php
/**
 * Duplicate gateways get extra settings: an admin title, an admin description, and "hide on checkout".
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\Settings;
use Codeception\TestCase\WPTestCase;
use WC_Payment_Gateways;

/**
 * @coversNothing
 */
class Payment_Gateway_Settings_Integration_Test extends WPTestCase {

	protected function setUp(): void {
		parent::setUp();

		add_filter(
			'woocommerce_payment_gateways',
			function ( array $gateways ): array {
				$gateways[] = Duplicable_Test_Gateway::class;
				return $gateways;
			},
			5
		);
	}

	public function test_duplicate_has_extra_settings_fields(): void {

		( new Settings() )->add_new_duplicate( 'duplicable_test_gateway_settings', Duplicable_Test_Gateway::class );

		// Reset the singleton so the gateways are loaded with the new duplicate.
		( new \ReflectionProperty( WC_Payment_Gateways::class, '_instance' ) )->setValue( null, null );

		$gateways = WC_Payment_Gateways::instance()->payment_gateways();

		$this->assertArrayHasKey( 'duplicable_test_gateway_settings', $gateways );

		$form_fields = $gateways['duplicable_test_gateway_settings']->get_form_fields();

		$this->assertArrayHasKey( Payment_Gateway_Settings::ADMIN_METHOD_TITLE, $form_fields );
		$this->assertArrayHasKey( Payment_Gateway_Settings::ADMIN_DESCRIPTION, $form_fields );
		$this->assertArrayHasKey( Payment_Gateway_Settings::HIDE_ON_CHECKOUT, $form_fields );
	}

	public function test_original_does_not_have_extra_settings_fields(): void {

		( new \ReflectionProperty( WC_Payment_Gateways::class, '_instance' ) )->setValue( null, null );

		$gateways = WC_Payment_Gateways::instance()->payment_gateways();

		$this->assertArrayNotHasKey( Payment_Gateway_Settings::HIDE_ON_CHECKOUT, $gateways['duplicable_test_gateway']->get_form_fields() );
	}
}
