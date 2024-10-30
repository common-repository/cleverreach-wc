/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

var CleverReach = CleverReach || {};

/**
 * Checks connection status
 */
(function () {

	/**
	 * Configurations and constants
	 *
	 * @type {{get}}
	 */
	const config = (function () {
		const constants = {
			STATUS_FINISHED: 'finished',
			STATUS_FAILED: 'failed',
		};

		return {
			get: function (name) {
				return constants[name];
			},
		};
	})();

	function AuthorizationConstructor(checkLoginStatusUrl, successCallback, failureCallback) {
		this.getStatus = function () {
			const self = this;

			CleverReach.Ajax.get(
				checkLoginStatusUrl,
				null,
				function (response) {
					if (response.status === config.get( 'STATUS_FINISHED' )) {
						successCallback();
					} else if (failureCallback && response.status === config.get( 'STATUS_FAILED' )) {
						failureCallback();
					} else {
						setTimeout(
							function () {
								self.getStatus();
							},
							250
						);
					}
				},
				'json',
				true
			);
		};
	}

	CleverReach.Authorization = AuthorizationConstructor;
})();
