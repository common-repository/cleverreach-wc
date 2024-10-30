<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Listeners;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Language\Translator;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Notification\Contracts\NotificationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Notification\DTO\Notification;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Exceptions\ServiceNotRegisteredException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\GuidProvider;

/**
 * Class ConnectionLostEventListener
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Listeners
 */
class ConnectionLostEventListener
{
    const CLASS_NAME = __CLASS__;

    /**
     * Push notification when connection lost with the CleverReach
     *
     * @return void
     */
    public static function handle()
    {
        try {
            /** @var NotificationService $notificationService */
            $notificationService = ServiceRegister::getService(NotificationService::CLASS_NAME);
            $notification = new Notification(GuidProvider::getInstance()->generateGuid(), 'connectionLost');
            $notification->setDescription(Translator::translate('connectionLost'));
            $notification->setDate(new \DateTime());
            $notificationService->push($notification);
        } catch (ServiceNotRegisteredException $exception) {
            Logger::logInfo("Notifications are not supported. {$exception->getMessage()}");
        }
    }
}
