<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components\AddReceiverToBlacklist;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components\ReceiverGroupResolver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components\ResolveReceiver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components\SyncServicesResolver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components\UnsubscribeReceiver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components\UpsertReceiver;

class UnsubscribeReceiverTask extends SubscribeReceiverTask
{
    /**
     * @inheritDoc
     */
    protected function getSubTasks()
    {
        return array(
            ReceiverGroupResolver::CLASS_NAME => 5,
            SyncServicesResolver::CLASS_NAME => 10,
            ResolveReceiver::CLASS_NAME => 5,
            UnsubscribeReceiver::CLASS_NAME => 45,
            AddReceiverToBlacklist::CLASS_NAME => 5,
            UpsertReceiver::CLASS_NAME => 30,
        );
    }
}
