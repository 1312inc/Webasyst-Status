<?php

/**
 * Class statusWeekDonutDto
 */
class statusWeekDonutDto
{
    /**
     * @var statusWeekDonutProjectDto[]
     */
    public $projects = [];

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
}
