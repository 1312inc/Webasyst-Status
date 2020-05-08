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
}
