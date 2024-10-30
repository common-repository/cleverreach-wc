<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

?>

<div class="wrap cr-container">

	<div id="curlPanel" class="cr-notice cr-error">
		<p class="title">
		<?php
			echo esc_html( __( 'An error occurred', 'cleverreach-wc' ) )
		?>
			</p>
		<p>
		<?php
			echo esc_html(
				__(
					'cURL is not installed or enabled in your PHP installation. This is required for background task to work. Please install it and then refresh this page.',
					'cleverreach-wc'
				)
			)
			?>
			</p>
	</div>

</div>
