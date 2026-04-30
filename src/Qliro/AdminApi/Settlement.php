<?php

declare(strict_types=1);

namespace Qliro\AdminApi;

use Qliro\Resource;
use Qliro\Transport\ConnectorInterface;

class Settlement extends Resource
{
    /**
     * Constructs a settlement instance.
     *
     * @param ConnectorInterface $connector HTTP transport connector
     */
    public function __construct(ConnectorInterface $connector)
    {
        parent::__construct($connector);
    }

    /**
     * Get consolidated settlements.
     *
     * @link https://developers.qliro.com/docs/api/v2settlements-get
     *
     * @param array<string, mixed>       $data
     *
     * @return array<int, array<string, mixed>>
     */
    public function getSettlements(array $data = []): array
    {
        $this->validateData([
            'FromDate',
            'ToDate',
        ], $data);

        return $this->get('/checkout/adminapi/v2/settlements', $data)
            ->expectSuccessful()
            ->status('200')
            ->contentType('application/json')
            ->getJson();
    }
}
