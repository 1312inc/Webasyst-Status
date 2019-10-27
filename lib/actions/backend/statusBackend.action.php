<?php

/**
 * Class statusBackendAction
 */
class statusBackendAction extends statusViewAction
{
    /**
     * @param null|array $params
     *
     * @return mixed
     * @throws Exception
     */
    public function runAction($params = null)
    {
        $weeks = statusWeekFactory::createLastNWeeks();
        $currentWeek = statusWeekFactory::createCurrentWeek();

        /** @var statusCheckinRepository $checkinRepository */
        $checkinRepository = stts()->getEntityRepository(statusCheckin::class);

        $allWeeks = array_merge([$currentWeek], $weeks);
        $checkins = $checkinRepository->findByWeeks($allWeeks);
        /** @var statusWeek $week */
        foreach ($allWeeks as $week) {
            /** @var statusDay $day */
            foreach ($week->getDays() as $day) {
                if (isset($checkins[$day->getDate()->format('Y-m-d')])) {
                    $day->setCheckins($checkins[$day->getDate()->format('Y-m-d')]);
                }
            }
        }

        $this->view->assign(
            [
                'currentWeek'  => $currentWeek,
                'weeks'        => $weeks,
                'checkins'     => $checkins,
                'sidebar_html' => (new statusBackendSidebarAction())->display(),
            ]
        );
    }
}
