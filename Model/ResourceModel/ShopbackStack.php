<?php
declare(strict_types=1);

namespace Tada\Shopback\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Tada\Shopback\Api\Data\ShopbackStackInterface;

class ShopbackStack extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(
            ShopbackStackInterface::TBL_NAME,
            ShopbackStackInterface::ENTITY_ID
        );
    }

    /**
     * @param int $orderItemId
     * @param string $action
     * @return int|false
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isExisted(int $orderItemId, string $action)
    {
        $conn = $this->getConnection();

        $select = $conn->select()
            ->from($this->getMainTable(), ShopbackStackInterface::ENTITY_ID)
            ->where('order_item_id = :order_item_id and action = :action and status = :status');

        $bind = [':order_item_id' => $orderItemId, ':action' => $action, ':status' => 'pending'];

        return $conn->fetchOne($select, $bind);
    }

    /**
     * @param int $orderItemId
     * @param string $action
     * @return int|false
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isStatusDone(int $orderItemId, string $action = 'create')
    {
        $conn = $this->getConnection();

        $select = $conn->select()
            ->from($this->getMainTable(), ShopbackStackInterface::ENTITY_ID)
            ->where('order_item_id = :order_item_id and action = :action and status = :status');

        $bind = [':order_item_id' => $orderItemId, ':action' => $action, ':status' => 'done'];

        return $conn->fetchOne($select, $bind);
    }
}
