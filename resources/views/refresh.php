<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

use CleverReach\WooCommerce\ViewModel\Refresh_Config;

$refresh_config = Refresh_Config::get_refresh_config();
?>

<div class="wrap cr-container">

	<input type="hidden" id="cr-check-status-url" value="
	<?php
	echo esc_url( $refresh_config['checkStatusUrl'] )
	?>
	">

	<div id="offlinePanel" class="cr-notice cr-error cr-err-account-disconnected">
		<p class="title">
		<?php
			echo esc_html( __( 'Your CleverReach® account is disconnected', 'cleverreach-wc' ) )
		?>
			</p>
		<p>
		<?php
			echo esc_html(
				__(
					'Something went wrong. Please reconnect your account to use the app.',
					'cleverreach-wc'
				)
			)
			?>
			</p>
	</div>

	<div class="cr-content-window-wrapper">
		<div class="cr-content-window">
			<iframe id="cr-iframe" scrolling="no" src="
			<?php
						echo esc_url( $refresh_config['authUrl'] )
			?>
						"></iframe>
		</div>
	</div>
</div>
