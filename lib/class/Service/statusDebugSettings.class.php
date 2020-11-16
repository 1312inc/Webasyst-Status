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
     * @var waContact
     */
    private $contact;

    /**
     * statusDebugSettings constructor.
     *
     * @param waContact|null $contact
     *
     * @throws waException
     */
    public function __construct(waContact $contact = null)
    {
        $this->appSettingsModel = new waAppSettingsModel();
        $data = json_decode($this->appSettingsModel->get(statusConfig::APP_ID, 'debug'), true);
        $this->showTrace = isset($data['show_trace']) ? (bool) $data['show_trace'] : false;
        $this->contact = $contact ?: wa()->getUser();
    }

    /**
     * @return bool
     */
    public function isShowTrace(): bool
    {
        return $this->showTrace && stts()->getRightConfig()->isAdmin();
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
