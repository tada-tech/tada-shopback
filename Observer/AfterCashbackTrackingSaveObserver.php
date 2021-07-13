<?php
declare(strict_types=1);

namespace Tada\Shopback\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Psr\Log\LoggerInterface;

class AfterCashbackTrackingSaveObserver implements ObserverInterface
{
    const TOPIC_NAME = "shopback.create_order";

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
     * @param PublisherInterface $publisher
     * @param LoggerInterface $logger
     */
    public function __construct(
        PublisherInterface $publisher,
        LoggerInterface $logger
    ) {
        $this->publisher = $publisher;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $cashbackTracking = $observer->getData('data_object');
        try {
            $this->publisher->publish(self::TOPIC_NAME, $cashbackTracking);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
