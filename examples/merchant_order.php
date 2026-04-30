<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Qliro\Exception\ConnectorException;
use Qliro\MerchantApi\Order;
use Qliro\Transport\GuzzleConnector;

// ---------------------------------------------------------------------------
// Bootstrap
// ---------------------------------------------------------------------------

$connector = new GuzzleConnector(
    apiKey: 'your-api-key',
    apiSecret: 'your-api-secret',
    isTest: true,
);

// ---------------------------------------------------------------------------
// Create an order
// ---------------------------------------------------------------------------

$order = new Order($connector);

try {
    $order->create([
        'MerchantReference'                    => 'ORDER-' . time(),
        'Currency'                             => 'SEK',
        'Country'                              => 'SE',
        'Language'                             => 'sv-SE',
        'MerchantTermsUrl'                     => 'https://example.com/terms',
        'MerchantConfirmationUrl'              => 'https://example.com/confirmation',
        'MerchantOrderManagementStatusPushUrl' => 'https://example.com/status-push',
        'OrderItems' => [
            [
                'MerchantReference' => 'SKU-001',
                'Description'       => 'Blue sneakers',
                'Quantity'          => 1,
                'UnitPrice'         => 89.90,
            ],
            [
                'MerchantReference' => 'SKU-002',
                'Description'       => 'White socks (3-pack)',
                'Quantity'          => 2,
                'UnitPrice'         => 49.00,
            ],
        ],
    ]);
} catch (ConnectorException $e) {
    echo 'API error [' . $e->getErrorCode() . ']: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}

$orderId = $order->getId();
echo 'Created order ID: ' . $orderId . PHP_EOL;

// ---------------------------------------------------------------------------
// Update the order (e.g. change shipping address before customer completes checkout)
// ---------------------------------------------------------------------------

try {
    $order->update([
        'OrderItems' => [
            [
                'MerchantReference' => 'SKU-001',
                'Description'       => 'Blue sneakers',
                'Quantity'          => 1,
                'UnitPrice'         => 89.90,
            ],
        ],
    ]);
    echo 'Order updated.' . PHP_EOL;
} catch (ConnectorException $e) {
    echo 'API error [' . $e->getErrorCode() . ']: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}

// ---------------------------------------------------------------------------
// Fetch the order back to inspect its current state
// ---------------------------------------------------------------------------

try {
    $order->getOrder();
    echo 'Fetched order: ' . print_r($order->getArrayCopy(), true) . PHP_EOL;
} catch (ConnectorException $e) {
    echo 'API error [' . $e->getErrorCode() . ']: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}
