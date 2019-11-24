<?php

/**
 * Class statusTodayStatus
 */
class statusTodayStatus implements kmwaHydratableInterface
{
    /**
     * @var int
     */
    private $calendarId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $bgColor;

    /**
     * @var string
     */
    private $fontColor;

    /**
     * @var int
     */
    private $days;

    /**
     * @var int
     */
    private $statusId = 0;

    /**
     * @return int
     */
    public function getCalendarId()
    {
        return $this->calendarId;
    }

    /**
     * @param int $calendarId
     *
     * @return statusTodayStatus
     */
    public function setCalendarId($calendarId)
    {
        $this->calendarId = $calendarId;

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
     * @return statusTodayStatus
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getBgColor()
    {
        return $this->bgColor;
    }

    /**
     * @param string $bgColor
     *
     * @return statusTodayStatus
     */
    public function setBgColor($bgColor)
    {
        $this->bgColor = $bgColor;

        return $this;
    }

    /**
     * @return string
     */
    public function getFontColor()
    {
        return $this->fontColor;
    }

    /**
     * @param string $fontColor
     *
     * @return statusTodayStatus
     */
    public function setFontColor($fontColor)
    {
        $this->fontColor = $fontColor;

        return $this;
    }

    /**
     * @return int
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * @param int $days
     *
     * @return statusTodayStatus
     */
    public function setDays($days)
    {
        $this->days = $days;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatusId()
    {
        return $this->statusId;
    }

    /**
     * @param int $statusId
     *
     * @return statusTodayStatus
     */
    public function setStatusId($statusId)
    {
        $this->statusId = $statusId;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function afterHydrate($data = [])
    {
        // TODO: Implement afterHydrate() method.
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    public function beforeExtract(array &$fields)
    {
        // TODO: Implement beforeExtract() method.
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    public function afterExtract(array &$fields)
    {
        // TODO: Implement afterExtract() method.
    }
}
