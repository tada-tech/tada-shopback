<?php
declare(strict_types=1);

namespace Tada\Shopback\Test\Unit\Model\ResourceModel\Order\Plugin;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order as ResourceOrder;
use PHPUnit\Framework\TestCase;
use Mockery;
use Tada\Shopback\Model\ResourceModel\Order\Plugin\ShopbackValidateTrigger;
use Tada\CashbackTracking\Api\Data\CashbackTrackingInterface;
use Magento\Sales\Model\Order;

class ShopbackValidateTriggerTest extends TestCase
{
    /**
     * @var Mockery\MockInterface
     */
    protected $orderRepository;

    /**
     * @var Mockery\MockInterface
     */
    protected $eventManager;

    /**
     * @var ShopbackValidateTrigger
     */
    protected $shopbackValidateTrigger;

    protected function setUp()
    {
        $this->orderRepository = Mockery::mock(OrderRepositoryInterface::class);
        $this->eventManager = Mockery::mock(EventManager::class);
        $this->shopbackValidateTrigger = new ShopbackValidateTrigger(
            $this->orderRepository,
            $this->eventManager
        );
    }

    protected function tearDown()
    {
        Mockery::close();
    }

    public function testAfterSaveAndTriggerEvent()
    {
        /**
         * @var Mockery\MockInterface $subject
         * @var Mockery\MockInterface $result
         * @var Mockery\MockInterface $orderModel
         * @var Mockery\MockInterface $order
         */
        list($subject, $result, $orderModel, $order) = $this->_getMockObject();

        $entityId = 1;
        $orderModel->shouldReceive('getEntityId')
            ->andReturn($entityId);

        $this->orderRepository
            ->shouldReceive('get')
            ->with($entityId)
            ->andReturn($order);

        $extensionAttributes = Mockery::mock(\Magento\Sales\Api\Data\OrderExtensionInterface::class);
        $order->shouldReceive('getExtensionAttributes')
            ->andReturn($extensionAttributes);

        $partnerTracking = Mockery::mock(CashbackTrackingInterface::class);
        $extensionAttributes->shouldReceive('getPartnerTracking')
            ->andReturn($partnerTracking);

        $orderModel->shouldReceive('getOrigData')
            ->with('state')
            ->andReturn(Order::STATE_NEW);

        $orderModel->shouldReceive('getData')
            ->with('state')
            ->andReturn(Order::STATE_COMPLETE);

        $data = ['order_partner_tracking' => $partnerTracking];
        $this->eventManager
            ->shouldReceive('dispatch')
            ->with('order_call_shopback_validate_request', $data);

        $this->assertSame($result, $this->shopbackValidateTrigger->afterSave($subject, $result, $orderModel));
    }

    public function testAfterSaveButNotTriggerEvent()
    {
        /**
         * @var Mockery\MockInterface $subject
         * @var Mockery\MockInterface $result
         * @var Mockery\MockInterface $orderModel
         * @var Mockery\MockInterface $order
         */
        list($subject, $result, $orderModel, $order) = $this->_getMockObject();

        $entityId = 1;
        $orderModel->shouldReceive('getEntityId')
            ->andReturn($entityId);

        $this->orderRepository
            ->shouldReceive('get')
            ->with($entityId)
            ->andReturn($order);

        $extensionAttributes = Mockery::mock(\Magento\Sales\Api\Data\OrderExtensionInterface::class);

        $order->shouldReceive('getExtensionAttributes')
            ->andReturn($extensionAttributes);

        $extensionAttributes->shouldReceive('getPartnerTracking')
            ->andReturn(null);

        $this->assertSame($result, $this->shopbackValidateTrigger->afterSave($subject, $result, $orderModel));
    }

    public function testAfterSaveWithException()
    {
        /**
         * @var Mockery\MockInterface $subject
         * @var Mockery\MockInterface $result
         * @var Mockery\MockInterface $orderModel
         * @var Mockery\MockInterface $order
         */
        list($subject, $result, $orderModel, $order) = $this->_getMockObject();

        $this->orderRepository
            ->shouldReceive('get')
            ->with(null)
            ->andThrow(\Exception::class);

        $this->assertSame($result, $this->shopbackValidateTrigger->afterSave($subject, $result, $orderModel));
    }

    /**
     * @return array
     */
    private function _getMockObject()
    {
        $subject = Mockery::mock(ResourceOrder::class);
        $result = Mockery::mock(ResourceOrder::class);
        $orderModel = Mockery::mock(AbstractModel::class);

        $order = Mockery::mock(\Magento\Sales\Api\Data\OrderInterface::class);
        return [
            $subject,
            $result,
            $orderModel,
            $order
        ];
    }
}
