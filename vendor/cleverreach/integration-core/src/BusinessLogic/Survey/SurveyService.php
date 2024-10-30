<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveUserInfoException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\Contracts\SurveyService as BaseService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\Contracts\SurveyType;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\DTO\PollAnswer;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\DTO\Survey;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class SurveyService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey
 */
class SurveyService implements BaseService
{
    /**
     * @var Proxy
     */
    protected $proxy;
    /**
     * @var SurveyStorageService
     */
    protected $storage;

    /**
     * Returns required type of survey if exits
     *
     * @param string $type Survey type {plugin_installed, initial_sync_finished, first_form_used, periodic}
     * @param string $lang Language in which the survey should be displayed.
     *
     * @return Survey|null
     * @throws QueryFilterInvalidParamException
     */
    public function getSurvey($type, $lang)
    {
        try {
            $survey = $this->getProxy()->getSurvey($type, $lang);
            $id = $survey->getMeta()->getId();
            if (($type === SurveyType::PERIODIC) && $id === $this->getStorage()->getLastPollId()) {
                // skip same poll id
                return null;
            }

            return $survey;
        } catch (HttpRequestException $exception) {
            return null;
        }
    }

    /**
     * Submits an answer to the CleverReach Poll API.
     *
     * @param string $token Token retrieved on requesting poll.
     * @param PollAnswer $pollAnswer
     *
     * @return int HTTP response status
     * @throws FailedToRetrieveUserInfoException
     * @throws QueryFilterInvalidParamException
     */
    public function submitAnswer($token, PollAnswer $pollAnswer)
    {
        $attributes = $pollAnswer->getAttributes();
        if (empty($attributes)) {
            /** @var AuthorizationService $authService */
            $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);
            $customerId = $authService->getUserInfo()->getId();
            $attributes['ID'] = $customerId;
            $attributes['userid'] = $customerId;

            $pollAnswer->setAttributes($attributes);
        }

        $this->getStorage()->setLastPollId($pollAnswer->getPoll());

        return $this->getProxy()->submitAnswer($token, $pollAnswer);
    }

    /**
     * Ignores survey form on CleverReach API.
     *
     * @param string $token Token retrieved on requesting poll.
     * @param string $pollId Poll ID
     * @param string $customerId Customer ID
     *
     * @return int HTTP response status
     */
    public function ignorePoll($token, $pollId, $customerId)
    {
        return $this->getProxy()->ignore($token, $pollId, $customerId);
    }

    /**
     * Retrieves survey proxy.
     *
     * @return Proxy Group proxy instance.
     */
    protected function getProxy()
    {
        if ($this->proxy === null) {
            /** @var Proxy $proxy */
            $proxy = ServiceRegister::getService(Proxy::CLASS_NAME);
            $this->proxy = $proxy;
        }

        return $this->proxy;
    }

    /**
     * @return SurveyStorageService
     */
    public function getStorage()
    {
        if ($this->storage === null) {
            /** @var SurveyStorageService $storage */
            $storage = ServiceRegister::getService(SurveyStorageService::CLASS_NAME);
            $this->storage = $storage;
        }

        return $this->storage;
    }
}
