<?php

/**
 * A list of localized strings to use in JS.
 */
class statusBackendLocAction extends statusViewAction
{
    /**
     * @param null $params
     *
     * @return mixed|void
     */
    public function runAction($params = null)
    {
        $strings = [];

        // Application locale strings
        $translates = [
            'h',
            'm',
            'Hello! This is a friendly reminder to check in your workday yesterday.',
        ];
        foreach ($translates as $s) {
            $strings[$s] = _w($s);
        }

        $this->view->assign(
            'strings',
            $strings ?: new stdClass()
        ); // stdClass is used to show {} instead of [] when there's no strings

        $this->getResponse()->addHeader('Content-Type', 'text/javascript; charset=utf-8');
    }
}
