<?php

/**
 * Class statusDayEditorCheckinDto
 */
class statusDayEditorCheckinDto
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $comment;

    /**
     * @var int
     */
    public $min = 0;

    /**
     * @var int
     */
    public $max = 0;

    /**
     * @var int
     */
    public $startTimestamp = 0;

    /**
     * @var int
     */
    public $endTimestamp = 0;

    public function __construct(statusCheckin $checkin)
    {
        $this->id = $checkin->getId();
        $this->comment = $checkin->getComment();
        $this->max = $checkin->getEndTime();
        $this->min = $checkin->getStartTime();

        $date = (new DateTime($checkin->getDate()))->setTime(0, 0);
        $this->startTimestamp = $date->getTimestamp() + $checkin->getStartTime();
        $this->endTimestamp = $date->getTimestamp() + $checkin->getEndTime();
    }
}
