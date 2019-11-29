<?php

/**
 * Class statusWaLogParser
 */
class statusWaLogParser
{
    /**
     * @var waLogModel
     */
    private $model;

    /**
     * @var waAppConfig[]
     */
    private $appConfigs = [];

    public function __construct()
    {
        $this->model = new waLogModel();
    }

    /**
     * @param statusWeek[] $weeks
     * @param statusUser   $user
     *
     * @return array
     */
    public function parseByWeeks(array $weeks, statusUser $user)
    {
        if (empty($weeks)) {
            return [];
        }

        $firstWeek = $weeks[0];
        $maxDay = $firstWeek->getLastDay();

        $lastWeek = $weeks[count($weeks) - 1];
        $minDay = $lastWeek->getFirstDay();

        return $this->parseByDays($minDay, $maxDay, $user->getContactId());
    }

    /**
     * @param statusDay $dayStart
     * @param statusDay $dayEnd
     * @param int       $contactId
     *
     * @return array
     */
    public function parseByDays(statusDay $dayStart, statusDay $dayEnd, $contactId)
    {
        $logs = $this->model->query(
            'select date(datetime) date, wa_log.* from wa_log 
             where (contact_id = i:contact_id 
                or subject_contact_id = i:contact_id)
                and datetime between s:date1 and s:date2',
            [
                'contact_id' => $contactId,
                'date1' => sprintf('%s 00:00:00', $dayStart->getDate()->format('Y-m-d')),
                'date2' => sprintf('%s 23:59:59', $dayEnd->getDate()->format('Y-m-d')),
            ]
        )->fetchAll('date', 2);

        $logsByApp = [];
        foreach ($logs as $date => $entries) {
            foreach ($entries as $entry) {
                $appId = $entry['app_id'];
                if (!isset($logsByApp[$appId])) {
                    $logsByApp[$appId] = [];
                }
                $entry['date'] = date('Y-m-d', strtotime($entry['datetime']));
                $logsByApp[$appId][] = $entry;
            }
        }

        foreach ($logsByApp as $appId => $entries) {
            $logsByApp[$appId] = wa($appId)->getConfig()->explainLogs($entries);
        }

        $result = [];
        foreach ($logsByApp as $appId => $entries) {
            foreach ($entries as $entry) {
                if (!isset($result[$entry['date']][$appId])) {
                    $result[$entry['date']][$appId] = [];
                }
                $result[$entry['date']][$appId][] = $entry;
            }
        }

        return $result;
    }

    /**
     * @param $appId
     *
     * @return waAppConfig
     */
    private function getConfig($appId)
    {
        if (!isset($this->appConfigs[$appId])) {
            $this->appConfigs[$appId] = wa($appId)->getConfig();
        }

        return $this->appConfigs[$appId];
    }
}
