/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

(function () {
	jQuery( document ).ready(
		function () {
			const newsletter_status_field     = jQuery( "#crNewsletterStatusField" ).val();
			const subscribe_url               = jQuery( "#crSubscribeUrl" ).val();
			const undo_url                    = jQuery( "#crUndoUrl" ).val();
			const subscription_checkbox       = jQuery( "input[name='" + newsletter_status_field + "']" );
			const subscription_checkbox_label = subscription_checkbox.parent();
			const email_input                 = jQuery( "input[name='billing_email']" );
			const subscription_label          = jQuery( '#crSubscriptionConfirmationMessage' );
			const undo_button                 = jQuery( '#crUndo' );
			const is_abandoned_cart_enabled   = jQuery( "#isAbandonedCartEnabled" ).val();
			const ac_time                     = jQuery( "#acTime" ).val();

			let timeoutID = 0;

			subscription_label.hide();

			subscription_checkbox.change(
				function () {
					if (this.checked && is_abandoned_cart_enabled) {
						jQuery.post(
							subscribe_url,
							{
								'billing_email': email_input.val(),
								'cr_status': this.checked
							},
							function (response) {
								if (response.success) {
									subscription_checkbox_label.hide( 100 );
									subscription_label.show( 100 );

									timeoutID = setTimeout(
										function () {
											subscription_checkbox.prop( 'checked', false );
											subscription_label.hide();
										},
										ac_time * 1000
									);
								}
							}
						);
					}
				}
			);

			undo_button.click(
				function () {
					jQuery.post(
						undo_url,
						null,
						function (response) {
							if (response.success) {
								subscription_checkbox.prop( 'checked', false );
								subscription_label.hide( 100 );
								subscription_checkbox_label.show( 100 );
								clearTimeout( timeoutID );
							}
						}
					);

				}
			);
		}
	);
})();