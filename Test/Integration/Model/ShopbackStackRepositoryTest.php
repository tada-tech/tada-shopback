<?php
declare(strict_types=1);

namespace Tada\Shopback\Test\Integration\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Tada\Shopback\Api\Data\ShopbackStackInterface;
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

    /**
     * @var CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->cartManagement = $this->objectManager->create(CartManagementInterface::class);
        $this->repository = $this->objectManager->get(ShopbackStackRepositoryInterface::class);
        $this->orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->deleteStacks();
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testGetPendingItems()
    {
        list($orderItems, $total) = $this->createStacks();

        /** @var ShopbackStackSearchResultInterface $result */
        $result = $this->repository->getPendingItems();
        $this->assertEquals($total, $result->getTotalCount());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testAddToStackWithAction()
    {
        list($orderItems, $total) = $this->createStacks('validate');

        /** @var OrderItemInterface $result */
        $result = $orderItems[0];
        $pendingItems = $this->repository->getPendingItems()->getItems();

        $pendingItem = array_pop($pendingItems);

        $this->assertEquals($pendingItem->getOrderItemId(), $result->getItemId());
        $this->assertEquals('validate', $pendingItem->getAction());
        $this->assertEquals('pending', $pendingItem->getStatus());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testAddToStackWithActionDuplicate()
    {
        list($orderItems, $total) = $this->createStacks();
        /** @var OrderItemInterface $orderItem */
        $orderItem = $orderItems[0];

        $orderItemId = (int)$orderItem->getItemId();
        $itemOne = $this->repository->addToStackWithAction($orderItemId, 'validate');
        $itemTwo = $this->repository->addToStackWithAction($orderItemId, 'validate');

        $this->assertNotFalse($itemOne);
        $this->assertEquals('pending', $itemOne->getStatus());
        $this->assertEquals('validate', $itemOne->getAction());
        $this->assertEquals($orderItemId, $itemOne->getOrderItemId());
        $this->assertNotNull($itemOne->getCreatedAt());
        $this->assertNull($itemOne->getUpdatedAt());

        $this->assertFalse($itemTwo);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testMakeDone()
    {
        list($orderItems, $total) = $this->createStacks();

        $pendingList = $this->repository->getPendingItems();

        $items = $pendingList->getItems();
        /** @var ShopbackStack $stackItem */
        $stackItem = array_pop($items);

        $this->assertNotFalse($stackItem);
        $this->assertEquals('pending', $stackItem->getStatus());

        /** @var ShopbackStack $result */
        $result = $stackItem->done();

        $this->assertEquals('done', $result->getStatus());
    }

    protected function _getOrderId()
    {
        $quote = $this->_getQuote('test01');
        $quote = $this->_prepareQuote($quote);

        $orderId = $this->cartManagement->placeOrder($quote->getId());
        return (int)$orderId;
    }

    /**
     * Gets quote by reserved order ID.
     *
     * @param string $reservedOrderId
     * @return Quote
     */
    private function _getQuote(string $reservedOrderId): Quote
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $items = $quoteRepository->getList($searchCriteria)
            ->getItems();

        return array_pop($items);
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _prepareQuote(\Magento\Quote\Api\Data\CartInterface $quote): Quote
    {
        $tracking = [
            'partner' => 'shopback',
            'partner_parameter' => 'happy10discount'
        ];

        $quote->getPayment()->setAdditionalInformation($tracking);

        $shippingAddress = $quote->getShippingAddress();

        /** @var $rate Rate */
        $rate = $this->objectManager->create(Rate::class);
        $rate->setCode('flatrate_flatrate');
        $rate->setPrice(5);

        $shippingAddress->setShippingMethod('flatrate_flatrate');
        $shippingAddress->addShippingRate($rate);

        $quote->setShippingAddress($shippingAddress);
        $quote->setCheckoutMethod('guest');
        $quoteRepository = $this->objectManager
            ->get(\Magento\Quote\Api\CartRepositoryInterface::class);
        $quoteRepository->save($quote);

        return $quote;
    }

    private function createStacks($action = 'create')
    {
        $orderId = $this->_getOrderId();
        /** @var OrderInterface $order */
        $order = $this->orderRepository->get($orderId);

        $orderItems = $order->getItems();
        $totalItems = $order->getTotalItemCount();
        foreach ($orderItems as $item) {
            $itemId = (int)$item->getItemId();
            $this->repository->addToStackWithAction($itemId, $action);
        }
        return [$orderItems, $totalItems];
    }

    private function deleteStacks()
    {
        $list = $this->repository->getPendingItems();
        /** @var ShopbackStackInterface $item */
        foreach ($list->getItems() as $item) {
            $this->repository->delete($item);
        }
    }
}
