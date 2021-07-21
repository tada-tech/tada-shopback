<?php
declare(strict_types=1);

namespace Tada\Shopback\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Psr\Log\LoggerInterface;
use Tada\CashbackTracking\Api\CashbackTrackingRepositoryInterface;
use Tada\CashbackTracking\Api\Data\CashbackTrackingExtensionFactory;
use Tada\CashbackTracking\Api\Data\CashbackTrackingInterface;

class AfterCashbackTrackingSaveObserver implements ObserverInterface
{
    const TOPIC_NAME = "shopback.api";

    /**
     * @var CashbackTrackingExtensionFactory
     */
    protected $cashbackExtensionFactory;
    /**
     * @var CashbackTrackingRepositoryInterface
     */
    protected $cashbackRepository;

    /**
     * @var PublisherInterface
     */
    protected $publisher;

    /**
     * @var LoggerInterfac
     */
    protected $logger;

    /**
     * AfterCashbackTrackingSaveObserver constructor.
     * @param CashbackTrackingExtensionFactory $cashbackExtensionFactory
     * @param CashbackTrackingRepositoryInterface $cashbackTrackingRepository
     * @param PublisherInterface $publisher
     * @param LoggerInterface $logger
     */
    public function __construct(
        CashbackTrackingExtensionFactory $cashbackExtensionFactory,
        CashbackTrackingRepositoryInterface $cashbackTrackingRepository,
        PublisherInterface $publisher,
        LoggerInterface $logger
    ) {
        $this->cashbackExtensionFactory = $cashbackExtensionFactory;
        $this->cashbackRepository = $cashbackTrackingRepository;
        $this->publisher = $publisher;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var CashbackTrackingInterface $cashbackTracking */
        $cashbackTracking = $observer->getData('data_object');
        $extensionAttributes = $cashbackTracking->getExtensionAttributes() ?? $this->cashbackExtensionFactory->create();
        $extensionAttributes->setAction('create');
        $cashbackTracking->setExtensionAttributes($extensionAttributes);

        try {
            $this->publisher->publish(self::TOPIC_NAME, $cashbackTracking);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
