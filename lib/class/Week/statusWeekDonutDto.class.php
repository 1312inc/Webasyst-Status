<?php

/**
 * Class statusWeekDonutDto
 */
class statusWeekDonutDto
{
    /**
     * @var statusWeekDonutDataDto[]
     */
    public $data = [];

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

    /**
     * @var bool
     */
    public $hasData = false;
}
