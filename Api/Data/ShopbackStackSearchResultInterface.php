<?php
declare(strict_types=1);

namespace Tada\Shopback\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface ShopbackStackSearchResultInterface extends SearchResultsInterface
{
    /**
     * @return ShopbackStackInterface[]
     */
    public function getItems();

    /**
     * @param ShopbackStackInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
