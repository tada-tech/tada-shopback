<?php
declare(strict_types=1);

namespace Tada\Shopback\Plugin\Order;

use Magento\Sales\Model\ResourceModel\Order as ResourceOrder;
use Magento\Sales\Model\Order;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Api\OrderRepositoryInterface;

class ShopbackValidateTrigger
{
    const ALLOW_STATE_TO_TRIGGER = [Order::STATE_COMPLETE, Order::STATE_CANCELED];

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * ShopbackValidateTrigger constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param EventManager $eventManager
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        EventManager $eventManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->eventManager = $eventManager;
    }

    /**
     * @param ResourceOrder $subject
     * @param ResourceOrder $result
     * @param \Magento\Framework\Model\AbstractModel $orderModel
     * @return ResourceOrder
     */
    public function afterSave(
        ResourceOrder $subject,
        ResourceOrder $result,
        \Magento\Framework\Model\AbstractModel $orderModel
    ) {
        try {
            $order = $this->orderRepository->get((int)$orderModel->getEntityId());
        } catch (\Exception $e) {
            return $result;
        }

        if (!$orderPartnerTracking = $order->getExtensionAttributes()->getPartnerTracking()) {
            return $result;
        }

        $oldState = $orderModel->getOrigData('state');
        $newState = $orderModel->getData('state');

        if ($oldState != $newState && in_array($newState, self::ALLOW_STATE_TO_TRIGGER)) {
            $this->eventManager->dispatch(
                'order_call_shopback_validate_request',
                ['order_partner_tracking' => $orderPartnerTracking]
            );
        }

        return $result;
    }
}
