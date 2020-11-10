<?php

/**
 * Class statusCheckinTraceModel
 */
class statusCheckinTraceModel extends statusModel
{
    protected $table = 'status_checkin_trace';

    /**
     * @param string $date
     * @param int    $endTime
     *
     * @return array
     */
    public function getLastTraceCheckin($date, $endTime)
    {
        return $this->query(
            'select * from status_checkin_trace where date = s:today and end_time >= i:end_time limit 1',
            ['today' => $date, 'end_time' => $endTime]
        )->fetchAssoc();
    }

    /**
     * @param string $dateStart
     * @param string $dateEnd
     * @param int    $contactId
     *
     * @return int
     */
    public function countTimeByDatesAndContactId($dateStart, $dateEnd, $contactId): int
    {
        $sql = <<<SQL
select sum(sc.total_duration) duration_by_user 
from status_checkin_trace sc
where sc.contact_id = i:contact_id 
  and sc.date between s:date1 and s:date2
SQL;

        return (int) $this->query($sql, ['contact_id' => $contactId, 'date1' => $dateStart, 'date2' => $dateEnd])
            ->fetchField('duration_by_user');
    }
}
