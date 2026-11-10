<?php
require __DIR__.'/vendor/autoload.php';

$start = (float) $argv[2];
$client = new App\Services\Warehouse\WarehouseClient('warehouse:50051', 5000);

while (microtime(true) < $start) { usleep(500); }

try {
    $r = $client->reserve('race-'.$argv[1].'-'.uniqid(), [1 => 4]);
    echo 'Order '.$argv[1].': '.($r['success'] ? 'SUCCESS' : 'REJECTED - '.$r['message']) ."\n";
} catch (Throwable $e) {
    echo 'Order '.$argv[1].': ERROR - '.$e->getMessage()."\n";
}
