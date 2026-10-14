import { request, type FullConfig } from '@playwright/test';
import { RequestUtils } from '@wordpress/e2e-test-utils-playwright';

async function globalSetup( config: FullConfig ) {
	const { storageState, baseURL } = config.projects[ 0 ].use;
	const storageStatePath =
		typeof storageState === 'string' ? storageState : undefined;

	// Retry the whole setup because the Docker container (with Xdebug) can be
	// slow enough that the library's internal 60s poll times out on a cold request.
	const MAX_ATTEMPTS = 5;
	let lastError: unknown;
	for ( let attempt = 1; attempt <= MAX_ATTEMPTS; attempt++ ) {
		const requestContext = await request.newContext( { baseURL } );
		const requestUtils = new RequestUtils( requestContext, { storageStatePath } );
		try {
			await requestUtils.setupRest();
			await requestContext.dispose();
			return;
		} catch ( error ) {
			await requestContext.dispose();
			lastError = error;
			if ( attempt < MAX_ATTEMPTS ) {
				// eslint-disable-next-line no-console
				console.log( `Global setup attempt ${ attempt } failed, retrying…` );
			}
		}
	}
	throw lastError;
}

export default globalSetup;