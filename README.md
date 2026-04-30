# Qliro PHP SDK

A PHP client for the [Qliro](https://www.qliro.com/) payment API, covering both the Merchant API (order creation) and the Admin API (order management and settlements).

## Requirements

- PHP 8.1+
- [Guzzle 7](https://docs.guzzlephp.org/)

## Installation

```bash
composer require iqv/qliro_php_sdk
```

## Quick start

```php
use Qliro\Transport\GuzzleConnector;
use Qliro\MerchantApi\Order;

// Pass true as the third argument to use the test environment.
$connector = new GuzzleConnector('your-api-key', 'your-api-secret', isTest: true);

$order = new Order($connector);
$order->create([
    'MerchantReference'                      => 'ORDER-001',
    'Currency'                               => 'SEK',
    'Country'                                => 'SE',
    'Language'                               => 'sv-SE',
    'MerchantTermsUrl'                       => 'https://example.com/terms',
    'MerchantConfirmationUrl'                => 'https://example.com/confirmation',
    'MerchantOrderManagementStatusPushUrl'   => 'https://example.com/status-push',
    'OrderItems' => [
        [
            'MerchantReference' => 'SKU-001',
            'Description'       => 'Blue sneakers',
            'Quantity'          => 1,
            'UnitPrice'         => 89.90, // Specified with 0, 1 or 2 decimals, e.g. 99, 99.9 or 99.99.
        ],
    ],
]);

echo 'Qliro order ID: ' . $order->getId() . PHP_EOL;
```

## Examples

See the [`examples/`](examples/) directory for runnable scripts:

| File | Description |
|------|-------------|
| [`examples/merchant_order.php`](examples/merchant_order.php) | Create and update a Merchant API order |
| [`examples/admin_order.php`](examples/admin_order.php) | Fetch, ship items, and cancel an Admin API order |

## API coverage

| Class | Description |
|-------|-------------|
| `Qliro\MerchantApi\Order` | Create, update, and fetch merchant orders |
| `Qliro\AdminApi\Order` | Manage orders: ship, return, cancel, add items |
| `Qliro\AdminApi\Settlement` | Query settlements by date range |
| `Qliro\AdminApi\OrderItemStatus` | Constants: `RESERVE`, `CAPTURE`, `REFUND` |
| `Qliro\AdminApi\SettlementStatus` | Constants: `ACCEPTED`, `REJECTED`, `PENDING` |

## Environments

| Environment | Base URL |
|-------------|----------|
| Production  | `https://payments.qit.nu/` |
| Test        | `https://pago.qit.nu/` |

Pass `true` as the third constructor argument of `GuzzleConnector` to target the test environment.
