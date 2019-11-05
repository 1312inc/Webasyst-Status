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
     * @param int    $projectId
     * @param string $date
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
     * @param int    $contactId
     * @param string $dateStart
     * @param string $dateEnd
     *
     * @return array
     */
    public function getByContactIdAndPeriod($contactId, $dateStart, $dateEnd)
    {
        return $this->query(
            'select sc.* from status_checkin sc 
            where sc.date between s:date1 and s:date2 and sc.contact_id = i:contact_id',
            ['date1' => $dateStart, 'date2' => $dateEnd, 'contact_id' => $contactId]
        )->fetchAll();
    }
}
