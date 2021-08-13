<?php
declare(strict_types=1);

namespace Tada\Shopback\Test\Integration;

use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Tada\Shopback\Api\Data\ShopbackStackInterface;
use Tada\Shopback\Api\ShopbackStackRepositoryInterface;
use Tada\Shopback\Model\UpdateStackProcessor;

class UpdateStackProcessorTest extends TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var ShopbackStackRepositoryInterface
     */
    protected $shopbackStackRepository;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var OrderItemRepositoryInterface
     */
    protected $orderItemRepository;

    /**
     * @var UpdateStackProcessor
     */
    protected $updateStackProcessor;

    /**
     * @var CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var array
     */
    protected $servicePool = [];

    /**
     * @var ResponseFactory
     */
    protected $responseFactory;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->cartManagement = $this->objectManager->create(CartManagementInterface::class);
        $this->shopbackStackRepository = $this->objectManager->create(ShopbackStackRepositoryInterface::class);
        $this->orderRepository = $this->objectManager->create(OrderRepositoryInterface::class);
        $this->orderItemRepository = $this->objectManager->create(OrderItemRepositoryInterface::class);

        $this->servicePool['create'] = $this->objectManager
            ->create(\Tada\Shopback\Api\ShopbackCreateOrderInterface::class);
        $this->servicePool['validate'] = $this->objectManager
            ->create(\Tada\Shopback\Api\ShopbackValidateOrderInterface::class);

        $this->responseFactory = $this->objectManager->create(ResponseFactory::class);

        $this->updateStackProcessor = new UpdateStackProcessor(
            $this->shopbackStackRepository,
            $this->orderItemRepository,
            $this->servicePool
        );
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->deleteStacks();
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testExecute()
    {
        list($orderItems, $total) = $this->createStacks();

        $pendingList = $this->shopbackStackRepository->getPendingItems();

        // fake success response
        /** @var  $response */
        $response = $this->responseFactory->create(
            [
                'status' => 200,
                'body' => 'success=true;'
            ]
        );

        foreach ($pendingList->getItems() as $item) {
            $action = $item->getAction();
            if ($this->servicePool[$action]->isSkip($item)) {
                continue;
            }

            $this->servicePool[$action]->beforeExecute($item);

            //Fake Execute => $response

            $this->servicePool[$action]->afterExecute($response, $item);
        }

        $pendingList = $this->shopbackStackRepository->getPendingItems();

        $this->assertEquals(0, $pendingList->getTotalCount());
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

    private function _getOrderId()
    {
        $quote = $this->_getQuote('test01');
        $quote = $this->_prepareQuote($quote);

        $orderId = $this->cartManagement->placeOrder($quote->getId());
        return (int)$orderId;
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
            $this->shopbackStackRepository->addToStackWithAction($itemId, $action);
        }
        return [$orderItems, $totalItems];
    }

    private function deleteStacks()
    {
        $list = $this->shopbackStackRepository->getPendingItems();
        /** @var ShopbackStackInterface $item */
        foreach ($list->getItems() as $item) {
            $this->shopbackStackRepository->delete($item);
        }
    }
}
