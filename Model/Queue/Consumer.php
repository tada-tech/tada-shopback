<?php
declare(strict_types=1);

namespace Tada\Shopback\Model\Queue;

use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Tada\CashbackTracking\Api\Data\CashbackTrackingInterface;
use Tada\Shopback\Api\ShopbackStackRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;

class Consumer
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ShopbackStackRepositoryInterface
     */
    protected $shopbackStackRepository;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     * @param ShopbackStackRepositoryInterface $shopbackStackRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        ShopbackStackRepositoryInterface $shopbackStackRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->shopbackStackRepository = $shopbackStackRepository;
    }

    /**
     * @param CashbackTrackingInterface $cashbackTracking
     */
    public function process(CashbackTrackingInterface $cashbackTracking)
    {
        try {
            $orderId = (int)$cashbackTracking->getOrderId();
            $action = $cashbackTracking->getExtensionAttributes()->getAction();
            $order = $this->orderRepository->get($orderId);

            $this->execute($order, $action);

        } catch (\Exception $exception) {
            $this->logger->error($exception);
        }
    }

    public function execute(OrderInterface $order, string $action): void
    {
        $orderId = (int) $order->getEntityId();
        $items = $order->getItems();

        $items = $this->_filterItems($items);

        /** @var OrderItemInterface $item */
        foreach ($items as $item) {
            $orderItemId = (int)$item->getItemId();
            $this->shopbackStackRepository->addToStackWithAction($orderItemId, $action);
        }
    }

    /**
     * @param OrderInterface[] $items
     * @return OrderInterface[]
     */
    protected function _filterItems(array $items)
    {
        $result = [];
        /** @var OrderItemInterface $item */
        foreach ($items as $item) {
            if ($this->isSkip($item)) {
                continue;
            }
            $result[] = $item;
        }
        return $result;
    }

    /**
     * @param OrderItemInterface $item
     * @return bool
     */
    protected function isSkip(OrderItemInterface $item)
    {
        return $this->isBundleType($item) || $this->hasParentAndParentTypeIsConfigurable($item);
    }

    /**
     * @param OrderItemInterface $item
     * @return bool
     */
    protected function isBundleType(OrderItemInterface $item)
    {
        $itemType = $item->getProductType();
        return $itemType == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE;
    }

    /**
     * @param OrderItemInterface $item
     * @return bool
     */
    protected function hasParentAndParentTypeIsConfigurable(OrderItemInterface $item)
    {
        if ($parent = $item->getParentItem()) {
            if ($parent->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                return true;
            }
        }
        return false;
    }
}
