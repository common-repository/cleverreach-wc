<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Contracts\AppStateService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\StateRegister;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\States\AutoConfig;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\States\Dashboard;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\States\InitialSync;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\States\InitialSyncConfig;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\States\Offline;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\States\Welcome;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Events\AuthorizationEventBus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Events\ConnectionLostEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Http\AuthProxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Http\OauthStatusProxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Http\RefreshProxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Http\TokenProxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Http\UserProxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Listeners\ConnectionLostEventListener;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Contracts\AbandonedCartEntityService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Contracts\AbandonedCartRecordService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Contracts\AbandonedCartSettingsService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Events\AbandonedCartConvertedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Events\AbandonedCartConvertedEventListener;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Events\AbandonedCartEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Events\AbandonedCartEventListener;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Events\AbandonedCartEventsBus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Events\AbandonedCartUpdatedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Events\AbandonedCartUpdatedEventListener;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Pipeline\AbandonedCartCreatePipeline;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Pipeline\AbandonedCartTriggerPipeline;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Pipeline\AlreadyProcessedFilter;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Pipeline\CartCreatedFilter;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Pipeline\CartFilter;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Pipeline\ReceiverFilter;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Pipeline\RecordFilter;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Contracts\BlacklistFilterService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Dashboard\Contracts\DashboardService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\BufferConfigurationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Contracts\BufferConfigurationInterface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Repositories\BufferConfigurationRepositoryInterface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Contracts\FieldMapConfigService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Contracts\FieldMapService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Events\EnabledFieldsSetEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Events\FieldEventBus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Events\FieldMapConfigSetEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Events\FieldMapEventBuss;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Listeners\EnabledFieldsSetEventListener;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Listeners\FieldMapConfigSetEventListener;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Contracts\FormCacheService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\WebHooks\Handler;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Events\InitialSyncCompletedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Listeners\InitialSyncCompletedListener;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\AutomationRecordService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\CartAutomationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Listeners\AutomationRecordStatusListener;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Tasks\Trigger\Filter\AutomationFilter;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Tasks\Trigger\Filter\CartDataFilter;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Tasks\Trigger\Filter\FilterChain;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\PaymentPlan\Contracts\PaymentPlanService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Contracts\SyncConfigService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SecondarySynchronization\Contracts\SecondarySyncEnqueueService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats\Contracts\SnapshotService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats\Contracts\StatsService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\Contracts\SurveyService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Events\EnabledServicesSetEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Events\SyncSettingsEventBus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Listeners\EnabledSyncServicesChangeRecorder;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Listeners\SyncSettingsUpdater;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events\QueueItemAbortedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events\QueueItemEnqueuedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events\QueueItemFailedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events\QueueItemFinishedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events\QueueItemStateTransitionEventBus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events\TaskCompletedEventBus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\QueueService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\BootstrapComponent as InfrastructureBootstrap;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\CurlHttpClient;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\HttpClient;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService as BaseQueueService;

/**
 * Class BootstrapComponent
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic
 */
class BootstrapComponent extends InfrastructureBootstrap
{
    /**
     * @return void
     */
    public static function init()
    {
        parent::init();

        static::initProxies();
        static::initWebHookHandlers();
        static::initPipelines();
        static::initStates();
    }

    /**
     * Initializes services and utilities.
     *
     * @return void
     */
    public static function initServices()
    {
        parent::initServices();

        ServiceRegister::registerService(
            BaseQueueService::CLASS_NAME,
            function () {
                return new QueueService();
            }
        );

        ServiceRegister::registerService(
            SyncConfigService::CLASS_NAME,
            function () {
                return Receiver\SyncConfigService::getInstance();
            }
        );

        ServiceRegister::registerService(
            HttpClient::CLASS_NAME,
            function () {
                return new CurlHttpClient();
            }
        );

        ServiceRegister::registerService(
            DashboardService::CLASS_NAME,
            function () {
                return new \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Dashboard\DashboardService();
            }
        );

        ServiceRegister::registerService(
            PaymentPlanService::CLASS_NAME,
            function () {
                return new PaymentPlan\PaymentPlanService();
            }
        );

        ServiceRegister::registerService(
            FormCacheService::CLASS_NAME,
            function () {
                return new Form\FormCacheService();
            }
        );

        ServiceRegister::registerService(
            SurveyService::CLASS_NAME,
            function () {
                return new Survey\SurveyService();
            }
        );

        ServiceRegister::registerService(
            StatsService::CLASS_NAME,
            function () {
                return new Stats\StatsService();
            }
        );
        ServiceRegister::registerService(
            SnapshotService::CLASS_NAME,
            function () {
                return new Stats\SnapshotService();
            }
        );

        ServiceRegister::registerService(
            AbandonedCartSettingsService::CLASS_NAME,
            function () {
                return new Automation\AbandonedCart\AbandonedCartSettingsService();
            }
        );

        ServiceRegister::registerService(
            AbandonedCartEntityService::CLASS_NAME,
            function () {
                return new Automation\AbandonedCart\AbandonedCartEntityService();
            }
        );

        ServiceRegister::registerService(
            AbandonedCartRecordService::CLASS_NAME,
            function () {
                return new Automation\AbandonedCart\AbandonedCartRecordService();
            }
        );

        ServiceRegister::registerService(
            CartAutomationService::CLASS_NAME,
            function () {
                return new Multistore\AbandonedCart\Services\CartAutomationService();
            }
        );

        ServiceRegister::registerService(
            AutomationRecordService::CLASS_NAME,
            function () {
                return new Multistore\AbandonedCart\Services\AutomationRecordService();
            }
        );

        ServiceRegister::registerService(
            AppStateService::CLASS_NAME,
            function () {
                return new AppState\AppStateService();
            }
        );

        ServiceRegister::registerService(
            FieldMapConfigService::CLASS_NAME,
            function () {
                return new Field\FieldMapConfigService();
            }
        );

        ServiceRegister::registerService(
            FieldMapService::CLASS_NAME,
            function () {
                return new Field\FieldMapService();
            }
        );
        ServiceRegister::registerService(
            SecondarySyncEnqueueService::CLASS_NAME,
            function () {
                return new SecondarySynchronization\SecondarySyncEnqueueService();
            }
        );
        ServiceRegister::registerService(
            BlacklistFilterService::CLASS_NAME,
            function () {
                return new \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Service\BlacklistFilterService();
            }
        );

        ServiceRegister::registerService(
            BufferConfigurationInterface::CLASS_NAME,
            function () {
                /** @var BufferConfigurationRepositoryInterface $bufferRepository */
                $bufferRepository = ServiceRegister::getService(BufferConfigurationRepositoryInterface::CLASS_NAME);

                return new BufferConfigurationService($bufferRepository);
            }
        );
    }

    /**
     * Initializes proxies.
     *
     * @return void
     */
    public static function initProxies()
    {
        ServiceRegister::registerService(
            Proxy::CLASS_NAME,
            function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
                /** @var AuthorizationService $authService */
                $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);

                return new Proxy($httpClient, $authService);
            }
        );

        ServiceRegister::registerService(
            Field\Http\Proxy::CLASS_NAME,
            function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
                /** @var AuthorizationService $authService */
                $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);

                return new Field\Http\Proxy($httpClient, $authService);
            }
        );

        ServiceRegister::registerService(
            Segment\Http\Proxy::CLASS_NAME,
            function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
                /** @var AuthorizationService $authService */
                $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);

                return new Segment\Http\Proxy($httpClient, $authService);
            }
        );

        ServiceRegister::registerService(
            Receiver\Http\Proxy::CLASS_NAME,
            function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
                /** @var AuthorizationService $authService */
                $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);

                return new Receiver\Http\Proxy($httpClient, $authService);
            }
        );

        ServiceRegister::registerService(
            UserProxy::CLASS_NAME,
            function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
                /** @var AuthorizationService $authService */
                $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);

                return new UserProxy($httpClient, $authService);
            }
        );

        ServiceRegister::registerService(
            AuthProxy::CLASS_NAME,
            function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);

                return new AuthProxy($httpClient);
            }
        );

        ServiceRegister::registerService(
            RefreshProxy::CLASS_NAME,
            function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);

                return new RefreshProxy($httpClient);
            }
        );

        ServiceRegister::registerService(
            OauthStatusProxy::CLASS_NAME,
            function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
                /** @var AuthorizationService $authService */
                $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);

                return new OauthStatusProxy($httpClient, $authService);
            }
        );

        ServiceRegister::registerService(
            SyncSettings\Http\Proxy::CLASS_NAME,
            function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
                /** @var AuthorizationService $authService */
                $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);

                return new SyncSettings\Http\Proxy($httpClient, $authService);
            }
        );

        ServiceRegister::registerService(
            PaymentPlan\Http\Proxy::CLASS_NAME,
            function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
                /** @var AuthorizationService $authService */
                $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);

                return new PaymentPlan\Http\Proxy($httpClient, $authService);
            }
        );

        ServiceRegister::registerService(
            API\Http\Proxy::CLASS_NAME,
            function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);

                return new API\Http\Proxy($httpClient);
            }
        );

        ServiceRegister::registerService(
            Form\Http\Proxy::CLASS_NAME,
            function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
                /** @var AuthorizationService $authService */
                $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);

                return new Form\Http\Proxy($httpClient, $authService);
            }
        );

        ServiceRegister::registerService(
            WebHookEvent\Http\Proxy::CLASS_NAME,
            function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
                /** @var AuthorizationService $authService */
                $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);

                return new WebHookEvent\Http\Proxy($httpClient, $authService);
            }
        );

        ServiceRegister::registerService(
            Survey\Http\Proxy::CLASS_NAME,
            function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
                /** @var AuthorizationService $authService */
                $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);

                return new Survey\Http\Proxy($httpClient, $authService);
            }
        );

        ServiceRegister::registerService(
            DynamicContent\Http\Proxy::CLASS_NAME,
            function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
                /** @var AuthorizationService $authService */
                $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);

                return new DynamicContent\Http\Proxy($httpClient, $authService);
            }
        );

        ServiceRegister::registerService(
            Mailing\Http\Proxy::CLASS_NAME,
            function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
                /** @var AuthorizationService $authService */
                $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);

                return new Mailing\Http\Proxy($httpClient, $authService);
            }
        );

        ServiceRegister::registerService(
            Report\Http\Proxy::CLASS_NAME,
            function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
                /** @var AuthorizationService $authService */
                $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);

                return new Report\Http\Proxy($httpClient, $authService);
            }
        );

        ServiceRegister::registerService(
            TokenProxy::CLASS_NAME,
            function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
                /** @var AuthorizationService $authService */
                $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);

                return new TokenProxy($httpClient, $authService);
            }
        );

        ServiceRegister::registerService(
            Stats\Http\Proxy::CLASS_NAME,
            function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
                /** @var AuthorizationService $authService */
                $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);

                return new Stats\Http\Proxy($httpClient, $authService);
            }
        );

        ServiceRegister::registerService(
            Automation\AbandonedCart\Http\Proxy::CLASS_NAME,
            function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
                /** @var AuthorizationService $authService */
                $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);

                return new Automation\AbandonedCart\Http\Proxy($httpClient, $authService);
            }
        );

        ServiceRegister::registerService(
            Multistore\Http\Proxy::CLASS_NAME,
            function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
                /** @var AuthorizationService $authService */
                $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);

                return new Multistore\Http\Proxy($httpClient, $authService);
            }
        );

        ServiceRegister::registerService(
            Multistore\AbandonedCart\Http\Proxy::CLASS_NAME,
            function () {
                /** @var HttpClient $httpClient */
                $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
                /** @var AuthorizationService $authService */
                $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);

                return new Multistore\AbandonedCart\Http\Proxy($httpClient, $authService);
            }
        );
    }

    /**
     * Initializes event listeners.
     *
     * @return void
     */
    protected static function initEvents()
    {
        parent::initEvents();

        SyncSettingsEventBus::getInstance()->when(
            EnabledServicesSetEvent::CLASS_NAME,
            EnabledSyncServicesChangeRecorder::CLASS_NAME . '::handle'
        );

        SyncSettingsEventBus::getInstance()->when(
            EnabledServicesSetEvent::CLASS_NAME,
            SyncSettingsUpdater::CLASS_NAME . '::handle'
        );

        TaskCompletedEventBus::getInstance()->when(
            InitialSyncCompletedEvent::CLASS_NAME,
            InitialSyncCompletedListener::CLASS_NAME . '::handle'
        );

        AuthorizationEventBus::getInstance()->when(
            ConnectionLostEvent::CLASS_NAME,
            ConnectionLostEventListener::CLASS_NAME . '::handle'
        );

        AbandonedCartEventsBus::getInstance()->when(
            AbandonedCartConvertedEvent::CLASS_NAME,
            array(new AbandonedCartConvertedEventListener(), 'handle')
        );

        AbandonedCartEventsBus::getInstance()->when(
            AbandonedCartEvent::CLASS_NAME,
            array(new AbandonedCartEventListener(), 'handle')
        );

        AbandonedCartEventsBus::getInstance()->when(
            AbandonedCartUpdatedEvent::CLASS_NAME,
            array(new AbandonedCartUpdatedEventListener(), 'handle')
        );

        QueueItemStateTransitionEventBus::getInstance()->when(
            QueueItemEnqueuedEvent::CLASS_NAME,
            array(new AutomationRecordStatusListener(), 'onEnqueue')
        );

        QueueItemStateTransitionEventBus::getInstance()->when(
            QueueItemFinishedEvent::CLASS_NAME,
            array(new AutomationRecordStatusListener(), 'onComplete')
        );

        QueueItemStateTransitionEventBus::getInstance()->when(
            QueueItemAbortedEvent::CLASS_NAME,
            array(new AutomationRecordStatusListener(), 'onAbort')
        );

        QueueItemStateTransitionEventBus::getInstance()->when(
            QueueItemFailedEvent::CLASS_NAME,
            array(new AutomationRecordStatusListener(), 'onFail')
        );

        FieldEventBus::getInstance()->when(
            EnabledFieldsSetEvent::CLASS_NAME,
            array(new EnabledFieldsSetEventListener(), 'handle')
        );

        FieldMapEventBuss::getInstance()->when(
            FieldMapConfigSetEvent::CLASS_NAME,
            array(new FieldMapConfigSetEventListener(), 'handle')
        );
    }

    /**
     * Initializes web hook handlers.
     *
     * @return void
     */
    protected static function initWebHookHandlers()
    {
        ServiceRegister::registerService(
            Handler::CLASS_NAME,
            function () {
                return new Handler();
            }
        );
    }

    /**
     * Initializes pipelines with filters.
     *
     * @return void
     */
    protected static function initPipelines()
    {
        AbandonedCartCreatePipeline::append(new CartCreatedFilter());
        AbandonedCartCreatePipeline::append(new AlreadyProcessedFilter());
        AbandonedCartCreatePipeline::append(new CartFilter());
        AbandonedCartCreatePipeline::append(new RecordFilter());
        AbandonedCartTriggerPipeline::append(new CartCreatedFilter());
        AbandonedCartTriggerPipeline::append(new AlreadyProcessedFilter());
        AbandonedCartTriggerPipeline::append(new ReceiverFilter());

        FilterChain::append(new AutomationFilter());
        FilterChain::append(new CartDataFilter());
        FilterChain::append(new Multistore\AbandonedCart\Tasks\Trigger\Filter\ReceiverFilter());
    }

    /**
     * Initializes default states.
     *
     * @return void
     */
    protected static function initStates()
    {
        StateRegister::registerState(
            AutoConfig::STATE_CODE,
            function () {
                return new AutoConfig();
            }
        );

        StateRegister::registerState(
            Welcome::STATE_CODE,
            function () {
                return new Welcome();
            }
        );

        StateRegister::registerState(
            InitialSyncConfig::STATE_CODE,
            function () {
                return new InitialSyncConfig();
            }
        );

        StateRegister::registerState(
            InitialSync::STATE_CODE,
            function () {
                return new InitialSync();
            }
        );

        StateRegister::registerState(
            Dashboard::STATE_CODE,
            function () {
                return new Dashboard();
            }
        );

        StateRegister::registerState(
            Offline::STATE_CODE,
            function () {
                return new Offline();
            }
        );
    }
}
