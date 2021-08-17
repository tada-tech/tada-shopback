<?php
declare(strict_types=1);

namespace Tada\Shopback\Plugin\Order;

use Magento\Sales\Model\ResourceModel\Order as ResourceOrder;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Api\OrderRepositoryInterface;
use Tada\Shopback\Helper\Data;

class ShopbackValidateTrigger
{
    protected $configData;
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
     * @param Data $configData
     * @param OrderRepositoryInterface $orderRepository
     * @param EventManager $eventManager
     */
    public function __construct(
        Data $configData,
        OrderRepositoryInterface $orderRepository,
        EventManager $eventManager
    ) {
        $this->configData = $configData;
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
        if (!$this->configData->isOrderValidationFlowEnabled()) {
            return $result;
        }

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

        $allowedStates = $this->configData->getAllowedOrderStateToTriggerValidate();
        if ($oldState != $newState && in_array($newState, $allowedStates)) {
            $this->eventManager->dispatch(
                'order_call_shopback_validate_request',
                ['order_partner_tracking' => $orderPartnerTracking]
            );
        }

        return $result;
    }
}
