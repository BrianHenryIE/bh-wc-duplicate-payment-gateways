<?php
/**
 * This code isn't nice because there is no way to extend a variable, so first a class_alias must be created
 * and that can be extended.
 *
 * @see class_alias()
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\Gateway_Copy_Interface;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\Settings_Interface;
use WC_Payment_Gateway;

/**
 * Add the duplicates to WooCommerce's gateways.
 */
class Payment_Gateways {

	const MAX_DUPLICATE_GATEWAYS = 10;

	/**
	 * Needed for the set of gateways to create.
	 *
	 * @see Settings_Interface::get_configs()
	 * @var Settings_Interface
	 */
	protected Settings_Interface $settings;

	/**
	 * Without this cache, when the WooCommerce filter is called repeatedly, the gateways are recreated, spending
	 * the allowance of max. gateways.
	 *
	 * @var array<string, WC_Payment_Gateway>
	 */
	protected array $cached_gateways = array();

	/**
	 * Constructor.
	 *
	 * @param Settings_Interface $settings The plugin settings.
	 */
	public function __construct( Settings_Interface $settings ) {
		$this->settings = $settings;
	}

	/**
	 *
	 * TODO: rename this register gateways?!
	 *
	 * @hooked woocommerce_payment_gateways
	 *
	 * @param array<string|WC_Payment_Gateway> $gateways The existing WooCommerce gateways.
	 * @throws \Exception When too many duplicate gateways are required.
	 * @return array<string|WC_Payment_Gateway>
	 */
	public function add_gateways( array $gateways ): array {
		// phpcs:disable Squiz.Commenting.FunctionComment.Missing

		// Stored keyed by id, but we want an index here, so this is an easy way to achieve that.
		$configs = $this->settings->get_configs();
		$configs = array_values( $configs );

		foreach ( $configs as $index => $config ) {
			$new_id = $config['new_id'];

			if ( isset( $this->cached_gateways[ $new_id ] ) ) {
				$gateways[] = $this->cached_gateways[ $new_id ];
				continue;
			}

			$gateway_to_copy = $config['class_name'];
			// If the class doesn't exist or is not a WC_Payment_Gateway...
			if ( ! class_exists( $gateway_to_copy ) || ! is_a( $gateway_to_copy, WC_Payment_Gateway::class, true ) ) {
				continue;
			}
			// E.g. the gateway plugin was updated since it was duplicated. Declaring the subclass would be a fatal error.
			if ( ! Gateway_Duplicability::is_validate_fields_compatible( $gateway_to_copy ) ) {
				continue;
			}
			$alias = __NAMESPACE__ . '\Gateway_' . $index;
			if ( ! class_exists( $alias, false ) ) {
				class_alias( $gateway_to_copy, $alias );
			} elseif ( strtolower( ( new \ReflectionClass( $alias ) )->getName() ) !== strtolower( ltrim( $gateway_to_copy, '\\' ) ) ) {
				// This alias was already used for a different gateway earlier in this request, and cannot be changed.
				continue;
			}

			switch ( $index ) {
				case 0:
					$new_gateway = new class( $new_id ) extends Gateway_0 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 1:
					$new_gateway = new class( $new_id ) extends Gateway_1 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 2:
					$new_gateway = new class( $new_id ) extends Gateway_2 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 3:
					$new_gateway = new class( $new_id ) extends Gateway_3 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 4:
					$new_gateway = new class( $new_id ) extends Gateway_4 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 5:
					$new_gateway = new class( $new_id ) extends Gateway_5 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 6:
					$new_gateway = new class( $new_id ) extends Gateway_6 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 7:
					$new_gateway = new class( $new_id ) extends Gateway_7 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 8:
					$new_gateway = new class( $new_id ) extends Gateway_8 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 9:
					$new_gateway = new class( $new_id ) extends Gateway_9 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 10:
					$new_gateway = new class( $new_id ) extends Gateway_10 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 11:
					$new_gateway = new class( $new_id ) extends Gateway_11 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 12:
					$new_gateway = new class( $new_id ) extends Gateway_12 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 13:
					$new_gateway = new class( $new_id ) extends Gateway_13 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 14:
					$new_gateway = new class( $new_id ) extends Gateway_14 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 15:
					$new_gateway = new class( $new_id ) extends Gateway_15 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 16:
					$new_gateway = new class( $new_id ) extends Gateway_16 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 17:
					$new_gateway = new class( $new_id ) extends Gateway_17 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 18:
					$new_gateway = new class( $new_id ) extends Gateway_18 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 19:
					$new_gateway = new class( $new_id ) extends Gateway_19 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 20:
					$new_gateway = new class( $new_id ) extends Gateway_20 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 21:
					$new_gateway = new class( $new_id ) extends Gateway_21 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 22:
					$new_gateway = new class( $new_id ) extends Gateway_22 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 23:
					$new_gateway = new class( $new_id ) extends Gateway_23 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 24:
					$new_gateway = new class( $new_id ) extends Gateway_24 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 25:
					$new_gateway = new class( $new_id ) extends Gateway_25 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 26:
					$new_gateway = new class( $new_id ) extends Gateway_26 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 27:
					$new_gateway = new class( $new_id ) extends Gateway_27 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 28:
					$new_gateway = new class( $new_id ) extends Gateway_28 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 29:
					$new_gateway = new class( $new_id ) extends Gateway_29 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				case 30:
					$new_gateway = new class( $new_id ) extends Gateway_30 implements Gateway_Copy_Interface {
						use CC_Gateway_Parameter_Names_Trait;

						public function __construct( string $new_id ) {
							$this->id = $new_id;
							if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
								parent::__construct();
							}
						}
					};
					break;
				default:
					// If you see this, just copy one of the above sections and increment the number.
					throw new \Exception( 'Too many duplicate gateways.' );
			}
			$gateways[]                       = $new_gateway;
			$this->cached_gateways[ $new_id ] = $new_gateway;
		}

		return $gateways;
	}
}
