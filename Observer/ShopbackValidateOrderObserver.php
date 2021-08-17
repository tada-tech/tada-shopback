<?php
declare(strict_types=1);

namespace Tada\Shopback\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Tada\CashbackTracking\Api\Data\CashbackTrackingInterface;
use Tada\CashbackTracking\Api\Data\CashbackTrackingExtensionFactory;
use Tada\Shopback\Api\AddStackProcessorInterface;

class ShopbackValidateOrderObserver implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CashbackTrackingExtensionFactory
     */
    protected $cashbackExtensionFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var AddStackProcessorInterface
     */
    protected $addStackProcessor;

    /**
     * @param LoggerInterface $logger
     * @param CashbackTrackingExtensionFactory $cashbackTrackingExtensionFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param AddStackProcessorInterface $addStackProcessor
     */
    public function __construct(
        LoggerInterface $logger,
        CashbackTrackingExtensionFactory $cashbackTrackingExtensionFactory,
        OrderRepositoryInterface $orderRepository,
        AddStackProcessorInterface $addStackProcessor
    ) {
        $this->logger = $logger;
        $this->cashbackExtensionFactory = $cashbackTrackingExtensionFactory;
        $this->orderRepository = $orderRepository;
        $this->addStackProcessor = $addStackProcessor;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var CashbackTrackingInterface $cashbackTracking */
        $cashbackTracking = $observer->getData('order_partner_tracking');
        $orderId = (int)$cashbackTracking->getOrderId();

        /** @var OrderInterface $order */
        $order = $this->orderRepository->get($orderId);

        try {
            $this->addStackProcessor->execute($order, 'validate');
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
