<?php

/**
 * Class statusWalogDialogAction
 */
class statusWalogDialogAction extends statusViewAction
{
    /**
     * @param null $params
     *
     * @return mixed|void
     * @throws waException
     * @throws kmwaLogicException
     */
    public function runAction($params = null)
    {
        $date = waRequest::request('date', waRequest::TYPE_STRING_TRIM);
        $contactId = waRequest::request('contact_id', waRequest::TYPE_INT);
        if (!$date) {
            $date = statusTimeHelper::createDatetimeForUser()->format('Y-m-d');
        }

        $user = stts()->getEntityRepository(statusUser::class)->findByContactId($contactId);
        if (!$user->getId()) {
            $user = stts()->getEntityFactory(statusUser::class)->createNewWithContact(new waContact($contactId));
        }

        $statusDay = new statusDay(new DateTime($date));

        $walogs = (new statusWaLogParser())->parseByDays(
            $statusDay,
            $statusDay,
            $user->getContactId(),
            true
        );
        $walogs = ifset($walogs, $date, []);
        $walogsDto = [];
        foreach ($walogs as $appId => $log) {
            if (wa()->appExists($appId)) {
                $walogsDto[$appId] = new statusWaLogDto($appId, $log);
            }
        }

        $this->view->assign(
            [
                'user' => $user,
                'walogs' => $walogsDto,
                'date' => $date,
            ]
        );
    }
}
