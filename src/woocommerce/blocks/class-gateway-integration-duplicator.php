<?php
/**
 * Create a gateway plugin's own block checkout integration for a duplicate of its gateway.
 *
 * Gateway plugins register their integration for their gateway's id only. When the integration's constructor takes
 * the gateway, and it sets its name from that gateway's id, a new instance of it constructed with the duplicate is
 * the duplicate's integration – with the gateway's own payment fields, if the plugin's script registers each
 * payment method it is given data for, rather than a hardcoded name.
 *
 * E.g.
 * ```
 * public function __construct( My_Gateway $gateway ) {
 *     $this->gateway = $gateway;
 *     $this->name    = $gateway->id;
 * }
 * ```
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Blocks;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;
use ReflectionClass;
use ReflectionNamedType;
use Throwable;
use WC_Payment_Gateway;

/**
 * `new $original_integration_class( $duplicate )`, when that is safe and sets the duplicate's id as its name.
 */
class Gateway_Integration_Duplicator {

	/**
	 * Create a new instance of the original gateway's integration for the duplicate.
	 *
	 * @param IntegrationInterface $original_integration The integration registered for the original gateway.
	 * @param WC_Payment_Gateway   $duplicate The duplicate gateway.
	 *
	 * @return ?IntegrationInterface Null when the integration's constructor does not take the gateway, or its name is
	 *                               not the duplicate's id.
	 */
	public function duplicate( IntegrationInterface $original_integration, WC_Payment_Gateway $duplicate ): ?IntegrationInterface {

		$integration_class = new ReflectionClass( $original_integration );

		if ( ! $this->is_constructor_gateway_parameter( $integration_class, $duplicate ) ) {
			return null;
		}

		try {
			$integration = $integration_class->newInstance( $duplicate );
		} catch ( Throwable $exception ) {
			return null;
		}

		if ( $integration->get_name() !== $duplicate->id ) {
			return null;
		}

		return $integration;
	}

	/**
	 * Is the constructor's only required parameter typed as a class the duplicate is an instance of.
	 *
	 * @param ReflectionClass    $integration_class The gateway plugin's integration class.
	 * @param WC_Payment_Gateway $duplicate The duplicate gateway.
	 *
	 * @phpstan-param ReflectionClass<IntegrationInterface> $integration_class
	 */
	protected function is_constructor_gateway_parameter( ReflectionClass $integration_class, WC_Payment_Gateway $duplicate ): bool {

		$constructor = $integration_class->getConstructor();

		if ( is_null( $constructor ) || 0 === $constructor->getNumberOfParameters() || 1 < $constructor->getNumberOfRequiredParameters() ) {
			return false;
		}

		$type = $constructor->getParameters()[0]->getType();

		return $type instanceof ReflectionNamedType
			&& ! $type->isBuiltin()
			&& is_a( $duplicate, $type->getName() );
	}
}
