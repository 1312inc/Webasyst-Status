<?php

/**
 * Проверяет можно ли показывать рекламу и вернет её если да
 */
final class statusTinyAddService
{
    public const MIN_CHECKINS_TO_SHOW = 40;

    private const HIDE_FLAG = 'hide_tiny_ad_until';
    private const AD_COUNT  = 'tiny_ad_checkin_count';

    /**
     * @return array<string, mixed>
     * @throws waException
     */
    public function getAd(waContact $user): array
    {
        if (!$this->canShow($user)) {
            return [];
        }

        $_tinyAds = [];

        $_webasyst_base_url = (wa()->getLocale() === 'ru_RU')
            ? 'https://www.webasyst.ru/'
            : 'https://www.webasyst.com/';
        $_whichUI = (wa()->whichUI() == '1.3') ? '1' : '2'; //utm

        if (!wa()->appExists('tasks')) {
            $_tinyAds[] = [
                'adtype' => 'app',
                'heading' => _w('Promocode'),
                'appurl' => $_webasyst_base_url . 'store/app/tasks/?utm_source=statusappwebasyst&utm_medium=inapp_tiny_ad&utm_campaign=1312_inapp_statusappwebasyst_tasksapp_wa' . $_whichUI,
                'buyurl' => $_webasyst_base_url . 'buy/store/1811/?utm_source=statusappwebasyst&utm_medium=inapp_tiny_ad&utm_campaign=1312_inapp_statusappwebasyst_tasksapp_wa' . $_whichUI,
                'image' => wa()->getAppStaticUrl() . 'img/tinyad/ad-tasks-app-144.png',
                'title' => _w('Teamwork'),
                'subtitle' => _w('When tasks become bigger and more complex.'),
                'teaser' => _w('Promocode for our flagship Webasyst app.'),
                'body' => '<strong>' . _w('Our flagship app.') . '</strong>' . ' ' .
                    _w(
                        'Amazing companion/upgrade for Pocket Lists when it’s time for real collaboration on <em>bigger and more complex tasks</em>. Assignments, task statuses, deadlines, kanban board, more — the app help bringing the order to any complex teamwork.'
                    ),
                'promocode' => '2EJZB021QE',
                'discount' => '15',
            ];
        }

        if (!wa()->appExists('cash')) {
            $_tinyAds[] = [
                'adtype' => 'app',
                'heading' => _w('Promocode'),
                'appurl' => $_webasyst_base_url . 'store/app/cash/?utm_source=statusappwebasyst&utm_medium=inapp_tiny_ad&utm_campaign=1312_inapp_statusappwebasyst_cashapp_wa' . $_whichUI,
                'buyurl' => $_webasyst_base_url . 'buy/store/5136/?utm_source=statusappwebasyst&utm_medium=inapp_tiny_ad&utm_campaign=1312_inapp_statusappwebasyst_cashapp_wa' . $_whichUI,
                'image' => wa()->getAppStaticUrl() . 'img/tinyad/ad-cash-app-144.png',
                'title' => _w('Cash Flow'),
                'subtitle' => _w('Forecasts and saves your business money.'),
                'teaser' => _w('Promocode for managing money the smarter way.'),
                'body' => '<strong>' . _w('Forecasts and saves your money.') . '</strong>' . ' ' .
                    _w(
                        'Shows exact cash on hand balance for any date in the future. This app could have been a <em>life saver</em> for most businesses which did not survive a cash gap because of not knowing it’s coming.'
                    ),
                'promocode' => '9ZYNJO6ENP',
                'discount' => '15',
            ];
        }

        //show random tiny
        return $_tinyAds ? $_tinyAds[array_rand($_tinyAds)] : [];
    }

    public function setHideFlagForUser(waContact $user): void
    {
        $user->setSettings(
            statusConfig::APP_ID,
            self::HIDE_FLAG,
            date('Y-m-d', strtotime('+30 days'))
        );
    }

    private function canShow(waContact $user): bool
    {
        if (date('Y-m-d') < $user->getSettings(
                statusConfig::APP_ID,
                self::HIDE_FLAG,
                date('Y-m-d')
            )
        ) {
            return false;
        }

        $itemsCount = $user->getSettings(
            statusConfig::APP_ID,
            self::AD_COUNT,
            0
        );

        if ($itemsCount < self::MIN_CHECKINS_TO_SHOW) {
            $user->setSettings(
                statusConfig::APP_ID,
                self::AD_COUNT,
                stts()->getModel(statusCheckin::class)->countAll()
            );

            return false;
        }

        return true;
    }
}
