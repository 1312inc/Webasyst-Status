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
     * @param bool      $explain
     *
     * @return array
     * @throws waException
     */
    public function parseByDays(statusDay $dayStart, statusDay $dayEnd, $contactId, $explain = false)
    {
        $sql = <<<SQL
SELECT l.*,
       date(l.datetime) date,
       c.name contact_name,
       c.photo contact_photo,
       c.firstname,
       c.lastname,
       c.middlename,
       c.company,
       c.is_company,
       c.is_user,
       c.login
from wa_log l
left join wa_contact c ON c.id = i:contact_id
where (contact_id = i:contact_id or subject_contact_id = i:contact_id)
    and datetime between s:date1 and s:date2
SQL;

        $logs = $this->model->query(
            $sql,
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

        if ($explain) {
            foreach ($logsByApp as $appId => $entries) {
                if (wa()->appExists($appId)) {
                    $logsByApp[$appId] = $this->getLogs($entries);
                }
            }
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

    /**
     * @param array $rows
     *
     * @return array
     * @throws waException
     */
    private function getLogs(array $rows = [])
    {
        $apps = wa()->getApps(true);
        $apps_rows = [];
        $prev = [];
        foreach ($rows as $row_id => &$row) {
            if ($prev) {
                $flag = true;
                foreach (['app_id', 'action', 'contact_id', 'subject_contact_id', 'params'] as $k) {
                    if ($prev[$k] != $row[$k]) {
                        $flag = false;
                        break;
                    }
                }
                if ($flag) {
                    unset($rows[$row_id]);
                    continue;
                }
            }
            $row['name'] = $row['contact_name'];
            $contact_name = waContactNameField::formatName($row);
            unset($row['name']);
            if ($contact_name) {
                $row['contact_name'] = $contact_name;
            }
            if ($row['is_user']) {
                $row['contact_photo_url'] = waContact::getPhotoUrl($row['contact_id'], $row['contact_photo'], 32, 32);
            }
            if (!empty($apps[$row['app_id']])) {
                $row['app'] = $apps[$row['app_id']];
                if (empty($apps_rows[$row['app_id']])) {
                    waLocale::loadByDomain($row['app_id']);
                }
                $logs = wa($row['app_id'])->getConfig()->getLogActions(true);
                $row['action_name'] = ifset($logs[$row['action']]['name'], $row['action']);
                if (strpos($row['action'], 'del')) {
                    $row['type'] = 4;
                } elseif (strpos($row['action'], 'add')) {
                    $row['type'] = 3;
                } else {
                    $row['type'] = 1;
                }
                $apps_rows[$row['app_id']][$row_id] = $row;
            } else {
                $row['app'] = [
                    'name' => $row['app_id'],
                ];
                $row['action_name'] = $row['action'];
                $row['type'] = 1;
            }

            $prev = $row;
            unset($row);
        }
        unset($row);

        foreach ($apps_rows as $app_id => $app_rows) {
            $app_rows = wa($app_id)->getConfig()->explainLogs($app_rows);
            foreach ($app_rows as $row_id => $row) {
                if ($row) {
                    $rows[$row_id] = $row;
                } else {
                    unset($rows[$row_id]);
                }
            }
        }

        $rows = array_values($rows);

        return $rows;
    }

}
