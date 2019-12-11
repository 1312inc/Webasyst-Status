<?php

/**
 * Class statusWeekDto
 */
class statusWeekDto
{
    /**
     * @var statusDayDto[]
     */
    public $days = [];

    /**
     * @var bool
     */
    public $isCurrent = false;

    /**
     * @var statusWeekDonutDto
     */
    public $donut;

    /**
     * @var int
     */
    public $number = 0;

    /**
     * statusWeekDto constructor.
     *
     * @param statusWeek $week
     */
    public function __construct(statusWeek $week)
    {
        $this->isCurrent = $week->isCurrent();
        $this->number = $week->getNumber();
    }
}
