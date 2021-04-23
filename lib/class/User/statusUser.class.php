<?php

/**
 * Class statusUser
 */
class statusUser extends statusAbstractEntity
{
    use kmwaWaUserTrait {
        kmwaWaUserTrait::setContact as setContactParent;
    }

    /**
     * @var int
     */
    private $id;

    /**
     * @var DateTime|string
     */
    private $last_checkin_datetime;

    /**
     * @var statusTodayStatus
     */
    private $todayStatus;

    /**
     * @var int
     */
    private $this_week_total_duration = 0;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * statusUser constructor.
     */
    public function __construct()
    {
        $this->todayStatus = new statusTodayStatus();
    }

    /**
     * @return statusTodayStatus|null
     */
    public function getTodayStatus()
    {
        return $this->todayStatus;
    }

    /**
     * @param statusTodayStatus|null $todayStatus
     *
     * @return self
     */
    public function setTodayStatus(statusTodayStatus $todayStatus = null)
    {
        $this->todayStatus = $todayStatus;

        return $this;
    }

    /**
     * @return DateTime|string
     */
    public function getLastCheckinDatetime()
    {
        return $this->last_checkin_datetime;
    }

    /**
     * @param DateTime|string $last_checkin_datetime
     *
     * @return self
     */
    public function setLastCheckinDatetime($last_checkin_datetime)
    {
        $this->last_checkin_datetime = $last_checkin_datetime;

        return $this;
    }

    /**
     * @return int
     */
    public function getThisWeekTotalDuration()
    {
        return $this->this_week_total_duration;
    }

    /**
     * @param int $this_week_total_duration
     *
     * @return self
     */
    public function setThisWeekTotalDuration($this_week_total_duration)
    {
        $this->this_week_total_duration = $this_week_total_duration;

        return $this;
    }

    public function setContact(waContact $contact)
    {
        // dirty hack to prevent other trait logic
        $realId = $this->id;
        $this->setContactParent($contact);
        $this->id = $realId;

        return $this;
    }
}
