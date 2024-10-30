<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\Contracts\DynamicContentService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\DynamicContent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\Exceptions\ContentWithSameNameExistsException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

/**
 * Class RegisterDynamicContentTask
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent
 */
class RegisterDynamicContentTask extends Task
{
    const CLASS_NAME = __CLASS__;

    const INITIAL_PROGRESS_PERCENT = 10;

    /**
     * @var DynamicContentService
     */
    protected $dynamicContentService;
    /**
     * @var Proxy
     */
    protected $dynamicContentProxy;

    /**
     * Runs task logic
     *
     * @return void
     *
     * @throws FailedToRefreshAccessToken
     * @throws FailedToRetrieveAuthInfoException
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     * @throws QueryFilterInvalidParamException
     */
    public function execute()
    {
        $this->reportProgress(static::INITIAL_PROGRESS_PERCENT);
        $supportedContents = $this->getDynamicContentService()->getSupportedDynamicContent();

        if (!empty($supportedContents)) {
            $currentProgress = self::INITIAL_PROGRESS_PERCENT;
            $progressStep = (int)((100 - self::INITIAL_PROGRESS_PERCENT) / count($supportedContents));
            foreach ($supportedContents as $supportedContent) {
                if ($createdContent = $this->registerDynamicContent($supportedContent)) {
                    $this->getDynamicContentService()->addCreatedDynamicContentId($createdContent->getId());
                }

                $currentProgress += $progressStep;
                $this->reportProgress($currentProgress);
            }
        }


        $this->reportProgress(100);
    }

    /**
     * @return DynamicContentService
     */
    protected function getDynamicContentService()
    {
        if ($this->dynamicContentService === null) {
            /** @var DynamicContentService $dynamicContentService */
            $dynamicContentService = ServiceRegister::getService(DynamicContentService::CLASS_NAME);
            $this->dynamicContentService = $dynamicContentService;
        }

        return $this->dynamicContentService;
    }

    /**
     * @return Proxy
     */
    protected function getDynamicContentProxy()
    {
        if ($this->dynamicContentProxy === null) {
            /** @var Proxy $proxy */
            $proxy = ServiceRegister::getService(Proxy::CLASS_NAME);
            $this->dynamicContentProxy = $proxy;
        }

        return $this->dynamicContentProxy;
    }

    /**
     * Creates new dynamic content, if content with the same name or url already registered, updates existing one
     *
     * @param DynamicContent $content
     *
     * @return DynamicContent|null
     *
     * @throws FailedToRefreshAccessToken
     * @throws FailedToRetrieveAuthInfoException
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     * @throws \Exception
     */
    protected function registerDynamicContent(DynamicContent $content)
    {
        try {
            return $this->getDynamicContentProxy()->create($content);
        } catch (ContentWithSameNameExistsException $exception) {
            Logger::logInfo("Dynamic content already created: {$exception->getMessage()}", 'Core');
            $existingContents = $this->getDynamicContentProxy()->fetchAll();

            $this->reportAlive();

            foreach ($existingContents as $existingContent) {
                if ($this->contentsMatch($content, $existingContent)) {
                    return $this->getDynamicContentProxy()->update($existingContent->getId(), $content);
                }
            }
        }

        return null;
    }

    /**
     * Checks if the names or the urls are the same
     *
     * @param DynamicContent $newContent
     * @param DynamicContent $existingContent
     *
     * @return bool
     */
    protected function contentsMatch(DynamicContent $newContent, DynamicContent $existingContent)
    {
        return $existingContent->getUrl() === $newContent->getUrl() ||
            $existingContent->getName() === $newContent->getName();
    }
}
