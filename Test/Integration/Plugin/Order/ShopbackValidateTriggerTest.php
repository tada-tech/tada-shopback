<?php
declare(strict_types=1);

namespace Tada\Shopback\Test\Integration\Plugin\Order;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Quote\Api\Data\CartInterface;
use Tada\Shopback\Api\ShopbackStackRepositoryInterface;

class ShopbackValidateTriggerTest extends TestCase
{
    /**
     * @var ShopbackStackRepositoryInterface
     */
    protected $shopbackStackRepository;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var CartManagementInterface
     */
    protected $cartManagement;

    protected function setUp()
    {
        $this->objectManager = $this->objectManager = Bootstrap::getObjectManager();
        $this->shopbackStackRepository = $this->objectManager->get(ShopbackStackRepositoryInterface::class);
        $this->orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $this->cartManagement = $this->objectManager->create(CartManagementInterface::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testAfterSaveOrderCanTriggerEvent()
    {
        $quote = $this->_getQuote('test01');
        /** @var OrderInterface $order */
        $order = $this->_getOrderHasCashbaskTrackingObject($quote);

        $this->assertEquals(Order::STATE_NEW, $order->getState());

        $order->setState(Order::STATE_CANCELED);
        $order->setStatus(Order::STATE_CANCELED);
        $order = $this->orderRepository->save($order);

        $this->assertEquals(Order::STATE_CANCELED, $order->getState());

        $pendingList = $this->shopbackStackRepository->getPendingItems();

        $items = $pendingList->getItems();
        $total = $pendingList->getTotalCount();

        $item = array_pop($items);

        $this->assertEquals($order->getEntityId(), $item->getOrderId());
        $this->assertEquals('validate', $item->getAction());
        $this->assertEquals('pending', $item->getStatus());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testAfterSaveOrderCanNotTriggerEvent()
    {
        $quote = $this->_getQuote('test01');
        /** @var OrderInterface $order */
        $order = $this->_getOrderHasNotCashbaskTrackingObject($quote);

        $partner = $order->getExtensionAttributes()->getPartnerTracking();
        $this->assertEquals(Order::STATE_NEW, $order->getState());

        $order->setState(Order::STATE_COMPLETE);
        $order->setStatus(Order::STATE_COMPLETE);
        $order = $this->orderRepository->save($order);
        $this->assertEquals(Order::STATE_COMPLETE, $order->getState());


        $pendingList = $this->shopbackStackRepository->getPendingItems();

        $items = $pendingList->getItems();
        $total = $pendingList->getTotalCount();


        $this->assertEquals(0, $total);
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
    private function _prepareQuote(CartInterface $quote): Quote
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

    protected function _getOrderHasCashbaskTrackingObject(CartInterface $quote): OrderInterface
    {
        $quote = $this->_prepareQuote($quote);
        $orderId = $this->cartManagement->placeOrder($quote->getId());
        $order = $this->orderRepository->get((int)$orderId);
        return $order;
    }

    protected function _getOrderHasNotCashbaskTrackingObject(CartInterface $quote): OrderInterface
    {
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


        $orderId = $this->cartManagement->placeOrder($quote->getId());
        $order = $this->orderRepository->get((int)$orderId);
        return $order;
    }
}
