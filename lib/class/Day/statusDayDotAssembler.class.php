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
        $traceDuration = $dayDuration = 0;
        $hasManualCheckins = false;
        foreach ($checkins as $check) {
            $checkin = new statusDayCheckinDto($check);

            $userDayInfoDto->checkins[] = $checkin;

            if (!$checkin->isTrace) {
                if (!$hasManualCheckins) {
                    $hasManualCheckins = true;
                    $userDayInfoDto->firstCheckin = $checkin;
                }

                $userDayInfoDto->realCheckinCount++;
                $userDayInfoDto->startTime = min($userDayInfoDto->startTime, $checkin->min);
                $userDayInfoDto->endTime = max($userDayInfoDto->endTime, $checkin->max);
                $dayDuration += $checkin->duration;
            } else {
                $traceDuration += $checkin->duration;
            }
        }

        if (!$hasManualCheckins) {
            $checkin = new statusDayCheckinDto(stts()->getEntityFactory(statusCheckin::class)->createNew());
            $userDayInfoDto->checkins[] = $checkin;
            $userDayInfoDto->firstCheckin = $checkin;
        }

        $userDayInfoDto->dayDurationString = statusTimeHelper::getTimeDurationInHuman(
            0,
            $dayDuration * 60,
            '0' . _w('h')
        );
        $userDayInfoDto->traceDurationString = statusTimeHelper::getTimeDurationInHuman(
            0,
            $traceDuration * 60,
            '0' . _w('h')
        );

        return $this;
    }

    /**
     * @param statusDayUserInfoDto $userDayInfoDto
     * @param array                $walogs
     *
     * @return mixed
     * @throws waException
     */
    public function fillWithWalogs(statusDayUserInfoDto $userDayInfoDto, array $walogs)
    {
        $midnight = DateTimeImmutable::createFromFormat('Y-m-d|', date('Y-m-d'));

        foreach ($walogs as $appId => $log) {
            if (!wa()->appExists($appId)) {
                continue;
            }

            $userDayInfoDto->walogs[$appId] = new statusWaLogDto($appId, $log);
            foreach ($log as &$item) {
                if ($item['app_id'] === 'webasyst'
                    || ($item['app_id'] === 'webasyst' && !in_array($item['action'], ['login', 'logout']))
                ) {
                    continue;
                }

                $item['app_color'] = $userDayInfoDto->walogs[$appId]->appColor;

                $userDatetime = new DateTimeImmutable(waDateTime::format('fulltime', $item['datetime']));
                $secondsFromMidnight = $userDatetime->getTimestamp() - $midnight->getTimestamp();
                $item['position'] = min(100, max(0, round(100 * $secondsFromMidnight / statusTimeHelper::SECONDS_IN_DAY)));

                $userDayInfoDto->walogsByDatetime[] = $item;
            }
            unset($item);
        }

        usort($userDayInfoDto->walogsByDatetime, static function($a, $b) {
           return $a['datetime'] < $b['datetime'];
        });

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
            if (!$projects) {
                return $this->projectsDtos;
            }

            /** @var statusProject $project */
            foreach ($projects as $project) {
                if (!stts()->getRightConfig()->hasAccessToProject($project)) {
                    continue;
                }

                $this->projectsDtos[$project->getId()] = new statusDayProjectDto($project);
            }
        }

        return $this->projectsDtos;
    }
}
