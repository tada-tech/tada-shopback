<?php
declare(strict_types=1);

namespace Tada\Shopback\Test\Unit\Observer;

use Magento\Framework\Event\Observer;
use PHPUnit\Framework\TestCase;
use Mockery;
use Psr\Log\LoggerInterface;
use Tada\CashbackTracking\Model\CashbackTracking;
use Tada\Shopback\Api\Data\ShopbackStackInterface;
use Tada\Shopback\Api\ShopbackStackRepositoryInterface;
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

    protected $shopbackStackRepository;

    protected function setUp()
    {
        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->shopbackStackRepository = Mockery::mock(ShopbackStackRepositoryInterface::class);
        $this->shopbackValidateOrderObserver = new ShopbackValidateOrderObserver(
            $this->shopbackStackRepository,
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

        $orderId = 1;

        $orderCashbackTrackingEntity->shouldReceive('getOrderId')
            ->andReturn($orderId);

        $shopbackStack = Mockery::mock(ShopbackStackInterface::class);

        $this->shopbackStackRepository
            ->shouldReceive('addToStackWithAction')
            ->with($orderId, 'validate')
            ->andReturn($shopbackStack);

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

        $e = Mockery::mock(\Exception::class);

        $this->shopbackStackRepository
            ->shouldReceive('addToStackWithAction')
            ->with($orderId, 'validate')
            ->andReturn($e);

        $this->logger->shouldReceive('error')
            ->with($e);

        $this->assertNull($this->shopbackValidateOrderObserver->execute($observer));
    }
}
