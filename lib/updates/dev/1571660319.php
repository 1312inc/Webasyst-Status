<?php

if (wa()->getUser()->getRights(statusConfig::APP_ID)) {
    try {
        $contact_id = wa()->getUser()->getId();
        $user = stts()->getEntityRepository(statusUser::class)->findByContactId($contact_id);
        if (!$user->getId()) {
            $user = stts()->getEntityFactory(statusUser::class)->createNewWithContact(new waContact($contact_id));
            stts()->getEntityPersister()->insert($user);
        }
    } catch (Exception $ex) {
        kmwaWaLogger::error('error on install '.$ex->getMessage());
    }
}
