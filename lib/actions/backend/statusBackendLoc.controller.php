<?php

/**
 * A list of localized strings to use in JS.
 */
class statusBackendLocController extends waViewController
{
    public function execute()
    {
        $this->executeAction(new statusBackendLocAction());
    }
}
