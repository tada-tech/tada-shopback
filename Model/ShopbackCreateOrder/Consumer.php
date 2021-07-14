<?php
declare(strict_types=1);

namespace Tada\Shopback\Model\ShopbackCreateOrder;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Tada\CashbackTracking\Api\Data\CashbackTrackingInterface as OrderPartnerTrackingInterface;
use Tada\Shopback\Api\ShopbackCreateOrderInterface;
use Psr\Log\LoggerInterface;

class Consumer
{
    /**
     * @var ShopbackCreateOrderInterface
     */
    protected $shopbackCreateOrderService;

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
     * @param ShopbackCreateOrderInterface $shopbackCreateOrderService
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        ShopbackCreateOrderInterface $shopbackCreateOrderService,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger
    ) {
        $this->shopbackCreateOrderService = $shopbackCreateOrderService;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }

    /**
     * @param OrderPartnerTrackingInterface $orderPartnerTracking
     */
    public function process(OrderPartnerTrackingInterface $orderPartnerTracking)
    {
        try {
            /** @var OrderInterface $order */
            $order = $this->orderRepository->get((int)$orderPartnerTracking->getOrderId());
            $this->shopbackCreateOrderService->execute($order);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
