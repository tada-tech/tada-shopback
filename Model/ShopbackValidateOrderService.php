<?php
declare(strict_types=1);

namespace Tada\Shopback\Model;

use Tada\CashbackTracking\Api\Data\CashbackTrackingInterface as OrderPartnerTrackingInterface;
use Tada\Shopback\Api\ShopbackValidateOrderInterface;

class ShopbackValidateOrderService implements ShopbackValidateOrderInterface
{
    /**
     * @param OrderPartnerTrackingInterface $orderPartnerTracking
     */
    public function execute(OrderPartnerTrackingInterface $orderPartnerTracking): void
    {
        // TODO: Implement execute() method.
    }
}
