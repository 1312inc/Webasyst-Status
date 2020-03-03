<?php

$appPath = wa()->getAppPath(null, 'status');
$path = [
    'kmwaWaJsonActions.class.php',
    'kmwaWaJsonController.class.php',
    'kmwaWaLogger.class.php',
    'kmwaWaViewAction.class.php',
    'kmwaWaViewActions.class.php',
    'kmwaWaViewTrait.class.php',
];

foreach ($path as $item) {
    try {
        $filePath = sprintf('%s/lib/vendor/kmwa/Wa/View/%s', $appPath, $item);
        waFiles::delete($filePath, true);
    } catch (Exception $ex) {
        waLog::log('Error on deleting file '.$filePath, 'status/update.log');
    }
}
