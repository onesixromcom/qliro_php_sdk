<?php

declare(strict_types=1);

namespace Qliro\MerchantApi;

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
     * Creates the order.
     *
     * @link https://developers.qliro.com/docs/api/Orders-post
     *
     * @param array<string, mixed> $data Creation data
     *
     * @throws \Qliro\Exception\ConnectorException        When the API replies with an error response
     * @throws \GuzzleHttp\Exception\RequestException     When an error is encountered
     * @throws \RuntimeException  If the location header is missing
     * @throws \RuntimeException  If the API replies with an unexpected response
     * @throws \LogicException    When Guzzle cannot populate the response
     *
     * @return static
     */
    public function create(array $data): static
    {
        $this->validateData([
            'MerchantReference',
            'Currency',
            'Country',
            'Language',
            'MerchantTermsUrl',
            'MerchantConfirmationUrl',
            'MerchantOrderManagementStatusPushUrl',
            'OrderItems',
        ], $data);

        $response = $this->post('/checkout/merchantapi/Orders', $data)
            ->expectSuccessful()
            ->status('201')
            ->contentType('application/json');

        $this->exchangeArray($response->getJson());

        return $this;
    }

    /**
     * Updates the order.
     *
     * @link https://developers.qliro.com/docs/api/Ordersid-put
     *
     * @param array<string, mixed> $data Update data
     *
     * @throws \Qliro\Exception\ConnectorException        When the API replies with an error response
     * @throws \GuzzleHttp\Exception\RequestException     When an error is encountered
     * @throws \RuntimeException         On an unexpected API response
     * @throws \RuntimeException         If the response content type is not JSON
     * @throws \InvalidArgumentException If the JSON cannot be parsed
     * @throws \LogicException           When Guzzle cannot populate the response
     *
     * @return static
     */
    public function update(array $data): static
    {
        $this->put('/checkout/merchantapi/Orders/' . $this->getId(), $data)
            ->expectSuccessful()
            ->status('200');

        return $this;
    }

    /**
     * Fetches the order.
     *
     * @link https://developers.qliro.com/docs/api/Ordersid-get
     *
     * @throws \Qliro\Exception\ConnectorException        When the API replies with an error response
     * @throws \GuzzleHttp\Exception\RequestException     When an error is encountered
     * @throws \RuntimeException         On an unexpected API response
     * @throws \RuntimeException         If the response content type is not JSON
     * @throws \InvalidArgumentException If the JSON cannot be parsed
     * @throws \LogicException           When Guzzle cannot populate the response
     *
     * @return static
     */
    public function getOrder(): static
    {
        $data = $this->get('/checkout/merchantapi/Orders/' . $this->getId())
            ->expectSuccessful()
            ->status('200')
            ->contentType('application/json')
            ->getJson();

        $this->exchangeArray($data);

        return $this;
    }
}
