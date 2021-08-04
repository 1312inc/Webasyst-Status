<?php

/**
 * Class statusBackendActions
 */
class statusBackendActions extends waJsonActions
{
    public function setSeenNoStatusNotificationAction()
    {
        $contactId = wa()->getUser()->getId();
        $key = sprintf('%s_%d', statusServiceStatusChecker::CACHE_KEY, $contactId);

        $ttl = strtotime('tomorrow') - time();

        stts()->getCache()->set($key, true, $ttl);
    }

    public function hideTinyAdAction()
    {
        (new statusTinyAddService())->setHideFlagForUser($this->getUser());
    }
}
