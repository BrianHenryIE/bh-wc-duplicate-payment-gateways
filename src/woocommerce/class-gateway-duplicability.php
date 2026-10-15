<?php
/**
 * Determine can a gateway be duplicated.
 *
 * A gateway cannot be duplicated when its constructor requires arguments, or when its constructor sets
 * `$this->id` unconditionally (as WooCommerce's own BACS, cheque and COD gateways do), since the duplicate's
 * id would be overwritten and it would replace the original.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\Gateway_Copy_Interface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use WC_Payment_Gateway;

/**
 * Runs a test duplication.
 */
class Gateway_Duplicability {

	/**
	 * Results already calculated in this request, keyed by gateway class name.
	 *
	 * @var array<string, bool>
	 */
	protected static array $results = array();

	/**
	 * Runs a test duplication to see if the duplicate id is overwritten during construction.
	 *
	 * If it is, then there will be problems and the gateway should not be duplicated.
	 *
	 * The result is cached per class, since each attempt uses one of a limited number of class aliases.
	 *
	 * @param WC_Payment_Gateway $gateway_to_copy A gateway we might want to duplicate.
	 */
	public function is_safe_to_duplicate( WC_Payment_Gateway $gateway_to_copy ): bool {

		$class_name = get_class( $gateway_to_copy );

		if ( ! isset( self::$results[ $class_name ] ) ) {
			self::$results[ $class_name ] = $this->attempt_duplicate( $gateway_to_copy );
		}

		return self::$results[ $class_name ];
	}

	/**
	 * Can `CC_Gateway_Parameter_Names_Trait::validate_fields(): bool` override the gateway's `validate_fields()`.
	 *
	 * An incompatible declaration is a compile error, which cannot be caught, so it must be checked before the
	 * duplicate's class is declared. Compatible: no return type, `bool`, `?bool`, `mixed`, or a union including `bool`.
	 * Incompatible: any other return type (e.g. `void`), `final`, or any parameters.
	 *
	 * @see CC_Gateway_Parameter_Names_Trait::validate_fields()
	 * @used-by Payment_Gateways::add_gateways()
	 *
	 * @param string $class_name The gateway class to be duplicated.
	 */
	public static function is_validate_fields_compatible( string $class_name ): bool {

		$method = new ReflectionMethod( $class_name, 'validate_fields' );

		if ( $method->isFinal() || 0 < $method->getNumberOfParameters() ) {
			return false;
		}

		$return_type = $method->getReturnType();

		if ( is_null( $return_type ) ) {
			return true;
		}

		if ( $return_type instanceof ReflectionNamedType ) {
			$types = array( $return_type );
		} elseif ( $return_type instanceof \ReflectionUnionType ) {
			$types = $return_type->getTypes();
		} else {
			return false;
		}

		foreach ( $types as $type ) {
			if ( $type instanceof ReflectionNamedType && in_array( $type->getName(), array( 'bool', 'mixed' ), true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Create a subclass of the gateway with a new id and check the id is unchanged after the parent constructor runs.
	 *
	 * @param WC_Payment_Gateway $gateway_to_copy A gateway we might want to duplicate.
	 *
	 * @throws \Exception When too many duplicate gateways are needed.
	 *
	 * phpcs:disable Squiz.Commenting.FunctionComment.Missing
	 */
	protected function attempt_duplicate( WC_Payment_Gateway $gateway_to_copy ): bool {

		// We won't duplicate duplicates.
		if ( $gateway_to_copy instanceof Gateway_Copy_Interface ) {
			return false;
		}

		// Declaring the subclass would be a fatal error.
		if ( ! self::is_validate_fields_compatible( get_class( $gateway_to_copy ) ) ) {
			return false;
		}

		// We cannot duplicate gateways that have constructor arguments (i.e. the WooCommerce-Payments plugin).
		$class       = new ReflectionClass( get_class( $gateway_to_copy ) );
		$constructor = $class->getConstructor();
		if ( is_null( $constructor ) ) {
			// Something went wrong.
			return false;
		}
		$constructor_parameter_count = $constructor->getNumberOfParameters();

		if ( 0 < $constructor_parameter_count ) {
			$parameters = $constructor->getParameters();
			foreach ( $parameters as $reflection_parameter ) {
				if ( ! $reflection_parameter->isDefaultValueAvailable() ) {
					return false;
				}
			}
		}

		static $count = 0;

		$duplicate_id = 'duplicate-id';

		class_alias( get_class( $gateway_to_copy ), __NAMESPACE__ . '\Attempt_Duplicate_Gateway_' . $count );

		switch ( $count ) {
			case 0:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_0 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;

					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
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
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
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
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
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
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 4:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_4 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;

					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
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
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
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
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 7:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_7 {
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
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
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
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
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
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
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
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
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
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
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
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
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 14:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_14 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;

					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
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
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
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
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 17:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_17 {
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
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
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
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
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
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
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
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
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
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
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
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
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 24:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_24 implements Gateway_Copy_Interface {
					use CC_Gateway_Parameter_Names_Trait;

					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
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
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
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
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
							parent::__construct();
						}
					}
				};
				break;
			case 27:
				$duplicate_gateway = new class( $duplicate_id ) extends Attempt_Duplicate_Gateway_27 {
					public function __construct( string $duplicate_id ) {
						$this->id = $duplicate_id;
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
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
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
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
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
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
						if ( method_exists( (string) get_parent_class( $this ), '__construct' ) ) {
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

		++$count;

		// If the id has been correctly set, the gateway can be duplicated.
		return $duplicate_gateway->id === $duplicate_id;
	}
}
