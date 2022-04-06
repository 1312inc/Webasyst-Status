<?php

abstract class statusAbstractWidget extends waWidget
{
    private $incognitoMode = false;

    /**
     * @var waWidgetSettingsModel
     */
    protected static $settingsModel;

    protected static function getSettingModel(): waWidgetSettingsModel
    {
        if (self::$settingsModel === null) {
            self::$settingsModel = new waWidgetSettingsModel();
        }

        return self::$settingsModel;
    }

    protected static function renderTemplate($template, $assign = []): string
    {
        if (!file_exists($template)) {
            return '';
        }
        $assign['ui'] = wa()->whichUI(wa()->getConfig()->getApplication());
        $assign['webasyst_ui'] = wa()->whichUI('webasyst');

        $view = wa()->getView();
        $old_vars = $view->getVars();
        $view->clearAllAssign();
        $view->assign($assign);
        $html = $view->fetch($template);
        $view->clearAllAssign();
        $view->assign($old_vars);

        return $html;
    }

    protected function isIncognito(): bool
    {
        $user = wa()->getUser();
        $this->incognitoMode = !$user || !$user->isAuth();

        return $this->incognitoMode;
    }

    protected function getStatusUser(): statusUser
    {
        if ($this->isIncognito()) {
            $appAdmins = (new waContactRightsModel())->getUsers('status');
            $user = stts()->getEntityRepository(statusUser::class)
                ->findByContactId(array_shift($appAdmins));
            wa()->getAuth()->auth(['id' => $user->getId()]);
        } else {
            $user = stts()->getUser();
        }

        return $user;
    }

    protected function incognitoLogout(): void
    {
        if ($this->incognitoMode) {
            wa()->getAuth()->clearAuth();
        }
    }
}
