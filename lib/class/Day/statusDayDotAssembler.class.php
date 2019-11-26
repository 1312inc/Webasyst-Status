<?php

/**
 * Class statusDayDotAssembler
 */
final class statusDayDotAssembler
{
    /**
     * @var statusDayProjectDto[]
     */
    private $projectsDtos;

    /**
     * @param statusDayDto    $dayDto
     * @param statusCheckin[] $checkins
     * @param statusUser      $user
     *
     * @return statusDayDotAssembler
     * @throws waException
     */
    public function fillWithCheckins(statusDayDto $dayDto, array $checkins, statusUser $user)
    {
        foreach ($checkins as $check) {
            $dayDto->startTime = min($dayDto->startTime, $check->getStartTime());
            $dayDto->endTime = max($dayDto->endTime, $check->getEndTime());
            $checkin = new statusDayCheckinDto($check);
            $checkin->user->todayStatus = statusTodayStatusFactory::getForUser($user, new DateTime($check->getDate()));

            $dayDto->checkins[] = $checkin;
        }

        if (empty($dayDto->checkins)) {
            $checkin = new statusDayCheckinDto(stts()->getEntityFactory(statusCheckin::class)->createNew());
            $checkin->user->todayStatus = statusTodayStatusFactory::getForUser($user, new DateTime($dayDto->date));

            $dayDto->checkins[] = $checkin;
        }

        $dayDto->firstCheckin = $dayDto->checkins[0];

        return $this;
    }

    /**
     * @param statusDayDto $dayDto
     * @param array        $walogs
     *
     * @return mixed
     */
    public function fillWithWalogs(statusDayDto $dayDto, array $walogs)
    {
        foreach ($walogs as $appId => $log) {
            $dayDto->walogs[$appId] = new statusWaLogDto($appId, $log);
        }

        return $this;
    }

    /**
     * @param statusDayCheckinDto[] $checkins
     * @param array                 $projectData
     *
     * @return mixed
     * @throws Exception
     */
    public function fillCheckinsWithProjects(array $checkins, array $projectData)
    {
        /** @var statusDayCheckinDto $checkin */
        foreach ($checkins as $checkin) {
            $css = [];
            $title = [];
            $percents = 0;

            /** @var statusDayProjectDto $projectDto */
            foreach ($this->getProjectsDto() as $projectDto) {
                $key = $checkin->id.'_'.$projectDto->id;
                if (isset($projectData[$key])) {
                    $checkin->hasProjects = true;
                    $checkin->projectsDuration[$projectDto->id] = new statusDayProjectDurationDto(
                        $projectDto,
                        $checkin->duration,
                        $projectData[$key]['project_checkin_id'],
                        $projectData[$key]['duration']
                    );
                } else {
                    $checkin->projectsDuration[$projectDto->id] = new statusDayProjectDurationDto($projectDto);
                }

                $projectDurationDto = $checkin->projectsDuration[$projectDto->id];
                $value = $checkin->duration ? round($projectDurationDto->duration / ($checkin->duration / 100)) : 0;
                $checkin->projectPercents[$projectDto->id] = $value;
                $css[] = $projectDurationDto->project->color.' '.$percents.'%';
                $percents += $value;
                $css[] = $projectDurationDto->project->color.' '.$percents.'%';
                $title[] = $projectDurationDto->project->name
                    .': '
                    .statusTimeHelper::getTimeDurationInHuman(
                        0,
                        $projectDurationDto->duration * statusTimeHelper::SECONDS_IN_MINUTE
                    );
            }

            if ($percents < 100) {
                $css[] = '#f1f2f3 '.$percents.'%';
                $css[] = '#f1f2f3 100%';
            }

            $checkin->projectDurationCss = implode(', ', $css);
            $checkin->projectDurationTitle = implode(', ', $title);
        }

        return $this;
    }

    /**
     * @return statusDayProjectDto[]
     * @throws waException
     */
    private function getProjectsDto()
    {
        if ($this->projectsDtos === null) {
            $this->projectsDtos = [];

            /** @var statusProjectRepository $projectRep */
            $projectRep = stts()->getEntityRepository(statusProject::class);
            $projects = $projectRep->findAll();
            /** @var statusProject $project */
            foreach ($projects as $project) {
                $this->projectsDtos[$project->getId()] = new statusDayProjectDto($project);
            }
        }

        return $this->projectsDtos;
    }
}
