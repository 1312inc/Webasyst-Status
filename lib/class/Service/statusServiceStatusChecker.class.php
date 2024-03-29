<?php

/**
 * Class statusServiceStatusChecker
 */
class statusServiceStatusChecker
{
    const CACHE_KEY = 'HAS_ACTIVITY_YESTERDAY';

    /**
     * @param statusUser $user
     * @param bool       $force
     *
     * @return bool
     * @throws waException
     */
    public function hasActivityYesterday(statusUser $user, $force = false)
    {
        $contactId = $user->getContactId();
        $key = sprintf('%s_%d', self::CACHE_KEY, $contactId);
        $cached = stts()->getCache()->get($key);
        if ($cached !== null && !$force) {
            return $cached;
        }

        $today = statusTimeHelper::createDatetimeForUser();
        $yesterday = statusTimeHelper::createDatetimeForUser('Y-m-d H:i:s', 'yesterday');

        // monday - relax
        if ($today->format('N') == 1) {
            return $this->cacheAndReturn($key, true);
        }

        /** @var statusCheckinModel $model */
        $model = stts()->getModel(statusCheckin::class);
        $count = $model->countTimeByDates(
            $yesterday->format('Y-m-d'),
            $today->format('Y-m-d'),
            $contactId
        );

        if ($count) {
            return $this->cacheAndReturn($key, isset($count[$contactId]));
        }

        $todayStatus = statusTodayStatusFactory::getForContactId($contactId, $today);
        $yesterdayStatus = statusTodayStatusFactory::getForContactId($contactId, $yesterday);

        $result = $yesterdayStatus->getStatusId() || $todayStatus->getStatusId();

        return $this->cacheAndReturn($key, $result);
    }

    /**
     * @param string $key
     * @param mixed  $result
     *
     * @return mixed
     */
    private function cacheAndReturn($key, $result)
    {
        stts()->getCache()->set($key, $result, 200);

        return $result;
    }

}