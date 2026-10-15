<?php
/**
 * WooCommerce / Duplicate Gateways admin page: list every payment gateway, with buttons to duplicate those that can
 * be duplicated, and to delete duplicates.
 *
 * Since WooCommerce 9.x, WooCommerce / Settings / Payments is a React app with no extension points for adding
 * buttons to each gateway, and the `woocommerce_payment_gateways_setting_columns` table it replaced is gone.
 *
 * `admin.php?page=bh-wc-duplicate-payment-gateways`
 * `admin.php?page=bh-wc-duplicate-payment-gateways&plugin={plugin-slug}` – only that plugin's gateways (linked from plugins.php).
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\Admin;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\Gateway_Copy_Interface;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\API_Interface;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Gateway_Duplicability;
use WC_Payment_Gateway;

/**
 * Register the submenu page, print it, and link to it from WooCommerce / Settings / Payments.
 */
class Duplicate_Gateways_Page {

	const PAGE_SLUG = 'bh-wc-duplicate-payment-gateways';

	/**
	 * Used to filter the list by plugin.
	 *
	 * @var API_Interface
	 */
	protected API_Interface $api;

	/**
	 * Used to decide whether to show the duplicate button for each gateway.
	 *
	 * @var Gateway_Duplicability
	 */
	protected Gateway_Duplicability $gateway_duplicability;

	/**
	 * Constructor.
	 *
	 * @param API_Interface         $api The main plugin functions.
	 * @param Gateway_Duplicability $gateway_duplicability Checks can a gateway be duplicated.
	 */
	public function __construct( API_Interface $api, Gateway_Duplicability $gateway_duplicability ) {
		$this->api                   = $api;
		$this->gateway_duplicability = $gateway_duplicability;
	}

	/**
	 * The URL of this page, optionally filtered to one plugin's gateways.
	 *
	 * @param ?string $plugin_slug The plugin directory name, e.g. `woocommerce`.
	 */
	public static function get_url( ?string $plugin_slug = null ): string {
		$args = array( 'page' => self::PAGE_SLUG );
		if ( ! empty( $plugin_slug ) ) {
			$args['plugin'] = $plugin_slug;
		}
		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}

	/**
	 * Add the page under the WooCommerce menu.
	 *
	 * @hooked admin_menu
	 * @see \BrianHenryIE\WC_Duplicate_Payment_Gateways\BH_WC_Duplicate_Payment_Gateways::define_duplicate_gateways_page_hooks()
	 */
	public function add_submenu_page(): void {
		add_submenu_page(
			'woocommerce',
			__( 'Duplicate Payment Gateways', 'bh-wc-duplicate-payment-gateways' ),
			__( 'Duplicate Gateways', 'bh-wc-duplicate-payment-gateways' ),
			'manage_woocommerce',
			self::PAGE_SLUG,
			array( $this, 'print_page' )
		);
	}

	/**
	 * Print the list of gateways.
	 *
	 * @see self::add_submenu_page()
	 */
	public function print_page(): void {

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Only used to filter the display.
		$plugin_slug = isset( $_GET['plugin'] ) ? sanitize_key( wp_unslash( $_GET['plugin'] ) ) : '';
		$added_id    = isset( $_GET['added'] ) ? sanitize_text_field( wp_unslash( $_GET['added'] ) ) : '';
		$deleted_id  = isset( $_GET['deleted'] ) ? sanitize_text_field( wp_unslash( $_GET['deleted'] ) ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		/**
		 * All payment gateways registered with WooCommerce.
		 *
		 * @var array<string, WC_Payment_Gateway> $gateways
		 */
		$gateways = WC()->payment_gateways()->payment_gateways();

		// Map each original gateway's class to its id, to find what each duplicate is a duplicate of.
		$class_names_to_ids = array();
		foreach ( $gateways as $gateway ) {
			if ( ! ( $gateway instanceof Gateway_Copy_Interface ) ) {
				$class_names_to_ids[ strtolower( get_class( $gateway ) ) ] = $gateway->id;
			}
		}

		if ( ! empty( $plugin_slug ) ) {
			$visible_ids = array();
			foreach ( $this->api->get_plugin_gateways( $plugin_slug ) as $gateway_id => $duplicate_ids ) {
				$visible_ids[] = $gateway_id;
				$visible_ids   = array_merge( $visible_ids, $duplicate_ids );
			}
			$gateways = array_filter(
				$gateways,
				fn( WC_Payment_Gateway $gateway ): bool => in_array( $gateway->id, $visible_ids, true )
			);
		}

		echo '<div class="wrap bh-wc-duplicate-payment-gateways">';
		echo '<h1>' . esc_html__( 'Duplicate Payment Gateways', 'bh-wc-duplicate-payment-gateways' ) . '</h1>';

		echo '<p>' . esc_html__( 'Create additional instances of a payment method, each with its own settings, title and description, e.g. to track orders from different sources separately.', 'bh-wc-duplicate-payment-gateways' ) . '</p>';
		echo '<p>' . esc_html__( 'Payment methods whose code sets their own id, or needs constructor arguments (including WooCommerce\'s bank transfer, check and cash on delivery methods), cannot be duplicated.', 'bh-wc-duplicate-payment-gateways' ) . '</p>';

		if ( ! empty( $added_id ) ) {
			printf(
				'<div class="notice notice-success"><p>%s <a href="%s">%s</a></p></div>',
				/* translators: %s: The new payment gateway id. */
				esc_html( sprintf( __( 'Created payment method "%s".', 'bh-wc-duplicate-payment-gateways' ), $added_id ) ),
				esc_url( $this->get_gateway_settings_url( $added_id ) ),
				esc_html__( 'Configure it', 'bh-wc-duplicate-payment-gateways' )
			);
		}

		if ( ! empty( $deleted_id ) ) {
			printf(
				'<div class="notice notice-success"><p>%s</p></div>',
				/* translators: %s: The deleted payment gateway id. */
				esc_html( sprintf( __( 'Deleted payment method "%s".', 'bh-wc-duplicate-payment-gateways' ), $deleted_id ) )
			);
		}

		echo '<div class="bh-wc-duplicate-payment-gateways-error notice notice-error hidden"><p></p></div>';

		if ( ! empty( $plugin_slug ) ) {
			printf(
				'<p class="bh-wc-duplicate-payment-gateways-filter">%s <a href="%s">%s</a></p>',
				/* translators: %s: The plugin slug. */
				esc_html( sprintf( __( 'Showing payment methods from the "%s" plugin.', 'bh-wc-duplicate-payment-gateways' ), $plugin_slug ) ),
				esc_url( self::get_url() ),
				esc_html__( 'Show all', 'bh-wc-duplicate-payment-gateways' )
			);
		}

		echo '<table class="wp-list-table widefat fixed striped">';
		echo '<thead><tr>';
		echo '<th scope="col">' . esc_html__( 'Payment method', 'bh-wc-duplicate-payment-gateways' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'ID', 'bh-wc-duplicate-payment-gateways' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Enabled', 'bh-wc-duplicate-payment-gateways' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Duplicate of', 'bh-wc-duplicate-payment-gateways' ) . '</th>';
		echo '<th scope="col" style="width:40%">' . esc_html__( 'Actions', 'bh-wc-duplicate-payment-gateways' ) . '</th>';
		echo '</tr></thead><tbody>';

		foreach ( $gateways as $gateway ) {
			$this->print_row( $gateway, $class_names_to_ids );
		}

		echo '</tbody></table>';
		echo '</div>';
	}

	/**
	 * Print one gateway's table row.
	 *
	 * @param WC_Payment_Gateway    $gateway The gateway.
	 * @param array<string, string> $class_names_to_ids Lowercase class name: id, of the original gateways.
	 */
	protected function print_row( WC_Payment_Gateway $gateway, array $class_names_to_ids ): void {

		$is_duplicate = $gateway instanceof Gateway_Copy_Interface;

		printf( '<tr data-gateway_id="%s">', esc_attr( $gateway->id ) );

		printf(
			'<td class="column-title"><a href="%s">%s</a></td>',
			esc_url( $this->get_gateway_settings_url( $gateway->id ) ),
			esc_html( wp_strip_all_tags( $gateway->get_method_title() ) )
		);

		printf( '<td class="column-id"><code>%s</code></td>', esc_html( $gateway->id ) );

		printf(
			'<td class="column-enabled">%s</td>',
			wc_string_to_bool( $gateway->enabled )
				? esc_html__( 'Yes', 'bh-wc-duplicate-payment-gateways' )
				: esc_html__( 'No', 'bh-wc-duplicate-payment-gateways' )
		);

		$parent_id = '';
		if ( $is_duplicate ) {
			$parent_class = get_parent_class( $gateway );
			$parent_id    = false !== $parent_class ? ( $class_names_to_ids[ strtolower( $parent_class ) ] ?? '' ) : '';
		}
		printf( '<td class="column-duplicate-of">%s</td>', empty( $parent_id ) ? '' : '<code>' . esc_html( $parent_id ) . '</code>' );

		echo '<td class="column-actions">';

		printf(
			'<a class="button" href="%s">%s</a> ',
			esc_url( $this->get_gateway_settings_url( $gateway->id ) ),
			esc_html__( 'Manage', 'bh-wc-duplicate-payment-gateways' )
		);

		if ( $is_duplicate ) {
			printf(
				'<button type="button" class="button button-link-delete duplicate-delete" data-gateway_id="%s">%s</button>',
				esc_attr( $gateway->id ),
				esc_html__( 'Delete', 'bh-wc-duplicate-payment-gateways' )
			);
		} elseif ( $this->gateway_duplicability->is_safe_to_duplicate( $gateway ) ) {
			printf(
				'<form class="duplicate-add-form" data-gateway_id="%1$s">
					<label class="screen-reader-text" for="duplicate-id-%1$s">%2$s</label>
					<input type="text" id="duplicate-id-%1$s" name="duplicate_id" class="regular-text" style="width:auto" required pattern="[A-Za-z0-9_\-]+" placeholder="%3$s" value="%3$s" />
					<button type="submit" class="button button-primary duplicate-add">%4$s</button>
				</form>',
				esc_attr( $gateway->id ),
				/* translators: %s: The payment gateway id. */
				esc_html( sprintf( __( 'New id for the duplicate of %s', 'bh-wc-duplicate-payment-gateways' ), $gateway->id ) ),
				esc_attr( $this->suggest_duplicate_id( $gateway->id ) ),
				esc_html__( 'Duplicate', 'bh-wc-duplicate-payment-gateways' )
			);
		} else {
			printf(
				'<span class="cannot-duplicate description">%s</span>',
				esc_html__( 'Cannot be duplicated', 'bh-wc-duplicate-payment-gateways' )
			);
		}

		echo '</td></tr>';
	}

	/**
	 * The first unused `{id}_n`.
	 *
	 * @param string $gateway_id The gateway being duplicated.
	 */
	protected function suggest_duplicate_id( string $gateway_id ): string {
		$existing_ids = array_keys( WC()->payment_gateways()->payment_gateways() );
		$index        = 2;
		while ( in_array( "{$gateway_id}_{$index}", $existing_ids, true ) ) {
			++$index;
		}
		return "{$gateway_id}_{$index}";
	}

	/**
	 * WooCommerce / Settings / Payments / {gateway}.
	 *
	 * @param string $gateway_id The gateway id.
	 */
	protected function get_gateway_settings_url( string $gateway_id ): string {
		return admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . strtolower( $gateway_id ) );
	}

	/**
	 * Print a link to this page above WooCommerce / Settings / Payments, and on each gateway's settings page.
	 *
	 * @hooked woocommerce_settings_checkout
	 * @see \WC_Settings_Payment_Gateways::output() Hooked at priority 10.
	 */
	public function print_link_on_payments_settings(): void {
		global $current_section;

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$section = is_string( $current_section ) ? $current_section : '';

		$gateways = WC()->payment_gateways()->payment_gateways();

		$gateway = null;
		foreach ( $gateways as $registered_gateway ) {
			if ( strtolower( $registered_gateway->id ) === $section ) {
				$gateway = $registered_gateway;
				break;
			}
		}

		if ( '' === $section || 'main' === $section ) {
			$message = __( 'Need more than one instance of a payment method?', 'bh-wc-duplicate-payment-gateways' );
			$link    = __( 'Duplicate payment gateways', 'bh-wc-duplicate-payment-gateways' );
		} elseif ( $gateway instanceof Gateway_Copy_Interface ) {
			$message = __( 'This payment method is a duplicate.', 'bh-wc-duplicate-payment-gateways' );
			$link    = __( 'Manage duplicate payment gateways', 'bh-wc-duplicate-payment-gateways' );
		} elseif ( $gateway instanceof WC_Payment_Gateway && $this->gateway_duplicability->is_safe_to_duplicate( $gateway ) ) {
			$message = __( 'This payment method can be duplicated.', 'bh-wc-duplicate-payment-gateways' );
			$link    = __( 'Duplicate payment gateways', 'bh-wc-duplicate-payment-gateways' );
		} else {
			return;
		}

		printf(
			'<p class="bh-wc-duplicate-payment-gateways-link">%s <a href="%s">%s</a></p>',
			esc_html( $message ),
			esc_url( self::get_url() ),
			esc_html( $link )
		);
	}
}
