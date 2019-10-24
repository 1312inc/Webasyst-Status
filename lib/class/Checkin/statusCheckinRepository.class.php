<?php

/**
 * Class statusCheckinRepository
 */
class statusCheckinRepository extends statusBaseRepository
{
    protected $entity = statusCheckin::class;

    /**
     * @param DateTime $dateTime
     *
     * @return statusCheckin[]
     * @throws waException
     */
    public function findByDate(DateTime $dateTime)
    {
        return $this->findByFields(['date' => $dateTime->format('Y-m-d')], true);
    }
}
