<?php

try {
    $rightsModel = new waContactRightsModel();
    $contact_ids = $rightsModel->getUsers('status');

    foreach ($contact_ids as $contact_id) {
        $user = stts()->getEntityRepository(statusUser::class)->findByContactId($contact_id);
        if (!$user instanceof statusUser) {
            $user = stts()->getEntityFactory(statusUser::class)->createNewWithContact(new waContact($contact_id));
            stts()->getEntityPersister()->insert($user);
        }
    }
} catch (waException $ex) {
    kmwaWaLogger::error('error on install '.$ex->getMessage());
}
