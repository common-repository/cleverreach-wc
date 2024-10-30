<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Webhooks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\CartAutomation;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToHandleWebhookException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\AutomationRecordService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\CartAutomationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\WebHook;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Handler
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Webhooks
 */
class Handler
{
    /**
     * List of supported events.
     *
     * @var string[]
     */
    protected static $supportedEvents = array(
        'automation.activated',
        'automation.deactivated',
        'automation.deleted',
    );

    /**
     * Handles cart automation webhook.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\WebHook $hook
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToDeleteAutomationRecordException
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToDeleteCartException
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToHandleWebhookException
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToUpdateCartException
     */
    public function handle(WebHook $hook)
    {
        $this->validate($hook);
        $carts = $this->getCartService()->findBy(array('condition' => $hook->getCondition()));
        $cart = !empty($carts[0]) ? $carts[0] : null;
        if (empty($cart)) {
            throw new FailedToHandleWebhookException('Cart not found [' . $hook->getCondition() . '].');
        }

        switch ($hook->getEvent()) {
            case 'automation.activated':
                $this->activateCart($cart);
                break;
            case 'automation.deactivated':
                $this->deactivateCart($cart);
                break;
            case 'automation.deleted':
                $this->deleteCart($cart);
                break;
        }
    }

    /**
     * Structurally validates webhook.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\WebHook $hook
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToHandleWebhookException
     */
    private function validate(WebHook $hook)
    {
        if (!in_array($hook->getEvent(), static::$supportedEvents, true)) {
            throw new FailedToHandleWebhookException('Event [' . $hook->getEvent() . '] not supported.');
        }

        $condition = $hook->getCondition();
        if (empty($condition)) {
            throw new FailedToHandleWebhookException('Condition not provided.');
        }
    }

    /**
     * Activates cart.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\CartAutomation $cart
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToUpdateCartException
     */
    private function activateCart(CartAutomation $cart)
    {
        $cart->setIsActive(true);
        $this->getCartService()->update($cart);
    }

    /**
     * Deactivates cart.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\CartAutomation $cart
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToUpdateCartException
     */
    private function deactivateCart(CartAutomation $cart)
    {
        $cart->setIsActive(false);
        $this->getCartService()->update($cart);
    }

    /**
     * Deletes cart.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\CartAutomation $cart
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToDeleteAutomationRecordException
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToDeleteCartException
     */
    private function deleteCart(CartAutomation $cart)
    {
        $this->getRecordService()->deleteBy(array('automationId' => $cart->getId()));
        $this->getCartService()->delete($cart->getId());
    }

    /**
     * Provides cart automation service.
     *
     * @return CartAutomationService
     */
    private function getCartService()
    {
        /** @var CartAutomationService $cartService */
        $cartService = ServiceRegister::getService(CartAutomationService::CLASS_NAME);

        return $cartService;
    }

    /**
     * Provides automation record service.
     *
     * @return AutomationRecordService
     */
    private function getRecordService()
    {
        /** @var AutomationRecordService $automationRecordService */
        $automationRecordService = ServiceRegister::getService(AutomationRecordService::CLASS_NAME);

        return $automationRecordService;
    }
}
