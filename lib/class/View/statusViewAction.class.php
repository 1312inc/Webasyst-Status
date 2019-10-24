<?php

/**
 * Class statusViewAction
 */
abstract class statusViewAction extends kmwaWaViewAction
{
    /**
     * @return array
     */
    protected function getDefaultViewVars()
    {
        return [
            'stts'    => stts(),
            'isAdmin' => wa()->getUser()->isAdmin(statusConfig::APP_ID),
        ];
    }
}
