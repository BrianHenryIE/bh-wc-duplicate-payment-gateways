(function( $ ) {
	'use strict';

	$(
		function() {

			$( '.duplicate-add' ).on(
				'click',
				function( e ) {

					var gateway_id = $( this ).data( 'gateway_id' );
					var duplicate_id = prompt( "Please enter a unique id for the new payment gateway. This is mainly for the database, the title can be changed later.", gateway_id + '-new' );
					var nonce = bh_wc_duplicate_payment_gateways.nonce;
					var action = 'add_new_duplicate_payment_gateway';

					$.post(
						ajaxurl,
						{
							_ajax_nonce: nonce,
							action: action,
							gateway_id: gateway_id,
							duplicate_id: duplicate_id
						},
						function(response) {
							if (response.success === true) {
								// TODO: Add an overlay with loading icon.
								// TODO: Should maybe open the newly duplicated gateway's settings (although hard to distinguish at this stage).
								window.location.reload();
							} else {
								alert( response.data.message );
							}
						}
					);

				}
			);

			$( '.duplicate-delete' ).on(
				'click',
				function( e ) {

					// TODO: Offer the option to hide instead.
					var confirmed = confirm( 'Deleting a duplicate gateway may cause issues with WooCommerce reports and rendering order details. Are you sure you want to delete it?' );

					if ( confirmed ) {

						var gateway_id = $( this ).data( 'gateway_id' );
						var nonce = bh_wc_duplicate_payment_gateways.nonce;
						var action = 'delete_duplicate_payment_gateway';

						$.post(
							ajaxurl,
							{
								_ajax_nonce: nonce,
								action: action,
								gateway_id: gateway_id
							},
							function(response) {
								if (response.success === true) {
									// TODO: Add an overlay with loading icon.
									// TODO: Should maybe open the newly duplicated gateway's settings (although hard to distinguish at this stage).
									window.location.reload();
								} else {
									alert( response.data.message );
								}
							}
						);
					}
				}
			);

		}
	);
})( jQuery );
