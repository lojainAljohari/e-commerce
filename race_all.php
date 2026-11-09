<?php

$start = microtime(true) + 4;
$procs = [];
$pipes = [];

for ($i = 1; $i <= 8; $i++) {
    $procs[$i] = proc_open(
        [PHP_BINARY, __DIR__.'/race_test.php', (string) $i, (string) $start],
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes[$i],
    );
}

foreach ($procs as $i => $process) {
    echo stream_get_contents($pipes[$i][1]);
    echo stream_get_contents($pipes[$i][2]);
    proc_close($process);
}
