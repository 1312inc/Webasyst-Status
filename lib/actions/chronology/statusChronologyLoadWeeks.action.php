<?php

/**
 * Class statusChronologyLoadWeeksAction
 */
class statusChronologyLoadWeeksAction extends statusViewAction
{
    /**
     * @param null|array $params
     *
     * @return mixed
     * @throws Exception
     */
    public function runAction($params = null)
    {
        $offset = waRequest::get('offset', 0, waRequest::TYPE_INT);

        $weeks = statusWeekFactory::createLastNWeeks(
            statusWeekFactory::DEFAULT_WEEKS_LOAD,
            false,
            statusWeekFactory::DEFAULT_WEEKS_LOAD * $offset
        );
        $weeksDto = [];

        /** @var statusCheckinRepository $checkinRepository */
        $checkinRepository = stts()->getEntityRepository(statusCheckin::class);

        $checkins = $checkinRepository->findByWeeks($weeks);
        /** @var statusWeek $week */
        foreach ($weeks as $week) {
            $weeksDto[] = new statusWeekDto($week, $checkins);
        }

        $this->view->assign(['weeks' => $weeksDto]);
    }
}
