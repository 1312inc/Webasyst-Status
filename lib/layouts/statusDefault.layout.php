<?php

/**
 * Class statusDefaultLayout
 */
class statusDefaultLayout extends waLayout
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->executeAction('sidebar_html', new statusBackendSidebarAction());
    }
}
