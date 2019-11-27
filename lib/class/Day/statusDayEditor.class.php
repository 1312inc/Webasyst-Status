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
        $dayDto = new statusDayDto($day);
        $userDto = new statusUserDto($user);
        $userDayInfo = new statusDayUserInfoDto($dayDto->date, $userDto->contactId);

        /** @var statusCheckinRepository $checkinRep */
        $checkinRep = stts()->getEntityRepository(statusCheckin::class);
        $checkins = $checkinRep->findByDayAndUser($day, $user);

        $walogs = (new statusWaLogParser())->parseByDays($day, $day, $userDto->contactId);

        $dayDtoAssembler = new statusDayDotAssembler();
        $dayDtoAssembler
            ->fillWithCheckins($userDayInfo, $checkins, $userDto)
            ->fillWithWalogs($userDayInfo, isset($walogs[$dayDto->date]) ? $walogs[$dayDto->date] : []);

        /** @var statusProjectModel $projectModel */
        $projectModel = stts()->getModel(statusProject::class);
        $projectData = $projectModel->getByDateAndContactId($day->getDate()->format('Y-m-d'), $user->getContactId());

        $dayDtoAssembler->fillCheckinsWithProjects($userDayInfo->checkins, $projectData);
        $userDayInfo->todayStatus = statusTodayStatusFactory::getForContactId(
            $userDto->contactId,
            new DateTime($dayDto->date)
        );

        $dayDto->userDayInfos[$userDto->contactId] = $userDayInfo;
        $dayDto->users[$userDto->contactId] = $userDto;

        return $dayDto;
    }
}
