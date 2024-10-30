<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Language\Contracts\TranslationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Notification\Contracts\NotificationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Notification\DTO\Notification;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\ScheduledTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\Contracts\SurveyService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\Contracts\SurveyStorageService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\Contracts\SurveyType;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\DTO\PollData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class SurveyCheckTask
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\Tasks
 */
class SurveyCheckTask extends ScheduledTask
{
    /**
     * @var SurveyService
     */
    protected $surveyService;
    /**
     * @var SurveyStorageService
     */
    protected $surveyStorageService;

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function execute()
    {
        $this->reportProgress(10);
        /** @var TranslationService $translationService */
        $translationService = ServiceRegister::getService(TranslationService::CLASS_NAME);
        /** @var SurveyService $surveyService */
        $surveyService = ServiceRegister::getService(SurveyService::CLASS_NAME);
        $survey = $surveyService->getSurvey(SurveyType::PERIODIC, $translationService->getSystemLanguage());

        if ($survey && $survey->getMeta()->getId() !== $this->getSurveyStorageService()->getLastPollId()) {
            $this->reportProgress(40);

            /** @var NotificationService $notificationService */
            $notificationService = ServiceRegister::getService(NotificationService::CLASS_NAME);
            $notificationService->push($this->createNotification($survey->getMeta()));

            $this->reportProgress(60);
        }

        $this->reportProgress(100);
    }

    /**
     * Creates notification
     *
     * @param PollData $pollData
     *
     * @return Notification
     * @throws \Exception
     */
    protected function createNotification(PollData $pollData)
    {
        $notification = new Notification($pollData->getId(), $pollData->getName());
        $notification->setDescription($this->getSurveyStorageService()->getDefaultMessage());
        $notification->setUrl($this->getSurveyStorageService()->getPopUpUrl());
        $notification->setDate(new \DateTime());

        return $notification;
    }

    /**
     * @return SurveyStorageService
     */
    protected function getSurveyStorageService()
    {
        if ($this->surveyStorageService === null) {
            /** @var SurveyStorageService $surveyStorageService */
            $surveyStorageService = ServiceRegister::getService(SurveyStorageService::CLASS_NAME);
            $this->surveyStorageService = $surveyStorageService;
        }

        return $this->surveyStorageService;
    }
}
