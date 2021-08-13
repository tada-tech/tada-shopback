<?php
declare(strict_types=1);

namespace Tada\Shopback\Api;

use GuzzleHttp\Psr7\Response;
use Magento\Sales\Api\Data\OrderItemInterface;
use Tada\Shopback\Api\Data\ShopbackStackInterface;

interface ShopbackServiceInterface
{
    const API_REQUEST_URI = "http://shopback.go2cloud.org/";
    const API_REQUEST_ENDPOINT = "aff_lsr";

    /**
     * @param OrderItemInterface $orderItem
     */
    public function execute(OrderItemInterface $orderItem): Response;

    /**
     * @param ShopbackStackInterface $shopbackStack
     */
    public function beforeExecute(ShopbackStackInterface $shopbackStack): void;

    /**
     * @param Response $response
     * @param ShopbackStackInterface $shopbackStack
     */
    public function afterExecute(Response $response, ShopbackStackInterface $shopbackStack): void;

    /**
     * @param ShopbackStackInterface $shopbackStack
     * @return bool
     */
    public function isSkip(ShopbackStackInterface $shopbackStack): bool;
}
