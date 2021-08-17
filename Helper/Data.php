<?php
declare(strict_types=1);

namespace Tada\Shopback\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Store\Model\ScopeInterface;
use Tada\Shopback\Block\Adminhtml\Form\Field\OrderStatus;
use Tada\Shopback\Block\Adminhtml\Form\Field\ShopbackStatus;

class Data extends AbstractHelper
{
    const SHOPBACK_GENERAL_COOKIE_TIMELIFE = 'shopback/general/cookie_timelife';
    const SHOPBACK_GENERAL_SHOPBACK_URL = 'shopback/general/shopback_url';
    const SHOPBACK_GENERAL_SHOPBACK_TRANSACTION_PARAMETER = 'shopback/general/shopback_transaction_parameter';
    const SHOPBACK_GENERAL_SHOPBACK_STATUS_MAPPING = 'shopback/general/shopback_status_mapping';
    const SHOPBACK_GENERAL_SHOPBACK_CREATE_OFFER_ID = 'shopback/general/shopback_create_offer_id';
    const SHOPBACK_GENERAL_SHOPBACK_CREATE_AFFILIATE_ID = 'shopback/general/shopback_create_affiliate_id';
    const SHOPBACK_GENERAL_SHOPBACK_VALIDATE_OFFER_ID = 'shopback/general/shopback_validate_offer_id';
    const SHOPBACK_GENERAL_SHOPBACK_VALIDATE_AFFILIATE_ID = 'shopback/general/shopback_validate_affiliate_id';
    const SHOPBACK_GENERAL_SHOPBACK_SECURITY_TOKEN = 'shopback/general/shopback_security_token';
    const SHOPBACK_GENERAL_SHOPBACK_ORDER_VALIDATION_FLOW_ENABLED = 'shopback/general/shopback_order_validation_flow_enabled';
    const SHOPBACK_GENERAL_SHOPBACK_TESTING_ENABLED = 'shopback/general/testing_enabled';

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
     * @return string
     */
    public function getShopbackUrl()
    {
        return $this->scopeConfig->getValue(self::SHOPBACK_GENERAL_SHOPBACK_URL);
    }

    /**
     * @return string
     */
    public function getShopbackTransactionParameter()
    {
        return $this->scopeConfig->getValue(self::SHOPBACK_GENERAL_SHOPBACK_TRANSACTION_PARAMETER);
    }

    /**
     * @return string
     */
    public function getCreateShopbackOfferId()
    {
        return $this->scopeConfig->getValue(self::SHOPBACK_GENERAL_SHOPBACK_CREATE_OFFER_ID);
    }

    /**
     * @return string
     */
    public function getCreateShopbackAffiliateId()
    {
        return $this->scopeConfig->getValue(self::SHOPBACK_GENERAL_SHOPBACK_CREATE_AFFILIATE_ID);
    }

    /**
     * @return string
     */
    public function getValidateShopbackOfferId()
    {
        return $this->scopeConfig->getValue(self::SHOPBACK_GENERAL_SHOPBACK_VALIDATE_OFFER_ID);
    }

    /**
     * @return string
     */
    public function getValidateShopbackAffiliateId()
    {
        return $this->scopeConfig->getValue(self::SHOPBACK_GENERAL_SHOPBACK_VALIDATE_AFFILIATE_ID);
    }

    /**
     * @return string
     */
    public function getShopbackSecurityToken()
    {
        return $this->scopeConfig->getValue(self::SHOPBACK_GENERAL_SHOPBACK_SECURITY_TOKEN);
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

    /**
     * @param OrderItemInterface $orderItem
     * @return string
     */
    public function toAdvSub(OrderItemInterface $orderItem): string
    {
        return 'ORD' . $orderItem->getOrderId() . '_' . $orderItem->getSku();
    }

    /**
     * @return bool
     */
    public function getTestingEnabled()
    {
        return (bool)$this->scopeConfig->getValue(self::SHOPBACK_GENERAL_SHOPBACK_TESTING_ENABLED);
    }

    /**
     * @return bool
     */
    public function isOrderValidationFlowEnabled()
    {
        return (bool)$this->scopeConfig->getValue(self::SHOPBACK_GENERAL_SHOPBACK_ORDER_VALIDATION_FLOW_ENABLED);
    }
}
