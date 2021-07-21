<?php
declare(strict_types=1);

namespace Tada\Shopback\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\BlockInterface;

class StatusMapping extends AbstractRenderer
{
    /**
     * @var BlockInterface
     */
    protected $orderStatusRenderer;

    /**
     * @var BlockInterface
     */
    protected $shopbackStatusRenderer;

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareToRender()
    {
        $columns = [
            OrderStatus::RENDERER_KEY => [
                'label' => __('Order Status'),
                'renderer' => $this->_getOrderStatusRenderer()
            ],
            ShopbackStatus::RENDERER_KEY => [
                'label' => __('ShopBack Status'),
                'renderer' => $this->_getShopbackStatusRenderer()
            ]
        ];

        foreach ($columns as $name => $params) {
            $this->addColumn($name, $params);
        }

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row):void
    {
        $this->prepareArrayRow($row, [
            OrderStatus::RENDERER_KEY => $this->_getOrderStatusRenderer(),
            ShopbackStatus::RENDERER_KEY => $this->_getShopbackStatusRenderer()
        ]);
    }

    /**
     * @return BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getOrderStatusRenderer(): BlockInterface
    {
        if (!$this->orderStatusRenderer) {
            $this->orderStatusRenderer = $this->getRenderer(OrderStatus::class);
        }
        return $this->orderStatusRenderer;
    }

    /**
     * @return BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getShopbackStatusRenderer(): BlockInterface
    {
        if (!$this->shopbackStatusRenderer) {
            $this->shopbackStatusRenderer = $this->getRenderer(ShopbackStatus::class);
        }

        return $this->shopbackStatusRenderer;
    }
}
