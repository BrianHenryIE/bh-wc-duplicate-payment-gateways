/**
 * Duplicates in the block checkout (WooCommerce's default checkout).
 *
 * The block checkout only lists gateways with a blocks integration registered under their id; the plugin registers
 * one for each duplicate of a gateway without payment fields.
 *
 * @see src/woocommerce/blocks/class-blocks-checkout.php
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import {
	createDuplicate,
	deleteDuplicate,
	uniqueDuplicateId,
	updateDuplicateSettings,
} from '../helpers/development-plugin/rest/duplicates';
import { addTestProductToCart } from '../helpers/general/ui/cart';

test.describe( 'Block checkout', () => {
	// Check out as a guest, so the address form is always empty (a logged-in customer's saved address is collapsed).
	test.use( { storageState: { cookies: [], origins: [] } } );

	let duplicateId: string;

	test.beforeEach( async ( { requestUtils } ) => {
		duplicateId = uniqueDuplicateId( 'demo_gateway' );
		await createDuplicate( requestUtils, 'demo_gateway', duplicateId );
	} );

	test.afterEach( async ( { requestUtils } ) => {
		await deleteDuplicate( requestUtils, duplicateId );
	} );

	test( 'an order can be placed with a duplicate gateway', async ( {
		page,
		requestUtils,
	} ) => {
		await updateDuplicateSettings( requestUtils, duplicateId, {
			enabled: 'yes',
			title: `Pay by phone ${ duplicateId }`,
			description: 'We will call you.',
		} );

		await addTestProductToCart( page, requestUtils );
		await page.goto( '/checkout-blocks/' );

		await page.getByLabel( 'Email address' ).fill( 'guest@example.com' );
		await page.getByLabel( 'First name' ).first().fill( 'John' );
		await page.getByLabel( 'Last name' ).first().fill( 'Doe' );
		await page
			.getByLabel( 'Address', { exact: true } )
			.first()
			.fill( '1 Main St' );
		await page.getByLabel( 'City' ).first().fill( 'San Francisco' );
		await page.getByLabel( 'ZIP Code' ).first().fill( '94107' );

		await page.getByLabel( `Pay by phone ${ duplicateId }` ).check();
		await expect( page.getByText( 'We will call you.' ) ).toBeVisible();

		await page.getByRole( 'button', { name: 'Place Order' } ).click();

		await page.waitForURL( /order-received\/(\d+)/ );
		const orderId = page.url().match( /order-received\/(\d+)/ )?.[ 1 ];

		const order: { payment_method: string; status: string } =
			await requestUtils.rest( {
				path: `/wc/v3/orders/${ orderId }`,
			} );
		expect( order.payment_method ).toBe( duplicateId );
		// The original gateway's `process_payment()` ran.
		expect( order.status ).toBe( 'on-hold' );
	} );
} );

/**
 * A gateway with payment fields, whose block checkout integration takes its name from the gateway passed to its
 * constructor: a new instance of it is registered for each duplicate, so duplicates get the gateway's own fields.
 *
 * @see src/woocommerce/blocks/class-gateway-integration-duplicator.php
 * @see development-plugin/gateways/class-demo-gateway-with-fields-blocks.php
 */
test.describe( 'Block checkout, gateway with payment fields', () => {
	test.use( { storageState: { cookies: [], origins: [] } } );

	let duplicateId: string;

	test.beforeEach( async ( { requestUtils } ) => {
		duplicateId = uniqueDuplicateId( 'demo_gateway_fields' );
		await createDuplicate(
			requestUtils,
			'demo_gateway_fields',
			duplicateId
		);
		await updateDuplicateSettings( requestUtils, duplicateId, {
			enabled: 'yes',
			title: `Reference ${ duplicateId }`,
			description: 'The duplicate’s description.',
		} );
	} );

	test.afterEach( async ( { requestUtils } ) => {
		await deleteDuplicate( requestUtils, duplicateId );
	} );

	test( 'an order can be placed with a duplicate, using the gateway’s own payment field', async ( {
		page,
		requestUtils,
	} ) => {
		await addTestProductToCart( page, requestUtils );
		await page.goto( '/checkout-blocks/' );

		await page.getByLabel( 'Email address' ).fill( 'guest@example.com' );
		await page.getByLabel( 'First name' ).first().fill( 'John' );
		await page.getByLabel( 'Last name' ).first().fill( 'Doe' );
		await page
			.getByLabel( 'Address', { exact: true } )
			.first()
			.fill( '1 Main St' );
		await page.getByLabel( 'City' ).first().fill( 'San Francisco' );
		await page.getByLabel( 'ZIP Code' ).first().fill( '94107' );

		// The original is listed too.
		await expect(
			page.locator(
				'input[name="radio-control-wc-payment-method-options"][value="demo_gateway_fields"]'
			)
		).toBeAttached();

		await page.getByLabel( `Reference ${ duplicateId }` ).check();
		await expect(
			page.getByText( 'The duplicate’s description.' )
		).toBeVisible();
		await page.locator( `#${ duplicateId }_reference` ).fill( 'REF-123' );

		await page.getByRole( 'button', { name: 'Place Order' } ).click();

		await page.waitForURL( /order-received\/(\d+)/ );
		const orderId = page.url().match( /order-received\/(\d+)/ )?.[ 1 ];

		const order: {
			payment_method: string;
			status: string;
			meta_data: Array< { key: string; value: string } >;
		} = await requestUtils.rest( {
			path: `/wc/v3/orders/${ orderId }`,
		} );
		expect( order.payment_method ).toBe( duplicateId );
		expect( order.status ).toBe( 'on-hold' );
		expect(
			order.meta_data.find( ( meta ) => meta.key === '_demo_reference' )
				?.value
		).toBe( 'REF-123' );
	} );
} );
