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
use Tada\Shopback\Api\Data\ShopbackStackInterface;
use Tada\Shopback\Observer\ShopbackValidateOrderObserver;

class ShopbackValidateOrderObserverTest extends TestCase
{
    /**
     * @var Mockery\MockInterface
     */
    protected $logger;

    /**
     * @var ShopbackValidateOrderObserver
     */
    protected $shopbackValidateOrderObserver;

    protected $publisher;

    protected $cashbackExtensionFactory;

    protected function setUp()
    {
        $this->publisher = Mockery::mock(PublisherInterface::class);
        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->cashbackExtensionFactory = Mockery::mock(CashbackTrackingExtensionFactory::class);
        $this->shopbackValidateOrderObserver = new ShopbackValidateOrderObserver(
            $this->publisher,
            $this->logger,
            $this->cashbackExtensionFactory
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

        $orderCashbackTrackingEntity
            ->shouldReceive('getExtensionAttributes')
            ->andReturnNull();

        $extensionAttributes = Mockery::mock(\Tada\CashbackTracking\Api\Data\CashbackTrackingExtension::class);

        $this->cashbackExtensionFactory
            ->shouldReceive('create')
            ->andReturn($extensionAttributes);

        $action = 'validate';

        $extensionAttributes->shouldReceive('setAction')
            ->with($action)
            ->andReturnSelf();

        $orderCashbackTrackingEntity->shouldReceive('setExtensionAttributes')
            ->with($extensionAttributes)
            ->andReturnSelf();

        $topic = ShopbackValidateOrderObserver::TOPIC_NAME;

        $this->publisher
            ->shouldReceive('publish')
            ->with($topic, $orderCashbackTrackingEntity);

        $this->assertNull($this->shopbackValidateOrderObserver->execute($observer));
    }

    public function testExecuteThrowException()
    {
        $observer = Mockery::mock(Observer::class);

        $orderCashbackTrackingEntity = Mockery::mock(CashbackTracking::class);
        $observer->shouldReceive('getData')
            ->with('order_partner_tracking')
            ->andReturn($orderCashbackTrackingEntity);

        $orderCashbackTrackingEntity
            ->shouldReceive('getExtensionAttributes')
            ->andReturnNull();

        $extensionAttributes = Mockery::mock(\Tada\CashbackTracking\Api\Data\CashbackTrackingExtension::class);

        $this->cashbackExtensionFactory
            ->shouldReceive('create')
            ->andReturn($extensionAttributes);

        $action = 'validate';

        $extensionAttributes->shouldReceive('setAction')
            ->with($action)
            ->andReturnSelf();

        $orderCashbackTrackingEntity->shouldReceive('setExtensionAttributes')
            ->with($extensionAttributes)
            ->andReturnSelf();

        $topic = ShopbackValidateOrderObserver::TOPIC_NAME;


        $e = Mockery::mock(\Exception::class);

        $this->publisher
            ->shouldReceive('publish')
            ->with($topic, $orderCashbackTrackingEntity)
            ->andThrow($e);

        $this->logger->shouldReceive('error')
            ->with($e);

        $this->assertNull($this->shopbackValidateOrderObserver->execute($observer));
    }
}
