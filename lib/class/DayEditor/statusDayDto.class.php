<?php

/**
 * Class statusDayDto
 */
class statusDayDto
{
    /**
     * @var string
     */
    public $date;

    /**
     * @var string
     */
    public $dayname;

    /**
     * @var bool
     */
    public $today;

    /**
     * @var statusDayCheckinDto[]
     */
    public $checkins;

    /**
     * @var statusDayCheckinDto[]
     */
    public $firstCheckin;

    /**
     * @var int
     */
    public $startTime = 0;

    /**
     * @var int
     */
    public $endTime = 0;

    /**
     * @var bool
     */
    public $isFromCurrentWeek = false;

    /**
     * @var statusWaLogDto[]
     */
    public $walogs = [];

    /**
     * statusDayEditorDto constructor.
     *
     * @param statusDay       $day
     * @param statusCheckin[] $checkins
     *
     * @throws Exception
     */
    public function __construct(statusDay $day, array $checkins)
    {
        $this->startTime = PHP_INT_MAX;
        foreach ($checkins as $checkin) {
            $this->startTime = min($this->startTime, $checkin->getStartTime());
            $this->endTime = max($this->endTime, $checkin->getEndTime());
            $this->checkins[] = new statusDayCheckinDto($checkin);
        }

        if (empty($this->checkins)) {
            $this->checkins[] = new statusDayCheckinDto(
                stts()->getEntityFactory(statusCheckin::class)->createNew()
            );
        }

        $this->firstCheckin = $this->checkins[0];

        if ($this->startTime === PHP_INT_MAX) {
            $this->startTime = 0;
        }

        $this->date = $day->getDate()->format('Y-m-d');
        $this->today = $day->isToday();
        $this->dayname = $day->getDate()->format('D');
    }
}
