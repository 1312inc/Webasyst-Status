<?php

/**
 * Class statusWeekDonutDto
 */
class statusWeekDonutDto
{
    /**
     * @var statusWeekDonutDataDto[]
     */
    public $datum = [];

    /**
     * @var int
     */
    public $totalDuration;

    /**
     * @var string
     */
    public $totalDurationStr;

    /**
     * @var int
     */
    public $weekNum = 0;

    /**
     * @var bool
     */
    public $chart = true;
}
