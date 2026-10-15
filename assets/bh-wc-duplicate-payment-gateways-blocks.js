/* global wc */
/**
 * Register each duplicate payment gateway with the block checkout: its title as the label and its description as
 * the content.
 *
 * @see \BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Blocks\Duplicate_Payment_Method_Type
 */
( function () {
	'use strict';

	const { registerPaymentMethod } = wc.wcBlocksRegistry;
	const { getSetting } = wc.wcSettings;
	const { createElement } = wp.element;
	const { decodeEntities } = wp.htmlEntities;

	const paymentMethodData = getSetting( 'paymentMethodData', {} );

	Object.keys( paymentMethodData ).forEach( function ( name ) {
		const data = paymentMethodData[ name ];

		if ( ! data || true !== data.bh_wc_duplicate_payment_gateway ) {
			return;
		}

		const label = decodeEntities( data.title || name );

		const Content = function () {
			return createElement(
				'div',
				null,
				decodeEntities( data.description || '' )
			);
		};

		registerPaymentMethod( {
			name,
			label: createElement( 'span', null, label ),
			ariaLabel: label,
			content: createElement( Content, null ),
			edit: createElement( Content, null ),
			canMakePayment: () => true,
			supports: {
				features: data.supports || [],
			},
		} );
	} );
} )();
