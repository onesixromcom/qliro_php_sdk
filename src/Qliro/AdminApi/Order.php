<?php

declare(strict_types=1);

namespace Qliro\AdminApi;

use Qliro\Resource;
use Qliro\Transport\ConnectorInterface;

class Order extends Resource
{
    /**
     * {@inheritDoc}
     */
    protected const ID_FIELD = 'OrderId';

    /**
     * Constructs an order instance.
     *
     * @param ConnectorInterface $connector HTTP transport connector
     * @param string|null        $orderId   Order ID
     */
    public function __construct(ConnectorInterface $connector, ?string $orderId = null)
    {
        parent::__construct($connector);

        if ($orderId !== null) {
            $this[static::ID_FIELD] = $orderId;
        }
    }

    /**
     * Add order items.
     *
     * @link https://developers.qliro.com/docs/api/v2AddItemsToInvoice-post
     *
     * @param array<string, mixed> $data
     *
     * @return static
     */
    public function addOrderItems(array $data): static
    {
        $this->post('/checkout/adminapi/v2/AddItemsToInvoice', $data)
            ->expectSuccessful()
            ->status('200');

        return $this;
    }

    /**
     * Cancel order.
     *
     * @link https://developers.qliro.com/docs/api/v2cancelOrder-post
     *
     * @param array<string, mixed> $data
     *
     * @return static
     */
    public function cancelOrder(array $data): static
    {
        $this->validateData([
            'RequestId',
            'OrderId',
        ], $data);

        $this->post('/checkout/adminapi/v2/cancelOrder', $data)
            ->expectSuccessful()
            ->status('200');

        return $this;
    }

    /**
     * Mark items as shipped.
     *
     * @link https://developers.qliro.com/docs/api/v2MarkItemsAsShipped-post
     *
     * @param array<string, mixed> $data
     *
     * @return static
     */
    public function markItemsAsShipped(array $data): static
    {
        $data = $this->post('/checkout/adminapi/v2/MarkItemsAsShipped', $data)
            ->expectSuccessful()
            ->contentType('application/json')
            ->status('200')
            ->getJson();

        $this->exchangeArray($data);

        return $this;
    }

    /**
     * Get order.
     *
     * @link https://developers.qliro.com/docs/api/v2ordersid-get
     *
     * @param string $orderId
     *
     * @return static
     */
    public function getOrder(string $orderId = ''): static
    {
        $orderId = (!empty($orderId) ? $orderId : $this[static::ID_FIELD]);
        $data = $this->get('/checkout/adminapi/v2/orders/' . $orderId)
            ->expectSuccessful()
            ->status('200')
            ->contentType('application/json')
            ->getJson();

        $this->exchangeArray($data);

        return $this;
    }

    /**
     * Get order details.
     *
     * @link https://developers.qliro.com/docs/api/v2ordersmerchantReference-get
     *
     * @param string $merchantReference
     *
     * @return static
     */
    public function getOrderDetails(string $merchantReference): static
    {
        $data = $this->get('/checkout/adminapi/v2/orders/' . $merchantReference)
            ->expectSuccessful()
            ->status('200')
            ->contentType('application/json')
            ->getJson();

        $this->exchangeArray($data);

        return $this;
    }

    /**
     * Return items.
     *
     * @link https://developers.qliro.com/docs/api/v2ReturnItems-post
     *
     * @param array<string, mixed> $data
     *
     * @return static
     */
    public function returnItems(array $data): static
    {
        $data = $this->post('/checkout/adminapi/v2/ReturnItems', $data)
            ->expectSuccessful()
            ->status('200')
            ->contentType('application/json')
            ->getJson();

        $this->exchangeArray($data);

        return $this;
    }

    /**
     * Update Items.
     *
     * @link https://developers.qliro.com/docs/api/v2UpdateItems-post
     *
     * @param array<string, mixed> $data
     *
     * @return static
     */
    public function updateItems(array $data): static
    {
        $data = $this->post('/checkout/adminapi/v2/UpdateItems', $data)
            ->expectSuccessful()
            ->status('200')
            ->contentType('application/json')
            ->getJson();

        $this->exchangeArray($data);

        return $this;
    }

    /**
     * Get items from the order with status.
     *
     * @param string $status
     *
     * @return array<int, array<string, mixed>>
     */
    public function getOrderItems(string $status = OrderItemStatus::RESERVE): array
    {
        if (empty($this['OrderItemActions'])) {
            return [];
        }

        $data = [];
        foreach ($this['OrderItemActions'] as $orderItem) {
            if ($orderItem['ActionType'] !== $status) {
                continue;
            }

            $data[] = $orderItem;
        }

        return $data;
    }
}
