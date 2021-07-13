<?php
declare(strict_types=1);

namespace Tada\Shopback\Block;

use Tada\Shopback\Helper\Data;

class ShopbackTracking extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Data
     */
    protected $configData;

    /**
     * ShopbackTracking constructor.
     * @param Data $configData
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        Data $configData,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->configData = $configData;
        parent::__construct($context, $data);
    }

    /**
     * @return int
     */
    public function getCookieTTL()
    {
        return $this->configData->getCookieTimeLife();
    }
}
