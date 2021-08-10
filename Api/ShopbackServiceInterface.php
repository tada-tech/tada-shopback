<?php
declare(strict_types=1);

namespace Tada\Shopback\Api;

use GuzzleHttp\Psr7\Response;
use Magento\Sales\Api\Data\OrderInterface;

interface ShopbackServiceInterface
{
    /**
     * @param OrderInterface $order
     */
    public function execute(OrderInterface $order):Response;
}
