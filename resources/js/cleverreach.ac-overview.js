/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

(function () {
	jQuery( document ).ready(
		function ($) {
			// Screen options.
			const toggleButton         = $( '#ac-screen-meta-links' ).find( '.show-settings' );
			const acSettingsButton     = $( '#show-ac-settings-link' );
			const acScreenOptionsPanel = $( '#ac-screen-meta' );

			acSettingsButton.on( 'click', toggleACScreenOptionsPanel );

			function toggleACScreenOptionsPanel() {
				if ( ! acScreenOptionsPanel.length) {
					return;
				}

				if (acScreenOptionsPanel.is( ':visible' )) {
					closeACScreenOptionsPanel()
				} else {
					openACScreenOptionsPanel()
				}
			}

			function openACScreenOptionsPanel() {
				$( '#ac-screen-meta-links' ).find( '.screen-meta-toggle' ).not( toggleButton.parent() ).css( 'visibility', 'hidden' );
				acScreenOptionsPanel.slideDown(
					'fast',
					function () {
						acScreenOptionsPanel.trigger( 'focus' );
						toggleButton.addClass( 'screen-meta-active' ).attr( 'aria-expanded', true );
						acScreenOptionsPanel.show();
					}
				);
			}

			function closeACScreenOptionsPanel() {
				acScreenOptionsPanel.slideUp(
					'fast',
					function () {
						toggleButton.removeClass( 'screen-meta-active' ).attr( 'aria-expanded', false );
						$( '#ac-screen-meta-links' ).find( '.screen-meta-toggle' ).css( 'visibility', '' );
						acScreenOptionsPanel.hide();
					}
				);
			}

			columns.init();

			const checkboxes = document.querySelectorAll( '.hide-column-tog' );

			const per_page_input = document.querySelector( '#ac_per_page' );
			const per_page_url   = document.querySelector( '#cr-ac-per-page-url' ).value;
			const apply_button   = document.querySelector( '#ac-screen-options-apply' );

			checkboxes.forEach(
				function (checkbox) {
					checkbox.addEventListener(
						'change',
						function () {
							if (this.checked) {
								columns.checked( checkbox.value );
							} else {
								columns.unchecked( checkbox.value );
							}
							columns.saveManageColumnsState();
						}
					);
				}
			);

			apply_button.addEventListener(
				'click',
				function () {
					$.post(
						per_page_url,
						{'per_page': per_page_input.value},
						function (response) {
							if (response !== false) {
								location.reload();
							}
						}
					);
				}
			);
		}
	);

	// AC records table.
	jQuery(
		function ($) {
			let from = $( 'input[name="scheduledTimeFrom"]' ),
			to       = $( 'input[name="scheduledTimeTo"]' );

			$( 'input[name="scheduledTimeFrom"], input[name="scheduledTimeTo"]' ).datepicker();

			from.on(
				'change',
				function () {
					to.datepicker( 'option', 'minDate', from.val() );
				}
			);

			to.on(
				'change',
				function () {
					from.datepicker( 'option', 'maxDate', to.val() );
				}
			);

			jQuery(
				function ($) {
					const send_now_buttons = document.querySelectorAll( '.wc-action-button-complete' );
					const delete_buttons   = document.querySelectorAll( '.wc-action-button-delete' );

					const send_now_url   = document.querySelector( '#cr-ac-send-now' ).value;
					const delete_now_url = document.querySelector( '#cr-ac-delete' ).value;

					send_now_buttons.forEach(
						function (button) {
							button && button.addEventListener(
								'click',
								function (event) {
									event.preventDefault();
									let sent = false;

									$.post(
										send_now_url,
										{'recordID': button.dataset.record_id},
										function (response) {
											if (response && response.success) {
												sent = true;
												location.reload();
											}
										}
									);
								}
							);
						}
					);

					delete_buttons.forEach(
						function (button) {
							button && button.addEventListener(
								'click',
								function (event) {
									event.preventDefault();

									$.post(
										delete_now_url,
										{'recordID': button.dataset.record_id},
										function (response) {
											if (response && response.success) {
												location.reload();
											}
										}
									);
								}
							);
						}
					);
				}
			);
		}
	);
})();
