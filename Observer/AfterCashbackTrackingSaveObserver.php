<?php
declare(strict_types=1);

namespace Tada\Shopback\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Tada\CashbackTracking\Api\CashbackTrackingRepositoryInterface;
use Tada\CashbackTracking\Api\Data\CashbackTrackingInterface;
use Tada\Shopback\Api\ShopbackStackRepositoryInterface;

class AfterCashbackTrackingSaveObserver implements ObserverInterface
{
    /**
     * @var ShopbackStackRepositoryInterface
     */
    protected $shopbackStackRepository;
    /**
     * @var CashbackTrackingRepositoryInterface
     */
    protected $cashbackRepository;

    /**
     * @var LoggerInterfac
     */
    protected $logger;

    /**
     * AfterCashbackTrackingSaveObserver constructor.
     * @param ShopbackStackRepositoryInterface $shopbackStackRepository
     * @param CashbackTrackingRepositoryInterface $cashbackTrackingRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        ShopbackStackRepositoryInterface $shopbackStackRepository,
        CashbackTrackingRepositoryInterface $cashbackTrackingRepository,
        LoggerInterface $logger
    ) {
        $this->shopbackStackRepository = $shopbackStackRepository;
        $this->cashbackRepository = $cashbackTrackingRepository;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var CashbackTrackingInterface $cashbackTracking */
        $cashbackTracking = $observer->getData('data_object');
        $orderId = (int)$cashbackTracking->getOrderId();

        try {
            $this->shopbackStackRepository->addToStackWithAction($orderId, 'create');
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
