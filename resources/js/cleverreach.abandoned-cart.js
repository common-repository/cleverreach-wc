/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

(function () {
	jQuery( document ).ready(
		function () {

			jQuery( '#tabs-container' ).on(
				"tabsactivate",
				function (event, ui) {
					ui.newTab[0].firstElementChild.classList.add( 'nav-tab-active' );
					ui.oldTab[0].firstElementChild.classList.remove( 'nav-tab-active' );
					switch (ui.newTab[0].id) {
						case 'cr-tab-ac':
							setupACPage();
							fetchCurrentSyncSettings();
							break;
						default:
					}
				}
			);

			const redirectUrl = document.getElementById( 'crRedirectUrl' ).value;

			const errorPanel   = document.getElementById( 'crACErrorPanel' );
			const errorTitle   = document.getElementById( 'crACErrorTitle' );
			const errorDismiss = document.getElementById( 'crDismissACError' );

			const activateACUrl   = document.querySelector( '#crActivateACUrl' ).value;
			const deactivateACUrl = document.querySelector( '#crDeactivateACUrl' ).value;
			const updateACUrl     = document.querySelector( '#crUpdateACUrl' ).value;

			const editACEmailUrl       = document.querySelector( '#crAbandonedCartEditEmailUrl' ).value;
			const fetchSyncSettingsUrl = document.querySelector( '#crFetchSyncSettingsUrl' ).value;

			const theaStatusCheckUrl       = document.querySelector( '#crTheaStatusCheckUrl' ).value;
			const automationStatusCheckUrl = document.querySelector( '#crAutomationStatusCheckUrl' ).value;

			const delayOptions = document.querySelectorAll( 'input[name="cr-email-after"]' );
			let initialDelay   = document.querySelector( 'input[name="cr-email-after"]:checked' ).value;
			let hours          = initialDelay;

			const activateACButton = document.querySelector( '#crAbandonedCartActivationButton' );

			const activateTHEAButton = document.querySelector( '#crAbandonedCartActivateTHEA' );
			const editEmailButton    = document.querySelector( '#crAbandonedCartEditEmail' );

			const editInSettings = document.querySelector( '#crACEditInSettings' );

			const saveChangesButton = document.querySelector( '#crACSaveButton' );

			const acDeactivateFailedTitle   = document.querySelector( '#acDeactivateFailed' ).value;
			const acActivateFailedTitle     = document.querySelector( '#acActivateFailed' ).value;
			const acChangeTimingFailedTitle = document.querySelector( '#acChangeTimingFailed' ).value;

			const acReportsTabLink = document.querySelector( '#crAcReportsTabLink' );

			function setupACPage() {

				const acActivated   = activateACButton.dataset.active === 'true';
				const theaActivated = activateTHEAButton.dataset.theaActive === 'true';

				if ( ! acActivated) {
					enableDisableACButtons( false );
					enableDisableTimingSettings( false );
					enableDisableEditEmailButton( false );
					showHideReportsTabLink( false );
				} else if ( ! theaActivated) {
					enableDisableTimingSettings( false );
					enableDisableEditEmailButton( false );
				}
				enableDisableSaveChangesButton();
			}

			function setAutomationButton(status) {
				activateACButton.dataset.active = status ? 'true' : 'false';
				const spinnerHTML               = '<span class="spinner"></span>';

				if (status) {
					activateACButton.innerHTML = spinnerHTML + 'Deactivate';
				} else {
					activateACButton.innerHTML = spinnerHTML + 'Activate';
				}
			}

			function setTheaButton(status) {
				activateTHEAButton.dataset.theaActive = status ? 'true' : 'false';

				if (status) {
					activateTHEAButton.innerHTML = 'Deactivate';
				} else {
					activateTHEAButton.innerHTML = 'Activate';
				}
			}

			function showHideErrorPanel(show, title = '') {
				if (show) {
					errorPanel.classList.remove( 'hidden' );
					errorTitle.innerText = title;
				} else {
					errorPanel.classList.add( 'hidden' );
				}
			}

			function enableDisableACButtons(enable) {
				const buttons = [
				activateTHEAButton,
				editInSettings,
				];

				if (enable) {
					buttons.forEach(
						(button) =>
						{
							button.disabled            = false;
							button.style.pointerEvents = '';
						}
					);

					activateTHEAButton.classList.remove( 'cr-disabled' );
				} else {
					buttons.forEach(
						(button) =>
						{
							button.disabled            = true;
							button.style.pointerEvents = 'none';
						}
					);

					activateTHEAButton.classList.add( 'cr-disabled' );
				}
			}

			function disableACSpinner() {
				activateACButton.classList.remove( 'is-loading' );
				activateACButton.disabled            = false;
				activateACButton.style.pointerEvents = '';

			}

			function enableDisableEditEmailButton(enable) {
				if (enable) {
					editEmailButton.disabled            = false;
					editEmailButton.style.pointerEvents = '';
					editEmailButton.classList.remove( 'cr-disabled' );
				} else {
					editEmailButton.disabled            = true;
					editEmailButton.style.pointerEvents = 'none';
					editEmailButton.classList.add( 'cr-disabled' );
				}
			}

			function enableDisableTimingSettings(enable) {
				const radios = document.getElementsByName( "cr-email-after" );

				radios.forEach( radio => radio.disabled = ! enable );

				enableDisableSaveChangesButton();

			}

			function enableDisableSaveChangesButton() {
				const automationActive = activateACButton.dataset.active === 'true'
				&& activateTHEAButton.dataset.theaActive === 'true';

				saveChangesButton.disabled            = ! automationActive || initialDelay === hours;
				saveChangesButton.style.pointerEvents = automationActive && initialDelay !== hours ? '' : 'none';
			}

			function fetchCurrentSyncSettings() {
				CleverReach.Ajax.get(
					fetchSyncSettingsUrl,
					null,
					function (response) {
						if (response) {
							document.getElementById( 'crACBuyersCheckbox' ).checked   = response['enabled_services']['buyers'];
							document.getElementById( 'crACContactsCheckbox' ).checked = response['enabled_services']['contacts'];
						}
					},
					'json',
					true
				);
			}

			function showHideReportsTabLink(show) {
				if (show) {
					acReportsTabLink.classList.remove( 'hidden' );
				} else {
					acReportsTabLink.classList.add( 'hidden' );
				}
			}

			activateACButton.addEventListener(
				'click',
				function () {
					activateACButton.classList.add( 'is-loading' );
					activateACButton.disabled            = true;
					activateACButton.style.pointerEvents = 'none';

					enableDisableACButtons( false );
					enableDisableTimingSettings( false );
					enableDisableEditEmailButton( false );

					if (activateACButton.dataset.active === 'true') {
						CleverReach.Ajax.post(
							deactivateACUrl,
							null,
							function (response) {
								if (response.success) {
									setAutomationButton( false );
									showHideErrorPanel( false );
									showHideReportsTabLink( false );
									disableACSpinner();
								} else {
									showHideErrorPanel( true, acDeactivateFailedTitle );
									checkAutomationStatus();
								}
							},
							'json',
							true
						);
					} else {
						CleverReach.Ajax.post(
							activateACUrl,
							null,
							function (response) {
								if (response.success) {
									checkAutomationStatus();
									showHideErrorPanel( false );
								} else {
									showHideErrorPanel( true, acActivateFailedTitle );
									disableACSpinner();
								}
							},
							'json',
							true
						);
					}
				}
			);

			activateTHEAButton.addEventListener(
				'click',
				function () {
					const theaId = activateTHEAButton.dataset.theaId;

					CleverReach.Ajax.post(
						redirectUrl,
						{param: editACEmailUrl + `${theaId}`},
						function (response) {
							activateTHEAButton.blur();
							if (response.url) {
								const win = window.open( response.url, '_blank' );
								win.focus();
							}
						},
						'json',
						true
					);
				}
			);

			editEmailButton.addEventListener(
				'click',
				function () {
					const theaId = activateTHEAButton.dataset.theaId;

					CleverReach.Ajax.post(
						redirectUrl,
						{param: editACEmailUrl + `${theaId}`},
						function (response) {
							editEmailButton.blur();
							if (response.url) {
								const win = window.open( response.url, '_blank' );
								win.focus();
							}
						},
						'json',
						true
					);
				}
			);

			editInSettings.addEventListener(
				'click',
				function () {
					jQuery( "#tabs-container" ).tabs( "option", "active", 1 );
				}
			);

			errorDismiss.addEventListener(
				'click',
				function () {
					showHideErrorPanel( false );
				}
			);

			saveChangesButton.addEventListener(
				'click',
				function () {
					CleverReach.Ajax.post(
						updateACUrl,
						{'hours': hours},
						function (response) {
							if (response.success) {
								initialDelay = hours;
								enableDisableSaveChangesButton();
								showHideErrorPanel( false );
							} else {
								showHideErrorPanel( true, acChangeTimingFailedTitle );
							}
						},
						'json',
						true
					);
				}
			);

			delayOptions.forEach(
				el =>
				{
					el.addEventListener(
						'change',
						function () {
							hours = document.querySelector( 'input[name="cr-email-after"]:checked' ).value;
							enableDisableSaveChangesButton();
						}
					);
				}
			);

			function checkAutomationStatus() {
				CleverReach.Ajax.post(
					automationStatusCheckUrl,
					null,
					function (response) {
						activateTHEAButton.dataset.theaId = response.theaID;
						const theaActive                  = activateTHEAButton.dataset.theaActive === 'true';

						if (['created', 'incomplete'].indexOf( response.status ) !== -1) {
							const acEnabled = response.status === 'created';

							showHideErrorPanel( ! acEnabled, acActivateFailedTitle );
							setAutomationButton( acEnabled );
							showHideReportsTabLink( acEnabled );

							enableDisableACButtons( acEnabled );
							enableDisableTimingSettings( acEnabled && theaActive );
							enableDisableEditEmailButton( acEnabled && theaActive );

							disableACSpinner();
						} else { // 'creating' or 'initialized'
							setTimeout( checkAutomationStatus, 1000 );
						}
					},
					'json',
					true
				);
			}

			function checkTheaStatus() {
				CleverReach.Ajax.post(
					theaStatusCheckUrl,
					null,
					function (response) {
						const theaActive = activateTHEAButton.dataset.theaActive === 'true';
						if ((response.theaIsActive && response.automationIsActive) !== theaActive) {
							setTheaButton( response.theaIsActive && response.automationIsActive );
							enableDisableTimingSettings( response.theaIsActive && response.automationIsActive );
							enableDisableEditEmailButton( response.theaIsActive && response.automationIsActive );
							showHideReportsTabLink( response.automationIsActive );
						}

						setTimeout( checkTheaStatus, 5000 );
					},
					'json',
					true
				);
			}

			setupACPage();
			fetchCurrentSyncSettings();
			checkTheaStatus();
		}
	);
})();