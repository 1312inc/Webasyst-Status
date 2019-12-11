<?php

/**
 * Class statusBackendController
 */
class statusBackendController extends waViewController
{
    public function execute()
    {
        $this->setLayout(new statusDefaultLayout());
    }
}
