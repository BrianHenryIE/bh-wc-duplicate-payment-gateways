/**
 * Put a product in the current user's cart, so the checkout can be visited.
 */
import type { Page } from '@playwright/test';
import type { RequestUtils } from '@wordpress/e2e-test-utils-playwright';

export async function getTestProductId(
	requestUtils: RequestUtils
): Promise< number > {
	const products: Array< { id: number; name: string } > =
		await requestUtils.rest( {
			path: '/wc/v3/products',
			params: { search: 'Test Product' },
		} );
	const product = products.find( ( p ) => p.name === 'Test Product' );
	if ( ! product ) {
		throw new Error(
			'"Test Product" not found; it is created by tests/_wp-env/initialize-internal.sh.'
		);
	}
	return product.id;
}

export async function addTestProductToCart(
	page: Page,
	requestUtils: RequestUtils
): Promise< void > {
	const productId = await getTestProductId( requestUtils );
	await page.goto( `/?add-to-cart=${ productId }` );
}
