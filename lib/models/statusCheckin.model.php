<?php

/**
 * Class statusCheckinModel
 */
class statusCheckinModel extends statusModel
{
    /**
     * @var string
     */
    protected $table = 'status_checkin';

    /**
     * @param int      $projectId
     * @param DateTime $date
     *
     * @return array
     */
    public function getByProjectIdAndDate($projectId, DateTime $date)
    {
        $sql = <<<SQL
select sc.* from status_checkin sc 
join status_checkin_projects scp on sc.id = scp.checkin_id 
where scp.project_id = i:project_id 
  and sc.date = s:date
SQL;

        return $this->query($sql, ['project_id' => $projectId, 'date' => $date->format('Y-m-d')])
            ->fetchAll();
    }

    /**
     * @param string $dateStart
     * @param string $dateEnd
     *
     * @return array
     */
    public function getByPeriod($dateStart, $dateEnd)
    {
        $sql = <<<SQL
select sc.* from status_checkin sc 
where sc.date between s:date1 and s:date2
SQL;

        return $this->query($sql, ['date1' => $dateStart->format('Y-m-d'), 'date2' => $dateEnd->format('Y-m-d')])
            ->fetchAll();
    }

    /**
     * @param int|array $contactIds
     * @param string    $dateStart
     * @param string    $dateEnd
     * @param int|null  $projectId
     *
     * @return array
     */
    public function getByContactIdsAndPeriod($contactIds, $dateStart, $dateEnd, $projectId = null)
    {
        if (!is_array($contactIds)) {
            $contactIds = [$contactIds];
        }

        if (empty($contactIds)) {
            return [];
        }

        $filterByProjectSql = '';
        if ($projectId) {
            $filterByProjectSql = ' join status_checkin_projects scp on scp.checkin_id = sc.id and scp.project_id = i:project_id';
        }

        $sql = <<<SQL
select sc.* from status_checkin sc
{$filterByProjectSql} 
where sc.date between s:date1 and s:date2 
  and sc.contact_id in (i:contact_ids)
SQL;


        return $this->query(
            $sql,
            [
                'date1' => $dateStart,
                'date2' => $dateEnd,
                'contact_ids' => $contactIds,
                'project_id' => $projectId,
            ]
        )->fetchAll();
    }

    /**
     * @param int|array $contactIds
     * @param string    $dateStart
     * @param string    $dateEnd
     * @param int|null  $projectId
     *
     * @return array
     */
    public function getWithTraceByContactIdsAndPeriod($contactIds, $dateStart, $dateEnd, $projectId = null): array
    {
        if (!is_array($contactIds)) {
            $contactIds = [$contactIds];
        }

        if (empty($contactIds)) {
            return [];
        }

        $filterByProjectSql = '';
        if ($projectId) {
            $filterByProjectSql = ' join status_checkin_projects scp on scp.checkin_id = sc.id and scp.project_id = i:project_id';
        }

        $sql = <<<SQL
select sc.id,
    sc.contact_id,
    sc.date,
    sc.start_time,
    sc.end_time,
    sc.break_duration,
    sc.total_duration,
    sc.comment,
    sc.timezone,
    sc.create_datetime,
    sc.update_datetime,
    0 trace
from status_checkin sc
{$filterByProjectSql} 
where sc.date between s:date1 and s:date2 
  and sc.contact_id in (i:contact_ids)
union all
select sc.id,
    sc.contact_id,
    sc.date,
    sc.start_time,
    sc.end_time,
    sc.break_duration,
    sc.total_duration,
    sc.comment,
    sc.timezone,
    sc.create_datetime,
    sc.update_datetime,
    1 trace
from status_checkin_trace sc
{$filterByProjectSql} 
where sc.date between s:date1 and s:date2 
  and sc.contact_id in (i:contact_ids)
SQL;


        return $this->query(
            $sql,
            [
                'date1' => $dateStart,
                'date2' => $dateEnd,
                'contact_ids' => $contactIds,
                'project_id' => $projectId,
            ]
        )->fetchAll();
    }

    /**
     * @param string   $dateStart
     * @param string   $dateEnd
     * @param null|int $contactId
     *
     * @return array
     */
    public function countTimeByDates($dateStart, $dateEnd, $contactId = null)
    {
        $byUserSql = '';
        if ($contactId) {
            if (!is_array($contactId)) {
                $contactId = [$contactId];
            }
            $byUserSql = ' and sc.contact_id in (i:contact_id)';
        }

        $sql = <<<SQL
select sc.contact_id, 
       sum(sc.total_duration) duration_by_user
from status_checkin sc
where sc.date between s:date1 and s:date2
{$byUserSql}
group by sc.contact_id
SQL;

        return $this->query($sql, ['date1' => $dateStart, 'date2' => $dateEnd, 'contact_id' => $contactId])
            ->fetchAll('contact_id', 1);
    }

    /**
     * @param string   $dateStart
     * @param string   $dateEnd
     * @param null|int $projectId
     *
     * @return array
     */
    public function countTimeByDatesWithProjects($dateStart, $dateEnd, $projectId = null)
    {
        $byProjectSql = '';
        if ($projectId) {
            if (!is_array($projectId)) {
                $projectId = [$projectId];
            }
            $byProjectSql = ' and scp.project_id in (i:project_id)';
        }

        $sql = <<<SQL
select scp.project_id, 
       scp.duration,
       sc.contact_id
from status_checkin sc
join status_checkin_projects scp on sc.id = scp.checkin_id
where sc.date between s:date1 and s:date2
{$byProjectSql}
SQL;

        return $this->query($sql, ['date1' => $dateStart, 'date2' => $dateEnd, 'project_id' => $projectId])
            ->fetchAll('project_id', 2);
    }

    /**
     * @param string $dateStart
     * @param string $dateEnd
     * @param int    $contactId
     *
     * @return int
     */
    public function countTimeByDatesAndContactId($dateStart, $dateEnd, $contactId)
    {
        $sql = <<<SQL
select sum(sc.total_duration) duration_by_user 
from status_checkin sc
where sc.contact_id = i:contact_id 
  and sc.date between s:date1 and s:date2
SQL;

        return $this->query($sql, ['contact_id' => $contactId, 'date1' => $dateStart, 'date2' => $dateEnd])
            ->fetchField('duration_by_user');
    }

    /**
     * @param string   $dateStart
     * @param string   $dateEnd
     * @param int|null $projectId
     *
     * @return bool|mixed
     */
    public function getContactIdsGroupedByDays($dateStart, $dateEnd, $projectId = null)
    {
        $filterByProjectSql = '';
        if ($projectId) {
            $filterByProjectSql = ' join status_checkin_projects scp on scp.checkin_id = sc.id and scp.project_id = i:project_id';
        }

        $sql = <<<SQL
select sc.date date, sc.contact_id contact_id 
from status_checkin sc
join status_user su on su.contact_id = sc.contact_id
{$filterByProjectSql} 
where sc.date between s:date1 and s:date2
SQL;

        return $this->query($sql, ['date1' => $dateStart, 'date2' => $dateEnd, 'project_id' => $projectId])
            ->fetchAll('date', 2);
    }

    /**
     * @param int $contactId
     *
     * @return int
     * @throws waException
     */
    public function countByUser($contactId)
    {
        return $this->countByField('contact_id', $contactId);
    }
}
