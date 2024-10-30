/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

(function () {
	const startConfigurationUrl = document.getElementById( 'cr-start-server-configuration-url' ).value;
	const statusCheckUrl        = document.getElementById( 'cr-check-status-url' ).value;
	let autoconfigureFailed     = document.getElementById( 'cr-autoconfigure-failed' ).value;
	const retryTest             = document.getElementById( 'cr-retryTest' );
	const loader                = document.getElementById( 'autoconfigure-loading-container' );

	retryTest.addEventListener(
		'click',
		function () {
			autoconfigureFailed = false;
			toggleErrorPanel();
			startConfigurationTest( startConfigurationUrl );
			setTimeout( checkConfigurationStatus, 250 );
		}
	);

	if (autoconfigureFailed) {
		toggleErrorPanel();
	} else {
		checkConfigurationStatus();
	}

	function toggleErrorPanel() {
		if (autoconfigureFailed) {
			document.getElementById( 'autoconfigureErrorPanel' ).classList.remove( 'cr-hidden' );
			loader.classList.add( 'cr-hidden' );
		} else {
			document.getElementById( 'autoconfigureErrorPanel' ).classList.add( 'cr-hidden' );
			loader.classList.remove( 'cr-hidden' );
		}
	}

	function startConfigurationTest(url) {
		CleverReach.Ajax.post(
			url,
			null,
			function () {
			},
			'json',
			true
		);
	}

	function checkConfigurationStatus() {
		CleverReach.Ajax.get(
			statusCheckUrl,
			null,
			function (response) {
				switch (response.status) {
					case 'started':
						setTimeout( checkConfigurationStatus, 250 );
						break;
					case 'succeeded':
						setTimeout(
							function () {
								location.reload();
							},
							250
						);
						break;
					case 'failed':
						autoconfigureFailed = true;
						toggleErrorPanel();
						break;
					default:
						startConfigurationTest( startConfigurationUrl );
						setTimeout( checkConfigurationStatus, 250 );
				}
			},
			'json',
			true
		);
	}
})();