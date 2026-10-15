/**
 * WooCommerce's own REST API for payment gateways – used for assertions, since duplicates are listed there too.
 */
import type { RequestUtils } from '@wordpress/e2e-test-utils-playwright';

export interface WcPaymentGateway {
	id: string;
	title: string;
	description: string;
	enabled: boolean;
	method_title: string;
	method_description: string;
	settings: Record< string, { value: string } >;
}

export async function getPaymentGateways(
	requestUtils: RequestUtils
): Promise< WcPaymentGateway[] > {
	return await requestUtils.rest( { path: '/wc/v3/payment_gateways' } );
}

export async function getPaymentGatewayIds(
	requestUtils: RequestUtils
): Promise< string[] > {
	return ( await getPaymentGateways( requestUtils ) ).map(
		( gateway ) => gateway.id
	);
}

export async function getPaymentGateway(
	requestUtils: RequestUtils,
	id: string
): Promise< WcPaymentGateway > {
	return await requestUtils.rest( {
		path: `/wc/v3/payment_gateways/${ id }`,
	} );
}

export async function updatePaymentGateway(
	requestUtils: RequestUtils,
	id: string,
	data: {
		enabled?: boolean;
		title?: string;
		settings?: Record< string, string >;
	}
): Promise< WcPaymentGateway > {
	return await requestUtils.rest( {
		method: 'PUT',
		path: `/wc/v3/payment_gateways/${ id }`,
		data,
	} );
}
