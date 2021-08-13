<?php
declare(strict_types=1);

namespace Tada\Shopback\Api;

use GuzzleHttp\Psr7\Response;
use Magento\Sales\Api\Data\OrderItemInterface;

interface ShopbackValidateOrderInterface extends ShopbackServiceInterface
{
    /**
     * @param OrderItemInterface $orderItem
     */
    public function execute(OrderItemInterface $orderItem): Response;
}
