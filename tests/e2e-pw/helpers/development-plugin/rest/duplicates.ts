/**
 * Arrange duplicate gateways via the development plugin's REST endpoints.
 *
 * @see development-plugin/rest/class-duplicates.php
 */
import type { RequestUtils } from '@wordpress/e2e-test-utils-playwright';

const PATH = '/bh-wc-duplicate-payment-gateways-development/v1/duplicates';

export async function createDuplicate(
	requestUtils: RequestUtils,
	gatewayId: string,
	duplicateId: string
): Promise< { success: boolean; message: string } > {
	return await requestUtils.rest( {
		method: 'POST',
		path: PATH,
		data: { gateway_id: gatewayId, duplicate_id: duplicateId },
	} );
}

export async function deleteAllDuplicates( requestUtils: RequestUtils ): Promise< void > {
	await requestUtils.rest( { method: 'DELETE', path: PATH } );
}

export async function getDuplicateConfigs(
	requestUtils: RequestUtils
): Promise< Record< string, { class_name: string; new_id: string } > > {
	const configs = await requestUtils.rest( { path: PATH } );
	// An empty PHP array serialises as `[]`.
	return Array.isArray( configs ) ? {} : configs;
}

export async function deleteDuplicate( requestUtils: RequestUtils, duplicateId: string ): Promise< void > {
	await requestUtils.rest( { method: 'DELETE', path: `${ PATH }/${ duplicateId }` } );
}

/**
 * A unique id per test, since the tests run in parallel against the same site.
 *
 * @param prefix E.g. the gateway being duplicated.
 */
export function uniqueDuplicateId( prefix: string ): string {
	return `${ prefix }_${ Date.now().toString( 36 ) }${ Math.floor( Math.random() * 1000 ) }`;
}
