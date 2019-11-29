<?php

/**
 * Class statusDay
 */
class statusDayDto
{
    /**
     * @var string
     */
    public $date;

    /**
     * @var int
     */
    public $weekNum;

    /**
     * @var string
     */
    public $dayname;

    /**
     * @var string
     */
    public $dayhuman;

    /**
     * @var bool
     */
    public $today;

    /**
     * @var bool
     */
    public $yesterday;

    /**
     * @var statusUserDto[]
     */
    public $users = [];

    /**
     * @var statusDayUserInfoDto[]
     */
    public $userDayInfos = [];

    /**
     * @var bool
     */
    public $isFromCurrentWeek = false;

    /**
     * @var int
     */
    public $checkinCount = 0;

    /**
     * statusDayEditorDto constructor.
     *
     * @param statusDay $day
     */
    public function __construct(statusDay $day)
    {
//        if ($this->checkins) {
//            $this->startTime = PHP_INT_MAX;
//        }
//
//        if ($this->startTime === PHP_INT_MAX) {
//            $this->startTime = 0;
//        }

        $this->date = $day->getDate()->format('Y-m-d');
        $this->today = $day->isToday();
        $this->dayname = $day->getDate()->format('D');
        if ($day->getDate() == new DateTime('yesterday')) {
            $this->yesterday = true;
        }
        $this->weekNum = statusTimeHelper::getWeekNumberByDate($day->getDate());
    }
}
