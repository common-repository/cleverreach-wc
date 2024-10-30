<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

use CleverReach\WooCommerce\ViewModel\InitialSyncSettings\Initial_Sync_Settings_Config;

$config = Initial_Sync_Settings_Config::get_initial_sync_settings_config();
?>

<input type="hidden" id="crSaveSettingsBeforeUrl" value="
<?php
echo esc_url( $config['saveSettingsUrl'] )
?>
">
<input type="hidden" id="crCancelSettingsBeforeUrl" value="
<?php
echo esc_url( $config['cancelUrl'] )
?>
">

<input type="hidden" id="crSubscribersExist" value="1">
<div class="wrap cr-container">
	<div id="cr-sync-settings-notification-error" class="notice notice-error cr-hidden"></div>
	<h2>
	<?php
		echo esc_html( __( 'Import Receivers', 'cleverreach-wc' ) )
	?>
		</h2>
	<p>
		<?php
		echo esc_html(
			__(
				'Select receivers to import. Observe the guidelines of the General Data Protection Regulation.',
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
		"
		target="_blank">
		<?php
		echo esc_html( __( 'Learn more about data protection', 'cleverreach-wc' ) )
		?>
	</a>
	<table class="form-table">
		<tbody>
		<tr valign="top">
			<td class="forminp forminp-text">
				<fieldset>
					<legend class="screen-reader-text">
						<span>checkbox</span>
					</legend>
					<label for="crSubscribersCheckbox">
						<input name="crSubscribersCheckbox" type="checkbox" id="crSubscribersCheckbox" checked readonly
								disabled value="1">
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
					<label for="crBuyersCheckbox">
						<input name="crBuyersCheckbox" type="checkbox" id="crBuyersCheckbox" value="1">
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
					<label for="crContactsCheckbox">
						<input name="crContactsCheckbox" type="checkbox" id="crContactsCheckbox" value="1">
						<?php
						echo esc_html( __( 'Contacts', 'cleverreach-wc' ) )
						?>
						<p class="description">
							<?php
							echo esc_html( __( 'Didn\'t subscribe or buy.', 'cleverreach-wc' ) )
							?>
						</p>
					</label>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td>
				<fieldset>
					<button id="crAdvancedConfigButton" class="button cr-secondary"><span id="crAdvancedConfigArrow"
																							class="cr-show-settings"></span>
						<?php
						echo esc_html( __( 'Advanced Recipient Import Configuration', 'cleverreach-wc' ) )
						?>
					</button>
				</fieldset>
				<fieldset id="cr-config-label" class="cr-filter-form-label" style="display: none">
					<label style="font-size: 16px;">
					<?php
						echo esc_html( strtoupper( __( 'Configure email filter', 'cleverreach-wc' ) ) )
					?>
						</label>
					<p>
					<?php
						echo esc_html( __( 'Configure filter that excludes emails from being synchronized.', 'cleverreach-wc' ) )
					?>
						</p>
					<a href="<?php echo esc_html( __( 'https://support.cleverreach.de/hc/en-us/articles/14920866742418', 'cleverreach-wc' ) ); ?>" target="_blank"><?php echo esc_html( __( 'Learn more about email blocklist filter.', 'cleverreach-wc' ) ); ?></a>
				</fieldset>
				<table id="cr-filter-form" style="display: none">
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
								<input name="crFilterTypeWildcard" id="crFilterTypeWildcard"
										placeholder="*@example.com">
							</fieldset>
						</td>
					</tr>
					</tbody>
				</table>
			</td>
		</tr>

		</tbody>
	</table>
	<button class="button cr-primary" id="crInitialSyncSettingsImportButton">
	<?php
		echo esc_html(
			__(
				'Import receivers',
				'cleverreach-wc'
			)
		)
		?>
			</button>
	<button class="button cr-secondary" id="crInitialSyncSettingsCancelButton">
	<?php
		echo esc_html(
			__(
				'Cancel',
				'cleverreach-wc'
			)
		)
		?>
			</button>

</div>
