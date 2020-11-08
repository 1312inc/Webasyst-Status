<?php

/**
 * Class statusBackendDebugAction
 */
class statusBackendDebugAction extends statusViewAction
{
    protected function preExecute()
    {
        if (!wa()->getUser()->isAdmin()) {
            throw new kmwaForbiddenException('Must be admin');
        }

        parent::preExecute();
    }

    /**
     * @param null|array $params
     */
    public function runAction($params = null)
    {
        $data = waRequest::post('debug', [], waRequest::TYPE_ARRAY_TRIM);
        $debugSettings = new statusDebugSettings();

        if (waRequest::getMethod() === 'post') {
            $debugSettings->setShowTrace(filter_var($data['show_trace'], FILTER_VALIDATE_BOOLEAN))
                ->save();
        }

        $this->view->assign(
            [
                'debug' => $debugSettings->getData(),
            ]
        );
    }
}
