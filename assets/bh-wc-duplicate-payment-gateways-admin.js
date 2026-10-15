/* global jQuery */
/**
 * Duplicate and delete payment gateways.
 *
 * On WooCommerce / Duplicate Gateways (`admin.php?page=bh-wc-duplicate-payment-gateways`), and on the legacy
 * (pre-WooCommerce 9.x) payment gateways list table.
 *
 * @param {jQuery} $ jQuery.
 * @see \BrianHenryIE\WC_Duplicate_Payment_Gateways\Admin\Duplicate_Gateways_Page
 * @see \BrianHenryIE\WC_Duplicate_Payment_Gateways\Admin\AJAX
 */
( function ( $ ) {
	'use strict';

	// Added with `wp_add_inline_script()` after this file, so read it when needed.
	function getSettings() {
		return window.bh_wc_duplicate_payment_gateways;
	}

	function isDuplicateGatewaysPage() {
		return $( '.wrap.bh-wc-duplicate-payment-gateways' ).length > 0;
	}

	function showError( message ) {
		const $notice = $( '.bh-wc-duplicate-payment-gateways-error' );
		if ( $notice.length === 0 ) {
			// eslint-disable-next-line no-alert
			window.alert( message );
			return;
		}
		$notice.find( 'p' ).text( message );
		$notice.removeClass( 'hidden' );
	}

	function reloadWithNotice( key, gatewayId ) {
		if ( ! isDuplicateGatewaysPage() ) {
			window.location.reload();
			return;
		}
		const url = new URL( window.location.href );
		url.searchParams.delete( 'added' );
		url.searchParams.delete( 'deleted' );
		url.searchParams.set( key, gatewayId );
		window.location.href = url.toString();
	}

	function addDuplicate( gatewayId, duplicateId, $button ) {
		$button.prop( 'disabled', true );
		$.post( getSettings().ajax_url, {
			_ajax_nonce: getSettings().nonce,
			action: 'add_new_duplicate_payment_gateway',
			gateway_id: gatewayId,
			duplicate_id: duplicateId,
		} )
			.done( function ( response ) {
				if ( response.success === true ) {
					reloadWithNotice( 'added', duplicateId );
				} else {
					showError( response.data.message );
					$button.prop( 'disabled', false );
				}
			} )
			.fail( function () {
				showError( 'Request failed.' );
				$button.prop( 'disabled', false );
			} );
	}

	function deleteDuplicate( gatewayId, $button ) {
		// eslint-disable-next-line no-alert
		const confirmed = window.confirm(
			'Deleting a duplicate gateway may cause issues with WooCommerce reports and rendering order details. Are you sure you want to delete it?'
		);
		if ( ! confirmed ) {
			return;
		}

		$button.prop( 'disabled', true );
		$.post( getSettings().ajax_url, {
			_ajax_nonce: getSettings().nonce,
			action: 'delete_duplicate_payment_gateway',
			gateway_id: gatewayId,
		} )
			.done( function ( response ) {
				if ( response.success === true ) {
					reloadWithNotice( 'deleted', gatewayId );
				} else {
					showError( response.data.message );
					$button.prop( 'disabled', false );
				}
			} )
			.fail( function () {
				showError( 'Request failed.' );
				$button.prop( 'disabled', false );
			} );
	}

	$( function () {
		// Duplicate Gateways page.
		$( '.duplicate-add-form' ).on( 'submit', function ( e ) {
			e.preventDefault();
			const $form = $( this );
			addDuplicate(
				$form.data( 'gateway_id' ),
				$form.find( 'input[name="duplicate_id"]' ).val(),
				$form.find( 'button' )
			);
		} );

		// Legacy payment gateways list table.
		$( 'span.duplicate-add' ).on( 'click', function () {
			const gatewayId = $( this ).data( 'gateway_id' );
			// eslint-disable-next-line no-alert
			const duplicateId = window.prompt(
				'Please enter a unique id for the new payment gateway. This is mainly for the database, the title can be changed later.',
				gatewayId + '_2'
			);
			if ( duplicateId ) {
				addDuplicate( gatewayId, duplicateId, $( this ) );
			}
		} );

		// Both.
		$( '.duplicate-delete' ).on( 'click', function () {
			deleteDuplicate( $( this ).data( 'gateway_id' ), $( this ) );
		} );
	} );
} )( jQuery );
