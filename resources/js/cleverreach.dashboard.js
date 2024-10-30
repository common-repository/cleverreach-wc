/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

(function () {
	jQuery( document ).ready(
		function () {
			const loader = document.getElementById( 'crLoader' );

			jQuery( '#tabs-container' ).tabs(
				{
					create: function (event, ui) {
						ui.tab[0].firstElementChild.classList.add( 'nav-tab-active' );
					},
					activate: function (event, ui) {
						ui.newTab[0].firstElementChild.classList.add( 'nav-tab-active' );
						ui.oldTab[0].firstElementChild.classList.remove( 'nav-tab-active' );
						switch (ui.newTab[0].id) {
							case 'cr-tab-settings':
								if ( ! secondarySyncOngoing) {
									fetchCurrentSettings();
									setButtonsState();
									setNewsletterSettingsState();
								}
								break;
							default:
						}
					}
				}
			);

			jQuery( "#dialog" ).dialog(
				{
					dialogClass: "no-close",
					resizable: false,
					height: "auto",
					width: 400,
					modal: true,
					buttons: [
					{
						text: document.getElementById( 'cr-confirm-translation' ).value,
						class: "button cr-primary",
						click: function () {
							const uninstallUrl = document.getElementById( 'crUninstallUrl' ).value;
							sendAjax( uninstallUrl, null, true );
							jQuery( this ).dialog( "close" );
						}
					},
					{
						text: document.getElementById( 'cr-cancel-translation' ).value,
						class: "button cr-secondary",
						click: function () {
							jQuery( this ).dialog( "close" );
						}
					}
					]
				}
			).dialog( 'close' );

			// DASHBOARD.
			const helpUrl = document.getElementById( 'crHelpUrl' ).value;

			const retrySyncUrl = document.getElementById( 'crRetrySyncUrl' ).value;
			const redirectUrl  = document.getElementById( 'crRedirectUrl' ).value;

			const blogButton       = document.getElementById( 'crPush' );
			const helpButton       = document.getElementById( 'crHelp' );
			const disconnectButton = document.getElementById( 'crDisconnect' );

			const retrySynchronization = document.getElementById( 'crRetrySync' );

			if (retrySynchronization) {
				retrySynchronization.addEventListener(
					'click',
					function () {
						sendAjax( retrySyncUrl, null, true );
					}
				);
			}

			const buttons = [
			document.getElementById( 'crBuildEmail' ),
			document.getElementById( 'crReports' ),
			document.getElementById( 'crForms' ),
			document.getElementById( 'crThea' ),
			document.getElementById( 'crEmails' ),
			document.getElementById( 'crPricePlan' ),
			];

			for (const b of buttons) {
				if (b) {
					b.addEventListener(
						'click',
						function () {
							openOnCleverReach( redirectUrl, {param: b.dataset.url} );
						}
					);
				}
			}

			if (blogButton) {
				blogButton.addEventListener(
					'click',
					function () {
						window.open( blogButton.dataset.url );
					}
				)
			}

			helpButton.addEventListener(
				'click',
				function () {
					const win = window.open( helpUrl, '_blank' );
					win.focus();
				}
			);

			disconnectButton.addEventListener(
				'click',
				function () {
					jQuery( "#dialog" ).dialog( 'open' );
				}
			);

			// Settings.
			const saveSyncSettingsUrl       = document.querySelector( '#crSaveSyncSettingsUrl' );
			const saveNewsletterSettingsUrl = document.querySelector( '#crSaveNewsletterSettingsUrl' );
			const fetchSettingsUrl          = document.querySelector( '#crFetchSettingsUrl' );
			const checkStatusUrl            = document.querySelector( '#crCheckSecondarySyncStatusUrl' );
			const reforceSyncUrl            = document.querySelector( '#crReforceSyncUrl' );

			const syncErrorPanel      = document.querySelector( '#syncErrorPanel' );
			const backgroundSyncPanel = document.querySelector( '#backgroundSyncPanel' );

			const subscribers                 = document.querySelector( '#crSubscribersCheckbox' );
			let buyersCheckbox                = document.querySelector( '#crBuyersCheckbox' );
			let contactsCheckbox              = document.querySelector( '#crContactsCheckbox' );
			let newsletterEnabledCheckbox     = document.querySelector( '#crNewsletterEnabled' );
			let newsletterCheckboxCaption     = document.querySelector( '#crNewsletterCaption' );
			let newsletterConfirmationMessage = document.querySelector( '#crNewsletterConfirmationMessage' );
			let doiEnabledCheckbox            = document.querySelector( '#crDoiEnabled' );
			let defaultFormSelect             = document.querySelector( '#crDefaultForm' );
			let newsletterCheckboxDisplayTime = document.querySelector( '#crDisplayTime' );
			let newsletterConfirmationRaw     = document.querySelector( '#crNewsletterConfirmationRaw' );
			let newsletterTimeRaw             = document.querySelector( '#crNewsletterTimeRaw' );
			const isUserDataComplete          = document.querySelector( '#crIsUserDataComplete' ).value === '1';

			let filterTypeSelectBox = document.querySelector( '#crFilterTypeSelectBox' ),
			staticInput             = document.querySelector( '#crFilterTypeStaticField' ),
			wildcardInput           = document.querySelector( '#crFilterTypeWildcardField' ),
			staticLabel             = document.querySelector( '#cr-static-label' ),
			wildcardLabel           = document.querySelector( '#cr-wildcard-label' ),
			input                   = getFilterInput();

			const saveButton        = document.querySelector( '#crSettingsSaveButton' );
			const reforceSyncButton = document.querySelector( '#crInitialSyncSettingsReforceSyncButton' );

			let intervalType   = document.getElementById( 'crInterval' ),
				customTime     = document.getElementById( 'crIntervalTime' ),
				customInterval = document.getElementById( 'crCustomInterval' );

			const initialSettings = {
				syncSettings: {
					subscribers: true,
					buyers: false,
					contacts: false
				},
				newsletterSettings: {
					isNewsletterCheckboxEnabled: newsletterEnabledCheckbox.checked,
					newsletterCheckboxCaption: newsletterCheckboxCaption.value,
					newsletterConfirmationMessage: newsletterConfirmationMessage.value,
					isDoiEnabled: doiEnabledCheckbox.checked,
					defaultForm: defaultFormSelect.value,
					displayTime: newsletterCheckboxDisplayTime.value.toString()
				},
				filterSettings: {
					filterType: filterTypeSelectBox.value,
					input: input
				}
			};

			let secondarySyncOngoing = false;
			let settingsHasChanged   = false;

			setFilterForm();

			function setFilterForm() {
				filterTypeSelectBox.addEventListener(
					'change',
					function (event) {
						if (event.target.value === 'static') {
							wildcardLabel.style.display = 'none';
							wildcardInput.style.display = 'none';
							staticLabel.style.display   = 'block';
							staticInput.style.display   = 'block';
						} else {
							staticLabel.style.display   = 'none';
							staticInput.style.display   = 'none';
							wildcardLabel.style.display = 'block';
							wildcardInput.style.display = 'block';
						}
					}
				);
			}

			function fetchCurrentSettings() {
				loader.classList.remove( 'cr-hidden' );

				CleverReach.Interval.get();

				CleverReach.Ajax.get(
					fetchSettingsUrl.value,
					null,
					function (response) {
						loader.classList.add( 'cr-hidden' );
						if (response) {
							let sync                 = response['enabled_services']
							buyersCheckbox.checked   = sync['buyers'];
							contactsCheckbox.checked = sync['contacts'];

							initialSettings.syncSettings.buyers   = sync['buyers'];
							initialSettings.syncSettings.contacts = sync['contacts'];

							let filterConfig = response['filter_configuration'];
							initialSettings.filterSettings.filterType = filterConfig['type'];
							initialSettings.filterSettings.input = filterConfig['rule'];
							filterTypeSelectBox.value            = filterConfig['type'];
							if (filterConfig['type'] === 'static') {
								staticInput.firstElementChild.value = filterConfig['rule'];
							} else if (filterConfig['type'] === 'wildcard') {
								wildcardInput.firstElementChild.value = filterConfig['rule'];
								staticLabel.style.display             = 'none';
								staticInput.style.display             = 'none';
								wildcardLabel.style.display           = 'block';
								wildcardInput.style.display           = 'block';
							}

							if (response['thea_activated']) {
								newsletterConfirmationRaw.classList.remove( 'cr-hidden' );
								newsletterTimeRaw.classList.remove( 'cr-hidden' );
							} else {
								newsletterConfirmationRaw.classList.add( 'cr-hidden' );
								newsletterTimeRaw.classList.add( 'cr-hidden' );
							}

							setButtonsState();
						}
					},
					'json',
					true
				);
			}

			/**
			 * Disables import button if settings have changed
			 */
			function setButtonsState() {
				isSettingsChanged();
				saveButton.disabled = ! settingsHasChanged;
			}

			function startSecSyncState() {
				secondarySyncOngoing = true;
				reforceSyncButton.classList.add( 'is-loading' );
				reforceSyncButton.disabled            = true;
				reforceSyncButton.style.pointerEvents = 'none';
			}

			function endSecSyncState() {
				secondarySyncOngoing = false;
				reforceSyncButton.classList.remove( 'is-loading' );
				reforceSyncButton.disabled            = false;
				reforceSyncButton.style.pointerEvents = '';
			}

			function secSyncHandler() {
				startSecSyncState();

				CleverReach.Ajax.post(
					reforceSyncUrl.value,
					null,
					function (response) {
						if (response.success) {
							setSyncSettingsFormToReadOnlyState();
							checkSecondarySync();
						} else {
							endSecSyncState();
							setFormToFailureState();
						}
					},
					'json',
					true
				);
			}

			reforceSyncButton.addEventListener( 'click', secSyncHandler );

			function checkSecondarySync() {
				setSyncSettingsFormToReadOnlyState();
				if (secondarySyncOngoing) {
					showBackgroundSyncPanel();
				}

				CleverReach.Ajax.get(
					checkStatusUrl.value,
					null,
					function (response) {
						if (response.status === 'completed') {
							endSecSyncState();
							enableForm();
						} else if (response.status === 'failed') {
							endSecSyncState();
							setFormToFailureState();
						} else {
							setTimeout(
								function () {
									startSecSyncState();
									checkSecondarySync();
								},
								250
							);
						}
					},
					'json',
					true
				);
			}

			function showBackgroundSyncPanel() {
				if (backgroundSyncPanel.classList.contains( 'cr-hidden' )) {
					backgroundSyncPanel.classList.remove( 'cr-hidden' );
				}
			}

			function setFormToFailureState() {
				buyersCheckbox.style.pointerEvents   = 'none';
				contactsCheckbox.style.pointerEvents = 'none';
				saveButton.disabled                  = true;
				syncErrorPanel.classList.remove( 'cr-hidden' );
				backgroundSyncPanel.classList.add( 'cr-hidden' );
			}

			function enableForm() {
				buyersCheckbox.style.pointerEvents   = '';
				contactsCheckbox.style.pointerEvents = '';
				saveButton.style.pointerEvents       = '';
				buyersCheckbox.disabled              = false;
				contactsCheckbox.disabled            = false;
				saveButton.disabled                  = false;
				buyersCheckbox.classList.remove( 'cr-disabled' );
				subscribers.classList.remove( 'cr-disabled' );
				contactsCheckbox.classList.remove( 'cr-disabled' );

				if ( ! syncErrorPanel.classList.contains( 'cr-hidden' )) {
					syncErrorPanel.classList.add( 'cr-hidden' );
				}
				if ( ! backgroundSyncPanel.classList.contains( 'cr-hidden' )) {
					backgroundSyncPanel.classList.add( 'cr-hidden' );
				}

				setButtonsState();
			}

			function setSyncSettingsFormToReadOnlyState() {
				buyersCheckbox.style.pointerEvents   = 'none';
				contactsCheckbox.style.pointerEvents = 'none';
				saveButton.style.pointerEvents       = 'none';
				buyersCheckbox.disabled              = true;
				contactsCheckbox.disabled            = true;
				saveButton.disabled                  = true;
				buyersCheckbox.classList.add( 'cr-disabled' );
				subscribers.classList.add( 'cr-disabled' );
				contactsCheckbox.classList.add( 'cr-disabled' );
			}

			if (buyersCheckbox) {
				buyersCheckbox.addEventListener(
					'change',
					function () {
						if (buyersCheckbox.checked === false) {
							contactsCheckbox.checked = false;
						}
						setButtonsState();
					}
				);
			}

			if (contactsCheckbox) {
				contactsCheckbox.addEventListener(
					'change',
					function () {
						if (contactsCheckbox.checked) {
							buyersCheckbox.checked = true;
						}
						setButtonsState();
					}
				);
			}

			function startSaveSettingsState() {
				loader.classList.remove( 'cr-hidden' );
			}

			function endSaveSettingsState() {
				loader.classList.add( 'cr-hidden' );
			}

			function displayError(message) {
				removeError();
				const crTabSettings = document.querySelector( '.cr-tab-settings' );

				if (crTabSettings) {
					const divElement = document.createElement( 'div' );
					divElement.classList.add( 'notice', 'notice-error' );

					const paragraphElement       = document.createElement( 'p' );
					paragraphElement.textContent = message;

					divElement.appendChild( paragraphElement );
					crTabSettings.insertBefore( divElement, crTabSettings.firstChild );

					window.scrollTo( 0, 0 );
				}
			}

			function removeError() {
				const crTabSettings = document.querySelector( '.cr-tab-settings' );
				if (crTabSettings) {
					const noticeElement = crTabSettings.querySelector( '.notice.notice-error' );

					if (noticeElement) {
						crTabSettings.removeChild( noticeElement );
					}
				}
			}

			function saveSettingsHandler() {
				startSaveSettingsState();
				setSyncSettingsFormToReadOnlyState();

				const selectedOptions = ['subscribers'];
				const filterSettings  = {
					'filterType': filterTypeSelectBox.value,
					'input': getFilterInput()
				};
				let date              = new Date();
				let [ hours, mins ]   = customTime.value.split( ':' );
				date.setHours( hours, mins );
				const intervalSettings = {
					'intervalType': intervalType.value,
					'customInterval': customInterval.value,
					'customTime': parseInt( '' + date.getTime() / 1000 )
				};

				if (buyersCheckbox.checked) {
					selectedOptions.push( 'buyers' );
				}

				if (contactsCheckbox.checked) {
					selectedOptions.push( 'contacts' );
				}
				const body = {
					'syncSettings': selectedOptions,
					'filterSettings': JSON.stringify( filterSettings ),
					'intervalSettings': JSON.stringify( intervalSettings )
				};

				CleverReach.Ajax.post(
					saveSyncSettingsUrl.value,
					body,
					function (response) {
						endSaveSettingsState();
						if (response.success) {
							removeError();
							/**
							 * Check if reforce sync should also be run
							 **/
							const selectedOptionsLength = selectedOptions.length;
							let initialSettingsLength   = 0;
							const initialSettingsValues = Object.values( initialSettings.syncSettings );
							for (const optionValue of initialSettingsValues) {
								if (optionValue) {
									initialSettingsLength++;
								}
							}
							initialSettings.syncSettings.buyers   = buyersCheckbox.checked;
							initialSettings.syncSettings.contacts = contactsCheckbox.checked;
							if (selectedOptionsLength > initialSettingsLength) {
								reforceSyncButton.click();
							}
							checkSecondarySync();
						} else {
							enableForm();
							displayError( response.message );
						}
					},
					'json',
					true
				);

				const newsletterSettingsBody = {
					'isNewsletterCheckboxEnabled': newsletterEnabledCheckbox.checked,
					'newsletterCheckboxCaption': newsletterEnabledCheckbox.checked ? newsletterCheckboxCaption.value : false,
					'newsletterConfirmationMessage': newsletterEnabledCheckbox.checked ? newsletterConfirmationMessage.value : false,
					'isDoiEnabled': ! ! doiEnabledCheckbox.checked,
					'defaultForm': defaultFormSelect.value || false,
					'displayTime': newsletterCheckboxDisplayTime.value || false
				};

				// Save Newsletter settings.
				CleverReach.Ajax.post(
					saveNewsletterSettingsUrl.value,
					newsletterSettingsBody,
					function (response) {
						if (response.success) {
							initialSettings.newsletterSettings.isNewsletterCheckboxEnabled   = newsletterEnabledCheckbox.checked;
							initialSettings.newsletterSettings.newsletterCheckboxCaption     = newsletterCheckboxCaption.value;
							initialSettings.newsletterSettings.newsletterConfirmationMessage = newsletterConfirmationMessage.value;
							initialSettings.newsletterSettings.isDoiEnabled                  = doiEnabledCheckbox.checked;
							initialSettings.newsletterSettings.defaultForm                   = defaultFormSelect.value;
							initialSettings.newsletterSettings.displayTime                   = newsletterCheckboxDisplayTime.value;
						}
						setButtonsState();
					},
					'json',
					true
				);
			}

			if (saveButton) {
				saveButton.addEventListener( 'click', saveSettingsHandler );
			}

			function setNewsletterSettingsState() {
				newsletterCheckboxCaption.disabled     = true;
				newsletterConfirmationMessage.disabled = true;
				doiEnabledCheckbox.disabled            = true;
				defaultFormSelect.disabled             = true;
				if (newsletterEnabledCheckbox.checked) {
					newsletterCheckboxCaption.disabled     = false;
					newsletterConfirmationMessage.disabled = false;
					if (isUserDataComplete) {
						doiEnabledCheckbox.disabled = false;
						if (doiEnabledCheckbox.checked) {
							defaultFormSelect.disabled = false;
						}
					}
				}
				setButtonsState();
			}

			newsletterEnabledCheckbox.addEventListener( 'change', setNewsletterSettingsState );
			newsletterCheckboxCaption.addEventListener( 'input', setButtonsState );
			newsletterConfirmationMessage.addEventListener( 'input', setButtonsState );
			doiEnabledCheckbox.addEventListener( 'change', setNewsletterSettingsState );
			defaultFormSelect.addEventListener( 'change', setButtonsState );
			newsletterCheckboxDisplayTime.addEventListener( 'change', setButtonsState );
			filterTypeSelectBox.addEventListener( 'change', setButtonsState );
			staticInput.firstElementChild.addEventListener( 'change', setButtonsState );
			wildcardInput.firstElementChild.addEventListener( 'change', setButtonsState );

			function getFilterInput() {
				let input;
				if (filterTypeSelectBox.value === 'static') {
					input = staticInput.firstElementChild.value;
				} else {
					input = wildcardInput.firstElementChild.value;
				}

				return input;
			}

			function isSettingsChanged() {
				if (
				initialSettings.syncSettings.buyers !== buyersCheckbox.checked ||
				initialSettings.syncSettings.contacts !== contactsCheckbox.checked ||
				initialSettings.newsletterSettings.isNewsletterCheckboxEnabled !== newsletterEnabledCheckbox.checked ||
				initialSettings.newsletterSettings.newsletterCheckboxCaption !== newsletterCheckboxCaption.value.trim() ||
				initialSettings.newsletterSettings.newsletterConfirmationMessage !== newsletterConfirmationMessage.value.trim() ||
				initialSettings.newsletterSettings.isDoiEnabled !== doiEnabledCheckbox.checked ||
				initialSettings.newsletterSettings.defaultForm !== defaultFormSelect.value ||
				initialSettings.newsletterSettings.displayTime !== newsletterCheckboxDisplayTime.value ||
				initialSettings.filterSettings.filterType !== filterTypeSelectBox.value ||
				initialSettings.filterSettings.input !== getFilterInput()
				) {
					settingsHasChanged = true;
					return
				}
				settingsHasChanged = false;
			}

			// General.
			function openOnCleverReach(url, data) {
				CleverReach.Ajax.post(
					url,
					data,
					function (response) {
						if (response.url) {
							const win = window.open( response.url, '_blank' );
							win.focus();
						}
					},
					'json',
					true
				);
			}

			function sendAjax(url, body = null, hasLoader = false) {
				if (hasLoader) {
					loader.classList.remove( 'cr-hidden' );
				}
				CleverReach.Ajax.post(
					url,
					body,
					function (response) {
						if (response.success === true) {
							location.reload();
						}
					},
					'json',
					true
				);
			}
		}
	);
})();
