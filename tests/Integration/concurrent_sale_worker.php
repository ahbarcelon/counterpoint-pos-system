<?php

require dirname(__DIR__, 2) . '/vendor/codeigniter4/framework/system/Test/bootstrap.php';

try {
    $sale = (new \App\Services\SaleService(db_connect('tests', false)))->record(
        (int) $argv[1],
        null,
        (int) $argv[2],
        1,
        (string) $argv[3],
    );
    echo json_encode(['ok' => true, 'sale' => $sale], JSON_THROW_ON_ERROR);
} catch (\Throwable $e) {
    echo json_encode(['ok' => false, 'message' => $e->getMessage()], JSON_THROW_ON_ERROR);
}
