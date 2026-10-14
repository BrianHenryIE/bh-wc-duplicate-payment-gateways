/**
 * The plugin is active on the plugins page.
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe( 'Plugin activation', () => {
	test( 'plugin is active on the plugins page', async ( { admin, page } ) => {
		await admin.visitAdminPage( 'plugins.php' );

		const pluginRow = page.locator(
			'tr[data-plugin="bh-wc-duplicate-payment-gateways/bh-wc-duplicate-payment-gateways.php"]'
		);
		await expect( pluginRow ).toBeVisible();
		await expect( pluginRow.getByRole( 'link', { name: /Deactivate/ } ) ).toBeVisible();
	} );
} );
