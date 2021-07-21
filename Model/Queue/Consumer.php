<?php
declare(strict_types=1);

namespace Tada\Shopback\Model\Queue;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Tada\CashbackTracking\Api\Data\CashbackTrackingInterface as OrderPartnerTrackingInterface;
use Tada\Shopback\Api\ShopbackValidateOrderInterface as ValidateAction;
use Tada\Shopback\Api\ShopbackCreateOrderInterface as CreateAction;

class Consumer
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CreateAction
     */
    private $createAction;

    /**
     * @var ValidateAction
     */
    private $validateAction;

    /**
     * Consumer constructor.
     * @param LoggerInterface $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param ValidateAction $validateAction
     * @param CreateAction $createAction
     */
    public function __construct(
        LoggerInterface $logger,
        OrderRepositoryInterface $orderRepository,
        ValidateAction $validateAction,
        CreateAction $createAction
    ) {
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->validateAction = $validateAction;
        $this->createAction = $createAction;
    }

    /**
     * @param OrderInterface $order
     */
    public function process(OrderPartnerTrackingInterface $orderPartnerTracking)
    {
        try {
            /** @var OrderInterface $order */
            $order = $this->orderRepository->get((int)$orderPartnerTracking->getOrderId());

            $action = $this->_getAction($orderPartnerTracking);

            if ($action) {
                $action->execute($order);
            }

        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }

    private function _getAction(OrderPartnerTrackingInterface $orderPartnerTracking)
    {
        $action = $orderPartnerTracking->getExtensionAttributes()->getAction();

        if ($action && $action == 'create') {
            return $this->createAction;
        }

        if ($action && $action == 'validate') {
            return $this->validateAction;
        }

        return null;
    }
}
