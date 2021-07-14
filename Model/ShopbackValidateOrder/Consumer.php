<?php
declare(strict_types=1);

namespace Tada\Shopback\Model\ShopbackValidateOrder;

use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Tada\Shopback\Api\ShopbackValidateOrderInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Tada\CashbackTracking\Api\Data\CashbackTrackingInterface as OrderPartnerTrackingInterface;

class Consumer
{
    /**
     * @var ShopbackValidateOrderInterface
     */
    protected $shopbackValidateOrder;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Consumer constructor.
     * @param ShopbackValidateOrderInterface $shopbackValidateOrder
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        ShopbackValidateOrderInterface $shopbackValidateOrder,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger
    ) {
        $this->shopbackValidateOrder = $shopbackValidateOrder;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }

    /**
     * @param OrderInterface $order
     */
    public function process(OrderPartnerTrackingInterface $orderPartnerTracking)
    {
        try {
            /** @var OrderInterface $order */
            $order = $this->orderRepository->get((int)$orderPartnerTracking->getOrderId());
            $this->shopbackValidateOrder->execute($order);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
