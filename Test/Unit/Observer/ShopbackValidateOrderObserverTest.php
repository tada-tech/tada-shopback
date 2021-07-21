<?php
declare(strict_types=1);

namespace Tada\Shopback\Test\Unit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\MessageQueue\PublisherInterface;
use PHPUnit\Framework\TestCase;
use Mockery;
use Psr\Log\LoggerInterface;
use Tada\CashbackTracking\Api\Data\CashbackTrackingExtensionFactory;
use Tada\CashbackTracking\Model\CashbackTracking;
use Tada\Shopback\Observer\ShopbackValidateOrderObserver;
use Tada\CashbackTracking\Api\Data\CashbackTrackingExtensionInterface;

class ShopbackValidateOrderObserverTest extends TestCase
{
    /**
     * @var Mockery\MockInterface
     */
    protected $logger;

    /**
     * @var Mockery\MockInterface
     */
    protected $publisher;

    /**
     * @var ShopbackValidateOrderObserver
     */
    protected $shopbackValidateOrderObserver;

    /**
     * @var Mockery\MockInterface
     */
    protected $cashbackExtensionFactory;

    protected function setUp()
    {
        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->publisher = Mockery::mock(PublisherInterface::class);
        $this->cashbackExtensionFactory = Mockery::mock(CashbackTrackingExtensionFactory::class);
        $this->shopbackValidateOrderObserver = new ShopbackValidateOrderObserver(
            $this->cashbackExtensionFactory,
            $this->publisher,
            $this->logger
        );
    }

    protected function tearDown()
    {
        Mockery::close();
    }

    public function testExecute()
    {
        $observer = Mockery::mock(Observer::class);

        $orderCashbackTrackingEntity = Mockery::mock(CashbackTracking::class);
        $observer->shouldReceive('getData')
            ->with('order_partner_tracking')
            ->andReturn($orderCashbackTrackingEntity);

        $extensionAttributes = Mockery::mock(CashbackTrackingExtensionInterface::class);
        $orderCashbackTrackingEntity->shouldReceive('getExtensionAttributes')
            ->andReturn($extensionAttributes);

        $extensionAttributes->shouldReceive('setAction')
            ->with('validate')
            ->andReturnSelf();

        $orderCashbackTrackingEntity->shouldReceive('setExtensionAttributes')
            ->with($extensionAttributes)
            ->andReturnSelf();

        $this->publisher->shouldReceive('publish')
            ->with(ShopbackValidateOrderObserver::TOPIC_NAME, $orderCashbackTrackingEntity);

        $this->assertNull($this->shopbackValidateOrderObserver->execute($observer));
    }

    public function testExecuteThrowException()
    {
        $observer = Mockery::mock(Observer::class);

        $orderCashbackTrackingEntity = Mockery::mock(CashbackTracking::class);
        $observer->shouldReceive('getData')
            ->with('order_partner_tracking')
            ->andReturn($orderCashbackTrackingEntity);

        $extensionAttributes = Mockery::mock(CashbackTrackingExtensionInterface::class);
        $orderCashbackTrackingEntity->shouldReceive('getExtensionAttributes')
            ->andReturn($extensionAttributes);

        $extensionAttributes->shouldReceive('setAction')
            ->with('validate')
            ->andReturnSelf();

        $orderCashbackTrackingEntity->shouldReceive('setExtensionAttributes')
            ->with($extensionAttributes)
            ->andReturnSelf();

        $e = Mockery::mock(\Exception::class);
        $this->publisher->shouldReceive('publish')
            ->with(ShopbackValidateOrderObserver::TOPIC_NAME, $orderCashbackTrackingEntity)
            ->andThrow($e);

        $this->logger->shouldReceive('error')
            ->with($e);

        $this->assertNull($this->shopbackValidateOrderObserver->execute($observer));
    }
}
