<?php
declare(strict_types=1);

namespace Tada\Shopback\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;
use Tada\Shopback\Block\Adminhtml\Form\Field\OrderStatus;
use Tada\Shopback\Block\Adminhtml\Form\Field\ShopbackStatus;

class Data extends AbstractHelper
{
    const SHOPBACK_GENERAL_COOKIE_TIMELIFE = 'shopback/general/cookie_timelife';
    const SHOPBACK_GENERAL_SHOPBACK_STATUS_MAPPING = 'shopback/general/shopback_status_mapping';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Data constructor.
     * @param SerializerInterface $serializer
     * @param Context $context
     */
    public function __construct(
        SerializerInterface $serializer,
        Context $context
    ) {
        $this->serializer = $serializer;
        parent::__construct($context);
    }

    /**
     * @return int
     */
    public function getCookieTimeLife()
    {
        return (int)$this->scopeConfig->getValue(self::SHOPBACK_GENERAL_COOKIE_TIMELIFE);
    }

    /**
     * @return array
     */
    public function getShopbackStatusMapping():array
    {
        return $this->getSerializedValue(self::SHOPBACK_GENERAL_SHOPBACK_STATUS_MAPPING);
    }

    /**
     * Get ShopBack Status by Order State
     *
     * @param string $name
     * @param int|null $storeId
     * @return string|null
     */
    public function getShopbackStatusByOrderState(string $state, int $storeId = null): ?string
    {
        foreach ($this->getShopbackStatusMapping() as $status) {
            $statusName = $status[OrderStatus::RENDERER_KEY] ?? null;
            if ($statusName === $state) {
                return $status[ShopbackStatus::RENDERER_KEY] ?? null;
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public function getAllowedOrderStateToTriggerValidate():array
    {
        $allowedOrderState = [];
        foreach ($this->getShopbackStatusMapping() as $status) {
            $allowedOrderState[] = $status[OrderStatus::RENDERER_KEY];
        }
        return $allowedOrderState;
    }

    /**
     * Returns serialized value
     *
     * @param string $path
     * @param int|null $storeId
     * @return array
     */
    private function getSerializedValue(string $path, int $storeId = null): array
    {
        $value = $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        try {
            $result = $this->serializer->unserialize($value);
        } catch (\InvalidArgumentException $e) {
            $result = [];
        }

        return $result;
    }
}
