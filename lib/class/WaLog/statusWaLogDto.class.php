<?php

/**
 * Class statusWaLogDto
 */
class statusWaLogDto
{
    /**
     * @var string
     */
    public $appId;

    /**
     * @var string
     */
    public $appIcon;

    /**
     * @var string[]
     */
    public $logs;

    /**
     * @var int
     */
    public $count = 0;

    /**
     * statusWaLogDto constructor.
     *
     * @param string $appId
     * @param array  $logs
     */
    public function __construct($appId, array $logs)
    {
        $this->appId = $appId;
        $this->appIcon = wa()->getAppStaticUrl($appId).'img/'.$appId.'48.png';
        $this->logs = $logs;
        $this->count = count($logs);
    }
}
