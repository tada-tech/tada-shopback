<?php
declare(strict_types=1);

namespace Tada\Shopback\Test\Unit\Observer;

use PHPUnit\Framework\TestCase;
use Mockery;
use Magento\Framework\Event\Observer;
use Magento\Framework\MessageQueue\PublisherInterface;
use Psr\Log\LoggerInterface;
use Tada\CashbackTracking\Api\CashbackTrackingRepositoryInterface;
use Tada\CashbackTracking\Api\Data\CashbackTrackingExtensionFactory;
use Tada\CashbackTracking\Model\CashbackTracking;
use Tada\Shopback\Api\Data\ShopbackStackInterface;
use Tada\Shopback\Api\ShopbackStackRepositoryInterface;
use Tada\Shopback\Observer\AfterCashbackTrackingSaveObserver;
use Tada\CashbackTracking\Api\Data\CashbackTrackingExtensionInterface;

class AfterCashbackTrackingSaveObserverTest extends TestCase
{
    /**
     * @var Mockery\MockInterface
     */
    protected $logger;

    /**
     * @var AfterCashbackTrackingSaveObserver
     */
    protected $afterCashbackTrackingSaveObserver;

    protected $publisher;

    protected $cashbackExtensionFactory;

    protected function setUp()
    {
        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->publisher = Mockery::mock(PublisherInterface::class);
        $this->cashbackExtensionFactory = Mockery::mock(CashbackTrackingExtensionFactory::class);

        $this->afterCashbackTrackingSaveObserver = new AfterCashbackTrackingSaveObserver(
            $this->logger,
            $this->publisher,
            $this->cashbackExtensionFactory
        );
    }

    protected function tearDown()
    {
        Mockery::close();
    }

    public function testExecute()
    {
        $cashbackEntity = Mockery::mock(CashbackTracking::class);
        $observer = Mockery::mock(Observer::class);
        $observer->shouldReceive('getData')
            ->with('data_object')
            ->andReturn($cashbackEntity);

        $cashbackEntity->shouldReceive('getExtensionAttributes')
            ->andReturnNull();

        $extensionAttributes = Mockery::mock(\Tada\CashbackTracking\Api\Data\CashbackTrackingExtension::class);
        $this->cashbackExtensionFactory
            ->shouldReceive('create')
            ->andReturn($extensionAttributes);

        $action = 'create';

        $extensionAttributes->shouldReceive('setAction')
            ->with($action)
            ->andReturnSelf();

        $cashbackEntity->shouldReceive('setExtensionAttributes')
            ->with($extensionAttributes)
            ->andReturnSelf();

        $topic = AfterCashbackTrackingSaveObserver::TOPIC_NAME;

        $this->publisher
            ->shouldReceive('publish')
            ->with($topic, $cashbackEntity);

        $this->assertNull($this->afterCashbackTrackingSaveObserver->execute($observer));
    }

    public function testExecuteAndThrowException()
    {
        $observer = Mockery::mock(Observer::class);
        $cashbackEntity = Mockery::mock(CashbackTracking::class);

        $observer->shouldReceive('getData')
            ->with('data_object')
            ->andReturn($cashbackEntity);

        $cashbackEntity->shouldReceive('getExtensionAttributes')
            ->andReturnNull();

        $extensionAttributes = Mockery::mock(\Tada\CashbackTracking\Api\Data\CashbackTrackingExtension::class);
        $this->cashbackExtensionFactory
            ->shouldReceive('create')
            ->andReturn($extensionAttributes);

        $action = 'create';

        $extensionAttributes->shouldReceive('setAction')
            ->with($action)
            ->andReturnSelf();

        $cashbackEntity->shouldReceive('setExtensionAttributes')
            ->with($extensionAttributes)
            ->andReturnSelf();

        $exception = Mockery::mock(\Exception::class);

        $topic = AfterCashbackTrackingSaveObserver::TOPIC_NAME;
        $this->publisher
            ->shouldReceive('publish')
            ->with($topic, $cashbackEntity)
            ->andThrow($exception);

        $this->logger
            ->shouldReceive('error')
            ->with($exception);

        $this->assertNull($this->afterCashbackTrackingSaveObserver->execute($observer));
    }
}
