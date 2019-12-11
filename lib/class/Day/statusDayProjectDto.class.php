<?php

/**
 * Class statusDayProjectDto
 */
class statusDayProjectDto
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $color;

    /**
     * statusDayCheckinProjectDto constructor.
     *
     * @param statusProject $project
     */
    public function __construct(statusProject $project)
    {
        $this->id = $project->getId();
        $this->name = $project->getName();
        $this->color = $project->getColor();
    }
}
