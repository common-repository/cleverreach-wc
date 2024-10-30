<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

?>

<div class="wrap cr-container">

	<div id="wcErrorPanel" class="cr-notice cr-error">
		<p class="title">
		<?php
			echo esc_html( __( 'WooCommerce not detected', 'cleverreach-wc' ) )
		?>
			</p>
		<p>
		<?php
			echo esc_html(
				__(
					'It seems that WooCommerce is not activated. This plugin fully depends on it. Please install and activate WooCommerce for this site in order to use CleverReach® integration.',
					'cleverreach-wc'
				)
			)
			?>
			</p>
	</div>

</div>