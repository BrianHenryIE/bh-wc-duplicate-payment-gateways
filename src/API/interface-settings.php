<?php

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\API;

interface Settings_Interface {

	/**
	 * Array key is the new gateway id.
	 *
	 * @return array<string, array{class_name:string,new_id:string}>
	 */
	public function get_configs(): array;


	public function add_new_duplicate( string $id, string $super_class ): void;


	public function delete_duplicate( string $duplicate_gateway_id ): void;

	/**
	 * @used-by Admin::enqueue_scripts()
	 * @used-by Admin::enqueue_styles()
	 *
	 * @return string Semver
	 */
	public function get_plugin_version(): string;

}
