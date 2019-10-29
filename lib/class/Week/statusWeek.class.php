<?php

/**
 * Class statusWeek
 */
class statusWeek
{
    /**
     * @var statusDay[]
     */
    private $days = [];

    /**
     * @var int
     */
    private $number = 0;

    /**
     * @var bool
     */
    private $current = false;

    /**
     * @var statusWeekDonutDto
     */
    private $donut;

    /**
     * statusWeek constructor.
     *
     * @param DateTime $sourceDay
     *
     * @throws Exception
     */
    public function __construct(DateTime $sourceDay)
    {
        $this->number = $sourceDay->format('W');

        $sourceDay->setISODate($sourceDay->format('Y'), $this->number);

        $today = new DateTime(date('Y-m-d'));
        for ($day = 7; $day >= 0; $day--) {
            $dayDate = new DateTime($sourceDay->format('Y-m-d'));
            $dayDate->modify("+$day days");
            if ($dayDate > $today) {
                continue;
            }

            $this->days[] = (new statusDay($dayDate))->setWeek($this);
            if ($dayDate == $today) {
                $this->current = true;
            }
        }
    }

    /**
     * @return statusDay[]
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return bool
     */
    public function isCurrent()
    {
        return $this->current;
    }

    /**
     * @return statusWeekDonutDto
     * @throws waException
     */
    public function makeDonut()
    {
        if ($this->donut === null) {
            $this->donut = new statusWeekDonutDto();
            $this->donut->week = $this;
            $pNum = 1;
            /** @var statusProject $project */
            foreach (stts()->getEntityRepository(statusProject::class)->findByWeek($this) as $project) {
                $projectDto = new statusWeekDonutProjectDto();
                $projectDto->name = $project->getName();
                $projectDto->time = 0;
                $projectDto->color = $project->getColor();
                $projectDto->num = $pNum++;
                $projectDto->rotate = 0;

                $this->donut->projects[] = $projectDto;
            }
        }

        return $this->donut;
    }
}
