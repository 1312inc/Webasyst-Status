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
        return $this->query(
            'select sc.* from status_checkin sc 
            join status_checkin_projects scp on sc.id = scp.checkin_id 
            where scp.project_id = i:project_id and
                sc.date = s:date',
            ['project_id' => $projectId, 'date' => $date->format('Y-m-d')]
        )->fetchAll();
    }

    /**
     * @param string $dateStart
     * @param string $dateEnd
     *
     * @return array
     */
    public function getByPeriod($dateStart, $dateEnd)
    {
        return $this->query(
            'select sc.* from status_checkin sc 
            where sc.date between s:date1 and s:date2',
            ['date1' => $dateStart->format('Y-m-d'), 'date2' => $dateEnd->format('Y-m-d')]
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
    public function getByContactIdsAndPeriod($contactIds, $dateStart, $dateEnd, $projectId = null)
    {
        if (!is_array($contactIds)) {
            $contactIds = [$contactIds];
        }

        $filterByProjectSql = '';
        if ($projectId) {
            $filterByProjectSql = ' join status_checkin_projects scp on scp.checkin_id = sc.id and scp.project_id = i:project_id';
        }

        return $this->query(
            "select sc.* from status_checkin sc
            {$filterByProjectSql} 
            where sc.date between s:date1 and s:date2 
                and sc.contact_id in (i:contact_ids)",
            [
                'date1' => $dateStart,
                'date2' => $dateEnd,
                'contact_ids' => $contactIds,
                'project_id' => $projectId,
            ]
        )->fetchAll();
    }

    /**
     * @param string $dateStart
     * @param string $dateEnd
     *
     * @return array
     */
    public function countTimeByDates($dateStart, $dateEnd)
    {
        return $this->query(
            'select 
            su.id, sum(sc.total_duration) duration_by_user 
            from status_checkin sc
            join status_user su on su.contact_id = sc.contact_id
            where sc.date between s:date1 and s:date2
            group by sc.contact_id',
            ['date1' => $dateStart, 'date2' => $dateEnd]
        )->fetchAll('id', 1);
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
        return $this->query(
            'select sum(sc.total_duration) duration_by_user 
            from status_checkin sc
            where sc.contact_id = i:contact_id 
                  and sc.date between s:date1 and s:date2',
            ['contact_id' => $contactId, 'date1' => $dateStart, 'date2' => $dateEnd]
        )->fetchField('duration_by_user');
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

        return $this->query(
            "select sc.date date, sc.contact_id contact_id 
            from status_checkin sc
            join status_user su on su.contact_id = sc.contact_id
            {$filterByProjectSql} 
            where sc.date between s:date1 and s:date2",
            ['date1' => $dateStart, 'date2' => $dateEnd, 'project_id' => $projectId]
        )->fetchAll('date', 2);
    }
}
