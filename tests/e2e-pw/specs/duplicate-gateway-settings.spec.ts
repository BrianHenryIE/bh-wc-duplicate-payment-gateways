/**
 * Duplicates have extra settings: an admin title and an admin description.
 *
 * @see src/woocommerce/class-payment-gateway-settings.php
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import {
	createDuplicate,
	deleteDuplicate,
	uniqueDuplicateId,
	updateDuplicateSettings,
} from '../helpers/development-plugin/rest/duplicates';
import { getPaymentGateway } from '../helpers/general/rest/wc-payment-gateways';
import { addTestProductToCart } from '../helpers/general/ui/cart';

const ADMIN_TITLE = 'bh_wc_duplicate_payment_gateways_admin_title';

test.describe( 'Duplicate gateway settings', () => {
	let duplicateId: string;

	test.beforeEach( async ( { requestUtils } ) => {
		duplicateId = uniqueDuplicateId( 'demo_gateway' );
		await createDuplicate( requestUtils, 'demo_gateway', duplicateId );
	} );

	test.afterEach( async ( { requestUtils } ) => {
		await deleteDuplicate( requestUtils, duplicateId );
	} );

	test( 'the admin title is shown to shop managers instead of the original title', async ( {
		admin,
		page,
		requestUtils,
	} ) => {
		await admin.visitAdminPage(
			`admin.php?page=wc-settings&tab=checkout&section=${ duplicateId }`
		);

		await page
			.locator( `#woocommerce_${ duplicateId }_${ ADMIN_TITLE }` )
			.fill( 'Demo Gateway – Phone orders' );
		await page.getByRole( 'button', { name: 'Save changes' } ).click();
		await expect(
			page.getByText( 'Your settings have been saved.' )
		).toBeVisible();

		const gateway = await getPaymentGateway( requestUtils, duplicateId );
		expect( gateway.method_title ).toBe( 'Demo Gateway – Phone orders' );
	} );

	test( "until an admin title is set, the duplicate's id distinguishes it in WooCommerce / Settings / Payments", async ( {
		admin,
		page,
	} ) => {
		await admin.visitAdminPage( 'admin.php?page=wc-settings&tab=checkout' );

		await expect(
			page.getByText( `Demo Gateway (${ duplicateId })` ).first()
		).toBeVisible();
	} );

	test( 'the original gateway does not have the extra settings', async ( {
		admin,
		page,
	} ) => {
		await admin.visitAdminPage(
			'admin.php?page=wc-settings&tab=checkout&section=demo_gateway'
		);

		await expect(
			page.locator( `#woocommerce_demo_gateway_${ ADMIN_TITLE }` )
		).toHaveCount( 0 );
	} );

	test( 'a duplicate is available at the (shortcode) checkout', async ( {
		page,
		requestUtils,
	} ) => {
		await updateDuplicateSettings( requestUtils, duplicateId, {
			enabled: 'yes',
			title: `Visible ${ duplicateId }`,
		} );

		await addTestProductToCart( page, requestUtils );
		await page.goto( '/checkout-shortcode/' );

		await expect(
			page.getByText( `Visible ${ duplicateId }` )
		).toBeVisible();
	} );
} );
