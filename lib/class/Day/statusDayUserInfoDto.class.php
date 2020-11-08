<?php

/**
 * Class statusDayUserInfoDto
 */
class statusDayUserInfoDto
{
    /**
     * @var string
     */
    public $date;

    /**
     * @var statusTodayStatus
     */
    public $todayStatus;

    /**
     * @var statusDayCheckinDto[]
     */
    public $checkins = [];

    /**
     * @var statusDayCheckinDto[]|null
     */
    public $firstCheckin = null;

    /**
     * @var int
     */
    public $startTime = 0;

    /**
     * @var int
     */
    public $endTime = 0;

    /**
     * @var statusWaLogDto[]
     */
    public $walogs = [];

    /**
     * @var statusWaLogDto[]
     */
    public $walogsByDatetime = [];

    /**
     * @var int
     */
    public $contactId;

    /**
     * @var int
     */
    public $realCheckinCount = 0;

    /**
     * statusDayCheckinUserDto constructor.
     *
     * @param $date
     * @param $contactId
     */
    public function __construct($date, $contactId)
    {
        $this->date = $date;
        $this->contactId = $contactId;
    }
}