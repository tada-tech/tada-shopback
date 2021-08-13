<?php
declare(strict_types=1);

namespace Tada\Shopback\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Psr\Log\LoggerInterface;
use Tada\CashbackTracking\Api\Data\CashbackTrackingInterface;
use Tada\CashbackTracking\Api\Data\CashbackTrackingExtensionFactory;

class ShopbackValidateOrderObserver implements ObserverInterface
{
    const TOPIC_NAME = 'shopback_stack.add';

    /**
     * @var PublisherInterface
     */
    protected $publisher;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CashbackTrackingExtensionFactory
     */
    protected $cashbackExtensionFactory;

    /**
     * @param PublisherInterface $publisher
     * @param LoggerInterface $logger
     * @param CashbackTrackingExtensionFactory $cashbackTrackingExtensionFactory
     */
    public function __construct(
        PublisherInterface $publisher,
        LoggerInterface $logger,
        CashbackTrackingExtensionFactory $cashbackTrackingExtensionFactory
    ) {
        $this->publisher = $publisher;
        $this->logger = $logger;
        $this->cashbackExtensionFactory = $cashbackTrackingExtensionFactory;
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
