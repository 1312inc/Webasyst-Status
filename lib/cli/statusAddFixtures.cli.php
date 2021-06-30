<?php

class statusAddFixturesCli extends waCliController
{
    public function run($params = null)
    {
        $dateFrom = waRequest::param('date_from', false, waRequest::TYPE_STRING_TRIM);
        $dateTo = waRequest::param('date_to', false, waRequest::TYPE_STRING_TRIM);
        $limit = waRequest::param('limit_walog', 0, waRequest::TYPE_INT);

        $users = stts()->getEntityRepository(statusUser::class)->findAll();

        $walogModel = new waLogModel();
        $apps = wa()->getApps();

        while (--$limit) {
            do {
                $user = $this->getRandomArray($users);
            } while (strpos($user->getContactId(), '_') !== false);

            $walogs[] =
                [
                    'app_id' => $this->getRandomArray($apps)['id'],
                    'contact_id' => $user->getContactId(),
                    'datetime' => $this->getRandDatetimeBetween($dateFrom, $dateTo),
                    'action' => 'fake action',
                ];
        }
        echo "inserting walogs...\n";
        $walogModel->multipleInsert($walogs);

        $checkinModel = new statusCheckinModel();
        while ($dateFrom <= $dateTo) {
            echo sprintf("checkin %s\n", $dateFrom);
            $startTime = 0;
            $endTime = 10;
            $checkins = [];

            while ($endTime < 1440) {
                do {
                    $user = $this->getRandomArray($users);
                } while (strpos($user->getContactId(), '_') !== false);

                $checkins[] = [
                    'contact_id' => $user->getContactId(),
                    'date' => date('Y-m-d', strtotime($dateFrom)),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'break_duration' => 0,
                    'total_duration' => $endTime - $startTime,
                    'comment' => 'fixture',
                    'timezone' => 0,
                    'create_datetime' => date('Y-m-d H:i:s', strtotime($dateFrom) + $startTime * 60),
                ];

                $startTime = mt_rand($startTime, $endTime);
                $endTime = $startTime + mt_rand(20, 60);
            }
            echo "inserting...\n";
            $checkinModel->multipleInsert($checkins);

            $dateFrom = date('Y-m-d', strtotime($dateFrom . ' +1 day'));
        }
    }

    private function getRandomArray(array $arr)
    {
        return $arr[array_rand($arr)];
    }

    private function getRandDatetimeBetween($date1, $date2, $format = 'Y-m-d H:i:s')
    {
        return date($format, mt_rand(strtotime($date1), strtotime($date2)));
    }
}
