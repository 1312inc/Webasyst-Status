<?php

/**
 * Class statusListenerProvider
 */
class statusListenerProvider implements kmwaListenerProviderInterface
{
    const EVENT_HANDLERS_KEY = 'status_event_handlers';
    const EVENT_HANDLERS_TTL = 300;

    /**
     * @var array
     */
    protected $handlers;

    /**
     * statusListenerProvider constructor.
     */
    public function __construct()
    {
        if (!is_array($this->handlers)) {
            $this->getAllHandlers();
            stts()->getCache()->set(
                self::EVENT_HANDLERS_KEY,
                $this->handlers,
                self::EVENT_HANDLERS_TTL
            );
        }
    }

    /**
     * @param kmwaEventInterface $event
     *
     * @return iterable[callable]
     */
    public function getListenersForEvent(kmwaEventInterface $event)
    {
        return isset($this->handlers[$event->getName()]) ? $this->handlers[$event->getName()] : [];
    }

    /**
     * @param string $eventConfigFile
     */
    protected function addHandlersToEvent($eventConfigFile)
    {
        if (file_exists($eventConfigFile)) {
            $appEvents = require $eventConfigFile;
            foreach ($appEvents as $eventName => $eventHandler) {
                if (!isset($this->handlers[$eventName])) {
                    $this->handlers[$eventName] = [];
                }

                if (is_array($eventHandler[0])) {
                    $this->handlers[$eventName] += $eventHandler;
                } else {
                    $this->handlers[$eventName][] = $eventHandler;
                }
            }
        }
    }

    protected function getAllHandlers()
    {
        $this->addHandlersToEvent(wa()->getAppPath('lib/config/status_events.php', 'status'));

        $plugins = stts()->getPlugins();
        foreach ($plugins as $pluginId => $plugin) {
            $this->addHandlersToEvent(wa()->getAppPath('plugins/'.$pluginId.'/lib/config/status_events.php', 'status'));
        }
    }
}
