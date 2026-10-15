<?php
/**
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Blocks;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\Gateway_Copy_Interface;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Duplicable_Test_Gateway;
use Codeception\TestCase\WPTestCase;
use Exception;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Blocks\Gateway_Integration_Duplicator
 */
class Gateway_Integration_Duplicator_WPUnit_Test extends WPTestCase {

	protected function get_duplicate(): Duplicable_Test_Gateway {
		return new class() extends Duplicable_Test_Gateway implements Gateway_Copy_Interface {
			public function __construct() {
				$this->id = 'duplicable_test_gateway_2';
				parent::__construct();
			}
		};
	}

	/**
	 * @covers ::duplicate
	 * @covers ::is_constructor_gateway_parameter
	 */
	public function test_duplicate(): void {

		$sut = new Gateway_Integration_Duplicator();

		$result = $sut->duplicate(
			new Gateway_Named_Test_Payment_Method_Type( new Duplicable_Test_Gateway() ),
			$this->get_duplicate()
		);

		$this->assertInstanceOf( Gateway_Named_Test_Payment_Method_Type::class, $result );
		$this->assertEquals( 'duplicable_test_gateway_2', $result->get_name() );
	}

	/**
	 * @covers ::duplicate
	 */
	public function test_hardcoded_name(): void {

		$sut = new Gateway_Integration_Duplicator();

		$result = $sut->duplicate(
			new Hardcoded_Name_Test_Payment_Method_Type( new Duplicable_Test_Gateway() ),
			$this->get_duplicate()
		);

		$this->assertNull( $result );
	}

	/**
	 * @covers ::is_constructor_gateway_parameter
	 */
	public function test_constructor_without_parameters(): void {

		$sut = new Gateway_Integration_Duplicator();

		$original = new class() extends AbstractPaymentMethodType {
			/**
			 * @var string
			 */
			protected $name = 'duplicable_test_gateway';

			public function initialize(): void {
			}
		};

		$this->assertNull( $sut->duplicate( $original, $this->get_duplicate() ) );
	}

	/**
	 * @covers ::is_constructor_gateway_parameter
	 */
	public function test_constructor_parameter_untyped(): void {

		$sut = new Gateway_Integration_Duplicator();

		$original = new class( 'duplicable_test_gateway' ) extends AbstractPaymentMethodType {
			/**
			 * @param mixed $name The id.
			 */
			public function __construct( $name ) {
				$this->name = is_string( $name ) ? $name : 'not_a_string';
			}

			public function initialize(): void {
			}
		};

		$this->assertNull( $sut->duplicate( $original, $this->get_duplicate() ) );
	}

	/**
	 * @covers ::is_constructor_gateway_parameter
	 */
	public function test_constructor_parameter_other_class(): void {

		$sut = new Gateway_Integration_Duplicator();

		$original = new class( new \WC_Order() ) extends AbstractPaymentMethodType {
			/**
			 * @param \WC_Order $order Not a gateway.
			 *
			 * @phpstan-ignore constructor.unusedParameter
			 */
			public function __construct( \WC_Order $order ) {
				$this->name = 'duplicable_test_gateway';
			}

			public function initialize(): void {
			}
		};

		$this->assertNull( $sut->duplicate( $original, $this->get_duplicate() ) );
	}

	/**
	 * @covers ::duplicate
	 */
	public function test_constructor_throws(): void {

		$sut = new Gateway_Integration_Duplicator();

		$original = new class( new Duplicable_Test_Gateway() ) extends AbstractPaymentMethodType {
			public function __construct( Duplicable_Test_Gateway $gateway ) {
				if ( 'duplicable_test_gateway' !== $gateway->id ) {
					throw new Exception( 'Only the original.' );
				}
				$this->name = $gateway->id;
			}

			public function initialize(): void {
			}
		};

		$this->assertNull( $sut->duplicate( $original, $this->get_duplicate() ) );
	}
}
