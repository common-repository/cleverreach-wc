<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\Contracts\DynamicContentHandler as BaseService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Request\DynamicContentRequest;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Request\SearchTerms;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Response\Filter\Filter;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Response\Filter\FilterCollection;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Response\SearchResult\SearchResult;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Response\SearchResult\Settings;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Response\SearchResult\Transformer\Transformer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpAuthenticationException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class DynamicContentHandler
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent
 */
abstract class DynamicContentHandler implements BaseService
{
    /**
     * @var DynamicContentService
     */
    protected $dynamicContentService;

    /**
     * @inheritDoc
     *
     * @throws HttpAuthenticationException
     * @throws QueryFilterInvalidParamException
     */
    public function handle(DynamicContentRequest $request)
    {
        ConfigurationManager::getInstance()->setContext($request->getContext());
        $this->verifyPassword($request->getPassword());
        if ($request->getType() === 'filter') {
            return $this->getFilters()->toArray();
        }

        $results = $this->getSearchResults($request->getSearchTerms());

        return $results ? Transformer::transform($results) : array();
    }

    /**
     * Validates dynamic content password
     *
     * @param string $password
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpAuthenticationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    protected function verifyPassword($password)
    {
        if ($this->getDynamicContentService()->getDynamicContentPassword() !== $password) {
            throw new HttpAuthenticationException("Dynamic content password doesn't match with the stored password", 403);
        }
    }

    /**
     * Returns filters for the dynamic content
     *
     * @return FilterCollection
     */
    abstract protected function getFilters();

    /**
     * Returns search results
     *
     * @param SearchTerms $searchTerms
     *
     * @return SearchResult|null
     */
    abstract protected function getSearchResults(SearchTerms $searchTerms);

    /**
     * Creates filter for the provided parameters
     *
     * @param string $name
     * @param string $key
     * @param string $type
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Response\Filter\Filter
     */
    protected function createFilter($name, $key, $type)
    {
        $filter = new Filter($name, $key, $type);
        $filter->setDescription($name);
        $filter->setRequired(true);

        return $filter;
    }

    /**
     * Creates default settings DTO
     *
     * @param string $type
     *
     * @return Settings
     */
    protected function createDefaultSettings($type)
    {
        $settings = new Settings();
        $settings->setType($type);
        $settings->setLinkEditable(true);
        $settings->setImageSizeEditable(true);
        $settings->setLinkTextEditable(true);

        return $settings;
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
}
