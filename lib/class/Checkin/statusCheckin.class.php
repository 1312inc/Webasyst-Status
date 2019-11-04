<?php

/**
 * Class statusCheckin
 */
class statusCheckin extends statusAbstractEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $contact_id;

    /**
     * @var DateTime|string
     */
    private $date;

    /**
     * @var int
     */
    private $start_time = 0;

    /**
     * @var int
     */
    private $end_time = 0;

    /**
     * @var int
     */
    private $break_duration = 0;

    /**
     * @var int
     */
    private $total_duration = 0;

    /**
     * @var string|null
     */
    private $comment;

    /**
     * @var int
     */
    private $timezone;

    /**
     * @var DateTime|string
     */
    private $create_datetime;

    /**
     * @var DateTime|string
     */
    private $update_datetime;

    /**
     * @var statusUser
     */
    private $user;

    /**
     * statusCheckin constructor.
     *
     * @throws waException
     */
    public function __construct()
    {
        $this->timezone = (new DateTimeZone(stts()->getUser()->getContact()->getTimezone()))->getOffset(new DateTime()) / 60 / 60;
        $this->contact_id = stts()->getUser()->getContactId();
    }

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
     * @return statusCheckin
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getContactId()
    {
        return $this->contact_id;
    }

    /**
     * @param int $contact_id
     *
     * @return statusCheckin
     */
    public function setContactId($contact_id)
    {
        $this->contact_id = $contact_id;

        return $this;
    }

    /**
     * @return DateTime|string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param DateTime|string $date
     *
     * @return statusCheckin
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return int
     */
    public function getStartTime()
    {
        return $this->start_time;
    }

    /**
     * @param int $start_time
     *
     * @return statusCheckin
     */
    public function setStartTime($start_time)
    {
        $this->start_time = $start_time;

        return $this;
    }

    /**
     * @return int
     */
    public function getEndTime()
    {
        return $this->end_time;
    }

    /**
     * @param int $end_time
     *
     * @return statusCheckin
     */
    public function setEndTime($end_time)
    {
        $this->end_time = $end_time;

        return $this;
    }

    /**
     * @return int
     */
    public function getBreakDuration()
    {
        return $this->break_duration;
    }

    /**
     * @param int $break_duration
     *
     * @return statusCheckin
     */
    public function setBreakDuration($break_duration)
    {
        $this->break_duration = $break_duration;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalDuration()
    {
        return $this->total_duration;
    }

    /**
     * @param int $total_duration
     *
     * @return statusCheckin
     */
    public function setTotalDuration($total_duration)
    {
        $this->total_duration = $total_duration;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     *
     * @return statusCheckin
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param int $timezone
     *
     * @return statusCheckin
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * @return statusUser
     * @throws waException
     */
    public function getUser()
    {
        if ($this->user === null) {
            /** @var statusUserRepository $factory */
            $factory = stts()->getEntityRepository(statusUser::class);
            $this->user = $factory->findByContactId($this->contact_id);
        }

        return $this->user;
    }

    /**
     * @return DateTime|string
     */
    public function getCreateDatetime()
    {
        return $this->create_datetime;
    }

    /**
     * @param DateTime|string $create_datetime
     *
     * @return statusCheckin
     */
    public function setCreateDatetime($create_datetime)
    {
        $this->create_datetime = $create_datetime;

        return $this;
    }

    /**
     * @return DateTime|string
     */
    public function getUpdateDatetime()
    {
        return $this->update_datetime;
    }

    /**
     * @param DateTime|string $update_datetime
     *
     * @return statusCheckin
     */
    public function setUpdateDatetime($update_datetime)
    {
        $this->update_datetime = $update_datetime;

        return $this;
    }

    /**
     * @return bool
     * @throws kmwaLogicException
     */
    public function beforeSave()
    {
        if (!$this->id) {
            $this->create_datetime = date('Y-m-d H:i:s');
        }
        $this->update_datetime = date('Y-m-d H:i:s');

        if ($this->start_time > $this->end_time) {
            list($this->end_time, $this->start_time) = [$this->start_time, $this->end_time];
        }

        $this->total_duration = $this->end_time - $this->start_time;
        $this->break_duration = min(($this->break_duration * statusTimeHelper::MINUTES_IN_HOUR), statusTimeHelper::MINUTES_IN_DAY);

        if ($this->break_duration + $this->total_duration > statusTimeHelper::MINUTES_IN_DAY) {
            throw new kmwaLogicException('Break and total duration can not be more then 24');
        }

        return true;
    }
}
