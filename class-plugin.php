<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce;

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use CleverReach\WooCommerce\Components\Exceptions\Unable_To_Create_Hook_Handler_Exception;
use CleverReach\WooCommerce\Components\HookHandlers\Hook_Handler;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Init_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Notification\Contracts\Notification_Service_Interface;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Notification\Notification_Service;
use CleverReach\WooCommerce\Components\Setup\Uninstall;
use CleverReach\WooCommerce\Components\Setup\Update;
use CleverReach\WooCommerce\Components\Util\Shop_Helper;
use CleverReach\WooCommerce\Controllers\Clever_Reach_Frontend_Controller;
use CleverReach\WooCommerce\Controllers\Clever_Reach_Index;
use CleverReach\WooCommerce\Integration\Clever_Reach_Block_Extend_Store_Endpoint;
use CleverReach\WooCommerce\Integration\Clever_Reach_Checkout_Integration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\TaskRunnerWakeupService;
use CleverReach\WooCommerce\ViewModel\AbandonedCartOverview\Abandoned_Cart_Records_List;
use wpdb;

/**
 * Class Plugin
 *
 * @package CleverReach\WooCommerce
 */
class Plugin {

	/**
	 * WordPress database session.
	 *
	 * @var wpdb
	 */
	public $db;
	/**
	 * Plugin instance.
	 *
	 * @var Plugin
	 */
	protected static $instance;
	/**
	 * Plugin file.
	 *
	 * @var string
	 */
	private $cleverreach_plugin_file;

	/**
	 * Notification service.
	 *
	 * @var Notification_Service
	 */
	private $notification_service;

	/**
	 * Task Runner Wake up Service
	 *
	 * @var TaskRunnerWakeupService
	 */
	private $task_runner_wakeup_service;


	/**
	 * Plugin constructor.
	 *
	 * @param wpdb   $wpdb WordPress database session.
	 * @param string $cleverreach_plugin_file Plugin file.
	 */
	public function __construct( $wpdb, $cleverreach_plugin_file ) {
		$this->db                      = $wpdb;
		$this->cleverreach_plugin_file = $cleverreach_plugin_file;
	}

	/**
	 * Returns singleton instance of the plugin.
	 *
	 * @param wpdb   $wpdb WordPress database session.
	 * @param string $cleverreach_plugin_file Plugin file.
	 *
	 * @return Plugin
	 */
	public static function instance( $wpdb, $cleverreach_plugin_file ) {
		if ( null === self::$instance ) {
			self::$instance = new self( $wpdb, $cleverreach_plugin_file );
		}

		self::$instance->initialize();

		return self::$instance;
	}

	/**
	 * Plugin activation function.
	 *
	 * @param bool $is_network_wide True if activation is network wide.
	 *
	 * @return void
	 */
	public function activate( $is_network_wide ) {
		if ( ! Shop_Helper::is_curl_enabled() ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die(
				esc_html(
					__(
						'cURL is not installed or enabled in your PHP installation. This is required for background task to work. Please install it and then refresh this page.',
						'cleverreach-wc'
					)
				),
				'Plugin dependency check',
				array( 'back_link' => true )
			);
		}

		if ( ! Shop_Helper::is_woocommerce_active() ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die(
				esc_html( __( 'Please install and activate WooCommerce.', 'cleverreach-wc' ) ),
				'Plugin dependency check',
				array( 'back_link' => true )
			);
		}

		$updater = new Update();
		$updater->check_and_initialize_sites( true, $is_network_wide );
		$this->get_task_runner_wakeup_service()->wakeup();
	}

	/**
	 * Adds cleverreach query variable.
	 *
	 * @param mixed[] $vars Filter variables.
	 *
	 * @return mixed[] Filter variables.
	 */
	public function plugin_add_trigger( $vars ) {
		$vars[] = 'cleverreach_wc_controller';

		return $vars;
	}

	/**
	 * Trigger action on calling plugin controller.
	 *
	 * @return void
	 */
	public function plugin_trigger_check() {
		$controller_name = get_query_var( 'cleverreach_wc_controller' );
		if ( ! empty( $controller_name ) ) {
			$controller = new Clever_Reach_Index();
			$controller->index();
		}
	}

	/**
	 * Adds newsletter field to the form.
	 *
	 * @return void
	 */
	public function register_form_field_newsletter() {
		include plugin_dir_path( $this->cleverreach_plugin_file ) . 'resources/views/partial/register-form-newsletter-checkbox.php';
	}

	/**
	 * Adds newsletter field to the form.
	 *
	 * @return void
	 */
	public function register_checkout_form_newsletter() {
		include plugin_dir_path( $this->cleverreach_plugin_file ) . 'resources/views/partial/checkout-form-newsletter-checkbox.php';

		$base_url = Shop_Helper::get_clever_reach_base_url( '/resources/' );

		wp_enqueue_script(
			'cr_checkout_form_newsletter_checkbox_js',
			$base_url . 'js/cleverreach.checkout-form-newsletter-checkbox.js',
			array(),
			Clever_Reach_Frontend_Controller::SCRIPT_VERSION
		);
	}

	/**
	 * Adds newsletter field to the form.
	 *
	 * @return void
	 */
	public function register_profile_newsletter() {
		include plugin_dir_path( $this->cleverreach_plugin_file ) . 'resources/views/partial/profile-newsletter-checkbox.php';
	}

	/**
	 * Adds billing email field listener.
	 *
	 * @return void
	 */
	public function add_abandoned_cart_billing_email_listener_jscript() {
		include plugin_dir_path( $this->cleverreach_plugin_file ) . 'resources/views/partial/abandoned-cart-billing-email-listener.php';

		$base_url = Shop_Helper::get_clever_reach_base_url( '/resources/' );

		wp_enqueue_script(
			'cr_ac_billing_email_listener_js',
			$base_url . 'js/cleverreach.ac-billing-email-listener.js',
			array(),
			Clever_Reach_Frontend_Controller::SCRIPT_VERSION
		);
	}

	/**
	 * Adds CleverReach - Abandoned Carts tab to Woocommerce reports.
	 *
	 * @param mixed $reports Reports array.
	 *
	 * @return mixed
	 */
	public function add_cr_abandoned_cart_tab_to_reports( $reports ) {
		$reports['cr-abandoned-cart'] = array(
			'title'   => __( 'CleverReach - Abandoned Carts', 'cleverreach-wc' ),
			'reports' => array(
				'taxes_by_code' => array(
					'title'       => __( 'CleverReach - Abandoned Carts', 'cleverreach-wc' ),
					'description' => '',
					'hide_title'  => true,
					'callback'    => array( $this, 'get_report' ),
				),
			),
		);

		return $reports;
	}

	/**
	 * Adds CleverReach - Abandoned Carts Overview page JS.
	 *
	 * @return void
	 */
	public function add_cr_abandoned_cart_overview_js() {
		$base_url = Shop_Helper::get_clever_reach_base_url( '/resources/' );
		wp_enqueue_script(
			'cr_ac_overview_js',
			$base_url . 'js/cleverreach.ac-overview.js',
			array(),
			Clever_Reach_Frontend_Controller::SCRIPT_VERSION
		);
	}

	/**
	 * Adds content to the CleverReach - Abandoned Carts tab on WC-Reports page.
	 *
	 * @return void
	 */
	public function get_report() {
		$base_url = Shop_Helper::get_clever_reach_base_url( '/resources/' );

		wp_enqueue_style(
			'cr_global-admin-styles',
			$base_url . 'css/cleverreach.css',
			array(),
			Clever_Reach_Frontend_Controller::SCRIPT_VERSION
		);

		$ac_records_table = new Abandoned_Cart_Records_List();

		$ac_records_table->prepare_items();

		echo '<div id="poststuff" class="woocommerce-reports-wide">';

		$this->add_abandoned_cart_overview_screen_options();
		$ac_records_table->render_table();

		echo '</div>';
	}

	/**
	 * Add abandoned orders button to orders page.
	 *
	 * @return void
	 */
	public function add_abandoned_orders_button_to_orders_page() {
		$screen = get_current_screen();
		if ( null !== $screen && 'edit-shop_order' === $screen->id ) {
			include plugin_dir_path( $this->cleverreach_plugin_file ) . 'resources/views/partial/abandoned-orders-link.php';
		}
	}

	/**
	 * Add abandoned cart overview screen options.
	 *
	 * @return void
	 */
	public function add_abandoned_cart_overview_screen_options() {
		include plugin_dir_path( $this->cleverreach_plugin_file ) . 'resources/views/partial/abandoned-cart-screen-options.php';
	}

	/**
	 * Creates CleverReach item in administrator menu.
	 *
	 * @return void
	 */
	public function create_admin_menu() {
		$controller = new Clever_Reach_Frontend_Controller();
		add_submenu_page(
			'woocommerce',
			'CleverReach®',
			'CleverReach®',
			'manage_options',
			'cleverreach-wc',
			array( $controller, 'render' )
		);
	}

	/**
	 * Starts WordPress session.
	 *
	 * @return void
	 */
	public function start_session() {
		if ( ! session_id() ) {
			session_start( array( 'read_and_close' => true ) );
		}
	}

	/**
	 * Ends WordPress session.
	 *
	 * @return void
	 */
	public function end_session() {
		if ( PHP_SESSION_ACTIVE === session_status() ) {
			session_destroy();
		}
	}

	/**
	 * Loads plugin translations.
	 *
	 * @return void
	 */
	public function load_plugin_text_domain() {
		unload_textdomain( 'cleverreach-wc' );
		load_plugin_textdomain(
			'cleverreach-wc',
			false,
			plugin_basename( dirname( $this->cleverreach_plugin_file ) ) . '/i18n/languages'
		);
	}

	/**
	 * Initializes the plugin.
	 *
	 * @return void
	 */
	private function initialize() {
		Init_Service::init();

		$this->load_plugin_init_hooks();

		if ( Shop_Helper::is_plugin_enabled() ) {
			$this->load_clever_reach_admin_menu();
			$this->load_clever_reach_newsletter_field();
			$this->load_plugin_text_domain();
			$this->load_clever_reach_ac_billing_email_listener();
			$this->load_clever_reach_ac_tab_in_wc_reports();
			$this->load_clever_reach_abandoned_orders_link();

			$hook_handler = new Hook_Handler();
			try {
				$hook_handler->register_hooks();
			} catch ( Unable_To_Create_Hook_Handler_Exception $e ) {
				Logger::logError( $e->getMessage(), 'Integration' );
			}

			if ( $this->get_notification_service()->should_show_notifications() ) {
				add_action( 'admin_notices', array( $this->get_notification_service(), 'show_message' ) );
			}
		}
	}

	/**
	 * Registers install and uninstall hook.
	 *
	 * @return void
	 */
	private function load_plugin_init_hooks() {
		register_activation_hook( $this->cleverreach_plugin_file, array( $this, 'activate' ) );
		add_action( 'init', array( $this, 'start_session' ) );
		add_action( 'admin_init', array( new Update(), 'check_and_initialize_sites' ) );
		add_action( 'upgrader_process_complete', array( new Update(), 'check_and_initialize_sites' ) );
		add_filter( 'query_vars', array( $this, 'plugin_add_trigger' ) );
		add_action( 'template_redirect', array( $this, 'plugin_trigger_check' ) );
		add_action( 'wp_logout', array( $this, 'end_session' ) );
		add_action( 'wp_login', array( $this, 'end_session' ) );
		add_action( 'wp_login', array( $this, 'end_session' ) );
		add_action(
			'woocommerce_before_checkout_form',
			function () {
				wp_enqueue_script( 'jquery' );
			}
		);
		add_action(
			'wp_enqueue_scripts',
			function () {
				wp_enqueue_script( 'jquery' ); // compatibility with wp > 6 .
			}
		);
		if ( is_multisite() ) {
			add_action( 'delete_blog', array( new Uninstall(), 'switch_to_site_and_uninstall_plugin' ) );
		}

		add_action( 'before_woocommerce_init', array( $this, 'declare_compatibility_with_hpos' ) );
	}

	/**
	 * Declare compatibility with High Performance Order Storage
	 *
	 * @return void
	 */
	public function declare_compatibility_with_hpos() {
		if ( class_exists( FeaturesUtil::class ) ) {
			FeaturesUtil::declare_compatibility( 'custom_order_tables', $this->cleverreach_plugin_file );
		}
	}

	/**
	 * Returns url for the provided directory
	 *
	 * @param string $path - Directory path.
	 *
	 * @return string
	 */
	public static function get_plugin_url( $path ) {
		return rtrim( plugins_url( "/{$path}/", __DIR__ ), '/' );
	}


	/**
	 * Returns base directory path
	 *
	 * @return string
	 */
	public static function get_plugin_dir_path() {
		return rtrim( plugin_dir_path( __DIR__ ), '/' );
	}

	/**
	 * Adds CleverReach item to backend administrator menu.
	 *
	 * @return void
	 */
	private function load_clever_reach_admin_menu() {
		if ( is_admin() && ! is_network_admin() ) {
			add_action( 'admin_menu', array( $this, 'create_admin_menu' ) );
		}
	}

	/**
	 * Adds newsletter subscription field to user forms.
	 *
	 * @return void
	 */
	private function load_clever_reach_newsletter_field() {
		add_action( 'register_form', array( $this, 'register_form_field_newsletter' ) );
		add_action( 'user_new_form', array( $this, 'register_profile_newsletter' ) );
		add_action( 'show_user_profile', array( $this, 'register_profile_newsletter' ) );
		add_action( 'edit_user_profile', array( $this, 'register_profile_newsletter' ) );

		add_action( 'woocommerce_register_form', array( $this, 'register_form_field_newsletter' ) );
		add_action( 'woocommerce_edit_account_form', array( $this, 'register_profile_newsletter' ) );
		add_action( 'woocommerce_after_checkout_billing_form', array( $this, 'register_checkout_form_newsletter' ) );

		/**
		 * Include the dependencies needed to instantiate the block.
		 */
		add_action(
			'woocommerce_blocks_loaded',
			function () {

				// Initialize our store endpoint extension when WC Blocks is loaded.
				Clever_Reach_Block_Extend_Store_Endpoint::init();

				add_action(
					'woocommerce_blocks_checkout_block_registration',
					function ( $integration_registry ) {
						$integration_registry->register( new Clever_Reach_Checkout_Integration() );
					}
				);
			}
		);
	}

	/**
	 * Adds event listener to the billing email field on checkout page.
	 *
	 * @return void
	 */
	private function load_clever_reach_ac_billing_email_listener() {
		add_action(
			'woocommerce_after_checkout_billing_form',
			array( $this, 'add_abandoned_cart_billing_email_listener_jscript' )
		);
	}

	/**
	 * Adds CleverReach - Abandoned Carts tab on WC-Reports page and loads necessary JS.
	 *
	 * @return void
	 */
	private function load_clever_reach_ac_tab_in_wc_reports() {
		add_filter( 'woocommerce_admin_reports', array( $this, 'add_cr_abandoned_cart_tab_to_reports' ), 100, 1 );
		add_action(
			'load-woocommerce_page_wc-reports',
			array( $this, 'add_cr_abandoned_cart_overview_js' ),
			100,
			1
		);
	}

	/**
	 * Adds link on orders page to the CleverReach - Abandoned Carts tab on WC-Reports page.
	 *
	 * @return void
	 */
	private function load_clever_reach_abandoned_orders_link() {
		add_action( 'admin_print_footer_scripts', array( $this, 'add_abandoned_orders_button_to_orders_page' ) );
	}

	/**
	 * Retrieves notification service;
	 *
	 * @return Notification_Service
	 */
	private function get_notification_service() {
		if ( null === $this->notification_service ) {
			/**
			 * Notification service.
			 *
			 * @var Notification_Service $notification_service
			 */
			$notification_service       = ServiceRegister::getService( Notification_Service_Interface::CLASS_NAME );
			$this->notification_service = $notification_service;
		}

		return $this->notification_service;
	}

	/**
	 * Retrieves Task runner wake up service
	 *
	 * @return TaskRunnerWakeupService
	 */
	private function get_task_runner_wakeup_service() {
		if ( null === $this->task_runner_wakeup_service ) {
			/**
			 * Task runner wakeup service
			 *
			 * @var TaskRunnerWakeupService $task_runner_wakeup_service
			 */
			$task_runner_wakeup_service       = ServiceRegister::getService( TaskRunnerWakeup::CLASS_NAME );
			$this->task_runner_wakeup_service = $task_runner_wakeup_service;
		}

		return $this->task_runner_wakeup_service;
	}
}
