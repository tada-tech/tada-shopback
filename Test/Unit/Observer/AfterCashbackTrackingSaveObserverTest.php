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

    protected $cashbackTrackingRepository;

    protected $shopbackStackRepository;

    protected function setUp()
    {
        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->cashbackTrackingRepository = Mockery::mock(CashbackTrackingRepositoryInterface::class);
        $this->shopbackStackRepository = Mockery::mock(ShopbackStackRepositoryInterface::class);
        $this->afterCashbackTrackingSaveObserver = new AfterCashbackTrackingSaveObserver(
            $this->shopbackStackRepository,
            $this->cashbackTrackingRepository,
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

        $orderId = 1;

        $cashbackEntity->shouldReceive('getOrderId')
            ->andReturn($orderId);

        $shopbackStack = Mockery::mock(ShopbackStackInterface::class);
        $this->shopbackStackRepository
            ->shouldReceive('addToStackWithAction')
            ->with($orderId, 'create')
            ->andReturn($shopbackStack);

        $this->assertNull($this->afterCashbackTrackingSaveObserver->execute($observer));
    }

    public function testExecuteAndThrowException()
    {
        $observer = Mockery::mock(Observer::class);
        $cashbackEntity = Mockery::mock(CashbackTracking::class);

        $observer->shouldReceive('getData')
            ->with('data_object')
            ->andReturn($cashbackEntity);


        $orderId = 1;

        $cashbackEntity->shouldReceive('getOrderId')
            ->andReturn($orderId);

        $exception = Mockery::mock(\Exception::class);

        $this->shopbackStackRepository
            ->shouldReceive('addToStackWithAction')
            ->with($orderId, 'create')
            ->andReturn($exception);

        $this->logger
            ->shouldReceive('error')
            ->with($exception);

        $this->assertNull($this->afterCashbackTrackingSaveObserver->execute($observer));
    }
}
