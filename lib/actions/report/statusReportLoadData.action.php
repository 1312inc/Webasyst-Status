<?php

/**
 * Class statusReportLoadDataAction
 */
class statusReportLoadDataAction extends statusReportAction
{
    private $response = [];

    /**
     * @param null|array $params
     *
     * @throws Exception
     */
    public function runAction($params = null)
    {
        $type = waRequest::request('type', waRequest::TYPE_STRING_TRIM);
        $id = waRequest::request('id', waRequest::TYPE_INT);
        if (empty($type)) {
            throw new kmwaRuntimeException('No params');
        }

        switch ($type) {
            case statusReportDataProject::TYPE:
                $dataProvider = new statusReportDataUser();
                break;

            case statusReportDataUser::TYPE:
                $dataProvider = new statusReportDataProject();
                break;

            default:
                throw new kmwaLogicException('Wrong type');
        }

        $this->response = $dataProvider->getData(
            $this->datePeriod->getDateStart(),
            $this->datePeriod->getDateEnd(),
            $id
        );

        if (!is_array($this->response)) {
            $this->response = [];
        }
    }

    /**
     * @param bool $clear_assign
     *
     * @return string|void
     */
    public function display($clear_assign = true)
    {
        try {
            $this->getResponse()->addHeader('Content-Type', 'application/json');
            $this->getResponse()->sendHeaders();

            parent::display($clear_assign);

            echo waUtils::jsonEncode(['status' => 'ok', 'data' => $this->response]);
        } catch (Exception $ex) {
            echo waUtils::jsonEncode(['status' => 'fail', 'errors' => $ex->getMessage()]);
        }
    }
}
