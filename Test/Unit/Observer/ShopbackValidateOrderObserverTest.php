<?php
declare(strict_types=1);

namespace Tada\Shopback\Test\Unit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Mockery;
use Psr\Log\LoggerInterface;
use Tada\CashbackTracking\Api\Data\CashbackTrackingExtensionFactory;
use Tada\CashbackTracking\Model\CashbackTracking;
use Tada\Shopback\Api\AddStackProcessorInterface;
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
        $this->cashbackExtensionFactory = Mockery::mock(CashbackTrackingExtensionFactory::class);
        $this->orderRepository = Mockery::mock(OrderRepositoryInterface::class);
        $this->addStackProcessor = Mockery::mock(AddStackProcessorInterface::class);
        $this->shopbackValidateOrderObserver = new ShopbackValidateOrderObserver(
            $this->logger,
            $this->cashbackExtensionFactory,
            $this->orderRepository,
            $this->addStackProcessor
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

        $orderId = 1;
        $orderCashbackTrackingEntity->shouldReceive('getOrderId')
            ->andReturn($orderId);

        $order = Mockery::mock(OrderInterface::class);

        $this->orderRepository
            ->shouldReceive('get')
            ->with($orderId)
            ->andReturn($order);

        $this->addStackProcessor
            ->shouldReceive('execute')
            ->with($order, 'validate');

        $this->assertNull($this->shopbackValidateOrderObserver->execute($observer));
    }

    public function testExecuteThrowException()
    {
        $observer = Mockery::mock(Observer::class);

        $orderCashbackTrackingEntity = Mockery::mock(CashbackTracking::class);
        $observer->shouldReceive('getData')
            ->with('order_partner_tracking')
            ->andReturn($orderCashbackTrackingEntity);

        $orderId = 1;
        $orderCashbackTrackingEntity->shouldReceive('getOrderId')
            ->andReturn($orderId);

        $order = Mockery::mock(OrderInterface::class);

        $this->orderRepository
            ->shouldReceive('get')
            ->with($orderId)
            ->andReturn($order);


        $e = Mockery::mock(\Exception::class);

        $this->addStackProcessor
            ->shouldReceive('execute')
            ->with($order, 'validate')
            ->andThrow($e);

        $this->logger
            ->shouldReceive('error')
            ->with($e);

        $this->assertNull($this->shopbackValidateOrderObserver->execute($observer));
    }
}
