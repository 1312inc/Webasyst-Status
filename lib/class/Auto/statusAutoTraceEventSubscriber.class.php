<?php

final class statusAutoTraceEventSubscriber
{
    public function handleWebasystBackendHeader(&$params)
    {
        try {
            $app = wa()->getApp();
            $user = stts()->getUser();

            if (!$user instanceof statusUser) {
                return;
            }

            (new statusAutoTrace($user))->addCheckin(false, $app);
        } catch (Exception $ex) {
            stts()->getLogger()->error('Error on auto trace handle', $ex);
        }
    }
}
