<?php

declare(strict_types=1);

namespace Qliro\AdminApi;

use Qliro\Resource;
use Qliro\Transport\ConnectorInterface;

class Payment extends Resource
{
    /**
     * {@inheritDoc}
     */
    protected const ID_FIELD = 'PaymentTransactionId';

    /**
     * Constructs an payment instance.
     *
     * @param ConnectorInterface $connector HTTP transport connector
     * @param string|null        $paymentTransactionId   Payment Transaction ID
     */
    public function __construct(ConnectorInterface $connector, ?string $paymentTransactionId = null)
    {
        parent::__construct($connector);

        if ($paymentTransactionId !== null) {
            $this[static::ID_FIELD] = $paymentTransactionId;
        }
    }

    /**
     * Get payment transaction data.
     *
     * @link https://developers.qliro.com/docs/api/v2paymentTransactionsid-get
     *
     * @param string $paymentTransactionId
     *
     * @return static
     */
    public function getTransaction(string $paymentTransactionId = ''): static
    {
        $paymentTransactionId = (!empty($paymentTransactionId) ? $paymentTransactionId : $this[static::ID_FIELD]);
        $data = $this->get('/checkout/adminapi/v2/paymentTransactions/' . $paymentTransactionId)
            ->expectSuccessful()
            ->status('200')
            ->contentType('application/json')
            ->getJson();

        $this->exchangeArray($data);

        return $this;
    }

}
