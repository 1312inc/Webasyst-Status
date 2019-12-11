<?php

/**
 * Class statusProjectModel
 */
class statusProjectModel extends statusModel
{
    /**
     * @var string
     */
    protected $table = 'status_project';

    /**
     * @param string $date
     * @param int    $contactId
     *
     * @return array
     */
    public function getByDateAndContactId($date, $contactId)
    {
        $q = <<<SQL
select sp.*, 
       scp.duration, 
       sc.id checkin_id, 
       scp.id project_checkin_id, 
       concat(sc.id,'_',sp.id) checkproj
from status_project sp
join status_checkin_projects scp on sp.id = scp.project_id
join status_checkin sc on sc.id = scp.checkin_id
where sc.contact_id = i:contact_id 
    and sc.date = s:date
    and sp.is_archived = 0
SQL;


        return $this->query($q, ['contact_id' => $contactId, 'date' => $date])->fetchAll('checkproj');
    }

    /**
     * @param string $dateStart
     * @param string $dateEnd
     * @param int    $contactId
     *
     * @return array
     */
    public function getByDatesAndContactId($dateStart, $dateEnd, $contactId)
    {
        $q = <<<SQL
select sp.*, 
       scp.duration, 
       sc.id checkin_id, 
       scp.id project_checkin_id, concat(sc.id,'_',sp.id) checkproj
from status_project sp
join status_checkin_projects scp on sp.id = scp.project_id
join status_checkin sc on sc.id = scp.checkin_id
where sc.contact_id = i:contact_id 
    and sc.date between s:date1 and s:date2
    and sp.is_archived = 0
SQL;


        return $this
            ->query(
                $q,
                [
                    'contact_id' => $contactId,
                    'date1'      => $dateStart,
                    'date2'      => $dateEnd,
                ]
            )
            ->fetchAll('checkproj');
    }

    /**
     * @param string $dateStart
     * @param string $dateEnd
     * @param int    $contactId
     *
     * @return array
     */
    public function getStatByDatesAndContactId($dateStart, $dateEnd, $contactId)
    {
        $sql = <<<SQL
select sp.id project_id,
       sp.name,
       sp.color,
       sp.is_archived,
       sp.created_by,
       sum(scp.duration) total_duration
from status_project sp
         join status_checkin_projects scp on sp.id = scp.project_id
         join status_checkin sc on sc.id = scp.checkin_id
where sc.contact_id = i:contact_id
  and sc.date between s:date1 and s:date2
  and sp.is_archived = 0
group by sp.id
SQL;

        return $this
            ->query(
                $sql,
                [
                    'contact_id' => $contactId,
                    'date1'      => $dateStart,
                    'date2'      => $dateEnd,
                ]
            )
            ->fetchAll('project_id');
    }

    /**
     * @param string $dateStart
     * @param string $dateEnd
     * @param int    $projectId
     *
     * @return array
     */
    public function getStatByDatesAndProjectId($dateStart, $dateEnd, $projectId)
    {
        $sql = <<<SQL
select max(sc.contact_id) contact_id,
       sum(scp.duration) total_duration
from status_project sp
         join status_checkin_projects scp on sp.id = scp.project_id
         join status_checkin sc on sc.id = scp.checkin_id
where sc.date between s:date1 and s:date2
  and sp.is_archived = 0
  and sp.id = i:project_id
group by sc.contact_id
SQL;

        return $this
            ->query(
                $sql,
                [
                    'project_id' => $projectId,
                    'date1'      => $dateStart,
                    'date2'      => $dateEnd,
                ]
            )
            ->fetchAll();
    }
}
