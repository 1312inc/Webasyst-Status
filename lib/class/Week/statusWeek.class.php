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
     * @param DateTime $sourceDay
     *
     * @throws Exception
     */
    public function __construct(DateTime $sourceDay)
    {
        $this->number = statusTimeHelper::getWeekNumberByDate($sourceDay);

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
