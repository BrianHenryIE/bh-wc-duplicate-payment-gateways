<?php

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

use BrianHenryIE\ColorLogger\ColorLogger;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\API_Interface;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\Settings_Interface;
use Codeception\TestCase\WPTestCase;
use WC_Gateway_BACS;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Payment_Gateways_List_UI
 */
class Payment_Gateways_List_UI_WPUnit_Test extends WPTestCase {


	/**
	 * @covers ::filter_to_only_bitcoin_gateways
	 */
	public function test_filter_to_only_bitcoin_gateways(): void {

		$logger   = new ColorLogger();
		$settings = $this->makeEmpty( Settings_Interface::class );
		$api      = $this->makeEmpty( API_Interface::class );

		$sut = new Payment_Gateways_List_UI( $api, $settings, $logger );

		$GLOBALS['bh_wc_bitcoin_gateway'] = $this->makeEmpty( API_Interface::class );

		$gateways = array(
			new WC_Gateway_BACS(),
			new Bitcoin_Gateway(),
			new class() extends Bitcoin_Gateway {
				/**
				 * Unique id for second instance.
				 *
				 * @var string
				 */
				public $id = 'bitcoin_gateway_2';
			},
			'nothing',
		);

		// Pretend we're on the gateways list page, with parameter `class=bh-wc-bitcoin-gateway`.
		$GLOBALS['current_tab'] = 'checkout';
		$_GET['class']          = 'bh-wc-bitcoin-gateway';

		$result = $sut->filter_to_only_bitcoin_gateways( $gateways );

		$this->assertCount( 2, $result );
	}

	/**
	 * @covers ::filter_to_only_bitcoin_gateways
	 */
	public function test_filter_to_only_bitcoin_gateways_wrong_page(): void {

		$logger   = new ColorLogger();
		$settings = $this->makeEmpty( Settings_Interface::class );
		$api      = $this->makeEmpty( API_Interface::class );

		$sut = new Payment_Gateways_List_UI( $api, $settings, $logger );

		$gateways = array(
			new WC_Gateway_BACS(),
			new Bitcoin_Gateway(),
		);

		$result = $sut->filter_to_only_bitcoin_gateways( $gateways );

		$this->assertCount( 2, $result );
	}
}