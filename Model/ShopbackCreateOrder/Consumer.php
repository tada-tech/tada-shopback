<?php
declare(strict_types=1);

namespace Tada\Shopback\Model\ShopbackCreateOrder;

use Tada\CashbackTracking\Api\Data\CashbackTrackingInterface as OrderPartnerTrackingInterface;
use Tada\Shopback\Api\ShopbackCreateOrderInterface;

class Consumer
{
    /**
     * @var ShopbackCreateOrderInterface
     */
    protected $shopbackCreateOrderService;

    /**
     * Consumer constructor.
     * @param ShopbackCreateOrderInterface $shopbackCreateOrderService
     */
    public function __construct(
        ShopbackCreateOrderInterface $shopbackCreateOrderService
    ) {
        $this->shopbackCreateOrderService = $shopbackCreateOrderService;
    }

    /**
     * @param OrderPartnerTrackingInterface $orderPartnerTracking
     */
    public function process(OrderPartnerTrackingInterface $orderPartnerTracking)
    {
        $this->shopbackCreateOrderService->execute($orderPartnerTracking);
    }
}
