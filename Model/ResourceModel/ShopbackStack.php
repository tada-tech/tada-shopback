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
}
