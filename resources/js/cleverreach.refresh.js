/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

(function () {
	document.addEventListener(
		'DOMContentLoaded',
		function () {
			const crIframe = document.getElementById( 'cr-iframe' );

			window.addEventListener(
				'message',
				function (event) {
					if (event.data === 'cr-auth-callback-action-finished') {
						crIframe.classList.add( 'cr-hidden' );
						window.location.reload();
					}
				},
				false
			);
		}
	);
})();
