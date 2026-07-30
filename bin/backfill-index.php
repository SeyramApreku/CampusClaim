<?php

require_once __DIR__ . '/../classes/IndexJobDAO.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$count = (new IndexJobDAO())->enqueueAllItems();
fwrite(STDOUT, "Queued $count item(s) for semantic indexing.\n");
