<?php

/**
 * Class statusWeekDtoAssembler
 */
final class statusWeekDtoAssembler
{
    /**
     * @param statusWeekDto $weekDto
     * @param statusWeek    $week
     * @param statusUser[]  $users
     *
     * @return statusWeekDonutDto
     * @throws waException
     */
    public function getDonutUserStatDto(statusWeekDto $weekDto, statusWeek $week, array $users): statusWeekDonutDto
    {
        $donut = new statusWeekDonutDto();
        $donut->weekNum = $weekDto->number;

        $contactIds = array_reduce(
            $users,
            static function ($contactIds, statusUser $user) {
                $contactIds[] = $user->getContactId();

                return $contactIds;
            },
            []
        );

        /** @var statusProjectModel $projectsModel */
        $projectsModel = stts()->getModel(statusProject::class);
        $projectsThisWeek = $projectsModel->getStatByDatesAndContactId(
            $week->getFirstDay()->getDate()->format('Y-m-d'),
            $week->getLastDay()->getDate()->format('Y-m-d'),
            $contactIds
        );

        /** @var statusCheckinModel $checkinModel */
        $checkinModel = stts()->getModel(statusCheckin::class);
        $donut->totalDuration = $checkinModel->countTimeByDatesAndContactId(
            $week->getFirstDay()->getDate()->format('Y-m-d'),
            $week->getLastDay()->getDate()->format('Y-m-d'),
            $contactIds
        );

        if (stts()->canShowTrace()) {
            /** @var statusCheckinTraceModel $checkinTraceModel */
            $checkinTraceModel = stts()->getModel('statusCheckinTrace');
            $traceDurationWithBreak = $checkinTraceModel->countTimeByDatesAndContactId(
                $week->getFirstDay()->getDate()->format('Y-m-d'),
                $week->getLastDay()->getDate()->format('Y-m-d'),
                $contactIds,
                statusCheckinTraceModel::TOTAL_DURATION_WITH_BREAK
            );
            $donut->traceTotalDurationWithBreakStr = statusTimeHelper::getTimeDurationInHuman(
                0,
                $traceDurationWithBreak * statusTimeHelper::SECONDS_IN_MINUTE
            );
            $traceDuration = $checkinTraceModel->countTimeByDatesAndContactId(
                $week->getFirstDay()->getDate()->format('Y-m-d'),
                $week->getLastDay()->getDate()->format('Y-m-d'),
                $contactIds,
                statusCheckinTraceModel::TOTAL_DURATION
            );
            $donut->traceTotalDurationStr = statusTimeHelper::getTimeDurationInHuman(
                0,
                $traceDuration * statusTimeHelper::SECONDS_IN_MINUTE
            );
            $traceBreakDuration = $checkinTraceModel->countTimeByDatesAndContactId(
                $week->getFirstDay()->getDate()->format('Y-m-d'),
                $week->getLastDay()->getDate()->format('Y-m-d'),
                $contactIds,
                statusCheckinTraceModel::BREAK_DURATION
            );
            $donut->traceTotalBreakStr = statusTimeHelper::getTimeDurationInHuman(
                0,
                $traceBreakDuration * statusTimeHelper::SECONDS_IN_MINUTE
            );
        }

        $projectDuration = 0;

        foreach ($projectsThisWeek as $project) {
            if (!stts()->getRightConfig()->hasAccessToProject($project['project_id'])) {
                continue;
            }

            $projectDto = new statusWeekDonutDataDto(
                $project['project_id'],
                $project['name'],
                $project['color'],
                $project['total_duration']
            );

            $donut->data[$projectDto->id] = $projectDto;
            $projectDuration += $projectDto->totalDuration;
            $donut->hasData = true;
        }
        $donut->totalDurationStr = statusTimeHelper::getTimeDurationInHuman(
            0,
            $donut->totalDuration * statusTimeHelper::SECONDS_IN_MINUTE
        );

        $maxDuration = max(
            $donut->totalDuration,
            statusSettingsUser::WEEK_WORKING_HOURS * statusTimeHelper::MINUTES_IN_HOUR * count($contactIds)
        );

        $percent = $maxDuration / 100;
        $degrees = $maxDuration / 360;

        $prevDegree = 0;
        foreach ($donut->data as $id => $project) {
            $projectDegree = round($project->totalDuration / $degrees, 2);
            $project->rotations[] = [
                'from' => $prevDegree,
                'to' => min(180, $projectDegree),
            ];
            $prevDegree += $project->rotations[0]['to'];

            if ($project->rotations[0]['to'] === 180) {
                $projectDegree -= 180;
                $project->rotations[] = [
                    'from' => $prevDegree,
                    'to' => $projectDegree,
                ];
                $prevDegree += $project->rotations[1]['to'];
            }

            $project->percentsInWeek = round($project->totalDuration / $percent, 2);
        }

        $noProjectDuration = $donut->totalDuration - $projectDuration;
        $noProjectDegree = $noProjectDuration > 0 ? round($noProjectDuration / $degrees, 2) : 0;
        $noProjectDto = new statusWeekDonutDataDto(
            0,
            _w('No project'),
            'rgb(230,167,217);
background: linear-gradient(135deg, rgb(225, 127, 206) 0%, rgb(225, 127, 206) 25%, rgba(52,203,254,1) 25%, rgba(52,203,254,1) 50%, rgb(225, 127, 206) 50%, rgb(225, 127, 206) 75%, rgba(52,203,254,1) 75%, rgba(52,203,254,1) 100%); background-size: 7px 7px',
            $noProjectDuration
        );
        $donut->data[0] = $noProjectDto;
        $noProjectDto->rotations[] = [
            'from' => $prevDegree,
            'to' => min(180, $noProjectDegree),
        ];
        $prevDegree += $noProjectDto->rotations[0]['to'];
        if ($noProjectDto->rotations[0]['to'] === 180) {
            $noProjectDegree -= 180;
            $noProjectDto->rotations[] = [
                'from' => $prevDegree,
                'to' => $noProjectDegree,
            ];
            $prevDegree += $noProjectDto->rotations[1]['to'];
        }

        $noActivityDuration = $maxDuration - $donut->totalDuration;
        $noActivityDegree = $noActivityDuration > 0 ? round($noActivityDuration / $degrees, 2) : 0;
        $noActivityDto = new statusWeekDonutDataDto(-1, _w('No activity'), wa()->whichUI() == '1.3' ? '#eee' : 'var(--background-color-input)', $noActivityDuration);
        $donut->data[-1] = $noActivityDto;
        $noActivityDto->rotations[] = [
            'from' => $prevDegree,
            'to' => min(180, $noActivityDegree),
        ];
        $prevDegree += $noActivityDto->rotations[0]['to'];
        if ($noActivityDto->rotations[0]['to'] === 180) {
            $noActivityDegree -= 180;
            $noActivityDto->rotations[] = [
                'from' => $prevDegree,
                'to' => $noActivityDegree,
            ];
        }

        return $donut;
    }

    /**
     * @param statusWeekDto $weekDto
     * @param statusWeek    $week
     * @param int           $projectId
     *
     * @return statusWeekDonutDto
     * @throws waException
     */
    public function getDonutProjectStatDto(statusWeekDto $weekDto, statusWeek $week, $projectId): statusWeekDonutDto
    {
        $donut = new statusWeekDonutDto();
        $donut->weekNum = $weekDto->number;
        $donut->chart = false;

        /** @var statusProjectModel $projectsModel */
        $projectsModel = stts()->getModel(statusProject::class);
        $contactTimesThisWeek = $projectsModel->getStatByDatesAndProjectId(
            $week->getFirstDay()->getDate()->format('Y-m-d'),
            $week->getLastDay()->getDate()->format('Y-m-d'),
            $projectId
        );

        $projectDuration = 0;

        /** @var statusUserRepository $userRep */
        $userRep = stts()->getEntityRepository(statusUser::class);
        foreach ($contactTimesThisWeek as $contactTime) {
            if (!stts()->getRightConfig()->hasAccessToTeammate($contactTime['contact_id'])) {
                continue;
            }

            $user = $userRep->findByContactId($contactTime['contact_id']);
            $donutDataDto = new statusWeekDonutDataDto(
                $contactTime['contact_id'],
                $user->getName(),
                "url({$user->getPhotoUrl()})",
                $contactTime['total_duration'],
                statusWeekDonutDataDto::USER
            );

            $donut->data[$donutDataDto->id] = $donutDataDto;
            $projectDuration += $donutDataDto->totalDuration;
        }

        $donut->totalDuration = $projectDuration;
        $donut->totalDurationStr = statusTimeHelper::getTimeDurationInHuman(
            0,
            $donut->totalDuration * statusTimeHelper::SECONDS_IN_MINUTE
        );

        $percent = $donut->totalDuration / 100;
        $degrees = $donut->totalDuration / 360;
        if ($percent && $degrees) {
            $prevDegree = 0;
            foreach ($donut->data as $id => $project) {
                $projectDegree = round($project->totalDuration / $degrees, 2);
                $project->rotations[] = [
                    'from' => $prevDegree,
                    'to' => min(180, $projectDegree),
                ];
                $prevDegree += $project->rotations[0]['to'];

                if ($project->rotations[0]['to'] === 180) {
                    $projectDegree -= 180;
                    $project->rotations[] = [
                        'from' => $prevDegree,
                        'to' => $projectDegree,
                    ];
                    $prevDegree += $project->rotations[1]['to'];
                }

                $project->percentsInWeek = round($project->totalDuration / $percent, 2);
                $donut->hasData = true;
            }
        }

        return $donut;
    }
}
