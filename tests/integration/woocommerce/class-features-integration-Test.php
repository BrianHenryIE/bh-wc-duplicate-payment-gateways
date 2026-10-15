<?php
/**
 * WooCommerce shows a warning for plugins that have not declared compatibility with its features.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use Codeception\TestCase\WPTestCase;

/**
 * @coversNothing
 */
class Features_Integration_Test extends WPTestCase {

	/**
	 * @return array<string, array{0:string}>
	 */
	public static function features(): array {
		return array(
			'HPOS'                 => array( 'custom_order_tables' ),
			'cart/checkout blocks' => array( 'cart_checkout_blocks' ),
		);
	}

	/**
	 * @dataProvider features
	 *
	 * @param string $feature_id The WooCommerce feature.
	 */
	public function test_declares_compatibility( string $feature_id ): void {

		$plugins = FeaturesUtil::get_compatible_plugins_for_feature( $feature_id );

		$this->assertContains( 'bh-wc-duplicate-payment-gateways/bh-wc-duplicate-payment-gateways.php', $plugins['compatible'] );
	}
}
