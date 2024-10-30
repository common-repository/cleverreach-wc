<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

$report_page = menu_page_url( 'wc-reports', false );
$report_page = add_query_arg( 'tab', 'cr-abandoned-cart', $report_page );
?>

<script>
	jQuery('.wrap .page-title-action').after('<a href="<?php echo esc_url( $report_page ); ?>" class="page-title-action">' +
		'<?php echo esc_html( __( 'Abandoned orders', 'cleverreach-wc' ) ); ?></a>');
</script>