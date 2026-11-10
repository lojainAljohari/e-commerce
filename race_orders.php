<?php

$url = 'http://localhost:8000/api/v1/user/orders';


$totalRequests = 8;
$multi = curl_multi_init();
$handles = [];

for ($i = 1; $i <= $totalRequests; $i++) {

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_POST => true,

        CURLOPT_POSTFIELDS => [
            'product_ids[0]' => '3',
        ],

        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . "26|q14oOmuYbF8McKPTj5fFEAQFWQ7J2MPXcZHN0lytab905a45",
            'Accept: application/json',
            'device: ' . "YElDaF63wZ1bC6WMY61f6zLVJc9PZtYtt0sut2LWpSf8F9sDGTX3XMgG3YsH",
            'lang: ' . "en",
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
    ]);

    $handles[$i] = $ch;
    curl_multi_add_handle($multi, $ch);
}

echo "Sending $totalRequests simultaneous orders...\n\n";

$running = null;

do {
    curl_multi_exec($multi, $running);

    if ($running) {
        curl_multi_select($multi, 0.1);
    }
} while ($running);

$success = 0;
$failed = 0;

foreach ($handles as $id => $ch) {

    $response = curl_multi_getcontent($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    echo "Order Request #$id\n";
    echo "HTTP Status: $httpCode\n";

    if ($error) {
        echo "ERROR: $error\n";
        $failed++;
    } else {
        echo "Response: $response\n";

        if ($httpCode >= 200 && $httpCode < 300) {
            $success++;
            echo "RESULT: SUCCESS\n";
        } else {
            $failed++;
            echo "RESULT: FAILED\n";
        }
    }

    echo "-------------------------\n";

    curl_multi_remove_handle($multi, $ch);
    curl_close($ch);
}

curl_multi_close($multi);

echo "\n========== SUMMARY ==========\n";
echo "Successful requests: $success\n";
echo "Failed requests: $failed\n";
