<?php

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

class CC_Gateway_Parameter_Names_Trait_WPUnit_Test extends \Codeception\TestCase\WPTestCase {

	public function test_trait_changes_ids() {

		$sut = new class( 'expected-id' ) extends \WC_Payment_Gateway_CC  {
			use CC_Gateway_Parameter_Names_Trait;
			public function __construct( string $new_id ) {
				$this->id = $new_id;
				if (is_callable('parent::__construct')) {
					parent::__construct();
				}
				$this->id = $new_id;
			}
		};

		$parent_gateway_id = 'old-gateway-id';

		$_POST["{$parent_gateway_id}-card-cvc"] = '123';
		$_POST["{$parent_gateway_id}-card-number"] = '4111111111111111';
		$_POST["{$parent_gateway_id}-card-expiry"] = '31/24';

		/**
		 * @see \WC_Payment_Gateway::process_payment() returns array().
		 */
		$sut->validate_fields();

		$this->assertArrayHasKey( 'expected-id-card-cvc', $_POST );
		$this->assertArrayHasKey( 'expected-id-card-number', $_POST );
		$this->assertArrayHasKey( 'expected-id-card-expiry', $_POST );

	}

}