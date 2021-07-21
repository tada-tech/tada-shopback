<?php
declare(strict_types=1);

namespace Tada\Shopback\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Psr\Log\LoggerInterface;
use Tada\CashbackTracking\Api\Data\CashbackTrackingExtensionFactory;
use Tada\CashbackTracking\Api\Data\CashbackTrackingInterface;

class ShopbackValidateOrderObserver implements ObserverInterface
{
    const TOPIC_NAME = "shopback.api";

    /**
     * @var CashbackTrackingExtensionFactory
     */
    protected $cashbackExtensionFactory;

    /**
     * @var PublisherInterface
     */
    protected $publisher;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * ShopbackValidateOrderObserver constructor.
     * @param CashbackTrackingExtensionFactory $cashbackExtensionFactory
     * @param PublisherInterface $publisher
     * @param LoggerInterface $logger
     */
    public function __construct(
        CashbackTrackingExtensionFactory $cashbackExtensionFactory,
        PublisherInterface $publisher,
        LoggerInterface $logger
    ) {
        $this->cashbackExtensionFactory = $cashbackExtensionFactory;
        $this->publisher = $publisher;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var CashbackTrackingInterface $cashbackTracking */
        $cashbackTracking = $observer->getData('order_partner_tracking');
        $extensionAttributes = $cashbackTracking->getExtensionAttributes() ?? $this->cashbackExtensionFactory->create();
        $extensionAttributes->setAction('validate');
        $cashbackTracking->setExtensionAttributes($extensionAttributes);

        try {
            $this->publisher->publish(self::TOPIC_NAME, $cashbackTracking);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
