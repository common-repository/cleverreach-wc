<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

use CleverReach\WooCommerce\ViewModel\Autoconfigure_Config;

$auto_configure_config = Autoconfigure_Config::get_autoconfigure_config();
?>

<div class="wrap cr-container">
	<input type="hidden" id="cr-start-server-configuration-url"
			value="
			<?php
			echo esc_url( $auto_configure_config['startServerConfigurationUrl'] )
			?>
			">
	<input type="hidden" id="cr-check-status-url"
			value="
			<?php
			echo esc_url( $auto_configure_config['checkStatusUrl'] )
			?>
			">
	<input type="hidden" id="cr-autoconfigure-failed"
			value="<?php echo esc_attr( $auto_configure_config['autoconfigureFailed'] ); ?>">

	<div id="autoconfigure-loading-container" class="loading-container">
		<div class="spinner-big"></div>
		<p class="autoconfig-text">
		<?php
			echo esc_html( __( 'Auto configuration...', 'cleverreach-wc' ) )
		?>
			</p>
	</div>

	<div id="autoconfigureErrorPanel"
		class="cr-notice cr-error 
		<?php
			echo ! $auto_configure_config['autoconfigureFailed'] ? 'cr-hidden' : ''
		?>
		">
		<p class="title">
		<?php
			echo esc_html( __( 'Autoconfiguration failed', 'cleverreach-wc' ) )
		?>
			</p>
		<p>
		<?php
			echo esc_html(
				__(
					'Please have a look at the ',
					'cleverreach-wc'
				)
			)
			?>
		<a href="
		<?php
		echo esc_html(
			__(
				'https://support.cleverreach.de/hc/en-us/articles/4408432814866',
				'cleverreach-wc'
			)
		)
		?>
		" target="_blank">
			<?php
			echo esc_html(
				__(
					'help center article ',
					'cleverreach-wc'
				)
			)
			?>
		</a>
			<?php
			echo esc_html(
				__(
					'to check out what causes the issue.',
					'cleverreach-wc'
				)
			)
			?>
		</p>
		<a id="cr-retryTest" class="button cr-secondary">
		<?php
			echo esc_html(
				__(
					'Retry auto configuration now',
					'cleverreach-wc'
				)
			)
			?>
			</a>
	</div>
</div>
