<?php

final class statusDebugSettings
{
    /**
     * @var bool
     */
    private $showTrace;

    /**
     * @var waAppSettingsModel
     */
    private $appSettingsModel;

    /**
     * statusDebugSettings constructor.
     */
    public function __construct()
    {
        $this->appSettingsModel = new waAppSettingsModel();
        $data = json_decode($this->appSettingsModel->get(statusConfig::APP_ID, 'debug'), true);
        $this->showTrace = isset($data['show_trace']) ? (bool) $data['show_trace'] : false;
    }

    /**
     * @return bool
     */
    public function isShowTrace(): bool
    {
        return $this->showTrace;
    }

    /**
     * @param bool $showTrace
     *
     * @return statusDebugSettings
     */
    public function setShowTrace(bool $showTrace): statusDebugSettings
    {
        $this->showTrace = $showTrace;

        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return [
            'show_trace' => $this->showTrace,
        ];
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        return $this->appSettingsModel->set(statusConfig::APP_ID, 'debug', json_encode($this->getData()));
    }
}
