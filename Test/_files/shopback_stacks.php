<?php

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderRepositoryInterface;

function getOrder($incrementId)
{
    $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

    /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
    $searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
    $searchCriteria = $searchCriteriaBuilder->addFilter('increment_id', $incrementId)
        ->create();

    /** @var OrderRepositoryInterface $repository */
    $repository = $objectManager->get(OrderRepositoryInterface::class);
    $items = $repository->getList($searchCriteria)
        ->getItems();

    $item = array_pop($items);
    return $item;
}

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$repo = $objectManager->get(\Tada\Shopback\Api\ShopbackStackRepositoryInterface::class);

$modelFactory = $objectManager->get(\Tada\Shopback\Model\ShopbackStackFactory::class);

/** @var \Magento\Sales\Api\Data\OrderInterface $order */
$order = getOrder('100000001');

$orderId = $order->getEntityId();

$item1 = $modelFactory->create();
$item1->setData([
    'order_id' => $orderId,
    'action' => 'create',
    'status' => 'pending'
]);

$repo->save($item1);


$item2 = $modelFactory->create();
$item2->setData([
    'order_id' => $orderId,
    'action' => 'create',
    'status' => 'done'
]);

$repo->save($item2);

$item3 = $modelFactory->create();
$item3->setData([
    'order_id' => $orderId,
    'action' => 'validate',
    'status' => 'pending'
]);

$repo->save($item3);
