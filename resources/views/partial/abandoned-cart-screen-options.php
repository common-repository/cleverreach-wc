<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

use CleverReach\WooCommerce\ViewModel\AbandonedCartOverview\Abandoned_Cart_Records_List;

$ac_record_list = new Abandoned_Cart_Records_List();
$column_headers = $ac_record_list->prepare_items();

?>

<div>
	<div id="ac-screen-meta" class="metabox-prefs" style="display: none;">
		<div id="ac-screen-options-wrap" class="hidden" tabindex="-1" aria-label="AC Screen Options"
			style="display: block;">
			<form id="ac-adv-settings" method="post">
				<fieldset class="metabox-prefs">
					<legend>
					<?php
						echo esc_html( __( 'Columns', 'cleverreach-wc' ) );
					?>
						</legend>
					<?php
					foreach ( $column_headers[0] as $key => $value ) :
						?>
						<label><input class="hide-column-tog" name="
						<?php
							echo esc_attr( $key );
						?>
							-hide" type="checkbox"
										id="
										<?php
										echo esc_attr( $key );
										?>
										-hide" value="
										<?php
										echo esc_attr( $key );
										?>
							"
								<?php
								echo ! in_array( $key, $column_headers[1], true ) ? 'checked="checked"' : ''
								?>
								>
							<?php
							echo esc_html( __( $value, 'cleverreach-wc' ) ) // phpcs:ignore
							?>
							</label>
						<?php
					endforeach;
					?>

				</fieldset>
				<fieldset class="screen-options">
					<legend>
					<?php
						echo esc_html( __( 'Pagination', 'cleverreach-wc' ) );
					?>
						</legend>
					<label for="per_page">
					<?php
						echo esc_html( __( 'Number of items per page:', 'cleverreach-wc' ) );
					?>
						</label>
					<input type="number" step="1" min="1" max="999" class="screen-per-page"
							name="wp_screen_options[value]" id="ac_per_page" maxlength="3"
							value="
							<?php
							echo esc_attr( $ac_record_list->get_number_of_items_per_page() );
							?>
							">
					<input id="cr-ac-per-page-url" type="hidden" value="
					<?php
					echo esc_attr( $ac_record_list->get_config()['update_per_page_settings'] );
					?>
					">
				</fieldset>
				<p class="submit">
					<input type="button" name="screen-options-apply" id="ac-screen-options-apply"
							class="button button-primary" value="
							<?php
							echo esc_attr( __( 'Apply', 'cleverreach-wc' ) );
							?>
					">
				</p>
				<?php
				wp_nonce_field( 'screen-options-nonce', 'screenoptionnonce', false );
				?>
			</form>
		</div>
	</div>
	<div id="ac-screen-meta-links">
		<div id="ac-screen-options-link-wrap" class="hide-if-no-js screen-meta-toggle">
			<button type="button" id="show-ac-settings-link" class="button show-settings"
					aria-controls="screen-options-wrap" aria-expanded="false">
				<?php
				echo esc_html( __( 'Screen Options', 'cleverreach-wc' ) );
				?>
			</button>
		</div>
	</div>
</div>