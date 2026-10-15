/**
 * WooCommerce / Duplicate Gateways – `admin.php?page=bh-wc-duplicate-payment-gateways`.
 *
 * The page replaces the duplicate/delete column of the payment gateways list table, which WooCommerce removed when
 * WooCommerce / Settings / Payments became a React app.
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import {
	createDuplicate,
	deleteDuplicate,
	uniqueDuplicateId,
} from '../helpers/development-plugin/rest/duplicates';
import { getPaymentGatewayIds } from '../helpers/general/rest/wc-payment-gateways';

const PAGE = 'admin.php?page=bh-wc-duplicate-payment-gateways';

test.describe( 'Duplicate Gateways page', () => {
	test( 'offers to duplicate only gateways that can be duplicated', async ( { admin, page } ) => {
		await admin.visitAdminPage( PAGE );

		await expect( page.getByRole( 'heading', { name: 'Duplicate Payment Gateways' } ) ).toBeVisible();

		const demoGatewayRow = page.locator( 'tr[data-gateway_id="demo_gateway"]' );
		await expect( demoGatewayRow.getByRole( 'button', { name: 'Duplicate' } ) ).toBeVisible();

		// WooCommerce's offline gateways, and the demo gateways which set their id or need constructor arguments.
		for ( const gatewayId of [ 'cheque', 'bacs', 'cod', 'demo_hardcoded_id', 'demo_constructor_args' ] ) {
			const row = page.locator( `tr[data-gateway_id="${ gatewayId }"]` );
			await expect( row.getByText( 'Cannot be duplicated' ) ).toBeVisible();
			await expect( row.getByRole( 'button', { name: 'Duplicate' } ) ).toHaveCount( 0 );
		}
	} );

	test( 'duplicates a gateway', async ( { admin, page, requestUtils } ) => {
		const duplicateId = uniqueDuplicateId( 'demo_gateway' );

		await admin.visitAdminPage( PAGE );

		const demoGatewayRow = page.locator( 'tr[data-gateway_id="demo_gateway"]' );
		await demoGatewayRow.getByRole( 'textbox' ).fill( duplicateId );
		await demoGatewayRow.getByRole( 'button', { name: 'Duplicate' } ).click();

		await expect( page.getByText( `Created payment method "${ duplicateId }".` ) ).toBeVisible();
		await expect( page.locator( `tr[data-gateway_id="${ duplicateId }"] .column-duplicate-of` ) ).toHaveText(
			'demo_gateway'
		);

		expect( await getPaymentGatewayIds( requestUtils ) ).toContain( duplicateId );

		await deleteDuplicate( requestUtils, duplicateId );
	} );

	test( 'shows why duplicating failed', async ( { admin, page } ) => {
		await admin.visitAdminPage( PAGE );

		const demoGatewayRow = page.locator( 'tr[data-gateway_id="demo_gateway"]' );
		await demoGatewayRow.getByRole( 'textbox' ).fill( 'cheque' );
		await demoGatewayRow.getByRole( 'button', { name: 'Duplicate' } ).click();

		await expect( page.getByText( 'A gateway with this id already exists.' ) ).toBeVisible();
	} );

	test( 'deletes a duplicate', async ( { admin, page, requestUtils } ) => {
		const duplicateId = uniqueDuplicateId( 'demo_gateway' );
		await createDuplicate( requestUtils, 'demo_gateway', duplicateId );

		await admin.visitAdminPage( PAGE );

		page.once( 'dialog', ( dialog ) => dialog.accept() );
		await page
			.locator( `tr[data-gateway_id="${ duplicateId }"]` )
			.getByRole( 'button', { name: 'Delete' } )
			.click();

		await expect( page.getByText( `Deleted payment method "${ duplicateId }".` ) ).toBeVisible();

		expect( await getPaymentGatewayIds( requestUtils ) ).not.toContain( duplicateId );
	} );

	test( 'is linked from WooCommerce / Settings / Payments', async ( { admin, page } ) => {
		await admin.visitAdminPage( 'admin.php?page=wc-settings&tab=checkout' );

		await page.getByRole( 'link', { name: 'Duplicate payment gateways' } ).click();

		await expect( page.getByRole( 'heading', { name: 'Duplicate Payment Gateways' } ) ).toBeVisible();
	} );

	test( 'is linked from a duplicable gateway\'s settings page', async ( { admin, page } ) => {
		await admin.visitAdminPage( 'admin.php?page=wc-settings&tab=checkout&section=demo_gateway' );

		await expect( page.getByText( 'This payment method can be duplicated.' ) ).toBeVisible();
	} );
} );

test.describe( 'Plugins page', () => {
	test( 'links to the Duplicate Gateways page', async ( { admin, page } ) => {
		await admin.visitAdminPage( 'plugins.php' );

		await page
			.locator( 'tr[data-plugin="bh-wc-duplicate-payment-gateways/bh-wc-duplicate-payment-gateways.php"]' )
			.getByRole( 'link', { name: 'Duplicate Gateways' } )
			.click();

		await expect( page.getByRole( 'heading', { name: 'Duplicate Payment Gateways' } ) ).toBeVisible();
	} );

	test( 'a plugin with duplicated gateways links to its gateways', async ( { admin, page, requestUtils } ) => {
		const duplicateId = uniqueDuplicateId( 'demo_gateway' );
		await createDuplicate( requestUtils, 'demo_gateway', duplicateId );

		await admin.visitAdminPage( 'plugins.php' );

		await page
			.locator( 'tr[data-plugin="development-plugin/development-plugin.php"]' )
			.getByRole( 'link', { name: 'Gateways', exact: true } )
			.click();

		await expect( page ).toHaveURL( /page=bh-wc-duplicate-payment-gateways&plugin=development-plugin/ );
		await expect( page.locator( `tr[data-gateway_id="${ duplicateId }"]` ) ).toBeVisible();
		await expect( page.locator( 'tr[data-gateway_id="demo_gateway"]' ) ).toBeVisible();
		// Gateways from other plugins are not listed.
		await expect( page.locator( 'tr[data-gateway_id="cheque"]' ) ).toHaveCount( 0 );

		await deleteDuplicate( requestUtils, duplicateId );
	} );
} );
