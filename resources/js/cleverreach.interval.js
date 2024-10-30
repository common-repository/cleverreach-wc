/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

var CleverReach = window['CleverReach'] || {};
(function () {

	var interval = {
		settings : {
			intervalType : '',
			nextRun: '',
			interval: ''
		},

		get: function () {
			let intervalType   = document.getElementById( 'crInterval' ),
				customTime     = document.getElementById( 'crIntervalTime' ),
				customInterval = document.getElementById( 'crCustomInterval' ),
				intervalUrl    = document.getElementById( 'crGetIntervalSettingsUrl' ),
				me             = this;

			intervalType.addEventListener( 'change', this.handleInputChange );
			customTime.addEventListener( 'change', this.handleCustomTimeChange );
			customInterval.addEventListener( 'change', this.handleCustomIntervalChange );

			CleverReach.Ajax.get(
				intervalUrl.value,
				null,
				function (response) {
					CleverReach.Interval.settings = response;
					intervalType.value            = response.intervalType;

					if (response.hasOwnProperty( 'interval' ) && response.intervalType === 'custom') {
						customInterval.value = response.interval;
					}

					if (response.hasOwnProperty( 'nextRun' ) && response.intervalType === 'daily') {
						let date         = new Date( parseInt( response.nextRun ) * 1000 );
						customTime.value = ( '0' + date.getHours() ).slice( -2 ) + ':' + ( '0' + date.getMinutes() ).slice( -2 );
					}

					me.renderCustomIntervalFields( response.intervalType );
				},
				'json',
				true
			);
		},

		handleInputChange: function (event) {
			CleverReach.Interval.renderCustomIntervalFields( event.target.value );
			CleverReach.Interval.enableSaveButton();
		},

		handleCustomIntervalChange: function (event) {
			if (event.target.value !== CleverReach.Interval.settings.interval) {
				CleverReach.Interval.enableSaveButton();
			}
		},

		handleCustomTimeChange: function (event) {
			if (event.target.value !== CleverReach.Interval.settings.nextRun) {
				CleverReach.Interval.enableSaveButton();
			}
		},

		renderCustomIntervalFields: function (intervalType) {
			let customTime     = document.getElementById( 'crIntervalTime' ),
				customInterval = document.getElementById( 'crCustomInterval' );

			if (intervalType === 'custom') {
				customInterval.style.display = "block";
				customTime.style.display     = "none";

				return;
			}

			if (intervalType === 'daily') {
				customInterval.style.display = "none";
				customTime.style.display     = "block";

				return;
			}

			customInterval.style.display = "none";
			customTime.style.display     = "none";
		},

		enableSaveButton: function () {
			const saveButton    = document.querySelector( '#crSettingsSaveButton' );
			saveButton.disabled = false;
		}
	};

	/**
	 * Interval component
	 *
	 * @type {
	 * {
	 * get: interval.get,
	 * handleInputChange: interval.handleInputChange,
	 * enableSaveButton: interval.enableSaveButton,
	 * settings: interval.settings
	 * }
	 * }
	 */
	CleverReach.Interval = interval;
})();