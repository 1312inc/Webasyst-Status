<?php

/**
 * Class kmwaWaViewAction
 */
abstract class kmwaWaViewAction extends waViewAction
{
    use kmwaWaViewTrait;

    /**
     * @param null|array $params
     *
     * @return mixed
     */
    abstract public function runAction($params = null);

    /**
     * @return array
     */
    abstract protected function getDefaultViewVars();

    /**
     * @param bool $clear_assign
     *
     * @return string|void
     */
    public function display($clear_assign = true)
    {
        try {
            $profileData = [
                'memory' => memory_get_usage(),
                'time' => microtime(true),
            ];

            $result = parent::display($clear_assign);

            if (waRequest::method() === 'get') {
                stts()->getLogger()->debug(
                    sprintf(
                        'display %s stat: mem=%s, time=%s. (request: %s)',
                        get_class($this),
                        sprintf('%0.2f Mb', (memory_get_usage() - $profileData['memory']) / 1048576),
                        sprintf('%0.2f s', microtime(true) - $profileData['time']),
                        json_encode(waRequest::request())
                    ),
                    'profile'
                );
            }

            return $result;
        } catch (Exception $ex) {
            $this->view->assign(
                'error',
                [
                    'code' => $ex->getCode(),
                    'message' => $ex->getMessage(),
                ]
            );

            $result = $this->view->fetch(
                wa()->getAppPath(stts()->getUI2TemplatePath('templates/include%s/error.html'))
            );
            if ($clear_assign) {
                $this->view->clearAllAssign();
            }

            return $result;
        }
    }

    /**
     * @param null $params
     */
    public function execute($params = null)
    {
        foreach ($this->getDefaultViewVars() as $key => $value) {
            $this->view->smarty->assignGlobal($key, $value);
        }

        $profileData = [
            'memory' => memory_get_usage(),
            'time' => microtime(true),
        ];

        $this->runAction($params);

        if (waRequest::method() === 'get') {
            stts()->getLogger()->debug(
                sprintf(
                    'runAction %s stat. mem=%s, time=%s. (request: %s)',
                    get_class($this),
                    sprintf('%0.2f Mb', (memory_get_usage() - $profileData['memory']) / 1048576),
                    sprintf('%0.2f s', microtime(true) - $profileData['time']),
                    json_encode(waRequest::request())
                ),
                'profile'
            );
        }
    }
}
