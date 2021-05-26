<?php

/**
 * Class statusCheckinTraceModel
 */
class statusCheckinTraceModel extends statusModel
{
    const TOTAL_DURATION = 'total_duration';
    const BREAK_DURATION = 'break_duration';
    const TOTAL_DURATION_WITH_BREAK = 'total_duration_with_break';

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
     * @param array  $contactIds
     * @param string $type
     *
     * @return int
     * @throws waDbException
     */
    public function countTimeByDatesAndContactId($dateStart, $dateEnd, array $contactIds, $type): int
    {
        $sql = <<<SQL
select sum(%s) duration_by_user 
from status_checkin_trace sc
where sc.contact_id in (i:contact_ids) 
  and sc.date between s:date1 and s:date2
SQL;

        switch ($type) {
            case self::TOTAL_DURATION:
                $select = 'sc.total_duration';
                break;

            case self::BREAK_DURATION:
                $select = 'break_duration';
                break;

            case self::TOTAL_DURATION_WITH_BREAK:
            default:
                $select = 'sc.total_duration + sc.break_duration';
                break;
        }

        return (int) $this->query(
                sprintf($sql, $select),
                ['contact_ids' => $contactIds, 'date1' => $dateStart, 'date2' => $dateEnd]
            )
            ->fetchField('duration_by_user');
    }
}
