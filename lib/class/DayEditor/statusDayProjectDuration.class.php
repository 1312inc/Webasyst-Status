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
     * statusDayCheckinProjectDto constructor.
     *
     * @param statusDayProjectDto $project
     * @param int                 $id
     * @param int                 $timeAtDay
     *
     * @throws Exception
     */
    public function __construct(statusDayProjectDto $project, $id = 0, $timeAtDay = 60)
    {
        $this->id = $id;
        $this->project = $project;
        $this->duration = $timeAtDay / statusTimeHelper::MINUTES_IN_HOUR;
        $this->durationStr = statusTimeHelper::getTimeDurationInHuman(
            0,
            $timeAtDay * statusTimeHelper::SECONDS_IN_MINUTE
        );
    }
}
