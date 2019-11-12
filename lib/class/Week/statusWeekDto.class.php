<?php

/**
 * Class statusWeekDto
 */
class statusWeekDto
{
    /**
     * @var statusDay[]
     */
    public $days = [];

    /**
     * @var bool
     */
    public $isCurrent = false;

    /**
     * @var statusWeekDonutDto
     */
    public $donut;

    /**
     * @var int
     */
    public $number = 0;

    /**
     * statusWeekDto constructor.
     *
     * @param statusWeek $week
     *
     * @throws waException
     * @throws Exception
     */
    public function __construct(statusWeek $week)
    {
        $this->isCurrent = $week->isCurrent();
        $this->number = $week->getNumber();

        $this->donut = new statusWeekDonutDto();
        $this->donut->week = $this;
        $pNum = 1;
        /** @var statusProject $project */
        foreach (stts()->getEntityRepository(statusProject::class)->findByWeek($week) as $project) {
            $projectDto = new statusWeekDonutProjectDto();
            $projectDto->name = $project->getName();
            $projectDto->time = 0;
            $projectDto->color = $project->getColor();
            $projectDto->num = $pNum++;
            $projectDto->rotate = 0;

            $this->donut->projects[] = $projectDto;
        }
    }
}
