<?php
declare(strict_types=1);

namespace Tada\Shopback\Model;

use Tada\CashbackTracking\Api\Data\CashbackTrackingInterface as OrderPartnerTrackingInterface;
use Tada\Shopback\Api\ShopbackCreateOrderInterface;

class ShopbackCreateOrderService implements ShopbackCreateOrderInterface
{
    /**
     * @param OrderPartnerTrackingInterface $orderPartnerTracking
     */
    public function execute(OrderPartnerTrackingInterface $orderPartnerTracking): void
    {
        //TODO: CALL HTTP REQUEST TO SHOPBACK CREATE ORDER API
    }
}
