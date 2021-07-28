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
     * @param int $orderId
     * @param string $action
     * @return int|false
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isExisted(int $orderId, string $action)
    {
        $conn = $this->getConnection();

        $select = $conn->select()
            ->from($this->getMainTable(), ShopbackStackInterface::ENTITY_ID)
            ->where('order_id = :order_id and action = :action and status = :status');

        $bind = [':order_id' => $orderId, ':action' => $action, ':status' => 'pending'];

        return $conn->fetchOne($select, $bind);
    }
}
