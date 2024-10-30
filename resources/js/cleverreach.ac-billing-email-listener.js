/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

(function () {
	jQuery( document ).ready(
		function () {
			const billing_email_field = document.querySelector( "input[name='billing_email']" );
			const listener_url        = document.querySelector( "#cr-billing-email-listener" ).value;

			billing_email_field.addEventListener(
				'focusout',
				function (event) {
					jQuery.post(
						listener_url,
						{'billing_email': event.target.value}
					);
				}
			);
		}
	);
})();