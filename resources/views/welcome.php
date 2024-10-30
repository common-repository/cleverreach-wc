<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

use CleverReach\WooCommerce\ViewModel\Welcome_Config;

$welcome_config = Welcome_Config::get_welcome_config();
?>

<div class="wrap cr-container">
	<input type="hidden" id="crCheckStatusUrl" value="
	<?php
	echo esc_url( $welcome_config['checkStatusUrl'] )
	?>
	">
	<div class="cr-content-window-wrapper">
		<div class="cr-content-window">
			<?php if ( $welcome_config['isGroupDeleted'] ) { ?>
				<div class="notice notice-error">
					<?php esc_html_e( 'The sync is not working because the initial connected group is deleted in CleverReach. Please reconnect your account to use the app.', 'cleverreach-wc' ); ?>
				</div>
			<?php } ?>
			<div id="welcome-loading-container" class="loading-container hidden">
				<div class="spinner-big"></div>
			</div>
			<iframe id="cr-iframe" scrolling="no" src="
			<?php
			echo esc_url( $welcome_config['authUrl'] );
			?>
			"></iframe>
		</div>
	</div>

</div>
