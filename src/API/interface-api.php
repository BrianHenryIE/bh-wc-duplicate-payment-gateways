<?php

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\API;

interface API_Interface {

	/**
	 * @param string $gateway_id
	 * @param string $duplicate_id
	 * @return array{success: bool, message:string}
	 */
	public function add_new_duplicate_payment_gateway( string $gateway_id, string $duplicate_id ): array;

	/**
	 * @param string $duplicate_id
	 * @return array{success: bool, message:string}
	 */
	public function delete_duplicate_payment_gateway( string $duplicate_id ): array;

}
