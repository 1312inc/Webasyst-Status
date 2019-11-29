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
     * @return statusBaseRepository
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

    public function onInit()
    {
//        $wa = wa();
//        $id = $wa->getUser()->getId();
//        if ($id && ($wa->getApp() == 'pocketlists') && ($wa->getEnv() == 'backend')) {
//            $this->setCount($this->onCount());
//        }
    }

    public function explainLogs($logs)
    {
        return $logs;
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
     * @return int|null|array
     * @throws waException
     */
    public function onCount($onlycount = false)
    {
        $yesterday = new DateTime('yesterday');

        /** @var statusCheckinModel $model */
        $model = stts()->getModel(statusCheckin::class);
        $contactId = wa()->getUser()->getId();
        $count = $model->countTimeByDates(
            date('Y-m-d', strtotime('-2 days')),
            $yesterday->format('Y-m-d'),
            $contactId
        );

        $yesterdayStatus = statusTodayStatusFactory::getForContactId($contactId, $yesterday);

        $url = $this->getBackendUrl(true).$this->application.'/';
        if (!isset($count[$contactId]) && !$yesterdayStatus->getStatusId()) {
            $url .= '#/y';

            return ['count' => 1, 'url' => $url];
        }

        return null;

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

    private function loadVendors()
    {
        $customClasses = [
            'lib/vendor/kmwa/Assert' => [
                'kmwaAssert',
                'kmwaAssertException',
            ],
            'lib/vendor/kmwa/Event' => [
                'kmwaEvent',
                'kmwaEventDispatcher',
                'kmwaEventDispatcherInterface',
                'kmwaEventInterface',
                'kmwaListenerProviderInterface',
                'kmwaListenerResponse',
                'kmwaListenerResponseInterface',
                'kmwaStoppableEventInterface',
            ],
            'lib/vendor/kmwa/Exception' => [
                'kmwaForbiddenException',
                'kmwaLogicException',
                'kmwaNotFoundException',
                'kmwaNotImplementedException',
            ],
            'lib/vendor/kmwa/Hydrator' => [
                'kmwaHydratableInterface',
                'kmwaHydrator',
                'kmwaHydratorInterface',
            ],
            'lib/vendor/kmwa/Wa/View' => [
                'kmwaWaJsonActions',
                'kmwaWaJsonController',
                'kmwaWaViewAction',
                'kmwaWaViewActions',
                'kmwaWaViewTrait',
            ],
        ];

        foreach ($customClasses as $path => $classes) {
            foreach ($classes as $class) {
                $file = wa()->getAppPath(sprintf('%s/%s.php', $path, $class), self::APP_ID);
                if (!class_exists($class, false) && file_exists($file)) {
                    waAutoload::getInstance()->add(
                        $class,
                        sprintf('wa-apps/%s/%s/%s.php', self::APP_ID, $path, $class)
                    );
                }
            }
        }
    }
}
