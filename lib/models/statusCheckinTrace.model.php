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
     * @param int    $contactId
     *
     * @return array
     */
    public function getLastTraceCheckin($date, $endTime, $contactId)
    {
        return $this->query(
            'select * from status_checkin_trace where date = s:today and end_time >= i:end_time and contact_id = i:contact_id limit 1',
            ['today' => $date, 'end_time' => $endTime, 'contact_id' => $contactId]
        )->fetchAssoc();
    }

    /**
     * @param string $dateStart
     * @param string $dateEnd
     * @param int    $contactId
     * @param string $type
     *
     * @return int
     */
    public function countTimeByDatesAndContactId($dateStart, $dateEnd, $contactId, $type): int
    {
        $sql = <<<SQL
select sum(%s) duration_by_user 
from status_checkin_trace sc
where sc.contact_id = i:contact_id 
  and sc.date between s:date1 and s:date2
SQL;

        $select = '';
        switch ($type) {
            case 'total_duration':
                $select = 'sc.total_duration';
                break;

            case 'break_duration':
                $select = 'break_duration';
                break;

            case 'total_duration_with_break':
            default:
                $select = 'sc.total_duration + sc.break_duration';
                break;
        }

        return (int) $this->query(
                sprintf($sql, $select),
                ['contact_id' => $contactId, 'date1' => $dateStart, 'date2' => $dateEnd]
            )
            ->fetchField('duration_by_user');
    }
}
