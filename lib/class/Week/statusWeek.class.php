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

        $sourceDay->setISODate($sourceDay->format('Y'), $this->number);

        $today = new DateTime(date('Y-m-d'));
        for ($day = 6; $day >= 0; $day--) {
            $dayDate = new DateTime($sourceDay->format('Y-m-d'));
            $dayDate->modify("+$day days");
            if ($dayDate > $today) {
                continue;
            }

            $this->days[] = (new statusDay($dayDate))->setWeek($this);
            if ($dayDate == $today) {
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
