<?php
declare(strict_types=1);

namespace Tada\Shopback\Test\Integration\Observer;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\MysqlMq\Model\Message;
use Magento\MysqlMq\Model\QueueManagement;
use Magento\MysqlMq\Model\ResourceModel\MessageCollection;
use Magento\MysqlMq\Model\ResourceModel\MessageStatusCollection;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Rate;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Tada\CashbackTracking\Api\CashbackTrackingRepositoryInterface;
use Tada\CashbackTracking\Model\CashbackTracking;
use Tada\CashbackTracking\Model\CashbackTrackingFactory;
use Tada\Shopback\Observer\AfterCashbackTrackingSaveObserver;

/**
 * @magentoAppIsolation enabled
 */
class AfterCashbackTrackingSaveObserverTest extends TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var CashbackTrackingFactory
     */
    protected $cashbackTrackingFactory;

    /**
     * @var CashbackTrackingRepositoryInterface
     */
    protected $cashbackTrackingRepository;

    /**
     * @var PublisherInterface
     */
    protected $publisher;

    /**
     * @var CartManagementInterface
     */
    protected $cartManagement;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->cashbackTrackingFactory = $this->objectManager->create(CashbackTrackingFactory::class);
        $this->cashbackTrackingRepository = $this->objectManager->create(CashbackTrackingRepositoryInterface::class);
        $this->publisher = $this->objectManager->create(PublisherInterface::class);
        $this->cartManagement = $this->objectManager->create(CartManagementInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testExecute()
    {
        /** @var Quote $quote */
        $quote = $this->_getQuote('test01');
        $quote = $this->_prepareQuote($quote);
        $orderId = (int) $this->cartManagement->placeOrder($quote->getId());

        $cashbackTracking = $this->cashbackTrackingFactory->create();
        $cashbackTracking->setData(
            [
                'order_id' => $orderId,
                'partner' => 'Shopback',
                'partner_parameter' => 'happynewyear'
            ]
        );
        $cashbackTrackingEntity = $this->cashbackTrackingRepository->save($cashbackTracking);
        $this->assertInstanceOf(CashbackTracking::class, $cashbackTrackingEntity);

        $this->publisher->publish(AfterCashbackTrackingSaveObserver::TOPIC_NAME, $cashbackTrackingEntity);

        $messageCollection = $this->_getListMessages(AfterCashbackTrackingSaveObserver::TOPIC_NAME);

        /** @var Message $message */
        $message = $messageCollection->getFirstItem();

        $this->assertEquals(QueueManagement::MESSAGE_STATUS_NEW, $message->getStatus());

        $messageBody = json_decode($message->getBody(), true);

        foreach ($messageBody as $key => $value) {
            $this->assertEquals($cashbackTrackingEntity->getData($key), $value);
        }
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
        $messageCollection->addOrder('updated_at', MessageCollection::SORT_ORDER_DESC);
        return $messageCollection;
    }
}
