<?php

/**
 * Class statusDayProjectDuration
 */
class statusDayProjectDuration
{
    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var statusDayProjectDto
     */
    public $project;

    /**
     * @var int
     */
    public $duration = 0;

    /**
     * @var string
     */
    public $durationStr = 0;

    /**
     * @var int
     */
    public $durationPercent = 0;

    /**
     * statusDayCheckinProjectDto constructor.
     *
     * @param statusDayProjectDto $project
     * @param int                 $checkinDuration
     * @param int                 $id
     * @param int                 $timeAtDay
     *
     * @throws Exception
     */
    public function __construct(statusDayProjectDto $project, $checkinDuration = 0, $id = 0, $timeAtDay = 0)
    {
        $this->id = $id;
        $this->project = $project;
        $this->duration = $timeAtDay;
        $timeAtDay = $timeAtDay ?: 60;
        $this->durationStr = statusTimeHelper::getTimeDurationInHuman(
            0,
            round($timeAtDay / statusTimeHelper::MINUTES_IN_HOUR, 1) * statusTimeHelper::SECONDS_IN_MINUTE
        );
        if ($checkinDuration) {
            $this->durationPercent = ceil($this->duration / ($checkinDuration / 100));
        }
    }
}
