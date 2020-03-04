<?php

/**
 * Class statusProjectRepository
 *
 * @method statusProjectModel getModel()
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

    public function findByDay(statusDay $day)
    {

    }

    /**
     * @return statusProject[]
     * @throws waException
     */
    public function findAllOrderByLastCheckin()
    {
        $sql = <<<SQL
select sp.*
from status_project sp
order by sp.last_checkin_datetime desc, sp.id desc
SQL;

        return $this->generateWithData($this->getModel()->query($sql), true);
    }

    /**
     * @param statusCheckin $checkin
     *
     * @return statusProject[]
     * @throws waException
     */
    public function findByCheckin(statusCheckin $checkin)
    {
        $sql = <<<SQL
select sp.* from status_project sp
join status_checkin_projects scp on scp.project_id = sp.id and scp.checkin_id = i:checkin_id
SQL;

        return $this->generateWithData($this->getModel()->query($sql, ['checkin_id' => $checkin->getId()]), true);
    }
}
