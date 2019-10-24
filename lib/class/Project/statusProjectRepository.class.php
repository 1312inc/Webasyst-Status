<?php

/**
 * Class statusProjectRepository
 */
class statusProjectRepository extends statusBaseRepository
{
    protected $entity = statusProject::class;

    /**
     * @param statusWeek $week
     *
     * @return statusProject[]
     */
    public function findByWeek(statusWeek $week)
    {
        return [];
    }
}
