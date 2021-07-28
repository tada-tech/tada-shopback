<?php
declare(strict_types=1);

namespace Tada\Shopback\Api;

interface FakeShopbackRepositoryInterface
{
    /**
     * GET - Create Order
     * @return \Tada\Shopback\Api\Data\FakeShopbackResponseInterface
     */
    public function createOrder();

    /**
     * GET - Validate Order
     * @return \Tada\Shopback\Api\Data\FakeShopbackResponseInterface
     */
    public function validateOrder();
}
