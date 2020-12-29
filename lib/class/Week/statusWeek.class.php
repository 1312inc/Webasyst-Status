<?php

/**
 * Class statusWeek
 */
class statusWeek
{
    /**
     * @var statusDay[]
     */
    private $days = [];

    /**
     * @var int
     */
    private $number = 0;

    /**
     * @var bool
     */
    private $current = false;

    /**
     * @var statusWeekDonutDto
     */
    private $donut;

    /**
     * statusWeek constructor.
     *
     * @param DateTimeInterface $sourceDay
     *
     * @throws Exception
     */
    public function __construct(DateTimeInterface $sourceDay)
    {
        $this->number = statusTimeHelper::getWeekNumberByDate($sourceDay);

        // last week bug php
        $nextYearBugDate = clone $sourceDay;
        $nextYearBugDate->modify('+5 days');
        if ($nextYearBugDate->format('Y') > $sourceDay->format('Y')) {
            $prevWeekDay = clone $sourceDay;
            $prevWeekDay->modify('-1 week');
            $this->number = statusTimeHelper::getWeekNumberByDate($prevWeekDay) + 1;
        }

        $sourceDay->setISODate($sourceDay->format('Y'), $this->number)->setTime(0, 0, 0);

        $today = statusTimeHelper::createDatetimeForUser()->setTime(0, 0, 0);
        for ($day = 6; $day >= 0; $day--) {
//            $dayDate = statusTimeHelper::createDatetimeForUser('Y-m-d H:i:s', $sourceDay)->setTime(0, 0, 0);
            $dayDate = (new DateTime($sourceDay->format('Y-m-d')))->setTime(0, 0, 0);
            $dayDate->modify("+$day days");
            if ($dayDate->format('Y-m-d') > $today->format('Y-m-d')) {
                continue;
            }

            $this->days[] = (new statusDay($dayDate))->setWeek($this);
            if ($dayDate->format('Y-m-d') == $today->format('Y-m-d')) {
                $this->current = true;
            }
        }
    }

    /**
     * @return statusDay[]
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * @param statusDay[] $days
     *
     * @return statusWeek
     */
    public function setDays(array $days)
    {
        $this->days = $days;

        return $this;
    }

    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return bool
     */
    public function isCurrent()
    {
        return $this->current;
    }

    /**
     * @return statusDay
     */
    public function getLastDay()
    {
        return $this->days[0];
    }

    /**
     * @return statusDay
     */
    public function getFirstDay()
    {
        return $this->days[count($this->days) - 1];
    }
}
