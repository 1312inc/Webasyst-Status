<?php

/**
 * Class statusDayEditor
 */
class statusDayEditor
{
    /**
     * @param statusDay  $day
     * @param statusUser $user
     *
     * @return statusDayDto
     * @throws waException
     * @throws Exception
     */
    public static function createDayDto(statusDay $day, statusUser $user)
    {
        /** @var statusCheckinRepository $checkinRep */
        $checkinRep = stts()->getEntityRepository(statusCheckin::class);
        $checkins = $checkinRep->findByDayAndUser($day, $user);

        $dayDto = new statusDayDto($day, $checkins);

        /** @var statusProjectRepository $projectRep */
        $projectRep = stts()->getEntityRepository(statusProject::class);
        $projects = $projectRep->findAll();
        /** @var statusProjectModel $projectModel */
        $projectModel = stts()->getModel(statusProject::class);
        $projectData = $projectModel->getByDateAndContactId($day->getDate()->format('Y-m-d'), $user->getContactId());

        $projectDtos = [];
        /** @var statusProject $project */
        foreach ($projects as $project) {
            $projectDtos[$project->getId()] = new statusDayProjectDto($project);
        }

        foreach ($dayDto->checkins as $checkin) {
            foreach ($projectDtos as $projectDto) {
                $key = $checkin->id.'_'.$projectDto->id;
                if (isset($projectData[$key])) {
                    $checkin->hasProjects = true;
                    $checkin->projectsDuration[$projectDto->id] = new statusDayProjectDuration(
                        $projectDto,
                        $projectData[$key]['project_checkin_id'],
                        $projectData[$key]['duration']
                    );
                } else {
                    $checkin->projectsDuration[$projectDto->id] = new statusDayProjectDuration($projectDto);
                }
            }
        }

        return $dayDto;
    }

    public function createDayDtoWithCheckins(statusDay $day, statusUser $user, array $checkins)
    {

    }
}
