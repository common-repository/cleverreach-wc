<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

use CleverReach\WooCommerce\ViewModel\Dashboard\Dashboard_Config;

$dashboard_config      = Dashboard_Config::get_dashboard_config();
$deep_links            = Dashboard_Config::get_deep_links();
$settings_config       = Dashboard_Config::get_settings_config();
$abandoned_cart_config = Dashboard_Config::get_abandoned_cart_config();

$import_statistics_model        = new CleverReach\WooCommerce\ViewModel\Dashboard\Import_Statistics();
$initial_sync_state             = new CleverReach\WooCommerce\ViewModel\Dashboard\Initial_Sync_State();
$receiver_statistics_model      = new CleverReach\WooCommerce\ViewModel\Dashboard\Receiver_Statistics();
$user_info_model                = new CleverReach\WooCommerce\ViewModel\Dashboard\User_Info();
$payment_plan_model             = new CleverReach\WooCommerce\ViewModel\Dashboard\Payment_Plan();
$newsletter_settings_view_model = new CleverReach\WooCommerce\ViewModel\Settings\Newsletter_Settings();
$abandoned_cart_view_model      = new CleverReach\WooCommerce\ViewModel\Dashboard\Abandoned_Cart();
$newsletter_checkbox_delay      = $newsletter_settings_view_model->get_display_time_of_newsletter_checkbox();

?>
<input id="cr-confirm-translation" type="hidden" value="
<?php
echo esc_attr( __( 'Confirm', 'cleverreach-wc' ) );
?>
">
<input id="cr-cancel-translation" type="hidden" value="
<?php
echo esc_attr( __( 'Cancel', 'cleverreach-wc' ) );
?>
">
<div class="wrap cr-container">

	<div id="tabs-container">
		<nav id="nav-tabs" class="nav-tab-wrapper">
			<ul>
				<li id="cr-tab-dashboard">
					<a style="box-shadow:none;"
						href="#cr-tab-dashboard-container" class="nav-tab">
						<?php
						echo esc_html(
							__(
								'Dashboard',
								'cleverreach-wc'
							)
						)
						?>
						</a>
				</li>
				<li id="cr-tab-settings">
					<a style="box-shadow:none;"
						href="#cr-tab-settings-container" class="nav-tab
						<?php
						if ( $initial_sync_state->is_initial_sync_in_progress() ) {
							echo 'cr-disabled';
						}
						?>
						"
					>
					<?php
						echo esc_html( __( 'Settings', 'cleverreach-wc' ) )
					?>
					</a>
				</li>
				<li id="cr-tab-ac">
					<a style="box-shadow:none;" href="#cr-tab-ac-container"
						class="nav-tab
						<?php
						if ( $initial_sync_state->is_initial_sync_in_progress() ) {
							echo 'cr-disabled';
						}
						?>
						"
					>
					<?php
						echo esc_html(
							__(
								'Abandoned cart',
								'cleverreach-wc'
							)
						)
						?>
						</a>
				</li>
			</ul>
		</nav>

		<!-- DASHBOARD tab content -->
		<div id="cr-tab-dashboard-container">
			<input type="hidden" id="crHelpUrl" value="
			<?php
			echo esc_url( $dashboard_config['helpUrl'] )
			?>
			">

			<input type="hidden" id="crUninstallUrl" value="
			<?php
			echo esc_url( $dashboard_config['uninstallUrl'] )
			?>
			">
			<input type="hidden" id="crRetrySyncUrl" value="
			<?php
			echo esc_url( $dashboard_config['retrySyncUrl'] )
			?>
			">
			<input type="hidden" id="crRedirectUrl" value="
			<?php
			echo esc_url( $dashboard_config['redirectUrl'] )
			?>
			">

			<input type="hidden" id="crDisplayParamsUrl" value="
			<?php
			echo esc_url( $dashboard_config['displaySupportParamsUrl'] )
			?>
			">
			<input type="hidden" id="crUpdateParamsUrl" value="
			<?php
			echo esc_url( $dashboard_config['updateSupportParamsUrl'] )
			?>
			">

			<input type="hidden" id="crAdminStatusCheckUrl" value="
			<?php
			echo esc_url( $dashboard_config['statusCheckUrl'] )
			?>
			">

			<div class="cr-tab-dashboard">
				<h2 style="display: none">Dashboard</h2>
				<?php
				if ( $initial_sync_state->is_initial_sync_in_progress() ) {
					?>
					<div class="cr-notice cr-info cr-import-running"
						id="crProgressPanel">
						<p class="title">
						<?php
							echo esc_html(
								__(
									'Account setup is running in the background',
									'cleverreach-wc'
								)
							)
						?>
						</p>
						<ol>
							<li id="subscriberlist">
							<?php
								echo esc_html(
									__(
										'Creating recipient list and form',
										'cleverreach-wc'
									)
								)
							?>
							</li>
							<li id="add_fields">
							<?php
								echo esc_html(
									__(
										'Adding data fields, segments and tags',
										'cleverreach-wc'
									)
								)
							?>
							</li>
							<li id="recipient_sync">
							<?php
								echo esc_html(
									__(
										'Importing recipients',
										'cleverreach-wc'
									)
								)
							?>
							</li>
						</ol>
						<div class="fc fc-jc-sb cr-import-progress">
							<progress id="crProgress" max="100"
										value="0"></progress>
							<div>
								<span id="crProgressText">0</span> / 100%
							</div>
						</div>
					</div>
					<?php
				}
				?>
				<?php
				if ( $initial_sync_state->is_initial_sync_failed() ) {
					?>
					<div class="cr-notice cr-error cr-err-account-in-sync">
						<p class="title">
						<?php
							echo esc_html(
								__(
									'An Error occured during synchronization',
									'cleverreach-wc'
								)
							)
						?>
						</p>
						<p>
							<?php
							echo esc_html(
								__(
									'You can retry now. If the problem occurs again please ',
									'cleverreach-wc'
								)
							)
							?>
							<a href="#">
							<?php
								echo esc_html( __( 'create a support ticket ', 'cleverreach-wc' ) )
							?>
							</a>
							<?php
							echo esc_html( __( 'at the CleverReach® helpcenter.', 'cleverreach-wc' ) )
							?>
						</p>
						<p>
							<?php
							echo esc_html( __( 'Error description: ', 'cleverreach-wc' ) )
							?>
						</p>
						<p>
							<?php
							echo esc_html( __( $initial_sync_state->get_failure_description() ) ) // phpcs:ignore
							?>
						</p>
						<button id="crRetrySync" class="button cr-secondary">
							<?php
							echo esc_html( __( 'Retry Synchronization', 'cleverreach-wc' ) )
							?>
							</button>
					</div>
					<?php
				}
				?>
				<?php
				if ( $initial_sync_state->should_display_import_stats() ) {
					?>
					<div class="is-dismissible cr-notice cr-success cr-import-successful">
						<p class="title">
						<?php
							echo esc_html( __( 'Setup was successful', 'cleverreach-wc' ) )
						?>
						</p>
						<p>
							<?php
							echo esc_html(
								__(
									'Synchronized recipients: ',
									'cleverreach-wc'
								) . $import_statistics_model->get_number_of_synced_recipients()
							)
							?>
						</p>
						<p>
							<?php
							echo esc_html(
								__(
									'Created recipient list: ',
									'cleverreach-wc'
								) . $import_statistics_model->get_group_name()
							)
							?>
						</p>
						<p>
							<?php
							echo esc_html(
								__(
									'Created segments: ',
									'cleverreach-wc'
								) . $import_statistics_model->get_segments()
							)
							?>
						</p>
					</div>
					<?php
				}
				?>
				<div class="fc fc-wrap fc-jc-sb cr-dashboard-links">
					<div class="cr-link-item" id="crBuildEmail" data-url="
					<?php
					echo esc_url( $deep_links['createNewsletterUrl'] )
					?>
					">
						<span class="dashicons dashicons-email"></span>
						<div>
							<a>
							<?php
								echo esc_html( __( 'Create email', 'cleverreach-wc' ) )
							?>
							</a>
							<p>
							<?php
								echo esc_html( __( 'Create your next newsletter.', 'cleverreach-wc' ) )
							?>
							</p>
						</div>
					</div>
					<div class="cr-link-item" id="crReports" data-url="
					<?php
					echo esc_url( $deep_links['reportsUrl'] )
					?>
					">
						<span class="dashicons dashicons-chart-bar"></span>
						<div>
							<a>
							<?php
								echo esc_html( __( 'Reports', 'cleverreach-wc' ) )
							?>
							</a>
							<p>
							<?php
								echo esc_html( __( 'Analyze your newsletter campaigns.', 'cleverreach-wc' ) )
							?>
							</p>
						</div>
					</div>
					<div class="cr-link-item" id="crForms" data-url="
					<?php
					echo esc_url( $deep_links['formsUrl'] )
					?>
					">
						<span class="dashicons dashicons-welcome-add-page"></span>
						<div>
							<a>
							<?php
								echo esc_html( __( 'Forms', 'cleverreach-wc' ) )
							?>
							</a>
							<p>
							<?php
								echo esc_html( __( 'Create and edit forms.', 'cleverreach-wc' ) )
							?>
							</p>
						</div>
					</div>
					<div class="cr-link-item" id="crThea" data-url="
					<?php
					echo esc_url( $deep_links['theaUrl'] )
					?>
					">
						<span class="dashicons dashicons-share-alt2"></span>
						<div>
							<a>
							<?php
								echo esc_html( __( 'Automation THEA', 'cleverreach-wc' ) )
							?>
							</a>
							<p>
							<?php
								echo esc_html( __( 'Create automated email campaigns.', 'cleverreach-wc' ) )
							?>
							</p>
						</div>
					</div>
					<div class="cr-link-item" id="crEmails" data-url="
					<?php
					echo esc_url( $deep_links['emailsUrl'] )
					?>
					">
						<span class="dashicons dashicons-editor-ul"></span>
						<div>
							<a>
							<?php
								echo esc_html( __( 'Emails', 'cleverreach-wc' ) )
							?>
							</a>
							<p>
							<?php
								echo esc_html( __( 'Overview of your emails.', 'cleverreach-wc' ) )
							?>
							</p>
						</div>
					</div>
					<div class="cr-link-item" id="crPush" data-url="
					<?php
					echo esc_url( __( 'https://www.cleverreach.com/en/tag/onlineshops/', 'cleverreach-wc' ) )
					?>
					">
						<span class="dashicons dashicons-welcome-learn-more"></span>
						<div>
							<a>
							<?php
								echo esc_html( __( 'PUSH///', 'cleverreach-wc' ) )
							?>
							</a>
							<p>
							<?php
								echo esc_html( __( 'Tips and tricks from our blog.', 'cleverreach-wc' ) )
							?>
							</p>
						</div>
					</div>
				</div>
				<div class="fc fc-wrap cr-dashboard-content">
					<div class="fc fc-jc-sb cr-info-header">
						<h3>
						<?php
							echo esc_html( __( 'Your CleverReach-Account', 'cleverreach-wc' ) )
						?>
						</h3>
						<div class="cr-account-info">
							<p>
							<?php
								echo esc_html(
									__(
										'E-Mail:',
										'cleverreach-wc'
									)
								) . ' ' . esc_html( __( $user_info_model->get_email() ) ) // phpcs:ignore
								?>
								</p>
							<p>
							<?php
								echo esc_html(
									__(
										'Account ID:',
										'cleverreach-wc'
									)
								) . ' ' . esc_html( __( $user_info_model->get_id() ) ) // phpcs:ignore
								?>
								</p>
						</div>
						<div class="fc cr-account-actions">
							<button id="crDisconnect"
									class="button cr-secondary"
									id="crDisconnect">
									<?php
									echo esc_html( __( 'Disconnect', 'cleverreach-wc' ) )
									?>
									</button>
							<button id="crHelp" class="button cr-secondary"
									id="crHelp">
									<?php
									echo esc_html( __( 'Help & Support', 'cleverreach-wc' ) )
									?>
									</button>
						</div>
					</div>
					<?php
					if ( $initial_sync_state->should_display_statistics() ) {
						?>
						<div class="fc fc-jc-sb cr-sync-info">
							<div class="cr-sync-status-container">
								<h3>
								<?php
									echo esc_html( __( 'Current Sync Status', 'cleverreach-wc' ) )
								?>
								</h3>
								<p>
									<?php
									echo esc_html( __( 'Last synchronization:', 'cleverreach-wc' ) )
									?>
									<span>
									<?php
										echo esc_html( $receiver_statistics_model->get_last_sync_time() )
									?>
									</span>
								</p>
								<p>
									<?php
									echo esc_html( __( 'Your current rate:', 'cleverreach-wc' ) )
									?>
									<span>
									<?php
										echo esc_html( $payment_plan_model->get_current_rate( $user_info_model->get_id() ) )
									?>
									</span>
								</p>
								<a href="#" id="crPricePlan" data-url="
								<?php
								echo esc_url( $deep_links['pricePlanUrl'] )
								?>
								"
									class="button cr-secondary">
									<?php
									echo esc_html(
										__(
											'Upgrade Plan',
											'cleverreach-wc'
										)
									)
									?>
									</a>
							</div>
							<div class="fc fc-jc-sb cr-sync-statistics-container">
								<div class="cr-ss-box cr-recipients">
									<h4>
									<?php
										echo esc_html( __( 'Recipients', 'cleverreach-wc' ) )
									?>
									</h4>
									<strong>
									<?php
										echo esc_html( $receiver_statistics_model->get_customers() )
									?>
									</strong>
								</div>
								<div class="cr-ss-box cr-subscribed">
									<h4>
										<?php
										echo esc_html( __( 'Subscribed', 'cleverreach-wc' ) )
										?>
										<span>
										<?php
											echo esc_html( __( '(Last 30 days)', 'cleverreach-wc' ) )
										?>
										</span>
									</h4>
									<strong>
									<?php
										echo esc_html( $receiver_statistics_model->get_subscribed() )
									?>
									</strong>
								</div>
								<div class="cr-ss-box cr-unsubscribed">
									<h4>
										<?php
										echo esc_html( __( 'Unsubscribed', 'cleverreach-wc' ) )
										?>
										<span>
										<?php
											echo esc_html( __( '(Last 30 days)', 'cleverreach-wc' ) )
										?>
										</span>
									</h4>
									<strong>
									<?php
										echo esc_html( $receiver_statistics_model->get_unsubscribed() )
									?>
									</strong>
								</div>
							</div>
						</div>
						<?php
					}
					?>
				</div>
			</div>

			<div id="dialog"
				title="
				<?php
					echo esc_attr( __( 'You are about to disconnect your CleverReach® account!', 'cleverreach-wc' ) )
				?>
					">
				<p>
				<?php
					echo esc_html(
						__(
							'If your account is disconnected no synchronization between WooCommerce and CleverReach® is
                    possible!',
							'cleverreach-wc'
						)
					)
					?>
					</p>
			</div>
		</div>

		<!-- SETTINGS tab content -->
		<div id="cr-tab-settings-container">

			<input type="hidden" id="loaderText"
					value="
					<?php
					echo esc_attr(
						__(
							'Data will be imported in the background. You can proceed with your action.',
							'cleverreach-wc'
						)
					)
					?>
					">

			<input type="hidden" id="crIsUserDataComplete"
					value="<?php echo esc_attr( $newsletter_settings_view_model->is_user_data_complete() ? '1' : '0' ); ?>">
			<input type="hidden" id="crSaveSyncSettingsUrl" value="
			<?php
			echo esc_url( $settings_config['saveSyncSettingsUrl'] )
			?>
			">
			<input type="hidden" id="crSaveNewsletterSettingsUrl"
					value="
					<?php
					echo esc_url( $settings_config['saveNewsletterSettingsUrl'] )
					?>
					">
			<input type="hidden" id="crFetchSettingsUrl" value="
			<?php
			echo esc_url( $settings_config['fetchSettingsUrl'] )
			?>
			">
			<input type="hidden" id="crReforceSyncUrl" value="
			<?php
			echo esc_url( $settings_config['retrySecondarySyncUrl'] )
			?>
			"/>
			<input type="hidden" id="crCheckSecondarySyncStatusUrl"
					value="
					<?php
					echo esc_url( $settings_config['checkSecondarySyncUrl'] )
					?>
					"/>
			<input type="hidden" id="crGetIntervalSettingsUrl"
					value="
					<?php
					echo esc_url( $settings_config['getIntervalUrl'] )
					?>
			"/>

			<div id="backgroundSyncPanel" class="cr-notice cr-info cr-hidden">
				<p class="title">
				<?php
					echo esc_html( __( 'Secondary synchronization in progress.', 'cleverreach-wc' ) )
				?>
				</p>
				<p>
					<?php
					echo esc_html(
						__(
							'Receivers will be synced in the background. You can proceed with your action.',
							'cleverreach-wc'
						)
					)
					?>
				</p>
			</div>

			<div id="syncErrorPanel"
				class="cr-notice cr-error cr-err-account-in-sync cr-hidden">
				<p class="title">
				<?php
					echo esc_html( __( 'Secondary synchronization failed.', 'cleverreach-wc' ) )
				?>
				</p>
				<p>
					<?php
					echo esc_html( __( 'Something went wrong. Please try again.', 'cleverreach-wc' ) )
					?>
				</p>
			</div>

			<div class="cr-tab-settings">
				<h2>
				<?php
					echo esc_html( __( 'Synchronization Settings', 'cleverreach-wc' ) )
				?>
				</h2>
				<p>
					<?php
					echo esc_html(
						__(
							'The following recipient list will be synced. Observe the guidelines of the General Data Protection Regulation.',
							'cleverreach-wc'
						)
					)
					?>
				</p>
				<a href="
				<?php
				echo esc_url(
					__(
						'https://support.cleverreach.de/hc/en-us/articles/360013571280',
						'cleverreach-wc'
					)
				)
				?>
				">
					<?php
					echo esc_html( __( 'Learn more about data protection.', 'cleverreach-wc' ) )
					?>
					</a>
				<p>
					<?php
					echo esc_html(
						__(
							'All changes will be applied on future synchronizations. No data will be deleted on CleverReach.',
							'cleverreach-wc'
						)
					)
					?>
				</p>
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row">
							<?php
							echo esc_html( __( 'Send emails to', 'cleverreach-wc' ) )
							?>
						</th>
						<td class="forminp forminp-text">
							<fieldset>
								<legend class="screen-reader-text">
									<span>checkbox</span>
								</legend>
								<label for="cr-subscriber">
									<input name="cr-subscriber" type="checkbox"
											id="crSubscribersCheckbox" checked
											disabled
											readonly value="1">
									<?php
									echo esc_html( __( 'Subscriber', 'cleverreach-wc' ) )
									?>
								</label>
								<p class="description">
									<?php
									echo esc_html( __( 'Subscribed to your newsletter.', 'cleverreach-wc' ) )
									?>
								</p>
							</fieldset>
							<fieldset>
								<legend class="screen-reader-text">
									<span>checkbox</span>
								</legend>
								<label for="cr-buyer">
									<input name="cr-buyer" type="checkbox"
											id="crBuyersCheckbox" value="1">
									<?php
									echo esc_html( __( 'Buyer', 'cleverreach-wc' ) )
									?>
								</label>
								<p class="description">
									<?php
									echo esc_html( __( 'Bought something from your store.', 'cleverreach-wc' ) )
									?>
								</p>
							</fieldset>
							<fieldset>
								<legend class="screen-reader-text">
									<span>checkbox</span>
								</legend>
								<label for="cr-contact">
									<input name="cr-contact" type="checkbox"
											id="crContactsCheckbox" value="1">
									<?php
									echo esc_html( __( 'Contacts', 'cleverreach-wc' ) )
									?>
								</label>
								<p class="description">
									<?php
									echo esc_html( __( 'Didn\'t subscribe or buy.', 'cleverreach-wc' ) )
									?>
								</p>
							</fieldset>
						</td>
					</tr>
					</tbody>
				</table>
				<h2>
					<?php
					echo esc_html( __( 'Synchronization interval', 'cleverreach-wc' ) )
					?>
				</h2>
					<p class="cr-interval-description">
						<?php
						echo esc_html(
							__(
								'You can choose the synchronization interval between immediate and a maximum of 24 hours.',
								'cleverreach-wc'
							)
						)
						?>
					</p>
					<p class="cr-interval-description">
						<?php
						echo esc_html(
							__(
								'The default setting is always immediate to ensure the live synchronization between your system ',
								'cleverreach-wc'
							)
						)
						?>
						<br/>
						<?php
						echo esc_html(
							__(
								'and CleverReach. Changing the value means that the live sync will be done in the selected time frame. ',
								'cleverreach-wc'
							)
						)
						?>
						<br/>
						<?php
						echo esc_html( __( 'Note: Automation emails with triggers are also delayed in that case.', 'cleverreach-wc' ) )
						?>
					</p>
					<p  class="cr-interval-description">
						<a href="
						<?php
							echo esc_url(
								__(
									'https://support.cleverreach.de/hc/en-us/articles/17498752175378',
									'cleverreach-wc'
								)
							)
							?>
						"  class="cr-interval-description">
							<?php
							echo esc_html( __( 'Learn more about the sync interval settings', 'cleverreach-wc' ) )
							?>
						</a>
					</p>
					<p>
						<?php
						echo esc_html(
							__(
								'Your opinion matters: Please take part in our short ',
								'cleverreach-wc'
							)
						)
						?>
						<a href="
						<?php
						echo esc_url(
							__(
								'https://de.surveymonkey.com/r/5S2TSB3?lang=en',
								'cleverreach-wc'
							)
						)
						?>
						">
							<?php
							echo esc_html( __( 'customer survey', 'cleverreach-wc' ) )
							?>
						</a>
					</p>
				<div class="cr-interval-card">
					<fieldset>
						<label for="crInterval"></label>
						<select name="crInterval" id="crInterval" class="cr-interval-select">
							<option value="immediate">
								<?php
								echo esc_html( __( 'Immediate', 'cleverreach-wc' ) )
								?>
							</option>
							<option value="one_minute">
								<?php
								echo esc_html( __( '1 minute', 'cleverreach-wc' ) )
								?>
							</option>
							<option value="five_minutes">
								<?php
								echo esc_html( __( '5 minutes', 'cleverreach-wc' ) )
								?>
							</option>
							<option value="fifteen_minutes">
								<?php
								echo esc_html( __( '15 minutes', 'cleverreach-wc' ) )
								?>
							</option>
							<option value="one_hour">
								<?php
								echo esc_html( __( '1 hour', 'cleverreach-wc' ) )
								?>
							</option>
							<option value="four_hours">
								<?php
								echo esc_html( __( '4 hours', 'cleverreach-wc' ) )
								?>
							</option>
							<option value="twelve_hours">
								<?php
								echo esc_html( __( '12 hours', 'cleverreach-wc' ) )
								?>
							</option>
							<option value="daily">
								<?php
								echo esc_html( __( 'Daily', 'cleverreach-wc' ) )
								?>
							</option>
							<option value="custom">
								<?php
								echo esc_html( __( 'Custom', 'cleverreach-wc' ) )
								?>
							</option>
						</select>
					</fieldset>
					<input id="crIntervalTime" type="time" class="cr-interval-time" value="12:00"/>
					<input id="crCustomInterval" type="number" min="0" max="1440" step="1" class="cr-interval-time"
							value="0"/>
					<label for="crIntervalTime"></label>
					<label for="crCustomInterval"></label>
				</div>
				<h2>
				<?php
					echo esc_html( __( 'Configure email filter', 'cleverreach-wc' ) )
				?>
				</h2>
				<p>
					<?php
					echo esc_html( __( 'Configure filter that excludes emails from being synchronized.', 'cleverreach-wc' ) )
					?>
				</p> <a href="<?php echo esc_html( __( 'https://support.cleverreach.de/hc/en-us/articles/14920866742418', 'cleverreach-wc' ) ); ?>" target="_blank"><?php echo esc_html( __( 'Learn more about email blocklist filter.', 'cleverreach-wc' ) ); ?></a>
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row">
							<label for="crFilterTypeSelectBox">
							<?php
								echo esc_html( __( 'Filter type', 'cleverreach-wc' ) )
							?>
							</label>
						</th>
						<td>
							<fieldset>
								<select name="crFilterTypeSelectBox" id="crFilterTypeSelectBox">
									<option value="static">
									<?php
										echo esc_html( __( 'Static', 'cleverreach-wc' ) )
									?>
									</option>
									<option value="wildcard">
									<?php
										echo esc_html( __( 'Wildcard', 'cleverreach-wc' ) )
									?>
									</option>
								</select>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<div id="cr-static-label">
								<label for="crFilterTypeStatic">
								<?php
									echo esc_html( __( 'Emails', 'cleverreach-wc' ) )
								?>
								</label>
								<div class="woocommerce-help-tip cr-tooltip">
								<span class="cr-tooltip-text">
								<?php
									echo esc_html( __( 'Enter one or more email addresses separated by commas (max. 500 email addresses).', 'cleverreach-wc' ) )
								?>
								</span>
								</div>
							</div>
							<div id="cr-wildcard-label" style="display: none;">
								<label for="crFilterTypeStatic">
								<?php
									echo esc_html( __( 'Pattern', 'cleverreach-wc' ) )
								?>
								</label>
								<div class="woocommerce-help-tip cr-tooltip">
								<span class="cr-tooltip-text">
								<?php
									echo esc_html( __( 'Use * for any sequence of characters and ? for any single character. E.g. *example.com blocks all emails from example.com; john?@example.com blocks john1@example.com, not john12@example.com.', 'cleverreach-wc' ) )
								?>
								</span>
								</div>
							</div>
						</th>
						<td>
							<fieldset id="crFilterTypeStaticField">
								<textarea rows="5" name="crFilterTypeStatic" id="crFilterTypeStatic"
											placeholder="john.doe@example.com,jane.doe@example.com"></textarea>
							</fieldset>
							<fieldset id="crFilterTypeWildcardField" style="display: none;">
								<input name="crFilterTypeWildcard" id="crFilterTypeWildcard" placeholder="*@example.com">
							</fieldset>
						</td>
					</tr>
					</tbody>
				</table>
				<button id="crInitialSyncSettingsReforceSyncButton"
						class="button cr-secondary">
					<span class="spinner"></span>
					<?php
					echo esc_html( __( 'Reforce sync', 'cleverreach-wc' ) )
					?>
				</button>
				<hr>
				<h2>
				<?php
					echo esc_html( __( 'Newsletter Sign-Up', 'cleverreach-wc' ) )
				?>
				</h2>


				<div id="crUserInfoIncompletePanel" class="cr-notice cr-error
			<?php
				echo esc_attr( $newsletter_settings_view_model->is_user_data_complete() ? 'cr-hidden' : '' )
			?>
			">
					<p class="title">
					<?php
						echo esc_html( __( 'DOI unavailable.', 'cleverreach-wc' ) )
					?>
					</p>
					<p>
						<?php
						echo esc_html(
							__(
								'Double opt-in can not be activated since your CleverReach® account is not verified yet. Please contact our support.',
								'cleverreach-wc'
							)
						)
						?>
					</p>
				</div>

				<p>
					<?php
					echo esc_html(
						__(
							'To sign up for your newsletter, you have to enable the newsletter checkbox. You can decide to use the double opt-in feature.',
							'cleverreach-wc'
						)
					)
					?>
				</p>
				<a href="
				<?php
				echo esc_html(
					__(
						'https://support.cleverreach.de/hc/en-us/articles/360018833540',
						'cleverreach-wc'
					)
				)
				?>
				">
					<?php
					echo esc_html( __( 'Learn how to use the newsletter checkbox.', 'cleverreach-wc' ) )
					?>
					</a>
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row">
							<?php
							echo esc_html( __( 'Newsletter checkbox', 'cleverreach-wc' ) )
							?>
						</th>
						<td class="forminp forminp-text">
							<fieldset>
								<legend class="screen-reader-text">
									<span>checkbox</span>
								</legend>
								<label for="crNewsletterEnabled">
									<input name="cr-newsletter" type="checkbox"
											id="crNewsletterEnabled"
											<?php
											echo esc_attr( $newsletter_settings_view_model->is_newsletter_checkbox_enabled() ? 'checked' : '' );
											?>
											>
									<?php
									echo esc_html( __( 'Enabled', 'cleverreach-wc' ) )
									?>
								</label>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php
							echo esc_html( __( 'Newsletter checkbox caption', 'cleverreach-wc' ) )
							?>
						</th>
						<td class="forminp forminp-text">
							<input name="cr-newsletter-caption" type="text"
									id="crNewsletterCaption"
									class="regular-text"
									placeholder="<?php	// phpcs:ignore;
									echo esc_html(
										__(
											'Sign up for our newsletter!',
											'cleverreach-wc'
										)
									)
									// phpcs:ignore;
									?>"
									value="<?php	// phpcs:ignore;
									echo $newsletter_settings_view_model->get_subscribe_for_newsletter_caption() ?
										esc_html( __( $newsletter_settings_view_model->get_subscribe_for_newsletter_caption(), 'cleverreach-wc' ) ) : esc_html( __( 'Sign up for our newsletter', 'cleverreach-wc' ) ) // phpcs:ignore
									// phpcs:ignore;
									?>">
						</td>
					</tr>
					<tr id="crNewsletterConfirmationRaw" <?php echo $abandoned_cart_view_model->is_ac_function_enabled() ? '' : 'class="cr-hidden"'; ?>>
						<th scope="row">
							<?php
							echo esc_html( __( 'Newsletter checkbox', 'cleverreach-wc' ) )
							?>
							<br>
							<?php
							echo esc_html( __( 'message caption', 'cleverreach-wc' ) )
							?>
						</th>
						<td class="forminp forminp-text">
							<input name="cr-newsletter-confirmation-message"
									type="text"
									id="crNewsletterConfirmationMessage"
									class="regular-text"
									placeholder="<?php	// phpcs:ignore;
									echo esc_attr(
										__(
											'You have successfully subscribed',
											'cleverreach-wc'
										)
									)
									// phpcs:ignore;
									?>"
									value="<?php	// phpcs:ignore;
									echo $newsletter_settings_view_model->get_newsletter_subscription_confirmation_message() ?
										esc_html( __( $newsletter_settings_view_model->get_newsletter_subscription_confirmation_message(), 'cleverreach-wc' ) ) : esc_html( __( 'You have successfully subscribed', 'cleverreach-wc' ) ) // phpcs:ignore
									// phpcs:ignore;
									?>">
						</td>
					</tr>
					<tr id="crNewsletterTimeRaw" <?php echo $abandoned_cart_view_model->is_ac_function_enabled() ? '' : 'class="cr-hidden"'; ?>>
						<th scope="row">
							<?php
							echo esc_html( __( 'Display time of newsletter checkbox caption', 'cleverreach-wc' ) )
							?>
						</th>
						<td class="forminp forminp-text">
							<select name="cr-display-time-select"
									id="crDisplayTime">
								<option value="5" <?php echo 5 === $newsletter_checkbox_delay ? 'selected="true"' : ''; ?>>
									<?php echo esc_html( __( '5 seconds', 'cleverreach-wc' ) ); ?>
								</option>
								<option value="10" <?php echo 10 === $newsletter_checkbox_delay ? 'selected="true"' : ''; ?>>
									<?php echo esc_html( __( '10 seconds', 'cleverreach-wc' ) ); ?>
								</option>
								<option value="30" <?php echo 30 === $newsletter_checkbox_delay ? 'selected="true"' : ''; ?>>
									<?php echo esc_html( __( '30 seconds', 'cleverreach-wc' ) ); ?>
								</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php
							echo esc_html( __( 'Double opt-in feature', 'cleverreach-wc' ) )
							?>
						</th>
						<td class="forminp forminp-text">
							<fieldset>
								<legend class="screen-reader-text">
									<span>checkbox</span>
								</legend>
								<label for="crDoiEnabled">
									<input name="cr-doi-checkbox"
											type="checkbox" id="crDoiEnabled"
										<?php
										echo esc_attr( $newsletter_settings_view_model->is_doi_enabled() ? 'checked' : '' );
										?>
										>
									<?php
									echo esc_html( __( 'Enabled', 'cleverreach-wc' ) )
									?>
								</label>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php
							echo esc_html( __( 'Default form', 'cleverreach-wc' ) )
							?>
						</th>
						<td class="forminp forminp-text">
							<select name="cr-form-select" id="crDefaultForm">
								<?php
								foreach ( $newsletter_settings_view_model->get_forms() as $form ) {
									?>
									<option value="
									<?php
									echo esc_attr( $form->getApiId() )
									?>
									"
										<?php
										echo esc_attr( $newsletter_settings_view_model->get_default_form_id() == $form->getApiId() ? 'selected="true"' : '' )
										?>
										>
										<?php
										echo esc_html( $form->getName() )
										?>
									</option>
									<?php
								}
								?>
							</select>
						</td>
					</tr>
					</tbody>
				</table>
				<hr>
				<button id="crSettingsSaveButton"
						class="button button-primary">
					<span class="spinner"></span>
					<?php
					echo esc_html( __( 'Save changes', 'cleverreach-wc' ) )
					?>
				</button>
			</div>
		</div>

		<!-- ABANDONED CART tab content -->
		<div id="cr-tab-ac-container">

			<input type="hidden" id="crActivateACUrl" value="
			<?php
			echo esc_attr( $abandoned_cart_config['activateACUrl'] )
			?>
			">
			<input type="hidden" id="crDeactivateACUrl" value="
			<?php
			echo esc_attr( $abandoned_cart_config['deactivateACUrl'] )
			?>
			">
			<input type="hidden" id="crUpdateACUrl" value="
			<?php
			echo esc_attr( $abandoned_cart_config['updateACUrl'] )
			?>
			">
			<input type="hidden" id="crFetchSyncSettingsUrl" value="
			<?php
			echo esc_attr( $abandoned_cart_config['fetchSettingsUrl'] )
			?>
			">
			<input type="hidden" id="crAbandonedCartEditEmailUrl" value="<?php	// phpcs:ignore;
			echo esc_attr( $abandoned_cart_config['editEmailUrl'] )
			// phpcs:ignore;
			?>">
			<input type="hidden" id="crTheaStatusCheckUrl" value="
			<?php
			echo esc_attr( $abandoned_cart_config['theaStatusCheckUrl'] )
			?>
			">
			<input type="hidden" id="crAutomationStatusCheckUrl" value="
			<?php
			echo esc_attr( $abandoned_cart_config['automationStatusCheckUrl'] )
			?>
			">

			<input type="hidden" id="acDeactivateFailed" value="
			<?php
			echo esc_html( __( 'Deactivating Abandoned Cart feature failed.', 'cleverreach-wc' ) )
			?>
			">
			<input type="hidden" id="acActivateFailed" value="
			<?php
			echo esc_html( __( 'Activating Abandoned Cart feature failed.', 'cleverreach-wc' ) )
			?>
			">
			<input type="hidden" id="acChangeTimingFailed" value="
			<?php
			echo esc_html( __( 'Changing Abandoned Cart timing settings failed.', 'cleverreach-wc' ) )
			?>
			">

			<div id="crACErrorPanel" style="max-width: 100%"
				class="cr-notice cr-error hidden">
				<button id='crDismissACError' type='button'
						class='notice-dismiss cr-ac-notice-dismiss'>
					<span class='screen-reader-text'>
					<?php
						esc_html( __( 'Dismiss this notice.', 'cleverreach-wc' ) )
					?>
					</span>
				</button>
				<p id="crACErrorTitle" class="title">
					<?php
					echo esc_html( __( 'Activating AC feature failed.', 'cleverreach-wc' ) )
					?>
					</p>
				<p>
					<?php
					echo esc_html( __( 'Something went wrong. Please try again.', 'cleverreach-wc' ) )
					?>
				</p>
				<p>
					<?php
					echo esc_html( __( $initial_sync_state->get_failure_description(), 'cleverreach-wc' ) ) // phpcs:ignore
					?>
				</p>
			</div>

			<div id="activationErrorPanel"
				class="cr-notice cr-error cr-err-abandoned-cart cr-hidden">
				<p class="title">
				<?php
					echo esc_html( __( 'Abandoned cart activation failed.', 'cleverreach-wc' ) )
				?>
				</p>
				<p>
					<?php
					echo esc_html( __( 'Something went wrong. Please try again.', 'cleverreach-wc' ) )
					?>
				</p>
			</div>

			<div class="cr-tab-abandoned-cart">

				<h2>
				<?php
					echo esc_html( __( 'Abandoned cart', 'cleverreach-wc' ) )
				?>
				</h2>
				<p>
					<?php
					echo esc_html(
						__(
							'Send automated emails to customers, who abandoned their checkout.',
							'cleverreach-wc'
						)
					)
					?>
				</p>
				<p id="crAcReportsTabLink">
					<?php
					echo esc_html(
						__(
							'You will find an overview in the menu at WooCommerce > Reports > ',
							'cleverreach-wc'
						)
					)
					?>
					<a href="
					<?php
					echo esc_html( add_query_arg( array( 'tab' => 'cr-abandoned-cart' ), menu_page_url( 'wc-reports', false ) ) );
					?>
					">
					<?php
						echo esc_html( __( 'CleverReach - Abandoned Carts', 'cleverreach-wc' ) )
					?>
					</a>.
				</p>
				<button id="crAbandonedCartActivationButton"
						class="button cr-secondary"
						data-active="<?php	// phpcs:ignore;
						echo $abandoned_cart_view_model->is_ac_function_enabled() ? 'true' : 'false'
						// phpcs:ignore;
						?>"
				>
					<span class="spinner"></span>
					<?php
					if ( ! $abandoned_cart_view_model->is_ac_function_enabled() ) {
						echo esc_html( __( 'Activate', 'cleverreach-wc' ) );
					} else {
						echo esc_html( __( 'Deactivate', 'cleverreach-wc' ) );
					}
					?>
				</button>
				<hr>
				<h2>
				<?php
					echo esc_html(
						__(
							'THEA Automation',
							'cleverreach-wc'
						)
					)
					?>
				</h2>
				<p>
					<?php
					echo esc_html(
						__(
							'To send emails this option has to be enabled!',
							'cleverreach-wc'
						)
					)
					?>
				</p>
				<div>
					<button id="crAbandonedCartActivateTHEA"
							class="button cr-secondary <?php	// phpcs:ignore;
							echo $abandoned_cart_view_model->is_ac_function_enabled() ? '' : 'cr-disabled'
							// phpcs:ignore;
							?>"
							data-thea-active="<?php	// phpcs:ignore;
							echo $abandoned_cart_view_model->is_thea_active() ? 'true' : 'false'
							// phpcs:ignore;
							?>"
							data-thea-id="<?php	// phpcs:ignore;
							echo esc_attr( $abandoned_cart_view_model->get_thea_id() )
							// phpcs:ignore;
							?>"
					>
						<?php
						echo $abandoned_cart_view_model->is_thea_active() ?
							esc_html( __( 'Deactivate', 'cleverreach-wc' ) ) :
							esc_html( __( 'Activate', 'cleverreach-wc' ) )
						?>
					</button>
					<button id="crAbandonedCartEditEmail"
							class="button cr-primary
							<?php
							echo $abandoned_cart_view_model->is_ac_function_enabled() ? '' : 'cr-disabled'
							?>
							"
					>
						<?php
						echo esc_html( __( 'Edit email', 'cleverreach-wc' ) )
						?>
					</button>
				</div>
				<hr>
				<h2>
				<?php
					echo esc_html(
						__(
							'Timing Settings',
							'cleverreach-wc'
						)
					)
					?>
				</h2>
				<p>
					<?php
					echo esc_html(
						__(
							'If the customer abandons their checkout, send them an email reminder to complete their order after',
							'cleverreach-wc'
						)
					)
					?>
				</p>
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row">
							<?php
							echo esc_html(
								__(
									'Send email after',
									'cleverreach-wc'
								)
							)
							?>
						</th>
						<td class="forminp forminp-text">
							<fieldset>
								<legend class="screen-reader-text">
									<span>checkbox</span>
								</legend>
								<label for="cr-email-after">
									<input name="cr-email-after" type="radio"
											id="crACEmailAfter1Hour"
										<?php
										echo $abandoned_cart_view_model->get_delay() === 1 ? 'checked' : ''
										?>
											value="1">
									<?php
									echo esc_html(
										__(
											'1 Hour',
											'cleverreach-wc'
										)
									)
									?>
								</label>
							</fieldset>
							<fieldset>
								<legend class="screen-reader-text">
									<span>checkbox</span>
								</legend>
								<label for="cr-email-after">
									<input name="cr-email-after" type="radio"
											id="crACEmailAfter3Hours"
										<?php
										echo $abandoned_cart_view_model->get_delay() === 3 ? 'checked' : ''
										?>
											value="3">
									<?php
									echo esc_html(
										__(
											'3 Hours',
											'cleverreach-wc'
										)
									)
									?>
								</label>
							</fieldset>
							<fieldset>
								<legend class="screen-reader-text">
									<span>checkbox</span>
								</legend>
								<label for="cr-email-after">
									<input name="cr-email-after" type="radio"
											id="crACEmailAfter10Hours"
										<?php
										echo $abandoned_cart_view_model->get_delay() === 10 ? 'checked' : ''
										?>
											value="10">
									<?php
									echo esc_html(
										__(
											'10 Hours (Recommended)',
											'cleverreach-wc'
										)
									)
									?>
								</label>
							</fieldset>
							<fieldset>
								<legend class="screen-reader-text">
									<span>checkbox</span>
								</legend>
								<label for="cr-email-after">
									<input name="cr-email-after" type="radio"
											id="crACEmailAfter24Hours"
										<?php
										echo $abandoned_cart_view_model->get_delay() === 24 ? 'checked' : ''
										?>
											value="24">
									<?php
									echo esc_html(
										__(
											'24 Hours',
											'cleverreach-wc'
										)
									)
									?>
								</label>
							</fieldset>

						</td>
					</tr>
					</tbody>
				</table>

				<hr>
				<h2>
					<?php
					echo esc_html(
						__(
							'Send Settings',
							'cleverreach-wc'
						)
					)
					?>
				</h2>
				<p>
					<?php
					echo esc_html(
						__(
							'Edit recipients in the recipient settings.',
							'cleverreach-wc'
						)
					)
					?>
				</p>
				<table class="form-table" id="ac-edit-settings">
					<tbody>
					<tr>
						<th scope="row">
							<?php
							echo esc_html( __( 'Send emails to', 'cleverreach-wc' ) )
							?>
						</th>
						<td class="forminp forminp-text">
							<fieldset>
								<legend class="screen-reader-text">
									<span>checkbox</span>
								</legend>
								<label for="cr-subscriber">
									<input name="cr-subscriber" type="checkbox"
											id="crACSubscribersCheckbox" checked
											disabled
											readonly value="1">
									<?php
									echo esc_html( __( 'Subscriber', 'cleverreach-wc' ) )
									?>
								</label>
								<p class="description">
									<?php
									echo esc_html( __( 'Subscribed to your newsletter.', 'cleverreach-wc' ) )
									?>
								</p>
							</fieldset>
							<fieldset>
								<legend class="screen-reader-text">
									<span>checkbox</span>
								</legend>
								<label for="cr-buyer">
									<input name="cr-buyer" type="checkbox"
											id="crACBuyersCheckbox" value="1"
											disabled
											readonly>
									<?php
									echo esc_html( __( 'Buyer', 'cleverreach-wc' ) )
									?>
								</label>
								<p class="description">
									<?php
									echo esc_html( __( 'Bought something from your store.', 'cleverreach-wc' ) )
									?>
								</p>
							</fieldset>
							<fieldset>
								<legend class="screen-reader-text">
									<span>checkbox</span>
								</legend>
								<label for="cr-contact">
									<input name="cr-contact" type="checkbox"
											id="crACContactsCheckbox" value="1"
											disabled
											readonly>
									<?php
									echo esc_html( __( 'Contacts', 'cleverreach-wc' ) )
									?>
								</label>
								<p class="description">
									<?php
									echo esc_html( __( 'Didn\'t subscribe or buy.', 'cleverreach-wc' ) )
									?>
								</p>
							</fieldset>
						</td>
					</tr>
					</tbody>
				</table>
				<button id="crACEditInSettings" class="button cr-primary">
					<?php
					echo esc_html( __( 'Edit in recipient settings', 'cleverreach-wc' ) )
					?>
				</button>
				<hr>
				<button id="crACSaveButton"
						class="button cr-secondary">
					<?php
					echo esc_html( __( 'Save changes', 'cleverreach-wc' ) )
					?>
				</button>
			</div>
		</div>
	</div>

</div>
