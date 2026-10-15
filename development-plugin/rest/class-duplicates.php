<?php
/**
 * REST endpoints so E2E tests can arrange duplicates without driving the UI.
 *
 * `POST   /wp-json/bh-wc-duplicate-payment-gateways-development/v1/duplicates` {gateway_id, duplicate_id}
 * `GET    /wp-json/bh-wc-duplicate-payment-gateways-development/v1/duplicates`
 * `DELETE /wp-json/bh-wc-duplicate-payment-gateways-development/v1/duplicates`        Delete all.
 * `DELETE /wp-json/bh-wc-duplicate-payment-gateways-development/v1/duplicates/{id}`   Delete one.
 * `POST   /wp-json/bh-wc-duplicate-payment-gateways-development/v1/duplicates/{id}/settings` {settings: {...}}
 *        Merge into the duplicate's `woocommerce_{id}_settings` option. WooCommerce's REST API only accepts the
 *        gateway's own fields, not the admin title/description fields this plugin adds.
 *
 * Assertions should use WooCommerce's own `/wc/v3/payment_gateways`, which lists duplicates alongside the originals.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\Development_Plugin\Rest;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\Settings;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\API_Interface;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Thin wrapper on the plugin's API class.
 */
class Duplicates {

	const NAMESPACE = 'bh-wc-duplicate-payment-gateways-development/v1';

	/**
	 * Add actions.
	 */
	public function register_hooks(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register the routes.
	 *
	 * @hooked rest_api_init
	 */
	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/duplicates',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_duplicates' ),
					'permission_callback' => array( $this, 'permission_callback' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_duplicate' ),
					'permission_callback' => array( $this, 'permission_callback' ),
					'args'                => array(
						'gateway_id'   => array(
							'type'     => 'string',
							'required' => true,
						),
						'duplicate_id' => array(
							'type'     => 'string',
							'required' => true,
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_all_duplicates' ),
					'permission_callback' => array( $this, 'permission_callback' ),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/duplicates/(?P<id>[\w-]+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_duplicate' ),
				'permission_callback' => array( $this, 'permission_callback' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/duplicates/(?P<id>[\w-]+)/settings',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'update_settings' ),
				'permission_callback' => array( $this, 'permission_callback' ),
				'args'                => array(
					'settings' => array(
						'type'     => 'object',
						'required' => true,
					),
				),
			)
		);
	}

	/**
	 * Merge settings into a duplicate's WooCommerce settings option.
	 *
	 * @param WP_REST_Request $request The request.
	 */
	public function update_settings( WP_REST_Request $request ): WP_REST_Response {
		$id = (string) $request->get_param( 'id' );

		if ( ! isset( ( new Settings() )->get_configs()[ $id ] ) ) {
			return new WP_REST_Response( array( 'message' => 'Not a duplicate: ' . $id ), 404 );
		}

		$option_name = "woocommerce_{$id}_settings";
		$settings    = array_merge( (array) get_option( $option_name, array() ), (array) $request->get_param( 'settings' ) );
		update_option( $option_name, $settings );

		return new WP_REST_Response( $settings );
	}

	/**
	 * Only shop managers and admins.
	 */
	public function permission_callback(): bool {
		return current_user_can( 'manage_woocommerce' );
	}

	/**
	 * The plugin's API instance.
	 */
	protected function get_api(): API_Interface {
		return $GLOBALS['bh_wc_duplicate_payment_gateways'];
	}

	/**
	 * List the saved duplicate configs.
	 */
	public function list_duplicates(): WP_REST_Response {
		return new WP_REST_Response( ( new Settings() )->get_configs() );
	}

	/**
	 * Create a duplicate.
	 *
	 * @param WP_REST_Request $request The request.
	 */
	public function create_duplicate( WP_REST_Request $request ): WP_REST_Response {
		$result = $this->get_api()->add_new_duplicate_payment_gateway(
			(string) $request->get_param( 'gateway_id' ),
			(string) $request->get_param( 'duplicate_id' )
		);
		return new WP_REST_Response( $result, $result['success'] ? 201 : 400 );
	}

	/**
	 * Delete one duplicate.
	 *
	 * @param WP_REST_Request $request The request.
	 */
	public function delete_duplicate( WP_REST_Request $request ): WP_REST_Response {
		$result = $this->get_api()->delete_duplicate_payment_gateway( (string) $request->get_param( 'id' ) );
		return new WP_REST_Response( $result, $result['success'] ? 200 : 400 );
	}

	/**
	 * Delete every duplicate (and its settings) – used to reset between tests.
	 */
	public function delete_all_duplicates(): WP_REST_Response {
		$deleted = array();
		foreach ( array_keys( ( new Settings() )->get_configs() ) as $duplicate_id ) {
			delete_option( "woocommerce_{$duplicate_id}_settings" );
			$deleted[] = $duplicate_id;
		}
		delete_option( Settings::OPTION_NAME );
		return new WP_REST_Response( array( 'deleted' => $deleted ) );
	}
}
