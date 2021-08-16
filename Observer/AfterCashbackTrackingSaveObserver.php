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

class AfterCashbackTrackingSaveObserver implements ObserverInterface
{
    /**
     * @var LoggerInterfac
     */
    protected $logger;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var AddStackProcessorInterface
     */
    protected $addStackProcessor;

    /**
     * @var CashbackTrackingExtensionFactory
     */
    protected $cashbackExtensionFactory;

    /**
     * @param LoggerInterface $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param CashbackTrackingExtensionFactory $cashbackExtensionFactory
     * @param AddStackProcessorInterface $addStackProcessor
     */
    public function __construct(
        LoggerInterface $logger,
        OrderRepositoryInterface $orderRepository,
        CashbackTrackingExtensionFactory $cashbackExtensionFactory,
        AddStackProcessorInterface $addStackProcessor
    ) {
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->cashbackExtensionFactory = $cashbackExtensionFactory;
        $this->addStackProcessor = $addStackProcessor;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var CashbackTrackingInterface $cashbackTracking */
        $cashbackTracking = $observer->getData('data_object');
        $orderId = (int)$cashbackTracking->getOrderId();

        /** @var OrderInterface $order */
        $order = $this->orderRepository->get($orderId);

        try {
            $this->addStackProcessor->execute($order, 'create');
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
