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
left join (select max(id) id, project_id from status_checkin_projects group by project_id) t on t.project_id = sp.id
order by t.id desc
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
