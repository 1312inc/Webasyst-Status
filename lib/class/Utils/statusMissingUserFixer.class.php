<?php

final class statusMissingUserFixer
{
    public function fix()
    {
        stts()->getLogger()->log('Start fix users');

        $rightsModel = new waContactRightsModel();
        $contactsWithRights = $rightsModel->getUsers(statusConfig::APP_ID);

        /** @var statusUserRepository $userRep */
        $userRep = stts()->getEntityRepository(statusUser::class);
        /** @var statusUserFactory $userFac */
        $userFac = stts()->getEntityFactory(statusUser::class);
        foreach ($contactsWithRights as $contactId) {
            stts()->getLogger()->log(sprintf('Check contact %d', $contactId));
            $user = $userRep->findByContactId($contactId);
            if (!$user->getId()) {
                stts()->getLogger()->log(sprintf('Contact %d without status user. Will create', $contactId));
                $user = $userFac->createNewWithContact(new waContact($contactId));
                stts()->getEntityPersister()->save($user);
                stts()->getLogger()->log(sprintf('Welcome new status user %d', $user->getId()));
            }
        }
    }
}
