<?php
declare(strict_types=1);

namespace Tada\Shopback\Api;

use Tada\CashbackTracking\Api\Data\CashbackTrackingInterface as OrderPartnerTrackingInterface;

interface ShopbackCreateOrderInterface
{
    /**
     * @param OrderPartnerTrackingInterface $orderPartnerTracking
     */
    public function execute(OrderPartnerTrackingInterface $orderPartnerTracking):void;
}
