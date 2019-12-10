<?php

/**
 * Class statusServiceStatusChecker
 */
class statusServiceStatusChecker
{
    /**
     * @param statusUser $user
     *
     * @return bool
     * @throws waException
     */
    public function hasActivityYesterday(statusUser $user)
    {
        $today = new DateTime();
        $yesterday = new DateTime('yesterday');

        // monday - relax
        if ($today->format('N') === 1) {
            return false;
        }

        /** @var statusCheckinModel $model */
        $model = stts()->getModel(statusCheckin::class);
        $contactId = $user->getContactId();
        $count = $model->countTimeByDates(
            date('Y-m-d', strtotime('-2 days')),
            $today->format('Y-m-d'),
            $contactId
        );

        $todayStatus = statusTodayStatusFactory::getForContactId($contactId, $today);
        $yesterdayStatus = statusTodayStatusFactory::getForContactId($contactId, $yesterday);

        if (!isset($count[$contactId]) && !$yesterdayStatus->getStatusId() && !$todayStatus->getStatusId()) {
            return true;
        }

        return false;
    }
}