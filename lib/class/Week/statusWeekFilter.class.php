<?php

final class statusWeekFilter
{
    /**
     * пользователь удален/заблокирован И никакой инфы — трейсов, действий, чекинов — для таймлайна нет, то просто
     * пропускать
     *
     * @param statusWeekDto $weekDto
     */
    public function filterNonExistingUserWithNoActivity(statusWeekDto $weekDto): void
    {
        foreach ($weekDto->days as $day) {
            foreach ($day->userDayInfos as $userId => $userDayInfo) {
                if (!$day->users[$userId]->exists
                    && $userDayInfo->firstCheckin->id === null
                    && empty($userDayInfo->walogs)
                ) {
                    unset($day->userDayInfos[$userId], $day->users[$userId]);
                }
            }
        }
    }
}
