/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

(function () {
	document.addEventListener(
		'DOMContentLoaded',
		function () {
			let buyersCheckbox   = document.getElementById( 'crBuyersCheckbox' );
			let contactsCheckbox = document.getElementById( 'crContactsCheckbox' );

			let filterTypeSelectBox = document.querySelector( '#crFilterTypeSelectBox' ),
			wildcardInput           = document.querySelector( '#crFilterTypeWildcardField' ),
			staticInput             = document.querySelector( '#crFilterTypeStaticField' );

			let importButton = document.getElementById( 'crInitialSyncSettingsImportButton' );
			let cancelButton = document.getElementById( 'crInitialSyncSettingsCancelButton' );

			let subscribersExistInSystem = document.getElementById( 'crSubscribersExist' ).value === '1';

			let saveSettingsUrl = document.getElementById( 'crSaveSettingsBeforeUrl' );
			let cancelUrl       = document.getElementById( 'crCancelSettingsBeforeUrl' );

			const loader = document.getElementById( 'crLoader' );

			setImportButtonState();
			setFilterForm();

			function setFilterForm() {
				let advancedConfigButton = document.querySelector( '#crAdvancedConfigButton' ),
				staticLabel              = document.querySelector( '#cr-static-label' ),
				wildcardLabel            = document.querySelector( '#cr-wildcard-label' );

				advancedConfigButton.addEventListener(
					'click',
					function (event) {
						if (advancedConfigButton.firstChild.classList.contains( 'cr-show-settings-active' )) {
							document.querySelector( '#cr-config-label' ).style.display = 'none';
							document.querySelector( '#cr-filter-form' ).style.display  = 'none';
							advancedConfigButton.firstChild.classList.remove( 'cr-show-settings-active' );
						} else {
							document.querySelector( '#cr-config-label' ).style.display = 'block';
							document.querySelector( '#cr-filter-form' ).style.display  = 'block';
							advancedConfigButton.firstChild.classList.add( 'cr-show-settings-active' );
						}
					}
				);

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

			function getFilterSettings() {
				let input;
				if (filterTypeSelectBox.value === 'static') {
					input = staticInput.firstElementChild.value;
				} else {
					input = wildcardInput.firstElementChild.value;
				}

				return {
					'filterType': filterTypeSelectBox.value,
					'input': input
				};
			}

			/**
			 * Disables import button if subscribers don't exist in the system and none of the other receivers is not selected
			 */
			function setImportButtonState() {
				const buyerChecked    = buyersCheckbox.checked;
				importButton.disabled = ! subscribersExistInSystem && ! buyerChecked;
			}

			function displayError(message) {
				let errorNotification       = document.getElementById( 'cr-sync-settings-notification-error' );
				errorNotification.innerHTML = '<p>' + message + '</p>';
				errorNotification.classList.remove( 'cr-hidden' );
			}

			if (importButton) {
				importButton.addEventListener(
					'click',
					function () {
						importButton.disabled = true;
						cancelButton.disabled = true;

						const selectedOptions = ['subscribers'];

						if (buyersCheckbox.checked) {
							selectedOptions.push( 'buyers' );
						}

						if (contactsCheckbox.checked) {
							selectedOptions.push( 'contacts' );
						}

						const body = {
							'syncSettings': selectedOptions,
							'filterSettings': JSON.stringify( getFilterSettings() )
						};

						loader.classList.remove( 'cr-hidden' );
						CleverReach.Ajax.post(
							saveSettingsUrl.value,
							body,
							function (response) {
								if (response.success) {
									location.reload();
								} else {
									displayError( response.message );
									importButton.disabled = false;
									cancelButton.disabled = false;
									loader.classList.add( 'cr-hidden' );
								}
							},
							'json',
							true
						);
					}
				);
			}

			if (cancelButton) {
				cancelButton.addEventListener(
					'click',
					function () {
						importButton.disabled = true;
						cancelButton.disabled = true;
						loader.classList.remove( 'cr-hidden' );
						CleverReach.Ajax.post(
							cancelUrl.value,
							null,
							function (response) {
								if (response.success) {
									location.reload();
								}
							},
							'json',
							true
						);
					}
				);
			}

			if (buyersCheckbox) {
				buyersCheckbox.addEventListener(
					'change',
					function () {
						if ( ! buyersCheckbox.checked) {
							contactsCheckbox.checked = false;
						}
						setImportButtonState();
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
						setImportButtonState();
					}
				);
			}
		}
	);
})();
