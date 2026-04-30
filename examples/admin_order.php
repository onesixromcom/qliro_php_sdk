<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Qliro\AdminApi\Order;
use Qliro\AdminApi\OrderItemStatus;
use Qliro\AdminApi\Settlement;
use Qliro\AdminApi\SettlementStatus;
use Qliro\Exception\ConnectorException;
use Qliro\Transport\GuzzleConnector;

// ---------------------------------------------------------------------------
// Bootstrap
// ---------------------------------------------------------------------------

$connector = new GuzzleConnector(
    apiKey: 'your-api-key',
    apiSecret: 'your-api-secret',
    isTest: true,
);

$orderId = '123456'; // replace with a real order ID

// ---------------------------------------------------------------------------
// Fetch the order and inspect its items
// ---------------------------------------------------------------------------

$order = new Order($connector, $orderId);

try {
    $order->getOrder();
} catch (ConnectorException $e) {
    echo 'API error [' . $e->getErrorCode() . ']: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}

echo 'Order status: ' . ($order['Status'] ?? 'unknown') . PHP_EOL;

$reservedItems = $order->getOrderItems(OrderItemStatus::RESERVE);
echo 'Reserved items: ' . count($reservedItems) . PHP_EOL;

// ---------------------------------------------------------------------------
// Mark items as shipped (capture payment)
// ---------------------------------------------------------------------------

if (!empty($reservedItems)) {
    try {
        $order->markItemsAsShipped([
            'OrderId'    => $orderId,
            'OrderItems' => array_map(fn($item) => [
                'MerchantReference' => $item['MerchantReference'],
                'Quantity'          => $item['Quantity'],
            ], $reservedItems),
        ]);
        echo 'Items marked as shipped.' . PHP_EOL;
    } catch (ConnectorException $e) {
        echo 'API error [' . $e->getErrorCode() . ']: ' . $e->getMessage() . PHP_EOL;
        exit(1);
    }
}

// ---------------------------------------------------------------------------
// Cancel an order (example — separate from the ship flow above)
// ---------------------------------------------------------------------------

// $order->cancelOrder([
//     'RequestId' => uniqid('req-', true),
//     'OrderId'   => $orderId,
// ]);

// ---------------------------------------------------------------------------
// Query settlements for a date range
// ---------------------------------------------------------------------------

$settlement = new Settlement($connector);

try {
    $settlements = $settlement->getSettlements([
        'FromDate' => '2024-01-01',
        'ToDate'   => '2024-01-31',
    ]);

    foreach ($settlements as $entry) {
        $status = $entry['Status'] ?? 'unknown';
        $ref    = $entry['Reference'] ?? '-';

        if ($status === SettlementStatus::ACCEPTED) {
            echo "Settlement {$ref}: accepted" . PHP_EOL;
        } elseif ($status === SettlementStatus::REJECTED) {
            echo "Settlement {$ref}: REJECTED" . PHP_EOL;
        } else {
            echo "Settlement {$ref}: pending" . PHP_EOL;
        }
    }
} catch (ConnectorException $e) {
    echo 'API error [' . $e->getErrorCode() . ']: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}
