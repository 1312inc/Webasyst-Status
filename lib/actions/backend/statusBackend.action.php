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
        $weeks = statusWeekFactory::createLastNWeeks(5, true);

        /** @var statusCheckinRepository $checkinRepository */
        $checkinRepository = stts()->getEntityRepository(statusCheckin::class);

        $weeksDto = [];
        $checkins = $checkinRepository->findByWeeks($weeks);
        /** @var statusWeek $week */
        foreach ($weeks as $week) {
            $weeksDto[] = new statusWeekDto($week, $checkins);
        }

        $currentWeek = array_shift($weeksDto);

        $this->view->assign(
            [
                'currentWeek'  => $currentWeek,
                'weeks'        => $weeksDto,
                'sidebar_html' => (new statusBackendSidebarAction())->display(),
            ]
        );
    }
}
