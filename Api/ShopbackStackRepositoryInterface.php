<?php
declare(strict_types=1);

namespace Tada\Shopback\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Tada\Shopback\Api\Data\ShopbackStackInterface;
use Tada\Shopback\Api\Data\ShopbackStackSearchResultInterface;

interface ShopbackStackRepositoryInterface
{
    /**
     * @param int $entityId
     * @return ShopbackStackInterface
     * @throws NoSuchEntityException
     */
    public function get(int $entityId): ShopbackStackInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return ShopbackStackSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param ShopbackStackInterface $object
     * @return ShopbackStackInterface
     * @throws CouldNotSaveException
     */
    public function save(ShopbackStackInterface $object):ShopbackStackInterface;

    /**
     * @param ShopbackStackInterface $object
     * @return ShopbackStackInterface
     * @throws \Exception
     */
    public function delete(ShopbackStackInterface $object): ShopbackStackInterface;
}
