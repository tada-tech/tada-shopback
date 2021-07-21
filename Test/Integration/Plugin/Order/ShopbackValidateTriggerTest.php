<?php
declare(strict_types=1);

namespace Tada\Shopback\Test\Integration\Plugin\Order;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\MysqlMq\Model\Message;
use Magento\MysqlMq\Model\QueueManagement;
use Magento\MysqlMq\Model\ResourceModel\MessageCollection;
use Magento\MysqlMq\Model\ResourceModel\MessageStatusCollection;
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

class ShopbackValidateTriggerTest extends TestCase
{
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
        $this->orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $this->cartManagement = $this->objectManager->create(CartManagementInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoAppIsolation enabled
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

        $partner = $order->getExtensionAttributes()->getPartnerTracking();

        $topic = "shopback.api";

        /** @var Message $message */
        $message = $this->getTopicLatestMessage($topic);
        $this->assertEquals(QueueManagement::MESSAGE_STATUS_NEW, $message->getStatus());

        $messageBody = json_decode($message->getBody(), true);

        $this->assertEquals($partner->getPartner(), $messageBody['partner']);
        $this->assertEquals($partner->getPartnerParameter(), $messageBody['partner_parameter']);
        $this->assertEquals($partner->getEntityId(), $messageBody['entity_id']);
        $this->assertEquals($partner->getOrderId(), $messageBody['order_id']);
        $this->assertEquals(
            $partner->getExtensionAttributes()->getAction(),
            $messageBody['extension_attributes']['action']
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoAppIsolation enabled
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

        $topic = "shopback.api";
        $messageCollection = $this->_getListMessages($topic);

        $this->assertEquals(0, $messageCollection->getSize());
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

    /**
     * @param string $topic
     * @return Message
     */
    private function getTopicLatestMessage(string $topic) : Message
    {
        $messageCollection = $this->_getListMessages($topic);
        $message = $messageCollection->getFirstItem();
        return $message;
    }

    private function _getListMessages($topic): MessageCollection
    {
        // Assert message status is error
        $messageCollection = $this->objectManager->create(MessageCollection::class);
        $messageStatusCollection = $this->objectManager->create(MessageStatusCollection::class);

        $messageCollection->addFilter('topic_name', $topic);
        $messageCollection->join(
            ['status' => $messageStatusCollection->getMainTable()],
            "status.message_id = main_table.id"
        );
        $messageCollection->addOrder('message_id', MessageCollection::SORT_ORDER_DESC);
        return $messageCollection;
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
