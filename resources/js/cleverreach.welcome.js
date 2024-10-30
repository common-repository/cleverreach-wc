/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

(function () {
	'use strict';

	const loader      = document.getElementById( 'crLoader' ),
		headerWrapper = document.getElementsByClassName( 'cr-logo-header' )[0];

	headerWrapper.style.backgroundColor = '#f0f0f1';

	window.addEventListener(
		'message',
		function (event) {
			if (event.data === 'cr-auth-callback-action-finished') {
				onAuthCallbackFinished();
			}
		},
		false,
	);

	function onAuthCallbackFinished() {
		let checkStatusUrl = window.document.getElementById( 'crCheckStatusUrl' );
		showSpinner();

		if (elementExists( checkStatusUrl )) {
			let auth = new CleverReach.Authorization( checkStatusUrl.value, completeAuth );
			auth.getStatus();
		}
	}

	function completeAuth() {
		window.location.reload();
	}

	function showSpinner() {
		let iframe = window.document.querySelector( '.cr-content-window' );

		loader.classList.remove( 'cr-hidden' );

		if (elementExists( iframe )) {
			iframe.style.display = 'none';
		}
	}

	function elementExists(element) {
		return typeof element !== 'undefined' && element !== null;
	}

})();