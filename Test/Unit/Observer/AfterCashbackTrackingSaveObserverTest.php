<?php
declare(strict_types=1);

namespace Tada\Shopback\Test\Unit\Observer;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Mockery;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;
use Tada\CashbackTracking\Api\Data\CashbackTrackingExtensionFactory;
use Tada\CashbackTracking\Model\CashbackTracking;
use Tada\Shopback\Api\AddStackProcessorInterface;
use Tada\Shopback\Observer\AfterCashbackTrackingSaveObserver;

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

    /**
     * @var Mockery\MockInterface
     */
    protected $cashbackExtensionFactory;

    /**
     * @var Mockery\MockInterface
     */
    protected $orderRepository;

    /**
     * @var Mockery\MockInterface
     */
    protected $addStackProcessor;

    protected function setUp()
    {
        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->orderRepository = Mockery::mock(OrderRepositoryInterface::class);
        $this->cashbackExtensionFactory = Mockery::mock(CashbackTrackingExtensionFactory::class);
        $this->addStackProcessor = Mockery::mock(AddStackProcessorInterface::class);
        $this->afterCashbackTrackingSaveObserver = new AfterCashbackTrackingSaveObserver(
            $this->logger,
            $this->orderRepository,
            $this->cashbackExtensionFactory,
            $this->addStackProcessor
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

        $order = Mockery::mock(OrderInterface::class);

        $this->orderRepository
            ->shouldReceive('get')
            ->with($orderId)
            ->andReturn($order);

        $this->addStackProcessor
            ->shouldReceive('execute')
            ->with($order, 'create');

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

        $order = Mockery::mock(OrderInterface::class);

        $this->orderRepository
            ->shouldReceive('get')
            ->with($orderId)
            ->andReturn($order);

        $exception = Mockery::mock(\Exception::class);

        $this->addStackProcessor
            ->shouldReceive('execute')
            ->with($order, 'create')
            ->andThrow($exception);

        $this->logger
            ->shouldReceive('error')
            ->with($exception);

        $this->assertNull($this->afterCashbackTrackingSaveObserver->execute($observer));
    }
}
