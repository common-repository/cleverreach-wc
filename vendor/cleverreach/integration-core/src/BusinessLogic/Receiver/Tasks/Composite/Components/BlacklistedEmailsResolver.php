<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components;

class BlacklistedEmailsResolver extends ReceiverSyncSubTask
{
    const CLASS_NAME = __CLASS__;

    /**
     * Resolves blacklisted emails.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function execute()
    {
        /** @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Context\ExecutionContext $context */
        $context = $this->getExecutionContext();
        $context->blacklistedEmails = $this->removeSuffix($this->getReceiverProxy()->getBlacklisted());

        $this->reportProgress(100);
    }

    /**
     * Removes suffix from blacklisted emails
     *
     * @param string[] $emails
     *
     * @return string[]
     */
    protected function removeSuffix(array $emails)
    {
        $suffix = $this->getGroupService()->getBlacklistedEmailsSuffix();

        return array_map(function ($email) use ($suffix) {
            return rtrim($email, $suffix);
        }, $emails);
    }
}
