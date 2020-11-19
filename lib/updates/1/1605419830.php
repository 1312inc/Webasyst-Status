<?php

$appPath = wa()->getAppPath(null, 'status');
$path = [
    'js/statusold.js',
    'img/status96.png',
    'img/status48.png',
];

foreach ($path as $item) {
    try {
        $filePath = sprintf('%s/%s', $appPath, $item);
        waFiles::delete($filePath, true);
    } catch (Exception $ex) {
        waLog::log('Error on deleting file '.$filePath, 'status/update.log');
    }
}
