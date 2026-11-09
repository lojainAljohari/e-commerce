<?php

namespace App\Services\Warehouse;


class WarehouseClient
{
    private string $hostname;

    private int $timeoutMs;

    public function __construct(?string $hostname = null, ?int $timeoutMs = null)
    {
        $this->hostname = $hostname ?? (string) config('services.warehouse.host', 'warehouse:50051');
        $this->timeoutMs = $timeoutMs ?? (int) config('services.warehouse.timeout_ms', 3000);
    }


    public function getQuantity(int $productId): int
    {
        $response = Protobuf::decode($this->call('GetQuantity', Protobuf::intField(1, $productId)));

        return (int) ($response[2][0] ?? 0);
    }

    public function reserve(string $reservationId, array $quantities): array
    {
        $items = '';
        foreach ($quantities as $productId => $quantity) {
            $items .= Protobuf::messageField(
                2,
                Protobuf::intField(1, (int) $productId) . Protobuf::intField(2, (int) $quantity),
            );
        }

        $response = Protobuf::decode($this->call('Reserve', Protobuf::stringField(1, $reservationId) . $items));

        return [
            'success' => (bool) ($response[1][0] ?? 0),
            'message' => (string) ($response[2][0] ?? ''),
        ];
    }


    public function release(string $reservationId): bool
    {
        $response = Protobuf::decode($this->call('Release', Protobuf::stringField(1, $reservationId)));

        return (bool) ($response[1][0] ?? 0);
    }


    private function call(string $method, string $payload): string
    {
        $headers = [];

        $ch = curl_init('http://' . $this->hostname . '/warehouse.Warehouse/' . $method);
        curl_setopt_array($ch, [
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_PRIOR_KNOWLEDGE,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => "\0" . pack('N', strlen($payload)) . $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/grpc', 'TE: trailers', 'Expect:'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT_MS => $this->timeoutMs,
            CURLOPT_TIMEOUT_MS => $this->timeoutMs,
            CURLOPT_HEADERFUNCTION => function ($handle, string $line) use (&$headers): int {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $headers[strtolower(trim($parts[0]))] = trim($parts[1]);
                }

                return strlen($line);
            },
        ]);

        $body = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($body === false) {

            throw new WarehouseUnavailableException('Warehouse service unreachable: ' . $error, $errno);
        }

        $status = (int) ($headers['grpc-status'] ?? -1);
        if ($status !== 0) {
            throw new WarehouseUnavailableException(
                'Warehouse service error (' . $status . '): ' . rawurldecode($headers['grpc-message'] ?? 'no grpc-status in response'),
                $status,
            );
        }

        if (strlen($body) < 5) {
            return '';
        }

        $length = unpack('N', substr($body, 1, 4))[1];

        return substr($body, 5, $length);
    }
}
