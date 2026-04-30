<?php

declare(strict_types=1);

namespace Qliro\AdminApi;

final class OrderItemStatus
{
    public const RESERVE = 'Reserve';

    public const CAPTURE = 'Ship';

    public const REFUND = 'Return';
}
