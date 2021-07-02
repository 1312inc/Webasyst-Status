<?php

final class statusBackendFixUsersAction extends statusViewAction
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
        (new statusMissingUserFixer())->fix();

        $this->redirect(['url' => wa()->getAppUrl(statusConfig::APP_ID)]);
    }
}
