/* global wc */
/**
 * Register each instance of the demo gateway with fields (the original and its duplicates) with the block checkout.
 *
 * @see \BrianHenryIE\WC_Duplicate_Payment_Gateways\Development_Plugin\Gateways\Demo_Gateway_With_Fields_Blocks
 */
( function () {
	'use strict';

	const { registerPaymentMethod } = wc.wcBlocksRegistry;
	const { getSetting } = wc.wcSettings;
	const { createElement, useEffect, useState } = wp.element;
	const { decodeEntities } = wp.htmlEntities;

	const paymentMethodData = getSetting( 'paymentMethodData', {} );

	Object.keys( paymentMethodData ).forEach( function ( name ) {
		const data = paymentMethodData[ name ];

		if ( ! data || true !== data.is_demo_gateway_with_fields ) {
			return;
		}

		const label = decodeEntities( data.title || name );
		const inputId = data.reference_field_name;

		const Content = function ( { eventRegistration, emitResponse } ) {
			const [ reference, setReference ] = useState( '' );
			const { onPaymentSetup } = eventRegistration;
			const { responseTypes } = emitResponse;

			useEffect(
				function () {
					return onPaymentSetup( function () {
						if ( '' === reference.trim() ) {
							return {
								type: responseTypes.ERROR,
								message: 'Please enter a reference.',
							};
						}
						return {
							type: responseTypes.SUCCESS,
							meta: {
								paymentMethodData: {
									[ data.reference_field_name ]:
										reference.trim(),
								},
							},
						};
					} );
				},
				[ reference, onPaymentSetup, responseTypes ]
			);

			return createElement(
				'div',
				null,
				createElement(
					'p',
					null,
					decodeEntities( data.description || '' )
				),
				createElement( 'label', { htmlFor: inputId }, 'Reference' ),
				createElement( 'input', {
					id: inputId,
					type: 'text',
					value: reference,
					onChange: ( event ) => setReference( event.target.value ),
				} )
			);
		};

		const Edit = function () {
			return createElement(
				'p',
				null,
				decodeEntities( data.description || '' )
			);
		};

		registerPaymentMethod( {
			name,
			label: createElement( 'span', null, label ),
			ariaLabel: label,
			content: createElement( Content, null ),
			edit: createElement( Edit, null ),
			canMakePayment: () => true,
			supports: { features: [ 'products' ] },
		} );
	} );
} )();
