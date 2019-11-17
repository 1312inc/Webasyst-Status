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
        $q = 'select sp.*, scp.duration, sc.id checkin_id, scp.id project_checkin_id, concat(sc.id,\'_\',sp.id) checkproj
            from status_project sp
            join status_checkin_projects scp on sp.id = scp.project_id
            join status_checkin sc on sc.id = scp.checkin_id
            where sc.contact_id = i:contact_id and sc.date = s:date';

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
        $q = 'select sp.*, scp.duration, sc.id checkin_id, scp.id project_checkin_id, concat(sc.id,\'_\',sp.id) checkproj
            from status_project sp
            join status_checkin_projects scp on sp.id = scp.project_id
            join status_checkin sc on sc.id = scp.checkin_id
            where sc.contact_id = i:contact_id and sc.date between s:date1 and s:date2';

        return $this->query($q, ['contact_id' => $contactId, 'date1' => $dateStart, 'date2' => $dateEnd])->fetchAll('checkproj');
    }
}
