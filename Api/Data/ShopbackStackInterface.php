<?php
declare(strict_types=1);

namespace Tada\Shopback\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;
use Tada\Shopback\Api\Data\ShopbackStackExtensionInterface;

interface ShopbackStackInterface extends ExtensibleDataInterface
{
    const TBL_NAME = 'tada_shopback_stack';
    const ENTITY_ID = 'entity_id';
    const ORDER_ITEM_ID = 'order_item_id';
    const ACTION = 'action';
    const STATUS = 'status';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const ALLOW_STATUS = ['pending', 'done'];

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * @return int
     */
    public function getOrderItemId();

    /**
     * @param int $orderItemId
     * @return $this
     */
    public function setOrderItemId(int $orderItemId);

    /**
     * @return string
     */
    public function getAction();

    /**
     * @param string $action
     * @return $this
     */
    public function setAction(string $action);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $status
     * @return string
     */
    public function setStatus(string $status);

    /**
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt(string $createdAt);

    /**
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt(string $updatedAt);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Tada\Shopback\Api\Data\ShopbackStackExtensionInterface
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Tada\Shopback\Api\Data\ShopbackStackExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(ShopbackStackExtensionInterface $extensionAttributes);
}
