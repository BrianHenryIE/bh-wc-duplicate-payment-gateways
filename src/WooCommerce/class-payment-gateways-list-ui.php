<?php
/**
 * Add a column to WooCommerce / Settings / Payments list of gateways, to allow duplicating, and deleting duplicates.
 *
 * @package     brianhenryie/bh-wc-duplicate-payment-gateways
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\Gateway_Copy_Interface;
use WC_Payment_Gateway;

/**
 * Register the column; print the column.
 *
 * @see WC_Settings_Payment_Gateways::payment_gateways_setting()
 */
class Payment_Gateways_List_UI {

	/**
	 * Add a 'duplicate' column after the 'sort' column.
	 *
	 * @hooked woocommerce_payment_gateways_setting_columns
	 *
	 * @param array<string, string> $columns Column id: column title.
	 * @return array<string, string>
	 */
	public function add_column( array $columns ): array {

		$new_columns = array();

		foreach ( $columns as $id => $title ) {
			$new_columns[ $id ] = $title;
			if ( 'action' === $id ) {
				$new_columns['duplicate'] = '';
			}
		}

		return $new_columns;
	}

	/**
	 * If the gateway is a duplicate, add a delete button.
	 * if it is an original, print a duplicate button.
	 *
	 * @hooked woocommerce_payment_gateways_setting_column_duplicate
	 *
	 * @param WC_Payment_Gateway $gateway The gateway object for the current row.
	 */
	public function print_column_for_duplicate_ui( WC_Payment_Gateway $gateway ): void {

		// TODO: Should all shop admins be allowed do this?
		// Add a "duplicate-gateways" role and if empty, allow shop managers, if not empty, restrict.

		$key   = 'duplicate';
		$width = '2%';

		echo '<td class="' . esc_attr( $key ) . '" width="' . esc_attr( $width ) . '">';

		$method_title = wp_kses( $gateway->get_method_title(), array() );

		if ( $gateway instanceof Gateway_Copy_Interface ) {
			// Print a delete button.
			/* translators: %s: The payment gateway ID. */
			echo '<span class="duplicate-payment-gateway duplicate-delete dashicons dashicons-trash" data-gateway_id="' . esc_attr( $gateway->id ) . '" title="' . esc_attr( sprintf( __( 'Delete the duplicate "%s" payment method.', 'woocommerce' ), $method_title ) ) . '"></span>';
		} else {
			// Maybe print an add button.

			if ( $this->is_safe_to_duplicate( $gateway ) ) {
				/* translators: %s: The payment gateway ID. */
				echo '<span class="duplicate-payment-gateway duplicate-add dashicons dashicons-admin-page" data-gateway_id="' . esc_attr( $gateway->id ) . '" title="' . esc_attr( sprintf( __( 'Duplicate the "%s" payment method.', 'woocommerce' ), $method_title ) ) . '"></span>';
			}
		}

		echo '</td>';
	}

	/**
	 * Runs a test duplication to see if the duplicate id is overwritten during construction.
	 *
	 * If it is, then there will be problems and the gateway should not be duplicated.
	 *
	 * @param WC_Payment_Gateway $gateway_to_copy A gateway we might want to duplicate.
	 *
	 * @return bool
	 *
	 * phpcs:disable Squiz.Commenting.FunctionComment.Missing
	 */
	protected function is_safe_to_duplicate( WC_Payment_Gateway $gateway_to_copy ) : bool {

		static $count = 0;

		$duplicate_id = 'duplicate-id';

		class_alias( get_class( $gateway_to_copy ), __NAMESPACE__ . '\Attempt_Duplicate_Gateway_' . $count );

		switch ( $count ) {
			case 0:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_0 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 1:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_1 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 2:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_2 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 3:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_3 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						parent::__construct();
					}
				};
				break;
			case 4:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_4 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 5:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_5 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 6:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_6 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 7:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_7 {
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 8:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_8 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 9:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_9 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
						$this->id = $duplicate_id;
					}
				};
				break;
			case 10:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_10 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
						$this->id = $duplicate_id;
					}
				};
				break;
			case 11:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_11 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 12:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_12 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 13:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_13 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						parent::__construct();
					}
				};
				break;
			case 14:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_14 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 15:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_15 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 16:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_16 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 17:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_17 {
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 18:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_18 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 19:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_19 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
						$this->id = $duplicate_id;
					}
				};
				break;
			case 20:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_20 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
						$this->id = $duplicate_id;
					}
				};
				break;
			case 21:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_21 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 22:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_22 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 23:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_23 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						parent::__construct();
					}
				};
				break;
			case 24:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_24 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 25:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_25 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 26:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_26 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 27:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_27 {
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 28:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_28 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 29:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_29 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
						$this->id = $duplicate_id;
					}
				};
				break;
			case 30:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_30 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( is_callable( 'parent::__construct' ) ) {
							parent::__construct();
						}
						$this->id = $duplicate_id;
					}
				};
				break;
			default:
				// If you see this, just copy one of the above sections and increment the number.
				throw new \Exception( 'Too many duplicate gateways.' );
		}

		$count++;

		return $duplicate_gateway->id === $duplicate_id;
	}
}
