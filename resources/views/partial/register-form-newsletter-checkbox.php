<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

use CleverReach\WooCommerce\Components\Repositories\Customers\Subscriber_Repository;

$newsletter_checkbox_view_model = new CleverReach\WooCommerce\ViewModel\NewsletterCheckbox\Newsletter_Checkbox();

$cr_checked = false;

$current_active_user    = wp_get_current_user();
$current_active_user_id = $current_active_user->ID;
// if this is set then the page is opened from admin panel.
if ( isset( $GLOBALS['profileuser']->data->ID ) ) {
	$current_active_user_id = $GLOBALS['profileuser']->data->ID;
}

$cr_checked = get_user_meta( $current_active_user_id, Subscriber_Repository::get_newsletter_column(), true ) === '1';

$cr_newsletter_caption = $newsletter_checkbox_view_model->get_newsletter_checkbox_caption();
$is_checkbox_disabled  = $newsletter_checkbox_view_model->get_newsletter_checkbox_disabled();

if ( wc()->session ) {
	wc()->session->set( 'from_profile', false );
}

?>
<?php
if ( ! $is_checkbox_disabled ) {
	?>
	<table class="form-table">
		<tbody>
		<tr>
			<th scope="row">
			<?php
				echo esc_html( __( $cr_newsletter_caption, 'cleverreach-wc' ) ); // phpcs:ignore
			?>
				</th>
			<td>
				<label for="<?php	// phpcs:ignore
				echo esc_html( Subscriber_Repository::NEWSLETTER_STATUS_FIELD );
				// phpcs:ignore
				?>">
					<input type="checkbox"
							class=""
							name="<?php	// phpcs:ignore
							echo esc_attr( Subscriber_Repository::NEWSLETTER_STATUS_FIELD );
							// phpcs:ignore
							?>"
							id="<?php	// phpcs:ignore
							echo esc_attr( Subscriber_Repository::NEWSLETTER_STATUS_FIELD );
							// phpcs:ignore
							?>"
							value="1"
						<?php
						echo esc_attr( $cr_checked ? ' checked="checked"' : '' );
						?>
					/>
				</label>
			</td>
		</tr>
		</tbody>
	</table>
	<div class="clear"></div>
	<?php
} ?>
