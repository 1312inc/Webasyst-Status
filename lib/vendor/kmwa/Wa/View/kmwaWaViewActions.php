<?php

/**
 * Class kmwaWaViewActions
 */
abstract class kmwaWaViewActions extends waViewActions
{
    use kmwaWaViewTrait;

    /**
     * @param null $params
     */
    public function run($params = null)
    {
        try {
            parent::run($params);
        } catch (Exception $ex) {
            $this->view->assign(
                'error',
                [
                    'code'    => $ex->getCode(),
                    'message' => $ex->getMessage(),
                ]
            );

            $this->setTemplate(stts()->getUI2TemplatePath('templates/include%s/error.html'));
        }
    }
}
