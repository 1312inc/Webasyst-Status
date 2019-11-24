<?php

/**
 * Class statusTodaystatusSaveController
 */
class statusTodaystatusSaveController extends statusJsonController
{
    /**
     * @throws kmwaNotFoundException
     * @throws waException
     */
    public function execute()
    {
        $data = waRequest::post('status', [], waRequest::TYPE_ARRAY);

        $date = new DateTime();
        $date->modify((!empty($data['offset']) ? $data['offset'] : '0').' day');
        $status = statusTodayStatusFactory::getForUser(stts()->getUser(), $date);

        $calendar = stts()->getModel('waContactCalendars')->getById($data['calendar_id']);
        if (empty($calendar)) {
            $this->setError('No calendar');

            return;
        }

        $data['summary'] = !empty($data['summary']) ? $data['summary'] : $calendar['default_status'];

        /** @var waContactEventsModel $waContactEventsModel */
        $waContactEventsModel = stts()->getModel('waContactEvents');
        if (!$status->getCalendarId() || ($status->getCalendarId() && $status->getDays(
                ) > 1) || !empty($data['brand_new'])) {
            $data['status_id'] = $waContactEventsModel->insertEvent(
                [
                    'calendar_id' => $data['calendar_id'],
                    'summary' => $data['summary'],
                    'start' => $date->format('Y-m-d H:00:00'),
                    'end' => min(
                        $date->format('Y-m-d 23:59:59'),
                        (clone $date)->modify('+1 hour')->format('Y-m-d H:00:00')
                    ),
                    'is_allday' => 1,
                    'is_status' => 1,
                    'summary_type' => !empty($data['summary']) ? 'custom' : 'default',
                    'update_datetime' => date('Y-m-d H:i:s'),
                    'description' => '',
                    'location' => '',
                ]
            );
        } else {
            $waContactEventsModel->updateById(
                $status->getStatusId(),
                [
                    'summary' => $data['summary'],
                    'calendar_id' => $data['calendar_id'],
                    'summary_type' => 'custom',
                ]
            );
        }

//        if (!empty($data['brand_new'])) {
        $view = wa()->getView();
        $view->assign(
            [
                'statuses' => statusTodayStatusFactory::getAllForUser(stts()->getUser(), true),
                'currentStatus' => statusTodayStatusFactory::getForUser(stts()->getUser(), $date, true),
            ]
        );

        $this->response = $view->fetch(wa()->getAppPath('templates/actions/todaystatus/TodaystatusList.html', 'status'));
//        } else {
//            $this->response = $data;
//        }
    }
}
