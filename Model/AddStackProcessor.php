<?php
declare(strict_types=1);

namespace Tada\Shopback\Model;

use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Tada\Shopback\Api\AddStackProcessorInterface;
use Tada\Shopback\Api\ShopbackStackRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;

class AddStackProcessor implements AddStackProcessorInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var ShopbackStackRepositoryInterface
     */
    protected $shopbackStackRepository;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param ShopbackStackRepositoryInterface $shopbackStackRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ShopbackStackRepositoryInterface $shopbackStackRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->shopbackStackRepository = $shopbackStackRepository;
    }

    /**
     * @param OrderInterface $order
     * @param string $action
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(OrderInterface $order, string $action): void
    {
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
