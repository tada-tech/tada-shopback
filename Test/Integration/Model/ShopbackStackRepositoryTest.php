<?php
declare(strict_types=1);

namespace Tada\Shopback\Test\Integration\Model;

if (!class_exists('\Magento\Catalog\Api\Data\ProductExtensionInterfaceFactory')) {
    require __DIR__ . '/../../_files/product_extension_interface_hacktory.php';
}

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Tada\Shopback\Api\Data\ShopbackStackSearchResultInterface;
use Tada\Shopback\Api\ShopbackStackRepositoryInterface;
use Tada\Shopback\Model\ShopbackStack;

class ShopbackStackRepositoryTest extends TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var ShopbackStackRepositoryInterface
     */
    protected $repository;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->repository = $this->objectManager->get(ShopbackStackRepositoryInterface::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/two_orders_with_order_items.php
     * @magentoDataFixture Tada_Shopback::Test/_files/shopback_stacks.php
     */
    public function testGetPendingItems()
    {
        /** @var ShopbackStackSearchResultInterface $result */
        $result = $this->repository->getPendingItems();
        $dones = $this->repository->getDoneItems();
        $this->assertEquals(2, $result->getTotalCount());
    }

    /**
     * Gets order entity by increment id.
     *
     * @param string $incrementId
     * @return OrderInterface
     */
    private function getOrder(string $incrementId): OrderInterface
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('increment_id', $incrementId)
            ->create();

        /** @var OrderRepositoryInterface $repository */
        $repository = $this->objectManager->get(OrderRepositoryInterface::class);
        $items = $repository->getList($searchCriteria)
            ->getItems();

        return array_pop($items);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/two_orders_with_order_items.php
     */
    public function testAddToStackWithAction()
    {
        /** @var OrderInterface $order */
        $order = $this->getOrder('100000001');
        $orderId = (int)$order->getEntityId();

        $result = $this->repository->addToStackWithAction($orderId, 'validate');

        $this->assertNotFalse($result);
        $this->assertEquals('pending', $result->getStatus());
        $this->assertEquals('validate', $result->getAction());
        $this->assertEquals($orderId, $result->getOrderId());
        $this->assertNotNull($result->getCreatedAt());
        $this->assertNull($result->getUpdatedAt());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/two_orders_with_order_items.php
     */
    public function testMakedone()
    {
        /** @var OrderInterface $order */
        $order = $this->getOrder('100000001');
        $orderId = (int)$order->getEntityId();

        /** @var ShopbackStack $stackItem */
        $stackItem = $this->repository->addToStackWithAction($orderId, 'create');

        $this->assertNotFalse($stackItem);
        $this->assertEquals('pending', $stackItem->getStatus());

        /** @var ShopbackStack $result */
        $result = $stackItem->done();

        $this->assertEquals('done', $result->getStatus());
    }
}
