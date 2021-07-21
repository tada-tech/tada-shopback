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
use Tada\Shopback\Observer\AfterCashbackTrackingSaveObserver;
use Tada\CashbackTracking\Api\Data\CashbackTrackingExtensionInterface;

class AfterCashbackTrackingSaveObserverTest extends TestCase
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
     * @var AfterCashbackTrackingSaveObserver
     */
    protected $afterCashbackTrackingSaveObserver;

    protected $cashbackTrackingRepository;

    protected $cashbackExtensionFactory;

    protected function setUp()
    {
        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->publisher = Mockery::mock(PublisherInterface::class);
        $this->cashbackTrackingRepository = Mockery::mock(CashbackTrackingRepositoryInterface::class);
        $this->cashbackExtensionFactory = Mockery::mock(CashbackTrackingExtensionFactory::class);
        $this->afterCashbackTrackingSaveObserver = new AfterCashbackTrackingSaveObserver(
            $this->cashbackExtensionFactory,
            $this->cashbackTrackingRepository,
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
        $cashbackEntity = Mockery::mock(CashbackTracking::class);
        $observer = Mockery::mock(Observer::class);
        $observer->shouldReceive('getData')
            ->with('data_object')
            ->andReturn($cashbackEntity);

        $extensionAttributes = Mockery::mock(CashbackTrackingExtensionInterface::class);

        $cashbackEntity->shouldReceive('getExtensionAttributes')
            ->andReturn(null);

        $this->cashbackExtensionFactory
            ->shouldReceive('create')
            ->andReturn($extensionAttributes);

        $extensionAttributes->shouldReceive('setAction')
            ->with('create')
            ->andReturnSelf();

        $cashbackEntity->shouldReceive('setExtensionAttributes')
            ->with($extensionAttributes)
            ->andReturnSelf();

        $this->publisher
            ->shouldReceive('publish')
            ->with(AfterCashbackTrackingSaveObserver::TOPIC_NAME, $cashbackEntity);

        $this->assertNull($this->afterCashbackTrackingSaveObserver->execute($observer));
    }

    public function testExecuteAndThrowException()
    {
        $observer = Mockery::mock(Observer::class);
        $cashbackEntity = Mockery::mock(CashbackTracking::class);

        $observer->shouldReceive('getData')
            ->with('data_object')
            ->andReturn($cashbackEntity);

        $extensionAttributes = Mockery::mock(CashbackTrackingExtensionInterface::class);

        $cashbackEntity->shouldReceive('getExtensionAttributes')
            ->andReturn($extensionAttributes);

        $extensionAttributes->shouldReceive('setAction')
            ->with('create')
            ->andReturnSelf();

        $cashbackEntity->shouldReceive('setExtensionAttributes')
            ->with($extensionAttributes)
            ->andReturnSelf();

        $exception = Mockery::mock(\Exception::class);

        $this->publisher
            ->shouldReceive('publish')
            ->with(AfterCashbackTrackingSaveObserver::TOPIC_NAME, $cashbackEntity)
            ->andThrow($exception);

        $this->logger
            ->shouldReceive('error')
            ->with($exception);

        $this->assertNull($this->afterCashbackTrackingSaveObserver->execute($observer));
    }
}
