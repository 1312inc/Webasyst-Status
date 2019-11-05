<?php

/**
 * Class statusProject
 */
class statusProject extends statusAbstractEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $color;

    /**
     * @var DateTime|string
     */
    private $create_datetime;

    /**
     * @var DateTime|string
     */
    private $last_checkin_datetime;

    /**
     * @var int
     */
    private $this_week_total_duration = 0;

    /**
     * @var int
     */
    private $is_archived = 0;

    /**
     * @var int
     */
    private $created_by = 0;

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
     * @return statusProject
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return statusProject
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $color
     *
     * @return statusProject
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return DateTime|string
     */
    public function getCreatedDatetime()
    {
        return $this->create_datetime;
    }

    /**
     * @param DateTime|string $create_datetime
     *
     * @return statusProject
     */
    public function setCreateDatetime($create_datetime)
    {
        $this->create_datetime = $create_datetime;

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
     * @return statusProject
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
     * @return statusProject
     */
    public function setThisWeekTotalDuration($this_week_total_duration)
    {
        $this->this_week_total_duration = $this_week_total_duration;

        return $this;
    }

    /**
     * @return int
     */
    public function getIsArchived()
    {
        return $this->is_archived;
    }

    /**
     * @param int $is_archived
     *
     * @return statusProject
     */
    public function setIsArchived($is_archived)
    {
        $this->is_archived = $is_archived;

        return $this;
    }

    /**
     * @return int
     */
    public function getCreatedBy()
    {
        return $this->created_by;
    }

    /**
     * @param int $created_by
     *
     * @return statusProject
     */
    public function setCreatedBy($created_by)
    {
        $this->created_by = $created_by;

        return $this;
    }
}
