<?php
/**
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\Admin;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\API_Interface;
use lucatume\WPBrowser\TestCase\WPAjaxTestCase;
use WPAjaxDieContinueException;
use WPAjaxDieStopException;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Duplicate_Payment_Gateways\Admin\AJAX
 */
class AJAX_WPUnit_Test extends WPAjaxTestCase {

	public function setUp(): void {
		parent::setUp();

		// WooCommerce's `admin_init` hooks, run by `_handleAjax()`, need its admin functions.
		require_once WC_ABSPATH . 'includes/admin/wc-admin-functions.php';
	}

	/**
	 * Register the handlers with the given API, and return the decoded JSON response.
	 *
	 * @param string        $action The AJAX action.
	 * @param API_Interface $api The (mock) API.
	 * @return array<string, mixed>
	 */
	protected function do_ajax( string $action, API_Interface $api ): array {
		$sut = new AJAX( $api );
		add_action( 'wp_ajax_add_new_duplicate_payment_gateway', array( $sut, 'add_new_duplicate_payment_gateway' ) );
		add_action( 'wp_ajax_delete_duplicate_payment_gateway', array( $sut, 'delete_duplicate_payment_gateway' ) );

		$_POST['_ajax_nonce'] = wp_create_nonce( AJAX::NONCE_ACTION );

		try {
			$this->_handleAjax( $action );
		} catch ( WPAjaxDieContinueException | WPAjaxDieStopException $e ) {
			unset( $e );
		}

		return (array) json_decode( $this->_last_response, true );
	}

	/**
	 * A logged-in user without `manage_woocommerce` (e.g. a customer) could create and delete duplicates.
	 *
	 * @covers ::add_new_duplicate_payment_gateway
	 */
	public function test_add_requires_manage_woocommerce(): void {
		$this->_setRole( 'customer' );

		$_POST['gateway_id']   = 'demo_gateway';
		$_POST['duplicate_id'] = 'demo_gateway_2';

		$api = $this->makeEmpty(
			API_Interface::class,
			array(
				'add_new_duplicate_payment_gateway' => \Codeception\Stub\Expected::never(),
			)
		);

		$response = $this->do_ajax( 'add_new_duplicate_payment_gateway', $api );

		$this->assertFalse( $response['success'] );
	}

	/**
	 * @covers ::delete_duplicate_payment_gateway
	 */
	public function test_delete_requires_manage_woocommerce(): void {
		$this->_setRole( 'customer' );

		$_POST['gateway_id'] = 'demo_gateway_2';

		$api = $this->makeEmpty(
			API_Interface::class,
			array(
				'delete_duplicate_payment_gateway' => \Codeception\Stub\Expected::never(),
			)
		);

		$response = $this->do_ajax( 'delete_duplicate_payment_gateway', $api );

		$this->assertFalse( $response['success'] );
	}

	/**
	 * Missing fields caused "Undefined array key" warnings and `strtolower( null )` deprecations.
	 *
	 * @covers ::add_new_duplicate_payment_gateway
	 */
	public function test_add_missing_fields(): void {
		$this->_setRole( 'administrator' );

		$api = $this->makeEmpty(
			API_Interface::class,
			array(
				'add_new_duplicate_payment_gateway' => \Codeception\Stub\Expected::never(),
			)
		);

		$response = $this->do_ajax( 'add_new_duplicate_payment_gateway', $api );

		$this->assertFalse( $response['success'] );
	}

	/**
	 * The new id is used as part of an option name and in HTML attributes, so restrict it.
	 *
	 * @covers ::add_new_duplicate_payment_gateway
	 */
	public function test_add_rejects_invalid_duplicate_id(): void {
		$this->_setRole( 'administrator' );

		$_POST['gateway_id']   = 'demo_gateway';
		$_POST['duplicate_id'] = 'has spaces & <tags>';

		$api = $this->makeEmpty(
			API_Interface::class,
			array(
				'add_new_duplicate_payment_gateway' => \Codeception\Stub\Expected::never(),
			)
		);

		$response = $this->do_ajax( 'add_new_duplicate_payment_gateway', $api );

		$this->assertFalse( $response['success'] );
	}

	/**
	 * The API's reason for failing should be shown, not just "failed".
	 *
	 * @covers ::add_new_duplicate_payment_gateway
	 */
	public function test_add_returns_api_error_message(): void {
		$this->_setRole( 'administrator' );

		$_POST['gateway_id']   = 'cheque';
		$_POST['duplicate_id'] = 'cheque_2';

		$api = $this->makeEmpty(
			API_Interface::class,
			array(
				'add_new_duplicate_payment_gateway' => array(
					'success' => false,
					'message' => 'The gateway cannot be duplicated.',
				),
			)
		);

		$response = $this->do_ajax( 'add_new_duplicate_payment_gateway', $api );

		$this->assertFalse( $response['success'] );
		$this->assertEquals( 'The gateway cannot be duplicated.', $response['data']['message'] );
	}

	/**
	 * @covers ::add_new_duplicate_payment_gateway
	 */
	public function test_add_success(): void {
		$this->_setRole( 'shop_manager' );

		$_POST['gateway_id']   = 'demo_gateway';
		$_POST['duplicate_id'] = 'demo_gateway_2';

		$api = $this->makeEmpty(
			API_Interface::class,
			array(
				'add_new_duplicate_payment_gateway' => \Codeception\Stub\Expected::once(
					array(
						'success' => true,
						'message' => 'New gateway demo_gateway_2 created.',
					)
				),
			)
		);

		$response = $this->do_ajax( 'add_new_duplicate_payment_gateway', $api );

		$this->assertTrue( $response['success'] );
	}
}
