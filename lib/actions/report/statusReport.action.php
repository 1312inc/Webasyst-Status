<?php

/**
 * Class statusReportAction¡
 */
class statusReportAction extends statusViewAction
{
    /**
     * @var DateTime
     */
    protected $dateStart;

    /**
     * @var DateTime
     */
    protected $dateEnd;

    /**
     * @var statusReportService
     */
    protected $reportService;

    protected function preExecute()
    {
        parent::preExecute();

        $this->dateStart = new DateTime(waRequest::get('start', date('Y-m-d'), waRequest::TYPE_STRING_TRIM));
        $this->dateEnd = new DateTime(waRequest::get('end', date('Y-m-d'), waRequest::TYPE_STRING_TRIM));
        $this->dateStart->setTime(0, 0);
        $this->dateEnd->setTime(23, 59, 59);

        $this->reportService = new statusReportService();
    }

    /**
     * @param null|array $params
     *
     * @return mixed
     * @throws Exception
     */
    public function runAction($params = null)
    {
        $projects = (new statusReportDataProject())->getData($this->dateStart, $this->dateEnd);
        $users = (new statusReportDataUser())->getData($this->dateStart, $this->dateEnd);

        $this->view->assign(
            [
                'projects' => $projects,
                'users' => $users,
            ]
        );
    }
}
