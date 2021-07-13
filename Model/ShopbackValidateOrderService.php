<?php
declare(strict_types=1);

namespace Tada\Shopback\Model;

use Magento\Sales\Api\Data\OrderInterface;
use Tada\Shopback\Api\ShopbackValidateOrderInterface;

class ShopbackValidateOrderService implements ShopbackValidateOrderInterface
{
    /**
     * @param OrderInterface $order
     */
    public function execute(OrderInterface $order): void
    {
        // TODO: Implement execute() method.
    }
}
