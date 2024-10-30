<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

use CleverReach\WooCommerce\ViewModel\Billing_Email_Listener_Config;

$listener_config = Billing_Email_Listener_Config::get_config();

?>

<input id="cr-billing-email-listener" type="hidden" value="
<?php
echo esc_attr( $listener_config['listenerUrl'] )
?>
">
