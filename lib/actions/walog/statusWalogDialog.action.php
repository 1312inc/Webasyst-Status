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
     */
    public function runAction($params = null)
    {
        $date = waRequest::request('date', waRequest::TYPE_STRING_TRIM);
        if (!$date) {
            $date = date('Y-m-d');
        }

        $statusDay = new statusDay(new DateTime($date));

        $walogs = (new statusWaLogParser())->parseByDays(
            $statusDay,
            $statusDay,
            stts()->getUser()->getContactId(),
            true
        );
        $walogs = ifset($walogs, $date, []);
        $walogsDto = [];
        foreach ($walogs as $appId => $log) {
            $walogsDto[$appId] = new statusWaLogDto($appId, $log);
        }

        $this->view->assign(
            [
                'walogs' => $walogsDto,
                'date' => $date,
            ]
        );
    }
}
