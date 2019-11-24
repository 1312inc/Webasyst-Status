<?php

/**
 * Class statusTodayStatusDialogAction
 */
class statusTodaystatusDialogAction extends statusViewAction
{
    /**
     * @param null $params
     *
     * @return mixed|void
     * @throws waException
     */
    public function runAction($params = null)
    {
        $this->view->assign([
            'statuses' => statusTodayStatusFactory::getAll(),
            'offset' => waRequest::request('offset', '', waRequest::TYPE_STRING_TRIM),
        ]);
    }
}
