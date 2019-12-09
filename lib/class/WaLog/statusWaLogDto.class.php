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
        $this->appName = wa($appId)->getConfig()->getName();
        $appStatic = wa()->getAppStaticUrl($appId);
        $appStaticAbsolute = wa()->getConfig()->getRootPath().$appStatic;
        $possibleFiles = [
            'img/'.$appId.'48.png',
            'img/'.$appId.'.png',
        ];

        foreach ($possibleFiles as $possibleFile) {
            if (file_exists($appStaticAbsolute.$possibleFile)) {
                $this->appIcon = $appStatic.$possibleFile;
                break;
            }
        }

        $this->logs = $logs;
        $this->count = count($logs);
    }
}
