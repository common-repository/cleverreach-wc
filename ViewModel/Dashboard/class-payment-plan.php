<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\ViewModel\Dashboard;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\PaymentPlan\Contracts\PaymentPlanService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Payment_Plan
 *
 * @package CleverReach\WooCommerce\ViewModel\Dashboard
 */
class Payment_Plan {


	/**
	 * Payment plan service
	 *
	 * @var PaymentPlanService
	 */
	private $payment_plan_service;

	/**
	 * Returns current plan info
	 *
	 * @param string $user_id User identification.
	 *
	 * @return string
	 */
	public function get_current_rate( $user_id ) {
		return (string) $this->get_payment_plan_service()->getPlanInfo( $user_id );
	}

	/**
	 * Retrieves Payment plan service
	 *
	 * @return PaymentPlanService
	 */
	private function get_payment_plan_service() {
		if ( null === $this->payment_plan_service ) {
			/**
			 * Payment plan service.
			 *
			 * @var PaymentPlanService $payment_plan_service
			 */
			$payment_plan_service       = ServiceRegister::getService( PaymentPlanService::CLASS_NAME );
			$this->payment_plan_service = $payment_plan_service;
		}

		return $this->payment_plan_service;
	}
}
