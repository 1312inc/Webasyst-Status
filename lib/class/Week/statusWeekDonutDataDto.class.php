<?php

/**
 * Class statusWeekDonutDataDto
 */
class statusWeekDonutDataDto
{
    const PROJECT = 'project';
    const USER = 'user';

    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $totalDuration;

    /**
     * @var string
     */
    public $totalDurationStr;

    /**
     * @var string
     */
    public $color;

    /**
     * @var int
     */
    public $percentsInWeek;

    /**
     * @var int
     */
    public $id;

    /**
     * @var array
     */
    public $rotations = [];

    /**
     * @var string
     */
    public $type = '';

    /**
     * statusWeekDonutDataDto constructor.
     *
     * @param int    $id
     * @param string $name
     * @param string $color
     * @param int    $totalDuration
     * @param string $type
     *
     * @throws Exception
     */
    public function __construct($id, $name, $color, $totalDuration, $type = self::PROJECT)
    {
        $this->id = $id;
        $this->name = $name;
        $this->color = $color;
        $this->totalDuration = $totalDuration;
        $this->totalDurationStr = statusTimeHelper::getTimeDurationInHuman(
            0,
            $totalDuration * statusTimeHelper::SECONDS_IN_MINUTE
        );
        $this->type = $type;
    }
}
