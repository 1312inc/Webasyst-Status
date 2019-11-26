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
            'isAdmin' => (int) wa()->getUser()->isAdmin(statusConfig::APP_ID),
        ];
    }
}
