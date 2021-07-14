<?php
declare(strict_types=1);

namespace Tada\Shopback\Api;

use Magento\Sales\Api\Data\OrderInterface;

interface ShopbackValidateOrderInterface
{
    /**
     * @param OrderInterface $order
     */
    public function execute(OrderInterface $order):void;
}
