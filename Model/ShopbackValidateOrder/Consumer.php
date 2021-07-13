<?php
declare(strict_types=1);

namespace Tada\Shopback\Model\ShopbackValidateOrder;

use Tada\CashbackTracking\Api\Data\CashbackTrackingInterface as OrderPartnerTrackingInterface;
use Tada\Shopback\Api\ShopbackValidateOrderInterface;
use Magento\Sales\Api\Data\OrderInterface;

class Consumer
{
    /**
     * @var ShopbackValidateOrderInterface
     */
    protected $shopbackValidateOrder;

    /**
     * Consumer constructor.
     * @param ShopbackValidateOrderInterface $shopbackValidateOrder
     */
    public function __construct(
        ShopbackValidateOrderInterface $shopbackValidateOrder
    ) {
        $this->shopbackValidateOrder = $shopbackValidateOrder;
    }

    /**
     * @param OrderInterface $order
     */
    public function process(OrderInterface $order)
    {
        $this->shopbackValidateOrder->execute($order);
    }
}
