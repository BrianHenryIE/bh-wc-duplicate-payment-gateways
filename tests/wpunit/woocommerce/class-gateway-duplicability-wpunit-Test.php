<?php
/**
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

use Codeception\TestCase\WPTestCase;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Gateway_Duplicability
 */
class Gateway_Duplicability_WPUnit_Test extends WPTestCase {

	/**
	 * Gateways which declare `validate_fields(): bool`, e.g. Venmo Gateway Pro, caused a fatal error when the
	 * Duplicate Gateways page tested whether they could be duplicated.
	 *
	 * @covers ::is_safe_to_duplicate
	 * @covers ::attempt_duplicate
	 */
	public function test_typed_validate_fields_is_safe_to_duplicate(): void {

		$sut = new Gateway_Duplicability();

		$this->assertTrue( $sut->is_safe_to_duplicate( new Typed_Validate_Fields_Test_Gateway() ) );
	}

	/**
	 * @covers ::is_safe_to_duplicate
	 * @covers ::attempt_duplicate
	 */
	public function test_void_validate_fields_is_not_safe_to_duplicate(): void {

		$sut = new Gateway_Duplicability();

		$this->assertFalse( $sut->is_safe_to_duplicate( new Void_Validate_Fields_Test_Gateway() ) );
	}

	/**
	 * @return array<string, array{class-string<\WC_Payment_Gateway>, bool}>
	 */
	public static function validate_fields_signatures(): array {
		return array(
			'untyped'           => array( Duplicable_Test_Gateway::class, true ),
			'bool'              => array( Typed_Validate_Fields_Test_Gateway::class, true ),
			'nullable bool'     => array( Nullable_Validate_Fields_Test_Gateway::class, true ),
			'untyped no return' => array( Null_Returning_Validate_Fields_Test_Gateway::class, true ),
			'void'              => array( Void_Validate_Fields_Test_Gateway::class, false ),
			'final'             => array( Final_Validate_Fields_Test_Gateway::class, false ),
			'parameter'         => array( Parameter_Validate_Fields_Test_Gateway::class, false ),
		);
	}

	/**
	 * @dataProvider validate_fields_signatures
	 * @covers ::is_validate_fields_compatible
	 *
	 * @param string $class_name The gateway class.
	 * @param bool   $expected Can `CC_Gateway_Parameter_Names_Trait::validate_fields()` override it.
	 */
	public function test_is_validate_fields_compatible( string $class_name, bool $expected ): void {

		$this->assertSame( $expected, Gateway_Duplicability::is_validate_fields_compatible( $class_name ) );
	}
}
