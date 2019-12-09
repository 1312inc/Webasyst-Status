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
     * @throws kmwaForbiddenException
     * @throws kmwaNotFoundException
     * @throws waException
     */
    public function runAction($params = null)
    {
        $date = waRequest::request('date', waRequest::TYPE_STRING_TRIM);
        $contactId = waRequest::request('contact_id', waRequest::TYPE_INT);
        if (!$date) {
            $date = date('Y-m-d');
        }

        $user = stts()->getEntityRepository(statusUser::class)->findByContactId($contactId);
        if (!$user instanceof statusUser) {
            throw new kmwaNotFoundException('No user found');
        }

        if (!stts()->getRightConfig()->hasAccessToApp($user)) {
            throw new kmwaForbiddenException();
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
            $walogsDto[$appId] = new statusWaLogDto($appId, $log);
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
