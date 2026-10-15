/* global wc */
( function () {
	'use strict';

	const { registerPaymentMethod } = wc.wcBlocksRegistry;
	const { getSetting } = wc.wcSettings;
	const { createElement } = wp.element;
	const { decodeEntities } = wp.htmlEntities;

	const data = getSetting( 'demo_gateway_data', {} );
	const label = decodeEntities( data.title || 'Demo Gateway' );
	const Content = () =>
		createElement( 'div', null, decodeEntities( data.description || '' ) );

	registerPaymentMethod( {
		name: 'demo_gateway',
		label: createElement( 'span', null, label ),
		ariaLabel: label,
		content: createElement( Content, null ),
		edit: createElement( Content, null ),
		canMakePayment: () => true,
		supports: { features: [ 'products' ] },
	} );
} )();
