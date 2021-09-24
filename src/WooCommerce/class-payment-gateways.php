<?php
/**
 * This code isn't nice because there is no way to extend a variable, so first a class_alias must be created
 * and that can be extended.
 *
 * @see class_alias()
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\Gateway_Copy_Interface;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\Settings_Interface;
use WC_Payment_Gateway;

/**
 * phpcs:disable Squiz.Commenting.FunctionComment.Missing
 */
class Payment_Gateways {

	const MAX_DUPLICATE_GATEWAYS = 10;

	protected Settings_Interface $settings;

	public function __construct( Settings_Interface $settings ) {
		$this->settings = $settings;
	}

	/**
	 * @hooked woocommerce_payment_gateways
	 *
	 * @param array<string|WC_Payment_Gateway> $gateways The existing WooCommerce gateways.
	 */
	public function add_gateways( array $gateways ) {

		// Stored keyed by id, but we want an index here, so this is an easy way to achieve that.
		$configs = $this->settings->get_configs();
		$configs = array_values( $configs );

		foreach ( $configs as $index => $config ) {

			$gateway_to_copy = $config['class_name'];
			// If the class doesn't exist or is not a WC_Payment_Gateway...
			if ( ! class_exists( $gateway_to_copy ) || ! is_a( $gateway_to_copy, WC_Payment_Gateway::class, true ) ) {
				continue;
			}
			if ( class_exists( __NAMESPACE__ . '\Gateway_' . $index ) ) {
				continue;
			}
			class_alias( $gateway_to_copy, __NAMESPACE__ . '\Gateway_' . $index );
			$new_id = $config['new_id'];

			switch ( $index ) {
				case 0:
					$gateways[] = new class( $new_id ) extends Gateway_0 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;
						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( is_callable( 'parent::__construct' ) ) {
								parent::__construct();
							}
							$this->id = $new_id;
						}
					};
					break;
				case 1:
					$gateways[] = new class( $new_id ) extends Gateway_1 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;
						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( is_callable( 'parent::__construct' ) ) {
								parent::__construct();
							}
							$this->id = $new_id;
						}
					};
					break;
				case 2:
					$gateways[] = new class( $new_id ) extends Gateway_2 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;
						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( is_callable( 'parent::__construct' ) ) {
								parent::__construct();
							}
							$this->id = $new_id;
						}
					};
					break;
				case 3:
					$gateways[] = new class( $new_id ) extends Gateway_3 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;
						public function __construct( string $new_id ) {
							$this->id = $new_id;
							parent::__construct();
							$this->id = $new_id;
						}
					};
					break;
				case 4:
					$gateways[] = new class( $new_id ) extends Gateway_4 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;
						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( is_callable( 'parent::__construct' ) ) {
								parent::__construct();
							}
							$this->id = $new_id;
						}
					};
					break;
				case 5:
					$gateways[] = new class( $new_id ) extends Gateway_5 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;
						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( is_callable( 'parent::__construct' ) ) {
								parent::__construct();
							}
							$this->id = $new_id;
						}
					};
					break;
				case 6:
					$gateways[] = new class( $new_id ) extends Gateway_6 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;
						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( is_callable( 'parent::__construct' ) ) {
								parent::__construct();
							}
							$this->id = $new_id;
						}
					};
					break;
				case 7:
					$gateways[] = new class( $new_id ) extends Gateway_7 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;
						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( is_callable( 'parent::__construct' ) ) {
								parent::__construct();
							}
							$this->id = $new_id;
						}
					};
					break;
				case 8:
					$gateways[] = new class( $new_id ) extends Gateway_8 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;
						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( is_callable( 'parent::__construct' ) ) {
								parent::__construct();
							}
							$this->id = $new_id;
						}
					};
					break;
				case 9:
					$gateways[] = new class( $new_id ) extends Gateway_8 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;
						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( is_callable( 'parent::__construct' ) ) {
								parent::__construct();
							}
							$this->id = $new_id;
						}
					};
					break;
				case 10:
					$gateways[] = new class( $new_id ) extends Gateway_8 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;
						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( is_callable( 'parent::__construct' ) ) {
								parent::__construct();
							}
							$this->id = $new_id;
						}
					};
					break;
				default:
					// If you see this, just copy one of the above sections and increment the number.
					throw new \Exception( 'Too many duplicate gateways.' );
			}
		}

		return $gateways;
	}



	// Hide gateways?


}
