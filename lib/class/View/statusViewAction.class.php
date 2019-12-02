<?php

/**
 * Class statusViewAction
 */
abstract class statusViewAction extends kmwaWaViewAction
{
    /**
     * @throws kmwaForbiddenException
     * @throws waException
     */
    protected function preExecute()
    {
        if (!stts()->getRightConfig()->hasAccessToApp()) {
            throw new kmwaForbiddenException(_w('No app access'));
        }
    }

    /**
     * @return array
     */
    protected function getDefaultViewVars()
    {
        return [
            'stts' => stts(),
            'isAdmin' => (int)wa()->getUser()->isAdmin(statusConfig::APP_ID),
        ];
    }
}
