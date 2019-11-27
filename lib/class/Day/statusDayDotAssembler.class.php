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
     * @param statusDayUserInfoDto $userDayInfoDto
     * @param statusCheckin[]      $checkins
     * @param statusUserDto        $userDto
     *
     * @return statusDayDotAssembler
     * @throws waException
     * @throws Exception
     */
    public function fillWithCheckins(statusDayUserInfoDto $userDayInfoDto, array $checkins, statusUserDto $userDto)
    {
        foreach ($checkins as $check) {
            $userDayInfoDto->startTime = min($userDayInfoDto->startTime, $check->getStartTime());
            $userDayInfoDto->endTime = max($userDayInfoDto->endTime, $check->getEndTime());
            $checkin = new statusDayCheckinDto($check);
            $userDayInfoDto->checkins[] = $checkin;
        }

        if (empty($userDayInfoDto->checkins)) {
            $checkin = new statusDayCheckinDto(stts()->getEntityFactory(statusCheckin::class)->createNew());
            $userDayInfoDto->checkins[] = $checkin;
        }

        $userDayInfoDto->firstCheckin = $userDayInfoDto->checkins[0];

        return $this;
    }

    /**
     * @param statusDayUserInfoDto $userDayInfoDto
     * @param array                $walogs
     *
     * @return mixed
     */
    public function fillWithWalogs(statusDayUserInfoDto $userDayInfoDto, array $walogs)
    {
        foreach ($walogs as $appId => $log) {
            $userDayInfoDto->walogs[$appId] = new statusWaLogDto($appId, $log);
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
