<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Blacklist\Blacklist;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Context\SubscribtionStateChangedExecutionContext;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;

/**
 * Class AddReceiverToBlacklist
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components
 */
class AddReceiverToBlacklist extends ReceiverSyncSubTask
{
    const CLASS_NAME = __CLASS__;

    /**
     * Blacklists receivers.
     */
    public function execute()
    {
        /** @var SubscribtionStateChangedExecutionContext $executionContext */
        $executionContext = $this->getExecutionContext();
        $receiver = $executionContext->receiver;
        if ($receiver !== null && $this->canBlacklistReceiver($receiver)) {
            try {
                $suffix = $this->getGroupService()->getBlacklistedEmailsSuffix();
                $blacklist = new Blacklist($receiver->getEmail() . $suffix);
                $blacklist->setComment('REST API' . $suffix);
                $this->getReceiverProxy()->blacklist($blacklist);
            } catch (\Exception $e) {
                Logger::logWarning(
                    "Failed to add receiver to a blacklist because: {$e->getMessage()}.",
                    'Core',
                    array(new LogContextData('trace', $e->getTraceAsString()))
                );
            }
        }

        $this->reportProgress(100);
    }

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver $receiver
     *
     * @return bool
     */
    private function canBlacklistReceiver(Receiver $receiver)
    {
        $deactivated = $receiver->getDeactivated();

        return empty($deactivated);
    }
}
