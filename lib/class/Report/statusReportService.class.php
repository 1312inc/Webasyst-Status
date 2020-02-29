<?php

/**
 * Class statusReportService
 */
final class statusReportService
{
    const TYPE_BY_PROJECT = 'project';
    const TYPE_BY_USER    = 'user';

    /**
     * @var statusModel
     */
    private $model;

    public function __construct()
    {
        $this->model = stts()->getModel();
    }

    /**
     * @param array $projects
     *
     * @return statusReportDataDto[]
     */
    public function loadDataForProjects(statusReportDataProviderInterface $dataProvider)
    {

    }

    /**
     * @param array $users
     *
     * @return statusReportDataDto[]
     */
    public function loadDataForUsers(array $users = [])
    {

    }
}
