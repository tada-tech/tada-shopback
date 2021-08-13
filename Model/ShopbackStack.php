<?php
declare(strict_types=1);

namespace Tada\Shopback\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\AbstractExtensibleModel;
use Tada\Shopback\Api\Data\ShopbackStackExtensionInterface;
use Tada\Shopback\Api\Data\ShopbackStackInterface;
use Tada\Shopback\Model\ResourceModel\ShopbackStack as ResourceModel;

class ShopbackStack extends AbstractExtensibleModel implements ShopbackStackInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $_dateFactory;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_dateFactory = $dateFactory;
    }

    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * Set created_at parameter
     *
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function beforeSave()
    {
        $date = $this->_dateFactory->create()->gmtDate();
        if ($this->isObjectNew() && !$this->getCreatedAt()) {
            $this->setCreatedAt($date);
        } else {
            $this->setUpdatedAt($date);
        }
        return parent::beforeSave();
    }

    /**
     * @return int
     */
    public function getOrderItemId()
    {
        return $this->getData(self::ORDER_ITEM_ID);
    }

    /**
     * @param int $orderItemId
     * @return $this
     */
    public function setOrderItemId(int $orderItemId)
    {
        $this->setData(self::ORDER_ITEM_ID, $orderItemId);
        return $this;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->getData(self::ACTION);
    }

    /**
     * @param string $action
     * @return $this
     */
    public function setAction(string $action)
    {
        $this->setData(self::ACTION, $action);
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status)
    {
        $this->setData(self::STATUS, $status);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt(string $createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt(string $updatedAt)
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
        return $this;
    }

    /**
     * @return ShopbackStackExtensionInterface
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @param ShopbackStackExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(ShopbackStackExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    public function done()
    {
        $this->setStatus('done');
        $this->getResource()->save($this);
        return $this;
    }
}
