<?php
declare(strict_types=1);

namespace Tada\Shopback\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Magento\Sales\Model\Config\Source\Order\Status;

class OrderStatus extends Select
{
    const RENDERER_KEY = 'order_status';
    /**
     * @var Status
     */
    protected $sourceOrderStatus;

    public function __construct(
        Status $sourceOrderStatus,
        \Magento\Framework\View\Element\Context $context,
        array $data = []
    ) {
        $this->sourceOrderStatus = $sourceOrderStatus;
        parent::__construct($context, $data);
    }

    /**
     * Set input name
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    /**
     * @return array
     */
    private function getSourceOptions(): array
    {
        return $this->sourceOrderStatus->toOptionArray();
    }
}
