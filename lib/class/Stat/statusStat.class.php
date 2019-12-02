<?php

/**
 * Class statusStat
 */
class statusStat
{
    /**
     * @var statusCheckinModel
     */
    protected $checkinModel;

    /**
     * statusStat constructor.
     *
     * @throws waException
     */
    public function __construct()
    {
        $this->checkinModel = stts()->getModel(statusCheckin::class);
    }

    /**
     * @param DateTime $date
     *
     * @return array
     * @throws Exception
     * @todo refactor and cache
     */
    public function usersTimeByWeek(DateTime $date)
    {
        $week = statusWeekFactory::createWeekByDate($date);

        $statistics = $this->checkinModel->countTimeByDates(
            $week->getFirstDay()->getDate()->format('Y-m-d'),
            $week->getLastDay()->getDate()->format('Y-m-d')
        );

        $result = [
            0 => [
                'time' => 0,
                'timeStr' => 0,
            ],
        ];
        /** @var statusUser $user */
        foreach (stts()->getEntityRepository(statusUser::class)->findAll() as $user) {
            $time = ifset($statistics, $user->getContactId(), 0);
            $result[0]['time'] += $time;
            $result[$user->getContactId()] = [
                'time' => $time,
                'timeStr' => statusTimeHelper::getTimeDurationInHuman(
                    0,
                    $time * statusTimeHelper::SECONDS_IN_MINUTE,
                    ''
                ),
            ];
        }
        $result[0]['timeStr'] = statusTimeHelper::getTimeDurationInHuman(
            0,
            $result[0]['time'] * statusTimeHelper::SECONDS_IN_MINUTE,
            ''
        );

        return $result;
    }

    /**
     * @param DateTime $date
     *
     * @return array
     * @throws Exception
     * @todo refactor and cache
     */
    public function projectsTimeByWeek(DateTime $date)
    {
        $week = statusWeekFactory::createWeekByDate($date);

        $statistics = $this->checkinModel->countTimeByDatesWithProjects(
            $week->getFirstDay()->getDate()->format('Y-m-d'),
            $week->getLastDay()->getDate()->format('Y-m-d')
        );

        $result = [];

        /** @var statusProject $project */
        foreach (stts()->getEntityRepository(statusProject::class)->findAll() as $project) {
            if (!stts()->getRightConfig()->hasAccessToProject($project)) {
                continue;
            }

            if (!isset($result[$project->getId()])) {
                $result[$project->getId()] = [
                    'time' => 0,
                    'timeStr' => '',
                ];
            }

            if (isset($statistics[$project->getId()])) {
                foreach ($statistics[$project->getId()] as $statistic) {
                    if (!stts()->getRightConfig()->hasAccessToTeammate($statistic['contact_id'])) {
                        continue;
                    }

                    $result[$project->getId()]['time'] += $statistic['duration'];
                }
            }
        }

        foreach ($result as $projectId => $timeDatum) {
            $result[$projectId]['timeStr'] = statusTimeHelper::getTimeDurationInHuman(
                0,
                $timeDatum['time'] * statusTimeHelper::SECONDS_IN_MINUTE,
                ''
            );
        }

        return $result;
    }
}