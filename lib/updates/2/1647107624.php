<?php

$path = wa()->getAppPath(null, 'status');
$deleted = ['/templates/actions-legacy/', '/templates/include-legacy/', '/templates/layouts-legacy/'];
foreach ($deleted as $item) {
    waFiles::delete($path . $item, true);
}
