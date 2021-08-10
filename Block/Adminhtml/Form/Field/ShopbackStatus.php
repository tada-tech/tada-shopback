<?php
declare(strict_types=1);

namespace Tada\Shopback\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Tada\Shopback\Model\Config\Source\Shopback\Status;

class ShopbackStatus extends Select
{
    const RENDERER_KEY = 'shopback_status';

    /**
     * @var Status
     */
    protected $sourceStatus;

    /**
     * ShopbackStatus constructor.
     * @param Status $sourceStatus
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Status $sourceStatus,
        \Magento\Framework\View\Element\Context $context,
        array $data = []
    ) {
        $this->sourceStatus = $sourceStatus;
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
        return $this->sourceStatus->toOptionArray();
    }
}
