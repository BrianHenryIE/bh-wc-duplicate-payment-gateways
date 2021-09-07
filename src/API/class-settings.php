<?php

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\API;

class Settings implements Settings_Interface {

	const OPTION_NAME = 'bh_wc_duplicate_payment_gateways';

	public function get_configs() :array {

		return get_option( self::OPTION_NAME, array() );
	}

	public function add_new_duplicate( string $id, string $super_class ): void {

		$config = get_option( self::OPTION_NAME, array() );

		$config[ $id ] = array(
			'class_name' => $super_class,
			'new_id'     => $id,
		);

		update_option( self::OPTION_NAME, $config );

	}

	/**
	 *
	 * Remove the config for the specified duplicate id and save. Delete the option if there are no duplicates remaining.
	 *
	 * @param string $id
	 */
	public function delete_duplicate( string $id ): void {
		$config = get_option( self::OPTION_NAME, array() );

		if ( empty( $config ) ) {
			return;
		}

		if ( isset( $config[ $id ] ) ) {

			// TODO: It would be good to log the data before deleting it.

			unset( $config[ $id ] );
			if ( ! empty( $config ) ) {
				update_option( self::OPTION_NAME, $config );
			} else {
				delete_option( self::OPTION_NAME );
			}
		}
	}

	public function get_plugin_version(): string {
		return '1.0.0';
	}
}
