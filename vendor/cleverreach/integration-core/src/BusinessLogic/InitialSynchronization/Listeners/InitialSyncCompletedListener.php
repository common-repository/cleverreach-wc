<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Listeners;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Contracts\AppStateService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\StateRegister;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\States\Dashboard;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Language\Translator;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Notification\Contracts\NotificationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Notification\DTO\Notification;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Exceptions\ServiceNotRegisteredException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\GuidProvider;

/**
 * Class InitialSyncCompletedListener
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Listeners
 */
class InitialSyncCompletedListener
{
    const CLASS_NAME = __CLASS__;

    /**
     * Push notification when initial sync finished
     *
     * @return void
     */
    public static function handle()
    {
        try {
            /** @var NotificationService $notificationService */
            $notificationService = ServiceRegister::getService(NotificationService::CLASS_NAME);
            $notification = new Notification(GuidProvider::getInstance()->generateGuid(), 'initialSyncCompleted');
            $notification->setDescription(Translator::translate('initialSyncCompleted'));
            $notification->setDate(new \DateTime());
            $notificationService->push($notification);
        } catch (ServiceNotRegisteredException $exception) {
            Logger::logInfo("Notifications are not supported. {$exception->getMessage()}");
        }


        /** @var AppStateService $appStateService */
        $appStateService = ServiceRegister::getService(AppStateService::CLASS_NAME);
        $context = $appStateService->getStateContext();
        $context->changeState();

        $appStateService->setStateContext($context);
    }
}
