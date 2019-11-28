<?php

/**
 * Class statusWeekDtoAssembler
 */
final class statusWeekDtoAssembler
{
    /**
     * @param statusWeekDto $weekDto
     * @param statusWeek    $week
     * @param statusUser    $user
     *
     * @return statusWeekDonutDto
     * @throws waException
     */
    public function getDonutUserStatDto(statusWeekDto $weekDto, statusWeek $week, statusUser $user)
    {
        $donut = new statusWeekDonutDto();
        $donut->weekNum = $weekDto->number;

        /** @var statusProjectModel $projectsModel */
        $projectsModel = stts()->getModel(statusProject::class);
        $projectsThisWeek = $projectsModel->getStatByDatesAndContactId(
            $week->getFirstDay()->getDate()->format('Y-m-d'),
            $week->getLastDay()->getDate()->format('Y-m-d'),
            $user->getContactId()
        );

        /** @var statusCheckinModel $checkinModel */
        $checkinModel = stts()->getModel(statusCheckin::class);
        $donut->totalDuration = $checkinModel->countTimeByDatesAndContactId(
            $week->getFirstDay()->getDate()->format('Y-m-d'),
            $week->getLastDay()->getDate()->format('Y-m-d'),
            $user->getContactId()
        );

        $projectDuration = 0;

        foreach ($projectsThisWeek as $project) {
            $projectDto = new statusWeekDonutDataDto(
                $project['project_id'],
                $project['name'],
                $project['color'],
                $project['total_duration']
            );

            $donut->datum[$projectDto->id] = $projectDto;
            $projectDuration += $projectDto->totalDuration;
        }
        $donut->totalDurationStr = statusTimeHelper::getTimeDurationInHuman(
            0,
            $donut->totalDuration * statusTimeHelper::SECONDS_IN_MINUTE
        );

        $maxDuration = max(
            $donut->totalDuration,
            statusSettingsUser::WEEK_WORKING_HOURS * statusTimeHelper::MINUTES_IN_HOUR
        );

        $percent = $maxDuration / 100;
        $degrees = $maxDuration / 360;

        $prevDegree = 0;
        foreach ($donut->datum as $id => $project) {
            $projectDegree = round($project->totalDuration / $degrees, 2);
            $project->rotations[] = [
                'from' => $prevDegree,
                'to'   => min(180, $projectDegree),
            ];
            $prevDegree += $project->rotations[0]['to'];

            if ($project->rotations[0]['to'] === 180) {
                $projectDegree -= 180;
                $project->rotations[] = [
                    'from' => $prevDegree,
                    'to'   => $projectDegree,
                ];
                $prevDegree += $project->rotations[1]['to'];
            }

            $project->percentsInWeek = round($project->totalDuration / $percent, 2);
        }

        $noProjectDuration = $donut->totalDuration - $projectDuration;
        $noProjectDegree = $noProjectDuration > 0? round($noProjectDuration / $degrees, 2) : 0;
        $noProjectDto = new statusWeekDonutDataDto(0, _w('No project'), 'linear-gradient(135deg, #50a8ff 25%, #74c2ff 25%, #74c2ff 50%, #50a8ff 50%, #50a8ff 75%, #74c2ff 75%, #74c2ff 100%) top center/5px 5px', $noProjectDuration);
        $donut->datum[0] = $noProjectDto;
        $noProjectDto->rotations[] = [
            'from' => $prevDegree,
            'to'   => min(180, $noProjectDegree),
        ];
        $prevDegree += $noProjectDto->rotations[0]['to'];
        if ($noProjectDto->rotations[0]['to'] === 180) {
            $noProjectDegree -= 180;
            $noProjectDto->rotations[] = [
                'from' => $prevDegree,
                'to'   => $noProjectDegree,
            ];
            $prevDegree += $noProjectDto->rotations[0]['to'];
        }

        $noActivityDuration = $maxDuration - $donut->totalDuration;
        $noActivityDegree = $noActivityDuration > 0 ? round($noActivityDuration / $degrees, 2) : 0;
        $noActivityDto = new statusWeekDonutDataDto(-1, _w('No activity'), '#eee', $noActivityDuration);
        $donut->datum[-1] = $noActivityDto;
        $noActivityDto->rotations[] = [
            'from' => $prevDegree,
            'to'   => min(180, $noActivityDegree),
        ];
        $prevDegree += $noActivityDto->rotations[0]['to'];
        if ($noActivityDto->rotations[0]['to'] === 180) {
            $noActivityDegree -= 180;
            $noActivityDto->rotations[] = [
                'from' => $prevDegree,
                'to'   => $noActivityDegree,
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
    public function getDonutProjectStatDto(statusWeekDto $weekDto, statusWeek $week, $projectId)
    {
        $donut = new statusWeekDonutDto();
        $donut->weekNum = $weekDto->number;

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
            $user = $userRep->findByContactId($contactTime['contact_id']);
            $donutDataDto = new statusWeekDonutDataDto(
                $contactTime['contact_id'],
                $user->getName(),
                '',
                $contactTime['total_duration']
            );

            $donut->datum[$donutDataDto->id] = $donutDataDto;
            $projectDuration += $donutDataDto->totalDuration;
        }

        $donut->totalDuration = $projectDuration;
        $donut->totalDurationStr = statusTimeHelper::getTimeDurationInHuman(
            0,
            $donut->totalDuration * statusTimeHelper::SECONDS_IN_MINUTE
        );

        $percent = $donut->totalDuration / 100;
        $degrees = $donut->totalDuration / 360;

        $prevDegree = 0;
        foreach ($donut->datum as $id => $project) {
            $projectDegree = round($project->totalDuration / $degrees, 2);
            $project->rotations[] = [
                'from' => $prevDegree,
                'to'   => min(180, $projectDegree),
            ];
            $prevDegree += $project->rotations[0]['to'];

            if ($project->rotations[0]['to'] === 180) {
                $projectDegree -= 180;
                $project->rotations[] = [
                    'from' => $prevDegree,
                    'to'   => $projectDegree,
                ];
                $prevDegree += $project->rotations[1]['to'];
            }

            $project->percentsInWeek = round($project->totalDuration / $percent, 2);
        }

        return $donut;
    }
}
