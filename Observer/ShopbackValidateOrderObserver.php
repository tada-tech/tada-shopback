<?php
declare(strict_types=1);

namespace Tada\Shopback\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Tada\CashbackTracking\Api\Data\CashbackTrackingInterface;
use Tada\Shopback\Api\ShopbackStackRepositoryInterface;

class ShopbackValidateOrderObserver implements ObserverInterface
{
    /**
     * @var ShopbackStackRepositoryInterface
     */
    protected $shopbackStackRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * ShopbackValidateOrderObserver constructor.
     * @param ShopbackStackRepositoryInterface $shopbackStackRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        ShopbackStackRepositoryInterface $shopbackStackRepository,
        LoggerInterface $logger
    ) {
        $this->shopbackStackRepository = $shopbackStackRepository;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var CashbackTrackingInterface $cashbackTracking */
        $cashbackTracking = $observer->getData('order_partner_tracking');
        $orderId = (int)$cashbackTracking->getOrderId();

        try {
            $this->shopbackStackRepository->addToStackWithAction($orderId, 'validate');
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
