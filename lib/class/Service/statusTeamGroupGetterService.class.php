<?php

final class statusTeamGroupGetterService
{
    public function getGroups(): array
    {
        $waGroups = (new waGroupModel())->select('*')
            ->order('sort')
            ->fetchAll('id');

        $userGroupModel = new waUserGroupsModel();
        $allUsers = stts()->getEntityRepository(statusUser::class)->findAll();
        foreach ($waGroups as $i => $waGroup) {
            $groupContactsIds = $userGroupModel->getContactIds($i);

            $groupIsVisible = false;

            if ($groupContactsIds) {
                foreach ($allUsers as $user) {
                    if (!$user->isExists()) {
                        continue;
                    }

                    if (in_array($user->getContactId(), $groupContactsIds)) {
                        $groupIsVisible = true;
                        break;
                    }
                }
            }

            if ($groupIsVisible === false) {
                unset($waGroups[$i]);
            }
        }

        return $waGroups;
    }
}
