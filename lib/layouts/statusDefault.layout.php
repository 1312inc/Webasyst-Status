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
        $this->assign(
            'showReviewWidget',
            stts()->getModel(statusCheckin::class)->countByUser($this->getUser()->getId()) >= 5
        );
        $this->executeAction('sidebar_html', new statusBackendSidebarAction());
    }
}
