<?php

/**
 * Class statusRightConfig
 */
class statusRightConfig extends waRightConfig
{
    const CAN_MANAGE_SELF_STATUS_TIMELINE = 'can_manage_self_status_timeline';
    const CAN_SEE_TEAMMATES = 'can_see_teammates';
    const CAN_SEE_CONTRIBUTE_TO_PROJECTS = 'can_see_contribute_to_projects';

    /**
     * @var int
     */
    private $userId;

    /**
     * @throws waException
     */
    public function init()
    {
        $this->addItem(self::CAN_MANAGE_SELF_STATUS_TIMELINE, _w('Can manage self status & timeline'));
        $this->addItem(self::CAN_SEE_TEAMMATES, _w('Can see teammates'));
        $this->addItem(self::CAN_SEE_CONTRIBUTE_TO_PROJECTS, _w('Can see & contribute to projects'));

        // POCKETS

//        $items = [];
//        /** @var pocketlistsPocketFactory $pocketFactory */
//        $pocketFactory = pl2()->getEntityFactory(pocketlistsPocket::class);
//        foreach ($pocketFactory->findAllForUser() as $pocket) {
//            $items[$pocket->getId()] = $pocket->getName();
//        }
//
//        $this->addItem(
//            pocketlistsRBAC::POCKET_ITEM,
//            _w('Pockets'),
//            'selectlist',
//            [
//                'items'    => $items,
//                'position' => 'right',
//                'options'  => [
//                    pocketlistsRBAC::RIGHT_NONE    => _w('No access'),
//                    pocketlistsRBAC::RIGHT_LIMITED => _w('Limited access'),
//                    pocketlistsRBAC::RIGHT_ADMIN   => _w('Full access'),
//                ],
//            ]
//        );
//
//        $rights = (new waContactRightsModel())->get($this->userId, pocketlistsHelper::APP_ID);
//        $currentPocketRights = [];
//        foreach ($rights as $right => $rightValue) {
//            if (strpos($right, pocketlistsRBAC::POCKET_ITEM.'.') !== 0) {
//                continue;
//            }
//
//            $currentPocketRights[str_replace(pocketlistsRBAC::POCKET_ITEM.'.', '', $right)] = $rightValue;
//        }
//        // LISTS
//
//        if (empty($rights)) {
//            $this->addItem(
//                '',
//                _w('To setup access rights by pocket, set Limited access for at least one pocket'),
//                'header',
//                ['hint1' => 'Подсказка']
//            );
//        } else {
//            /** @var pocketlistsListFactory $listFactory */
//            $listFactory = pl2()->getEntityFactory(pocketlistsList::class);
//            foreach ($currentPocketRights as $currentPocketId => $rightValue) {
//                if ($rightValue == pocketlistsRBAC::RIGHT_ADMIN) {
//                    continue;
//                }
//
//                $pocket = $pocketFactory->findById($currentPocketId);
//                if (!$pocket) {
//                    continue;
//                }
//
//                $lists = $listFactory->findListsByPocket($pocket, false);
//                $items = [];
//
//                if ($lists) {
//                    $filter = new pocketlistsStrategyListFilterAndSort($lists);
//                    $lists = $filter->filter();
//
//                    foreach ($lists->getNonArchived() as $list) {
//                        $items[$list->getId()] = $list->getName();
//                    }
//
//                    foreach ($lists->getArchived() as $list) {
//                        $items[$list->getId()] = "("._w('archive').") " .$list->getName();
//                    }
//
//                    $this->addItem(
//                        pocketlistsRBAC::LIST_ITEM,
//                        $pocket->getName(),
//                        'list',
//                        [
//                            'items' => $items,
//                            //                'hint1' => 'all_checkbox',
//                        ]
//                    );
//                }
//            }
//        }

        /**
         * @event rights.config
         * @param waRightConfig $this Rights setup object
         * @return void
         */
        wa()->event('rights.config', $this);
    }

    /**
     * @param int $contact_id
     *
     * @return array
     */
    public function setDefaultRights($contact_id)
    {
        return [
            self::CAN_MANAGE_SELF_STATUS_TIMELINE    => 1,
        ];
    }

    /**
     * @param int    $contact_id
     * @param string $right
     * @param null   $value
     *
     * @return bool
     * @throws waException
     */
//    public function setRights($contact_id, $right, $value = null)
//    {
//        $right_model = new waContactRightsModel();
//
//        if (strpos($right, pocketlistsRBAC::POCKET_ITEM.'.') === 0) {
//            $pocketId = (int)str_replace(pocketlistsRBAC::POCKET_ITEM.'.', '', $right);
//            if ($value == pocketlistsRBAC::RIGHT_NONE) {
//                /** @var pocketlistsPocket $pocket */
//                $pocket = pl2()->getEntityFactory(pocketlistsPocket::class)->findById($pocketId);
//
//                /** @var pocketlistsList[] $lists */
//                $lists = pl2()->getEntityFactory(pocketlistsList::class)->findListsByPocket($pocket, false);
//
//                /** @var pocketlistsListModel $list */
//                foreach ($lists as $list) {
//                    if ($right_model->save(
//                        $contact_id,
//                        pocketlistsHelper::APP_ID,
//                        pocketlistsRBAC::LIST_ITEM.'.'.$list->getId(),
//                        $value
//                    )) {
//                        self::$rightsCache['lists'][$list->getId()] = true;
//                    }
//                }
//            }
//        }
//
//        if (strpos($right, pocketlistsRBAC::LIST_ITEM.'.') === 0) {
//            $listId = (int)str_replace(pocketlistsRBAC::LIST_ITEM.'.', '', $right);
//            if (isset(self::$rightsCache['lists'][$listId])) {
//                return true;
//            }
//        }
//
//        $right_model->save(
//            $contact_id,
//            pocketlistsHelper::APP_ID,
//            $right,
//            $value
//        );
//
//        return true;
//    }
}
