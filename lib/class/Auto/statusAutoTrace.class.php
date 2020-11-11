<?php

/**
 * Class statusAutoTrace
 */
final class statusAutoTrace
{
    const START_NEW_CHECKIN_TOLERANCE_IN_MINUTES = 15;
    const BREAK_CHECKIN_TOLERANCE_IN_MINUTES     = 5;
    const SETTINGS_NAME                          = 'autoTrace';

    /**
     * @var statusCheckinTraceModel
     */
    private $model;

    /**
     * @var waContactSettingsModel
     */
    private $settingsModel;

    /**
     * @var statusUser
     */
    private $user;

    /**
     * @var array
     */
    private $traceSettings = [];

    /**
     * @var array
     */
    private $debug = [];

    /**
     * @var string
     */
    private $todayWithTime;

    /**
     * @var string
     */
    private $today;

    /**
     * @var int
     */
    private $now;

    /**
     * @var int
     */
    private $currentDayMinutes;

    /**
     * statusAutoTrace constructor.
     *
     * @param statusUser $user
     *
     * @throws waException
     */
    public function __construct(statusUser $user)
    {
        $this->model = stts()->getModel('statusCheckinTrace');
        $this->settingsModel = stts()->getModel('waContactSettings');
        $this->user = $user;

        $this->todayWithTime = date('Y-m-d H:i:s');

        $this->traceSettings = json_decode(
            $this->settingsModel->getOne($this->user->getContactId(), statusConfig::APP_ID, self::SETTINGS_NAME),
            true
        );
        if (!is_array($this->traceSettings)) {
            $this->traceSettings = [
                'break' => 0,
                'checkin_id' => 0,
            ];
        }
        $this->traceSettings['checkin'] = $this->todayWithTime;
        $this->today = date('Y-m-d');
        $this->now = time();
        $this->currentDayMinutes = (int)(($this->now - strtotime($this->today)) / 60);
    }

    /**
     * @param bool        $idle
     * @param null|string $app
     *
     * @throws kmwaLogicException
     * @throws waException
     */
    public function addCheckin($idle, $app = null)
    {
        $lastCheckin = $this->model->getLastTraceCheckin(
            $this->today,
            $this->currentDayMinutes - self::START_NEW_CHECKIN_TOLERANCE_IN_MINUTES,
            $this->user->getContactId()
        );

        if ($lastCheckin) {
            $this->oldCheckin($lastCheckin, $idle, $app);
        } elseif (!$idle) {
            $this->newCheckin($app);
        } else {
            $this->traceSettings = [
                'checkin' => $this->todayWithTime,
                'break' => 0,
                'checkin_id' => 0,
            ];
        }

        $this->settingsModel->set(
            $this->user->getContactId(),
            statusConfig::APP_ID,
            self::SETTINGS_NAME,
            json_encode($this->traceSettings)
        );
    }

    public function __destruct()
    {
        if ($this->debug) {
            stts()->getLogger()->debug($this->debug);
        }
    }

    /**
     * @param array  $lastCheckin
     * @param bool   $idle
     * @param string $app
     *
     * @return bool
     * @throws kmwaLogicException
     * @throws waException
     */
    private function oldCheckin($lastCheckin, $idle, $app)
    {
        /** @var statusCheckin $checkin */
        $checkin = stts()->getHydrator()->hydrate(new statusCheckin(), $lastCheckin);

        $this->debug[] = sprintf(
            'Found trace checkin %d. Current minutes: %s (%s).',
            $checkin->getId(),
            $this->currentDayMinutes,
            $this->todayWithTime
        );

        $apps = json_decode($checkin->getComment(), true);
        if (!$apps) {
            $apps[$this->todayWithTime] = $app;
        } else {
            if (end($apps) !== $app) {
                $apps[$this->todayWithTime] = $app;
            }
        }
        $checkin->setComment(json_encode($apps))
            ->setEndTime($this->currentDayMinutes);

        if ($idle) {
            if (!$this->traceSettings['break']) {
                $this->traceSettings['break'] = $this->currentDayMinutes + self::BREAK_CHECKIN_TOLERANCE_IN_MINUTES;
                $this->debug[] = sprintf(
                    'First idle saved %s (+%s)',
                    $this->traceSettings['break'],
                    self::BREAK_CHECKIN_TOLERANCE_IN_MINUTES
                );
            } elseif ($this->traceSettings['break'] <= $this->currentDayMinutes) {
                $newBreak = $this->traceSettings['break']
                    ? $this->currentDayMinutes - $this->traceSettings['break']
                    : 1;
                $checkin->setBreakDuration($checkin->getBreakDuration() + $newBreak);

                $this->debug[] = sprintf(
                    'Idle for %s minutes. Total checkin break: %s. Save new idle: %s',
                    $newBreak,
                    $checkin->getBreakDuration(),
                    $this->currentDayMinutes
                );

                if ($this->currentDayMinutes - $this->traceSettings['break'] > self::START_NEW_CHECKIN_TOLERANCE_IN_MINUTES) {
                    $this->debug[] = sprintf(
                        'Idle too long (%s - %s). Nothing will do.',
                        $this->currentDayMinutes,
                        $this->traceSettings['break']
                    );

                    return false;
                }

//                    $this->traceSettings['break'] = $this->currentDayMinutes;// - self::BREAK_CHECKIN_TOLERANCE_IN_MINUTES;
            } else {
                $this->debug[] = sprintf(
                    'Waiting idle tolerance: first break %s < %s',
                    $this->traceSettings['break'],
                    $this->currentDayMinutes
                );
            }
        } else {
            $this->traceSettings['break'] = 0;
        }

        $checkin->beforeSave();

        $checkinData = stts()->getHydrator()->extract($checkin);
        $checkinData['counter'] = ($checkin->getDataField('counter') ?: 1) + 1;
        $this->model->updateById($checkin->getId(), $checkinData);
        $this->debug[] = sprintf('Updated trace checkin data: %s', json_encode($checkinData));

        $this->traceSettings['checkin_id'] = $checkin->getId();

        return true;
    }

    /**
     * @param string $app
     *
     * @return bool
     * @throws kmwaLogicException
     * @throws waException
     */
    private function newCheckin($app)
    {
        $totalDuration = 1;
        /** @var statusCheckin $checkin */
        $checkin = stts()->getEntityFactory(statusCheckin::class)->createNew()
            ->setStartTime($this->currentDayMinutes)
            ->setTotalDuration($totalDuration)
            ->setEndTime($this->currentDayMinutes + $totalDuration)
            ->setBreakDuration(0)
            ->setComment(json_encode([$this->todayWithTime => $app]));
        $checkin->beforeSave();

        $checkinData = stts()->getHydrator()->extract($checkin);
        $checkinData['counter'] = 1;
        $id = $this->model->insert($checkinData);

        if ($id) {
            $this->debug[] = sprintf(
                'New trace checkin %d. Current minutes: %s (%s). Reset break if exists.',
                $id,
                $this->currentDayMinutes,
                $this->todayWithTime
            );
            $this->debug[] = sprintf('New trace checkin data: %s', json_encode($checkinData));

            $this->traceSettings['checkin_id'] = $id;
            $this->traceSettings['break'] = 0;
        }

        return true;
    }
}
