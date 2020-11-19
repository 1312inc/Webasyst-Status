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
    public $appName;

    /**
     * @var string
     */
    public $appColor = '#777777'; //fallback for apps with no sash color

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
        $info = wa()->getAppInfo($appId);

        $this->appId = $appId;
        $this->appName = $info['name'];

        if (!empty($info['sash_color'])) {
            $this->appColor = $info['sash_color'];
        }

        if (isset($info['icon'][48])) {
            $this->appIcon = '/' . $info['icon'][48];
        }

        $this->logs = $logs;
        $this->count = count($logs);
    }
}
