<?php
declare(strict_types=1);

namespace Tada\Shopback\Test\Unit\Model\ShopbackCreateOrder;

use Magento\Sales\Api\OrderRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Mockery;
use Psr\Log\LoggerInterface;
use Tada\Shopback\Api\ShopbackCreateOrderInterface;
use Tada\Shopback\Model\ShopbackCreateOrder\Consumer;
use Tada\CashbackTracking\Api\Data\CashbackTrackingInterface as OrderPartnerTrackingInterface;
use Magento\Sales\Api\Data\OrderInterface;

class ConsumerTest extends TestCase
{
    /**
     * @var Mockery\MockInterface
     */
    protected $shopbackCreateOrderService;

    /**
     * @var Mockery\MockInterface
     */
    protected $orderRepository;

    /**
     * @var Mockery\MockInterface
     */
    protected $logger;
    /**
     * @var Consumer
     */
    protected $consumer;

    public function setUp()
    {
        $this->shopbackCreateOrderService = Mockery::mock(ShopbackCreateOrderInterface::class);
        $this->orderRepository = Mockery::mock(OrderRepositoryInterface::class);
        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->consumer = new Consumer(
            $this->shopbackCreateOrderService,
            $this->orderRepository,
            $this->logger
        );
    }

    protected function tearDown()
    {
        Mockery::close();
    }

    public function testProcess()
    {
        $orderId = 1;
        $orderPartnerTracking = Mockery::mock(OrderPartnerTrackingInterface::class);
        $orderPartnerTracking->shouldReceive('getOrderId')
            ->andReturn($orderId);

        $order = Mockery::mock(OrderInterface::class);
        $this->orderRepository
            ->shouldReceive('get')
            ->with($orderId)
            ->andReturn($order);

        $this->shopbackCreateOrderService
            ->shouldReceive('execute')
            ->with($order);

        $this->assertNull($this->consumer->process($orderPartnerTracking));
    }

    public function testProcessThrowExceptionAndLogFile()
    {
        $orderId = 1;
        $orderPartnerTracking = Mockery::mock(OrderPartnerTrackingInterface::class);
        $orderPartnerTracking->shouldReceive('getOrderId')
            ->andReturn($orderId);

        $order = Mockery::mock(OrderInterface::class);
        $this->orderRepository
            ->shouldReceive('get')
            ->with($orderId)
            ->andReturn($order);

        $e = Mockery::mock(\Exception::class);

        $this->shopbackCreateOrderService
            ->shouldReceive('execute')
            ->with($order)
            ->andThrow($e);

        $this->logger
            ->shouldReceive('error')
            ->with($e);

        $this->assertNull($this->consumer->process($orderPartnerTracking));
    }
}
