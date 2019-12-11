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
        $statusDate = $date->format('Y-m-d');
        if (!empty($data['date'])) {
            $statusDate = $data['date'];
            $date = new DateTime($data['date']);
        }
        $status = statusTodayStatusFactory::getForContactId(stts()->getUser()->getContactId(), $date);

        /** @var waContactEventsModel $waContactEventsModel */
        $waContactEventsModel = stts()->getModel('waContactEvents');

        $calendar = stts()->getModel('waContactCalendars')->getById($data['calendar_id']);
        if (!empty($calendar)) {
            $data['summary'] = !empty($data['summary'])
                ? $data['summary']
                : ifset($calendar, 'default_status', $calendar['name']) ;

            if (!$status->getCalendarId()
                || ($status->getCalendarId() && $status->getDays() > 1)
                || !empty($data['brand_new'])
            ) {
                $endDate = clone $date;
                $data['status_id'] = $waContactEventsModel->insertEvent(
                    [
                        'calendar_id' => $data['calendar_id'],
                        'summary' => $data['summary'],
                        'start' => $date->format('Y-m-d H:00:00'),
                        'end' => min(
                            $date->format('Y-m-d 23:59:59'),
                            $endDate->modify('+1 hour')->format('Y-m-d H:00:00')
                        ),
                        'is_allday' => 1,
                        'is_status' => 1,
                        'summary_type' => !empty($data['summary']) ? 'custom' : 'default',
                        'update_datetime' => date('Y-m-d H:i:s'),
                        'description' => '',
                        'location' => '',
                    ]
                );
                (new waLogModel())->insert(
                    [
                        'app_id' => 'team',
                        'contact_id' => wa()->getUser()->getId(),
                        'params' => $data['status_id'],
                        'datetime' => date('Y-m-d H:i:s'),
                        'action' => 'event_add',
                        'subject_contact_id' => null,
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
                (new waLogModel())->insert(
                    [
                        'app_id' => 'team',
                        'contact_id' => wa()->getUser()->getId(),
                        'params' => $status->getStatusId(),
                        'datetime' => date('Y-m-d H:i:s'),
                        'action' => 'event_edit',
                        'subject_contact_id' => null,
                    ]
                );
            }
        } else {
            $waContactEventsModel->deleteById($status->getStatusId());
            (new waLogModel())->insert(
                [
                    'app_id' => 'team',
                    'contact_id' => wa()->getUser()->getId(),
                    'params' => $status->getStatusId(),
                    'datetime' => date('Y-m-d H:i:s'),
                    'action' => 'event_delete',
                    'subject_contact_id' => null,
                ]
            );
        }

        $view = wa()->getView();
        $view->assign(
            [
                'statuses' => statusTodayStatusFactory::getAllForUser(stts()->getUser(), true),
                'currentStatus' => statusTodayStatusFactory::getForContactId(
                    stts()->getUser()->getContactId(),
                    $date,
                    true
                ),
                'statusDate' => $statusDate,
            ]
        );

        $this->response = $view->fetch(
            wa()->getAppPath('templates/actions/todaystatus/TodaystatusList.html', 'status')
        );
    }
}
