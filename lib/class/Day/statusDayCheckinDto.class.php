<?php

/**
 * Class statusDayEditorCheckinDto
 */
class statusDayCheckinDto implements JsonSerializable
{
    /**
     * @var int
     */
    public $id = 0;

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

    /**
     * @var int
     */
    public $duration = 0;

    /**
     * @var string
     */
    public $durationString = '';

    /**
     * @var int
     */
    public $break = 1;

    /**
     * @var string
     */
    public $breakString = '';

    /**
     * @var int
     */
    public $contactId;

    /**
     * @var float
     */
    public $minPercent;

    /**
     * @var float
     */
    public $maxPercent;

    /**
     * @var statusDayProjectDurationDto[]
     */
    public $projectsDuration = [];

    /**
     * @var string
     */
    public $projectDurationCss = '';

    /**
     * @var string
     */
    public $projectDurationTitle = '';

    /**
     * @var bool
     */
    public $hasProjects = false;

    /**
     * @var array
     */
    public $projectPercents = [];

    /**
     * @var bool
     */
    public $isTrace = false;

    /**
     * statusDayCheckinDto constructor.
     *
     * @param statusCheckin $checkin
     *
     * @throws Exception
     */
    public function __construct(statusCheckin $checkin)
    {
        $this->isTrace = (bool) $checkin->getDataField('trace');

        if ($this->isTrace) {
            $date = DateTimeImmutable::createFromFormat(
                'Y-m-d|',
                $checkin->getDate(),
                wa()->getUser()->getTimezone(true)
            );
        } else {
            $date = DateTimeImmutable::createFromFormat('Y-m-d|', $checkin->getDate());
        }
        $userHourDiff = 0;

        $this->id = $checkin->getId();
        $this->comment = $checkin->getComment();
        $this->max = ($userHourDiff * statusTimeHelper::MINUTES_IN_HOUR) + $checkin->getEndTime();
        $this->min = ($userHourDiff * statusTimeHelper::MINUTES_IN_HOUR) + $checkin->getStartTime();
        $this->contactId = $checkin->getContactId();

        $this->minPercent = statusTimeHelper::getDayMinutesInPercent($this->min);
        $this->maxPercent = statusTimeHelper::getDayMinutesInPercent($this->max);

        $this->startTimestamp = $date->getTimestamp() + ($checkin->getStartTime() * statusTimeHelper::MINUTES_IN_HOUR);
        $this->endTimestamp = $date->getTimestamp() + ($checkin->getEndTime() * statusTimeHelper::MINUTES_IN_HOUR);
        $this->duration = $checkin->getTotalDuration();
        $this->break = round($checkin->getBreakDuration() / 60, 1);

        $this->durationString = statusTimeHelper::getTimeDurationInHuman(0, $this->duration * 60, '0 ' . _w('h'));

        $this->breakString = statusTimeHelper::getTimeDurationInHuman(
            0,
            (int) $checkin->getBreakDuration() * 60,
            sprintf_wp('%dh', 1)
        );
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'comment' => $this->comment,
            'min' => $this->min,
            'max' => $this->max,
            'minPercent' => $this->minPercent,
            'maxPercent' => $this->maxPercent,
            'startTimestamp' => $this->startTimestamp,
            'endTimestamp' => $this->endTimestamp,
            'duration' => $this->duration,
            'durationString' => $this->durationString,
            'break' => $this->break,
            'breakString' => $this->breakString,
        ];
    }
}
