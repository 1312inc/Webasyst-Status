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
     * @var statusCheckin[]
     */
    private $checkins;

    /**
     * @var statusWeek
     */
    private $week;

    public function __construct(DateTime $date)
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
     * @return statusCheckin[]
     * @throws waException
     */
    public function getCheckins()
    {
        if ($this->checkins === null) {
            $this->checkins = stts()->getEntityRepository(statusCheckin::class)->findByDate($this->getDate());
            $this->checkins = $this->checkins ?: [];
        }

        return $this->checkins;
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
