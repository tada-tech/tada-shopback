<?php
declare(strict_types=1);

namespace Tada\Shopback\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Psr\Log\LoggerInterface;
use Tada\CashbackTracking\Api\Data\CashbackTrackingInterface;
use Tada\CashbackTracking\Api\Data\CashbackTrackingExtensionFactory;

class AfterCashbackTrackingSaveObserver implements ObserverInterface
{
    const TOPIC_NAME = 'shopback_stack.add';

    /**
     * @var LoggerInterfac
     */
    protected $logger;

    /**
     * @var PublisherInterface
     */
    protected $publisher;

    /**
     * @var CashbackTrackingExtensionFactory
     */
    protected $cashbackExtensionFactory;

    public function __construct(
        LoggerInterface $logger,
        PublisherInterface $publisher,
        CashbackTrackingExtensionFactory $cashbackExtensionFactory
    ) {
        $this->logger = $logger;
        $this->publisher = $publisher;
        $this->cashbackExtensionFactory = $cashbackExtensionFactory;
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
