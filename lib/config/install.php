<?php

try {
    $contact_id = wa()->getUser()->getId();

    $user = stts()->getEntityRepository(statusUser::class)->findByContactId($contact_id);
    if (!$user instanceof statusUser) {
        $user = stts()->getEntityFactory(statusUser::class)->createNewWithContact(new waContact($contact_id));
        stts()->getEntityPersister()->insert($user);
    }
} catch (waException $ex) {
    kmwaWaLogger::error('error on install '.$ex->getMessage());
}
