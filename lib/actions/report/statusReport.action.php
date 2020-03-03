<?php

/**
 * Class statusReportAction¡
 */
class statusReportAction extends statusViewAction
{
    const SETTING_PERIOD_DATE_START = 'periodDateStart';
    const SETTING_PERIOD_DATE_END   = 'periodDateEnd';

    /**
     * @var statusDatePeriodVO
     */
    protected $datePeriod;

    /**
     * @var statusReportService
     */
    protected $reportService;

    /**
     * @throws kmwaForbiddenException
     * @throws waException
     */
    protected function preExecute()
    {
        parent::preExecute();

        $this->reportService = new statusReportService();

        $this->datePeriod = $this->reportService->getPeriodByDates(
            new DateTime(
                waRequest::get(
                    'start',
                    $this->getUser()->getSettings(
                        statusConfig::APP_ID,
                        self::SETTING_PERIOD_DATE_START,
                        (new DateTime())->modify('-1 month')->format('Y-m-d')
                    ),
                    waRequest::TYPE_STRING_TRIM
                )
            ),
            new DateTime(
                waRequest::get(
                    'end',
                    $this->getUser()->getSettings(
                        statusConfig::APP_ID,
                        self::SETTING_PERIOD_DATE_END,
                        (new DateTime())->format('Y-m-d')
                    ),
                    waRequest::TYPE_STRING_TRIM
                )
            )
        );
        $this->getUser()->setSettings(
            statusConfig::APP_ID,
            self::SETTING_PERIOD_DATE_START,
            $this->datePeriod->getDateStartFormat()
        );
        $this->getUser()->setSettings(
            statusConfig::APP_ID,
            self::SETTING_PERIOD_DATE_END,
            $this->datePeriod->getDateEndFormat()
        );
    }

    /**
     * @param null|array $params
     *
     * @return mixed
     * @throws Exception
     */
    public function runAction($params = null)
    {
        $projects = (new statusReportDataProject())->getData(
            $this->datePeriod->getDateStart(),
            $this->datePeriod->getDateEnd()
        );
        $users = (new statusReportDataUser())->getData(
            $this->datePeriod->getDateStart(),
            $this->datePeriod->getDateEnd()
        );

        $this->view->assign(
            [
                'projects' => $projects,
                'users' => $users,
                'currentPeriod' => $this->datePeriod,
                'today' => new DateTime(),
            ]
        );
    }
}
