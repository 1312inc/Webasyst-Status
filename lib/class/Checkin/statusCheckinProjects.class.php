<?php

/**
 * Class statusCheckinProjects
 */
class statusCheckinProjects extends statusAbstractEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $checkin_id;

    /**
     * @var int
     */
    private $project_id;

    /**
     * @var int
     */
    private $duration = 0;

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
     * @return statusCheckinProjects
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getCheckinId()
    {
        return $this->checkin_id;
    }

    /**
     * @param int $checkin_id
     *
     * @return statusCheckinProjects
     */
    public function setCheckinId($checkin_id)
    {
        $this->checkin_id = $checkin_id;

        return $this;
    }

    /**
     * @return int
     */
    public function getProjectId()
    {
        return $this->project_id;
    }

    /**
     * @param int $project_id
     *
     * @return statusCheckinProjects
     */
    public function setProjectId($project_id)
    {
        $this->project_id = $project_id;

        return $this;
    }

    /**
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param int $duration
     *
     * @return statusCheckinProjects
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }
}
