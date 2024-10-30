<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Context\SubscribtionStateChangedExecutionContext;

/**
 * Class RemoveReceiverFromBlacklist
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components
 */
class RemoveReceiverFromBlacklist extends ReceiverSyncSubTask
{
    const CLASS_NAME = __CLASS__;

    /**
     * Whitelists receiver.
     */
    public function execute()
    {
        /** @var SubscribtionStateChangedExecutionContext $context */
        $context = $this->getExecutionContext();

        $receiver = $context->receiver;
        if ($receiver !== null) {
            try {
                $suffix = $this->getGroupService()->getBlacklistedEmailsSuffix();
                $this->getReceiverProxy()->whitelist($receiver->getEmail() . $suffix);
            } catch (\Exception $e) {
                Logger::logWarning(
                    "Failed to remove receiver from a blacklist because: {$e->getMessage()}.",
                    'Core',
                    array(new LogContextData('trace', $e->getTraceAsString()))
                );
            }
        }

        $this->reportProgress(100);
    }
}
