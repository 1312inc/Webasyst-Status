<?php

/**
 * Class statusDay
 */
class statusDay
{
    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var bool
     */
    private $isToday = false;

    /**
     * @var statusWeek
     */
    private $week;

    /**
     * @var statusCheckin[]
     */
    private $checkins;

    /**
     * statusDay constructor.
     *
     * @param DateTimeInterface $date
     */
    public function __construct(DateTimeInterface $date)
    {
        $this->date = $date;

        if ($this->date->format('Y-m-d') === date('Y-m-d')) {
            $this->isToday = true;
        }
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param statusCheckin[] $checkins
     *
     * @return statusDay
     */
    public function setCheckins($checkins)
    {
        $this->checkins = $checkins;

        return $this;
    }

    /**
     * @return statusCheckin[]
     */
    public function getCheckins()
    {
        return $this->checkins;
    }

    /**
     * @return statusWeek
     */
    public function getWeek()
    {
        return $this->week;
    }

    /**
     * @param statusWeek $week
     *
     * @return statusDay
     */
    public function setWeek($week)
    {
        $this->week = $week;

        return $this;
    }

    /**
     * @return bool
     */
    public function isToday()
    {
        return $this->isToday;
    }
}
