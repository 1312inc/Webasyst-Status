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
     * Время начала чекина (мин)
     *
     * @var int
     */
    public $min = 0;

    /**
     * Время окончания чекина (мин)
     *
     * @var int
     */
    public $max = 0;

    /**
     * время начала (HH:MM) - время окончания (HH:MM) // таймзона
     *
     * @var string
     */
    public $durationDatetimeTitle = '';

    /**
     * Время начала чекина (unix)
     *
     * @var int
     */
    public $startTimestamp = 0;

    /**
     * Время окончания чекина (unix)
     *
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
     * Перерыв в часах
     *
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
     * Время начала чекина (проценты от дня)
     *
     * @var float
     */
    public $minPercent;

    /**
     * Время окончания чекина (проценты от дня)
     *
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

        $timezone = new DateTimeZone($checkin->getUser()->getContact()->getTimezone());
        if ($this->isTrace) {
            $date = DateTimeImmutable::createFromFormat('Y-m-d|', $checkin->getDate(), $timezone);
        } else {
            $date = DateTimeImmutable::createFromFormat('Y-m-d|', $checkin->getDate());
        }
        $userHourDiff = 0;

        /*
         if ($this->isTrace) {
            $date = DateTimeImmutable::createFromFormat(
                'Y-m-d|',
                $checkin->getDate(),
                wa()->getUser()->getTimezone(true)
            );

            $serverDate = DateTimeImmutable::createFromFormat('Y-m-d|', date('Y-m-d'), wa()->getUser()->getTimezone(true));

            $diff = $serverDate->diff($date);
            $userHourDiff = $diff->h;
        } else {
            $date = DateTimeImmutable::createFromFormat('Y-m-d|', $checkin->getDate());
            $userHourDiff = 0;
        }
         */

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
            '1 ' . _w('h')
        );

        /*
              $this->breakString = statusTimeHelper::getTimeDurationInHuman(
            0,
            (int) $checkin->getBreakDuration() * 60,
            sprintf_wp('%dh', 1)
        );
         */

        $dateStart = $date->modify("+{$this->min} minutes");
        $dateEnd = $date->modify("+{$this->max} minutes");
        $offset = $timezone->getOffset($date) / (statusTimeHelper::SECONDS_IN_MINUTE * statusTimeHelper::MINUTES_IN_HOUR);
        $this->durationDatetimeTitle = sprintf(
            '%s — %s @ GMT%s%s',
            $dateStart->format('H:i'),
            $dateEnd->format('H:i'),
            $offset > 0 ? '+' : '',
            $offset
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
            'projectsDuration' => $this->projectsDuration,
            'projectDurationCss' => $this->projectDurationCss,
            'hasProjects' => $this->hasProjects,
            'isTrace' => $this->isTrace,
        ];
    }
}
