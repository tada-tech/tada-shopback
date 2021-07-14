<?php
declare(strict_types=1);

namespace Tada\Shopback\Api;

use Tada\Shopback\Api\Data\FakeShopbackResponseInterface;

interface FakeShopbackRepositoryInterface
{
    /**
     * GET - Create Order
     * @return FakeShopbackResponseInterface
     */
    public function createOrder();

    /**
     * GET - Validate Order
     * @return FakeShopbackResponseInterface
     */
    public function validateOrder();
}
