<?php
declare(strict_types=1);

namespace Tada\Shopback\Model\ResourceModel\ShopbackStack;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Tada\Shopback\Model\ShopbackStack;
use Tada\Shopback\Model\ResourceModel\ShopbackStack as ShopbackStackResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            ShopbackStack::class,
            ShopbackStackResourceModel::class
        );
    }
}
