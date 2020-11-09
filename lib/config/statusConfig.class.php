<?php

/**
 * Class statusConfig
 */
class statusConfig extends waAppConfig
{
    const APP_ID = 'status';

    /**
     * @var array
     */
    protected $factories = [];

    /**
     * @var array
     */
    protected $models = [];

    /**
     * @var array
     */
    protected $repositories = [];

    /**
     * @var statusUser
     */
    protected $user;

    /**
     * @var kmwaHydratorInterface
     */
    protected $hydrator;

    /**
     * @var kmwaEventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var statusEntityPersist
     */
    protected $persister;

    /**
     * @var statusUser
     */
    protected $contextUser;

    /**
     * @var statusRightConfig
     */
    protected $rightConfig;

    /**
     * @var statusLogger
     */
    private $logger;

    public function __construct($environment, $root_path, $application = null, $locale = null)
    {
        parent::__construct($environment, $root_path, $application, $locale);

        $this->logger = new statusLogger();
    }

    /**
     * @param string $type
     *
     * @return waCache
     */
    public function getCache($type = 'default')
    {
        if ($this->cache === null) {
            $this->cache = parent::getCache($type) ?: new waCache(new statusCacheAdapter(['type' => 'file']), 'status');
        }

        return $this->cache;
    }

    /**
     * @return kmwaEventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        if ($this->eventDispatcher === null) {
            $this->eventDispatcher = new kmwaEventDispatcher(
                new statusListenerProvider()
            );
        }

        return $this->eventDispatcher;
    }

    /**
     * @param kmwaEventInterface $event
     *
     * @return array
     */
    public function waDispatchEvent(kmwaEventInterface $event)
    {
        return wa(self::APP_ID)->event($event->getName(), $event);
    }

    /**
     * @param string      $eventName
     * @param object|null $object
     * @param array       $params
     *
     * @return kmwaListenerResponseInterface
     */
    public function event($eventName, $object = null, $params = [])
    {
        return $this->getEventDispatcher()->dispatch(new statusEvent($eventName, $object, $params));
    }

    /**
     * @return kmwaHydratorInterface
     */
    public function getHydrator()
    {
        if ($this->hydrator === null) {
            $this->hydrator = new kmwaHydrator();
        }

        return $this->hydrator;
    }

    /**
     * @param $entity
     *
     * @return statusBaseFactory|statusUserFactory
     */
    public function getEntityFactory($entity)
    {
        if (isset($this->factories[$entity])) {
            return $this->factories[$entity];
        }

        $factoryClass = sprintf('%sFactory', $entity);

        if (!class_exists($factoryClass)) {
            return $this->factories['']->setEntity($entity);
        }

        $this->factories[$entity] = new $factoryClass();
        $this->factories[$entity]->setEntity($entity);

        return $this->factories[$entity];
    }


    /**
     * @param $entity
     *
     * @return statusModel
     * @throws waException
     */
    public function getModel($entity = false)
    {
        if ($entity === false) {
            return $this->models[''];
        }

        if (isset($this->models[$entity])) {
            return $this->models[$entity];
        }

        $modelClass = sprintf('%sModel', $entity);

        if (!class_exists($modelClass)) {
            throw new waException(sprintf('No model class for %s', $entity));
        }

        $this->models[$entity] = new $modelClass();

        return $this->models[$entity];
    }

    /**
     * @param string $entity
     *
     * @return statusBaseRepository|statusUserRepository
     * @throws waException
     */
    public function getEntityRepository($entity)
    {
        if (isset($this->repositories[$entity])) {
            return $this->repositories[$entity]->resetLimitAndOffset();
        }

        $repositoryClass = sprintf('%sRepository', $entity);

        if (!class_exists($repositoryClass)) {
            throw new waException(sprintf('No repository class for %s', $entity));
        }

        $this->repositories[$entity] = new $repositoryClass();

        return $this->repositories[$entity];
    }

    public function init()
    {
        parent::init();

        $this->models[''] = new statusModel();
        $this->factories[''] = new statusBaseFactory();
        $this->repositories[''] = new statusBaseRepository();

        $this->registerGlobal();
        $this->loadVendors();
    }

    /**
     * @return cashLogger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param null $name
     *
     * @return array|mixed|null
     */
    public function getCronJob($name = null)
    {
        static $tasks;
        if (!isset($tasks)) {
            $tasks = [];
            $path = $this->getAppConfigPath('cron');
            if (file_exists($path)) {
                $tasks = include($path);
            } else {
                $tasks = [];
            }
        }

        return $name ? (isset($tasks[$name]) ? $tasks[$name] : null) : $tasks;
    }

    /**
     * @return string
     */
    public function getUtf8mb4ColumnsPath()
    {
        return wa()->getAppPath('lib/config/utf8mb4.php', pocketlistsHelper::APP_ID);
    }

    /**
     * @return statusUser
     * @throws waException
     */
    public function getUser()
    {
        if ($this->user === null) {
            $this->user = $this->getEntityRepository(statusUser::class)->findByContact(wa()->getUser());
        }

        return $this->user;
    }

    /**
     * @return array
     */
    public function getDefaultViewVars()
    {
        return [
            'backend_url' => $this->getBackendUrl(true),
            'plurl' => wa()->getAppUrl(pocketlistsHelper::APP_ID),
            'current_user' => $this->getUser(),
            'pl2_attachments_path' => wa()->getDataUrl('attachments/', true, pocketlistsHelper::APP_ID),
            'wa_app_static_url' => wa()->getAppStaticUrl(pocketlistsHelper::APP_ID),
            'pl2' => pl2(),
        ];
    }

    /**
     * @return statusEntityPersist
     */
    public function getEntityPersister()
    {
        if ($this->persister === null) {
            $this->persister = new statusEntityPersist();
        }

        return $this->persister;
    }

    /**
     * @return statusUser
     * @throws waException
     */
    public function getContextUser()
    {
        if ($this->contextUser === null) {
            $this->contextUser = $this->getUser();
        }

        return $this->contextUser;
    }

    /**
     * @param statusUser $contextUser
     *
     * @return statusConfig
     */
    public function setContextUser($contextUser)
    {
        $this->contextUser = $contextUser;

        return $this;
    }

    /**
     * @return statusRightConfig
     */
    public function getRightConfig()
    {
        if ($this->rightConfig === null) {
            $this->rightConfig = new statusRightConfig();
        }

        return $this->rightConfig;
    }

    /**
     * @param bool $onlycount
     *
     * @return int|null|array
     * @throws waException
     */
    public function onCount($onlycount = false)
    {
        $idle = filter_var(waRequest::request('idle', false), FILTER_VALIDATE_BOOLEAN);
        $app = wa()->getApp();
        $user = stts()->getUser();

        $url = $this->getBackendUrl(true) . $this->application . '/';
        if (!$user instanceof statusUser) {
            return ['count' => null, 'url' => $url];
        }

        try {
            (new statusAutoTrace($user))->addCheckin($idle, $app);
        } catch (Exception $ex) {
            stts()->getLogger()->error('Error on auto trace handle', $ex);
        }

        if (!(new statusServiceStatusChecker())->hasActivityYesterday($user)) {
            return ['count' => 1, 'url' => $url.'#/y'];
        }

        return ['count' => null, 'url' => $url];

    }

    /**
     * @return bool
     */
    public function canShowTrace(): bool
    {
        return (bool) $this->getInfo('show_trace');
    }

    private function registerGlobal()
    {
        if (!function_exists('stts')) {
            /**
             * @return statusConfig|SystemConfig|waAppConfig
             */
            function stts()
            {
                return wa(statusConfig::APP_ID)->getConfig();
            }
        }
    }

    protected function loadVendors()
    {
        $kmwaLoaderClassPath = 'lib/vendor/kmwa/Wa/kmwaWaConfigHelper.php';
        $appPath = wa()->getAppPath($kmwaLoaderClassPath, self::APP_ID);

        if (!class_exists('kmwaWaConfigHelper', false) && file_exists($appPath)) {
            require_once $appPath;
            (new kmwaWaConfigHelper)->loadKmwaClasses(self::APP_ID);
        }
    }
}
