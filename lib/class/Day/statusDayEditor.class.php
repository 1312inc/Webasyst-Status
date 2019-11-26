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

        $walogs = (new statusWaLogParser())->parseByDays($day, $day, $user);

        $dayDto = new statusDayDto($day);

        $dayDtoAssembler = new statusDayDotAssembler();
        $dayDtoAssembler
            ->fillWithCheckins($dayDto, $checkins, $user)
            ->fillWithWalogs($dayDto, isset($walogs[$dayDto->date]) ? $walogs[$dayDto->date] : [])
        ;

        /** @var statusProjectModel $projectModel */
        $projectModel = stts()->getModel(statusProject::class);
        $projectData = $projectModel->getByDateAndContactId($day->getDate()->format('Y-m-d'), $user->getContactId());

        $dayDtoAssembler->fillCheckinsWithProjects($dayDto->checkins, $projectData);

        return $dayDto;
    }
}
